/**
 * Moteur de transitions de page de l'espace admin — même principe que
 * assets_site/js/site-transitions.js (site public) : intercepte les clics/formulaires
 * GET internes, récupère la page cible en fetch(), et ne remplace que <body> (le <head>
 * — un seul, partagé par toutes les vues admin via admin/partials/header.blade.php —
 * reste en place). Fichier séparé du site public car les règles d'inclusion sont
 * inversées (ici on ne veut QUE les liens /admin, jamais l'inverse) et parce que l'admin
 * a des besoins spécifiques (ré-init DataTables/select2 après chaque page, cf. plus bas).
 *
 * Volontairement PAS interceptés (mêmes règles que le site) : liens externes, ancres #,
 * mailto:/tel:, target!=_self, download, data-no-transition — et tout formulaire dont
 * l'attribut method n'est pas explicitement "get" (l'admin a des dizaines de formulaires
 * POST dans des modales ; une navigation douce ne doit jamais leur voler leur soumission,
 * exactement la leçon du bug du formulaire de réservation sur le site public : ne JAMAIS
 * supposer une méthode par défaut pour un formulaire sans attribut method).
 */
(function () {
    'use strict';

    if (window.__adminTransitionsInit) return;
    window.__adminTransitionsInit = true;

    // window.tgReady est défini dans admin/partials/header.blade.php (tout début de
    // <head>), pas ici : admin/partials/set_flash est inclus dans le layout AVANT ce
    // script (chargé par foot.blade.php, en toute fin de <body>) et appelle tgReady(...)
    // immédiatement, donc la fonction doit déjà exister avant même que ce fichier soit
    // atteint. Utilisé par tous les scripts de page (@section('scripts')) à la place de
    // document.addEventListener('DOMContentLoaded', ...) ou $(document).ready(...) : ces
    // événements ne se déclenchent qu'une seule fois par vrai chargement de page, donc
    // plus jamais après un swap de <body>.

    function isInternalAdminLink(url) {
        try {
            var u = new URL(url, window.location.href);
            return u.origin === window.location.origin && u.pathname.indexOf('/admin') === 0;
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
       `existingSrcs` DOIT être capturé AVANT le remplacement de <body> (voir navigateTo) :
       le calculer après, comme le fait naïvement une première version de ce mécanisme,
       inclut par erreur les <script src> du nouveau body lui-même (déjà dans le document à
       ce moment-là), qui se retrouvent donc toujours considérés comme "déjà chargés" et
       jamais exécutés — y compris la toute première fois qu'une page qui en a besoin est
       atteinte. Concrètement : les scripts spécifiques à certaines pages (confirmation de
       suppression, pont d'impression thermique...) resteraient inertes si cette page était
       la première atteinte par navigation douce dans la session — y compris un lien de
       suppression SANS confirmation, un vrai risque de sécurité des données. */
    function reexecuteScripts(container, existingSrcs) {
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
       Toutes les vues admin partagent un seul <head> (admin/partials/header.blade.php),
       donc ce mécanisme est surtout une sécurité si une vue définissait un <style> propre
       dans son contenu — comme sur le site public, on marque ce qu'on injecte pour le
       remplacer proprement à la navigation suivante. */
    function swapPageStyles(newHead) {
        document.querySelectorAll('style[data-tg-page-style]').forEach(function (el) { el.remove(); });
        newHead.querySelectorAll('style').forEach(function (styleEl) {
            var clone = document.createElement('style');
            clone.setAttribute('data-tg-page-style', '');
            clone.textContent = styleEl.textContent;
            document.head.appendChild(clone);
        });
    }

    // Après chaque swap : DataTables/select2 doivent être ré-initialisés sur les éléments
    // du NOUVEAU body (leurs scripts de chargement globaux, table-datatable.js/
    // form-select2.js, sont des <script src> dédupliqués — ils ne s'exécutent donc plus
    // après le tout premier chargement réel). Fonctions partagées définies par ces mêmes
    // fichiers (window.tgInitDataTables/tgInitSelect2) pour éviter toute divergence entre
    // le premier chargement et les navigations suivantes (ex: les boutons d'export de
    // #example2 doivent être configurés à l'identique dans les deux cas).
    function reinitPageWidgets() {
        if (window.tgInitDataTables) window.tgInitDataTables(document);
        if (window.tgInitSelect2) window.tgInitSelect2(document);
    }

    /* ===== Navigation ===== */
    function navigateTo(url, opts) {
        opts = opts || {};
        var push = opts.push !== false;

        // Capturé AVANT le fetch : rien ne doit changer entre-temps, et surtout pas après
        // le remplacement de <body> (voir le commentaire de reexecuteScripts ci-dessus).
        var existingSrcs = Array.prototype.map.call(
            document.querySelectorAll('script[src]'),
            function (s) { return s.src; }
        );

        progressStart();
        document.body.classList.add('tg-page-leaving');

        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (res) {
                if (! res.ok) throw new Error('HTTP ' + res.status);
                // Une session expirée fait rediriger /admin/... vers la page de connexion
                // (document HTML autonome, pas construit sur layouts.admin) : fetch() suit
                // les redirections en silence et résout avec un 200 "normal", donc rien
                // dans le .catch() ci-dessous n'attraperait ce cas — on le détecte ici
                // explicitement et on bascule sur une vraie navigation plutôt que d'injecter
                // la page de connexion dans la coquille (navbar/sidebar) admin encore visible.
                // Piège : la route de connexion EST "/admin" tout court (routes/web.php,
                // name('login')) — un simple `indexOf('/admin') !== 0` la traite à tort comme
                // une page admin normale puisque "/admin" commence bien par "/admin". Il faut
                // explicitement exclure ce chemin exact, en plus de tout ce qui ne commence pas
                // par "/admin/".
                var targetPath = new URL(res.url, window.location.href).pathname;
                if (targetPath === '/admin' || targetPath.indexOf('/admin/') !== 0) {
                    window.location.href = res.url;
                    return null;
                }
                return res.text();
            })
            .then(function (html) {
                if (html === null) return; // redirection hors /admin déjà gérée ci-dessus

                var doc = new DOMParser().parseFromString(html, 'text/html');
                var newTitle = doc.title;
                var newBody = doc.body;

                setTimeout(function () {
                    document.title = newTitle;
                    swapPageStyles(doc.head);
                    document.body.replaceWith(newBody);
                    reexecuteScripts(newBody, existingSrcs);
                    reinitPageWidgets();
                    newBody.classList.add('tg-page-leaving');
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
        if (! isInternalAdminLink(link.href)) return;

        var target = new URL(link.href, window.location.href);
        if (target.pathname === window.location.pathname && target.search === window.location.search && target.hash) return;

        e.preventDefault();
        navigateTo(link.href);
    }, true);

    /* ===== Interception des formulaires GET ===== */
    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (! (form instanceof HTMLFormElement)) return;
        if (form.hasAttribute('data-no-transition')) return;
        // Seuls les formulaires marqués GET EXPLICITEMENT sont interceptés. Un formulaire
        // sans attribut method (très fréquent dans l'admin : formulaires POST des modales
        // d'ajout/édition) ne doit jamais être traité comme un GET implicite.
        var methodAttr = form.getAttribute('method');
        if (! methodAttr || methodAttr.toLowerCase() !== 'get') return;
        if (! isInternalAdminLink(form.action || window.location.href)) return;

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
