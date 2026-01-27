{{--
    Racing Checkbox Component
    A styled checkbox with label and description
--}}
@props([
    'name' => '',
    'label' => null,
    'description' => null,
    'checked' => false,
    'disabled' => false,
    'value' => '1',
])

@php
    $id = $attributes->get('id', $name);
    $hasError = $errors->has($name);
    $wireModel = $attributes->whereStartsWith('wire:model')->first();
    $isChecked = old($name, $checked);
@endphp

<div {{ $attributes->only('class')->merge(['class' => 'racing-checkbox-wrapper']) }}>
    <label for="{{ $id }}" class="flex items-start gap-3 cursor-pointer group">
        {{-- Checkbox Input --}}
        <div class="relative flex-shrink-0 mt-0.5">
            <input
                type="checkbox"
                name="{{ $name }}"
                id="{{ $id }}"
                value="{{ $value }}"
                @if($wireModel) wire:model="{{ $wireModel }}" @endif
                @if($isChecked) checked @endif
                @if($disabled) disabled @endif
                class="peer sr-only"
            />
            {{-- Custom Checkbox --}}
            <div @class([
                'w-5 h-5 rounded-md border-2 transition-all duration-200 ease-out flex items-center justify-center',
                'peer-focus:ring-2 peer-focus:ring-racing-red-500/20',
                'peer-checked:bg-racing-red-500 peer-checked:border-racing-red-500',
                'peer-disabled:opacity-50 peer-disabled:cursor-not-allowed',
                $hasError
                    ? 'border-status-danger'
                    : 'border-carbon-300 dark:border-carbon-600 group-hover:border-racing-red-400',
            ])>
                {{-- Check Icon --}}
                <svg
                    class="w-3 h-3 text-white opacity-0 peer-checked:opacity-100 transition-opacity duration-150"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            {{-- Animated Check (shows when checked) --}}
            <svg
                class="absolute inset-0 w-5 h-5 text-white pointer-events-none opacity-0 peer-checked:opacity-100 transition-opacity duration-150"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
            >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
            </svg>
        </div>

        {{-- Label & Description --}}
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

            {{-- Slot for additional content --}}
            {{ $slot }}
        </div>
    </label>

    {{-- Error Message --}}
    @if($hasError)
        <p class="mt-1.5 text-xs text-status-danger flex items-center gap-1 ml-8">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ $errors->first($name) }}
        </p>
    @endif
</div>
