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
            
            <!-- User Actions -->
            <div class="flex items-center space-x-2 sm:space-x-4">
                <?php if (is_user_logged_in()): ?>
                    <!-- User Menu -->
                    <div class="hidden sm:flex items-center space-x-3">
                        <span class="text-sm text-gray-600">
                            Hi, <?php echo esc_html(explode(' ', wp_get_current_user()->display_name)[0]); ?>
                        </span>
                        <a href="<?php echo get_permalink(get_page_by_path('dashboard')); ?>" 
                           class="inline-flex items-center px-3 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 transition-colors duration-200">
                            <i class="fas fa-tachometer-alt mr-2 text-xs"></i>
                            Dashboard
                        </a>
                    </div>
                    
                    <!-- Mobile Dashboard Button -->
                    <a href="<?php echo get_permalink(get_page_by_path('dashboard')); ?>" 
                       class="sm:hidden inline-flex items-center px-3 py-2 bg-primary text-white text-sm font-medium rounded-lg">
                        <i class="fas fa-tachometer-alt"></i>
                    </a>
                <?php else: ?>
                    <!-- Login/Register -->
                    <div class="hidden sm:flex items-center space-x-2">
                        <a href="<?php echo wp_login_url(); ?>" 
                           class="text-secondary hover:text-primary font-medium text-sm px-3 py-2 transition-colors duration-300">
                            Login
                        </a>
                        <a href="<?php echo wp_registration_url(); ?>" 
                           class="inline-flex items-center px-4 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary-dark transition-colors duration-200">
                            Get Started
                        </a>
                    </div>
                    
                    <!-- Mobile Login Button -->
                    <a href="<?php echo wp_login_url(); ?>" 
                       class="sm:hidden inline-flex items-center px-3 py-2 bg-primary text-white text-sm font-medium rounded-lg">
                        <i class="fas fa-sign-in-alt"></i>
                    </a>
                <?php endif; ?>
                
                <!-- Mobile Menu Button -->
                <button type="button" 
                        class="lg:hidden inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-primary ml-2"
                        aria-expanded="false"
                        onclick="toggleMainMenu()">
                    <span class="sr-only">Open main menu</span>
                    <i class="fas fa-bars text-lg" id="main-menu-icon"></i>
                </button>
            </div>
        </div>
        
        <!-- Mobile Navigation Menu -->
        <div class="lg:hidden hidden" id="main-mobile-menu">
            <div class="px-4 pt-2 pb-4 space-y-1 bg-gray-50 border-t border-gray-200">
                <a href="<?php echo home_url(); ?>" 
                   class="<?php echo is_front_page() ? 'bg-primary text-white' : 'text-gray-700 hover:bg-gray-100'; ?> flex items-center px-3 py-3 text-base font-medium rounded-md">
                    <i class="fas fa-home mr-3 text-gray-400"></i>
                    Home
                </a>
                <a href="<?php echo get_post_type_archive_link('work_group'); ?>" 
                   class="<?php echo is_post_type_archive('work_group') ? 'bg-primary text-white' : 'text-gray-700 hover:bg-gray-100'; ?> flex items-center px-3 py-3 text-base font-medium rounded-md">
                    <i class="fas fa-graduation-cap mr-3 text-gray-400"></i>
                    Courses
                </a>
                <?php if (is_user_logged_in()): ?>
                <a href="<?php echo get_permalink(get_page_by_path('dashboard')); ?>" 
                   class="<?php echo is_page('dashboard') ? 'bg-primary text-white' : 'text-gray-700 hover:bg-gray-100'; ?> flex items-center px-3 py-3 text-base font-medium rounded-md">
                    <i class="fas fa-tachometer-alt mr-3 text-gray-400"></i>
                    My Learning
                </a>
                <?php endif; ?>
                <a href="<?php echo get_permalink(get_page_by_path('resources')); ?>" 
                   class="<?php echo is_page('resources') ? 'bg-primary text-white' : 'text-gray-700 hover:bg-gray-100'; ?> flex items-center px-3 py-3 text-base font-medium rounded-md">
                    <i class="fas fa-book mr-3 text-gray-400"></i>
                    Resources
                </a>
                <a href="<?php echo get_permalink(get_page_by_path('community')); ?>" 
                   class="<?php echo is_page('community') ? 'bg-primary text-white' : 'text-gray-700 hover:bg-gray-100'; ?> flex items-center px-3 py-3 text-base font-medium rounded-md">
                    <i class="fas fa-users mr-3 text-gray-400"></i>
                    Community
                </a>
                <a href="<?php echo get_permalink(get_page_by_path('about')); ?>" 
                   class="<?php echo is_page('about') ? 'bg-primary text-white' : 'text-gray-700 hover:bg-gray-100'; ?> flex items-center px-3 py-3 text-base font-medium rounded-md">
                    <i class="fas fa-info-circle mr-3 text-gray-400"></i>
                    About
                </a>
                
                <?php if (!is_user_logged_in()): ?>
                <div class="border-t border-gray-200 pt-4 mt-4">
                    <a href="<?php echo wp_login_url(); ?>" 
                       class="flex items-center px-3 py-3 text-base font-medium text-gray-700 hover:bg-gray-100 rounded-md">
                        <i class="fas fa-sign-in-alt mr-3 text-gray-400"></i>
                        Login
                    </a>
                    <a href="<?php echo wp_registration_url(); ?>" 
                       class="flex items-center px-3 py-3 text-base font-medium bg-primary text-white rounded-md mt-2">
                        <i class="fas fa-user-plus mr-3"></i>
                        Get Started
                    </a>
                </div>
                <?php else: ?>
                <div class="border-t border-gray-200 pt-4 mt-4">
                    <a href="<?php echo wp_logout_url(home_url()); ?>" 
                       class="flex items-center px-3 py-3 text-base font-medium text-gray-700 hover:bg-gray-100 rounded-md">
                        <i class="fas fa-sign-out-alt mr-3 text-gray-400"></i>
                        Logout
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </nav>
</header>

<script>
function toggleMainMenu() {
    const menu = document.getElementById('main-mobile-menu');
    const icon = document.getElementById('main-menu-icon');
    const isHidden = menu.classList.contains('hidden');
    
    if (isHidden) {
        menu.classList.remove('hidden');
        icon.classList.remove('fa-bars');
        icon.classList.add('fa-times');
    } else {
        menu.classList.add('hidden');
        icon.classList.remove('fa-times');
        icon.classList.add('fa-bars');
    }
}

// Close mobile menu when clicking outside
document.addEventListener('click', function(event) {
    const menu = document.getElementById('main-mobile-menu');
    const button = event.target.closest('button');
    
    if (menu && !menu.contains(event.target) && !button) {
        menu.classList.add('hidden');
        const icon = document.getElementById('main-menu-icon');
        if (icon) {
            icon.classList.remove('fa-times');
            icon.classList.add('fa-bars');
        }
    }
});
</script>
<?php endif; ?>
