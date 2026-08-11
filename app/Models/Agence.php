<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Agence extends Model
{
    protected $table = 'agence';
    protected $primaryKey = 'idAgence';
    public $timestamps = false;

    protected $fillable = [
        'code',
        'localite',
        'numeroGare',
        'tel',
        'id_compagnie',
        'status',
    ];

    public function compagnie()
    {
        return $this->belongsTo(Compagnie::class, 'id_compagnie', 'id_compagnie');
    }
}
