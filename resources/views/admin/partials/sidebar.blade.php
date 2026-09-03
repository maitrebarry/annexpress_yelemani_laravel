@php
    $authUser = auth('staff')->user()->load('agence');
    $droit = $authUser->droit;
    $ville = $authUser->agence?->localite;
    $numeroGare = $authUser->agence?->numeroGare;

    $navAlerts = app(\App\Services\NavAlertsService::class);

    $carsCompletsSidebar = collect();
    if ($authUser->userHasPermission('Billets_embarquement')) {
        $isAdminSidebar = in_array($droit, ['Admin', 'PDG', 'secretaire'], true);
        $carsCompletsSidebar = collect($navAlerts->getCarsComplets(
            $isAdminSidebar ? null : $ville,
            $isAdminSidebar ? null : $numeroGare,
            $authUser->id_compagnie
        ));
    }

    $demandesReportSidebar = collect();
    if ($authUser->userHasPermission('Billets_annulation')) {
        if (in_array($droit, ['Admin', 'super_admin', 'PDG', 'secretaire'], true)) {
            $demandesReportSidebar = collect($navAlerts->getDemandesReportEnAttente($authUser->id_compagnie));
        } elseif ($droit === 'chef_d_escale') {
            $demandesReportSidebar = collect($navAlerts->getDemandesReportEnAttenteChef($authUser->id_compagnie, $ville, $numeroGare));
        }
    }

    $nbPartenairesEnAttente = $droit === 'super_admin' ? $navAlerts->countPartenairesEnAttente() : 0;

    // Active-state : on compare le chemin de la requête au chemin du lien (comparaison par
    // préfixe, ces écrans n'ayant pas tous une route nommée exploitable via routeIs()).
    $isActive = function (string $path) {
        return request()->is(ltrim($path, '/')) || request()->is(ltrim($path, '/') . '/*') ? 'active' : '';
    };
    // Un groupe (dropdown) est ouvert par défaut si la page actuelle est un de ses enfants —
    // sinon l'utilisateur atterrit sur un écran dont le menu parent est fermé, déroutant.
    $groupActive = function (array $paths) {
        foreach ($paths as $path) {
            if (request()->is(ltrim($path, '/')) || request()->is(ltrim($path, '/') . '/*')) {
                return true;
            }
        }
        return false;
    };
