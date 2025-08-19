/**
 * AI SEO Optimizer Admin JavaScript
 * 
 * Handles admin dashboard functionality and AJAX operations
 * 
 * @package AI_SEO_Optimizer
 * @since 1.0.0
 */

(function($) {
    'use strict';

    // Main AI SEO Admin object
    var AISEOAdmin = {
        
        // Initialize the admin interface
        init: function() {
            this.bindEvents();
            this.initDashboard();
            this.initSettings();
            this.initLogs();
        },

        // Bind event handlers
        bindEvents: function() {
            // Scan button
            $(document).on('click', '.ai-seo-scan-btn', this.handleScan);
            
            // Apply changes button
            $(document).on('click', '.ai-seo-apply-changes-btn', this.handleApplyChanges);
            
            // Apply single recommendation
            $(document).on('click', '.apply-single-recommendation', this.handleApplySingleRecommendation);
            
            // Apply all recommendations
            $(document).on('click', '.ai-seo-apply-all-btn', this.handleApplyAllRecommendations);
            
            // Settings form submission
            $(document).on('submit', '#ai-seo-settings-form', this.handleSettingsSave);
            
            // Log filters
            $(document).on('change', '.ai-seo-log-filter', this.handleLogFilter);
            
            // Pagination
            $(document).on('click', '.ai-seo-pagination a', this.handlePagination);
            
            // Export logs
            $(document).on('click', '.ai-seo-export-logs', this.handleExportLogs);
            
            // Refresh dashboard data
            $(document).on('click', '.ai-seo-refresh-dashboard', this.refreshDashboard);
            
            // Auto-refresh every 30 seconds
            setInterval(this.refreshDashboard.bind(this), 30000);
        },

        // Initialize dashboard
        initDashboard: function() {
            if ($('#ai-seo-dashboard').length) {
                this.loadDashboardData();
                this.initCharts();
            }
        },

        // Initialize settings page
        initSettings: function() {
            if ($('#ai-seo-settings').length) {
                this.testConnection();
            }
        },

        // Initialize logs page
        initLogs: function() {
            if ($('#ai-seo-logs').length) {
                this.loadLogs();
            }
        },

        // Handle scan button click
        handleScan: function(e) {
            e.preventDefault();
            
            var $btn = $(this);
            var originalText = $btn.text();
            
            // Show loading state
            $btn.prop('disabled', true)
                .text(aiSeoAjax.strings.scanning)
                .addClass('updating-message');
            
            // Show loading recommendations
            $('.ai-seo-loading-recommendations').show();
            $('.ai-seo-no-recommendations').hide();
            $('.ai-seo-recommendations-list').hide();
            $('.ai-seo-apply-all').hide();
            
            $.ajax({
                url: aiSeoAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'ai_seo_scan',
                    nonce: aiSeoAjax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        AISEOAdmin.showNotice('success', 'Scan completed successfully!');
                        AISEOAdmin.displayRecommendations(response.data);
                        AISEOAdmin.refreshDashboard();
                    } else {
                        AISEOAdmin.showNotice('error', response.data || 'Scan failed');
                        $('.ai-seo-loading-recommendations').hide();
                        $('.ai-seo-no-recommendations').show();
                    }
                },
                error: function() {
                    AISEOAdmin.showNotice('error', 'Network error occurred');
                    $('.ai-seo-loading-recommendations').hide();
                    $('.ai-seo-no-recommendations').show();
                },
                complete: function() {
                    $btn.prop('disabled', false)
                        .text(originalText)
                        .removeClass('updating-message');
                }
            });
        },

        // Handle apply changes button click
        handleApplyChanges: function(e) {
            e.preventDefault();
            
            var $btn = $(this);
            var changes = $btn.data('changes');
            
            if (!changes || !changes.length) {
                AISEOAdmin.showNotice('error', 'No changes to apply');
                return;
            }
            
            var originalText = $btn.text();
            
            $btn.prop('disabled', true)
                .text(aiSeoAjax.strings.applying)
                .addClass('updating-message');
            
            var changesJson = JSON.stringify(changes);
            
            // Ensure proper JSON formatting
            console.log('DEBUG: Sending changes JSON:', changesJson);
            
            $.ajax({
                url: aiSeoAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'ai_seo_apply_changes',
                    nonce: aiSeoAjax.nonce,
                    changes: changesJson
                },
                success: function(response) {
                    if (response.success) {
                        AISEOAdmin.showNotice('success', 'Changes applied successfully!');
                        AISEOAdmin.refreshDashboard();
                        AISEOAdmin.loadLogs();
                    } else {
                        AISEOAdmin.showNotice('error', response.data || 'Failed to apply changes');
                    }
                },
                error: function(xhr, status, error) {
                    AISEOAdmin.showNotice('error', 'Network error occurred');
                },
                complete: function() {
                    $btn.prop('disabled', false)
                        .text(originalText)
                        .removeClass('updating-message');
                }
            });
        },

        // Handle apply single recommendation
        handleApplySingleRecommendation: function(e) {
            e.preventDefault();
            
            var $btn = $(this);
            var index = $btn.data('index');
            
            console.log('DEBUG: Single recommendation index:', index);
            
            // Get the recommendation from the global variable
            if (!window.aiSeoRecommendations || !window.aiSeoRecommendations[index]) {
                AISEOAdmin.showNotice('error', 'No recommendation data found');
                return;
            }
            
            var changes = [window.aiSeoRecommendations[index]];
            console.log('DEBUG: Original changes:', changes);
            
            var originalText = $btn.text();
            
            $btn.prop('disabled', true)
                .text(aiSeoAjax.strings.applying)
                .addClass('updating-message');
            
            // Clean the data before JSON encoding
            var cleanChanges = changes.map(function(change) {
                return {
                    change_id: String(change.change_id || ''),
                    change_type: String(change.change_type || ''),
                    target_post_id: parseInt(change.target_post_id) || 0,
                    old_value: String(change.old_value || ''),
                    new_value: String(change.new_value || ''),
                    priority: String(change.priority || 'medium'),
                    reason: String(change.reason || '')
                };
            });
            
            console.log('DEBUG: Cleaned changes:', cleanChanges);
            
            var changesJson = JSON.stringify(cleanChanges);
            console.log('DEBUG: JSON string being sent:', changesJson);
            
            // Validate JSON before sending
            try {
                JSON.parse(changesJson);
                console.log('DEBUG: JSON validation passed');
            } catch (e) {
                console.error('DEBUG: JSON validation failed:', e);
                AISEOAdmin.showNotice('error', 'Invalid JSON generated: ' + e.message);
                $btn.prop('disabled', false)
                    .text(originalText)
                    .removeClass('updating-message');
                return;
            }
            
            $.ajax({
                url: aiSeoAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'ai_seo_apply_changes',
                    nonce: aiSeoAjax.nonce,
                    changes: changesJson
                },
                success: function(response) {
                    console.log('DEBUG: AJAX response:', response);
                    if (response.success) {
                        AISEOAdmin.showNotice('success', 'Recommendation applied successfully!');
                        // Remove the applied recommendation from the list
                        $btn.closest('.recommendation-item').fadeOut();
                        AISEOAdmin.refreshDashboard();
                        AISEOAdmin.loadLogs();
                    } else {
                        AISEOAdmin.showNotice('error', response.data || 'Failed to apply recommendation');
                    }
                },
                error: function(xhr, status, error) {
                    console.log('DEBUG: AJAX error:', {xhr: xhr, status: status, error: error});
                    AISEOAdmin.showNotice('error', 'Network error occurred');
                },
                complete: function() {
                    $btn.prop('disabled', false)
                        .text(originalText)
                        .removeClass('updating-message');
                }
            });
        },

        // Handle apply all recommendations
        handleApplyAllRecommendations: function(e) {
            e.preventDefault();
            
            var $btn = $(this);
            var changes = $btn.data('changes');
            
            if (!changes || !changes.length) {
                AISEOAdmin.showNotice('error', 'No recommendations to apply');
                return;
            }
            
            var originalText = $btn.text();
            
            $btn.prop('disabled', true)
                .text(aiSeoAjax.strings.applying)
                .addClass('updating-message');
            
            var changesJson = JSON.stringify(changes);
            
            $.ajax({
                url: aiSeoAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'ai_seo_apply_changes',
                    nonce: aiSeoAjax.nonce,
                    changes: changesJson
                },
                success: function(response) {
                    if (response.success) {
                        AISEOAdmin.showNotice('success', 'All recommendations applied successfully!');
                        // Hide all recommendations
                        $('.recommendation-item').fadeOut();
                        $('.ai-seo-apply-all').hide();
                        $('.ai-seo-no-recommendations').show();
                        AISEOAdmin.refreshDashboard();
                        AISEOAdmin.loadLogs();
                    } else {
                        AISEOAdmin.showNotice('error', response.data || 'Failed to apply recommendations');
                    }
                },
                error: function(xhr, status, error) {
                    AISEOAdmin.showNotice('error', 'Network error occurred');
                },
                complete: function() {
                    $btn.prop('disabled', false)
                        .text(originalText)
                        .removeClass('updating-message');
                }
            });
        },

        // Handle settings form submission
        handleSettingsSave: function(e) {
            e.preventDefault();
            
            var $form = $(this);
            var $submitBtn = $form.find('input[type="submit"]');
            var originalText = $submitBtn.val();
            
            $submitBtn.prop('disabled', true)
                .val('Saving...')
                .addClass('updating-message');
            
            var formData = $form.serialize();
            
            $.ajax({
                url: aiSeoAjax.ajaxurl,
                type: 'POST',
                data: formData + '&action=ai_seo_save_settings&nonce=' + aiSeoAjax.nonce,
                success: function(response) {
                    if (response.success) {
                        AISEOAdmin.showNotice('success', 'Settings saved successfully!');
                        AISEOAdmin.testConnection();
                    } else {
                        AISEOAdmin.showNotice('error', response.data || 'Failed to save settings');
                    }
                },
                error: function() {
                    AISEOAdmin.showNotice('error', 'Network error occurred');
                },
                complete: function() {
                    $submitBtn.prop('disabled', false)
                        .val(originalText)
                        .removeClass('updating-message');
                }
            });
        },

        // Handle log filter changes
        handleLogFilter: function() {
            AISEOAdmin.loadLogs();
        },

        // Handle pagination
        handlePagination: function(e) {
            e.preventDefault();
            
            var page = $(this).data('page');
            AISEOAdmin.loadLogs(page);
        },

        // Handle export logs
        handleExportLogs: function(e) {
            e.preventDefault();
            
            var filters = AISEOAdmin.getLogFilters();
            var url = aiSeoAjax.ajaxurl + '?action=ai_seo_export_logs&nonce=' + aiSeoAjax.nonce;
            
            // Add filters to URL
            if (filters.change_type) {
                url += '&change_type=' + encodeURIComponent(filters.change_type);
            }
            if (filters.status) {
                url += '&status=' + encodeURIComponent(filters.status);
            }
            if (filters.date_from) {
                url += '&date_from=' + encodeURIComponent(filters.date_from);
            }
            if (filters.date_to) {
                url += '&date_to=' + encodeURIComponent(filters.date_to);
            }
            
            window.location.href = url;
        },

        // Load dashboard data
        loadDashboardData: function() {
            $.ajax({
                url: aiSeoAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'ai_seo_get_dashboard_data',
                    nonce: aiSeoAjax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        AISEOAdmin.updateDashboard(response.data);
                    }
                }
            });
        },

        // Update dashboard with new data
        updateDashboard: function(data) {
            // Update SEO score
            if (data.seo_score !== undefined) {
                $('.ai-seo-score').text(data.seo_score + '/100');
                $('.ai-seo-score-circle').attr('data-score', data.seo_score);
            }
            
            // Update connection status
            if (data.connection_status !== undefined) {
                var $status = $('.ai-seo-connection-status');
                if (data.connection_status) {
                    $status.removeClass('disconnected').addClass('connected').text('Connected');
                } else {
                    $status.removeClass('connected').addClass('disconnected').text('Disconnected');
                }
            }
            
            // Update last scan
            if (data.last_scan) {
                $('.ai-seo-last-scan').text(data.last_scan);
            }
            
            // Update recent changes
            if (data.recent_changes) {
                AISEOAdmin.updateRecentChanges(data.recent_changes);
            }
        },

        // Update recent changes list
        updateRecentChanges: function(changes) {
            var $container = $('.ai-seo-recent-changes');
            if (!$container.length) return;
            
            var html = '';
            
            if (changes.length === 0) {
                html = '<p class="no-changes">No recent changes</p>';
            } else {
                changes.forEach(function(change) {
                    html += '<div class="change-item">';
                    html += '<div class="change-header">';
                    html += '<span class="change-type">' + change.change_type + '</span>';
                    html += '<span class="change-status ' + change.status + '">' + change.status + '</span>';
                    html += '</div>';
                    html += '<div class="change-content">';
                    html += '<strong>' + change.post_title + '</strong>';
                    html += '<div class="change-time">' + change.applied_at_formatted + '</div>';
                    html += '</div>';
                    html += '</div>';
                });
            }
            
            $container.html(html);
        },

        // Load logs
        loadLogs: function(page) {
            var filters = this.getLogFilters();
            filters.page = page || 1;
            
            $.ajax({
                url: aiSeoAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'ai_seo_get_logs',
                    nonce: aiSeoAjax.nonce,
                    page: filters.page,
                    per_page: 20,
                    filters: filters
                },
                success: function(response) {
                    if (response.success) {
                        AISEOAdmin.updateLogs(response.data);
                    }
                }
            });
        },

        // Update logs display
        updateLogs: function(data) {
            var $container = $('#ai-seo-logs-table');
            if (!$container.length) return;
            
            var html = '';
            
            if (data.logs.length === 0) {
                html = '<tr><td colspan="6" class="no-logs">No logs found</td></tr>';
            } else {
                data.logs.forEach(function(log) {
                    html += '<tr>';
                    html += '<td>' + log.change_type + '</td>';
                    html += '<td><a href="' + log.edit_url + '">' + log.post_title + '</a></td>';
                    html += '<td><span class="status-' + log.status + '">' + log.status + '</span></td>';
                    html += '<td>' + log.old_value_preview + '</td>';
                    html += '<td>' + log.new_value_preview + '</td>';
                    html += '<td>' + log.applied_at_formatted + '</td>';
                    html += '</tr>';
                });
            }
            
            $container.find('tbody').html(html);
            
            // Update pagination
            AISEOAdmin.updatePagination(data);
        },

        // Update pagination
        updatePagination: function(data) {
            var $pagination = $('.ai-seo-pagination');
            if (!$pagination.length) return;
            
            var html = '';
            
            if (data.total_pages > 1) {
                // Previous page
                if (data.page > 1) {
                    html += '<a href="#" data-page="' + (data.page - 1) + '">&laquo; Previous</a>';
                }
                
                // Page numbers
                for (var i = 1; i <= data.total_pages; i++) {
                    if (i === data.page) {
                        html += '<span class="current">' + i + '</span>';
                    } else {
                        html += '<a href="#" data-page="' + i + '">' + i + '</a>';
                    }
                }
                
                // Next page
                if (data.page < data.total_pages) {
                    html += '<a href="#" data-page="' + (data.page + 1) + '">Next &raquo;</a>';
                }
            }
            
            $pagination.html(html);
        },

        // Get log filters
        getLogFilters: function() {
            return {
                change_type: $('.ai-seo-filter-type').val(),
                status: $('.ai-seo-filter-status').val(),
                date_from: $('.ai-seo-filter-date-from').val(),
                date_to: $('.ai-seo-filter-date-to').val()
            };
        },

        // Test API connection
        testConnection: function() {
            $.ajax({
                url: aiSeoAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'ai_seo_test_connection',
                    nonce: aiSeoAjax.nonce
                },
                success: function(response) {
                    var $status = $('.ai-seo-connection-test');
                    if (response.success) {
                        $status.removeClass('failed').addClass('success').text('Connection successful');
                    } else {
                        $status.removeClass('success').addClass('failed').text('Connection failed');
                    }
                }
            });
        },

        // Refresh dashboard
        refreshDashboard: function() {
            this.loadDashboardData();
        },

        // Initialize charts (if Chart.js is available)
        initCharts: function() {
            if (typeof Chart !== 'undefined') {
                this.initSEOChart();
                this.initChangesChart();
            }
        },

        // Initialize SEO score chart
        initSEOChart: function() {
            var ctx = document.getElementById('ai-seo-score-chart');
            if (!ctx) return;
            
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    datasets: [{
                        data: [75, 25],
                        backgroundColor: ['#46b450', '#f1f1f1'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        },

        // Initialize changes chart
        initChangesChart: function() {
            var ctx = document.getElementById('ai-seo-changes-chart');
            if (!ctx) return;
            
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                    datasets: [{
                        label: 'Changes Applied',
                        data: [12, 19, 3, 5, 2, 3, 7],
                        borderColor: '#0073aa',
                        backgroundColor: 'rgba(0, 115, 170, 0.1)',
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        },

        // Display recommendations
        displayRecommendations: function(data) {
            $('.ai-seo-loading-recommendations').hide();
            
            if (data && data.recommendations && data.recommendations.length > 0) {
                var html = '';
                data.recommendations.forEach(function(rec, index) {
                    html += '<div class="recommendation-item">';
                    html += '<div class="recommendation-header">';
                    html += '<span class="recommendation-type">' + (rec.change_type || 'SEO Update') + '</span>';
                    html += '<span class="recommendation-priority">' + (rec.priority || 'medium') + '</span>';
                    html += '</div>';
                    html += '<div class="recommendation-content">';
                    html += '<strong>' + (rec.reason || 'SEO Optimization') + '</strong>';
                    html += '<p>Improve SEO performance for better search visibility</p>';
                    if (rec.old_value) {
                        html += '<div class="current-value"><strong>Current:</strong> ' + (rec.old_value.length > 100 ? rec.old_value.substring(0, 100) + '...' : rec.old_value) + '</div>';
                    }
                    if (rec.new_value) {
                        html += '<div class="suggested-value"><strong>Suggested:</strong> ' + (rec.new_value.length > 100 ? rec.new_value.substring(0, 100) + '...' : rec.new_value) + '</div>';
                    }
                    
                    html += '<button type="button" class="ai-seo-btn secondary apply-single-recommendation" data-index="' + index + '">Apply This Change</button>';
                    html += '</div>';
                    html += '</div>';
                });
                
                $('.ai-seo-recommendations-list').html(html).show();
                $('.ai-seo-apply-all').show();
                $('.ai-seo-no-recommendations').hide();
                
                // Store recommendations data for apply all functionality
                $('.ai-seo-apply-all-btn').data('changes', data.recommendations);
                
                // Store individual recommendations in a global variable for easy access
                window.aiSeoRecommendations = data.recommendations;
                
                // Also store in localStorage as backup
                try {
                    localStorage.setItem('aiSeoRecommendations', JSON.stringify(data.recommendations));
                } catch (e) {
                    // Silently fail if localStorage is not available
                }
            } else {
                $('.ai-seo-no-recommendations').show();
                $('.ai-seo-recommendations-list').hide();
                $('.ai-seo-apply-all').hide();
            }
        },

        // Show notice
        showNotice: function(type, message) {
            var noticeClass = type === 'success' ? 'notice-success' : 'notice-error';
            var notice = '<div class="notice ' + noticeClass + ' is-dismissible"><p>' + message + '</p></div>';
            
            $('.wrap h1').after(notice);
            
            // Auto-dismiss after 5 seconds
            setTimeout(function() {
                $('.notice').fadeOut();
            }, 5000);
        },

        // Utility function to format dates
        formatDate: function(dateString) {
            var date = new Date(dateString);
            return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
        },

        // Utility function to truncate text
        truncateText: function(text, length) {
            if (text.length <= length) return text;
            return text.substring(0, length) + '...';
        }
    };

    // Initialize when document is ready
    $(document).ready(function() {
        AISEOAdmin.init();
    });

    // Make AISEOAdmin available globally
    window.AISEOAdmin = AISEOAdmin;

})(jQuery);
