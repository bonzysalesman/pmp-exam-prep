<?php
/**
 * Progress Cards Component
 * 
 * Displays domain-specific progress cards with visual indicators
 */

$user_id = get_current_user_id();
$progress_data = PMP_Progress_Tracker::get_user_progress($user_id);

$domain_info = [
    'people' => [
        'name' => 'People',
        'description' => 'Team building, leadership, conflict resolution',
        'weight' => '42%',
        'color' => 'bg-green-500',
        'icon' => 'fas fa-users'
    ],
    'process' => [
        'name' => 'Process', 
        'description' => 'Project lifecycle, methodologies, tools',
        'weight' => '50%',
        'color' => 'bg-blue-500',
        'icon' => 'fas fa-cogs'
    ],
    'business_environment' => [
        'name' => 'Business Environment',
        'description' => 'Organizational strategy, compliance, benefits', 
        'weight' => '8%',
        'color' => 'bg-orange-500',
        'icon' => 'fas fa-building'
    ]
];
?>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <?php foreach ($domain_info as $domain_key => $domain): ?>
        <?php 
        $domain_progress = $progress_data['domains'][$domain_key] ?? [
            'completion_percentage' => 0,
            'lessons_completed' => 0,
            'total_lessons' => 0,
            'time_spent_minutes' => 0
        ];
        $percentage = $domain_progress['completion_percentage'];
        ?>
        
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow duration-300">
            <!-- Domain Header -->
            <div class="flex items-center mb-4">
                <div class="w-12 h-12 <?php echo esc_attr($domain['color']); ?> rounded-full flex items-center justify-center mr-4">
                    <i class="<?php echo esc_attr($domain['icon']); ?> text-white text-lg"></i>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900"><?php echo esc_html($domain['name']); ?></h3>
                    <p class="text-sm text-gray-500"><?php echo esc_html($domain['weight']); ?> of exam</p>
                </div>
            </div>
            
            <!-- Progress Bar -->
            <div class="mb-4">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-sm font-medium text-gray-700">Progress</span>
                    <span class="text-sm font-bold text-gray-900"><?php echo number_format($percentage, 1); ?>%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-3">
                    <div class="<?php echo esc_attr($domain['color']); ?> h-3 rounded-full transition-all duration-500" 
                         style="width: <?php echo esc_attr($percentage); ?>%"
                         role="progressbar" 
                         aria-valuenow="<?php echo esc_attr($percentage); ?>" 
                         aria-valuemin="0" 
                         aria-valuemax="100"
                         aria-label="<?php echo esc_attr($domain['name']); ?> progress: <?php echo number_format($percentage, 1); ?>%">
                    </div>
                </div>
            </div>
            
            <!-- Stats -->
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-gray-500">Lessons</p>
                    <p class="font-semibold text-gray-900">
                        <?php echo esc_html($domain_progress['lessons_completed']); ?> / <?php echo esc_html($domain_progress['total_lessons']); ?>
                    </p>
                </div>
                <div>
                    <p class="text-gray-500">Time Spent</p>
                    <p class="font-semibold text-gray-900">
                        <?php 
                        $hours = floor($domain_progress['time_spent_minutes'] / 60);
                        $minutes = $domain_progress['time_spent_minutes'] % 60;
                        if ($hours > 0) {
                            echo esc_html($hours . 'h ' . $minutes . 'm');
                        } else {
                            echo esc_html($minutes . 'm');
                        }
                        ?>
                    </p>
                </div>
            </div>
            
            <!-- Description -->
            <p class="text-xs text-gray-600 mt-3 leading-relaxed"><?php echo esc_html($domain['description']); ?></p>
        </div>
    <?php endforeach; ?>
</div>

<!-- Overall Progress Summary -->
<div class="bg-gradient-to-r from-primary to-primary-dark rounded-lg p-6 text-white mb-8">
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-xl font-bold mb-2">Overall Progress</h3>
            <p class="text-primary-100">Your weighted progress across all domains</p>
        </div>
        <div class="text-right">
            <div class="text-4xl font-black mb-1"><?php echo number_format($progress_data['overall_progress'], 1); ?>%</div>
            <div class="text-sm text-primary-100">
                <?php echo esc_html($progress_data['total_lessons_completed']); ?> lessons completed
            </div>
        </div>
    </div>
    
    <!-- Overall Progress Bar -->
    <div class="mt-4">
        <div class="w-full bg-primary-dark bg-opacity-30 rounded-full h-2">
            <div class="bg-white h-2 rounded-full transition-all duration-700" 
                 style="width: <?php echo esc_attr($progress_data['overall_progress']); ?>%">
            </div>
        </div>
    </div>
</div>
?>
