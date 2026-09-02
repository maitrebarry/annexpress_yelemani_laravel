@extends('layouts.admin')

@php
    $authUser = auth('staff')->user();
    $totalAttente = $listeColis->count();
    $totalRecu = $listeColisRecue->count();
    $totalLivre = $listeColisLivre->count();
@endphp

@section('title', 'Mouvement des colis · TransGest Admin')

@section('breadcrumb-title')
    <span class="text-primary"><i class="fas fa-box-open me-1"></i> G-colis</span>
@endsection
@section('breadcrumb-active', 'Mouvement des colis')

@section('breadcrumb-actions')
    <a href="{{ route('admin.colis.index') }}" class="btn btn-sm btn-primary rounded-pill shadow-sm me-2">
        <i class="fas fa-list-ul me-1"></i> Liste des colis
    </a>
@endsection

@section('content')

    <!-- Stat cards -->
    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 stat-card">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="ms-3">
                        <div class="text-muted small">Colis en attente</div>
                        <div class="fw-bold fs-4 lh-1">{{ $totalAttente }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 stat-card">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-success bg-opacity-10 text-success">
                        <i class="fas fa-inbox"></i>
                    </div>
                    <div class="ms-3">
                        <div class="text-muted small">Colis reçus</div>
                        <div class="fw-bold fs-4 lh-1">{{ $totalRecu }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 stat-card">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-info bg-opacity-10 text-info">
                        <i class="fas fa-shield-halved"></i>
                    </div>
                    <div class="ms-3">
                        <div class="text-muted small">Colis livrés</div>
                        <div class="fw-bold fs-4 lh-1">{{ $totalLivre }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xxl-12">
            <div class="col-xl-12 mx-auto">
                <div class="card border-0 shadow-sm rounded">
                    <div class="card-body">

                        <!-- Tabs -->
                        <ul class="nav nav-pills mb-4 justify-content-start flex-wrap" role="tablist">
                            <li class="nav-item me-2 mb-2" role="presentation">
                                <a class="nav-link active d-flex align-items-center px-3 py-2" data-bs-toggle="pill" href="#info-pills-home" role="tab">
                                    <i class='fas fa-clock me-2'></i> Colis en attente
                                    <span class="badge rounded-pill bg-warning text-dark ms-2">{{ $totalAttente }}</span>
                                </a>
                            </li>
                            <li class="nav-item me-2 mb-2" role="presentation">
                                <a class="nav-link d-flex align-items-center px-3 py-2" data-bs-toggle="pill" href="#info-pills-profile" role="tab">
                                    <i class='fas fa-inbox me-2'></i> Colis reçu
                                    <span class="badge rounded-pill bg-success ms-2">{{ $totalRecu }}</span>
                                </a>
                            </li>
                            <li class="nav-item me-2 mb-2" role="presentation">
                                <a class="nav-link d-flex align-items-center px-3 py-2" data-bs-toggle="pill" href="#info-pills-contact" role="tab">
                                    <i class='fas fa-shield-halved me-2'></i> Colis livré
                                    <span class="badge rounded-pill bg-info ms-2">{{ $totalLivre }}</span>
                                </a>
                            </li>
                        </ul>

                        <!-- Tab Contents -->
                        <div class="tab-content" id="pills-tabContent">

                            <!-- Colis en attente -->
                            <div class="tab-pane fade show active" id="info-pills-home" role="tabpanel">
                                <form action="{{ route('admin.colis.mouvement.receive') }}" method="post">
                                    @csrf
                                    <div class="d-flex justify-content-end mb-3">
                                        <div class="w-30 position-relative">
                                            <div class="position-absolute top-50 translate-middle-y ps-3">
                                                <i class="fas fa-magnifying-glass text-secondary"></i>
                                            </div>
                                            <input class="form-control ps-5 rounded-pill search-input" type="text" data-target="table-attente" placeholder="Rechercher un colis...">
                                        </div>
                                    </div>
                                    <div class="table-responsive mb-3">
                                        <table id="table-attente" class="table table-striped table-hover align-middle mb-0 mobile-card-table">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>@if ($authUser->droit !== 'PDG')<input type="checkbox" id="selectAll">@endif</th>
                                                    <th>Nom colis</th>
                                                    <th>Nature</th>
                                                    <th>Valeur</th>
                                                    <th>Frais de transaction</th>
                                                    <th>Destination</th>
                                                    <th>Code colis</th>
                                                    <th>Status</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($listeColis as $c)
                                                    <tr>
                                                        <td data-label="Sélection">@if ($authUser->droit !== 'PDG')<input type="checkbox" name="selected_colis[]" value="{{ $c->id_colis }}" class="form-check-input checkbox-car">@endif</td>
                                                        <td data-label="Nom colis" class="fw-medium">{{ $c->nom_colis }}</td>
                                                        <td data-label="Nature">{{ $c->nature }}</td>
                                                        <td data-label="Valeur">{{ number_format($c->valeur, 0, ',', ' ') }} FCFA</td>
                                                        <td data-label="Frais de transaction">{{ number_format($c->fraix_transaction, 0, ',', ' ') }} FCFA</td>
                                                        <td data-label="Destination">{{ $c->destination }}</td>
                                                        <td data-label="Code colis">{{ $c->code_colis }}</td>
                                                        <td data-label="Status">{!! \App\Support\ColisStatus::badge($c->status) !!}</td>
                                                        <td data-label="Action">
                                                            <div class="dropdown">
                                                                <a href="#" class="text-dark fs-5" data-bs-toggle="dropdown">
                                                                    <i class="fas fa-ellipsis-vertical"></i>
                                                                </a>
                                                                <ul class="dropdown-menu dropdown-menu-end">
                                                                    <li>
                                                                        <a class="dropdown-item details-colis-btn" href="#"
                                                                            data-bs-toggle="modal" data-bs-target="#detailsColisModal"
                                                                            data-nom="{{ $c->nom_colis }}"
                                                                            data-nature="{{ $c->nature }}"
                                                                            data-valeur="{{ number_format($c->valeur, 0, ',', ' ') }}"
                                                                            data-frais="{{ number_format($c->fraix_transaction, 0, ',', ' ') }}"
                                                                            data-lieu-label="Destination"
                                                                            data-lieu="{{ $c->destination }}"
                                                                            data-code="{{ $c->code_colis }}"
                                                                            data-status="{{ $c->status }}"
                                                                            data-status-html="{{ \App\Support\ColisStatus::badge($c->status) }}"
                                                                            data-date="{{ $c->date_enregistrement }}"
                                                                            data-expediteur="{{ $c->expediteur }}"
                                                                            data-numero-exp="{{ $c->numero_exp }}"
                                                                            data-destinataire="{{ $c->destinataire }}"
                                                                            data-numero-dest="{{ $c->numero_dest }}">
                                                                            <i class="fas fa-eye me-1"></i> Détails
                                                                        </a>
                                                                    </li>
                                                                    @if ($authUser->droit !== 'PDG')
                                                                        <li><a class="dropdown-item" href="#"><i class="fas fa-ban me-1"></i> Désactiver</a></li>
                                                                    @endif
                                                                </ul>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                        @if ($listeColis->isEmpty())
                                            <div class="text-center text-muted py-5">
                                                <i class="fas fa-box-open fs-1 d-block mb-2"></i>
                                                Aucun colis en attente pour le moment.
                                            </div>
                                        @endif
                                    </div>
                                    @if ($listeColis->isNotEmpty() && $authUser->droit !== 'PDG')
                                        <div class="d-flex justify-content-end mt-3">
                                            <button class="btn btn-success rounded-pill px-4" type="submit" name="reception">
                                                <i class="fas fa-check me-1"></i> Réception
                                            </button>
                                        </div>
                                    @endif
                                </form>
                            </div>

                            <!-- Colis reçu -->
                            <div class="tab-pane fade" id="info-pills-profile" role="tabpanel">
                                <div class="d-flex justify-content-end mb-3">
                                    <div class="w-30 position-relative">
                                        <div class="position-absolute top-50 translate-middle-y ps-3">
                                            <i class="fas fa-magnifying-glass text-secondary"></i>
                                        </div>
                                        <input class="form-control ps-5 rounded-pill search-input" type="text" data-target="table-recu" placeholder="Rechercher un colis...">
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table id="table-recu" class="table table-striped table-hover align-middle text-center mb-0 mobile-card-table">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Nom colis</th>
                                                <th>Nature</th>
                                                <th>Valeur</th>
                                                <th>Frais de transaction</th>
                                                <th>Provenance</th>
                                                <th>Code colis</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($listeColisRecue as $colis)
                                                @php
                                                    $msgRecu = 'Bonjour '.$colis->destinataire.', votre colis (code '
                                                        .$colis->code_colis.') est arrivé à '.$colis->destination
                                                        .', gare n° '.$colis->numero_gare_retrait.', et vous y attend.'
                                                        .' Présentez ce message ou le code du colis pour le retrait.';
                                                    $lienWhatsappRecu = \App\Support\WhatsAppLink::url($colis->whatsapp_dest ?: $colis->numero_dest, $msgRecu);
                                                @endphp
                                                <tr>
                                                    <td data-label="Nom colis">{{ $colis->nom_colis }}</td>
                                                    <td data-label="Nature">{{ $colis->nature }}</td>
                                                    <td data-label="Valeur">{{ number_format($colis->valeur, 0, ',', ' ') }} FCFA</td>
                                                    <td data-label="Frais de transaction">{{ number_format($colis->fraix_transaction, 0, ',', ' ') }} FCFA</td>
                                                    <td data-label="Provenance">{{ $colis->provient_de }}</td>
                                                    <td data-label="Code colis">{{ $colis->code_colis }}</td>
                                                    <td data-label="Status">{!! \App\Support\ColisStatus::badge($colis->status) !!}</td>
                                                    <td data-label="Action">
                                                        <div class="dropdown">
                                                            <a href="#" class="text-dark fs-5" data-bs-toggle="dropdown">
                                                                <i class="fas fa-ellipsis-vertical"></i>
                                                            </a>
                                                            <ul class="dropdown-menu dropdown-menu-end">
                                                                <li>
                                                                    <a class="dropdown-item details-colis-btn" href="#"
                                                                        data-bs-toggle="modal" data-bs-target="#detailsColisModal"
                                                                        data-nom="{{ $colis->nom_colis }}"
                                                                        data-nature="{{ $colis->nature }}"
                                                                        data-valeur="{{ number_format($colis->valeur, 0, ',', ' ') }}"
                                                                        data-frais="{{ number_format($colis->fraix_transaction, 0, ',', ' ') }}"
                                                                        data-lieu-label="Provenance"
                                                                        data-lieu="{{ $colis->provient_de }}"
                                                                        data-code="{{ $colis->code_colis }}"
                                                                        data-status="{{ $colis->status }}"
                                                                        data-status-html="{{ \App\Support\ColisStatus::badge($colis->status) }}"
                                                                        data-date="{{ $colis->date_enregistrement }}"
                                                                        data-expediteur="{{ $colis->expediteur }}"
                                                                        data-numero-exp="{{ $colis->numero_exp }}"
                                                                        data-destinataire="{{ $colis->destinataire }}"
                                                                        data-numero-dest="{{ $colis->numero_dest }}">
                                                                        <i class="fas fa-eye me-1"></i> Détails
                                                                    </a>
                                                                </li>
                                                                @if ($authUser->droit !== 'PDG')
                                                                    <li>
                                                                        <a class="dropdown-item"
                                                                            href="{{ url('/admin/Livraison_colis?code='.urlencode($colis->code_colis)) }}">
                                                                            <i class="fas fa-truck me-2"></i>Livrer
                                                                        </a>
                                                                    </li>
                                                                @endif
                                                                @if ($lienWhatsappRecu)
                                                                    <li>
                                                                        <a class="dropdown-item" href="{{ $lienWhatsappRecu }}" target="_blank" rel="noopener">
                                                                            <i class="fab fa-whatsapp me-2 text-success"></i>Notifier par WhatsApp
                                                                        </a>
                                                                    </li>
                                                                @endif
                                                            </ul>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                    @if ($listeColisRecue->isEmpty())
                                        <div class="text-center text-muted py-5">
                                            <i class="fas fa-inbox fs-1 d-block mb-2"></i>
                                            Aucun colis reçu pour le moment.
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Colis livré -->
                            <div class="tab-pane fade" id="info-pills-contact" role="tabpanel">
                                <div class="d-flex justify-content-end mb-3">
                                    <div class="w-30 position-relative">
                                        <div class="position-absolute top-50 translate-middle-y ps-3">
                                            <i class="fas fa-magnifying-glass text-secondary"></i>
                                        </div>
                                        <input class="form-control ps-5 rounded-pill search-input" type="text" data-target="table-livre" placeholder="Rechercher un colis...">
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table id="table-livre" class="table table-striped table-hover align-middle text-center mb-0 mobile-card-table">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Nom colis</th>
                                                <th>Nature</th>
                                                <th>Valeur</th>
                                                <th>Frais de transaction</th>
                                                <th>Destination</th>
                                                <th>Date de livraison</th>
                                                <th>Code colis</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($listeColisLivre as $colisLivre)
                                                @php
                                                    $msgLivre = 'Bonjour '.$colisLivre->expediteur.', votre colis (code '
                                                        .$colisLivre->code_colis.') a bien été remis à son destinataire le '
                                                        .$colisLivre->date_livraison.'. Merci de votre confiance.';
                                                    $lienWhatsappLivre = \App\Support\WhatsAppLink::url($colisLivre->whatsapp_exp ?: $colisLivre->numero_exp, $msgLivre);
                                                @endphp
                                                <tr>
                                                    <td data-label="Nom colis">{{ $colisLivre->nom_colis }}</td>
                                                    <td data-label="Nature">{{ $colisLivre->nature }}</td>
                                                    <td data-label="Valeur">{{ number_format($colisLivre->valeur, 0, ',', ' ') }} FCFA</td>
                                                    <td data-label="Frais de transaction">{{ number_format($colisLivre->fraix_transaction, 0, ',', ' ') }} FCFA</td>
                                                    <td data-label="Destination">{{ $colisLivre->destination }}</td>
                                                    <td data-label="Date de livraison">{{ $colisLivre->date_livraison }}</td>
                                                    <td data-label="Code colis">{{ $colisLivre->code_colis }}</td>
                                                    <td data-label="Status">{!! \App\Support\ColisStatus::badge($colisLivre->status) !!}</td>
                                                    <td data-label="Action">
                                                        @if ($lienWhatsappLivre)
                                                            <a href="{{ $lienWhatsappLivre }}" target="_blank" rel="noopener"
                                                                class="btn btn-sm btn-outline-success" title="Confirmer la remise par WhatsApp (à l'expéditeur)">
                                                                <i class="fab fa-whatsapp"></i>
                                                            </a>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                    @if ($listeColisLivre->isEmpty())
                                        <div class="text-center text-muted py-5">
                                            <i class="fas fa-shield-halved fs-1 d-block mb-2"></i>
                                            Aucun colis livré pour le moment.
                                        </div>
                                    @endif
                                </div>
                            </div>

                        </div> <!-- tab-content -->

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Détails Colis -->
    <div class="modal fade" id="detailsColisModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow colis-modal">
                <div class="modal-header colis-modal-header text-white">
                    <div>
                        <h5 class="modal-title mb-0"><i class="fas fa-box-open me-2"></i><span id="dc_nom"></span></h5>
                        <small class="opacity-75">Code colis : <span id="dc_code"></span></small>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3 mb-4 text-center">
                        <div class="col-4">
                            <div class="colis-stat-icon bg-success bg-opacity-10 text-success"><i class="fas fa-money-bill-wave"></i></div>
                            <div class="small text-muted">Valeur</div>
                            <div class="fw-bold"><span id="dc_valeur"></span> FCFA</div>
                        </div>
                        <div class="col-4">
                            <div class="colis-stat-icon bg-warning bg-opacity-10 text-warning"><i class="fas fa-receipt"></i></div>
                            <div class="small text-muted">Frais</div>
                            <div class="fw-bold"><span id="dc_frais"></span> FCFA</div>
                        </div>
                        <div class="col-4">
                            <div class="colis-stat-icon bg-info bg-opacity-10 text-info"><i class="fas fa-shield-halved"></i></div>
                            <div class="small text-muted">Statut</div>
                            <div id="dc_status_badge"></div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="colis-panel">
                                <div class="colis-panel-title"><i class="fas fa-upload me-1"></i> Expéditeur</div>
                                <div class="colis-info-row"><i class="fas fa-user"></i><span id="dc_expediteur"></span></div>
                                <div class="colis-info-row"><i class="fas fa-phone"></i><span id="dc_numero_exp"></span></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="colis-panel">
                                <div class="colis-panel-title"><i class="fas fa-download me-1"></i> Destinataire</div>
                                <div class="colis-info-row"><i class="fas fa-user"></i><span id="dc_destinataire"></span></div>
                                <div class="colis-info-row"><i class="fas fa-phone"></i><span id="dc_numero_dest"></span></div>
                            </div>
                        </div>
                    </div>

                    <div class="colis-panel mt-3">
                        <div class="colis-panel-title"><i class="fas fa-box me-1"></i> Colis</div>
                        <div class="colis-info-row"><i class="fas fa-tag"></i> Nature : <span class="ms-1" id="dc_nature"></span></div>
                        <div class="colis-info-row"><i class="fas fa-location-dot"></i> <span id="dc_lieu_label">Destination</span> : <span class="ms-1" id="dc_lieu"></span></div>
                        <div class="colis-info-row"><i class="fas fa-calendar"></i> Enregistré le <span class="ms-1" id="dc_date"></span></div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    </div>

    <style>
        .colis-modal-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        }

        .colis-stat-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            margin: 0 auto 6px;
        }

        .colis-panel {
            background: #f8f9fb;
            border-radius: 10px;
            padding: 14px 16px;
            height: 100%;
        }

        .colis-panel-title {
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 10px;
            font-size: .95rem;
        }

        .colis-info-row {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 4px 0;
            font-size: .92rem;
        }

        .colis-info-row i {
            color: #6c757d;
            font-size: 1rem;
            width: 18px;
        }

        .stat-card .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            flex-shrink: 0;
        }

        .nav-pills .nav-link {
            border-radius: 50px;
            background-color: #f4f6f9;
            color: #495057;
        }

        .nav-pills .nav-link.active {
            box-shadow: 0 2px 6px rgba(0, 0, 0, .12);
        }
    </style>

