<?php
/**
 * Content Archive Pages
 * Task: T012 - Content Archive Pages
 */

get_header();

// Get current post type
$post_type = get_query_var('post_type') ?: 'lesson';
$post_type_object = get_post_type_object($post_type);

// Get current user for progress data
$current_user_id = get_current_user_id();

// Archive titles and descriptions
$archive_info = [
    'lesson' => [
        'title' => 'Lessons',
        'description' => 'Comprehensive PMP exam preparation lessons organized by domains and knowledge areas',
        'icon' => 'book'
    ],
    'practice_test' => [
        'title' => 'Practice Tests',
        'description' => 'Realistic PMP exam simulations to test your knowledge and identify weak areas',
        'icon' => 'clipboard-check'
    ],
    'resource' => [
        'title' => 'Resources',
        'description' => 'Study guides, templates, and reference materials to support your learning',
        'icon' => 'download'
    ]
];

$current_archive = $archive_info[$post_type] ?? $archive_info['lesson'];

// Get filter parameters
$selected_domain = $_GET['domain'] ?? '';
$selected_difficulty = $_GET['difficulty'] ?? '';
$search_query = $_GET['s'] ?? '';

// Get taxonomy terms for filters
$domains = get_terms(['taxonomy' => 'pmp_domain', 'hide_empty' => false]);
$difficulties = get_terms(['taxonomy' => 'difficulty_level', 'hide_empty' => false]);

// Get content statistics
$content_stats = PMP_Content_Manager::get_content_stats($post_type);
?>

