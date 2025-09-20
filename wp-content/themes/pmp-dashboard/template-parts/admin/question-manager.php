<?php
/**
 * Question Management Interface
 * Task: T023 - Question Management Interface
 */

// Check admin permissions
if (!current_user_can('manage_options')) {
    wp_die('Access denied');
}

// Get question statistics
$question_stats = PMP_Question_Bank::get_question_statistics();

// Handle bulk actions
if (isset($_POST['bulk_action']) && isset($_POST['question_ids'])) {
    $action = sanitize_text_field($_POST['bulk_action']);
    $question_ids = array_map('intval', $_POST['question_ids']);
    
    switch ($action) {
        case 'delete':
            foreach ($question_ids as $id) {
                wp_delete_post($id, true);
            }
            echo '<div class="notice notice-success"><p>Questions deleted successfully.</p></div>';
            break;
            
        case 'publish':
            foreach ($question_ids as $id) {
                wp_update_post(['ID' => $id, 'post_status' => 'publish']);
            }
            echo '<div class="notice notice-success"><p>Questions published successfully.</p></div>';
            break;
            
        case 'draft':
            foreach ($question_ids as $id) {
                wp_update_post(['ID' => $id, 'post_status' => 'draft']);
            }
            echo '<div class="notice notice-success"><p>Questions moved to draft.</p></div>';
            break;
    }
}

// Get filter parameters
$filter_domain = $_GET['filter_domain'] ?? '';
$filter_difficulty = $_GET['filter_difficulty'] ?? '';
$filter_type = $_GET['filter_type'] ?? '';
$search = $_GET['s'] ?? '';

// Build query args
$args = [
    'post_type' => 'question',
    'post_status' => ['publish', 'draft'],
    'posts_per_page' => 20,
    'paged' => get_query_var('paged') ?: 1,
    'orderby' => 'date',
    'order' => 'DESC'
];

if ($search) {
    $args['s'] = $search;
}

$meta_query = [];
if ($filter_domain) {
    $meta_query[] = [
        'key' => '_domain',
        'value' => $filter_domain,
        'compare' => '='
    ];
}

if ($filter_difficulty) {
    $meta_query[] = [
        'key' => '_difficulty_level',
        'value' => $filter_difficulty,
        'compare' => '='
    ];
}

if ($filter_type) {
    $meta_query[] = [
        'key' => '_question_type',
        'value' => $filter_type,
        'compare' => '='
    ];
}

if (!empty($meta_query)) {
    $args['meta_query'] = $meta_query;
}

$questions_query = new WP_Query($args);
?>

