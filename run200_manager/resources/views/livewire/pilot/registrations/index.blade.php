<div>
    {{-- Header avec gradient --}}
    <div class="relative mb-8 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-8 bg-racing-gradient-subtle overflow-hidden">
        <div class="absolute top-0 right-0 w-64 h-64 bg-racing-red-500/5 rounded-full -translate-y-1/2 translate-x-1/2"></div>

        <div class="relative flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-carbon-900 dark:text-white flex items-center gap-3">
                    <span>📋</span> Mes Inscriptions
                </h1>
                <p class="mt-2 text-carbon-600 dark:text-carbon-400">
                    Suivez l'état de vos inscriptions aux courses
                </p>
            </div>
            <x-racing.button href="{{ route('pilot.races.index') }}" class="self-start sm:self-auto">
                + Nouvelle inscription
            </x-racing.button>
        </div>
    </div>

    @if (!$pilot)
        {{-- Alerte profil manquant --}}
        <x-racing.alert type="warning" class="mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <p class="font-semibold">Profil pilote requis</p>
                    <p class="text-sm mt-1 opacity-90">Vous devez créer votre profil pilote pour voir vos inscriptions.</p>
                </div>
                <x-racing.button href="{{ route('pilot.profile.edit') }}" variant="secondary" size="sm">
                    Créer mon profil
                </x-racing.button>
            </div>
        </x-racing.alert>
    @else
        {{-- Filtres --}}
        <x-racing.card class="mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-racing.form.select
                    wire:model.live="statusFilter"
                    label="Filtrer par statut"
                >
                    <option value="">Tous les statuts</option>
                    <option value="PENDING_PAYMENT">💳 En attente de paiement</option>
                    <option value="PENDING_VALIDATION">⏳ En attente de validation</option>
                    <option value="ACCEPTED">✅ Acceptée</option>
                    <option value="REFUSED">❌ Refusée</option>
                    <option value="CANCELLED">🚫 Annulée</option>
                    <option value="TECH_CHECKED_OK">🔧 Contrôle technique OK</option>
                    <option value="TECH_CHECKED_FAIL">⚠️ Contrôle technique refusé</option>
                    <option value="RACE_READY">🏁 Prêt à courir</option>
                </x-racing.form.select>
            </div>
        </x-racing.card>

        {{-- Liste des inscriptions --}}
        @if($registrations->isEmpty())
            <x-racing.card>
                <x-racing.empty-state
                    icon="📋"
                    title="Aucune inscription"
                    description="Vous n'avez pas encore d'inscription à une course."
                    actionLabel="Voir les courses disponibles"
                    actionHref="{{ route('pilot.races.index') }}"
                />
            </x-racing.card>
        @else
            <div class="space-y-4">
                @foreach($registrations as $registration)
                    @php
                        $paidPayment = $registration->payments->where('status', 'paid')->first();
                    @endphp
                    <x-racing.card class="hover:shadow-lg transition-shadow">
                        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
                            {{-- Infos principales --}}
                            <div class="flex-1 min-w-0">
                                {{-- Titre et statut --}}
                                <div class="flex flex-wrap items-center gap-3 mb-3">
                                    <h3 class="text-lg font-semibold text-carbon-900 dark:text-white truncate">
                                        {{ $registration->race->name ?? 'Course inconnue' }}
                                    </h3>
                                    <x-racing.badge-status :status="$registration->status" />
                                    @if($paidPayment)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-status-success/10 text-status-success">
                                            ✓ Payé
                                        </span>
                                    @endif
                                </div>

                                {{-- Détails --}}
                                <div class="flex flex-wrap items-center gap-4 text-sm text-carbon-500 dark:text-carbon-400">
                                    @if($registration->race)
                                        <span class="flex items-center gap-1.5">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                            {{ $registration->race->race_date->format('d/m/Y') }}
                                        </span>
                                        <span class="flex items-center gap-1.5">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                            </svg>
                                            {{ $registration->race->location }}
                                        </span>
                                    @endif
                                    @if($registration->car)
                                        <span class="flex items-center gap-1.5">
                                            🚗 {{ $registration->car->brand }} {{ $registration->car->model }}
                                        </span>
                                    @endif
                                    <span class="flex items-center gap-1.5 text-carbon-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ $registration->created_at->format('d/m/Y') }}
                                    </span>
                                </div>

                                {{-- Paddock si assigné --}}
                                @if($registration->paddockSpot)
                                    <div class="mt-3">
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                                            </svg>
                                            Paddock : {{ $registration->paddockSpot->full_name }}
                                        </span>
                                    </div>
                                @endif
                            </div>

                            {{-- Actions --}}
                            <div class="flex flex-wrap lg:flex-col gap-2 lg:items-end">
                                @if($registration->status === 'PENDING_PAYMENT' && !$paidPayment)
                                    <x-racing.button href="{{ route('pilot.registrations.payment', $registration) }}" size="sm" variant="warning">
                                        💳 Payer maintenant
                                    </x-racing.button>
                                @endif

                                @if(in_array($registration->status, ['ACCEPTED', 'TECH_CHECKED_OK', 'RACE_READY']))
                                    <x-racing.button href="{{ route('pilot.registrations.paddock.select', $registration) }}" size="sm" variant="secondary">
                                        📍 {{ $registration->paddockSpot ? 'Changer paddock' : 'Choisir paddock' }}
                                    </x-racing.button>

                                    <x-racing.button href="{{ route('pilot.registrations.ecard', $registration) }}" size="sm" variant="outline">
                                        📱 E-Card
                                    </x-racing.button>
                                @endif

                                @if(in_array($registration->status, ['ACCEPTED', 'TECH_CHECKED_OK']) && !$paidPayment)
                                    <x-racing.button href="{{ route('pilot.registrations.payment', $registration) }}" size="sm">
                                        💳 Payer
                                    </x-racing.button>
                                @endif
                            </div>
                        </div>

                        {{-- Raison de refus si applicable --}}
                        @if($registration->status === 'REFUSED' && $registration->reason)
                            <div class="mt-4 p-4 bg-status-danger/10 rounded-xl border border-status-danger/20">
                                <p class="text-sm text-status-danger">
                                    <strong>Raison du refus :</strong> {{ $registration->reason }}
                                </p>
                            </div>
                        @endif
                    </x-racing.card>
                @endforeach
            </div>

            {{-- Pagination --}}
            <div class="mt-6">
                {{ $registrations->links() }}
            </div>
        @endif
    @endif
</div>
