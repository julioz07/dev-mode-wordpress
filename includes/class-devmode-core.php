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
        
        // Block file uploads with dangerous extensions
        add_filter('upload_mimes', [$this, 'block_dangerous_uploads'], 1);
        add_filter('wp_handle_upload_prefilter', [$this, 'block_php_uploads'], 1);
        
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
            $this->log_blocked_action('plugins_api', [
                'action' => $action,
                'args' => $args,
                'user_agent' => sanitize_text_field($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown')
            ]);
            
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
        $this->log_blocked_action('upgrader_download', [
            'package' => $package,
            'upgrader_class' => get_class($upgrader),
            'request_uri' => sanitize_text_field($_SERVER['REQUEST_URI'] ?? '')
        ]);
        
        return new \WP_Error(
            'devmode_protected',
            __('Downloads are blocked while Dev.Mode is in Protected state.', 'dev-mode')
        );
    }
    
    /**
     * Block upgrader installations
     */
    public function block_upgrader_install($response, $hook_extra) {
        $this->log_blocked_action('upgrader_install', [
            'hook_extra' => $hook_extra,
            'type' => $hook_extra['type'] ?? 'unknown'
        ]);
        
        return new \WP_Error(
            'devmode_protected',
            __('Installations are blocked while Dev.Mode is in Protected state.', 'dev-mode')
        );
    }
    
    /**
     * Block file modifications
     */
    public function block_file_modifications($allow, $context) {
        $this->log_blocked_action('file_modification', [
            'context' => $context,
            'request_uri' => $_SERVER['REQUEST_URI'] ?? '',
            'referer' => wp_get_referer()
        ]);
        
        return false;
    }
    
    /**
     * Block dangerous file uploads by removing dangerous MIME types
     */
    public function block_dangerous_uploads($mimes) {
        $dangerous_extensions = [
            'php', 'php3', 'php4', 'php5', 'php7', 'php8', 'phtml', 'pht', 'phps',
            'asp', 'aspx', 'jsp', 'cgi', 'pl', 'py', 'rb', 'sh', 'exe', 'bat',
            'com', 'scr', 'vbs', 'ws', 'wsf'
        ];
        
        foreach ($dangerous_extensions as $ext) {
            if (isset($mimes[$ext])) {
                unset($mimes[$ext]);
            }
        }
        
        return $mimes;
    }
    
    /**
     * Block PHP file uploads with detailed logging
     */
    public function block_php_uploads($file) {
        $filename = $file['name'] ?? '';
        $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        $dangerous_extensions = [
            'php', 'php3', 'php4', 'php5', 'php7', 'php8', 'phtml', 'pht', 'phps',
            'asp', 'aspx', 'jsp', 'cgi', 'pl', 'py', 'rb', 'sh', 'exe', 'bat',
            'com', 'scr', 'vbs', 'ws', 'wsf'
        ];
        
        if (in_array($file_ext, $dangerous_extensions)) {
            $this->log_blocked_action('dangerous_file_upload', [
                'filename' => $filename,
                'extension' => $file_ext,
                'size' => $file['size'] ?? 0,
                'type' => $file['type'] ?? 'unknown',
                'upload_path' => sanitize_text_field($_SERVER['REQUEST_URI'] ?? '')
            ]);
            
            $file['error'] = sprintf(
                __('File upload blocked: .%s files are not allowed while Dev.Mode is in Protected state.', 'dev-mode'),
                $file_ext
            );
        }
        
        return $file;
    }
    
    /**
     * Block user-related capabilities
     */
    public function block_user_capabilities($caps, $cap, $user_id, $args) {
        $blocked_caps = ['create_users', 'promote_users', 'delete_users', 'edit_users'];
        
        if (in_array($cap, $blocked_caps)) {
            $this->log_blocked_action('user_capability', [
                'capability' => $cap,
                'target_user_id' => $args[0] ?? null,
                'requesting_user_id' => $user_id
            ]);
            
            $caps[] = 'do_not_allow';
        }
        
        return $caps;
    }
    
    /**
     * Block user registration
     */
    public function block_user_registration($user_id) {
        $this->log_blocked_action('user_registration', [
            'attempted_user_id' => $user_id,
            'registration_method' => 'admin_panel'
        ]);
        
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
        $this->log_blocked_action('rest_user_creation', [
            'user_data' => [
                'username' => $prepared_user->user_login ?? 'unknown',
                'email' => $prepared_user->user_email ?? 'unknown'
            ],
            'request_method' => $request->get_method(),
            'user_agent' => $request->get_header('User-Agent')
        ]);
        
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
            $this->log_blocked_action('admin_file_editing', [
                'page' => $pagenow,
                'query_params' => array_map('sanitize_text_field', $_GET)
            ]);
            
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
        $current_user = wp_get_current_user();
        $timestamp = current_time('Y-m-d H:i:s');
        
        $log_entry = sprintf(
            "[%s] STATE_CHANGE: %s → %s | User: %s (ID: %d) | IP: %s\n",
            $timestamp,
            strtoupper($old_state),
            strtoupper($new_state),
            $current_user->user_login,
            $current_user->ID,
            $this->get_client_ip()
        );
        
        $this->write_to_log($log_entry);
    }
    
    /**
     * Log blocked actions with detailed information
     */
    private function log_blocked_action($action_type, $details = []) {
        $current_user = wp_get_current_user();
        $timestamp = current_time('Y-m-d H:i:s');
        
        // Format details for logging
        $details_str = '';
        if (!empty($details)) {
            $formatted_details = [];
            foreach ($details as $key => $value) {
                if (is_array($value)) {
                    $value = json_encode($value);
                } elseif (is_object($value)) {
                    $value = get_class($value);
                }
                $formatted_details[] = $key . '=' . $value;
            }
            $details_str = ' | Details: ' . implode(', ', $formatted_details);
        }
        
        $log_entry = sprintf(
            "[%s] BLOCKED_%s: Action blocked in Protected mode | User: %s (ID: %d) | IP: %s%s\n",
            $timestamp,
            strtoupper($action_type),
            $current_user->user_login ?: 'guest',
            $current_user->ID ?: 0,
            $this->get_client_ip(),
            $details_str
        );
        
        $this->write_to_log($log_entry);
    }
    
    /**
     * Get client IP address
     */
    private function get_client_ip() {
        $ip_fields = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];
        
        foreach ($ip_fields as $field) {
            if (!empty($_SERVER[$field])) {
                $ip = sanitize_text_field($_SERVER[$field]);
                // Handle comma-separated IPs (from proxies)
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return sanitize_text_field($_SERVER['REMOTE_ADDR'] ?? 'unknown');
    }
    
    /**
     * Write entry to log file with rotation
     */
    private function write_to_log($log_entry) {
        $log_file = WP_CONTENT_DIR . '/devmode.log';
        
        // Rotate log if it gets too large (> 2MB)
        if (file_exists($log_file) && filesize($log_file) > 2097152) {
            $old_log = $this->safe_file_get_contents($log_file);
            if ($old_log !== false) {
                $lines = explode("\n", $old_log);
                $lines = array_slice($lines, -1000); // Keep last 1000 lines
                file_put_contents($log_file, implode("\n", $lines));
            }
        }
        
        // Append new log entry
        file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Safe file_get_contents wrapper with local file validation
     */
    private function safe_file_get_contents($file_path) {
        // Ensure it's a local file path and within WordPress directory structure
        $real_path = realpath($file_path);
        $wp_content_dir = realpath(WP_CONTENT_DIR);
        
        if ($real_path === false || strpos($real_path, $wp_content_dir) !== 0) {
            return false;
        }
        
        return file_get_contents($real_path);
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
        
        $content = $this->safe_file_get_contents($log_file);
        if ($content === false) {
            return [];
        }
        
        $lines = array_filter(explode("\n", $content));
        $lines = array_slice($lines, -$limit);
        
        return array_reverse($lines);
    }
}