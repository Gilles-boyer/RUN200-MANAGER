<div class="space-y-6">
    {{-- Header avec style Racing --}}
    <div class="relative overflow-hidden rounded-xl bg-racing-gradient-subtle p-6 border border-carbon-700">
        <div class="absolute top-0 right-0 w-64 h-64 bg-checkered-yellow-500/5 rounded-full -translate-y-1/2 translate-x-1/2"></div>

        <div class="relative flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-checkered-yellow-500/20 flex items-center justify-center">
                    <svg class="w-6 h-6 text-checkered-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-white">Gestion du Paddock</h1>
                    <p class="text-carbon-400 text-sm">Gérez les emplacements du paddock pour les pilotes</p>
                </div>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.paddock-spots.map') }}" wire:navigate>
                    <x-racing.button variant="ghost">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                        </svg>
                        Voir la carte
                    </x-racing.button>
                </a>
                <x-racing.button wire:click="openBulkModal" variant="secondary">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    Créer en masse
                </x-racing.button>
                <x-racing.button wire:click="openCreateModal" variant="primary">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Nouvel emplacement
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

    @if(session('error'))
        <x-racing.alert type="danger" :dismissible="true">
            {{ session('error') }}
        </x-racing.alert>
    @endif

    @if(session('warning'))
        <x-racing.alert type="warning" :dismissible="true">
            {{ session('warning') }}
        </x-racing.alert>
    @endif

    {{-- Statistics --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <x-racing.card class="!p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-status-info/20 flex items-center justify-center">
                    <svg class="w-5 h-5 text-status-info" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-white">{{ $stats['total'] }}</p>
                    <p class="text-sm text-carbon-400">Total emplacements</p>
                </div>
            </div>
        </x-racing.card>

        <x-racing.card class="!p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-status-success/20 flex items-center justify-center">
                    <svg class="w-5 h-5 text-status-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-status-success">{{ $stats['in_service'] }}</p>
                    <p class="text-sm text-carbon-400">En service</p>
                </div>
            </div>
        </x-racing.card>

        <x-racing.card class="!p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-status-warning/20 flex items-center justify-center">
                    <svg class="w-5 h-5 text-status-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-status-warning">{{ $stats['out_of_service'] }}</p>
                    <p class="text-sm text-carbon-400">Hors service</p>
                </div>
            </div>
        </x-racing.card>
    </div>

    {{-- Info box --}}
    <x-racing.alert type="info">
        <strong>💡 Nouveau fonctionnement :</strong> Les emplacements sont réservés <strong>par course</strong>. Un pilote doit réserver un emplacement pour chaque course à laquelle il participe.
        Le champ "En service" indique si l'emplacement est utilisable (non hors service pour maintenance).
    </x-racing.alert>

    {{-- Filtres --}}
    <x-racing.card>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <x-racing.form.input
                wire:model.live.debounce.300ms="search"
                label="Rechercher"
                placeholder="Numéro d'emplacement..."
                icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>'
            />
            <x-racing.form.select
                wire:model.live="zoneFilter"
                label="Zone"
                :options="$zones"
            />
            <x-racing.form.select
                wire:model.live="statusFilter"
                label="État"
                :options="['' => 'Tous', 'in_service' => 'En service', 'out_of_service' => 'Hors service']"
            />
        </div>
    </x-racing.card>

    {{-- Table Desktop --}}
    <x-racing.card class="hidden md:block">
        @if($spots->isEmpty())
            <x-racing.empty-state
                title="Aucun emplacement"
                description="Créez vos premiers emplacements de paddock."
                icon='<svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>'
            />
        @else
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-carbon-700">
                            <th class="px-4 py-3 text-left text-xs font-semibold text-carbon-400 uppercase tracking-wider">Emplacement</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-carbon-400 uppercase tracking-wider">Zone</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-carbon-400 uppercase tracking-wider">Historique</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-carbon-400 uppercase tracking-wider">Notes</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-carbon-400 uppercase tracking-wider">État</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-carbon-400 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-carbon-700/50">
                        @foreach($spots as $spot)
                            <tr class="hover:bg-carbon-800/50 transition-colors {{ !$spot->is_available ? 'opacity-60' : '' }}">
                                <td class="px-4 py-4">
                                    <span class="inline-flex items-center px-3 py-1.5 rounded-lg bg-checkered-yellow-500/20 text-checkered-yellow-500 text-sm font-mono font-bold">
                                        {{ $spot->spot_number }}
                                    </span>
                                </td>
                                <td class="px-4 py-4">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-carbon-700 text-carbon-300 text-sm font-medium">
                                        Zone {{ $spot->zone }}
                                    </span>
                                </td>
                                <td class="px-4 py-4">
                                    <span class="text-sm text-carbon-400">
                                        {{ $spot->registrations_count }} réservation(s)
                                    </span>
                                </td>
                                <td class="px-4 py-4">
                                    @if($spot->notes)
                                        <span class="text-sm text-carbon-400 truncate max-w-[150px] block" title="{{ $spot->notes }}">
                                            {{ Str::limit($spot->notes, 30) }}
                                        </span>
                                    @else
                                        <span class="text-sm text-carbon-500 italic">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4">
                                    <button
                                        wire:click="toggleAvailable({{ $spot->id }})"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium transition-colors
                                            @if($spot->is_available) bg-status-success/20 text-status-success hover:bg-status-success/30
                                            @else bg-status-warning/20 text-status-warning hover:bg-status-warning/30
                                            @endif
                                        "
                                    >
                                        <span class="w-2 h-2 rounded-full {{ $spot->is_available ? 'bg-status-success' : 'bg-status-warning' }}"></span>
                                        {{ $spot->is_available ? 'En service' : 'Hors service' }}
                                    </button>
                                </td>
                                <td class="px-4 py-4">
                                    <div class="flex items-center justify-end gap-2">
                                        <button
                                            wire:click="openEditModal({{ $spot->id }})"
                                            class="p-2 rounded-lg text-racing-red-500 hover:bg-racing-red-500/20 transition-colors"
                                            title="Modifier"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </button>
                                        <button
                                            wire:click="delete({{ $spot->id }})"
                                            wire:confirm="Êtes-vous sûr de vouloir supprimer cet emplacement ?{{ $spot->registrations_count > 0 ? ' Il a ' . $spot->registrations_count . ' réservation(s) dans l\'historique.' : '' }}"
                                            class="p-2 rounded-lg transition-colors {{ $spot->registrations_count > 0 ? 'text-carbon-500 hover:bg-carbon-700' : 'text-status-danger hover:bg-status-danger/20' }}"
                                            title="{{ $spot->registrations_count > 0 ? 'Suppression impossible : historique de réservations' : 'Supprimer' }}"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($spots->hasPages())
                <div class="mt-6 pt-6 border-t border-carbon-700">
                    {{ $spots->links() }}
                </div>
            @endif
        @endif
    </x-racing.card>

    {{-- Mobile Cards View --}}
    <div class="md:hidden space-y-4">
        @if($spots->isEmpty())
            <x-racing.card>
                <x-racing.empty-state
                    title="Aucun emplacement"
                    description="Créez vos premiers emplacements de paddock."
                    icon='<svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>'
                />
            </x-racing.card>
        @else
            @foreach($spots as $spot)
                <x-racing.card class="!p-0 overflow-hidden {{ !$spot->is_available ? 'opacity-60' : '' }}">
                    {{-- Card Header --}}
                    <div class="p-4 border-b border-carbon-700 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex items-center px-3 py-1.5 rounded-lg bg-checkered-yellow-500/20 text-checkered-yellow-500 text-sm font-mono font-bold">
                                {{ $spot->spot_number }}
                            </span>
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-carbon-700 text-carbon-300 text-xs font-medium">
                                Zone {{ $spot->zone }}
                            </span>
                        </div>
                        <button
                            wire:click="toggleAvailable({{ $spot->id }})"
                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium transition-colors
                                @if($spot->is_available) bg-status-success/20 text-status-success
                                @else bg-status-warning/20 text-status-warning
                                @endif
                            "
                        >
                            <span class="w-2 h-2 rounded-full {{ $spot->is_available ? 'bg-status-success' : 'bg-status-warning' }}"></span>
                            {{ $spot->is_available ? 'En service' : 'Hors service' }}
                        </button>
                    </div>

                    {{-- Card Content --}}
                    <div class="p-4 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-carbon-400">Historique</span>
                            <span class="text-sm text-carbon-300">{{ $spot->registrations_count }} réservation(s)</span>
                        </div>

                        @if($spot->notes)
                            <div class="flex items-start justify-between">
                                <span class="text-xs text-carbon-400">Notes</span>
                                <span class="text-sm text-carbon-300 text-right max-w-[60%]">{{ Str::limit($spot->notes, 50) }}</span>
                            </div>
                        @endif
                    </div>

                    {{-- Card Footer --}}
                    <div class="p-4 bg-carbon-900/30 border-t border-carbon-700 flex justify-end gap-2">
                        <x-racing.button
                            wire:click="openEditModal({{ $spot->id }})"
                            variant="ghost"
                            size="sm"
                        >
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Modifier
                        </x-racing.button>
                        <x-racing.button
                            wire:click="delete({{ $spot->id }})"
                            wire:confirm="Êtes-vous sûr de vouloir supprimer cet emplacement ?{{ $spot->registrations_count > 0 ? ' Il a ' . $spot->registrations_count . ' réservation(s) dans l\'historique.' : '' }}"
                            variant="{{ $spot->registrations_count > 0 ? 'ghost' : 'danger' }}"
                            size="sm"
                            class="{{ $spot->registrations_count > 0 ? 'opacity-50 cursor-not-allowed' : '' }}"
                        >
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Supprimer
                        </x-racing.button>
                    </div>
                </x-racing.card>
            @endforeach

            @if($spots->hasPages())
                <div class="mt-4">
                    {{ $spots->links() }}
                </div>
            @endif
        @endif
    </div>

    {{-- Modal Create/Edit --}}
    @if($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-carbon-900/75 transition-opacity" wire:click="closeModal"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                <div class="inline-block align-bottom bg-carbon-800 rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-carbon-700">
                    <form wire:submit="save">
                        <div class="px-6 py-4 border-b border-carbon-700">
                            <h3 class="text-lg font-semibold text-white">
                                {{ $editingId ? 'Modifier l\'emplacement' : 'Nouvel emplacement' }}
                            </h3>
                        </div>

                        <div class="px-6 py-4 space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <x-racing.form.input
                                    wire:model="spotNumber"
                                    label="Numéro"
                                    placeholder="Ex: A001, B012..."
                                    required
                                    :error="$errors->first('spotNumber')"
                                />
                                <x-racing.form.input
                                    wire:model="zone"
                                    label="Zone"
                                    placeholder="Ex: A, B, VIP..."
                                    required
                                    :error="$errors->first('zone')"
                                />
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <x-racing.form.input
                                    wire:model="positionX"
                                    type="number"
                                    label="Position X (optionnel)"
                                    min="0"
                                    :error="$errors->first('positionX')"
                                />
                                <x-racing.form.input
                                    wire:model="positionY"
                                    type="number"
                                    label="Position Y (optionnel)"
                                    min="0"
                                    :error="$errors->first('positionY')"
                                />
                            </div>

                            <x-racing.form.textarea
                                wire:model="notes"
                                label="Notes (optionnel)"
                                placeholder="Notes supplémentaires..."
                                rows="2"
                                :error="$errors->first('notes')"
                            />

                            <x-racing.form.toggle
                                wire:model="isAvailable"
                                label="En service"
                                description="Un emplacement hors service ne peut pas être réservé (maintenance, travaux...)"
                            />
                        </div>

                        <div class="px-6 py-4 bg-carbon-900/50 border-t border-carbon-700 flex justify-end gap-3">
                            <x-racing.button type="button" variant="ghost" wire:click="closeModal">
                                Annuler
                            </x-racing.button>
                            <x-racing.button type="submit" variant="primary">
                                {{ $editingId ? 'Mettre à jour' : 'Créer' }}
                            </x-racing.button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal Bulk Create --}}
    @if($showBulkModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-carbon-900/75 transition-opacity" wire:click="closeBulkModal"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                <div class="inline-block align-bottom bg-carbon-800 rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-carbon-700">
                    <form wire:submit="createBulk">
                        <div class="px-6 py-4 border-b border-carbon-700">
                            <h3 class="text-lg font-semibold text-white">Créer des emplacements en masse</h3>
                            <p class="text-sm text-carbon-400 mt-1">Les emplacements seront numérotés automatiquement (ex: A001, A002...)</p>
                        </div>

                        <div class="px-6 py-4 space-y-4">
                            <x-racing.form.input
                                wire:model="bulkZone"
                                label="Zone"
                                placeholder="Ex: A, B, VIP..."
                                required
                            />

                            <div class="grid grid-cols-2 gap-4">
                                <x-racing.form.input
                                    wire:model="bulkStartNumber"
                                    type="number"
                                    label="Numéro de début"
                                    min="1"
                                    required
                                />
                                <x-racing.form.input
                                    wire:model="bulkEndNumber"
                                    type="number"
                                    label="Numéro de fin"
                                    min="1"
                                    max="999"
                                    required
                                />
                            </div>

                            <div class="p-3 rounded-lg bg-carbon-700/50 text-sm text-carbon-300">
                                <strong>Aperçu :</strong>
                                {{ strtoupper($bulkZone) }}{{ str_pad((string)$bulkStartNumber, 3, '0', STR_PAD_LEFT) }}
                                à
                                {{ strtoupper($bulkZone) }}{{ str_pad((string)$bulkEndNumber, 3, '0', STR_PAD_LEFT) }}
                                ({{ max(0, $bulkEndNumber - $bulkStartNumber + 1) }} emplacements)
                            </div>
                        </div>

                        <div class="px-6 py-4 bg-carbon-900/50 border-t border-carbon-700 flex justify-end gap-3">
                            <x-racing.button type="button" variant="ghost" wire:click="closeBulkModal">
                                Annuler
                            </x-racing.button>
                            <x-racing.button type="submit" variant="primary">
                                Créer les emplacements
                            </x-racing.button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
