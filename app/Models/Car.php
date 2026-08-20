<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Car extends Model
{
    protected $table = 'car';

    protected $primaryKey = 'id_car';

    public $timestamps = false;

    protected $fillable = [
        'numero_car',
        'matriculle',
        'nbr_place',
        'nbr_place_reserve',
        'programmer_car',
        'id_compagnie',
        'status_car',
    ];

    public function compagnie()
    {
        return $this->belongsTo(Compagnie::class, 'id_compagnie', 'id_compagnie');
    }

    public function chauffeurs()
    {
        return $this->hasMany(Chauffeur::class, 'id_car', 'id_car');
    }
}
