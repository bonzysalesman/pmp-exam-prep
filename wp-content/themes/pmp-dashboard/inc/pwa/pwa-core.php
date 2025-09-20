<?php
/**
 * PWA WordPress Integration
 * Task: T008 - PWA WordPress Integration
 */

class PMP_PWA_Core {
    
    public static function init() {
        add_action('wp_head', [__CLASS__, 'add_pwa_meta_tags']);
        add_action('wp_enqueue_scripts', [__CLASS__, 'enqueue_pwa_scripts']);
        add_action('init', [__CLASS__, 'add_manifest_rewrite']);
        add_action('template_redirect', [__CLASS__, 'serve_manifest']);
        add_action('template_redirect', [__CLASS__, 'serve_service_worker']);
        add_action('admin_menu', [__CLASS__, 'add_pwa_admin_page']);
        add_filter('wp_headers', [__CLASS__, 'add_pwa_headers']);
    }
    
    /**
     * Add PWA meta tags to head
     */
    public static function add_pwa_meta_tags() {
        $theme_uri = get_template_directory_uri();
        
        ?>
        <!-- PWA Meta Tags -->
        <meta name="theme-color" content="#3b82f6">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="default">
        <meta name="apple-mobile-web-app-title" content="PMP Prep">
        <meta name="application-name" content="PMP Prep">
        <meta name="msapplication-TileColor" content="#3b82f6">
        <meta name="msapplication-TileImage" content="<?php echo $theme_uri; ?>/assets/icons/icon-144.png">
        
        <!-- Apple Touch Icons -->
        <link rel="apple-touch-icon" sizes="57x57" href="<?php echo $theme_uri; ?>/assets/icons/icon-57.png">
        <link rel="apple-touch-icon" sizes="60x60" href="<?php echo $theme_uri; ?>/assets/icons/icon-60.png">
        <link rel="apple-touch-icon" sizes="72x72" href="<?php echo $theme_uri; ?>/assets/icons/icon-72.png">
        <link rel="apple-touch-icon" sizes="76x76" href="<?php echo $theme_uri; ?>/assets/icons/icon-76.png">
        <link rel="apple-touch-icon" sizes="114x114" href="<?php echo $theme_uri; ?>/assets/icons/icon-114.png">
        <link rel="apple-touch-icon" sizes="120x120" href="<?php echo $theme_uri; ?>/assets/icons/icon-120.png">
        <link rel="apple-touch-icon" sizes="144x144" href="<?php echo $theme_uri; ?>/assets/icons/icon-144.png">
        <link rel="apple-touch-icon" sizes="152x152" href="<?php echo $theme_uri; ?>/assets/icons/icon-152.png">
        <link rel="apple-touch-icon" sizes="180x180" href="<?php echo $theme_uri; ?>/assets/icons/icon-180.png">
        
        <!-- Favicon -->
        <link rel="icon" type="image/png" sizes="32x32" href="<?php echo $theme_uri; ?>/assets/icons/icon-32.png">
        <link rel="icon" type="image/png" sizes="16x16" href="<?php echo $theme_uri; ?>/assets/icons/icon-16.png">
        <link rel="shortcut icon" href="<?php echo $theme_uri; ?>/assets/icons/favicon.ico">
        
        <!-- Web App Manifest -->
        <link rel="manifest" href="<?php echo home_url('/manifest.json'); ?>">
        
        <!-- PWA Configuration -->
        <script>
            window.pwaConfig = <?php echo json_encode(self::get_pwa_config()); ?>;
        </script>
        <?php
    }
    
