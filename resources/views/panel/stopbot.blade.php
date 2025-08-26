@extends('layouts.envelope')

@section('title', 'Stopbot Configuration')

@section('content')
<div class="envelope-container">
    <div class="paper" style="width:100%;max-width:800px;padding:20px;">
        <div class="heading-primary">Stopbot.net Configuration</div>
        <div class="text-muted" style="margin-bottom:20px;">External bot blocking service integration</div>

        <div class="card" style="padding:20px;">
            <form id="stopbotForm">
                <div class="form-group">
                    <label class="form-label">Enable Stopbot</label>
                    <select id="enabled" class="form-control">
                        <option value="true" {{ \App\Models\PanelSetting::get('stopbot_enabled', false) ? 'selected' : '' }}>Enabled</option>
                        <option value="false" {{ !\App\Models\PanelSetting::get('stopbot_enabled', false) ? 'selected' : '' }}>Disabled</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">API Key</label>
                    <input type="text" id="apiKey" class="form-control" 
                           value="{{ \App\Models\PanelSetting::get('stopbot_api_key', '') }}" 
                           placeholder="Enter your Stopbot.net API key">
                    <small class="text-muted">Get your API key from <a href="https://stopbot.net" target="_blank">stopbot.net</a></small>
                </div>

                <div class="form-group">
                    <label class="form-label">Redirect URL</label>
                    <input type="url" id="redirectUrl" class="form-control" 
                           value="{{ \App\Models\PanelSetting::get('stopbot_redirect_url', 'https://www.google.com') }}" 
                           placeholder="https://www.google.com">
                    <small class="text-muted">Leave empty to return 404 response instead of redirecting</small>
                </div>

                <div class="form-group">
                    <label class="form-label">Enable Logging</label>
                    <select id="logEnabled" class="form-control">
                        <option value="true" {{ \App\Models\PanelSetting::get('stopbot_log_enabled', true) ? 'selected' : '' }}>Enabled</option>
                        <option value="false" {{ !\App\Models\PanelSetting::get('stopbot_log_enabled', true) ? 'selected' : '' }}>Disabled</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">API Timeout (seconds)</label>
                    <input type="number" id="timeout" class="form-control" 
                           value="{{ \App\Models\PanelSetting::get('stopbot_timeout', 5) }}" 
                           min="1" max="30" placeholder="5">
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Save Configuration</button>
                    <button type="button" class="btn btn-secondary" id="testBtn">Test API Connection</button>
                    <a href="{{ route('panel.shortlinks') }}" class="btn btn-outline">Back</a>
                </div>
            </form>
        </div>

        <div class="card" style="padding:20px;margin-top:20px;">
            <div class="heading-secondary">Current Status</div>
            <div style="margin-top:10px;">
                <p><strong>Status:</strong> 
                    <span class="badge {{ \App\Models\PanelSetting::get('stopbot_enabled', false) ? 'bg-success' : 'bg-secondary' }}">
                        {{ \App\Models\PanelSetting::get('stopbot_enabled', false) ? 'Enabled' : 'Disabled' }}
                    </span>
                </p>
                <p><strong>API Key:</strong> {{ \App\Models\PanelSetting::get('stopbot_api_key', '') ? 'Configured' : 'Not configured' }}</p>
                <p><strong>Redirect URL:</strong> {{ \App\Models\PanelSetting::get('stopbot_redirect_url', '') ?: 'Return 404' }}</p>
                <p><strong>Logging:</strong> {{ \App\Models\PanelSetting::get('stopbot_log_enabled', true) ? 'Enabled' : 'Disabled' }}</p>
                <p><strong>Timeout:</strong> {{ \App\Models\PanelSetting::get('stopbot_timeout', 5) }} seconds</p>
            </div>
        </div>

        <div class="card" style="padding:20px;margin-top:20px;">
            <div class="heading-secondary">Usage Statistics</div>
            <div id="statsContent" style="margin-top:10px;">
                <button type="button" class="btn btn-outline" id="loadStatsBtn">Load Statistics</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('stopbotForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const data = {
        enabled: document.getElementById('enabled').value === 'true',
        api_key: document.getElementById('apiKey').value,
        redirect_url: document.getElementById('redirectUrl').value,
        log_enabled: document.getElementById('logEnabled').value === 'true',
        timeout: parseInt(document.getElementById('timeout').value) || 5
    };

    try {
        const response = await fetch('/api/stopbot/config', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();
        if (result.success) {
            const data = result.data || {};
            alert('✅ Tersimpan. Status: ' + (data.enabled ? 'Enabled' : 'Disabled'));
            location.reload();
        } else {
            alert('❌ Gagal: ' + (result.message || 'Tidak bisa menyimpan'));            
        }
    } catch (error) {
        alert('❌ Error: ' + error.message);
    }
});

document.getElementById('testBtn').addEventListener('click', async () => {
    const apiKey = document.getElementById('apiKey').value;
    
    if (!apiKey) {
        alert('Please enter an API key first');
        return;
    }

    const testBtn = document.getElementById('testBtn');
    const originalText = testBtn.textContent;
    testBtn.disabled = true;
    testBtn.textContent = '⏳ Testing...';

    try {
        const response = await fetch('/api/stopbot/test', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ api_key: apiKey })
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }

        const responseText = await response.text();
                let result;
        try {
            result = JSON.parse(responseText);
        } catch (e) {
            throw new Error(`Invalid JSON response: ${responseText.substring(0, 200)}`);
        }
        
        if (result.success) {
            alert('✅ API connection successful!\n\nResponse: ' + JSON.stringify(result.data || {}, null, 2));
        } else {
            alert('❌ API test failed: ' + (result.message || 'Unknown error'));
        }
    } catch (error) {
        console.error('Test API Error:', error);
        alert('❌ Connection error: ' + error.message);
    } finally {
        testBtn.disabled = false;
        testBtn.textContent = originalText;
    }
});

document.getElementById('loadStatsBtn').addEventListener('click', async () => {
    try {
        const response = await fetch('/api/stopbot/stats', {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });

        const result = await response.json();
        
        if (result.success) {
            const stats = result.data || {};
            document.getElementById('statsContent').innerHTML = `
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px;">
                    <div class="card" style="padding:12px;text-align:center;">
                        <div class="text-small text-muted">Total Checks</div>
                        <div style="font-size:24px;font-weight:bold;color:var(--color-primary);">${stats.total_checks || 0}</div>
                    </div>
                    <div class="card" style="padding:12px;text-align:center;">
                        <div class="text-small text-muted">Blocked Requests</div>
                        <div style="font-size:24px;font-weight:bold;color:var(--color-danger);">${stats.blocked_requests || 0}</div>
                    </div>
                    <div class="card" style="padding:12px;text-align:center;">
                        <div class="text-small text-muted">Success Rate</div>
                        <div style="font-size:24px;font-weight:bold;color:var(--color-success);">${stats.success_rate || '0%'}</div>
                    </div>
                </div>
            `;
        } else {
            document.getElementById('statsContent').innerHTML = '<p class="text-muted">Failed to load statistics</p>';
        }
    } catch (error) {
        document.getElementById('statsContent').innerHTML = '<p class="text-muted">Error loading statistics</p>';
    }
});
</script>
@endpush
@endsection
