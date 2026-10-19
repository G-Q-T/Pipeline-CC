@extends('layouts.app')

@section('title', 'Edit Konten: ' . $content->title)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('contents.index') }}">Pipeline Konten</a></li>
    <li class="breadcrumb-item"><a href="{{ route('contents.show', $content) }}">{{ Str::limit($content->title, 20) }}</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <h4 class="fw-bold mb-1">Edit Konten</h4>
                    <p class="text-muted small mb-0">Perbarui naskah, jadwal, atau informasi produksi konten.</p>
                </div>
                <a href="{{ route('contents.show', $content) }}" class="btn btn-outline-secondary btn-sm rounded-3">
                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Detail
                </a>
            </div>

            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('contents.update', $content) }}" novalidate>
                        @csrf
                        @method('PUT')

                        @if (auth()->user()->isEditor())
                            <!-- Editor Edit View -->
                            <div class="alert alert-info small mb-4">
                                <i class="bi bi-info-circle me-1"></i> Sebagai Editor, Anda dapat mengedit judul konten, draft naskah, dan catatan deskripsi produksi. Pengaturan channel dan timeline jadwal rilis dikelola oleh Creator.
                            </div>

                            <div class="mb-3">
                                <label for="title" class="form-label small fw-semibold">Judul Konten <span class="text-danger">*</span></label>
                                <input type="text"
                                       class="form-control @error('title') is-invalid @enderror"
                                       id="title"
                                       name="title"
                                       value="{{ old('title', $content->title) }}"
                                       maxlength="255"
                                       required>
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label small fw-semibold">Deskripsi / Catatan Tambahan</label>
                                <textarea class="form-control @error('description') is-invalid @enderror"
                                          id="description"
                                          name="description"
                                          rows="3">{{ old('description', $content->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label for="script_draft" class="form-label small fw-semibold">Draft Naskah (Script Draft)</label>
                                <textarea class="form-control font-monospace @error('script_draft') is-invalid @enderror"
                                          id="script_draft"
                                          name="script_draft"
                                          rows="10">{{ old('script_draft', $content->script_draft) }}</textarea>
                                @error('script_draft')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        @else
                            <!-- Creator / Admin Full View -->
                            <div class="mb-3">
                                <label for="title" class="form-label small fw-semibold">Judul Konten <span class="text-danger">*</span></label>
                                <input type="text"
                                       class="form-control form-control-lg @error('title') is-invalid @enderror"
                                       id="title"
                                       name="title"
                                       value="{{ old('title', $content->title) }}"
                                       maxlength="255"
                                       required>
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label for="channel_id" class="form-label small fw-semibold">Channel Publikasi <span class="text-danger">*</span></label>
                                    <select class="form-select @error('channel_id') is-invalid @enderror" id="channel_id" name="channel_id" required>
                                        @foreach ($channels as $channel)
                                            <option value="{{ $channel->id }}" {{ old('channel_id', $content->channel_id) == $channel->id ? 'selected' : '' }}>
                                                {{ ucfirst($channel->platform) }} — {{ $channel->handle_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('channel_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="format_type" class="form-label small fw-semibold">Format Konten <span class="text-danger">*</span></label>
                                    <select class="form-select @error('format_type') is-invalid @enderror" id="format_type" name="format_type" required>
                                        <option value="short_video" {{ old('format_type', $content->format_type) === 'short_video' ? 'selected' : '' }}>Short Video (Reels/TikTok/Shorts)</option>
                                        <option value="long_video" {{ old('format_type', $content->format_type) === 'long_video' ? 'selected' : '' }}>Long Video (YouTube)</option>
                                        <option value="carousel" {{ old('format_type', $content->format_type) === 'carousel' ? 'selected' : '' }}>Carousel / Slide</option>
                                        <option value="single_post" {{ old('format_type', $content->format_type) === 'single_post' ? 'selected' : '' }}>Single Post (Gambar/Teks)</option>
                                    </select>
                                    @error('format_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label for="priority" class="form-label small fw-semibold">Tingkat Prioritas <span class="text-danger">*</span></label>
                                    <select class="form-select @error('priority') is-invalid @enderror" id="priority" name="priority" required>
                                        <option value="low" {{ old('priority', $content->priority) === 'low' ? 'selected' : '' }}>Rendah (Low)</option>
                                        <option value="medium" {{ old('priority', $content->priority) === 'medium' ? 'selected' : '' }}>Sedang (Medium)</option>
                                        <option value="high" {{ old('priority', $content->priority) === 'high' ? 'selected' : '' }}>Tinggi (High)</option>
                                    </select>
                                    @error('priority')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="scheduled_date" class="form-label small fw-semibold">Rencana Tanggal Rilis</label>
                                    <input type="date"
                                           class="form-control @error('scheduled_date') is-invalid @enderror"
                                           id="scheduled_date"
                                           name="scheduled_date"
                                           value="{{ old('scheduled_date', $content->scheduled_date?->format('Y-m-d')) }}">
                                    @error('scheduled_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label small fw-semibold">Deskripsi / Catatan Ide</label>
                                <textarea class="form-control @error('description') is-invalid @enderror"
                                          id="description"
                                          name="description"
                                          rows="3">{{ old('description', $content->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label for="script_draft" class="form-label small fw-semibold">Draft Naskah (Script Draft)</label>
                                <textarea class="form-control font-monospace @error('script_draft') is-invalid @enderror"
                                          id="script_draft"
                                          name="script_draft"
                                          rows="8">{{ old('script_draft', $content->script_draft) }}</textarea>
                                @error('script_draft')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        @endif

                        <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                            <a href="{{ route('contents.show', $content) }}" class="btn btn-light rounded-3 px-3">Batal</a>
                            <button type="submit" class="btn btn-primary rounded-3 px-4 shadow-sm">
                                <i class="bi bi-check-lg me-1"></i> Perbarui Konten
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            @can('delete', $content)
                <!-- Danger Zone -->
                <div class="card border-danger-subtle shadow-sm rounded-4">
                    <div class="card-header bg-danger-subtle text-danger py-3 border-0">
                        <h6 class="fw-bold mb-0"><i class="bi bi-trash me-2"></i>Hapus Konten</h6>
                    </div>
                    <div class="card-body p-4 d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-semibold text-danger">Hapus konten ini secara permanen</div>
                            <small class="text-muted">Seluruh checklist, catatan metrik, dan penugasan editor akan dihapus.</small>
                        </div>
                        <form method="POST" action="{{ route('contents.destroy', $content) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus konten ini secara permanen?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger btn-sm rounded-3">
                                <i class="bi bi-trash me-1"></i> Hapus Konten
                            </button>
                        </form>
                    </div>
                </div>
            @endcan
        </div>
    </div>
@endsection
