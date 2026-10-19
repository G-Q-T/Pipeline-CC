@extends('layouts.app')

@section('title', 'Pipeline Tabel')

@section('breadcrumb')
    <li class="breadcrumb-item active">Pipeline Tabel</li>
@endsection

@section('topbar_actions')
    <a href="{{ route('contents.kanban') }}" class="btn btn-sm btn-outline-secondary rounded-3">
        <i class="bi bi-kanban me-1"></i> Mode Kanban
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
            <h4 class="fw-bold mb-1">Pipeline Konten — Tabel</h4>
            <p class="text-muted small mb-0">Kelola dan pantau seluruh siklus produksi konten dalam format tabel.</p>
        </div>
    </div>

    @livewire('pipeline-table')
@endsection
