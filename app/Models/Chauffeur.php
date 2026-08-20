<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chauffeur extends Model
{
    protected $table = 'chauffeur';

    protected $primaryKey = 'id_chauffeur';

    public $timestamps = false;

    protected $fillable = ['nom_prenom', 'numero', 'id_car', 'id_compagnie', 'photo'];

    public function car()
    {
        return $this->belongsTo(Car::class, 'id_car', 'id_car');
    }

    public function compagnie()
    {
        return $this->belongsTo(Compagnie::class, 'id_compagnie', 'id_compagnie');
    }
}
