<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen antialiased bg-carbon-50 dark:bg-carbon-950">
        {{-- Background decorative elements --}}
        <div class="fixed inset-0 overflow-hidden pointer-events-none">
            <div class="absolute -top-40 -right-40 w-96 h-96 bg-racing-red-500/5 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-racing-red-500/5 rounded-full blur-3xl"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[800px] h-[800px] bg-checkered-yellow-500/3 rounded-full blur-3xl"></div>
        </div>

        <div class="relative flex min-h-svh flex-col items-center justify-center gap-6 p-6 md:p-10">
            <div class="flex w-full max-w-md flex-col gap-6">
                {{-- Logo --}}
                <a href="{{ route('home') }}" class="flex flex-col items-center gap-3 font-medium group" wire:navigate>
                    <span class="flex h-16 w-16 items-center justify-center rounded-2xl bg-racing-red-500 shadow-lg shadow-racing-red-500/30 group-hover:shadow-racing-red-500/50 transition-shadow duration-300">
                        <x-app-logo-icon class="size-10 fill-current text-white" />
                    </span>
                    <span class="text-xl font-bold text-carbon-900 dark:text-white">{{ config('app.name', 'RUN200') }}</span>
                </a>

                {{-- Card --}}
                <div class="bg-white dark:bg-carbon-900 rounded-2xl shadow-xl shadow-carbon-900/5 dark:shadow-none border border-carbon-200 dark:border-carbon-800 p-8">
                    {{ $slot }}
                </div>

                {{-- Footer --}}
                <p class="text-center text-xs text-carbon-500 dark:text-carbon-400">
                    © {{ date('Y') }} {{ config('app.name', 'RUN200') }}. Tous droits réservés.
                </p>
            </div>
        </div>
        @fluxScripts
    </body>
</html>
