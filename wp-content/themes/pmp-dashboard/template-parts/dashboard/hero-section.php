<?php
/**
 * Dashboard Hero Section Template Part
 */

$user_id = get_current_user_id();
$user_progress = pmp_get_user_progress($user_id);

// Get current lesson
$current_lesson = get_posts(array(
    'post_type' => 'lesson',
    'meta_query' => array(
        array(
            'key' => 'current_lesson',
            'value' => '1',
            'compare' => '='
        )
    ),
    'posts_per_page' => 1
));

$current_lesson = !empty($current_lesson) ? $current_lesson[0] : null;
?>

<div class="bg-gradient-to-r from-primary to-primary-dark rounded-lg lg:rounded-xl p-4 sm:p-6 lg:p-8 text-white mb-6 lg:mb-8">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
        <div class="flex-1 mb-4 lg:mb-0">
            <h2 class="text-xl sm:text-2xl lg:text-3xl font-bold mb-2 uppercase" style="line-height: 1.1618;">Continue Learning</h2>
            <p class="text-primary-100 mb-1 text-sm sm:text-base">
                <?php echo $current_lesson ? esc_html($current_lesson->post_title) : 'WG3: Doing the Work'; ?>
            </p>
            <p class="text-xs sm:text-sm text-primary-200 mb-4 lg:mb-6">
                <?php if ($current_lesson) : ?>
                    <?php echo esc_html(get_post_meta($current_lesson->ID, 'lesson_duration', true) ?: '12'); ?> min remaining
                <?php else : ?>
                    Day 43: Manage Project Artefacts • 12 min remaining
                <?php endif; ?>
            </p>
            <div class="flex flex-col sm:flex-row gap-3 lg:gap-4">
                <a href="<?php echo $current_lesson ? get_permalink($current_lesson->ID) : '#'; ?>" 
                   class="bg-white text-primary px-4 sm:px-6 py-2 sm:py-3 rounded-lg font-medium hover:bg-gray-100 transition-colors flex items-center justify-center text-sm sm:text-base">
                    <i class="fas fa-play mr-2"></i>Resume Lesson
                </a>
                <a href="<?php echo get_post_type_archive_link('work_group'); ?>" 
                   class="border border-white border-opacity-30 text-white px-4 sm:px-6 py-2 sm:py-3 rounded-lg font-medium hover:bg-white hover:bg-opacity-10 transition-colors text-sm sm:text-base">
                    View Course
                </a>
            </div>
        </div>
        <div class="hidden lg:block ml-8">
            <div class="w-24 lg:w-32 h-24 lg:h-32 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                <i class="fas fa-play text-2xl lg:text-4xl"></i>
            </div>
        </div>
    </div>
</div>
