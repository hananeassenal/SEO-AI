<?php
/**
 * Content Updater Class
 * 
 * Handles applying SEO changes to WordPress content
 * 
 * @package AI_SEO_Optimizer
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * AI SEO Content Updater Class
 */
class AI_SEO_Content_Updater {
    
    /**
     * Audit logger instance
     */
    private $audit_logger;
    
    /**
     * Constructor
     */
    public function __construct() {
        // Initialize audit logger when needed
        $this->audit_logger = null;
    }
    
    /**
     * Get audit logger instance
     */
    private function get_audit_logger() {
        if ($this->audit_logger === null) {
            global $ai_seo_optimizer;
            if (isset($ai_seo_optimizer) && $ai_seo_optimizer) {
                $this->audit_logger = $ai_seo_optimizer->audit_logger;
            } else {
                // Create a new instance if global is not available
                require_once AI_SEO_OPTIMIZER_PLUGIN_DIR . 'includes/class-audit-logger.php';
                $this->audit_logger = new AI_SEO_Audit_Logger();
            }
        }
        return $this->audit_logger;
    }
    
    /**
     * Apply changes to content
     */
    public function apply_changes($changes) {
        if (!is_array($changes)) {
            return new WP_Error('invalid_changes', __('Invalid changes format', 'ai-seo-optimizer'));
        }
        
        $results = array(
            'success' => 0,
            'failed' => 0,
            'errors' => array(),
            'changes' => array(),
        );
        
        foreach ($changes as $change) {
            $result = $this->apply_single_change($change);
            
            if (is_wp_error($result)) {
                $results['failed']++;
                $results['errors'][] = $result->get_error_message();
            } else {
                $results['success']++;
                $results['changes'][] = $result;
            }
        }
        
        return $results;
    }
    
    /**
     * Apply a single change
     */
    private function apply_single_change($change) {
        // Handle both old format and new recommendation format
        if (isset($change['change_type'])) {
            // New recommendation format from API
            $post_id = intval($change['target_post_id']);
            $change_type = sanitize_text_field($change['change_type']);
            $new_value = isset($change['new_value']) ? $change['new_value'] : '';
            $old_value = isset($change['old_value']) ? $change['old_value'] : '';
        } else {
            // Old format
            if (!isset($change['type']) || !isset($change['post_id']) || !isset($change['field'])) {
                return new WP_Error('invalid_change', __('Invalid change structure', 'ai-seo-optimizer'));
            }
            
            $post_id = intval($change['post_id']);
            $change_type = sanitize_text_field($change['type']);
            $field = sanitize_text_field($change['field']);
            $new_value = isset($change['new_value']) ? $change['new_value'] : '';
            $old_value = isset($change['old_value']) ? $change['old_value'] : '';
        }
        
        // Verify post exists
        $post = get_post($post_id);
        if (!$post) {
            return new WP_Error('post_not_found', sprintf(__('Post with ID %d not found', 'ai-seo-optimizer'), $post_id));
        }
        
        // Check user permissions
        if (!current_user_can('edit_post', $post_id)) {
            return new WP_Error('insufficient_permissions', __('Insufficient permissions to edit this post', 'ai-seo-optimizer'));
        }
        
        // Apply change based on type
        switch ($change_type) {
            case 'post_title':
                return $this->update_post_title($post_id, $new_value, $old_value);
                
            case 'post_content':
                return $this->update_post_content($post_id, $new_value, $old_value);
                
            case 'post_excerpt':
                return $this->update_post_excerpt($post_id, $new_value, $old_value);
                
            case 'meta_description':
                return $this->update_meta_description($post_id, $new_value, $old_value);
                
            case 'meta_title':
                return $this->update_meta_title($post_id, $new_value, $old_value);
                
            case 'focus_keyword':
                return $this->update_focus_keyword($post_id, $new_value, $old_value);
                
            case 'image_alt':
                return $this->update_image_alt($post_id, $change);
                
            case 'schema_markup':
                return $this->update_schema_markup($post_id, $new_value, $old_value);
                
            case 'custom_field':
                return $this->update_custom_field($post_id, $field, $new_value, $old_value);
                
            default:
                return new WP_Error('unknown_change_type', sprintf(__('Unknown change type: %s', 'ai-seo-optimizer'), $change_type));
        }
    }
    
