<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agence;
use App\Models\Caisse;
use App\Models\CaisseUtilisateur;
use App\Models\ClotureEscale;
use App\Models\Utilisateur;
use App\Services\CaisseUtilisateurService;
use App\Support\Flash;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Port de Projets_licence/app/controllers/admin/Caisse.php. Couvre le système de caisse
 * individuelle par opérateur (table `caisse_utilisateur`) ET la lecture seule du "Bilan
 * de caisse" (table `caisse`, système de caisse de gare plus ancien). La création/gestion
 * d'une caisse de gare (écrans legacy `caisse`/`add_caisse`, non liés au menu) reste hors
 * scope : la table `caisse` est vide, "Bilan de caisse" fonctionne mais n'affichera rien
 * tant qu'aucune ligne n'y existe.
 */
class CaisseController extends Controller
{
    public function maCaisse(CaisseUtilisateurService $service): View
    {
        $user = Auth::guard('staff')->user();

        $caisse = $service->getCaisseOuverte($user->idUser);
        $caisseFermee = $caisse ? null : $service->getCaisseFermeeNonVersee($user->idUser);

        return view('admin.caisse.ma-caisse', [
            'caisse' => $caisse,
            'caisseFermee' => $caisseFermee,
            'journal' => $caisse ? $service->getJournal($caisse->id_caisse_user) : collect(),
            'historique' => $service->getHistoriqueCaisses($user->idUser, 20),
            'listeAgences' => $user->droit === 'Admin' ? Agence::where('id_compagnie', $user->id_compagnie)->orderBy('localite')->get() : collect(),
            'chefs' => $caisseFermee ? $service->getChefsDEscale($caisseFermee->id_agence, $user->id_compagnie) : collect(),
        ]);
    }

    public function ouvrirCaisse(Request $request, CaisseUtilisateurService $service): RedirectResponse
    {
        $user = Auth::guard('staff')->user();

        $resultat = $service->ouvrirCaisse(
            $user,
            $request->filled('id_agence') ? (int) $request->input('id_agence') : null,
            (float) $request->input('montant_initial', 0)
        );

        Flash::set($resultat['message'], $resultat['type']);

        return redirect()->route('admin.caisse.ma-caisse');
    }

    public function fermerCaisse(Request $request, CaisseUtilisateurService $service): RedirectResponse
    {
        $user = Auth::guard('staff')->user();

        $resultat = $service->fermerCaisse($user, (float) $request->input('montant_compte', 0));
        Flash::set($resultat['message'], $resultat['type']);

        return redirect()->route('admin.caisse.ma-caisse');
    }

    public function verser(Request $request, CaisseUtilisateurService $service): RedirectResponse
    {
        $user = Auth::guard('staff')->user();

        $resultat = $service->creerVersement(
            $user,
            (int) $request->input('id_chef_escale'),
            (float) $request->input('montant', 0),
            $request->input('commentaire')
        );
        Flash::set($resultat['message'], $resultat['type']);

        return redirect()->route('admin.caisse.ma-caisse');
    }

    public function caissesEscale(Request $request, CaisseUtilisateurService $service): View
    {
        $user = Auth::guard('staff')->user();
        [$idAgence, $estAdmin, $listeAgences] = $this->resolveGareEscale($user, $request);
        $date = $request->input('date', now()->toDateString());
        $idChef = $user->droit === 'chef_d_escale' ? $user->idUser : null;

        $caisses = $idAgence ? $service->getCaissesEscale($idAgence, $date) : collect();

        return view('admin.caisse.caisses-escale', [
            'caisses' => $caisses,
            'versementsAttente' => $idAgence ? $service->getVersementsEnAttente($estAdmin ? null : $idChef, $idAgence) : collect(),
            'historiqueVersements' => $idAgence ? $service->getHistoriqueVersements($estAdmin ? null : $idChef, $idAgence) : collect(),
            'date' => $date,
            'totalBillets' => $caisses->sum('total_billets'),
            'totalColis' => $caisses->sum('total_colis'),
            'totalEcarts' => $caisses->sum('ecart'),
            'estAdmin' => $estAdmin,
            'listeAgences' => $listeAgences,
            'idAgence' => $idAgence,
            'caissesOuvertes' => $idAgence ? CaisseUtilisateur::where('id_agence', $idAgence)->whereDate('date_service', $date)->where('statut', 'ouverte')->count() : 0,
            'historiqueClotures' => $idAgence ? ClotureEscale::where('id_agence', $idAgence)->orderByDesc('date_cloture')->limit(30)->get() : collect(),
        ]);
    }

