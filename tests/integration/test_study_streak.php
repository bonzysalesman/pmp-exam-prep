<?php
/**
 * Integration Test: Study Streak Tracking
 * 
 * Tests the complete study streak tracking workflow - MUST FAIL initially
 */

class Test_Study_Streak_Integration extends WP_UnitTestCase {
    
    private $user_id;
    private $lesson_id;
    
    public function setUp(): void {
        parent::setUp();
        $this->user_id = $this->factory->user->create();
        $this->lesson_id = $this->factory->post->create([
            'post_type' => 'lesson',
            'post_status' => 'publish',
            'meta_input' => ['pmp_domain' => 'people']
        ]);
    }
    
    /**
     * Test streak initialization for new user
     */
    public function test_streak_initialization() {
        // Complete first lesson
        PMP_Progress_Tracker::update_lesson_progress($this->user_id, $this->lesson_id, 'completed', 100, 20);
        
        $streak = PMP_Progress_Tracker::get_study_streak($this->user_id);
        
        $this->assertEquals(1, $streak['current_streak']);
        $this->assertEquals(1, $streak['longest_streak']);
        $this->assertEquals(1, $streak['total_study_days']);
        $this->assertEquals(current_time('Y-m-d'), $streak['last_study_date']);
    }
    
    /**
     * Test consecutive day streak building
     */
    public function test_consecutive_streak_building() {
        // Simulate studying for 5 consecutive days
        for ($i = 0; $i < 5; $i++) {
            $date = date('Y-m-d', strtotime("-$i days"));
            
            // Mock the current date
            add_filter('current_time', function($format) use ($date) {
                if ($format === 'Y-m-d') return $date;
                return current_time($format);
            });
            
            // Create study session for this date
            global $wpdb;
            $wpdb->replace(
                $wpdb->prefix . 'pmp_study_sessions',
                [
                    'user_id' => $this->user_id,
                    'session_date' => $date,
                    'duration_minutes' => 20,
                    'lessons_completed' => 1,
                    'domain_focus' => 'people'
                ]
            );
            
            // Update streak
            PMP_Progress_Tracker::update_study_streak($this->user_id);
            
            remove_all_filters('current_time');
        }
        
        $streak = PMP_Progress_Tracker::get_study_streak($this->user_id);
        
        $this->assertEquals(5, $streak['current_streak']);
        $this->assertEquals(5, $streak['longest_streak']);
        $this->assertEquals(5, $streak['total_study_days']);
    }
    
    /**
     * Test streak reset after gap
     */
    public function test_streak_reset_after_gap() {
        // Build initial streak of 3 days
        for ($i = 2; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            
            global $wpdb;
            $wpdb->replace(
                $wpdb->prefix . 'pmp_study_sessions',
                [
                    'user_id' => $this->user_id,
                    'session_date' => $date,
                    'duration_minutes' => 20,
                    'lessons_completed' => 1,
                    'domain_focus' => 'people'
                ]
            );
        }
        
        // Update streak to 3
        $wpdb->replace(
            $wpdb->prefix . 'pmp_study_streaks',
            [
                'user_id' => $this->user_id,
                'current_streak' => 3,
                'longest_streak' => 3,
                'last_study_date' => date('Y-m-d'),
                'total_study_days' => 3
            ]
        );
        
        // Skip 2 days and study again (should reset streak to 1)
        $future_date = date('Y-m-d', strtotime('+3 days'));
        
        add_filter('current_time', function($format) use ($future_date) {
            if ($format === 'Y-m-d') return $future_date;
            return current_time($format);
        });
        
        PMP_Progress_Tracker::update_study_streak($this->user_id);
        
        remove_all_filters('current_time');
        
        $streak = PMP_Progress_Tracker::get_study_streak($this->user_id);
        
        $this->assertEquals(1, $streak['current_streak']); // Reset to 1
        $this->assertEquals(3, $streak['longest_streak']); // Longest remains 3
        $this->assertEquals(4, $streak['total_study_days']); // Total incremented
    }
    
