<?php
/**
 * PMP Dashboard Theme Functions
 */

// Theme setup
function pmp_theme_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', array('search-form', 'comment-form', 'comment-list', 'gallery', 'caption'));
    
    register_nav_menus(array(
        'primary' => 'Primary Menu',
        'mobile' => 'Mobile Menu'
    ));
}
add_action('after_setup_theme', 'pmp_theme_setup');

// Enqueue scripts and styles
function pmp_enqueue_assets() {
    // Remove Tailwind CDN for production - use local build instead
    wp_enqueue_style('fontawesome', 'https://use.fontawesome.com/releases/v6.0.0/css/all.css');
    wp_enqueue_style('google-fonts', 'https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@300;400;600;900&display=swap');
    wp_enqueue_style('pmp-style', get_stylesheet_uri());
    
    // Add Tailwind via inline styles for now (replace with build process later)
    wp_add_inline_style('pmp-style', '
        /* Tailwind CSS Reset and Base Styles */
        *, ::before, ::after { box-sizing: border-box; border-width: 0; border-style: solid; border-color: #e5e7eb; }
        ::before, ::after { --tw-content: ""; }
        html { line-height: 1.5; -webkit-text-size-adjust: 100%; -moz-tab-size: 4; tab-size: 4; font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji"; font-feature-settings: normal; font-variation-settings: normal; }
        body { margin: 0; line-height: inherit; }
        /* Add essential Tailwind utilities here */
    ');
    
    wp_enqueue_script('pmp-dashboard', get_template_directory_uri() . '/assets/js/dashboard.js', array('jquery'), '1.0', true);
    
    // Chart.js for progress visualizations
    wp_enqueue_script('chartjs', 'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.min.js', array(), '4.4.0', true);
    
    // Progress tracking scripts
    wp_enqueue_script('progress-tracker', get_template_directory_uri() . '/assets/js/progress-tracker.js', array('jquery'), '1.0', true);
    wp_enqueue_script('analytics-charts', get_template_directory_uri() . '/assets/js/analytics-charts.js', array('chartjs'), '1.0', true);
    
    wp_localize_script('pmp-dashboard', 'pmp_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('pmp_nonce'),
        'rest_url' => rest_url('pmp/v1/'),
        'rest_nonce' => wp_create_nonce('wp_rest')
    ));
    
    // PWA Service Worker registration
    wp_add_inline_script('pmp-dashboard', "
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('" . get_template_directory_uri() . "/sw.js')
                    .then(registration => {
                        console.log('SW registered: ', registration);
                    })
                    .catch(registrationError => {
                        console.log('SW registration failed: ', registrationError);
                    });
            });
        }
    ");
}
add_action('wp_enqueue_scripts', 'pmp_enqueue_assets');

// Include progress tracking system
require_once get_template_directory() . '/inc/progress-database.php';
require_once get_template_directory() . '/inc/progress-tracking.php';
require_once get_template_directory() . '/inc/progress-api.php';

// Include existing user progress functionality
require_once get_template_directory() . '/inc/user-progress.php';
require_once get_template_directory() . '/inc/user-progress.php';
require_once get_template_directory() . '/inc/ajax-handlers.php';

// Create database tables on theme activation
function pmp_create_tables() {
    global $wpdb;
    
    $charset_collate = $wpdb->get_charset_collate();
    
    // User lesson progress table
    $table_name = $wpdb->prefix . 'user_lesson_progress';
    $sql = "CREATE TABLE $table_name (
        id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id bigint(20) UNSIGNED NOT NULL,
        lesson_id bigint(20) UNSIGNED NOT NULL,
        status enum('not_started','in_progress','completed') DEFAULT 'not_started',
        progress_percentage tinyint(3) UNSIGNED DEFAULT 0,
        time_spent int(11) UNSIGNED DEFAULT 0,
        started_at datetime NULL,
        completed_at datetime NULL,
        last_accessed datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY unique_user_lesson (user_id, lesson_id),
        KEY idx_user_id (user_id),
        KEY idx_lesson_id (lesson_id)
    ) $charset_collate;";
    
    // User test results table
    $table_name2 = $wpdb->prefix . 'user_test_results';
    $sql2 = "CREATE TABLE $table_name2 (
        id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id bigint(20) UNSIGNED NOT NULL,
        test_id bigint(20) UNSIGNED NOT NULL,
        score decimal(5,2) NOT NULL,
        percentage decimal(5,2) NOT NULL,
        time_taken int(11) UNSIGNED NOT NULL,
        attempt_number tinyint(3) UNSIGNED DEFAULT 1,
        taken_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY idx_user_test (user_id, test_id)
    ) $charset_collate;";
    
    // Study sessions table
    $table_name3 = $wpdb->prefix . 'study_sessions';
    $sql3 = "CREATE TABLE $table_name3 (
        id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id bigint(20) UNSIGNED NOT NULL,
        session_date date NOT NULL,
        duration int(11) UNSIGNED NOT NULL,
        lessons_completed tinyint(3) UNSIGNED DEFAULT 0,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY unique_user_date (user_id, session_date)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
    dbDelta($sql2);
    dbDelta($sql3);
}
add_action('after_switch_theme', 'pmp_create_tables');

// Add custom user roles
function pmp_add_user_roles() {
    add_role('pmp_student', 'PMP Student', array(
        'read' => true,
        'access_lessons' => true,
        'take_tests' => true,
        'view_progress' => true
    ));
    
    add_role('pmp_instructor', 'PMP Instructor', array(
        'read' => true,
        'access_lessons' => true,
        'take_tests' => true,
        'view_progress' => true,
        'edit_lessons' => true,
        'view_student_progress' => true,
        'manage_tests' => true
    ));
}
add_action('after_switch_theme', 'pmp_add_user_roles');

/**
 * Displays the current template file being used on the frontend for logged-in admins.
 */
function display_template_file_on_frontend() {
    // Only show for logged-in administrators
    if ( is_user_logged_in() && current_user_can( 'manage_options' ) ) {
        global $template;
        $template_file = basename( $template );
        echo '<div style="position: fixed; bottom: 0; right: 0; background: #282c34; color: #fff; padding: 10px 15px; font-size: 14px; z-index: 9999; border-top-left-radius: 5px;">';
        echo 'Template: <strong>' . esc_html( $template_file ) . '</strong>';
        echo '</div>';
    }
}
add_action( 'wp_footer', 'display_template_file_on_frontend' );

// Hide wp-admin for all users
function pmp_hide_admin_bar() {
    show_admin_bar(false);
}
add_action('after_setup_theme', 'pmp_hide_admin_bar');

// Redirect non-admin users away from wp-admin
function pmp_redirect_from_admin() {
    if (is_admin() && !wp_doing_ajax() && !current_user_can('manage_options')) {
        wp_redirect(get_permalink(get_page_by_path('dashboard')));
        exit;
    }
}
add_action('admin_init', 'pmp_redirect_from_admin');
?>
