<?php
/**
 * Plugin Name: AI SEO Optimizer - Enhanced Automation Platform
 * Plugin URI: https://example.com/ai-seo-optimizer
 * Description: Traditional SEO (Google/Bing) AI Automation Platform with CMS connectors, automated implementation, backup/rollback system, and approval workflow.
 * Version: 2.0.0
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
define('AI_SEO_OPTIMIZER_VERSION', '2.0.0');
define('AI_SEO_OPTIMIZER_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('AI_SEO_OPTIMIZER_PLUGIN_URL', plugin_dir_url(__FILE__));
define('AI_SEO_OPTIMIZER_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Enable test mode for development (remove this in production)
if (!defined('AI_SEO_TEST_MODE')) {
    define('AI_SEO_TEST_MODE', true);
}

/**
 * Enhanced AI SEO Optimizer Plugin Class
 * Traditional SEO (Google/Bing) AI Automation Platform
 */
class AI_SEO_Optimizer_Enhanced {
    
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
     * Backup Manager instance
     */
    public $backup_manager;
    
    /**
     * Approval Workflow instance
     */
    public $approval_workflow;
    
    /**
     * Automation Engine instance
     */
    public $automation_engine;
    
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
        
        // AJAX handlers
        add_action('wp_ajax_ai_seo_scan', array($this, 'ajax_scan'));
        add_action('wp_ajax_ai_seo_apply_changes', array($this, 'ajax_apply_changes'));
        add_action('wp_ajax_ai_seo_get_logs', array($this, 'ajax_get_logs'));
        add_action('wp_ajax_ai_seo_get_dashboard_data', array($this, 'ajax_get_dashboard_data'));
        add_action('wp_ajax_ai_seo_save_settings', array($this, 'ajax_save_settings'));
        add_action('wp_ajax_ai_seo_test_connection', array($this, 'ajax_test_connection'));
        
        // Enhanced automation handlers
        add_action('wp_ajax_ai_seo_approve_change', array($this, 'ajax_approve_change'));
        add_action('wp_ajax_ai_seo_reject_change', array($this, 'ajax_reject_change'));
        add_action('wp_ajax_ai_seo_modify_suggestion', array($this, 'ajax_modify_suggestion'));
        add_action('wp_ajax_ai_seo_create_backup', array($this, 'ajax_create_backup'));
        add_action('wp_ajax_ai_seo_rollback_changes', array($this, 'ajax_rollback_changes'));
        add_action('wp_ajax_ai_seo_get_pending_changes', array($this, 'ajax_get_pending_changes'));
        add_action('wp_ajax_ai_seo_automate_implementation', array($this, 'ajax_automate_implementation'));
        
        // REST API routes
        add_action('rest_api_init', array($this, 'register_rest_routes'));
        
        // Cron jobs
        add_action('ai_seo_daily_scan', array($this, 'daily_scan'));
        add_action('ai_seo_automation_check', array($this, 'automation_check'));
        
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
            __('AI SEO Automation', 'ai-seo-optimizer'),
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
            __('Pending Changes', 'ai-seo-optimizer'),
            __('Pending Changes', 'ai-seo-optimizer'),
            'manage_options',
            'ai-seo-pending-changes',
            array($this, 'pending_changes_page')
        );
        
        add_submenu_page(
            'ai-seo-optimizer',
            __('Automation History', 'ai-seo-optimizer'),
            __('History', 'ai-seo-optimizer'),
            'manage_options',
            'ai-seo-history',
            array($this, 'history_page')
        );
        
        add_submenu_page(
            'ai-seo-optimizer',
            __('Backup & Rollback', 'ai-seo-optimizer'),
            __('Backups', 'ai-seo-optimizer'),
            'manage_options',
            'ai-seo-backups',
            array($this, 'backups_page')
        );
        
        add_submenu_page(
            'ai-seo-optimizer',
            __('Settings', 'ai-seo-optimizer'),
            __('Settings', 'ai-seo-optimizer'),
            'manage_options',
            'ai-seo-settings',
            array($this, 'settings_page')
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
                'approving' => __('Approving change...', 'ai-seo-optimizer'),
                'rejecting' => __('Rejecting change...', 'ai-seo-optimizer'),
                'creating_backup' => __('Creating backup...', 'ai-seo-optimizer'),
                'rolling_back' => __('Rolling back changes...', 'ai-seo-optimizer'),
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
     * Pending changes page
     */
    public function pending_changes_page() {
        include AI_SEO_OPTIMIZER_PLUGIN_DIR . 'templates/pending-changes.php';
    }
    
    /**
     * History page
     */
    public function history_page() {
        include AI_SEO_OPTIMIZER_PLUGIN_DIR . 'templates/history.php';
    }
    
    /**
     * Backups page
     */
    public function backups_page() {
        include AI_SEO_OPTIMIZER_PLUGIN_DIR . 'templates/backups.php';
    }
    
    /**
     * Settings page
     */
    public function settings_page() {
        include AI_SEO_OPTIMIZER_PLUGIN_DIR . 'templates/settings-page.php';
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Create database tables
        $this->audit_logger->create_table();
        $this->approval_workflow->create_tables();
        $this->backup_manager->create_tables();
        
        // Schedule cron jobs
        if (!wp_next_scheduled('ai_seo_daily_scan')) {
            wp_schedule_event(time(), 'daily', 'ai_seo_daily_scan');
        }
        
        if (!wp_next_scheduled('ai_seo_automation_check')) {
            wp_schedule_event(time(), 'hourly', 'ai_seo_automation_check');
        }
        
        // Set default options
        add_option('ai_seo_api_key', '');
        add_option('ai_seo_customer_id', '');
        add_option('ai_seo_api_url', 'https://api.example.com/v1');
        add_option('ai_seo_automation_enabled', false);
        add_option('ai_seo_auto_backup', true);
        add_option('ai_seo_approval_required', true);
        
        flush_rewrite_rules();
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Clear scheduled cron jobs
        wp_clear_scheduled_hook('ai_seo_daily_scan');
        wp_clear_scheduled_hook('ai_seo_automation_check');
        
        flush_rewrite_rules();
    }
}

// Initialize the plugin
function ai_seo_optimizer_enhanced_init() {
    global $ai_seo_optimizer_enhanced;
    $ai_seo_optimizer_enhanced = AI_SEO_Optimizer_Enhanced::get_instance();
    return $ai_seo_optimizer_enhanced;
}

// Start the plugin
add_action('plugins_loaded', 'ai_seo_optimizer_enhanced_init');
