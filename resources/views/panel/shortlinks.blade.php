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
    
    .links-table th:last-child,
    .links-table td:last-child {
        text-align: center;
        width: 100px;
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
    
    /* Success Animation */
    @keyframes checkmark {
        0% { 
            transform: scale(0) rotate(45deg);
            opacity: 0;
        }
        50% { 
            transform: scale(1.3) rotate(45deg);
            opacity: 1;
        }
        100% { 
            transform: scale(1) rotate(0deg);
            opacity: 1;
        }
    }
    
    @keyframes bounce-in {
        0% { 
            transform: scale(0.3);
            opacity: 0;
        }
        50% { 
            transform: scale(1.05);
        }
        70% { 
            transform: scale(0.9);
        }
        100% { 
            transform: scale(1);
            opacity: 1;
        }
    }
    
    @keyframes fade-in {
        0% { opacity: 0; transform: translateY(-10px); }
        100% { opacity: 1; transform: translateY(0); }
    }
    
    .success-animation {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #10b981;
        font-weight: 600;
        animation: bounce-in 0.6s ease-out;
    }
    
    .success-checkmark {
        font-size: 18px;
        animation: checkmark 0.8s ease-out;
        filter: drop-shadow(0 2px 4px rgba(16, 185, 129, 0.3));
    }
    
    .loading-create {
        display: flex;
        align-items: center;
        gap: 8px;
        color: var(--color-primary);
        font-weight: 600;
    }
    
    .loading-create .loading-spinner {
        width: 16px;
        height: 16px;
        border-width: 2px;
    }
    
    /* Reset buttons styling */
    .reset-buttons .btn {
        margin-left: 8px;
        font-size: 12px;
        padding: 6px 12px;
    }
    
    .btn-warning {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
        border: 1px solid #d97706;
    }
    
    .btn-warning:hover {
        background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
        transform: translateY(-1px);
    }
    
    .btn-danger-sm {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
        border: 1px solid #dc2626;
        font-size: 11px;
        padding: 4px 8px;
        border-radius: 4px;
    }
    
    .btn-danger-sm:hover {
        background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        transform: translateY(-1px);
    }
    
    .btn-delete-sm {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
        border: 1px solid #dc2626;
        font-size: 11px;
        padding: 4px 8px;
        border-radius: 4px;
        margin-left: 4px;
    }
    
    .btn-delete-sm:hover {
        background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        transform: translateY(-1px);
    }
    
    .action-buttons {
        display: flex;
        gap: 4px;
        justify-content: center;
    }
    
    /* Notification System */
    .notification {
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 16px 20px;
        border-radius: 8px;
        color: white;
        font-weight: 500;
        z-index: 1000;
        min-width: 300px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        animation: slideInRight 0.3s ease-out;
    }
    
    .notification.success {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    }
    
    .notification.error {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    }
    
    .notification.warning {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    }
    
    @keyframes slideInRight {
        0% { 
            opacity: 0; 
            transform: translateX(100%); 
        }
        100% { 
            opacity: 1; 
            transform: translateX(0); 
        }
    }
    
    @keyframes slideOutRight {
        0% { 
            opacity: 1; 
            transform: translateX(0); 
        }
        100% { 
            opacity: 0; 
            transform: translateX(100%); 
        }
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
                <a href="{{ route('panel.domains') }}" class="btn btn-info">
                    🌍 Domains
                </a>
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
                        <label class="form-label" for="domain_id">Domain</label>
                        <select id="domain_id" name="domain_id" class="form-input">
                            <option value="">Use default domain</option>
                            <!-- Domain options will be populated by JavaScript -->
                        </select>
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
                <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <h2 class="card-title" style="margin: 0;">Recent Shortlinks</h2>
                    <div class="reset-buttons">
                        <button id="resetAllVisitorsBtn" class="btn btn-warning btn-sm" title="Reset all visitor counts">
                            🔄 Reset All Visitors
                        </button>
                    </div>
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

// Notification system
function showNotification(message, type = 'success') {
    // Remove existing notifications
    const existingNotifications = document.querySelectorAll('.notification');
    existingNotifications.forEach(notification => notification.remove());
    
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    
    // Add to page
    document.body.appendChild(notification);
    
    // Auto-remove after 4 seconds
    setTimeout(() => {
        notification.style.opacity = '0';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, 4000);
}

// Initialize dashboard
document.addEventListener('DOMContentLoaded', function() {
    initializeTime();
    loadAnalytics();
    loadLinks();
    loadDomains();
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
                    <th>Actions</th>
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
                        <td>
                            <div class="action-buttons">
                                <button class="btn-danger-sm" onclick="resetVisitors('${link.slug}')" title="Reset visitor count">
                                    🔄
                                </button>
                                <button class="btn-delete-sm" onclick="deleteShortlink('${link.slug}')" title="Delete shortlink">
                                    🗑️
                                </button>
                            </div>
                        </td>
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
    const domain_id = formData.get('domain_id');
    
    // Get submit button
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalBtnText = submitBtn.innerHTML;
    
    // Show loading animation
    submitBtn.innerHTML = '<div class="loading-create"><div class="loading-spinner"></div>Creating...</div>';
    submitBtn.disabled = true;
    
    try {
        const response = await fetch('/api/create', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ destination, slug, domain_id })
        });
        
        const data = await response.json();
        
        if (data.ok) {
            // Show success animation
            submitBtn.innerHTML = '<div class="success-animation"><span class="success-checkmark">✅</span>Created Successfully!</div>';
            
            form.reset();
            loadLinks();
            loadAnalytics();
            
            // Reset button after success without alert
            setTimeout(() => {
                submitBtn.innerHTML = originalBtnText;
                submitBtn.disabled = false;
            }, 2000);
        } else {
            // Reset button on error
            submitBtn.innerHTML = originalBtnText;
            submitBtn.disabled = false;
            
            // Show error notification instead of alert
            showNotification(`Error: ${data.message || data.error || 'Failed to create shortlink'}`, 'error');
        }
    } catch (error) {
        console.error('Failed to create shortlink:', error);
        // Reset button on error
        submitBtn.innerHTML = originalBtnText;
        submitBtn.disabled = false;
        showNotification('Failed to create shortlink. Please try again.', 'error');
    }
}

async function loadDomains() {
    try {
        const response = await fetch('/api/domains');
        const data = await response.json();
        
        if (data.ok) {
            const domainSelect = document.getElementById('domain_id');
            
            // Clear existing options except the default one
            domainSelect.innerHTML = '<option value="">Use default domain</option>';
            
            data.data.forEach(domain => {
                const option = document.createElement('option');
                option.value = domain.id;
                option.textContent = domain.domain + (domain.is_default ? ' (Default)' : '');
                
                if (domain.is_default) {
                    option.selected = true;
                }
                
                domainSelect.appendChild(option);
            });
        }
        
    } catch (error) {
        console.error('Failed to load domains:', error);
    }
}

function refreshData() {
    loadAnalytics();
    loadLinks();
}

// Reset visitor functions
async function resetVisitors(slug) {
    if (!confirm(`Are you sure you want to reset visitor count for "${slug}"?\n\nThis action cannot be undone.`)) {
        return;
    }
    
    try {
        const response = await fetch(`/api/reset-visitors/${slug}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (data.ok) {
            showNotification(`✅ ${data.message}`, 'success');
            loadLinks();
            loadAnalytics();
        } else {
            showNotification(`❌ Error: ${data.message}`, 'error');
        }
    } catch (error) {
        console.error('Failed to reset visitors:', error);
        showNotification('❌ Failed to reset visitor count. Please try again.', 'error');
    }
}

async function resetAllVisitors() {
    if (!confirm('Are you sure you want to reset ALL visitor counts?\n\nThis will reset the visitor count for ALL shortlinks.\nThis action cannot be undone.')) {
        return;
    }
    
    const btn = document.getElementById('resetAllVisitorsBtn');
    const originalText = btn.innerHTML;
    
    // Show loading
    btn.innerHTML = '🔄 Resetting...';
    btn.disabled = true;
    
    try {
        const response = await fetch('/api/reset-all-visitors', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (data.ok) {
            btn.innerHTML = '✅ Reset Complete!';
            showNotification(`✅ ${data.message}`, 'success');
            loadLinks();
            loadAnalytics();
            
            // Reset button after success
            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.disabled = false;
            }, 2000);
        } else {
            btn.innerHTML = originalText;
            btn.disabled = false;
            showNotification(`❌ Error: ${data.message}`, 'error');
        }
    } catch (error) {
        console.error('Failed to reset all visitors:', error);
        btn.innerHTML = originalText;
        btn.disabled = false;
        showNotification('❌ Failed to reset visitor counts. Please try again.', 'error');
    }
}

async function deleteShortlink(slug) {
    if (!confirm(`Are you sure you want to delete the shortlink "${slug}"?\n\nThis action cannot be undone.`)) {
        return;
    }
    
    try {
        const response = await fetch(`/api/delete/${slug}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (data.ok) {
            showNotification(`✅ Shortlink "${slug}" deleted successfully!`, 'success');
            loadLinks();
            loadAnalytics();
        } else {
            showNotification(`❌ Error: ${data.message}`, 'error');
        }
    } catch (error) {
        console.error('Failed to delete shortlink:', error);
        showNotification('❌ Failed to delete shortlink. Please try again.', 'error');
    }
}

// Add event listener for reset all button
document.addEventListener('DOMContentLoaded', function() {
    // Add existing event listeners...
    
    // Reset all visitors button
    document.getElementById('resetAllVisitorsBtn').addEventListener('click', resetAllVisitors);
});
</script>
@endsection
