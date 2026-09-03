<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Actualite extends Model
{
    protected $fillable = [
        'id_compagnie',
        'titre',
        'contenu',
        'image',
        'date_publication',
    ];

    protected $casts = [
        'date_publication' => 'date',
    ];

    public function compagnie()
    {
        return $this->belongsTo(Compagnie::class, 'id_compagnie', 'id_compagnie');
    }
}
