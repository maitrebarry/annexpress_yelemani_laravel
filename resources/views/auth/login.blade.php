<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Connexion · TransHub</title>
    <meta name="description" content="Accédez à votre espace de gestion TransHub.">


    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --primary:      #0f3b5e;
            --primary-light:#1d6fa5;
            --accent:       #e67e22;
            --accent-light: #f59e0b;
            --bg:           #f4f6fb;
            --card-bg:      #ffffff;
            --border:       #e2e8f0;
            --text:         #1e293b;
            --muted:        #64748b;
            --error:        #dc2626;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            min-height: 100vh;
            background: #f4f6fb;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
            color: var(--text);
        }

        /* ── Logo ───────────────────────────────────────── */
        .logo-wrap {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 24px;
        }
        .logo-wrap img {
            height: 56px;
            width: auto;
            object-fit: contain;
        }

        /* ── Card ───────────────────────────────────────── */
        .card {
            background: var(--card-bg);
            border-radius: 12px;
            padding: 36px 40px 32px;
            width: 100%;
            max-width: 420px;
            border-top: 3.5px solid var(--accent);
            box-shadow: 0 2px 16px rgba(0,0,0,.07);
        }

        .card-title {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 4px;
            letter-spacing: -.3px;
            color: var(--primary);
        }
        .card-sub {
            font-size: 13.5px;
            color: var(--muted);
            margin-bottom: 28px;
        }

        /* ── Form ───────────────────────────────────────── */
        label {
            display: block;
            font-size: 13px;
            font-weight: 500;
            margin-bottom: 6px;
            color: var(--text);
        }

        .input-wrap { margin-bottom: 20px; }

        input[type="email"],
        input[type="password"],
        input[type="text"] {
            width: 100%;
            height: 42px;
            padding: 0 12px;
            border: 1.5px solid var(--border);
            border-radius: 6px;
            font-size: 14px;
            font-family: inherit;
            color: var(--text);
            background: #fff;
            outline: none;
            transition: border-color .18s, box-shadow .18s;
        }
        input:focus {
            border-color: var(--primary-light);
            box-shadow: 0 0 0 3px rgba(29,111,165,.2);
        }
        input.is-error { border-color: var(--error); }

        .error-msg {
            font-size: 12.5px;
            color: var(--error);
            margin-top: 5px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        /* ── Password toggle ───────────────────────────── */
        .pw-wrap { position: relative; }
        .pw-wrap input { padding-right: 40px; }
        .pw-toggle {
            position: absolute;
            right: 11px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            padding: 0;
            color: var(--muted);
            display: flex;
            align-items: center;
        }
        .pw-toggle:hover { color: var(--text); }
        .pw-toggle svg { width: 18px; height: 18px; }

        /* ── Remember / Forgot ─────────────────────────── */
        .row-remember {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 22px;
            gap: 8px;
        }
        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 7px;
            font-size: 13px;
            color: var(--muted);
            cursor: pointer;
            user-select: none;
        }
        .checkbox-label input[type="checkbox"] {
            width: 15px;
            height: 15px;
            accent-color: var(--primary);
            cursor: pointer;
            flex-shrink: 0;
        }
        .forgot-link {
            font-size: 13px;
            color: var(--primary-light);
            text-decoration: none;
            font-weight: 500;
        }
        .forgot-link:hover { text-decoration: underline; }

        /* ── Button ─────────────────────────────────────── */
        .btn-submit {
            width: 100%;
            height: 44px;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: #fff;
            border: none;
            border-radius: 6px;
            font-size: 14.5px;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: opacity .18s, transform .1s;
        }
        .btn-submit:hover  { opacity: .9; }
        .btn-submit:active { transform: scale(.985); }
        .btn-submit:disabled { opacity: .7; cursor: not-allowed; }
        .spinner {
            width: 16px; height: 16px;
            border: 2px solid rgba(255,255,255,.35);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin .7s linear infinite;
            display: none;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* ── Footer ─────────────────────────────────────── */
        .page-footer {
            margin-top: 28px;
            text-align: center;
        }
        .badge-row {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 16px;
            flex-wrap: wrap;
            margin-bottom: 10px;
        }
        .badge-item {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 12px;
            color: var(--muted);
        }
        .badge-item svg { width: 13px; height: 13px; stroke: var(--primary-light); fill: none; }
        .copyright {
            font-size: 11.5px;
            color: #94a3b8;
        }

        @media (max-width: 480px) {
            .card { padding: 28px 20px 24px; }
        }
    </style>
</head>
<body>

    {{-- Logo --}}
    <div class="logo-wrap">
        <a href="{{ url('/') }}">
            <img src="{{ asset('images/logos/transhub_logo.png') }}" alt="TransHub">
        </a>
    </div>

    {{-- Card --}}
    <div class="card">
        <div class="card-title">Connexion</div>
        <div class="card-sub">Accédez à votre espace de gestion.</div>

        @if (session('status'))
            <div style="background:#d1fae5;border:1px solid #6ee7b7;color:#065f46;padding:10px 14px;border-radius:6px;font-size:13px;margin-bottom:20px;">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.store') }}" id="formLogin" novalidate>
            @csrf

            {{-- Email --}}
            <div class="input-wrap">
                <label for="emailUser">Email</label>
                <input
                    type="email"
                    id="emailUser"
                    name="emailUser"
                    value="{{ old('emailUser') }}"
                    autocomplete="email"
                    autofocus
                    class="{{ $errors->has('emailUser') ? 'is-error' : '' }}"
                >
                @error('emailUser')
                    <div class="error-msg">
                        <svg viewBox="0 0 20 20" fill="currentColor" style="width:13px;height:13px;"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                        {{ $message }}
                    </div>
                @enderror
            </div>

            {{-- Mot de passe --}}
            <div class="input-wrap">
                <label for="motPasse">Mot de passe</label>
                <div class="pw-wrap">
                    <input
                        type="password"
                        id="motPasse"
                        name="motPasse"
                        autocomplete="current-password"
                        class="{{ $errors->has('motPasse') ? 'is-error' : '' }}"
                    >
                    <button type="button" class="pw-toggle" id="pwToggle" aria-label="Afficher/Masquer">
                        <svg id="eyeOff" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19M1 1l22 22"/>
                        </svg>
                        <svg id="eyeOn" style="display:none;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12S5 4 12 4s11 8 11 8-4 8-11 8S1 12 1 12z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                    </button>
                </div>
                @error('motPasse')
                    <div class="error-msg">
                        <svg viewBox="0 0 20 20" fill="currentColor" style="width:13px;height:13px;"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                        {{ $message }}
                    </div>
                @enderror
            </div>

            {{-- Se souvenir + mot de passe oublié --}}
            <div class="row-remember">
                <label class="checkbox-label">
                    <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                    Se souvenir de moi
                </label>
                <a href="#" class="forgot-link">Mot de passe oublié ?</a>
            </div>

            {{-- Bouton --}}
            <button type="submit" class="btn-submit" id="btnSubmit">
                <span class="spinner" id="loginSpinner"></span>
                <span id="loginLabel">Se connecter</span>
            </button>
        </form>
    </div>

    {{-- Footer --}}
    <footer class="page-footer">
        <div class="copyright">&copy; {{ date('Y') }} TransHub &mdash; Plateforme de gestion du transport</div>
    </footer>

    <script>
        // Toggle mot de passe
        const pwToggle = document.getElementById('pwToggle');
        const pwInput  = document.getElementById('motPasse');
        const eyeOff   = document.getElementById('eyeOff');
        const eyeOn    = document.getElementById('eyeOn');

        pwToggle.addEventListener('click', function () {
            const show = pwInput.type === 'password';
            pwInput.type = show ? 'text' : 'password';
            eyeOff.style.display = show ? 'none' : '';
            eyeOn.style.display  = show ? '' : 'none';
        });

        // Spinner au submit
        document.getElementById('formLogin').addEventListener('submit', function () {
            const btn = document.getElementById('btnSubmit');
            btn.disabled = true;
            document.getElementById('loginSpinner').style.display = 'block';
            document.getElementById('loginLabel').textContent = 'Connexion…';
        });
    </script>

</body>
</html>
