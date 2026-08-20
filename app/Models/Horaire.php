<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Horaire extends Model
{
    protected $table = 'horaire';

    protected $primaryKey = 'id_heure';

    public $timestamps = false;

    protected $fillable = ['heuredepart', 'id_compagnie'];

    public function compagnie()
    {
        return $this->belongsTo(Compagnie::class, 'id_compagnie', 'id_compagnie');
    }
}
