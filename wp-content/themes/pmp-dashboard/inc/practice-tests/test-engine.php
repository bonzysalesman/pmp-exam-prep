<?php
/**
 * Test Engine Core
 * Task: T017 - Test Engine Core
 */

class PMP_Test_Engine {
    
    /**
     * Generate test with randomized questions
     */
    public static function generate_test($test_id, $user_id, $options = []) {
        $defaults = [
            'question_count' => 50,
            'time_limit' => 180, // minutes
            'randomize' => true,
            'domain_distribution' => true
        ];
        
        $options = wp_parse_args($options, $defaults);
        
        // Get test settings
        $test_settings = self::get_test_settings($test_id);
        $question_count = $test_settings['question_count'] ?? $options['question_count'];
        $time_limit = $test_settings['time_limit'] ?? $options['time_limit'];
        
        // Get questions for this test
        $questions = self::get_test_questions($test_id, $question_count, $options);
        
        if (empty($questions)) {
            return new WP_Error('no_questions', 'No questions available for this test');
        }
        
        // Create test session
        $session_id = self::create_test_session($user_id, $test_id, $questions, $time_limit);
        
        return [
            'session_id' => $session_id,
            'questions' => $questions,
            'time_limit' => $time_limit,
            'question_count' => count($questions)
        ];
    }
    
