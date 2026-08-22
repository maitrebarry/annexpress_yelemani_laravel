@php
    $authUser = auth('staff')->user()->load('agence');
    $droit = $authUser->droit;
    $ville = $authUser->agence?->localite;
    $numeroGare = $authUser->agence?->numeroGare;

    $navAlerts = app(\App\Services\NavAlertsService::class);

    $carsCompletsSidebar = collect();
    if ($authUser->userHasPermission('Billets_embarquement')) {
        $isAdminSidebar = in_array($droit, ['Admin', 'PDG'], true);
        $carsCompletsSidebar = collect($navAlerts->getCarsComplets(
            $isAdminSidebar ? null : $ville,
            $isAdminSidebar ? null : $numeroGare,
            $authUser->id_compagnie
        ));
    }

    $demandesReportSidebar = collect();
    if ($authUser->userHasPermission('Billets_annulation')) {
        if (in_array($droit, ['Admin', 'super_admin', 'PDG'], true)) {
            $demandesReportSidebar = collect($navAlerts->getDemandesReportEnAttente($authUser->id_compagnie));
        } elseif ($droit === 'chef_d_escale') {
            $demandesReportSidebar = collect($navAlerts->getDemandesReportEnAttenteChef($authUser->id_compagnie, $ville, $numeroGare));
        }
    }

    $nbPartenairesEnAttente = $droit === 'super_admin' ? $navAlerts->countPartenairesEnAttente() : 0;
