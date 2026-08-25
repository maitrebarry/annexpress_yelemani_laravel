<?php

use App\Http\Controllers\Admin\BanqueController;
use App\Http\Controllers\Admin\BilletController;
use App\Http\Controllers\Admin\CaisseController;
use App\Http\Controllers\Admin\CarController;
use App\Http\Controllers\Admin\ChauffeurController;
use App\Http\Controllers\Admin\ColisPriseEnChargeController;
use App\Http\Controllers\Admin\CompagnieController;
use App\Http\Controllers\Admin\ConfigurationController;
use App\Http\Controllers\Admin\DepenseController;
use App\Http\Controllers\Admin\DepotBanqueController;
use App\Http\Controllers\Admin\EmployeController;
use App\Http\Controllers\Admin\EnvoiColisController;
use App\Http\Controllers\Admin\EscaleController;
use App\Http\Controllers\Admin\FlotteController;
use App\Http\Controllers\Admin\GaresController;
use App\Http\Controllers\Admin\HistoriqueColisController;
use App\Http\Controllers\Admin\HomeController;
use App\Http\Controllers\Admin\HoraireController;
use App\Http\Controllers\Admin\ListeEntenteController;
use App\Http\Controllers\Admin\LivraisonColisController;
use App\Http\Controllers\Admin\LocationCarController;
use App\Http\Controllers\Admin\MouvementColisController;
use App\Http\Controllers\Admin\PartenariatController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\PlaceLimiteController;
use App\Http\Controllers\Admin\ProgrammationCarController;
use App\Http\Controllers\Admin\ProgrammationVoyageController;
use App\Http\Controllers\Admin\ProgrammeController;
use App\Http\Controllers\Admin\RapportBilletController;
use App\Http\Controllers\Admin\ReclamationColisController;
use App\Http\Controllers\Admin\TransfertGareController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Site\CompagnieController as SiteCompagnieController;
use App\Http\Controllers\Site\ContactController as SiteContactController;
use App\Http\Controllers\Site\HomeController as SiteHomeController;
use App\Http\Controllers\Site\PartenaireController as SitePartenaireController;
use App\Http\Controllers\Site\RechercheController as SiteRechercheController;
use App\Http\Controllers\Site\ReservationController as SiteReservationController;
use App\Http\Controllers\Site\SuiviColisController as SiteSuiviColisController;
use Illuminate\Support\Facades\Route;

// Site public (vitrine) : pas de guard, accessible à tous. L'admin (staff) y accède via
// le lien "Espace pro" du menu, qui mène à /login.
Route::name('site.')->group(function () {
    Route::get('/', [SiteHomeController::class, 'index'])->name('home');
    Route::get('/compagnies', [SiteCompagnieController::class, 'index'])->name('compagnies');
    // Pas de suffixe /trajets : cette page joue le rôle de "mini-site" propre à la
    // compagnie (voir site/partials/nav.blade.php) plutôt qu'une simple sous-page listant
    // ses trajets — l'URL doit ressembler à sa propre page, pas à un détail imbriqué.
    Route::get('/compagnies/{compagnie}', [SiteCompagnieController::class, 'show'])->name('compagnie.trajets');
    Route::get('/recherche', [SiteRechercheController::class, 'index'])->name('recherche');
    Route::get('/suivi-colis', [SiteSuiviColisController::class, 'index'])->name('suivi-colis');
    Route::get('/reservation/{id}/donnees', [SiteReservationController::class, 'donnees'])->name('reservation.donnees');
    Route::post('/reservation', [SiteReservationController::class, 'store'])->name('reservation.store');
    Route::get('/billet/{numeroBillets}', [SiteReservationController::class, 'billet'])->name('billet');
    Route::get('/contact', [SiteContactController::class, 'index'])->name('contact');

    // Espace partenaire (guard `partenaire`, voir config/auth.php).
    Route::middleware('guest:partenaire')->group(function () {
        Route::get('/espace-partenaire', [SitePartenaireController::class, 'login'])->name('partenaire.login');
        Route::post('/espace-partenaire/connexion', [SitePartenaireController::class, 'connexion'])->name('partenaire.connexion');
        Route::post('/espace-partenaire/inscription', [SitePartenaireController::class, 'inscription'])->name('partenaire.inscription');
    });
    Route::middleware('auth:partenaire')->group(function () {
        Route::get('/espace-partenaire/discussion', [SitePartenaireController::class, 'discussion'])->name('partenaire.discussion');
        Route::post('/espace-partenaire/discussion', [SitePartenaireController::class, 'envoyerMessage'])->name('partenaire.message');
        Route::post('/espace-partenaire/deconnexion', [SitePartenaireController::class, 'deconnexion'])->name('partenaire.deconnexion');
    });
});