    /**
     * Get test questions with distribution
     */
    private static function get_test_questions($test_id, $question_count, $options) {
        global $wpdb;
        
        if ($options['domain_distribution']) {
            // Distribute questions by PMP domain percentages (42% People, 50% Process, 8% Business)
            $domain_counts = [
                'people' => round($question_count * 0.42),
                'process' => round($question_count * 0.50),
                'business_environment' => round($question_count * 0.08)
            ];
            
            $questions = [];
            
            foreach ($domain_counts as $domain => $count) {
                if ($count > 0) {
                    $domain_questions = $wpdb->get_results($wpdb->prepare(
                        "SELECT * FROM {$wpdb->prefix}pmp_test_questions 
                         WHERE test_id = %d AND domain = %s 
                         ORDER BY " . ($options['randomize'] ? 'RAND()' : 'id') . " 
                         LIMIT %d",
                        $test_id, $domain, $count
                    ));
                    
                    $questions = array_merge($questions, $domain_questions);
                }
            }
            
            // Fill remaining slots if needed
            $remaining = $question_count - count($questions);
            if ($remaining > 0) {
                $additional = $wpdb->get_results($wpdb->prepare(
                    "SELECT * FROM {$wpdb->prefix}pmp_test_questions 
                     WHERE test_id = %d AND id NOT IN (" . implode(',', wp_list_pluck($questions, 'id')) . ")
                     ORDER BY " . ($options['randomize'] ? 'RAND()' : 'id') . " 
                     LIMIT %d",
                    $test_id, $remaining
                ));
                
                $questions = array_merge($questions, $additional);
            }
        } else {
            // Simple random selection
            $questions = $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}pmp_test_questions 
                 WHERE test_id = %d 
                 ORDER BY " . ($options['randomize'] ? 'RAND()' : 'id') . " 
                 LIMIT %d",
                $test_id, $question_count
            ));
        }
        
        // Add answer options for each question
        foreach ($questions as &$question) {
            $question->options = $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}pmp_question_options 
                 WHERE question_id = %d 
                 ORDER BY option_order",
                $question->id
            ));
        }
        
        return $questions;
    }
    
    /**
     * Create test session
     */
    private static function create_test_session($user_id, $test_id, $questions, $time_limit) {
        global $wpdb;
        
        $session_data = [
            'questions' => wp_list_pluck($questions, 'id'),
            'answers' => [],
            'start_time' => current_time('mysql'),
            'time_limit' => $time_limit,
            'current_question' => 0
        ];
        
        $wpdb->insert(
            $wpdb->prefix . 'pmp_test_attempts',
            [
                'user_id' => $user_id,
                'test_id' => $test_id,
                'attempt_data' => json_encode($session_data),
                'created_at' => current_time('mysql')
            ],
            ['%d', '%d', '%s', '%s']
        );
        
        return $wpdb->insert_id;
    }
    
    /**
     * Submit answer for question
     */
    public static function submit_answer($session_id, $question_id, $answer, $time_spent = 0) {
        global $wpdb;
        
        // Get session data
        $session = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}pmp_test_attempts WHERE id = %d",
            $session_id
        ));
        
        if (!$session) {
            return new WP_Error('invalid_session', 'Test session not found');
        }
        
        $session_data = json_decode($session->attempt_data, true);
        
        // Check if session is still valid (not expired)
        if (self::is_session_expired($session_data)) {
            return new WP_Error('session_expired', 'Test session has expired');
        }
        
        // Store answer
        $session_data['answers'][$question_id] = [
            'answer' => $answer,
            'time_spent' => $time_spent,
            'timestamp' => current_time('mysql')
        ];
        
        // Update session
        $wpdb->update(
            $wpdb->prefix . 'pmp_test_attempts',
            ['attempt_data' => json_encode($session_data)],
            ['id' => $session_id],
            ['%s'],
            ['%d']
        );
        
        return true;
    }
    
    /**
     * Complete test and calculate results
     */
    public static function complete_test($session_id) {
        global $wpdb;
        
        // Get session data
        $session = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}pmp_test_attempts WHERE id = %d",
            $session_id
        ));
        
        if (!$session) {
            return new WP_Error('invalid_session', 'Test session not found');
        }
        
        $session_data = json_decode($session->attempt_data, true);
        
        // Calculate results
        $results = self::calculate_results($session->test_id, $session_data);
        
        // Update session with results
        $wpdb->update(
            $wpdb->prefix . 'pmp_test_attempts',
            [
                'score' => $results['score'],
                'total_questions' => $results['total_questions'],
                'correct_answers' => $results['correct_answers'],
                'time_spent' => $results['time_spent'],
                'completed_at' => current_time('mysql'),
                'attempt_data' => json_encode(array_merge($session_data, ['results' => $results]))
            ],
            ['id' => $session_id],
            ['%f', '%d', '%d', '%d', '%s', '%s'],
            ['%d']
        );
        
        // Update user progress
        PMP_Content_Progress::update_content_progress(
            $session->user_id,
            $session->test_id,
            'practice_test',
            'completed',
            100,
            $results['time_spent']
        );
        
        return $results;
    }
    
    /**
     * Calculate test results
     */
    private static function calculate_results($test_id, $session_data) {
        global $wpdb;
        
        $answers = $session_data['answers'] ?? [];
        $question_ids = $session_data['questions'] ?? [];
        
        $correct_answers = 0;
        $total_questions = count($question_ids);
        $total_time = 0;
        $domain_results = [];
        
        foreach ($question_ids as $question_id) {
            // Get correct answer
            $question = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}pmp_test_questions WHERE id = %d",
                $question_id
            ));
            
            if (!$question) continue;
            
            $user_answer = $answers[$question_id]['answer'] ?? '';
            $is_correct = ($user_answer === $question->correct_answer);
            
            if ($is_correct) {
                $correct_answers++;
            }
            
            // Track domain performance
            $domain = $question->domain ?: 'unknown';
            if (!isset($domain_results[$domain])) {
                $domain_results[$domain] = ['correct' => 0, 'total' => 0];
            }
            
            $domain_results[$domain]['total']++;
            if ($is_correct) {
                $domain_results[$domain]['correct']++;
            }
            
            // Add time spent
            $total_time += intval($answers[$question_id]['time_spent'] ?? 0);
        }
        
        $score = $total_questions > 0 ? ($correct_answers / $total_questions) * 100 : 0;
        
        // Calculate domain percentages
        foreach ($domain_results as $domain => &$result) {
            $result['percentage'] = $result['total'] > 0 ? ($result['correct'] / $result['total']) * 100 : 0;
        }
        
        return [
            'score' => round($score, 2),
            'total_questions' => $total_questions,
            'correct_answers' => $correct_answers,
            'time_spent' => round($total_time / 60), // Convert to minutes
            'domain_results' => $domain_results,
            'passed' => $score >= 70, // PMP passing score
            'completion_time' => current_time('mysql')
        ];
    }
    
    /**
     * Get test settings
     */
    private static function get_test_settings($test_id) {
        return [
            'question_count' => get_post_meta($test_id, '_question_count', true) ?: 50,
            'time_limit' => get_post_meta($test_id, '_time_limit', true) ?: 180,
            'passing_score' => get_post_meta($test_id, '_passing_score', true) ?: 70,
            'difficulty_level' => get_post_meta($test_id, '_difficulty_level', true) ?: 'medium'
        ];
    }
    
    /**
     * Check if session is expired
     */
    private static function is_session_expired($session_data) {
        $start_time = strtotime($session_data['start_time']);
        $time_limit = $session_data['time_limit'] * 60; // Convert to seconds
        
        return (time() - $start_time) > $time_limit;
    }
    
    /**
     * Get session status
     */
    public static function get_session_status($session_id) {
        global $wpdb;
        
        $session = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}pmp_test_attempts WHERE id = %d",
            $session_id
        ));
        
        if (!$session) {
            return null;
        }
        
        $session_data = json_decode($session->attempt_data, true);
        $start_time = strtotime($session_data['start_time']);
        $time_limit = $session_data['time_limit'] * 60;
        $elapsed = time() - $start_time;
        $remaining = max(0, $time_limit - $elapsed);
        
        return [
            'session_id' => $session_id,
            'is_completed' => !empty($session->completed_at),
            'is_expired' => $remaining <= 0,
            'time_remaining' => $remaining,
            'time_elapsed' => $elapsed,
            'questions_answered' => count($session_data['answers'] ?? []),
            'total_questions' => count($session_data['questions'] ?? []),
            'current_question' => $session_data['current_question'] ?? 0
        ];
    }
    
    /**
     * Get user test history
     */
    public static function get_user_test_history($user_id, $test_id = null, $limit = 10) {
        global $wpdb;
        
        $sql = "SELECT ta.*, p.post_title 
                FROM {$wpdb->prefix}pmp_test_attempts ta
                JOIN {$wpdb->posts} p ON ta.test_id = p.ID
                WHERE ta.user_id = %d";
        
        $params = [$user_id];
        
        if ($test_id) {
            $sql .= " AND ta.test_id = %d";
            $params[] = $test_id;
        }
        
        $sql .= " ORDER BY ta.created_at DESC LIMIT %d";
        $params[] = $limit;
        
        return $wpdb->get_results($wpdb->prepare($sql, $params));
    }
    
    /**
     * Get test statistics
     */
    public static function get_test_statistics($test_id) {
        global $wpdb;
        
        $stats = [];
        
        // Total attempts
        $stats['total_attempts'] = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}pmp_test_attempts WHERE test_id = %d",
            $test_id
        ));
        
        // Completed attempts
        $stats['completed_attempts'] = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}pmp_test_attempts 
             WHERE test_id = %d AND completed_at IS NOT NULL",
            $test_id
        ));
        
        // Average score
        $stats['average_score'] = $wpdb->get_var($wpdb->prepare(
            "SELECT AVG(score) FROM {$wpdb->prefix}pmp_test_attempts 
             WHERE test_id = %d AND completed_at IS NOT NULL",
            $test_id
        ));
        
        // Pass rate
        $stats['pass_rate'] = $wpdb->get_var($wpdb->prepare(
            "SELECT (COUNT(*) * 100.0 / (SELECT COUNT(*) FROM {$wpdb->prefix}pmp_test_attempts WHERE test_id = %d AND completed_at IS NOT NULL)) 
             FROM {$wpdb->prefix}pmp_test_attempts 
             WHERE test_id = %d AND completed_at IS NOT NULL AND score >= 70",
            $test_id, $test_id
        ));
        
        return $stats;
    }
}
?>
