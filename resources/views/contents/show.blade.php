@extends('layouts.app')

@section('title', $content->title)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('contents.index') }}">Pipeline Konten</a></li>
    <li class="breadcrumb-item active">{{ Str::limit($content->title, 25) }}</li>
@endsection

@section('topbar_actions')
    @can('update', $content)
        <a href="{{ route('contents.edit', $content) }}" class="btn btn-sm btn-outline-secondary rounded-3">
            <i class="bi bi-pencil me-1"></i> Edit Konten
        </a>
    @endcan
@endsection

@section('content')
    <!-- Header Banner -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-light text-dark border px-2.5 py-1.5">
                        <i class="bi bi-{{ $content->channel->platform === 'youtube' ? 'youtube text-danger' : ($content->channel->platform === 'tiktok' ? 'tiktok text-dark' : ($content->channel->platform === 'instagram' ? 'instagram text-danger' : 'linkedin text-primary')) }} me-1"></i>
                        {{ ucfirst($content->channel->platform ?? 'Channel') }} — {{ $content->channel->handle_name ?? '-' }}
                    </span>
                    <span class="badge bg-secondary-subtle text-secondary border px-2 py-1.5">{{ ucfirst(str_replace('_', ' ', $content->format_type)) }}</span>
                    <x-priority-badge :priority="$content->priority" />
                    <x-status-badge :status="$content->status" />
                </div>
                <div class="small text-muted">
                    Dibuat oleh: <strong class="text-dark">{{ $content->user->name ?? '-' }}</strong> &bull; {{ $content->created_at->format('d M Y') }}
                </div>
            </div>

            <h3 class="fw-bold text-dark mb-2">{{ $content->title }}</h3>

            @if ($content->description)
                <p class="text-muted mb-3">{{ $content->description }}</p>
            @endif

            <div class="d-flex flex-wrap gap-4 pt-3 border-top small text-muted">
                <div>
                    <i class="bi bi-calendar-event me-1 text-primary"></i> Target Rilis:
                    <strong class="text-dark">{{ $content->scheduled_date ? $content->scheduled_date->format('d M Y') : 'Belum ditentukan' }}</strong>
                </div>
                @if ($content->status === 'published')
                    <div>
                        <i class="bi bi-calendar-check me-1 text-success"></i> Terbit Aktual:
                        <strong class="text-success">{{ $content->published_date ? $content->published_date->format('d M Y') : '-' }}</strong>
                    </div>
                    <div>
                        <i class="bi bi-link-45deg me-1 text-primary"></i> Link:
                        <a href="{{ $content->published_url }}" target="_blank" rel="noopener" class="text-primary text-decoration-none fw-semibold">
                            Buka Postingan <i class="bi bi-box-arrow-up-right small"></i>
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Status Transition Controls Bar -->
        <div class="card-footer bg-light px-4 py-3 border-top d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-2">
                <span class="small fw-bold text-muted text-uppercase" style="font-size: 0.75rem;">Tahap Saat Ini:</span>
                <x-status-badge :status="$content->status" />
            </div>

            <div class="d-flex flex-wrap align-items-center gap-2">
                @php
                    $user = auth()->user();
                    $allowedStatuses = [];
                    if (!$user->isAdmin()) {
                        $allowedStatuses = $content->getAllowedTransitions();
                        if ($user->isEditor()) {
                            $allowedStatuses = in_array('ready', $allowedStatuses) && $content->status !== 'ready' ? ['ready'] : [];
                        }
                        if ($user->isCreator() && $content->editors->isNotEmpty()) {
                            $allowedStatuses = array_values(array_filter($allowedStatuses, fn ($s) => $s !== \App\Models\Content::STATUS_READY));
                        }
                    }
                    $isCreatorWaitingForEditor = $user->isCreator() && $content->editors->isNotEmpty() && $content->status !== 'ready' && $content->status !== 'published';
                @endphp

                @if (auth()->user()->isAdmin())
                    <span class="badge bg-light text-muted border px-3 py-2 small d-inline-flex align-items-center gap-1.5">
                        <i class="bi bi-eye text-primary"></i> Mode Monitoring: Admin mengawasi siklus produksi (Read-Only)
                    </span>
                @elseif ($content->status !== 'published')
                    @if ($isCreatorWaitingForEditor && in_array($content->status, ['recording', 'editing']))
                        <span class="badge bg-warning-subtle text-warning-emphasis border px-2.5 py-2 small d-inline-flex align-items-center gap-1">
                            <i class="bi bi-hourglass-split"></i> Menunggu penyelesaian oleh Editor: <strong>{{ $content->editors->pluck('name')->join(', ') }}</strong> sebelum dapat ke Ready
                        </span>
                    @endif

                    @foreach ($allowedStatuses as $nextStatus)
                        @if ($nextStatus === 'published')
                            @can('managePublication', $content)
                                <button type="button" class="btn btn-sm btn-success rounded-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#publishModal">
                                    <i class="bi bi-send-check me-1"></i> Terbitkan (Publish)
                                </button>
                            @endcan
                        @else
                            <form method="POST" action="{{ route('contents.status.update', $content) }}" class="d-inline">
                                @csrf
                                <input type="hidden" name="status" value="{{ $nextStatus }}">
                                @php
                                    $isForward = \App\Models\Content::STATUS_ORDER[$nextStatus] > \App\Models\Content::STATUS_ORDER[$content->status];
                                @endphp
                                <button type="submit" class="btn btn-sm {{ $isForward ? 'btn-primary' : 'btn-outline-secondary' }} rounded-3 shadow-sm">
                                    <i class="bi {{ $isForward ? 'bi-arrow-right-circle' : 'bi-arrow-left-circle' }} me-1"></i>
                                    {{ $isForward ? 'Lanjut ke ' : 'Kembali ke ' }}{{ ucfirst($nextStatus) }}
                                </button>
                            </form>
                        @endif
                    @endforeach
                @else
                    <span class="badge bg-success-subtle text-success border px-3 py-2 small">
                        <i class="bi bi-check-all me-1"></i> Konten ini sudah terbit. Siklus produksi telah selesai.
                    </span>
                @endif
            </div>
        </div>
    </div>

    <!-- Main Content Details Row -->
    <div class="row g-4">
        <!-- Left: Script Draft & Performance Metrics -->
        <div class="col-lg-7">
            <!-- Script Draft Card -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
                    <h6 class="fw-bold mb-0"><i class="bi bi-file-earmark-text me-2 text-primary"></i>Draft Naskah (Script Draft)</h6>
                    @can('update', $content)
                        <a href="{{ route('contents.edit', $content) }}" class="btn btn-sm btn-light border rounded-3">
                            <i class="bi bi-pencil me-1"></i> Edit Naskah
                        </a>
                    @endcan
                </div>
                <div class="card-body p-4">
                    @if ($content->script_draft)
                        <div class="p-3 bg-light rounded-3 font-monospace small" style="white-space: pre-wrap; line-height: 1.6;">{{ $content->script_draft }}</div>
                    @else
                        <div class="text-muted small py-4 text-center">
                            <i class="bi bi-journal-text fs-3 d-block mb-2 text-secondary"></i>
                            Belum ada draft naskah untuk konten ini.
                            @can('update', $content)
                                <div class="mt-2">
                                    <a href="{{ route('contents.edit', $content) }}" class="btn btn-sm btn-outline-primary rounded-3">Mulai Tulis Naskah</a>
                                </div>
                            @endcan
                        </div>
                    @endif
                </div>
            </div>

            <!-- Content Performance Metrics Card -->
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="fw-bold mb-0"><i class="bi bi-graph-up-arrow me-2 text-success"></i>Metrik Performa Manual</h6>
                        <small class="text-muted">Pencatatan statistik views, likes, comments, dan shares.</small>
                    </div>
                    @if ($content->status === 'published')
                        @can('manageMetrics', $content)
                            <button type="button" class="btn btn-sm btn-success rounded-3" data-bs-toggle="modal" data-bs-target="#metricModal">
                                <i class="bi bi-plus-lg me-1"></i> Catat Metrik
                            </button>
                        @endcan
                    @endif
                </div>
                <div class="card-body p-0">
                    @if ($content->status !== 'published')
                        <div class="p-4 text-center text-muted small">
                            <i class="bi bi-lock fs-3 d-block mb-2 text-secondary"></i>
                            Metrik hanya dapat dicatat setelah konten berstatus <strong>Published</strong>.
                        </div>
                    @elseif ($content->metrics->isEmpty())
                        <x-empty-state icon="bi-bar-chart-line" title="Belum ada riwayat metrik" description="Catat statistik manual pertama setelah Anda mempublikasikan konten di media sosial.">
                            @can('manageMetrics', $content)
                                <button type="button" class="btn btn-sm btn-primary rounded-3" data-bs-toggle="modal" data-bs-target="#metricModal">
                                    Catat Metrik Pertama
                                </button>
                            @endcan
                        </x-empty-state>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 small">
                                <thead class="table-light">
                                    <tr>
                                        <th>Tanggal Catat</th>
                                        <th class="text-end">Views</th>
                                        <th class="text-end">Likes</th>
                                        <th class="text-end">Comments</th>
                                        <th class="text-end">Shares</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($content->metrics as $metric)
                                        <tr>
                                            <td class="fw-semibold">{{ $metric->recorded_at->format('d M Y') }}</td>
                                            <td class="text-end fw-bold text-primary">{{ number_format($metric->views_count) }}</td>
                                            <td class="text-end text-danger">{{ number_format($metric->likes_count) }}</td>
                                            <td class="text-end text-muted">{{ number_format($metric->comments_count) }}</td>
                                            <td class="text-end text-muted">{{ number_format($metric->shares_count) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right: Checklists & Editor Assignments -->
        <div class="col-lg-5">
            <!-- Production Checklists Card -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white py-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h6 class="fw-bold mb-0"><i class="bi bi-check2-square me-2 text-primary"></i>Checklist Produksi</h6>
                        <span class="badge bg-primary-subtle text-primary border">{{ $content->checklist_percentage }}% Selesai</span>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $content->checklist_percentage }}%"></div>
                    </div>
                </div>

                <div class="card-body p-3">
                    <!-- Add Checklist Form -->
                    @can('manageChecklist', $content)
                        <form method="POST" action="{{ route('contents.checklists.store', $content) }}" class="mb-3">
                            @csrf
                            <div class="input-group">
                                <input type="text"
                                       class="form-control form-control-sm"
                                       name="task_name"
                                       placeholder="Tambah tugas baru (misal: Sound Design)..."
                                       maxlength="255"
                                       required>
                                <button type="submit" class="btn btn-sm btn-primary">
                                    <i class="bi bi-plus-lg"></i> Tambah
                                </button>
                            </div>
                        </form>
                    @endcan

                    <!-- Checklists List -->
                    @if ($content->checklists->isEmpty())
                        <x-empty-state icon="bi-clipboard-check" title="Belum ada checklist" description="Tambahkan checklist tugas produksi agar progres konten terpantau rapi." />
                    @else
                        <div class="list-group list-group-flush border-top">
                            @foreach ($content->checklists as $item)
                                <div class="list-group-item px-0 py-2.5 d-flex align-items-center justify-content-between border-0 border-bottom">
                                    <form method="POST" action="{{ route('checklists.toggle', $item) }}" class="d-flex align-items-center gap-2 flex-grow-1 me-2">
                                        @csrf
                                        @method('PATCH')
                                        <input class="form-check-input mt-0"
                                               type="checkbox"
                                               onChange="this.form.submit()"
                                               {{ $item->is_completed ? 'checked' : '' }}
                                               @cannot('manageChecklist', $content) disabled @endcannot>
                                        <span class="small {{ $item->is_completed ? 'text-decoration-line-through text-muted' : 'text-dark fw-medium' }}">
                                            {{ $item->task_name }}
                                        </span>
                                    </form>

                                    @can('manageChecklist', $content)
                                        <form method="POST" action="{{ route('checklists.destroy', $item) }}" onsubmit="return confirm('Hapus tugas ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-link text-danger p-0 border-0" title="Hapus tugas">
                                                <i class="bi bi-x-lg small"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <!-- Assigned Editors Card -->
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="fw-bold mb-0"><i class="bi bi-people me-2 text-primary"></i>Editor Ditugaskan</h6>
                        <small class="text-muted">Akses editing diberikan kepada editor pilihan.</small>
                    </div>
                </div>

                <div class="card-body p-3">
                    <!-- Assign Editor Form (Creator/Admin only) -->
                    @can('assignEditor', $content)
                        @if ($availableEditors->isNotEmpty())
                            <form method="POST" action="{{ route('contents.assignments.store', $content) }}" class="mb-3">
                                @csrf
                                <div class="input-group">
                                    <select class="form-select form-select-sm" name="editor_id" required>
                                        <option value="" disabled selected>Pilih editor untuk ditugaskan...</option>
                                        @foreach ($availableEditors as $ed)
                                            <option value="{{ $ed->id }}">{{ $ed->name }} ({{ $ed->email }})</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="btn btn-sm btn-primary">
                                        <i class="bi bi-person-plus"></i> Tugaskan
                                    </button>
                                </div>
                            </form>
                        @endif
                    @endcan

                    <!-- Assigned Editors List -->
                    @if ($content->editors->isEmpty())
                        <div class="text-center py-4 text-muted small">
                            <i class="bi bi-person-x fs-3 d-block mb-1 text-secondary"></i>
                            Belum ada editor yang ditugaskan pada konten ini.
                        </div>
                    @else
                        <div class="list-group list-group-flush">
                            @foreach ($content->editors as $editor)
                                <div class="list-group-item px-0 py-2.5 d-flex align-items-center justify-content-between border-0 border-bottom">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; font-size: 0.8rem;">
                                            {{ strtoupper(substr($editor->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="fw-semibold small text-dark">{{ $editor->name }}</div>
                                            <div class="text-muted" style="font-size: 0.75rem;">Ditugaskan: {{ $editor->pivot->assigned_at ? \Carbon\Carbon::parse($editor->pivot->assigned_at)->format('d M Y') : '-' }}</div>
                                        </div>
                                    </div>

                                    @can('assignEditor', $content)
                                        <form method="POST" action="{{ route('contents.assignments.destroy', [$content, $editor]) }}" onsubmit="return confirm('Cabut akses editor {{ $editor->name }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2 rounded-2" style="font-size: 0.75rem;">
                                                Cabut
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @can('managePublication', $content)
    <!-- Modal Publish -->
    <div class="modal fade" id="publishModal" tabindex="-1" aria-labelledby="publishModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow rounded-4">
                <form method="POST" action="{{ route('contents.publish', $content) }}">
                    @csrf
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-bold" id="publishModalLabel">Publikasikan Konten</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body py-3">
                        <p class="small text-muted mb-3">Masukkan link postingan aktual dan tanggal publikasi di media sosial.</p>

                        <div class="mb-3">
                            <label for="published_url" class="form-label small fw-semibold">URL Postingan Media Sosial <span class="text-danger">*</span></label>
                            <input type="url"
                                   class="form-control"
                                   id="published_url"
                                   name="published_url"
                                   placeholder="https://youtube.com/watch?v=... atau https://tiktok.com/@..."
                                   value="{{ old('published_url', $content->published_url) }}"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label for="published_date" class="form-label small fw-semibold">Tanggal Publikasi Aktual <span class="text-danger">*</span></label>
                            <input type="date"
                                   class="form-control"
                                   id="published_date"
                                   name="published_date"
                                   value="{{ old('published_date', now()->format('Y-m-d')) }}"
                                   required>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success rounded-3 px-3 shadow-sm">
                            <i class="bi bi-send-check me-1"></i> Simpan & Terbitkan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endcan

    @can('manageMetrics', $content)
    <!-- Modal Metric Input -->
    <div class="modal fade" id="metricModal" tabindex="-1" aria-labelledby="metricModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow rounded-4">
                <form method="POST" action="{{ route('contents.metrics.store', $content) }}">
                    @csrf
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-bold" id="metricModalLabel">Catat Metrik Performa</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body py-3">
                        <div class="mb-3">
                            <label for="recorded_at" class="form-label small fw-semibold">Tanggal Pencatatan <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="recorded_at" name="recorded_at" value="{{ now()->format('Y-m-d') }}" required>
                        </div>

                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <label for="views_count" class="form-label small fw-semibold">Views <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="views_count" name="views_count" min="0" value="0" required>
                            </div>
                            <div class="col-6">
                                <label for="likes_count" class="form-label small fw-semibold">Likes <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="likes_count" name="likes_count" min="0" value="0" required>
                            </div>
                        </div>

                        <div class="row g-2">
                            <div class="col-6">
                                <label for="comments_count" class="form-label small fw-semibold">Comments <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="comments_count" name="comments_count" min="0" value="0" required>
                            </div>
                            <div class="col-6">
                                <label for="shares_count" class="form-label small fw-semibold">Shares <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="shares_count" name="shares_count" min="0" value="0" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary rounded-3 px-3 shadow-sm">
                            <i class="bi bi-save me-1"></i> Simpan Catatan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endcan
@endsection
