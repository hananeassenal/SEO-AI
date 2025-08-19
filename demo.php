<?php
/**
 * AI SEO Optimizer Demo
 * 
 * This file demonstrates the AI SEO Optimizer functionality
 * Run this file to see test recommendations in action
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    // If not in WordPress, simulate basic environment
    if (!function_exists('wp_die')) {
        function wp_die($message) {
            echo "Error: " . $message;
            exit;
        }
    }
}

// Enable test mode
define('AI_SEO_TEST_MODE', true);

// Include the main plugin file
require_once 'ai-seo-optimizer.php';

// Initialize the plugin
$ai_seo_optimizer = AI_SEO_Optimizer::get_instance();

// Test the API handler
$api_handler = $ai_seo_optimizer->api_handler;

// Prepare test scan data
$scan_data = $api_handler->prepare_scan_data();

// Get test recommendations
$recommendations = $api_handler->send_scan_data($scan_data);

echo "<h1>AI SEO Optimizer Demo</h1>";
echo "<p>This demo shows how the AI SEO Optimizer generates and displays recommendations.</p>";

echo "<h2>Test Recommendations</h2>";

if (is_wp_error($recommendations)) {
    echo "<p style='color: red;'>Error: " . $recommendations->get_error_message() . "</p>";
} else {
    echo "<p>Found " . count($recommendations['recommendations']) . " recommendations:</p>";
    
    foreach ($recommendations['recommendations'] as $index => $rec) {
        echo "<div style='border: 1px solid #ddd; padding: 15px; margin: 10px 0; border-radius: 5px;'>";
        echo "<h3>Recommendation " . ($index + 1) . "</h3>";
        echo "<p><strong>Type:</strong> " . esc_html($rec['change_type']) . "</p>";
        echo "<p><strong>Priority:</strong> " . esc_html($rec['priority']) . "</p>";
        echo "<p><strong>Reason:</strong> " . esc_html($rec['reason']) . "</p>";
        echo "<p><strong>Post ID:</strong> " . esc_html($rec['target_post_id']) . "</p>";
        echo "<p><strong>Old Value:</strong> " . esc_html(substr($rec['old_value'], 0, 100)) . "...</p>";
        echo "<p><strong>New Value:</strong> " . esc_html(substr($rec['new_value'], 0, 100)) . "...</p>";
        echo "</div>";
    }
}

echo "<h2>How to Use</h2>";
echo "<ol>";
echo "<li>Go to WordPress Admin → AI SEO Optimizer → Dashboard</li>";
echo "<li>Click 'Scan Website' to generate recommendations</li>";
echo "<li>Review the recommendations and click 'Apply This Change' for individual changes</li>";
echo "<li>Or click 'Apply All Recommendations' to apply all changes at once</li>";
echo "</ol>";

echo "<h2>Features</h2>";
echo "<ul>";
echo "<li>Automated SEO scanning and recommendations</li>";
echo "<li>One-click application of SEO improvements</li>";
echo "<li>Audit logging of all changes</li>";
echo "<li>Support for Yoast SEO, RankMath, and All in One SEO</li>";
echo "<li>Test mode for development and testing</li>";
echo "</ul>";

echo "<p><strong>Note:</strong> This is running in test mode. In production, you would need to configure your API credentials in the settings.</p>";
?>
