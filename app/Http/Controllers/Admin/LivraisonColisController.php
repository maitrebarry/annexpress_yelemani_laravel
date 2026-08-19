<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Colis;
use App\Models\Utilisateur;
use App\Support\Flash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Port de Projets_licence/app/controllers/admin/Livraison_colis.php +
 * app/models/Livraisons_colis.php (findByCode, getById, livrer — pas infoCompagnie, qui ne sert
 * qu'à l'impression PDF reportée).
 */
class LivraisonColisController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::guard('staff')->user()->load('agence');

        return $this->chercherEtRendre($user, $request->query('code'));
    }

    public function store(Request $request): View|RedirectResponse
    {
        $user = Auth::guard('staff')->user()->load('agence');

        if (! $request->has('livrer')) {
            return $this->chercherEtRendre($user, $request->input('code'));
        }

        $idColis = (int) $request->input('id_colis', 0);

        if ($idColis <= 0) {
            Flash::set('Colis introuvable.', 'danger');

            return redirect()->route('admin.colis.livraison.index');
        }

        $colis = $this->trouverParId($user, $idColis);

        if (! $colis) {
            Flash::set('Colis introuvable.', 'danger');

            return redirect()->route('admin.colis.livraison.index');
        }

        if (! $this->peutLivrer($user, $colis)) {
            Flash::set('Pas les droits pour livrer.', 'danger');

            return redirect()->route('admin.colis.livraison.index');
        }

        $updated = Colis::where('id_colis', $idColis)
            ->where('id_compagnie', $user->id_compagnie)
            ->update(['status' => 'livre', 'date_livraison' => now()->toDateString()]);

        if (! $updated) {
            Flash::set('Erreur lors de la mise à jour du statut.', 'danger');

            return redirect()->route('admin.colis.livraison.index');
        }

        Flash::set('Colis livré avec succès !', 'primary');

        // On réaffiche la page avec le colis (désormais livré) pour proposer tout de suite le
        // bouton WhatsApp de confirmation à l'expéditeur.
        $colis->status = 'livre';

        return view('admin.colis.livraison.index', [
            'colis' => $colis,
            'peutLivrer' => false,
            'codeRecherche' => $colis->code_colis ?? '',
            'livraisonReussie' => true,
        ]);
    }

    private function chercherEtRendre(Utilisateur $user, ?string $codeRecherche): View
    {
        $colis = null;
        $peutLivrer = false;

        if ($codeRecherche !== null) {
            $code = trim($codeRecherche);

            if ($code === '') {
                Flash::set('Merci de renseigner un code colis.', 'danger');
            } else {
                $colis = $this->trouverParCode($user, $code);

                if (! $colis) {
                    Flash::set("Le code colis n'existe pas.", 'danger');
                } else {
                    $peutLivrer = $this->peutLivrer($user, $colis);
                }
            }
        }

        return view('admin.colis.livraison.index', [
            'colis' => $colis,
            'peutLivrer' => $peutLivrer,
            'codeRecherche' => $codeRecherche !== null ? trim($codeRecherche) : '',
            'livraisonReussie' => false,
        ]);
    }

    private function trouverParCode(Utilisateur $user, string $code): ?object
    {
        return $this->requeteColis($user)
            ->where('colis.code_colis', $code)
            ->first();
    }

    private function trouverParId(Utilisateur $user, int $idColis): ?object
    {
        return $this->requeteColis($user)
            ->leftJoin('utilisateur', 'utilisateur.idUser', '=', 'colis.id_utilisateur')
            ->addSelect('utilisateur.utilisateurs as agent_nom')
            ->where('colis.id_colis', $idColis)
            ->first();
    }

    private function requeteColis(Utilisateur $user)
    {
        return DB::table('colis')
            ->join('expediteurs', 'expediteurs.id_expediteur', '=', 'colis.id_expediteur')
            ->join('destinataires', 'destinataires.id_destinataire', '=', 'colis.id_destinataire')
            ->join('agence', 'agence.idAgence', '=', 'colis.id_agence')
            ->where('colis.id_compagnie', $user->id_compagnie)
            ->select(
                'colis.*',
                'expediteurs.expediteur', 'expediteurs.numero_exp', 'expediteurs.whatsapp_exp',
                'destinataires.destinataire', 'destinataires.numero_dest', 'destinataires.whatsapp_dest',
                'agence.localite as localite', 'agence.numeroGare as numero_gare'
            );
    }

    /**
     * Port exact du match() legacy — 'utilisateur' en minuscule ne correspond jamais au rôle
     * réel 'Utilisateur' (toujours en majuscule en session) : un compte Utilisateur ne peut
     * donc jamais livrer via cet écran, seul chef_d_escale le peut. Typo legacy reproduite telle
     * quelle, pas corrigée.
     */
    private function peutLivrer(Utilisateur $user, object $colis): bool
    {
        $memeVille = $colis->localite === $user->agence?->localite;
        $memeGare = $colis->numero_gare === $user->agence?->numeroGare;
        $bonStatut = $colis->status === 'recu';

        return match ($user->droit) {
            'chef_d_escale' => $memeVille && $bonStatut,
            'utilisateur' => $memeVille && $memeGare && $bonStatut,
            default => false,
        };
    }
}
