{{--
    Racing Radio Group Component
    A styled radio group with card-style options
--}}
@props([
    'name' => '',
    'label' => null,
    'required' => false,
    'options' => [],
    'selected' => null,
    'layout' => 'vertical', // vertical, horizontal, grid
    'cardStyle' => false,
    'hint' => null,
])

@php
    $hasError = $errors->has($name);
    $wireModel = $attributes->whereStartsWith('wire:model')->first();
    $currentValue = old($name, $selected);

    $layoutClasses = [
        'vertical' => 'flex flex-col gap-3',
        'horizontal' => 'flex flex-wrap gap-4',
        'grid' => 'grid grid-cols-2 md:grid-cols-3 gap-3',
    ];
@endphp

<div {{ $attributes->only('class')->merge(['class' => 'racing-radio-group-wrapper']) }}>
    {{-- Label --}}
    @if($label)
        <label class="block text-sm font-medium text-carbon-700 dark:text-carbon-300 mb-3">
            {{ $label }}
            @if($required)
                <span class="text-racing-red-500 ml-0.5">*</span>
            @endif
        </label>
    @endif

    {{-- Radio Options --}}
    <div class="{{ $layoutClasses[$layout] ?? $layoutClasses['vertical'] }}">
        @foreach($options as $value => $option)
            @php
                $optionLabel = is_array($option) ? ($option['label'] ?? $value) : $option;
                $optionDescription = is_array($option) ? ($option['description'] ?? null) : null;
                $optionIcon = is_array($option) ? ($option['icon'] ?? null) : null;
                $optionDisabled = is_array($option) ? ($option['disabled'] ?? false) : false;
                $isSelected = $currentValue == $value;
            @endphp

            @if($cardStyle)
                {{-- Card Style Radio --}}
                <label
                    for="{{ $name }}_{{ $value }}"
                    @class([
                        'relative flex cursor-pointer rounded-xl border-2 p-4 transition-all duration-200',
                        'hover:border-racing-red-400' => !$optionDisabled && !$isSelected,
                        'border-racing-red-500 bg-racing-red-50 dark:bg-racing-red-900/20 ring-2 ring-racing-red-500/20' => $isSelected,
                        'border-carbon-200 dark:border-carbon-700 bg-white dark:bg-carbon-900' => !$isSelected,
                        'opacity-50 cursor-not-allowed' => $optionDisabled,
                    ])
                >
                    <input
                        type="radio"
                        name="{{ $name }}"
                        id="{{ $name }}_{{ $value }}"
                        value="{{ $value }}"
                        @if($wireModel) wire:model="{{ $wireModel }}" @endif
                        @if($isSelected) checked @endif
                        @if($optionDisabled) disabled @endif
                        class="sr-only"
                    />

                    <div class="flex items-start gap-3 w-full">
                        {{-- Icon --}}
                        @if($optionIcon)
                            <div @class([
                                'flex-shrink-0 w-10 h-10 rounded-lg flex items-center justify-center',
                                'bg-racing-red-500 text-white' => $isSelected,
                                'bg-carbon-100 dark:bg-carbon-800 text-carbon-500' => !$isSelected,
                            ])>
                                <span class="text-xl">{{ $optionIcon }}</span>
                            </div>
                        @endif

                        <div class="flex-1">
                            <span @class([
                                'block text-sm font-medium',
                                'text-racing-red-700 dark:text-racing-red-300' => $isSelected,
                                'text-carbon-900 dark:text-carbon-100' => !$isSelected,
                            ])>
                                {{ $optionLabel }}
                            </span>

                            @if($optionDescription)
                                <span class="block text-xs text-carbon-500 dark:text-carbon-400 mt-0.5">
                                    {{ $optionDescription }}
                                </span>
                            @endif
                        </div>

                        {{-- Check indicator --}}
                        <div @class([
                            'flex-shrink-0 w-5 h-5 rounded-full border-2 flex items-center justify-center transition-all',
                            'border-racing-red-500 bg-racing-red-500' => $isSelected,
                            'border-carbon-300 dark:border-carbon-600' => !$isSelected,
                        ])>
                            @if($isSelected)
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                </svg>
                            @endif
                        </div>
                    </div>
                </label>
            @else
                {{-- Simple Radio --}}
                <x-racing.form.radio
                    :name="$name"
                    :value="$value"
                    :label="$optionLabel"
                    :description="$optionDescription"
                    :checked="$isSelected"
                    :disabled="$optionDisabled"
                    :wire:model="$wireModel"
                />
            @endif
        @endforeach
    </div>

    {{-- Hint Text --}}
    @if($hint && !$hasError)
        <p class="mt-2 text-xs text-carbon-500 dark:text-carbon-400">{{ $hint }}</p>
    @endif

    {{-- Error Message --}}
    @if($hasError)
        <p class="mt-2 text-xs text-status-danger flex items-center gap-1">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ $errors->first($name) }}
        </p>
    @endif
</div>
