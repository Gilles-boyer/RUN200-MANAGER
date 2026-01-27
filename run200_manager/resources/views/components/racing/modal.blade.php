{{--
    Racing Modal/Dialog Component
    A styled modal with animations and backdrop
--}}
@props([
    'name' => 'modal',
    'title' => null,
    'maxWidth' => 'md', // sm, md, lg, xl, 2xl, full
    'closeable' => true,
    'footer' => null,
])

@php
    $maxWidthClasses = [
        'sm' => 'max-w-sm',
        'md' => 'max-w-md',
        'lg' => 'max-w-lg',
        'xl' => 'max-w-xl',
        '2xl' => 'max-w-2xl',
        '3xl' => 'max-w-3xl',
        '4xl' => 'max-w-4xl',
        'full' => 'max-w-full mx-4',
    ];
    $maxWidthClass = $maxWidthClasses[$maxWidth] ?? $maxWidthClasses['md'];
@endphp

<div
    x-data="{ open: false }"
    x-on:open-modal.window="if ($event.detail === '{{ $name }}') open = true"
    x-on:close-modal.window="if ($event.detail === '{{ $name }}') open = false"
    x-on:keydown.escape.window="open = false"
    {{ $attributes->merge(['class' => 'racing-modal']) }}
>
    {{-- Trigger Slot --}}
    @if(isset($trigger))
        <div x-on:click="open = true">
            {{ $trigger }}
        </div>
    @endif

    {{-- Modal Backdrop --}}
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 overflow-y-auto"
        style="display: none;"
    >
        {{-- Backdrop --}}
        <div
            class="fixed inset-0 bg-black/50 backdrop-blur-sm"
            @if($closeable) x-on:click="open = false" @endif
        ></div>

        {{-- Modal Container --}}
        <div class="flex min-h-full items-center justify-center p-4">
            {{-- Modal Panel --}}
            <div
                x-show="open"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                x-on:click.stop
                class="relative w-full {{ $maxWidthClass }} bg-white dark:bg-carbon-900 rounded-2xl shadow-2xl overflow-hidden"
            >
                {{-- Header --}}
                @if($title || $closeable)
                    <div class="flex items-center justify-between px-6 py-4 border-b border-carbon-200 dark:border-carbon-800">
                        @if($title)
                            <h3 class="text-lg font-semibold text-carbon-900 dark:text-white">
                                {{ $title }}
                            </h3>
                        @else
                            <div></div>
                        @endif

                        @if($closeable)
                            <button
                                type="button"
                                x-on:click="open = false"
                                class="p-2 -m-2 rounded-lg text-carbon-500 hover:text-carbon-700 dark:hover:text-carbon-300 hover:bg-carbon-100 dark:hover:bg-carbon-800 transition-colors"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        @endif
                    </div>
                @endif

                {{-- Body --}}
                <div class="px-6 py-4">
                    {{ $slot }}
                </div>

                {{-- Footer --}}
                @if(isset($footer))
                    <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-carbon-200 dark:border-carbon-800 bg-carbon-50 dark:bg-carbon-800/50">
                        {{ $footer }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
