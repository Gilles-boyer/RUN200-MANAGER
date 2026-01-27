{{--
    Racing Layout - Unified Layout for all authenticated users
    Supports: Pilot, Staff, Admin with role-based navigation
    Features: Responsive sidebar, mobile bottom nav, dark mode
--}}
@props([
    'title' => null,
    'role' => null, // pilot, staff, admin - auto-detected if null
])

@php
    // Auto-detect role if not provided
    if (!$role && auth()->check()) {
        $user = auth()->user();
        if ($user->isAdmin()) {
            $role = 'admin';
        } elseif ($user->isStaff()) {
            $role = 'staff';
        } else {
            $role = 'pilot';
        }
    }
    $role = $role ?? 'pilot';

    // Role-specific configuration
    $roleConfig = [
        'pilot' => [
            'label' => 'Pilote',
            'color' => 'racing-red',
            'homeRoute' => 'pilot.dashboard',
        ],
        'staff' => [
            'label' => 'Staff',
            'color' => 'status-info',
            'homeRoute' => 'staff.dashboard',
        ],
        'admin' => [
            'label' => 'Admin',
            'color' => 'status-pending',
            'homeRoute' => 'admin.dashboard',
        ],
    ];

    $config = $roleConfig[$role] ?? $roleConfig['pilot'];
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ sidebarOpen: false }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#E53935">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

    <title>{{ $title ?? config('app.name', 'RUN200') }} - {{ $config['label'] }}</title>

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
    <div class="min-h-screen flex">
        {{-- Desktop Sidebar --}}
        <x-racing.navigation.sidebar :role="$role" :config="$config" />

        {{-- Mobile Sidebar Overlay --}}
        <div
            x-show="sidebarOpen"
            x-transition:enter="transition-opacity ease-linear duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-linear duration-300"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/50 z-40 lg:hidden"
            @click="sidebarOpen = false"
        ></div>

        {{-- Mobile Sidebar --}}
        <div
            x-show="sidebarOpen"
            x-transition:enter="transition ease-in-out duration-300 transform"
            x-transition:enter-start="-translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in-out duration-300 transform"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="-translate-x-full"
            class="fixed inset-y-0 left-0 z-50 w-72 lg:hidden"
        >
            <x-racing.navigation.sidebar :role="$role" :config="$config" mobile />
        </div>

        {{-- Main Content Area --}}
        <div class="flex-1 flex flex-col min-w-0">
            {{-- Top Header (Mobile) --}}
            <header class="lg:hidden sticky top-0 z-30 glass-effect border-b border-carbon-200 dark:border-carbon-800">
                <div class="flex items-center justify-between h-16 px-4">
                    {{-- Menu Button --}}
                    <button
                        @click="sidebarOpen = true"
                        class="p-2 rounded-lg text-carbon-600 dark:text-carbon-400 hover:bg-carbon-100 dark:hover:bg-carbon-800 transition-colors"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>

                    {{-- Logo --}}
                    <a href="{{ route($config['homeRoute']) }}" class="flex items-center gap-2">
                        <img src="{{ asset('images/logorun200.svg') }}" alt="RUN200" class="h-8 w-auto" />
                    </a>

                    {{-- User Menu --}}
                    <x-racing.navigation.user-menu />
                </div>
            </header>

            {{-- Page Content --}}
            <main class="flex-1 pb-20 lg:pb-0">
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

                    @if (session('warning'))
                        <div class="mt-4">
                            <x-racing.alert type="warning" dismissible>
                                {{ session('warning') }}
                            </x-racing.alert>
                        </div>
                    @endif

                    @if (session('info'))
                        <div class="mt-4">
                            <x-racing.alert type="info" dismissible>
                                {{ session('info') }}
                            </x-racing.alert>
                        </div>
                    @endif
                </div>

                {{-- Main Slot --}}
                <div class="py-6">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        {{ $slot }}
                    </div>
                </div>
            </main>
        </div>
    </div>

    {{-- Mobile Bottom Navigation --}}
    <x-racing.navigation.bottom-nav :role="$role" />
</body>
</html>
