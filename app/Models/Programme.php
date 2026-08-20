<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Port de Projets_licence/app/models/Programmer_voyage.php (table `programmer`) —
 * un voyage programmé (départ, destination, heure, prix) pour une compagnie.
 */
class Programme extends Model
{
    protected $table = 'programmer';
    protected $primaryKey = 'idProgrammer';
    public $timestamps = false;

    protected $fillable = [
        'idDepart',
        'idDestination',
        'heureDepart',
        'rdv',
        'prix',
        'id_compagnie',
    ];

    public function depart()
    {
        return $this->belongsTo(Agence::class, 'idDepart', 'idAgence');
    }

    public function destination()
    {
        return $this->belongsTo(Agence::class, 'idDestination', 'idAgence');
    }

    public function compagnie()
    {
        return $this->belongsTo(Compagnie::class, 'id_compagnie', 'id_compagnie');
    }

    public function escales()
    {
        return $this->hasMany(LigneTrajet::class, 'id_trajets', 'idProgrammer')
            ->where('type_trajet', 'programmer');
    }
}
