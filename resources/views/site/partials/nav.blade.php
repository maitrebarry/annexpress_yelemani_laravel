{{--
    Port de Projets_licence/app/views/site/partials/nav.view.php — en-tête public
    partagé par toutes les pages du site vitrine (Accueil, Compagnies, Contact...).
    Pas de lien "Espace pro" (connexion staff) sur le site public : retiré à la demande de
    l'utilisateur 2026-08-24 — le staff atteint /login directement par son URL, sans qu'elle
    soit mise en avant aux visiteurs.

    Site dédié à une seule compagnie (2026-08-24) : le branding (logo + nom du header) est
    désormais celui de App\Models\Compagnie::site() sur TOUTES les pages du site (une vue
    peut passer $compagnie explicitement si elle l'a déjà résolue, sinon ce partial le
    résout lui-même) — plus de logo TransHub générique en usage normal. Le deuxième lien de
    nav pointe vers la page trajets de cette compagnie ("Nos trajets") plutôt que vers
    l'ancien catalogue multi-compagnies (route `site.compagnies` retirée de la nav, elle
    redirige maintenant vers cette même page si on y accède directement).
--}}
@php
    $navCompagnie = $compagnie ?? \App\Models\Compagnie::site();
@endphp
<header class="header" style="position:sticky;top:0;z-index:1000;background:white;box-shadow:0 2px 10px rgba(0,0,0,0.05);">
    <div class="container" style="max-width:1280px;margin:0 auto;padding:0 24px;">
        <div class="header-inner" style="display:flex;justify-content:space-between;align-items:center;padding:16px 0;">
            <a href="{{ route('site.home') }}" class="logo" style="display:flex;align-items:center;gap:10px;text-decoration:none;">
                @if ($navCompagnie->logo)
                    <img src="{{ asset('images/logos/'.$navCompagnie->logo) }}" alt="{{ $navCompagnie->nom_compagnie }}" style="height:52px;width:auto;object-fit:contain;">
                    <span style="line-height:1.2;">
                        <span style="display:block;font-weight:800;color:#0f3b5e;font-size:1.05rem;">{{ $navCompagnie->nom_compagnie }}</span>
                        <span style="display:block;font-weight:500;color:#9aa5b1;font-size:0.65rem;letter-spacing:.3px;">VIA TRANSHUB</span>
                    </span>
                @else
                    <span style="font-weight:800;color:#0f3b5e;font-size:1.15rem;">{{ $navCompagnie->nom_compagnie }}</span>
                @endif
            </a>

            <div class="nav" style="display:flex;gap:32px;align-items:center;">
                <a href="{{ route('site.home') }}" class="nav-link{{ request()->routeIs('site.home') ? ' active' : '' }}" style="text-decoration:none;color:#2c3e50;font-weight:500;transition:color 0.3s;">Accueil</a>
                <a href="{{ route('site.compagnie.trajets', $navCompagnie) }}" class="nav-link{{ request()->routeIs('site.compagnie.trajets') ? ' active' : '' }}" style="text-decoration:none;color:#2c3e50;font-weight:500;transition:color 0.3s;">Nos trajets</a>
                <a href="{{ route('site.suivi-colis') }}" class="nav-link{{ request()->routeIs('site.suivi-colis') ? ' active' : '' }}" style="text-decoration:none;color:#2c3e50;font-weight:500;transition:color 0.3s;">Suivis de colis</a>
                <a href="{{ route('site.contact') }}" class="nav-link{{ request()->routeIs('site.contact') ? ' active' : '' }}" style="text-decoration:none;color:#2c3e50;font-weight:500;transition:color 0.3s;">Contact</a>
            </div>

            <button id="menuToggle" style="display:none;background:none;border:none;font-size:1.8rem;color:#0f3b5e;cursor:pointer;padding:4px;">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </div>
</header>

<div id="mobileNav" style="position:fixed;top:0;right:-100%;width:80%;max-width:320px;height:100vh;background:white;box-shadow:-5px 0 30px rgba(0,0,0,0.15);z-index:2000;padding:80px 30px 30px;transition:right 0.35s ease;display:flex;flex-direction:column;gap:0;">
    <button id="closeMenu" style="position:absolute;top:20px;right:20px;background:none;border:none;font-size:1.8rem;cursor:pointer;color:#2c3e50;"><i class="fas fa-times"></i></button>
    <a href="{{ route('site.home') }}" class="nav-link{{ request()->routeIs('site.home') ? ' active' : '' }}" style="display:block;padding:15px 0;text-decoration:none;color:#2c3e50;font-weight:600;border-bottom:1px solid #eee;font-size:1rem;">Accueil</a>
    <a href="{{ route('site.compagnie.trajets', $navCompagnie) }}" class="nav-link{{ request()->routeIs('site.compagnie.trajets') ? ' active' : '' }}" style="display:block;padding:15px 0;text-decoration:none;color:#2c3e50;font-weight:600;border-bottom:1px solid #eee;font-size:1rem;">Nos trajets</a>
    <a href="{{ route('site.suivi-colis') }}" class="nav-link{{ request()->routeIs('site.suivi-colis') ? ' active' : '' }}" style="display:block;padding:15px 0;text-decoration:none;color:#2c3e50;font-weight:600;border-bottom:1px solid #eee;font-size:1rem;">Suivis de colis</a>
    <a href="{{ route('site.contact') }}" class="nav-link{{ request()->routeIs('site.contact') ? ' active' : '' }}" style="display:block;padding:15px 0;text-decoration:none;color:#2c3e50;font-weight:600;border-bottom:1px solid #eee;font-size:1rem;">Contact</a>
</div>
<div id="overlay" style="position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:1500;display:none;"></div>

<style>
    .header::after {
        content: '';
        display: block;
        height: 3px;
        background: linear-gradient(90deg, var(--primary, #0f3b5e) 0%, var(--secondary, #e67e22) 100%);
    }
    @media (max-width: 768px) {
        #menuToggle { display: block !important; }
        .nav { display: none !important; }
    }
    .nav-link.active {
        color: #e67e22 !important;
        font-weight: 700 !important;
    }
    .nav .nav-link.active {
        border-bottom: 2px solid #e67e22;
        padding-bottom: 6px;
    }
    /* #mobileNav est positionné hors écran via "right:-100%" : sans ceci, il élargit
       quand même le scroll horizontal de la page sur mobile, même fermé. */
    html {
        overflow-x: hidden;
        max-width: 100%;
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
            if (window.innerWidth > 768) closeMenuFunc();
        });
    })();
</script>

{{-- Toast de notification (App\Support\Flash — même mécanisme que côté admin, voir
     resources/views/admin/partials/set_flash.blade.php), chargé une seule fois ici plutôt
     que dans chaque page du site. --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
