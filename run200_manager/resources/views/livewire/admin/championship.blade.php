<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Racing Header --}}
        <div class="mb-8">
            <div class="bg-racing-gradient rounded-2xl p-6 border border-carbon-700/50">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-white flex items-center gap-3">
                            <span class="text-3xl">🏆</span>
                            Championnat {{ $season->name }}
                        </h1>
                        <p class="mt-1 text-carbon-400">
                            Classements général et par catégorie
                        </p>
                    </div>
                    <x-racing.button
                        wire:click="confirmRebuild"
                        variant="secondary"
                        size="lg"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Recalculer
                    </x-racing.button>
                </div>
            </div>
        </div>

        {{-- Messages --}}
        @if($successMessage)
            <div class="mb-6">
                <x-racing.alert type="success">
                    {{ $successMessage }}
                </x-racing.alert>
            </div>
        @endif

        @if($errorMessage)
            <div class="mb-6">
                <x-racing.alert type="error">
                    {{ $errorMessage }}
                </x-racing.alert>
            </div>
        @endif

        {{-- Stats Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-8">
            <x-racing.stat-card
                label="Courses publiées"
                :value="$this->seasonStats['published_races'] . ' / ' . $this->seasonStats['total_races']"
                icon="🏁"
                color="blue"
            />
            <x-racing.stat-card
                label="Pilotes classés"
                :value="$this->seasonStats['ranked_pilots']"
                icon="🎖️"
                color="purple"
            />
            <x-racing.stat-card
                label="Total participants"
                :value="$this->seasonStats['total_pilots']"
                icon="👥"
                color="cyan"
            />
            <x-racing.stat-card
                label="Avec bonus"
                :value="$this->seasonStats['pilots_with_bonus']"
                icon="⭐"
                color="green"
            />
            <x-racing.stat-card
                label="Courses min."
                :value="$this->seasonStats['min_races_required']"
                icon="📋"
                color="orange"
            />
        </div>

        {{-- Navigation Tabs --}}
        <x-racing.card class="mb-6 overflow-hidden">
            <div class="border-b border-carbon-700/50">
                <nav class="flex -mb-px overflow-x-auto">
                    <button
                        wire:click="selectGeneral"
                        class="py-4 px-6 text-sm font-medium whitespace-nowrap transition-all duration-200 {{ $view === 'general'
                            ? 'border-b-2 border-racing-red-500 text-racing-red-500'
                            : 'text-carbon-400 hover:text-white hover:bg-carbon-700/30' }}"
                    >
                        <span class="mr-2">🏆</span>
                        Classement Général
                    </button>
                    @foreach($this->categories as $category)
                        <button
                            wire:click="selectCategory('{{ $category->id }}')"
                            class="py-4 px-6 text-sm font-medium whitespace-nowrap transition-all duration-200 {{ $selectedCategoryId == $category->id
                                ? 'border-b-2 border-racing-red-500 text-racing-red-500'
                                : 'text-carbon-400 hover:text-white hover:bg-carbon-700/30' }}"
                        >
                            {{ $category->name }}
                        </button>
                    @endforeach
                </nav>
            </div>
        </x-racing.card>

        {{-- Standings Table --}}
        <x-racing.card>
            @if($view === 'general')
                {{-- General Standings --}}
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="border-b border-carbon-700/50">
                                <th class="px-6 py-4 text-left text-xs font-semibold text-carbon-400 uppercase tracking-wider">Rang</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-carbon-400 uppercase tracking-wider">Pilote</th>
                                <th class="px-6 py-4 text-center text-xs font-semibold text-carbon-400 uppercase tracking-wider">Courses</th>
                                <th class="px-6 py-4 text-center text-xs font-semibold text-carbon-400 uppercase tracking-wider">Points</th>
                                <th class="px-6 py-4 text-center text-xs font-semibold text-carbon-400 uppercase tracking-wider">Bonus</th>
                                <th class="px-6 py-4 text-center text-xs font-semibold text-carbon-400 uppercase tracking-wider">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-carbon-700/30">
                            @forelse($this->generalStandings as $standing)
                                <tr class="hover:bg-carbon-700/20 transition-colors {{ $standing->rank && $standing->rank <= 3 ? 'bg-checkered-yellow-500/5' : '' }}">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($standing->rank)
                                            @if($standing->rank === 1)
                                                <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-gradient-to-br from-yellow-400 to-yellow-600 text-carbon-900 font-bold text-lg shadow-lg shadow-yellow-500/30">
                                                    1
                                                </span>
                                            @elseif($standing->rank === 2)
                                                <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-gradient-to-br from-gray-300 to-gray-500 text-carbon-900 font-bold text-lg shadow-lg shadow-gray-500/30">
                                                    2
                                                </span>
                                            @elseif($standing->rank === 3)
                                                <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-gradient-to-br from-amber-500 to-amber-700 text-white font-bold text-lg shadow-lg shadow-amber-500/30">
                                                    3
                                                </span>
                                            @else
                                                <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-carbon-700 text-white font-bold">
                                                    {{ $standing->rank }}
                                                </span>
                                            @endif
                                        @else
                                            <span class="text-carbon-500 italic">NC</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div>
                                            <div class="text-sm font-semibold text-white">
                                                {{ $standing->pilot->full_name ?? 'Pilote inconnu' }}
                                            </div>
                                            <div class="text-xs text-carbon-400">
                                                Licence: {{ $standing->pilot->license_number ?? '-' }}
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span class="text-sm text-white font-medium">{{ $standing->races_count }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span class="text-sm text-white font-medium">{{ $standing->base_points }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        @if($standing->bonus_points > 0)
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-status-success/20 text-status-success border border-status-success/30">
                                                +{{ $standing->bonus_points }}
                                            </span>
                                        @else
                                            <span class="text-carbon-500">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span class="text-lg font-bold text-racing-red-500">{{ $standing->total_points }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12">
                                        <x-racing.empty-state
                                            icon="🏆"
                                            title="Aucun classement disponible"
                                            description="Publiez des résultats pour générer le classement du championnat."
                                        />
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($this->generalStandings->hasPages())
                    <div class="px-6 py-4 border-t border-carbon-700/50">
                        {{ $this->generalStandings->links() }}
                    </div>
                @endif
            @else
                {{-- Category Standings --}}
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="border-b border-carbon-700/50">
                                <th class="px-6 py-4 text-left text-xs font-semibold text-carbon-400 uppercase tracking-wider">Rang</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-carbon-400 uppercase tracking-wider">Pilote</th>
                                <th class="px-6 py-4 text-center text-xs font-semibold text-carbon-400 uppercase tracking-wider">Courses</th>
                                <th class="px-6 py-4 text-center text-xs font-semibold text-carbon-400 uppercase tracking-wider">Points</th>
                                <th class="px-6 py-4 text-center text-xs font-semibold text-carbon-400 uppercase tracking-wider">Bonus</th>
                                <th class="px-6 py-4 text-center text-xs font-semibold text-carbon-400 uppercase tracking-wider">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-carbon-700/30">
                            @forelse($this->categoryStandings as $standing)
                                <tr class="hover:bg-carbon-700/20 transition-colors {{ $standing->rank && $standing->rank <= 3 ? 'bg-checkered-yellow-500/5' : '' }}">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($standing->rank)
                                            @if($standing->rank === 1)
                                                <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-gradient-to-br from-yellow-400 to-yellow-600 text-carbon-900 font-bold text-lg shadow-lg shadow-yellow-500/30">
                                                    1
                                                </span>
                                            @elseif($standing->rank === 2)
                                                <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-gradient-to-br from-gray-300 to-gray-500 text-carbon-900 font-bold text-lg shadow-lg shadow-gray-500/30">
                                                    2
                                                </span>
                                            @elseif($standing->rank === 3)
                                                <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-gradient-to-br from-amber-500 to-amber-700 text-white font-bold text-lg shadow-lg shadow-amber-500/30">
                                                    3
                                                </span>
                                            @else
                                                <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-carbon-700 text-white font-bold">
                                                    {{ $standing->rank }}
                                                </span>
                                            @endif
                                        @else
                                            <span class="text-carbon-500 italic">NC</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-semibold text-white">
                                            {{ $standing->pilot->full_name ?? 'Pilote inconnu' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span class="text-sm text-white font-medium">{{ $standing->races_count }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span class="text-sm text-white font-medium">{{ $standing->base_points }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        @if($standing->bonus_points > 0)
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-status-success/20 text-status-success border border-status-success/30">
                                                +{{ $standing->bonus_points }}
                                            </span>
                                        @else
                                            <span class="text-carbon-500">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span class="text-lg font-bold text-racing-red-500">{{ $standing->total_points }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12">
                                        <x-racing.empty-state
                                            icon="🏎️"
                                            title="Aucun classement disponible"
                                            description="Aucun classement disponible pour cette catégorie."
                                        />
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($this->categoryStandings->hasPages())
                    <div class="px-6 py-4 border-t border-carbon-700/50">
                        {{ $this->categoryStandings->links() }}
                    </div>
                @endif
            @endif
        </x-racing.card>

        {{-- Points Rules --}}
        <x-racing.card class="mt-6">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                    <span>📊</span>
                    Barème des points
                </h3>
                <div class="grid grid-cols-4 md:grid-cols-7 gap-3">
                    @foreach($this->pointsRules as $rule)
                        <div class="text-center p-3 bg-carbon-700/50 rounded-xl border border-carbon-600/30 hover:border-racing-red-500/30 transition-colors">
                            <span class="block text-xs text-carbon-400 mb-1">
                                @if($rule->position_from === $rule->position_to)
                                    {{ $rule->position_from }}{{ $rule->position_from === 1 ? 'er' : 'ème' }}
                                @else
                                    {{ $rule->position_from }}+
                                @endif
                            </span>
                            <span class="block text-xl font-bold text-white">{{ $rule->points }}</span>
                        </div>
                    @endforeach
                </div>
                <div class="mt-6 p-4 bg-carbon-700/30 rounded-xl border border-carbon-600/30">
                    <p class="text-sm text-carbon-300">
                        <span class="text-checkered-yellow-500 font-semibold">📋 Règles :</span>
                        Minimum <span class="text-white font-semibold">{{ $this->seasonStats['min_races_required'] }}</span> courses pour être classé.
                        Bonus de <span class="text-status-success font-semibold">+{{ $this->seasonStats['bonus_points'] }}</span> points si participation à toutes les courses.
                    </p>
                </div>
            </div>
        </x-racing.card>
    </div>

    {{-- Rebuild Confirmation Modal --}}
    @if($showRebuildConfirmation)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen p-4">
                {{-- Backdrop --}}
                <div class="fixed inset-0 bg-carbon-900/80 backdrop-blur-sm transition-opacity" wire:click="cancelRebuild"></div>

                {{-- Modal --}}
                <div class="relative bg-carbon-800 rounded-2xl border border-carbon-700/50 shadow-2xl transform transition-all w-full max-w-lg">
                    {{-- Header --}}
                    <div class="p-6 border-b border-carbon-700/50">
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0 w-12 h-12 bg-checkered-yellow-500/20 rounded-xl flex items-center justify-center">
                                <svg class="h-6 w-6 text-checkered-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-white" id="modal-title">
                                    Recalculer le championnat
                                </h3>
                                <p class="mt-2 text-sm text-carbon-400">
                                    Cette action va recalculer tous les classements (général et par catégorie)
                                    basés sur les résultats des courses publiées. Les classements actuels seront remplacés.
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="p-6 flex flex-col-reverse sm:flex-row sm:justify-end gap-3">
                        <x-racing.button
                            wire:click="cancelRebuild"
                            variant="secondary"
                        >
                            Annuler
                        </x-racing.button>
                        <x-racing.button
                            wire:click="rebuildStandings"
                            variant="primary"
                        >
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Recalculer
                        </x-racing.button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
