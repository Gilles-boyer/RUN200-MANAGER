<div class="space-y-6" x-data="paddockMapAdmin()">
    {{-- Header --}}
    <div class="relative overflow-hidden rounded-xl bg-racing-gradient-subtle p-6 border border-carbon-700">
        <div class="absolute top-0 right-0 w-64 h-64 bg-checkered-yellow-500/5 rounded-full -translate-y-1/2 translate-x-1/2"></div>

        <div class="relative flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-checkered-yellow-500/20 flex items-center justify-center">
                    <svg class="w-6 h-6 text-checkered-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-white">Carte du Paddock</h1>
                    <p class="text-carbon-400 text-sm">Positionnez les emplacements sur le plan du paddock</p>
                </div>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.paddock-spots.index') }}" wire:navigate>
                    <x-racing.button variant="ghost">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Retour à la liste
                    </x-racing.button>
                </a>
                <x-racing.button
                    wire:click="toggleEditMode"
                    :variant="$editMode ? 'primary' : 'secondary'"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        @if($editMode)
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        @else
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                        @endif
                    </svg>
                    {{ $editMode ? 'Terminer l\'édition' : 'Mode édition' }}
                </x-racing.button>
            </div>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
        <x-racing.alert type="success" :dismissible="true">
            {{ session('success') }}
        </x-racing.alert>
    @endif

    {{-- Mode édition : Actions rapides --}}
    @if($editMode)
        <x-racing.card class="!p-4">
            <div class="flex flex-wrap items-center gap-4">
                <span class="text-sm text-carbon-400 font-medium">Actions rapides :</span>
                <x-racing.button wire:click="autoPositionByZone" variant="secondary" size="sm">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
                    </svg>
                    Auto-positionner par zone
                </x-racing.button>
                <x-racing.button
                    wire:click="resetAllPositions"
                    wire:confirm="Réinitialiser toutes les positions ? Cette action est irréversible."
                    variant="ghost"
                    size="sm"
                    class="text-status-danger hover:bg-status-danger/10"
                >
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Réinitialiser tout
                </x-racing.button>

                <div class="ml-auto flex items-center gap-2 text-sm text-carbon-400">
                    <span class="w-3 h-3 rounded-full bg-status-success"></span> En service
                    <span class="w-3 h-3 rounded-full bg-status-warning ml-2"></span> Hors service
                    <span class="w-3 h-3 rounded-full bg-carbon-600 ml-2"></span> Non positionné
                </div>
            </div>
        </x-racing.card>
    @endif

    <div class="grid grid-cols-1 xl:grid-cols-4 gap-6">
        {{-- Carte principale --}}
        <div class="xl:col-span-3">
            <x-racing.card class="!p-0 overflow-hidden">
                <div class="p-4 border-b border-carbon-700 flex items-center justify-between">
                    <h2 class="font-semibold text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-checkered-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                        </svg>
                        Plan du Paddock
                    </h2>
                    @if($editMode)
                        <div class="flex flex-col items-end gap-1">
                            <span class="text-xs text-checkered-yellow-500 bg-checkered-yellow-500/10 px-2 py-1 rounded-lg">
                                🖱️ Glissez-déposez ou cliquez pour placer
                            </span>
                            <span class="text-xs text-carbon-400">
                                Sélectionnez un emplacement puis cliquez sur la carte
                            </span>
                        </div>
                    @endif
                </div>

                {{-- Zone de la carte - Plus grande pour permettre le scroll sur une carte circulaire --}}
                <div
                    class="relative bg-carbon-900 overflow-auto"
                    style="height: 700px;"
                    x-ref="mapContainer"
                >
                    <div
                        class="relative bg-contain bg-center bg-no-repeat"
                        style="width: {{ $mapWidth }}px; height: {{ $mapHeight }}px; background-image: url('{{ $mapImage }}'); background-color: #1a1a1a;"
                        x-ref="mapArea"
                        @if($editMode)
                            @dragover.prevent
                            @drop="handleDrop($event)"
                            @click="handleClick($event)"
                        @endif
                    >
                        {{-- Grille en mode édition --}}
                        @if($editMode)
                            <div class="absolute inset-0 pointer-events-none opacity-20"
                                 style="background-image: linear-gradient(to right, #374151 1px, transparent 1px), linear-gradient(to bottom, #374151 1px, transparent 1px); background-size: 50px 50px;">
                            </div>
                        @endif

                        {{-- Marqueurs des emplacements --}}
                        @foreach($spots as $spot)
                            @if($spot['has_position'])
                                <div
                                    class="absolute transform -translate-x-1/2 -translate-y-1/2 transition-all duration-150
                                        {{ $editMode ? 'cursor-move hover:scale-110' : 'cursor-pointer hover:scale-105' }}
                                        {{ $selectedSpotId === $spot['id'] ? 'z-20 scale-125' : 'z-10' }}"
                                    style="left: {{ $spot['position_x'] }}px; top: {{ $spot['position_y'] }}px;"
                                    wire:key="spot-{{ $spot['id'] }}"
                                    @if($editMode)
                                        draggable="true"
                                        @dragstart="handleDragStart($event, {{ $spot['id'] }})"
                                        @dragend="handleDragEnd($event)"
                                    @endif
                                    @click="$wire.selectSpot({{ $spot['id'] }})"
                                >
                                    {{-- Marqueur --}}
                                    <div class="relative group">
                                        <div class="w-10 h-10 rounded-lg flex items-center justify-center text-xs font-bold shadow-lg border-2 transition-colors
                                            {{ $spot['is_available']
                                                ? 'bg-status-success/90 border-status-success text-white'
                                                : 'bg-status-warning/90 border-status-warning text-carbon-900'
                                            }}
                                            {{ $selectedSpotId === $spot['id'] ? 'ring-2 ring-white ring-offset-2 ring-offset-carbon-900' : '' }}
                                        ">
                                            {{ $spot['spot_number'] }}
                                        </div>

                                        {{-- Tooltip --}}
                                        <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 bg-carbon-800 text-white text-xs rounded-lg whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none shadow-lg border border-carbon-700">
                                            {{ $spot['spot_number'] }} - Zone {{ $spot['zone'] }}
                                            <br>
                                            <span class="{{ $spot['is_available'] ? 'text-status-success' : 'text-status-warning' }}">
                                                {{ $spot['is_available'] ? 'En service' : 'Hors service' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach

                        {{-- Indicateur de zone de drop en mode édition --}}
                        @if($editMode)
                            <div
                                x-show="isDragging"
                                x-transition
                                class="absolute inset-0 bg-racing-red-500/10 border-2 border-dashed border-racing-red-500/50 pointer-events-none"
                            ></div>
                        @endif
                    </div>
                </div>
            </x-racing.card>
        </div>

        {{-- Panneau latéral --}}
        <div class="space-y-4">
            {{-- Emplacement sélectionné --}}
            @if($selectedSpotId)
                @php
                    $selectedSpot = collect($spots)->firstWhere('id', $selectedSpotId);
                @endphp
                @if($selectedSpot)
                    <x-racing.card>
                        <h3 class="font-semibold text-white mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-racing-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Emplacement sélectionné
                        </h3>

                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-carbon-400">Numéro</span>
                                <span class="font-mono font-bold text-checkered-yellow-500 text-lg">{{ $selectedSpot['spot_number'] }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-carbon-400">Zone</span>
                                <span class="px-2 py-0.5 bg-carbon-700 rounded text-white text-sm">{{ $selectedSpot['zone'] }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-carbon-400">État</span>
                                <span class="px-2 py-0.5 rounded text-xs font-medium {{ $selectedSpot['is_available'] ? 'bg-status-success/20 text-status-success' : 'bg-status-warning/20 text-status-warning' }}">
                                    {{ $selectedSpot['is_available'] ? 'En service' : 'Hors service' }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-carbon-400">Position</span>
                                <span class="font-mono text-sm text-white">
                                    X: {{ $selectedSpot['position_x'] }}, Y: {{ $selectedSpot['position_y'] }}
                                </span>
                            </div>
                        </div>

                        <div class="mt-4 pt-4 border-t border-carbon-700">
                            <button
                                wire:click="selectSpot(null)"
                                class="w-full text-center text-sm text-carbon-400 hover:text-white transition-colors"
                            >
                                Désélectionner
                            </button>
                        </div>
                    </x-racing.card>
                @endif
            @endif

            {{-- Emplacements non positionnés --}}
            @if($editMode && count($unpositionedSpots) > 0)
                <x-racing.card>
                    <h3 class="font-semibold text-white mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-status-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        Non positionnés
                        <span class="ml-auto text-xs bg-status-warning/20 text-status-warning px-2 py-0.5 rounded-full">
                            {{ count($unpositionedSpots) }}
                        </span>
                    </h3>

                    <div class="space-y-2 max-h-64 overflow-y-auto">
                        @foreach($unpositionedSpots as $spot)
                            <div
                                class="flex items-center gap-2 p-2 rounded-lg bg-carbon-700/50 cursor-move hover:bg-carbon-700 transition-colors"
                                draggable="true"
                                @dragstart="handleDragStart($event, {{ $spot->id }})"
                                @dragend="handleDragEnd($event)"
                            >
                                <div class="w-8 h-8 rounded flex items-center justify-center text-xs font-bold
                                    {{ $spot->is_available ? 'bg-status-success/20 text-status-success' : 'bg-status-warning/20 text-status-warning' }}">
                                    {{ Str::limit($spot->spot_number, 4) }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm text-white truncate">{{ $spot->spot_number }}</p>
                                    <p class="text-xs text-carbon-400">Zone {{ $spot->zone }}</p>
                                </div>
                                <svg class="w-4 h-4 text-carbon-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/>
                                </svg>
                            </div>
                        @endforeach
                    </div>

                    <p class="mt-3 text-xs text-carbon-500 text-center">
                        Glissez sur la carte pour positionner
                    </p>
                </x-racing.card>
            @endif

            {{-- Légende des zones --}}
            <x-racing.card>
                <h3 class="font-semibold text-white mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-carbon-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    Statistiques par zone
                </h3>

                @php
                    $zoneStats = collect($spots)->groupBy('zone')->map(function ($items, $zone) {
                        return [
                            'zone' => $zone,
                            'total' => $items->count(),
                            'positioned' => $items->where('has_position', true)->count(),
                            'in_service' => $items->where('is_available', true)->count(),
                        ];
                    })->sortKeys();
                @endphp

                <div class="space-y-2">
                    @foreach($zoneStats as $stat)
                        <div class="flex items-center gap-3 p-2 rounded-lg bg-carbon-700/30">
                            <div class="w-8 h-8 rounded-lg bg-checkered-yellow-500/20 flex items-center justify-center">
                                <span class="text-sm font-bold text-checkered-yellow-500">{{ $stat['zone'] }}</span>
                            </div>
                            <div class="flex-1">
                                <div class="flex justify-between text-sm">
                                    <span class="text-white">Zone {{ $stat['zone'] }}</span>
                                    <span class="text-carbon-400">{{ $stat['total'] }} places</span>
                                </div>
                                <div class="mt-1 flex gap-2 text-xs text-carbon-500">
                                    <span>{{ $stat['positioned'] }} positionnés</span>
                                    <span>•</span>
                                    <span class="text-status-success">{{ $stat['in_service'] }} en service</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-racing.card>
        </div>
    </div>
</div>

@script
<script>
    Alpine.data('paddockMapAdmin', () => ({
        isDragging: false,
        draggedSpotId: null,

        handleDragStart(event, spotId) {
            this.isDragging = true;
            this.draggedSpotId = spotId;
            event.dataTransfer.effectAllowed = 'move';
            event.dataTransfer.setData('text/plain', spotId);

            // Style du drag
            if (event.target.querySelector('div')) {
                event.dataTransfer.setDragImage(event.target.querySelector('div'), 20, 20);
            }
        },

        handleDragEnd(event) {
            this.isDragging = false;
            this.draggedSpotId = null;
        },

        handleDrop(event) {
            event.preventDefault();
            this.isDragging = false;

            const spotId = parseInt(event.dataTransfer.getData('text/plain'));
            if (!spotId) return;

            this.positionSpotAtEvent(event, spotId);
        },

        // Placement par clic direct : si un emplacement est sélectionné, le placer où on clique
        handleClick(event) {
            // Ignorer si on clique sur un marqueur existant
            if (event.target.closest('[wire\\:key^="spot-"]')) return;

            const selectedSpotId = this.$wire.selectedSpotId;
            if (!selectedSpotId) return;

            this.positionSpotAtEvent(event, selectedSpotId);
        },

        // Méthode commune pour calculer et envoyer la position
        positionSpotAtEvent(event, spotId) {
            const mapArea = this.$refs.mapArea;
            const rect = mapArea.getBoundingClientRect();

            // getBoundingClientRect() donne la position relative au viewport
            // Quand on scroll, rect.left/top deviennent négatifs, ce qui compense automatiquement
            // Donc on n'a PAS besoin d'ajouter scrollLeft/scrollTop (sinon on compte le scroll 2 fois)
            const x = Math.round(event.clientX - rect.left);
            const y = Math.round(event.clientY - rect.top);

            console.log('Position calculée:', { x, y, clientX: event.clientX, clientY: event.clientY, rectLeft: rect.left, rectTop: rect.top });

            // Appeler directement la méthode Livewire
            this.$wire.updateSpotPosition(spotId, x, y);
        }
    }));
</script>
@endscript
