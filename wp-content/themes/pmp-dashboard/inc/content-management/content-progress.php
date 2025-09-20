<?php
/**
 * Content Progress Integration
 * Task: T008 - Integration with Progress Tracking
 */

class PMP_Content_Progress extends PMP_Progress_Tracker {
    
    /**
     * Track content completion (extends lesson progress)
     */
    public static function update_content_progress($user_id, $content_id, $content_type, $status, $progress_percentage = 100, $time_spent = 0) {
        global $wpdb;
        
        // Use existing lesson progress table for all content types
        $result = parent::update_lesson_progress($user_id, $content_id, $status, $progress_percentage, $time_spent);
        
        if ($result) {
            // Update learning path progress if content is part of a path
            self::update_learning_path_progress($user_id, $content_id, $content_type);
            
            // Update domain progress based on content taxonomy
            self::update_domain_progress_from_content($user_id, $content_id);
            
            // Track content-specific metrics
            self::track_content_engagement($user_id, $content_id, $content_type, $time_spent);
        }
        
        return $result;
    }
    
    /**
     * Update learning path progress when content is completed
     */
    private static function update_learning_path_progress($user_id, $content_id, $content_type) {
        global $wpdb;
        
        // Find learning paths containing this content
        $paths = $wpdb->get_results($wpdb->prepare(
            "SELECT DISTINCT path_id FROM {$wpdb->prefix}pmp_learning_path_content 
             WHERE content_id = %d AND content_type = %s",
            $content_id, $content_type
        ));
        
        foreach ($paths as $path) {
            $progress = PMP_Sequence_Manager::update_path_progress($user_id, $path->path_id, $content_id);
            
            // Store updated progress in user meta
            update_user_meta($user_id, "learning_path_{$path->path_id}_progress", $progress);
        }
    }
    
    /**
     * Update domain progress based on content taxonomy
     */
    private static function update_domain_progress_from_content($user_id, $content_id) {
        // Get content domains
        $domains = wp_get_post_terms($content_id, 'pmp_domain');
        
        foreach ($domains as $domain) {
            // Get all content in this domain
            $domain_content = get_posts([
                'post_type' => ['lesson', 'practice_test', 'resource'],
                'post_status' => 'publish',
                'posts_per_page' => -1,
                'tax_query' => [
                    [
                        'taxonomy' => 'pmp_domain',
                        'field' => 'term_id',
                        'terms' => $domain->term_id
                    ]
                ]
            ]);
            
            // Calculate completion percentage for this domain
            $total_content = count($domain_content);
            $completed_content = 0;
            
            foreach ($domain_content as $content) {
                if (self::is_content_completed($user_id, $content->ID)) {
                    $completed_content++;
                }
            }
            
            $completion_percentage = $total_content > 0 ? ($completed_content / $total_content) * 100 : 0;
            
            // Update domain progress
            self::update_domain_progress($user_id, $domain->slug, $completion_percentage);
        }
    }
    
    /**
     * Track content engagement metrics
     */
    private static function track_content_engagement($user_id, $content_id, $content_type, $time_spent) {
        global $wpdb;
        
        // Insert or update engagement record
        $wpdb->query($wpdb->prepare(
            "INSERT INTO {$wpdb->prefix}pmp_content_engagement 
             (user_id, content_id, content_type, total_time_spent, last_accessed, access_count)
             VALUES (%d, %d, %s, %d, NOW(), 1)
             ON DUPLICATE KEY UPDATE 
             total_time_spent = total_time_spent + VALUES(total_time_spent),
             last_accessed = NOW(),
             access_count = access_count + 1",
            $user_id, $content_id, $content_type, $time_spent
        ));
    }
    
    /**
     * Get comprehensive content progress for user
     */
    public static function get_user_content_progress($user_id) {
        $base_progress = parent::get_user_progress($user_id);
        
        // Add content-specific metrics
        $base_progress['content_stats'] = self::get_content_statistics($user_id);
        $base_progress['learning_paths'] = self::get_learning_path_progress($user_id);
        $base_progress['recent_activity'] = self::get_recent_content_activity($user_id);
        $base_progress['recommendations'] = PMP_Sequence_Manager::get_next_content($user_id, 5);
        
        return $base_progress;
    }
    
