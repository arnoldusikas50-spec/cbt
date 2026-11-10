/**
 * CBT Offline Manager
 * Mengelola auto-save, offline detection, dan sync jawaban
 * 
 * @version 1.0.0
 */
class CbtOfflineManager {
    constructor() {
        this.db = null;
        this.syncQueue = [];
        this.isOnline = navigator.onLine;
        this.syncInterval = null;
        this.maxRetries = 3;
        this.retryDelay = 5000; // 5 seconds
        
        this.init();
    }

    async init() {
        await this.initIndexedDB();
        this.setupEventListeners();
        this.startSyncInterval();
        this.registerServiceWorker();
    }

    /**
     * Initialize IndexedDB
     */
    async initIndexedDB() {
        return new Promise((resolve, reject) => {
            const request = indexedDB.open('cbt-offline-db', 1);

            request.onerror = () => {
                console.error('[CbtOffline] IndexedDB error:', request.error);
                reject(request.error);
            };

            request.onsuccess = () => {
                this.db = request.result;
                console.log('[CbtOffline] IndexedDB initialized');
                resolve(this.db);
            };

            request.onupgradeneeded = (event) => {
                const db = event.target.result;

                // Sync queue store
                if (!db.objectStoreNames.contains('syncQueue')) {
                    const syncStore = db.createObjectStore('syncQueue', { keyPath: 'id' });
                    syncStore.createIndex('status', 'status', { unique: false });
                    syncStore.createIndex('timestamp', 'timestamp', { unique: false });
                }

                // Jawaban cache store
                if (!db.objectStoreNames.contains('jawabanCache')) {
                    const jawabanStore = db.createObjectStore('jawabanCache', { keyPath: 'key' });
                    jawabanStore.createIndex('timestamp', 'timestamp', { unique: false });
                }
            };
        });
    }

    /**
     * Register Service Worker
     */
    async registerServiceWorker() {
        if ('serviceWorker' in navigator) {
            try {
                const registration = await navigator.serviceWorker.register('/assets/app/js/sw-cbt.js');
                console.log('[CbtOffline] SW registered:', registration.scope);
            } catch (error) {
                console.error('[CbtOffline] SW registration failed:', error);
            }
        }
    }

    /**
     * Setup event listeners untuk online/offline
     */
    setupEventListeners() {
        window.addEventListener('online', () => {
            this.isOnline = true;
            this.showNotification('Koneksi tersambung. Menyinkronkan jawaban...', 'success');
            this.syncNow();
        });

        window.addEventListener('offline', () => {
            this.isOnline = false;
            this.showNotification('Koneksi terputus. Jawaban akan disimpan lokal.', 'warning');
        });

        // Before unload - attempt sync
        window.addEventListener('beforeunload', (e) => {
            if (this.syncQueue.length > 0) {
                e.preventDefault();
                e.returnValue = 'Masih ada jawaban yang belum tersimpan. Yakin ingin keluar?';
            }
        });
    }

    /**
     * Simpan jawaban dengan queue management
     */
    async saveAnswer(data) {
        const id = `${data.siswa}_${data.jadwal}_${data.id_soal}_${Date.now()}`;
        
        // Tambahkan ke queue
        const queueItem = {
            id: id,
            data: data,
            timestamp: Date.now(),
            attempts: 0,
            status: 'pending'
        };

        // Simpan ke IndexedDB
        await this.addToQueue(queueItem);

        // Coba kirim langsung jika online
        if (this.isOnline) {
            return await this.trySendImmediate(queueItem);
        }

        // Return success dengan status queued
        return {
            status: true,
            offline: true,
            message: 'Jawaban disimpan lokal'
        };
    }

    /**
     * Tambahkan ke IndexedDB queue
     */
    async addToQueue(item) {
        return new Promise((resolve, reject) => {
            const transaction = this.db.transaction('syncQueue', 'readwrite');
            const store = transaction.objectStore('syncQueue');
            const request = store.put(item);

            request.onsuccess = () => resolve();
            request.onerror = () => reject(request.error);
        });
    }

    /**
     * Coba kirim jawaban langsung
     */
    async trySendImmediate(queueItem) {
        try {
            const response = await this.sendToServer(queueItem.data);
            
            if (response.status) {
                // Hapus dari queue
                await this.removeFromQueue(queueItem.id);
                return response;
            }
            
            throw new Error(response.message || 'Save failed');
        } catch (error) {
            console.error('[CbtOffline] Immediate send failed:', error);
            return {
                status: true,
                offline: true,
                message: 'Jawaban akan disinkronkan nanti'
            };
        }
    }

