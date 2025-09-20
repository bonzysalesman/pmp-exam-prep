<?php
/**
 * Quick Theme Test Script
 * Place in WordPress root and access via browser to test theme functionality
 */

// Load WordPress
require_once('wp-config.php');
require_once('wp-load.php');

// Test custom post types
echo "<h2>Testing Custom Post Types</h2>";
$work_groups = get_posts(array('post_type' => 'work_group', 'posts_per_page' => -1));
echo "<p>Work Groups found: " . count($work_groups) . "</p>";

$lessons = get_posts(array('post_type' => 'lesson', 'posts_per_page' => -1));
echo "<p>Lessons found: " . count($lessons) . "</p>";

// Test database tables
echo "<h2>Testing Database Tables</h2>";
global $wpdb;

$progress_table = $wpdb->prefix . 'user_lesson_progress';
$sessions_table = $wpdb->prefix . 'study_sessions';

$progress_count = $wpdb->get_var("SELECT COUNT(*) FROM $progress_table");
echo "<p>Progress records: " . ($progress_count ?: 0) . "</p>";

$sessions_count = $wpdb->get_var("SELECT COUNT(*) FROM $sessions_table");
echo "<p>Study sessions: " . ($sessions_count ?: 0) . "</p>";

// Test progress functions
echo "<h2>Testing Progress Functions</h2>";
if (function_exists('pmp_get_user_progress')) {
    $test_user_id = 1; // Adjust based on your test user
    $progress = pmp_get_user_progress($test_user_id);
    echo "<pre>" . print_r($progress, true) . "</pre>";
} else {
    echo "<p>Progress functions not loaded</p>";
}

// Test AJAX endpoints
echo "<h2>Testing AJAX Endpoints</h2>";
$ajax_actions = array(
    'update_lesson_progress',
    'get_dashboard_data',
    'submit_practice_test'
);

foreach ($ajax_actions as $action) {
    if (has_action("wp_ajax_$action")) {
        echo "<p>✓ $action registered</p>";
    } else {
        echo "<p>✗ $action missing</p>";
    }
}

// Test theme files
echo "<h2>Testing Theme Files</h2>";
$theme_dir = get_template_directory();
$required_files = array(
    'functions.php',
    'index.php',
    'front-page.php',
    'page.php',
    'single.php',
    'style.css'
);

foreach ($required_files as $file) {
    if (file_exists($theme_dir . '/' . $file)) {
        echo "<p>✓ $file exists</p>";
    } else {
        echo "<p>✗ $file missing</p>";
    }
}

echo "<h2>Theme Testing Complete</h2>";
echo "<p><a href='" . home_url() . "'>Visit Homepage</a></p>";
echo "<p><a href='" . home_url('/dashboard/') . "'>Visit Dashboard</a></p>";
?>
