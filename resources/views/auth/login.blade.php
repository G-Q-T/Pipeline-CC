@extends('layouts.auth')

@section('title', 'Masuk')

@section('content')
    <h4 class="fw-bold mb-1">Masuk ke Akun</h4>
    <p class="text-muted small mb-4">Kelola siklus produksi konten Anda dengan mudah.</p>

    <form method="POST" action="{{ route('login') }}" novalidate>
        @csrf

        <div class="mb-3">
            <label for="email" class="form-label small fw-semibold">Alamat Email</label>
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-envelope"></i></span>
                <input type="email"
                       class="form-control border-start-0 ps-0 @error('email') is-invalid @enderror"
                       id="email"
                       name="email"
                       value="{{ old('email') }}"
                       placeholder="nama@email.com"
                       required
                       autofocus>
                @error('email')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label small fw-semibold">Kata Sandi</label>
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-lock"></i></span>
                <input type="password"
                       class="form-control border-start-0 ps-0 @error('password') is-invalid @enderror"
                       id="password"
                       name="password"
                       placeholder="••••••••"
                       required>
                @error('password')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                <label class="form-check-label small text-muted" for="remember">
                    Ingat saya
                </label>
            </div>
        </div>

        <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold rounded-3 mb-3 shadow-sm">
            <i class="bi bi-box-arrow-in-right me-1"></i> Masuk Sekarang
        </button>

        <div class="text-center pt-2 border-top">
            <span class="small text-muted">Belum punya akun?</span>
            <a href="{{ route('register') }}" class="small fw-semibold text-decoration-none ms-1">Registrasi kuy</a>
        </div>
    </form>
@endsection
