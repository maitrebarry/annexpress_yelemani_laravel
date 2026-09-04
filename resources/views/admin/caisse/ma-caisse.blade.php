@extends('layouts.admin')

@php
    $authUser = auth('staff')->user();
    $montantAttendu = $caisse ? (float) $caisse->montant_initial + (float) $caisse->total_billets + (float) $caisse->total_colis : 0;
@endphp

@section('title', 'Ma Caisse · Sirali Admin')

@section('breadcrumb-title')
    <span class="text-primary"><i class="fas fa-wallet me-1"></i> Caisse</span>
@endsection
@section('breadcrumb-active', 'Ma Caisse')

@section('breadcrumb-actions')
    @if ($caisse)
        <button type="button" class="btn btn-sm btn-warning rounded-pill shadow-sm" data-bs-toggle="modal" data-bs-target="#modalFermerCaisse">
            <i class="fas fa-lock me-1"></i> Fermer ma caisse
        </button>
    @elseif ($caisseFermee)
        <button type="button" class="btn btn-sm btn-primary rounded-pill shadow-sm" data-bs-toggle="modal" data-bs-target="#modalVerser">
            <i class="fas fa-paper-plane me-1"></i> Verser
        </button>
    @else
        <button type="button" class="btn btn-sm btn-success rounded-pill shadow-sm" data-bs-toggle="modal" data-bs-target="#modalOuvrirCaisse">
            <i class="fas fa-unlock me-1"></i> Ouvrir ma caisse
        </button>
    @endif
@endsection

