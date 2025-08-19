// AI SEO Dashboard API Connector
// This file handles communication with the WordPress plugin REST API

class AISEODashboardAPI {
    constructor(config = {}) {
        // Use configuration from config.js if available
        if (window.DASHBOARD_CONFIG) {
            this.baseURL = window.DASHBOARD_CONFIG.wordpress.baseURL + window.DASHBOARD_CONFIG.wordpress.apiEndpoint;
            this.apiKey = window.DASHBOARD_CONFIG.wordpress.apiKey;
            this.customerId = window.DASHBOARD_CONFIG.wordpress.customerId;
        } else {
            this.baseURL = config.baseURL || 'http://localhost/digital-mareketing/wp-json/ai-seo/v1';
            this.apiKey = config.apiKey || '';
            this.customerId = config.customerId || '';
        }
        this.timeout = config.timeout || 10000;
        
        // Use endpoints that match your WordPress plugin
        this.endpoints = {
            dashboardData: '/dashboard-data',
            scan: '/scan',
            applyChanges: '/apply-changes',
            logs: '/logs',
            health: '/health'
        };
    }

    // Set authentication credentials
    setAuth(apiKey, customerId) {
        this.apiKey = apiKey;
        this.customerId = customerId;
    }

    // Make API request
    async makeRequest(endpoint, options = {}) {
        const url = `${this.baseURL}${endpoint}`;
        
        const defaultOptions = {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-API-Key': this.apiKey,
                'X-Customer-ID': this.customerId
            },
            timeout: this.timeout
        };

        const requestOptions = { ...defaultOptions, ...options };

        try {
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), this.timeout);
            
            const response = await fetch(url, {
                ...requestOptions,
                signal: controller.signal
            });

            clearTimeout(timeoutId);

            if (!response.ok) {
                throw new Error(`API Error: ${response.status} ${response.statusText}`);
            }

            return await response.json();
        } catch (error) {
            console.error('API Request failed:', error);
            throw error;
        }
    }

    // Get dashboard data (matches your WordPress plugin)
    async getDashboardData() {
        return this.makeRequest(this.endpoints.dashboardData);
    }

    // Get logs (matches your WordPress plugin)
    async getLogs(page = 1, perPage = 20) {
        return this.makeRequest(`${this.endpoints.logs}?page=${page}&per_page=${perPage}`);
    }

    // Run SEO scan on a site
    async runScan(siteId) {
        return this.makeRequest(this.endpoints.scan, {
            method: 'GET'
        });
    }

    // Approve pending changes
    async approveChanges(changeIds) {
        return this.makeRequest(this.endpoints.approve, {
            method: 'POST',
            body: JSON.stringify({ change_ids: changeIds })
        });
    }

    // Get settings
    async getSettings() {
        return this.makeRequest(this.endpoints.settings);
    }

    // Update settings
    async updateSettings(settings) {
        return this.makeRequest(this.endpoints.settings, {
            method: 'PUT',
            body: JSON.stringify(settings)
        });
    }

    // Create backup
    async createBackup(siteId) {
        return this.makeRequest(this.endpoints.backup, {
            method: 'POST',
            body: JSON.stringify({ site_id: siteId })
        });
    }

    // Restore from backup
    async restoreBackup(backupId) {
        return this.makeRequest(this.endpoints.restore, {
            method: 'POST',
            body: JSON.stringify({ backup_id: backupId })
        });
    }

    // Add new site
    async addSite(siteData) {
        return this.makeRequest(this.endpoints.sites, {
            method: 'POST',
            body: JSON.stringify(siteData)
        });
    }

    // Update site
    async updateSite(siteId, siteData) {
        return this.makeRequest(`${this.endpoints.sites}/${siteId}`, {
            method: 'PUT',
            body: JSON.stringify(siteData)
        });
    }

    // Delete site
    async deleteSite(siteId) {
        return this.makeRequest(`${this.endpoints.sites}/${siteId}`, {
            method: 'DELETE'
        });
    }

    // Get site details
    async getSiteDetails(siteId) {
        return this.makeRequest(`${this.endpoints.sites}/${siteId}`);
    }

    // Get site SEO score
    async getSiteSEOScore(siteId) {
        return this.makeRequest(`${this.endpoints.sites}/${siteId}/seo-score`);
    }

    // Get site optimization history
    async getSiteHistory(siteId, limit = 20) {
        return this.makeRequest(`${this.endpoints.sites}/${siteId}/history?limit=${limit}`);
    }

    // Test API connection
    async testConnection() {
        try {
            const response = await this.makeRequest('/health');
            return {
                success: true,
                message: 'API connection successful',
                data: response
            };
        } catch (error) {
            return {
                success: false,
                message: 'API connection failed',
                error: error.message
            };
        }
    }

    // Get API status
    async getAPIStatus() {
        return this.makeRequest('/status');
    }

    // Upload site credentials
    async uploadCredentials(siteId, credentials) {
        return this.makeRequest(`${this.endpoints.sites}/${siteId}/credentials`, {
            method: 'POST',
            body: JSON.stringify(credentials)
        });
    }

    // Get automation rules
    async getAutomationRules() {
        return this.makeRequest('/automation/rules');
    }

    // Update automation rules
    async updateAutomationRules(rules) {
        return this.makeRequest('/automation/rules', {
            method: 'PUT',
            body: JSON.stringify(rules)
        });
    }

    // Get audit logs
    async getAuditLogs(filters = {}) {
        const queryParams = new URLSearchParams(filters).toString();
        return this.makeRequest(`/audit-logs?${queryParams}`);
    }

    // Export data
    async exportData(type, filters = {}) {
        const queryParams = new URLSearchParams({ type, ...filters }).toString();
        return this.makeRequest(`/export?${queryParams}`);
    }

    // Get real-time updates (WebSocket alternative)
    async getRealTimeUpdates(lastUpdate = null) {
        const params = lastUpdate ? `?last_update=${lastUpdate}` : '';
        return this.makeRequest(`/real-time${params}`);
    }
}

