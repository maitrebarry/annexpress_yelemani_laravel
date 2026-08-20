<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Port de la table `versements_caisse` : demande de versement d'un opérateur (caisse
 * individuelle fermée) vers un chef d'escale, à valider/rejeter par ce dernier.
 */
class VersementCaisse extends Model
{
    protected $table = 'versements_caisse';
    protected $primaryKey = 'id_versement';
    public $timestamps = false;

    protected $fillable = [
        'id_caisse_user',
        'id_emetteur',
        'id_chef_escale',
        'id_agence',
        'id_compagnie',
        'montant',
        'statut',
        'commentaire',
        'date_validation',
    ];

    public function caisseUser()
    {
        return $this->belongsTo(CaisseUtilisateur::class, 'id_caisse_user', 'id_caisse_user');
    }

    public function emetteur()
    {
        return $this->belongsTo(Utilisateur::class, 'id_emetteur', 'idUser');
    }

    public function chefEscale()
    {
        return $this->belongsTo(Utilisateur::class, 'id_chef_escale', 'idUser');
    }

    public function agence()
    {
        return $this->belongsTo(Agence::class, 'id_agence', 'idAgence');
    }
}
