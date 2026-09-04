@php
    // Chemin absolu vers une capture, pour Dompdf (voir DocumentationController::pdf(),
    // setChroot(public_path()) + setIsRemoteEnabled(true)) — même principe que
    // admin/pdf/bulletin-paie.blade.php pour le logo compagnie.
    function docPdfImg(string $file): string
    {
        $path = realpath(public_path('images/documentation/'.$file));

        return $path ? '<img class="pdf-shot" src="file://'.$path.'" alt="">' : '';
    }
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<style>
  @page { margin: 18mm 16mm; }
  body { font-family: "DejaVu Sans", sans-serif; font-size: 11px; color: #1c2128; line-height: 1.55; }
  h1, h2, h3 { font-family: "DejaVu Serif", serif; font-weight: normal; color: #1c2128; }

  .eyebrow { font-family: "DejaVu Sans Mono", monospace; font-size: 9px; letter-spacing: 1px; text-transform: uppercase; color: #1e4d6b; }
  h1 { font-size: 28px; margin: 8px 0 6px; }
  .subtitle { color: #4b5563; font-size: 11.5px; margin-bottom: 12px; }
  .cover-rule { border-top: 2px solid #1e4d6b; margin: 10px 0 20px; }

  h2 { font-size: 16px; border-bottom: 1.5px solid #1e4d6b; padding-bottom: 5px; margin: 22px 0 8px; page-break-after: avoid; }
  h2 .num { font-family: "DejaVu Sans Mono", monospace; font-size: 10px; color: #b45309; margin-right: 6px; }
  h3 { font-size: 12px; margin: 12px 0 5px; font-weight: bold; font-family: "DejaVu Sans", sans-serif; page-break-after: avoid; }
  .role { font-size: 8.5px; font-family: "DejaVu Sans Mono", monospace; text-transform: uppercase; color: #b45309; }

  p { margin: 0 0 7px; }
  ul.plain { margin: 0 0 8px; padding-left: 14px; }
  ul.plain li { margin-bottom: 4px; }
  ol.steps { margin: 0 0 8px; padding-left: 16px; }
  ol.steps li { margin-bottom: 5px; }

  .route-path { font-family: "DejaVu Sans Mono", monospace; font-size: 9.5px; background: #f7f5f0; border: 1px solid #ddd7ca; padding: 5px 9px; color: #1e4d6b; margin: 4px 0 10px; }

  .callout { border: 1px solid #e8c58a; background: #fdf1de; padding: 7px 11px; margin: 8px 0; }
  .callout .callout-label { font-family: "DejaVu Sans Mono", monospace; font-size: 8px; letter-spacing: .5px; text-transform: uppercase; color: #b45309; font-weight: bold; margin-bottom: 3px; }
  .callout p:last-child { margin-bottom: 0; }

  .section { page-break-inside: avoid; }
  .pdf-shot { width: 100%; border: 1px solid #ddd7ca; margin: 6px 0 10px; }
</style>
</head>
<body>

<div class="eyebrow">Sirali</div>
<h1>Manuel d'utilisation</h1>
<p class="subtitle">De la connexion au dernier module, dans l'ordre où vous en avez besoin.</p>
<div class="cover-rule"></div>

<div class="section">
<h2><span class="num">00</span>Connexion & rôles</h2>
<p>Tout commence sur l'écran de connexion (<code>/admin</code>), commun à tous les rôles.</p>
<ol class="steps">
    <li>Saisir l'adresse e-mail et le mot de passe du compte.</li>
    <li>Valider — en cas d'erreur, un message explicite s'affiche.</li>
    <li>Une fois connecté, le menu de gauche s'adapte automatiquement au rôle et aux permissions du compte.</li>
</ol>
{!! docPdfImg('connexion.jpg') !!}
<ul class="plain">
    <li><strong>Admin</strong> — administrateur d'une compagnie : accès complet à tous les modules de sa compagnie.</li>
    <li><strong>PDG</strong> — dirigeant d'une compagnie, en lecture seule.</li>
    <li><strong>Secrétaire Général</strong> — ne reçoit aucune permission par défaut ; l'Admin les accorde au cas par cas.</li>
    <li><strong>Chef d'escale</strong> — responsable d'une gare précise, limité à son propre personnel côté Salaires.</li>
    <li><strong>Utilisateur</strong> — compte opérationnel simple, accès restreint à sa tâche.</li>
    <li><strong>super_admin</strong> — équipe éditrice de la plateforme, non couvert par ce manuel.</li>
</ul>
</div>

<div class="section">
<h2><span class="num">01</span>Configuration</h2>
<p>Réservée à l'Admin. C'est la première chose à mettre en place.</p>
<div class="route-path">Menu → Paramètres → Configuration</div>

<h3>Utilisateurs & permissions</h3>
<p>Création des comptes de la compagnie. Chaque compte reçoit les permissions par défaut de son rôle — sauf le Secrétaire Général, qui n'en reçoit aucune. Ajustables ensuite compte par compte.</p>
{!! docPdfImg('config-utilisateurs.jpg') !!}
{!! docPdfImg('permission-assigner.jpg') !!}
<div class="callout">
    <div class="callout-label">Nouveau module ajouté au système</div>
    <p>Une nouvelle permission n'est automatiquement donnée qu'aux comptes créés après son ajout. Pour un compte existant, il faut la cocher manuellement.</p>
</div>

<h3>Gares</h3>
<p>Points de départ/arrivée de la compagnie. Saisie possible en plusieurs lignes à la fois.</p>
{!! docPdfImg('config-gares.jpg') !!}

<h3>Escale</h3>
<p>Arrêts intermédiaires possibles entre deux gares, utilisés lors de la création d'un trajet.</p>
{!! docPdfImg('config-escale.jpg') !!}

<h3>Horaire</h3>
<p>Créneaux horaires de départ proposés lors de la programmation d'un voyage.</p>
{!! docPdfImg('config-horaire.jpg') !!}

<h3>Cars & Camions & Chauffeurs</h3>
<p>Cars et camions se saisissent en plusieurs lignes à la fois ; les chauffeurs s'ajoutent un par un.</p>
{!! docPdfImg('config-cars-chauffeurs.jpg') !!}

<h3>Place limite</h3>
<p>Nombre de places par défaut appliqué lors de la création d'un trajet/voyage.</p>
{!! docPdfImg('config-place-limite.jpg') !!}
</div>

<div class="section">
<h2><span class="num">02</span>Programmation</h2>
<p>Une fois gares, escales, horaires et cars configurés, on peut créer et programmer les trajets.</p>
<div class="route-path">Menu → Programmation</div>

<h3>Voyages</h3>
<p>Création d'un trajet en 3 étapes : Itinéraire (départ, escales, destination), Horaire, puis Tarification. Le trajet retour est créé automatiquement. Les doublons sont détectés et signalés.</p>
{!! docPdfImg('programme-voyages.jpg') !!}

<h3>Cars <span class="role">Admin · chef d'escale</span></h3>
<p>Affectation d'un ou plusieurs cars, pour une date donnée, aux trajets déjà créés.</p>
{!! docPdfImg('programme-cars.jpg') !!}

<h3>Trajets programmés</h3>
<p>Programmation journalière effective : quel car part de quelle gare, à quelle heure.</p>
{!! docPdfImg('programme-trajets.jpg') !!}

<h3>Transferts</h3>
<p>En cas de voyage annulé ou perturbé, transfert des passagers vers un autre voyage/gare, avec le mouvement de caisse correspondant.</p>
{!! docPdfImg('programme-transferts.jpg') !!}

<h3>État de la flotte</h3>
<p>Vue d'ensemble des cars/camions et de leur situation du jour.</p>
{!! docPdfImg('programme-flotte.jpg') !!}
</div>

<div class="section">
<h2><span class="num">03</span>Caisse</h2>
<p>Doit être ouverte avant de pouvoir vendre un billet, enregistrer un colis, une dépense ou une location de car.</p>
<div class="route-path">Menu → Caisse</div>

<h3>Ma caisse</h3>
<p>Chaque utilisateur ouvre sa propre caisse en début de service et la ferme en fin de journée.</p>
{!! docPdfImg('caisse-ma-caisse.jpg') !!}

<h3>Supervision escale <span class="role">chef d'escale · Admin</span></h3>
<p>Vue d'ensemble de toutes les caisses ouvertes à une gare, et clôture de l'escale en fin de journée.</p>
{!! docPdfImg('caisse-supervision-escale.jpg') !!}

<h3>Rapport compagnie <span class="role">Admin</span></h3>
<p>Vue consolidée de toutes les caisses, toutes gares confondues.</p>
{!! docPdfImg('caisse-rapport-compagnie.jpg') !!}

<h3>Bilan de caisse</h3>
<p>Bilans détaillés des recettes billets et colis par caisse.</p>
{!! docPdfImg('caisse-bilan.jpg') !!}
</div>

<div class="section">
<h2><span class="num">04</span>Billets</h2>
<div class="route-path">Menu → Billetterie</div>

<h3>Achat de ticket</h3>
<p>Vente d'un billet : gare de départ, destination, horaire, informations du client, numéro de place.</p>
{!! docPdfImg('billets-achat.jpg') !!}

<h3>Liste des tickets</h3>
<p>Liste d'embarquement du jour et de demain. Impression du reçu, report, demande/validation d'annulation.</p>
{!! docPdfImg('billets-liste.jpg') !!}

<h3>Historique des billets</h3>
<p>Consultation et réimpression d'un billet à n'importe quelle date passée.</p>
{!! docPdfImg('billets-historique.jpg') !!}

<h3>Embarquement</h3>
<p>Marquage de chaque client comme embarqué, avec compteur en direct.</p>
{!! docPdfImg('billets-embarquement.jpg') !!}

<h3>Ticket en entente</h3>
<p>Validation des réservations en ligne ou en attente de confirmation de paiement.</p>
{!! docPdfImg('billets-entente.jpg') !!}

<h3>Demandes d'annulation <span class="role">Admin</span></h3>
<p>Un chef d'escale demande l'annulation ; l'Admin valide ou refuse définitivement.</p>
{!! docPdfImg('billets-demandes-annulation.jpg') !!}

<h3>Demandes de report <span class="role">chef d'escale · Admin</span></h3>
<p>Pour un client non embarqué, demande de report vers une nouvelle date/heure, validée par l'Admin.</p>
{!! docPdfImg('billets-demandes-report.jpg') !!}

<h3>Rapport billets</h3>
<p>Rapport mensuel et rapport annuel des ventes de billets.</p>
{!! docPdfImg('billets-rapport-mensuel.jpg') !!}
</div>

<div class="section">
<h2><span class="num">05</span>Colis</h2>
<div class="route-path">Menu → Colis</div>

<h3>Liste des colis</h3>
<p>Prise en charge d'un nouveau colis, génère un code unique.</p>
{!! docPdfImg('colis-liste.jpg') !!}

<h3>Envoi des colis</h3>
<p>Affectation d'un colis pris en charge à un car/trajet précis.</p>
{!! docPdfImg('colis-envoi.jpg') !!}

<h3>Mouvement des colis</h3>
<p>Suivi des colis actuellement en transit entre deux gares.</p>
{!! docPdfImg('colis-mouvement.jpg') !!}

<h3>Livraison des colis</h3>
<p>Remise du colis au destinataire à l'arrivée, recherche par code.</p>
{!! docPdfImg('colis-livraison.jpg') !!}

<h3>Réclamation</h3>
<p>Suivi des réclamations liées à un colis.</p>
{!! docPdfImg('colis-reclamations.jpg') !!}

<h3>Historique</h3>
<p>Historique complet des colis enregistrés et livrés.</p>
{!! docPdfImg('colis-historique.jpg') !!}
</div>

<div class="section">
<h2><span class="num">06</span>Finances <span class="role">Admin · chef d'escale</span></h2>
<div class="route-path">Menu → Finances</div>

<h3>Dépenses</h3>
<p>Dépense locale (rattachée à une gare) ou globale (Admin uniquement). Une dépense créée par un chef d'escale reste en attente jusqu'à validation.</p>
{!! docPdfImg('finances-depenses.jpg') !!}

<h3>Bénéfice de la compagnie <span class="role">Admin</span></h3>
<p>Vue consolidée des revenus moins remboursements et dépenses, sur une période.</p>
{!! docPdfImg('finances-benefice.jpg') !!}

<h3>Location des cars</h3>
<p>Location ponctuelle d'un car, en dehors des trajets programmés.</p>
{!! docPdfImg('finances-locations-cars.jpg') !!}
</div>

<div class="section">
<h2><span class="num">07</span>Banque <span class="role">Admin · chef d'escale</span></h2>
<div class="route-path">Menu → Banque</div>

<h3>Comptes banque <span class="role">Admin</span></h3>
<p>Gestion des comptes bancaires de la compagnie.</p>
{!! docPdfImg('banque-comptes.jpg') !!}

<h3>Faire un dépôt</h3>
<p>Enregistrement d'un dépôt en banque.</p>
{!! docPdfImg('banque-depot.jpg') !!}

<h3>Demandes en attente <span class="role">Admin</span></h3>
<p>Confirmation ou rejet des dépôts déclarés par les gares.</p>
{!! docPdfImg('banque-demandes-attente.jpg') !!}

<h3>Historique des dépôts</h3>
<p>Historique de tous les dépôts effectués.</p>
{!! docPdfImg('banque-historique-depots.jpg') !!}
</div>

<div class="section">
<h2><span class="num">08</span>Personnel</h2>
<p>Réservée à l'Admin (le chef d'escale ne voit que le personnel de sa propre gare côté Salaires).</p>
<div class="route-path">Menu → Personnel</div>

<h3>Employés</h3>
<p>Liste du personnel de la compagnie. Un employé hors-système peut être ajouté directement.</p>
{!! docPdfImg('personnel-employes.jpg') !!}

<h3>Salaires</h3>
<p>Génération des bulletins de paie par mois/année. Un chef d'escale ne génère que pour le personnel de sa gare.</p>
{!! docPdfImg('personnel-salaires.jpg') !!}

<h3>Bulletins générés</h3>
<p>Historique de tous les bulletins déjà générés, téléchargeables en PDF.</p>
{!! docPdfImg('personnel-bulletins.jpg') !!}
</div>

<div class="section">
<h2><span class="num">09</span>Impression des reçus</h2>
<p>Concerne les reçus de billets et de colis.</p>

<h3>Imprimante câble/USB</h3>
<p>Ouvre un PDF classique, imprimable via le pilote habituel de l'imprimante branchée au PC.</p>

<h3>Imprimante thermique WiFi</h3>
<p>Nécessite d'avoir installé une fois le « pont d'impression » sur le PC connecté à l'imprimante réseau.</p>

<h3>Impression depuis un téléphone</h3>
<p>Sur le téléphone (même Wi-Fi), configurer l'adresse affichée par le PC (ex : 192.168.1.50:9200), mémorisée ensuite pour les prochaines impressions.</p>
</div>

</body>
</html>
