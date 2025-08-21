<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laravel Forge Troubleshoot</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body {
            font-family: 'Consolas', 'Monaco', monospace;
            background: #0a0a0a;
            color: #22c55e;
            padding: 20px;
            margin: 0;
        }
        .container { max-width: 1200px; margin: 0 auto; }
        .section {
            background: rgba(34, 197, 94, 0.1);
            border: 1px solid #22c55e;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .btn {
            background: #22c55e;
            color: #000;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            margin: 5px;
            font-weight: bold;
        }
        #output {
            background: rgba(0, 0, 0, 0.8);
            border: 1px solid #333;
            border-radius: 4px;
            padding: 15px;
            white-space: pre-wrap;
            font-family: monospace;
            font-size: 12px;
            max-height: 500px;
            overflow-y: auto;
            color: #e0e0e0;
        }
        .success { color: #22c55e; }
        .error { color: #ef4444; }
        .warning { color: #f59e0b; }
        .info { color: #3b82f6; }
        .form-group { margin-bottom: 15px; }
        .form-input {
            width: 100%;
            padding: 8px;
            background: rgba(0, 0, 0, 0.6);
            border: 1px solid #333;
            color: #e0e0e0;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Laravel Forge Troubleshoot</h1>
        
        <div class="section">
            <h2>Environment Check</h2>
            <button class="btn" onclick="checkEnvironment()">Check Environment</button>
            <button class="btn" onclick="testCSRF()">Test CSRF Token</button>
            <button class="btn" onclick="testAPIEndpoints()">Test All API Endpoints</button>
            <button class="btn" onclick="clearOutput()">Clear Log</button>
        </div>
        
        <div class="section">
            <h2>Manual Shortlink Test</h2>
            <div class="form-group">
                <label>Destination URL:</label>
                <input type="url" id="test-url" class="form-input" value="https://www.google.com" placeholder="https://example.com">
            </div>
            <div class="form-group">
                <label>Custom Slug (optional):</label>
                <input type="text" id="test-slug" class="form-input" placeholder="my-test-link">
            </div>
            <button class="btn" onclick="testShortlinkCreation()">Create Test Shortlink</button>
        </div>
        
        <div class="section">
            <h2>Debug Output</h2>
            <div id="output">Ready for testing...\n</div>
        </div>
    </div>

    <script>
        const output = document.getElementById('output');
        
        function log(message, type = 'info') {
            const timestamp = new Date().toLocaleTimeString();
            const prefix = type === 'error' ? '❌' : type === 'success' ? '✅' : type === 'warning' ? '⚠️' : 'ℹ️';
            output.textContent += `[${timestamp}] ${prefix} ${message}\n`;
            output.scrollTop = output.scrollHeight;
        }
        
        function clearOutput() {
            output.textContent = '';
        }
        
        function checkEnvironment() {
            log('=== Environment Check ===');
            log(`Current URL: ${window.location.href}`);
            log(`Protocol: ${window.location.protocol}`);
            log(`Host: ${window.location.host}`);
            log(`User Agent: ${navigator.userAgent}`);
            
            // Check CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            if (csrfToken) {
                log(`CSRF Token: ${csrfToken.getAttribute('content').substring(0, 20)}...`, 'success');
            } else {
                log('CSRF Token: Missing!', 'error');
            }
            
            // Check if HTTPS
            if (window.location.protocol === 'https:') {
                log('HTTPS: Enabled', 'success');
            } else {
                log('HTTPS: Disabled', 'warning');
            }
        }
        
        async function testCSRF() {
            log('=== Testing CSRF Token ===');
            try {
                const response = await fetch('/api/debug', {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });
                
                log(`CSRF Test Response: ${response.status} ${response.statusText}`);
                
                if (response.ok) {
                    const data = await response.json();
                    log(`CSRF Test Result: ${JSON.stringify(data, null, 2)}`, 'success');
                } else {
                    const text = await response.text();
                    log(`CSRF Test Error: ${text}`, 'error');
                }
            } catch (error) {
                log(`CSRF Test Error: ${error.message}`, 'error');
            }
        }
        
        async function testAPIEndpoints() {
            log('=== Testing API Endpoints ===');
            
            const endpoints = [
                { name: 'Analytics', url: '/api/analytics', method: 'GET' },
                { name: 'Links List', url: '/api/links', method: 'GET' },
                { name: 'Debug', url: '/api/debug', method: 'GET' }
            ];
            
            for (const endpoint of endpoints) {
                try {
                    log(`Testing ${endpoint.name}...`);
                    
                    const fullUrl = window.location.protocol + '//' + window.location.host + endpoint.url;
                    const response = await fetch(fullUrl, {
                        method: endpoint.method,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });
                    
                    log(`${endpoint.name}: ${response.status} ${response.statusText}`);
                    
                    if (response.ok) {
                        const data = await response.json();
                        if (data.ok) {
                            log(`${endpoint.name}: Success ✅`, 'success');
                        } else {
                            log(`${endpoint.name}: API returned error: ${data.message || 'Unknown'}`, 'warning');
                        }
                    } else {
                        const text = await response.text();
                        log(`${endpoint.name}: HTTP Error: ${text.substring(0, 200)}`, 'error');
                    }
                    
                } catch (error) {
                    log(`${endpoint.name}: Network Error: ${error.message}`, 'error');
                }
            }
        }
        
        async function testShortlinkCreation() {
            log('=== Testing Shortlink Creation ===');
            
            const url = document.getElementById('test-url').value.trim();
            const slug = document.getElementById('test-slug').value.trim();
            
            if (!url) {
                log('Please enter a destination URL', 'error');
                return;
            }
            
            const data = {
                is_rotator: false,
                destination: url
            };
            
            if (slug) {
                data.slug = slug;
            }
            
            log(`Request Data: ${JSON.stringify(data, null, 2)}`);
            
            try {
                const fullUrl = window.location.protocol + '//' + window.location.host + '/api/create';
                log(`Making request to: ${fullUrl}`);
                
                const response = await fetch(fullUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(data)
                });
                
                log(`Creation Response: ${response.status} ${response.statusText}`);
                log(`Response Headers: ${JSON.stringify(Object.fromEntries(response.headers.entries()), null, 2)}`);
                
                const contentType = response.headers.get('content-type');
                
                if (contentType && contentType.includes('application/json')) {
                    const result = await response.json();
                    log(`Response Data: ${JSON.stringify(result, null, 2)}`);
                    
                    if (response.ok && result.ok) {
                        log(`SUCCESS! Shortlink created: ${result.data.full_url}`, 'success');
                        
                        // Clear form
                        document.getElementById('test-slug').value = '';
                    } else {
                        log(`Creation failed: ${result.message || 'Unknown error'}`, 'error');
                    }
                } else {
                    const text = await response.text();
                    log(`Non-JSON Response: ${text}`, 'error');
                }
                
            } catch (error) {
                log(`Creation Error: ${error.message}`, 'error');
            }
        }
        
        // Auto-run environment check
        document.addEventListener('DOMContentLoaded', function() {
            checkEnvironment();
        });
    </script>
</body>
</html>
