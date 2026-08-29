@php
    use Illuminate\Support\Facades\DB;

    $authUser = auth('staff')->user();
    $droit = $authUser->droit;
    $ville = $authUser->agence->localite ?? null;
    $numeroGare = $authUser->agence->numeroGare ?? null;
    $idCompagnie = $authUser->id_compagnie;

    $sql = "SELECT idBillets, Heur_departs, destinationId, departId, num_gare, id_compagnie
            FROM billets
            WHERE validation_billets = 'en_attente'
              AND status_reservation = 'en_ligne'";
    $params = [];

    if (in_array($droit, ['chef_d_escale', 'Utilisateur'], true)) {
        $sql .= " AND id_compagnie = ? AND departId = ? AND num_gare = ?";
        $params = [$idCompagnie, $ville, $numeroGare];
    } elseif ($droit === 'Admin') {
        $sql .= " AND id_compagnie = ?";
        $params = [$idCompagnie];
    }

    $billetsEnAttente = in_array($droit, ['chef_d_escale', 'Utilisateur', 'Admin'], true) || $droit === 'super_admin' || $droit === 'PDG'
        ? collect(DB::select($sql, $params))
        : collect();

    $locationsEnAttente = collect();
    if ($droit === 'Admin') {
        $locationsEnAttente = collect(DB::select(
            "SELECT id_location, destination, frais_location, a.localite
             FROM location_car l
             LEFT JOIN agence a ON l.id_agence_depart = a.idAgence
             WHERE l.statut = 'en_attente' AND l.id_compagnie = ?",
            [$idCompagnie]
        ));
    }

    $navAlerts = app(\App\Services\NavAlertsService::class);
    $billetsEnRetard = collect();
    if ($droit === 'chef_d_escale') {
        $billetsEnRetard = collect($navAlerts->getBilletsEnRetard($ville, $numeroGare, $idCompagnie));
    } elseif ($droit === 'Admin') {
        $billetsEnRetard = collect($navAlerts->getBilletsEnRetard(null, null, $idCompagnie));
    }

    $notifCount = $billetsEnAttente->count() + $locationsEnAttente->count() + $billetsEnRetard->count();

    $libellesRole = [
        'Utilisateur'   => 'Utilisateur',
        'chef_d_escale' => "Chef d'escale",
        'Admin'         => 'Admin',
        'super_admin'   => 'Super Admin',
        'PDG'           => 'PDG',
    ];
    $libellesService = ['billet' => 'Billetterie', 'colis' => 'Colis / Courrier'];

    $roleAffiche = $libellesRole[$droit] ?? $droit;
    if ($droit === 'Utilisateur' && $authUser->profile && isset($libellesService[$authUser->profile])) {
        $roleAffiche = $libellesService[$authUser->profile];
    }

    $gareAffichee = trim(($ville ?? '') . (!empty($numeroGare) ? ' (' . $numeroGare . ')' : ''));
    $identiteAffichee = $roleAffiche . ($gareAffichee !== '' ? ' — ' . $gareAffichee : '');
