<?php

namespace Database\Seeders;

use App\Models\Agence;
use App\Models\Banque;
use App\Models\Billet;
use App\Models\CaisseUtilisateur;
use App\Models\Car;
use App\Models\Chauffeur;
use App\Models\Colis;
use App\Models\Compagnie;
use App\Models\Depense;
use App\Models\DepotBanque;
use App\Models\Destinataire;
use App\Models\Escale;
use App\Models\Expediteur;
use App\Models\Horaire;
use App\Models\LiaisonCarTrajet;
use App\Models\LigneTrajet;
use App\Models\LocationCar;
use App\Models\Permission;
use App\Models\PlaceMinimale;
use App\Models\ProgrammationVoyage;
use App\Models\Programme;
use App\Models\ReferenceCar;
use App\Models\Utilisateur;
use App\Models\VersementCaisse;
use App\Services\BanqueService;
use App\Services\BilletService;
use App\Services\CaisseUtilisateurService;
use App\Services\DepenseService;
use App\Services\DepotBanqueService;
use App\Services\EnvoiColisService;
use App\Services\LocationCarService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

/**
 * Jeu de données de démo pour tester manuellement TOUTES les fonctionnalités de l'admin
 * (Configuration, G-programme, Caisse, Dépenses/Banque, Location des cars, Employés,
 * Billets, Colis) sur une compagnie complète. Réutilise les Services applicatifs
 * existants (BilletService, CaisseUtilisateurService, DepenseService, BanqueService,
 * DepotBanqueService, LocationCarService, EnvoiColisService) plutôt que de réinsérer du
 * SQL brut, pour garantir que les données générées respectent exactement les mêmes
 * règles métier que les écrans réels (calcul de prix serveur, crédit de caisse,
 * verrous, etc).
 *
 * Volontairement laissé "à moitié fait" par endroits (caisses jamais ouvertes/fermées,
 * cars jamais programmés, demandes en attente) pour que chaque rôle ait quelque chose à
 * faire en se connectant, plutôt que de tout pré-remplir.
 */
class DemoDataSeeder extends Seeder
{
    private Compagnie $compagnie;

    private Agence $bamako;

    private Agence $segou;

    private Agence $sikasso;

    private CaisseUtilisateurService $caisseService;

    private BilletService $billetService;

    public function run(): void
    {
        $this->caisseService = new CaisseUtilisateurService;
        $this->billetService = new BilletService;

        $this->compagnie = Compagnie::firstOrCreate(
            ['nom_compagnie' => 'ANN EXPRESS'],
            ['libele' => 'ANN Express Voyages', 'slogant' => 'Voyagez en confiance']
        );

        $this->seedAgences();
        $users = $this->seedUtilisateurs();
        $this->seedConfigurationDeBase();
        $cars = $this->seedCarsEtChauffeurs();
        $programmes = $this->seedProgrammes();
        $this->seedAffectationCars($cars, $programmes);
        $this->seedTrajetDuJour($cars, $users);
        $this->seedCaissesEtBillets($users);
        $this->seedColis($users, $cars);
        $this->seedDepenses($users);
        $banques = $this->seedBanques();
        $this->seedDepotsBanque($users, $banques);
        $this->seedLocationCar($users, $cars);

        $this->command?->info('Données de démo créées pour '.$this->compagnie->nom_compagnie.'.');
    }

    private function seedAgences(): void
    {
        $this->bamako = Agence::firstOrCreate(
            ['id_compagnie' => $this->compagnie->id_compagnie, 'localite' => 'Bamako'],
            ['code' => 1, 'numeroGare' => 'BKO-01', 'tel' => '+22300000000', 'status' => 1]
        );

        $this->segou = Agence::firstOrCreate(
            ['id_compagnie' => $this->compagnie->id_compagnie, 'localite' => 'Ségou'],
            ['code' => 2, 'numeroGare' => 'SEG-01', 'tel' => '+22300000001', 'status' => 1]
        );

        $this->sikasso = Agence::firstOrCreate(
            ['id_compagnie' => $this->compagnie->id_compagnie, 'localite' => 'Sikasso'],
            ['code' => 3, 'numeroGare' => 'SIK-01', 'tel' => '+22300000002', 'status' => 1]
        );
    }

