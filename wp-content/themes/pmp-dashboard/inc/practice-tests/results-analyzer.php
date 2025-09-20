<?php
/**
 * Results Analyzer
 * Task: T019 - Results Analyzer
 */

class PMP_Results_Analyzer {
    
    /**
     * Analyze test results and provide insights
     */
    public static function analyze_results($session_id) {
        global $wpdb;
        
        $session = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}pmp_test_attempts WHERE id = %d",
            $session_id
        ));
        
        if (!$session || !$session->completed_at) {
            return new WP_Error('invalid_session', 'Test session not found or not completed');
        }
        
        $session_data = json_decode($session->attempt_data, true);
        $results = $session_data['results'] ?? [];
        
        $analysis = [
            'basic_stats' => self::get_basic_stats($session),
            'domain_analysis' => self::analyze_domain_performance($results),
            'question_analysis' => self::analyze_question_performance($session_data),
            'time_analysis' => self::analyze_time_performance($session_data),
            'weak_areas' => self::identify_weak_areas($results),
            'recommendations' => self::generate_recommendations($session->user_id, $results),
            'comparison' => self::compare_with_averages($session),
            'improvement_areas' => self::identify_improvement_areas($results)
        ];
        
        return $analysis;
    }
    
    /**
     * Get basic statistics
     */
    private static function get_basic_stats($session) {
        return [
            'score' => floatval($session->score),
            'total_questions' => intval($session->total_questions),
            'correct_answers' => intval($session->correct_answers),
            'incorrect_answers' => intval($session->total_questions - $session->correct_answers),
            'time_spent' => intval($session->time_spent),
            'passed' => floatval($session->score) >= 70,
            'completion_date' => $session->completed_at,
            'grade' => self::calculate_grade($session->score)
        ];
    }
    
    /**
     * Analyze domain performance
     */
    private static function analyze_domain_performance($results) {
        $domain_results = $results['domain_results'] ?? [];
        $analysis = [];
        
        // PMP domain target percentages
        $domain_targets = [
            'people' => 42,
            'process' => 50,
            'business_environment' => 8
        ];
        
        foreach ($domain_results as $domain => $data) {
            $target = $domain_targets[$domain] ?? 0;
            $performance = $data['percentage'];
            
            $analysis[$domain] = [
                'name' => self::get_domain_name($domain),
                'correct' => $data['correct'],
                'total' => $data['total'],
                'percentage' => round($performance, 1),
                'target_weight' => $target,
                'performance_level' => self::get_performance_level($performance),
                'needs_improvement' => $performance < 70,
                'strength_level' => self::get_strength_level($performance)
            ];
        }
        
        return $analysis;
    }
    
    /**
     * Analyze question performance
     */
    private static function analyze_question_performance($session_data) {
        $answers = $session_data['answers'] ?? [];
        $questions = $session_data['questions'] ?? [];
        
        global $wpdb;
        
        $analysis = [
            'by_difficulty' => ['easy' => 0, 'medium' => 0, 'hard' => 0],
            'by_type' => ['multiple_choice' => 0, 'drag_drop' => 0, 'scenario' => 0],
            'time_per_question' => [],
            'longest_questions' => [],
            'quickest_questions' => []
        ];
        
        $question_times = [];
        
        foreach ($questions as $question_id) {
            $question = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}pmp_test_questions WHERE id = %d",
                $question_id
            ));
            
            if (!$question) continue;
            
            $user_answer = $answers[$question_id] ?? null;
            $is_correct = $user_answer && ($user_answer['answer'] === $question->correct_answer);
            $time_spent = intval($user_answer['time_spent'] ?? 0);
            
            // Track by difficulty
            $difficulty = $question->difficulty_level ?: 'medium';
            if (!isset($analysis['by_difficulty'][$difficulty])) {
                $analysis['by_difficulty'][$difficulty] = 0;
            }
            if ($is_correct) {
                $analysis['by_difficulty'][$difficulty]++;
            }
            
            // Track by type
            $type = $question->question_type ?: 'multiple_choice';
            if (!isset($analysis['by_type'][$type])) {
                $analysis['by_type'][$type] = 0;
            }
            if ($is_correct) {
                $analysis['by_type'][$type]++;
            }
            
            // Track time
            if ($time_spent > 0) {
                $question_times[] = [
                    'question_id' => $question_id,
                    'time_spent' => $time_spent,
                    'question_text' => substr($question->question_text, 0, 100) . '...',
                    'is_correct' => $is_correct
                ];
            }
        }
        
        // Sort by time
        usort($question_times, function($a, $b) {
            return $b['time_spent'] - $a['time_spent'];
        });
        
        $analysis['longest_questions'] = array_slice($question_times, 0, 5);
        $analysis['quickest_questions'] = array_slice(array_reverse($question_times), 0, 5);
        $analysis['average_time_per_question'] = !empty($question_times) 
            ? array_sum(array_column($question_times, 'time_spent')) / count($question_times)
            : 0;
        
        return $analysis;
    }
    
    /**
     * Analyze time performance
     */
    private static function analyze_time_performance($session_data) {
        $start_time = strtotime($session_data['start_time']);
        $time_limit = $session_data['time_limit'] * 60; // Convert to seconds
        $answers = $session_data['answers'] ?? [];
        
        $total_answer_time = array_sum(array_column($answers, 'time_spent'));
        $time_used_percentage = ($total_answer_time / $time_limit) * 100;
        
        return [
            'time_limit_minutes' => $session_data['time_limit'],
            'time_used_minutes' => round($total_answer_time / 60, 1),
            'time_used_percentage' => round($time_used_percentage, 1),
            'time_remaining_minutes' => round(($time_limit - $total_answer_time) / 60, 1),
            'pace_analysis' => self::analyze_pace($answers, $time_limit),
            'time_management_score' => self::calculate_time_management_score($time_used_percentage)
        ];
    }
    
    /**
     * Identify weak areas
     */
    private static function identify_weak_areas($results) {
        $domain_results = $results['domain_results'] ?? [];
        $weak_areas = [];
        
        foreach ($domain_results as $domain => $data) {
            if ($data['percentage'] < 70) {
                $weak_areas[] = [
                    'domain' => $domain,
                    'name' => self::get_domain_name($domain),
                    'percentage' => $data['percentage'],
                    'gap' => 70 - $data['percentage'],
                    'priority' => self::calculate_priority($data['percentage'], $domain)
                ];
            }
        }
        
        // Sort by priority (lowest scores first)
        usort($weak_areas, function($a, $b) {
            return $a['percentage'] - $b['percentage'];
        });
        
        return $weak_areas;
    }
    
    /**
     * Generate personalized recommendations
     */
    private static function generate_recommendations($user_id, $results) {
        $recommendations = [];
        $domain_results = $results['domain_results'] ?? [];
        
        foreach ($domain_results as $domain => $data) {
            if ($data['percentage'] < 70) {
                $recommendations[] = [
                    'type' => 'study_focus',
                    'domain' => $domain,
                    'title' => 'Focus on ' . self::get_domain_name($domain),
                    'description' => "Your score in {$data['percentage']}% indicates need for improvement. Target: 70%+",
                    'action' => 'study_domain',
                    'priority' => 'high'
                ];
            } elseif ($data['percentage'] < 85) {
                $recommendations[] = [
                    'type' => 'practice_more',
                    'domain' => $domain,
                    'title' => 'Practice more ' . self::get_domain_name($domain) . ' questions',
                    'description' => "Good foundation at {$data['percentage']}%. Practice more to reach mastery (85%+)",
                    'action' => 'practice_domain',
                    'priority' => 'medium'
                ];
            }
        }
        
        // Add general recommendations
        $overall_score = $results['score'] ?? 0;
        
        if ($overall_score < 70) {
            $recommendations[] = [
                'type' => 'overall_study',
                'title' => 'Comprehensive Study Plan Needed',
                'description' => 'Your overall score suggests need for systematic study across all domains',
                'action' => 'create_study_plan',
                'priority' => 'high'
            ];
        }
        
        if ($overall_score >= 70 && $overall_score < 85) {
            $recommendations[] = [
                'type' => 'exam_ready',
                'title' => 'Continue Practice Tests',
                'description' => 'You\'re on track! Take more practice tests to build confidence',
                'action' => 'more_practice_tests',
                'priority' => 'medium'
            ];
        }
        
        return array_slice($recommendations, 0, 5); // Limit to top 5
    }
    
    /**
     * Compare with averages
     */
    private static function compare_with_averages($session) {
        global $wpdb;
        
        // Get test averages
        $test_stats = $wpdb->get_row($wpdb->prepare(
            "SELECT AVG(score) as avg_score, AVG(time_spent) as avg_time
             FROM {$wpdb->prefix}pmp_test_attempts 
             WHERE test_id = %d AND completed_at IS NOT NULL",
            $session->test_id
        ));
        
        // Get user's historical average
        $user_avg = $wpdb->get_var($wpdb->prepare(
            "SELECT AVG(score) FROM {$wpdb->prefix}pmp_test_attempts 
             WHERE user_id = %d AND completed_at IS NOT NULL AND id != %d",
            $session->user_id, $session->id
        ));
        
        return [
            'test_average_score' => round(floatval($test_stats->avg_score ?? 0), 1),
            'test_average_time' => round(floatval($test_stats->avg_time ?? 0), 1),
            'user_average_score' => round(floatval($user_avg ?? 0), 1),
            'score_vs_test_avg' => round($session->score - floatval($test_stats->avg_score ?? 0), 1),
            'score_vs_user_avg' => $user_avg ? round($session->score - floatval($user_avg), 1) : null,
            'percentile' => self::calculate_percentile($session->score, $session->test_id)
        ];
    }
    
    /**
     * Identify improvement areas
     */
    private static function identify_improvement_areas($results) {
        $areas = [];
        $domain_results = $results['domain_results'] ?? [];
        
        // Find domains that need most improvement
        foreach ($domain_results as $domain => $data) {
            if ($data['percentage'] < 85) {
                $improvement_needed = 85 - $data['percentage'];
                $areas[] = [
                    'area' => self::get_domain_name($domain),
                    'current_score' => $data['percentage'],
                    'target_score' => 85,
                    'improvement_needed' => round($improvement_needed, 1),
                    'study_hours_estimate' => self::estimate_study_hours($improvement_needed),
                    'resources' => self::get_domain_resources($domain)
                ];
            }
        }
        
        // Sort by improvement needed (highest first)
        usort($areas, function($a, $b) {
            return $b['improvement_needed'] - $a['improvement_needed'];
        });
        
        return $areas;
    }
    
    /**
     * Helper methods
     */
    private static function get_domain_name($domain) {
        $names = [
            'people' => 'People',
            'process' => 'Process',
            'business_environment' => 'Business Environment'
        ];
        
        return $names[$domain] ?? ucfirst($domain);
    }
    
    private static function calculate_grade($score) {
        if ($score >= 90) return 'A';
        if ($score >= 80) return 'B';
        if ($score >= 70) return 'C';
        if ($score >= 60) return 'D';
        return 'F';
    }
    
    private static function get_performance_level($percentage) {
        if ($percentage >= 85) return 'Excellent';
        if ($percentage >= 70) return 'Good';
        if ($percentage >= 60) return 'Fair';
        return 'Needs Improvement';
    }
    
    private static function get_strength_level($percentage) {
        if ($percentage >= 90) return 'strength';
        if ($percentage >= 70) return 'adequate';
        return 'weakness';
    }
    
    private static function calculate_priority($percentage, $domain) {
        $domain_weights = [
            'process' => 0.5,
            'people' => 0.42,
            'business_environment' => 0.08
        ];
        
        $weight = $domain_weights[$domain] ?? 0.33;
        $gap = max(0, 70 - $percentage);
        
        return $gap * $weight;
    }
    
    private static function analyze_pace($answers, $time_limit) {
        $total_questions = count($answers);
        $ideal_time_per_question = $time_limit / $total_questions;
        
        $fast_answers = 0;
        $slow_answers = 0;
        
        foreach ($answers as $answer) {
            $time_spent = $answer['time_spent'] ?? 0;
            
            if ($time_spent < $ideal_time_per_question * 0.5) {
                $fast_answers++;
            } elseif ($time_spent > $ideal_time_per_question * 1.5) {
                $slow_answers++;
            }
        }
        
        return [
            'ideal_time_per_question' => round($ideal_time_per_question, 1),
            'fast_answers' => $fast_answers,
            'slow_answers' => $slow_answers,
            'pace_rating' => self::calculate_pace_rating($fast_answers, $slow_answers, $total_questions)
        ];
    }
    
    private static function calculate_time_management_score($time_used_percentage) {
        if ($time_used_percentage <= 80) return 'Excellent';
        if ($time_used_percentage <= 90) return 'Good';
        if ($time_used_percentage <= 100) return 'Adequate';
        return 'Poor';
    }
    
    private static function calculate_pace_rating($fast, $slow, $total) {
        $fast_ratio = $fast / $total;
        $slow_ratio = $slow / $total;
        
        if ($fast_ratio > 0.3) return 'Too Fast';
        if ($slow_ratio > 0.3) return 'Too Slow';
        return 'Good Pace';
    }
    
    private static function calculate_percentile($score, $test_id) {
        global $wpdb;
        
        $lower_scores = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}pmp_test_attempts 
             WHERE test_id = %d AND completed_at IS NOT NULL AND score < %f",
            $test_id, $score
        ));
        
        $total_attempts = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}pmp_test_attempts 
             WHERE test_id = %d AND completed_at IS NOT NULL",
            $test_id
        ));
        
        return $total_attempts > 0 ? round(($lower_scores / $total_attempts) * 100) : 0;
    }
    
    private static function estimate_study_hours($improvement_needed) {
        // Rough estimate: 2 hours per percentage point improvement
        return round($improvement_needed * 2);
    }
    
    private static function get_domain_resources($domain) {
        // Get related lessons and resources for the domain
        $resources = get_posts([
            'post_type' => ['lesson', 'resource'],
            'post_status' => 'publish',
            'posts_per_page' => 5,
            'tax_query' => [
                [
                    'taxonomy' => 'pmp_domain',
                    'field' => 'slug',
                    'terms' => $domain
                ]
            ]
        ]);
        
        return array_map(function($post) {
            return [
                'id' => $post->ID,
                'title' => $post->post_title,
                'type' => $post->post_type,
                'url' => get_permalink($post->ID)
            ];
        }, $resources);
    }
    
    /**
     * Get historical performance trends
     */
    public static function get_performance_trends($user_id, $limit = 10) {
        global $wpdb;
        
        $attempts = $wpdb->get_results($wpdb->prepare(
            "SELECT score, completed_at, test_id 
             FROM {$wpdb->prefix}pmp_test_attempts 
             WHERE user_id = %d AND completed_at IS NOT NULL 
             ORDER BY completed_at DESC 
             LIMIT %d",
            $user_id, $limit
        ));
        
        $trends = [
            'scores' => array_reverse(array_column($attempts, 'score')),
            'dates' => array_reverse(array_column($attempts, 'completed_at')),
            'improvement_trend' => self::calculate_trend(array_column($attempts, 'score')),
            'average_score' => !empty($attempts) ? array_sum(array_column($attempts, 'score')) / count($attempts) : 0
        ];
        
        return $trends;
    }
    
    private static function calculate_trend($scores) {
        if (count($scores) < 2) return 'insufficient_data';
        
        $recent_avg = array_sum(array_slice($scores, 0, 3)) / min(3, count($scores));
        $older_avg = array_sum(array_slice($scores, -3)) / min(3, count($scores));
        
        $difference = $recent_avg - $older_avg;
        
        if ($difference > 5) return 'improving';
        if ($difference < -5) return 'declining';
        return 'stable';
    }
}
?>
