<?php
/**
 * Content API Endpoints
 * Task: T007 - Content API Endpoints Structure
 */

class PMP_Content_API extends WP_REST_Controller {
    
    protected $namespace = 'pmp/v1';
    
    public function __construct() {
        add_action('rest_api_init', [$this, 'register_routes']);
    }
    
    /**
     * Register REST API routes
     */
    public function register_routes() {
        // Get content by type
        register_rest_route($this->namespace, '/content/(?P<type>[a-zA-Z0-9_-]+)', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'get_content'],
            'permission_callback' => [$this, 'check_permissions'],
            'args' => [
                'type' => [
                    'required' => true,
                    'validate_callback' => [$this, 'validate_content_type']
                ],
                'page' => [
                    'default' => 1,
                    'sanitize_callback' => 'absint'
                ],
                'per_page' => [
                    'default' => 10,
                    'sanitize_callback' => 'absint'
                ],
                'domain' => [
                    'sanitize_callback' => 'sanitize_text_field'
                ],
                'difficulty' => [
                    'sanitize_callback' => 'sanitize_text_field'
                ]
            ]
        ]);
        
        // Get single content item
        register_rest_route($this->namespace, '/content/(?P<type>[a-zA-Z0-9_-]+)/(?P<id>[\d]+)', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'get_single_content'],
            'permission_callback' => [$this, 'check_permissions'],
            'args' => [
                'type' => [
                    'required' => true,
                    'validate_callback' => [$this, 'validate_content_type']
                ],
                'id' => [
                    'required' => true,
                    'sanitize_callback' => 'absint'
                ]
            ]
        ]);
        
        // Get related content
        register_rest_route($this->namespace, '/content/(?P<id>[\d]+)/related', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'get_related_content'],
            'permission_callback' => [$this, 'check_permissions'],
            'args' => [
                'id' => [
                    'required' => true,
                    'sanitize_callback' => 'absint'
                ],
                'limit' => [
                    'default' => 5,
                    'sanitize_callback' => 'absint'
                ]
            ]
        ]);
        
        // Search content
        register_rest_route($this->namespace, '/search', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'search_content'],
            'permission_callback' => [$this, 'check_permissions'],
            'args' => [
                'q' => [
                    'required' => true,
                    'sanitize_callback' => 'sanitize_text_field'
                ],
                'type' => [
                    'sanitize_callback' => 'sanitize_text_field'
                ],
                'facets' => [
                    'default' => false,
                    'sanitize_callback' => 'rest_sanitize_boolean'
                ]
            ]
        ]);
        
        // Get learning path
        register_rest_route($this->namespace, '/learning-path/(?P<user_id>[\d]+)', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'get_learning_path'],
            'permission_callback' => [$this, 'check_user_permissions'],
            'args' => [
                'user_id' => [
                    'required' => true,
                    'sanitize_callback' => 'absint'
                ],
                'path_id' => [
                    'sanitize_callback' => 'absint'
                ]
            ]
        ]);
        
        // Update content bookmark
        register_rest_route($this->namespace, '/bookmark', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [$this, 'update_bookmark'],
            'permission_callback' => [$this, 'check_authenticated'],
            'args' => [
                'content_id' => [
                    'required' => true,
                    'sanitize_callback' => 'absint'
                ],
                'content_type' => [
                    'required' => true,
                    'sanitize_callback' => 'sanitize_text_field'
                ],
                'bookmark_type' => [
                    'default' => 'favorite',
                    'sanitize_callback' => 'sanitize_text_field'
                ]
            ]
        ]);
    }
    
    /**
     * Get content by type
     */
    public function get_content($request) {
        $type = $request['type'];
        $page = $request['page'];
        $per_page = min($request['per_page'], 50); // Max 50 items
        
        $args = [
            'post_type' => $type,
            'post_status' => 'publish',
            'posts_per_page' => $per_page,
            'paged' => $page,
            'orderby' => 'menu_order',
            'order' => 'ASC'
        ];
        
        // Add filters
        if (!empty($request['domain'])) {
            $args['domain'] = $request['domain'];
        }
        
        if (!empty($request['difficulty'])) {
            $args['difficulty'] = $request['difficulty'];
        }
        
        $query = PMP_Content_Manager::get_content($type, $args);
        
        $content = [];
        foreach ($query->posts as $post) {
            $content[] = $this->prepare_content_item($post);
        }
        
        return rest_ensure_response([
            'content' => $content,
            'pagination' => [
                'page' => $page,
                'per_page' => $per_page,
                'total' => $query->found_posts,
                'total_pages' => $query->max_num_pages
            ]
        ]);
    }
    
    /**
     * Get single content item
     */
    public function get_single_content($request) {
        $post = get_post($request['id']);
        
        if (!$post || $post->post_type !== $request['type']) {
            return new WP_Error('not_found', 'Content not found', ['status' => 404]);
        }
        
        if (!PMP_Content_Manager::can_access_content($post->ID)) {
            return new WP_Error('access_denied', 'Access denied', ['status' => 403]);
        }
        
        $content = $this->prepare_content_item($post, true);
        
        // Add hierarchy information
        $content['hierarchy'] = PMP_Content_Manager::get_content_hierarchy($post->ID);
        
        return rest_ensure_response($content);
    }
    
    /**
     * Get related content
     */
    public function get_related_content($request) {
        $related = PMP_Content_Manager::get_related_content($request['id'], $request['limit']);
        
        $content = [];
        foreach ($related as $post) {
            $content[] = $this->prepare_content_item($post);
        }
        
        return rest_ensure_response(['related' => $content]);
    }
    
    /**
     * Search content
     */
    public function search_content($request) {
        $query = $request['q'];
        $filters = [];
        
        if (!empty($request['type'])) {
            $filters['post_type'] = explode(',', $request['type']);
        }
        
        if ($request['facets']) {
            $results = PMP_Search_Engine::faceted_search($query, $filters);
        } else {
            $search_results = PMP_Search_Engine::search($query, ['filters' => $filters]);
            $results = [
                'posts' => $search_results->posts,
                'total' => $search_results->found_posts
            ];
        }
        
        // Prepare content items
        $content = [];
        foreach ($results['posts'] as $post) {
            $content[] = $this->prepare_content_item($post);
        }
        
        $response = [
            'results' => $content,
            'total' => $results['total']
        ];
        
        if (isset($results['facets'])) {
            $response['facets'] = $results['facets'];
        }
        
        return rest_ensure_response($response);
    }
    
    /**
     * Get learning path for user
     */
    public function get_learning_path($request) {
        $user_id = $request['user_id'];
        $path_id = $request['path_id'] ?? null;
        
        $path = PMP_Sequence_Manager::generate_learning_path($user_id, $path_id);
        
        return rest_ensure_response(['learning_path' => $path]);
    }
    
    /**
     * Update content bookmark
     */
    public function update_bookmark($request) {
        global $wpdb;
        
        $user_id = get_current_user_id();
        $content_id = $request['content_id'];
        $content_type = $request['content_type'];
        $bookmark_type = $request['bookmark_type'];
        
        // Check if bookmark exists
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}pmp_content_bookmarks 
             WHERE user_id = %d AND content_id = %d AND bookmark_type = %s",
            $user_id, $content_id, $bookmark_type
        ));
        
        if ($existing) {
            // Remove bookmark
            $wpdb->delete(
                $wpdb->prefix . 'pmp_content_bookmarks',
                ['id' => $existing],
                ['%d']
            );
            $action = 'removed';
        } else {
            // Add bookmark
            $wpdb->insert(
                $wpdb->prefix . 'pmp_content_bookmarks',
                [
                    'user_id' => $user_id,
                    'content_id' => $content_id,
                    'content_type' => $content_type,
                    'bookmark_type' => $bookmark_type
                ],
                ['%d', '%d', '%s', '%s']
            );
            $action = 'added';
        }
        
        return rest_ensure_response(['action' => $action, 'bookmark_type' => $bookmark_type]);
    }
    
    /**
     * Prepare content item for API response
     */
    private function prepare_content_item($post, $detailed = false) {
        $item = [
            'id' => $post->ID,
            'title' => $post->post_title,
            'excerpt' => $post->post_excerpt,
            'type' => $post->post_type,
            'status' => $post->post_status,
            'date' => $post->post_date,
            'modified' => $post->post_modified,
            'permalink' => get_permalink($post->ID)
        ];
        
        // Add featured image
        if (has_post_thumbnail($post->ID)) {
            $item['featured_image'] = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'medium');
        }
        
        // Add taxonomies
        $item['domains'] = wp_get_post_terms($post->ID, 'pmp_domain', ['fields' => 'names']);
        $item['knowledge_areas'] = wp_get_post_terms($post->ID, 'pmp_knowledge_area', ['fields' => 'names']);
        $item['difficulty'] = wp_get_post_terms($post->ID, 'difficulty_level', ['fields' => 'names']);
        
        // Add metadata
        $item['meta'] = [
            'estimated_minutes' => get_post_meta($post->ID, '_estimated_minutes', true),
            'difficulty_level' => get_post_meta($post->ID, '_difficulty_level', true),
            'is_premium' => get_post_meta($post->ID, '_is_premium', true)
        ];
        
        if ($detailed) {
            $item['content'] = apply_filters('the_content', $post->post_content);
        }
        
        return $item;
    }
    
    /**
     * Validate content type
     */
    public function validate_content_type($param) {
        $allowed_types = ['lesson', 'practice_test', 'resource', 'question'];
        return in_array($param, $allowed_types);
    }
    
    /**
     * Check basic permissions
     */
    public function check_permissions() {
        return true; // Public content access
    }
    
    /**
     * Check user-specific permissions
     */
    public function check_user_permissions($request) {
        $current_user = get_current_user_id();
        $requested_user = $request['user_id'];
        
        // Users can access their own data, admins can access any
        return $current_user == $requested_user || current_user_can('manage_options');
    }
    
    /**
     * Check if user is authenticated
     */
    public function check_authenticated() {
        return is_user_logged_in();
    }
}

// Initialize API
new PMP_Content_API();
?>
