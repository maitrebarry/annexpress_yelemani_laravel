<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Escale extends Model
{
    protected $table = 'escale';

    protected $primaryKey = 'id_escale';

    public $timestamps = false;

    protected $fillable = ['escales', 'id_compagnie'];

    public function compagnie()
    {
        return $this->belongsTo(Compagnie::class, 'id_compagnie', 'id_compagnie');
    }
}
