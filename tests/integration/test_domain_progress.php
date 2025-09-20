<?php
/**
 * Integration Test: Domain Progress Calculation
 * 
 * Tests the complete domain progress calculation workflow - MUST FAIL initially
 */

class Test_Domain_Progress_Integration extends WP_UnitTestCase {
    
    private $user_id;
    private $people_lessons = [];
    private $process_lessons = [];
    private $business_lessons = [];
    
    public function setUp(): void {
        parent::setUp();
        $this->user_id = $this->factory->user->create();
        
        // Create test lessons for each domain
        for ($i = 0; $i < 5; $i++) {
            $this->people_lessons[] = $this->factory->post->create([
                'post_type' => 'lesson',
                'post_status' => 'publish',
                'meta_input' => ['pmp_domain' => 'people']
            ]);
            
            $this->process_lessons[] = $this->factory->post->create([
                'post_type' => 'lesson', 
                'post_status' => 'publish',
                'meta_input' => ['pmp_domain' => 'process']
            ]);
        }
        
        for ($i = 0; $i < 2; $i++) {
            $this->business_lessons[] = $this->factory->post->create([
                'post_type' => 'lesson',
                'post_status' => 'publish', 
                'meta_input' => ['pmp_domain' => 'business_environment']
            ]);
        }
    }
    
    /**
     * Test domain progress calculation after lesson completion
     */
    public function test_domain_progress_calculation() {
        // Complete 2 out of 5 people domain lessons
        PMP_Progress_Tracker::update_lesson_progress($this->user_id, $this->people_lessons[0], 'completed', 100, 20);
        PMP_Progress_Tracker::update_lesson_progress($this->user_id, $this->people_lessons[1], 'completed', 100, 25);
        
        $progress = PMP_Progress_Tracker::get_user_progress($this->user_id);
        
        // Should be 40% completion for people domain (2/5 lessons)
        $this->assertEquals(40.0, $progress['domains']['people']['completion_percentage']);
        $this->assertEquals(2, $progress['domains']['people']['lessons_completed']);
        $this->assertEquals(45, $progress['domains']['people']['time_spent_minutes']);
    }
    
    /**
     * Test overall progress calculation with domain weights
     */
    public function test_overall_progress_calculation() {
        // Complete all people lessons (42% weight)
        foreach ($this->people_lessons as $lesson_id) {
            PMP_Progress_Tracker::update_lesson_progress($this->user_id, $lesson_id, 'completed', 100, 20);
        }
        
        // Complete half of process lessons (50% weight)
        for ($i = 0; $i < 3; $i++) {
            PMP_Progress_Tracker::update_lesson_progress($this->user_id, $this->process_lessons[$i], 'completed', 100, 20);
        }
        
        // Complete all business lessons (8% weight)
        foreach ($this->business_lessons as $lesson_id) {
            PMP_Progress_Tracker::update_lesson_progress($this->user_id, $lesson_id, 'completed', 100, 15);
        }
        
        $progress = PMP_Progress_Tracker::get_user_progress($this->user_id);
        
        // People: 100% * 0.42 = 42
        // Process: 60% * 0.50 = 30  
        // Business: 100% * 0.08 = 8
        // Total: 42 + 30 + 8 = 80%
        $expected_overall = (100 * 0.42) + (60 * 0.50) + (100 * 0.08);
        $this->assertEquals($expected_overall, $progress['overall_progress']);
    }
    
    /**
     * Test progress recalculation when lesson status changes
     */
    public function test_progress_recalculation() {
        // Initially complete a lesson
        PMP_Progress_Tracker::update_lesson_progress($this->user_id, $this->people_lessons[0], 'completed', 100, 20);
        
        $progress1 = PMP_Progress_Tracker::get_user_progress($this->user_id);
        $this->assertEquals(20.0, $progress1['domains']['people']['completion_percentage']); // 1/5 = 20%
        
        // Complete another lesson
        PMP_Progress_Tracker::update_lesson_progress($this->user_id, $this->people_lessons[1], 'completed', 100, 25);
        
        $progress2 = PMP_Progress_Tracker::get_user_progress($this->user_id);
        $this->assertEquals(40.0, $progress2['domains']['people']['completion_percentage']); // 2/5 = 40%
        
        // Change first lesson back to in_progress
        PMP_Progress_Tracker::update_lesson_progress($this->user_id, $this->people_lessons[0], 'in_progress', 50, 5);
        
        $progress3 = PMP_Progress_Tracker::get_user_progress($this->user_id);
        $this->assertEquals(20.0, $progress3['domains']['people']['completion_percentage']); // 1/5 = 20%
    }
    
    /**
     * Test time tracking across domain
     */
    public function test_time_tracking_across_domain() {
        // Complete lessons with different time spent
        PMP_Progress_Tracker::update_lesson_progress($this->user_id, $this->people_lessons[0], 'completed', 100, 20);
        PMP_Progress_Tracker::update_lesson_progress($this->user_id, $this->people_lessons[1], 'completed', 100, 30);
        PMP_Progress_Tracker::update_lesson_progress($this->user_id, $this->people_lessons[2], 'in_progress', 75, 15);
        
        $progress = PMP_Progress_Tracker::get_user_progress($this->user_id);
        
        // Total time should be 20 + 30 + 15 = 65 minutes
        $this->assertEquals(65, $progress['domains']['people']['time_spent_minutes']);
        
        // Only completed lessons count for completion percentage (2/5 = 40%)
        $this->assertEquals(40.0, $progress['domains']['people']['completion_percentage']);
        $this->assertEquals(2, $progress['domains']['people']['lessons_completed']);
    }
    
    /**
     * Test multiple users don't interfere with each other
     */
    public function test_multiple_users_isolation() {
        $user2_id = $this->factory->user->create();
        
        // User 1 completes 2 lessons
        PMP_Progress_Tracker::update_lesson_progress($this->user_id, $this->people_lessons[0], 'completed', 100, 20);
        PMP_Progress_Tracker::update_lesson_progress($this->user_id, $this->people_lessons[1], 'completed', 100, 25);
        
        // User 2 completes 3 lessons
        PMP_Progress_Tracker::update_lesson_progress($user2_id, $this->people_lessons[0], 'completed', 100, 15);
        PMP_Progress_Tracker::update_lesson_progress($user2_id, $this->people_lessons[1], 'completed', 100, 20);
        PMP_Progress_Tracker::update_lesson_progress($user2_id, $this->people_lessons[2], 'completed', 100, 18);
        
        $progress1 = PMP_Progress_Tracker::get_user_progress($this->user_id);
        $progress2 = PMP_Progress_Tracker::get_user_progress($user2_id);
        
        $this->assertEquals(40.0, $progress1['domains']['people']['completion_percentage']); // 2/5
        $this->assertEquals(60.0, $progress2['domains']['people']['completion_percentage']); // 3/5
        
        $this->assertEquals(45, $progress1['domains']['people']['time_spent_minutes']); // 20+25
        $this->assertEquals(53, $progress2['domains']['people']['time_spent_minutes']); // 15+20+18
    }
}
?>