Route::middleware('guest:staff')->group(function () {
    Route::get('/admin', [LoginController::class, 'create'])->name('login');
    Route::post('/admin', [LoginController::class, 'store'])->name('login.store');
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

Route::middleware(['auth:staff', 'super_admin'])->prefix('admin')->group(function () {
    Route::get('/Partenariats', [PartenariatController::class, 'index'])->name('admin.partenariat.index');
    Route::post('/Partenariats/repondre', [PartenariatController::class, 'repondre'])->name('admin.partenariat.repondre');
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
    Route::get('/Flotte', [FlotteController::class, 'index'])->name('admin.flotte.index');
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

    Route::middleware('permission:colis_reclamation')->group(function () {
        Route::get('/Reclamations', [ReclamationColisController::class, 'index'])->name('admin.colis.reclamation.index');
        Route::get('/Reclamations/rechercher', [ReclamationColisController::class, 'rechercher'])->name('admin.colis.reclamation.rechercher');
        Route::post('/Reclamations', [ReclamationColisController::class, 'store'])->name('admin.colis.reclamation.store');
        Route::post('/Reclamations/statut', [ReclamationColisController::class, 'updateStatus'])->name('admin.colis.reclamation.statut');
    });

    Route::middleware('permission:colis_historique')->group(function () {
        Route::get('/Historiques/historique_colis_enregistrer', [HistoriqueColisController::class, 'index'])->name('admin.colis.historique.index');
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

Route::middleware(['auth:staff', 'permission:Depenses_gestion'])->prefix('admin')->group(function () {
    Route::get('/Depenses', [DepenseController::class, 'index'])->name('admin.depense.index');
    Route::post('/Depenses', [DepenseController::class, 'store'])->name('admin.depense.store');
    Route::get('/Depenses/benefice', [DepenseController::class, 'benefice'])->name('admin.depense.benefice');
    Route::post('/Depenses/valider/{id}', [DepenseController::class, 'valider'])->name('admin.depense.valider');
    Route::post('/Depenses/rejeter/{id}', [DepenseController::class, 'rejeter'])->name('admin.depense.rejeter');
});

// Pas de middleware permission unique : la page est visible si l'utilisateur a AU MOINS
// une des deux permissions (utilisateurs OU chauffeurs) — vérifié en contrôleur, comme le
// legacy (Employes::__construct()).
Route::middleware('auth:staff')->prefix('admin')->group(function () {
    Route::get('/Employes', [EmployeController::class, 'index'])->name('admin.employe.index');
    Route::get('/Employes/listeImprimable', [EmployeController::class, 'listeImprimable'])->name('admin.employe.liste-imprimable');
    Route::get('/Employes/printCard/{type}/{id}', [EmployeController::class, 'printCard'])->name('admin.employe.print-card');
    Route::post('/Employes/printSelection', [EmployeController::class, 'printSelection'])->name('admin.employe.print-selection');
});

Route::middleware(['auth:staff', 'permission:Location_gestion'])->prefix('admin')->group(function () {
    Route::get('/Locations_cars', [LocationCarController::class, 'index'])->name('admin.location-car.index');
    Route::post('/Locations_cars', [LocationCarController::class, 'store'])->name('admin.location-car.store');
    Route::post('/Locations_cars/ajaxCarsDisponibles', [LocationCarController::class, 'ajaxCarsDisponibles'])->name('admin.location-car.ajax-cars-disponibles');
    Route::post('/Locations_cars/valider/{id}', [LocationCarController::class, 'valider'])->name('admin.location-car.valider');
    Route::post('/Locations_cars/rejeter/{id}', [LocationCarController::class, 'rejeter'])->name('admin.location-car.rejeter');
    Route::get('/Locations_cars/facture/{id}', [LocationCarController::class, 'facture'])->name('admin.location-car.facture');
});

// Pas de permission dédiée en base pour Banque/Dépôts_banque (le legacy gate ces écrans
// par rôle uniquement, comme TransfertGareController) : gating fait en contrôleur.
Route::middleware('auth:staff')->prefix('admin')->group(function () {
    Route::get('/Banques', [BanqueController::class, 'index'])->name('admin.banque.index');
    Route::post('/Banques', [BanqueController::class, 'store'])->name('admin.banque.store');
    Route::post('/Banques/{id}', [BanqueController::class, 'update'])->name('admin.banque.update');
    Route::get('/Banques/mouvement/{id}', [BanqueController::class, 'mouvements'])->name('admin.banque.mouvements');

    Route::get('/Depots_banque', [DepotBanqueController::class, 'index'])->name('admin.depot-banque.index');
    Route::post('/Depots_banque', [DepotBanqueController::class, 'store'])->name('admin.depot-banque.store');
    Route::get('/Depots_banque/enAttente', [DepotBanqueController::class, 'enAttente'])->name('admin.depot-banque.en-attente');
    Route::post('/Depots_banque/confirmer/{id}', [DepotBanqueController::class, 'confirmer'])->name('admin.depot-banque.confirmer');
    Route::post('/Depots_banque/rejeter/{id}', [DepotBanqueController::class, 'rejeter'])->name('admin.depot-banque.rejeter');
    Route::get('/Depots_banque/historique', [DepotBanqueController::class, 'historique'])->name('admin.depot-banque.historique');
});

Route::middleware(['auth:staff', 'permission:Billets_creation'])->prefix('admin')->group(function () {
    Route::get('/Add_billets', [BilletController::class, 'create'])->name('admin.billet.create');
    Route::post('/Add_billets', [BilletController::class, 'store'])->name('admin.billet.store');
});

Route::middleware(['auth:staff', 'permission:Billets_apercue'])->prefix('admin')->group(function () {
    Route::get('/Liste_du_jours', [BilletController::class, 'index'])->name('admin.billet.index');
    Route::get('/Liste_du_jours/historique', [BilletController::class, 'historique'])->name('admin.billet.historique');
});

Route::middleware(['auth:staff', 'permission:Billets_impression'])->prefix('admin')->group(function () {
    // Chemin figé : public/mon_js/thermal-print.js (déjà présent, réutilisé tel quel) appelle
    // cette URL en dur.
    Route::get('/Liste_du_jours/donneesTicketThermique/{id}', [BilletController::class, 'donneesTicketThermique'])->name('admin.billet.ticket-thermique');
});

Route::middleware(['auth:staff', 'permission:Billets_annulation'])->prefix('admin')->group(function () {
    Route::post('/Liste_du_jours/annuler', [BilletController::class, 'annuler'])->name('admin.billet.annuler');
    Route::get('/Liste_du_jours/demandesAnnulation', [BilletController::class, 'demandesAnnulation'])->name('admin.billet.demandes-annulation');
    Route::post('/Liste_du_jours/confirmerAnnulation/{id}', [BilletController::class, 'confirmerAnnulation'])->name('admin.billet.confirmer-annulation');
    Route::post('/Liste_du_jours/rejeterAnnulation/{id}', [BilletController::class, 'rejeterAnnulation'])->name('admin.billet.rejeter-annulation');
});

Route::middleware(['auth:staff', 'permission:Billets_reporte'])->prefix('admin')->group(function () {
    Route::post('/Liste_du_jours/reporter', [BilletController::class, 'reporter'])->name('admin.billet.reporter');
});

Route::middleware(['auth:staff', 'permission:Billets_embarquement'])->prefix('admin')->group(function () {
    Route::get('/Liste_du_jours/embarquement', [BilletController::class, 'embarquement'])->name('admin.billet.embarquement');
    Route::post('/Liste_du_jours/decollerCar', [BilletController::class, 'decollerCar'])->name('admin.billet.decoller-car');
    Route::post('/Liste_du_jours/marquerEmbarque', [BilletController::class, 'marquerEmbarque'])->name('admin.billet.marquer-embarque');
    Route::post('/Liste_du_jours/annulerEmbarquement', [BilletController::class, 'annulerEmbarquement'])->name('admin.billet.annuler-embarquement');
    Route::post('/Liste_du_jours/marquerEmbarqueLot', [BilletController::class, 'marquerEmbarqueLot'])->name('admin.billet.marquer-embarque-lot');
    Route::post('/Liste_du_jours/demanderReport', [BilletController::class, 'demanderReport'])->name('admin.billet.demander-report');
});

Route::middleware(['auth:staff', 'permission:Billets_annulation'])->prefix('admin')->group(function () {
    Route::get('/Liste_du_jours/demandesReport', [BilletController::class, 'demandesReport'])->name('admin.billet.demandes-report');
    Route::post('/Liste_du_jours/transmettreReport/{id}', [BilletController::class, 'transmettreReport'])->name('admin.billet.transmettre-report');
    Route::post('/Liste_du_jours/confirmerReport/{id}', [BilletController::class, 'confirmerReportDemande'])->name('admin.billet.confirmer-report');
    Route::post('/Liste_du_jours/rejeterReport/{id}', [BilletController::class, 'rejeterReportDemande'])->name('admin.billet.rejeter-report');
});

Route::middleware(['auth:staff', 'permission:Billets_rapport'])->prefix('admin')->group(function () {
    Route::get('/Rapport_billets/rapport_billets', [RapportBilletController::class, 'mensuel'])->name('admin.rapport-billet.mensuel');
    Route::get('/Rapport_billets/rapport_annuel', [RapportBilletController::class, 'annuel'])->name('admin.rapport-billet.annuel');
});

Route::middleware(['auth:staff', 'permission:Billets_validation'])->prefix('admin')->group(function () {
    Route::get('/Liste_ententes', [ListeEntenteController::class, 'index'])->name('admin.entente.index');
    Route::post('/Liste_ententes/{id}/valider', [ListeEntenteController::class, 'valider'])->name('admin.entente.valider');
});
