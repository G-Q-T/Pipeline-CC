<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Pipeline CC') — Content Creator Management</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body>
    <div id="sidebarBackdrop" class="sidebar-backdrop d-none" onclick="toggleSidebar()"></div>

    <!-- App Sidebar -->
    <aside class="app-sidebar" id="appSidebar">
        <!-- Brand -->
        <div class="px-4 py-3 d-flex align-items-center justify-content-between border-bottom">
            <a href="{{ route('dashboard') }}" class="d-flex align-items-center gap-2 text-decoration-none text-dark">
                <div class="bg-primary text-white rounded-3 p-2 d-flex align-items-center justify-content-center shadow-sm" style="width: 36px; height: 36px;">
                    <i class="bi bi-camera-reels fs-5"></i>
                </div>
                <div>
                    <h6 class="fw-bold mb-0 text-dark">Pipeline CC</h6>
                    <small class="text-muted" style="font-size: 0.75rem;">Production Hub</small>
                </div>
            </a>
            <button type="button" class="btn btn-sm btn-light d-lg-none" onclick="toggleSidebar()">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <!-- Navigation Links -->
        <div class="p-3 flex-grow-1 overflow-y-auto">
            <div class="text-uppercase text-muted fw-bold px-3 mb-2" style="font-size: 0.7rem; letter-spacing: 0.05em;">Menu Utama</div>

            <a href="{{ route('dashboard') }}" class="sidebar-nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="bi bi-grid-1x2"></i>
                <span>Dashboard</span>
            </a>

            <div class="text-uppercase text-muted fw-bold px-3 mt-3 mb-2" style="font-size: 0.7rem; letter-spacing: 0.05em;">Produksi Konten</div>

            <a href="{{ route('contents.index') }}" class="sidebar-nav-link {{ request()->routeIs('contents.index') && !request()->routeIs('contents.kanban') ? 'active' : '' }}">
                <i class="bi bi-table"></i>
                <span>Pipeline Tabel</span>
            </a>

            <a href="{{ route('contents.kanban') }}" class="sidebar-nav-link {{ request()->routeIs('contents.kanban') ? 'active' : '' }}">
                <i class="bi bi-kanban"></i>
                <span>Pipeline Kanban</span>
            </a>

            <a href="{{ route('metrics.index') }}" class="sidebar-nav-link {{ request()->routeIs('metrics.*') ? 'active' : '' }}">
                <i class="bi bi-graph-up"></i>
                <span>Analitik & Metrik</span>
            </a>

            @if(auth()->user()->isCreator() || auth()->user()->isAdmin())
                <div class="text-uppercase text-muted fw-bold px-3 mt-3 mb-2" style="font-size: 0.7rem; letter-spacing: 0.05em;">Channel & Distribusi</div>

                <a href="{{ route('channels.index') }}" class="sidebar-nav-link {{ request()->routeIs('channels.*') ? 'active' : '' }}">
                    <i class="bi bi-broadcast-pin"></i>
                    <span>Kelola Channel</span>
                </a>
            @endif

            @if(auth()->user()->isEditor())
                <div class="text-uppercase text-muted fw-bold px-3 mt-3 mb-2" style="font-size: 0.7rem; letter-spacing: 0.05em;">Tugas Saya</div>

                <a href="{{ route('contents.index', ['assigned' => 'me']) }}" class="sidebar-nav-link {{ request()->get('assigned') === 'me' ? 'active' : '' }}">
                    <i class="bi bi-person-workspace"></i>
                    <span>Penugasan Editor</span>
                </a>
            @endif

            @if(auth()->user()->isAdmin())
                <div class="text-uppercase text-muted fw-bold px-3 mt-3 mb-2" style="font-size: 0.7rem; letter-spacing: 0.05em;">Administrasi</div>

                <a href="{{ route('admin.users.index') }}" class="sidebar-nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    <i class="bi bi-people"></i>
                    <span>Manajemen User</span>
                </a>
            @endif
        </div>

        <!-- Sidebar User Card -->
        <div class="p-3 border-top bg-light">
            <div class="d-flex align-items-center gap-2">
                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 38px; height: 38px; font-size: 0.9rem;">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div class="flex-grow-1 overflow-hidden">
                    <div class="fw-semibold text-truncate small">{{ auth()->user()->name }}</div>
                    <div class="d-flex align-items-center gap-1">
                        <span class="badge bg-secondary-subtle text-secondary border px-1.5 py-0.5" style="font-size: 0.65rem;">
                            {{ strtoupper(auth()->user()->role) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </aside>

    <!-- App Main Wrapper -->
    <div class="app-main">
        <!-- Topbar -->
        <header class="app-topbar d-flex align-items-center justify-content-between px-4 sticky-top">
            <div class="d-flex align-items-center gap-3">
                <button type="button" class="btn btn-sm btn-light border d-lg-none" onclick="toggleSidebar()">
                    <i class="bi bi-list fs-5"></i>
                </button>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 small">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-muted">Pipeline CC</a></li>
                        @yield('breadcrumb')
                    </ol>
                </nav>
            </div>

            <div class="d-flex align-items-center gap-3">
                @yield('topbar_actions')

                <div class="dropdown">
                    <button class="btn btn-sm btn-light border d-flex align-items-center gap-2 rounded-3 px-3 py-1.5" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-circle text-primary"></i>
                        <span class="small fw-medium d-none d-sm-inline">{{ auth()->user()->name }}</span>
                        <i class="bi bi-chevron-down small text-muted"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3 mt-1">
                        <li class="px-3 py-2 border-bottom">
                            <div class="small fw-bold">{{ auth()->user()->name }}</div>
                            <div class="text-muted" style="font-size: 0.75rem;">{{ auth()->user()->email }}</div>
                            <span class="badge bg-primary-subtle text-primary border mt-1" style="font-size: 0.7rem;">Role: {{ ucfirst(auth()->user()->role) }}</span>
                        </li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}" class="p-1">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger rounded-2 small d-flex align-items-center gap-2">
                                    <i class="bi bi-box-arrow-right"></i> Keluar (Logout)
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <main class="flex-grow-1 p-4">
            <div class="container-fluid px-0">
                <x-alerts />
                @yield('content')
            </div>
        </main>

        <!-- Footer -->
        <footer class="py-3 px-4 bg-white border-top text-muted small d-flex flex-wrap justify-content-between align-items-center">
            <span>&copy; {{ date('Y') }} Pipeline CC — Sistem Manajemen Siklus Produksi Konten.</span>
            <span>Versi MVP 1.0</span>
        </footer>
    </div>

    @livewireScripts
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('appSidebar');
            const backdrop = document.getElementById('sidebarBackdrop');
            sidebar.classList.toggle('show');
            backdrop.classList.toggle('d-none');
        }
    </script>
    @stack('scripts')
</body>
</html>
