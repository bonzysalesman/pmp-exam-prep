<?php
/**
 * Main Progress Tracking Class
 * 
 * Core business logic for tracking user progress, calculating domain completion,
 * and managing study streaks.
 */

if (!defined('ABSPATH')) {
    exit;
}

class PMP_Progress_Tracker {
    
    /**
     * Update lesson progress and recalculate domain progress
     */
    public static function update_lesson_progress($user_id, $lesson_id, $status, $progress_percentage = 0, $time_spent = 0) {
        global $wpdb;
        
        // Validate inputs
        $user_id = absint($user_id);
        $lesson_id = absint($lesson_id);
        $status = sanitize_text_field($status);
        $progress_percentage = floatval($progress_percentage);
        $time_spent = absint($time_spent);
        
        if (!in_array($status, ['not_started', 'in_progress', 'completed'])) {
            return new WP_Error('invalid_status', 'Invalid lesson status');
        }
        
        // Get lesson domain
        $lesson_domain = get_post_meta($lesson_id, 'pmp_domain', true);
        if (!$lesson_domain) {
            $lesson_domain = 'process'; // Default domain
        }
        
        // Update lesson progress
        $completed_at = ($status === 'completed') ? current_time('mysql') : null;
        
        $wpdb->replace(
            $wpdb->prefix . 'pmp_lesson_progress',
            [
                'user_id' => $user_id,
                'lesson_id' => $lesson_id,
                'status' => $status,
                'progress_percentage' => $progress_percentage,
                'time_spent_minutes' => $time_spent,
                'completed_at' => $completed_at,
                'attempts' => $wpdb->get_var($wpdb->prepare(
                    "SELECT attempts + 1 FROM {$wpdb->prefix}pmp_lesson_progress WHERE user_id = %d AND lesson_id = %d",
                    $user_id, $lesson_id
                )) ?: 1
            ],
            ['%d', '%d', '%s', '%f', '%d', '%s', '%d']
        );
        
        // Recalculate domain progress
        self::recalculate_domain_progress($user_id, $lesson_domain);
        
        // Update study session if lesson completed
        if ($status === 'completed') {
            self::update_study_session($user_id, $lesson_domain, $time_spent);
        }
        
        return true;
    }
    
    /**
     * Recalculate domain progress based on completed lessons
     */
    public static function recalculate_domain_progress($user_id, $domain) {
        global $wpdb;
        
        // Get completed lessons count for domain
        $completed_count = $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(*) 
            FROM {$wpdb->prefix}pmp_lesson_progress lp
            JOIN {$wpdb->prefix}posts p ON lp.lesson_id = p.ID
            WHERE lp.user_id = %d 
            AND lp.status = 'completed'
            AND p.post_type = 'lesson'
            AND EXISTS (
                SELECT 1 FROM {$wpdb->prefix}postmeta pm 
                WHERE pm.post_id = p.ID 
                AND pm.meta_key = 'pmp_domain' 
                AND pm.meta_value = %s
            )
        ", $user_id, $domain));
        
        // Get total time spent in domain
        $total_time = $wpdb->get_var($wpdb->prepare("
            SELECT SUM(lp.time_spent_minutes)
            FROM {$wpdb->prefix}pmp_lesson_progress lp
            JOIN {$wpdb->prefix}posts p ON lp.lesson_id = p.ID
            WHERE lp.user_id = %d
            AND p.post_type = 'lesson'
            AND EXISTS (
                SELECT 1 FROM {$wpdb->prefix}postmeta pm 
                WHERE pm.post_id = p.ID 
                AND pm.meta_key = 'pmp_domain' 
                AND pm.meta_value = %s
            )
        ", $user_id, $domain)) ?: 0;
        
        // Get total lessons for domain
        $total_lessons = $wpdb->get_var($wpdb->prepare(
            "SELECT total_lessons FROM {$wpdb->prefix}pmp_user_progress WHERE user_id = %d AND domain = %s",
            $user_id, $domain
        ));
        
        // Calculate completion percentage
        $completion_percentage = $total_lessons > 0 ? ($completed_count / $total_lessons) * 100 : 0;
        
        // Update domain progress
        $wpdb->update(
            $wpdb->prefix . 'pmp_user_progress',
            [
                'completion_percentage' => $completion_percentage,
                'lessons_completed' => $completed_count,
                'time_spent_minutes' => $total_time
            ],
            ['user_id' => $user_id, 'domain' => $domain],
            ['%f', '%d', '%d'],
            ['%d', '%s']
        );
    }
    
    /**
     * Update or create study session for today
     */
    public static function update_study_session($user_id, $domain_focus, $time_spent) {
        global $wpdb;
        
        $today = current_time('Y-m-d');
        
        // Check if session exists for today
        $existing_session = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}pmp_study_sessions WHERE user_id = %d AND session_date = %s",
            $user_id, $today
        ));
        
        if ($existing_session) {
            // Update existing session
            $wpdb->update(
                $wpdb->prefix . 'pmp_study_sessions',
                [
                    'duration_minutes' => $existing_session->duration_minutes + $time_spent,
                    'lessons_completed' => $existing_session->lessons_completed + 1,
                    'domain_focus' => $domain_focus // Update to latest domain
                ],
                ['user_id' => $user_id, 'session_date' => $today],
                ['%d', '%d', '%s'],
                ['%d', '%s']
            );
        } else {
            // Create new session
            $wpdb->insert(
                $wpdb->prefix . 'pmp_study_sessions',
                [
                    'user_id' => $user_id,
                    'session_date' => $today,
                    'duration_minutes' => $time_spent,
                    'lessons_completed' => 1,
                    'domain_focus' => $domain_focus
                ],
                ['%d', '%s', '%d', '%d', '%s']
            );
        }
        
