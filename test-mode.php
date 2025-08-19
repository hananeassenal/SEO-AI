<?php
/**
 * Test Mode Configuration
 * 
 * Add this to your wp-config.php or include it to enable test mode
 * This allows testing the plugin without a real external API
 */

// Enable test mode
define('AI_SEO_TEST_MODE', true);

// Optional: Set test API credentials
if (!get_option('ai_seo_api_key')) {
    update_option('ai_seo_api_key', 'test_api_key_12345');
    update_option('ai_seo_customer_id', 'test_customer_67890');
    update_option('ai_seo_api_url', 'https://api.example.com/v1');
}

echo "AI SEO Optimizer Test Mode Enabled!";
echo "<br>You can now test the plugin without a real external API.";
echo "<br>Go to WordPress Admin → AI SEO Optimizer → Dashboard";
echo "<br>Click 'Scan Website' to see test recommendations.";
?>
