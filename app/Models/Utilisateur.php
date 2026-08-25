<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class Utilisateur extends Authenticatable
{
    protected $table = 'utilisateur';
    protected $primaryKey = 'idUser';
    public $timestamps = false;

    protected $fillable = [
        'utilisateurs',
        'droit',
        'contact',
        'motPasse',
        'status',
        'emailUser',
        'telephone',
        'id_agence',
        'id_compagnie',
        'profile',
        'photo',
    ];

    protected $hidden = [
        'motPasse',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'integer',
        ];
    }

    public function getAuthPassword(): string
    {
        return $this->motPasse;
    }

    public function getAuthPasswordName(): string
    {
        return 'motPasse';
    }

    public function isSuperAdmin(): bool
    {
        return $this->droit === 'super_admin';
    }

    public function estLectureSeule(): bool
    {
        return $this->droit === 'PDG';
    }

    public function userHasPermission(string $nomPermission): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return \Illuminate\Support\Facades\DB::table('permision')
            ->join('user_permission', 'permision.id_permision', '=', 'user_permission.permission_id')
            ->where('user_permission.user_id', $this->idUser)
            ->where('permision.nom_permission', $nomPermission)
            ->exists();
    }

    public function compagnie()
    {
        return $this->belongsTo(Compagnie::class, 'id_compagnie', 'id_compagnie');
    }

    public function agence()
    {
        return $this->belongsTo(Agence::class, 'id_agence', 'idAgence');
    }

    // Comptes super_admin garantis présents en base (mêmes 3 comptes que Projets_licence,
    // catalogue et mots de passe dans config/super_admins.php, lus depuis .env) : on vérifie
    // individuellement chacun par email, et on ne (re)crée que ceux qui manquent, plutôt que
    // d'attendre que la table utilisateur entière soit vide. Ça couvre aussi bien une base
    // neuve qu'un vidage partiel (un seul compte supprimé par erreur).
    //
    // Appelée à chaque affichage de la page de connexion (LoginController::create()).
    // Idempotente : firstOrCreate() par email, insertOrIgnore() pour les permissions.
    public static function seedSuperAdminsParDefaut(): void
    {
        Permission::seedPermissionsParDefautSiVide();
        $toutesLesPermissions = Permission::pluck('id_permision');

        foreach (config('super_admins') as $admin) {
            // Mot de passe absent de .env sur cet environnement : on ne crée pas le compte
            // plutôt que de le créer avec un mot de passe vide/prévisible.
            if (empty($admin['motPasse'])) {
                continue;
            }

            $utilisateur = static::firstOrCreate(
                ['emailUser' => $admin['email']],
                [
                    'utilisateurs' => $admin['nom'],
                    'droit' => 'super_admin',
                    'motPasse' => Hash::make($admin['motPasse']),
                    'status' => 1,
                ]
            );

            // Un super_admin a toutes les permissions par conception (voir userHasPermission()),
            // pas seulement à la création : si le catalogue permision était incomplet au moment
            // où ce compte a été créé, on rattrape ici les permissions manquantes.
            $rows = $toutesLesPermissions->map(fn ($idPermission) => [
                'user_id' => $utilisateur->idUser,
                'permission_id' => $idPermission,
            ])->all();

            if (! empty($rows)) {
                DB::table('user_permission')->insertOrIgnore($rows);
            }
        }
    }
}
