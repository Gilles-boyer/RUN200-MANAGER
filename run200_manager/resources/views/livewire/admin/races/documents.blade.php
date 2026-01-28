<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <nav class="flex items-center gap-2 text-sm mb-2">
                <a href="{{ route('admin.races.index') }}" class="text-carbon-400 hover:text-white transition-colors" wire:navigate>
                    Courses
                </a>
                <svg class="w-4 h-4 text-carbon-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
                <span class="text-carbon-300">{{ $race->name }}</span>
                <svg class="w-4 h-4 text-carbon-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
                <span class="text-white">Documents</span>
            </nav>
            <h1 class="text-2xl font-bold text-white flex items-center gap-3">
                <div class="p-2 bg-racing-red-500/20 rounded-lg">
                    <svg class="w-6 h-6 text-racing-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                Tableau d'affichage
            </h1>
            <p class="text-carbon-400 mt-1">
                {{ $race->race_date->format('d/m/Y') }} • {{ $race->location }}
            </p>
        </div>
        <div class="flex items-center gap-3">
            {{-- Lien vers page publique --}}
            <a href="{{ route('board.show', $race->slug) }}" target="_blank"
               class="inline-flex items-center gap-2 px-4 py-2 bg-carbon-700 hover:bg-carbon-600 text-white rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                </svg>
                Voir public
            </a>
            {{-- Bouton d'upload --}}
            <button wire:click="openUploadModal"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-racing-red-500 hover:bg-racing-red-600 text-white rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Ajouter un document
            </button>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if (session()->has('success'))
        <x-racing.alert type="success" :message="session('success')" />
    @endif

    @if (session()->has('error'))
        <x-racing.alert type="error" :message="session('error')" />
    @endif

    {{-- Indicateur de complétude --}}
    @if (count($missingRequired) > 0)
        <div class="bg-status-warning/10 border border-status-warning/30 rounded-lg p-4">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-status-warning flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <div>
                    <h3 class="font-semibold text-status-warning">Documents obligatoires manquants</h3>
                    <p class="text-sm text-carbon-300 mt-1">
                        Les documents suivants doivent être publiés pour compléter le tableau d'affichage :
                    </p>
                    <ul class="mt-2 space-y-1">
                        @foreach ($missingRequired as $categoryName)
                            <li class="text-sm text-carbon-400 flex items-center gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-status-warning"></span>
                                {{ $categoryName }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @else
        <div class="bg-status-success/10 border border-status-success/30 rounded-lg p-4">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-status-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="text-status-success font-medium">Tous les documents obligatoires sont publiés</span>
            </div>
        </div>
    @endif

    {{-- Liste des documents par catégorie --}}
    <div class="space-y-6">
        @forelse ($this->categories as $category)
            @php
                $categoryDocuments = $this->documents->get($category->id, collect());
            @endphp
            <x-racing.card>
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <h2 class="text-lg font-semibold text-white">{{ $category->name }}</h2>
                        @if ($category->is_required)
                            <span class="px-2 py-0.5 text-xs font-medium bg-status-warning/20 text-status-warning rounded-full">
                                Obligatoire
                            </span>
                        @endif
                        @if ($category->is_multiple)
                            <span class="px-2 py-0.5 text-xs font-medium bg-status-info/20 text-status-info rounded-full">
                                Multiple
                            </span>
                        @endif
                    </div>
                    @if ($category->is_multiple || $categoryDocuments->isEmpty())
                        <button wire:click="openUploadModal({{ $category->id }})"
                                class="text-sm text-racing-red-400 hover:text-racing-red-300 transition-colors flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Ajouter
                        </button>
                    @endif
                </div>

                @if ($categoryDocuments->isEmpty())
                    <div class="py-8 text-center">
                        <svg class="w-12 h-12 text-carbon-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <p class="text-carbon-500">Aucun document dans cette catégorie</p>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach ($categoryDocuments as $document)
                            <div class="flex items-center justify-between p-4 bg-carbon-800/50 rounded-lg border border-carbon-700/50">
                                <div class="flex items-center gap-4">
                                    {{-- Icône PDF --}}
                                    <div class="w-10 h-10 rounded-lg bg-red-500/20 flex items-center justify-center">
                                        <svg class="w-5 h-5 text-red-400" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6zm-1 2l5 5h-5V4zM8.5 12a.5.5 0 00-.5.5v3a.5.5 0 001 0V14h.5a1.5 1.5 0 000-3H8.5zm.5 1h.5a.5.5 0 010 1H9v-1zm3.5-1a.5.5 0 00-.5.5v3a.5.5 0 00.5.5h1a1.5 1.5 0 001.5-1.5v-1a1.5 1.5 0 00-1.5-1.5h-1zm.5 1h.5a.5.5 0 01.5.5v1a.5.5 0 01-.5.5h-.5v-2zm4-.5a.5.5 0 01.5.5v.5h1a.5.5 0 010 1h-1v1a.5.5 0 01-1 0v-3a.5.5 0 01.5-.5h1.5a.5.5 0 010 1H17z"/>
                                        </svg>
                                    </div>
                                    {{-- Infos document --}}
                                    <div>
                                        <h3 class="font-medium text-white">{{ $document->title }}</h3>
                                        <div class="flex items-center gap-3 mt-1 text-sm text-carbon-400">
                                            <span>v{{ $document->current_version }}</span>
                                            <span>•</span>
                                            <span>{{ $document->formattedFileSize }}</span>
                                            @if ($document->published_at)
                                                <span>•</span>
                                                <span>Publié le {{ $document->published_at->format('d/m/Y à H:i') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2">
                                    {{-- Badge statut --}}
                                    @if ($document->isPublished())
                                        <span class="px-2.5 py-1 text-xs font-semibold bg-status-success/20 text-status-success rounded-lg">
                                            Publié
                                        </span>
                                    @elseif ($document->isDraft())
                                        <span class="px-2.5 py-1 text-xs font-semibold bg-carbon-600 text-carbon-300 rounded-lg">
                                            Brouillon
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 text-xs font-semibold bg-carbon-700 text-carbon-400 rounded-lg">
                                            Archivé
                                        </span>
                                    @endif

                                    {{-- Actions --}}
                                    <div class="flex items-center gap-1">
                                        {{-- Voir versions --}}
                                        <button wire:click="showVersions({{ $document->id }})"
                                                class="p-2 rounded-lg text-carbon-400 hover:text-white hover:bg-carbon-700 transition-colors"
                                                title="Voir les versions">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </button>

                                        {{-- Publier/Dépublier --}}
                                        @if ($document->isDraft())
                                            <button wire:click="publishDocument({{ $document->id }})"
                                                    class="p-2 rounded-lg text-status-success hover:bg-status-success/20 transition-colors"
                                                    title="Publier">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                            </button>
                                        @elseif ($document->isPublished())
                                            <button wire:click="unpublishDocument({{ $document->id }})"
                                                    class="p-2 rounded-lg text-status-warning hover:bg-status-warning/20 transition-colors"
                                                    title="Retirer du tableau">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                                </svg>
                                            </button>
                                        @endif

                                        {{-- Nouvelle version --}}
                                        <button wire:click="openUploadModal({{ $category->id }})"
                                                class="p-2 rounded-lg text-carbon-400 hover:text-white hover:bg-carbon-700 transition-colors"
                                                title="Nouvelle version">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                            </svg>
                                        </button>

                                        {{-- Supprimer (brouillon uniquement) --}}
                                        @if ($document->canBeDeleted())
                                            <button wire:click="confirmDelete({{ $document->id }})"
                                                    class="p-2 rounded-lg text-status-danger hover:bg-status-danger/20 transition-colors"
                                                    title="Supprimer">
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
                @endif
            </x-racing.card>
        @empty
            <x-racing.empty-state
                title="Aucune catégorie de document"
                description="Les catégories de documents n'ont pas été configurées."
            />
        @endforelse
    </div>

    {{-- Modal Upload --}}
    @if ($showUploadModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                {{-- Backdrop --}}
                <div class="fixed inset-0 bg-carbon-900/75 transition-opacity z-40" wire:click="closeUploadModal"></div>

                {{-- Spacer pour centrage vertical --}}
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                {{-- Contenu modal --}}
                <div class="relative z-50 inline-block align-bottom bg-carbon-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-carbon-700">
                    <form wire:submit="uploadDocument">
                        <div class="px-6 py-4 border-b border-carbon-700">
                            <h3 class="text-lg font-semibold text-white">Ajouter un document</h3>
                        </div>

                        <div class="px-6 py-4 space-y-4">
                            {{-- Catégorie --}}
                            <div>
                                <label for="category" class="block text-sm font-medium text-carbon-300 mb-1">
                                    Catégorie <span class="text-racing-red-500">*</span>
                                </label>
                                <select wire:model.live="selectedCategoryId" id="category"
                                        class="w-full rounded-lg bg-carbon-900 border-carbon-600 text-white focus:border-racing-red-500 focus:ring-racing-red-500">
                                    <option value="">Sélectionner une catégorie</option>
                                    @foreach ($this->categories as $cat)
                                        <option value="{{ $cat->id }}">
                                            {{ $cat->name }}
                                            @if ($cat->is_required) (obligatoire) @endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('selectedCategoryId')
                                    <p class="mt-1 text-sm text-status-danger">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Titre --}}
                            <div>
                                <label for="title" class="block text-sm font-medium text-carbon-300 mb-1">
                                    Titre <span class="text-racing-red-500">*</span>
                                </label>
                                <input type="text" wire:model="documentTitle" id="title"
                                       class="w-full rounded-lg bg-carbon-900 border-carbon-600 text-white focus:border-racing-red-500 focus:ring-racing-red-500"
                                       placeholder="Ex: Règlement particulier 2026">
                                @error('documentTitle')
                                    <p class="mt-1 text-sm text-status-danger">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Description --}}
                            <div>
                                <label for="description" class="block text-sm font-medium text-carbon-300 mb-1">
                                    Description (optionnel)
                                </label>
                                <textarea wire:model="documentDescription" id="description" rows="2"
                                          class="w-full rounded-lg bg-carbon-900 border-carbon-600 text-white focus:border-racing-red-500 focus:ring-racing-red-500"
                                          placeholder="Notes ou commentaires..."></textarea>
                            </div>

                            {{-- Visibilité --}}
                            <div>
                                <label class="block text-sm font-medium text-carbon-300 mb-1">Visibilité</label>
                                <div class="flex gap-4">
                                    <label class="flex items-center gap-2">
                                        <input type="radio" wire:model="visibility" value="PUBLIC" class="text-racing-red-500 focus:ring-racing-red-500">
                                        <span class="text-white">Public</span>
                                    </label>
                                    <label class="flex items-center gap-2">
                                        <input type="radio" wire:model="visibility" value="REGISTERED_ONLY" class="text-racing-red-500 focus:ring-racing-red-500">
                                        <span class="text-white">Pilotes inscrits uniquement</span>
                                    </label>
                                </div>
                            </div>

                            {{-- Fichier --}}
                            <div>
                                <label class="block text-sm font-medium text-carbon-300 mb-1">
                                    Fichier PDF <span class="text-racing-red-500">*</span>
                                </label>
                                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-carbon-600 border-dashed rounded-lg hover:border-racing-red-500/50 transition-colors"
                                     x-data="{ isDragging: false }"
                                     x-on:dragover.prevent="isDragging = true"
                                     x-on:dragleave.prevent="isDragging = false"
                                     x-on:drop.prevent="isDragging = false"
                                     :class="{ 'border-racing-red-500 bg-racing-red-500/10': isDragging }">
                                    <div class="space-y-1 text-center">
                                        <svg class="mx-auto h-12 w-12 text-carbon-500" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                        <div class="flex text-sm text-carbon-400">
                                            <label for="file-upload" class="relative cursor-pointer rounded-md font-medium text-racing-red-400 hover:text-racing-red-300 focus-within:outline-none">
                                                <span>Sélectionner un fichier</span>
                                                <input id="file-upload" wire:model="uploadedFile" type="file" class="sr-only" accept=".pdf">
                                            </label>
                                            <p class="pl-1">ou glisser-déposer</p>
                                        </div>
                                        <p class="text-xs text-carbon-500">PDF uniquement, max 10 Mo</p>
                                    </div>
                                </div>
                                @if ($uploadedFile)
                                    <div class="mt-2 flex items-center gap-2 text-sm text-status-success">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        {{ $uploadedFile->getClientOriginalName() }}
                                    </div>
                                @endif
                                @error('uploadedFile')
                                    <p class="mt-1 text-sm text-status-danger">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="px-6 py-4 bg-carbon-900/50 flex justify-end gap-3">
                            <button type="button" wire:click="closeUploadModal"
                                    class="px-4 py-2 bg-carbon-700 hover:bg-carbon-600 text-white rounded-lg transition-colors">
                                Annuler
                            </button>
                            <button type="submit"
                                    class="px-4 py-2 bg-racing-red-500 hover:bg-racing-red-600 text-white rounded-lg transition-colors flex items-center gap-2"
                                    wire:loading.attr="disabled"
                                    wire:loading.class="opacity-50 cursor-not-allowed">
                                <span wire:loading wire:target="uploadDocument">
                                    <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </span>
                                <span wire:loading.remove wire:target="uploadDocument">Uploader</span>
                                <span wire:loading wire:target="uploadDocument">Upload en cours...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal Suppression --}}
    @if ($showDeleteModal && $selectedDocument)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                {{-- Backdrop --}}
                <div class="fixed inset-0 bg-carbon-900/75 transition-opacity z-40" wire:click="$set('showDeleteModal', false)"></div>

                {{-- Spacer --}}
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                {{-- Contenu modal --}}
                <div class="relative z-50 inline-block align-bottom bg-carbon-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-carbon-700">
                    <div class="px-6 py-4 border-b border-carbon-700">
                        <h3 class="text-lg font-semibold text-white">Confirmer la suppression</h3>
                    </div>

                    <div class="px-6 py-4">
                        <p class="text-carbon-300">
                            Êtes-vous sûr de vouloir supprimer le document <strong class="text-white">{{ $selectedDocument->title }}</strong> ?
                        </p>
                        <p class="text-sm text-carbon-500 mt-2">
                            Toutes les versions seront supprimées définitivement. Cette action est irréversible.
                        </p>
                    </div>

                    <div class="px-6 py-4 bg-carbon-900/50 flex justify-end gap-3">
                        <button type="button" wire:click="$set('showDeleteModal', false)"
                                class="px-4 py-2 bg-carbon-700 hover:bg-carbon-600 text-white rounded-lg transition-colors">
                            Annuler
                        </button>
                        <button type="button" wire:click="deleteDocument"
                                class="px-4 py-2 bg-status-danger hover:bg-status-danger/80 text-white rounded-lg transition-colors">
                            Supprimer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal Versions --}}
    @if ($showVersionsModal && $selectedDocument)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                {{-- Backdrop --}}
                <div class="fixed inset-0 bg-carbon-900/75 transition-opacity z-40" wire:click="closeVersionsModal"></div>

                {{-- Spacer --}}
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                {{-- Contenu modal --}}
                <div class="relative z-50 inline-block align-bottom bg-carbon-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl sm:w-full border border-carbon-700">
                    <div class="px-6 py-4 border-b border-carbon-700 flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-white">Historique des versions</h3>
                        <button wire:click="closeVersionsModal" class="text-carbon-400 hover:text-white">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <div class="px-6 py-4 max-h-96 overflow-y-auto">
                        <div class="space-y-3">
                            @foreach ($selectedDocument->versions as $version)
                                <div class="flex items-center justify-between p-3 bg-carbon-900/50 rounded-lg {{ $version->version === $selectedDocument->current_version ? 'ring-2 ring-racing-red-500/50' : '' }}">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-carbon-700 flex items-center justify-center text-sm font-medium text-white">
                                            v{{ $version->version }}
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-white">{{ $version->original_filename }}</p>
                                            <p class="text-xs text-carbon-400">
                                                {{ $version->formatted_file_size }} •
                                                Uploadé par {{ $version->uploader?->name ?? 'Inconnu' }} le
                                                {{ $version->created_at->format('d/m/Y à H:i') }}
                                            </p>
                                        </div>
                                    </div>
                                    @if ($version->version === $selectedDocument->current_version)
                                        <span class="px-2 py-1 text-xs font-medium bg-racing-red-500/20 text-racing-red-400 rounded">
                                            Actuelle
                                        </span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-carbon-900/50 flex justify-end">
                        <button type="button" wire:click="closeVersionsModal"
                                class="px-4 py-2 bg-carbon-700 hover:bg-carbon-600 text-white rounded-lg transition-colors">
                            Fermer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