    /**
     * Test longest streak tracking
     */
    public function test_longest_streak_tracking() {
        global $wpdb;
        
        // Set initial streak data: current=5, longest=7
        $wpdb->replace(
            $wpdb->prefix . 'pmp_study_streaks',
            [
                'user_id' => $this->user_id,
                'current_streak' => 5,
                'longest_streak' => 7,
                'last_study_date' => date('Y-m-d', strtotime('-1 day')),
                'total_study_days' => 10
            ]
        );
        
        // Study today (should increment current to 6)
        PMP_Progress_Tracker::update_study_streak($this->user_id);
        
        $streak = PMP_Progress_Tracker::get_study_streak($this->user_id);
        $this->assertEquals(6, $streak['current_streak']);
        $this->assertEquals(7, $streak['longest_streak']); // Still 7
        
        // Study tomorrow (should increment current to 7, equal to longest)
        add_filter('current_time', function($format) {
            if ($format === 'Y-m-d') return date('Y-m-d', strtotime('+1 day'));
            return current_time($format);
        });
        
        PMP_Progress_Tracker::update_study_streak($this->user_id);
        
        remove_all_filters('current_time');
        
        $streak = PMP_Progress_Tracker::get_study_streak($this->user_id);
        $this->assertEquals(7, $streak['current_streak']);
        $this->assertEquals(7, $streak['longest_streak']); // Still 7
        
        // Study day after (should increment current to 8, new longest)
        add_filter('current_time', function($format) {
            if ($format === 'Y-m-d') return date('Y-m-d', strtotime('+2 days'));
            return current_time($format);
        });
        
        PMP_Progress_Tracker::update_study_streak($this->user_id);
        
        remove_all_filters('current_time');
        
        $streak = PMP_Progress_Tracker::get_study_streak($this->user_id);
        $this->assertEquals(8, $streak['current_streak']);
        $this->assertEquals(8, $streak['longest_streak']); // New record!
    }
    
    /**
     * Test study session creation triggers streak update
     */
    public function test_lesson_completion_triggers_streak() {
        // Complete a lesson (should create session and update streak)
        PMP_Progress_Tracker::update_lesson_progress($this->user_id, $this->lesson_id, 'completed', 100, 25);
        
        // Check that study session was created
        global $wpdb;
        $session = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}pmp_study_sessions WHERE user_id = %d AND session_date = %s",
            $this->user_id, current_time('Y-m-d')
        ));
        
        $this->assertNotNull($session);
        $this->assertEquals(25, $session->duration_minutes);
        $this->assertEquals(1, $session->lessons_completed);
        
        // Check that streak was updated
        $streak = PMP_Progress_Tracker::get_study_streak($this->user_id);
        $this->assertEquals(1, $streak['current_streak']);
    }
    
    /**
     * Test multiple lessons same day don't create multiple streaks
     */
    public function test_multiple_lessons_same_day() {
        $lesson2_id = $this->factory->post->create([
            'post_type' => 'lesson',
            'post_status' => 'publish',
            'meta_input' => ['pmp_domain' => 'process']
        ]);
        
        // Complete two lessons on same day
        PMP_Progress_Tracker::update_lesson_progress($this->user_id, $this->lesson_id, 'completed', 100, 20);
        PMP_Progress_Tracker::update_lesson_progress($this->user_id, $lesson2_id, 'completed', 100, 30);
        
        // Should have one session with combined data
        global $wpdb;
        $session = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}pmp_study_sessions WHERE user_id = %d AND session_date = %s",
            $this->user_id, current_time('Y-m-d')
        ));
        
        $this->assertEquals(50, $session->duration_minutes); // 20 + 30
        $this->assertEquals(2, $session->lessons_completed);
        
        // Should still have streak of 1
        $streak = PMP_Progress_Tracker::get_study_streak($this->user_id);
        $this->assertEquals(1, $streak['current_streak']);
        $this->assertEquals(1, $streak['total_study_days']);
    }
}
?>