    /**
     * @return array<string, Utilisateur>
     */
    private function seedUtilisateurs(): array
    {
        $creer = function (string $email, string $nom, string $droit, ?int $idAgence, ?string $profile = null): Utilisateur {
            $user = Utilisateur::updateOrCreate(
                ['emailUser' => $email],
                [
                    'utilisateurs' => $nom,
                    'droit' => $droit,
                    'motPasse' => Hash::make('password'),
                    'status' => 1,
                    'id_agence' => $idAgence,
                    'id_compagnie' => $this->compagnie->id_compagnie,
                    'profile' => $profile,
                ]
            );
            Permission::assignPermissionsParDefautPourRole($user->idUser, $droit, $profile);

            return $user;
        };

        return [
            // super_admin/Admin déjà créés par StaffDemoSeeder — on les récupère plutôt que
            // de dupliquer la logique de création.
            'super_admin' => Utilisateur::where('emailUser', 'superadmin@transhub.test')->firstOrFail(),
            'admin' => $creer('admin.compagnie@transhub.test', 'Admin Compagnie', 'Admin', $this->bamako->idAgence),
            'pdg' => $creer('pdg@transhub.test', 'Directeur Général', 'PDG', null),
            'chef_bamako' => $creer('chef.bamako@transhub.test', "Chef d'escale Bamako", 'chef_d_escale', $this->bamako->idAgence),
            'chef_segou' => $creer('chef.segou@transhub.test', "Chef d'escale Ségou", 'chef_d_escale', $this->segou->idAgence),
            'billet_bamako' => $creer('billet.bamako@transhub.test', 'Agent Billetterie Bamako', 'Utilisateur', $this->bamako->idAgence, 'billet'),
            'colis_bamako' => $creer('colis.bamako@transhub.test', 'Agent Colis Bamako', 'Utilisateur', $this->bamako->idAgence, 'colis'),
        ];
    }

    private function seedConfigurationDeBase(): void
    {
        Escale::firstOrCreate(['escales' => 'Fana', 'id_compagnie' => $this->compagnie->id_compagnie]);

        foreach (['06:00:00', '08:00:00', '10:00:00', '14:00:00', '16:00:00', '18:00:00'] as $heure) {
            Horaire::firstOrCreate(['heuredepart' => $heure, 'id_compagnie' => $this->compagnie->id_compagnie]);
        }

        PlaceMinimale::updateOrCreate(
            ['id_compagnie' => $this->compagnie->id_compagnie],
            ['place_minumale' => 70]
        );
    }

    /**
     * @return array<string, Car>
     */
    private function seedCarsEtChauffeurs(): array
    {
        $definitions = [
            'C101' => ['numero_car' => 101, 'matriculle' => 'AB-1001-MA', 'nbr_place' => 70, 'chauffeur' => 'Issa Traoré'],
            'C102' => ['numero_car' => 102, 'matriculle' => 'AB-1002-MA', 'nbr_place' => 70, 'chauffeur' => 'Moussa Keita'],
            'C103' => ['numero_car' => 103, 'matriculle' => 'AB-1003-MA', 'nbr_place' => 60, 'chauffeur' => 'Sidiki Coulibaly'],
            'C104' => ['numero_car' => 104, 'matriculle' => 'AB-1004-MA', 'nbr_place' => 60, 'chauffeur' => 'Boubacar Diarra'],
        ];

        $cars = [];
        foreach ($definitions as $cle => $def) {
            $car = Car::firstOrCreate(
                ['numero_car' => $def['numero_car'], 'id_compagnie' => $this->compagnie->id_compagnie],
                ['matriculle' => $def['matriculle'], 'nbr_place' => $def['nbr_place'], 'nbr_place_reserve' => 0, 'programmer_car' => 'off']
            );

            Chauffeur::firstOrCreate(
                ['id_car' => $car->id_car, 'id_compagnie' => $this->compagnie->id_compagnie],
                ['nom_prenom' => $def['chauffeur'], 'numero' => '+22370'.($def['numero_car']).'00']
            );

            $cars[$cle] = $car;
        }

        return $cars;
    }

