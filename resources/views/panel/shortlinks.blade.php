<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Panel Shortlink</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root{
            --bg: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --card: rgba(255, 255, 255, 0.1);
            --card-hover: rgba(255, 255, 255, 0.15);
            --border: rgba(255, 255, 255, 0.2);
            --text: #ffffff;
            --text-muted: rgba(255, 255, 255, 0.7);
            --primary: #4fc3f7;
            --primary-dark: #29b6f6;
            --success: #66bb6a;
            --danger: #ef5350;
            --shadow: 0 8px 32px rgba(31, 38, 135, 0.37);
        }
        
        * { box-sizing: border-box; margin: 0; padding: 0; }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            line-height: 1.6;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .logout-btn {
            background: var(--danger);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 25px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: var(--shadow);
        }
        
        .logout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 40px rgba(239, 83, 80, 0.4);
        }
        
        .grid {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 25px;
            margin-bottom: 30px;
        }
        
        .card {
            background: var(--card);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 25px;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
        }
        
        .card:hover {
            background: var(--card-hover);
            transform: translateY(-5px);
        }
        
        .card h3 {
            margin-bottom: 20px;
            font-size: 1.4rem;
            color: var(--primary);
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: var(--card);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border);
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-3px);
            background: var(--card-hover);
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary);
            display: block;
        }
        
        .stat-label {
            color: var(--text-muted);
            font-size: 0.9rem;
            margin-top: 5px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--text-muted);
        }
        
        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid var(--border);
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.1);
            color: var(--text);
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(79, 195, 247, 0.2);
            background: rgba(255, 255, 255, 0.15);
        }
        
        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }
        
        .btn {
            background: linear-gradient(45deg, var(--primary), var(--primary-dark));
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 25px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: var(--shadow);
            width: 100%;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 40px rgba(79, 195, 247, 0.4);
        }
        
        .shortlink-list {
            max-height: 400px;
            overflow-y: auto;
            margin-top: 20px;
        }
        
        .shortlink-item {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .shortlink-item:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateX(5px);
        }
        
        .shortlink-item.active {
            background: rgba(79, 195, 247, 0.2);
            border-color: var(--primary);
        }
        
        .shortlink-slug {
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 5px;
        }
        
        .shortlink-dest {
            font-size: 0.85rem;
            color: var(--text-muted);
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            margin-bottom: 5px;
        }
        
        .shortlink-clicks {
            font-size: 0.8rem;
            color: var(--success);
            font-weight: 600;
        }
        
        .chart-container {
            background: var(--card);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 25px;
            box-shadow: var(--shadow);
            margin-bottom: 30px;
        }
        
        .chart-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .chart-tab {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid var(--border);
            color: var(--text);
            padding: 8px 16px;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.85rem;
        }
        
        .chart-tab.active {
            background: var(--primary);
            border-color: var(--primary);
        }
        
        .analytics-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
        }
        
        .table-responsive {
            overflow-x: auto;
            margin-top: 20px;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.85rem;
        }
        
        .table th,
        .table td {
            padding: 12px 8px;
            text-align: left;
            border-bottom: 1px solid var(--border);
        }
        
        .table th {
            color: var(--primary);
            font-weight: 600;
            background: rgba(255, 255, 255, 0.05);
        }
        
        .table td {
            color: var(--text-muted);
        }
        
        .success-message {
            background: rgba(102, 187, 106, 0.2);
            border: 1px solid var(--success);
            color: var(--success);
            padding: 12px;
            border-radius: 10px;
            margin-top: 15px;
            display: none;
        }
        
        .error-message {
            background: rgba(239, 83, 80, 0.2);
            border: 1px solid var(--danger);
            color: var(--danger);
            padding: 12px;
            border-radius: 10px;
            margin-top: 15px;
            display: none;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.7rem;
            font-weight: 600;
        }
        
        .badge-success {
            background: rgba(102, 187, 106, 0.2);
            color: var(--success);
        }
        
        .badge-danger {
            background: rgba(239, 83, 80, 0.2);
            color: var(--danger);
        }
        
        /* Responsive Design */
        @media (max-width: 1024px) {
            .grid {
                grid-template-columns: 1fr;
            }
            
            .analytics-grid {
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
            
            .header h1 {
                font-size: 2rem;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .chart-tabs {
                justify-content: center;
            }
            
            .table {
                font-size: 0.75rem;
            }
            
            .table th,
            .table td {
                padding: 8px 4px;
            }
        }
        
        /* Scrollbar Styling */
        ::-webkit-scrollbar {
            width: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb {
            background: var(--primary);
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: var(--primary-dark);
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>📊 Dashboard Shortlink</h1>
            <form method="POST" action="{{ route('panel.logout') }}">
                @csrf
                <button type="submit" class="logout-btn">Logout</button>
            </form>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <span class="stat-number" id="totalLinks">0</span>
                <div class="stat-label">Total Shortlinks</div>
            </div>
            <div class="stat-card">
                <span class="stat-number" id="totalClicks">0</span>
                <div class="stat-label">Total Clicks</div>
            </div>
            <div class="stat-card">
                <span class="stat-number" id="todayClicks">0</span>
                <div class="stat-label">Clicks Hari Ini</div>
            </div>
            <div class="stat-card">
                <span class="stat-number" id="avgClicks">0</span>
                <div class="stat-label">Rata-rata per Link</div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="chart-container">
            <h3>📈 Analytics</h3>
            <div class="chart-tabs">
                <button class="chart-tab active" data-chart="clicks">Clicks Timeline</button>
                <button class="chart-tab" data-chart="top">Top Shortlinks</button>
                <button class="chart-tab" data-chart="countries">Negara</button>
                <button class="chart-tab" data-chart="devices">Device & Browser</button>
            </div>
            <div style="height: 400px; position: relative;">
                <canvas id="mainChart"></canvas>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid">
            <!-- Create Shortlink Form -->
            <div class="card">
                <h3>🔗 Buat Shortlink Baru</h3>
                <form id="createForm">
                    <div class="form-group">
                        <label for="destination">URL Tujuan</label>
                        <input type="text" id="destination" name="destination" class="form-control" 
                               placeholder="https://example.com/page" required>
                    </div>
                    <div class="form-group">
                        <label for="slug">Custom Slug (opsional)</label>
                        <input type="text" id="slug" name="slug" class="form-control" 
                               placeholder="my-custom-link" pattern="[a-zA-Z0-9_-]+">
                    </div>
                    <button type="submit" class="btn">Buat Shortlink</button>
                </form>
                
                <div id="successMessage" class="success-message"></div>
                <div id="errorMessage" class="error-message"></div>

                <!-- Shortlinks List -->
                <div class="shortlink-list" id="shortlinksList">
                    <h4 style="margin-bottom: 15px; color: var(--text-muted);">📋 Shortlinks Anda</h4>
                    <div id="linksContainer"></div>
                </div>
            </div>

            <!-- Analytics Details -->
            <div class="analytics-grid">
                <div class="card">
                    <h3>🎯 Link Terpilih: <span id="selectedSlug">-</span></h3>
                    <div class="stats-grid" style="grid-template-columns: 1fr 1fr; margin-bottom: 15px;">
                        <div class="stat-card">
                            <span class="stat-number" id="selectedClicks">0</span>
                            <div class="stat-label">Total Clicks</div>
                        </div>
                        <div class="stat-card">
                            <span class="stat-number" id="selectedToday">0</span>
                            <div class="stat-label">Hari Ini</div>
                        </div>
                    </div>
                    <div id="selectedUrl" style="font-size: 0.85rem; color: var(--text-muted); word-break: break-all;"></div>
                </div>

                <div class="card">
                    <h3>🌍 Recent Activity</h3>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Waktu</th>
                                    <th>Negara</th>
                                    <th>Device</th>
                                    <th>Browser</th>
                                    <th>Bot?</th>
                                </tr>
                            </thead>
                            <tbody id="recentActivity"></tbody>
                        </table>
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
        Chart.defaults.color = 'rgba(255, 255, 255, 0.8)';
        Chart.defaults.borderColor = 'rgba(255, 255, 255, 0.2)';

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
            initializeChart();
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
                    showSuccess(`Shortlink berhasil dibuat: <a href="${data.short_url}" target="_blank" style="color: var(--primary);">${data.short_url}</a>`);
                    createForm.reset();
                    // Force reload dashboard data after successful creation
                    setTimeout(() => {
                        loadDashboardData();
                    }, 500);
                } else {
                    showError(data.message || 'Gagal membuat shortlink');
                }
            } catch (error) {
                showError('Terjadi kesalahan: ' + error.message);
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
                console.log('Loading dashboard data...');
                
                // Load shortlinks list
                const linksResponse = await fetch('{{ route('panel.shortlinks.list') }}');
                const linksData = await linksResponse.json();
                
                console.log('Links data:', linksData);
                
                if (linksData.ok) {
                    allLinks = linksData.data;
                    renderShortlinksList();
                }

                // Load analytics data
                const analyticsResponse = await fetch('{{ route('panel.analytics') }}');
                const analyticsData = await analyticsResponse.json();
                
                console.log('Analytics data:', analyticsData);
                
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
            console.log('Rendering shortlinks list:', allLinks);
            linksContainer.innerHTML = '';
            
            if (!allLinks || allLinks.length === 0) {
                linksContainer.innerHTML = '<div style="text-align:center; color:var(--text-muted); padding:20px;">Belum ada shortlink</div>';
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
                    chartConfig = getDevicesChart(data.device_stats, data.browser_stats);
                    break;
            }

            currentChart = new Chart(mainChart, chartConfig);
        }

        // Clicks timeline chart
        function getClicksTimelineChart(timelineData) {
            const labels = timelineData.map(item => item.date);
            const data = timelineData.map(item => item.clicks);

            return {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Total Clicks',
                        data: data,
                        borderColor: '#4fc3f7',
                        backgroundColor: 'rgba(79, 195, 247, 0.1)',
                        tension: 0.4,
                        fill: true
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
                            }
                        },
                        x: {
                            grid: {
                                color: 'rgba(255, 255, 255, 0.1)'
                            }
                        }
                    }
                }
            };
        }

        // Top shortlinks chart
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
                        backgroundColor: 'rgba(79, 195, 247, 0.8)',
                        borderColor: '#4fc3f7',
                        borderWidth: 1
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
                            }
                        },
                        x: {
                            grid: {
                                color: 'rgba(255, 255, 255, 0.1)'
                            }
                        }
                    }
                }
            };
        }

        // Countries chart
        function getCountriesChart(countriesData) {
            const labels = countriesData.map(item => item.country || 'Unknown');
            const data = countriesData.map(item => item.count);
            const colors = [
                '#4fc3f7', '#66bb6a', '#ffca28', '#ef5350', '#9c27b0',
                '#ff7043', '#26a69a', '#ab47bc', '#42a5f5', '#8bc34a'
            ];

            return {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: data,
                        backgroundColor: colors.slice(0, data.length)
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            };
        }

        // Devices chart
        function getDevicesChart(deviceData, browserData) {
            const deviceLabels = deviceData.map(item => item.device || 'Unknown');
            const deviceCounts = deviceData.map(item => item.count);
            
            return {
                type: 'bar',
                data: {
                    labels: deviceLabels,
                    datasets: [{
                        label: 'Devices',
                        data: deviceCounts,
                        backgroundColor: 'rgba(79, 195, 247, 0.8)',
                        borderColor: '#4fc3f7',
                        borderWidth: 1
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
                            }
                        },
                        x: {
                            grid: {
                                color: 'rgba(255, 255, 255, 0.1)'
                            }
                        }
                    }
                }
            };
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
