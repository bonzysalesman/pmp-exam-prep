<?php
/**
 * Custom Post Types Registration
 * Task: T002 - Custom Post Types Registration
 */

class PMP_Content_Post_Types {
    
    public static function init() {
        add_action('init', [__CLASS__, 'register_post_types']);
        add_action('add_meta_boxes', [__CLASS__, 'add_meta_boxes']);
        add_action('save_post', [__CLASS__, 'save_meta_boxes']);
    }
    
    /**
     * Register all custom post types
     */
    public static function register_post_types() {
        self::register_practice_test();
        self::register_resource();
        self::register_question();
    }
    
    /**
     * Register practice test post type
     */
    private static function register_practice_test() {
        register_post_type('practice_test', [
            'labels' => [
                'name' => 'Practice Tests',
                'singular_name' => 'Practice Test',
                'add_new' => 'Add New Test',
                'add_new_item' => 'Add New Practice Test',
                'edit_item' => 'Edit Practice Test',
                'new_item' => 'New Practice Test',
                'view_item' => 'View Practice Test',
                'search_items' => 'Search Practice Tests',
                'not_found' => 'No practice tests found',
                'not_found_in_trash' => 'No practice tests found in trash'
            ],
            'public' => true,
            'has_archive' => true,
            'menu_icon' => 'dashicons-clipboard',
            'supports' => ['title', 'editor', 'excerpt', 'thumbnail', 'custom-fields'],
            'rewrite' => ['slug' => 'practice-tests'],
            'show_in_rest' => true,
            'capability_type' => 'post',
            'hierarchical' => false,
            'menu_position' => 25
        ]);
    }
    
    /**
     * Register resource post type
     */
    private static function register_resource() {
        register_post_type('resource', [
            'labels' => [
                'name' => 'Resources',
                'singular_name' => 'Resource',
                'add_new' => 'Add New Resource',
                'add_new_item' => 'Add New Resource',
                'edit_item' => 'Edit Resource',
                'new_item' => 'New Resource',
                'view_item' => 'View Resource',
                'search_items' => 'Search Resources',
                'not_found' => 'No resources found',
                'not_found_in_trash' => 'No resources found in trash'
            ],
            'public' => true,
            'has_archive' => true,
            'menu_icon' => 'dashicons-download',
            'supports' => ['title', 'editor', 'excerpt', 'thumbnail', 'custom-fields'],
            'rewrite' => ['slug' => 'resources'],
            'show_in_rest' => true,
            'capability_type' => 'post',
            'hierarchical' => false,
            'menu_position' => 26
        ]);
    }
    
    /**
     * Register question post type (for practice test questions)
     */
    private static function register_question() {
        register_post_type('question', [
            'labels' => [
                'name' => 'Questions',
                'singular_name' => 'Question',
                'add_new' => 'Add New Question',
                'add_new_item' => 'Add New Question',
                'edit_item' => 'Edit Question',
                'new_item' => 'New Question',
                'view_item' => 'View Question',
                'search_items' => 'Search Questions',
                'not_found' => 'No questions found',
                'not_found_in_trash' => 'No questions found in trash'
            ],
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => 'edit.php?post_type=practice_test',
            'menu_icon' => 'dashicons-editor-help',
            'supports' => ['title', 'editor', 'custom-fields'],
            'show_in_rest' => true,
            'capability_type' => 'post',
            'hierarchical' => false
        ]);
    }
    
    /**
     * Add meta boxes for custom fields
     */
    public static function add_meta_boxes() {
        // Practice Test meta boxes
        add_meta_box(
            'practice_test_settings',
            'Test Settings',
            [__CLASS__, 'practice_test_meta_box'],
            'practice_test',
            'normal',
            'high'
        );
        
        // Resource meta boxes
        add_meta_box(
            'resource_settings',
            'Resource Settings',
            [__CLASS__, 'resource_meta_box'],
            'resource',
            'normal',
            'high'
        );
        
        // Question meta boxes
        add_meta_box(
            'question_settings',
            'Question Settings',
            [__CLASS__, 'question_meta_box'],
            'question',
            'normal',
            'high'
        );
    }
    
