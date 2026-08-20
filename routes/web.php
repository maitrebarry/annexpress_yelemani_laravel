<?php

use App\Http\Controllers\Admin\CaisseController;
use App\Http\Controllers\Admin\CarController;
use App\Http\Controllers\Admin\ChauffeurController;
use App\Http\Controllers\Admin\ColisPriseEnChargeController;
use App\Http\Controllers\Admin\CompagnieController;
use App\Http\Controllers\Admin\ConfigurationController;
use App\Http\Controllers\Admin\EnvoiColisController;
use App\Http\Controllers\Admin\EscaleController;
use App\Http\Controllers\Admin\GaresController;
use App\Http\Controllers\Admin\HomeController;
use App\Http\Controllers\Admin\HoraireController;
use App\Http\Controllers\Admin\LivraisonColisController;
use App\Http\Controllers\Admin\MouvementColisController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\PlaceLimiteController;
use App\Http\Controllers\Admin\ProgrammationCarController;
use App\Http\Controllers\Admin\ProgrammationVoyageController;
use App\Http\Controllers\Admin\ProgrammeController;
use App\Http\Controllers\Admin\TransfertGareController;
use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (Auth::guard('staff')->check()) {
        return redirect()->route('admin.home');
    }

    return redirect()->route('login');
});

Route::middleware('guest:staff')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});

Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth:staff')
    ->name('logout');

Route::middleware('auth:staff')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/Homes/home', [HomeController::class, 'index'])->name('home');
});

Route::middleware(['auth:staff', 'permission:Configuration_gestion_gare'])->prefix('admin')->group(function () {
    Route::get('/Liste_gares', [GaresController::class, 'index'])->name('admin.gares.index');
    Route::post('/Liste_gares/add_gares', [GaresController::class, 'store'])->name('admin.gares.store');
    Route::post('/Liste_gares/edit', [GaresController::class, 'update'])->name('admin.gares.update');
    Route::get('/Liste_gares/suspend/{idAgence}', [GaresController::class, 'suspend'])->name('admin.gares.suspend');
    Route::get('/Liste_gares/delete/{idAgence}', [GaresController::class, 'destroy'])->name('admin.gares.destroy');
});

Route::middleware(['auth:staff', 'super_admin'])->prefix('admin')->group(function () {
    Route::get('/Compagnies', [CompagnieController::class, 'index'])->name('admin.compagnie.index');
    Route::post('/Compagnies/store', [CompagnieController::class, 'store'])->name('admin.compagnie.store');
    Route::post('/Compagnies/edit', [CompagnieController::class, 'update'])->name('admin.compagnie.update');
    Route::get('/Compagnies/delete/{idCompagnie}', [CompagnieController::class, 'destroy'])->name('admin.compagnie.destroy');
});

Route::middleware(['auth:staff', 'permission:Configuration_gestion_escale'])->prefix('admin')->group(function () {
    Route::get('/Add_liste_escales', [EscaleController::class, 'index'])->name('admin.escale.index');
    Route::post('/Add_liste_escales/store', [EscaleController::class, 'store'])->name('admin.escale.store');
    Route::post('/Add_liste_escales/update', [EscaleController::class, 'update'])->name('admin.escale.update');
    Route::get('/Add_liste_escales/delete/{idEscale}', [EscaleController::class, 'destroy'])->name('admin.escale.destroy');
});

Route::middleware(['auth:staff', 'permission:Configuration_gestion_horaire'])->prefix('admin')->group(function () {
    Route::get('/Add_liste_horaire', [HoraireController::class, 'index'])->name('admin.horaire.index');
    Route::post('/Add_liste_horaire/store', [HoraireController::class, 'store'])->name('admin.horaire.store');
    Route::post('/Add_liste_horaire/edit', [HoraireController::class, 'update'])->name('admin.horaire.update');
    Route::get('/Add_liste_horaire/delete/{idHeure}', [HoraireController::class, 'destroy'])->name('admin.horaire.destroy');
});

Route::middleware(['auth:staff', 'permission:Configuration_gestion_car/chauffeur'])->prefix('admin')->group(function () {
    Route::get('/Cars_chauffeurs', [CarController::class, 'index'])->name('admin.car.index');
    Route::post('/Cars_chauffeurs/store', [CarController::class, 'store'])->name('admin.car.store');
    Route::post('/Cars_chauffeurs/update', [CarController::class, 'update'])->name('admin.car.update');
    Route::get('/Cars_chauffeurs/delete/{idCar}', [CarController::class, 'destroy'])->name('admin.car.destroy');

    Route::post('/Chauffeurs_cars/store', [ChauffeurController::class, 'store'])->name('admin.chauffeur.store');
    Route::post('/Chauffeurs_cars/update', [ChauffeurController::class, 'update'])->name('admin.chauffeur.update');
    Route::get('/Chauffeurs_cars/delete/{idChauffeur}', [ChauffeurController::class, 'destroy'])->name('admin.chauffeur.destroy');
});

