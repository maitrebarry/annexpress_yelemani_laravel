<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Port de Projets_licence/app/models/Location_car.php — table `location_car` (déjà migrée,
 * inutilisée avant ce chantier).
 */
class LocationCar extends Model
{
    protected $table = 'location_car';

    protected $primaryKey = 'id_location';

    public $timestamps = false;

    public const STATUTS = ['en_attente', 'valide', 'rejete'];

    protected $fillable = [
        'id_compagnie',
        'id_agence_depart',
        'destination',
        'id_car',
        'id_caisse',
        'id_caisse_user',
        'nom_client',
        'prenom_client',
        'telephone_client',
        'date_depart',
        'date_retour_prevu',
        'frais_location',
        'statut',
        'id_utilisateur',
        'id_valide_par',
    ];

    public function agence()
    {
        return $this->belongsTo(Agence::class, 'id_agence_depart', 'idAgence');
    }

    public function car()
    {
        return $this->belongsTo(Car::class, 'id_car', 'id_car');
    }

    public function agent()
    {
        return $this->belongsTo(Utilisateur::class, 'id_utilisateur', 'idUser');
    }

    public function valideur()
    {
        return $this->belongsTo(Utilisateur::class, 'id_valide_par', 'idUser');
    }
}
