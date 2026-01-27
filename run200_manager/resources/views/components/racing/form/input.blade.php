{{--
    Racing Input Component
    A styled input field with label, error handling, and icons
--}}
@props([
    'type' => 'text',
    'name' => '',
    'label' => null,
    'placeholder' => '',
    'value' => null,
    'required' => false,
    'disabled' => false,
    'readonly' => false,
    'autocomplete' => null,
    'hint' => null,
    'icon' => null,
    'iconPosition' => 'left',
    'prefix' => null,
    'suffix' => null,
])

@php
    $id = $attributes->get('id', $name);
    $hasError = $errors->has($name);
    $wireModel = $attributes->whereStartsWith('wire:model')->first();
    $currentValue = old($name, $value);

    // Icon SVG paths
    $icons = [
        'user' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>',
        'email' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>',
        'phone' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>',
        'lock' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>',
        'search' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>',
        'calendar' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>',
        'tag' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>',
        'car' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>',
        'credit-card' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>',
        'hashtag' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/>',
    ];
    $iconPath = $icons[$icon] ?? null;
@endphp

<div {{ $attributes->only('class')->merge(['class' => 'racing-input-wrapper']) }}>
    {{-- Label --}}
    @if($label)
        <label for="{{ $id }}" class="block text-sm font-medium text-carbon-700 dark:text-carbon-300 mb-1.5">
            {{ $label }}
            @if($required)
                <span class="text-racing-red-500 ml-0.5">*</span>
            @endif
        </label>
    @endif

    {{-- Input Container --}}
    <div class="relative">
        {{-- Prefix --}}
        @if($prefix)
            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                <span class="text-carbon-500 dark:text-carbon-400 text-sm">{{ $prefix }}</span>
            </div>
        @endif

        {{-- Icon Left --}}
        @if($icon && $iconPosition === 'left' && $iconPath)
            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                <svg class="w-5 h-5 text-carbon-400 dark:text-carbon-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    {!! $iconPath !!}
                </svg>
            </div>
        @endif

        {{-- Input Field --}}
        <input
            type="{{ $type }}"
            name="{{ $name }}"
            id="{{ $id }}"
            @if($wireModel) wire:model="{{ $wireModel }}" @endif
            @if($currentValue && !$wireModel) value="{{ $currentValue }}" @endif
            @if($placeholder) placeholder="{{ $placeholder }}" @endif
            @if($autocomplete) autocomplete="{{ $autocomplete }}" @endif
            @if($required) required @endif
            @if($disabled) disabled @endif
            @if($readonly) readonly @endif
            {{ $attributes->except(['class', 'id'])->merge([
                'class' => implode(' ', array_filter([
                    'racing-input w-full rounded-xl border bg-white dark:bg-carbon-900 text-carbon-900 dark:text-carbon-100',
                    'placeholder:text-carbon-400 dark:placeholder:text-carbon-500',
                    'transition-all duration-200 ease-out',
                    'focus:outline-none focus:ring-2 focus:ring-racing-red-500/20 focus:border-racing-red-500',
                    $hasError
                        ? 'border-status-danger ring-2 ring-status-danger/20'
                        : 'border-carbon-300 dark:border-carbon-700',
                    ($icon && $iconPosition === 'left') || $prefix ? 'pl-10' : 'pl-4',
                    ($icon && $iconPosition === 'right') || $suffix ? 'pr-10' : 'pr-4',
                    'py-2.5 text-sm',
                    $disabled ? 'opacity-50 cursor-not-allowed bg-carbon-100 dark:bg-carbon-800' : '',
                    $readonly ? 'bg-carbon-50 dark:bg-carbon-800/50' : '',
                    $type === 'date' ? 'dark:[color-scheme:dark]' : '',
                ]))
            ]) }}
        />

        {{-- Icon Right --}}
        @if($icon && $iconPosition === 'right' && $iconPath)
            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                <svg class="w-5 h-5 text-carbon-400 dark:text-carbon-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    {!! $iconPath !!}
                </svg>
            </div>
        @endif

        {{-- Suffix --}}
        @if($suffix)
            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                <span class="text-carbon-500 dark:text-carbon-400 text-sm">{{ $suffix }}</span>
            </div>
        @endif

        {{-- Error Icon --}}
        @if($hasError)
            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none {{ $suffix ? 'pr-12' : '' }}">
                <svg class="w-5 h-5 text-status-danger" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        @endif
    </div>

    {{-- Hint Text --}}
    @if($hint && !$hasError)
        <p class="mt-1.5 text-xs text-carbon-500 dark:text-carbon-400">{{ $hint }}</p>
    @endif

    {{-- Error Message --}}
    @if($hasError)
        <p class="mt-1.5 text-xs text-status-danger flex items-center gap-1">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ $errors->first($name) }}
        </p>
    @endif
</div>
