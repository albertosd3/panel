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

    /* Form Styles */
    .form-group {
        margin-bottom: 20px;
    }

    .form-label {
        display: block;
        margin-bottom: 8px;
        color: var(--color-text-primary);
        font-weight: 500;
        font-size: 14px;
    }

    .form-input {
        width: 100%;
        padding: 12px 16px;
        border: 1px solid var(--color-border);
        border-radius: 8px;
        background: var(--color-surface);
        color: var(--color-text-primary);
        font-family: var(--font-primary);
        font-size: 14px;
        transition: all 0.2s ease;
    }

    .form-input:focus {
        outline: none;
        border-color: var(--color-primary);
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .form-input::placeholder {
        color: var(--color-text-muted);
    }

    /* Link Type Toggle */
    .link-type-toggle {
        display: flex;
        gap: 12px;
        padding: 8px;
        background: var(--color-background);
        border: 1px solid var(--color-border);
        border-radius: 8px;
    }

    .toggle-option {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px 12px;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.2s ease;
        font-size: 14px;
        color: var(--color-text-primary);
    }

    .toggle-option:hover {
        background: rgba(59, 130, 246, 0.1);
    }

    .toggle-option input[type="radio"] {
        margin: 0;
    }

    .toggle-option input[type="radio"]:checked + span {
        color: var(--color-primary);
        font-weight: 600;
    }

    /* Destination Management */
    .destination-section {
        transition: all 0.3s ease;
    }

    .destination-item {
        margin-bottom: 12px;
        padding: 16px;
        background: var(--color-background);
        border: 1px solid var(--color-border);
        border-radius: 8px;
    }

    .destination-inputs {
        display: grid;
        grid-template-columns: 2fr 1fr 80px auto;
        gap: 12px;
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
    }

    .btn-remove-destination {
        grid-column: 4;
        background: var(--color-danger);
        color: white;
        border: none;
        border-radius: 6px;
        padding: 8px 12px;
        cursor: pointer;
        font-size: 12px;
        transition: all 0.2s ease;
    }

    .btn-remove-destination:hover {
        background: #dc2626;
        transform: scale(1.05);
    }

    /* Link Display */
    .link-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 16px;
        border: 1px solid var(--color-border);
        border-radius: 8px;
        margin-bottom: 12px;
        background: var(--color-surface);
        transition: all 0.2s ease;
    }

    .link-item:hover {
        background: var(--color-background);
        transform: translateX(4px);
    }

    .link-info {
        flex: 1;
    }

    .link-url {
        font-family: var(--font-mono);
        font-weight: 600;
        color: var(--color-primary);
        font-size: 16px;
        margin-bottom: 4px;
    }

    .link-destination {
        color: var(--color-text-muted);
        font-size: 14px;
        margin-bottom: 4px;
        word-break: break-all;
    }

    .link-meta {
        color: var(--color-text-muted);
        font-size: 12px;
    }

    .link-actions {
        display: flex;
        gap: 8px;
    }

    .badge {
        display: inline-block;
        padding: 2px 6px;
        background: var(--color-accent);
        color: white;
        border-radius: 4px;
        font-size: 10px;
        font-weight: 600;
        margin-left: 8px;
    }

    /* Loading State */
    .loading {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        padding: 40px;
        color: var(--color-text-muted);
        font-size: 14px;
    }

    /* Button Variants */
    .btn-danger {
        background: var(--color-danger);
        color: white;
    }

    .btn-danger:hover {
        background: #dc2626;
    }
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
                            <button type="button" class="btn btn-outline btn-sm" style="margin-left:8px;" onclick="toggleBulkAdd()">📥 Bulk Add</button>
                            <div id="bulk-add-container" style="display:none; margin-top:12px;">
                                <div style="margin-bottom:8px; color: var(--color-text-muted); font-size:12px;">One URL per line. You can paste dozens or hundreds of links at once.</div>
                                <textarea id="bulk-add-textarea" class="form-input" rows="6" placeholder="https://example.com/a\nhttps://example.com/b\nhttps://example.com/c"></textarea>
                                <div style="display:flex; gap:8px; margin-top:8px; align-items:center;">
                                    <label style="font-size:12px; color: var(--color-text-secondary);">Default weight</label>
                                    <input id="bulk-add-weight" type="number" value="1" min="1" max="100" class="form-input" style="width:90px;">
                                    <button type="button" class="btn btn-primary btn-sm" onclick="bulkAddDestinations()">Add Links</button>
                                    <span id="bulk-add-feedback" style="font-size:12px; color: var(--color-text-muted);"></span>
                                </div>
                            </div>
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