<div class="content-archive bg-gray-50 min-h-screen">
    
    <!-- Archive Header -->
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            
            <!-- Breadcrumb -->
            <nav class="flex items-center space-x-2 text-sm text-gray-500 mb-6">
                <a href="<?php echo home_url(); ?>" class="hover:text-primary">Home</a>
                <i class="fas fa-chevron-right text-xs"></i>
                <span class="text-gray-900"><?php echo $current_archive['title']; ?></span>
            </nav>
            
            <!-- Archive Title & Description -->
            <div class="text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-primary rounded-full mb-4">
                    <i class="fas fa-<?php echo $current_archive['icon']; ?> text-2xl text-white"></i>
                </div>
                
                <h1 class="text-4xl font-bold text-gray-900 mb-4"><?php echo $current_archive['title']; ?></h1>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto mb-8"><?php echo $current_archive['description']; ?></p>
                
                <!-- Statistics -->
                <div class="flex justify-center space-x-8 text-sm">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-primary"><?php echo $content_stats[$post_type]['published'] ?? 0; ?></div>
                        <div class="text-gray-600">Published</div>
                    </div>
                    
                    <?php if ($current_user_id): ?>
                        <?php 
                        // Get user completion stats
                        global $wpdb;
                        $completed_count = $wpdb->get_var($wpdb->prepare(
                            "SELECT COUNT(DISTINCT p.lesson_id) 
                             FROM {$wpdb->prefix}pmp_lesson_progress p
                             JOIN {$wpdb->posts} post ON p.lesson_id = post.ID
                             WHERE p.user_id = %d AND p.status = 'completed' 
                             AND post.post_type = %s",
                            $current_user_id, $post_type
                        ));
                        
                        $completion_rate = $content_stats[$post_type]['published'] > 0 
                            ? round(($completed_count / $content_stats[$post_type]['published']) * 100, 1)
                            : 0;
                        ?>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-green-600"><?php echo $completed_count; ?></div>
                            <div class="text-gray-600">Completed</div>
                        </div>
                        
                        <div class="text-center">
                            <div class="text-2xl font-bold text-blue-600"><?php echo $completion_rate; ?>%</div>
                            <div class="text-gray-600">Progress</div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Content Type Navigation -->
    <div class="bg-white border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <nav class="flex space-x-8">
                <?php foreach ($archive_info as $type => $info): ?>
                    <a href="<?php echo get_post_type_archive_link($type); ?>" 
                       class="flex items-center py-4 px-2 border-b-2 font-medium text-sm transition-colors
                              <?php echo $post_type === $type 
                                  ? 'border-primary text-primary' 
                                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'; ?>">
                        <i class="fas fa-<?php echo $info['icon']; ?> mr-2"></i>
                        <?php echo $info['title']; ?>
                        <span class="ml-2 bg-gray-100 text-gray-600 py-1 px-2 rounded-full text-xs">
                            <?php echo $content_stats[$type]['published'] ?? 0; ?>
                        </span>
                    </a>
                <?php endforeach; ?>
            </nav>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            
            <!-- Sidebar Filters -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-sm p-6 sticky top-4">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Filters</h3>
                    
                    <form method="GET" class="space-y-6">
                        <!-- Search -->
                        <div>
                            <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                            <input type="text" id="search" name="s" 
                                   value="<?php echo esc_attr($search_query); ?>"
                                   placeholder="Search content..."
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                        </div>
                        
                        <!-- Domain Filter -->
                        <div>
                            <label for="domain" class="block text-sm font-medium text-gray-700 mb-2">Domain</label>
                            <select id="domain" name="domain" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                                <option value="">All Domains</option>
                                <?php foreach ($domains as $domain): ?>
                                    <option value="<?php echo esc_attr($domain->slug); ?>" 
                                            <?php selected($selected_domain, $domain->slug); ?>>
                                        <?php echo esc_html($domain->name); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <!-- Difficulty Filter -->
                        <div>
                            <label for="difficulty" class="block text-sm font-medium text-gray-700 mb-2">Difficulty</label>
                            <select id="difficulty" name="difficulty" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                                <option value="">All Levels</option>
                                <?php foreach ($difficulties as $difficulty): ?>
                                    <option value="<?php echo esc_attr($difficulty->slug); ?>" 
                                            <?php selected($selected_difficulty, $difficulty->slug); ?>>
                                        <?php echo esc_html($difficulty->name); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <!-- Filter Buttons -->
                        <div class="flex space-x-2">
                            <button type="submit" class="flex-1 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition-colors">
                                Apply
                            </button>
                            <a href="<?php echo get_post_type_archive_link($post_type); ?>" 
                               class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                                Clear
                            </a>
                        </div>
                    </form>
                    
                    <!-- Quick Stats -->
                    <?php if ($current_user_id): ?>
                        <div class="mt-8 pt-6 border-t border-gray-200">
                            <h4 class="text-sm font-semibold text-gray-900 mb-3">Your Progress</h4>
                            
                            <?php foreach ($domains as $domain): ?>
                                <?php
                                // Get domain progress
                                $domain_content = get_posts([
                                    'post_type' => $post_type,
                                    'post_status' => 'publish',
                                    'posts_per_page' => -1,
                                    'tax_query' => [
                                        [
                                            'taxonomy' => 'pmp_domain',
                                            'field' => 'term_id',
                                            'terms' => $domain->term_id
                                        ]
                                    ]
                                ]);
                                
                                $total_domain = count($domain_content);
                                $completed_domain = 0;
                                
                                foreach ($domain_content as $content) {
                                    $is_completed = $wpdb->get_var($wpdb->prepare(
                                        "SELECT id FROM {$wpdb->prefix}pmp_lesson_progress 
                                         WHERE user_id = %d AND lesson_id = %d AND status = 'completed'",
                                        $current_user_id, $content->ID
                                    ));
                                    if ($is_completed) $completed_domain++;
                                }
                                
                                $domain_progress = $total_domain > 0 ? round(($completed_domain / $total_domain) * 100) : 0;
                                ?>
                                
                                <div class="mb-3">
                                    <div class="flex justify-between text-sm mb-1">
                                        <span class="text-gray-600"><?php echo esc_html($domain->name); ?></span>
                                        <span class="text-gray-900"><?php echo $domain_progress; ?>%</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="bg-primary h-2 rounded-full transition-all duration-300" 
                                             style="width: <?php echo $domain_progress; ?>%"></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Content List -->
            <div class="lg:col-span-3">
                <?php if (have_posts()): ?>
                    
                    <!-- Results Header -->
                    <div class="flex items-center justify-between mb-6">
                        <div class="text-gray-600">
                            Showing <?php echo $wp_query->found_posts; ?> results
                            <?php if ($search_query): ?>
                                for "<?php echo esc_html($search_query); ?>"
                            <?php endif; ?>
                        </div>
                        
                        <!-- Sort Options -->
                        <select id="sort-select" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                            <option value="date">Newest First</option>
                            <option value="title">Alphabetical</option>
                            <option value="difficulty">By Difficulty</option>
                        </select>
                    </div>
                    
                    <!-- Content Grid -->
                    <?php get_template_part('template-parts/content/content-browser'); ?>
                    
                <?php else: ?>
                    <!-- No Content Found -->
                    <div class="text-center py-12">
                        <i class="fas fa-search text-6xl text-gray-300 mb-4"></i>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">No <?php echo strtolower($current_archive['title']); ?> found</h3>
                        <p class="text-gray-600 mb-6">Try adjusting your search terms or filters</p>
                        <a href="<?php echo get_post_type_archive_link($post_type); ?>" 
                           class="inline-flex items-center px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition-colors">
                            <i class="fas fa-refresh mr-2"></i>View All <?php echo $current_archive['title']; ?>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
// Sort functionality
document.getElementById('sort-select').addEventListener('change', function() {
    const sortBy = this.value;
    const url = new URL(window.location);
    url.searchParams.set('orderby', sortBy);
    window.location.href = url.toString();
});

// Set current sort option
const urlParams = new URLSearchParams(window.location.search);
const currentSort = urlParams.get('orderby');
if (currentSort) {
    document.getElementById('sort-select').value = currentSort;
}
</script>

<?php get_footer(); ?>
