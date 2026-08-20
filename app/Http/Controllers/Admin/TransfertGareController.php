<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProgrammationVoyage;
use App\Models\TransfertGare;
use App\Support\Flash;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Port de Projets_licence/app/controllers/admin/Transferts_gares.php +
 * app/models/Transfert_gare.php — écran "Transferts" du menu G-programme.
 *
 * Transfert de passagers entre deux gares d'une même localité : quand le car
 * d'une gare n'est pas complet, ses passagers réservés peuvent être consolidés
 * sur le car d'une gare voisine (même destination/heure/jour) plutôt que de
 * faire partir deux cars à moitié vides. Tout ou rien : le transfert n'a lieu
 * que si la gare cible peut accueillir la totalité des passagers de la source.
 *
 * candidats()/executer() sont invoqués depuis la modale "Transférer les
 * passagers" de la liste "Trajets programmés" (admin.programmation-voyage.liste-journaliere).
 */
class TransfertGareController extends Controller
{
    // AJAX : gares compatibles (même destination/heure/jour/localité) pour la programmation
    // donnée, avec pour chacune un aperçu du transfert (montant, passagers, places).
    public function candidats(int $idProgrammation): JsonResponse
    {
        $user = Auth::guard('staff')->user();

        if (! in_array($user->droit, ['chef_d_escale', 'Admin', 'super_admin'], true)) {
            return response()->json(['error' => 'Accès refusé.']);
        }

        $source = $this->programmationAvecAgence($idProgrammation, $user->id_compagnie);
        if (! $source || $source->statut !== 'active') {
            return response()->json(['gares' => []]);
        }

        // Un chef d'escale ne peut consulter les alternatives que pour sa propre gare, jamais
        // pour le compte d'une autre (même si l'id_programmation posté est valide).
        if ($user->droit === 'chef_d_escale' && (int) $source->id_agence !== (int) ($user->id_agence ?? 0)) {
            return response()->json(['gares' => []]);
        }

        $gares = DB::table('programmation_voyage as pv2')
            ->join('agence as a2', 'pv2.id_agence', '=', 'a2.idAgence')
            ->join('car as c', 'c.id_car', '=', 'pv2.id_car_programmer')
            ->where('pv2.id_horaire', $source->id_horaire)->where('pv2.id_trajet', $source->id_trajet)
            ->where('pv2.date_enregistre', $source->date_enregistre)->where('pv2.id_compagnie', $user->id_compagnie)
            ->where('pv2.statut', 'active')->where('a2.localite', $source->localite)
            ->where('a2.idAgence', '!=', $source->id_agence)
            ->orderBy('a2.numeroGare')
            ->get(['pv2.id_programmation', 'a2.idAgence', 'a2.numeroGare', 'c.nbr_place', 'c.nbr_place_reserve', DB::raw('(c.nbr_place - c.nbr_place_reserve) AS places_libres')]);

        $resultats = $gares->map(fn ($g) => [
            'id_programmation' => $g->id_programmation,
            'numeroGare' => $g->numeroGare,
            'places_libres' => (int) $g->places_libres,
            'apercu' => $this->apercu($idProgrammation, $g->id_programmation, $user->id_compagnie),
        ]);

        return response()->json(['gares' => $resultats]);
    }

