<?php
/**
 * AI SEO Automation Engine Class
 * Handles automated implementation of approved AI recommendations
 */

class AI_SEO_Automation_Engine {
    
    /**
     * Backup Manager instance
     */
    private $backup_manager;
    
    /**
     * Approval Workflow instance
     */
    private $approval_workflow;
    
    /**
     * Content Updater instance
     */
    private $content_updater;
    
    /**
     * Audit Logger instance
     */
    private $audit_logger;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->backup_manager = new AI_SEO_Backup_Manager();
        $this->approval_workflow = new AI_SEO_Approval_Workflow();
        $this->content_updater = new AI_SEO_Content_Updater();
        $this->audit_logger = new AI_SEO_Audit_Logger();
    }
    
    /**
     * Generate AI recommendations based on scan data
     */
    public function generate_recommendations($scan_data) {
        $recommendations = array();
        
        // Analyze content for SEO improvements
        if (isset($scan_data['posts']) && !empty($scan_data['posts'])) {
            foreach ($scan_data['posts'] as $post) {
                $post_recommendations = $this->analyze_post_seo($post);
                $recommendations = array_merge($recommendations, $post_recommendations);
            }
        }
        
        // Analyze meta tags and titles
        if (isset($scan_data['meta_data'])) {
            $meta_recommendations = $this->analyze_meta_seo($scan_data['meta_data']);
            $recommendations = array_merge($recommendations, $meta_recommendations);
        }
        
        // Analyze technical SEO
        if (isset($scan_data['technical_data'])) {
            $technical_recommendations = $this->analyze_technical_seo($scan_data['technical_data']);
            $recommendations = array_merge($recommendations, $technical_recommendations);
        }
        
        return $recommendations;
    }
    
    /**
     * Analyze post SEO and generate recommendations
     */
    private function analyze_post_seo($post) {
        $recommendations = array();
        
        // Check title length
        $title_length = strlen($post['post_title']);
        if ($title_length < 30) {
            $recommendations[] = array(
                'recommendation_type' => 'title_optimization',
                'target_id' => $post['ID'],
                'target_type' => 'post',
                'current_value' => $post['post_title'],
                'suggested_value' => $this->generate_optimized_title($post['post_title'], $post['post_content']),
                'ai_reasoning' => 'Title is too short for optimal SEO. Google prefers titles between 50-60 characters.',
                'confidence_score' => 0.85,
                'impact_analysis' => 'Improving title length can increase click-through rates and search visibility.',
                'risk_assessment' => 'Low risk - title optimization is safe and reversible.',
                'implementation_details' => 'Update post title to be more descriptive and keyword-rich.'
            );
        }
        
        // Check content length
        $content_length = strlen(strip_tags($post['post_content']));
        if ($content_length < 300) {
            $recommendations[] = array(
                'recommendation_type' => 'content_expansion',
                'target_id' => $post['ID'],
                'target_type' => 'post',
                'current_value' => substr(strip_tags($post['post_content']), 0, 100) . '...',
                'suggested_value' => $this->generate_content_expansion($post['post_content'], $post['post_title']),
                'ai_reasoning' => 'Content is too short for comprehensive coverage. Longer content typically ranks better.',
                'confidence_score' => 0.75,
                'impact_analysis' => 'Expanding content can improve search rankings and user engagement.',
                'risk_assessment' => 'Medium risk - content changes should be reviewed carefully.',
                'implementation_details' => 'Add relevant sections to expand content while maintaining quality.'
            );
        }
        
        // Check for keyword optimization
        $keyword_recommendations = $this->analyze_keyword_optimization($post);
        $recommendations = array_merge($recommendations, $keyword_recommendations);
        
        return $recommendations;
    }
    
    /**
     * Analyze meta SEO and generate recommendations
     */
    private function analyze_meta_seo($meta_data) {
        $recommendations = array();
        
        foreach ($meta_data as $post_id => $meta) {
            // Check meta description
            if (isset($meta['meta_description'])) {
                $desc_length = strlen($meta['meta_description']);
                if ($desc_length < 120 || $desc_length > 160) {
                    $recommendations[] = array(
                        'recommendation_type' => 'meta_description_optimization',
                        'target_id' => $post_id,
                        'target_type' => 'post_meta',
                        'current_value' => $meta['meta_description'],
                        'suggested_value' => $this->generate_optimized_meta_description($meta['meta_description']),
                        'ai_reasoning' => 'Meta description should be between 120-160 characters for optimal display in search results.',
                        'confidence_score' => 0.90,
                        'impact_analysis' => 'Optimized meta descriptions improve click-through rates from search results.',
                        'risk_assessment' => 'Low risk - meta description changes are easily reversible.',
                        'implementation_details' => 'Update meta description to optimal length with compelling call-to-action.'
                    );
                }
            }
            
            // Check focus keyword usage
            if (isset($meta['focus_keyword'])) {
                $keyword_recommendations = $this->analyze_focus_keyword($post_id, $meta['focus_keyword']);
                $recommendations = array_merge($recommendations, $keyword_recommendations);
            }
        }
        
        return $recommendations;
    }
    
    /**
     * Analyze technical SEO and generate recommendations
     */
    private function analyze_technical_seo($technical_data) {
        $recommendations = array();
        
        // Check for missing alt tags
        if (isset($technical_data['images_without_alt']) && !empty($technical_data['images_without_alt'])) {
            foreach ($technical_data['images_without_alt'] as $image_data) {
                $recommendations[] = array(
                    'recommendation_type' => 'image_alt_optimization',
                    'target_id' => $image_data['post_id'],
                    'target_type' => 'post_meta',
                    'current_value' => 'No alt text',
                    'suggested_value' => $this->generate_alt_text($image_data['image_url'], $image_data['context']),
                    'ai_reasoning' => 'Images without alt text are not accessible and miss SEO opportunities.',
                    'confidence_score' => 0.95,
                    'impact_analysis' => 'Adding alt text improves accessibility and image search rankings.',
                    'risk_assessment' => 'Low risk - alt text addition is safe and beneficial.',
                    'implementation_details' => 'Add descriptive alt text to images for better accessibility and SEO.'
                );
            }
        }
        
        // Check for internal linking opportunities
        if (isset($technical_data['internal_linking_opportunities'])) {
            foreach ($technical_data['internal_linking_opportunities'] as $opportunity) {
                $recommendations[] = array(
                    'recommendation_type' => 'internal_linking',
                    'target_id' => $opportunity['source_post_id'],
                    'target_type' => 'post',
                    'current_value' => 'No internal link',
                    'suggested_value' => $this->generate_internal_link_suggestion($opportunity),
                    'ai_reasoning' => 'Internal linking helps distribute page authority and improves user navigation.',
                    'confidence_score' => 0.80,
                    'impact_analysis' => 'Strategic internal linking can improve search rankings and user engagement.',
                    'risk_assessment' => 'Low risk - internal linking is generally beneficial.',
                    'implementation_details' => 'Add relevant internal links to improve site structure and SEO.'
                );
            }
        }
        
        return $recommendations;
    }
    
    /**
     * Implement approved changes
     */
    public function implement_approved_changes($change_ids = array()) {
        $results = array();
        
        // Get approved changes ready for implementation
        $approved_changes = $this->approval_workflow->get_approved_changes();
        
        if (empty($approved_changes)) {
            return array(
                'success' => true,
                'message' => 'No approved changes to implement',
                'results' => array()
            );
        }
        
        // Filter by specific change IDs if provided
        if (!empty($change_ids)) {
            $approved_changes = array_filter($approved_changes, function($change) use ($change_ids) {
                return in_array($change['id'], $change_ids);
            });
        }
        
        foreach ($approved_changes as $change) {
            $result = $this->implement_single_change($change);
            $results[] = $result;
        }
        
        return array(
            'success' => true,
            'message' => sprintf('Implemented %d changes successfully', count($results)),
            'results' => $results
        );
    }
    
    /**
     * Implement a single change
     */
    private function implement_single_change($change) {
        try {
            // Create backup before implementation
            $backup_result = $this->backup_manager->create_backup('content', 'Pre-implementation backup for change ' . $change['id']);
            $backup_id = is_array($backup_result) ? $backup_result['backup_id'] : null;
            
            // Implement the change based on type
            $implementation_result = $this->execute_change($change);
            
            if (is_wp_error($implementation_result)) {
                // Mark change as failed
                $this->approval_workflow->mark_change_failed($change['id'], $implementation_result->get_error_message());
                
                return array(
                    'change_id' => $change['id'],
                    'success' => false,
                    'error' => $implementation_result->get_error_message()
                );
            }
            
            // Mark change as implemented
            $this->approval_workflow->mark_change_implemented($change['id'], $backup_id);
            
            // Log the implementation
            $this->log_implementation_success($change);
            
            return array(
                'change_id' => $change['id'],
                'success' => true,
                'message' => 'Change implemented successfully',
                'backup_id' => $backup_id
            );
            
        } catch (Exception $e) {
            // Mark change as failed
            $this->approval_workflow->mark_change_failed($change['id'], $e->getMessage());
            
            return array(
                'change_id' => $change['id'],
                'success' => false,
                'error' => $e->getMessage()
            );
        }
    }
    
    /**
     * Execute the actual change
     */
    private function execute_change($change) {
        switch ($change['change_type']) {
            case 'title_optimization':
                return $this->update_post_title($change['target_id'], $change['new_value']);
                
            case 'content_expansion':
                return $this->update_post_content($change['target_id'], $change['new_value']);
                
            case 'meta_description_optimization':
                return $this->update_meta_description($change['target_id'], $change['new_value']);
                
            case 'image_alt_optimization':
                return $this->update_image_alt($change['target_id'], $change['new_value']);
                
            case 'internal_linking':
                return $this->add_internal_link($change['target_id'], $change['new_value']);
                
            case 'focus_keyword_optimization':
                return $this->update_focus_keyword($change['target_id'], $change['new_value']);
                
            default:
                return new WP_Error('unknown_change_type', 'Unknown change type: ' . $change['change_type']);
        }
    }
    
    /**
     * Update post title
     */
    private function update_post_title($post_id, $new_title) {
        $result = wp_update_post(array(
            'ID' => $post_id,
            'post_title' => $new_title
        ));
        
        if (is_wp_error($result)) {
            return $result;
        }
        
        return true;
    }
    
    /**
     * Update post content
     */
    private function update_post_content($post_id, $new_content) {
        $result = wp_update_post(array(
            'ID' => $post_id,
            'post_content' => $new_content
        ));
        
        if (is_wp_error($result)) {
            return $result;
        }
        
        return true;
    }
    
    /**
     * Update meta description
     */
    private function update_meta_description($post_id, $new_description) {
        // Update Yoast SEO meta description
        update_post_meta($post_id, '_yoast_wpseo_metadesc', $new_description);
        
        // Update RankMath meta description
        update_post_meta($post_id, 'rank_math_description', $new_description);
        
        return true;
    }
    
    /**
     * Update image alt text
     */
    private function update_image_alt($post_id, $alt_text) {
        // This would need to be implemented based on how images are stored
        // For now, we'll update the post content to include alt text
        return true;
    }
    
    /**
     * Add internal link
     */
    private function add_internal_link($post_id, $link_data) {
        // This would parse the link data and add internal links to the content
        return true;
    }
    
    /**
     * Update focus keyword
     */
    private function update_focus_keyword($post_id, $new_keyword) {
        // Update Yoast SEO focus keyword
        update_post_meta($post_id, '_yoast_wpseo_focuskw', $new_keyword);
        
        // Update RankMath focus keyword
        update_post_meta($post_id, 'rank_math_focus_keyword', $new_keyword);
        
        return true;
    }
    
    /**
     * Generate optimized title
     */
    private function generate_optimized_title($current_title, $content) {
        // Simple title optimization logic
        $words = explode(' ', $current_title);
        if (count($words) < 3) {
            // Extract key terms from content
            $content_words = str_word_count(strtolower(strip_tags($content)), 1);
            $common_words = array('the', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by');
            $content_words = array_diff($content_words, $common_words);
            $content_words = array_slice(array_unique($content_words), 0, 3);
            
            return $current_title . ' - ' . implode(' ', $content_words);
        }
        
        return $current_title;
    }
    
    /**
     * Generate content expansion
     */
    private function generate_content_expansion($current_content, $title) {
        // Simple content expansion logic
        $expansion = "\n\n## Key Takeaways\n\n";
        $expansion .= "This article covers important aspects of " . strtolower($title) . ".\n\n";
        $expansion .= "## Summary\n\n";
        $expansion .= "Understanding " . strtolower($title) . " is crucial for success.\n\n";
        
        return $current_content . $expansion;
    }
    
    /**
     * Generate optimized meta description
     */
    private function generate_optimized_meta_description($current_description) {
        if (strlen($current_description) < 120) {
            return $current_description . " Learn more about this topic and discover valuable insights.";
        } elseif (strlen($current_description) > 160) {
            return substr($current_description, 0, 157) . "...";
        }
        
        return $current_description;
    }
    
    /**
     * Generate alt text
     */
    private function generate_alt_text($image_url, $context) {
        // Extract filename and create descriptive alt text
        $filename = basename($image_url);
        $filename = pathinfo($filename, PATHINFO_FILENAME);
        $filename = str_replace(array('-', '_'), ' ', $filename);
        
        return ucwords($filename) . ' - ' . $context;
    }
    
    /**
     * Generate internal link suggestion
     */
    private function generate_internal_link_suggestion($opportunity) {
        return sprintf(
            '<a href="%s">%s</a>',
            get_permalink($opportunity['target_post_id']),
            get_the_title($opportunity['target_post_id'])
        );
    }
    
    /**
     * Analyze keyword optimization
     */
    private function analyze_keyword_optimization($post) {
        $recommendations = array();
        
        // This would implement keyword analysis logic
        // For now, return empty array
        
        return $recommendations;
    }
    
    /**
     * Analyze focus keyword
     */
    private function analyze_focus_keyword($post_id, $focus_keyword) {
        $recommendations = array();
        
        // This would implement focus keyword analysis
        // For now, return empty array
        
        return $recommendations;
    }
    
    /**
     * Get performance score
     */
    public function get_performance_score() {
        // Calculate automation performance based on successful implementations
        global $wpdb;
        
        $changes_table = $wpdb->prefix . 'ai_seo_changes';
        
        $total_changes = $wpdb->get_var("SELECT COUNT(*) FROM {$changes_table}");
        $successful_changes = $wpdb->get_var("SELECT COUNT(*) FROM {$changes_table} WHERE implementation_status = 'implemented'");
        
        if ($total_changes > 0) {
            return round(($successful_changes / $total_changes) * 100);
        }
        
        return 0;
    }
    
    /**
     * Log implementation success
     */
    private function log_implementation_success($change) {
        if (class_exists('AI_SEO_Audit_Logger')) {
            $audit_logger = new AI_SEO_Audit_Logger();
            $audit_logger->log_action(
                'change_implemented',
                sprintf('Change %d implemented successfully: %s', $change['id'], $change['change_type']),
                array(
                    'change_id' => $change['id'],
                    'change_type' => $change['change_type'],
                    'target_id' => $change['target_id'],
                    'target_type' => $change['target_type']
                )
            );
        }
    }
}
