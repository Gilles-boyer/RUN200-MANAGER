<div class="space-y-6">
    {{-- Racing Header --}}
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-racing-gradient rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-white">Résultats : {{ $race->name }}</h1>
                <p class="text-gray-400 mt-1">
                    🏁 {{ $race->race_date->format('d/m/Y') }}
                    @if($race->season)
                        — {{ $race->season->name }}
                    @endif
                </p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            {{-- Status Badge --}}
            <span class="inline-flex items-center px-4 py-2 rounded-xl text-sm font-semibold
                @if($race->isPublished()) bg-status-success/20 text-status-success border border-status-success/30
                @elseif($race->isResultsReady()) bg-status-info/20 text-status-info border border-status-info/30
                @else bg-carbon-700 text-gray-400 border border-carbon-600
                @endif">
                @if($race->isPublished())
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    Publié
                @elseif($race->isResultsReady())
                    📋 Prêt à publier
                @else
                    {{ $race->status }}
                @endif
            </span>
        </div>
    </div>

    {{-- Messages --}}
    @if($successMessage)
        <x-racing.alert type="success">
            {{ $successMessage }}
        </x-racing.alert>
    @endif

    @if($errorMessage)
        <x-racing.alert type="danger">
            <span class="whitespace-pre-line">{{ $errorMessage }}</span>
        </x-racing.alert>
    @endif

    {{-- Action Buttons --}}
    <div class="flex flex-wrap gap-3">
        @if($this->canImport)
            <x-racing.button wire:click="openUploadModal" variant="secondary">
                <svg class="w-5 h-5 mr-2 -ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                </svg>
                Importer CSV
            </x-racing.button>
        @endif

        @if($this->canPublish)
            <button wire:click="confirmPublish"
                    class="inline-flex items-center px-5 py-2.5 bg-status-success text-white rounded-xl font-semibold hover:bg-status-success/80 transition-all duration-200 shadow-lg shadow-status-success/25">
                <svg class="w-5 h-5 mr-2 -ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Publier les résultats
            </button>
        @endif

        @if($this->canUnpublish)
            <button wire:click="confirmUnpublish"
                    class="inline-flex items-center px-5 py-2.5 bg-carbon-700 text-gray-300 border border-carbon-600 rounded-xl font-semibold hover:bg-carbon-600 transition-all duration-200">
                <svg class="w-5 h-5 mr-2 -ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                </svg>
                Dépublier
            </button>
        @endif
    </div>

    {{-- Results Table --}}
    <x-racing.card noPadding>
        <div class="px-6 py-4 border-b border-carbon-700/50">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                    🏆 Classement
                    <span class="text-sm font-normal text-gray-500">
                        ({{ $this->results->total() }} résultats)
                    </span>
                </h3>

                {{-- Search --}}
                <div class="relative">
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="searchQuery"
                        placeholder="Rechercher un pilote..."
                        class="block w-full sm:w-64 rounded-xl bg-carbon-700 border-carbon-600 text-white placeholder-gray-500 shadow-sm focus:border-racing-red-500 focus:ring-racing-red-500 text-sm pl-10"
                    >
                    <svg class="w-5 h-5 text-gray-500 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    @if($searchQuery)
                        <button
                            wire:click="$set('searchQuery', '')"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 hover:text-gray-300"
                        >
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                    @endif
                </div>
            </div>
        </div>

        @if($this->results->isEmpty())
            <x-racing.empty-state
                icon='<svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>'
                title="Aucun résultat"
                :description="$this->canImport ? 'Importez un fichier CSV pour ajouter les résultats.' : 'Les résultats n\'ont pas encore été importés.'"
            />
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="bg-carbon-800/50 border-b border-carbon-700/50">
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Pos</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Dossard</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Pilote</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Voiture</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Catégorie</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Temps</th>
                            <th scope="col" class="relative px-6 py-4">
                                <span class="sr-only">Actions</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-carbon-700/50">
                        @foreach($this->results as $result)
                            <tr class="hover:bg-carbon-700/30 transition-colors duration-150
                                @if($result->position <= 3) bg-checkered-yellow-500/5 @endif">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl font-black text-sm
                                        @if($result->position === 1) bg-gradient-to-br from-yellow-400 to-yellow-600 text-yellow-900 shadow-lg shadow-yellow-500/30
                                        @elseif($result->position === 2) bg-gradient-to-br from-gray-300 to-gray-500 text-gray-900 shadow-lg shadow-gray-400/30
                                        @elseif($result->position === 3) bg-gradient-to-br from-amber-500 to-amber-700 text-white shadow-lg shadow-amber-500/30
                                        @else bg-carbon-700 text-gray-400 border border-carbon-600
                                        @endif">
                                        {{ $result->position }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-lg font-bold text-racing-red-500">#{{ $result->bib }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-white font-medium">
                                    {{ $result->pilot_name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-400">
                                    {{ $result->car_description }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-400">
                                    {{ $result->category_name ?? '—' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="font-mono text-checkered-yellow-500 font-semibold">{{ $result->formatted_time }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    @if(!$race->isPublished())
                                        <button
                                            wire:click="deleteResult({{ $result->id }})"
                                            wire:confirm="Êtes-vous sûr de vouloir supprimer ce résultat ?"
                                            class="text-status-danger hover:text-status-danger/80 transition-colors text-sm font-medium"
                                        >
                                            Supprimer
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="px-6 py-4 border-t border-carbon-700/50">
                {{ $this->results->links() }}
            </div>
        @endif
    </x-racing.card>

    {{-- Import History --}}
    @if($this->imports->isNotEmpty())
        <x-racing.card noPadding>
            <div class="px-6 py-4 border-b border-carbon-700/50">
                <h3 class="text-lg font-semibold text-white">📁 Historique des imports</h3>
            </div>
            <ul class="divide-y divide-carbon-700/50">
                @foreach($this->imports as $import)
                    <li class="px-6 py-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold
                                    @if($import->isImported()) bg-status-success/20 text-status-success border border-status-success/30
                                    @elseif($import->isFailed()) bg-status-danger/20 text-status-danger border border-status-danger/30
                                    @else bg-status-warning/20 text-status-warning border border-status-warning/30
                                    @endif">
                                    {{ $import->status }}
                                </span>
                                <span class="text-sm text-white font-medium">
                                    {{ $import->original_filename }}
                                </span>
                            </div>
                            <div class="text-sm text-gray-500">
                                {{ $import->created_at->format('d/m/Y H:i') }}
                                @if($import->uploader)
                                    par <span class="text-gray-400">{{ $import->uploader->name }}</span>
                                @endif
                            </div>
                        </div>
                        @if($import->isFailed() && $import->errors)
                            <div class="mt-2 text-sm text-status-danger">
                                @foreach(array_slice($import->errors, 0, 3) as $error)
                                    <p>Ligne {{ $error['row'] }}: {{ $error['message'] }}</p>
                                @endforeach
                                @if(count($import->errors) > 3)
                                    <p class="text-gray-500">... et {{ count($import->errors) - 3 }} autres erreurs</p>
                                @endif
                            </div>
                        @endif
                        @if($import->isImported())
                            <p class="mt-2 text-sm text-gray-500">
                                ✅ {{ $import->row_count }} résultats importés
                            </p>
                        @endif
                    </li>
                @endforeach
            </ul>
        </x-racing.card>
    @endif

    {{-- Upload Modal --}}
    @if($showUploadModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-carbon-950/80 backdrop-blur-sm transition-opacity z-40" wire:click="closeUploadModal"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                <div class="relative z-50 inline-block align-bottom bg-carbon-800 rounded-2xl px-6 pt-6 pb-6 text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-carbon-700/50">
                    <div>
                        <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-xl bg-status-info/20 border border-status-info/30">
                            <svg class="h-7 w-7 text-status-info" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                        </div>
                        <div class="mt-4 text-center">
                            <h3 class="text-xl font-bold text-white" id="modal-title">
                                Importer les résultats
                            </h3>
                            <p class="mt-2 text-sm text-gray-400">
                                Sélectionnez un fichier CSV avec les colonnes : position, bib, pilote, voiture, catégorie, temps.
                            </p>
                        </div>
                    </div>

                    <div class="mt-6">
                        <label class="block">
                            <span class="sr-only">Choisir un fichier</span>
                            <input
                                type="file"
                                wire:model="csvFile"
                                accept=".csv,.txt"
                                class="block w-full text-sm text-gray-400
                                    file:mr-4 file:py-3 file:px-5
                                    file:rounded-xl file:border-0
                                    file:text-sm file:font-semibold
                                    file:bg-racing-gradient file:text-white
                                    hover:file:opacity-90 file:cursor-pointer file:transition-all"
                            >
                        </label>
                        @error('csvFile')
                            <p class="mt-2 text-sm text-status-danger">{{ $message }}</p>
                        @enderror

                        <div wire:loading wire:target="csvFile" class="mt-3 text-sm text-gray-400 flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Chargement du fichier...
                        </div>
                    </div>

                    <div class="mt-6 flex gap-3">
                        <button
                            wire:click="closeUploadModal"
                            type="button"
                            class="flex-1 px-4 py-3 bg-carbon-700 text-gray-300 rounded-xl font-semibold hover:bg-carbon-600 transition-all duration-200 border border-carbon-600"
                        >
                            Annuler
                        </button>
                        <button
                            wire:click="uploadCsv"
                            wire:loading.attr="disabled"
                            type="button"
                            class="flex-1 px-4 py-3 bg-racing-gradient text-white rounded-xl font-semibold hover:opacity-90 transition-all duration-200 disabled:opacity-50 shadow-lg shadow-racing-red-500/25"
                        >
                            <span wire:loading.remove wire:target="uploadCsv">📤 Importer</span>
                            <span wire:loading wire:target="uploadCsv">Import en cours...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Publish Confirmation Modal --}}
    @if($showPublishConfirmation)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-carbon-950/80 backdrop-blur-sm transition-opacity z-40" wire:click="cancelPublish"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                <div class="relative z-50 inline-block align-bottom bg-carbon-800 rounded-2xl px-6 pt-6 pb-6 text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-carbon-700/50">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-14 w-14 rounded-xl bg-status-success/20 border border-status-success/30 sm:mx-0">
                            <svg class="h-7 w-7 text-status-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-xl font-bold text-white">
                                Publier les résultats
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-400">
                                    Êtes-vous sûr de vouloir publier ces résultats ? Ils seront visibles par tous les pilotes.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-6 flex gap-3 sm:flex-row-reverse">
                        <button
                            wire:click="publishResults"
                            type="button"
                            class="flex-1 sm:flex-none px-6 py-3 bg-status-success text-white rounded-xl font-semibold hover:bg-status-success/80 transition-all duration-200 shadow-lg shadow-status-success/25"
                        >
                            ✅ Publier
                        </button>
                        <button
                            wire:click="cancelPublish"
                            type="button"
                            class="flex-1 sm:flex-none px-6 py-3 bg-carbon-700 text-gray-300 rounded-xl font-semibold hover:bg-carbon-600 transition-all duration-200 border border-carbon-600"
                        >
                            Annuler
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Unpublish Confirmation Modal --}}
    @if($showUnpublishConfirmation)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-carbon-950/80 backdrop-blur-sm transition-opacity z-40" wire:click="cancelUnpublish"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                <div class="relative z-50 inline-block align-bottom bg-carbon-800 rounded-2xl px-6 pt-6 pb-6 text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-carbon-700/50">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-14 w-14 rounded-xl bg-status-warning/20 border border-status-warning/30 sm:mx-0">
                            <svg class="h-7 w-7 text-status-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-xl font-bold text-white">
                                Dépublier les résultats
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-400">
                                    Les résultats ne seront plus visibles par les pilotes. Vous pourrez les republier après modification.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-6 flex gap-3 sm:flex-row-reverse">
                        <button
                            wire:click="unpublishResults"
                            type="button"
                            class="flex-1 sm:flex-none px-6 py-3 bg-status-warning text-white rounded-xl font-semibold hover:bg-status-warning/80 transition-all duration-200 shadow-lg shadow-status-warning/25"
                        >
                            ⚠️ Dépublier
                        </button>
                        <button
                            wire:click="cancelUnpublish"
                            type="button"
                            class="flex-1 sm:flex-none px-6 py-3 bg-carbon-700 text-gray-300 rounded-xl font-semibold hover:bg-carbon-600 transition-all duration-200 border border-carbon-600"
                        >
                            Annuler
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
