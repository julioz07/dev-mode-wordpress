<?php

namespace DevMode;

/**
 * Hardening functionality for Dev.Mode plugin
 *
 * Handles .htaccess/web.config rules and file system protections
 */
class Hardener {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('devmode_state_changed', [$this, 'handle_state_change'], 10, 2);
        
        // Apply uploads protection if option is enabled and in protected mode
        $options = devmode_get_options();
        if ($options['block_uploads_php'] && devmode_is_protected()) {
            $this->apply_uploads_protection();
        }
    }
    
    /**
     * Safe file_get_contents wrapper with local file validation
     */
    private function safe_file_get_contents($file_path) {
        // Ensure it's a local file path and within WordPress directory structure
        $real_path = realpath($file_path);
        $wp_content_dir = realpath(WP_CONTENT_DIR);
        $abspath = realpath(ABSPATH);
        
        if ($real_path === false || 
            (strpos($real_path, $wp_content_dir) !== 0 && strpos($real_path, $abspath) !== 0)) {
            return false;
        }
        
        return file_get_contents($real_path);
    }
    
    /**
     * Handle state changes
     */
    public function handle_state_change($new_state, $old_state) {
        $options = devmode_get_options();
        
        if ($options['block_uploads_php']) {
            if ($new_state === 'protected') {
                $this->apply_uploads_protection();
            } else {
                // Optionally remove protection when switching to active mode
                // Commented out to keep protection even in active mode for safety
                // $this->remove_uploads_protection();
            }
        }
    }
    
    /**
     * Apply uploads directory protection
     */
    public function apply_uploads_protection() {
        $upload_dir = wp_upload_dir();
        $uploads_path = $upload_dir['basedir'];
        
        if (!is_dir($uploads_path)) {
            return false;
        }
        
        // Create .htaccess for Apache servers
        $this->create_htaccess_protection($uploads_path);
        
        // Create web.config for IIS servers
        $this->create_webconfig_protection($uploads_path);
        
        return true;
    }
    
    /**
     * Remove uploads directory protection
     */
    public function remove_uploads_protection() {
        $upload_dir = wp_upload_dir();
        $uploads_path = $upload_dir['basedir'];
        
        if (!is_dir($uploads_path)) {
            return false;
        }
        
        // Remove .htaccess protection
        $htaccess_file = $uploads_path . '/.htaccess';
        if (file_exists($htaccess_file)) {
            $content = $this->safe_file_get_contents($htaccess_file);
            if ($content === false) {
                return false;
            }
            
            // Remove only DevMode-specific rules
            $content = preg_replace('/# BEGIN DevMode Protection.*?# END DevMode Protection\s*/s', '', $content);
            
            if (trim($content) === '') {
                unlink($htaccess_file);
            } else {
                file_put_contents($htaccess_file, $content);
            }
        }
        
        // Remove web.config protection
        $webconfig_file = $uploads_path . '/web.config';
        if (file_exists($webconfig_file)) {
            $content = $this->safe_file_get_contents($webconfig_file);
            if ($content === false) {
                return false;
            }
            
            // Remove only DevMode-specific rules
            $content = preg_replace('/<!-- BEGIN DevMode Protection -->.*?<!-- END DevMode Protection -->\s*/s', '', $content);
            
            if (trim($content) === '' || $content === '<?xml version="1.0" encoding="UTF-8"?>') {
                unlink($webconfig_file);
            } else {
                file_put_contents($webconfig_file, $content);
            }
        }
        
        return true;
    }
    
    /**
     * Create .htaccess protection for Apache servers
     */
    private function create_htaccess_protection($uploads_path) {
        $htaccess_file = $uploads_path . '/.htaccess';
        
        // Protection rules
        $protection_rules = "# BEGIN DevMode Protection\n";
        $protection_rules .= "# Prevent execution of PHP files in uploads directory\n";
        $protection_rules .= "<IfModule mod_php7.c>\n";
        $protection_rules .= "    php_flag engine off\n";
        $protection_rules .= "</IfModule>\n";
        $protection_rules .= "<IfModule mod_php8.c>\n";
        $protection_rules .= "    php_flag engine off\n";
        $protection_rules .= "</IfModule>\n";
        $protection_rules .= "<FilesMatch \"\\.(php|php3|php4|php5|php7|php8|phtml|pht|phps)$\">\n";
        $protection_rules .= "    <IfModule mod_authz_core.c>\n";
        $protection_rules .= "        Require all denied\n";
        $protection_rules .= "    </IfModule>\n";
        $protection_rules .= "    <IfModule !mod_authz_core.c>\n";
        $protection_rules .= "        Order allow,deny\n";
        $protection_rules .= "        Deny from all\n";
        $protection_rules .= "    </IfModule>\n";
        $protection_rules .= "</FilesMatch>\n";
        $protection_rules .= "# END DevMode Protection\n\n";
        
        // Read existing content
        $existing_content = '';
        if (file_exists($htaccess_file)) {
            $existing_content = $this->safe_file_get_contents($htaccess_file);
            if ($existing_content === false) {
                $existing_content = '';
            }
            
            // Remove any existing DevMode protection rules
            $existing_content = preg_replace('/# BEGIN DevMode Protection.*?# END DevMode Protection\s*/s', '', $existing_content);
        }
        
        // Combine new rules with existing content
        $new_content = $protection_rules . $existing_content;
        
        // Write the file
        if (file_put_contents($htaccess_file, $new_content, LOCK_EX) === false) {
            error_log('DevMode: Failed to create .htaccess protection in uploads directory');
            return false;
        }
        
        return true;
    }
    
    /**
     * Create web.config protection for IIS servers
     */
    private function create_webconfig_protection($uploads_path) {
        $webconfig_file = $uploads_path . '/web.config';
        
        // Check if we're likely on an IIS server
        if (!isset($_SERVER['SERVER_SOFTWARE']) || stripos(sanitize_text_field($_SERVER['SERVER_SOFTWARE']), 'microsoft-iis') === false) {
            return false;
        }
        
        $protection_rules = <<<XML
<!-- BEGIN DevMode Protection -->
<configuration>
    <system.webServer>
        <handlers>
            <remove name="PHP_via_FastCGI" />
            <remove name="PHP74_via_FastCGI" />
            <remove name="PHP80_via_FastCGI" />
            <remove name="PHP81_via_FastCGI" />
            <remove name="PHP82_via_FastCGI" />
            <remove name="PHP83_via_FastCGI" />
            <add name="PHP_Block" path="*.php" verb="*" type="" resourceType="Unspecified" requireAccess="None" preCondition="" responseBufferLimit="4194304" />
            <add name="PHP3_Block" path="*.php3" verb="*" type="" resourceType="Unspecified" requireAccess="None" preCondition="" responseBufferLimit="4194304" />
            <add name="PHP4_Block" path="*.php4" verb="*" type="" resourceType="Unspecified" requireAccess="None" preCondition="" responseBufferLimit="4194304" />
            <add name="PHP5_Block" path="*.php5" verb="*" type="" resourceType="Unspecified" requireAccess="None" preCondition="" responseBufferLimit="4194304" />
            <add name="PHTML_Block" path="*.phtml" verb="*" type="" resourceType="Unspecified" requireAccess="None" preCondition="" responseBufferLimit="4194304" />
        </handlers>
        <security>
            <requestFiltering>
                <fileExtensions>
                    <add fileExtension=".php" allowed="false" />
                    <add fileExtension=".php3" allowed="false" />
                    <add fileExtension=".php4" allowed="false" />
                    <add fileExtension=".php5" allowed="false" />
                    <add fileExtension=".php7" allowed="false" />
                    <add fileExtension=".php8" allowed="false" />
                    <add fileExtension=".phtml" allowed="false" />
                    <add fileExtension=".pht" allowed="false" />
                    <add fileExtension=".phps" allowed="false" />
                </fileExtensions>
            </requestFiltering>
        </security>
    </system.webServer>
</configuration>
<!-- END DevMode Protection -->

XML;
        
        // Read existing content
        $existing_content = '';
        if (file_exists($webconfig_file)) {
            $existing_content = $this->safe_file_get_contents($webconfig_file);
            if ($existing_content === false) {
                $existing_content = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
            }
            
            // Remove any existing DevMode protection rules
            $existing_content = preg_replace('/<!-- BEGIN DevMode Protection -->.*?<!-- END DevMode Protection -->\s*/s', '', $existing_content);
        } else {
            // Start with basic XML declaration if file doesn't exist
            $existing_content = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        }
        
        // Combine new rules with existing content
        $new_content = $existing_content . $protection_rules;
        
        // Write the file
        if (file_put_contents($webconfig_file, $new_content, LOCK_EX) === false) {
            error_log('DevMode: Failed to create web.config protection in uploads directory');
            return false;
        }
        
        return true;
    }
    
    /**
     * Check if uploads protection is active
     */
    public function is_uploads_protected() {
        $upload_dir = wp_upload_dir();
        $uploads_path = $upload_dir['basedir'];
        
        $htaccess_file = $uploads_path . '/.htaccess';
        $webconfig_file = $uploads_path . '/web.config';
        
        $htaccess_protected = false;
        $webconfig_protected = false;
        
        // Check .htaccess
        if (file_exists($htaccess_file)) {
            $content = $this->safe_file_get_contents($htaccess_file);
            $htaccess_protected = ($content !== false) && strpos($content, '# BEGIN DevMode Protection') !== false;
        }
        
        // Check web.config
        if (file_exists($webconfig_file)) {
            $content = $this->safe_file_get_contents($webconfig_file);
            $webconfig_protected = ($content !== false) && strpos($content, '<!-- BEGIN DevMode Protection -->') !== false;
        }
        
        return $htaccess_protected || $webconfig_protected;
    }
    
    /**
     * Get protection status information
     */
    public function get_protection_status() {
        $upload_dir = wp_upload_dir();
        $uploads_path = $upload_dir['basedir'];
        
        $status = [
            'uploads_path' => $uploads_path,
            'htaccess_exists' => file_exists($uploads_path . '/.htaccess'),
            'htaccess_protected' => false,
            'webconfig_exists' => file_exists($uploads_path . '/web.config'),
            'webconfig_protected' => false,
            'writable' => is_writable($uploads_path)
        ];
        
        // Check .htaccess protection
        if ($status['htaccess_exists']) {
            $content = $this->safe_file_get_contents($uploads_path . '/.htaccess');
            $status['htaccess_protected'] = ($content !== false) && strpos($content, '# BEGIN DevMode Protection') !== false;
        }
        
        // Check web.config protection
        if ($status['webconfig_exists']) {
            $content = $this->safe_file_get_contents($uploads_path . '/web.config');
            $status['webconfig_protected'] = ($content !== false) && strpos($content, '<!-- BEGIN DevMode Protection -->') !== false;
        }
        
        return $status;
    }
    
    /**
     * Test if PHP execution is actually blocked
     */
    public function test_php_execution_block() {
        $upload_dir = wp_upload_dir();
        $uploads_path = $upload_dir['basedir'];
        $test_file = $uploads_path . '/devmode-test.php';
        
        // Create a simple test PHP file
        $test_content = '<?php echo "PHP_EXECUTION_ALLOWED"; ?>';
        
        if (file_put_contents($test_file, $test_content) === false) {
            return ['status' => 'error', 'message' => 'Cannot write test file'];
        }
        
        // Try to execute it via HTTP request
        $test_url = wp_upload_dir()['baseurl'] . '/devmode-test.php';
        $response = wp_remote_get($test_url, [
            'timeout' => 5,
            'sslverify' => false
        ]);
        
        // Clean up test file
        unlink($test_file);
        
        if (is_wp_error($response)) {
            return ['status' => 'error', 'message' => 'HTTP request failed: ' . $response->get_error_message()];
        }
        
        $body = wp_remote_retrieve_body($response);
        $code = wp_remote_retrieve_response_code($response);
        
        if ($code === 403 || $code === 404) {
            return ['status' => 'blocked', 'message' => 'PHP execution is blocked (HTTP ' . $code . ')'];
        } elseif (strpos($body, 'PHP_EXECUTION_ALLOWED') !== false) {
            return ['status' => 'allowed', 'message' => 'PHP execution is NOT blocked'];
        } else {
            return ['status' => 'unknown', 'message' => 'Unable to determine status (HTTP ' . $code . ')'];
        }
    }
}