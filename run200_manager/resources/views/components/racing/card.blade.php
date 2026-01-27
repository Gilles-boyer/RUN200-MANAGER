@props([
    'hover' => true,
    'padding' => true,
])

@php
    $classes = 'card-racing';
    if (!$hover) {
        $classes .= ' hover:transform-none hover:shadow-none';
    }
@endphp

<div {{ $attributes->merge(['class' => $classes]) }}>
    {{-- Card Header (optional slot) --}}
    @if(isset($header))
        <div class="card-racing-header">
            {{ $header }}
        </div>
    @endif

    {{-- Card Body --}}
    <div @class(['card-racing-body' => $padding, 'p-0' => !$padding])>
        {{ $slot }}
    </div>

    {{-- Card Footer (optional slot) --}}
    @if(isset($footer))
        <div class="card-racing-footer">
            {{ $footer }}
        </div>
    @endif
</div>