    /**
     * @return array<string, Programme>
     */
    private function seedProgrammes(): array
    {
        [$bamSegAller8, $bamSegRetour8] = $this->creerVoyageAvecRetour($this->bamako->idAgence, $this->segou->idAgence, '08:00:00', 5000);
        [$bamSegAller14, $bamSegRetour14] = $this->creerVoyageAvecRetour($this->bamako->idAgence, $this->segou->idAgence, '14:00:00', 5000);

        $fana = Escale::where('escales', 'Fana')->where('id_compagnie', $this->compagnie->id_compagnie)->firstOrFail();
        [$bamSikAller10, $bamSikRetour10] = $this->creerVoyageAvecRetour(
            $this->bamako->idAgence, $this->sikasso->idAgence, '10:00:00', 7000,
            [$fana->id_escale => 4000]
        );

        return [
            'bamako_segou_08_aller' => $bamSegAller8,
            'bamako_segou_08_retour' => $bamSegRetour8,
            'bamako_segou_14_aller' => $bamSegAller14,
            'bamako_segou_14_retour' => $bamSegRetour14,
            'bamako_sikasso_10_aller' => $bamSikAller10,
            'bamako_sikasso_10_retour' => $bamSikRetour10,
        ];
    }

    /**
     * Port simplifié de ProgrammeController::store()/assurerTrajetRetour() : crée un
     * voyage et son retour automatique (avec les mêmes escales/tarifs), en réutilisant
     * l'existant si déjà présent pour rester rejouable.
     *
     * @param  array<int, float>  $escales  id_escale => prix_escale
     * @return array{0: Programme, 1: Programme}
     */
    private function creerVoyageAvecRetour(int $idDepart, int $idDestination, string $heureDepart, int $prix, array $escales = []): array
    {
        $rdv = Carbon::createFromFormat('H:i:s', $heureDepart)->subMinutes(45)->format('H:i:s');

        $aller = Programme::firstOrCreate(
            ['idDepart' => $idDepart, 'idDestination' => $idDestination, 'heureDepart' => $heureDepart, 'id_compagnie' => $this->compagnie->id_compagnie],
            ['rdv' => $rdv, 'prix' => $prix]
        );
        $this->enregistrerEscales($aller, $escales);

        $retour = Programme::firstOrCreate(
            ['idDepart' => $idDestination, 'idDestination' => $idDepart, 'heureDepart' => $heureDepart, 'id_compagnie' => $this->compagnie->id_compagnie],
            ['rdv' => $rdv, 'prix' => $prix]
        );
        $this->enregistrerEscales($retour, $escales);

        return [$aller, $retour];
    }

    /**
     * @param  array<int, float>  $escales
     */
    private function enregistrerEscales(Programme $programme, array $escales): void
    {
        foreach ($escales as $idEscale => $prixEscale) {
            LigneTrajet::firstOrCreate(
                ['id_escales' => $idEscale, 'id_trajets' => $programme->idProgrammer, 'type_trajet' => 'programmer'],
                ['prix_escale' => $prixEscale]
            );
        }
    }

    /**
     * @param  array<string, Car>  $cars
     * @param  array<string, Programme>  $programmes
     */
    private function seedAffectationCars(array $cars, array $programmes): void
    {
        // C101 est délibérément laissé sans affectation : sert à tester tout le cycle
        // "Cars (affectation)" -> "Trajets programmés" depuis un car neuf.
        $this->programmerCarSurTrajets($cars['C102'], [$programmes['bamako_segou_08_aller'], $programmes['bamako_segou_08_retour']]);
        $this->programmerCarSurTrajets($cars['C103'], [$programmes['bamako_sikasso_10_aller'], $programmes['bamako_sikasso_10_retour']]);
        $this->programmerCarSurTrajets($cars['C104'], [$programmes['bamako_segou_08_aller'], $programmes['bamako_segou_08_retour']]);
    }

