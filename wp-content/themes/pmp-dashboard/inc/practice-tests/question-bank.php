<?php
/**
 * Question Bank Management
 * Task: T018 - Question Bank Management
 */

class PMP_Question_Bank {
    
    public static function init() {
        add_action('add_meta_boxes', [__CLASS__, 'add_question_meta_boxes']);
        add_action('save_post', [__CLASS__, 'save_question_data']);
        add_filter('manage_question_posts_columns', [__CLASS__, 'add_question_columns']);
        add_action('manage_question_posts_custom_column', [__CLASS__, 'display_question_columns'], 10, 2);
    }
    
    /**
     * Add question meta boxes
     */
    public static function add_question_meta_boxes() {
        add_meta_box(
            'question_details',
            'Question Details',
            [__CLASS__, 'question_details_meta_box'],
            'question',
            'normal',
            'high'
        );
        
        add_meta_box(
            'question_options',
            'Answer Options',
            [__CLASS__, 'question_options_meta_box'],
            'question',
            'normal',
            'high'
        );
    }
    
    /**
     * Question details meta box
     */
    public static function question_details_meta_box($post) {
        wp_nonce_field('question_details_meta', 'question_details_nonce');
        
        $question_type = get_post_meta($post->ID, '_question_type', true) ?: 'multiple_choice';
        $difficulty_level = get_post_meta($post->ID, '_difficulty_level', true) ?: 'medium';
        $domain = get_post_meta($post->ID, '_domain', true);
        $knowledge_area = get_post_meta($post->ID, '_knowledge_area', true);
        $points = get_post_meta($post->ID, '_points', true) ?: 1;
        $time_limit = get_post_meta($post->ID, '_time_limit', true);
        $explanation = get_post_meta($post->ID, '_explanation', true);
        
        ?>
        <table class="form-table">
            <tr>
                <th><label for="question_type">Question Type</label></th>
                <td>
                    <select id="question_type" name="question_type" class="regular-text">
                        <option value="multiple_choice" <?php selected($question_type, 'multiple_choice'); ?>>Multiple Choice</option>
                        <option value="drag_drop" <?php selected($question_type, 'drag_drop'); ?>>Drag & Drop</option>
                        <option value="scenario" <?php selected($question_type, 'scenario'); ?>>Scenario Based</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="difficulty_level">Difficulty Level</label></th>
                <td>
                    <select id="difficulty_level" name="difficulty_level" class="regular-text">
                        <option value="easy" <?php selected($difficulty_level, 'easy'); ?>>Easy</option>
                        <option value="medium" <?php selected($difficulty_level, 'medium'); ?>>Medium</option>
                        <option value="hard" <?php selected($difficulty_level, 'hard'); ?>>Hard</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="domain">PMP Domain</label></th>
                <td>
                    <select id="domain" name="domain" class="regular-text">
                        <option value="">Select Domain</option>
                        <option value="people" <?php selected($domain, 'people'); ?>>People (42%)</option>
                        <option value="process" <?php selected($domain, 'process'); ?>>Process (50%)</option>
                        <option value="business_environment" <?php selected($domain, 'business_environment'); ?>>Business Environment (8%)</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="knowledge_area">Knowledge Area</label></th>
                <td>
                    <select id="knowledge_area" name="knowledge_area" class="regular-text">
                        <option value="">Select Knowledge Area</option>
                        <option value="integration" <?php selected($knowledge_area, 'integration'); ?>>Project Integration Management</option>
                        <option value="scope" <?php selected($knowledge_area, 'scope'); ?>>Project Scope Management</option>
                        <option value="schedule" <?php selected($knowledge_area, 'schedule'); ?>>Project Schedule Management</option>
                        <option value="cost" <?php selected($knowledge_area, 'cost'); ?>>Project Cost Management</option>
                        <option value="quality" <?php selected($knowledge_area, 'quality'); ?>>Project Quality Management</option>
                        <option value="resource" <?php selected($knowledge_area, 'resource'); ?>>Project Resource Management</option>
                        <option value="communications" <?php selected($knowledge_area, 'communications'); ?>>Project Communications Management</option>
                        <option value="risk" <?php selected($knowledge_area, 'risk'); ?>>Project Risk Management</option>
                        <option value="procurement" <?php selected($knowledge_area, 'procurement'); ?>>Project Procurement Management</option>
                        <option value="stakeholder" <?php selected($knowledge_area, 'stakeholder'); ?>>Project Stakeholder Management</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="points">Points</label></th>
                <td>
                    <input type="number" id="points" name="points" value="<?php echo esc_attr($points); ?>" 
                           min="1" max="10" class="small-text" />
                </td>
            </tr>
            <tr>
                <th><label for="time_limit">Time Limit (seconds)</label></th>
                <td>
                    <input type="number" id="time_limit" name="time_limit" value="<?php echo esc_attr($time_limit); ?>" 
                           min="30" max="600" class="small-text" />
                    <p class="description">Leave empty for no time limit</p>
                </td>
            </tr>
            <tr>
                <th><label for="explanation">Explanation</label></th>
                <td>
                    <textarea id="explanation" name="explanation" rows="4" class="large-text"><?php echo esc_textarea($explanation); ?></textarea>
                    <p class="description">Detailed explanation for the correct answer</p>
                </td>
            </tr>
        </table>
        <?php
    }
    
