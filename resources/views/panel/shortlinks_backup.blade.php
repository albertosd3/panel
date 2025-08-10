@extends('layouts.envelope')

@section('title', 'Professional Shortlink Management')

@push('styles')
<style>
    /* Dashboard-specific styles */
    .envelope-container {
        padding: 20px;
        min-height: 100vh;
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 50%, #cbd5e1 100%);
    }
    
    .dashboard-wrapper {
        max-width: 1400px;
        margin: 0 auto;
    }
    
    .dashboard-header {
        background: var(--color-white);
        border-radius: 12px;
        padding: 24px 32px;
        margin-bottom: 24px;
        box-shadow: var(--shadow-lg);
        border: 1px solid rgba(0, 0, 0, 0.05);
        position: relative;
        overflow: hidden;
    }
    
    .dashboard-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--color-primary) 0%, var(--color-accent) 100%);
    }
    
    .header-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .header-info h1 {
        font-family: var(--font-serif);
        font-size: 32px;
        font-weight: 600;
        color: var(--color-dark);
        margin-bottom: 4px;
        letter-spacing: -0.025em;
    }
    
    .header-subtitle {
        color: var(--color-muted);
        font-size: 14px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .status-indicator {
        width: 8px;
        height: 8px;
        background: var(--color-success);
        border-radius: 50%;
        display: inline-block;
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }
    
    .header-actions {
        display: flex;
        gap: 12px;
        align-items: center;
    }
    
    .btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 16px;
        border: none;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        text-decoration: none;
        transition: all 0.2s ease;
        font-family: var(--font-primary);
    }
    
    .btn-primary {
        background: var(--color-primary);
        color: var(--color-white);
    }
    
    .btn-primary:hover {
        background: var(--color-primary-dark);
        transform: translateY(-1px);
    }
    
    .btn-danger {
        background: var(--color-danger);
        color: var(--color-white);
    }
    
    .btn-danger:hover {
        background: #b91c1c;
        transform: translateY(-1px);
    }
    
    .btn-secondary {
        background: var(--color-light);
        color: var(--color-dark);
        border: 1px solid #e5e7eb;
    }
    
    .btn-secondary:hover {
        background: #f1f5f9;
        border-color: #d1d5db;
    }
    
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 24px;
    }
    
    .stat-card {
        background: var(--color-white);
        border-radius: 12px;
        padding: 24px;
        box-shadow: var(--shadow-md);
        border: 1px solid rgba(0, 0, 0, 0.05);
        transition: all 0.2s ease;
        position: relative;
        overflow: hidden;
    }
    
    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, var(--color-primary) 0%, var(--color-accent) 100%);
    }
    
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-lg);
    }
    
    .stat-content {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    
    .stat-info h3 {
        font-size: 28px;
        font-weight: 700;
        color: var(--color-dark);
        margin-bottom: 4px;
        font-family: var(--font-primary);
    }
    
    .stat-info p {
        color: var(--color-muted);
        font-size: 13px;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-accent) 100%);
        color: var(--color-white);
    }
    
    .main-grid {
        display: grid;
        grid-template-columns: 1fr 350px;
        gap: 24px;
        margin-bottom: 24px;
    }
    
    .content-card {
        background: var(--color-white);
        border-radius: 12px;
        padding: 24px;
        box-shadow: var(--shadow-md);
        border: 1px solid rgba(0, 0, 0, 0.05);
        position: relative;
        overflow: hidden;
    }
    
    .content-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, var(--color-primary) 0%, var(--color-accent) 100%);
    }
    
    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 16px;
        border-bottom: 1px solid #f1f5f9;
    }
    
    .card-title {
        font-family: var(--font-serif);
        font-size: 20px;
        font-weight: 600;
        color: var(--color-dark);
    }
    
    .analytics-controls {
        display: flex;
        gap: 8px;
    }
    
    .period-btn {
        padding: 6px 12px;
        border: 1px solid #e5e7eb;
        background: var(--color-white);
        color: var(--color-muted);
        border-radius: 6px;
        font-size: 12px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    
    .period-btn.active {
        background: var(--color-primary);
        border-color: var(--color-primary);
        color: var(--color-white);
    }
    
    .period-btn:hover:not(.active) {
        background: var(--color-light);
        border-color: #d1d5db;
    }
    
    .chart-container {
        position: relative;
        height: 300px;
        margin-bottom: 16px;
    }
    
    .comparison-stats {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
        padding: 16px;
        background: #f8fafc;
        border-radius: 8px;
        margin-top: 16px;
    }
    
    .comparison-item {
        text-align: center;
    }
    
    .comparison-value {
        font-size: 18px;
        font-weight: 600;
        color: var(--color-dark);
    }
    
    .comparison-label {
        font-size: 12px;
        color: var(--color-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-top: 4px;
    }
    
    .comparison-change {
        font-size: 12px;
        font-weight: 500;
        margin-top: 4px;
    }
    
    .change-positive { color: var(--color-success); }
    .change-negative { color: var(--color-danger); }
    .change-neutral { color: var(--color-muted); }
    
    /* Form Styles */
    .form-group {
        margin-bottom: 20px;
    }
    
    .form-label {
        display: block;
        font-weight: 500;
        color: var(--color-dark);
        margin-bottom: 6px;
        font-size: 13px;
    }
    
    .form-input {
        width: 100%;
        padding: 12px 14px;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        font-size: 14px;
        background: var(--color-white);
        transition: all 0.2s ease;
        font-family: var(--font-primary);
    }
    
    .form-input:focus {
        outline: none;
        border-color: var(--color-primary);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }
    
    .form-input::placeholder {
        color: #9ca3af;
    }
    
    /* Links Table */
    .links-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 16px;
    }
    
    .links-table th,
    .links-table td {
        padding: 12px;
        text-align: left;
        border-bottom: 1px solid #f1f5f9;
    }
    
    .links-table th {
        background: #f8fafc;
        font-weight: 600;
        color: var(--color-dark);
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .links-table td {
        font-size: 13px;
        color: var(--color-dark);
    }
    
    .links-table tr:hover {
        background: #f8fafc;
    }
    
    .link-slug {
        font-family: 'SF Mono', 'Monaco', 'Consolas', 'Roboto Mono', monospace;
        background: #f3f4f6;
        padding: 4px 6px;
        border-radius: 4px;
        font-size: 11px;
        color: var(--color-primary);
        font-weight: 500;
    }
    
    .link-destination {
        max-width: 200px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        color: var(--color-muted);
    }
    
    .clicks-badge {
        background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-accent) 100%);
        color: var(--color-white);
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
    }
    
    .status-active {
        color: var(--color-success);
        font-weight: 600;
    }
    
    .status-inactive {
        color: var(--color-danger);
        font-weight: 600;
    }
    
    .loading {
        text-align: center;
        padding: 40px;
        color: var(--color-muted);
    }
    
    .loading-spinner {
        width: 32px;
        height: 32px;
        border: 3px solid #f3f4f6;
        border-top: 3px solid var(--color-primary);
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin: 0 auto 16px;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    /* Sidebar Styles */
    .sidebar-list {
        list-style: none;
    }
    
    .sidebar-item {
        padding: 12px 0;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .sidebar-item:last-child {
        border-bottom: none;
    }
    
    .sidebar-label {
        font-size: 13px;
        color: var(--color-dark);
        font-weight: 500;
    }
    
    .sidebar-value {
        font-size: 13px;
        color: var(--color-muted);
        font-weight: 500;
    }
    
    .country-flag {
        width: 16px;
        height: 12px;
        margin-right: 8px;
        border-radius: 2px;
    }
    
    /* Responsive */
    @media (max-width: 1200px) {
        .main-grid {
            grid-template-columns: 1fr;
        }
    }
    
    @media (max-width: 768px) {
        .dashboard-wrapper {
            padding: 0 12px;
        }
        
        .header-content {
            flex-direction: column;
            gap: 16px;
            align-items: flex-start;
        }
        
        .header-actions {
            width: 100%;
            justify-content: flex-start;
        }
        
        .stats-grid {
            grid-template-columns: 1fr;
        }
        
        .analytics-controls {
            flex-wrap: wrap;
        }
        
        .comparison-stats {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<div class="dashboard-wrapper">
    <!-- Header -->
    <div class="paper dashboard-header">
        <div class="header-content">
            <div class="header-info">
                <h1>Shortlink Management</h1>
                <div class="header-subtitle">
                    <span class="status-indicator"></span>
                    Professional Analytics Dashboard
                    <span id="current-time"></span>
                </div>
            </div>
            <div class="header-actions">
                <button class="btn btn-primary" onclick="refreshData()">
                    🔄 Refresh
                </button>
                <form method="POST" action="/logout" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-danger">🚪 Logout</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Statistics Overview -->
    <div class="stats-grid" id="stats-container">
        <div class="stat-card">
            <div class="stat-content">
                <div class="stat-info">
                    <h3 id="total-links">-</h3>
                    <p>Total Links</p>
                </div>
                <div class="stat-icon">📎</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-content">
                <div class="stat-info">
                    <h3 id="total-clicks">-</h3>
                    <p>Total Clicks</p>
                </div>
                <div class="stat-icon">👆</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-content">
                <div class="stat-info">
                    <h3 id="today-clicks">-</h3>
                    <p>Today's Clicks</p>
                </div>
                <div class="stat-icon">📊</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-content">
                <div class="stat-info">
                    <h3 id="avg-clicks">-</h3>
                    <p>Avg per Link</p>
                </div>
                <div class="stat-icon">📈</div>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="main-grid">
        <!-- Left Column: Analytics & Link Management -->
        <div>
            <!-- Analytics Chart -->
            <div class="paper content-card">
                <div class="card-header">
                    <h2 class="card-title">Analytics Overview</h2>
                    <div class="analytics-controls">
                        <button class="period-btn active" data-period="day">Day</button>
                        <button class="period-btn" data-period="week">Week</button>
                        <button class="period-btn" data-period="month">Month</button>
                        <button class="period-btn" data-period="year">Year</button>
                    </div>
                </div>
                <div class="chart-container">
                    <canvas id="analytics-chart"></canvas>
                </div>
                <div class="comparison-stats" id="comparison-stats">
                    <div class="comparison-item">
                        <div class="comparison-value" id="current-period">-</div>
                        <div class="comparison-label">Current Period</div>
                    </div>
                    <div class="comparison-item">
                        <div class="comparison-value" id="previous-period">-</div>
                        <div class="comparison-label" id="comparison-label">Previous Period</div>
                        <div class="comparison-change" id="comparison-change">-</div>
                    </div>
                </div>
            </div>

            <!-- Link Creation -->
            <div class="paper content-card" style="margin-top: 24px;">
                <div class="card-header">
                    <h2 class="card-title">Create New Shortlink</h2>
                </div>
                <form id="create-form">
                    <div class="form-group">
                        <label class="form-label" for="destination">Destination URL</label>
                        <input type="url" 
                               id="destination" 
                               name="destination" 
                               class="form-input" 
                               placeholder="https://example.com/your-long-url"
                               required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="slug">Custom Slug (optional)</label>
                        <input type="text" 
                               id="slug" 
                               name="slug" 
                               class="form-input" 
                               placeholder="my-custom-link"
                               pattern="[a-zA-Z0-9_-]+"
                               title="Only letters, numbers, hyphens and underscores allowed">
                    </div>
                    <button type="submit" class="btn btn-primary">✨ Create Shortlink</button>
                </form>
            </div>

            <!-- Links Table -->
            <div class="paper content-card" style="margin-top: 24px;">
                <div class="card-header">
                    <h2 class="card-title">Recent Shortlinks</h2>
                </div>
                <div id="links-container">
                    <div class="loading">
                        <div class="loading-spinner"></div>
                        Loading shortlinks...
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Statistics & Info -->
        <div>
            <!-- Top Countries -->
            <div class="paper content-card">
                <div class="card-header">
                    <h2 class="card-title">Top Countries</h2>
                </div>
                <ul class="sidebar-list" id="countries-list">
                    <li class="sidebar-item">
                        <div class="loading">Loading...</div>
                    </li>
                </ul>
            </div>

            <!-- Device Statistics -->
            <div class="paper content-card" style="margin-top: 24px;">
                <div class="card-header">
                    <h2 class="card-title">Device Types</h2>
                </div>
                <ul class="sidebar-list" id="devices-list">
                    <li class="sidebar-item">
                        <div class="loading">Loading...</div>
                    </li>
                </ul>
            </div>

            <!-- Browser Statistics -->
            <div class="paper content-card" style="margin-top: 24px;">
                <div class="card-header">
                    <h2 class="card-title">Top Browsers</h2>
                </div>
                <ul class="sidebar-list" id="browsers-list">
                    <li class="sidebar-item">
                        <div class="loading">Loading...</div>
                    </li>
                </ul>
            </div>

            <!-- Top Links -->
            <div class="paper content-card" style="margin-top: 24px;">
                <div class="card-header">
                    <h2 class="card-title">Popular Links</h2>
                </div>
                <ul class="sidebar-list" id="popular-links">
                    <li class="sidebar-item">
                        <div class="loading">Loading...</div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
// Global variables
let currentPeriod = 'week';
let analyticsChart = null;

// Initialize dashboard
document.addEventListener('DOMContentLoaded', function() {
    initializeTime();
    loadAnalytics();
    loadLinks();
    setupEventListeners();
    
    // Auto-refresh every 30 seconds
    setInterval(() => {
        loadAnalytics();
        loadLinks();
    }, 30000);
});

function initializeTime() {
    function updateTime() {
        const now = new Date();
        const timeString = now.toLocaleTimeString('id-ID', { 
            hour: '2-digit', 
            minute: '2-digit',
            second: '2-digit'
        });
        document.getElementById('current-time').textContent = `• ${timeString}`;
    }
    
    updateTime();
    setInterval(updateTime, 1000);
}

function setupEventListeners() {
    // Period buttons
    document.querySelectorAll('.period-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.period-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentPeriod = this.dataset.period;
            loadAnalytics();
        });
    });
    
    // Create form
    document.getElementById('create-form').addEventListener('submit', function(e) {
        e.preventDefault();
        createShortlink();
    });
}

async function loadAnalytics() {
    try {
        const response = await fetch(`/api/analytics?period=${currentPeriod}`);
        const data = await response.json();
        
        if (data.ok) {
            updateOverviewStats(data.data.overview);
            updateChart(data.data.timeline, data.data.period);
            updateComparison(data.data.comparison);
            updateSidebarStats(data.data);
        }
    } catch (error) {
        console.error('Failed to load analytics:', error);
    }
}

function updateOverviewStats(overview) {
    document.getElementById('total-links').textContent = overview.total_links.toLocaleString();
    document.getElementById('total-clicks').textContent = overview.total_clicks.toLocaleString();
    document.getElementById('today-clicks').textContent = overview.today_clicks.toLocaleString();
    document.getElementById('avg-clicks').textContent = overview.avg_clicks_per_link;
}

function updateChart(timeline, period) {
    const ctx = document.getElementById('analytics-chart').getContext('2d');
    
    if (analyticsChart) {
        analyticsChart.destroy();
    }
    
    analyticsChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: timeline.map(item => item.date),
            datasets: [{
                label: 'Clicks',
                data: timeline.map(item => item.clicks),
                borderColor: '#2563eb',
                backgroundColor: 'rgba(37, 99, 235, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#2563eb',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6
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
                x: {
                    grid: {
                        color: '#f1f5f9',
                        borderColor: '#e5e7eb'
                    },
                    ticks: {
                        color: '#64748b',
                        font: {
                            size: 11
                        }
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: {
                        color: '#f1f5f9',
                        borderColor: '#e5e7eb'
                    },
                    ticks: {
                        color: '#64748b',
                        font: {
                            size: 11
                        }
                    }
                }
            },
            interaction: {
                intersect: false,
                mode: 'index'
            }
        }
    });
}