    /**
     * @param  array<Programme>  $trajets
     */
    private function programmerCarSurTrajets(Car $car, array $trajets): void
    {
        foreach ($trajets as $trajet) {
            LiaisonCarTrajet::firstOrCreate([
                'id_car' => $car->id_car,
                'id_trajets' => $trajet->idProgrammer,
                'id_compagnie' => $this->compagnie->id_compagnie,
            ]);
        }

        ReferenceCar::firstOrCreate(['id_car' => $car->id_car]);
        $car->update(['programmer_car' => 'on']);
    }

    /**
     * @param  array<string, Car>  $cars
     * @param  array<string, Utilisateur>  $users
     */
    private function seedTrajetDuJour(array $cars, array $users): void
    {
        // C102 : déjà programmé et parti aujourd'hui vers Ségou (08:00) — pour tester
        // "Cars en transit"/État de la flotte/Embarquement immédiatement.
        ProgrammationVoyage::firstOrCreate(
            [
                'id_car_programmer' => $cars['C102']->id_car,
                'id_horaire' => '08:00:00',
                'date_enregistre' => now()->toDateString(),
                'id_compagnie' => $this->compagnie->id_compagnie,
            ],
            [
                'id_trajet' => 'Ségou',
                'localite_user' => 'Bamako',
                'id_agence' => $this->bamako->idAgence,
                'id_agence_destination' => $this->segou->idAgence,
                'statut' => 'active',
                'decolle_le' => now()->subHours(2),
                'decolle_par' => $users['chef_bamako']->idUser,
            ]
        );
        $cars['C102']->update(['status_car' => 'En_transit_Ségou']);

        // C103 : de retour à Bamako, prêt à être programmé aujourd'hui par le chef
        // d'escale via le tableau de bord "Trajets programmés" (non fait ici exprès).
        $cars['C103']->update(['status_car' => 'Bamako']);

        // C104 : basé à Ségou, prêt à être programmé par son propre chef d'escale.
        $cars['C104']->update(['status_car' => 'Ségou']);
    }