    /**
     * Update post title
     */
    private function update_post_title($post_id, $new_value, $old_value) {
        $new_value = sanitize_text_field($new_value);
        
        if (empty($new_value)) {
            return new WP_Error('empty_title', __('Post title cannot be empty', 'ai-seo-optimizer'));
        }
        
        $result = wp_update_post(array(
            'ID' => $post_id,
            'post_title' => $new_value,
        ));
        
        if (is_wp_error($result)) {
            return $result;
        }
        
        $this->log_change($post_id, 'post_title', $old_value, $new_value, 'success');
        
        return array(
            'post_id' => $post_id,
            'type' => 'post_title',
            'old_value' => $old_value,
            'new_value' => $new_value,
            'status' => 'success',
        );
    }
    
    /**
     * Update post content
     */
    private function update_post_content($post_id, $new_value, $old_value) {
        $new_value = wp_kses_post($new_value);
        
        if (empty($new_value)) {
            return new WP_Error('empty_content', __('Post content cannot be empty', 'ai-seo-optimizer'));
        }
        
        $result = wp_update_post(array(
            'ID' => $post_id,
            'post_content' => $new_value,
        ));
        
        if (is_wp_error($result)) {
            return $result;
        }
        
        $this->log_change($post_id, 'post_content', $old_value, $new_value, 'success');
        
        return array(
            'post_id' => $post_id,
            'type' => 'post_content',
            'old_value' => $old_value,
            'new_value' => $new_value,
            'status' => 'success',
        );
    }
    
    /**
     * Update post excerpt
     */
    private function update_post_excerpt($post_id, $new_value, $old_value) {
        $new_value = sanitize_textarea_field($new_value);
        
        $result = wp_update_post(array(
            'ID' => $post_id,
            'post_excerpt' => $new_value,
        ));
        
        if (is_wp_error($result)) {
            return $result;
        }
        
        $this->log_change($post_id, 'post_excerpt', $old_value, $new_value, 'success');
        
        return array(
            'post_id' => $post_id,
            'type' => 'post_excerpt',
            'old_value' => $old_value,
            'new_value' => $new_value,
            'status' => 'success',
        );
    }
    
    /**
     * Update meta description
     */
    private function update_meta_description($post_id, $new_value, $old_value) {
        $new_value = sanitize_textarea_field($new_value);
        
        // Update for Yoast SEO
        if (is_plugin_active('wordpress-seo/wp-seo.php')) {
            update_post_meta($post_id, '_yoast_wpseo_metadesc', $new_value);
        }
        
        // Update for RankMath
        if (is_plugin_active('seo-by-rank-math/rank-math.php')) {
            update_post_meta($post_id, 'rank_math_description', $new_value);
        }
        
        // Update for All in One SEO
        if (is_plugin_active('all-in-one-seo-pack/all_in_one_seo_pack.php')) {
            update_post_meta($post_id, '_aioseo_description', $new_value);
        }
        
        $this->log_change($post_id, 'meta_description', $old_value, $new_value, 'success');
        
        return array(
            'post_id' => $post_id,
            'type' => 'meta_description',
            'old_value' => $old_value,
            'new_value' => $new_value,
            'status' => 'success',
        );
    }
    
    /**
     * Update meta title
     */
    private function update_meta_title($post_id, $new_value, $old_value) {
        $new_value = sanitize_text_field($new_value);
        
        // Update for Yoast SEO
        if (is_plugin_active('wordpress-seo/wp-seo.php')) {
            update_post_meta($post_id, '_yoast_wpseo_title', $new_value);
        }
        
        // Update for RankMath
        if (is_plugin_active('seo-by-rank-math/rank-math.php')) {
            update_post_meta($post_id, 'rank_math_title', $new_value);
        }
        
        // Update for All in One SEO
        if (is_plugin_active('all-in-one-seo-pack/all_in_one_seo_pack.php')) {
            update_post_meta($post_id, '_aioseo_title', $new_value);
        }
        
        $this->log_change($post_id, 'meta_title', $old_value, $new_value, 'success');
        
        return array(
            'post_id' => $post_id,
            'type' => 'meta_title',
            'old_value' => $old_value,
            'new_value' => $new_value,
            'status' => 'success',
        );
    }
    
