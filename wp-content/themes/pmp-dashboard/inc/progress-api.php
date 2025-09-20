<?php
/**
 * Progress Tracking REST API Endpoints
 * 
 * WordPress REST API endpoints for progress tracking functionality.
 */

if (!defined('ABSPATH')) {
    exit;
}

class PMP_Progress_API {
    
    public function __construct() {
        add_action('rest_api_init', [$this, 'register_routes']);
    }
    
    /**
     * Register REST API routes
     */
    public function register_routes() {
        register_rest_route('pmp/v1', '/progress/(?P<user_id>\d+)', [
            'methods' => 'GET',
            'callback' => [$this, 'get_user_progress'],
            'permission_callback' => [$this, 'check_progress_permissions'],
            'args' => [
                'user_id' => [
                    'required' => true,
                    'validate_callback' => function($param) {
                        return is_numeric($param);
                    }
                ]
            ]
        ]);
        
        register_rest_route('pmp/v1', '/progress/lesson', [
            'methods' => 'POST',
            'callback' => [$this, 'update_lesson_progress'],
            'permission_callback' => [$this, 'check_update_permissions'],
            'args' => [
                'lesson_id' => [
                    'required' => true,
                    'validate_callback' => function($param) {
                        return is_numeric($param);
                    }
                ],
                'status' => [
                    'required' => true,
                    'validate_callback' => function($param) {
                        return in_array($param, ['not_started', 'in_progress', 'completed']);
                    }
                ],
                'progress_percentage' => [
                    'default' => 0,
                    'validate_callback' => function($param) {
                        return is_numeric($param) && $param >= 0 && $param <= 100;
                    }
                ],
                'time_spent_minutes' => [
                    'default' => 0,
                    'validate_callback' => function($param) {
                        return is_numeric($param) && $param >= 0;
                    }
                ]
            ]
        ]);
        
        register_rest_route('pmp/v1', '/analytics/(?P<user_id>\d+)', [
            'methods' => 'GET',
            'callback' => [$this, 'get_user_analytics'],
            'permission_callback' => [$this, 'check_progress_permissions'],
            'args' => [
                'user_id' => [
                    'required' => true,
                    'validate_callback' => function($param) {
                        return is_numeric($param);
                    }
                ],
                'period' => [
                    'default' => 'month',
                    'validate_callback' => function($param) {
                        return in_array($param, ['week', 'month', 'all']);
                    }
                ]
            ]
        ]);
        
        register_rest_route('pmp/v1', '/streak/(?P<user_id>\d+)', [
            'methods' => 'GET',
            'callback' => [$this, 'get_user_streak'],
            'permission_callback' => [$this, 'check_progress_permissions'],
            'args' => [
                'user_id' => [
                    'required' => true,
                    'validate_callback' => function($param) {
                        return is_numeric($param);
                    }
                ]
            ]
        ]);
    }
    
    /**
     * Get user progress summary
     */
    public function get_user_progress($request) {
        $user_id = $request->get_param('user_id');
        
        if (!get_userdata($user_id)) {
            return new WP_Error('user_not_found', 'User not found', ['status' => 404]);
        }
        
        $progress = PMP_Progress_Tracker::get_user_progress($user_id);
        
        return rest_ensure_response($progress);
    }
    
    /**
     * Update lesson progress
     */
    public function update_lesson_progress($request) {
        $lesson_id = $request->get_param('lesson_id');
        $status = $request->get_param('status');
        $progress_percentage = $request->get_param('progress_percentage');
        $time_spent = $request->get_param('time_spent_minutes');
        
        $user_id = get_current_user_id();
        
        $result = PMP_Progress_Tracker::update_lesson_progress(
            $user_id, 
            $lesson_id, 
            $status, 
            $progress_percentage, 
            $time_spent
        );
        
        if (is_wp_error($result)) {
            return $result;
        }
        
        // Get updated progress for the lesson's domain
        $lesson_domain = $result['domain'];
        $updated_progress = PMP_Progress_Tracker::get_user_progress($user_id);
        
        return rest_ensure_response([
            'success' => true,
            'message' => 'Progress updated successfully',
            'updated_progress' => $updated_progress['domains'][$lesson_domain] ?? null
        ]);
    }
    
