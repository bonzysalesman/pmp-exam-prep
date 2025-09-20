<?php
/**
 * Contract Test: POST /wp-json/pmp/v1/progress/lesson
 * 
 * Tests the lesson update API endpoint contract - MUST FAIL initially
 */

class Test_Lesson_Update_Contract extends WP_UnitTestCase {
    
    private $user_id;
    private $lesson_id;
    
    public function setUp(): void {
        parent::setUp();
        $this->user_id = $this->factory->user->create();
        $this->lesson_id = $this->factory->post->create([
            'post_type' => 'lesson',
            'post_status' => 'publish'
        ]);
        wp_set_current_user($this->user_id);
    }
    
    /**
     * Test POST lesson update returns success response
     */
    public function test_lesson_update_returns_success() {
        $request = new WP_REST_Request('POST', '/pmp/v1/progress/lesson');
        $request->set_param('lesson_id', $this->lesson_id);
        $request->set_param('status', 'completed');
        $request->set_param('progress_percentage', 100);
        $request->set_param('time_spent_minutes', 25);
        
        $response = rest_do_request($request);
        
        $this->assertEquals(200, $response->get_status());
        
        $data = $response->get_data();
        
        // Test response structure
        $this->assertArrayHasKey('success', $data);
        $this->assertArrayHasKey('message', $data);
        $this->assertArrayHasKey('updated_progress', $data);
        
        $this->assertTrue($data['success']);
        $this->assertIsString($data['message']);
    }
    
    /**
     * Test required parameters validation
     */
    public function test_required_parameters_validation() {
        // Missing lesson_id
        $request = new WP_REST_Request('POST', '/pmp/v1/progress/lesson');
        $request->set_param('status', 'completed');
        $response = rest_do_request($request);
        $this->assertEquals(400, $response->get_status());
        
        // Missing status
        $request = new WP_REST_Request('POST', '/pmp/v1/progress/lesson');
        $request->set_param('lesson_id', $this->lesson_id);
        $response = rest_do_request($request);
        $this->assertEquals(400, $response->get_status());
    }
    
    /**
     * Test status validation
     */
    public function test_status_validation() {
        $valid_statuses = ['not_started', 'in_progress', 'completed'];
        
        foreach ($valid_statuses as $status) {
            $request = new WP_REST_Request('POST', '/pmp/v1/progress/lesson');
            $request->set_param('lesson_id', $this->lesson_id);
            $request->set_param('status', $status);
            $response = rest_do_request($request);
            $this->assertEquals(200, $response->get_status());
        }
        
        // Invalid status
        $request = new WP_REST_Request('POST', '/pmp/v1/progress/lesson');
        $request->set_param('lesson_id', $this->lesson_id);
        $request->set_param('status', 'invalid_status');
        $response = rest_do_request($request);
        $this->assertEquals(400, $response->get_status());
    }
    
    /**
     * Test progress percentage validation
     */
    public function test_progress_percentage_validation() {
        // Valid percentage
        $request = new WP_REST_Request('POST', '/pmp/v1/progress/lesson');
        $request->set_param('lesson_id', $this->lesson_id);
        $request->set_param('status', 'in_progress');
        $request->set_param('progress_percentage', 50);
        $response = rest_do_request($request);
        $this->assertEquals(200, $response->get_status());
        
        // Invalid percentage (over 100)
        $request = new WP_REST_Request('POST', '/pmp/v1/progress/lesson');
        $request->set_param('lesson_id', $this->lesson_id);
        $request->set_param('status', 'in_progress');
        $request->set_param('progress_percentage', 150);
        $response = rest_do_request($request);
        $this->assertEquals(400, $response->get_status());
        
        // Invalid percentage (negative)
        $request = new WP_REST_Request('POST', '/pmp/v1/progress/lesson');
        $request->set_param('lesson_id', $this->lesson_id);
        $request->set_param('status', 'in_progress');
        $request->set_param('progress_percentage', -10);
        $response = rest_do_request($request);
        $this->assertEquals(400, $response->get_status());
    }
    
    /**
     * Test unauthorized access
     */
    public function test_unauthorized_access() {
        wp_set_current_user(0); // Logout
        
        $request = new WP_REST_Request('POST', '/pmp/v1/progress/lesson');
        $request->set_param('lesson_id', $this->lesson_id);
        $request->set_param('status', 'completed');
        $response = rest_do_request($request);
        
        $this->assertEquals(403, $response->get_status());
    }
    
    /**
     * Test non-existent lesson
     */
    public function test_nonexistent_lesson() {
        $request = new WP_REST_Request('POST', '/pmp/v1/progress/lesson');
        $request->set_param('lesson_id', 99999);
        $request->set_param('status', 'completed');
        $response = rest_do_request($request);
        
        $this->assertEquals(404, $response->get_status());
    }
}
?>
