<div>
    {{-- Racing Header --}}
    <div class="relative mb-8 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-8 bg-racing-gradient-subtle overflow-hidden">
        <div class="absolute top-0 right-0 w-64 h-64 bg-racing-red-500/5 rounded-full -translate-y-1/2 translate-x-1/2"></div>
        <div class="absolute bottom-0 left-0 w-48 h-48 bg-checkered-yellow-500/5 rounded-full translate-y-1/2 -translate-x-1/2"></div>

        <div class="relative">
            <h1 class="text-3xl font-bold text-white flex items-center gap-3">
                <span>🚗</span> Gestion des Voitures
            </h1>
            <p class="mt-2 text-gray-400">
                Accès aux voitures et historique des contrôles techniques
            </p>
        </div>
    </div>

    {{-- Filters --}}
    <x-racing.card class="mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <x-racing.form.input
                wire:model.live.debounce.300ms="search"
                label="Rechercher"
                placeholder="Numéro, marque, pilote..."
                icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>'
            />

            <x-racing.form.select wire:model.live="categoryFilter" label="Catégorie">
                <option value="">Toutes</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </x-racing.form.select>

            <x-racing.form.select wire:model.live="pilotFilter" label="Pilote">
                <option value="">Tous</option>
                @foreach($pilots as $pilot)
                    <option value="{{ $pilot->id }}">{{ $pilot->last_name }} {{ $pilot->first_name }}</option>
                @endforeach
            </x-racing.form.select>

            <div class="flex items-end">
                <x-racing.button wire:click="resetFilters" variant="secondary" class="w-full">
                    Réinitialiser
                </x-racing.button>
            </div>
        </div>
    </x-racing.card>

    {{-- Table --}}
    <x-racing.card noPadding>
        @if($cars->isEmpty())
            <div class="p-8">
                <x-racing.empty-state
                    icon="🚗"
                    title="Aucune voiture"
                    description="Aucune voiture ne correspond à vos critères de recherche."
                />
            </div>
        @else
            {{-- Version Desktop --}}
            <div class="hidden md:block overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="bg-carbon-800/50 border-b border-carbon-700/50">
                            <th
                                wire:click="sortByColumn('race_number')"
                                class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider cursor-pointer hover:bg-carbon-700/50 transition-colors"
                            >
                                <div class="flex items-center gap-1">
                                    N°
                                    @if($sortBy === 'race_number')
                                        <svg class="w-4 h-4 text-racing-red-500" fill="currentColor" viewBox="0 0 20 20">
                                            @if($sortDirection === 'asc')
                                                <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd"/>
                                            @else
                                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                            @endif
                                        </svg>
                                    @endif
                                </div>
                            </th>
                            <th
                                wire:click="sortByColumn('make')"
                                class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider cursor-pointer hover:bg-carbon-700/50 transition-colors"
                            >
                                <div class="flex items-center gap-1">
                                    Voiture
                                    @if($sortBy === 'make')
                                        <svg class="w-4 h-4 text-racing-red-500" fill="currentColor" viewBox="0 0 20 20">
                                            @if($sortDirection === 'asc')
                                                <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd"/>
                                            @else
                                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                            @endif
                                        </svg>
                                    @endif
                                </div>
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Catégorie</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Pilote</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Dernier Contrôle</th>
                            <th class="px-6 py-4 text-right text-xs font-semibold text-gray-400 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-carbon-700/50">
                        @foreach($cars as $car)
                            <tr class="hover:bg-carbon-700/30 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-2xl font-black text-racing-red-500">
                                        #{{ $car->race_number }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-semibold text-white">{{ $car->make }}</div>
                                    <div class="text-sm text-gray-400">{{ $car->model }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($car->category)
                                        <span class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-semibold bg-carbon-700 text-gray-300 border border-carbon-600">
                                            {{ $car->category->name }}
                                        </span>
                                    @else
                                        <span class="text-gray-500">—</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-white">
                                    {{ $car->pilot->last_name }} {{ $car->pilot->first_name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($car->latestTechInspection)
                                        <div class="flex items-center gap-2">
                                            @if($car->latestTechInspection->status === 'OK')
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold bg-status-success/20 text-status-success border border-status-success/30">
                                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                                    OK
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold bg-status-danger/20 text-status-danger border border-status-danger/30">
                                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                                                    FAIL
                                                </span>
                                            @endif
                                            <span class="text-sm text-gray-400">
                                                {{ $car->latestTechInspection->inspected_at->format('d/m/Y') }}
                                            </span>
                                        </div>
                                    @else
                                        <span class="text-sm text-gray-500 italic">Aucun contrôle</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button wire:click="openCategoryModal({{ $car->id }})"
                                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium text-gray-400 hover:text-white hover:bg-carbon-700/50 transition-colors"
                                           title="Modifier la catégorie">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            Catégorie
                                        </button>
                                        <a href="{{ route('staff.cars.tech-history', $car) }}"
                                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium text-checkered-yellow-500 hover:text-checkered-yellow-400 hover:bg-checkered-yellow-500/10 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            Historique
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Version Mobile (Cards) --}}
            <div class="md:hidden p-4 space-y-4">
                @foreach($cars as $car)
                    <div class="bg-carbon-800/50 rounded-xl border border-carbon-700 overflow-hidden">
                        {{-- Header de la carte --}}
                        <div class="p-4 bg-carbon-800 border-b border-carbon-700 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <span class="text-2xl font-black text-racing-red-500">
                                    #{{ $car->race_number }}
                                </span>
                                <div>
                                    <div class="text-sm font-semibold text-white">{{ $car->make }}</div>
                                    <div class="text-xs text-gray-400">{{ $car->model }}</div>
                                </div>
                            </div>
                            @if($car->category)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-carbon-700 text-gray-300 border border-carbon-600">
                                    {{ $car->category->name }}
                                </span>
                            @endif
                        </div>

                        {{-- Contenu --}}
                        <div class="p-4 space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-xs text-carbon-400 uppercase tracking-wider">Pilote</span>
                                <span class="text-sm text-white">{{ $car->pilot->last_name }} {{ $car->pilot->first_name }}</span>
                            </div>

                            {{-- Dernier contrôle --}}
                            <div class="pt-2 border-t border-carbon-700">
                                <span class="text-xs text-carbon-400 uppercase tracking-wider block mb-2">Dernier Contrôle</span>
                                @if($car->latestTechInspection)
                                    <div class="flex items-center gap-2">
                                        @if($car->latestTechInspection->status === 'OK')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold bg-status-success/20 text-status-success border border-status-success/30">
                                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                                OK
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold bg-status-danger/20 text-status-danger border border-status-danger/30">
                                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                                                FAIL
                                            </span>
                                        @endif
                                        <span class="text-sm text-gray-400">
                                            {{ $car->latestTechInspection->inspected_at->format('d/m/Y') }}
                                        </span>
                                    </div>
                                @else
                                    <span class="text-sm text-gray-500 italic">Aucun contrôle</span>
                                @endif
                            </div>
                        </div>

                        {{-- Footer avec action --}}
                        <div class="px-4 py-3 bg-carbon-800/50 border-t border-carbon-700 flex gap-2">
                            <button wire:click="openCategoryModal({{ $car->id }})"
                               class="flex items-center justify-center gap-2 flex-1 py-2 rounded-lg text-sm font-medium text-gray-400 hover:text-white hover:bg-carbon-700/50 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                Catégorie
                            </button>
                            <a href="{{ route('staff.cars.tech-history', $car) }}"
                               class="flex items-center justify-center gap-2 flex-1 py-2 rounded-lg text-sm font-medium text-checkered-yellow-500 hover:bg-checkered-yellow-500/10 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Historique
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Pagination --}}
            @if($cars->hasPages())
                <div class="px-6 py-4 border-t border-carbon-700/50">
                    {{ $cars->links() }}
                </div>
            @endif
        @endif
    </x-racing.card>

    <!-- Category Edit Modal -->
    @if($showCategoryModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                <div class="fixed inset-0 bg-carbon-950/80 transition-opacity" wire:click="closeCategoryModal"></div>

                <div class="relative inline-block w-full max-w-md p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-carbon-800 border border-carbon-700 shadow-xl rounded-2xl">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-semibold text-white" id="modal-title">
                            Modifier la catégorie
                        </h3>
                        <button wire:click="closeCategoryModal" class="text-gray-400 hover:text-white transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <div class="space-y-4">
                        <x-racing.form.select wire:model="newCategoryId" label="Catégorie">
                            <option value="">Sélectionner une catégorie</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </x-racing.form.select>
                    </div>

                    <div class="mt-6 flex justify-end gap-3">
                        <x-racing.button variant="secondary" wire:click="closeCategoryModal">
                            Annuler
                        </x-racing.button>
                        <x-racing.button wire:click="updateCategory">
                            Enregistrer
                        </x-racing.button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
