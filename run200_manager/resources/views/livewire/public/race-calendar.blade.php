<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    {{-- Header --}}
    <div class="text-center mb-10">
        <div class="inline-flex items-center gap-3 mb-4">
            <div class="w-12 h-1 bg-gradient-to-r from-transparent to-racing-red-500 rounded-full"></div>
            <svg class="w-8 h-8 text-racing-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <div class="w-12 h-1 bg-gradient-to-l from-transparent to-racing-red-500 rounded-full"></div>
        </div>
        <h1 class="text-3xl md:text-4xl font-display font-bold text-carbon-900 dark:text-white">
            Calendrier des courses
        </h1>
        @if($this->season)
            <p class="mt-3 text-lg text-racing-red-600 dark:text-racing-red-400 font-semibold">
                Saison {{ $this->season->name }}
            </p>
            <p class="mt-1 text-sm text-carbon-500 dark:text-carbon-400">
                Du {{ $this->season->start_date->format('d/m/Y') }} au {{ $this->season->end_date->format('d/m/Y') }}
            </p>
        @endif
    </div>

    @if(!$this->season)
        {{-- No active season --}}
        <div class="racing-card p-8 text-center border-l-4 border-checkered-yellow-500">
            <svg class="mx-auto h-16 w-16 text-checkered-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <h3 class="mt-4 text-lg font-semibold text-carbon-800 dark:text-carbon-200">Aucune saison active</h3>
            <p class="mt-2 text-carbon-600 dark:text-carbon-400">Le calendrier des courses sera disponible lors de l'ouverture de la prochaine saison.</p>
        </div>
    @else
        {{-- Season Stats --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
            <div class="racing-card p-6 text-center">
                <div class="w-12 h-12 mx-auto mb-3 bg-racing-red-100 dark:bg-racing-red-900/30 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-racing-red-600 dark:text-racing-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                </div>
                <div class="text-3xl font-bold text-racing-red-600 dark:text-racing-red-400">{{ $this->seasonStats['total_races'] }}</div>
                <div class="text-sm text-carbon-500 dark:text-carbon-400 mt-1">Courses au total</div>
            </div>
            <div class="racing-card p-6 text-center">
                <div class="w-12 h-12 mx-auto mb-3 bg-green-100 dark:bg-green-900/30 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="text-3xl font-bold text-green-600 dark:text-green-400">{{ $this->seasonStats['completed_races'] }}</div>
                <div class="text-sm text-carbon-500 dark:text-carbon-400 mt-1">Courses terminées</div>
            </div>
            <div class="racing-card p-6 text-center">
                <div class="w-12 h-12 mx-auto mb-3 bg-checkered-yellow-100 dark:bg-checkered-yellow-900/30 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-checkered-yellow-600 dark:text-checkered-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="text-3xl font-bold text-checkered-yellow-600 dark:text-checkered-yellow-400">{{ $this->seasonStats['upcoming_races'] }}</div>
                <div class="text-sm text-carbon-500 dark:text-carbon-400 mt-1">Courses à venir</div>
            </div>
        </div>

        {{-- Upcoming Races --}}
        @if($this->upcomingRaces->isNotEmpty())
            <div class="mb-12">
                <h2 class="text-xl font-bold text-carbon-900 dark:text-white mb-6 flex items-center gap-2">
                    <div class="w-8 h-8 bg-checkered-yellow-100 dark:bg-checkered-yellow-900/30 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-checkered-yellow-600 dark:text-checkered-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    Prochaines courses
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($this->upcomingRaces as $race)
                        <div class="racing-card overflow-hidden hover:shadow-racing transition-all duration-300 group">
                            {{-- Date Header --}}
                            <div class="bg-gradient-to-r from-racing-red-500 to-racing-red-700 px-6 py-4 relative overflow-hidden">
                                {{-- Decorative element --}}
                                <div class="absolute top-0 right-0 w-20 h-20 bg-white/10 rounded-bl-full"></div>

                                <div class="flex items-center justify-between relative z-10">
                                    <div>
                                        <div class="text-white/80 text-sm uppercase tracking-wide">
                                            {{ $race->race_date->translatedFormat('l') }}
                                        </div>
                                        <div class="text-white text-2xl font-bold">
                                            {{ $race->race_date->format('d') }}
                                            <span class="text-lg font-normal">{{ $race->race_date->translatedFormat('F Y') }}</span>
                                        </div>
                                    </div>
                                    @if($race->status === 'OPEN')
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-500 text-white shadow-sm">
                                            <span class="w-2 h-2 bg-white rounded-full mr-1.5 animate-pulse"></span>
                                            Inscriptions ouvertes
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-carbon-600 text-white">
                                            Inscriptions fermées
                                        </span>
                                    @endif
                                </div>
                            </div>

                            {{-- Race Info --}}
                            <div class="p-6">
                                <h3 class="text-lg font-semibold text-carbon-900 dark:text-white mb-2 group-hover:text-racing-red-600 dark:group-hover:text-racing-red-400 transition-colors">
                                    {{ $race->name }}
                                </h3>
                                <div class="flex items-center text-carbon-500 dark:text-carbon-400 mb-4">
                                    <svg class="w-5 h-5 mr-2 text-carbon-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    {{ $race->location }}
                                </div>

                                {{-- Days countdown --}}
                                @php
                                    $daysUntil = now()->startOfDay()->diffInDays($race->race_date, false);
                                @endphp
                                <div class="flex items-center justify-between py-3 px-4 bg-carbon-50 dark:bg-carbon-800/50 rounded-lg mb-4">
                                    <span class="text-sm text-carbon-600 dark:text-carbon-400">
                                        @if($daysUntil === 0)
                                            <span class="text-green-600 dark:text-green-400 font-bold flex items-center gap-1">
                                                <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                                                Aujourd'hui !
                                            </span>
                                        @elseif($daysUntil === 1)
                                            <span class="text-checkered-yellow-600 dark:text-checkered-yellow-400 font-bold">Demain !</span>
                                        @else
                                            Dans <span class="font-bold text-racing-red-600 dark:text-racing-red-400">{{ $daysUntil }}</span> jours
                                        @endif
                                    </span>
                                    @if($race->entry_fee_cents)
                                        <span class="text-sm font-semibold text-carbon-700 dark:text-carbon-300 bg-white dark:bg-carbon-700 px-3 py-1 rounded-full">
                                            {{ $race->formatted_entry_fee }}
                                        </span>
                                    @endif
                                </div>

                                @if($race->status === 'OPEN')
                                    <div>
                                        @auth
                                            <a href="{{ route('pilot.registrations.create', $race) }}"
                                               class="btn-racing-primary w-full text-center">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                                S'inscrire
                                            </a>
                                        @else
                                            <a href="{{ route('register') }}"
                                               class="btn-racing-primary w-full text-center">
                                                Créer un compte pour s'inscrire
                                            </a>
                                        @endauth
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Past Races --}}
        @if($this->pastRaces->isNotEmpty())
            <div>
                <h2 class="text-xl font-bold text-carbon-900 dark:text-white mb-6 flex items-center gap-2">
                    <div class="w-8 h-8 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    Courses passées
                </h2>

                <div class="racing-card overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-carbon-200 dark:divide-carbon-700">
                            <thead class="bg-carbon-50 dark:bg-carbon-800">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-carbon-600 dark:text-carbon-300 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-carbon-600 dark:text-carbon-300 uppercase tracking-wider">Course</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-carbon-600 dark:text-carbon-300 uppercase tracking-wider">Lieu</th>
                                    <th class="px-6 py-4 text-center text-xs font-semibold text-carbon-600 dark:text-carbon-300 uppercase tracking-wider">Statut</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-carbon-200 dark:divide-carbon-700">
                                @foreach($this->pastRaces as $race)
                                    <tr class="hover:bg-carbon-50 dark:hover:bg-carbon-800/50 transition-colors">
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
                                            @if($race->status === 'PUBLISHED')
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300">
                                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                                    </svg>
                                                    Résultats publiés
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-carbon-100 dark:bg-carbon-700 text-carbon-700 dark:text-carbon-300">
                                                    Terminée
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        {{-- No races --}}
        @if($this->allRaces->isEmpty())
            <div class="racing-card p-8 text-center">
                <div class="w-16 h-16 mx-auto mb-4 bg-carbon-100 dark:bg-carbon-800 rounded-full flex items-center justify-center">
                    <svg class="h-8 w-8 text-carbon-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-carbon-700 dark:text-carbon-200">Aucune course programmée</h3>
                <p class="mt-2 text-carbon-500 dark:text-carbon-400">Les courses de la saison seront bientôt annoncées.</p>
            </div>
        @endif

        {{-- CTA for registration --}}
        @guest
            <div class="mt-12 relative overflow-hidden rounded-2xl">
                {{-- Background with gradient --}}
                <div class="absolute inset-0 bg-gradient-to-r from-racing-red-600 to-racing-red-800"></div>

                {{-- Decorative elements --}}
                <div class="absolute top-0 right-0 w-64 h-64 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/2"></div>
                <div class="absolute bottom-0 left-0 w-48 h-48 bg-black/10 rounded-full translate-y-1/2 -translate-x-1/2"></div>

                {{-- Checkered pattern --}}
                <div class="absolute bottom-0 right-0 w-32 h-8 opacity-20">
                    <div class="grid grid-cols-8 h-full">
                        @for($i = 0; $i < 16; $i++)
                            <div class="{{ $i % 2 === ($i < 8 ? 0 : 1) ? 'bg-white' : 'bg-transparent' }}"></div>
                        @endfor
                    </div>
                </div>

                <div class="relative z-10 p-8 md:p-10 text-center">
                    <h3 class="text-2xl md:text-3xl font-display font-bold text-white mb-3">Rejoignez le championnat !</h3>
                    <p class="text-racing-red-100 mb-6 max-w-lg mx-auto">Créez votre compte pilote et inscrivez-vous aux prochaines courses pour vivre l'adrénaline de la compétition.</p>
                    <a href="{{ route('register') }}"
                       class="inline-flex items-center px-8 py-4 bg-white text-racing-red-600 font-bold rounded-xl hover:bg-racing-red-50 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                        </svg>
                        S'inscrire maintenant
                    </a>
                </div>
            </div>
        @endguest
    @endif
</div>
