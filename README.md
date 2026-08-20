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

### Autres sections déjà présentes avant ce chantier

- **Colis** : `Admin\ColisPriseEnChargeController`, `EnvoiColisController`,
  `MouvementColisController`, `LivraisonColisController` — `resources/views/admin/colis/**`.
- **Accueil / dashboard** : `Admin\HomeController` — `admin/home.blade.php`.
- **Auth** : `Auth\LoginController`.

### Pas encore portées depuis `Projets_licence`

Billets/réservations, caisse, dépenses, programmation des voyages, banque, rapports —
non explorées lors de ce chantier Configuration. Vérifier `Projets_licence/app/controllers/admin/`
pour la liste complète avant de commencer.
