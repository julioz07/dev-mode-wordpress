<?php

namespace DevMode;

/**
 * Admin functionality for Dev.Mode plugin
 *
 * Handles admin interface, settings page, admin bar, and AJAX functionality
 */
class Admin {
    
    /**
     * Constructor - Initialize admin hooks
     */
    public function __construct() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'init_settings']);
        add_action('admin_bar_menu', [$this, 'add_admin_bar_item'], 100);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        add_action('wp_ajax_devmode_toggle', [$this, 'handle_ajax_toggle']);
        add_action('admin_notices', [$this, 'show_admin_notices']);
        
        // Handle settings form submission
        add_action('admin_post_devmode_save_settings', [$this, 'save_settings']);
    }
    
    /**
     * Add admin menu page
     */
    public function add_admin_menu() {
        add_options_page(
            __('Dev.Mode Settings', 'dev-mode'),
            __('Dev.Mode', 'dev-mode'),
            'manage_options',
            'devmode-settings',
            [$this, 'settings_page']
        );
    }
    
    /**
     * Initialize settings
     */
    public function init_settings() {
        register_setting('devmode_settings', 'devmode_options', [$this, 'validate_options']);
        
        // Main settings section
        add_settings_section(
            'devmode_main',
            __('Dev.Mode Configuration', 'dev-mode'),
            [$this, 'settings_section_callback'],
            'devmode-settings'
        );
        
        // State toggle field
        add_settings_field(
            'devmode_state',
            __('Current State', 'dev-mode'),
            [$this, 'state_field_callback'],
            'devmode-settings',
            'devmode_main'
        );
        
        // Block user creation option
        add_settings_field(
            'block_user_creation',
            __('Block User Creation', 'dev-mode'),
            [$this, 'checkbox_field_callback'],
            'devmode-settings',
            'devmode_main',
            ['field' => 'block_user_creation', 'description' => __('Block creation of new users when in Protected mode', 'dev-mode')]
        );
        
        // Disable file modifications option
        add_settings_field(
            'disable_file_modifications',
            __('Disable File Modifications', 'dev-mode'),
            [$this, 'checkbox_field_callback'],
            'devmode-settings',
            'devmode_main',
            ['field' => 'disable_file_modifications', 'description' => __('Disable updates/installations/edits when in Protected mode', 'dev-mode')]
        );
        
        // Block uploads PHP option
        add_settings_field(
            'block_uploads_php',
            __('Block PHP in Uploads', 'dev-mode'),
            [$this, 'checkbox_field_callback'],
            'devmode-settings',
            'devmode_main',
            ['field' => 'block_uploads_php', 'description' => __('Apply rules to prevent PHP execution in /uploads directory', 'dev-mode')]
        );
        
        // Auto-revert hours option
        add_settings_field(
            'auto_revert_hours',
            __('Auto-Revert Hours', 'dev-mode'),
            [$this, 'number_field_callback'],
            'devmode-settings',
            'devmode_main',
            ['field' => 'auto_revert_hours', 'description' => __('Automatically revert to Protected mode after X hours in Dev Mode (0 = disabled)', 'dev-mode')]
        );
    }
    
    /**
     * Settings section callback
     */
    public function settings_section_callback() {
        echo '<p>' . __('Configure Dev.Mode settings and security options.', 'dev-mode') . '</p>';
    }
    
    /**
     * State toggle field callback
     */
    public function state_field_callback() {
        $current_state = devmode_get_state();
        $is_active = $current_state === 'active';
        
        $options = devmode_get_options();
        $auto_revert_info = '';
        if ($is_active && $options['auto_revert_hours'] > 0) {
            $next_event = wp_next_scheduled('devmode_auto_revert');
            if ($next_event) {
                $time_left = $next_event - time();
                $hours_left = round($time_left / 3600, 1);
                $auto_revert_info = sprintf(
                    __(' (will auto-revert in %s hours)', 'dev-mode'),
                    $hours_left
                );
            }
        }
        
        echo '<div class="devmode-state-toggle">';
        echo '<button type="button" id="devmode-toggle-btn" class="button button-large devmode-btn-' . $current_state . '" data-nonce="' . wp_create_nonce('devmode_toggle') . '">';
        echo $is_active ? __('Dev Mode: Active', 'dev-mode') : __('Dev Mode: Protected', 'dev-mode');
        echo '</button>';
        echo '<span class="devmode-state-info">' . $auto_revert_info . '</span>';
        echo '</div>';
        
        echo '<p class="description">';
        if ($is_active) {
            echo __('Currently in Active state - changes are allowed.', 'dev-mode');
        } else {
            echo __('Currently in Protected state - changes are blocked.', 'dev-mode');
        }
        echo '</p>';
    }
    
    /**
     * Checkbox field callback
     */
    public function checkbox_field_callback($args) {
        $options = devmode_get_options();
        $field = $args['field'];
        $value = isset($options[$field]) ? $options[$field] : false;
        
        echo '<label>';
        echo '<input type="checkbox" name="devmode_options[' . esc_attr($field) . ']" value="1" ' . checked($value, true, false) . ' />';
        echo ' ' . esc_html($args['description']);
        echo '</label>';
    }
    
    /**
     * Number field callback
     */
    public function number_field_callback($args) {
        $options = devmode_get_options();
        $field = $args['field'];
        $value = isset($options[$field]) ? $options[$field] : 0;
        
        echo '<input type="number" name="devmode_options[' . esc_attr($field) . ']" value="' . esc_attr($value) . '" min="0" max="168" class="small-text" />';
        echo '<p class="description">' . esc_html($args['description']) . '</p>';
    }
    
    /**
     * Validate options
     */
    public function validate_options($input) {
        $output = [];
        
        // Validate checkboxes
        $checkboxes = ['block_user_creation', 'disable_file_modifications', 'block_uploads_php'];
        foreach ($checkboxes as $checkbox) {
            $output[$checkbox] = isset($input[$checkbox]) && $input[$checkbox] == '1';
        }
        
        // Validate auto-revert hours
        $output['auto_revert_hours'] = isset($input['auto_revert_hours']) ? intval($input['auto_revert_hours']) : 0;
        if ($output['auto_revert_hours'] < 0 || $output['auto_revert_hours'] > 168) {
            $output['auto_revert_hours'] = 0;
        }
        
        return $output;
    }
    
    /**
     * Settings page HTML
     */
    public function settings_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'dev-mode'));
        }
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <form method="post" action="options.php">
                <?php
                settings_fields('devmode_settings');
                do_settings_sections('devmode-settings');
                submit_button();
                ?>
            </form>
            
            <hr>
            
            <h2><?php _e('Security Activity Log', 'dev-mode'); ?></h2>
            <p class="description"><?php _e('Shows all blocked actions and state changes. Logs are automatically rotated when they exceed 2MB.', 'dev-mode'); ?></p>
            <?php $this->display_log_entries(); ?>
            
            <hr>
            
            <h3><?php _e('Log Legend', 'dev-mode'); ?></h3>
            <ul class="devmode-log-legend">
                <li><strong>STATE_CHANGE:</strong> <?php _e('Dev.Mode state was changed between Active and Protected', 'dev-mode'); ?></li>
                <li><strong>BLOCKED_DANGEROUS_FILE_UPLOAD:</strong> <?php _e('Attempt to upload PHP or other dangerous files', 'dev-mode'); ?></li>
                <li><strong>BLOCKED_FILE_MODIFICATION:</strong> <?php _e('Attempt to modify files through WordPress', 'dev-mode'); ?></li>
                <li><strong>BLOCKED_PLUGINS_API:</strong> <?php _e('Attempt to access plugin installation/update API', 'dev-mode'); ?></li>
                <li><strong>BLOCKED_USER_CREATION:</strong> <?php _e('Attempt to create new user accounts', 'dev-mode'); ?></li>
                <li><strong>BLOCKED_ADMIN_FILE_EDITING:</strong> <?php _e('Attempt to access file editors in admin', 'dev-mode'); ?></li>
            </ul>
        </div>
        <?php
    }
    
    /**
     * Display recent log entries
     */
    private function display_log_entries() {
        if (class_exists('DevMode\\Core')) {
            $core = new Core();
            $entries = $core->get_log_entries(20); // Show last 20 entries
            
            if (empty($entries)) {
                echo '<p>' . __('No activity logged yet.', 'dev-mode') . '</p>';
                return;
            }
            
            echo '<div class="devmode-log-entries">';
            foreach ($entries as $entry) {
                $entry_class = 'devmode-log-entry';
                
                // Add special styling for different log types
                if (strpos($entry, 'STATE_CHANGE') !== false) {
                    $entry_class .= ' state-change';
                } elseif (strpos($entry, 'BLOCKED_') !== false) {
                    $entry_class .= ' blocked-action';
                }
                
                echo '<div class="' . $entry_class . '">' . esc_html($entry) . '</div>';
            }
            echo '</div>';
            
            // Add refresh button
            echo '<p><button type="button" class="button" onclick="location.reload()">' . __('Refresh Log', 'dev-mode') . '</button></p>';
        }
    }
    
    /**
     * Add admin bar item
     */
    public function add_admin_bar_item($wp_admin_bar) {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        $current_state = devmode_get_state();
        $is_active = $current_state === 'active';
        
        $title = $is_active ? __('Dev Mode: Active', 'dev-mode') : __('Dev Mode: Protected', 'dev-mode');
        $class = 'devmode-admin-bar-' . $current_state;
        
        $wp_admin_bar->add_node([
            'id' => 'devmode-toggle',
            'title' => '<span>' . $title . '</span>',
            'href' => '#',
            'meta' => [
                'class' => 'devmode-admin-bar-toggle ' . $class,
                'title' => __('Click to toggle Dev.Mode state', 'dev-mode')
            ]
        ]);
    }
    
    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        // Enqueue on settings page
        if ($hook === 'settings_page_devmode-settings') {
            wp_enqueue_style(
                'devmode-admin',
                DEVMODE_PLUGIN_URL . 'assets/admin.css',
                [],
                DEVMODE_VERSION
            );
        }
        
        // Enqueue admin bar assets on all admin pages
        wp_enqueue_style(
            'devmode-admin-bar',
            DEVMODE_PLUGIN_URL . 'assets/admin.css',
            [],
            DEVMODE_VERSION
        );
        
        wp_enqueue_script(
            'devmode-admin',
            DEVMODE_PLUGIN_URL . 'assets/admin.js',
            ['jquery'],
            DEVMODE_VERSION,
            true
        );
        
        // Localize script for AJAX
        wp_localize_script('devmode-admin', 'devmode_ajax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('devmode_toggle'),
            'messages' => [
                'confirm_activate' => __('Are you sure you want to activate Dev Mode? This will allow file modifications and other changes.', 'dev-mode'),
                'confirm_protect' => __('Are you sure you want to enable Protected mode? This will block modifications and secure the site.', 'dev-mode'),
                'error' => __('An error occurred while toggling Dev Mode state.', 'dev-mode'),
                'switching' => __('Switching...', 'dev-mode')
            ]
        ]);
    }
    
    /**
     * Handle AJAX toggle request
     */
    public function handle_ajax_toggle() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'devmode_toggle')) {
            wp_die(__('Security check failed.', 'dev-mode'));
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have permission to perform this action.', 'dev-mode'));
        }
        
        // Toggle state
        $current_state = devmode_get_state();
        $new_state = $current_state === 'active' ? 'protected' : 'active';
        
        if (devmode_set_state($new_state)) {
            wp_send_json_success([
                'new_state' => $new_state,
                'message' => sprintf(
                    __('Dev.Mode state changed to %s.', 'dev-mode'),
                    $new_state === 'active' ? __('Active', 'dev-mode') : __('Protected', 'dev-mode')
                )
            ]);
        } else {
            wp_send_json_error([
                'message' => __('Failed to change Dev.Mode state.', 'dev-mode')
            ]);
        }
    }
    
    /**
     * Show admin notices
     */
    public function show_admin_notices() {
        // Show auto-revert notice
        if (get_option('devmode_auto_reverted')) {
            echo '<div class="notice notice-warning is-dismissible">';
            echo '<p>' . __('Dev.Mode has been automatically reverted to Protected state.', 'dev-mode') . '</p>';
            echo '</div>';
            delete_option('devmode_auto_reverted');
        }
        
        // Show state change notices
        if (isset($_GET['devmode_message'])) {
            $message_type = sanitize_text_field($_GET['devmode_message']);
            
            switch ($message_type) {
                case 'state_changed':
                    echo '<div class="notice notice-success is-dismissible">';
                    echo '<p>' . __('Dev.Mode state has been updated successfully.', 'dev-mode') . '</p>';
                    echo '</div>';
                    break;
                case 'settings_saved':
                    echo '<div class="notice notice-success is-dismissible">';
                    echo '<p>' . __('Settings saved successfully.', 'dev-mode') . '</p>';
                    echo '</div>';
                    break;
            }
        }
    }
}