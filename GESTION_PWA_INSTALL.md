# Modal d'installation de l'application (PWA) après connexion

## Résumé

L'espace admin dispose d'un manifest, d'un service worker et d'un modal d'installation PWA (Progressive Web App) complet et fonctionnel. Ce qui a changé ici : **le moment où le modal apparaît**. Le script `public/mon_js/pwa-install.js` existait déjà dans le dépôt (porté depuis `Projets_licence`) mais n'était **chargé sur aucune page** — mort depuis le portage. Il est désormais chargé sur toutes les pages admin, ne peut apparaître **qu'après une connexion réussie**, et fonctionne aussi bien sur **mobile que sur ordinateur (desktop)**.

## Contexte / motivation

Rien n'a été reconstruit : `public/mon_js/pwa-install.js` gère déjà tout (icône, titre, bouton "Installer" ou instructions manuelles iOS, bouton "Plus tard", mémorisation du choix dans `localStorage` sous les clés `sirali_pwa_prompt_dismissed`/`sirali_pwa_installed`). Le manifest (`public/pwa/admin-manifest.json`) et le service worker (`public/sw-admin.js`, scope `/admin/`) sont déjà enregistrés indépendamment dans `resources/views/admin/partials/header.blade.php` — `pwa-install.js` détecte leur présence (`document.querySelector('link[rel="manifest"]')`) et ne les re-injecte pas en double.

Deux problèmes empêchaient ce script d'avoir l'effet voulu :

1. **Aucune inclusion `<script>`** : le fichier était présent dans `public/mon_js/` mais n'apparaissait dans aucune vue Blade — recherche confirmée sur tout `resources/views`. Le modal ne pouvait donc jamais apparaître, sur aucune page.
2. **Pas de garde de connexion, et restriction `isMobile()` sur la branche Chrome/Edge** : même une fois câblé, le script aurait pu se déclencher sur une page admin accessible sans session (aucune actuellement, mais rien ne le garantissait structurellement), et `deferredPrompt.prompt()` (l'API native d'installation) est bridée aux seuls écrans mobiles alors qu'elle fonctionne très bien sur Chrome/Edge **desktop** aussi.