    // Exécute le transfert : tout ou rien, transactionnel, verrouille les deux cars et les
    // billets concernés pour empêcher toute vente/annulation concurrente de fausser les compteurs.
    public function executer(Request $request): RedirectResponse
    {
        $user = Auth::guard('staff')->user();
        $idProgrammationSource = $request->input('id_programmation_source');
        $idProgrammationDestination = $request->input('id_programmation_destination');

        if (! in_array($user->droit, ['chef_d_escale', 'Admin', 'super_admin'], true)) {
            Flash::set('Accès refusé.', 'danger');

            return redirect()->route('admin.programmation-voyage.liste-journaliere');
        }
        if ($idProgrammationSource == $idProgrammationDestination) {
            Flash::set('Impossible de transférer une gare vers elle-même.', 'danger');

            return redirect()->route('admin.programmation-voyage.liste-journaliere');
        }

        $idCompagnie = $user->id_compagnie;

        try {
            DB::transaction(function () use ($idProgrammationSource, $idProgrammationDestination, $idCompagnie, $user) {
                $source = DB::table('programmation_voyage as pv')
                    ->join('car as c', 'c.id_car', '=', 'pv.id_car_programmer')
                    ->join('agence as a', 'a.idAgence', '=', 'pv.id_agence')
                    ->where('pv.id_programmation', $idProgrammationSource)->where('pv.id_compagnie', $idCompagnie)
                    ->lockForUpdate()
                    ->first(['pv.*', 'c.id_car', 'c.status_car', 'c.nbr_place', 'c.nbr_place_reserve', 'a.localite', 'a.numeroGare']);

                if (! $source) {
                    throw new \RuntimeException('flash:Programmation source introuvable.');
                }
                if ($source->statut !== 'active') {
                    throw new \RuntimeException("flash:Ce voyage est déjà annulé ou a déjà fait l'objet d'un transfert.");
                }
                // Basé sur decolle_le (positionné uniquement une fois le bus réellement parti),
                // pas sur car.status_car ('En_transit_...', positionné dès la programmation —
                // bien avant le départ réel, ce qui bloquerait des transferts trop tôt).
                if (! empty($source->decolle_le)) {
                    throw new \RuntimeException('flash:Ce car est déjà parti : impossible de transférer ses passagers.');
                }
                // Un chef d'escale ne peut transférer que les passagers de sa propre gare (IDOR).
                if ($user->droit === 'chef_d_escale' && (int) $source->id_agence !== (int) ($user->id_agence ?? 0)) {
                    throw new \RuntimeException('flash:Vous ne pouvez transférer que les passagers de votre propre gare.');
                }

                // Destination revalidée intégralement côté serveur (même créneau exact, même
                // localité, gare différente, voyage actif) : jamais faire confiance au client.
                $dest = DB::table('programmation_voyage as pv')
                    ->join('car as c', 'c.id_car', '=', 'pv.id_car_programmer')
                    ->join('agence as a', 'a.idAgence', '=', 'pv.id_agence')
                    ->where('pv.id_programmation', $idProgrammationDestination)->where('pv.id_compagnie', $idCompagnie)
                    ->lockForUpdate()
                    ->first(['pv.*', 'c.id_car', 'c.status_car', 'c.nbr_place', 'c.nbr_place_reserve', 'a.localite', 'a.numeroGare']);

                $compatible = $dest
                    && $dest->statut === 'active'
                    && (int) $dest->id_agence !== (int) $source->id_agence
                    && $dest->id_horaire === $source->id_horaire
                    && $dest->id_trajet === $source->id_trajet
                    && $dest->date_enregistre === $source->date_enregistre
                    && $dest->localite === $source->localite;

                if (! $compatible) {
                    throw new \RuntimeException("flash:Cette gare n'est plus une cible valide pour ce transfert.");
                }

                $billets = DB::table('billets as b')->join('client as cl', 'cl.idClient', '=', 'b.id_client')
                    ->where('b.jourVoyage', $source->date_enregistre)->where('b.Heur_departs', $source->id_horaire)
                    ->where('b.departId', $source->localite)->where('b.num_gare', $source->numeroGare)
                    ->where('b.id_compagnie', $idCompagnie)
                    ->whereIn('b.destinationId', ProgrammationVoyage::destinationsPourCreneau($source->id_horaire, $source->id_trajet, $source->localite, $idCompagnie, $source->id_agence))
                    ->where(fn ($q) => $q->whereNull('b.status_billets')->orWhere('b.status_billets', '!=', 'annule'))
                    ->lockForUpdate()
                    ->get(['b.idBillets', 'b.nombrePassages', DB::raw("CAST(REPLACE(REPLACE(cl.montant_payer, ' ', ''), 'FCFA', '') AS DECIMAL(12,2)) AS montant_payer")]);

                $totalPassagers = (int) $billets->sum('nombrePassages');
                $totalMontant = (float) $billets->sum('montant_payer');

                if ($totalPassagers === 0) {
                    throw new \RuntimeException('flash:Aucun passager à transférer sur cette gare.');
                }

                // Tout ou rien : le transfert n'a lieu que si la gare cible peut accueillir la
                // totalité des passagers de la source (objectif : économiser un car, pas
                // déplacer une partie seulement).
                $placesLibresDest = (int) $dest->nbr_place - (int) $dest->nbr_place_reserve;
                if ($totalPassagers > $placesLibresDest) {
                    throw new \RuntimeException("flash:Transfert impossible : places insuffisantes à la gare cible ($placesLibresDest restantes pour $totalPassagers passagers).");
                }

                // Une caisse ouverte est indispensable des deux côtés : la recette doit toujours
                // avoir une caisse d'origine à débiter et une caisse d'accueil à créditer.
                $caisseSource = DB::table('caisse')->where('id_agence', $source->id_agence)->where('status_caisse', 1)->first();
                if (! $caisseSource) {
                    throw new \RuntimeException('flash:Transfert impossible : aucune caisse ouverte pour votre gare.');
                }
                $caisseDest = DB::table('caisse')->where('id_agence', $dest->id_agence)->where('status_caisse', 1)->first();
                if (! $caisseDest) {
                    throw new \RuntimeException('flash:Transfert impossible : aucune caisse ouverte à la gare cible pour recevoir la recette.');
                }

                $transfert = TransfertGare::create([
                    'id_compagnie' => $idCompagnie,
                    'id_agence_source' => $source->id_agence,
                    'id_agence_destination' => $dest->id_agence,
                    'id_programmation_source' => $idProgrammationSource,
                    'id_programmation_destination' => $idProgrammationDestination,
                    'id_caisse_source' => $caisseSource->id_caisse,
                    'id_caisse_destination' => $caisseDest->id_caisse,
                    'nombre_billets' => $billets->count(),
                    'nombre_passagers' => $totalPassagers,
                    'montant_total' => $totalMontant,
                    'id_utilisateur' => $user->idUser,
                ]);

                // Déplacement des billets : gare + numéro de place recalculé par rapport aux
                // places déjà occupées côté destination.
                $prochainePlace = (int) $dest->nbr_place_reserve;
                foreach ($billets as $b) {
                    $start = $prochainePlace + 1;
                    $end = $start + (int) $b->nombrePassages - 1;
                    $numPlace = ((int) $b->nombrePassages === 1) ? "$start" : "$start-$end";
                    $prochainePlace = $end;

                    DB::table('billets')->where('idBillets', $b->idBillets)
                        ->update(['num_gare' => $dest->numeroGare, 'numeroPlace' => $numPlace]);

                    DB::table('transfert_billets')->insert([
                        'id_transfert' => $transfert->id_transfert,
                        'idBillets' => $b->idBillets,
                        'ancien_num_gare' => $source->numeroGare,
                        'nouveau_num_gare' => $dest->numeroGare,
                    ]);
                }

                // Compteurs de places : tout part du côté destination, plus rien côté source
                // (la totalité des passagers a été transférée).
                DB::table('car')->where('id_car', $dest->id_car)->increment('nbr_place_reserve', $totalPassagers);
                DB::table('car')->where('id_car', $source->id_car)->update(['nbr_place_reserve' => 0, 'status_car' => $source->localite_user]);

                // Le voyage de la gare source est annulé : aucun car ne part de son côté.
                DB::table('programmation_voyage')->where('id_programmation', $idProgrammationSource)->update(['statut' => 'annulee']);

                // Mouvement de caisse source -> destination, jamais négatif côté source.
                DB::table('caisse')->where('id_caisse', $caisseSource->id_caisse)
                    ->update(['montant_billets' => DB::raw('GREATEST(0, montant_billets - '.(float) $totalMontant.')')]);
                DB::table('caisse')->where('id_caisse', $caisseDest->id_caisse)
                    ->update(['montant_billets' => DB::raw('montant_billets + '.(float) $totalMontant)]);

                Flash::set(
                    "Transfert effectué : $totalPassagers passager(s) et ".number_format($totalMontant, 0, ',', ' ')." FCFA déplacés vers la gare {$dest->numeroGare}.",
                    'success'
                );
            });
        } catch (\RuntimeException $e) {
            $message = str_starts_with($e->getMessage(), 'flash:') ? substr($e->getMessage(), 6) : 'Erreur lors du transfert.';
            Flash::set($message, 'danger');
        }

        return redirect()->route('admin.programmation-voyage.liste-journaliere');
    }

