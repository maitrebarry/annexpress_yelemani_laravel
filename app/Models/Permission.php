<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Permission extends Model
{
    protected $table = 'permision';

    protected $primaryKey = 'id_permision';

    public $timestamps = false;

    protected $fillable = ['nom_permission'];

    // Catalogue des permissions correspondant aux écrans réels de l'admin. La table
    // n'a pas de seed SQL versionné : sur une base neuve elle est vide, donc on la
    // seed ici au premier besoin (cf. seedPermissionsParDefautSiVide()).
    private const NOMS_PERMISSIONS_PAR_DEFAUT = [
        'utilisateur_apercu',
        'utilisateur_creation',
        'utilisateur_modifier',
        'utilisateur_active/desactive',
        'Configuration_apercu',
        'Configuration_gestion_gare',
        'Configuration_gestion_escale',
        'Configuration_gestion_trajets',
        'Configuration_gestion_horaire',
        'Configuration_gestion_car/chauffeur',
        'Configuration_place/limite',
        'Caisse_creation',
        'Caisse_apercue',
        'Caisse_billant',
        'Caisse_modifier',
        'Billets_creation',
        'Billets_apercue',
        'Billets_validation',
        'Billets_historique',
        'Billets_notification',
        'Billets_annulation',
        'Billets_impression',
        'Billets_rapport',
        'Billets_reporte',
        'Billets_embarquement',
        'colis_creation',
        'colis_envoi',
        'colis_mouvement',
        'colis_livraison',
        'colis_reclamation',
        'colis_historique',
        'colis_apercue',
        'Depenses_gestion',
        'Location_gestion',
        'Programme_Creation',
        'Programme_programmer_car',
        'Programme_programmation_voyage',
        'Programme_hors_programme',
        'Salaire_apercu',
    ];

    // Chef d'escale : toutes les permissions par défaut SAUF la programmation fixe et
    // l'affectation des cars sur un trajet, réservées à l'Admin/super_admin.
    private const NOMS_PERMISSIONS_CHEF_ESCALE = [
        'utilisateur_apercu',
        'Configuration_apercu',
        'Configuration_gestion_gare',
        'Configuration_gestion_escale',
        'Configuration_gestion_trajets',
        'Configuration_gestion_horaire',
        'Configuration_gestion_car/chauffeur',
        'Configuration_place/limite',
        'Caisse_creation',
        'Caisse_apercue',
        'Caisse_billant',
        'Caisse_modifier',
        'Billets_creation',
        'Billets_apercue',
        'Billets_validation',
        'Billets_historique',
        'Billets_notification',
        'Billets_annulation',
        'Billets_impression',
        'Billets_rapport',
        'Billets_reporte',
        'Billets_embarquement',
        'colis_creation',
        'colis_envoi',
        'colis_mouvement',
        'colis_livraison',
        'colis_reclamation',
        'colis_historique',
        'colis_apercue',
        'Depenses_gestion',
        'Location_gestion',
        'Programme_programmation_voyage',
        'Programme_hors_programme',
    ];

    // Utilisateur simple, service "Billetterie" : écrans billets + sa propre caisse.
    private const NOMS_PERMISSIONS_BILLET = [
        'Billets_creation',
        'Billets_apercue',
        'Billets_validation',
        'Billets_historique',
        'Billets_notification',
        'Billets_impression',
        'Billets_rapport',
        'Billets_reporte',
        'Billets_embarquement',
        'Caisse_creation',
        'Caisse_apercue',
        'Caisse_modifier',
    ];

    // Utilisateur simple, service "Colis / Courrier" : uniquement les écrans colis.
    private const NOMS_PERMISSIONS_COLIS = [
        'colis_creation',
        'colis_envoi',
        'colis_mouvement',
        'colis_livraison',
        'colis_reclamation',
        'colis_historique',
        'colis_apercue',
    ];

    public static function seedPermissionsParDefautSiVide(): void
    {
        $existants = static::pluck('nom_permission')->all();

        foreach (self::NOMS_PERMISSIONS_PAR_DEFAUT as $nom) {
            if (! in_array($nom, $existants, true)) {
                static::create(['nom_permission' => $nom]);
            }
        }
    }

    // Assigne à un utilisateur nouvellement créé le jeu de permissions correspondant à
    // son droit (et, pour un simple Utilisateur, à son service billet/colis).
    public static function assignPermissionsParDefautPourRole(int $idUtilisateur, string $droit, ?string $profile = null): void
    {
        $noms = match (true) {
            in_array($droit, ['super_admin', 'Admin', 'PDG'], true) => self::NOMS_PERMISSIONS_PAR_DEFAUT,
            $droit === 'chef_d_escale' => self::NOMS_PERMISSIONS_CHEF_ESCALE,
            $droit === 'Utilisateur' && $profile === 'billet' => self::NOMS_PERMISSIONS_BILLET,
            $droit === 'Utilisateur' && $profile === 'colis' => self::NOMS_PERMISSIONS_COLIS,
            default => null,
        };

        if ($noms === null) {
            return;
        }

        self::seedPermissionsParDefautSiVide();

        $idsParNom = static::whereIn('nom_permission', $noms)->pluck('id_permision', 'nom_permission');

        $rows = $idsParNom->values()->map(fn ($idPermission) => [
            'user_id' => $idUtilisateur,
            'permission_id' => $idPermission,
        ])->all();

        if (! empty($rows)) {
            DB::table('user_permission')->insertOrIgnore($rows);
        }
    }

    // IDs des permissions actuellement assignées à un utilisateur (écran d'assignation).
    public static function getUserPermissionIds(int $idUtilisateur): array
    {
        return DB::table('user_permission')->where('user_id', $idUtilisateur)->pluck('permission_id')->all();
    }

    // Remplace entièrement le jeu de permissions d'un utilisateur par celui fourni
    // (décoché = retiré). Utilisé par l'écran "Assigner les permissions".
    public static function syncUserPermissions(int $idUtilisateur, array $permissionIds): void
    {
        DB::table('user_permission')->where('user_id', $idUtilisateur)->delete();

        $rows = collect($permissionIds)->map(fn ($id) => [
            'user_id' => $idUtilisateur,
            'permission_id' => (int) $id,
        ])->all();

        if (! empty($rows)) {
            DB::table('user_permission')->insertOrIgnore($rows);
        }
    }
}
