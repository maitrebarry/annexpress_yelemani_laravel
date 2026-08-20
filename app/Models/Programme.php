<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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

    // Port de Liste_du_jour::resolveDestinationPrincipale() : un billet stocke soit la
    // destination finale, soit le nom d'une escale intermédiaire dans `destinationId`. Cette
    // méthode retrouve la destination FINALE du trajet, nécessaire pour retrouver la bonne
    // ligne `programmation_voyage`/`suivis` (qui utilisent toujours la destination finale,
    // jamais un nom d'escale). Partagée par les annulations et les reports.
    public static function resolveDestinationPrincipale(string $depart, string $heure, string $destinationId, int $idCompagnie): string
    {
        $direct = self::query()
            ->join('agence as a1', 'programmer.idDepart', '=', 'a1.idAgence')
            ->join('agence as a2', 'programmer.idDestination', '=', 'a2.idAgence')
            ->where('a1.localite', $depart)->where('a2.localite', $destinationId)
            ->where('programmer.heureDepart', $heure)->where('programmer.id_compagnie', $idCompagnie)
            ->exists();

        if ($direct) {
            return $destinationId;
        }

        $viaEscale = DB::table('ligneTrajet as lt')
            ->join('escale as e', 'e.id_escale', '=', 'lt.id_escales')
            ->join('programmer as p', function ($join) {
                $join->on('p.idProgrammer', '=', 'lt.id_trajets')->where('lt.type_trajet', 'programmer');
            })
            ->join('agence as a1', 'p.idDepart', '=', 'a1.idAgence')
            ->join('agence as a2', 'p.idDestination', '=', 'a2.idAgence')
            ->where('e.escales', $destinationId)->where('a1.localite', $depart)
            ->where('p.heureDepart', $heure)->where('p.id_compagnie', $idCompagnie)
            ->value('a2.localite');

        return $viaEscale ?? $destinationId;
    }
}