    public function validerVersement(Request $request, CaisseUtilisateurService $service): RedirectResponse
    {
        $user = Auth::guard('staff')->user();

        $resultat = $service->validerVersement(
            $user,
            (int) $request->input('id_versement'),
            (string) $request->input('action'),
            $request->filled('id_agence') ? (int) $request->input('id_agence') : null
        );
        Flash::set($resultat['message'], $resultat['type']);

        // Conserve la gare/date consultées au retour, sinon l'Admin retomberait sur
        // un écran "choisissez une gare" à chaque action.
        return redirect()->route('admin.caisse.caisses-escale', array_filter([
            'id_agence' => $request->input('id_agence'),
            'date' => $request->input('date'),
        ]));
    }

    public function clotureEscale(Request $request, CaisseUtilisateurService $service): RedirectResponse
    {
        $user = Auth::guard('staff')->user();
        $idAgence = $user->droit === 'Admin' ? (int) $request->input('id_agence') : $user->id_agence;
        $date = $request->input('date', now()->toDateString());

        if (! $idAgence) {
            Flash::set('Gare manquante.', 'danger');

            return redirect()->route('admin.caisse.caisses-escale');
        }

        $resultat = $service->cloturerEscale($user, $idAgence, $date);
        Flash::set($resultat['message'], $resultat['type']);

        return redirect()->route('admin.caisse.caisses-escale', array_filter([
            'id_agence' => $user->droit === 'Admin' ? $idAgence : null,
            'date' => $date,
        ]));
    }

    public function rapportProprietaire(Request $request, CaisseUtilisateurService $service): View|RedirectResponse
    {
        $user = Auth::guard('staff')->user();

        if ($user->id_compagnie === null) {
            Flash::set("Ce rapport n'est disponible que pour un compte rattaché à une compagnie.", 'warning');

            return redirect()->route('admin.home');
        }

        $date = $request->input('date', now()->toDateString());

        $rapport = $service->getRapportProprietaire($user->id_compagnie, $date);

        return view('admin.caisse.rapport-proprietaire', [
            'rapport' => $rapport,
            'date' => $date,
            'totalBillets' => $rapport->sum('total_billets'),
            'totalColis' => $rapport->sum('total_colis'),
            'totalEcarts' => $rapport->sum('total_ecarts'),
            'grandTotal' => $rapport->sum('total_billets') + $rapport->sum('total_colis'),
        ]);
    }

    // bilant_caisse_billets()/bilant_caisse_colis() dans le legacy — vues quasi
    // identiques (seule la colonne "état actuel" change), fusionnées ici en une seule
    // vue paramétrée par $type.
    public function bilantBillets(): View|RedirectResponse
    {
        return $this->bilant('billets');
    }

    public function bilantColis(): View|RedirectResponse
    {
        return $this->bilant('colis');
    }

    private function bilant(string $type): View|RedirectResponse
    {
        $user = Auth::guard('staff')->user();

        // Aucune scope n'existe pour un simple Utilisateur, qui verrait donc les caisses
        // de tout le monde : il ne doit voir que la sienne, via "Ma Caisse".
        if ($user->droit === 'Utilisateur') {
            Flash::set('Vous ne pouvez consulter que votre propre caisse.', 'danger');

            return redirect()->route('admin.caisse.ma-caisse');
        }

        $listeCaisse = Caisse::query()
            ->join('agence as a', 'a.idAgence', '=', 'caisse.id_agence')
            ->where('a.id_compagnie', $user->id_compagnie)
            ->when($user->droit === 'chef_d_escale', fn ($q) => $q->where('a.idAgence', $user->id_agence))
            ->get(['caisse.*', 'a.localite', 'a.numeroGare']);

        $sommeFn = $type === 'billets' ? 'sommeBillets' : 'sommeColis';
        $listeCaisse->each(function ($c) use ($user, $sommeFn) {
            $c->total_jour = $this->$sommeFn($user->id_compagnie, $c->localite, 'jour', $c->numeroGare);
            $c->total_mois = $this->$sommeFn($user->id_compagnie, $c->localite, 'mois', $c->numeroGare);
        });

        return view('admin.caisse.bilant', ['listeCaisse' => $listeCaisse, 'type' => $type]);
    }

