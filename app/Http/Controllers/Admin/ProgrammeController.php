<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agence;
use App\Models\Compagnie;
use App\Models\Escale;
use App\Models\Horaire;
use App\Models\LigneTrajet;
use App\Models\Programme;
use App\Support\Flash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Port de Projets_licence/app/controllers/admin/Programmer_voyages.php +
 * app/models/Programmer_voyage.php — écran "Voyages" du menu G-programme
 * (création/consultation/modification des voyages programmés).
 */
class ProgrammeController extends Controller
{
    public function index(): View
    {
        $user = Auth::guard('staff')->user();

        $liste = Programme::query()
            ->select('programmer.*')
            ->join('agence as a1', 'programmer.idDepart', '=', 'a1.idAgence')
            ->join('agence as a2', 'programmer.idDestination', '=', 'a2.idAgence')
            ->when($user->id_compagnie, fn ($q) => $q->where('programmer.id_compagnie', $user->id_compagnie))
            ->with(['depart', 'destination', 'escales.escale'])
            ->orderBy('a1.localite')
            ->orderBy('a2.localite')
            ->orderBy('programmer.heureDepart')
            ->get();

        // Un même trajet (départ/destination/heure) ne devrait jamais apparaître deux fois :
        // signalé s'il existe encore dans des données plus anciennes plutôt que laissé
        // passer inaperçu dans la liste.
        $occurrences = [];
        foreach ($liste as $programme) {
            $cle = $programme->idDepart.'|'.$programme->idDestination.'|'.$programme->heureDepart;
            $occurrences[$cle] = ($occurrences[$cle] ?? 0) + 1;
        }
        foreach ($liste as $programme) {
            $cle = $programme->idDepart.'|'.$programme->idDestination.'|'.$programme->heureDepart;
            $programme->estDoublon = $occurrences[$cle] > 1;
        }

        // Horaires de la compagnie, pour permettre de changer l'heure de départ à la
        // modification (l'heure de départ de Ségou n'est pas la même que celle de Bamako).
        $listeHoraire = $user->id_compagnie
            ? Horaire::where('id_compagnie', $user->id_compagnie)->orderBy('heuredepart')->get()
            : collect();

        return view('admin.programme.index', [
            'liste' => $liste,
            'listeHoraire' => $listeHoraire,
        ]);
    }

    public function create(Request $request): View
    {
        $user = Auth::guard('staff')->user();

        // Un super_admin n'a pas de compagnie propre : il doit d'abord en choisir une
        // (les listes de gares/escales/horaires en dépendent), sinon le formulaire ne
        // peut pas être rempli correctement.
        $idCompagnie = $user->isSuperAdmin() ? $request->query('id_compagnie') : $user->id_compagnie;

        if ($user->isSuperAdmin() && ! $idCompagnie) {
            return view('admin.programme.create', [
                'choixCompagnieRequis' => true,
                'listeCompagnie' => Compagnie::orderBy('nom_compagnie')->get(),
            ]);
        }

        $listeAgence = Agence::where('id_compagnie', $idCompagnie)->orderBy('localite')->get();

        // Le chef d'escale ne peut créer un programme qu'au départ de sa propre gare ;
        // l'Admin (ou le super_admin agissant pour la compagnie choisie) peut choisir
        // n'importe quelle gare de la compagnie comme départ.
        $listeAgenceDepart = $user->droit === 'chef_d_escale' && $user->id_agence
            ? Agence::where('idAgence', $user->id_agence)->get()
            : $listeAgence;

        $listeEscale = Escale::where('id_compagnie', $idCompagnie)->orderBy('escales')->get();
        $listeHoraire = Horaire::where('id_compagnie', $idCompagnie)->orderBy('heuredepart')->get();

        // Tous les trajets déjà programmés (toutes gares confondues) : affichés en
        // référence à côté du choix de la destination, pour repérer d'un coup d'œil ce
        // qui existe déjà avant d'en créer un nouveau.
        $tousLesTrajets = Programme::query()
            ->join('agence as a1', 'programmer.idDepart', '=', 'a1.idAgence')
            ->join('agence as a2', 'programmer.idDestination', '=', 'a2.idAgence')
            ->where('programmer.id_compagnie', $idCompagnie)
            ->orderBy('a1.localite')->orderBy('a2.localite')->orderBy('programmer.heureDepart')
            ->get(['programmer.heureDepart', 'programmer.prix', 'a1.localite as departLocalite', 'a1.numeroGare as departNumeroGare', 'a2.localite as destinationLocalite', 'a2.numeroGare as destinationNumeroGare']);

        return view('admin.programme.create', [
            'choixCompagnieRequis' => false,
            'idCompagnie' => $idCompagnie,
            'listeAgence' => $listeAgence,
            'listeAgenceDepart' => $listeAgenceDepart,
            'listeEscale' => $listeEscale,
            'listeHoraire' => $listeHoraire,
            'tousLesTrajets' => $tousLesTrajets,
        ]);
    }

