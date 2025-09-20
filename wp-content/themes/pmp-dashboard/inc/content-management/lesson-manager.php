<?php
/**
 * Lesson Management Enhancement
 * Task: T009 - Lesson Management Enhancement
 */

class PMP_Lesson_Manager {
    
    /**
     * Initialize lesson management
     */
    public static function init() {
        add_action('add_meta_boxes', [__CLASS__, 'add_lesson_meta_boxes']);
        add_action('save_post', [__CLASS__, 'save_lesson_meta']);
        add_filter('manage_lesson_posts_columns', [__CLASS__, 'add_lesson_columns']);
        add_action('manage_lesson_posts_custom_column', [__CLASS__, 'display_lesson_columns'], 10, 2);
    }
    
    /**
     * Add lesson meta boxes
     */
    public static function add_lesson_meta_boxes() {
        add_meta_box(
            'lesson_hierarchy',
            'Lesson Hierarchy & Sequencing',
            [__CLASS__, 'lesson_hierarchy_meta_box'],
            'lesson',
            'normal',
            'high'
        );
        
        add_meta_box(
            'lesson_settings',
            'Lesson Settings',
            [__CLASS__, 'lesson_settings_meta_box'],
            'lesson',
            'side',
            'default'
        );
    }
    
    /**
     * Lesson hierarchy meta box
     */
    public static function lesson_hierarchy_meta_box($post) {
        wp_nonce_field('lesson_hierarchy_meta', 'lesson_hierarchy_nonce');
        
        // Get current prerequisites
        $prerequisites = self::get_lesson_prerequisites($post->ID);
        
        // Get available lessons for prerequisites
        $available_lessons = get_posts([
            'post_type' => 'lesson',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'exclude' => [$post->ID],
            'orderby' => 'title',
            'order' => 'ASC'
        ]);
        
        ?>
        <table class="form-table">
            <tr>
                <th><label for="lesson_prerequisites">Prerequisites</label></th>
                <td>
                    <select name="lesson_prerequisites[]" id="lesson_prerequisites" multiple style="width: 100%; height: 120px;">
                        <?php foreach ($available_lessons as $lesson): ?>
                            <option value="<?php echo $lesson->ID; ?>" 
                                <?php echo in_array($lesson->ID, wp_list_pluck($prerequisites, 'prerequisite_id')) ? 'selected' : ''; ?>>
                                <?php echo esc_html($lesson->post_title); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="description">Hold Ctrl/Cmd to select multiple prerequisites</p>
                </td>
            </tr>
            <tr>
                <th><label for="sequence_order">Sequence Order</label></th>
                <td>
                    <input type="number" id="sequence_order" name="sequence_order" 
                           value="<?php echo esc_attr(get_post_meta($post->ID, '_sequence_order', true) ?: 0); ?>" 
                           min="0" step="1" />
                    <p class="description">Lower numbers appear first in the sequence</p>
                </td>
            </tr>
            <tr>
                <th><label for="is_required">Required Lesson</label></th>
                <td>
                    <input type="checkbox" id="is_required" name="is_required" value="1" 
                           <?php checked(get_post_meta($post->ID, '_is_required', true), '1'); ?> />
                    <label for="is_required">This lesson is required for course completion</label>
                </td>
            </tr>
        </table>
        
        <h4>Current Prerequisites:</h4>
        <?php if (!empty($prerequisites)): ?>
            <ul>
                <?php foreach ($prerequisites as $prereq): ?>
                    <li><?php echo esc_html(get_the_title($prereq->prerequisite_id)); ?></li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p><em>No prerequisites set</em></p>
        <?php endif; ?>
        <?php
    }
    
