{{--
    Racing Table Cell Component

    @props
    - label: Label shown on mobile view (required for mobile cards)
    - align: Text alignment (left, center, right)
--}}

@props([
    'label' => '',
    'align' => 'left',
])

@php
    $alignClasses = match($align) {
        'center' => 'text-center',
        'right' => 'text-right',
        default => 'text-left',
    };
@endphp

<td
    {{ $attributes->merge(['class' => "px-4 py-4 text-sm text-carbon-900 dark:text-carbon-100 $alignClasses"]) }}
    @if($label) data-label="{{ $label }}" @endif
>
    {{ $slot }}
</td>
