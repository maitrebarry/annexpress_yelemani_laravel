<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agence;
use App\Models\CaisseUtilisateur;
use App\Models\Colis;
use App\Models\Depense;
use App\Models\Utilisateur;
use App\Support\Flash;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Port de Projets_licence/app/controllers/admin/Reclamations.php. Écran repensé en modales
 * (recherche + soumission de réclamation, gestion du statut) au lieu du formulaire deux
 * colonnes + rechargement de page du legacy — même direction UI que Location des cars et
 * Embarquement de billets.
 *
 * Écart avec le legacy : le remboursement débitait l'ancienne table `caisse` (morte dans
 * cette app, colonne `montant_rembourse`). Substitué par le pattern déjà établi pour
 * Billets/Dépôt en banque/Dépense : une `Depense` (catégorie "Remboursement colis") contre
 * la caisse individuelle actuellement ouverte (`caisse_utilisateur`) de la gare concernée.
 */
class ReclamationColisController extends Controller
{
    private const STATUTS = ['En attente', 'Remboursé', 'Rejeté'];

    public function index(): View
    {
        $user = Auth::guard('staff')->user()->load('agence');

        $reclamations = Colis::query()
            ->avecDetails()
            ->where('colis.id_compagnie', $user->id_compagnie)
            ->where('colis.reclamer', 1)
            ->orderByDesc('colis.date_reclamer')
            ->get();

        $caissesOuvertes = [];
        if ($user->droit === 'Admin') {
            $caissesOuvertes = CaisseUtilisateur::query()
                ->join('agence as a', 'a.idAgence', '=', 'caisse_utilisateur.id_agence')
                ->where('caisse_utilisateur.id_compagnie', $user->id_compagnie)
                ->where('caisse_utilisateur.statut', 'ouverte')
                ->select('caisse_utilisateur.*', 'a.localite')
                ->get();
        }

        return view('admin.colis.reclamation.index', [
            'reclamations' => $reclamations,
            'caissesOuvertes' => $caissesOuvertes,
            'peutGerer' => in_array($user->droit, ['Admin', 'chef_d_escale'], true),
        ]);
    }

