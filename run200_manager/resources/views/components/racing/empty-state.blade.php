@props([
    'title',
    'description' => null,
    'icon' => '🏎️',
    'actionLabel' => null,
    'actionHref' => null,
    'actionVariant' => 'primary',
])

<div {{ $attributes->merge(['class' => 'empty-state-racing']) }}>
    <div class="empty-state-racing-icon">
        {!! $icon !!}
    </div>

    <h3 class="empty-state-racing-title">
        {{ $title }}
    </h3>

    @if($description)
        <p class="empty-state-racing-description">
            {{ $description }}
        </p>
    @endif

    {{-- Custom slot content --}}
    {{ $slot }}

    @if($actionLabel && $actionHref)
        <x-racing.button :href="$actionHref" :variant="$actionVariant">
            {{ $actionLabel }}
        </x-racing.button>
    @endif
</div>