    /**
     * @param  array<string, Utilisateur>  $users
     */
    private function seedCaissesEtBillets(array $users): void
    {
        if (Billet::where('idUser', $users['billet_bamako']->idUser)->exists()) {
            return; // déjà semé lors d'un run précédent
        }

        $this->caisseService->ouvrirCaisse($users['billet_bamako'], null, 20000);
        $this->caisseService->ouvrirCaisse($users['colis_bamako'], null, 10000);
        $this->caisseService->ouvrirCaisse($users['chef_bamako'], null, 15000);

        $aujourdhui = now()->toDateString();
        $demain = now()->addDay()->toDateString();

        // Aujourd'hui, sur le trajet Bamako -> Ségou 08:00 (C102, déjà programmé).
        $billetA = $this->billetService->creerReservation($users['billet_bamako'], [
            'Client' => 'Awa Diarra', 'jourVoyage' => $aujourdhui, 'programme' => '08:00:00',
            'destinationId' => 'Ségou', 'nombrePassages' => 2,
        ], $this->caisseService);

        $billetB = $this->billetService->creerReservation($users['billet_bamako'], [
            'Client' => 'Moussa Coulibaly', 'jourVoyage' => $aujourdhui, 'programme' => '08:00:00',
            'destinationId' => 'Ségou', 'nombrePassages' => 1,
        ], $this->caisseService);

        $this->billetService->creerReservation($users['billet_bamako'], [
            'Client' => 'Sekou Bagayoko', 'jourVoyage' => $aujourdhui, 'programme' => '08:00:00',
            'destinationId' => 'Ségou', 'nombrePassages' => 1,
        ], $this->caisseService);

        // Demain, sur Bamako -> Sikasso 10:00 (mécanisme `suivis`, aucun car requis).
        $this->billetService->creerReservation($users['billet_bamako'], [
            'Client' => 'Fatoumata Keita', 'jourVoyage' => $demain, 'programme' => '10:00:00',
            'destinationId' => 'Sikasso', 'nombrePassages' => 3,
        ], $this->caisseService);

        $this->billetService->creerReservation($users['billet_bamako'], [
            'Client' => 'Ibrahim Sangaré', 'jourVoyage' => $demain, 'programme' => '10:00:00',
            'destinationId' => 'Sikasso', 'escale' => 'Fana', 'nombrePassages' => 1,
        ], $this->caisseService);

        // Une demande de report (par l'agent lui-même, en attente de transmission par le
        // chef d'escale) et une demande d'annulation (par le chef d'escale, en attente
        // de confirmation Admin) : de quoi tester les deux écrans immédiatement.
        if (! empty($billetA['idBillet'])) {
            $this->billetService->demanderReport($users['billet_bamako'], $billetA['idBillet'], $demain, '14:00:00');
        }
        if (! empty($billetB['idBillet'])) {
            $this->billetService->demanderAnnulation($users['chef_bamako'], $billetB['idBillet'], 'Client a annulé son voyage.');
        }

        // Caisse de la billetterie : fermée puis versée au chef d'escale, laissée EN
        // ATTENTE pour que le chef d'escale teste lui-même Valider/Rejeter.
        $this->caisseService->fermerCaisse($users['billet_bamako'], 35000);
        $caisseBillet = $this->caisseService->getCaisseFermeeNonVersee($users['billet_bamako']->idUser)
            ?? CaisseUtilisateur::where('id_utilisateur', $users['billet_bamako']->idUser)->latest('id_caisse_user')->first();
        if ($caisseBillet) {
            $this->caisseService->creerVersement($users['billet_bamako'], $users['chef_bamako']->idUser, 35000, 'Versement fin de journée');
        }

        // Caisse colis : intégralement bouclée (fermée, versée ET validée) pour donner
        // un solde disponible réel au chef d'escale sur l'écran Dépôt en banque.
        $this->caisseService->fermerCaisse($users['colis_bamako'], 30000);
        $this->caisseService->creerVersement($users['colis_bamako'], $users['chef_bamako']->idUser, 30000, 'Versement colis du jour');
        $versementColis = VersementCaisse::where('id_chef_escale', $users['chef_bamako']->idUser)
            ->where('montant', 30000)->latest('id_versement')->first();
        if ($versementColis) {
            $this->caisseService->validerVersement($users['chef_bamako'], $versementColis->id_versement, 'valide', null);
        }

        // La caisse du chef d'escale (Bamako) reste OUVERTE : sert pour les dépenses/
        // locations ci-dessous, et laisse un cycle complet à tester en se connectant.
    }

