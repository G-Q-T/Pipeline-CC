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
                    $totalTasks = $content->checklists->count();
                    $incompleteTasks = $content->checklists->where('is_completed', false)->count();
                    $hasIncompleteTasks = $incompleteTasks > 0;

                    $allowedStatuses = [];
                    if (!$user->isAdmin()) {
                        $allowedStatuses = $content->getAllowedTransitions();
                        if ($user->isEditor()) {
                            $allowedStatuses = in_array('ready', $allowedStatuses) && $content->status !== 'ready' && !$hasIncompleteTasks ? ['ready'] : [];
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

                    @if ($user->isCreator() && $content->editors->isNotEmpty() && $totalTasks === 0)
                        <span class="badge bg-warning-subtle text-warning-emphasis border px-3 py-2 small d-inline-flex align-items-center gap-1.5 shadow-xs">
                            <i class="bi bi-exclamation-triangle-fill text-warning"></i>
                            <strong>Validasi:</strong> Wajib memberikan minimal 1 tugas untuk editor.
                        </span>
                    @endif

                    @if ($user->isEditor() && $content->status !== 'ready')
                        @if ($incompleteTasks > 0)
                            <span class="badge bg-warning-subtle text-warning-emphasis border px-3 py-2 small d-inline-flex align-items-center gap-1.5">
                                <i class="bi bi-hourglass-split text-warning"></i> Selesaikan seluruh tugas dari Creator (<strong>{{ $incompleteTasks }} tugas tersisa</strong>) untuk dapat mengubah status ke Ready.
                            </span>
                        @endif
                    @endif

                    @foreach ($allowedStatuses as $nextStatus)
                        @if ($nextStatus === 'published')
                            @can('managePublication', $content)
                                <button type="button" class="btn btn-sm btn-success rounded-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#publishModal">
                                    <i class="bi bi-send-check me-1"></i> Terbitkan (Publish)
                                </button>
                            @endcan
                        @elseif ($user->isEditor() && $nextStatus === 'ready')
                            <form method="POST" action="{{ route('contents.status.update', $content) }}" class="d-inline">
                                @csrf
                                <input type="hidden" name="status" value="ready">
                                <button type="submit" class="btn btn-sm btn-success rounded-3 shadow-sm">
                                    <i class="bi bi-check-circle me-1"></i> Semua Tugas Selesai — Ubah Status ke Ready
                                </button>
                            </form>
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
                        <div>
                            <h6 class="fw-bold mb-0"><i class="bi bi-check2-square me-2 text-primary"></i>Checklist Produksi</h6>
                            <small class="text-muted" style="font-size: 0.75rem;">Tugas dari Creator untuk diselesaikan oleh Editor</small>
                        </div>
                        <span class="badge {{ $content->checklist_percentage === 100 ? 'bg-success-subtle text-success' : 'bg-primary-subtle text-primary' }} border">
                            {{ $content->checklist_percentage }}% Selesai ({{ $content->checklists->where('is_completed', true)->count() }}/{{ $content->checklists->count() }})
                        </span>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar {{ $content->checklist_percentage === 100 ? 'bg-success' : 'bg-primary' }}" role="progressbar" style="width: {{ $content->checklist_percentage }}%"></div>
                    </div>
                </div>

                <div class="card-body p-3">
                    <!-- Validation Alert: Creator must provide at least 1 task if editor is assigned -->
                    @if (auth()->user()->isCreator() && $content->editors->isNotEmpty() && $content->checklists->isEmpty())
                        <div class="alert alert-warning border-warning-subtle d-flex align-items-center gap-2.5 mb-3 rounded-3 shadow-xs">
                            <i class="bi bi-exclamation-triangle-fill text-warning fs-5 flex-shrink-0"></i>
                            <div>
                                <div class="fw-bold small text-dark">Wajib Memberikan Minimal 1 Tugas untuk Editor!</div>
                                <div class="text-secondary small" style="font-size: 0.8rem;">Konten ini telah ditugaskan kepada Editor. Anda belum memberikan tugas. Wajib memberikan minimal 1 tugas untuk editor agar proses pengerjaan dapat berjalan.</div>
                            </div>
                        </div>
                    @endif

                    <!-- Add Checklist Form (Creator only) -->
                    @if (auth()->user()->isCreator() && $content->user_id === auth()->id())
                        <div class="bg-light p-3 rounded-3 mb-3 border">
                            <div class="fw-semibold small mb-2 text-dark d-flex align-items-center gap-1">
                                <i class="bi bi-plus-circle text-primary"></i> Berikan Tugas untuk Editor
                            </div>
                            <form method="POST" action="{{ route('contents.checklists.store', $content) }}">
                                @csrf
                                <!-- Satu Pilihan Select Tugas Editor (Dengan opsi Input Manual) -->
                                <div class="mb-2">
                                    <label class="form-label text-muted mb-1" style="font-size: 0.72rem; font-weight: 600;">PILIHAN TUGAS EDITOR (PILIH ATAU INPUT MANUAL):</label>
                                    <select id="presetTaskSelect" name="task_preset" class="form-select form-select-sm mb-2" onchange="handlePresetChange(this)" required>
                                        <option value="" disabled selected>-- Pilih Jenis Tugas Editor --</option>
                                        <option value="Cutting & Trimming Video (Rough Cut & Pacing)">1. Cutting & Trimming Video (Rough Cut & Pacing)</option>
                                        <option value="Color Grading & Audio Mixing">2. Color Grading & Audio Mixing</option>
                                        <option value="Subtitle, Visual Effects & Thumbnail">3. Subtitle, Visual Effects & Thumbnail</option>
                                        <option value="manual">4. Input Manual (Ketik Tugas Sendiri)...</option>
                                    </select>
                                </div>

                                <!-- Input Nama Tugas Manual (Muncul jika opsi manual dipilih) -->
                                <div id="manualTaskWrapper" class="mb-2 d-none">
                                    <label class="form-label text-muted mb-1" style="font-size: 0.72rem; font-weight: 600;">KETIK NAMA TUGAS MANUAL:</label>
                                    <input type="text"
                                           class="form-control form-control-sm"
                                           id="manualTaskInput"
                                           name="manual_task_name"
                                           placeholder="Masukkan nama tugas (misal: Sound Design / Motion Graphics)...">
                                </div>

                                <!-- Input Keterangan Tugas untuk Editor (Tanpa auto terisi agar leluasa) -->
                                <div class="mb-3">
                                    <label class="form-label text-muted mb-1" style="font-size: 0.72rem; font-weight: 600;">KETERANGAN TUGAS UNTUK EDITOR (DETAIL / CATATAN):</label>
                                    <textarea class="form-control form-control-sm"
                                              id="keteranganInput"
                                              name="keterangan"
                                              rows="3"
                                              placeholder="Tuliskan keterangan detail tugas untuk editor di sini secara leluasa..."></textarea>
                                </div>

                                <button type="submit" class="btn btn-sm btn-primary w-100 rounded-3 shadow-sm">
                                    <i class="bi bi-plus-lg me-1"></i> Tambahkan Tugas untuk Editor
                                </button>
                            </form>
                        </div>
                    @endif

                    @if (auth()->user()->isCreator() && $content->user_id === auth()->id() && $content->checklists->isNotEmpty())
                        <div class="alert alert-light border small text-muted py-2 px-3 mb-2 d-flex align-items-center">
                            <span><i class="bi bi-info-circle text-primary me-1"></i>Penceklisan tugas dilakukan oleh Editor. Jika ada kesalahan input tugas, silakan gunakan tombol hapus (<i class="bi bi-trash text-danger"></i>) di sebelah kanan.</span>
                        </div>
                    @endif

                    <!-- Checklists List -->
                    @if ($content->checklists->isEmpty())
                        @if ($content->editors->isNotEmpty())
                            <x-empty-state icon="bi-clipboard-x" title="Belum ada tugas untuk editor" description="Wajib memberikan minimal 1 tugas untuk editor agar proses editing dapat dimulai." />
                        @else
                            <x-empty-state icon="bi-clipboard-check" title="Belum ada checklist tugas" description="Creator belum menambahkan checklist tugas produksi." />
                        @endif
                    @else
                        <div class="list-group list-group-flush border-0">
                            @foreach ($content->checklists as $item)
                                <div class="p-3 rounded-3 mb-2 border transition {{ $item->is_completed ? 'bg-light border-light-subtle' : 'bg-white border-secondary-subtle shadow-xs' }}">
                                    <div class="d-flex align-items-start justify-content-between gap-2">
                                        <form method="POST" action="{{ route('checklists.toggle', $item) }}" class="d-flex align-items-start gap-2.5 flex-grow-1">
                                            @csrf
                                            @method('PATCH')
                                            <input class="form-check-input mt-1 flex-shrink-0"
                                                   type="checkbox"
                                                   onChange="this.form.submit()"
                                                   {{ $item->is_completed ? 'checked' : '' }}
                                                   @if(!auth()->user()->isEditor()) disabled @endif
                                                   title="{{ auth()->user()->isEditor() ? ($item->is_completed ? 'Tandai belum selesai' : 'Tandai selesai') : 'Hanya Editor yang bertugas yang dapat menceklis tugas ini' }}">
                                            <div class="flex-grow-1">
                                                <div class="d-flex align-items-center gap-1.5 flex-wrap">
                                                    <span class="fw-semibold small {{ $item->is_completed ? 'text-decoration-line-through text-muted' : 'text-dark' }}">
                                                        {{ $item->task_name }}
                                                    </span>
                                                    @if ($item->is_completed)
                                                        <span class="badge bg-success-subtle text-success border px-1.5 py-0.5" style="font-size: 0.65rem;">
                                                            <i class="bi bi-check2"></i> Selesai
                                                        </span>
                                                    @else
                                                        <span class="badge bg-warning-subtle text-warning-emphasis border px-1.5 py-0.5" style="font-size: 0.65rem;">
                                                            <i class="bi bi-clock"></i> Belum Selesai
                                                        </span>
                                                    @endif
                                                </div>

                                                @if ($item->keterangan)
                                                    <div class="mt-2 p-2 rounded-2 {{ $item->is_completed ? 'bg-white text-muted border' : 'bg-primary-subtle bg-opacity-25 border-start border-3 border-primary text-dark' }}" style="font-size: 0.8rem;">
                                                        <div class="d-flex align-items-center gap-1 fw-semibold text-primary mb-0.5" style="font-size: 0.72rem;">
                                                            <i class="bi bi-card-checklist"></i> Keterangan Tugas untuk Editor:
                                                        </div>
                                                        <div style="line-height: 1.45;">{{ $item->keterangan }}</div>
                                                    </div>
                                                @endif
                                            </div>
                                        </form>

                                        @if (auth()->user()->isCreator() && $content->user_id === auth()->id())
                                            <form method="POST" action="{{ route('checklists.destroy', $item) }}" onsubmit="return confirm('Hapus tugas ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-link text-danger p-0 border-0" title="Hapus tugas">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
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

@push('scripts')
<script>
    function handlePresetChange(select) {
        const manualWrapper = document.getElementById('manualTaskWrapper');
        const manualInput = document.getElementById('manualTaskInput');
        if (!manualWrapper || !manualInput) return;

        if (select.value === 'manual') {
            manualWrapper.classList.remove('d-none');
            manualInput.required = true;
            manualInput.focus();
        } else {
            manualWrapper.classList.add('d-none');
            manualInput.required = false;
            manualInput.value = '';
        }
        // Catatan: Keterangan sengaja TIDAK di-auto isi agar Creator leluasa mengetikkan detail instruksi khusus
    }
</script>
@endpush
