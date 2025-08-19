// AI SEO Dashboard Configuration
// Update these settings to connect to your WordPress site

const DASHBOARD_CONFIG = {
    // WordPress Site Configuration
    wordpress: {
        // Replace with your actual WordPress site URL
        baseURL: 'http://digital-mareketing.local',
        
        // REST API endpoint for your plugin
        apiEndpoint: '/wp-json/ai-seo/v1',
        
        // Your API key (get this from WordPress admin)
        apiKey: 'test_api_key_12345',
        
        // Customer ID (if applicable)
        customerId: 'your-customer-id'
    },
    
    // Test Shopping Site Configuration
    testSite: {
        // Test shopping site URL
        baseURL: 'http://localhost/test-shopping-site',
        
        // Test API endpoint
        apiEndpoint: '/test-seo-connection.php',
        
        // Test API key
        apiKey: 'test_shopping_site_key',
        
        // Site type
        siteType: 'e-commerce'
    },
    
    // Shopify Configuration
    shopify: {
        // Shopify store URL
        storeUrl: '',
        
        // Access token for Shopify API
        accessToken: '',
        
        // API version
        apiVersion: '2024-01',
        
        // Enable Shopify integration
        enabled: false
    },
    
    // Dashboard Settings
    dashboard: {
        // Auto-refresh interval (in milliseconds)
        refreshInterval: 30000,
        
        // Enable real-time updates
        realTimeUpdates: true,
        
        // Theme settings
        theme: 'default',
        
        // Language
        language: 'en'
    },
    
    // API Endpoints (these match your WordPress plugin)
    endpoints: {
        // Dashboard data
        dashboardData: '/dashboard-data',
        
        // Site scanning
        scan: '/scan',
        
        // Apply changes
        applyChanges: '/apply-changes',
        
        // Get logs
        logs: '/logs',
        
        // Health check
        health: '/health'
    }
};

// Helper function to get full API URL
function getApiUrl(endpoint) {
    return DASHBOARD_CONFIG.wordpress.baseURL + 
           DASHBOARD_CONFIG.wordpress.apiEndpoint + 
           endpoint;
}

// Helper function to get API headers
function getApiHeaders() {
    return {
        'Content-Type': 'application/json',
        'X-API-Key': DASHBOARD_CONFIG.wordpress.apiKey,
        'X-Customer-ID': DASHBOARD_CONFIG.wordpress.customerId
    };
}

// Export configuration
window.DASHBOARD_CONFIG = DASHBOARD_CONFIG;
window.getApiUrl = getApiUrl;
window.getApiHeaders = getApiHeaders;
