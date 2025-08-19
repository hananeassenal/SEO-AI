<?php
/**
 * Settings Page Template
 * 
 * @package AI_SEO_Optimizer
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get current settings
$api_key = get_option('ai_seo_api_key', '');
$customer_id = get_option('ai_seo_customer_id', '');
$api_url = get_option('ai_seo_api_url', home_url('/wp-json/ai-seo/v1'));
$auto_apply = get_option('ai_seo_auto_apply', false);
$scan_frequency = get_option('ai_seo_scan_frequency', 'daily');

// Get current data
global $ai_seo_optimizer;

// Check if plugin is properly initialized
if (!isset($ai_seo_optimizer) || !$ai_seo_optimizer) {
    $ai_seo_optimizer = AI_SEO_Optimizer::get_instance();
}

// Get API status with error handling
try {
    $api_status = $ai_seo_optimizer->api_handler->get_api_status();
} catch (Exception $e) {
    $api_status = array(
        'connected' => false,
        'api_url' => '',
        'has_api_key' => false,
        'has_customer_id' => false,
        'last_check' => current_time('mysql')
    );
}
?>

<div class="wrap">
    <h1><?php _e('AI SEO Optimizer Settings', 'ai-seo-optimizer'); ?></h1>
    
    <div class="ai-seo-settings-container">
        <form id="ai-seo-settings-form" method="post">
            <?php wp_nonce_field('ai_seo_settings_nonce', 'ai_seo_nonce'); ?>
            
            <div class="ai-seo-card">
                <h2><?php _e('API Configuration', 'ai-seo-optimizer'); ?></h2>
                
                <div class="ai-seo-form-row">
                    <label for="ai_seo_api_key"><?php _e('API Key', 'ai-seo-optimizer'); ?></label>
                    <input type="password" id="ai_seo_api_key" name="ai_seo_api_key" value="<?php echo esc_attr($api_key); ?>" class="regular-text" />
                    <p class="description"><?php _e('Enter your AI SEO service API key', 'ai-seo-optimizer'); ?></p>
                </div>
                
                <div class="ai-seo-form-row">
                    <label for="ai_seo_customer_id"><?php _e('Customer ID', 'ai-seo-optimizer'); ?></label>
                    <input type="text" id="ai_seo_customer_id" name="ai_seo_customer_id" value="<?php echo esc_attr($customer_id); ?>" class="regular-text" />
                    <p class="description"><?php _e('Enter your customer ID from the AI SEO service', 'ai-seo-optimizer'); ?></p>
                </div>
                
                <div class="ai-seo-form-row">
                    <label for="ai_seo_api_url"><?php _e('API URL', 'ai-seo-optimizer'); ?></label>
                    <input type="url" id="ai_seo_api_url" name="ai_seo_api_url" value="<?php echo esc_attr($api_url); ?>" class="regular-text" />
                    <p class="description"><?php _e('The base URL for the AI SEO API', 'ai-seo-optimizer'); ?></p>
                </div>
                
                <div class="ai-seo-form-row">
                    <button type="button" id="test-connection" class="ai-seo-btn ai-seo-btn-secondary">
                        <?php _e('Test Connection', 'ai-seo-optimizer'); ?>
                    </button>
                    <span id="connection-status" class="ai-seo-status-indicator">
                        <?php if ($api_status['connected']): ?>
                            <span class="ai-seo-status-connected"><?php _e('Connected', 'ai-seo-optimizer'); ?></span>
                        <?php else: ?>
                            <span class="ai-seo-status-disconnected"><?php _e('Disconnected', 'ai-seo-optimizer'); ?></span>
                        <?php endif; ?>
                    </span>
                </div>
            </div>
            
            <div class="ai-seo-card">
                <h2><?php _e('Automation Settings', 'ai-seo-optimizer'); ?></h2>
                
                <div class="ai-seo-form-row">
                    <label for="ai_seo_auto_apply">
                        <input type="checkbox" id="ai_seo_auto_apply" name="ai_seo_auto_apply" value="1" <?php checked($auto_apply); ?> />
                        <?php _e('Automatically apply approved changes', 'ai-seo-optimizer'); ?>
                    </label>
                    <p class="description"><?php _e('When enabled, approved SEO changes will be applied automatically without manual review', 'ai-seo-optimizer'); ?></p>
                </div>
                
                <div class="ai-seo-form-row">
                    <label for="ai_seo_scan_frequency"><?php _e('Scan Frequency', 'ai-seo-optimizer'); ?></label>
                    <select id="ai_seo_scan_frequency" name="ai_seo_scan_frequency">
                        <option value="hourly" <?php selected($scan_frequency, 'hourly'); ?>><?php _e('Hourly', 'ai-seo-optimizer'); ?></option>
                        <option value="daily" <?php selected($scan_frequency, 'daily'); ?>><?php _e('Daily', 'ai-seo-optimizer'); ?></option>
                        <option value="weekly" <?php selected($scan_frequency, 'weekly'); ?>><?php _e('Weekly', 'ai-seo-optimizer'); ?></option>
                        <option value="monthly" <?php selected($scan_frequency, 'monthly'); ?>><?php _e('Monthly', 'ai-seo-optimizer'); ?></option>
                    </select>
                    <p class="description"><?php _e('How often to automatically scan your website for SEO improvements', 'ai-seo-optimizer'); ?></p>
                </div>
            </div>
            
            <div class="ai-seo-card">
                <h2><?php _e('Content Settings', 'ai-seo-optimizer'); ?></h2>
                
                <div class="ai-seo-form-row">
                    <label for="ai_seo_post_types"><?php _e('Post Types to Optimize', 'ai-seo-optimizer'); ?></label>
                    <?php
                    $post_types = get_post_types(['public' => true], 'objects');
                    $selected_types = get_option('ai_seo_post_types', ['post', 'page']);
                    
                    foreach ($post_types as $post_type) {
                        $checked = in_array($post_type->name, $selected_types) ? 'checked' : '';
                        echo '<label style="display: block; margin: 5px 0;">';
                        echo '<input type="checkbox" name="ai_seo_post_types[]" value="' . esc_attr($post_type->name) . '" ' . $checked . ' />';
                        echo ' ' . esc_html($post_type->label);
                        echo '</label>';
                    }
                    ?>
                    <p class="description"><?php _e('Select which post types should be included in SEO optimization', 'ai-seo-optimizer'); ?></p>
                </div>
                
                <div class="ai-seo-form-row">
                    <label for="ai_seo_max_posts"><?php _e('Maximum Posts per Scan', 'ai-seo-optimizer'); ?></label>
                    <input type="number" id="ai_seo_max_posts" name="ai_seo_max_posts" value="<?php echo esc_attr(get_option('ai_seo_max_posts', 50)); ?>" min="1" max="500" />
                    <p class="description"><?php _e('Limit the number of posts analyzed in each scan to prevent API rate limits', 'ai-seo-optimizer'); ?></p>
                </div>
            </div>
            
            <div class="ai-seo-form-actions">
                <button type="submit" class="ai-seo-btn ai-seo-btn-primary">
                    <?php _e('Save Settings', 'ai-seo-optimizer'); ?>
                </button>
                <button type="button" id="reset-settings" class="ai-seo-btn ai-seo-btn-secondary">
                    <?php _e('Reset to Defaults', 'ai-seo-optimizer'); ?>
                </button>
            </div>
        </form>
    </div>
    
    <div class="ai-seo-card">
        <h2><?php _e('System Information', 'ai-seo-optimizer'); ?></h2>
        <table class="widefat">
            <tr>
                <td><strong><?php _e('Plugin Version', 'ai-seo-optimizer'); ?></strong></td>
                <td><?php echo AI_SEO_OPTIMIZER_VERSION; ?></td>
            </tr>
            <tr>
                <td><strong><?php _e('WordPress Version', 'ai-seo-optimizer'); ?></strong></td>
                <td><?php echo get_bloginfo('version'); ?></td>
            </tr>
            <tr>
                <td><strong><?php _e('PHP Version', 'ai-seo-optimizer'); ?></strong></td>
                <td><?php echo PHP_VERSION; ?></td>
            </tr>
            <tr>
                <td><strong><?php _e('Database Table', 'ai-seo-optimizer'); ?></strong></td>
                <td><?php global $wpdb; echo $wpdb->prefix . 'ai_seo_audit_log'; ?></td>
            </tr>
            <tr>
                <td><strong><?php _e('Cron Status', 'ai-seo-optimizer'); ?></strong></td>
                <td>
                    <?php
                    $next_scan = wp_next_scheduled('ai_seo_daily_scan');
                    if ($next_scan) {
                        echo '<span class="ai-seo-status-connected">' . __('Scheduled', 'ai-seo-optimizer') . '</span> - ' . date('Y-m-d H:i:s', $next_scan);
                    } else {
                        echo '<span class="ai-seo-status-disconnected">' . __('Not Scheduled', 'ai-seo-optimizer') . '</span>';
                    }
                    ?>
                </td>
            </tr>
        </table>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Test connection
    $('#test-connection').on('click', function() {
        var button = $(this);
        var originalText = button.text();
        
        button.prop('disabled', true).text('<?php _e('Testing...', 'ai-seo-optimizer'); ?>');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'ai_seo_test_connection',
                nonce: '<?php echo wp_create_nonce('ai_seo_test_connection'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    $('#connection-status').html('<span class="ai-seo-status-connected"><?php _e('Connected', 'ai-seo-optimizer'); ?></span>');
                    showNotice('<?php _e('Connection successful!', 'ai-seo-optimizer'); ?>', 'success');
                } else {
                    $('#connection-status').html('<span class="ai-seo-status-disconnected"><?php _e('Disconnected', 'ai-seo-optimizer'); ?></span>');
                    showNotice(response.data || '<?php _e('Connection failed', 'ai-seo-optimizer'); ?>', 'error');
                }
            },
            error: function() {
                $('#connection-status').html('<span class="ai-seo-status-disconnected"><?php _e('Disconnected', 'ai-seo-optimizer'); ?></span>');
                showNotice('<?php _e('Connection test failed', 'ai-seo-optimizer'); ?>', 'error');
            },
            complete: function() {
                button.prop('disabled', false).text(originalText);
            }
        });
    });
    
    // Save settings
    $('#ai-seo-settings-form').on('submit', function(e) {
        e.preventDefault();
        
        var form = $(this);
        var submitButton = form.find('button[type="submit"]');
        var originalText = submitButton.text();
        
        submitButton.prop('disabled', true).text('<?php _e('Saving...', 'ai-seo-optimizer'); ?>');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'ai_seo_save_settings',
                nonce: '<?php echo wp_create_nonce('ai_seo_save_settings'); ?>',
                formData: form.serialize()
            },
            success: function(response) {
                if (response.success) {
                    showNotice('<?php _e('Settings saved successfully!', 'ai-seo-optimizer'); ?>', 'success');
                } else {
                    showNotice(response.data || '<?php _e('Failed to save settings', 'ai-seo-optimizer'); ?>', 'error');
                }
            },
            error: function() {
                showNotice('<?php _e('Failed to save settings', 'ai-seo-optimizer'); ?>', 'error');
            },
            complete: function() {
                submitButton.prop('disabled', false).text(originalText);
            }
        });
    });
    
    // Reset settings
    $('#reset-settings').on('click', function() {
        if (confirm('<?php _e('Are you sure you want to reset all settings to defaults?', 'ai-seo-optimizer'); ?>')) {
            var button = $(this);
            var originalText = button.text();
            
            button.prop('disabled', true).text('<?php _e('Resetting...', 'ai-seo-optimizer'); ?>');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'ai_seo_reset_settings',
                    nonce: '<?php echo wp_create_nonce('ai_seo_reset_settings'); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        showNotice('<?php _e('Settings reset successfully!', 'ai-seo-optimizer'); ?>', 'success');
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    } else {
                        showNotice(response.data || '<?php _e('Failed to reset settings', 'ai-seo-optimizer'); ?>', 'error');
                    }
                },
                error: function() {
                    showNotice('<?php _e('Failed to reset settings', 'ai-seo-optimizer'); ?>', 'error');
                },
                complete: function() {
                    button.prop('disabled', false).text(originalText);
                }
            });
        }
    });
    
    function showNotice(message, type) {
        var noticeClass = type === 'success' ? 'notice-success' : 'notice-error';
        var notice = $('<div class="notice ' + noticeClass + ' is-dismissible"><p>' + message + '</p></div>');
        $('.wrap h1').after(notice);
        
        // Auto-dismiss after 5 seconds
        setTimeout(function() {
            notice.fadeOut();
        }, 5000);
    }
});
</script>
