<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LigneEnvoi extends Model
{
    protected $table = 'ligne_envoi';

    protected $primaryKey = 'id_ligne_envoi';

    public $timestamps = false;

    protected $fillable = [
        'numero_car',
        'numero_camion',
        'dates',
        'id_compagnie',
    ];
}
