@extends('layouts.app')

@section('title', 'Edit Channel')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('channels.index') }}">Kelola Channel</a></li>
    <li class="breadcrumb-item active">Edit Channel</li>
@endsection

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <h4 class="fw-bold mb-1">Edit Channel</h4>
                    <p class="text-muted small mb-0">Perbarui platform atau nama akun channel Anda.</p>
                </div>
                <a href="{{ route('channels.index') }}" class="btn btn-outline-secondary btn-sm rounded-3">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </a>
            </div>

            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('channels.update', $channel) }}" novalidate>
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="platform" class="form-label small fw-semibold">Platform Media Sosial <span class="text-danger">*</span></label>
                            <select class="form-select @error('platform') is-invalid @enderror" id="platform" name="platform" required>
                                <option value="youtube" {{ old('platform', $channel->platform) === 'youtube' ? 'selected' : '' }}>YouTube</option>
                                <option value="tiktok" {{ old('platform', $channel->platform) === 'tiktok' ? 'selected' : '' }}>TikTok</option>
                                <option value="instagram" {{ old('platform', $channel->platform) === 'instagram' ? 'selected' : '' }}>Instagram</option>
                                <option value="linkedin" {{ old('platform', $channel->platform) === 'linkedin' ? 'selected' : '' }}>LinkedIn</option>
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
                                       value="{{ old('handle_name', $channel->handle_name) }}"
                                       maxlength="100"
                                       required>
                                @error('handle_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                            <a href="{{ route('channels.index') }}" class="btn btn-light rounded-3 px-3">Batal</a>
                            <button type="submit" class="btn btn-primary rounded-3 px-4">
                                <i class="bi bi-check-lg me-1"></i> Perbarui Channel
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Danger Zone: Deletion -->
            <div class="card border-danger-subtle shadow-sm rounded-4">
                <div class="card-header bg-danger-subtle text-danger py-3 border-0">
                    <h6 class="fw-bold mb-0"><i class="bi bi-exclamation-triangle me-2"></i>Zona Bahaya</h6>
                </div>
                <div class="card-body p-4">
                    @php $contentCount = $channel->contents()->count(); @endphp
                    @if ($contentCount > 0)
                        <div class="alert alert-warning mb-3 small d-flex align-items-center">
                            <i class="bi bi-exclamation-triangle-fill me-2 fs-5 text-warning"></i>
                            <div>
                                Channel ini memiliki <strong>{{ $contentCount }} konten</strong> di dalamnya. Jika channel ini dihapus, <strong>seluruh {{ $contentCount }} konten terkait (termasuk naskah, checklist, dan riwayat metrik) akan ikut terhapus secara permanen</strong>.
                            </div>
                        </div>
                    @else
                        <p class="small text-muted mb-3">Menghapus channel ini tidak dapat dibatalkan. Pastikan channel tidak lagi diperlukan.</p>
                    @endif

                    <form method="POST" action="{{ route('channels.destroy', $channel) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus channel {{ $channel->handle_name }} {{ $contentCount > 0 ? "beserta seluruh {$contentCount} konten di dalamnya" : "" }}? Tindakan ini permanen dan tidak dapat dibatalkan!');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm rounded-3">
                            <i class="bi bi-trash me-1"></i> Hapus Channel {{ $contentCount > 0 ? "Beserta Kontennya" : "Ini" }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
