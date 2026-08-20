<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Table `client` (port de Projets_licence) : un client est créé à chaque réservation de
 * billet, pas de recherche/réutilisation d'un client existant (fidèle au legacy).
 */
class Client extends Model
{
    protected $table = 'client';

    protected $primaryKey = 'idClient';

    public $timestamps = false;

    protected $fillable = [
        'Client',
        'numeroClient',
        'numeroPaiement',
        'emailClient',
        'codeQuere',
        'montant_payer',
        'date_enregistrement',
        'id_compagnie',
    ];

    public function billets()
    {
        return $this->hasMany(Billet::class, 'id_client', 'idClient');
    }
}
