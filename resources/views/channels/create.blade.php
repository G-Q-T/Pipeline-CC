@extends('layouts.app')

@section('title', 'Tambah Channel Baru')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('channels.index') }}">Kelola Channel</a></li>
    <li class="breadcrumb-item active">Tambah Channel</li>
@endsection

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <h4 class="fw-bold mb-1">Tambah Channel Baru</h4>
                    <p class="text-muted small mb-0">Hubungkan channel atau akun media sosial untuk distribusi konten.</p>
                </div>
                <a href="{{ route('channels.index') }}" class="btn btn-outline-secondary btn-sm rounded-3">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </a>
            </div>

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('channels.store') }}" novalidate>
                        @csrf

                        <div class="mb-3">
                            <label for="platform" class="form-label small fw-semibold">Platform Media Sosial <span class="text-danger">*</span></label>
                            <select class="form-select @error('platform') is-invalid @enderror" id="platform" name="platform" required>
                                <option value="" disabled {{ old('platform') ? '' : 'selected' }}>Pilih Platform</option>
                                <option value="youtube" {{ old('platform') === 'youtube' ? 'selected' : '' }}>YouTube</option>
                                <option value="tiktok" {{ old('platform') === 'tiktok' ? 'selected' : '' }}>TikTok</option>
                                <option value="instagram" {{ old('platform') === 'instagram' ? 'selected' : '' }}>Instagram</option>
                                <option value="linkedin" {{ old('platform') === 'linkedin' ? 'selected' : '' }}>LinkedIn</option>
                            </select>
                            @error('platform')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="handle_name" class="form-label small fw-semibold">Handle / Nama Akun <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted">@</span>
                                <input type="text"
                                       class="form-control @error('handle_name') is-invalid @enderror"
                                       id="handle_name"
                                       name="handle_name"
                                       value="{{ old('handle_name') }}"
                                       placeholder="contoh: nama_creator_id"
                                       maxlength="100"
                                       required>
                                @error('handle_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-text small">Maksimal 100 karakter.</div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                            <a href="{{ route('channels.index') }}" class="btn btn-light rounded-3 px-3">Batal</a>
                            <button type="submit" class="btn btn-primary rounded-3 px-4">
                                <i class="bi bi-check-lg me-1"></i> Simpan Channel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
