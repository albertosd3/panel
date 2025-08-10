<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login Panel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial; background:#0f172a; color:#e2e8f0; display:grid; place-items:center; min-height:100vh; margin:0; }
        .card { background:#111827; padding:28px; border-radius:14px; width:min(420px,90vw); box-shadow:0 10px 30px rgba(0,0,0,.35); }
        h1 { margin:0 0 16px; font-size:22px; font-weight:700; }
        label { display:block; margin:10px 0 6px; font-size:14px; color:#cbd5e1; }
        input { width:100%; padding:12px 14px; font-size:18px; border-radius:10px; border:1px solid #374151; background:#0b1220; color:#e5e7eb; outline:none; }
        input:focus { border-color:#60a5fa; box-shadow:0 0 0 4px rgba(96,165,250,.15); }
        .btn { width:100%; margin-top:16px; padding:12px 16px; border-radius:10px; border:0; background:#2563eb; color:white; font-weight:600; font-size:16px; cursor:pointer; }
        .btn:hover { background:#1d4ed8; }
        .error { color:#fda4af; margin:8px 0 0; font-size:14px; }
    </style>
</head>
<body>
<div class="card">
    <h1>Masuk Panel</h1>

    @if ($errors->any())
        <div class="error">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('panel.verify') }}">
        @csrf
        <label for="pin">PIN (6 digit):</label>
        <input id="pin"
               name="pin"
               type="password"
               inputmode="numeric"
               pattern="[0-9]*"
               minlength="6"
               maxlength="6"
               autocomplete="one-time-code"
               required
               value="{{ old('pin') }}">
        <button class="btn" type="submit">Masuk</button>
    </form>
</div>
</body>
</html>
