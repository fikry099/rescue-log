const CACHE_NAME = 'sigap-subposko-cache-v23';

// DAFTAR RUTE & ASET UTAMA SUB-POSKO LAPANGAN
const ASSETS_TO_CACHE = [
    '/lapangan/dashboard',
    '/lapangan/pengungsi',
    '/lapangan/pengungsi/create',
    '/lapangan/pengajuan',
    '/lapangan/penyaluran',
    '/lapangan/stok',
    '/lapangan/ambulans',
    '/img/Rescue-log.png',
    '/favicon.png',
    'https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&display=swap',
    'https://cdn.jsdelivr.net/npm/sweetalert2@11'
];

// 1. INSTALL EVENT: Pre-cache Rute Utama saat Online
self.addEventListener('install', (event) => {
    console.log('[PWA SW Lapangan] Installing Fast Cache v23...');
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return Promise.allSettled(
                ASSETS_TO_CACHE.map((url) => {
                    const req = new Request(url, { credentials: 'same-origin' });
                    return fetch(req).then((response) => {
                        if (response.status === 200) {
                            return cache.put(url, response);
                        }
                    }).catch((err) => {
                        console.warn('[PWA SW Pre-cache Skip]:', url, err);
                    });
                })
            );
        }).then(() => self.skipWaiting())
    );
});

// 2. ACTIVATE EVENT: Bersihkan Cache Versi Lama
self.addEventListener('activate', (event) => {
    console.log('[PWA SW Lapangan] Activating v23...');
    event.waitUntil(
        caches.keys().then((keys) => {
            return Promise.all(
                keys.map((key) => {
                    if (key !== CACHE_NAME) {
                        return caches.delete(key);
                    }
                })
            );
        }).then(() => self.clients.claim())
    );
});

// 3. FETCH EVENT: STRATEGI PURE CACHE-FIRST UNTUK KECEPATAN INSTAN (< 10ms)
self.addEventListener('fetch', (event) => {
    const url = new URL(event.request.url);

    // Abaikan request non-GET atau ping heartbeat
    if (event.request.method !== 'GET' || url.pathname.includes('/ping') || url.search.includes('check=')) {
        return;
    }

    // A. PENANGANAN HALAMAN HTML (MENU LAPANGAN)
    if (event.request.mode === 'navigate' || event.request.headers.get('accept')?.includes('text/html')) {
        event.respondWith(
            caches.match(event.request).then((cachedResponse) => {
                // ⚡ JIKA ADA DI CACHE: Buka LANGSUNG tanpa menunggu jaringan (0-10ms)
                if (cachedResponse) {
                    // Update cache di background secara silent HANYA jika sedang Online
                    if (navigator.onLine) {
                        fetch(event.request).then((networkResponse) => {
                            if (networkResponse && networkResponse.status === 200 && networkResponse.type === 'basic') {
                                caches.open(CACHE_NAME).then((cache) => cache.put(event.request, networkResponse));
                            }
                        }).catch(() => {});
                    }
                    return cachedResponse;
                }

                // Jika belum ter-cache, baru fetch dari network
                return fetch(event.request)
                    .then((networkResponse) => {
                        if (networkResponse && networkResponse.status === 200 && networkResponse.type === 'basic') {
                            const responseClone = networkResponse.clone();
                            caches.open(CACHE_NAME).then((cache) => cache.put(event.request, responseClone));
                        }
                        return networkResponse;
                    })
                    .catch(() => {
                        return caches.match('/lapangan/dashboard');
                    });
            })
        );
        return;
    }

    // B. PENANGANAN ASET STATIS (CSS, JS, GAMBAR)
    event.respondWith(
        caches.match(event.request).then((cachedResponse) => {
            if (cachedResponse) {
                return cachedResponse;
            }
            return fetch(event.request)
                .then((networkResponse) => {
                    if (networkResponse && networkResponse.status === 200) {
                        const responseClone = networkResponse.clone();
                        caches.open(CACHE_NAME).then((cache) => cache.put(event.request, responseClone));
                    }
                    return networkResponse;
                })
                .catch(() => {
                    return new Response('', { status: 504, statusText: 'Offline - Asset Unavailable' });
                });
        })
    );
});