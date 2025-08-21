@extends('layouts.envelope')

@section('title', 'Analytics Overview')

@section('content')
<div class="envelope-container">
    <div class="paper" style="width:100%;max-width:1400px;padding:20px;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;">
            <div>
                <div class="heading-primary">Analytics Overview</div>
                <div class="text-muted">Comprehensive analytics for all your shortlinks</div>
            </div>
            <div style="display:flex;gap:8px;align-items:center;">
                <div class="analytics-controls">
                    <button class="btn btn-sm" data-period="day">Day</button>
                    <button class="btn btn-sm" data-period="week">Week</button>
                    <button class="btn btn-sm" data-period="month">Month</button>
                    <button class="btn btn-sm" data-period="year">Year</button>
                </div>
                <a href="{{ route('panel.shortlinks') }}" class="btn btn-secondary">Back to Shortlinks</a>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;margin-bottom:24px;">
            <div class="stat-card">
                <div class="stat-value" id="total-clicks">-</div>
                <div class="stat-label">Total Clicks</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" id="total-links">-</div>
                <div class="stat-label">Active Links</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" id="unique-visitors">-</div>
                <div class="stat-label">Unique Visitors</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" id="bot-percentage">-</div>
                <div class="stat-label">Bot Traffic</div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div style="display:grid;grid-template-columns:2fr 1fr;gap:24px;">
            <!-- Left Column: Charts -->
            <div class="card">
                <div class="card-header">
                    <h3>Click Trends</h3>
                </div>
                <div class="card-body">
                    <canvas id="analytics-chart" style="width:100%;height:300px;"></canvas>
                </div>
            </div>

            <!-- Right Column: Sidebar Stats -->
            <div style="display:flex;flex-direction:column;gap:16px;">
                <!-- Top Countries -->
                <div class="card">
                    <div class="card-header">
                        <h4>Top Countries</h4>
                    </div>
                    <div class="card-body">
                        <div id="countries-list" class="stats-list">
                            <div class="loading-placeholder">Loading...</div>
                        </div>
                    </div>
                </div>

                <!-- Device Types -->
                <div class="card">
                    <div class="card-header">
                        <h4>Device Types</h4>
                    </div>
                    <div class="card-body">
                        <div id="devices-list" class="stats-list">
                            <div class="loading-placeholder">Loading...</div>
                        </div>
                    </div>
                </div>

                <!-- Top Browsers -->
                <div class="card">
                    <div class="card-header">
                        <h4>Top Browsers</h4>
                    </div>
                    <div class="card-body">
                        <div id="browsers-list" class="stats-list">
                            <div class="loading-placeholder">Loading...</div>
                        </div>
                    </div>
                </div>

                <!-- Popular Links -->
                <div class="card">
                    <div class="card-header">
                        <h4>Popular Links</h4>
                    </div>
                    <div class="card-body">
                        <div id="popular-links" class="stats-list">
                            <div class="loading-placeholder">Loading...</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Period Comparison -->
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-top:24px;">
            <div class="card">
                <div class="card-header">
                    <h4>Current Period</h4>
                </div>
                <div class="card-body">
                    <div id="current-period-stats">
                        <div class="loading-placeholder">Loading...</div>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    <h4>Previous Period</h4>
                </div>
                <div class="card-body">
                    <div id="previous-period-stats">
                        <div class="loading-placeholder">Loading...</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.analytics-controls .btn {
    padding: 8px 16px;
    border: 1px solid var(--color-border);
    background: var(--color-surface);
    color: var(--color-text-secondary);
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s;
}

.analytics-controls .btn.active {
    background: var(--color-primary);
    color: white;
    border-color: var(--color-primary);
}

.stat-card {
    background: var(--color-surface);
    padding: 20px;
    border-radius: 12px;
    border: 1px solid var(--color-border);
    text-align: center;
}

.stat-value {
    font-size: 32px;
    font-weight: 700;
    color: var(--color-primary);
    margin-bottom: 8px;
}

.stat-label {
    color: var(--color-text-muted);
    font-size: 14px;
}

.card {
    background: var(--color-surface);
    border: 1px solid var(--color-border);
    border-radius: 12px;
    overflow: hidden;
}

.card-header {
    padding: 16px 20px;
    border-bottom: 1px solid var(--color-border);
    background: var(--color-surface-hover);
}

.card-header h3, .card-header h4 {
    margin: 0;
    color: var(--color-text-primary);
    font-size: 18px;
}

.card-body {
    padding: 20px;
}

.stats-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.stat-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid var(--color-border-light);
}

.stat-item:last-child {
    border-bottom: none;
}

.stat-name {
    color: var(--color-text-secondary);
}

.stat-count {
    color: var(--color-primary);
    font-weight: 600;
}

.loading-placeholder {
    color: var(--color-text-muted);
    text-align: center;
    padding: 20px;
    font-style: italic;
}

.period-stat {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 0;
    border-bottom: 1px solid var(--color-border-light);
}

.period-stat:last-child {
    border-bottom: none;
}

.period-label {
    color: var(--color-text-secondary);
}

.period-value {
    color: var(--color-text-primary);
    font-weight: 600;
}
</style>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let currentPeriod = 'week';
let analyticsChart = null;

// Initialize analytics
document.addEventListener('DOMContentLoaded', function() {
    setupPeriodButtons();
    loadAnalytics();
});

function setupPeriodButtons() {
    const buttons = document.querySelectorAll('.analytics-controls .btn');
    buttons.forEach(btn => {
        btn.addEventListener('click', function() {
            const period = this.dataset.period;
            if (period && period !== currentPeriod) {
                currentPeriod = period;
                updatePeriodButtons();
                loadAnalytics();
            }
        });
    });
    updatePeriodButtons();
}

