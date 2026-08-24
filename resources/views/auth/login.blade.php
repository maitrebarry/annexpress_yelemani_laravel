<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Connexion · TransHub</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
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
            --text-main:    #0B1F3A;
            --text-muted:   #64748b;
        }

        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Outfit', sans-serif;
            min-height: 100vh;
            background: var(--bg);
            color: var(--text-main);
        }

        .login-wrapper { display: flex; min-height: 100vh; width: 100%; }

        /* ═══════════ PANNEAU GAUCHE — Formulaire ═══════════ */
        .form-panel {
            flex: 1;
            min-width: 340px;
            max-width: 560px;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 24px;
            background: linear-gradient(165deg, #081226 0%, var(--navy) 55%, var(--navy-2) 100%);
        }
        .form-panel .blob { position: absolute; border-radius: 50%; filter: blur(70px); opacity: .45; pointer-events: none; }
        .form-panel .blob-1 { width: 280px; height: 280px; background: rgba(245,158,11,.28); top: -70px; left: -70px; }
        .form-panel .blob-2 { width: 240px; height: 240px; background: rgba(20,184,166,.22); bottom: -60px; right: -60px; }

        .login-box { width: 100%; max-width: 400px; position: relative; z-index: 2; }

        .glass-card {
            background: rgba(255,255,255,.06);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            border: 1px solid rgba(255,255,255,.14);
            border-radius: 22px;
            box-shadow: 0 30px 60px -24px rgba(0,0,0,.55);
            padding: 42px 36px;
            animation: cardIn .6s cubic-bezier(.22,1,.36,1) both;
        }
        @keyframes cardIn { from { transform: translateY(18px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }

        .card-header-block { text-align: center; margin-bottom: 30px; }
        .card-logo-badge {
            width: 66px; height: 66px; border-radius: 50%;
            border: 2px solid rgba(255,255,255,.35);
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-size: 1.6rem; margin: 0 auto 18px;
            background: rgba(255,255,255,.05);
            animation: badgePop .6s cubic-bezier(.34,1.56,.64,1) both;
        }
        @keyframes badgePop { from { transform: scale(.5); opacity: 0; } to { transform: scale(1); opacity: 1; } }
        .card-header-block h1 { font-size: 1.6rem; font-weight: 800; color: #fff; letter-spacing: -.3px; }
        .card-sub { color: rgba(255,255,255,.55); font-size: .86rem; margin-top: 6px; }

        .form-group { margin-bottom: 18px; }
        .form-group label {
            display: block; font-size: .78rem; font-weight: 600;
            color: rgba(255,255,255,.75); margin-bottom: 8px;
        }

        .input-wrap { position: relative; }
        .input-wrap .icon-l {
            position: absolute; left: 15px; top: 50%; transform: translateY(-50%);
            color: rgba(255,255,255,.4); font-size: .98rem; pointer-events: none;
        }
        .input-wrap input {
            width: 100%; padding: 13px 44px 13px 42px;
            background: rgba(255,255,255,.95);
            border: 1.5px solid transparent; border-radius: 12px; color: var(--text-main);
            font-size: .95rem; font-family: inherit; transition: border-color .15s, box-shadow .15s;
        }
        .input-wrap input::placeholder { color: #a3adbd; }
        .input-wrap input:focus {
            outline: none; background: #fff; border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(245,158,11,.22);
        }
        .input-wrap input:focus ~ .icon-l { color: var(--primary); }

        .pwd-toggle {
            position: absolute; right: 15px; top: 50%; transform: translateY(-50%);
            color: var(--text-muted); font-size: .98rem; cursor: pointer;
        }
        .pwd-toggle:hover { color: var(--text-main); }

        .row-between { display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; }
        .remember-row { display: flex; align-items: center; gap: 7px; cursor: pointer; user-select: none; }
        .remember-row input { accent-color: var(--primary); width: 15px; height: 15px; }
        .remember-row span { font-size: .84rem; color: rgba(255,255,255,.65); }
        .forgot-link { font-size: .84rem; color: var(--primary-light); text-decoration: underline; }
        .forgot-link:hover { color: var(--primary); }

        .alert-box { display: flex; align-items: flex-start; gap: 10px; border-radius: 12px; padding: 12px 15px; font-size: .85rem; margin-bottom: 20px; }
        .alert-error { background: rgba(239,68,68,.15); border: 1px solid rgba(239,68,68,.35); color: #fecaca; }
        .alert-success { background: rgba(34,197,94,.15); border: 1px solid rgba(34,197,94,.35); color: #bbf7d0; }
        .alert-box i { margin-top: 1px; }

        .btn-submit {
            width: 100%; padding: 14px;
            background: #fff;
            border: none; border-radius: 13px;
            color: var(--navy); font-size: 1rem; font-weight: 700;
            font-family: inherit; cursor: pointer;
            display: flex; align-items: center; justify-content: center; gap: 8px;
            box-shadow: 0 14px 28px -14px rgba(0,0,0,.5);
            transition: transform .15s ease, box-shadow .15s ease;
        }
        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 18px 32px -14px rgba(0,0,0,.6); }
        .btn-submit i { color: var(--primary-dark); }

        .card-footer-text {
            display: flex; align-items: center; justify-content: center;
            gap: 7px; margin-top: 26px; color: rgba(255,255,255,.4); font-size: .78rem;
        }

        /* ═══════════ PANNEAU DROIT — Marque + fonctionnalités ═══════════ */
        .brand-panel {
            flex: 1.4;
            display: none;
            position: relative;
            align-items: center;
            justify-content: center;
            padding: 60px 40px;
            background: var(--bg);
            overflow: hidden;
        }
        @media (min-width: 992px) { .brand-panel { display: flex; } }

        .brand-panel .dot-pattern {
            position: absolute; inset: 0; z-index: 0;
            background-image: radial-gradient(rgba(11,31,58,.07) 1.5px, transparent 1.5px);
            background-size: 26px 26px;
            mask-image: radial-gradient(ellipse at center, #000 0%, transparent 75%);
        }

        .brand-blob { position: absolute; border-radius: 50%; filter: blur(80px); pointer-events: none; z-index: 0; animation: blobFloat 10s ease-in-out infinite; }
        .brand-blob-1 { width: 320px; height: 320px; background: rgba(245,158,11,.18); top: -80px; left: -60px; }
        .brand-blob-2 { width: 280px; height: 280px; background: rgba(20,184,166,.15); bottom: -70px; right: -40px; animation-delay: -3s; }
        .brand-blob-3 { width: 200px; height: 200px; background: rgba(234,88,12,.12); top: 45%; right: 8%; animation-delay: -6s; }
        @keyframes blobFloat { 0%, 100% { transform: translate(0,0) scale(1); } 50% { transform: translate(18px,-14px) scale(1.06); } }

        .brand-content { position: relative; z-index: 2; text-align: center; max-width: 620px; }

        .brand-mark {
            display: flex; align-items: center; justify-content: center; gap: 18px;
            margin-bottom: 22px;
            animation: markIn .7s cubic-bezier(.22,1,.36,1) both;
        }
        @keyframes markIn { from { transform: translateY(-16px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        .brand-mark .icon-badge {
            position: relative;
            width: 84px; height: 84px; border-radius: 26px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-size: 2.4rem;
            box-shadow: 0 20px 40px -16px rgba(234,88,12,.5);
        }
        .brand-mark .icon-badge::after {
            content: ''; position: absolute; inset: -8px; border-radius: 32px;
            border: 2px solid rgba(245,158,11,.35);
            animation: ringPulse 2.4s ease-out infinite;
        }
        @keyframes ringPulse {
            0% { transform: scale(.94); opacity: .9; }
            70% { transform: scale(1.16); opacity: 0; }
            100% { opacity: 0; }
        }
        .brand-mark .wordmark { font-size: 3.4rem; font-weight: 800; color: var(--navy); letter-spacing: -1.5px; line-height: 1; }
        .brand-mark .wordmark em { font-style: normal; color: var(--primary); }

        .brand-tagline {
            font-size: 1.05rem; font-weight: 500; color: var(--text-muted);
            margin-bottom: 46px;
            animation: markIn .7s .1s cubic-bezier(.22,1,.36,1) both;
        }

        .objectif-block {
            text-align: left;
            background: #fff;
            border: 1px solid #e7ebf2;
            border-radius: 18px;
            padding: 26px 30px;
            box-shadow: 0 20px 45px -28px rgba(11,31,58,.25);
            margin-bottom: 44px;
            animation: markIn .7s .2s cubic-bezier(.22,1,.36,1) both;
        }
        .objectif-block .objectif-label {
            display: flex; align-items: center; gap: 8px;
            font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px;
            color: var(--primary-dark); margin-bottom: 12px;
        }
        .objectif-text {
            font-family: 'JetBrains Mono', monospace;
            font-size: 1.05rem; line-height: 1.6; color: var(--navy);
            min-height: 3.4em;
        }
        .objectif-text .cursor {
            display: inline-block; width: 2px; height: 1.05em; background: var(--primary);
            margin-left: 2px; vertical-align: text-bottom;
            animation: blink 1s step-end infinite;
        }
        @keyframes blink { 50% { opacity: 0; } }

        .feature-row { display: flex; justify-content: center; gap: 14px; flex-wrap: wrap; }
        .feature-chip {
            display: flex; align-items: center; gap: 9px;
            background: #fff; border: 1px solid #e7ebf2; border-radius: 999px;
            padding: 10px 18px; font-size: .84rem; font-weight: 600; color: var(--text-main);
            box-shadow: 0 10px 22px -16px rgba(11,31,58,.2);
            animation: markIn .7s cubic-bezier(.22,1,.36,1) both;
            transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease;
        }
        .feature-chip:hover {
            transform: translateY(-4px);
            border-color: rgba(245,158,11,.4);
            box-shadow: 0 16px 30px -16px rgba(234,88,12,.35);
        }
        .feature-chip:nth-child(1) { animation-delay: .3s; }
        .feature-chip:nth-child(2) { animation-delay: .38s; }
        .feature-chip:nth-child(3) { animation-delay: .46s; }
        .feature-chip:nth-child(4) { animation-delay: .54s; }
        .feature-chip i { color: var(--primary-dark); font-size: 1rem; }

        input:-webkit-autofill,
        input:-webkit-autofill:hover,
        input:-webkit-autofill:focus {
            -webkit-box-shadow: 0 0 0 30px #fff inset !important;
            -webkit-text-fill-color: var(--text-main) !important;
        }
    </style>
</head>
<body>

<div class="login-wrapper">

    <!-- ═══ PANNEAU GAUCHE — Marque + fonctionnalités ═══ -->
    <div class="brand-panel">
        <div class="dot-pattern"></div>
        <div class="brand-blob brand-blob-1"></div>
        <div class="brand-blob brand-blob-2"></div>
        <div class="brand-blob brand-blob-3"></div>

        <div class="brand-content">
            <div class="brand-mark">
                <div class="icon-badge"><i class="bi bi-signpost-split-fill"></i></div>
                <div class="wordmark">Trans<em>Hub</em></div>
            </div>
            <p class="brand-tagline">La plateforme qui pilote vos compagnies de transport, du guichet au tableau de bord</p>

            <div class="objectif-block">
                <div class="objectif-label"><i class="bi bi-stars"></i> Ce que TransHub vous permet de faire</div>
                <div class="objectif-text" id="objectifText"><span class="cursor"></span></div>
            </div>

            <div class="feature-row">
                <div class="feature-chip"><i class="bi bi-bus-front-fill"></i> Trajets &amp; flotte</div>
                <div class="feature-chip"><i class="bi bi-ticket-perforated-fill"></i> Billets en ligne</div>
                <div class="feature-chip"><i class="bi bi-box-seam-fill"></i> Suivi de colis</div>
                <div class="feature-chip"><i class="bi bi-cash-coin"></i> Caisse &amp; rapports</div>
            </div>
        </div>
    </div>

    <!-- ═══ PANNEAU DROIT — Formulaire ═══ -->
    <div class="form-panel">
        <div class="blob blob-1"></div>
        <div class="blob blob-2"></div>

        <div class="login-box">
            <div class="glass-card">

                <div class="card-header-block">
                    <div class="card-logo-badge"><i class="bi bi-signpost-split-fill"></i></div>
                    <h1>Connexion</h1>
                    <div class="card-sub">Accédez à votre espace TransHub</div>
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
                        <label>Email</label>
                        <div class="input-wrap">
                            <input type="email" name="emailUser" value="{{ old('emailUser') }}" placeholder="Adresse Email" required autofocus>
                            <i class="bi bi-envelope-fill icon-l"></i>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Mot de passe</label>
                        <div class="input-wrap">
                            <input type="password" name="motPasse" id="pwd" placeholder="Mot de passe" required>
                            <i class="bi bi-lock-fill icon-l"></i>
                            <i class="bi bi-eye pwd-toggle" id="togglePwd"></i>
                        </div>
                    </div>

                    <div class="row-between">
                        <label class="remember-row">
                            <input type="checkbox" name="remember">
                            <span>Se souvenir de moi</span>
                        </label>
                        <a href="#" class="forgot-link" onclick="event.preventDefault()">Mot de passe oublié ?</a>
                    </div>

                    <button type="submit" class="btn-submit" id="btnLogin">
                        <span class="spinner-border spinner-border-sm d-none" id="loginSpinner" role="status" aria-hidden="true"></span>
                        <i class="bi bi-box-arrow-in-right" id="loginIcon"></i>
                        <span id="loginLabel">Se connecter</span>
                    </button>
                </form>

                <div class="card-footer-text">
                    <span>&copy; {{ date('Y') }} TransHub — tous droits réservés</span>
                </div>

            </div>
        </div>
    </div>

</div>

<script>
    // Effet machine à écrire : met en avant les fonctionnalités clés une par une.
    (function () {
        const el = document.getElementById('objectifText');
        if (!el) return;
        const cursor = el.querySelector('.cursor');
        const phrases = [
            'Gérez plusieurs compagnies de transport depuis un seul tableau de bord.',
            'Vendez et validez vos billets en temps réel, au guichet ou en ligne.',
            'Suivez chaque colis, de l’envoi jusqu’à la livraison.',
            'Pilotez caisse, dépenses et rapports automatiquement.',
        ];
        let phraseIndex = 0;
        let charIndex = 0;
        let deleting = false;

        function tick() {
            const current = phrases[phraseIndex];

            if (!deleting) {
                charIndex++;
                el.textContent = current.slice(0, charIndex);
                el.appendChild(cursor);
                if (charIndex === current.length) {
                    deleting = true;
                    return setTimeout(tick, 1800);
                }
                return setTimeout(tick, 38);
            }

            charIndex--;
            el.textContent = current.slice(0, charIndex);
            el.appendChild(cursor);
            if (charIndex === 0) {
                deleting = false;
                phraseIndex = (phraseIndex + 1) % phrases.length;
                return setTimeout(tick, 400);
            }
            return setTimeout(tick, 18);
        }

        tick();
    })();

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
