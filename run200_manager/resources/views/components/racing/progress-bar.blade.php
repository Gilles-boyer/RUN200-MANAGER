@props([
    'value' => 0,
    'max' => 100,
    'showLabel' => false,
    'animated' => true,
    'size' => 'md', // sm, md, lg
])

@php
    $percentage = $max > 0 ? min(100, ($value / $max) * 100) : 0;

    $sizeClasses = match($size) {
        'sm' => 'h-1',
        'lg' => 'h-3',
        default => 'h-2',
    };

    $animatedClass = $animated ? 'progress-racing-bar-animated' : '';
@endphp

<div {{ $attributes->merge(['class' => 'w-full']) }}>
    @if($showLabel)
        <div class="flex justify-between items-center mb-1">
            <span class="text-sm text-carbon-600 dark:text-carbon-400">Progression</span>
            <span class="text-sm font-medium text-carbon-900 dark:text-white">{{ round($percentage) }}%</span>
        </div>
    @endif

    <div class="progress-racing {{ $sizeClasses }}">
        <div
            class="progress-racing-bar {{ $animatedClass }}"
            style="width: {{ $percentage }}%"
            role="progressbar"
            aria-valuenow="{{ $value }}"
            aria-valuemin="0"
            aria-valuemax="{{ $max }}"
        ></div>
    </div>
</div>
