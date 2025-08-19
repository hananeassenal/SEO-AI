<?php
/**
 * Plugin Name: AI SEO Optimizer
 * Plugin URI: https://example.com/ai-seo-optimizer
 * Description: Automated SEO content updates via AI recommendations with external API integration.
 * Version: 1.0.0
 * Author: AI SEO Team
 * Author URI: https://example.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: ai-seo-optimizer
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * Network: false
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('AI_SEO_OPTIMIZER_VERSION', '1.0.0');
define('AI_SEO_OPTIMIZER_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('AI_SEO_OPTIMIZER_PLUGIN_URL', plugin_dir_url(__FILE__));
define('AI_SEO_OPTIMIZER_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Enable test mode for development (remove this in production)
if (!defined('AI_SEO_TEST_MODE')) {
    define('AI_SEO_TEST_MODE', true);
}

/**
 * Main AI SEO Optimizer Plugin Class
 */
class AI_SEO_Optimizer {
    
    /**
     * Plugin instance
     */
    private static $instance = null;
    
    /**
     * API Handler instance
     */
    public $api_handler;
    
    /**
     * Content Updater instance
     */
    public $content_updater;
    
    /**
     * Audit Logger instance
     */
    public $audit_logger;
    
    /**
     * Get plugin instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->init_hooks();
        $this->load_dependencies();
    }
    
    /**
     * Initialize WordPress hooks
     */
    private function init_hooks() {
        add_action('init', array($this, 'init'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_action('wp_ajax_ai_seo_scan', array($this, 'ajax_scan'));
        add_action('wp_ajax_ai_seo_apply_changes', array($this, 'ajax_apply_changes'));
        add_action('wp_ajax_ai_seo_get_logs', array($this, 'ajax_get_logs'));
        add_action('wp_ajax_ai_seo_get_dashboard_data', array($this, 'ajax_get_dashboard_data'));
        add_action('wp_ajax_ai_seo_save_settings', array($this, 'ajax_save_settings'));
        add_action('wp_ajax_ai_seo_test_connection', array($this, 'ajax_test_connection'));
        add_action('wp_ajax_ai_seo_approve_change', array($this, 'ajax_approve_change'));
        add_action('wp_ajax_ai_seo_reject_change', array($this, 'ajax_reject_change'));
        add_action('wp_ajax_ai_seo_modify_suggestion', array($this, 'ajax_modify_suggestion'));
        add_action('wp_ajax_ai_seo_create_backup', array($this, 'ajax_create_backup'));
        add_action('wp_ajax_ai_seo_rollback_changes', array($this, 'ajax_rollback_changes'));
        add_action('wp_ajax_ai_seo_get_pending_changes', array($this, 'ajax_get_pending_changes'));
        add_action('wp_ajax_ai_seo_automate_implementation', array($this, 'ajax_automate_implementation'));
        add_action('rest_api_init', array($this, 'register_rest_routes'));
        add_action('ai_seo_daily_scan', array($this, 'daily_scan'));
        add_action('init', array($this, 'add_cors_headers'));
        
        // Activation and deactivation hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    /**
     * Load plugin dependencies
     */
    private function load_dependencies() {
        require_once AI_SEO_OPTIMIZER_PLUGIN_DIR . 'includes/class-api-handler.php';
        require_once AI_SEO_OPTIMIZER_PLUGIN_DIR . 'includes/class-content-updater.php';
        require_once AI_SEO_OPTIMIZER_PLUGIN_DIR . 'includes/class-audit-logger.php';
        require_once AI_SEO_OPTIMIZER_PLUGIN_DIR . 'includes/class-backup-manager.php';
        require_once AI_SEO_OPTIMIZER_PLUGIN_DIR . 'includes/class-approval-workflow.php';
        require_once AI_SEO_OPTIMIZER_PLUGIN_DIR . 'includes/class-automation-engine.php';
        
        $this->api_handler = new AI_SEO_API_Handler();
        $this->content_updater = new AI_SEO_Content_Updater();
        $this->audit_logger = new AI_SEO_Audit_Logger();
        $this->backup_manager = new AI_SEO_Backup_Manager();
        $this->approval_workflow = new AI_SEO_Approval_Workflow();
        $this->automation_engine = new AI_SEO_Automation_Engine();
    }
    
    /**
     * Initialize plugin
     */
    public function init() {
        load_plugin_textdomain('ai-seo-optimizer', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            __('AI SEO Optimizer', 'ai-seo-optimizer'),
            __('AI SEO', 'ai-seo-optimizer'),
            'manage_options',
            'ai-seo-optimizer',
            array($this, 'admin_dashboard_page'),
            'dashicons-chart-line',
            30
        );
        
        add_submenu_page(
            'ai-seo-optimizer',
            __('Dashboard', 'ai-seo-optimizer'),
            __('Dashboard', 'ai-seo-optimizer'),
            'manage_options',
            'ai-seo-optimizer',
            array($this, 'admin_dashboard_page')
        );
        
        add_submenu_page(
            'ai-seo-optimizer',
            __('Settings', 'ai-seo-optimizer'),
            __('Settings', 'ai-seo-optimizer'),
            'manage_options',
            'ai-seo-settings',
            array($this, 'settings_page')
        );
        
        add_submenu_page(
            'ai-seo-optimizer',
            __('Pending Changes', 'ai-seo-optimizer'),
            __('Pending Changes', 'ai-seo-optimizer'),
            'manage_options',
            'ai-seo-pending-changes',
            array($this, 'pending_changes_page')
        );
    }
    
    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        if (strpos($hook, 'ai-seo') === false) {
            return;
        }
        
        wp_enqueue_script(
            'ai-seo-admin',
            AI_SEO_OPTIMIZER_PLUGIN_URL . 'assets/admin.js',
            array('jquery', 'wp-api-fetch'),
            AI_SEO_OPTIMIZER_VERSION,
            true
        );
        
        wp_enqueue_style(
            'ai-seo-admin',
            AI_SEO_OPTIMIZER_PLUGIN_URL . 'assets/admin.css',
            array(),
            AI_SEO_OPTIMIZER_VERSION
        );
        
        wp_localize_script('ai-seo-admin', 'aiSeoAjax', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ai_seo_nonce'),
            'strings' => array(
                'scanning' => __('Scanning...', 'ai-seo-optimizer'),
                'applying' => __('Applying changes...', 'ai-seo-optimizer'),
                'success' => __('Success!', 'ai-seo-optimizer'),
                'error' => __('Error occurred', 'ai-seo-optimizer'),
            )
        ));
    }
    
    /**
     * Admin dashboard page
     */
    public function admin_dashboard_page() {
        include AI_SEO_OPTIMIZER_PLUGIN_DIR . 'templates/admin-dashboard.php';
    }
    
    /**
     * Settings page
     */
    public function settings_page() {
        include AI_SEO_OPTIMIZER_PLUGIN_DIR . 'templates/settings-page.php';
    }
    
    /**
     * Pending changes page
     */
    public function pending_changes_page() {
        include AI_SEO_OPTIMIZER_PLUGIN_DIR . 'templates/pending-changes.php';
    }
    
    /**
     * Register REST API routes
     */
    public function register_rest_routes() {
        register_rest_route('ai-seo/v1', '/apply-changes', array(
            'methods' => 'POST',
            'callback' => array($this, 'rest_apply_changes'),
            'permission_callback' => array($this, 'rest_permission_callback'),
        ));
        
        register_rest_route('ai-seo/v1', '/scan', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_scan'),
            'permission_callback' => array($this, 'rest_permission_callback'),
        ));
        
        register_rest_route('ai-seo/v1', '/logs', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_logs'),
            'permission_callback' => array($this, 'rest_permission_callback'),
        ));
        
