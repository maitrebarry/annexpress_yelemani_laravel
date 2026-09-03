{{--
    Footer partagé par toutes les pages du site public (avant, dupliqué à l'identique
    dans chaque vue — remplacé par ce partial lors de la refonte visuelle du site
    2026-08-26 pour garder un seul endroit à modifier).

    Refonte 2026-09-02 (maquette utilisateur, fidélité explicitement demandée) : 5 blocs
    (marque+copyright / Liens rapides / Services / Informations / Nous suivre), sans
    bandeau de coordonnées séparé (téléphone/email sont déjà dans le bandeau du haut, voir
    nav.blade.php — absents de cette maquette au niveau du footer). Rien de propre à une
    compagnie précise n'est codé en dur : $footerCompagnie vient de App\Models\Compagnie et
    les réseaux sociaux s'effacent simplement si non renseignés, pour rester valable pour
    n'importe quelle compagnie servie par ce même code.
--}}
@php
    $footerCompagnie = $compagnie ?? \App\Models\Compagnie::site();
@endphp
<footer class="footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-brand-col">
                <div class="footer-brand">
                    @if ($footerCompagnie->logo)
                        <img src="{{ asset('images/logos/'.$footerCompagnie->logo) }}" alt="{{ $footerCompagnie->nom_compagnie }}">
                    @endif
                    <span>{{ $footerCompagnie->nom_compagnie }}</span>
                </div>
                <p class="footer-copyright">Tous droits réservés &copy; {{ date('Y') }} Computer Service Barry</p>
            </div>
            <div>
                <h4>Liens rapides</h4>
                <a href="{{ route('site.home') }}">Accueil</a>
                <a href="{{ route('site.compagnie.trajets', $footerCompagnie) }}">Nos trajets</a>
                <a href="{{ route('site.suivi-colis') }}">Suivi de colis</a>
            </div>
            <div>
                <h4>Services</h4>
                <a href="{{ route('site.services') }}">Services</a>
                <a href="{{ route('site.actualites') }}">Actualités</a>
                <a href="{{ route('site.contact') }}">Contact</a>
            </div>
            <div>
                <h4>Informations</h4>
                <a href="#" onclick="tgBientot(event)">À propos de nous</a>
                <a href="#" onclick="tgBientot(event)">Conditions d'utilisation</a>
                <a href="#" onclick="tgBientot(event)">Politique de confidentialité</a>
            </div>
            <div>
                <h4>Nous suivre</h4>
                <div class="footer-socials">
                    <a href="{{ $footerCompagnie->facebook ?: '#' }}" @if(! $footerCompagnie->facebook) onclick="tgBientot(event)" @else target="_blank" rel="noopener" @endif aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="{{ $footerCompagnie->instagram ?: '#' }}" @if(! $footerCompagnie->instagram) onclick="tgBientot(event)" @else target="_blank" rel="noopener" @endif aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                    <a href="{{ $footerCompagnie->whatsapp ? 'https://wa.me/'.preg_replace('/\D+/', '', $footerCompagnie->whatsapp) : '#' }}" @if(! $footerCompagnie->whatsapp) onclick="tgBientot(event)" @else target="_blank" rel="noopener" @endif aria-label="WhatsApp"><i class="fab fa-whatsapp"></i></a>
                </div>
            </div>
        </div>
    </div>
</footer>

