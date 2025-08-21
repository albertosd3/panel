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
                <a href="{{ route('panel.stopbot') }}" class="btn btn-secondary">
                    🛡️ Stopbot Config
                </a>
                <a href="{{ route('panel.ips') }}" class="btn btn-secondary">
                    📋 IP List
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
        <div class="stat-card">
            <div class="stat-content">
                <div class="stat-info">
                    <h3 id="stopbot-status">
                        @if(\App\Models\PanelSetting::get('stopbot_enabled', false))
                            <span style="color: var(--color-success);">ON</span>
                        @else
                            <span style="color: var(--color-secondary);">OFF</span>
                        @endif
                    </h3>
                    <p>Stopbot Status</p>
                </div>
                <div class="stat-icon">🛡️</div>
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

            <!-- Stopbot Quick Settings -->
            <div class="paper content-card" style="margin-top: 24px;">
                <div class="card-header">
                    <h2 class="card-title">🛡️ Bot Protection</h2>
                </div>
                <div style="padding: 16px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                        <span style="font-size: 13px; color: var(--color-text-secondary);">Stopbot Status:</span>
                        <span class="badge {{ \App\Models\PanelSetting::get('stopbot_enabled', false) ? 'bg-success' : 'bg-secondary' }}">
                            {{ \App\Models\PanelSetting::get('stopbot_enabled', false) ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                    @if(\App\Models\PanelSetting::get('stopbot_api_key', ''))
                        <div style="margin-bottom: 12px; font-size: 12px; color: var(--color-text-muted);">
                            API Key: {{ substr(\App\Models\PanelSetting::get('stopbot_api_key', ''), 0, 8) }}***
                        </div>
                    @endif
                    <div style="display: flex; gap: 8px;">
                        <a href="{{ route('panel.stopbot') }}" class="btn btn-outline btn-sm" style="flex: 1; font-size: 11px;">
                            ⚙️ Configure
                        </a>
                        <button onclick="toggleStopbot()" class="btn btn-primary btn-sm" style="flex: 1; font-size: 11px;" id="toggleStopbotBtn">
                            {{ \App\Models\PanelSetting::get('stopbot_enabled', false) ? '⏹️ Disable' : '▶️ Enable' }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Popular Links -->
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

    // Reset all visitors button
    const resetAllBtn = document.getElementById('resetAllVisitorsBtn');
    if (resetAllBtn) {
        resetAllBtn.addEventListener('click', async function() {
            if (!confirm('Are you sure you want to reset visitor counts for ALL shortlinks? This cannot be undone.')) return;
            try {
                const resp = await fetch('/api/reset-all-visitors', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                const json = await resp.json();
                if (json?.ok) {
                    showNotification(json.message || 'All visitor counts reset', 'success');
                    loadLinks();
                    loadAnalytics();
                } else {
                    showNotification('Error: ' + (json?.message || 'Failed to reset'), 'error');
                }
            } catch (e) {
                showNotification('Error: ' + e.message, 'error');
            }
        });
    }
}

// Toggle Stopbot status function
async function toggleStopbot() {
    const btn = document.getElementById('toggleStopbotBtn');
    const originalText = btn.textContent;
    btn.disabled = true;
    btn.textContent = '⏳ Processing...';

    try {
        // Get current status first
        const currentStatus = {{ \App\Models\PanelSetting::get('stopbot_enabled', false) ? 'true' : 'false' }};
        const newStatus = !currentStatus;

        const response = await fetch('/panel/api/stopbot/config', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                enabled: newStatus,
                api_key: '{{ \App\Models\PanelSetting::get('stopbot_api_key', '') }}',
                redirect_url: '{{ \App\Models\PanelSetting::get('stopbot_redirect_url', 'https://www.google.com') }}',
                log_enabled: {{ \App\Models\PanelSetting::get('stopbot_log_enabled', true) ? 'true' : 'false' }},
                timeout: {{ \App\Models\PanelSetting::get('stopbot_timeout', 5) }}
            })
        });

        const result = await response.json();
        
        if (result.ok) {
            showNotification(`Stopbot ${newStatus ? 'enabled' : 'disabled'} successfully!`, 'success');
            // Reload page to update status displays
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification('Error: ' + (result.message || 'Failed to toggle Stopbot'), 'error');
            btn.textContent = originalText;
            btn.disabled = false;
        }
    } catch (error) {
        showNotification('Error: ' + error.message, 'error');
        btn.textContent = originalText;
        btn.disabled = false;
    }
}
</script>
@endsection
