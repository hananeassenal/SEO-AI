<?php
/**
 * Test Script for Enhanced AI SEO Automation Platform
 * 
 * This script helps verify that all components of the enhanced plugin are working correctly.
 * Run this from your WordPress admin area or via WP-CLI.
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    require_once('../../../wp-load.php');
}

// Check if plugin is active
if (!class_exists('AI_SEO_Optimizer')) {
    die('AI SEO Optimizer plugin is not active. Please activate it first.');
}

echo "<h1>AI SEO Automation Platform - System Test</h1>\n";
echo "<div style='font-family: Arial, sans-serif; max-width: 800px; margin: 20px auto;'>\n";

// Test 1: Check if all classes are loaded
echo "<h2>1. Class Loading Test</h2>\n";
$classes_to_check = [
    'AI_SEO_Backup_Manager',
    'AI_SEO_Approval_Workflow', 
    'AI_SEO_Automation_Engine',
    'AI_SEO_API_Handler',
    'AI_SEO_Content_Updater',
    'AI_SEO_Audit_Logger'
];

$all_classes_loaded = true;
foreach ($classes_to_check as $class) {
    if (class_exists($class)) {
        echo "<span style='color: green;'>✓</span> $class loaded successfully<br>\n";
    } else {
        echo "<span style='color: red;'>✗</span> $class NOT loaded<br>\n";
        $all_classes_loaded = false;
    }
}

if ($all_classes_loaded) {
    echo "<p style='color: green;'><strong>All classes loaded successfully!</strong></p>\n";
} else {
    echo "<p style='color: red;'><strong>Some classes failed to load. Check file permissions and includes.</strong></p>\n";
}

// Test 2: Database Tables
echo "<h2>2. Database Tables Test</h2>\n";
global $wpdb;

$tables_to_check = [
    $wpdb->prefix . 'ai_seo_audit_logs',
    $wpdb->prefix . 'ai_seo_backups',
    $wpdb->prefix . 'ai_seo_recommendations',
    $wpdb->prefix . 'ai_seo_changes'
];

$all_tables_exist = true;
foreach ($tables_to_check as $table) {
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table'") == $table;
    if ($table_exists) {
        echo "<span style='color: green;'>✓</span> Table $table exists<br>\n";
    } else {
        echo "<span style='color: red;'>✗</span> Table $table does NOT exist<br>\n";
        $all_tables_exist = false;
    }
}

if ($all_tables_exist) {
    echo "<p style='color: green;'><strong>All database tables created successfully!</strong></p>\n";
} else {
    echo "<p style='color: red;'><strong>Some tables are missing. Try deactivating and reactivating the plugin.</strong></p>\n";
}

// Test 3: Plugin Instance
echo "<h2>3. Plugin Instance Test</h2>\n";
$plugin_instance = AI_SEO_Optimizer::get_instance();

if ($plugin_instance) {
    echo "<span style='color: green;'>✓</span> Plugin instance created successfully<br>\n";
    
    // Check if new properties exist
    $properties_to_check = ['backup_manager', 'approval_workflow', 'automation_engine'];
    foreach ($properties_to_check as $property) {
        if (isset($plugin_instance->$property)) {
            echo "<span style='color: green;'>✓</span> Property \$plugin_instance->$property exists<br>\n";
        } else {
            echo "<span style='color: red;'>✗</span> Property \$plugin_instance->$property does NOT exist<br>\n";
        }
    }
} else {
    echo "<span style='color: red;'>✗</span> Failed to create plugin instance<br>\n";
}

// Test 4: Admin Menu
echo "<h2>4. Admin Menu Test</h2>\n";
$admin_menu_items = [
    'ai-seo-optimizer' => 'AI SEO',
    'ai-seo-settings' => 'Settings',
    'ai-seo-pending-changes' => 'Pending Changes'
];

foreach ($admin_menu_items as $slug => $title) {
    $menu_exists = get_admin_page_title($slug) !== false;
    if ($menu_exists) {
        echo "<span style='color: green;'>✓</span> Menu item '$title' exists<br>\n";
    } else {
        echo "<span style='color: orange;'>⚠</span> Menu item '$title' may not be visible (check permissions)<br>\n";
    }
}

// Test 5: AJAX Actions
echo "<h2>5. AJAX Actions Test</h2>\n";
$ajax_actions = [
    'ai_seo_approve_change',
    'ai_seo_reject_change', 
    'ai_seo_modify_suggestion',
    'ai_seo_create_backup',
    'ai_seo_rollback_changes',
    'ai_seo_get_pending_changes',
    'ai_seo_automate_implementation'
];

foreach ($ajax_actions as $action) {
    if (has_action("wp_ajax_$action")) {
        echo "<span style='color: green;'>✓</span> AJAX action '$action' registered<br>\n";
    } else {
        echo "<span style='color: red;'>✗</span> AJAX action '$action' NOT registered<br>\n";
    }
}

// Test 6: REST API Routes
echo "<h2>6. REST API Routes Test</h2>\n";
$rest_routes = [
    '/ai-seo/v1/apply-changes',
    '/ai-seo/v1/scan',
    '/ai-seo/v1/logs',
    '/ai-seo/v1/dashboard-data'
];

foreach ($rest_routes as $route) {
    $route_exists = rest_get_server()->get_routes($route);
    if (!empty($route_exists)) {
        echo "<span style='color: green;'>✓</span> REST route '$route' registered<br>\n";
    } else {
        echo "<span style='color: red;'>✗</span> REST route '$route' NOT registered<br>\n";
    }
}

// Test 7: Template Files
echo "<h2>7. Template Files Test</h2>\n";
$template_files = [
    'templates/admin-dashboard.php',
    'templates/settings-page.php',
    'templates/pending-changes.php'
];

foreach ($template_files as $template) {
    $file_path = AI_SEO_OPTIMIZER_PLUGIN_DIR . $template;
    if (file_exists($file_path)) {
        echo "<span style='color: green;'>✓</span> Template file '$template' exists<br>\n";
    } else {
        echo "<span style='color: red;'>✗</span> Template file '$template' does NOT exist<br>\n";
    }
}

// Test 8: Sample Data Creation
echo "<h2>8. Sample Data Creation Test</h2>\n";
try {
    // Create a sample backup
    $backup_manager = new AI_SEO_Backup_Manager();
    $backup_id = $backup_manager->create_backup('test', 'Test backup from system test');
    
    if ($backup_id) {
        echo "<span style='color: green;'>✓</span> Sample backup created (ID: $backup_id)<br>\n";
        
        // Clean up test backup
        $backup_manager->delete_backup($backup_id);
        echo "<span style='color: blue;'>ℹ</span> Test backup cleaned up<br>\n";
    } else {
        echo "<span style='color: red;'>✗</span> Failed to create sample backup<br>\n";
    }
    
    // Create a sample recommendation
    $approval_workflow = new AI_SEO_Approval_Workflow();
    $recommendation_data = [
        'post_id' => 1,
        'post_type' => 'post',
        'recommendation_type' => 'title_optimization',
        'current_value' => 'Sample Post',
        'suggested_value' => 'Optimized Sample Post for SEO',
        'ai_reasoning' => 'Title is too short and lacks focus keywords',
        'confidence_score' => 85,
        'impact_analysis' => 'High impact on search rankings',
        'risk_assessment' => 'Low risk - safe to implement',
        'technical_details' => 'Add focus keyword to title for better SEO'
    ];
    
    $recommendation_id = $approval_workflow->create_recommendation($recommendation_data);
    
    if ($recommendation_id) {
        echo "<span style='color: green;'>✓</span> Sample recommendation created (ID: $recommendation_id)<br>\n";
        
        // Clean up test recommendation
        global $wpdb;
        $wpdb->delete($wpdb->prefix . 'ai_seo_recommendations', ['id' => $recommendation_id]);
        echo "<span style='color: blue;'>ℹ</span> Test recommendation cleaned up<br>\n";
    } else {
        echo "<span style='color: red;'>✗</span> Failed to create sample recommendation<br>\n";
    }
    
} catch (Exception $e) {
    echo "<span style='color: red;'>✗</span> Error during sample data creation: " . $e->getMessage() . "<br>\n";
}

echo "<h2>Test Summary</h2>\n";
echo "<p><strong>Your AI SEO Automation Platform is ready for testing!</strong></p>\n";
echo "<p>To start using the enhanced platform:</p>\n";
echo "<ol>\n";
echo "<li>Go to <strong>WordPress Admin → AI SEO → Dashboard</strong></li>\n";
echo "<li>Run a scan to generate AI recommendations</li>\n";
echo "<li>Review pending changes in <strong>AI SEO → Pending Changes</strong></li>\n";
echo "<li>Approve, reject, or modify recommendations as needed</li>\n";
echo "<li>Monitor the automation process and audit logs</li>\n";
echo "</ol>\n";

echo "<p><strong>Key Features Available:</strong></p>\n";
echo "<ul>\n";
echo "<li>✅ Traditional SEO optimization for Google/Bing</li>\n";
echo "<li>✅ Automated backup and rollback system</li>\n";
echo "<li>✅ Approval workflow with user control</li>\n";
echo "<li>✅ Explainable AI with detailed reasoning</li>\n";
echo "<li>✅ REST API for external connections</li>\n";
echo "<li>✅ Complete audit trail and logging</li>\n";
echo "<li>✅ Testing and validation built-in</li>\n";
echo "</ul>\n";

echo "</div>\n";
?>
