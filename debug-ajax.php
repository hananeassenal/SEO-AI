<?php
/**
 * Debug AJAX functionality for AI SEO Optimizer
 * 
 * This file helps debug the "Invalid JSON data provided" error
 * 
 * Usage: Add this to your WordPress root directory temporarily and access it via browser
 */

// Load WordPress
require_once('wp-load.php');

// Check if user is logged in and has admin privileges
if (!current_user_can('manage_options')) {
    die('Access denied. You must be an administrator.');
}

echo '<h1>AI SEO Optimizer - AJAX Debug Tool</h1>';

// Test data - same as what the plugin generates
$test_changes = array(
    array(
        'change_id' => 'test_1',
        'change_type' => 'post_title',
        'target_post_id' => 1,
        'old_value' => 'Test Post',
        'new_value' => 'Test Post - Optimized for SEO',
        'priority' => 'medium',
        'reason' => 'Title optimization for better search visibility'
    )
);

echo '<h2>Test Data:</h2>';
echo '<pre>' . print_r($test_changes, true) . '</pre>';

echo '<h2>JSON Tests:</h2>';

// Test 1: Basic JSON
$json_data = json_encode($test_changes);
echo '<h3>1. Basic JSON:</h3>';
echo '<pre>' . htmlspecialchars($json_data) . '</pre>';

// Test 2: HTML escaped JSON (like what we're doing in JS)
$escaped_json = json_encode($test_changes);
$escaped_json = str_replace("'", '&#39;', $escaped_json);
$escaped_json = str_replace('"', '&quot;', $escaped_json);
echo '<h3>2. HTML Escaped JSON:</h3>';
echo '<pre>' . htmlspecialchars($escaped_json) . '</pre>';

// Test 3: Decode escaped JSON
$decoded_escaped = html_entity_decode($escaped_json, ENT_QUOTES, 'UTF-8');
echo '<h3>3. Decoded Escaped JSON:</h3>';
echo '<pre>' . htmlspecialchars($decoded_escaped) . '</pre>';

// Test 4: Final decode
$final_decode = json_decode($decoded_escaped, true);
echo '<h3>4. Final Decode Result:</h3>';
echo '<pre>' . print_r($final_decode, true) . '</pre>';

echo '<h2>AJAX Test:</h2>';
echo '<button onclick="testBasicAjax()">Test Basic AJAX</button>';
echo '<button onclick="testEscapedAjax()">Test Escaped AJAX</button>';
echo '<div id="ajax-result"></div>';

?>

<script>
function testBasicAjax() {
    var testChanges = <?php echo json_encode($test_changes); ?>;
    
    console.log('Basic test changes:', testChanges);
    
    jQuery.ajax({
        url: '<?php echo admin_url('admin-ajax.php'); ?>',
        type: 'POST',
        data: {
            action: 'ai_seo_apply_changes',
            nonce: '<?php echo wp_create_nonce('ai_seo_nonce'); ?>',
            changes: JSON.stringify(testChanges)
        },
        success: function(response) {
            console.log('Basic AJAX Response:', response);
            document.getElementById('ajax-result').innerHTML = '<h3>Basic AJAX Result:</h3><pre>' + JSON.stringify(response, null, 2) + '</pre>';
        },
        error: function(xhr, status, error) {
            console.log('Basic AJAX Error:', {xhr: xhr, status: status, error: error});
            document.getElementById('ajax-result').innerHTML = '<h3>Basic AJAX Error:</h3><pre>Error: ' + error + '</pre>';
        }
    });
}

function testEscapedAjax() {
    var testChanges = <?php echo json_encode($test_changes); ?>;
    var escapedJson = JSON.stringify(testChanges).replace(/'/g, '&#39;').replace(/"/g, '&quot;');
    
    console.log('Escaped test changes:', escapedJson);
    
    jQuery.ajax({
        url: '<?php echo admin_url('admin-ajax.php'); ?>',
        type: 'POST',
        data: {
            action: 'ai_seo_apply_changes',
            nonce: '<?php echo wp_create_nonce('ai_seo_nonce'); ?>',
            changes: escapedJson
        },
        success: function(response) {
            console.log('Escaped AJAX Response:', response);
            document.getElementById('ajax-result').innerHTML += '<h3>Escaped AJAX Result:</h3><pre>' + JSON.stringify(response, null, 2) + '</pre>';
        },
        error: function(xhr, status, error) {
            console.log('Escaped AJAX Error:', {xhr: xhr, status: status, error: error});
            document.getElementById('ajax-result').innerHTML += '<h3>Escaped AJAX Error:</h3><pre>Error: ' + error + '</pre>';
        }
    });
}
</script>

<script src="<?php echo includes_url('js/jquery/jquery.min.js'); ?>"></script>
