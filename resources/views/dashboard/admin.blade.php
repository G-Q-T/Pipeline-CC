@extends('layouts.app')

@section('title', 'Dashboard Administrator')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Dashboard Admin</li>
@endsection

@section('content')
    <div class="row align-items-center mb-4">
        <div class="col-md-8">
            <h4 class="fw-bold text-dark mb-1">Panel Administrasi Sistem 🛡️</h4>
            <p class="text-muted small mb-0">Pengawasan statistik pengguna, distribusi role, dan aktivitas platform.</p>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
            <a href="{{ route('admin.users.index') }}" class="btn btn-primary btn-sm rounded-3">
                <i class="bi bi-people me-1"></i> Kelola Pengguna
            </a>
        </div>
    </div>

    <!-- Admin KPI Stats -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 h-100">
                <span class="text-muted small fw-medium">Total Pengguna</span>
                <h3 class="fw-bold mb-0 text-dark">{{ $totalUsers }}</h3>
                <small class="text-muted">Terdaftar di sistem</small>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 h-100">
                <span class="text-muted small fw-medium">Creators</span>
                <h3 class="fw-bold mb-0 text-primary">{{ $totalCreators }}</h3>
                <small class="text-muted">Pemilik konten</small>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 h-100">
                <span class="text-muted small fw-medium">Editors</span>
                <h3 class="fw-bold mb-0 text-info">{{ $totalEditors }}</h3>
                <small class="text-muted">Tim produksi</small>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 h-100">
                <span class="text-muted small fw-medium">Total Konten</span>
                <h3 class="fw-bold mb-0 text-success">{{ $totalContents }}</h3>
                <small class="text-muted">Dari semua creator</small>
            </div>
        </div>
    </div>

    <!-- Admin Activity Feeds -->
    <div class="row g-4">
        <!-- Recent Users -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
                    <h6 class="fw-bold mb-0"><i class="bi bi-person-plus me-2 text-primary"></i>Pengguna Terbaru</h6>
                    <a href="{{ route('admin.users.index') }}" class="small text-decoration-none">Kelola Semua</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light small">
                                <tr>
                                    <th>Nama & Email</th>
                                    <th>Role</th>
                                    <th>Tanggal Bergabung</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($recentUsers as $user)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $user->name }}</div>
                                            <div class="text-muted small">{{ $user->email }}</div>
                                        </td>
                                        <td>
                                            <span class="badge {{ $user->isAdmin() ? 'bg-danger-subtle text-danger' : ($user->isEditor() ? 'bg-info-subtle text-info' : 'bg-primary-subtle text-primary') }} border px-2 py-1">
                                                {{ strtoupper($user->role) }}
                                            </span>
                                        </td>
                                        <td class="small text-muted">{{ $user->created_at->format('d M Y') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Contents Across Platform -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
                    <h6 class="fw-bold mb-0"><i class="bi bi-camera-reels me-2 text-primary"></i>Konten Terakhir Dibuat</h6>
                    <a href="{{ route('contents.index') }}" class="small text-decoration-none">Lihat Semua</a>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @foreach ($recentContents as $content)
                            <div class="list-group-item p-3 border-0 border-bottom d-flex align-items-center justify-content-between">
                                <div class="overflow-hidden pe-2">
                                    <div class="fw-semibold text-truncate small mb-1">{{ $content->title }}</div>
                                    <div class="small text-muted">
                                        Oleh: {{ $content->user->name ?? 'User' }} &bull; {{ ucfirst($content->channel->platform ?? 'Platform') }}
                                    </div>
                                </div>
                                <div class="text-end text-nowrap">
                                    <x-status-badge :status="$content->status" />
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
