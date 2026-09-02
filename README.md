# TransGest Admin (transgest-laravel)

Portage Laravel de l'application legacy PHP `Projets_licence` (gestion de compagnies de
transport : billets, colis, caisse, configuration). Beaucoup de fichiers Laravel portent un
commentaire `Port de Projets_licence/...` pointant vers leur source d'origine — utile pour
retrouver la logique métier legacy en cas de doute.

## Stack

- Laravel, PHP 8.4, MySQL (base `db_annexpress_laravel` — `transgest_db` appartient à un
  autre projet, `transgest-laravel`, ne pas confondre malgré le nom proche)
- Auth admin/staff sur le guard **`auth:staff`** (pas le guard `web` par défaut), backé par
  `App\Models\Utilisateur` (table `utilisateur`, PK `idUser`)
- Bootstrap 5 + jQuery + SweetAlert2 (déjà chargés globalement, cf. `resources/views/admin/partials/foot.blade.php`)

## Template admin (2026-09-02)

L'admin (`resources/views/admin/**`, `layouts/admin.blade.php`,
`admin/partials/{header,navbar,sidebar,foot,theme}.blade.php`) a été rethémé sur le modèle
visuel de `DojoManager_laravel` (navbar/sidebar marine foncé, sélecteur de couleur de thème,
mode sombre, icônes Font Awesome à la place de Boxicons) — copié depuis le vrai rendu de ce
projet, pas reconstruit de mémoire. `admin/partials/theme.blade.php` porte toutes les
variables CSS (`--primary-color`, `--secondary-color`, etc.) et les surcharges dark-mode ;
`assets/css/transgest-theme.css` (habillage marine/orange déjà existant, antérieur à ce
chantier) reste chargé et suit désormais le thème choisi via ces mêmes variables (`--tg-navy`,
`--tg-orange` y sont pilotées par `--primary-color`/`--secondary-color`). Ossature Skodash
(`assets/css/style.css`, MetisMenu, DataTables...) conservée telle quelle pour la mécanique de
mise en page — seul l'habillage change, aucune vue de contenu n'a eu besoin d'être retouchée
pour ça. Marque : **TransGest** (pas TransHub, corrigé après coup — voir logo
`public/images/logos/transgest_icon.png`/`transgest_logo.png`, recadrés depuis l'image source
fournie par l'utilisateur).

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

### Sections "Dépenses" (menu Finances) et "Banque" — ✅ terminées (2026-08-20)

Écran build from scratch : les tables `depense`, `banques`, `depots_banque` et leurs
migrations existaient déjà (créées avant ce chantier, jamais lues/écrites) mais aucun
modèle/contrôleur/vue n'existait — les liens sidebar `Finances → Dépenses` et
`Banque → Dépôt en banque` étaient des 404.

**Écart avec le legacy, discuté et confirmé avec l'utilisateur :** le legacy calcule le
"solde disponible" d'un dépôt en banque depuis l'ancienne caisse de gare (table `caisse`,
système mort — voir section Caisse ci-dessus), ce qui aurait rendu "Faire un dépôt"
non fonctionnel dans cette app (0 ligne, aucune création possible). Remplacé par un
solde cumulatif calculé depuis `versements_caisse` (système individuel réellement
utilisé) : `SOMME(versements validés de la gare) − SOMME(dépôts déjà confirmés de la
gare)` — voir `DepotBanqueService::soldeDisponible()`. Ce solde ne se remet pas à zéro
chaque jour : un chef d'escale peut accumuler plusieurs jours de versements validés
avant de faire un seul dépôt.

Autres corrections faites en passant (pas de nouvelles décisions, juste des bugs trouvés
en lisant le modèle legacy) : migration `depense.categorie` complétée avec
`'Remboursement annulation'` (présent dans la constante PHP legacy, absent de l'enum
SQL) ; `depense.montant` passé de `integer` à `decimal(12,2)` (cohérence avec
`banques.solde`/`depots_banque.montant`) ; `depots_banque.id_caisse` rendu nullable
(toujours `NULL` désormais, conservé pour parité de schéma uniquement).

| Écran | Contrôleur | Vue | Notes |
|---|---|---|---|
| Dépenses | `Admin\DepenseController` | `admin/depense/index.blade.php` | ✅ terminé. Formulaire "Enregistrer une dépense" en **modale** (le legacy avait un formulaire toujours visible en haut de page). Portée locale (déduite de la caisse ouverte de la gare) ou globale (Admin uniquement). Valider/Rejeter (Admin) via confirmation SweetAlert2. |
| Bénéfice de la compagnie | `Admin\DepenseController@benefice` | `admin/depense/benefice.blade.php` | ✅ terminé. Filtrable jour/mois/tout, KPI cards + carte "Bénéfice net". Port quasi direct du legacy (déjà dans le style cible). |
| Comptes banque | `Admin\BanqueController` | `admin/banque/index.blade.php` | ✅ terminé. Création/modification en modales (déjà ainsi dans le legacy). **Amélioration** : "Mouvements" devient une **modale alimentée en AJAX** (`BanqueController::mouvements`, JSON) au lieu d'une page séparée — jamais de rechargement pour consulter l'historique d'un compte. |
| Dépôt en banque | `Admin\DepotBanqueController` | `admin/depot-banque/{index,en-attente,historique}.blade.php` | ✅ terminé. "Faire un dépôt" en **modale**, avec le solde disponible affiché en direct pour un chef d'escale. Confirmer (SweetAlert2) / Rejeter (modale avec motif) sur les demandes en attente. |

Modèles ajoutés : `Depense`, `Banque`, `DepotBanque`. Logique métier dans
`App\Services\{DepenseService,BanqueService,DepotBanqueService}` (même découpage
contrôleur-fin/service-gras que `CaisseUtilisateurService`). Pas de permission dédiée
en base pour Banque/Dépôt en banque (le legacy gate ces écrans par rôle uniquement,
`Admin`/`chef_d_escale`/`PDG`, jamais `super_admin` — même gating que le sidebar
existant) ; `Depenses_gestion` (déjà dans le catalogue) gate la section Dépenses.

### Section "Location des cars" (menu Finances) — ✅ terminée (2026-08-21)

| Écran | Contrôleur | Vue | Notes |
|---|---|---|---|
| Location des cars | `Admin\LocationCarController` | `admin/location-car/index.blade.php` | ✅ terminé. Réservé à Admin/chef d'escale/PDG (même gate que Dépenses). **Différence délibérée avec le legacy, demandée par l'utilisateur** : le formulaire "Nouvelle location" (toujours visible en haut de page dans le legacy) devient une **modale** avec un volet "Résumé" en direct (destination/car/période/client/frais, même traitement visuel que la page Achat de ticket) ; chaque ligne a en plus une **modale "Détails"** dédiée (le legacy n'avait aucune modale) ; Valider/Rejeter restent des confirmations SweetAlert2 mais avec boutons Bootstrap stylés (`buttonsStyling:false`, même pattern que Dépenses) plutôt que les boutons SweetAlert2 par défaut du legacy. Disponibilité des cars calculée en AJAX (`fetch()` + `<meta name="csrf-token">`, même plomberie que l'Embarquement) au fil des changements de gare/dates, avec un badge "X disponible(s)" en direct. |
| Facture location | `Admin\LocationCarController@facture` | `admin/location-car/facture.blade.php` | ✅ terminé. Le legacy générait un PDF (Dompdf) ; **dompdf n'est pas installé dans cette app** (même écart déjà assumé pour le reçu de billet thermique) — remplacé par une page HTML imprimable autonome (`window.print()`), même contenu/mise en page que le PDF legacy (mention de signature "P.O." si la location a été créée par un chef d'escale puis validée par l'Admin). |

Modèle ajouté : `LocationCar` (table `location_car`, déjà migrée, inutilisée avant ce
chantier). Logique métier dans `App\Services\LocationCarService`, calqué très
directement sur `DepenseService` (même résolution `id_caisse`/`id_caisse_user`, même
convention `['ok','type','message']`, même compare-and-swap sur le `statut` pour éviter
qu'une validation et un rejet concurrents créditent tous les deux la caisse). Différence
propre à ce module : verrouillage anti-survente sur le car choisi (`lockForUpdate()` +
re-vérification du chevauchement de dates dans la transaction), pour éviter que deux
locations soumises à quelques millisecondes d'intervalle ne réservent le même car sur la
même période.

**Bug de schéma trouvé et corrigé, pas une décision de conception :** la migration
d'origine de `location_car` ne comportait pas la colonne `id_valide_par` (ajoutée après
coup dans le legacy via un script SQL séparé, jamais reporté dans cette migration Laravel)
— nécessaire pour la mention "Signature (P.O. `<nom>`)" sur la facture et pour savoir qui a
validé une location. Migration `2026_08_21_130000_add_id_valide_par_to_location_car.php`
ajoutée (même précédent que `2026_08_20_120000_fix_depense_depots_banque_columns.php`).

Vérifié de bout en bout en HTTP réel (`php artisan serve`, login `admin.compagnie@transhub.test`)
et via `tinker` pour le chemin chef d'escale seul (pas de compte de démonstration pour ce
rôle) : création Admin → validée + caisse créditée immédiatement ; création chef d'escale
→ `en_attente`, puis validation Admin réelle en HTTP → `id_valide_par` renseigné, caisse
du chef créditée ; conflit sur le même car/période correctement rejeté ; double-validation
bloquée (compare-and-swap) ; facture HTML correcte (montant, mention de signature). Toutes
les fixtures (`location_car`, `caisse_utilisateur` de test) supprimées par ID exact après
coup.

