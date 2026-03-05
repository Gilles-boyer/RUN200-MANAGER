{{--
    Racing Form Errors Component
    Displays all validation errors in a styled alert box

    Usage:
    <x-racing.form.errors />
    <x-racing.form.errors :only="['email', 'password']" />
    <x-racing.form.errors bag="login" />
--}}
@props([
    'only' => null,
    'bag' => 'default',
    'title' => 'Veuillez corriger les erreurs suivantes :',
    'dismissible' => true,
])

@php
    $errorBag = $errors->getBag($bag);
    $allErrors = $only
        ? collect($only)->filter(fn($field) => $errorBag->has($field))->mapWithKeys(fn($field) => [$field => $errorBag->get($field)])
        : $errorBag->toArray();
    $hasErrors = !empty($allErrors);
@endphp

@if($hasErrors)
<div
    {{ $attributes->merge(['class' => 'bg-status-danger/10 dark:bg-status-danger/20 border border-status-danger/50 rounded-xl p-4']) }}
    @if($dismissible) x-data="{ show: true }" x-show="show" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" @endif
>
    <div class="flex items-start gap-3">
        {{-- Icon --}}
        <div class="flex-shrink-0">
            <svg class="w-5 h-5 text-status-danger mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>

        {{-- Content --}}
        <div class="flex-1">
            <h3 class="text-sm font-semibold text-red-800 dark:text-red-300">
                {{ $title }}
            </h3>
            <ul class="mt-2 space-y-1 list-disc list-inside">
                @foreach($allErrors as $field => $messages)
                    @foreach((array) $messages as $message)
                        <li class="text-sm text-red-700 dark:text-red-400">
                            {{ $message }}
                        </li>
                    @endforeach
                @endforeach
            </ul>
        </div>

        {{-- Dismiss Button --}}
        @if($dismissible)
        <button
            @click="show = false"
            class="flex-shrink-0 text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 transition-colors"
            type="button"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
        @endif
    </div>
</div>
@endif
