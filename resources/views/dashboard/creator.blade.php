@extends('layouts.app')

@section('title', 'Dashboard Creator')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
@endsection

@section('topbar_actions')
    <a href="{{ route('contents.create') }}" class="btn btn-sm btn-primary rounded-3 d-flex align-items-center gap-1 shadow-sm">
        <i class="bi bi-plus-lg"></i>
        <span>Buat Konten</span>
    </a>
@endsection

@section('content')
    <!-- Greeting & Quick Stats -->
    <div class="row align-items-center mb-4">
        <div class="col-md-8">
            <h4 class="fw-bold text-dark mb-1">Halo, {{ $user->name }}! 👋</h4>
            <p class="text-muted small mb-0">Berikut adalah ringkasan proses produksi dan performa pipeline konten Anda.</p>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
            <div class="d-inline-flex gap-2">
                <a href="{{ route('contents.kanban') }}" class="btn btn-outline-secondary btn-sm rounded-3">
                    <i class="bi bi-kanban me-1"></i> Buka Kanban
                </a>
                <a href="{{ route('contents.index') }}" class="btn btn-outline-primary btn-sm rounded-3">
                    <i class="bi bi-table me-1"></i> Buka Tabel
                </a>
            </div>
        </div>
    </div>

    <!-- KPI Metric Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-3 h-100 p-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-medium">Total Konten</span>
                    <div class="bg-primary-subtle text-primary rounded-3 p-2">
                        <i class="bi bi-collection-play fs-5"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-0">{{ number_format($totalContents) }}</h3>
                <small class="text-muted">Di semua tahapan</small>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-3 h-100 p-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-medium">Channel Terdaftar</span>
                    <div class="bg-info-subtle text-info rounded-3 p-2">
                        <i class="bi bi-broadcast fs-5"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-0">{{ number_format($totalChannels) }}</h3>
                <small class="text-muted">Platform media sosial</small>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-3 h-100 p-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-medium">Total Penayangan (Views)</span>
                    <div class="bg-success-subtle text-success rounded-3 p-2">
                        <i class="bi bi-eye fs-5"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-0">{{ number_format($metricsSummary->total_views ?? 0) }}</h3>
                <small class="text-muted">Dari konten terbit</small>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-3 h-100 p-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-medium">Total Interaksi (Likes)</span>
                    <div class="bg-danger-subtle text-danger rounded-3 p-2">
                        <i class="bi bi-heart fs-5"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-0">{{ number_format($metricsSummary->total_likes ?? 0) }}</h3>
                <small class="text-muted">Likes tercatat</small>
            </div>
        </div>
    </div>

    <!-- Pipeline Stage Counters -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white py-3 border-0">
            <h6 class="fw-bold mb-0"><i class="bi bi-diagram-3 me-2 text-primary"></i>Status Pipeline Produksi</h6>
        </div>
        <div class="card-body pt-0">
            <div class="row g-2">
                @foreach (['idea' => 'Idea', 'scripting' => 'Scripting', 'recording' => 'Recording', 'editing' => 'Editing', 'ready' => 'Ready', 'published' => 'Published'] as $statusKey => $statusLabel)
                    <div class="col-4 col-md-2">
                        <a href="{{ route('contents.index', ['status' => $statusKey]) }}" class="text-decoration-none">
                            <div class="p-3 rounded-3 text-center border badge-status-{{ $statusKey }} h-100 transition">
                                <small class="fw-semibold d-block text-truncate">{{ $statusLabel }}</small>
                                <span class="fs-4 fw-bold">{{ $statusCounts[$statusKey] ?? 0 }}</span>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Content Sections -->
    <div class="row g-4">
        <!-- Upcoming Schedules -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
                    <h6 class="fw-bold mb-0"><i class="bi bi-calendar-event me-2 text-primary"></i>Jadwal Terdekat</h6>
                    <a href="{{ route('contents.index') }}" class="small text-decoration-none">Lihat Semua</a>
                </div>
                <div class="card-body p-0">
                    @if ($upcomingContents->isEmpty())
                        <x-empty-state icon="bi-calendar-check" title="Tidak ada jadwal rilis terdekat" description="Semua konten sudah dirilis atau belum memiliki tanggal target rilis." />
                    @else
                        <div class="list-group list-group-flush">
                            @foreach ($upcomingContents as $content)
                                <div class="list-group-item p-3 border-0 border-bottom d-flex align-items-center justify-content-between">
                                    <div class="overflow-hidden pe-3">
                                        <a href="{{ route('contents.show', $content) }}" class="fw-semibold text-dark text-decoration-none d-block text-truncate mb-1">
                                            {{ $content->title }}
                                        </a>
                                        <div class="d-flex align-items-center gap-2 small text-muted">
                                            <span class="badge bg-light text-dark border">{{ ucfirst($content->channel->platform ?? 'Platform') }}</span>
                                            <span><i class="bi bi-calendar-day me-1"></i>{{ $content->scheduled_date->format('d M Y') }}</span>
                                        </div>
                                    </div>
                                    <div class="text-end text-nowrap">
                                        <x-status-badge :status="$content->status" />
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Recent Contents -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
                    <h6 class="fw-bold mb-0"><i class="bi bi-clock-history me-2 text-primary"></i>Konten Terbaru</h6>
                    <a href="{{ route('contents.index') }}" class="small text-decoration-none">Lihat Semua</a>
                </div>
                <div class="card-body p-0">
                    @if ($recentContents->isEmpty())
                        <x-empty-state icon="bi-pencil-square" title="Belum ada konten" description="Mulai siklus produksi Anda dengan membuat ide konten pertama.">
                            <a href="{{ route('contents.create') }}" class="btn btn-sm btn-primary rounded-3">Buat Konten Pertama</a>
                        </x-empty-state>
                    @else
                        <div class="list-group list-group-flush">
                            @foreach ($recentContents as $content)
                                <div class="list-group-item p-3 border-0 border-bottom d-flex align-items-center justify-content-between">
                                    <div class="overflow-hidden pe-3">
                                        <a href="{{ route('contents.show', $content) }}" class="fw-semibold text-dark text-decoration-none d-block text-truncate mb-1">
                                            {{ $content->title }}
                                        </a>
                                        <div class="d-flex align-items-center gap-2 small text-muted">
                                            <span class="badge bg-light text-dark border">{{ ucfirst($content->format_type) }}</span>
                                            @if ($content->checklists->count() > 0)
                                                <span><i class="bi bi-check2-square me-1"></i>Checklist: {{ $content->checklist_percentage }}%</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="text-end text-nowrap">
                                        <x-priority-badge :priority="$content->priority" />
                                        <div class="mt-1">
                                            <x-status-badge :status="$content->status" />
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
