{{--
    Racing Select Component
    A styled select dropdown with label, error handling, and icons
--}}
@props([
    'name' => '',
    'label' => null,
    'placeholder' => 'Sélectionnez une option',
    'required' => false,
    'disabled' => false,
    'options' => [],
    'selected' => null,
    'hint' => null,
    'icon' => null,
    'multiple' => false,
])

@php
    $id = $attributes->get('id', $name);
    $hasError = $errors->has($name);
    $wireModel = $attributes->whereStartsWith('wire:model')->first();
    $currentValue = old($name, $selected);

    // Icon SVG paths
    $icons = [
        'flag' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/>',
        'car' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>',
        'user' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>',
        'tag' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>',
        'calendar' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>',
        'location' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>',
    ];
    $iconPath = $icons[$icon] ?? null;
@endphp

<div {{ $attributes->only('class')->merge(['class' => 'racing-select-wrapper']) }}>
    {{-- Label --}}
    @if($label)
        <label for="{{ $id }}" class="block text-sm font-medium text-carbon-700 dark:text-carbon-300 mb-1.5">
            {{ $label }}
            @if($required)
                <span class="text-racing-red-500 ml-0.5">*</span>
            @endif
        </label>
    @endif

    {{-- Select Container --}}
    <div class="relative">
        {{-- Icon --}}
        @if($icon && $iconPath)
            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none z-10">
                <svg class="w-5 h-5 text-carbon-400 dark:text-carbon-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    {!! $iconPath !!}
                </svg>
            </div>
        @endif

        {{-- Select Field --}}
        <select
            name="{{ $name }}"
            id="{{ $id }}"
            @if($wireModel) wire:model="{{ $wireModel }}" @endif
            @if($required) required @endif
            @if($disabled) disabled @endif
            @if($multiple) multiple @endif
            {{ $attributes->except(['class', 'id'])->merge([
                'class' => implode(' ', array_filter([
                    'racing-select w-full rounded-xl border bg-white dark:bg-carbon-900 text-carbon-900 dark:text-carbon-100',
                    'transition-all duration-200 ease-out appearance-none cursor-pointer',
                    'focus:outline-none focus:ring-2 focus:ring-racing-red-500/20 focus:border-racing-red-500',
                    $hasError
                        ? 'border-status-danger ring-2 ring-status-danger/20'
                        : 'border-carbon-300 dark:border-carbon-700',
                    $icon ? 'pl-10' : 'pl-4',
                    'pr-10 py-2.5 text-sm',
                    $disabled ? 'opacity-50 cursor-not-allowed bg-carbon-100 dark:bg-carbon-800' : '',
                ]))
            ]) }}
        >
            @if($placeholder && !$multiple)
                <option value="" disabled {{ !$currentValue ? 'selected' : '' }}>{{ $placeholder }}</option>
            @endif

            {{-- Options from array --}}
            @foreach($options as $key => $optionLabel)
                @if(is_array($optionLabel) && isset($optionLabel['group']))
                    {{-- Option Group --}}
                    <optgroup label="{{ $optionLabel['group'] }}">
                        @foreach($optionLabel['options'] ?? [] as $optKey => $optLabel)
                            <option
                                value="{{ $optKey }}"
                                {{ (is_array($currentValue) ? in_array($optKey, $currentValue) : $currentValue == $optKey) ? 'selected' : '' }}
                            >
                                {{ $optLabel }}
                            </option>
                        @endforeach
                    </optgroup>
                @else
                    <option
                        value="{{ $key }}"
                        {{ (is_array($currentValue) ? in_array($key, $currentValue) : $currentValue == $key) ? 'selected' : '' }}
                    >
                        {{ $optionLabel }}
                    </option>
                @endif
            @endforeach

            {{-- Slot for custom options --}}
            {{ $slot }}
        </select>

        {{-- Dropdown Arrow --}}
        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
            <svg class="w-5 h-5 text-carbon-400 dark:text-carbon-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </div>
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
