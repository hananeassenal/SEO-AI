<?php
/**
 * Pending Changes Template
 * Displays AI recommendations that need approval before implementation
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get pending recommendations
$pending_recommendations = $this->approval_workflow->get_pending_recommendations(50);
?>

<div class="wrap">
    <h1><?php _e('Pending AI Recommendations', 'ai-seo-optimizer'); ?></h1>
    
    <div class="notice notice-info">
        <p><?php _e('Review and approve AI-generated SEO recommendations before they are automatically implemented.', 'ai-seo-optimizer'); ?></p>
    </div>
    
    <?php if (empty($pending_recommendations)): ?>
        <div class="card">
            <h2><?php _e('No Pending Recommendations', 'ai-seo-optimizer'); ?></h2>
            <p><?php _e('All AI recommendations have been reviewed. Run a new scan to generate fresh recommendations.', 'ai-seo-optimizer'); ?></p>
            <p><a href="<?php echo admin_url('admin.php?page=ai-seo-optimizer'); ?>" class="button button-primary"><?php _e('Run New Scan', 'ai-seo-optimizer'); ?></a></p>
        </div>
    <?php else: ?>
        <div class="ai-seo-pending-changes">
            <?php foreach ($pending_recommendations as $recommendation): ?>
                <div class="ai-seo-recommendation-card" data-recommendation-id="<?php echo esc_attr($recommendation['id']); ?>">
                    <div class="recommendation-header">
                        <h3><?php echo esc_html($this->get_recommendation_type_label($recommendation['recommendation_type'])); ?></h3>
                        <div class="confidence-score">
                            <span class="score-label"><?php _e('Confidence:', 'ai-seo-optimizer'); ?></span>
                            <span class="score-value"><?php echo round($recommendation['confidence_score'] * 100); ?>%</span>
                        </div>
                    </div>
                    
                    <div class="recommendation-content">
                        <div class="ai-reasoning">
                            <h4><?php _e('AI Reasoning:', 'ai-seo-optimizer'); ?></h4>
                            <p><?php echo esc_html($recommendation['ai_reasoning']); ?></p>
                        </div>
                        
                        <div class="impact-analysis">
                            <h4><?php _e('Impact Analysis:', 'ai-seo-optimizer'); ?></h4>
                            <p><?php echo esc_html($recommendation['impact_analysis']); ?></p>
                        </div>
                        
                        <div class="risk-assessment">
                            <h4><?php _e('Risk Assessment:', 'ai-seo-optimizer'); ?></h4>
                            <p><?php echo esc_html($recommendation['risk_assessment']); ?></p>
                        </div>
                        
                        <div class="current-vs-suggested">
                            <div class="current-value">
                                <h4><?php _e('Current Value:', 'ai-seo-optimizer'); ?></h4>
                                <div class="value-display"><?php echo esc_html($recommendation['current_value']); ?></div>
                            </div>
                            
                            <div class="suggested-value">
                                <h4><?php _e('Suggested Value:', 'ai-seo-optimizer'); ?></h4>
                                <div class="value-display"><?php echo esc_html($recommendation['suggested_value']); ?></div>
                            </div>
                        </div>
                        
                        <div class="implementation-details">
                            <h4><?php _e('Implementation Details:', 'ai-seo-optimizer'); ?></h4>
                            <p><?php echo esc_html($recommendation['implementation_details']); ?></p>
                        </div>
                    </div>
                    
                    <div class="recommendation-actions">
                        <button class="button button-primary approve-change" data-recommendation-id="<?php echo esc_attr($recommendation['id']); ?>">
                            <?php _e('Approve', 'ai-seo-optimizer'); ?>
                        </button>
                        
                        <button class="button button-secondary modify-suggestion" data-recommendation-id="<?php echo esc_attr($recommendation['id']); ?>">
                            <?php _e('Modify', 'ai-seo-optimizer'); ?>
                        </button>
                        
                        <button class="button button-secondary reject-change" data-recommendation-id="<?php echo esc_attr($recommendation['id']); ?>">
                            <?php _e('Reject', 'ai-seo-optimizer'); ?>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="bulk-actions">
            <h3><?php _e('Bulk Actions', 'ai-seo-optimizer'); ?></h3>
            <button class="button button-primary approve-all-changes">
                <?php _e('Approve All', 'ai-seo-optimizer'); ?>
            </button>
            <button class="button button-secondary reject-all-changes">
                <?php _e('Reject All', 'ai-seo-optimizer'); ?>
            </button>
        </div>
    <?php endif; ?>
</div>

<!-- Modals for actions -->
<div id="approve-modal" class="ai-seo-modal" style="display: none;">
    <div class="modal-content">
        <h3><?php _e('Approve Recommendation', 'ai-seo-optimizer'); ?></h3>
        <p><?php _e('Are you sure you want to approve this recommendation? It will be automatically implemented.', 'ai-seo-optimizer'); ?></p>
        <textarea id="approval-notes" placeholder="<?php _e('Add review notes (optional)', 'ai-seo-optimizer'); ?>"></textarea>
        <div class="modal-actions">
            <button class="button button-primary confirm-approve"><?php _e('Confirm Approval', 'ai-seo-optimizer'); ?></button>
            <button class="button button-secondary cancel-action"><?php _e('Cancel', 'ai-seo-optimizer'); ?></button>
        </div>
    </div>
</div>

<div id="reject-modal" class="ai-seo-modal" style="display: none;">
    <div class="modal-content">
        <h3><?php _e('Reject Recommendation', 'ai-seo-optimizer'); ?></h3>
        <p><?php _e('Please provide a reason for rejecting this recommendation:', 'ai-seo-optimizer'); ?></p>
        <textarea id="rejection-reason" placeholder="<?php _e('Rejection reason', 'ai-seo-optimizer'); ?>" required></textarea>
        <div class="modal-actions">
            <button class="button button-primary confirm-reject"><?php _e('Confirm Rejection', 'ai-seo-optimizer'); ?></button>
            <button class="button button-secondary cancel-action"><?php _e('Cancel', 'ai-seo-optimizer'); ?></button>
        </div>
    </div>
</div>

<div id="modify-modal" class="ai-seo-modal" style="display: none;">
    <div class="modal-content">
        <h3><?php _e('Modify Suggestion', 'ai-seo-optimizer'); ?></h3>
        <p><?php _e('Modify the AI suggestion before approval:', 'ai-seo-optimizer'); ?></p>
        <div class="modify-fields">
            <label for="modified-suggested-value"><?php _e('Suggested Value:', 'ai-seo-optimizer'); ?></label>
            <textarea id="modified-suggested-value"></textarea>
            
            <label for="modified-reasoning"><?php _e('AI Reasoning:', 'ai-seo-optimizer'); ?></label>
            <textarea id="modified-reasoning"></textarea>
        </div>
        <div class="modal-actions">
            <button class="button button-primary confirm-modify"><?php _e('Save Modifications', 'ai-seo-optimizer'); ?></button>
            <button class="button button-secondary cancel-action"><?php _e('Cancel', 'ai-seo-optimizer'); ?></button>
        </div>
    </div>
</div>

<style>
.ai-seo-pending-changes {
    margin-top: 20px;
}

.ai-seo-recommendation-card {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    margin-bottom: 20px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.recommendation-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 1px solid #eee;
}

.recommendation-header h3 {
    margin: 0;
    color: #333;
}

.confidence-score {
    display: flex;
    align-items: center;
    gap: 5px;
}

.score-label {
    font-weight: 500;
    color: #666;
}

.score-value {
    background: #0073aa;
    color: white;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: bold;
}

.recommendation-content {
    margin-bottom: 20px;
}

.recommendation-content h4 {
    margin: 15px 0 5px 0;
    color: #333;
    font-size: 14px;
}

.recommendation-content p {
    margin: 0 0 10px 0;
    color: #666;
    line-height: 1.5;
}

.current-vs-suggested {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin: 15px 0;
}

.value-display {
    background: #f9f9f9;
    padding: 10px;
    border-radius: 4px;
    border-left: 3px solid #0073aa;
    font-family: monospace;
    white-space: pre-wrap;
    max-height: 100px;
    overflow-y: auto;
}

.recommendation-actions {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
}

.bulk-actions {
    margin-top: 30px;
    padding: 20px;
    background: #f9f9f9;
    border-radius: 8px;
}

.bulk-actions h3 {
    margin: 0 0 15px 0;
}

.ai-seo-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 100000;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-content {
    background: white;
    padding: 30px;
    border-radius: 8px;
    max-width: 500px;
    width: 90%;
    max-height: 80vh;
    overflow-y: auto;
}

.modal-content h3 {
    margin: 0 0 15px 0;
}

.modal-content textarea {
    width: 100%;
    min-height: 100px;
    margin: 10px 0;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.modal-actions {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
    margin-top: 20px;
}

.modify-fields label {
    display: block;
    margin: 10px 0 5px 0;
    font-weight: 500;
}

.modify-fields textarea {
    width: 100%;
    min-height: 80px;
    margin-bottom: 15px;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Approve change
    $('.approve-change').on('click', function() {
        var recommendationId = $(this).data('recommendation-id');
        $('#approve-modal').show();
        $('#approve-modal').data('recommendation-id', recommendationId);
    });
    
    // Reject change
    $('.reject-change').on('click', function() {
        var recommendationId = $(this).data('recommendation-id');
        $('#reject-modal').show();
        $('#reject-modal').data('recommendation-id', recommendationId);
    });
    
    // Modify suggestion
    $('.modify-suggestion').on('click', function() {
        var recommendationId = $(this).data('recommendation-id');
        var card = $('[data-recommendation-id="' + recommendationId + '"]');
        
        // Populate modal with current values
        $('#modified-suggested-value').val(card.find('.suggested-value .value-display').text());
        $('#modified-reasoning').val(card.find('.ai-reasoning p').text());
        
        $('#modify-modal').show();
        $('#modify-modal').data('recommendation-id', recommendationId);
    });
    
    // Confirm approve
    $('.confirm-approve').on('click', function() {
        var recommendationId = $('#approve-modal').data('recommendation-id');
        var notes = $('#approval-notes').val();
        
        $.ajax({
            url: aiSeoAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'ai_seo_approve_change',
                nonce: aiSeoAjax.nonce,
                change_id: recommendationId,
                notes: notes
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('Error: ' + response.data);
                }
            }
        });
        
        $('#approve-modal').hide();
    });
    
    // Confirm reject
    $('.confirm-reject').on('click', function() {
        var recommendationId = $('#reject-modal').data('recommendation-id');
        var reason = $('#rejection-reason').val();
        
        if (!reason) {
            alert('Please provide a rejection reason.');
            return;
        }
        
        $.ajax({
            url: aiSeoAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'ai_seo_reject_change',
                nonce: aiSeoAjax.nonce,
                change_id: recommendationId,
                reason: reason
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('Error: ' + response.data);
                }
            }
        });
        
        $('#reject-modal').hide();
    });
    
    // Confirm modify
    $('.confirm-modify').on('click', function() {
        var recommendationId = $('#modify-modal').data('recommendation-id');
        var modifications = {
            suggested_value: $('#modified-suggested-value').val(),
            ai_reasoning: $('#modified-reasoning').val()
        };
        
        $.ajax({
            url: aiSeoAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'ai_seo_modify_suggestion',
                nonce: aiSeoAjax.nonce,
                change_id: recommendationId,
                modifications: modifications
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('Error: ' + response.data);
                }
            }
        });
        
        $('#modify-modal').hide();
    });
    
    // Cancel action
    $('.cancel-action').on('click', function() {
        $('.ai-seo-modal').hide();
    });
    
    // Close modal on background click
    $('.ai-seo-modal').on('click', function(e) {
        if (e.target === this) {
            $(this).hide();
        }
    });
});
</script>

<?php
/**
 * Helper function to get recommendation type label
 */
function get_recommendation_type_label($type) {
    $labels = array(
        'title_optimization' => __('Title Optimization', 'ai-seo-optimizer'),
        'content_expansion' => __('Content Expansion', 'ai-seo-optimizer'),
        'meta_description_optimization' => __('Meta Description Optimization', 'ai-seo-optimizer'),
        'image_alt_optimization' => __('Image Alt Text Optimization', 'ai-seo-optimizer'),
        'internal_linking' => __('Internal Linking', 'ai-seo-optimizer'),
        'focus_keyword_optimization' => __('Focus Keyword Optimization', 'ai-seo-optimizer'),
    );
    
    return isset($labels[$type]) ? $labels[$type] : ucfirst(str_replace('_', ' ', $type));
}
?>

