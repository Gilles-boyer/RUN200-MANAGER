@props([
    'variant' => 'primary', // primary, secondary, ghost, danger, success
    'size' => 'md', // sm, md, lg
    'type' => 'button',
    'href' => null,
    'disabled' => false,
    'loading' => false,
    'icon' => null,
    'iconPosition' => 'left', // left, right
])

@php
    $baseClasses = 'btn-racing focus-ring-racing';

    $variantClasses = match($variant) {
        'primary' => 'btn-racing-primary',
        'secondary' => 'btn-racing-secondary',
        'ghost' => 'btn-racing-ghost',
        'danger' => 'btn-racing-danger',
        'success' => 'btn-racing-success',
        default => 'btn-racing-primary',
    };

    $sizeClasses = match($size) {
        'sm' => 'btn-racing-sm',
        'lg' => 'btn-racing-lg',
        default => '',
    };

    $classes = implode(' ', array_filter([$baseClasses, $variantClasses, $sizeClasses]));
@endphp

@if($href && !$disabled)
    <a
        href="{{ $href }}"
        {{ $attributes->merge(['class' => $classes]) }}
    >
        @if($loading)
            <span class="spinner-racing spinner-racing-sm"></span>
        @elseif($icon && $iconPosition === 'left')
            <span class="w-5 h-5">{!! $icon !!}</span>
        @endif

        <span>{{ $slot }}</span>

        @if($icon && $iconPosition === 'right' && !$loading)
            <span class="w-5 h-5">{!! $icon !!}</span>
        @endif
    </a>
@else
    <button
        type="{{ $type }}"
        {{ $attributes->merge(['class' => $classes]) }}
        @if($disabled || $loading) disabled @endif
    >
        @if($loading)
            <span class="spinner-racing spinner-racing-sm"></span>
        @elseif($icon && $iconPosition === 'left')
            <span class="w-5 h-5">{!! $icon !!}</span>
        @endif

        <span>{{ $slot }}</span>

        @if($icon && $iconPosition === 'right' && !$loading)
            <span class="w-5 h-5">{!! $icon !!}</span>
        @endif
    </button>
@endif
