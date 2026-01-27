{{--
    Racing Textarea Component
    A styled textarea with label, error handling, and character count
--}}
@props([
    'name' => '',
    'label' => null,
    'placeholder' => '',
    'value' => null,
    'required' => false,
    'disabled' => false,
    'readonly' => false,
    'rows' => 4,
    'maxlength' => null,
    'hint' => null,
    'showCount' => false,
    'resize' => 'vertical', // none, vertical, horizontal, both
])

@php
    $id = $attributes->get('id', $name);
    $hasError = $errors->has($name);
    $wireModel = $attributes->whereStartsWith('wire:model')->first();
    $currentValue = old($name, $value);

    $resizeClasses = [
        'none' => 'resize-none',
        'vertical' => 'resize-y',
        'horizontal' => 'resize-x',
        'both' => 'resize',
    ];
@endphp

<div
    {{ $attributes->only('class')->merge(['class' => 'racing-textarea-wrapper']) }}
    @if($showCount && $maxlength)
        x-data="{ count: {{ strlen($currentValue ?? '') }} }"
    @endif
>
    {{-- Label --}}
    @if($label)
        <label for="{{ $id }}" class="block text-sm font-medium text-carbon-700 dark:text-carbon-300 mb-1.5">
            {{ $label }}
            @if($required)
                <span class="text-racing-red-500 ml-0.5">*</span>
            @endif
        </label>
    @endif

    {{-- Textarea Container --}}
    <div class="relative">
        <textarea
            name="{{ $name }}"
            id="{{ $id }}"
            rows="{{ $rows }}"
            @if($wireModel) wire:model="{{ $wireModel }}" @endif
            @if($placeholder) placeholder="{{ $placeholder }}" @endif
            @if($maxlength) maxlength="{{ $maxlength }}" @endif
            @if($required) required @endif
            @if($disabled) disabled @endif
            @if($readonly) readonly @endif
            @if($showCount && $maxlength) x-on:input="count = $el.value.length" @endif
            {{ $attributes->except(['class', 'id'])->merge([
                'class' => implode(' ', array_filter([
                    'racing-textarea w-full rounded-xl border bg-white dark:bg-carbon-900 text-carbon-900 dark:text-carbon-100',
                    'placeholder:text-carbon-400 dark:placeholder:text-carbon-500',
                    'transition-all duration-200 ease-out',
                    'focus:outline-none focus:ring-2 focus:ring-racing-red-500/20 focus:border-racing-red-500',
                    $hasError
                        ? 'border-status-danger ring-2 ring-status-danger/20'
                        : 'border-carbon-300 dark:border-carbon-700',
                    'px-4 py-3 text-sm',
                    $resizeClasses[$resize] ?? 'resize-y',
                    $disabled ? 'opacity-50 cursor-not-allowed bg-carbon-100 dark:bg-carbon-800' : '',
                    $readonly ? 'bg-carbon-50 dark:bg-carbon-800/50' : '',
                ]))
            ]) }}
        >{{ $currentValue }}</textarea>
    </div>

    {{-- Footer: Hint & Character Count --}}
    <div class="flex items-center justify-between mt-1.5">
        {{-- Hint Text --}}
        @if($hint && !$hasError)
            <p class="text-xs text-carbon-500 dark:text-carbon-400">{{ $hint }}</p>
        @elseif($hasError)
            <p class="text-xs text-status-danger flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ $errors->first($name) }}
            </p>
        @else
            <span></span>
        @endif

        {{-- Character Count --}}
        @if($showCount && $maxlength)
            <p class="text-xs" :class="count > {{ $maxlength * 0.9 }} ? 'text-status-warning' : 'text-carbon-500 dark:text-carbon-400'">
                <span x-text="count">{{ strlen($currentValue ?? '') }}</span> / {{ $maxlength }}
            </p>
        @endif
    </div>
</div>