    /**
     * @param  array<string, Utilisateur>  $users
     * @param  array<string, Car>  $cars
     */
    private function seedColis(array $users, array $cars): void
    {
        if (Colis::where('id_utilisateur', $users['colis_bamako']->idUser)->exists()) {
            return;
        }

        $envoiService = new EnvoiColisService;
        $user = $users['colis_bamako'];

        $definitions = [
            ['exp' => 'Kadiatou Sanogo', 'dest' => 'Aly Sidibé', 'nom_colis' => 'Documents', 'nature' => 'Documents', 'destination' => $this->segou, 'valeur' => 5000, 'fraix' => 2000, 'envoyer' => false, 'statut_final' => 'enregistre'],
            ['exp' => 'Drissa Konaté', 'dest' => 'Mamadou Touré', 'nom_colis' => 'Colis vêtements', 'nature' => 'Habillement', 'destination' => $this->segou, 'valeur' => 20000, 'fraix' => 3000, 'envoyer' => true, 'statut_final' => 'en_cours'],
            ['exp' => 'Bintou Camara', 'dest' => 'Yacouba Ouattara', 'nom_colis' => 'Pièces auto', 'nature' => 'Pièces détachées', 'destination' => $this->segou, 'valeur' => 50000, 'fraix' => 5000, 'envoyer' => true, 'statut_final' => 'recu'],
            ['exp' => 'Assitan Doumbia', 'dest' => 'Karim Berthé', 'nom_colis' => 'Colis électronique', 'nature' => 'Électronique', 'destination' => $this->sikasso, 'valeur' => 80000, 'fraix' => 6000, 'envoyer' => true, 'statut_final' => 'livre'],
            ['exp' => 'Salimata Diakité', 'dest' => 'Adama Koné', 'nom_colis' => 'Malle', 'nature' => 'Bagage', 'destination' => $this->sikasso, 'valeur' => 15000, 'fraix' => 4000, 'envoyer' => false, 'statut_final' => 'enregistre', 'reclamation' => true],
        ];

        foreach ($definitions as $def) {
            $idExpediteur = Expediteur::create([
                'expediteur' => $def['exp'], 'numero_exp' => '+22376000000', 'whatsapp_exp' => '+22376000000',
            ])->id_expediteur;

            $idDestinataire = Destinataire::create([
                'destinataire' => $def['dest'], 'numero_dest' => '+22377000000', 'whatsapp_dest' => '+22377000000',
                'id_exp' => $idExpediteur,
            ])->id_destinataire;

            $codeColis = strtoupper(bin2hex(random_bytes(3)));

            $colis = Colis::create([
                'nom_colis' => $def['nom_colis'],
                'nature' => $def['nature'],
                'provient_de' => 'Bamako',
                'id_agence' => $def['destination']->idAgence,
                'valeur' => $def['valeur'],
                'fraix_transaction' => $def['fraix'],
                'id_expediteur' => $idExpediteur,
                'id_destinataire' => $idDestinataire,
                'id_utilisateur' => $user->idUser,
                'date_enregistrement' => now()->toDateString(),
                'code_colis' => $codeColis,
                'num_gare' => $this->bamako->numeroGare,
                'status' => 'enregistre',
                'id_compagnie' => $this->compagnie->id_compagnie,
            ]);

            $this->caisseService->crediterColis($user->idUser, (float) $def['fraix'], $codeColis, $colis->id_colis);

            if ($def['envoyer']) {
                $idCar = $def['destination']->idAgence === $this->segou->idAgence ? $cars['C102']->id_car : $cars['C103']->id_car;
                $envoiService->traiterEnvoi1([$colis->id_colis], $idCar, $this->compagnie->id_compagnie);

                if ($def['statut_final'] === 'recu') {
                    $colis->update(['status' => 'recu']);
                } elseif ($def['statut_final'] === 'livre') {
                    $colis->update(['status' => 'livre', 'date_livraison' => now()->toDateString()]);
                }
            }

            if (! empty($def['reclamation'])) {
                $colis->update([
                    'reclamer' => 1,
                    'date_reclamer' => now()->toDateString(),
                    'motif_reclamation' => 'Colis arrivé endommagé.',
                    'montant_remboursement' => $def['valeur'],
                    'status_reclamation' => 'En attente',
                ]);
            }
        }
    }

    /**
     * @param  array<string, Utilisateur>  $users
     */
    private function seedDepenses(array $users): void
    {
        if (Depense::where('id_utilisateur', $users['chef_bamako']->idUser)->exists()) {
            return;
        }

        $service = new DepenseService;
        $aujourdhui = now()->toDateString();

        // Globale (Admin), validée immédiatement.
        $service->saveDepense($users['admin'], [
            'portee' => 'globale', 'categorie' => 'Salaire', 'montant' => 150000, 'date_depense' => $aujourdhui,
        ]);

        // Locale, initiée par le chef d'escale : reste EN ATTENTE (à valider/rejeter par l'Admin).
        $service->saveDepense($users['chef_bamako'], [
            'portee' => 'locale', 'categorie' => 'Carburant', 'montant' => 25000, 'date_depense' => $aujourdhui,
        ]);

        // Locale, initiée par l'Admin pour la gare de Bamako : validée immédiatement.
        $service->saveDepense($users['admin'], [
            'portee' => 'locale', 'categorie' => 'Entretien/Reparation', 'montant' => 40000,
            'date_depense' => $aujourdhui, 'id_agence' => $this->bamako->idAgence,
        ]);
    }

