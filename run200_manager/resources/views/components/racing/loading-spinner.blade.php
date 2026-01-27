@props([
    'size' => 'md', // sm, md, lg
    'label' => null,
    'overlay' => false,
])

@php
    $sizeClasses = match($size) {
        'sm' => 'spinner-racing-sm',
        'lg' => 'spinner-racing-lg',
        default => '',
    };
@endphp

@if($overlay)
    <div {{ $attributes->merge(['class' => 'fixed inset-0 bg-black/50 flex items-center justify-center z-50']) }}>
        <div class="bg-white dark:bg-carbon-800 rounded-xl p-6 flex flex-col items-center gap-4 shadow-xl">
            <div class="spinner-racing {{ $sizeClasses }}"></div>
            @if($label)
                <p class="text-sm text-carbon-600 dark:text-carbon-300">{{ $label }}</p>
            @endif
        </div>
    </div>
@else
    <div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center gap-3']) }}>
        <div class="spinner-racing {{ $sizeClasses }}"></div>
        @if($label)
            <p class="text-sm text-carbon-600 dark:text-carbon-300">{{ $label }}</p>
        @endif
    </div>
@endif
