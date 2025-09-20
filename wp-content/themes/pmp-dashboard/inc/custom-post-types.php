<?php
/**
 * Custom Post Types for PMP Dashboard
 */

// Register Work Group post type
function register_work_group_post_type() {
    $args = array(
        'labels' => array(
            'name' => 'Work Groups',
            'singular_name' => 'Work Group',
            'add_new' => 'Add New Work Group',
            'add_new_item' => 'Add New Work Group',
            'edit_item' => 'Edit Work Group',
            'new_item' => 'New Work Group',
            'view_item' => 'View Work Group',
            'search_items' => 'Search Work Groups',
            'not_found' => 'No work groups found',
            'not_found_in_trash' => 'No work groups found in trash'
        ),
        'public' => true,
        'has_archive' => true,
        'supports' => array('title', 'editor', 'thumbnail', 'custom-fields'),
        'menu_icon' => 'dashicons-groups',
        'rewrite' => array('slug' => 'work-group'),
        'show_in_rest' => true
    );
    register_post_type('work_group', $args);
}
add_action('init', 'register_work_group_post_type');

// Register Lesson post type
function register_lesson_post_type() {
    $args = array(
        'labels' => array(
            'name' => 'Lessons',
            'singular_name' => 'Lesson',
            'add_new' => 'Add New Lesson',
            'add_new_item' => 'Add New Lesson',
            'edit_item' => 'Edit Lesson',
            'new_item' => 'New Lesson',
            'view_item' => 'View Lesson',
            'search_items' => 'Search Lessons',
            'not_found' => 'No lessons found',
            'not_found_in_trash' => 'No lessons found in trash'
        ),
        'public' => true,
        'has_archive' => true,
        'supports' => array('title', 'editor', 'thumbnail', 'custom-fields', 'comments'),
        'menu_icon' => 'dashicons-video-alt3',
        'rewrite' => array('slug' => 'lesson'),
        'show_in_rest' => true,
        'hierarchical' => true
    );
    register_post_type('lesson', $args);
}
add_action('init', 'register_lesson_post_type');

// Register Practice Test post type
function register_practice_test_post_type() {
    $args = array(
        'labels' => array(
            'name' => 'Practice Tests',
            'singular_name' => 'Practice Test',
            'add_new' => 'Add New Test',
            'add_new_item' => 'Add New Practice Test',
            'edit_item' => 'Edit Practice Test',
            'new_item' => 'New Practice Test',
            'view_item' => 'View Practice Test',
            'search_items' => 'Search Practice Tests',
            'not_found' => 'No practice tests found',
            'not_found_in_trash' => 'No practice tests found in trash'
        ),
        'public' => true,
        'has_archive' => true,
        'supports' => array('title', 'editor', 'custom-fields'),
        'menu_icon' => 'dashicons-clipboard',
        'rewrite' => array('slug' => 'practice-test'),
        'show_in_rest' => true
    );
    register_post_type('practice_test', $args);
}
add_action('init', 'register_practice_test_post_type');

// Register Domain taxonomy
function register_domain_taxonomy() {
    $args = array(
        'labels' => array(
            'name' => 'Domains',
            'singular_name' => 'Domain',
            'search_items' => 'Search Domains',
            'all_items' => 'All Domains',
            'edit_item' => 'Edit Domain',
            'update_item' => 'Update Domain',
            'add_new_item' => 'Add New Domain',
            'new_item_name' => 'New Domain Name',
            'menu_name' => 'Domains'
        ),
        'hierarchical' => true,
        'public' => true,
        'show_in_rest' => true,
        'rewrite' => array('slug' => 'domain')
    );
    register_taxonomy('domain', array('work_group', 'lesson', 'practice_test'), $args);
}
add_action('init', 'register_domain_taxonomy');

// Register Difficulty taxonomy
function register_difficulty_taxonomy() {
    $args = array(
        'labels' => array(
            'name' => 'Difficulty Levels',
            'singular_name' => 'Difficulty',
            'search_items' => 'Search Difficulty Levels',
            'all_items' => 'All Difficulty Levels',
            'edit_item' => 'Edit Difficulty',
            'update_item' => 'Update Difficulty',
            'add_new_item' => 'Add New Difficulty',
            'new_item_name' => 'New Difficulty Name',
            'menu_name' => 'Difficulty'
        ),
        'hierarchical' => true,
        'public' => true,
        'show_in_rest' => true,
        'rewrite' => array('slug' => 'difficulty')
    );
    register_taxonomy('difficulty', array('lesson', 'practice_test'), $args);
}
add_action('init', 'register_difficulty_taxonomy');

// Add default terms
function add_default_taxonomy_terms() {
    // Add default domains
    if (!term_exists('People', 'domain')) {
        wp_insert_term('People', 'domain', array('description' => 'People Domain - Leadership and team management'));
    }
    if (!term_exists('Process', 'domain')) {
        wp_insert_term('Process', 'domain', array('description' => 'Process Domain - Technical project management'));
    }
    if (!term_exists('Business Environment', 'domain')) {
        wp_insert_term('Business Environment', 'domain', array('description' => 'Business Environment - Strategy and compliance'));
    }
    
    // Add default difficulty levels
    if (!term_exists('Beginner', 'difficulty')) {
        wp_insert_term('Beginner', 'difficulty');
    }
    if (!term_exists('Intermediate', 'difficulty')) {
        wp_insert_term('Intermediate', 'difficulty');
    }
    if (!term_exists('Advanced', 'difficulty')) {
        wp_insert_term('Advanced', 'difficulty');
    }
}
add_action('init', 'add_default_taxonomy_terms');
?>