### Section "Employés" (menu Personnel) — ✅ terminée (2026-08-21)

| Écran | Contrôleur | Vue | Notes |
|---|---|---|---|
| Liste des employés | `Admin\EmployeController` | `admin/employe/index.blade.php` | ✅ terminé. Vue unifiée qui fusionne comptes utilisateurs (avec droit/fonction) et chauffeurs de la compagnie ; chaque bloc n'apparaît que si l'utilisateur connecté a la permission correspondante (`utilisateur_apercu` / `Configuration_gestion_car/chauffeur` — page visible dès qu'AU MOINS une des deux est présente, vérifié en contrôleur comme le legacy). "Sélection multiple" (toolbar + cases à cocher) pour l'impression groupée de badges ; modale "Imprimer le badge" par ligne (déjà une modale dans le legacy, conservée telle quelle). |
| Badge employé | `Admin\EmployeController@printCard` / `@printSelection` | `admin/employe/print-card.blade.php` | ✅ terminé. Port quasi verbatim du design corporate du legacy (déjà très abouti : dégradé marine/or, photo en médaillon) — impression individuelle centrée ou planche A4 de 4 badges avec pagination auto pour la sélection groupée. **PDF généré côté client** (`html2canvas` + `jsPDF`, CDN, déjà ainsi dans le legacy) — pas de Dompdf impliqué ici, contrairement aux autres exports PDF de cette app. |
| Liste imprimable | `Admin\EmployeController@listeImprimable` | `admin/employe/liste-imprimable.blade.php` | ✅ terminé. Le legacy générait ce PDF via Dompdf (`printListPdf`) ; **dompdf n'est pas installé dans cette app** (même écart déjà assumé pour la facture de location/le reçu de billet) — remplacé par une page HTML imprimable (`window.print()`), même contenu que le PDF legacy. |

Logique métier dans `App\Services\EmployeService::{buildListe,resolveEmploye}` (pas de
nouveau modèle — recombine `Utilisateur` et `Chauffeur`, déjà existants). `resolveEmploye()`
applique le même périmètre IDOR que `buildListe()` (un compte non `super_admin` ne peut
résoudre/imprimer que les employés de sa propre compagnie) — vérifié avec une compagnie et
un utilisateur factices temporaires (aucune fuite, ni dans `buildListe()` ni dans
`resolveEmploye()`), supprimés après coup.

### Section "Billets" (menu G-réservation) — ✅ terminée (2026-08-24)

Le module legacy "Billets" est en réalité 4 contrôleurs/modèles distincts (~2 850 lignes) :
création (`Add_billets`), cycle de vie (`Liste_du_jours`, 682+1338 lignes — historique,
annulation en 2 temps, report en 2 temps, embarquement), validation "en entente"
(`Liste_ententes`, 706 lignes) et rapports (`Rapport_billets`). Comme pour G-programme,
livré en plusieurs fois plutôt qu'en un seul chantier : Achat de billet + Liste des
tickets/Historique + Annulation + Report (direct et en 2 temps) + Embarquement + Rapports
(mensuel/annuel) + **validation "en entente" (`Liste_ententes`, terminée 2026-08-24)**.

**Report : le legacy a deux mécanismes distincts, les deux sont construits :**
1. `reporter()` — report **direct**, immédiat, gate uniquement par la permission
   `Billets_reporte` (pas de validation Admin). Disponible depuis la Liste des tickets.
2. `demanderReport()`→`transmettreReport()`→`confirmerReport()` — workflow en **3 rôles**
   (agent simple → chef d'escale → Admin, avec saut de la 1ère étape si c'est déjà un chef
   d'escale/Admin/PDG qui demande), déclenché depuis l'écran **Embarquement** (client non
   présenté). Confirmé réellement atteignable dans cette app : le profil "billettière"
   (`Utilisateur` + `profile=billet`) a `Billets_embarquement` par défaut au catalogue.
   Les deux mécanismes réutilisent le même moteur de bascule des compteurs de places
   (`BilletService::appliquerReport()`, extrait de la logique déjà écrite pour le report
   direct — pas dupliqué).

**Écart avec le legacy, confirmé avec l'utilisateur :** l'impression utilise uniquement
le pont thermique existant (`public/mon_js/thermal-print.js`, déjà présent, aucune
nouvelle dépendance) — pas le PDF de secours Dompdf+QR code du legacy (`dompdf/dompdf` et
`endroid/qr-code` ne sont pas installés). Conséquence connue : si le pont d'impression
est injoignable, `thermal-print.js` tente d'ouvrir `/admin/Liste_du_jours/recu/{id}` en
repli — cette route n'existe pas encore, donc ce cas précis affichera un 404 au lieu d'un
PDF. Acceptable pour l'instant, à corriger quand le PDF sera porté.

| Écran | Contrôleur | Vue | Notes |
|---|---|---|---|
| Achat de ticket | `Admin\BilletController` | `admin/billet/create.blade.php` | ✅ terminé. Page unique (pas un assistant multi-étapes — c'est fondamentalement une seule action), mais avec un **résumé de réservation en direct** (destination/heure/escale/places/prix total, mis à jour à chaque changement) que le legacy n'avait pas. Reprend la logique JS de cascade départ→destination→heure→escale du legacy (aucun rechargement de page), juste restylée. Prix toujours recalculé côté serveur, jamais fait confiance au client. |
| Liste des tickets | `Admin\BilletController@index` | `admin/billet/index.blade.php` | ✅ terminé. **Consolide Liste_du_jours + Liste_de_demains** (2 pages legacy séparées) en une seule page à 2 onglets (Aujourd'hui / Demain), même traitement que Cars & Chauffeurs. Filtre par `jourVoyage` en SQL — le legacy filtrait "demain" côté vue (PHP `foreach`+`if`), corrigé au passage. |
| Historique des billets | `Admin\BilletController@historique` | `admin/billet/historique.blade.php` | ✅ terminé. Filtrable par date/destination/heure, badges de statut colorés. |
| Annulation | `Admin\BilletController` (annuler/demandesAnnulation/confirmerAnnulation/rejeterAnnulation) | Modales sur `admin/billet/partials/table-billets.blade.php` + `admin/billet/demandes-annulation.blade.php` | ✅ terminé. Reste en 2 temps comme le legacy : chef d'escale ne peut que **demander** (rien ne bouge), Admin **confirme** (restitue la place, rembourse) ou **rejette**. IDOR corrigé au passage : le legacy ne vérifiait pas que le chef d'escale demandeur possède bien la gare du billet visé. |
| Report (direct) | `Admin\BilletController@reporter` | Modale sur `admin/billet/partials/table-billets.blade.php` | ✅ terminé. PDG explicitement bloqué (ajout délibéré : le legacy ne gate cette action que par permission, que PDG a par défaut — seule action mutante de toute l'app qu'un rôle lecture-seule aurait pu déclencher sans ce correctif). |
| Embarquement | `Admin\BilletController` (embarquement/decollerCar/marquerEmbarque/annulerEmbarquement/marquerEmbarqueLot/demanderReport) | `admin/billet/embarquement.blade.php` | ✅ terminé. Bannière "cars complets", cartes par car du jour avec bouton "Faire décoller" (désactivé tant que des passagers restent non traités), embarquement individuel + en masse (case à cocher), "Reporter" ouvre la demande de report en 2 temps. Toutes les actions passent par `fetch()` + rechargement (pas de patch DOM manuel côté JS, pour éviter de dupliquer le rendu des lignes en JS — voir la leçon XSS/robustesse déjà tirée pour la Banque) ; nécessite le `<meta name="csrf-token">` ajouté à `admin/partials/header.blade.php` (nouveau pour cette app — jusqu'ici l'AJAX ne servait qu'à des lectures). |
| Demandes de report | `Admin\BilletController` (demandesReport/transmettreReport/confirmerReportDemande/rejeterReportDemande) | `admin/billet/demandes-report.blade.php` | ✅ terminé. Même écran pour les 2 étapes, contenu différent selon le rôle (chef d'escale : Transmettre/Rejeter sa gare ; Admin : Confirmer/Rejeter, compagnie entière) — même traitement que `demandes-annulation.blade.php`. |
| Rapport mensuel / annuel | `Admin\RapportBilletController` (mensuel/annuel) | `admin/rapport_billet/{mensuel,annuel}.blade.php` | ✅ terminé. Contrôleur/service dédiés (`RapportBilletService`), fidèle au découpage du legacy (fichiers `Rapport_billets`/`Rapport_billet` séparés de `Liste_du_jours`). Totaux par type (présentiel/en ligne/reporté) + répartition mensuelle/annuelle + ventilation par localité/gare (Admin/PDG voient tout, chef d'escale seulement sa gare). Liens sidebar déjà présents mais mal gatés (permissions placeholder `Billets_creation`/`Billets_apercue`/`Billets_validation`) — corrigés vers `Billets_rapport`. Bug d'affichage du legacy corrigé au passage (le rapport annuel étiquetait un simple décompte de billets comme un montant "FCFA"). |
| Ticket en entente (validation réservations en ligne) | `Admin\ListeEntenteController` | `admin/liste-entente/index.blade.php` | ✅ terminé (2026-08-24). Port de `Liste_ententes`/`Liste_entente` (contrôleur/modèle legacy séparés, comme Rapports — nouveau service dédié `ListeEntenteService`, pas une extension de `BilletService`). Liste les billets `status_reservation='en_ligne'`/`validation_billets='en_attente'` de la gare de départ de l'agent (Admin/PDG voient toute la compagnie). "Valider" ouvre une **modale** (le legacy avait une page dédiée) avec récap en lecture seule + confirmation du n° de paiement (comparaison insensible aux espaces — amélioration délibérée, le legacy comparait la chaîne brute). Transaction : bascule `validation_billets='valider'` (garde-fou anti double-validation) puis crédite la **caisse individuelle ouverte de l'agent qui valide** (`CaisseUtilisateurService::crediterBillet()`, même mécanisme qu'une vente au guichet) — pas la caisse de gare morte du legacy. Envoie un email "✅ Billet validé" (`App\Mail\BilletValide`) si le client a fourni une adresse ; **pas de PDF+QR joint** (Dompdf non installé, même écart que `ReservationConfirmee`) — le mail renvoie vers `site.billet` (fiche imprimable) à la place. Vérifié de bout en bout via HTTP réel (login `chef.bamako@transhub.test`) + appel direct du service : rejet correct sans caisse ouverte (transaction entièrement annulée, billet resté `en_attente`), succès complet une fois une caisse ouverte (billet validé, caisse créditée, journal créé, email loggé avec le bon lien), email absent proprement géré quand aucune adresse n'est fournie. Tous les billets/clients/caisse de test supprimés après coup. |

Modèles ajoutés : `Billet` (table `billets`), `Client` (table `client`, un client recréé
à chaque réservation — pas de recherche/réutilisation, fidèle au legacy), `Suivis` (table
`suivis`, quota de places pour "demain"). Logique métier dans `App\Services\BilletService`
(port de `Add_billet.php` + les méthodes liste/historique/annulation/report de
`Liste_du_jour.php`). `Programme::resolveDestinationPrincipale()` ajouté (retrouve la
destination finale d'un trajet à partir du nom stocké sur un billet, qui peut être une
escale) — utilisé par l'annulation et le report pour retrouver la bonne ligne
`programmation_voyage`/`suivis`. `CaisseUtilisateurService::crediterBillet()` ajouté
(miroir exact de `crediterColis()`, comblait un manque déjà identifié dans la mémoire du
chantier Caisse). Verrouillage anti-survente : `car` (aujourd'hui, `lockForUpdate()`) ou
`suivis` (demain, quota + `lockForUpdate()`) — la réservation échoue proprement si aucune
caisse individuelle n'est ouverte pour l'utilisateur (transaction annulée en entier,
aucune ligne `client` orpheline).

**Écart avec le legacy (même classe déjà corrigée pour Dépôt en banque/Dépense) :** le
remboursement d'une annulation confirmée vise la caisse individuelle actuellement ouverte
de la gare (`caisse_utilisateur`), pas l'ancienne caisse de gare (table `caisse`, morte
dans cette app).

`decolle_le`/`decolle_par` sur `programmation_voyage` (colonnes déjà présentes, jamais
renseignées avant ce chantier — voir mémoire G-programme) sont désormais réellement écrites
par `BilletService::decollerCar()`. "Cars en approche"/flotte restent hors-scope (toujours
non alimentés par un écran existant, comme documenté précédemment).

**Bug trouvé et corrigé pendant la vérification, pas une décision de conception :** le
`$fillable` du modèle `Billet` (posé lors du chantier création) ne listait que les
colonnes utiles à la réservation — les colonnes d'annulation/report
(`motif_annulation`, `demande_annulation_par`, `date_repporte`, etc.) étaient donc
silencieusement ignorées par les `update()` Eloquent (protection mass-assignment).
Complété. Une deuxième régression similaire (montant de remboursement toujours à 0 car
`montant_payer` vit sur `client`, jamais chargé par la requête `Billet` seule utilisée
par l'annulation) a aussi été trouvée et corrigée — capturée uniquement parce que la
vérification allait jusqu'à inspecter la table `depense` et pas seulement le message flash
(les deux branches success/warning partagent la sous-chaîne "Billet annulé avec succès").

### Fenêtre de réservation élargie à J+6, et rôle "Secrétaire Général", ajoutés 2026-09-02

**Réservation à l'avance.** Le port Laravel avait repris la version **legacy la plus
ancienne** de la règle de date (`Add_billet.php`, bloc aujourd'hui commenté/mort côté
legacy) : `[$aujourdhui, $demain]` uniquement. La version **actuellement live** côté
legacy (`Add_billet::saveBillets()`, `Reservations_ligne::saveBilletsEnligne()`,
`Reservation_formulaire.php`) autorise en réalité **aujourd'hui → J+6** (une semaine),
dupliquée en dur à 4 endroits faute de constante commune. Corrigé côté Laravel avec une
vraie constante centrale, `config('billets.jours_reservation_avance')` (défaut `6`, env
`BILLETS_JOURS_RESERVATION_AVANCE`), appliquée dans `BilletService::creerReservation()`
(vente guichet), `ReservationEnLigneService::creerReservation()` (site public) et les deux
sélecteurs de date (`admin/billet/create.blade.php`,
`site/partials/reservation-modal.blade.php`). Le branchement `jourVoyage === $aujourdhui`
(nécessite un `programmation_voyage` déjà affecté) vs. tout autre jour de la fenêtre
(quota `place_minumale` + suivi `suivis`, aucune affectation de car requise à l'avance)
existait déjà côté Laravel et est resté inchangé — il était déjà écrit génériquement
(`else`), pas limité à "demain" en dur, donc valide tel quel pour J+1..J+6.

L'écran `admin/billet/index.blade.php` (onglets Aujourd'hui/Demain) ne permettait de
consulter que 2 des 7 jours désormais réservables. L'onglet "Demain" est devenu **"Autre
jour"** : un sélecteur de date (`min`=demain, `max`=J+`jours_reservation_avance`) recharge
la liste via `?date=YYYY-MM-DD` (`BilletController::index()`), toujours sur
`BilletService::getListeParDate()` (déjà générique, aucun changement de service).

**Volontairement laissé de côté** : le **report** d'un billet déjà créé
(`BilletService::reporter()`/`appliquerReport()`) a sa propre règle, différente et non
touchée — limité à `[aujourd'hui, demain]` par rapport à *la date actuelle*, alors que le
legacy borne le report à `date_expiration` du billet (`jourVoyage + 1 semaine`), une
notion distincte de la fenêtre d'achat. Les branches de libération/réservation de place
(`appliquerReport()`) sont câblées en dur sur `=== $aujourdhui`/`=== $demain` (pas un
`else` générique comme pour l'achat) — les élargir sans adapter ces branches créerait un
bug silencieux de comptage de places pour un report vers J+2 et au-delà. À reprendre
séparément si le report doit lui aussi couvrir toute la semaine.

**Rôle "Secrétaire Général"** (`droit = 'secretaire'`, existe côté legacy — table
`utilisateur.droit`, simple `varchar`, pas d'enum SQL). Périmètre identique au legacy :
même portée **compagnie entière** que Admin/PDG (pas restreint à une gare) sur ~20
fichiers (`in_array($droit, ['Admin', 'PDG', ...])` → `'secretaire'` ajouté partout où
Admin+PDG apparaissaient ensemble — contrôleurs, services, sidebar, dashboard), mais
**zéro permission par défaut à la création** (`Permission::assignPermissionsParDefautPourRole()`
n'avait déjà aucun cas pour `'secretaire'` → reste un no-op, comme le legacy : c'est
volontairement l'Admin qui attribue chaque permission à la main via l'écran
"Assigner les permissions"). Ajouté à `ConfigurationController::droitsAutorisesPour()`
(assignable par super_admin et par Admin, comme Utilisateur/chef d'escale — jamais par
lui-même), au formulaire (`admin/configuration/index.blade.php`, affiche le champ
Compagnie plutôt que Gare, comme Admin/PDG) et aux libellés (navbar, dropdown de rôle) —
"Secrétaire Général" à l'affichage, `secretaire` en base. `estLectureSeule()` (PDG
uniquement) ne le concerne pas, comme le legacy. Vérifié en HTTP réel : le rôle apparaît
dans le sélecteur, la modale bascule bien vers le champ Compagnie une fois sélectionné.

### État de la flotte, ajouté 2026-08-22

Port de `Projets_licence/app/controllers/admin/Flotte.php` +
`Programmation_voyage::getEtatFlotte()`. Écran de supervision globale (Admin/super_admin/
PDG) listant **tous** les cars de la compagnie — disponibles ou non — contrairement au
dashboard "Trajets programmés" qui ne montre qu'un sous-ensemble filtré (cars en transit
décollés, cars bloqués) pour le suivi opérationnel du jour. Ce lien sidebar existait dans le
legacy mais n'avait jamais été porté ni relié dans cette app.

`Admin\FlotteController` (index, lecture seule) + `ProgrammationVoyage::etatFlotte()`
(nouvelle méthode statique sur le modèle, LEFT JOIN `car`/`programmation_voyage`/`agence`
pour renvoyer une ligne par car même sans transit en cours) — `admin/flotte/index.blade.php`.
Route `/admin/Flotte`, gardée par un contrôle de rôle explicite dans le contrôleur (pas un
`permission:` de catalogue) car ce garde-fou n'a jamais eu de permission dédiée côté legacy
non plus. Badges d'état : Disponible / Position inconnue / Embarquement en cours / En route
(avec durée écoulée depuis le décollage) / Anomalie (transit sans programmation active
correspondante, renvoie vers "Cars bloqués").

**Widget "Cars vers votre gare" ajouté sur la page d'accueil du chef d'escale**, même
session — port du bloc `Homes::home()`/`home.view.php` équivalent (données
`getCarsInTransit()`+`getCarsProgrammesVersMaGare()` du legacy, jamais portées avant). Deux
nouvelles méthodes `HomeStatsService::getCarsEnTransitVersGare()`/`getCarsProgrammesVersGare()`
(prennent `$ville` en paramètre plutôt que de lire la session, cohérent avec le reste du
service) appelées depuis `HomeController::index()` seulement pour `chef_d_escale`, affichées
dans `admin/home.blade.php` juste avant la section Finances (même emplacement que le legacy).
Distingue "En transit" (badge vert, décollage réel enregistré) de "Programmé" (badge orange,
pas encore décollé) — même logique de `decolle_le` que le dashboard Trajets programmés/l'écran
Flotte. **Non vérifié en HTTP réel** (la vérification a été interrompue côté utilisateur) —
seulement relu statiquement (`php -l`, revue du code) ; la requête `decolle_le IS NOT NULL`
réutilise exactement le même pattern déjà testé bout en bout pour l'écran Flotte ci-dessus. À
vérifier en conditions réelles avant de considérer ce widget définitivement acquis.

Vérifié en HTTP réel (`php artisan serve` + `curl`) : les 5 états de badge s'affichent
correctement (Position inconnue par défaut, En route avec durée/gare, Embarquement en cours,
Anomalie), et l'accès est bien refusé à un chef_d_escale (redirigé vers l'accueil). Fixtures
temporaires (`programmation_voyage`, `car.status_car`, utilisateur chef jetable) nettoyées/
réinitialisées à l'identique après vérification.

### Réclamation + Historique des colis, ajoutés 2026-08-22

Port de `Projets_licence/app/controllers/admin/{Reclamations,Historiques}.php`. Écran
Réclamation entièrement repensé en modales (recherche d'un colis par code + soumission,
gestion du statut/remboursement) au lieu du formulaire deux colonnes + rechargement de page
du legacy — même direction UI que Location des cars/Embarquement de billets. Historique
consolide les deux pages legacy (`historique_colis_enregistrer`/`historique_colis_livre`) en
une seule page à deux onglets DataTables (même traitement que Cars & Chauffeurs et Billets
Liste/Historique), filtrage par simple rechargement GET plutôt que le va-et-vient jQuery AJAX
manuel du legacy.

`Admin\ReclamationColisController` (index/rechercher/store/updateStatus) —
`admin/colis/reclamation/index.blade.php`. `Admin\HistoriqueColisController` (index) —
`admin/colis/historique/index.blade.php`.

**Écart avec le legacy (même classe déjà appliquée à Billets/Dépôt en banque/Dépense) :** le
remboursement d'une réclamation visait l'ancienne table `caisse` (morte dans cette app,
colonne `montant_rembourse`). Substitué par une `Depense` (nouvelle catégorie "Remboursement
colis", ENUM `depense.categorie` étendu par une migration dédiée) contre la caisse
individuelle actuellement ouverte (`caisse_utilisateur`) de la gare concernée — Admin choisit
la caisse (départ ou destination du colis, même filtre que legacy), chef_d_escale utilise
automatiquement sa propre caisse ouverte du jour.

**IDOR corrigé, pas une décision de conception (même classe de bug déjà trouvée sur
Billets) :** le legacy ne vérifiait aucune portée sur `update_status` — n'importe quel
chef_d_escale pouvait gérer/rembourser une réclamation concernant n'importe quelle gare de la
compagnie, débitant sa propre caisse pour un colis qui ne la concernait pas. Corrigé : un
chef_d_escale ne peut gérer que les réclamations dont son agence est le départ ou la
destination du colis ; l'Admin garde une portée compagnie entière.

Vérifié de bout en bout en HTTP réel (login + requêtes `curl` avec jeton CSRF, `php artisan
serve`) avec des fixtures temporaires (colis, expéditeur/destinataire, deux
`caisse_utilisateur` ouvertes départ+destination, un utilisateur chef_d_escale jetable, une
agence jetable pour tester le cas hors-gare) : recherche par code, soumission de réclamation,
double-soumission bloquée, remboursement Admin avec sélection de caisse (Depense créée,
`montant_depense` incrémenté), remboursement chef_d_escale auto-caisse, blocage IDOR pour un
chef d'une gare tierce. Tout nettoyé par ID exact après vérification. Un bug a été trouvé et
corrigé pendant cette vérification (pas une décision) : `Depense::CATEGORIES` (liste PHP)
avait été mise à jour mais pas l'ENUM MySQL réel de `depense.categorie`, qui rejetait
silencieusement l'insertion — migration `2026_08_22_120000_add_remboursement_colis_to_depense_categorie`
ajoutée.

### Autres sections déjà présentes avant ce chantier

- **Colis** : `Admin\ColisPriseEnChargeController`, `EnvoiColisController`,
  `MouvementColisController`, `LivraisonColisController` — `resources/views/admin/colis/**`.
  Réclamation + Historique : voir ci-dessus.
- **Accueil / dashboard** : `Admin\HomeController` — `admin/home.blade.php`.
- **Auth** : `Auth\LoginController`.

### Section "Site public" (vitrine, hors `/admin`) — 🚧 en cours (commencée 2026-08-22)

Nouveau chantier séparé de l'admin : le site public destiné aux visiteurs/clients
(`Projets_licence/app/controllers/site/`), qui n'avait aucun équivalent Laravel avant
cette session (ni contrôleur, ni vue, ni route hors `/admin`). Pas de guard d'auth dédié
pour le moment (les 3 pages ci-dessous sont en lecture seule, sans espace client/partenaire).

**Site dédié à une seule compagnie (décision du 2026-08-24, `SITE_COMPAGNIE_ID` dans `.env`,
`ANN EXPRESS` par défaut) :** le site public a été construit initialement comme une vitrine
multi-compagnies (catalogue, recherche/suivi de colis tous transporteurs confondus), pendant
que l'admin reste volontairement multi-tenant. L'utilisateur gère en réalité une seule
compagnie et a demandé que le site public donne l'impression d'être **son** site propre, pas
un catalogue — "pour l'instant" (le réactiver reste un simple changement de config, pas une
réécriture). Point de bascule unique : `App\Models\Compagnie::site()` (memoïsé, lit
`config('site.compagnie_id')`), utilisé par tous les contrôleurs et par
`site/partials/nav.blade.php` (branding — logo/nom de cette compagnie sur **toutes** les
pages, plus de logo TransGest générique en usage normal ; lien "Nos trajets" au lieu de
l'ancien lien mal libellé qui menait au catalogue). `Site\CompagnieController::index()`
(`/compagnies`) redirige désormais vers la page de cette compagnie plutôt que de lister un
catalogue ; `show()` renvoie 404 pour toute autre compagnie (`site/compagnies.blade.php`
supprimée, devenue inatteignable). Les sélecteurs de compagnie ont disparu des formulaires de
Recherche/Suivi de colis (Accueil et pages dédiées) — la compagnie est forcée côté serveur, la
liste des villes/résultats est scopée à elle (`Programme::villesDisponibles($idCompagnie)`
prend désormais un paramètre optionnel). Défense en profondeur côté réservation en ligne :
`Site\ReservationController::donnees()` et `ReservationEnLigneService::creerReservation()`
rejettent tout trajet n'appartenant pas à la compagnie du site, même via une requête forgée à
la main. Autres compagnies (ex. `Air-niono`, `id_compagnie = 2`) toujours en base pour l'admin,
juste ignorées par le site public. Vérifié via `php artisan serve` + `curl` : toutes les pages
publiques rendent le bon branding, `/compagnies` redirige (302), `/compagnies/2` 404,
formulaires sans sélecteur de compagnie, flux de réservation en ligne toujours fonctionnel de
bout en bout.

| Écran | Contrôleur(s) | Vue | Notes |
|---|---|---|---|
| Accueil | `Site\HomeController` | `site/home.blade.php` | ✅ terminé. Hero (slider `public/assets_site/img/hero-slides/*`, titre/slogan de la compagnie du site), recherche (branchée sur `site.recherche`), suivi colis (branché sur `site.suivi-colis`), "Destinations populaires" de cette compagnie (plus d'onglets par compagnie ni de grille "compagnies partenaires" depuis le 2026-08-24), stats réelles (destinations/trajets/clients de cette compagnie). |
| Compagnies | `Site\CompagnieController@index` | — | ✅ terminé, **redirige** depuis le 2026-08-24 vers la page de la compagnie du site (voir note ci-dessus) — n'affiche plus de catalogue. |
| Détails trajets d'une compagnie | `Site\CompagnieController@show` | `site/compagnie-trajets.blade.php` | ✅ terminé (port de l'écran legacy "Programmer"), restreint à la compagnie du site depuis le 2026-08-24 (404 sinon). Trajets groupés par gare de départ (groupes repliables `<details>`), triables (prix/heure), vue grille/liste, escales/tarifs affichés par trajet. Route `/compagnies/{compagnie}` (sans suffixe `/trajets` depuis le 2026-08-24, pour ressembler à la page propre de la compagnie) avec binding Eloquent standard. |
| Recherche | `Site\RechercheController` | `site/recherche.blade.php` | ✅ terminé. Filtre départ/destination (optionnels), trié par prix, scopé à la compagnie du site depuis le 2026-08-24 (plus de sélecteur de compagnie). Le champ "date" est affiché/conservé dans l'URL mais ne filtre rien, fidèle au legacy : `programmer` décrit des trajets récurrents quotidiens, pas des instances datées (celles-ci vivent côté admin dans `programmation_voyage`, jamais exposées ici). |
| Suivi de colis public | `Site\SuiviColisController` | `site/suivi-colis.blade.php` | ✅ terminé. Code de suivi uniquement depuis le 2026-08-24 (l'étape de sélection de compagnie a disparu, la compagnie du site est forcée côté serveur — le lookup vérifie toujours code + compagnie, jamais le code seul). PRG (redirection vers `?show_code=...` après une recherche réussie) pour avoir une URL de résultat propre/partageable. Réutilise le scope `Colis::avecDetails()` déjà existant côté admin. Timeline de statut animée (enregistré → en_cours → reçu → livré). |
| Contact | `Site\ContactController` | `site/contact.blade.php` | ✅ terminé. Stats réelles scopées à la compagnie du site depuis le 2026-08-24 (destinations/trajets/clients) + formulaire décoratif (le legacy ne le traitait pas non plus côté serveur). |
| Espace partenaire | `Site\PartenaireController` | `site/partenaire/{login,discussion}.blade.php` | ✅ terminé. Connexion/inscription (guard Laravel `partenaire`, voir stratégie d'auth ci-dessous) + messagerie avec l'admin. **Contrepartie admin construite dans la foulée** : `Admin\PartenariatController` (`/admin/Partenariats`, super_admin) — sans elle, un partenaire pourrait écrire mais personne ne pourrait lui répondre depuis l'interface (le lien sidebar existait déjà avant ce chantier, jamais implémenté, menait à un 404). |
| Réservation en ligne | `Site\ReservationController` | `site/partials/reservation-modal.blade.php` (+ `site/billet.blade.php`) | ✅ terminé (2026-08-24). Port de `Reservation_formulaire` — **formulaire en modal** (pas une page dédiée, à la demande explicite de l'utilisateur "pour que ce soit rapide"), ouvert depuis les boutons "Réserver" de Recherche et Détails trajets, alimenté en AJAX (`GET /reservation/{id}/donnees`) et soumis en AJAX (`POST /reservation`, réponse JSON, pas de redirection PRG). Transaction + verrou anti-surbooking (`App\Services\ReservationEnLigneService`, même structure que `BilletService` côté admin), email de confirmation (`App\Mail\ReservationConfirmee`), redirection vers `site/billet.blade.php` (fiche imprimable) en cas de succès. **Pas de PDF/QR** (legacy générait un PDF Dompdf+QR — même écart assumé qu'ailleurs dans l'app, non installé ici) : `window.print()` sur une page HTML à la place. Le billet créé reste `validation_billets = 'en_attente'` jusqu'à validation manuelle par un agent de la gare de départ (`Admin\ListeEntenteController`, voir section Billets ci-dessus — terminé le même jour). |

#### Stratégie d'authentification client/partenaire

Décidée pendant ce chantier (les deux écrans restants — Espace client, Espace partenaire —
en avaient besoin) :

- **Partenaire → vrai guard Laravel `partenaire`** (`config/auth.php`, provider Eloquent sur
  `App\Models\PartenaireCompte`), session, mêmes conventions que le guard `staff` existant
  (`Auth::guard('partenaire')->attempt()/login()/logout()`, middleware
  `guest:partenaire`/`auth:partenaire`). Justifié : un compte partenaire a un vrai mot de
  passe et une identité durable, exactement le cas d'usage pour lequel Laravel Auth existe.
- **Client → PAS de guard Laravel.** Le legacy authentifie par numéro de billet + téléphone,
  sans mot de passe (`EspaceClient::login()` : `JOIN billets ON id_client... WHERE
  numeroBillets = ? AND numeroClient = ?`) — et une ligne `client` est créée à chaque
  réservation (pas de compte réutilisé d'une réservation à l'autre, voir `App\Models\Client`).
  Ce n'est pas un compte au sens propre, donc forcer ça dans le contrat Authenticatable/Guard
  serait artificiel. À la place (pour l'écran Espace client, pas encore construit) : simples
  valeurs de session (`session(['client_id' => ...])`) posées après vérification manuelle du
  couple billet+téléphone, protégées par un middleware dédié léger — reproduit fidèlement
  `requireClientLogin()` du legacy sans le forcer dans le système de Guard.

Route `/` (page d'accueil du site, plus de redirection auto vers `/admin` ou `/login` selon
l'auth staff). Le menu public n'a pas de lien "Espace pro" (connexion staff) — retiré à la
demande de l'utilisateur 2026-08-24, `/login` reste accessible directement par son URL mais
n'est plus mis en avant aux visiteurs. Nouvelles routes `site.home`/`site.compagnies`/`site.compagnie.trajets`/
`site.recherche`/`site.suivi-colis`/`site.contact` dans `routes/web.php`, sans middleware
(public). Nouveau `resources/views/site/partials/nav.blade.php` (en-tête + menu mobile
partagé par toutes les pages) et `public/assets_site/css/site-common.css` (styles communs —
reset, variables, boutons, footer, bandeau de page — extraits des templates legacy
quasi-identiques pour éviter de les dupliquer). Nouvelles méthodes statiques sur
`App\Models\Programme` : `pourVitrine($idCompagnie)`, `pourCompagnieAvecEscales($idCompagnie)`,
`villesDisponibles()`, `rechercher($depart, $destination, $idCompagnie)`.

**Piège MariaDB rencontré sur `pourCompagnieAvecEscales()`** : le `GROUP_CONCAT` des
escales (port du `GROUP BY p.idProgrammer` du legacy) fait planter cette instance MariaDB
(`ONLY_FULL_GROUP_BY`, erreur 1055) même en groupant uniquement par la clé primaire
`programmer.idProgrammer` — l'exception de dépendance fonctionnelle de MySQL pour "GROUP BY
sur la PK" n'est pas fiable sur cette version de MariaDB (10.4.32), contrairement à ce que la
doc MySQL laisse penser. Contourné avec une sous-requête corrélée (`(SELECT GROUP_CONCAT(...)
... WHERE lt.id_trajets = programmer.idProgrammer)` dans le `SELECT`) plutôt qu'un `GROUP BY`
sur la requête principale — à réutiliser si un futur écran a besoin d'un `GROUP_CONCAT`
similaire.

**Liens/formulaires volontairement inertes pour l'instant** (les boutons "Réserver" sont
maintenant réels — voir ligne "Réservation en ligne" ci-dessus) : seuls les liens de pied de
page FAQ/CGU/Politique de confidentialité (sur toutes les pages du site) et le formulaire de
contact restent décoratifs — ils affichent "Cette fonctionnalité arrive bientôt !" au
clic/submit (fonction JS `tgBientot()` dans le nav partial) plutôt que de mener à un 404 —
même logique que les liens `href="#"` déjà utilisés côté admin pour les écrans pas encore
construits (ex. "Hors programme"). Un bug legacy a été corrigé au passage : `nav.view.php` et
chaque page avaient chacun leur propre `<div id="mobileNav">` (id dupliqué), rendant le lien
"Espace pro" du panneau mobile de page totalement inatteignable au clic à l'époque — il n'y a
maintenant qu'un seul panneau mobile (dans le nav partagé) ; le lien "Espace pro" lui-même a
depuis été retiré (voir plus haut).

**Pas encore portée** (prochaine étape de ce chantier, voir
`Projets_licence/app/controllers/site/`) : EspaceClient (login par n° billet+tél, dashboard,
épargne, paiements — nécessite le middleware de session léger décrit ci-dessus, pas encore
créé). Modèle Eloquent manquant : `Epargne`. La table `demande_partenariat` (migration déjà
là) n'est en fait référencée nulle part dans le legacy — dead table, aucun modèle à créer. La
table `reservation` (migration déjà là) a aussi un rôle encore à clarifier avec l'utilisateur
(pré-réservation vs `billets`).

### Pas encore portées depuis `Projets_licence`

Programmation des voyages : voir "G-programme" ci-dessus (tout fait sauf Hors programme).
Caisse : voir ci-dessus (système individuel fait, caisse de gare hors scope).
Dépenses/Banque : voir ci-dessus (terminées).
Billets : voir ci-dessus — **terminé** (Achat + Liste/Historique + Annulation + Report +
Embarquement + Rapports + validation "en entente").
Site public : voir "Section Site public" ci-dessus (vitrine + Recherche + détails trajets +
Suivi colis public + Espace partenaire + Réservation en ligne faits ; seul l'Espace client
reste à porter).
Vérifier `Projets_licence/app/controllers/admin/` et `Projets_licence/app/controllers/site/`
pour la liste complète avant de commencer.
