<?php
/**
 * Test SEO Analysis for Shopping Site
 * This script demonstrates the AI SEO Optimizer analyzing the TechGear Pro shopping site
 */

// Include WordPress functions if available
if (file_exists('../../../wp-load.php')) {
    require_once('../../../wp-load.php');
}

// Set headers
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI SEO Optimizer - Shopping Site Test</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #3498db;
        }
        .header h1 {
            color: #2c3e50;
            margin-bottom: 10px;
        }
        .header p {
            color: #7f8c8d;
            font-size: 1.1em;
        }
        .test-section {
            margin-bottom: 30px;
            padding: 20px;
            border: 1px solid #ecf0f1;
            border-radius: 8px;
        }
        .test-section h2 {
            color: #2c3e50;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }
        .test-section h2 i {
            margin-right: 10px;
            color: #3498db;
        }
        .btn {
            background: #3498db;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1em;
            margin: 5px;
            text-decoration: none;
            display: inline-block;
        }
        .btn:hover {
            background: #2980b9;
        }
        .btn-success {
            background: #27ae60;
        }
        .btn-success:hover {
            background: #229954;
        }
        .btn-warning {
            background: #f39c12;
        }
        .btn-warning:hover {
            background: #e67e22;
        }
        .results {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-top: 15px;
            white-space: pre-wrap;
            font-family: 'Courier New', monospace;
            font-size: 0.9em;
            max-height: 400px;
            overflow-y: auto;
        }
        .status {
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
        }
        .status.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .status.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .status.info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-left: 4px solid #3498db;
        }
        .card h3 {
            margin-top: 0;
            color: #2c3e50;
        }
        .score {
            font-size: 2em;
            font-weight: bold;
            color: #27ae60;
            text-align: center;
            margin: 10px 0;
        }
        .recommendation {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
        }
        .recommendation h4 {
            margin: 0 0 5px 0;
            color: #856404;
        }
        .recommendation p {
            margin: 0;
            color: #856404;
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-search"></i> AI SEO Optimizer - Shopping Site Test</h1>
            <p>Testing SEO analysis and optimization for TechGear Pro e-commerce site</p>
        </div>

        <div class="test-section">
            <h2><i class="fas fa-globe"></i> Test Site Information</h2>
            <p><strong>Site Name:</strong> TechGear Pro</p>
            <p><strong>URL:</strong> <a href="test-shopping-site/index.html" target="_blank">test-shopping-site/index.html</a></p>
            <p><strong>Type:</strong> E-commerce (Electronics & Gadgets)</p>
            <p><strong>Products:</strong> 6 premium electronics products</p>
            <p><strong>Images:</strong> 7 product and hero images</p>
            
            <div class="grid">
                <div class="card">
                    <h3>Current SEO Score</h3>
                    <div class="score">87/100</div>
                    <p>Good foundation with room for improvement</p>
                </div>
                <div class="card">
                    <h3>AI Recommendations</h3>
                    <div class="score">5</div>
                    <p>High-priority optimizations available</p>
                </div>
                <div class="card">
                    <h3>Content Quality</h3>
                    <div class="score">85%</div>
                    <p>Well-written product descriptions</p>
                </div>
            </div>
        </div>

        <div class="test-section">
            <h2><i class="fas fa-cogs"></i> SEO Analysis Tests</h2>
            
            <button class="btn" onclick="runFullAnalysis()">
                <i class="fas fa-search"></i> Run Full SEO Analysis
            </button>
            
            <button class="btn btn-success" onclick="getRecommendations()">
                <i class="fas fa-lightbulb"></i> Get AI Recommendations
            </button>
            
            <button class="btn btn-warning" onclick="testMetaTags()">
                <i class="fas fa-tags"></i> Test Meta Tags
            </button>
            
            <button class="btn" onclick="testImageOptimization()">
                <i class="fas fa-image"></i> Test Image Optimization
            </button>
            
            <button class="btn" onclick="testPerformance()">
                <i class="fas fa-tachometer-alt"></i> Test Performance
            </button>
            
            <div id="results" class="results" style="display: none;"></div>
        </div>

        <div class="test-section">
            <h2><i class="fas fa-robot"></i> AI Recommendations Preview</h2>
            
            <div class="recommendation">
                <h4><i class="fas fa-star"></i> High Priority: Optimize Meta Description</h4>
                <p>Current meta description is good but could be more compelling for click-through rates. 
                Suggested improvement: "Shop premium electronics & gadgets at TechGear Pro! iPhone 15 Pro Max, MacBook Pro, Apple Watch & more. Free shipping over $50. 30-day returns. Best prices guaranteed!"</p>
                <p><strong>Impact:</strong> +3 SEO points | <strong>Confidence:</strong> 95%</p>
            </div>
            
            <div class="recommendation">
                <h4><i class="fas fa-star"></i> High Priority: Add Product Schema Markup</h4>
                <p>Missing structured data for products. Add JSON-LD schema for products, organization, and breadcrumbs to improve search engine understanding.</p>
                <p><strong>Impact:</strong> +4 SEO points | <strong>Confidence:</strong> 90%</p>
            </div>
            
            <div class="recommendation">
                <h4><i class="fas fa-star-half-alt"></i> Medium Priority: Add Alt Text to Hero Image</h4>
                <p>Hero image is missing alt text which affects accessibility and SEO. Add descriptive alt text for better user experience and search engine optimization.</p>
                <p><strong>Impact:</strong> +1 SEO point | <strong>Confidence:</strong> 92%</p>
            </div>
            
            <div class="recommendation">
                <h4><i class="fas fa-star-half-alt"></i> Medium Priority: Add H2 Subheadings</h4>
                <p>Product descriptions could benefit from better heading structure. Add H2 headings like 'Smartphone Collection', 'Laptop Selection', 'Smart Gadgets' for better content organization.</p>
                <p><strong>Impact:</strong> +2 SEO points | <strong>Confidence:</strong> 88%</p>
            </div>
            
            <div class="recommendation">
                <h4><i class="far fa-star"></i> Low Priority: Add Internal Links</h4>
                <p>No internal linking structure found. Add links to product categories, about page, contact page to improve site structure and user navigation.</p>
                <p><strong>Impact:</strong> +1 SEO point | <strong>Confidence:</strong> 85%</p>
            </div>
        </div>

        <div class="test-section">
            <h2><i class="fas fa-link"></i> Quick Actions</h2>
            
            <a href="test-shopping-site/index.html" target="_blank" class="btn">
                <i class="fas fa-external-link-alt"></i> View Shopping Site
            </a>
            
            <a href="dashboard/index.html" target="_blank" class="btn">
                <i class="fas fa-tachometer-alt"></i> Open SEO Dashboard
            </a>
            
            <a href="test-shopping-site/test-seo-connection.php?action=analyze" target="_blank" class="btn">
                <i class="fas fa-code"></i> View API Response
            </a>
        </div>
    </div>

    <script>
        function showResults(data) {
            const resultsDiv = document.getElementById('results');
            resultsDiv.style.display = 'block';
            resultsDiv.textContent = JSON.stringify(data, null, 2);
        }

        function showStatus(message, type = 'info') {
            const statusDiv = document.createElement('div');
            statusDiv.className = `status ${type}`;
            statusDiv.innerHTML = `<i class="fas fa-info-circle"></i> ${message}`;
            document.getElementById('results').parentNode.insertBefore(statusDiv, document.getElementById('results'));
        }

        async function runFullAnalysis() {
            showStatus('Running full SEO analysis...', 'info');
            try {
                const response = await fetch('test-shopping-site/test-seo-connection.php?action=analyze');
                const data = await response.json();
                showResults(data);
                showStatus('Full SEO analysis completed successfully!', 'success');
            } catch (error) {
                showStatus('Error running analysis: ' + error.message, 'error');
            }
        }

        async function getRecommendations() {
            showStatus('Fetching AI recommendations...', 'info');
            try {
                const response = await fetch('test-shopping-site/test-seo-connection.php?action=recommendations');
                const data = await response.json();
                showResults(data);
                showStatus('AI recommendations loaded successfully!', 'success');
            } catch (error) {
                showStatus('Error fetching recommendations: ' + error.message, 'error');
            }
        }

        async function testMetaTags() {
            showStatus('Testing meta tags...', 'info');
            try {
                const response = await fetch('test-shopping-site/test-seo-connection.php?action=analyze');
                const data = await response.json();
                const metaAnalysis = data.technical_analysis.meta_tags;
                showResults(metaAnalysis);
                showStatus('Meta tags analysis completed!', 'success');
            } catch (error) {
                showStatus('Error testing meta tags: ' + error.message, 'error');
            }
        }

        async function testImageOptimization() {
            showStatus('Testing image optimization...', 'info');
            try {
                const response = await fetch('test-shopping-site/test-seo-connection.php?action=analyze');
                const data = await response.json();
                const imageAnalysis = data.technical_analysis.images;
                showResults(imageAnalysis);
                showStatus('Image optimization analysis completed!', 'success');
            } catch (error) {
                showStatus('Error testing image optimization: ' + error.message, 'error');
            }
        }

        async function testPerformance() {
            showStatus('Testing performance...', 'info');
            try {
                const response = await fetch('test-shopping-site/test-seo-connection.php?action=analyze');
                const data = await response.json();
                const performanceAnalysis = data.technical_analysis.performance;
                showResults(performanceAnalysis);
                showStatus('Performance analysis completed!', 'success');
            } catch (error) {
                showStatus('Error testing performance: ' + error.message, 'error');
            }
        }
    </script>
</body>
</html>
