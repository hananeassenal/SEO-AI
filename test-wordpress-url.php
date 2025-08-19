<?php
/**
 * Test WordPress URL Configuration
 */

// Load WordPress
require_once('../../../wp-load.php');

echo "<h1>WordPress URL Test</h1>";

echo "<h2>WordPress Site Information:</h2>";
echo "<p><strong>Site URL:</strong> " . get_site_url() . "</p>";
echo "<p><strong>Home URL:</strong> " . get_home_url() . "</p>";
echo "<p><strong>Admin URL:</strong> " . admin_url() . "</p>";
echo "<p><strong>REST API Base:</strong> " . rest_url() . "</p>";

echo "<h2>Testing API Endpoints:</h2>";

// Test the REST API base
$rest_base = rest_url();
echo "<p><strong>REST API Base:</strong> <a href='{$rest_base}' target='_blank'>{$rest_base}</a></p>";

// Test our plugin endpoints
$plugin_base = rest_url('ai-seo/v1/');
echo "<p><strong>Plugin API Base:</strong> <a href='{$plugin_base}' target='_blank'>{$plugin_base}</a></p>";

// Test health endpoint
$health_url = rest_url('ai-seo/v1/health');
echo "<p><strong>Health Endpoint:</strong> <a href='{$health_url}' target='_blank'>{$health_url}</a></p>";

// Test if plugin is active
if (is_plugin_active('ai-seo-optimizer/ai-seo-optimizer.php')) {
    echo "<p style='color: green;'>✅ AI SEO Optimizer plugin is ACTIVE</p>";
} else {
    echo "<p style='color: red;'>❌ AI SEO Optimizer plugin is NOT ACTIVE</p>";
}

// Test if REST API is working
$rest_test = wp_remote_get($rest_base);
if (is_wp_error($rest_test)) {
    echo "<p style='color: red;'>❌ REST API Error: " . $rest_test->get_error_message() . "</p>";
} else {
    echo "<p style='color: green;'>✅ REST API is working (Status: " . wp_remote_retrieve_response_code($rest_test) . ")</p>";
}

echo "<h2>🎯 Next Steps:</h2>";
echo "<p>1. Copy the correct URL from above</p>";
echo "<p>2. Update your dashboard config with the correct URL</p>";
echo "<p>3. Make sure the plugin is activated</p>";
?>
