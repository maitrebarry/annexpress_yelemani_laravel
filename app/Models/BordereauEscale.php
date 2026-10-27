<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Port de Projets_licence/app/models/Bordereau_escale.php (table `bordereau_escale`) —
 * audit de la saisie système d'un bordereau d'escale papier. La logique métier
 * (saveBordereau) est dans App\Services\BordereauEscaleService.
 */
class BordereauEscale extends Model
{
    protected $table = 'bordereau_escale';

    protected $primaryKey = 'id_bordereau';

    public $timestamps = false;

    protected $fillable = [
        'numero_bordereau',
        'date_escale',
        'numero_bus',
        'escale_libelle',
        'nom_agent_escale',
        'id_agence',
        'id_compagnie',
        'id_caisse_user',
        'total_billets',
        'total_bagages',
        'montant_depenses',
        'montant_commission',
        'total_verse_saisi',
        'saisi_par',
    ];

    public function agence()
    {
        return $this->belongsTo(Agence::class, 'id_agence', 'idAgence');
    }

    public function caisseUtilisateur()
    {
        return $this->belongsTo(CaisseUtilisateur::class, 'id_caisse_user', 'id_caisse_user');
    }

    public function lignes()
    {
        return $this->hasMany(BordereauEscaleLigne::class, 'id_bordereau', 'id_bordereau');
    }
}