    // Historique des transferts déjà effectués (traçabilité permanente).
    public function historique(): View|RedirectResponse
    {
        $user = Auth::guard('staff')->user();

        if (! in_array($user->droit, ['chef_d_escale', 'Admin', 'super_admin', 'PDG'], true)) {
            Flash::set('Accès refusé.', 'danger');

            return redirect()->route('admin.home');
        }

        $liste = TransfertGare::with(['agenceSource', 'agenceDestination', 'agent'])
            ->where('id_compagnie', $user->id_compagnie)
            ->when($user->droit === 'chef_d_escale', fn ($q) => $q->where(
                fn ($q2) => $q2->where('id_agence_source', $user->id_agence)->orWhere('id_agence_destination', $user->id_agence)
            ))
            ->orderByDesc('date_transfert')
            ->get();

        return view('admin.transfert-gare.historique', ['liste' => $liste]);
    }

    private function programmationAvecAgence(int $idProgrammation, ?int $idCompagnie): ?object
    {
        return DB::table('programmation_voyage as pv')
            ->join('agence as a', 'a.idAgence', '=', 'pv.id_agence')
            ->where('pv.id_programmation', $idProgrammation)->where('pv.id_compagnie', $idCompagnie)
            ->first(['pv.id_horaire', 'pv.id_trajet', 'pv.date_enregistre', 'pv.id_agence', 'pv.statut', 'a.localite', 'a.numeroGare']);
    }

