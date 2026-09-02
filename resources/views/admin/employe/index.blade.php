@extends('layouts.admin')

@php
    $authUser = auth('staff')->user();
@endphp

@section('title', 'Employés · TransGest Admin')

@section('breadcrumb-title')
    <span class="text-primary"><i class="fas fa-id-card me-1"></i> Personnel</span>
@endsection
@section('breadcrumb-active', 'Employés')

@section('breadcrumb-actions')
    <div class="d-flex gap-2 flex-wrap">
        <button type="button" id="btnSelectionMultiple" class="btn btn-sm btn-outline-secondary rounded-pill shadow-sm">
            <i class="fas fa-list-check me-1"></i> Sélection multiple
        </button>
        <a href="{{ route('admin.employe.liste-imprimable') }}" target="_blank" class="btn btn-sm btn-outline-danger rounded-pill shadow-sm">
            <i class="fas fa-file-pdf me-1"></i> Imprimer la liste
        </a>
        @if ($peutVoirUtilisateurs)
            <a href="{{ route('admin.configuration.index') }}" class="btn btn-sm btn-outline-primary rounded-pill shadow-sm">
                <i class="fas fa-user me-1"></i> Gérer les utilisateurs
            </a>
        @endif
        @if ($peutVoirChauffeurs)
            <a href="{{ route('admin.car.index') }}" class="btn btn-sm btn-outline-primary rounded-pill shadow-sm">
                <i class="fas fa-car me-1"></i> Gérer les chauffeurs
            </a>
        @endif
    </div>
@endsection

@section('content')


    <div class="card border-0 shadow rounded-4 overflow-hidden">
        <div class="card-header border-0 py-4 px-4 d-flex align-items-center gap-2"
             style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: #fff;">
            <i class="fas fa-id-card fs-4"></i>
            <span class="fw-semibold fs-5">Liste des employés</span>
        </div>
        <div class="card-body p-4">
            <form id="formImpressionGroupee" method="post" action="{{ route('admin.employe.print-selection') }}" target="_blank">
                @csrf
                <div id="batchToolbar" class="d-none align-items-center flex-wrap gap-3 mb-3 p-3 bg-light rounded-3 border">
                    <span id="selectionCount" class="fw-semibold">0 sélectionné(s)</span>
                    <button type="submit" id="btnImprimerSelection" class="btn btn-primary btn-sm d-flex align-items-center gap-2" disabled>
                        <i class="fas fa-print"></i> Imprimer la sélection (max 4 par feuille A4)
                    </button>
                </div>

                <div class="table-responsive">
                    @php
                        $theadStyle = 'background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: #fff;';
                    @endphp
                    <table id="example" class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="selection-col d-none border-0" style="{{ $theadStyle }}"><input type="checkbox" id="checkAll" class="form-check-input"></th>
                                <th class="border-0" style="{{ $theadStyle }}">Photo</th>
                                <th class="border-0" style="{{ $theadStyle }}">Nom &amp; prénom</th>
                                <th class="border-0" style="{{ $theadStyle }}">Fonction</th>
                                <th class="border-0" style="{{ $theadStyle }}">Contact</th>
                                <th class="border-0" style="{{ $theadStyle }}">Téléphone</th>
                                <th class="border-0" style="{{ $theadStyle }}">Affectation</th>
                                <th class="border-0" style="{{ $theadStyle }}">Statut</th>
                                <th class="border-0" style="{{ $theadStyle }}">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($employes as $employe)
                                <tr>
                                    <td class="selection-col d-none">
                                        <input type="checkbox" class="form-check-input row-check" name="selection[]" value="{{ $employe['type'] }}:{{ $employe['id'] }}">
                                    </td>
                                    <td>
                                        @if (! empty($employe['photo']))
                                            <img src="{{ asset('storage/profiles/'.$employe['photo']) }}" alt="Photo" class="rounded-circle" width="40" height="40" style="object-fit: cover;">
                                        @else
                                            <div class="rounded-circle bg-secondary text-white d-inline-flex align-items-center justify-content-center" style="width:40px;height:40px;">
                                                <i class="fas fa-user fs-5"></i>
                                            </div>
                                        @endif
                                    </td>
                                    <td>{{ $employe['nom'] }}</td>
                                    <td>
                                        <span class="badge {{ $employe['type'] === 'Chauffeur' ? 'bg-warning text-dark' : 'bg-primary' }}">
                                            {{ $employe['fonction'] }}
                                        </span>
                                    </td>
                                    <td>{{ $employe['contact'] }}</td>
                                    <td>{{ $employe['telephone'] }}</td>
                                    <td>{{ $employe['affectation'] }}</td>
                                    <td>
                                        <span class="badge {{ $employe['statut'] === 'Actif' ? 'bg-success' : 'bg-secondary' }}">
                                            {{ $employe['statut'] }}
                                        </span>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-2"
                                            data-bs-toggle="modal" data-bs-target="#modalImprimerBadge"
                                            data-type="{{ $employe['type'] }}" data-id="{{ $employe['id'] }}" data-nom="{{ $employe['nom'] }}">
                                            <i class="fas fa-print"></i> Imprimer
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </form>
        </div>
    </div>

