/**
 * Service Worker Registration
 */

if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/wp-content/themes/pmp-dashboard/pwa/service-worker.js')
            .then(registration => {
                console.log('SW registered:', registration);
                
                // Show install prompt after 2 visits
                let visitCount = localStorage.getItem('pmp-visit-count') || 0;
                visitCount++;
                localStorage.setItem('pmp-visit-count', visitCount);
                
                if (visitCount >= 2) {
                    showInstallPrompt();
                }
            })
            .catch(error => {
                console.log('SW registration failed:', error);
            });
    });
}

// Install prompt
let deferredPrompt;

window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault();
    deferredPrompt = e;
});

function showInstallPrompt() {
    if (deferredPrompt && !localStorage.getItem('pmp-install-dismissed')) {
        const banner = document.createElement('div');
        banner.innerHTML = `
            <div style="position:fixed;top:0;left:0;right:0;background:#2563eb;color:white;padding:12px;text-align:center;z-index:9999;">
                Install PMP Prep app for offline access
                <button onclick="installApp()" style="margin-left:10px;background:white;color:#2563eb;border:none;padding:4px 8px;border-radius:4px;">Install</button>
                <button onclick="dismissInstall()" style="margin-left:5px;background:transparent;color:white;border:1px solid white;padding:4px 8px;border-radius:4px;">Later</button>
            </div>
        `;
        document.body.appendChild(banner);
    }
}

function installApp() {
    if (deferredPrompt) {
        deferredPrompt.prompt();
        deferredPrompt.userChoice.then(() => {
            deferredPrompt = null;
            document.querySelector('[style*="position:fixed"]')?.remove();
        });
    }
}

function dismissInstall() {
    localStorage.setItem('pmp-install-dismissed', 'true');
    document.querySelector('[style*="position:fixed"]')?.remove();
}