// Load shortlinks data
async function loadLinks() {
    const container = document.getElementById('links-container');
    if (!container) return;
    
    // Show loading state
    container.innerHTML = `
        <div class="loading">
            <div class="loading-spinner"></div>
            Loading shortlinks...
        </div>
    `;
    
    try {
        console.log('Loading links from API...');
        const apiUrl = window.location.protocol + '//' + window.location.host + '/api/links';
        console.log('API URL:', apiUrl);
        
        const response = await fetch(apiUrl, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        });
        
        console.log('Links response status:', response.status);
        
        if (!response.ok) {
            const errorText = await response.text();
            console.error('Links error response:', errorText);
            throw new Error(`HTTP ${response.status}: ${errorText.substring(0, 100)}`);
        }
        
        const result = await response.json();
        console.log('Links result:', result);
        
        if (result.ok && result.data) {
            displayLinks(result.data);
        } else {
            throw new Error(result.message || 'Failed to load links');
        }
    } catch (error) {
        console.error('Load links error:', error);
        container.innerHTML = `
            <div class="text-center p-4" style="color: var(--color-danger);">
                ❌ Failed to load shortlinks: ${error.message}
                <br><button onclick="loadLinks()" class="btn btn-sm btn-primary" style="margin-top: 8px;">🔄 Retry</button>
            </div>
        `;
        showNotification('Failed to load shortlinks', 'error');
    }
}

// Load analytics data
async function loadAnalytics() {
    try {
        console.log('Loading analytics...');
        const apiUrl = window.location.protocol + '//' + window.location.host + `/api/analytics?period=${currentPeriod || 'week'}`;
        console.log('Analytics API URL:', apiUrl);
        
        const response = await fetch(apiUrl, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        });
        
        console.log('Analytics response status:', response.status);
        
        if (!response.ok) {
            const errorText = await response.text();
            console.error('Analytics error response:', errorText);
            throw new Error(`HTTP ${response.status}`);
        }
        
        const result = await response.json();
        console.log('Analytics result:', result);
        
        if (result.ok && result.data) {
            displayAnalytics(result.data);
        } else {
            throw new Error(result.message || 'Failed to load analytics');
        }
    } catch (error) {
        console.error('Load analytics error:', error);
        // Don't show notification for analytics failure, just log it
        // Set default values
        displayAnalytics({
            overview: {
                total_links: '-',
                total_clicks: '-',
                today_clicks: '-',
                avg_clicks_per_link: '-'
            }
        });
    }
}

// Display links in table
function displayLinks(links) {
    const container = document.getElementById('links-container');
    if (!container) return;
    
    if (!links || links.length === 0) {
        container.innerHTML = '<div class="text-muted text-center p-4">No shortlinks created yet</div>';
        return;
    }
    
    const html = links.map(link => `
        <div class="link-item">
            <div class="link-info">
                <div class="link-url">
                    <strong>/${link.slug}</strong>
                    ${link.is_rotator ? '<span class="badge">🔄 Rotator</span>' : ''}
                </div>
                <div class="link-destination">${link.destination}</div>
                <div class="link-meta">
                    ${link.clicks} clicks • Created ${new Date(link.created_at).toLocaleDateString()}
                </div>
            </div>
            <div class="link-actions">
                <button onclick="openVisitors('${link.slug}')" class="btn btn-sm" title="View IPs">👁️ IPs</button>
                <button onclick="openEditModal('${link.slug}', ${link.is_rotator ? 'true' : 'false'})" class="btn btn-sm" title="Edit destination(s)">✏️ Edit</button>
                <button onclick="resetViews('${link.slug}')" class="btn btn-sm" title="Reset views">🔄 Reset</button>
                <button onclick="copyLink('${link.slug}')" class="btn btn-sm" title="Copy link">📋 Copy</button>
                <button onclick="deleteLink('${link.slug}')" class="btn btn-sm btn-danger" title="Delete">🗑️</button>
            </div>
        </div>
    `).join('');
    
    container.innerHTML = html;
}

