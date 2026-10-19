@extends('layouts.auth')

@section('title', 'Daftar Akun')

@section('content')
    <h4 class="fw-bold mb-1">Daftar Akun Baru</h4>
    <p class="text-muted small mb-4">Pilih peran akun Anda dan mulai kelola alur produksi konten.</p>

    <form method="POST" action="{{ route('register') }}" novalidate>
        @csrf

        <div class="mb-3">
            <label for="name" class="form-label small fw-semibold">Nama Lengkap</label>
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-person"></i></span>
                <input type="text"
                       class="form-control border-start-0 ps-0 @error('name') is-invalid @enderror"
                       id="name"
                       name="name"
                       value="{{ old('name') }}"
                       placeholder="Contoh: Budi Santoso"
                       required
                       autofocus>
                @error('name')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
        </div>

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
                       required>
                @error('email')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="mb-3">
            <label for="role" class="form-label small fw-semibold">Daftar Sebagai Peran <span class="text-danger">*</span></label>
            <select class="form-select @error('role') is-invalid @enderror" id="role" name="role" required>
                <option value="creator" {{ old('role', 'creator') === 'creator' ? 'selected' : '' }}>Creator (Pemilik Konten & Channel)</option>
                <option value="editor" {{ old('role') === 'editor' ? 'selected' : '' }}>Editor (Penyunting Naskah & Produksi)</option>
            </select>
            @error('role')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
            <div class="form-text small text-muted">Creator mengelola channel & konten miliknya. Editor mengerjakan tugas yang didelegasikan creator.</div>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label small fw-semibold">Kata Sandi</label>
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-lock"></i></span>
                <input type="password"
                       class="form-control border-start-0 ps-0 @error('password') is-invalid @enderror"
                       id="password"
                       name="password"
                       placeholder="Minimal 8 karakter"
                       required>
                @error('password')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="mb-4">
            <label for="password_confirmation" class="form-label small fw-semibold">Konfirmasi Kata Sandi</label>
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-shield-check"></i></span>
                <input type="password"
                       class="form-control border-start-0 ps-0"
                       id="password_confirmation"
                       name="password_confirmation"
                       placeholder="Ketik ulang kata sandi"
                       required>
            </div>
        </div>

        <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold rounded-3 mb-3 shadow-sm">
            <i class="bi bi-person-plus me-1"></i> Daftar Sekarang
        </button>

        <div class="text-center pt-2 border-top">
            <span class="small text-muted">Sudah punya akun?</span>
            <a href="{{ route('login') }}" class="small fw-semibold text-decoration-none ms-1">Masuk sekarang</a>
        </div>
    </form>
@endsection
