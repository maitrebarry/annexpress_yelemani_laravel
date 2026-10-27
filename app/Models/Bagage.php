<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Port de Projets_licence/app/models/Bagage.php — bagages payants associés à un billet
 * (cf. migration create_bagages_table). Pas de formule de calcul automatique : l'agent
 * pèse le bagage et saisit le prix à la main.
 */
class Bagage extends Model
{
    protected $table = 'bagages';

    protected $primaryKey = 'id_bagage';

    public $timestamps = false;

    protected $fillable = [
        'idBillets',
        'description',
        'poids',
        'prix',
        'id_compagnie',
        'id_utilisateur',
    ];

    public static function getBagagesPourBillet(int $idBillets, int $idCompagnie)
    {
        return static::where('idBillets', $idBillets)
            ->where('id_compagnie', $idCompagnie)
            ->orderBy('id_bagage')
            ->get(['id_bagage', 'description', 'poids', 'prix', 'date_enregistrement']);
    }

    public static function getTotalBagages(int $idBillets, int $idCompagnie): float
    {
        return (float) static::where('idBillets', $idBillets)
            ->where('id_compagnie', $idCompagnie)
            ->sum('prix');
    }
}
