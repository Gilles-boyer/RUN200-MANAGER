{{--
    Racing Public Layout - For guest/public pages
    Features: Clean navigation, auth links, mobile responsive
--}}
@props([
    'title' => null,
    'description' => null,
    'indexable' => true,
])

@php
    $pageDescription = $description ?? match (true) {
        str_starts_with((string) $title, 'RUN200 Manager -') => 'Inscrivez-vous aux courses RUN200, retrouvez le calendrier et suivez les résultats du championnat.',
        str_starts_with((string) $title, 'Calendrier des courses') => 'Consultez le calendrier des courses RUN200 et les prochaines dates de compétition.',
        str_starts_with((string) $title, 'Classement du championnat') => 'Retrouvez le classement du championnat RUN200 par catégorie et suivez les pilotes.',
        str_starts_with((string) $title, 'Résultats :') => 'Consultez les résultats publiés de cette course RUN200.',
        str_starts_with((string) $title, 'Tableaux d\'affichage') => 'Accédez aux tableaux d’affichage et aux documents publics des courses RUN200.',
        str_starts_with((string) $title, 'Tableau d\'affichage') => 'Consultez le tableau d’affichage public de cette course RUN200.',
        str_starts_with((string) $title, 'Mentions Légales') => 'Consultez les mentions légales de la plateforme RUN200.',
        str_starts_with((string) $title, 'Politique de Confidentialité') => 'Découvrez comment RUN200 traite et protège vos données personnelles.',
        default => 'Suivez les courses, les résultats et le championnat RUN200.',
    };
    $pageTitle = $title ?? config('app.name', 'RUN200');
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#E53935">

    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $pageDescription }}">
    @if($indexable)
        <link rel="canonical" href="{{ url()->current() }}">
    @else
        <meta name="robots" content="noindex, nofollow">
    @endif
    <meta property="og:type" content="website">
    <meta property="og:locale" content="fr_FR">
    <meta property="og:site_name" content="RUN200">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $pageDescription }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ asset('apple-touch-icon.png') }}">
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="{{ $pageTitle }}">
    <meta name="twitter:description" content="{{ $pageDescription }}">
    <meta name="twitter:image" content="{{ asset('apple-touch-icon.png') }}">

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

                    @if(Route::has('public.boards'))
                        <a href="{{ route('public.boards') }}" @class([
                            'px-4 py-2 rounded-xl text-sm font-medium transition-all duration-200',
                            'bg-racing-red-500 text-white shadow-racing' => request()->routeIs('public.boards') || request()->routeIs('board.show'),
                            'text-carbon-600 dark:text-carbon-400 hover:bg-carbon-100 dark:hover:bg-carbon-800 hover:text-carbon-900 dark:hover:text-white' => !request()->routeIs('public.boards') && !request()->routeIs('board.show'),
                        ])>
                            Tableaux d'affichage
                        </a>
                    @endif
                </div>

                {{-- Right Side --}}
                <div class="flex items-center gap-3">
                    {{-- Auth Links (Desktop) --}}
                    <div class="hidden md:flex items-center gap-2">
                        @guest
                            <a href="{{ route('login') }}" class="px-4 py-2 text-sm font-medium text-carbon-600 dark:text-carbon-400 hover:text-carbon-900 dark:hover:text-white transition-colors">
                                Connexion
                            </a>
                            <a href="{{ route('register') }}" class="btn-racing btn-racing-primary btn-sm">
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
                            <a href="{{ route($dashboardRoute) }}" class="btn-racing btn-racing-primary btn-sm">
                                Mon Espace
                            </a>
                        @endguest
                    </div>

                    {{-- Mobile Menu Button --}}
                    <button
                        type="button"
                        data-public-menu-toggle
                        aria-controls="public-mobile-menu"
                        aria-expanded="false"
                        aria-label="Ouvrir le menu"
                        class="md:hidden p-2 rounded-lg text-carbon-600 dark:text-carbon-400 hover:bg-carbon-100 dark:hover:bg-carbon-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-racing-red-500 transition-colors"
                    >
                        <svg aria-hidden="true" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                </div>
            </nav>
        </div>

        {{-- Mobile Menu --}}
        <div
            id="public-mobile-menu"
            hidden
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

                @if(Route::has('public.boards'))
                    <a href="{{ route('public.boards') }}" @class([
                        'block px-4 py-3 rounded-xl text-sm font-medium transition-colors',
                        'bg-racing-red-500 text-white' => request()->routeIs('public.boards') || request()->routeIs('board.show'),
                        'text-carbon-700 dark:text-carbon-300 hover:bg-carbon-100 dark:hover:bg-carbon-800' => !request()->routeIs('public.boards') && !request()->routeIs('board.show'),
                    ])>
                        Tableaux d'affichage
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

            @foreach (['warning', 'info'] as $flashType)
                @if (session($flashType))
                    <div class="mt-4">
                        <x-racing.alert :type="$flashType" dismissible>{{ session($flashType) }}</x-racing.alert>
                    </div>
                @endif
            @endforeach
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
                    <a href="{{ route('legal') }}" class="hover:text-carbon-900 dark:hover:text-white transition-colors">Mentions légales</a>
                    <a href="{{ route('privacy') }}" class="hover:text-carbon-900 dark:hover:text-white transition-colors">Confidentialité</a>
                    <a href="https://cfg.re/#contactCfg" target="_blank" rel="noopener" class="hover:text-carbon-900 dark:hover:text-white transition-colors inline-flex items-center gap-1">
                        Contact
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                    </a>
                </div>

                {{-- Copyright --}}
                <p class="text-sm text-carbon-500 dark:text-carbon-400">
                    © {{ date('Y') }} RUN200. Tous droits réservés.
                </p>
            </div>
        </div>
    </footer>
    @fluxScripts
    <script>
        (() => {
            const toggle = document.querySelector('[data-public-menu-toggle]');
            const menu = document.getElementById('public-mobile-menu');

            if (!toggle || !menu) return;

            const setOpen = (open) => {
                menu.hidden = !open;
                toggle.setAttribute('aria-expanded', String(open));
                toggle.setAttribute('aria-label', open ? 'Fermer le menu' : 'Ouvrir le menu');
            };

            toggle.addEventListener('click', () => setOpen(menu.hidden));
            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && !menu.hidden) {
                    setOpen(false);
                    toggle.focus();
                }
            });
            document.addEventListener('click', (event) => {
                if (!menu.hidden && !menu.contains(event.target) && !toggle.contains(event.target)) {
                    setOpen(false);
                }
            });
        })();
    </script>
</body>
</html>
