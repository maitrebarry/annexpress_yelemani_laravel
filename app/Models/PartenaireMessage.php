<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Port de Projets_licence/app/models/Partenaire_message.php (table `partenaire_message`)
 * — un message dans la conversation entre un partenaire et l'admin. `auteur` vaut
 * 'partenaire' ou 'admin' (pas d'ENUM en base, valeur libre fidèle au legacy).
 */
class PartenaireMessage extends Model
{
    protected $table = 'partenaire_message';

    protected $primaryKey = 'id_message';

    public $timestamps = false;

    protected $fillable = [
        'id_partenaire',
        'auteur',
        'message',
        'date_envoi',
    ];

    protected $casts = [
        'date_envoi' => 'datetime',
    ];

    public function partenaire()
    {
        return $this->belongsTo(PartenaireCompte::class, 'id_partenaire', 'id_partenaire');
    }
}
