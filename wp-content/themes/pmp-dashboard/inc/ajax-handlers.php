<?php
/**
 * AJAX Handlers for PMP Dashboard
 */

// Update lesson progress via AJAX
function pmp_ajax_update_lesson_progress() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'pmp_nonce')) {
        wp_die('Security check failed');
    }
    
    // Check if user is logged in
    if (!is_user_logged_in()) {
        wp_send_json_error('User not logged in');
    }
    
    $user_id = get_current_user_id();
    $lesson_id = intval($_POST['lesson_id']);
    $progress_percentage = intval($_POST['progress_percentage']);
    $time_spent = intval($_POST['time_spent']);
    $status = sanitize_text_field($_POST['status']);
    
    // Validate inputs
    if (!$lesson_id || !in_array($status, ['not_started', 'in_progress', 'completed'])) {
        wp_send_json_error('Invalid data');
    }
    
    $progress_data = array(
        'status' => $status,
        'progress_percentage' => $progress_percentage,
        'time_spent' => $time_spent
    );
    
    $result = pmp_update_lesson_progress($user_id, $lesson_id, $progress_data);
    
    if ($result) {
        // Get updated user progress
        $user_progress = pmp_get_user_progress($user_id);
        wp_send_json_success($user_progress);
    } else {
        wp_send_json_error('Failed to update progress');
    }
}
add_action('wp_ajax_update_lesson_progress', 'pmp_ajax_update_lesson_progress');

// Get dashboard data via AJAX
function pmp_ajax_get_dashboard_data() {
    if (!wp_verify_nonce($_POST['nonce'], 'pmp_nonce')) {
        wp_die('Security check failed');
    }
    
    if (!is_user_logged_in()) {
        wp_send_json_error('User not logged in');
    }
    
    $user_id = get_current_user_id();
    
    // Get user progress
    $user_progress = pmp_get_user_progress($user_id);
    
    // Get Work Group progress
    $work_groups = get_posts(array(
        'post_type' => 'work_group',
        'posts_per_page' => -1,
        'orderby' => 'menu_order',
        'order' => 'ASC'
    ));
    
    $wg_progress = array();
    foreach ($work_groups as $wg) {
        $wg_progress[] = array(
            'id' => $wg->ID,
            'title' => $wg->post_title,
            'progress' => pmp_get_work_group_progress($user_id, $wg->ID)
        );
    }
    
    // Get recent activity
    $recent_activity = pmp_get_recent_activity($user_id);
    
    wp_send_json_success(array(
        'user_progress' => $user_progress,
        'work_groups' => $wg_progress,
        'recent_activity' => $recent_activity
    ));
}
add_action('wp_ajax_get_dashboard_data', 'pmp_ajax_get_dashboard_data');

// Submit practice test via AJAX
function pmp_ajax_submit_practice_test() {
    if (!wp_verify_nonce($_POST['nonce'], 'pmp_nonce')) {
        wp_die('Security check failed');
    }
    
    if (!is_user_logged_in()) {
        wp_send_json_error('User not logged in');
    }
    
    $user_id = get_current_user_id();
    $test_id = intval($_POST['test_id']);
    $answers = json_decode(stripslashes($_POST['answers']), true);
    $time_taken = intval($_POST['time_taken']);
    
    if (!$test_id || !$answers) {
        wp_send_json_error('Invalid test data');
    }
    
    // Calculate score
    $score_data = pmp_calculate_test_score($test_id, $answers);
    
    // Save test result
    global $wpdb;
    $table_name = $wpdb->prefix . 'user_test_results';
    
    $wpdb->insert(
        $table_name,
        array(
            'user_id' => $user_id,
            'test_id' => $test_id,
            'score' => $score_data['score'],
            'percentage' => $score_data['percentage'],
            'time_taken' => $time_taken,
            'attempt_number' => pmp_get_next_attempt_number($user_id, $test_id)
        )
    );
    
    wp_send_json_success(array(
        'score' => $score_data['score'],
        'percentage' => $score_data['percentage'],
        'passed' => $score_data['percentage'] >= 70
    ));
}
add_action('wp_ajax_submit_practice_test', 'pmp_ajax_submit_practice_test');

// Get recent activity
function pmp_get_recent_activity($user_id, $limit = 5) {
    global $wpdb;
    
    $progress_table = $wpdb->prefix . 'user_lesson_progress';
    $test_table = $wpdb->prefix . 'user_test_results';
    
    // Get recent lesson completions
    $lessons = $wpdb->get_results($wpdb->prepare("
        SELECT p.post_title, ulp.completed_at, 'lesson' as type
        FROM $progress_table ulp
        JOIN {$wpdb->posts} p ON ulp.lesson_id = p.ID
        WHERE ulp.user_id = %d AND ulp.status = 'completed'
        ORDER BY ulp.completed_at DESC
        LIMIT %d
    ", $user_id, $limit));
    
    // Get recent test results
    $tests = $wpdb->get_results($wpdb->prepare("
        SELECT p.post_title, utr.taken_at, utr.percentage, 'test' as type
        FROM $test_table utr
        JOIN {$wpdb->posts} p ON utr.test_id = p.ID
        WHERE utr.user_id = %d
        ORDER BY utr.taken_at DESC
        LIMIT %d
    ", $user_id, $limit));
    
    // Combine and sort activities
    $activities = array_merge($lessons, $tests);
    usort($activities, function($a, $b) {
        $date_a = $a->type === 'lesson' ? $a->completed_at : $a->taken_at;
        $date_b = $b->type === 'lesson' ? $b->completed_at : $b->taken_at;
        return strtotime($date_b) - strtotime($date_a);
    });
    
    return array_slice($activities, 0, $limit);
}

// Calculate test score
function pmp_calculate_test_score($test_id, $answers) {
    // Get correct answers from test meta
    $correct_answers = get_post_meta($test_id, 'test_answers', true);
    
    if (!$correct_answers) {
        return array('score' => 0, 'percentage' => 0);
    }
    
    $total_questions = count($correct_answers);
    $correct_count = 0;
    
    foreach ($answers as $question_id => $answer) {
        if (isset($correct_answers[$question_id]) && 
            $correct_answers[$question_id] === $answer) {
            $correct_count++;
        }
    }
    
    $percentage = ($correct_count / $total_questions) * 100;
    
    return array(
        'score' => $correct_count,
        'total' => $total_questions,
        'percentage' => round($percentage, 2)
    );
}

// Get next attempt number
function pmp_get_next_attempt_number($user_id, $test_id) {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'user_test_results';
    
    $max_attempt = $wpdb->get_var($wpdb->prepare(
        "SELECT MAX(attempt_number) FROM $table_name WHERE user_id = %d AND test_id = %d",
        $user_id, $test_id
    ));
    
    return ($max_attempt ?: 0) + 1;
}
?>
