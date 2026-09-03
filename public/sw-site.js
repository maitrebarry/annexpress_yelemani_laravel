/**
 * Service worker du site public — rend l'app installable (PWA) et met en cache les
 * fichiers statiques (CSS/JS/polices/images) pour un chargement plus rapide et un
 * minimum de résilience hors-ligne. Volontairement PAS de cache pour les pages HTML
 * elles-mêmes (network-first strict, jamais de fallback cache) : le contenu (trajets,
 * prix, disponibilités, billets) doit toujours être frais, jamais servi périmé.
 */
const CACHE = 'tg-site-static-v1';
const STATIC_EXTENSIONS = /\.(css|js|png|jpg|jpeg|webp|svg|gif|woff2?|ttf)$/i;

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

    // Pages HTML (navigations) : toujours le réseau, jamais de cache — contenu vivant.
    if (req.mode === 'navigate') {
        event.respondWith(
            fetch(req).catch(() =>
                new Response(
                    '<!doctype html><html lang="fr"><meta charset="utf-8"><title>Hors ligne</title>' +
                    '<body style="font-family:sans-serif;text-align:center;padding:60px 20px;color:#0f3b5e;">' +
                    '<h1>Connexion indisponible</h1><p>Vérifiez votre connexion internet et réessayez.</p></body></html>',
                    { headers: { 'Content-Type': 'text/html; charset=utf-8' } }
                )
            )
        );
        return;
    }

    // Fichiers statiques : cache-first (rapide), avec repli réseau + mise en cache.
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
