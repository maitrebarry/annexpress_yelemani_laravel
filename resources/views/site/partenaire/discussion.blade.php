<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Espace partenaire - TransGest</title>
    <link rel="icon" href="{{ asset('assets_site/img/favicon.svg') }}">
    <link href="{{ asset('assets_site/css/inter.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets_site/css/all.min.css') }}">
    <link href="{{ asset('assets_site/css/site-common.css') }}" rel="stylesheet">
    <style>
        .container { max-width: 900px; }
        .discussion-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
        .discussion-card { background: white; border-radius: var(--radius-lg); box-shadow: var(--shadow-md); display: flex; flex-direction: column; height: 60vh; }
        .discussion-messages { flex: 1; overflow-y: auto; padding: 24px; display: flex; flex-direction: column; gap: 14px; }
        .msg { max-width: 70%; padding: 12px 16px; border-radius: var(--radius-lg); font-size: 0.9rem; line-height: 1.5; }
        .msg-partenaire { align-self: flex-end; background: var(--primary); color: white; border-bottom-right-radius: 2px; }
        .msg-admin { align-self: flex-start; background: var(--gray-light); color: var(--dark); border-bottom-left-radius: 2px; }
        .msg-meta { font-size: 0.7rem; opacity: 0.7; margin-top: 6px; }
        .discussion-empty { text-align: center; color: var(--gray); margin: auto; }
        .discussion-form { display: flex; gap: 12px; padding: 16px 24px; border-top: 1px solid #eee; }
        .discussion-form textarea { flex: 1; resize: none; border: 1px solid #ddd; border-radius: var(--radius); padding: 10px 14px; font-family: inherit; font-size: 0.9rem; }
        .discussion-form textarea:focus { outline: none; border-color: var(--secondary); }
    </style>
</head>
<body>

@include('site.partials.nav')

<section>
    <div class="container">
        <div class="discussion-header">
            <div>
                <h2 style="margin-bottom:4px;">Espace partenaire</h2>
                <p style="color: var(--gray); font-size: 0.85rem;">Bonjour {{ $partenaire->nom_compagnie }}, discutez avec notre équipe ici.</p>
            </div>
            <form method="POST" action="{{ route('site.partenaire.deconnexion') }}">
                @csrf
                <button type="submit" class="btn btn-outline"><i class="fas fa-sign-out-alt"></i> Déconnexion</button>
            </form>
        </div>

        <div class="discussion-card">
            <div class="discussion-messages" id="discussionMessages">
                @forelse ($messages as $m)
                    <div class="msg {{ $m->auteur === 'partenaire' ? 'msg-partenaire' : 'msg-admin' }}">
                        {!! nl2br(e($m->message)) !!}
                        <div class="msg-meta">{{ $m->date_envoi->format('d/m/Y H:i') }}</div>
                    </div>
                @empty
                    <p class="discussion-empty">Envoyez votre premier message pour démarrer la discussion avec notre équipe.</p>
                @endforelse
            </div>
            <form method="POST" action="{{ route('site.partenaire.message') }}" class="discussion-form">
                @csrf
                <textarea name="message" rows="2" placeholder="Écrire un message..." required></textarea>
                <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i></button>
            </form>
        </div>
    </div>
</section>

<script>
    const box = document.getElementById('discussionMessages');
    box.scrollTop = box.scrollHeight;
</script>
</body>
</html>
