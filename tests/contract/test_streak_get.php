<?php
/**
 * Contract Test: GET /wp-json/pmp/v1/streak/{user_id}
 * 
 * Tests the streak API endpoint contract - MUST FAIL initially
 */

class Test_Streak_Get_Contract extends WP_UnitTestCase {
    
    private $user_id;
    
    public function setUp(): void {
        parent::setUp();
        $this->user_id = $this->factory->user->create();
        wp_set_current_user($this->user_id);
    }
    
    /**
     * Test GET streak returns correct structure
     */
    public function test_get_streak_returns_correct_structure() {
        $request = new WP_REST_Request('GET', '/pmp/v1/streak/' . $this->user_id);
        $response = rest_do_request($request);
        
        $this->assertEquals(200, $response->get_status());
        
        $data = $response->get_data();
        
        // Test required fields exist
        $this->assertArrayHasKey('current_streak', $data);
        $this->assertArrayHasKey('longest_streak', $data);
        $this->assertArrayHasKey('last_study_date', $data);
        $this->assertArrayHasKey('total_study_days', $data);
        $this->assertArrayHasKey('streak_message', $data);
        $this->assertArrayHasKey('next_milestone', $data);
        
        // Test data types
        $this->assertIsInt($data['current_streak']);
        $this->assertIsInt($data['longest_streak']);
        $this->assertIsInt($data['total_study_days']);
        $this->assertIsString($data['streak_message']);
        $this->assertIsInt($data['next_milestone']);
        
        // Test value constraints
        $this->assertGreaterThanOrEqual(0, $data['current_streak']);
        $this->assertGreaterThanOrEqual(0, $data['longest_streak']);
        $this->assertGreaterThanOrEqual(0, $data['total_study_days']);
        $this->assertGreaterThanOrEqual($data['current_streak'], $data['longest_streak']);
    }
    
    /**
     * Test streak data for new user
     */
    public function test_new_user_streak_data() {
        $request = new WP_REST_Request('GET', '/pmp/v1/streak/' . $this->user_id);
        $response = rest_do_request($request);
        $data = $response->get_data();
        
        // New user should have zero streaks
        $this->assertEquals(0, $data['current_streak']);
        $this->assertEquals(0, $data['longest_streak']);
        $this->assertEquals(0, $data['total_study_days']);
        $this->assertNull($data['last_study_date']);
        
        // Should have motivational message
        $this->assertStringContainsString('Start', $data['streak_message']);
        
        // Should have first milestone
        $this->assertEquals(7, $data['next_milestone']);
    }
    
    /**
     * Test streak data with existing streak
     */
    public function test_existing_streak_data() {
        // Create streak data
        global $wpdb;
        $wpdb->insert(
            $wpdb->prefix . 'pmp_study_streaks',
            [
                'user_id' => $this->user_id,
                'current_streak' => 5,
                'longest_streak' => 10,
                'last_study_date' => current_time('Y-m-d'),
                'total_study_days' => 15
            ]
        );
        
        $request = new WP_REST_Request('GET', '/pmp/v1/streak/' . $this->user_id);
        $response = rest_do_request($request);
        $data = $response->get_data();
        
        $this->assertEquals(5, $data['current_streak']);
        $this->assertEquals(10, $data['longest_streak']);
        $this->assertEquals(15, $data['total_study_days']);
        $this->assertEquals(current_time('Y-m-d'), $data['last_study_date']);
    }
    
    /**
     * Test streak message variations
     */
    public function test_streak_message_variations() {
        $streak_scenarios = [
            0 => 'Start',
            1 => 'Great start',
            5 => 'streak',
            10 => 'Amazing',
            35 => 'Incredible'
        ];
        
        foreach ($streak_scenarios as $streak => $expected_word) {
            // Update streak data
            global $wpdb;
            $wpdb->replace(
                $wpdb->prefix . 'pmp_study_streaks',
                [
                    'user_id' => $this->user_id,
                    'current_streak' => $streak,
                    'longest_streak' => $streak,
                    'total_study_days' => $streak
                ]
            );
            
            $request = new WP_REST_Request('GET', '/pmp/v1/streak/' . $this->user_id);
            $response = rest_do_request($request);
            $data = $response->get_data();
            
            $this->assertStringContainsString($expected_word, $data['streak_message']);
        }
    }
    
    /**
     * Test next milestone calculation
     */
    public function test_next_milestone_calculation() {
        $milestone_tests = [
            0 => 7,
            5 => 7,
            7 => 14,
            15 => 30,
            35 => 60,
            65 => 100
        ];
        
        foreach ($milestone_tests as $streak => $expected_milestone) {
            global $wpdb;
            $wpdb->replace(
                $wpdb->prefix . 'pmp_study_streaks',
                [
                    'user_id' => $this->user_id,
                    'current_streak' => $streak,
                    'longest_streak' => $streak,
                    'total_study_days' => $streak
                ]
            );
            
            $request = new WP_REST_Request('GET', '/pmp/v1/streak/' . $this->user_id);
            $response = rest_do_request($request);
            $data = $response->get_data();
            
            $this->assertEquals($expected_milestone, $data['next_milestone']);
        }
    }
    
    /**
     * Test unauthorized access
     */
    public function test_unauthorized_access() {
        wp_set_current_user(0); // Logout
        
        $request = new WP_REST_Request('GET', '/pmp/v1/streak/' . $this->user_id);
        $response = rest_do_request($request);
        
        $this->assertEquals(403, $response->get_status());
    }
    
    /**
     * Test non-existent user
     */
    public function test_nonexistent_user() {
        $request = new WP_REST_Request('GET', '/pmp/v1/streak/99999');
        $response = rest_do_request($request);
        
        $this->assertEquals(404, $response->get_status());
    }
}
?>