Route::middleware(['auth:staff', 'permission:utilisateur_apercu'])->prefix('admin')->group(function () {
    Route::get('/Configurations', [ConfigurationController::class, 'index'])->name('admin.configuration.index');
    Route::post('/Configurations/store', [ConfigurationController::class, 'store'])->name('admin.configuration.store');
    Route::post('/Configurations/update', [ConfigurationController::class, 'update'])->name('admin.configuration.update');
    Route::post('/Configurations/status', [ConfigurationController::class, 'updateStatus'])->name('admin.configuration.status');
    Route::post('/Configurations/destroy', [ConfigurationController::class, 'destroy'])->name('admin.configuration.destroy');
});

Route::middleware(['auth:staff', 'super_admin'])->prefix('admin')->group(function () {
    Route::get('/Add_liste_horaire/add_permission', [PermissionController::class, 'catalogue'])->name('admin.permission.catalogue');
    Route::post('/Add_liste_horaire/add_permission', [PermissionController::class, 'storeCatalogue'])->name('admin.permission.catalogue.store');
});

Route::middleware('auth:staff')->prefix('admin')->group(function () {
    Route::match(['get', 'post'], '/Permissions/assigner/{idUtilisateur}', [PermissionController::class, 'assigner'])->name('admin.permission.assigner');
});

Route::middleware(['auth:staff', 'permission:Configuration_place/limite'])->prefix('admin')->group(function () {
    Route::get('/Compagnies/place_limite', [PlaceLimiteController::class, 'index'])->name('admin.place-limite.index');
    Route::post('/Compagnies/edit1', [PlaceLimiteController::class, 'update'])->name('admin.place-limite.update');
});

Route::middleware(['auth:staff', 'permission:Programme_Creation'])->prefix('admin')->group(function () {
    Route::get('/Programmer_voyages', [ProgrammeController::class, 'index'])->name('admin.programme.index');
    Route::get('/Programmer_voyages/add_programmer', [ProgrammeController::class, 'create'])->name('admin.programme.create');
    Route::post('/Programmer_voyages/add_programmer', [ProgrammeController::class, 'store'])->name('admin.programme.store');
    Route::post('/Programmer_voyages/edit', [ProgrammeController::class, 'update'])->name('admin.programme.update');
    Route::get('/Programmer_voyages/delete/{idProgrammer}', [ProgrammeController::class, 'destroy'])->name('admin.programme.destroy');
});

Route::middleware(['auth:staff', 'permission:Programme_programmer_car'])->prefix('admin')->group(function () {
    Route::get('/Programmation_cars', [ProgrammationCarController::class, 'index'])->name('admin.programmation-car.index');
    Route::post('/Programmation_cars/store', [ProgrammationCarController::class, 'store'])->name('admin.programmation-car.store');
    Route::post('/Programmation_cars/ajouter_trajet', [ProgrammationCarController::class, 'ajouterTrajet'])->name('admin.programmation-car.ajouter-trajet');
    Route::get('/Programmation_cars/supprimer/{idCar}', [ProgrammationCarController::class, 'destroy'])->name('admin.programmation-car.destroy');
});

Route::middleware(['auth:staff', 'permission:Programme_programmation_voyage'])->prefix('admin')->group(function () {
    Route::get('/Programmation_voyages', [ProgrammationVoyageController::class, 'dashboard'])->name('admin.programmation-voyage.dashboard');
    Route::post('/Programmation_voyages/store', [ProgrammationVoyageController::class, 'store'])->name('admin.programmation-voyage.store');
    Route::post('/Programmation_voyages/valider-arrivee', [ProgrammationVoyageController::class, 'validerArrivee'])->name('admin.programmation-voyage.valider-arrivee');
    Route::post('/Programmation_voyages/debloquer-arrive', [ProgrammationVoyageController::class, 'debloquerArrive'])->name('admin.programmation-voyage.debloquer-arrive');
    Route::post('/Programmation_voyages/debloquer-jamais-parti', [ProgrammationVoyageController::class, 'debloquerJamaisParti'])->name('admin.programmation-voyage.debloquer-jamais-parti');
    Route::get('/Programmation_voyages/liste_programmer_voyage', [ProgrammationVoyageController::class, 'listeJournaliere'])->name('admin.programmation-voyage.liste-journaliere');
    Route::get('/Programmation_voyages/edit/{idProgrammation}', [ProgrammationVoyageController::class, 'edit'])->name('admin.programmation-voyage.edit');
    Route::post('/Programmation_voyages/edit/{idProgrammation}', [ProgrammationVoyageController::class, 'update'])->name('admin.programmation-voyage.update');
});

