<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Port de la table `programmation_voyage` : affectation réelle d'un car à un
 * créneau (horaire + destination) pour une date donnée — contrairement à
 * `Programme` (table `programmer`), qui décrit seulement les trajets possibles.
 */
class ProgrammationVoyage extends Model
{
    protected $table = 'programmation_voyage';
    protected $primaryKey = 'id_programmation';
    public $timestamps = false;

    protected $fillable = [
        'id_car_programmer',
        'id_horaire',
        'id_trajet',
        'id_agence_destination',
        'localite_user',
        'id_agence',
        'date_enregistre',
        'id_compagnie',
        'statut',
        'decolle_le',
        'decolle_par',
    ];

    public function car()
    {
        return $this->belongsTo(Car::class, 'id_car_programmer', 'id_car');
    }

    public function agence()
    {
        return $this->belongsTo(Agence::class, 'id_agence', 'idAgence');
    }

    public function agenceDestination()
    {
        return $this->belongsTo(Agence::class, 'id_agence_destination', 'idAgence');
    }

    public function compagnie()
    {
        return $this->belongsTo(Compagnie::class, 'id_compagnie', 'id_compagnie');
    }

    // Toutes les valeurs de destinationId correspondant à un créneau (destination finale +
    // nom de chaque escale, car un billet vers une escale y est enregistré comme destinationId
    // plutôt que la destination finale). Partagé par ProgrammationVoyageController (recalcul du
    // nombre de places vendues) et TransfertGareController (billets à transférer).
    public static function destinationsPourCreneau($idHoraire, $idDestination, $localiteUser, $idCompagnie, $idAgence = null): array
    {
        $destinations = [$idDestination];

        $prog = $idAgence
            ? DB::table('programmer as p')->leftJoin('agence as a2', 'p.idDestination', '=', 'a2.idAgence')
                ->where('p.idDepart', $idAgence)->where('a2.localite', $idDestination)
                ->where('p.heureDepart', $idHoraire)->where('p.id_compagnie', $idCompagnie)->first(['p.idProgrammer'])
            : DB::table('programmer as p')->leftJoin('agence as a1', 'p.idDepart', '=', 'a1.idAgence')
                ->leftJoin('agence as a2', 'p.idDestination', '=', 'a2.idAgence')
                ->where('a1.localite', $localiteUser)->where('a2.localite', $idDestination)
                ->where('p.heureDepart', $idHoraire)->where('p.id_compagnie', $idCompagnie)->first(['p.idProgrammer']);

        if ($prog) {
            $escales = DB::table('ligneTrajet as lt')->join('escale as e', 'e.id_escale', '=', 'lt.id_escales')
                ->where('lt.id_trajets', $prog->idProgrammer)->where('lt.type_trajet', 'programmer')
                ->pluck('e.escales');
            $destinations = array_merge($destinations, $escales->all());
        }

        return $destinations;
    }
}
