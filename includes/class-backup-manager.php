<?php
/**
 * AI SEO Backup Manager Class
 * Handles automatic backups and rollback functionality for the AI SEO Automation Platform
 */

class AI_SEO_Backup_Manager {
    
    /**
     * Database table name
     */
    private $table_name;
    
    /**
     * Constructor
     */
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'ai_seo_backups';
    }
    
    /**
     * Create database table
     */
    public function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE {$this->table_name} (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            backup_name varchar(255) NOT NULL,
            backup_type varchar(50) NOT NULL,
            backup_data longtext NOT NULL,
            backup_size int(11) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            description text,
            status varchar(20) DEFAULT 'active',
            PRIMARY KEY (id),
            KEY backup_type (backup_type),
            KEY created_at (created_at),
            KEY status (status)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Create a backup before making changes
     */
    public function create_backup($type = 'content', $description = '') {
        try {
            // Validate backup type
            $valid_types = array('content', 'settings', 'full');
            if (!in_array($type, $valid_types)) {
                return new WP_Error('invalid_backup_type', 'Invalid backup type specified');
            }
            
            // Generate backup data based on type
            $backup_data = $this->generate_backup_data($type);
            
            if (is_wp_error($backup_data)) {
                return $backup_data;
            }
            
            // Create backup name
            $backup_name = 'ai_seo_backup_' . $type . '_' . date('Y-m-d_H-i-s');
            
            // Calculate backup size
            $backup_size = strlen(serialize($backup_data));
            
            // Insert backup into database
            global $wpdb;
            
            $result = $wpdb->insert(
                $this->table_name,
                array(
                    'backup_name' => $backup_name,
                    'backup_type' => $type,
                    'backup_data' => serialize($backup_data),
                    'backup_size' => $backup_size,
                    'description' => $description,
                    'status' => 'active'
                ),
                array('%s', '%s', '%s', '%d', '%s', '%s')
            );
            
            if ($result === false) {
                return new WP_Error('backup_failed', 'Failed to create backup in database');
            }
            
            $backup_id = $wpdb->insert_id;
            
            // Log the backup creation
            $this->log_backup_creation($backup_id, $type, $description);
            
            return array(
                'success' => true,
                'backup_id' => $backup_id,
                'backup_name' => $backup_name,
                'backup_type' => $type,
                'backup_size' => $backup_size,
                'message' => sprintf('Backup created successfully: %s', $backup_name)
            );
            
        } catch (Exception $e) {
            return new WP_Error('backup_exception', 'Exception occurred during backup: ' . $e->getMessage());
        }
    }
    
    /**
     * Generate backup data based on type
     */
    private function generate_backup_data($type) {
        switch ($type) {
            case 'content':
                return $this->backup_content_data();
            case 'settings':
                return $this->backup_settings_data();
            case 'full':
                return $this->backup_full_data();
            default:
                return new WP_Error('invalid_type', 'Invalid backup type');
        }
    }
    
    /**
     * Backup content data (posts, pages, etc.)
     */
    private function backup_content_data() {
        global $wpdb;
        
        $backup_data = array();
        
        // Backup posts
        $posts = $wpdb->get_results("
            SELECT ID, post_title, post_content, post_excerpt, post_status, post_type, post_name
            FROM {$wpdb->posts}
            WHERE post_status IN ('publish', 'draft', 'pending')
            AND post_type IN ('post', 'page')
        ");
        
        $backup_data['posts'] = $posts;
        
        // Backup post meta
        $post_meta = $wpdb->get_results("
            SELECT post_id, meta_key, meta_value
            FROM {$wpdb->postmeta}
            WHERE meta_key LIKE '_yoast_%' OR meta_key LIKE '_ai_seo_%'
        ");
        
        $backup_data['post_meta'] = $post_meta;
        
        return $backup_data;
    }
    
    /**
     * Backup settings data
     */
    private function backup_settings_data() {
        $backup_data = array();
        
        // Backup AI SEO settings
        $ai_seo_settings = array(
            'ai_seo_api_key' => get_option('ai_seo_api_key'),
            'ai_seo_customer_id' => get_option('ai_seo_customer_id'),
            'ai_seo_api_url' => get_option('ai_seo_api_url'),
            'ai_seo_automation_enabled' => get_option('ai_seo_automation_enabled'),
            'ai_seo_auto_backup' => get_option('ai_seo_auto_backup'),
            'ai_seo_approval_required' => get_option('ai_seo_approval_required'),
        );
        
        $backup_data['ai_seo_settings'] = $ai_seo_settings;
        
        // Backup SEO plugin settings
        if (is_plugin_active('wordpress-seo/wp-seo.php')) {
            $backup_data['yoast_settings'] = get_option('wpseo_titles');
        }
        
        if (is_plugin_active('seo-by-rank-math/rank-math.php')) {
            $backup_data['rankmath_settings'] = get_option('rank_math_options');
        }
        
        return $backup_data;
    }
    
    /**
     * Backup full data (content + settings + additional data)
     */
    private function backup_full_data() {
        $backup_data = array();
        
        // Include content backup
        $content_backup = $this->backup_content_data();
        if (!is_wp_error($content_backup)) {
            $backup_data['content'] = $content_backup;
        }
        
        // Include settings backup
        $settings_backup = $this->backup_settings_data();
        if (!is_wp_error($settings_backup)) {
            $backup_data['settings'] = $settings_backup;
        }
        
        // Include additional data
        $backup_data['additional'] = array(
            'site_url' => get_site_url(),
            'home_url' => get_home_url(),
            'blog_name' => get_bloginfo('name'),
            'blog_description' => get_bloginfo('description'),
            'theme' => get_template(),
            'plugins' => get_option('active_plugins'),
            'backup_timestamp' => current_time('mysql'),
            'wordpress_version' => get_bloginfo('version'),
        );
        
        return $backup_data;
    }
    
    /**
     * Rollback to a specific backup
     */
    public function rollback_to_backup($backup_id) {
        try {
            // Get backup data
            $backup = $this->get_backup($backup_id);
            
            if (is_wp_error($backup)) {
                return $backup;
            }
            
            if (!$backup) {
                return new WP_Error('backup_not_found', 'Backup not found');
            }
            
            // Create a backup before rollback
            $pre_rollback_backup = $this->create_backup('full', 'Pre-rollback backup');
            
            // Perform rollback based on backup type
            $rollback_result = $this->perform_rollback($backup);
            
            if (is_wp_error($rollback_result)) {
                return $rollback_result;
            }
            
            // Log the rollback
            $this->log_rollback($backup_id, $backup['backup_type']);
            
            return array(
                'success' => true,
                'backup_id' => $backup_id,
                'backup_name' => $backup['backup_name'],
                'message' => sprintf('Successfully rolled back to backup: %s', $backup['backup_name'])
            );
            
        } catch (Exception $e) {
            return new WP_Error('rollback_exception', 'Exception occurred during rollback: ' . $e->getMessage());
        }
    }
    
    /**
     * Get backup by ID
     */
    public function get_backup($backup_id) {
        global $wpdb;
        
        $backup = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$this->table_name} WHERE id = %d AND status = 'active'", $backup_id),
            ARRAY_A
        );
        
        if ($backup) {
            $backup['backup_data'] = unserialize($backup['backup_data']);
        }
        
        return $backup;
    }
    
    /**
     * Perform the actual rollback
     */
    private function perform_rollback($backup) {
        $backup_data = $backup['backup_data'];
        $backup_type = $backup['backup_type'];
        
        switch ($backup_type) {
            case 'content':
                return $this->rollback_content($backup_data);
            case 'settings':
                return $this->rollback_settings($backup_data);
            case 'full':
                return $this->rollback_full($backup_data);
            default:
                return new WP_Error('invalid_rollback_type', 'Invalid rollback type');
        }
    }
    
    /**
     * Rollback content data
     */
    private function rollback_content($backup_data) {
        global $wpdb;
        
        try {
            // Rollback posts
            if (isset($backup_data['posts'])) {
                foreach ($backup_data['posts'] as $post) {
                    $wpdb->update(
                        $wpdb->posts,
                        array(
                            'post_title' => $post->post_title,
                            'post_content' => $post->post_content,
                            'post_excerpt' => $post->post_excerpt,
                            'post_status' => $post->post_status,
                            'post_name' => $post->post_name
                        ),
                        array('ID' => $post->ID),
                        array('%s', '%s', '%s', '%s', '%s'),
                        array('%d')
                    );
                }
            }
            
            // Rollback post meta
            if (isset($backup_data['post_meta'])) {
                foreach ($backup_data['post_meta'] as $meta) {
                    update_post_meta($meta->post_id, $meta->meta_key, $meta->meta_value);
                }
            }
            
            return true;
            
        } catch (Exception $e) {
            return new WP_Error('content_rollback_failed', 'Content rollback failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Rollback settings data
     */
    private function rollback_settings($backup_data) {
        try {
            // Rollback AI SEO settings
            if (isset($backup_data['ai_seo_settings'])) {
                foreach ($backup_data['ai_seo_settings'] as $key => $value) {
                    update_option($key, $value);
                }
            }
            
            // Rollback Yoast settings
            if (isset($backup_data['yoast_settings'])) {
                update_option('wpseo_titles', $backup_data['yoast_settings']);
            }
            
            // Rollback RankMath settings
            if (isset($backup_data['rankmath_settings'])) {
                update_option('rank_math_options', $backup_data['rankmath_settings']);
            }
            
            return true;
            
        } catch (Exception $e) {
            return new WP_Error('settings_rollback_failed', 'Settings rollback failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Rollback full data
     */
    private function rollback_full($backup_data) {
        // Rollback content
        if (isset($backup_data['content'])) {
            $content_result = $this->rollback_content($backup_data['content']);
            if (is_wp_error($content_result)) {
                return $content_result;
            }
        }
        
        // Rollback settings
        if (isset($backup_data['settings'])) {
            $settings_result = $this->rollback_settings($backup_data['settings']);
            if (is_wp_error($settings_result)) {
                return $settings_result;
            }
        }
        
        return true;
    }
    
    /**
     * Get all backups
     */
    public function get_backups($limit = 20, $offset = 0) {
        global $wpdb;
        
        $backups = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT id, backup_name, backup_type, backup_size, created_at, description, status 
                 FROM {$this->table_name} 
                 WHERE status = 'active' 
                 ORDER BY created_at DESC 
                 LIMIT %d OFFSET %d",
                $limit,
                $offset
            ),
            ARRAY_A
        );
        
        return $backups;
    }
    
    /**
     * Get backup count
     */
    public function get_backup_count() {
        global $wpdb;
        
        $count = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$this->table_name} WHERE status = 'active'"
        );
        
        return intval($count);
    }
    
    /**
     * Delete backup
     */
    public function delete_backup($backup_id) {
        global $wpdb;
        
        $result = $wpdb->update(
            $this->table_name,
            array('status' => 'deleted'),
            array('id' => $backup_id),
            array('%s'),
            array('%d')
        );
        
        return $result !== false;
    }
    
    /**
     * Clean up old backups
     */
    public function cleanup_old_backups($days_to_keep = 30) {
        global $wpdb;
        
        $cutoff_date = date('Y-m-d H:i:s', strtotime("-{$days_to_keep} days"));
        
        $result = $wpdb->update(
            $this->table_name,
            array('status' => 'deleted'),
            array(
                'created_at <' => $cutoff_date,
                'status' => 'active'
            ),
            array('%s'),
            array('%s', '%s')
        );
        
        return $result;
    }
    
    /**
     * Log backup creation
     */
    private function log_backup_creation($backup_id, $type, $description) {
        // This would integrate with the audit logger
        if (class_exists('AI_SEO_Audit_Logger')) {
            $audit_logger = new AI_SEO_Audit_Logger();
            $audit_logger->log_action(
                'backup_created',
                sprintf('Backup created: %s (%s)', $description ?: $type, $backup_id),
                array(
                    'backup_id' => $backup_id,
                    'backup_type' => $type,
                    'description' => $description
                )
            );
        }
    }
    
    /**
     * Log rollback
     */
    private function log_rollback($backup_id, $type) {
        // This would integrate with the audit logger
        if (class_exists('AI_SEO_Audit_Logger')) {
            $audit_logger = new AI_SEO_Audit_Logger();
            $audit_logger->log_action(
                'rollback_performed',
                sprintf('Rollback performed to backup %d (%s)', $backup_id, $type),
                array(
                    'backup_id' => $backup_id,
                    'backup_type' => $type
                )
            );
        }
    }
}