<div class="question-manager-interface">
    
    <!-- Header -->
    <div class="wrap">
        <h1 class="wp-heading-inline">Question Bank Management</h1>
        <a href="<?php echo admin_url('post-new.php?post_type=question'); ?>" class="page-title-action">Add New Question</a>
        <hr class="wp-header-end">
        
        <!-- Statistics Dashboard -->
        <div class="question-stats-dashboard" style="margin: 20px 0;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                
                <div class="stats-card" style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; border-radius: 4px;">
                    <h3 style="margin: 0 0 10px 0; color: #23282d;">Total Questions</h3>
                    <div style="font-size: 2em; font-weight: bold; color: #0073aa;"><?php echo $question_stats['total']; ?></div>
                </div>
                
                <?php if (!empty($question_stats['by_difficulty'])): ?>
                    <div class="stats-card" style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; border-radius: 4px;">
                        <h3 style="margin: 0 0 10px 0; color: #23282d;">By Difficulty</h3>
                        <?php foreach ($question_stats['by_difficulty'] as $diff): ?>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                                <span><?php echo ucfirst($diff->difficulty); ?>:</span>
                                <strong><?php echo $diff->count; ?></strong>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($question_stats['by_domain'])): ?>
                    <div class="stats-card" style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; border-radius: 4px;">
                        <h3 style="margin: 0 0 10px 0; color: #23282d;">By Domain</h3>
                        <?php foreach ($question_stats['by_domain'] as $domain): ?>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                                <span><?php echo ucfirst($domain->domain); ?>:</span>
                                <strong><?php echo $domain->count; ?></strong>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <div class="stats-card" style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; border-radius: 4px;">
                    <h3 style="margin: 0 0 10px 0; color: #23282d;">Quick Actions</h3>
                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        <a href="<?php echo admin_url('post-new.php?post_type=question'); ?>" class="button button-primary">Add Question</a>
                        <a href="<?php echo admin_url('edit.php?post_type=practice_test'); ?>" class="button">Manage Tests</a>
                        <button onclick="exportQuestions()" class="button">Export Questions</button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Filters -->
        <div class="tablenav top">
            <form method="get" style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                <input type="hidden" name="post_type" value="question">
                
                <select name="filter_domain">
                    <option value="">All Domains</option>
                    <option value="people" <?php selected($filter_domain, 'people'); ?>>People</option>
                    <option value="process" <?php selected($filter_domain, 'process'); ?>>Process</option>
                    <option value="business_environment" <?php selected($filter_domain, 'business_environment'); ?>>Business Environment</option>
                </select>
                
                <select name="filter_difficulty">
                    <option value="">All Difficulties</option>
                    <option value="easy" <?php selected($filter_difficulty, 'easy'); ?>>Easy</option>
                    <option value="medium" <?php selected($filter_difficulty, 'medium'); ?>>Medium</option>
                    <option value="hard" <?php selected($filter_difficulty, 'hard'); ?>>Hard</option>
                </select>
                
                <select name="filter_type">
                    <option value="">All Types</option>
                    <option value="multiple_choice" <?php selected($filter_type, 'multiple_choice'); ?>>Multiple Choice</option>
                    <option value="drag_drop" <?php selected($filter_type, 'drag_drop'); ?>>Drag & Drop</option>
                    <option value="scenario" <?php selected($filter_type, 'scenario'); ?>>Scenario</option>
                </select>
                
                <input type="search" name="s" value="<?php echo esc_attr($search); ?>" placeholder="Search questions...">
                
                <input type="submit" class="button" value="Filter">
                
                <?php if ($filter_domain || $filter_difficulty || $filter_type || $search): ?>
                    <a href="<?php echo admin_url('admin.php?page=question-manager'); ?>" class="button">Clear Filters</a>
                <?php endif; ?>
            </form>
        </div>
        
        <!-- Questions Table -->
        <?php if ($questions_query->have_posts()): ?>
            <form method="post" id="questions-form">
                
                <!-- Bulk Actions -->
                <div class="tablenav top">
                    <div class="alignleft actions bulkactions">
                        <select name="bulk_action">
                            <option value="">Bulk Actions</option>
                            <option value="publish">Publish</option>
                            <option value="draft">Move to Draft</option>
                            <option value="delete">Delete</option>
                        </select>
                        <input type="submit" class="button action" value="Apply" onclick="return confirm('Are you sure you want to perform this bulk action?')">
                    </div>
                    
                    <div class="alignright">
                        <span class="displaying-num"><?php echo $questions_query->found_posts; ?> items</span>
                    </div>
                </div>
                
                <table class="wp-list-table widefat fixed striped posts">
                    <thead>
                        <tr>
                            <td class="manage-column column-cb check-column">
                                <input type="checkbox" id="cb-select-all">
                            </td>
                            <th class="manage-column column-title">Question</th>
                            <th class="manage-column">Type</th>
                            <th class="manage-column">Domain</th>
                            <th class="manage-column">Difficulty</th>
                            <th class="manage-column">Options</th>
                            <th class="manage-column">Status</th>
                            <th class="manage-column">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($questions_query->have_posts()): $questions_query->the_post(); ?>
                            <?php
                            $question_id = get_the_ID();
                            $question_type = get_post_meta($question_id, '_question_type', true) ?: 'multiple_choice';
                            $domain = get_post_meta($question_id, '_domain', true);
                            $difficulty = get_post_meta($question_id, '_difficulty_level', true) ?: 'medium';
                            $options = PMP_Question_Bank::get_question_options($question_id);
                            ?>
                            
                            <tr>
                                <th class="check-column">
                                    <input type="checkbox" name="question_ids[]" value="<?php echo $question_id; ?>">
                                </th>
                                
                                <td class="column-title">
                                    <strong>
                                        <a href="<?php echo get_edit_post_link($question_id); ?>">
                                            <?php echo esc_html(wp_trim_words(get_the_title(), 10)); ?>
                                        </a>
                                    </strong>
                                    
                                    <div class="row-actions">
                                        <span class="edit">
                                            <a href="<?php echo get_edit_post_link($question_id); ?>">Edit</a> |
                                        </span>
                                        <span class="view">
                                            <a href="<?php echo get_permalink($question_id); ?>" target="_blank">View</a> |
                                        </span>
                                        <span class="trash">
                                            <a href="<?php echo get_delete_post_link($question_id); ?>" 
                                               onclick="return confirm('Are you sure you want to delete this question?')">Delete</a>
                                        </span>
                                    </div>
                                </td>
                                
                                <td>
                                    <span class="question-type-badge" style="padding: 2px 8px; border-radius: 3px; font-size: 11px; background: #f0f0f1; color: #50575e;">
                                        <?php echo ucfirst(str_replace('_', ' ', $question_type)); ?>
                                    </span>
                                </td>
                                
                                <td>
                                    <?php if ($domain): ?>
                                        <span class="domain-badge" style="padding: 2px 8px; border-radius: 3px; font-size: 11px; 
                                            <?php 
                                            $colors = [
                                                'people' => 'background: #e7f3ff; color: #0073aa;',
                                                'process' => 'background: #f0f6fc; color: #0969da;',
                                                'business_environment' => 'background: #fff8e1; color: #b45309;'
                                            ];
                                            echo $colors[$domain] ?? 'background: #f0f0f1; color: #50575e;';
                                            ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', $domain)); ?>
                                        </span>
                                    <?php else: ?>
                                        <span style="color: #a7aaad;">—</span>
                                    <?php endif; ?>
                                </td>
                                
                                <td>
                                    <span class="difficulty-badge" style="padding: 2px 8px; border-radius: 3px; font-size: 11px; font-weight: 500;
                                        <?php 
                                        $diff_colors = [
                                            'easy' => 'background: #d1fae5; color: #065f46;',
                                            'medium' => 'background: #fef3c7; color: #92400e;',
                                            'hard' => 'background: #fee2e2; color: #991b1b;'
                                        ];
                                        echo $diff_colors[$difficulty] ?? $diff_colors['medium'];
                                        ?>">
                                        <?php echo ucfirst($difficulty); ?>
                                    </span>
                                </td>
                                
                                <td>
                                    <span style="color: #50575e;"><?php echo count($options); ?> options</span>
                                </td>
                                
                                <td>
                                    <?php if (get_post_status() === 'publish'): ?>
                                        <span style="color: #00a32a;">Published</span>
                                    <?php else: ?>
                                        <span style="color: #dba617;">Draft</span>
                                    <?php endif; ?>
                                </td>
                                
                                <td>
                                    <abbr title="<?php echo get_the_date('Y/m/d g:i:s a'); ?>">
                                        <?php echo get_the_date('Y/m/d'); ?>
                                    </abbr>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                
                <!-- Pagination -->
                <?php if ($questions_query->max_num_pages > 1): ?>
                    <div class="tablenav bottom">
                        <div class="tablenav-pages">
                            <?php
                            echo paginate_links([
                                'total' => $questions_query->max_num_pages,
                                'current' => max(1, get_query_var('paged')),
                                'format' => '?paged=%#%',
                                'show_all' => false,
                                'end_size' => 1,
                                'mid_size' => 2,
                                'prev_next' => true,
                                'prev_text' => '‹',
                                'next_text' => '›',
                                'type' => 'plain'
                            ]);
                            ?>
                        </div>
                    </div>
                <?php endif; ?>
            </form>
            
        <?php else: ?>
            <div class="no-questions" style="text-align: center; padding: 40px; background: #fff; border: 1px solid #ccd0d4;">
                <h3>No questions found</h3>
                <p>No questions match your current filters.</p>
                <a href="<?php echo admin_url('post-new.php?post_type=question'); ?>" class="button button-primary">Add Your First Question</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Select all checkbox functionality
