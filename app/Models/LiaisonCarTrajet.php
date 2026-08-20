<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Port de la table `liaison_car_trajet` : affectation d'un trajet programmé (`programmer`)
 * à un car, pour une compagnie donnée.
 */
class LiaisonCarTrajet extends Model
{
    protected $table = 'liaison_car_trajet';
    protected $primaryKey = 'id_liaison';
    public $timestamps = false;

    protected $fillable = [
        'id_car',
        'id_trajets',
        'id_compagnie',
    ];

    public function car()
    {
        return $this->belongsTo(Car::class, 'id_car', 'id_car');
    }

    public function trajet()
    {
        return $this->belongsTo(Programme::class, 'id_trajets', 'idProgrammer');
    }
}
