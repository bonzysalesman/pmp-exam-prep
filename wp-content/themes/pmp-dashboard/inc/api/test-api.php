<?php
/**
 * Practice Test API
 * Task: T022 - Practice Test API
 */

class PMP_Test_API extends WP_REST_Controller {
    
    protected $namespace = 'pmp/v1';
    
    public function __construct() {
        add_action('rest_api_init', [$this, 'register_routes']);
    }
    
    /**
     * Register REST API routes
     */
    public function register_routes() {
        
        // Start test session
        register_rest_route($this->namespace, '/test/(?P<test_id>[\d]+)/start', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [$this, 'start_test'],
            'permission_callback' => [$this, 'check_authenticated'],
            'args' => [
                'test_id' => [
                    'required' => true,
                    'sanitize_callback' => 'absint'
                ],
                'options' => [
                    'default' => [],
                    'sanitize_callback' => [$this, 'sanitize_test_options']
                ]
            ]
        ]);
        
        // Submit answer
        register_rest_route($this->namespace, '/test/answer', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [$this, 'submit_answer'],
            'permission_callback' => [$this, 'check_authenticated'],
            'args' => [
                'session_id' => [
                    'required' => true,
                    'sanitize_callback' => 'absint'
                ],
                'question_id' => [
                    'required' => true,
                    'sanitize_callback' => 'absint'
                ],
                'answer' => [
                    'required' => true,
                    'sanitize_callback' => 'sanitize_text_field'
                ],
                'time_spent' => [
                    'default' => 0,
                    'sanitize_callback' => 'absint'
                ]
            ]
        ]);
        
        // Complete test
        register_rest_route($this->namespace, '/test/complete', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [$this, 'complete_test'],
            'permission_callback' => [$this, 'check_authenticated'],
            'args' => [
                'session_id' => [
                    'required' => true,
                    'sanitize_callback' => 'absint'
                ]
            ]
        ]);
        
        // Get test session status
        register_rest_route($this->namespace, '/test/session/(?P<session_id>[\d]+)', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'get_session_status'],
            'permission_callback' => [$this, 'check_session_owner'],
            'args' => [
                'session_id' => [
                    'required' => true,
                    'sanitize_callback' => 'absint'
                ]
            ]
        ]);
        
        // Get test results
        register_rest_route($this->namespace, '/test/results/(?P<session_id>[\d]+)', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'get_test_results'],
            'permission_callback' => [$this, 'check_session_owner'],
            'args' => [
                'session_id' => [
                    'required' => true,
                    'sanitize_callback' => 'absint'
                ]
            ]
        ]);
        
        // Get user test history
        register_rest_route($this->namespace, '/test/history/(?P<user_id>[\d]+)', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'get_test_history'],
            'permission_callback' => [$this, 'check_user_permissions'],
            'args' => [
                'user_id' => [
                    'required' => true,
                    'sanitize_callback' => 'absint'
                ],
                'test_id' => [
                    'sanitize_callback' => 'absint'
                ],
                'limit' => [
                    'default' => 10,
                    'sanitize_callback' => 'absint'
                ]
            ]
        ]);
        
        // Get test analytics
        register_rest_route($this->namespace, '/test/analytics/(?P<test_id>[\d]+)', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'get_test_analytics'],
            'permission_callback' => [$this, 'check_admin_permissions'],
            'args' => [
                'test_id' => [
                    'required' => true,
                    'sanitize_callback' => 'absint'
                ]
            ]
        ]);
        
        // Get available tests
        register_rest_route($this->namespace, '/tests', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'get_tests'],
            'permission_callback' => [$this, 'check_authenticated'],
            'args' => [
                'difficulty' => [
                    'sanitize_callback' => 'sanitize_text_field'
                ],
                'domain' => [
                    'sanitize_callback' => 'sanitize_text_field'
                ],
                'per_page' => [
                    'default' => 10,
                    'sanitize_callback' => 'absint'
                ]
            ]
        ]);
        
        // Get question bank statistics
        register_rest_route($this->namespace, '/test/question-stats', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'get_question_stats'],
            'permission_callback' => [$this, 'check_admin_permissions']
        ]);
    }
    
    /**
     * Start a new test session
     */
    public function start_test($request) {
        $test_id = $request['test_id'];
        $options = $request['options'];
        $user_id = get_current_user_id();
        
        // Validate test exists and is published
        $test = get_post($test_id);
        if (!$test || $test->post_type !== 'practice_test' || $test->post_status !== 'publish') {
            return new WP_Error('invalid_test', 'Test not found or not available', ['status' => 404]);
        }
        
        // Check if user can access this test
        if (!PMP_Content_Manager::can_access_content($test_id, $user_id)) {
            return new WP_Error('access_denied', 'You do not have access to this test', ['status' => 403]);
        }
        
        // Generate test session
        $test_data = PMP_Test_Engine::generate_test($test_id, $user_id, $options);
        
        if (is_wp_error($test_data)) {
            return $test_data;
        }
        
        return rest_ensure_response([
            'success' => true,
            'session_id' => $test_data['session_id'],
            'question_count' => $test_data['question_count'],
            'time_limit' => $test_data['time_limit'],
            'test_url' => add_query_arg(['session' => $test_data['session_id']], get_permalink($test_id))
        ]);
    }
    
    /**
     * Submit an answer for a question
     */
    public function submit_answer($request) {
        $session_id = $request['session_id'];
        $question_id = $request['question_id'];
        $answer = $request['answer'];
        $time_spent = $request['time_spent'];
        
        // Validate session ownership
        if (!$this->validate_session_owner($session_id)) {
            return new WP_Error('access_denied', 'Invalid session access', ['status' => 403]);
        }
        
        $result = PMP_Test_Engine::submit_answer($session_id, $question_id, $answer, $time_spent);
        
        if (is_wp_error($result)) {
            return $result;
        }
        
        return rest_ensure_response([
            'success' => true,
            'message' => 'Answer saved successfully',
            'timestamp' => current_time('mysql')
        ]);
    }
    
    /**
     * Complete a test session
     */
    public function complete_test($request) {
        $session_id = $request['session_id'];
        
        // Validate session ownership
        if (!$this->validate_session_owner($session_id)) {
            return new WP_Error('access_denied', 'Invalid session access', ['status' => 403]);
        }
        
        $results = PMP_Test_Engine::complete_test($session_id);
        
        if (is_wp_error($results)) {
            return $results;
        }
        
        return rest_ensure_response([
            'success' => true,
            'results' => $results,
            'results_url' => add_query_arg(['results' => $session_id], get_permalink())
        ]);
    }
    
    /**
     * Get test session status
     */
    public function get_session_status($request) {
        $session_id = $request['session_id'];
        
        $status = PMP_Test_Engine::get_session_status($session_id);
        
        if (!$status) {
            return new WP_Error('session_not_found', 'Test session not found', ['status' => 404]);
        }
        
        return rest_ensure_response($status);
    }
    
    /**
     * Get test results with analysis
     */
    public function get_test_results($request) {
        $session_id = $request['session_id'];
        
        $analysis = PMP_Results_Analyzer::analyze_results($session_id);
        
        if (is_wp_error($analysis)) {
            return $analysis;
        }
        
        return rest_ensure_response([
            'success' => true,
            'analysis' => $analysis
        ]);
    }
    
    /**
     * Get user test history
     */
    public function get_test_history($request) {
        $user_id = $request['user_id'];
        $test_id = $request['test_id'] ?? null;
        $limit = $request['limit'];
        
        $history = PMP_Test_Engine::get_user_test_history($user_id, $test_id, $limit);
        
        // Add additional data
        foreach ($history as &$attempt) {
            $attempt->test_url = get_permalink($attempt->test_id);
            $attempt->results_url = add_query_arg(['results' => $attempt->id], get_permalink());
            $attempt->formatted_date = human_time_diff(strtotime($attempt->created_at)) . ' ago';
            $attempt->passed = floatval($attempt->score) >= 70;
        }
        
        return rest_ensure_response([
            'success' => true,
            'history' => $history,
            'total_attempts' => count($history)
        ]);
    }
    
    /**
     * Get test analytics for administrators
     */
    public function get_test_analytics($request) {
        $test_id = $request['test_id'];
        
        $stats = PMP_Test_Engine::get_test_statistics($test_id);
        $question_stats = PMP_Question_Bank::get_question_statistics();
        
        return rest_ensure_response([
            'success' => true,
            'test_statistics' => $stats,
            'question_statistics' => $question_stats,
            'test_info' => [
                'id' => $test_id,
                'title' => get_the_title($test_id),
                'url' => get_permalink($test_id)
            ]
        ]);
    }
    
    /**
     * Get available tests
     */
    public function get_tests($request) {
        $args = [
            'post_type' => 'practice_test',
            'post_status' => 'publish',
            'posts_per_page' => $request['per_page'],
            'orderby' => 'menu_order',
            'order' => 'ASC'
        ];
        
        // Add filters
        if (!empty($request['difficulty'])) {
            $args['meta_query'][] = [
                'key' => '_difficulty_level',
                'value' => $request['difficulty'],
                'compare' => '='
            ];
        }
        
        if (!empty($request['domain'])) {
            $args['tax_query'][] = [
                'taxonomy' => 'pmp_domain',
                'field' => 'slug',
                'terms' => $request['domain']
            ];
        }
        
        $tests = get_posts($args);
        $user_id = get_current_user_id();
        
        $formatted_tests = [];
        
        foreach ($tests as $test) {
            // Get test metadata
            $question_count = get_post_meta($test->ID, '_question_count', true) ?: 50;
            $time_limit = get_post_meta($test->ID, '_time_limit', true) ?: 180;
            $difficulty = get_post_meta($test->ID, '_difficulty_level', true) ?: 'medium';
            $passing_score = get_post_meta($test->ID, '_passing_score', true) ?: 70;
            
            // Get user's best score for this test
            global $wpdb;
            $best_score = $wpdb->get_var($wpdb->prepare(
                "SELECT MAX(score) FROM {$wpdb->prefix}pmp_test_attempts 
                 WHERE user_id = %d AND test_id = %d AND completed_at IS NOT NULL",
                $user_id, $test->ID
            ));
            
            $attempt_count = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}pmp_test_attempts 
                 WHERE user_id = %d AND test_id = %d AND completed_at IS NOT NULL",
                $user_id, $test->ID
            ));
            
            $formatted_tests[] = [
                'id' => $test->ID,
                'title' => $test->post_title,
                'excerpt' => $test->post_excerpt,
                'url' => get_permalink($test->ID),
                'question_count' => intval($question_count),
                'time_limit' => intval($time_limit),
                'difficulty' => $difficulty,
                'passing_score' => intval($passing_score),
                'user_stats' => [
                    'best_score' => $best_score ? floatval($best_score) : null,
                    'attempt_count' => intval($attempt_count),
                    'passed' => $best_score ? floatval($best_score) >= $passing_score : false
                ],
                'domains' => wp_get_post_terms($test->ID, 'pmp_domain', ['fields' => 'names']),
                'can_access' => PMP_Content_Manager::can_access_content($test->ID, $user_id)
            ];
        }
        
        return rest_ensure_response([
            'success' => true,
            'tests' => $formatted_tests,
            'total' => count($formatted_tests)
        ]);
    }
    
    /**
     * Get question bank statistics
     */
    public function get_question_stats($request) {
        $stats = PMP_Question_Bank::get_question_statistics();
        
        return rest_ensure_response([
            'success' => true,
            'statistics' => $stats
        ]);
    }
    
    /**
     * Sanitize test options
     */
    public function sanitize_test_options($options) {
        $sanitized = [];
        
        if (isset($options['question_count'])) {
            $sanitized['question_count'] = absint($options['question_count']);
        }
        
        if (isset($options['time_limit'])) {
            $sanitized['time_limit'] = absint($options['time_limit']);
        }
        
        if (isset($options['randomize'])) {
            $sanitized['randomize'] = rest_sanitize_boolean($options['randomize']);
        }
        
        if (isset($options['domain_distribution'])) {
            $sanitized['domain_distribution'] = rest_sanitize_boolean($options['domain_distribution']);
        }
        
        return $sanitized;
    }
    
    /**
     * Validate session ownership
     */
    private function validate_session_owner($session_id) {
        global $wpdb;
        
        $session = $wpdb->get_row($wpdb->prepare(
            "SELECT user_id FROM {$wpdb->prefix}pmp_test_attempts WHERE id = %d",
            $session_id
        ));
        
        return $session && $session->user_id == get_current_user_id();
    }
    
    /**
     * Check if user is authenticated
     */
    public function check_authenticated() {
        return is_user_logged_in();
    }
    
    /**
     * Check session owner permissions
     */
    public function check_session_owner($request) {
        if (!is_user_logged_in()) {
            return false;
        }
        
        $session_id = $request['session_id'];
        return $this->validate_session_owner($session_id) || current_user_can('manage_options');
    }
    
    /**
     * Check user permissions
     */
    public function check_user_permissions($request) {
        $current_user = get_current_user_id();
        $requested_user = $request['user_id'];
        
        return $current_user == $requested_user || current_user_can('manage_options');
    }
    
    /**
     * Check admin permissions
     */
    public function check_admin_permissions() {
        return current_user_can('manage_options');
    }
}

// Initialize API
new PMP_Test_API();
?>
