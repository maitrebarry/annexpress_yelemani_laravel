# TransHub Admin (transgest-laravel)

Portage Laravel de l'application legacy PHP `Projets_licence` (gestion de compagnies de
transport : billets, colis, caisse, configuration). Beaucoup de fichiers Laravel portent un
commentaire `Port de Projets_licence/...` pointant vers leur source d'origine — utile pour
retrouver la logique métier legacy en cas de doute.

## Stack

- Laravel, PHP 8.4, MySQL (base `transgest_db`)
- Auth admin/staff sur le guard **`auth:staff`** (pas le guard `web` par défaut), backé par
  `App\Models\Utilisateur` (table `utilisateur`, PK `idUser`)
- Bootstrap 5 + jQuery + SweetAlert2 (déjà chargés globalement, cf. `resources/views/admin/partials/foot.blade.php`)

## Démarrer en local

```bash
composer install
php artisan migrate --seed   # crée notamment les comptes de démo ci-dessous
php artisan storage:link     # nécessaire pour les photos (utilisateurs, chauffeurs)
php artisan serve
```

Comptes de démo (`database/seeders/StaffDemoSeeder.php`), mot de passe `password` :
- `superadmin@transhub.test` — `super_admin`, toutes compagnies
- `admin.compagnie@transhub.test` — `Admin`, scopé à la compagnie "ANN EXPRESS"

## Conventions à suivre pour tout nouvel écran admin

- Vue Blade dans `resources/views/admin/<module>/index.blade.php`, `@extends('layouts.admin')`.
- **Modales, pas de pages séparées** pour créer/modifier/supprimer (`@section('modals')` +
  `@section('scripts')`) — choix explicite du produit pour que l'admin reste rapide.
- Notifications via `App\Support\Flash::set($message, $type)`, rendues par
  `@include('admin.partials.set_flash')` sous forme de toast SweetAlert2 animé (pas de bandeau statique).
- Permissions : middleware de route `permission:<nom_permission>` (voir
  `App\Http\Middleware\EnsureHasPermission`) + `App\Models\Permission` pour le catalogue par défaut.
- Route naming : `admin.<module>.<action>`.
- **Piège récurrent** : `super_admin` a `id_compagnie = NULL` (pas rattaché à une compagnie).
  Toute création qui écrit `id_compagnie` doit lui proposer un `<select>` compagnie dans la
  modale ; sinon l'INSERT échoue (colonnes `id_compagnie` NOT NULL sur `agence`, `horaire`,
  `escale`, `car`...).

## Modules — état d'avancement

Cette section doit être mise à jour à chaque module terminé, pour que n'importe quel
développeur (ou agent) puisse reprendre le travail sans redécouvrir le contexte.

### Section "Configuration" (menu Paramètres) — ✅ terminée (2026-08-20)

| Module | Contrôleur(s) | Vue | Notes |
|---|---|---|---|
| Utilisateur | `Admin\ConfigurationController` | `admin/configuration/index.blade.php` | Liste, ajout/modif/activation/suppression en modales. L'ancienne page séparée "add_utilisateurs" a été fusionnée en modale. Lien vers l'assignation de permissions par ligne. |
| Compagnie | `Admin\CompagnieController` | `admin/compagnie/index.blade.php` | super_admin uniquement. Upload logo. |
| Gares | `Admin\GaresController` | `admin/gares/index.blade.php` | Préexistant, a servi de patron pour les autres modules. Ajout multi-lignes. |
| Escale | `Admin\EscaleController` | `admin/escale/index.blade.php` | Ajout multi-lignes. |
| Horaire | `Admin\HoraireController` | `admin/horaire/index.blade.php` | Ajout multi-lignes, champ `time`. |
| Cars & Chauffeurs | `Admin\CarController`, `Admin\ChauffeurController` | `admin/car/index.blade.php` | Une seule page à deux onglets (Cars / Chauffeurs) sans rechargement, alors que le legacy utilisait deux pages séparées. Photo du chauffeur modifiable. |
| Permissions | `Admin\PermissionController` | `admin/permission/catalogue.blade.php`, `admin/permission/assigner.blade.php` | Catalogue (super_admin) + écran d'assignation par utilisateur (Admin/super_admin, scopé à la compagnie). |
| Place limite | `Admin\PlaceLimiteController` | `admin/place-limite/index.blade.php` | Limite de places "demain" par compagnie. |

Modèles ajoutés : `Escale`, `Horaire`, `Car`, `Chauffeur`, `Permission`, `PlaceMinimale`
(les modèles `Utilisateur`, `Agence`, `Compagnie` existaient déjà).

### Section "G-programme" (menu Programmation) — 🚧 en cours (commencée 2026-08-20)

