<?php
/**
 * Shopify REST API Connector Class
 * 
 * Handles Shopify store integration for AI SEO optimization
 * Supports Shopify Admin API for product, page, and SEO management
 * 
 * @package AI_SEO_Optimizer
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class AI_SEO_Shopify_Connector {
    
    /**
     * Shopify API configuration
     */
    private $api_key;
    private $api_secret;
    private $store_url;
    private $access_token;
    private $api_version = '2024-01';
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->api_key = get_option('ai_seo_shopify_api_key', '');
        $this->api_secret = get_option('ai_seo_shopify_api_secret', '');
        $this->store_url = get_option('ai_seo_shopify_store_url', '');
        $this->access_token = get_option('ai_seo_shopify_access_token', '');
    }
    
    /**
     * Test Shopify connection
     */
    public function test_connection() {
        if (empty($this->store_url) || empty($this->access_token)) {
            return new WP_Error('shopify_not_configured', 'Shopify store not configured');
        }
        
        $response = $this->make_api_request('GET', '/admin/api/' . $this->api_version . '/shop.json');
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        return array(
            'status' => 'connected',
            'store_name' => $response['shop']['name'],
            'store_url' => $response['shop']['domain'],
            'email' => $response['shop']['email'],
            'plan' => $response['shop']['plan_name']
        );
    }
    
    /**
     * Get Shopify store data for SEO analysis
     */
    public function get_store_data() {
        $store_data = array(
            'store_info' => $this->get_store_info(),
            'products' => $this->get_products(),
            'pages' => $this->get_pages(),
            'blogs' => $this->get_blogs(),
            'collections' => $this->get_collections()
        );
        
        return $store_data;
    }
    
    /**
     * Get store information
     */
    private function get_store_info() {
        $response = $this->make_api_request('GET', '/admin/api/' . $this->api_version . '/shop.json');
        
        if (is_wp_error($response)) {
            return array();
        }
        
        return array(
            'name' => $response['shop']['name'],
            'domain' => $response['shop']['domain'],
            'email' => $response['shop']['email'],
            'phone' => $response['shop']['phone'],
            'address' => $response['shop']['address1'],
            'city' => $response['shop']['city'],
            'province' => $response['shop']['province'],
            'country' => $response['shop']['country'],
            'zip' => $response['shop']['zip'],
            'currency' => $response['shop']['currency'],
            'timezone' => $response['shop']['iana_timezone'],
            'plan_name' => $response['shop']['plan_name']
        );
    }
    
    /**
     * Get products for SEO analysis
     */
    private function get_products($limit = 250) {
        $products = array();
        $params = array(
            'limit' => $limit,
            'fields' => 'id,title,body_html,handle,seo_title,seo_description,tags,vendor,product_type,created_at,updated_at,images,options,variants'
        );
        
        $response = $this->make_api_request('GET', '/admin/api/' . $this->api_version . '/products.json', $params);
        
        if (is_wp_error($response)) {
            return array();
        }
        
        foreach ($response['products'] as $product) {
            $products[] = array(
                'id' => $product['id'],
                'title' => $product['title'],
                'description' => $product['body_html'],
                'handle' => $product['handle'],
                'seo_title' => $product['seo_title'],
                'seo_description' => $product['seo_description'],
                'tags' => $product['tags'],
                'vendor' => $product['vendor'],
                'product_type' => $product['product_type'],
                'created_at' => $product['created_at'],
                'updated_at' => $product['updated_at'],
                'images' => $this->process_product_images($product['images']),
                'variants' => $this->process_product_variants($product['variants']),
                'options' => $product['options']
            );
        }
        
        return $products;
    }
    
    /**
     * Get pages for SEO analysis
     */
    private function get_pages($limit = 250) {
        $pages = array();
        $params = array(
            'limit' => $limit,
            'fields' => 'id,title,body_html,handle,seo_title,seo_description,created_at,updated_at'
        );
        
        $response = $this->make_api_request('GET', '/admin/api/' . $this->api_version . '/pages.json', $params);
        
        if (is_wp_error($response)) {
            return array();
        }
        
        foreach ($response['pages'] as $page) {
            $pages[] = array(
                'id' => $page['id'],
                'title' => $page['title'],
                'content' => $page['body_html'],
                'handle' => $page['handle'],
                'seo_title' => $page['seo_title'],
                'seo_description' => $page['seo_description'],
                'created_at' => $page['created_at'],
                'updated_at' => $page['updated_at']
            );
        }
        
        return $pages;
    }
    
    /**
     * Get blogs for SEO analysis
     */
    private function get_blogs($limit = 250) {
        $blogs = array();
        $params = array(
            'limit' => $limit,
            'fields' => 'id,title,handle,created_at,updated_at'
        );
        
        $response = $this->make_api_request('GET', '/admin/api/' . $this->api_version . '/blogs.json', $params);
        
        if (is_wp_error($response)) {
            return array();
        }
        
        foreach ($response['blogs'] as $blog) {
            $articles = $this->get_blog_articles($blog['id']);
            $blogs[] = array(
                'id' => $blog['id'],
                'title' => $blog['title'],
                'handle' => $blog['handle'],
                'created_at' => $blog['created_at'],
                'updated_at' => $blog['updated_at'],
                'articles' => $articles
            );
        }
        
        return $blogs;
    }
    
    /**
     * Get blog articles
     */
    private function get_blog_articles($blog_id, $limit = 250) {
        $articles = array();
        $params = array(
            'limit' => $limit,
            'fields' => 'id,title,body_html,handle,seo_title,seo_description,author,tags,created_at,updated_at,image'
        );
        
        $response = $this->make_api_request('GET', '/admin/api/' . $this->api_version . '/blogs/' . $blog_id . '/articles.json', $params);
        
        if (is_wp_error($response)) {
            return array();
        }
        
        foreach ($response['articles'] as $article) {
            $articles[] = array(
                'id' => $article['id'],
                'title' => $article['title'],
                'content' => $article['body_html'],
                'handle' => $article['handle'],
                'seo_title' => $article['seo_title'],
                'seo_description' => $article['seo_description'],
                'author' => $article['author'],
                'tags' => $article['tags'],
                'created_at' => $article['created_at'],
                'updated_at' => $article['updated_at'],
                'image' => $article['image']
            );
        }
        
        return $articles;
    }
    
    /**
     * Get collections for SEO analysis
     */
    private function get_collections($limit = 250) {
        $collections = array();
        $params = array(
            'limit' => $limit,
            'fields' => 'id,title,body_html,handle,seo_title,seo_description,created_at,updated_at,image'
        );
        
        $response = $this->make_api_request('GET', '/admin/api/' . $this->api_version . '/collections.json', $params);
        
        if (is_wp_error($response)) {
            return array();
        }
        
        foreach ($response['collections'] as $collection) {
            $collections[] = array(
                'id' => $collection['id'],
                'title' => $collection['title'],
                'description' => $collection['body_html'],
                'handle' => $collection['handle'],
                'seo_title' => $collection['seo_title'],
                'seo_description' => $collection['seo_description'],
                'created_at' => $collection['created_at'],
                'updated_at' => $collection['updated_at'],
                'image' => $collection['image']
            );
        }
        
        return $collections;
    }
    
    /**
     * Get meta fields for SEO analysis
     */
    private function get_meta_fields() {
        $meta_fields = array();
        $params = array(
            'limit' => 250
        );
        
        $response = $this->make_api_request('GET', '/admin/api/' . $this->api_version . '/metafields.json', $params);
        
        if (is_wp_error($response)) {
            return array();
        }
        
        foreach ($response['metafields'] as $metafield) {
            $meta_fields[] = array(
                'id' => $metafield['id'],
                'namespace' => $metafield['namespace'],
                'key' => $metafield['key'],
                'value' => $metafield['value'],
                'type' => $metafield['type'],
                'owner_resource' => $metafield['owner_resource'],
                'owner_id' => $metafield['owner_id']
            );
        }
        
        return $meta_fields;
    }
    
    /**
     * Process product images for SEO analysis
     */
    private function process_product_images($images) {
        $processed_images = array();
        
        foreach ($images as $image) {
            $processed_images[] = array(
                'id' => $image['id'],
                'src' => $image['src'],
                'alt' => $image['alt'],
                'width' => $image['width'],
                'height' => $image['height'],
                'position' => $image['position']
            );
        }
        
        return $processed_images;
    }
    
    /**
     * Process product variants for SEO analysis
     */
    private function process_product_variants($variants) {
        $processed_variants = array();
        
        foreach ($variants as $variant) {
            $processed_variants[] = array(
                'id' => $variant['id'],
                'title' => $variant['title'],
                'sku' => $variant['sku'],
                'price' => $variant['price'],
                'compare_at_price' => $variant['compare_at_price'],
                'inventory_quantity' => $variant['inventory_quantity'],
                'weight' => $variant['weight'],
                'weight_unit' => $variant['weight_unit']
            );
        }
        
        return $processed_variants;
    }
    
    /**
     * Update product SEO data
     */
    public function update_product_seo($product_id, $seo_data) {
        $update_data = array(
            'product' => array(
                'id' => $product_id,
                'seo_title' => $seo_data['seo_title'],
                'seo_description' => $seo_data['seo_description']
            )
        );
        
        $response = $this->make_api_request('PUT', '/admin/api/' . $this->api_version . '/products/' . $product_id . '.json', array(), $update_data);
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        return array(
            'success' => true,
            'product_id' => $product_id,
            'updated_data' => $seo_data
        );
    }
    
    /**
     * Update page SEO data
     */
    public function update_page_seo($page_id, $seo_data) {
        $update_data = array(
            'page' => array(
                'id' => $page_id,
                'seo_title' => $seo_data['seo_title'],
                'seo_description' => $seo_data['seo_description']
            )
        );
        
        $response = $this->make_api_request('PUT', '/admin/api/' . $this->api_version . '/pages/' . $page_id . '.json', array(), $update_data);
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        return array(
            'success' => true,
            'page_id' => $page_id,
            'updated_data' => $seo_data
        );
    }
    
    /**
     * Update article SEO data
     */
    public function update_article_seo($blog_id, $article_id, $seo_data) {
        $update_data = array(
            'article' => array(
                'id' => $article_id,
                'seo_title' => $seo_data['seo_title'],
                'seo_description' => $seo_data['seo_description']
            )
        );
        
        $response = $this->make_api_request('PUT', '/admin/api/' . $this->api_version . '/blogs/' . $blog_id . '/articles/' . $article_id . '.json', array(), $update_data);
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        return array(
            'success' => true,
            'article_id' => $article_id,
            'updated_data' => $seo_data
        );
    }
    
    /**
     * Update collection SEO data
     */
    public function update_collection_seo($collection_id, $seo_data) {
        $update_data = array(
            'collection' => array(
                'id' => $collection_id,
                'seo_title' => $seo_data['seo_title'],
                'seo_description' => $seo_data['seo_description']
            )
        );
        
        $response = $this->make_api_request('PUT', '/admin/api/' . $this->api_version . '/collections/' . $collection_id . '.json', array(), $update_data);
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        return array(
            'success' => true,
            'collection_id' => $collection_id,
            'updated_data' => $seo_data
        );
    }
    
    /**
     * Make API request to Shopify
     */
    private function make_api_request($method, $endpoint, $params = array(), $data = null) {
        $url = 'https://' . $this->store_url . $endpoint;
        
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        
        $headers = array(
            'X-Shopify-Access-Token' => $this->access_token,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        );
        
        $args = array(
            'method' => $method,
            'headers' => $headers,
            'timeout' => 30
        );
        
        if ($data && in_array($method, array('POST', 'PUT'))) {
            $args['body'] = json_encode($data);
        }
        
        $response = wp_remote_request($url, $args);
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        
        if ($response_code >= 400) {
            return new WP_Error('shopify_api_error', 'Shopify API Error: ' . $response_code . ' - ' . $response_body);
        }
        
        $decoded_response = json_decode($response_body, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return new WP_Error('shopify_json_error', 'Invalid JSON response from Shopify');
        }
        
        return $decoded_response;
    }
    
    /**
     * Get SEO analysis for Shopify store
     */
    public function analyze_store_seo() {
        $store_data = $this->get_store_data();
        
        if (empty($store_data)) {
            return new WP_Error('no_store_data', 'Unable to retrieve store data');
        }
        
        $analysis = array(
            'store_info' => $store_data['store_info'],
            'seo_score' => $this->calculate_seo_score($store_data),
            'recommendations' => $this->generate_recommendations($store_data),
            'technical_analysis' => $this->analyze_technical_seo($store_data),
            'content_analysis' => $this->analyze_content($store_data),
            'image_analysis' => $this->analyze_images($store_data)
        );
        
        return $analysis;
    }
    
    /**
     * Calculate overall SEO score
     */
    private function calculate_seo_score($store_data) {
        $score = 0;
        $total_checks = 0;
        
        // Check products
        if (!empty($store_data['products'])) {
            foreach ($store_data['products'] as $product) {
                $total_checks += 3;
                if (!empty($product['seo_title'])) $score += 1;
                if (!empty($product['seo_description'])) $score += 1;
                if (!empty($product['images'])) $score += 1;
            }
        }
        
        // Check pages
        if (!empty($store_data['pages'])) {
            foreach ($store_data['pages'] as $page) {
                $total_checks += 2;
                if (!empty($page['seo_title'])) $score += 1;
                if (!empty($page['seo_description'])) $score += 1;
            }
        }
        
        // Check collections
        if (!empty($store_data['collections'])) {
            foreach ($store_data['collections'] as $collection) {
                $total_checks += 2;
                if (!empty($collection['seo_title'])) $score += 1;
                if (!empty($collection['seo_description'])) $score += 1;
            }
        }
        
        return $total_checks > 0 ? round(($score / $total_checks) * 100) : 0;
    }
    
    /**
     * Generate SEO recommendations
     */
    private function generate_recommendations($store_data) {
        $recommendations = array();
        
        // Product recommendations
        if (!empty($store_data['products'])) {
            foreach ($store_data['products'] as $product) {
                if (empty($product['seo_title'])) {
                    $recommendations[] = array(
                        'type' => 'product_seo_title',
                        'target_id' => $product['id'],
                        'target_type' => 'product',
                        'priority' => 'high',
                        'title' => 'Add SEO Title for ' . $product['title'],
                        'description' => 'Product is missing SEO title which affects search rankings',
                        'ai_reasoning' => 'SEO titles are crucial for product visibility in search results',
                        'confidence_score' => 0.95,
                        'impact_score' => 8
                    );
                }
                
                if (empty($product['seo_description'])) {
                    $recommendations[] = array(
                        'type' => 'product_seo_description',
                        'target_id' => $product['id'],
                        'target_type' => 'product',
                        'priority' => 'high',
                        'title' => 'Add SEO Description for ' . $product['title'],
                        'description' => 'Product is missing SEO description which affects click-through rates',
                        'ai_reasoning' => 'SEO descriptions improve click-through rates from search results',
                        'confidence_score' => 0.90,
                        'impact_score' => 7
                    );
                }
            }
        }
        
        return $recommendations;
    }
    
    /**
     * Analyze technical SEO
     */
    private function analyze_technical_seo($store_data) {
        return array(
            'total_products' => count($store_data['products']),
            'total_pages' => count($store_data['pages']),
            'total_collections' => count($store_data['collections']),
            'meta_fields_count' => count($store_data['meta_fields']),
            'store_configured' => !empty($store_data['store_info']['name'])
        );
    }
    
    /**
     * Analyze content
     */
    private function analyze_content($store_data) {
        $total_words = 0;
        $products_with_content = 0;
        
        foreach ($store_data['products'] as $product) {
            if (!empty($product['description'])) {
                $total_words += str_word_count(strip_tags($product['description']));
                $products_with_content++;
            }
        }
        
        return array(
            'total_words' => $total_words,
            'products_with_content' => $products_with_content,
            'average_words_per_product' => $products_with_content > 0 ? round($total_words / $products_with_content) : 0
        );
    }
    
    /**
     * Analyze images
     */
    private function analyze_images($store_data) {
        $total_images = 0;
        $images_with_alt = 0;
        
        foreach ($store_data['products'] as $product) {
            if (!empty($product['images'])) {
                $total_images += count($product['images']);
                foreach ($product['images'] as $image) {
                    if (!empty($image['alt'])) {
                        $images_with_alt++;
                    }
                }
            }
        }
        
        return array(
            'total_images' => $total_images,
            'images_with_alt' => $images_with_alt,
            'alt_text_coverage' => $total_images > 0 ? round(($images_with_alt / $total_images) * 100) : 0
        );
    }
}
