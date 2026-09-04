@extends('layouts.admin')

@section('title', 'Mon Profil · Sirali Admin')
@section('breadcrumb-title', 'Utilisateur')
@section('breadcrumb-active', 'Mon Profil')

@section('content')
<div class="row">
    <div class="col-12">

        @if ($errors->any())
            <div class="alert alert-danger shadow-sm border-0 mb-4">
                <div class="d-flex align-items-center">
                    <div class="fs-4 text-danger"><i class="fas fa-circle-xmark"></i></div>
                    <div class="ms-3">
                        <h6 class="mb-0 text-danger fw-bold">Erreur de validation</h6>
                        <ul class="mb-0 mt-1 ps-3 text-danger">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <div class="row g-4">
            <!-- Colonne photo + identité, en aperçu -->
            <div class="col-12 col-lg-3">
                <div class="card shadow-sm border-0 text-center h-100">
                    <div class="card-body p-4 d-flex flex-column align-items-center justify-content-center">
                        @if ($user->photo)
                            <img src="{{ asset('storage/profiles/'.$user->photo) }}" alt="Photo de profil" class="rounded-circle border p-1 mb-3" width="120" height="120" style="object-fit: cover;">
                        @else
                            <div class="rounded-circle bg-light text-secondary d-inline-flex align-items-center justify-content-center border mb-3" style="width: 120px; height: 120px;">
                                <i class="fas fa-user fs-1"></i>
                            </div>
                        @endif
                        <h6 class="fw-bold mb-1">{{ $user->utilisateurs }}</h6>
                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle">{{ $user->droit }}</span>
                        @if($user->id_agence)
                            <div class="text-secondary small mt-2">
                                <i class="fas fa-location-dot me-1"></i>{{ $user->agence->localite ?? 'Non défini' }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Onglets : infos personnelles / mot de passe -->
            <div class="col-12 col-lg-9">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
                        <ul class="nav nav-pills gap-2" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabInfos" type="button">
                                    <i class="fas fa-id-card me-1"></i> Informations personnelles
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabMotPasse" type="button">
                                    <i class="fas fa-lock me-1"></i> Mot de passe
                                </button>
                            </li>
                        </ul>
                    </div>

                    <div class="card-body p-4">
                        <div class="tab-content">

                            <!-- Onglet Informations personnelles -->
                            <div class="tab-pane fade show active" id="tabInfos" role="tabpanel">
                                <form action="{{ route('admin.profil.update') }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <input type="hidden" name="section" value="infos">

                                    <div class="row g-3">
                                        <div class="col-12">
                                            <label class="form-label fw-bold small text-secondary">Photo de profil</label>
                                            <input type="file" class="form-control" name="photo" accept="image/*">
                                            <div class="form-text small text-muted">Format accepté : JPG, PNG ou WEBP. Taille max : 2 Mo.</div>
                                        </div>

                                        <div class="col-12 col-md-6">
                                            <label class="form-label fw-bold small text-secondary">Nom d'utilisateur</label>
                                            <input type="text" class="form-control" name="utilisateurs" value="{{ old('utilisateurs', $user->utilisateurs) }}" required>
                                        </div>

                                        <div class="col-12 col-md-6">
                                            <label class="form-label fw-bold small text-secondary">Adresse Email</label>
                                            <input type="email" class="form-control" name="emailUser" value="{{ old('emailUser', $user->emailUser) }}" required>
                                        </div>

                                        <div class="col-12 col-md-6">
                                            <label class="form-label fw-bold small text-secondary">Téléphone</label>
                                            <input type="text" class="form-control" name="telephone" value="{{ old('telephone', $user->telephone) }}">
                                        </div>

                                        <div class="col-12 col-md-6">
                                            <label class="form-label fw-bold small text-secondary">Rôle / Droit</label>
                                            <input type="text" class="form-control bg-light" value="{{ $user->droit }}" readonly>
                                        </div>

                                        @if($user->id_agence)
                                            <div class="col-12 col-md-6">
                                                <label class="form-label fw-bold small text-secondary">Agence assignée</label>
                                                <input type="text" class="form-control bg-light" value="{{ $user->agence->localite ?? 'Non défini' }}" readonly>
                                            </div>
                                        @endif

                                        <div class="col-12 mt-4 text-end">
                                            <button type="submit" class="btn btn-primary px-4 shadow-sm">
                                                <i class="fas fa-circle-check me-1"></i>Enregistrer les modifications
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>

                            <!-- Onglet Mot de passe -->
                            <div class="tab-pane fade" id="tabMotPasse" role="tabpanel">
                                <form action="{{ route('admin.profil.update') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="section" value="mot_de_passe">
                                    {{-- Champs verrouillés de cet onglet, réenvoyés inchangés pour que la
                                         validation (qui porte toujours sur les 2 sections) ne les efface pas. --}}
                                    <input type="hidden" name="utilisateurs" value="{{ $user->utilisateurs }}">
                                    <input type="hidden" name="emailUser" value="{{ $user->emailUser }}">
                                    <input type="hidden" name="telephone" value="{{ $user->telephone }}">

                                    <p class="text-secondary small mb-3">
                                        Choisissez un nouveau mot de passe pour votre compte.
                                    </p>

                                    <div class="row g-3">
                                        <div class="col-12 col-md-6">
                                            <label class="form-label fw-bold small text-secondary">Nouveau mot de passe</label>
                                            <input type="password" class="form-control" name="motPasse" placeholder="Minimum 6 caractères" required>
                                        </div>

                                        <div class="col-12 col-md-6">
                                            <label class="form-label fw-bold small text-secondary">Confirmer le nouveau mot de passe</label>
                                            <input type="password" class="form-control" name="motPasse_confirmation" placeholder="Ressaisir le mot de passe" required>
                                        </div>

                                        <div class="col-12 mt-4 text-end">
                                            <button type="submit" class="btn btn-primary px-4 shadow-sm">
                                                <i class="fas fa-lock me-1"></i>Modifier le mot de passe
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if ($errors->any() && old('section') === 'mot_de_passe')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            new bootstrap.Tab(document.querySelector('[data-bs-target="#tabMotPasse"]')).show();
        });
    </script>
@endif
@endsection
