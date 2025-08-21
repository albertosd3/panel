<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Shortlink Creation Debug</title>
    <style>
        body { font-family: monospace; background: #1e1e1e; color: #ddd; padding: 20px; }
        .form-group { margin: 15px 0; }
        label { display: block; margin-bottom: 5px; color: #22c55e; }
        input, select, textarea { width: 100%; padding: 8px; background: #2a2a2a; border: 1px solid #444; color: #ddd; }
        button { background: #22c55e; color: #000; padding: 10px 20px; border: none; cursor: pointer; margin: 10px 5px; }
        #output { background: #000; padding: 15px; margin: 20px 0; white-space: pre-wrap; font-size: 12px; max-height: 400px; overflow-y: auto; }
        .success { color: #22c55e; }
        .error { color: #ef4444; }
    </style>
</head>
<body>
    <h1>🔧 Shortlink Creation Debug Tool</h1>
    
    <div class="form-group">
        <label>Link Type:</label>
        <select id="linkType" onchange="toggleForm()">
            <option value="single">Single Link</option>
            <option value="rotator">Link Rotator</option>
        </select>
    </div>
    
    <div id="singleForm">
        <div class="form-group">
            <label>Destination URL:</label>
            <input type="url" id="destination" value="https://google.com" placeholder="https://example.com">
        </div>
    </div>
    
    <div id="rotatorForm" style="display: none;">
        <div class="form-group">
            <label>Destinations (JSON format):</label>
            <textarea id="destinations" rows="6">[
  {"url": "https://google.com", "name": "Google", "weight": 1},
  {"url": "https://youtube.com", "name": "YouTube", "weight": 2}
]</textarea>
        </div>
    </div>
    
    <div class="form-group">
        <label>Custom Slug (optional):</label>
        <input type="text" id="slug" placeholder="my-custom-link">
    </div>
    
    <button onclick="testCreate()">🚀 Test Create Shortlink</button>
    <button onclick="clearOutput()">🧹 Clear Output</button>
    
    <div id="output"></div>

    <script>
        function log(message, type = 'info') {
            const output = document.getElementById('output');
            const timestamp = new Date().toLocaleTimeString();
            const className = type === 'error' ? 'error' : type === 'success' ? 'success' : '';
            output.innerHTML += `<span class="${className}">[${timestamp}] ${message}</span>\n`;
            output.scrollTop = output.scrollHeight;
        }
        
        function clearOutput() {
            document.getElementById('output').innerHTML = '';
        }
        
        function toggleForm() {
            const linkType = document.getElementById('linkType').value;
            const singleForm = document.getElementById('singleForm');
            const rotatorForm = document.getElementById('rotatorForm');
            
            if (linkType === 'rotator') {
                singleForm.style.display = 'none';
                rotatorForm.style.display = 'block';
            } else {
                singleForm.style.display = 'block';
                rotatorForm.style.display = 'none';
            }
        }
        
        async function testCreate() {
            log('🚀 Starting shortlink creation test...', 'info');
            
            try {
                const linkType = document.getElementById('linkType').value;
                const slug = document.getElementById('slug').value.trim();
                
                let data = {
                    is_rotator: linkType === 'rotator'
                };
                
                if (slug) {
                    data.slug = slug;
                }
                
                if (linkType === 'rotator') {
                    const destinationsText = document.getElementById('destinations').value.trim();
                    try {
                        data.destinations = JSON.parse(destinationsText);
                        data.rotation_type = 'random';
                        log(`📋 Parsed ${data.destinations.length} destinations`, 'info');
                    } catch (e) {
                        throw new Error('Invalid JSON format for destinations: ' + e.message);
                    }
                } else {
                    const destination = document.getElementById('destination').value.trim();
                    if (!destination) {
                        throw new Error('Destination URL is required');
                    }
                    data.destination = destination;
                }
                
                log('📤 Request data: ' + JSON.stringify(data, null, 2), 'info');
                
                // Get CSRF token
                const csrfToken = document.querySelector('meta[name="csrf-token"]');
                if (!csrfToken) {
                    throw new Error('CSRF token not found in page');
                }
                
                log('🔒 CSRF token found: ' + csrfToken.getAttribute('content').substring(0, 20) + '...', 'info');
                
                // Make API request
                const apiUrl = window.location.protocol + '//' + window.location.host + '/api/create';
                log('🌐 Making request to: ' + apiUrl, 'info');
                
                const response = await fetch(apiUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken.getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(data)
                });
                
                log('📥 Response status: ' + response.status, 'info');
                log('📥 Response headers: ' + JSON.stringify(Object.fromEntries(response.headers.entries()), null, 2), 'info');
                
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    const text = await response.text();
                    log('❌ Non-JSON response received:', 'error');
                    log(text, 'error');
                    throw new Error('Server returned non-JSON response');
                }
                
                const result = await response.json();
                log('📥 Response data: ' + JSON.stringify(result, null, 2), 'info');
                
                if (response.ok && result.ok) {
                    log('✅ SUCCESS! Shortlink created successfully!', 'success');
                    if (result.data && result.data.slug) {
                        log(`🔗 Created shortlink: /${result.data.slug}`, 'success');
                        log(`🌐 Full URL: ${result.short_url || window.location.origin + '/' + result.data.slug}`, 'success');
                    }
                } else {
                    log('❌ API returned error: ' + (result.message || 'Unknown error'), 'error');
                }
                
            } catch (error) {
                log('❌ ERROR: ' + error.message, 'error');
                console.error('Detailed error:', error);
            }
        }
        
        // Initialize
        log('🔧 Debug tool loaded. Ready to test shortlink creation.', 'info');
        log('🔍 Current URL: ' + window.location.href, 'info');
        log('🔍 Protocol: ' + window.location.protocol, 'info');
    </script>
</body>
</html>
            color: #e0e0e0;
            font-family: inherit;
        }
        .form-input:focus, .form-select:focus {
            outline: none;
            border-color: #22c55e;
            box-shadow: 0 0 0 2px rgba(34, 197, 94, 0.2);
        }
        .btn {
            background: #22c55e;
            color: #000;
            border: none;
            padding: 12px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            margin: 5px;
            transition: all 0.2s;
        }
        .btn:hover {
            background: #16a34a;
            transform: translateY(-1px);
        }
        .btn:disabled {
            background: #666;
            color: #999;
            cursor: not-allowed;
            transform: none;
        }
        .toggle-group {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
        }
        .toggle-option {
            flex: 1;
            padding: 10px;
            border: 1px solid #333;
            border-radius: 6px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
            background: rgba(0, 0, 0, 0.3);
        }
        .toggle-option:hover {
            border-color: #22c55e;
        }
        .toggle-option.active {
            background: rgba(34, 197, 94, 0.2);
            border-color: #22c55e;
            color: #22c55e;
        }
        .destination-item {
            border: 1px solid #333;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 10px;
            background: rgba(0, 0, 0, 0.2);
        }
        .destination-inputs {
            display: grid;
            grid-template-columns: 2fr 1fr 80px 40px;
            gap: 10px;
            align-items: center;
        }
        .btn-remove {
            background: #dc2626;
            padding: 8px;
            border-radius: 4px;
            color: white;
            text-align: center;
        }
        #output {
            background: rgba(0, 0, 0, 0.8);
            border: 1px solid #333;
            border-radius: 6px;
            padding: 15px;
            white-space: pre-wrap;
            font-family: 'Consolas', monospace;
            font-size: 12px;
            max-height: 400px;
            overflow-y: auto;
            color: #e0e0e0;
        }
        .success { color: #22c55e; }
        .error { color: #ef4444; }
        .warning { color: #f59e0b; }
        .info { color: #3b82f6; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔗 Shortlink Panel - Debug Interface</h1>
            <p>Test shortlink creation functionality</p>
        </div>

        <div class="test-section">
            <h2>Create Shortlink</h2>
            
            <form id="shortlink-form">
                <div class="toggle-group">
                    <div class="toggle-option active" data-type="single">
                        📎 Single Link
                    </div>
                    <div class="toggle-option" data-type="rotator">
                        🔄 Link Rotator
                    </div>
                </div>

                <!-- Single Link Section -->
                <div id="single-section">
                    <div class="form-group">
                        <label class="form-label">Destination URL</label>
                        <input type="url" id="destination" class="form-input" 
                               placeholder="https://example.com/your-long-url" 
                               value="https://www.google.com">
                    </div>
                </div>

                <!-- Rotator Section -->
                <div id="rotator-section" style="display: none;">
                    <div class="form-group">
                        <label class="form-label">Rotation Type</label>
                        <select id="rotation-type" class="form-select">
                            <option value="random">Random</option>
                            <option value="sequential">Sequential</option>
                            <option value="weighted">Weighted</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Destinations</label>
                        <div id="destinations-container">
                            <div class="destination-item">
                                <div class="destination-inputs">
                                    <input type="url" class="form-input dest-url" placeholder="https://example.com/page-1" value="https://www.google.com">
                                    <input type="text" class="form-input dest-name" placeholder="Name" value="Google">
                                    <input type="number" class="form-input dest-weight" placeholder="Weight" value="1" min="1" max="100">
                                    <div class="btn-remove" onclick="removeDestination(this)">🗑️</div>
                                </div>
                            </div>
                            <div class="destination-item">
                                <div class="destination-inputs">
                                    <input type="url" class="form-input dest-url" placeholder="https://example.com/page-2" value="https://www.bing.com">
                                    <input type="text" class="form-input dest-name" placeholder="Name" value="Bing">
                                    <input type="number" class="form-input dest-weight" placeholder="Weight" value="2" min="1" max="100">
                                    <div class="btn-remove" onclick="removeDestination(this)">🗑️</div>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn" onclick="addDestination()">+ Add Destination</button>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Custom Slug (optional)</label>
                    <input type="text" id="slug" class="form-input" 
                           placeholder="my-custom-link" 
                           pattern="[a-zA-Z0-9_-]+"
                           title="Only letters, numbers, hyphens and underscores">
                </div>

                <button type="submit" class="btn" id="create-btn">✨ Create Shortlink</button>
                <button type="button" class="btn" onclick="clearLog()">🧹 Clear Log</button>
            </form>
        </div>

        <div class="test-section">
            <h2>Debug Output</h2>
            <div id="output">Ready for testing...</div>
        </div>
    </div>

    <script>
        let currentType = 'single';
        const output = document.getElementById('output');

        function log(message, type = 'info') {
            const timestamp = new Date().toLocaleTimeString();
            const prefix = type === 'error' ? '❌' : type === 'success' ? '✅' : type === 'warning' ? '⚠️' : 'ℹ️';
            output.textContent += `[${timestamp}] ${prefix} ${message}\n`;
            output.scrollTop = output.scrollHeight;
        }

        function clearLog() {
            output.textContent = '';
        }

        // Toggle between single and rotator
        document.querySelectorAll('.toggle-option').forEach(option => {
            option.addEventListener('click', () => {
                document.querySelectorAll('.toggle-option').forEach(opt => opt.classList.remove('active'));
                option.classList.add('active');
                
                currentType = option.dataset.type;
                
                if (currentType === 'single') {
                    document.getElementById('single-section').style.display = 'block';
                    document.getElementById('rotator-section').style.display = 'none';
                } else {
                    document.getElementById('single-section').style.display = 'none';
                    document.getElementById('rotator-section').style.display = 'block';
                }
                
                log(`Switched to ${currentType} mode`);
            });
        });

        function addDestination() {
            const container = document.getElementById('destinations-container');
            const newDestination = document.createElement('div');
            newDestination.className = 'destination-item';
            newDestination.innerHTML = `
                <div class="destination-inputs">
                    <input type="url" class="form-input dest-url" placeholder="https://example.com/new-page">
                    <input type="text" class="form-input dest-name" placeholder="Name">
                    <input type="number" class="form-input dest-weight" placeholder="Weight" value="1" min="1" max="100">
                    <div class="btn-remove" onclick="removeDestination(this)">🗑️</div>
                </div>
            `;
            container.appendChild(newDestination);
            log('Added new destination');
        }

        function removeDestination(button) {
            const container = document.getElementById('destinations-container');
            if (container.children.length > 1) {
                button.closest('.destination-item').remove();
                log('Removed destination');
            } else {
                log('Cannot remove the last destination', 'warning');
            }
        }

        // Form submission
        document.getElementById('shortlink-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const createBtn = document.getElementById('create-btn');
            const originalText = createBtn.textContent;
            
            try {
                createBtn.disabled = true;
                createBtn.textContent = '⏳ Creating...';
                
                log('=== Starting shortlink creation ===');
                
                // Prepare data
                const data = {
                    is_rotator: currentType === 'rotator'
                };
                
                if (currentType === 'single') {
                    data.destination = document.getElementById('destination').value.trim();
                    if (!data.destination) {
                        throw new Error('Destination URL is required');
                    }
                    log(`Single destination: ${data.destination}`);
                } else {
                    data.rotation_type = document.getElementById('rotation-type').value;
                    data.destinations = [];
                    
                    document.querySelectorAll('.destination-item').forEach((item, index) => {
                        const url = item.querySelector('.dest-url').value.trim();
                        const name = item.querySelector('.dest-name').value.trim();
                        const weight = parseInt(item.querySelector('.dest-weight').value) || 1;
                        
                        if (url) {
                            data.destinations.push({
                                url: url,
                                name: name || `Destination ${index + 1}`,
                                weight: weight,
                                active: true
                            });
                        }
                    });
                    
                    if (data.destinations.length === 0) {
                        throw new Error('At least one destination is required');
                    }
                    
                    log(`Rotator with ${data.destinations.length} destinations (type: ${data.rotation_type})`);
                }
                
                // Optional slug
                const slug = document.getElementById('slug').value.trim();
                if (slug) {
                    data.slug = slug;
                    log(`Custom slug: ${slug}`);
                }
                
                log(`Final request data: ${JSON.stringify(data, null, 2)}`);
                
                // Make API request
                const response = await fetch('/api/create', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(data)
                });
                
                log(`Response status: ${response.status} ${response.statusText}`);
                
                const contentType = response.headers.get('content-type');
                log(`Response content-type: ${contentType}`);
                
                let result;
                if (contentType && contentType.includes('application/json')) {
                    result = await response.json();
                } else {
                    const text = await response.text();
                    log(`Non-JSON response received: ${text.substring(0, 200)}...`, 'error');
                    throw new Error('Server returned non-JSON response');
                }
                
                log(`Response data: ${JSON.stringify(result, null, 2)}`);
                
                if (response.ok && result.ok) {
                    log(`SUCCESS! Shortlink created:`, 'success');
                    log(`  - Slug: ${result.data.slug}`, 'success');
                    log(`  - URL: ${result.data.full_url}`, 'success');
                    if (result.data.is_rotator) {
                        log(`  - Destinations: ${result.data.destinations.length}`, 'success');
                    }
                    
                    // Reset form
                    document.getElementById('shortlink-form').reset();
                    if (currentType === 'rotator') {
                        // Reset destinations to default
                        const container = document.getElementById('destinations-container');
                        container.innerHTML = `
                            <div class="destination-item">
                                <div class="destination-inputs">
                                    <input type="url" class="form-input dest-url" placeholder="https://example.com/page-1" value="https://www.google.com">
                                    <input type="text" class="form-input dest-name" placeholder="Name" value="Google">
                                    <input type="number" class="form-input dest-weight" placeholder="Weight" value="1" min="1" max="100">
                                    <div class="btn-remove" onclick="removeDestination(this)">🗑️</div>
                                </div>
                            </div>
                            <div class="destination-item">
                                <div class="destination-inputs">
                                    <input type="url" class="form-input dest-url" placeholder="https://example.com/page-2" value="https://www.bing.com">
                                    <input type="text" class="form-input dest-name" placeholder="Name" value="Bing">
                                    <input type="number" class="form-input dest-weight" placeholder="Weight" value="2" min="1" max="100">
                                    <div class="btn-remove" onclick="removeDestination(this)">🗑️</div>
                                </div>
                            </div>
                        `;
                    }
                } else {
                    log(`FAILED! ${result.message || 'Unknown error'}`, 'error');
                }
                
            } catch (error) {
                log(`ERROR: ${error.message}`, 'error');
                console.error('Shortlink creation error:', error);
            } finally {
                createBtn.disabled = false;
                createBtn.textContent = originalText;
            }
        });

        // Initialize
        log('Debug interface loaded');
        log('CSRF token: ' + document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
    </script>
</body>
</html>
