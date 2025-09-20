<?php
/**
 * Taxonomy Registration
 * Task: T003 - Taxonomy Registration
 */

class PMP_Content_Taxonomies {
    
    public static function init() {
        add_action('init', [__CLASS__, 'register_taxonomies']);
    }
    
    /**
     * Register all taxonomies
     */
    public static function register_taxonomies() {
        self::register_pmp_domain();
        self::register_pmp_knowledge_area();
        self::register_pmp_topic();
        self::register_resource_category();
        self::register_difficulty_level();
        self::register_content_tags();
    }
    
    /**
     * Register PMP Domain taxonomy
     */
    private static function register_pmp_domain() {
        register_taxonomy('pmp_domain', ['lesson', 'practice_test', 'question'], [
            'labels' => [
                'name' => 'PMP Domains',
                'singular_name' => 'PMP Domain',
                'menu_name' => 'PMP Domains',
                'all_items' => 'All Domains',
                'edit_item' => 'Edit Domain',
                'view_item' => 'View Domain',
                'update_item' => 'Update Domain',
                'add_new_item' => 'Add New Domain',
                'new_item_name' => 'New Domain Name',
                'search_items' => 'Search Domains',
                'not_found' => 'No domains found'
            ],
            'hierarchical' => false,
            'public' => true,
            'show_ui' => true,
            'show_admin_column' => true,
            'show_in_nav_menus' => true,
            'show_tagcloud' => false,
            'show_in_rest' => true,
            'rewrite' => ['slug' => 'domain']
        ]);
        
        // Insert default domains
        self::insert_default_domains();
    }
    
    /**
     * Register PMP Knowledge Area taxonomy
     */
    private static function register_pmp_knowledge_area() {
        register_taxonomy('pmp_knowledge_area', ['lesson', 'practice_test', 'question'], [
            'labels' => [
                'name' => 'Knowledge Areas',
                'singular_name' => 'Knowledge Area',
                'menu_name' => 'Knowledge Areas',
                'all_items' => 'All Knowledge Areas',
                'edit_item' => 'Edit Knowledge Area',
                'view_item' => 'View Knowledge Area',
                'update_item' => 'Update Knowledge Area',
                'add_new_item' => 'Add New Knowledge Area',
                'new_item_name' => 'New Knowledge Area Name',
                'search_items' => 'Search Knowledge Areas',
                'not_found' => 'No knowledge areas found'
            ],
            'hierarchical' => true,
            'public' => true,
            'show_ui' => true,
            'show_admin_column' => true,
            'show_in_nav_menus' => true,
            'show_tagcloud' => false,
            'show_in_rest' => true,
            'rewrite' => ['slug' => 'knowledge-area']
        ]);
        
        // Insert default knowledge areas
        self::insert_default_knowledge_areas();
    }
    
    /**
     * Register PMP Topic taxonomy
     */
    private static function register_pmp_topic() {
        register_taxonomy('pmp_topic', ['lesson', 'practice_test', 'question'], [
            'labels' => [
                'name' => 'Topics',
                'singular_name' => 'Topic',
                'menu_name' => 'Topics',
                'all_items' => 'All Topics',
                'edit_item' => 'Edit Topic',
                'view_item' => 'View Topic',
                'update_item' => 'Update Topic',
                'add_new_item' => 'Add New Topic',
                'new_item_name' => 'New Topic Name',
                'search_items' => 'Search Topics',
                'not_found' => 'No topics found'
            ],
            'hierarchical' => true,
            'public' => true,
            'show_ui' => true,
            'show_admin_column' => false,
            'show_in_nav_menus' => true,
            'show_tagcloud' => true,
            'show_in_rest' => true,
            'rewrite' => ['slug' => 'topic']
        ]);
    }
    
    /**
     * Register Resource Category taxonomy
     */
    private static function register_resource_category() {
        register_taxonomy('resource_category', ['resource'], [
            'labels' => [
                'name' => 'Resource Categories',
                'singular_name' => 'Resource Category',
                'menu_name' => 'Categories',
                'all_items' => 'All Categories',
                'edit_item' => 'Edit Category',
                'view_item' => 'View Category',
                'update_item' => 'Update Category',
                'add_new_item' => 'Add New Category',
                'new_item_name' => 'New Category Name',
                'search_items' => 'Search Categories',
                'not_found' => 'No categories found'
            ],
            'hierarchical' => true,
            'public' => true,
            'show_ui' => true,
            'show_admin_column' => true,
            'show_in_nav_menus' => true,
            'show_tagcloud' => false,
            'show_in_rest' => true,
            'rewrite' => ['slug' => 'resource-category']
        ]);
        
        // Insert default resource categories
        self::insert_default_resource_categories();
    }
    
