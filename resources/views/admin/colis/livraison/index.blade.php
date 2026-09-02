@extends('layouts.admin')

@section('title', 'Livraison des colis · TransGest Admin')

@section('breadcrumb-title', 'G-colis')
@section('breadcrumb-active', 'Livraison des colis')

@section('breadcrumb-actions')
    <a href="{{ route('admin.colis.envoi.index') }}" class="btn btn-primary split-bg-primary text-white">Voir la liste</a>
@endsection

@section('content')


    <div class="card">
        <div class="card-body">
            <!-- ========= Formulaire unique ========= -->
            <form method="post" action="{{ route('admin.colis.livraison.store') }}" class="mb-4">
                @csrf
                <div class="row g-3">
                    <!-- Champ Code -->
                    <div class="col-md-12">
                        <label class="form-label">Numéro de code</label>
                        <input type="text"
                            name="code"
                            class="form-control"
                            placeholder="Numéro du code"
                            value="{{ $codeRecherche ?? '' }}"
                            required
                            autocomplete="off">
                    </div>

                    @if ($colis)
                        <!-- Champ caché pour permettre la livraison -->
                        <input type="hidden" name="id_colis" value="{{ $colis->id_colis }}">

                        <!-- ===== Détails du colis ===== -->
                        <div class="col-12">
                            <div class="row mt-3">
                                <!-- Expéditeur -->
                                <div class="col-md-4 mb-3">
                                    <div class="card shadow-sm h-100">
                                        <h5 class="card-header">Expéditeur</h5>
                                        <div class="card-body">
                                            <div class="mb-2">
                                                <label class="form-label">Nom</label>
                                                <input class="form-control" value="{{ $colis->expediteur }}" readonly>
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label">Téléphone</label>
                                                <input class="form-control" value="{{ $colis->numero_exp }}" readonly>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Destinataire -->
                                <div class="col-md-4 mb-3">
                                    <div class="card shadow-sm h-100">
                                        <h5 class="card-header">Destinataire</h5>
                                        <div class="card-body">
                                            <div class="mb-2">
                                                <label class="form-label">Nom</label>
                                                <input class="form-control" value="{{ $colis->destinataire }}" readonly>
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label">Téléphone</label>
                                                <input class="form-control" value="{{ $colis->numero_dest }}" readonly>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Colis -->
                                <div class="col-md-4 mb-3">
                                    <div class="card shadow-sm h-100">
                                        <h5 class="card-header">Colis</h5>
                                        <div class="card-body">
                                            <div class="mb-2">
                                                <label class="form-label">Nom</label>
                                                <input class="form-control" value="{{ $colis->nom_colis }}" readonly>
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label">Nature</label>
                                                <input class="form-control" value="{{ $colis->nature }}" readonly>
                                            </div>
                                            <div class="row mb-2">
                                                <div class="col">
                                                    <label class="form-label">Destination</label>
                                                    <input class="form-control" value="{{ $colis->localite }}" readonly>
                                                </div>
                                                <div class="col">
                                                    <label class="form-label">Valeur</label>
                                                    <input class="form-control" value="{{ $colis->valeur }}" readonly>
                                                </div>
                                            </div>
                                            <div class="row mb-2">
                                                <div class="col">
                                                    <label class="form-label">Frais transaction</label>
                                                    <input class="form-control" value="{{ $colis->fraix_transaction }}" readonly>
                                                </div>
                                                <div class="col">
                                                    <label class="form-label">Statut</label>
                                                    <input class="form-control" value="{{ $colis->status }}" readonly>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div> <!-- /row -->
                        </div>
                    @endif

                    <!-- ===== Bouton dynamique ===== -->
                    @if ($livraisonReussie && $colis)
                        @php
                            $msgLivraison = 'Bonjour '.$colis->expediteur.', votre colis (code '
                                .$colis->code_colis.') a bien été remis à son destinataire. Merci de votre confiance.';
                            $lienWhatsappLivraison = \App\Support\WhatsAppLink::url($colis->whatsapp_exp ?: $colis->numero_exp, $msgLivraison);
                        @endphp
                        <div class="col-12 mt-3">
                            <div class="alert alert-success d-flex flex-wrap align-items-center justify-content-between gap-2 mb-0">
                                <span><i class="fas fa-circle-check me-1"></i> Colis livré avec succès !</span>
                                @if ($lienWhatsappLivraison)
                                    <a href="{{ $lienWhatsappLivraison }}" target="_blank" rel="noopener" class="btn btn-success">
                                        <i class="fab fa-whatsapp me-1"></i> Confirmer la remise à l'expéditeur par WhatsApp
                                    </a>
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="col-12 mt-3">
                            <button type="submit"
                                class="btn {{ $colis ? ($peutLivrer ? 'btn-success' : 'btn-secondary') : 'btn-primary' }}"
                                name="{{ $colis ? ($peutLivrer ? 'livrer' : 'envoi') : 'envoi' }}"
                                {{ $colis && ! $peutLivrer ? 'disabled' : '' }}>
                                {{ $colis ? ($peutLivrer ? 'Valider la livraison' : 'Livraison impossible') : 'Valider' }}
                            </button>
                        </div>
                    @endif
                </div> <!-- /row -->
            </form>
        </div>
    </div>

@endsection
