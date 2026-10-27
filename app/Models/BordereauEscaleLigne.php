<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BordereauEscaleLigne extends Model
{
    protected $table = 'bordereau_escale_lignes';

    protected $primaryKey = 'id_ligne';

    public $timestamps = false;

    protected $fillable = [
        'id_bordereau',
        'type_ligne',
        'destination',
        'nombre',
        'prix_unitaire',
        'description',
        'montant',
    ];
}
