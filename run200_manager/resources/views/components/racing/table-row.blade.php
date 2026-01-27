{{--
    Racing Table Row Component
--}}

@props([])

<tr {{ $attributes->merge(['class' => 'bg-white dark:bg-carbon-900 hover:bg-carbon-50 dark:hover:bg-carbon-800/50 transition-colors']) }}>
    {{ $slot }}
</tr>
