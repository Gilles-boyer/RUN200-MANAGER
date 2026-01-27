<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    {{-- Header --}}
    <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
            Classement du Championnat
        </h1>
        @if($this->season)
            <p class="mt-2 text-lg text-indigo-600 dark:text-indigo-400 font-semibold">
                Saison {{ $this->season->name }}
            </p>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Classement provisoire basé sur {{ $this->seasonStats['published_races'] }} course(s) disputée(s)
            </p>
        @endif
    </div>

    @if(!$this->season)
        {{-- No active season --}}
        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-xl p-8 text-center">
            <svg class="mx-auto h-16 w-16 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <h3 class="mt-4 text-lg font-semibold text-yellow-800 dark:text-yellow-200">Aucune saison active</h3>
            <p class="mt-2 text-yellow-600 dark:text-yellow-300">Le classement du championnat sera disponible lors de l'ouverture de la prochaine saison.</p>
        </div>
    @else
        {{-- Season Stats --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm p-5 text-center border border-gray-200 dark:border-zinc-700">
                <div class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">{{ $this->seasonStats['published_races'] }}</div>
                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Courses comptabilisées</div>
            </div>
            <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm p-5 text-center border border-gray-200 dark:border-zinc-700">
                <div class="text-2xl font-bold text-gray-700 dark:text-gray-300">{{ $this->seasonStats['total_races'] }}</div>
                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Courses prévues</div>
            </div>
            <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm p-5 text-center border border-gray-200 dark:border-zinc-700">
                <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $this->seasonStats['participants'] }}</div>
                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Participants</div>
            </div>
            <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm p-5 text-center border border-gray-200 dark:border-zinc-700">
                <div class="text-2xl font-bold text-orange-600 dark:text-orange-400">{{ $this->rules['min_races'] }}</div>
                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Courses min. pour classement</div>
            </div>
        </div>

        {{-- View Tabs --}}
        <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-gray-200 dark:border-zinc-700 mb-6">
            <div class="border-b border-gray-200 dark:border-zinc-700">
                <nav class="flex flex-wrap -mb-px">
                    <button
                        wire:click="switchView('general')"
                        class="px-6 py-4 text-sm font-medium border-b-2 transition-colors {{ $view === 'general' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300' }}"
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
                            class="px-6 py-4 text-sm font-medium border-b-2 transition-colors {{ $view === (string)$category->id ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300' }}"
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
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Aucun classement disponible</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Le classement sera disponible après la publication des résultats de la première course.</p>
                        </div>
                    @else
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-zinc-700">
                            <thead class="bg-gray-50 dark:bg-zinc-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Rang</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Pilote</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Courses</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Points</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Bonus</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-zinc-700">
                                @foreach($this->generalStandings as $standing)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-zinc-700/50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($standing->rank)
                                                @if($standing->rank === 1)
                                                    <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-yellow-400 text-white font-bold shadow-lg">
                                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                            <path d="M10 3a1 1 0 011 1v1.293l1.146-1.147a1 1 0 011.414 1.414L11 8.12V9a1 1 0 11-2 0V8.12L6.44 5.56a1 1 0 011.414-1.414L9 5.293V4a1 1 0 011-1z"/>
                                                            <path fill-rule="evenodd" d="M3 10a7 7 0 1114 0 7 7 0 01-14 0zm7-5a5 5 0 100 10 5 5 0 000-10z" clip-rule="evenodd"/>
                                                        </svg>
                                                    </span>
                                                @elseif($standing->rank === 2)
                                                    <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-gray-400 text-white font-bold shadow-lg">2</span>
                                                @elseif($standing->rank === 3)
                                                    <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-amber-600 text-white font-bold shadow-lg">3</span>
                                                @else
                                                    <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 font-bold">{{ $standing->rank }}</span>
                                                @endif
                                            @else
                                                <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-gray-100 dark:bg-zinc-600 text-gray-400 dark:text-gray-500 font-medium">NC</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10 rounded-full bg-indigo-100 dark:bg-indigo-900/50 flex items-center justify-center">
                                                    <span class="text-sm font-medium text-indigo-700 dark:text-indigo-300">
                                                        {{ substr($standing->pilot->first_name ?? 'P', 0, 1) }}{{ substr($standing->pilot->last_name ?? '', 0, 1) }}
                                                    </span>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                        {{ $standing->pilot->first_name }} {{ $standing->pilot->last_name }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <span class="text-sm text-gray-700 dark:text-gray-300">{{ $standing->races_count }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <span class="text-sm text-gray-700 dark:text-gray-300">{{ $standing->base_points }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            @if($standing->bonus_points > 0)
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300">
                                                    +{{ $standing->bonus_points }}
                                                </span>
                                            @else
                                                <span class="text-sm text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <span class="text-lg font-bold text-indigo-600 dark:text-indigo-400">{{ $standing->total_points }}</span>
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
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Aucun classement pour cette catégorie</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Aucun pilote n'a encore participé dans cette catégorie.</p>
                        </div>
                    @else
                        <div class="px-6 py-3 bg-indigo-50 dark:bg-indigo-900/20 border-b border-indigo-100 dark:border-indigo-800">
                            <h3 class="text-sm font-semibold text-indigo-700 dark:text-indigo-300">
                                Catégorie : {{ $this->currentCategory?->name }}
                            </h3>
                        </div>
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-zinc-700">
                            <thead class="bg-gray-50 dark:bg-zinc-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Rang</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Pilote</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Courses</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Points</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Bonus</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-zinc-700">
                                @foreach($this->categoryStandings as $standing)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-zinc-700/50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($standing->rank)
                                                @if($standing->rank === 1)
                                                    <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-yellow-400 text-white font-bold shadow-lg">1</span>
                                                @elseif($standing->rank === 2)
                                                    <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-gray-400 text-white font-bold shadow-lg">2</span>
                                                @elseif($standing->rank === 3)
                                                    <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-amber-600 text-white font-bold shadow-lg">3</span>
                                                @else
                                                    <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 font-bold">{{ $standing->rank }}</span>
                                                @endif
                                            @else
                                                <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-gray-100 dark:bg-zinc-600 text-gray-400 dark:text-gray-500 font-medium">NC</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10 rounded-full bg-indigo-100 dark:bg-indigo-900/50 flex items-center justify-center">
                                                    <span class="text-sm font-medium text-indigo-700 dark:text-indigo-300">
                                                        {{ substr($standing->pilot->first_name ?? 'P', 0, 1) }}{{ substr($standing->pilot->last_name ?? '', 0, 1) }}
                                                    </span>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                        {{ $standing->pilot->first_name }} {{ $standing->pilot->last_name }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <span class="text-sm text-gray-700 dark:text-gray-300">{{ $standing->races_count }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <span class="text-sm text-gray-700 dark:text-gray-300">{{ $standing->base_points }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            @if($standing->bonus_points > 0)
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300">
                                                    +{{ $standing->bonus_points }}
                                                </span>
                                            @else
                                                <span class="text-sm text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <span class="text-lg font-bold text-indigo-600 dark:text-indigo-400">{{ $standing->total_points }}</span>
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
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-6">
            <h3 class="text-sm font-semibold text-blue-800 dark:text-blue-200 mb-3 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Règles du championnat
            </h3>
            <ul class="text-sm text-blue-700 dark:text-blue-300 space-y-1">
                <li>• Minimum <strong>{{ $this->rules['min_races'] }} courses</strong> disputées pour être classé</li>
                <li>• Bonus de <strong>{{ $this->rules['bonus_points'] }} points</strong> pour les pilotes ayant participé à toutes les courses</li>
                <li>• NC = Non Classé (nombre de courses insuffisant)</li>
            </ul>
        </div>

        {{-- CTA for registration --}}
        @guest
            <div class="mt-8 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-xl p-8 text-center">
                <h3 class="text-xl font-bold text-white mb-2">Rejoignez le championnat !</h3>
                <p class="text-indigo-100 mb-6">Créez votre compte pilote pour participer aux courses et apparaître au classement.</p>
                <a href="{{ route('register') }}"
                   class="inline-flex items-center px-6 py-3 bg-white text-indigo-600 font-semibold rounded-lg hover:bg-indigo-50 transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                    </svg>
                    S'inscrire maintenant
                </a>
            </div>
        @endguest
    @endif
</div>
