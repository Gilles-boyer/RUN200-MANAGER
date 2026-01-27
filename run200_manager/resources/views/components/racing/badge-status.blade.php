@props([
    'status' => 'neutral', // pending, success, danger, warning, info, neutral
    'size' => 'md', // sm, md
    'icon' => null,
    'pulse' => false, // Add pulse animation for pending status
])

@php
    $statusClasses = match($status) {
        'pending' => 'badge-racing-pending',
        'success' => 'badge-racing-success',
        'danger' => 'badge-racing-danger',
        'warning' => 'badge-racing-warning',
        'info' => 'badge-racing-info',
        'neutral' => 'badge-racing-neutral',
        // Registration statuses
        'PENDING', 'PENDING_VALIDATION' => 'badge-racing-pending',
        'ACCEPTED', 'VALIDATED' => 'badge-racing-success',
        'REFUSED', 'REJECTED', 'CANCELLED' => 'badge-racing-danger',
        default => 'badge-racing-neutral',
    };

    $sizeClasses = match($size) {
        'sm' => 'text-[0.625rem] px-2 py-0.5',
        default => '',
    };

    $pulseClass = ($pulse || $status === 'pending' || $status === 'PENDING' || $status === 'PENDING_VALIDATION')
        ? 'animate-pulse-pending'
        : '';

    $defaultIcons = [
        'pending' => '⏳',
        'PENDING' => '⏳',
        'PENDING_VALIDATION' => '⏳',
        'success' => '✓',
        'ACCEPTED' => '✓',
        'VALIDATED' => '✓',
        'danger' => '✗',
        'REFUSED' => '✗',
        'REJECTED' => '✗',
        'CANCELLED' => '✗',
        'warning' => '⚠',
        'info' => 'ℹ',
        'neutral' => '',
    ];

    $displayIcon = $icon ?? ($defaultIcons[$status] ?? '');
@endphp

<span {{ $attributes->merge(['class' => "badge-racing {$statusClasses} {$sizeClasses} {$pulseClass}"]) }}>
    @if($displayIcon)
        <span>{{ $displayIcon }}</span>
    @endif
    <span>{{ $slot }}</span>
</span>
