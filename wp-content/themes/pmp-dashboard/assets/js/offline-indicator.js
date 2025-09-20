/**
 * Offline Detection UI
 */

class OfflineIndicator {
    constructor() {
        this.indicator = null;
        this.init();
    }
    
    init() {
        this.createIndicator();
        this.bindEvents();
        this.updateStatus();
    }
    
    createIndicator() {
        this.indicator = document.createElement('div');
        this.indicator.id = 'offline-indicator';
        this.indicator.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: #dc2626;
            color: white;
            text-align: center;
            padding: 8px;
            font-size: 14px;
            z-index: 9999;
            transform: translateY(-100%);
            transition: transform 0.3s ease;
        `;
        this.indicator.textContent = '📡 You are offline - using cached content';
        document.body.appendChild(this.indicator);
    }
    
    bindEvents() {
        window.addEventListener('online', () => this.handleOnline());
        window.addEventListener('offline', () => this.handleOffline());
    }
    
    handleOnline() {
        this.indicator.style.background = '#059669';
        this.indicator.textContent = '✅ Back online - syncing data';
        this.show();
        
        setTimeout(() => this.hide(), 3000);
    }
    
    handleOffline() {
        this.indicator.style.background = '#dc2626';
        this.indicator.textContent = '📡 You are offline - using cached content';
        this.show();
    }
    
    show() {
        this.indicator.style.transform = 'translateY(0)';
    }
    
    hide() {
        this.indicator.style.transform = 'translateY(-100%)';
    }
    
    updateStatus() {
        if (!navigator.onLine) {
            this.handleOffline();
        }
    }
}

// Initialize when DOM ready
document.addEventListener('DOMContentLoaded', () => {
    new OfflineIndicator();
});
