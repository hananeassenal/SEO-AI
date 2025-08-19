<?php
/**
 * Test AJAX functionality for AI SEO Optimizer
 * 
 * This file helps debug the "No changes provided" error
 * 
 * Usage: Add this to your WordPress root directory temporarily and access it via browser
 */

// Load WordPress
require_once('wp-load.php');

// Check if user is logged in and has admin privileges
if (!current_user_can('manage_options')) {
    die('Access denied. You must be an administrator.');
}

echo '<h1>AI SEO Optimizer - AJAX Test</h1>';

// Test data
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

echo '<h2>JSON Test:</h2>';
$json_data = json_encode($test_changes);
echo '<pre>' . htmlspecialchars($json_data) . '</pre>';

echo '<h2>Decode Test:</h2>';
$decoded = json_decode($json_data, true);
echo '<pre>' . print_r($decoded, true) . '</pre>';

echo '<h2>AJAX Test:</h2>';
echo '<button onclick="testAjax()">Test AJAX Apply Changes</button>';
echo '<div id="ajax-result"></div>';

?>

<script>
function testAjax() {
    var testChanges = <?php echo json_encode($test_changes); ?>;
    
    console.log('Test changes:', testChanges);
    
    jQuery.ajax({
        url: '<?php echo admin_url('admin-ajax.php'); ?>',
        type: 'POST',
        data: {
            action: 'ai_seo_apply_changes',
            nonce: '<?php echo wp_create_nonce('ai_seo_nonce'); ?>',
            changes: JSON.stringify(testChanges)
        },
        success: function(response) {
            console.log('AJAX Response:', response);
            document.getElementById('ajax-result').innerHTML = '<pre>' + JSON.stringify(response, null, 2) + '</pre>';
        },
        error: function(xhr, status, error) {
            console.log('AJAX Error:', {xhr: xhr, status: status, error: error});
            document.getElementById('ajax-result').innerHTML = '<pre>Error: ' + error + '</pre>';
        }
    });
}
</script>

<script src="<?php echo includes_url('js/jquery/jquery.min.js'); ?>"></script>
