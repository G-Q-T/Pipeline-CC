@extends('layouts.app')

@section('title', 'Dashboard Editor')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Dashboard Editor</li>
@endsection

@section('content')
    <!-- Greeting & Summary -->
    <div class="row align-items-center mb-4">
        <div class="col-md-8">
            <h4 class="fw-bold text-dark mb-1">Halo, {{ $user->name }}! 🎬</h4>
            <p class="text-muted small mb-0">Berikut adalah daftar penugasan produksi konten yang dipercayakan kepada Anda.</p>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
            <span class="badge bg-primary fs-6 px-3 py-2 rounded-pill shadow-sm">
                <i class="bi bi-briefcase me-1"></i> {{ $totalAssigned }} Total Penugasan
            </span>
        </div>
    </div>

    <!-- Active Tasks and Deadlines -->
    <div class="row g-4">
        <!-- Assigned Contents -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
                    <h6 class="fw-bold mb-0"><i class="bi bi-list-task me-2 text-primary"></i>Konten Ditugaskan Aktif</h6>
                    <span class="badge bg-light text-dark border">{{ $activeAssignments->count() }} Konten</span>
                </div>
                <div class="card-body p-0">
                    @if ($activeAssignments->isEmpty())
                        <x-empty-state icon="bi-check2-all" title="Tidak ada penugasan aktif" description="Saat ini Anda tidak memiliki penugasan yang sedang berjalan. Hubungi creator untuk penugasan baru." />
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light small">
                                    <tr>
                                        <th>Judul Konten</th>
                                        <th>Creator</th>
                                        <th>Status</th>
                                        <th>Checklist</th>
                                        <th>Target Rilis</th>
                                        <th class="text-end">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($activeAssignments as $content)
                                        <tr>
                                            <td>
                                                <a href="{{ route('contents.show', $content) }}" class="fw-semibold text-dark text-decoration-none">
                                                    {{ $content->title }}
                                                </a>
                                                <div class="small text-muted">{{ ucfirst($content->channel->platform ?? '-') }} &bull; {{ ucfirst($content->format_type) }}</div>
                                            </td>
                                            <td class="small">{{ $content->user->name ?? '-' }}</td>
                                            <td><x-status-badge :status="$content->status" /></td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="progress flex-grow-1" style="height: 6px; width: 60px;">
                                                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ $content->checklist_percentage }}%"></div>
                                                    </div>
                                                    <span class="small fw-semibold">{{ $content->checklist_percentage }}%</span>
                                                </div>
                                            </td>
                                            <td class="small">
                                                {{ $content->scheduled_date ? $content->scheduled_date->format('d M Y') : '-' }}
                                            </td>
                                            <td class="text-end">
                                                <a href="{{ route('contents.show', $content) }}" class="btn btn-sm btn-outline-primary rounded-3">
                                                    Detail & Checklist
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Upcoming Deadlines Sidebar -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold mb-0"><i class="bi bi-clock-history me-2 text-danger"></i>Deadline Terdekat</h6>
                </div>
                <div class="card-body p-0">
                    @if ($upcomingDeadlines->isEmpty())
                        <x-empty-state icon="bi-calendar-x" title="Tidak ada deadline terdekat" description="Semua tugas tidak memiliki batas waktu mendesak." />
                    @else
                        <div class="list-group list-group-flush">
                            @foreach ($upcomingDeadlines as $content)
                                <div class="list-group-item p-3 border-0 border-bottom">
                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                        <a href="{{ route('contents.show', $content) }}" class="fw-semibold text-dark text-decoration-none small text-truncate pe-2">
                                            {{ $content->title }}
                                        </a>
                                        <x-priority-badge :priority="$content->priority" />
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center small text-muted">
                                        <span><i class="bi bi-calendar-event me-1"></i>{{ $content->scheduled_date->format('d M Y') }}</span>
                                        <x-status-badge :status="$content->status" />
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
