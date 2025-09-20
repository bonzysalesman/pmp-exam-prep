<?php
/**
 * Template Name: Foundation Test Page
 */

get_header(); ?>

<div class="min-h-screen bg-gray-50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h1 class="text-3xl font-bold text-gray-900 mb-6">🧪 PMP Foundation Test</h1>
            
            <?php if (current_user_can('manage_options')): ?>
                <div class="mb-6">
                    <p class="text-gray-600 mb-4">This page tests the foundation components of the Enhanced Content Management System (Tasks T001-T006).</p>
                    
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                        <h3 class="text-lg font-semibold text-blue-800 mb-2">What This Tests:</h3>
                        <ul class="text-blue-700 space-y-1">
                            <li>✓ T001: Database Schema Setup</li>
                            <li>✓ T002: Custom Post Types Registration</li>
                            <li>✓ T003: Taxonomy Registration</li>
                            <li>✓ T004: Content Manager Core Class</li>
                            <li>✓ T005: Sequence Manager Class</li>
                            <li>✓ T006: Search Engine Foundation</li>
                        </ul>
                    </div>
                </div>
                
                <div class="test-results">
                    <?php pmp_test_foundation(); ?>
                </div>
                
                <div class="mt-8 p-4 bg-gray-50 rounded-lg">
                    <h3 class="text-lg font-semibold mb-2">Quick Actions:</h3>
                    <div class="space-x-4">
                        <a href="<?php echo admin_url('edit.php?post_type=practice_test'); ?>" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            <i class="fas fa-clipboard mr-2"></i>
                            Manage Practice Tests
                        </a>
                        <a href="<?php echo admin_url('edit.php?post_type=resource'); ?>" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                            <i class="fas fa-download mr-2"></i>
                            Manage Resources
                        </a>
                        <a href="<?php echo admin_url('edit-tags.php?taxonomy=pmp_domain'); ?>" class="inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                            <i class="fas fa-tags mr-2"></i>
                            Manage Domains
                        </a>
                    </div>
                </div>
                
            <?php else: ?>
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <p class="text-red-700">⚠️ Access denied. Administrator privileges required to run foundation tests.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.test-results h3 {
    color: #374151;
    font-size: 1.25rem;
    font-weight: 600;
    margin: 1.5rem 0 0.5rem 0;
    border-bottom: 2px solid #e5e7eb;
    padding-bottom: 0.5rem;
}

.test-results h2 {
    color: #1f2937;
    font-size: 1.5rem;
    font-weight: 700;
    margin: 2rem 0 1rem 0;
}
</style>

<?php get_footer(); ?>
