<div class="space-y-6">
    {{-- Racing Header --}}
    <div class="relative overflow-hidden rounded-xl bg-racing-gradient-subtle border border-carbon-700/50 p-6">
        <div class="absolute top-0 right-0 w-32 h-32 opacity-5">
            <svg viewBox="0 0 100 100" fill="currentColor" class="text-racing-red-500">
                <path d="M50 5L90 25v50L50 95 10 75V25L50 5z"/>
            </svg>
        </div>
        <div class="relative flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-white flex items-center gap-3">
                    <div class="p-2 bg-racing-red-500/20 rounded-lg">
                        <svg class="w-6 h-6 text-racing-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                    Gestion des Utilisateurs
                </h1>
                <p class="mt-1 text-carbon-400">Gérez les utilisateurs et leurs rôles</p>
            </div>
            <x-racing.button wire:click="openCreateModal">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Ajouter un utilisateur
            </x-racing.button>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
        <x-racing.alert type="success">{{ session('success') }}</x-racing.alert>
    @endif

    @if(session('error'))
        <x-racing.alert type="danger">{{ session('error') }}</x-racing.alert>
    @endif

    {{-- Filtres --}}
    <x-racing.card>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <x-racing.form.input
                label="Rechercher"
                wire:model.live.debounce.300ms="search"
                id="search"
                placeholder="Nom, email, licence..."
            />

            <x-racing.form.select
                label="Rôle"
                wire:model.live="roleFilter"
                id="roleFilter"
            >
                <option value="">Tous les rôles</option>
                @foreach($roles as $role)
                    <option value="{{ $role->name }}">{{ $role->name }}</option>
                @endforeach
            </x-racing.form.select>

            <x-racing.form.select
                label="Statut"
                wire:model.live="statusFilter"
                id="statusFilter"
            >
                <option value="active">Actifs</option>
                <option value="deleted">Supprimés</option>
                <option value="all">Tous</option>
            </x-racing.form.select>

            <div class="flex items-end">
                <span class="text-sm text-carbon-400">
                    {{ $users->total() }} utilisateur(s) trouvé(s)
                </span>
            </div>
        </div>
    </x-racing.card>

    {{-- Table --}}
    <x-racing.card noPadding>
        {{-- Version Desktop --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="min-w-full divide-y divide-carbon-700/50">
                <thead class="bg-carbon-800/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-carbon-400 uppercase tracking-wider">Utilisateur</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-carbon-400 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-carbon-400 uppercase tracking-wider">Profil Pilote</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-carbon-400 uppercase tracking-wider">Rôle</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-carbon-400 uppercase tracking-wider">Inscrit le</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-carbon-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-carbon-700/50">
                    @forelse($users as $user)
                        <tr class="hover:bg-carbon-800/30 transition-colors {{ $user->trashed() ? 'bg-status-danger/5' : '' }}">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        @if($user->pilot && $user->pilot->photo_path)
                                            <img class="h-10 w-10 rounded-full object-cover border-2 border-carbon-600" src="{{ Storage::url($user->pilot->photo_path) }}" alt="{{ $user->name }}">
                                        @else
                                            <span class="inline-flex items-center justify-center h-10 w-10 rounded-full bg-racing-red-500/20 border-2 border-racing-red-500/30">
                                                <span class="text-sm font-medium text-racing-red-400">
                                                    {{ strtoupper(substr($user->name, 0, 2)) }}
                                                </span>
                                            </span>
                                        @endif
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-white flex items-center gap-2">
                                            {{ $user->name }}
                                            @if($user->trashed())
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-status-danger/20 text-status-danger border border-status-danger/30">
                                                    Supprimé
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-carbon-400">
                                {{ $user->email }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($user->pilot)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-status-success/20 text-status-success border border-status-success/30">
                                        {{ $user->pilot->fullName }}
                                    </span>
                                @else
                                    <span class="text-sm text-carbon-500">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-wrap gap-1">
                                    @foreach($user->roles as $role)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $user->trashed() ? 'bg-carbon-700 text-carbon-400' : 'bg-racing-red-500/20 text-racing-red-400 border border-racing-red-500/30' }}">
                                            {{ $role->name }}
                                        </span>
                                    @endforeach
                                    @if($user->roles->isEmpty())
                                        <span class="text-xs text-carbon-500">Aucun rôle</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-carbon-400">
                                {{ $user->created_at->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <div class="flex justify-end space-x-1">
                                    {{-- View button --}}
                                    <button
                                        wire:click="viewUser({{ $user->id }})"
                                        class="p-2 text-carbon-400 hover:text-status-info hover:bg-status-info/10 rounded-lg transition-colors"
                                        title="Voir les détails"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </button>

                                    @if(!$user->trashed())
                                        {{-- Edit user button --}}
                                        <button
                                            wire:click="editUser({{ $user->id }})"
                                            class="p-2 text-carbon-400 hover:text-checkered-yellow-500 hover:bg-checkered-yellow-500/10 rounded-lg transition-colors"
                                            title="Modifier l'utilisateur"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>

                                        {{-- Edit pilot button (if pilot exists) --}}
                                        @if($user->pilot)
                                            <button
                                                wire:click="editPilot({{ $user->pilot->id }})"
                                                class="p-2 text-carbon-400 hover:text-status-success hover:bg-status-success/10 rounded-lg transition-colors"
                                                title="Modifier le profil pilote"
                                            >
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                </svg>
                                            </button>
                                        @endif

                                        {{-- Delete button --}}
                                        <button
                                            wire:click="confirmDelete({{ $user->id }})"
                                            class="p-2 text-carbon-400 hover:text-status-danger hover:bg-status-danger/10 rounded-lg transition-colors"
                                            title="Supprimer"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    @else
                                        {{-- Restore button --}}
                                        <button
                                            wire:click="restoreUser({{ $user->id }})"
                                            class="p-2 text-carbon-400 hover:text-status-success hover:bg-status-success/10 rounded-lg transition-colors"
                                            title="Restaurer"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12">
                                <x-racing.empty-state
                                    icon='<svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>'
                                    title="Aucun utilisateur trouvé"
                                    description="Modifiez vos filtres ou ajoutez un nouvel utilisateur."
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Version Mobile (Cards) --}}
        <div class="md:hidden p-4 space-y-4">
            @forelse($users as $user)
                <div class="bg-carbon-800/50 rounded-xl border border-carbon-700 overflow-hidden {{ $user->trashed() ? 'border-status-danger/30' : '' }}">
                    {{-- Header de la carte --}}
                    <div class="p-4 bg-carbon-800 border-b border-carbon-700 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            @if($user->pilot && $user->pilot->photo_path)
                                <img class="h-10 w-10 rounded-full object-cover border-2 border-carbon-600" src="{{ Storage::url($user->pilot->photo_path) }}" alt="{{ $user->name }}">
                            @else
                                <span class="inline-flex items-center justify-center h-10 w-10 rounded-full bg-racing-red-500/20 border-2 border-racing-red-500/30">
                                    <span class="text-sm font-medium text-racing-red-400">
                                        {{ strtoupper(substr($user->name, 0, 2)) }}
                                    </span>
                                </span>
                            @endif
                            <div>
                                <div class="text-sm font-medium text-white flex items-center gap-2">
                                    {{ $user->name }}
                                </div>
                                <div class="text-xs text-carbon-400 truncate max-w-[150px]">{{ $user->email }}</div>
                            </div>
                        </div>
                        @if($user->trashed())
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-status-danger/20 text-status-danger border border-status-danger/30">
                                Supprimé
                            </span>
                        @endif
                    </div>

                    {{-- Contenu --}}
                    <div class="p-4 space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-carbon-400 uppercase tracking-wider">Profil Pilote</span>
                            @if($user->pilot)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-status-success/20 text-status-success border border-status-success/30">
                                    {{ $user->pilot->fullName }}
                                </span>
                            @else
                                <span class="text-sm text-carbon-500">-</span>
                            @endif
                        </div>
                        <div class="flex justify-between items-start">
                            <span class="text-xs text-carbon-400 uppercase tracking-wider">Rôles</span>
                            <div class="flex flex-wrap gap-1 justify-end max-w-[60%]">
                                @foreach($user->roles as $role)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $user->trashed() ? 'bg-carbon-700 text-carbon-400' : 'bg-racing-red-500/20 text-racing-red-400 border border-racing-red-500/30' }}">
                                        {{ $role->name }}
                                    </span>
                                @endforeach
                                @if($user->roles->isEmpty())
                                    <span class="text-xs text-carbon-500">Aucun rôle</span>
                                @endif
                            </div>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-carbon-400 uppercase tracking-wider">Inscrit le</span>
                            <span class="text-sm text-carbon-300">{{ $user->created_at->format('d/m/Y') }}</span>
                        </div>
                    </div>

                    {{-- Footer avec actions --}}
                    <div class="px-4 py-3 bg-carbon-800/50 border-t border-carbon-700">
                        <div class="flex items-center justify-center gap-2">
                            {{-- View button --}}
                            <button
                                wire:click="viewUser({{ $user->id }})"
                                class="flex-1 flex items-center justify-center gap-1.5 py-2 rounded-lg text-sm font-medium text-status-info hover:bg-status-info/10 transition-colors"
                                title="Voir les détails"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                Voir
                            </button>

                            @if(!$user->trashed())
                                {{-- Edit user button --}}
                                <button
                                    wire:click="editUser({{ $user->id }})"
                                    class="flex-1 flex items-center justify-center gap-1.5 py-2 rounded-lg text-sm font-medium text-checkered-yellow-500 hover:bg-checkered-yellow-500/10 transition-colors"
                                    title="Modifier l'utilisateur"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                    Modifier
                                </button>

                                {{-- Edit pilot button (if pilot exists) --}}
                                @if($user->pilot)
                                    <button
                                        wire:click="editPilot({{ $user->pilot->id }})"
                                        class="p-2 rounded-lg text-status-success hover:bg-status-success/10 transition-colors"
                                        title="Modifier le profil pilote"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                    </button>
                                @endif

                                {{-- Delete button --}}
                                <button
                                    wire:click="confirmDelete({{ $user->id }})"
                                    class="p-2 rounded-lg text-status-danger hover:bg-status-danger/10 transition-colors"
                                    title="Supprimer"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            @else
                                {{-- Restore button --}}
                                <button
                                    wire:click="restoreUser({{ $user->id }})"
                                    class="flex-1 flex items-center justify-center gap-1.5 py-2 rounded-lg text-sm font-medium text-status-success hover:bg-status-success/10 transition-colors"
                                    title="Restaurer"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                    Restaurer
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <x-racing.empty-state
                    icon='<svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>'
                    title="Aucun utilisateur trouvé"
                    description="Modifiez vos filtres ou ajoutez un nouvel utilisateur."
                />
            @endforelse
        </div>

        <div class="px-6 py-4 border-t border-carbon-700/50">
            {{ $users->links() }}
        </div>
    </x-racing.card>

    {{-- View User Modal --}}
    @if($showViewModal && $this->viewingUser)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center">
                <div class="fixed inset-0 bg-carbon-900/80 backdrop-blur-sm transition-opacity" wire:click="closeViewModal"></div>

                <div class="relative bg-carbon-800 border border-carbon-700 rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:max-w-2xl sm:w-full">
                    <div class="px-6 py-4 border-b border-carbon-700 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                            <svg class="w-5 h-5 text-racing-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            Détails de l'utilisateur
                        </h3>
                        <button wire:click="closeViewModal" class="p-2 text-carbon-400 hover:text-white hover:bg-carbon-700 rounded-lg transition-colors">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div class="px-6 py-4 max-h-[70vh] overflow-y-auto">
                        {{-- User Info --}}
                        <div class="flex items-center mb-6">
                            @if($this->viewingUser->pilot && $this->viewingUser->pilot->photo_path)
                                <img class="h-20 w-20 rounded-full object-cover border-4 border-carbon-600" src="{{ Storage::url($this->viewingUser->pilot->photo_path) }}" alt="">
                            @else
                                <span class="inline-flex items-center justify-center h-20 w-20 rounded-full bg-racing-red-500/20 border-4 border-racing-red-500/30">
                                    <span class="text-2xl font-bold text-racing-red-400">{{ strtoupper(substr($this->viewingUser->name, 0, 2)) }}</span>
                                </span>
                            @endif
                            <div class="ml-4">
                                <h4 class="text-xl font-bold text-white">{{ $this->viewingUser->name }}</h4>
                                <p class="text-carbon-400">{{ $this->viewingUser->email }}</p>
                                <div class="mt-2 flex flex-wrap gap-1">
                                    @foreach($this->viewingUser->roles as $role)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-racing-red-500/20 text-racing-red-400 border border-racing-red-500/30">
                                            {{ $role->name }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4 mb-6">
                            <div class="p-3 bg-carbon-800/50 rounded-lg border border-carbon-700/50">
                                <span class="text-sm text-carbon-500">Inscrit le</span>
                                <p class="font-medium text-white">{{ $this->viewingUser->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                            <div class="p-3 bg-carbon-800/50 rounded-lg border border-carbon-700/50">
                                <span class="text-sm text-carbon-500">Dernière mise à jour</span>
                                <p class="font-medium text-white">{{ $this->viewingUser->updated_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>

                        {{-- Pilot Info --}}
                        @if($this->viewingUser->pilot)
                            <div class="border-t border-carbon-700 pt-4">
                                <h5 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-checkered-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    Profil Pilote
                                </h5>

                                <div class="grid grid-cols-2 gap-4">
                                    <div class="p-3 bg-carbon-800/50 rounded-lg border border-carbon-700/50">
                                        <span class="text-sm text-carbon-500">Prénom</span>
                                        <p class="font-medium text-white">{{ $this->viewingUser->pilot->first_name }}</p>
                                    </div>
                                    <div class="p-3 bg-carbon-800/50 rounded-lg border border-carbon-700/50">
                                        <span class="text-sm text-carbon-500">Nom</span>
                                        <p class="font-medium text-white">{{ $this->viewingUser->pilot->last_name }}</p>
                                    </div>
                                    <div class="p-3 bg-carbon-800/50 rounded-lg border border-carbon-700/50">
                                        <span class="text-sm text-carbon-500">N° de licence</span>
                                        <p class="font-medium text-white">{{ $this->viewingUser->pilot->license_number ?? '-' }}</p>
                                    </div>
                                    <div class="p-3 bg-carbon-800/50 rounded-lg border border-carbon-700/50">
                                        <span class="text-sm text-carbon-500">Téléphone</span>
                                        <p class="font-medium text-white">{{ $this->viewingUser->pilot->phone ?? '-' }}</p>
                                    </div>
                                    <div class="p-3 bg-carbon-800/50 rounded-lg border border-carbon-700/50">
                                        <span class="text-sm text-carbon-500">Date de naissance</span>
                                        <p class="font-medium text-white">{{ $this->viewingUser->pilot->birth_date?->format('d/m/Y') ?? '-' }}</p>
                                    </div>
                                    <div class="p-3 bg-carbon-800/50 rounded-lg border border-carbon-700/50">
                                        <span class="text-sm text-carbon-500">Lieu de naissance</span>
                                        <p class="font-medium text-white">{{ $this->viewingUser->pilot->birth_place ?? '-' }}</p>
                                    </div>
                                    <div class="col-span-2 p-3 bg-carbon-800/50 rounded-lg border border-carbon-700/50">
                                        <span class="text-sm text-carbon-500">Adresse</span>
                                        <p class="font-medium text-white">
                                            {{ $this->viewingUser->pilot->address ?? '' }}
                                            @if($this->viewingUser->pilot->postal_code || $this->viewingUser->pilot->city)
                                                <br>{{ $this->viewingUser->pilot->postal_code }} {{ $this->viewingUser->pilot->city }}
                                            @endif
                                            @if(!$this->viewingUser->pilot->address && !$this->viewingUser->pilot->city)
                                                -
                                            @endif
                                        </p>
                                    </div>
                                    <div class="p-3 bg-carbon-800/50 rounded-lg border border-carbon-700/50">
                                        <span class="text-sm text-carbon-500">Contact urgence</span>
                                        <p class="font-medium text-white">{{ $this->viewingUser->pilot->emergency_contact_name ?? '-' }}</p>
                                    </div>
                                    <div class="p-3 bg-carbon-800/50 rounded-lg border border-carbon-700/50">
                                        <span class="text-sm text-carbon-500">Tél. urgence</span>
                                        <p class="font-medium text-white">{{ $this->viewingUser->pilot->emergency_contact_phone ?? '-' }}</p>
                                    </div>
                                    <div class="p-3 bg-carbon-800/50 rounded-lg border border-carbon-700/50">
                                        <span class="text-sm text-carbon-500">Certificat médical</span>
                                        <p class="font-medium text-white">{{ $this->viewingUser->pilot->medical_certificate_date?->format('d/m/Y') ?? '-' }}</p>
                                    </div>
                                    <div class="p-3 bg-carbon-800/50 rounded-lg border border-carbon-700/50">
                                        <span class="text-sm text-carbon-500">Mineur</span>
                                        <p class="font-medium text-white">{{ $this->viewingUser->pilot->is_minor ? 'Oui' : 'Non' }}</p>
                                    </div>
                                </div>

                                @if($this->viewingUser->pilot->is_minor)
                                    <div class="mt-4 p-4 bg-status-warning/10 rounded-xl border border-status-warning/30">
                                        <h6 class="font-medium text-status-warning mb-2 flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                            </svg>
                                            Tuteur légal
                                        </h6>
                                        <div class="grid grid-cols-2 gap-2 text-sm">
                                            <div>
                                                <span class="text-status-warning/70">Prénom:</span>
                                                <span class="text-white ml-1">{{ $this->viewingUser->pilot->guardian_first_name ?? '-' }}</span>
                                            </div>
                                            <div>
                                                <span class="text-status-warning/70">Nom:</span>
                                                <span class="text-white ml-1">{{ $this->viewingUser->pilot->guardian_last_name ?? '-' }}</span>
                                            </div>
                                            <div class="col-span-2">
                                                <span class="text-status-warning/70">Licence:</span>
                                                <span class="text-white ml-1">{{ $this->viewingUser->pilot->guardian_license_number ?? '-' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                @if($this->viewingUser->pilot->notes)
                                    <div class="mt-4 p-3 bg-carbon-800/50 rounded-lg border border-carbon-700/50">
                                        <span class="text-sm text-carbon-500">Notes</span>
                                        <p class="font-medium text-white">{{ $this->viewingUser->pilot->notes }}</p>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>

                    <div class="px-6 py-4 border-t border-carbon-700 flex justify-end">
                        <x-racing.button wire:click="closeViewModal" variant="secondary">
                            Fermer
                        </x-racing.button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Edit User Modal --}}
    @if($showEditModal)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center">
                <div class="fixed inset-0 bg-carbon-900/80 backdrop-blur-sm transition-opacity" wire:click="closeEditModal"></div>

                <div class="relative bg-carbon-800 border border-carbon-700 rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:max-w-lg sm:w-full">
                    <form wire:submit="updateUser">
                        <div class="px-6 py-4 border-b border-carbon-700">
                            <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                                <svg class="w-5 h-5 text-checkered-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                Modifier l'utilisateur
                            </h3>
                        </div>

                        <div class="px-6 py-4 space-y-4">
                            <x-racing.form.input
                                label="Nom"
                                wire:model="editName"
                                :error="$errors->first('editName')"
                            />

                            <x-racing.form.input
                                type="email"
                                label="Email"
                                wire:model="editEmail"
                                :error="$errors->first('editEmail')"
                            />

                            <div>
                                <label class="block text-sm font-medium text-carbon-300 mb-2">Rôles *</label>
                                <div class="space-y-2 max-h-48 overflow-y-auto bg-carbon-800/50 border border-carbon-700/50 rounded-lg p-3">
                                    @foreach($roles as $role)
                                        <label class="flex items-center cursor-pointer hover:bg-carbon-700/50 p-2 rounded-lg transition-colors">
                                            <input
                                                type="checkbox"
                                                wire:model="editRoles"
                                                value="{{ $role->name }}"
                                                class="h-4 w-4 rounded bg-carbon-800 border-carbon-600 text-racing-red-500 focus:ring-racing-red-500 focus:ring-offset-carbon-900"
                                            >
                                            <span class="ml-2 text-sm text-carbon-300">{{ $role->name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                                @error('editRoles')
                                    <p class="mt-1 text-sm text-status-danger">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="px-6 py-4 border-t border-carbon-700 flex justify-end space-x-3">
                            <x-racing.button wire:click="closeEditModal" type="button" variant="secondary">
                                Annuler
                            </x-racing.button>
                            <x-racing.button type="submit">
                                Enregistrer
                            </x-racing.button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Edit Pilot Modal --}}
    @if($showEditPilotModal)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center">
                <div class="fixed inset-0 bg-carbon-900/80 backdrop-blur-sm transition-opacity" wire:click="closeEditPilotModal"></div>

                <div class="relative bg-carbon-800 border border-carbon-700 rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:max-w-2xl sm:w-full">
                    <form wire:submit="updatePilot">
                        <div class="px-6 py-4 border-b border-carbon-700">
                            <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                                <svg class="w-5 h-5 text-status-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                Modifier le profil pilote
                            </h3>
                        </div>

                        <div class="px-6 py-4 max-h-[60vh] overflow-y-auto">
                            <div class="grid grid-cols-2 gap-4">
                                <x-racing.form.input
                                    label="Prénom"
                                    wire:model="pilotFirstName"
                                    required
                                    :error="$errors->first('pilotFirstName')"
                                />

                                <x-racing.form.input
                                    label="Nom"
                                    wire:model="pilotLastName"
                                    required
                                    :error="$errors->first('pilotLastName')"
                                />

                                <x-racing.form.input
                                    type="tel"
                                    label="Téléphone"
                                    wire:model="pilotPhone"
                                />

                                <x-racing.form.input
                                    label="N° de licence"
                                    wire:model="pilotLicenseNumber"
                                />

                                <x-racing.form.input
                                    type="date"
                                    label="Date de naissance"
                                    wire:model="pilotBirthDate"
                                />

                                <x-racing.form.input
                                    label="Lieu de naissance"
                                    wire:model="pilotBirthPlace"
                                />

                                <div class="col-span-2">
                                    <x-racing.form.input
                                        label="Adresse"
                                        wire:model="pilotAddress"
                                    />
                                </div>

                                <x-racing.form.input
                                    label="Code postal"
                                    wire:model="pilotPostalCode"
                                />

                                <x-racing.form.input
                                    label="Ville"
                                    wire:model="pilotCity"
                                />

                                <x-racing.form.input
                                    label="Contact d'urgence"
                                    wire:model="pilotEmergencyName"
                                />

                                <x-racing.form.input
                                    type="tel"
                                    label="Tél. urgence"
                                    wire:model="pilotEmergencyPhone"
                                />
                            </div>
                        </div>

                        <div class="px-6 py-4 border-t border-carbon-700 flex justify-end space-x-3">
                            <x-racing.button wire:click="closeEditPilotModal" type="button" variant="secondary">
                                Annuler
                            </x-racing.button>
                            <x-racing.button type="submit">
                                Enregistrer
                            </x-racing.button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Delete Confirmation Modal --}}
    @if($showDeleteConfirmation && $this->deletingUser)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center">
                <div class="fixed inset-0 bg-carbon-900/80 backdrop-blur-sm transition-opacity" wire:click="closeDeleteConfirmation"></div>

                <div class="relative bg-carbon-800 border border-carbon-700 rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:max-w-lg sm:w-full">
                    <div class="px-6 py-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-status-danger/20 border border-status-danger/30">
                                <svg class="h-6 w-6 text-status-danger" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-semibold text-white">Supprimer l'utilisateur</h3>
                                <p class="mt-2 text-sm text-carbon-400">
                                    Êtes-vous sûr de vouloir supprimer l'utilisateur <strong class="text-white">{{ $this->deletingUser->name }}</strong> ?
                                    Cette action est réversible (soft delete).
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="px-6 py-4 border-t border-carbon-700 flex justify-end space-x-3">
                        <x-racing.button wire:click="closeDeleteConfirmation" type="button" variant="secondary">
                            Annuler
                        </x-racing.button>
                        <x-racing.button wire:click="deleteUser" type="button" variant="danger">
                            Supprimer
                        </x-racing.button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Create User Modal --}}
    @if($showCreateModal)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center">
                <div class="fixed inset-0 bg-carbon-900/80 backdrop-blur-sm transition-opacity" wire:click="closeCreateModal"></div>

                <div class="relative bg-carbon-800 border border-carbon-700 rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:max-w-lg sm:w-full">
                    <form wire:submit="createUser">
                        <div class="px-6 py-4 border-b border-carbon-700 flex items-center gap-3">
                            <div class="flex-shrink-0 flex items-center justify-center h-10 w-10 rounded-full bg-racing-red-500/20 border border-racing-red-500/30">
                                <svg class="h-5 w-5 text-racing-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-white">Ajouter un utilisateur</h3>
                        </div>

                        <div class="px-6 py-4 space-y-4">
                            <p class="text-sm text-carbon-400">
                                Créez un compte pour un membre du staff ou un contrôleur technique.
                            </p>

                            <x-racing.form.input
                                label="Nom complet"
                                wire:model="createName"
                                required
                                placeholder="Jean Dupont"
                                :error="$errors->first('createName')"
                            />

                            <x-racing.form.input
                                type="email"
                                label="Email"
                                wire:model="createEmail"
                                required
                                placeholder="jean.dupont@example.com"
                                :error="$errors->first('createEmail')"
                            />

                            <x-racing.form.input
                                type="password"
                                label="Mot de passe"
                                wire:model="createPassword"
                                required
                                placeholder="Minimum 8 caractères"
                                :error="$errors->first('createPassword')"
                            />

                            <div>
                                <label class="block text-sm font-medium text-carbon-300 mb-2">Rôle(s) *</label>
                                <div class="space-y-2 max-h-48 overflow-y-auto bg-carbon-800/50 border border-carbon-700/50 rounded-lg p-3">
                                    @foreach($roles as $role)
                                        @if($role->name !== 'PILOTE')
                                            <label class="flex items-center gap-2 cursor-pointer hover:bg-carbon-700/50 p-2 rounded-lg transition-colors">
                                                <input
                                                    type="checkbox"
                                                    wire:model="createRoles"
                                                    value="{{ $role->name }}"
                                                    class="h-4 w-4 rounded bg-carbon-800 border-carbon-600 text-racing-red-500 focus:ring-racing-red-500 focus:ring-offset-carbon-900"
                                                >
                                                <span class="text-sm text-carbon-300">{{ $role->name }}</span>
                                                @if($role->name === 'ADMIN')
                                                    <span class="text-xs text-status-danger">(accès total)</span>
                                                @elseif($role->name === 'CONTROLEUR_TECHNIQUE')
                                                    <span class="text-xs text-carbon-500">(vérification véhicules)</span>
                                                @elseif($role->name === 'STAFF_ADMINISTRATIF')
                                                    <span class="text-xs text-carbon-500">(gestion inscriptions)</span>
                                                @elseif($role->name === 'STAFF_ENTREE')
                                                    <span class="text-xs text-carbon-500">(accueil pilotes)</span>
                                                @elseif($role->name === 'STAFF_SONO')
                                                    <span class="text-xs text-carbon-500">(sonorisation)</span>
                                                @endif
                                            </label>
                                        @endif
                                    @endforeach
                                </div>
                                @error('createRoles')
                                    <p class="mt-1 text-sm text-status-danger">{{ $message }}</p>
                                @enderror
                                <p class="mt-2 text-xs text-carbon-500">
                                    Note : Les pilotes s'inscrivent eux-mêmes via le formulaire d'inscription public.
                                </p>
                            </div>
                        </div>

                        <div class="px-6 py-4 border-t border-carbon-700 flex justify-end space-x-3">
                            <x-racing.button wire:click="closeCreateModal" type="button" variant="secondary">
                                Annuler
                            </x-racing.button>
                            <x-racing.button type="submit">
                                Créer l'utilisateur
                            </x-racing.button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