@endsection

@section('modals')
    <div class="modal fade" id="modalImprimerBadge" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow rounded-4 overflow-hidden">
                <div class="modal-header border-0 py-3 px-4" style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));">
                    <h5 class="modal-title text-white d-flex align-items-center gap-2">
                        <i class="fas fa-print"></i> Imprimer le badge <span id="modalImprimerNom"></span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted mb-3">Choisissez le format de la carte à imprimer.</p>
                    <a href="#" id="modalImprimerLienFormat1" target="_blank"
                       class="btn btn-outline-primary w-100 d-flex align-items-center justify-content-between px-3 py-3">
                        <span class="d-flex align-items-center gap-2">
                            <i class="fas fa-id-card fs-4"></i>
                            Format 1 — Corporate Horizontal
                        </span>
                        <i class="fas fa-chevron-right fs-4"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        (function () {
            const btnToggle = document.getElementById('btnSelectionMultiple');
            const toolbar = document.getElementById('batchToolbar');
            const table = document.getElementById('example');
            const checkAll = document.getElementById('checkAll');
            const btnSubmit = document.getElementById('btnImprimerSelection');
            const countLabel = document.getElementById('selectionCount');
            if (!btnToggle || !toolbar || !table) return;

            function updateCount() {
                const checked = table.querySelectorAll('.row-check:checked').length;
                countLabel.textContent = checked + ' sélectionné(s)';
                btnSubmit.disabled = checked === 0;
            }

            btnToggle.addEventListener('click', function () {
                const enabling = toolbar.classList.contains('d-none');
                toolbar.classList.toggle('d-none', !enabling);
                toolbar.classList.toggle('d-flex', enabling);
                document.querySelectorAll('.selection-col').forEach(el => el.classList.toggle('d-none', !enabling));
                btnToggle.classList.toggle('active', enabling);
                btnToggle.classList.toggle('btn-outline-secondary', !enabling);
                btnToggle.classList.toggle('btn-secondary', enabling);

                if (!enabling) {
                    table.querySelectorAll('.row-check').forEach(cb => cb.checked = false);
                    if (checkAll) checkAll.checked = false;
                }
                updateCount();
            });

            table.addEventListener('change', function (e) {
                if (e.target.classList.contains('row-check')) updateCount();
            });

            checkAll?.addEventListener('change', function () {
                table.querySelectorAll('.row-check').forEach(cb => cb.checked = checkAll.checked);
                updateCount();
            });

            const modalImprimer = document.getElementById('modalImprimerBadge');
            const lienFormat1 = document.getElementById('modalImprimerLienFormat1');
            modalImprimer?.addEventListener('show.bs.modal', function (event) {
                const btn = event.relatedTarget;
                if (!btn) return;
                const type = btn.getAttribute('data-type');
                const id = btn.getAttribute('data-id');
                const nom = btn.getAttribute('data-nom') || '';
                document.getElementById('modalImprimerNom').textContent = nom;
                lienFormat1.href = '{{ url('/admin/Employes/printCard') }}/' + encodeURIComponent(type) + '/' + encodeURIComponent(id);
            });
            lienFormat1?.addEventListener('click', function () {
                bootstrap.Modal.getInstance(modalImprimer)?.hide();
            });
        })();
    </script>
@endsection
