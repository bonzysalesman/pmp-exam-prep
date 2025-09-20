<?php
/**
 * Sidebar Navigation Template Part
 */

$user_id = get_current_user_id();
$user_progress = pmp_get_user_progress($user_id);

// Get current Work Group and lesson
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

$current_wg = !empty($current_wg) ? $current_wg[0] : null;
?>

<div class="flex items-center justify-between h-16 px-4 sm:px-6 border-b border-gray-200 flex-shrink-0">
    <div class="flex items-center">
        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/pmp-exam-prep-logo.png" alt="PMP Exam Prep" class="w-16 sm:w-20 h-auto">
        <span class="ml-2 sm:ml-3 text-base sm:text-lg font-semibold text-secondary hidden sm:block">DASHBOARD</span>
    </div>
    <button id="closeSidebar" class="lg:hidden text-gray-500 hover:text-gray-700">
        <i class="fas fa-times text-xl"></i>
    </button>
</div>

<!-- Scrollable Content -->
<div class="flex-1 overflow-y-auto" style="background: #FFF;">
    <!-- Current Course Progress -->
    <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
        <div class="bg-primary bg-opacity-10 rounded-lg p-3 sm:p-4 border-l-4 border-primary">
            <div class="flex items-center justify-between mb-2">
                <h4 class="text-xs sm:text-sm font-semibold text-primary">
                    <?php echo $current_wg ? esc_html($current_wg->post_title) : 'WG3: Doing the Work'; ?>
                </h4>
                <span class="text-xs text-primary font-medium">Week 7 of 13</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2 mb-2">
                <div class="bg-primary h-2 rounded-full" style="width: <?php echo esc_attr($user_progress['completion_percentage']); ?>%"></div>
            </div>
            <p class="text-xs text-gray-600">
                <?php echo esc_html($user_progress['completed_lessons']); ?> of <?php echo esc_html($user_progress['total_lessons']); ?> lessons completed
            </p>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="mt-4">
        <div class="space-y-1">
            <a href="<?php echo esc_url(get_permalink(get_page_by_path('dashboard'))); ?>" class="flex items-center px-6 py-3 text-sm font-medium text-primary bg-primary bg-opacity-10 border-r-2 border-primary">
                <i class="fas fa-tachometer-alt w-5 mr-3"></i>
                Dashboard
            </a>
            
            <?php
            // Get Work Groups
            $work_groups = get_posts(array(
                'post_type' => 'work_group',
                'posts_per_page' => -1,
                'orderby' => 'menu_order',
                'order' => 'ASC'
            ));
            
            $wg_colors = array(
                1 => 'text-green-600',
                2 => 'text-blue-600', 
                3 => 'text-blue-600',
                4 => 'text-green-600',
                5 => 'text-orange-600'
            );
            
            $wg_icons = array(
                1 => 'fas fa-users',
                2 => 'fas fa-rocket',
                3 => 'fas fa-cogs',
                4 => 'fas fa-chart-line',
                5 => 'fas fa-building'
            );
            
            foreach ($work_groups as $index => $wg) :
                $wg_number = $index + 1;
                $wg_progress = pmp_get_work_group_progress($user_id, $wg->ID);
                $is_current = ($wg_number == 3); // Current WG
                $link_class = $is_current ? 'text-primary bg-gray-50' : 'text-gray-600 hover:text-primary hover:bg-gray-50';
            ?>
                <a href="<?php echo esc_url(get_permalink($wg->ID)); ?>" class="flex items-center px-6 py-3 text-sm font-medium <?php echo esc_attr($link_class); ?>">
                    <i class="<?php echo esc_attr($wg_icons[$wg_number] ?? 'fas fa-circle'); ?> w-5 mr-3 <?php echo esc_attr($wg_colors[$wg_number] ?? 'text-gray-600'); ?>"></i>
                    <?php echo esc_html($wg->post_title); ?>
                </a>
            <?php endforeach; ?>
            
            <a href="<?php echo esc_url(get_post_type_archive_link('practice_test')); ?>" class="flex items-center px-6 py-3 text-sm font-medium text-gray-600 hover:text-primary hover:bg-gray-50">
                <i class="fas fa-clipboard-check w-5 mr-3 text-purple-600"></i>
                Practice Tests
            </a>
            <a href="<?php echo esc_url(get_permalink(get_page_by_path('resources'))); ?>" class="flex items-center px-6 py-3 text-sm font-medium text-gray-600 hover:text-primary hover:bg-gray-50">
                <i class="fas fa-download w-5 mr-3"></i>
                Resources
            </a>
        </div>

        <!-- Study Stats -->
        <div class="px-6 mt-8 pt-6 border-t border-gray-200 pb-6">
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-4">Study Stats</h3>
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Study Streak</span>
                    <span class="text-sm font-semibold text-success flex items-center study-streak">
                        <i class="fas fa-fire text-orange-500 mr-1"></i><?php echo esc_html($user_progress['study_streak']); ?> days
                    </span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">This Week</span>
                    <span class="text-sm font-semibold text-primary week-time"><?php echo esc_html($user_progress['week_time']); ?>h</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Overall</span>
                    <span class="text-sm font-semibold text-primary overall-progress"><?php echo esc_html($user_progress['completion_percentage']); ?>%</span>
                </div>
            </div>
        </div>
    </nav>
</div>
