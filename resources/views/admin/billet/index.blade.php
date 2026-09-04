@extends('layouts.admin')

@php
    $authUser = auth('staff')->user();
    $montantListe = fn ($liste) => $liste->sum(fn ($b) => (float) preg_replace('/[^\d.]/', '', (string) $b->montant_payer));
@endphp

@section('title', 'Liste des tickets · Sirali Admin')

@section('breadcrumb-title')
    <span class="text-primary"><i class="fas fa-list me-1"></i> G-réservation</span>
@endsection
@section('breadcrumb-active', 'Liste des tickets')

@section('breadcrumb-actions')
    @if (! $authUser->estLectureSeule())
        <a href="{{ route('admin.billet.create') }}" class="btn btn-sm btn-success rounded-pill shadow-sm">
            <i class="fas fa-plus me-1"></i> Nouvelle réservation
        </a>
    @endif
@endsection

@section('content')


    @php $autreJourActif = request()->filled('date'); @endphp
    <ul class="nav nav-pills gap-2 mb-4" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ $autreJourActif ? '' : 'active' }}" data-bs-toggle="tab" data-bs-target="#tabAujourdhui" type="button">
                <i class="fas fa-calendar-days me-1"></i> Aujourd'hui
                <span class="badge bg-light text-dark ms-1">{{ $listeAujourdhui->count() }}</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ $autreJourActif ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#tabAutreJour" type="button">
                <i class="fas fa-clock me-1"></i> Autre jour
                <span class="badge bg-light text-dark ms-1">{{ $listeAutreJour->count() }}</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link" href="{{ route('admin.billet.historique') }}">
                <i class="fas fa-clock-rotate-left me-1"></i> Historique
            </a>
        </li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane fade {{ $autreJourActif ? '' : 'show active' }}" id="tabAujourdhui" role="tabpanel">
            @include('admin.billet.partials.table-billets', ['liste' => $listeAujourdhui, 'montant' => $montantListe($listeAujourdhui), 'tableId' => 'tableAujourdhui'])
        </div>
        <div class="tab-pane fade {{ $autreJourActif ? 'show active' : '' }}" id="tabAutreJour" role="tabpanel">
            <form method="get" class="d-flex align-items-center gap-2 mb-3">
                <label for="dateAutreJour" class="form-label mb-0 fw-semibold small">
                    Réservations du :
                </label>
                <input type="date" id="dateAutreJour" name="date" class="form-control form-control-sm"
                       style="max-width:170px;" value="{{ $dateAutre }}" min="{{ $dateMin }}" max="{{ $dateMax }}"
                       onchange="this.form.submit()">
                <span class="text-muted small">(jusqu'à {{ \Illuminate\Support\Carbon::parse($dateMax)->format('d/m/Y') }} — réservation possible jusqu'à {{ (int) config('billets.jours_reservation_avance', 6) }} jours à l'avance)</span>
            </form>
            @include('admin.billet.partials.table-billets', ['liste' => $listeAutreJour, 'montant' => $montantListe($listeAutreJour), 'tableId' => 'tableAutreJour'])
        </div>
    </div>

@endsection

@section('scripts')
    <script src="{{ asset('mon_js/thermal-print.js') }}?v={{ @filemtime(public_path('mon_js/thermal-print.js')) }}"></script>
    <script>
        tgReady(function () {
            // Deux tables sur la même page (onglets) : id="example" (init globale via
            // table-datatable.js) ne convient qu'à une table par page, donc initialisées ici
            // explicitement. La table de l'onglet "Autre jour", caché au chargement, a ses
            // largeurs de colonnes recalculées via columns.adjust() dès que l'onglet devient
            // visible — sinon DataTables les fige à 0 (limitation connue sur un conteneur
            // display:none).
            const tableAujourdhui = $('#tableAujourdhui').DataTable();
            const tableAutreJour = $('#tableAutreJour').DataTable();
            document.querySelector('[data-bs-target="#tabAutreJour"]')?.addEventListener('shown.bs.tab', function () {
                tableAutreJour.columns.adjust();
            });

            document.querySelectorAll('.form-annuler-billet').forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (form.dataset.confirme === '1') return;
                    event.preventDefault();
                    const estAdmin = form.querySelector('.btn-confirmer-annulation').textContent.includes('Confirmer');
                    Swal.fire({
                        title: estAdmin ? 'Annuler ce billet ?' : "Demander l'annulation ?",
                        text: estAdmin
                            ? 'Cette action est définitive : la place sera restituée et un remboursement enregistré.'
                            : 'Un Admin devra confirmer avant que la place et l\'argent ne soient libérés.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Oui, continuer',
                        cancelButtonText: 'Annuler',
                        customClass: { confirmButton: 'btn btn-danger me-2', cancelButton: 'btn btn-secondary' },
                        buttonsStyling: false,
                    }).then(function (result) {
                        if (result.isConfirmed) {
                            form.dataset.confirme = '1';
                            form.submit();
                        }
                    });
                });
            });
        });
    </script>
@endsection
