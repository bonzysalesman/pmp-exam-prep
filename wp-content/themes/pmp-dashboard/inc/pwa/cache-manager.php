<?php
/**
 * PWA Cache Management System
 * Task: T004 - Cache Management System
 */

class PMP_Cache_Manager {
    
    const CACHE_VERSION = '1.0.0';
    const MAX_CACHE_SIZE = 50; // MB
    const CACHE_EXPIRY = 7 * 24 * 60 * 60; // 7 days in seconds
    
    public static function init() {
        add_action('wp_ajax_pwa_cache_status', [__CLASS__, 'ajax_cache_status']);
        add_action('wp_ajax_pwa_clear_cache', [__CLASS__, 'ajax_clear_cache']);
        add_action('wp_ajax_pwa_preload_content', [__CLASS__, 'ajax_preload_content']);
        add_filter('pwa_cache_resources', [__CLASS__, 'get_cache_resources']);
    }
    
    /**
     * Get cache configuration for service worker
     */
    public static function get_cache_config() {
        return [
            'version' => self::CACHE_VERSION,
            'max_size' => self::MAX_CACHE_SIZE,
            'expiry' => self::CACHE_EXPIRY,
            'strategies' => [
                'app_shell' => 'cache-first',
                'content' => 'stale-while-revalidate',
                'api' => 'network-first',
                'images' => 'cache-first',
                'fonts' => 'cache-first'
            ],
            'resources' => self::get_cache_resources()
        ];
    }
    
    /**
     * Get resources to cache
     */
    public static function get_cache_resources() {
        $theme_uri = get_template_directory_uri();
        
        $resources = [
            'app_shell' => [
                home_url('/'),
                $theme_uri . '/style.css',
                $theme_uri . '/assets/js/main.js',
                $theme_uri . '/assets/js/pwa-register.js',
                $theme_uri . '/assets/css/critical.css',
                $theme_uri . '/manifest.json'
            ],
            'critical_pages' => [
                home_url('/dashboard'),
                home_url('/lessons'),
                home_url('/practice-tests'),
                home_url('/resources')
            ],
            'offline_fallbacks' => [
                $theme_uri . '/offline.html',
                $theme_uri . '/assets/images/offline-placeholder.png'
            ]
        ];
        
        return apply_filters('pwa_cache_resources', $resources);
    }
    
    /**
     * Get cache invalidation triggers
     */
    public static function get_invalidation_triggers() {
        return [
            'post_updated' => ['content'],
            'theme_switched' => ['app_shell'],
            'plugin_activated' => ['app_shell'],
            'user_progress_updated' => ['api'],
            'test_completed' => ['api']
        ];
    }
    
    /**
     * AJAX: Get cache status
     */
    public static function ajax_cache_status() {
        check_ajax_referer('pwa_cache_nonce', 'nonce');
        
        $status = [
            'version' => self::CACHE_VERSION,
            'config' => self::get_cache_config(),
            'storage_estimate' => self::get_storage_estimate(),
            'cache_stats' => self::get_cache_statistics()
        ];
        
        wp_send_json_success($status);
    }
    
