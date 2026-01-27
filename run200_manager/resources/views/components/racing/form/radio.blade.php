{{--
    Racing Radio Component
    A styled radio button with label and description
--}}
@props([
    'name' => '',
    'label' => null,
    'description' => null,
    'checked' => false,
    'disabled' => false,
    'value' => '',
])

@php
    $id = $attributes->get('id', $name . '_' . $value);
    $hasError = $errors->has($name);
    $wireModel = $attributes->whereStartsWith('wire:model')->first();
    $currentValue = old($name);
    $isChecked = $currentValue ? ($currentValue == $value) : $checked;
@endphp

<div {{ $attributes->only('class')->merge(['class' => 'racing-radio-wrapper']) }}>
    <label for="{{ $id }}" class="flex items-start gap-3 cursor-pointer group">
        {{-- Radio Input --}}
        <div class="relative flex-shrink-0 mt-0.5">
            <input
                type="radio"
                name="{{ $name }}"
                id="{{ $id }}"
                value="{{ $value }}"
                @if($wireModel) wire:model="{{ $wireModel }}" @endif
                @if($isChecked) checked @endif
                @if($disabled) disabled @endif
                class="peer sr-only"
            />
            {{-- Custom Radio --}}
            <div @class([
                'w-5 h-5 rounded-full border-2 transition-all duration-200 ease-out flex items-center justify-center',
                'peer-focus:ring-2 peer-focus:ring-racing-red-500/20',
                'peer-checked:border-racing-red-500',
                'peer-disabled:opacity-50 peer-disabled:cursor-not-allowed',
                $hasError
                    ? 'border-status-danger'
                    : 'border-carbon-300 dark:border-carbon-600 group-hover:border-racing-red-400',
            ])>
                {{-- Inner Dot --}}
                <div class="w-2.5 h-2.5 rounded-full bg-racing-red-500 scale-0 peer-checked:scale-100 transition-transform duration-150"></div>
            </div>
            {{-- Animated Dot (shows when checked) --}}
            <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                <div class="w-2.5 h-2.5 rounded-full bg-racing-red-500 scale-0 peer-checked:scale-100 transition-transform duration-150"></div>
            </div>
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
</div>
