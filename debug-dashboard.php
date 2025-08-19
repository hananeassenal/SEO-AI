<?php
/**
 * Debug Dashboard Data Loading
 */

// Load WordPress
require_once('../../../wp-load.php');

echo "<h1>Debug Dashboard Data Loading</h1>";

// Test if plugin is active
if (is_plugin_active('ai-seo-optimizer/ai-seo-optimizer.php')) {
    echo "<p style='color: green;'>✅ AI SEO Optimizer plugin is ACTIVE</p>";
} else {
    echo "<p style='color: red;'>❌ AI SEO Optimizer plugin is NOT ACTIVE</p>";
    echo "<p><a href='" . admin_url('plugins.php') . "' target='_blank'>Go to Plugins page to activate it</a></p>";
}

// Test dashboard data endpoint
$dashboard_url = rest_url('ai-seo/v1/dashboard-data');
echo "<h2>Testing Dashboard Data Endpoint</h2>";
echo "<p><strong>URL:</strong> <a href='{$dashboard_url}' target='_blank'>{$dashboard_url}</a></p>";

// Test without API key first
$response_no_key = wp_remote_get($dashboard_url);
if (is_wp_error($response_no_key)) {
    echo "<p style='color: red;'>❌ Error without API key: " . $response_no_key->get_error_message() . "</p>";
} else {
    $status = wp_remote_retrieve_response_code($response_no_key);
    echo "<p>Status without API key: {$status}</p>";
    if ($status === 403) {
        echo "<p style='color: orange;'>⚠️ Expected: 403 (API key required)</p>";
    }
}

// Test with API key
$response_with_key = wp_remote_get($dashboard_url, array(
    'headers' => array('X-API-Key' => 'test_api_key_12345'),
    'timeout' => 10
));

if (is_wp_error($response_with_key)) {
    echo "<p style='color: red;'>❌ Error with API key: " . $response_with_key->get_error_message() . "</p>";
} else {
    $status = wp_remote_retrieve_response_code($response_with_key);
    $body = wp_remote_retrieve_body($response_with_key);
    
    if ($status === 200) {
        echo "<p style='color: green;'>✅ SUCCESS! Status: {$status}</p>";
        echo "<p><strong>Response:</strong></p>";
        echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 5px;'>" . htmlspecialchars($body) . "</pre>";
    } else {
        echo "<p style='color: red;'>❌ FAILED! Status: {$status}</p>";
        echo "<p><strong>Response:</strong></p>";
        echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 5px;'>" . htmlspecialchars($body) . "</pre>";
    }
}

// Test scan endpoint
$scan_url = rest_url('ai-seo/v1/scan');
echo "<h2>Testing Scan Endpoint</h2>";
echo "<p><strong>URL:</strong> <a href='{$scan_url}' target='_blank'>{$scan_url}</a></p>";

$scan_response = wp_remote_get($scan_url, array(
    'headers' => array('X-API-Key' => 'test_api_key_12345'),
    'timeout' => 10
));

if (is_wp_error($scan_response)) {
    echo "<p style='color: red;'>❌ Scan Error: " . $scan_response->get_error_message() . "</p>";
} else {
    $status = wp_remote_retrieve_response_code($scan_response);
    $body = wp_remote_retrieve_body($scan_response);
    
    if ($status === 200) {
        echo "<p style='color: green;'>✅ Scan SUCCESS! Status: {$status}</p>";
        echo "<p><strong>Response:</strong></p>";
        echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 5px;'>" . htmlspecialchars($body) . "</pre>";
    } else {
        echo "<p style='color: red;'>❌ Scan FAILED! Status: {$status}</p>";
        echo "<p><strong>Response:</strong></p>";
        echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 5px;'>" . htmlspecialchars($body) . "</pre>";
    }
}

echo "<h2>🎯 Next Steps:</h2>";
echo "<p>1. If you see ✅ SUCCESS, the API is working correctly</p>";
echo "<p>2. If you see ❌ FAILED, we need to fix the WordPress plugin</p>";
echo "<p>3. Copy the response data to update your dashboard</p>";
?>
