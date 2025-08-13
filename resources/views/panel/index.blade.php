<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Panel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="{{ mix('css/app.css') }}">
    <style>
        .envelope-container { display: flex; justify-content: center; align-items: center; min-height: 100vh; background-color: #0f172a; }
        .paper { background: #111827; padding: 24px; border-radius: 12px; width: min(720px, 92vw); box-shadow: 0 10px 30px rgba(0, 0, 0, .35); }
        .heading-primary { margin-bottom: 12px; font-size: 24px; font-weight: 700; color: #e2e8f0; }
        .text-muted { margin-bottom: 16px; color: #94a3b8; }
        .btn { display: inline-block; padding: 10px 14px; border-radius: 10px; border: 0; font-weight: 600; font-size: 14px; text-decoration: none; cursor: pointer; }
        .btn-primary { background: #2563eb; color: white; }
        .btn-primary:hover { background: #1d4ed8; }
        .btn-danger { background: #ef4444; color: white; }
        .btn-danger:hover { background: #dc2626; }
    </style>
</head>
<body>
@extends('layouts.envelope')

@section('title', 'Panel')

@section('content')
<div class="envelope-container">
    <div class="paper">
        <h1 class="heading-primary">Selamat datang di Panel</h1>
        <p class="text-muted">Kelola shortlink Anda dengan tema terminal hijau.</p>
        <p>
            <a class="btn btn-primary" href="{{ route('panel.shortlinks') }}">$ open shortlinks</a>
        </p>
        <form method="POST" action="{{ route('panel.logout') }}" class="mt-4">
            @csrf
            <button class="btn btn-danger" type="submit">$ logout</button>
        </form>
    </div>
</div>
@endsection
</body>
</html>
