<div class="space-y-6">
    {{-- Header avec style Racing --}}
    <div class="relative overflow-hidden rounded-xl bg-racing-gradient-subtle p-6 border border-carbon-700">
        <div class="absolute top-0 right-0 w-64 h-64 bg-racing-red-500/5 rounded-full -translate-y-1/2 translate-x-1/2"></div>

        <div class="relative flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-racing-red-500/20 flex items-center justify-center">
                    <svg class="w-6 h-6 text-racing-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-white">Gestion des Inscriptions</h1>
                    <p class="text-carbon-400 text-sm">Valider les inscriptions et assigner les paddocks</p>
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

    {{-- Filtres --}}
    <x-racing.card>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <x-racing.form.select
                wire:model.live="raceId"
                label="Course"
                :options="$races->pluck('name', 'id')->prepend('Toutes les courses', '')"
            />
            <x-racing.form.select
                wire:model.live="statusFilter"
                label="Statut"
                :options="[
                    '' => 'Tous les statuts',
                    'SUBMITTED' => 'Soumise',
                    'PENDING_VALIDATION' => 'En attente de validation',
                    'ACCEPTED' => 'Acceptée',
                    'REFUSED' => 'Refusée',
                    'ADMIN_CHECKED' => 'Validation administrative',
                    'TECH_CHECKED_OK' => 'Contrôle technique OK',
                    'TECH_CHECKED_FAIL' => 'Contrôle technique échoué',
                    'ENTRY_SCANNED' => 'Entrée effectuée',
                    'BRACELET_GIVEN' => 'Bracelet remis',
                    'RESULTS_IMPORTED' => 'Résultats importés',
                    'PUBLISHED' => 'Résultats publiés',
                ]"
            />
            <x-racing.form.input
                wire:model.live.debounce.300ms="search"
                label="Recherche"
                placeholder="Nom, prénom, licence..."
                icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>'
            />
        </div>
    </x-racing.card>

    {{-- Tableau des inscriptions --}}
    <x-racing.card>
        {{-- Version Desktop --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="table-racing w-full">
                <thead>
                    <tr class="border-b border-carbon-700">
                        <th class="px-4 py-3 text-left text-xs font-semibold text-carbon-400 uppercase tracking-wider">Pilote</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-carbon-400 uppercase tracking-wider">Course</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-carbon-400 uppercase tracking-wider">Voiture</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-carbon-400 uppercase tracking-wider">Statut</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-carbon-400 uppercase tracking-wider">Paiement</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-carbon-400 uppercase tracking-wider">Étapes</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-carbon-400 uppercase tracking-wider">Paddock</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-carbon-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-carbon-700/50">
                    @forelse($registrations as $registration)
                        <tr class="hover:bg-carbon-800/50 transition-colors">
                            <td class="px-4 py-4">
                                <div class="text-sm font-medium text-white">
                                    {{ $registration->pilot->last_name }} {{ $registration->pilot->first_name }}
                                </div>
                                <div class="text-xs text-carbon-400">
                                    Licence: {{ $registration->pilot->license_number }}
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                <div class="text-sm text-white">{{ $registration->race->name }}</div>
                                <div class="text-xs text-carbon-400">{{ $registration->race->race_date->format('d/m/Y') }}</div>
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-racing-red-500/20 text-racing-red-500 text-xs font-bold">
                                        {{ $registration->car->race_number }}
                                    </span>
                                    <div>
                                        <div class="text-sm text-white">{{ $registration->car->make }} {{ $registration->car->model }}</div>
                                        <div class="text-xs text-carbon-400">{{ $registration->car->category->name ?? 'N/A' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                <button wire:click="openStatusModal({{ $registration->id }})"
                                        class="group flex items-center gap-2 hover:bg-carbon-700/50 rounded-lg px-2 py-1 -mx-2 -my-1 transition-colors"
                                        title="Cliquer pour modifier le statut">
                                    <x-racing.badge-status :status="$registration->status" />
                                    <svg class="w-4 h-4 text-carbon-500 group-hover:text-racing-red-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                    </svg>
                                </button>
                                @if($registration->reason)
                                    <p class="mt-1 text-xs text-carbon-400 max-w-xs truncate" title="{{ $registration->reason }}">
                                        {{ $registration->reason }}
                                    </p>
                                @endif
                            </td>
                            {{-- Colonne Paiement --}}
                            <td class="px-4 py-4">
                                @php
                                    $latestPayment = $registration->payments->sortByDesc('created_at')->first();
                                @endphp
                                @if($latestPayment)
                                    <div class="space-y-1">
                                        {{-- Statut du paiement --}}
                                        @if($latestPayment->isPaid())
                                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-xs font-medium bg-status-success/20 text-status-success">
                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                </svg>
                                                Payé
                                            </span>
                                        @elseif($latestPayment->isPending())
                                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-xs font-medium bg-status-warning/20 text-status-warning">
                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                                </svg>
                                                En attente
                                            </span>
                                        @elseif($latestPayment->isFailed())
                                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-xs font-medium bg-status-danger/20 text-status-danger">
                                                ✗ Échoué
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-1 rounded-lg text-xs font-medium bg-carbon-700 text-carbon-400">
                                                {{ $latestPayment->status_label }}
                                            </span>
                                        @endif
                                        {{-- Méthode et montant --}}
                                        <div class="text-xs text-carbon-400">
                                            @php
                                                $methodIcons = [
                                                    'cash' => '💵',
                                                    'stripe' => '💳',
                                                    'bank_transfer' => '🏦',
                                                    'card_onsite' => '💳',
                                                    'manual' => '✋',
                                                ];
                                            @endphp
                                            {{ $methodIcons[$latestPayment->method->value] ?? '❓' }}
                                            {{ $latestPayment->formatted_amount }}
                                        </div>
                                    </div>
                                @else
                                    <span class="inline-flex items-center px-2 py-1 rounded-lg text-xs font-medium bg-carbon-700/50 text-carbon-500">
                                        Aucun paiement
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-4">
                                @php
                                    $passedCheckpoints = $registration->passages->pluck('checkpoint_id')->toArray();
                                    $totalCheckpoints = $this->checkpoints->count();
                                    $passedCount = count($passedCheckpoints);
                                @endphp
                                <a href="{{ route('staff.registrations.checkpoints', $registration) }}"
                                   class="inline-flex items-center gap-2 group">
                                    <div class="flex items-center gap-1">
                                        @foreach($this->checkpoints as $checkpoint)
                                            @php
                                                $isPassed = in_array($checkpoint->id, $passedCheckpoints);
                                            @endphp
                                            <div class="w-3 h-3 rounded-full {{ $isPassed ? 'bg-status-success' : 'bg-carbon-600' }}"
                                                 title="{{ $checkpoint->name }}: {{ $isPassed ? 'Validé' : 'En attente' }}">
                                            </div>
                                        @endforeach
                                    </div>
                                    <span class="text-xs text-carbon-400 group-hover:text-racing-red-500 transition-colors">
                                        {{ $passedCount }}/{{ $totalCheckpoints }}
                                    </span>
                                    <svg class="w-4 h-4 text-carbon-500 group-hover:text-racing-red-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </a>
                            </td>
                            <td class="px-4 py-4">
                                @if($registration->paddock)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-status-info/20 text-status-info text-xs font-medium">
                                        {{ $registration->paddock }}
                                    </span>
                                @else
                                    <span class="text-carbon-500">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    {{-- Bouton voir les étapes --}}
                                    <a href="{{ route('staff.registrations.checkpoints', $registration) }}"
                                       class="p-2 rounded-lg text-carbon-400 hover:text-white hover:bg-carbon-700 transition-colors"
                                       title="Voir les étapes">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                                        </svg>
                                    </a>

                                    {{-- Bouton gérer les paiements --}}
                                    @can('payment.manage')
                                        <a href="{{ route('staff.registrations.payments', $registration) }}"
                                           class="p-2 rounded-lg text-status-warning hover:bg-status-warning/20 transition-colors"
                                           title="Gérer les paiements">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </a>
                                    @endcan

                                    @if($registration->isPending())
                                        <button wire:click="openValidationModal({{ $registration->id }}, 'accept')"
                                                class="p-2 rounded-lg text-status-success hover:bg-status-success/20 transition-colors"
                                                title="Accepter">
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                            </svg>
                                        </button>
                                        <button wire:click="openValidationModal({{ $registration->id }}, 'refuse')"
                                                class="p-2 rounded-lg text-status-danger hover:bg-status-danger/20 transition-colors"
                                                title="Refuser">
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                            </svg>
                                        </button>
                                    @endif

                                    @if($registration->isAccepted())
                                        <button wire:click="openPaddockModal({{ $registration->id }})"
                                                class="p-2 rounded-lg text-status-info hover:bg-status-info/20 transition-colors"
                                                title="Assigner paddock">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12">
                                <x-racing.empty-state
                                    title="Aucune inscription trouvée"
                                    description="Modifiez vos filtres ou attendez de nouvelles inscriptions."
                                    icon='<svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>'
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Version Mobile (Cards) --}}
        <div class="md:hidden space-y-4">
            @forelse($registrations as $registration)
                <div class="bg-carbon-800/50 rounded-xl border border-carbon-700 overflow-hidden">
                    {{-- Header de la carte --}}
                    <div class="p-4 bg-carbon-800 border-b border-carbon-700 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-racing-red-500/20 text-racing-red-500 text-sm font-bold">
                                {{ $registration->car->race_number }}
                            </span>
                            <div>
                                <div class="text-sm font-medium text-white">
                                    {{ $registration->pilot->last_name }} {{ $registration->pilot->first_name }}
                                </div>
                                <div class="text-xs text-carbon-400">
                                    {{ $registration->car->make }} {{ $registration->car->model }}
                                </div>
                            </div>
                        </div>
                        <button wire:click="openStatusModal({{ $registration->id }})"
                                class="group flex items-center gap-1"
                                title="Cliquer pour modifier le statut">
                            <x-racing.badge-status :status="$registration->status" />
                            <svg class="w-3 h-3 text-carbon-500 group-hover:text-racing-red-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                            </svg>
                        </button>
                    </div>

                    {{-- Contenu --}}
                    <div class="p-4 space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-carbon-400 uppercase tracking-wider">Course</span>
                            <span class="text-sm text-white">{{ $registration->race->name }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-carbon-400 uppercase tracking-wider">Date</span>
                            <span class="text-sm text-white">{{ $registration->race->race_date->format('d/m/Y') }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-carbon-400 uppercase tracking-wider">Licence</span>
                            <span class="text-sm text-white">{{ $registration->pilot->license_number }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-carbon-400 uppercase tracking-wider">Catégorie</span>
                            <span class="text-sm text-white">{{ $registration->car->category->name ?? 'N/A' }}</span>
                        </div>
                        @if($registration->paddock)
                            <div class="flex justify-between items-center">
                                <span class="text-xs text-carbon-400 uppercase tracking-wider">Paddock</span>
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-status-info/20 text-status-info text-xs font-medium">
                                    {{ $registration->paddock }}
                                </span>
                            </div>
                        @endif

                        {{-- Paiement (Mobile) --}}
                        @php
                            $latestPayment = $registration->payments->sortByDesc('created_at')->first();
                        @endphp
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-carbon-400 uppercase tracking-wider">Paiement</span>
                            @if($latestPayment)
                                <div class="flex items-center gap-2">
                                    @if($latestPayment->isPaid())
                                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-xs font-medium bg-status-success/20 text-status-success">
                                            ✓ {{ $latestPayment->formatted_amount }}
                                        </span>
                                    @elseif($latestPayment->isPending())
                                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-xs font-medium bg-status-warning/20 text-status-warning">
                                            ⏳ En attente
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-1 rounded-lg text-xs font-medium bg-carbon-700 text-carbon-400">
                                            {{ $latestPayment->status_label }}
                                        </span>
                                    @endif
                                </div>
                            @else
                                <span class="text-xs text-carbon-500">Aucun</span>
                            @endif
                        </div>

                        {{-- Étapes --}}
                        @php
                            $passedCheckpoints = $registration->passages->pluck('checkpoint_id')->toArray();
                            $totalCheckpoints = $this->checkpoints->count();
                            $passedCount = count($passedCheckpoints);
                        @endphp
                        <div class="pt-2 border-t border-carbon-700">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-xs text-carbon-400 uppercase tracking-wider">Progression</span>
                                <span class="text-xs text-carbon-400">{{ $passedCount }}/{{ $totalCheckpoints }}</span>
                            </div>
                            <div class="flex items-center gap-1">
                                @foreach($this->checkpoints as $checkpoint)
                                    @php
                                        $isPassed = in_array($checkpoint->id, $passedCheckpoints);
                                    @endphp
                                    <div class="flex-1 h-2 rounded-full {{ $isPassed ? 'bg-status-success' : 'bg-carbon-600' }}"></div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="p-4 bg-carbon-800/50 border-t border-carbon-700 flex flex-wrap gap-2">
                        <a href="{{ route('staff.registrations.checkpoints', $registration) }}"
                           class="flex-1 flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-carbon-700 text-white text-sm font-medium hover:bg-carbon-600 transition-colors tap-target">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            Étapes
                        </a>
                        @can('payment.manage')
                            <a href="{{ route('staff.registrations.payments', $registration) }}"
                               class="flex-1 flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-status-warning/20 text-status-warning text-sm font-medium hover:bg-status-warning/30 transition-colors tap-target">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Paiements
                            </a>
                        @endcan
                        <button wire:click="openStatusModal({{ $registration->id }})"
                                class="flex-1 flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-racing-red-500/20 text-racing-red-500 text-sm font-medium hover:bg-racing-red-500/30 transition-colors tap-target">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Statut
                        </button>
                        @if($registration->isPending())
                            <button wire:click="openValidationModal({{ $registration->id }}, 'accept')"
                                    class="flex-1 flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-status-success/20 text-status-success text-sm font-medium hover:bg-status-success/30 transition-colors tap-target">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                Accepter
                            </button>
                            <button wire:click="openValidationModal({{ $registration->id }}, 'refuse')"
                                    class="flex-1 flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-status-danger/20 text-status-danger text-sm font-medium hover:bg-status-danger/30 transition-colors tap-target">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                </svg>
                                Refuser
                            </button>
                        @endif
                        @if($registration->isAccepted())
                            <button wire:click="openPaddockModal({{ $registration->id }})"
                                    class="flex-1 flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-status-info/20 text-status-info text-sm font-medium hover:bg-status-info/30 transition-colors tap-target">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                </svg>
                                Paddock
                            </button>
                        @endif
                    </div>
                </div>
            @empty
                <x-racing.empty-state
                    title="Aucune inscription trouvée"
                    description="Modifiez vos filtres ou attendez de nouvelles inscriptions."
                    icon='<svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>'
                />
            @endforelse
        </div>

        {{-- Pagination --}}
        @if($registrations->hasPages())
            <div class="mt-6 pt-6 border-t border-carbon-700">
                {{ $registrations->links() }}
            </div>
        @endif
    </x-racing.card>

    {{-- Modal Validation --}}
    @if($showValidationModal && $selectedRegistration)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="fixed inset-0 bg-carbon-950/80 backdrop-blur-sm transition-opacity" wire:click="closeValidationModal"></div>

                <div class="relative bg-carbon-800 rounded-xl border border-carbon-700 shadow-2xl transform transition-all sm:max-w-lg w-full">
                    <div class="p-6">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-10 h-10 rounded-lg {{ $validationAction === 'accept' ? 'bg-status-success/20' : 'bg-status-danger/20' }} flex items-center justify-center">
                                @if($validationAction === 'accept')
                                    <svg class="w-6 h-6 text-status-success" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                @else
                                    <svg class="w-6 h-6 text-status-danger" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                    </svg>
                                @endif
                            </div>
                            <h3 class="text-lg font-semibold text-white">
                                {{ $validationAction === 'accept' ? 'Accepter l\'inscription' : 'Refuser l\'inscription' }}
                            </h3>
                        </div>

                        <div class="mb-6 p-4 bg-carbon-700/50 rounded-xl border border-carbon-600">
                            <div class="space-y-2">
                                <p class="text-sm text-carbon-300">
                                    <span class="text-carbon-400">Pilote:</span>
                                    <span class="text-white font-medium">{{ $selectedRegistration->pilot->last_name }} {{ $selectedRegistration->pilot->first_name }}</span>
                                </p>
                                <p class="text-sm text-carbon-300">
                                    <span class="text-carbon-400">Course:</span>
                                    <span class="text-white">{{ $selectedRegistration->race->name }}</span>
                                </p>
                                <p class="text-sm text-carbon-300">
                                    <span class="text-carbon-400">Voiture:</span>
                                    <span class="text-white">#{{ $selectedRegistration->car->race_number }} - {{ $selectedRegistration->car->make }} {{ $selectedRegistration->car->model }}</span>
                                </p>
                            </div>
                        </div>

                        @if($validationAction === 'refuse')
                            <x-racing.form.textarea
                                wire:model="refusalReason"
                                label="Raison du refus"
                                required
                                rows="3"
                                placeholder="Indiquez la raison du refus..."
                            />
                        @endif
                    </div>

                    <div class="flex items-center justify-end gap-3 px-6 py-4 bg-carbon-900/50 border-t border-carbon-700 rounded-b-xl">
                        <x-racing.button variant="outline" wire:click="closeValidationModal">
                            Annuler
                        </x-racing.button>
                        <x-racing.button
                            wire:click="confirmValidation"
                            variant="{{ $validationAction === 'accept' ? 'primary' : 'danger' }}"
                        >
                            {{ $validationAction === 'accept' ? 'Accepter' : 'Refuser' }}
                        </x-racing.button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal Paddock --}}
    @if($showPaddockModal && $selectedRegistration)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="fixed inset-0 bg-carbon-950/80 backdrop-blur-sm transition-opacity" wire:click="closePaddockModal"></div>

                <div class="relative bg-carbon-800 rounded-xl border border-carbon-700 shadow-2xl transform transition-all sm:max-w-lg w-full">
                    <div class="p-6">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-10 h-10 rounded-lg bg-status-info/20 flex items-center justify-center">
                                <svg class="w-6 h-6 text-status-info" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-white">Assigner un paddock</h3>
                        </div>

                        <div class="mb-6 p-4 bg-carbon-700/50 rounded-xl border border-carbon-600">
                            <div class="space-y-2">
                                <p class="text-sm text-carbon-300">
                                    <span class="text-carbon-400">Pilote:</span>
                                    <span class="text-white font-medium">{{ $selectedRegistration->pilot->last_name }} {{ $selectedRegistration->pilot->first_name }}</span>
                                </p>
                                <p class="text-sm text-carbon-300">
                                    <span class="text-carbon-400">Voiture:</span>
                                    <span class="text-white">#{{ $selectedRegistration->car->race_number }}</span>
                                </p>
                            </div>
                        </div>

                        <x-racing.form.input
                            wire:model="paddockNumber"
                            label="Numéro de paddock"
                            required
                            placeholder="Ex: P1, A12, Zone-3..."
                        />
                    </div>

                    <div class="flex items-center justify-end gap-3 px-6 py-4 bg-carbon-900/50 border-t border-carbon-700 rounded-b-xl">
                        <x-racing.button variant="outline" wire:click="closePaddockModal">
                            Annuler
                        </x-racing.button>
                        <x-racing.button wire:click="assignPaddock" variant="primary">
                            Assigner
                        </x-racing.button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal Changement de Statut --}}
    @if($showStatusModal && $selectedRegistration)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="fixed inset-0 bg-carbon-950/80 backdrop-blur-sm transition-opacity" wire:click="closeStatusModal"></div>

                <div class="relative bg-carbon-800 rounded-xl border border-carbon-700 shadow-2xl transform transition-all sm:max-w-lg w-full">
                    <div class="p-6">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-10 h-10 rounded-lg bg-racing-red-500/20 flex items-center justify-center">
                                <svg class="w-6 h-6 text-racing-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-white">Modifier le statut</h3>
                        </div>

                        <div class="mb-6 p-4 bg-carbon-700/50 rounded-xl border border-carbon-600">
                            <div class="space-y-2">
                                <p class="text-sm text-carbon-300">
                                    <span class="text-carbon-400">Pilote:</span>
                                    <span class="text-white font-medium">{{ $selectedRegistration->pilot->last_name }} {{ $selectedRegistration->pilot->first_name }}</span>
                                </p>
                                <p class="text-sm text-carbon-300">
                                    <span class="text-carbon-400">Course:</span>
                                    <span class="text-white">{{ $selectedRegistration->race->name }}</span>
                                </p>
                                <p class="text-sm text-carbon-300">
                                    <span class="text-carbon-400">Voiture:</span>
                                    <span class="text-white">#{{ $selectedRegistration->car->race_number }} - {{ $selectedRegistration->car->make }} {{ $selectedRegistration->car->model }}</span>
                                </p>
                                <p class="text-sm text-carbon-300">
                                    <span class="text-carbon-400">Statut actuel:</span>
                                    <x-racing.badge-status :status="$selectedRegistration->status" class="ml-2" />
                                </p>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <x-racing.form.select
                                wire:model.live="newStatus"
                                label="Nouveau statut"
                                required
                                :options="$this->availableStatuses"
                            />

                            @if($newStatus === 'REFUSED')
                                <x-racing.form.textarea
                                    wire:model="statusChangeReason"
                                    label="Raison du changement"
                                    rows="3"
                                    placeholder="Indiquez la raison du refus (optionnel)..."
                                />
                            @endif
                        </div>

                        {{-- Avertissement pour les changements critiques --}}
                        @if(in_array($newStatus, ['REFUSED', 'TECH_CHECKED_FAIL']))
                            <div class="mt-4 p-3 bg-status-danger/10 border border-status-danger/30 rounded-lg">
                                <div class="flex items-start gap-2">
                                    <svg class="w-5 h-5 text-status-danger flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    <div>
                                        <p class="text-sm font-medium text-status-danger">Attention</p>
                                        <p class="text-xs text-status-danger/80">Ce statut indique un problème. Assurez-vous que c'est intentionnel.</p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="flex items-center justify-end gap-3 px-6 py-4 bg-carbon-900/50 border-t border-carbon-700 rounded-b-xl">
                        <x-racing.button variant="outline" wire:click="closeStatusModal">
                            Annuler
                        </x-racing.button>
                        <x-racing.button wire:click="updateStatus" variant="primary">
                            Enregistrer
                        </x-racing.button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
