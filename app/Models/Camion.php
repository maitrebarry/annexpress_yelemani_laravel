<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Camion extends Model
{
    protected $table = 'camion';

    protected $primaryKey = 'id_camion';

    public $timestamps = false;

    protected $fillable = [
        'numero_camion',
        'matriculle',
        'actif',
        'id_compagnie',
    ];

    public function compagnie()
    {
        return $this->belongsTo(Compagnie::class, 'id_compagnie', 'id_compagnie');
    }

    public function chauffeurs()
    {
        return $this->hasMany(Chauffeur::class, 'id_camion', 'id_camion');
    }
}
