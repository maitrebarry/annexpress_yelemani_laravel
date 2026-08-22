<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * Port de Projets_licence/app/models/Home.php (méthodes utilisées par la vue home.view.php
 * uniquement) et de la branche 'jour' de Depense::getBenefice, pour la page d'accueil.
 */
class HomeStatsService
{
    public function getPlatformStats(): array
    {
        $today = now()->toDateString();

        return [
            'totalCompagnies' => DB::table('compagnie')->count(),
            'totalGares' => DB::table('agence')->count(),
            'totalUtilisateurs' => DB::table('utilisateur')->where('status', 1)->count(),
            'totalBilletsJour' => DB::table('billets')->where('date_reservation', $today)->count(),
        ];
    }

    public function getCompagniesOverview(): array
    {
        $today = now()->toDateString();
        $debutMois = now()->startOfMonth()->toDateString();
        $finMois = now()->endOfMonth()->toDateString();

        return DB::table('compagnie as c')
            ->selectRaw('c.id_compagnie, c.nom_compagnie')
            ->selectRaw('(SELECT COUNT(*) FROM agence a WHERE a.id_compagnie = c.id_compagnie) AS nb_gares')
            ->selectRaw('(SELECT COUNT(*) FROM utilisateur u WHERE u.id_compagnie = c.id_compagnie AND u.status = 1) AS nb_utilisateurs')
            ->selectRaw('(SELECT COUNT(*) FROM billets b WHERE b.id_compagnie = c.id_compagnie AND b.date_reservation = ?) AS billets_jour', [$today])
            ->selectRaw('(SELECT COUNT(*) FROM colis co WHERE co.id_compagnie = c.id_compagnie AND DATE(co.date_enregistrement) BETWEEN ? AND ?) AS colis_mois', [$debutMois, $finMois])
            ->orderBy('c.nom_compagnie')
            ->get()
            ->map(fn ($row) => (array) $row)
            ->all();
    }

    public function getGaresCompagnie(int $idCompagnie)
    {
        return DB::table('agence')
            ->select('idAgence', 'localite', 'numeroGare')
            ->where('id_compagnie', $idCompagnie)
            ->orderBy('localite')
            ->get();
    }