    /**
     * Question options meta box
     */
    public static function question_options_meta_box($post) {
        wp_nonce_field('question_options_meta', 'question_options_nonce');
        
        // Get existing options
        $options = self::get_question_options($post->ID);
        
        ?>
        <div id="question-options-container">
            <div class="question-options-header">
                <h4>Answer Options</h4>
                <button type="button" id="add-option" class="button">Add Option</button>
            </div>
            
            <div id="options-list">
                <?php if (!empty($options)): ?>
                    <?php foreach ($options as $index => $option): ?>
                        <div class="option-row" data-index="<?php echo $index; ?>">
                            <div class="option-controls">
                                <label>
                                    <input type="radio" name="correct_option" value="<?php echo $index; ?>" 
                                           <?php checked($option->is_correct, 1); ?> />
                                    Correct
                                </label>
                                <button type="button" class="remove-option button-link-delete">Remove</button>
                            </div>
                            <textarea name="options[<?php echo $index; ?>][text]" 
                                     placeholder="Enter answer option..." 
                                     rows="2" class="large-text"><?php echo esc_textarea($option->option_text); ?></textarea>
                            <input type="hidden" name="options[<?php echo $index; ?>][order]" value="<?php echo $option->option_order; ?>" />
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Default 4 options for new questions -->
                    <?php for ($i = 0; $i < 4; $i++): ?>
                        <div class="option-row" data-index="<?php echo $i; ?>">
                            <div class="option-controls">
                                <label>
                                    <input type="radio" name="correct_option" value="<?php echo $i; ?>" 
                                           <?php checked($i, 0); ?> />
                                    Correct
                                </label>
                                <button type="button" class="remove-option button-link-delete">Remove</button>
                            </div>
                            <textarea name="options[<?php echo $i; ?>][text]" 
                                     placeholder="Enter answer option..." 
                                     rows="2" class="large-text"></textarea>
                            <input type="hidden" name="options[<?php echo $i; ?>][order]" value="<?php echo $i + 1; ?>" />
                        </div>
                    <?php endfor; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <style>
        .option-row {
            margin-bottom: 15px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background: #f9f9f9;
        }
        
        .option-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }
        
        .question-options-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .question-options-header h4 {
            margin: 0;
        }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            let optionIndex = <?php echo count($options) ?: 4; ?>;
            
            // Add new option
            $('#add-option').on('click', function() {
                const optionHtml = `
                    <div class="option-row" data-index="${optionIndex}">
                        <div class="option-controls">
                            <label>
                                <input type="radio" name="correct_option" value="${optionIndex}" />
                                Correct
                            </label>
                            <button type="button" class="remove-option button-link-delete">Remove</button>
                        </div>
                        <textarea name="options[${optionIndex}][text]" 
                                 placeholder="Enter answer option..." 
                                 rows="2" class="large-text"></textarea>
                        <input type="hidden" name="options[${optionIndex}][order]" value="${optionIndex + 1}" />
                    </div>
                `;
                
                $('#options-list').append(optionHtml);
                optionIndex++;
            });
            
