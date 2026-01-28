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
                        $paidPayment = $registration->payments->first(fn($p) => $p->isPaid());
                        $pendingPayment = $registration->payments->first(fn($p) => $p->isPending());
                        $latestPayment = $registration->payments->sortByDesc('created_at')->first();
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

                                {{-- Section Paiement détaillée --}}
                                <div class="mt-4 p-4 rounded-xl border {{ $paidPayment ? 'bg-status-success/5 border-status-success/20' : ($pendingPayment ? 'bg-status-warning/5 border-status-warning/20' : 'bg-carbon-700/30 border-carbon-700/50') }}">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="text-lg">💳</span>
                                        <h4 class="font-medium {{ $paidPayment ? 'text-status-success' : ($pendingPayment ? 'text-status-warning' : 'text-carbon-400') }}">
                                            @if($paidPayment)
                                                Paiement effectué
                                            @elseif($pendingPayment)
                                                Paiement en attente
                                            @else
                                                Paiement requis
                                            @endif
                                        </h4>
                                    </div>

                                    @if($latestPayment)
                                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-sm">
                                            {{-- Montant --}}
                                            <div>
                                                <span class="text-carbon-500 dark:text-carbon-400 block text-xs">Montant</span>
                                                <span class="font-bold text-white">{{ $latestPayment->formatted_amount }}</span>
                                            </div>

                                            {{-- Méthode --}}
                                            <div>
                                                <span class="text-carbon-500 dark:text-carbon-400 block text-xs">Méthode</span>
                                                <span class="font-medium text-carbon-200">
                                                    @php
                                                        $methodIcons = [
                                                            'cash' => '💵',
                                                            'stripe' => '💳',
                                                            'bank_transfer' => '🏦',
                                                            'card_onsite' => '💳',
                                                            'manual' => '✋',
                                                        ];
                                                        $methodLabels = [
                                                            'cash' => 'Espèces',
                                                            'stripe' => 'Carte en ligne',
                                                            'bank_transfer' => 'Virement',
                                                            'card_onsite' => 'CB sur place',
                                                            'manual' => 'Manuel',
                                                        ];
                                                        $method = $latestPayment->method->value ?? 'unknown';
                                                    @endphp
                                                    {{ $methodIcons[$method] ?? '❓' }} {{ $methodLabels[$method] ?? $latestPayment->method_label }}
                                                </span>
                                            </div>

                                            {{-- Statut --}}
                                            <div>
                                                <span class="text-carbon-500 dark:text-carbon-400 block text-xs">Statut</span>
                                                @if($latestPayment->isPaid())
                                                    <span class="inline-flex items-center gap-1 text-status-success font-medium">
                                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                        </svg>
                                                        Payé
                                                    </span>
                                                @elseif($latestPayment->isPending())
                                                    <span class="inline-flex items-center gap-1 text-status-warning font-medium">
                                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                                        </svg>
                                                        En attente
                                                    </span>
                                                @elseif($latestPayment->isFailed())
                                                    <span class="inline-flex items-center gap-1 text-status-danger font-medium">
                                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                                        </svg>
                                                        Échoué
                                                    </span>
                                                @elseif($latestPayment->isRefunded())
                                                    <span class="inline-flex items-center gap-1 text-purple-400 font-medium">
                                                        ↩️ Remboursé
                                                    </span>
                                                @else
                                                    <span class="text-carbon-400">{{ $latestPayment->status_label }}</span>
                                                @endif
                                            </div>

                                            {{-- Date --}}
                                            <div>
                                                <span class="text-carbon-500 dark:text-carbon-400 block text-xs">
                                                    {{ $latestPayment->isPaid() ? 'Payé le' : 'Créé le' }}
                                                </span>
                                                <span class="text-carbon-200">
                                                    {{ ($latestPayment->paid_at ?? $latestPayment->created_at)->format('d/m/Y H:i') }}
                                                </span>
                                            </div>
                                        </div>

                                        {{-- Notes de paiement si présentes --}}
                                        @if($latestPayment->metadata && isset($latestPayment->metadata['notes']) && $latestPayment->metadata['notes'])
                                            <div class="mt-3 pt-3 border-t border-carbon-700/30 text-sm text-carbon-400">
                                                <span class="text-carbon-500">📝 Note :</span> {{ $latestPayment->metadata['notes'] }}
                                            </div>
                                        @endif
                                    @else
                                        <p class="text-sm text-carbon-400">
                                            Aucun paiement enregistré.
                                            @if($registration->race && $registration->race->entry_fee)
                                                Montant dû : <span class="font-bold text-checkered-yellow-500">{{ number_format($registration->race->entry_fee, 2) }} €</span>
                                            @endif
                                        </p>
                                    @endif
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
