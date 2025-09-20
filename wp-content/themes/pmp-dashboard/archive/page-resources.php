<?php
/**
 * Template Name: Resources
 */

get_header();

if (!is_user_logged_in()) {
    wp_redirect(wp_login_url(get_permalink()));
    exit;
}
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
                        <h1 class="text-xl font-semibold text-gray-900">Resources</h1>
                        <p class="text-sm text-gray-500">Study materials, guides, and tools for PMP success</p>
                    </div>
                </div>
            </div>
        </header>

        <main class="flex-1 overflow-y-auto p-6">
            <div class="max-w-6xl mx-auto">
                <!-- Quick Access -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 text-center hover:shadow-lg transition-shadow cursor-pointer">
                        <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-book text-blue-600 text-xl"></i>
                        </div>
                        <h3 class="font-semibold text-gray-900 mb-2">PMBOK Guide</h3>
                        <p class="text-sm text-gray-600">7th Edition PDF</p>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 text-center hover:shadow-lg transition-shadow cursor-pointer">
                        <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-file-alt text-green-600 text-xl"></i>
                        </div>
                        <h3 class="font-semibold text-gray-900 mb-2">ECO Guide</h3>
                        <p class="text-sm text-gray-600">Exam Content Outline</p>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 text-center hover:shadow-lg transition-shadow cursor-pointer">
                        <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-chart-bar text-purple-600 text-xl"></i>
                        </div>
                        <h3 class="font-semibold text-gray-900 mb-2">Formula Sheet</h3>
                        <p class="text-sm text-gray-600">Key calculations</p>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 text-center hover:shadow-lg transition-shadow cursor-pointer">
                        <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-sticky-note text-orange-600 text-xl"></i>
                        </div>
                        <h3 class="font-semibold text-gray-900 mb-2">Cheat Sheets</h3>
                        <p class="text-sm text-gray-600">Quick reference</p>
                    </div>
                </div>

                <!-- Resource Categories -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Study Materials -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-6 flex items-center">
                            <i class="fas fa-book-open text-blue-600 mr-3"></i>
                            Study Materials
                        </h3>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                                <div class="flex items-center">
                                    <i class="fas fa-file-pdf text-red-500 mr-3"></i>
                                    <div>
                                        <h4 class="font-medium text-gray-900">PMBOK Guide 7th Edition</h4>
                                        <p class="text-sm text-gray-600">Complete project management guide</p>
                                    </div>
                                </div>
                                <i class="fas fa-download text-gray-400"></i>
                            </div>
                            
                            <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                                <div class="flex items-center">
                                    <i class="fas fa-file-alt text-blue-500 mr-3"></i>
                                    <div>
                                        <h4 class="font-medium text-gray-900">Agile Practice Guide</h4>
                                        <p class="text-sm text-gray-600">Agile methodologies for PMP</p>
                                    </div>
                                </div>
                                <i class="fas fa-download text-gray-400"></i>
                            </div>
                            
                            <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                                <div class="flex items-center">
                                    <i class="fas fa-clipboard-list text-green-500 mr-3"></i>
                                    <div>
                                        <h4 class="font-medium text-gray-900">Process Groups Guide</h4>
                                        <p class="text-sm text-gray-600">Detailed process breakdown</p>
                                    </div>
                                </div>
                                <i class="fas fa-download text-gray-400"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Quick References -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-6 flex items-center">
                            <i class="fas fa-lightning-bolt text-yellow-600 mr-3"></i>
                            Quick References
                        </h3>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                                <div class="flex items-center">
                                    <i class="fas fa-calculator text-purple-500 mr-3"></i>
                                    <div>
                                        <h4 class="font-medium text-gray-900">Formula Cheat Sheet</h4>
                                        <p class="text-sm text-gray-600">All PMP formulas in one place</p>
                                    </div>
                                </div>
                                <i class="fas fa-external-link-alt text-gray-400"></i>
                            </div>
                            
                            <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                                <div class="flex items-center">
                                    <i class="fas fa-sitemap text-orange-500 mr-3"></i>
                                    <div>
                                        <h4 class="font-medium text-gray-900">Process Flow Chart</h4>
                                        <p class="text-sm text-gray-600">Visual process relationships</p>
                                    </div>
                                </div>
                                <i class="fas fa-external-link-alt text-gray-400"></i>
                            </div>
                            
                            <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                                <div class="flex items-center">
                                    <i class="fas fa-list-ul text-teal-500 mr-3"></i>
                                    <div>
                                        <h4 class="font-medium text-gray-900">ITTOs Summary</h4>
                                        <p class="text-sm text-gray-600">Inputs, Tools, Techniques, Outputs</p>
                                    </div>
                                </div>
                                <i class="fas fa-external-link-alt text-gray-400"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Templates & Tools -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-6 flex items-center">
                            <i class="fas fa-tools text-gray-600 mr-3"></i>
                            Templates & Tools
                        </h3>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                                <div class="flex items-center">
                                    <i class="fas fa-file-excel text-green-600 mr-3"></i>
                                    <div>
                                        <h4 class="font-medium text-gray-900">Project Charter Template</h4>
                                        <p class="text-sm text-gray-600">Excel template for project initiation</p>
                                    </div>
                                </div>
                                <i class="fas fa-download text-gray-400"></i>
                            </div>
                            
                            <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                                <div class="flex items-center">
                                    <i class="fas fa-calendar-alt text-blue-600 mr-3"></i>
                                    <div>
                                        <h4 class="font-medium text-gray-900">Schedule Template</h4>
                                        <p class="text-sm text-gray-600">Project timeline planning tool</p>
                                    </div>
                                </div>
                                <i class="fas fa-download text-gray-400"></i>
                            </div>
                            
                            <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                                <div class="flex items-center">
                                    <i class="fas fa-exclamation-triangle text-yellow-600 mr-3"></i>
                                    <div>
                                        <h4 class="font-medium text-gray-900">Risk Register Template</h4>
                                        <p class="text-sm text-gray-600">Risk identification and tracking</p>
                                    </div>
                                </div>
                                <i class="fas fa-download text-gray-400"></i>
                            </div>
                        </div>
                    </div>

                    <!-- External Resources -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-6 flex items-center">
                            <i class="fas fa-external-link-alt text-indigo-600 mr-3"></i>
                            External Resources
                        </h3>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                                <div class="flex items-center">
                                    <i class="fas fa-globe text-blue-500 mr-3"></i>
                                    <div>
                                        <h4 class="font-medium text-gray-900">PMI Official Website</h4>
                                        <p class="text-sm text-gray-600">Latest updates and resources</p>
                                    </div>
                                </div>
                                <i class="fas fa-external-link-alt text-gray-400"></i>
                            </div>
                            
                            <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                                <div class="flex items-center">
                                    <i class="fas fa-video text-red-500 mr-3"></i>
                                    <div>
                                        <h4 class="font-medium text-gray-900">Video Tutorials</h4>
                                        <p class="text-sm text-gray-600">Supplementary learning videos</p>
                                    </div>
                                </div>
                                <i class="fas fa-external-link-alt text-gray-400"></i>
                            </div>
                            
                            <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                                <div class="flex items-center">
                                    <i class="fas fa-comments text-green-500 mr-3"></i>
                                    <div>
                                        <h4 class="font-medium text-gray-900">Study Groups</h4>
                                        <p class="text-sm text-gray-600">Connect with other candidates</p>
                                    </div>
                                </div>
                                <i class="fas fa-external-link-alt text-gray-400"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Study Tips -->
                <div class="mt-8 bg-gradient-to-r from-green-500 to-teal-600 rounded-xl p-8 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-2xl font-bold mb-2">Study Tips & Best Practices</h3>
                            <p class="text-green-100 mb-4">Maximize your study efficiency with proven strategies</p>
                            <button class="bg-white text-green-600 px-6 py-3 rounded-lg font-medium hover:bg-gray-100 transition-colors">
                                <i class="fas fa-lightbulb mr-2"></i>View Study Guide
                            </button>
                        </div>
                        <div class="hidden lg:block">
                            <div class="w-24 h-24 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                                <i class="fas fa-graduation-cap text-3xl"></i>
                            </div>
                        </div>
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
