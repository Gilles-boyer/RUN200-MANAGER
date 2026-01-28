<div class="max-w-4xl mx-auto px-4 py-8">
    {{-- Header --}}
    <div class="text-center mb-8">
        <div class="inline-flex items-center gap-2 px-4 py-2 bg-racing-red-500/10 dark:bg-racing-red-500/20 rounded-full text-racing-red-600 dark:text-racing-red-400 mb-4">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <span class="text-sm font-semibold">Tableau d'affichage officiel</span>
        </div>
        <h1 class="text-3xl md:text-4xl font-bold text-carbon-900 dark:text-white mb-2">
            {{ $race->name }}
        </h1>
        <p class="text-lg text-carbon-600 dark:text-carbon-400">
            {{ $race->race_date->isoFormat('dddd D MMMM YYYY') }} • {{ $race->location }}
        </p>
    </div>

    {{-- Indicateur de complétude --}}
    @if (count($missingRequired) > 0)
        <div class="bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/30 rounded-xl p-4 mb-6">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <div>
                    <h3 class="font-semibold text-amber-700 dark:text-amber-400">Documents en cours de publication</h3>
                    <p class="text-sm text-amber-600 dark:text-amber-300/80 mt-1">
                        Certains documents obligatoires sont en attente de publication.
                    </p>
                </div>
            </div>
        </div>
    @endif

    {{-- Documents par catégorie --}}
    @if ($this->categories->isEmpty())
        <div class="bg-white dark:bg-carbon-800 rounded-xl border border-carbon-200 dark:border-carbon-700 p-12 text-center">
            <svg class="w-16 h-16 text-carbon-300 dark:text-carbon-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <h3 class="text-xl font-semibold text-carbon-700 dark:text-carbon-300 mb-2">Aucun document disponible</h3>
            <p class="text-carbon-500 dark:text-carbon-400">
                Les documents officiels de cette course n'ont pas encore été publiés.
            </p>
        </div>
    @else
        <div class="space-y-6">
            @foreach ($this->categories as $category)
                <div class="bg-white dark:bg-carbon-800 rounded-xl border border-carbon-200 dark:border-carbon-700 overflow-hidden">
                    {{-- Catégorie header --}}
                    <div class="px-5 py-4 bg-carbon-50 dark:bg-carbon-800/50 border-b border-carbon-200 dark:border-carbon-700">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-racing-red-500/10 dark:bg-racing-red-500/20 flex items-center justify-center">
                                <svg class="w-5 h-5 text-racing-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 19a2 2 0 01-2-2V7a2 2 0 012-2h4l2 2h4a2 2 0 012 2v1M5 19h14a2 2 0 002-2v-5a2 2 0 00-2-2H9a2 2 0 00-2 2v5a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <div>
                                <h2 class="font-semibold text-carbon-900 dark:text-white">{{ $category->name }}</h2>
                                @if ($category->description)
                                    <p class="text-sm text-carbon-500 dark:text-carbon-400">{{ $category->description }}</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Liste des documents --}}
                    <div class="divide-y divide-carbon-100 dark:divide-carbon-700">
                        @foreach ($this->getDocumentsForCategory($category->id) as $document)
                            <div class="p-4 hover:bg-carbon-50 dark:hover:bg-carbon-700/30 transition-colors">
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                    <div class="flex items-start gap-4">
                                        {{-- Icône PDF --}}
                                        <div class="w-12 h-12 rounded-lg bg-red-100 dark:bg-red-500/20 flex items-center justify-center flex-shrink-0">
                                            <svg class="w-6 h-6 text-red-500" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6zm-1 2l5 5h-5V4zM8.5 12a.5.5 0 00-.5.5v3a.5.5 0 001 0V14h.5a1.5 1.5 0 000-3H8.5zm.5 1h.5a.5.5 0 010 1H9v-1zm3.5-1a.5.5 0 00-.5.5v3a.5.5 0 00.5.5h1a1.5 1.5 0 001.5-1.5v-1a1.5 1.5 0 00-1.5-1.5h-1zm.5 1h.5a.5.5 0 01.5.5v1a.5.5 0 01-.5.5h-.5v-2zm4-.5a.5.5 0 01.5.5v.5h1a.5.5 0 010 1h-1v1a.5.5 0 01-1 0v-3a.5.5 0 01.5-.5h1.5a.5.5 0 010 1H17z"/>
                                            </svg>
                                        </div>
                                        {{-- Infos --}}
                                        <div>
                                            <h3 class="font-medium text-carbon-900 dark:text-white">{{ $document->title }}</h3>
                                            <div class="flex flex-wrap items-center gap-x-3 gap-y-1 mt-1 text-sm text-carbon-500 dark:text-carbon-400">
                                                <span>{{ $document->formattedFileSize }}</span>
                                                @if ($document->published_at)
                                                    <span>•</span>
                                                    <span>Publié le {{ $document->published_at->format('d/m/Y') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Actions --}}
                                    <div class="flex items-center gap-2 sm:gap-3">
                                        {{-- Consulter (ouvre dans le navigateur) --}}
                                        <a href="{{ route('board.view', $document->slug) }}" target="_blank"
                                           class="inline-flex items-center gap-2 px-4 py-2 bg-carbon-100 dark:bg-carbon-700 hover:bg-carbon-200 dark:hover:bg-carbon-600 text-carbon-700 dark:text-white rounded-lg transition-colors text-sm font-medium">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                            <span class="hidden sm:inline">Consulter</span>
                                        </a>

                                        {{-- Télécharger --}}
                                        <a href="{{ route('board.download', $document->slug) }}"
                                           class="inline-flex items-center gap-2 px-4 py-2 bg-racing-red-500 hover:bg-racing-red-600 text-white rounded-lg transition-colors text-sm font-medium">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                            </svg>
                                            <span class="hidden sm:inline">Télécharger</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Footer info --}}
    <div class="mt-8 text-center">
        <p class="text-sm text-carbon-500 dark:text-carbon-400">
            Ce tableau d'affichage numérique remplace le tableau d'affichage physique obligatoire.
        </p>
        <p class="text-xs text-carbon-400 dark:text-carbon-500 mt-2">
            Organisation : ASA CFG •
            <a href="{{ route('home') }}" class="text-racing-red-500 hover:text-racing-red-600">
                run200-manager.fr
            </a>
        </p>
    </div>
</div>
