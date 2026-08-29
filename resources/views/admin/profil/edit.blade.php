@extends('layouts.admin')

@section('title', 'Mon Profil · TransHub Admin')
@section('breadcrumb-title', 'Utilisateur')
@section('breadcrumb-active', 'Mon Profil')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        @include('admin.partials.set_flash')

        @if ($errors->any())
            <div class="alert alert-danger shadow-sm border-0 mb-4">
                <div class="d-flex align-items-center">
                    <div class="fs-4 text-danger"><i class="bi bi-x-circle-fill"></i></div>
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

        <div class="card shadow-sm border-0">
            <div class="card-header bg-transparent border-0 pt-4 px-4">
                <h5 class="mb-0 fw-bold text-dark"><i class="bx bxs-user me-2 text-primary"></i>Mon Profil</h5>
                <p class="text-secondary small mb-0 mt-1">Gérez vos informations personnelles et mettez à jour votre mot de passe.</p>
            </div>
            
            <div class="card-body p-4">
                <form action="{{ route('admin.profil.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="row align-items-center mb-4">
                        <div class="col-12 col-md-auto text-center mb-3 mb-md-0">
                            @if ($user->photo)
                                <img src="{{ asset('storage/profiles/'.$user->photo) }}" alt="Photo de profil" class="rounded-circle border p-1" width="100" height="100" style="object-fit: cover;">
                            @else
                                <div class="rounded-circle bg-light text-secondary d-inline-flex align-items-center justify-content-center border" style="width: 100px; height: 100px;">
                                    <i class="bx bx-user fs-1"></i>
                                </div>
                            @endif
                        </div>
                        <div class="col-12 col-md">
                            <label class="form-label fw-bold small text-secondary mb-1">Photo de profil</label>
                            <input type="file" class="form-control" name="photo" accept="image/*">
                            <div class="form-text small text-muted">Format accepté : JPG, PNG ou WEBP. Taille max : 2 Mo.</div>
                        </div>
                    </div>

                    <div class="row g-3">
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
                        
                        <div class="col-12">
                            <hr class="my-4">
                            <h6 class="fw-bold mb-3"><i class="bx bx-lock-alt me-2 text-primary"></i>Modifier le mot de passe</h6>
                            <p class="text-secondary small mb-3">Laissez ces champs vides si vous ne souhaitez pas modifier votre mot de passe actuel.</p>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold small text-secondary">Nouveau mot de passe</label>
                            <input type="password" class="form-control" name="motPasse" placeholder="Minimum 6 caractères">
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold small text-secondary">Confirmer le nouveau mot de passe</label>
                            <input type="password" class="form-control" name="motPasse_confirmation" placeholder="Ressaisir le mot de passe">
                        </div>

                        <div class="col-12 mt-4 text-end">
                            <button type="submit" class="btn btn-primary px-4 shadow-sm">
                                <i class="bx bx-check-circle me-1"></i>Enregistrer les modifications
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
