<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Port de Projets_licence/app/models/Employe.php — voir GESTION_SALAIRES.md.
 */
class Employe extends Model
{
    protected $table = 'employe';

    protected $primaryKey = 'id_employe';

    public $timestamps = false;

    protected $fillable = [
        'id_utilisateur',
        'id_chauffeur',
        'nom_prenom',
        'poste',
        'id_agence',
        'id_compagnie',
        'salaire_base',
        'statut',
        'date_creation',
    ];

    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class, 'id_utilisateur', 'idUser');
    }

    public function chauffeur()
    {
        return $this->belongsTo(Chauffeur::class, 'id_chauffeur', 'id_chauffeur');
    }

    public function agence()
    {
        return $this->belongsTo(Agence::class, 'id_agence', 'idAgence');
    }

    // Nom affiché : celui du compte utilisateur, ou de la fiche chauffeur, ou (personnel
    // hors-système) celui saisi directement sur la fiche employé.
    private static function selectAvecNoms(): Builder
    {
        return static::query()
            ->leftJoin('utilisateur', 'employe.id_utilisateur', '=', 'utilisateur.idUser')
            ->leftJoin('chauffeur', 'employe.id_chauffeur', '=', 'chauffeur.id_chauffeur')
            ->leftJoin('agence', 'employe.id_agence', '=', 'agence.idAgence')
            ->select(
                'employe.*',
                DB::raw('COALESCE(utilisateur.utilisateurs, chauffeur.nom_prenom, employe.nom_prenom) AS nom_affiche'),
                'agence.localite',
                'agence.numeroGare'
            );
    }

    // Liste des employés visibles pour l'utilisateur connecté, calquée sur
    // DepenseService::getDepenses() : un chef d'escale (ou tout rôle rattaché à une gare
    // précise via id_agence) ne voit QUE le personnel de SA gare, jamais celui d'une autre
    // gare ni le personnel compagnie-entière (chauffeurs, Admin/PDG, hors-système sans
    // gare) — ces derniers n'ont d'ailleurs pas vocation à être "ses" employés. Un
    // Admin/PDG/secretaire (id_agence NULL) voit toute la compagnie. Le contrôleur
    // garantit déjà que cette méthode n'est jamais atteinte sans la permission
    // Salaire_apercu.
    public static function getEmployesVisibles(Utilisateur $user)
    {
        $query = self::selectAvecNoms();

        if ($user->droit === 'super_admin') {
            return $query->orderBy('nom_affiche')->get();
        }

        $query->where('employe.id_compagnie', $user->id_compagnie);

        if (! empty($user->id_agence)) {
            $query->where('employe.id_agence', $user->id_agence);
        }

        return $query->orderBy('nom_affiche')->get();
    }

    // Une seule fiche employe, avec re-vérification IDOR (compagnie, et gare si
    // l'appelant est scopé) indépendamment de getEmployesVisibles().
    public static function getEmployeVisibleById(Utilisateur $user, int $id): ?self
    {
        $query = self::selectAvecNoms()->where('employe.id_employe', $id);

        if ($user->droit !== 'super_admin') {
            $query->where('employe.id_compagnie', $user->id_compagnie);

            if (! empty($user->id_agence)) {
                $query->where('employe.id_agence', $user->id_agence);
            }
        }

        return $query->first();
    }

    // Hook additif appelé par ConfigurationController::store() après la création d'un
    // nouveau compte (hors super_admin) : lui crée directement sa fiche employe (salaire
    // à 0, à renseigner ensuite par l'Admin depuis "Salaires").
    public static function creerEmployePourUtilisateur(int $idUtilisateur, string $droit, ?int $idAgence, ?int $idCompagnie): void
    {
        if ($droit === 'super_admin') {
            return;
        }

        static::create([
            'id_utilisateur' => $idUtilisateur,
            'poste' => $droit,
            'id_agence' => $idAgence,
            'id_compagnie' => $idCompagnie,
            'salaire_base' => 0,
            'statut' => 'actif',
            'date_creation' => now()->toDateString(),
        ]);
    }

    // Hook additif équivalent, appelé par ChauffeurController::store() après la création
    // d'un nouveau chauffeur (car ou camion).
    public static function creerEmployePourChauffeur(int $idChauffeur, ?int $idCompagnie): void
    {
        static::create([
            'id_chauffeur' => $idChauffeur,
            'poste' => 'Chauffeur',
            'id_agence' => null,
            'id_compagnie' => $idCompagnie,
            'salaire_base' => 0,
            'statut' => 'actif',
            'date_creation' => now()->toDateString(),
        ]);
    }
}
