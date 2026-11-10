/**
 * CBT Offline Service Worker
 * Menangani caching dan sync offline untuk jawaban ujian
 * 
 * @version 1.0.0
 */

const CACHE_NAME = 'cbt-v1';
const STATIC_ASSETS = [
    '/assets/app/css/cbt-ujian.css',
    '/assets/app/js/cbt-offline.js',
    '/assets/js/jquery.min.js',
    '/assets/js/bootstrap.bundle.min.js'
];

// Install event - cache static assets
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => {
                return cache.addAll(STATIC_ASSETS);
            })
            .then(() => {
                return self.skipWaiting();
            })
    );
});

// Activate event - clean up old caches
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames
                    .filter((name) => name !== CACHE_NAME)
                    .map((name) => caches.delete(name))
            );
        }).then(() => {
            return self.clients.claim();
        })
    );
});

// Fetch event - handle network requests
self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);

    // Handle save jawaban API dengan background sync
    if (url.pathname.includes('/saveJawaban') && request.method === 'POST') {
        event.respondWith(handleSaveJawaban(request));
        return;
    }

    // Static assets - cache first
    if (STATIC_ASSETS.includes(url.pathname)) {
        event.respondWith(
            caches.match(request)
                .then((response) => {
                    return response || fetch(request);
                })
        );
        return;
    }

    // Default: network first
    event.respondWith(
        fetch(request)
            .catch(() => {
                return caches.match(request);
            })
    );
});

/**
 * Handle save jawaban dengan background sync
 */
async function handleSaveJawaban(request) {
    try {
        // Clone request untuk retry jika gagal
        const clonedRequest = request.clone();
        
        // Coba kirim ke server
        const response = await fetch(request);
        
        if (response.ok) {
            return response;
        }
        
        throw new Error('Server error');
    } catch (error) {
        // Simpan ke IndexedDB untuk sync nanti
        await queueForSync(clonedRequest);
        
        // Return success response ke client
        // Client akan menangani sync status
        return new Response(
            JSON.stringify({
                status: true,
                message: 'Jawaban disimpan secara lokal, akan disinkronkan',
                offline: true
            }),
            {
                status: 200,
                headers: { 'Content-Type': 'application/json' }
            }
        );
    }
}

/**
 * Queue request untuk background sync
 */
async function queueForSync(request) {
    const formData = await request.formData();
    const data = {};
    formData.forEach((value, key) => {
        data[key] = value;
    });

    // Simpan ke IndexedDB
    const db = await openIndexedDB();
    const transaction = db.transaction('syncQueue', 'readwrite');
    const store = transaction.objectStore('syncQueue');

    await store.add({
        id: `${data.siswa}_${data.jadwal}_${data.id_soal}_${Date.now()}`,
        data: data,
        timestamp: Date.now(),
        attempts: 0,
        status: 'pending'
    });
}

/**
 * Open IndexedDB untuk sync queue
 */
function openIndexedDB() {
    return new Promise((resolve, reject) => {
        const request = indexedDB.open('cbt-offline-db', 1);

        request.onerror = () => reject(request.error);
        request.onsuccess = () => resolve(request.result);

        request.onupgradeneeded = (event) => {
            const db = event.target.result;
            
            // Store untuk sync queue
            if (!db.objectStoreNames.contains('syncQueue')) {
                const syncStore = db.createObjectStore('syncQueue', { keyPath: 'id' });
                syncStore.createIndex('status', 'status', { unique: false });
                syncStore.createIndex('timestamp', 'timestamp', { unique: false });
            }

            // Store untuk jawaban cache
            if (!db.objectStoreNames.contains('jawabanCache')) {
                const jawabanStore = db.createObjectStore('jawabanCache', { keyPath: 'key' });
                jawabanStore.createIndex('timestamp', 'timestamp', { unique: false });
            }
        };
    });
}

/**
 * Background sync event handler
 */
self.addEventListener('sync', (event) => {
    if (event.tag === 'sync-jawaban') {
        event.waitUntil(syncQueuedJawaban());
    }
});

/**
 * Sync jawaban yang di-queue
 */
async function syncQueuedJawaban() {
    const db = await openIndexedDB();
    const transaction = db.transaction('syncQueue', 'readonly');
    const store = transaction.objectStore('syncQueue');
    const index = store.index('status');

    const pending = await index.getAll('pending');

    for (const item of pending) {
        try {
            const response = await fetch('/cbtsiswa/saveJawaban', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams(item.data)
            });

            if (response.ok) {
                // Hapus dari queue
                const deleteTx = db.transaction('syncQueue', 'readwrite');
                const deleteStore = deleteTx.objectStore('syncQueue');
                await deleteStore.delete(item.id);
            } else {
                // Increment attempt count
                item.attempts++;
                if (item.attempts >= 3) {
                    item.status = 'failed';
                }
                
                const updateTx = db.transaction('syncQueue', 'readwrite');
                const updateStore = updateTx.objectStore('syncQueue');
                await updateStore.put(item);
            }
        } catch (error) {
            console.error('[SW] Sync failed:', error);
        }
    }
}

/**
 * Message handler dari main thread
 */
self.addEventListener('message', (event) => {
    if (event.data === 'sync-now') {
        syncQueuedJawaban();
    }
});
