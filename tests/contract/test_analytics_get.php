<?php
/**
 * Contract Test: GET /wp-json/pmp/v1/analytics/{user_id}
 * 
 * Tests the analytics API endpoint contract - MUST FAIL initially
 */

class Test_Analytics_Get_Contract extends WP_UnitTestCase {
    
    private $user_id;
    
    public function setUp(): void {
        parent::setUp();
        $this->user_id = $this->factory->user->create();
        wp_set_current_user($this->user_id);
    }
    
    /**
     * Test GET analytics returns correct structure
     */
    public function test_get_analytics_returns_correct_structure() {
        $request = new WP_REST_Request('GET', '/pmp/v1/analytics/' . $this->user_id);
        $response = rest_do_request($request);
        
        $this->assertEquals(200, $response->get_status());
        
        $data = $response->get_data();
        
        // Test required fields exist
        $this->assertArrayHasKey('study_sessions', $data);
        $this->assertArrayHasKey('weekly_progress', $data);
        $this->assertArrayHasKey('domain_breakdown', $data);
        $this->assertArrayHasKey('recommendations', $data);
        
        // Test study_sessions is array
        $this->assertIsArray($data['study_sessions']);
        
        // Test weekly_progress is array
        $this->assertIsArray($data['weekly_progress']);
        
        // Test domain_breakdown structure
        $this->assertArrayHasKey('people', $data['domain_breakdown']);
        $this->assertArrayHasKey('process', $data['domain_breakdown']);
        $this->assertArrayHasKey('business_environment', $data['domain_breakdown']);
        
        // Test recommendations is array
        $this->assertIsArray($data['recommendations']);
    }
    
    /**
     * Test study session structure
     */
    public function test_study_session_structure() {
        // Create a study session first
        global $wpdb;
        $wpdb->insert(
            $wpdb->prefix . 'pmp_study_sessions',
            [
                'user_id' => $this->user_id,
                'session_date' => current_time('Y-m-d'),
                'duration_minutes' => 30,
                'lessons_completed' => 2,
                'domain_focus' => 'people'
            ]
        );
        
        $request = new WP_REST_Request('GET', '/pmp/v1/analytics/' . $this->user_id);
        $response = rest_do_request($request);
        $data = $response->get_data();
        
        if (!empty($data['study_sessions'])) {
            $session = $data['study_sessions'][0];
            
            $this->assertArrayHasKey('session_date', $session);
            $this->assertArrayHasKey('duration_minutes', $session);
            $this->assertArrayHasKey('lessons_completed', $session);
            $this->assertArrayHasKey('domain_focus', $session);
        }
    }
    
    /**
     * Test period parameter validation
     */
    public function test_period_parameter_validation() {
        $valid_periods = ['week', 'month', 'all'];
        
        foreach ($valid_periods as $period) {
            $request = new WP_REST_Request('GET', '/pmp/v1/analytics/' . $this->user_id);
            $request->set_param('period', $period);
            $response = rest_do_request($request);
            $this->assertEquals(200, $response->get_status());
        }
        
        // Invalid period should default to 'month'
        $request = new WP_REST_Request('GET', '/pmp/v1/analytics/' . $this->user_id);
        $request->set_param('period', 'invalid');
        $response = rest_do_request($request);
        $this->assertEquals(400, $response->get_status());
    }
    
    /**
     * Test weekly progress structure
     */
    public function test_weekly_progress_structure() {
        $request = new WP_REST_Request('GET', '/pmp/v1/analytics/' . $this->user_id);
        $response = rest_do_request($request);
        $data = $response->get_data();
        
        foreach ($data['weekly_progress'] as $week_data) {
            $this->assertArrayHasKey('week_start', $week_data);
            $this->assertArrayHasKey('lessons_completed', $week_data);
            $this->assertArrayHasKey('time_spent', $week_data);
            
            $this->assertIsString($week_data['week_start']);
            $this->assertIsNumeric($week_data['lessons_completed']);
            $this->assertIsNumeric($week_data['time_spent']);
        }
    }
    
    /**
     * Test unauthorized access
     */
    public function test_unauthorized_access() {
        wp_set_current_user(0); // Logout
        
        $request = new WP_REST_Request('GET', '/pmp/v1/analytics/' . $this->user_id);
        $response = rest_do_request($request);
        
        $this->assertEquals(403, $response->get_status());
    }
    
    /**
     * Test non-existent user
     */
    public function test_nonexistent_user() {
        $request = new WP_REST_Request('GET', '/pmp/v1/analytics/99999');
        $response = rest_do_request($request);
        
        $this->assertEquals(404, $response->get_status());
    }
}
?>
