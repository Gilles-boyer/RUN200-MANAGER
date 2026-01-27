<div class="space-y-6">
    {{-- Header avec style Racing --}}
    <div class="relative overflow-hidden rounded-xl bg-racing-gradient-subtle p-6 border border-carbon-700">
        {{-- Éléments décoratifs --}}
        <div class="absolute top-0 right-0 w-64 h-64 bg-racing-red-500/5 rounded-full -translate-y-1/2 translate-x-1/2"></div>
        <div class="absolute bottom-0 left-0 w-32 h-32 bg-checkered-yellow-500/5 rounded-full translate-y-1/2 -translate-x-1/2"></div>

        <div class="relative">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 rounded-lg bg-racing-red-500/20 flex items-center justify-center">
                    <svg class="w-6 h-6 text-racing-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-white">Dashboard Staff</h1>
                    <p class="text-carbon-400 text-sm">Gestion des inscriptions et des courses</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if (session()->has('success'))
        <x-racing.alert type="success" :dismissible="true">
            {{ session('success') }}
        </x-racing.alert>
    @endif

    @if (session()->has('error'))
        <x-racing.alert type="danger" :dismissible="true">
            {{ session('error') }}
        </x-racing.alert>
    @endif

    {{-- Statistiques Grid --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
        <x-racing.stat-card
            label="En attente"
            :value="$this->stats['pending_registrations']"
            icon="⏳"
            :highlight="$this->stats['pending_registrations'] > 0"
        />
        <x-racing.stat-card
            label="Acceptées"
            :value="$this->stats['accepted_registrations']"
            icon="✅"
        />
        <x-racing.stat-card
            label="Tech en attente"
            :value="$this->stats['tech_pending']"
            icon="🔧"
        />
        <x-racing.stat-card
            label="Courses ouvertes"
            :value="$this->stats['open_races']"
            icon="🏁"
        />
        <x-racing.stat-card
            label="Courses à venir"
            :value="$this->stats['upcoming_races']"
            icon="📅"
        />
        <x-racing.stat-card
            label="Inscriptions du jour"
            :value="$this->stats['today_registrations']"
            icon="📊"
        />
    </div>

    {{-- Graphiques d'activité --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Activité du jour par heure --}}
        <x-racing.card>
            <div class="p-6">
                <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                    <span>📈</span>
                    Activité du jour
                </h3>
                @if(array_sum($this->todayActivity['data']) > 0)
                    <x-racing.chart
                        id="today-activity"
                        type="bar"
                        height="220px"
                        :labels="$this->todayActivity['labels']"
                        :datasets="[['label' => 'Inscriptions', 'data' => $this->todayActivity['data']]]"
                    />
                @else
                    <div class="h-[220px] flex items-center justify-center">
                        <x-racing.empty-state
                            icon="📊"
                            title="Pas d'activité aujourd'hui"
                            description="Les inscriptions du jour apparaîtront ici."
                            compact
                        />
                    </div>
                @endif
            </div>
        </x-racing.card>

        {{-- Activité de la semaine --}}
        <x-racing.card>
            <div class="p-6">
                <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                    <span>📊</span>
                    Activité de la semaine
                </h3>
                @if(count($this->weeklyActivity['labels']) > 0)
                    <x-racing.chart
                        id="weekly-activity"
                        type="line"
                        height="220px"
                        :labels="$this->weeklyActivity['labels']"
                        :datasets="[['label' => 'Inscriptions', 'data' => $this->weeklyActivity['data']]]"
                    />
                @else
                    <div class="h-[220px] flex items-center justify-center">
                        <x-racing.empty-state
                            icon="📈"
                            title="Pas assez de données"
                            description="L'historique des 7 derniers jours apparaîtra ici."
                            compact
                        />
                    </div>
                @endif
            </div>
        </x-racing.card>
    </div>

    {{-- Checkpoints du jour --}}
    @if(count($this->checkpointStats['labels']) > 0)
    <x-racing.card>
        <div class="p-6">
            <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                <span>📍</span>
                Passages checkpoints du jour
            </h3>
            <x-racing.chart
                id="checkpoint-stats"
                type="bar"
                height="180px"
                :labels="$this->checkpointStats['labels']"
                :datasets="[['label' => 'Passages', 'data' => $this->checkpointStats['data']]]"
            />
        </div>
    </x-racing.card>
    @endif

    {{-- Prochaines courses --}}
    <x-racing.card title="Prochaines courses" icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/></svg>'>
        <div class="divide-y divide-carbon-700/50">
            @forelse($this->upcomingRaces as $race)
                <div class="flex items-center justify-between py-4 first:pt-0 last:pb-0">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-lg bg-carbon-700/50 flex items-center justify-center">
                            <span class="text-lg font-bold text-racing-red-500">{{ $race->race_date->format('d') }}</span>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-white">{{ $race->name }}</h3>
                            <p class="text-xs text-carbon-400">
                                {{ $race->race_date->format('d/m/Y') }} • {{ $race->location }}
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <x-racing.badge-status :status="$race->status" />
                        <a href="{{ route('staff.registrations.index', ['raceId' => $race->id]) }}"
                           class="text-sm text-racing-red-500 hover:text-racing-red-400 font-medium transition-colors">
                            Inscriptions →
                        </a>
                    </div>
                </div>
            @empty
                <x-racing.empty-state
                    title="Aucune course à venir"
                    description="Il n'y a pas de courses planifiées pour le moment."
                    icon='<svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>'
                    compact
                />
            @endforelse
        </div>
    </x-racing.card>

    {{-- Scanners Checkpoints --}}
    <x-racing.card title="Scanners Checkpoints" subtitle="Pointez les pilotes selon votre rôle" icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>'>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @can('checkpoint.scan.admin_check')
            <a href="{{ route('staff.scan.admin') }}"
               class="group relative flex flex-col items-center p-6 rounded-xl border-2 border-carbon-700 bg-carbon-800/50 hover:border-status-info hover:bg-status-info/10 transition-all duration-200">
                <div class="w-14 h-14 rounded-full bg-status-info/20 flex items-center justify-center mb-3 group-hover:bg-status-info/30 transition-colors">
                    <svg class="w-7 h-7 text-status-info" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <span class="text-sm font-semibold text-white group-hover:text-status-info transition-colors">Vérification Admin</span>
                <span class="text-xs text-carbon-400 mt-1">Validation administrative</span>
            </a>
            @endcan

            @can('checkpoint.scan.tech_check')
            <a href="{{ route('staff.scan.tech') }}"
               class="group relative flex flex-col items-center p-6 rounded-xl border-2 border-carbon-700 bg-carbon-800/50 hover:border-checkered-yellow-500 hover:bg-checkered-yellow-500/10 transition-all duration-200">
                <div class="w-14 h-14 rounded-full bg-checkered-yellow-500/20 flex items-center justify-center mb-3 group-hover:bg-checkered-yellow-500/30 transition-colors">
                    <svg class="w-7 h-7 text-checkered-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <span class="text-sm font-semibold text-white group-hover:text-checkered-yellow-500 transition-colors">Contrôle Technique</span>
                <span class="text-xs text-carbon-400 mt-1">Vérification véhicule</span>
            </a>
            @endcan

            @can('checkpoint.scan.entry')
            <a href="{{ route('staff.scan.entry') }}"
               class="group relative flex flex-col items-center p-6 rounded-xl border-2 border-carbon-700 bg-carbon-800/50 hover:border-status-success hover:bg-status-success/10 transition-all duration-200">
                <div class="w-14 h-14 rounded-full bg-status-success/20 flex items-center justify-center mb-3 group-hover:bg-status-success/30 transition-colors">
                    <svg class="w-7 h-7 text-status-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                    </svg>
                </div>
                <span class="text-sm font-semibold text-white group-hover:text-status-success transition-colors">Entrée Circuit</span>
                <span class="text-xs text-carbon-400 mt-1">Accès au paddock</span>
            </a>
            @endcan

            @can('checkpoint.scan.bracelet')
            <a href="{{ route('staff.scan.bracelet') }}"
               class="group relative flex flex-col items-center p-6 rounded-xl border-2 border-carbon-700 bg-carbon-800/50 hover:border-purple-500 hover:bg-purple-500/10 transition-all duration-200">
                <div class="w-14 h-14 rounded-full bg-purple-500/20 flex items-center justify-center mb-3 group-hover:bg-purple-500/30 transition-colors">
                    <svg class="w-7 h-7 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/>
                    </svg>
                </div>
                <span class="text-sm font-semibold text-white group-hover:text-purple-500 transition-colors">Remise Bracelet</span>
                <span class="text-xs text-carbon-400 mt-1">Distribution bracelets</span>
            </a>
            @endcan
        </div>

        @if(!auth()->user()->can('checkpoint.scan.admin_check') && !auth()->user()->can('checkpoint.scan.tech_check') && !auth()->user()->can('checkpoint.scan.entry') && !auth()->user()->can('checkpoint.scan.bracelet'))
        <x-racing.empty-state
            title="Aucune permission de scan"
            description="Vous n'avez pas de permission de scan attribuée. Contactez un administrateur pour obtenir les permissions nécessaires."
            icon='<svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>'
        />
        @endif
    </x-racing.card>

    {{-- Actions rapides --}}
    <x-racing.card title="Actions rapides" icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>'>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @can('registration.manage')
            <a href="{{ route('staff.registrations.engagement') }}"
               class="group flex items-center gap-4 p-4 rounded-xl border border-dashed border-carbon-600 hover:border-racing-red-500/50 hover:bg-racing-red-500/5 transition-all duration-200">
                <div class="w-12 h-12 rounded-lg bg-racing-red-500/20 flex items-center justify-center group-hover:bg-racing-red-500/30 transition-colors">
                    <svg class="w-6 h-6 text-racing-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-white group-hover:text-racing-red-500 transition-colors">Feuilles d'engagement</p>
                    <p class="text-xs text-carbon-400">Signature électronique</p>
                </div>
            </a>

            <a href="{{ route('staff.registrations.walk-in') }}"
               class="group flex items-center gap-4 p-4 rounded-xl border border-dashed border-carbon-600 hover:border-status-info/50 hover:bg-status-info/5 transition-all duration-200">
                <div class="w-12 h-12 rounded-lg bg-status-info/20 flex items-center justify-center group-hover:bg-status-info/30 transition-colors">
                    <svg class="w-6 h-6 text-status-info" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-white group-hover:text-status-info transition-colors">Inscription sur place</p>
                    <p class="text-xs text-carbon-400">Nouveau pilote walk-in</p>
                </div>
            </a>
            @endcan

            <a href="{{ route('staff.registrations.index', ['statusFilter' => 'PENDING_VALIDATION']) }}"
               class="group flex items-center gap-4 p-4 rounded-xl border border-dashed border-carbon-600 hover:border-status-warning/50 hover:bg-status-warning/5 transition-all duration-200">
                <div class="w-12 h-12 rounded-lg bg-status-warning/20 flex items-center justify-center group-hover:bg-status-warning/30 transition-colors">
                    <svg class="w-6 h-6 text-status-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-white group-hover:text-status-warning transition-colors">Valider inscriptions</p>
                    <p class="text-xs text-carbon-400">{{ $this->stats['pending_registrations'] }} en attente</p>
                </div>
            </a>

            <a href="{{ route('staff.races.index') }}"
               class="group flex items-center gap-4 p-4 rounded-xl border border-dashed border-carbon-600 hover:border-checkered-yellow-500/50 hover:bg-checkered-yellow-500/5 transition-all duration-200">
                <div class="w-12 h-12 rounded-lg bg-checkered-yellow-500/20 flex items-center justify-center group-hover:bg-checkered-yellow-500/30 transition-colors">
                    <svg class="w-6 h-6 text-checkered-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-white group-hover:text-checkered-yellow-500 transition-colors">Export PDF</p>
                    <p class="text-xs text-carbon-400">Liste des engagés</p>
                </div>
            </a>

            <a href="{{ route('staff.registrations.index') }}"
               class="group flex items-center gap-4 p-4 rounded-xl border border-dashed border-carbon-600 hover:border-status-success/50 hover:bg-status-success/5 transition-all duration-200">
                <div class="w-12 h-12 rounded-lg bg-status-success/20 flex items-center justify-center group-hover:bg-status-success/30 transition-colors">
                    <svg class="w-6 h-6 text-status-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-white group-hover:text-status-success transition-colors">Toutes inscriptions</p>
                    <p class="text-xs text-carbon-400">Gérer les inscriptions</p>
                </div>
            </a>

            <a href="{{ route('staff.pilots.index') }}"
               class="group flex items-center gap-4 p-4 rounded-xl border border-dashed border-carbon-600 hover:border-purple-500/50 hover:bg-purple-500/5 transition-all duration-200">
                <div class="w-12 h-12 rounded-lg bg-purple-500/20 flex items-center justify-center group-hover:bg-purple-500/30 transition-colors">
                    <svg class="w-6 h-6 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-white group-hover:text-purple-500 transition-colors">Gestion Pilotes</p>
                    <p class="text-xs text-carbon-400">Voir/modifier les fiches</p>
                </div>
            </a>
        </div>
    </x-racing.card>
</div>
