<div class="space-y-6">
    {{-- Header avec style Racing --}}
    <div class="relative overflow-hidden rounded-xl bg-racing-gradient-subtle p-6 border border-carbon-700">
        <div class="absolute top-0 right-0 w-64 h-64 bg-checkered-yellow-500/5 rounded-full -translate-y-1/2 translate-x-1/2"></div>

        <div class="relative flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.seasons.index') }}" class="p-2 rounded-lg bg-carbon-700/50 text-carbon-400 hover:text-white hover:bg-carbon-700 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div class="w-10 h-10 rounded-lg bg-checkered-yellow-500/20 flex items-center justify-center">
                    <svg class="w-6 h-6 text-checkered-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-white">Règles de Points</h1>
                    <p class="text-carbon-400 text-sm">{{ $season->name }} - Configuration du barème de points</p>
                </div>
            </div>
            <div class="flex gap-2">
                @if($rules->isEmpty())
                    <x-racing.button wire:click="createDefaultRules" variant="secondary">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Créer règles F1
                    </x-racing.button>
                @endif
                <x-racing.button wire:click="openCreateModal" variant="primary">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Nouvelle règle
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

    {{-- Info Card --}}
    <x-racing.card class="!p-4 !bg-status-info/10 !border-status-info/30">
        <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-status-info flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div class="text-sm text-carbon-300">
                <p><strong>Comment ça marche :</strong> Définissez les points attribués pour chaque plage de positions.</p>
                <p class="mt-1">Exemple : Position 1-1 = 25pts, Position 2-2 = 18pts, Position 11-20 = 0pts...</p>
            </div>
        </div>
    </x-racing.card>

    {{-- Table --}}
    <x-racing.card>
        @if($rules->isEmpty())
            <x-racing.empty-state
                title="Aucune règle de points"
                description="Définissez le barème de points pour cette saison."
                icon='<svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>'
            />
        @else
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-carbon-700">
                            <th class="px-4 py-3 text-left text-xs font-semibold text-carbon-400 uppercase tracking-wider">Positions</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-carbon-400 uppercase tracking-wider">Points attribués</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-carbon-400 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-carbon-700/50">
                        @foreach($rules as $rule)
                            <tr class="hover:bg-carbon-800/50 transition-colors">
                                <td class="px-4 py-4">
                                    <div class="flex items-center gap-2">
                                        @if($rule->position_from === $rule->position_to)
                                            {{-- Single position --}}
                                            @if($rule->position_from <= 3)
                                                <div class="w-10 h-10 rounded-lg flex items-center justify-center
                                                    @if($rule->position_from === 1) bg-yellow-500/20 text-yellow-500
                                                    @elseif($rule->position_from === 2) bg-gray-400/20 text-gray-400
                                                    @else bg-amber-600/20 text-amber-600
                                                    @endif
                                                ">
                                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                                        <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z"/>
                                                    </svg>
                                                </div>
                                            @else
                                                <div class="w-10 h-10 rounded-lg bg-carbon-700 flex items-center justify-center">
                                                    <span class="text-sm font-bold text-carbon-300">{{ $rule->position_from }}</span>
                                                </div>
                                            @endif
                                            <span class="text-sm font-medium text-white">
                                                {{ $rule->position_from }}{{ $rule->position_from === 1 ? 'er' : 'ème' }}
                                            </span>
                                        @else
                                            {{-- Position range --}}
                                            <div class="w-10 h-10 rounded-lg bg-carbon-700 flex items-center justify-center">
                                                <span class="text-xs font-bold text-carbon-300">{{ $rule->position_from }}-{{ $rule->position_to }}</span>
                                            </div>
                                            <span class="text-sm font-medium text-white">
                                                {{ $rule->position_from }}{{ $rule->position_from === 1 ? 'er' : 'ème' }}
                                                au {{ $rule->position_to }}{{ $rule->position_to === 1 ? 'er' : 'ème' }}
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-4">
                                    <span class="inline-flex items-center px-3 py-1.5 rounded-lg
                                        @if($rule->points > 0) bg-checkered-yellow-500/20 text-checkered-yellow-500
                                        @else bg-carbon-700 text-carbon-400
                                        @endif
                                        text-lg font-bold"
                                    >
                                        {{ $rule->points }} pts
                                    </span>
                                </td>
                                <td class="px-4 py-4">
                                    <div class="flex items-center justify-end gap-2">
                                        <button
                                            wire:click="openEditModal({{ $rule->id }})"
                                            class="p-2 rounded-lg text-racing-red-500 hover:bg-racing-red-500/20 transition-colors"
                                            title="Modifier"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </button>
                                        <button
                                            wire:click="delete({{ $rule->id }})"
                                            wire:confirm="Êtes-vous sûr de vouloir supprimer cette règle ?"
                                            class="p-2 rounded-lg text-status-danger hover:bg-status-danger/20 transition-colors"
                                            title="Supprimer"
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

            {{-- Summary --}}
            <div class="mt-6 pt-6 border-t border-carbon-700">
                <div class="flex items-center gap-4 text-sm text-carbon-400">
                    <span>{{ $rules->count() }} règle(s) configurée(s)</span>
                    <span>•</span>
                    <span>Max points pour 1ère place : {{ $rules->where('position_from', 1)->first()?->points ?? 0 }} pts</span>
                </div>
            </div>
        @endif
    </x-racing.card>

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
                                {{ $editingId ? 'Modifier la règle' : 'Nouvelle règle de points' }}
                            </h3>
                        </div>

                        <div class="px-6 py-4 space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <x-racing.form.input
                                    wire:model="positionFrom"
                                    type="number"
                                    label="Position de début"
                                    min="1"
                                    max="999"
                                    required
                                    :error="$errors->first('positionFrom')"
                                />
                                <x-racing.form.input
                                    wire:model="positionTo"
                                    type="number"
                                    label="Position de fin"
                                    min="1"
                                    max="999"
                                    required
                                    :error="$errors->first('positionTo')"
                                />
                            </div>

                            <x-racing.form.input
                                wire:model="points"
                                type="number"
                                label="Points attribués"
                                min="0"
                                max="1000"
                                required
                                :error="$errors->first('points')"
                            />

                            <div class="p-3 rounded-lg bg-carbon-700/50 text-sm text-carbon-300">
                                @if($positionFrom == $positionTo)
                                    <strong>Aperçu :</strong> Le {{ $positionFrom }}{{ $positionFrom === 1 ? 'er' : 'ème' }} recevra {{ $points }} point(s)
                                @else
                                    <strong>Aperçu :</strong> Du {{ $positionFrom }}{{ $positionFrom === 1 ? 'er' : 'ème' }} au {{ $positionTo }}{{ $positionTo === 1 ? 'er' : 'ème' }} recevront {{ $points }} point(s) chacun
                                @endif
                            </div>
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
</div>
