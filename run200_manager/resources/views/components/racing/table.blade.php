{{--
    Racing Responsive Table Component

    Usage:
    <x-racing.table :headers="['Nom', 'Email', 'Actions']" :mobile-cards="true">
        <x-racing.table-row>
            <x-racing.table-cell label="Nom">John Doe</x-racing.table-cell>
            <x-racing.table-cell label="Email">john@example.com</x-racing.table-cell>
            <x-racing.table-cell label="Actions">
                <button>Edit</button>
            </x-racing.table-cell>
        </x-racing.table-row>
    </x-racing.table>
--}}

@props([
    'headers' => [],
    'mobileCards' => true,
    'striped' => false,
    'hoverable' => true,
])

@php
    $tableClasses = 'table-racing w-full';
    if ($mobileCards) {
        $tableClasses .= ' table-mobile-cards';
    }
    if ($striped) {
        $tableClasses .= ' table-striped';
    }
    if ($hoverable) {
        $tableClasses .= ' table-hoverable';
    }
@endphp

<div class="table-responsive">
    <table {{ $attributes->merge(['class' => $tableClasses]) }}>
        @if (count($headers) > 0)
            <thead class="hidden md:table-header-group">
                <tr>
                    @foreach ($headers as $header)
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-carbon-500 dark:text-carbon-400 bg-carbon-50 dark:bg-carbon-800/50">
                            {{ $header }}
                        </th>
                    @endforeach
                </tr>
            </thead>
        @endif
        <tbody class="divide-y divide-carbon-200 dark:divide-carbon-700">
            {{ $slot }}
        </tbody>
    </table>
</div>
