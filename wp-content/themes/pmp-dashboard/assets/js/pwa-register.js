/**
 * PWA Registration and Management
 * Task: T003 - PWA Registration Script
 */

class PWAManager {
    constructor() {
        this.deferredPrompt = null;
        this.isInstalled = false;
        this.serviceWorker = null;
        
        this.init();
    }
    
    /**
     * Initialize PWA functionality
     */
    async init() {
        // Check if PWA is already installed
        this.checkInstallStatus();
        
        // Register service worker
        await this.registerServiceWorker();
        
        // Setup install prompt handling
        this.setupInstallPrompt();
        
        // Setup update detection
        this.setupUpdateDetection();
        
        // Track PWA events
        this.setupAnalytics();
        
        console.log('[PWA] PWA Manager initialized');
    }
    
    /**
     * Register service worker
     */
    async registerServiceWorker() {
        if (!('serviceWorker' in navigator)) {
            console.warn('[PWA] Service workers not supported');
            return;
        }
        
        try {
            const registration = await navigator.serviceWorker.register('/wp-content/themes/pmp-dashboard/assets/js/sw.js', {
                scope: '/'
            });
            
            this.serviceWorker = registration;
            
            console.log('[PWA] Service worker registered successfully');
            
            // Handle updates
            registration.addEventListener('updatefound', () => {
                this.handleServiceWorkerUpdate(registration);
            });
            
            // Check for existing updates
            if (registration.waiting) {
                this.showUpdateAvailable();
            }
            
            // Track registration
            this.trackEvent('sw_registered', {
                scope: registration.scope,
                updateViaCache: registration.updateViaCache
            });
            
        } catch (error) {
            console.error('[PWA] Service worker registration failed:', error);
            this.trackEvent('sw_registration_failed', { error: error.message });
        }
    }
    
