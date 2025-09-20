/**
 * Offline Synchronization
 * Handles background sync and offline data management
 */

class OfflineSync {
    constructor() {
        this.dbName = 'pmp-offline-db';
        this.dbVersion = 1;
        this.db = null;
        this.syncQueue = [];
        
        this.init();
    }
    
    async init() {
        await this.initDB();
        this.registerSyncEvents();
        this.startPeriodicSync();
    }
    
    async initDB() {
        return new Promise((resolve, reject) => {
            const request = indexedDB.open(this.dbName, this.dbVersion);
            
            request.onerror = () => reject(request.error);
            request.onsuccess = () => {
                this.db = request.result;
                resolve();
            };
            
            request.onupgradeneeded = (event) => {
                const db = event.target.result;
                
                // Lessons store
                if (!db.objectStoreNames.contains('lessons')) {
                    const lessonsStore = db.createObjectStore('lessons', { keyPath: 'id' });
                    lessonsStore.createIndex('modified', 'modified');
                }
                
                // Progress store
                if (!db.objectStoreNames.contains('progress')) {
                    const progressStore = db.createObjectStore('progress', { keyPath: 'id' });
                    progressStore.createIndex('user_id', 'user_id');
                }
                
                // Sync metadata
                if (!db.objectStoreNames.contains('sync_meta')) {
                    db.createObjectStore('sync_meta', { keyPath: 'type' });
                }
            };
        });
    }
    
    registerSyncEvents() {
        // Register background sync
        if ('serviceWorker' in navigator && 'sync' in window.ServiceWorkerRegistration.prototype) {
            navigator.serviceWorker.ready.then(registration => {
                return registration.sync.register('background-sync');
            });
        }
        
        // Online/offline events
        window.addEventListener('online', () => this.handleOnline());
        window.addEventListener('offline', () => this.handleOffline());
    }
    
    async syncLessons() {
        const lastSync = await this.getLastSync('lessons');
        
        try {
            const response = await fetch(pmpSync.ajaxUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'sync_offline_data',
                    sync_type: 'lessons',
                    last_sync: lastSync,
                    nonce: pmpSync.nonce
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                await this.storeLessons(data.data);
                await this.updateLastSync('lessons');
            }
        } catch (error) {
            console.error('Lesson sync failed:', error);
        }
    }
    
    async storeLessons(lessons) {
        const transaction = this.db.transaction(['lessons'], 'readwrite');
        const store = transaction.objectStore('lessons');
        
        for (const lesson of lessons) {
            await store.put(lesson);
        }
    }
    
    async getOfflineLessons() {
        const transaction = this.db.transaction(['lessons'], 'readonly');
        const store = transaction.objectStore('lessons');
        
        return new Promise((resolve, reject) => {
            const request = store.getAll();
            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(request.error);
        });
    }
    
    async getLastSync(type) {
        const transaction = this.db.transaction(['sync_meta'], 'readonly');
        const store = transaction.objectStore('sync_meta');
        
        return new Promise((resolve) => {
            const request = store.get(type);
            request.onsuccess = () => {
                const result = request.result;
                resolve(result ? result.timestamp : null);
            };
            request.onerror = () => resolve(null);
        });
    }
    
    async updateLastSync(type) {
        const transaction = this.db.transaction(['sync_meta'], 'readwrite');
        const store = transaction.objectStore('sync_meta');
        
        await store.put({
            type: type,
            timestamp: new Date().toISOString()
        });
    }
    
    handleOnline() {
        console.log('Back online - syncing data');
        this.syncAll();
    }
    
    handleOffline() {
        console.log('Gone offline - using cached data');
    }
    
    async syncAll() {
        await this.syncLessons();
        // Add other sync operations
    }
    
    startPeriodicSync() {
        // Sync every 5 minutes when online
        setInterval(() => {
            if (navigator.onLine) {
                this.syncAll();
            }
        }, 5 * 60 * 1000);
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    if (typeof pmpSync !== 'undefined') {
        window.pmpOfflineSync = new OfflineSync();
    }
});
