{{--
    Racing Confirm Dialog Component
    A styled confirmation dialog with actions
--}}
@props([
    'name' => 'confirm',
    'title' => 'Confirmation',
    'message' => 'Êtes-vous sûr de vouloir continuer ?',
    'confirmText' => 'Confirmer',
    'cancelText' => 'Annuler',
    'variant' => 'danger', // danger, warning, info
    'icon' => null,
])

@php
    $iconColors = [
        'danger' => 'bg-status-danger/10 text-status-danger',
        'warning' => 'bg-status-warning/10 text-status-warning',
        'info' => 'bg-status-info/10 text-status-info',
        'success' => 'bg-status-success/10 text-status-success',
    ];
    $iconColorClass = $iconColors[$variant] ?? $iconColors['danger'];

    $buttonVariants = [
        'danger' => 'danger',
        'warning' => 'primary',
        'info' => 'primary',
        'success' => 'success',
    ];
    $buttonVariant = $buttonVariants[$variant] ?? 'danger';

    // Default icons per variant
    $defaultIcons = [
        'danger' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>',
        'warning' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
        'info' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
        'success' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
    ];
    $iconSvg = $icon ?? ($defaultIcons[$variant] ?? $defaultIcons['danger']);
@endphp

<x-racing.modal :name="$name" maxWidth="sm" :closeable="true">
    <div class="text-center py-4">
        {{-- Icon --}}
        <div class="mx-auto w-14 h-14 rounded-full {{ $iconColorClass }} flex items-center justify-center mb-4">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                {!! $iconSvg !!}
            </svg>
        </div>

        {{-- Title --}}
        <h4 class="text-lg font-semibold text-carbon-900 dark:text-white mb-2">
            {{ $title }}
        </h4>

        {{-- Message --}}
        <p class="text-sm text-carbon-600 dark:text-carbon-400">
            {{ $message }}
        </p>

        {{-- Custom content slot --}}
        @if($slot->isNotEmpty())
            <div class="mt-4">
                {{ $slot }}
            </div>
        @endif
    </div>

    <x-slot:footer>
        <x-racing.button
            variant="secondary"
            size="sm"
            x-on:click="open = false"
        >
            {{ $cancelText }}
        </x-racing.button>

        <x-racing.button
            :variant="$buttonVariant"
            size="sm"
            x-on:click="$dispatch('confirm-{{ $name }}'); open = false"
        >
            {{ $confirmText }}
        </x-racing.button>
    </x-slot:footer>
</x-racing.modal>
