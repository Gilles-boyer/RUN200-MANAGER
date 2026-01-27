<div>
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                    Sélection d'Emplacement Paddock
                </h1>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Course: {{ $registration->race->name }} - {{ $registration->race->race_date->format('d/m/Y') }}
                </p>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Voiture: #{{ $registration->car->race_number }} - {{ $registration->car->make }} {{ $registration->car->model }}
                </p>
            </div>
            <a href="{{ route('pilot.registrations.index') }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400">
                ← Retour aux inscriptions
            </a>
        </div>

        @if($registration->paddockSpot)
            <div class="mt-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-medium text-green-800 dark:text-green-300">
                            Emplacement actuel: {{ $registration->paddockSpot->full_name }}
                        </h3>
                        <p class="text-sm text-green-700 dark:text-green-400 mt-1">
                            Vous avez déjà réservé cet emplacement
                        </p>
                    </div>
                    @can('releasePaddockSpot', $registration)
                        <button
                            wire:click="releaseSpot"
                            wire:confirm="Êtes-vous sûr de vouloir libérer cet emplacement ?"
                            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700"
                        >
                            Libérer l'emplacement
                        </button>
                    @endcan
                </div>
            </div>
        @endif
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-indigo-100 dark:bg-indigo-900/30 rounded-md p-3">
                    <svg class="h-6 w-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Emplacements</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $statistics['total'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-green-100 dark:bg-green-900/30 rounded-md p-3">
                    <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Disponibles</p>
                    <p class="text-2xl font-semibold text-green-600 dark:text-green-400">{{ $statistics['available'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-red-100 dark:bg-red-900/30 rounded-md p-3">
                    <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Occupés</p>
                    <p class="text-2xl font-semibold text-red-600 dark:text-red-400">{{ $statistics['occupied'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-purple-100 dark:bg-purple-900/30 rounded-md p-3">
                    <svg class="h-6 w-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Taux d'occupation</p>
                    <p class="text-2xl font-semibold text-purple-600 dark:text-purple-400">{{ $statistics['occupancy_rate'] }}%</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Zone Filter -->
    <div class="mb-6 bg-white dark:bg-gray-800 rounded-lg shadow p-4">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Filtrer par zone</label>
                <div class="flex flex-wrap gap-2">
                    <button
                        wire:click="filterByZone(null)"
                        class="px-4 py-2 rounded-md {{ !$selectedZone ? 'bg-indigo-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300' }}"
                    >
                        Toutes les zones
                    </button>
                    @foreach($zones as $zone)
                        <button
                            wire:click="filterByZone('{{ $zone }}')"
                            class="px-4 py-2 rounded-md {{ $selectedZone === $zone ? 'bg-indigo-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300' }}"
                        >
                            Zone {{ $zone }}
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Toggle vue grille / carte --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Mode d'affichage</label>
                <div class="inline-flex rounded-lg bg-gray-200 dark:bg-gray-700 p-1">
                    <button
                        wire:click="setViewMode('grid')"
                        class="flex items-center gap-2 px-4 py-2 rounded-md text-sm font-medium transition-colors
                            {{ $viewMode === 'grid' ? 'bg-white dark:bg-gray-800 text-indigo-600 shadow' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                        </svg>
                        Grille
                    </button>
                    <button
                        wire:click="setViewMode('map')"
                        class="flex items-center gap-2 px-4 py-2 rounded-md text-sm font-medium transition-colors
                            {{ $viewMode === 'map' ? 'bg-white dark:bg-gray-800 text-indigo-600 shadow' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                        </svg>
                        Carte
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Vue Carte --}}
    @if($viewMode === 'map')
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Carte du Paddock</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                📅 Réservation pour la course: <strong>{{ $registration->race->name }}</strong> ({{ $registration->race->race_date->format('d/m/Y') }})
            </p>

            <x-racing.paddock-map-view
                :spots="collect($spotsForMap)"
                :selectedSpotId="$selectedSpotId"
                :highlightSpotId="$registration->paddock_spot_id"
                wire:click="selectSpot"
            />
        </div>
    @else
    <!-- Paddock Map Grid -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Plan des Emplacements</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
            📅 Réservation pour la course: <strong>{{ $registration->race->name }}</strong> ({{ $registration->race->race_date->format('d/m/Y') }})
        </p>

        <div class="grid grid-cols-2 md:grid-cols-6 lg:grid-cols-10 gap-3">
            @foreach($spots as $spot)
                @php
                    $isAvailable = !$spot->is_occupied_for_race;
                    $currentRegistration = $spot->registration_for_race;
                @endphp
                <button
                    wire:click="selectSpot({{ $spot->id }})"
                    class="relative h-20 rounded-lg border-2 transition-all duration-200 flex flex-col items-center justify-center
                        {{ $isAvailable
                            ? 'border-green-500 bg-green-50 dark:bg-green-900/20 hover:bg-green-100 dark:hover:bg-green-900/30 hover:scale-105'
                            : 'border-red-500 bg-red-50 dark:bg-red-900/20 hover:bg-red-100 dark:hover:bg-red-900/30'
                        }}
                        {{ $selectedSpotId === $spot->id ? 'ring-4 ring-indigo-500 scale-105' : '' }}
                        {{ $registration->paddock_spot_id === $spot->id ? 'ring-4 ring-green-500' : '' }}"
                    title="{{ $spot->full_name }}{{ !$isAvailable && $currentRegistration ? ' - Réservé par ' . $currentRegistration->pilot->first_name . ' ' . $currentRegistration->pilot->last_name : '' }}"
                >
                    <!-- Spot Number -->
                    <span class="text-lg font-bold
                        {{ $isAvailable ? 'text-green-700 dark:text-green-300' : 'text-red-700 dark:text-red-300' }}">
                        {{ $spot->spot_number }}
                    </span>

                    <!-- Zone Badge -->
                    <span class="text-xs px-2 py-0.5 rounded-full mt-1
                        {{ $isAvailable ? 'bg-green-200 dark:bg-green-800 text-green-800 dark:text-green-200' : 'bg-red-200 dark:bg-red-800 text-red-800 dark:text-red-200' }}">
                        Zone {{ $spot->zone }}
                    </span>

                    <!-- Status Icon -->
                    <div class="absolute top-1 right-1">
                        @if($isAvailable)
                            <svg class="h-4 w-4 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        @else
                            <svg class="h-4 w-4 text-red-600 dark:text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                        @endif
                    </div>

                    <!-- Current Registration Badge -->
                    @if($registration->paddock_spot_id === $spot->id)
                        <div class="absolute -top-2 -right-2 bg-green-500 text-white text-xs rounded-full px-2 py-1">
                            Votre place
                        </div>
                    @endif
                </button>
            @endforeach
        </div>

        <!-- Legend -->
        <div class="mt-6 flex items-center justify-center gap-6 text-sm">
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 bg-green-500 rounded"></div>
                <span class="text-gray-700 dark:text-gray-300">Disponible pour cette course</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 bg-red-500 rounded"></div>
                <span class="text-gray-700 dark:text-gray-300">Déjà réservé pour cette course</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 bg-indigo-500 rounded ring-2 ring-indigo-300"></div>
                <span class="text-gray-700 dark:text-gray-300">Sélectionné</span>
            </div>
        </div>
    </div>
    @endif {{-- Fin vue grille --}}

    <!-- Confirm Selection Button -->
    @if($selectedSpotId && !$registration->paddockSpot)
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                        Confirmer la sélection
                    </h3>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Emplacement sélectionné: {{ \App\Models\PaddockSpot::find($selectedSpotId)->full_name }}
                    </p>
                </div>
                <button
                    wire:click="confirmSelection"
                    class="px-6 py-3 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 font-medium"
                >
                    Confirmer et Réserver
                </button>
            </div>
        </div>
    @endif

    <!-- Spot Details Modal -->
    @if($showSpotDetails && $spotDetails)
        @php
            $isOccupiedForRace = $spotDetails->isOccupiedForRace($registration->race_id);
            $registrationForRace = $spotDetails->registrationForRace($registration->race_id);
        @endphp
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" wire:click="closeSpotDetails">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800" wire:click.stop>
                <div class="mt-3">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                            {{ $spotDetails->full_name }}
                        </h3>
                        <button wire:click="closeSpotDetails" class="text-gray-400 hover:text-gray-600">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                        Pour la course: <strong>{{ $registration->race->name }}</strong>
                    </p>

                    @if($isOccupiedForRace && $registrationForRace)
                        <div class="space-y-3">
                            <div class="bg-red-50 dark:bg-red-900/20 rounded-lg p-4">
                                <p class="text-sm font-medium text-red-800 dark:text-red-300 mb-2">
                                    Emplacement réservé pour cette course
                                </p>
                                <div class="text-sm text-gray-700 dark:text-gray-300">
                                    <p><strong>Pilote:</strong>
                                        {{ $registrationForRace->pilot->first_name }}
                                        {{ $registrationForRace->pilot->last_name }}
                                    </p>
                                    <p><strong>Voiture:</strong>
                                        #{{ $registrationForRace->car->race_number }} -
                                        {{ $registrationForRace->car->make }}
                                        {{ $registrationForRace->car->model }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4">
                            <p class="text-sm font-medium text-green-800 dark:text-green-300">
                                Cet emplacement est disponible pour cette course
                            </p>
                        </div>
                    @endif

                    @if($spotDetails->notes)
                        <div class="mt-4">
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes:</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $spotDetails->notes }}</p>
                        </div>
                    @endif

                    <div class="mt-6">
                        <button
                            wire:click="closeSpotDetails"
                            class="w-full px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-md hover:bg-gray-300 dark:hover:bg-gray-600"
                        >
                            Fermer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