    // Le formulaire permet de cocher plusieurs heures de départ à la fois : le même
    // itinéraire/escales/tarifs saisis une fois sont réutilisés pour programmer un
    // voyage par heure cochée, RDV calculé automatiquement (heure de départ - 45 min).
    // Une heure déjà programmée pour ce même trajet est ignorée plutôt que de faire
    // échouer tout l'envoi.
    public function store(Request $request): RedirectResponse
    {
        $user = Auth::guard('staff')->user();

        if ($user->isSuperAdmin()) {
            $idCompagnie = $request->input('id_compagnie');
            if (! $idCompagnie) {
                Flash::set('Veuillez choisir une compagnie.', 'danger');

                return redirect()->route('admin.programme.create');
            }
        } else {
            $idCompagnie = $user->id_compagnie;
        }

        $idDepart = $request->input('idDepart');
        $idDestination = $request->input('idDestination');
        $prix = $request->input('prix');
        $heureDeparts = array_values(array_unique(array_filter(
            (array) $request->input('heureDepart', []),
            fn ($h) => trim((string) $h) !== ''
        )));
        $idEscales = array_filter((array) $request->input('idEscale', []));
        $prixEscales = (array) $request->input('prix_escale', []);

        $errors = [];

        if ($user->droit === 'chef_d_escale' && (string) $idDepart !== (string) $user->id_agence) {
            $errors[] = 'Vous ne pouvez créer un programme qu\'au départ de votre propre gare.';
        }

        if (! $idDepart || ! $idDestination) {
            $errors[] = 'Le départ et la destination sont obligatoires.';
        } elseif ($idDepart === $idDestination) {
            $errors[] = 'Impossible d\'enregistrer ce trajet : départ et destination identiques.';
        } else {
            $departAgence = Agence::find($idDepart);
            $destinationAgence = Agence::find($idDestination);
            if ($departAgence && $destinationAgence && $departAgence->localite === $destinationAgence->localite) {
                $errors[] = 'Impossible d\'enregistrer ce trajet : le départ et la destination sont dans la même localité (voyage interne).';
            }
        }

        if (empty($heureDeparts)) {
            $errors[] = 'Veuillez cocher au moins une heure de départ.';
        }

        if (! empty($errors)) {
            Flash::set(implode(' ', $errors), 'warning');

            return redirect()->route('admin.programme.create', $user->isSuperAdmin() ? ['id_compagnie' => $idCompagnie] : []);
        }

        $nbAjoutes = 0;
        $dejaExistants = [];

        foreach ($heureDeparts as $heureDepart) {
            $existe = Programme::where('idDepart', $idDepart)
                ->where('idDestination', $idDestination)
                ->where('heureDepart', $heureDepart)
                ->where('id_compagnie', $idCompagnie)
                ->exists();

            if ($existe) {
                $dejaExistants[] = $heureDepart;

                continue;
            }

            $rdv = $this->calculerRdv($heureDepart);

            $programme = Programme::create([
                'idDepart' => $idDepart,
                'idDestination' => $idDestination,
                'heureDepart' => $heureDepart,
                'rdv' => $rdv,
                'prix' => $prix,
                'id_compagnie' => $idCompagnie,
            ]);

            $this->enregistrerEscales($programme->idProgrammer, $idEscales, $prixEscales);

            // Crée automatiquement le trajet retour (destination -> départ) s'il n'existe
            // pas déjà, pour qu'un car ne se retrouve jamais sans destination valide au retour.
            $this->assurerTrajetRetour($idDepart, $idDestination, $heureDepart, $rdv, $prix, $idCompagnie, $idEscales, $prixEscales);

            $nbAjoutes++;
        }

        if ($nbAjoutes > 0) {
            $message = $nbAjoutes > 1
                ? "$nbAjoutes voyages programmés avec succès (aller-retour créé automatiquement pour chacun)."
                : 'Le programme a été ajouté avec succès (aller-retour créé automatiquement).';
            if (! empty($dejaExistants)) {
                $message .= ' Déjà programmé, donc ignoré pour : '.implode(', ', $dejaExistants).'.';
            }
            Flash::set($message, 'success');
        } elseif (! empty($dejaExistants)) {
            Flash::set('Ce trajet est déjà programmé pour : '.implode(', ', $dejaExistants).'.', 'warning');
        }

        return redirect()->route('admin.programme.create', $user->isSuperAdmin() ? ['id_compagnie' => $idCompagnie] : []);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = Auth::guard('staff')->user();

        if ($user->estLectureSeule()) {
            Flash::set('Votre rôle est en lecture seule.', 'danger');

            return redirect()->route('admin.programme.index');
        }

        $idProgrammer = $request->input('idProgrammer');
        $prix = $request->input('prix');

        $programme = Programme::find($idProgrammer);
        if (! $programme) {
            Flash::set('Programme introuvable.', 'danger');

            return redirect()->route('admin.programme.index');
        }

        $programme->prix = $prix;

        $heureDepart = $request->input('heureDepart');
        $rdv = $request->input('rdv');
        if ($heureDepart && $rdv) {
            // Empêche de changer l'heure vers une heure déjà utilisée par un AUTRE trajet
            // identique (même départ/destination), pour ne pas créer de doublon en éditant.
            $doublon = Programme::where('idDepart', $programme->idDepart)
                ->where('idDestination', $programme->idDestination)
                ->where('heureDepart', $heureDepart)
                ->where('id_compagnie', $programme->id_compagnie)
                ->where('idProgrammer', '!=', $programme->idProgrammer)
                ->exists();

            if ($doublon) {
                Flash::set('Impossible de changer l\'heure : ce trajet existe déjà à cette heure-là. Modifiez plutôt ce trajet existant, ou choisissez une autre heure.', 'danger');

                return redirect()->route('admin.programme.index');
            }

            $programme->heureDepart = $heureDepart;
            $programme->rdv = $rdv;
        }

        $programme->save();

        $prixEscales = (array) $request->input('prix_escale', []);
        foreach ($prixEscales as $idEscale => $prixLigne) {
            LigneTrajet::where('id_trajets', $programme->idProgrammer)
                ->where('id_escales', $idEscale)
                ->where('type_trajet', 'programmer')
                ->update(['prix_escale' => (float) $prixLigne]);
        }

        Flash::set('Modification faite avec succès.', 'success');

        return redirect()->route('admin.programme.index');
    }

