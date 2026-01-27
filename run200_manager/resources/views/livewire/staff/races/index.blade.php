<div>
    {{-- Racing Header --}}
    <div class="relative mb-8 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-8 bg-racing-gradient-subtle overflow-hidden">
        <div class="absolute top-0 right-0 w-64 h-64 bg-racing-red-500/5 rounded-full -translate-y-1/2 translate-x-1/2"></div>
        <div class="absolute bottom-0 left-0 w-48 h-48 bg-checkered-yellow-500/5 rounded-full translate-y-1/2 -translate-x-1/2"></div>

        <div class="relative">
            <h1 class="text-3xl font-bold text-white flex items-center gap-3">
                <span>🏁</span> Gestion des Courses
            </h1>
            <p class="mt-2 text-gray-400">
                Liste des courses et export PDF des engagés
            </p>
        </div>
    </div>

    {{-- Filters --}}
    <x-racing.card class="mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-racing.form.select wire:model.live="statusFilter" label="Statut">
                <option value="">Tous les statuts</option>
                <option value="DRAFT">Brouillon</option>
                <option value="OPEN">Ouverte</option>
                <option value="CLOSED">Fermée</option>
            </x-racing.form.select>

            <x-racing.form.input
                wire:model.live.debounce.300ms="search"
                label="Recherche"
                placeholder="Nom, lieu..."
                icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>'
            />
        </div>
    </x-racing.card>

    {{-- Races Table --}}
    <x-racing.card noPadding>
        {{-- Version Desktop --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="bg-carbon-800/50 border-b border-carbon-700/50">
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Course</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Lieu</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Statut</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Inscriptions</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-carbon-700/50">
                    @forelse($races as $race)
                        @php
                            $acceptedCount = $race->registrations->where('status', 'ACCEPTED')->count();
                            $pendingCount = $race->registrations->where('status', 'PENDING_VALIDATION')->count();
                            $totalCount = $race->registrations->count();
                        @endphp
                        <tr class="hover:bg-carbon-700/30 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-semibold text-white">{{ $race->name }}</div>
                                <div class="text-sm text-checkered-yellow-500">{{ $race->season->name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm font-medium text-white">{{ $race->race_date->format('d/m/Y') }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400">
                                {{ $race->location }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold
                                    @if($race->status === 'OPEN') bg-status-success/20 text-status-success border border-status-success/30
                                    @elseif($race->status === 'DRAFT') bg-carbon-700 text-gray-400 border border-carbon-600
                                    @else bg-status-danger/20 text-status-danger border border-status-danger/30
                                    @endif">
                                    @if($race->status === 'OPEN') Ouverte
                                    @elseif($race->status === 'DRAFT') Brouillon
                                    @else Fermée
                                    @endif
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-status-success/20 text-status-success border border-status-success/30">
                                        {{ $acceptedCount }} acceptés
                                    </span>
                                    @if($pendingCount > 0)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-status-warning/20 text-status-warning border border-status-warning/30">
                                            {{ $pendingCount }} en attente
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <div class="flex items-center justify-end gap-3">
                                    <a href="{{ route('staff.registrations.index', ['raceId' => $race->id]) }}"
                                       wire:navigate
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium text-checkered-yellow-500 hover:text-checkered-yellow-400 hover:bg-checkered-yellow-500/10 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                                        Inscriptions
                                    </a>
                                    @if($acceptedCount > 0)
                                        <button wire:click="downloadEngagedList({{ $race->id }})"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium text-racing-red-500 hover:text-racing-red-400 hover:bg-racing-red-500/10 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                            PDF
                                        </button>
                                    @endif
                                    @can('race.manage')
                                        @if($race->canImportResults())
                                            <a href="{{ route('staff.races.results', $race) }}"
                                               wire:navigate
                                               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium text-status-success hover:text-status-success hover:bg-status-success/10 transition-colors"
                                               title="Importer les résultats CSV">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                                </svg>
                                                Résultats
                                            </a>
                                        @elseif($race->isPublished())
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium text-checkered-yellow-500 bg-checkered-yellow-500/10">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                                Publiés
                                            </span>
                                        @endif
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12">
                                <x-racing.empty-state
                                    icon="🏁"
                                    title="Aucune course"
                                    description="Aucune course trouvée avec les filtres actuels."
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Version Mobile (Cards) --}}
        <div class="md:hidden p-4 space-y-4">
            @forelse($races as $race)
                @php
                    $acceptedCount = $race->registrations->where('status', 'ACCEPTED')->count();
                    $pendingCount = $race->registrations->where('status', 'PENDING_VALIDATION')->count();
                    $totalCount = $race->registrations->count();
                @endphp
                <div class="bg-carbon-800/50 rounded-xl border border-carbon-700 overflow-hidden">
                    {{-- Header de la carte --}}
                    <div class="p-4 bg-carbon-800 border-b border-carbon-700 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-racing-red-500 to-racing-red-700 flex items-center justify-center text-white text-lg">
                                🏁
                            </div>
                            <div>
                                <div class="text-sm font-semibold text-white">{{ $race->name }}</div>
                                <div class="text-xs text-checkered-yellow-500">{{ $race->season->name }}</div>
                            </div>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold
                            @if($race->status === 'OPEN') bg-status-success/20 text-status-success border border-status-success/30
                            @elseif($race->status === 'DRAFT') bg-carbon-700 text-gray-400 border border-carbon-600
                            @else bg-status-danger/20 text-status-danger border border-status-danger/30
                            @endif">
                            @if($race->status === 'OPEN') Ouverte
                            @elseif($race->status === 'DRAFT') Brouillon
                            @else Fermée
                            @endif
                        </span>
                    </div>

                    {{-- Contenu --}}
                    <div class="p-4 space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-carbon-400 uppercase tracking-wider">Date</span>
                            <span class="text-sm font-medium text-white">{{ $race->race_date->format('d/m/Y') }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-carbon-400 uppercase tracking-wider">Lieu</span>
                            <span class="text-sm text-carbon-300">{{ $race->location }}</span>
                        </div>

                        {{-- Inscriptions --}}
                        <div class="pt-2 border-t border-carbon-700">
                            <span class="text-xs text-carbon-400 uppercase tracking-wider block mb-2">Inscriptions</span>
                            <div class="flex flex-wrap gap-2">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-status-success/20 text-status-success border border-status-success/30">
                                    {{ $acceptedCount }} acceptés
                                </span>
                                @if($pendingCount > 0)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-status-warning/20 text-status-warning border border-status-warning/30">
                                        {{ $pendingCount }} en attente
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Footer avec actions --}}
                    <div class="px-4 py-3 bg-carbon-800/50 border-t border-carbon-700">
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('staff.registrations.index', ['raceId' => $race->id]) }}"
                               wire:navigate
                               class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-lg text-sm font-medium text-checkered-yellow-500 hover:bg-checkered-yellow-500/10 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                                Inscriptions
                            </a>
                            @if($acceptedCount > 0)
                                <button wire:click="downloadEngagedList({{ $race->id }})"
                                        class="inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-lg text-sm font-medium text-racing-red-500 hover:bg-racing-red-500/10 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    PDF
                                </button>
                            @endif
                            @can('race.manage')
                                @if($race->canImportResults())
                                    <a href="{{ route('staff.races.results', $race) }}"
                                       wire:navigate
                                       class="inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-lg text-sm font-medium text-status-success hover:bg-status-success/10 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                        </svg>
                                        Résultats
                                    </a>
                                @elseif($race->isPublished())
                                    <span class="inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-lg text-sm font-medium text-checkered-yellow-500 bg-checkered-yellow-500/10">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        Publiés
                                    </span>
                                @endif
                            @endcan
                        </div>
                    </div>
                </div>
            @empty
                <x-racing.empty-state
                    icon="🏁"
                    title="Aucune course"
                    description="Aucune course trouvée avec les filtres actuels."
                />
            @endforelse
        </div>

        {{-- Pagination --}}
        @if($races->hasPages())
            <div class="px-6 py-4 border-t border-carbon-700/50">
                {{ $races->links() }}
            </div>
        @endif
    </x-racing.card>
</div>
