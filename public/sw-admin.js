/**
 * Service worker de l'admin — rend l'espace de gestion installable (PWA), même logique
 * que public/sw-site.js : cache-first pour les fichiers statiques, network-first strict
 * pour toute page HTML (données sensibles/changeantes — billets, caisses, permissions —
 * jamais servies depuis un cache périmé).
 */
const CACHE = 'tg-admin-static-v1';
const STATIC_EXTENSIONS = /\.(css|js|png|jpg|jpeg|webp|svg|gif|woff2?|ttf|ico)$/i;

self.addEventListener('install', (event) => {
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) =>
            Promise.all(keys.filter((k) => k !== CACHE).map((k) => caches.delete(k)))
        )
    );
    self.clients.claim();
});

self.addEventListener('fetch', (event) => {
    const req = event.request;
    if (req.method !== 'GET') return;

    const url = new URL(req.url);
    if (url.origin !== self.location.origin) return;

    if (req.mode === 'navigate') {
        event.respondWith(fetch(req).catch(() =>
            new Response(
                '<!doctype html><html lang="fr"><meta charset="utf-8"><title>Hors ligne</title>' +
                '<body style="font-family:sans-serif;text-align:center;padding:60px 20px;color:#0f3b5e;">' +
                '<h1>Connexion indisponible</h1><p>Vérifiez votre connexion internet et réessayez.</p></body></html>',
                { headers: { 'Content-Type': 'text/html; charset=utf-8' } }
            )
        ));
        return;
    }

    if (STATIC_EXTENSIONS.test(url.pathname)) {
        event.respondWith(
            caches.match(req).then((cached) => {
                if (cached) return cached;
                return fetch(req).then((res) => {
                    if (res.ok) {
                        const clone = res.clone();
                        caches.open(CACHE).then((c) => c.put(req, clone));
                    }
                    return res;
                }).catch(() => cached);
            })
        );
    }
});
