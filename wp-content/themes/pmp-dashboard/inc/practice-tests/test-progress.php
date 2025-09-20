<?php
/**
 * Test Progress Integration
 * Task: T024 - Test Progress Integration
 */

class PMP_Test_Progress {
    
    public static function init() {
        add_action('pmp_test_completed', [__CLASS__, 'update_progress_on_completion'], 10, 3);
        add_action('wp_ajax_get_test_progress', [__CLASS__, 'ajax_get_test_progress']);
        add_filter('pmp_dashboard_widgets', [__CLASS__, 'add_test_progress_widgets']);
        add_shortcode('pmp_test_progress', [__CLASS__, 'test_progress_shortcode']);
    }
    
    /**
     * Update progress when test is completed
     */
    public static function update_progress_on_completion($user_id, $test_id, $results) {
        // Update lesson progress for the test
        PMP_Content_Progress::update_content_progress(
            $user_id,
            $test_id,
            'practice_test',
            'completed',
            100,
            $results['time_spent']
        );
        
        // Update domain progress based on test results
        self::update_domain_progress_from_test($user_id, $results);
        
        // Update study streak if test was passed
        if ($results['passed']) {
            self::update_study_streak($user_id);
        }
        
        // Generate recommendations based on results
        self::generate_study_recommendations($user_id, $results);
        
        // Update overall PMP readiness score
        self::update_pmp_readiness($user_id);
    }
    
    /**
     * Update domain progress based on test results
     */
    private static function update_domain_progress_from_test($user_id, $results) {
        global $wpdb;
        
        $domain_results = $results['domain_results'] ?? [];
        
        foreach ($domain_results as $domain => $data) {
            // Get current domain progress
            $current_progress = $wpdb->get_var($wpdb->prepare(
                "SELECT completion_percentage FROM {$wpdb->prefix}pmp_domain_progress 
                 WHERE user_id = %d AND domain = %s",
                $user_id, $domain
            ));
            
            // Calculate weighted progress (tests count more than lessons)
            $test_weight = 0.7; // Tests are 70% of domain progress
            $lesson_weight = 0.3; // Lessons are 30% of domain progress
            
            $test_score = $data['percentage'];
            $lesson_progress = $current_progress ? floatval($current_progress) : 0;
            
            $new_progress = ($test_score * $test_weight) + ($lesson_progress * $lesson_weight);
            
            // Update domain progress
            $wpdb->query($wpdb->prepare(
                "INSERT INTO {$wpdb->prefix}pmp_domain_progress 
                 (user_id, domain, completion_percentage, updated_at)
                 VALUES (%d, %s, %f, NOW())
                 ON DUPLICATE KEY UPDATE 
                 completion_percentage = %f, updated_at = NOW()",
                $user_id, $domain, $new_progress, $new_progress
            ));
        }
    }
    