    /**
     * Kirim jawaban ke server
     */
    async sendToServer(data) {
        const formData = new FormData();
        Object.keys(data).forEach(key => {
            formData.append(key, data[key]);
        });

        const response = await fetch(base_url + 'cbt/saveJawaban', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        return await response.json();
    }

    /**
     * Hapus dari queue
     */
    async removeFromQueue(id) {
        return new Promise((resolve, reject) => {
            const transaction = this.db.transaction('syncQueue', 'readwrite');
            const store = transaction.objectStore('syncQueue');
            const request = store.delete(id);

            request.onsuccess = () => resolve();
            request.onerror = () => reject(request.error);
        });
    }

    /**
     * Start interval untuk auto-sync
     */
    startSyncInterval() {
        // Sync setiap 30 detik
        this.syncInterval = setInterval(() => {
            if (this.isOnline) {
                this.syncNow();
            }
        }, 30000);
    }

    /**
     * Sync semua jawaban yang pending
     */
    async syncNow() {
        if (!this.isOnline) return;

        try {
            const pendingItems = await this.getPendingItems();
            
            if (pendingItems.length === 0) return;

            this.showSyncIndicator(true);

            for (const item of pendingItems) {
                try {
                    const response = await this.sendToServer(item.data);
                    
                    if (response.status) {
                        await this.removeFromQueue(item.id);
                    } else {
                        await this.incrementAttempt(item);
                    }
                } catch (error) {
                    console.error('[CbtOffline] Sync item failed:', error);
                    await this.incrementAttempt(item);
                }
            }

            this.showSyncIndicator(false);
        } catch (error) {
            console.error('[CbtOffline] Sync failed:', error);
            this.showSyncIndicator(false);
        }
    }

    /**
     * Ambil semua item pending dari IndexedDB
     */
    async getPendingItems() {
        return new Promise((resolve, reject) => {
            const transaction = this.db.transaction('syncQueue', 'readonly');
            const store = transaction.objectStore('syncQueue');
            const index = store.index('status');
            const request = index.getAll('pending');

            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(request.error);
        });
    }

    /**
     * Increment attempt count dan update status
     */
    async incrementAttempt(item) {
        item.attempts++;
        
        if (item.attempts >= this.maxRetries) {
            item.status = 'failed';
            this.showNotification(`Gagal menyimpan jawaban untuk soal ${item.data.id_soal}. Akan dicoba lagi nanti.`, 'error');
        }

        await this.addToQueue(item);
    }

    /**
     * Show sync indicator
     */
    showSyncIndicator(show) {
        let indicator = document.getElementById('sync-indicator');
        
        if (!indicator) {
            indicator = document.createElement('div');
            indicator.id = 'sync-indicator';
            indicator.className = 'sync-indicator';
            indicator.innerHTML = '<i class="bi bi-cloud-arrow-up"></i> Menyinkronkan...';
            document.body.appendChild(indicator);
        }

        indicator.style.display = show ? 'block' : 'none';
    }

    /**
     * Show notification
     */
    showNotification(message, type = 'info') {
        // Gunakan toast atau notification system yang sudah ada
        if (typeof showToast === 'function') {
            showToast(message, type);
        } else {
            console.log(`[CbtOffline] ${type}: ${message}`);
        }
    }

    /**
     * Get sync status
     */
    getStatus() {
        return {
            isOnline: this.isOnline,
            pendingCount: this.syncQueue.length,
            dbReady: this.db !== null
        };
    }
}

// Initialize offline manager
const cbtOfflineManager = new CbtOfflineManager();

// Override fungsi saveJawaban asli
window.originalSaveJawaban = window.saveJawaban;

window.saveJawaban = async function(data, callback) {
    try {
        const result = await cbtOfflineManager.saveAnswer(data);
        
        if (callback && typeof callback === 'function') {
            callback(result);
        }
        
        return result;
    } catch (error) {
        console.error('[CbtOffline] Save error:', error);
        
        // Fallback ke original function
        if (window.originalSaveJawaban) {
            return window.originalSaveJawaban(data, callback);
        }
        
        return { status: false, message: error.message };
    }
};

// Export untuk global access
window.cbtOfflineManager = cbtOfflineManager;