function updateComparison(comparison) {
    document.getElementById('current-period').textContent = comparison.current.toLocaleString();
    document.getElementById('previous-period').textContent = comparison.previous.toLocaleString();
    document.getElementById('comparison-label').textContent = comparison.label;
    
    const changeElement = document.getElementById('comparison-change');
    const changeValue = comparison.change;
    
    changeElement.className = 'comparison-change ';
    
    if (changeValue > 0) {
        changeElement.className += 'change-positive';
        changeElement.textContent = `+${changeValue.toFixed(1)}%`;
    } else if (changeValue < 0) {
        changeElement.className += 'change-negative';
        changeElement.textContent = `${changeValue.toFixed(1)}%`;
    } else {
        changeElement.className += 'change-neutral';
        changeElement.textContent = '0%';
    }
}

function updateSidebarStats(data) {
    // Countries
    const countriesList = document.getElementById('countries-list');
    if (data.top_countries && data.top_countries.length > 0) {
        countriesList.innerHTML = data.top_countries.map(country => `
            <li class="sidebar-item">
                <span class="sidebar-label">${country.country || 'Unknown'}</span>
                <span class="sidebar-value">${country.count.toLocaleString()}</span>
            </li>
        `).join('');
    } else {
        countriesList.innerHTML = '<li class="sidebar-item"><span class="sidebar-label">No data available</span></li>';
    }
    
    // Devices
    const devicesList = document.getElementById('devices-list');
    if (data.device_stats && data.device_stats.length > 0) {
        devicesList.innerHTML = data.device_stats.map(device => `
            <li class="sidebar-item">
                <span class="sidebar-label">${device.device || 'Unknown'}</span>
                <span class="sidebar-value">${device.count.toLocaleString()}</span>
            </li>
        `).join('');
    } else {
        devicesList.innerHTML = '<li class="sidebar-item"><span class="sidebar-label">No data available</span></li>';
    }
    
    // Browsers
    const browsersList = document.getElementById('browsers-list');
    if (data.browser_stats && data.browser_stats.length > 0) {
        browsersList.innerHTML = data.browser_stats.map(browser => `
            <li class="sidebar-item">
                <span class="sidebar-label">${browser.browser || 'Unknown'}</span>
                <span class="sidebar-value">${browser.count.toLocaleString()}</span>
            </li>
        `).join('');
    } else {
        browsersList.innerHTML = '<li class="sidebar-item"><span class="sidebar-label">No data available</span></li>';
    }
    
    // Popular links
    const popularLinks = document.getElementById('popular-links');
    if (data.top_links && data.top_links.length > 0) {
        popularLinks.innerHTML = data.top_links.map(link => `
            <li class="sidebar-item">
                <div>
                    <div class="sidebar-label">${link.slug}</div>
                    <div style="font-size: 11px; color: var(--color-muted); margin-top: 2px;">${link.destination.substring(0, 30)}${link.destination.length > 30 ? '...' : ''}</div>
                </div>
                <span class="sidebar-value">${link.clicks.toLocaleString()}</span>
            </li>
        `).join('');
    } else {
        popularLinks.innerHTML = '<li class="sidebar-item"><span class="sidebar-label">No links yet</span></li>';
    }
}

