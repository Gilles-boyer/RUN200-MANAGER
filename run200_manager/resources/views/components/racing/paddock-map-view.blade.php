@props([
    'spots',
    'selectedSpotId' => null,
    'highlightSpotId' => null,
    'width' => 2000,
    'height' => 2000,
    'interactive' => true,
    'emptyMessage' => 'Aucun emplacement positionné sur la carte',
])

@php
    $positionedSpots = $spots->filter(fn($s) => isset($s['position_x']) && isset($s['position_y']) && $s['position_x'] !== null && $s['position_y'] !== null);
@endphp

<div class="relative bg-carbon-900 rounded-xl overflow-hidden border border-carbon-700">
    @if($positionedSpots->isEmpty())
        <div class="flex items-center justify-center" style="height: 400px;">
            <div class="text-center">
                <svg class="w-16 h-16 mx-auto text-carbon-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                </svg>
                <p class="text-carbon-500">{{ $emptyMessage }}</p>
            </div>
        </div>
    @else
        <div class="overflow-auto" style="max-height: 700px;">
            <div
                class="relative bg-contain bg-center bg-no-repeat"
                style="width: {{ $width }}px; height: {{ $height }}px; background-image: url('{{ asset('images/paddock-map.svg') }}'); background-color: #1a1a1a;"
            >
                @foreach($positionedSpots as $spot)
                    @php
                        $isSelected = $selectedSpotId === $spot['id'];
                        $isHighlighted = $highlightSpotId === $spot['id'];
                        $isOccupied = isset($spot['is_occupied_for_race']) ? $spot['is_occupied_for_race'] : !($spot['is_available'] ?? true);
                        $isAvailable = !$isOccupied && ($spot['is_available'] ?? true);
                    @endphp
                    <div
                        class="absolute transform -translate-x-1/2 -translate-y-1/2 transition-all duration-150 z-10
                            {{ $interactive ? 'cursor-pointer hover:scale-110' : '' }}
                            {{ $isSelected || $isHighlighted ? 'z-20 scale-125' : '' }}"
                        style="left: {{ $spot['position_x'] }}px; top: {{ $spot['position_y'] }}px;"
                        @if($interactive && isset($attributes['wire:click']))
                            wire:click="{{ $attributes['wire:click'] }}({{ $spot['id'] }})"
                        @endif
                    >
                        <div class="relative group">
                            {{-- Marqueur --}}
                            <div class="w-10 h-10 rounded-lg flex items-center justify-center text-xs font-bold shadow-lg border-2 transition-colors
                                @if($isHighlighted)
                                    bg-racing-red-500 border-racing-red-400 text-white ring-2 ring-racing-red-500/50 ring-offset-2 ring-offset-carbon-900
                                @elseif($isSelected)
                                    bg-checkered-yellow-500 border-checkered-yellow-400 text-carbon-900 ring-2 ring-white ring-offset-2 ring-offset-carbon-900
                                @elseif($isOccupied)
                                    bg-status-danger/90 border-status-danger text-white
                                @elseif($isAvailable)
                                    bg-status-success/90 border-status-success text-white
                                @else
                                    bg-status-warning/90 border-status-warning text-carbon-900
                                @endif
                            ">
                                {{ $spot['spot_number'] ?? 'N/A' }}
                            </div>

                            {{-- Tooltip --}}
                            <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-3 py-2 bg-carbon-800 text-white text-xs rounded-lg whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none shadow-lg border border-carbon-700 z-30">
                                <div class="font-bold text-sm">{{ $spot['spot_number'] ?? 'N/A' }}</div>
                                <div class="text-carbon-400">Zone {{ $spot['zone'] ?? '?' }}</div>
                                @if(isset($spot['pilot_name']) && $spot['pilot_name'])
                                    <div class="mt-1 pt-1 border-t border-carbon-700">
                                        <span class="text-racing-red-500">🏎️ {{ $spot['pilot_name'] }}</span>
                                    </div>
                                @endif
                                <div class="mt-1">
                                    @if($isOccupied)
                                        <span class="text-status-danger">● Occupé</span>
                                    @elseif($isAvailable)
                                        <span class="text-status-success">● Disponible</span>
                                    @else
                                        <span class="text-status-warning">● Hors service</span>
                                    @endif
                                </div>
                                {{-- Flèche --}}
                                <div class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-carbon-800"></div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Légende --}}
        <div class="p-3 bg-carbon-800/50 border-t border-carbon-700 flex flex-wrap items-center justify-center gap-4 text-xs">
            <div class="flex items-center gap-1.5">
                <span class="w-3 h-3 rounded bg-status-success"></span>
                <span class="text-carbon-400">Disponible</span>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="w-3 h-3 rounded bg-status-danger"></span>
                <span class="text-carbon-400">Occupé</span>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="w-3 h-3 rounded bg-status-warning"></span>
                <span class="text-carbon-400">Hors service</span>
            </div>
            @if($selectedSpotId)
                <div class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded bg-checkered-yellow-500"></span>
                    <span class="text-carbon-400">Sélectionné</span>
                </div>
            @endif
            @if($highlightSpotId)
                <div class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded bg-racing-red-500"></span>
                    <span class="text-carbon-400">Votre emplacement</span>
                </div>
            @endif
        </div>
    @endif
</div>
