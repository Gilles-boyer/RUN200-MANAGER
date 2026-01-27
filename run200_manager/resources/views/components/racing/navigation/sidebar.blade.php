{{--
    Racing Sidebar Navigation Component
    Responsive sidebar with role-based navigation items
--}}
@props([
    'role' => 'pilot',
    'config' => [],
    'mobile' => false,
])

@php
    // Navigation items per role
    $navItems = [
        'pilot' => [
            ['route' => 'pilot.dashboard', 'label' => 'Dashboard', 'icon' => 'dashboard'],
            ['route' => 'pilot.races.index', 'label' => 'Courses', 'icon' => 'flag'],
            ['route' => 'pilot.registrations.index', 'label' => 'Inscriptions', 'icon' => 'clipboard'],
            ['route' => 'pilot.cars.index', 'label' => 'Mes Voitures', 'icon' => 'car'],
            ['route' => 'pilot.profile.show', 'label' => 'Profil', 'icon' => 'user'],
        ],
        'staff' => [
            ['route' => 'staff.dashboard', 'label' => 'Dashboard', 'icon' => 'dashboard'],
            ['route' => 'staff.races.index', 'label' => 'Courses', 'icon' => 'flag'],
            ['route' => 'staff.registrations.index', 'label' => 'Inscriptions', 'icon' => 'clipboard'],
            ['route' => 'staff.paddock.manage', 'label' => 'Paddock', 'icon' => 'grid'],
            ['route' => 'staff.pilots.index', 'label' => 'Pilotes', 'icon' => 'users'],
        ],
        'admin' => [
            ['route' => 'admin.dashboard', 'label' => 'Dashboard', 'icon' => 'dashboard'],
            ['route' => 'admin.races.index', 'label' => 'Courses', 'icon' => 'flag'],
            ['route' => 'admin.users.index', 'label' => 'Utilisateurs', 'icon' => 'users'],
            ['route' => 'admin.seasons.index', 'label' => 'Saisons', 'icon' => 'calendar'],
            ['route' => 'settings.profile', 'label' => 'Paramètres', 'icon' => 'settings'],
        ],
    ];

    $items = $navItems[$role] ?? $navItems['pilot'];
@endphp

<aside @class([
    'flex flex-col bg-white dark:bg-carbon-900 border-r border-carbon-200 dark:border-carbon-800',
    'w-72' => true,
    'hidden lg:flex' => !$mobile,
    'h-full' => $mobile,
])>
    {{-- Sidebar Header --}}
    <div class="h-16 flex items-center justify-between px-4 border-b border-carbon-200 dark:border-carbon-800">
        <a href="{{ route($config['homeRoute'] ?? 'pilot.dashboard') }}" class="flex items-center gap-3">
            {{-- Logo --}}
            <img src="{{ asset('images/logorun200.svg') }}" alt="RUN200" class="h-10 w-auto" />
            <div>
                <span class="block text-xs text-{{ $config['color'] ?? 'racing-red' }}-500 font-medium uppercase tracking-wider">
                    {{ $config['label'] ?? 'Pilote' }}
                </span>
            </div>
        </a>

        {{-- Close button (mobile) --}}
        @if($mobile)
            <button @click="sidebarOpen = false" class="p-2 rounded-lg hover:bg-carbon-100 dark:hover:bg-carbon-800">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        @endif
    </div>

    {{-- Navigation Items --}}
    <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
        @foreach($items as $item)
            <x-racing.navigation.nav-item
                :route="$item['route']"
                :label="$item['label']"
                :icon="$item['icon']"
            />
        @endforeach
    </nav>

    {{-- Sidebar Footer --}}
    <div class="p-4 border-t border-carbon-200 dark:border-carbon-800">
        {{-- Dark Mode Toggle --}}
        <button
            @click="darkMode = !darkMode"
            class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-carbon-600 dark:text-carbon-400 hover:bg-carbon-100 dark:hover:bg-carbon-800 transition-colors"
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
            <span x-text="darkMode ? 'Mode clair' : 'Mode sombre'" class="text-sm font-medium"></span>
        </button>

        {{-- User Profile Card --}}
        @auth
            <div class="mt-3 p-3 bg-carbon-50 dark:bg-carbon-800 rounded-xl">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-racing-red-500 to-racing-red-600 flex items-center justify-center text-white font-semibold">
                        {{ substr(auth()->user()->first_name ?? 'U', 0, 1) }}{{ substr(auth()->user()->last_name ?? '', 0, 1) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-carbon-900 dark:text-white truncate">
                            {{ auth()->user()->full_name ?? auth()->user()->email }}
                        </p>
                        <p class="text-xs text-carbon-500 dark:text-carbon-400 truncate">
                            {{ auth()->user()->email }}
                        </p>
                    </div>
                </div>
                <div class="mt-3 flex gap-2">
                    <a
                        href="{{ route('settings.profile') }}"
                        class="flex-1 text-center text-xs font-medium py-2 px-3 rounded-lg bg-white dark:bg-carbon-700 text-carbon-700 dark:text-carbon-300 hover:bg-carbon-100 dark:hover:bg-carbon-600 transition-colors"
                    >
                        Paramètres
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="flex-1">
                        @csrf
                        <button
                            type="submit"
                            class="w-full text-xs font-medium py-2 px-3 rounded-lg text-racing-red-600 dark:text-racing-red-400 hover:bg-racing-red-50 dark:hover:bg-racing-red-900/20 transition-colors"
                        >
                            Déconnexion
                        </button>
                    </form>
                </div>
            </div>
        @endauth
    </div>
</aside>
