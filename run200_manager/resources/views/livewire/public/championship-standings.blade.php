<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    {{-- Header --}}
    <div class="text-center mb-10">
        <div class="inline-flex items-center gap-3 mb-4">
            <div class="w-12 h-1 bg-gradient-to-r from-transparent to-racing-red-500 rounded-full"></div>
            <svg class="w-8 h-8 text-racing-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
            <div class="w-12 h-1 bg-gradient-to-l from-transparent to-racing-red-500 rounded-full"></div>
        </div>
        <h1 class="text-3xl md:text-4xl font-display font-bold text-carbon-900 dark:text-white">
            Classement du Championnat
        </h1>
        @if($this->season)
            <p class="mt-3 text-lg text-racing-red-600 dark:text-racing-red-400 font-semibold">
                Saison {{ $this->season->name }}
            </p>
            <p class="mt-1 text-sm text-carbon-500 dark:text-carbon-400">
                Classement provisoire basé sur {{ $this->seasonStats['published_races'] }} course(s) disputée(s)
            </p>
        @endif
    </div>

    @if(!$this->season)
        {{-- No active season --}}
        <div class="racing-card p-8 text-center border-l-4 border-checkered-yellow-500">
            <svg class="mx-auto h-16 w-16 text-checkered-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <h3 class="mt-4 text-lg font-semibold text-carbon-800 dark:text-carbon-200">Aucune saison active</h3>
            <p class="mt-2 text-carbon-600 dark:text-carbon-400">Le classement du championnat sera disponible lors de l'ouverture de la prochaine saison.</p>
        </div>
    @else
        {{-- Season Stats --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-10">
            <div class="racing-card p-5 text-center">
                <div class="w-10 h-10 mx-auto mb-2 bg-racing-red-100 dark:bg-racing-red-900/30 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-racing-red-600 dark:text-racing-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="text-2xl font-bold text-racing-red-600 dark:text-racing-red-400">{{ $this->seasonStats['published_races'] }}</div>
                <div class="text-xs text-carbon-500 dark:text-carbon-400 mt-1">Courses comptabilisées</div>
            </div>
            <div class="racing-card p-5 text-center">
                <div class="w-10 h-10 mx-auto mb-2 bg-carbon-100 dark:bg-carbon-700 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-carbon-600 dark:text-carbon-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <div class="text-2xl font-bold text-carbon-700 dark:text-carbon-300">{{ $this->seasonStats['total_races'] }}</div>
                <div class="text-xs text-carbon-500 dark:text-carbon-400 mt-1">Courses prévues</div>
            </div>
            <div class="racing-card p-5 text-center">
                <div class="w-10 h-10 mx-auto mb-2 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $this->seasonStats['participants'] }}</div>
                <div class="text-xs text-carbon-500 dark:text-carbon-400 mt-1">Participants</div>
            </div>
            <div class="racing-card p-5 text-center">
                <div class="w-10 h-10 mx-auto mb-2 bg-checkered-yellow-100 dark:bg-checkered-yellow-900/30 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-checkered-yellow-600 dark:text-checkered-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
                <div class="text-2xl font-bold text-checkered-yellow-600 dark:text-checkered-yellow-400">{{ $this->rules['min_races'] }}</div>
                <div class="text-xs text-carbon-500 dark:text-carbon-400 mt-1">Courses min. pour classement</div>
            </div>
        </div>

        {{-- View Tabs --}}
        <div class="racing-card overflow-hidden mb-8">
            <div class="border-b border-carbon-200 dark:border-carbon-700">
                <nav class="flex flex-wrap -mb-px">
                    <button
                        wire:click="switchView('general')"
                        class="px-6 py-4 text-sm font-medium border-b-2 transition-all duration-200 {{ $view === 'general' ? 'border-racing-red-500 text-racing-red-600 dark:text-racing-red-400 bg-racing-red-50/50 dark:bg-racing-red-900/10' : 'border-transparent text-carbon-500 dark:text-carbon-400 hover:text-carbon-700 dark:hover:text-carbon-300 hover:border-carbon-300' }}"
                    >
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                            Classement Général
                        </span>
                    </button>

                    @foreach($this->categories as $category)
                        <button
                            wire:click="switchView('{{ $category->id }}')"
                            class="px-6 py-4 text-sm font-medium border-b-2 transition-all duration-200 {{ $view === (string)$category->id ? 'border-racing-red-500 text-racing-red-600 dark:text-racing-red-400 bg-racing-red-50/50 dark:bg-racing-red-900/10' : 'border-transparent text-carbon-500 dark:text-carbon-400 hover:text-carbon-700 dark:hover:text-carbon-300 hover:border-carbon-300' }}"
                        >
                            {{ $category->name }}
                        </button>
                    @endforeach
                </nav>
            </div>

            {{-- Standings Table --}}
            <div class="overflow-x-auto">
                @if($view === 'general')
                    {{-- General Standings --}}
                    @if($this->generalStandings->isEmpty())
                        <div class="p-8 text-center">
                            <div class="w-16 h-16 mx-auto mb-4 bg-carbon-100 dark:bg-carbon-800 rounded-full flex items-center justify-center">
                                <svg class="h-8 w-8 text-carbon-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </div>
                            <h3 class="text-sm font-medium text-carbon-900 dark:text-white">Aucun classement disponible</h3>
                            <p class="mt-1 text-sm text-carbon-500 dark:text-carbon-400">Le classement sera disponible après la publication des résultats de la première course.</p>
                        </div>
                    @else
                        <table class="min-w-full divide-y divide-carbon-200 dark:divide-carbon-700">
                            <thead class="bg-carbon-50 dark:bg-carbon-800">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-carbon-600 dark:text-carbon-300 uppercase tracking-wider">Rang</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-carbon-600 dark:text-carbon-300 uppercase tracking-wider">Pilote</th>
                                    <th class="px-6 py-4 text-center text-xs font-semibold text-carbon-600 dark:text-carbon-300 uppercase tracking-wider">Courses</th>
                                    <th class="px-6 py-4 text-center text-xs font-semibold text-carbon-600 dark:text-carbon-300 uppercase tracking-wider">Points</th>
                                    <th class="px-6 py-4 text-center text-xs font-semibold text-carbon-600 dark:text-carbon-300 uppercase tracking-wider">Bonus</th>
                                    <th class="px-6 py-4 text-center text-xs font-semibold text-carbon-600 dark:text-carbon-300 uppercase tracking-wider">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-carbon-200 dark:divide-carbon-700">
                                @foreach($this->generalStandings as $standing)
                                    <tr class="hover:bg-carbon-50 dark:hover:bg-carbon-800/50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($standing->rank)
                                                @if($standing->rank === 1)
                                                    <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-gradient-to-br from-yellow-400 to-yellow-500 text-white font-bold shadow-lg">
                                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                            <path d="M10 3a1 1 0 011 1v1.293l1.146-1.147a1 1 0 011.414 1.414L11 8.12V9a1 1 0 11-2 0V8.12L6.44 5.56a1 1 0 011.414-1.414L9 5.293V4a1 1 0 011-1z"/>
                                                            <path fill-rule="evenodd" d="M3 10a7 7 0 1114 0 7 7 0 01-14 0zm7-5a5 5 0 100 10 5 5 0 000-10z" clip-rule="evenodd"/>
                                                        </svg>
                                                    </span>
                                                @elseif($standing->rank === 2)
                                                    <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-gradient-to-br from-gray-300 to-gray-400 text-white font-bold shadow-lg">2</span>
                                                @elseif($standing->rank === 3)
                                                    <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-gradient-to-br from-amber-500 to-amber-600 text-white font-bold shadow-lg">3</span>
                                                @else
                                                    <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-racing-red-100 dark:bg-racing-red-900/50 text-racing-red-700 dark:text-racing-red-300 font-bold">{{ $standing->rank }}</span>
                                                @endif
                                            @else
                                                <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-carbon-100 dark:bg-carbon-700 text-carbon-400 dark:text-carbon-500 font-medium">NC</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10 rounded-full bg-racing-red-100 dark:bg-racing-red-900/50 flex items-center justify-center">
                                                    <span class="text-sm font-medium text-racing-red-700 dark:text-racing-red-300">
                                                        {{ substr($standing->pilot->first_name ?? 'P', 0, 1) }}{{ substr($standing->pilot->last_name ?? '', 0, 1) }}
                                                    </span>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-carbon-900 dark:text-white">
                                                        {{ $standing->pilot->first_name }} {{ $standing->pilot->last_name }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <span class="text-sm text-carbon-700 dark:text-carbon-300">{{ $standing->races_count }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <span class="text-sm text-carbon-700 dark:text-carbon-300">{{ $standing->base_points }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            @if($standing->bonus_points > 0)
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300">
                                                    +{{ $standing->bonus_points }}
                                                </span>
                                            @else
                                                <span class="text-sm text-carbon-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <span class="text-lg font-bold text-racing-red-600 dark:text-racing-red-400">{{ $standing->total_points }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                @else
                    {{-- Category Standings --}}
                    @if($this->categoryStandings->isEmpty())
                        <div class="p-8 text-center">
                            <div class="w-16 h-16 mx-auto mb-4 bg-carbon-100 dark:bg-carbon-800 rounded-full flex items-center justify-center">
                                <svg class="h-8 w-8 text-carbon-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </div>
                            <h3 class="text-sm font-medium text-carbon-900 dark:text-white">Aucun classement pour cette catégorie</h3>
                            <p class="mt-1 text-sm text-carbon-500 dark:text-carbon-400">Aucun pilote n'a encore participé dans cette catégorie.</p>
                        </div>
                    @else
                        <div class="px-6 py-3 bg-racing-red-50 dark:bg-racing-red-900/20 border-b border-racing-red-100 dark:border-racing-red-800">
                            <h3 class="text-sm font-semibold text-racing-red-700 dark:text-racing-red-300 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                </svg>
                                Catégorie : {{ $this->currentCategory?->name }}
                            </h3>
                        </div>
                        <table class="min-w-full divide-y divide-carbon-200 dark:divide-carbon-700">
                            <thead class="bg-carbon-50 dark:bg-carbon-800">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-carbon-600 dark:text-carbon-300 uppercase tracking-wider">Rang</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-carbon-600 dark:text-carbon-300 uppercase tracking-wider">Pilote</th>
                                    <th class="px-6 py-4 text-center text-xs font-semibold text-carbon-600 dark:text-carbon-300 uppercase tracking-wider">Courses</th>
                                    <th class="px-6 py-4 text-center text-xs font-semibold text-carbon-600 dark:text-carbon-300 uppercase tracking-wider">Points</th>
                                    <th class="px-6 py-4 text-center text-xs font-semibold text-carbon-600 dark:text-carbon-300 uppercase tracking-wider">Bonus</th>
                                    <th class="px-6 py-4 text-center text-xs font-semibold text-carbon-600 dark:text-carbon-300 uppercase tracking-wider">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-carbon-200 dark:divide-carbon-700">
                                @foreach($this->categoryStandings as $standing)
                                    <tr class="hover:bg-carbon-50 dark:hover:bg-carbon-800/50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($standing->rank)
                                                @if($standing->rank === 1)
                                                    <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-gradient-to-br from-yellow-400 to-yellow-500 text-white font-bold shadow-lg">1</span>
                                                @elseif($standing->rank === 2)
                                                    <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-gradient-to-br from-gray-300 to-gray-400 text-white font-bold shadow-lg">2</span>
                                                @elseif($standing->rank === 3)
                                                    <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-gradient-to-br from-amber-500 to-amber-600 text-white font-bold shadow-lg">3</span>
                                                @else
                                                    <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-racing-red-100 dark:bg-racing-red-900/50 text-racing-red-700 dark:text-racing-red-300 font-bold">{{ $standing->rank }}</span>
                                                @endif
                                            @else
                                                <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-carbon-100 dark:bg-carbon-700 text-carbon-400 dark:text-carbon-500 font-medium">NC</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10 rounded-full bg-racing-red-100 dark:bg-racing-red-900/50 flex items-center justify-center">
                                                    <span class="text-sm font-medium text-racing-red-700 dark:text-racing-red-300">
                                                        {{ substr($standing->pilot->first_name ?? 'P', 0, 1) }}{{ substr($standing->pilot->last_name ?? '', 0, 1) }}
                                                    </span>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-carbon-900 dark:text-white">
                                                        {{ $standing->pilot->first_name }} {{ $standing->pilot->last_name }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <span class="text-sm text-carbon-700 dark:text-carbon-300">{{ $standing->races_count }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <span class="text-sm text-carbon-700 dark:text-carbon-300">{{ $standing->base_points }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            @if($standing->bonus_points > 0)
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300">
                                                    +{{ $standing->bonus_points }}
                                                </span>
                                            @else
                                                <span class="text-sm text-carbon-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <span class="text-lg font-bold text-racing-red-600 dark:text-racing-red-400">{{ $standing->total_points }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                @endif
            </div>
        </div>

        {{-- Rules Info --}}
        <div class="racing-card p-6 border-l-4 border-blue-500">
            <h3 class="text-sm font-semibold text-carbon-800 dark:text-carbon-200 mb-3 flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Règles du championnat
            </h3>
            <ul class="text-sm text-carbon-600 dark:text-carbon-400 space-y-2">
                <li class="flex items-start gap-2">
                    <span class="w-1.5 h-1.5 bg-racing-red-500 rounded-full mt-1.5 flex-shrink-0"></span>
                    Minimum <strong class="text-carbon-900 dark:text-white">{{ $this->rules['min_races'] }} courses</strong> disputées pour être classé
                </li>
                <li class="flex items-start gap-2">
                    <span class="w-1.5 h-1.5 bg-racing-red-500 rounded-full mt-1.5 flex-shrink-0"></span>
                    Bonus de <strong class="text-carbon-900 dark:text-white">{{ $this->rules['bonus_points'] }} points</strong> pour les pilotes ayant participé à toutes les courses
                </li>
                <li class="flex items-start gap-2">
                    <span class="w-1.5 h-1.5 bg-racing-red-500 rounded-full mt-1.5 flex-shrink-0"></span>
                    <strong class="text-carbon-900 dark:text-white">NC</strong> = Non Classé (nombre de courses insuffisant)
                </li>
            </ul>
        </div>

        {{-- CTA for registration --}}
        @guest
            <div class="mt-10 relative overflow-hidden rounded-2xl">
                {{-- Background with gradient --}}
                <div class="absolute inset-0 bg-gradient-to-r from-racing-red-600 to-racing-red-800"></div>

                {{-- Decorative elements --}}
                <div class="absolute top-0 right-0 w-64 h-64 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/2"></div>
                <div class="absolute bottom-0 left-0 w-48 h-48 bg-black/10 rounded-full translate-y-1/2 -translate-x-1/2"></div>

                {{-- Checkered pattern --}}
                <div class="absolute bottom-0 right-0 w-32 h-8 opacity-20">
                    <div class="grid grid-cols-8 h-full">
                        @for($i = 0; $i < 16; $i++)
                            <div class="{{ $i % 2 === ($i < 8 ? 0 : 1) ? 'bg-white' : 'bg-transparent' }}"></div>
                        @endfor
                    </div>
                </div>

                <div class="relative z-10 p-8 md:p-10 text-center">
                    <h3 class="text-2xl md:text-3xl font-display font-bold text-white mb-3">Rejoignez le championnat !</h3>
                    <p class="text-racing-red-100 mb-6 max-w-lg mx-auto">Créez votre compte pilote pour participer aux courses et apparaître au classement.</p>
                    <a href="{{ route('register') }}"
                       class="inline-flex items-center px-8 py-4 bg-white text-racing-red-600 font-bold rounded-xl hover:bg-racing-red-50 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                        </svg>
                        S'inscrire maintenant
                    </a>
                </div>
            </div>
        @endguest
    @endif
</div>
