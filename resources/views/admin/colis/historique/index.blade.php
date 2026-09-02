@extends('layouts.admin')

@php $authUser = auth('staff')->user(); @endphp

@section('title', 'Historique des colis · TransGest Admin')

@section('breadcrumb-title')
    <span class="text-primary"><i class="fas fa-clock-rotate-left me-1"></i> G-colis</span>
@endsection
@section('breadcrumb-active', 'Historique des colis')

@section('breadcrumb-actions')
    @if ($authUser->droit !== 'PDG')
        <a href="{{ route('admin.colis.create') }}" class="btn btn-sm btn-primary rounded-pill shadow-sm">
            <i class="fas fa-plus me-1"></i> Ajouter
        </a>
    @endif
@endsection

@section('content')


    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="get" action="{{ route('admin.colis.historique.index') }}" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label mb-1">Date de début</label>
                    <input type="date" class="form-control" name="date_debut" value="{{ $dateDebut }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label mb-1">Date de fin</label>
                    <input type="date" class="form-control" name="date_fin" value="{{ $dateFin }}">
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-outline-primary w-100"><i class="fas fa-filter me-1"></i> Filtrer</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body border-top border-primary border-1">
            <ul class="nav nav-tabs nav-primary" role="tablist">
                <li class="nav-item" role="presentation">
                    <a class="nav-link active" data-bs-toggle="tab" href="#tabEnregistres" role="tab" aria-selected="true">
                        <div class="d-flex align-items-center">
                            <div class="tab-icon"><i class="fadeIn animated fas fa-calendar-days font-19"></i></div>
                            <div class="tab-title">Colis enregistrés</div>
                        </div>
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" data-bs-toggle="tab" href="#tabLivres" role="tab" aria-selected="false">
                        <div class="d-flex align-items-center">
                            <div class="tab-icon"><i class="fadeIn animated fas fa-clock font-19"></i></div>
                            <div class="tab-title">Colis livrés</div>
                        </div>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="tab-content py-3">
                <div class="tab-pane fade show active" id="tabEnregistres" role="tabpanel">
                    <div class="table-responsive">
                        <table id="tableEnregistres" class="table table-striped table-bordered mobile-card-table" style="width:100%">
                            <thead>
                                <tr class="text-center">
                                    <th>Expéditeur</th>
                                    <th>Destinataire</th>
                                    <th>Nom colis</th>
                                    <th>Valeur</th>
                                    <th>Frais de transaction</th>
                                    <th>Code colis</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody class="text-center">
                                @foreach ($enregistres as $c)
                                    <tr>
                                        <td data-label="Expéditeur">{{ $c->expediteur }}</td>
                                        <td data-label="Destinataire">{{ $c->destinataire }}</td>
                                        <td data-label="Nom colis">{{ $c->nom_colis }}</td>
                                        <td data-label="Valeur">{{ number_format($c->valeur, 0, ',', ' ') }} FCFA</td>
                                        <td data-label="Frais de transaction">{{ number_format($c->fraix_transaction, 0, ',', ' ') }} FCFA</td>
                                        <td data-label="Code colis" class="fw-bold text-primary">{{ $c->code_colis }}</td>
                                        <td data-label="Statut">{!! \App\Support\ColisStatus::badge($c->status) !!}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="tab-pane fade" id="tabLivres" role="tabpanel">
                    <div class="table-responsive">
                        <table id="tableLivres" class="table table-striped table-bordered mobile-card-table" style="width:100%">
                            <thead>
                                <tr class="text-center">
                                    <th>Expéditeur</th>
                                    <th>Destinataire</th>
                                    <th>Nom colis</th>
                                    <th>Valeur</th>
                                    <th>Frais de transaction</th>
                                    <th>Code colis</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody class="text-center">
                                @foreach ($livres as $c)
                                    <tr>
                                        <td data-label="Expéditeur">{{ $c->expediteur }}</td>
                                        <td data-label="Destinataire">{{ $c->destinataire }}</td>
                                        <td data-label="Nom colis">{{ $c->nom_colis }}</td>
                                        <td data-label="Valeur">{{ number_format($c->valeur, 0, ',', ' ') }} FCFA</td>
                                        <td data-label="Frais de transaction">{{ number_format($c->fraix_transaction, 0, ',', ' ') }} FCFA</td>
                                        <td data-label="Code colis" class="fw-bold text-primary">{{ $c->code_colis }}</td>
                                        <td data-label="Statut"><span class="badge bg-info">Livré</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <script>
        $(function () {
            var tEnregistres = $('#tableEnregistres').DataTable();
            var tLivres;

            $('a[href="#tabLivres"]').on('shown.bs.tab', function () {
                if (! tLivres) {
                    tLivres = $('#tableLivres').DataTable();
                } else {
                    tLivres.columns.adjust();
                }
            });

            $('a[href="#tabEnregistres"]').on('shown.bs.tab', function () {
                tEnregistres.columns.adjust();
            });
        });
    </script>
@endsection
