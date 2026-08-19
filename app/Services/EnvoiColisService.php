<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Port de Projets_licence/app/models/Envoie_colis.php (méthodes réellement utilisées par les
 * actions vivantes du contrôleur uniquement — traiterEnvoi1(), pas traiterEnvoi() qui n'est
 * utilisée que par l'action morte index()).
 */
class EnvoiColisService
{
    public function traiterEnvoi1(array $colisIds, int $idCar, int $idCompagnie): void
    {
        try {
            DB::transaction(function () use ($colisIds, $idCar, $idCompagnie) {
                // Verrouille le car : sans ça, deux envois simultanés pour le même car
                // pourraient chacun créer leur propre lot ligne_envoi du jour.
                DB::table('car')
                    ->where('id_car', $idCar)
                    ->where('id_compagnie', $idCompagnie)
                    ->lockForUpdate()
                    ->first();

                $dateEnregistre = $this->getOuCreerLigneEnvoiDuJour($idCar, $idCompagnie);

                $colisDisponibles = $this->verrouillerColisDisponibles($colisIds, $idCompagnie);

                if (empty($colisDisponibles)) {
                    throw new \RuntimeException('Aucun colis disponible pour cet envoi.');
                }

                DB::table('envoi')->insert(array_map(fn ($id) => [
                    'id_coli' => $id,
                    'id_car' => $idCar,
                    'date_enregistre' => $dateEnregistre,
                    'id_compagnie' => $idCompagnie,
                ], $colisDisponibles));

                DB::table('colis')
                    ->whereIn('id_colis', $colisDisponibles)
                    ->where('id_compagnie', $idCompagnie)
                    ->update(['status' => 'en_cours']);
            });
        } catch (\Throwable $e) {
            // Port fidèle du comportement legacy : rollback silencieux, jamais d'erreur remontée
            // à l'appelant (le contrôleur affiche toujours le message de succès).
        }
    }

    public function verrouillerColisDisponibles(array $colisIds, int $idCompagnie): array
    {
        if (empty($colisIds)) {
            return [];
        }

        return DB::table('colis')
            ->whereIn('id_colis', $colisIds)
            ->where('id_compagnie', $idCompagnie)
            ->where('status', 'enregistre')
            ->lockForUpdate()
            ->pluck('id_colis')
            ->all();
    }

    public function getOuCreerLigneEnvoiDuJour(int $idCar, int $idCompagnie): Carbon
    {
        $existing = DB::table('ligne_envoi')
            ->where('numero_car', $idCar)
            ->whereDate('dates', now()->toDateString())
            ->where('id_compagnie', $idCompagnie)
            ->value('dates');

        if ($existing) {
            return Carbon::parse($existing);
        }

        $dateEnregistre = now();

        DB::table('ligne_envoi')->insert([
            'numero_car' => $idCar,
            'dates' => $dateEnregistre,
            'id_compagnie' => $idCompagnie,
        ]);

        return $dateEnregistre;
    }

    public function getCarsDisponiblesAujourdhui(int $idCompagnie, string $droit, ?string $ville, ?int $idAgence): array
    {
        $query = DB::table('programmation_voyage')
            ->join('horaire', function ($join) {
                $join->on('horaire.heuredepart', '=', 'programmation_voyage.id_horaire')
                    ->on('horaire.id_compagnie', '=', 'programmation_voyage.id_compagnie');
            })
            ->where('programmation_voyage.date_enregistre', now()->toDateString())
            ->where('programmation_voyage.id_compagnie', $idCompagnie)
            ->where('programmation_voyage.statut', 'active');

        if ($droit === 'chef_d_escale') {
            $query->where('programmation_voyage.localite_user', $ville)
                ->where('programmation_voyage.id_agence', $idAgence);
        }

        return $query->distinct()
            ->select(
                'programmation_voyage.id_car_programmer',
                'programmation_voyage.id_horaire',
                'programmation_voyage.id_trajet',
                'programmation_voyage.localite_user',
                'programmation_voyage.date_enregistre',
                'programmation_voyage.id_compagnie as compagnie_prog',
                'horaire.heuredepart',
                'horaire.id_compagnie as compagnie_horaire'
            )
            ->get()
            ->map(fn ($row) => (array) $row)
            ->all();
    }

    public function getCarById(int $idCarProgrammer, int $idCompagnie): ?object
    {
        return DB::table('programmation_voyage')
            ->join('horaire', function ($join) {
                $join->on('horaire.heuredepart', '=', 'programmation_voyage.id_horaire')
                    ->on('horaire.id_compagnie', '=', 'programmation_voyage.id_compagnie');
            })
            ->where('programmation_voyage.id_car_programmer', $idCarProgrammer)
            ->where('programmation_voyage.id_compagnie', $idCompagnie)
            ->where('programmation_voyage.date_enregistre', now()->toDateString())
            ->select('programmation_voyage.*', 'horaire.heuredepart')
            ->first();
    }

    public function getColisParCarEtDate(int $idCar, string $dateEnvoi): array
    {
        return DB::table('envoi')
            ->join('colis', 'envoi.id_coli', '=', 'colis.id_colis')
            ->where('envoi.id_car', $idCar)
            ->where('envoi.date_enregistre', $dateEnvoi)
            ->select('envoi.*', 'colis.*')
            ->get()
            ->all();
    }

    public function changerCarColis(int $idColis, int $ancienIdCar, string $ancienneDate, int $nouveauIdCar, int $idCompagnie): bool
    {
        $nouvelleDate = $this->getOuCreerLigneEnvoiDuJour($nouveauIdCar, $idCompagnie);

        $updated = DB::table('envoi')
            ->where('id_coli', $idColis)
            ->where('id_car', $ancienIdCar)
            ->where('date_enregistre', $ancienneDate)
            ->where('id_compagnie', $idCompagnie)
            ->update([
                'id_car' => $nouveauIdCar,
                'date_enregistre' => $nouvelleDate,
            ]);

        return $updated > 0;
    }

    public function annulerEnvoi(int $idCar, string $dateEnvoi, int $idCompagnie): bool
    {
        return DB::transaction(function () use ($idCar, $dateEnvoi, $idCompagnie) {
            $ids = DB::table('envoi')
                ->where('id_car', $idCar)
                ->where('date_enregistre', $dateEnvoi)
                ->where('id_compagnie', $idCompagnie)
                ->pluck('id_coli');

            if ($ids->isEmpty()) {
                return false;
            }

            DB::table('colis')->whereIn('id_colis', $ids)->update(['status' => 'enregistre']);

            DB::table('envoi')
                ->where('id_car', $idCar)
                ->where('date_enregistre', $dateEnvoi)
                ->where('id_compagnie', $idCompagnie)
                ->delete();

            DB::table('ligne_envoi')
                ->where('numero_car', $idCar)
                ->where('dates', $dateEnvoi)
                ->where('id_compagnie', $idCompagnie)
                ->delete();

            return true;
        });
    }
}
