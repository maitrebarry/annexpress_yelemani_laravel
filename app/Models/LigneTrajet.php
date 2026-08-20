<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Port de la table `ligneTrajet` : tarif d'une escale sur un trajet donné.
 * `type_trajet` distingue les trajets du site public ('trajet') des voyages
 * programmés côté admin ('programmer') — seul ce dernier type est utilisé ici.
 */
class LigneTrajet extends Model
{
    protected $table = 'ligneTrajet';
    protected $primaryKey = 'id_trajet_ligne';
    public $timestamps = false;

    protected $fillable = [
        'id_escales',
        'id_trajets',
        'type_trajet',
        'prix_escale',
    ];

    public function escale()
    {
        return $this->belongsTo(Escale::class, 'id_escales', 'id_escale');
    }
}
