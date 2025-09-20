<?php
/**
 * Search Engine Foundation
 * Task: T006 - Search Engine Foundation
 */

class PMP_Search_Engine {
    
    /**
     * Perform full-text search across content
     */
    public static function search($query, $args = []) {
        $defaults = [
            'post_type' => ['lesson', 'practice_test', 'resource'],
            'post_status' => 'publish',
            'posts_per_page' => 20,
            'orderby' => 'relevance',
            'filters' => []
        ];
        
        $args = wp_parse_args($args, $defaults);
        
        // Build search query
        $search_args = [
            'post_type' => $args['post_type'],
            'post_status' => $args['post_status'],
            'posts_per_page' => $args['posts_per_page'],
            's' => sanitize_text_field($query)
        ];
        
        // Add taxonomy filters
        if (!empty($args['filters'])) {
            $search_args['tax_query'] = self::build_tax_query($args['filters']);
        }
        
        // Add meta filters
        if (!empty($args['filters']['difficulty'])) {
            $search_args['meta_query'][] = [
                'key' => '_difficulty_level',
                'value' => $args['filters']['difficulty'],
                'compare' => 'IN'
            ];
        }
        
        // Custom ordering for relevance
        if ($args['orderby'] === 'relevance') {
            add_filter('posts_orderby', [__CLASS__, 'search_orderby_relevance']);
        }
        
        $results = new WP_Query($search_args);
        
        // Remove filter
        remove_filter('posts_orderby', [__CLASS__, 'search_orderby_relevance']);
        
        // Add search analytics
        self::track_search($query, $results->found_posts);
        
        return $results;
    }
    
    /**
     * Get search suggestions/autocomplete
     */
    public static function get_suggestions($partial_query, $limit = 5) {
        global $wpdb;
        
        $suggestions = [];
        
        // Search in post titles
        $titles = $wpdb->get_results($wpdb->prepare(
            "SELECT DISTINCT post_title 
             FROM {$wpdb->posts} 
             WHERE post_type IN ('lesson', 'practice_test', 'resource') 
             AND post_status = 'publish'
             AND post_title LIKE %s 
             LIMIT %d",
            '%' . $wpdb->esc_like($partial_query) . '%',
            $limit
        ));
        
        foreach ($titles as $title) {
            $suggestions[] = [
                'text' => $title->post_title,
                'type' => 'title'
            ];
        }
        
        // Search in taxonomy terms
        $terms = $wpdb->get_results($wpdb->prepare(
            "SELECT DISTINCT t.name, tt.taxonomy 
             FROM {$wpdb->terms} t
             JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id
             WHERE tt.taxonomy IN ('pmp_domain', 'pmp_knowledge_area', 'pmp_topic')
             AND t.name LIKE %s 
             LIMIT %d",
            '%' . $wpdb->esc_like($partial_query) . '%',
            $limit
        ));
        
        foreach ($terms as $term) {
            $suggestions[] = [
                'text' => $term->name,
                'type' => 'taxonomy',
                'taxonomy' => $term->taxonomy
            ];
        }
        
        return array_slice($suggestions, 0, $limit);
    }
    
    /**
     * Faceted search with filters
     */
    public static function faceted_search($query = '', $facets = []) {
        $results = [
            'posts' => [],
            'facets' => [],
            'total' => 0
        ];
        
        // Perform search
        $search_results = self::search($query, ['filters' => $facets]);
        $results['posts'] = $search_results->posts;
        $results['total'] = $search_results->found_posts;
        
        // Build facets
        $results['facets'] = self::build_facets($query, $facets);
        
        return $results;
    }
    
    /**
     * Build facets for filtering
     */
    private static function build_facets($query = '', $current_facets = []) {
        $facets = [];
        
        // Domain facets
        $facets['domains'] = self::get_taxonomy_facets('pmp_domain', $query, $current_facets);
        
        // Knowledge area facets
        $facets['knowledge_areas'] = self::get_taxonomy_facets('pmp_knowledge_area', $query, $current_facets);
        
        // Difficulty facets
        $facets['difficulty'] = self::get_meta_facets('_difficulty_level', $query, $current_facets);
        
        // Content type facets
        $facets['content_types'] = self::get_post_type_facets($query, $current_facets);
        
        return $facets;
    }
    