    /**
     * Get content statistics for user
     */
    private static function get_content_statistics($user_id) {
        global $wpdb;
        
        $stats = [];
        
        // Content completion by type
        $content_types = ['lesson', 'practice_test', 'resource'];
        
        foreach ($content_types as $type) {
            $total = wp_count_posts($type)->publish ?? 0;
            
            $completed = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(DISTINCT p.lesson_id) 
                 FROM {$wpdb->prefix}pmp_lesson_progress p
                 JOIN {$wpdb->posts} post ON p.lesson_id = post.ID
                 WHERE p.user_id = %d AND p.status = 'completed' 
                 AND post.post_type = %s",
                $user_id, $type
            ));
            
            $stats[$type] = [
                'total' => intval($total),
                'completed' => intval($completed),
                'percentage' => $total > 0 ? round(($completed / $total) * 100, 1) : 0
            ];
        }
        
        return $stats;
    }
    
    /**
     * Get learning path progress for user
     */
    private static function get_learning_path_progress($user_id) {
        global $wpdb;
        
        $paths = $wpdb->get_results(
            "SELECT * FROM {$wpdb->prefix}pmp_learning_paths WHERE is_active = 1"
        );
        
        $progress = [];
        
        foreach ($paths as $path) {
            $user_progress = get_user_meta($user_id, "learning_path_{$path->id}_progress", true) ?: 0;
            
            $progress[] = [
                'id' => $path->id,
                'name' => $path->name,
                'description' => $path->description,
                'progress_percentage' => floatval($user_progress),
                'estimated_hours' => $path->estimated_hours,
                'is_default' => (bool) $path->is_default
            ];
        }
        
        return $progress;
    }
    
    /**
     * Get recent content activity
     */
    private static function get_recent_content_activity($user_id, $limit = 10) {
        global $wpdb;
        
        $recent = $wpdb->get_results($wpdb->prepare(
            "SELECT p.lesson_id, p.status, p.progress_percentage, p.updated_at, post.post_title, post.post_type
             FROM {$wpdb->prefix}pmp_lesson_progress p
             JOIN {$wpdb->posts} post ON p.lesson_id = post.ID
             WHERE p.user_id = %d
             ORDER BY p.updated_at DESC
             LIMIT %d",
            $user_id, $limit
        ));
        
        $activity = [];
        
        foreach ($recent as $item) {
            $activity[] = [
                'content_id' => $item->lesson_id,
                'title' => $item->post_title,
                'type' => $item->post_type,
                'status' => $item->status,
                'progress' => intval($item->progress_percentage),
                'date' => $item->updated_at
            ];
        }
        
        return $activity;
    }
    
    /**
     * Check if content is completed
     */
    private static function is_content_completed($user_id, $content_id) {
        global $wpdb;
        
        return $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}pmp_lesson_progress 
             WHERE user_id = %d AND lesson_id = %d AND status = 'completed'",
            $user_id, $content_id
        )) ? true : false;
    }
    
    /**
     * Update domain progress
     */
    private static function update_domain_progress($user_id, $domain_slug, $completion_percentage) {
        global $wpdb;
        
        // Update or insert domain progress
        $wpdb->query($wpdb->prepare(
            "INSERT INTO {$wpdb->prefix}pmp_domain_progress 
             (user_id, domain, completion_percentage, updated_at)
             VALUES (%d, %s, %f, NOW())
             ON DUPLICATE KEY UPDATE 
             completion_percentage = VALUES(completion_percentage),
             updated_at = NOW()",
            $user_id, $domain_slug, $completion_percentage
        ));
    }
    
    /**
     * Get content bookmarks for user
     */
    public static function get_user_bookmarks($user_id, $bookmark_type = null) {
        global $wpdb;
        
        $sql = "SELECT cb.*, p.post_title, p.post_type 
                FROM {$wpdb->prefix}pmp_content_bookmarks cb
                JOIN {$wpdb->posts} p ON cb.content_id = p.ID
                WHERE cb.user_id = %d";
        
        $params = [$user_id];
        
        if ($bookmark_type) {
            $sql .= " AND cb.bookmark_type = %s";
            $params[] = $bookmark_type;
        }
        
        $sql .= " ORDER BY cb.created_at DESC";
        
        return $wpdb->get_results($wpdb->prepare($sql, $params));
    }
    
    /**
     * Get content engagement analytics
     */
    public static function get_engagement_analytics($user_id, $days = 30) {
        global $wpdb;
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT content_type, COUNT(*) as items_accessed, 
                    SUM(total_time_spent) as total_time,
                    AVG(total_time_spent) as avg_time_per_item
             FROM {$wpdb->prefix}pmp_content_engagement 
             WHERE user_id = %d AND last_accessed >= DATE_SUB(NOW(), INTERVAL %d DAY)
             GROUP BY content_type",
            $user_id, $days
        ));
    }
}
?>
