<?php
/**
 * Study Statistics Template Part
 */

$user_id = get_current_user_id();
$user_progress = pmp_get_user_progress($user_id);
?>

<div class="bg-white rounded-lg lg:rounded-xl shadow-sm border border-gray-200 p-4 lg:p-6">
    <h3 class="text-base lg:text-lg font-semibold text-gray-900 mb-4 lg:mb-6">Study Statistics</h3>
    <div class="grid grid-cols-2 gap-3 lg:gap-4 mb-4 lg:mb-6">
        <div class="text-center">
            <div class="text-2xl lg:text-3xl font-bold text-primary mb-1">
                <?php echo esc_html(round($user_progress['total_time_spent'] / 3600, 1)); ?>
            </div>
            <div class="text-xs lg:text-sm text-gray-600">Hours Studied</div>
        </div>
        <div class="text-center">
            <div class="text-2xl lg:text-3xl font-bold text-success mb-1">
                <?php echo esc_html($user_progress['completed_lessons']); ?>
            </div>
            <div class="text-xs lg:text-sm text-gray-600">Lessons Done</div>
        </div>
    </div>
    <div class="space-y-3 lg:space-y-4">
        <div class="flex items-center justify-between">
            <span class="text-xs lg:text-sm text-gray-600">Practice Tests</span>
            <span class="text-xs lg:text-sm font-semibold text-gray-900">5 completed</span>
        </div>
        <div class="flex items-center justify-between">
            <span class="text-xs lg:text-sm text-gray-600">Average Score</span>
            <span class="text-xs lg:text-sm font-semibold text-success">87%</span>
        </div>
        <div class="flex items-center justify-between">
            <span class="text-xs lg:text-sm text-gray-600">Exam Readiness</span>
            <span class="text-xs lg:text-sm font-semibold text-primary">
                <?php echo esc_html($user_progress['completion_percentage']); ?>%
            </span>
        </div>
        <div class="flex items-center justify-between">
            <span class="text-xs lg:text-sm text-gray-600">Study Streak</span>
            <span class="text-xs lg:text-sm font-semibold text-orange-600 flex items-center">
                <i class="fas fa-fire mr-1"></i><?php echo esc_html($user_progress['study_streak']); ?> days
            </span>
        </div>
    </div>
    
    <!-- Progress Chart Placeholder -->
    <div class="mt-6 pt-6 border-t border-gray-200">
        <h4 class="text-sm font-semibold text-gray-900 mb-3">Weekly Progress</h4>
        <div class="flex items-end justify-between h-20 space-x-1">
            <?php for ($i = 0; $i < 7; $i++) : 
                $height = rand(20, 80);
                $day = date('D', strtotime("-$i days"));
            ?>
                <div class="flex flex-col items-center flex-1">
                    <div class="bg-primary rounded-t w-full mb-1" style="height: <?php echo $height; ?>%"></div>
                    <span class="text-xs text-gray-500"><?php echo $day; ?></span>
                </div>
            <?php endfor; ?>
        </div>
    </div>
</div>
