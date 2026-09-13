<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Camion extends Model
{
    protected $table = 'camion';

    protected $primaryKey = 'id_camion';

    public $timestamps = false;

    protected $fillable = [
        'numero_camion',
        'matriculle',
        'actif',
        'id_compagnie',
    ];

    public function compagnie()
    {
        return $this->belongsTo(Compagnie::class, 'id_compagnie', 'id_compagnie');
    }

    public function chauffeurs()
    {
        return $this->hasMany(Chauffeur::class, 'id_camion', 'id_camion');
    }

    /**
     * Port de Camion::getTousPourFlotte() (legacy). Pour l'écran "Flotte" : tous les
     * camions de la compagnie, actifs ET inactifs (contrairement à
     * EnvoiColisService::getCamionsActifs(), qui ne veut que les actifs pour l'envoi de
     * colis — ici on veut voir l'état de toute la flotte). Même portée que
     * ProgrammationVoyage::etatFlotte() : filtre par id_compagnie sans exception
     * super_admin (déjà le comportement actuel pour les cars).
     */
    public static function etatFlotte(?int $idCompagnie)
    {
        return DB::table('camion as c')
            ->where('c.id_compagnie', $idCompagnie)
            ->orderBy('c.numero_camion')
            ->get([
                'c.id_camion', 'c.numero_camion', 'c.matriculle', 'c.actif',
                DB::raw("(SELECT lc.destination FROM location_car lc
                    WHERE lc.id_camion = c.id_camion AND lc.statut IN ('en_attente', 'valide')
                      AND CURDATE() BETWEEN lc.date_depart AND lc.date_retour_prevu
                    LIMIT 1) as destination_location"),
                DB::raw("(SELECT lc.date_retour_prevu FROM location_car lc
                    WHERE lc.id_camion = c.id_camion AND lc.statut IN ('en_attente', 'valide')
                      AND CURDATE() BETWEEN lc.date_depart AND lc.date_retour_prevu
                    LIMIT 1) as retour_prevu"),
                DB::raw("(SELECT COUNT(*) FROM envoi e
                    INNER JOIN colis col ON e.id_coli = col.id_colis
                    WHERE e.id_camion = c.id_camion AND col.status = 'en_cours') as nb_colis_en_cours"),
                DB::raw("(SELECT e.date_enregistre FROM envoi e
                    INNER JOIN colis col ON e.id_coli = col.id_colis
                    WHERE e.id_camion = c.id_camion AND col.status = 'en_cours'
                    ORDER BY e.date_enregistre DESC LIMIT 1) as date_envoi_colis"),
            ]);
    }
}