            // Remove option
            $(document).on('click', '.remove-option', function() {
                if ($('.option-row').length > 2) { // Keep at least 2 options
                    $(this).closest('.option-row').remove();
                } else {
                    alert('A question must have at least 2 options.');
                }
            });
        });
        </script>
        <?php
    }
    
    /**
     * Save question data
     */
    public static function save_question_data($post_id) {
        if (get_post_type($post_id) !== 'question') {
            return;
        }
        
        // Save question details
        if (isset($_POST['question_details_nonce']) && wp_verify_nonce($_POST['question_details_nonce'], 'question_details_meta')) {
            $fields = ['question_type', 'difficulty_level', 'domain', 'knowledge_area', 'points', 'time_limit', 'explanation'];
            
            foreach ($fields as $field) {
                if (isset($_POST[$field])) {
                    $value = $field === 'points' || $field === 'time_limit' 
                        ? intval($_POST[$field]) 
                        : sanitize_text_field($_POST[$field]);
                    
                    if ($field === 'explanation') {
                        $value = sanitize_textarea_field($_POST[$field]);
                    }
                    
                    update_post_meta($post_id, "_{$field}", $value);
                }
            }
        }
        
        // Save question options
        if (isset($_POST['question_options_nonce']) && wp_verify_nonce($_POST['question_options_nonce'], 'question_options_meta')) {
            // Clear existing options
            self::clear_question_options($post_id);
            
            if (!empty($_POST['options'])) {
                $correct_option = intval($_POST['correct_option'] ?? 0);
                
                foreach ($_POST['options'] as $index => $option_data) {
                    if (!empty($option_data['text'])) {
                        self::add_question_option(
                            $post_id,
                            sanitize_textarea_field($option_data['text']),
                            $index == $correct_option,
                            intval($option_data['order'] ?? $index + 1)
                        );
                    }
                }
            }
        }
    }
    
    /**
     * Add custom columns
     */
    public static function add_question_columns($columns) {
        $new_columns = [];
        
        foreach ($columns as $key => $value) {
            $new_columns[$key] = $value;
            
            if ($key === 'title') {
                $new_columns['question_type'] = 'Type';
                $new_columns['difficulty'] = 'Difficulty';
                $new_columns['domain'] = 'Domain';
                $new_columns['options_count'] = 'Options';
            }
        }
        
        return $new_columns;
    }
    
    /**
     * Display custom columns
     */
    public static function display_question_columns($column, $post_id) {
        switch ($column) {
            case 'question_type':
                $type = get_post_meta($post_id, '_question_type', true) ?: 'multiple_choice';
                echo ucfirst(str_replace('_', ' ', $type));
                break;
                
            case 'difficulty':
                $difficulty = get_post_meta($post_id, '_difficulty_level', true) ?: 'medium';
                $colors = ['easy' => '#10b981', 'medium' => '#f59e0b', 'hard' => '#ef4444'];
                echo '<span style="color: ' . $colors[$difficulty] . '; font-weight: bold;">' . ucfirst($difficulty) . '</span>';
                break;
                
            case 'domain':
                $domain = get_post_meta($post_id, '_domain', true);
                if ($domain) {
                    $domain_names = [
                        'people' => 'People',
                        'process' => 'Process',
                        'business_environment' => 'Business Env.'
                    ];
                    echo $domain_names[$domain] ?? ucfirst($domain);
                } else {
                    echo '—';
                }
                break;
                
            case 'options_count':
                $options = self::get_question_options($post_id);
                echo count($options) . ' options';
                break;
        }
    }
    
    /**
     * Get question options
     */
    public static function get_question_options($question_id) {
        global $wpdb;
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}pmp_question_options 
             WHERE question_id = %d 
             ORDER BY option_order",
            $question_id
        ));
    }
    
    /**
     * Add question option
     */
    public static function add_question_option($question_id, $option_text, $is_correct = false, $order = 1) {
        global $wpdb;
        
        return $wpdb->insert(
            $wpdb->prefix . 'pmp_question_options',
            [
                'question_id' => $question_id,
                'option_text' => $option_text,
                'is_correct' => $is_correct ? 1 : 0,
                'option_order' => $order
            ],
            ['%d', '%s', '%d', '%d']
        );
    }
    
    /**
     * Clear question options
     */
    private static function clear_question_options($question_id) {
        global $wpdb;
        
        $wpdb->delete(
            $wpdb->prefix . 'pmp_question_options',
            ['question_id' => $question_id],
            ['%d']
        );
    }
    
    /**
     * Get questions by criteria
     */
    public static function get_questions($args = []) {
        $defaults = [
            'domain' => '',
            'difficulty' => '',
            'question_type' => '',
            'limit' => 50,
            'randomize' => false
        ];
        
        $args = wp_parse_args($args, $defaults);
        
        global $wpdb;
        
        $sql = "SELECT q.*, p.post_title, p.post_content 
                FROM {$wpdb->prefix}pmp_test_questions q
                JOIN {$wpdb->posts} p ON q.id = p.ID
                WHERE p.post_status = 'publish'";
        
        $params = [];
        
        if (!empty($args['domain'])) {
            $sql .= " AND q.domain = %s";
            $params[] = $args['domain'];
        }
        
        if (!empty($args['difficulty'])) {
            $sql .= " AND q.difficulty_level = %s";
            $params[] = $args['difficulty'];
        }
        
        if (!empty($args['question_type'])) {
            $sql .= " AND q.question_type = %s";
            $params[] = $args['question_type'];
        }
        
        $sql .= $args['randomize'] ? " ORDER BY RAND()" : " ORDER BY q.id";
        $sql .= " LIMIT %d";
        $params[] = $args['limit'];
        
        return $wpdb->get_results($wpdb->prepare($sql, $params));
    }
    
    /**
     * Get question statistics
     */
    public static function get_question_statistics() {
        global $wpdb;
        
        $stats = [];
        
        // Total questions
        $stats['total'] = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'question' AND post_status = 'publish'"
        );
        
        // By difficulty
        $stats['by_difficulty'] = $wpdb->get_results(
            "SELECT pm.meta_value as difficulty, COUNT(*) as count
             FROM {$wpdb->postmeta} pm
             JOIN {$wpdb->posts} p ON pm.post_id = p.ID
             WHERE pm.meta_key = '_difficulty_level' 
             AND p.post_type = 'question' 
             AND p.post_status = 'publish'
             GROUP BY pm.meta_value"
        );
        
        // By domain
        $stats['by_domain'] = $wpdb->get_results(
            "SELECT pm.meta_value as domain, COUNT(*) as count
             FROM {$wpdb->postmeta} pm
             JOIN {$wpdb->posts} p ON pm.post_id = p.ID
             WHERE pm.meta_key = '_domain' 
             AND p.post_type = 'question' 
             AND p.post_status = 'publish'
             GROUP BY pm.meta_value"
        );
        
        return $stats;
    }
}

// Initialize
PMP_Question_Bank::init();
?>
