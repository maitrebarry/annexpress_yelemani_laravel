<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

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
}