    /**
     * AJAX: Clear cache
     */
    public static function ajax_clear_cache() {
        check_ajax_referer('pwa_cache_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        // Trigger cache clear via service worker message
        $result = [
            'action' => 'clear_cache',
            'timestamp' => time(),
            'version' => self::CACHE_VERSION
        ];
        
        wp_send_json_success($result);
    }
    
    /**
     * AJAX: Preload content
     */
    public static function ajax_preload_content() {
        check_ajax_referer('pwa_cache_nonce', 'nonce');
        
        $content_ids = array_map('intval', $_POST['content_ids'] ?? []);
        $content_type = sanitize_text_field($_POST['content_type'] ?? 'lesson');
        
        $preload_urls = [];
        
        foreach ($content_ids as $content_id) {
            $url = get_permalink($content_id);
            if ($url) {
                $preload_urls[] = $url;
            }
        }
        
        wp_send_json_success([
            'urls' => $preload_urls,
            'count' => count($preload_urls)
        ]);
    }
    
    /**
     * Get storage estimate (placeholder for client-side implementation)
     */
    private static function get_storage_estimate() {
        return [
            'quota' => 0, // Will be filled by client-side
            'usage' => 0, // Will be filled by client-side
            'available' => 0 // Will be filled by client-side
        ];
    }
    
    /**
     * Get cache statistics
     */
    private static function get_cache_statistics() {
        return [
            'total_resources' => count(self::get_cache_resources()['app_shell']),
            'last_updated' => get_option('pwa_cache_last_updated', time()),
            'cache_hits' => get_option('pwa_cache_hits', 0),
            'cache_misses' => get_option('pwa_cache_misses', 0)
        ];
    }
    
    /**
     * Update cache statistics
     */
    public static function update_cache_stats($type, $increment = 1) {
        $current = get_option("pwa_cache_{$type}", 0);
        update_option("pwa_cache_{$type}", $current + $increment);
    }
    
    /**
     * Get content priority for caching
     */
    public static function get_content_priority($content_id, $user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        $priority = 5; // Default priority
        
        // Higher priority for current lesson
        $current_lesson = get_user_meta($user_id, 'current_lesson_id', true);
        if ($content_id == $current_lesson) {
            $priority = 1;
        }
        
        // Check if it's next in sequence
        $next_lessons = PMP_Sequence_Manager::get_next_content($user_id, 3);
        $next_ids = wp_list_pluck($next_lessons, 'content_id');
        if (in_array($content_id, $next_ids)) {
            $priority = 2;
        }
        
        // Check if it's a practice test
        if (get_post_type($content_id) === 'practice_test') {
            $priority = 3;
        }
        
        // Check if it's completed (lower priority)
        global $wpdb;
        $completed = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}pmp_lesson_progress 
             WHERE user_id = %d AND lesson_id = %d AND status = 'completed'",
            $user_id, $content_id
        ));
        
        if ($completed) {
            $priority = 5;
        }
        
        return $priority;
    }
    
    /**
     * Generate cache manifest for offline content
     */
    public static function generate_cache_manifest($user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        $manifest = [
            'version' => self::CACHE_VERSION,
            'timestamp' => time(),
            'user_id' => $user_id,
            'content' => []
        ];
        
        // Get user's current and next lessons
        $next_content = PMP_Sequence_Manager::get_next_content($user_id, 10);
        
        foreach ($next_content as $content) {
            $manifest['content'][] = [
                'id' => $content['content_id'],
                'url' => get_permalink($content['content_id']),
                'type' => $content['type'],
                'priority' => self::get_content_priority($content['content_id'], $user_id),
                'estimated_size' => self::estimate_content_size($content['content_id'])
            ];
        }
        
        // Sort by priority
        usort($manifest['content'], function($a, $b) {
            return $a['priority'] - $b['priority'];
        });
        
        return $manifest;
    }
    
    /**
     * Estimate content size for caching decisions
     */
    private static function estimate_content_size($content_id) {
        $post = get_post($content_id);
        if (!$post) {
            return 0;
        }
        
        // Base size for HTML content
        $size = strlen($post->post_content) * 2; // Rough estimate including HTML
        
        // Add image sizes
        $images = get_attached_media('image', $content_id);
        foreach ($images as $image) {
            $file_size = filesize(get_attached_file($image->ID));
            $size += $file_size ?: 50000; // Default 50KB if can't determine
        }
        
        // Add video sizes (if any)
        $video_url = get_post_meta($content_id, '_video_url', true);
        if ($video_url) {
            $size += 5000000; // Estimate 5MB for video content
        }
        
        return $size;
    }
    
    /**
     * Check if content should be cached offline
     */
    public static function should_cache_content($content_id, $user_id = null) {
        // Check content priority
        $priority = self::get_content_priority($content_id, $user_id);
        
        // Only cache high priority content (1-3)
        if ($priority > 3) {
            return false;
        }
        
        // Check content size
        $size = self::estimate_content_size($content_id);
        $max_content_size = 10 * 1024 * 1024; // 10MB max per content
        
        if ($size > $max_content_size) {
            return false;
        }
        
        // Check if user has enough storage quota
        // This would be checked on client-side
        
        return true;
    }
    
    /**
     * Get cache cleanup recommendations
     */
    public static function get_cleanup_recommendations($user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        $recommendations = [];
        
        // Find completed content that can be removed
        global $wpdb;
        $completed_content = $wpdb->get_results($wpdb->prepare(
            "SELECT lesson_id, updated_at FROM {$wpdb->prefix}pmp_lesson_progress 
             WHERE user_id = %d AND status = 'completed' 
             AND updated_at < DATE_SUB(NOW(), INTERVAL 30 DAY)
             ORDER BY updated_at ASC",
            $user_id
        ));
        
        foreach ($completed_content as $content) {
            $recommendations[] = [
                'content_id' => $content->lesson_id,
                'reason' => 'Completed over 30 days ago',
                'size_estimate' => self::estimate_content_size($content->lesson_id),
                'priority' => 'low'
            ];
        }
        
        return $recommendations;
    }
}

// Initialize
PMP_Cache_Manager::init();
?>
