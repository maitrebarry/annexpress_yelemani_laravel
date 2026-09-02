@php
    $estEmbarque = $b->statut_embarquement === 'embarque';
    $busDecolle = ! empty($b->bus_decolle_le);
@endphp
<tr>
    <td>
        <input type="checkbox" class="form-check-input chk-billet" value="{{ $b->idBillets }}" {{ ($estEmbarque || $busDecolle) ? 'disabled' : '' }}>
    </td>
    <td><span class="badge bg-light text-dark border">{{ $b->numeroBillets }}</span></td>
    <td class="fw-semibold">{{ $b->Client }}</td>
    <td>{{ $b->destinationId }}</td>
    <td>{{ $b->numeroPlace ?? '-' }}</td>
    <td>{{ \Illuminate\Support\Carbon::parse($b->Heur_departs)->format('H:i') }}</td>
    <td>
        @if ($busDecolle)
            <span class="badge rounded-pill bg-secondary-subtle text-secondary border border-secondary-subtle px-3 py-2">Bus décollé</span>
        @elseif ($estEmbarque)
            <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-3 py-2">
                Embarqué {{ $b->embarque_par_nom ? 'par '.$b->embarque_par_nom : '' }}
            </span>
        @else
            <span class="badge rounded-pill bg-warning-subtle text-warning-emphasis border border-warning-subtle px-3 py-2">En attente</span>
        @endif
    </td>
    <td>
        @if ($busDecolle)
            <span class="text-muted small">—</span>
        @elseif ($estEmbarque)
            <button type="button" class="btn btn-sm btn-outline-secondary btn-annuler-embarquement" data-id="{{ $b->idBillets }}">
                <i class="fas fa-rotate-left"></i> Annuler
            </button>
        @else
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-sm btn-success btn-marquer-embarque" data-id="{{ $b->idBillets }}">
                    <i class="fas fa-check"></i> Embarquer
                </button>
                <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalDemanderReport"
                    data-id="{{ $b->idBillets }}" data-client="{{ $b->Client }}">
                    <i class="fas fa-right-left"></i> Reporter
                </button>
            </div>
        @endif
    </td>
</tr>