    /**
     * Lesson settings meta box
     */
    public static function lesson_settings_meta_box($post) {
        wp_nonce_field('lesson_settings_meta', 'lesson_settings_nonce');
        
        $estimated_minutes = get_post_meta($post->ID, '_estimated_minutes', true) ?: 15;
        $difficulty_level = get_post_meta($post->ID, '_difficulty_level', true) ?: 'intermediate';
        $lesson_type = get_post_meta($post->ID, '_lesson_type', true) ?: 'text';
        $video_url = get_post_meta($post->ID, '_video_url', true);
        $downloadable_resources = get_post_meta($post->ID, '_downloadable_resources', true);
        
        ?>
        <table class="form-table">
            <tr>
                <th><label for="estimated_minutes">Duration (minutes)</label></th>
                <td><input type="number" id="estimated_minutes" name="estimated_minutes" 
                          value="<?php echo esc_attr($estimated_minutes); ?>" min="1" max="180" /></td>
            </tr>
            <tr>
                <th><label for="difficulty_level">Difficulty</label></th>
                <td>
                    <select id="difficulty_level" name="difficulty_level">
                        <option value="beginner" <?php selected($difficulty_level, 'beginner'); ?>>Beginner</option>
                        <option value="intermediate" <?php selected($difficulty_level, 'intermediate'); ?>>Intermediate</option>
                        <option value="advanced" <?php selected($difficulty_level, 'advanced'); ?>>Advanced</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="lesson_type">Lesson Type</label></th>
                <td>
                    <select id="lesson_type" name="lesson_type">
                        <option value="text" <?php selected($lesson_type, 'text'); ?>>Text Content</option>
                        <option value="video" <?php selected($lesson_type, 'video'); ?>>Video Lesson</option>
                        <option value="interactive" <?php selected($lesson_type, 'interactive'); ?>>Interactive</option>
                        <option value="mixed" <?php selected($lesson_type, 'mixed'); ?>>Mixed Media</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="video_url">Video URL</label></th>
                <td><input type="url" id="video_url" name="video_url" 
                          value="<?php echo esc_attr($video_url); ?>" style="width: 100%;" /></td>
            </tr>
            <tr>
                <th><label for="downloadable_resources">Resources</label></th>
                <td>
                    <textarea id="downloadable_resources" name="downloadable_resources" 
                             rows="3" style="width: 100%;"><?php echo esc_textarea($downloadable_resources); ?></textarea>
                    <p class="description">One resource URL per line</p>
                </td>
            </tr>
        </table>
        <?php
    }
    
    /**
     * Save lesson meta data
     */
    public static function save_lesson_meta($post_id) {
        // Check if this is a lesson
        if (get_post_type($post_id) !== 'lesson') {
            return;
        }
        
        // Save hierarchy data
        if (isset($_POST['lesson_hierarchy_nonce']) && wp_verify_nonce($_POST['lesson_hierarchy_nonce'], 'lesson_hierarchy_meta')) {
            // Clear existing prerequisites
            self::clear_lesson_prerequisites($post_id);
            
            // Add new prerequisites
            if (!empty($_POST['lesson_prerequisites'])) {
                foreach ($_POST['lesson_prerequisites'] as $prereq_id) {
                    PMP_Content_Manager::add_to_sequence($post_id, intval($prereq_id), 0, true);
                }
            }
            
            // Save sequence order
            if (isset($_POST['sequence_order'])) {
                update_post_meta($post_id, '_sequence_order', intval($_POST['sequence_order']));
            }
            
            // Save required status
            update_post_meta($post_id, '_is_required', isset($_POST['is_required']) ? '1' : '0');
        }
        
        // Save settings data
        if (isset($_POST['lesson_settings_nonce']) && wp_verify_nonce($_POST['lesson_settings_nonce'], 'lesson_settings_meta')) {
            $fields = ['estimated_minutes', 'difficulty_level', 'lesson_type', 'video_url', 'downloadable_resources'];
            
            foreach ($fields as $field) {
                if (isset($_POST[$field])) {
                    $value = $field === 'estimated_minutes' ? intval($_POST[$field]) : sanitize_text_field($_POST[$field]);
                    if ($field === 'downloadable_resources') {
                        $value = sanitize_textarea_field($_POST[$field]);
                    }
                    update_post_meta($post_id, "_{$field}", $value);
                }
            }
        }
    }
    
    /**
     * Add custom columns to lesson list
     */
    public static function add_lesson_columns($columns) {
        $new_columns = [];
        
        foreach ($columns as $key => $value) {
            $new_columns[$key] = $value;
            
            if ($key === 'title') {
                $new_columns['duration'] = 'Duration';
                $new_columns['difficulty'] = 'Difficulty';
                $new_columns['sequence'] = 'Order';
                $new_columns['prerequisites'] = 'Prerequisites';
            }
        }
        
        return $new_columns;
    }
    
    /**
     * Display custom column content
     */
    public static function display_lesson_columns($column, $post_id) {
        switch ($column) {
            case 'duration':
                $minutes = get_post_meta($post_id, '_estimated_minutes', true) ?: 15;
                echo $minutes . ' min';
                break;
                
            case 'difficulty':
                $difficulty = get_post_meta($post_id, '_difficulty_level', true) ?: 'intermediate';
                $colors = [
                    'beginner' => '#10b981',
                    'intermediate' => '#f59e0b', 
                    'advanced' => '#ef4444'
                ];
                echo '<span style="color: ' . $colors[$difficulty] . '; font-weight: bold;">' . ucfirst($difficulty) . '</span>';
                break;
                
            case 'sequence':
                $order = get_post_meta($post_id, '_sequence_order', true) ?: 0;
                echo $order;
                break;
                
            case 'prerequisites':
                $prerequisites = self::get_lesson_prerequisites($post_id);
                if (!empty($prerequisites)) {
                    echo count($prerequisites) . ' prereq(s)';
                } else {
                    echo '—';
                }
                break;
        }
    }
    
