<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Connexion · TransHub</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --primary:      #f59e0b;
            --primary-light:#fbbf24;
            --primary-dark: #ea580c;
            --teal:         #14b8a6;
            --navy:         #0B1F3A;
            --navy-2:       #132b52;
            --bg:           #f4f6fb;
            --card-bg:      rgba(255,255,255,.78);
            --text-main:    #0B1F3A;
            --text-muted:   #64748b;
            --input-bg:     #f8fafc;
            --input-border: #e2e8f0;
        }

        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Outfit', sans-serif;
            min-height: 100vh;
            background: var(--bg);
            color: var(--text-main);
        }

        .login-wrapper { display: flex; min-height: 100vh; width: 100%; }

        /* ═══════════ PANNEAU GAUCHE ═══════════ */
        .left-panel {
            flex: 1.25;
            position: relative;
            display: none;
            overflow: hidden;
            align-items: center;
            justify-content: center;
            background:
                radial-gradient(circle at 15% 20%, rgba(245,158,11,.20), transparent 45%),
                radial-gradient(circle at 85% 75%, rgba(20,184,166,.18), transparent 45%),
                linear-gradient(160deg, #060f22 0%, var(--navy) 45%, var(--navy-2) 100%);
        }
        @media (min-width: 992px) { .left-panel { display: flex; } }

        /* Formes douces, statiques — pas d'animation */
        .blob { position: absolute; border-radius: 50%; filter: blur(60px); opacity: .5; }
        .blob-1 { width: 320px; height: 320px; background: rgba(245,158,11,.32); top: -60px; left: -60px; }
        .blob-2 { width: 260px; height: 260px; background: rgba(20,184,166,.28); bottom: -40px; right: -40px; }
        .blob-3 { width: 180px; height: 180px; background: rgba(234,88,12,.22); top: 45%; right: 12%; }

        .grid-overlay {
            position: absolute; inset: 0;
            background-image:
                linear-gradient(rgba(255,255,255,.035) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,.035) 1px, transparent 1px);
            background-size: 42px 42px;
            mask-image: radial-gradient(ellipse at center, #000 40%, transparent 78%);
        }

        .route-track {
            position: absolute;
            bottom: 15%;
            left: 8%;
            right: 8%;
            height: 2px;
            background-image: repeating-linear-gradient(90deg, rgba(255,255,255,.24) 0 10px, transparent 10px 20px);
            z-index: 3;
        }
        .route-track .stop {
            position: absolute;
            top: -10px;
            width: 22px; height: 22px;
            border-radius: 7px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-size: .75rem;
        }
        .route-track .stop.s1 { left: 0; }
        .route-track .stop.s2 { left: calc(50% - 11px); }
        .route-track .stop.s3 { right: 0; }

        .left-content {
            position: relative;
            z-index: 10;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            text-align: left;
            padding: 56px 56px;
            max-width: 520px;
        }

        .brand-badge {
            display: inline-flex; align-items: center; gap: 10px;
            background: rgba(255,255,255,.08);
            border: 1px solid rgba(255,255,255,.14);
            padding: 8px 16px 8px 10px;
            border-radius: 999px;
            margin-bottom: 34px;
        }
        .brand-badge .icon-wrap {
            width: 30px; height: 30px; border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-size: 1rem;
        }
        .brand-badge span { color: #fff; font-weight: 700; font-size: 1.05rem; letter-spacing: -.3px; }
        .brand-badge span em { color: var(--primary); font-style: normal; }

        .left-content h2 {
            font-size: 2.3rem;
            font-weight: 800;
            color: #fff;
            line-height: 1.28;
            margin-bottom: 16px;
            letter-spacing: -.5px;
        }
        .left-content h2 em { font-style: normal; color: var(--primary); }

        .left-content p {
            font-size: 1rem;
            color: rgba(255,255,255,.62);
            max-width: 400px;
            font-weight: 300;
            line-height: 1.85;
            margin-bottom: 40px;
        }

        .feature-list { display: flex; flex-direction: column; gap: 14px; }
        .feature-item { display: flex; align-items: center; gap: 12px; color: rgba(255,255,255,.85); font-size: .92rem; }
        .feature-item .dot {
            width: 34px; height: 34px; border-radius: 10px;
            background: rgba(255,255,255,.07);
            border: 1px solid rgba(255,255,255,.12);
            display: flex; align-items: center; justify-content: center;
            color: var(--primary); font-size: 1rem; flex-shrink: 0;
        }

        /* ═══════════ PANNEAU DROIT ═══════════ */
        .right-panel {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
            background:
                radial-gradient(circle at 20% 15%, rgba(245,158,11,.06), transparent 40%),
                radial-gradient(circle at 85% 90%, rgba(20,184,166,.05), transparent 40%),
                var(--bg);
        }

        .dot-pattern {
            position: absolute; inset: 0; z-index: 0;
            background-image: radial-gradient(rgba(11,31,58,.06) 1.4px, transparent 1.4px);
            background-size: 26px 26px;
            mask-image: radial-gradient(ellipse at center, #000 0%, transparent 72%);
        }

        .login-box { width: 100%; max-width: 430px; position: relative; z-index: 2; }

        .glass-card {
            background: var(--card-bg);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255,255,255,.6);
            border-radius: 24px;
            box-shadow: 0 20px 50px -18px rgba(11,31,58,.16);
            padding: 44px 38px;
        }

        .card-header-block { text-align: center; margin-bottom: 30px; }
        .card-logo-badge {
            width: 52px; height: 52px; border-radius: 15px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-size: 1.4rem; margin: 0 auto 16px;
        }
        .card-header-block .brand-title { font-size: 1.45rem; font-weight: 800; color: var(--text-main); letter-spacing: -.4px; }
        .card-header-block .brand-title em { font-style: normal; color: var(--primary); }
        .card-sub { color: var(--text-muted); font-size: .89rem; margin-top: 5px; }

        .form-group { margin-bottom: 19px; }
        .form-group label {
            display: block; font-size: .74rem; font-weight: 600; text-transform: uppercase;
            letter-spacing: 1.1px; color: var(--text-muted); margin-bottom: 8px;
        }

        .input-wrap { position: relative; }
        .input-wrap .icon-l {
            position: absolute; left: 16px; top: 50%; transform: translateY(-50%);
            color: var(--text-muted); font-size: 1.02rem; pointer-events: none;
        }
        .input-wrap input {
            width: 100%; padding: 13px 46px 13px 44px; background: var(--input-bg);
            border: 1.5px solid var(--input-border); border-radius: 13px; color: var(--text-main);
            font-size: .96rem; font-family: inherit; transition: border-color .15s, box-shadow .15s;
        }
        .input-wrap input::placeholder { color: #a3adbd; }
        .input-wrap input:focus {
            outline: none; background: #fff; border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(245,158,11,.13);
        }
        .input-wrap input:focus ~ .icon-l { color: var(--primary); }

        .pwd-toggle {
            position: absolute; right: 16px; top: 50%; transform: translateY(-50%);
            color: var(--text-muted); font-size: 1.02rem; cursor: pointer;
        }
        .pwd-toggle:hover { color: var(--text-main); }

        .remember-row { display: flex; align-items: center; gap: 8px; margin-bottom: 24px; cursor: pointer; user-select: none; }
        .remember-row input { accent-color: var(--primary); width: 16px; height: 16px; }
        .remember-row span { font-size: .87rem; color: var(--text-muted); }

        .alert-box { display: flex; align-items: flex-start; gap: 10px; border-radius: 12px; padding: 12px 15px; font-size: .85rem; margin-bottom: 20px; }
        .alert-error { background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; }
        .alert-success { background: #f0fdf4; border: 1px solid #bbf7d0; color: #15803d; }
        .alert-box i { margin-top: 1px; }

        .btn-submit {
            width: 100%; padding: 14px;
            background: linear-gradient(135deg, var(--primary-light), var(--primary-dark));
            border: none; border-radius: 13px;
            color: #fff; font-size: 1rem; font-weight: 600;
            font-family: inherit; cursor: pointer;
            display: flex; align-items: center; justify-content: center; gap: 8px;
            box-shadow: 0 10px 22px -10px rgba(234,88,12,.55);
            transition: box-shadow .15s ease;
        }
        .btn-submit:hover { box-shadow: 0 12px 26px -10px rgba(234,88,12,.68); }

        .card-footer-text {
            display: flex; align-items: center; justify-content: center;
            gap: 7px; margin-top: 28px; color: var(--text-muted); font-size: .79rem; opacity: .7;
        }
        .card-footer-text i { color: var(--teal); }

        input:-webkit-autofill,
        input:-webkit-autofill:hover,
        input:-webkit-autofill:focus {
            -webkit-box-shadow: 0 0 0 30px #f8fafc inset !important;
            -webkit-text-fill-color: var(--text-main) !important;
        }
    </style>
</head>
<body>

<div class="login-wrapper">

    <!-- ═══ PANNEAU GAUCHE ═══ -->
    <div class="left-panel">
        <div class="blob blob-1"></div>
        <div class="blob blob-2"></div>
        <div class="blob blob-3"></div>
        <div class="grid-overlay"></div>
        <div class="route-track">
            <div class="stop s1"><i class="bi bi-geo-alt-fill"></i></div>
            <div class="stop s2"><i class="bi bi-bus-front-fill"></i></div>
            <div class="stop s3"><i class="bi bi-geo-alt-fill"></i></div>
        </div>

        <div class="left-content">
            <div class="brand-badge">
                <span class="icon-wrap"><i class="bi bi-signpost-split-fill"></i></span>
                <span>Trans<em>Hub</em></span>
            </div>

            <h2>Pilotez vos <em>compagnies</em><br>de transport en un clin d'œil</h2>
            <p>Un espace unique pour administrer plusieurs compagnies : trajets, billets, colis et caisses, en temps réel.</p>

            <div class="feature-list">
                <div class="feature-item"><span class="dot"><i class="bi bi-shield-lock-fill"></i></span> Connexion sécurisée et chiffrée</div>
                <div class="feature-item"><span class="dot"><i class="bi bi-diagram-3-fill"></i></span> Gestion multi-compagnie centralisée</div>
                <div class="feature-item"><span class="dot"><i class="bi bi-lightning-charge-fill"></i></span> Suivi en temps réel des opérations</div>
            </div>
        </div>
    </div>

    <!-- ═══ PANNEAU DROIT — Formulaire ═══ -->
    <div class="right-panel">
        <div class="dot-pattern"></div>

        <div class="login-box">
            <div class="glass-card">

                <div class="card-header-block">
                    <div class="card-logo-badge"><i class="bi bi-signpost-split-fill"></i></div>
                    <div class="brand-title">Trans<em>Hub</em></div>
                    <div class="card-sub">Connectez-vous à votre espace</div>
                </div>

                @if ($errors->any())
                    <div class="alert-box alert-error">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                @if (session('status'))
                    <div class="alert-box alert-success">
                        <i class="bi bi-check-circle-fill"></i>
                        <span>{{ session('status') }}</span>
                    </div>
                @endif

                <form method="POST" action="{{ route('login.store') }}" id="formLogin">
                    @csrf

                    <div class="form-group">
                        <label>Adresse e-mail</label>
                        <div class="input-wrap">
                            <input type="email" name="emailUser" value="{{ old('emailUser') }}" placeholder="exemple@transhub.com" required autofocus>
                            <i class="bi bi-envelope-fill icon-l"></i>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Mot de passe</label>
                        <div class="input-wrap">
                            <input type="password" name="motPasse" id="pwd" placeholder="••••••••" required>
                            <i class="bi bi-lock-fill icon-l"></i>
                            <i class="bi bi-eye pwd-toggle" id="togglePwd"></i>
                        </div>
                    </div>

                    <label class="remember-row">
                        <input type="checkbox" name="remember">
                        <span>Se souvenir de moi</span>
                    </label>

                    <button type="submit" class="btn-submit" id="btnLogin">
                        <span class="spinner-border spinner-border-sm d-none" id="loginSpinner" role="status" aria-hidden="true"></span>
                        <i class="bi bi-box-arrow-in-right" id="loginIcon"></i>
                        <span id="loginLabel">Se connecter</span>
                    </button>
                </form>

                <div class="card-footer-text">
                    <i class="bi bi-shield-check"></i>
                    <span>Connexion sécurisée &bull; &copy; {{ date('Y') }} TransHub</span>
                </div>

            </div>
        </div>
    </div>

</div>

<script>
    document.getElementById('togglePwd').addEventListener('click', function () {
        const pwd = document.getElementById('pwd');
        const isText = pwd.type === 'text';
        pwd.type = isText ? 'password' : 'text';
        this.classList.toggle('bi-eye', isText);
        this.classList.toggle('bi-eye-slash', !isText);
    });

    document.getElementById('formLogin').addEventListener('submit', function () {
        const btn = document.getElementById('btnLogin');
        document.getElementById('loginSpinner').classList.remove('d-none');
        document.getElementById('loginIcon').classList.add('d-none');
        document.getElementById('loginLabel').textContent = 'Connexion en cours...';
        btn.disabled = true;
    });
</script>

</body>
</html>
