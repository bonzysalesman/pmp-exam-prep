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
        
        // Read SQL file and execute
        $sql_file = get_template_directory() . '/database-content-setup.sql';
        
        if (file_exists($sql_file)) {
            $sql = file_get_contents($sql_file);
            
            // Replace table prefix placeholder
            $sql = str_replace('pmp_', $wpdb->prefix . 'pmp_', $sql);
            
            // Split into individual queries
            $queries = array_filter(array_map('trim', explode(';', $sql)));
            
            foreach ($queries as $query) {
                if (!empty($query) && !str_starts_with($query, '--')) {
                    $wpdb->query($query);
                }
            }
        }
        
        // Verify tables were created
        self::verify_tables();
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
