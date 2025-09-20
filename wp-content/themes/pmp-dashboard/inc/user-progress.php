<?php
/**
 * User Progress Tracking Functions
 */

// Update lesson progress
function pmp_update_lesson_progress($user_id, $lesson_id, $progress_data) {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'user_lesson_progress';
    
    $defaults = array(
        'status' => 'not_started',
        'progress_percentage' => 0,
        'time_spent' => 0
    );
    
    $data = wp_parse_args($progress_data, $defaults);
    
    // Check if record exists
    $existing = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table_name WHERE user_id = %d AND lesson_id = %d",
        $user_id, $lesson_id
    ));
    
    if ($existing) {
        // Update existing record
        $update_data = array(
            'status' => $data['status'],
            'progress_percentage' => $data['progress_percentage'],
            'time_spent' => $existing->time_spent + $data['time_spent']
        );
        
        if ($data['status'] === 'completed' && !$existing->completed_at) {
            $update_data['completed_at'] = current_time('mysql');
        }
        
        if ($data['status'] === 'in_progress' && !$existing->started_at) {
            $update_data['started_at'] = current_time('mysql');
        }
        
        $wpdb->update(
            $table_name,
            $update_data,
            array('user_id' => $user_id, 'lesson_id' => $lesson_id),
            array('%s', '%d', '%d', '%s', '%s'),
            array('%d', '%d')
        );
    } else {
        // Insert new record
        $insert_data = array(
            'user_id' => $user_id,
            'lesson_id' => $lesson_id,
            'status' => $data['status'],
            'progress_percentage' => $data['progress_percentage'],
            'time_spent' => $data['time_spent']
        );
        
        if ($data['status'] === 'in_progress') {
            $insert_data['started_at'] = current_time('mysql');
        }
        
        if ($data['status'] === 'completed') {
            $insert_data['started_at'] = current_time('mysql');
            $insert_data['completed_at'] = current_time('mysql');
        }
        
        $wpdb->insert($table_name, $insert_data);
    }
    
    // Update study session
    pmp_update_study_session($user_id, $data['time_spent']);
    
    return true;
}

// Get user progress for a lesson
function pmp_get_lesson_progress($user_id, $lesson_id) {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'user_lesson_progress';
    
    $progress = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table_name WHERE user_id = %d AND lesson_id = %d",
        $user_id, $lesson_id
    ));
    
    if (!$progress) {
        return array(
            'status' => 'not_started',
            'progress_percentage' => 0,
            'time_spent' => 0
        );
    }
    
    return array(
        'status' => $progress->status,
        'progress_percentage' => $progress->progress_percentage,
        'time_spent' => $progress->time_spent,
        'started_at' => $progress->started_at,
        'completed_at' => $progress->completed_at
    );
}

// Get user's overall progress
function pmp_get_user_progress($user_id) {
    global $wpdb;
    
    $progress_table = $wpdb->prefix . 'user_lesson_progress';
    $sessions_table = $wpdb->prefix . 'study_sessions';
    
    // Get lesson statistics - handle missing domain_type column
    $column_exists = $wpdb->get_results("SHOW COLUMNS FROM $progress_table LIKE 'domain_type'");
    
    if (!empty($column_exists)) {
        // New query with domain_type
        $lesson_stats = $wpdb->get_row($wpdb->prepare("
            SELECT 
                COUNT(*) as total_lessons,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_lessons,
                SUM(time_spent) as total_time_spent
            FROM $progress_table 
            WHERE user_id = %d
        ", $user_id));
    } else {
        // Fallback query without domain_type
        $lesson_stats = $wpdb->get_row($wpdb->prepare("
            SELECT 
                COUNT(*) as total_lessons,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_lessons,
                SUM(time_spent) as total_time_spent
            FROM $progress_table 
            WHERE user_id = %d
        ", $user_id));
    }
    
    // Get study streak
    $study_streak = pmp_calculate_study_streak($user_id);
    
    // Get this week's study time
    $week_start = date('Y-m-d', strtotime('monday this week'));
    
    // Check if sessions table exists
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$sessions_table'");
    $week_time = 0;
    
    if ($table_exists) {
        $week_time = $wpdb->get_var($wpdb->prepare("
            SELECT SUM(duration) 
            FROM $sessions_table 
            WHERE user_id = %d AND session_date >= %s
        ", $user_id, $week_start));
    }
    
    // Handle null values
    $total_lessons = $lesson_stats ? (int) $lesson_stats->total_lessons : 0;
    $completed_lessons = $lesson_stats ? (int) $lesson_stats->completed_lessons : 0;
    $total_time_spent = $lesson_stats ? (int) $lesson_stats->total_time_spent : 0;
    
    return array(
        'total_lessons' => $total_lessons,
        'completed_lessons' => $completed_lessons,
        'total_time_spent' => $total_time_spent,
        'study_streak' => $study_streak,
        'week_time' => round(($week_time ?: 0) / 3600, 1), // Convert to hours
        'completion_percentage' => $total_lessons > 0 ? 
            round(($completed_lessons / $total_lessons) * 100) : 0
    );
}

// Calculate study streak
function pmp_calculate_study_streak($user_id) {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'study_sessions';
    
    $sessions = $wpdb->get_results($wpdb->prepare("
        SELECT session_date 
        FROM $table_name 
        WHERE user_id = %d 
        ORDER BY session_date DESC
    ", $user_id));
    
    if (empty($sessions)) {
        return 0;
    }
    
    $streak = 0;
    $current_date = date('Y-m-d');
    
    foreach ($sessions as $session) {
        $session_date = $session->session_date;
        
        if ($session_date === $current_date || 
            $session_date === date('Y-m-d', strtotime($current_date . ' -' . $streak . ' days'))) {
            $streak++;
            $current_date = date('Y-m-d', strtotime($current_date . ' -1 day'));
        } else {
            break;
        }
    }
    
    return $streak;
}

// Update study session
function pmp_update_study_session($user_id, $duration) {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'study_sessions';
    $today = date('Y-m-d');
    
    $existing = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table_name WHERE user_id = %d AND session_date = %s",
        $user_id, $today
    ));
    
    if ($existing) {
        $wpdb->update(
            $table_name,
            array('duration' => $existing->duration + $duration),
            array('user_id' => $user_id, 'session_date' => $today),
            array('%d'),
            array('%d', '%s')
        );
    } else {
        $wpdb->insert(
            $table_name,
            array(
                'user_id' => $user_id,
                'session_date' => $today,
                'duration' => $duration,
                'lessons_completed' => 0
            )
        );
    }
}

// Get Work Group progress
function pmp_get_work_group_progress($user_id, $work_group_id) {
    $lessons = get_posts(array(
        'post_type' => 'lesson',
        'meta_query' => array(
            array(
                'key' => 'work_group_id',
                'value' => $work_group_id,
                'compare' => '='
            )
        ),
        'posts_per_page' => -1
    ));
    
    $total_lessons = count($lessons);
    $completed_lessons = 0;
    
    foreach ($lessons as $lesson) {
        $progress = pmp_get_lesson_progress($user_id, $lesson->ID);
        if ($progress['status'] === 'completed') {
            $completed_lessons++;
        }
    }
    
    return array(
        'total_lessons' => $total_lessons,
        'completed_lessons' => $completed_lessons,
        'completion_percentage' => $total_lessons > 0 ? 
            round(($completed_lessons / $total_lessons) * 100) : 0
    );
}
?>
