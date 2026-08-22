<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * Port de Projets_licence/app/models/Partenaire.php (table `partenaire_compte`) — compte
 * d'une compagnie de transport qui s'est inscrite elle-même sur le site public pour
 * discuter avec l'admin (pas d'étape d'approbation : l'inscription crée le compte
 * directement, voir Site\PartenaireController::register()).
 *
 * Authenticatable (guard `partenaire`, voir config/auth.php) : contrairement à l'espace
 * client (pas de mot de passe, voir SuiviColisController), ce compte a un vrai mot de
 * passe — un guard Laravel standard s'applique donc naturellement ici.
 */
class PartenaireCompte extends Authenticatable
{
    protected $table = 'partenaire_compte';

    protected $primaryKey = 'id_partenaire';

    public $timestamps = false;

    protected $fillable = [
        'nom_compagnie',
        'email',
        'mot_de_passe',
        'telephone',
        'date_creation',
    ];

    protected $hidden = [
        'mot_de_passe',
    ];

    public function getAuthPassword(): string
    {
        return $this->mot_de_passe;
    }

    public function getAuthPasswordName(): string
    {
        return 'mot_de_passe';
    }

    public function messages()
    {
        return $this->hasMany(PartenaireMessage::class, 'id_partenaire', 'id_partenaire');
    }

    // Dernier message de la conversation (peu importe l'auteur) — utilisé par l'aperçu
    // admin (liste des partenaires) et pour savoir si la balle est dans le camp de l'admin.
    public function dernierMessage(): ?PartenaireMessage
    {
        return $this->messages()->latest('date_envoi')->first();
    }
}