    // Détail des mouvements (entrées/sorties) d'une caisse de gare, pour la modale
    // "Voir" du bilan. Répond en JSON (AJAX).
    public function mouvements(Request $request, int $id): JsonResponse
    {
        $user = Auth::guard('staff')->user();

        $caisse = Caisse::query()
            ->join('agence as a', 'a.idAgence', '=', 'caisse.id_agence')
            ->where('caisse.id_caisse', $id)->where('caisse.id_compagnie', $user->id_compagnie)
            ->first(['caisse.*', 'a.localite', 'a.numeroGare', 'a.idAgence']);

        if (! $caisse) {
            return response()->json(['error' => 'Caisse introuvable.'], 404);
        }
        if ($user->droit === 'chef_d_escale' && (int) $caisse->idAgence !== (int) $user->id_agence) {
            return response()->json(['error' => 'Accès refusé.'], 403);
        }

        $periode = in_array($request->query('periode'), ['jour', 'mois', 'tout'], true) ? $request->query('periode') : 'jour';
        $type = in_array($request->query('type'), ['billets', 'colis', 'tout'], true) ? $request->query('type') : 'tout';

        $today = now()->toDateString();
        if ($periode === 'jour') {
            $debut = $fin = $today;
        } elseif ($periode === 'mois') {
            $debut = now()->startOfMonth()->toDateString();
            $fin = now()->endOfMonth()->toDateString();
        } else {
            $debut = $caisse->date_enregistrement;
            $fin = $caisse->date_fermeture ?? $today;
        }

        $entrees = [];
        $sorties = [];

        // Billets vendus pour cette gare précise pendant la période (bilan billets ou "tout").
        if ($type !== 'colis') {
            $billets = DB::table('billets as b')->join('client as c', 'b.id_client', '=', 'c.idClient')
                ->leftJoin('utilisateur as u', 'b.idUser', '=', 'u.idUser')
                ->where('b.id_compagnie', $user->id_compagnie)->where('b.departId', $caisse->localite)->where('b.num_gare', $caisse->numeroGare)
                ->where('b.validation_billets', 'valider')
                ->where(fn ($q) => $q->whereNull('b.status_billets')->orWhere('b.status_billets', '!=', 'annule'))
                ->whereBetween('b.date_reservation', [$debut, $fin])
                ->orderByDesc('b.date_reservation')
                ->get(['b.numeroBillets as reference', DB::raw("CAST(REPLACE(REPLACE(c.montant_payer, ' ', ''), 'FCFA', '') AS DECIMAL(12,2)) as montant"), 'b.date_reservation as date_mvt', 'b.idUser as id_agent', 'u.utilisateurs as nom_agent']);
            foreach ($billets as $b) {
                $entrees[] = ['type' => 'Billet', 'reference' => $b->reference, 'montant' => (float) $b->montant, 'date' => $b->date_mvt, 'agent' => $this->nomAgent($b->id_agent, $b->nom_agent)];
            }
        }

        // Colis enregistrés pour cette gare précise pendant la période (bilan colis ou "tout").
        // colis.id_agence stocke la gare de DESTINATION, pas celle qui encaisse : on filtre
        // par provient_de/num_gare (gare d'origine), comme sommeColis().
        if ($type !== 'billets') {
            $colis = DB::table('colis as co')->leftJoin('utilisateur as u', 'co.id_utilisateur', '=', 'u.idUser')
                ->where('co.id_compagnie', $user->id_compagnie)->where('co.provient_de', $caisse->localite)->where('co.num_gare', $caisse->numeroGare)
                ->whereBetween('co.date_enregistrement', [$debut, $fin])
                ->orderByDesc('co.date_enregistrement')
                ->get(['co.code_colis as reference', 'co.fraix_transaction as montant', 'co.date_enregistrement as date_mvt', 'co.id_utilisateur as id_agent', 'u.utilisateurs as nom_agent']);
            foreach ($colis as $c) {
                $entrees[] = ['type' => 'Colis', 'reference' => $c->reference, 'montant' => (float) $c->montant, 'date' => $c->date_mvt, 'agent' => $this->nomAgent($c->id_agent, $c->nom_agent)];
            }
        }

        // Versements reçus des caisses individuelles des opérateurs de cette gare :
        // uniquement visible en mode "tout" (page non filtrée par type).
        if ($type === 'tout') {
            $versements = DB::table('versements_caisse as v')->leftJoin('utilisateur as u', 'v.id_emetteur', '=', 'u.idUser')
                ->where('v.id_agence', $caisse->idAgence)->where('v.id_compagnie', $user->id_compagnie)->where('v.statut', 'valide')
                ->whereBetween(DB::raw('DATE(v.date_versement)'), [$debut, $fin])
                ->orderByDesc('v.date_versement')
                ->get(['v.id_versement', 'v.montant', 'v.date_versement', 'v.id_emetteur as id_agent', 'u.utilisateurs as nom_agent']);
            foreach ($versements as $v) {
                $entrees[] = ['type' => 'Versement', 'reference' => "VRS-{$v->id_versement}", 'montant' => (float) $v->montant, 'date' => $v->date_versement, 'agent' => $this->nomAgent($v->id_agent, $v->nom_agent)];
            }
        }

        // Dépenses déduites directement de cette caisse.
        $depenses = DB::table('depense as d')->leftJoin('utilisateur as u', 'd.id_utilisateur', '=', 'u.idUser')
            ->where('d.id_caisse', $id)->where('d.statut', 'valide')
            ->whereBetween('d.date_depense', [$debut, $fin])
            ->orderByDesc('d.date_depense')
            ->get(['d.categorie', 'd.libelle', 'd.montant', 'd.date_depense', 'd.id_utilisateur as id_agent', 'u.utilisateurs as nom_agent']);
        foreach ($depenses as $d) {
            $sorties[] = ['type' => 'Dépense', 'reference' => $d->libelle ?: $d->categorie, 'montant' => (float) $d->montant, 'date' => $d->date_depense, 'agent' => $this->nomAgent($d->id_agent, $d->nom_agent)];
        }

        // Remboursements colis : aucun lien individuel fiable vers une caisse ni date de
        // traitement en base — seul le cumul déjà déduit de CETTE caisse est fiable,
        // donc uniquement visible sur "tout l'historique".
        if ($periode === 'tout' && (float) $caisse->montant_rembourse > 0) {
            $sorties[] = [
                'type' => 'Remboursement',
                'reference' => 'Cumul des remboursements colis sur cette caisse',
                'montant' => (float) $caisse->montant_rembourse,
                'date' => null,
                'agent' => null,
            ];
        }

        return response()->json([
            'periode' => $periode, 'type' => $type,
            'caisse' => [
                'reference' => $caisse->reference_caise, 'localite' => $caisse->localite, 'numeroGare' => $caisse->numeroGare,
                'debut' => $debut, 'fin' => $fin, 'ouverte' => (int) $caisse->status_caisse === 1,
            ],
            'entrees' => $entrees, 'sorties' => $sorties,
            'total_entrees' => array_sum(array_column($entrees, 'montant')),
            'total_sorties' => array_sum(array_column($sorties, 'montant')),
        ]);
    }

