<?php

namespace DevMode;

/**
 * Core functionality for Dev.Mode plugin
 *
 * Handles the main logic, state management, and security hooks
 */
class Core {
    
    /**
     * Constructor - Initialize core hooks and functionality
     */
    public function __construct() {
        add_action('init', [$this, 'init']);
        add_action('devmode_state_changed', [$this, 'handle_state_change'], 10, 2);
        add_action('devmode_auto_revert', [$this, 'auto_revert_to_protected']);
        
        // Apply protections if in protected mode
        if (devmode_is_protected()) {
            $this->apply_protected_mode();
        }
    }
    
    /**
     * Initialize core functionality
     */
    public function init() {
        // Schedule auto-revert if enabled and in active mode
        $this->maybe_schedule_auto_revert();
    }
    
    /**
     * Apply all protections when in protected mode
     */
    private function apply_protected_mode() {
        // Define file editing and modification constants if not already set
        if (!defined('DISALLOW_FILE_EDIT')) {
            define('DISALLOW_FILE_EDIT', true);
        }
        if (!defined('DISALLOW_FILE_MODS')) {
            define('DISALLOW_FILE_MODS', true);
        }
        
        // Block automatic updates
        add_filter('automatic_updater_disabled', '__return_true');
        
        // Block plugin/theme installations and updates
        add_filter('plugins_api', [$this, 'block_plugins_api'], 10, 3);
        add_filter('upgrader_pre_download', [$this, 'block_upgrader_download'], 10, 3);
        add_filter('upgrader_pre_install', [$this, 'block_upgrader_install'], 10, 2);
        add_filter('file_mod_allowed', [$this, 'block_file_modifications'], 10, 2);
        
        // Block user creation and management
        $options = devmode_get_options();
        if ($options['block_user_creation']) {
            add_filter('map_meta_cap', [$this, 'block_user_capabilities'], 10, 4);
            add_action('user_register', [$this, 'block_user_registration']);
            add_filter('rest_pre_insert_user', [$this, 'block_rest_user_creation'], 10, 2);
        }
        
        // Block file editing in admin
        add_action('admin_init', [$this, 'block_admin_file_editing']);
    }
    
    /**
     * Block plugins API calls
     */
    public function block_plugins_api($result, $action, $args) {
        if (in_array($action, ['plugin_information', 'query_plugins'])) {
            return new \WP_Error(
                'devmode_protected',
                __('Plugin installations and updates are blocked while Dev.Mode is in Protected state.', 'dev-mode')
            );
        }
        return $result;
    }
    
    /**
     * Block upgrader downloads
     */
    public function block_upgrader_download($reply, $package, $upgrader) {
        return new \WP_Error(
            'devmode_protected',
            __('Downloads are blocked while Dev.Mode is in Protected state.', 'dev-mode')
        );
    }
    
    /**
     * Block upgrader installations
     */
    public function block_upgrader_install($response, $hook_extra) {
        return new \WP_Error(
            'devmode_protected',
            __('Installations are blocked while Dev.Mode is in Protected state.', 'dev-mode')
        );
    }
    
    /**
     * Block file modifications
     */
    public function block_file_modifications($allow, $context) {
        return false;
    }
    
    /**
     * Block user-related capabilities
     */
    public function block_user_capabilities($caps, $cap, $user_id, $args) {
        $blocked_caps = ['create_users', 'promote_users', 'delete_users', 'edit_users'];
        
        if (in_array($cap, $blocked_caps)) {
            $caps[] = 'do_not_allow';
        }
        
        return $caps;
    }
    
    /**
     * Block user registration
     */
    public function block_user_registration($user_id) {
        if (is_admin()) {
            wp_die(
                __('User creation is blocked while Dev.Mode is in Protected state.', 'dev-mode'),
                __('Access Denied', 'dev-mode'),
                ['response' => 403]
            );
        }
    }
    
    /**
     * Block REST API user creation
     */
    public function block_rest_user_creation($prepared_user, $request) {
        return new \WP_Error(
            'devmode_protected',
            __('User creation via REST API is blocked while Dev.Mode is in Protected state.', 'dev-mode'),
            ['status' => 403]
        );
    }
    
    /**
     * Block admin file editing pages
     */
    public function block_admin_file_editing() {
        global $pagenow;
        
        $blocked_pages = ['plugin-editor.php', 'theme-editor.php'];
        
        if (in_array($pagenow, $blocked_pages)) {
            wp_die(
                __('File editing is blocked while Dev.Mode is in Protected state.', 'dev-mode'),
                __('Access Denied', 'dev-mode'),
                ['response' => 403]
            );
        }
    }
    
    /**
     * Handle state changes
     */
    public function handle_state_change($new_state, $old_state) {
        // Log the state change
        $this->log_state_change($new_state, $old_state);
        
        // Handle auto-revert scheduling
        if ($new_state === 'active') {
            $this->maybe_schedule_auto_revert();
        } else {
            $this->clear_auto_revert();
        }
        
        // Apply or remove hardening based on new state
        if (class_exists('DevMode\\Hardener')) {
            $hardener = new Hardener();
            if ($new_state === 'protected') {
                $hardener->apply_uploads_protection();
            }
        }
        
        // Trigger action for other plugins/themes to hook into
        do_action('devmode_after_state_change', $new_state, $old_state);
    }
    
    /**
     * Maybe schedule auto-revert to protected mode
     */
    private function maybe_schedule_auto_revert() {
        $options = devmode_get_options();
        
        if ($options['auto_revert_hours'] > 0 && devmode_get_state() === 'active') {
            // Clear any existing scheduled event
            wp_clear_scheduled_hook('devmode_auto_revert');
            
            // Schedule new event
            $timestamp = time() + ($options['auto_revert_hours'] * HOUR_IN_SECONDS);
            wp_schedule_single_event($timestamp, 'devmode_auto_revert');
        }
    }
    
    /**
     * Clear auto-revert scheduling
     */
    private function clear_auto_revert() {
        wp_clear_scheduled_hook('devmode_auto_revert');
    }
    
    /**
     * Auto-revert to protected mode
     */
    public function auto_revert_to_protected() {
        if (devmode_get_state() === 'active') {
            devmode_set_state('protected');
            
            // Add admin notice about auto-revert
            add_option('devmode_auto_reverted', true);
        }
    }
    
    /**
     * Log state changes to file
     */
    private function log_state_change($new_state, $old_state) {
        $log_file = WP_CONTENT_DIR . '/devmode.log';
        $current_user = wp_get_current_user();
        $timestamp = current_time('Y-m-d H:i:s');
        
        $log_entry = sprintf(
            "[%s] State changed from %s to %s by user %s (ID: %d)\n",
            $timestamp,
            $old_state,
            $new_state,
            $current_user->user_login,
            $current_user->ID
        );
        
        // Rotate log if it gets too large (> 1MB)
        if (file_exists($log_file) && filesize($log_file) > 1048576) {
            $old_log = file_get_contents($log_file);
            $lines = explode("\n", $old_log);
            $lines = array_slice($lines, -500); // Keep last 500 lines
            file_put_contents($log_file, implode("\n", $lines));
        }
        
        // Append new log entry
        file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Get recent log entries
     *
     * @param int $limit Number of entries to return
     * @return array Log entries
     */
    public function get_log_entries($limit = 50) {
        $log_file = WP_CONTENT_DIR . '/devmode.log';
        
        if (!file_exists($log_file)) {
            return [];
        }
        
        $content = file_get_contents($log_file);
        $lines = array_filter(explode("\n", $content));
        $lines = array_slice($lines, -$limit);
        
        return array_reverse($lines);
    }
}