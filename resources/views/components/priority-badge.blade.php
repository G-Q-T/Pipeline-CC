@props(['priority'])

@php
    $labels = [
        'low' => 'Rendah',
        'medium' => 'Sedang',
        'high' => 'Tinggi',
    ];

    $icons = [
        'low' => 'bi-arrow-down-short',
        'medium' => 'bi-dash',
        'high' => 'bi-arrow-up-short',
    ];

    $label = $labels[$priority] ?? ucfirst($priority);
    $icon = $icons[$priority] ?? 'bi-dash';
@endphp

<span class="badge badge-priority-{{ $priority }} rounded-pill px-2 py-1 fw-medium d-inline-flex align-items-center">
    <i class="bi {{ $icon }} me-0.5"></i> {{ $label }}
</span>
