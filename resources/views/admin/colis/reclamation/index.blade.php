@extends('layouts.admin')

@php $authUser = auth('staff')->user(); @endphp

@section('title', 'Réclamation de colis · Sirali Admin')

@section('breadcrumb-title')
    <span class="text-primary"><i class="fas fa-triangle-exclamation me-1"></i> G-colis</span>
@endsection
@section('breadcrumb-active', 'Réclamation de colis')

@section('breadcrumb-actions')
    @if ($authUser->droit !== 'PDG')
        <button type="button" class="btn btn-sm btn-danger rounded-pill shadow-sm" data-bs-toggle="modal" data-bs-target="#modalNouvelleReclamation">
            <i class="fas fa-plus me-1"></i> Nouvelle réclamation
        </button>
    @endif
@endsection

@section('content')


    <div class="card shadow-lg border-0 rounded-3">
        <div class="card-header bg-primary text-white fw-bold">
            <i class="fas fa-list-ul me-1"></i> Réclamations
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="example" class="table table-hover align-middle mb-0 table-striped table-bordered rounded-3 mobile-card-table">
                    <thead class="table-primary text-center">
                        <tr>
                            <th>Date</th>
                            <th>Code colis</th>
                            <th>Nom colis</th>
                            <th>Motif</th>
                            <th>Montant</th>
                            <th>Statut</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody class="text-center">
                        @foreach ($reclamations as $rec)
                            <tr>
                                <td data-label="Date">{{ $rec->date_reclamer ? \Illuminate\Support\Carbon::parse($rec->date_reclamer)->format('d/m/Y') : '-' }}</td>
                                <td data-label="Code colis" class="fw-bold text-primary">{{ $rec->code_colis }}</td>
                                <td data-label="Nom colis">{{ $rec->nom_colis }}</td>
                                <td data-label="Motif"><span title="{{ $rec->motif_reclamation }}">{{ \Illuminate\Support\Str::limit($rec->motif_reclamation, 30) }}</span></td>
                                <td data-label="Montant"><span class="fw-bold">{{ number_format($rec->montant_remboursement, 0, ',', ' ') }} F</span></td>
                                <td data-label="Statut">
                                    @php
                                        $badge = match ($rec->status_reclamation) {
                                            'Remboursé' => 'bg-success',
                                            'Rejeté' => 'bg-danger',
                                            default => 'bg-warning text-dark',
                                        };
                                    @endphp
                                    <span class="badge {{ $badge }}">{{ $rec->status_reclamation ?? 'En attente' }}</span>
                                </td>
                                <td data-label="Action">
                                    @if ($peutGerer)
                                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalGerer{{ $rec->id_colis }}">
                                            <i class="fas fa-gear"></i> Gérer
                                        </button>
                                    @else
                                        <span class="text-muted small"><i class="fas fa-lock"></i> Non autorisé</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection

@section('modals')

    <!-- Modal : Nouvelle réclamation -->
    <div class="modal fade" id="modalNouvelleReclamation" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow rounded-4 overflow-hidden">
                <div class="modal-header border-0 py-3 px-4" style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));">
                    <h5 class="modal-title text-white d-flex align-items-center gap-2">
                        <i class="fas fa-magnifying-glass"></i> Nouvelle réclamation
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" id="rc_code_recherche" placeholder="Code du colis (ex: C12345)">
                        <button class="btn btn-primary" type="button" id="rc_btn_rechercher">
                            <i class="fas fa-magnifying-glass"></i> Rechercher
                        </button>
                    </div>

                    <div id="rc_resultat" class="d-none">
                        <div class="bg-light p-3 rounded-3 border mb-3">
                            <h6 class="fw-bold text-primary mb-3">Colis trouvé : <span id="rc_code_affiche"></span></h6>
                            <div class="row g-2 small">
                                <div class="col-md-6"><span class="text-muted">Nature :</span> <span id="rc_nature" class="fw-semibold"></span></div>
                                <div class="col-md-6"><span class="text-muted">Nom colis :</span> <span id="rc_nom" class="fw-semibold"></span></div>
                                <div class="col-md-6"><span class="text-muted">Expéditeur :</span> <span id="rc_expediteur" class="fw-semibold"></span></div>
                                <div class="col-md-6"><span class="text-muted">Destinataire :</span> <span id="rc_destinataire" class="fw-semibold"></span></div>
                            </div>
                        </div>

                        <form method="post" action="{{ route('admin.colis.reclamation.store') }}">
                            @csrf
                            <input type="hidden" name="id_colis" id="rc_id_colis">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Motif de la réclamation <span class="text-danger">*</span></label>
                                <textarea class="form-control" name="motif_reclamation" rows="3" placeholder="Ex: Colis endommagé pendant le transport..." required></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Montant à rembourser (FCFA) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="montant_remboursement" id="rc_montant" min="1" required>
                                <small class="text-muted">Par défaut : valeur déclarée du colis.</small>
                            </div>
                            <button type="submit" class="btn btn-danger w-100 fw-bold">Soumettre la réclamation</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modales : Gérer une réclamation -->
    @if ($peutGerer)
        @foreach ($reclamations as $rec)
            <div class="modal fade" id="modalGerer{{ $rec->id_colis }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow rounded-4 overflow-hidden">
                        <div class="modal-header border-0 py-3 px-4" style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));">
                            <h5 class="modal-title text-white">Gérer la réclamation</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form method="post" action="{{ route('admin.colis.reclamation.statut') }}">
                            @csrf
                            <div class="modal-body p-4">
                                <input type="hidden" name="id_colis_status" value="{{ $rec->id_colis }}">
                                <p><strong>Colis :</strong> {{ $rec->code_colis }}</p>
                                <p><strong>Motif :</strong><br>{{ $rec->motif_reclamation }}</p>
                                <p><strong>Montant à rembourser :</strong> {{ number_format($rec->montant_remboursement, 0, ',', ' ') }} FCFA</p>

                                <div class="mb-3">
                                    <label class="form-label">Changer le statut</label>
                                    <select name="status_reclamation" class="form-select statut-select" data-id="{{ $rec->id_colis }}">
                                        <option value="En attente" @selected($rec->status_reclamation === 'En attente')>En attente</option>
                                        <option value="Remboursé" @selected($rec->status_reclamation === 'Remboursé')>Remboursé</option>
                                        <option value="Rejeté" @selected($rec->status_reclamation === 'Rejeté')>Rejeté</option>
                                    </select>
                                </div>

                                @if ($authUser->droit === 'Admin')
                                    <div class="mb-3 admin-caisse-div" id="admin-caisse-{{ $rec->id_colis }}" style="{{ $rec->status_reclamation === 'Remboursé' ? '' : 'display:none;' }}">
                                        <label class="form-label text-danger fw-bold"><i class="fas fa-wallet"></i> Débiter quelle caisse ouverte ?</label>
                                        @php
                                            $caissesFiltrees = collect($caissesOuvertes)->filter(fn ($c) => $c->localite === $rec->provient_de || $c->localite === $rec->destination);
                                        @endphp
                                        <select name="admin_id_caisse" class="form-select border-danger">
                                            <option value="">-- Choisissez la caisse à débiter --</option>
                                            @forelse ($caissesFiltrees as $c)
                                                <option value="{{ $c->id_caisse_user }}">
                                                    Gare {{ $c->localite }} ({{ $c->localite === $rec->provient_de ? 'Départ' : 'Destination' }})
                                                </option>
                                            @empty
                                                <option value="">Aucune caisse ouverte pour la gare de départ ou de destination.</option>
                                            @endforelse
                                        </select>
                                        <small class="text-muted">Seules les caisses ouvertes de la gare de départ et de destination sont proposées.</small>
                                    </div>
                                @else
                                    <div class="alert alert-info small mb-0 admin-caisse-div" id="admin-caisse-{{ $rec->id_colis }}" style="{{ $rec->status_reclamation === 'Remboursé' ? '' : 'display:none;' }}">
                                        <i class="fas fa-circle-info"></i> Le remboursement sera débité de votre caisse individuelle actuellement ouverte.
                                    </div>
                                @endif
                            </div>
                            <div class="modal-footer border-0">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                                <button type="submit" class="btn btn-primary">Mettre à jour</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    @endif

@endsection

@section('scripts')
    <script>
        document.querySelectorAll('.statut-select').forEach(function (select) {
            select.addEventListener('change', function () {
                var adminDiv = document.getElementById('admin-caisse-' + this.dataset.id);
                if (adminDiv) {
                    adminDiv.style.display = (this.value === 'Remboursé') ? 'block' : 'none';
                }
            });
        });

        document.getElementById('rc_btn_rechercher')?.addEventListener('click', function () {
            var code = document.getElementById('rc_code_recherche').value.trim();
            if (! code) {
                Swal.fire('Champ requis', 'Merci de saisir un code colis.', 'warning');
                return;
            }

            document.getElementById('rc_resultat').classList.add('d-none');

            fetch('{{ route('admin.colis.reclamation.rechercher') }}?code=' + encodeURIComponent(code), {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(function (r) { return r.json().then(function (data) { return { ok: r.ok, data: data }; }); })
                .then(function (res) {
                    if (! res.ok) {
                        Swal.fire('Introuvable', res.data.error || 'Erreur.', 'error');
                        return;
                    }

                    var c = res.data.colis;
                    document.getElementById('rc_id_colis').value = c.id_colis;
                    document.getElementById('rc_code_affiche').textContent = c.code_colis;
                    document.getElementById('rc_nature').textContent = c.nature;
                    document.getElementById('rc_nom').textContent = c.nom_colis;
                    document.getElementById('rc_expediteur').textContent = c.expediteur + ' (' + c.numero_exp + ')';
                    document.getElementById('rc_destinataire').textContent = c.destinataire + ' (' + c.numero_dest + ')';
                    document.getElementById('rc_montant').value = c.valeur;
                    document.getElementById('rc_resultat').classList.remove('d-none');
                })
                .catch(function () {
                    Swal.fire('Erreur', 'Impossible de contacter le serveur.', 'error');
                });
        });

        document.getElementById('modalNouvelleReclamation')?.addEventListener('hidden.bs.modal', function () {
            document.getElementById('rc_resultat').classList.add('d-none');
            document.getElementById('rc_code_recherche').value = '';
        });
    </script>
@endsection