Remarque : la page de connexion (`resources/views/auth/login.blade.php`, route `admin` → `LoginController::create`) est un document HTML autonome qui n'étend pas `layouts.admin` — elle n'inclut donc jamais `foot.blade.php` ni ce script. Le risque que le legacy corrigeait (modal visible sur l'écran de connexion lui-même) ne peut donc pas se produire ici de toute façon ; la garde `PWA_USER_LOGGED_IN` est ajoutée pour rester fidèle au comportement voulu et pour ne pas dépendre de cette seule particularité de routage si elle changeait un jour.

## Ce qui a changé

### `resources/views/admin/partials/foot.blade.php`

Juste avant `</body>` (donc sur toutes les pages qui étendent `layouts.admin`, soit tout l'espace admin) :

```blade
<script>
  window.PWA_USER_LOGGED_IN = @json(auth('staff')->check());
</script>
<script src="{{ asset('mon_js/pwa-install.js') }}?v={{ @filemtime(public_path('mon_js/pwa-install.js')) }}"></script>
```

`auth('staff')->check()` vaut `true` uniquement lorsqu'un membre du staff est authentifié — même garde que `$_SESSION['id_utilisateur']` côté legacy, adaptée au guard Laravel utilisé partout ailleurs dans les vues admin (`navbar.blade.php`, `sidebar.blade.php`, `config-nav.blade.php`).

Placé dans `foot.blade.php` (pas `header.blade.php`) car ce script agit sur `<body>` (overlay de la modale), et parce que c'est l'équivalent Laravel du point d'injection legacy ("juste avant `</body>`" dans `index.php`).

Le moteur de navigation sans rechargement complet (`mon_js/admin-transitions.js`) déduplique les `<script src>` déjà présents lors d'un swap de `<body>` : ce script ne s'exécute donc qu'une seule fois par vrai chargement de page (jamais ré-exécuté à chaque navigation douce), ce qui évite d'accumuler plusieurs écouteurs `beforeinstallprompt`. La ligne `window.PWA_USER_LOGGED_IN = ...` (script inline) est en revanche ré-évaluée à chaque navigation douce comme tout le reste de `foot.blade.php` — sans effet de bord puisque sa valeur ne change pas en cours de session.

### `public/mon_js/pwa-install.js`

Nouvelle fonction `estConnecte()` qui lit `window.PWA_USER_LOGGED_IN`, ajoutée en garde sur les deux points de déclenchement automatique du modal :

- **Branche Android/Chrome/desktop** (`beforeinstallprompt`) : la condition `isMobile()` a été **retirée** — c'était le seul frein empêchant le modal d'apparaître sur ordinateur. `isAdminSection()` et `estConnecte()` restent en garde.
- **Branche iOS** (Safari ne déclenche jamais `beforeinstallprompt`, instructions manuelles) : `isMobile()`/`isIos()` sont **conservés** — il n'existe pas d'équivalent "iOS desktop" à gérer.

Résultat concret : le modal apparaît environ 1,2 seconde après l'arrivée sur une page admin authentifiée, aussi bien sur mobile que sur une fenêtre desktop de taille normale (pas seulement en réduisant la fenêtre pour simuler du mobile).

## Comment tester

1. Vider dans les outils développeur du navigateur (onglet Application/Storage) les clés `localStorage` suivantes, propres à ce domaine :
   - `sirali_pwa_prompt_dismissed`
   - `sirali_pwa_installed`
2. Se connecter à l'espace admin : le modal doit apparaître environ 1,2 seconde après l'arrivée sur `admin/Homes/home` — y compris dans une fenêtre de navigateur desktop de taille normale (Chrome/Edge).
3. Cliquer "Plus tard" : le modal ne doit plus réapparaître tant que `sirali_pwa_prompt_dismissed` n'est pas effacé.
4. Cliquer "Installer" (Chrome/Edge desktop ou Android) : un raccourci/une icône d'application doit être proposé(e) par le navigateur.
5. Se déconnecter et recharger une page admin (ou aller sur l'écran de connexion) : le modal ne doit jamais apparaître hors session.

## Limites connues / choix assumés

- Le modal ne se déclenche que si le navigateur émet l'évènement `beforeinstallprompt` (Chrome, Edge, la plupart des navigateurs Chromium) ou s'il s'agit de Safari iOS (instructions manuelles). Firefox desktop, par exemple, ne propose pas d'installation PWA native — ce n'est pas un bug de ce code.
- `beforeinstallprompt` est soumis aux heuristiques d'engagement propres à Chrome/Edge (pas systématiquement dès la première visite/connexion) : le modal ne peut pas apparaître avant que le navigateur ait lui-même décidé que le site est "installable".
- Aucun changement sur le contenu du modal lui-même (icône, textes, boutons) : uniquement sa condition de déclenchement et son branchement effectif sur les pages admin.
- Le choix (installé / "plus tard") reste mémorisé par navigateur via `localStorage` — pas de mémorisation côté serveur par utilisateur.
- Pas de bouton flottant persistant d'installation (fonctionnalité ajoutée ultérieurement côté `Projets_licence`, hors du périmètre de ce chantier) : le seul point d'entrée est le modal automatique.

## Fichiers modifiés

- `resources/views/admin/partials/foot.blade.php`
- `public/mon_js/pwa-install.js`
- `GESTION_PWA_INSTALL.md` (ce fichier, nouveau)

## Rollback

Retirer les deux balises `<script>` ajoutées dans `resources/views/admin/partials/foot.blade.php`, et dans `pwa-install.js` : remettre `isMobile()` sur la branche `beforeinstallprompt`, retirer `estConnecte()` des deux conditions de déclenchement — ou revenir simplement au commit précédent pour ces deux fichiers. Le fichier `pwa-install.js` redeviendrait alors mort (non chargé), comme avant ce chantier.
