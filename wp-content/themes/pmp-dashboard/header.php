<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- PWA Meta Tags -->
    <meta name="theme-color" content="#5b28b3">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="PMP Prep">
    <link rel="manifest" href="<?php echo get_template_directory_uri(); ?>/manifest.json">
    <link rel="apple-touch-icon" href="<?php echo get_template_directory_uri(); ?>/assets/pmp-exam-prep-logo.png">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: 'rgb(91, 40, 179)',
                        'primary-dark': 'rgb(71, 30, 139)',
                        secondary: '#333',
                        'light-gray': '#f5f5f5',
                        'border-color': '#eaeaea',
                        success: '#28a745'
                    },
                    fontFamily: {
                        'source': ['Source Sans Pro', 'sans-serif']
                    }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@300;400;600;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.0.0/css/all.css">
    <style>
        p { line-height: 1.1618; }
        
        /* Hamburger Menu Animation */
        .hamburger-icon {
            width: 20px;
            height: 16px;
            position: relative;
            transform: rotate(0deg);
            transition: .3s ease-in-out;
            cursor: pointer;
        }
        
        .hamburger-line {
            display: block;
            position: absolute;
            height: 2px;
            width: 100%;
            background: currentColor;
            border-radius: 1px;
            opacity: 1;
            left: 0;
            transform: rotate(0deg);
            transition: .25s ease-in-out;
        }
        
        .hamburger-line:nth-child(1) {
            top: 0px;
        }
        
        .hamburger-line:nth-child(2) {
            top: 7px;
        }
        
        .hamburger-line:nth-child(3) {
            top: 14px;
        }
        
        .hamburger-icon.open .hamburger-line:nth-child(1) {
            top: 7px;
            transform: rotate(135deg);
        }
        
        .hamburger-icon.open .hamburger-line:nth-child(2) {
            opacity: 0;
            left: -20px;
        }
        
        .hamburger-icon.open .hamburger-line:nth-child(3) {
            top: 7px;
            transform: rotate(-135deg);
        }
        
        /* Flyout Animation */
        .flyout-enter {
            transform: translateX(0);
        }
        
        .flyout-exit {
            transform: translateX(100%);
        }
    </style>
    
    <?php wp_head(); ?>
</head>
<body <?php body_class('bg-gray-50 font-source'); ?>>