    /**
     * Get lesson prerequisites
     */
    public static function get_lesson_prerequisites($lesson_id) {
        global $wpdb;
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}pmp_content_sequences 
             WHERE content_id = %d",
            $lesson_id
        ));
    }
    
    /**
     * Clear lesson prerequisites
     */
    private static function clear_lesson_prerequisites($lesson_id) {
        global $wpdb;
        
        $wpdb->delete(
            $wpdb->prefix . 'pmp_content_sequences',
            ['content_id' => $lesson_id],
            ['%d']
        );
    }
    
    /**
     * Get lessons by domain
     */
    public static function get_lessons_by_domain($domain_slug, $args = []) {
        $defaults = [
            'post_type' => 'lesson',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'meta_key' => '_sequence_order',
            'orderby' => 'meta_value_num',
            'order' => 'ASC'
        ];
        
        $args = wp_parse_args($args, $defaults);
        
        $args['tax_query'] = [
            [
                'taxonomy' => 'pmp_domain',
                'field' => 'slug',
                'terms' => $domain_slug
            ]
        ];
        
        return get_posts($args);
    }
    
    /**
     * Get lesson completion statistics
     */
    public static function get_lesson_stats($lesson_id) {
        global $wpdb;
        
        $stats = [];
        
        // Total enrollments (users who started)
        $stats['total_started'] = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(DISTINCT user_id) FROM {$wpdb->prefix}pmp_lesson_progress 
             WHERE lesson_id = %d",
            $lesson_id
        ));
        
        // Completed count
        $stats['total_completed'] = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(DISTINCT user_id) FROM {$wpdb->prefix}pmp_lesson_progress 
             WHERE lesson_id = %d AND status = 'completed'",
            $lesson_id
        ));
        
        // Average completion time
        $stats['avg_time_spent'] = $wpdb->get_var($wpdb->prepare(
            "SELECT AVG(time_spent) FROM {$wpdb->prefix}pmp_lesson_progress 
             WHERE lesson_id = %d AND status = 'completed'",
            $lesson_id
        ));
        
        // Completion rate
        $stats['completion_rate'] = $stats['total_started'] > 0 
            ? round(($stats['total_completed'] / $stats['total_started']) * 100, 1)
            : 0;
        
        return $stats;
    }
    
    /**
     * Get next lesson in sequence
     */
    public static function get_next_lesson($current_lesson_id, $user_id = null) {
        // Get current lesson's sequence order
        $current_order = get_post_meta($current_lesson_id, '_sequence_order', true) ?: 0;
        
        // Get next lesson in sequence
        $next_lesson = get_posts([
            'post_type' => 'lesson',
            'post_status' => 'publish',
            'posts_per_page' => 1,
            'meta_query' => [
                [
                    'key' => '_sequence_order',
                    'value' => $current_order,
                    'compare' => '>'
                ]
            ],
            'meta_key' => '_sequence_order',
            'orderby' => 'meta_value_num',
            'order' => 'ASC'
        ]);
        
        if (!empty($next_lesson) && $user_id) {
            // Check if user can access next lesson
            if (PMP_Content_Manager::can_access_content($next_lesson[0]->ID, $user_id)) {
                return $next_lesson[0];
            }
        }
        
        return !empty($next_lesson) ? $next_lesson[0] : null;
    }
    
    /**
     * Get previous lesson in sequence
     */
    public static function get_previous_lesson($current_lesson_id) {
        $current_order = get_post_meta($current_lesson_id, '_sequence_order', true) ?: 0;
        
        $prev_lesson = get_posts([
            'post_type' => 'lesson',
            'post_status' => 'publish',
            'posts_per_page' => 1,
            'meta_query' => [
                [
                    'key' => '_sequence_order',
                    'value' => $current_order,
                    'compare' => '<'
                ]
            ],
            'meta_key' => '_sequence_order',
            'orderby' => 'meta_value_num',
            'order' => 'DESC'
        ]);
        
        return !empty($prev_lesson) ? $prev_lesson[0] : null;
    }
}

// Initialize
PMP_Lesson_Manager::init();
?>
