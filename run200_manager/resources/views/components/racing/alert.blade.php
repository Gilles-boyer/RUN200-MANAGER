@props([
    'type' => 'info', // info, success, warning, danger
    'title' => null,
    'dismissible' => false,
    'icon' => null,
])

@php
    $typeStyles = match($type) {
        'success' => [
            'bg' => 'bg-status-success/10 dark:bg-status-success/20',
            'border' => 'border-status-success',
            'icon' => '✓',
            'iconColor' => 'text-status-success',
            'titleColor' => 'text-green-800 dark:text-green-300',
            'textColor' => 'text-green-700 dark:text-green-400',
        ],
        'warning' => [
            'bg' => 'bg-status-warning/10 dark:bg-status-warning/20',
            'border' => 'border-status-warning',
            'icon' => '⚠',
            'iconColor' => 'text-status-warning',
            'titleColor' => 'text-orange-800 dark:text-orange-300',
            'textColor' => 'text-orange-700 dark:text-orange-400',
        ],
        'danger' => [
            'bg' => 'bg-status-danger/10 dark:bg-status-danger/20',
            'border' => 'border-status-danger',
            'icon' => '✗',
            'iconColor' => 'text-status-danger',
            'titleColor' => 'text-red-800 dark:text-red-300',
            'textColor' => 'text-red-700 dark:text-red-400',
        ],
        default => [
            'bg' => 'bg-status-info/10 dark:bg-status-info/20',
            'border' => 'border-status-info',
            'icon' => 'ℹ',
            'iconColor' => 'text-status-info',
            'titleColor' => 'text-blue-800 dark:text-blue-300',
            'textColor' => 'text-blue-700 dark:text-blue-400',
        ],
    };

    $displayIcon = $icon ?? $typeStyles['icon'];
@endphp

<div
    {{ $attributes->merge(['class' => "{$typeStyles['bg']} border-l-4 {$typeStyles['border']} p-4 rounded-r-lg"]) }}
    @if($dismissible) x-data="{ show: true }" x-show="show" x-transition @endif
>
    <div class="flex">
        <div class="flex-shrink-0">
            <span class="text-xl {{ $typeStyles['iconColor'] }}">{{ $displayIcon }}</span>
        </div>
        <div class="ml-3 flex-1">
            @if($title)
                <h3 class="text-sm font-semibold {{ $typeStyles['titleColor'] }}">
                    {{ $title }}
                </h3>
            @endif
            <div class="text-sm {{ $typeStyles['textColor'] }} @if($title) mt-1 @endif">
                {{ $slot }}
            </div>
        </div>
        @if($dismissible)
            <div class="ml-auto pl-3">
                <button
                    @click="show = false"
                    class="{{ $typeStyles['iconColor'] }} hover:opacity-70 transition-opacity"
                >
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
        @endif
    </div>

    {{-- Optional action slot --}}
    @if(isset($action))
        <div class="mt-4">
            {{ $action }}
        </div>
    @endif
</div>
