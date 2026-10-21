@extends('layouts.app')

@section('title', auth()->user()->isAdmin() ? 'Detail & Hapus Channel' : 'Edit Channel')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('channels.index') }}">Kelola Channel</a></li>
    <li class="breadcrumb-item active">{{ auth()->user()->isAdmin() ? 'Detail & Hapus Channel' : 'Edit Channel' }}</li>
@endsection

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <h4 class="fw-bold mb-1">{{ auth()->user()->isAdmin() ? 'Detail Channel' : 'Edit Channel' }}</h4>
                    <p class="text-muted small mb-0">
                        {{ auth()->user()->isAdmin() ? 'Informasi kepemilikan channel creator dan kontrol penghapusan.' : 'Perbarui platform atau nama akun channel Anda.' }}
                    </p>
                </div>
                <a href="{{ route('channels.index') }}" class="btn btn-outline-secondary btn-sm rounded-3">
                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
                </a>
            </div>

            @if(auth()->user()->isAdmin())
                {{-- Tampilan Khusus Administrator: Form Menampilkan Informasi Kepemilikan Creator & Channel --}}
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white py-3 border-0">
                        <div class="d-flex align-items-center justify-content-between">
                            <h6 class="fw-bold mb-0 text-dark">
                                <i class="bi bi-info-circle text-primary me-2"></i>Informasi & Kepemilikan Channel
                            </h6>
                            <span class="badge bg-secondary-subtle text-secondary px-2 py-1">
                                ID Channel #{{ $channel->id }}
                            </span>
                        </div>
                    </div>
                    <div class="card-body p-4 pt-1">
                        <div class="alert alert-info border-0 rounded-3 small mb-4 d-flex align-items-start">
                            <i class="bi bi-shield-lock-fill fs-5 me-2 text-info flex-shrink-0"></i>
                            <div>
                                <strong>Akses Administrator:</strong> Anda hanya dapat melihat informasi channel dan menghapusnya jika diperlukan. Perubahan nama dan platform hanya dapat dilakukan oleh Creator pemilik channel.
                            </div>
                        </div>

                        <!-- Form Informasi Creator & Channel (Read-Only untuk Admin) -->
                        <form onsubmit="return false;">
                            <div class="mb-3">
                                <label for="admin_creator_name" class="form-label small fw-semibold text-muted">Nama Creator (Pemilik Channel)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted"><i class="bi bi-person-fill"></i></span>
                                    <input type="text" id="admin_creator_name" class="form-control bg-light fw-semibold" value="{{ $channel->user->name ?? 'Tidak Diketahui' }}" readonly disabled>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="admin_platform" class="form-label small fw-semibold text-muted">Platform Media Sosial</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light text-muted">
                                            <i class="bi bi-{{ $channel->platform === 'youtube' ? 'youtube text-danger' : ($channel->platform === 'tiktok' ? 'tiktok text-dark' : ($channel->platform === 'instagram' ? 'instagram text-danger' : 'linkedin text-primary')) }}"></i>
                                        </span>
                                        <input type="text" id="admin_platform" class="form-control bg-light" value="{{ $channel->platform === 'youtube' ? 'YouTube' : ($channel->platform === 'tiktok' ? 'TikTok' : ($channel->platform === 'linkedin' ? 'LinkedIn' : ucfirst($channel->platform))) }}" readonly disabled>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label for="admin_handle" class="form-label small fw-semibold text-muted">Handle / Nama Akun</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light text-muted">@</span>
                                        <input type="text" id="admin_handle" class="form-control bg-light fw-semibold" value="{{ ltrim($channel->handle_name, '@') }}" readonly disabled>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Bagian Administrator: Hanya Dapat Menghapus Channel -->
                <div class="card border-danger-subtle shadow-sm rounded-4">
                    <div class="card-header bg-danger-subtle text-danger py-3 border-0">
                        <h6 class="fw-bold mb-0"><i class="bi bi-exclamation-triangle me-2"></i>Hapus Channel</h6>
                    </div>
                    <div class="card-body p-4">
                        @php $contentCount = $channel->contents_count ?? $channel->contents()->count(); @endphp
                        @if ($contentCount > 0)
                            <div class="alert alert-warning mb-3 small d-flex align-items-center">
                                <i class="bi bi-exclamation-triangle-fill me-2 fs-5 text-warning flex-shrink-0"></i>
                                <div>
                                    Channel ini memiliki <strong>{{ $contentCount }} konten</strong> milik creator <strong>{{ $channel->user->name ?? 'Creator' }}</strong>. Jika channel ini dihapus, <strong>seluruh {{ $contentCount }} konten terkait (termasuk naskah, checklist, penugasan editor, dan riwayat metrik) akan ikut terhapus secara permanen</strong>.
                                </div>
                            </div>
                        @else
                            <p class="small text-muted mb-3">Tindakan ini bersifat permanen. Channel ini belum memiliki konten yang terkait.</p>
                        @endif

                        <div class="d-flex align-items-center justify-content-between pt-2">
                            <a href="{{ route('channels.index') }}" class="btn btn-light rounded-3 px-3">
                                <i class="bi bi-arrow-left me-1"></i> Batal
                            </a>
                            <form method="POST" action="{{ route('channels.destroy', $channel) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus channel {{ $channel->handle_name }} milik creator {{ $channel->user->name ?? '' }} {{ $contentCount > 0 ? "beserta seluruh {$contentCount} konten di dalamnya" : "" }}? Tindakan ini permanen dan tidak dapat dibatalkan!');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger rounded-3 px-4">
                                    <i class="bi bi-trash me-1"></i> Hapus Channel {{ $contentCount > 0 ? "Beserta Seluruh Kontennya" : "Ini" }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @else
                {{-- Tampilan Creator: Form Edit Channel & Opsi Hapus --}}
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

                <!-- Danger Zone: Deletion untuk Creator -->
                <div class="card border-danger-subtle shadow-sm rounded-4">
                    <div class="card-header bg-danger-subtle text-danger py-3 border-0">
                        <h6 class="fw-bold mb-0"><i class="bi bi-exclamation-triangle me-2"></i>Zona Bahaya</h6>
                    </div>
                    <div class="card-body p-4">
                        @php $contentCount = $channel->contents_count ?? $channel->contents()->count(); @endphp
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
            @endif
        </div>
    </div>
@endsection
