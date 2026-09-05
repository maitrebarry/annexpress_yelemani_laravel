{{--
    Port de Projets_licence/app/views/site/partials/nav.view.php — en-tête public
    partagé par toutes les pages du site vitrine (Accueil, Compagnies, Contact...).
    Pas de lien "Espace pro" (connexion staff) sur le site public : retiré à la demande de
    l'utilisateur 2026-08-24 — le staff atteint /login directement par son URL, sans qu'elle
    soit mise en avant aux visiteurs.

    Site dédié à une seule compagnie (2026-08-24) : le branding (logo + nom du header) est
    désormais celui de App\Models\Compagnie::site() sur TOUTES les pages du site (une vue
    peut passer $compagnie explicitement si elle l'a déjà résolue, sinon ce partial le
    résout lui-même) — plus de logo Sirali générique en usage normal. Le deuxième lien de
    nav pointe vers la page trajets de cette compagnie ("Nos trajets") plutôt que vers
    l'ancien catalogue multi-compagnies (route `site.compagnies` retirée de la nav, elle
    redirige maintenant vers cette même page si on y accède directement).

    Refonte 2026-09-02 (maquette fournie par l'utilisateur) : en-tête à deux niveaux
    (bandeau de coordonnées + navigation), dans l'esprit d'un vrai site de compagnie de
    transport. Le site restant multi-compagnie (une instance = une compagnie, mais le même
    code sert n'importe laquelle), rien de propre à une compagnie précise n'est codé en dur
    ici : logo, nom, slogan, téléphone/email/réseaux sociaux viennent tous de $navCompagnie
    (nouveaux champs facultatifs telephone/email/adresse/facebook/instagram/whatsapp sur la
    table compagnie) et chaque bloc s'efface simplement si l'info n'est pas renseignée.
--}}
@php
    $navCompagnie = $compagnie ?? \App\Models\Compagnie::site();
@endphp
<div class="topbar">
    <div class="container topbar-inner">
        <div class="topbar-left">
            <span><i class="fas fa-location-dot"></i> Via {{ $navCompagnie->nom_compagnie }}</span>
            @if ($navCompagnie->slogant)
                <span class="d-none-mobile"><i class="fas fa-shield-halved"></i> {{ $navCompagnie->slogant }}</span>
            @endif
        </div>
        <div class="topbar-right">
            @if ($navCompagnie->telephone)
                <a href="tel:{{ preg_replace('/\s+/', '', $navCompagnie->telephone) }}"><i class="fas fa-phone"></i> {{ $navCompagnie->telephone }}</a>
            @endif
            @if ($navCompagnie->email)
                <a href="mailto:{{ $navCompagnie->email }}"><i class="fas fa-envelope"></i> {{ $navCompagnie->email }}</a>
            @endif
            <span class="topbar-socials">
                @if ($navCompagnie->facebook)
                    <a href="{{ $navCompagnie->facebook }}" target="_blank" rel="noopener" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                @endif
                @if ($navCompagnie->instagram)
                    <a href="{{ $navCompagnie->instagram }}" target="_blank" rel="noopener" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                @endif
                @if ($navCompagnie->whatsapp)
                    <a href="https://wa.me/{{ preg_replace('/\D+/', '', $navCompagnie->whatsapp) }}" target="_blank" rel="noopener" aria-label="WhatsApp"><i class="fab fa-whatsapp"></i></a>
                @endif
            </span>
            <span class="topbar-lang"><i class="fas fa-globe"></i> Français <i class="fas fa-chevron-down" style="font-size:.6rem;"></i></span>

            <div class="theme-picker" id="themePicker">
                <button type="button" class="theme-toggle-btn" id="themePickerToggle" title="Couleur du thème" aria-label="Couleur du thème">
                    <i class="fas fa-palette"></i>
                </button>
                <div class="theme-picker-menu" id="themePickerMenu" hidden>
                    <div class="theme-picker-swatches">
                        <button type="button" data-theme="default" style="background:#0f3b5e;" title="Marine (défaut)"></button>
                        <button type="button" data-theme="orange" style="background:#ea580c;" title="Orange"></button>
                        <button type="button" data-theme="green" style="background:#10b981;" title="Vert"></button>
                        <button type="button" data-theme="red" style="background:#ef4444;" title="Rouge"></button>
                        <button type="button" data-theme="indigo" style="background:#6366f1;" title="Indigo"></button>
                    </div>
                </div>
            </div>
            <button type="button" class="theme-toggle-btn" id="darkModeToggle" title="Mode sombre" aria-label="Mode sombre">
                <i class="fas fa-moon"></i>
            </button>
        </div>
    </div>
</div>

<header class="header">
    <div class="container header-inner">
        <a href="{{ route('site.home') }}" class="logo">
            @if ($navCompagnie->logo)
                <img src="{{ asset('images/logos/'.$navCompagnie->logo) }}" alt="{{ $navCompagnie->nom_compagnie }}">
            @else
                <span class="logo-text">{{ $navCompagnie->nom_compagnie }}</span>
            @endif
        </a>

        <div class="nav" id="mainNav">
            <a href="{{ route('site.home') }}" class="nav-link{{ request()->routeIs('site.home') ? ' active' : '' }}">Accueil</a>
            <a href="{{ route('site.compagnie.trajets', $navCompagnie) }}" class="nav-link{{ request()->routeIs('site.compagnie.trajets') ? ' active' : '' }}">Nos trajets</a>
            <a href="{{ route('site.suivi-colis') }}" class="nav-link{{ request()->routeIs('site.suivi-colis') ? ' active' : '' }}">Suivi de colis</a>
            <a href="{{ route('site.services') }}" class="nav-link{{ request()->routeIs('site.services') ? ' active' : '' }}">Services</a>
            <a href="{{ route('site.actualites') }}" class="nav-link{{ request()->routeIs('site.actualites*') ? ' active' : '' }}">Actualités</a>
            <a href="{{ route('site.contact') }}" class="nav-link{{ request()->routeIs('site.contact') ? ' active' : '' }}">Contact</a>
        </div>

        <div class="header-actions">
            <a href="{{ route('site.mon-compte') }}" class="btn-account"><i class="fas fa-user"></i> Mon compte</a>
            <a href="{{ route('site.compagnie.trajets', $navCompagnie) }}" class="btn btn-secondary btn-cta"><i class="fas fa-ticket"></i> Réserver un billet</a>
        </div>

        <button id="menuToggle" aria-label="Menu">
            <i class="fas fa-bars"></i>
        </button>
    </div>
</header>

<div id="mobileNav">
    <button id="closeMenu"><i class="fas fa-times"></i></button>
    <a href="{{ route('site.home') }}" class="nav-link{{ request()->routeIs('site.home') ? ' active' : '' }}">Accueil</a>
    <a href="{{ route('site.compagnie.trajets', $navCompagnie) }}" class="nav-link{{ request()->routeIs('site.compagnie.trajets') ? ' active' : '' }}">Nos trajets</a>
    <a href="{{ route('site.suivi-colis') }}" class="nav-link{{ request()->routeIs('site.suivi-colis') ? ' active' : '' }}">Suivi de colis</a>
    <a href="{{ route('site.services') }}" class="nav-link{{ request()->routeIs('site.services') ? ' active' : '' }}">Services</a>
    <a href="{{ route('site.actualites') }}" class="nav-link{{ request()->routeIs('site.actualites*') ? ' active' : '' }}">Actualités</a>
    <a href="{{ route('site.contact') }}" class="nav-link{{ request()->routeIs('site.contact') ? ' active' : '' }}">Contact</a>
    <a href="{{ route('site.compagnie.trajets', $navCompagnie) }}" class="btn btn-secondary mt-3 justify-content-center"><i class="fas fa-ticket"></i> Réserver un billet</a>
</div>
<div id="overlay"></div>

<style>
    /* ===== BANDEAU DE COORDONNÉES ===== */
    .topbar {
        background: var(--primary-dark);
        color: rgba(255, 255, 255, .85);
        font-size: .78rem;
    }
    .topbar-inner {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 9px 24px;
    }
    .topbar-left, .topbar-right { display: flex; align-items: center; gap: 22px; }
    .topbar-left span, .topbar-right a, .topbar-lang {
        display: inline-flex; align-items: center; gap: 6px;
        color: rgba(255, 255, 255, .85); text-decoration: none;
    }
    .topbar-right a:hover { color: white; }
    .topbar-left i, .topbar-right i, .topbar-lang i { font-size: .75rem; opacity: .8; }
    .topbar-socials { display: flex; align-items: center; gap: 12px; }
    .topbar-socials a { font-size: .82rem; }

    /* ===== EN-TÊTE PRINCIPALE ===== */
    .header {
        position: sticky; top: 0; z-index: 1000;
        background: white;
        box-shadow: 0 2px 10px rgba(0, 0, 0, .06);
    }
    .header-inner { display: flex; align-items: center; justify-content: space-between; gap: 24px; padding: 14px 24px; }
    .logo { display: flex; align-items: center; text-decoration: none; }
    .logo img { height: 50px; width: auto; object-fit: contain; }
    .logo-text { font-weight: 800; color: var(--primary); font-size: 1.25rem; }

    .nav { display: flex; gap: 28px; align-items: center; flex: 1; justify-content: center; }
    .nav-link {
        text-decoration: none; color: var(--dark); font-weight: 600; font-size: .92rem;
        padding-bottom: 4px; border-bottom: 2px solid transparent; transition: color .2s, border-color .2s;
        white-space: nowrap;
    }
    .nav-link:hover { color: var(--secondary); }
    .nav-link.active { color: var(--secondary); border-color: var(--secondary); }

    .header-actions { display: flex; align-items: center; gap: 12px; }
    .btn-account {
        display: inline-flex; align-items: center; gap: 8px;
        background: white; border: 1.5px solid #dbe2ea; color: var(--dark);
        font-weight: 600; font-size: .85rem; padding: 10px 18px; border-radius: var(--radius);
        cursor: pointer; transition: border-color .2s, color .2s;
        white-space: nowrap; text-decoration: none;
    }
    .btn-account:hover { border-color: var(--secondary); color: var(--secondary); }
    .btn-cta { padding: 10px 20px; font-size: .85rem; }

    #menuToggle { display: none; background: none; border: none; font-size: 1.7rem; color: var(--primary); cursor: pointer; padding: 4px; }

    @media (max-width: 1160px) {
        .nav { display: none !important; }
        #menuToggle { display: block !important; }
        .btn-account { display: none; }
    }
    @media (max-width: 480px) {
        .btn-cta span.label-full { display: none; }
    }

    #mobileNav {
        position: fixed; top: 0; right: -100%; width: 82%; max-width: 320px; height: 100vh;
        background: white; box-shadow: -5px 0 30px rgba(0, 0, 0, .15); z-index: 2000;
        padding: 80px 30px 30px; transition: right .35s ease;
        display: flex; flex-direction: column; gap: 0;
    }
    #mobileNav .nav-link {
        display: block; padding: 15px 0; border-bottom: 1px solid #eee; border-bottom-width: 1px;
        font-size: 1rem;
    }
    #mobileNav .nav-link.active { border-bottom-color: var(--secondary); }
    #closeMenu { position: absolute; top: 20px; right: 20px; background: none; border: none; font-size: 1.8rem; cursor: pointer; color: var(--dark); }
    #overlay { position: fixed; inset: 0; background: rgba(0, 0, 0, .5); z-index: 1500; display: none; }

    html { overflow-x: hidden; max-width: 100%; }

    @media (max-width: 640px) {
        .topbar-left span:nth-child(2) { display: none; }
        .topbar-right a span.full { display: none; }
    }

    /* .topbar-right en flex simple (pas de wrap ni de scroll) + html en overflow-x:hidden
       (ligne 198) : sur un petit écran, avec un téléphone/email/réseaux sociaux réellement
       renseignés (voir Compagnie), le contenu dépasse largement les 375px courants — le
       sélecteur de langue, le thème et le mode sombre se retrouvaient poussés à 500-600px,
       donc invisibles sans le moindre indice qu'ils existent (juste rognés, pas de
       scrollbar). On dégraisse : les coordonnées de contact restent joignables en un tap
       (icône seule, via l'astuce font-size:0 sur le lien — l'icône garde sa propre taille
       explicite ci-dessus), le sélecteur de langue (décoratif : aucune traduction
       n'existe réellement derrière) et les réseaux sociaux (déjà présents ailleurs sur le
       site) disparaissent, pour garantir que thème et mode sombre restent toujours
       visibles — ce sont les seuls contrôles fonctionnels de cette barre. */
    @media (max-width: 575.98px) {
        .topbar-right a { font-size: 0; }
        .topbar-right a i { font-size: .75rem; }
        .topbar-socials, .topbar-lang { display: none; }
        .topbar-right { gap: 14px; }
    }