async function loadLinks() {
    try {
        const response = await fetch('/api/links');
        const data = await response.json();
        
        if (data.ok && data.data) {
            displayLinks(data.data);
        }
    } catch (error) {
        console.error('Failed to load links:', error);
        document.getElementById('links-container').innerHTML = '<div class="loading">Failed to load links</div>';
    }
}

function displayLinks(links) {
    const container = document.getElementById('links-container');
    
    if (links.length === 0) {
        container.innerHTML = '<div class="loading">No shortlinks created yet</div>';
        return;
    }
    
    const table = `
        <table class="links-table">
            <thead>
                <tr>
                    <th>Slug</th>
                    <th>Destination</th>
                    <th>Clicks</th>
                    <th>Status</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody>
                ${links.map(link => `
                    <tr>
                        <td><span class="link-slug">${link.slug}</span></td>
                        <td><div class="link-destination" title="${link.destination}">${link.destination}</div></td>
                        <td><span class="clicks-badge">${link.clicks}</span></td>
                        <td><span class="status-${link.active ? 'active' : 'inactive'}">${link.active ? 'Active' : 'Inactive'}</span></td>
                        <td>${new Date(link.created_at).toLocaleDateString('id-ID')}</td>
                    </tr>
                `).join('')}
            </tbody>
        </table>
    `;
    
    container.innerHTML = table;
}

