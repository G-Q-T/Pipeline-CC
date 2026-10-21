@extends('layouts.app')

@section('title', 'Analitik & Metrik Konten')

@section('breadcrumb')
    <li class="breadcrumb-item active">Analitik & Metrik</li>
@endsection

@section('content')
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-1">Analitik & Metrik Konten</h4>
            <p class="text-muted small mb-0">Pantau perkembangan performa penayangan dan interaksi dari seluruh konten yang telah terbit.</p>
        </div>
    </div>

    <!-- Aggregate Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-medium">Konten Terbit</span>
                    <div class="bg-success-subtle text-success rounded-3 p-2">
                        <i class="bi bi-send-check fs-5"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-0 text-success">{{ number_format($publishedContents->total()) }}</h3>
                <small class="text-muted">Status Published</small>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-medium">Total Views</span>
                    <div class="bg-primary-subtle text-primary rounded-3 p-2">
                        <i class="bi bi-eye fs-5"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-0 text-primary">{{ number_format($totalViews) }}</h3>
                <small class="text-muted">Total penayangan</small>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-medium">Total Likes</span>
                    <div class="bg-danger-subtle text-danger rounded-3 p-2">
                        <i class="bi bi-heart fs-5"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-0 text-danger">{{ number_format($totalLikes) }}</h3>
                <small class="text-muted">Total suka</small>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-medium">Comments & Shares</span>
                    <div class="bg-info-subtle text-info rounded-3 p-2">
                        <i class="bi bi-chat-square-dots fs-5"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-0 text-dark">{{ number_format($totalComments + $totalShares) }}</h3>
                <small class="text-muted">{{ number_format($totalComments) }} komen &bull; {{ number_format($totalShares) }} bagikan</small>
            </div>
        </div>
    </div>

    <!-- Search & Filter Card -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('metrics.index') }}" class="row g-2 align-items-center">
                <div class="col-12 col-md-6">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text"
                               class="form-control border-start-0"
                               name="search"
                               placeholder="Cari judul konten terbit..."
                               value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-8 col-md-4">
                    <select class="form-select form-select-sm" name="channel" onchange="this.form.submit()">
                        <option value="all">Semua Channel</option>
                        @foreach ($channels as $ch)
                            <option value="{{ $ch->id }}" {{ request('channel') == $ch->id ? 'selected' : '' }}>
                                {{ ucfirst($ch->platform) }} — {{ $ch->handle_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-4 col-md-2 text-end">
                    @if(request('search') || (request('channel') && request('channel') !== 'all'))
                        <a href="{{ route('metrics.index') }}" class="btn btn-sm btn-outline-secondary w-100">
                            <i class="bi bi-x-circle me-1"></i> Reset
                        </a>
                    @else
                        <button type="submit" class="btn btn-sm btn-primary w-100">
                            <i class="bi bi-filter me-1"></i> Terapkan
                        </button>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Published Contents Table -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-0">
            @if ($publishedContents->isEmpty())
                <x-empty-state icon="bi-bar-chart-steps" title="Belum ada konten terbit" description="Konten yang telah berstatus Published dan memiliki URL publikasi akan muncul di sini untuk dicatat metriknya.">
                    <a href="{{ route('contents.index') }}" class="btn btn-sm btn-primary rounded-3">Buka Pipeline Konten</a>
                </x-empty-state>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small">
                            <tr>
                                <th>Konten & Platform</th>
                                <th>Tanggal Terbit</th>
                                <th>Update Terakhir</th>
                                <th class="text-end">Views</th>
                                <th class="text-end">Likes</th>
                                <th class="text-end">Comments</th>
                                <th class="text-end">Shares</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($publishedContents as $item)
                                @php $latest = $item->latestMetric; @endphp
                                <tr>
                                    <td>
                                        <a href="{{ route('contents.show', $item) }}" class="fw-semibold text-dark text-decoration-none d-block">
                                            {{ $item->title }}
                                        </a>
                                        <div class="d-flex align-items-center gap-2 small text-muted">
                                            <span>
                                                <i class="bi bi-{{ $item->channel->platform === 'youtube' ? 'youtube text-danger' : ($item->channel->platform === 'tiktok' ? 'tiktok text-dark' : ($item->channel->platform === 'instagram' ? 'instagram text-danger' : 'linkedin text-primary')) }}"></i>
                                                {{ $item->channel->handle_name ?? '-' }}
                                            </span>
                                            @if ($item->published_url)
                                                &bull;
                                                <a href="{{ $item->published_url }}" target="_blank" rel="noopener" class="text-primary text-decoration-none" title="Buka Link Postingan">
                                                    Lihat Postingan <i class="bi bi-box-arrow-up-right" style="font-size: 0.7rem;"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="small">{{ $item->published_date ? $item->published_date->format('d M Y') : '-' }}</td>
                                    <td class="small text-muted">{{ $latest ? $latest->recorded_at->format('d M Y') : 'Belum dicatat' }}</td>
                                    <td class="text-end fw-bold text-primary">{{ $latest ? number_format($latest->views_count) : '-' }}</td>
                                    <td class="text-end text-danger">{{ $latest ? number_format($latest->likes_count) : '-' }}</td>
                                    <td class="text-end text-muted">{{ $latest ? number_format($latest->comments_count) : '-' }}</td>
                                    <td class="text-end text-muted">{{ $latest ? number_format($latest->shares_count) : '-' }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('contents.show', $item) }}" class="btn btn-sm btn-outline-primary rounded-3">
                                            <i class="bi bi-graph-up me-1"></i> {{ auth()->user()->can('manageMetrics', $item) ? 'Detail & Catat' : 'Detail Metrik' }}
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-3 border-top">
                    {{ $publishedContents->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
