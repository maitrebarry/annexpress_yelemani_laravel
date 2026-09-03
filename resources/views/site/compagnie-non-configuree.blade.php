<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script>
        // Applique le mode sombre / la couleur de theme AVANT le premier rendu (pas de
        // flash de theme par defaut) - meme mecanique que resources/views/admin/partials/header.blade.php.
        (function () {
            try {
                if (localStorage.getItem('tgSiteDarkMode') === 'true') {
                    document.documentElement.classList.add('dark-mode');
                }
                var theme = localStorage.getItem('tgSiteTheme');
                if (theme && theme !== 'default') {
                    document.documentElement.setAttribute('data-theme', theme);
                }
            } catch (e) {}
        })();
    </script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Site en cours de configuration · TransGest</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            min-height: 100vh;
            margin: 0;
            background: #eef2f8;
            color: #1e293b;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .card {
            background: #fff;
            border-radius: 12px;
            border-top: 3.5px solid #e67e22;
            box-shadow: 0 2px 16px rgba(0,0,0,.07);
            padding: 40px;
            max-width: 480px;
            text-align: center;
        }
        .icon {
            font-size: 40px;
            margin-bottom: 16px;
        }
        h1 {
            font-size: 20px;
            color: #0f3b5e;
            margin: 0 0 12px;
        }
        p {
            color: #64748b;
            font-size: 14px;
            line-height: 1.6;
            margin: 0 0 24px;
        }
        a.btn {
            display: inline-block;
            background: #0f3b5e;
            color: #fff;
            text-decoration: none;
            padding: 10px 24px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">🚧</div>
        <h1>Site en cours de configuration</h1>
        <p>
            Ce site n'est pas encore prêt : aucune compagnie n'a été créée.
            Connectez-vous à l'espace d'administration pour créer votre compagnie
            (menu Configuration → Compagnie).
        </p>
        <a class="btn" href="{{ route('login') }}">Accéder à l'administration</a>
    </div>
</body>
</html>