@endsection

@section('scripts')
    <script>
        document.getElementById('selectAll').addEventListener('change', function() {
            const isChecked = this.checked;
            document.querySelectorAll('.checkbox-car').forEach(function(checkbox) {
                checkbox.checked = isChecked;
            });
        });

        document.querySelectorAll('.details-colis-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                document.getElementById('dc_expediteur').textContent = this.dataset.expediteur || '-';
                document.getElementById('dc_numero_exp').textContent = this.dataset.numeroExp || '-';
                document.getElementById('dc_destinataire').textContent = this.dataset.destinataire || '-';
                document.getElementById('dc_numero_dest').textContent = this.dataset.numeroDest || '-';
                document.getElementById('dc_nom').textContent = this.dataset.nom || '-';
                document.getElementById('dc_nature').textContent = this.dataset.nature || '-';
                document.getElementById('dc_valeur').textContent = this.dataset.valeur || '0';
                document.getElementById('dc_frais').textContent = this.dataset.frais || '0';
                document.getElementById('dc_lieu_label').textContent = this.dataset.lieuLabel || 'Destination';
                document.getElementById('dc_lieu').textContent = this.dataset.lieu || '-';
                document.getElementById('dc_code').textContent = this.dataset.code || '-';
                document.getElementById('dc_date').textContent = this.dataset.date || '-';
                document.getElementById('dc_status_badge').innerHTML = this.dataset.statusHtml || '-';
            });
        });

        $(document).ready(function() {
            const tables = {};
            ['table-attente', 'table-recu', 'table-livre'].forEach(function(id) {
                if ($('#' + id).find('tbody tr').length > 0) {
                    tables[id] = $('#' + id).DataTable({
                        dom: 'lrtip',
                        order: [],
                        language: {
                            emptyTable: "Aucune donnée disponible",
                            zeroRecords: "Aucun résultat trouvé",
                            lengthMenu: "Afficher _MENU_ lignes",
                            info: "Affichage de _START_ à _END_ sur _TOTAL_ lignes",
                            infoEmpty: "0 ligne",
                            search: "Rechercher :",
                            paginate: {
                                previous: "Précédent",
                                next: "Suivant"
                            }
                        }
                    });
                }
            });

            // Recherche personnalisée reliée aux DataTables
            $('.search-input').on('keyup', function() {
                const target = $(this).data('target');
                if (tables[target]) {
                    tables[target].search(this.value).draw();
                }
            });

            // Recalcule la largeur des colonnes quand un onglet caché devient visible
            const tableByTab = {
                '#info-pills-home': 'table-attente',
                '#info-pills-profile': 'table-recu',
                '#info-pills-contact': 'table-livre'
            };
            $('a[data-bs-toggle="pill"]').on('shown.bs.tab', function(e) {
                const id = tableByTab[$(e.target).attr('href')];
                if (id && tables[id]) {
                    tables[id].columns.adjust();
                }
            });
        });
    </script>
@endsection
