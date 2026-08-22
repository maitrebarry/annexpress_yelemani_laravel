<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Port de Projets_licence/app/models/Depense.php (table `depense`). Logique métier
 * (saveDepense/getBenefice/valider/rejeter) portée dans App\Services\DepenseService.
 */
class Depense extends Model
{
    protected $table = 'depense';
    protected $primaryKey = 'id_depense';
    public $timestamps = false;

    public const CATEGORIES = [
        'Carburant',
        'Entretien/Reparation',
        'Peage',
        'Fournitures',
        'Communication',
        'Salaire',
        'Loyer',
        'Assurance',
        'Remboursement annulation',
        'Remboursement colis',
        'Autre',
    ];

    protected $fillable = [
        'id_compagnie',
        'id_agence',
        'id_caisse',
        'id_caisse_user',
        'categorie',
        'libelle',
        'montant',
        'date_depense',
        'id_utilisateur',
        'statut',
    ];

    protected $casts = [
        'date_depense' => 'date',
        'date_enregistrement' => 'datetime',
        'montant' => 'float',
    ];

    public function agence()
    {
        return $this->belongsTo(Agence::class, 'id_agence', 'idAgence');
    }

    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class, 'id_utilisateur', 'idUser');
    }
}