    /**
     * Update focus keyword
     */
    private function update_focus_keyword($post_id, $new_value, $old_value) {
        $new_value = sanitize_text_field($new_value);
        
        // Update for Yoast SEO
        if (is_plugin_active('wordpress-seo/wp-seo.php')) {
            update_post_meta($post_id, '_yoast_wpseo_focuskw', $new_value);
        }
        
        // Update for RankMath
        if (is_plugin_active('seo-by-rank-math/rank-math.php')) {
            update_post_meta($post_id, 'rank_math_focus_keyword', $new_value);
        }
        
        // Update for All in One SEO
        if (is_plugin_active('all-in-one-seo-pack/all_in_one_seo_pack.php')) {
            update_post_meta($post_id, '_aioseo_keywords', $new_value);
        }
        
        $this->log_change($post_id, 'focus_keyword', $old_value, $new_value, 'success');
        
        return array(
            'post_id' => $post_id,
            'type' => 'focus_keyword',
            'old_value' => $old_value,
            'new_value' => $new_value,
            'status' => 'success',
        );
    }
    
    /**
     * Update image alt text
     */
    private function update_image_alt($post_id, $change) {
        if (!isset($change['image_id']) || !isset($change['new_value'])) {
            return new WP_Error('invalid_image_change', __('Invalid image change data', 'ai-seo-optimizer'));
        }
        
        $image_id = intval($change['image_id']);
        $new_value = sanitize_text_field($change['new_value']);
        $old_value = isset($change['old_value']) ? $change['old_value'] : '';
        
        // Verify image exists
        $image = get_post($image_id);
        if (!$image || $image->post_type !== 'attachment') {
            return new WP_Error('image_not_found', __('Image not found', 'ai-seo-optimizer'));
        }
        
        // Update alt text
        update_post_meta($image_id, '_wp_attachment_image_alt', $new_value);
        
        $this->log_change($post_id, 'image_alt', $old_value, $new_value, 'success', $image_id);
        
        return array(
            'post_id' => $post_id,
            'image_id' => $image_id,
            'type' => 'image_alt',
            'old_value' => $old_value,
            'new_value' => $new_value,
            'status' => 'success',
        );
    }
    
    /**
     * Update schema markup
     */
    private function update_schema_markup($post_id, $new_value, $old_value) {
        $new_value = wp_kses_post($new_value);
        
        // Store schema markup in custom field
        update_post_meta($post_id, '_ai_seo_schema_markup', $new_value);
        
        $this->log_change($post_id, 'schema_markup', $old_value, $new_value, 'success');
        
        return array(
            'post_id' => $post_id,
            'type' => 'schema_markup',
            'old_value' => $old_value,
            'new_value' => $new_value,
            'status' => 'success',
        );
    }
    
    /**
     * Update custom field
     */
    private function update_custom_field($post_id, $field, $new_value, $old_value) {
        $field = sanitize_key($field);
        $new_value = sanitize_text_field($new_value);
        
        if (empty($field)) {
            return new WP_Error('invalid_field', __('Invalid custom field name', 'ai-seo-optimizer'));
        }
        
        update_post_meta($post_id, $field, $new_value);
        
        $this->log_change($post_id, 'custom_field', $old_value, $new_value, 'success', null, $field);
        
        return array(
            'post_id' => $post_id,
            'type' => 'custom_field',
            'field' => $field,
            'old_value' => $old_value,
            'new_value' => $new_value,
            'status' => 'success',
        );
    }
    
    /**
     * Log change to audit log
     */
    private function log_change($post_id, $change_type, $old_value, $new_value, $status, $image_id = null, $field = null) {
        $change_id = uniqid('change_');
        
        $log_data = array(
            'change_id' => $change_id,
            'change_type' => $change_type,
            'target_post_id' => $post_id,
            'old_value' => $old_value,
            'new_value' => $new_value,
            'status' => $status,
            'message' => sprintf(__('Applied %s change to post %d', 'ai-seo-optimizer'), $change_type, $post_id),
            'applied_at' => current_time('mysql'),
        );
        
        if ($image_id) {
            $log_data['image_id'] = $image_id;
        }
        
        if ($field) {
            $log_data['field'] = $field;
        }
        
        $audit_logger = $this->get_audit_logger();
        if ($audit_logger) {
            $audit_logger->log_change($log_data);
        }
    }
    
