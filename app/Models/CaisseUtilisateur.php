<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CaisseUtilisateur extends Model
{
    protected $table = 'caisse_utilisateur';

    protected $primaryKey = 'id_caisse_user';

    public $timestamps = false;

    protected $fillable = [
        'id_utilisateur',
        'id_agence',
        'id_compagnie',
        'date_service',
        'heure_ouverture',
        'heure_fermeture',
        'montant_initial',
        'total_billets',
        'total_colis',
        'montant_depense',
        'montant_location',
        'nb_billets',
        'nb_colis',
        'montant_compte',
        'ecart',
        'statut',
        'reference',
    ];
}
