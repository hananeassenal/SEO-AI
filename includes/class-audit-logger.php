<?php
/**
 * Audit Logger Class
 * 
 * Handles logging of all SEO changes and operations
 * 
 * @package AI_SEO_Optimizer
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * AI SEO Audit Logger Class
 */
class AI_SEO_Audit_Logger {
    
    /**
     * Database table name
     */
    private $table_name;
    
    /**
     * Constructor
     */
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'ai_seo_audit_log';
    }
    
    /**
     * Create audit log table
     */
    public function create_table() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE {$this->table_name} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            change_id varchar(50) NOT NULL,
            change_type varchar(100) NOT NULL,
            target_post_id bigint(20) unsigned NOT NULL,
            old_value longtext,
            new_value longtext,
            status varchar(50) NOT NULL DEFAULT 'pending',
            message text,
            image_id bigint(20) unsigned DEFAULT NULL,
            field varchar(100) DEFAULT NULL,
            applied_at datetime NOT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY change_id (change_id),
            KEY change_type (change_type),
            KEY target_post_id (target_post_id),
            KEY status (status),
            KEY applied_at (applied_at)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        // Add version option to track table structure
        add_option('ai_seo_audit_log_version', '1.0.0');
    }
    
    /**
     * Log a change
     */
    public function log_change($data) {
        global $wpdb;
        
        $defaults = array(
            'change_id' => uniqid('change_'),
            'change_type' => '',
            'target_post_id' => 0,
            'old_value' => '',
            'new_value' => '',
            'status' => 'pending',
            'message' => '',
            'image_id' => null,
            'field' => null,
            'applied_at' => current_time('mysql'),
        );
        
        $data = wp_parse_args($data, $defaults);
        
        // Sanitize data
        $data['change_id'] = sanitize_text_field($data['change_id']);
        $data['change_type'] = sanitize_text_field($data['change_type']);
        $data['target_post_id'] = intval($data['target_post_id']);
        $data['old_value'] = wp_kses_post($data['old_value']);
        $data['new_value'] = wp_kses_post($data['new_value']);
        $data['status'] = sanitize_text_field($data['status']);
        $data['message'] = sanitize_textarea_field($data['message']);
        $data['image_id'] = $data['image_id'] ? intval($data['image_id']) : null;
        $data['field'] = $data['field'] ? sanitize_key($data['field']) : null;
        $data['applied_at'] = sanitize_text_field($data['applied_at']);
        
        $result = $wpdb->insert(
            $this->table_name,
            $data,
            array(
                '%s', // change_id
                '%s', // change_type
                '%d', // target_post_id
                '%s', // old_value
                '%s', // new_value
                '%s', // status
                '%s', // message
                '%d', // image_id
                '%s', // field
                '%s', // applied_at
            )
        );
        
        if ($result === false) {
            error_log('AI SEO Optimizer: Failed to log change - ' . $wpdb->last_error);
            return false;
        }
        
        return $wpdb->insert_id;
    }
    
    /**
     * Get logs with pagination
     */
    public function get_logs($page = 1, $per_page = 20, $filters = array()) {
        global $wpdb;
        
        $page = max(1, intval($page));
        $per_page = max(1, min(100, intval($per_page)));
        $offset = ($page - 1) * $per_page;
        
        // Build WHERE clause
        $where_clauses = array();
        $where_values = array();
        
        if (!empty($filters['change_type'])) {
            $where_clauses[] = 'change_type = %s';
            $where_values[] = sanitize_text_field($filters['change_type']);
        }
        
        if (!empty($filters['status'])) {
            $where_clauses[] = 'status = %s';
            $where_values[] = sanitize_text_field($filters['status']);
        }
        
        if (!empty($filters['post_id'])) {
            $where_clauses[] = 'target_post_id = %d';
            $where_values[] = intval($filters['post_id']);
        }
        
        if (!empty($filters['date_from'])) {
            $where_clauses[] = 'applied_at >= %s';
            $where_values[] = sanitize_text_field($filters['date_from']);
        }
        
        if (!empty($filters['date_to'])) {
            $where_clauses[] = 'applied_at <= %s';
            $where_values[] = sanitize_text_field($filters['date_to']);
        }
        
        $where_sql = '';
        if (!empty($where_clauses)) {
            $where_sql = 'WHERE ' . implode(' AND ', $where_clauses);
        }
        
        // Get total count
        $count_sql = "SELECT COUNT(*) FROM {$this->table_name} {$where_sql}";
        if (!empty($where_values)) {
            $count_sql = $wpdb->prepare($count_sql, $where_values);
        }
        $total = $wpdb->get_var($count_sql);
        
        // Get logs
        $sql = "SELECT * FROM {$this->table_name} {$where_sql} ORDER BY applied_at DESC LIMIT %d OFFSET %d";
        $where_values[] = $per_page;
        $where_values[] = $offset;
        $sql = $wpdb->prepare($sql, $where_values);
        
        $logs = $wpdb->get_results($sql, ARRAY_A);
        
        // Enhance logs with post information
        $logs = $this->enhance_logs_with_post_info($logs);
        
        return array(
            'logs' => $logs,
            'total' => intval($total),
            'page' => $page,
            'per_page' => $per_page,
            'total_pages' => ceil($total / $per_page),
        );
    }
    
    /**
     * Get recent changes
     */
    public function get_recent_changes($limit = 10) {
        global $wpdb;
        
        $limit = max(1, min(100, intval($limit)));
        
        $sql = $wpdb->prepare(
            "SELECT * FROM {$this->table_name} ORDER BY applied_at DESC LIMIT %d",
            $limit
        );
        
        $logs = $wpdb->get_results($sql, ARRAY_A);
        
        return $this->enhance_logs_with_post_info($logs);
    }
    
    /**
     * Get changes by post ID
     */
    public function get_changes_by_post($post_id, $limit = 20) {
        global $wpdb;
        
        $post_id = intval($post_id);
        $limit = max(1, min(100, intval($limit)));
        
        $sql = $wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE target_post_id = %d ORDER BY applied_at DESC LIMIT %d",
            $post_id,
            $limit
        );
        
        $logs = $wpdb->get_results($sql, ARRAY_A);
        
        return $this->enhance_logs_with_post_info($logs);
    }
    
    /**
     * Get changes by type
     */
    public function get_changes_by_type($change_type, $limit = 20) {
        global $wpdb;
        
        $change_type = sanitize_text_field($change_type);
        $limit = max(1, min(100, intval($limit)));
        
        $sql = $wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE change_type = %s ORDER BY applied_at DESC LIMIT %d",
            $change_type,
            $limit
        );
        
        $logs = $wpdb->get_results($sql, ARRAY_A);
        
        return $this->enhance_logs_with_post_info($logs);
    }
    
    /**
     * Get statistics
     */
    public function get_statistics($days = 30) {
        global $wpdb;
        
        $days = max(1, intval($days));
        $date_from = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        
        $sql = $wpdb->prepare(
            "SELECT 
                change_type,
                status,
                COUNT(*) as count
            FROM {$this->table_name} 
            WHERE applied_at >= %s 
            GROUP BY change_type, status 
            ORDER BY change_type, status",
            $date_from
        );
        
        $results = $wpdb->get_results($sql, ARRAY_A);
        
        $stats = array(
            'total_changes' => 0,
            'successful_changes' => 0,
            'failed_changes' => 0,
            'by_type' => array(),
            'by_status' => array(),
        );
        
        foreach ($results as $row) {
            $stats['total_changes'] += $row['count'];
            
            if ($row['status'] === 'success') {
                $stats['successful_changes'] += $row['count'];
            } else {
                $stats['failed_changes'] += $row['count'];
            }
            
            // Group by type
            if (!isset($stats['by_type'][$row['change_type']])) {
                $stats['by_type'][$row['change_type']] = 0;
            }
            $stats['by_type'][$row['change_type']] += $row['count'];
            
            // Group by status
            if (!isset($stats['by_status'][$row['status']])) {
                $stats['by_status'][$row['status']] = 0;
            }
            $stats['by_status'][$row['status']] += $row['count'];
        }
        
        return $stats;
    }
    
    /**
     * Update log status
     */
    public function update_log_status($change_id, $status, $message = '') {
        global $wpdb;
        
        $change_id = sanitize_text_field($change_id);
        $status = sanitize_text_field($status);
        $message = sanitize_textarea_field($message);
        
        $result = $wpdb->update(
            $this->table_name,
            array(
                'status' => $status,
                'message' => $message,
            ),
            array('change_id' => $change_id),
            array('%s', '%s'),
            array('%s')
        );
        
        return $result !== false;
    }
    
    /**
     * Delete old logs
     */
    public function cleanup_old_logs($days = 90) {
        global $wpdb;
        
        $days = max(1, intval($days));
        $date_before = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        
        $sql = $wpdb->prepare(
            "DELETE FROM {$this->table_name} WHERE applied_at < %s",
            $date_before
        );
        
        $result = $wpdb->query($sql);
        
        return $result !== false;
    }
    
    /**
     * Enhance logs with post information
     */
    private function enhance_logs_with_post_info($logs) {
        if (empty($logs)) {
            return $logs;
        }
        
        $post_ids = array_unique(array_column($logs, 'target_post_id'));
        $posts = get_posts(array(
            'post__in' => $post_ids,
            'post_type' => 'any',
            'posts_per_page' => -1,
        ));
        
        $posts_by_id = array();
        foreach ($posts as $post) {
            $posts_by_id[$post->ID] = $post;
        }
        
        foreach ($logs as &$log) {
            $post_id = $log['target_post_id'];
            
            if (isset($posts_by_id[$post_id])) {
                $post = $posts_by_id[$post_id];
                $log['post_title'] = $post->post_title;
                $log['post_type'] = $post->post_type;
                $log['post_url'] = get_permalink($post_id);
                $log['edit_url'] = get_edit_post_link($post_id, 'raw');
            } else {
                $log['post_title'] = __('Post not found', 'ai-seo-optimizer');
                $log['post_type'] = '';
                $log['post_url'] = '';
                $log['edit_url'] = '';
            }
            
            // Format applied_at date
            $log['applied_at_formatted'] = date_i18n(
                get_option('date_format') . ' ' . get_option('time_format'),
                strtotime($log['applied_at'])
            );
            
            // Truncate long values for display
            if (strlen($log['old_value']) > 100) {
                $log['old_value_preview'] = substr($log['old_value'], 0, 100) . '...';
            } else {
                $log['old_value_preview'] = $log['old_value'];
            }
            
            if (strlen($log['new_value']) > 100) {
                $log['new_value_preview'] = substr($log['new_value'], 0, 100) . '...';
            } else {
                $log['new_value_preview'] = $log['new_value'];
            }
        }
        
        return $logs;
    }
    
    /**
     * Get change types
     */
    public function get_change_types() {
        global $wpdb;
        
        $sql = "SELECT DISTINCT change_type FROM {$this->table_name} ORDER BY change_type";
        $types = $wpdb->get_col($sql);
        
        $type_labels = array(
            'post_title' => __('Post Title', 'ai-seo-optimizer'),
            'post_content' => __('Post Content', 'ai-seo-optimizer'),
            'post_excerpt' => __('Post Excerpt', 'ai-seo-optimizer'),
            'meta_description' => __('Meta Description', 'ai-seo-optimizer'),
            'meta_title' => __('Meta Title', 'ai-seo-optimizer'),
            'focus_keyword' => __('Focus Keyword', 'ai-seo-optimizer'),
            'image_alt' => __('Image Alt Text', 'ai-seo-optimizer'),
            'schema_markup' => __('Schema Markup', 'ai-seo-optimizer'),
            'custom_field' => __('Custom Field', 'ai-seo-optimizer'),
        );
        
        $result = array();
        foreach ($types as $type) {
            $result[$type] = isset($type_labels[$type]) ? $type_labels[$type] : ucfirst(str_replace('_', ' ', $type));
        }
        
        return $result;
    }
    
    /**
     * Get statuses
     */
    public function get_statuses() {
        global $wpdb;
        
        $sql = "SELECT DISTINCT status FROM {$this->table_name} ORDER BY status";
        $statuses = $wpdb->get_col($sql);
        
        $status_labels = array(
            'pending' => __('Pending', 'ai-seo-optimizer'),
            'success' => __('Success', 'ai-seo-optimizer'),
            'failed' => __('Failed', 'ai-seo-optimizer'),
            'skipped' => __('Skipped', 'ai-seo-optimizer'),
        );
        
        $result = array();
        foreach ($statuses as $status) {
            $result[$status] = isset($status_labels[$status]) ? $status_labels[$status] : ucfirst($status);
        }
        
        return $result;
    }
    
    /**
     * Export logs to CSV
     */
    public function export_logs_csv($filters = array()) {
        $logs_data = $this->get_logs(1, 10000, $filters); // Get all logs
        $logs = $logs_data['logs'];
        
        if (empty($logs)) {
            return false;
        }
        
        $filename = 'ai-seo-audit-logs-' . date('Y-m-d-H-i-s') . '.csv';
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        $output = fopen('php://output', 'w');
        
        // CSV headers
        $headers = array(
            'ID',
            'Change ID',
            'Change Type',
            'Post ID',
            'Post Title',
            'Post Type',
            'Old Value',
            'New Value',
            'Status',
            'Message',
            'Applied At',
        );
        
        fputcsv($output, $headers);
        
        // CSV data
        foreach ($logs as $log) {
            $row = array(
                $log['id'],
                $log['change_id'],
                $log['change_type'],
                $log['target_post_id'],
                $log['post_title'],
                $log['post_type'],
                $log['old_value'],
                $log['new_value'],
                $log['status'],
                $log['message'],
                $log['applied_at'],
            );
            
            fputcsv($output, $row);
        }
        
        fclose($output);
        exit;
    }
}
