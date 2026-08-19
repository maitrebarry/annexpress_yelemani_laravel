<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * Compteurs/alertes affichés dans la sidebar et le navbar (partagés par toutes les pages
 * back-office). Port de Projets_licence/app/models/Liste_du_jour.php (getCarsComplets,
 * getDemandesReportEnAttente(Chef), getBilletsEnRetard) et Partenaire::countEnAttenteReponse.
 */
class NavAlertsService
{
    public function getCarsComplets(?string $idDepart, ?string $numeroGare, ?int $idCompagnie, ?string $jour = null): array
    {
        if (! $idCompagnie) {
            return [];
        }

        $jour ??= date('Y-m-d');
        date_default_timezone_set('Africa/Bamako');
        $maintenant = date('Y-m-d H:i:s');

        $idAgence = null;
        if ($idDepart !== null && $numeroGare !== null) {
            $agence = DB::table('agence')
                ->where('localite', $idDepart)
                ->where('numeroGare', $numeroGare)
                ->where('id_compagnie', $idCompagnie)
                ->first();
            $idAgence = $agence->idAgence ?? null;
        }

        $query = DB::table('programmation_voyage as pv')
            ->join('car as c', 'c.id_car', '=', 'pv.id_car_programmer')
            ->where('pv.date_enregistre', $jour)
            ->where('pv.id_compagnie', $idCompagnie)
            ->where('pv.statut', 'active')
            ->where('c.nbr_place', '>', 0)
            ->whereColumn('c.nbr_place_reserve', '>=', 'c.nbr_place')
            ->whereRaw('TIMESTAMP(pv.date_enregistre, pv.id_horaire) >= ?', [$maintenant])
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('billets as b')
                    ->whereColumn('b.jourVoyage', 'pv.date_enregistre')
                    ->whereColumn('b.Heur_departs', 'pv.id_horaire')
                    ->whereColumn('b.destinationId', 'pv.id_trajet')
                    ->whereColumn('b.departId', 'pv.localite_user')
                    ->whereColumn('b.id_compagnie', 'pv.id_compagnie')
                    ->where(function ($q2) {
                        $q2->whereNull('b.status_billets')->orWhere('b.status_billets', '');
                    })
                    ->where(function ($q2) {
                        $q2->whereNull('b.statut_embarquement')->orWhere('b.statut_embarquement', '!=', 'embarque');
                    });
            });

        if ($idAgence !== null) {
            $query->where('pv.id_agence', $idAgence);
        } elseif ($idDepart !== null) {
            $query->where('pv.localite_user', $idDepart);
        }

        return $query->orderBy('pv.id_horaire')
            ->select('pv.id_trajet as destination', 'pv.id_horaire as heure', 'pv.localite_user as depart', 'c.numero_car', 'c.matriculle', 'c.nbr_place', 'c.nbr_place_reserve')
            ->get()
            ->all();
    }

    public function getDemandesReportEnAttenteChef(?int $idCompagnie, ?string $ville, ?string $numeroGare): array
    {
        if (! $idCompagnie) {
            return [];
        }

        return DB::table('billets as b')
            ->join('client as c', 'b.id_client', '=', 'c.idClient')
            ->leftJoin('utilisateur as u', 'u.idUser', '=', 'b.demande_report_par')
            ->where('b.id_compagnie', $idCompagnie)
            ->where('b.status_billets', 'report_demande')
            ->where('b.departId', $ville)
            ->where('b.num_gare', $numeroGare)
            ->orderBy('b.demande_report_le')
            ->select('b.idBillets', 'b.numeroBillets', 'b.jourVoyage', 'b.Heur_departs', 'b.departId', 'b.destinationId', 'b.nouvelle_date_demandee', 'b.nouvelle_heure_demandee', 'b.demande_report_le', 'c.Client', 'u.utilisateurs as demandeur')
            ->get()
            ->all();
    }

    public function getDemandesReportEnAttente(?int $idCompagnie): array
    {
        if (! $idCompagnie) {
            return [];
        }

        return DB::table('billets as b')
            ->join('client as c', 'b.id_client', '=', 'c.idClient')
            ->leftJoin('utilisateur as u', 'u.idUser', '=', 'b.demande_report_par')
            ->leftJoin('utilisateur as ut', 'ut.idUser', '=', 'b.report_transmis_par')
            ->where('b.id_compagnie', $idCompagnie)
            ->where('b.status_billets', 'report_transmis')
            ->orderBy('b.report_transmis_le')
            ->select('b.idBillets', 'b.numeroBillets', 'b.jourVoyage', 'b.Heur_departs', 'b.departId', 'b.destinationId', 'b.nouvelle_date_demandee', 'b.nouvelle_heure_demandee', 'b.demande_report_le', 'c.Client', 'u.utilisateurs as demandeur', 'ut.utilisateurs as transmis_par_nom')
            ->get()
            ->all();
    }

    public function countPartenairesEnAttente(): int
    {
        $sql = "SELECT COUNT(*) AS total FROM partenaire_compte pc
                WHERE (
                    SELECT auteur FROM partenaire_message pm
                    WHERE pm.id_partenaire = pc.id_partenaire
                    ORDER BY pm.date_envoi DESC LIMIT 1
                ) = 'partenaire'";

        return (int) DB::selectOne($sql)->total;
    }

    public function getBilletsEnRetard(?string $idDepart, ?string $numeroGare, ?int $idCompagnie): array
    {
        if (! $idCompagnie) {
            return [];
        }

        date_default_timezone_set('Africa/Bamako');
        $aujourdhui = date('Y-m-d');
        $seuilRetard = date('Y-m-d H:i:s', strtotime('-30 minutes'));

        $query = DB::table('billets as b')
            ->join('client as c', 'b.id_client', '=', 'c.idClient')
            ->where('b.id_compagnie', $idCompagnie)
            ->where('b.jourVoyage', $aujourdhui)
            ->where(function ($q) {
                $q->whereNull('b.status_billets')->orWhere('b.status_billets', '');
            })
            ->where(function ($q) {
                $q->whereNull('b.statut_embarquement')->orWhere('b.statut_embarquement', '!=', 'embarque');
            })
            ->whereRaw('TIMESTAMP(b.jourVoyage, b.Heur_departs) < ?', [$seuilRetard]);

        if ($idDepart !== null) {
            $query->where('b.departId', $idDepart);
        }
        if ($numeroGare !== null) {
            $query->where('b.num_gare', $numeroGare);
        }

        return $query->orderBy('b.Heur_departs')->orderBy('c.Client')
            ->select('b.idBillets', 'b.destinationId', 'b.Heur_departs', 'c.Client')
            ->get()
            ->all();
    }
}
