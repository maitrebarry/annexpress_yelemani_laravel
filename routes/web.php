<?php

use App\Http\Controllers\Admin\ColisPriseEnChargeController;
use App\Http\Controllers\Admin\EnvoiColisController;
use App\Http\Controllers\Admin\GaresController;
use App\Http\Controllers\Admin\HomeController;
use App\Http\Controllers\Admin\LivraisonColisController;
use App\Http\Controllers\Admin\MouvementColisController;
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