</style>

<script>
    // Placeholder commun pour les liens/formulaires du site public pas encore
    // fonctionnels (même esprit que les liens inertes du menu admin type "Hors
    // programme") — évite un 404 tant que l'écran correspondant n'est pas porté.
    function tgBientot(e) {
        e.preventDefault();
        alert('Cette fonctionnalité arrive bientôt !');
    }

    (function() {
        var toggle    = document.getElementById('menuToggle');
        var mobileNav = document.getElementById('mobileNav');
        var closeBtn  = document.getElementById('closeMenu');
        var overlay   = document.getElementById('overlay');

        function openMenu() {
            mobileNav.style.right = '0';
            overlay.style.display = 'block';
            document.body.style.overflow = 'hidden';
        }
        function closeMenuFunc() {
            mobileNav.style.right = '-100%';
            overlay.style.display = 'none';
            document.body.style.overflow = '';
        }

        if (toggle)   toggle.addEventListener('click', openMenu);
        if (closeBtn) closeBtn.addEventListener('click', closeMenuFunc);
        if (overlay)  overlay.addEventListener('click', closeMenuFunc);

        window.addEventListener('resize', function() {
            if (window.innerWidth > 1160) closeMenuFunc();
        });
    })();

    // Thème couleur + mode sombre — même mécanique que l'admin (localStorage +
    // script anti-flash dans le <head> de chaque page, voir tgSiteTheme/tgSiteDarkMode).
    (function () {
        var toggle  = document.getElementById('themePickerToggle');
        var menu    = document.getElementById('themePickerMenu');
        var darkBtn = document.getElementById('darkModeToggle');
        if (! toggle || ! menu || ! darkBtn) return;

        function markActiveSwatch() {
            var current = localStorage.getItem('tgSiteTheme') || 'default';
            menu.querySelectorAll('button[data-theme]').forEach(function (b) {
                b.classList.toggle('is-active', b.dataset.theme === current);
            });
        }
        function updateDarkIcon() {
            var isDark = document.documentElement.classList.contains('dark-mode');
            darkBtn.querySelector('i').className = isDark ? 'fas fa-sun' : 'fas fa-moon';
        }

        markActiveSwatch();
        updateDarkIcon();

        toggle.addEventListener('click', function (e) {
            e.stopPropagation();
            menu.hidden = ! menu.hidden;
        });
        document.addEventListener('click', function (e) {
            if (! menu.hidden && ! menu.contains(e.target) && e.target !== toggle) menu.hidden = true;
        });

        menu.querySelectorAll('button[data-theme]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var theme = btn.dataset.theme;
                if (theme === 'default') {
                    document.documentElement.removeAttribute('data-theme');
                    localStorage.removeItem('tgSiteTheme');
                } else {
                    document.documentElement.setAttribute('data-theme', theme);
                    localStorage.setItem('tgSiteTheme', theme);
                }
                markActiveSwatch();
                menu.hidden = true;
            });
        });

        darkBtn.addEventListener('click', function () {
            var isDark = document.documentElement.classList.toggle('dark-mode');
            localStorage.setItem('tgSiteDarkMode', isDark ? 'true' : 'false');
            updateDarkIcon();
        });
    })();
</script>

{{-- Toast de notification (App\Support\Flash — même mécanisme que côté admin, voir
     resources/views/admin/partials/set_flash.blade.php), chargé une seule fois ici plutôt
     que dans chaque page du site. --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
{{-- Alpine.js : interactions instantanées (widget d'aide, menus, futurs composants) sans
     étape de build — chargé une seule fois, ré-exploite automatiquement les nouveaux
     éléments x-data injectés par les transitions de page (site-transitions.js). --}}
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>
@if (session('notification'))
    @php $notification = session('notification'); @endphp
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: @json($notification['swal_icon'] ?? 'info'),
                html: @json($notification['message']),
                showConfirmButton: false,
                timer: 4500,
                timerProgressBar: true,
            });
        });
    </script>
@endif
