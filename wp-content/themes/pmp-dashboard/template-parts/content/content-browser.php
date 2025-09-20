<?php
/**
 * Content Browser Frontend
 * Task: T010 - Content Browser Frontend
 */

// Get current user for progress data
$current_user_id = get_current_user_id();

// Get filter parameters
$selected_domain = $_GET['domain'] ?? '';
$selected_difficulty = $_GET['difficulty'] ?? '';
$selected_type = $_GET['type'] ?? 'lesson';
$search_query = $_GET['s'] ?? '';

// Get content based on filters
$content_args = [
    'post_type' => $selected_type,
    'post_status' => 'publish',
    'posts_per_page' => 12,
    'paged' => get_query_var('paged') ?: 1
];

if ($selected_domain) {
    $content_args['domain'] = $selected_domain;
}

if ($selected_difficulty) {
    $content_args['difficulty'] = $selected_difficulty;
}

if ($search_query) {
    $content_args['s'] = $search_query;
}

$content_query = PMP_Content_Manager::get_content($selected_type, $content_args);

// Get filter options
$domains = get_terms(['taxonomy' => 'pmp_domain', 'hide_empty' => false]);
$difficulties = get_terms(['taxonomy' => 'difficulty_level', 'hide_empty' => false]);
?>

<div class="content-browser bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-4">Content Library</h1>
            <p class="text-gray-600">Explore lessons, practice tests, and resources for your PMP preparation</p>
        </div>
        
        <!-- Filters & Search -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
            <form method="GET" class="space-y-4 md:space-y-0 md:flex md:items-end md:space-x-4">
                
                <!-- Search -->
                <div class="flex-1">
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Search Content</label>
                    <div class="relative">
                        <input type="text" id="search" name="s" 
                               value="<?php echo esc_attr($search_query); ?>"
                               placeholder="Search lessons, tests, resources..."
                               class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                    </div>
                </div>
                
                <!-- Content Type -->
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 mb-2">Content Type</label>
                    <select id="type" name="type" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                        <option value="lesson" <?php selected($selected_type, 'lesson'); ?>>Lessons</option>
                        <option value="practice_test" <?php selected($selected_type, 'practice_test'); ?>>Practice Tests</option>
                        <option value="resource" <?php selected($selected_type, 'resource'); ?>>Resources</option>
                    </select>
                </div>
                
                <!-- Domain Filter -->
                <div>
                    <label for="domain" class="block text-sm font-medium text-gray-700 mb-2">Domain</label>
                    <select id="domain" name="domain" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
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
                    <select id="difficulty" name="difficulty" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                        <option value="">All Levels</option>
                        <?php foreach ($difficulties as $difficulty): ?>
                            <option value="<?php echo esc_attr($difficulty->slug); ?>" 
                                    <?php selected($selected_difficulty, $difficulty->slug); ?>>
                                <?php echo esc_html($difficulty->name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Filter Button -->
                <div>
                    <button type="submit" class="px-6 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark focus:ring-2 focus:ring-primary focus:ring-offset-2 transition-colors">
                        <i class="fas fa-filter mr-2"></i>Filter
                    </button>
                </div>
                
            </form>
        </div>
        
        <!-- Results Summary -->
        <div class="flex items-center justify-between mb-6">
            <div class="text-gray-600">
                <?php if ($content_query->found_posts > 0): ?>
                    Showing <?php echo $content_query->found_posts; ?> 
                    <?php echo $selected_type === 'lesson' ? 'lessons' : ($selected_type === 'practice_test' ? 'practice tests' : 'resources'); ?>
                    <?php if ($search_query): ?>
                        for "<?php echo esc_html($search_query); ?>"
                    <?php endif; ?>
                <?php else: ?>
                    No content found
                <?php endif; ?>
            </div>
            
            <!-- View Toggle -->
            <div class="flex items-center space-x-2">
                <button id="grid-view" class="p-2 text-gray-500 hover:text-primary active">
                    <i class="fas fa-th-large"></i>
                </button>
                <button id="list-view" class="p-2 text-gray-500 hover:text-primary">
                    <i class="fas fa-list"></i>
                </button>
            </div>
        </div>
        
        <!-- Content Grid -->
        <?php if ($content_query->have_posts()): ?>
            <div id="content-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-8">
                <?php while ($content_query->have_posts()): $content_query->the_post(); ?>
                    <?php 
                    $post_id = get_the_ID();
                    $content_type = get_post_type();
                    $estimated_minutes = get_post_meta($post_id, '_estimated_minutes', true) ?: 15;
                    $difficulty = get_post_meta($post_id, '_difficulty_level', true) ?: 'intermediate';
                    $is_premium = get_post_meta($post_id, '_is_premium', true);
                    
                    // Get user progress
                    $user_progress = 0;
                    $is_completed = false;
                    if ($current_user_id) {
                        global $wpdb;
                        $progress_data = $wpdb->get_row($wpdb->prepare(
                            "SELECT progress_percentage, status FROM {$wpdb->prefix}pmp_lesson_progress 
                             WHERE user_id = %d AND lesson_id = %d",
                            $current_user_id, $post_id
                        ));
                        
                        if ($progress_data) {
                            $user_progress = intval($progress_data->progress_percentage);
                            $is_completed = $progress_data->status === 'completed';
                        }
                    }
                    
                    // Get domains and knowledge areas
                    $domains = wp_get_post_terms($post_id, 'pmp_domain');
                    $knowledge_areas = wp_get_post_terms($post_id, 'pmp_knowledge_area');
                    ?>
                    
                    <div class="content-card bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200 overflow-hidden">
                        
                        <!-- Card Header -->
                        <div class="relative">
                            <?php if (has_post_thumbnail()): ?>
                                <img src="<?php echo get_the_post_thumbnail_url(get_the_ID(), 'medium'); ?>" 
                                     alt="<?php the_title(); ?>" 
                                     class="w-full h-48 object-cover">
                            <?php else: ?>
                                <div class="w-full h-48 bg-gradient-to-br from-primary to-primary-dark flex items-center justify-center">
                                    <i class="fas fa-<?php echo $content_type === 'lesson' ? 'book' : ($content_type === 'practice_test' ? 'clipboard-check' : 'download'); ?> text-4xl text-white"></i>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Premium Badge -->
                            <?php if ($is_premium): ?>
                                <div class="absolute top-2 right-2 bg-yellow-500 text-white px-2 py-1 rounded-full text-xs font-semibold">
                                    <i class="fas fa-crown mr-1"></i>Premium
                                </div>
                            <?php endif; ?>
                            
                            <!-- Progress Badge -->
                            <?php if ($is_completed): ?>
                                <div class="absolute top-2 left-2 bg-green-500 text-white px-2 py-1 rounded-full text-xs font-semibold">
                                    <i class="fas fa-check mr-1"></i>Complete
                                </div>
                            <?php elseif ($user_progress > 0): ?>
                                <div class="absolute top-2 left-2 bg-blue-500 text-white px-2 py-1 rounded-full text-xs font-semibold">
                                    <?php echo $user_progress; ?>%
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Card Content -->
                        <div class="p-4">
                            <div class="flex items-start justify-between mb-2">
                                <h3 class="text-lg font-semibold text-gray-900 line-clamp-2">
                                    <a href="<?php the_permalink(); ?>" class="hover:text-primary transition-colors">
                                        <?php the_title(); ?>
                                    </a>
                                </h3>
                                
                                <!-- Bookmark Button -->
                                <button class="bookmark-btn text-gray-400 hover:text-red-500 transition-colors" 
                                        data-content-id="<?php echo $post_id; ?>" 
                                        data-content-type="<?php echo $content_type; ?>">
                                    <i class="fas fa-heart"></i>
                                </button>
                            </div>
                            
                            <!-- Excerpt -->
                            <p class="text-gray-600 text-sm mb-3 line-clamp-2">
                                <?php echo get_the_excerpt(); ?>
                            </p>
                            
                            <!-- Meta Information -->
                            <div class="flex items-center justify-between text-xs text-gray-500 mb-3">
                                <span class="flex items-center">
                                    <i class="fas fa-clock mr-1"></i>
                                    <?php echo $estimated_minutes; ?> min
                                </span>
                                
                                <span class="px-2 py-1 rounded-full text-xs font-medium
                                    <?php echo $difficulty === 'beginner' ? 'bg-green-100 text-green-800' : 
                                              ($difficulty === 'advanced' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800'); ?>">
                                    <?php echo ucfirst($difficulty); ?>
                                </span>
                            </div>
                            
                            <!-- Domains -->
                            <?php if (!empty($domains)): ?>
                                <div class="flex flex-wrap gap-1 mb-3">
                                    <?php foreach (array_slice($domains, 0, 2) as $domain): ?>
                                        <span class="px-2 py-1 bg-gray-100 text-gray-700 rounded-full text-xs">
                                            <?php echo esc_html($domain->name); ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Progress Bar -->
                            <?php if ($user_progress > 0): ?>
                                <div class="w-full bg-gray-200 rounded-full h-2 mb-3">
                                    <div class="bg-primary h-2 rounded-full transition-all duration-300" 
                                         style="width: <?php echo $user_progress; ?>%"></div>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Action Button -->
                            <a href="<?php the_permalink(); ?>" 
                               class="block w-full text-center px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition-colors duration-200">
                                <?php if ($is_completed): ?>
                                    <i class="fas fa-eye mr-2"></i>Review
                                <?php elseif ($user_progress > 0): ?>
                                    <i class="fas fa-play mr-2"></i>Continue
                                <?php else: ?>
                                    <i class="fas fa-play mr-2"></i>Start
                                <?php endif; ?>
                            </a>
                        </div>
                    </div>
                    
                <?php endwhile; ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($content_query->max_num_pages > 1): ?>
                <div class="flex justify-center">
                    <?php
                    echo paginate_links([
                        'total' => $content_query->max_num_pages,
                        'current' => max(1, get_query_var('paged')),
                        'format' => '?paged=%#%',
                        'show_all' => false,
                        'end_size' => 1,
                        'mid_size' => 2,
                        'prev_next' => true,
                        'prev_text' => '<i class="fas fa-chevron-left"></i>',
                        'next_text' => '<i class="fas fa-chevron-right"></i>',
                        'type' => 'list',
                        'class' => 'pagination'
                    ]);
                    ?>
                </div>
            <?php endif; ?>
            
        <?php else: ?>
            <!-- No Content Found -->
            <div class="text-center py-12">
                <i class="fas fa-search text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">No content found</h3>
                <p class="text-gray-600 mb-4">Try adjusting your filters or search terms</p>
                <a href="?" class="inline-flex items-center px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition-colors">
                    <i class="fas fa-refresh mr-2"></i>Clear Filters
                </a>
            </div>
        <?php endif; ?>
        
    </div>
</div>

<script>
// View toggle functionality
document.getElementById('grid-view').addEventListener('click', function() {
    document.getElementById('content-grid').className = 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-8';
    this.classList.add('active');
    document.getElementById('list-view').classList.remove('active');
});

document.getElementById('list-view').addEventListener('click', function() {
    document.getElementById('content-grid').className = 'space-y-4 mb-8';
    this.classList.add('active');
    document.getElementById('grid-view').classList.remove('active');
});

// Bookmark functionality
document.querySelectorAll('.bookmark-btn').forEach(button => {
    button.addEventListener('click', function() {
        const contentId = this.dataset.contentId;
        const contentType = this.dataset.contentType;
        
        fetch('/wp-json/pmp/v1/bookmark', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
            },
            body: JSON.stringify({
                content_id: contentId,
                content_type: contentType,
                bookmark_type: 'favorite'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.action === 'added') {
                this.classList.add('text-red-500');
                this.classList.remove('text-gray-400');
            } else {
                this.classList.remove('text-red-500');
                this.classList.add('text-gray-400');
            }
        });
    });
});
</script>

<style>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.pagination {
    display: flex;
    list-style: none;
    padding: 0;
    margin: 0;
    gap: 0.5rem;
}

.pagination li a,
.pagination li span {
    display: block;
    padding: 0.5rem 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    color: #374151;
    text-decoration: none;
    transition: all 0.2s;
}

.pagination li a:hover {
    background-color: #f3f4f6;
    border-color: #9ca3af;
}

.pagination li.current span {
    background-color: #3b82f6;
    border-color: #3b82f6;
    color: white;
}

#grid-view.active,
#list-view.active {
    color: #3b82f6;
}
</style>

<?php wp_reset_postdata(); ?>