| Module | Contrôleur(s) | Vue | Notes |
|---|---|---|---|
| Voyages | `Admin\ProgrammeController` | `admin/programme/index.blade.php`, `admin/programme/create.blade.php` | ✅ terminé. Liste + assistant 3 étapes (itinéraire/horaire/tarification, style `bs-stepper` comme `admin/colis/create.blade.php`) pour créer un ou plusieurs voyages (une heure cochée = un voyage), avec création automatique du trajet retour et tarifs par escale. Modale de modification (prix/heure/tarifs escale) sur la page liste. super_admin doit choisir une compagnie via `?id_compagnie=` avant de voir le formulaire (pas géré côté legacy). |
| Cars (affectation) | `Admin\ProgrammationCarController` | `admin/programmation-car/index.blade.php` | ✅ terminé. Liste des cars programmés (`car.programmer_car = 'on'`) ; modale "Programmer un car" (cars + trajets multi-select `select2`, aller-retour affecté automatiquement) ; modale "Ajouter un trajet" à un car déjà programmé (options déjà affectées grisées) ; modale "Détails" par car (remplace la page séparée du legacy). `reference_car` tenu à jour en écriture (parité DB) mais non lu — la liste se base sur `car.programmer_car`. |
| Trajets programmés | `Admin\ProgrammationVoyageController` | `admin/programmation-voyage/{dashboard,liste-journaliere,edit}.blade.php` | ✅ terminé. `liste-journaliere` est l'écran lié au sidebar (programmations `statut='active'` du jour). `dashboard` (non lié au sidebar, atteint via son bouton "Ajouter") génère les affectations car→créneau du jour (verrou `lockForUpdate()` + anti-doublon), affiche "Véhicules en approche" et "Cars bloqués" (Admin/super_admin). `edit` gère le choix "suivre les billets / nouveau car" quand le créneau visé a déjà des réservations. Un car n'apparaît disponible dans `dashboard` que s'il a déjà des trajets affectés via l'écran Cars (`liaison_car_trajet`). Pas de modèle `Billet` : requêtes `DB::table('billets')` directes (table déjà migrée, 0 lignes tant qu'aucun écran Billets n'existe). "Transférer les passagers" a été ajouté à `liste-journaliere` par le module Transferts (ci-dessous), une fois ce dernier construit. Hors scope restant (voir mémoire projet) : "Désactiver" (mort côté legacy aussi), `decollerCar()`/`getEtatFlotte()` (jamais appelés depuis cet écran). |
| Transferts | `Admin\TransfertGareController` | `admin/transfert-gare/historique.blade.php` (+ modale sur `programmation-voyage/liste-journaliere.blade.php`) | ✅ terminé. Consolide les passagers d'une gare vers une gare voisine (même localité/destination/heure/jour) quand le car cible peut les accueillir en totalité — tout ou rien, transactionnel (`lockForUpdate()` sur les deux `programmation_voyage`/`car` et les `billets` concernés), mouvement de caisse source→destination. `candidats()` (AJAX, sans permission dédiée — `auth:staff` seul, comme le legacy) alimente la modale ; `executer()` fait le transfert ; `historique` (sous `permission:Programme_programmation_voyage`, même gate que le sidebar) est l'écran lié. Pas de modèle `Client`/`Caisse` : `DB::table()` direct (même pattern que Trajets programmés). Vérifié avec 2 gares/cars/programmations de test (même créneau, localités identiques) : la liste des gares candidates et l'aperçu (0 passager ⇒ bouton "Confirmer" désactivé) fonctionnent ; le garde-fou serveur "Aucun passager à transférer" bloque proprement une tentative directe. Le déplacement réel de billets n'est pas testable tant qu'aucun écran Billets n'existe (0 lignes en base). |
| Hors programme | — | — | Pas commencé (lien `href="#"` dans le sidebar). |

Modèles ajoutés : `Programme` (table `programmer`), `LigneTrajet` (table `ligneTrajet`,
partagée avec le futur module site public — `type_trajet = 'programmer'` uniquement ici),
`ProgrammationVoyage` (table `programmation_voyage`), `TransfertGare` (table `transferts_gare`).
`ProgrammationVoyage::destinationsPourCreneau()` (résolution destination finale + escales d'un
créneau, pour matcher les `billets`) est partagée entre `ProgrammationVoyageController` et
`TransfertGareController` — ne pas la dupliquer si un futur écran en a aussi besoin.

Libellés du sous-menu sidebar renommés pour coller au style court de `config-nav.blade.php`
(`Programme du voyage`→`Voyages`, `Affectation des cars`→`Cars`, `Programmation du
voyage`→`Trajets programmés`, `Transferts entre gares`→`Transferts`, `Hors programmer`→`Hors
programme`) — voir `resources/views/admin/partials/sidebar.blade.php` ~ligne 244.