<?php if (!is_page_template('page-dashboard.php')): ?>
<!-- Main Site Header -->
<header class="bg-white shadow-sm sticky top-0 z-50">
    <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16 lg:h-20">
            
            <!-- Logo -->
            <div class="flex-shrink-0">
                <a href="<?php echo home_url(); ?>" class="flex items-center">
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/pmp-exam-prep-logo.png" 
                         alt="PMP Exam Prep" 
                         class="h-8 sm:h-10 lg:h-12 w-auto">
                </a>
            </div>
            
            <!-- Desktop Navigation -->
            <div class="hidden lg:flex items-center space-x-1">
                <a href="<?php echo home_url(); ?>" 
                   class="<?php echo is_front_page() ? 'text-primary font-medium' : 'text-secondary hover:text-primary font-light'; ?> text-lg px-4 py-2 transition-colors duration-300 border-r border-dotted border-black border-opacity-10">
                    Home
                </a>
                <a href="<?php echo get_post_type_archive_link('work_group'); ?>" 
                   class="<?php echo is_post_type_archive('work_group') ? 'text-primary font-medium' : 'text-secondary hover:text-primary font-light'; ?> text-lg px-4 py-2 transition-colors duration-300 border-r border-dotted border-black border-opacity-10">
                    Courses
                </a>
                <?php if (is_user_logged_in()): ?>
                <a href="<?php echo get_permalink(get_page_by_path('dashboard')); ?>" 
                   class="<?php echo is_page('dashboard') ? 'text-primary font-medium' : 'text-secondary hover:text-primary font-light'; ?> text-lg px-4 py-2 transition-colors duration-300 border-r border-dotted border-black border-opacity-10">
                    My Learning
                </a>
                <?php endif; ?>
                <a href="<?php echo get_permalink(get_page_by_path('resources')); ?>" 
                   class="<?php echo is_page('resources') ? 'text-primary font-medium' : 'text-secondary hover:text-primary font-light'; ?> text-lg px-4 py-2 transition-colors duration-300 border-r border-dotted border-black border-opacity-10">
                    Resources
                </a>
                <a href="<?php echo get_permalink(get_page_by_path('community')); ?>" 
                   class="<?php echo is_page('community') ? 'text-primary font-medium' : 'text-secondary hover:text-primary font-light'; ?> text-lg px-4 py-2 transition-colors duration-300 border-r border-dotted border-black border-opacity-10">
                    Community
                </a>
                <a href="<?php echo get_permalink(get_page_by_path('about')); ?>" 
                   class="<?php echo is_page('about') ? 'text-primary font-medium' : 'text-secondary hover:text-primary font-light'; ?> text-lg px-4 py-2 transition-colors duration-300">
                    About
                </a>
            </div>
            
            <!-- Right Section: User Actions & Mobile Menu -->
            <div class="flex items-center space-x-2 sm:space-x-4">
                <?php if (is_user_logged_in()): ?>
                    <!-- Desktop User Menu -->
                    <div class="hidden lg:flex items-center space-x-3">
                        <span class="text-sm text-gray-600">
                            Hi, <?php echo esc_html(explode(' ', wp_get_current_user()->display_name)[0]); ?>
                        </span>
                        <a href="<?php echo get_permalink(get_page_by_path('dashboard')); ?>" 
                           class="inline-flex items-center px-3 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 transition-colors duration-200">
                            <i class="fas fa-tachometer-alt mr-2 text-xs"></i>
                            Dashboard
                        </a>
                    </div>
                <?php else: ?>
                    <!-- Desktop Login/Register -->
                    <div class="hidden lg:flex items-center space-x-2">
                        <a href="<?php echo wp_login_url(); ?>" 
                           class="text-secondary hover:text-primary font-medium text-sm px-3 py-2 transition-colors duration-300">
                            Login
                        </a>
                        <a href="<?php echo wp_registration_url(); ?>" 
                           class="inline-flex items-center px-4 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary-dark transition-colors duration-200">
                            Get Started
                        </a>
                    </div>
                <?php endif; ?>
                
                <!-- Mobile Menu Button (Hamburger) -->
                <button type="button" 
                        class="lg:hidden relative inline-flex items-center justify-center p-2 rounded-md text-gray-600 hover:text-primary hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-primary transition-colors duration-200"
                        aria-expanded="false"
                        aria-label="Toggle navigation menu"
                        onclick="toggleMainMenu()">
                    <div class="hamburger-icon">
                        <span class="hamburger-line"></span>
                        <span class="hamburger-line"></span>
                        <span class="hamburger-line"></span>
                    </div>
                </button>
            </div>
        </div>
        
        <!-- Mobile Flyout Navigation -->
        <div class="lg:hidden fixed inset-0 z-50 hidden" id="main-mobile-menu">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity duration-300" onclick="toggleMainMenu()"></div>
            
            <!-- Flyout Panel -->
            <div class="fixed top-0 right-0 h-full w-80 max-w-sm bg-white shadow-xl transform translate-x-full transition-transform duration-300 ease-in-out" id="flyout-panel">
                <!-- Header -->
                <div class="flex items-center justify-between p-4 border-b border-gray-200">
                    <div class="flex items-center space-x-3">
                        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/pmp-exam-prep-logo.png" 
                             alt="PMP Exam Prep" 
                             class="h-8 w-auto">
                        <span class="text-lg font-semibold text-gray-900">Menu</span>
                    </div>
                    <button type="button" 
                            class="p-2 rounded-md text-gray-400 hover:text-gray-600 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-primary"
                            onclick="toggleMainMenu()">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>
                
                <!-- Navigation Links -->
                <div class="flex-1 overflow-y-auto py-4">
                    <nav class="px-4 space-y-2">
                        <a href="<?php echo home_url(); ?>" 
                           class="<?php echo is_front_page() ? 'bg-primary text-white' : 'text-gray-700 hover:bg-gray-100'; ?> flex items-center px-4 py-3 text-base font-medium rounded-lg transition-colors duration-200">
                            <i class="fas fa-home mr-4 text-lg w-5"></i>
                            Home
                        </a>
                        <a href="<?php echo get_post_type_archive_link('work_group'); ?>" 
                           class="<?php echo is_post_type_archive('work_group') ? 'bg-primary text-white' : 'text-gray-700 hover:bg-gray-100'; ?> flex items-center px-4 py-3 text-base font-medium rounded-lg transition-colors duration-200">
                            <i class="fas fa-graduation-cap mr-4 text-lg w-5"></i>
                            Courses
                        </a>
                        <?php if (is_user_logged_in()): ?>
                        <a href="<?php echo get_permalink(get_page_by_path('dashboard')); ?>" 
                           class="<?php echo is_page('dashboard') ? 'bg-primary text-white' : 'text-gray-700 hover:bg-gray-100'; ?> flex items-center px-4 py-3 text-base font-medium rounded-lg transition-colors duration-200">
                            <i class="fas fa-tachometer-alt mr-4 text-lg w-5"></i>
                            My Learning
                        </a>
                        <?php endif; ?>
                        <a href="<?php echo get_permalink(get_page_by_path('resources')); ?>" 
                           class="<?php echo is_page('resources') ? 'bg-primary text-white' : 'text-gray-700 hover:bg-gray-100'; ?> flex items-center px-4 py-3 text-base font-medium rounded-lg transition-colors duration-200">
                            <i class="fas fa-book mr-4 text-lg w-5"></i>
                            Resources
                        </a>
                        <a href="<?php echo get_permalink(get_page_by_path('community')); ?>" 
                           class="<?php echo is_page('community') ? 'bg-primary text-white' : 'text-gray-700 hover:bg-gray-100'; ?> flex items-center px-4 py-3 text-base font-medium rounded-lg transition-colors duration-200">
                            <i class="fas fa-users mr-4 text-lg w-5"></i>
                            Community
                        </a>
                        <a href="<?php echo get_permalink(get_page_by_path('about')); ?>" 
                           class="<?php echo is_page('about') ? 'bg-primary text-white' : 'text-gray-700 hover:bg-gray-100'; ?> flex items-center px-4 py-3 text-base font-medium rounded-lg transition-colors duration-200">
                            <i class="fas fa-info-circle mr-4 text-lg w-5"></i>
                            About
                        </a>
                    </nav>
                </div>
                
                <!-- Footer Actions -->
                <div class="border-t border-gray-200 p-4">
                    <?php if (is_user_logged_in()): ?>
                        <div class="space-y-3">
                            <div class="flex items-center space-x-3 px-4 py-2 bg-gray-50 rounded-lg">
                                <div class="w-8 h-8 bg-primary rounded-full flex items-center justify-center">
                                    <i class="fas fa-user text-white text-sm"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900"><?php echo esc_html(wp_get_current_user()->display_name); ?></p>
                                    <p class="text-xs text-gray-500"><?php echo esc_html(wp_get_current_user()->user_email); ?></p>
                                </div>
                            </div>
                            <a href="<?php echo wp_logout_url(home_url()); ?>" 
                               class="flex items-center px-4 py-3 text-base font-medium text-red-600 hover:bg-red-50 rounded-lg transition-colors duration-200">
                                <i class="fas fa-sign-out-alt mr-4 text-lg w-5"></i>
                                Logout
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="space-y-3">
                            <a href="<?php echo wp_login_url(); ?>" 
                               class="flex items-center justify-center px-4 py-3 text-base font-medium text-primary border border-primary rounded-lg hover:bg-primary hover:text-white transition-colors duration-200">
                                <i class="fas fa-sign-in-alt mr-2"></i>
                                Login
                            </a>
                            <a href="<?php echo wp_registration_url(); ?>" 
                               class="flex items-center justify-center px-4 py-3 text-base font-medium bg-primary text-white rounded-lg hover:bg-primary-dark transition-colors duration-200">
                                <i class="fas fa-user-plus mr-2"></i>
                                Get Started
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
</header>