    /**
     * Handle service worker updates
     */
    handleServiceWorkerUpdate(registration) {
        const newWorker = registration.installing;
        
        newWorker.addEventListener('statechange', () => {
            if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                this.showUpdateAvailable();
            }
        });
    }
    
    /**
     * Show update available notification
     */
    showUpdateAvailable() {
        const updateBanner = document.createElement('div');
        updateBanner.className = 'pwa-update-banner';
        updateBanner.innerHTML = `
            <div class="update-content">
                <span class="update-message">A new version is available!</span>
                <button class="update-btn" onclick="pwaManager.applyUpdate()">Update</button>
                <button class="dismiss-btn" onclick="this.parentElement.parentElement.remove()">×</button>
            </div>
        `;
        
        document.body.appendChild(updateBanner);
        
        this.trackEvent('update_available_shown');
    }
    
    /**
     * Apply service worker update
     */
    async applyUpdate() {
        if (!this.serviceWorker || !this.serviceWorker.waiting) {
            return;
        }
        
        this.serviceWorker.waiting.postMessage({ type: 'SKIP_WAITING' });
        
        // Reload page after update
        navigator.serviceWorker.addEventListener('controllerchange', () => {
            window.location.reload();
        });
        
        this.trackEvent('update_applied');
    }
    
    /**
     * Setup install prompt handling
     */
    setupInstallPrompt() {
        // Listen for beforeinstallprompt event
        window.addEventListener('beforeinstallprompt', (e) => {
            console.log('[PWA] Install prompt available');
            
            // Prevent default prompt
            e.preventDefault();
            
            // Store the event
            this.deferredPrompt = e;
            
            // Check if we should show custom prompt
            if (this.shouldShowInstallPrompt()) {
                this.showInstallPrompt();
            }
            
            this.trackEvent('install_prompt_available');
        });
        
        // Listen for app installed event
        window.addEventListener('appinstalled', () => {
            console.log('[PWA] App installed successfully');
            this.isInstalled = true;
            this.hideInstallPrompt();
            this.trackEvent('app_installed');
        });
    }
    
    /**
     * Check if install prompt should be shown
     */
    shouldShowInstallPrompt() {
        // Check user criteria
        const visitCount = parseInt(localStorage.getItem('pwa_visit_count') || '0');
        const hasCompletedLesson = localStorage.getItem('pwa_completed_lesson') === 'true';
        const promptDismissed = localStorage.getItem('pwa_prompt_dismissed');
        const lastDismissed = parseInt(localStorage.getItem('pwa_prompt_dismissed_time') || '0');
        
        // Don't show if recently dismissed (within 7 days)
        if (promptDismissed && (Date.now() - lastDismissed) < 7 * 24 * 60 * 60 * 1000) {
            return false;
        }
        
        // Show if user has visited 3+ times and completed a lesson
        return visitCount >= 3 && hasCompletedLesson;
    }
    
    /**
     * Show custom install prompt
     */
    showInstallPrompt() {
        const installBanner = document.createElement('div');
        installBanner.id = 'pwa-install-banner';
        installBanner.className = 'pwa-install-banner';
        installBanner.innerHTML = `
            <div class="install-content">
                <div class="install-icon">
                    <img src="/wp-content/themes/pmp-dashboard/assets/icons/icon-96.png" alt="PMP Prep">
                </div>
                <div class="install-text">
                    <h3>Install PMP Prep</h3>
                    <p>Get quick access and study offline</p>
                </div>
                <div class="install-actions">
                    <button class="install-btn" onclick="pwaManager.promptInstall()">Install</button>
                    <button class="dismiss-btn" onclick="pwaManager.dismissInstallPrompt()">Not now</button>
                </div>
            </div>
        `;
        
        document.body.appendChild(installBanner);
        
        // Auto-hide after 10 seconds
        setTimeout(() => {
            if (document.getElementById('pwa-install-banner')) {
                this.dismissInstallPrompt();
            }
        }, 10000);
        
        this.trackEvent('install_prompt_shown');
    }
    
    /**
     * Prompt user to install
     */
    async promptInstall() {
        if (!this.deferredPrompt) {
            console.warn('[PWA] No install prompt available');
            return;
        }
        
        try {
            // Show the install prompt
            this.deferredPrompt.prompt();
            
            // Wait for user response
            const { outcome } = await this.deferredPrompt.userChoice;
            
            console.log('[PWA] Install prompt outcome:', outcome);
            
            this.trackEvent('install_prompt_responded', { outcome });
            
            // Clear the prompt
            this.deferredPrompt = null;
            
            // Hide the banner
            this.hideInstallPrompt();
            
        } catch (error) {
            console.error('[PWA] Install prompt error:', error);
            this.trackEvent('install_prompt_error', { error: error.message });
        }
    }
    
    /**
     * Dismiss install prompt
     */
    dismissInstallPrompt() {
        this.hideInstallPrompt();
        
        // Store dismissal
        localStorage.setItem('pwa_prompt_dismissed', 'true');
        localStorage.setItem('pwa_prompt_dismissed_time', Date.now().toString());
        
        this.trackEvent('install_prompt_dismissed');
    }
    
    /**
     * Hide install prompt
     */
    hideInstallPrompt() {
        const banner = document.getElementById('pwa-install-banner');
        if (banner) {
            banner.remove();
        }
    }
    
    /**
     * Check if PWA is installed
     */
    checkInstallStatus() {
        // Check if running in standalone mode
        if (window.matchMedia('(display-mode: standalone)').matches) {
            this.isInstalled = true;
            document.body.classList.add('pwa-installed');
            this.trackEvent('pwa_launched_standalone');
        }
        
        // Check if running as PWA on iOS
        if (window.navigator.standalone === true) {
            this.isInstalled = true;
            document.body.classList.add('pwa-installed', 'pwa-ios');
            this.trackEvent('pwa_launched_ios');
        }
        
        // Update visit count
        const visitCount = parseInt(localStorage.getItem('pwa_visit_count') || '0') + 1;
        localStorage.setItem('pwa_visit_count', visitCount.toString());
    }
    
    /**
     * Setup update detection
     */
    setupUpdateDetection() {
        // Check for updates every 30 minutes
        setInterval(() => {
            if (this.serviceWorker) {
                this.serviceWorker.update();
            }
        }, 30 * 60 * 1000);
        
        // Check for updates on page focus
        window.addEventListener('focus', () => {
            if (this.serviceWorker) {
                this.serviceWorker.update();
            }
        });
    }
    
    /**
     * Setup PWA analytics
     */
    setupAnalytics() {
        // Track PWA usage
        this.trackEvent('pwa_session_start', {
            isInstalled: this.isInstalled,
            displayMode: this.getDisplayMode(),
            userAgent: navigator.userAgent
        });
        
        // Track page visibility changes
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.trackEvent('pwa_session_hidden');
            } else {
                this.trackEvent('pwa_session_visible');
            }
        });
        
        // Track beforeunload
        window.addEventListener('beforeunload', () => {
            this.trackEvent('pwa_session_end');
        });
    }
    
    /**
     * Get current display mode
     */
    getDisplayMode() {
        if (window.matchMedia('(display-mode: standalone)').matches) {
            return 'standalone';
        }
        if (window.matchMedia('(display-mode: minimal-ui)').matches) {
            return 'minimal-ui';
        }
        if (window.matchMedia('(display-mode: fullscreen)').matches) {
            return 'fullscreen';
        }
        return 'browser';
    }
    
    /**
     * Track PWA events
     */
    trackEvent(eventName, data = {}) {
        // Send to analytics
        if (typeof gtag !== 'undefined') {
            gtag('event', eventName, {
                event_category: 'PWA',
                ...data
            });
        }
        
        // Send to WordPress analytics
        if (typeof pmpAnalytics !== 'undefined') {
            pmpAnalytics.track(eventName, data);
        }
        
        console.log('[PWA Analytics]', eventName, data);
    }
    
    /**
     * Get PWA status
     */
    getStatus() {
        return {
            isInstalled: this.isInstalled,
            hasServiceWorker: !!this.serviceWorker,
            canInstall: !!this.deferredPrompt,
            displayMode: this.getDisplayMode(),
            visitCount: parseInt(localStorage.getItem('pwa_visit_count') || '0')
        };
    }
}

// Initialize PWA Manager
let pwaManager;

// Wait for DOM to be ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        pwaManager = new PWAManager();
    });
} else {
    pwaManager = new PWAManager();
}

// Export for global access
window.pwaManager = pwaManager;
