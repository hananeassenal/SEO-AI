<?php
/**
 * Test Scan Endpoint
 */

// Load WordPress
require_once('../../../wp-load.php');

echo "<h1>Test Scan Endpoint</h1>";

// Test the scan endpoint directly
$scan_url = rest_url('ai-seo/v1/scan');
echo "<p><strong>Scan URL:</strong> <a href='{$scan_url}' target='_blank'>{$scan_url}</a></p>";

// Test with API key
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

// Test dashboard data endpoint
$dashboard_url = rest_url('ai-seo/v1/dashboard-data');
echo "<h2>Test Dashboard Data Endpoint</h2>";
echo "<p><strong>Dashboard URL:</strong> <a href='{$dashboard_url}' target='_blank'>{$dashboard_url}</a></p>";

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

echo "<h2>🎯 Next Steps:</h2>";
echo "<p>1. If both endpoints show ✅ green status, the API is working</p>";
echo "<p>2. The issue might be browser cache or DNS resolution</p>";
echo "<p>3. Try opening the dashboard in a different browser or incognito mode</p>";
?>
