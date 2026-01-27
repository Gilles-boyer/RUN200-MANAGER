<div class="space-y-6">
    {{-- Header avec style Racing --}}
    <div class="relative overflow-hidden rounded-xl bg-racing-gradient-subtle p-6 border border-carbon-700">
        <div class="absolute top-0 right-0 w-64 h-64 bg-racing-red-500/5 rounded-full -translate-y-1/2 translate-x-1/2"></div>

        <div class="relative flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-racing-red-500/20 flex items-center justify-center">
                    <svg class="w-6 h-6 text-racing-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-white">Gestion des Pilotes</h1>
                    <p class="text-carbon-400 text-sm">Liste de tous les pilotes inscrits dans le système</p>
                </div>
            </div>
            <x-racing.button href="{{ route('staff.pilots.create') }}" variant="primary" wire:navigate>
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Nouveau pilote
            </x-racing.button>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if (session()->has('success'))
        <x-racing.alert type="success" :dismissible="true">
            {{ session('success') }}
        </x-racing.alert>
    @endif

    {{-- Statistiques --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <x-racing.stat-card
            label="Total pilotes"
            :value="$pilots->total()"
            icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>'
            color="info"
        />
    </div>

    {{-- Filtres --}}
    <x-racing.card>
        <x-racing.form.input
            wire:model.live.debounce.300ms="search"
            label="Rechercher"
            placeholder="Rechercher par nom, prénom, licence, téléphone ou email..."
            icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>'
        />
    </x-racing.card>

    {{-- Table --}}
    <x-racing.card>
        {{-- Version Desktop --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-carbon-700">
                        <th class="px-4 py-3 text-left text-xs font-semibold text-carbon-400 uppercase tracking-wider">
                            <button wire:click="sortBy('last_name')" class="group inline-flex items-center gap-1 hover:text-white transition-colors">
                                Pilote
                                @if ($sortField === 'last_name')
                                    <svg class="w-4 h-4 text-racing-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $sortDirection === 'asc' ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7' }}"/>
                                    </svg>
                                @endif
                            </button>
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-carbon-400 uppercase tracking-wider">Contact</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-carbon-400 uppercase tracking-wider">
                            <button wire:click="sortBy('license_number')" class="group inline-flex items-center gap-1 hover:text-white transition-colors">
                                Licence
                                @if ($sortField === 'license_number')
                                    <svg class="w-4 h-4 text-racing-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $sortDirection === 'asc' ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7' }}"/>
                                    </svg>
                                @endif
                            </button>
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-carbon-400 uppercase tracking-wider">Véhicules</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-carbon-400 uppercase tracking-wider">Inscriptions</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-carbon-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-carbon-700/50">
                    @forelse ($pilots as $pilot)
                        <tr class="hover:bg-carbon-800/50 transition-colors">
                            <td class="px-4 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-racing-red-500 to-racing-red-700 flex items-center justify-center text-white font-semibold text-sm">
                                        {{ substr($pilot->first_name, 0, 1) }}{{ substr($pilot->last_name, 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-white">
                                            {{ $pilot->last_name }} {{ $pilot->first_name }}
                                        </div>
                                        @if ($pilot->birth_date)
                                            <div class="text-xs text-carbon-400">
                                                {{ $pilot->birth_date->format('d/m/Y') }}
                                                <span class="text-carbon-500">({{ $pilot->birth_date->age }} ans)</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                <div class="space-y-1">
                                    @if ($pilot->user?->email)
                                        <a href="mailto:{{ $pilot->user->email }}" class="block text-sm text-racing-red-500 hover:text-racing-red-400 transition-colors">
                                            {{ $pilot->user->email }}
                                        </a>
                                    @else
                                        <span class="text-sm text-carbon-500">-</span>
                                    @endif
                                    @if ($pilot->phone)
                                        <a href="tel:{{ $pilot->phone }}" class="block text-xs text-carbon-400 hover:text-carbon-300 transition-colors">
                                            {{ $pilot->phone }}
                                        </a>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                @if ($pilot->license_number)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-status-success/20 text-status-success text-xs font-medium">
                                        {{ $pilot->license_number }}
                                    </span>
                                @else
                                    <span class="text-sm text-carbon-500">Non renseignée</span>
                                @endif
                            </td>
                            <td class="px-4 py-4">
                                @if ($pilot->cars->count() > 0)
                                    <div class="space-y-1">
                                        @foreach ($pilot->cars->take(2) as $car)
                                            <div class="flex items-center gap-1.5">
                                                <span class="inline-flex items-center justify-center w-6 h-6 rounded bg-racing-red-500/20 text-racing-red-500 text-xs font-bold">
                                                    {{ $car->race_number ?? '?' }}
                                                </span>
                                                <span class="text-sm text-carbon-300">{{ $car->brand ?? '' }} {{ $car->model ?? '' }}</span>
                                            </div>
                                        @endforeach
                                        @if ($pilot->cars->count() > 2)
                                            <span class="text-xs text-carbon-500">+{{ $pilot->cars->count() - 2 }} autre(s)</span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-sm text-carbon-500">Aucun véhicule</span>
                                @endif
                            </td>
                            <td class="px-4 py-4">
                                @php
                                    $registrationsCount = $pilot->race_registrations_count ?? $pilot->raceRegistrations()->count();
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg {{ $registrationsCount > 0 ? 'bg-status-info/20 text-status-info' : 'bg-carbon-700 text-carbon-400' }} text-xs font-medium">
                                    {{ $registrationsCount }} inscription(s)
                                </span>
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex items-center justify-end">
                                    <a
                                        href="{{ route('staff.pilots.edit', $pilot) }}"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium text-racing-red-500 hover:bg-racing-red-500/20 transition-colors"
                                        wire:navigate
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        Voir / Modifier
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12">
                                <x-racing.empty-state
                                    title="Aucun pilote trouvé"
                                    :description="$search ? 'Aucun pilote ne correspond à votre recherche \"' . $search . '\".' : 'Aucun pilote n\'est enregistré dans le système.'"
                                    icon='<svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>'
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Version Mobile (Cards) --}}
        <div class="md:hidden space-y-4">
            @forelse ($pilots as $pilot)
                <div class="bg-carbon-800/50 rounded-xl border border-carbon-700 overflow-hidden">
                    {{-- Header de la carte --}}
                    <div class="p-4 bg-carbon-800 border-b border-carbon-700 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-racing-red-500 to-racing-red-700 flex items-center justify-center text-white font-semibold text-sm">
                                {{ substr($pilot->first_name, 0, 1) }}{{ substr($pilot->last_name, 0, 1) }}
                            </div>
                            <div>
                                <div class="text-sm font-medium text-white">
                                    {{ $pilot->last_name }} {{ $pilot->first_name }}
                                </div>
                                @if ($pilot->birth_date)
                                    <div class="text-xs text-carbon-400">
                                        {{ $pilot->birth_date->format('d/m/Y') }}
                                        <span class="text-carbon-500">({{ $pilot->birth_date->age }} ans)</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                        @if ($pilot->license_number)
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-status-success/20 text-status-success text-xs font-medium">
                                {{ $pilot->license_number }}
                            </span>
                        @endif
                    </div>

                    {{-- Contenu --}}
                    <div class="p-4 space-y-3">
                        {{-- Contact --}}
                        @if ($pilot->user?->email)
                            <div class="flex justify-between items-center">
                                <span class="text-xs text-carbon-400 uppercase tracking-wider">Email</span>
                                <a href="mailto:{{ $pilot->user->email }}" class="text-sm text-racing-red-500 hover:text-racing-red-400 transition-colors truncate max-w-[60%]">
                                    {{ $pilot->user->email }}
                                </a>
                            </div>
                        @endif
                        @if ($pilot->phone)
                            <div class="flex justify-between items-center">
                                <span class="text-xs text-carbon-400 uppercase tracking-wider">Téléphone</span>
                                <a href="tel:{{ $pilot->phone }}" class="text-sm text-white hover:text-carbon-300 transition-colors">
                                    {{ $pilot->phone }}
                                </a>
                            </div>
                        @endif

                        {{-- Véhicules --}}
                        @if ($pilot->cars->count() > 0)
                            <div class="pt-2 border-t border-carbon-700">
                                <span class="text-xs text-carbon-400 uppercase tracking-wider block mb-2">Véhicules</span>
                                <div class="flex flex-wrap gap-2">
                                    @foreach ($pilot->cars->take(3) as $car)
                                        <div class="flex items-center gap-1.5 bg-carbon-700/50 rounded-lg px-2 py-1">
                                            <span class="inline-flex items-center justify-center w-5 h-5 rounded bg-racing-red-500/20 text-racing-red-500 text-xs font-bold">
                                                {{ $car->race_number ?? '?' }}
                                            </span>
                                            <span class="text-xs text-carbon-300">{{ $car->brand ?? '' }}</span>
                                        </div>
                                    @endforeach
                                    @if ($pilot->cars->count() > 3)
                                        <span class="text-xs text-carbon-500 self-center">+{{ $pilot->cars->count() - 3 }}</span>
                                    @endif
                                </div>
                            </div>
                        @endif

                        {{-- Inscriptions --}}
                        @php
                            $registrationsCount = $pilot->race_registrations_count ?? $pilot->raceRegistrations()->count();
                        @endphp
                        <div class="flex justify-between items-center pt-2 border-t border-carbon-700">
                            <span class="text-xs text-carbon-400 uppercase tracking-wider">Inscriptions</span>
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg {{ $registrationsCount > 0 ? 'bg-status-info/20 text-status-info' : 'bg-carbon-700 text-carbon-400' }} text-xs font-medium">
                                {{ $registrationsCount }}
                            </span>
                        </div>
                    </div>

                    {{-- Footer avec action --}}
                    <div class="px-4 py-3 bg-carbon-800/50 border-t border-carbon-700">
                        <a
                            href="{{ route('staff.pilots.edit', $pilot) }}"
                            class="flex items-center justify-center gap-2 w-full py-2 rounded-lg text-sm font-medium text-racing-red-500 hover:bg-racing-red-500/20 transition-colors"
                            wire:navigate
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            Voir / Modifier
                        </a>
                    </div>
                </div>
            @empty
                <x-racing.empty-state
                    title="Aucun pilote trouvé"
                    :description="$search ? 'Aucun pilote ne correspond à votre recherche \"' . $search . '\".' : 'Aucun pilote n\'est enregistré dans le système.'"
                    icon='<svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>'
                />
            @endforelse
        </div>

        {{-- Pagination --}}
        @if ($pilots->hasPages())
            <div class="mt-6 pt-6 border-t border-carbon-700">
                {{ $pilots->links() }}
            </div>
        @endif
    </x-racing.card>
</div>
