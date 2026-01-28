<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Header --}}
        <div class="text-center mb-12">
            <div class="inline-flex items-center gap-2 px-4 py-2 bg-racing-red-500/10 dark:bg-racing-red-500/20 rounded-full text-racing-red-600 dark:text-racing-red-400 mb-4">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span class="text-sm font-semibold">Documents officiels</span>
            </div>
            <h1 class="font-display text-3xl md:text-4xl font-bold text-carbon-900 dark:text-white mb-4">
                Tableaux d'affichage
            </h1>
            <p class="text-lg text-carbon-600 dark:text-carbon-400 max-w-2xl mx-auto">
                Consultez les documents officiels de chaque course : règlements, horaires, communiqués et résultats.
            </p>
        </div>

        @if(!$this->season)
            {{-- No active season --}}
            <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl p-8 text-center">
                <svg class="mx-auto h-16 w-16 text-amber-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <h3 class="text-lg font-semibold text-amber-800 dark:text-amber-200 mb-2">Aucune saison active</h3>
                <p class="text-amber-600 dark:text-amber-300">Les tableaux d'affichage seront disponibles lors de l'ouverture de la prochaine saison.</p>
            </div>
        @elseif($this->racesWithBoards->isEmpty())
            {{-- No boards available --}}
            <div class="bg-white dark:bg-carbon-800 rounded-xl border border-carbon-200 dark:border-carbon-700 p-12 text-center">
                <svg class="w-16 h-16 text-carbon-300 dark:text-carbon-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <h3 class="text-xl font-semibold text-carbon-700 dark:text-carbon-300 mb-2">Aucun tableau d'affichage disponible</h3>
                <p class="text-carbon-500 dark:text-carbon-400">
                    Les documents officiels des courses seront publiés ici au fur et à mesure de la saison.
                </p>
            </div>
        @else
            {{-- Upcoming races with boards --}}
            @if($this->upcomingRacesWithBoards->isNotEmpty())
                <div class="mb-12">
                    <h2 class="flex items-center gap-3 text-xl font-bold text-carbon-900 dark:text-white mb-6">
                        <div class="w-10 h-10 rounded-xl bg-racing-red-500/10 dark:bg-racing-red-500/20 flex items-center justify-center">
                            <svg class="w-5 h-5 text-racing-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        Courses à venir
                    </h2>

                    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($this->upcomingRacesWithBoards as $race)
                            <a href="{{ route('board.show', $race) }}"
                               class="group racing-card p-6 hover:scale-[1.02] transition-all duration-300">
                                {{-- Date badge --}}
                                <div class="flex items-start justify-between mb-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-14 h-14 rounded-xl bg-racing-red-500 text-white flex flex-col items-center justify-center shadow-racing">
                                            <span class="text-xs font-medium uppercase">{{ $race->race_date->translatedFormat('M') }}</span>
                                            <span class="text-xl font-bold leading-none">{{ $race->race_date->format('d') }}</span>
                                        </div>
                                        <div>
                                            <p class="text-sm text-carbon-500 dark:text-carbon-400">{{ $race->race_date->translatedFormat('l') }}</p>
                                            <p class="text-sm font-medium text-carbon-700 dark:text-carbon-300">{{ $race->race_date->format('Y') }}</p>
                                        </div>
                                    </div>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-racing-red-100 dark:bg-racing-red-900/30 text-racing-red-700 dark:text-racing-red-300">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                                            <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/>
                                        </svg>
                                        {{ $race->documents_count }} doc{{ $race->documents_count > 1 ? 's' : '' }}
                                    </span>
                                </div>

                                {{-- Race info --}}
                                <h3 class="font-display text-lg font-bold text-carbon-900 dark:text-white mb-2 group-hover:text-racing-red-500 transition-colors">
                                    {{ $race->name }}
                                </h3>
                                <div class="flex items-center text-carbon-500 dark:text-carbon-400 text-sm">
                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    {{ $race->location }}
                                </div>

                                {{-- CTA --}}
                                <div class="mt-4 pt-4 border-t border-carbon-100 dark:border-carbon-700 flex items-center justify-between">
                                    <span class="text-sm font-medium text-racing-red-500 group-hover:text-racing-red-600">
                                        Voir les documents
                                    </span>
                                    <svg class="w-5 h-5 text-racing-red-500 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                    </svg>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Past races with boards --}}
            @if($this->pastRacesWithBoards->isNotEmpty())
                <div>
                    <h2 class="flex items-center gap-3 text-xl font-bold text-carbon-900 dark:text-white mb-6">
                        <div class="w-10 h-10 rounded-xl bg-carbon-100 dark:bg-carbon-800 flex items-center justify-center">
                            <svg class="w-5 h-5 text-carbon-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                            </svg>
                        </div>
                        Archives des courses passées
                    </h2>

                    <div class="bg-white dark:bg-carbon-800 rounded-xl border border-carbon-200 dark:border-carbon-700 overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-carbon-200 dark:divide-carbon-700">
                                <thead class="bg-carbon-50 dark:bg-carbon-800/50">
                                    <tr>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-carbon-500 dark:text-carbon-400 uppercase tracking-wider">Date</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-carbon-500 dark:text-carbon-400 uppercase tracking-wider">Course</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-carbon-500 dark:text-carbon-400 uppercase tracking-wider">Lieu</th>
                                        <th class="px-6 py-4 text-center text-xs font-semibold text-carbon-500 dark:text-carbon-400 uppercase tracking-wider">Documents</th>
                                        <th class="px-6 py-4 text-right text-xs font-semibold text-carbon-500 dark:text-carbon-400 uppercase tracking-wider"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-carbon-100 dark:divide-carbon-700">
                                    @foreach($this->pastRacesWithBoards as $race)
                                        <tr class="hover:bg-carbon-50 dark:hover:bg-carbon-700/30 transition-colors">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-carbon-900 dark:text-white">
                                                    {{ $race->race_date->format('d/m/Y') }}
                                                </div>
                                                <div class="text-xs text-carbon-500 dark:text-carbon-400">
                                                    {{ $race->race_date->translatedFormat('l') }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-carbon-900 dark:text-white">
                                                    {{ $race->name }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-carbon-500 dark:text-carbon-400">
                                                    {{ $race->location }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-carbon-100 dark:bg-carbon-700 text-carbon-700 dark:text-carbon-300">
                                                    {{ $race->documents_count }} doc{{ $race->documents_count > 1 ? 's' : '' }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                                <a href="{{ route('board.show', $race) }}" class="inline-flex items-center gap-1 text-sm font-medium text-racing-red-500 hover:text-racing-red-600 transition-colors">
                                                    Consulter
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                                    </svg>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        @endif
    </div>
</div>