{{--
    Widget d'assistance flottant — vrai formulaire de contact rapide (décision
    2026-09-03), plus une porte d'entrée directe vers WhatsApp quand la compagnie l'a
    renseigné. Soumet vers la même route que la page Contact (site.contact.store,
    origine=aide) : les deux alimentent le même écran admin "Messages reçus".
--}}
<div class="help-widget-wrap" x-data="{ open: false }">
    <div class="help-panel" x-show="open" x-transition.origin.bottom.right x-cloak @click.outside="open = false">
        <div class="help-panel-head">
            <strong>Besoin d'aide ?</strong>
            <button type="button" class="help-panel-close" @click="open = false" aria-label="Fermer"><i class="fas fa-xmark"></i></button>
        </div>
        <p class="help-panel-sub">Écrivez-nous, on vous répond rapidement.</p>

        @if ($footerCompagnie->whatsapp)
            <a href="https://wa.me/{{ preg_replace('/\D+/', '', $footerCompagnie->whatsapp) }}" target="_blank" rel="noopener" class="help-panel-whatsapp">
                <i class="fab fa-whatsapp"></i> Discuter sur WhatsApp
            </a>
            <div class="help-panel-or"><span>ou par message</span></div>
        @endif

        <form method="POST" action="{{ route('site.contact.store') }}" class="help-panel-form">
            @csrf
            <input type="hidden" name="origine" value="aide">
            <input type="text" name="nom" placeholder="Votre nom" required>
            <input type="tel" name="telephone" placeholder="Téléphone (facultatif)">
            <textarea name="message" placeholder="Votre message..." rows="3" required></textarea>
            <button type="submit"><i class="fas fa-paper-plane"></i> Envoyer</button>
        </form>
    </div>

    <button type="button" class="help-widget" @click="open = !open">
        <span class="help-widget-icon"><i class="fas fa-headset"></i></span>
        <span class="help-widget-text">
            <strong>Besoin d'aide ?</strong>
            <small>Écrivez-nous</small>
        </span>
    </button>
</div>

<style>
    .help-widget-wrap { position: fixed; right: 24px; bottom: 24px; z-index: 1200; }
    [x-cloak] { display: none !important; }
    .help-widget {
        display: flex; align-items: center; gap: 12px;
        background: var(--primary-dark); color: white; text-decoration: none;
        border: none; padding: 12px 20px 12px 12px; border-radius: 50px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, .25);
        transition: transform .2s ease; cursor: pointer;
    }
    .help-widget:hover { transform: translateY(-3px); color: white; }
    .help-widget-icon {
        width: 34px; height: 34px; border-radius: 50%; background: var(--secondary);
        display: flex; align-items: center; justify-content: center; flex-shrink: 0; color: white;
    }
    .help-widget-text { display: flex; flex-direction: column; line-height: 1.3; text-align: left; }
    .help-widget-text strong { font-size: .82rem; }
    .help-widget-text small { font-size: .72rem; opacity: .75; }
    @media (max-width: 576px) {
        .help-widget-text { display: none; }
        .help-widget { padding: 12px; }
    }

    .help-panel {
        position: absolute; right: 0; bottom: calc(100% + 14px);
        width: 320px; background: white; border-radius: var(--radius-lg);
        box-shadow: 0 20px 50px rgba(0, 0, 0, .25); padding: 20px; color: var(--dark);
        transform-origin: bottom right;
    }
    .help-panel-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 2px; }
    .help-panel-head strong { font-size: 1rem; }
    .help-panel-close { background: none; border: none; color: var(--gray); font-size: 1rem; cursor: pointer; padding: 4px; }
    .help-panel-sub { font-size: .8rem; color: var(--gray); margin-bottom: 14px; }
    .help-panel-whatsapp {
        display: flex; align-items: center; justify-content: center; gap: 8px;
        background: #25d366; color: white; text-decoration: none; font-weight: 600; font-size: .85rem;
        padding: 10px; border-radius: var(--radius); margin-bottom: 12px;
    }
    .help-panel-or { text-align: center; margin-bottom: 12px; position: relative; }
    .help-panel-or span { background: white; padding: 0 10px; font-size: .7rem; color: var(--gray); position: relative; z-index: 1; }
    .help-panel-or::before { content: ''; position: absolute; left: 0; right: 0; top: 50%; height: 1px; background: #eef1f5; }
    .help-panel-form { display: flex; flex-direction: column; gap: 10px; }
    .help-panel-form input, .help-panel-form textarea {
        border: 1px solid #e2e8f0; border-radius: var(--radius); padding: 9px 12px; font-size: .85rem; font-family: inherit; resize: none;
    }
    .help-panel-form input:focus, .help-panel-form textarea:focus { outline: none; border-color: var(--secondary); }
    .help-panel-form button {
        display: flex; align-items: center; justify-content: center; gap: 8px;
        background: var(--secondary); color: white; border: none; border-radius: var(--radius);
        padding: 10px; font-weight: 600; font-size: .85rem; cursor: pointer; transition: background .2s;
    }
    .help-panel-form button:hover { background: var(--secondary-dark); }
    @media (max-width: 576px) {
        .help-panel { width: calc(100vw - 48px); right: -12px; }
    }
</style>

