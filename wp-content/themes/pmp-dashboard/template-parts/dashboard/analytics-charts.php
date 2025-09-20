<?php
/**
 * Analytics Charts Component
 * 
 * Displays progress analytics with Chart.js visualizations
 */

$user_id = get_current_user_id();
?>

<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
    <h3 class="text-lg font-semibold text-gray-900 mb-6">Progress Analytics</h3>
    
    <!-- Chart Tabs -->
    <div class="flex space-x-1 mb-6 bg-gray-100 rounded-lg p-1">
        <button class="analytics-tab-btn flex-1 py-2 px-4 text-sm font-medium rounded-md transition-colors active" 
                data-tab="weekly" data-user-id="<?php echo esc_attr($user_id); ?>">
            Weekly Progress
        </button>
        <button class="analytics-tab-btn flex-1 py-2 px-4 text-sm font-medium rounded-md transition-colors" 
                data-tab="domain" data-user-id="<?php echo esc_attr($user_id); ?>">
            Domain Breakdown
        </button>
        <button class="analytics-tab-btn flex-1 py-2 px-4 text-sm font-medium rounded-md transition-colors" 
                data-tab="time" data-user-id="<?php echo esc_attr($user_id); ?>">
            Study Time
        </button>
    </div>
    
    <!-- Chart Container -->
    <div class="relative">
        <canvas id="analyticsChart" width="400" height="200"></canvas>
        <div id="chartLoading" class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-75 hidden">
            <div class="flex items-center space-x-2 text-gray-600">
                <i class="fas fa-spinner fa-spin"></i>
                <span>Loading chart data...</span>
            </div>
        </div>
    </div>
    
    <!-- Chart Legend -->
    <div id="chartLegend" class="mt-4 flex flex-wrap justify-center gap-4 text-sm"></div>
</div>

<!-- Study Sessions Summary -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Study Sessions</h3>
    
    <div id="studySessionsList" class="space-y-3">
        <div class="flex items-center justify-center py-8 text-gray-500">
            <i class="fas fa-spinner fa-spin mr-2"></i>
            Loading study sessions...
        </div>
    </div>
    
    <!-- Period Filter -->
    <div class="mt-6 flex justify-center">
        <select id="sessionsPeriod" class="border border-gray-300 rounded-md px-3 py-2 text-sm">
            <option value="week">Last 7 days</option>
            <option value="month" selected>Last 30 days</option>
            <option value="all">All time</option>
        </select>
    </div>
</div>

<!-- Recommendations -->
<div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg p-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">
        <i class="fas fa-lightbulb text-yellow-500 mr-2"></i>
        Personalized Recommendations
    </h3>
    
    <div id="recommendationsList" class="space-y-3">
        <div class="flex items-center justify-center py-4 text-gray-500">
            <i class="fas fa-spinner fa-spin mr-2"></i>
            Generating recommendations...
        </div>
    </div>
</div>

<script>
// Initialize analytics when Chart.js is loaded
document.addEventListener('DOMContentLoaded', function() {
    if (typeof Chart !== 'undefined' && typeof window.analyticsCharts !== 'undefined') {
        window.analyticsCharts.init();
    } else {
        // Wait for Chart.js to load
        const checkChart = setInterval(() => {
            if (typeof Chart !== 'undefined' && typeof window.analyticsCharts !== 'undefined') {
                clearInterval(checkChart);
                window.analyticsCharts.init();
            }
        }, 100);
    }
});
</script>