document.getElementById('cb-select-all').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('input[name="question_ids[]"]');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
});

// Export questions functionality
function exportQuestions() {
    const selectedIds = Array.from(document.querySelectorAll('input[name="question_ids[]"]:checked'))
        .map(cb => cb.value);
    
    if (selectedIds.length === 0) {
        alert('Please select questions to export.');
        return;
    }
    
    // Create export URL
    const exportUrl = '<?php echo admin_url('admin-ajax.php'); ?>?action=export_questions&question_ids=' + selectedIds.join(',');
    
    // Trigger download
    window.location.href = exportUrl;
}

// Question preview functionality
function previewQuestion(questionId) {
    // Open question preview in modal or new window
    const previewUrl = '<?php echo admin_url('admin-ajax.php'); ?>?action=preview_question&question_id=' + questionId;
    window.open(previewUrl, 'question-preview', 'width=800,height=600,scrollbars=yes');
}

// Auto-save search filters
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form[method="get"]');
    const inputs = form.querySelectorAll('select, input[type="search"]');
    
    inputs.forEach(input => {
        input.addEventListener('change', function() {
            // Auto-submit form after short delay
            setTimeout(() => {
                if (this.value !== '') {
                    form.submit();
                }
            }, 500);
        });
    });
});

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Ctrl/Cmd + A to select all
    if ((e.ctrlKey || e.metaKey) && e.key === 'a' && e.target.tagName !== 'INPUT') {
        e.preventDefault();
        document.getElementById('cb-select-all').click();
    }
    
    // Delete key to delete selected
    if (e.key === 'Delete' && document.activeElement.tagName !== 'INPUT') {
        const selected = document.querySelectorAll('input[name="question_ids[]"]:checked');
        if (selected.length > 0) {
            if (confirm('Delete selected questions?')) {
                document.querySelector('select[name="bulk_action"]').value = 'delete';
                document.getElementById('questions-form').submit();
            }
        }
    }
});
</script>

<style>
.question-manager-interface .stats-card:hover {
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.question-manager-interface .wp-list-table th,
.question-manager-interface .wp-list-table td {
    vertical-align: middle;
}

.question-manager-interface .column-title {
    width: 40%;
}

.question-manager-interface .row-actions {
    visibility: hidden;
}

.question-manager-interface tr:hover .row-actions {
    visibility: visible;
}

@media (max-width: 768px) {
    .question-manager-interface .stats-card {
        grid-column: 1 / -1;
    }
    
    .question-manager-interface .wp-list-table {
        font-size: 14px;
    }
    
    .question-manager-interface .wp-list-table th,
    .question-manager-interface .wp-list-table td {
        padding: 8px 4px;
    }
}
</style>

<?php wp_reset_postdata(); ?>