    private function sommeBillets(int $idCompagnie, string $ville, string $periode, ?string $numeroGare): float
    {
        return (float) DB::table('billets as b')->join('client as c', 'b.id_client', '=', 'c.idClient')
            ->where('b.id_compagnie', $idCompagnie)
            ->where('b.validation_billets', 'valider')
            ->where(fn ($q) => $q->whereNull('b.status_billets')->orWhere('b.status_billets', '!=', 'annule'))
            ->where('b.departId', $ville)
            ->when($numeroGare, fn ($q) => $q->where('b.num_gare', $numeroGare))
            ->when($periode === 'jour', fn ($q) => $q->whereDate('b.date_reservation', now()->toDateString()))
            ->when($periode === 'mois', fn ($q) => $q->whereMonth('b.date_reservation', now()->month)->whereYear('b.date_reservation', now()->year))
            ->sum(DB::raw("CAST(REPLACE(REPLACE(c.montant_payer, ' ', ''), 'FCFA', '') AS DECIMAL(12,2))"));
    }

    private function sommeColis(int $idCompagnie, string $ville, string $periode, ?string $numeroGare): float
    {
        return (float) DB::table('colis')
            ->where('id_compagnie', $idCompagnie)->where('provient_de', $ville)
            ->when($numeroGare, fn ($q) => $q->where('num_gare', $numeroGare))
            ->when($periode === 'jour', fn ($q) => $q->whereDate('date_enregistrement', now()->toDateString()))
            ->when($periode === 'mois', fn ($q) => $q->whereMonth('date_enregistrement', now()->month)->whereYear('date_enregistrement', now()->year))
            ->sum('fraix_transaction');
    }

    // Nom de l'utilisateur ayant fait l'action. Si le compte a depuis été supprimé, on
    // l'indique au lieu de laisser un champ vide.
    private function nomAgent(?int $id, ?string $nom): ?string
    {
        if ($nom) {
            return $nom;
        }

        return $id ? "Utilisateur #$id (supprimé)" : null;
    }

    // Résout la gare consultée pour les écrans de supervision d'escale. Admin (pas de
    // gare fixe) : gare choisie dans le formulaire, revalidée contre sa propre
    // compagnie. chef_d_escale : toujours sa propre gare de session.
    private function resolveGareEscale(Utilisateur $user, Request $request): array
    {
        if ($user->droit !== 'Admin') {
            return [$user->id_agence, false, collect()];
        }

        $listeAgences = Agence::where('id_compagnie', $user->id_compagnie)->orderBy('localite')->get();

        $idAgencePoste = (int) ($request->input('id_agence') ?? 0);
        if ($idAgencePoste && ! $listeAgences->contains('idAgence', $idAgencePoste)) {
            $idAgencePoste = 0;
        }

        return [$idAgencePoste ?: null, true, $listeAgences];
    }
}
