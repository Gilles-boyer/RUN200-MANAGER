{{--
    Racing Toggle/Switch Component
    A styled toggle switch
--}}
@props([
    'name' => '',
    'label' => null,
    'description' => null,
    'checked' => false,
    'disabled' => false,
    'size' => 'md', // sm, md, lg
    'color' => 'primary', // primary, success, warning, danger
])

@php
    $id = $attributes->get('id', $name);
    $hasError = $errors->has($name);
    $wireModel = $attributes->whereStartsWith('wire:model')->first();
    $isChecked = old($name, $checked);

    $sizes = [
        'sm' => ['track' => 'w-8 h-4', 'thumb' => 'w-3 h-3', 'translate' => 'translate-x-4'],
        'md' => ['track' => 'w-11 h-6', 'thumb' => 'w-5 h-5', 'translate' => 'translate-x-5'],
        'lg' => ['track' => 'w-14 h-7', 'thumb' => 'w-6 h-6', 'translate' => 'translate-x-7'],
    ];
    $sizeConfig = $sizes[$size] ?? $sizes['md'];

    $colors = [
        'primary' => 'peer-checked:bg-racing-red-500',
        'success' => 'peer-checked:bg-status-success',
        'warning' => 'peer-checked:bg-status-warning',
        'danger' => 'peer-checked:bg-status-danger',
    ];
    $colorClass = $colors[$color] ?? $colors['primary'];
@endphp

<div {{ $attributes->only('class')->merge(['class' => 'racing-toggle-wrapper']) }}>
    <label for="{{ $id }}" class="flex items-center justify-between gap-3 cursor-pointer group">
        {{-- Label & Description (Left) --}}
        @if($label || $description)
            <div class="flex-1">
                @if($label)
                    <span @class([
                        'text-sm font-medium',
                        'text-carbon-900 dark:text-carbon-100' => !$disabled,
                        'text-carbon-500 dark:text-carbon-500' => $disabled,
                    ])>
                        {{ $label }}
                    </span>
                @endif

                @if($description)
                    <p class="text-xs text-carbon-500 dark:text-carbon-400 mt-0.5">
                        {{ $description }}
                    </p>
                @endif
            </div>
        @endif

        {{-- Toggle Switch --}}
        <div class="relative flex-shrink-0">
            <input
                type="checkbox"
                name="{{ $name }}"
                id="{{ $id }}"
                value="1"
                @if($wireModel) wire:model="{{ $wireModel }}" @endif
                @if($isChecked) checked @endif
                @if($disabled) disabled @endif
                class="peer sr-only"
            />

            {{-- Track --}}
            <div @class([
                'rounded-full transition-colors duration-200 ease-out',
                'bg-carbon-300 dark:bg-carbon-600',
                $colorClass,
                $sizeConfig['track'],
                'peer-focus:ring-2 peer-focus:ring-racing-red-500/20',
                'peer-disabled:opacity-50 peer-disabled:cursor-not-allowed',
            ])></div>

            {{-- Thumb --}}
            <div @class([
                'absolute top-0.5 left-0.5 bg-white rounded-full shadow-md transition-transform duration-200 ease-out',
                $sizeConfig['thumb'],
                'peer-checked:' . $sizeConfig['translate'],
            ])></div>
        </div>
    </label>

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