    /**
     * Get user analytics data
     */
    public function get_user_analytics($request) {
        global $wpdb;
        
        $user_id = $request->get_param('user_id');
        $period = $request->get_param('period');
        
        if (!get_userdata($user_id)) {
            return new WP_Error('user_not_found', 'User not found', ['status' => 404]);
        }
        
        // Calculate date range
        $date_condition = '';
        switch ($period) {
            case 'week':
                $date_condition = "AND session_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
                break;
            case 'month':
                $date_condition = "AND session_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
                break;
            case 'all':
            default:
                $date_condition = '';
                break;
        }
        
        // Get study sessions
        $study_sessions = $wpdb->get_results($wpdb->prepare("
            SELECT session_date, duration_minutes, lessons_completed, domain_focus
            FROM {$wpdb->prefix}pmp_study_sessions 
            WHERE user_id = %d {$date_condition}
            ORDER BY session_date DESC
        ", $user_id), ARRAY_A);
        
        // Get weekly progress
        $weekly_progress = $wpdb->get_results($wpdb->prepare("
            SELECT 
                YEARWEEK(session_date) as week,
                DATE(session_date - INTERVAL WEEKDAY(session_date) DAY) as week_start,
                SUM(lessons_completed) as lessons_completed,
                SUM(duration_minutes) as time_spent
            FROM {$wpdb->prefix}pmp_study_sessions 
            WHERE user_id = %d {$date_condition}
            GROUP BY YEARWEEK(session_date)
            ORDER BY week DESC
        ", $user_id), ARRAY_A);
        
        // Get domain breakdown
        $progress_data = PMP_Progress_Tracker::get_user_progress($user_id);
        $domain_breakdown = [];
        foreach ($progress_data['domains'] as $domain => $data) {
            $domain_breakdown[$domain] = $data['completion_percentage'];
        }
        
        // Generate recommendations
        $recommendations = $this->generate_recommendations($progress_data, $study_sessions);
        
        return rest_ensure_response([
            'study_sessions' => $study_sessions,
            'weekly_progress' => $weekly_progress,
            'domain_breakdown' => $domain_breakdown,
            'recommendations' => $recommendations
        ]);
    }
    
    /**
     * Get user study streak
     */
    public function get_user_streak($request) {
        $user_id = $request->get_param('user_id');
        
        if (!get_userdata($user_id)) {
            return new WP_Error('user_not_found', 'User not found', ['status' => 404]);
        }
        
        $streak_data = PMP_Progress_Tracker::get_study_streak($user_id);
        
        return rest_ensure_response($streak_data);
    }
    
    /**
     * Check permissions for reading progress data
     */
    public function check_progress_permissions($request) {
        if (!is_user_logged_in()) {
            return false;
        }
        
        $user_id = $request->get_param('user_id');
        $current_user_id = get_current_user_id();
        
        // Users can view their own progress, admins can view any
        return $current_user_id == $user_id || current_user_can('manage_options');
    }
    
    /**
     * Check permissions for updating progress
     */
    public function check_update_permissions($request) {
        if (!is_user_logged_in()) {
            return false;
        }
        
        // Verify nonce for security
        $nonce = $request->get_header('X-WP-Nonce');
        if (!$nonce || !wp_verify_nonce($nonce, 'wp_rest')) {
            return new WP_Error('invalid_nonce', 'Invalid security token', ['status' => 403]);
        }
        
        return true;
    }
    
    /**
     * Generate personalized recommendations
     */
    private function generate_recommendations($progress_data, $study_sessions) {
        $recommendations = [];
        
        // Check domain balance
        $domains = $progress_data['domains'];
        $lowest_domain = '';
        $lowest_percentage = 100;
        
        foreach ($domains as $domain => $data) {
            if ($data['completion_percentage'] < $lowest_percentage) {
                $lowest_percentage = $data['completion_percentage'];
                $lowest_domain = $domain;
            }
        }
        
        // Domain-specific recommendations
        if ($lowest_percentage < 25) {
            $domain_name = ucfirst(str_replace('_', ' ', $lowest_domain));
            $recommendations[] = "Focus on {$domain_name} domain - you're at {$lowest_percentage}% completion. This needs immediate attention!";
        } elseif ($lowest_percentage < 50) {
            $domain_name = ucfirst(str_replace('_', ' ', $lowest_domain));
            $recommendations[] = "Consider spending more time on {$domain_name} domain to balance your preparation";
        }
        
        // Study consistency recommendations
        $recent_sessions = count(array_filter($study_sessions, function($session) {
            return strtotime($session['session_date']) >= strtotime('-7 days');
        }));
        
        if ($recent_sessions < 3) {
            $recommendations[] = "Try to study more consistently - aim for at least 4-5 sessions per week";
        } elseif ($recent_sessions >= 6) {
            $recommendations[] = "Excellent consistency! You're studying almost daily. Keep up the great work!";
        }
        
        // Overall progress recommendations
        $overall = $progress_data['overall_progress'];
        if ($overall < 25) {
            $recommendations[] = "You're just getting started! Focus on completing 2-3 lessons per day to build momentum";
        } elseif ($overall < 50) {
            $recommendations[] = "Good progress! Consider taking your first practice test to identify knowledge gaps";
        } elseif ($overall < 75) {
            $recommendations[] = "Great progress! Start incorporating more practice questions into your routine";
        } elseif ($overall < 90) {
            $recommendations[] = "Excellent progress! Focus on practice exams and review weak areas";
        } else {
            $recommendations[] = "Outstanding! You're almost ready. Take full-length practice exams and fine-tune your knowledge";
        }
        
        // Time-based recommendations
        $total_time = array_sum(array_column($study_sessions, 'duration_minutes'));
        $avg_session = $total_time > 0 ? $total_time / count($study_sessions) : 0;
        
        if ($avg_session < 15) {
            $recommendations[] = "Try to extend your study sessions to 20-30 minutes for better retention";
        } elseif ($avg_session > 60) {
            $recommendations[] = "Consider breaking longer sessions into smaller chunks with breaks for better focus";
        }
        
        // Motivational messages
        if (empty($recommendations)) {
            $recommendations[] = "You're doing great! Keep maintaining your current study pace and consistency";
        }
        
        return array_slice($recommendations, 0, 3); // Limit to 3 recommendations
    }
}

// Initialize API
new PMP_Progress_API();
?>
