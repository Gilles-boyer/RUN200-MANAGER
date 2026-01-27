{{--
    Racing User Menu Component
    Dropdown menu for user actions (mobile header)
--}}
@props([])

<div x-data="{ open: false }" class="relative">
    {{-- Trigger Button --}}
    <button
        @click="open = !open"
        class="flex items-center gap-2 p-1.5 rounded-full hover:bg-carbon-100 dark:hover:bg-carbon-800 transition-colors"
    >
        @auth
            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-racing-red-500 to-racing-red-600 flex items-center justify-center text-white text-sm font-semibold">
                {{ substr(auth()->user()->first_name ?? 'U', 0, 1) }}
            </div>
        @else
            <div class="w-8 h-8 rounded-full bg-carbon-200 dark:bg-carbon-700 flex items-center justify-center">
                <svg class="w-4 h-4 text-carbon-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
        @endauth
    </button>

    {{-- Dropdown Menu --}}
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        @click.away="open = false"
        class="absolute right-0 mt-2 w-56 origin-top-right z-[1000]"
        style="z-index: var(--z-dropdown, 1000);"
    >
        <div class="rounded-2xl bg-white dark:bg-carbon-900 shadow-xl border border-carbon-200 dark:border-carbon-800 overflow-hidden">
            @auth
                {{-- User Info --}}
                <div class="p-4 bg-carbon-50 dark:bg-carbon-800/50">
                    <p class="text-sm font-medium text-carbon-900 dark:text-white">
                        {{ auth()->user()->full_name ?? auth()->user()->email }}
                    </p>
                    <p class="text-xs text-carbon-500 dark:text-carbon-400 truncate">
                        {{ auth()->user()->email }}
                    </p>
                </div>

                {{-- Menu Items --}}
                <div class="py-2">
                    <a href="{{ route('settings.profile') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-carbon-700 dark:text-carbon-300 hover:bg-carbon-50 dark:hover:bg-carbon-800 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        Profil
                    </a>
                    <a href="{{ route('settings.profile') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-carbon-700 dark:text-carbon-300 hover:bg-carbon-50 dark:hover:bg-carbon-800 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        Paramètres
                    </a>

                    {{-- Dark Mode Toggle - Disabled until light mode is properly supported --}}
                    <button
                        @click.prevent="window.toggleDarkMode()"
                        class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-carbon-700 dark:text-carbon-300 opacity-50 cursor-not-allowed"
                        title="Le mode sombre est requis pour une lisibilité optimale"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                        </svg>
                        <span>Mode sombre (requis)</span>
                    </button>
                </div>

                {{-- Logout --}}
                <div class="border-t border-carbon-200 dark:border-carbon-800 py-2">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-racing-red-600 dark:text-racing-red-400 hover:bg-racing-red-50 dark:hover:bg-racing-red-900/20 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            Déconnexion
                        </button>
                    </form>
                </div>
            @else
                {{-- Guest Menu --}}
                <div class="py-2">
                    <a href="{{ route('login') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-carbon-700 dark:text-carbon-300 hover:bg-carbon-50 dark:hover:bg-carbon-800 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                        </svg>
                        Connexion
                    </a>
                    <a href="{{ route('register') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-carbon-700 dark:text-carbon-300 hover:bg-carbon-50 dark:hover:bg-carbon-800 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                        </svg>
                        Inscription
                    </a>
                </div>
            @endauth
        </div>
    </div>
</div>