    /**
     * Get available SEO fields for a post
     */
    public function get_available_seo_fields($post_id) {
        $fields = array();
        
        // Basic post fields
        $fields['post_title'] = __('Post Title', 'ai-seo-optimizer');
        $fields['post_content'] = __('Post Content', 'ai-seo-optimizer');
        $fields['post_excerpt'] = __('Post Excerpt', 'ai-seo-optimizer');
        
        // SEO plugin fields
        if (is_plugin_active('wordpress-seo/wp-seo.php')) {
            $fields['yoast_title'] = __('Yoast SEO Title', 'ai-seo-optimizer');
            $fields['yoast_meta_description'] = __('Yoast SEO Meta Description', 'ai-seo-optimizer');
            $fields['yoast_focus_keyword'] = __('Yoast SEO Focus Keyword', 'ai-seo-optimizer');
        }
        
        if (is_plugin_active('seo-by-rank-math/rank-math.php')) {
            $fields['rankmath_title'] = __('RankMath Title', 'ai-seo-optimizer');
            $fields['rankmath_meta_description'] = __('RankMath Meta Description', 'ai-seo-optimizer');
            $fields['rankmath_focus_keyword'] = __('RankMath Focus Keyword', 'ai-seo-optimizer');
        }
        
        if (is_plugin_active('all-in-one-seo-pack/all_in_one_seo_pack.php')) {
            $fields['aioseo_title'] = __('All in One SEO Title', 'ai-seo-optimizer');
            $fields['aioseo_meta_description'] = __('All in One SEO Meta Description', 'ai-seo-optimizer');
            $fields['aioseo_keywords'] = __('All in One SEO Keywords', 'ai-seo-optimizer');
        }
        
        // Custom fields
        $custom_fields = get_post_custom_keys($post_id);
        if ($custom_fields) {
            foreach ($custom_fields as $field) {
                if (strpos($field, '_') !== 0) { // Skip private fields
                    $fields['custom_' . $field] = sprintf(__('Custom Field: %s', 'ai-seo-optimizer'), $field);
                }
            }
        }
        
        return $fields;
    }
    
    /**
     * Get current SEO values for a post
     */
    public function get_current_seo_values($post_id) {
        $post = get_post($post_id);
        if (!$post) {
            return new WP_Error('post_not_found', __('Post not found', 'ai-seo-optimizer'));
        }
        
        $values = array(
            'post_title' => $post->post_title,
            'post_content' => $post->post_content,
            'post_excerpt' => $post->post_excerpt,
        );
        
        // Yoast SEO values
        if (is_plugin_active('wordpress-seo/wp-seo.php')) {
            $values['yoast_title'] = get_post_meta($post_id, '_yoast_wpseo_title', true);
            $values['yoast_meta_description'] = get_post_meta($post_id, '_yoast_wpseo_metadesc', true);
            $values['yoast_focus_keyword'] = get_post_meta($post_id, '_yoast_wpseo_focuskw', true);
        }
        
        // RankMath values
        if (is_plugin_active('seo-by-rank-math/rank-math.php')) {
            $values['rankmath_title'] = get_post_meta($post_id, 'rank_math_title', true);
            $values['rankmath_meta_description'] = get_post_meta($post_id, 'rank_math_description', true);
            $values['rankmath_focus_keyword'] = get_post_meta($post_id, 'rank_math_focus_keyword', true);
        }
        
        // All in One SEO values
        if (is_plugin_active('all-in-one-seo-pack/all_in_one_seo_pack.php')) {
            $values['aioseo_title'] = get_post_meta($post_id, '_aioseo_title', true);
            $values['aioseo_meta_description'] = get_post_meta($post_id, '_aioseo_description', true);
            $values['aioseo_keywords'] = get_post_meta($post_id, '_aioseo_keywords', true);
        }
        
        return $values;
    }
}
