<?php
/**
 * Template Name: Dashboard
 */

get_header(); ?>

<div class="min-h-screen bg-gray-50">
    <!-- Dashboard Header -->
    <div class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div class="flex items-center space-x-4">
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/mohlomi_institute_logo.png" 
                         alt="Mohlomi Institute" 
                         class="h-10 w-auto">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">
                            Welcome back, <?php echo esc_html(wp_get_current_user()->display_name); ?>!
                        </h1>
                        <p class="text-gray-600">Track your PMP exam preparation progress</p>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="<?php echo esc_url(get_post_type_archive_link('lesson')); ?>" 
                       class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary-dark transition-colors">
                        <i class="fas fa-book mr-2"></i>Continue Learning
                    </a>
                </div>
            </div>
        </div>
    </div>

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