    /**
     * Enqueue PWA scripts
     */
    public static function enqueue_pwa_scripts() {
        $theme_uri = get_template_directory_uri();
        
        // PWA registration script
        wp_enqueue_script(
            'pwa-register',
            $theme_uri . '/assets/js/pwa-register.js',
            [],
            '1.0.0',
            true
        );
        
        // Offline manager
        wp_enqueue_script(
            'offline-manager',
            $theme_uri . '/assets/js/offline-manager.js',
            [],
            '1.0.0',
            true
        );
        
        // Install prompt
        wp_enqueue_script(
            'install-prompt',
            $theme_uri . '/assets/js/install-prompt.js',
            [],
            '1.0.0',
            true
        );
        
        // Localize PWA data
        wp_localize_script('pwa-register', 'pwaData', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('pwa_nonce'),
            'cache_nonce' => wp_create_nonce('pwa_cache_nonce'),
            'config' => self::get_pwa_config(),
            'user_id' => get_current_user_id()
        ]);
    }
    
    /**
     * Get PWA configuration
     */
    public static function get_pwa_config() {
        return [
            'name' => get_bloginfo('name'),
            'short_name' => 'PMP Prep',
            'theme_color' => '#3b82f6',
            'background_color' => '#ffffff',
            'start_url' => home_url('/'),
            'scope' => home_url('/'),
            'display' => 'standalone',
            'orientation' => 'portrait-primary',
            'cache_version' => PMP_Cache_Manager::CACHE_VERSION,
            'offline_page' => home_url('/offline'),
            'features' => [
                'offline_content' => true,
                'push_notifications' => true,
                'background_sync' => true,
                'install_prompt' => true
            ]
        ];
    }
    
    /**
     * Add manifest rewrite rule
     */
    public static function add_manifest_rewrite() {
        add_rewrite_rule(
            '^manifest\.json$',
            'index.php?pwa_manifest=1',
            'top'
        );
        
        add_rewrite_rule(
            '^sw\.js$',
            'index.php?pwa_service_worker=1',
            'top'
        );
        
        add_rewrite_rule(
            '^offline\.html$',
            'index.php?pwa_offline_page=1',
            'top'
        );
    }
    
    /**
     * Serve manifest file
     */
    public static function serve_manifest() {
        if (get_query_var('pwa_manifest')) {
            header('Content-Type: application/json');
            header('Cache-Control: public, max-age=3600');
            
            $manifest_path = get_template_directory() . '/manifest.json';
            
            if (file_exists($manifest_path)) {
                readfile($manifest_path);
            } else {
                echo json_encode(self::generate_manifest());
            }
            
            exit;
        }
    }
    
    /**
     * Serve service worker
     */
    public static function serve_service_worker() {
        if (get_query_var('pwa_service_worker')) {
            header('Content-Type: application/javascript');
            header('Cache-Control: no-cache');
            
            $sw_path = get_template_directory() . '/assets/js/sw.js';
            
            if (file_exists($sw_path)) {
                readfile($sw_path);
            }
            
            exit;
        }
    }
    
    /**
     * Generate manifest dynamically
     */
    private static function generate_manifest() {
        $theme_uri = get_template_directory_uri();
        
        return [
            'name' => get_bloginfo('name') . ' - PMP Exam Preparation',
            'short_name' => 'PMP Prep',
            'description' => get_bloginfo('description'),
            'start_url' => home_url('/'),
            'scope' => home_url('/'),
            'display' => 'standalone',
            'orientation' => 'portrait-primary',
            'theme_color' => '#3b82f6',
            'background_color' => '#ffffff',
            'icons' => [
                [
                    'src' => $theme_uri . '/assets/icons/icon-192.png',
                    'sizes' => '192x192',
                    'type' => 'image/png',
                    'purpose' => 'any maskable'
                ],
                [
                    'src' => $theme_uri . '/assets/icons/icon-512.png',
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'purpose' => 'any maskable'
                ]
            ]
        ];
    }
    
    /**
     * Add PWA headers
     */
    public static function add_pwa_headers($headers) {
        // Add security headers for PWA
        $headers['X-Content-Type-Options'] = 'nosniff';
        $headers['X-Frame-Options'] = 'SAMEORIGIN';
        $headers['Referrer-Policy'] = 'strict-origin-when-cross-origin';
        
        // Add cache headers for PWA resources
        if (strpos($_SERVER['REQUEST_URI'], '/assets/') !== false) {
            $headers['Cache-Control'] = 'public, max-age=31536000'; // 1 year
        }
        
        return $headers;
    }
    
    /**
     * Add PWA admin page
     */
    public static function add_pwa_admin_page() {
        add_options_page(
            'PWA Settings',
            'PWA Settings',
            'manage_options',
            'pwa-settings',
            [__CLASS__, 'render_admin_page']
        );
    }
    
    /**
     * Render PWA admin page
     */
    public static function render_admin_page() {
        if (isset($_POST['submit'])) {
            // Save PWA settings
            update_option('pwa_enabled', isset($_POST['pwa_enabled']));
            update_option('pwa_offline_content', isset($_POST['pwa_offline_content']));
            update_option('pwa_push_notifications', isset($_POST['pwa_push_notifications']));
            update_option('pwa_install_prompt', isset($_POST['pwa_install_prompt']));
            
            echo '<div class="notice notice-success"><p>PWA settings saved!</p></div>';
        }
        
        $pwa_enabled = get_option('pwa_enabled', true);
        $offline_content = get_option('pwa_offline_content', true);
        $push_notifications = get_option('pwa_push_notifications', true);
        $install_prompt = get_option('pwa_install_prompt', true);
        
        ?>
        <div class="wrap">
            <h1>PWA Settings</h1>
            
            <form method="post" action="">
                <table class="form-table">
                    <tr>
                        <th scope="row">Enable PWA</th>
                        <td>
                            <input type="checkbox" name="pwa_enabled" value="1" <?php checked($pwa_enabled); ?>>
                            <label>Enable Progressive Web App features</label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Offline Content</th>
                        <td>
                            <input type="checkbox" name="pwa_offline_content" value="1" <?php checked($offline_content); ?>>
                            <label>Allow content caching for offline access</label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Push Notifications</th>
                        <td>
                            <input type="checkbox" name="pwa_push_notifications" value="1" <?php checked($push_notifications); ?>>
                            <label>Enable push notifications for study reminders</label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Install Prompt</th>
                        <td>
                            <input type="checkbox" name="pwa_install_prompt" value="1" <?php checked($install_prompt); ?>>
                            <label>Show install prompt to eligible users</label>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(); ?>
            </form>
            
            <h2>PWA Status</h2>
            <div class="pwa-status-dashboard">
                <?php self::render_pwa_status(); ?>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render PWA status dashboard
     */
    private static function render_pwa_status() {
        $cache_stats = PMP_Cache_Manager::get_cache_statistics();
        
        ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 20px 0;">
            <div style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; border-radius: 4px;">
                <h3>Service Worker</h3>
                <p><strong>Status:</strong> <span style="color: #00a32a;">Active</span></p>
                <p><strong>Version:</strong> <?php echo PMP_Cache_Manager::CACHE_VERSION; ?></p>
            </div>
            
            <div style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; border-radius: 4px;">
                <h3>Cache Statistics</h3>
                <p><strong>Resources:</strong> <?php echo $cache_stats['total_resources']; ?></p>
                <p><strong>Hits:</strong> <?php echo $cache_stats['cache_hits']; ?></p>
                <p><strong>Misses:</strong> <?php echo $cache_stats['cache_misses']; ?></p>
            </div>
            
            <div style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; border-radius: 4px;">
                <h3>Manifest</h3>
                <p><strong>URL:</strong> <a href="<?php echo home_url('/manifest.json'); ?>" target="_blank">View Manifest</a></p>
                <p><strong>Icons:</strong> 8 sizes</p>
                <p><strong>Shortcuts:</strong> 4 actions</p>
            </div>
            
            <div style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; border-radius: 4px;">
                <h3>Quick Actions</h3>
                <p><a href="<?php echo admin_url('admin.php?page=pwa-settings&action=clear_cache'); ?>" class="button">Clear Cache</a></p>
                <p><a href="<?php echo admin_url('admin.php?page=pwa-settings&action=test_notifications'); ?>" class="button">Test Notifications</a></p>
            </div>
        </div>
        <?php
    }
    
    /**
     * Check if PWA is enabled
     */
    public static function is_pwa_enabled() {
        return get_option('pwa_enabled', true);
    }
    
    /**
     * Check if feature is enabled
     */
    public static function is_feature_enabled($feature) {
        return get_option("pwa_{$feature}", true);
    }
    
    /**
     * Get PWA analytics data
     */
    public static function get_pwa_analytics() {
        global $wpdb;
        
        // This would integrate with analytics system
        return [
            'total_installs' => get_option('pwa_total_installs', 0),
            'active_users' => get_option('pwa_active_users', 0),
            'offline_sessions' => get_option('pwa_offline_sessions', 0),
            'push_subscriptions' => get_option('pwa_push_subscriptions', 0)
        ];
    }
    
    /**
     * Update PWA analytics
     */
    public static function update_analytics($metric, $value) {
        update_option("pwa_{$metric}", $value);
    }
    
    /**
     * Generate critical CSS
     */
    public static function generate_critical_css() {
        // This would extract critical above-the-fold CSS
        $critical_css = "
            body { margin: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
            .app-shell { min-height: 100vh; display: flex; flex-direction: column; }
            .app-header { background: #fff; border-bottom: 1px solid #e5e7eb; }
            .app-main { flex: 1; }
            .loading-spinner { width: 40px; height: 40px; border: 3px solid #e5e7eb; border-top: 3px solid #3b82f6; border-radius: 50%; animation: spin 1s linear infinite; }
            @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        ";
        
        return $critical_css;
    }
    
    /**
     * Create offline page
     */
    public static function create_offline_page() {
        $offline_content = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title>You\'re Offline - PMP Prep</title>
            <style>
                body { font-family: -apple-system, BlinkMacSystemFont, sans-serif; margin: 0; padding: 2rem; text-align: center; background: #f9fafb; }
                .offline-container { max-width: 400px; margin: 0 auto; }
                .offline-icon { font-size: 4rem; margin-bottom: 1rem; }
                h1 { color: #374151; margin-bottom: 1rem; }
                p { color: #6b7280; margin-bottom: 2rem; }
                .retry-btn { background: #3b82f6; color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 0.5rem; cursor: pointer; }
            </style>
        </head>
        <body>
            <div class="offline-container">
                <div class="offline-icon">📱</div>
                <h1>You\'re Offline</h1>
                <p>Check your internet connection and try again.</p>
                <button class="retry-btn" onclick="window.location.reload()">Try Again</button>
            </div>
        </body>
        </html>
        ';
        
        return $offline_content;
    }
    
    /**
     * Handle PWA-specific query vars
     */
    public static function add_query_vars($vars) {
        $vars[] = 'pwa_manifest';
        $vars[] = 'pwa_service_worker';
        $vars[] = 'pwa_offline_page';
        
        return $vars;
    }
    
    /**
     * Serve offline page
     */
    public static function serve_offline_page() {
        if (get_query_var('pwa_offline_page')) {
            header('Content-Type: text/html');
            header('Cache-Control: public, max-age=3600');
            
            echo self::create_offline_page();
            exit;
        }
    }
}

// Initialize PWA Core
PMP_PWA_Core::init();

// Add query vars filter
add_filter('query_vars', ['PMP_PWA_Core', 'add_query_vars']);
?>
