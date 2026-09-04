<!DOCTYPE html>
<html lang="fr" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion · Sirali</title>
    <meta name="description" content="Accédez à votre espace de gestion Sirali.">
    <meta name="theme-color" content="#0f3b5e">
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --login-primary: #0f3b5e;
            --login-accent: #1d6fa5;
            --login-highlight: #e67e22;
            --login-danger: #dc2626;
            --login-bg: #eef2f8;
            --login-text: #1e293b;
        }

        body {
            min-height: 100vh;
            margin: 0;
            background-color: var(--login-bg);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            color: var(--login-text);
            overflow: hidden;
        }

        .split-layout {
            display: flex;
            width: 100%;
            height: 100vh;
        }

        .image-side {
            flex: 1;
            position: relative;
            background: linear-gradient(180deg, rgba(15,59,94,.55), rgba(15,59,94,.75)), url('{{ asset('assets_site/img/hero-bg.jpg') }}') center/cover no-repeat var(--login-primary);
        }

        /* The SVG wave divider */
        .wave-divider {
            position: absolute;
            top: 0;
            right: -1px; /* Align perfectly with the login side */
            height: 100%;
            width: 150px;
            z-index: 5;
        }

        .wave-divider svg {
            height: 100%;
            width: 100%;
            display: block;
        }

        .login-side {
            flex: 0 0 520px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: var(--login-bg);
            padding: 24px;
            position: relative;
            z-index: 10;
        }

        .login-container {
            width: 100%;
            max-width: 420px;
        }

        .login-card {
            border: 0;
            border-radius: 12px;
            border-top: 3.5px solid var(--login-highlight);
            box-shadow: 0 24px 70px rgba(15, 23, 42, 0.18);
            overflow: hidden;
            width: 100%;
        }

        .login-header {
            background: var(--login-primary);
            color: #fff;
            padding: 28px 34px;
            text-align: center;
        }

        .brand-mark {
            width: 64px;
            height: 64px;
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #fff;
            padding: 6px;
            margin-bottom: 12px;
            overflow: hidden;
        }

        .brand-mark img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .login-header h1 {
            font-size: 24px;
            margin: 0;
            font-weight: 800;
            letter-spacing: .3px;
        }

        .login-header p {
            margin: 4px 0 0;
            font-size: 13px;
            color: rgba(255,255,255,.75);
        }

        .login-body {
            background: #fff;
            padding: 32px 34px;
        }

        .form-label {
            font-weight: 600;
            margin-bottom: 7px;
            font-size: 13.5px;
        }

        .form-control {
            border-radius: 8px;
            border: 1px solid #d7dee9;
            padding: 12px 14px;
            min-height: 46px;
        }

        .form-control:focus {
            border-color: var(--login-accent);
            box-shadow: 0 0 0 0.2rem rgba(29, 111, 165, 0.16);
        }

        .password-field {
            position: relative;
        }

        .password-field .form-control {
            padding-right: 48px;
        }

        .toggle-password {
            position: absolute;
            top: 50%;
            right: 12px;
            transform: translateY(-50%);
            width: 34px;
            height: 34px;
            border: 0;
            border-radius: 8px;
            background: transparent;
            color: #64748b;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .toggle-password:hover,
        .toggle-password:focus {
            color: var(--login-primary);
            background: rgba(29, 111, 165, 0.08);
            outline: none;
        }

        .invalid-feedback {
            display: block;
        }

        .btn-login {
            background: var(--login-primary);
            border-color: var(--login-primary);
            border-radius: 8px;
            min-height: 46px;
            font-weight: 700;
            width: 100%;
            color: #fff;
        }

        .btn-login:hover,
        .btn-login:focus {
            background: #0b2c48;
            border-color: #0b2c48;
            color: #fff;
        }

        .login-link {
            color: var(--login-accent);
            font-weight: 600;
            text-decoration: none;
            font-size: 13px;
        }

        .login-link:hover {
            text-decoration: underline;
        }

        .alert {
            border-radius: 8px;
        }

        .page-footer {
            position: absolute;
            bottom: 18px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 11.5px;
            color: #94a3b8;
        }

        @media (max-width: 992px) {
            .split-layout {
                flex-direction: column;
            }
            .image-side {
                display: none;
            }
            .login-side {
                flex: 1;
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="split-layout">
        <!-- Image Side (Left) -->
        <div class="image-side">
            <!-- Wavy oblique SVG divider -->
            <div class="wave-divider">
                <svg viewBox="0 0 100 1000" preserveAspectRatio="none">
                    <path d="M100,0 L0,0 C40,250 -40,500 60,750 C110,875 0,1000 0,1000 L100,1000 Z" fill="#eef2f8" />
                </svg>
            </div>
        </div>

        <!-- Login Side (Right) -->
        <div class="login-side">
            <main class="login-container">
                <div class="card login-card">
                    <div class="login-header">
                        <div class="brand-mark">
                            <img src="{{ asset('images/logos/transgest_icon.png') }}" alt="Sirali">
                        </div>
                        <h1>Sirali</h1>
                        <p>Accédez à votre espace de gestion</p>
                    </div>

                    <div class="login-body">
                        @if($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-circle"></i> <strong>Échec de la connexion</strong>
                                <ul class="mb-0 mt-2">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                            </div>
                        @endif

                        @if(session('status'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle"></i> {{ session('status') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('login.store') }}" id="loginForm" novalidate>
                            @csrf

                            <div class="mb-3">
                                <label for="emailUser" class="form-label">Email</label>
                                <input type="email"
                                       class="form-control @error('emailUser') is-invalid @enderror"
                                       id="emailUser"
                                       name="emailUser"
                                       value="{{ old('emailUser') }}"
                                       autocomplete="email"
                                       autofocus>
                                @error('emailUser')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="motPasse" class="form-label">Mot de passe</label>
                                <div class="password-field">
                                    <input type="password"
                                           class="form-control @error('motPasse') is-invalid @enderror"
                                           id="motPasse"
                                           name="motPasse"
                                           autocomplete="current-password">
                                    <button type="button"
                                            class="toggle-password"
                                            aria-label="Afficher le mot de passe"
                                            aria-pressed="false"
                                            data-password-toggle="motPasse">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                @error('motPasse')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div class="form-check mb-0">
                                    <input class="form-check-input"
                                           type="checkbox"
                                           name="remember"
                                           id="remember"
                                           {{ old('remember') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="remember">
                                        Se souvenir de moi
                                    </label>
                                </div>
                                <a class="login-link" href="#">Mot de passe oublié ?</a>
                            </div>

                            <button type="submit" class="btn btn-login">
                                <i class="fas fa-right-to-bracket"></i> Se connecter
                            </button>
                        </form>
                    </div>
                </div>
            </main>

            <footer class="page-footer">
                &copy; {{ date('Y') }} Sirali &mdash; Plateforme de gestion du transport
            </footer>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.querySelectorAll('[data-password-toggle]').forEach((button) => {
            button.addEventListener('click', () => {
                const passwordInput = document.getElementById(button.dataset.passwordToggle);
                const icon = button.querySelector('i');
                const isHidden = passwordInput.type === 'password';

                passwordInput.type = isHidden ? 'text' : 'password';
                button.setAttribute('aria-label', isHidden ? 'Masquer le mot de passe' : 'Afficher le mot de passe');
                button.setAttribute('aria-pressed', isHidden ? 'true' : 'false');
                icon.classList.toggle('fa-eye', !isHidden);
                icon.classList.toggle('fa-eye-slash', isHidden);
            });
        });

        document.getElementById('loginForm')?.addEventListener('submit', function () {
            var btn = this.querySelector('button[type="submit"]');
            if (!btn || btn.disabled) return;
            btn.disabled = true;
            btn.insertAdjacentHTML('afterbegin', '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>');
        });
    </script>
</body>
</html>
