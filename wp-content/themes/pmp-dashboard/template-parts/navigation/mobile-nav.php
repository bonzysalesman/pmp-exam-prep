<?php
/**
 * Mobile Bottom Navigation Template Part
 */

$current_page = get_queried_object();
$is_dashboard = is_page_template('page-dashboard.php') || is_page('dashboard');
$is_lesson = is_singular('lesson');
$is_practice = is_singular('practice_test') || is_post_type_archive('practice_test');
$is_resources = is_page('resources');
?>

<div class="grid grid-cols-4 gap-1">
    <a href="<?php echo get_permalink(get_page_by_path('dashboard')); ?>" 
       class="flex flex-col items-center py-2 px-1 <?php echo $is_dashboard ? 'text-primary' : 'text-gray-600'; ?>">
        <i class="fas fa-tachometer-alt text-lg mb-1"></i>
        <span class="text-xs <?php echo $is_dashboard ? 'font-medium' : ''; ?>">Dashboard</span>
    </a>
    
    <?php
    // Get current Work Group or default to WG3
    $current_wg = get_posts(array(
        'post_type' => 'work_group',
        'meta_query' => array(
            array(
                'key' => 'wg_current',
                'value' => '1',
                'compare' => '='
            )
        ),
        'posts_per_page' => 1
    ));
    
    if (empty($current_wg)) {
        $current_wg = get_posts(array(
            'post_type' => 'work_group',
            'posts_per_page' => 1,
            'orderby' => 'menu_order',
            'order' => 'ASC',
            'offset' => 2 // Get WG3 (index 2)
        ));
    }
    
    $current_wg_url = !empty($current_wg) ? get_permalink($current_wg[0]->ID) : get_post_type_archive_link('work_group');
    ?>
    
    <a href="<?php echo esc_url($current_wg_url); ?>" 
       class="flex flex-col items-center py-2 px-1 <?php echo ($is_lesson || is_singular('work_group')) ? 'text-primary' : 'text-gray-600'; ?>">
        <i class="fas fa-cogs text-lg mb-1"></i>
        <span class="text-xs">Current</span>
    </a>
    
    <a href="<?php echo get_post_type_archive_link('practice_test'); ?>" 
       class="flex flex-col items-center py-2 px-1 <?php echo $is_practice ? 'text-primary' : 'text-gray-600'; ?>">
        <i class="fas fa-clipboard-check text-lg mb-1"></i>
        <span class="text-xs">Tests</span>
    </a>
    
    <a href="<?php echo get_permalink(get_page_by_path('resources')); ?>" 
       class="flex flex-col items-center py-2 px-1 <?php echo $is_resources ? 'text-primary' : 'text-gray-600'; ?>">
        <i class="fas fa-download text-lg mb-1"></i>
        <span class="text-xs">Resources</span>
    </a>
</div>
