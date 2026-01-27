<div>
    {{-- Header avec gradient --}}
    <div class="relative mb-8 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-8 bg-racing-gradient-subtle overflow-hidden">
        {{-- Decorative elements --}}
        <div class="absolute top-0 right-0 w-64 h-64 bg-racing-red-500/5 rounded-full -translate-y-1/2 translate-x-1/2"></div>
        <div class="absolute bottom-0 left-0 w-32 h-32 bg-checkered-yellow-500/10 rounded-full translate-y-1/2 -translate-x-1/2"></div>

        <div class="relative">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-carbon-900 dark:text-white">
                        Bonjour, <span class="text-racing-gradient">{{ auth()->user()->first_name ?? 'Pilote' }}</span> 👋
                    </h1>
                    <p class="mt-1 text-carbon-600 dark:text-carbon-400">
                        @if($canRegisterForRace)
                            Prêt pour votre prochaine course ?
                        @else
                            Complétez votre profil pour participer aux courses
                        @endif
                    </p>
                </div>

                @if($canRegisterForRace)
                    <x-racing.button href="{{ route('pilot.races.index') }}" variant="primary" icon="🏁">
                        S'inscrire à une course
                    </x-racing.button>
                @endif
            </div>
        </div>
    </div>

    {{-- Alertes de statut --}}
    @if (!$hasProfile)
        <x-racing.alert type="warning" class="mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <p class="font-semibold">Profil pilote non créé</p>
                    <p class="text-sm mt-1 opacity-90">Créez votre profil pilote pour pouvoir vous inscrire aux courses.</p>
                </div>
                <x-racing.button href="{{ route('pilot.profile.edit') }}" variant="secondary" size="sm">
                    Créer mon profil
                </x-racing.button>
            </div>
        </x-racing.alert>
    @elseif (!$profileComplete)
        <x-racing.alert type="info" class="mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex-1">
                    <p class="font-semibold">Profil incomplet</p>
                    <p class="text-sm mt-1 opacity-90">Complétez votre profil à 100% pour pouvoir vous inscrire aux courses.</p>
                    <div class="mt-3">
                        <x-racing.progress-bar :value="$profileCompletionPercentage" :max="100" size="sm" showLabel />
                    </div>
                </div>
                <x-racing.button href="{{ route('pilot.profile.edit') }}" variant="secondary" size="sm">
                    Compléter
                </x-racing.button>
            </div>
        </x-racing.alert>
    @endif

    @if ($hasProfile && $profileComplete && $stats['cars_count'] === 0)
        <x-racing.alert type="warning" class="mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <p class="font-semibold">🚗 Aucune voiture enregistrée</p>
                    <p class="text-sm mt-1 opacity-90">Ajoutez au moins une voiture pour pouvoir vous inscrire aux courses.</p>
                </div>
                <x-racing.button href="{{ route('pilot.cars.create') }}" variant="secondary" size="sm">
                    Ajouter une voiture
                </x-racing.button>
            </div>
        </x-racing.alert>
    @endif

    @if ($canRegisterForRace)
        <x-racing.alert type="success" class="mb-6">
            <div class="flex items-center gap-3">
                <span class="text-2xl">✅</span>
                <div>
                    <p class="font-semibold">Prêt à courir !</p>
                    <p class="text-sm opacity-90">Votre profil est complet et vous avez au moins une voiture. Inscrivez-vous maintenant !</p>
                </div>
            </div>
        </x-racing.alert>
    @endif

    {{-- Statistiques --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <x-racing.stat-card
            value="{{ $stats['cars_count'] }}"
            label="Mes Voitures"
            icon="🚗"
            href="{{ route('pilot.cars.index') }}"
        />
        <x-racing.stat-card
            value="{{ $stats['registrations_count'] }}"
            label="Inscriptions"
            icon="📋"
            highlight
            href="{{ route('pilot.registrations.index') }}"
        />
        <x-racing.stat-card
            value="{{ $stats['championship_position'] ?? '-' }}"
            label="Position"
            icon="🏆"
            href="{{ route('pilot.championship') }}"
        />
        <x-racing.stat-card
            value="{{ $stats['total_points'] ?? 0 }}"
            label="Points"
            icon="⭐"
            href="{{ route('pilot.championship') }}"
        />
    </div>

    {{-- Contenu principal --}}
    @if ($hasProfile && $profileComplete)
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            {{-- Courses ouvertes --}}
            <x-racing.card>
                <x-slot:header>
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-white flex items-center gap-2">
                            <span>🏁</span> Courses ouvertes
                        </h2>
                        <a href="{{ route('pilot.races.index') }}" class="text-sm text-white/80 hover:text-white transition-colors">
                            Voir tout →
                        </a>
                    </div>
                </x-slot:header>

                <div class="divide-y divide-carbon-200 dark:divide-carbon-700 -mx-4 sm:-mx-6">
                    @forelse($openRaces as $race)
                        <div class="px-4 sm:px-6 py-4 flex items-center justify-between hover:bg-carbon-50 dark:hover:bg-carbon-800/50 transition-colors">
                            <div class="min-w-0 flex-1">
                                <p class="font-medium text-carbon-900 dark:text-white truncate">{{ $race->name }}</p>
                                <div class="flex items-center gap-3 mt-1 text-sm text-carbon-500 dark:text-carbon-400">
                                    <span class="flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        {{ $race->race_date->format('d/m/Y') }}
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        </svg>
                                        {{ $race->location }}
                                    </span>
                                </div>
                            </div>
                            <x-racing.button href="{{ route('pilot.registrations.create', $race) }}" size="sm" class="ml-4 flex-shrink-0">
                                S'inscrire
                            </x-racing.button>
                        </div>
                    @empty
                        <x-racing.empty-state
                            title="Aucune course ouverte"
                            description="Les inscriptions aux prochaines courses ne sont pas encore ouvertes."
                            icon="🏁"
                            class="py-8"
                        />
                    @endforelse
                </div>
            </x-racing.card>

            {{-- Inscriptions récentes --}}
            <x-racing.card>
                <x-slot:header>
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-white flex items-center gap-2">
                            <span>📋</span> Mes inscriptions
                        </h2>
                        <a href="{{ route('pilot.registrations.index') }}" class="text-sm text-white/80 hover:text-white transition-colors">
                            Voir tout →
                        </a>
                    </div>
                </x-slot:header>

                <div class="divide-y divide-carbon-200 dark:divide-carbon-700 -mx-4 sm:-mx-6">
                    @forelse($recentRegistrations as $registration)
                        <div class="px-4 sm:px-6 py-4 flex items-center justify-between hover:bg-carbon-50 dark:hover:bg-carbon-800/50 transition-colors">
                            <div class="min-w-0 flex-1">
                                <p class="font-medium text-carbon-900 dark:text-white truncate">{{ $registration->race->name ?? 'Course inconnue' }}</p>
                                <div class="flex items-center gap-3 mt-1 text-sm text-carbon-500 dark:text-carbon-400">
                                    <span>🚗 {{ $registration->car->brand ?? '' }} {{ $registration->car->model ?? '' }}</span>
                                    <span>{{ $registration->created_at->format('d/m/Y') }}</span>
                                </div>
                            </div>
                            <x-racing.badge-status :status="$registration->status" class="ml-4 flex-shrink-0" />
                        </div>
                    @empty
                        <x-racing.empty-state
                            title="Aucune inscription"
                            description="Inscrivez-vous à votre première course !"
                            icon="📋"
                            actionLabel="Voir les courses"
                            actionHref="{{ route('pilot.races.index') }}"
                            class="py-8"
                        />
                    @endforelse
                </div>
            </x-racing.card>
        </div>

        {{-- Actions Rapides --}}
        <x-racing.card class="mb-8">
            <h2 class="text-lg font-semibold text-carbon-900 dark:text-white mb-4 flex items-center gap-2">
                <span>⚡</span> Actions Rapides
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <a href="{{ route('pilot.cars.create') }}" class="group flex items-center gap-4 p-4 rounded-xl border-2 border-dashed border-carbon-300 dark:border-carbon-700 hover:border-racing-red-500 dark:hover:border-racing-red-500 hover:bg-racing-red-50 dark:hover:bg-racing-red-900/10 transition-all duration-200">
                    <div class="w-12 h-12 rounded-xl bg-carbon-100 dark:bg-carbon-800 group-hover:bg-racing-red-100 dark:group-hover:bg-racing-red-900/30 flex items-center justify-center transition-colors">
                        <span class="text-2xl">🚗</span>
                    </div>
                    <div>
                        <p class="font-medium text-carbon-900 dark:text-white">Ajouter une voiture</p>
                        <p class="text-sm text-carbon-500 dark:text-carbon-400">Enregistrer un véhicule</p>
                    </div>
                </a>

                <a href="{{ route('pilot.races.index') }}" class="group flex items-center gap-4 p-4 rounded-xl border-2 border-dashed border-carbon-300 dark:border-carbon-700 hover:border-racing-red-500 dark:hover:border-racing-red-500 hover:bg-racing-red-50 dark:hover:bg-racing-red-900/10 transition-all duration-200">
                    <div class="w-12 h-12 rounded-xl bg-carbon-100 dark:bg-carbon-800 group-hover:bg-racing-red-100 dark:group-hover:bg-racing-red-900/30 flex items-center justify-center transition-colors">
                        <span class="text-2xl">🏁</span>
                    </div>
                    <div>
                        <p class="font-medium text-carbon-900 dark:text-white">S'inscrire</p>
                        <p class="text-sm text-carbon-500 dark:text-carbon-400">Voir les courses ouvertes</p>
                    </div>
                </a>

                <a href="{{ route('pilot.championship') }}" class="group flex items-center gap-4 p-4 rounded-xl border-2 border-dashed border-carbon-300 dark:border-carbon-700 hover:border-checkered-yellow-500 dark:hover:border-checkered-yellow-500 hover:bg-checkered-yellow-50 dark:hover:bg-checkered-yellow-900/10 transition-all duration-200">
                    <div class="w-12 h-12 rounded-xl bg-carbon-100 dark:bg-carbon-800 group-hover:bg-checkered-yellow-100 dark:group-hover:bg-checkered-yellow-900/30 flex items-center justify-center transition-colors">
                        <span class="text-2xl">🏆</span>
                    </div>
                    <div>
                        <p class="font-medium text-carbon-900 dark:text-white">Classement</p>
                        <p class="text-sm text-carbon-500 dark:text-carbon-400">Voir le championnat</p>
                    </div>
                </a>
            </div>
        </x-racing.card>
    @else
        {{-- Stepper pour guider le pilote --}}
        <x-racing.card class="mb-8">
            <h2 class="text-lg font-semibold text-carbon-900 dark:text-white mb-6 flex items-center gap-2">
                <span>📍</span> Étapes pour participer
            </h2>
            <x-racing.stepper
                :steps="[
                    ['label' => 'Créer mon profil', 'description' => 'Informations personnelles'],
                    ['label' => 'Ajouter une voiture', 'description' => 'Enregistrer un véhicule'],
                    ['label' => 'S\'inscrire', 'description' => 'Choisir une course'],
                ]"
                :currentStep="$hasProfile ? ($stats['cars_count'] > 0 ? 3 : 2) : 1"
            />

            <div class="mt-8 flex justify-center">
                @if(!$hasProfile)
                    <x-racing.button href="{{ route('pilot.profile.edit') }}">
                        Créer mon profil →
                    </x-racing.button>
                @elseif(!$profileComplete)
                    <x-racing.button href="{{ route('pilot.profile.edit') }}">
                        Compléter mon profil →
                    </x-racing.button>
                @elseif($stats['cars_count'] === 0)
                    <x-racing.button href="{{ route('pilot.cars.create') }}">
                        Ajouter une voiture →
                    </x-racing.button>
                @endif
            </div>
        </x-racing.card>
    @endif
</div>