    private function apercu(int $idProgrammationSource, int $idProgrammationDestination, ?int $idCompagnie): array|false
    {
        $source = $this->programmationAvecAgence($idProgrammationSource, $idCompagnie);
        $dest = DB::table('programmation_voyage as pv')
            ->join('agence as a', 'a.idAgence', '=', 'pv.id_agence')
            ->join('car as c', 'c.id_car', '=', 'pv.id_car_programmer')
            ->where('pv.id_programmation', $idProgrammationDestination)->where('pv.id_compagnie', $idCompagnie)
            ->first(['pv.id_agence', 'a.numeroGare', 'c.nbr_place', 'c.nbr_place_reserve']);

        if (! $source || ! $dest) {
            return false;
        }

        $destinations = ProgrammationVoyage::destinationsPourCreneau($source->id_horaire, $source->id_trajet, $source->localite, $idCompagnie, $source->id_agence);

        $billets = DB::table('billets as b')->join('client as cl', 'cl.idClient', '=', 'b.id_client')
            ->where('b.jourVoyage', $source->date_enregistre)->where('b.Heur_departs', $source->id_horaire)
            ->where('b.departId', $source->localite)->where('b.num_gare', $source->numeroGare)
            ->where('b.id_compagnie', $idCompagnie)->whereIn('b.destinationId', $destinations)
            ->where(fn ($q) => $q->whereNull('b.status_billets')->orWhere('b.status_billets', '!=', 'annule'))
            ->get(['b.nombrePassages', DB::raw("CAST(REPLACE(REPLACE(cl.montant_payer, ' ', ''), 'FCFA', '') AS DECIMAL(12,2)) AS montant_payer")]);

        $totalPassagers = (int) $billets->sum('nombrePassages');
        $totalMontant = (float) $billets->sum('montant_payer');
        $placesLibresDest = (int) $dest->nbr_place - (int) $dest->nbr_place_reserve;

        return [
            'nombre_billets' => $billets->count(),
            'nombre_passagers' => $totalPassagers,
            'montant_total' => $totalMontant,
            'places_libres_dest' => $placesLibresDest,
            'numero_gare_dest' => $dest->numeroGare,
            'possible' => $totalPassagers > 0 && $totalPassagers <= $placesLibresDest,
        ];
    }
}
