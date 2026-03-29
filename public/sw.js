const CACHE = 'sesc-swim-v1';

const PRECACHE = [
    '/',
    '/placar',
    '/evolucao',
    '/resultados',
    '/indices',
    'https://cdn.tailwindcss.com',
    'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js',
];

// Instala e pré-cacheia os recursos estáticos
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE).then(cache => cache.addAll(PRECACHE)).then(() => self.skipWaiting())
    );
});

// Remove caches antigos ao ativar
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(keys =>
            Promise.all(keys.filter(k => k !== CACHE).map(k => caches.delete(k)))
        ).then(() => self.clients.claim())
    );
});

// Estratégia: Network First para páginas, Cache First para assets estáticos
self.addEventListener('fetch', event => {
    const url = new URL(event.request.url);

    // Ignora requisições POST/Livewire/AJAX
    if (event.request.method !== 'GET') return;
    if (url.pathname.startsWith('/livewire')) return;

    const isPage = event.request.mode === 'navigate';
    const isStaticAsset = url.hostname !== location.hostname;

    if (isStaticAsset) {
        // CDN assets: Cache First
        event.respondWith(
            caches.match(event.request).then(cached => {
                if (cached) return cached;
                return fetch(event.request).then(response => {
                    const clone = response.clone();
                    caches.open(CACHE).then(cache => cache.put(event.request, clone));
                    return response;
                });
            })
        );
    } else if (isPage) {
        // Páginas: Network First com fallback para cache
        event.respondWith(
            fetch(event.request)
                .then(response => {
                    const clone = response.clone();
                    caches.open(CACHE).then(cache => cache.put(event.request, clone));
                    return response;
                })
                .catch(() => caches.match(event.request))
        );
    }
});
