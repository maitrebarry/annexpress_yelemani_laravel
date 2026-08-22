<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Port de Projets_licence/app/models/Rapport_billet.php (contrôleur legacy
 * Rapport_billets.php). Toutes les requêtes sont fidèles au legacy, y compris
 * l'expression `REPLACE/CAST` sur `client.montant_payer` (colonne string, potentiellement
 * suffixée "FCFA" sur des données historiques même si ce port n'y écrit que des nombres).
 */
class RapportBilletService
{
    private const MONTANT_PAYER_DECIMAL = "CAST(REPLACE(REPLACE(client.montant_payer, ' ', ''), 'FCFA', '') AS DECIMAL(12,2))";

    public function getTotalParType(int $idCompagnie, string $type): int
    {
        if ($type === 'repporte') {
            return (int) DB::table('billets')
                ->whereNotNull('date_repporte')
                ->where('id_compagnie', $idCompagnie)
                ->count();
        }

        return (int) DB::table('billets')
            ->where('status_reservation', $type)
            ->where('id_compagnie', $idCompagnie)
            ->count();
    }

    public function getParMois(int $idCompagnie): Collection
    {
        return DB::table('billets')
            ->selectRaw("DATE_FORMAT(IF(date_repporte IS NOT NULL, date_repporte, date_reservation), '%Y-%m') AS mois")
            ->selectRaw("CASE WHEN date_repporte IS NOT NULL THEN 'repporte' ELSE status_reservation END AS type_reservation")
            ->selectRaw('COUNT(*) AS total')
            ->where('id_compagnie', $idCompagnie)
            ->groupBy('mois', 'type_reservation')
            ->orderBy('mois')
            ->get();
    }

    public function getTotalParTypeAnnuel(int $idCompagnie, string $type, int $annee): int
    {
        if ($type === 'repporte') {
            return (int) DB::table('billets')
                ->whereNotNull('date_repporte')
                ->where('id_compagnie', $idCompagnie)
                ->whereRaw('YEAR(date_repporte) = ?', [$annee])
                ->count();
        }

        return (int) DB::table('billets')
            ->where('status_reservation', $type)
            ->where('id_compagnie', $idCompagnie)
            ->whereRaw('YEAR(date_reservation) = ?', [$annee])
            ->count();
    }

    public function getParAnnee(int $idCompagnie, int $annee): Collection
    {
        return DB::table('billets')
            ->selectRaw('YEAR(IF(date_repporte IS NOT NULL, date_repporte, date_reservation)) AS annee')
            ->selectRaw("CASE WHEN date_repporte IS NOT NULL THEN 'repporte' ELSE status_reservation END AS type_reservation")
            ->selectRaw('COUNT(*) AS total')
            ->where('id_compagnie', $idCompagnie)
            ->whereRaw('YEAR(IF(date_repporte IS NOT NULL, date_repporte, date_reservation)) = ?', [$annee])
            ->groupBy('annee', 'type_reservation')
            ->orderBy('annee')
            ->get();
    }

    public function getSommeBilletsParLocaliteEtGare(int $idCompagnie, ?string $localite = null, ?string $mois = null): Collection
    {
        $query = DB::table('billets')
            ->join('client', 'billets.id_client', '=', 'client.idClient')
            ->selectRaw('billets.departId AS localite, billets.num_gare, billets.status_reservation')
            ->selectRaw('SUM('.self::MONTANT_PAYER_DECIMAL.') AS total')
            ->where('billets.id_compagnie', $idCompagnie)
            ->whereIn('billets.status_reservation', ['presentiel', 'en_ligne'])
            ->where(function ($q) {
                $q->whereNull('billets.status_billets')->orWhere('billets.status_billets', '!=', 'annule');
            });

        if ($mois !== null) {
            $query->whereRaw("DATE_FORMAT(billets.date_reservation, '%Y-%m') = ?", [$mois]);
        }

        if ($localite !== null) {
            $query->where('billets.departId', $localite);
        }

        return $query->groupBy('billets.departId', 'billets.num_gare', 'billets.status_reservation')
            ->orderBy('billets.departId')
            ->orderBy('billets.num_gare')
            ->get();
    }

    public function getSommeBilletsParLocaliteEtGareAnnuel(int $idCompagnie, ?string $localite = null, ?int $annee = null): Collection
    {
        $query = DB::table('billets')
            ->join('client', 'billets.id_client', '=', 'client.idClient')
            ->selectRaw('billets.departId AS localite, billets.num_gare, billets.status_reservation')
            ->selectRaw('SUM('.self::MONTANT_PAYER_DECIMAL.') AS total')
            ->where('billets.id_compagnie', $idCompagnie)
            ->whereIn('billets.status_reservation', ['presentiel', 'en_ligne'])
            ->where(function ($q) {
                $q->whereNull('billets.status_billets')->orWhere('billets.status_billets', '!=', 'annule');
            });

        if ($annee !== null) {
            $query->whereRaw('YEAR(billets.date_reservation) = ?', [$annee]);
        }

        if ($localite !== null) {
            $query->where('billets.departId', $localite);
        }

        return $query->groupBy('billets.departId', 'billets.num_gare', 'billets.status_reservation')
            ->orderBy('billets.departId')
            ->orderBy('billets.num_gare')
            ->get();
    }
}
