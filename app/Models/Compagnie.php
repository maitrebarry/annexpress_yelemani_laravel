<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Compagnie extends Model
{
    protected $table = 'compagnie';
    protected $primaryKey = 'id_compagnie';
    public $timestamps = false;

    protected $fillable = [
        'nom_compagnie',
        'libele',
        'slogant',
        'logo',
    ];

    public function agences()
    {
        return $this->hasMany(Agence::class, 'id_compagnie', 'id_compagnie');
    }

    public function utilisateurs()
    {
        return $this->hasMany(Utilisateur::class, 'id_compagnie', 'id_compagnie');
    }
}
