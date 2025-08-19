@extends('layouts.envelope')

@section('title', 'Professional Shortlink Management')

@push('styles')
<style>
    /* Dashboard-specific styles - Dark Theme */
    .envelope-container {
        padding: 20px;
        min-height: 100vh;
        /* Remove page-level blue gradient to use global terminal grid background */
        background: none;
    }
    
    .dashboard-wrapper {
        max-width: 1400px;
        margin: 0 auto;
    }
    
    .dashboard-header {
        background: var(--color-surface);
        border-radius: 12px;
        padding: 24px 32px;
        margin-bottom: 24px;
        box-shadow: var(--shadow-lg);
        border: 1px solid var(--color-border);
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
        font-family: var(--font-mono);
        font-size: 32px;
        font-weight: 600;
        color: var(--color-text-primary);
        margin-bottom: 4px;
        letter-spacing: -0.025em;
    }
    
    .header-subtitle {
        color: var(--color-text-muted);
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
        color: white;
    }
    
    .btn-primary:hover {
        background: var(--color-primary-dark);
        transform: translateY(-1px);
    }
    
    .btn-danger {
        background: var(--color-danger);
        color: white;
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
        background: var(--color-surface);
        border-radius: 12px;
        padding: 24px;
        box-shadow: var(--shadow-md);
        border: 1px solid var(--color-border);
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
        color: var(--color-text-primary);
        margin-bottom: 4px;
        font-family: var(--font-primary);
    }
    
    .stat-info p {
        color: var(--color-text-muted);
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
        color: white;
    }
    
    .main-grid {
        display: grid;
        grid-template-columns: 1fr 350px;
        gap: 24px;
        margin-bottom: 24px;
    }
    
    .content-card {
        background: var(--color-surface);
        border-radius: 12px;
        padding: 24px;
        box-shadow: var(--shadow-md);
        border: 1px solid var(--color-border);
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
        border-bottom: 1px solid var(--color-border);
    }
    
    .card-title {
        font-family: var(--font-mono);
        font-size: 20px;
        font-weight: 600;
        color: var(--color-text-primary);
    }
    
    .analytics-controls {
        display: flex;
        gap: 8px;
    }
    
    .period-btn {
        padding: 6px 12px;
        border: 1px solid var(--color-border);
        background: var(--color-bg-secondary);
        color: var(--color-text-muted);
        border-radius: 6px;
        font-size: 12px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    
    .period-btn.active {
        background: var(--color-primary);
        border-color: var(--color-primary);
        color: white;
    }
    
    .period-btn:hover:not(.active) {
        background: var(--color-surface-hover);
        border-color: var(--color-border-light);
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
        background: var(--color-bg-tertiary);
        border-radius: 8px;
        margin-top: 16px;
    }
    
    .comparison-item {
        text-align: center;
    }
    
    .comparison-value {
        font-size: 18px;
        font-weight: 600;
        color: var(--color-text-primary);
    }
    
    .comparison-label {
        font-size: 12px;
        color: var(--color-text-muted);
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
        color: var(--color-text-secondary);
        margin-bottom: 6px;
        font-size: 13px;
    }
    
    .form-input {
        width: 100%;
        padding: 12px 14px;
        border: 1px solid var(--color-border);
        border-radius: 8px;
        font-size: 14px;
        background: var(--color-bg-secondary);
        color: var(--color-text-primary);
        transition: all 0.2s ease;
        font-family: var(--font-primary);
    }
    
    .form-input:focus {
        outline: none;
        border-color: var(--color-primary);
        box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.2);
        background: var(--color-bg-tertiary);
    }
    
    .form-input::placeholder {
        color: var(--color-text-muted);
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
        border-bottom: 1px solid var(--color-border);
    }
    
    .links-table th {
        background: var(--color-bg-tertiary);
        font-weight: 600;
        color: var(--color-text-primary);
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .links-table td {
        font-size: 13px;
        color: var(--color-text-primary);
    }
    
    .links-table tr:hover {
        background: var(--color-surface-hover);
    }
    
    .links-table th:last-child,
    .links-table td:last-child {
        text-align: center;
        width: 100px;
    }
    
    .link-slug {
        font-family: 'SF Mono', 'Monaco', 'Consolas', 'Roboto Mono', monospace;
        background: var(--color-bg-tertiary);
        padding: 4px 6px;
        border-radius: 4px;
        font-size: 11px;
        color: var(--color-primary);
        font-weight: 500;
        text-decoration: none;
        display: inline-block;
        cursor: pointer;
        transition: background 0.2s ease, color 0.2s ease, box-shadow 0.2s ease;
    }

    .link-slug:hover {
        background: var(--color-surface-hover);
        color: var(--color-accent);
        box-shadow: 0 0 0 1px var(--color-border-light) inset;
    }
    
    .link-destination {
        max-width: 200px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        color: var(--color-text-muted);
    }
    
    /* Loading state */
    .loading {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 24px;
        color: var(--color-text-muted);
    }

    /* Link Type Toggle */
    .link-type-toggle {
        display: flex;
        gap: 12px;
        margin-bottom: 16px;
    }

    .toggle-option {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px 12px;
        border: 1px solid var(--color-border);
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.2s ease;
        background: var(--color-bg-secondary);
        color: var(--color-text-secondary);
        font-family: var(--font-mono);
        font-size: 13px;
    }

    .toggle-option:hover {
        border-color: var(--color-primary);
        background: var(--color-bg-tertiary);
    }

    .toggle-option input[type="radio"] {
        display: none;
    }

    .toggle-option input[type="radio"]:checked + span {
        color: var(--color-primary);
        font-weight: 600;
    }

    .toggle-option:has(input[type="radio"]:checked) {
        border-color: var(--color-primary);
        background: var(--color-bg-tertiary);
        box-shadow: 0 0 0 2px rgba(34, 197, 94, 0.1);
    }

    /* Destination Management */
    .destination-item {
        margin-bottom: 12px;
        padding: 12px;
        border: 1px solid var(--color-border);
        border-radius: 8px;
        background: var(--color-bg-tertiary);
    }

    .destination-inputs {
        display: grid;
        grid-template-columns: 2fr 1fr 80px 40px;
        gap: 8px;
        align-items: center;
    }

    .destination-url {
        grid-column: 1;
    }

    .destination-name {
        grid-column: 2;
    }

    .destination-weight {
        grid-column: 3;
        text-align: center;
    }

    .btn-remove-destination {
        grid-column: 4;
        background: var(--color-danger);
        color: white;
        border: none;
        border-radius: 6px;
        padding: 6px;
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
    }

    .btn-remove-destination:hover {
        background: #b91c1c;
        transform: scale(1.05);
    }

    .btn-sm {
        padding: 6px 12px;
        font-size: 12px;
        min-height: 32px;
    }

    /* Rotator Badge */
    .rotator-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 2px 6px;
        background: var(--color-accent);
        color: var(--color-text-inverse);
        border-radius: 4px;
        font-size: 10px;
        font-weight: 600;
        margin-left: 6px;
    }

    .rotator-info {
        font-size: 11px;
        color: var(--color-text-muted);
        margin-top: 2px;
    }

    /* Action Buttons */
    .action-buttons {
        display: flex;
        gap: 4px;
        align-items: center;
    }

    .btn-manage-rotator {
        background: var(--color-accent);
        color: var(--color-text-inverse);
        border: none;
        border-radius: 4px;
        padding: 4px 6px;
        cursor: pointer;
        font-size: 10px;
        transition: all 0.2s ease;
    }

    .btn-manage-rotator:hover {
        background: #65a30d;
        transform: scale(1.05);
    }

    .btn-edit {
        background: var(--color-primary);
        color: white;
        border: none;
        border-radius: 4px;
        padding: 4px 6px;
        font-size: 12px;
        cursor: pointer;
    }

    .btn-edit:hover { transform: scale(1.05); }

    @media (max-width: 768px) {
        .destination-inputs {
            grid-template-columns: 1fr;
            gap: 8px;
        }
        
        .destination-url,
        .destination-name,
        .destination-weight,
        .btn-remove-destination {
            grid-column: 1;
        }
        
        .link-type-toggle {
            flex-direction: column;
        }
    }

    /* Modal Styles */
    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.7);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1000;
        backdrop-filter: blur(4px);
    }

    .modal-content {
        background: var(--color-surface);
        border-radius: 12px;
        border: 1px solid var(--color-border);
        box-shadow: var(--shadow-xl);
        width: 90%;
        max-width: 600px;
        max-height: 80vh;
        overflow-y: auto;
        position: relative;
    }

    .modal-content::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, var(--color-primary) 0%, var(--color-accent) 100%);
    }

    .modal-header {
        padding: 20px 24px 16px;
        border-bottom: 1px solid var(--color-border);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .modal-header h3 {
        margin: 0;
        color: var(--color-text-primary);
        font-family: var(--font-mono);
        font-size: 18px;
    }

    .modal-close {
        background: none;
        border: none;
        color: var(--color-text-muted);
        font-size: 24px;
        cursor: pointer;
        padding: 0;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        transition: all 0.2s ease;
    }

    .modal-close:hover {
        background: var(--color-bg-tertiary);
        color: var(--color-text-primary);
    }

    .modal-content form {
        padding: 20px 24px;
    }

    .modal-actions {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        padding-top: 16px;
        border-top: 1px solid var(--color-border);
        margin-top: 20px;
    }

    /* Notification Styles */
    .notification {
        position: fixed;
        top: 20px;
        right: 20px;
        background: var(--color-surface);
        border: 1px solid var(--color-border);
        border-radius: 8px;
        box-shadow: var(--shadow-lg);
        padding: 12px 16px;
        z-index: 1001;
        min-width: 300px;
        animation: slideInRight 0.3s ease-out;
    }

    .notification-success {
        border-left: 4px solid var(--color-success);
    }

    .notification-error {
        border-left: 4px solid var(--color-danger);
    }

    .notification-info {
        border-left: 4px solid var(--color-primary);
    }

    .notification-content {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .notification-icon {
        font-size: 16px;
    }

    .notification-message {
        color: var(--color-text-primary);
        font-size: 14px;
        font-family: var(--font-mono);
    }

    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    /* Loading Spinner */
    .loading-spinner {
        display: inline-block;
        width: 16px;
        height: 16px;
        border: 2px solid var(--color-border);
        border-radius: 50%;
        border-top: 2px solid var(--color-primary);
        animation: spin 1s linear infinite;
        margin-right: 8px;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .loading-create {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .success-animation {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .success-checkmark {
        animation: bounceIn 0.5s ease-out;
    }

    @keyframes bounceIn {
        0% { transform: scale(0); }
        50% { transform: scale(1.2); }
        100% { transform: scale(1); }
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
                    <!-- Link Type Toggle -->
                    <div class="form-group">
                        <div class="link-type-toggle">
                            <label class="toggle-option">
                                <input type="radio" name="link_type" value="single" checked onchange="toggleLinkType()">
                                <span>📎 Single Link</span>
                            </label>
                            <label class="toggle-option">
                                <input type="radio" name="link_type" value="rotator" onchange="toggleLinkType()">
                                <span>🔄 Link Rotator</span>
                            </label>
                        </div>
                    </div>

                    <!-- Single Destination -->
                    <div id="single-destination" class="destination-section">
                        <div class="form-group">
                            <label class="form-label" for="destination">Destination URL</label>
                            <input type="url" 
                                   id="destination" 
                                   name="destination" 
                                   class="form-input" 
                                   placeholder="https://example.com/your-long-url">
                        </div>
                    </div>

                    <!-- Rotator Destinations -->
                    <div id="rotator-destinations" class="destination-section" style="display: none;">
                        <div class="form-group">
                            <label class="form-label">Rotation Type</label>
                            <select name="rotation_type" class="form-input">
                                <option value="random">Random - Each click goes to random destination</option>
                                <option value="sequential">Sequential - Round-robin through destinations</option>
                                <option value="weighted">Weighted - Based on weight distribution</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Destinations</label>
                            <div id="destinations-container">
                                <div class="destination-item">
                                    <div class="destination-inputs">
                                        <input type="url" name="destinations[0][url]" class="form-input destination-url" placeholder="https://example.com/page-1" required>
                                        <input type="text" name="destinations[0][name]" class="form-input destination-name" placeholder="Name (optional)">
                                        <input type="number" name="destinations[0][weight]" class="form-input destination-weight" placeholder="Weight" value="1" min="1" max="100">
                                        <button type="button" class="btn-remove-destination" onclick="removeDestination(this)" title="Remove destination">🗑️</button>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-secondary btn-sm" onclick="addDestination()">+ Add Destination</button>
                        </div>
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
                ${links.map(link => {
                    const rotatorBadge = link.is_rotator ? 
                        `<span class="rotator-badge">🔄 ${link.destinations?.length || 0} URLs</span>` : '';
                    const destination = link.is_rotator ? 
                        `Multiple destinations (${link.rotation_type})` : link.destination;
                    const rotatorBtn = link.is_rotator ? 
                        `<button class="btn-manage-rotator" onclick="manageRotator('${link.slug}')" title="Manage rotator">⚙️</button>` : '';
                    const editBtn = `<button class="btn-edit" onclick="editDestinations('${link.slug}')" title="Edit destinations">✏️</button>`;
                    
                    return `
                    <tr>
                        <td>
                            <a href="${link.full_url || ('/' + link.slug)}" class="link-slug" target="_blank" rel="noopener" title="${link.full_url || ('/' + link.slug)}">${link.slug}</a>
                            ${rotatorBadge}
                        </td>
                        <td>
                            <div class="link-destination" title="${link.destination}">${destination}</div>
                            ${link.is_rotator ? `<div class="rotator-info">${link.destinations?.length || 0} destinations • ${link.rotation_type} rotation</div>` : ''}
                        </td>
                        <td><span class="clicks-badge">${link.clicks}</span></td>
                        <td><span class="status-${link.active ? 'active' : 'inactive'}">${link.active ? 'Active' : 'Inactive'}</span></td>
                        <td>${new Date(link.created_at).toLocaleDateString('id-ID')}</td>
                        <td>
                            <div class="action-buttons">
                                ${editBtn}
                                ${rotatorBtn}
                                <button class="btn-danger-sm" onclick="resetVisitors('${link.slug}')" title="Reset visitor count">
                                    🔄
                                </button>
                                <button class="btn-delete-sm" onclick="deleteShortlink('${link.slug}')" title="Delete shortlink">
                                    🗑️
                                </button>
                            </div>
                        </td>
                    </tr>
                    `;
                }).join('')}
            </tbody>
        </table>
    `;
    
    container.innerHTML = table;
}

async function createShortlink() {
    const form = document.getElementById('create-form');
    const formData = new FormData(form);
    
    // Get submit button
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalBtnText = submitBtn.innerHTML;
    
    // Show loading animation
    submitBtn.innerHTML = '<div class="loading-create"><div class="loading-spinner"></div>Creating...</div>';
    submitBtn.disabled = true;
    
    try {
        // Prepare data based on link type
        const linkType = formData.get('link_type');
        const isRotator = linkType === 'rotator';
        
        const requestData = {
            slug: formData.get('slug'),
            is_rotator: isRotator
        };
        
        if (isRotator) {
            requestData.rotation_type = formData.get('rotation_type');
            requestData.destinations = [];
            
            // Collect destinations
            const destinationInputs = document.querySelectorAll('.destination-item');
            destinationInputs.forEach((item, index) => {
                const url = formData.get(`destinations[${index}][url]`);
                const name = formData.get(`destinations[${index}][name]`);
                const weight = formData.get(`destinations[${index}][weight]`);
                
                if (url) {
                    requestData.destinations.push({
                        url: url,
                        name: name || '',
                        weight: parseInt(weight) || 1,
                        active: true
                    });
                }
            });
            
            if (requestData.destinations.length === 0) {
                throw new Error('At least one destination is required for rotator');
            }
        } else {
            requestData.destination = formData.get('destination');
        }
        
        const response = await fetch('/api/create', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(requestData)
        });
        
        const data = await response.json();
        
        if (data.ok) {
            // Show success animation
            submitBtn.innerHTML = '<div class="success-animation"><span class="success-checkmark">✅</span>Created Successfully!</div>';
            
            form.reset();
            toggleLinkType(); // Reset to default state
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

function refreshData() {
    loadAnalytics();
    loadLinks();
}

// Link type toggle functionality
function toggleLinkType() {
    const linkType = document.querySelector('input[name="link_type"]:checked').value;
    const singleSection = document.getElementById('single-destination');
    const rotatorSection = document.getElementById('rotator-destinations');
    
    if (linkType === 'rotator') {
        singleSection.style.display = 'none';
        rotatorSection.style.display = 'block';
        document.getElementById('destination').removeAttribute('required');
        // Ensure at least one destination exists
        if (document.querySelectorAll('.destination-item').length === 0) {
            addDestination();
        }
    } else {
        singleSection.style.display = 'block';
        rotatorSection.style.display = 'none';
        document.getElementById('destination').setAttribute('required', '');
    }
}

// Add destination to rotator
function addDestination() {
    const container = document.getElementById('destinations-container');
    const index = container.children.length;
    
    const destinationItem = document.createElement('div');
    destinationItem.className = 'destination-item';
    destinationItem.innerHTML = `
        <div class="destination-inputs">
            <input type="url" name="destinations[${index}][url]" class="form-input destination-url" placeholder="https://example.com/page-${index + 1}" required>
            <input type="text" name="destinations[${index}][name]" class="form-input destination-name" placeholder="Name (optional)">
            <input type="number" name="destinations[${index}][weight]" class="form-input destination-weight" placeholder="Weight" value="1" min="1" max="100">
            <button type="button" class="btn-remove-destination" onclick="removeDestination(this)" title="Remove destination">🗑️</button>
        </div>
    `;
    
    container.appendChild(destinationItem);
}

// Remove destination from rotator
function removeDestination(button) {
    const container = document.getElementById('destinations-container');
    if (container.children.length > 1) {
        button.closest('.destination-item').remove();
        // Reindex remaining destinations
        Array.from(container.children).forEach((item, index) => {
            const inputs = item.querySelectorAll('input');
            inputs[0].name = `destinations[${index}][url]`;
            inputs[1].name = `destinations[${index}][name]`;
            inputs[2].name = `destinations[${index}][weight]`;
        });
    } else {
        showNotification('At least one destination is required', 'error');
    }
}

// Manage rotator modal/popup
async function manageRotator(slug) {
    try {
        const response = await fetch(`/api/rotator/${slug}`);
        const data = await response.json();
        
        if (data.ok) {
            showEditDestinationsModal(data.data);
        } else {
            showNotification('Failed to load rotator data', 'error');
        }
    } catch (error) {
        console.error('Failed to load rotator:', error);
        showNotification('Failed to load rotator data', 'error');
    }
}

// Show rotator management modal
function showEditDestinationsModal(rotatorData) {
    // Create modal HTML
    const modalHTML = `
        <div id="rotator-modal" class="modal-overlay" onclick="closeRotatorModal(event)">
            <div class="modal-content" onclick="event.stopPropagation()">
                <div class="modal-header">
                    <h3>Edit Destinations: ${rotatorData.slug}</h3>
                    <button class="modal-close" onclick="closeRotatorModal()">&times;</button>
                </div>
                <form id="destinations-form">
                    <div class="form-group">
                        <label class="form-label">Type</label>
                        <div class="link-type-toggle">
                            <label class="toggle-option"><input type="radio" name="is_rotator" value="0" ${!rotatorData.is_rotator ? 'checked' : ''}><span>Single</span></label>
                            <label class="toggle-option"><input type="radio" name="is_rotator" value="1" ${rotatorData.is_rotator ? 'checked' : ''}><span>Rotator</span></label>
                        </div>
                    </div>

                    <div id="single-destination-group" class="form-group" style="display: ${rotatorData.is_rotator ? 'none' : 'block'};">
                        <label class="form-label">Destination URL</label>
                        <input type="url" name="destination" class="form-input" value="${rotatorData.destination || ''}" placeholder="https://example.com" ${rotatorData.is_rotator ? '' : 'required'}>
                    </div>

                    <div id="rotator-destinations-group" class="form-group" style="display: ${rotatorData.is_rotator ? 'block' : 'none'};">
                        <label class="form-label">Rotation Type</label>
                        <select name="rotation_type" class="form-input">
                            <option value="random" ${rotatorData.rotation_type === 'random' ? 'selected' : ''}>Random</option>
                            <option value="sequential" ${rotatorData.rotation_type === 'sequential' ? 'selected' : ''}>Sequential</option>
                            <option value="weighted" ${rotatorData.rotation_type === 'weighted' ? 'selected' : ''}>Weighted</option>
                        </select>
                        <label class="form-label" style="margin-top:10px">Destinations</label>
                        <div id="modal-destinations-container">
                            ${ (rotatorData.destinations || []).map((dest, index) => `
                                <div class="destination-item">
                                    <div class="destination-inputs">
                                        <input type="url" name="destinations[${index}][url]" class="form-input destination-url" value="${dest.url}" required>
                                        <input type="text" name="destinations[${index}][name]" class="form-input destination-name" value="${dest.name || ''}" placeholder="Name (optional)">
                                        <input type="number" name="destinations[${index}][weight]" class="form-input destination-weight" value="${dest.weight || 1}" min="1" max="100">
                                        <button type="button" class="btn-remove-destination" onclick="removeModalDestination(this)">🗑️</button>
                                    </div>
                                </div>
                            `).join('') }
                        </div>
                        <button type="button" class="btn btn-secondary btn-sm" onclick="addModalDestination()">+ Add Destination</button>
                    </div>

                    <div class="modal-actions">
                        <button type="button" class="btn btn-secondary" onclick="closeRotatorModal()">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    `;
    
    // Add modal to page
    document.body.insertAdjacentHTML('beforeend', modalHTML);
    
    // Toggle visibility between single and rotator UI
    const formEl = document.getElementById('destinations-form');
    formEl.querySelectorAll('input[name="is_rotator"]').forEach(r => r.addEventListener('change', function(){
        const isRot = this.value === '1';
        document.getElementById('single-destination-group').style.display = isRot ? 'none' : 'block';
        document.getElementById('rotator-destinations-group').style.display = isRot ? 'block' : 'none';
    }));
    
    // Setup form submission
    document.getElementById('destinations-form').addEventListener('submit', function(e) {
        e.preventDefault();
        submitDestinations(rotatorData.slug);
    });
}

// Close rotator modal
function closeRotatorModal(event) {
    if (!event || event.target.classList.contains('modal-overlay') || event.target.classList.contains('modal-close')) {
        const modal = document.getElementById('rotator-modal');
        if (modal) {
            modal.remove();
        }
    }
}

// Add destination in modal
function addModalDestination() {
    const container = document.getElementById('modal-destinations-container');
    const index = container.children.length;
    
    const destinationItem = document.createElement('div');
    destinationItem.className = 'destination-item';
    destinationItem.innerHTML = `
        <div class="destination-inputs">
            <input type="url" name="destinations[${index}][url]" class="form-input destination-url" placeholder="https://example.com/page-${index + 1}" required>
            <input type="text" name="destinations[${index}][name]" class="form-input destination-name" placeholder="Name (optional)">
            <input type="number" name="destinations[${index}][weight]" class="form-input destination-weight" value="1" min="1" max="100">
            <button type="button" class="btn-remove-destination" onclick="removeModalDestination(this)">🗑️</button>
        </div>
    `;
    
    container.appendChild(destinationItem);
}

// Remove destination in modal
function removeModalDestination(button) {
    const container = document.getElementById('modal-destinations-container');
    if (container.children.length > 1) {
        button.closest('.destination-item').remove();
        // Reindex
        Array.from(container.children).forEach((item, index) => {
            const inputs = item.querySelectorAll('input');
            inputs[0].name = `destinations[${index}][url]`;
            inputs[1].name = `destinations[${index}][name]`;
            inputs[2].name = `destinations[${index}][weight]`;
        });
    } else {
        showNotification('At least one destination is required', 'error');
    }
}

// Update rotator
async function updateRotator(slug) {
    const form = document.getElementById('rotator-form');
    const formData = new FormData(form);
    
    try {
        const requestData = {
            is_rotator: true,
            rotation_type: formData.get('rotation_type'),
            destinations: []
        };
        
        // Collect destinations
        const destinationInputs = document.querySelectorAll('#modal-destinations-container .destination-item');
        destinationInputs.forEach((item, index) => {
            const url = formData.get(`destinations[${index}][url]`);
            const name = formData.get(`destinations[${index}][name]`);
            const weight = formData.get(`destinations[${index}][weight]`);
            
            if (url) {
                requestData.destinations.push({
                    url: url,
                    name: name || '',
                    weight: parseInt(weight) || 1,
                    active: true
                });
            }
        });
        
        const response = await fetch(`/api/rotator/${slug}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(requestData)
        });
        
        const data = await response.json();
        
        if (data.ok) {
            showNotification('Rotator updated successfully', 'success');
            closeRotatorModal();
            loadLinks();
        } else {
            showNotification(`Error: ${data.message}`, 'error');
        }
    } catch (error) {
        console.error('Failed to update rotator:', error);
        showNotification('Failed to update rotator', 'error');
    }
}

// Edit destinations (open unified modal)
async function editDestinations(slug) {
    try {
        const response = await fetch(`/api/rotator/${slug}`);
        const data = await response.json();
        if (data.ok) {
            showEditDestinationsModal(data.data);
        } else {
            showNotification('Failed to load link data', 'error');
        }
    } catch (err) {
        console.error('Failed to load link data:', err);
        showNotification('Failed to load link data', 'error');
    }
}

async function submitDestinations(slug) {
    const form = document.getElementById('destinations-form');
    const formData = new FormData(form);

    try {
        const isRotator = formData.get('is_rotator') === '1';
        const requestData = { is_rotator: isRotator };

        if (isRotator) {
            requestData.rotation_type = formData.get('rotation_type');
            requestData.destinations = [];
            const destinationItems = document.querySelectorAll('#modal-destinations-container .destination-item');
            destinationItems.forEach((item, index) => {
                const url = formData.get(`destinations[${index}][url]`);
                const name = formData.get(`destinations[${index}][name]`);
                const weight = formData.get(`destinations[${index}][weight]`);
                if (url) requestData.destinations.push({ url: url, name: name || '', weight: parseInt(weight) || 1, active: true });
            });
        } else {
            requestData.destination = formData.get('destination');
        }

        const response = await fetch(`/panel/shortlinks/${slug}/destinations`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(requestData)
        });

        const data = await response.json();
        if (data.ok) {
            showNotification('Destinations updated', 'success');
            closeRotatorModal();
            loadLinks();
        } else {
            showNotification('Error: ' + (data.message || 'Failed to update'), 'error');
        }
    } catch (err) {
        console.error('Failed to submit destinations:', err);
        showNotification('Failed to update destinations', 'error');
    }
}
</script>
@endsection
