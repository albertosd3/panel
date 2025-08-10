<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Panel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial; background:#0f172a; color:#e2e8f0; display:grid; place-items:center; min-height:100vh; margin:0; }
        .card { background:#111827; padding:28px; border-radius:14px; width:min(720px,92vw); box-shadow:0 10px 30px rgba(0,0,0,.35); }
        h1 { margin:0 0 16px; font-size:22px; font-weight:700; }
        a.btn { display:inline-block; padding:10px 14px; border-radius:10px; background:#2563eb; color:white; font-weight:600; font-size:14px; text-decoration:none; }
        form { margin-top:16px; }
        .btn { padding:10px 14px; border-radius:10px; border:0; background:#ef4444; color:white; font-weight:600; font-size:14px; cursor:pointer; }
        .btn:hover { background:#dc2626; }
    </style>
</head>
<body>
<div class="card">
    <h1>Selamat datang di Panel</h1>
    <p><a class="btn" href="{{ route('panel.shortlinks') }}">Kelola Shortlink</a></p>

    <form method="POST" action="{{ route('panel.logout') }}">
        @csrf
        <button class="btn" type="submit">Logout</button>
    </form>
</div>
</body>
</html>
