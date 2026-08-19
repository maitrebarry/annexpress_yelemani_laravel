<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Colis extends Model
{
    protected $table = 'colis';

    protected $primaryKey = 'id_colis';

    public $timestamps = false;

    protected $fillable = [
        'nom_colis',
        'nature',
        'provient_de',
        'id_agence',
        'valeur',
        'fraix_transaction',
        'id_expediteur',
        'id_destinataire',
        'id_utilisateur',
        'date_enregistrement',
        'code_colis',
        'num_gare',
        'status',
        'id_compagnie',
        'id_caisse_user',
        'date_livraison',
        'reclamer',
        'date_reclamer',
        'livre',
        'motif_reclamation',
        'montant_remboursement',
        'status_reclamation',
    ];

    public function expediteur()
    {
        return $this->belongsTo(Expediteur::class, 'id_expediteur', 'id_expediteur');
    }

    public function destinataire()
    {
        return $this->belongsTo(Destinataire::class, 'id_destinataire', 'id_destinataire');
    }

    public function agence()
    {
        return $this->belongsTo(Agence::class, 'id_agence', 'idAgence');
    }

    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class, 'id_utilisateur', 'idUser');
    }

    /**
     * Port du scoping par rôle de Model::FetchSelectColis() (legacy) : un chef_d_escale ne voit
     * que les colis de sa gare, un Utilisateur seulement ceux de sa gare exacte (ville + numéro
     * de gare), les autres rôles voient toute la compagnie.
     */
    public function scopeVisiblePar($query, Utilisateur $user)
    {
        $query->where('colis.id_compagnie', $user->id_compagnie);

        if ($user->droit === 'chef_d_escale') {
            $query->where('colis.provient_de', $user->agence?->localite);
        } elseif ($user->droit === 'Utilisateur') {
            $query->where('colis.provient_de', $user->agence?->localite)
                ->where('colis.num_gare', $user->agence?->numeroGare);
        }

        return $query;
    }

    /**
     * Jointures + colonnes de Model::FetchSelectColis() (legacy) : expéditeur, destinataire,
     * libellé de destination (agence) et nom de l'agent. Les colis sans expéditeur/destinataire
     * valide (INNER JOIN) sont exclus, comme legacy.
     */
    public function scopeAvecDetails($query)
    {
        return $query
            ->join('expediteurs', 'colis.id_expediteur', '=', 'expediteurs.id_expediteur')
            ->join('destinataires', 'colis.id_destinataire', '=', 'destinataires.id_destinataire')
            ->join('agence as a', 'colis.id_agence', '=', 'a.idAgence')
            ->leftJoin('utilisateur', 'utilisateur.idUser', '=', 'colis.id_utilisateur')
            ->select(
                'colis.*',
                'a.localite as destination',
                'expediteurs.expediteur', 'expediteurs.numero_exp', 'expediteurs.whatsapp_exp',
                'destinataires.destinataire', 'destinataires.numero_dest', 'destinataires.whatsapp_dest',
                'utilisateur.utilisateurs as agent_nom'
            );
    }

    /**
     * Port du scoping par rôle de Mouvements_colis (legacy) : filtre par la localité de la gare
     * de DESTINATION (a.localite, via colis.id_agence) — contrairement à scopeVisiblePar() qui
     * filtre par la ville d'ORIGINE (colis.provient_de). Ce module suit les colis qui arrivent
     * dans une gare, pas ceux qui en partent. Nécessite que avecDetails() (jointure vers
     * "agence as a") ait déjà été appliqué. Note : pour "Utilisateur", le filtre combine la
     * localité de destination ET colis.num_gare (numéro de gare d'ORIGINE) — incohérence du
     * legacy assumée telle quelle, pas corrigée.
     */
    public function scopeVisibleALaLivraison($query, Utilisateur $user)
    {
        $query->where('colis.id_compagnie', $user->id_compagnie);

        if ($user->droit === 'chef_d_escale') {
            $query->where('a.localite', $user->agence?->localite);
        } elseif ($user->droit === 'Utilisateur') {
            $query->where('a.localite', $user->agence?->localite)
                ->where('colis.num_gare', $user->agence?->numeroGare);
        }

        return $query;
    }
}
