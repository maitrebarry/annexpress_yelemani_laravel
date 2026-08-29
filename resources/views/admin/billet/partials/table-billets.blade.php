@php
    $isAdmin = in_array($authUser->droit, ['Admin', 'PDG'], true);
    $peutAnnuler = in_array($authUser->droit, ['Admin', 'chef_d_escale'], true) && $authUser->userHasPermission('Billets_annulation');
    $peutReporter = ! $authUser->estLectureSeule() && $authUser->userHasPermission('Billets_reporte');
    $peutImprimer = $authUser->userHasPermission('Billets_impression');
    $aujourdhuiStr = now()->toDateString();
    $demainStr = now()->addDay()->toDateString();
    $tableId = $tableId ?? 'example';
@endphp

<div class="row g-3 mb-4">
    <div class="col-6 col-xl-4">
        <div class="card border-0 shadow-sm border-start border-4 border-primary h-100">
            <div class="card-body">
                <div class="text-muted small"><i class="bx bx-ticket me-1"></i> Tickets</div>
                <div class="fs-4 fw-bold">{{ $liste->count() }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-4">
        <div class="card bg-primary text-white border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="small opacity-75"><i class="bx bx-money me-1"></i> Montant total</div>
                <div class="fs-4 fw-bold">{{ number_format($montant, 0, ',', ' ') }} F</div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow rounded-4 overflow-hidden">
    <div class="card-header border-0 py-3 px-4 d-flex align-items-center gap-2"
         style="background: linear-gradient(135deg, #0f3b5e, #1d6fa5); color: #fff;">
        <i class="bx bx-list-ul fs-5"></i>
        <span class="fw-semibold">Billets</span>
    </div>
    <div class="table-responsive p-2">
        <table id="{{ $tableId }}" class="table table-hover align-middle mb-0" style="width:100%">
            <thead>
                <tr class="text-muted small text-uppercase">
                    <th class="border-0">N° billet</th>
                    <th class="border-0">Client</th>
                    @if ($isAdmin)
                        <th class="border-0">Gare</th>
                    @endif
                    <th class="border-0">Destination</th>
                    <th class="border-0">N° place</th>
                    <th class="border-0">Heure</th>
                    <th class="border-0 text-end">Montant</th>
                    <th class="border-0">Statut</th>
                    @if ($peutAnnuler || $peutReporter || $peutImprimer)
                        <th class="border-0">Actions</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach ($liste as $b)
                    <tr>
                        <td><span class="badge bg-light text-dark border">{{ $b->numeroBillets }}</span></td>
                        <td class="fw-semibold">{{ $b->Client }}</td>
                        @if ($isAdmin)
                            <td>{{ $b->departId }} <span class="text-muted small">({{ $b->num_gare }})</span></td>
                        @endif
                        <td>{{ $b->destinationId }}</td>
                        <td>{{ $b->numeroPlace ?? '-' }}</td>
                        <td>{{ \Illuminate\Support\Carbon::parse($b->Heur_departs)->format('H:i') }}</td>
                        <td class="text-end fw-bold text-success">{{ number_format((float) preg_replace('/[^\d.]/', '', (string) $b->montant_payer), 0, ',', ' ') }} F</td>
                        <td>
                            @if ($b->status_billets === 'annule')
                                <span class="badge rounded-pill bg-danger-subtle text-danger border border-danger-subtle px-3 py-2">Annulé</span>
                            @elseif ($b->status_billets === 'annulation_demandee')
                                <span class="badge rounded-pill bg-warning-subtle text-warning-emphasis border border-warning-subtle px-3 py-2">Annulation demandée</span>
                            @else
                                <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-3 py-2">Actif</span>
                            @endif
                        </td>
                        @if ($peutAnnuler || $peutReporter || $peutImprimer)
                            <td>
                                @if (in_array($b->status_billets, ['annule', 'annulation_demandee'], true) && ! $peutImprimer)
                                    <span class="text-muted small">—</span>
                                @else
                                    <div class="dropdown">
                                        <a href="#" class="text-dark fs-5" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="bx bx-dots-vertical-rounded"></i>
                                        </a>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                            @if ($peutImprimer)
                                                <li>
                                                    <a class="dropdown-item thermal-print-btn" href="#" data-id="{{ $b->idBillets }}">
                                                        <i class="bx bx-printer me-2"></i>Imprimer (thermique)
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="{{ url('/admin/Liste_du_jours/recu/' . $b->idBillets) }}" target="_blank">
                                                        <i class="bx bx-file-pdf me-2"></i>Ouvrir PDF
                                                    </a>
                                                </li>
                                            @endif
                                            @if (! in_array($b->status_billets, ['annule', 'annulation_demandee'], true))
                                                @if ($peutReporter)
                                                    <li>
                                                        <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#modalReporter{{ $b->idBillets }}">
                                                            <i class="bx bx-calendar-edit me-2"></i>Reporter
                                                        </a>
                                                    </li>
                                                @endif
                                                @if ($peutAnnuler)
                                                    <li>
                                                        <a class="dropdown-item text-danger" href="#" data-bs-toggle="modal" data-bs-target="#modalAnnuler{{ $b->idBillets }}">
                                                            <i class="bx bx-x-circle me-2"></i>{{ $authUser->droit === 'chef_d_escale' ? "Demander l'annulation" : 'Annuler le billet' }}
                                                        </a>
                                                    </li>
                                                @endif
                                            @endif
                                        </ul>
                                    </div>
                                @endif
                            </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@if ($peutAnnuler || $peutReporter)
    @foreach ($liste as $b)
        @continue(in_array($b->status_billets, ['annule', 'annulation_demandee'], true))

        @if ($peutReporter)
            <div class="modal fade" id="modalReporter{{ $b->idBillets }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <form method="post" action="{{ route('admin.billet.reporter') }}">
                            @csrf
                            <input type="hidden" name="idBillets" value="{{ $b->idBillets }}">
                            <div class="modal-header bg-primary">
                                <h5 class="modal-title text-white"><i class="bx bx-calendar-edit me-1"></i> Reporter le voyage</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <p class="text-muted small">Billet {{ $b->numeroBillets }} — {{ $b->Client }}, actuellement le {{ \Illuminate\Support\Carbon::parse($b->jourVoyage)->format('d/m/Y') }} à {{ \Illuminate\Support\Carbon::parse($b->Heur_departs)->format('H:i') }}.</p>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Nouvelle date de voyage</label>
                                    <input type="date" class="form-control" name="nouvelle_date" min="{{ $aujourdhuiStr }}" max="{{ $demainStr }}" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Nouvelle heure de départ</label>
                                    <input type="time" class="form-control" name="nouvelle_heure" required>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                                <button type="submit" class="btn btn-primary">Reporter</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif

        @if ($peutAnnuler)
            @php $estAdminAnnulation = $authUser->droit === 'Admin'; @endphp
            <div class="modal fade" id="modalAnnuler{{ $b->idBillets }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <form method="post" action="{{ route('admin.billet.annuler') }}" class="form-annuler-billet">
                            @csrf
                            <input type="hidden" name="idBillets" value="{{ $b->idBillets }}">
                            <div class="modal-header bg-danger">
                                <h5 class="modal-title text-white">{{ $estAdminAnnulation ? 'Annuler le billet' : "Demander l'annulation" }}</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <p class="text-muted small">
                                    @if ($estAdminAnnulation)
                                        Cette action est définitive : la place sera restituée et le remboursement enregistré comme dépense sur la caisse ouverte de cette gare.
                                    @else
                                        La place et l'argent ne seront libérés qu'après confirmation par un Admin. Le billet reste valide en attendant.
                                    @endif
                                </p>
                                <label class="form-label fw-semibold">Motif {{ $estAdminAnnulation ? '(optionnel)' : '' }}</label>
                                <textarea class="form-control" name="motif_annulation" rows="2" {{ $estAdminAnnulation ? '' : 'required' }}></textarea>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Fermer</button>
                                <button type="submit" class="btn btn-danger btn-confirmer-annulation">{{ $estAdminAnnulation ? "Confirmer l'annulation" : 'Envoyer la demande' }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    @endforeach
@endif
