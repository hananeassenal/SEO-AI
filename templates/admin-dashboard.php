<?php
/**
 * Admin Dashboard Template
 * 
 * Main dashboard page for AI SEO Optimizer
 * 
 * @package AI_SEO_Optimizer
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

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

$last_scan = get_option('ai_seo_last_scan', '');

// Get recent changes with error handling
try {
    $recent_changes = $ai_seo_optimizer->audit_logger->get_recent_changes(5);
} catch (Exception $e) {
    $recent_changes = array();
}

// Get statistics with error handling
try {
    $stats = $ai_seo_optimizer->audit_logger->get_statistics(30);
} catch (Exception $e) {
    $stats = array(
        'total_changes' => 0,
        'successful_changes' => 0,
        'failed_changes' => 0
    );
}
?>

<div class="wrap">
    <h1><?php _e('AI SEO Optimizer Dashboard', 'ai-seo-optimizer'); ?></h1>
    
    <div class="ai-seo-dashboard" id="ai-seo-dashboard">
        
        <!-- Dashboard Grid -->
        <div class="ai-seo-dashboard-grid">
            
            <!-- SEO Score Card -->
            <div class="ai-seo-card ai-seo-score-card">
                <h3><?php _e('SEO Score', 'ai-seo-optimizer'); ?></h3>
                <div class="ai-seo-score-circle" data-score="75">
                    75/100
                </div>
                <div class="ai-seo-score-label">
                    <?php _e('Overall SEO Performance', 'ai-seo-optimizer'); ?>
                </div>
            </div>
            
            <!-- Connection Status Card -->
            <div class="ai-seo-card">
                <h3><?php _e('API Connection', 'ai-seo-optimizer'); ?></h3>
                <div class="ai-seo-connection-status <?php echo $api_status['connected'] ? 'connected' : 'disconnected'; ?>">
                    <?php echo $api_status['connected'] ? __('Connected', 'ai-seo-optimizer') : __('Disconnected', 'ai-seo-optimizer'); ?>
                </div>
                <p class="description">
                    <?php if ($api_status['connected']): ?>
                        <?php _e('Successfully connected to AI SEO API', 'ai-seo-optimizer'); ?>
                    <?php else: ?>
                        <?php _e('Unable to connect to AI SEO API. Check your settings.', 'ai-seo-optimizer'); ?>
                    <?php endif; ?>
                </p>
            </div>
            
            <!-- Last Scan Card -->
            <div class="ai-seo-card">
                <h3><?php _e('Last Scan', 'ai-seo-optimizer'); ?></h3>
                <div class="ai-seo-last-scan">
                    <?php if ($last_scan): ?>
                        <?php echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($last_scan)); ?>
                    <?php else: ?>
                        <?php _e('No scans performed yet', 'ai-seo-optimizer'); ?>
                    <?php endif; ?>
                </div>
                <p class="description">
                    <?php _e('Last time your website was scanned for SEO improvements', 'ai-seo-optimizer'); ?>
                </p>
            </div>
            
        </div>
        
        <!-- Action Buttons -->
        <div class="ai-seo-actions">
            <button type="button" class="ai-seo-btn ai-seo-scan-btn">
                <span class="dashicons dashicons-search"></span>
                <?php _e('Scan Website', 'ai-seo-optimizer'); ?>
            </button>
            
            <a href="<?php echo admin_url('admin.php?page=ai-seo-settings'); ?>" class="ai-seo-btn secondary">
                <span class="dashicons dashicons-admin-settings"></span>
                <?php _e('Settings', 'ai-seo-optimizer'); ?>
            </a>
            
            <button type="button" class="ai-seo-btn secondary ai-seo-refresh-dashboard">
                <span class="dashicons dashicons-update"></span>
                <?php _e('Refresh', 'ai-seo-optimizer'); ?>
            </button>
        </div>
        
        <!-- Statistics -->
        <div class="ai-seo-stats">
            <div class="ai-seo-stat-item">
                <div class="ai-seo-stat-number"><?php echo $stats['total_changes']; ?></div>
                <div class="ai-seo-stat-label"><?php _e('Total Changes', 'ai-seo-optimizer'); ?></div>
            </div>
            
            <div class="ai-seo-stat-item">
                <div class="ai-seo-stat-number"><?php echo $stats['successful_changes']; ?></div>
                <div class="ai-seo-stat-label"><?php _e('Successful', 'ai-seo-optimizer'); ?></div>
            </div>
            
            <div class="ai-seo-stat-item">
                <div class="ai-seo-stat-number"><?php echo $stats['failed_changes']; ?></div>
                <div class="ai-seo-stat-label"><?php _e('Failed', 'ai-seo-optimizer'); ?></div>
            </div>
            
            <div class="ai-seo-stat-item">
                <div class="ai-seo-stat-number"><?php echo count($recent_changes); ?></div>
                <div class="ai-seo-stat-label"><?php _e('Recent Changes', 'ai-seo-optimizer'); ?></div>
            </div>
        </div>
        
        <!-- Charts Section -->
        <div class="ai-seo-charts">
            <div class="ai-seo-chart-container">
                <h3><?php _e('SEO Score Trend', 'ai-seo-optimizer'); ?></h3>
                <canvas id="ai-seo-score-chart"></canvas>
            </div>
            
            <div class="ai-seo-chart-container">
                <h3><?php _e('Changes Applied', 'ai-seo-optimizer'); ?></h3>
                <canvas id="ai-seo-changes-chart"></canvas>
            </div>
        </div>
        
        <!-- SEO Recommendations -->
        <div class="ai-seo-card" id="ai-seo-recommendations">
            <h3><?php _e('SEO Recommendations', 'ai-seo-optimizer'); ?></h3>
            <div class="ai-seo-recommendations-content">
                <div class="ai-seo-loading-recommendations" style="display: none;">
                    <p><?php _e('Analyzing your website...', 'ai-seo-optimizer'); ?></p>
                </div>
                <div class="ai-seo-no-recommendations">
                    <p><?php _e('No recommendations yet. Click "Scan Website" to analyze your site.', 'ai-seo-optimizer'); ?></p>
                </div>
                <div class="ai-seo-recommendations-list" style="display: none;">
                    <!-- Recommendations will be loaded here -->
                </div>
                <div class="ai-seo-apply-all" style="display: none; margin-top: 15px;">
                    <button type="button" class="ai-seo-btn ai-seo-apply-all-btn">
                        <?php _e('Apply All Recommendations', 'ai-seo-optimizer'); ?>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Recent Changes -->
        <div class="ai-seo-card">
            <h3><?php _e('Recent Changes', 'ai-seo-optimizer'); ?></h3>
            <div class="ai-seo-recent-changes">
                <?php if (empty($recent_changes)): ?>
                    <p class="no-changes"><?php _e('No recent changes found', 'ai-seo-optimizer'); ?></p>
                <?php else: ?>
                    <?php foreach ($recent_changes as $change): ?>
                        <div class="change-item">
                            <div class="change-header">
                                <span class="change-type"><?php echo esc_html($change['change_type']); ?></span>
                                <span class="change-status <?php echo esc_attr($change['status']); ?>">
                                    <?php echo esc_html($change['status']); ?>
                                </span>
                            </div>
                            <div class="change-content">
                                <strong><?php echo esc_html($change['post_title']); ?></strong>
                                <div class="change-time"><?php echo esc_html($change['applied_at_formatted']); ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <div style="margin-top: 15px;">
                <a href="<?php echo admin_url('admin.php?page=ai-seo-logs'); ?>" class="ai-seo-btn secondary">
                    <?php _e('View All Changes', 'ai-seo-optimizer'); ?>
                </a>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="ai-seo-card">
            <h3><?php _e('Quick Actions', 'ai-seo-optimizer'); ?></h3>
            <div class="ai-seo-actions">
                <a href="<?php echo admin_url('edit.php'); ?>" class="ai-seo-btn secondary">
                    <span class="dashicons dashicons-admin-post"></span>
                    <?php _e('Manage Posts', 'ai-seo-optimizer'); ?>
                </a>
                
                <a href="<?php echo admin_url('edit.php?post_type=page'); ?>" class="ai-seo-btn secondary">
                    <span class="dashicons dashicons-admin-page"></span>
                    <?php _e('Manage Pages', 'ai-seo-optimizer'); ?>
                </a>
                
                <a href="<?php echo admin_url('upload.php'); ?>" class="ai-seo-btn secondary">
                    <span class="dashicons dashicons-admin-media"></span>
                    <?php _e('Media Library', 'ai-seo-optimizer'); ?>
                </a>
                
                <a href="<?php echo admin_url('admin.php?page=ai-seo-settings'); ?>" class="ai-seo-btn secondary">
                    <span class="dashicons dashicons-admin-tools"></span>
                    <?php _e('Plugin Settings', 'ai-seo-optimizer'); ?>
                </a>
            </div>
        </div>
        
        <!-- System Information -->
        <div class="ai-seo-card">
            <h3><?php _e('System Information', 'ai-seo-optimizer'); ?></h3>
            <table class="widefat" style="margin-top: 10px;">
                <tbody>
                    <tr>
                        <td><strong><?php _e('WordPress Version:', 'ai-seo-optimizer'); ?></strong></td>
                        <td><?php echo get_bloginfo('version'); ?></td>
                    </tr>
                    <tr>
                        <td><strong><?php _e('Plugin Version:', 'ai-seo-optimizer'); ?></strong></td>
                        <td><?php echo AI_SEO_OPTIMIZER_VERSION; ?></td>
                    </tr>
                    <tr>
                        <td><strong><?php _e('PHP Version:', 'ai-seo-optimizer'); ?></strong></td>
                        <td><?php echo PHP_VERSION; ?></td>
                    </tr>
                    <tr>
                        <td><strong><?php _e('Site URL:', 'ai-seo-optimizer'); ?></strong></td>
                        <td><?php echo get_site_url(); ?></td>
                    </tr>
                    <tr>
                        <td><strong><?php _e('API URL:', 'ai-seo-optimizer'); ?></strong></td>
                        <td><?php echo esc_html($api_status['api_url']); ?></td>
                    </tr>
                    <tr>
                        <td><strong><?php _e('SEO Plugins:', 'ai-seo-optimizer'); ?></strong></td>
                        <td>
                            <?php
                            $seo_plugins = array();
                            if (is_plugin_active('wordpress-seo/wp-seo.php')) {
                                $seo_plugins[] = 'Yoast SEO';
                            }
                            if (is_plugin_active('seo-by-rank-math/rank-math.php')) {
                                $seo_plugins[] = 'RankMath';
                            }
                            if (is_plugin_active('all-in-one-seo-pack/all_in_one_seo_pack.php')) {
                                $seo_plugins[] = 'All in One SEO';
                            }
                            echo empty($seo_plugins) ? __('None detected', 'ai-seo-optimizer') : implode(', ', $seo_plugins);
                            ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
    </div>
</div>

<script>
// Initialize dashboard when page loads
jQuery(document).ready(function($) {
    // Update SEO score circle based on data-score attribute
    function updateScoreCircle() {
        $('.ai-seo-score-circle').each(function() {
            var score = $(this).attr('data-score') || 0;
            var percentage = (score / 100) * 360;
            $(this).css('background', 'conic-gradient(#46b450 0deg, #46b450 ' + percentage + 'deg, #f1f1f1 ' + percentage + 'deg, #f1f1f1 360deg)');
        });
    }
    
    updateScoreCircle();
    
    // Auto-refresh connection status
    setInterval(function() {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'ai_seo_get_dashboard_data',
                nonce: aiSeoAjax.nonce
            },
            success: function(response) {
                if (response.success) {
                    // Update connection status
                    var $status = $('.ai-seo-connection-status');
                    if (response.data.connection_status) {
                        $status.removeClass('disconnected').addClass('connected').text('<?php _e('Connected', 'ai-seo-optimizer'); ?>');
                    } else {
                        $status.removeClass('connected').addClass('disconnected').text('<?php _e('Disconnected', 'ai-seo-optimizer'); ?>');
                    }
                    
                    // Update last scan
                    if (response.data.last_scan) {
                        $('.ai-seo-last-scan').text(response.data.last_scan);
                    }
                }
            }
        });
    }, 60000); // Check every minute
});
</script>
