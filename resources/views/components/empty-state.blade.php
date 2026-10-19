@props([
    'icon' => 'bi-folder2-open',
    'title' => 'Belum ada data',
    'description' => null,
])

<div class="text-center py-5 px-3">
    <div class="d-inline-flex align-items-center justify-content-center bg-light text-secondary rounded-circle mb-3 p-3 shadow-sm" style="width: 64px; height: 64px;">
        <i class="bi {{ $icon }} fs-3 text-muted"></i>
    </div>
    <h5 class="fw-bold text-dark mb-1">{{ $title }}</h5>
    @if ($description)
        <p class="text-muted small mb-3 mx-auto" style="max-width: 420px;">{{ $description }}</p>
    @endif
    @if ($slot->isNotEmpty())
        <div class="mt-2">
            {{ $slot }}
        </div>
    @endif
</div>
