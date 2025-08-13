<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Professional Shortlink Panel')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            /* Terminal Green Theme */
            --color-primary: #22c55e;        /* emerald */
            --color-primary-dark: #16a34a;
            --color-secondary: #94a3b8;     /* neutral for secondary */
            --color-accent: #84cc16;        /* lime accent */
            --color-success: #22c55e;
            --color-warning: #f59e0b;
            --color-danger: #ef4444;

            /* Backgrounds */
            --color-bg-primary: #0a0f0a;     /* near-black green */
            --color-bg-secondary: #0e1510;
            --color-bg-tertiary: #122016;
            --color-surface: #0f1a12;
            --color-surface-hover: #112317;

            /* Text */
            --color-text-primary: #e2f7ea;   /* soft greenish white */
            --color-text-secondary: #b8e6c9;
            --color-text-muted: #7fb48e;
            --color-text-inverse: #061107;

            /* Borders */
            --color-border: #173624;
            --color-border-light: #1f472f;

            --font-primary: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            --font-mono: 'JetBrains Mono', 'SF Mono', 'Monaco', 'Consolas', 'Roboto Mono', monospace;

            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.4);
            --shadow: 0 1px 3px 0 rgb(0 0 0 / 0.5), 0 1px 2px -1px rgb(0 0 0 / 0.5);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.55), 0 2px 4px -2px rgb(0 0 0 / 0.55);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.6), 0 4px 6px -4px rgb(0 0 0 / 0.6);
            --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.65), 0 8px 10px -6px rgb(0 0 0 / 0.65);
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: var(--font-primary);
            background-color: var(--color-bg-primary);
            color: var(--color-text-primary);
            line-height: 1.6;
            min-height: 100vh;
            font-size: 14px;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            /* subtle terminal grid */
            background-image:
                linear-gradient(rgba(34,197,94,0.04), rgba(34,197,94,0.04)),
                repeating-linear-gradient(0deg, rgba(34,197,94,0.06) 0, rgba(34,197,94,0.06) 1px, transparent 1px, transparent 24px),
                repeating-linear-gradient(90deg, rgba(34,197,94,0.05) 0, rgba(34,197,94,0.05) 1px, transparent 1px, transparent 24px);
            background-blend-mode: screen, normal, normal;
        }

        /* Container */
        .envelope-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        /* Card/Surface */
        .paper, .card, .content-card, .dashboard-header {
            background: var(--color-surface);
            border-radius: 12px;
            box-shadow: var(--shadow-xl);
            border: 1px solid var(--color-border);
            position: relative;
            overflow: hidden;
        }

        .paper::before, .card::before, .content-card::before, .dashboard-header::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--color-primary) 0%, var(--color-accent) 100%);
            box-shadow: 0 0 16px rgba(34,197,94,0.25);
        }

        /* Headings */
        .heading-primary { font-family: var(--font-mono); font-size: 28px; font-weight: 700; letter-spacing: -0.01em; }
        .heading-secondary { font-family: var(--font-mono); font-size: 20px; font-weight: 700; }
        .text-muted { color: var(--color-text-muted); font-size: 13px; }
        .text-small { font-size: 12px; }

        /* Buttons */
        .btn { display: inline-flex; align-items: center; justify-content: center; padding: 10px 18px; border: 1px solid transparent; border-radius: 8px; font-weight: 600; font-size: 14px; cursor: pointer; transition: all .2s ease; text-decoration: none; font-family: var(--font-mono); min-height: 40px; }
        .btn-primary { background: linear-gradient(135deg, #1fb157, #22c55e); color: #061107; border-color: #1c7a43; box-shadow: 0 8px 18px rgba(34,197,94,.18), inset 0 1px 0 rgba(255,255,255,.04); }
        .btn-primary:hover { background: linear-gradient(135deg, #1a9b4c, #1fb157); transform: translateY(-1px); box-shadow: 0 12px 28px rgba(34,197,94,.28); }
        .btn-secondary { background: #1b2a20; color: var(--color-text-primary); border-color: var(--color-border); }
        .btn-secondary:hover { background: #1f3226; }
        .btn-danger { background: linear-gradient(135deg, #ef4444, #b91c1c); color: #fff; border-color: #7f1d1d; box-shadow: 0 8px 18px rgba(239,68,68,.18); }
        .btn-danger:hover { background: linear-gradient(135deg, #dc2626, #991b1b); transform: translateY(-1px); }
        .btn-outline { background: transparent; color: var(--color-primary); border: 1px solid var(--color-primary); }
        .btn-outline:hover { background: var(--color-primary); color: #061107; }
        .btn-full { width: 100%; }

        /* Form Elements */
        .form-group { margin-bottom: 16px; }
        .form-label { display: block; font-weight: 600; color: var(--color-text-secondary); margin-bottom: 6px; font-size: 12px; letter-spacing: .06em; text-transform: uppercase; }
        .form-control { width: 100%; padding: 12px 14px; border: 1px solid var(--color-border); border-radius: 8px; font-size: 14px; background: var(--color-bg-secondary); color: var(--color-text-primary); transition: all .2s ease; font-family: var(--font-mono); }
        .form-control:focus { outline: none; border-color: var(--color-primary); box-shadow: 0 0 0 3px rgba(34,197,94,.25), 0 0 12px rgba(34,197,94,.25); background: var(--color-bg-tertiary); }
        .form-control::placeholder { color: var(--color-text-muted); }

        /* Tables */
        .table { width: 100%; border-collapse: collapse; }
        .table th, .table td { padding: 12px 14px; border-bottom: 1px solid var(--color-border); color: var(--color-text-primary); }
        .table thead th { background: #102116; font-size: 12px; text-transform: uppercase; letter-spacing: .06em; color: var(--color-text-secondary); }
        .table tbody tr:hover { background: var(--color-surface-hover); }

        /* Badges */
        .badge { display: inline-block; padding: 4px 8px; border-radius: 999px; font-size: 11px; font-weight: 700; font-family: var(--font-mono); }
        .badge.bg-primary { background: var(--color-primary); color: #061107; }
        .badge.bg-success { background: #16a34a; color: #031007; }
        .badge.bg-secondary { background: #1b2a20; color: var(--color-text-secondary); }
        .badge.bg-info { background: #22d3ee; color: #052028; }

        /* Alerts */
        .alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; border: 1px solid transparent; font-family: var(--font-mono); }
        .alert-error { background: rgba(239,68,68,.08); border-color: rgba(239,68,68,.35); color: #fecaca; }
        .alert-success { background: rgba(34,197,94,.08); border-color: rgba(34,197,94,.35); color: #bbf7d0; }
        .alert-warning { background: rgba(245,158,11,.08); border-color: rgba(245,158,11,.35); color: #fde68a; }

        /* Utilities */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .mt-4 { margin-top: 16px; } .mb-4 { margin-bottom: 16px; }
        .mr-2 { margin-right: 8px; } .ml-2 { margin-left: 8px; }
        .p-4 { padding: 16px; } .px-4 { padding-left: 16px; padding-right: 16px; } .py-4 { padding-top: 16px; padding-bottom: 16px; }

        /* Responsive */
        @media (max-width: 768px) { .envelope-container { padding: 12px; } .heading-primary { font-size: 24px; } .heading-secondary { font-size: 18px; } }
        @media (max-width: 480px) { .btn { padding: 12px 16px; font-size: 13px; } }
    </style>
    @stack('styles')
</head>
<body>
    @yield('content')
    @stack('scripts')
</body>
</html>
