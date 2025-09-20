<?php
/**
 * Foundation Test Script for Content Management System
 * Tests T001-T006 implementation
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

function pmp_test_foundation() {
    echo "<h2>🧪 PMP Content Management Foundation Test</h2>";
    
    $tests_passed = 0;
    $total_tests = 0;
    
    // Test 1: Database Tables
    echo "<h3>T001: Database Schema Test</h3>";
    $total_tests++;
    if (PMP_Content_Database::verify_tables()) {
        echo "✅ All database tables exist<br>";
        $stats = PMP_Content_Database::get_table_stats();
        echo "📊 Table stats: " . json_encode($stats) . "<br>";
        $tests_passed++;
    } else {
        echo "❌ Missing database tables<br>";
    }
    
    // Test 2: Post Types
    echo "<h3>T002: Custom Post Types Test</h3>";
    $total_tests++;
    $post_types = ['practice_test', 'resource', 'question'];
    $registered_types = get_post_types(['public' => true]);
    $all_registered = true;
    
    foreach ($post_types as $type) {
        if (post_type_exists($type)) {
            echo "✅ Post type '$type' registered<br>";
        } else {
            echo "❌ Post type '$type' not found<br>";
            $all_registered = false;
        }
    }
    
    if ($all_registered) $tests_passed++;
    
    // Test 3: Taxonomies
    echo "<h3>T003: Taxonomies Test</h3>";
    $total_tests++;
    $taxonomies = ['pmp_domain', 'pmp_knowledge_area', 'pmp_topic', 'resource_category', 'difficulty_level', 'content_tags'];
    $all_tax_registered = true;
    
    foreach ($taxonomies as $taxonomy) {
        if (taxonomy_exists($taxonomy)) {
            echo "✅ Taxonomy '$taxonomy' registered<br>";
            
            // Check for default terms
            $terms = get_terms(['taxonomy' => $taxonomy, 'hide_empty' => false]);
            if (!empty($terms)) {
                echo "&nbsp;&nbsp;📋 " . count($terms) . " terms found<br>";
            }
        } else {
            echo "❌ Taxonomy '$taxonomy' not found<br>";
            $all_tax_registered = false;
        }
    }
    
    if ($all_tax_registered) $tests_passed++;
    
    // Test 4: Content Manager
    echo "<h3>T004: Content Manager Test</h3>";
    $total_tests++;
    if (class_exists('PMP_Content_Manager')) {
        echo "✅ PMP_Content_Manager class exists<br>";
        
        // Test getting content stats
        $stats = PMP_Content_Manager::get_content_stats();
        echo "📊 Content stats: " . json_encode($stats) . "<br>";
        $tests_passed++;
    } else {
        echo "❌ PMP_Content_Manager class not found<br>";
    }
    
    // Test 5: Sequence Manager
    echo "<h3>T005: Sequence Manager Test</h3>";
    $total_tests++;
    if (class_exists('PMP_Sequence_Manager')) {
        echo "✅ PMP_Sequence_Manager class exists<br>";
        
        // Test with current user (if logged in)
        if (is_user_logged_in()) {
            $user_id = get_current_user_id();
            $recommendations = PMP_Sequence_Manager::get_next_content($user_id, 3);
            echo "🎯 Next content recommendations: " . count($recommendations) . " items<br>";
        }
        $tests_passed++;
    } else {
        echo "❌ PMP_Sequence_Manager class not found<br>";
    }
    
    // Test 6: Search Engine
    echo "<h3>T006: Search Engine Test</h3>";
    $total_tests++;
    if (class_exists('PMP_Search_Engine')) {
        echo "✅ PMP_Search_Engine class exists<br>";
        
        // Test search functionality
        $search_results = PMP_Search_Engine::search('test', ['posts_per_page' => 5]);
        echo "🔍 Search test returned " . $search_results->found_posts . " results<br>";
        
        // Test suggestions
        $suggestions = PMP_Search_Engine::get_suggestions('project', 3);
        echo "💡 Suggestions test returned " . count($suggestions) . " suggestions<br>";
        $tests_passed++;
    } else {
        echo "❌ PMP_Search_Engine class not found<br>";
    }
    
    // Test Summary
    echo "<h3>📋 Test Summary</h3>";
    $pass_rate = ($tests_passed / $total_tests) * 100;
    
    if ($pass_rate == 100) {
        echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px;'>";
        echo "🎉 <strong>ALL TESTS PASSED!</strong><br>";
        echo "✅ {$tests_passed}/{$total_tests} tests successful ({$pass_rate}%)<br>";
        echo "🚀 Foundation is ready for Phase 2 implementation!";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px;'>";
        echo "⚠️ <strong>SOME TESTS FAILED</strong><br>";
        echo "📊 {$tests_passed}/{$total_tests} tests successful ({$pass_rate}%)<br>";
        echo "🔧 Please fix failing tests before continuing to Phase 2";
        echo "</div>";
    }
    
    // Additional Info
    echo "<h3>ℹ️ System Information</h3>";
    echo "WordPress Version: " . get_bloginfo('version') . "<br>";
    echo "PHP Version: " . PHP_VERSION . "<br>";
    echo "Current User: " . (is_user_logged_in() ? wp_get_current_user()->display_name : 'Not logged in') . "<br>";
    echo "Active Theme: " . get_template() . "<br>";
    
    return $pass_rate == 100;
}

// Add admin page for testing
function pmp_add_test_page() {
    add_management_page(
        'PMP Foundation Test',
        'PMP Foundation Test',
        'manage_options',
        'pmp-foundation-test',
        'pmp_test_page_callback'
    );
}
add_action('admin_menu', 'pmp_add_test_page');

function pmp_test_page_callback() {
    echo '<div class="wrap">';
    echo '<h1>PMP Content Management Foundation Test</h1>';
    
    if (isset($_GET['run_test'])) {
        pmp_test_foundation();
    } else {
        echo '<p>This will test the foundation components of the Content Management System (T001-T006).</p>';
        echo '<a href="?page=pmp-foundation-test&run_test=1" class="button button-primary">Run Foundation Test</a>';
    }
    
    echo '</div>';
}

// Quick test function for frontend
function pmp_quick_test() {
    if (!current_user_can('manage_options')) {
        return "Access denied";
    }
    
    $results = [];
    
    // Quick checks
    $results['database'] = PMP_Content_Database::verify_tables();
    $results['post_types'] = post_type_exists('practice_test') && post_type_exists('resource') && post_type_exists('question');
    $results['taxonomies'] = taxonomy_exists('pmp_domain') && taxonomy_exists('pmp_knowledge_area');
    $results['classes'] = class_exists('PMP_Content_Manager') && class_exists('PMP_Sequence_Manager') && class_exists('PMP_Search_Engine');
    
    $passed = array_sum($results);
    $total = count($results);
    
    return "Foundation Test: {$passed}/{$total} components working (" . round(($passed/$total)*100) . "%)";
}
?>
