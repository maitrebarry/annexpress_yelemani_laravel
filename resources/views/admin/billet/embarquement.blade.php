@extends('layouts.admin')

@php $authUser = auth('staff')->user(); @endphp

@section('title', 'Embarquement · Sirali Admin')

@section('breadcrumb-title')
    <span class="text-primary"><i class="fas fa-list me-1"></i> G-réservation</span>
@endsection
@section('breadcrumb-active', 'Embarquement')

@section('breadcrumb-actions')
    <a href="{{ route('admin.billet.demandes-report') }}" class="btn btn-sm btn-outline-primary rounded-pill shadow-sm">
        <i class="fas fa-right-left me-1"></i> Demandes de report
    </a>
@endsection

@section('content')


    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="get" action="{{ route('admin.billet.embarquement') }}" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label mb-1">Date</label>
                    <input type="date" class="form-control" name="date" value="{{ $date }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label mb-1">Destination</label>
                    <select class="form-select" name="destination">
                        <option value="">Toutes</option>
                        @foreach ($destinations as $d)
                            <option value="{{ $d }}" {{ $destinationSelectionnee === $d ? 'selected' : '' }}>{{ $d }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label mb-1">Heure</label>
                    <input type="time" class="form-control" name="heure" value="{{ $heureSelectionnee }}">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-outline-primary w-100"><i class="fas fa-filter me-1"></i> Filtrer</button>
                </div>
            </form>
        </div>
    </div>

    @if ($carsComplets->isNotEmpty())
        <div class="row g-3 mb-4">
            @foreach ($carsComplets as $cc)
                <div class="col-12 col-md-6 col-xl-4">
                    <div class="card border-0 shadow-sm border-start border-4 border-warning h-100">
                        <div class="card-body py-2">
                            <div class="small text-warning-emphasis fw-semibold"><i class="fas fa-circle-exclamation me-1"></i> Car complet</div>
                            <div class="fw-bold">{{ $cc->numero_car }} — {{ $cc->depart }} → {{ $cc->destination }} ({{ \Illuminate\Support\Carbon::parse($cc->heure)->format('H:i') }})</div>
                            <div class="text-muted small">{{ $cc->nbr_place_reserve }}/{{ $cc->nbr_place }} places</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    @if ($carsDuJour->isNotEmpty())
        <div class="row g-3 mb-4">
            @foreach ($carsDuJour as $c)
                <div class="col-12 col-md-6 col-xl-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <div class="fw-bold">{{ $c->numero_car }} <span class="text-muted small">({{ $c->matriculle }})</span></div>
                                    <div class="text-muted small">{{ $c->depart }} → {{ $c->destination }} · {{ \Illuminate\Support\Carbon::parse($c->heure)->format('H:i') }}</div>
                                </div>
                                <span class="badge bg-light text-dark border">{{ $c->nbr_place_reserve }}/{{ $c->nbr_place }}</span>
                            </div>
                            @if ($c->decolle_le)
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                    <i class="fas fa-circle-check"></i> Décollé à {{ \Illuminate\Support\Carbon::parse($c->decolle_le)->format('H:i') }}{{ $c->decolle_par_nom ? ' par '.$c->decolle_par_nom : '' }}
                                </span>
                            @else
                                <button type="button" class="btn btn-sm btn-primary w-100 btn-decoller" data-id="{{ $c->id_programmation }}" {{ $c->nb_restants > 0 ? 'disabled' : '' }}>
                                    <i class="fas fa-paper-plane me-1"></i> Faire décoller {{ $c->nb_restants > 0 ? "($c->nb_restants restant(s))" : '' }}
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <div class="card border-0 shadow rounded-4 overflow-hidden">
        <div class="card-header border-0 py-3 px-4 d-flex align-items-center justify-content-between gap-2"
             style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: #fff;">
            <span class="fw-semibold"><i class="fas fa-list-ul me-1"></i> Passagers du {{ \Illuminate\Support\Carbon::parse($date)->format('d/m/Y') }}</span>
            <button type="button" class="btn btn-sm btn-success" id="btnEmbarquerSelection" disabled>
                <i class="fas fa-check-double me-1"></i> Embarquer la sélection
            </button>
        </div>
        <div class="table-responsive p-2">
            <table id="example" class="table table-hover align-middle mb-0" style="width:100%">
                <thead>
                    <tr class="text-muted small text-uppercase">
                        <th class="border-0"><input type="checkbox" class="form-check-input" id="checkAll"></th>
                        <th class="border-0">N° billet</th>
                        <th class="border-0">Client</th>
                        <th class="border-0">Destination</th>
                        <th class="border-0">N° place</th>
                        <th class="border-0">Heure</th>
                        <th class="border-0">Statut</th>
                        <th class="border-0">Action</th>
                    </tr>
                </thead>
                <tbody id="tableEmbarquement">
                    @foreach ($liste as $b)
                        @include('admin.billet.partials.ligne-embarquement', ['b' => $b])
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

@endsection

@section('modals')
    <div class="modal fade" id="modalDemanderReport" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="post" action="{{ route('admin.billet.demander-report') }}">
                    @csrf
                    <input type="hidden" name="idBillets" id="reportIdBillets">
                    <div class="modal-header bg-primary">
                        <h5 class="modal-title text-white"><i class="fas fa-right-left me-1"></i> Demander un report</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted small">Client <strong id="reportClientNom"></strong>, non présenté à l'embarquement.</p>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nouvelle date de voyage</label>
                            <input type="date" class="form-control" name="nouvelle_date" min="{{ now()->toDateString() }}" max="{{ now()->addDay()->toDateString() }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nouvelle heure de départ</label>
                            <input type="time" class="form-control" name="nouvelle_heure" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Envoyer la demande</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        function appelAjax(url, body) {
            return fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify(body),
            }).then(r => r.json());
        }

        document.getElementById('modalDemanderReport').addEventListener('show.bs.modal', function (event) {
            const btn = event.relatedTarget;
            document.getElementById('reportIdBillets').value = btn.dataset.id;
            document.getElementById('reportClientNom').textContent = btn.dataset.client;
        });

        document.body.addEventListener('click', function (event) {
            const btnEmbarquer = event.target.closest('.btn-marquer-embarque');
            if (btnEmbarquer) {
                appelAjax('{{ route('admin.billet.marquer-embarque') }}', { idBillets: btnEmbarquer.dataset.id })
                    .then(res => { if (res.ok) location.reload(); else Swal.fire('Erreur', res.message, 'error'); });
                return;
            }

            const btnAnnuler = event.target.closest('.btn-annuler-embarquement');
            if (btnAnnuler) {
                appelAjax('{{ route('admin.billet.annuler-embarquement') }}', { idBillets: btnAnnuler.dataset.id })
                    .then(res => { if (res.ok) location.reload(); else Swal.fire('Erreur', res.message, 'error'); });
                return;
            }

            const btnDecoller = event.target.closest('.btn-decoller');
            if (btnDecoller) {
                Swal.fire({
                    title: 'Faire décoller ce bus ?',
                    text: 'Cette action est définitive.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Oui, décoller',
                    cancelButtonText: 'Annuler',
                    customClass: { confirmButton: 'btn btn-primary me-2', cancelButton: 'btn btn-secondary' },
                    buttonsStyling: false,
                }).then(result => {
                    if (! result.isConfirmed) return;
                    appelAjax('{{ route('admin.billet.decoller-car') }}', { idProgrammation: btnDecoller.dataset.id })
                        .then(res => { if (res.ok) location.reload(); else Swal.fire('Erreur', res.message, 'error'); });
                });
            }
        });

        const checkAll = document.getElementById('checkAll');
        const btnLot = document.getElementById('btnEmbarquerSelection');

        function majBoutonLot() {
            const coches = document.querySelectorAll('.chk-billet:checked');
            btnLot.disabled = coches.length === 0;
        }

        checkAll?.addEventListener('change', function () {
            document.querySelectorAll('.chk-billet:not(:disabled)').forEach(chk => { chk.checked = checkAll.checked; });
            majBoutonLot();
        });
        document.getElementById('tableEmbarquement').addEventListener('change', function (event) {
            if (event.target.classList.contains('chk-billet')) majBoutonLot();
        });

        btnLot.addEventListener('click', function () {
            const ids = Array.from(document.querySelectorAll('.chk-billet:checked')).map(chk => chk.value);
            if (! ids.length) return;
            appelAjax('{{ route('admin.billet.marquer-embarque-lot') }}', { idsBillets: ids })
                .then(res => {
                    if (res.ok) {
                        Swal.fire('Embarquement en masse', `${res.succes} embarqué(s), ${res.deja} déjà fait(s), ${res.echecs} échec(s).`, 'success')
                            .then(() => location.reload());
                    } else {
                        Swal.fire('Erreur', res.message || 'Erreur lors de l\'embarquement en masse.', 'error');
                    }
                });
        });
    </script>
@endsection
