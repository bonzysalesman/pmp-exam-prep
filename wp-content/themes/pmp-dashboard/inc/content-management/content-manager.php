<?php
/**
 * Content Manager Core Class
 * Task: T004 - Content Manager Core Class
 */

class PMP_Content_Manager {
    
    /**
     * Get content by type with filters
     */
    public static function get_content($type = 'lesson', $args = []) {
        $defaults = [
            'post_type' => $type,
            'post_status' => 'publish',
            'posts_per_page' => 10,
            'orderby' => 'menu_order',
            'order' => 'ASC'
        ];
        
        $args = wp_parse_args($args, $defaults);
        
        // Add taxonomy filters
        if (!empty($args['domain'])) {
            $args['tax_query'][] = [
                'taxonomy' => 'pmp_domain',
                'field' => 'slug',
                'terms' => $args['domain']
            ];
            unset($args['domain']);
        }
        
        if (!empty($args['difficulty'])) {
            $args['tax_query'][] = [
                'taxonomy' => 'difficulty_level',
                'field' => 'slug',
                'terms' => $args['difficulty']
            ];
            unset($args['difficulty']);
        }
        
        return new WP_Query($args);
    }
    
    /**
     * Get content hierarchy for a specific item
     */
    public static function get_content_hierarchy($content_id) {
        global $wpdb;
        
        // Get prerequisites
        $prerequisites = $wpdb->get_results($wpdb->prepare(
            "SELECT p.ID, p.post_title, cs.is_required 
             FROM {$wpdb->prefix}pmp_content_sequences cs
             JOIN {$wpdb->posts} p ON cs.prerequisite_id = p.ID
             WHERE cs.content_id = %d
             ORDER BY cs.sequence_order",
            $content_id
        ));
        
        // Get dependents
        $dependents = $wpdb->get_results($wpdb->prepare(
            "SELECT p.ID, p.post_title, cs.is_required 
             FROM {$wpdb->prefix}pmp_content_sequences cs
             JOIN {$wpdb->posts} p ON cs.content_id = p.ID
             WHERE cs.prerequisite_id = %d
             ORDER BY cs.sequence_order",
            $content_id
        ));
        
        return [
            'prerequisites' => $prerequisites,
            'dependents' => $dependents
        ];
    }
    
    /**
     * Add content to sequence
     */
    public static function add_to_sequence($content_id, $prerequisite_id = null, $order = 0, $required = false) {
        global $wpdb;
        
        return $wpdb->insert(
            $wpdb->prefix . 'pmp_content_sequences',
            [
                'content_id' => $content_id,
                'prerequisite_id' => $prerequisite_id,
                'sequence_order' => $order,
                'is_required' => $required ? 1 : 0
            ],
            ['%d', '%d', '%d', '%d']
        );
    }
    
    /**
     * Get content metadata with caching
     */
    public static function get_content_meta($content_id, $key = null) {
        $cache_key = "pmp_content_meta_{$content_id}";
        $meta = wp_cache_get($cache_key, 'pmp_content');
        
        if (false === $meta) {
            $meta = get_post_meta($content_id);
            wp_cache_set($cache_key, $meta, 'pmp_content', 3600);
        }
        
        return $key ? ($meta[$key][0] ?? null) : $meta;
    }
    
    /**
     * Update content metadata
     */
    public static function update_content_meta($content_id, $key, $value) {
        $result = update_post_meta($content_id, $key, $value);
        
        // Clear cache
        wp_cache_delete("pmp_content_meta_{$content_id}", 'pmp_content');
        
        return $result;
    }
    
    /**
     * Get related content
     */
    public static function get_related_content($content_id, $limit = 5) {
        $post = get_post($content_id);
        if (!$post) return [];
        
        // Get content with same domain/knowledge area
        $terms = wp_get_post_terms($content_id, ['pmp_domain', 'pmp_knowledge_area']);
        if (empty($terms)) return [];
        
        $term_ids = wp_list_pluck($terms, 'term_id');
        
        $args = [
            'post_type' => [$post->post_type, 'lesson', 'practice_test', 'resource'],
            'post_status' => 'publish',
            'posts_per_page' => $limit,
            'post__not_in' => [$content_id],
            'tax_query' => [
                [
                    'taxonomy' => ['pmp_domain', 'pmp_knowledge_area'],
                    'field' => 'term_id',
                    'terms' => $term_ids,
                    'operator' => 'IN'
                ]
            ],
            'orderby' => 'rand'
        ];
        
        return get_posts($args);
    }
    
    /**
     * Check if user can access content
     */
    public static function can_access_content($content_id, $user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        if (!$user_id) return false;
        
        $post = get_post($content_id);
        if (!$post || $post->post_status !== 'publish') return false;
        
        // Check if premium content
        $is_premium = get_post_meta($content_id, '_is_premium', true);
        if ($is_premium) {
            // Check user capabilities or subscription
            return current_user_can('access_premium_content') || 
                   user_can($user_id, 'manage_options');
        }
        
        // Check prerequisites
        return self::check_prerequisites($content_id, $user_id);
    }
    
    /**
     * Check if prerequisites are met
     */
    public static function check_prerequisites($content_id, $user_id) {
        global $wpdb;
        
        $required_prerequisites = $wpdb->get_col($wpdb->prepare(
            "SELECT prerequisite_id 
             FROM {$wpdb->prefix}pmp_content_sequences 
             WHERE content_id = %d AND is_required = 1",
            $content_id
        ));
        
        if (empty($required_prerequisites)) return true;
        
        // Check if user has completed required prerequisites
        foreach ($required_prerequisites as $prereq_id) {
            $completed = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM {$wpdb->prefix}pmp_lesson_progress 
                 WHERE user_id = %d AND lesson_id = %d AND status = 'completed'",
                $user_id, $prereq_id
            ));
            
            if (!$completed) return false;
        }
        
        return true;
    }
    
    /**
     * Get content statistics
     */
    public static function get_content_stats($type = null) {
        $stats = [];
        
        $post_types = $type ? [$type] : ['lesson', 'practice_test', 'resource', 'question'];
        
        foreach ($post_types as $post_type) {
            $count = wp_count_posts($post_type);
            $stats[$post_type] = [
                'published' => $count->publish ?? 0,
                'draft' => $count->draft ?? 0,
                'total' => ($count->publish ?? 0) + ($count->draft ?? 0)
            ];
        }
        
        return $stats;
    }
    
    /**
     * Bulk update content
     */
    public static function bulk_update($content_ids, $data) {
        $updated = 0;
        
        foreach ($content_ids as $content_id) {
            $post_data = ['ID' => $content_id];
            
            // Update post fields
            if (isset($data['post_status'])) {
                $post_data['post_status'] = $data['post_status'];
            }
            
            if (!empty($post_data)) {
                wp_update_post($post_data);
            }
            
            // Update meta fields
            foreach ($data as $key => $value) {
                if (str_starts_with($key, '_')) {
                    update_post_meta($content_id, $key, $value);
                }
            }
            
            // Update taxonomies
            if (isset($data['taxonomies'])) {
                foreach ($data['taxonomies'] as $taxonomy => $terms) {
                    wp_set_post_terms($content_id, $terms, $taxonomy);
                }
            }
            
            $updated++;
        }
        
        return $updated;
    }
}
?>