// Display analytics
function displayAnalytics(data) {
    console.log('Displaying analytics:', data);
    
    // Update stats cards - using correct data structure
    const overview = data.overview || {};
    const elements = {
        'total-links': overview.total_links || 0,
        'total-clicks': overview.total_clicks || 0,
        'today-clicks': overview.today_clicks || 0,
        'avg-clicks': overview.avg_clicks_per_link || 0
    };
    
    Object.entries(elements).forEach(([id, value]) => {
        const element = document.getElementById(id);
        if (element) {
            element.textContent = value;
            console.log(`Updated ${id} = ${value}`);
        } else {
            console.warn(`Element not found: ${id}`);
        }
    });
    
    // Update charts if present
    if (data.chart_data) {
        updateCharts(data.chart_data);
    }

    // Render sidebars: countries, devices, browsers, top links
    try {
        const escapeHtml = (str) => String(str == null ? '' : str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');

        // Top countries
        const countries = Array.isArray(data.top_countries) ? data.top_countries : [];
        const countriesList = document.getElementById('countries-list');
        if (countriesList) {
            if (countries.length === 0) {
                countriesList.innerHTML = '<li class="sidebar-item"><div class="text-muted">No data</div></li>';
            } else {
                countriesList.innerHTML = countries.map(c => `
                    <li class="sidebar-item" style="display:flex;justify-content:space-between;padding:8px 12px;">
                        <span>${escapeHtml(c.country || 'Unknown')}</span>
                        <span>${c.count ?? 0}</span>
                    </li>`).join('');
            }
        }

        // Device stats
        const devices = Array.isArray(data.device_stats) ? data.device_stats : [];
        const devicesList = document.getElementById('devices-list');
        if (devicesList) {
            if (devices.length === 0) {
                devicesList.innerHTML = '<li class="sidebar-item"><div class="text-muted">No data</div></li>';
            } else {
                devicesList.innerHTML = devices.map(d => `
                    <li class="sidebar-item" style="display:flex;justify-content:space-between;padding:8px 12px;">
                        <span>${escapeHtml(d.device || 'Unknown')}</span>
                        <span>${d.count ?? 0}</span>
                    </li>`).join('');
            }
        }

        // Browser stats
        const browsers = Array.isArray(data.browser_stats) ? data.browser_stats : [];
        const browsersList = document.getElementById('browsers-list');
        if (browsersList) {
            if (browsers.length === 0) {
                browsersList.innerHTML = '<li class="sidebar-item"><div class="text-muted">No data</div></li>';
            } else {
                browsersList.innerHTML = browsers.map(b => `
                    <li class="sidebar-item" style="display:flex;justify-content:space-between;padding:8px 12px;">
                        <span>${escapeHtml(b.browser || 'Unknown')}</span>
                        <span>${b.count ?? 0}</span>
                    </li>`).join('');
            }
        }

        // Top/Popular links
        const topLinks = Array.isArray(data.top_links) ? data.top_links : [];
        const popularList = document.getElementById('popular-links');
        if (popularList) {
            if (topLinks.length === 0) {
                popularList.innerHTML = '<li class="sidebar-item"><div class="text-muted">No data</div></li>';
            } else {
                popularList.innerHTML = topLinks.slice(0, 10).map(l => `
                    <li class="sidebar-item" style="padding:8px 12px;">
                        <div style="display:flex;justify-content:space-between;gap:8px;align-items:center;">
                            <a class="link-slug" href="/${escapeHtml(l.slug)}" target="_blank">/${escapeHtml(l.slug)}</a>
                            <span style="font-size:12px;color:var(--color-text-muted);">${l.clicks ?? 0} clicks</span>
                        </div>
                        <div class="link-destination" style="margin-top:4px;">${escapeHtml(l.destination || '')}</div>
                    </li>`).join('');
            }
        }
    } catch (e) {
        console.warn('Failed to render sidebar lists:', e);
    }
}

// Copy link to clipboard
async function copyLink(slug) {
    const fullUrl = `${window.location.origin}/${slug}`;
    try {
        await navigator.clipboard.writeText(fullUrl);
        showNotification('Link copied to clipboard!', 'success');
    } catch (error) {
        showNotification('Failed to copy link', 'error');
    }
}

// Delete link
async function deleteLink(slug) {
    if (!confirm(`Are you sure you want to delete /${slug}?`)) return;
    
    try {
        const apiUrl = window.location.protocol + '//' + window.location.host + `/api/delete/${slug}`;
        const response = await fetch(apiUrl, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        });
        
        if (!response.ok) throw new Error('Failed to delete');
        
        const result = await response.json();
        if (result.ok) {
            showNotification('Shortlink deleted successfully', 'success');
            loadLinks();
            loadAnalytics();
        } else {
            throw new Error(result.message || 'Delete failed');
        }
    } catch (error) {
        console.error('Delete error:', error);
        showNotification('Failed to delete shortlink', 'error');
    }
}

