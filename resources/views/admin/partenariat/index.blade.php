@extends('layouts.admin')

@section('title', 'Demandes de partenariat · Sirali Admin')

@section('breadcrumb-title')
    <span class="text-primary"><i class="fas fa-handshake me-1"></i> Partenariats</span>
@endsection
@section('breadcrumb-active', 'Demandes de partenariat')

@section('content')


    <div class="card border-0 shadow rounded-4 overflow-hidden">
        <div class="card-header border-0 py-4 px-4 d-flex align-items-center gap-2"
             style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: #fff;">
            <i class="fas fa-handshake fs-4"></i>
            <span class="fw-semibold fs-5">Compagnies partenaires inscrites</span>
        </div>
        <div class="table-responsive">
            @php
                $theadStyle = 'background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: #fff;';
            @endphp
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="border-0" style="{{ $theadStyle }}">Compagnie</th>
                        <th class="border-0" style="{{ $theadStyle }}">Contact</th>
                        <th class="border-0" style="{{ $theadStyle }}">Dernier message</th>
                        <th class="border-0" style="{{ $theadStyle }}">Statut</th>
                        <th class="border-0" style="{{ $theadStyle }}">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($partenaires as $p)
                        <tr>
                            <td class="fw-semibold">{{ $p->nom_compagnie }}</td>
                            <td>
                                <div>{{ $p->email }}</div>
                                @if ($p->telephone)
                                    <div class="text-muted small">{{ $p->telephone }}</div>
                                @endif
                            </td>
                            <td>
                                @if ($p->dernier_message)
                                    <div class="text-truncate" style="max-width: 260px;">
                                        <span class="text-muted small">{{ $p->dernier_auteur === 'admin' ? 'Vous : ' : '' }}</span>{{ $p->dernier_message }}
                                    </div>
                                    <div class="text-muted small">{{ $p->date_dernier_message?->format('d/m/Y H:i') }}</div>
                                @else
                                    <span class="text-muted small">Aucun message</span>
                                @endif
                            </td>
                            <td>
                                @if ($p->en_attente_reponse)
                                    <span class="badge bg-warning text-dark py-2 px-3"><i class="fas fa-clock me-1"></i>En attente de réponse</span>
                                @elseif ($p->messages_count > 0)
                                    <span class="badge bg-success py-2 px-3"><i class="fas fa-check me-1"></i>À jour</span>
                                @else
                                    <span class="badge bg-secondary py-2 px-3">Inscrit</span>
                                @endif
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalDiscussion{{ $p->id_partenaire }}">
                                    <i class="fas fa-comment-dots me-1"></i> Discussion
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Aucun partenaire inscrit pour le moment.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @foreach ($partenaires as $p)
        <div class="modal fade" id="modalDiscussion{{ $p->id_partenaire }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content border-0 shadow rounded-4 overflow-hidden">
                    <div class="modal-header border-0 py-3 px-4" style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));">
                        <h5 class="modal-title text-white d-flex align-items-center gap-2">
                            <i class="fas fa-handshake"></i> {{ $p->nom_compagnie }}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4" style="max-height: 55vh; overflow-y: auto; background: #f4f6fa;">
                        @forelse ($p->messages()->orderBy('date_envoi')->get() as $m)
                            <div class="d-flex mb-3 {{ $m->auteur === 'admin' ? 'justify-content-end' : 'justify-content-start' }}">
                                <div class="p-3 rounded-4 {{ $m->auteur === 'admin' ? 'bg-primary text-white' : 'bg-white border' }}" style="max-width: 75%;">
                                    <div style="white-space: pre-line;">{{ $m->message }}</div>
                                    <div class="{{ $m->auteur === 'admin' ? 'text-white-50' : 'text-muted' }} small mt-1">{{ $m->date_envoi->format('d/m/Y H:i') }}</div>
                                </div>
                            </div>
                        @empty
                            <p class="text-center text-muted">Aucun message pour le moment.</p>
                        @endforelse
                    </div>
                    <form method="POST" action="{{ route('admin.partenariat.repondre') }}" class="modal-footer border-0 p-3 gap-2">
                        @csrf
                        <input type="hidden" name="id_partenaire" value="{{ $p->id_partenaire }}">
                        <textarea name="message" rows="2" class="form-control" placeholder="Répondre à {{ $p->nom_compagnie }}..." required></textarea>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i></button>
                    </form>
                </div>
            </div>
        </div>
    @endforeach

@endsection
