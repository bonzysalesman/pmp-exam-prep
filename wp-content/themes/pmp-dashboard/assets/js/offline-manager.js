/**
 * Offline Detection & UI Manager
 * Task: T005 - Offline Detection & UI
 */

class OfflineManager {
    constructor() {
        this.isOnline = navigator.onLine;
        this.connectionType = this.getConnectionType();
        this.indicator = null;
        this.syncQueue = [];
        
        this.init();
    }
    
    /**
     * Initialize offline management
     */
    init() {
        this.createOfflineIndicator();
        this.setupEventListeners();
        this.updateConnectionStatus();
        this.setupSyncQueue();
        
        console.log('[Offline] Manager initialized');
    }
    
    /**
     * Create offline indicator UI
     */
    createOfflineIndicator() {
        this.indicator = document.createElement('div');
        this.indicator.id = 'offline-indicator';
        this.indicator.className = 'offline-indicator hidden';
        this.indicator.innerHTML = `
            <div class="offline-content">
                <div class="offline-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M23.64 7c-.45-.34-4.93-4-11.64-4-1.5 0-2.89.19-4.15.48L18.18 13.8 23.64 7zm-6.6 8.22L3.27 1.44 2 2.72l2.05 2.06C1.91 5.76.59 6.82.36 7l11.63 14.49.01.01.01-.01 3.9-4.86 3.32 3.32 1.27-1.27-3.46-3.46z"/>
                    </svg>
                </div>
                <div class="offline-text">
                    <span class="offline-title">You're offline</span>
                    <span class="offline-subtitle">Some features may be limited</span>
                </div>
                <div class="offline-actions">
                    <button class="retry-btn" onclick="offlineManager.checkConnection()">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M17.65 6.35C16.2 4.9 14.21 4 12 4c-4.42 0-7.99 3.58-7.99 8s3.57 8 7.99 8c3.73 0 6.84-2.55 7.73-6h-2.08c-.82 2.33-3.04 4-5.65 4-3.31 0-6-2.69-6-6s2.69-6 6-6c1.66 0 3.14.69 4.22 1.78L13 11h7V4l-2.35 2.35z"/>
                        </svg>
                    </button>
                </div>
            </div>
        `;
        
        document.body.appendChild(this.indicator);
    }
    