// Open visitors IPs page
function openVisitors(slug) {
    window.location.href = `/panel/shortlinks/${slug}/visitors`;
}

// Reset views for a specific shortlink
async function resetViews(slug) {
    if (!confirm(`Reset visitor count for /${slug}?`)) return;
    try {
        const apiUrl = `/api/reset-visitors/${slug}`;
        const response = await fetch(apiUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        });
        const result = await response.json();
        if (!response.ok || !result.ok) throw new Error(result.message || 'Failed to reset');
        showNotification(result.message || 'Views reset', 'success');
        loadLinks();
        loadAnalytics();
    } catch (e) {
        showNotification('Error: ' + e.message, 'error');
    }
}

// Edit destinations/destination via modal
function openEditModal(slug, isRotator) {
    const overlay = document.createElement('div');
    overlay.className = 'modal-overlay';
    const content = document.createElement('div');
    content.className = 'modal-content';
    content.innerHTML = `
        <div class="modal-header">
            <h3>Edit ${isRotator ? 'Rotator' : 'Destination'}</h3>
            <button class="modal-close" onclick="this.closest('.modal-overlay').remove()">✖</button>
        </div>
        <form id="edit-form">
            <div id="edit-body" style="padding:12px;">Loading...</div>
            <div class="modal-actions">
                <button type="button" class="btn" onclick="document.querySelector('.modal-overlay').remove()">Cancel</button>
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>`;
    overlay.appendChild(content);
    document.body.appendChild(overlay);

    if (isRotator) {
        fetch(`/api/rotator/${slug}`)
            .then(r => r.json())
            .then(res => {
                if (!res.ok) throw new Error(res.message || 'Failed');
                const data = res.data || {};
                const dests = (data.destinations || []).map((d) => `
                    <div class="destination-item">
                        <div class="destination-inputs">
                            <input type="url" class="form-input edit-url" value="${d.url || ''}" required>
                            <input type="text" class="form-input edit-name" value="${d.name || ''}" placeholder="Name (optional)">
                            <input type="number" class="form-input edit-weight" value="${d.weight || 1}" min="1" max="100">
                            <button type="button" class="btn-remove-destination" onclick="this.closest('.destination-item').remove()">🗑️</button>
                        </div>
                    </div>`).join('');
                document.getElementById('edit-body').innerHTML = `
                    <div class="form-group">
                        <label class="form-label">Rotation Type</label>
                        <select id="edit-rotation-type" class="form-input">
                            <option value="random" ${data.rotation_type === 'random' ? 'selected' : ''}>Random</option>
                            <option value="sequential" ${data.rotation_type === 'sequential' ? 'selected' : ''}>Sequential</option>
                            <option value="weighted" ${data.rotation_type === 'weighted' ? 'selected' : ''}>Weighted</option>
                        </select>
                    </div>
                    <div id="edit-dests">${dests ||('<div class=\'text-muted\'>No destinations</div>')}</div>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="addEditDestination()">+ Add Destination</button>
                `;
                const form = document.getElementById('edit-form');
                form.onsubmit = async (e) => {
                    e.preventDefault();
                    const nodes = Array.from(document.querySelectorAll('#edit-dests .destination-item'));
                    const destinations = nodes.map((n) => ({
                        url: n.querySelector('.edit-url').value.trim(),
                        name: n.querySelector('.edit-name').value.trim(),
                        weight: parseInt(n.querySelector('.edit-weight').value || '1', 10) || 1,
                        active: true
                    })).filter(d => d.url);
                    try {
                        const resp = await fetch(`/api/rotator/${slug}`, {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ is_rotator: true, rotation_type: document.getElementById('edit-rotation-type').value, destinations })
                        });
                        const json = await resp.json();
                        if (!resp.ok || !json.ok) throw new Error(json.message || 'Failed to save');
                        showNotification('Saved', 'success');
                        document.querySelector('.modal-overlay').remove();
                        loadLinks();
                    } catch (err) {
                        showNotification('Error: ' + err.message, 'error');
                    }
                };
            })
            .catch(e => {
                document.getElementById('edit-body').textContent = 'Failed to load: ' + e.message;
            });
    } else {
        document.getElementById('edit-body').innerHTML = `
            <div class="form-group">
                <label class="form-label">Destination URL</label>
                <input id="edit-single-url" class="form-input" type="url" required>
            </div>`;
        const card = Array.from(document.querySelectorAll('.link-item')).find(d => d.querySelector('.link-url strong').textContent === `/${slug}`);
        if (card) {
            const cur = card.querySelector('.link-destination')?.textContent?.trim();
            if (cur) document.getElementById('edit-single-url').value = cur;
        }
        const form = document.getElementById('edit-form');
        form.onsubmit = async (e) => {
            e.preventDefault();
            const destination = document.getElementById('edit-single-url').value.trim();
            try {
                const resp = await fetch(`/panel/shortlinks/${slug}/destinations`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ is_rotator: false, destination })
                });
                const json = await resp.json();
                if (!resp.ok || !json.ok) throw new Error(json.message || 'Failed to save');
                showNotification('Saved', 'success');
                document.querySelector('.modal-overlay').remove();
                loadLinks();
            } catch (err) {
                showNotification('Error: ' + err.message, 'error');
            }
        };
    }
}

