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
        .auth-card { max-width: 460px; margin: 0 auto; background: white; border-radius: var(--radius-lg); box-shadow: var(--shadow-md); padding: 36px; }
        .auth-tabs { display: flex; gap: 8px; margin-bottom: 28px; background: var(--gray-light); border-radius: var(--radius); padding: 4px; }
        .auth-tab { flex: 1; text-align: center; padding: 10px; border-radius: var(--radius); font-weight: 600; font-size: 0.85rem; cursor: pointer; color: var(--gray); }
        .auth-tab.active { background: white; color: var(--primary); box-shadow: var(--shadow); }
        .auth-panel { display: none; }
        .auth-panel.active { display: block; }
        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; font-size: 0.8rem; font-weight: 600; margin-bottom: 6px; color: var(--dark); }
        .form-control { width: 100%; padding: 10px 14px; border: 1px solid #ddd; border-radius: var(--radius); font-size: 0.9rem; font-family: inherit; }
        .form-control:focus { outline: none; border-color: var(--secondary); }
        h1 { text-align: center; margin-bottom: 8px; font-size: 1.6rem; }
        .auth-subtitle { text-align: center; color: var(--gray); font-size: 0.85rem; margin-bottom: 28px; }
    </style>
</head>
<body>

@include('site.partials.nav')

<section>
    <div class="container">
        <h1>Espace partenaire</h1>
        <p class="auth-subtitle">Discutez directement avec notre équipe pour rejoindre TransGest</p>

        <div class="auth-card">
            <div class="auth-tabs">
                <div class="auth-tab active" data-tab="connexion">Se connecter</div>
                <div class="auth-tab" data-tab="inscription">Créer un compte</div>
            </div>

            <div class="auth-panel active" id="panel-connexion">
                <form method="POST" action="{{ route('site.partenaire.connexion') }}">
                    @csrf
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Mot de passe</label>
                        <input type="password" name="mot_de_passe" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Se connecter</button>
                </form>
            </div>

            <div class="auth-panel" id="panel-inscription">
                <form method="POST" action="{{ route('site.partenaire.inscription') }}">
                    @csrf
                    <div class="form-group">
                        <label>Nom de la compagnie *</label>
                        <input type="text" name="nom_compagnie" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Email *</label>
                        <input type="email" name="email_inscription" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Téléphone</label>
                        <input type="text" name="telephone" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Mot de passe *</label>
                        <input type="password" name="mot_de_passe_inscription" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Créer mon compte</button>
                </form>
            </div>
        </div>
    </div>
</section>

<script>
    document.querySelectorAll('.auth-tab').forEach(function(tab) {
        tab.addEventListener('click', function() {
            document.querySelectorAll('.auth-tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.auth-panel').forEach(p => p.classList.remove('active'));
            tab.classList.add('active');
            document.getElementById('panel-' + tab.getAttribute('data-tab')).classList.add('active');
        });
    });
</script>
</body>
</html>
