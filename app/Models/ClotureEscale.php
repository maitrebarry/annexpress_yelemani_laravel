<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Port de la table `clotures_escale` : rapport de clôture journalière d'une gare
 * (consolidation des caisses individuelles fermées/versées de ses opérateurs).
 */
class ClotureEscale extends Model
{
    protected $table = 'clotures_escale';
    protected $primaryKey = 'id_cloture';
    public $timestamps = false;

    protected $fillable = [
        'id_chef_escale',
        'id_agence',
        'id_compagnie',
        'date_cloture',
        'total_billets',
        'total_colis',
        'total_versements',
        'total_ecarts',
        'statut',
        'rapport_json',
    ];

    public function chefEscale()
    {
        return $this->belongsTo(Utilisateur::class, 'id_chef_escale', 'idUser');
    }

    public function agence()
    {
        return $this->belongsTo(Agence::class, 'id_agence', 'idAgence');
    }
}
