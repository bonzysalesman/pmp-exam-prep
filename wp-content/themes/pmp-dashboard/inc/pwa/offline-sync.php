<?php
/**
 * Offline Synchronization Manager
 * Handles background sync for offline content updates
 */

class PMP_Offline_Sync {
    
    public function __construct() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_sync_scripts'));
        add_action('wp_ajax_sync_offline_data', array($this, 'handle_sync_request'));
        add_action('wp_ajax_nopriv_sync_offline_data', array($this, 'handle_sync_request'));
    }
    
    public function enqueue_sync_scripts() {
        wp_enqueue_script(
            'pmp-offline-sync',
            get_template_directory_uri() . '/assets/js/offline-sync.js',
            array(),
            '1.0.0',
            true
        );
        
        wp_localize_script('pmp-offline-sync', 'pmpSync', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('pmp_sync_nonce'),
            'syncEndpoints' => $this->get_sync_endpoints()
        ));
    }
    
    private function get_sync_endpoints() {
        return array(
            'lessons' => rest_url('pmp/v1/lessons'),
            'progress' => rest_url('pmp/v1/progress'),
            'bookmarks' => rest_url('pmp/v1/bookmarks'),
            'tests' => rest_url('pmp/v1/tests')
        );
    }
    
    public function handle_sync_request() {
        check_ajax_referer('pmp_sync_nonce', 'nonce');
        
        $sync_type = sanitize_text_field($_POST['sync_type']);
        $last_sync = sanitize_text_field($_POST['last_sync']);
        
        switch($sync_type) {
            case 'lessons':
                $data = $this->sync_lessons($last_sync);
                break;
            case 'progress':
                $data = $this->sync_progress($last_sync);
                break;
            default:
                wp_die('Invalid sync type');
        }
        
        wp_send_json_success($data);
    }
    
    private function sync_lessons($last_sync) {
        $args = array(
            'post_type' => 'lesson',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => 'offline_enabled',
                    'value' => '1'
                )
            )
        );
        
        if ($last_sync) {
            $args['date_query'] = array(
                array(
                    'after' => $last_sync,
                    'column' => 'post_modified'
                )
            );
        }
        
        $lessons = get_posts($args);
        $sync_data = array();
        
        foreach ($lessons as $lesson) {
            $sync_data[] = array(
                'id' => $lesson->ID,
                'title' => $lesson->post_title,
                'content' => $lesson->post_content,
                'modified' => $lesson->post_modified,
                'meta' => get_post_meta($lesson->ID)
            );
        }
        
        return $sync_data;
    }
    
    private function sync_progress($last_sync) {
        if (!is_user_logged_in()) {
            return array();
        }
        
        global $wpdb;
        $user_id = get_current_user_id();
        
        $query = "SELECT * FROM {$wpdb->prefix}pmp_progress 
                  WHERE user_id = %d";
        
        if ($last_sync) {
            $query .= " AND updated_at > %s";
            $results = $wpdb->get_results(
                $wpdb->prepare($query, $user_id, $last_sync)
            );
        } else {
            $results = $wpdb->get_results(
                $wpdb->prepare($query, $user_id)
            );
        }
        
        return $results;
    }
}

new PMP_Offline_Sync();
