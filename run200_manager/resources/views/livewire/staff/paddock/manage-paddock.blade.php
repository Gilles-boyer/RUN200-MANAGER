<div>
    {{-- Racing Header --}}
    <div class="relative mb-8 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-8 bg-racing-gradient-subtle overflow-hidden">
        <div class="absolute top-0 right-0 w-64 h-64 bg-racing-red-500/5 rounded-full -translate-y-1/2 translate-x-1/2"></div>
        <div class="absolute bottom-0 left-0 w-48 h-48 bg-checkered-yellow-500/5 rounded-full translate-y-1/2 -translate-x-1/2"></div>

        <div class="relative flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-white flex items-center gap-3">
                    <span>🏕️</span> Gestion du Paddock
                </h1>
                <p class="mt-2 text-gray-400">
                    Assignation et gestion des emplacements de paddock pour les courses
                </p>
            </div>

            {{-- Toggle vue grille / carte --}}
            @if($selectedRaceId)
            <div class="flex items-center gap-2">
                <span class="text-sm text-carbon-400 mr-2">Affichage :</span>
                <div class="inline-flex rounded-xl bg-carbon-800 p-1 border border-carbon-700">
                    <button
                        wire:click="setViewMode('grid')"
                        class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition-all
                            {{ $viewMode === 'grid' ? 'bg-racing-red-600 text-white shadow-lg shadow-racing-red-500/30' : 'text-gray-400 hover:text-white hover:bg-carbon-700' }}"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                        </svg>
                        Grille
                    </button>
                    <button
                        wire:click="setViewMode('map')"
                        class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition-all
                            {{ $viewMode === 'map' ? 'bg-racing-red-600 text-white shadow-lg shadow-racing-red-500/30' : 'text-gray-400 hover:text-white hover:bg-carbon-700' }}"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                        </svg>
                        Carte
                    </button>
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- Flash Messages --}}
    @if (session()->has('success'))
        <x-racing.alert type="success" :dismissible="true" class="mb-6">
            {{ session('success') }}
        </x-racing.alert>
    @endif

    @if($errors->any())
        <x-racing.alert type="danger" :dismissible="true" class="mb-6">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </x-racing.alert>
    @endif

    {{-- Statistics Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        @if($selectedRaceId)
            <x-racing.stat-card
                label="En service"
                :value="$statistics['total']"
                icon="🗺️"
            />
            <x-racing.stat-card
                label="Disponibles"
                :value="$statistics['available']"
                icon="✅"
                class="border-status-success/30"
            />
            <x-racing.stat-card
                label="Occupés"
                :value="$statistics['occupied']"
                icon="🚗"
                class="border-status-danger/30"
            />
            <x-racing.stat-card
                label="Taux d'occupation"
                :value="$statistics['occupancy_rate'] . '%'"
                icon="📊"
            />
        @else
            <x-racing.stat-card
                label="Total"
                :value="$statistics['total']"
                icon="🗺️"
            />
            <x-racing.stat-card
                label="En service"
                :value="$statistics['in_service']"
                icon="✅"
            />
            <x-racing.stat-card
                label="Hors service"
                :value="$statistics['out_of_service']"
                icon="🔧"
            />
            <x-racing.stat-card
                label="—"
                value="—"
                icon="📊"
            />
        @endif
    </div>

    {{-- Info --}}
    @if(!$selectedRaceId)
        <x-racing.alert type="info" class="mb-6">
            <strong>Note :</strong> Sélectionnez une course pour voir les disponibilités et assigner des emplacements. Les réservations sont spécifiques à chaque course.
        </x-racing.alert>
    @endif

    {{-- Filters --}}
    <x-racing.card class="mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            {{-- Race Selection --}}
            <x-racing.form.select wire:model.live="selectedRaceId" label="Course">
                <option value="">Toutes les courses</option>
                @foreach($races as $race)
                    <option value="{{ $race->id }}">{{ $race->name }} - {{ $race->race_date->format('d/m/Y') }}</option>
                @endforeach
            </x-racing.form.select>

            {{-- Zone Filter --}}
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Zone</label>
                <div class="flex flex-wrap gap-2">
                    <button
                        wire:click="filterByZone(null)"
                        class="px-3 py-2 text-sm rounded-lg font-medium transition-all
                            {{ !$selectedZone
                                ? 'bg-racing-red-600 text-white shadow-lg shadow-racing-red-500/30'
                                : 'bg-carbon-700 text-gray-300 border border-carbon-600 hover:bg-carbon-600' }}"
                    >
                        Toutes
                    </button>
                    @foreach($zones as $zone)
                        <button
                            wire:click="filterByZone('{{ $zone }}')"
                            class="px-3 py-2 text-sm rounded-lg font-medium transition-all
                                {{ $selectedZone === $zone
                                    ? 'bg-racing-red-600 text-white shadow-lg shadow-racing-red-500/30'
                                    : 'bg-carbon-700 text-gray-300 border border-carbon-600 hover:bg-carbon-600' }}"
                        >
                            {{ $zone }}
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Search Pilot (pour la grille) --}}
            @if($viewMode === 'grid' && $selectedRaceId)
                <x-racing.form.input
                    wire:model.live.debounce.300ms="searchPilot"
                    label="Rechercher"
                    placeholder="Nom du pilote..."
                    icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>'
                />
            @endif

            {{-- Show Only Available --}}
            <div class="flex items-end">
                <label class="flex items-center cursor-pointer group">
                    <input
                        type="checkbox"
                        wire:model.live="showOnlyAvailable"
                        class="rounded border-carbon-600 bg-carbon-700 text-racing-red-600 focus:ring-racing-red-500 focus:ring-offset-carbon-800"
                    >
                    <span class="ml-2 text-sm text-gray-300 group-hover:text-white transition-colors">Seulement les disponibles</span>
                </label>
            </div>
        </div>
    </x-racing.card>

    {{-- Main Content Area --}}
    <div class="grid grid-cols-1 {{ $selectedRaceId && $registrationsWithoutSpot->count() > 0 ? 'xl:grid-cols-4' : '' }} gap-6">
        {{-- Paddock Grid/Map --}}
        <div class="{{ $selectedRaceId && $registrationsWithoutSpot->count() > 0 ? 'xl:col-span-3' : '' }}">
            @if($viewMode === 'map' && $selectedRaceId)
                {{-- Vue Carte --}}
                <x-racing.card class="mb-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-bold text-white flex items-center gap-2">
                            <span>🗺️</span> Carte du Paddock
                        </h2>
                        <span class="text-sm text-carbon-400">
                            Cliquez sur un emplacement pour l'assigner
                        </span>
                    </div>

                    <x-racing.paddock-map-view
                        :spots="collect($spotsForMap)"
                        :selectedSpotId="$selectedSpotId"
                        wire:click="selectSpot"
                    />
                </x-racing.card>
            @else
                {{-- Vue Grille --}}
                <x-racing.card class="mb-6">
                    <h2 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
                        <span>🗺️</span> Plan des Emplacements
                        @if($selectedRaceId)
                            <span class="text-sm font-normal text-carbon-400 ml-2">— Cliquez pour assigner</span>
                        @endif
                    </h2>

                    <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-8 xl:grid-cols-10 gap-3">
                        @foreach($spots as $spot)
                            @php
                                $isOccupied = $selectedRaceId ? $spot->is_occupied_for_race : false;
                                $registration = $selectedRaceId ? $spot->registration_for_race : null;
                            @endphp
                            <div class="relative group">
                                <button
                                    @if($selectedRaceId)
                                        wire:click="openAssignModal({{ $spot->id }})"
                                    @else
                                        wire:click="showSpotDetails({{ $spot->id }})"
                                    @endif
                                    class="w-full h-24 rounded-xl border-2 transition-all duration-200 flex flex-col items-center justify-center p-2
                                        {{ !$selectedRaceId
                                            ? 'border-carbon-600 bg-carbon-700/50 hover:bg-carbon-700 hover:border-carbon-500 cursor-pointer'
                                            : ($isOccupied
                                                ? 'border-status-danger/50 bg-status-danger/10 hover:bg-status-danger/20 hover:border-status-danger cursor-pointer'
                                                : 'border-status-success/50 bg-status-success/10 hover:bg-status-success/20 hover:border-status-success cursor-pointer'
                                            )
                                        }}"
                                >
                                    {{-- Spot Number --}}
                                    <span class="text-lg font-black {{ !$selectedRaceId ? 'text-carbon-300' : ($isOccupied ? 'text-status-danger' : 'text-status-success') }}">
                                        {{ $spot->spot_number }}
                                    </span>

                                    {{-- Zone Badge --}}
                                    <span class="text-xs px-2 py-0.5 rounded-lg mt-1 font-medium
                                        {{ !$selectedRaceId
                                            ? 'bg-carbon-700 text-carbon-400 border border-carbon-600'
                                            : ($isOccupied
                                                ? 'bg-status-danger/20 text-status-danger border border-status-danger/30'
                                                : 'bg-status-success/20 text-status-success border border-status-success/30')
                                        }}">
                                        Zone {{ $spot->zone }}
                                    </span>

                                    {{-- Pilot Info if Occupied for this race --}}
                                    @if($registration)
                                        <div class="mt-1 text-xs text-center text-white font-bold truncate w-full px-1">
                                            <span class="text-racing-red-500">#{{ $registration->car->race_number ?? 'N/A' }}</span>
                                            <span class="text-carbon-400">{{ Str::limit($registration->pilot->last_name, 8) }}</span>
                                        </div>
                                    @endif
                                </button>

                                {{-- Release Button if Occupied for this race --}}
                                @if($registration)
                                    <button
                                        wire:click="releaseSpot({{ $registration->id }})"
                                        wire:confirm="Libérer l'emplacement {{ $spot->spot_number }} ?"
                                        class="absolute -top-2 -right-2 bg-status-danger text-white rounded-lg p-1.5 hover:bg-status-danger/80 shadow-lg transition-all opacity-0 group-hover:opacity-100"
                                        title="Libérer cet emplacement"
                                    >
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    {{-- Légende --}}
                    @if($selectedRaceId)
                        <div class="mt-6 pt-4 border-t border-carbon-700 flex flex-wrap items-center justify-center gap-6 text-sm">
                            <div class="flex items-center gap-2">
                                <div class="w-4 h-4 rounded bg-status-success/30 border border-status-success"></div>
                                <span class="text-carbon-400">Disponible</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-4 h-4 rounded bg-status-danger/30 border border-status-danger"></div>
                                <span class="text-carbon-400">Occupé</span>
                            </div>
                        </div>
                    @endif
                </x-racing.card>
            @endif
        </div>

        {{-- Sidebar: Pilotes sans emplacement --}}
        @if($selectedRaceId && $registrationsWithoutSpot->count() > 0)
            <div class="xl:col-span-1">
                <x-racing.card>
                    <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                        <span>🚗</span> Sans emplacement
                        <span class="ml-auto px-2 py-1 text-xs rounded-lg bg-status-warning/20 text-status-warning border border-status-warning/30">
                            {{ $registrationsWithoutSpot->count() }}
                        </span>
                    </h3>

                    <div class="space-y-2 max-h-[500px] overflow-y-auto">
                        @foreach($registrationsWithoutSpot as $reg)
                            <div class="p-3 rounded-xl bg-carbon-700/50 border border-carbon-600 hover:border-carbon-500 transition-colors">
                                <div class="flex items-center gap-3">
                                    <span class="flex-shrink-0 w-10 h-10 rounded-lg bg-racing-red-500/20 text-racing-red-500 flex items-center justify-center font-bold text-sm">
                                        #{{ $reg->car->race_number }}
                                    </span>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-medium text-white truncate">
                                            {{ $reg->pilot->first_name }} {{ $reg->pilot->last_name }}
                                        </p>
                                        <p class="text-xs text-carbon-400 truncate">
                                            {{ $reg->car->make }} {{ $reg->car->model }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <p class="mt-4 text-xs text-carbon-500 text-center">
                        Cliquez sur un emplacement disponible pour assigner
                    </p>
                </x-racing.card>
            </div>
        @endif
    </div>

    {{-- Liste des inscriptions avec emplacement --}}
    @if($selectedRaceId)
        @php
            $registrationsWithSpot = $registrationsForAssignment->filter(fn($r) => $r->paddock_spot_id);
        @endphp
        @if($registrationsWithSpot->count() > 0)
            <x-racing.card class="mt-6">
                <h2 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                    <span>📋</span> Emplacements assignés
                    <span class="ml-2 px-2 py-1 text-xs rounded-lg bg-status-success/20 text-status-success border border-status-success/30">
                        {{ $registrationsWithSpot->count() }}
                    </span>
                </h2>

                <div class="overflow-x-auto">
                    <table class="table-racing w-full">
                        <thead>
                            <tr class="border-b border-carbon-700">
                                <th class="px-4 py-3 text-left text-xs font-semibold text-carbon-400 uppercase tracking-wider">Emplacement</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-carbon-400 uppercase tracking-wider">Pilote</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-carbon-400 uppercase tracking-wider">Voiture</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-carbon-400 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-carbon-700/50">
                            @foreach($registrationsWithSpot->sortBy('paddockSpot.spot_number') as $reg)
                                <tr class="hover:bg-carbon-800/50 transition-colors">
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center px-3 py-1.5 rounded-lg bg-status-success/20 text-status-success font-bold text-sm border border-status-success/30">
                                            {{ $reg->paddockSpot->spot_number }}
                                        </span>
                                        <span class="ml-2 text-xs text-carbon-400">Zone {{ $reg->paddockSpot->zone }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <p class="text-sm font-medium text-white">
                                            {{ $reg->pilot->first_name }} {{ $reg->pilot->last_name }}
                                        </p>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            <span class="text-racing-red-500 font-bold">#{{ $reg->car->race_number }}</span>
                                            <span class="text-sm text-carbon-400">{{ $reg->car->make }} {{ $reg->car->model }}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <button
                                            wire:click="releaseSpot({{ $reg->id }})"
                                            wire:confirm="Libérer l'emplacement {{ $reg->paddockSpot->spot_number }} ?"
                                            class="p-2 rounded-lg text-status-danger hover:bg-status-danger/20 transition-colors"
                                            title="Libérer"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-racing.card>
        @endif
    @endif

    {{-- Assignment Modal --}}
    @if($showAssignModal && $spotToAssignId)
        @php $spotToAssign = \App\Models\PaddockSpot::find($spotToAssignId); @endphp
        <div class="fixed inset-0 bg-carbon-900/80 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-start justify-center pt-10 sm:pt-20 px-4" wire:click="closeAssignModal">
            <div class="relative w-full max-w-2xl bg-carbon-800 rounded-2xl shadow-2xl border border-carbon-700/50" wire:click.stop>
                <div class="p-6">
                    {{-- Modal Header --}}
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h3 class="text-xl font-bold text-white flex items-center gap-2">
                                <span>📍</span> Emplacement {{ $spotToAssign->spot_number }}
                            </h3>
                            <p class="text-sm text-carbon-400 mt-1">Zone {{ $spotToAssign->zone }}</p>
                        </div>
                        <button wire:click="closeAssignModal" class="p-2 text-gray-400 hover:text-white hover:bg-carbon-700 rounded-lg transition-colors">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    @if($selectedRaceId)
                        @php
                            $currentRegistration = $spotToAssign->registrationForRace($selectedRaceId);
                        @endphp

                        {{-- Emplacement déjà occupé --}}
                        @if($currentRegistration)
                            <div class="mb-6 p-4 rounded-xl bg-status-warning/10 border border-status-warning/30">
                                <p class="text-sm text-status-warning font-medium mb-2">⚠️ Cet emplacement est actuellement occupé</p>
                                <div class="flex items-center gap-3">
                                    <span class="flex-shrink-0 w-12 h-12 rounded-lg bg-racing-red-500/20 text-racing-red-500 flex items-center justify-center font-bold">
                                        #{{ $currentRegistration->car->race_number }}
                                    </span>
                                    <div>
                                        <p class="text-white font-medium">{{ $currentRegistration->pilot->first_name }} {{ $currentRegistration->pilot->last_name }}</p>
                                        <p class="text-sm text-carbon-400">{{ $currentRegistration->car->make }} {{ $currentRegistration->car->model }}</p>
                                    </div>
                                    <button
                                        wire:click="releaseSpot({{ $currentRegistration->id }})"
                                        wire:confirm="Libérer cet emplacement ?"
                                        class="ml-auto px-4 py-2 rounded-lg bg-status-danger/20 text-status-danger hover:bg-status-danger/30 transition-colors text-sm font-medium"
                                    >
                                        Libérer
                                    </button>
                                </div>
                            </div>
                        @endif

                        {{-- Search Pilot --}}
                        <div class="mb-4">
                            <x-racing.form.input
                                wire:model.live.debounce.300ms="searchPilot"
                                label="Rechercher un pilote à assigner"
                                placeholder="Nom du pilote..."
                                icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>'
                            />
                        </div>

                        {{-- Registrations List --}}
                        <div class="max-h-80 overflow-y-auto space-y-2 rounded-xl border border-carbon-700 p-2 bg-carbon-900/50">
                            @forelse($registrationsForAssignment as $registration)
                                <div
                                    wire:click="$set('registrationToAssignId', {{ $registration->id }})"
                                    class="p-3 rounded-xl border-2 cursor-pointer transition-all
                                        {{ $registrationToAssignId === $registration->id
                                            ? 'border-racing-red-500 bg-racing-red-500/10'
                                            : 'border-carbon-700 hover:border-carbon-600 hover:bg-carbon-700/50'
                                        }}"
                                >
                                    <div class="flex items-center gap-3">
                                        <span class="flex-shrink-0 w-10 h-10 rounded-lg bg-racing-red-500/20 text-racing-red-500 flex items-center justify-center font-bold text-sm">
                                            #{{ $registration->car->race_number }}
                                        </span>
                                        <div class="min-w-0 flex-1">
                                            <p class="font-medium text-white">
                                                {{ $registration->pilot->first_name }} {{ $registration->pilot->last_name }}
                                            </p>
                                            <p class="text-sm text-carbon-400">
                                                {{ $registration->car->make }} {{ $registration->car->model }}
                                            </p>
                                        </div>
                                        @if($registration->paddockSpot)
                                            <span class="flex-shrink-0 px-2 py-1 text-xs rounded-lg font-medium bg-status-warning/20 text-status-warning border border-status-warning/30">
                                                {{ $registration->paddockSpot->spot_number }}
                                            </span>
                                        @else
                                            <span class="flex-shrink-0 px-2 py-1 text-xs rounded-lg font-medium bg-carbon-700 text-carbon-400 border border-carbon-600">
                                                —
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="py-8">
                                    <x-racing.empty-state
                                        icon="📋"
                                        title="Aucune inscription"
                                        description="Aucune inscription trouvée pour cette course."
                                    />
                                </div>
                            @endforelse
                        </div>

                        {{-- Modal Actions --}}
                        <div class="mt-6 flex justify-end gap-3 pt-4 border-t border-carbon-700/50">
                            <x-racing.button wire:click="closeAssignModal" variant="secondary">
                                Annuler
                            </x-racing.button>
                            <x-racing.button
                                wire:click="assignSpotToRegistration"
                                :disabled="!$registrationToAssignId"
                            >
                                Assigner l'emplacement
                            </x-racing.button>
                        </div>
                    @else
                        <div class="py-8">
                            <x-racing.empty-state
                                icon="🏁"
                                title="Sélectionnez une course"
                                description="Veuillez sélectionner une course pour assigner un emplacement."
                            />
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- Spot Details Modal (when no race selected) --}}
    @if($showSpotDetailsModal && $spotDetails)
        <div class="fixed inset-0 bg-carbon-900/80 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center px-4" wire:click="closeSpotDetailsModal">
            <div class="relative w-full max-w-md bg-carbon-800 rounded-2xl shadow-2xl border border-carbon-700/50" wire:click.stop>
                <div class="p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-bold text-white">{{ $spotDetails->full_name }}</h3>
                        <button wire:click="closeSpotDetailsModal" class="p-2 text-gray-400 hover:text-white hover:bg-carbon-700 rounded-lg transition-colors">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <div class="space-y-4">
                        <div class="flex justify-between items-center p-3 rounded-lg bg-carbon-700/50">
                            <span class="text-carbon-400">Numéro</span>
                            <span class="text-white font-bold text-lg">{{ $spotDetails->spot_number }}</span>
                        </div>
                        <div class="flex justify-between items-center p-3 rounded-lg bg-carbon-700/50">
                            <span class="text-carbon-400">Zone</span>
                            <span class="text-white">{{ $spotDetails->zone }}</span>
                        </div>
                        <div class="flex justify-between items-center p-3 rounded-lg bg-carbon-700/50">
                            <span class="text-carbon-400">Statut</span>
                            @if($spotDetails->is_available)
                                <span class="px-2 py-1 rounded-lg bg-status-success/20 text-status-success text-sm font-medium">En service</span>
                            @else
                                <span class="px-2 py-1 rounded-lg bg-status-danger/20 text-status-danger text-sm font-medium">Hors service</span>
                            @endif
                        </div>
                        @if($spotDetails->notes)
                            <div class="p-3 rounded-lg bg-carbon-700/50">
                                <span class="text-carbon-400 text-sm block mb-1">Notes</span>
                                <p class="text-white">{{ $spotDetails->notes }}</p>
                            </div>
                        @endif
                    </div>

                    <div class="mt-6">
                        <p class="text-sm text-carbon-400 text-center">
                            Sélectionnez une course pour voir les réservations
                        </p>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
