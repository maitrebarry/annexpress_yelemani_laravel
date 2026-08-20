<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlaceMinimale extends Model
{
    protected $table = 'place_minumale';

    protected $primaryKey = 'id_place_minumale';

    public $timestamps = false;

    protected $fillable = ['place_minumale', 'id_compagnie'];

    public function compagnie()
    {
        return $this->belongsTo(Compagnie::class, 'id_compagnie', 'id_compagnie');
    }
}
