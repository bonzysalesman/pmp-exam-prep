/**
 * Enhanced PWA Install Prompt
 * Task: T006 - PWA Install Prompt
 */

class InstallPrompt {
    constructor() {
        this.deferredPrompt = null;
        this.isInstalled = false;
        this.criteria = {
            minVisits: 3,
            minEngagement: 300, // seconds
            hasCompletedLesson: true,
            minSessionTime: 120 // seconds
        };
        
        this.init();
    }
    
    /**
     * Initialize install prompt
     */
    init() {
        this.checkInstallStatus();
        this.setupEventListeners();
        this.trackEngagement();
        
        console.log('[Install] Prompt manager initialized');
    }
    
    /**
     * Setup event listeners
     */
    setupEventListeners() {
        // Listen for beforeinstallprompt
        window.addEventListener('beforeinstallprompt', (e) => {
            console.log('[Install] Install prompt available');
            
            e.preventDefault();
            this.deferredPrompt = e;
            
            // Check criteria and show prompt
            if (this.shouldShowPrompt()) {
                setTimeout(() => {
                    this.showCustomPrompt();
                }, 2000); // Delay to avoid interrupting user
            }
            
            this.trackEvent('prompt_available');
        });
        
        // Listen for app installed
        window.addEventListener('appinstalled', () => {
            console.log('[Install] App installed');
            this.isInstalled = true;
            this.hidePrompt();
            this.showInstallSuccess();
            this.trackEvent('app_installed');
        });
        
        // Listen for page visibility
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                this.updateEngagement();
            }
        });
    }
    
    /**
     * Check if install prompt should be shown
     */
    shouldShowPrompt() {
        // Don't show if already installed
        if (this.isInstalled) {
            return false;
        }
        
        // Don't show if no deferred prompt
        if (!this.deferredPrompt) {
            return false;
        }
        
        // Check dismissal history
        const dismissals = this.getDismissalHistory();
        if (dismissals.length >= 3) {
            console.log('[Install] Too many dismissals, not showing prompt');
            return false;
        }
        
        // Check recent dismissal (within 7 days)
        const lastDismissal = dismissals[dismissals.length - 1];
        if (lastDismissal && (Date.now() - lastDismissal) < 7 * 24 * 60 * 60 * 1000) {
            return false;
        }
        
        // Check user criteria
        return this.checkUserCriteria();
    }
    
    /**
     * Check user criteria for install prompt
     */
    checkUserCriteria() {
        const visitCount = this.getVisitCount();
        const totalEngagement = this.getTotalEngagement();
        const hasCompletedLesson = this.hasCompletedLesson();
        const currentSessionTime = this.getCurrentSessionTime();
        
        console.log('[Install] Criteria check:', {
            visitCount,
            totalEngagement,
            hasCompletedLesson,
            currentSessionTime
        });
        
        return (
            visitCount >= this.criteria.minVisits &&
            totalEngagement >= this.criteria.minEngagement &&
            hasCompletedLesson &&
            currentSessionTime >= this.criteria.minSessionTime
        );
    }
    
    /**
     * Show custom install prompt
     */
    showCustomPrompt() {
        // Remove existing prompt
        this.hidePrompt();
        
        const prompt = document.createElement('div');
        prompt.id = 'pwa-install-prompt';
        prompt.className = 'pwa-install-prompt';
        prompt.innerHTML = `
            <div class="prompt-overlay"></div>
            <div class="prompt-content">
                <div class="prompt-header">
                    <div class="prompt-icon">
                        <img src="/wp-content/themes/pmp-dashboard/assets/icons/icon-96.png" alt="PMP Prep">
                    </div>
                    <button class="prompt-close" onclick="installPrompt.dismiss()">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
                        </svg>
                    </button>
                </div>
                
                <div class="prompt-body">
                    <h3>Install PMP Exam Prep</h3>
                    <p>Get the full app experience with:</p>
                    
                    <ul class="prompt-features">
                        <li>
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                            </svg>
                            <span>Study offline without internet</span>
                        </li>
                        <li>
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.89 2 2 2zm6-6v-5c0-3.07-1.64-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.63 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z"/>
                            </svg>
                            <span>Study reminders and notifications</span>
                        </li>
                        <li>
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M13 3c-4.97 0-9 4.03-9 9H1l3.89 3.89.07.14L9 12H6c0-3.87 3.13-7 7-7s7 3.13 7 7-3.13 7-7 7c-1.93 0-3.68-.79-4.94-2.06l-1.42 1.42C8.27 19.99 10.51 21 13 21c4.97 0 9-4.03 9-9s-4.03-9-9-9zm-1 5v5l4.28 2.54.72-1.21-3.5-2.08V8H12z"/>
                            </svg>
                            <span>Faster loading and better performance</span>
                        </li>
                        <li>
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                            </svg>
                            <span>Quick access from home screen</span>
                        </li>
                    </ul>
                </div>
                
                <div class="prompt-actions">
                    <button class="install-btn" onclick="installPrompt.install()">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M19 9h-4V3H9v6H5l7 7 7-7zM5 18v2h14v-2H5z"/>
                        </svg>
                        Install App
                    </button>
                    <button class="later-btn" onclick="installPrompt.dismiss()">
                        Maybe Later
                    </button>
                </div>
                
                <div class="prompt-footer">
                    <small>Free • No app store required • Uninstall anytime</small>
                </div>
            </div>
        `;
        
        document.body.appendChild(prompt);
        
        // Animate in
        setTimeout(() => {
            prompt.classList.add('visible');
        }, 100);
        
        // Auto-dismiss after 30 seconds
        setTimeout(() => {
            if (document.getElementById('pwa-install-prompt')) {
                this.dismiss();
            }
        }, 30000);
        
        this.trackEvent('custom_prompt_shown');
    }
    
    /**
     * Install the app
     */
    async install() {
        if (!this.deferredPrompt) {
            console.warn('[Install] No deferred prompt available');
            return;
        }
        
        try {
            // Show the install prompt
            this.deferredPrompt.prompt();
            
            // Wait for user response
            const { outcome } = await this.deferredPrompt.userChoice;
            
            console.log('[Install] User choice:', outcome);
            
            this.trackEvent('install_prompted', { outcome });
            
            if (outcome === 'accepted') {
                this.trackEvent('install_accepted');
            } else {
                this.trackEvent('install_declined');
                this.recordDismissal();
            }
            
            // Clear the prompt
            this.deferredPrompt = null;
            this.hidePrompt();
            
        } catch (error) {
            console.error('[Install] Install error:', error);
            this.trackEvent('install_error', { error: error.message });
        }
    }
    
    /**
     * Dismiss the prompt
     */
    dismiss() {
        this.hidePrompt();
        this.recordDismissal();
        this.trackEvent('custom_prompt_dismissed');
    }
    
    /**
     * Hide the prompt
     */
    hidePrompt() {
        const prompt = document.getElementById('pwa-install-prompt');
        if (prompt) {
            prompt.classList.remove('visible');
            setTimeout(() => {
                prompt.remove();
            }, 300);
        }
    }
    
    /**
     * Show install success message
     */
    showInstallSuccess() {
        const success = document.createElement('div');
        success.className = 'install-success-notification';
        success.innerHTML = `
            <div class="success-content">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                </svg>
                <div class="success-text">
                    <h4>App Installed Successfully!</h4>
                    <p>You can now access PMP Prep from your home screen</p>
                </div>
            </div>
        `;
        
        document.body.appendChild(success);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            success.remove();
        }, 5000);
    }
    
    /**
     * Check install status
     */
    checkInstallStatus() {
        // Check if running in standalone mode
        if (window.matchMedia('(display-mode: standalone)').matches) {
            this.isInstalled = true;
        }
        
        // Check if running as PWA on iOS
        if (window.navigator.standalone === true) {
            this.isInstalled = true;
        }
    }
    
    /**
     * Track engagement
     */
    trackEngagement() {
        this.sessionStart = Date.now();
        
        // Track page interactions
        ['click', 'scroll', 'keydown'].forEach(event => {
            document.addEventListener(event, () => {
                this.updateEngagement();
            }, { passive: true });
        });
    }
    
    /**
     * Update engagement metrics
     */
    updateEngagement() {
        const now = Date.now();
        const sessionTime = Math.floor((now - this.sessionStart) / 1000);
        
        // Update total engagement time
        const totalEngagement = this.getTotalEngagement() + 1;
        localStorage.setItem('pwa_total_engagement', totalEngagement.toString());
        
        // Update visit count
        if (!sessionStorage.getItem('pwa_visit_counted')) {
            const visitCount = this.getVisitCount() + 1;
            localStorage.setItem('pwa_visit_count', visitCount.toString());
            sessionStorage.setItem('pwa_visit_counted', 'true');
        }
    }
    
    /**
     * Get visit count
     */
    getVisitCount() {
        return parseInt(localStorage.getItem('pwa_visit_count') || '0');
    }
    
    /**
     * Get total engagement time
     */
    getTotalEngagement() {
        return parseInt(localStorage.getItem('pwa_total_engagement') || '0');
    }
    
    /**
     * Get current session time
     */
    getCurrentSessionTime() {
        return Math.floor((Date.now() - this.sessionStart) / 1000);
    }
    
    /**
     * Check if user has completed a lesson
     */
    hasCompletedLesson() {
        return localStorage.getItem('pwa_completed_lesson') === 'true';
    }
    
    /**
     * Mark lesson as completed (called from lesson completion)
     */
    markLessonCompleted() {
        localStorage.setItem('pwa_completed_lesson', 'true');
    }
    
    /**
     * Record dismissal
     */
    recordDismissal() {
        const dismissals = this.getDismissalHistory();
        dismissals.push(Date.now());
        localStorage.setItem('pwa_dismissal_history', JSON.stringify(dismissals));
    }
    
    /**
     * Get dismissal history
     */
    getDismissalHistory() {
        const stored = localStorage.getItem('pwa_dismissal_history');
        return stored ? JSON.parse(stored) : [];
    }
    
    /**
     * Track events
     */
    trackEvent(eventName, data = {}) {
        // Send to analytics
        if (typeof gtag !== 'undefined') {
            gtag('event', eventName, {
                event_category: 'PWA_Install',
                ...data
            });
        }
        
        console.log('[Install Analytics]', eventName, data);
    }
    
    /**
     * Get install status
     */
    getStatus() {
        return {
            isInstalled: this.isInstalled,
            canInstall: !!this.deferredPrompt,
            visitCount: this.getVisitCount(),
            totalEngagement: this.getTotalEngagement(),
            currentSessionTime: this.getCurrentSessionTime(),
            hasCompletedLesson: this.hasCompletedLesson(),
            dismissalCount: this.getDismissalHistory().length
        };
    }
}

// Initialize install prompt
let installPrompt;

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        installPrompt = new InstallPrompt();
    });
} else {
    installPrompt = new InstallPrompt();
}

// Export for global access
window.installPrompt = installPrompt;