        register_rest_route('ai-seo/v1', '/dashboard-data', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_dashboard_data'),
            'permission_callback' => array($this, 'rest_permission_callback'),
        ));
        
        register_rest_route('ai-seo/v1', '/health', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_health'),
            'permission_callback' => array($this, 'rest_permission_callback'),
        ));
    }
    
    /**
     * REST API permission callback
     */
    public function rest_permission_callback($request) {
        $api_key = $request->get_header('X-API-Key');
        $stored_key = get_option('ai_seo_api_key', '');
        
        if (empty($api_key) || $api_key !== $stored_key) {
            return new WP_Error('rest_forbidden', __('Invalid API key', 'ai-seo-optimizer'), array('status' => 403));
        }
        
        return true;
    }
    
    /**
     * REST API: Apply changes
     */
    public function rest_apply_changes($request) {
        $params = $request->get_params();
        
        if (empty($params['changes'])) {
            return new WP_Error('invalid_request', __('No changes provided', 'ai-seo-optimizer'), array('status' => 400));
        }
        
        $result = $this->content_updater->apply_changes($params['changes']);
        
        return rest_ensure_response($result);
    }
    
    /**
     * REST API: Scan website
     */
    public function rest_scan($request) {
        $scan_data = $this->api_handler->prepare_scan_data();
        return rest_ensure_response($scan_data);
    }
    
    /**
     * REST API: Get logs
     */
    public function rest_logs($request) {
        $page = $request->get_param('page') ?: 1;
        $per_page = $request->get_param('per_page') ?: 20;
        
        $logs = $this->audit_logger->get_logs($page, $per_page);
        return rest_ensure_response($logs);
    }
    
    /**
     * REST API: Get dashboard data
     */
    public function rest_dashboard_data($request) {
        $data = array(
            'seo_score' => $this->calculate_seo_score(),
            'recent_changes' => $this->audit_logger->get_recent_changes(5),
            'connection_status' => $this->api_handler->check_connection(),
            'last_scan' => get_option('ai_seo_last_scan', ''),
        );
        
        return rest_ensure_response($data);
    }
    
    /**
     * REST API: Health check
     */
    public function rest_health($request) {
        $data = array(
            'status' => 'healthy',
            'plugin_version' => AI_SEO_OPTIMIZER_VERSION,
            'wordpress_version' => get_bloginfo('version'),
            'php_version' => PHP_VERSION,
            'timestamp' => current_time('mysql'),
            'api_key_configured' => !empty(get_option('ai_seo_api_key', '')),
            'connection_status' => $this->api_handler->check_connection()
        );
        
        return rest_ensure_response($data);
    }
    
    /**
     * AJAX: Scan website
     */
    public function ajax_scan() {
        check_ajax_referer('ai_seo_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions', 'ai-seo-optimizer'));
        }
        
        $scan_data = $this->api_handler->prepare_scan_data();
        $result = $this->api_handler->send_scan_data($scan_data);
        
        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        } else {
            update_option('ai_seo_last_scan', current_time('mysql'));
            wp_send_json_success($result);
        }
    }
    
    /**
     * AJAX: Apply changes
     */
    public function ajax_apply_changes() {
        check_ajax_referer('ai_seo_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions', 'ai-seo-optimizer'));
        }
        
        // Get changes data from POST
        $changes_json = isset($_POST['changes']) ? $_POST['changes'] : '';
        
        // Debug: Log what we received
        error_log('AI SEO DEBUG: Received changes_json: ' . $changes_json);
        error_log('AI SEO DEBUG: POST data: ' . print_r($_POST, true));
        
        if (empty($changes_json)) {
            wp_send_json_error(__('No changes data provided', 'ai-seo-optimizer'));
        }
        
        // Clean the JSON string to handle potential WordPress escaping
        $cleaned_json = stripslashes($changes_json);
        $cleaned_json = html_entity_decode($cleaned_json, ENT_QUOTES, 'UTF-8');
        
        // Try to decode JSON data
        $changes = json_decode($cleaned_json, true);
        $json_error = json_last_error();
        
        error_log('AI SEO DEBUG: JSON decode error: ' . json_last_error_msg());
        error_log('AI SEO DEBUG: Decoded changes: ' . print_r($changes, true));
        
        if ($json_error !== JSON_ERROR_NONE) {
            // Try with original JSON as fallback
            $changes = json_decode($changes_json, true);
            $json_error = json_last_error();
            
            if ($json_error !== JSON_ERROR_NONE) {
                error_log('AI SEO DEBUG: Still failed after cleaning: ' . json_last_error_msg());
                wp_send_json_error(sprintf(__('Invalid JSON data provided: %s', 'ai-seo-optimizer'), json_last_error_msg()));
            }
        }
        
        if (empty($changes) || !is_array($changes)) {
            wp_send_json_error(__('No valid changes provided', 'ai-seo-optimizer'));
        }
        
        $result = $this->content_updater->apply_changes($changes);
        
        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        } else {
            wp_send_json_success($result);
        }
    }
    
    /**
     * AJAX: Get logs
     */
    public function ajax_get_logs() {
        check_ajax_referer('ai_seo_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions', 'ai-seo-optimizer'));
        }
        
        $page = intval($_GET['page']) ?: 1;
        $per_page = intval($_GET['per_page']) ?: 20;
        
        $logs = $this->audit_logger->get_logs($page, $per_page);
        wp_send_json_success($logs);
    }
    
    /**
     * AJAX: Get dashboard data
     */
    public function ajax_get_dashboard_data() {
        check_ajax_referer('ai_seo_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions', 'ai-seo-optimizer'));
        }
        
        $data = array(
            'seo_score' => $this->calculate_seo_score(),
            'recent_changes' => $this->audit_logger->get_recent_changes(5),
            'connection_status' => $this->api_handler->check_connection(),
            'last_scan' => get_option('ai_seo_last_scan', ''),
        );
        
        wp_send_json_success($data);
    }
    
    /**
     * Daily scan cron job
     */
    public function daily_scan() {
        $scan_data = $this->api_handler->prepare_scan_data();
        $result = $this->api_handler->send_scan_data($scan_data);
        
        if (!is_wp_error($result)) {
            update_option('ai_seo_last_scan', current_time('mysql'));
        }
    }
    
    /**
     * AJAX: Save settings
     */
    public function ajax_save_settings() {
        check_ajax_referer('ai_seo_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions', 'ai-seo-optimizer'));
        }
        
        // Save API settings
        if (isset($_POST['ai_seo_api_url'])) {
            update_option('ai_seo_api_url', sanitize_url($_POST['ai_seo_api_url']));
        }
        
        if (isset($_POST['ai_seo_api_key'])) {
            update_option('ai_seo_api_key', sanitize_text_field($_POST['ai_seo_api_key']));
        }
        
        if (isset($_POST['ai_seo_customer_id'])) {
            update_option('ai_seo_customer_id', sanitize_text_field($_POST['ai_seo_customer_id']));
        }
        
        wp_send_json_success(__('Settings saved successfully', 'ai-seo-optimizer'));
    }
    
    /**
     * AJAX: Test connection
     */
    public function ajax_test_connection() {
        check_ajax_referer('ai_seo_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions', 'ai-seo-optimizer'));
        }
        
        $connection_status = $this->api_handler->check_connection();
        
        if ($connection_status) {
            wp_send_json_success(__('Connection successful', 'ai-seo-optimizer'));
        } else {
            wp_send_json_error(__('Connection failed', 'ai-seo-optimizer'));
        }
    }
    
    /**
     * Calculate SEO score
     */
    private function calculate_seo_score() {
        // Simple SEO score calculation based on various factors
        $score = 0;
        $total_factors = 0;
        
        // Check for Yoast SEO
        if (is_plugin_active('wordpress-seo/wp-seo.php')) {
            $score += 20;
            $total_factors++;
        }
        
        // Check for RankMath
        if (is_plugin_active('seo-by-rank-math/rank-math.php')) {
            $score += 20;
            $total_factors++;
        }
        
        // Check for All in One SEO
        if (is_plugin_active('all-in-one-seo-pack/all_in_one_seo_pack.php')) {
            $score += 20;
            $total_factors++;
        }
        
        // Check recent changes
        $recent_changes = $this->audit_logger->get_recent_changes(10);
        if (!empty($recent_changes)) {
            $score += 30;
            $total_factors++;
        }
        
        // Check connection status
        if ($this->api_handler->check_connection()) {
            $score += 30;
            $total_factors++;
        }
        
        return $total_factors > 0 ? round($score / $total_factors) : 0;
    }
    
    /**
     * AJAX: Approve change
     */
    public function ajax_approve_change() {
        check_ajax_referer('ai_seo_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions', 'ai-seo-optimizer'));
        }
        
        $recommendation_id = intval($_POST['recommendation_id']);
        $review_notes = sanitize_textarea_field($_POST['review_notes'] ?? '');
        
        $result = $this->approval_workflow->approve_change($recommendation_id, $review_notes);
        
        if ($result) {
            wp_send_json_success(__('Change approved successfully', 'ai-seo-optimizer'));
        } else {
            wp_send_json_error(__('Failed to approve change', 'ai-seo-optimizer'));
        }
    }
    
    /**
     * AJAX: Reject change
     */
    public function ajax_reject_change() {
        check_ajax_referer('ai_seo_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions', 'ai-seo-optimizer'));
        }
        
        $recommendation_id = intval($_POST['recommendation_id']);
        $reason = sanitize_textarea_field($_POST['reason'] ?? '');
        
        $result = $this->approval_workflow->reject_change($recommendation_id, $reason);
        
        if ($result) {
            wp_send_json_success(__('Change rejected successfully', 'ai-seo-optimizer'));
        } else {
            wp_send_json_error(__('Failed to reject change', 'ai-seo-optimizer'));
        }
    }
    
    /**
     * AJAX: Modify suggestion
     */
    public function ajax_modify_suggestion() {
        check_ajax_referer('ai_seo_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions', 'ai-seo-optimizer'));
        }
        
        $recommendation_id = intval($_POST['recommendation_id']);
        $modifications = array(
            'title' => sanitize_text_field($_POST['title'] ?? ''),
            'content' => wp_kses_post($_POST['content'] ?? ''),
            'meta_description' => sanitize_textarea_field($_POST['meta_description'] ?? ''),
            'focus_keyword' => sanitize_text_field($_POST['focus_keyword'] ?? '')
        );
        
        $result = $this->approval_workflow->modify_suggestion($recommendation_id, $modifications);
        
        if ($result) {
            wp_send_json_success(__('Suggestion modified successfully', 'ai-seo-optimizer'));
        } else {
            wp_send_json_error(__('Failed to modify suggestion', 'ai-seo-optimizer'));
        }
    }
    
    /**
     * AJAX: Create backup
     */
    public function ajax_create_backup() {
        check_ajax_referer('ai_seo_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions', 'ai-seo-optimizer'));
        }
        
        $type = sanitize_text_field($_POST['type'] ?? 'content');
        $description = sanitize_textarea_field($_POST['description'] ?? '');
        
        $backup_id = $this->backup_manager->create_backup($type, $description);
        
        if ($backup_id) {
            wp_send_json_success(array(
                'backup_id' => $backup_id,
                'message' => __('Backup created successfully', 'ai-seo-optimizer')
            ));
        } else {
            wp_send_json_error(__('Failed to create backup', 'ai-seo-optimizer'));
        }
    }
    
    /**
     * AJAX: Rollback changes
     */
    public function ajax_rollback_changes() {
        check_ajax_referer('ai_seo_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions', 'ai-seo-optimizer'));
        }
        
        $backup_id = intval($_POST['backup_id']);
        
        $result = $this->backup_manager->rollback_to_backup($backup_id);
        
        if ($result) {
            wp_send_json_success(__('Rollback completed successfully', 'ai-seo-optimizer'));
        } else {
            wp_send_json_error(__('Failed to rollback changes', 'ai-seo-optimizer'));
        }
    }
    
    /**
     * AJAX: Get pending changes
     */
    public function ajax_get_pending_changes() {
        check_ajax_referer('ai_seo_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions', 'ai-seo-optimizer'));
        }
        
        $limit = intval($_POST['limit'] ?? 20);
        $offset = intval($_POST['offset'] ?? 0);
        
        $changes = $this->approval_workflow->get_pending_changes($limit, $offset);
        
        wp_send_json_success($changes);
    }
    
    /**
     * AJAX: Automate implementation
     */
    public function ajax_automate_implementation() {
        check_ajax_referer('ai_seo_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions', 'ai-seo-optimizer'));
        }
        
        $change_ids = array_map('intval', $_POST['change_ids'] ?? array());
        
        if (empty($change_ids)) {
            wp_send_json_error(__('No changes selected for implementation', 'ai-seo-optimizer'));
        }
        
        $results = $this->automation_engine->implement_approved_changes($change_ids);
        
        wp_send_json_success(array(
            'results' => $results,
            'message' => __('Implementation completed', 'ai-seo-optimizer')
        ));
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Create database tables
        $this->audit_logger->create_table();
        $this->backup_manager->create_tables();
        $this->approval_workflow->create_tables();
        
        // Schedule daily cron job
        if (!wp_next_scheduled('ai_seo_daily_scan')) {
            wp_schedule_event(time(), 'daily', 'ai_seo_daily_scan');
        }
        
        // Set default options
        add_option('ai_seo_api_key', '');
        add_option('ai_seo_customer_id', '');
        add_option('ai_seo_api_url', 'https://api.example.com/v1');
        add_option('ai_seo_auto_apply', false);
        
        flush_rewrite_rules();
    }
    
    /**
     * Add CORS headers for API access
     */
    public function add_cors_headers() {
        // Only add headers for REST API requests
        if (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/wp-json/ai-seo/') !== false) {
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type, X-API-Key, X-Customer-ID');
            header('Access-Control-Max-Age: 86400');
            
            // Handle preflight OPTIONS requests
            if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
                http_response_code(200);
                exit;
            }
        }
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Clear scheduled cron job
        wp_clear_scheduled_hook('ai_seo_daily_scan');
        
        flush_rewrite_rules();
    }
}

// Initialize the plugin
function ai_seo_optimizer_init() {
    global $ai_seo_optimizer;
    $ai_seo_optimizer = AI_SEO_Optimizer::get_instance();
    return $ai_seo_optimizer;
}

// Start the plugin
add_action('plugins_loaded', 'ai_seo_optimizer_init');
