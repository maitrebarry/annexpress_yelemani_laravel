<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Port de Projets_licence/app/models/Banque.php (table `banques`, comptes bancaires de la
 * compagnie). Logique métier portée dans App\Services\BanqueService et
 * App\Services\DepotBanqueService.
 */
class Banque extends Model
{
    protected $table = 'banques';
    protected $primaryKey = 'id_banque';
    public $timestamps = false;

    protected $fillable = [
        'id_compagnie',
        'nom',
        'numero_compte',
        'solde',
        'statut',
    ];

    protected $casts = [
        'solde' => 'float',
        'date_creation' => 'datetime',
    ];

    public function depots()
    {
        return $this->hasMany(DepotBanque::class, 'id_banque', 'id_banque');
    }
}