@section('content')


    @if ($caisse)
        <div class="row g-3 mb-4">
            <div class="col-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted small">Billets</div>
                        <div class="fs-4 fw-bold text-primary">{{ number_format($caisse->total_billets, 0, ',', ' ') }} F</div>
                        <div class="text-muted small">{{ $caisse->nb_billets }} vente(s)</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted small">Colis</div>
                        <div class="fs-4 fw-bold text-info">{{ number_format($caisse->total_colis, 0, ',', ' ') }} F</div>
                        <div class="text-muted small">{{ $caisse->nb_colis }} colis</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted small">Solde actuel</div>
                        <div class="fs-4 fw-bold text-success">{{ number_format($montantAttendu, 0, ',', ' ') }} F</div>
                        <div class="text-muted small">Fond initial {{ number_format($caisse->montant_initial, 0, ',', ' ') }} F</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted small"><span class="badge bg-success">● Ouverte</span></div>
                        <div class="fw-bold">{{ $caisse->reference }}</div>
                        <div class="text-muted small">depuis {{ \Illuminate\Support\Carbon::parse($caisse->heure_ouverture)->format('H:i') }}</div>
                    </div>
                </div>
            </div>
        </div>
    @elseif ($caisseFermee)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <span class="badge bg-secondary mb-1">🔒 Fermée — non versée</span>
                    <div class="fw-bold">{{ $caisseFermee->reference }}</div>
                    <div class="text-muted small">Montant compté : {{ number_format($caisseFermee->montant_compte, 0, ',', ' ') }} FCFA</div>
                </div>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalVerser">
                    <i class="fas fa-paper-plane me-1"></i> Verser au chef d'escale
                </button>
            </div>
        </div>
    @else
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body text-center py-5">
                <i class="fas fa-wallet fs-1 text-muted"></i>
                <p class="text-muted mt-2 mb-3">Aucune caisse ouverte pour le moment.</p>
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalOuvrirCaisse">
                    <i class="fas fa-unlock me-1"></i> Ouvrir ma caisse
                </button>
            </div>
        </div>
    @endif

    @if ($caisse)
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-primary text-white fw-bold">
                <i class="fas fa-list-ul me-1"></i> Journal du jour
                <span class="badge bg-light text-dark ms-2">{{ $journal->count() }}</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Heure</th>
                                <th>Type</th>
                                <th>Référence</th>
                                <th>Libellé</th>
                                <th class="text-end">Montant</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($journal as $j)
                                @php
                                    $sortie = in_array($j->type_operation, ['fermeture', 'versement'], true);
                                    $icones = ['ouverture' => 'bx-lock-open-alt', 'billet' => 'bx-ticket', 'colis' => 'bx-package', 'fermeture' => 'bx-lock-alt', 'versement' => 'bx-send'];
                                @endphp
                                <tr>
                                    <td>{{ \Illuminate\Support\Carbon::parse($j->date_heure)->format('H:i:s') }}</td>
                                    <td><span class="badge bg-{{ $sortie ? 'danger' : 'success' }}-subtle text-{{ $sortie ? 'danger' : 'success' }}"><i class="bx {{ $icones[$j->type_operation] ?? 'bx-circle' }} me-1"></i>{{ ucfirst($j->type_operation) }}</span></td>
                                    <td>{{ $j->reference_op ?: '—' }}</td>
                                    <td>{{ $j->libelle }}</td>
                                    <td class="text-end fw-semibold {{ $sortie ? 'text-danger' : 'text-success' }}">{{ $sortie ? '-' : '+' }}{{ number_format($j->montant, 0, ',', ' ') }} F</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-muted fst-italic text-center py-3">Aucun mouvement pour le moment.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white fw-bold">
            <i class="fas fa-clock-rotate-left me-1"></i> Historique de mes caisses
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="example" class="table table-striped table-bordered table-hover-effect table-custom-header text-center mobile-card-table" style="width:100%">
                    <thead class="table-light text-center">
                        <tr>
                            <th>Date</th>
                            <th>Référence</th>
                            <th>Billets</th>
                            <th>Colis</th>
                            <th>Attendu</th>
                            <th>Compté</th>
                            <th>Écart</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($historique as $h)
                            <tr>
                                <td data-label="Date">{{ \Illuminate\Support\Carbon::parse($h->date_service)->format('d/m/Y') }}</td>
                                <td data-label="Référence">{{ $h->reference }}</td>
                                <td data-label="Billets">{{ number_format($h->total_billets, 0, ',', ' ') }} F</td>
                                <td data-label="Colis">{{ number_format($h->total_colis, 0, ',', ' ') }} F</td>
                                <td data-label="Attendu">{{ number_format($h->montant_attendu, 0, ',', ' ') }} F</td>
                                <td data-label="Compté">{{ $h->montant_compte !== null ? number_format($h->montant_compte, 0, ',', ' ').' F' : '—' }}</td>
                                <td data-label="Écart" class="{{ $h->ecart > 0 ? 'text-success' : ($h->ecart < 0 ? 'text-danger' : '') }}">
                                    {{ $h->ecart !== null ? ($h->ecart > 0 ? '+' : '').number_format($h->ecart, 0, ',', ' ').' F' : '—' }}
                                </td>
                                <td data-label="Statut">
                                    @if ($h->statut === 'ouverte')
                                        <span class="badge bg-success">Ouverte</span>
                                    @elseif ($h->statut === 'fermee')
                                        <span class="badge bg-secondary">Fermée</span>
                                    @else
                                        <span class="badge bg-primary">Versée</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-muted fst-italic">Aucun historique pour le moment.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection

