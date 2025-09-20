<?php
/**
 * Content Management Database Setup
 * Task: T001 - Database Schema Setup
 */

class PMP_Content_Database {
    
    /**
     * Create all content management tables
     */
    public static function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Define all table creation SQL
        $sql_queries = [
            // Content sequences table
            "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}pmp_content_sequences (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                content_id BIGINT UNSIGNED NOT NULL,
                prerequisite_id BIGINT UNSIGNED NULL,
                sequence_order INT NOT NULL DEFAULT 0,
                is_required BOOLEAN NOT NULL DEFAULT FALSE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY idx_content_id (content_id),
                KEY idx_prerequisite_id (prerequisite_id),
                KEY idx_sequence_order (sequence_order),
                UNIQUE KEY unique_content_sequence (content_id, prerequisite_id)
            ) ENGINE=InnoDB {$charset_collate};",
            
            // Test questions table
            "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}pmp_test_questions (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                test_id BIGINT UNSIGNED NOT NULL,
                question_text TEXT NOT NULL,
                question_type ENUM('multiple_choice', 'drag_drop', 'scenario') DEFAULT 'multiple_choice',
                correct_answer TEXT NOT NULL,
                explanation TEXT NULL,
                difficulty_level ENUM('easy', 'medium', 'hard') DEFAULT 'medium',
                domain VARCHAR(50) NULL,
                knowledge_area VARCHAR(100) NULL,
                points INT NOT NULL DEFAULT 1,
                time_limit INT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY idx_test_id (test_id),
                KEY idx_difficulty_level (difficulty_level),
                KEY idx_domain (domain),
                KEY idx_knowledge_area (knowledge_area),
                KEY idx_question_type (question_type)
            ) ENGINE=InnoDB {$charset_collate};",
            
            // Question options table
            "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}pmp_question_options (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                question_id BIGINT UNSIGNED NOT NULL,
                option_text TEXT NOT NULL,
                is_correct BOOLEAN NOT NULL DEFAULT FALSE,
                option_order INT NOT NULL DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY idx_question_id (question_id),
                KEY idx_option_order (option_order),
                KEY idx_is_correct (is_correct)
            ) ENGINE=InnoDB {$charset_collate};",
            
            // Test attempts table
            "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}pmp_test_attempts (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                user_id BIGINT UNSIGNED NOT NULL,
                test_id BIGINT UNSIGNED NOT NULL,
                score DECIMAL(5,2) NULL,
                total_questions INT NOT NULL DEFAULT 0,
                correct_answers INT NOT NULL DEFAULT 0,
                time_spent INT NULL,
                completed_at TIMESTAMP NULL,
                attempt_data JSON NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY idx_user_id (user_id),
                KEY idx_test_id (test_id),
                KEY idx_completed_at (completed_at),
                KEY idx_score (score),
                KEY idx_user_test (user_id, test_id)
            ) ENGINE=InnoDB {$charset_collate};",
            
            // Content bookmarks table
            "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}pmp_content_bookmarks (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                user_id BIGINT UNSIGNED NOT NULL,
                content_id BIGINT UNSIGNED NOT NULL,
                content_type VARCHAR(50) NOT NULL,
                bookmark_type ENUM('favorite', 'later', 'completed') DEFAULT 'favorite',
                notes TEXT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY idx_user_id (user_id),
                KEY idx_content_id (content_id),
                KEY idx_content_type (content_type),
                KEY idx_bookmark_type (bookmark_type),
                UNIQUE KEY unique_bookmark (user_id, content_id, bookmark_type)
            ) ENGINE=InnoDB {$charset_collate};",
            
            // Learning paths table
            "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}pmp_learning_paths (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                name VARCHAR(255) NOT NULL,
                description TEXT NULL,
                is_default BOOLEAN NOT NULL DEFAULT FALSE,
                is_active BOOLEAN NOT NULL DEFAULT TRUE,
                estimated_hours INT NULL,
                difficulty_level ENUM('beginner', 'intermediate', 'advanced') DEFAULT 'intermediate',
                created_by BIGINT UNSIGNED NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY idx_is_default (is_default),
                KEY idx_is_active (is_active),
                KEY idx_difficulty_level (difficulty_level),
                KEY idx_created_by (created_by)
            ) ENGINE=InnoDB {$charset_collate};",
            
            // Learning path content table
            "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}pmp_learning_path_content (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                path_id BIGINT UNSIGNED NOT NULL,
                content_id BIGINT UNSIGNED NOT NULL,
                content_type VARCHAR(50) NOT NULL,
                sequence_order INT NOT NULL DEFAULT 0,
                is_required BOOLEAN NOT NULL DEFAULT TRUE,
                estimated_minutes INT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY idx_path_id (path_id),
                KEY idx_content_id (content_id),
                KEY idx_content_type (content_type),
                KEY idx_sequence_order (sequence_order),
                UNIQUE KEY unique_path_content (path_id, content_id, content_type)
            ) ENGINE=InnoDB {$charset_collate};"
        ];
        
        // Execute each query
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        foreach ($sql_queries as $sql) {
            dbDelta($sql);
        }
        
        // Insert default learning path
        $existing_path = $wpdb->get_var(
            "SELECT id FROM {$wpdb->prefix}pmp_learning_paths WHERE is_default = 1 LIMIT 1"
        );
        
        if (!$existing_path) {
            $wpdb->insert(
                $wpdb->prefix . 'pmp_learning_paths',
                [
                    'name' => 'PMP Certification Preparation',
                    'description' => 'Complete 13-week PMP exam preparation curriculum following PMI standards',
                    'is_default' => 1,
                    'estimated_hours' => 120,
                    'difficulty_level' => 'intermediate'
                ]
            );
        }
        
        // Verify tables were created
        return self::verify_tables();
    }
    
    /**
     * Verify all required tables exist
     */
    public static function verify_tables() {
        global $wpdb;
        
        $required_tables = [
            $wpdb->prefix . 'pmp_content_sequences',
            $wpdb->prefix . 'pmp_test_questions',
            $wpdb->prefix . 'pmp_question_options',
            $wpdb->prefix . 'pmp_test_attempts',
            $wpdb->prefix . 'pmp_content_bookmarks',
            $wpdb->prefix . 'pmp_learning_paths',
            $wpdb->prefix . 'pmp_learning_path_content'
        ];
        
        $missing_tables = [];
        
        foreach ($required_tables as $table) {
            $exists = $wpdb->get_var("SHOW TABLES LIKE '$table'");
            if ($exists !== $table) {
                $missing_tables[] = $table;
            }
        }
        
        if (!empty($missing_tables)) {
            error_log('PMP Content Management: Missing tables - ' . implode(', ', $missing_tables));
            return false;
        }
        
        return true;
    }
    
    /**
     * Drop all content management tables (for development/testing)
     */
    public static function drop_tables() {
        global $wpdb;
        
        $tables = [
            $wpdb->prefix . 'pmp_learning_path_content',
            $wpdb->prefix . 'pmp_learning_paths',
            $wpdb->prefix . 'pmp_content_bookmarks',
            $wpdb->prefix . 'pmp_test_attempts',
            $wpdb->prefix . 'pmp_question_options',
            $wpdb->prefix . 'pmp_test_questions',
            $wpdb->prefix . 'pmp_content_sequences'
        ];
        
        foreach ($tables as $table) {
            $wpdb->query("DROP TABLE IF EXISTS $table");
        }
    }
    
    /**
     * Get table statistics for monitoring
     */
    public static function get_table_stats() {
        global $wpdb;
        
        $stats = [];
        $tables = [
            'content_sequences' => $wpdb->prefix . 'pmp_content_sequences',
            'test_questions' => $wpdb->prefix . 'pmp_test_questions',
            'question_options' => $wpdb->prefix . 'pmp_question_options',
            'test_attempts' => $wpdb->prefix . 'pmp_test_attempts',
            'content_bookmarks' => $wpdb->prefix . 'pmp_content_bookmarks',
            'learning_paths' => $wpdb->prefix . 'pmp_learning_paths',
            'learning_path_content' => $wpdb->prefix . 'pmp_learning_path_content'
        ];
        
        foreach ($tables as $key => $table) {
            $count = $wpdb->get_var("SELECT COUNT(*) FROM $table");
            $stats[$key] = intval($count);
        }
        
        return $stats;
    }
}
?>
