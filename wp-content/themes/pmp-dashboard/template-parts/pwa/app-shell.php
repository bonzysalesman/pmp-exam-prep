<?php
/**
 * PWA App Shell Template
 * Task: T007 - App Shell Architecture
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get critical CSS
$critical_css = file_get_contents(get_template_directory() . '/assets/css/critical.css');
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js">
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#3b82f6">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="PMP Prep">
    
    <title><?php wp_title('|', true, 'right'); ?></title>
    
    <!-- Critical CSS Inline -->
    <style>
        <?php echo $critical_css; ?>
        
        /* App Shell Specific Styles */
        .app-shell {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .app-header {
            position: sticky;
            top: 0;
            z-index: 50;
            background: #ffffff;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .app-main {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        
        .app-content {
            flex: 1;
            padding: 1rem;
        }
        
        .app-loading {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 200px;
        }
        
        .loading-spinner {
            width: 40px;
            height: 40px;
            border: 3px solid #e5e7eb;
            border-top: 3px solid #3b82f6;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .skeleton {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
        }
        
        @keyframes loading {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }
        
        .skeleton-text {
            height: 1rem;
            border-radius: 4px;
            margin-bottom: 0.5rem;
        }
        
        .skeleton-title {
            height: 1.5rem;
            border-radius: 4px;
            margin-bottom: 1rem;
            width: 60%;
        }
        
        .skeleton-card {
            height: 200px;
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        
        /* PWA specific styles */
        .pwa-installed .app-header {
            padding-top: env(safe-area-inset-top);
        }
        
        .pwa-installed .app-content {
            padding-bottom: env(safe-area-inset-bottom);
        }
        
        /* Offline styles */
        .offline .app-content {
            opacity: 0.8;
        }
        
        .offline-indicator {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: #ef4444;
            color: white;
            padding: 0.5rem;
            text-align: center;
            font-size: 0.875rem;
            z-index: 100;
            transform: translateY(-100%);
            transition: transform 0.3s ease;
        }
        
        .offline-indicator.visible {
            transform: translateY(0);
        }
        
        /* Install prompt styles */
        .pwa-install-prompt {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }
        
        .pwa-install-prompt.visible {
            opacity: 1;
            visibility: visible;
        }
        
        .prompt-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
        }
        
        .prompt-content {
            position: relative;
            background: white;
            border-radius: 12px;
            max-width: 400px;
            margin: 1rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }
        
        /* Bottom navigation for mobile */
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: white;
            border-top: 1px solid #e5e7eb;
            padding: 0.5rem;
            padding-bottom: calc(0.5rem + env(safe-area-inset-bottom));
            z-index: 40;
        }
        
        .nav-items {
            display: flex;
            justify-content: space-around;
            align-items: center;
        }
        
        .nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 0.5rem;
            color: #6b7280;
            text-decoration: none;
            font-size: 0.75rem;
            min-width: 44px;
            min-height: 44px;
        }
        
        .nav-item.active {
            color: #3b82f6;
        }
        
        .nav-icon {
            width: 24px;
            height: 24px;
            margin-bottom: 0.25rem;
        }
        
        /* Hide bottom nav on desktop */
        @media (min-width: 768px) {
            .bottom-nav {
                display: none;
            }
        }
    </style>
    
    <!-- Preload critical resources -->
    <link rel="preload" href="<?php echo get_template_directory_uri(); ?>/assets/fonts/inter.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="<?php echo get_template_directory_uri(); ?>/assets/js/main.js" as="script">
    
    <!-- Web App Manifest -->
    <link rel="manifest" href="<?php echo get_template_directory_uri(); ?>/manifest.json">
    
    <!-- Apple Touch Icons -->
    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo get_template_directory_uri(); ?>/assets/icons/icon-180.png">
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo get_template_directory_uri(); ?>/assets/icons/icon-32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="<?php echo get_template_directory_uri(); ?>/assets/icons/icon-16.png">
    
    <!-- Microsoft Tiles -->
    <meta name="msapplication-TileColor" content="#3b82f6">
    <meta name="msapplication-TileImage" content="<?php echo get_template_directory_uri(); ?>/assets/icons/icon-144.png">
    
    <?php wp_head(); ?>
</head>

