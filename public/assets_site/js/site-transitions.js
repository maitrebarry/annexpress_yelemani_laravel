/**
 * Moteur de transitions de page du site public — donne une sensation d'application
 * "à la Vue/Node" (navigation instantanée, animée, sans rechargement complet) sans
 * réécrire le backend Laravel/Blade : chaque page reste une vraie URL server-rendered
 * (SEO, partage de lien, retour navigateur intacts) ; ce script intercepte simplement les
 * clics/recherches internes, récupère la page cible en fetch(), et ne remplace que le
 * <body> (le <head> — styles, polices — reste en place, aucun flash).
 *
 * Volontairement PAS interceptés (sécurité / simplicité) :
 *  - tout lien vers /admin ou /login (autre application, autre <head>) ;
 *  - tout formulaire POST (réservation, connexion, contact...) — soumission classique ;
 *  - liens externes, ancres #, mailto:/tel:, target="_blank", download, ou tout élément
 *    marqué data-no-transition.
 */
(function () {
    'use strict';

    if (window.__siteTransitionsInit) return; // déjà initialisé sur cette page (tête, jamais swappée)
    window.__siteTransitionsInit = true;

    var EXCLUDED_PREFIXES = ['/admin', '/login', '/logout', '/storage'];

    function isInternal(url) {
        try {
            var u = new URL(url, window.location.href);
            if (u.origin !== window.location.origin) return false;
            return ! EXCLUDED_PREFIXES.some(function (p) { return u.pathname.indexOf(p) === 0; });
        } catch (e) {
            return false;
        }
    }

    /* ===== Barre de progression (haut de page) ===== */
    var bar = document.createElement('div');
    bar.className = 'tg-progress-bar';
    document.documentElement.appendChild(bar);
    var barTimer = null;

    function progressStart() {
        clearTimeout(barTimer);
        bar.style.transition = 'none';
        bar.style.width = '0%';
        bar.style.opacity = '1';
        // force reflow avant de réactiver la transition
        bar.offsetWidth; // eslint-disable-line no-unused-expressions
        bar.style.transition = 'width 3s cubic-bezier(.1,.6,.3,1), opacity .2s ease .2s';
        bar.style.width = '80%';
    }

    function progressDone() {
        bar.style.transition = 'width .25s ease';
        bar.style.width = '100%';
        barTimer = setTimeout(function () {
            bar.style.opacity = '0';
        }, 150);
    }

    /* ===== Ré-exécution des scripts du nouveau body =====
       innerHTML n'exécute jamais les <script> injectés : on les recrée à l'identique.
       Les <script src="..."> déjà présents ailleurs dans le document (Bootstrap, AOS,
       Swiper, Alpine, ce fichier lui-même...) ne sont PAS rechargés — seuls le nouveau
       markup change, leurs effets/globals restent valables d'une navigation à l'autre. */
    function reexecuteScripts(container) {
        var existingSrcs = Array.prototype.map.call(
            document.querySelectorAll('script[src]'),
            function (s) { return s.src; }
        );

        Array.prototype.forEach.call(container.querySelectorAll('script'), function (oldScript) {
            if (oldScript.src) {
                if (existingSrcs.indexOf(oldScript.src) !== -1) return;
                var s = document.createElement('script');
                Array.prototype.forEach.call(oldScript.attributes, function (a) { s.setAttribute(a.name, a.value); });
                oldScript.parentNode.replaceChild(s, oldScript);
                return;
            }
            var inline = document.createElement('script');
            inline.textContent = oldScript.textContent;
            oldScript.parentNode.replaceChild(inline, oldScript);
        });
    }

    /* ===== Styles propres à une page =====
       Chaque vue du site définit son propre <style> dans SON <head> (pas de layout Blade
       partagé) : lors d'un swap du <body> seul, ce <head> d'origine ne bouge jamais, donc
       les classes CSS propres à la page cible seraient absentes tant qu'on ne les
       transfère pas nous-mêmes. On marque celles qu'on injecte pour les remplacer proprement
       à la navigation suivante, sans jamais toucher aux styles "de base" (site-common.css
       etc., chargés une fois pour toutes en <link>). */
    function swapPageStyles(newHead) {
        document.querySelectorAll('style[data-tg-page-style]').forEach(function (el) { el.remove(); });
        newHead.querySelectorAll('style').forEach(function (styleEl) {
            var clone = document.createElement('style');
            clone.setAttribute('data-tg-page-style', '');
            clone.textContent = styleEl.textContent;
            document.head.appendChild(clone);
        });
    }

    /* ===== Navigation ===== */
    function navigateTo(url, opts) {
        opts = opts || {};
        var push = opts.push !== false;

        progressStart();
        document.body.classList.add('tg-page-leaving');

        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (res) {
                if (! res.ok) throw new Error('HTTP ' + res.status);
                return res.text();
            })
            .then(function (html) {
                var doc = new DOMParser().parseFromString(html, 'text/html');
                var newTitle = doc.title;
                var newBody = doc.body;

                setTimeout(function () {
                    document.title = newTitle;
                    swapPageStyles(doc.head);
                    document.body.replaceWith(newBody);
                    reexecuteScripts(newBody);
                    newBody.classList.add('tg-page-leaving');
                    // reflow puis retrait de la classe = anime le fade-in
                    newBody.offsetWidth; // eslint-disable-line no-unused-expressions
                    newBody.classList.remove('tg-page-leaving');
                    window.scrollTo({ top: 0, behavior: 'auto' });
                    if (push) history.pushState({ tgSoftNav: true }, '', url);
                    progressDone();
                    document.dispatchEvent(new CustomEvent('tg:page-loaded'));
                }, 160);
            })
            .catch(function () {
                // Repli : navigation classique, jamais bloquer l'utilisateur.
                window.location.href = url;
            });
    }

    /* ===== Interception des clics ===== */
    document.addEventListener('click', function (e) {
        if (e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;

        var link = e.target.closest('a[href]');
        if (! link) return;
        if (link.target && link.target !== '' && link.target !== '_self') return;
        if (link.hasAttribute('download')) return;
        if (link.hasAttribute('data-no-transition')) return;
        if (link.closest('[data-no-transition]')) return;

        var href = link.getAttribute('href');
        if (! href || href.charAt(0) === '#') return;
        if (/^(mailto:|tel:|javascript:)/i.test(href)) return;
        if (! isInternal(link.href)) return;

        // Un lien vers la même page (juste une ancre) : laisser faire le navigateur.
        var target = new URL(link.href, window.location.href);
        if (target.pathname === window.location.pathname && target.search === window.location.search && target.hash) return;

        e.preventDefault();
        navigateTo(link.href);
    }, true);

    /* ===== Interception des formulaires GET (recherche) ===== */
    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (! (form instanceof HTMLFormElement)) return;
        if (form.hasAttribute('data-no-transition')) return;
        // Seuls les formulaires marqués GET EXPLICITEMENT sont interceptés (recherche...).
        // Un formulaire SANS attribut method (comme le modal de réservation, géré en JS/
        // fetch pur) ne doit surtout pas être traité comme un GET implicite : ça
        // court-circuitait sa propre soumission AJAX en la remplaçant par une navigation
        // de cette page, effaçant le message de succès juste après son affichage.
        var methodAttr = form.getAttribute('method');
        if (! methodAttr || methodAttr.toLowerCase() !== 'get') return;
        if (! isInternal(form.action || window.location.href)) return;

        var url = new URL(form.action || window.location.href, window.location.href);
        var formData = new FormData(form);
        var params = new URLSearchParams();
        formData.forEach(function (value, key) { params.append(key, value); });
        url.search = params.toString();

        e.preventDefault();
        navigateTo(url.toString());
    }, true);

    /* ===== Précédent / Suivant navigateur ===== */
    window.addEventListener('popstate', function () {
        navigateTo(window.location.href, { push: false });
    });
})();
