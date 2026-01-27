<div class="space-y-6">
    {{-- Header avec style Racing Admin --}}
    <div class="relative overflow-hidden rounded-xl bg-racing-gradient-subtle p-6 border border-carbon-700">
        {{-- Éléments décoratifs --}}
        <div class="absolute top-0 right-0 w-64 h-64 bg-racing-red-500/5 rounded-full -translate-y-1/2 translate-x-1/2"></div>
        <div class="absolute bottom-0 left-0 w-32 h-32 bg-checkered-yellow-500/5 rounded-full translate-y-1/2 -translate-x-1/2"></div>

        <div class="relative">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-racing-red-500 to-racing-red-700 flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-white">Administration</h1>
                    <p class="text-carbon-400 text-sm">Bienvenue, {{ auth()->user()->name }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Saison Active / Alerte --}}
    @if($this->activeSeason)
        <x-racing.alert type="success">
            <div class="flex items-center justify-between">
                <div>
                    <span class="font-semibold">Saison active : {{ $this->activeSeason->name }}</span>
                    <span class="text-sm ml-2 opacity-80">
                        Du {{ $this->activeSeason->start_date->format('d/m/Y') }} au {{ $this->activeSeason->end_date->format('d/m/Y') }}
                    </span>
                </div>
                <a href="{{ route('admin.seasons.index') }}" class="text-sm font-medium underline hover:no-underline">
                    Gérer →
                </a>
            </div>
        </x-racing.alert>
    @else
        <x-racing.alert type="warning">
            <div class="flex items-center justify-between">
                <div>
                    <span class="font-semibold">Aucune saison active</span>
                    <span class="text-sm ml-2 opacity-80">Créez une saison pour commencer à gérer les courses.</span>
                </div>
                <a href="{{ route('admin.seasons.create') }}" class="text-sm font-medium underline hover:no-underline">
                    Créer une saison →
                </a>
            </div>
        </x-racing.alert>
    @endif

    {{-- Statistiques principales --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-4">
        <x-racing.stat-card
            label="Utilisateurs"
            :value="$this->stats['total_users']"
            icon="👥"
        />
        <x-racing.stat-card
            label="Pilotes"
            :value="$this->stats['total_pilots']"
            icon="🏎️"
        />
        <x-racing.stat-card
            label="Voitures"
            :value="$this->stats['total_cars']"
            icon="🚗"
        />
        <x-racing.stat-card
            label="Inscriptions"
            :value="$this->stats['total_registrations']"
            icon="📋"
        />
        <x-racing.stat-card
            label="En attente"
            :value="$this->stats['pending_registrations']"
            icon="⏳"
            :highlight="$this->stats['pending_registrations'] > 0"
        />
        <x-racing.stat-card
            label="Courses ouvertes"
            :value="$this->stats['open_races']"
            icon="🏁"
        />
        <x-racing.stat-card
            label="Total Courses"
            :value="$this->stats['total_races']"
            icon="🗓️"
        />
        <x-racing.stat-card
            label="Saisons"
            :value="$this->stats['total_seasons']"
            icon="📅"
        />
    </div>

    {{-- KPIs et taux de conversion --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <x-racing.card class="text-center">
            <div class="py-4">
                <div class="text-4xl font-bold text-racing-red-500 mb-2">
                    {{ $this->paymentStats['conversion_rate'] }}%
                </div>
                <div class="text-sm text-carbon-400">Taux de conversion</div>
                <div class="text-xs text-carbon-500 mt-1">Inscriptions validées / Total</div>
            </div>
        </x-racing.card>
        <x-racing.card class="text-center">
            <div class="py-4">
                <div class="text-4xl font-bold text-status-success mb-2">
                    {{ $this->paymentStats['accepted'] }}
                </div>
                <div class="text-sm text-carbon-400">Inscriptions acceptées</div>
            </div>
        </x-racing.card>
        <x-racing.card class="text-center">
            <div class="py-4">
                <div class="text-4xl font-bold text-status-warning mb-2">
                    {{ $this->paymentStats['pending'] }}
                </div>
                <div class="text-sm text-carbon-400">En attente validation</div>
            </div>
        </x-racing.card>
        <x-racing.card class="text-center">
            <div class="py-4">
                <div class="text-4xl font-bold text-status-danger mb-2">
                    {{ $this->paymentStats['refused'] }}
                </div>
                <div class="text-sm text-carbon-400">Refusées</div>
            </div>
        </x-racing.card>
    </div>

    {{-- Section Graphiques --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Évolution des inscriptions --}}
        <x-racing.card>
            <div class="p-6">
                <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                    <span>📈</span>
                    Évolution des inscriptions
                </h3>
                @if(count($this->registrationsEvolution['labels']) > 0)
                    <x-racing.chart
                        id="registrations-evolution"
                        type="line"
                        height="280px"
                        :labels="$this->registrationsEvolution['labels']"
                        :datasets="[['label' => 'Inscriptions', 'data' => $this->registrationsEvolution['data']]]"
                    />
                @else
                    <div class="h-[280px] flex items-center justify-center">
                        <x-racing.empty-state
                            icon="📊"
                            title="Pas assez de données"
                            description="Les données apparaîtront après quelques inscriptions."
                            compact
                        />
                    </div>
                @endif
            </div>
        </x-racing.card>

        {{-- Répartition par statut --}}
        <x-racing.card>
            <div class="p-6">
                <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                    <span>🎯</span>
                    Répartition par statut
                </h3>
                @if(count($this->registrationsByStatus['labels']) > 0)
                    <x-racing.chart
                        id="registrations-status"
                        type="doughnut"
                        height="280px"
                        :labels="$this->registrationsByStatus['labels']"
                        :datasets="$this->registrationsByStatus['data']"
                    />
                @else
                    <div class="h-[280px] flex items-center justify-center">
                        <x-racing.empty-state
                            icon="🎯"
                            title="Pas de données"
                            description="Aucune inscription pour le moment."
                            compact
                        />
                    </div>
                @endif
            </div>
        </x-racing.card>

        {{-- Répartition par catégorie --}}
        <x-racing.card>
            <div class="p-6">
                <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                    <span>🏆</span>
                    Voitures par catégorie
                </h3>
                @if(count($this->carsByCategory['labels']) > 0)
                    <x-racing.chart
                        id="cars-category"
                        type="doughnut"
                        height="280px"
                        :labels="$this->carsByCategory['labels']"
                        :datasets="$this->carsByCategory['data']"
                    />
                @else
                    <div class="h-[280px] flex items-center justify-center">
                        <x-racing.empty-state
                            icon="🚗"
                            title="Pas de voitures"
                            description="Aucune voiture enregistrée."
                            compact
                        />
                    </div>
                @endif
            </div>
        </x-racing.card>

        {{-- Inscriptions par course --}}
        <x-racing.card>
            <div class="p-6">
                <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                    <span>🏁</span>
                    Inscriptions par course
                </h3>
                @if(count($this->racesFillRate['labels']) > 0)
                    <x-racing.chart
                        id="races-fill"
                        type="bar"
                        height="280px"
                        :labels="$this->racesFillRate['labels']"
                        :datasets="[['label' => 'Inscriptions', 'data' => $this->racesFillRate['data']]]"
                    />
                @else
                    <div class="h-[280px] flex items-center justify-center">
                        <x-racing.empty-state
                            icon="🏁"
                            title="Pas de courses"
                            description="Créez des courses pour voir les statistiques."
                            compact
                        />
                    </div>
                @endif
            </div>
        </x-racing.card>
    </div>

    {{-- Top pilotes --}}
    <x-racing.card>
        <div class="p-6">
            <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                <span>🥇</span>
                Top 5 Pilotes (par inscriptions)
            </h3>
            @if(count($this->topPilots['labels']) > 0)
                <x-racing.chart
                    id="top-pilots"
                    type="horizontalBar"
                    height="200px"
                    :labels="$this->topPilots['labels']"
                    :datasets="$this->topPilots['data']"
                    :colors="['#ef4444']"
                />
            @else
                <div class="h-[200px] flex items-center justify-center">
                    <x-racing.empty-state
                        icon="👥"
                        title="Pas de pilotes"
                        description="Les pilotes avec le plus d'inscriptions apparaîtront ici."
                        compact
                    />
                </div>
            @endif
        </div>
    </x-racing.card>

    {{-- Grid 2 colonnes : Courses et Inscriptions récentes --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Courses récentes --}}
        <x-racing.card title="Courses récentes" icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/></svg>'>
            <x-slot:actions>
                <a href="{{ route('admin.races.index') }}" class="text-sm text-racing-red-500 hover:text-racing-red-400 font-medium">
                    Voir tout →
                </a>
            </x-slot:actions>
            <div class="divide-y divide-carbon-700/50">
                @forelse($this->recentRaces as $race)
                    <div class="flex items-center justify-between py-3 first:pt-0 last:pb-0">
                        <div>
                            <p class="text-sm font-medium text-white">{{ $race->name }}</p>
                            <p class="text-xs text-carbon-400">
                                {{ $race->race_date->format('d/m/Y') }} • {{ $race->location }}
                            </p>
                        </div>
                        <x-racing.badge-status :status="$race->status" />
                    </div>
                @empty
                    <x-racing.empty-state
                        title="Aucune course"
                        description="Créez votre première course"
                        icon='<svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/></svg>'
                        compact
                    />
                @endforelse
            </div>
        </x-racing.card>

        {{-- Inscriptions récentes --}}
        <x-racing.card title="Inscriptions récentes" icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>'>
            <x-slot:actions>
                <a href="{{ route('staff.registrations.index') }}" class="text-sm text-racing-red-500 hover:text-racing-red-400 font-medium">
                    Voir tout →
                </a>
            </x-slot:actions>
            <div class="divide-y divide-carbon-700/50">
                @forelse($this->recentRegistrations as $registration)
                    <div class="flex items-center justify-between py-3 first:pt-0 last:pb-0">
                        <div>
                            <p class="text-sm font-medium text-white">
                                {{ $registration->pilot->fullName ?? 'Pilote inconnu' }}
                            </p>
                            <p class="text-xs text-carbon-400">
                                {{ $registration->race->name ?? 'Course inconnue' }} • {{ $registration->car->model ?? '' }}
                            </p>
                        </div>
                        <x-racing.badge-status :status="$registration->status" />
                    </div>
                @empty
                    <x-racing.empty-state
                        title="Aucune inscription"
                        description="Les inscriptions apparaîtront ici"
                        icon='<svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>'
                        compact
                    />
                @endforelse
            </div>
        </x-racing.card>
    </div>

    {{-- Actions rapides --}}
    <x-racing.card title="Actions rapides" icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>'>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <a href="{{ route('admin.seasons.create') }}"
               class="group flex items-center gap-4 p-4 rounded-xl border border-dashed border-carbon-600 hover:border-racing-red-500/50 hover:bg-racing-red-500/5 transition-all duration-200">
                <div class="w-12 h-12 rounded-lg bg-racing-red-500/20 flex items-center justify-center group-hover:bg-racing-red-500/30 transition-colors">
                    <svg class="w-6 h-6 text-racing-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-white group-hover:text-racing-red-500 transition-colors">Nouvelle Saison</p>
                    <p class="text-xs text-carbon-400">Créer une saison</p>
                </div>
            </a>

            <a href="{{ route('admin.races.create') }}"
               class="group flex items-center gap-4 p-4 rounded-xl border border-dashed border-carbon-600 hover:border-checkered-yellow-500/50 hover:bg-checkered-yellow-500/5 transition-all duration-200">
                <div class="w-12 h-12 rounded-lg bg-checkered-yellow-500/20 flex items-center justify-center group-hover:bg-checkered-yellow-500/30 transition-colors">
                    <svg class="w-6 h-6 text-checkered-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-white group-hover:text-checkered-yellow-500 transition-colors">Nouvelle Course</p>
                    <p class="text-xs text-carbon-400">Créer une course</p>
                </div>
            </a>

            <a href="{{ route('admin.users.index') }}"
               class="group flex items-center gap-4 p-4 rounded-xl border border-dashed border-carbon-600 hover:border-status-info/50 hover:bg-status-info/5 transition-all duration-200">
                <div class="w-12 h-12 rounded-lg bg-status-info/20 flex items-center justify-center group-hover:bg-status-info/30 transition-colors">
                    <svg class="w-6 h-6 text-status-info" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-white group-hover:text-status-info transition-colors">Utilisateurs</p>
                    <p class="text-xs text-carbon-400">Gérer les utilisateurs</p>
                </div>
            </a>

            <a href="{{ route('staff.registrations.index') }}"
               class="group flex items-center gap-4 p-4 rounded-xl border border-dashed border-carbon-600 hover:border-status-warning/50 hover:bg-status-warning/5 transition-all duration-200">
                <div class="w-12 h-12 rounded-lg bg-status-warning/20 flex items-center justify-center group-hover:bg-status-warning/30 transition-colors">
                    <svg class="w-6 h-6 text-status-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-white group-hover:text-status-warning transition-colors">Inscriptions</p>
                    <p class="text-xs text-carbon-400">Valider les inscriptions</p>
                </div>
            </a>

            <a href="{{ route('admin.registrations.walk-in') }}"
               class="group flex items-center gap-4 p-4 rounded-xl border border-dashed border-carbon-600 hover:border-status-success/50 hover:bg-status-success/5 transition-all duration-200">
                <div class="w-12 h-12 rounded-lg bg-status-success/20 flex items-center justify-center group-hover:bg-status-success/30 transition-colors">
                    <svg class="w-6 h-6 text-status-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-white group-hover:text-status-success transition-colors">Inscription manuelle</p>
                    <p class="text-xs text-carbon-400">Inscrire un pilote sur place</p>
                </div>
            </a>

            <a href="{{ route('staff.registrations.engagement') }}"
               class="group flex items-center gap-4 p-4 rounded-xl border border-dashed border-carbon-600 hover:border-purple-500/50 hover:bg-purple-500/5 transition-all duration-200">
                <div class="w-12 h-12 rounded-lg bg-purple-500/20 flex items-center justify-center group-hover:bg-purple-500/30 transition-colors">
                    <svg class="w-6 h-6 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-white group-hover:text-purple-500 transition-colors">Feuilles d'engagement</p>
                    <p class="text-xs text-carbon-400">Signature électronique</p>
                </div>
            </a>
        </div>
    </x-racing.card>

    {{-- Scanners Checkpoints --}}
    <x-racing.card title="Scanners Checkpoints" subtitle="Scannez les QR codes des pilotes aux différents points de contrôle" icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>'>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <a href="{{ route('staff.scan.admin') }}"
               class="group relative flex flex-col items-center p-6 rounded-xl border-2 border-carbon-700 bg-carbon-800/50 hover:border-purple-500 hover:bg-purple-500/10 transition-all duration-200">
                <div class="w-14 h-14 rounded-full bg-purple-500/20 flex items-center justify-center mb-3 group-hover:bg-purple-500/30 transition-colors">
                    <svg class="w-7 h-7 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    </svg>
                </div>
                <span class="text-sm font-semibold text-white group-hover:text-purple-500 transition-colors">Contrôle Administratif</span>
                <span class="text-xs text-carbon-400 mt-1">Vérification documents</span>
            </a>

            <a href="{{ route('staff.scan.tech') }}"
               class="group relative flex flex-col items-center p-6 rounded-xl border-2 border-carbon-700 bg-carbon-800/50 hover:border-status-info hover:bg-status-info/10 transition-all duration-200">
                <div class="w-14 h-14 rounded-full bg-status-info/20 flex items-center justify-center mb-3 group-hover:bg-status-info/30 transition-colors">
                    <svg class="w-7 h-7 text-status-info" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <span class="text-sm font-semibold text-white group-hover:text-status-info transition-colors">Contrôle Technique</span>
                <span class="text-xs text-carbon-400 mt-1">Vérification véhicule</span>
            </a>

            <a href="{{ route('staff.scan.entry') }}"
               class="group relative flex flex-col items-center p-6 rounded-xl border-2 border-carbon-700 bg-carbon-800/50 hover:border-status-success hover:bg-status-success/10 transition-all duration-200">
                <div class="w-14 h-14 rounded-full bg-status-success/20 flex items-center justify-center mb-3 group-hover:bg-status-success/30 transition-colors">
                    <svg class="w-7 h-7 text-status-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                    </svg>
                </div>
                <span class="text-sm font-semibold text-white group-hover:text-status-success transition-colors">Point Entrée</span>
                <span class="text-xs text-carbon-400 mt-1">Entrée sur le circuit</span>
            </a>

            <a href="{{ route('staff.scan.bracelet') }}"
               class="group relative flex flex-col items-center p-6 rounded-xl border-2 border-carbon-700 bg-carbon-800/50 hover:border-checkered-yellow-500 hover:bg-checkered-yellow-500/10 transition-all duration-200">
                <div class="w-14 h-14 rounded-full bg-checkered-yellow-500/20 flex items-center justify-center mb-3 group-hover:bg-checkered-yellow-500/30 transition-colors">
                    <svg class="w-7 h-7 text-checkered-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                </div>
                <span class="text-sm font-semibold text-white group-hover:text-checkered-yellow-500 transition-colors">Distribution Bracelets</span>
                <span class="text-xs text-carbon-400 mt-1">Remise des bracelets</span>
            </a>
        </div>
    </x-racing.card>
</div>
