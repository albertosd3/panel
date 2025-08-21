<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Simple test page to check shortlink functionality
?>
<!DOCTYPE html>
<html>
<head>
    <title>Shortlink API Test</title>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="<?php echo csrf_token(); ?>">
    <style>
        body { font-family: monospace; background: #1a1a1a; color: #e0e0e0; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        .test-section { margin: 20px 0; padding: 20px; border: 1px solid #333; background: #2a2a2a; }
        .btn { padding: 10px 15px; background: #22c55e; color: #000; border: none; cursor: pointer; margin: 5px; }
        #output { margin-top: 20px; padding: 15px; background: #0a0a0a; border: 1px solid #333; white-space: pre-wrap; min-height: 200px; }
        .success { color: #22c55e; }
        .error { color: #ef4444; }
        .warning { color: #f59e0b; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔗 Shortlink API Test Suite</h1>
        
        <div class="test-section">
            <h2>Quick Tests</h2>
            <button class="btn" onclick="testSingleShortlink()">Test Single Shortlink</button>
            <button class="btn" onclick="testRotatorShortlink()">Test Rotator Shortlink</button>
            <button class="btn" onclick="testListShortlinks()">Test List Shortlinks</button>
            <button class="btn" onclick="testAnalytics()">Test Analytics</button>
            <button class="btn" onclick="clearOutput()">Clear Output</button>
        </div>
        
        <div id="output">Ready to test...</div>
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
        
        async function makeApiRequest(endpoint, data = null) {
            try {
                const options = {
                    method: data ? 'POST' : 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                };
                
                if (data) {
                    options.body = JSON.stringify(data);
                }
                
                log(`Making ${options.method} request to ${endpoint}...`);
                if (data) {
                    log(`Request data: ${JSON.stringify(data, null, 2)}`);
                }
                
                const response = await fetch(endpoint, options);
                
                log(`Response status: ${response.status} ${response.statusText}`);
                
                const contentType = response.headers.get('content-type');
                let result;
                
                if (contentType && contentType.includes('application/json')) {
                    result = await response.json();
                } else {
                    const text = await response.text();
                    log(`Non-JSON response: ${text.substring(0, 200)}...`, 'warning');
                    return { ok: false, error: 'Non-JSON response received' };
                }
                
                if (response.ok && result.ok) {
                    log(`Success: ${JSON.stringify(result, null, 2)}`, 'success');
                } else {
                    log(`Error: ${JSON.stringify(result, null, 2)}`, 'error');
                }
                
                return result;
                
            } catch (error) {
                log(`Network error: ${error.message}`, 'error');
                return { ok: false, error: error.message };
            }
        }
        
        async function testSingleShortlink() {
            log('=== Testing Single Shortlink Creation ===');
            
            const data = {
                is_rotator: false,
                destination: 'https://www.google.com',
                slug: 'test-single-' + Date.now()
            };
            
            await makeApiRequest('/api/create', data);
        }
        
        async function testRotatorShortlink() {
            log('=== Testing Rotator Shortlink Creation ===');
            
            const data = {
                is_rotator: true,
                rotation_type: 'random',
                destinations: [
                    { url: 'https://www.google.com', name: 'Google', weight: 1, active: true },
                    { url: 'https://www.bing.com', name: 'Bing', weight: 2, active: true },
                    { url: 'https://www.duckduckgo.com', name: 'DuckDuckGo', weight: 1, active: true }
                ],
                slug: 'test-rotator-' + Date.now()
            };
            
            await makeApiRequest('/api/create', data);
        }
        
        async function testListShortlinks() {
            log('=== Testing Shortlink List ===');
            await makeApiRequest('/api/links');
        }
        
        async function testAnalytics() {
            log('=== Testing Analytics ===');
            await makeApiRequest('/api/analytics');
        }
    </script>
</body>
</html>