    /**
     * Practice test meta box
     */
    public static function practice_test_meta_box($post) {
        wp_nonce_field('practice_test_meta', 'practice_test_nonce');
        
        $time_limit = get_post_meta($post->ID, '_time_limit', true) ?: 180;
        $question_count = get_post_meta($post->ID, '_question_count', true) ?: 50;
        $passing_score = get_post_meta($post->ID, '_passing_score', true) ?: 70;
        $difficulty = get_post_meta($post->ID, '_difficulty_level', true) ?: 'medium';
        ?>
        <table class="form-table">
            <tr>
                <th><label for="time_limit">Time Limit (minutes)</label></th>
                <td><input type="number" id="time_limit" name="time_limit" value="<?php echo esc_attr($time_limit); ?>" min="1" max="300" /></td>
            </tr>
            <tr>
                <th><label for="question_count">Number of Questions</label></th>
                <td><input type="number" id="question_count" name="question_count" value="<?php echo esc_attr($question_count); ?>" min="1" max="200" /></td>
            </tr>
            <tr>
                <th><label for="passing_score">Passing Score (%)</label></th>
                <td><input type="number" id="passing_score" name="passing_score" value="<?php echo esc_attr($passing_score); ?>" min="1" max="100" /></td>
            </tr>
            <tr>
                <th><label for="difficulty_level">Difficulty Level</label></th>
                <td>
                    <select id="difficulty_level" name="difficulty_level">
                        <option value="easy" <?php selected($difficulty, 'easy'); ?>>Easy</option>
                        <option value="medium" <?php selected($difficulty, 'medium'); ?>>Medium</option>
                        <option value="hard" <?php selected($difficulty, 'hard'); ?>>Hard</option>
                    </select>
                </td>
            </tr>
        </table>
        <?php
    }
    
    /**
     * Resource meta box
     */
    public static function resource_meta_box($post) {
        wp_nonce_field('resource_meta', 'resource_nonce');
        
        $file_url = get_post_meta($post->ID, '_file_url', true);
        $file_size = get_post_meta($post->ID, '_file_size', true);
        $download_count = get_post_meta($post->ID, '_download_count', true) ?: 0;
        $is_premium = get_post_meta($post->ID, '_is_premium', true);
        ?>
        <table class="form-table">
            <tr>
                <th><label for="file_url">File URL</label></th>
                <td><input type="url" id="file_url" name="file_url" value="<?php echo esc_attr($file_url); ?>" class="regular-text" /></td>
            </tr>
            <tr>
                <th><label for="file_size">File Size (MB)</label></th>
                <td><input type="number" id="file_size" name="file_size" value="<?php echo esc_attr($file_size); ?>" step="0.1" /></td>
            </tr>
            <tr>
                <th><label for="download_count">Download Count</label></th>
                <td><input type="number" id="download_count" name="download_count" value="<?php echo esc_attr($download_count); ?>" readonly /></td>
            </tr>
            <tr>
                <th><label for="is_premium">Premium Content</label></th>
                <td><input type="checkbox" id="is_premium" name="is_premium" value="1" <?php checked($is_premium, '1'); ?> /></td>
            </tr>
        </table>
        <?php
    }
    