    public function destroy(int $idProgrammer): RedirectResponse
    {
        $user = Auth::guard('staff')->user();

        if ($user->estLectureSeule()) {
            Flash::set('Votre rôle est en lecture seule.', 'danger');

            return redirect()->route('admin.programme.index');
        }

        DB::transaction(function () use ($idProgrammer) {
            LigneTrajet::where('id_trajets', $idProgrammer)->where('type_trajet', 'programmer')->delete();
            DB::table('liaison_car_trajet')->where('id_trajets', $idProgrammer)->delete();
            Programme::where('idProgrammer', $idProgrammer)->delete();
        });

        Flash::set('Le programme a été supprimé avec succès.', 'success');

        return redirect()->route('admin.programme.index');
    }

    // RDV = heure de départ moins 45 minutes.
    private function calculerRdv(string $heureDepart): string
    {
        $parts = array_map('intval', explode(':', $heureDepart));
        $heures = $parts[0] ?? 0;
        $minutes = $parts[1] ?? 0;

        $minutes -= 45;
        if ($minutes < 0) {
            $minutes += 60;
            $heures--;
            if ($heures < 0) {
                $heures += 24;
            }
        }

        return sprintf('%02d:%02d', $heures, $minutes);
    }

    private function enregistrerEscales(int $idProgrammer, array $idEscales, array $prixEscales): void
    {
        foreach ($idEscales as $idEscale) {
            LigneTrajet::create([
                'id_escales' => $idEscale,
                'id_trajets' => $idProgrammer,
                'type_trajet' => 'programmer',
                'prix_escale' => isset($prixEscales[$idEscale]) ? (float) $prixEscales[$idEscale] : 0,
            ]);
        }
    }

    // Garantit qu'un trajet et son sens inverse existent toujours ensemble, pour qu'un
    // car ne se retrouve jamais bloqué sans destination valide au retour.
    private function assurerTrajetRetour($idDepart, $idDestination, $heureDepart, $rdv, $prix, $idCompagnie, array $idEscales, array $prixEscales): void
    {
        $existeDejaRetour = Programme::where('idDepart', $idDestination)
            ->where('idDestination', $idDepart)
            ->where('heureDepart', $heureDepart)
            ->where('id_compagnie', $idCompagnie)
            ->exists();

        if ($existeDejaRetour) {
            return;
        }

        $retour = Programme::create([
            'idDepart' => $idDestination,
            'idDestination' => $idDepart,
            'heureDepart' => $heureDepart,
            'rdv' => $rdv,
            'prix' => $prix,
            'id_compagnie' => $idCompagnie,
        ]);

        $this->enregistrerEscales($retour->idProgrammer, $idEscales, $prixEscales);
    }
}
