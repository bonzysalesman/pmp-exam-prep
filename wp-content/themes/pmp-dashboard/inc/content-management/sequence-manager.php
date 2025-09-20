<?php
/**
 * Sequence Manager Class
 * Task: T005 - Sequence Manager Class
 */

class PMP_Sequence_Manager {
    
    /**
     * Generate learning path for user
     */
    public static function generate_learning_path($user_id, $path_id = null) {
        global $wpdb;
        
        if (!$path_id) {
            // Get default learning path
            $path_id = $wpdb->get_var(
                "SELECT id FROM {$wpdb->prefix}pmp_learning_paths WHERE is_default = 1 LIMIT 1"
            );
        }
        
        if (!$path_id) return [];
        
        // Get path content in sequence order
        $content = $wpdb->get_results($wpdb->prepare(
            "SELECT lpc.*, p.post_title, p.post_type, p.post_status
             FROM {$wpdb->prefix}pmp_learning_path_content lpc
             JOIN {$wpdb->posts} p ON lpc.content_id = p.ID
             WHERE lpc.path_id = %d AND p.post_status = 'publish'
             ORDER BY lpc.sequence_order",
            $path_id
        ));
        
        // Add progress information
        foreach ($content as &$item) {
            $item->completed = self::is_content_completed($user_id, $item->content_id);
            $item->accessible = PMP_Content_Manager::can_access_content($item->content_id, $user_id);
            $item->progress_percentage = self::get_content_progress($user_id, $item->content_id);
        }
        
        return $content;
    }
    
    /**
     * Get next recommended content for user
     */
    public static function get_next_content($user_id, $limit = 3) {
        $learning_path = self::generate_learning_path($user_id);
        $recommendations = [];
        
        foreach ($learning_path as $item) {
            if (count($recommendations) >= $limit) break;
            
            if (!$item->completed && $item->accessible) {
                $recommendations[] = [
                    'content_id' => $item->content_id,
                    'title' => $item->post_title,
                    'type' => $item->post_type,
                    'estimated_minutes' => $item->estimated_minutes,
                    'is_required' => $item->is_required,
                    'reason' => self::get_recommendation_reason($item)
                ];
            }
        }
        
        return $recommendations;
    }
    
    /**
     * Validate prerequisites for content
     */
    public static function validate_prerequisites($content_id, $user_id) {
        global $wpdb;
        
        $prerequisites = $wpdb->get_results($wpdb->prepare(
            "SELECT cs.prerequisite_id, cs.is_required, p.post_title
             FROM {$wpdb->prefix}pmp_content_sequences cs
             JOIN {$wpdb->posts} p ON cs.prerequisite_id = p.ID
             WHERE cs.content_id = %d",
            $content_id
        ));
        
        $validation = [
            'valid' => true,
            'missing_required' => [],
            'missing_optional' => []
        ];
        
        foreach ($prerequisites as $prereq) {
            $completed = self::is_content_completed($user_id, $prereq->prerequisite_id);
            
            if (!$completed) {
                if ($prereq->is_required) {
                    $validation['valid'] = false;
                    $validation['missing_required'][] = [
                        'id' => $prereq->prerequisite_id,
                        'title' => $prereq->post_title
                    ];
                } else {
                    $validation['missing_optional'][] = [
                        'id' => $prereq->prerequisite_id,
                        'title' => $prereq->post_title
                    ];
                }
            }
        }
        
        return $validation;
    }
    
    /**
     * Create adaptive sequence based on user performance
     */
    public static function create_adaptive_sequence($user_id, $domain = null) {
        // Get user's weak areas from progress tracking
        $user_progress = PMP_Progress_Tracker::get_user_progress($user_id);
        
        $weak_domains = [];
        foreach ($user_progress['domains'] as $domain_name => $data) {
            if ($data['completion_percentage'] < 50) {
                $weak_domains[] = $domain_name;
            }
        }
        
        // Get content for weak domains
        $args = [
            'post_type' => ['lesson', 'practice_test'],
            'post_status' => 'publish',
            'posts_per_page' => 20,
            'meta_query' => [
                [
                    'key' => '_difficulty_level',
                    'value' => ['easy', 'medium'],
                    'compare' => 'IN'
                ]
            ]
        ];
        
        if (!empty($weak_domains)) {
            $args['tax_query'] = [
                [
                    'taxonomy' => 'pmp_domain',
                    'field' => 'slug',
                    'terms' => $weak_domains,
                    'operator' => 'IN'
                ]
            ];
        }
        
        $content = get_posts($args);
        
        // Sort by priority (lessons first, then tests)
        usort($content, function($a, $b) {
            if ($a->post_type === 'lesson' && $b->post_type !== 'lesson') return -1;
            if ($a->post_type !== 'lesson' && $b->post_type === 'lesson') return 1;
            return 0;
        });
        
        return array_slice($content, 0, 10);
    }
    
    /**
     * Update learning path progress
     */
    public static function update_path_progress($user_id, $path_id, $content_id) {
        global $wpdb;
        
        // Mark content as completed in path
        $wpdb->query($wpdb->prepare(
            "UPDATE {$wpdb->prefix}pmp_learning_path_content 
             SET completed_at = NOW() 
             WHERE path_id = %d AND content_id = %d",
            $path_id, $content_id
        ));
        
        // Calculate overall path progress
        $total_content = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}pmp_learning_path_content WHERE path_id = %d",
            $path_id
        ));
        
        $completed_content = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}pmp_learning_path_content 
             WHERE path_id = %d AND completed_at IS NOT NULL",
            $path_id
        ));
        
        $progress_percentage = $total_content > 0 ? ($completed_content / $total_content) * 100 : 0;
        
        // Store user's path progress
        update_user_meta($user_id, "learning_path_{$path_id}_progress", $progress_percentage);
        
        return $progress_percentage;
    }
    
    /**
     * Get learning path statistics
     */
    public static function get_path_statistics($path_id) {
        global $wpdb;
        
        $stats = [];
        
        // Total content count
        $stats['total_content'] = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}pmp_learning_path_content WHERE path_id = %d",
            $path_id
        ));
        
        // Content by type
        $content_types = $wpdb->get_results($wpdb->prepare(
            "SELECT content_type, COUNT(*) as count 
             FROM {$wpdb->prefix}pmp_learning_path_content 
             WHERE path_id = %d 
             GROUP BY content_type",
            $path_id
        ));
        
        $stats['content_by_type'] = [];
        foreach ($content_types as $type) {
            $stats['content_by_type'][$type->content_type] = $type->count;
        }
        
        // Estimated total time
        $stats['estimated_hours'] = $wpdb->get_var($wpdb->prepare(
            "SELECT SUM(estimated_minutes) / 60 
             FROM {$wpdb->prefix}pmp_learning_path_content 
             WHERE path_id = %d",
            $path_id
        ));
        
        return $stats;
    }
    
    /**
     * Check if content is completed by user
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
     * Get content progress percentage
     */
    private static function get_content_progress($user_id, $content_id) {
        global $wpdb;
        
        $progress = $wpdb->get_var($wpdb->prepare(
            "SELECT progress_percentage FROM {$wpdb->prefix}pmp_lesson_progress 
             WHERE user_id = %d AND lesson_id = %d",
            $user_id, $content_id
        ));
        
        return $progress ? intval($progress) : 0;
    }
    
    /**
     * Get recommendation reason
     */
    private static function get_recommendation_reason($item) {
        if ($item->is_required) {
            return 'Required for curriculum completion';
        }
        
        return 'Next in your learning path';
    }
}
?>
