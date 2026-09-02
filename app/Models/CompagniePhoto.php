<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompagniePhoto extends Model
{
    protected $table = 'compagnie_photos';

    protected $fillable = [
        'id_compagnie',
        'chemin',
        'ordre',
    ];

    public function compagnie()
    {
        return $this->belongsTo(Compagnie::class, 'id_compagnie', 'id_compagnie');
    }
}