Route::middleware('auth:staff')->prefix('admin')->group(function () {
    Route::get('/Transferts_gares/candidats/{idProgrammation}', [TransfertGareController::class, 'candidats'])->name('admin.transfert-gare.candidats');
    Route::post('/Transferts_gares/executer', [TransfertGareController::class, 'executer'])->name('admin.transfert-gare.executer');
});

Route::middleware(['auth:staff', 'permission:Programme_programmation_voyage'])->prefix('admin')->group(function () {
    Route::get('/Transferts_gares/historique', [TransfertGareController::class, 'historique'])->name('admin.transfert-gare.historique');
});

Route::middleware('auth:staff')->prefix('admin')->group(function () {
    Route::get('/Colis_prise_en_charges', [ColisPriseEnChargeController::class, 'index'])->name('admin.colis.index');
    Route::post('/Colis_prise_en_charges', [ColisPriseEnChargeController::class, 'update'])->name('admin.colis.update');

    Route::middleware('permission:colis_creation')->group(function () {
        Route::get('/Colis_prise_en_charges/ajouter_colis', [ColisPriseEnChargeController::class, 'create'])->name('admin.colis.create');
        Route::post('/Colis_prise_en_charges/ajouter_colis', [ColisPriseEnChargeController::class, 'store'])->name('admin.colis.store');
    });

    Route::middleware('permission:colis_envoi')->group(function () {
        Route::get('/Envoi_colis/envoi_colis', [EnvoiColisController::class, 'create'])->name('admin.colis.envoi.create');
        Route::post('/Envoi_colis/envoi_colis', [EnvoiColisController::class, 'store'])->name('admin.colis.envoi.store');
        Route::get('/Envoi_colis/liste_colis_envoyer', [EnvoiColisController::class, 'index'])->name('admin.colis.envoi.index');
        Route::get('/Envoi_colis/details_colis_envoyer', [EnvoiColisController::class, 'details'])->name('admin.colis.envoi.details');
        Route::post('/Envoi_colis/changer_car', [EnvoiColisController::class, 'changerCar'])->name('admin.colis.envoi.changer-car');
        Route::get('/Envoi_colis/annuler_envoi', [EnvoiColisController::class, 'annuler'])->name('admin.colis.envoi.annuler');
    });

    Route::middleware('permission:colis_mouvement')->group(function () {
        Route::get('/Mouvement_colis', [MouvementColisController::class, 'index'])->name('admin.colis.mouvement.index');
        Route::post('/Mouvement_colis', [MouvementColisController::class, 'receive'])->name('admin.colis.mouvement.receive');
    });

    Route::middleware('permission:colis_livraison')->group(function () {
        Route::get('/Livraison_colis', [LivraisonColisController::class, 'index'])->name('admin.colis.livraison.index');
        Route::post('/Livraison_colis', [LivraisonColisController::class, 'store'])->name('admin.colis.livraison.store');
    });
});

Route::middleware(['auth:staff', 'permission:Caisse_apercue'])->prefix('admin')->group(function () {
    Route::get('/Caisse/ma_caisse', [CaisseController::class, 'maCaisse'])->name('admin.caisse.ma-caisse');
    Route::post('/Caisse/ouvrir_caisse_user', [CaisseController::class, 'ouvrirCaisse'])->name('admin.caisse.ouvrir-caisse');
    Route::post('/Caisse/fermer_caisse_user', [CaisseController::class, 'fermerCaisse'])->name('admin.caisse.fermer-caisse');
    Route::get('/Caisse/caisses_escale', [CaisseController::class, 'caissesEscale'])->name('admin.caisse.caisses-escale');
    Route::get('/Caisse/rapport_proprietaire', [CaisseController::class, 'rapportProprietaire'])->name('admin.caisse.rapport-proprietaire');

    Route::middleware('permission:Caisse_billant')->group(function () {
        Route::get('/Caisse/bilant_caisse_billets', [CaisseController::class, 'bilantBillets'])->name('admin.caisse.bilant-billets');
        Route::get('/Caisse/bilant_caisse_colis', [CaisseController::class, 'bilantColis'])->name('admin.caisse.bilant-colis');
        Route::get('/Caisse/mouvements/{id}', [CaisseController::class, 'mouvements'])->name('admin.caisse.mouvements');
    });

    Route::middleware('permission:Caisse_modifier')->group(function () {
        Route::post('/Caisse/verser', [CaisseController::class, 'verser'])->name('admin.caisse.verser');
        Route::post('/Caisse/valider_versement', [CaisseController::class, 'validerVersement'])->name('admin.caisse.valider-versement');
        Route::post('/Caisse/cloture_escale', [CaisseController::class, 'clotureEscale'])->name('admin.caisse.cloture-escale');
    });
});