    public function rechercher(Request $request): JsonResponse
    {
        $user = Auth::guard('staff')->user();
        $code = trim((string) $request->query('code'));

        if ($code === '') {
            return response()->json(['error' => 'Merci de saisir un code colis.'], 422);
        }

        $colis = Colis::query()
            ->avecDetails()
            ->where('colis.id_compagnie', $user->id_compagnie)
            ->where('colis.code_colis', $code)
            ->first();

        if (! $colis) {
            return response()->json(['error' => "Aucun colis trouvé avec le code $code."], 404);
        }

        if ((int) $colis->reclamer === 1) {
            return response()->json(['error' => 'Ce colis fait déjà l\'objet d\'une réclamation.'], 409);
        }

        return response()->json(['colis' => [
            'id_colis' => $colis->id_colis,
            'code_colis' => $colis->code_colis,
            'nature' => $colis->nature,
            'nom_colis' => $colis->nom_colis,
            'valeur' => (int) $colis->valeur,
            'expediteur' => $colis->expediteur,
            'numero_exp' => $colis->numero_exp,
            'destinataire' => $colis->destinataire,
            'numero_dest' => $colis->numero_dest,
        ]]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = Auth::guard('staff')->user();

        if ($user->estLectureSeule()) {
            Flash::set('Votre rôle est en lecture seule.', 'danger');

            return redirect()->route('admin.colis.reclamation.index');
        }

        $idColis = (int) $request->input('id_colis');
        $motif = trim((string) $request->input('motif_reclamation'));
        $montant = (int) $request->input('montant_remboursement');

        if ($idColis <= 0 || $motif === '' || $montant <= 0) {
            Flash::set('Le motif et le montant de remboursement sont obligatoires.', 'danger');

            return redirect()->route('admin.colis.reclamation.index');
        }

        $updated = Colis::where('id_colis', $idColis)
            ->where('id_compagnie', $user->id_compagnie)
            ->where(function ($q) {
                $q->whereNull('reclamer')->orWhere('reclamer', '!=', 1);
            })
            ->update([
                'reclamer' => 1,
                'date_reclamer' => now()->toDateString(),
                'motif_reclamation' => $motif,
                'montant_remboursement' => $montant,
                'status_reclamation' => 'En attente',
            ]);

        Flash::set(
            $updated > 0 ? 'La réclamation a été soumise avec succès.' : 'Colis introuvable ou déjà réclamé.',
            $updated > 0 ? 'success' : 'danger'
        );

        return redirect()->route('admin.colis.reclamation.index');
    }

    public function updateStatus(Request $request): RedirectResponse
    {
        $user = Auth::guard('staff')->user()->load('agence');

        if (! in_array($user->droit, ['Admin', 'chef_d_escale'], true)) {
            Flash::set("Vous n'avez pas l'autorisation d'approuver ou gérer les réclamations.", 'danger');

            return redirect()->route('admin.colis.reclamation.index');
        }

        $idColis = (int) $request->input('id_colis_status');
        $statut = $request->input('status_reclamation');
        $idCaisseUser = $request->input('admin_id_caisse');

        if ($idColis <= 0 || ! in_array($statut, self::STATUTS, true)) {
            Flash::set('Requête invalide.', 'danger');

            return redirect()->route('admin.colis.reclamation.index');
        }

        try {
            DB::transaction(function () use ($user, $idColis, $statut, $idCaisseUser) {
                $colis = Colis::where('id_colis', $idColis)->where('id_compagnie', $user->id_compagnie)
                    ->lockForUpdate()->first();

                if (! $colis) {
                    throw new \RuntimeException('COLIS_INTROUVABLE');
                }

                // Un chef d'escale ne peut gérer que les réclamations qui concernent sa propre
                // gare (départ ou destination) — le legacy ne vérifiait aucune portée ici,
                // permettant à n'importe quel chef de rembourser n'importe quel colis de la
                // compagnie contre sa propre caisse. Corrigé, même classe de bug que celle
                // déjà trouvée/corrigée sur les Billets.
                if ($user->droit === 'chef_d_escale') {
                    $localiteDestination = Agence::where('idAgence', $colis->id_agence)->value('localite');
                    $localiteChef = $user->agence?->localite;
                    if ($colis->provient_de !== $localiteChef && $localiteDestination !== $localiteChef) {
                        throw new \RuntimeException('HORS_GARE');
                    }
                }

                if ($statut === 'Remboursé' && $colis->status_reclamation !== 'Remboursé') {
                    $caisse = $this->resoudreCaisseADebiter($user, $idCaisseUser);
                    $montant = (float) $colis->montant_remboursement;

                    if ($montant > 0) {
                        Depense::create([
                            'id_compagnie' => $user->id_compagnie,
                            'id_agence' => $caisse->id_agence,
                            'id_caisse_user' => $caisse->id_caisse_user,
                            'categorie' => 'Remboursement colis',
                            'libelle' => 'Remboursement colis n°'.$colis->code_colis,
                            'montant' => $montant,
                            'date_depense' => now()->toDateString(),
                            'id_utilisateur' => $user->idUser,
                            'statut' => 'valide',
                        ]);

                        $caisse->update(['montant_depense' => $caisse->montant_depense + $montant]);
                    }
                }

                $colis->update(['status_reclamation' => $statut]);
            });
        } catch (\RuntimeException $e) {
            Flash::set(match ($e->getMessage()) {
                'COLIS_INTROUVABLE' => 'Colis introuvable.',
                'HORS_GARE' => 'Cette réclamation ne concerne pas votre gare.',
                'CAISSE_MANQUANTE' => 'Veuillez sélectionner une caisse pour effectuer le remboursement.',
                'CAISSE_FERMEE' => "Impossible de rembourser : aucune caisse n'est actuellement ouverte pour votre gare.",
                default => 'Erreur lors de la mise à jour.',
            }, 'danger');

            return redirect()->route('admin.colis.reclamation.index');
        }

        Flash::set('Le statut de la réclamation a été modifié avec succès.', 'success');

        return redirect()->route('admin.colis.reclamation.index');
    }

    private function resoudreCaisseADebiter(Utilisateur $user, $idCaisseUser): CaisseUtilisateur
    {
        if ($user->droit === 'Admin') {
            if (empty($idCaisseUser)) {
                throw new \RuntimeException('CAISSE_MANQUANTE');
            }

            $caisse = CaisseUtilisateur::where('id_caisse_user', $idCaisseUser)
                ->where('id_compagnie', $user->id_compagnie)
                ->where('statut', 'ouverte')
                ->lockForUpdate()
                ->first();

            if (! $caisse) {
                throw new \RuntimeException('CAISSE_FERMEE');
            }

            return $caisse;
        }

        // chef_d_escale : sa caisse individuelle ouverte aujourd'hui, même logique que le
        // remboursement d'annulation de billet (BilletService::executerAnnulation()).
        $caisse = CaisseUtilisateur::where('id_agence', $user->id_agence)
            ->whereDate('date_service', now()->toDateString())
            ->where('statut', 'ouverte')
            ->lockForUpdate()
            ->first();

        if (! $caisse) {
            throw new \RuntimeException('CAISSE_FERMEE');
        }

        return $caisse;
    }
}