    /**
     * Register Difficulty Level taxonomy
     */
    private static function register_difficulty_level() {
        register_taxonomy('difficulty_level', ['lesson', 'practice_test', 'question', 'resource'], [
            'labels' => [
                'name' => 'Difficulty Levels',
                'singular_name' => 'Difficulty Level',
                'menu_name' => 'Difficulty',
                'all_items' => 'All Levels',
                'edit_item' => 'Edit Level',
                'view_item' => 'View Level',
                'update_item' => 'Update Level',
                'add_new_item' => 'Add New Level',
                'new_item_name' => 'New Level Name',
                'search_items' => 'Search Levels',
                'not_found' => 'No levels found'
            ],
            'hierarchical' => false,
            'public' => true,
            'show_ui' => true,
            'show_admin_column' => true,
            'show_in_nav_menus' => true,
            'show_tagcloud' => false,
            'show_in_rest' => true,
            'rewrite' => ['slug' => 'difficulty']
        ]);
        
        // Insert default difficulty levels
        self::insert_default_difficulty_levels();
    }
    
    /**
     * Register Content Tags taxonomy
     */
    private static function register_content_tags() {
        register_taxonomy('content_tags', ['lesson', 'practice_test', 'question', 'resource'], [
            'labels' => [
                'name' => 'Content Tags',
                'singular_name' => 'Content Tag',
                'menu_name' => 'Tags',
                'all_items' => 'All Tags',
                'edit_item' => 'Edit Tag',
                'view_item' => 'View Tag',
                'update_item' => 'Update Tag',
                'add_new_item' => 'Add New Tag',
                'new_item_name' => 'New Tag Name',
                'search_items' => 'Search Tags',
                'not_found' => 'No tags found'
            ],
            'hierarchical' => false,
            'public' => true,
            'show_ui' => true,
            'show_admin_column' => false,
            'show_in_nav_menus' => false,
            'show_tagcloud' => true,
            'show_in_rest' => true,
            'rewrite' => ['slug' => 'tag']
        ]);
    }
    
    /**
     * Insert default PMP domains
     */
    private static function insert_default_domains() {
        $domains = [
            'people' => 'People (42%)',
            'process' => 'Process (50%)',
            'business_environment' => 'Business Environment (8%)'
        ];
        
        foreach ($domains as $slug => $name) {
            if (!term_exists($name, 'pmp_domain')) {
                wp_insert_term($name, 'pmp_domain', ['slug' => $slug]);
            }
        }
    }
    
    /**
     * Insert default knowledge areas
     */
    private static function insert_default_knowledge_areas() {
        $knowledge_areas = [
            'Project Integration Management',
            'Project Scope Management',
            'Project Schedule Management',
            'Project Cost Management',
            'Project Quality Management',
            'Project Resource Management',
            'Project Communications Management',
            'Project Risk Management',
            'Project Procurement Management',
            'Project Stakeholder Management'
        ];
        
        foreach ($knowledge_areas as $area) {
            if (!term_exists($area, 'pmp_knowledge_area')) {
                wp_insert_term($area, 'pmp_knowledge_area');
            }
        }
    }
    
    /**
     * Insert default resource categories
     */
    private static function insert_default_resource_categories() {
        $categories = [
            'Study Guides',
            'Templates',
            'Checklists',
            'Reference Materials',
            'Practice Exams',
            'Video Content',
            'Audio Content',
            'Interactive Tools'
        ];
        
        foreach ($categories as $category) {
            if (!term_exists($category, 'resource_category')) {
                wp_insert_term($category, 'resource_category');
            }
        }
    }
    
    /**
     * Insert default difficulty levels
     */
    private static function insert_default_difficulty_levels() {
        $levels = [
            'beginner' => 'Beginner',
            'intermediate' => 'Intermediate',
            'advanced' => 'Advanced'
        ];
        
        foreach ($levels as $slug => $name) {
            if (!term_exists($name, 'difficulty_level')) {
                wp_insert_term($name, 'difficulty_level', ['slug' => $slug]);
            }
        }
    }
}

// Initialize
PMP_Content_Taxonomies::init();
?>
