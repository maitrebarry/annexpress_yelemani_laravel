@extends('layouts.admin')

@section('title', 'Documentation · Sirali Admin')

@section('breadcrumb-title')
    <span class="text-primary"><i class="fas fa-book me-1"></i> Documentation</span>
@endsection
@section('breadcrumb-active', "Manuel d'utilisation")

@section('breadcrumb-actions')
    {{-- data-no-transition : vrai téléchargement de fichier (Content-Disposition: attachment),
         à ne jamais intercepter/fetch par le moteur de navigation (voir mon_js/admin-transitions.js). --}}
    <a href="{{ route('admin.documentation.pdf') }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill shadow-sm" data-no-transition>
        <i class="fas fa-file-arrow-down me-1"></i> Télécharger en PDF
    </a>
@endsection

@section('content')

    <div class="doc-intro">
        <h4 class="fw-bold mb-2">Manuel d'utilisation — Sirali</h4>
        <p class="mb-2 text-muted">Ce guide couvre l'ensemble du système, de la connexion au dernier module, dans l'ordre où vous en avez besoin&nbsp;: on configure d'abord ce qui ne change pas souvent (gares, horaires, cars), puis on programme les voyages, et enfin on utilise les écrans du quotidien (billets, colis, caisse, personnel).</p>
        <p class="mb-0 text-muted"><span class="badge bg-secondary">Accessible à tous les comptes</span> Cette page ne dépend d'aucune permission — chaque utilisateur ne verra bien sûr, dans le reste de l'application, que les écrans autorisés par son rôle.</p>
    </div>

    <div class="doc-toc-card">
        <div class="doc-toc-label">Sommaire</div>
        <div class="doc-toc">
            <a href="#doc-connexion"><span>00</span>Connexion & rôles</a>
            <a href="#doc-configuration"><span>01</span>Configuration</a>
            <a href="#doc-programmation"><span>02</span>Programmation</a>
            <a href="#doc-caisse"><span>03</span>Caisse</a>
            <a href="#doc-billets"><span>04</span>Billets</a>
            <a href="#doc-colis"><span>05</span>Colis</a>
            <a href="#doc-finances"><span>06</span>Finances</a>
            <a href="#doc-banque"><span>07</span>Banque</a>
            <a href="#doc-personnel"><span>08</span>Personnel</a>
            <a href="#doc-impression"><span>09</span>Impression</a>
        </div>
    </div>

    {{-- ================= 00. CONNEXION & RÔLES ================= --}}
    <section id="doc-connexion" class="doc-section">
        <h2><span class="doc-num">00</span>Connexion & rôles</h2>
        <p>Tout commence sur l'écran de connexion (<code>/admin</code>), commun à tous les rôles.</p>
        <ol class="doc-steps">
            <li>Saisir l'<strong>adresse e-mail</strong> et le <strong>mot de passe</strong> du compte.</li>
            <li>Valider — en cas d'erreur (compte inconnu, mot de passe incorrect, compte désactivé), un message explicite s'affiche.</li>
            <li>Une fois connecté, vous arrivez sur <strong>Accueil</strong>, le tableau de bord. Le menu de gauche s'adapte automatiquement à votre rôle et à vos permissions&nbsp;: vous ne voyez que ce que vous êtes autorisé à utiliser.</li>
        </ol>
        <x-doc-shot file="connexion.jpg" alt="Écran de connexion Sirali" />
        <x-doc-shot file="accueil.jpg" alt="Tableau de bord Accueil, une fois connecté" />

        <div class="doc-callout">
            <div class="doc-callout-label">Les rôles du système</div>
            <ul class="doc-plain">
                <li><strong>Admin</strong> — administrateur d'une compagnie&nbsp;: accès complet à tous les modules de sa compagnie.</li>
                <li><strong>PDG</strong> — dirigeant d'une compagnie, en <strong>lecture seule</strong>&nbsp;: peut consulter les mêmes écrans qu'un Admin mais ne peut ni créer, ni modifier, ni supprimer.</li>
                <li><strong>Secrétaire Général</strong> — rôle modulable&nbsp;: ne reçoit <strong>aucune permission par défaut</strong> à la création du compte. C'est l'Admin qui doit lui accorder, au cas par cas, les permissions nécessaires (écran Assignation de permissions, section Configuration ci-dessous) — y compris, si besoin, la gestion complète d'un module comme les Salaires.</li>
                <li><strong>Chef d'escale</strong> — responsable d'une gare précise&nbsp;: accès aux opérations de sa gare (billets, colis, caisse, dépenses, location de cars), limité à son propre personnel côté Salaires, certaines actions restant soumises à validation de l'Admin.</li>
                <li><strong>Utilisateur</strong> — compte opérationnel simple (ex&nbsp;: agent billetterie), accès restreint aux écrans nécessaires à sa tâche.</li>
                <li><strong>super_admin</strong> — rôle réservé à l'équipe éditrice de la plateforme (gestion des compagnies clientes, partenariats). Non couvert par ce manuel, orienté vers l'utilisation quotidienne d'<em>une</em> compagnie.</li>
            </ul>
        </div>
    </section>

    {{-- ================= 01. CONFIGURATION ================= --}}
    <section id="doc-configuration" class="doc-section">
        <h2><span class="doc-num">01</span>Configuration</h2>
        <p class="text-muted">Réservée à l'<strong>Admin</strong> (et au Secrétaire Général si la permission lui a été accordée). C'est la première chose à mettre en place&nbsp;: rien d'autre ne fonctionne tant que gares, escales, horaires et cars ne sont pas configurés.</p>
        <div class="route-path">Menu → Paramètres → Configuration</div>

        <h3>Utilisateurs & permissions</h3>
        <p>Création des comptes de la compagnie (nom, e-mail, mot de passe, rôle, gare d'affectation pour un chef d'escale/Utilisateur). Chaque compte reçoit automatiquement les permissions par défaut de son rôle&nbsp;— à l'exception du <strong>Secrétaire Général</strong>, qui n'en reçoit aucune à la création. Les permissions restent ensuite ajustables individuellement, compte par compte, sur l'écran d'assignation.</p>
        <x-doc-shot file="config-utilisateurs.jpg" alt="Liste des utilisateurs de la compagnie" />
        <x-doc-shot file="permission-assigner.jpg" alt="Écran d'assignation des permissions à un compte" />
        <div class="doc-callout">
            <div class="doc-callout-label">Nouveau module ajouté au système</div>
            <p class="mb-0">Une nouvelle permission n'est automatiquement donnée qu'aux comptes créés <em>après</em> son ajout (et jamais au Secrétaire Général, qui n'a pas de permissions par défaut). Pour un compte existant, il faut la cocher manuellement sur l'écran d'assignation.</p>
        </div>

        <h3>Gares</h3>
        <p>Les points de départ/arrivée de la compagnie (localité + numéro de gare). Saisie possible en plusieurs lignes à la fois, en cliquant sur « Ajouter une ligne »&nbsp;: tout ou rien&nbsp;— si une ligne est invalide (doublon, champ manquant), aucune n'est enregistrée et les erreurs sont détaillées.</p>
        <x-doc-shot file="config-gares.jpg" alt="Liste des gares de la compagnie" />

        <h3>Escale</h3>
        <p>Les arrêts intermédiaires possibles entre deux gares, utilisés ensuite lors de la création d'un trajet (section Programmation).</p>
        <x-doc-shot file="config-escale.jpg" alt="Liste des escales" />

        <h3>Horaire</h3>
        <p>Les créneaux horaires de départ proposés lors de la programmation d'un voyage (ex&nbsp;: 06h00, 14h00, 20h00).</p>
        <x-doc-shot file="config-horaire.jpg" alt="Liste des horaires" />

        <h3>Cars & Camions & Chauffeurs</h3>
        <p>Le parc de véhicules de la compagnie, en 3 onglets. <strong>Cars</strong> et <strong>Camions</strong> se saisissent en plusieurs lignes à la fois comme les gares (« Ajouter une ligne »). Les <strong>chauffeurs</strong>, eux, s'ajoutent un par un (nom, contact, numéro de permis).</p>
        <x-doc-shot file="config-cars-chauffeurs.jpg" alt="Onglets Cars / Camions / Chauffeurs" />

        <h3>Place limite</h3>
        <p>Le nombre de places par défaut appliqué lors de la création d'un trajet/voyage.</p>
        <x-doc-shot file="config-place-limite.jpg" alt="Place limite par défaut" />

        <h3>Actualités & Messages reçus</h3>
        <p>Gestion des actualités publiées sur le site public de la compagnie, et consultation des messages envoyés depuis son formulaire de contact.</p>
    </section>

    {{-- ================= 02. PROGRAMMATION ================= --}}
    <section id="doc-programmation" class="doc-section">
        <h2><span class="doc-num">02</span>Programmation</h2>
        <p class="text-muted">Une fois gares, escales, horaires et cars configurés (section précédente), on peut créer les trajets et les programmer réellement. Réservée à l'<strong>Admin</strong> et, pour certains écrans, au <strong>chef d'escale</strong>.</p>
        <div class="route-path">Menu → Programmation</div>

        <h3>Voyages</h3>
        <p>Création d'un <strong>trajet</strong> en 3 étapes&nbsp;: <em>Itinéraire</em> (gare de départ, une ou plusieurs escales à cocher, destination — les gares de la même localité que le départ sont masquées pour éviter un voyage interne), <em>Horaire</em> (heure de départ, point de rendez-vous), puis <em>Tarification</em> (prix du transport, et un tarif optionnel par escale cochée). Le trajet retour (destination → départ) est créé automatiquement. Le système détecte et signale les doublons (même départ/destination/horaire déjà existant) — un raccourci permet de voir directement tous les trajets déjà programmés depuis le formulaire.</p>
        <x-doc-shot file="programme-voyages.jpg" alt="Programmer un voyage, étape Itinéraire" />

        <h3>Cars <span class="doc-role role-chef">Admin · chef d'escale</span></h3>
        <p>Affectation d'un ou plusieurs cars, pour une date donnée, aux trajets déjà créés.</p>
        <x-doc-shot file="programme-cars.jpg" alt="Programmation des cars sur les trajets" />

        <h3>Trajets programmés</h3>
        <p>Vue de la programmation journalière effective&nbsp;: quel car part de quelle gare, à quelle heure, ce jour précis. C'est cette liste qui alimente ensuite l'écran <strong>Liste des tickets</strong> (billets) pour la vente.</p>
        <x-doc-shot file="programme-trajets.jpg" alt="Liste des trajets programmés" />

        <h3>Transferts</h3>
        <p>En cas de voyage annulé ou perturbé, permet de transférer les passagers concernés vers un autre voyage/gare, avec le mouvement de caisse correspondant (montant déplacé de la caisse source vers la caisse destination).</p>
        <x-doc-shot file="programme-transferts.jpg" alt="Historique des transferts entre gares" />

        <h3>État de la flotte</h3>
        <p>Vue d'ensemble de tous les cars/camions de la compagnie et de leur situation du jour&nbsp;: en trajet, disponible, ou non programmé.</p>
        <x-doc-shot file="programme-flotte.jpg" alt="État de la flotte" />
    </section>

    {{-- ================= 03. CAISSE ================= --}}
    <section id="doc-caisse" class="doc-section">
        <h2><span class="doc-num">03</span>Caisse</h2>
        <p class="text-muted">La caisse doit être <strong>ouverte</strong> avant de pouvoir vendre un billet, enregistrer un colis, une dépense ou une location de car — ces montants viennent s'y créditer/débiter automatiquement.</p>
        <div class="route-path">Menu → Caisse</div>

        <h3>Ma caisse</h3>
        <p>Chaque utilisateur (Utilisateur, chef d'escale, Admin) ouvre sa <strong>propre caisse individuelle</strong> en début de service (montant initial en espèces), et la ferme en fin de journée (comptage réel, écart éventuel avec le montant attendu). Tant qu'elle n'est pas ouverte, aucune vente ni aucune dépense ne peut lui être imputée.</p>
        <x-doc-shot file="caisse-ma-caisse.jpg" alt="Ma caisse" />

        <h3>Supervision escale <span class="doc-role role-chef">chef d'escale · Admin</span></h3>
        <p>Vue d'ensemble de toutes les caisses individuelles ouvertes à une gare&nbsp;: qui a ouvert, quel montant, permet aussi de clôturer une escale (ensemble des caisses de la gare) en fin de journée.</p>
        <x-doc-shot file="caisse-supervision-escale.jpg" alt="Supervision escale" />

        <h3>Rapport compagnie <span class="doc-role role-admin">Admin</span></h3>
        <p>Vue consolidée de toutes les caisses, toutes gares confondues, pour l'Admin.</p>
        <x-doc-shot file="caisse-rapport-compagnie.jpg" alt="Rapport compagnie" />

        <h3>Bilan de caisse</h3>
        <p>Bilans détaillés des recettes billets et colis par caisse.</p>
        <x-doc-shot file="caisse-bilan.jpg" alt="Bilan de caisse" />
    </section>

    {{-- ================= 04. BILLETS ================= --}}
    <section id="doc-billets" class="doc-section">
        <h2><span class="doc-num">04</span>Billets</h2>
        <div class="route-path">Menu → Billetterie</div>

        <h3>Achat de ticket</h3>
        <p>Vente d'un billet&nbsp;: gare de départ, destination, horaire, informations du client, numéro de place. Les champs se mettent à jour dynamiquement (sans recharger la page) au fur et à mesure des choix.</p>
        <x-doc-shot file="billets-achat.jpg" alt="Formulaire d'achat de ticket" />

        <h3>Liste des tickets</h3>
        <p>Liste d'embarquement du jour (et de demain, onglet séparé)&nbsp;: qui doit embarquer, sur quel car, à quelle heure. Depuis cette liste&nbsp;: impression du reçu (câble/USB ou imprimante WiFi), report du voyage, demande/validation d'annulation.</p>
        <x-doc-shot file="billets-liste.jpg" alt="Liste des tickets du jour" />

        <h3>Historique des billets</h3>
        <p>Consulter et réimprimer (câble/USB ou WiFi) un billet à <strong>n'importe quelle date passée</strong>&nbsp;: filtre par date, destination et heure.</p>
        <x-doc-shot file="billets-historique.jpg" alt="Historique des billets" />

        <h3>Embarquement</h3>
        <p>Remplace la case à cocher papier de la liste d'embarquement&nbsp;: pour un trajet/date donné, marquer chaque client comme <strong>embarqué</strong> (horodaté, avec l'agent qui l'a fait), avec un compteur en direct (X / Y embarqués). Un embarquement fait par erreur peut être annulé.</p>
        <x-doc-shot file="billets-embarquement.jpg" alt="Écran Embarquement" />

        <h3>Ticket en entente</h3>
        <p>Validation des réservations faites en ligne (site public de la compagnie) ou en attente de confirmation de paiement, avant qu'elles n'apparaissent comme billets définitifs.</p>
        <x-doc-shot file="billets-entente.jpg" alt="Liste des tickets en entente" />

        <h3>Demandes d'annulation <span class="doc-role role-admin">Admin</span></h3>
        <p>Un chef d'escale ne peut que <em>demander</em> l'annulation d'un billet&nbsp;; c'est l'Admin qui valide (ou refuse) définitivement ici. L'Admin, lui, peut annuler directement sans passer par cette étape.</p>
        <x-doc-shot file="billets-demandes-annulation.jpg" alt="Demandes d'annulation en attente" />

        <h3>Demandes de report <span class="doc-role role-chef">chef d'escale · Admin</span></h3>
        <p>Pour un client <strong>non embarqué</strong> (depuis l'écran Embarquement), une demande de report vers une nouvelle date/heure peut être envoyée. Le circuit s'adapte à qui la fait&nbsp;: un agent simple envoie d'abord au <strong>chef d'escale de sa gare</strong>, qui transmet ensuite à l'Admin&nbsp;; un chef d'escale (ou l'Admin) qui fait la demande lui-même l'envoie <strong>directement</strong> à l'Admin. Seul l'Admin valide définitivement (le billet est réellement reprogrammé) ou rejette, à n'importe quelle étape.</p>
        <x-doc-shot file="billets-demandes-report.jpg" alt="Demandes de report" />

        <h3>Rapport billets</h3>
        <p>Rapport mensuel et rapport annuel des ventes de billets.</p>
        <x-doc-shot file="billets-rapport-mensuel.jpg" alt="Rapport mensuel des billets" />
    </section>

    {{-- ================= 05. COLIS ================= --}}
    <section id="doc-colis" class="doc-section">
        <h2><span class="doc-num">05</span>Colis</h2>
        <div class="route-path">Menu → Colis</div>

        <h3>Liste des colis</h3>
        <p>Prise en charge d'un nouveau colis (expéditeur, destinataire, nature, valeur, frais de transport, gare de destination) — génère un code unique.</p>
        <x-doc-shot file="colis-liste.jpg" alt="Liste des colis" />

        <h3>Envoi des colis</h3>
        <p>Affectation d'un colis pris en charge à un car/trajet précis pour l'expédier réellement.</p>
        <x-doc-shot file="colis-envoi.jpg" alt="Envoi des colis" />

        <h3>Mouvement des colis</h3>
        <p>Suivi des colis actuellement en transit entre deux gares.</p>
        <x-doc-shot file="colis-mouvement.jpg" alt="Mouvement des colis" />

        <h3>Livraison des colis</h3>
        <p>Remise du colis au destinataire à l'arrivée&nbsp;: recherche par code, marquage comme livré.</p>
        <x-doc-shot file="colis-livraison.jpg" alt="Livraison des colis" />

        <h3>Réclamation</h3>
        <p>Suivi des réclamations liées à un colis (perte, retard, dommage).</p>
        <x-doc-shot file="colis-reclamations.jpg" alt="Réclamations colis" />

        <h3>Historique</h3>
        <p>Historique complet des colis enregistrés et des colis livrés.</p>
        <x-doc-shot file="colis-historique.jpg" alt="Historique des colis" />
    </section>

    {{-- ================= 06. FINANCES ================= --}}
    <section id="doc-finances" class="doc-section">
        <h2><span class="doc-num">06</span>Finances<span class="doc-role role-chef">Admin · chef d'escale</span></h2>
        <div class="route-path">Menu → Finances</div>

        <h3>Dépenses</h3>
        <p>Enregistrement d'une dépense, <strong>locale</strong> (rattachée à une gare et déduite de sa caisse) ou <strong>globale</strong> (à l'échelle de la compagnie, Admin uniquement). Une dépense créée par un chef d'escale reste <strong>en attente</strong> jusqu'à validation par l'Admin&nbsp;; créée par l'Admin, elle est déduite immédiatement.</p>
        <x-doc-shot file="finances-depenses.jpg" alt="Gestion des dépenses" />

        <h3>Bénéfice de la compagnie <span class="doc-role role-admin">Admin</span></h3>
        <p>Vue consolidée&nbsp;: revenus billets + colis + location de cars, moins les remboursements et les dépenses, sur une période (jour / mois / depuis le début).</p>
        <x-doc-shot file="finances-benefice.jpg" alt="Bénéfice de la compagnie" />

        <h3>Location des cars</h3>
        <p>Location ponctuelle d'un car à un client, en dehors des trajets programmés (destination libre, dates, coordonnées client, frais). Un chef d'escale crée une <strong>demande</strong> (en attente de validation)&nbsp;; l'Admin crée directement une location effective ou valide celles en attente.</p>
        <x-doc-shot file="finances-locations-cars.jpg" alt="Location des cars" />
    </section>

    {{-- ================= 07. BANQUE ================= --}}
    <section id="doc-banque" class="doc-section">
        <h2><span class="doc-num">07</span>Banque<span class="doc-role role-chef">Admin · chef d'escale</span></h2>
        <div class="route-path">Menu → Banque</div>

        <h3>Comptes banque <span class="doc-role role-admin">Admin</span></h3>
        <p>Gestion des comptes bancaires de la compagnie, vers lesquels les recettes en espèces sont déposées.</p>
        <x-doc-shot file="banque-comptes.jpg" alt="Comptes banque" />

        <h3>Faire un dépôt</h3>
        <p>Enregistrement d'un dépôt en banque (montant retiré de la caisse physique, versé sur un compte).</p>
        <x-doc-shot file="banque-depot.jpg" alt="Faire un dépôt en banque" />

        <h3>Demandes en attente <span class="doc-role role-admin">Admin</span></h3>
        <p>Confirmation ou rejet des dépôts déclarés par les gares, avant qu'ils ne soient définitivement comptabilisés.</p>
        <x-doc-shot file="banque-demandes-attente.jpg" alt="Demandes de dépôt en attente" />

        <h3>Historique des dépôts</h3>
        <p>Historique de tous les dépôts effectués.</p>
        <x-doc-shot file="banque-historique-depots.jpg" alt="Historique des dépôts en banque" />
    </section>

    {{-- ================= 08. PERSONNEL ================= --}}
    <section id="doc-personnel" class="doc-section">
        <h2><span class="doc-num">08</span>Personnel</h2>
        <p class="text-muted">Réservée à l'<strong>Admin</strong> (le <strong>chef d'escale</strong> ne voit, côté Salaires, que le personnel de sa propre gare) et au <strong>Secrétaire Général</strong> si la gestion des salaires lui a été accordée.</p>
        <div class="route-path">Menu → Personnel</div>

        <h3>Employés</h3>
        <p>Liste du personnel de la compagnie&nbsp;: comptes créés dans Configuration (chauffeurs compris) apparaissent automatiquement ici. Un employé <strong>hors-système</strong> (sans compte de connexion — ex&nbsp;: agent d'entretien) peut aussi être ajouté directement depuis cet écran, avec son poste et son salaire de base.</p>
        <x-doc-shot file="personnel-employes.jpg" alt="Liste des employés" />

        <h3>Salaires</h3>
        <p>Génération des bulletins de paie&nbsp;: sélection du mois et de l'année (menus déroulants, l'année en cours étant toujours proposée automatiquement), puis génération pour un employé ou pour toute une sélection à la fois. Un <strong>chef d'escale</strong> avec la permission ne voit et ne peut générer de bulletin que pour le personnel <strong>de sa propre gare</strong>.</p>
        <x-doc-shot file="personnel-salaires.jpg" alt="Liste des salaires, génération de bulletins" />

        <h3>Bulletins générés</h3>
        <p>Historique de tous les bulletins de paie déjà générés, avec téléchargement PDF individuel.</p>
        <x-doc-shot file="personnel-bulletins.jpg" alt="Bulletins de paie générés" />
    </section>

    {{-- ================= 09. IMPRESSION ================= --}}
    <section id="doc-impression" class="doc-section">
        <h2><span class="doc-num">09</span>Impression des reçus</h2>
        <p class="text-muted">Concerne les reçus de billets et de colis, imprimables depuis leurs listes respectives.</p>

        <h3>Imprimante câble/USB</h3>
        <p>Ouvre un PDF classique, imprimable via le pilote habituel de l'imprimante branchée au PC.</p>

        <h3>Imprimante thermique WiFi</h3>
        <p>Nécessite d'avoir installé une fois le « pont d'impression » sur le PC connecté à l'imprimante réseau, fourni séparément à l'équipe technique.</p>

        <h3>Impression depuis un téléphone</h3>
        <p>Le PC hébergeant le pont doit être installé avec l'option réseau local activée. Sur le téléphone (même Wi-Fi), la première tentative d'impression échoue et propose « Configurer l'adresse »&nbsp;: y saisir l'adresse affichée par le PC (ex&nbsp;: <code>192.168.1.50:9200</code>). Elle est ensuite mémorisée sur ce téléphone pour les prochaines impressions.</p>
    </section>

@endsection

@section('scripts')
    <style>
        .doc-intro {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-left: 4px solid var(--primary-color);
            border-radius: .5rem;
            padding: 22px 28px;
            margin-bottom: 20px;
        }

        .doc-toc-card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: .5rem;
            padding: 20px 28px;
            margin-bottom: 24px;
        }

        .doc-toc-label {
            font-size: 11px;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: var(--footer-text);
            margin-bottom: 10px;
        }

        .doc-toc {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .doc-toc a {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            border: 1px solid var(--card-border);
            border-radius: 999px;
            font-size: 13.5px;
            text-decoration: none;
            color: var(--body-text);
        }

        .doc-toc a:hover { border-color: var(--primary-color); color: var(--primary-color); }

        .doc-toc a span {
            font-family: ui-monospace, "SF Mono", Consolas, monospace;
            font-size: 11px;
            color: var(--secondary-color);
        }

        .doc-section {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: .5rem;
            padding: 28px 32px;
            margin-bottom: 24px;
            scroll-margin-top: 16px;
        }

        .doc-section h2 {
            font-size: 22px;
            font-weight: 700;
            border-bottom: 2px solid var(--primary-color);
            padding-bottom: 10px;
            margin-bottom: 14px;
            color: var(--body-text);
        }

        .doc-num {
            font-family: ui-monospace, "SF Mono", Consolas, monospace;
            font-size: 15px;
            color: var(--secondary-color);
            margin-right: 8px;
        }

        .doc-section h3 {
            font-size: 16px;
            font-weight: 700;
            margin: 22px 0 8px;
            color: var(--body-text);
        }

        .doc-section p { max-width: 76ch; color: var(--body-text); }

        .doc-role {
            display: inline-block;
            font-family: ui-monospace, "SF Mono", Consolas, monospace;
            font-size: 10.5px;
            letter-spacing: .05em;
            text-transform: uppercase;
            padding: 2px 8px;
            border-radius: 3px;
            margin-left: 8px;
            vertical-align: 2px;
            font-weight: 600;
        }

        .role-admin { background: rgba(26, 122, 76, .15); color: #1a7a4c; }
        .role-chef { background: rgba(180, 83, 9, .15); color: #b45309; }

        .doc-plain { list-style: none; padding-left: 0; margin: 0; max-width: 76ch; }
        .doc-plain li { position: relative; padding-left: 18px; margin-bottom: 8px; color: var(--body-text); }
        .doc-plain li::before { content: "—"; position: absolute; left: 0; color: var(--footer-text); }

        .doc-steps { padding-left: 20px; max-width: 76ch; color: var(--body-text); }
        .doc-steps li { margin-bottom: 8px; }

        .route-path {
            display: inline-flex;
            align-items: center;
            font-family: ui-monospace, "SF Mono", Consolas, monospace;
            font-size: 13px;
            background: var(--main-bg);
            border: 1px solid var(--card-border);
            padding: 8px 14px;
            border-radius: 4px;
            margin: 6px 0 16px;
            color: var(--secondary-color);
        }

        .doc-shot-img {
            display: block;
            max-width: 100%;
            margin: 10px 0 6px;
            border: 1px solid var(--card-border);
            border-radius: 6px;
            overflow: hidden;
            line-height: 0;
        }

        .doc-shot-img img { display: block; width: 100%; height: auto; }

        .doc-callout {
            border: 1px solid #e8c58a;
            background: #fdf1de;
            border-left: 4px solid #b45309;
            border-radius: 4px;
            padding: 12px 16px;
            margin: 14px 0;
            max-width: 76ch;
        }

        .doc-callout p, .doc-callout li { color: #5c3a0e; }

        .doc-callout-label {
            font-family: ui-monospace, "SF Mono", Consolas, monospace;
            font-size: 10.5px;
            letter-spacing: .05em;
            text-transform: uppercase;
            color: #b45309;
            font-weight: 700;
            margin-bottom: 6px;
        }

        code {
            background: var(--main-bg);
            border: 1px solid var(--card-border);
            border-radius: 3px;
            padding: 1px 5px;
            font-size: .9em;
        }
    </style>
@endsection
