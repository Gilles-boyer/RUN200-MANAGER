<div>
    {{-- Racing Header --}}
    <div class="relative mb-8 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-8 bg-racing-gradient-subtle overflow-hidden">
        <div class="absolute top-0 right-0 w-64 h-64 bg-racing-red-500/5 rounded-full -translate-y-1/2 translate-x-1/2"></div>

        <div class="relative">
            <h1 class="text-3xl font-bold text-carbon-900 dark:text-white flex items-center gap-3">
                <span>🏆</span> Mon Championnat
            </h1>
            @if($season)
                <p class="mt-2 text-carbon-600 dark:text-carbon-400">
                    Saison {{ $season->name }}
                </p>
            @endif
        </div>
    </div>

    @if(!$season)
        <x-racing.card>
            <x-racing.empty-state
                icon="⚠️"
                title="Aucune saison active"
                description="Il n'y a pas de saison de championnat active actuellement."
            />
        </x-racing.card>
    @elseif(!$this->pilot)
        <x-racing.card>
            <x-racing.empty-state
                icon="👤"
                title="Profil pilote requis"
                description="Créez votre profil pilote pour voir vos statistiques de championnat."
                actionLabel="Créer mon profil"
                actionHref="{{ route('pilot.profile.edit') }}"
            />
        </x-racing.card>
    @else
        {{-- My Standing Card --}}
        <x-racing.card noPadding class="mb-6 overflow-hidden">
            <div class="bg-racing-gradient px-6 py-4">
                <h2 class="text-lg font-bold text-white flex items-center gap-2">🎯 Mon classement</h2>
            </div>

            @if($this->generalStanding)
                <div class="p-6">
                    <div class="flex flex-col sm:flex-row items-center sm:justify-between gap-6">
                        <div class="flex items-center gap-4">
                            {{-- Rank Badge --}}
                            <div class="flex-shrink-0">
                                @if($this->generalStanding->rank)
                                    @if($this->generalStanding->rank === 1)
                                        <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-yellow-400 to-yellow-600 flex items-center justify-center shadow-lg shadow-yellow-500/30">
                                            <span class="text-3xl font-black text-yellow-900">1</span>
                                        </div>
                                    @elseif($this->generalStanding->rank === 2)
                                        <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-gray-300 to-gray-500 flex items-center justify-center shadow-lg shadow-gray-400/30">
                                            <span class="text-3xl font-black text-gray-900">2</span>
                                        </div>
                                    @elseif($this->generalStanding->rank === 3)
                                        <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-amber-500 to-amber-700 flex items-center justify-center shadow-lg shadow-amber-500/30">
                                            <span class="text-3xl font-black text-white">3</span>
                                        </div>
                                    @else
                                        <div class="w-20 h-20 rounded-2xl bg-carbon-700 border border-carbon-600 flex items-center justify-center">
                                            <span class="text-3xl font-black text-racing-red-500">{{ $this->generalStanding->rank }}</span>
                                        </div>
                                    @endif
                                @else
                                    <div class="w-20 h-20 rounded-2xl bg-carbon-700 border border-carbon-600 flex items-center justify-center">
                                        <span class="text-xl font-bold text-gray-500">NC</span>
                                    </div>
                                @endif
                            </div>

                            <div>
                                <p class="text-sm text-gray-400">{{ $this->rankingStatus }}</p>
                                <p class="text-4xl font-black text-white">
                                    {{ $this->generalStanding->total_points }}
                                    <span class="text-lg font-normal text-gray-400">points</span>
                                </p>
                            </div>
                        </div>

                        <div class="text-center sm:text-right">
                            <div class="text-sm text-gray-400">Courses disputées</div>
                            <div class="text-3xl font-bold text-white">
                                {{ $this->generalStanding->races_count }} <span class="text-gray-500">/</span> {{ $this->seasonStats['published_races'] }}
                            </div>
                        </div>
                    </div>

                    {{-- Points breakdown --}}
                    <div class="mt-6 grid grid-cols-3 gap-4">
                        <div class="bg-carbon-700/30 rounded-xl p-4 text-center border border-carbon-600/50">
                            <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold">Points de base</p>
                            <p class="text-2xl font-bold text-white mt-1">{{ $this->generalStanding->base_points }}</p>
                        </div>
                        <div class="bg-carbon-700/30 rounded-xl p-4 text-center border border-carbon-600/50">
                            <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold">Bonus</p>
                            <p class="text-2xl font-bold mt-1 {{ $this->generalStanding->bonus_points > 0 ? 'text-status-success' : 'text-gray-500' }}">
                                {{ $this->generalStanding->bonus_points > 0 ? '+' . $this->generalStanding->bonus_points : '—' }}
                            </p>
                        </div>
                        <div class="bg-racing-red-500/10 rounded-xl p-4 text-center border border-racing-red-500/30">
                            <p class="text-xs text-racing-red-400 uppercase tracking-wide font-semibold">Total</p>
                            <p class="text-2xl font-black text-racing-red-500 mt-1">{{ $this->generalStanding->total_points }}</p>
                        </div>
                    </div>

                    {{-- Bonus status --}}
                    <div class="mt-4 p-4 rounded-xl {{ $this->generalStanding->bonus_points > 0 ? 'bg-status-success/10 border border-status-success/30' : 'bg-carbon-700/30 border border-carbon-600/50' }}">
                        <p class="text-sm {{ $this->generalStanding->bonus_points > 0 ? 'text-status-success' : 'text-gray-400' }}">
                            {{ $this->bonusStatus }}
                        </p>
                    </div>
                </div>
            @else
                <div class="p-8">
                    <x-racing.empty-state
                        icon="📊"
                        title="Pas encore de participation"
                        description="Vous n'avez pas encore participé à une course cette saison."
                    />
                </div>
            @endif
        </x-racing.card>

        {{-- View Toggle --}}
        <div class="flex rounded-xl bg-carbon-800 border border-carbon-700/50 p-1 mb-6">
            <button
                wire:click="switchView('general')"
                class="flex-1 px-4 py-3 text-sm font-semibold rounded-lg transition-all duration-200
                {{ $view === 'general' ? 'bg-racing-gradient text-white shadow-lg shadow-racing-red-500/25' : 'text-gray-400 hover:text-white hover:bg-carbon-700/50' }}"
            >
                🏅 Top 10 Général
            </button>
            <button
                wire:click="switchView('categories')"
                class="flex-1 px-4 py-3 text-sm font-semibold rounded-lg transition-all duration-200
                {{ $view === 'categories' ? 'bg-racing-gradient text-white shadow-lg shadow-racing-red-500/25' : 'text-gray-400 hover:text-white hover:bg-carbon-700/50' }}"
            >
                📂 Mes catégories
            </button>
        </div>

        @if($view === 'general')
            {{-- Top 10 General Standings --}}
            <x-racing.card noPadding>
                <div class="px-6 py-4 border-b border-carbon-700/50">
                    <h3 class="text-lg font-semibold text-white">🏆 Top 10 - Classement Général</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="bg-carbon-800/50 border-b border-carbon-700/50">
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Rang</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Pilote</th>
                                <th class="px-6 py-4 text-center text-xs font-semibold text-gray-400 uppercase tracking-wider">Courses</th>
                                <th class="px-6 py-4 text-center text-xs font-semibold text-gray-400 uppercase tracking-wider">Points</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-carbon-700/50">
                            @forelse($this->topGeneralStandings as $standing)
                                <tr class="hover:bg-carbon-700/30 transition-colors {{ $standing->pilot_id === $this->pilot?->id ? 'bg-racing-red-500/10' : '' }}">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($standing->rank === 1)
                                            <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-yellow-400 to-yellow-600 text-yellow-900 font-black shadow-lg shadow-yellow-500/30">1</span>
                                        @elseif($standing->rank === 2)
                                            <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-gray-300 to-gray-500 text-gray-900 font-black shadow-lg shadow-gray-400/30">2</span>
                                        @elseif($standing->rank === 3)
                                            <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-amber-500 to-amber-700 text-white font-black shadow-lg shadow-amber-500/30">3</span>
                                        @else
                                            <span class="text-white font-bold">{{ $standing->rank }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="font-semibold {{ $standing->pilot_id === $this->pilot?->id ? 'text-racing-red-500' : 'text-white' }}">
                                            {{ $standing->pilot->full_name ?? 'Pilote' }}
                                            @if($standing->pilot_id === $this->pilot?->id)
                                                <span class="ml-2 text-xs bg-racing-red-500/20 text-racing-red-400 px-2 py-0.5 rounded-full">(vous)</span>
                                            @endif
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-white">
                                        {{ $standing->races_count }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span class="font-bold text-checkered-yellow-500">{{ $standing->total_points }}</span>
                                        @if($standing->bonus_points > 0)
                                            <span class="ml-1 text-xs text-status-success">+{{ $standing->bonus_points }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                                        Aucun classement disponible.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-racing.card>
        @else
            {{-- My Category Standings --}}
            <x-racing.card noPadding>
                <div class="px-6 py-4 border-b border-carbon-700/50">
                    <h3 class="text-lg font-semibold text-white">📂 Mes classements par catégorie</h3>
                </div>

                @forelse($this->categoryStandings as $catStanding)
                    <div class="px-6 py-4 {{ !$loop->last ? 'border-b border-carbon-700/50' : '' }} hover:bg-carbon-700/20 transition-colors">
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="font-semibold text-white">{{ $catStanding->category->name ?? 'Catégorie' }}</h4>
                                <p class="text-sm text-gray-500">{{ $catStanding->races_count }} course(s)</p>
                            </div>
                            <div class="flex items-center gap-6">
                                @if($catStanding->rank)
                                    <div class="text-center">
                                        <p class="text-xs text-gray-500 uppercase font-semibold">Rang</p>
                                        @if($catStanding->rank <= 3)
                                            <p class="text-2xl font-black text-checkered-yellow-500">{{ $catStanding->rank }}</p>
                                        @else
                                            <p class="text-2xl font-bold text-white">{{ $catStanding->rank }}</p>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-sm text-gray-500 italic">Non classé</span>
                                @endif
                                <div class="text-center">
                                    <p class="text-xs text-gray-500 uppercase font-semibold">Points</p>
                                    <p class="text-2xl font-bold text-racing-red-500">{{ $catStanding->total_points }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-8 text-center text-gray-500">
                        Aucune participation dans les catégories cette saison.
                    </div>
                @endforelse
            </x-racing.card>
        @endif

        {{-- Rules reminder --}}
        <x-racing.card class="mt-6">
            <h4 class="text-sm font-semibold text-white mb-3 flex items-center gap-2">📋 Règlement du championnat</h4>
            <ul class="text-sm text-gray-400 space-y-2">
                <li class="flex items-center gap-2">
                    <span class="w-1.5 h-1.5 bg-racing-red-500 rounded-full"></span>
                    Minimum <strong class="text-white">{{ $this->seasonStats['min_races_required'] }} courses</strong> pour être classé
                </li>
                <li class="flex items-center gap-2">
                    <span class="w-1.5 h-1.5 bg-racing-red-500 rounded-full"></span>
                    Bonus de <strong class="text-status-success">+{{ $this->seasonStats['bonus_points'] }} points</strong> si participation à toutes les courses
                </li>
                <li class="flex items-center gap-2">
                    <span class="w-1.5 h-1.5 bg-racing-red-500 rounded-full"></span>
                    Points : <span class="text-checkered-yellow-500 font-mono">1er=25, 2ème=20, 3ème=16, 4ème=14, 5ème=10, 6ème=8, autres=5</span>
                </li>
            </ul>
        </x-racing.card>
    @endif
</div>
