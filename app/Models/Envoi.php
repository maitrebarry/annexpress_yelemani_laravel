<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Envoi extends Model
{
    protected $table = 'envoi';

    protected $primaryKey = 'id_envoi';

    public $timestamps = false;

    protected $fillable = [
        'id_coli',
        'id_car',
        'id_camion',
        'date_enregistre',
        'id_compagnie',
    ];
}
