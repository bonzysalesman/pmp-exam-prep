<?php
/**
 * Template Name: Dashboard
 */

get_header(); 

// Redirect if not logged in
if (!is_user_logged_in()) {
    wp_redirect(wp_login_url(get_permalink()));
    exit;
}

$user_id = get_current_user_id();
$user_progress = pmp_get_user_progress($user_id);
$current_user = wp_get_current_user();
?>

<div class="flex h-screen overflow-hidden">
    <!-- Sidebar -->
    <div id="sidebar" class="fixed inset-y-0 left-0 z-50 w-64 bg-white shadow-lg transform -translate-x-full transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0 lg:block lg:flex-shrink-0 flex flex-col">
        <?php get_template_part('template-parts/navigation/sidebar-nav'); ?>
    </div>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-hidden">
        <!-- Header -->
        <header class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-40 flex-shrink-0">
            <div class="flex items-center justify-between h-16 px-4 sm:px-6">
                <div class="flex items-center">
                    <button id="openSidebar" class="lg:hidden text-gray-500 hover:text-gray-700 mr-3 sm:mr-4">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                    <div>
                        <p class="text-sm text-gray-500">Welcome back, <?php echo esc_html($current_user->display_name); ?></p>
                    </div>
                </div>
                
                <!-- In-page Navigation -->
                <nav class="hidden md:flex space-x-6 lg:space-x-8">
                    <a href="#overview" class="text-primary border-b-2 border-primary pb-2 text-sm font-medium">Overview</a>
                    <a href="#progress" class="text-gray-500 hover:text-primary pb-2 text-sm font-medium transition-colors">Progress</a>
                    <a href="#activity" class="text-gray-500 hover:text-primary pb-2 text-sm font-medium transition-colors">Activity</a>
                </nav>

                <div class="flex items-center space-x-2 sm:space-x-4">
                    <button class="text-gray-500 hover:text-gray-700 relative">
                        <i class="fas fa-bell text-lg"></i>
                        <span class="absolute -top-1 -right-1 w-3 h-3 bg-red-500 rounded-full"></span>
                    </button>
                    <div class="relative">
                        <button class="w-8 h-8 bg-primary rounded-full flex items-center justify-center" onclick="toggleUserDropdown()">
                            <span class="text-white text-sm font-medium"><?php echo esc_html(substr($current_user->display_name, 0, 1)); ?></span>
                        </button>
                        <div id="userAccountDropdown" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-50">
                            <a href="<?php echo esc_url(get_edit_user_link()); ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-t-lg">Manage Account</a>
                            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Account Settings</a>
                            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Billing</a>
                            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Profile</a>
                            <hr class="my-1">
                            <a href="<?php echo esc_url(wp_logout_url(home_url())); ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-b-lg">Logout</a>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content Area -->
        <main class="flex-1 overflow-y-auto p-3 sm:p-4 lg:p-6 pb-20 md:pb-6">
            <!-- Continue Learning Hero Section -->
            <section id="overview" class="mb-6 lg:mb-8">
                <?php get_template_part('template-parts/dashboard/hero-section'); ?>
                <?php get_template_part('template-parts/dashboard/progress-cards'); ?>
            </section>

            <!-- Progress Section -->
            <section id="progress" class="mb-6 lg:mb-8">
                <div class="grid grid-cols-1 xl:grid-cols-2 gap-4 lg:gap-6">
                    <?php get_template_part('template-parts/dashboard/work-groups-accordion'); ?>
                    <?php get_template_part('template-parts/dashboard/study-statistics'); ?>
                </div>
            </section>

            <!-- Recent Activity -->
            <section id="activity">
                <?php get_template_part('template-parts/dashboard/activity-feed'); ?>
            </section>

            <!-- Footer -->
            <?php get_template_part('template-parts/dashboard/footer'); ?>
        </main>
    </div>
</div>

<!-- Mobile Bottom Navigation -->
<div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 md:hidden z-50">
    <?php get_template_part('template-parts/navigation/mobile-nav'); ?>
</div>

<!-- Sidebar Overlay -->
<div id="sidebarOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden hidden"></div>

<script>
// Dashboard-specific JavaScript
jQuery(document).ready(function($) {
    // Load dashboard data
    loadDashboardData();
    
    // Auto-refresh every 5 minutes
    setInterval(loadDashboardData, 300000);
});

function loadDashboardData() {
    jQuery.post(pmp_ajax.ajax_url, {
        action: 'get_dashboard_data',
        nonce: pmp_ajax.nonce
    }, function(response) {
        if (response.success) {
            updateDashboardUI(response.data);
        }
    });
}

function updateDashboardUI(data) {
    // Update progress indicators
    if (data.user_progress) {
        jQuery('.study-streak').text(data.user_progress.study_streak + ' days');
        jQuery('.week-time').text(data.user_progress.week_time + 'h');
        jQuery('.overall-progress').text(data.user_progress.completion_percentage + '%');
    }
}
</script>

<?php get_footer(); ?>
