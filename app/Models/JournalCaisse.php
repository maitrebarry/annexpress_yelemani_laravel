<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Port de la table `journal_caisse` : journal (append-only) des mouvements d'une
 * caisse individuelle — une ligne par ouverture/vente billet/vente colis/fermeture/
 * versement.
 */
class JournalCaisse extends Model
{
    protected $table = 'journal_caisse';
    protected $primaryKey = 'id_journal';
    public $timestamps = false;

    protected $fillable = [
        'id_caisse_user',
        'id_utilisateur',
        'type_operation',
        'reference_op',
        'montant',
        'libelle',
    ];

    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class, 'id_utilisateur', 'idUser');
    }
}
