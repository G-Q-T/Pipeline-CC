@extends('layouts.app')

@section('title', 'Manajemen Pengguna')

@section('breadcrumb')
    <li class="breadcrumb-item active">Manajemen Pengguna</li>
@endsection

@section('content')
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-1">Manajemen Pengguna</h4>
            <p class="text-muted small mb-0">Kelola akun, hak akses role, dan distribusi penugasan tim.</p>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('admin.users.index') }}" class="row g-2 align-items-center">
                <div class="col-12 col-md-5">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text"
                               class="form-control border-start-0"
                               name="search"
                               placeholder="Cari nama atau email..."
                               value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-6 col-md-3">
                    <select class="form-select form-select-sm" name="role" onchange="this.form.submit()">
                        <option value="all">Semua Peran (Role)</option>
                        <option value="creator" {{ request('role') === 'creator' ? 'selected' : '' }}>Creator</option>
                        <option value="editor" {{ request('role') === 'editor' ? 'selected' : '' }}>Editor</option>
                        <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Administrator</option>
                    </select>
                </div>

                <div class="col-6 col-md-2">
                    <button type="submit" class="btn btn-sm btn-primary w-100">
                        <i class="bi bi-filter me-1"></i> Terapkan
                    </button>
                </div>

                @if(request('search') || (request('role') && request('role') !== 'all'))
                    <div class="col-12 col-md-2">
                        <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-secondary w-100">
                            <i class="bi bi-x-circle me-1"></i> Reset
                        </a>
                    </div>
                @endif
            </form>
        </div>
    </div>

    <!-- Users Table -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-0">
            @if ($users->isEmpty())
                <x-empty-state icon="bi-people" title="Pengguna tidak ditemukan" description="Tidak ada data pengguna yang sesuai dengan kriteria pencarian." />
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small">
                            <tr>
                                <th>Nama Lengkap</th>
                                <th>Alamat Email</th>
                                <th>Peran (Role)</th>
                                <th>Konten / Channel</th>
                                <th>Tanggal Bergabung</th>
                                <th class="text-end">Ubah Role</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $u)
                                <tr>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $u->name }}</div>
                                        @if ($u->id === auth()->id())
                                            <span class="badge bg-light text-muted border" style="font-size: 0.65rem;">Akun Anda</span>
                                        @endif
                                    </td>
                                    <td class="small">{{ $u->email }}</td>
                                    <td>
                                        <span class="badge {{ $u->isAdmin() ? 'bg-danger-subtle text-danger border-danger-subtle' : ($u->isEditor() ? 'bg-info-subtle text-info border-info-subtle' : 'bg-primary-subtle text-primary border-primary-subtle') }} border px-2.5 py-1">
                                            {{ strtoupper($u->role) }}
                                        </span>
                                    </td>
                                    <td class="small text-muted">
                                        @if ($u->isCreator())
                                            {{ $u->contents_count }} konten &bull; {{ $u->channels_count }} channel
                                        @elseif ($u->isEditor())
                                            {{ $u->assigned_contents_count }} penugasan
                                        @else
                                            Sistem Admin
                                        @endif
                                    </td>
                                    <td class="small text-muted">{{ $u->created_at->format('d M Y') }}</td>
                                    <td class="text-end">
                                        <form method="POST" action="{{ route('admin.users.role.update', $u) }}" class="d-inline-flex gap-1 align-items-center">
                                            @csrf
                                            @method('PATCH')
                                            <select name="role" class="form-select form-select-sm py-0 px-2" style="width: auto; font-size: 0.8rem;" onchange="if(confirm('Ubah role pengguna {{ $u->name }} menjadi ' + this.value.toUpperCase() + '?')) { this.form.submit(); } else { this.value = '{{ $u->role }}'; }">
                                                <option value="creator" {{ $u->role === 'creator' ? 'selected' : '' }}>Creator</option>
                                                <option value="editor" {{ $u->role === 'editor' ? 'selected' : '' }}>Editor</option>
                                                <option value="admin" {{ $u->role === 'admin' ? 'selected' : '' }}>Admin</option>
                                            </select>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-3 border-top">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
