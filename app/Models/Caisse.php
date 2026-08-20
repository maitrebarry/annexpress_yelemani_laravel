<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Port de la table `caisse` : caisse de gare (système plus ancien, distinct de
 * `caisse_utilisateur` — voir App\Services\CaisseUtilisateurService). Aucun écran ne
 * permet encore de créer/alimenter une caisse de gare (table vide) : l'écran "Bilan de
 * caisse" reste donc vide tant qu'aucune ligne n'existe, mais fonctionne correctement.
 */
class Caisse extends Model
{
    protected $table = 'caisse';
    protected $primaryKey = 'id_caisse';
    public $timestamps = false;

    protected $fillable = [
        'id_user',
        'type_caisse',
        'id_compagnie',
        'id_agence',
        'montant_initial',
        'montant_billets',
        'montant_colis',
        'date_enregistrement',
        'date_fermeture',
        'reference_caise',
        'status_caisse',
        'montant_rembourse',
        'montant_versements_recus',
        'montant_depense',
        'montant_location',
        'montant_attendu',
        'montant_reel',
        'ecart',
    ];

    public function agence()
    {
        return $this->belongsTo(Agence::class, 'id_agence', 'idAgence');
    }
}
