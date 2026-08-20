<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Port de Projets_licence — table `billets`. `departId`/`destinationId` stockent des noms
 * de localité (pas des id d'agence), fidèle au legacy — déjà lu ainsi partout ailleurs dans
 * l'app (ProgrammationVoyageController, TransfertGareController, CaisseController...).
 * Seule la création + liste/historique sont couvertes par ce chantier ; les workflows
 * d'annulation/report/embarquement/validation restent à porter plus tard.
 */
class Billet extends Model
{
    protected $table = 'billets';

    protected $primaryKey = 'idBillets';

    public $timestamps = false;

    protected $fillable = [
        'id_client',
        'idUser',
        'numeroBillets',
        'jourVoyage',
        'Heur_departs',
        'nombrePassages',
        'destinationId',
        'departId',
        'date_expiration',
        'numeroPlace',
        'date_reservation',
        'status_billets',
        'statut_embarquement',
        'embarque_le',
        'embarque_par',
        'status_reservation',
        'validation_billets',
        'date_repporte',
        'id_compagnie',
        'num_gare',
        'date_annulation',
        'motif_annulation',
        'demande_annulation_par',
        'demande_annulation_le',
        'annule_par',
        'nouvelle_date_demandee',
        'nouvelle_heure_demandee',
        'demande_report_par',
        'demande_report_le',
        'report_transmis_par',
        'report_transmis_le',
        'id_caisse_user',
    ];

    protected $casts = [
        'jourVoyage' => 'date',
        'date_expiration' => 'date',
        'date_reservation' => 'date',
        'date_annulation' => 'datetime',
        'demande_annulation_le' => 'datetime',
        'date_repporte' => 'date',
        'nouvelle_date_demandee' => 'date',
        'demande_report_le' => 'datetime',
        'report_transmis_le' => 'datetime',
        'embarque_le' => 'datetime',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class, 'id_client', 'idClient');
    }

    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class, 'idUser', 'idUser');
    }
}
