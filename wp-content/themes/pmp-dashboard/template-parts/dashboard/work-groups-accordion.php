<?php
/**
 * Work Groups Accordion Template Part
 */

$user_id = get_current_user_id();
$work_groups = get_posts(array(
    'post_type' => 'work_group',
    'posts_per_page' => -1,
    'orderby' => 'menu_order',
    'order' => 'ASC'
));

$wg_data = array(
    1 => array('color' => 'green', 'icon' => 'fas fa-users', 'status' => 'completed'),
    2 => array('color' => 'blue', 'icon' => 'fas fa-rocket', 'status' => 'completed'),
    3 => array('color' => 'primary', 'icon' => 'fas fa-cogs', 'status' => 'current'),
    4 => array('color' => 'green', 'icon' => 'fas fa-chart-line', 'status' => 'upcoming'),
    5 => array('color' => 'orange', 'icon' => 'fas fa-building', 'status' => 'upcoming')
);
?>

<div class="bg-white rounded-lg lg:rounded-xl shadow-sm border border-gray-200 p-4 lg:p-6">
    <h3 class="text-base lg:text-lg font-semibold text-gray-900 mb-4 lg:mb-6">Work Groups Progress</h3>
    
    <?php foreach ($work_groups as $index => $wg) : 
        $wg_number = $index + 1;
        $wg_info = $wg_data[$wg_number] ?? $wg_data[1];
        $wg_progress = pmp_get_work_group_progress($user_id, $wg->ID);
        
        $status_classes = array(
            'completed' => 'bg-green-50 hover:bg-green-100',
            'current' => 'bg-primary bg-opacity-10 hover:bg-primary hover:bg-opacity-20 border-2 border-primary',
            'upcoming' => 'bg-gray-50 hover:bg-gray-100'
        );
        
        $status_badges = array(
            'completed' => '<span class="ml-2 text-xs bg-green-600 text-white px-2 py-1 rounded">COMPLETED</span>',
            'current' => '<span class="ml-2 text-xs bg-primary text-white px-2 py-1 rounded">CURRENT</span>',
            'upcoming' => '<span class="ml-2 text-xs bg-gray-400 text-white px-2 py-1 rounded">UPCOMING</span>'
        );
        
        $button_class = $status_classes[$wg_info['status']] ?? $status_classes['upcoming'];
        $is_current = $wg_info['status'] === 'current';
    ?>
        <div class="border border-gray-200 rounded-lg mb-3 <?php echo $is_current ? 'border-2 border-primary' : ''; ?>">
            <button class="w-full px-4 py-3 text-left flex items-center justify-between <?php echo esc_attr($button_class); ?> rounded-lg" 
                    onclick="toggleAccordion('wg<?php echo $wg_number; ?>')">
                <div class="flex items-center">
                    <i class="<?php echo esc_attr($wg_info['icon']); ?> text-<?php echo esc_attr($wg_info['color']); ?>-600 mr-3"></i>
                    <span class="font-medium text-gray-900"><?php echo esc_html($wg->post_title); ?></span>
                    <?php echo $status_badges[$wg_info['status']]; ?>
                </div>
                <i class="fas fa-chevron-down text-gray-400" id="wg<?php echo $wg_number; ?>-icon"></i>
            </button>
            
            <div id="wg<?php echo $wg_number; ?>-content" class="<?php echo $is_current ? '' : 'hidden'; ?> px-4 pb-3">
                <div class="space-y-2 text-sm">
                    <?php
                    // Get lessons for this Work Group
                    $lessons = get_posts(array(
                        'post_type' => 'lesson',
                        'meta_query' => array(
                            array(
                                'key' => 'work_group_id',
                                'value' => $wg->ID,
                                'compare' => '='
                            )
                        ),
                        'orderby' => 'menu_order',
                        'order' => 'ASC',
                        'posts_per_page' => -1
                    ));
                    
                    if (empty($lessons)) {
                        // Default lessons for demo
                        $default_lessons = array(
                            1 => array('Build A Team', 'Define Team Ground Rules', 'Negotiate Project Agreements', 'Empower Team Members & Stakeholders', 'Ensure Adequate Training', 'Engage Virtual Teams', 'Address Impediments & Blockers'),
                            2 => array('Determine Project Methodology', 'Plan and Manage Scope', 'Plan & Manage Budget/Resources', 'Manage Project Artefacts', 'Plan & Manage Schedule', 'Plan & Manage Quality', 'Integrate Planning Activities', 'Plan & Manage Procurement', 'Establish Governance & Plan Closure'),
                            3 => array('Assess & Manage Risks', 'Execute for Value', 'Manage Communications', 'Engage Stakeholders', 'Manage Project Artefacts', 'Manage Project Changes', 'Manage Project Issues', 'Knowledge Transfer', 'Manage Suppliers'),
                            4 => array('Lead A Team', 'Support Team Performance', 'Collaborate with Stakeholders', 'Manage Conflict', 'Mentor Relevant Stakeholders', 'Build Shared Understanding', 'Promote Team Performance through EI'),
                            5 => array('Plan & Manage Compliance', 'Evaluate & Deliver Benefits/Value', 'Evaluate External Changes', 'Support Organisational Change')
                        );
                        
                        $lesson_list = $default_lessons[$wg_number] ?? array();
                        
                        foreach ($lesson_list as $lesson_index => $lesson_title) :
                            if ($wg_info['status'] === 'completed') {
                                $icon = 'fas fa-check-circle';
                                $color = 'text-' . $wg_info['color'] . '-600';
                            } elseif ($wg_info['status'] === 'current' && $lesson_index === 4) {
                                $icon = 'fas fa-play-circle';
                                $color = 'text-primary font-medium';
                            } elseif ($wg_info['status'] === 'current' && $lesson_index < 4) {
                                $icon = 'fas fa-check-circle';
                                $color = 'text-blue-600';
                            } else {
                                $icon = 'fas fa-circle';
                                $color = 'text-gray-400';
                            }
                        ?>
                            <div class="flex items-center <?php echo esc_attr($color); ?>">
                                <i class="<?php echo esc_attr($icon); ?> mr-2"></i>
                                <?php echo esc_html($lesson_title); ?>
                            </div>
                        <?php endforeach;
                    } else {
                        foreach ($lessons as $lesson) :
                            $lesson_progress = pmp_get_lesson_progress($user_id, $lesson->ID);
                            
                            if ($lesson_progress['status'] === 'completed') {
                                $icon = 'fas fa-check-circle';
                                $color = 'text-' . $wg_info['color'] . '-600';
                            } elseif ($lesson_progress['status'] === 'in_progress') {
                                $icon = 'fas fa-play-circle';
                                $color = 'text-primary font-medium';
                            } else {
                                $icon = 'fas fa-circle';
                                $color = 'text-gray-400';
                            }
                        ?>
                            <div class="flex items-center <?php echo esc_attr($color); ?>">
                                <i class="<?php echo esc_attr($icon); ?> mr-2"></i>
                                <a href="<?php echo get_permalink($lesson->ID); ?>" class="hover:underline">
                                    <?php echo esc_html($lesson->post_title); ?>
                                </a>
                            </div>
                        <?php endforeach;
                    } ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
