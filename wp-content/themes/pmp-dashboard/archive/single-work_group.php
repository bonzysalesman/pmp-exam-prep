<?php
/**
 * Single Work Group Template
 */

get_header();

if (!is_user_logged_in()) {
    wp_redirect(wp_login_url(get_permalink()));
    exit;
}

$user_id = get_current_user_id();
$wg_id = get_the_ID();
$wg_progress = pmp_get_work_group_progress($user_id, $wg_id);

// Get Work Group meta
$wg_number = get_post_meta($wg_id, 'wg_number', true) ?: 3;
$wg_domain = get_post_meta($wg_id, 'wg_domain', true) ?: 'Process Domain';
$wg_duration = get_post_meta($wg_id, 'wg_duration', true) ?: 'Weeks 6-8';

$wg_colors = array(
    1 => 'green', 2 => 'blue', 3 => 'primary', 4 => 'green', 5 => 'orange'
);
$color = $wg_colors[$wg_number] ?? 'primary';
?>

<div class="flex h-screen">
    <!-- Sidebar -->
    <div id="sidebar" class="fixed inset-y-0 left-0 z-50 w-64 bg-white shadow-lg transform -translate-x-full transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0 lg:block lg:flex-shrink-0 flex flex-col">
        <?php get_template_part('template-parts/navigation/sidebar-nav'); ?>
    </div>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-hidden">
        <!-- Header -->
        <header class="bg-white shadow-sm border-b border-gray-200 flex-shrink-0">
            <div class="flex items-center justify-between h-16 px-6">
                <div class="flex items-center">
                    <a href="<?php echo get_permalink(get_page_by_path('dashboard')); ?>" class="text-gray-500 hover:text-primary mr-4">
                        <i class="fas fa-arrow-left text-lg"></i>
                    </a>
                    <div>
                        <h1 class="text-xl font-semibold text-gray-900"><?php the_title(); ?></h1>
                        <p class="text-sm text-gray-500"><?php echo esc_html($wg_domain); ?> • <?php echo esc_html($wg_duration); ?></p>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-sm bg-<?php echo esc_attr($color); ?>-100 text-<?php echo esc_attr($color); ?>-800 px-3 py-1 rounded-full font-medium">
                        <?php echo esc_html($wg_progress['completion_percentage']); ?>% Complete
                    </span>
                </div>
            </div>
        </header>

        <main class="flex-1 overflow-y-auto p-6">
            <div class="max-w-4xl mx-auto">
                <!-- Progress Overview -->
                <div class="bg-gradient-to-r from-<?php echo esc_attr($color); ?>-500 to-<?php echo esc_attr($color); ?>-600 rounded-xl p-8 text-white mb-8">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <h2 class="text-3xl font-bold mb-2"><?php the_title(); ?></h2>
                            <p class="text-<?php echo esc_attr($color); ?>-100 mb-1"><?php echo esc_html($wg_domain); ?></p>
                            <p class="text-sm text-<?php echo esc_attr($color); ?>-200 mb-6">
                                <?php echo esc_html($wg_progress['completed_lessons']); ?> of <?php echo esc_html($wg_progress['total_lessons']); ?> lessons completed
                            </p>
                            <div class="w-full bg-white bg-opacity-20 rounded-full h-3 mb-4">
                                <div class="bg-white h-3 rounded-full" style="width: <?php echo esc_attr($wg_progress['completion_percentage']); ?>%"></div>
                            </div>
                            <?php if ($wg_progress['completion_percentage'] < 100) : ?>
                                <button class="bg-white text-<?php echo esc_attr($color); ?>-600 px-6 py-3 rounded-lg font-medium hover:bg-gray-100 transition-colors">
                                    <i class="fas fa-play mr-2"></i>Continue Learning
                                </button>
                            <?php else : ?>
                                <div class="flex items-center text-white">
                                    <i class="fas fa-check-circle mr-2"></i>
                                    Work Group Completed!
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="hidden lg:block ml-8">
                            <div class="w-32 h-32 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                                <span class="text-4xl font-bold"><?php echo esc_html($wg_progress['completion_percentage']); ?>%</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Lessons Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <?php
                    // Get lessons for this Work Group
                    $lessons = get_posts(array(
                        'post_type' => 'lesson',
                        'meta_query' => array(
                            array(
                                'key' => 'work_group_id',
                                'value' => $wg_id,
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
                            'Assess & Manage Risks' => 'completed',
                            'Execute for Value' => 'completed', 
                            'Manage Communications' => 'completed',
                            'Engage Stakeholders' => 'completed',
                            'Manage Project Artefacts' => 'current',
                            'Manage Project Changes' => 'upcoming',
                            'Manage Project Issues' => 'upcoming',
                            'Knowledge Transfer' => 'upcoming',
                            'Manage Suppliers' => 'upcoming'
                        );
                        
                        foreach ($default_lessons as $lesson_title => $status) :
                            $is_current = $status === 'current';
                            $is_completed = $status === 'completed';
                            $is_locked = $status === 'upcoming';
                    ?>
                            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 <?php echo $is_locked ? 'opacity-75' : ''; ?>">
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="text-lg font-semibold text-gray-900"><?php echo esc_html($lesson_title); ?></h3>
                                    <?php if ($is_completed) : ?>
                                        <i class="fas fa-check-circle text-<?php echo esc_attr($color); ?>-600 text-xl"></i>
                                    <?php elseif ($is_current) : ?>
                                        <i class="fas fa-play-circle text-primary text-xl"></i>
                                    <?php else : ?>
                                        <i class="fas fa-circle text-gray-400 text-xl"></i>
                                    <?php endif; ?>
                                </div>
                                <p class="text-gray-600 mb-4">Learn essential project management concepts and practical applications.</p>
                                <?php if ($is_completed) : ?>
                                    <button class="w-full bg-<?php echo esc_attr($color); ?>-600 text-white py-2 rounded-lg hover:bg-<?php echo esc_attr($color); ?>-700 transition-colors">
                                        <i class="fas fa-eye mr-2"></i>Review Lesson
                                    </button>
                                <?php elseif ($is_current) : ?>
                                    <button class="w-full bg-primary text-white py-2 rounded-lg hover:bg-primary-dark transition-colors">
                                        <i class="fas fa-play mr-2"></i>Start Lesson
                                    </button>
                                <?php else : ?>
                                    <button class="w-full bg-gray-400 text-white py-2 rounded-lg cursor-not-allowed">
                                        <i class="fas fa-lock mr-2"></i>Locked
                                    </button>
                                <?php endif; ?>
                            </div>
                    <?php endforeach;
                    } else {
                        foreach ($lessons as $lesson) :
                            $lesson_progress = pmp_get_lesson_progress($user_id, $lesson->ID);
                            $is_completed = $lesson_progress['status'] === 'completed';
                            $is_current = $lesson_progress['status'] === 'in_progress';
                            $is_locked = $lesson_progress['status'] === 'not_started';
                    ?>
                            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 <?php echo $is_locked ? 'opacity-75' : ''; ?>">
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="text-lg font-semibold text-gray-900"><?php echo esc_html($lesson->post_title); ?></h3>
                                    <?php if ($is_completed) : ?>
                                        <i class="fas fa-check-circle text-<?php echo esc_attr($color); ?>-600 text-xl"></i>
                                    <?php elseif ($is_current) : ?>
                                        <i class="fas fa-play-circle text-primary text-xl"></i>
                                    <?php else : ?>
                                        <i class="fas fa-circle text-gray-400 text-xl"></i>
                                    <?php endif; ?>
                                </div>
                                <p class="text-gray-600 mb-4"><?php echo esc_html(wp_trim_words($lesson->post_excerpt ?: $lesson->post_content, 15)); ?></p>
                                <a href="<?php echo get_permalink($lesson->ID); ?>" 
                                   class="block w-full text-center py-2 rounded-lg transition-colors <?php 
                                   if ($is_completed) {
                                       echo 'bg-' . esc_attr($color) . '-600 text-white hover:bg-' . esc_attr($color) . '-700';
                                   } elseif ($is_current) {
                                       echo 'bg-primary text-white hover:bg-primary-dark';
                                   } else {
                                       echo 'bg-gray-400 text-white cursor-not-allowed pointer-events-none';
                                   } ?>">
                                    <?php if ($is_completed) : ?>
                                        <i class="fas fa-eye mr-2"></i>Review Lesson
                                    <?php elseif ($is_current) : ?>
                                        <i class="fas fa-play mr-2"></i>Continue Lesson
                                    <?php else : ?>
                                        <i class="fas fa-lock mr-2"></i>Locked
                                    <?php endif; ?>
                                </a>
                            </div>
                    <?php endforeach;
                    } ?>
                </div>

                <!-- Work Group Description -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">About This Work Group</h3>
                    <div class="prose max-w-none">
                        <?php the_content(); ?>
                    </div>
                </div>

                <!-- Practice Test -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Work Group Assessment</h3>
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 mb-2">Test your knowledge of <?php the_title(); ?> concepts</p>
                            <?php if ($wg_progress['completion_percentage'] >= 80) : ?>
                                <p class="text-sm text-green-600 font-medium">Ready to take the assessment</p>
                            <?php else : ?>
                                <p class="text-sm text-gray-500">Complete 80% of lessons to unlock</p>
                            <?php endif; ?>
                        </div>
                        <?php if ($wg_progress['completion_percentage'] >= 80) : ?>
                            <button class="bg-<?php echo esc_attr($color); ?>-600 text-white px-6 py-2 rounded-lg hover:bg-<?php echo esc_attr($color); ?>-700 transition-colors">
                                <i class="fas fa-clipboard-check mr-2"></i>Take Assessment
                            </button>
                        <?php else : ?>
                            <button class="bg-gray-400 text-white px-6 py-2 rounded-lg cursor-not-allowed">
                                <i class="fas fa-lock mr-2"></i>Locked
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Mobile Bottom Navigation -->
<div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 md:hidden z-50">
    <?php get_template_part('template-parts/navigation/mobile-nav'); ?>
</div>

<!-- Sidebar Overlay -->
<div id="sidebarOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden hidden"></div>

<?php get_footer(); ?>
