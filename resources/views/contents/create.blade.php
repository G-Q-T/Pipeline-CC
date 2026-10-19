@extends('layouts.app')

@section('title', 'Buat Konten Baru')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('contents.index') }}">Pipeline Konten</a></li>
    <li class="breadcrumb-item active">Buat Konten</li>
@endsection

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <h4 class="fw-bold mb-1">Buat Ide Konten Baru</h4>
                    <p class="text-muted small mb-0">Catat ide konten Anda untuk memulai siklus produksi.</p>
                </div>
                <a href="{{ route('contents.index') }}" class="btn btn-outline-secondary btn-sm rounded-3">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </a>
            </div>

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('contents.store') }}" novalidate>
                        @csrf

                        <!-- Title -->
                        <div class="mb-3">
                            <label for="title" class="form-label small fw-semibold">Judul Konten <span class="text-danger">*</span></label>
                            <input type="text"
                                   class="form-control form-control-lg @error('title') is-invalid @enderror"
                                   id="title"
                                   name="title"
                                   value="{{ old('title') }}"
                                   placeholder="Contoh: 5 Tips Optimasi Algoritma Video 2026"
                                   maxlength="255"
                                   required
                                   autofocus>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row g-3 mb-3">
                            <!-- Channel -->
                            <div class="col-md-6">
                                <label for="channel_id" class="form-label small fw-semibold">Channel Publikasi <span class="text-danger">*</span></label>
                                <select class="form-select @error('channel_id') is-invalid @enderror" id="channel_id" name="channel_id" required>
                                    <option value="" disabled {{ old('channel_id') ? '' : 'selected' }}>Pilih Channel</option>
                                    @foreach ($channels as $channel)
                                        <option value="{{ $channel->id }}" {{ old('channel_id') == $channel->id ? 'selected' : '' }}>
                                            {{ ucfirst($channel->platform) }} — {{ $channel->handle_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('channel_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Format Type -->
                            <div class="col-md-6">
                                <label for="format_type" class="form-label small fw-semibold">Format Konten <span class="text-danger">*</span></label>
                                <select class="form-select @error('format_type') is-invalid @enderror" id="format_type" name="format_type" required>
                                    <option value="short_video" {{ old('format_type') === 'short_video' ? 'selected' : '' }}>Short Video (Reels/TikTok/Shorts)</option>
                                    <option value="long_video" {{ old('format_type') === 'long_video' ? 'selected' : '' }}>Long Video (YouTube)</option>
                                    <option value="carousel" {{ old('format_type') === 'carousel' ? 'selected' : '' }}>Carousel / Slide</option>
                                    <option value="single_post" {{ old('format_type') === 'single_post' ? 'selected' : '' }}>Single Post (Gambar/Teks)</option>
                                </select>
                                @error('format_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <!-- Priority -->
                            <div class="col-md-6">
                                <label for="priority" class="form-label small fw-semibold">Tingkat Prioritas <span class="text-danger">*</span></label>
                                <select class="form-select @error('priority') is-invalid @enderror" id="priority" name="priority" required>
                                    <option value="low" {{ old('priority') === 'low' ? 'selected' : '' }}>Rendah (Low)</option>
                                    <option value="medium" {{ old('priority', 'medium') === 'medium' ? 'selected' : '' }}>Sedang (Medium)</option>
                                    <option value="high" {{ old('priority') === 'high' ? 'selected' : '' }}>Tinggi (High)</option>
                                </select>
                                @error('priority')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Scheduled Date -->
                            <div class="col-md-6">
                                <label for="scheduled_date" class="form-label small fw-semibold">Rencana Tanggal Rilis</label>
                                <input type="date"
                                       class="form-control @error('scheduled_date') is-invalid @enderror"
                                       id="scheduled_date"
                                       name="scheduled_date"
                                       value="{{ old('scheduled_date') }}">
                                @error('scheduled_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Assign Editor (Optional) -->
                        <div class="mb-3">
                            <label for="editor_id" class="form-label small fw-semibold">Tugaskan Editor (Opsional)</label>
                            <select class="form-select @error('editor_id') is-invalid @enderror" id="editor_id" name="editor_id">
                                <option value="">-- Belum ada penugasan editor --</option>
                                @foreach ($editors as $editor)
                                    <option value="{{ $editor->id }}" {{ old('editor_id') == $editor->id ? 'selected' : '' }}>
                                        {{ $editor->name }} ({{ $editor->email }})
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text text-muted small">Jika ditugaskan, pengerjaan editing akan dilakukan oleh Editor dan tahap Ready harus diselesaikan oleh Editor.</div>
                            @error('editor_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Description -->
                        <div class="mb-3">
                            <label for="description" class="form-label small fw-semibold">Deskripsi / Catatan Ide</label>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                      id="description"
                                      name="description"
                                      rows="3"
                                      placeholder="Tuliskan poin-poin ringkasan ide konten...">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Initial Script Draft -->
                        <div class="mb-4">
                            <label for="script_draft" class="form-label small fw-semibold">Draft Naskah Awal (Opsional)</label>
                            <textarea class="form-control font-monospace @error('script_draft') is-invalid @enderror"
                                      id="script_draft"
                                      name="script_draft"
                                      rows="5"
                                      placeholder="Hook, Problem, Solution, Call to Action...">{{ old('script_draft') }}</textarea>
                            @error('script_draft')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                            <a href="{{ route('contents.index') }}" class="btn btn-light rounded-3 px-3">Batal</a>
                            <button type="submit" class="btn btn-primary rounded-3 px-4 shadow-sm">
                                <i class="bi bi-check-lg me-1"></i> Simpan ke Status Idea
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
