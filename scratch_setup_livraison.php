<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

// Grant colis_livraison to existing test accounts
$id = DB::table('permision')->where('nom_permission', 'colis_livraison')->value('id_permision');
if (! $id) {
    $id = DB::table('permision')->insertGetId(['nom_permission' => 'colis_livraison']);
    echo "Created permission colis_livraison (id=$id)\n";
}

$adminUser = App\Models\Utilisateur::where('emailUser', 'admin.compagnie@transhub.test')->first();
if (! DB::table('user_permission')->where('permission_id', $id)->where('user_id', $adminUser->idUser)->exists()) {
    DB::table('user_permission')->insert(['permission_id' => $id, 'user_id' => $adminUser->idUser]);
}

// Check colis 3 (pochete) destination agence
$colis = DB::table('colis')->where('id_colis', 3)->first();
echo "colis id=3 status={$colis->status} id_agence={$colis->id_agence} code={$colis->code_colis}\n";
$agence = DB::table('agence')->where('idAgence', $colis->id_agence)->first();
echo "destination agence: localite={$agence->localite} numeroGare={$agence->numeroGare}\n";

// Create a chef_d_escale test account at that destination agence
$chef = App\Models\Utilisateur::updateOrCreate(
    ['emailUser' => 'chef.test@transhub.test'],
    [
        'utilisateurs' => 'Chef Escale Test',
        'droit' => 'chef_d_escale',
        'motPasse' => Hash::make('password'),
        'status' => 1,
        'id_agence' => $colis->id_agence,
        'id_compagnie' => $adminUser->id_compagnie,
    ]
);
if (! DB::table('user_permission')->where('permission_id', $id)->where('user_id', $chef->idUser)->exists()) {
    DB::table('user_permission')->insert(['permission_id' => $id, 'user_id' => $chef->idUser]);
}
echo "chef_d_escale user id={$chef->idUser}\n";