@endphp
<aside class="sidebar-wrapper" data-simplebar="true">
  <div class="sidebar-header">
    <div>
      <a href="{{ url('/admin/Homes/home') }}" style="text-decoration:none; display:flex; align-items:center; justify-content:center;">
        <img src="{{ asset('images/logos/transgest_logo.png') }}" alt="TransGest" style="height:60px; width:auto; object-fit:contain;">
      </a>
    </div>
    <div class="toggle-icon ms-auto"><i class="bi bi-chevron-double-left"></i>
    </div>
  </div>
  <!--navigation-->
  <ul class="metismenu" id="menu">

    <li>
      <a href="{{ url('/admin/Homes/home') }}" class="has-">
        <div class="">Accueil</div>
      </a>
    </li>

    @if ($droit !== 'super_admin')
      @if ($authUser->userHasPermission('Billets_creation'))
        <li class="menu-label">Gestion des reservation</li>
        <li>
          <a href="javascript:;" class="has-arrow">
            <div class="parent-icon"><i class="bx bx-category"></i></div>
            <div class="menu-title">G-réservation</div>
          </a>
      @endif
        <ul>
          @if ($authUser->userHasPermission('Billets_creation') && !$authUser->estLectureSeule())
            <li> <a href="{{ url('/admin/Add_billets') }}"><i class="bi bi-arrow-right-short"></i>Achat de ticket</a></li>
          @endif

          @if ($authUser->userHasPermission('Billets_apercue'))
            <li> <a href="{{ url('/admin/Liste_du_jours') }}"><i class="bi bi-arrow-right-short"></i>Liste des ticket</a></li>
            <li> <a href="{{ url('/admin/Liste_du_jours/historique') }}"><i class="bi bi-arrow-right-short"></i>Historique des billets</a></li>
          @endif
          @if ($authUser->userHasPermission('Billets_embarquement'))
            <li> <a href="{{ url('/admin/Liste_du_jours/embarquement') }}"><i class="bi bi-arrow-right-short"></i>Embarquement
                @if ($carsCompletsSidebar->isNotEmpty())
                  <span class="badge bg-danger rounded-pill float-end" title="Bus complet(s), embarquement requis">{{ $carsCompletsSidebar->count() }}</span>
                @endif
              </a>
            </li>
          @endif
          @if ($authUser->userHasPermission('Billets_validation'))
            <li> <a href="{{ url('/admin/Liste_ententes') }}"><i class="bi bi-arrow-right-short"></i>Ticket en entente</a></li>
          @endif
          @if (in_array($droit, ['Admin', 'super_admin', 'PDG'], true))
            <li> <a href="{{ url('/admin/Liste_du_jours/demandesAnnulation') }}"><i class="bi bi-arrow-right-short"></i>Demandes d'annulation</a></li>
          @endif
          @if (in_array($droit, ['Admin', 'super_admin', 'PDG', 'chef_d_escale'], true) && $authUser->userHasPermission('Billets_annulation'))
            <li> <a href="{{ url('/admin/Liste_du_jours/demandesReport') }}"><i class="bi bi-arrow-right-short"></i>Demandes de report
                @if ($demandesReportSidebar->isNotEmpty())
                  <span class="badge bg-warning text-dark rounded-pill float-end" title="Demande(s) de report en attente">{{ $demandesReportSidebar->count() }}</span>
                @endif
              </a>
            </li>
          @endif
        </ul>
        </li>

        @if ($authUser->userHasPermission('Billets_rapport'))
          <li>
            <a href="javascript:;" class="has-arrow">
              <div class="parent-icon"><i class="bx bx-bar-chart-alt-2"></i></div>
              <div class="menu-title">Rapport billets</div>
            </a>
            <ul>
              <li> <a href="{{ url('/admin/Rapport_billets/rapport_billets') }}"><i class="bi bi-arrow-right-short"></i>Rapport mensuel</a></li>
              <li> <a href="{{ url('/admin/Rapport_billets/rapport_annuel') }}"><i class="bi bi-arrow-right-short"></i>Rapport annuel</a></li>
            </ul>
          </li>
        @endif

          @if ($authUser->userHasPermission('colis_creation'))
            <li class="menu-label">Gestion des colis</li>
            <li>
              <a href="javascript:;" class="has-arrow">
                <div class="font-22"> <i class="fadeIn animated bx bx-layer-plus"></i></div>
                <div class="menu-title">G-colis</div>
              </a>
          @endif
            <ul>
              @if ($authUser->userHasPermission('colis_creation'))
                <li> <a href="{{ url('/admin/Colis_prise_en_charges') }}"><i class="bi bi-arrow-right-short"></i>Liste des colis</a></li>
              @endif
              @if ($authUser->userHasPermission('colis_envoi'))
                <li> <a href="{{ url('/admin/Envoi_colis/envoi_colis') }}"><i class="bi bi-arrow-right-short"></i>Envoi des colis</a></li>
              @endif
              @if ($authUser->userHasPermission('colis_mouvement'))
                <li> <a href="{{ url('/admin/Mouvement_colis') }}"><i class="bi bi-arrow-right-short"></i>Mouvement des colis</a></li>
              @endif
              @if ($authUser->userHasPermission('colis_livraison'))
                <li> <a href="{{ url('/admin/Livraison_colis') }}"><i class="bi bi-arrow-right-short"></i>Livraison des colis</a></li>
              @endif
            </ul>
            </li>
            @if ($authUser->userHasPermission('colis_reclamation'))
              <li>
                <a href="{{ url('/admin/Reclamations') }}">
                  <div class="font-22"> <i class="fadeIn animated bx bx-error"></i></div>
                  <div class="menu-title">Reclamation</div>
                </a>
              </li>
            @endif
            @if ($authUser->userHasPermission('colis_historique'))
              <li>
                <a href="{{ url('/admin/Historiques/historique_colis_enregistrer') }}">
                  <div class="font-22"><i class="bx bx-history"></i></div>
                  <div class="menu-title">Historique des colis</div>
                </a>
              </li>
            @endif

            @if ($authUser->userHasPermission('Caisse_apercue'))
              <li class="menu-label">Gestion de caisse</li>
              <li>
                <a href="javascript:;" class="has-arrow">
                  <div class="parent-icon"><i class="bx bx-wallet"></i></div>
                  <div class="menu-title">Caisse</div>
                </a>
            @endif
              <ul>
                @if ($authUser->userHasPermission('Caisse_apercue'))
                  <!-- Module de caisse individuelle (remplace l'ancienne "Caisse Générale") -->
                  @if (in_array($droit, ['Utilisateur', 'Admin', 'chef_d_escale'], true))
                    <li> <a href="{{ url('/admin/Caisse/ma_caisse') }}"><i class="bi bi-arrow-right-short"></i>Ma Caisse</a></li>
                  @endif

                  @if (in_array($droit, ['chef_d_escale', 'Admin', 'PDG'], true))
                    <li> <a href="{{ url('/admin/Caisse/caisses_escale') }}"><i class="bi bi-arrow-right-short"></i>Supervision Escale</a></li>
                  @endif

                  @if (in_array($droit, ['Admin', 'PDG'], true))
                    <li> <a href="{{ url('/admin/Caisse/rapport_proprietaire') }}"><i class="bi bi-arrow-right-short"></i>Rapport Compagnie</a></li>
                  @endif
                @endif
                @if ($authUser->userHasPermission('Caisse_billant') && $droit !== 'Utilisateur')
                  <li> <a href="{{ url('/admin/Caisse/bilant_caisse_billets') }}"><i class="bi bi-arrow-right-short"></i>Bilan de caisse</a></li>
                @endif
              </ul>
              </li>

              @if (in_array($droit, ['Admin', 'chef_d_escale', 'PDG'], true))
                <li class="menu-label">Finances</li>
                <li>
                  <a href="javascript:;" class="has-arrow">
                    <div class="parent-icon"><i class="bx bx-money"></i></div>
                    <div class="menu-title">Dépenses</div>
                  </a>
                  <ul>
                    <li> <a href="{{ url('/admin/Depenses') }}"><i class="bi bi-arrow-right-short"></i>Gérer les dépenses</a></li>
                    @if (in_array($droit, ['Admin', 'PDG'], true))
                      <li> <a href="{{ url('/admin/Depenses/benefice') }}"><i class="bi bi-arrow-right-short"></i>Bénéfice de la compagnie</a></li>
                    @endif
                  </ul>
                </li>
                <li>
                  <a href="javascript:;" class="has-arrow">
                    <div class="parent-icon"><i class="bx bx-car"></i></div>
                    <div class="menu-title">Location des cars</div>
                  </a>
                  <ul>
                    <li> <a href="{{ url('/admin/Locations_cars') }}"><i class="bi bi-arrow-right-short"></i>Gérer les locations</a></li>
                  </ul>
                </li>
              @endif

              @php
                $peutVoirUtilisateurs = $authUser->userHasPermission('utilisateur_apercu');
                $peutVoirChauffeurs = $authUser->userHasPermission('Configuration_gestion_car/chauffeur');
              @endphp
              @if ($peutVoirUtilisateurs || $peutVoirChauffeurs)
                <li class="menu-label">Personnel</li>
                <li>
                  <a href="{{ url('/admin/Employes') }}">
                    <div class="parent-icon"><i class="bx bx-id-card"></i></div>
                    <div class="menu-title">Employés</div>
                  </a>
                </li>
              @endif

              @if (in_array($droit, ['Admin', 'chef_d_escale', 'PDG'], true))
                <li class="menu-label">Banque</li>
                <li>
                  <a href="javascript:;" class="has-arrow">
                    <div class="parent-icon"><i class="bx bx-buildings"></i></div>
                    <div class="menu-title">Dépôt en banque</div>
                  </a>
                  <ul>
                    @if (in_array($droit, ['Admin', 'PDG'], true))
                      <li> <a href="{{ url('/admin/Banques') }}"><i class="bi bi-arrow-right-short"></i>Comptes banque</a></li>
                      <li> <a href="{{ url('/admin/Depots_banque/enAttente') }}"><i class="bi bi-arrow-right-short"></i>Demandes en attente</a></li>
                    @endif
                    @if ($droit !== 'PDG')
                      <li> <a href="{{ url('/admin/Depots_banque') }}"><i class="bi bi-arrow-right-short"></i>Faire un dépôt</a></li>
                    @endif
                    <li> <a href="{{ url('/admin/Depots_banque/historique') }}"><i class="bi bi-arrow-right-short"></i>Historique des dépôts</a></li>
                  </ul>
                </li>
              @endif

              @php
                $peutVoirGProgramme = $authUser->userHasPermission('Programme_Creation')
                    || $authUser->userHasPermission('Programme_programmer_car')
                    || $authUser->userHasPermission('Programme_programmation_voyage')
                    || $authUser->userHasPermission('Programme_hors_programme');
              @endphp
              @if ($peutVoirGProgramme)
                <li class="menu-label">Programmation</li>
                <li>
                  <a href="javascript:;" class="has-arrow">
                    <div class="parent-icon"><i class="bx bx-calendar"></i></div>
                    <div class="menu-title">G-programme</div>
                  </a>
                <ul>
                  @if ($authUser->userHasPermission('Programme_Creation'))
                    <li> <a href="{{ url('/admin/Programmer_voyages') }}"><i class="bi bi-arrow-right-short"></i>Voyages</a></li>
                  @endif
                  @if ($authUser->userHasPermission('Programme_programmer_car'))
                    <li> <a href="{{ url('/admin/Programmation_cars') }}"><i class="bi bi-arrow-right-short"></i>Cars</a></li>
                  @endif
                  @if ($authUser->userHasPermission('Programme_programmation_voyage'))
                    <li> <a href="{{ url('/admin/Programmation_voyages/liste_programmer_voyage') }}"><i class="bi bi-arrow-right-short"></i>Trajets programmés</a></li>
                  @endif
                  @if ($authUser->userHasPermission('Programme_programmation_voyage') && in_array($droit, ['Admin', 'chef_d_escale', 'super_admin', 'PDG'], true))
                    <li> <a href="{{ url('/admin/Transferts_gares/historique') }}"><i class="bi bi-arrow-right-short"></i>Transferts</a></li>
                  @endif
                  @if ($authUser->userHasPermission('Programme_hors_programme'))
                    <li> <a href="#"><i class="bi bi-arrow-right-short"></i>Hors programme</a></li>
                  @endif
                  @if (in_array($droit, ['Admin', 'super_admin', 'PDG'], true))
                    <li> <a href="{{ url('/admin/Flotte') }}"><i class="bi bi-arrow-right-short"></i>État de la flotte</a></li>
                  @endif
                </ul>
                </li>
              @endif
              <li class="menu-label"></li>

    @endif{{-- fin du if !== 'super_admin' --}}

    @if (in_array($droit, ['Admin', 'super_admin', 'PDG'], true))
      @if ($authUser->userHasPermission('Configuration_apercu'))
        <li class="menu-label">Paramètre</li>
        <li>
          @if ($droit === 'super_admin')
            <a href="{{ url('/admin/Compagnies') }}">
              <div class="parent-icon"><i class="fadeIn animated bx bx-shape-polygon"></i></div>
              <div class="menu-title">Configuration</div>
            </a>
          @else
            <a href="{{ url('/admin/Liste_gares') }}">
              <div class="parent-icon"><i class="fadeIn animated bx bx-shape-polygon"></i></div>
              <div class="menu-title">Configuration</div>
            </a>
          @endif
        </li>
        @if ($droit === 'super_admin')
          <li>
              <a href="{{ url('/admin/Partenariats') }}">
                <div class="parent-icon"><i class="fadeIn animated bx bx-handshake"></i></div>
                <div class="menu-title">
                  Demandes de partenariat
                  @if ($nbPartenairesEnAttente > 0)
                    <span class="badge bg-danger rounded-pill ms-1">{{ $nbPartenairesEnAttente }}</span>
                  @endif
                </div>
              </a>
          </li>
        @endif
      @endif
    @endif

    <!-- Accessible a TOUT compte connecte : documentation, pas un ecran metier. -->
    <li class="menu-label">Aide</li>
    <li>
      <a href="{{ url('/admin/Documentations') }}">
        <div class="parent-icon"><i class="bx bx-book-open"></i></div>
        <div class="menu-title">Documentation</div>
      </a>
    </li>

  </ul>
  <!--end navigation-->
</aside>
