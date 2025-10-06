/**
 * Dev.Mode Admin JavaScript
 */
(function($) {
    'use strict';
    
    $(document).ready(function() {
        initAdminBarToggle();
        initSettingsPageToggle();
        initSettingsForm();
    });
    
    /**
     * Initialize admin bar toggle functionality
     */
    function initAdminBarToggle() {
        $('#wp-admin-bar-devmode-toggle').on('click', 'a', function(e) {
            e.preventDefault();
            
            const currentState = getCurrentStateFromAdminBar();
            const newState = currentState === 'active' ? 'protected' : 'active';
            
            if (confirmStateChange(newState)) {
                toggleDevModeState();
            }
        });
    }
    
    /**
     * Initialize settings page toggle functionality
     */
    function initSettingsPageToggle() {
        $('#devmode-toggle-btn').on('click', function(e) {
            e.preventDefault();
            
            const $button = $(this);
            const currentState = getCurrentStateFromButton($button);
            const newState = currentState === 'active' ? 'protected' : 'active';
            
            if (confirmStateChange(newState)) {
                toggleDevModeState($button);
            }
        });
    }
    
    /**
     * Initialize settings form functionality
     */
    function initSettingsForm() {
        // Auto-save indication
        $('form').on('submit', function() {
            const $submitButton = $(this).find('input[type="submit"]');
            $submitButton.val(devmode_ajax.messages.switching || 'Saving...');
        });
        
        // Form validation
        $('input[name="devmode_options[auto_revert_hours]"]').on('change', function() {
            const value = parseInt($(this).val());
            if (value < 0 || value > 168) {
                alert('Auto-revert hours must be between 0 and 168 (1 week).');
                $(this).val(0);
            }
        });
    }
    
    /**
     * Get current state from admin bar
     */
    function getCurrentStateFromAdminBar() {
        const $adminBarItem = $('#wp-admin-bar-devmode-toggle .ab-item span');
        
        if ($adminBarItem.hasClass('devmode-admin-bar-active')) {
            return 'active';
        } else if ($adminBarItem.hasClass('devmode-admin-bar-protected')) {
            return 'protected';
        }
        
        // Fallback: parse text content
        const text = $adminBarItem.text();
        return text.includes('Active') ? 'active' : 'protected';
    }
    
    /**
     * Get current state from settings page button
     */
    function getCurrentStateFromButton($button) {
        if ($button.hasClass('devmode-btn-active')) {
            return 'active';
        } else if ($button.hasClass('devmode-btn-protected')) {
            return 'protected';
        }
        
        // Fallback: parse text content
        const text = $button.text();
        return text.includes('Active') ? 'active' : 'protected';
    }
    
    /**
     * Confirm state change with user
     */
    function confirmStateChange(newState) {
        const message = newState === 'active' 
            ? devmode_ajax.messages.confirm_activate 
            : devmode_ajax.messages.confirm_protect;
            
        return confirm(message);
    }
    
    /**
     * Toggle Dev.Mode state via AJAX
     */
    function toggleDevModeState($button) {
        // Get nonce from different sources based on context
        let nonce;
        if ($button && $button.data('nonce')) {
            // From settings page button
            nonce = $button.data('nonce');
        } else if (devmode_ajax.nonce) {
            // From localized script (admin bar)
            nonce = devmode_ajax.nonce;
        } else {
            // Fallback: refresh page to get updated state
            console.log('No nonce available, refreshing page');
            window.location.reload();
            return;
        }
        
        // Update UI to show loading state
        updateUIForLoading($button);
        
        $.ajax({
            url: devmode_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'devmode_toggle',
                nonce: nonce
            },
            success: function(response) {
                if (response.success) {
                    handleToggleSuccess(response.data, $button);
                } else {
                    handleToggleError(response.data, $button);
                }
            },
            error: function(xhr, status, error) {
                console.log('AJAX Error:', xhr, status, error);
                handleToggleError({
                    message: devmode_ajax.messages.error + ' ' + error
                }, $button);
            }
        });
    }
    
    /**
     * Update UI for loading state
     */
    function updateUIForLoading($button) {
        // Update admin bar
        const $adminBarItem = $('#wp-admin-bar-devmode-toggle .ab-item span');
        $adminBarItem.text(devmode_ajax.messages.switching);
        
        // Update settings page button
        if ($button) {
            $button.addClass('devmode-btn-switching');
            $button.text(devmode_ajax.messages.switching);
            $button.prop('disabled', true);
        }
    }
    
    /**
     * Handle successful toggle
     */
    function handleToggleSuccess(data, $button) {
        const newState = data.new_state;
        const message = data.message;
        
        // Update admin bar
        updateAdminBar(newState);
        
        // Update settings page button
        if ($button) {
            updateSettingsButton($button, newState);
        }
        
        // Show success message
        showNotice(message, 'success');
        
        // Refresh page after a short delay to show updated state info
        setTimeout(function() {
            if (window.location.pathname.includes('devmode-settings')) {
                window.location.reload();
            }
        }, 1500);
    }
    
    /**
     * Handle toggle error
     */
    function handleToggleError(data, $button) {
        const message = data.message || devmode_ajax.messages.error;
        
        // Reset UI
        resetUIFromLoading($button);
        
        // Show error message
        showNotice(message, 'error');
        
        // For admin bar, refresh page if error persists
        if (!$button) {
            setTimeout(function() {
                console.log('Admin bar toggle error, refreshing page');
                window.location.reload();
            }, 2000);
        }
    }
    
    /**
     * Reset UI from loading state
     */
    function resetUIFromLoading($button) {
        // Get current state to restore proper display
        const currentState = getCurrentStateFromButton($button || $('#devmode-toggle-btn'));
        
        // Reset admin bar
        updateAdminBar(currentState);
        
        // Reset settings page button
        if ($button) {
            updateSettingsButton($button, currentState);
        }
    }
    
    /**
     * Update admin bar display
     */
    function updateAdminBar(state) {
        const $adminBarItem = $('#wp-admin-bar-devmode-toggle .ab-item span');
        const isActive = state === 'active';
        
        $adminBarItem
            .removeClass('devmode-admin-bar-active devmode-admin-bar-protected')
            .addClass(isActive ? 'devmode-admin-bar-active' : 'devmode-admin-bar-protected')
            .text(isActive ? 'Dev Mode: Active' : 'Dev Mode: Protected');
    }
    
    /**
     * Update settings page button
     */
    function updateSettingsButton($button, state) {
        const isActive = state === 'active';
        
        $button
            .removeClass('devmode-btn-active devmode-btn-protected devmode-btn-switching')
            .addClass(isActive ? 'devmode-btn-active' : 'devmode-btn-protected')
            .text(isActive ? 'Dev Mode: Active' : 'Dev Mode: Protected')
            .prop('disabled', false);
    }
    
    /**
     * Show admin notice
     */
    function showNotice(message, type) {
        type = type || 'info';
        
        const noticeClass = 'notice notice-' + type + ' is-dismissible devmode-notice';
        const $notice = $('<div class="' + noticeClass + '"><p>' + message + '</p></div>');
        
        // Insert notice after page title
        if ($('.wrap h1').length) {
            $('.wrap h1').after($notice);
        } else {
            $('.wrap').prepend($notice);
        }
        
        // Auto-dismiss after 5 seconds
        setTimeout(function() {
            $notice.fadeOut(function() {
                $(this).remove();
            });
        }, 5000);
        
        // Make notice dismissible
        $notice.on('click', '.notice-dismiss', function() {
            $notice.fadeOut(function() {
                $(this).remove();
            });
        });
        
        // Add dismiss button if not present
        if (!$notice.find('.notice-dismiss').length) {
            $notice.append('<button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>');
        }
    }
    
    /**
     * Keyboard accessibility
     */
    $(document).on('keydown', '#devmode-toggle-btn', function(e) {
        if (e.which === 13 || e.which === 32) { // Enter or Space
            e.preventDefault();
            $(this).click();
        }
    });
    
    /**
     * Auto-refresh state info every 30 seconds on settings page
     */
    if (window.location.pathname.includes('devmode-settings')) {
        setInterval(function() {
            // Check if auto-revert time has changed
            const $stateInfo = $('.devmode-state-info');
            if ($stateInfo.length && $stateInfo.text().includes('will auto-revert')) {
                // Refresh the page to update countdown
                window.location.reload();
            }
        }, 30000);
    }
    
})(jQuery);