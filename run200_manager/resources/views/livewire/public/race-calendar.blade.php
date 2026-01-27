<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    {{-- Header --}}
    <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
            Calendrier des courses
        </h1>
        @if($this->season)
            <p class="mt-2 text-lg text-indigo-600 dark:text-indigo-400 font-semibold">
                Saison {{ $this->season->name }}
            </p>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Du {{ $this->season->start_date->format('d/m/Y') }} au {{ $this->season->end_date->format('d/m/Y') }}
            </p>
        @endif
    </div>

    @if(!$this->season)
        {{-- No active season --}}
        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-xl p-8 text-center">
            <svg class="mx-auto h-16 w-16 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <h3 class="mt-4 text-lg font-semibold text-yellow-800 dark:text-yellow-200">Aucune saison active</h3>
            <p class="mt-2 text-yellow-600 dark:text-yellow-300">Le calendrier des courses sera disponible lors de l'ouverture de la prochaine saison.</p>
        </div>
    @else
        {{-- Season Stats --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm p-6 text-center border border-gray-200 dark:border-zinc-700">
                <div class="text-3xl font-bold text-indigo-600 dark:text-indigo-400">{{ $this->seasonStats['total_races'] }}</div>
                <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Courses au total</div>
            </div>
            <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm p-6 text-center border border-gray-200 dark:border-zinc-700">
                <div class="text-3xl font-bold text-green-600 dark:text-green-400">{{ $this->seasonStats['completed_races'] }}</div>
                <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Courses terminées</div>
            </div>
            <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm p-6 text-center border border-gray-200 dark:border-zinc-700">
                <div class="text-3xl font-bold text-orange-600 dark:text-orange-400">{{ $this->seasonStats['upcoming_races'] }}</div>
                <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Courses à venir</div>
            </div>
        </div>

        {{-- Upcoming Races --}}
        @if($this->upcomingRaces->isNotEmpty())
            <div class="mb-10">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <svg class="w-6 h-6 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Prochaines courses
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($this->upcomingRaces as $race)
                        <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm overflow-hidden border border-gray-200 dark:border-zinc-700 hover:shadow-md transition-shadow">
                            {{-- Date Header --}}
                            <div class="bg-gradient-to-r from-indigo-500 to-purple-600 px-6 py-4">
                                <div class="flex items-center justify-between">
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
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-500 text-white">
                                            Inscriptions ouvertes
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gray-500 text-white">
                                            Inscriptions fermées
                                        </span>
                                    @endif
                                </div>
                            </div>

                            {{-- Race Info --}}
                            <div class="p-6">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                                    {{ $race->name }}
                                </h3>
                                <div class="flex items-center text-gray-500 dark:text-gray-400 mb-3">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    {{ $race->location }}
                                </div>

                                {{-- Days countdown --}}
                                @php
                                    $daysUntil = now()->startOfDay()->diffInDays($race->race_date, false);
                                @endphp
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-500 dark:text-gray-400">
                                        @if($daysUntil === 0)
                                            <span class="text-green-600 dark:text-green-400 font-semibold">Aujourd'hui !</span>
                                        @elseif($daysUntil === 1)
                                            <span class="text-orange-600 dark:text-orange-400 font-semibold">Demain !</span>
                                        @else
                                            Dans <span class="font-semibold text-indigo-600 dark:text-indigo-400">{{ $daysUntil }}</span> jours
                                        @endif
                                    </span>
                                    @if($race->entry_fee_cents)
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                            {{ $race->formatted_entry_fee }}
                                        </span>
                                    @endif
                                </div>

                                @if($race->status === 'OPEN')
                                    <div class="mt-4">
                                        @auth
                                            <a href="{{ route('pilot.registrations.create', $race) }}"
                                               class="block w-full text-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">
                                                S'inscrire
                                            </a>
                                        @else
                                            <a href="{{ route('register') }}"
                                               class="block w-full text-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">
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
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Courses passées
                </h2>

                <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm overflow-hidden border border-gray-200 dark:border-zinc-700">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-zinc-700">
                        <thead class="bg-gray-50 dark:bg-zinc-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Course</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Lieu</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Statut</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-zinc-700">
                            @foreach($this->pastRaces as $race)
                                <tr class="hover:bg-gray-50 dark:hover:bg-zinc-700/50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $race->race_date->format('d/m/Y') }}
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ $race->race_date->translatedFormat('l') }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $race->name }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ $race->location }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        @if($race->status === 'PUBLISHED')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300">
                                                Résultats publiés
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-zinc-600 text-gray-800 dark:text-gray-300">
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
        @endif

        {{-- No races --}}
        @if($this->allRaces->isEmpty())
            <div class="bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl p-8 text-center">
                <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <h3 class="mt-4 text-lg font-semibold text-gray-700 dark:text-gray-200">Aucune course programmée</h3>
                <p class="mt-2 text-gray-500 dark:text-gray-400">Les courses de la saison seront bientôt annoncées.</p>
            </div>
        @endif

        {{-- CTA for registration --}}
        @guest
            <div class="mt-10 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-xl p-8 text-center">
                <h3 class="text-xl font-bold text-white mb-2">Rejoignez le championnat !</h3>
                <p class="text-indigo-100 mb-6">Créez votre compte pilote et inscrivez-vous aux prochaines courses.</p>
                <a href="{{ route('register') }}"
                   class="inline-flex items-center px-6 py-3 bg-white text-indigo-600 font-semibold rounded-lg hover:bg-indigo-50 transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                    </svg>
                    S'inscrire maintenant
                </a>
            </div>
        @endguest
    @endif
</div>
