<?php
/**
 * Plugin Name: Dev.Mode
 * Plugin URI: https://github.com/julioz07/dev-mode-wordpress
 * Description: Alternates between two states: Active (allows changes) and Protected (blocks modifications to core, plugins, themes, and user creation). Free for personal and non-commercial use.
 * Version: 1.1.1
 * Author: Júlio Rodrigues
 * Author URI: https://julio-cr.pt/
 * Text Domain: dev-mode
 * Domain Path: /languages
 * Requires at least: 6.0
 * Tested up to: 6.6
 * Requires PHP: 8.1
 * License: CC BY-NC-SA 4.0
 * License URI: https://creativecommons.org/licenses/by-nc-sa/4.0/
 * Network: false
 *
 * Copyright (c) 2025 Júlio Rodrigues (https://github.com/julioz07)
 * 
 * This work is licensed under the Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International License.
 * Commercial use is prohibited. Contributions are welcome!
 * 
 * Developed with assistance from Claude Sonnet (Anthropic AI)
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('DEVMODE_VERSION', '1.1.1');
define('DEVMODE_PLUGIN_FILE', __FILE__);
define('DEVMODE_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('DEVMODE_PLUGIN_URL', plugin_dir_url(__FILE__));
define('DEVMODE_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Simple autoloader for plugin classes
spl_autoload_register(function ($class) {
    if (strpos($class, 'DevMode\\') === 0) {
        $class_name = str_replace('DevMode\\', '', $class);
        $class_name = strtolower(str_replace('_', '-', $class_name));
        $file = DEVMODE_PLUGIN_DIR . 'includes/class-devmode-' . $class_name . '.php';
        
        if (file_exists($file)) {
            require_once $file;
        }
    }
});

/**
 * Helper function to check if Dev.Mode is in protected state
 *
 * @return bool True if in protected state, false if active
 */
function devmode_is_protected() {
    return get_option('devmode_state', 'protected') === 'protected';
}

/**
 * Helper function to get current Dev.Mode state
 *
 * @return string 'active' or 'protected'
 */
function devmode_get_state() {
    return get_option('devmode_state', 'protected');
}

/**
 * Helper function to set Dev.Mode state
 *
 * @param string $state 'active' or 'protected'
 * @return bool True on success, false on failure
 */
function devmode_set_state($state) {
    if (!in_array($state, ['active', 'protected'])) {
        return false;
    }
    
    $old_state = devmode_get_state();
    $result = update_option('devmode_state', $state);
    
    if ($result && $old_state !== $state) {
        do_action('devmode_state_changed', $state, $old_state);
    }
    
    return $result;
}

/**
 * Helper function to get plugin options
 *
 * @return array Plugin options with defaults
 */
function devmode_get_options() {
    $defaults = [
        'block_user_creation' => true,
        'disable_file_modifications' => true,
        'block_uploads_php' => true,
        'auto_revert_hours' => 0,
    ];
    
    return wp_parse_args(get_option('devmode_options', []), $defaults);
}

/**
 * Main plugin initialization
 */
function devmode_init() {
    // Load text domain for translations
    load_plugin_textdomain('dev-mode', false, dirname(DEVMODE_PLUGIN_BASENAME) . '/languages');
    
    // Initialize core functionality
    if (class_exists('DevMode\\Core')) {
        new DevMode\Core();
    }
    
    // Initialize admin functionality if in admin area
    if (is_admin() && class_exists('DevMode\\Admin')) {
        new DevMode\Admin();
    }
    
    // Initialize hardening functionality
    if (class_exists('DevMode\\Hardener')) {
        new DevMode\Hardener();
    }
}

// Hook into plugins_loaded to ensure all WordPress functions are available
add_action('plugins_loaded', 'devmode_init');

/**
 * Plugin activation hook
 */
function devmode_activate() {
    // Check if user has proper permissions
    if (!current_user_can('activate_plugins')) {
        wp_die(__('You do not have sufficient permissions to activate plugins.', 'dev-mode'));
    }
    
    // Set default state to protected on activation
    if (!get_option('devmode_state')) {
        update_option('devmode_state', 'protected');
    }
    
    // Set default options
    if (!get_option('devmode_options')) {
        update_option('devmode_options', [
            'block_user_creation' => true,
            'disable_file_modifications' => true,
            'block_uploads_php' => true,
            'auto_revert_hours' => 0,
        ]);
    }
    
    // Clear any scheduled auto-revert events
    wp_clear_scheduled_hook('devmode_auto_revert');
}
register_activation_hook(__FILE__, 'devmode_activate');

/**
 * Plugin deactivation hook
 */
function devmode_deactivate() {
    // Check if user has proper permissions
    if (!current_user_can('deactivate_plugins')) {
        wp_die(__('You do not have sufficient permissions to deactivate plugins.', 'dev-mode'));
    }
    
    // Clear scheduled auto-revert events
    wp_clear_scheduled_hook('devmode_auto_revert');
    
    // Optionally remove .htaccess rules from uploads directory
    if (class_exists('DevMode\\Hardener')) {
        $hardener = new DevMode\Hardener();
        $hardener->remove_uploads_protection();
    }
}
register_deactivation_hook(__FILE__, 'devmode_deactivate');

/**
 * Plugin uninstall hook
 */
function devmode_uninstall() {
    // Remove all plugin options
    delete_option('devmode_state');
    delete_option('devmode_options');
    
    // Remove any scheduled events
    wp_clear_scheduled_hook('devmode_auto_revert');
    
    // Remove hardening files
    if (class_exists('DevMode\\Hardener')) {
        $hardener = new DevMode\Hardener();
        $hardener->remove_uploads_protection();
    }
}
register_uninstall_hook(__FILE__, 'devmode_uninstall');