    /**
     * Setup event listeners
     */
    setupEventListeners() {
        // Online/offline events
        window.addEventListener('online', () => {
            this.handleOnline();
        });
        
        window.addEventListener('offline', () => {
            this.handleOffline();
        });
        
        // Connection change detection
        if ('connection' in navigator) {
            navigator.connection.addEventListener('change', () => {
                this.handleConnectionChange();
            });
        }
        
        // Page visibility for connection checks
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                this.checkConnection();
            }
        });
        
        // Periodic connection check
        setInterval(() => {
            this.checkConnection();
        }, 30000); // Check every 30 seconds
    }
    
    /**
     * Handle online event
     */
    handleOnline() {
        console.log('[Offline] Connection restored');
        
        this.isOnline = true;
        this.hideOfflineIndicator();
        this.showConnectionRestored();
        this.processSyncQueue();
        
        // Update UI state
        document.body.classList.remove('offline');
        document.body.classList.add('online');
        
        // Trigger custom event
        this.dispatchConnectionEvent('online');
    }
    
    /**
     * Handle offline event
     */
    handleOffline() {
        console.log('[Offline] Connection lost');
        
        this.isOnline = false;
        this.showOfflineIndicator();
        
        // Update UI state
        document.body.classList.remove('online');
        document.body.classList.add('offline');
        
        // Trigger custom event
        this.dispatchConnectionEvent('offline');
    }
    
    /**
     * Handle connection change
     */
    handleConnectionChange() {
        this.connectionType = this.getConnectionType();
        
        console.log('[Offline] Connection changed:', this.connectionType);
        
        // Update connection quality indicator
        this.updateConnectionQuality();
        
        // Trigger custom event
        this.dispatchConnectionEvent('connection-change', {
            type: this.connectionType,
            quality: this.getConnectionQuality()
        });
    }
    
    /**
     * Check connection status
     */
    async checkConnection() {
        try {
            // Try to fetch a small resource
            const response = await fetch('/wp-content/themes/pmp-dashboard/manifest.json', {
                method: 'HEAD',
                cache: 'no-cache'
            });
            
            if (response.ok && !this.isOnline) {
                this.handleOnline();
            }
        } catch (error) {
            if (this.isOnline) {
                this.handleOffline();
            }
        }
    }
    
    /**
     * Get connection type
     */
    getConnectionType() {
        if (!('connection' in navigator)) {
            return 'unknown';
        }
        
        const connection = navigator.connection;
        return connection.effectiveType || connection.type || 'unknown';
    }
    
    /**
     * Get connection quality
     */
    getConnectionQuality() {
        const type = this.connectionType;
        
        if (type === '4g') return 'excellent';
        if (type === '3g') return 'good';
        if (type === '2g') return 'poor';
        if (type === 'slow-2g') return 'very-poor';
        
        return 'unknown';
    }
    
    /**
     * Show offline indicator
     */
    showOfflineIndicator() {
        if (this.indicator) {
            this.indicator.classList.remove('hidden');
            this.indicator.classList.add('visible');
        }
    }
    
    /**
     * Hide offline indicator
     */
    hideOfflineIndicator() {
        if (this.indicator) {
            this.indicator.classList.remove('visible');
            this.indicator.classList.add('hidden');
        }
    }
    
    /**
     * Show connection restored notification
     */
    showConnectionRestored() {
        const notification = document.createElement('div');
        notification.className = 'connection-restored-notification';
        notification.innerHTML = `
            <div class="notification-content">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                </svg>
                <span>Connection restored</span>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        // Auto-remove after 3 seconds
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }
    
    /**
     * Update connection quality indicator
     */
    updateConnectionQuality() {
        const quality = this.getConnectionQuality();
        
        // Update existing quality indicators
        const qualityElements = document.querySelectorAll('.connection-quality');
        qualityElements.forEach(element => {
            element.className = `connection-quality quality-${quality}`;
            element.textContent = quality.charAt(0).toUpperCase() + quality.slice(1);
        });
    }
    
    /**
     * Update connection status
     */
    updateConnectionStatus() {
        if (this.isOnline) {
            this.handleOnline();
        } else {
            this.handleOffline();
        }
    }
    
    /**
     * Setup sync queue for offline actions
     */
    setupSyncQueue() {
        // Load existing queue from localStorage
        const stored = localStorage.getItem('pwa_sync_queue');
        if (stored) {
            try {
                this.syncQueue = JSON.parse(stored);
            } catch (error) {
                console.error('[Offline] Failed to load sync queue:', error);
                this.syncQueue = [];
            }
        }
    }
    
    /**
     * Add action to sync queue
     */
    addToSyncQueue(action) {
        const queueItem = {
            id: Date.now() + Math.random(),
            action: action.type,
            data: action.data,
            timestamp: Date.now(),
            retries: 0
        };
        
        this.syncQueue.push(queueItem);
        this.saveSyncQueue();
        
        console.log('[Offline] Added to sync queue:', queueItem);
        
        // Try to sync immediately if online
        if (this.isOnline) {
            this.processSyncQueue();
        }
    }
    
    /**
     * Process sync queue
     */
    async processSyncQueue() {
        if (!this.isOnline || this.syncQueue.length === 0) {
            return;
        }
        
        console.log('[Offline] Processing sync queue:', this.syncQueue.length, 'items');
        
        const processedItems = [];
        
        for (const item of this.syncQueue) {
            try {
                await this.syncItem(item);
                processedItems.push(item);
                console.log('[Offline] Synced item:', item.id);
            } catch (error) {
                console.error('[Offline] Sync failed for item:', item.id, error);
                
                // Increment retry count
                item.retries++;
                
                // Remove if too many retries
                if (item.retries >= 3) {
                    processedItems.push(item);
                    console.warn('[Offline] Removing item after 3 failed attempts:', item.id);
                }
            }
        }
        
        // Remove processed items
        this.syncQueue = this.syncQueue.filter(item => !processedItems.includes(item));
        this.saveSyncQueue();
        
        if (processedItems.length > 0) {
            this.showSyncComplete(processedItems.length);
        }
    }
    
    /**
     * Sync individual item
     */
    async syncItem(item) {
        const { action, data } = item;
        
        switch (action) {
            case 'progress_update':
                return this.syncProgressUpdate(data);
            case 'test_completion':
                return this.syncTestCompletion(data);
            case 'bookmark_action':
                return this.syncBookmarkAction(data);
            default:
                throw new Error(`Unknown sync action: ${action}`);
        }
    }
    
    /**
     * Sync progress update
     */
    async syncProgressUpdate(data) {
        const response = await fetch('/wp-json/pmp/v1/progress/sync', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': window.pmpAjax?.nonce || ''
            },
            body: JSON.stringify(data)
        });
        
        if (!response.ok) {
            throw new Error(`Sync failed: ${response.status}`);
        }
        
        return response.json();
    }
    
    /**
     * Sync test completion
     */
    async syncTestCompletion(data) {
        const response = await fetch('/wp-json/pmp/v1/test/sync-results', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': window.pmpAjax?.nonce || ''
            },
            body: JSON.stringify(data)
        });
        
        if (!response.ok) {
            throw new Error(`Sync failed: ${response.status}`);
        }
        
        return response.json();
    }
    
    /**
     * Sync bookmark action
     */
    async syncBookmarkAction(data) {
        const response = await fetch('/wp-json/pmp/v1/bookmark/sync', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': window.pmpAjax?.nonce || ''
            },
            body: JSON.stringify(data)
        });
        
        if (!response.ok) {
            throw new Error(`Sync failed: ${response.status}`);
        }
        
        return response.json();
    }
    
    /**
     * Save sync queue to localStorage
     */
    saveSyncQueue() {
        try {
            localStorage.setItem('pwa_sync_queue', JSON.stringify(this.syncQueue));
        } catch (error) {
            console.error('[Offline] Failed to save sync queue:', error);
        }
    }
    
    /**
     * Show sync complete notification
     */
    showSyncComplete(count) {
        const notification = document.createElement('div');
        notification.className = 'sync-complete-notification';
        notification.innerHTML = `
            <div class="notification-content">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M9 16.2L4.8 12l-1.4 1.4L9 19 21 7l-1.4-1.4L9 16.2z"/>
                </svg>
                <span>Synced ${count} item${count > 1 ? 's' : ''}</span>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        // Auto-remove after 2 seconds
        setTimeout(() => {
            notification.remove();
        }, 2000);
    }
    
    /**
     * Dispatch connection event
     */
    dispatchConnectionEvent(type, detail = {}) {
        const event = new CustomEvent(`pwa-${type}`, {
            detail: {
                isOnline: this.isOnline,
                connectionType: this.connectionType,
                ...detail
            }
        });
        
        window.dispatchEvent(event);
    }
    
    /**
     * Get current status
     */
    getStatus() {
        return {
            isOnline: this.isOnline,
            connectionType: this.connectionType,
            connectionQuality: this.getConnectionQuality(),
            syncQueueLength: this.syncQueue.length
        };
    }
}

// Initialize offline manager
let offlineManager;

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        offlineManager = new OfflineManager();
    });
} else {
    offlineManager = new OfflineManager();
}

// Export for global access
window.offlineManager = offlineManager;