function updatePeriodButtons() {
    const buttons = document.querySelectorAll('.analytics-controls .btn');
    buttons.forEach(btn => {
        if (btn.dataset.period === currentPeriod) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });
}

async function loadAnalytics() {
    try {
        const response = await fetch(`/api/analytics?period=${currentPeriod}`);
        const result = await response.json();
        
        if (result.ok) {
            displayAnalytics(result.data);
        } else {
            throw new Error(result.message || 'Failed to load analytics');
        }
    } catch (error) {
        console.error('Load analytics error:', error);
        displayAnalytics({
            overview: {},
            chart_data: [],
            top_countries: [],
            device_types: [],
            top_browsers: [],
            popular_links: []
        });
    }
}

function displayAnalytics(data) {
    // Update stats cards
    const overview = data.overview || {};
    document.getElementById('total-clicks').textContent = overview.total_clicks || '0';
    document.getElementById('total-links').textContent = overview.total_links || '0';
    document.getElementById('unique-visitors').textContent = overview.unique_visitors || '0';
    document.getElementById('bot-percentage').textContent = overview.bot_percentage || '0%';

    // Update chart
    if (data.chart_data && data.chart_data.length > 0) {
        updateChart(data.chart_data);
    }

    // Update sidebars
    updateSidebarStats(data);
    
    // Update period comparison
    updatePeriodComparison(data);
}

function updateChart(chartData) {
    const ctx = document.getElementById('analytics-chart').getContext('2d');
    
    if (analyticsChart) {
        analyticsChart.destroy();
    }

    const labels = chartData.map(item => item.date);
    const clicks = chartData.map(item => item.clicks);

    analyticsChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Clicks',
                data: clicks,
                borderColor: '#22c55e',
                backgroundColor: 'rgba(34, 197, 94, 0.1)',
                borderWidth: 2,
                fill: true,
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
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(255, 255, 255, 0.1)'
                    },
                    ticks: {
                        color: '#94a3b8'
                    }
                },
                x: {
                    grid: {
                        color: 'rgba(255, 255, 255, 0.1)'
                    },
                    ticks: {
                        color: '#94a3b8'
                    }
                }
            }
        }
    });
}

function updateSidebarStats(data) {
    // Top Countries
    const countries = data.top_countries || [];
    const countriesList = document.getElementById('countries-list');
    if (countries.length > 0) {
        countriesList.innerHTML = countries.map(country => 
            `<div class="stat-item">
                <span class="stat-name">${country.country || 'Unknown'}</span>
                <span class="stat-count">${country.count}</span>
            </div>`
        ).join('');
    } else {
        countriesList.innerHTML = '<div class="loading-placeholder">No data available</div>';
    }

    // Device Types
    const devices = data.device_types || [];
    const devicesList = document.getElementById('devices-list');
    if (devices.length > 0) {
        devicesList.innerHTML = devices.map(device => 
            `<div class="stat-item">
                <span class="stat-name">${device.device || 'Unknown'}</span>
                <span class="stat-count">${device.count}</span>
            </div>`
        ).join('');
    } else {
        devicesList.innerHTML = '<div class="loading-placeholder">No data available</div>';
    }

    // Top Browsers
    const browsers = data.top_browsers || [];
    const browsersList = document.getElementById('browsers-list');
    if (browsers.length > 0) {
        browsersList.innerHTML = browsers.map(browser => 
            `<div class="stat-item">
                <span class="stat-name">${browser.browser || 'Unknown'}</span>
                <span class="stat-count">${browser.count}</span>
            </div>`
        ).join('');
    } else {
        browsersList.innerHTML = '<div class="loading-placeholder">No data available</div>';
    }

    // Popular Links
    const links = data.popular_links || [];
    const popularLinks = document.getElementById('popular-links');
    if (links.length > 0) {
        popularLinks.innerHTML = links.slice(0, 5).map(link => 
            `<div class="stat-item">
                <span class="stat-name">${link.slug}</span>
                <span class="stat-count">${link.clicks}</span>
            </div>`
        ).join('');
    } else {
        popularLinks.innerHTML = '<div class="loading-placeholder">No data available</div>';
    }
}

function updatePeriodComparison(data) {
    const current = data.current_period || {};
    const previous = data.previous_period || {};
    
    const currentStats = document.getElementById('current-period-stats');
    const previousStats = document.getElementById('previous-period-stats');
    
    currentStats.innerHTML = `
        <div class="period-stat">
            <span class="period-label">Total Clicks</span>
            <span class="period-value">${current.total_clicks || '0'}</span>
        </div>
        <div class="period-stat">
            <span class="period-label">Unique Visitors</span>
            <span class="period-value">${current.unique_visitors || '0'}</span>
        </div>
        <div class="period-stat">
            <span class="period-label">Bot Traffic</span>
            <span class="period-value">${current.bot_percentage || '0%'}</span>
        </div>
    `;
    
    previousStats.innerHTML = `
        <div class="period-stat">
            <span class="period-label">Total Clicks</span>
            <span class="period-value">${previous.total_clicks || '0'}</span>
        </div>
        <div class="period-stat">
            <span class="period-label">Unique Visitors</span>
            <span class="period-value">${previous.unique_visitors || '0'}</span>
        </div>
        <div class="period-stat">
            <span class="period-label">Bot Traffic</span>
            <span class="period-value">${previous.bot_percentage || '0%'}</span>
        </div>
    `;
}
</script>
@endpush
@endsection
