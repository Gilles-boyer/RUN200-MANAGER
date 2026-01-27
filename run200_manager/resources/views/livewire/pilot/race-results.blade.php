<div>
    {{-- Racing Header --}}
    <div class="relative mb-8 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-8 bg-racing-gradient-subtle overflow-hidden text-center">
        <div class="absolute top-0 right-0 w-64 h-64 bg-racing-red-500/5 rounded-full -translate-y-1/2 translate-x-1/2"></div>
        <div class="absolute bottom-0 left-0 w-48 h-48 bg-checkered-yellow-500/5 rounded-full translate-y-1/2 -translate-x-1/2"></div>

        <div class="relative">
            <h1 class="text-3xl font-bold text-white flex items-center justify-center gap-3">
                <span>🏁</span> Résultats : {{ $race->name }}
            </h1>
            <p class="mt-2 text-lg text-gray-400">
                {{ $race->race_date->format('d F Y') }}
                @if($race->season)
                    — <span class="text-checkered-yellow-500">{{ $race->season->name }}</span>
                @endif
            </p>
        </div>
    </div>

    {{-- Podium --}}
    @if($this->podium->count() >= 3)
        <x-racing.card class="mb-6 overflow-hidden">
            <div class="bg-gradient-to-r from-checkered-yellow-500/10 via-carbon-800 to-checkered-yellow-500/10 -mx-6 -mt-6 px-6 pt-6 pb-8">
                <h2 class="text-xl font-bold text-center text-white mb-8">🏆 Podium</h2>
                <div class="flex justify-center items-end gap-4 sm:gap-8">
                    {{-- 2nd place --}}
                    <div class="text-center">
                        <div class="w-20 h-20 sm:w-24 sm:h-24 mx-auto bg-gradient-to-br from-gray-300 to-gray-500 rounded-2xl flex items-center justify-center mb-3 shadow-lg shadow-gray-400/30">
                            <span class="text-2xl sm:text-3xl font-black text-gray-900">2</span>
                        </div>
                        <div class="bg-carbon-700/50 backdrop-blur rounded-xl px-3 sm:px-4 py-6 sm:py-8 min-w-[100px] sm:min-w-[120px] border border-carbon-600/50">
                            <p class="font-bold text-white text-sm truncate">{{ $this->podium[1]->pilot_name }}</p>
                            <p class="text-xs text-racing-red-500 mt-1 font-bold">#{{ $this->podium[1]->bib }}</p>
                            <p class="text-sm font-mono text-checkered-yellow-500 mt-2 font-semibold">{{ $this->podium[1]->formatted_time }}</p>
                        </div>
                    </div>

                    {{-- 1st place --}}
                    <div class="text-center -mt-4">
                        <div class="w-24 h-24 sm:w-32 sm:h-32 mx-auto bg-gradient-to-br from-yellow-400 to-yellow-600 rounded-2xl flex items-center justify-center mb-3 shadow-xl shadow-yellow-500/40 ring-4 ring-yellow-400/30">
                            <span class="text-3xl sm:text-4xl font-black text-yellow-900">1</span>
                        </div>
                        <div class="bg-gradient-to-b from-yellow-500/20 to-carbon-700/50 backdrop-blur rounded-xl px-4 sm:px-6 py-8 sm:py-12 min-w-[120px] sm:min-w-[160px] border border-yellow-500/30">
                            <p class="font-bold text-white text-base sm:text-lg">{{ $this->podium[0]->pilot_name }}</p>
                            <p class="text-sm text-racing-red-500 mt-1 font-bold">#{{ $this->podium[0]->bib }}</p>
                            <p class="text-lg sm:text-xl font-mono font-black text-checkered-yellow-500 mt-3">{{ $this->podium[0]->formatted_time }}</p>
                        </div>
                    </div>

                    {{-- 3rd place --}}
                    <div class="text-center">
                        <div class="w-16 h-16 sm:w-20 sm:h-20 mx-auto bg-gradient-to-br from-amber-500 to-amber-700 rounded-2xl flex items-center justify-center mb-3 shadow-lg shadow-amber-500/30">
                            <span class="text-xl sm:text-2xl font-black text-white">3</span>
                        </div>
                        <div class="bg-carbon-700/50 backdrop-blur rounded-xl px-3 sm:px-4 py-4 sm:py-6 min-w-[90px] sm:min-w-[100px] border border-carbon-600/50">
                            <p class="font-bold text-white text-xs sm:text-sm truncate">{{ $this->podium[2]->pilot_name }}</p>
                            <p class="text-xs text-racing-red-500 mt-1 font-bold">#{{ $this->podium[2]->bib }}</p>
                            <p class="text-sm font-mono text-checkered-yellow-500 mt-2 font-semibold">{{ $this->podium[2]->formatted_time }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </x-racing.card>
    @endif

    {{-- Statistics --}}
    @if(!empty($this->statistics))
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <x-racing.stat-card
                label="Participants"
                :value="$this->statistics['total_participants']"
                icon="👥"
            />
            <x-racing.stat-card
                label="Meilleur temps"
                :value="$this->statistics['fastest_time']"
                icon="⚡"
            />
            <x-racing.stat-card
                label="Temps moyen"
                :value="$this->statistics['average_time'] ?? '—'"
                icon="⏱️"
            />
            <x-racing.stat-card
                label="Catégories"
                :value="$this->statistics['categories_count']"
                icon="📂"
            />
        </div>
    @endif

    {{-- Filters --}}
    <x-racing.card class="mb-6">
        <div class="flex flex-col sm:flex-row gap-4">
            {{-- Search --}}
            <div class="flex-1">
                <x-racing.form.input
                    wire:model.live.debounce.300ms="searchQuery"
                    placeholder="Rechercher un pilote, dossard..."
                    icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>'
                />
            </div>

            {{-- Category filter --}}
            @if(count($this->categories) > 0)
                <div class="sm:w-56">
                    <x-racing.form.select wire:model.live="categoryFilter">
                        <option value="">Toutes les catégories</option>
                        @foreach($this->categories as $category)
                            <option value="{{ $category }}">{{ $category }}</option>
                        @endforeach
                    </x-racing.form.select>
                </div>
            @endif
        </div>
    </x-racing.card>

    {{-- Results Table --}}
    <x-racing.card noPadding>
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="bg-carbon-800/50 border-b border-carbon-700/50">
                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Pos</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Dossard</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Pilote</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Voiture</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Catégorie</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Temps</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-carbon-700/50">
                    @forelse($this->results as $result)
                        <tr class="hover:bg-carbon-700/30 transition-colors @if($result->position <= 3) bg-checkered-yellow-500/5 @endif">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl font-black text-sm
                                    @if($result->position === 1) bg-gradient-to-br from-yellow-400 to-yellow-600 text-yellow-900 shadow-lg shadow-yellow-500/30
                                    @elseif($result->position === 2) bg-gradient-to-br from-gray-300 to-gray-500 text-gray-900 shadow-lg shadow-gray-400/30
                                    @elseif($result->position === 3) bg-gradient-to-br from-amber-500 to-amber-700 text-white shadow-lg shadow-amber-500/30
                                    @else bg-carbon-700 text-gray-400 border border-carbon-600
                                    @endif">
                                    {{ $result->position }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-lg font-bold text-racing-red-500">#{{ $result->bib }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="font-semibold text-white">{{ $result->pilot_name }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-400">
                                {{ $result->car_description ?? '—' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($result->category_name)
                                    <span class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-semibold bg-carbon-700 text-gray-300 border border-carbon-600">
                                        {{ $result->category_name }}
                                    </span>
                                @else
                                    <span class="text-gray-500">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="font-mono font-semibold
                                    @if($result->position === 1) text-checkered-yellow-500
                                    @else text-white
                                    @endif">
                                    {{ $result->formatted_time }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12">
                                <x-racing.empty-state
                                    icon="📋"
                                    title="Aucun résultat trouvé"
                                    description="Essayez de modifier vos filtres de recherche."
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($this->results->hasPages())
            <div class="px-6 py-4 border-t border-carbon-700/50">
                {{ $this->results->links() }}
            </div>
        @endif
    </x-racing.card>
</div>
