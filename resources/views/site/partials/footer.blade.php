{{--
    Footer partagé par toutes les pages du site public (avant, dupliqué à l'identique
    dans chaque vue — remplacé par ce partial lors de la refonte visuelle du site
    2026-08-26 pour garder un seul endroit à modifier).
--}}
@php
    $footerCompagnie = $compagnie ?? \App\Models\Compagnie::site();
@endphp
<footer class="footer">
    <div class="container">
        <div class="footer-grid">
            <div>
                <div class="footer-brand">
                    @if ($footerCompagnie->logo)
                        <img src="{{ asset('images/logos/'.$footerCompagnie->logo) }}" alt="{{ $footerCompagnie->nom_compagnie }}">
                    @endif
                    <span>{{ $footerCompagnie->nom_compagnie }}</span>
                </div>
                <p style="font-size: 0.85rem;">{{ $footerCompagnie->slogant ?: "Réservation de billets de bus et suivi de colis en ligne, simple et rapide." }}</p>
                <div class="footer-socials">
                    <a href="#" onclick="tgBientot(event)" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" onclick="tgBientot(event)" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                    <a href="#" onclick="tgBientot(event)" aria-label="WhatsApp"><i class="fab fa-whatsapp"></i></a>
                </div>
            </div>
            <div>
                <h4>Liens rapides</h4>
                <a href="{{ route('site.home') }}">Accueil</a>
                <a href="{{ route('site.compagnie.trajets', $footerCompagnie) }}">Nos trajets</a>
                <a href="{{ route('site.suivi-colis') }}">Suivi de colis</a>
                <a href="{{ route('site.contact') }}">Contact</a>
            </div>
            <div>
                <h4>Support</h4>
                <a href="#" onclick="tgBientot(event)">FAQ</a>
                <a href="#" onclick="tgBientot(event)">Conditions générales</a>
                <a href="#" onclick="tgBientot(event)">Politique de confidentialité</a>
            </div>
            <div>
                <h4>Contact</h4>
                <a href="tel:+22390259438"><i class="fas fa-phone"></i> +223 90 25 94 38</a>
                <a href="mailto:annexpress@gmail.com"><i class="fas fa-envelope"></i> annexpress@gmail.com</a>
                <a href="#"><i class="fas fa-map-marker-alt"></i> Pelegana, Segou, Mali</a>
            </div>
        </div>
        <div class="footer-bottom">
            <p>Copyright &copy; 2026 Computer Service Barry. All rights reserved.</p>
        </div>
    </div>
</footer>
