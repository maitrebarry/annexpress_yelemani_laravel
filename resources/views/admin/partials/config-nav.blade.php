@php
    $authUser = auth('staff')->user();
    $active = $active ?? null;
@endphp
<div class="col-12 col-xxl-3">
  <div class="card config-card">
    <div class="card-header">
      <div class="card-title">
        <i class="fas fa-gear fs-4 me-2"></i> Paramètres Généraux
      </div>
    </div>
    <div class="card-body p-3">
      <ul class="nav nav-tabs flex-column vertical-tabs-custom" role="tablist">
        @if ($authUser->droit === 'super_admin')
          <li class="nav-item">
            <a class="nav-link {{ $active === 'compagnie' ? 'active' : '' }} text-break" role="tab"
              aria-current="page" href="{{ url('/admin/Compagnies') }}"
              aria-selected="true">
              <i class="fas fa-building-columns me-2 align-middle d-inline-block"></i>Compagnie
            </a>
          </li>
        @endif
        @if ($authUser->userHasPermission('utilisateur_apercu'))
          <li class="nav-item">
            <a class="nav-link {{ $active === 'utilisateur' ? 'active' : '' }} text-break" role="tab"
              aria-current="page" href="{{ url('/admin/Configurations') }}"
              aria-selected="true">
              <i class="fas fa-user me-2 align-middle d-inline-block"></i>Utilisateur
            </a>
          </li>
        @endif
        @if ($authUser->userHasPermission('Configuration_gestion_gare'))
          <li class="nav-item">
            <a class="nav-link {{ $active === 'gares' ? 'active' : '' }} text-break" role="tab"
              aria-current="page" href="{{ url('/admin/Liste_gares') }}"
              aria-selected="true">
              <i class="fas fa-house me-2 align-middle d-inline-block"></i>Gares
            </a>
          </li>
        @endif

        @if ($authUser->userHasPermission('Configuration_gestion_escale'))
          <li class="nav-item">
            <a class="nav-link {{ $active === 'escale' ? 'active' : '' }} text-break mb-0" role="tab"
              aria-current="page" href="{{ url('/admin/Add_liste_escales') }}"
              aria-selected="true">
              <i class="fas fa-location-dot me-2 align-middle d-inline-block"></i>Escale
            </a>
          </li>
        @endif

        @if ($authUser->userHasPermission('Configuration_gestion_horaire'))
          <li class="nav-item mt-2">
            <a class="nav-link {{ $active === 'horaire' ? 'active' : '' }} text-break mb-0" role="tab"
              aria-current="page" href="{{ url('/admin/Add_liste_horaire') }}"
              aria-selected="true">
              <i class="fas fa-clock me-2 align-middle d-inline-block"></i>Horaire
            </a>
          </li>
        @endif
        @if ($authUser->userHasPermission('Configuration_gestion_car/chauffeur'))
          <li class="nav-item mt-2">
            <a class="nav-link {{ $active === 'cars' ? 'active' : '' }} text-break" role="tab"
              aria-current="page" href="{{ url('/admin/Cars_chauffeurs') }}"
              aria-selected="true">
              <i class="fas fa-car me-2 align-middle d-inline-block"></i>Cars & Camions & Chauffeurs
            </a>
          </li>
        @endif

        @if ($authUser->droit === 'super_admin')
        <li class="nav-item mt-2">
          <a class="nav-link {{ $active === 'permission' ? 'active' : '' }} text-break mb-0" role="tab"
            aria-current="page" href="{{ url('/admin/Add_liste_horaire/add_permission') }}"
            aria-selected="true">
            <i class="fas fa-shield-halved me-2 align-middle d-inline-block"></i>Permission
          </a>
        </li>
        @endif

        @if ($authUser->userHasPermission('Configuration_place/limite'))
          <li class="nav-item mt-2">
            <a class="nav-link {{ $active === 'place_limite' ? 'active' : '' }} text-break" role="tab"
              aria-current="page" href="{{ url('/admin/Compagnies/place_limite') }}"
              aria-selected="true">
              <i class="fas fa-chair me-2 align-middle d-inline-block"></i>Place limite
            </a>
          </li>
        @endif
        @if (in_array($authUser->droit, ['Admin', 'PDG', 'secretaire'], true))
          <li class="nav-item mt-2">
            <a class="nav-link {{ $active === 'actualite' ? 'active' : '' }} text-break" role="tab"
              aria-current="page" href="{{ url('/admin/Actualites') }}"
              aria-selected="true">
              <i class="fas fa-newspaper me-2 align-middle d-inline-block"></i>Actualités
            </a>
          </li>
          <li class="nav-item mt-2">
            <a class="nav-link {{ $active === 'messages' ? 'active' : '' }} text-break" role="tab"
              aria-current="page" href="{{ url('/admin/Messages_contact') }}"
              aria-selected="true">
              <i class="fas fa-envelope-open-text me-2 align-middle d-inline-block"></i>Messages reçus
            </a>
          </li>
        @endif
      </ul>
    </div>
  </div>
</div>
