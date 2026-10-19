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
                <div class="col-12 col-md-5">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text"
                               class="form-control border-start-0"
                               placeholder="Cari judul kartu..."
                               wire:model.live.debounce.300ms="search">
                    </div>
                </div>

                <!-- Channel Filter -->
                <div class="col-6 col-md-4">
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

                <div class="col-12 col-md-1 text-end">
                    @if ($search !== '' || $channelFilter !== 'all' || $priorityFilter !== 'all')
                        <button type="button" class="btn btn-sm btn-outline-secondary w-100" wire:click="$set('search', ''); $set('channelFilter', 'all'); $set('priorityFilter', 'all');" title="Reset filter">
                            <i class="bi bi-x-circle"></i>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Kanban Board Grid -->
    <div class="d-flex gap-3 overflow-x-auto pb-4 kanban-container" style="min-height: 70vh;">
        @php
            $columnDefinitions = [
                'idea' => ['title' => 'Idea', 'icon' => 'bi-lightbulb', 'color' => 'secondary'],
                'scripting' => ['title' => 'Scripting', 'icon' => 'bi-file-earmark-text', 'color' => 'info'],
                'recording' => ['title' => 'Recording', 'icon' => 'bi-camera-video', 'color' => 'warning'],
                'editing' => ['title' => 'Editing', 'icon' => 'bi-scissors', 'color' => 'primary'],
                'ready' => ['title' => 'Ready', 'icon' => 'bi-check2-circle', 'color' => 'teal'],
                'published' => ['title' => 'Published', 'icon' => 'bi-send-check', 'color' => 'success'],
            ];
        @endphp

        @foreach ($columnDefinitions as $statusKey => $def)
            @php $columnItems = $columns[$statusKey] ?? collect(); @endphp
            <div class="kanban-column flex-shrink-0 bg-white border rounded-4 shadow-sm p-3"
                 style="width: 310px;"
                 ondragover="event.preventDefault(); this.classList.add('bg-light');"
                 ondragleave="this.classList.remove('bg-light');"
                 ondrop="event.preventDefault(); this.classList.remove('bg-light'); const cardId = event.dataTransfer.getData('text/plain'); if(cardId) { @this.moveStatus(cardId, '{{ $statusKey }}'); }">

                <!-- Column Header -->
                <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi {{ $def['icon'] }} text-{{ $def['color'] }} fs-5"></i>
                        <h6 class="fw-bold mb-0 text-dark">{{ $def['title'] }}</h6>
                    </div>
                    <span class="badge bg-light text-muted border px-2 py-1 rounded-pill fw-bold">
                        {{ $columnItems->count() }}
                    </span>
                </div>

                <!-- Column Cards Container -->
                <div class="d-flex flex-column gap-2" style="min-height: 150px;">
                    @if ($columnItems->isEmpty())
                        <div class="text-center py-4 text-muted small border border-dashed rounded-3 bg-light">
                            Belum ada konten
                        </div>
                    @else
                        @foreach ($columnItems as $item)
                            <div class="card border shadow-sm rounded-3 p-3 kanban-card bg-white"
                                 draggable="true"
                                 ondragstart="event.dataTransfer.setData('text/plain', '{{ $item->id }}'); event.dataTransfer.effectAllowed = 'move';"
                                 style="cursor: grab;">

                                <!-- Platform & Priority -->
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="badge bg-light text-dark border small">
                                        <i class="bi bi-{{ $item->channel->platform === 'youtube' ? 'youtube text-danger' : ($item->channel->platform === 'tiktok' ? 'tiktok text-dark' : ($item->channel->platform === 'instagram' ? 'instagram text-danger' : 'linkedin text-primary')) }}"></i>
                                        {{ $item->channel->handle_name ?? '-' }}
                                    </span>
                                    <x-priority-badge :priority="$item->priority" />
                                </div>

                                <!-- Card Title -->
                                <a href="{{ route('contents.show', $item) }}" class="fw-semibold text-dark text-decoration-none small mb-2 d-block">
                                    {{ $item->title }}
                                </a>

                                <!-- Checklist Progress (if exists) -->
                                @if ($item->checklists->count() > 0)
                                    <div class="mb-2">
                                        <div class="d-flex justify-content-between align-items-center small text-muted mb-1" style="font-size: 0.7rem;">
                                            <span>Checklist</span>
                                            <span class="fw-bold">{{ $item->checklist_percentage }}%</span>
                                        </div>
                                        <div class="progress" style="height: 4px;">
                                            <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $item->checklist_percentage }}%"></div>
                                        </div>
                                    </div>
                                @endif

                                <!-- Footer Info & Quick Controls -->
                                <div class="d-flex align-items-center justify-content-between pt-2 border-top mt-1" style="font-size: 0.75rem;">
                                    <div class="text-muted text-truncate" title="Jadwal Rilis">
                                        <i class="bi bi-calendar-event me-1"></i>
                                        {{ $item->scheduled_date ? $item->scheduled_date->format('d M') : '-' }}
                                    </div>

                                    <!-- Accessible Quick Shift Controls -->
                                    <div class="d-flex align-items-center gap-1">
                                        @if (auth()->user()->isEditor())
                                            @if ($item->status !== 'ready' && $item->status !== 'published')
                                                <button type="button"
                                                        class="btn btn-sm btn-outline-success py-0 px-1.5 rounded-2"
                                                        style="font-size: 0.7rem;"
                                                        wire:click="moveStatus({{ $item->id }}, 'ready')"
                                                        title="Tandai selesai & kirim ke Ready">
                                                    <i class="bi bi-check2"></i> Ready
                                                </button>
                                            @elseif ($item->status === 'ready')
                                                <span class="badge bg-info-subtle text-info border" style="font-size: 0.65rem;">Siap Rilis</span>
                                            @endif
                                        @else
                                            @php
                                                $currentIndex = \App\Models\Content::STATUS_ORDER[$item->status];
                                                $prevStatus = null;
                                                $nextStatus = null;
                                                foreach (\App\Models\Content::STATUS_ORDER as $sKey => $orderIdx) {
                                                    if ($orderIdx === $currentIndex - 1) $prevStatus = $sKey;
                                                    if ($orderIdx === $currentIndex + 1) $nextStatus = $sKey;
                                                }
                                            @endphp

                                            @if ($prevStatus)
                                                <button type="button"
                                                        class="btn btn-sm btn-light border py-0 px-1.5"
                                                        wire:click="moveStatus({{ $item->id }}, '{{ $prevStatus }}')"
                                                        title="Mundur ke {{ ucfirst($prevStatus) }}">
                                                    <i class="bi bi-arrow-left"></i>
                                                </button>
                                            @endif

                                            @if ($nextStatus)
                                                @if ($nextStatus === 'published')
                                                    <a href="{{ route('contents.show', $item) }}"
                                                       class="btn btn-sm btn-success py-0 px-1.5"
                                                       title="Buka form untuk menerbitkan">
                                                        <i class="bi bi-send-check"></i>
                                                    </a>
                                                @elseif (auth()->user()->isCreator() && $nextStatus === 'ready' && $item->editors->isNotEmpty())
                                                    <span class="badge bg-warning-subtle text-warning-emphasis border py-1 px-1.5" style="font-size: 0.65rem;" title="Menunggu Editor menyelesaikan pengerjaan sebelum dapat ke Ready">
                                                        <i class="bi bi-hourglass-split"></i> Editor
                                                    </span>
                                                @else
                                                    <button type="button"
                                                            class="btn btn-sm btn-light border py-0 px-1.5"
                                                            wire:click="moveStatus({{ $item->id }}, '{{ $nextStatus }}')"
                                                            title="Maju ke {{ ucfirst($nextStatus) }}">
                                                        <i class="bi bi-arrow-right"></i>
                                                    </button>
                                                @endif
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>
