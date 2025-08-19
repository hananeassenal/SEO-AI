<?php
/**
 * Test SEO Connection for TechGear Pro Shopping Site
 * This script tests the connection between the shopping site and the AI SEO Optimizer dashboard
 */

// Set headers for JSON response
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Load the site configuration
$config_file = __DIR__ . '/config.json';
$config = json_decode(file_get_contents($config_file), true);

// Function to analyze the shopping site
function analyzeShoppingSite($config) {
    $analysis = [
        'timestamp' => date('Y-m-d H:i:s'),
        'site_info' => $config['site_info'],
        'seo_score' => 87,
        'recommendations' => $config['ai_recommendations'],
        'technical_analysis' => [
            'meta_tags' => analyzeMetaTags(),
            'images' => analyzeImages(),
            'content' => analyzeContent(),
            'performance' => analyzePerformance(),
            'mobile_friendly' => true,
            'ssl_enabled' => false
        ],
        'content_analysis' => $config['content_analysis'],
        'keyword_analysis' => analyzeKeywords($config['seo_settings']['target_keywords']),
        'competitor_analysis' => analyzeCompetitors($config['seo_settings']['competitors'])
    ];
    
    return $analysis;
}

function analyzeMetaTags() {
    return [
        'title' => [
            'present' => true,
            'length' => 65,
            'optimal_length' => '50-60 characters',
            'score' => 85,
            'suggestion' => 'Title is good but could be more action-oriented'
        ],
        'description' => [
            'present' => true,
            'length' => 155,
            'optimal_length' => '150-160 characters',
            'score' => 90,
            'suggestion' => 'Description is well-optimized'
        ],
        'keywords' => [
            'present' => true,
            'count' => 7,
            'score' => 80,
            'suggestion' => 'Consider adding more long-tail keywords'
        ],
        'open_graph' => [
            'present' => true,
            'complete' => true,
            'score' => 95
        ]
    ];
}

function analyzeImages() {
    return [
        'total_images' => 7,
        'with_alt_text' => 6,
        'missing_alt' => 1,
        'optimized' => 5,
        'needs_optimization' => 2,
        'score' => 82,
        'recommendations' => [
            'Add alt text to hero image',
            'Compress product images for faster loading',
            'Use WebP format for better performance'
        ]
    ];
}

function analyzeContent() {
    return [
        'word_count' => 1250,
        'readability_score' => 78,
        'keyword_density' => [
            'electronics' => 12,
            'smartphones' => 8,
            'laptops' => 6,
            'gadgets' => 10
        ],
        'heading_structure' => [
            'h1_count' => 1,
            'h2_count' => 2,
            'h3_count' => 8,
            'score' => 75,
            'suggestion' => 'Add more H2 headings for better structure'
        ],
        'internal_links' => [
            'count' => 0,
            'score' => 0,
            'suggestion' => 'Add internal links to improve site structure'
        ]
    ];
}

function analyzePerformance() {
    return [
        'page_speed' => 85,
        'mobile_speed' => 82,
        'desktop_speed' => 88,
        'issues' => [
            'Large image files',
            'No image compression',
            'Missing browser caching'
        ],
        'recommendations' => [
            'Enable GZIP compression',
            'Optimize images',
            'Implement browser caching',
            'Minify CSS and JavaScript'
        ]
    ];
}

function analyzeKeywords($target_keywords) {
    $analysis = [];
    foreach ($target_keywords as $keyword) {
        $analysis[$keyword] = [
            'density' => rand(5, 15),
            'position' => rand(1, 10),
            'competition' => rand(20, 80),
            'search_volume' => rand(1000, 50000),
            'difficulty' => rand(30, 70)
        ];
    }
    return $analysis;
}

function analyzeCompetitors($competitors) {
    $analysis = [];
    foreach ($competitors as $competitor) {
        $analysis[$competitor] = [
            'domain_authority' => rand(50, 95),
            'backlinks' => rand(1000, 100000),
            'organic_keywords' => rand(1000, 50000),
            'organic_traffic' => rand(10000, 500000),
            'seo_score' => rand(60, 95)
        ];
    }
    return $analysis;
}

// Handle different request types
$action = $_GET['action'] ?? 'analyze';

switch ($action) {
    case 'analyze':
        $result = analyzeShoppingSite($config);
        break;
        
    case 'recommendations':
        $result = [
            'recommendations' => $config['ai_recommendations'],
            'total_count' => count($config['ai_recommendations']),
            'high_priority' => array_filter($config['ai_recommendations'], function($rec) {
                return $rec['priority'] === 'high';
            }),
            'medium_priority' => array_filter($config['ai_recommendations'], function($rec) {
                return $rec['priority'] === 'medium';
            }),
            'low_priority' => array_filter($config['ai_recommendations'], function($rec) {
                return $rec['priority'] === 'low';
            })
        ];
        break;
        
    case 'apply_recommendation':
        $recommendation_id = $_POST['recommendation_id'] ?? '';
        $result = [
            'success' => true,
            'message' => "Recommendation $recommendation_id applied successfully",
            'changes_made' => [
                'meta_description_updated' => true,
                'alt_text_added' => true,
                'schema_markup_added' => true
            ],
            'new_seo_score' => 91
        ];
        break;
        
    default:
        $result = [
            'error' => 'Invalid action',
            'available_actions' => ['analyze', 'recommendations', 'apply_recommendation']
        ];
}

// Return JSON response
echo json_encode($result, JSON_PRETTY_PRINT);
?>
