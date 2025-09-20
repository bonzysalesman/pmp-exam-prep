<?php
/**
 * PWA Initialization
 */

class PMP_PWA_Init {
    
    public function __construct() {
        add_action('wp_head', array($this, 'add_manifest'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_pwa_scripts'));
        add_action('init', array($this, 'add_offline_page'));
    }
    
    public function add_manifest() {
        $manifest_url = get_template_directory_uri() . '/pwa/manifest.json';
        echo '<link rel="manifest" href="' . esc_url($manifest_url) . '">';
        echo '<meta name="theme-color" content="#2563eb">';
        echo '<meta name="apple-mobile-web-app-capable" content="yes">';
    }
    
    public function enqueue_pwa_scripts() {
        wp_enqueue_script(
            'pmp-pwa-register',
            get_template_directory_uri() . '/pwa/register-sw.js',
            array(),
            '1.0.0',
            true
        );
    }
    
    public function add_offline_page() {
        add_rewrite_rule('^offline/?$', 'index.php?offline=1', 'top');
        add_filter('query_vars', function($vars) {
            $vars[] = 'offline';
            return $vars;
        });
        
        add_action('template_redirect', function() {
            if (get_query_var('offline')) {
                include get_template_directory() . '/pwa/offline.html';
                exit;
            }
        });
    }
}

new PMP_PWA_Init();
