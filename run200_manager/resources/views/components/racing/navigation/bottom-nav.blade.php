{{--
    Racing Mobile Bottom Navigation Component
    Fixed bottom navigation bar for mobile devices
--}}
@props([
    'role' => 'pilot',
])

@php
    // Bottom nav items per role (max 5 items)
    $navItems = [
        'pilot' => [
            ['route' => 'pilot.dashboard', 'label' => 'Accueil', 'icon' => 'home'],
            ['route' => 'pilot.races.index', 'label' => 'Courses', 'icon' => 'flag'],
            ['route' => 'pilot.registrations.index', 'label' => 'Inscriptions', 'icon' => 'clipboard'],
            ['route' => 'pilot.cars.index', 'label' => 'Voitures', 'icon' => 'car'],
            ['route' => 'pilot.profile.show', 'label' => 'Profil', 'icon' => 'user'],
        ],
        'staff' => [
            ['route' => 'staff.dashboard', 'label' => 'Accueil', 'icon' => 'home'],
            ['route' => 'staff.races.index', 'label' => 'Courses', 'icon' => 'flag'],
            ['route' => 'staff.paddock.manage', 'label' => 'Paddock', 'icon' => 'grid'],
            ['route' => 'staff.registrations.index', 'label' => 'Inscrits', 'icon' => 'clipboard'],
            ['route' => 'staff.pilots.index', 'label' => 'Pilotes', 'icon' => 'users'],
        ],
        'admin' => [
            ['route' => 'admin.dashboard', 'label' => 'Accueil', 'icon' => 'home'],
            ['route' => 'admin.races.index', 'label' => 'Courses', 'icon' => 'flag'],
            ['route' => 'admin.users.index', 'label' => 'Users', 'icon' => 'users'],
            ['route' => 'admin.seasons.index', 'label' => 'Saisons', 'icon' => 'calendar'],
            ['route' => 'settings.profile', 'label' => 'Config', 'icon' => 'settings'],
        ],
    ];

    $items = $navItems[$role] ?? $navItems['pilot'];

    // Icon SVG paths
    $icons = [
        'home' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>',
        'flag' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/>',
        'clipboard' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>',
        'car' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>',
        'user' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>',
        'users' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>',
        'grid' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>',
        'check-circle' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
        'tag' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>',
        'settings' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>',
    ];
@endphp

<nav class="lg:hidden fixed bottom-0 inset-x-0 z-50 glass-effect border-t border-carbon-200 dark:border-carbon-800 safe-area-bottom">
    <div class="flex items-center justify-around h-16 px-2">
        @foreach($items as $item)
            @php
                $isActive = request()->routeIs($item['route'] . '*');
                $iconPath = $icons[$item['icon']] ?? $icons['home'];
            @endphp

            <a
                href="{{ Route::has($item['route']) ? route($item['route']) : '#' }}"
                @class([
                    'flex flex-col items-center justify-center flex-1 py-2 min-w-0 transition-all duration-200',
                    'text-racing-red-500' => $isActive,
                    'text-carbon-500 dark:text-carbon-400' => !$isActive,
                ])
            >
                {{-- Icon Container --}}
                <span @class([
                    'relative flex items-center justify-center w-12 h-8 rounded-2xl transition-all duration-300',
                    'bg-racing-red-100 dark:bg-racing-red-900/30' => $isActive,
                ])>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        {!! $iconPath !!}
                    </svg>

                    {{-- Active Dot --}}
                    @if($isActive)
                        <span class="absolute -top-0.5 -right-0.5 w-2 h-2 bg-racing-red-500 rounded-full animate-pulse"></span>
                    @endif
                </span>

                {{-- Label --}}
                <span @class([
                    'mt-1 text-[10px] font-medium truncate max-w-full px-1',
                    'font-semibold' => $isActive,
                ])>
                    {{ $item['label'] }}
                </span>
            </a>
        @endforeach
    </div>
</nav>
