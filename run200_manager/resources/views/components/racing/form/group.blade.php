{{--
    Racing Form Group Component
    A wrapper for form fields with consistent spacing
--}}
@props([
    'label' => null,
    'required' => false,
    'error' => null,
    'hint' => null,
    'inline' => false,
])

<div {{ $attributes->merge(['class' => $inline ? 'flex items-center gap-4' : 'space-y-1.5']) }}>
    {{-- Label --}}
    @if($label)
        <label @class([
            'text-sm font-medium text-carbon-700 dark:text-carbon-300',
            'flex-shrink-0 w-32' => $inline,
            'block' => !$inline,
        ])>
            {{ $label }}
            @if($required)
                <span class="text-racing-red-500 ml-0.5">*</span>
            @endif
        </label>
    @endif

    {{-- Content --}}
    <div class="{{ $inline ? 'flex-1' : '' }}">
        {{ $slot }}

        {{-- Hint --}}
        @if($hint && !$error)
            <p class="mt-1.5 text-xs text-carbon-500 dark:text-carbon-400">{{ $hint }}</p>
        @endif

        {{-- Error --}}
        @if($error)
            <p class="mt-1.5 text-xs text-status-danger flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ $error }}
            </p>
        @endif
    </div>
</div>
