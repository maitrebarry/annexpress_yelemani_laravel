<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Port de Projets_licence/app/models/Banque.php (partie "dépôt en banque", table
 * `depots_banque`). Logique métier portée dans App\Services\DepotBanqueService.
 *
 * `id_caisse` n'est plus renseigné (toujours NULL) : l'ancienne caisse de gare qu'il
 * référençait n'a aucune ligne dans cette app. Le solde disponible pour un dépôt est
 * désormais calculé depuis `versements_caisse` (voir DepotBanqueService::soldeDisponible()).
 */
class DepotBanque extends Model
{
    protected $table = 'depots_banque';
    protected $primaryKey = 'id_depot';
    public $timestamps = false;

    protected $fillable = [
        'id_compagnie',
        'id_agence',
        'id_caisse',
        'id_banque',
        'montant',
        'reference',
        'statut',
        'motif_rejet',
        'id_utilisateur_demandeur',
        'id_utilisateur_validateur',
        'date_validation',
    ];

    protected $casts = [
        'montant' => 'float',
        'date_demande' => 'datetime',
        'date_validation' => 'datetime',
    ];

    public function agence()
    {
        return $this->belongsTo(Agence::class, 'id_agence', 'idAgence');
    }

    public function banque()
    {
        return $this->belongsTo(Banque::class, 'id_banque', 'id_banque');
    }

    public function demandeur()
    {
        return $this->belongsTo(Utilisateur::class, 'id_utilisateur_demandeur', 'idUser');
    }

    public function validateur()
    {
        return $this->belongsTo(Utilisateur::class, 'id_utilisateur_validateur', 'idUser');
    }
}
