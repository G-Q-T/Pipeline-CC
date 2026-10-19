@extends('layouts.app')

@section('title', 'Pipeline Kanban')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('contents.index') }}">Pipeline Konten</a></li>
    <li class="breadcrumb-item active">Kanban</li>
@endsection

@section('topbar_actions')
    <a href="{{ route('contents.index') }}" class="btn btn-sm btn-outline-secondary rounded-3">
        <i class="bi bi-table me-1"></i> Mode Tabel
    </a>
    @can('create', \App\Models\Content::class)
        <a href="{{ route('contents.create') }}" class="btn btn-sm btn-primary rounded-3 shadow-sm">
            <i class="bi bi-plus-lg me-1"></i> Buat Konten
        </a>
    @endcan
@endsection

@section('content')
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-1">Pipeline Konten — Papan Kanban</h4>
            <p class="text-muted small mb-0">Geser (drag & drop) atau gunakan tombol panah untuk memindahkan status produksi.</p>
        </div>
    </div>

    @livewire('pipeline-kanban')
@endsection
