// Debug API Configuration
console.log('=== API Configuration Debug ===');

// Check if config is loaded
if (window.DASHBOARD_CONFIG) {
    console.log('✅ DASHBOARD_CONFIG loaded');
    console.log('Base URL:', window.DASHBOARD_CONFIG.wordpress.baseURL);
    console.log('API Endpoint:', window.DASHBOARD_CONFIG.wordpress.apiEndpoint);
    console.log('API Key:', window.DASHBOARD_CONFIG.wordpress.apiKey);
    console.log('Full API URL:', window.DASHBOARD_CONFIG.wordpress.baseURL + window.DASHBOARD_CONFIG.wordpress.apiEndpoint);
} else {
    console.log('❌ DASHBOARD_CONFIG not loaded');
}

// Test API connector
if (typeof AISEODashboardAPI !== 'undefined') {
    console.log('✅ AISEODashboardAPI class available');
    const api = new AISEODashboardAPI();
    console.log('API Base URL:', api.baseURL);
    console.log('API Endpoints:', api.endpoints);
} else {
    console.log('❌ AISEODashboardAPI class not available');
}

// Test API calls
async function testAPI() {
    try {
        const api = new AISEODashboardAPI();
        
        console.log('Testing health endpoint...');
        const healthResult = await api.makeRequest('/health');
        console.log('✅ Health API working:', healthResult);
        
        console.log('Testing dashboard data endpoint...');
        const dashboardResult = await api.makeRequest('/dashboard-data');
        console.log('✅ Dashboard API working:', dashboardResult);
        
    } catch (error) {
        console.log('❌ API Error:', error.message);
    }
}

// Run test when page loads
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', testAPI);
} else {
    testAPI();
}
