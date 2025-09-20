<?php
/**
 * Content Search Implementation
 * Task: T013 - Content Search Implementation
 */

class PMP_Search_Implementation {
    
    public static function init() {
        add_action('wp_ajax_pmp_search_suggestions', [__CLASS__, 'ajax_search_suggestions']);
        add_action('wp_ajax_nopriv_pmp_search_suggestions', [__CLASS__, 'ajax_search_suggestions']);
        add_action('wp_enqueue_scripts', [__CLASS__, 'enqueue_search_scripts']);
        add_shortcode('pmp_search', [__CLASS__, 'search_shortcode']);
    }
    
    /**
     * AJAX search suggestions
     */
    public static function ajax_search_suggestions() {
        $query = sanitize_text_field($_GET['q'] ?? '');
        
        if (strlen($query) < 2) {
            wp_send_json_error('Query too short');
        }
        
        $suggestions = PMP_Search_Engine::get_suggestions($query, 8);
        
        wp_send_json_success($suggestions);
    }
    
    /**
     * Enqueue search scripts
     */
    public static function enqueue_search_scripts() {
        wp_enqueue_script('pmp-search', get_template_directory_uri() . '/assets/js/search.js', ['jquery'], '1.0', true);
        wp_localize_script('pmp-search', 'pmpSearch', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('pmp_search_nonce')
        ]);
    }
    
    /**
     * Search shortcode
     */
    public static function search_shortcode($atts) {
        $atts = shortcode_atts([
            'placeholder' => 'Search lessons, tests, resources...',
            'show_filters' => true,
            'results_per_page' => 12
        ], $atts);
        
        ob_start();
        self::render_search_interface($atts);
        return ob_get_clean();
    }
    
    /**
     * Render search interface
     */
    private static function render_search_interface($atts) {
        $search_query = $_GET['s'] ?? '';
        $selected_type = $_GET['type'] ?? '';
        $selected_domain = $_GET['domain'] ?? '';
        
        ?>
        <div class="pmp-search-container">
            <!-- Search Form -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <form method="GET" class="pmp-search-form">
                    <div class="relative mb-4">
                        <input type="text" 
                               name="s" 
                               id="pmp-search-input"
                               value="<?php echo esc_attr($search_query); ?>"
                               placeholder="<?php echo esc_attr($atts['placeholder']); ?>"
                               class="w-full pl-12 pr-4 py-3 text-lg border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"
                               autocomplete="off">
                        <i class="fas fa-search absolute left-4 top-4 text-gray-400"></i>
                        
                        <!-- Search Suggestions -->
                        <div id="search-suggestions" class="absolute top-full left-0 right-0 bg-white border border-gray-200 rounded-lg shadow-lg mt-1 hidden z-50">
                            <div class="p-2" id="suggestions-list"></div>
                        </div>
                    </div>
                    
                    <?php if ($atts['show_filters']): ?>
                        <div class="flex flex-wrap gap-4">
                            <select name="type" class="px-3 py-2 border border-gray-300 rounded-lg">
                                <option value="">All Content</option>
                                <option value="lesson" <?php selected($selected_type, 'lesson'); ?>>Lessons</option>
                                <option value="practice_test" <?php selected($selected_type, 'practice_test'); ?>>Practice Tests</option>
                                <option value="resource" <?php selected($selected_type, 'resource'); ?>>Resources</option>
                            </select>
                            
                            <select name="domain" class="px-3 py-2 border border-gray-300 rounded-lg">
                                <option value="">All Domains</option>
                                <?php
                                $domains = get_terms(['taxonomy' => 'pmp_domain', 'hide_empty' => false]);
                                foreach ($domains as $domain):
                                ?>
                                    <option value="<?php echo esc_attr($domain->slug); ?>" <?php selected($selected_domain, $domain->slug); ?>>
                                        <?php echo esc_html($domain->name); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            
                            <button type="submit" class="px-6 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition-colors">
                                <i class="fas fa-search mr-2"></i>Search
                            </button>
                        </div>
                    <?php endif; ?>
                </form>
            </div>
            
            <!-- Search Results -->
            <div id="search-results">
                <?php if (!empty($search_query)): ?>
                    <?php self::display_search_results($search_query, $atts); ?>
                <?php endif; ?>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            let searchTimeout;
            
            $('#pmp-search-input').on('input', function() {
                const query = $(this).val();
                
                clearTimeout(searchTimeout);
                
                if (query.length < 2) {
                    $('#search-suggestions').addClass('hidden');
                    return;
                }
                
                searchTimeout = setTimeout(() => {
                    $.ajax({
                        url: pmpSearch.ajax_url,
                        data: {
                            action: 'pmp_search_suggestions',
                            q: query,
                            _wpnonce: pmpSearch.nonce
                        },
                        success: function(response) {
                            if (response.success) {
                                displaySuggestions(response.data);
                            }
                        }
                    });
                }, 300);
            });
            
            function displaySuggestions(suggestions) {
                const $list = $('#suggestions-list');
                $list.empty();
                
                if (suggestions.length === 0) {
                    $('#search-suggestions').addClass('hidden');
                    return;
                }
                
                suggestions.forEach(suggestion => {
                    const $item = $('<div class="px-3 py-2 hover:bg-gray-100 cursor-pointer border-b border-gray-100 last:border-b-0">')
                        .text(suggestion.text)
                        .on('click', function() {
                            $('#pmp-search-input').val(suggestion.text);
                            $('#search-suggestions').addClass('hidden');
                            $('.pmp-search-form').submit();
                        });
                    $list.append($item);
                });
                
                $('#search-suggestions').removeClass('hidden');
            }
            
            // Hide suggestions when clicking outside
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.pmp-search-container').length) {
                    $('#search-suggestions').addClass('hidden');
                }
            });
        });
        </script>
        <?php
    }
    
    /**
     * Display search results
     */
    private static function display_search_results($query, $atts) {
        $filters = [
            'post_type' => !empty($_GET['type']) ? [$_GET['type']] : ['lesson', 'practice_test', 'resource']
        ];
        
        if (!empty($_GET['domain'])) {
            $filters['domain'] = $_GET['domain'];
        }
        
        $results = PMP_Search_Engine::faceted_search($query, $filters);
        
        ?>
        <div class="search-results">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-gray-900">
                    Search Results for "<?php echo esc_html($query); ?>"
                </h2>
                <span class="text-gray-600"><?php echo $results['total']; ?> results found</span>
            </div>
            
            <?php if (!empty($results['posts'])): ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                    <?php foreach ($results['posts'] as $post): ?>
                        <?php
                        $post_id = $post->ID;
                        $content_type = $post->post_type;
                        $estimated_minutes = get_post_meta($post_id, '_estimated_minutes', true) ?: 15;
                        $difficulty = get_post_meta($post_id, '_difficulty_level', true) ?: 'intermediate';
                        ?>
                        
                        <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow overflow-hidden">
                            <div class="p-4">
                                <div class="flex items-start justify-between mb-2">
                                    <span class="px-2 py-1 bg-primary text-white rounded-full text-xs font-medium">
                                        <?php echo ucfirst(str_replace('_', ' ', $content_type)); ?>
                                    </span>
                                    <span class="text-xs text-gray-500">
                                        <i class="fas fa-clock mr-1"></i><?php echo $estimated_minutes; ?> min
                                    </span>
                                </div>
                                
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">
                                    <a href="<?php echo get_permalink($post_id); ?>" class="hover:text-primary transition-colors">
                                        <?php echo esc_html($post->post_title); ?>
                                    </a>
                                </h3>
                                
                                <p class="text-gray-600 text-sm mb-3 line-clamp-2">
                                    <?php echo esc_html(get_the_excerpt($post)); ?>
                                </p>
                                
                                <div class="flex items-center justify-between">
                                    <span class="px-2 py-1 rounded-full text-xs font-medium
                                        <?php echo $difficulty === 'beginner' ? 'bg-green-100 text-green-800' : 
                                                  ($difficulty === 'advanced' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800'); ?>">
                                        <?php echo ucfirst($difficulty); ?>
                                    </span>
                                    
                                    <a href="<?php echo get_permalink($post_id); ?>" 
                                       class="text-primary hover:text-primary-dark font-medium text-sm">
                                        View →
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Facets -->
                <?php if (!empty($results['facets'])): ?>
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Refine Results</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            
                            <?php if (!empty($results['facets']['domains'])): ?>
                                <div>
                                    <h4 class="font-medium text-gray-900 mb-2">Domains</h4>
                                    <?php foreach (array_slice($results['facets']['domains'], 0, 5) as $facet): ?>
                                        <a href="?s=<?php echo urlencode($query); ?>&domain=<?php echo urlencode($facet->slug); ?>" 
                                           class="block text-sm text-gray-600 hover:text-primary mb-1">
                                            <?php echo esc_html($facet->name); ?> (<?php echo $facet->count; ?>)
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($results['facets']['content_types'])): ?>
                                <div>
                                    <h4 class="font-medium text-gray-900 mb-2">Content Types</h4>
                                    <?php foreach ($results['facets']['content_types'] as $facet): ?>
                                        <a href="?s=<?php echo urlencode($query); ?>&type=<?php echo urlencode($facet->post_type); ?>" 
                                           class="block text-sm text-gray-600 hover:text-primary mb-1">
                                            <?php echo ucfirst(str_replace('_', ' ', $facet->post_type)); ?> (<?php echo $facet->count; ?>)
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($results['facets']['difficulty'])): ?>
                                <div>
                                    <h4 class="font-medium text-gray-900 mb-2">Difficulty</h4>
                                    <?php foreach ($results['facets']['difficulty'] as $facet): ?>
                                        <a href="?s=<?php echo urlencode($query); ?>&difficulty=<?php echo urlencode($facet->value); ?>" 
                                           class="block text-sm text-gray-600 hover:text-primary mb-1">
                                            <?php echo ucfirst($facet->value); ?> (<?php echo $facet->count; ?>)
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
            <?php else: ?>
                <div class="text-center py-12">
                    <i class="fas fa-search text-6xl text-gray-300 mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">No results found</h3>
                    <p class="text-gray-600 mb-4">Try different keywords or adjust your filters</p>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }
    
    /**
     * Get popular searches for display
     */
    public static function get_popular_searches_widget() {
        $popular = PMP_Search_Engine::get_popular_searches(5);
        
        if (empty($popular)) {
            return '';
        }
        
        ob_start();
        ?>
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Popular Searches</h3>
            <div class="space-y-2">
                <?php foreach ($popular as $search): ?>
                    <a href="?s=<?php echo urlencode($search->search_query); ?>" 
                       class="flex items-center justify-between text-sm text-gray-600 hover:text-primary transition-colors">
                        <span><?php echo esc_html($search->search_query); ?></span>
                        <span class="text-xs bg-gray-100 px-2 py-1 rounded-full"><?php echo $search->search_count; ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}

// Initialize
PMP_Search_Implementation::init();
?>
