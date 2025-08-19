<?php
/**
 * Shopify Data Retrieval Methods
 * 
 * @package AI_SEO_Optimizer
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class AI_SEO_Shopify_Data {
    
    /**
     * Get store information
     */
    public static function get_store_info($connector) {
        $response = $connector->make_api_request('GET', '/admin/api/2024-01/shop.json');
        
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
    public static function get_products($connector, $limit = 250) {
        $products = array();
        $params = array(
            'limit' => $limit,
            'fields' => 'id,title,body_html,handle,seo_title,seo_description,tags,vendor,product_type,created_at,updated_at,images,options,variants'
        );
        
        $response = $connector->make_api_request('GET', '/admin/api/2024-01/products.json', $params);
        
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
                'images' => self::process_product_images($product['images']),
                'variants' => self::process_product_variants($product['variants'])
            );
        }
        
        return $products;
    }
    
    /**
     * Get pages for SEO analysis
     */
    public static function get_pages($connector, $limit = 250) {
        $pages = array();
        $params = array(
            'limit' => $limit,
            'fields' => 'id,title,body_html,handle,seo_title,seo_description,created_at,updated_at'
        );
        
        $response = $connector->make_api_request('GET', '/admin/api/2024-01/pages.json', $params);
        
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
     * Get collections for SEO analysis
     */
    public static function get_collections($connector, $limit = 250) {
        $collections = array();
        $params = array(
            'limit' => $limit,
            'fields' => 'id,title,body_html,handle,seo_title,seo_description,created_at,updated_at,image'
        );
        
        $response = $connector->make_api_request('GET', '/admin/api/2024-01/collections.json', $params);
        
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
     * Process product images for SEO analysis
     */
    private static function process_product_images($images) {
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
    private static function process_product_variants($variants) {
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
}
