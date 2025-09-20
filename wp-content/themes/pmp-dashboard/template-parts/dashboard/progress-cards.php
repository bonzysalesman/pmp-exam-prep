<?php
/**
 * Dashboard Progress Cards Template Part
 */

$user_id = get_current_user_id();
$user_progress = pmp_get_user_progress($user_id);
?>

<!-- Domain Progress Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 lg:gap-6">
    <!-- People Domain -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center">
                <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center mr-3">
                    <i class="fas fa-users text-green-600"></i>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900">People</h3>
                    <p class="text-sm text-gray-600">Leadership & Team</p>
                </div>
            </div>
            <span class="text-2xl font-bold text-green-600">100%</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-2 mb-2">
            <div class="bg-green-600 h-2 rounded-full" style="width: 100%"></div>
        </div>
        <p class="text-sm text-gray-600">WG1 & WG4 Complete</p>
    </div>

    <!-- Process Domain -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center">
                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                    <i class="fas fa-cogs text-blue-600"></i>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900">Process</h3>
                    <p class="text-sm text-gray-600">Technical PM</p>
                </div>
            </div>
            <span class="text-2xl font-bold text-blue-600">67%</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-2 mb-2">
            <div class="bg-blue-600 h-2 rounded-full" style="width: 67%"></div>
        </div>
        <p class="text-sm text-gray-600">WG2 Complete, WG3 In Progress</p>
    </div>

    <!-- Business Environment -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center">
                <div class="w-10 h-10 bg-orange-100 rounded-full flex items-center justify-center mr-3">
                    <i class="fas fa-building text-orange-600"></i>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900">Business</h3>
                    <p class="text-sm text-gray-600">Strategy & Value</p>
                </div>
            </div>
            <span class="text-2xl font-bold text-orange-600">0%</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-2 mb-2">
            <div class="bg-orange-600 h-2 rounded-full" style="width: 0%"></div>
        </div>
        <p class="text-sm text-gray-600">WG5 Upcoming</p>
    </div>
</div>
