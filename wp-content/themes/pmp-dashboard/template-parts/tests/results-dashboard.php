<?php
/**
 * Test Results Dashboard
 * Task: T021 - Test Results Dashboard
 */

// Get session ID from URL
$session_id = $_GET['results'] ?? $_GET['session'] ?? null;
$current_user_id = get_current_user_id();

if (!$session_id || !$current_user_id) {
    echo '<div class="error">Invalid results session.</div>';
    return;
}

// Get results analysis
$analysis = PMP_Results_Analyzer::analyze_results($session_id);

if (is_wp_error($analysis)) {
    echo '<div class="error">Error: ' . $analysis->get_error_message() . '</div>';
    return;
}

$basic_stats = $analysis['basic_stats'];
$domain_analysis = $analysis['domain_analysis'];
$weak_areas = $analysis['weak_areas'];
$recommendations = $analysis['recommendations'];
$comparison = $analysis['comparison'];
$time_analysis = $analysis['time_analysis'];
?>

<div class="results-dashboard bg-gray-50 min-h-screen">
    
    <!-- Results Header -->
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="text-center">
                
                <!-- Pass/Fail Status -->
                <div class="mb-6">
                    <?php if ($basic_stats['passed']): ?>
                        <div class="inline-flex items-center px-6 py-3 bg-green-100 text-green-800 rounded-full text-lg font-semibold">
                            <i class="fas fa-check-circle mr-3 text-2xl"></i>
                            Congratulations! You Passed
                        </div>
                    <?php else: ?>
                        <div class="inline-flex items-center px-6 py-3 bg-red-100 text-red-800 rounded-full text-lg font-semibold">
                            <i class="fas fa-times-circle mr-3 text-2xl"></i>
                            Keep Studying - You Can Do This!
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Score Display -->
                <div class="mb-8">
                    <div class="text-6xl font-bold text-gray-900 mb-2"><?php echo $basic_stats['score']; ?>%</div>
                    <div class="text-xl text-gray-600">
                        <?php echo $basic_stats['correct_answers']; ?> of <?php echo $basic_stats['total_questions']; ?> questions correct
                    </div>
                    <div class="text-lg text-gray-500 mt-2">
                        Grade: <span class="font-semibold"><?php echo $basic_stats['grade']; ?></span>
                    </div>
                </div>
                
                <!-- Quick Stats -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-blue-600"><?php echo $basic_stats['time_spent']; ?> min</div>
                        <div class="text-sm text-gray-600">Time Spent</div>
                    </div>
                    
                    <div class="text-center">
                        <div class="text-2xl font-bold text-purple-600"><?php echo $comparison['percentile']; ?>th</div>
                        <div class="text-sm text-gray-600">Percentile</div>
                    </div>
                    
                    <div class="text-center">
                        <div class="text-2xl font-bold text-green-600"><?php echo count($domain_analysis); ?></div>
                        <div class="text-sm text-gray-600">Domains Tested</div>
                    </div>
                    
                    <div class="text-center">
                        <div class="text-2xl font-bold text-orange-600"><?php echo $time_analysis['time_management_score']; ?></div>
                        <div class="text-sm text-gray-600">Time Management</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Results Content -->
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Main Results -->
            <div class="lg:col-span-2 space-y-8">
                
                <!-- Domain Performance -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-6">Domain Performance</h2>
                    
                    <div class="space-y-6">
                        <?php foreach ($domain_analysis as $domain => $data): ?>
                            <div class="domain-result">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center">
                                        <h3 class="font-medium text-gray-900"><?php echo $data['name']; ?></h3>
                                        <span class="ml-2 px-2 py-1 bg-gray-100 text-gray-600 rounded-full text-xs">
                                            <?php echo $data['target_weight']; ?>% of exam
                                        </span>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-lg font-semibold <?php echo $data['needs_improvement'] ? 'text-red-600' : 'text-green-600'; ?>">
                                            <?php echo $data['percentage']; ?>%
                                        </span>
                                        <div class="text-sm text-gray-500">
                                            <?php echo $data['correct']; ?>/<?php echo $data['total']; ?> correct
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Progress Bar -->
                                <div class="w-full bg-gray-200 rounded-full h-3 mb-2">
                                    <div class="h-3 rounded-full transition-all duration-500 <?php echo $data['needs_improvement'] ? 'bg-red-500' : 'bg-green-500'; ?>" 
                                         style="width: <?php echo min($data['percentage'], 100); ?>%"></div>
                                </div>
                                
                                <!-- Performance Level -->
                                <div class="flex items-center justify-between text-sm">
                                    <span class="px-2 py-1 rounded-full text-xs font-medium
                                        <?php echo $data['strength_level'] === 'strength' ? 'bg-green-100 text-green-800' : 
                                                  ($data['strength_level'] === 'adequate' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'); ?>">
                                        <?php echo $data['performance_level']; ?>
                                    </span>
                                    
                                    <?php if ($data['needs_improvement']): ?>
                                        <span class="text-red-600 font-medium">
                                            <i class="fas fa-arrow-up mr-1"></i>
                                            Need <?php echo 70 - $data['percentage']; ?>% improvement
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Performance Chart -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-6">Performance Breakdown</h2>
                    
                    <div class="relative">
                        <canvas id="performance-chart" width="400" height="200"></canvas>
                    </div>
                </div>
                
                <!-- Time Analysis -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-6">Time Management Analysis</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="font-medium text-gray-900 mb-3">Overall Time Usage</h3>
                            <div class="space-y-3">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Time Limit:</span>
                                    <span class="font-medium"><?php echo $time_analysis['time_limit_minutes']; ?> minutes</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Time Used:</span>
                                    <span class="font-medium"><?php echo $time_analysis['time_used_minutes']; ?> minutes</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Time Remaining:</span>
                                    <span class="font-medium"><?php echo $time_analysis['time_remaining_minutes']; ?> minutes</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Usage:</span>
                                    <span class="font-medium <?php echo $time_analysis['time_used_percentage'] > 90 ? 'text-red-600' : 'text-green-600'; ?>">
                                        <?php echo $time_analysis['time_used_percentage']; ?>%
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <h3 class="font-medium text-gray-900 mb-3">Pace Analysis</h3>
                            <div class="space-y-3">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Pace Rating:</span>
                                    <span class="font-medium"><?php echo $time_analysis['pace_analysis']['pace_rating']; ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Fast Answers:</span>
                                    <span class="font-medium"><?php echo $time_analysis['pace_analysis']['fast_answers']; ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Slow Answers:</span>
                                    <span class="font-medium"><?php echo $time_analysis['pace_analysis']['slow_answers']; ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Management Score:</span>
                                    <span class="font-medium"><?php echo $time_analysis['time_management_score']; ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Sidebar -->
            <div class="lg:col-span-1 space-y-6">
                
                <!-- Weak Areas -->
                <?php if (!empty($weak_areas)): ?>
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            <i class="fas fa-exclamation-triangle text-yellow-500 mr-2"></i>
                            Areas for Improvement
                        </h3>
                        
                        <div class="space-y-4">
                            <?php foreach (array_slice($weak_areas, 0, 3) as $area): ?>
                                <div class="p-3 bg-red-50 rounded-lg">
                                    <div class="font-medium text-red-900"><?php echo $area['name']; ?></div>
                                    <div class="text-sm text-red-700 mt-1">
                                        <?php echo $area['percentage']; ?>% - Need <?php echo round($area['gap'], 1); ?>% improvement
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Recommendations -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-lightbulb text-yellow-500 mr-2"></i>
                        Recommendations
                    </h3>
                    
                    <div class="space-y-4">
                        <?php foreach (array_slice($recommendations, 0, 4) as $rec): ?>
                            <div class="p-3 bg-blue-50 rounded-lg">
                                <div class="font-medium text-blue-900"><?php echo $rec['title']; ?></div>
                                <div class="text-sm text-blue-700 mt-1"><?php echo $rec['description']; ?></div>
                                
                                <?php if ($rec['priority'] === 'high'): ?>
                                    <span class="inline-block mt-2 px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs font-medium">
                                        High Priority
                                    </span>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Comparison -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-chart-line text-green-500 mr-2"></i>
                        Performance Comparison
                    </h3>
                    
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Your Score:</span>
                            <span class="font-semibold text-gray-900"><?php echo $basic_stats['score']; ?>%</span>
                        </div>
                        
                        <div class="flex justify-between">
                            <span class="text-gray-600">Test Average:</span>
                            <span class="font-medium"><?php echo $comparison['test_average_score']; ?>%</span>
                        </div>
                        
                        <?php if ($comparison['user_average_score']): ?>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Your Average:</span>
                                <span class="font-medium"><?php echo $comparison['user_average_score']; ?>%</span>
                            </div>
                        <?php endif; ?>
                        
                        <div class="flex justify-between">
                            <span class="text-gray-600">Percentile:</span>
                            <span class="font-semibold text-blue-600"><?php echo $comparison['percentile']; ?>th</span>
                        </div>
                        
                        <div class="pt-3 border-t">
                            <?php if ($comparison['score_vs_test_avg'] > 0): ?>
                                <div class="text-green-600 font-medium">
                                    <i class="fas fa-arrow-up mr-1"></i>
                                    <?php echo $comparison['score_vs_test_avg']; ?>% above average
                                </div>
                            <?php else: ?>
                                <div class="text-red-600 font-medium">
                                    <i class="fas fa-arrow-down mr-1"></i>
                                    <?php echo abs($comparison['score_vs_test_avg']); ?>% below average
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Actions -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Next Steps</h3>
                    
                    <div class="space-y-3">
                        <a href="<?php echo get_post_type_archive_link('practice_test'); ?>" 
                           class="block w-full px-4 py-3 bg-primary text-white text-center rounded-lg hover:bg-primary-dark transition-colors">
                            <i class="fas fa-redo mr-2"></i>Take Another Test
                        </a>
                        
                        <a href="<?php echo get_post_type_archive_link('lesson'); ?>" 
                           class="block w-full px-4 py-3 border border-gray-300 text-gray-700 text-center rounded-lg hover:bg-gray-50 transition-colors">
                            <i class="fas fa-book mr-2"></i>Study Lessons
                        </a>
                        
                        <button onclick="window.print()" 
                                class="block w-full px-4 py-3 border border-gray-300 text-gray-700 text-center rounded-lg hover:bg-gray-50 transition-colors">
                            <i class="fas fa-print mr-2"></i>Print Results
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Performance Chart
const ctx = document.getElementById('performance-chart').getContext('2d');

const domainData = <?php echo json_encode(array_values($domain_analysis)); ?>;
const domainNames = domainData.map(d => d.name);
const domainScores = domainData.map(d => d.percentage);
const domainTargets = domainData.map(d => 70); // Passing threshold

new Chart(ctx, {
    type: 'bar',
    data: {
        labels: domainNames,
        datasets: [
            {
                label: 'Your Score',
                data: domainScores,
                backgroundColor: domainScores.map(score => score >= 70 ? '#10b981' : '#ef4444'),
                borderColor: domainScores.map(score => score >= 70 ? '#059669' : '#dc2626'),
                borderWidth: 1
            },
            {
                label: 'Passing Threshold',
                data: domainTargets,
                type: 'line',
                borderColor: '#6b7280',
                borderWidth: 2,
                borderDash: [5, 5],
                fill: false,
                pointRadius: 0
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                max: 100,
                ticks: {
                    callback: function(value) {
                        return value + '%';
                    }
                }
            }
        },
        plugins: {
            legend: {
                position: 'top'
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return context.dataset.label + ': ' + context.parsed.y + '%';
                    }
                }
            }
        }
    }
});

// Print styles
const printStyles = `
    @media print {
        .results-dashboard {
            background: white !important;
        }
        
        .bg-gray-50 {
            background: white !important;
        }
        
        .shadow-sm {
            box-shadow: none !important;
        }
        
        .border-b {
            border-bottom: 1px solid #e5e7eb !important;
        }
        
        button {
            display: none !important;
        }
        
        .no-print {
            display: none !important;
        }
    }
`;

const styleSheet = document.createElement('style');
styleSheet.textContent = printStyles;
document.head.appendChild(styleSheet);
</script>

<style>
.domain-result {
    padding: 1rem;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    background: #f9fafb;
}

.domain-result:hover {
    background: #f3f4f6;
}

#performance-chart {
    height: 300px !important;
}

@media (max-width: 768px) {
    .grid-cols-1.md\\:grid-cols-4 {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .text-6xl {
        font-size: 3rem;
    }
}
</style>