### Section "Caisse" (menu Gestion de caisse) — 🚧 en cours (commencée 2026-08-20)

Deux systèmes de caisse coexistent dans le legacy : (1) **caisse individuelle par
opérateur** (table `caisse_utilisateur`, système actuel) et (2) **caisse de gare**
(table `caisse`, plus ancienne — le menu legacy dit lui-même qu'elle est "remplacée"
par (1)). La *création/gestion* d'une caisse de gare (écrans legacy `caisse`/
`add_caisse`, non liés au menu) reste hors scope — mais "Bilan de caisse" (lien sidebar
réel, pointait vers un 404 avant d'être corrigé le 2026-08-20) a quand même été
construit en lecture seule : il fonctionne, juste vide tant qu'aucune caisse de gare
n'existe (même traitement que les écrans dépendant de `billets` ailleurs dans l'app).

| Écran | Contrôleur | Vue | Notes |
|---|---|---|---|
| Ma Caisse | `Admin\CaisseController` | `admin/caisse/ma-caisse.blade.php` | ✅ terminé. Dashboard personnel (KPI billets/colis/solde, journal du jour, historique). Ouvrir/Fermer/Verser sont des **modales sur cette même page** (le legacy en faisait 3 pages séparées) — direction UI demandée explicitement par l'utilisateur pour ce module ("plus interactif... beaucoup de modale"). La modale de fermeture reproduit l'aperçu d'écart en temps réel du legacy (JS vanilla). |
| Supervision Escale | `Admin\CaisseController` | `admin/caisse/caisses-escale.blade.php` | ✅ terminé. Caisses des opérateurs + versements en attente (Valider/Rejeter, confirmation SweetAlert2 pour Rejeter) + historique. "Clôturer la journée" est une **modale** (remplace la page séparée du legacy) avec aperçu + garde-fou serveur si des caisses sont encore ouvertes (le legacy ne bloquait ça que côté JS, contournable). |
| Rapport Compagnie | `Admin\CaisseController` | `admin/caisse/rapport-proprietaire.blade.php` | ✅ terminé. Rapport consolidé toutes gares, filtrable par date, bouton imprimer. |
| Bilan de caisse | `Admin\CaisseController` | `admin/caisse/bilant.blade.php` | ✅ terminé (ajouté après coup — le lien sidebar 404ait). Rendu volontairement différent du legacy (table plate + bouton "Voir") : grille de cartes cliquables (filtrables en direct par un champ de recherche), modale de mouvements repensée (sélecteur de période en pastilles, colonnes Entrées/Sorties colorées). Une seule vue `bilant.blade.php` paramétrée par `$type` sert les deux onglets Billets/Colis (le legacy avait deux fichiers quasi identiques). `mouvements()` (AJAX JSON) lit `billets`/`colis`/`versements_caisse`/`depense` — vide tant qu'aucune caisse de gare n'existe, comme le reste de l'écran. |

Toute la logique métier vit dans `App\Services\CaisseUtilisateurService` (déjà existant
avant ce chantier pour `crediterColis()`, utilisé par `ColisPriseEnChargeController`) —
étendu avec `ouvrirCaisse`, `fermerCaisse`, `creerVersement`, `validerVersement`,
`cloturerEscale`, `getCaissesEscale`, `getRapportProprietaire`, etc. Verrous
anti-race-condition conservés (`lockForUpdate()` + compare-and-swap sur les updates
conditionnels `WHERE statut = ...`). Modèles ajoutés : `VersementCaisse`,
`ClotureEscale`, `JournalCaisse` (+ relations sur `CaisseUtilisateur` existant).

**Écart avec le legacy, volontaire :** `cloturerEscale()` utilise `updateOrCreate()`
sur `(id_agence, date_cloture)` pour être réellement idempotent — le legacy tentait un
`ON DUPLICATE KEY UPDATE` mais `clotures_escale` n'a aucune contrainte unique
correspondante, donc chaque relance créait une clôture en double au lieu de mettre à
jour l'existante (bug legacy, pas une divergence de comportement voulue).

### Autres sections déjà présentes avant ce chantier

- **Colis** : `Admin\ColisPriseEnChargeController`, `EnvoiColisController`,
  `MouvementColisController`, `LivraisonColisController` — `resources/views/admin/colis/**`.
- **Accueil / dashboard** : `Admin\HomeController` — `admin/home.blade.php`.
- **Auth** : `Auth\LoginController`.

### Pas encore portées depuis `Projets_licence`

Billets/réservations, dépenses, banque, rapports — non explorées lors de ce chantier.
Programmation des voyages : voir "G-programme" ci-dessus (tout fait sauf Hors programme).
Caisse : voir ci-dessus (système individuel fait, caisse de gare hors scope).
Vérifier `Projets_licence/app/controllers/admin/` pour la liste complète avant de commencer.