    public function getBilletsJournalier(string $droit, int $idCompagnie, ?string $ville, ?int $idUser, ?string $gareVille, string $date): array
    {
        $query = DB::table('billets')
            ->where('date_reservation', $date)
            ->where('id_compagnie', $idCompagnie);

        if ($droit === 'chef_d_escale') {
            $query->where('departId', $ville);
        } elseif ($droit === 'Utilisateur') {
            $query->where('idUser', $idUser);
        } elseif ($droit === 'Admin' && $gareVille) {
            $query->where('departId', $gareVille);
        }

        $row = $query->selectRaw("
            SUM(CASE WHEN status_reservation = 'presentiel' THEN 1 ELSE 0 END) AS total_presentiel,
            SUM(CASE WHEN status_reservation = 'en_ligne' AND validation_billets = 'valider' THEN 1 ELSE 0 END) AS total_en_ligne,
            SUM(CASE WHEN status_reservation = 'en_ligne' AND validation_billets = 'en_attente' THEN 1 ELSE 0 END) AS total_en_attente
        ")->first();

        return [
            'presentiel' => (int) ($row->total_presentiel ?? 0),
            'en_ligne' => (int) ($row->total_en_ligne ?? 0),
            'en_attente' => (int) ($row->total_en_attente ?? 0),
        ];
    }

    public function getVoyagesProgrammes(string $droit, int $idCompagnie, ?string $ville, ?int $idAgence, ?string $gareVille, ?int $gareId, string $date): int
    {
        $query = DB::table('programmation_voyage')
            ->where('date_enregistre', $date)
            ->where('id_compagnie', $idCompagnie);

        if (in_array($droit, ['chef_d_escale', 'Utilisateur'], true)) {
            $query->where('localite_user', $ville)->where('id_agence', $idAgence);
        } elseif ($droit === 'Admin' && $gareVille) {
            $query->where('localite_user', $gareVille);
            if ($gareId) {
                $query->where('id_agence', $gareId);
            }
        }

        return (int) $query->count();
    }

    public function getColisJournalier(string $droit, int $idCompagnie, ?string $ville, ?string $numGare, ?string $gareVille, string $date): array
    {
        $query = DB::table('colis')
            ->join('agence', 'colis.id_agence', '=', 'agence.idAgence')
            ->where('colis.id_compagnie', $idCompagnie)
            ->whereDate('colis.date_enregistrement', $date);

        if ($droit === 'Utilisateur') {
            $query->where(function ($q) use ($ville, $numGare) {
                $q->where(function ($q2) use ($ville, $numGare) {
                    $q2->where('colis.status', '<>', 'enregistre')
                        ->where('agence.localite', $ville)
                        ->where('agence.numeroGare', $numGare);
                })->orWhere(function ($q2) use ($ville, $numGare) {
                    $q2->where('colis.status', 'enregistre')
                        ->where('colis.provient_de', $ville)
                        ->where('colis.num_gare', $numGare);
                });
            });
        } elseif ($droit === 'chef_d_escale') {
            $query->where(function ($q) use ($ville) {
                $q->where(function ($q2) use ($ville) {
                    $q2->where('colis.status', '<>', 'enregistre')->where('agence.localite', $ville);
                })->orWhere(function ($q2) use ($ville) {
                    $q2->where('colis.status', 'enregistre')->where('colis.provient_de', $ville);
                });
            });
        } elseif ($droit === 'Admin' && $gareVille) {
            $query->where(function ($q) use ($gareVille) {
                $q->where(function ($q2) use ($gareVille) {
                    $q2->where('colis.status', '<>', 'enregistre')->where('agence.localite', $gareVille);
                })->orWhere(function ($q2) use ($gareVille) {
                    $q2->where('colis.status', 'enregistre')->where('colis.provient_de', $gareVille);
                });
            });
        }

        $results = $query->groupBy('colis.status')
            ->selectRaw('colis.status, COUNT(*) as total')
            ->pluck('total', 'status');

        return [
            'prise_en_charge' => (int) ($results['enregistre'] ?? 0),
            'en_cours' => (int) ($results['en_cours'] ?? 0),
            'recu' => (int) ($results['recu'] ?? 0),
            'livre' => (int) ($results['livre'] ?? 0),
            'attente' => (int) ($results['attente'] ?? 0),
        ];
    }

    public function getTopGares(int $idCompagnie): array
    {
        $debutMois = now()->startOfMonth()->toDateString();
        $finMois = now()->endOfMonth()->toDateString();

        return DB::table('billets')
            ->where('id_compagnie', $idCompagnie)
            ->whereBetween('date_reservation', [$debutMois, $finMois])
            ->groupBy('departId')
            ->orderByDesc('total_billets')
            ->limit(5)
            ->selectRaw('departId as gare, COUNT(*) as total_billets, SUM(nombrePassages) as total_passagers')
            ->get()
            ->map(fn ($row) => (array) $row)
            ->all();
    }

    public function getCaisseGare(int $idAgence): ?object
    {
        $caisse = DB::table('caisse')
            ->where('id_agence', $idAgence)
            ->where('status_caisse', 1)
            ->first();

        if (! $caisse) {
            return null;
        }

        $caisse->solde = ($caisse->montant_billets ?? 0) + ($caisse->montant_colis ?? 0)
            - ($caisse->montant_rembourse ?? 0) - ($caisse->montant_depense ?? 0);

        return $caisse;
    }

    /**
     * Port de Homes::home() (bloc "Cars destinés à la gare du chef d'escale" — en transit).
     * Un car n'apparaît ici que s'il a réellement décollé (decolle_le rempli sur sa
     * programmation active vers cette ville), pas dès la simple programmation du voyage —
     * même condition que ProgrammationVoyageController::carsEnTransit().
     */
    public function getCarsEnTransitVersGare(int $idCompagnie, string $ville)
    {
        return DB::table('car')
            ->where('car.status_car', 'En_transit_'.$ville)
            ->where('car.id_compagnie', $idCompagnie)
            ->whereExists(function ($q) use ($ville) {
                $q->select(DB::raw(1))
                    ->from('programmation_voyage as pv')
                    ->whereColumn('pv.id_car_programmer', 'car.id_car')
                    ->where('pv.id_trajet', $ville)
                    ->where('pv.statut', 'active')
                    ->whereNotNull('pv.decolle_le');
            })
            ->get(['car.id_car', 'car.numero_car', 'car.nbr_place'])
            ->map(function ($car) use ($ville) {
                $prog = DB::table('programmation_voyage')
                    ->where('id_car_programmer', $car->id_car)
                    ->where('id_trajet', $ville)
                    ->where('statut', 'active')
                    ->orderByDesc('date_enregistre')->orderByDesc('id_programmation')
                    ->first(['localite_user', 'id_horaire']);

                $car->provenance = $prog->localite_user ?? null;
                $car->id_horaire = $prog->id_horaire ?? null;

                return $car;
            });
    }

    /**
     * Port de Programmation_voyage::getCarsProgrammesVersMaGare() : cars affectés à un
     * trajet vers cette gare aujourd'hui mais pas encore décollés — visibilité directe sur
     * la page d'accueil du chef d'escale, avant même que le car n'apparaisse "en transit".
     */
    public function getCarsProgrammesVersGare(int $idCompagnie, string $ville)
    {
        return DB::table('programmation_voyage as pv')
            ->join('car', 'car.id_car', '=', 'pv.id_car_programmer')
            ->where('pv.id_trajet', $ville)
            ->where('pv.id_compagnie', $idCompagnie)
            ->where('pv.statut', 'active')
            ->whereNull('pv.decolle_le')
            ->where('pv.date_enregistre', now()->toDateString())
            ->orderBy('pv.id_horaire')
            ->get(['car.id_car', 'car.numero_car', 'car.nbr_place', 'pv.id_horaire', 'pv.localite_user']);
    }

    public function getActiviteRecente(string $droit, int $idCompagnie, ?string $ville, ?int $idUser, ?string $profile, ?string $gareVille, int $limit = 6): array
    {
        $activites = [];

        $inclureBillets = $droit !== 'Utilisateur' || $profile === 'billet';
        $inclureColis = $droit !== 'Utilisateur' || $profile === 'colis';

        if ($inclureBillets) {
            $query = DB::table('billets')->where('id_compagnie', $idCompagnie);

            if ($droit === 'chef_d_escale') {
                $query->where('departId', $ville);
            } elseif ($droit === 'Utilisateur') {
                $query->where('idUser', $idUser);
            } elseif ($droit === 'Admin' && $gareVille) {
                $query->where('departId', $gareVille);
            }

            $rows = $query->orderByDesc('date_reservation')->orderByDesc('idBillets')
                ->limit(3)
                ->select('numeroBillets', 'departId', 'destinationId', 'date_reservation')
                ->get();

            foreach ($rows as $row) {
                $activites[] = [
                    'type' => 'billet',
                    'titre' => 'Billet vendu',
                    'detail' => 'Billet #'.$row->numeroBillets.' — '.$row->departId.' → '.$row->destinationId,
                    'date' => $row->date_reservation,
                ];
            }
        }

        if ($inclureColis) {
            $query = DB::table('colis')
                ->join('agence', 'colis.id_agence', '=', 'agence.idAgence')
                ->where('colis.id_compagnie', $idCompagnie);

            if ($droit === 'chef_d_escale') {
                $query->where('agence.localite', $ville);
            } elseif ($droit === 'Utilisateur') {
                $query->where('colis.id_utilisateur', $idUser);
            } elseif ($droit === 'Admin' && $gareVille) {
                $query->where('agence.localite', $gareVille);
            }

            $rows = $query->orderByDesc('colis.date_enregistrement')->orderByDesc('colis.id_colis')
                ->limit(3)
                ->select('colis.code_colis', 'colis.status', 'colis.date_enregistrement', 'agence.localite')
                ->get();

            foreach ($rows as $row) {
                $activites[] = [
                    'type' => 'colis',
                    'titre' => 'Colis '.str_replace('_', ' ', $row->status),
                    'detail' => 'Colis #'.$row->code_colis.' — '.$row->localite,
                    'date' => $row->date_enregistrement,
                ];
            }
        }

        usort($activites, fn ($a, $b) => strcmp($b['date'] ?? '', $a['date'] ?? ''));

        return array_slice($activites, 0, $limit);
    }

    public function getBeneficeJour(int $idCompagnie, ?string $gareVille, string $date): array
    {
        $totalBillets = (float) (DB::table('billets as b')
            ->join('client as c', 'b.id_client', '=', 'c.idClient')
            ->where('b.id_compagnie', $idCompagnie)
            ->where('b.validation_billets', 'valider')
            ->where(function ($q) {
                $q->whereNull('b.status_billets')->orWhere('b.status_billets', '!=', 'annule');
            })
            ->where('b.date_reservation', $date)
            ->when($gareVille, fn ($q) => $q->where('b.departId', $gareVille))
            ->selectRaw("SUM(CAST(REPLACE(REPLACE(c.montant_payer, ' ', ''), 'FCFA', '') AS DECIMAL(12,2))) as total")
            ->value('total') ?? 0);

        $totalColis = (float) (DB::table('colis')
            ->join('agence', 'colis.id_agence', '=', 'agence.idAgence')
            ->where('colis.id_compagnie', $idCompagnie)
            ->whereDate('colis.date_enregistrement', $date)
            ->when($gareVille, fn ($q) => $q->where('agence.localite', $gareVille))
            ->sum('colis.fraix_transaction') ?? 0);

        $totalRembourse = (float) (DB::table('caisse as c')
            ->join('agence as a', 'c.id_agence', '=', 'a.idAgence')
            ->where('a.id_compagnie', $idCompagnie)
            ->when($gareVille, fn ($q) => $q->where('a.localite', $gareVille))
            ->sum('c.montant_rembourse') ?? 0);

        $totalDepenseLocale = (float) (DB::table('depense')
            ->join('agence as a', 'depense.id_agence', '=', 'a.idAgence')
            ->where('depense.id_compagnie', $idCompagnie)
            ->where('depense.statut', 'valide')
            ->whereDate('depense.date_depense', $date)
            ->when($gareVille, fn ($q) => $q->where('a.localite', $gareVille))
            ->sum('depense.montant') ?? 0);

        $totalDepenseGlobale = 0.0;
        if (! $gareVille) {
            $totalDepenseGlobale = (float) (DB::table('depense')
                ->where('id_compagnie', $idCompagnie)
                ->whereNull('id_agence')
                ->where('statut', 'valide')
                ->whereDate('date_depense', $date)
                ->sum('montant') ?? 0);
        }

        $totalLocation = (float) (DB::table('location_car as l')
            ->join('agence as a', 'l.id_agence_depart', '=', 'a.idAgence')
            ->where('l.id_compagnie', $idCompagnie)
            ->where('l.statut', 'valide')
            ->whereDate('l.date_depart', $date)
            ->when($gareVille, fn ($q) => $q->where('a.localite', $gareVille))
            ->sum('l.frais_location') ?? 0);

        $benefice = $totalBillets + $totalColis + $totalLocation - $totalRembourse - $totalDepenseLocale - $totalDepenseGlobale;

        return [
            'revenus_billets' => $totalBillets,
            'revenus_colis' => $totalColis,
            'revenus_location' => $totalLocation,
            'remboursements' => $totalRembourse,
            'depenses_locales' => $totalDepenseLocale,
            'depenses_globales' => $totalDepenseGlobale,
            'benefice' => $benefice,
        ];
    }
}
