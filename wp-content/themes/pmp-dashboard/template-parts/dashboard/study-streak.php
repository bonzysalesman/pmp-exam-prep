<?php
/**
 * Study Streak Display Component
 * 
 * Shows current study streak with motivational messaging and milestones
 */

$user_id = get_current_user_id();
$streak_data = PMP_Progress_Tracker::get_study_streak($user_id);

// Determine streak badge color
$badge_color = 'bg-gray-500';
if ($streak_data['current_streak'] >= 30) {
    $badge_color = 'bg-purple-500';
} elseif ($streak_data['current_streak'] >= 14) {
    $badge_color = 'bg-yellow-500';
} elseif ($streak_data['current_streak'] >= 7) {
    $badge_color = 'bg-green-500';
} elseif ($streak_data['current_streak'] >= 3) {
    $badge_color = 'bg-blue-500';
}

// Calculate days until next milestone
$days_to_milestone = $streak_data['next_milestone'] - $streak_data['current_streak'];
?>

<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center">
            <div class="w-12 h-12 bg-orange-500 rounded-full flex items-center justify-center mr-4">
                <i class="fas fa-fire text-white text-lg"></i>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-gray-900">Study Streak</h3>
                <p class="text-sm text-gray-500">Keep the momentum going!</p>
            </div>
        </div>
        
        <!-- Current Streak Badge -->
        <div class="text-center">
            <div class="<?php echo esc_attr($badge_color); ?> text-white rounded-full w-16 h-16 flex items-center justify-center mb-2">
                <span class="text-2xl font-bold"><?php echo esc_html($streak_data['current_streak']); ?></span>
            </div>
            <p class="text-xs text-gray-600">
                <?php echo $streak_data['current_streak'] === 1 ? 'day' : 'days'; ?>
            </p>
        </div>
    </div>
    
    <!-- Streak Message -->
    <div class="bg-gray-50 rounded-lg p-4 mb-6">
        <p class="text-center text-gray-700 font-medium">
            <?php echo esc_html($streak_data['streak_message']); ?>
        </p>
    </div>
    
    <!-- Streak Stats -->
    <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="text-center">
            <div class="text-2xl font-bold text-gray-900"><?php echo esc_html($streak_data['current_streak']); ?></div>
            <div class="text-xs text-gray-500 uppercase tracking-wide">Current</div>
        </div>
        <div class="text-center">
            <div class="text-2xl font-bold text-gray-900"><?php echo esc_html($streak_data['longest_streak']); ?></div>
            <div class="text-xs text-gray-500 uppercase tracking-wide">Best</div>
        </div>
        <div class="text-center">
            <div class="text-2xl font-bold text-gray-900"><?php echo esc_html($streak_data['total_study_days']); ?></div>
            <div class="text-xs text-gray-500 uppercase tracking-wide">Total Days</div>
        </div>
    </div>
    
    <!-- Next Milestone -->
    <?php if ($days_to_milestone > 0): ?>
        <div class="border-t border-gray-200 pt-4">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-700">Next Milestone</span>
                <span class="text-sm font-bold text-primary"><?php echo esc_html($streak_data['next_milestone']); ?> days</span>
            </div>
            
            <!-- Progress to next milestone -->
            <div class="w-full bg-gray-200 rounded-full h-2 mb-2">
                <?php 
                $milestone_progress = ($streak_data['current_streak'] / $streak_data['next_milestone']) * 100;
                ?>
                <div class="bg-primary h-2 rounded-full transition-all duration-500" 
                     style="width: <?php echo esc_attr($milestone_progress); ?>%">
                </div>
            </div>
            
            <p class="text-xs text-gray-600 text-center">
                <?php echo esc_html($days_to_milestone); ?> more 
                <?php echo $days_to_milestone === 1 ? 'day' : 'days'; ?> to reach your next milestone!
            </p>
        </div>
    <?php endif; ?>
    
    <!-- Last Study Date -->
    <?php if ($streak_data['last_study_date']): ?>
        <div class="mt-4 pt-4 border-t border-gray-200">
            <p class="text-xs text-gray-500 text-center">
                Last studied: <?php echo esc_html(date('M j, Y', strtotime($streak_data['last_study_date']))); ?>
            </p>
        </div>
    <?php endif; ?>
</div>

<!-- Streak Milestones Info -->
<div class="mt-4 bg-blue-50 rounded-lg p-4">
    <h4 class="text-sm font-semibold text-blue-900 mb-2">Streak Milestones</h4>
    <div class="grid grid-cols-2 gap-2 text-xs">
        <div class="flex items-center">
            <div class="w-3 h-3 bg-blue-500 rounded-full mr-2"></div>
            <span class="text-blue-800">7 days - Building habit</span>
        </div>
        <div class="flex items-center">
            <div class="w-3 h-3 bg-green-500 rounded-full mr-2"></div>
            <span class="text-blue-800">14 days - Strong routine</span>
        </div>
        <div class="flex items-center">
            <div class="w-3 h-3 bg-yellow-500 rounded-full mr-2"></div>
            <span class="text-blue-800">30 days - Dedicated learner</span>
        </div>
        <div class="flex items-center">
            <div class="w-3 h-3 bg-purple-500 rounded-full mr-2"></div>
            <span class="text-blue-800">60+ days - Study champion</span>
        </div>
    </div>
</div>
