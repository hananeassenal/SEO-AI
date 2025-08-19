<?php
/**
 * API Handler Class
 * 
 * Handles communication with external AI SEO API
 * 
 * @package AI_SEO_Optimizer
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * AI SEO API Handler Class
 */
class AI_SEO_API_Handler {
    
    /**
     * API base URL
     */
    private $api_url;
    
    /**
     * API key
     */
    private $api_key;
    
    /**
     * Customer ID
     */
    private $customer_id;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->api_url = get_option('ai_seo_api_url', 'https://api.example.com/v1');
        $this->api_key = get_option('ai_seo_api_key', '');
        $this->customer_id = get_option('ai_seo_customer_id', '');
    }
    
    /**
     * Prepare scan data for API
     */
    public function prepare_scan_data() {
        $data = array(
            'customer_id' => $this->customer_id,
            'site_url' => get_site_url(),
            'site_name' => get_bloginfo('name'),
            'wordpress_version' => get_bloginfo('version'),
            'plugin_version' => AI_SEO_OPTIMIZER_VERSION,
            'scan_timestamp' => current_time('mysql'),
            'posts' => $this->get_posts_data(),
            'pages' => $this->get_pages_data(),
            'seo_plugins' => $this->get_seo_plugins_data(),
            'site_settings' => $this->get_site_settings(),
        );
        
        return $data;
    }
    
    /**
     * Get posts data
     */
    private function get_posts_data() {
        $posts = get_posts(array(
            'numberposts' => 50,
            'post_status' => 'publish',
            'orderby' => 'date',
            'order' => 'DESC'
        ));
        
        $posts_data = array();
        
        foreach ($posts as $post) {
            $post_data = array(
                'id' => $post->ID,
                'title' => $post->post_title,
                'content' => wp_strip_all_tags($post->post_content),
                'excerpt' => $post->post_excerpt,
                'url' => get_permalink($post->ID),
                'date' => $post->post_date,
                'author' => get_the_author_meta('display_name', $post->post_author),
                'categories' => wp_get_post_categories($post->ID, array('fields' => 'names')),
                'tags' => wp_get_post_tags($post->ID, array('fields' => 'names')),
                'seo_data' => $this->get_post_seo_data($post->ID),
            );
            
            $posts_data[] = $post_data;
        }
        
        return $posts_data;
    }
    
    /**
     * Get pages data
     */
    private function get_pages_data() {
        $pages = get_pages(array(
            'number' => 20,
            'post_status' => 'publish',
            'sort_column' => 'post_date',
            'sort_order' => 'DESC'
        ));
        
        $pages_data = array();
        
        foreach ($pages as $page) {
            $page_data = array(
                'id' => $page->ID,
                'title' => $page->post_title,
                'content' => wp_strip_all_tags($page->post_content),
                'url' => get_permalink($page->ID),
                'date' => $page->post_date,
                'seo_data' => $this->get_post_seo_data($page->ID),
            );
            
            $pages_data[] = $page_data;
        }
        
        return $pages_data;
    }
    
    /**
     * Get SEO data for a post/page
     */
    private function get_post_seo_data($post_id) {
        $seo_data = array();
        
        // Yoast SEO
        if (is_plugin_active('wordpress-seo/wp-seo.php')) {
            $seo_data['yoast'] = array(
                'title' => get_post_meta($post_id, '_yoast_wpseo_title', true),
                'meta_description' => get_post_meta($post_id, '_yoast_wpseo_metadesc', true),
                'focus_keyword' => get_post_meta($post_id, '_yoast_wpseo_focuskw', true),
                'seo_score' => get_post_meta($post_id, '_yoast_wpseo_linkdex', true),
            );
        }
        
        // RankMath
        if (is_plugin_active('seo-by-rank-math/rank-math.php')) {
            $seo_data['rankmath'] = array(
                'title' => get_post_meta($post_id, 'rank_math_title', true),
                'meta_description' => get_post_meta($post_id, 'rank_math_description', true),
                'focus_keyword' => get_post_meta($post_id, 'rank_math_focus_keyword', true),
                'seo_score' => get_post_meta($post_id, 'rank_math_seo_score', true),
            );
        }
        
        // All in One SEO
        if (is_plugin_active('all-in-one-seo-pack/all_in_one_seo_pack.php')) {
            $seo_data['aioseo'] = array(
                'title' => get_post_meta($post_id, '_aioseo_title', true),
                'meta_description' => get_post_meta($post_id, '_aioseo_description', true),
                'keywords' => get_post_meta($post_id, '_aioseo_keywords', true),
            );
        }
        
        return $seo_data;
    }
    
    /**
     * Get SEO plugins data
     */
    private function get_seo_plugins_data() {
        $plugins = array();
        
        // Check for Yoast SEO
        if (is_plugin_active('wordpress-seo/wp-seo.php')) {
            $plugins['yoast'] = array(
                'active' => true,
                'version' => get_plugin_data(WP_PLUGIN_DIR . '/wordpress-seo/wp-seo.php')['Version'],
            );
        }
        
        // Check for RankMath
        if (is_plugin_active('seo-by-rank-math/rank-math.php')) {
            $plugins['rankmath'] = array(
                'active' => true,
                'version' => get_plugin_data(WP_PLUGIN_DIR . '/seo-by-rank-math/rank-math.php')['Version'],
            );
        }
        
        // Check for All in One SEO
        if (is_plugin_active('all-in-one-seo-pack/all_in_one_seo_pack.php')) {
            $plugins['aioseo'] = array(
                'active' => true,
                'version' => get_plugin_data(WP_PLUGIN_DIR . '/all-in-one-seo-pack/all_in_one_seo_pack.php')['Version'],
            );
        }
        
        return $plugins;
    }
    
    /**
     * Get site settings
     */
    private function get_site_settings() {
        return array(
            'site_title' => get_bloginfo('name'),
            'site_description' => get_bloginfo('description'),
            'site_url' => get_site_url(),
            'admin_email' => get_option('admin_email'),
            'timezone' => get_option('timezone_string'),
            'date_format' => get_option('date_format'),
            'time_format' => get_option('time_format'),
            'permalink_structure' => get_option('permalink_structure'),
            'theme' => wp_get_theme()->get('Name'),
            'theme_version' => wp_get_theme()->get('Version'),
        );
    }
    
    /**
     * Send scan data to external API
     *
     * @param array $scan_data The data to send
     * @return array|WP_Error Response data or error
     */
    public function send_scan_data($scan_data) {
        // Test mode - simulate API response
        if (defined('AI_SEO_TEST_MODE') && AI_SEO_TEST_MODE) {
            return $this->get_test_recommendations($scan_data);
        }

        $response = wp_remote_post($this->api_url . '/scan', array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'X-API-Key' => $this->api_key,
                'X-Customer-ID' => $this->customer_id
            ),
            'body' => json_encode($scan_data),
            'timeout' => 60
        ));

        if (is_wp_error($response)) {
            return $response;
        }

        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);

        if ($response_code !== 200) {
            return new WP_Error('api_error', sprintf('API returned status %d: %s', $response_code, $response_body));
        }

        $data = json_decode($response_body, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return new WP_Error('json_error', 'Invalid JSON response from API');
        }

        return $data;
    }

    /**
     * Get test recommendations for development/testing
     *
     * @param array $scan_data The scan data
     * @return array Test recommendations
     */
    private function get_test_recommendations($scan_data) {
        $recommendations = array();
        
        if (isset($scan_data['posts']) && is_array($scan_data['posts'])) {
            foreach ($scan_data['posts'] as $post) {
                $post_id = $post['id'];
                $current_title = $post['title'];
                $content = $post['content'];
                
                // Clean up title (remove any existing "Optimized" suffixes)
                $clean_title = preg_replace('/\s*-\s*Optimized.*$/', '', $current_title);
                
                // Generate intelligent title recommendations
                $recommendations[] = array(
                    'change_id' => 'test_' . $post_id . '_title_1',
                    'change_type' => 'post_title',
                    'target_post_id' => $post_id,
                    'old_value' => $current_title,
                    'new_value' => $clean_title . ' - Complete Guide',
                    'priority' => 'high',
                    'reason' => 'Add benefit-focused suffix to improve click-through rates'
                );
                
                $recommendations[] = array(
                    'change_id' => 'test_' . $post_id . '_title_2',
                    'change_type' => 'post_title',
                    'target_post_id' => $post_id,
                    'old_value' => $current_title,
                    'new_value' => $clean_title . ' - Best Practices & Tips',
                    'priority' => 'medium',
                    'reason' => 'Make title more specific and descriptive'
                );
                
                // Generate better meta description recommendations
                $recommendations[] = array(
                    'change_id' => 'test_' . $post_id . '_meta_1',
                    'change_type' => 'meta_description',
                    'target_post_id' => $post_id,
                    'old_value' => $post['meta_description'] ?? '',
                    'new_value' => 'Discover proven strategies and best practices for content optimization. Learn expert tips to improve your search visibility and drive better results.',
                    'priority' => 'high',
                    'reason' => 'Create compelling meta description that drives clicks'
                );
                
                $recommendations[] = array(
                    'change_id' => 'test_' . $post_id . '_meta_2',
                    'change_type' => 'meta_description',
                    'target_post_id' => $post_id,
                    'old_value' => $post['meta_description'] ?? '',
                    'new_value' => 'Master content optimization with our comprehensive guide. Get actionable strategies and expert insights to boost your SEO performance.',
                    'priority' => 'medium',
                    'reason' => 'Use action-oriented format for better engagement'
                );
                
                // Generate better focus keyword recommendations
                $recommendations[] = array(
                    'change_id' => 'test_' . $post_id . '_keyword_1',
                    'change_type' => 'focus_keyword',
                    'target_post_id' => $post_id,
                    'old_value' => $post['focus_keyword'] ?? '',
                    'new_value' => 'content optimization strategies',
                    'priority' => 'high',
                    'reason' => 'Target specific, actionable keyword phrase'
                );
                
                $recommendations[] = array(
                    'change_id' => 'test_' . $post_id . '_keyword_2',
                    'change_type' => 'focus_keyword',
                    'target_post_id' => $post_id,
                    'old_value' => $post['focus_keyword'] ?? '',
                    'new_value' => 'how to optimize content for seo',
                    'priority' => 'medium',
                    'reason' => 'Target question-based searches for better visibility'
                );
                
                $recommendations[] = array(
                    'change_id' => 'test_' . $post_id . '_keyword_3',
                    'change_type' => 'focus_keyword',
                    'target_post_id' => $post_id,
                    'old_value' => $post['focus_keyword'] ?? '',
                    'new_value' => 'seo best practices guide',
                    'priority' => 'medium',
                    'reason' => 'Focus on long-tail keyword with less competition'
                );
            }
        }
        
        return array(
            'success' => true,
            'recommendations' => $recommendations,
            'total_changes' => count($recommendations),
            'scan_timestamp' => current_time('mysql')
        );
    }
    
    /**
     * Check API connection
     */
    public function check_connection() {
        if (empty($this->api_key) || empty($this->customer_id)) {
            return false;
        }
        
        $response = wp_remote_get($this->api_url . '/health', array(
            'headers' => array(
                'X-API-Key' => $this->api_key,
                'User-Agent' => 'AI-SEO-Optimizer/' . AI_SEO_OPTIMIZER_VERSION,
            ),
            'timeout' => 10,
            'sslverify' => true,
        ));
        
        if (is_wp_error($response)) {
            return false;
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        return $response_code === 200;
    }
    
    /**
     * Get recommendations from API
     */
    public function get_recommendations($post_id = null) {
        if (empty($this->api_key) || empty($this->customer_id)) {
            return new WP_Error('missing_credentials', __('API key or Customer ID not configured', 'ai-seo-optimizer'));
        }
        
        $endpoint = $this->api_url . '/recommendations';
        $data = array(
            'customer_id' => $this->customer_id,
            'site_url' => get_site_url(),
        );
        
        if ($post_id) {
            $data['post_id'] = $post_id;
            $data['post_data'] = $this->get_post_data($post_id);
        }
        
        $response = wp_remote_post($endpoint, array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'X-API-Key' => $this->api_key,
                'User-Agent' => 'AI-SEO-Optimizer/' . AI_SEO_OPTIMIZER_VERSION,
            ),
            'body' => json_encode($data),
            'timeout' => 30,
            'sslverify' => true,
        ));
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        
        if ($response_code !== 200) {
            return new WP_Error(
                'api_error',
                sprintf(__('API request failed with status %d: %s', 'ai-seo-optimizer'), $response_code, $response_body)
            );
        }
        
        $result = json_decode($response_body, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return new WP_Error('json_error', __('Invalid JSON response from API', 'ai-seo-optimizer'));
        }
        
        return $result;
    }
    
    /**
     * Get single post data
     */
    private function get_post_data($post_id) {
        $post = get_post($post_id);
        
        if (!$post) {
            return null;
        }
        
        return array(
            'id' => $post->ID,
            'title' => $post->post_title,
            'content' => wp_strip_all_tags($post->post_content),
            'excerpt' => $post->post_excerpt,
            'url' => get_permalink($post->ID),
            'date' => $post->post_date,
            'author' => get_the_author_meta('display_name', $post->post_author),
            'categories' => wp_get_post_categories($post->ID, array('fields' => 'names')),
            'tags' => wp_get_post_tags($post->ID, array('fields' => 'names')),
            'seo_data' => $this->get_post_seo_data($post->ID),
        );
    }
    
    /**
     * Update API credentials
     */
    public function update_credentials($api_key, $customer_id, $api_url = null) {
        $this->api_key = sanitize_text_field($api_key);
        $this->customer_id = sanitize_text_field($customer_id);
        
        if ($api_url) {
            $this->api_url = esc_url_raw($api_url);
        }
        
        update_option('ai_seo_api_key', $this->api_key);
        update_option('ai_seo_customer_id', $this->customer_id);
        update_option('ai_seo_api_url', $this->api_url);
        
        return true;
    }
    
    /**
     * Get API status
     */
    public function get_api_status() {
        $connection = $this->check_connection();
        
        return array(
            'connected' => $connection,
            'api_url' => $this->api_url,
            'has_api_key' => !empty($this->api_key),
            'has_customer_id' => !empty($this->customer_id),
            'last_check' => current_time('mysql'),
        );
    }
}
