<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Table `suivis` (port de Projets_licence) : quota de places pour les réservations de
 * "demain" (contrairement à aujourd'hui, qui verrouille directement `car.nbr_place_reserve`).
 * Une ligne par créneau (gare/destination/heure/date) une fois la première réservation faite.
 */
class Suivis extends Model
{
    protected $table = 'suivis';

    protected $primaryKey = 'idSuivis';

    public $timestamps = false;

    protected $fillable = [
        'place_totals',
        'place_reserve',
        'statut',
        'nombre_paliers',
        'destination',
        'heur_depart',
        'date_reservation',
        'depart',
        'id_agence',
        'id_compagnie',
    ];
}
