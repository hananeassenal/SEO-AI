<?php
/**
 * Shopify Settings Template
 * 
 * @package AI_SEO_Optimizer
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1><?php _e('Shopify Integration Settings', 'ai-seo-optimizer'); ?></h1>
    
    <div class="ai-seo-settings-container">
        <div class="ai-seo-settings-section">
            <h2><?php _e('Shopify Store Connection', 'ai-seo-optimizer'); ?></h2>
            <p><?php _e('Connect your Shopify store to enable AI-powered SEO optimization for products, pages, and collections.', 'ai-seo-optimizer'); ?></p>
            
            <form id="shopify-connection-form" class="ai-seo-form">
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="shopify_store_url"><?php _e('Store URL', 'ai-seo-optimizer'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="shopify_store_url" name="store_url" class="regular-text" 
                                   value="<?php echo esc_attr(get_option('ai_seo_shopify_store_url', '')); ?>" 
                                   placeholder="your-store.myshopify.com" required>
                            <p class="description"><?php _e('Your Shopify store URL (without https://)', 'ai-seo-optimizer'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="shopify_access_token"><?php _e('Access Token', 'ai-seo-optimizer'); ?></label>
                        </th>
                        <td>
                            <input type="password" id="shopify_access_token" name="access_token" class="regular-text" 
                                   value="<?php echo esc_attr(get_option('ai_seo_shopify_access_token', '')); ?>" required>
                            <p class="description">
                                <?php _e('Private app access token. Create a private app in your Shopify admin to get this token.', 'ai-seo-optimizer'); ?>
                                <br>
                                <a href="https://help.shopify.com/en/manual/apps/private-apps" target="_blank">
                                    <?php _e('How to create a private app', 'ai-seo-optimizer'); ?>
                                </a>
                            </p>
                        </td>
                    </tr>
                </table>
                
                <p class="submit">
                    <button type="submit" class="button button-primary" id="shopify-connect-btn">
                        <span class="button-text"><?php _e('Connect Store', 'ai-seo-optimizer'); ?></span>
                        <span class="spinner" style="display: none;"></span>
                    </button>
                    <button type="button" class="button" id="shopify-test-btn">
                        <?php _e('Test Connection', 'ai-seo-optimizer'); ?>
                    </button>
                </p>
            </form>
        </div>
        
        <div class="ai-seo-settings-section" id="shopify-connection-status" style="display: none;">
            <h2><?php _e('Connection Status', 'ai-seo-optimizer'); ?></h2>
            <div id="shopify-status-content"></div>
        </div>
        
        <div class="ai-seo-settings-section">
            <h2><?php _e('Shopify SEO Features', 'ai-seo-optimizer'); ?></h2>
            <div class="feature-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-box"></i>
                    </div>
                    <h3><?php _e('Product SEO', 'ai-seo-optimizer'); ?></h3>
                    <p><?php _e('Optimize product titles, descriptions, and meta tags for better search rankings.', 'ai-seo-optimizer'); ?></p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <h3><?php _e('Page SEO', 'ai-seo-optimizer'); ?></h3>
                    <p><?php _e('Improve page SEO titles and descriptions for better visibility in search results.', 'ai-seo-optimizer'); ?></p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-tags"></i>
                    </div>
                    <h3><?php _e('Collection SEO', 'ai-seo-optimizer'); ?></h3>
                    <p><?php _e('Optimize collection pages for category-based search terms and better organization.', 'ai-seo-optimizer'); ?></p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-image"></i>
                    </div>
                    <h3><?php _e('Image Optimization', 'ai-seo-optimizer'); ?></h3>
                    <p><?php _e('Analyze and optimize product images with proper alt text and compression.', 'ai-seo-optimizer'); ?></p>
                </div>
            </div>
        </div>
        
        <div class="ai-seo-settings-section">
            <h2><?php _e('Quick Actions', 'ai-seo-optimizer'); ?></h2>
            <div class="quick-actions">
                <button type="button" class="button button-secondary" id="shopify-analyze-btn">
                    <i class="fas fa-search"></i>
                    <?php _e('Analyze Store SEO', 'ai-seo-optimizer'); ?>
                </button>
                
                <button type="button" class="button button-secondary" id="shopify-export-btn">
                    <i class="fas fa-download"></i>
                    <?php _e('Export SEO Report', 'ai-seo-optimizer'); ?>
                </button>
                
                <button type="button" class="button button-secondary" id="shopify-sync-btn">
                    <i class="fas fa-sync"></i>
                    <?php _e('Sync Store Data', 'ai-seo-optimizer'); ?>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Shopify connection form
    $('#shopify-connection-form').on('submit', function(e) {
        e.preventDefault();
        
        var $form = $(this);
        var $btn = $('#shopify-connect-btn');
        var $spinner = $btn.find('.spinner');
        var $text = $btn.find('.button-text');
        
        $btn.prop('disabled', true);
        $spinner.show();
        $text.text('<?php _e('Connecting...', 'ai-seo-optimizer'); ?>');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'ai_seo_shopify_connect',
                nonce: ai_seo_ajax.nonce,
                store_url: $('#shopify_store_url').val(),
                access_token: $('#shopify_access_token').val()
            },
            success: function(response) {
                if (response.success) {
                    showShopifyStatus('success', response.data.message, response.data.store_info);
                } else {
                    showShopifyStatus('error', response.data);
                }
            },
            error: function() {
                showShopifyStatus('error', '<?php _e('Connection failed. Please try again.', 'ai-seo-optimizer'); ?>');
            },
            complete: function() {
                $btn.prop('disabled', false);
                $spinner.hide();
                $text.text('<?php _e('Connect Store', 'ai-seo-optimizer'); ?>');
            }
        });
    });
    
    // Test connection
    $('#shopify-test-btn').on('click', function() {
        var $btn = $(this);
        var originalText = $btn.text();
        
        $btn.prop('disabled', true).text('<?php _e('Testing...', 'ai-seo-optimizer'); ?>');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'ai_seo_shopify_connect',
                nonce: ai_seo_ajax.nonce,
                store_url: $('#shopify_store_url').val(),
                access_token: $('#shopify_access_token').val()
            },
            success: function(response) {
                if (response.success) {
                    showShopifyStatus('success', '<?php _e('Connection successful!', 'ai-seo-optimizer'); ?>', response.data.store_info);
                } else {
                    showShopifyStatus('error', response.data);
                }
            },
            error: function() {
                showShopifyStatus('error', '<?php _e('Connection test failed.', 'ai-seo-optimizer'); ?>');
            },
            complete: function() {
                $btn.prop('disabled', false).text(originalText);
            }
        });
    });
    
    // Analyze store SEO
    $('#shopify-analyze-btn').on('click', function() {
        var $btn = $(this);
        var originalText = $btn.text();
        
        $btn.prop('disabled', true).text('<?php _e('Analyzing...', 'ai-seo-optimizer'); ?>');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'ai_seo_shopify_analyze',
                nonce: ai_seo_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    showAnalysisResults(response.data);
                } else {
                    alert('<?php _e('Analysis failed:', 'ai-seo-optimizer'); ?> ' + response.data);
                }
            },
            error: function() {
                alert('<?php _e('Analysis failed. Please try again.', 'ai-seo-optimizer'); ?>');
            },
            complete: function() {
                $btn.prop('disabled', false).text(originalText);
            }
        });
    });
    
    function showShopifyStatus(type, message, storeInfo) {
        var $status = $('#shopify-connection-status');
        var $content = $('#shopify-status-content');
        
        var html = '<div class="notice notice-' + (type === 'success' ? 'success' : 'error') + ' is-dismissible">';
        html += '<p>' + message + '</p>';
        
        if (storeInfo) {
            html += '<div class="store-info">';
            html += '<h4><?php _e('Store Information:', 'ai-seo-optimizer'); ?></h4>';
            html += '<ul>';
            html += '<li><strong><?php _e('Name:', 'ai-seo-optimizer'); ?></strong> ' + storeInfo.store_name + '</li>';
            html += '<li><strong><?php _e('Domain:', 'ai-seo-optimizer'); ?></strong> ' + storeInfo.store_url + '</li>';
            html += '<li><strong><?php _e('Plan:', 'ai-seo-optimizer'); ?></strong> ' + storeInfo.plan + '</li>';
            html += '</ul>';
            html += '</div>';
        }
        
        html += '</div>';
        
        $content.html(html);
        $status.show();
    }
    
    function showAnalysisResults(data) {
        var html = '<div class="analysis-results">';
        html += '<h3><?php _e('SEO Analysis Results', 'ai-seo-optimizer'); ?></h3>';
        html += '<div class="seo-score">';
        html += '<span class="score">' + data.seo_score + '</span>';
        html += '<span class="label"><?php _e('SEO Score', 'ai-seo-optimizer'); ?></span>';
        html += '</div>';
        
        if (data.recommendations && data.recommendations.length > 0) {
            html += '<h4><?php _e('Recommendations:', 'ai-seo-optimizer'); ?></h4>';
            html += '<ul class="recommendations-list">';
            data.recommendations.forEach(function(rec) {
                html += '<li class="recommendation-item priority-' + rec.priority + '">';
                html += '<strong>' + rec.title + '</strong>';
                html += '<p>' + rec.description + '</p>';
                html += '<small><?php _e('Impact:', 'ai-seo-optimizer'); ?> +' + rec.seo_points + ' <?php _e('points', 'ai-seo-optimizer'); ?> | <?php _e('Confidence:', 'ai-seo-optimizer'); ?> ' + Math.round(rec.confidence_score * 100) + '%</small>';
                html += '</li>';
            });
            html += '</ul>';
        }
        
        html += '</div>';
        
        // Show in a modal or replace content
        $('#shopify-connection-status').show();
        $('#shopify-status-content').html(html);
    }
});
</script>

<style>
.ai-seo-settings-container {
    max-width: 1200px;
}

.ai-seo-settings-section {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
    margin-bottom: 20px;
}

.feature-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.feature-card {
    background: #f9f9f9;
    border: 1px solid #e1e1e1;
    border-radius: 4px;
    padding: 20px;
    text-align: center;
}

.feature-icon {
    font-size: 2em;
    color: #0073aa;
    margin-bottom: 15px;
}

.quick-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.quick-actions .button {
    display: flex;
    align-items: center;
    gap: 5px;
}

.store-info {
    margin-top: 15px;
    padding: 15px;
    background: #f9f9f9;
    border-radius: 4px;
}

.store-info ul {
    margin: 10px 0 0 0;
    padding-left: 20px;
}

.analysis-results {
    background: #f9f9f9;
    padding: 20px;
    border-radius: 4px;
}

.seo-score {
    text-align: center;
    margin: 20px 0;
}

.seo-score .score {
    display: block;
    font-size: 3em;
    font-weight: bold;
    color: #0073aa;
}

.recommendations-list {
    list-style: none;
    padding: 0;
}

.recommendation-item {
    background: #fff;
    border: 1px solid #e1e1e1;
    border-radius: 4px;
    padding: 15px;
    margin-bottom: 10px;
}

.recommendation-item.priority-high {
    border-left: 4px solid #dc3232;
}

.recommendation-item.priority-medium {
    border-left: 4px solid #ffb900;
}

.recommendation-item.priority-low {
    border-left: 4px solid #46b450;
}

.badge {
    background: #0073aa;
    color: #fff;
    border-radius: 10px;
    padding: 2px 8px;
    font-size: 0.8em;
    margin-left: 5px;
}
</style>
