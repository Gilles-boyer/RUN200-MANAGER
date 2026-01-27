@props([
    'race' => null,
    'name' => null,
    'date' => null,
    'location' => null,
    'status' => null,
    'price' => null,
    'registrations' => null,
    'season' => null,
    'isRegistered' => false,
    'canRegister' => true,
    'registerUrl' => null,
    'showRegisterButton' => true,
    'registerRoute' => null,
])

@php
    // Support les deux modes : objet race ou props individuels
    if ($race) {
        $raceName = $race->name;
        $raceDate = $race->race_date ?? $race->starts_at;
        $raceLocation = $race->location;
        $raceStatus = $race->status;
        $racePrice = $race->formatted_entry_fee ?? null;
        $raceRegistrations = $race->registrations_count ?? $race->registrations?->count();
        $raceSeason = $race->season?->name;
        $isOpen = $race->status === 'OPEN' || (isset($race->registration_opens_at) && $race->registration_opens_at <= now() && $race->registration_closes_at >= now());
        $raceRegisterUrl = $registerRoute ?? route('pilot.registrations.create', $race);
    } else {
        $raceName = $name;
        $raceDate = $date;
        $raceLocation = $location;
        $raceStatus = $status;
        $racePrice = $price;
        $raceRegistrations = $registrations;
        $raceSeason = $season;
        $isOpen = $status === 'OPEN';
        $raceRegisterUrl = $registerUrl ?? '#';
    }
    $daysUntil = $raceDate ? now()->startOfDay()->diffInDays($raceDate, false) : -1;
@endphp

<div {{ $attributes->merge(['class' => 'card-racing overflow-hidden hover:shadow-lg transition-shadow']) }}>
    {{-- Header with date --}}
    <div class="card-racing-header">
        <div class="flex items-center justify-between">
            <div>
                <div class="text-white/80 text-sm uppercase tracking-wide">
                    {{ $raceDate?->translatedFormat('l') }}
                </div>
                <div class="text-white text-2xl font-bold">
                    {{ $raceDate?->format('d') }}
                    <span class="text-lg font-normal">{{ $raceDate?->translatedFormat('F Y') }}</span>
                </div>
            </div>
            @if($isRegistered)
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm font-medium bg-status-success/20 text-status-success border border-status-success/30">
                    ✓ Inscrit
                </span>
            @elseif($isOpen)
                <x-racing.badge-status status="success" size="sm">
                    Ouvert
                </x-racing.badge-status>
            @elseif($raceStatus === 'COMPLETED')
                <x-racing.badge-status status="info" size="sm">
                    Terminé
                </x-racing.badge-status>
            @else
                <x-racing.badge-status status="neutral" size="sm">
                    Fermé
                </x-racing.badge-status>
            @endif
        </div>
    </div>

    {{-- Body --}}
    <div class="card-racing-body">
        <h3 class="text-lg font-semibold text-carbon-900 dark:text-white mb-2">
            {{ $raceName }}
        </h3>

        <div class="space-y-2 text-sm">
            {{-- Lieu --}}
            <div class="flex items-center text-carbon-500 dark:text-carbon-400">
                <svg class="w-4 h-4 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                {{ $raceLocation }}
            </div>

            {{-- Infos ligne --}}
            <div class="flex flex-wrap items-center gap-4 text-carbon-500 dark:text-carbon-400">
                @if($raceRegistrations !== null)
                    <span class="flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        {{ $raceRegistrations }} inscrit(s)
                    </span>
                @endif

                @if($raceSeason)
                    <span class="flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                        {{ $raceSeason }}
                    </span>
                @endif
            </div>
        </div>

        {{-- Prix et Countdown --}}
        <div class="flex items-center justify-between mt-4 pt-4 border-t border-carbon-200 dark:border-carbon-700">
            <div>
                @if($daysUntil === 0)
                    <span class="text-status-success font-semibold">🏁 Aujourd'hui !</span>
                @elseif($daysUntil === 1)
                    <span class="text-status-warning font-semibold">⏰ Demain</span>
                @elseif($daysUntil > 0)
                    <span class="text-carbon-500 dark:text-carbon-400">Dans {{ $daysUntil }} jours</span>
                @else
                    <span class="text-carbon-400">Course passée</span>
                @endif
            </div>

            @if($racePrice)
                <span class="text-lg font-bold text-racing-red-600 dark:text-racing-red-400">
                    {{ $racePrice }}
                </span>
            @endif
        </div>
    </div>

    {{-- Footer with action --}}
    @if($showRegisterButton)
        <div class="card-racing-footer">
            @if($isRegistered)
                <span class="w-full text-center py-2 text-status-success font-medium">
                    ✓ Vous êtes inscrit à cette course
                </span>
            @elseif($canRegister && $isOpen && $daysUntil >= 0)
                <x-racing.button
                    :href="$raceRegisterUrl"
                    variant="primary"
                    class="w-full"
                >
                    S'inscrire →
                </x-racing.button>
            @elseif(!$isOpen)
                <span class="w-full text-center py-2 text-carbon-400 font-medium">
                    Inscriptions fermées
                </span>
            @endif
        </div>
    @endif
</div>
