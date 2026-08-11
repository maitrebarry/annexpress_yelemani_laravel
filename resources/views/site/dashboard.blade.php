<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Espace Compagnie · TransHub</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root { --primary:#f59e0b; --navy:#0B1F3A; --bg:#f4f6f9; --text-muted:#64748b; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Outfit', sans-serif; background: var(--bg); color: var(--navy); }
        header {
            background: linear-gradient(135deg, #071525, #0f2d54);
            color: #fff; padding: 20px 32px;
            display: flex; align-items: center; justify-content: space-between;
        }
        header .brand { font-weight: 800; font-size: 1.3rem; }
        header .brand span { color: var(--primary); }
        header .who { font-size: .85rem; color: rgba(255,255,255,.7); }
        main { padding: 32px; max-width: 900px; margin: 0 auto; }
        h1 { font-size: 1.5rem; margin-bottom: 4px; }
        .sub { color: var(--text-muted); margin-bottom: 28px; }
        .badge-role {
            display: inline-block; background: var(--primary); color: #fff;
            font-size: .72rem; font-weight: 600; padding: 3px 10px; border-radius: 999px;
            letter-spacing: .5px; text-transform: uppercase;
        }
        .cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 18px; }
        .card {
            background: #fff; border-radius: 16px; padding: 22px;
            box-shadow: 0 8px 24px -12px rgba(0,0,0,.1);
        }
        .card .label { font-size: .72rem; text-transform: uppercase; letter-spacing: .6px; color: var(--text-muted); margin-bottom: 8px; }
        .card .value { font-size: 1.15rem; font-weight: 600; }
        .card .value.empty { color: var(--text-muted); font-weight: 400; font-style: italic; font-size: 1rem; }
        form.logout-form { margin: 0; }
        .btn-logout {
            background: transparent; border: 1px solid rgba(255,255,255,.3); color: #fff;
            padding: 7px 16px; border-radius: 8px; font-family: inherit; cursor: pointer; font-size: .85rem;
        }
        .btn-logout:hover { background: rgba(255,255,255,.1); }
    </style>
</head>
<body>

<header>
    <div class="brand">Trans<span>Hub</span> <span style="font-weight:400;font-size:.8rem;color:rgba(255,255,255,.6);">Espace Compagnie</span></div>
    <div style="display:flex; align-items:center; gap:16px;">
        <div class="who">{{ $utilisateur->utilisateurs }} · <span class="badge-role">{{ $utilisateur->droit }}</span></div>
        <form class="logout-form" method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn-logout"><i class="bi bi-box-arrow-right"></i> Déconnexion</button>
        </form>
    </div>
</header>

<main>
    <h1>Bonjour, {{ $utilisateur->utilisateurs }}</h1>
    <p class="sub">Votre espace de gestion, propre à votre compagnie et votre agence.</p>

    <div class="cards">
        <div class="card">
            <div class="label">Compagnie</div>
            <div class="value {{ $utilisateur->compagnie ? '' : 'empty' }}">
                {{ $utilisateur->compagnie->nom_compagnie ?? 'Aucune compagnie associée' }}
            </div>
        </div>
        <div class="card">
            <div class="label">Agence</div>
            <div class="value {{ $utilisateur->agence ? '' : 'empty' }}">
                {{ $utilisateur->agence->localite ?? 'Aucune agence associée' }}
            </div>
        </div>
        <div class="card">
            <div class="label">Rôle</div>
            <div class="value">{{ $utilisateur->droit }}</div>
        </div>
        <div class="card">
            <div class="label">Email</div>
            <div class="value">{{ $utilisateur->emailUser }}</div>
        </div>
    </div>
</main>

</body>
</html>