    /**
     * Question meta box
     */
    public static function question_meta_box($post) {
        wp_nonce_field('question_meta', 'question_nonce');
        
        $question_type = get_post_meta($post->ID, '_question_type', true) ?: 'multiple_choice';
        $correct_answer = get_post_meta($post->ID, '_correct_answer', true);
        $explanation = get_post_meta($post->ID, '_explanation', true);
        $difficulty = get_post_meta($post->ID, '_difficulty_level', true) ?: 'medium';
        $points = get_post_meta($post->ID, '_points', true) ?: 1;
        ?>
        <table class="form-table">
            <tr>
                <th><label for="question_type">Question Type</label></th>
                <td>
                    <select id="question_type" name="question_type">
                        <option value="multiple_choice" <?php selected($question_type, 'multiple_choice'); ?>>Multiple Choice</option>
                        <option value="drag_drop" <?php selected($question_type, 'drag_drop'); ?>>Drag & Drop</option>
                        <option value="scenario" <?php selected($question_type, 'scenario'); ?>>Scenario</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="correct_answer">Correct Answer</label></th>
                <td><input type="text" id="correct_answer" name="correct_answer" value="<?php echo esc_attr($correct_answer); ?>" class="regular-text" /></td>
            </tr>
            <tr>
                <th><label for="explanation">Explanation</label></th>
                <td><textarea id="explanation" name="explanation" rows="4" class="large-text"><?php echo esc_textarea($explanation); ?></textarea></td>
            </tr>
            <tr>
                <th><label for="difficulty_level">Difficulty Level</label></th>
                <td>
                    <select id="difficulty_level" name="difficulty_level">
                        <option value="easy" <?php selected($difficulty, 'easy'); ?>>Easy</option>
                        <option value="medium" <?php selected($difficulty, 'medium'); ?>>Medium</option>
                        <option value="hard" <?php selected($difficulty, 'hard'); ?>>Hard</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="points">Points</label></th>
                <td><input type="number" id="points" name="points" value="<?php echo esc_attr($points); ?>" min="1" max="10" /></td>
            </tr>
        </table>
        <?php
    }
    
    /**
     * Save meta box data
     */
    public static function save_meta_boxes($post_id) {
        // Practice test meta
        if (isset($_POST['practice_test_nonce']) && wp_verify_nonce($_POST['practice_test_nonce'], 'practice_test_meta')) {
            if (isset($_POST['time_limit'])) {
                update_post_meta($post_id, '_time_limit', intval($_POST['time_limit']));
            }
            if (isset($_POST['question_count'])) {
                update_post_meta($post_id, '_question_count', intval($_POST['question_count']));
            }
            if (isset($_POST['passing_score'])) {
                update_post_meta($post_id, '_passing_score', intval($_POST['passing_score']));
            }
            if (isset($_POST['difficulty_level'])) {
                update_post_meta($post_id, '_difficulty_level', sanitize_text_field($_POST['difficulty_level']));
            }
        }
        
        // Resource meta
        if (isset($_POST['resource_nonce']) && wp_verify_nonce($_POST['resource_nonce'], 'resource_meta')) {
            if (isset($_POST['file_url'])) {
                update_post_meta($post_id, '_file_url', esc_url_raw($_POST['file_url']));
            }
            if (isset($_POST['file_size'])) {
                update_post_meta($post_id, '_file_size', floatval($_POST['file_size']));
            }
            if (isset($_POST['download_count'])) {
                update_post_meta($post_id, '_download_count', intval($_POST['download_count']));
            }
            update_post_meta($post_id, '_is_premium', isset($_POST['is_premium']) ? '1' : '0');
        }
        
        // Question meta
        if (isset($_POST['question_nonce']) && wp_verify_nonce($_POST['question_nonce'], 'question_meta')) {
            if (isset($_POST['question_type'])) {
                update_post_meta($post_id, '_question_type', sanitize_text_field($_POST['question_type']));
            }
            if (isset($_POST['correct_answer'])) {
                update_post_meta($post_id, '_correct_answer', sanitize_text_field($_POST['correct_answer']));
            }
            if (isset($_POST['explanation'])) {
                update_post_meta($post_id, '_explanation', sanitize_textarea_field($_POST['explanation']));
            }
            if (isset($_POST['difficulty_level'])) {
                update_post_meta($post_id, '_difficulty_level', sanitize_text_field($_POST['difficulty_level']));
            }
            if (isset($_POST['points'])) {
                update_post_meta($post_id, '_points', intval($_POST['points']));
            }
        }
    }
}

// Initialize
PMP_Content_Post_Types::init();
?>
