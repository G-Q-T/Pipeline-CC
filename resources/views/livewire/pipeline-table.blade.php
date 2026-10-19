<div>
    <!-- Flash Messages inside Livewire -->
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-3 rounded-3 shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center mb-3 rounded-3 shadow-sm" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if (session('warning'))
        <div class="alert alert-warning alert-dismissible fade show d-flex align-items-center mb-3 rounded-3 shadow-sm" role="alert">
            <i class="bi bi-exclamation-circle-fill me-2"></i> {{ session('warning') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Search & Filter Controls -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3">
            <div class="row g-2 align-items-center">
                <!-- Search Input -->
                <div class="col-12 col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text"
                               class="form-control border-start-0"
                               placeholder="Cari judul konten..."
                               wire:model.live.debounce.300ms="search">
                    </div>
                </div>

                <!-- Status Filter -->
                <div class="col-6 col-md-2">
                    <select class="form-select form-select-sm" wire:model.live="statusFilter">
                        <option value="all">Semua Status</option>
                        <option value="idea">Idea</option>
                        <option value="scripting">Scripting</option>
                        <option value="recording">Recording</option>
                        <option value="editing">Editing</option>
                        <option value="ready">Ready</option>
                        <option value="published">Published</option>
                    </select>
                </div>

                <!-- Channel Filter -->
                <div class="col-6 col-md-3">
                    <select class="form-select form-select-sm" wire:model.live="channelFilter">
                        <option value="all">Semua Channel</option>
                        @foreach ($channels as $ch)
                            <option value="{{ $ch->id }}">{{ ucfirst($ch->platform) }} - {{ $ch->handle_name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Priority Filter -->
                <div class="col-6 col-md-2">
                    <select class="form-select form-select-sm" wire:model.live="priorityFilter">
                        <option value="all">Semua Prioritas</option>
                        <option value="high">Tinggi (High)</option>
                        <option value="medium">Sedang (Medium)</option>
                        <option value="low">Rendah (Low)</option>
                    </select>
                </div>

                <!-- Reset Filters Button -->
                <div class="col-6 col-md-1 text-end">
                    @if ($search !== '' || $statusFilter !== 'all' || $channelFilter !== 'all' || $priorityFilter !== 'all')
                        <button type="button" class="btn btn-sm btn-outline-secondary w-100" wire:click="$set('search', ''); $set('statusFilter', 'all'); $set('channelFilter', 'all'); $set('priorityFilter', 'all');" title="Reset filter">
                            <i class="bi bi-x-circle"></i> Reset
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Content Table -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-0">
            @if ($contents->isEmpty())
                <x-empty-state icon="bi-table" title="Tidak ada konten ditemukan" description="Coba sesuaikan kata kunci pencarian atau filter yang aktif.">
                    @can('create', \App\Models\Content::class)
                        <a href="{{ route('contents.create') }}" class="btn btn-sm btn-primary rounded-3">Buat Konten Baru</a>
                    @endcan
                </x-empty-state>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small">
                            <tr>
                                <th wire:click="sortBy('title')" style="cursor: pointer;">
                                    Judul Konten
                                    @if ($sortField === 'title')
                                        <i class="bi bi-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-primary"></i>
                                    @endif
                                </th>
                                <th>Channel</th>
                                <th>Status Produksi</th>
                                <th wire:click="sortBy('priority')" style="cursor: pointer;">
                                    Prioritas
                                    @if ($sortField === 'priority')
                                        <i class="bi bi-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-primary"></i>
                                    @endif
                                </th>
                                <th wire:click="sortBy('scheduled_date')" style="cursor: pointer;">
                                    Target Rilis
                                    @if ($sortField === 'scheduled_date')
                                        <i class="bi bi-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-primary"></i>
                                    @endif
                                </th>
                                <th>Editor</th>
                                <th>Checklist</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($contents as $content)
                                <tr>
                                    <!-- Title & Format -->
                                    <td>
                                        <a href="{{ route('contents.show', $content) }}" class="fw-semibold text-dark text-decoration-none d-block">
                                            {{ $content->title }}
                                        </a>
                                        <span class="badge bg-light text-muted border" style="font-size: 0.7rem;">
                                            {{ ucfirst(str_replace('_', ' ', $content->format_type)) }}
                                        </span>
                                    </td>

                                    <!-- Channel -->
                                    <td class="small">
                                        <span class="d-inline-flex align-items-center gap-1">
                                            <i class="bi bi-{{ $content->channel->platform === 'youtube' ? 'youtube text-danger' : ($content->channel->platform === 'tiktok' ? 'tiktok text-dark' : ($content->channel->platform === 'instagram' ? 'instagram text-danger' : 'linkedin text-primary')) }}"></i>
                                            {{ $content->channel->handle_name ?? '-' }}
                                        </span>
                                    </td>

                                    <!-- Status with Allowed Inline Transitions -->
                                    <td>
                                        @php
                                            $allowedTransitions = $content->getAllowedTransitions();
                                            if (auth()->user()->isEditor()) {
                                                $allowedTransitions = in_array('ready', $allowedTransitions) && $content->status !== 'ready' ? ['ready'] : [];
                                            }
                                            if (auth()->user()->isCreator() && $content->editors->isNotEmpty()) {
                                                $allowedTransitions = array_values(array_filter($allowedTransitions, fn ($s) => $s !== \App\Models\Content::STATUS_READY));
                                            }
                                        @endphp
                                        @if (count($allowedTransitions) > 0 && $content->status !== 'published')
                                            <div class="dropdown">
                                                <button class="btn btn-sm p-0 border-0 dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <x-status-badge :status="$content->status" />
                                                </button>
                                                <ul class="dropdown-menu shadow-sm border-0 small">
                                                    <li class="dropdown-header text-uppercase" style="font-size: 0.65rem;">Pindah Status:</li>
                                                    @foreach ($allowedTransitions as $statusOption)
                                                        @if ($statusOption === 'published')
                                                            <li>
                                                                <a class="dropdown-item text-success" href="{{ route('contents.show', $content) }}">
                                                                    <i class="bi bi-send-check me-1"></i> Terbitkan (Buka Form)
                                                                </a>
                                                            </li>
                                                        @else
                                                            <li>
                                                                <button type="button" class="dropdown-item" wire:click="changeStatus({{ $content->id }}, '{{ $statusOption }}')">
                                                                    <i class="bi bi-arrow-right me-1"></i> {{ ucfirst($statusOption) }}
                                                                </button>
                                                            </li>
                                                        @endif
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @else
                                            <x-status-badge :status="$content->status" />
                                        @endif
                                    </td>

                                    <!-- Priority -->
                                    <td>
                                        <x-priority-badge :priority="$content->priority" />
                                    </td>

                                    <!-- Scheduled Date -->
                                    <td class="small text-muted">
                                        {{ $content->scheduled_date ? $content->scheduled_date->format('d M Y') : '-' }}
                                    </td>

                                    <!-- Assigned Editors -->
                                    <td class="small">
                                        @if ($content->editors->isEmpty())
                                            <span class="text-muted">-</span>
                                        @else
                                            <div class="d-flex align-items-center gap-1">
                                                @foreach ($content->editors->take(2) as $ed)
                                                    <span class="badge bg-secondary-subtle text-secondary border" title="{{ $ed->name }}">
                                                        {{ $ed->name }}
                                                    </span>
                                                @endforeach
                                                @if ($content->editors->count() > 2)
                                                    <span class="badge bg-light text-muted border">+{{ $content->editors->count() - 2 }}</span>
                                                @endif
                                            </div>
                                        @endif
                                    </td>

                                    <!-- Checklist Progress -->
                                    <td style="min-width: 100px;">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress flex-grow-1" style="height: 6px;">
                                                <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $content->checklist_percentage }}%"></div>
                                            </div>
                                            <span class="small fw-semibold text-muted" style="font-size: 0.75rem;">{{ $content->checklist_percentage }}%</span>
                                        </div>
                                    </td>

                                    <!-- Actions -->
                                    <td class="text-end text-nowrap">
                                        <a href="{{ route('contents.show', $content) }}" class="btn btn-sm btn-light border rounded-3" title="Detail">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        @can('update', $content)
                                            <a href="{{ route('contents.edit', $content) }}" class="btn btn-sm btn-light border rounded-3" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="p-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <span class="small text-muted">Menampilkan {{ $contents->firstItem() ?? 0 }} - {{ $contents->lastItem() ?? 0 }} dari {{ $contents->total() }} konten</span>
                    <div>{{ $contents->links() }}</div>
                </div>
            @endif
        </div>
    </div>
</div>
