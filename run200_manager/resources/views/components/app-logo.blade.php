@props([
    'sidebar' => false,
])

@if($sidebar)
    <flux:sidebar.brand name="RUN200 Manager" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center rounded-md">
            <img src="{{ asset('images/logorun200.svg') }}" alt="RUN200" class="h-8 w-auto" />
        </x-slot>
    </flux:sidebar.brand>
@else
    <flux:brand name="RUN200 Manager" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center rounded-md">
            <img src="{{ asset('images/logorun200.svg') }}" alt="RUN200" class="h-8 w-auto" />
        </x-slot>
    </flux:brand>
@endif
