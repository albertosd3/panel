<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Professional Shortlink Panel')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Crimson+Text:ital,wght@0,400;0,600;1,400&family=JetBrains+Mono:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            /* Dark Theme Colors */
            --color-primary: #3b82f6;
            --color-primary-dark: #2563eb;
            --color-secondary: #94a3b8;
            --color-accent: #f59e0b;
            --color-success: #10b981;
            --color-warning: #f59e0b;
            --color-danger: #ef4444;
            
            /* Cyberpunk neon accents */
            --neon-blue: #3b82f6;
            --neon-pink: #ec4899;
            --neon-green: #22d3ee;
            --neon-amber: #f59e0b;
            
            /* Dark Background Colors */
            --color-bg-primary: #0f172a;
            --color-bg-secondary: #1e293b;
            --color-bg-tertiary: #334155;
            --color-surface: #1e293b;
            --color-surface-hover: #334155;
            
            /* Dark Text Colors */
            --color-text-primary: #f1f5f9;
            --color-text-secondary: #cbd5e1;
            --color-text-muted: #94a3b8;
            --color-text-inverse: #0f172a;
            
            /* Border Colors */
            --color-border: #334155;
            --color-border-light: #475569;
            
            --font-primary: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            --font-serif: 'Crimson Text', 'Times New Roman', serif;
            --font-mono: 'JetBrains Mono', 'SF Mono', 'Monaco', 'Consolas', 'Roboto Mono', monospace;
            
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.3);
            --shadow: 0 1px 3px 0 rgb(0 0 0 / 0.4), 0 1px 2px -1px rgb(0 0 0 / 0.4);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.4), 0 2px 4px -2px rgb(0 0 0 / 0.4);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.5), 0 4px 6px -4px rgb(0 0 0 / 0.5);
            --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.6), 0 8px 10px -6px rgb(0 0 0 / 0.6);
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: var(--font-primary);
            background: linear-gradient(135deg, #0f172a 0%, #111827 35%, #1e293b 65%, #0b1221 100%);
            color: var(--color-text-primary);
            line-height: 1.6;
            min-height: 100vh;
            font-size: 14px;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            /* subtle cyber grid */
            background-image:
                radial-gradient(600px 400px at 20% 10%, rgba(59,130,246,0.10), transparent 60%),
                radial-gradient(500px 300px at 80% 90%, rgba(236,72,153,0.08), transparent 60%),
                linear-gradient(rgba(255,255,255,0.02), rgba(255,255,255,0.02)),
                repeating-linear-gradient(90deg, rgba(148,163,184,0.06) 0, rgba(148,163,184,0.06) 1px, transparent 1px, transparent 24px),
                repeating-linear-gradient(0deg, rgba(148,163,184,0.04) 0, rgba(148,163,184,0.04) 1px, transparent 1px, transparent 24px);
            background-blend-mode: screen, screen, normal, normal, normal;
        }
        
        /* Paper/Envelope Base - Dark Theme */
        .envelope-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background-image: 
                radial-gradient(circle at 25% 25%, rgba(59, 130, 246, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 75% 75%, rgba(245, 158, 11, 0.1) 0%, transparent 50%);
        }
        
        .paper {
            background: var(--color-surface);
            border-radius: 12px;
            box-shadow: var(--shadow-xl);
            overflow: hidden;
            position: relative;
            border: 1px solid var(--color-border);
        }
        
        .paper::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--neon-blue) 0%, var(--neon-pink) 50%, var(--neon-amber) 100%);
            box-shadow: 0 0 18px rgba(59,130,246,0.35);
        }
        
        /* Typography - Dark Theme */
        .heading-primary {
            font-family: var(--font-serif);
            font-size: 28px;
            font-weight: 600;
            color: var(--color-text-primary);
            margin-bottom: 8px;
            letter-spacing: -0.025em;
            text-shadow: 0 1px 0 rgba(0,0,0,0.4);
        }
        
        .heading-secondary {
            font-family: var(--font-serif);
            font-size: 20px;
            font-weight: 600;
            color: var(--color-text-primary);
            margin-bottom: 16px;
        }
        
        .text-muted {
            color: var(--color-text-muted);
            font-size: 13px;
        }
        
        .text-small {
            font-size: 12px;
        }
        
        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-weight: 500;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            font-family: inherit;
            min-height: 40px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--neon-blue), var(--neon-amber));
            color: white;
            box-shadow: 0 6px 14px rgba(59,130,246,0.25), inset 0 1px 0 rgba(255,255,255,0.06);
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, var(--color-primary-dark), var(--neon-amber));
            transform: translateY(-1px);
            box-shadow: 0 10px 20px rgba(59,130,246,0.35), 0 0 18px rgba(59,130,246,0.3);
        }
        
        .btn-secondary {
            background: var(--color-secondary);
            color: white;
        }
        
        .btn-secondary:hover {
            background: #475569;
            transform: translateY(-1px);
        }
        
        .btn-danger {
            background: linear-gradient(135deg, #ef4444, #b91c1c);
            color: white;
            box-shadow: 0 6px 14px rgba(239,68,68,0.2);
        }
        
        .btn-danger:hover {
            background: linear-gradient(135deg, #dc2626, #991b1b);
            transform: translateY(-1px);
            box-shadow: 0 10px 20px rgba(239,68,68,0.3);
        }
        
        .btn-outline {
            background: transparent;
            border: 1px solid var(--color-primary);
            color: var(--color-primary);
        }
        
        .btn-outline:hover {
            background: var(--color-primary);
            color: white;
        }
        
        .btn-full {
            width: 100%;
        }
        
        /* Form Elements */
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            display: block;
            font-weight: 500;
            color: var(--color-text-secondary);
            margin-bottom: 6px;
            font-size: 13px;
            letter-spacing: 0.025em;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid var(--color-border);
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
            background: var(--color-bg-secondary);
            color: var(--color-text-primary);
            transition: all 0.2s ease;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--color-primary);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.25), 0 0 12px rgba(59,130,246,0.25);
            background: var(--color-bg-tertiary);
        }
        
        .form-control::placeholder {
            color: var(--color-text-muted);
        }
        
        /* Cards - Dark Theme */
        .card {
            background: var(--color-surface);
            border-radius: 12px;
            border: 1px solid var(--color-border);
            box-shadow: var(--shadow);
            overflow: hidden;
        }
        
        .card-header {
            padding: 20px 24px;
            border-bottom: 1px solid var(--color-border);
            background: var(--color-bg-tertiary);
        }
        
        .card-body {
            padding: 24px;
        }
        
        .card-title {
            font-family: var(--font-serif);
            font-size: 18px;
            font-weight: 600;
            color: var(--color-text-primary);
            margin-bottom: 4px;
        }
        
        .card-subtitle {
            color: var(--color-text-muted);
            font-size: 13px;
        }
        
        /* Alerts */
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid transparent;
        }
        
        .alert-error {
            background: rgba(239,68,68,0.08);
            border-color: rgba(239,68,68,0.35);
            color: #fecaca;
        }
        
        .alert-success {
            background: rgba(16,185,129,0.08);
            border-color: rgba(16,185,129,0.35);
            color: #bbf7d0;
        }
        
        .alert-warning {
            background: rgba(245,158,11,0.08);
            border-color: rgba(245,158,11,0.35);
            color: #fde68a;
        }
        
        /* Tables (generic dark style) */
        .table { width: 100%; border-collapse: collapse; }
        .table th, .table td { padding: 12px 14px; border-bottom: 1px solid var(--color-border); color: var(--color-text-primary); }
        .table thead th { background: var(--color-bg-tertiary); font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; }
        .table tbody tr:hover { background: var(--color-surface-hover); }
        
        /* Badges */
        .badge { display: inline-block; padding: 4px 8px; border-radius: 999px; font-size: 11px; font-weight: 700; }
        .badge.bg-primary { background: var(--color-primary); color: #fff; }
        .badge.bg-success { background: var(--color-success); color: #052e1b; }
        .badge.bg-secondary { background: #64748b; color: #0b1221; }
        .badge.bg-info { background: #38bdf8; color: #06202a; }
        
        /* Utilities */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .mt-4 { margin-top: 16px; }
        .mb-4 { margin-bottom: 16px; }
        .mr-2 { margin-right: 8px; }
        .ml-2 { margin-left: 8px; }
        .p-4 { padding: 16px; }
        .px-4 { padding-left: 16px; padding-right: 16px; }
        .py-4 { padding-top: 16px; padding-bottom: 16px; }
        
        /* Responsive */
        @media (max-width: 768px) {
            .envelope-container { padding: 12px; }
            .heading-primary { font-size: 24px; }
            .heading-secondary { font-size: 18px; }
            .card-body { padding: 20px; }
        }
        
        @media (max-width: 480px) {
            .card-body { padding: 16px; }
            .btn { padding: 12px 16px; font-size: 13px; }
        }
    </style>
    @stack('styles')
</head>
<body>
    @yield('content')
    @stack('scripts')
</body>
</html>
