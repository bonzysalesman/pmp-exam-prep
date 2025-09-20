<?php
/**
 * Dashboard Activity Feed Template Part
 */

$user_id = get_current_user_id();
$recent_activity = pmp_get_recent_activity($user_id, 5);
?>

<div class="bg-white rounded-lg lg:rounded-xl shadow-sm border border-gray-200 p-4 lg:p-6">
    <div class="flex items-center justify-between mb-4 lg:mb-6">
        <h3 class="text-base lg:text-lg font-semibold text-gray-900">Recent Activity</h3>
        <button class="text-xs lg:text-sm text-primary hover:text-primary-dark font-medium">View All</button>
    </div>
    <div class="space-y-3 lg:space-y-4">
        <?php if (!empty($recent_activity)) : ?>
            <?php foreach ($recent_activity as $activity) : ?>
                <div class="flex items-start space-x-3 lg:space-x-4">
                    <div class="w-8 lg:w-10 h-8 lg:h-10 bg-primary rounded-full flex items-center justify-center flex-shrink-0">
                        <?php if ($activity->type === 'lesson') : ?>
                            <i class="fas fa-play text-white text-xs lg:text-sm"></i>
                        <?php else : ?>
                            <i class="fas fa-clipboard-check text-white text-xs lg:text-sm"></i>
                        <?php endif; ?>
                    </div>
                    <div class="flex-1 min-w-0">
                        <?php if ($activity->type === 'lesson') : ?>
                            <p class="text-xs lg:text-sm font-medium text-gray-900">Completed: <?php echo esc_html($activity->post_title); ?></p>
                            <p class="text-xs lg:text-sm text-gray-600">Lesson completed successfully</p>
                            <p class="text-xs text-gray-500 mt-1"><?php echo human_time_diff(strtotime($activity->completed_at)); ?> ago</p>
                        <?php else : ?>
                            <p class="text-xs lg:text-sm font-medium text-gray-900">Test: <?php echo esc_html($activity->post_title); ?></p>
                            <p class="text-xs lg:text-sm text-gray-600">Score: <?php echo esc_html($activity->percentage); ?>%</p>
                            <p class="text-xs text-gray-500 mt-1"><?php echo human_time_diff(strtotime($activity->taken_at)); ?> ago</p>
                        <?php endif; ?>
                    </div>
                    <div class="text-xs lg:text-sm font-medium text-success">+15 XP</div>
                </div>
            <?php endforeach; ?>
        <?php else : ?>
            <!-- Default activity for demo -->
            <div class="flex items-start space-x-3 lg:space-x-4">
                <div class="w-8 lg:w-10 h-8 lg:h-10 bg-primary rounded-full flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-play text-white text-xs lg:text-sm"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs lg:text-sm font-medium text-gray-900">Completed: Risk Identification Techniques</p>
                    <p class="text-xs lg:text-sm text-gray-600">Risk Management Fundamentals</p>
                    <p class="text-xs text-gray-500 mt-1">2 hours ago</p>
                </div>
                <div class="text-xs lg:text-sm font-medium text-success">+15 XP</div>
            </div>
            
            <div class="flex items-start space-x-3 lg:space-x-4">
                <div class="w-8 lg:w-10 h-8 lg:h-10 bg-success rounded-full flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-certificate text-white text-xs lg:text-sm"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs lg:text-sm font-medium text-gray-900">Earned Certificate: Agile Project Management</p>
                    <p class="text-xs lg:text-sm text-gray-600">Score: 95%</p>
                    <p class="text-xs text-gray-500 mt-1">1 day ago</p>
                </div>
                <div class="text-xs lg:text-sm font-medium text-success">+50 XP</div>
            </div>
            
            <div class="flex items-start space-x-3 lg:space-x-4">
                <div class="w-8 lg:w-10 h-8 lg:h-10 bg-blue-500 rounded-full flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-users text-white text-xs lg:text-sm"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs lg:text-sm font-medium text-gray-900">Joined Study Group Discussion</p>
                    <p class="text-xs lg:text-sm text-gray-600">Topic: Stakeholder Management</p>
                    <p class="text-xs text-gray-500 mt-1">3 days ago</p>
                </div>
                <div class="text-xs lg:text-sm font-medium text-primary">+5 XP</div>
            </div>
        <?php endif; ?>
    </div>
</div>
