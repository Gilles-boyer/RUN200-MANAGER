<div class="space-y-6">
    {{-- Header avec style Racing --}}
    <div class="relative overflow-hidden rounded-xl bg-racing-gradient-subtle p-6 border border-carbon-700">
        <div class="absolute top-0 right-0 w-64 h-64 bg-checkered-yellow-500/5 rounded-full -translate-y-1/2 translate-x-1/2"></div>

        <div class="relative flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-checkered-yellow-500/20 flex items-center justify-center">
                    <svg class="w-6 h-6 text-checkered-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-white">Gestion des Saisons</h1>
                    <p class="text-carbon-400 text-sm">Créez et gérez les saisons du championnat</p>
                </div>
            </div>
            <x-racing.button href="{{ route('admin.seasons.create') }}" variant="primary">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Nouvelle saison
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
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <x-racing.form.input
                wire:model.live.debounce.300ms="search"
                label="Rechercher"
                placeholder="Nom de la saison..."
                icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>'
            />
            <x-racing.form.select
                wire:model.live="statusFilter"
                label="Statut"
                :options="['' => 'Tous', 'active' => 'Active', 'inactive' => 'Inactive']"
            />
        </div>
    </x-racing.card>

    {{-- Table --}}
    <x-racing.card>
        @if($seasons->isEmpty())
            <x-racing.empty-state
                title="Aucune saison"
                description="Commencez par créer une saison."
                icon='<svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>'
                actionLabel="Créer une saison"
                :actionUrl="route('admin.seasons.create')"
            />
        @else
            {{-- Version Desktop --}}
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-carbon-700">
                            <th class="px-4 py-3 text-left text-xs font-semibold text-carbon-400 uppercase tracking-wider">Saison</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-carbon-400 uppercase tracking-wider">Période</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-carbon-400 uppercase tracking-wider">Courses</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-carbon-400 uppercase tracking-wider">Statut</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-carbon-400 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-carbon-700/50">
                        @foreach($seasons as $season)
                            <tr class="hover:bg-carbon-800/50 transition-colors">
                                <td class="px-4 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-lg {{ $season->is_active ? 'bg-status-success/20' : 'bg-carbon-700' }} flex items-center justify-center">
                                            <svg class="w-5 h-5 {{ $season->is_active ? 'text-status-success' : 'text-carbon-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                        </div>
                                        <span class="text-sm font-medium text-white">{{ $season->name }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-sm text-carbon-300">
                                    {{ $season->start_date->format('d/m/Y') }} - {{ $season->end_date->format('d/m/Y') }}
                                </td>
                                <td class="px-4 py-4">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-racing-red-500/20 text-racing-red-500 text-sm font-medium">
                                        {{ $season->races_count }} course(s)
                                    </span>
                                </td>
                                <td class="px-4 py-4">
                                    <button
                                        wire:click="toggleActive({{ $season->id }})"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium transition-colors
                                            @if($season->is_active) bg-status-success/20 text-status-success hover:bg-status-success/30
                                            @else bg-carbon-700 text-carbon-400 hover:bg-carbon-600 hover:text-carbon-300
                                            @endif
                                        "
                                    >
                                        <span class="w-2 h-2 rounded-full {{ $season->is_active ? 'bg-status-success' : 'bg-carbon-500' }}"></span>
                                        {{ $season->is_active ? 'Active' : 'Inactive' }}
                                    </button>
                                </td>
                                <td class="px-4 py-4">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.seasons.points-rules', $season) }}"
                                           class="p-2 rounded-lg text-status-success hover:bg-status-success/20 transition-colors"
                                           title="Règles de points">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                                            </svg>
                                        </a>
                                        <a href="{{ route('admin.championship', $season) }}"
                                           class="p-2 rounded-lg text-checkered-yellow-500 hover:bg-checkered-yellow-500/20 transition-colors"
                                           title="Classement">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                            </svg>
                                        </a>
                                        <a href="{{ route('admin.seasons.edit', $season) }}"
                                           class="p-2 rounded-lg text-racing-red-500 hover:bg-racing-red-500/20 transition-colors"
                                           title="Modifier">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </a>
                                        @if($season->races_count === 0)
                                            <button
                                                wire:click="deleteSeason({{ $season->id }})"
                                                wire:confirm="Êtes-vous sûr de vouloir supprimer cette saison ?"
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
                @foreach($seasons as $season)
                    <div class="bg-carbon-800/50 rounded-xl border border-carbon-700 overflow-hidden">
                        {{-- Header de la carte --}}
                        <div class="p-4 bg-carbon-800 border-b border-carbon-700 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg {{ $season->is_active ? 'bg-status-success/20' : 'bg-carbon-700' }} flex items-center justify-center">
                                    <svg class="w-5 h-5 {{ $season->is_active ? 'text-status-success' : 'text-carbon-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <span class="text-sm font-medium text-white">{{ $season->name }}</span>
                            </div>
                            <button
                                wire:click="toggleActive({{ $season->id }})"
                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium transition-colors
                                    @if($season->is_active) bg-status-success/20 text-status-success
                                    @else bg-carbon-700 text-carbon-400
                                    @endif
                                "
                            >
                                <span class="w-2 h-2 rounded-full {{ $season->is_active ? 'bg-status-success' : 'bg-carbon-500' }}"></span>
                                {{ $season->is_active ? 'Active' : 'Inactive' }}
                            </button>
                        </div>

                        {{-- Contenu --}}
                        <div class="p-4 space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-xs text-carbon-400 uppercase tracking-wider">Période</span>
                                <span class="text-sm text-carbon-300">{{ $season->start_date->format('d/m/Y') }} - {{ $season->end_date->format('d/m/Y') }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-xs text-carbon-400 uppercase tracking-wider">Courses</span>
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-racing-red-500/20 text-racing-red-500 text-xs font-medium">
                                    {{ $season->races_count }} course(s)
                                </span>
                            </div>
                        </div>

                        {{-- Footer avec actions --}}
                        <div class="px-4 py-3 bg-carbon-800/50 border-t border-carbon-700">
                            <div class="flex items-center justify-center gap-3">
                                <a href="{{ route('admin.seasons.points-rules', $season) }}"
                                   class="flex-1 flex items-center justify-center gap-1.5 py-2 rounded-lg text-sm font-medium text-status-success hover:bg-status-success/20 transition-colors"
                                   title="Règles de points">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                                    </svg>
                                    Points
                                </a>
                                <a href="{{ route('admin.championship', $season) }}"
                                   class="flex-1 flex items-center justify-center gap-1.5 py-2 rounded-lg text-sm font-medium text-checkered-yellow-500 hover:bg-checkered-yellow-500/20 transition-colors"
                                   title="Classement">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                    </svg>
                                    Classement
                                </a>
                                <a href="{{ route('admin.seasons.edit', $season) }}"
                                   class="flex-1 flex items-center justify-center gap-1.5 py-2 rounded-lg text-sm font-medium text-racing-red-500 hover:bg-racing-red-500/20 transition-colors"
                                   title="Modifier">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                    Modifier
                                </a>
                                @if($season->races_count === 0)
                                    <button
                                        wire:click="deleteSeason({{ $season->id }})"
                                        wire:confirm="Êtes-vous sûr de vouloir supprimer cette saison ?"
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

            @if($seasons->hasPages())
                <div class="mt-6 pt-6 border-t border-carbon-700">
                    {{ $seasons->links() }}
                </div>
            @endif
        @endif
    </x-racing.card>
</div>
