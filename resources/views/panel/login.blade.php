<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login Panel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        :root{ --bg:#0b1020; --card:#0e1726; --muted:#8aa0b3; --text:#e6eef6; --primary:#4f8cff; --primary2:#3b82f6; }
        *{ box-sizing:border-box }
        body { font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial; background:radial-gradient(1200px 600px at 10% -10%, #1b2540 0%, transparent 60%), var(--bg); color:var(--text); display:grid; place-items:center; min-height:100vh; margin:0; }
        .card { background:linear-gradient(180deg, rgba(255,255,255,.02), rgba(255,255,255,.005)); border:1px solid rgba(255,255,255,.06); backdrop-filter: blur(8px); padding:28px; border-radius:16px; width:min(420px,92vw); box-shadow:0 20px 60px rgba(0,0,0,.45); }
        h1 { margin:0 0 8px; font-size:24px; font-weight:800; letter-spacing:.2px; }
        p.sub{ margin:0 0 18px; color:var(--muted); font-size:14px; }
        label { display:block; margin:10px 0 8px; font-size:14px; color:#cbd5e1; }
        input { width:100%; padding:14px 14px; font-size:18px; border-radius:12px; border:1px solid rgba(255,255,255,.08); background:rgba(5,10,22,.8); color:#e5e7eb; outline:none; transition:.15s border, .15s box-shadow; }
        input:focus { border-color:var(--primary); box-shadow:0 0 0 6px rgba(79,140,255,.12); }
        .btn { width:100%; margin-top:16px; padding:12px 16px; border-radius:12px; border:0; background:linear-gradient(90deg, var(--primary), var(--primary2)); color:white; font-weight:700; font-size:16px; cursor:pointer; box-shadow:0 8px 24px rgba(63,131,248,.25); }
        .btn:hover { filter:brightness(1.05); }
        .error { color:#fda4af; margin:8px 0 0; font-size:14px; }
        footer{ margin-top:14px; color:var(--muted); font-size:12px; text-align:center }
    </style>
</head>
<body>
<div class="card">
    <h1>Masuk Panel</h1>
    <p class="sub">Gunakan PIN 6 digit untuk mengakses panel.</p>

    @if ($errors->any())
        <div class="error">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('panel.verify') }}">
        @csrf
        <label for="pin">PIN (6 digit)</label>
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
    <footer>Keamanan PIN tersimpan di server (.env)</footer>
</div>
</body>
</html>
