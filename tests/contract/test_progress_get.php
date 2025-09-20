<?php
/**
 * Contract Test: GET /wp-json/pmp/v1/progress/{user_id}
 * 
 * Tests the progress API endpoint contract - MUST FAIL initially
 */

class Test_Progress_Get_Contract extends WP_UnitTestCase {
    
    private $user_id;
    
    public function setUp(): void {
        parent::setUp();
        $this->user_id = $this->factory->user->create();
    }
    
    /**
     * Test GET progress endpoint returns correct structure
     */
    public function test_get_progress_returns_correct_structure() {
        $request = new WP_REST_Request('GET', '/pmp/v1/progress/' . $this->user_id);
        $response = rest_do_request($request);
        
        $this->assertEquals(200, $response->get_status());
        
        $data = $response->get_data();
        
        // Test required fields exist
        $this->assertArrayHasKey('user_id', $data);
        $this->assertArrayHasKey('overall_progress', $data);
        $this->assertArrayHasKey('domains', $data);
        $this->assertArrayHasKey('total_lessons_completed', $data);
        $this->assertArrayHasKey('total_study_time_minutes', $data);
        $this->assertArrayHasKey('last_updated', $data);
        
        // Test user_id is correct
        $this->assertEquals($this->user_id, $data['user_id']);
        
        // Test overall_progress is numeric between 0-100
        $this->assertIsNumeric($data['overall_progress']);
        $this->assertGreaterThanOrEqual(0, $data['overall_progress']);
        $this->assertLessThanOrEqual(100, $data['overall_progress']);
        
        // Test domains structure
        $this->assertArrayHasKey('people', $data['domains']);
        $this->assertArrayHasKey('process', $data['domains']);
        $this->assertArrayHasKey('business_environment', $data['domains']);
        
        // Test domain structure
        foreach ($data['domains'] as $domain) {
            $this->assertArrayHasKey('completion_percentage', $domain);
            $this->assertArrayHasKey('lessons_completed', $domain);
            $this->assertArrayHasKey('total_lessons', $domain);
            $this->assertArrayHasKey('time_spent_minutes', $domain);
            
            $this->assertIsNumeric($domain['completion_percentage']);
            $this->assertIsInt($domain['lessons_completed']);
            $this->assertIsInt($domain['total_lessons']);
            $this->assertIsInt($domain['time_spent_minutes']);
        }
    }
    
    /**
     * Test unauthorized access returns 403
     */
    public function test_unauthorized_access_returns_403() {
        wp_set_current_user(0); // Logout
        
        $request = new WP_REST_Request('GET', '/pmp/v1/progress/' . $this->user_id);
        $response = rest_do_request($request);
        
        $this->assertEquals(403, $response->get_status());
    }
    
    /**
     * Test non-existent user returns 404
     */
    public function test_nonexistent_user_returns_404() {
        wp_set_current_user($this->user_id);
        
        $request = new WP_REST_Request('GET', '/pmp/v1/progress/99999');
        $response = rest_do_request($request);
        
        $this->assertEquals(404, $response->get_status());
    }
    
    /**
     * Test invalid user_id returns error
     */
    public function test_invalid_user_id_returns_error() {
        wp_set_current_user($this->user_id);
        
        $request = new WP_REST_Request('GET', '/pmp/v1/progress/invalid');
        $response = rest_do_request($request);
        
        $this->assertNotEquals(200, $response->get_status());
    }
}
?>
