<?php
/**
 * Progress Tracking Database Management
 * 
 * Handles database table creation, migration, and schema management
 * for the enhanced progress tracking system.
 */

if (!defined('ABSPATH')) {
    exit;
}

class PMP_Progress_Database {
    
    /**
     * Create all progress tracking tables
     */
    public static function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // User Progress table
        $table_name = $wpdb->prefix . 'pmp_user_progress';
        $sql = "CREATE TABLE $table_name (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id bigint(20) UNSIGNED NOT NULL,
            domain varchar(50) NOT NULL,
            completion_percentage decimal(5,2) DEFAULT 0.00,
            lessons_completed int(11) DEFAULT 0,
            total_lessons int(11) DEFAULT 0,
            time_spent_minutes int(11) DEFAULT 0,
            last_updated datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY unique_user_domain (user_id, domain),
            KEY idx_user_id (user_id),
            KEY idx_domain (domain),
            KEY idx_completion (completion_percentage),
            KEY idx_updated (last_updated)
        ) $charset_collate;";
        
        // Study Sessions table
        $table_name2 = $wpdb->prefix . 'pmp_study_sessions';
        $sql2 = "CREATE TABLE $table_name2 (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id bigint(20) UNSIGNED NOT NULL,
            session_date date NOT NULL,
            duration_minutes int(11) DEFAULT 0,
            lessons_completed tinyint(3) UNSIGNED DEFAULT 0,
            domain_focus varchar(50) DEFAULT '',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY unique_user_date (user_id, session_date),
            KEY idx_user_id (user_id),
            KEY idx_session_date (session_date),
            KEY idx_domain_focus (domain_focus),
            KEY idx_user_date (user_id, session_date)
        ) $charset_collate;";
        
        // Lesson Progress table
        $table_name3 = $wpdb->prefix . 'pmp_lesson_progress';
        $sql3 = "CREATE TABLE $table_name3 (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id bigint(20) UNSIGNED NOT NULL,
            lesson_id bigint(20) UNSIGNED NOT NULL,
            status varchar(20) DEFAULT 'not_started',
            progress_percentage decimal(5,2) DEFAULT 0.00,
            time_spent_minutes int(11) DEFAULT 0,
            completed_at datetime NULL,
            last_accessed datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            attempts tinyint(3) UNSIGNED DEFAULT 0,
            PRIMARY KEY (id),
            UNIQUE KEY unique_user_lesson (user_id, lesson_id),
            KEY idx_user_id (user_id),
            KEY idx_lesson_id (lesson_id),
            KEY idx_status (status),
            KEY idx_completed (completed_at),
            KEY idx_user_status (user_id, status)
        ) $charset_collate;";
        
        // Study Streak table
        $table_name4 = $wpdb->prefix . 'pmp_study_streaks';
        $sql4 = "CREATE TABLE $table_name4 (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id bigint(20) UNSIGNED NOT NULL,
            current_streak int(11) DEFAULT 0,
            longest_streak int(11) DEFAULT 0,
            last_study_date date NULL,
            streak_start_date date NULL,
            total_study_days int(11) DEFAULT 0,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY unique_user (user_id),
            KEY idx_current_streak (current_streak),
            KEY idx_longest_streak (longest_streak),
            KEY idx_last_study_date (last_study_date)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        dbDelta($sql2);
        dbDelta($sql3);
        dbDelta($sql4);
        
        // Initialize domain data
        self::initialize_domain_data();
        
        // Set database version
        update_option('pmp_progress_db_version', '1.0');
    }
    
    /**
     * Initialize domain data for all users
     */
    private static function initialize_domain_data() {
        global $wpdb;
        
        $domains = ['people', 'process', 'business_environment'];
        $domain_lessons = [
            'people' => 38,           // 42% of 91 lessons
            'process' => 45,          // 50% of 91 lessons  
            'business_environment' => 8  // 8% of 91 lessons
        ];
        
        $users = get_users(['fields' => 'ID']);
        
        foreach ($users as $user_id) {
            foreach ($domains as $domain) {
                $wpdb->replace(
                    $wpdb->prefix . 'pmp_user_progress',
                    [
                        'user_id' => $user_id,
                        'domain' => $domain,
                        'total_lessons' => $domain_lessons[$domain],
                        'completion_percentage' => 0.00,
                        'lessons_completed' => 0,
                        'time_spent_minutes' => 0
                    ],
                    ['%d', '%s', '%d', '%f', '%d', '%d']
                );
            }
            
            // Initialize streak data
            $wpdb->replace(
                $wpdb->prefix . 'pmp_study_streaks',
                [
                    'user_id' => $user_id,
                    'current_streak' => 0,
                    'longest_streak' => 0,
                    'total_study_days' => 0
                ],
                ['%d', '%d', '%d', '%d']
            );
        }
    }
    
    /**
     * Drop all progress tracking tables
     */
    public static function drop_tables() {
        global $wpdb;
        
        $tables = [
            $wpdb->prefix . 'pmp_user_progress',
            $wpdb->prefix . 'pmp_study_sessions', 
            $wpdb->prefix . 'pmp_lesson_progress',
            $wpdb->prefix . 'pmp_study_streaks'
        ];
        
        foreach ($tables as $table) {
            $wpdb->query("DROP TABLE IF EXISTS $table");
        }
    }
    
    /**
     * Check if tables exist
     */
    public static function tables_exist() {
        global $wpdb;
        
        $table = $wpdb->prefix . 'pmp_user_progress';
        return $wpdb->get_var("SHOW TABLES LIKE '$table'") === $table;
    }
}

// Hook into theme activation
add_action('after_switch_theme', ['PMP_Progress_Database', 'create_tables']);

// Hook into new user registration
add_action('user_register', function($user_id) {
    $domains = ['people', 'process', 'business_environment'];
    $domain_lessons = [
        'people' => 38,
        'process' => 45, 
        'business_environment' => 8
    ];
    
    global $wpdb;
    
    foreach ($domains as $domain) {
        $wpdb->insert(
            $wpdb->prefix . 'pmp_user_progress',
            [
                'user_id' => $user_id,
                'domain' => $domain,
                'total_lessons' => $domain_lessons[$domain],
                'completion_percentage' => 0.00,
                'lessons_completed' => 0,
                'time_spent_minutes' => 0
            ],
            ['%d', '%s', '%d', '%f', '%d', '%d']
        );
    }
    
    // Initialize streak data
    $wpdb->insert(
        $wpdb->prefix . 'pmp_study_streaks',
        [
            'user_id' => $user_id,
            'current_streak' => 0,
            'longest_streak' => 0,
            'total_study_days' => 0
        ],
        ['%d', '%d', '%d', '%d']
    );
});
?>
