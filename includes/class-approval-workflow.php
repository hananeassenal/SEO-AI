<?php
/**
 * AI SEO Approval Workflow Class
 * Handles the approval workflow for AI recommendations before implementation
 */

class AI_SEO_Approval_Workflow {
    
    /**
     * Database table names
     */
    private $recommendations_table;
    private $changes_table;
    
    /**
     * Constructor
     */
    public function __construct() {
        global $wpdb;
        $this->recommendations_table = $wpdb->prefix . 'ai_seo_recommendations';
        $this->changes_table = $wpdb->prefix . 'ai_seo_changes';
    }
    
    /**
     * Create database tables
     */
    public function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Recommendations table
        $recommendations_sql = "CREATE TABLE {$this->recommendations_table} (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            recommendation_type varchar(50) NOT NULL,
            target_id int(11) NOT NULL,
            target_type varchar(20) NOT NULL,
            current_value longtext,
            suggested_value longtext,
            ai_reasoning text NOT NULL,
            confidence_score decimal(3,2) NOT NULL,
            impact_analysis text,
            risk_assessment text,
            implementation_details text,
            status varchar(20) DEFAULT 'pending',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            reviewed_at datetime NULL,
            reviewed_by int(11) NULL,
            review_notes text,
            PRIMARY KEY (id),
            KEY recommendation_type (recommendation_type),
            KEY target_id (target_id),
            KEY target_type (target_type),
            KEY status (status),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        // Changes table
        $changes_sql = "CREATE TABLE {$this->changes_table} (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            recommendation_id mediumint(9) NOT NULL,
            change_type varchar(50) NOT NULL,
            target_id int(11) NOT NULL,
            target_type varchar(20) NOT NULL,
            old_value longtext,
            new_value longtext,
            implementation_status varchar(20) DEFAULT 'pending',
            implemented_at datetime NULL,
            implemented_by int(11) NULL,
            backup_id mediumint(9) NULL,
            rollback_available boolean DEFAULT 1,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY recommendation_id (recommendation_id),
            KEY change_type (change_type),
            KEY target_id (target_id),
            KEY implementation_status (implementation_status),
            FOREIGN KEY (recommendation_id) REFERENCES {$this->recommendations_table}(id) ON DELETE CASCADE
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($recommendations_sql);
        dbDelta($changes_sql);
    }
    
    /**
     * Create a new recommendation
     */
    public function create_recommendation($data) {
        global $wpdb;
        
        $defaults = array(
            'recommendation_type' => '',
            'target_id' => 0,
            'target_type' => '',
            'current_value' => '',
            'suggested_value' => '',
            'ai_reasoning' => '',
            'confidence_score' => 0.0,
            'impact_analysis' => '',
            'risk_assessment' => '',
            'implementation_details' => '',
            'status' => 'pending'
        );
        
        $data = wp_parse_args($data, $defaults);
        
        // Validate required fields
        if (empty($data['recommendation_type']) || empty($data['target_type']) || empty($data['ai_reasoning'])) {
            return new WP_Error('invalid_data', 'Missing required fields for recommendation');
        }
        
        // Validate confidence score
        if ($data['confidence_score'] < 0 || $data['confidence_score'] > 1) {
            return new WP_Error('invalid_confidence', 'Confidence score must be between 0 and 1');
        }
        
        $result = $wpdb->insert(
            $this->recommendations_table,
            $data,
            array('%s', '%d', '%s', '%s', '%s', '%s', '%f', '%s', '%s', '%s', '%s')
        );
        
        if ($result === false) {
            return new WP_Error('insert_failed', 'Failed to create recommendation');
        }
        
        $recommendation_id = $wpdb->insert_id;
        
        // Log the recommendation creation
        $this->log_recommendation_creation($recommendation_id, $data);
        
        return array(
            'success' => true,
            'recommendation_id' => $recommendation_id,
            'message' => 'Recommendation created successfully'
        );
    }
    
    /**
     * Approve a recommendation
     */
    public function approve_change($recommendation_id, $review_notes = '') {
        global $wpdb;
        
        // Get the recommendation
        $recommendation = $this->get_recommendation($recommendation_id);
        
        if (!$recommendation) {
            return new WP_Error('recommendation_not_found', 'Recommendation not found');
        }
        
        if ($recommendation['status'] !== 'pending') {
            return new WP_Error('invalid_status', 'Recommendation is not pending approval');
        }
        
        // Update recommendation status
        $result = $wpdb->update(
            $this->recommendations_table,
            array(
                'status' => 'approved',
                'reviewed_at' => current_time('mysql'),
                'reviewed_by' => get_current_user_id(),
                'review_notes' => $review_notes
            ),
            array('id' => $recommendation_id),
            array('%s', '%s', '%d', '%s'),
            array('%d')
        );
        
        if ($result === false) {
            return new WP_Error('update_failed', 'Failed to approve recommendation');
        }
        
        // Create change record
        $change_data = array(
            'recommendation_id' => $recommendation_id,
            'change_type' => $recommendation['recommendation_type'],
            'target_id' => $recommendation['target_id'],
            'target_type' => $recommendation['target_type'],
            'old_value' => $recommendation['current_value'],
            'new_value' => $recommendation['suggested_value'],
            'implementation_status' => 'pending'
        );
        
        $change_result = $this->create_change($change_data);
        
        if (is_wp_error($change_result)) {
            return $change_result;
        }
        
        // Log the approval
        $this->log_recommendation_approval($recommendation_id, $review_notes);
        
        return array(
            'success' => true,
            'recommendation_id' => $recommendation_id,
            'change_id' => $change_result['change_id'],
            'message' => 'Recommendation approved successfully'
        );
    }
    
    /**
     * Reject a recommendation
     */
    public function reject_change($recommendation_id, $reason = '') {
        global $wpdb;
        
        // Get the recommendation
        $recommendation = $this->get_recommendation($recommendation_id);
        
        if (!$recommendation) {
            return new WP_Error('recommendation_not_found', 'Recommendation not found');
        }
        
        if ($recommendation['status'] !== 'pending') {
            return new WP_Error('invalid_status', 'Recommendation is not pending approval');
        }
        
        // Update recommendation status
        $result = $wpdb->update(
            $this->recommendations_table,
            array(
                'status' => 'rejected',
                'reviewed_at' => current_time('mysql'),
                'reviewed_by' => get_current_user_id(),
                'review_notes' => $reason
            ),
            array('id' => $recommendation_id),
            array('%s', '%s', '%d', '%s'),
            array('%d')
        );
        
        if ($result === false) {
            return new WP_Error('update_failed', 'Failed to reject recommendation');
        }
        
        // Log the rejection
        $this->log_recommendation_rejection($recommendation_id, $reason);
        
        return array(
            'success' => true,
            'recommendation_id' => $recommendation_id,
            'message' => 'Recommendation rejected successfully'
        );
    }
    
    /**
     * Modify a suggestion
     */
    public function modify_suggestion($recommendation_id, $modifications) {
        global $wpdb;
        
        // Get the recommendation
        $recommendation = $this->get_recommendation($recommendation_id);
        
        if (!$recommendation) {
            return new WP_Error('recommendation_not_found', 'Recommendation not found');
        }
        
        if ($recommendation['status'] !== 'pending') {
            return new WP_Error('invalid_status', 'Recommendation is not pending approval');
        }
        
        // Apply modifications
        $updated_data = array();
        
        if (isset($modifications['suggested_value'])) {
            $updated_data['suggested_value'] = $modifications['suggested_value'];
        }
        
        if (isset($modifications['ai_reasoning'])) {
            $updated_data['ai_reasoning'] = $modifications['ai_reasoning'];
        }
        
        if (isset($modifications['implementation_details'])) {
            $updated_data['implementation_details'] = $modifications['implementation_details'];
        }
        
        if (empty($updated_data)) {
            return new WP_Error('no_modifications', 'No modifications provided');
        }
        
        // Update the recommendation
        $result = $wpdb->update(
            $this->recommendations_table,
            $updated_data,
            array('id' => $recommendation_id),
            array_fill(0, count($updated_data), '%s'),
            array('%d')
        );
        
        if ($result === false) {
            return new WP_Error('update_failed', 'Failed to modify recommendation');
        }
        
        // Log the modification
        $this->log_recommendation_modification($recommendation_id, $modifications);
        
        return array(
            'success' => true,
            'recommendation_id' => $recommendation_id,
            'message' => 'Recommendation modified successfully'
        );
    }
    
    /**
     * Get pending recommendations
     */
    public function get_pending_recommendations($limit = 20, $offset = 0) {
        global $wpdb;
        
        $recommendations = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$this->recommendations_table} 
                 WHERE status = 'pending' 
                 ORDER BY created_at DESC 
                 LIMIT %d OFFSET %d",
                $limit,
                $offset
            ),
            ARRAY_A
        );
        
        return $recommendations;
    }
    
    /**
     * Get pending changes
     */
    public function get_pending_changes($limit = 20, $offset = 0) {
        global $wpdb;
        
        $changes = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT c.*, r.recommendation_type, r.ai_reasoning, r.confidence_score 
                 FROM {$this->changes_table} c
                 JOIN {$this->recommendations_table} r ON c.recommendation_id = r.id
                 WHERE c.implementation_status = 'pending' 
                 ORDER BY c.created_at DESC 
                 LIMIT %d OFFSET %d",
                $limit,
                $offset
            ),
            ARRAY_A
        );
        
        return $changes;
    }
    
    /**
     * Get recommendation by ID
     */
    public function get_recommendation($recommendation_id) {
        global $wpdb;
        
        $recommendation = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$this->recommendations_table} WHERE id = %d", $recommendation_id),
            ARRAY_A
        );
        
        return $recommendation;
    }
    
    /**
     * Get change by ID
     */
    public function get_change($change_id) {
        global $wpdb;
        
        $change = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$this->changes_table} WHERE id = %d", $change_id),
            ARRAY_A
        );
        
        return $change;
    }
    
    /**
     * Create a change record
     */
    private function create_change($data) {
        global $wpdb;
        
        $result = $wpdb->insert(
            $this->changes_table,
            $data,
            array('%d', '%s', '%d', '%s', '%s', '%s', '%s')
        );
        
        if ($result === false) {
            return new WP_Error('change_creation_failed', 'Failed to create change record');
        }
        
        return array(
            'success' => true,
            'change_id' => $wpdb->insert_id
        );
    }
    
    /**
     * Get pending count
     */
    public function get_pending_count() {
        global $wpdb;
        
        $count = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$this->recommendations_table} WHERE status = 'pending'"
        );
        
        return intval($count);
    }
    
    /**
     * Get approved changes ready for implementation
     */
    public function get_approved_changes($limit = 20, $offset = 0) {
        global $wpdb;
        
        $changes = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT c.*, r.recommendation_type, r.ai_reasoning, r.confidence_score 
                 FROM {$this->changes_table} c
                 JOIN {$this->recommendations_table} r ON c.recommendation_id = r.id
                 WHERE c.implementation_status = 'pending' 
                 AND r.status = 'approved'
                 ORDER BY c.created_at ASC 
                 LIMIT %d OFFSET %d",
                $limit,
                $offset
            ),
            ARRAY_A
        );
        
        return $changes;
    }
    
    /**
     * Mark change as implemented
     */
    public function mark_change_implemented($change_id, $backup_id = null) {
        global $wpdb;
        
        $update_data = array(
            'implementation_status' => 'implemented',
            'implemented_at' => current_time('mysql'),
            'implemented_by' => get_current_user_id()
        );
        
        if ($backup_id) {
            $update_data['backup_id'] = $backup_id;
        }
        
        $result = $wpdb->update(
            $this->changes_table,
            $update_data,
            array('id' => $change_id),
            array('%s', '%s', '%d', '%d'),
            array('%d')
        );
        
        return $result !== false;
    }
    
    /**
     * Mark change as failed
     */
    public function mark_change_failed($change_id, $error_message = '') {
        global $wpdb;
        
        $result = $wpdb->update(
            $this->changes_table,
            array(
                'implementation_status' => 'failed',
                'implemented_at' => current_time('mysql'),
                'implemented_by' => get_current_user_id()
            ),
            array('id' => $change_id),
            array('%s', '%s', '%d'),
            array('%d')
        );
        
        // Log the failure
        if ($result !== false) {
            $this->log_implementation_failure($change_id, $error_message);
        }
        
        return $result !== false;
    }
    
    /**
     * Log recommendation creation
     */
    private function log_recommendation_creation($recommendation_id, $data) {
        if (class_exists('AI_SEO_Audit_Logger')) {
            $audit_logger = new AI_SEO_Audit_Logger();
            $audit_logger->log_action(
                'recommendation_created',
                sprintf('AI recommendation created: %s for %s %d', 
                    $data['recommendation_type'], 
                    $data['target_type'], 
                    $data['target_id']
                ),
                array(
                    'recommendation_id' => $recommendation_id,
                    'recommendation_type' => $data['recommendation_type'],
                    'target_type' => $data['target_type'],
                    'target_id' => $data['target_id'],
                    'confidence_score' => $data['confidence_score']
                )
            );
        }
    }
    
    /**
     * Log recommendation approval
     */
    private function log_recommendation_approval($recommendation_id, $review_notes) {
        if (class_exists('AI_SEO_Audit_Logger')) {
            $audit_logger = new AI_SEO_Audit_Logger();
            $audit_logger->log_action(
                'recommendation_approved',
                sprintf('Recommendation %d approved by user %d', $recommendation_id, get_current_user_id()),
                array(
                    'recommendation_id' => $recommendation_id,
                    'review_notes' => $review_notes,
                    'reviewed_by' => get_current_user_id()
                )
            );
        }
    }
    
    /**
     * Log recommendation rejection
     */
    private function log_recommendation_rejection($recommendation_id, $reason) {
        if (class_exists('AI_SEO_Audit_Logger')) {
            $audit_logger = new AI_SEO_Audit_Logger();
            $audit_logger->log_action(
                'recommendation_rejected',
                sprintf('Recommendation %d rejected by user %d', $recommendation_id, get_current_user_id()),
                array(
                    'recommendation_id' => $recommendation_id,
                    'rejection_reason' => $reason,
                    'reviewed_by' => get_current_user_id()
                )
            );
        }
    }
    
    /**
     * Log recommendation modification
     */
    private function log_recommendation_modification($recommendation_id, $modifications) {
        if (class_exists('AI_SEO_Audit_Logger')) {
            $audit_logger = new AI_SEO_Audit_Logger();
            $audit_logger->log_action(
                'recommendation_modified',
                sprintf('Recommendation %d modified by user %d', $recommendation_id, get_current_user_id()),
                array(
                    'recommendation_id' => $recommendation_id,
                    'modifications' => $modifications,
                    'modified_by' => get_current_user_id()
                )
            );
        }
    }
    
    /**
     * Log implementation failure
     */
    private function log_implementation_failure($change_id, $error_message) {
        if (class_exists('AI_SEO_Audit_Logger')) {
            $audit_logger = new AI_SEO_Audit_Logger();
            $audit_logger->log_action(
                'implementation_failed',
                sprintf('Change %d implementation failed: %s', $change_id, $error_message),
                array(
                    'change_id' => $change_id,
                    'error_message' => $error_message,
                    'implemented_by' => get_current_user_id()
                )
            );
        }
    }
}