async function createShortlink() {
    const form = document.getElementById('create-form');
    const formData = new FormData(form);
    const destination = formData.get('destination');
    const slug = formData.get('slug');
    
    try {
        const response = await fetch('/api/create', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ destination, slug })
        });
        
        const data = await response.json();
        
        if (data.ok) {
            form.reset();
            loadLinks();
            loadAnalytics();
            
            // Show success message
            alert(`Shortlink created successfully!\nSlug: ${data.data.slug}\nURL: ${window.location.origin}/${data.data.slug}`);
        } else {
            alert(`Error: ${data.error || 'Failed to create shortlink'}`);
        }
    } catch (error) {
        console.error('Failed to create shortlink:', error);
        alert('Failed to create shortlink. Please try again.');
    }
}

function refreshData() {
    loadAnalytics();
    loadLinks();
}
</script>
@endsection
            display: grid;
            grid-template-columns: 350px 1fr;
            gap: 20px;
        }
        
        .card {
            background: #ffffff;
            border-radius: 8px;
            padding: 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-left: 4px solid #667eea;
        }
        
        .card h3 {
            color: #2c3e50;
            margin-bottom: 20px;
            font-size: 18px;
            font-weight: 600;
            padding-bottom: 10px;
            border-bottom: 1px solid #ecf0f1;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #34495e;
            font-weight: 500;
            font-size: 14px;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #bdc3c7;
            border-radius: 4px;
            font-size: 14px;
            transition: border-color 0.3s ease;
            background: #fdfdfd;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: transform 0.2s ease;
        }
        
        .btn:hover {
            transform: translateY(-1px);
        }
        
        .shortlink-list {
            max-height: 400px;
            overflow-y: auto;
            margin-top: 20px;
        }
        
        .shortlink-item {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 4px;
            padding: 15px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .shortlink-item:hover {
            background: #e3f2fd;
            border-color: #2196f3;
        }
        
        .shortlink-item.active {
            background: #e8f5e8;
            border-color: #4caf50;
            border-left: 4px solid #4caf50;
        }
        
        .shortlink-slug {
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
            font-family: 'Courier New', monospace;
        }
        
        .shortlink-dest {
            font-size: 12px;
            color: #7f8c8d;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            margin-bottom: 5px;
        }
        
        .shortlink-clicks {
            font-size: 12px;
            color: #27ae60;
            font-weight: 500;
        }
        
        .analytics-section {
            display: grid;
            grid-template-rows: auto 1fr;
            gap: 20px;
        }
        
        .chart-container {
            background: #ffffff;
            border-radius: 8px;
            padding: 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-left: 4px solid #e67e22;
        }
        
        .chart-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .chart-tab {
            background: #ecf0f1;
            border: 1px solid #bdc3c7;
            color: #2c3e50;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 12px;
            font-weight: 500;
        }
        
        .chart-tab.active {
            background: #667eea;
            border-color: #667eea;
            color: white;
        }
        
        .details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .selected-info {
            background: #ffffff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-left: 4px solid #f39c12;
        }
        
        .selected-stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .mini-stat {
            text-align: center;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 4px;
            border: 1px solid #e9ecef;
        }
        
        .mini-stat-number {
            font-size: 20px;
            font-weight: bold;
            color: #2c3e50;
            display: block;
        }
        
        .mini-stat-label {
            font-size: 11px;
            color: #7f8c8d;
            text-transform: uppercase;
        }
        
        .activity-card {
            background: #ffffff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-left: 4px solid #9b59b6;
        }
        
        .table-responsive {
            overflow-x: auto;
            margin-top: 15px;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }
        
        .table th,
        .table td {
            padding: 8px 10px;
            text-align: left;
            border-bottom: 1px solid #ecf0f1;
        }
        
        .table th {
            background: #f8f9fa;
            color: #2c3e50;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.5px;
        }
        
        .table td {
            color: #34495e;
        }
        
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: 500;
            text-transform: uppercase;
        }
        
        .badge-success {
            background: #d4edda;
            color: #155724;
        }
        
        .badge-danger {
            background: #f8d7da;
            color: #721c24;
        }
        
        .success-message {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 12px;
            border-radius: 4px;
            margin-top: 15px;
            display: none;
        }
        
        .error-message {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 12px;
            border-radius: 4px;
            margin-top: 15px;
            display: none;
        }
        
        /* Scrollbar styling */
        ::-webkit-scrollbar {
            width: 6px;
        }
        
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 3px;
        }
        
        ::-webkit-scrollbar-thumb {
            background: #bdc3c7;
            border-radius: 3px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: #95a5a6;
        }
        
        @media (max-width: 1024px) {
            .main-grid {
                grid-template-columns: 1fr;
            }
            
            .details-grid {
                grid-template-columns: 1fr;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }
            
            .header {
                flex-direction: column;
                text-align: center;
                gap: 15px;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .chart-tabs {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div>
                <h1>Shortlink Management Panel</h1>
                <div class="subtitle">Professional URL shortening service</div>
            </div>
            <form method="POST" action="{{ route('panel.logout') }}">
                @csrf
                <button type="submit" class="btn-logout">Sign Out</button>
            </form>
        </div>

        <!-- Statistics Overview -->
        <div class="stats-grid">
            <div class="stat-card">
                <span class="stat-number" id="totalLinks">0</span>
                <div class="stat-label">Total Links</div>
            </div>
            <div class="stat-card">
                <span class="stat-number" id="totalClicks">0</span>
                <div class="stat-label">Total Clicks</div>
            </div>
            <div class="stat-card">
                <span class="stat-number" id="todayClicks">0</span>
                <div class="stat-label">Today's Clicks</div>
            </div>
            <div class="stat-card">
                <span class="stat-number" id="avgClicks">0</span>
                <div class="stat-label">Average per Link</div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-grid">
            <!-- Sidebar: Create Form & Links List -->
            <div>
                <div class="card">
                    <h3>Create New Shortlink</h3>
                    <form id="createForm">
                        <div class="form-group">
                            <label for="destination">Destination URL</label>
                            <input type="text" id="destination" name="destination" class="form-control" 
                                   placeholder="https://example.com/page" required>
                        </div>
                        <div class="form-group">
                            <label for="slug">Custom Slug (optional)</label>
                            <input type="text" id="slug" name="slug" class="form-control" 
                                   placeholder="my-custom-link" pattern="[a-zA-Z0-9_-]+">
                        </div>
                        <button type="submit" class="btn">Create Shortlink</button>
                    </form>
                    
                    <div id="successMessage" class="success-message"></div>
                    <div id="errorMessage" class="error-message"></div>
                </div>

                <div class="card" style="margin-top: 20px;">
                    <h3>Your Shortlinks</h3>
                    <div class="shortlink-list" id="shortlinksList">
                        <div id="linksContainer"></div>
                    </div>
                </div>
            </div>

            <!-- Main Analytics Area -->
            <div class="analytics-section">
                <!-- Charts -->
                <div class="chart-container">
                    <h3>Analytics Overview</h3>
                    <div class="chart-tabs">
                        <button class="chart-tab active" data-chart="clicks">Timeline</button>
                        <button class="chart-tab" data-chart="top">Top Links</button>
                        <button class="chart-tab" data-chart="countries">Countries</button>
                        <button class="chart-tab" data-chart="devices">Devices</button>
                    </div>
                    <div style="height: 300px; position: relative;">
                        <canvas id="mainChart"></canvas>
                    </div>
                </div>

                <!-- Selected Link Details -->
                <div class="details-grid">
                    <div class="selected-info">
                        <h3>Selected: <span id="selectedSlug">None</span></h3>
                        <div class="selected-stats">
                            <div class="mini-stat">
                                <span class="mini-stat-number" id="selectedClicks">0</span>
                                <div class="mini-stat-label">Total Clicks</div>
                            </div>
                            <div class="mini-stat">
                                <span class="mini-stat-number" id="selectedToday">0</span>
                                <div class="mini-stat-label">Today</div>
                            </div>
                        </div>
                        <div id="selectedUrl" style="font-size: 12px; color: #7f8c8d; word-break: break-all;"></div>
                    </div>

                    <div class="activity-card">
                        <h3>Recent Activity</h3>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Time</th>
                                        <th>Country</th>
                                        <th>Device</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody id="recentActivity"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Global variables
        let currentChart = null;
        let currentSlug = null;
        let allLinks = [];
        let allStats = {};

        // Chart.js default config
        Chart.defaults.color = '#2c3e50';
        Chart.defaults.borderColor = '#ecf0f1';

        // DOM elements
        const createForm = document.getElementById('createForm');
        const successMessage = document.getElementById('successMessage');
        const errorMessage = document.getElementById('errorMessage');
        const linksContainer = document.getElementById('linksContainer');
        const chartTabs = document.querySelectorAll('.chart-tab');
        const mainChart = document.getElementById('mainChart').getContext('2d');

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            loadDashboardData();
            setInterval(updateRealTimeStats, 5000); // Update every 5 seconds
        });

        // Create shortlink form handler
        createForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(createForm);
            const payload = Object.fromEntries(formData.entries());
            
            try {
                const response = await fetch('{{ route('panel.shortlinks.store') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams(payload)
                });
                
                const data = await response.json();
                
                if (data.ok) {
                    showSuccess(`Shortlink created successfully: <a href="${data.short_url}" target="_blank" style="color: #2980b9; text-decoration: underline;">${data.short_url}</a>`);
                    createForm.reset();
                    setTimeout(() => {
                        loadDashboardData();
                    }, 500);
                } else {
                    showError(data.message || 'Failed to create shortlink');
                }
            } catch (error) {
                showError('An error occurred: ' + error.message);
            }
        });

        // Chart tab handlers
        chartTabs.forEach(tab => {
            tab.addEventListener('click', function() {
                chartTabs.forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                updateChart(this.dataset.chart);
            });
        });

        // Load all dashboard data
        async function loadDashboardData() {
            try {
                // Load shortlinks list
                const linksResponse = await fetch('{{ route('panel.shortlinks.list') }}');
                const linksData = await linksResponse.json();
                
                if (linksData.ok) {
                    allLinks = linksData.data;
                    renderShortlinksList();
                }

                // Load analytics data
                const analyticsResponse = await fetch('{{ route('panel.analytics') }}');
                const analyticsData = await analyticsResponse.json();
                
                if (analyticsData.ok) {
                    updateDashboardStats(analyticsData.data);
                    updateChart('clicks', analyticsData.data);
                }
            } catch (error) {
                console.error('Failed to load dashboard data:', error);
            }
        }

        // Render shortlinks list
        function renderShortlinksList() {
            linksContainer.innerHTML = '';
            
            if (!allLinks || allLinks.length === 0) {
                linksContainer.innerHTML = '<div style="text-align:center; color:#7f8c8d; padding:20px; font-style:italic;">No shortlinks created yet</div>';
                return;
            }
            
            allLinks.forEach(link => {
                const item = document.createElement('div');
                item.className = 'shortlink-item';
                item.innerHTML = `
                    <div class="shortlink-slug">/${link.slug}</div>
                    <div class="shortlink-dest">${link.destination}</div>
                    <div class="shortlink-clicks">${link.clicks} clicks</div>
                `;
                
                item.addEventListener('click', () => selectShortlink(link.slug));
                linksContainer.appendChild(item);
            });
        }

        // Update dashboard statistics
        function updateDashboardStats(data) {
            const overview = data.overview;
            
            document.getElementById('totalLinks').textContent = overview.total_links.toLocaleString();
            document.getElementById('totalClicks').textContent = overview.total_clicks.toLocaleString();
            document.getElementById('todayClicks').textContent = overview.today_clicks.toLocaleString();
            document.getElementById('avgClicks').textContent = overview.avg_clicks_per_link.toLocaleString();
            
            // Store data for charts
            window.analyticsData = data;
        }

        // Update chart based on type
        function updateChart(type, analyticsData = null) {
            if (currentChart) {
                currentChart.destroy();
            }

            const data = analyticsData || window.analyticsData;
            if (!data) return;

            let chartConfig;

            switch (type) {
                case 'clicks':
                    chartConfig = getClicksTimelineChart(data.timeline);
                    break;
                case 'top':
                    chartConfig = getTopShortlinksChart(data.top_links);
                    break;
                case 'countries':
                    chartConfig = getCountriesChart(data.top_countries);
                    break;
                case 'devices':
                    chartConfig = getDevicesChart(data.device_stats);
                    break;
            }

            currentChart = new Chart(mainChart, chartConfig);
        }

        // Chart configurations
        function getClicksTimelineChart(timelineData) {
            const labels = timelineData.map(item => item.date);
            const data = timelineData.map(item => item.clicks);

            return {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Clicks',
                        data: data,
                        borderColor: '#667eea',
                        backgroundColor: 'rgba(102, 126, 234, 0.1)',
                        tension: 0.4,
                        fill: true,
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: { beginAtZero: true },
                        x: {}
                    }
                }
            };
        }

        function getTopShortlinksChart(topLinksData) {
            const labels = topLinksData.map(link => link.slug);
            const data = topLinksData.map(link => link.clicks);
            
            return {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Clicks',
                        data: data,
                        backgroundColor: '#3498db',
                        borderColor: '#2980b9',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true } }
                }
            };
        }

        function getCountriesChart(countriesData) {
            const labels = countriesData.map(item => item.country || 'Unknown');
            const data = countriesData.map(item => item.count);
            const colors = ['#3498db', '#e74c3c', '#f39c12', '#2ecc71', '#9b59b6', '#1abc9c', '#34495e', '#e67e22', '#95a5a6', '#f1c40f'];

            return {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: data,
                        backgroundColor: colors.slice(0, data.length),
                        borderWidth: 2,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom' } }
                }
            };
        }

        function getDevicesChart(deviceData) {
            const deviceLabels = deviceData.map(item => item.device || 'Unknown');
            const deviceCounts = deviceData.map(item => item.count);
            
            return {
                type: 'bar',
                data: {
                    labels: deviceLabels,
                    datasets: [{
                        label: 'Devices',
                        data: deviceCounts,
                        backgroundColor: '#2ecc71',
                        borderColor: '#27ae60',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true } }
                }
            };
        }

        // Select shortlink for detailed view
        async function selectShortlink(slug) {
            currentSlug = slug;
            
            // Update UI
            document.querySelectorAll('.shortlink-item').forEach(item => {
                item.classList.remove('active');
                if (item.querySelector('.shortlink-slug').textContent === '/' + slug) {
                    item.classList.add('active');
                }
            });
            
            document.getElementById('selectedSlug').textContent = slug;
            
            // Load detailed stats
            try {
                const response = await fetch(`/panel/shortlinks/${slug}/stats`);
                const data = await response.json();
                
                if (data.ok) {
                    allStats[slug] = data.summary;
                    updateSelectedStats(data.summary);
                }
            } catch (error) {
                console.error('Failed to load shortlink stats:', error);
            }
        }

        // Update selected shortlink stats
        function updateSelectedStats(stats) {
            const link = allLinks.find(l => l.slug === currentSlug);
            
            document.getElementById('selectedClicks').textContent = stats.clicks.toLocaleString();
            document.getElementById('selectedToday').textContent = Math.floor(stats.clicks * 0.1);
            document.getElementById('selectedUrl').textContent = link ? link.destination : '';
            
            // Update recent activity
            const tbody = document.getElementById('recentActivity');
            tbody.innerHTML = '';
            
            stats.last_200.slice(0, 8).forEach(event => {
                const row = tbody.insertRow();
                row.innerHTML = `
                    <td>${new Date(event.clicked_at).toLocaleTimeString()}</td>
                    <td>${event.country || '-'}</td>
                    <td>${event.device || '-'}</td>
                    <td><span class="badge ${event.is_bot ? 'badge-danger' : 'badge-success'}">${event.is_bot ? 'Bot' : 'Human'}</span></td>
                `;
            });
        }

        // Update real-time stats
        async function updateRealTimeStats() {
            if (currentSlug) {
                try {
                    const response = await fetch(`/panel/shortlinks/${currentSlug}/stats`);
                    const data = await response.json();
                    
                    if (data.ok) {
                        updateSelectedStats(data.summary);
                    }
                } catch (error) {
                    console.error('Failed to update real-time stats:', error);
                }
            }
            
            // Reload dashboard data periodically
            loadDashboardData();
        }

        // Utility functions
        function showSuccess(message) {
            successMessage.innerHTML = message;
            successMessage.style.display = 'block';
            errorMessage.style.display = 'none';
            setTimeout(() => {
                successMessage.style.display = 'none';
            }, 5000);
        }

        function showError(message) {
            errorMessage.textContent = message;
            errorMessage.style.display = 'block';
            successMessage.style.display = 'none';
            setTimeout(() => {
                errorMessage.style.display = 'none';
            }, 5000);
        }
    </script>
</body>
</html>
