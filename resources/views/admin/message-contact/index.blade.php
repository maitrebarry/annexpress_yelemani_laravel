@extends('layouts.admin')

@section('title', 'Messages reçus · Sirali Admin')
@section('breadcrumb-title', 'Configuration')
@section('breadcrumb-active', 'Messages reçus')

@section('content')
<div class="row">
    @include('admin.partials.config-nav', ['active' => 'messages'])

    <div class="col-12 col-xxl-9">
        <div class="card config-card">
            <div class="card-header">
                <h5 class="mb-0 fw-bold"><i class="fas fa-envelope-open-text me-2"></i>Messages reçus (site public & widget d'aide)</h5>
            </div>
            <div class="card-body p-4">
                @forelse ($liste as $m)
                    <div class="border rounded-3 p-3 mb-3 {{ $m->traite ? 'bg-light' : '' }}">
                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                            <div>
                                <span class="fw-bold">{{ $m->nom }}</span>
                                <span class="badge {{ $m->origine === 'aide' ? 'bg-info' : 'bg-secondary' }} ms-2">
                                    {{ $m->origine === 'aide' ? 'Widget aide' : 'Page contact' }}
                                </span>
                                @if ($m->traite)
                                    <span class="badge bg-success ms-1">Traité</span>
                                @endif
                                <div class="text-secondary small mt-1">
                                    @if ($m->telephone)<span class="me-3"><i class="fas fa-phone me-1"></i>{{ $m->telephone }}</span>@endif
                                    @if ($m->email)<span class="me-3"><i class="fas fa-envelope me-1"></i>{{ $m->email }}</span>@endif
                                    <span><i class="fas fa-clock me-1"></i>{{ $m->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <form action="{{ route('admin.message-contact.traiter', $m->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-sm {{ $m->traite ? 'btn-outline-secondary' : 'btn-outline-success' }}">
                                        <i class="fas {{ $m->traite ? 'fa-rotate-left' : 'fa-check' }} me-1"></i>{{ $m->traite ? 'Rouvrir' : 'Marquer traité' }}
                                    </button>
                                </form>
                                <a href="{{ route('admin.message-contact.destroy', $m->id) }}" class="btn btn-sm btn-outline-danger delete-button">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </div>
                        <p class="mb-0 mt-2">{{ $m->message }}</p>
                    </div>
                @empty
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-inbox fs-3 d-block mb-2"></i>
                        Aucun message reçu pour le moment.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    <script src="{{ asset('mon_js/alert_delete.js') }}?v={{ @filemtime(public_path('mon_js/alert_delete.js')) }}"></script>
@endsection