<body <?php body_class('app-shell'); ?>>
    
    <!-- App Header -->
    <header class="app-header">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                
                <!-- Logo -->
                <div class="flex items-center">
                    <a href="<?php echo home_url(); ?>" class="flex items-center">
                        <img src="<?php echo get_template_directory_uri(); ?>/assets/icons/icon-32.png" 
                             alt="PMP Prep" class="w-8 h-8 mr-2">
                        <span class="text-xl font-bold text-gray-900">PMP Prep</span>
                    </a>
                </div>
                
                <!-- Desktop Navigation -->
                <nav class="hidden md:flex space-x-8">
                    <a href="<?php echo home_url('/dashboard'); ?>" 
                       class="nav-link <?php echo is_page('dashboard') ? 'active' : ''; ?>">
                        Dashboard
                    </a>
                    <a href="<?php echo get_post_type_archive_link('lesson'); ?>" 
                       class="nav-link <?php echo is_post_type_archive('lesson') ? 'active' : ''; ?>">
                        Lessons
                    </a>
                    <a href="<?php echo get_post_type_archive_link('practice_test'); ?>" 
                       class="nav-link <?php echo is_post_type_archive('practice_test') ? 'active' : ''; ?>">
                        Practice Tests
                    </a>
                    <a href="<?php echo get_post_type_archive_link('resource'); ?>" 
                       class="nav-link <?php echo is_post_type_archive('resource') ? 'active' : ''; ?>">
                        Resources
                    </a>
                </nav>
                
                <!-- User Menu -->
                <div class="flex items-center space-x-4">
                    <?php if (is_user_logged_in()): ?>
                        <div class="relative">
                            <button class="flex items-center text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-primary">
                                <img class="w-8 h-8 rounded-full" 
                                     src="<?php echo get_avatar_url(get_current_user_id()); ?>" 
                                     alt="Profile">
                            </button>
                        </div>
                    <?php else: ?>
                        <a href="<?php echo wp_login_url(); ?>" class="text-primary hover:text-primary-dark">
                            Login
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>
    
    <!-- Main Content Area -->
    <main class="app-main">
        <div class="app-content" id="app-content">
            
            <!-- Loading State -->
            <div class="app-loading" id="app-loading">
                <div class="text-center">
                    <div class="loading-spinner mx-auto mb-4"></div>
                    <p class="text-gray-600">Loading...</p>
                </div>
            </div>
            
            <!-- Content Skeleton -->
            <div class="content-skeleton hidden" id="content-skeleton">
                <div class="skeleton skeleton-title"></div>
                <div class="skeleton skeleton-text" style="width: 80%;"></div>
                <div class="skeleton skeleton-text" style="width: 60%;"></div>
                <div class="skeleton skeleton-card"></div>
                <div class="skeleton skeleton-text" style="width: 90%;"></div>
                <div class="skeleton skeleton-text" style="width: 70%;"></div>
            </div>
            
            <!-- Dynamic Content Area -->
            <div class="dynamic-content hidden" id="dynamic-content">
                <!-- Content will be loaded here -->
            </div>
            
        </div>
    </main>
    
    <!-- Bottom Navigation (Mobile) -->
    <nav class="bottom-nav">
        <div class="nav-items">
            <a href="<?php echo home_url('/dashboard'); ?>" 
               class="nav-item <?php echo is_page('dashboard') ? 'active' : ''; ?>">
                <svg class="nav-icon" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/>
                </svg>
                <span>Home</span>
            </a>
            
            <a href="<?php echo get_post_type_archive_link('lesson'); ?>" 
               class="nav-item <?php echo is_post_type_archive('lesson') ? 'active' : ''; ?>">
                <svg class="nav-icon" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M18 2H6c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zM6 4h5v8l-2.5-1.5L6 12V4z"/>
                </svg>
                <span>Lessons</span>
            </a>
            
            <a href="<?php echo get_post_type_archive_link('practice_test'); ?>" 
               class="nav-item <?php echo is_post_type_archive('practice_test') ? 'active' : ''; ?>">
                <svg class="nav-icon" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/>
                </svg>
                <span>Tests</span>
            </a>
            
            <a href="<?php echo get_post_type_archive_link('resource'); ?>" 
               class="nav-item <?php echo is_post_type_archive('resource') ? 'active' : ''; ?>">
                <svg class="nav-icon" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"/>
                </svg>
                <span>Resources</span>
            </a>
            
            <a href="<?php echo home_url('/profile'); ?>" 
               class="nav-item <?php echo is_page('profile') ? 'active' : ''; ?>">
                <svg class="nav-icon" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                </svg>
                <span>Profile</span>
            </a>
        </div>
    </nav>
    
    <!-- PWA Scripts -->
    <script>
        // App Shell JavaScript
        class AppShell {
            constructor() {
                this.contentArea = document.getElementById('dynamic-content');
                this.loadingArea = document.getElementById('app-loading');
                this.skeletonArea = document.getElementById('content-skeleton');
                
                this.init();
            }
            
            init() {
                // Hide loading, show content
                setTimeout(() => {
                    this.hideLoading();
                    this.showContent();
                }, 500);
                
                // Setup navigation
                this.setupNavigation();
            }
            
            showLoading() {
                this.loadingArea.classList.remove('hidden');
                this.contentArea.classList.add('hidden');
                this.skeletonArea.classList.add('hidden');
            }
            
            showSkeleton() {
                this.loadingArea.classList.add('hidden');
                this.contentArea.classList.add('hidden');
                this.skeletonArea.classList.remove('hidden');
            }
            
            showContent() {
                this.loadingArea.classList.add('hidden');
                this.skeletonArea.classList.add('hidden');
                this.contentArea.classList.remove('hidden');
            }
            
            hideLoading() {
                this.loadingArea.classList.add('hidden');
            }
            
            setupNavigation() {
                // Add active states to navigation
                const currentPath = window.location.pathname;
                const navLinks = document.querySelectorAll('.nav-link, .nav-item');
                
                navLinks.forEach(link => {
                    const href = link.getAttribute('href');
                    if (href && currentPath.includes(href.replace(window.location.origin, ''))) {
                        link.classList.add('active');
                    }
                });
            }
            
            updateContent(html) {
                this.contentArea.innerHTML = html;
                this.showContent();
            }
        }
        
        // Initialize app shell
        const appShell = new AppShell();
        window.appShell = appShell;
    </script>
    
    <?php wp_footer(); ?>
</body>
</html>
