<?php
/**
 * Shopify SEO Analysis and Update Methods
 * 
 * @package AI_SEO_Optimizer
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class AI_SEO_Shopify_SEO {
    
    /**
     * Update product SEO data
     */
    public static function update_product_seo($connector, $product_id, $seo_data) {
        $update_data = array(
            'product' => array(
                'id' => $product_id,
                'seo_title' => $seo_data['seo_title'],
                'seo_description' => $seo_data['seo_description']
            )
        );
        
        $response = $connector->make_api_request('PUT', '/admin/api/2024-01/products/' . $product_id . '.json', array(), $update_data);
        
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
    public static function update_page_seo($connector, $page_id, $seo_data) {
        $update_data = array(
            'page' => array(
                'id' => $page_id,
                'seo_title' => $seo_data['seo_title'],
                'seo_description' => $seo_data['seo_description']
            )
        );
        
        $response = $connector->make_api_request('PUT', '/admin/api/2024-01/pages/' . $page_id . '.json', array(), $update_data);
        
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
     * Update collection SEO data
     */
    public static function update_collection_seo($connector, $collection_id, $seo_data) {
        $update_data = array(
            'collection' => array(
                'id' => $collection_id,
                'seo_title' => $seo_data['seo_title'],
                'seo_description' => $seo_data['seo_description']
            )
        );
        
        $response = $connector->make_api_request('PUT', '/admin/api/2024-01/collections/' . $collection_id . '.json', array(), $update_data);
        
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
     * Get SEO analysis for Shopify store
     */
    public static function analyze_store_seo($store_data) {
        if (empty($store_data)) {
            return new WP_Error('no_store_data', 'Unable to retrieve store data');
        }
        
        $analysis = array(
            'store_info' => $store_data['store_info'],
            'seo_score' => self::calculate_seo_score($store_data),
            'recommendations' => self::generate_recommendations($store_data),
            'technical_analysis' => self::analyze_technical_seo($store_data),
            'content_analysis' => self::analyze_content($store_data),
            'image_analysis' => self::analyze_images($store_data)
        );
        
        return $analysis;
    }
    
    /**
     * Calculate overall SEO score
     */
    private static function calculate_seo_score($store_data) {
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
    private static function generate_recommendations($store_data) {
        $recommendations = array();
        
        // Product recommendations
        if (!empty($store_data['products'])) {
            foreach ($store_data['products'] as $product) {
                if (empty($product['seo_title'])) {
                    $recommendations[] = array(
                        'id' => 'shopify_product_' . $product['id'] . '_seo_title',
                        'type' => 'product_seo_title',
                        'target_id' => $product['id'],
                        'target_type' => 'product',
                        'priority' => 'high',
                        'title' => 'Add SEO Title for ' . $product['title'],
                        'description' => 'Product is missing SEO title which affects search rankings',
                        'ai_reasoning' => 'SEO titles are crucial for product visibility in search results. Google uses this for SERP display.',
                        'confidence_score' => 0.95,
                        'impact_score' => 8,
                        'seo_points' => 3,
                        'current_value' => 'No SEO title',
                        'suggested_value' => $product['title'] . ' - Best Price & Reviews'
                    );
                }
                
                if (empty($product['seo_description'])) {
                    $recommendations[] = array(
                        'id' => 'shopify_product_' . $product['id'] . '_seo_description',
                        'type' => 'product_seo_description',
                        'target_id' => $product['id'],
                        'target_type' => 'product',
                        'priority' => 'high',
                        'title' => 'Add SEO Description for ' . $product['title'],
                        'description' => 'Product is missing SEO description which affects click-through rates',
                        'ai_reasoning' => 'SEO descriptions improve click-through rates from search results. They should be compelling and include key benefits.',
                        'confidence_score' => 0.90,
                        'impact_score' => 7,
                        'seo_points' => 2,
                        'current_value' => 'No SEO description',
                        'suggested_value' => 'Shop ' . $product['title'] . ' online. Free shipping, 30-day returns. Best prices guaranteed!'
                    );
                }
            }
        }
        
        // Page recommendations
        if (!empty($store_data['pages'])) {
            foreach ($store_data['pages'] as $page) {
                if (empty($page['seo_title'])) {
                    $recommendations[] = array(
                        'id' => 'shopify_page_' . $page['id'] . '_seo_title',
                        'type' => 'page_seo_title',
                        'target_id' => $page['id'],
                        'target_type' => 'page',
                        'priority' => 'medium',
                        'title' => 'Add SEO Title for ' . $page['title'],
                        'description' => 'Page is missing SEO title which affects search rankings',
                        'ai_reasoning' => 'Page SEO titles help with search visibility and click-through rates.',
                        'confidence_score' => 0.88,
                        'impact_score' => 6,
                        'seo_points' => 2,
                        'current_value' => 'No SEO title',
                        'suggested_value' => $page['title'] . ' - Complete Guide'
                    );
                }
            }
        }
        
        return $recommendations;
    }
    
    /**
     * Analyze technical SEO
     */
    private static function analyze_technical_seo($store_data) {
        return array(
            'total_products' => count($store_data['products']),
            'total_pages' => count($store_data['pages']),
            'total_collections' => count($store_data['collections']),
            'store_configured' => !empty($store_data['store_info']['name']),
            'products_with_seo' => self::count_items_with_seo($store_data['products']),
            'pages_with_seo' => self::count_items_with_seo($store_data['pages']),
            'collections_with_seo' => self::count_items_with_seo($store_data['collections'])
        );
    }
    
    /**
     * Analyze content
     */
    private static function analyze_content($store_data) {
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
            'average_words_per_product' => $products_with_content > 0 ? round($total_words / $products_with_content) : 0,
            'content_quality_score' => self::calculate_content_quality($store_data)
        );
    }
    
    /**
     * Analyze images
     */
    private static function analyze_images($store_data) {
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
            'alt_text_coverage' => $total_images > 0 ? round(($images_with_alt / $total_images) * 100) : 0,
            'image_optimization_score' => self::calculate_image_score($store_data)
        );
    }
    
    /**
     * Count items with SEO data
     */
    private static function count_items_with_seo($items) {
        $count = 0;
        foreach ($items as $item) {
            if (!empty($item['seo_title']) || !empty($item['seo_description'])) {
                $count++;
            }
        }
        return $count;
    }
    
    /**
     * Calculate content quality score
     */
    private static function calculate_content_quality($store_data) {
        $score = 0;
        $total_products = count($store_data['products']);
        
        if ($total_products === 0) return 0;
        
        foreach ($store_data['products'] as $product) {
            if (!empty($product['description'])) {
                $word_count = str_word_count(strip_tags($product['description']));
                if ($word_count > 50) $score += 1;
                if ($word_count > 100) $score += 1;
            }
        }
        
        return round(($score / ($total_products * 2)) * 100);
    }
    
    /**
     * Calculate image optimization score
     */
    private static function calculate_image_score($store_data) {
        $total_images = 0;
        $optimized_images = 0;
        
        foreach ($store_data['products'] as $product) {
            if (!empty($product['images'])) {
                $total_images += count($product['images']);
                foreach ($product['images'] as $image) {
                    if (!empty($image['alt']) && !empty($image['width']) && !empty($image['height'])) {
                        $optimized_images++;
                    }
                }
            }
        }
        
        return $total_images > 0 ? round(($optimized_images / $total_images) * 100) : 0;
    }
}
