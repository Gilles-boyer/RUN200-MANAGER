<div class="space-y-6">
    {{-- Header avec style Racing --}}
    <div class="relative overflow-hidden rounded-xl bg-racing-gradient-subtle p-6 border border-carbon-700">
        <div class="absolute top-0 right-0 w-64 h-64 bg-racing-red-500/5 rounded-full -translate-y-1/2 translate-x-1/2"></div>

        <div class="relative flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-racing-red-500/20 flex items-center justify-center">
                    <svg class="w-6 h-6 text-racing-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-white">Gestion des Courses</h1>
                    <p class="text-carbon-400 text-sm">Créez et gérez les courses du championnat</p>
                </div>
            </div>
            <x-racing.button href="{{ route('admin.races.create') }}" variant="primary">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Nouvelle course
            </x-racing.button>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
        <x-racing.alert type="success" :dismissible="true">
            {{ session('success') }}
        </x-racing.alert>
    @endif

    @if(session('error'))
        <x-racing.alert type="danger" :dismissible="true">
            {{ session('error') }}
        </x-racing.alert>
    @endif

    {{-- Filtres --}}
    <x-racing.card>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <x-racing.form.input
                wire:model.live.debounce.300ms="search"
                label="Rechercher"
                placeholder="Nom, lieu..."
                icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>'
            />
            <x-racing.form.select
                wire:model.live="statusFilter"
                label="Statut"
                :options="['' => 'Tous', 'DRAFT' => 'Brouillon', 'OPEN' => 'Ouvert', 'CLOSED' => 'Fermé', 'COMPLETED' => 'Terminé', 'CANCELLED' => 'Annulé']"
            />
            <x-racing.form.select
                wire:model.live="seasonFilter"
                label="Saison"
                :options="$seasons->pluck('name', 'id')->prepend('Toutes', '')"
            />
        </div>
    </x-racing.card>

    {{-- Table --}}
    <x-racing.card>
        @if($races->isEmpty())
            <x-racing.empty-state
                title="Aucune course"
                description="Commencez par créer une course."
                icon='<svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/></svg>'
                actionLabel="Créer une course"
                :actionUrl="route('admin.races.create')"
            />
        @else
            {{-- Version Desktop --}}
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-carbon-700">
                            <th class="px-4 py-3 text-left text-xs font-semibold text-carbon-400 uppercase tracking-wider">Course</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-carbon-400 uppercase tracking-wider">Saison</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-carbon-400 uppercase tracking-wider">Date</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-carbon-400 uppercase tracking-wider">Prix</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-carbon-400 uppercase tracking-wider">Inscriptions</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-carbon-400 uppercase tracking-wider">Statut</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-carbon-400 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-carbon-700/50">
                        @foreach($races as $race)
                            <tr class="hover:bg-carbon-800/50 transition-colors">
                                <td class="px-4 py-4">
                                    <div class="text-sm font-medium text-white">{{ $race->name }}</div>
                                    <div class="text-xs text-carbon-400">{{ $race->location }}</div>
                                </td>
                                <td class="px-4 py-4 text-sm text-carbon-400">
                                    {{ $race->season->name ?? 'N/A' }}
                                </td>
                                <td class="px-4 py-4 text-sm text-carbon-300">
                                    {{ $race->race_date->format('d/m/Y') }}
                                </td>
                                <td class="px-4 py-4 text-sm font-medium text-racing-red-500">
                                    {{ $race->formatted_entry_fee }}
                                </td>
                                <td class="px-4 py-4">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-carbon-700 text-carbon-300 text-sm font-medium">
                                        {{ $race->registrations->count() }}
                                    </span>
                                </td>
                                <td class="px-4 py-4">
                                    <select
                                        wire:change="updateStatus({{ $race->id }}, $event.target.value)"
                                        class="text-xs rounded-lg px-3 py-1.5 border-0 cursor-pointer font-medium
                                            @if($race->status === 'OPEN') bg-status-success/20 text-status-success
                                            @elseif($race->status === 'DRAFT') bg-carbon-700 text-carbon-300
                                            @elseif($race->status === 'CLOSED') bg-status-danger/20 text-status-danger
                                            @elseif($race->status === 'COMPLETED') bg-status-info/20 text-status-info
                                            @else bg-status-warning/20 text-status-warning
                                            @endif
                                        "
                                    >
                                        <option value="DRAFT" @selected($race->status === 'DRAFT')>Brouillon</option>
                                        <option value="OPEN" @selected($race->status === 'OPEN')>Ouvert</option>
                                        <option value="CLOSED" @selected($race->status === 'CLOSED')>Fermé</option>
                                        <option value="COMPLETED" @selected($race->status === 'COMPLETED')>Terminé</option>
                                        <option value="CANCELLED" @selected($race->status === 'CANCELLED')>Annulé</option>
                                    </select>
                                </td>
                                <td class="px-4 py-4">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.races.notifications', $race) }}"
                                           class="p-2 rounded-lg text-purple-500 hover:bg-purple-500/20 transition-colors"
                                           title="Notifications">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                            </svg>
                                        </a>
                                        <a href="{{ route('admin.races.edit', $race) }}"
                                           class="p-2 rounded-lg text-racing-red-500 hover:bg-racing-red-500/20 transition-colors"
                                           title="Modifier">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </a>
                                        @if($race->registrations->count() === 0)
                                            <button
                                                wire:click="deleteRace({{ $race->id }})"
                                                wire:confirm="Êtes-vous sûr de vouloir supprimer cette course ?"
                                                class="p-2 rounded-lg text-status-danger hover:bg-status-danger/20 transition-colors"
                                                title="Supprimer"
                                            >
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Version Mobile (Cards) --}}
            <div class="md:hidden space-y-4">
                @foreach($races as $race)
                    <div class="bg-carbon-800/50 rounded-xl border border-carbon-700 overflow-hidden">
                        {{-- Header de la carte --}}
                        <div class="p-4 bg-carbon-800 border-b border-carbon-700 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-racing-red-500/20 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-racing-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/>
                                    </svg>
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-white">{{ $race->name }}</div>
                                    <div class="text-xs text-carbon-400">{{ $race->location }}</div>
                                </div>
                            </div>
                            <select
                                wire:change="updateStatus({{ $race->id }}, $event.target.value)"
                                class="text-xs rounded-lg px-2 py-1 border-0 cursor-pointer font-medium
                                    @if($race->status === 'OPEN') bg-status-success/20 text-status-success
                                    @elseif($race->status === 'DRAFT') bg-carbon-700 text-carbon-300
                                    @elseif($race->status === 'CLOSED') bg-status-danger/20 text-status-danger
                                    @elseif($race->status === 'COMPLETED') bg-status-info/20 text-status-info
                                    @else bg-status-warning/20 text-status-warning
                                    @endif
                                "
                            >
                                <option value="DRAFT" @selected($race->status === 'DRAFT')>Brouillon</option>
                                <option value="OPEN" @selected($race->status === 'OPEN')>Ouvert</option>
                                <option value="CLOSED" @selected($race->status === 'CLOSED')>Fermé</option>
                                <option value="COMPLETED" @selected($race->status === 'COMPLETED')>Terminé</option>
                                <option value="CANCELLED" @selected($race->status === 'CANCELLED')>Annulé</option>
                            </select>
                        </div>

                        {{-- Contenu --}}
                        <div class="p-4 space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-xs text-carbon-400 uppercase tracking-wider">Saison</span>
                                <span class="text-sm text-carbon-300">{{ $race->season->name ?? 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-xs text-carbon-400 uppercase tracking-wider">Date</span>
                                <span class="text-sm text-carbon-300">{{ $race->race_date->format('d/m/Y') }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-xs text-carbon-400 uppercase tracking-wider">Prix</span>
                                <span class="text-sm font-medium text-racing-red-500">{{ $race->formatted_entry_fee }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-xs text-carbon-400 uppercase tracking-wider">Inscriptions</span>
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-carbon-700 text-carbon-300 text-xs font-medium">
                                    {{ $race->registrations->count() }}
                                </span>
                            </div>
                        </div>

                        {{-- Footer avec actions --}}
                        <div class="px-4 py-3 bg-carbon-800/50 border-t border-carbon-700">
                            <div class="flex items-center justify-center gap-3">
                                <a href="{{ route('admin.races.notifications', $race) }}"
                                   class="flex-1 flex items-center justify-center gap-1.5 py-2 rounded-lg text-sm font-medium text-purple-500 hover:bg-purple-500/20 transition-colors"
                                   title="Notifications">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                    </svg>
                                    Notifs
                                </a>
                                <a href="{{ route('admin.races.edit', $race) }}"
                                   class="flex-1 flex items-center justify-center gap-1.5 py-2 rounded-lg text-sm font-medium text-racing-red-500 hover:bg-racing-red-500/20 transition-colors"
                                   title="Modifier">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                    Modifier
                                </a>
                                @if($race->registrations->count() === 0)
                                    <button
                                        wire:click="deleteRace({{ $race->id }})"
                                        wire:confirm="Êtes-vous sûr de vouloir supprimer cette course ?"
                                        class="p-2 rounded-lg text-status-danger hover:bg-status-danger/20 transition-colors"
                                        title="Supprimer"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @if($races->hasPages())
                <div class="mt-6 pt-6 border-t border-carbon-700">
                    {{ $races->links() }}
                </div>
            @endif
        @endif
    </x-racing.card>
</div>