    /**
     * Get taxonomy facets
     */
    private static function get_taxonomy_facets($taxonomy, $query, $current_facets) {
        global $wpdb;
        
        $sql = "SELECT t.term_id, t.name, t.slug, COUNT(p.ID) as count
                FROM {$wpdb->terms} t
                JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id
                JOIN {$wpdb->term_relationships} tr ON tt.term_taxonomy_id = tr.term_taxonomy_id
                JOIN {$wpdb->posts} p ON tr.object_id = p.ID
                WHERE tt.taxonomy = %s
                AND p.post_type IN ('lesson', 'practice_test', 'resource')
                AND p.post_status = 'publish'";
        
        $params = [$taxonomy];
        
        if (!empty($query)) {
            $sql .= " AND (p.post_title LIKE %s OR p.post_content LIKE %s)";
            $params[] = '%' . $wpdb->esc_like($query) . '%';
            $params[] = '%' . $wpdb->esc_like($query) . '%';
        }
        
        $sql .= " GROUP BY t.term_id ORDER BY count DESC, t.name ASC";
        
        return $wpdb->get_results($wpdb->prepare($sql, $params));
    }
    
    /**
     * Get meta field facets
     */
    private static function get_meta_facets($meta_key, $query, $current_facets) {
        global $wpdb;
        
        $sql = "SELECT pm.meta_value as value, COUNT(p.ID) as count
                FROM {$wpdb->postmeta} pm
                JOIN {$wpdb->posts} p ON pm.post_id = p.ID
                WHERE pm.meta_key = %s
                AND p.post_type IN ('lesson', 'practice_test', 'resource')
                AND p.post_status = 'publish'";
        
        $params = [$meta_key];
        
        if (!empty($query)) {
            $sql .= " AND (p.post_title LIKE %s OR p.post_content LIKE %s)";
            $params[] = '%' . $wpdb->esc_like($query) . '%';
            $params[] = '%' . $wpdb->esc_like($query) . '%';
        }
        
        $sql .= " GROUP BY pm.meta_value ORDER BY count DESC";
        
        return $wpdb->get_results($wpdb->prepare($sql, $params));
    }
    
    /**
     * Get post type facets
     */
    private static function get_post_type_facets($query, $current_facets) {
        global $wpdb;
        
        $sql = "SELECT p.post_type, COUNT(p.ID) as count
                FROM {$wpdb->posts} p
                WHERE p.post_type IN ('lesson', 'practice_test', 'resource')
                AND p.post_status = 'publish'";
        
        $params = [];
        
        if (!empty($query)) {
            $sql .= " AND (p.post_title LIKE %s OR p.post_content LIKE %s)";
            $params[] = '%' . $wpdb->esc_like($query) . '%';
            $params[] = '%' . $wpdb->esc_like($query) . '%';
        }
        
        $sql .= " GROUP BY p.post_type ORDER BY count DESC";
        
        return $wpdb->get_results($wpdb->prepare($sql, $params));
    }
    
    /**
     * Build taxonomy query from filters
     */
    private static function build_tax_query($filters) {
        $tax_query = ['relation' => 'AND'];
        
        if (!empty($filters['domain'])) {
            $tax_query[] = [
                'taxonomy' => 'pmp_domain',
                'field' => 'slug',
                'terms' => (array) $filters['domain']
            ];
        }
        
        if (!empty($filters['knowledge_area'])) {
            $tax_query[] = [
                'taxonomy' => 'pmp_knowledge_area',
                'field' => 'slug',
                'terms' => (array) $filters['knowledge_area']
            ];
        }
        
        if (!empty($filters['topic'])) {
            $tax_query[] = [
                'taxonomy' => 'pmp_topic',
                'field' => 'slug',
                'terms' => (array) $filters['topic']
            ];
        }
        
        return count($tax_query) > 1 ? $tax_query : [];
    }
    
    /**
     * Custom orderby for search relevance
     */
    public static function search_orderby_relevance($orderby) {
        global $wpdb;
        
        return "
            CASE 
                WHEN {$wpdb->posts}.post_title LIKE '%{$_GET['s']}%' THEN 1
                WHEN {$wpdb->posts}.post_content LIKE '%{$_GET['s']}%' THEN 2
                ELSE 3
            END ASC,
            {$wpdb->posts}.post_date DESC
        ";
    }
    
    /**
     * Track search analytics
     */
    private static function track_search($query, $results_count) {
        global $wpdb;
        
        // Simple search tracking - could be expanded
        $wpdb->insert(
            $wpdb->prefix . 'pmp_search_analytics',
            [
                'search_query' => sanitize_text_field($query),
                'results_count' => intval($results_count),
                'user_id' => get_current_user_id(),
                'search_date' => current_time('mysql')
            ],
            ['%s', '%d', '%d', '%s']
        );
    }
    
    /**
     * Get popular searches
     */
    public static function get_popular_searches($limit = 10) {
        global $wpdb;
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT search_query, COUNT(*) as search_count
             FROM {$wpdb->prefix}pmp_search_analytics
             WHERE search_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
             GROUP BY search_query
             ORDER BY search_count DESC
             LIMIT %d",
            $limit
        ));
    }
}
?>