function addEditDestination() {
    const container = document.getElementById('edit-dests');
    const html = `
        <div class="destination-item">
            <div class="destination-inputs">
                <input type="url" class="form-input edit-url" placeholder="https://example.com" required>
                <input type="text" class="form-input edit-name" placeholder="Name (optional)">
                <input type="number" class="form-input edit-weight" value="1" min="1" max="100">
                <button type="button" class="btn-remove-destination" onclick="this.closest('.destination-item').remove()">🗑️</button>
            </div>
        </div>`;
    container.insertAdjacentHTML('beforeend', html);
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

// Refresh all data
function refreshData() {
    showNotification('🔄 Refreshing data...', 'info');
    loadLinks();
    loadAnalytics();
}

// Update charts (placeholder function)
function updateCharts(chartData) {
    // This can be implemented later with chart libraries like Chart.js
    console.log('Chart data received:', chartData);
}

// Form helper functions
function toggleLinkType() {
    const linkType = document.querySelector('input[name="link_type"]:checked').value;
    const singleSection = document.getElementById('single-destination');
    const rotatorSection = document.getElementById('rotator-destinations');
    
    if (linkType === 'single') {
        singleSection.style.display = 'block';
        rotatorSection.style.display = 'none';
        // Clear rotator required
        document.querySelectorAll('#rotator-destinations input[required]').forEach(input => {
            input.removeAttribute('required');
        });
        // Add single required
        document.getElementById('destination').setAttribute('required', 'required');
    } else {
        singleSection.style.display = 'none';
        rotatorSection.style.display = 'block';
        // Clear single required
        document.getElementById('destination').removeAttribute('required');
        // Add rotator required
        document.querySelectorAll('#rotator-destinations .destination-url').forEach(input => {
            input.setAttribute('required', 'required');
        });
    }
}

function addDestination() {
    const container = document.getElementById('destinations-container');
    const index = container.children.length;
    
    const destinationHtml = `
        <div class="destination-item">
            <div class="destination-inputs">
                <input type="url" name="destinations[${index}][url]" class="form-input destination-url" placeholder="https://example.com/page-${index + 1}" required>
                <input type="text" name="destinations[${index}][name]" class="form-input destination-name" placeholder="Name (optional)">
                <input type="number" name="destinations[${index}][weight]" class="form-input destination-weight" placeholder="Weight" value="1" min="1" max="100">
                <button type="button" class="btn-remove-destination" onclick="removeDestination(this)" title="Remove destination">🗑️</button>
            </div>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', destinationHtml);
}

function removeDestination(button) {
    const container = document.getElementById('destinations-container');
    if (container.children.length > 1) {
        button.closest('.destination-item').remove();
        // Reindex remaining destinations
        Array.from(container.children).forEach((item, index) => {
            const inputs = item.querySelectorAll('input');
            inputs.forEach(input => {
                const name = input.name;
                if (name.includes('[') && name.includes(']')) {
                    const newName = name.replace(/\[\d+\]/, `[${index}]`);
                    input.name = newName;
                }
            });
        });
    }
}

// Toggle bulk add panel
function toggleBulkAdd() {
    const el = document.getElementById('bulk-add-container');
    el.style.display = (el.style.display === 'none' || el.style.display === '') ? 'block' : 'none';
}

// Append many destinations from textarea
function bulkAddDestinations() {
    const textarea = document.getElementById('bulk-add-textarea');
    const weightInput = document.getElementById('bulk-add-weight');
    const feedback = document.getElementById('bulk-add-feedback');
    if (!textarea) return;

    const lines = (textarea.value || '').split(/\r?\n/).map(v => v.trim()).filter(v => v !== '');
    const defaultWeight = Math.max(1, Math.min(100, parseInt(weightInput.value || '1', 10)));
    if (lines.length === 0) {
        feedback.textContent = 'No URLs detected.';
        return;
    }

    let added = 0;
    lines.forEach((val, i) => {
        try {
            // Pre-normalize for quick validation
            const url = val.startsWith('http') ? val : ('https://' + val.replace(/^\/+/, ''));
            new URL(url);

            const container = document.getElementById('destinations-container');
            const index = container.children.length;
            const html = `
                <div class="destination-item">
                    <div class="destination-inputs">
                        <input type="url" name="destinations[${index}][url]" class="form-input destination-url" value="${url}" required>
                        <input type="text" name="destinations[${index}][name]" class="form-input destination-name" value="${'Link ' + (index + 1)}">
                        <input type="number" name="destinations[${index}][weight]" class="form-input destination-weight" value="${defaultWeight}" min="1" max="100">
                        <button type="button" class="btn-remove-destination" onclick="removeDestination(this)" title="Remove destination">🗑️</button>
                    </div>
                </div>`;
            container.insertAdjacentHTML('beforeend', html);
            added++;
        } catch (e) {
            // skip invalid line
        }
    });

    feedback.textContent = `${added} link(s) added.`;
    if (added > 0) {
        textarea.value = '';
    }
}

// Create shortlink function
async function createShortlink() {
    const form = document.getElementById('create-form');
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    
    // Disable submit button
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<div class="loading-spinner"></div> Creating...';
    
    try {
        const formData = new FormData(form);
        
        // Convert FormData to JSON - FIXED VERSION with better error handling
        const data = {};
        
        // Get link type from radio buttons
        const linkTypeRadio = document.querySelector('input[name="link_type"]:checked');
        if (!linkTypeRadio) {
            throw new Error('Please select link type (Single Link or Link Rotator)');
        }
        
        const linkType = linkTypeRadio.value;
        data.is_rotator = (linkType === 'rotator');
        
        console.log('Form submission - Link type:', linkType, 'Is rotator:', data.is_rotator);
        
        if (data.is_rotator) {
            // Handle rotator data
            data.rotation_type = formData.get('rotation_type') || 'random';
            data.destinations = [];
            
            // Get all destination inputs
            const destinationItems = document.querySelectorAll('#destinations-container .destination-item');
            console.log('Found destination items:', destinationItems.length);
            
            if (destinationItems.length === 0) {
                throw new Error('No destination items found. Please add at least one destination.');
            }
            
            destinationItems.forEach((item, index) => {
                const urlInput = item.querySelector('input[name*="[url]"]');
                const nameInput = item.querySelector('input[name*="[name]"]');
                const weightInput = item.querySelector('input[name*="[weight]"]');
                
                if (!urlInput) {
                    console.warn(`No URL input found for destination ${index}`);
                    return;
                }
                
                const url = urlInput.value.trim();
                const name = nameInput ? nameInput.value.trim() : '';
                const weight = weightInput ? parseInt(weightInput.value) || 1 : 1;
                
                console.log(`Processing destination ${index}:`, {url, name, weight});
                
                if (url) {
                    // Validate URL format
                    try {
                        new URL(url.startsWith('http') ? url : 'https://' + url);
                    } catch (e) {
                        throw new Error(`Invalid URL format for destination ${index + 1}: ${url}`);
                    }
                    
                    data.destinations.push({
                        url: url,
                        name: name || `Destination ${index + 1}`,
                        weight: weight,
                        active: true
                    });
                }
            });
            
            if (data.destinations.length === 0) {
                throw new Error('At least one destination URL is required for link rotator');
            }
            
            console.log('Final rotator destinations:', data.destinations);
        } else {
            // Handle single destination
            data.destination = formData.get('destination');
            if (!data.destination || !data.destination.trim()) {
                throw new Error('Destination URL is required for single link');
            }
            data.destination = data.destination.trim();
            
            // Validate single destination URL
            try {
                new URL(data.destination.startsWith('http') ? data.destination : 'https://' + data.destination);
            } catch (e) {
                throw new Error(`Invalid URL format: ${data.destination}`);
            }
            
            console.log('Single destination:', data.destination);
        }
        
        // Handle optional slug
        data.slug = formData.get('slug');
        if (data.slug) {
            data.slug = data.slug.trim();
            // Validate slug format
            if (data.slug && !/^[a-zA-Z0-9_-]+$/.test(data.slug)) {
                throw new Error('Custom slug can only contain letters, numbers, hyphens and underscores');
            }
        }
        
        console.log('Final data being sent to API:', data);

        // Make API request with better error handling for production
        const apiUrl = window.location.protocol + '//' + window.location.host + '/api/create';
        console.log('Making request to:', apiUrl);
        
        const response = await fetch(apiUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        });

        console.log('API Response status:', response.status);
        console.log('API Response headers:', Object.fromEntries(response.headers.entries()));

        let result;
        const contentType = response.headers.get('content-type');
        
        if (contentType && contentType.includes('application/json')) {
            result = await response.json();
        } else {
            const textResponse = await response.text();
            console.error('Non-JSON response received:', textResponse);
            throw new Error('Server returned non-JSON response. Please check server configuration.');
        }

        console.log('API Response data:', result);
        
        if (response.ok && result.ok) {
            // Success!
            showNotification('✅ Shortlink berhasil dibuat!', 'success');
            
            // Reset form
            form.reset();
            
            // Reset form display to single link mode
            document.querySelector('input[name="link_type"][value="single"]').checked = true;
            toggleLinkType();
            
            // Refresh data
            await Promise.all([loadLinks(), loadAnalytics()]);
            
            // Show created shortlink info
            if (result.short_url) {
                setTimeout(() => {
                    showNotification(`🔗 Created: ${result.short_url}`, 'info');
                }, 500);
            } else if (result.data && result.data.slug) {
                setTimeout(() => {
                    showNotification(`🔗 Created: /${result.data.slug}`, 'info');
                }, 500);
            }
        } else {
            // Handle API errors
            const errorMessage = result.message || 'Failed to create shortlink';
            throw new Error(errorMessage);
        }
    } catch (error) {
        console.error('Create Shortlink Error:', error);
        
        let errorMessage = error.message;
        if (errorMessage.includes('ValidationException') || errorMessage.includes('422')) {
            errorMessage = 'Please check your input data and try again.';
        } else if (errorMessage.includes('fetch')) {
            errorMessage = 'Network error. Please check your connection and try again.';
        }
        
        showNotification('❌ Error: ' + errorMessage, 'error');
    } finally {
        // Re-enable submit button
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
}

// Toggle Stopbot status function
async function toggleStopbot() {
    const btn = document.getElementById('toggleStopbotBtn');
    if (!btn) return;
    
    const originalText = btn.textContent;
    btn.disabled = true;
    btn.textContent = '⏳ Processing...';

    try {
        // Get current status first
        const currentStatus = {{ \App\Models\PanelSetting::get('stopbot_enabled', false) ? 'true' : 'false' }};
        const newStatus = !currentStatus;

        const response = await fetch('/api/stopbot/config', {
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

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }

        const result = await response.json();
        
        if (result.ok) {
            showNotification(`Stopbot ${newStatus ? 'enabled' : 'disabled'} successfully!`, 'success');
            // Reload page to update status displays
            setTimeout(() => location.reload(), 1000);
        } else {
            throw new Error(result.message || 'Failed to toggle Stopbot');
        }
    } catch (error) {
        console.error('Toggle Stopbot Error:', error);
        showNotification('Error: ' + error.message, 'error');
        btn.textContent = originalText;
        btn.disabled = false;
    }
}
</script>
@endsection