@endphp
<aside class="sidebar" id="sidebar">
    <div class="position-sticky pt-3">
        <button type="button" id="sidebarCollapseBtn" class="sidebar-collapse-btn" aria-label="Réduire / Agrandir le menu">
            <i class="fas fa-chevron-left"></i>
        </button>
        <nav class="nav flex-column">

        <a class="nav-link {{ $isActive('admin/Homes/home') }}" href="{{ url('/admin/Homes/home') }}">
            <i class="fas fa-house"></i> <span class="nav-text">Accueil</span>
        </a>

        @if ($droit !== 'super_admin')
            {{-- ========== GROUPE : BILLETTERIE (le plus utilisé au quotidien) ========== --}}
            @php
                $peutVoirBilletterie = $authUser->userHasPermission('Billets_creation')
                    || $authUser->userHasPermission('Billets_apercue')
                    || $authUser->userHasPermission('Billets_embarquement')
                    || $authUser->userHasPermission('Billets_validation')
                    || $authUser->userHasPermission('Billets_rapport')
                    || in_array($droit, ['Admin', 'super_admin', 'PDG', 'secretaire'], true);
                $billetteriePaths = [
                    'admin/Add_billets', 'admin/Liste_du_jours', 'admin/Liste_du_jours/historique',
                    'admin/Liste_du_jours/embarquement', 'admin/Liste_ententes',
                    'admin/Liste_du_jours/demandesAnnulation', 'admin/Liste_du_jours/demandesReport',
                    'admin/Rapport_billets/rapport_billets', 'admin/Rapport_billets/rapport_annuel',
                ];
                $billetterieOuvert = $groupActive($billetteriePaths);
                $alertesBilletterie = $carsCompletsSidebar->count() + $demandesReportSidebar->count();
            @endphp
            @if ($peutVoirBilletterie)
                <a class="nav-link nav-link-group {{ $billetterieOuvert ? '' : 'collapsed' }}" href="#groupBilletterie" data-bs-toggle="collapse" role="button" aria-expanded="{{ $billetterieOuvert ? 'true' : 'false' }}">
                    <i class="fas fa-ticket"></i>
                    <span class="nav-text flex-grow-1">Billetterie</span>
                    @if ($alertesBilletterie > 0)
                        <span class="badge bg-danger rounded-pill me-1">{{ $alertesBilletterie }}</span>
                    @endif
                    <i class="fas fa-chevron-down nav-caret"></i>
                </a>
                <div class="collapse {{ $billetterieOuvert ? 'show' : '' }}" id="groupBilletterie">
                    <div class="nav flex-column nav-subgroup">
                        @if ($authUser->userHasPermission('Billets_creation') && !$authUser->estLectureSeule())
                            <a class="nav-link {{ $isActive('admin/Add_billets') }}" href="{{ url('/admin/Add_billets') }}">
                                <i class="fas fa-circle-plus"></i> <span class="nav-text">Achat de ticket</span>
                            </a>
                        @endif
                        @if ($authUser->userHasPermission('Billets_apercue'))
                            <a class="nav-link {{ $isActive('admin/Liste_du_jours') }}" href="{{ url('/admin/Liste_du_jours') }}">
                                <i class="fas fa-list-ul"></i> <span class="nav-text">Liste des tickets</span>
                            </a>
                            <a class="nav-link {{ $isActive('admin/Liste_du_jours/historique') }}" href="{{ url('/admin/Liste_du_jours/historique') }}">
                                <i class="fas fa-clock-rotate-left"></i> <span class="nav-text">Historique des billets</span>
                            </a>
                        @endif
                        @if ($authUser->userHasPermission('Billets_embarquement'))
                            <a class="nav-link d-flex align-items-center {{ $isActive('admin/Liste_du_jours/embarquement') }}" href="{{ url('/admin/Liste_du_jours/embarquement') }}">
                                <i class="fas fa-bus"></i> <span class="nav-text flex-grow-1">Embarquement</span>
                                @if ($carsCompletsSidebar->isNotEmpty())
                                    <span class="badge bg-danger rounded-pill" title="Bus complet(s), embarquement requis">{{ $carsCompletsSidebar->count() }}</span>
                                @endif
                            </a>
                        @endif
                        @if ($authUser->userHasPermission('Billets_validation'))
                            <a class="nav-link {{ $isActive('admin/Liste_ententes') }}" href="{{ url('/admin/Liste_ententes') }}">
                                <i class="fas fa-handshake"></i> <span class="nav-text">Ticket en entente</span>
                            </a>
                        @endif
                        @if (in_array($droit, ['Admin', 'super_admin', 'PDG', 'secretaire'], true))
                            <a class="nav-link {{ $isActive('admin/Liste_du_jours/demandesAnnulation') }}" href="{{ url('/admin/Liste_du_jours/demandesAnnulation') }}">
                                <i class="fas fa-circle-xmark"></i> <span class="nav-text">Demandes d'annulation</span>
                            </a>
                        @endif
                        @if (in_array($droit, ['Admin', 'super_admin', 'PDG', 'secretaire', 'chef_d_escale'], true) && $authUser->userHasPermission('Billets_annulation'))
                            <a class="nav-link d-flex align-items-center {{ $isActive('admin/Liste_du_jours/demandesReport') }}" href="{{ url('/admin/Liste_du_jours/demandesReport') }}">
                                <i class="fas fa-rotate-left"></i> <span class="nav-text flex-grow-1">Demandes de report</span>
                                @if ($demandesReportSidebar->isNotEmpty())
                                    <span class="badge bg-warning text-dark rounded-pill" title="Demande(s) de report en attente">{{ $demandesReportSidebar->count() }}</span>
                                @endif
                            </a>
                        @endif
                        @if ($authUser->userHasPermission('Billets_rapport'))
                            <a class="nav-link {{ $isActive('admin/Rapport_billets/rapport_billets') }}" href="{{ url('/admin/Rapport_billets/rapport_billets') }}">
                                <i class="fas fa-chart-column"></i> <span class="nav-text">Rapport mensuel</span>
                            </a>
                            <a class="nav-link {{ $isActive('admin/Rapport_billets/rapport_annuel') }}" href="{{ url('/admin/Rapport_billets/rapport_annuel') }}">
                                <i class="fas fa-chart-column"></i> <span class="nav-text">Rapport annuel</span>
                            </a>
                        @endif
                    </div>
                </div>
            @endif

            {{-- ========== GROUPE : COLIS ========== --}}
            @php
                $peutVoirColis = $authUser->userHasPermission('colis_creation')
                    || $authUser->userHasPermission('colis_envoi')
                    || $authUser->userHasPermission('colis_mouvement')
                    || $authUser->userHasPermission('colis_livraison')
                    || $authUser->userHasPermission('colis_reclamation')
                    || $authUser->userHasPermission('colis_historique');
                $colisPaths = [
                    'admin/Colis_prise_en_charges', 'admin/Envoi_colis/envoi_colis', 'admin/Mouvement_colis',
                    'admin/Livraison_colis', 'admin/Reclamations', 'admin/Historiques/historique_colis_enregistrer',
                ];
                $colisOuvert = $groupActive($colisPaths);
            @endphp
            @if ($peutVoirColis)
                <a class="nav-link nav-link-group {{ $colisOuvert ? '' : 'collapsed' }}" href="#groupColis" data-bs-toggle="collapse" role="button" aria-expanded="{{ $colisOuvert ? 'true' : 'false' }}">
                    <i class="fas fa-box"></i> <span class="nav-text flex-grow-1">Colis</span>
                    <i class="fas fa-chevron-down nav-caret"></i>
                </a>
                <div class="collapse {{ $colisOuvert ? 'show' : '' }}" id="groupColis">
                    <div class="nav flex-column nav-subgroup">
                        @if ($authUser->userHasPermission('colis_creation'))
                            <a class="nav-link {{ $isActive('admin/Colis_prise_en_charges') }}" href="{{ url('/admin/Colis_prise_en_charges') }}">
                                <i class="fas fa-list-ul"></i> <span class="nav-text">Liste des colis</span>
                            </a>
                        @endif
                        @if ($authUser->userHasPermission('colis_envoi'))
                            <a class="nav-link {{ $isActive('admin/Envoi_colis/envoi_colis') }}" href="{{ url('/admin/Envoi_colis/envoi_colis') }}">
                                <i class="fas fa-paper-plane"></i> <span class="nav-text">Envoi des colis</span>
                            </a>
                        @endif
                        @if ($authUser->userHasPermission('colis_mouvement'))
                            <a class="nav-link {{ $isActive('admin/Mouvement_colis') }}" href="{{ url('/admin/Mouvement_colis') }}">
                                <i class="fas fa-arrow-right-arrow-left"></i> <span class="nav-text">Mouvement des colis</span>
                            </a>
                        @endif
                        @if ($authUser->userHasPermission('colis_livraison'))
                            <a class="nav-link {{ $isActive('admin/Livraison_colis') }}" href="{{ url('/admin/Livraison_colis') }}">
                                <i class="fas fa-truck"></i> <span class="nav-text">Livraison des colis</span>
                            </a>
                        @endif
                        @if ($authUser->userHasPermission('colis_reclamation'))
                            <a class="nav-link {{ $isActive('admin/Reclamations') }}" href="{{ url('/admin/Reclamations') }}">
                                <i class="fas fa-triangle-exclamation"></i> <span class="nav-text">Réclamations</span>
                            </a>
                        @endif
                        @if ($authUser->userHasPermission('colis_historique'))
                            <a class="nav-link {{ $isActive('admin/Historiques/historique_colis_enregistrer') }}" href="{{ url('/admin/Historiques/historique_colis_enregistrer') }}">
                                <i class="fas fa-clock-rotate-left"></i> <span class="nav-text">Historique des colis</span>
                            </a>
                        @endif
                    </div>
                </div>
            @endif

            {{-- ========== GROUPE : PROGRAMMATION ========== --}}
            @php
                $peutVoirGProgramme = $authUser->userHasPermission('Programme_Creation')
                    || $authUser->userHasPermission('Programme_programmer_car')
                    || $authUser->userHasPermission('Programme_programmation_voyage')
                    || $authUser->userHasPermission('Programme_hors_programme');
                $programmationPaths = [
                    'admin/Programmer_voyages', 'admin/Programmation_cars',
                    'admin/Programmation_voyages/liste_programmer_voyage', 'admin/Transferts_gares/historique', 'admin/Flotte',
                ];
                $programmationOuvert = $groupActive($programmationPaths);
            @endphp
            @if ($peutVoirGProgramme)
                <a class="nav-link nav-link-group {{ $programmationOuvert ? '' : 'collapsed' }}" href="#groupProgrammation" data-bs-toggle="collapse" role="button" aria-expanded="{{ $programmationOuvert ? 'true' : 'false' }}">
                    <i class="fas fa-calendar-days"></i> <span class="nav-text flex-grow-1">Programmation</span>
                    <i class="fas fa-chevron-down nav-caret"></i>
                </a>
                <div class="collapse {{ $programmationOuvert ? 'show' : '' }}" id="groupProgrammation">
                    <div class="nav flex-column nav-subgroup">
                        @if ($authUser->userHasPermission('Programme_Creation'))
                            <a class="nav-link {{ $isActive('admin/Programmer_voyages') }}" href="{{ url('/admin/Programmer_voyages') }}">
                                <i class="fas fa-route"></i> <span class="nav-text">Voyages</span>
                            </a>
                        @endif
                        @if ($authUser->userHasPermission('Programme_programmer_car'))
                            <a class="nav-link {{ $isActive('admin/Programmation_cars') }}" href="{{ url('/admin/Programmation_cars') }}">
                                <i class="fas fa-bus"></i> <span class="nav-text">Cars</span>
                            </a>
                        @endif
                        @if ($authUser->userHasPermission('Programme_programmation_voyage'))
                            <a class="nav-link {{ $isActive('admin/Programmation_voyages/liste_programmer_voyage') }}" href="{{ url('/admin/Programmation_voyages/liste_programmer_voyage') }}">
                                <i class="fas fa-map-location-dot"></i> <span class="nav-text">Trajets programmés</span>
                            </a>
                        @endif
                        @if ($authUser->userHasPermission('Programme_programmation_voyage') && in_array($droit, ['Admin', 'chef_d_escale', 'super_admin', 'PDG', 'secretaire'], true))
                            <a class="nav-link {{ $isActive('admin/Transferts_gares/historique') }}" href="{{ url('/admin/Transferts_gares/historique') }}">
                                <i class="fas fa-right-left"></i> <span class="nav-text">Transferts</span>
                            </a>
                        @endif
                        @if ($authUser->userHasPermission('Programme_hors_programme'))
                            <a class="nav-link" href="#">
                                <i class="fas fa-calendar-xmark"></i> <span class="nav-text">Hors programme</span>
                            </a>
                        @endif
                        @if (in_array($droit, ['Admin', 'super_admin', 'PDG', 'secretaire'], true))
                            <a class="nav-link {{ $isActive('admin/Flotte') }}" href="{{ url('/admin/Flotte') }}">
                                <i class="fas fa-truck-front"></i> <span class="nav-text">État de la flotte</span>
                            </a>
                        @endif
                    </div>
                </div>
            @endif

            {{-- ========== GROUPE : CAISSE ========== --}}
            @php
                $caissePaths = [
                    'admin/Caisse/ma_caisse', 'admin/Caisse/caisses_escale',
                    'admin/Caisse/rapport_proprietaire', 'admin/Caisse/bilant_caisse_billets',
                ];
                $caisseOuvert = $groupActive($caissePaths);
            @endphp
            @if ($authUser->userHasPermission('Caisse_apercue'))
                <a class="nav-link nav-link-group {{ $caisseOuvert ? '' : 'collapsed' }}" href="#groupCaisse" data-bs-toggle="collapse" role="button" aria-expanded="{{ $caisseOuvert ? 'true' : 'false' }}">
                    <i class="fas fa-wallet"></i> <span class="nav-text flex-grow-1">Caisse</span>
                    <i class="fas fa-chevron-down nav-caret"></i>
                </a>
                <div class="collapse {{ $caisseOuvert ? 'show' : '' }}" id="groupCaisse">
                    <div class="nav flex-column nav-subgroup">
                        @if (in_array($droit, ['Utilisateur', 'Admin', 'chef_d_escale'], true))
                            <a class="nav-link {{ $isActive('admin/Caisse/ma_caisse') }}" href="{{ url('/admin/Caisse/ma_caisse') }}">
                                <i class="fas fa-hand-holding-dollar"></i> <span class="nav-text">Ma caisse</span>
                            </a>
                        @endif
                        @if (in_array($droit, ['chef_d_escale', 'Admin', 'PDG', 'secretaire'], true))
                            <a class="nav-link {{ $isActive('admin/Caisse/caisses_escale') }}" href="{{ url('/admin/Caisse/caisses_escale') }}">
                                <i class="fas fa-building-columns"></i> <span class="nav-text">Supervision escale</span>
                            </a>
                        @endif
                        @if (in_array($droit, ['Admin', 'PDG', 'secretaire'], true))
                            <a class="nav-link {{ $isActive('admin/Caisse/rapport_proprietaire') }}" href="{{ url('/admin/Caisse/rapport_proprietaire') }}">
                                <i class="fas fa-chart-column"></i> <span class="nav-text">Rapport compagnie</span>
                            </a>
                        @endif
                        @if ($authUser->userHasPermission('Caisse_billant') && $droit !== 'Utilisateur')
                            <a class="nav-link {{ $isActive('admin/Caisse/bilant_caisse_billets') }}" href="{{ url('/admin/Caisse/bilant_caisse_billets') }}">
                                <i class="fas fa-scale-balanced"></i> <span class="nav-text">Bilan de caisse</span>
                            </a>
                        @endif
                    </div>
                </div>
            @endif

            {{-- ========== GROUPE : FINANCES ========== --}}
            @php
                $financesPaths = ['admin/Depenses', 'admin/Depenses/benefice', 'admin/Locations_cars'];
                $financesOuvert = $groupActive($financesPaths);
            @endphp
            @if (in_array($droit, ['Admin', 'chef_d_escale', 'PDG', 'secretaire'], true))
                <a class="nav-link nav-link-group {{ $financesOuvert ? '' : 'collapsed' }}" href="#groupFinances" data-bs-toggle="collapse" role="button" aria-expanded="{{ $financesOuvert ? 'true' : 'false' }}">
                    <i class="fas fa-money-bill-wave"></i> <span class="nav-text flex-grow-1">Finances</span>
                    <i class="fas fa-chevron-down nav-caret"></i>
                </a>
                <div class="collapse {{ $financesOuvert ? 'show' : '' }}" id="groupFinances">
                    <div class="nav flex-column nav-subgroup">
                        <a class="nav-link {{ $isActive('admin/Depenses') }}" href="{{ url('/admin/Depenses') }}">
                            <i class="fas fa-receipt"></i> <span class="nav-text">Gérer les dépenses</span>
                        </a>
                        @if (in_array($droit, ['Admin', 'PDG', 'secretaire'], true))
                            <a class="nav-link {{ $isActive('admin/Depenses/benefice') }}" href="{{ url('/admin/Depenses/benefice') }}">
                                <i class="fas fa-chart-line"></i> <span class="nav-text">Bénéfice de la compagnie</span>
                            </a>
                        @endif
                        <a class="nav-link {{ $isActive('admin/Locations_cars') }}" href="{{ url('/admin/Locations_cars') }}">
                            <i class="fas fa-bus"></i> <span class="nav-text">Location des cars</span>
                        </a>
                    </div>
                </div>
            @endif

            {{-- ========== GROUPE : BANQUE ========== --}}
            @php
                $banquePaths = ['admin/Banques', 'admin/Depots_banque/enAttente', 'admin/Depots_banque', 'admin/Depots_banque/historique'];
                $banqueOuvert = $groupActive($banquePaths);
            @endphp
            @if (in_array($droit, ['Admin', 'chef_d_escale', 'PDG', 'secretaire'], true))
                <a class="nav-link nav-link-group {{ $banqueOuvert ? '' : 'collapsed' }}" href="#groupBanque" data-bs-toggle="collapse" role="button" aria-expanded="{{ $banqueOuvert ? 'true' : 'false' }}">
                    <i class="fas fa-building-columns"></i> <span class="nav-text flex-grow-1">Banque</span>
                    <i class="fas fa-chevron-down nav-caret"></i>
                </a>
                <div class="collapse {{ $banqueOuvert ? 'show' : '' }}" id="groupBanque">
                    <div class="nav flex-column nav-subgroup">
                        @if (in_array($droit, ['Admin', 'PDG', 'secretaire'], true))
                            <a class="nav-link {{ $isActive('admin/Banques') }}" href="{{ url('/admin/Banques') }}">
                                <i class="fas fa-building-columns"></i> <span class="nav-text">Comptes banque</span>
                            </a>
                            <a class="nav-link {{ $isActive('admin/Depots_banque/enAttente') }}" href="{{ url('/admin/Depots_banque/enAttente') }}">
                                <i class="fas fa-hourglass-half"></i> <span class="nav-text">Demandes en attente</span>
                            </a>
                        @endif
                        @if ($droit !== 'PDG')
                            <a class="nav-link {{ $isActive('admin/Depots_banque') }}" href="{{ url('/admin/Depots_banque') }}">
                                <i class="fas fa-circle-plus"></i> <span class="nav-text">Faire un dépôt</span>
                            </a>
                        @endif
                        <a class="nav-link {{ $isActive('admin/Depots_banque/historique') }}" href="{{ url('/admin/Depots_banque/historique') }}">
                            <i class="fas fa-clock-rotate-left"></i> <span class="nav-text">Historique des dépôts</span>
                        </a>
                    </div>
                </div>
            @endif

            {{-- ========== PERSONNEL (item unique — pas de dropdown pour un seul lien) ========== --}}
            @php
                $peutVoirUtilisateurs = $authUser->userHasPermission('utilisateur_apercu');
                $peutVoirChauffeurs = $authUser->userHasPermission('Configuration_gestion_car/chauffeur');
            @endphp
            @if ($peutVoirUtilisateurs || $peutVoirChauffeurs)
                <a class="nav-link {{ $isActive('admin/Employes') }}" href="{{ url('/admin/Employes') }}">
                    <i class="fas fa-id-card"></i> <span class="nav-text">Employés</span>
                </a>
            @endif
        @endif{{-- fin du if !== 'super_admin' --}}

        {{-- ========== PARAMÈTRES (rarement utilisé — tout en bas, avant Aide) ========== --}}
        @if (in_array($droit, ['Admin', 'super_admin', 'PDG', 'secretaire'], true))
            @php
                $parametresOuvert = $groupActive(['admin/Compagnies', 'admin/Liste_gares', 'admin/Partenariats']);
            @endphp
            @if ($authUser->userHasPermission('Configuration_apercu'))
                <a class="nav-link nav-link-group {{ $parametresOuvert ? '' : 'collapsed' }}" href="#groupParametres" data-bs-toggle="collapse" role="button" aria-expanded="{{ $parametresOuvert ? 'true' : 'false' }}">
                    <i class="fas fa-gear"></i> <span class="nav-text flex-grow-1">Paramètres</span>
                    @if ($nbPartenairesEnAttente > 0)
                        <span class="badge bg-danger rounded-pill me-1">{{ $nbPartenairesEnAttente }}</span>
                    @endif
                    <i class="fas fa-chevron-down nav-caret"></i>
                </a>
                <div class="collapse {{ $parametresOuvert ? 'show' : '' }}" id="groupParametres">
                    <div class="nav flex-column nav-subgroup">
                        @if ($droit === 'super_admin')
                            <a class="nav-link {{ $isActive('admin/Compagnies') }}" href="{{ url('/admin/Compagnies') }}">
                                <i class="fas fa-sliders"></i> <span class="nav-text">Configuration</span>
                            </a>
                        @else
                            <a class="nav-link {{ $isActive('admin/Liste_gares') }}" href="{{ url('/admin/Liste_gares') }}">
                                <i class="fas fa-sliders"></i> <span class="nav-text">Configuration</span>
                            </a>
                        @endif
                        @if ($droit === 'super_admin')
                            <a class="nav-link d-flex align-items-center {{ $isActive('admin/Partenariats') }}" href="{{ url('/admin/Partenariats') }}">
                                <i class="fas fa-handshake"></i> <span class="nav-text flex-grow-1">Demandes de partenariat</span>
                                @if ($nbPartenairesEnAttente > 0)
                                    <span class="badge bg-danger rounded-pill">{{ $nbPartenairesEnAttente }}</span>
                                @endif
                            </a>
                        @endif
                    </div>
                </div>
            @endif
        @endif

        <a class="nav-link {{ $isActive('admin/Documentations') }}" href="{{ url('/admin/Documentations') }}">
            <i class="fas fa-book"></i> <span class="nav-text">Documentation</span>
        </a>

        </nav>
    </div>
</aside>
