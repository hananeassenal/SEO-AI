<?php
/**
 * Quick Setup for AI SEO Optimizer API
 */

// Load WordPress
require_once('../../../wp-load.php');

echo "<h1>AI SEO Optimizer API Setup</h1>";

// Set the API key
update_option('ai_seo_api_key', 'test_api_key_12345');
echo "<p>✅ API Key set to: test_api_key_12345</p>";

// Test the endpoints
$base_url = home_url('/wp-json/ai-seo/v1');

echo "<h2>Testing API Endpoints:</h2>";

// Test health endpoint
$health_url = $base_url . '/health';
echo "<h3>1. Health Endpoint</h3>";
echo "<p>URL: <a href='{$health_url}' target='_blank'>{$health_url}</a></p>";

$health_response = wp_remote_get($health_url, array(
    'headers' => array('X-API-Key' => 'test_api_key_12345'),
    'timeout' => 10
));

if (is_wp_error($health_response)) {
    echo "<p style='color: red;'>❌ Health API Error: " . $health_response->get_error_message() . "</p>";
} else {
    $status = wp_remote_retrieve_response_code($health_response);
    $body = wp_remote_retrieve_body($health_response);
    echo "<p style='color: green;'>✅ Health API Status: {$status}</p>";
    echo "<p>Response: <pre>" . htmlspecialchars($body) . "</pre></p>";
}

// Test dashboard data endpoint
$dashboard_url = $base_url . '/dashboard-data';
echo "<h3>2. Dashboard Data Endpoint</h3>";
echo "<p>URL: <a href='{$dashboard_url}' target='_blank'>{$dashboard_url}</a></p>";

$dashboard_response = wp_remote_get($dashboard_url, array(
    'headers' => array('X-API-Key' => 'test_api_key_12345'),
    'timeout' => 10
));

if (is_wp_error($dashboard_response)) {
    echo "<p style='color: red;'>❌ Dashboard API Error: " . $dashboard_response->get_error_message() . "</p>";
} else {
    $status = wp_remote_retrieve_response_code($dashboard_response);
    $body = wp_remote_retrieve_body($dashboard_response);
    echo "<p style='color: green;'>✅ Dashboard API Status: {$status}</p>";
    echo "<p>Response: <pre>" . htmlspecialchars($body) . "</pre></p>";
}

// Test scan endpoint
$scan_url = $base_url . '/scan';
echo "<h3>3. Scan Endpoint</h3>";
echo "<p>URL: <a href='{$scan_url}' target='_blank'>{$scan_url}</a></p>";

$scan_response = wp_remote_get($scan_url, array(
    'headers' => array('X-API-Key' => 'test_api_key_12345'),
    'timeout' => 10
));

if (is_wp_error($scan_response)) {
    echo "<p style='color: red;'>❌ Scan API Error: " . $scan_response->get_error_message() . "</p>";
} else {
    $status = wp_remote_retrieve_response_code($scan_response);
    $body = wp_remote_retrieve_body($scan_response);
    echo "<p style='color: green;'>✅ Scan API Status: {$status}</p>";
    echo "<p>Response: <pre>" . htmlspecialchars($body) . "</pre></p>";
}

echo "<h2>🎯 Next Steps:</h2>";
echo "<p>1. If all endpoints show ✅ green status, your API is working!</p>";
echo "<p>2. <a href='dashboard/index.html' target='_blank'>Open your Dashboard</a></p>";
echo "<p>3. Try the 'Run Scan' button in the dashboard</p>";

echo "<h2>🔧 If you see errors:</h2>";
echo "<p>1. Make sure your WordPress plugin is activated</p>";
echo "<p>2. Check that the plugin files are in the correct location</p>";
echo "<p>3. Try refreshing this page to re-run the tests</p>";
?>
