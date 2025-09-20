<?php
/**
 * Template Name: Dashboard
 */

get_header(); ?>

<div class="min-h-screen bg-gray-50">
    <!-- Dashboard Breadcrumb -->
    <nav class="bg-white shadow-sm border-b border-gray-200 sticky top-16 z-30" style="background-color: #EFEFEF;" aria-label="Dashboard breadcrumb">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Breadcrumb Navigation -->
            <div class="flex items-center py-3 text-sm">
                <a href="<?php echo home_url(); ?>" class="text-gray-500 hover:text-gray-700 transition-colors">
                    <i class="fas fa-home mr-1"></i>
                    Home
                </a>
                <i class="fas fa-chevron-right mx-2 text-gray-400 text-xs"></i>
                <span class="text-gray-900 font-medium">Dashboard</span>
            </div>
            
            <!-- Dashboard Header Content -->
            <div class="flex items-center justify-between pb-4">
                
                <!-- Left Section: Welcome -->
                <div class="flex items-center min-w-0 flex-1">
                    <div class="min-w-0">
                        <h1 class="text-lg sm:text-xl lg:text-2xl font-bold text-gray-900 truncate">
                            Welcome back, <?php echo esc_html(wp_get_current_user()->display_name); ?>!
                        </h1>
                        <p class="text-sm text-gray-600 hidden md:block">Track your PMP exam preparation progress</p>
                    </div>
                </div>
                
                <!-- Right Section: Actions & Progress -->
                <div class="flex items-center space-x-2 sm:space-x-4">
                    
                    <!-- Progress Indicator - Hidden on mobile -->
                    <div class="hidden lg:flex items-center space-x-2 text-sm text-gray-600">
                        <div class="w-16 bg-gray-200 rounded-full h-2">
                            <div class="bg-primary h-2 rounded-full transition-all duration-300" 
                                 style="width: <?php 
                                 $user_progress = PMP_Progress_Tracker::get_user_progress(get_current_user_id());
                                 echo esc_attr($user_progress['overall_progress']); 
                                 ?>%"></div>
                        </div>
                        <span class="font-medium"><?php echo number_format($user_progress['overall_progress'], 0); ?>%</span>
                    </div>
                    
                    <!-- Continue Learning Button -->
                    <a href="<?php echo esc_url(get_post_type_archive_link('lesson')); ?>" 
                       class="inline-flex items-center px-3 py-2 sm:px-4 sm:py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 transition-colors duration-200">
                        <i class="fas fa-book mr-1 sm:mr-2 text-xs sm:text-sm"></i>
                        <span class="hidden sm:inline">Continue Learning</span>
                        <span class="sm:hidden">Learn</span>
                    </a>
                    
                    <!-- Mobile Menu Button -->
                    <button type="button" 
                            class="sm:hidden inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-primary" 
                            aria-expanded="false" 
                            aria-label="Open dashboard menu"
                            onclick="toggleMobileMenu()">
                        <div class="hamburger-icon">
                            <span class="hamburger-line"></span>
                            <span class="hamburger-line"></span>
                            <span class="hamburger-line"></span>
                        </div>
                    </button>
                </div>
            </div>
            
            <!-- Mobile Progress - Shown only on mobile -->
            <div class="sm:hidden pb-4">
                <div class="flex items-center justify-between">
                    <p class="text-sm text-gray-600">Your progress</p>
                    <div class="flex items-center space-x-2">
                        <div class="w-20 bg-gray-200 rounded-full h-2">
                            <div class="bg-primary h-2 rounded-full transition-all duration-300" 
                                 style="width: <?php echo esc_attr($user_progress['overall_progress']); ?>%"></div>
                        </div>
                        <span class="text-sm font-medium text-gray-900"><?php echo number_format($user_progress['overall_progress'], 0); ?>%</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Mobile Navigation Menu -->
        <div class="sm:hidden hidden" id="mobile-menu">
            <div class="px-4 pt-2 pb-3 space-y-1 bg-gray-50 border-t border-gray-200">
                <!-- Close Button -->
                <div class="flex justify-end mb-2">
                    <button type="button" 
                            class="p-2 rounded-md text-gray-400 hover:text-gray-600 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-primary"
                            onclick="toggleMobileMenu()">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>
                
                <a href="<?php echo esc_url(get_post_type_archive_link('lesson')); ?>" 
                   class="flex items-center px-3 py-2 text-base font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-100 rounded-md">
                    <i class="fas fa-book mr-3 text-gray-400"></i>
                    Lessons
                </a>
                <a href="<?php echo esc_url(get_post_type_archive_link('practice_test')); ?>" 
                   class="flex items-center px-3 py-2 text-base font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-100 rounded-md">
                    <i class="fas fa-clipboard-check mr-3 text-gray-400"></i>
                    Practice Tests
                </a>
                <a href="<?php echo esc_url(get_post_type_archive_link('work_group')); ?>" 
                   class="flex items-center px-3 py-2 text-base font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-100 rounded-md">
                    <i class="fas fa-users mr-3 text-gray-400"></i>
                    Work Groups
                </a>
                <a href="<?php echo wp_logout_url(home_url()); ?>" 
                   class="flex items-center px-3 py-2 text-base font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-100 rounded-md">
                    <i class="fas fa-sign-out-alt mr-3 text-gray-400"></i>
                    Logout
                </a>
            </div>
        </div>
    </nav>

    <script>
    function toggleMobileMenu() {
        const menu = document.getElementById('mobile-menu');
        const hamburger = document.querySelector('.hamburger-icon');
        const isHidden = menu.classList.contains('hidden');
        
        if (isHidden) {
            menu.classList.remove('hidden');
            hamburger.classList.add('open');
        } else {
            menu.classList.add('hidden');
            hamburger.classList.remove('open');
        }
    }
    
    // Close mobile menu when clicking outside
    document.addEventListener('click', function(event) {
        const menu = document.getElementById('mobile-menu');
        const button = event.target.closest('button');
        const hamburger = document.querySelector('.hamburger-icon');
        
        if (!menu.contains(event.target) && !button) {
            menu.classList.add('hidden');
            if (hamburger) {
                hamburger.classList.remove('open');
            }
        }
    });
    </script>

    <!-- Dashboard Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Main Content Area -->
            <div class="lg:col-span-2 space-y-8">
                
                <!-- Progress Cards -->
                <section>
                    <h2 class="text-xl font-semibold text-gray-900 mb-6">Domain Progress</h2>
                    <?php get_template_part('template-parts/dashboard/progress-cards'); ?>
                </section>
                
                <!-- Analytics Charts -->
                <section>
                    <h2 class="text-xl font-semibold text-gray-900 mb-6">Analytics</h2>
                    <?php get_template_part('template-parts/dashboard/analytics-charts'); ?>
                </section>
                
            </div>
            
            <!-- Sidebar -->
            <div class="space-y-6">
                
                <!-- Study Streak -->
                <section>
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Study Streak</h2>
                    <?php get_template_part('template-parts/dashboard/study-streak'); ?>
                </section>
                
                <!-- Quick Actions -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
                    <div class="space-y-3">
                        <a href="<?php echo esc_url(get_post_type_archive_link('lesson')); ?>" 
                           class="flex items-center p-3 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                            <i class="fas fa-book text-blue-600 mr-3"></i>
                            <span class="text-blue-800 font-medium">Browse Lessons</span>
                        </a>
                        <a href="<?php echo esc_url(get_post_type_archive_link('practice_test')); ?>" 
                           class="flex items-center p-3 bg-green-50 rounded-lg hover:bg-green-100 transition-colors">
                            <i class="fas fa-clipboard-check text-green-600 mr-3"></i>
                            <span class="text-green-800 font-medium">Practice Tests</span>
                        </a>
                        <a href="<?php echo esc_url(get_post_type_archive_link('work_group')); ?>" 
                           class="flex items-center p-3 bg-purple-50 rounded-lg hover:bg-purple-100 transition-colors">
                            <i class="fas fa-users text-purple-600 mr-3"></i>
                            <span class="text-purple-800 font-medium">Work Groups</span>
                        </a>
                    </div>
                </div>
                
                <!-- Recent Activity -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Activity</h3>
                    <div id="recentActivity" class="space-y-3">
                        <div class="flex items-center justify-center py-4 text-gray-500">
                            <i class="fas fa-spinner fa-spin mr-2"></i>
                            Loading activity...
                        </div>
                    </div>
                </div>
                
            </div>
            
        </div>
    </div>
</div>

<!-- Set current user ID for JavaScript -->
<script>
window.pmpCurrentUserId = <?php echo get_current_user_id(); ?>;
</script>

<style>
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
</style>

<?php get_footer(); ?>
