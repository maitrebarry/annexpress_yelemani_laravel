<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Espace Admin · TransHub</title>
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
        main { padding: 32px; max-width: 1100px; margin: 0 auto; }
        h1 { font-size: 1.5rem; margin-bottom: 4px; }
        .sub { color: var(--text-muted); margin-bottom: 28px; }
        .badge-role {
            display: inline-block; background: var(--primary); color: #fff;
            font-size: .72rem; font-weight: 600; padding: 3px 10px; border-radius: 999px;
            letter-spacing: .5px; text-transform: uppercase;
        }
        table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 14px; overflow: hidden; box-shadow: 0 8px 24px -12px rgba(0,0,0,.1); }
        th, td { text-align: left; padding: 14px 18px; font-size: .92rem; }
        th { background: #f1f5f9; color: var(--text-muted); font-weight: 600; text-transform: uppercase; font-size: .72rem; letter-spacing: .6px; }
        tr:not(:last-child) td { border-bottom: 1px solid #eef1f5; }
        .empty { padding: 40px; text-align: center; color: var(--text-muted); }
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
    <div class="brand">Trans<span>Hub</span> <span style="font-weight:400;font-size:.8rem;color:rgba(255,255,255,.6);">Admin</span></div>
    <div style="display:flex; align-items:center; gap:16px;">
        <div class="who">{{ auth('staff')->user()->utilisateurs }} · <span class="badge-role">{{ auth('staff')->user()->droit }}</span></div>
        <form class="logout-form" method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn-logout"><i class="bi bi-box-arrow-right"></i> Déconnexion</button>
        </form>
    </div>
</header>

<main>
    <h1>Toutes les compagnies</h1>
    <p class="sub">Vue globale multi-compagnie — accès réservé au super administrateur.</p>

    @if ($compagnies->isEmpty())
        <div class="empty">Aucune compagnie enregistrée pour le moment.</div>
    @else
        <table>
            <thead>
                <tr>
                    <th>Compagnie</th>
                    <th>Libellé</th>
                    <th>Agences</th>
                    <th>Utilisateurs</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($compagnies as $compagnie)
                    <tr>
                        <td><strong>{{ $compagnie->nom_compagnie }}</strong></td>
                        <td>{{ $compagnie->libele }}</td>
                        <td>{{ $compagnie->agences_count }}</td>
                        <td>{{ $compagnie->utilisateurs_count }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</main>

</body>
</html>
