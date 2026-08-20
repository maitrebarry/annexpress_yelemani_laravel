<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Port de la table `reference_car` : simple journal des cars programmés au moins une fois
 * (une ligne par programmation ; nettoyée quand la programmation est supprimée). Aucune
 * lecture n'en dépend actuellement — la liste des cars programmés se base sur
 * `car.programmer_car`, plus fiable.
 */
class ReferenceCar extends Model
{
    protected $table = 'reference_car';
    protected $primaryKey = 'id_reference';
    public $timestamps = false;

    protected $fillable = ['id_car'];
}
