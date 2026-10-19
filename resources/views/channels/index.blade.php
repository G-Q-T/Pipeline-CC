@extends('layouts.app')

@section('title', 'Kelola Channel')

@section('breadcrumb')
    <li class="breadcrumb-item active">Kelola Channel</li>
@endsection

@section('content')
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-1">Channel Media Sosial</h4>
            <p class="text-muted small mb-0">Kelola akun dan channel publikasi konten Anda.</p>
        </div>
        <a href="{{ route('channels.create') }}" class="btn btn-primary btn-sm rounded-3">
            <i class="bi bi-plus-lg me-1"></i> Tambah Channel
        </a>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-0">
            @if ($channels->isEmpty())
                <x-empty-state icon="bi-broadcast-pin" title="Belum ada channel terdaftar" description="Tambahkan channel YouTube, TikTok, Instagram, atau LinkedIn Anda untuk mulai mengaitkan konten.">
                    <a href="{{ route('channels.create') }}" class="btn btn-sm btn-primary rounded-3">Tambah Channel Pertama</a>
                </x-empty-state>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small">
                            <tr>
                                <th>Platform</th>
                                <th>Handle / Nama Akun</th>
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
                                            {{ ucfirst($channel->platform) }}
                                        </span>
                                    </td>
                                    <td class="fw-semibold">{{ $channel->handle_name }}</td>
                                    <td><span class="badge bg-secondary-subtle text-secondary">{{ $channel->contents_count }} Konten</span></td>
                                    <td class="text-end">
                                        <a href="{{ route('channels.edit', $channel) }}" class="btn btn-sm btn-outline-secondary rounded-3">
                                            <i class="bi bi-pencil me-1"></i> Edit
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
@endsection
