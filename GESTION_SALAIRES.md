# Module Salaire + Bulletin de paie

## Résumé

Chaque membre du personnel salarié (tous les comptes `utilisateur` sauf `super_admin`, plus le personnel sans compte système comme un gardien ou un balayeur) a désormais une fiche employé avec un salaire de base, et peut recevoir des bulletins de paie mensuels téléchargeables en PDF. La visibilité de ces données est strictement contrôlée par une permission.

## Contexte / motivation

Avant ce module, aucune donnée salariale structurée n'existait dans l'application : "Salaire" n'était qu'une catégorie de dépense libre (texte + montant), sans lien vers une personne précise. Le personnel sans compte système (gardien, balayeur, etc.) n'avait quant à lui aucune existence dans l'application.

**Choix architectural : une table `employe` séparée**, plutôt que d'ajouter des colonnes salaire directement sur `utilisateur`/`chauffeur`. Le nom et les informations de contact restent la source de vérité sur `utilisateur`/`chauffeur` (lus par jointure, jamais dupliqués) ; `employe` ne porte que les données propres à la paie (poste, gare de rattachement, salaire, statut). Seul le personnel hors-système (sans compte ni fiche chauffeur) porte son propre nom directement sur `employe`.

## Modèle de données

Migration : voir [`ajout_salaires.sql`](ajout_salaires.sql) à la racine — à exécuter manuellement, dev puis prod (vérifier `DESCRIBE utilisateur; DESCRIBE chauffeur;` avant, comme pour les autres migrations de ce projet).

### Table `employe`

| Colonne | Description |
|---|---|
| `id_employe` | PK |
| `id_utilisateur` | Renseigné si compte système existant |
| `id_chauffeur` | Renseigné si chauffeur (car ou camion) |
| `nom_prenom` | Renseigné **uniquement** pour le personnel hors-système |
| `poste` | Libellé du poste, pré-rempli à la création, éditable |
| `id_agence` | Gare de rattachement — NULL pour Admin/PDG et le personnel compagnie-entière (scoping) |
| `id_compagnie` | |
| `salaire_base` | Montant mensuel de base (0 par défaut, à renseigner par l'Admin) |
| `statut` | `'actif'` / `'inactif'` |

### Table `bulletin_paie`

| Colonne | Description |
|---|---|
| `id_bulletin` | PK |
| `id_employe` | |
| `periode` | `'YYYY-MM'` |
| `salaire_verse` | Montant figé au moment de la génération (indépendant d'un changement ultérieur de `employe.salaire_base`) |
| `date_generation` | |
| `genere_par` | `id_utilisateur` de la personne ayant généré le bulletin |

Un seul bulletin par `(id_employe, periode)`, vérifié en code (pas de contrainte SQL — ce schéma n'utilise nulle part de `FOREIGN KEY`/`UNIQUE`, cohérence déléguée au code applicatif comme partout ailleurs dans ce projet).

### Création automatique des fiches

- **Backfill initial** (`ajout_salaires.sql`) : une fiche `employe` pour chaque compte `utilisateur` existant (hors `super_admin`) et chaque `chauffeur` existant, salaire à 0.
- **Désormais automatique** : `Configuration::saveUtilisateur()` et `Chauffeurs_car::saveChauffeur()` créent chacun une fiche `employe` (salaire à 0) juste après la création du compte/chauffeur — rien à faire manuellement pour le personnel qui a déjà un compte ou une fiche chauffeur.
- **Personnel hors-système** (gardien, balayeur...) : ajouté manuellement par l'Admin depuis l'écran Salaires.

## Permission

Nouvelle permission **`Salaire_apercu`**, accordée par défaut à `super_admin`/`Admin`/`PDG`. **Absente** des jeux par défaut de `chef_d_escale`/`secretaire`/`Utilisateur` : pour eux, l'Admin doit l'accorder manuellement via l'écran d'assignation de permissions déjà existant (`/admin/Permissions/assigner/{id}`) — aucune nouvelle interface n'a été nécessaire pour ça, la permission apparaît automatiquement dans un groupe "Salaire" (le regroupement par module y est dynamique, basé sur le préfixe du nom de la permission).

⚠️ L'attribution "par défaut" ne se déclenche qu'à la **création** d'un compte (`Permission::assignPermissionsParDefautPourRole()`) — les comptes Admin/PDG déjà existants avant ce module ne l'avaient donc pas automatiquement. `ajout_salaires.sql` fait ce backfill rétroactif pour eux ; à ne pas oublier lors du déploiement, sinon un Admin déjà créé se retrouve sans accès à "Salaires" malgré la documentation ci-dessus. De même, un compte déjà connecté au moment où une permission lui est accordée doit se reconnecter pour que ça prenne effet (les permissions sont mises en cache en session).

Cette permission gouverne **toute** la visibilité du module, y compris son propre salaire : sans elle, personne d'autre qu'Admin/PDG/super_admin ne voit rien dans "Salaires" (le lien de menu lui-même est masqué).

**Édition/création restée séparée** : avoir la permission `Salaire_apercu` donne uniquement la **lecture**. Ajouter un employé hors-système, modifier un salaire, ou générer un bulletin reste réservé à `Admin`/`PDG`/`super_admin` (contrôle de rôle, comme `Depenses.php` le fait déjà pour la validation des dépenses) — même un chef d'escale ayant reçu la permission de voir ne peut donc que consulter.

## Visibilité hiérarchique (scoping)

Calqué sur le pattern déjà utilisé par `Depense::getDepenses()` :

- **`super_admin`** : voit tout, toutes compagnies.
- **Admin / PDG** (pas de `id_agence`) : voit tout le personnel de sa compagnie, toutes gares confondues.
- **chef_d_escale / secretaire / Utilisateur avec `id_agence`**, s'ils ont reçu la permission : voient le personnel de **leur propre gare** (ce qui inclut automatiquement leur propre fiche) **+ le personnel sans gare précise** (`id_agence IS NULL` — notamment les chauffeurs, qui n'ont pas de rattachement à une gare, cf. plus bas). Sans ce `OR IS NULL`, `employe.id_agence = :id_agence` ne matche jamais NULL en SQL : un chauffeur ne serait alors visible que par Admin/PDG, jamais par un chef d'escale.

## Écrans

| Écran | Route | Description |
|---|---|---|
| Liste des salaires | `/admin/Salaires` | Personnel visible selon le scoping ci-dessus, avec salaire de base. Ajout d'un employé hors-système et modification réservés Admin/PDG/super_admin. Pour ces derniers, une case à cocher par ligne (+ "tout sélectionner") permet de choisir plusieurs employés ; pour un compte en lecture seule (permission accordée sans droit de gestion), la colonne Action affiche un tiret plutôt qu'un menu vide. |
| Génération de bulletin | (modal sur l'écran ci-dessus) | Choix d'une période (mois/année), snapshot du salaire au moment de la génération. Deux déclencheurs vers le même contrôleur (`Salaires::generer_bulletin()`) : le bouton "Générer un bulletin" d'une ligne (un seul employé, `id_employe`) ou "Générer pour la sélection" (plusieurs employés cochés, `ids_employes[]`, une seule période pour tout le lot). |
| Liste des bulletins | `/admin/Salaires/liste_bulletins` | Historique des bulletins déjà générés (même scoping), avec téléchargement. |
| Téléchargement PDF | `/admin/Salaires/telecharger_bulletin/{id}` | Génère le PDF à la volée (Dompdf, A4 portrait, même convention que les autres PDF du projet), re-vérifie l'appartenance compagnie/gare avant de servir le fichier. |

Lien de menu "Salaires" ajouté dans la sidebar sous le label "Personnel" (déjà utilisé par le lien "Employés" existant), gated par `userHasPermission('Salaire_apercu')`.

⚠️ **Piège rencontré pendant le développement, à garder en tête pour toute évolution de ce module** : les méthodes de récupération de ce framework ne renvoient pas toutes le même type — `FetchSelectWheres()`/`SelectAllData()` (base `Model`) renvoient des **objets** (`PDO::FETCH_OBJ`), `FetchSelectWhere1()` renvoie des **tableaux** (`PDO::FETCH_ASSOC`). `Employe::getEmployesVisibles()`/`BulletinPaie::getBulletinsVisibles()` (listes) renvoient donc des objets (`$employe->poste`), tandis que `Employe::getEmployeVisibleById()`/`BulletinPaie::getBulletinVisibleById()` (une seule fiche) renvoient des tableaux (`$employe['poste']`) — une confusion entre les deux a d'abord causé une page blanche à la génération d'un bulletin et un nom de fichier PDF cassé au téléchargement, avant correction.

## Limites connues / choix assumés

- Pas de gestion de primes, retenues, ou cotisations — uniquement un salaire de base fixe par bulletin (net = salaire de base). À étendre si besoin.
- Pas de contrainte `FOREIGN KEY` ni `UNIQUE` (cohérence déléguée au code, comme le reste du schéma).
- `chauffeur` n'a pas de `id_agence` propre : un chauffeur créé aujourd'hui a `employe.id_agence = NULL`, donc visible par **tout le monde** ayant la permission (traité comme "compagnie entière", cf. scoping ci-dessus) plutôt que rattaché à une gare précise. Si un jour `chauffeur` gagne une colonne `id_agence`, `Chauffeurs_car::saveChauffeur()`/`Employe::creerEmployePourChauffeur()` devront être mis à jour pour la propager.
- La page "Employés" existante (`app/controllers/admin/Employes.php`) n'a pas été modifiée : elle reste indépendante (liste RH générale, pas de salaire), même si elle partage conceptuellement les mêmes personnes.

## Fichiers créés

- `ajout_salaires.sql`
- `app/models/Employe.php`
- `app/models/BulletinPaie.php`
- `app/controllers/admin/Salaires.php`
- `app/views/admin/salaires.view.php`
- `app/views/admin/liste_bulletins.view.php`
- `app/views/admin/pdf/bulletin_paie.php`
- `GESTION_SALAIRES.md` (ce fichier)

## Fichiers modifiés

- `app/models/Permission.php` — ajout de `Salaire_apercu` à `NOMS_PERMISSIONS_PAR_DEFAUT`.
- `app/models/Configuration.php` — hook additif dans `saveUtilisateur()`.
- `app/models/Chauffeurs_car.php` — hook additif dans `saveChauffeur()` (bascule vers `insertion_update_simples_insert_id()` pour récupérer l'id du chauffeur créé).
- `app/views/admin/partials/sidebar.view.php` — nouveau lien "Salaires".

## Procédure de déploiement

1. Vérifier la structure réelle (`DESCRIBE utilisateur; DESCRIBE chauffeur;`).
2. Exécuter `ajout_salaires.sql` (dev, puis prod après validation) — vérifier le backfill (`SELECT COUNT(*) FROM employe`).
3. Déployer le code (`git pull`).
4. Tester : accès avec/sans la permission, scoping par gare, ajout d'un hors-système, génération/téléchargement d'un bulletin, création d'un nouvel utilisateur/chauffeur (vérifier la fiche `employe` auto-créée).

### Rollback

```sql
DROP TABLE IF EXISTS bulletin_paie;
DROP TABLE IF EXISTS employe;
DELETE FROM permision WHERE nom_permission = 'Salaire_apercu';
DELETE FROM user_permission WHERE permission_id IN (
    SELECT id_permision FROM permision WHERE nom_permission = 'Salaire_apercu'
);
```
Retirer aussi les fichiers créés et revenir au commit précédent pour le reste du code si un rollback complet est nécessaire.
