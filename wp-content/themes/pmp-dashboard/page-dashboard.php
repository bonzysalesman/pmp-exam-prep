<?php
/**
 * Template Name: Dashboard
 */

get_header(); ?>

<div class="min-h-screen bg-gray-50">
    <!-- Dashboard Header -->
    <header class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 sm:h-20">
                
                <!-- Left Section: Logo & Welcome -->
                <div class="flex items-center min-w-0 flex-1">
                    <!-- Logo -->
                    <div class="flex-shrink-0">
                        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/mohlomi_institute_logo.png" 
                             alt="Mohlomi Institute" 
                             class="h-8 sm:h-10 w-auto">
                    </div>
                    
                    <!-- Welcome Text - Hidden on mobile, shown on sm+ -->
                    <div class="hidden sm:block ml-4 min-w-0">
                        <h1 class="text-lg sm:text-xl lg:text-2xl font-bold text-gray-900 truncate">
                            Welcome back, <?php echo esc_html(wp_get_current_user()->display_name); ?>!
                        </h1>
                        <p class="text-sm text-gray-600 hidden md:block">Track your PMP exam preparation progress</p>
                    </div>
                </div>
                
                <!-- Right Section: Actions & Menu -->
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
                            onclick="toggleMobileMenu()">
                        <span class="sr-only">Open main menu</span>
                        <i class="fas fa-bars text-lg" id="menu-icon"></i>
                    </button>
                </div>
            </div>
            
            <!-- Mobile Welcome Text - Shown only on mobile -->
            <div class="sm:hidden pb-4">
                <h1 class="text-lg font-bold text-gray-900 truncate">
                    Welcome, <?php echo esc_html(explode(' ', wp_get_current_user()->display_name)[0]); ?>!
                </h1>
                <div class="flex items-center justify-between mt-2">
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
    </header>

    <script>
    function toggleMobileMenu() {
        const menu = document.getElementById('mobile-menu');
        const icon = document.getElementById('menu-icon');
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
        const menu = document.getElementById('mobile-menu');
        const button = event.target.closest('button');
        
        if (!menu.contains(event.target) && !button) {
            menu.classList.add('hidden');
            document.getElementById('menu-icon').classList.remove('fa-times');
            document.getElementById('menu-icon').classList.add('fa-bars');
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

<?php get_footer(); ?>
