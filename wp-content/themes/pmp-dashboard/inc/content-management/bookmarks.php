<?php
/**
 * Content Bookmarking System
 * Task: T014 - Content Bookmarking System
 */

class PMP_Bookmarks_System {
    
    public static function init() {
        add_action('wp_ajax_pmp_toggle_bookmark', [__CLASS__, 'ajax_toggle_bookmark']);
        add_action('wp_ajax_pmp_get_bookmarks', [__CLASS__, 'ajax_get_bookmarks']);
        add_shortcode('pmp_bookmarks', [__CLASS__, 'bookmarks_shortcode']);
        add_action('wp_enqueue_scripts', [__CLASS__, 'enqueue_scripts']);
    }
    
    /**
     * AJAX toggle bookmark
     */
    public static function ajax_toggle_bookmark() {
        check_ajax_referer('pmp_bookmark_nonce', 'nonce');
        
        if (!is_user_logged_in()) {
            wp_send_json_error('User not logged in');
        }
        
        $user_id = get_current_user_id();
        $content_id = intval($_POST['content_id']);
        $content_type = sanitize_text_field($_POST['content_type']);
        $bookmark_type = sanitize_text_field($_POST['bookmark_type'] ?? 'favorite');
        
        global $wpdb;
        
        // Check if bookmark exists
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}pmp_content_bookmarks 
             WHERE user_id = %d AND content_id = %d AND bookmark_type = %s",
            $user_id, $content_id, $bookmark_type
        ));
        
        if ($existing) {
            // Remove bookmark
            $wpdb->delete(
                $wpdb->prefix . 'pmp_content_bookmarks',
                ['id' => $existing],
                ['%d']
            );
            $action = 'removed';
        } else {
            // Add bookmark
            $wpdb->insert(
                $wpdb->prefix . 'pmp_content_bookmarks',
                [
                    'user_id' => $user_id,
                    'content_id' => $content_id,
                    'content_type' => $content_type,
                    'bookmark_type' => $bookmark_type
                ],
                ['%d', '%d', '%s', '%s']
            );
            $action = 'added';
        }
        
        wp_send_json_success([
            'action' => $action,
            'bookmark_type' => $bookmark_type,
            'count' => self::get_bookmark_count($user_id, $bookmark_type)
        ]);
    }
    
    /**
     * AJAX get bookmarks
     */
    public static function ajax_get_bookmarks() {
        check_ajax_referer('pmp_bookmark_nonce', 'nonce');
        
        if (!is_user_logged_in()) {
            wp_send_json_error('User not logged in');
        }
        
        $user_id = get_current_user_id();
        $bookmark_type = sanitize_text_field($_GET['type'] ?? 'favorite');
        $page = intval($_GET['page'] ?? 1);
        $per_page = 12;
        
        $bookmarks = self::get_user_bookmarks($user_id, $bookmark_type, $page, $per_page);
        
        wp_send_json_success($bookmarks);
    }
    
    /**
     * Enqueue scripts
     */
    public static function enqueue_scripts() {
        wp_enqueue_script('pmp-bookmarks', get_template_directory_uri() . '/assets/js/bookmarks.js', ['jquery'], '1.0', true);
        wp_localize_script('pmp-bookmarks', 'pmpBookmarks', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('pmp_bookmark_nonce')
        ]);
    }
    
    /**
     * Bookmarks shortcode
     */
    public static function bookmarks_shortcode($atts) {
        if (!is_user_logged_in()) {
            return '<p>Please log in to view your bookmarks.</p>';
        }
        
        $atts = shortcode_atts([
            'type' => 'favorite',
            'show_tabs' => true,
            'per_page' => 12
        ], $atts);
        
        ob_start();
        self::render_bookmarks_interface($atts);
        return ob_get_clean();
    }
    
    /**
     * Render bookmarks interface
     */
    private static function render_bookmarks_interface($atts) {
        $user_id = get_current_user_id();
        $current_type = $_GET['bookmark_type'] ?? $atts['type'];
        
        // Get bookmark counts
        $counts = [
            'favorite' => self::get_bookmark_count($user_id, 'favorite'),
            'later' => self::get_bookmark_count($user_id, 'later'),
            'completed' => self::get_bookmark_count($user_id, 'completed')
        ];
        
        ?>
        <div class="pmp-bookmarks-container">
            
            <?php if ($atts['show_tabs']): ?>
                <!-- Bookmark Tabs -->
                <div class="bg-white rounded-lg shadow-sm mb-6">
                    <div class="border-b border-gray-200">
                        <nav class="flex space-x-8 px-6">
                            <a href="?bookmark_type=favorite" 
                               class="py-4 px-1 border-b-2 font-medium text-sm transition-colors
                                      <?php echo $current_type === 'favorite' 
                                          ? 'border-red-500 text-red-600' 
                                          : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'; ?>">
                                <i class="fas fa-heart mr-2"></i>
                                Favorites
                                <span class="ml-2 bg-gray-100 text-gray-600 py-1 px-2 rounded-full text-xs">
                                    <?php echo $counts['favorite']; ?>
                                </span>
                            </a>
                            
                            <a href="?bookmark_type=later" 
                               class="py-4 px-1 border-b-2 font-medium text-sm transition-colors
                                      <?php echo $current_type === 'later' 
                                          ? 'border-blue-500 text-blue-600' 
                                          : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'; ?>">
                                <i class="fas fa-clock mr-2"></i>
                                Save for Later
                                <span class="ml-2 bg-gray-100 text-gray-600 py-1 px-2 rounded-full text-xs">
                                    <?php echo $counts['later']; ?>
                                </span>
                            </a>
                            
                            <a href="?bookmark_type=completed" 
                               class="py-4 px-1 border-b-2 font-medium text-sm transition-colors
                                      <?php echo $current_type === 'completed' 
                                          ? 'border-green-500 text-green-600' 
                                          : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'; ?>">
                                <i class="fas fa-check-circle mr-2"></i>
                                Completed
                                <span class="ml-2 bg-gray-100 text-gray-600 py-1 px-2 rounded-full text-xs">
                                    <?php echo $counts['completed']; ?>
                                </span>
                            </a>
                        </nav>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Bookmarks Grid -->
            <div id="bookmarks-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <!-- Content loaded via AJAX -->
            </div>
            
            <!-- Load More Button -->
            <div class="text-center mt-8">
                <button id="load-more-bookmarks" 
                        class="px-6 py-3 bg-primary text-white rounded-lg hover:bg-primary-dark transition-colors hidden">
                    <i class="fas fa-plus mr-2"></i>Load More
                </button>
            </div>
            
            <!-- Empty State -->
            <div id="bookmarks-empty" class="text-center py-12 hidden">
                <i class="fas fa-heart text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">No bookmarks yet</h3>
                <p class="text-gray-600 mb-4">Start bookmarking content to build your personal library</p>
                <a href="<?php echo get_post_type_archive_link('lesson'); ?>" 
                   class="inline-flex items-center px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition-colors">
                    <i class="fas fa-search mr-2"></i>Browse Content
                </a>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            let currentPage = 1;
            let loading = false;
            const bookmarkType = '<?php echo esc_js($current_type); ?>';
            
            // Load initial bookmarks
            loadBookmarks(1, true);
            
            // Load more button
            $('#load-more-bookmarks').on('click', function() {
                if (!loading) {
                    loadBookmarks(currentPage + 1, false);
                }
            });
            
            function loadBookmarks(page, replace = false) {
                loading = true;
                
                $.ajax({
                    url: pmpBookmarks.ajax_url,
                    data: {
                        action: 'pmp_get_bookmarks',
                        type: bookmarkType,
                        page: page,
                        _wpnonce: pmpBookmarks.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            const bookmarks = response.data.bookmarks;
                            const hasMore = response.data.has_more;
                            
                            if (bookmarks.length === 0 && page === 1) {
                                $('#bookmarks-grid').addClass('hidden');
                                $('#bookmarks-empty').removeClass('hidden');
                                $('#load-more-bookmarks').addClass('hidden');
                            } else {
                                $('#bookmarks-empty').addClass('hidden');
                                $('#bookmarks-grid').removeClass('hidden');
                                
                                if (replace) {
                                    $('#bookmarks-grid').empty();
                                }
                                
                                bookmarks.forEach(bookmark => {
                                    $('#bookmarks-grid').append(createBookmarkCard(bookmark));
                                });
                                
                                currentPage = page;
                                
                                if (hasMore) {
                                    $('#load-more-bookmarks').removeClass('hidden');
                                } else {
                                    $('#load-more-bookmarks').addClass('hidden');
                                }
                            }
                        }
                        loading = false;
                    }
                });
            }
            
            function createBookmarkCard(bookmark) {
                const difficultyColors = {
                    'beginner': 'bg-green-100 text-green-800',
                    'intermediate': 'bg-yellow-100 text-yellow-800',
                    'advanced': 'bg-red-100 text-red-800'
                };
                
                const typeIcons = {
                    'lesson': 'book',
                    'practice_test': 'clipboard-check',
                    'resource': 'download'
                };
                
                return `
                    <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow overflow-hidden">
                        <div class="p-4">
                            <div class="flex items-start justify-between mb-2">
                                <span class="px-2 py-1 bg-primary text-white rounded-full text-xs font-medium">
                                    <i class="fas fa-${typeIcons[bookmark.content_type] || 'file'} mr-1"></i>
                                    ${bookmark.content_type.replace('_', ' ')}
                                </span>
                                <button class="bookmark-remove text-gray-400 hover:text-red-500 transition-colors" 
                                        data-content-id="${bookmark.content_id}" 
                                        data-content-type="${bookmark.content_type}">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">
                                <a href="${bookmark.permalink}" class="hover:text-primary transition-colors">
                                    ${bookmark.post_title}
                                </a>
                            </h3>
                            
                            <p class="text-gray-600 text-sm mb-3 line-clamp-2">
                                ${bookmark.excerpt || ''}
                            </p>
                            
                            <div class="flex items-center justify-between text-xs text-gray-500 mb-3">
                                <span>Bookmarked ${bookmark.created_at}</span>
                                ${bookmark.difficulty ? `<span class="px-2 py-1 rounded-full ${difficultyColors[bookmark.difficulty] || 'bg-gray-100 text-gray-800'}">${bookmark.difficulty}</span>` : ''}
                            </div>
                            
                            <a href="${bookmark.permalink}" 
                               class="block w-full text-center px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition-colors">
                                <i class="fas fa-eye mr-2"></i>View Content
                            </a>
                        </div>
                    </div>
                `;
            }
            
            // Remove bookmark functionality
            $(document).on('click', '.bookmark-remove', function() {
                const $card = $(this).closest('.bg-white');
                const contentId = $(this).data('content-id');
                const contentType = $(this).data('content-type');
                
                $.ajax({
                    url: pmpBookmarks.ajax_url,
                    method: 'POST',
                    data: {
                        action: 'pmp_toggle_bookmark',
                        content_id: contentId,
                        content_type: contentType,
                        bookmark_type: bookmarkType,
                        _wpnonce: pmpBookmarks.nonce
                    },
                    success: function(response) {
                        if (response.success && response.data.action === 'removed') {
                            $card.fadeOut(300, function() {
                                $(this).remove();
                                
                                // Check if grid is empty
                                if ($('#bookmarks-grid .bg-white').length === 0) {
                                    $('#bookmarks-grid').addClass('hidden');
                                    $('#bookmarks-empty').removeClass('hidden');
                                }
                            });
                        }
                    }
                });
            });
        });
        </script>
        <?php
    }
    
    /**
     * Get user bookmarks
     */
    public static function get_user_bookmarks($user_id, $bookmark_type = 'favorite', $page = 1, $per_page = 12) {
        global $wpdb;
        
        $offset = ($page - 1) * $per_page;
        
        $bookmarks = $wpdb->get_results($wpdb->prepare(
            "SELECT cb.*, p.post_title, p.post_excerpt, p.post_type 
             FROM {$wpdb->prefix}pmp_content_bookmarks cb
             JOIN {$wpdb->posts} p ON cb.content_id = p.ID
             WHERE cb.user_id = %d AND cb.bookmark_type = %s
             ORDER BY cb.created_at DESC
             LIMIT %d OFFSET %d",
            $user_id, $bookmark_type, $per_page, $offset
        ));
        
        // Add additional data
        foreach ($bookmarks as &$bookmark) {
            $bookmark->permalink = get_permalink($bookmark->content_id);
            $bookmark->excerpt = get_the_excerpt($bookmark->content_id);
            $bookmark->difficulty = get_post_meta($bookmark->content_id, '_difficulty_level', true);
            $bookmark->created_at = human_time_diff(strtotime($bookmark->created_at)) . ' ago';
        }
        
        // Check if there are more
        $total = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}pmp_content_bookmarks 
             WHERE user_id = %d AND bookmark_type = %s",
            $user_id, $bookmark_type
        ));
        
        $has_more = ($offset + $per_page) < $total;
        
        return [
            'bookmarks' => $bookmarks,
            'has_more' => $has_more,
            'total' => intval($total)
        ];
    }
    
    /**
     * Get bookmark count
     */
    public static function get_bookmark_count($user_id, $bookmark_type) {
        global $wpdb;
        
        return intval($wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}pmp_content_bookmarks 
             WHERE user_id = %d AND bookmark_type = %s",
            $user_id, $bookmark_type
        )));
    }
    
    /**
     * Check if content is bookmarked
     */
    public static function is_bookmarked($user_id, $content_id, $bookmark_type = 'favorite') {
        global $wpdb;
        
        return $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}pmp_content_bookmarks 
             WHERE user_id = %d AND content_id = %d AND bookmark_type = %s",
            $user_id, $content_id, $bookmark_type
        )) ? true : false;
    }
}

// Initialize
PMP_Bookmarks_System::init();
?>
