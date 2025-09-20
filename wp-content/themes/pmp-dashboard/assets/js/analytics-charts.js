/**
 * Analytics Charts JavaScript
 * 
 * Handles Chart.js visualizations for progress analytics
 */

class AnalyticsCharts {
    constructor() {
        this.apiBase = '/wp-json/pmp/v1';
        this.nonce = pmp_ajax.nonce;
        this.chart = null;
        this.currentTab = 'weekly';
        this.userId = null;
    }
    
    init() {
        this.userId = document.querySelector('[data-user-id]')?.dataset.userId;
        if (!this.userId) return;
        
        this.bindEvents();
        this.loadChart('weekly');
        this.loadStudySessions();
        this.loadRecommendations();
    }
    
    bindEvents() {
        // Tab switching
        document.querySelectorAll('.analytics-tab-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const tab = e.target.dataset.tab;
                this.switchTab(tab);
            });
        });
        
        // Period filter for sessions
        const periodSelect = document.getElementById('sessionsPeriod');
        if (periodSelect) {
            periodSelect.addEventListener('change', (e) => {
                this.loadStudySessions(e.target.value);
            });
        }
    }
    
    switchTab(tab) {
        // Update active tab
        document.querySelectorAll('.analytics-tab-btn').forEach(btn => {
            btn.classList.remove('active', 'bg-white', 'text-primary', 'shadow-sm');
            btn.classList.add('text-gray-600', 'hover:text-gray-900');
        });
        
        const activeBtn = document.querySelector(`[data-tab="${tab}"]`);
        if (activeBtn) {
            activeBtn.classList.add('active', 'bg-white', 'text-primary', 'shadow-sm');
            activeBtn.classList.remove('text-gray-600', 'hover:text-gray-900');
        }
        
        this.currentTab = tab;
        this.loadChart(tab);
    }
    
    async loadChart(type) {
        this.showLoading(true);
        
        try {
            const response = await fetch(`${this.apiBase}/analytics/${this.userId}?period=month`, {
                headers: { 'X-WP-Nonce': this.nonce }
            });
            
            if (!response.ok) throw new Error('Failed to load analytics data');
            
            const data = await response.json();
            this.renderChart(type, data);
        } catch (error) {
            console.error('Error loading chart data:', error);
            this.showError('Failed to load chart data');
        } finally {
            this.showLoading(false);
        }
    }
    
    renderChart(type, data) {
        const ctx = document.getElementById('analyticsChart');
        if (!ctx) return;
        
        // Destroy existing chart
        if (this.chart) {
            this.chart.destroy();
        }
        
        let chartConfig;
        
        switch (type) {
            case 'weekly':
                chartConfig = this.getWeeklyProgressConfig(data);
                break;
            case 'domain':
                chartConfig = this.getDomainBreakdownConfig(data);
                break;
            case 'time':
                chartConfig = this.getStudyTimeConfig(data);
                break;
            default:
                return;
        }
        
        this.chart = new Chart(ctx, chartConfig);
        this.updateLegend(chartConfig);
    }
    
    getWeeklyProgressConfig(data) {
        const weeklyData = data.weekly_progress || [];
        const labels = weeklyData.map(week => {
            const date = new Date(week.week_start);
            return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
        });
        
        return {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Lessons Completed',
                    data: weeklyData.map(week => week.lessons_completed),
                    borderColor: 'rgb(91, 40, 179)',
                    backgroundColor: 'rgba(91, 40, 179, 0.1)',
                    tension: 0.4,
                    fill: true
                }, {
                    label: 'Study Time (hours)',
                    data: weeklyData.map(week => Math.round(week.time_spent / 60 * 10) / 10),
                    borderColor: 'rgb(34, 197, 94)',
                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                    tension: 0.4,
                    yAxisID: 'y1'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        mode: 'index',
                        intersect: false
                    }
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: { display: true, text: 'Lessons' }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: { display: true, text: 'Hours' },
                        grid: { drawOnChartArea: false }
                    }
                }
            }
        };
    }
    
    getDomainBreakdownConfig(data) {
        const domains = data.domain_breakdown || {};
        const domainNames = {
            people: 'People (42%)',
            process: 'Process (50%)',
            business_environment: 'Business Environment (8%)'
        };
        
        return {
            type: 'doughnut',
            data: {
                labels: Object.keys(domains).map(key => domainNames[key] || key),
                datasets: [{
                    data: Object.values(domains),
                    backgroundColor: [
                        'rgb(34, 197, 94)',   // Green for People
                        'rgb(59, 130, 246)',  // Blue for Process  
                        'rgb(249, 115, 22)'   // Orange for Business
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `${context.label}: ${context.parsed.toFixed(1)}%`;
                            }
                        }
                    }
                }
            }
        };
    }
    
    getStudyTimeConfig(data) {
        const sessions = data.study_sessions || [];
        const last7Days = [];
        
        // Generate last 7 days
        for (let i = 6; i >= 0; i--) {
            const date = new Date();
            date.setDate(date.getDate() - i);
            const dateStr = date.toISOString().split('T')[0];
            
            const session = sessions.find(s => s.session_date === dateStr);
            last7Days.push({
                date: date.toLocaleDateString('en-US', { weekday: 'short' }),
                minutes: session ? session.duration_minutes : 0
            });
        }
        
        return {
            type: 'bar',
            data: {
                labels: last7Days.map(day => day.date),
                datasets: [{
                    label: 'Study Time (minutes)',
                    data: last7Days.map(day => day.minutes),
                    backgroundColor: 'rgba(91, 40, 179, 0.8)',
                    borderColor: 'rgb(91, 40, 179)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const hours = Math.floor(context.parsed.y / 60);
                                const minutes = context.parsed.y % 60;
                                return `${hours}h ${minutes}m`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: { display: true, text: 'Minutes' }
                    }
                }
            }
        };
    }
    
    updateLegend(chartConfig) {
        const legendContainer = document.getElementById('chartLegend');
        if (!legendContainer || !chartConfig.data.datasets) return;
        
        let legendHTML = '';
        
        chartConfig.data.datasets.forEach((dataset, index) => {
            const color = dataset.backgroundColor || dataset.borderColor;
            legendHTML += `
                <div class="flex items-center">
                    <div class="w-3 h-3 rounded-full mr-2" style="background-color: ${Array.isArray(color) ? color[0] : color}"></div>
                    <span class="text-gray-700">${dataset.label}</span>
                </div>
            `;
        });
        
        legendContainer.innerHTML = legendHTML;
    }
    
    async loadStudySessions(period = 'month') {
        const container = document.getElementById('studySessionsList');
        if (!container) return;
        
        try {
            const response = await fetch(`${this.apiBase}/analytics/${this.userId}?period=${period}`, {
                headers: { 'X-WP-Nonce': this.nonce }
            });
            
            if (!response.ok) throw new Error('Failed to load sessions');
            
            const data = await response.json();
            this.renderStudySessions(data.study_sessions || []);
        } catch (error) {
            console.error('Error loading study sessions:', error);
            container.innerHTML = '<div class="text-center text-gray-500 py-4">Failed to load study sessions</div>';
        }
    }
    
    renderStudySessions(sessions) {
        const container = document.getElementById('studySessionsList');
        if (!container) return;
        
        if (sessions.length === 0) {
            container.innerHTML = '<div class="text-center text-gray-500 py-4">No study sessions found</div>';
            return;
        }
        
        const sessionsHTML = sessions.slice(0, 5).map(session => {
            const date = new Date(session.session_date);
            const hours = Math.floor(session.duration_minutes / 60);
            const minutes = session.duration_minutes % 60;
            
            return `
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-primary rounded-full flex items-center justify-center mr-3">
                            <i class="fas fa-book text-white text-sm"></i>
                        </div>
                        <div>
                            <div class="font-medium text-gray-900">
                                ${date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}
                            </div>
                            <div class="text-sm text-gray-600">
                                ${session.lessons_completed} lesson${session.lessons_completed !== 1 ? 's' : ''} • ${session.domain_focus}
                            </div>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="font-semibold text-gray-900">
                            ${hours > 0 ? `${hours}h ` : ''}${minutes}m
                        </div>
                    </div>
                </div>
            `;
        }).join('');
        
        container.innerHTML = sessionsHTML;
    }
    
    async loadRecommendations() {
        const container = document.getElementById('recommendationsList');
        if (!container) return;
        
        try {
            const response = await fetch(`${this.apiBase}/analytics/${this.userId}`, {
                headers: { 'X-WP-Nonce': this.nonce }
            });
            
            if (!response.ok) throw new Error('Failed to load recommendations');
            
            const data = await response.json();
            this.renderRecommendations(data.recommendations || []);
        } catch (error) {
            console.error('Error loading recommendations:', error);
            container.innerHTML = '<div class="text-center text-gray-500 py-4">Failed to load recommendations</div>';
        }
    }
    
    renderRecommendations(recommendations) {
        const container = document.getElementById('recommendationsList');
        if (!container) return;
        
        if (recommendations.length === 0) {
            container.innerHTML = '<div class="text-center text-gray-500 py-4">Keep up the great work! No specific recommendations at this time.</div>';
            return;
        }
        
        const recommendationsHTML = recommendations.map(rec => `
            <div class="flex items-start p-3 bg-white rounded-lg border border-blue-200">
                <i class="fas fa-arrow-right text-blue-500 mt-1 mr-3"></i>
                <span class="text-gray-700">${rec}</span>
            </div>
        `).join('');
        
        container.innerHTML = recommendationsHTML;
    }
    
    showLoading(show) {
        const loading = document.getElementById('chartLoading');
        if (loading) {
            loading.classList.toggle('hidden', !show);
        }
    }
    
    showError(message) {
        const ctx = document.getElementById('analyticsChart');
        if (ctx) {
            const parent = ctx.parentElement;
            parent.innerHTML = `
                <div class="flex items-center justify-center py-8 text-gray-500">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    ${message}
                </div>
            `;
        }
    }
}

// Initialize and export
window.analyticsCharts = new AnalyticsCharts();
