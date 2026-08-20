<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Port de la table `transferts_gare` : trace permanente d'un transfert de passagers
 * d'une gare vers une autre (même destination/heure/jour), une fois exécuté.
 * Voir `App\Http\Controllers\Admin\TransfertGareController` pour l'exécution elle-même.
 */
class TransfertGare extends Model
{
    protected $table = 'transferts_gare';
    protected $primaryKey = 'id_transfert';
    public $timestamps = false;

    protected $fillable = [
        'id_compagnie',
        'id_agence_source',
        'id_agence_destination',
        'id_programmation_source',
        'id_programmation_destination',
        'id_caisse_source',
        'id_caisse_destination',
        'nombre_billets',
        'nombre_passagers',
        'montant_total',
        'id_utilisateur',
    ];

    public function agenceSource()
    {
        return $this->belongsTo(Agence::class, 'id_agence_source', 'idAgence');
    }

    public function agenceDestination()
    {
        return $this->belongsTo(Agence::class, 'id_agence_destination', 'idAgence');
    }

    public function agent()
    {
        return $this->belongsTo(Utilisateur::class, 'id_utilisateur', 'idUser');
    }
}
