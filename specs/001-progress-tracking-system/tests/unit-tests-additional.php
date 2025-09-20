<?php
/**
 * Additional Unit Tests for Progress Tracking System
 * T031: Edge Cases and Error Handling
 */

class PMP_Progress_Additional_Tests extends WP_UnitTestCase {
    
    private $user_id;
    private $lesson_id;
    
    public function setUp(): void {
        parent::setUp();
        
        // Create test user and lesson
        $this->user_id = $this->factory->user->create();
        $this->lesson_id = $this->factory->post->create([
            'post_type' => 'lesson',
            'post_status' => 'publish'
        ]);
        
        // Initialize database tables
        PMP_Progress_Database::create_tables();
    }
    
    /**
     * Test invalid user ID handling
     */
    public function test_invalid_user_id() {
        $result = PMP_Progress_Tracker::update_lesson_progress(
            999999, // Invalid user ID
            $this->lesson_id,
            'completed',
            100,
            30
        );
        
        $this->assertInstanceOf('WP_Error', $result);
        $this->assertEquals('invalid_user', $result->get_error_code());
    }
    
    /**
     * Test invalid lesson ID handling
     */
    public function test_invalid_lesson_id() {
        $result = PMP_Progress_Tracker::update_lesson_progress(
            $this->user_id,
            999999, // Invalid lesson ID
            'completed',
            100,
            30
        );
        
        $this->assertInstanceOf('WP_Error', $result);
        $this->assertEquals('invalid_lesson', $result->get_error_code());
    }
    
    /**
     * Test invalid progress percentage
     */
    public function test_invalid_progress_percentage() {
        $result = PMP_Progress_Tracker::update_lesson_progress(
            $this->user_id,
            $this->lesson_id,
            'in_progress',
            150, // Invalid percentage > 100
            30
        );
        
        $this->assertInstanceOf('WP_Error', $result);
        $this->assertEquals('invalid_percentage', $result->get_error_code());
    }
    
    /**
     * Test negative time spent
     */
    public function test_negative_time_spent() {
        $result = PMP_Progress_Tracker::update_lesson_progress(
            $this->user_id,
            $this->lesson_id,
            'in_progress',
            50,
            -10 // Negative time
        );
        
        $this->assertInstanceOf('WP_Error', $result);
        $this->assertEquals('invalid_time', $result->get_error_code());
    }
    
    /**
     * Test concurrent progress updates
     */
    public function test_concurrent_updates() {
        // First update
        $result1 = PMP_Progress_Tracker::update_lesson_progress(
            $this->user_id,
            $this->lesson_id,
            'in_progress',
            50,
            15
        );
        
        // Second update (should update existing record)
        $result2 = PMP_Progress_Tracker::update_lesson_progress(
            $this->user_id,
            $this->lesson_id,
            'completed',
            100,
            30
        );
        
        $this->assertTrue($result1);
        $this->assertTrue($result2);
        
        // Verify only one record exists
        global $wpdb;
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}pmp_lesson_progress 
             WHERE user_id = %d AND lesson_id = %d",
            $this->user_id,
            $this->lesson_id
        ));
        
        $this->assertEquals(1, $count);
    }
    
    /**
     * Test streak calculation with gaps
     */
    public function test_streak_with_gaps() {
        // Create progress entries with gaps
        global $wpdb;
        
        $dates = [
            date('Y-m-d', strtotime('-5 days')),
            date('Y-m-d', strtotime('-4 days')),
            date('Y-m-d', strtotime('-3 days')),
            // Gap on -2 days
            date('Y-m-d', strtotime('-1 day')),
            date('Y-m-d')
        ];
        
        foreach ($dates as $i => $date) {
            if ($i === 3) continue; // Skip to create gap
            
            $wpdb->insert(
                $wpdb->prefix . 'pmp_study_sessions',
                [
                    'user_id' => $this->user_id,
                    'session_date' => $date,
                    'duration_minutes' => 30,
                    'lessons_completed' => 1,
                    'domain_focus' => 'people'
                ]
            );
        }
        
        $streak_data = PMP_Progress_Tracker::get_study_streak($this->user_id);
        
        // Should only count consecutive days from today
        $this->assertEquals(2, $streak_data['current_streak']);
    }
    
    /**
     * Test domain progress with no lessons
     */
    public function test_domain_progress_no_lessons() {
        $progress = PMP_Progress_Tracker::get_user_progress($this->user_id);
        
        $this->assertEquals(0, $progress['overall_progress']);
        $this->assertEquals(0, $progress['total_lessons_completed']);
        
        foreach ($progress['domains'] as $domain) {
            $this->assertEquals(0, $domain['completion_percentage']);
            $this->assertEquals(0, $domain['lessons_completed']);
        }
    }
    
    /**
     * Test API endpoint security
     */
    public function test_api_security_without_nonce() {
        // Simulate REST request without proper nonce
        $request = new WP_REST_Request('GET', '/pmp/v1/progress/' . $this->user_id);
        
        $api = new PMP_Progress_API();
        $response = $api->get_user_progress($request);
        
        // Should require authentication
        $this->assertInstanceOf('WP_Error', $response);
    }
    
    /**
     * Test database table creation
     */
    public function test_database_tables_exist() {
        global $wpdb;
        
        $tables = [
            $wpdb->prefix . 'pmp_lesson_progress',
            $wpdb->prefix . 'pmp_domain_progress', 
            $wpdb->prefix . 'pmp_study_sessions',
            $wpdb->prefix . 'pmp_study_streaks'
        ];
        
        foreach ($tables as $table) {
            $exists = $wpdb->get_var("SHOW TABLES LIKE '$table'");
            $this->assertEquals($table, $exists, "Table $table should exist");
        }
    }
    
    /**
     * Test memory usage with large datasets
     */
    public function test_memory_usage_large_dataset() {
        $memory_start = memory_get_usage();
        
        // Create 100 progress entries
        for ($i = 0; $i < 100; $i++) {
            $lesson_id = $this->factory->post->create([
                'post_type' => 'lesson',
                'post_status' => 'publish'
            ]);
            
            PMP_Progress_Tracker::update_lesson_progress(
                $this->user_id,
                $lesson_id,
                'completed',
                100,
                rand(10, 60)
            );
        }
        
        $progress = PMP_Progress_Tracker::get_user_progress($this->user_id);
        
        $memory_end = memory_get_usage();
        $memory_used = $memory_end - $memory_start;
        
        // Should use less than 5MB for 100 records
        $this->assertLessThan(5 * 1024 * 1024, $memory_used);
        $this->assertNotEmpty($progress);
    }
}
?>