    /**
     * @return array<string, Banque>
     */
    private function seedBanques(): array
    {
        $service = new BanqueService;

        if (! Banque::where('id_compagnie', $this->compagnie->id_compagnie)->where('nom', 'BDM SA')->exists()) {
            $service->creerBanque($this->compagnie->id_compagnie, ['nom' => 'BDM SA', 'numero_compte' => 'ML0312345678901234']);
            $service->creerBanque($this->compagnie->id_compagnie, ['nom' => 'Ecobank Mali', 'numero_compte' => 'ML0398765432109876']);
        }

        return Banque::where('id_compagnie', $this->compagnie->id_compagnie)->get()->keyBy('nom')->all();
    }

    /**
     * @param  array<string, Utilisateur>  $users
     * @param  array<string, Banque>  $banques
     */
    private function seedDepotsBanque(array $users, array $banques): void
    {
        if (DepotBanque::where('id_utilisateur_demandeur', $users['chef_bamako']->idUser)->exists()) {
            return;
        }

        $service = new DepotBanqueService;

        // En attente : pour que l'Admin teste Confirmer/Rejeter.
        $service->creerDemande($users['chef_bamako'], [
            'montant' => 15000, 'id_banque' => $banques['BDM SA']->id_banque, 'reference' => 'DEP-DEMO-1',
        ]);

        // Confirmée tout de suite : pour peupler l'historique + les mouvements banque.
        $resultat = $service->creerDemande($users['chef_bamako'], [
            'montant' => 5000, 'id_banque' => $banques['Ecobank Mali']->id_banque, 'reference' => 'DEP-DEMO-2',
        ]);
        if ($resultat['ok']) {
            $depot = DepotBanque::where('id_compagnie', $this->compagnie->id_compagnie)
                ->where('reference', 'DEP-DEMO-2')->latest('id_depot')->first();
            if ($depot) {
                $service->confirmerDemande($depot->id_depot, $users['admin']);
            }
        }
    }

    /**
     * @param  array<string, Utilisateur>  $users
     * @param  array<string, Car>  $cars
     */
    private function seedLocationCar(array $users, array $cars): void
    {
        if (LocationCar::where('id_utilisateur', $users['chef_bamako']->idUser)->exists()) {
            return;
        }

        $service = new LocationCarService;
        $demain = now()->addDay()->toDateString();
        $dansTroisJours = now()->addDays(3)->toDateString();
        $dansDeuxJours = now()->addDays(2)->toDateString();

        // En attente : initiée par le chef d'escale, pour que l'Admin teste Valider/Rejeter.
        $service->saveLocation($users['chef_bamako'], [
            'destination' => 'Kita', 'id_car' => $cars['C103']->id_car,
            'nom_client' => 'Oumar', 'prenom_client' => 'Diallo', 'telephone_client' => '+22370000001',
            'date_depart' => $demain, 'date_retour_prevu' => $dansTroisJours, 'frais_location' => 100000,
        ]);

        // Validée directement par l'Admin : pour tester tout de suite la facture imprimable.
        $service->saveLocation($users['admin'], [
            'id_agence_depart' => $this->bamako->idAgence, 'destination' => 'Nioro',
            'id_car' => $cars['C101']->id_car,
            'nom_client' => 'Aissata', 'prenom_client' => 'Cissé', 'telephone_client' => '+22370000002',
            'date_depart' => $demain, 'date_retour_prevu' => $dansDeuxJours, 'frais_location' => 90000,
        ]);
    }
}
