<?php

use Livewire\Component;

new class extends Component {
    //
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading class="sr-only">{{ __('Appearance Settings') }}</flux:heading>

    <x-pages::settings.layout :heading="__('Appearance')" :subheading="__('Update the appearance settings for your account')">
        {{-- Theme toggle temporarily disabled - dark mode is required for optimal readability --}}
        <div class="rounded-xl bg-zinc-800/50 border border-zinc-700/50 p-4">
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-racing-red/10 text-racing-red">
                    <flux:icon.moon class="w-5 h-5" />
                </div>
                <div>
                    <p class="text-white font-medium">{{ __('Dark Mode Active') }}</p>
                    <p class="text-zinc-400 text-sm">{{ __('The Racing Design System currently requires dark mode for optimal text visibility. Light mode support is coming in a future update.') }}</p>
                </div>
            </div>
        </div>

        {{-- Original theme selector - hidden until light mode is properly supported
        <flux:radio.group x-data variant="segmented" x-model="$flux.appearance">
            <flux:radio value="light" icon="sun">{{ __('Light') }}</flux:radio>
            <flux:radio value="dark" icon="moon">{{ __('Dark') }}</flux:radio>
            <flux:radio value="system" icon="computer-desktop">{{ __('System') }}</flux:radio>
        </flux:radio.group>
        --}}
    </x-pages::settings.layout>
</section>
