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

    $billetsEnAttente = in_array($droit, ['chef_d_escale', 'Utilisateur', 'Admin'], true) || $droit === 'super_admin' || $droit === 'PDG' || $droit === 'secretaire'
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
        'secretaire'    => 'Secrétaire Général',
    ];
    $libellesService = ['billet' => 'Billetterie', 'colis' => 'Colis / Courrier'];

    $roleAffiche = $libellesRole[$droit] ?? $droit;
    if ($droit === 'Utilisateur' && $authUser->profile && isset($libellesService[$authUser->profile])) {
        $roleAffiche = $libellesService[$authUser->profile];
    }

    $gareAffichee = trim(($ville ?? '') . (!empty($numeroGare) ? ' (' . $numeroGare . ')' : ''));
    $identiteAffichee = $roleAffiche . ($gareAffichee !== '' ? ' — ' . $gareAffichee : '');
@endphp
<nav class="navbar navbar-expand-md sticky-top" id="navbar">
    <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center gap-2" href="{{ url('/admin/Homes/home') }}">
            <span class="brand-3d">TRANSGEST</span>
        </a>

        <button class="navbar-toggler" type="button" id="sidebarToggle">
            <i class="fas fa-bars" style="color: var(--navbar-text);"></i>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <!-- Sélecteur de couleur -->
                <li class="nav-item dropdown me-2">
                    <button class="btn btn-sm btn-theme-toggle dropdown-toggle" id="themeSelector" data-bs-toggle="dropdown" title="Changer le thème">
                        <i class="fas fa-palette"></i> Thème
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="themeSelector">
                        <li><a class="dropdown-item" href="#" onclick="setTheme('default'); return false;"><i class="fas fa-circle" style="color:#0f3b5e;"></i> Marine (défaut)</a></li>
                        <li><a class="dropdown-item" href="#" onclick="setTheme('orange'); return false;"><i class="fas fa-circle" style="color:#ea580c;"></i> Orange</a></li>
                        <li><a class="dropdown-item" href="#" onclick="setTheme('green'); return false;"><i class="fas fa-circle" style="color:#10b981;"></i> Vert</a></li>
                        <li><a class="dropdown-item" href="#" onclick="setTheme('red'); return false;"><i class="fas fa-circle" style="color:#ef4444;"></i> Rouge</a></li>
                        <li><a class="dropdown-item" href="#" onclick="setTheme('indigo'); return false;"><i class="fas fa-circle" style="color:#4f46e5;"></i> Indigo</a></li>
                    </ul>
                </li>

                <!-- Mode sombre -->
                <li class="nav-item">
                    <button class="btn btn-outline-light btn-sm me-2" id="darkModeToggle" title="Mode sombre">
                        <i class="fas fa-moon"></i>
                    </button>
                </li>

                @if ($authUser->userHasPermission('Billets_notification'))
                    <li class="nav-item dropdown me-2">
                        <button class="btn btn-sm btn-theme-toggle position-relative" data-bs-toggle="dropdown" title="Notifications">
                            <i class="fas fa-bell"></i>
                            @if ($notifCount > 0)
                                <span class="notify-badge">{{ $notifCount }}</span>
                            @endif
                        </button>
                        <div class="dropdown-menu dropdown-menu-end p-0" style="width: 320px; max-height: 400px; overflow-y: auto;">
                            <div class="p-2">
                                @if ($notifCount > 0)
                                    @foreach ($billetsEnAttente as $billet)
                                        <a class="dropdown-item d-flex align-items-center gap-2 rounded" href="{{ route('admin.entente.index', ['billet' => $billet->idBillets]) }}">
                                            <i class="fas fa-ticket"></i>
                                            <div class="flex-grow-1">
                                                <div class="fw-semibold small">Billet en attente</div>
                                                <div class="text-secondary" style="font-size: .75rem;">
                                                    @if ($droit === 'Admin')<span class="badge bg-secondary me-1">{{ $billet->departId }}</span>@endif
                                                    {{ $billet->destinationId }} - {{ $billet->Heur_departs }}
                                                </div>
                                            </div>
                                        </a>
                                    @endforeach
                                    @foreach ($locationsEnAttente as $location)
                                        <a class="dropdown-item d-flex align-items-center gap-2 rounded" href="{{ url('/admin/Locations_cars') }}">
                                            <i class="fas fa-bus"></i>
                                            <div class="flex-grow-1">
                                                <div class="fw-semibold small">Location de car en attente</div>
                                                <div class="text-secondary" style="font-size: .75rem;">
                                                    <span class="badge bg-secondary me-1">{{ $location->localite ?? '-' }}</span>
                                                    {{ $location->destination }} - {{ number_format($location->frais_location, 0, ',', ' ') }} F
                                                </div>
                                            </div>
                                        </a>
                                    @endforeach
                                    @foreach ($billetsEnRetard as $billetRetard)
                                        <a class="dropdown-item d-flex align-items-center gap-2 rounded" href="{{ url('/admin/Liste_du_jours/embarquement?destination=' . urlencode($billetRetard->destinationId) . '&heure=' . urlencode($billetRetard->Heur_departs)) }}">
                                            <i class="fas fa-clock text-danger"></i>
                                            <div class="flex-grow-1">
                                                <div class="fw-semibold small">Client non embarqué (retard)</div>
                                                <div class="text-secondary" style="font-size: .75rem;">{{ $billetRetard->Client }} — {{ $billetRetard->destinationId }} - {{ $billetRetard->Heur_departs }}</div>
                                            </div>
                                        </a>
                                    @endforeach
                                @else
                                    <p class="text-center text-secondary p-3 mb-0">Aucune notification</p>
                                @endif
                            </div>
                        </div>
                    </li>
                @endif

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        @if ($authUser->photo)
                            <img src="{{ asset('storage/profiles/' . $authUser->photo) }}" alt="{{ $authUser->utilisateurs }}" class="rounded-circle" style="width: 28px; height: 28px; object-fit: cover;">
                        @else
                            <i class="fas fa-user-circle fs-5"></i>
                        @endif
                        <span class="d-flex flex-column lh-1 text-start">
                            <small class="opacity-75" style="font-size: .68rem;">{{ $identiteAffichee }}</small>
                            <span>{{ $authUser->utilisateurs }}</span>
                        </span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li>
                            <a class="dropdown-item" href="{{ url('/admin/Profils') }}">
                                <i class="fas fa-user"></i> Mon Profil
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form action="{{ route('logout') }}" method="POST" style="display: inline; width: 100%;">
                                @csrf
                                <button type="submit" class="dropdown-item">
                                    <i class="fas fa-sign-out-alt"></i> Déconnexion
                                </button>
                            </form>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<style>
    /* Badge de marque : fond dégradé sur les couleurs du thème choisi (change avec le
       sélecteur "Thème"), texte plein blanc pour une lisibilité maximale (pas de texte en
       dégradé transparent, illisible sur certains fonds), + un reflet animé qui balaie le
       badge en continu pour un rendu vivant. */
    .brand-3d {
        position: relative;
        display: inline-flex;
        align-items: center;
        overflow: hidden;
        font-weight: 800;
        font-size: 18px;
        letter-spacing: .5px;
        color: #ffffff;
        padding: 7px 16px;
        border-radius: 9px;
        background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
        border: 1px solid rgba(255, 255, 255, .3);
        box-shadow: 0 4px 14px rgba(0, 0, 0, .35), inset 0 1px 0 rgba(255, 255, 255, .3);
        text-shadow: 0 1px 2px rgba(0, 0, 0, .4);
    }

    .brand-3d::before {
        content: '';
        position: absolute;
        top: 0;
        left: -60%;
        width: 45%;
        height: 100%;
        background: linear-gradient(115deg, transparent, rgba(255, 255, 255, .55), transparent);
        transform: skewX(-20deg);
        animation: brandShine 3.2s ease-in-out infinite;
    }

    @keyframes brandShine {
        0%   { left: -60%; }
        55%  { left: 130%; }
        100% { left: 130%; }
    }

    @media (prefers-reduced-motion: reduce) {
        .brand-3d::before { animation: none; }
    }
</style>