    /**
     * Update study streak
     */
    private static function update_study_streak($user_id) {
        $streak_data = PMP_Progress_Tracker::get_study_streak($user_id);
        
        // Add test completion to study session
        global $wpdb;
        
        $today = date('Y-m-d');
        
        // Check if there's already a session today
        $existing_session = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}pmp_study_sessions 
             WHERE user_id = %d AND session_date = %s",
            $user_id, $today
        ));
        
        if ($existing_session) {
            // Update existing session
            $wpdb->query($wpdb->prepare(
                "UPDATE {$wpdb->prefix}pmp_study_sessions 
                 SET tests_completed = tests_completed + 1,
                     updated_at = NOW()
                 WHERE id = %d",
                $existing_session
            ));
        } else {
            // Create new session
            $wpdb->insert(
                $wpdb->prefix . 'pmp_study_sessions',
                [
                    'user_id' => $user_id,
                    'session_date' => $today,
                    'duration_minutes' => 0,
                    'lessons_completed' => 0,
                    'tests_completed' => 1,
                    'domain_focus' => 'mixed'
                ]
            );
        }
    }
    
    /**
     * Generate study recommendations based on test results
     */
    private static function generate_study_recommendations($user_id, $results) {
        $recommendations = [];
        $domain_results = $results['domain_results'] ?? [];
        
        // Identify weak areas
        foreach ($domain_results as $domain => $data) {
            if ($data['percentage'] < 70) {
                $recommendations[] = [
                    'type' => 'weak_domain',
                    'domain' => $domain,
                    'priority' => 'high',
                    'message' => "Focus on {$domain} domain - scored {$data['percentage']}%",
                    'action' => 'study_domain_lessons',
                    'created_at' => current_time('mysql')
                ];
            }
        }
        
        // Overall performance recommendations
        $overall_score = $results['score'] ?? 0;
        
        if ($overall_score < 70) {
            $recommendations[] = [
                'type' => 'overall_improvement',
                'priority' => 'high',
                'message' => 'Take more practice tests to improve overall score',
                'action' => 'more_practice_tests',
                'created_at' => current_time('mysql')
            ];
        } elseif ($overall_score < 85) {
            $recommendations[] = [
                'type' => 'exam_readiness',
                'priority' => 'medium',
                'message' => 'Good progress! Continue with regular practice tests',
                'action' => 'maintain_practice',
                'created_at' => current_time('mysql')
            ];
        }
        
        // Store recommendations
        update_user_meta($user_id, 'pmp_study_recommendations', $recommendations);
    }
    
    /**
     * Update PMP readiness score
     */
    private static function update_pmp_readiness($user_id) {
        global $wpdb;
        
        // Get domain progress
        $domain_progress = $wpdb->get_results($wpdb->prepare(
            "SELECT domain, completion_percentage FROM {$wpdb->prefix}pmp_domain_progress 
             WHERE user_id = %d",
            $user_id
        ));
        
        // Calculate weighted readiness score
        $domain_weights = [
            'people' => 0.42,
            'process' => 0.50,
            'business_environment' => 0.08
        ];
        
        $weighted_score = 0;
        $total_weight = 0;
        
        foreach ($domain_progress as $progress) {
            $weight = $domain_weights[$progress->domain] ?? 0;
            $weighted_score += $progress->completion_percentage * $weight;
            $total_weight += $weight;
        }
        
        $readiness_score = $total_weight > 0 ? ($weighted_score / $total_weight) : 0;
        
        // Get recent test performance
        $recent_tests = $wpdb->get_results($wpdb->prepare(
            "SELECT score FROM {$wpdb->prefix}pmp_test_attempts 
             WHERE user_id = %d AND completed_at IS NOT NULL 
             ORDER BY completed_at DESC LIMIT 5",
            $user_id
        ));
        
        if (!empty($recent_tests)) {
            $avg_test_score = array_sum(array_column($recent_tests, 'score')) / count($recent_tests);
            // Weight: 60% domain progress, 40% test performance
            $readiness_score = ($readiness_score * 0.6) + ($avg_test_score * 0.4);
        }
        
        // Determine readiness level
        $readiness_level = 'not_ready';
        if ($readiness_score >= 85) {
            $readiness_level = 'exam_ready';
        } elseif ($readiness_score >= 70) {
            $readiness_level = 'almost_ready';
        } elseif ($readiness_score >= 50) {
            $readiness_level = 'progressing';
        }
        
        // Store readiness data
        update_user_meta($user_id, 'pmp_readiness_score', round($readiness_score, 1));
        update_user_meta($user_id, 'pmp_readiness_level', $readiness_level);
        update_user_meta($user_id, 'pmp_readiness_updated', current_time('mysql'));
    }
    
    /**
     * Get comprehensive test progress for user
     */
    public static function get_user_test_progress($user_id) {
        global $wpdb;
        
        // Get test statistics
        $test_stats = [
            'total_attempts' => $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}pmp_test_attempts WHERE user_id = %d",
                $user_id
            )),
            'completed_tests' => $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}pmp_test_attempts 
                 WHERE user_id = %d AND completed_at IS NOT NULL",
                $user_id
            )),
            'passed_tests' => $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}pmp_test_attempts 
                 WHERE user_id = %d AND completed_at IS NOT NULL AND score >= 70",
                $user_id
            )),
            'average_score' => $wpdb->get_var($wpdb->prepare(
                "SELECT AVG(score) FROM {$wpdb->prefix}pmp_test_attempts 
                 WHERE user_id = %d AND completed_at IS NOT NULL",
                $user_id
            )),
            'best_score' => $wpdb->get_var($wpdb->prepare(
                "SELECT MAX(score) FROM {$wpdb->prefix}pmp_test_attempts 
                 WHERE user_id = %d AND completed_at IS NOT NULL",
                $user_id
            ))
        ];
        
        // Get recent performance trend
        $recent_scores = $wpdb->get_col($wpdb->prepare(
            "SELECT score FROM {$wpdb->prefix}pmp_test_attempts 
             WHERE user_id = %d AND completed_at IS NOT NULL 
             ORDER BY completed_at DESC LIMIT 10",
            $user_id
        ));
        
        // Get domain performance
        $domain_performance = $wpdb->get_results($wpdb->prepare(
            "SELECT domain, completion_percentage FROM {$wpdb->prefix}pmp_domain_progress 
             WHERE user_id = %d",
            $user_id
        ));
        
        // Get readiness data
        $readiness_score = get_user_meta($user_id, 'pmp_readiness_score', true) ?: 0;
        $readiness_level = get_user_meta($user_id, 'pmp_readiness_level', true) ?: 'not_ready';
        
        // Get recommendations
        $recommendations = get_user_meta($user_id, 'pmp_study_recommendations', true) ?: [];
        
        return [
            'test_statistics' => $test_stats,
            'recent_scores' => array_reverse($recent_scores), // Chronological order
            'domain_performance' => $domain_performance,
            'readiness' => [
                'score' => floatval($readiness_score),
                'level' => $readiness_level,
                'message' => self::get_readiness_message($readiness_level, $readiness_score)
            ],
            'recommendations' => array_slice($recommendations, 0, 5), // Top 5 recommendations
            'next_steps' => self::get_next_steps($readiness_level, $test_stats)
        ];
    }
    
    /**
     * AJAX handler for test progress
     */
    public static function ajax_get_test_progress() {
        check_ajax_referer('pmp_progress_nonce', 'nonce');
        
        $user_id = get_current_user_id();
        if (!$user_id) {
            wp_send_json_error('User not logged in');
        }
        
        $progress = self::get_user_test_progress($user_id);
        wp_send_json_success($progress);
    }
    
    /**
     * Add test progress widgets to dashboard
     */
    public static function add_test_progress_widgets($widgets) {
        $widgets['test_progress'] = [
            'title' => 'Test Progress',
            'callback' => [__CLASS__, 'render_test_progress_widget'],
            'priority' => 20
        ];
        
        $widgets['exam_readiness'] = [
            'title' => 'Exam Readiness',
            'callback' => [__CLASS__, 'render_readiness_widget'],
            'priority' => 15
        ];
        
        return $widgets;
    }
    
    /**
     * Render test progress widget
     */
    public static function render_test_progress_widget($user_id) {
        $progress = self::get_user_test_progress($user_id);
        $stats = $progress['test_statistics'];
        
        ?>
        <div class="test-progress-widget">
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div class="text-center">
                    <div class="text-2xl font-bold text-blue-600"><?php echo $stats['completed_tests']; ?></div>
                    <div class="text-sm text-gray-600">Tests Completed</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-green-600"><?php echo $stats['passed_tests']; ?></div>
                    <div class="text-sm text-gray-600">Tests Passed</div>
                </div>
            </div>
            
            <?php if ($stats['average_score']): ?>
                <div class="mb-4">
                    <div class="flex justify-between text-sm mb-1">
                        <span>Average Score</span>
                        <span class="font-medium"><?php echo round($stats['average_score'], 1); ?>%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-blue-500 h-2 rounded-full" style="width: <?php echo min($stats['average_score'], 100); ?>%"></div>
                    </div>
                </div>
            <?php endif; ?>
            
            <div class="text-center">
                <a href="<?php echo get_post_type_archive_link('practice_test'); ?>" 
                   class="inline-flex items-center px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition-colors">
                    <i class="fas fa-play mr-2"></i>Take Practice Test
                </a>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render exam readiness widget
     */
    public static function render_readiness_widget($user_id) {
        $progress = self::get_user_test_progress($user_id);
        $readiness = $progress['readiness'];
        
        $colors = [
            'not_ready' => 'text-red-600',
            'progressing' => 'text-yellow-600',
            'almost_ready' => 'text-blue-600',
            'exam_ready' => 'text-green-600'
        ];
        
        ?>
        <div class="exam-readiness-widget text-center">
            <div class="mb-4">
                <div class="text-3xl font-bold <?php echo $colors[$readiness['level']] ?? 'text-gray-600'; ?>">
                    <?php echo $readiness['score']; ?>%
                </div>
                <div class="text-sm text-gray-600">Exam Readiness</div>
            </div>
            
            <div class="mb-4">
                <div class="w-full bg-gray-200 rounded-full h-3">
                    <div class="h-3 rounded-full transition-all duration-500 
                        <?php echo $readiness['level'] === 'exam_ready' ? 'bg-green-500' : 
                                  ($readiness['level'] === 'almost_ready' ? 'bg-blue-500' : 
                                  ($readiness['level'] === 'progressing' ? 'bg-yellow-500' : 'bg-red-500')); ?>" 
                         style="width: <?php echo min($readiness['score'], 100); ?>%"></div>
                </div>
            </div>
            
            <p class="text-sm text-gray-700 mb-4"><?php echo $readiness['message']; ?></p>
            
            <?php if (!empty($progress['next_steps'])): ?>
                <div class="space-y-2">
                    <?php foreach (array_slice($progress['next_steps'], 0, 2) as $step): ?>
                        <a href="<?php echo $step['url']; ?>" 
                           class="block px-3 py-2 text-sm bg-gray-100 hover:bg-gray-200 rounded transition-colors">
                            <?php echo $step['title']; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }
    
    /**
     * Test progress shortcode
     */
    public static function test_progress_shortcode($atts) {
        if (!is_user_logged_in()) {
            return '<p>Please log in to view your test progress.</p>';
        }
        
        $user_id = get_current_user_id();
        $progress = self::get_user_test_progress($user_id);
        
        ob_start();
        ?>
        <div class="pmp-test-progress">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white p-6 rounded-lg shadow-sm">
                    <?php self::render_test_progress_widget($user_id); ?>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-sm">
                    <?php self::render_readiness_widget($user_id); ?>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Helper methods
     */
    private static function get_readiness_message($level, $score) {
        $messages = [
            'not_ready' => 'Keep studying! Focus on weak areas and take more practice tests.',
            'progressing' => 'Good progress! Continue with regular study sessions and practice tests.',
            'almost_ready' => 'You\'re almost there! A few more practice tests should get you ready.',
            'exam_ready' => 'Congratulations! You\'re ready to take the PMP exam.'
        ];
        
        return $messages[$level] ?? 'Continue your PMP preparation journey.';
    }
    
    private static function get_next_steps($level, $stats) {
        $steps = [];
        
        switch ($level) {
            case 'not_ready':
                $steps[] = [
                    'title' => 'Study Weak Domains',
                    'url' => get_post_type_archive_link('lesson')
                ];
                $steps[] = [
                    'title' => 'Take Practice Tests',
                    'url' => get_post_type_archive_link('practice_test')
                ];
                break;
                
            case 'progressing':
                $steps[] = [
                    'title' => 'Continue Practice Tests',
                    'url' => get_post_type_archive_link('practice_test')
                ];
                $steps[] = [
                    'title' => 'Review Study Materials',
                    'url' => get_post_type_archive_link('resource')
                ];
                break;
                
            case 'almost_ready':
            case 'exam_ready':
                $steps[] = [
                    'title' => 'Final Practice Tests',
                    'url' => get_post_type_archive_link('practice_test')
                ];
                $steps[] = [
                    'title' => 'Schedule Your Exam',
                    'url' => 'https://www.pmi.org/certifications/project-management-pmp'
                ];
                break;
        }
        
        return $steps;
    }
}

// Initialize
PMP_Test_Progress::init();
?>