        // Update study streak
        self::update_study_streak($user_id);
    }
    
    /**
     * Update study streak based on session activity
     */
    public static function update_study_streak($user_id) {
        global $wpdb;
        
        $today = current_time('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        
        // Get current streak data
        $streak_data = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}pmp_study_streaks WHERE user_id = %d",
            $user_id
        ));
        
        if (!$streak_data) {
            // Create initial streak record
            $wpdb->insert(
                $wpdb->prefix . 'pmp_study_streaks',
                [
                    'user_id' => $user_id,
                    'current_streak' => 1,
                    'longest_streak' => 1,
                    'last_study_date' => $today,
                    'streak_start_date' => $today,
                    'total_study_days' => 1
                ],
                ['%d', '%d', '%d', '%s', '%s', '%d']
            );
            return;
        }
        
        // Calculate new streak
        $current_streak = 1;
        $longest_streak = $streak_data->longest_streak;
        $total_study_days = $streak_data->total_study_days;
        $streak_start_date = $today;
        
        if ($streak_data->last_study_date === $today) {
            // Already studied today, no change needed
            return;
        } elseif ($streak_data->last_study_date === $yesterday) {
            // Consecutive day, increment streak
            $current_streak = $streak_data->current_streak + 1;
            $streak_start_date = $streak_data->streak_start_date;
        }
        
        // Update longest streak if current exceeds it
        if ($current_streak > $longest_streak) {
            $longest_streak = $current_streak;
        }
        
        // Increment total study days if not already studied today
        if ($streak_data->last_study_date !== $today) {
            $total_study_days++;
        }
        
        // Update streak record
        $wpdb->update(
            $wpdb->prefix . 'pmp_study_streaks',
            [
                'current_streak' => $current_streak,
                'longest_streak' => $longest_streak,
                'last_study_date' => $today,
                'streak_start_date' => $streak_start_date,
                'total_study_days' => $total_study_days
            ],
            ['user_id' => $user_id],
            ['%d', '%d', '%s', '%s', '%d'],
            ['%d']
        );
    }
    
    /**
     * Get user progress summary
     */
    public static function get_user_progress($user_id) {
        global $wpdb;
        
        // Get domain progress
        $domain_progress = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}pmp_user_progress WHERE user_id = %d",
            $user_id
        ), ARRAY_A);
        
        // Calculate overall progress (weighted by domain percentages)
        $overall_progress = 0;
        $total_lessons = 0;
        $total_time = 0;
        $domains = [];
        
        foreach ($domain_progress as $domain) {
            $weight = ($domain['domain'] === 'people') ? 0.42 : 
                     (($domain['domain'] === 'process') ? 0.50 : 0.08);
            
            $overall_progress += $domain['completion_percentage'] * $weight;
            $total_lessons += $domain['lessons_completed'];
            $total_time += $domain['time_spent_minutes'];
            
            $domains[$domain['domain']] = [
                'completion_percentage' => floatval($domain['completion_percentage']),
                'lessons_completed' => intval($domain['lessons_completed']),
                'total_lessons' => intval($domain['total_lessons']),
                'time_spent_minutes' => intval($domain['time_spent_minutes'])
            ];
        }
        
        return [
            'user_id' => intval($user_id),
            'overall_progress' => round($overall_progress, 2),
            'domains' => $domains,
            'total_lessons_completed' => $total_lessons,
            'total_study_time_minutes' => $total_time,
            'last_updated' => current_time('mysql')
        ];
    }
    
    /**
     * Get study streak data
     */
    public static function get_study_streak($user_id) {
        global $wpdb;
        
        $streak_data = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}pmp_study_streaks WHERE user_id = %d",
            $user_id
        ), ARRAY_A);
        
        if (!$streak_data) {
            return [
                'current_streak' => 0,
                'longest_streak' => 0,
                'last_study_date' => null,
                'total_study_days' => 0,
                'streak_message' => 'Start your study streak today!',
                'next_milestone' => 7
            ];
        }
        
        // Generate streak message
        $current_streak = intval($streak_data['current_streak']);
        $streak_message = self::get_streak_message($current_streak);
        $next_milestone = self::get_next_milestone($current_streak);
        
        return [
            'current_streak' => $current_streak,
            'longest_streak' => intval($streak_data['longest_streak']),
            'last_study_date' => $streak_data['last_study_date'],
            'total_study_days' => intval($streak_data['total_study_days']),
            'streak_message' => $streak_message,
            'next_milestone' => $next_milestone
        ];
    }
    
    /**
     * Generate motivational streak message
     */
    private static function get_streak_message($streak) {
        if ($streak === 0) {
            return 'Start your study streak today!';
        } elseif ($streak === 1) {
            return 'Great start! Keep it going tomorrow.';
        } elseif ($streak < 7) {
            return "You're on a {$streak}-day streak! Keep building momentum.";
        } elseif ($streak < 30) {
            return "Amazing! {$streak} days in a row. You're building a strong habit.";
        } else {
            return "Incredible! {$streak}-day streak. You're a study champion!";
        }
    }
    
    /**
     * Get next streak milestone
     */
    private static function get_next_milestone($streak) {
        $milestones = [7, 14, 30, 60, 100, 365];
        
        foreach ($milestones as $milestone) {
            if ($streak < $milestone) {
                return $milestone;
            }
        }
        
        return $streak + 100; // Next hundred milestone
    }
}
?>
