<?php
/**
 * AI SEO Debug Code - Add this to your theme's functions.php
 * Remove this after debugging is complete
 */

// Add debug tool to admin footer
add_action('admin_footer', 'ai_seo_debug_tool');

function ai_seo_debug_tool() {
    // Only show on AI SEO pages
    if (!isset($_GET['page']) || strpos($_GET['page'], 'ai-seo') === false) {
        return;
    }
    
    ?>
    <div id="ai-seo-debug" style="position: fixed; bottom: 10px; right: 10px; background: #fff; border: 2px solid #0073aa; padding: 15px; border-radius: 5px; box-shadow: 0 2px 10px rgba(0,0,0,0.3); z-index: 9999; max-width: 400px;">
        <h4 style="margin: 0 0 10px 0; color: #0073aa;">AI SEO Debug Tool</h4>
        
        <div style="margin-bottom: 10px;">
            <button type="button" onclick="testAjaxDirect()" style="background: #0073aa; color: white; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer; margin-right: 5px;">
                Test AJAX
            </button>
            <button type="button" onclick="showRecommendations()" style="background: #46b450; color: white; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer;">
                Show Data
            </button>
            <button type="button" onclick="document.getElementById('ai-seo-debug').style.display='none'" style="background: #dc3232; color: white; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer; float: right;">
                X
            </button>
        </div>
        
        <div id="debug-output" style="background: #f1f1f1; padding: 10px; border-radius: 3px; font-family: monospace; font-size: 12px; max-height: 200px; overflow-y: auto;">
            Ready to debug...
        </div>
    </div>

    <script>
    function testAjaxDirect() {
        var testData = [{
            change_id: 'debug_test_1',
            change_type: 'post_title',
            target_post_id: 1,
            old_value: 'Test Post',
            new_value: 'Test Post - Debug Optimized',
            priority: 'medium',
            reason: 'Debug test'
        }];
        
        console.log('Testing with data:', testData);
        addDebugOutput('Testing with data: ' + JSON.stringify(testData));
        
        jQuery.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'ai_seo_apply_changes',
                nonce: aiSeoAjax.nonce,
                changes: JSON.stringify(testData)
            },
            success: function(response) {
                console.log('AJAX Success:', response);
                addDebugOutput('SUCCESS: ' + JSON.stringify(response));
            },
            error: function(xhr, status, error) {
                console.log('AJAX Error:', {xhr: xhr, status: status, error: error});
                addDebugOutput('ERROR: ' + error + ' - Status: ' + status);
                addDebugOutput('Response: ' + xhr.responseText);
            }
        });
    }
    
    function showRecommendations() {
        if (window.aiSeoRecommendations) {
            addDebugOutput('Global recommendations: ' + JSON.stringify(window.aiSeoRecommendations));
        } else {
            addDebugOutput('No global recommendations found');
        }
        
        try {
            var stored = localStorage.getItem('aiSeoRecommendations');
            if (stored) {
                addDebugOutput('LocalStorage: ' + stored);
            } else {
                addDebugOutput('No localStorage data');
            }
        } catch (e) {
            addDebugOutput('LocalStorage error: ' + e.message);
        }
    }
    
    function addDebugOutput(message) {
        var output = document.getElementById('debug-output');
        var timestamp = new Date().toLocaleTimeString();
        output.innerHTML += '<div style="margin-bottom: 5px;"><strong>[' + timestamp + ']</strong> ' + message + '</div>';
        output.scrollTop = output.scrollHeight;
    }
    
    // Auto-test when page loads
    jQuery(document).ready(function() {
        setTimeout(function() {
            addDebugOutput('Page loaded. Ready for testing.');
        }, 1000);
    });
    </script>
    <?php
}
