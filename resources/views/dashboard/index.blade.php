<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — Pipeline CC</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark px-4 py-3">
        <a class="navbar-brand fw-bold" href="{{ route('dashboard') }}">
            <i class="bi bi-camera-reels me-2 text-primary"></i>Pipeline CC
        </a>
        <div class="ms-auto d-flex align-items-center gap-3">
            <span class="text-white small">
                {{ auth()->user()->name }} 
                <span class="badge bg-primary ms-1">{{ strtoupper(auth()->user()->role) }}</span>
            </span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-box-arrow-right me-1"></i>Logout
                </button>
            </form>
        </div>
    </nav>
    <div class="container py-5">
        <div class="card border-0 shadow-sm p-4">
            <h4 class="fw-bold mb-2">Selamat Datang, {{ auth()->user()->name }}!</h4>
            <p class="text-muted">Role akun Anda: <span class="badge bg-primary">{{ auth()->user()->role }}</span></p>
            <div class="alert alert-success">
                <i class="bi bi-check-circle-fill me-2"></i> Sistem autentikasi dan database berhasil terintegrasi.
            </div>
        </div>
    </div>
</body>
</html>
