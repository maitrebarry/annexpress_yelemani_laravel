@extends('layouts.admin')

@php $authUser = auth('staff')->user(); @endphp

@section('title', "Bordereau d'escale · Sirali Admin")

@section('breadcrumb-title')
    <span class="text-primary"><i class="fas fa-wallet me-1"></i> Caisse</span>
@endsection
@section('breadcrumb-active', 'N° '.($bordereau->numero_bordereau ?? $bordereau->id_bordereau))

@section('breadcrumb-actions')
    <a href="{{ route('admin.bordereau-escale.index') }}" class="btn btn-sm btn-outline-primary rounded-pill shadow-sm">
        <i class="fas fa-arrow-left me-1"></i> Retour
    </a>
@endsection

@section('content')

    <div class="row">
        <div class="col-xl-8 mx-auto">

            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white border-0 pt-3 px-4">
                    <h5 class="mb-0 text-primary">Informations</h5>
                </div>
                <div class="card-body px-4 pb-4">
                    <div class="row g-2">
                        <div class="col-md-4"><strong>Date :</strong> {{ \Illuminate\Support\Carbon::parse($bordereau->date_escale)->format('d/m/Y') }}</div>
                        <div class="col-md-4"><strong>Bus N° :</strong> {{ $bordereau->numero_bus ?? '-' }}</div>
                        <div class="col-md-4"><strong>N° Bordereau :</strong> {{ $bordereau->numero_bordereau ?? '-' }}</div>
                        <div class="col-md-4"><strong>Gare de rattachement :</strong> {{ ($bordereau->localite ?? '-').' ('.($bordereau->numeroGare ?? '-').')' }}</div>
                        <div class="col-md-4"><strong>Escale :</strong> {{ $bordereau->escale_libelle ?? '-' }}</div>
                        <div class="col-md-4"><strong>Chef / agent d'escale :</strong> {{ $bordereau->nom_agent_escale ?? '-' }}</div>
                        <div class="col-md-4"><strong>Saisi par :</strong> {{ $bordereau->nom_saisi_par ?? '-' }}</div>
                        <div class="col-md-4"><strong>Saisi le :</strong> {{ \Illuminate\Support\Carbon::parse($bordereau->date_saisie)->format('d/m/Y H:i') }}</div>
                        <div class="col-md-4">
                            <strong>Caisse :</strong>
                            @if (($bordereau->statut_caisse ?? null) === 'versee')
                                <span class="badge bg-success">Versée</span>
                            @elseif (($bordereau->statut_caisse ?? null) === 'fermee')
                                <span class="badge bg-warning text-dark">À verser</span>
                            @else
                                <span class="badge bg-secondary">—</span>
                            @endif
                        </div>
                    </div>

                    @if (($bordereau->statut_caisse ?? null) === 'fermee' && (int) ($bordereau->id_utilisateur_caisse ?? 0) === (int) $authUser->idUser)
                        <div class="alert alert-info mt-3 mb-0">
                            Cette caisse est encore à verser.
                            <a href="{{ route('admin.caisse.ma-caisse') }}" class="alert-link">Aller sur « Ma Caisse »</a> pour procéder au versement.
                        </div>
                    @endif
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white border-0 pt-3 px-4">
                    <h5 class="mb-0 text-primary">Billets vendus</h5>
                </div>
                <div class="card-body p-0">
                    <table class="table mb-0">
                        <thead><tr><th>Destination</th><th>Nb billets</th><th>Prix unit.</th><th class="text-end">Montant</th></tr></thead>
                        <tbody>
                            @php $lignesBillets = $bordereau->lignes->where('type_ligne', 'billet'); @endphp
                            @forelse ($lignesBillets as $l)
                                <tr>
                                    <td>{{ $l->destination }}</td>
                                    <td>{{ (int) $l->nombre }}</td>
                                    <td>{{ number_format((float) $l->prix_unitaire, 0, ',', ' ') }}</td>
                                    <td class="text-end">{{ number_format((float) $l->montant, 0, ',', ' ') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted">Aucune ligne.</td></tr>
                            @endforelse
                        </tbody>
                        <tfoot><tr><th colspan="3">Sous-total</th><th class="text-end">{{ number_format((float) $bordereau->total_billets, 0, ',', ' ') }}</th></tr></tfoot>
                    </table>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white border-0 pt-3 px-4">
                    <h5 class="mb-0 text-primary">Bagages payants</h5>
                </div>
                <div class="card-body p-0">
                    <table class="table mb-0">
                        <thead><tr><th>Description</th><th class="text-end">Montant</th></tr></thead>
                        <tbody>
                            @php $lignesBagages = $bordereau->lignes->where('type_ligne', 'bagage'); @endphp
                            @forelse ($lignesBagages as $l)
                                <tr>
                                    <td>{{ $l->description ?? '-' }}</td>
                                    <td class="text-end">{{ number_format((float) $l->montant, 0, ',', ' ') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="2" class="text-center text-muted">Aucune ligne.</td></tr>
                            @endforelse
                        </tbody>
                        <tfoot><tr><th>Sous-total</th><th class="text-end">{{ number_format((float) $bordereau->total_bagages, 0, ',', ' ') }}</th></tr></tfoot>
                    </table>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pt-3 px-4">
                    <h5 class="mb-0 text-primary">Récapitulatif</h5>
                </div>
                <div class="card-body px-4 pb-4">
                    <div class="row g-2">
                        <div class="col-md-6"><strong>Dépenses :</strong> {{ number_format((float) $bordereau->montant_depenses, 0, ',', ' ') }} FCFA</div>
                        <div class="col-md-6"><strong>Commission agent d'escale :</strong> {{ number_format((float) $bordereau->montant_commission, 0, ',', ' ') }} FCFA</div>
                        <div class="col-md-6"><strong>Total versé (saisi) :</strong> {{ number_format((float) $bordereau->total_verse_saisi, 0, ',', ' ') }} FCFA</div>
                        <div class="col-md-6">
                            <strong>Écart système :</strong>
                            <span class="{{ (float) ($bordereau->ecart ?? 0) < 0 ? 'text-danger' : 'text-success' }}">
                                {{ isset($bordereau->ecart) ? number_format((float) $bordereau->ecart, 0, ',', ' ').' FCFA' : '-' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

@endsection
