@props(['status'])

@php
    $labels = [
        'idea' => 'Idea',
        'scripting' => 'Scripting',
        'recording' => 'Recording',
        'editing' => 'Editing',
        'ready' => 'Ready',
        'published' => 'Published',
    ];

    $icons = [
        'idea' => 'bi-lightbulb',
        'scripting' => 'bi-file-earmark-text',
        'recording' => 'bi-camera-video',
        'editing' => 'bi-scissors',
        'ready' => 'bi-check2-circle',
        'published' => 'bi-send-check',
    ];

    $label = $labels[$status] ?? ucfirst($status);
    $icon = $icons[$status] ?? 'bi-circle';
@endphp

<span class="badge badge-status-{{ $status }} rounded-pill px-2.5 py-1.5 fw-semibold d-inline-flex align-items-center gap-1">
    <i class="bi {{ $icon }}"></i> {{ $label }}
</span>
