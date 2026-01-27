{{--
    Racing Public Layout - For guest/public pages
    Features: Clean navigation, auth links, mobile responsive
--}}
@props([
    'title' => null,
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ mobileMenuOpen: false }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#E53935">

    <title>{{ $title ?? config('app.name', 'RUN200') }}</title>

    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

    <!-- Unified Theme Script - MUST be before CSS -->
    @include('partials.theme-script')

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-carbon-50 dark:bg-carbon-950 text-carbon-900 dark:text-carbon-50">
    {{-- Navigation Header --}}
    <header class="sticky top-0 z-50 glass-effect border-b border-carbon-200 dark:border-carbon-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <nav class="flex items-center justify-between h-16">
                {{-- Logo --}}
                <a href="{{ route('home') }}" class="flex items-center gap-3 group">
                    <img src="{{ asset('images/logorun200.svg') }}" alt="RUN200" class="h-10 w-auto transition-transform group-hover:scale-105" />
                </a>

                {{-- Desktop Navigation --}}
                <div class="hidden md:flex items-center gap-1">
                    <a href="{{ route('home') }}" @class([
                        'px-4 py-2 rounded-xl text-sm font-medium transition-all duration-200',
                        'bg-racing-red-500 text-white shadow-racing' => request()->routeIs('home'),
                        'text-carbon-600 dark:text-carbon-400 hover:bg-carbon-100 dark:hover:bg-carbon-800 hover:text-carbon-900 dark:hover:text-white' => !request()->routeIs('home'),
                    ])>
                        Accueil
                    </a>

                    @if(Route::has('public.calendar'))
                        <a href="{{ route('public.calendar') }}" @class([
                            'px-4 py-2 rounded-xl text-sm font-medium transition-all duration-200',
                            'bg-racing-red-500 text-white shadow-racing' => request()->routeIs('public.calendar'),
                            'text-carbon-600 dark:text-carbon-400 hover:bg-carbon-100 dark:hover:bg-carbon-800 hover:text-carbon-900 dark:hover:text-white' => !request()->routeIs('public.calendar'),
                        ])>
                            Calendrier
                        </a>
                    @endif

                    @if(Route::has('public.standings'))
                        <a href="{{ route('public.standings') }}" @class([
                            'px-4 py-2 rounded-xl text-sm font-medium transition-all duration-200',
                            'bg-racing-red-500 text-white shadow-racing' => request()->routeIs('public.standings'),
                            'text-carbon-600 dark:text-carbon-400 hover:bg-carbon-100 dark:hover:bg-carbon-800 hover:text-carbon-900 dark:hover:text-white' => !request()->routeIs('public.standings'),
                        ])>
                            Classement
                        </a>
                    @endif
                </div>

                {{-- Right Side --}}
                <div class="flex items-center gap-3">
                    {{-- Dark Mode Toggle --}}
                    <button
                        @click="darkMode = !darkMode"
                        class="p-2 rounded-lg text-carbon-500 hover:bg-carbon-100 dark:hover:bg-carbon-800 transition-colors"
                    >
                        <template x-if="darkMode">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                        </template>
                        <template x-if="!darkMode">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                            </svg>
                        </template>
                    </button>

                    {{-- Auth Links (Desktop) --}}
                    <div class="hidden md:flex items-center gap-2">
                        @guest
                            <a href="{{ route('login') }}" class="px-4 py-2 text-sm font-medium text-carbon-600 dark:text-carbon-400 hover:text-carbon-900 dark:hover:text-white transition-colors">
                                Connexion
                            </a>
                            <a href="{{ route('register') }}" class="btn-racing-primary btn-sm">
                                Inscription
                            </a>
                        @else
                            @php
                                $dashboardRoute = 'pilot.dashboard';
                                if (auth()->user()->isAdmin()) {
                                    $dashboardRoute = 'admin.dashboard';
                                } elseif (auth()->user()->isStaff()) {
                                    $dashboardRoute = 'staff.dashboard';
                                }
                            @endphp
                            <a href="{{ route($dashboardRoute) }}" class="btn-racing-primary btn-sm">
                                Mon Espace
                            </a>
                        @endguest
                    </div>

                    {{-- Mobile Menu Button --}}
                    <button
                        @click="mobileMenuOpen = !mobileMenuOpen"
                        class="md:hidden p-2 rounded-lg text-carbon-600 dark:text-carbon-400 hover:bg-carbon-100 dark:hover:bg-carbon-800 transition-colors"
                    >
                        <svg x-show="!mobileMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                        <svg x-show="mobileMenuOpen" x-cloak class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </nav>
        </div>

        {{-- Mobile Menu --}}
        <div
            x-show="mobileMenuOpen"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-4"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-4"
            @click.away="mobileMenuOpen = false"
            class="md:hidden border-t border-carbon-200 dark:border-carbon-800"
        >
            <div class="px-4 py-4 space-y-2">
                <a href="{{ route('home') }}" @class([
                    'block px-4 py-3 rounded-xl text-sm font-medium transition-colors',
                    'bg-racing-red-500 text-white' => request()->routeIs('home'),
                    'text-carbon-700 dark:text-carbon-300 hover:bg-carbon-100 dark:hover:bg-carbon-800' => !request()->routeIs('home'),
                ])>
                    Accueil
                </a>

                @if(Route::has('public.calendar'))
                    <a href="{{ route('public.calendar') }}" @class([
                        'block px-4 py-3 rounded-xl text-sm font-medium transition-colors',
                        'bg-racing-red-500 text-white' => request()->routeIs('public.calendar'),
                        'text-carbon-700 dark:text-carbon-300 hover:bg-carbon-100 dark:hover:bg-carbon-800' => !request()->routeIs('public.calendar'),
                    ])>
                        Calendrier
                    </a>
                @endif

                @if(Route::has('public.standings'))
                    <a href="{{ route('public.standings') }}" @class([
                        'block px-4 py-3 rounded-xl text-sm font-medium transition-colors',
                        'bg-racing-red-500 text-white' => request()->routeIs('public.standings'),
                        'text-carbon-700 dark:text-carbon-300 hover:bg-carbon-100 dark:hover:bg-carbon-800' => !request()->routeIs('public.standings'),
                    ])>
                        Classement
                    </a>
                @endif

                {{-- Auth Links (Mobile) --}}
                <div class="pt-4 border-t border-carbon-200 dark:border-carbon-800 space-y-2">
                    @guest
                        <a href="{{ route('login') }}" class="block px-4 py-3 rounded-xl text-sm font-medium text-carbon-700 dark:text-carbon-300 hover:bg-carbon-100 dark:hover:bg-carbon-800 transition-colors">
                            Connexion
                        </a>
                        <a href="{{ route('register') }}" class="block px-4 py-3 rounded-xl text-sm font-medium text-center bg-racing-red-500 text-white">
                            Inscription
                        </a>
                    @else
                        @php
                            $dashboardRoute = 'pilot.dashboard';
                            if (auth()->user()->isAdmin()) {
                                $dashboardRoute = 'admin.dashboard';
                            } elseif (auth()->user()->isStaff()) {
                                $dashboardRoute = 'staff.dashboard';
                            }
                        @endphp
                        <a href="{{ route($dashboardRoute) }}" class="block px-4 py-3 rounded-xl text-sm font-medium text-center bg-racing-red-500 text-white">
                            Mon Espace
                        </a>
                    @endguest
                </div>
            </div>
        </div>
    </header>

    {{-- Main Content --}}
    <main>
        {{-- Flash Messages --}}
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mt-4">
                    <x-racing.alert type="success" dismissible>
                        {{ session('success') }}
                    </x-racing.alert>
                </div>
            @endif

            @if (session('error'))
                <div class="mt-4">
                    <x-racing.alert type="danger" dismissible>
                        {{ session('error') }}
                    </x-racing.alert>
                </div>
            @endif
        </div>

        {{ $slot }}
    </main>

    {{-- Footer --}}
    <footer class="mt-auto border-t border-carbon-200 dark:border-carbon-800 bg-white dark:bg-carbon-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                {{-- Logo --}}
                <div class="flex items-center gap-3">
                    <img src="{{ asset('images/logorun200.svg') }}" alt="RUN200" class="h-8 w-auto" />
                </div>

                {{-- Links --}}
                <div class="flex items-center gap-6 text-sm text-carbon-500 dark:text-carbon-400">
                    <a href="#" class="hover:text-carbon-900 dark:hover:text-white transition-colors">Mentions légales</a>
                    <a href="#" class="hover:text-carbon-900 dark:hover:text-white transition-colors">Confidentialité</a>
                    <a href="#" class="hover:text-carbon-900 dark:hover:text-white transition-colors">Contact</a>
                </div>

                {{-- Copyright --}}
                <p class="text-sm text-carbon-500 dark:text-carbon-400">
                    © {{ date('Y') }} RUN200. Tous droits réservés.
                </p>
            </div>
        </div>
    </footer>
</body>
</html>
