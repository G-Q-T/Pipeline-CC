@extends('layouts.app')

@section('title', 'Kelola Channel')

@section('breadcrumb')
    <li class="breadcrumb-item active">Kelola Channel</li>
@endsection

@section('content')
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-1">Channel Media Sosial</h4>
            <p class="text-muted small mb-0">
                @if(auth()->user()->isAdmin())
                    Daftar seluruh channel publikasi milik creator dalam sistem.
                @else
                    Kelola akun dan channel publikasi konten Anda.
                @endif
            </p>
        </div>
        @can('create', App\Models\Channel::class)
            <a href="{{ route('channels.create') }}" class="btn btn-primary btn-sm rounded-3">
                <i class="bi bi-plus-lg me-1"></i> Tambah Channel
            </a>
        @endcan
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-0">
            @if ($channels->isEmpty())
                <x-empty-state icon="bi-broadcast-pin"
                               title="Belum ada channel terdaftar"
                               description="{{ auth()->user()->isAdmin() ? 'Belum ada creator yang mendaftarkan channel media sosial.' : 'Tambahkan channel YouTube, TikTok, Instagram, atau LinkedIn Anda untuk mulai mengaitkan konten.' }}">
                    @can('create', App\Models\Channel::class)
                        <a href="{{ route('channels.create') }}" class="btn btn-sm btn-primary rounded-3">Tambah Channel Pertama</a>
                    @endcan
                </x-empty-state>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small">
                            <tr>
                                <th>Platform</th>
                                <th>Handle / Nama Akun</th>
                                @if(auth()->user()->isAdmin())
                                    <th>Pemilik (Creator)</th>
                                @endif
                                <th>Jumlah Konten</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($channels as $channel)
                                <tr>
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            <i class="bi bi-{{ $channel->platform === 'youtube' ? 'youtube text-danger' : ($channel->platform === 'tiktok' ? 'tiktok text-dark' : ($channel->platform === 'instagram' ? 'instagram text-danger' : 'linkedin text-primary')) }} me-1"></i>
                                            {{ $channel->platform === 'youtube' ? 'YouTube' : ($channel->platform === 'tiktok' ? 'TikTok' : ($channel->platform === 'linkedin' ? 'LinkedIn' : ucfirst($channel->platform))) }}
                                        </span>
                                    </td>
                                    <td class="fw-semibold">{{ $channel->handle_name }}</td>
                                    @if(auth()->user()->isAdmin())
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center fw-semibold small" style="width: 28px; height: 28px; font-size: 0.8rem;">
                                                    {{ strtoupper(substr($channel->user->name ?? 'C', 0, 1)) }}
                                                </div>
                                                <div>
                                                    <div class="fw-medium text-dark small">{{ $channel->user->name ?? 'Tidak Diketahui' }}</div>
                                                    <div class="text-muted" style="font-size: 0.75rem;">{{ $channel->user->email ?? '' }}</div>
                                                </div>
                                            </div>
                                        </td>
                                    @endif
                                    <td><span class="badge bg-secondary-subtle text-secondary">{{ $channel->contents_count }} Konten</span></td>
                                    <td class="text-end">
                                        @if(auth()->user()->isAdmin())
                                            <a href="{{ route('channels.edit', $channel) }}" class="btn btn-sm btn-outline-secondary rounded-3" title="Detail & Hapus Channel">
                                                <i class="bi bi-eye me-1"></i> Detail / Hapus
                                            </a>
                                        @else
                                            <a href="{{ route('channels.edit', $channel) }}" class="btn btn-sm btn-outline-secondary rounded-3">
                                                <i class="bi bi-pencil me-1"></i> Edit
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection
