<?php

namespace Database\Seeders;

use App\Models\Agence;
use App\Models\Compagnie;
use App\Models\Permission;
use App\Models\Utilisateur;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class StaffDemoSeeder extends Seeder
{
    /**
     * Seed a super_admin (multi-compagnie) and a compagnie-scoped user so the
     * login page can be exercised on both the admin side and the site side.
     */
    public function run(): void
    {
        $superAdmin = Utilisateur::updateOrCreate(
            ['emailUser' => 'superadmin@transhub.test'],
            [
                'utilisateurs' => 'Super Admin',
                'droit' => 'super_admin',
                'motPasse' => Hash::make('password'),
                'status' => 1,
                'id_agence' => null,
                'id_compagnie' => null,
            ]
        );
        Permission::assignPermissionsParDefautPourRole($superAdmin->idUser, 'super_admin');

        $compagnie = Compagnie::firstOrCreate(
            ['nom_compagnie' => 'ANN EXPRESS'],
            ['libele' => 'ANN Express Voyages', 'slogant' => 'Voyagez en confiance']
        );

        $agence = Agence::firstOrCreate(
            ['id_compagnie' => $compagnie->id_compagnie, 'localite' => 'Bamako'],
            ['code' => 1, 'numeroGare' => 'BKO-01', 'tel' => '+22300000000', 'status' => 1]
        );

        $adminCompagnie = Utilisateur::updateOrCreate(
            ['emailUser' => 'admin.compagnie@transhub.test'],
            [
                'utilisateurs' => 'Admin Compagnie',
                'droit' => 'Admin',
                'motPasse' => Hash::make('password'),
                'status' => 1,
                'id_agence' => $agence->idAgence,
                'id_compagnie' => $compagnie->id_compagnie,
            ]
        );
        Permission::assignPermissionsParDefautPourRole($adminCompagnie->idUser, 'Admin');
    }
}
