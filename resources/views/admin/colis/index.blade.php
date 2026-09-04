@extends('layouts.admin')

@php $authUser = auth('staff')->user(); @endphp

@section('title', 'Liste des colis · Sirali Admin')

@section('breadcrumb-title')
    <span class="text-primary"><i class="fas fa-box-open me-1"></i> G-colis</span>
@endsection
@section('breadcrumb-active', 'Liste des colis')

@section('breadcrumb-actions')
    @if ($authUser->droit !== 'PDG')
        <a href="{{ route('admin.colis.create') }}" class="btn btn-sm btn-success rounded-pill shadow-sm">
            <i class="fas fa-plus me-1"></i> Ajouter
        </a>
    @endif
@endsection

@section('content')


    <!-- Filtrage : meme principe que la liste d'embarquement (admin/Liste_du_jours) -->
    <div class="card border-top border-primary border-1">
        <div class="bg-light border-bottom rounded-top px-3 py-2 d-flex align-items-center mb-0 mt-1" style="gap:8px;">
            <i class="fas fa-filter text-primary" style="font-size:1.3rem;"></i>
            <h6 class="mb-0 fw-bold text-primary" style="letter-spacing:1px;">Filtrage</h6>
        </div>

        <div class="card-body p-4 border-1">
            <div class="row">
                <div class="col-md-6">
                    <label for="id_destination_colis" class="form-label">Destination</label>
                    <select class="form-select" id="id_destination_colis" name="destination">
                        <option value="">Toutes les destinations</option>
                        @foreach ($listesAgences as $agence)
                            <option value="{{ $agence->idAgence }}">{{ $agence->localite }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="selectstatut_colis" class="form-label">Statut</label>
                    <select class="form-select" id="selectstatut_colis" name="statut">
                        <option value="">Tous les statuts</option>
                        <option value="enregistre">Prise en charge</option>
                        <option value="en_cours">En cours</option>
                        <option value="recu">Colis reçu</option>
                        <option value="livre">Colis livré</option>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 mt-3" id="printBtnWrapperColis">
                    <button type="button" class="btn btn-success" id="btnImprimerListeColis">
                        <i class="fas fa-print"></i> Exporter la liste en PDF
                    </button>
                    <small class="text-muted ms-2">Laissez "Toutes" pour exporter tous les colis, ou choisissez un filtre pour n'exporter que la liste filtrée.</small>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-lg border-0 rounded-3">
        <div class="card-header bg-primary text-white fw-bold">
            <i class="fas fa-list-ul me-1"></i> Liste des colis enregistrés
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="example" class="table table-hover align-middle mb-0 table-striped table-bordered rounded-3 mobile-card-table">
                    <thead class="table-primary text-center">
                        <tr>
                            <th>Nom colis</th>
                            <th>Nature</th>
                            <th>Valeur</th>
                            <th>Frais de transaction</th>
                            <th>Destination</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody class="text-center">
                        @foreach ($listeColis as $colis)
                            <tr>
                                <td data-label="Nom colis">{{ $colis->nom_colis }}</td>
                                <td data-label="Nature">{{ $colis->nature }}</td>
                                <td data-label="Valeur"><span class="badge bg-light text-dark">{{ $colis->valeur }}</span></td>
                                <td data-label="Frais de transaction"><span class="badge bg-secondary">{{ $colis->fraix_transaction }}</span></td>
                                <td data-label="Destination">{{ $colis->destination }}</td>
                                <td data-label="Status">{!! \App\Support\ColisStatus::badge($colis->status) !!}</td>
                                <td data-label="Action">
                                    <div class="dropdown">
                                        <a href="#" class="text-dark fs-5" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="fas fa-ellipsis-vertical"></i>
                                        </a>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                            <li>
                                                <a class="dropdown-item details-colis-btn" href="#"
                                                    data-bs-toggle="modal" data-bs-target="#detailsColisModal"
                                                    data-nom="{{ $colis->nom_colis }}"
                                                    data-nature="{{ $colis->nature }}"
                                                    data-valeur="{{ number_format($colis->valeur, 0, ',', ' ') }}"
                                                    data-frais="{{ number_format($colis->fraix_transaction, 0, ',', ' ') }}"
                                                    data-lieu-label="Destination"
                                                    data-lieu="{{ $colis->destination }}"
                                                    data-code="{{ $colis->code_colis }}"
                                                    data-status="{{ $colis->status }}"
                                                    data-status-html="{{ \App\Support\ColisStatus::badge($colis->status) }}"
                                                    data-date="{{ $colis->date_enregistrement }}"
                                                    data-expediteur="{{ $colis->expediteur }}"
                                                    data-numero-exp="{{ $colis->numero_exp }}"
                                                    data-destinataire="{{ $colis->destinataire }}"
                                                    data-numero-dest="{{ $colis->numero_dest }}">
                                                    <i class="fas fa-eye me-2"></i>Détails
                                                </a>
                                            </li>
                                            @if ($authUser->droit !== 'PDG')
                                                <li>
                                                    <a class="dropdown-item edit-colis-btn" href="#"
                                                        data-bs-toggle="modal" data-bs-target="#editColisModal"
                                                        data-id="{{ $colis->id_colis }}"
                                                        data-nom="{{ $colis->nom_colis }}"
                                                        data-nature="{{ $colis->nature }}"
                                                        data-destination-id="{{ $colis->id_agence }}"
                                                        data-valeur="{{ $colis->valeur }}"
                                                        data-frais="{{ $colis->fraix_transaction }}">
                                                        <i class="fas fa-pen me-2"></i>Modifier
                                                    </a>
                                                </li>
                                            @endif
                                            <li>
                                                <a class="dropdown-item" href="{{ url('/admin/Colis_prise_en_charges/imprimer_recu/'.$colis->id_colis) }}" target="_blank">
                                                    <i class="fas fa-print me-2"></i>Imprimer (imprimante câble/USB)
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item thermal-print-colis-btn" href="#" data-id="{{ $colis->id_colis }}">
                                                    <i class="fas fa-print me-2"></i>Imprimer (imprimante WiFi)
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
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

    <!-- Modal Modifier Colis -->
    <div class="modal fade" id="editColisModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title text-dark">Modifier le colis</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post" action="{{ route('admin.colis.update') }}">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="id_colis" id="edit_id_colis">

                        <div class="mb-3">
                            <label class="form-label">Nom du colis</label>
                            <input type="text" class="form-control" name="nom_colis" id="edit_nom_colis" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nature</label>
                            <input type="text" class="form-control" name="nature" id="edit_nature" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Destination</label>
                            <select class="form-control" name="destination" id="edit_destination" required>
                                <option value="" disabled>Choisissez la destination</option>
                                @foreach ($listesAgences as $agence)
                                    @if ($agence->idAgence != $authUser->id_agence)
                                        <option value="{{ $agence->idAgence }}">{{ $agence->localite }} ( {{ $agence->numeroGare }} )</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Valeur</label>
                                <input type="number" min="0" class="form-control" name="valeur" id="edit_valeur" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Frais de transaction</label>
                                <input type="number" min="0" class="form-control" name="fraix_transaction" id="edit_fraix_transaction" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        @if ($authUser->droit !== 'PDG')
                            <button type="submit" class="btn btn-primary" name="update_colis">Enregistrer les modifications</button>
                        @endif
                    </div>
                </form>
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
    </style>

@endsection

@section('scripts')
    <script src="{{ asset('mon_js/thermal-print.js') }}"></script>
    <script>
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

        document.querySelectorAll('.edit-colis-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                document.getElementById('edit_id_colis').value = this.dataset.id || '';
                document.getElementById('edit_nom_colis').value = this.dataset.nom || '';
                document.getElementById('edit_nature').value = this.dataset.nature || '';
                document.getElementById('edit_destination').value = this.dataset.destinationId || '';
                document.getElementById('edit_valeur').value = this.dataset.valeur || '';
                document.getElementById('edit_fraix_transaction').value = this.dataset.frais || '';
            });
        });

        $('#btnImprimerListeColis').on('click', function() {
            const destination = $('#id_destination_colis').val();
            const destinationNom = $('#id_destination_colis option:selected').text();
            const statut = $('#selectstatut_colis').val();
            const statutNom = $('#selectstatut_colis option:selected').text();
            const url = '{{ url('/admin/Colis_prise_en_charges/imprimerListeColis') }}'
                + '?destination=' + encodeURIComponent(destination)
                + '&destination_nom=' + encodeURIComponent(destinationNom)
                + '&statut=' + encodeURIComponent(statut)
                + '&statut_nom=' + encodeURIComponent(statutNom);
            window.open(url, '_blank');
        });
    </script>
@endsection
