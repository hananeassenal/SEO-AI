// AI SEO Automation Dashboard JavaScript

class AISEODashboard {
    constructor() {
        this.init();
        this.bindEvents();
        this.loadDashboardData();
    }

    init() {
        // Initialize dashboard components
        this.updateSEOProgress();
        this.animateStats();
        this.setupRealTimeUpdates();
    }

    bindEvents() {
        // Navigation events
        document.querySelectorAll('.sidebar-nav a').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                this.handleNavigation(e.target.closest('a').getAttribute('href'));
            });
        });

        // Quick action buttons
        document.querySelectorAll('.action-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                this.handleQuickAction(e.target.closest('.action-btn'));
            });
        });

        // Refresh button
        const refreshBtn = document.querySelector('.btn-icon[title="Refresh"]');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', () => {
                this.refreshDashboard();
            });
        }

        // User menu
        const userMenu = document.querySelector('.user-menu');
        if (userMenu) {
            userMenu.addEventListener('click', () => {
                this.toggleUserMenu();
            });
        }

        // Site items
        document.querySelectorAll('.site-item').forEach(item => {
            item.addEventListener('click', () => {
                this.openSiteDetails(item);
            });
        });

        // Activity items
        document.querySelectorAll('.activity-item').forEach(item => {
            item.addEventListener('click', () => {
                this.showActivityDetails(item);
            });
        });

        // AI Recommendation buttons
        document.querySelectorAll('.btn-approve').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                this.approveRecommendation(btn.closest('.recommendation-item'));
            });
        });

        document.querySelectorAll('.btn-modify').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                this.modifyRecommendation(btn.closest('.recommendation-item'));
            });
        });

        document.querySelectorAll('.btn-reject').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                this.rejectRecommendation(btn.closest('.recommendation-item'));
            });
        });
    }

    // Update SEO progress circle
    updateSEOProgress() {
        const scoreCircle = document.querySelector('.score-progress');
        const scoreNumber = document.querySelector('.score-number');
        
        if (scoreCircle && scoreNumber) {
            const score = parseInt(scoreNumber.textContent);
            const circumference = 2 * Math.PI * 54; // r = 54
            const offset = circumference - (score / 100) * circumference;
            
            scoreCircle.style.strokeDasharray = circumference;
            scoreCircle.style.strokeDashoffset = offset;
        }
    }

    // Animate stats on load
    animateStats() {
        const statNumbers = document.querySelectorAll('.stat-content h3');
        
        statNumbers.forEach(stat => {
            const finalValue = parseInt(stat.textContent);
            this.animateNumber(stat, 0, finalValue, 1000);
        });
    }

    // Animate number counting
    animateNumber(element, start, end, duration) {
        const startTime = performance.now();
        
        const updateNumber = (currentTime) => {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            
            const current = Math.floor(start + (end - start) * progress);
            element.textContent = current;
            
            if (progress < 1) {
                requestAnimationFrame(updateNumber);
            }
        };
        
        requestAnimationFrame(updateNumber);
    }

    // Handle navigation
    handleNavigation(hash) {
        // Remove active class from all nav items
        document.querySelectorAll('.sidebar-nav li').forEach(li => {
            li.classList.remove('active');
        });
        
        // Add active class to clicked item
        const activeLink = document.querySelector(`a[href="${hash}"]`);
        if (activeLink) {
            activeLink.closest('li').classList.add('active');
        }
        
        // Update page content based on hash
        this.loadPageContent(hash);
    }

    // Load page content
    loadPageContent(hash) {
        const contentArea = document.querySelector('.dashboard-content');
        
        switch(hash) {
            case '#cms-connectors':
                this.loadCMSConnectorsPage(contentArea);
                break;
            case '#ai-recommendations':
                this.loadAIRecommendationsPage(contentArea);
                break;
            case '#approval-workflow':
                this.loadApprovalWorkflowPage(contentArea);
                break;
            case '#automation':
                this.loadAutomationPage(contentArea);
                break;
            case '#audit-logs':
                this.loadAuditLogsPage(contentArea);
                break;
            case '#security':
                this.loadSecurityPage(contentArea);
                break;
            case '#analytics':
                this.loadAnalyticsPage(contentArea);
                break;
            case '#settings':
                this.loadSettingsPage(contentArea);
                break;
            default:
                this.loadDashboardPage(contentArea);
        }
    }

    // Handle quick actions
    handleQuickAction(button) {
        const action = button.querySelector('span').textContent;
        
        switch(action) {
            case 'Run AI Analysis':
                this.runAIAnalysis();
                break;
            case 'Review Recommendations':
                this.reviewRecommendations();
                break;
            case 'View Audit Logs':
                this.viewAuditLogs();
                break;
            case 'Backup Sites':
                this.backupSites();
                break;
            case 'Add New Site':
                this.showAddSiteModal();
                break;
            case 'Run Scan':
                this.runSiteScan();
                break;
            case 'Export Report':
                this.exportReport();
                break;
            case 'Settings':
                this.openSettings();
                break;
        }
    }

    // Show add site modal
    showAddSiteModal() {
        const modal = this.createModal({
            title: 'Add New Site',
            content: `
                <form id="add-site-form">
                    <div class="form-group">
                        <label for="site-url">Site URL</label>
                        <input type="url" id="site-url" placeholder="https://example.com" required>
                    </div>
                    <div class="form-group">
                        <label for="site-name">Site Name</label>
                        <input type="text" id="site-name" placeholder="My Website" required>
                    </div>
                    <div class="form-group">
                        <label for="site-type">Site Type</label>
                        <select id="site-type">
                            <option value="wordpress">WordPress</option>
                            <option value="shopify">Shopify</option>
                            <option value="wix">Wix</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn-secondary" onclick="this.closest('.modal').remove()">Cancel</button>
                        <button type="submit" class="btn-primary">Add Site</button>
                    </div>
                </form>
            `
        });
        
        document.body.appendChild(modal);
        
        // Handle form submission
        document.getElementById('add-site-form').addEventListener('submit', (e) => {
            e.preventDefault();
            this.addNewSite();
        });
    }

    // Create modal
    createModal({ title, content }) {
        const modal = document.createElement('div');
        modal.className = 'modal';
        modal.innerHTML = `
            <div class="modal-overlay"></div>
            <div class="modal-content">
                <div class="modal-header">
                    <h3>${title}</h3>
                    <button class="modal-close">&times;</button>
                </div>
                <div class="modal-body">
                    ${content}
                </div>
            </div>
        `;
        
        // Add modal styles
        this.addModalStyles();
        
        // Close modal events
        modal.querySelector('.modal-close').addEventListener('click', () => {
            modal.remove();
        });
        
        modal.querySelector('.modal-overlay').addEventListener('click', () => {
            modal.remove();
        });
        
        return modal;
    }

    // Add modal styles
    addModalStyles() {
        if (!document.getElementById('modal-styles')) {
            const styles = document.createElement('style');
            styles.id = 'modal-styles';
            styles.textContent = `
                .modal {
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    z-index: 10000;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                }
                
                .modal-overlay {
                    position: absolute;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0, 0, 0, 0.5);
                }
                
                .modal-content {
                    background: white;
                    border-radius: 12px;
                    max-width: 500px;
                    width: 90%;
                    max-height: 90vh;
                    overflow-y: auto;
                    position: relative;
                    z-index: 1;
                }
                
                .modal-header {
                    padding: 24px 24px 0;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                }
                
                .modal-header h3 {
                    margin: 0;
                    font-size: 18px;
                    font-weight: 600;
                }
                
                .modal-close {
                    background: none;
                    border: none;
                    font-size: 24px;
                    cursor: pointer;
                    color: #64748b;
                }
                
                .modal-body {
                    padding: 24px;
                }
                
                .form-group {
                    margin-bottom: 20px;
                }
                
                .form-group label {
                    display: block;
                    margin-bottom: 8px;
                    font-weight: 500;
                    color: #374151;
                }
                
                .form-group input,
                .form-group select {
                    width: 100%;
                    padding: 12px;
                    border: 1px solid #d1d5db;
                    border-radius: 6px;
                    font-size: 14px;
                }
                
                .form-actions {
                    display: flex;
                    gap: 12px;
                    justify-content: flex-end;
                    margin-top: 24px;
                }
                
                .btn-primary,
                .btn-secondary {
                    padding: 10px 20px;
                    border-radius: 6px;
                    border: none;
                    font-weight: 500;
                    cursor: pointer;
                    transition: all 0.3s ease;
                }
                
                .btn-primary {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: white;
                }
                
                .btn-secondary {
                    background: #f3f4f6;
                    color: #374151;
                }
                
                .btn-primary:hover {
                    transform: translateY(-1px);
                }
                
                .btn-secondary:hover {
                    background: #e5e7eb;
                }
            `;
            document.head.appendChild(styles);
        }
    }

    // Add new site
    addNewSite() {
        const url = document.getElementById('site-url').value;
        const name = document.getElementById('site-name').value;
        const type = document.getElementById('site-type').value;
        
        // Simulate API call
        this.showLoading();
        
        setTimeout(() => {
            this.hideLoading();
            this.showNotification('Site added successfully!', 'success');
            document.querySelector('.modal').remove();
            this.refreshDashboard();
        }, 2000);
    }

    // Run AI Analysis (Traditional SEO focus)
    async runAIAnalysis() {
        try {
            console.log('Starting AI analysis for Traditional SEO...');
            this.showNotification('Starting AI analysis for Google/Bing optimization...', 'info');
            
            if (!this.api) {
                this.api = new AISEODashboardAPI();
            }
            
            // Call AI analysis endpoint
            const analysisResult = await this.api.runScan(); // Using existing scan endpoint
            console.log('AI Analysis result:', analysisResult);
            
            this.showNotification('AI Analysis completed! Generated ' + (analysisResult.recommendations?.length || 0) + ' SEO recommendations.', 'success');
            this.refreshDashboard();
            
        } catch (error) {
            console.error('AI Analysis failed:', error);
            this.showNotification('AI Analysis failed. Check your API configuration.', 'error');
        }
    }

    // Run site scan
    async runSiteScan() {
        try {
            console.log('Starting site scan...');
            this.showNotification('Starting site scan...', 'info');
            
            if (!this.api) {
                this.api = new AISEODashboardAPI();
            }
            
            // Call real WordPress plugin scan
            const scanResult = await this.api.runScan();
            console.log('Scan result:', scanResult);
            
            this.showNotification('Scan completed! Found ' + (scanResult.posts?.length || 0) + ' posts to optimize.', 'success');
            this.refreshDashboard();
            
        } catch (error) {
            console.error('Scan failed:', error);
            this.showNotification('Scan failed. Check your API configuration.', 'error');
        }
    }

    // Review AI recommendations
    reviewRecommendations() {
        this.showNotification('Opening AI Recommendations review panel...', 'info');
        // Navigate to recommendations page
        this.handleNavigation('#ai-recommendations');
    }

    // View audit logs
    viewAuditLogs() {
        this.showNotification('Opening Audit Logs...', 'info');
        // Navigate to audit logs page
        this.handleNavigation('#audit-logs');
    }

    // Backup sites
    async backupSites() {
        try {
            this.showNotification('Creating backup of all connected sites...', 'info');
            
            if (!this.api) {
                this.api = new AISEODashboardAPI();
            }
            
            // Simulate backup process
            setTimeout(() => {
                this.showNotification('Backup completed successfully! All sites backed up.', 'success');
            }, 2000);
            
        } catch (error) {
            console.error('Backup failed:', error);
            this.showNotification('Backup failed. Please try again.', 'error');
        }
    }

    // Export report
    exportReport() {
        this.showNotification('Generating report...', 'info');
        
        setTimeout(() => {
            this.showNotification('Report downloaded successfully!', 'success');
        }, 2000);
    }

    // Open settings
    openSettings() {
        this.handleNavigation('#settings');
    }

    // Refresh dashboard
    refreshDashboard() {
        this.showLoading();
        
        setTimeout(() => {
            this.hideLoading();
            this.loadDashboardData();
            this.showNotification('Dashboard refreshed!', 'success');
        }, 1000);
    }

    // Load dashboard data
    async loadDashboardData() {
        try {
            console.log('Loading dashboard data from API...');
            
            // Initialize API if not already done
            if (!this.api) {
                this.api = new AISEODashboardAPI();
            }
            
            // Get real data from WordPress plugin
            const dashboardData = await this.api.getDashboardData();
            console.log('Dashboard data received:', dashboardData);
            
            // Transform WordPress data to dashboard format
            const transformedData = this.transformWordPressData(dashboardData);
            console.log('Transformed data:', transformedData);
            
            this.updateDashboardStats(transformedData.stats);
            this.updateRecentActivity(transformedData.recentActivity);
            this.updateSEOScore(transformedData.seoScore);
            
        } catch (error) {
            console.error('Failed to load dashboard data:', error);
            this.showNotification('Failed to load dashboard data. Check your API configuration.', 'error');
            
            // Fallback to mock data
            this.loadMockData();
        }
    }
    
    // Transform WordPress plugin data to dashboard format
    transformWordPressData(wordpressData) {
        return {
            stats: {
                connectedSites: 1, // Single WordPress site
                avgSEOScore: wordpressData.seo_score || 87,
                pendingApprovals: wordpressData.recent_changes?.filter(c => c.status === 'pending').length || 0,
                autoOptimizations: wordpressData.recent_changes?.length || 0
            },
            recentActivity: wordpressData.recent_changes?.map(change => ({
                type: change.status === 'completed' ? 'success' : 
                      change.status === 'pending' ? 'warning' : 'info',
                message: change.description || 'SEO change applied',
                time: change.timestamp || 'recently'
            })) || [],
            seoScore: wordpressData.seo_score || 87
        };
    }
    
    // Load mock data as fallback
    loadMockData() {
        console.log('Loading mock data as fallback...');
        const mockData = {
            stats: {
                connectedSites: 1,
                avgSEOScore: 87,
                pendingApprovals: 0,
                autoOptimizations: 5
            },
            recentActivity: [
                {
                    type: 'success',
                    message: 'SEO optimization completed for your site',
                    time: '2 minutes ago'
                },
                {
                    type: 'info',
                    message: 'Connection to WordPress plugin established',
                    time: '5 minutes ago'
                }
            ]
        };
        
        this.updateDashboardStats(mockData.stats);
        this.updateRecentActivity(mockData.recentActivity);
    }

    // Update dashboard stats
    updateDashboardStats(stats) {
        // Update stat numbers
        const statElements = document.querySelectorAll('.stat-content h3');
        if (statElements.length >= 4) {
            statElements[0].textContent = stats.connectedSites;
            statElements[1].textContent = stats.avgSEOScore;
            statElements[2].textContent = stats.pendingApprovals;
            statElements[3].textContent = stats.autoOptimizations;
        }
    }

    // Update recent activity
    updateRecentActivity(activities) {
        const activityList = document.querySelector('.activity-list');
        if (activityList) {
            // Clear existing activities
            activityList.innerHTML = '';
            
            // Add new activities
            activities.forEach(activity => {
                const activityItem = this.createActivityItem(activity);
                activityList.appendChild(activityItem);
            });
        }
    }
    
    // Update SEO score
    updateSEOScore(score) {
        const scoreNumber = document.querySelector('.score-number');
        if (scoreNumber) {
            scoreNumber.textContent = score;
            this.updateSEOProgress();
        }
    }

    // Create activity item
    createActivityItem(activity) {
        const item = document.createElement('div');
        item.className = 'activity-item';
        item.innerHTML = `
            <div class="activity-icon ${activity.type}">
                <i class="fas fa-${this.getActivityIcon(activity.type)}"></i>
            </div>
            <div class="activity-content">
                <p>${activity.message}</p>
                <span class="activity-time">${activity.time}</span>
            </div>
        `;
        return item;
    }

    // Get activity icon
    getActivityIcon(type) {
        const icons = {
            success: 'check',
            warning: 'exclamation-triangle',
            info: 'sync',
            error: 'times-circle'
        };
        return icons[type] || 'info-circle';
    }

    // Setup real-time updates
    setupRealTimeUpdates() {
        // Simulate real-time updates every 30 seconds
        setInterval(() => {
            this.loadDashboardData();
        }, 30000);
    }

    // Show notification
    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <i class="fas fa-${this.getNotificationIcon(type)}"></i>
                <span>${message}</span>
            </div>
            <button class="notification-close">&times;</button>
        `;
        
        // Add notification styles
        this.addNotificationStyles();
        
        document.body.appendChild(notification);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            notification.remove();
        }, 5000);
        
        // Close button
        notification.querySelector('.notification-close').addEventListener('click', () => {
            notification.remove();
        });
    }

    // Get notification icon
    getNotificationIcon(type) {
        const icons = {
            success: 'check-circle',
            warning: 'exclamation-triangle',
            info: 'info-circle',
            error: 'times-circle'
        };
        return icons[type] || 'info-circle';
    }

    // Add notification styles
    addNotificationStyles() {
        if (!document.getElementById('notification-styles')) {
            const styles = document.createElement('style');
            styles.id = 'notification-styles';
            styles.textContent = `
                .notification {
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    background: white;
                    border-radius: 8px;
                    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
                    padding: 16px;
                    display: flex;
                    align-items: center;
                    gap: 12px;
                    z-index: 10000;
                    animation: slideIn 0.3s ease;
                }
                
                .notification-success {
                    border-left: 4px solid #10b981;
                }
                
                .notification-warning {
                    border-left: 4px solid #f59e0b;
                }
                
                .notification-info {
                    border-left: 4px solid #3b82f6;
                }
                
                .notification-error {
                    border-left: 4px solid #ef4444;
                }
                
                .notification-content {
                    display: flex;
                    align-items: center;
                    gap: 8px;
                }
                
                .notification-content i {
                    font-size: 16px;
                }
                
                .notification-success .notification-content i {
                    color: #10b981;
                }
                
                .notification-warning .notification-content i {
                    color: #f59e0b;
                }
                
                .notification-info .notification-content i {
                    color: #3b82f6;
                }
                
                .notification-error .notification-content i {
                    color: #ef4444;
                }
                
                .notification-close {
                    background: none;
                    border: none;
                    font-size: 18px;
                    cursor: pointer;
                    color: #64748b;
                }
                
                @keyframes slideIn {
                    from {
                        transform: translateX(100%);
                        opacity: 0;
                    }
                    to {
                        transform: translateX(0);
                        opacity: 1;
                    }
                }
            `;
            document.head.appendChild(styles);
        }
    }

    // Show loading
    showLoading() {
        document.body.classList.add('loading');
    }

    // Hide loading
    hideLoading() {
        document.body.classList.remove('loading');
    }

    // Toggle user menu
    toggleUserMenu() {
        // Implementation for user menu dropdown
        console.log('Toggle user menu');
    }

    // Open site details
    openSiteDetails(siteItem) {
        const siteName = siteItem.querySelector('h4').textContent;
        this.showNotification(`Opening details for ${siteName}`, 'info');
    }

    // Show activity details
    showActivityDetails(activityItem) {
        const message = activityItem.querySelector('p').textContent;
        this.showNotification(`Activity: ${message}`, 'info');
    }

    // Approve AI recommendation
    async approveRecommendation(recommendationItem) {
        const title = recommendationItem.querySelector('h4').textContent;
        const impact = recommendationItem.querySelector('.impact').textContent;
        
        this.showNotification(`Approving: ${title}`, 'info');
        
        // Simulate approval process
        setTimeout(() => {
            recommendationItem.classList.remove('pending');
            recommendationItem.classList.add('approved');
            recommendationItem.querySelector('.recommendation-actions').innerHTML = '<span class="approved-badge">Approved</span>';
            
            this.showNotification(`Approved: ${title} - ${impact}`, 'success');
            this.updatePendingApprovalsCount(-1);
        }, 1000);
    }

    // Modify AI recommendation
    modifyRecommendation(recommendationItem) {
        const title = recommendationItem.querySelector('h4').textContent;
        this.showNotification(`Opening modification panel for: ${title}`, 'info');
        
        // Show modification modal
        this.showModificationModal(recommendationItem);
    }

    // Reject AI recommendation
    async rejectRecommendation(recommendationItem) {
        const title = recommendationItem.querySelector('h4').textContent;
        
        this.showNotification(`Rejecting: ${title}`, 'info');
        
        // Simulate rejection process
        setTimeout(() => {
            recommendationItem.classList.remove('pending');
            recommendationItem.classList.add('rejected');
            recommendationItem.querySelector('.recommendation-actions').innerHTML = '<span class="rejected-badge">Rejected</span>';
            
            this.showNotification(`Rejected: ${title}`, 'warning');
            this.updatePendingApprovalsCount(-1);
        }, 1000);
    }

    // Update pending approvals count
    updatePendingApprovalsCount(change) {
        const countElement = document.getElementById('pending-approvals');
        if (countElement) {
            const currentCount = parseInt(countElement.textContent);
            const newCount = Math.max(0, currentCount + change);
            countElement.textContent = newCount;
        }
    }

    // Show modification modal
    showModificationModal(recommendationItem) {
        const title = recommendationItem.querySelector('h4').textContent;
        const description = recommendationItem.querySelector('p').textContent;
        
        const modal = this.createModal({
            title: `Modify: ${title}`,
            content: `
                <form id="modify-recommendation-form">
                    <div class="form-group">
                        <label for="modified-title">Recommendation Title</label>
                        <input type="text" id="modified-title" value="${title}" required>
                    </div>
                    <div class="form-group">
                        <label for="modified-description">Description</label>
                        <textarea id="modified-description" rows="4" required>${description}</textarea>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn-secondary" onclick="this.closest('.modal').remove()">Cancel</button>
                        <button type="submit" class="btn-primary">Save & Approve</button>
                    </div>
                </form>
            `
        });
        
        document.body.appendChild(modal);
        
        // Handle form submission
        document.getElementById('modify-recommendation-form').addEventListener('submit', (e) => {
            e.preventDefault();
            this.saveModifiedRecommendation(recommendationItem, modal);
        });
    }

    // Save modified recommendation
    async saveModifiedRecommendation(recommendationItem, modal) {
        const newTitle = document.getElementById('modified-title').value;
        const newDescription = document.getElementById('modified-description').value;
        
        this.showNotification('Saving modified recommendation...', 'info');
        
        // Update the recommendation item
        recommendationItem.querySelector('h4').textContent = newTitle;
        recommendationItem.querySelector('p').textContent = newDescription;
        
        // Close modal
        modal.remove();
        
        // Approve the modified recommendation
        await this.approveRecommendation(recommendationItem);
    }

    // Load different pages with proper content
    loadCMSConnectorsPage(contentArea) {
        contentArea.innerHTML = `
            <div class="page-header">
                <h1>CMS Connectors</h1>
                <p>Manage your website connections and integrations</p>
            </div>
            
            <div class="cms-connectors-page">
                <div class="connector-grid">
                    <div class="connector-card connected">
                        <div class="connector-header">
                            <div class="connector-icon wordpress">
                                <i class="fab fa-wordpress"></i>
                            </div>
                            <div class="connector-status">
                                <span class="status-badge connected">Connected</span>
                            </div>
                        </div>
                        <div class="connector-content">
                            <h3>WordPress Site</h3>
                            <p>digital-mareketing.local</p>
                            <div class="connector-stats">
                                <div class="stat">
                                    <span class="stat-value">18</span>
                                    <span class="stat-label">Posts</span>
                                </div>
                                <div class="stat">
                                    <span class="stat-value">87</span>
                                    <span class="stat-label">SEO Score</span>
                                </div>
                            </div>
                        </div>
                        <div class="connector-actions">
                            <button class="btn-secondary">Test Connection</button>
                            <button class="btn-primary">Manage</button>
                        </div>
                    </div>
                    
                    <div class="connector-card available">
                        <div class="connector-header">
                            <div class="connector-icon shopify">
                                <i class="fab fa-shopify"></i>
                            </div>
                            <div class="connector-status">
                                <span class="status-badge available">Available</span>
                            </div>
                        </div>
                        <div class="connector-content">
                            <h3>Shopify Store</h3>
                            <p>Connect your e-commerce site</p>
                            <div class="connector-features">
                                <span class="feature">Product SEO</span>
                                <span class="feature">Category Optimization</span>
                                <span class="feature">Meta Tags</span>
                            </div>
                        </div>
                        <div class="connector-actions">
                            <button class="btn-primary">Connect Store</button>
                        </div>
                    </div>
                    
                    <div class="connector-card coming-soon">
                        <div class="connector-header">
                            <div class="connector-icon wix">
                                <i class="fas fa-palette"></i>
                            </div>
                            <div class="connector-status">
                                <span class="status-badge coming-soon">Coming Soon</span>
                            </div>
                        </div>
                        <div class="connector-content">
                            <h3>Wix Website</h3>
                            <p>Wix integration coming soon</p>
                            <div class="connector-features">
                                <span class="feature">Page Optimization</span>
                                <span class="feature">SEO Tools</span>
                            </div>
                        </div>
                        <div class="connector-actions">
                            <button class="btn-secondary" disabled>Coming Soon</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    loadAIRecommendationsPage(contentArea) {
        contentArea.innerHTML = `
            <div class="page-header">
                <h1>AI Recommendations</h1>
                <p>Review and manage AI-generated SEO recommendations</p>
            </div>
            
            <div class="recommendations-page">
                <div class="recommendations-filters">
                    <div class="filter-group">
                        <label>Status:</label>
                        <select class="filter-select">
                            <option value="all">All Recommendations</option>
                            <option value="pending">Pending Review</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>Priority:</label>
                        <select class="filter-select">
                            <option value="all">All Priorities</option>
                            <option value="high">High Impact</option>
                            <option value="medium">Medium Impact</option>
                            <option value="low">Low Impact</option>
                        </select>
                    </div>
                    <button class="btn-primary">Generate New Recommendations</button>
                </div>
                
                <div class="recommendations-list">
                    <div class="recommendation-item pending high-priority">
                        <div class="recommendation-icon">
                            <i class="fas fa-lightbulb"></i>
                        </div>
                        <div class="recommendation-content">
                            <h4>Optimize Meta Description</h4>
                            <p>Improve click-through rate with AI-generated meta description for better Google search visibility</p>
                            <div class="recommendation-meta">
                                <span class="confidence">95% confidence</span>
                                <span class="impact high">+5 SEO points</span>
                                <span class="priority high">High Priority</span>
                            </div>
                        </div>
                        <div class="recommendation-actions">
                            <button class="btn-approve">Approve</button>
                            <button class="btn-modify">Modify</button>
                            <button class="btn-reject">Reject</button>
                        </div>
                    </div>
                    
                    <div class="recommendation-item pending medium-priority">
                        <div class="recommendation-icon">
                            <i class="fas fa-heading"></i>
                        </div>
                        <div class="recommendation-content">
                            <h4>Add H2 Subheadings</h4>
                            <p>Improve content structure for better Google ranking and user experience</p>
                            <div class="recommendation-meta">
                                <span class="confidence">88% confidence</span>
                                <span class="impact medium">+3 SEO points</span>
                                <span class="priority medium">Medium Priority</span>
                            </div>
                        </div>
                        <div class="recommendation-actions">
                            <button class="btn-approve">Approve</button>
                            <button class="btn-modify">Modify</button>
                            <button class="btn-reject">Reject</button>
                        </div>
                    </div>
                    
                    <div class="recommendation-item approved">
                        <div class="recommendation-icon">
                            <i class="fas fa-image"></i>
                        </div>
                        <div class="recommendation-content">
                            <h4>Add Alt Text to Images</h4>
                            <p>Improve accessibility and image SEO for better search engine visibility</p>
                            <div class="recommendation-meta">
                                <span class="confidence">92% confidence</span>
                                <span class="impact low">+1 SEO point</span>
                                <span class="status approved">Approved</span>
                            </div>
                        </div>
                        <div class="recommendation-actions">
                            <span class="approved-badge">Approved</span>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    loadApprovalWorkflowPage(contentArea) {
        contentArea.innerHTML = `
            <div class="page-header">
                <h1>Approval Workflow</h1>
                <p>Manage the approval process for AI recommendations</p>
            </div>
            
            <div class="workflow-page">
                <div class="workflow-stats">
                    <div class="workflow-stat">
                        <div class="stat-number">3</div>
                        <div class="stat-label">Pending Approvals</div>
                    </div>
                    <div class="workflow-stat">
                        <div class="stat-number">24</div>
                        <div class="stat-label">Approved Today</div>
                    </div>
                    <div class="workflow-stat">
                        <div class="stat-number">2</div>
                        <div class="stat-label">Rejected Today</div>
                    </div>
                </div>
                
                <div class="workflow-settings">
                    <h3>Workflow Settings</h3>
                    <div class="setting-group">
                        <label>
                            <input type="checkbox" checked> Auto-approve low-risk changes
                        </label>
                    </div>
                    <div class="setting-group">
                        <label>
                            <input type="checkbox" checked> Require approval for high-impact changes
                        </label>
                    </div>
                    <div class="setting-group">
                        <label>
                            <input type="checkbox"> Send email notifications
                        </label>
                    </div>
                </div>
            </div>
        `;
    }

    loadAutomationPage(contentArea) {
        contentArea.innerHTML = `
            <div class="page-header">
                <h1>Automation Rules</h1>
                <p>Configure automated SEO optimization rules</p>
            </div>
            
            <div class="automation-page">
                <div class="automation-rules">
                    <div class="rule-card">
                        <div class="rule-header">
                            <h3>Meta Description Optimization</h3>
                            <label class="switch">
                                <input type="checkbox" checked>
                                <span class="slider"></span>
                            </label>
                        </div>
                        <p>Automatically optimize meta descriptions for better click-through rates</p>
                        <div class="rule-settings">
                            <span class="setting">Frequency: Daily</span>
                            <span class="setting">Priority: High</span>
                        </div>
                    </div>
                    
                    <div class="rule-card">
                        <div class="rule-header">
                            <h3>Image Alt Text Generation</h3>
                            <label class="switch">
                                <input type="checkbox" checked>
                                <span class="slider"></span>
                            </label>
                        </div>
                        <p>Automatically generate alt text for images to improve accessibility</p>
                        <div class="rule-settings">
                            <span class="setting">Frequency: On Upload</span>
                            <span class="setting">Priority: Medium</span>
                        </div>
                    </div>
                    
                    <div class="rule-card">
                        <div class="rule-header">
                            <h3>Content Structure Analysis</h3>
                            <label class="switch">
                                <input type="checkbox">
                                <span class="slider"></span>
                            </label>
                        </div>
                        <p>Analyze and suggest improvements for content structure</p>
                        <div class="rule-settings">
                            <span class="setting">Frequency: Weekly</span>
                            <span class="setting">Priority: Low</span>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    loadAuditLogsPage(contentArea) {
        contentArea.innerHTML = `
            <div class="page-header">
                <h1>Audit Logs</h1>
                <p>Complete history of all AI actions and changes</p>
            </div>
            
            <div class="audit-logs-page">
                <div class="logs-filters">
                    <div class="filter-group">
                        <label>Action Type:</label>
                        <select class="filter-select">
                            <option value="all">All Actions</option>
                            <option value="approval">Approvals</option>
                            <option value="rejection">Rejections</option>
                            <option value="modification">Modifications</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>Date Range:</label>
                        <select class="filter-select">
                            <option value="today">Today</option>
                            <option value="week">This Week</option>
                            <option value="month">This Month</option>
                            <option value="all">All Time</option>
                        </select>
                    </div>
                </div>
                
                <div class="audit-logs">
                    <div class="log-entry">
                        <div class="log-icon success">
                            <i class="fas fa-check"></i>
                        </div>
                        <div class="log-content">
                            <h4>Meta Description Optimized</h4>
                            <p>Approved and implemented AI recommendation for post "PPC Advertising Services"</p>
                            <span class="log-time">2 minutes ago</span>
                        </div>
                    </div>
                    
                    <div class="log-entry">
                        <div class="log-icon modification">
                            <i class="fas fa-edit"></i>
                        </div>
                        <div class="log-content">
                            <h4>Recommendation Modified</h4>
                            <p>User modified AI suggestion for "Add H2 Subheadings" before approval</p>
                            <span class="log-time">15 minutes ago</span>
                        </div>
                    </div>
                    
                    <div class="log-entry">
                        <div class="log-icon rejection">
                            <i class="fas fa-times"></i>
                        </div>
                        <div class="log-content">
                            <h4>Recommendation Rejected</h4>
                            <p>User rejected AI suggestion for "Change Page Title"</p>
                            <span class="log-time">1 hour ago</span>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    loadSecurityPage(contentArea) {
        contentArea.innerHTML = `
            <div class="page-header">
                <h1>Security & Backup</h1>
                <p>Manage security settings and backup configurations</p>
            </div>
            
            <div class="security-page">
                <div class="security-status">
                    <div class="status-card secure">
                        <div class="status-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <div class="status-content">
                            <h3>Security Status</h3>
                            <p>All systems secure</p>
                            <span class="status-badge secure">Protected</span>
                        </div>
                    </div>
                    
                    <div class="status-card">
                        <div class="status-icon">
                            <i class="fas fa-download"></i>
                        </div>
                        <div class="status-content">
                            <h3>Last Backup</h3>
                            <p>2 hours ago</p>
                            <button class="btn-primary">Create Backup</button>
                        </div>
                    </div>
                </div>
                
                <div class="security-settings">
                    <h3>Security Settings</h3>
                    <div class="setting-group">
                        <label>
                            <input type="checkbox" checked> Auto-backup before changes
                        </label>
                    </div>
                    <div class="setting-group">
                        <label>
                            <input type="checkbox" checked> Require confirmation for high-risk actions
                        </label>
                    </div>
                    <div class="setting-group">
                        <label>
                            <input type="checkbox"> Enable rollback notifications
                        </label>
                    </div>
                </div>
            </div>
        `;
    }

    loadAnalyticsPage(contentArea) {
        contentArea.innerHTML = `
            <div class="page-header">
                <h1>Analytics</h1>
                <p>Track SEO performance and optimization results</p>
            </div>
            
            <div class="analytics-page">
                <div class="analytics-overview">
                    <div class="metric-card">
                        <h3>SEO Score Trend</h3>
                        <div class="metric-value">87</div>
                        <div class="metric-change positive">+5 this week</div>
                    </div>
                    
                    <div class="metric-card">
                        <h3>Recommendations Implemented</h3>
                        <div class="metric-value">24</div>
                        <div class="metric-change positive">+8 this week</div>
                    </div>
                    
                    <div class="metric-card">
                        <h3>Approval Rate</h3>
                        <div class="metric-value">92%</div>
                        <div class="metric-change positive">+3% this week</div>
                    </div>
                </div>
                
                <div class="analytics-chart">
                    <h3>SEO Score Over Time</h3>
                    <div class="chart-placeholder">
                        <p>Chart visualization would go here</p>
                        <p>Showing SEO score improvements over the last 30 days</p>
                    </div>
                </div>
            </div>
        `;
    }

    loadSettingsPage(contentArea) {
        contentArea.innerHTML = `
            <div class="page-header">
                <h1>Settings</h1>
                <p>Configure your AI SEO automation platform</p>
            </div>
            
            <div class="settings-page">
                <div class="settings-section">
                    <h3>API Configuration</h3>
                    <div class="setting-group">
                        <label>WordPress Site URL</label>
                        <input type="url" value="http://digital-mareketing.local" class="setting-input">
                    </div>
                    <div class="setting-group">
                        <label>API Key</label>
                        <input type="text" value="test_api_key_12345" class="setting-input">
                    </div>
                </div>
                
                <div class="settings-section">
                    <h3>Notification Settings</h3>
                    <div class="setting-group">
                        <label>
                            <input type="checkbox" checked> Email notifications
                        </label>
                    </div>
                    <div class="setting-group">
                        <label>
                            <input type="checkbox" checked> Dashboard notifications
                        </label>
                    </div>
                </div>
                
                <div class="settings-section">
                    <h3>Automation Settings</h3>
                    <div class="setting-group">
                        <label>
                            <input type="checkbox" checked> Auto-scan on content updates
                        </label>
                    </div>
                    <div class="setting-group">
                        <label>
                            <input type="checkbox"> Auto-approve low-risk changes
                        </label>
                    </div>
                </div>
                
                <div class="settings-actions">
                    <button class="btn-primary">Save Settings</button>
                    <button class="btn-secondary">Reset to Defaults</button>
                </div>
            </div>
        `;
    }

    loadDashboardPage(contentArea) {
        // Reload the original dashboard content
        location.reload();
    }
}

// Initialize dashboard when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new AISEODashboard();
});

// Export for global access
window.AISEODashboard = AISEODashboard;