@endphp
<header class="top-header">
  <nav class="navbar navbar-expand">
    <div class="mobile-toggle-icon d-xl-none">
      <i class="bi bi-list"></i>
    </div>
    <div class="top-navbar d-none d-xl-block">

    </div>
    <div class="search-toggle-icon d-xl-none ms-auto">
      <i class="bi bi-search"></i>
    </div>
    <form class="searchbar d-none d-xl-flex ms-auto">
      <div class="position-absolute top-50 translate-middle-y search-icon ms-3"></div>

      <div class="position-absolute top-50 translate-middle-y d-block d-xl-none search-close-icon"><i class="bi bi-x-lg"></i></div>
    </form>
    <div class="top-navbar-right ms-3">
      <ul class="navbar-nav align-items-center">

        @if ($authUser->userHasPermission('Billets_notification'))
          <li class="nav-item dropdown dropdown-large d-none d-sm-block">
            <a class="nav-link" href="#" data-bs-toggle="dropdown">
              <div class="notifications">
                @if ($notifCount > 0)
                  <span class="notify-badge">{{ $notifCount }}</span>
                @endif
                <i class="bx bxs-bell"></i>
              </div>
            </a>

            <div class="dropdown-menu dropdown-menu-end p-0">
              <div class="header-notifications-list p-2">
                @if ($notifCount > 0)
                  @foreach ($billetsEnAttente as $billet)
                    <a class="dropdown-item" href="{{ url('/admin/Liste_ententes/validation/' . $billet->idBillets) }}">
                      <div class="d-flex align-items-center">
                        <div class="notification-box"><i class="bx bxs-coupon"></i></div>
                        <div class="ms-3 flex-grow-1">
                          <h6 class="mb-0 dropdown-msg-user">Billet en attente</h6>
                          <small class="mb-0 dropdown-msg-text text-secondary">
                            @if ($droit === 'Admin')
                              <span class="badge bg-secondary me-1">{{ $billet->departId }}</span>
                            @endif
                            {{ $billet->destinationId }} - {{ $billet->Heur_departs }}
                          </small>
                        </div>
                      </div>
                    </a>
                  @endforeach
                  @foreach ($locationsEnAttente as $location)
                    <a class="dropdown-item" href="{{ url('/admin/Locations_cars') }}">
                      <div class="d-flex align-items-center">
                        <div class="notification-box"><i class="bx bx-car"></i></div>
                        <div class="ms-3 flex-grow-1">
                          <h6 class="mb-0 dropdown-msg-user">Location de car en attente</h6>
                          <small class="mb-0 dropdown-msg-text text-secondary">
                            <span class="badge bg-secondary me-1">{{ $location->localite ?? '-' }}</span>
                            {{ $location->destination }} - {{ number_format($location->frais_location, 0, ',', ' ') }} F
                          </small>
                        </div>
                      </div>
                    </a>
                  @endforeach
                  @foreach ($billetsEnRetard as $billetRetard)
                    <a class="dropdown-item" href="{{ url('/admin/Liste_du_jours/embarquement?destination=' . urlencode($billetRetard->destinationId) . '&heure=' . urlencode($billetRetard->Heur_departs)) }}">
                      <div class="d-flex align-items-center">
                        <div class="notification-box"><i class="bx bx-time-five text-danger"></i></div>
                        <div class="ms-3 flex-grow-1">
                          <h6 class="mb-0 dropdown-msg-user">Client non embarqué (retard)</h6>
                          <small class="mb-0 dropdown-msg-text text-secondary">
                            {{ $billetRetard->Client }} —
                            {{ $billetRetard->destinationId }} - {{ $billetRetard->Heur_departs }}
                          </small>
                        </div>
                      </div>
                    </a>
                  @endforeach
                @else
                  <p class="text-center text-secondary p-2">Aucune notification</p>
                @endif
              </div>
            </div>
          </li>
        @endif

        <li class="nav-item dropdown dropdown-large">
          <a class="nav-link " href="#" data-bs-toggle="dropdown">
            <div class="user-setting d-flex align-items-center gap-1">
              <img src="{{ $authUser->photo ? asset('storage/profiles/' . $authUser->photo) : asset('assets_site/img/reservation.png') }}" class="user-img" alt="">
              <div class="user-name">{{ $authUser->utilisateurs }} <small style="font-size: 0.75rem; color: #f59e0b; display: block; line-height: 1;">{{ $identiteAffichee }}</small></div>
            </div>
          </a>
          <ul class="dropdown-menu dropdown-menu-end">
            <li>
              <a class="dropdown-item" href="{{ url('/admin/Profils') }}">
                <div class="d-flex align-items-center">
                  <div class="setting-icon"><i class="bx bxs-user"></i></div>
                  <div class="setting-text ms-3"><span>Profile</span></div>
                </div>
              </a>
            </li>

            <li>
              <hr class="dropdown-divider">
            </li>
            <li>
              <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="dropdown-item border-0 bg-transparent w-100 text-start">
                  <div class="d-flex align-items-center">
                    <div class="setting-icon"><i class="bx bx-log-out-circle"></i></div>
                    <div class="setting-text ms-3">
                      Déconnexion
                    </div>
                  </div>
                </button>
              </form>
            </li>

          </ul>
        </li>
      </ul>
    </div>
  </nav>
</header>