// Configuration helper
class DashboardConfig {
    constructor() {
        this.config = this.loadConfig();
    }

    loadConfig() {
        // Try to load from localStorage
        const saved = localStorage.getItem('ai_seo_dashboard_config');
        if (saved) {
            return JSON.parse(saved);
        }

        // Default configuration
        return {
            baseURL: 'https://your-wordpress-site.com/wp-json/ai-seo-optimizer/v1',
            apiKey: '',
            customerId: '',
            timeout: 10000,
            autoRefresh: true,
            refreshInterval: 30000,
            theme: 'default',
            language: 'en'
        };
    }

    saveConfig() {
        localStorage.setItem('ai_seo_dashboard_config', JSON.stringify(this.config));
    }

    updateConfig(newConfig) {
        this.config = { ...this.config, ...newConfig };
        this.saveConfig();
    }

    get(key) {
        return this.config[key];
    }

    set(key, value) {
        this.config[key] = value;
        this.saveConfig();
    }
}

// Error handling utility
class APIErrorHandler {
    static handle(error, context = '') {
        console.error(`API Error in ${context}:`, error);

        let userMessage = 'An error occurred while processing your request.';

        if (error.name === 'AbortError') {
            userMessage = 'Request timed out. Please try again.';
        } else if (error.message.includes('401')) {
            userMessage = 'Authentication failed. Please check your API credentials.';
        } else if (error.message.includes('403')) {
            userMessage = 'Access denied. Please check your permissions.';
        } else if (error.message.includes('404')) {
            userMessage = 'Resource not found. Please check the URL.';
        } else if (error.message.includes('500')) {
            userMessage = 'Server error. Please try again later.';
        }

        return {
            error: true,
            message: userMessage,
            details: error.message,
            context
        };
    }
}

// Usage example:
/*
// Initialize API connector
const api = new AISEODashboardAPI({
    baseURL: 'https://your-site.com/wp-json/ai-seo-optimizer/v1',
    apiKey: 'your-api-key',
    customerId: 'your-customer-id'
});

// Test connection
api.testConnection().then(result => {
    if (result.success) {
        console.log('API connected successfully!');
    } else {
        console.error('API connection failed:', result.error);
    }
});

// Get dashboard data
async function loadDashboardData() {
    try {
        const [stats, sites, activity] = await Promise.all([
            api.getStats(),
            api.getSites(),
            api.getActivity()
        ]);
        
        // Update dashboard with real data
        updateDashboard(stats, sites, activity);
    } catch (error) {
        const errorInfo = APIErrorHandler.handle(error, 'loadDashboardData');
        showNotification(errorInfo.message, 'error');
    }
}
*/

// Export for global access
window.AISEODashboardAPI = AISEODashboardAPI;
window.DashboardConfig = DashboardConfig;
window.APIErrorHandler = APIErrorHandler;
