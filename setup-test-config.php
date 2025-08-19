<?php
/**
 * Setup Test Configuration for AI SEO Optimizer
 * 
 * Run this script to set up the test API key and configuration
 */

// Load WordPress
require_once('../../../wp-load.php');

// Set test API key
update_option('ai_seo_api_key', 'test_api_key_12345');
update_option('ai_seo_customer_id', 'test_customer_67890');
update_option('ai_seo_api_url', home_url('/wp-json/ai-seo/v1'));

echo "<h2>AI SEO Optimizer Test Configuration Setup</h2>";
echo "<p><strong>✅ API Key set:</strong> test_api_key_12345</p>";
echo "<p><strong>✅ Customer ID set:</strong> test_customer_67890</p>";
echo "<p><strong>✅ API URL set:</strong> " . home_url('/wp-json/ai-seo/v1') . "</p>";

// Test the API endpoints
echo "<h3>Testing API Endpoints:</h3>";

// Test health endpoint
$health_url = home_url('/wp-json/ai-seo/v1/health');
echo "<p><strong>Health Endpoint:</strong> <a href='{$health_url}' target='_blank'>{$health_url}</a></p>";

// Test dashboard data endpoint
$dashboard_url = home_url('/wp-json/ai-seo/v1/dashboard-data');
echo "<p><strong>Dashboard Data Endpoint:</strong> <a href='{$dashboard_url}' target='_blank'>{$dashboard_url}</a></p>";

// Test scan endpoint
$scan_url = home_url('/wp-json/ai-seo/v1/scan');
echo "<p><strong>Scan Endpoint:</strong> <a href='{$scan_url}' target='_blank'>{$scan_url}</a></p>";

echo "<p><strong>🎯 Next Steps:</strong></p>";
echo "<ol>";
echo "<li>Test the endpoints above by clicking the links</li>";
echo "<li>Go to your dashboard: <a href='dashboard/index.html' target='_blank'>Open Dashboard</a></li>";
echo "<li>Try the 'Run Scan' button in the dashboard</li>";
echo "</ol>";

echo "<p><strong>Note:</strong> If you see 404 errors, make sure your WordPress site is running and the plugin is activated.</p>";
?>
