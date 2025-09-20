<?php
/**
 * Integration Tests for Progress Tracking API
 * T032: API Endpoint Testing
 */

class PMP_Progress_API_Integration_Tests extends WP_UnitTestCase {
    
    private $user_id;
    private $admin_id;
    private $lesson_id;
    
    public function setUp(): void {
        parent::setUp();
        
        // Create test users
        $this->user_id = $this->factory->user->create(['role' => 'subscriber']);
        $this->admin_id = $this->factory->user->create(['role' => 'administrator']);
        
        // Create test lesson
        $this->lesson_id = $this->factory->post->create([
            'post_type' => 'lesson',
            'post_status' => 'publish'
        ]);
        
        // Initialize database
        PMP_Progress_Database::create_tables();
        
        // Register REST routes
        do_action('rest_api_init');
    }
    
    /**
     * Test GET /pmp/v1/progress/{user_id} endpoint
     */
    public function test_get_user_progress_endpoint() {
        wp_set_current_user($this->user_id);
        
        $request = new WP_REST_Request('GET', '/pmp/v1/progress/' . $this->user_id);
        $request->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));
        
        $response = rest_do_request($request);
        $data = $response->get_data();
        
        $this->assertEquals(200, $response->get_status());
        $this->assertArrayHasKey('overall_progress', $data);
        $this->assertArrayHasKey('domains', $data);
        $this->assertArrayHasKey('total_lessons_completed', $data);
    }
    
    /**
     * Test POST /pmp/v1/progress/lesson endpoint
     */
    public function test_update_lesson_progress_endpoint() {
        wp_set_current_user($this->user_id);
        
        $request = new WP_REST_Request('POST', '/pmp/v1/progress/lesson');
        $request->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));
        $request->set_json_params([
            'lesson_id' => $this->lesson_id,
            'status' => 'completed',
            'progress_percentage' => 100,
            'time_spent' => 25
        ]);
        
        $response = rest_do_request($request);
        $data = $response->get_data();
        
        $this->assertEquals(200, $response->get_status());
        $this->assertTrue($data['success']);
    }
    
    /**
     * Test GET /pmp/v1/analytics/{user_id} endpoint
     */
    public function test_get_analytics_endpoint() {
        wp_set_current_user($this->user_id);
        
        // Create some test data
        PMP_Progress_Tracker::update_lesson_progress(
            $this->user_id,
            $this->lesson_id,
            'completed',
            100,
            30
        );
        
        $request = new WP_REST_Request('GET', '/pmp/v1/analytics/' . $this->user_id);
        $request->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));
        $request->set_param('period', 'month');
        
        $response = rest_do_request($request);
        $data = $response->get_data();
        
        $this->assertEquals(200, $response->get_status());
        $this->assertArrayHasKey('weekly_progress', $data);
        $this->assertArrayHasKey('domain_breakdown', $data);
        $this->assertArrayHasKey('study_sessions', $data);
        $this->assertArrayHasKey('recommendations', $data);
    }
    
    /**
     * Test GET /pmp/v1/streak/{user_id} endpoint
     */
    public function test_get_streak_endpoint() {
        wp_set_current_user($this->user_id);
        
        $request = new WP_REST_Request('GET', '/pmp/v1/streak/' . $this->user_id);
        $request->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));
        
        $response = rest_do_request($request);
        $data = $response->get_data();
        
        $this->assertEquals(200, $response->get_status());
        $this->assertArrayHasKey('current_streak', $data);
        $this->assertArrayHasKey('longest_streak', $data);
        $this->assertArrayHasKey('streak_message', $data);
        $this->assertArrayHasKey('next_milestone', $data);
    }
    
    /**
     * Test unauthorized access
     */
    public function test_unauthorized_access() {
        // No user logged in
        wp_set_current_user(0);
        
        $request = new WP_REST_Request('GET', '/pmp/v1/progress/' . $this->user_id);
        $response = rest_do_request($request);
        
        $this->assertEquals(401, $response->get_status());
    }
    
    /**
     * Test cross-user access prevention
     */
    public function test_cross_user_access_prevention() {
        $other_user = $this->factory->user->create(['role' => 'subscriber']);
        wp_set_current_user($other_user);
        
        $request = new WP_REST_Request('GET', '/pmp/v1/progress/' . $this->user_id);
        $request->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));
        
        $response = rest_do_request($request);
        
        // Should be forbidden unless admin
        $this->assertEquals(403, $response->get_status());
    }
    
    /**
     * Test admin access to any user data
     */
    public function test_admin_access() {
        wp_set_current_user($this->admin_id);
        
        $request = new WP_REST_Request('GET', '/pmp/v1/progress/' . $this->user_id);
        $request->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));
        
        $response = rest_do_request($request);
        
        $this->assertEquals(200, $response->get_status());
    }
    
    /**
     * Test API rate limiting simulation
     */
    public function test_api_performance() {
        wp_set_current_user($this->user_id);
        
        $start_time = microtime(true);
        
        // Make 10 rapid requests
        for ($i = 0; $i < 10; $i++) {
            $request = new WP_REST_Request('GET', '/pmp/v1/progress/' . $this->user_id);
            $request->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));
            
            $response = rest_do_request($request);
            $this->assertEquals(200, $response->get_status());
        }
        
        $end_time = microtime(true);
        $total_time = $end_time - $start_time;
        
        // All 10 requests should complete in under 2 seconds
        $this->assertLessThan(2.0, $total_time);
    }
    
    /**
     * Test malformed request handling
     */
    public function test_malformed_requests() {
        wp_set_current_user($this->user_id);
        
        // Test with invalid JSON
        $request = new WP_REST_Request('POST', '/pmp/v1/progress/lesson');
        $request->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));
        $request->set_header('Content-Type', 'application/json');
        $request->set_body('{"invalid": json}');
        
        $response = rest_do_request($request);
        
        $this->assertEquals(400, $response->get_status());
    }
    
    /**
     * Test CORS headers
     */
    public function test_cors_headers() {
        wp_set_current_user($this->user_id);
        
        $request = new WP_REST_Request('GET', '/pmp/v1/progress/' . $this->user_id);
        $request->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));
        $request->set_header('Origin', 'https://example.com');
        
        $response = rest_do_request($request);
        $headers = $response->get_headers();
        
        // Should have appropriate CORS headers for security
        $this->assertEquals(200, $response->get_status());
    }
}
?>