<script>
function toggleMainMenu() {
    const menu = document.getElementById('main-mobile-menu');
    const panel = document.getElementById('flyout-panel');
    const hamburger = document.querySelector('.hamburger-icon');
    const isHidden = menu.classList.contains('hidden');
    
    if (isHidden) {
        // Open menu
        menu.classList.remove('hidden');
        hamburger.classList.add('open');
        
        // Trigger animation after element is visible
        setTimeout(() => {
            panel.classList.remove('translate-x-full');
            panel.classList.add('flyout-enter');
        }, 10);
        
        // Prevent body scroll
        document.body.style.overflow = 'hidden';
    } else {
        // Close menu
        panel.classList.add('translate-x-full');
        panel.classList.remove('flyout-enter');
        hamburger.classList.remove('open');
        
        // Hide menu after animation
        setTimeout(() => {
            menu.classList.add('hidden');
        }, 300);
        
        // Restore body scroll
        document.body.style.overflow = '';
    }
}

// Close mobile menu when clicking outside or on backdrop
document.addEventListener('click', function(event) {
    const menu = document.getElementById('main-mobile-menu');
    const panel = document.getElementById('flyout-panel');
    const button = event.target.closest('button');
    const hamburger = document.querySelector('.hamburger-icon');
    
    // Check if click is on backdrop or outside panel
    if (menu && !menu.classList.contains('hidden') && 
        (!panel.contains(event.target) || event.target.classList.contains('bg-black'))) {
        
        if (!button || !button.contains(hamburger)) {
            panel.classList.add('translate-x-full');
            panel.classList.remove('flyout-enter');
            hamburger.classList.remove('open');
            
            setTimeout(() => {
                menu.classList.add('hidden');
            }, 300);
            
            document.body.style.overflow = '';
        }
    }
});

// Close menu on escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        const menu = document.getElementById('main-mobile-menu');
        if (menu && !menu.classList.contains('hidden')) {
            toggleMainMenu();
        }
    }
});

// Handle window resize
window.addEventListener('resize', function() {
    if (window.innerWidth >= 1024) { // lg breakpoint
        const menu = document.getElementById('main-mobile-menu');
        const hamburger = document.querySelector('.hamburger-icon');
        
        if (menu && !menu.classList.contains('hidden')) {
            menu.classList.add('hidden');
            hamburger.classList.remove('open');
            document.body.style.overflow = '';
        }
    }
});
</script>
<?php endif; ?>
