<div class="space-y-6">
    {{-- Header avec style Racing --}}
    <div class="relative overflow-hidden rounded-xl bg-racing-gradient-subtle p-6 border border-carbon-700">
        <div class="absolute top-0 right-0 w-64 h-64 bg-checkered-yellow-500/5 rounded-full -translate-y-1/2 translate-x-1/2"></div>

        <div class="relative flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-racing-red-500/20 flex items-center justify-center">
                    <svg class="w-6 h-6 text-racing-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-white">Catégories de Voitures</h1>
                    <p class="text-carbon-400 text-sm">Gérez les catégories pour le classement du championnat</p>
                </div>
            </div>
            <x-racing.button wire:click="openCreateModal" variant="primary">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Nouvelle catégorie
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
                placeholder="Nom de la catégorie..."
                icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>'
            />
            <x-racing.form.select
                wire:model.live="statusFilter"
                label="Statut"
                :options="['' => 'Tous', 'active' => 'Active', 'inactive' => 'Inactive']"
            />
        </div>
    </x-racing.card>

    {{-- Table Desktop --}}
    <x-racing.card class="hidden md:block">
        @if($categories->isEmpty())
            <x-racing.empty-state
                title="Aucune catégorie"
                description="Créez votre première catégorie de voiture."
                icon='<svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>'
            />
        @else
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-carbon-700">
                            <th class="px-4 py-3 text-left text-xs font-semibold text-carbon-400 uppercase tracking-wider">Ordre</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-carbon-400 uppercase tracking-wider">Catégorie</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-carbon-400 uppercase tracking-wider">Voitures</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-carbon-400 uppercase tracking-wider">Statut</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-carbon-400 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-carbon-700/50">
                        @foreach($categories as $category)
                            <tr class="hover:bg-carbon-800/50 transition-colors">
                                <td class="px-4 py-4">
                                    <div class="flex items-center gap-1">
                                        <button
                                            wire:click="moveUp({{ $category->id }})"
                                            class="p-1 rounded text-carbon-400 hover:text-white hover:bg-carbon-700 transition-colors disabled:opacity-30"
                                            @if($loop->first) disabled @endif
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                                            </svg>
                                        </button>
                                        <span class="w-8 text-center text-sm text-carbon-400">{{ $category->sort_order }}</span>
                                        <button
                                            wire:click="moveDown({{ $category->id }})"
                                            class="p-1 rounded text-carbon-400 hover:text-white hover:bg-carbon-700 transition-colors disabled:opacity-30"
                                            @if($loop->last) disabled @endif
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                                <td class="px-4 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-lg {{ $category->is_active ? 'bg-racing-red-500/20' : 'bg-carbon-700' }} flex items-center justify-center">
                                            <svg class="w-5 h-5 {{ $category->is_active ? 'text-racing-red-500' : 'text-carbon-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                            </svg>
                                        </div>
                                        <span class="text-sm font-medium text-white">{{ $category->name }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-4">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-carbon-700 text-carbon-300 text-sm font-medium">
                                        {{ $category->cars_count }} voiture(s)
                                    </span>
                                </td>
                                <td class="px-4 py-4">
                                    <button
                                        wire:click="toggleActive({{ $category->id }})"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium transition-colors
                                            @if($category->is_active) bg-status-success/20 text-status-success hover:bg-status-success/30
                                            @else bg-carbon-700 text-carbon-400 hover:bg-carbon-600 hover:text-carbon-300
                                            @endif
                                        "
                                    >
                                        <span class="w-2 h-2 rounded-full {{ $category->is_active ? 'bg-status-success' : 'bg-carbon-500' }}"></span>
                                        {{ $category->is_active ? 'Active' : 'Inactive' }}
                                    </button>
                                </td>
                                <td class="px-4 py-4">
                                    <div class="flex items-center justify-end gap-2">
                                        <button
                                            wire:click="openEditModal({{ $category->id }})"
                                            class="p-2 rounded-lg text-racing-red-500 hover:bg-racing-red-500/20 transition-colors"
                                            title="Modifier"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </button>
                                        <button
                                            wire:click="delete({{ $category->id }})"
                                            wire:confirm="Êtes-vous sûr de vouloir supprimer cette catégorie ?{{ $category->cars_count > 0 ? ' Elle contient '.$category->cars_count.' voiture(s) !' : '' }}"
                                            class="p-2 rounded-lg transition-colors {{ $category->cars_count > 0 ? 'text-carbon-500 hover:bg-carbon-700 cursor-not-allowed' : 'text-status-danger hover:bg-status-danger/20' }}"
                                            title="{{ $category->cars_count > 0 ? 'Suppression impossible : '.$category->cars_count.' voiture(s) associée(s)' : 'Supprimer' }}"
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

            @if($categories->hasPages())
                <div class="mt-6 pt-6 border-t border-carbon-700">
                    {{ $categories->links() }}
                </div>
            @endif
        @endif
    </x-racing.card>

    {{-- Mobile Cards View --}}
    <div class="md:hidden space-y-4">
        @if($categories->isEmpty())
            <x-racing.card>
                <x-racing.empty-state
                    title="Aucune catégorie"
                    description="Créez votre première catégorie de voiture."
                    icon='<svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>'
                />
            </x-racing.card>
        @else
            @foreach($categories as $category)
                <x-racing.card class="!p-0 overflow-hidden">
                    {{-- Card Header --}}
                    <div class="p-4 border-b border-carbon-700 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg {{ $category->is_active ? 'bg-racing-red-500/20' : 'bg-carbon-700' }} flex items-center justify-center">
                                <svg class="w-5 h-5 {{ $category->is_active ? 'text-racing-red-500' : 'text-carbon-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-semibold text-white">{{ $category->name }}</h3>
                                <p class="text-xs text-carbon-400">Ordre : {{ $category->sort_order }}</p>
                            </div>
                        </div>
                        <button
                            wire:click="toggleActive({{ $category->id }})"
                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium transition-colors
                                @if($category->is_active) bg-status-success/20 text-status-success
                                @else bg-carbon-700 text-carbon-400
                                @endif
                            "
                        >
                            <span class="w-2 h-2 rounded-full {{ $category->is_active ? 'bg-status-success' : 'bg-carbon-500' }}"></span>
                            {{ $category->is_active ? 'Active' : 'Inactive' }}
                        </button>
                    </div>

                    {{-- Card Content --}}
                    <div class="p-4 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-carbon-400">Voitures</span>
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-carbon-700 text-carbon-300 text-sm font-medium">
                                {{ $category->cars_count }} voiture(s)
                            </span>
                        </div>

                        {{-- Order Controls --}}
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-carbon-400">Réorganiser</span>
                            <div class="flex items-center gap-1">
                                <button
                                    wire:click="moveUp({{ $category->id }})"
                                    class="p-1.5 rounded text-carbon-400 hover:text-white hover:bg-carbon-700 transition-colors disabled:opacity-30"
                                    @if($loop->first) disabled @endif
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                                    </svg>
                                </button>
                                <button
                                    wire:click="moveDown({{ $category->id }})"
                                    class="p-1.5 rounded text-carbon-400 hover:text-white hover:bg-carbon-700 transition-colors disabled:opacity-30"
                                    @if($loop->last) disabled @endif
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Card Footer --}}
                    <div class="p-4 bg-carbon-900/30 border-t border-carbon-700 flex justify-end gap-2">
                        <x-racing.button
                            wire:click="openEditModal({{ $category->id }})"
                            variant="ghost"
                            size="sm"
                        >
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Modifier
                        </x-racing.button>
                        <x-racing.button
                            wire:click="delete({{ $category->id }})"
                            wire:confirm="Êtes-vous sûr de vouloir supprimer cette catégorie ?{{ $category->cars_count > 0 ? ' Elle contient '.$category->cars_count.' voiture(s) !' : '' }}"
                            variant="{{ $category->cars_count > 0 ? 'ghost' : 'danger' }}"
                            size="sm"
                            class="{{ $category->cars_count > 0 ? 'opacity-50 cursor-not-allowed' : '' }}"
                        >
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Supprimer
                        </x-racing.button>
                    </div>
                </x-racing.card>
            @endforeach

            @if($categories->hasPages())
                <div class="mt-4">
                    {{ $categories->links() }}
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
                                {{ $editingId ? 'Modifier la catégorie' : 'Nouvelle catégorie' }}
                            </h3>
                        </div>

                        <div class="px-6 py-4 space-y-4">
                            <x-racing.form.input
                                wire:model="name"
                                label="Nom de la catégorie"
                                placeholder="Ex: GT, TOURING, PROTO..."
                                required
                                :error="$errors->first('name')"
                            />

                            <x-racing.form.input
                                wire:model="sortOrder"
                                type="number"
                                label="Ordre d'affichage"
                                min="0"
                                :error="$errors->first('sortOrder')"
                            />

                            <x-racing.form.toggle
                                wire:model="isActive"
                                label="Catégorie active"
                                description="Les catégories inactives ne sont pas visibles lors de l'inscription"
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
</div>