@section('modals')

    <!-- Modal Ouvrir ma caisse -->
    <div class="modal fade" id="modalOuvrirCaisse" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-success">
                    <h5 class="modal-title text-white"><i class="fas fa-unlock me-1"></i> Ouvrir ma caisse</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post" action="{{ route('admin.caisse.ouvrir-caisse') }}">
                    @csrf
                    <div class="modal-body">
                        @if ($authUser->droit === 'Admin')
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Gare</label>
                                <select class="form-select" name="id_agence" required>
                                    <option value="" disabled selected>Choisissez une gare</option>
                                    @foreach ($listeAgences as $a)
                                        <option value="{{ $a->idAgence }}">{{ $a->localite.' ('.$a->numeroGare.')' }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @else
                            <p class="text-muted">Gare : <strong>{{ $authUser->agence->localite ?? '—' }} ({{ $authUser->agence->numeroGare ?? '—' }})</strong></p>
                        @endif
                        <div class="mb-2">
                            <label class="form-label fw-semibold">Montant initial (fond de caisse)</label>
                            <div class="input-group">
                                <input type="number" class="form-control" name="montant_initial" min="0" step="100" value="0" required>
                                <span class="input-group-text">FCFA</span>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-success fw-semibold"><i class="fas fa-unlock me-2"></i>Ouvrir</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Fermer ma caisse -->
    @if ($caisse)
        <div class="modal fade" id="modalFermerCaisse" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title text-dark"><i class="fas fa-lock me-1"></i> Fermer ma caisse</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="post" action="{{ route('admin.caisse.fermer-caisse') }}">
                        @csrf
                        <div class="modal-body">
                            <p class="mb-1">Montant attendu :</p>
                            <p class="fs-4 fw-bold text-primary mb-3">{{ number_format($montantAttendu, 0, ',', ' ') }} FCFA</p>
                            <label class="form-label fw-semibold">Montant compté (physique)</label>
                            <div class="input-group mb-2">
                                <input type="number" class="form-control" id="montantCompte" name="montant_compte" min="0" step="1" required data-attendu="{{ $montantAttendu }}">
                                <span class="input-group-text">FCFA</span>
                            </div>
                            <div id="ecartPreview" class="alert d-none mb-0"></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-warning fw-semibold text-dark"><i class="fas fa-lock me-2"></i>Confirmer la fermeture</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal Verser -->
    @if ($caisseFermee)
        <div class="modal fade" id="modalVerser" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-primary">
                        <h5 class="modal-title text-white"><i class="fas fa-paper-plane me-1"></i> Verser au chef d'escale</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="post" action="{{ route('admin.caisse.verser') }}">
                        @csrf
                        <div class="modal-body">
                            <p class="mb-1">Montant compté à la fermeture :</p>
                            <p class="fs-4 fw-bold text-success mb-3">{{ number_format($caisseFermee->montant_compte, 0, ',', ' ') }} FCFA</p>

                            @if ($chefs->isEmpty())
                                <div class="alert alert-danger mb-0">Aucun chef d'escale n'est configuré pour votre gare. Le versement est impossible pour le moment.</div>
                            @else
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Chef d'escale</label>
                                    <select class="form-select" name="id_chef_escale" required>
                                        <option value="" disabled selected>Choisissez un chef d'escale</option>
                                        @foreach ($chefs as $c)
                                            <option value="{{ $c->idUser }}">{{ $c->utilisateurs }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label fw-semibold">Montant</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" name="montant" min="1" step="1" value="{{ $caisseFermee->montant_compte }}" required>
                                        <span class="input-group-text">FCFA</span>
                                    </div>
                                </div>
                                <div class="mb-0">
                                    <label class="form-label fw-semibold">Commentaire <small class="text-muted">(optionnel)</small></label>
                                    <textarea class="form-control" name="commentaire" rows="2"></textarea>
                                </div>
                            @endif
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                            @if ($chefs->isNotEmpty())
                                <button type="submit" class="btn btn-primary fw-semibold"><i class="fas fa-paper-plane me-2"></i>Envoyer la demande</button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

@endsection

@section('scripts')
    <script>
        tgReady(function () {
            var montantCompte = document.getElementById('montantCompte');
            if (!montantCompte) return;
            var preview = document.getElementById('ecartPreview');
            var attendu = parseFloat(montantCompte.dataset.attendu || '0');

            montantCompte.addEventListener('input', function () {
                var valeur = parseFloat(montantCompte.value);
                if (isNaN(valeur)) {
                    preview.classList.add('d-none');
                    return;
                }
                var ecart = valeur - attendu;
                preview.classList.remove('d-none', 'alert-success', 'alert-info', 'alert-danger');
                if (ecart === 0) {
                    preview.classList.add('alert-success');
                    preview.textContent = 'Aucun écart — le compte est juste.';
                } else if (ecart > 0) {
                    preview.classList.add('alert-info');
                    preview.textContent = 'Excédent : +' + ecart.toLocaleString('fr-FR') + ' FCFA';
                } else {
                    preview.classList.add('alert-danger');
                    preview.textContent = 'Déficit : ' + ecart.toLocaleString('fr-FR') + ' FCFA';
                }
            });
        });
    </script>
@endsection
