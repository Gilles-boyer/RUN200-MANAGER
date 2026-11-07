<div class="space-y-6" x-on:license-modal-closed.window="requestAnimationFrame(() => (document.getElementById($event.detail.id) || document.getElementById('registration-page-title'))?.focus())"
     x-on:registration-modal-closed.window="requestAnimationFrame(() => (document.getElementById($event.detail.id) || document.getElementById('registration-page-title'))?.focus())">
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
                    <h1 id="registration-page-title" tabindex="-1" class="text-2xl font-bold text-white">Gestion des Inscriptions</h1>
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
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
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
            <x-racing.form.select
                wire:model.live="licenseFilter"
                label="Contrôle licence"
                :options="[
                    '' => 'Tous les contrôles',
                    'not_checked' => 'À contrôler',
                    'valid' => 'RCCR vérifiée',
                    'invalid' => 'Alerte non conforme',
                    'needs_review' => 'À examiner',
                    'stale' => 'À recontrôler',
                    'not_applicable' => 'Sans objet',
                ]"
            />
            <x-racing.form.input
                wire:model.live.debounce.300ms="search"
                label="Recherche"
                placeholder="Nom, prénom, licence, n° voiture..."
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
                        <th class="px-4 py-3 text-left text-xs font-semibold text-carbon-400 uppercase tracking-wider">
                            <button wire:click="sort('pilot_name')" class="flex items-center gap-1 hover:text-white transition-colors">
                                Pilote
                                @if($sortBy === 'pilot_name')
                                    <svg class="w-4 h-4 text-racing-red-500" fill="currentColor" viewBox="0 0 20 20">
                                        @if($sortDirection === 'asc')
                                            <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd"/>
                                        @else
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                        @endif
                                    </svg>
                                @else
                                    <svg class="w-4 h-4 text-carbon-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                    </svg>
                                @endif
                            </button>
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-carbon-400 uppercase tracking-wider">
                            <button wire:click="sort('race_date')" class="flex items-center gap-1 hover:text-white transition-colors">
                                Course
                                @if($sortBy === 'race_date')
                                    <svg class="w-4 h-4 text-racing-red-500" fill="currentColor" viewBox="0 0 20 20">
                                        @if($sortDirection === 'asc')
                                            <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd"/>
                                        @else
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                        @endif
                                    </svg>
                                @else
                                    <svg class="w-4 h-4 text-carbon-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                    </svg>
                                @endif
                            </button>
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-carbon-400 uppercase tracking-wider">
                            <button wire:click="sort('race_number')" class="flex items-center gap-1 hover:text-white transition-colors">
                                Voiture
                                @if($sortBy === 'race_number')
                                    <svg class="w-4 h-4 text-racing-red-500" fill="currentColor" viewBox="0 0 20 20">
                                        @if($sortDirection === 'asc')
                                            <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd"/>
                                        @else
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                        @endif
                                    </svg>
                                @else
                                    <svg class="w-4 h-4 text-carbon-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                    </svg>
                                @endif
                            </button>
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-carbon-400 uppercase tracking-wider">
                            <button wire:click="sort('status')" class="flex items-center gap-1 hover:text-white transition-colors">
                                Statut
                                @if($sortBy === 'status')
                                    <svg class="w-4 h-4 text-racing-red-500" fill="currentColor" viewBox="0 0 20 20">
                                        @if($sortDirection === 'asc')
                                            <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd"/>
                                        @else
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                        @endif
                                    </svg>
                                @else
                                    <svg class="w-4 h-4 text-carbon-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                    </svg>
                                @endif
                            </button>
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-carbon-400 uppercase tracking-wider">Paiement</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-carbon-400 uppercase tracking-wider">Licence requise</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-carbon-400 uppercase tracking-wider">Étapes</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-carbon-400 uppercase tracking-wider">Paddock</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-carbon-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-carbon-700/50">
                    @forelse($registrations as $registration)
                        @php
                            $regCode = sprintf('%s-%s-%04d',
                                strtoupper(substr($registration->race->name, 0, 3)),
                                str_pad($registration->pilot->license_number ?? $registration->pilot_id, 6, '0', STR_PAD_LEFT),
                                $registration->id
                            );
                        @endphp
                        <tr class="hover:bg-carbon-800/50 transition-colors">
                            <td class="px-4 py-4">
                                <div class="text-sm font-medium text-white">
                                    {{ $registration->pilot->last_name }} {{ $registration->pilot->first_name }}
                                </div>
                                <div class="text-xs text-carbon-400">
                                    Licence: {{ $registration->pilot->license_number }}
                                </div>
                                <div class="text-xs text-racing-red-400 font-mono mt-1">
                                    {{ $regCode }}
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
                                <x-racing.badge-status :status="$registration->status" />
                                @if($registration->status === 'REFUSED')
                                    <p class="mt-1 text-xs font-medium text-status-danger">Ne participe pas</p>
                                @endif
                                @if($registration->reason)
                                    <p class="mt-1 text-xs text-carbon-400 max-w-xs truncate" title="{{ $registration->reason }}">
                                        {{ $registration->reason }}
                                    </p>
                                @endif
                            </td>
                            {{-- Colonne Paiement --}}
                            <td class="px-4 py-4">
                                @php
                                    $paymentRows = $registration->payments->sortByDesc('created_at')->map(function ($payment) {
                                        $netCents = 0;
                                        if ($payment->isPaid() || $payment->isPartiallyRefunded() || $payment->isRefunded()) {
                                            $paidCents = ($payment->amount_cents ?? 0) > 0
                                                ? $payment->amount_cents : (int) round((float) $payment->amount * 100);
                                            $refundedCents = (int) $payment->refund_amount_cents;
                                            if (!$payment->isRefunded() && (!$payment->isPartiallyRefunded()
                                                || ($refundedCents > 0 && $refundedCents < $paidCents))) {
                                                $netCents = max(0, $paidCents - $refundedCents);
                                            }
                                        }
                                        return ['payment' => $payment, 'net_cents' => $netCents];
                                    });
                                    $paidAmountCents = $paymentRows->sum('net_cents');
                                    $latestPayment = $paymentRows->first()['payment'] ?? null;
                                    $activePayment = $latestPayment?->isCancelled()
                                        ? ($paymentRows->first(fn ($row) => $row['net_cents'] > 0)['payment'] ?? null)
                                        : null;
                                    $displayPayment = $activePayment ?? $latestPayment;
                                    $cancelledWithoutPayment = $latestPayment?->isCancelled() && $activePayment === null;
                                @endphp
                                @if($cancelledWithoutPayment)
                                    <div class="space-y-1">
                                        <span class="inline-flex items-center px-2 py-1 rounded-lg text-xs font-medium bg-status-warning/20 text-status-warning">Non payé</span>
                                        <div class="text-xs text-carbon-400">Dernière saisie annulée</div>
                                    </div>
                                @elseif($displayPayment)
                                    <div class="space-y-1">
                                        {{-- Statut du paiement --}}
                                        @if($displayPayment->isPaid())
                                            @php
                                                $balanceCents = max(0, (int) $registration->race->entry_fee_cents - $paidAmountCents);
                                            @endphp
                                            @if($balanceCents > 0)
                                                <span class="inline-flex items-center px-2 py-1 rounded-lg text-xs font-medium bg-status-warning/20 text-status-warning">
                                                    À régler · {{ number_format($balanceCents / 100, 2, ',', ' ') }} €
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-xs font-medium bg-status-success/20 text-status-success">
                                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                    </svg>
                                                    Payé
                                                </span>
                                            @endif
                                        @elseif($displayPayment->isPending())
                                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-xs font-medium bg-status-warning/20 text-status-warning">
                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                                </svg>
                                                En attente
                                            </span>
                                        @elseif($displayPayment->isFailed())
                                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-xs font-medium bg-status-danger/20 text-status-danger">
                                                ✗ Échoué
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-1 rounded-lg text-xs font-medium bg-carbon-700 text-carbon-400">
                                                {{ $displayPayment->status_label }}
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
                                            {{ $methodIcons[$displayPayment->method->value] ?? '❓' }}
                                            {{ $displayPayment->formatted_amount }}
                                        </div>
                                    </div>
                                @else
                                    @if($registration->race?->entry_fee_cents > 0 && ($registration->isPendingPayment() || in_array($registration->status, \App\Models\RaceRegistration::engagedStatuses(), true)))
                                        <span class="inline-flex items-center px-2 py-1 rounded-lg text-xs font-medium bg-status-warning/20 text-status-warning">
                                            À régler · {{ $registration->race->formatted_entry_fee }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-1 rounded-lg text-xs font-medium bg-carbon-700/50 text-carbon-500">Aucun paiement</span>
                                    @endif
                                @endif
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex flex-col items-start gap-2">
                                    <x-racing.license-check-badge :registration="$registration" />
                                    @if($registration->licenseCheckState() !== 'not_applicable' && auth()->user()->can('license.verify'))
                                        <button id="license-check-desktop-{{ $registration->id }}" type="button" wire:click="openLicenseModal({{ $registration->id }}, 'desktop')"
                                                wire:loading.attr="disabled" wire:target="openLicenseModal"
                                                class="text-xs font-semibold text-checkered-yellow-500 hover:underline focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-checkered-yellow-500">
                                            Contrôler la licence
                                        </button>
                                    @endif
                                </div>
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
                                    {{-- Bouton QR Code --}}
                                    <button wire:click="openQrModal({{ $registration->id }})"
                                            class="p-2 rounded-lg text-carbon-400 hover:text-white hover:bg-carbon-700 transition-colors"
                                            title="Voir le QR Code d'inscription">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h2M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                                        </svg>
                                    </button>

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

                                    @if(($registration->isPending() || $registration->isPendingPayment()) && auth()->user()->can('race_registration.validate'))
                                        <button wire:click="openValidationModal({{ $registration->id }}, 'accept')"
                                                class="p-2 rounded-lg text-status-success hover:bg-status-success/20 transition-colors"
                                                title="{{ $registration->isPendingPayment() ? 'Accepter avant paiement' : 'Accepter' }}"
                                                aria-label="{{ $registration->isPendingPayment() ? 'Accepter avant paiement' : 'Accepter' }}">
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                            </svg>
                                        </button>
                                    @endif
                                    @if(in_array($registration->status, ['PENDING_VALIDATION', 'PENDING_PAYMENT', 'ACCEPTED', 'ADMIN_CHECKED', 'TECH_CHECKED_OK', 'TECH_CHECKED_FAIL'], true) && auth()->user()->can('race_registration.validate'))
                                        <button id="registration-refuse-desktop-{{ $registration->id }}" type="button" wire:click="openValidationModal({{ $registration->id }}, 'refuse', 'desktop')"
                                                class="p-2 rounded-lg text-status-danger hover:bg-status-danger/20 transition-colors focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-status-danger"
                                                title="Refuser la participation"
                                                aria-label="Refuser la participation de {{ $registration->pilot->first_name }} {{ $registration->pilot->last_name }}">
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                            </svg>
                                        </button>
                                    @endif
                                    @if($registration->status === 'REFUSED' && auth()->user()->can('race_registration.validate'))
                                        <button id="registration-reopen-desktop-{{ $registration->id }}" type="button" wire:click="openReopenModal({{ $registration->id }}, 'desktop')"
                                                class="px-3 py-2 rounded-lg text-status-info hover:bg-status-info/20 transition-colors focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-status-info"
                                                aria-label="Réouvrir l'inscription de {{ $registration->pilot->first_name }} {{ $registration->pilot->last_name }}">
                                            Réouvrir
                                        </button>
                                    @endif

                                    @if($registration->isAccepted() && auth()->user()->can('race_registration.assign_paddock'))
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
                            <td colspan="9" class="px-4 py-12">
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
                        <div class="text-right">
                            <x-racing.badge-status :status="$registration->status" />
                            @if($registration->status === 'REFUSED')
                                <p class="mt-1 text-xs font-medium text-status-danger">Ne participe pas</p>
                            @endif
                        </div>
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
                        <div class="flex flex-wrap justify-between items-center gap-2">
                            <span class="text-xs text-carbon-400 uppercase tracking-wider">Contrôle licence</span>
                            <x-racing.license-check-badge :registration="$registration" />
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
                            $paymentRows = $registration->payments->sortByDesc('created_at')->map(function ($payment) {
                                $netCents = 0;
                                if ($payment->isPaid() || $payment->isPartiallyRefunded() || $payment->isRefunded()) {
                                    $paidCents = ($payment->amount_cents ?? 0) > 0
                                        ? $payment->amount_cents : (int) round((float) $payment->amount * 100);
                                    $refundedCents = (int) $payment->refund_amount_cents;
                                    if (!$payment->isRefunded() && (!$payment->isPartiallyRefunded()
                                        || ($refundedCents > 0 && $refundedCents < $paidCents))) {
                                        $netCents = max(0, $paidCents - $refundedCents);
                                    }
                                }
                                return ['payment' => $payment, 'net_cents' => $netCents];
                            });
                            $paidAmountCents = $paymentRows->sum('net_cents');
                            $latestPayment = $paymentRows->first()['payment'] ?? null;
                            $activePayment = $latestPayment?->isCancelled()
                                ? ($paymentRows->first(fn ($row) => $row['net_cents'] > 0)['payment'] ?? null)
                                : null;
                            $displayPayment = $activePayment ?? $latestPayment;
                            $cancelledWithoutPayment = $latestPayment?->isCancelled() && $activePayment === null;
                        @endphp
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-carbon-400 uppercase tracking-wider">Paiement</span>
                            @if($cancelledWithoutPayment)
                                <span class="text-xs font-medium text-status-warning">Non payé · saisie annulée</span>
                            @elseif($displayPayment)
                                <div class="flex items-center gap-2">
                                    @if($displayPayment->isPaid())
                                        @php
                                            $balanceCents = max(0, (int) $registration->race->entry_fee_cents - $paidAmountCents);
                                        @endphp
                                        @if($balanceCents > 0)
                                            <span class="inline-flex items-center px-2 py-1 rounded-lg text-xs font-medium bg-status-warning/20 text-status-warning">
                                                À régler · {{ number_format($balanceCents / 100, 2, ',', ' ') }} €
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-xs font-medium bg-status-success/20 text-status-success">
                                                ✓ {{ $displayPayment->formatted_amount }}
                                            </span>
                                        @endif
                                    @elseif($displayPayment->isPending())
                                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-xs font-medium bg-status-warning/20 text-status-warning">
                                            ⏳ En attente
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-1 rounded-lg text-xs font-medium bg-carbon-700 text-carbon-400">
                                            {{ $displayPayment->status_label }}
                                        </span>
                                    @endif
                                </div>
                            @else
                                @if($registration->race?->entry_fee_cents > 0 && ($registration->isPendingPayment() || in_array($registration->status, \App\Models\RaceRegistration::engagedStatuses(), true)))
                                    <span class="text-xs font-medium text-status-warning">À régler · {{ $registration->race->formatted_entry_fee }}</span>
                                @else
                                    <span class="text-xs text-carbon-500">Aucun</span>
                                @endif
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
                        @if($registration->licenseCheckState() !== 'not_applicable' && auth()->user()->can('license.verify'))
                            <button id="license-check-mobile-{{ $registration->id }}" type="button" wire:click="openLicenseModal({{ $registration->id }}, 'mobile')"
                                    wire:loading.attr="disabled" wire:target="openLicenseModal"
                                    class="flex-1 flex items-center justify-center px-4 py-2.5 rounded-lg bg-checkered-yellow-500/20 text-checkered-yellow-500 text-sm font-medium hover:bg-checkered-yellow-500/30 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-checkered-yellow-500 tap-target">
                                Contrôler la licence
                            </button>
                        @endif
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
                        @if(($registration->isPending() || $registration->isPendingPayment()) && auth()->user()->can('race_registration.validate'))
                            <button wire:click="openValidationModal({{ $registration->id }}, 'accept')"
                                    class="flex-1 flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-status-success/20 text-status-success text-sm font-medium hover:bg-status-success/30 transition-colors tap-target">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                {{ $registration->isPendingPayment() ? 'Accepter avant paiement' : 'Accepter' }}
                            </button>
                        @endif
                        @if(in_array($registration->status, ['PENDING_VALIDATION', 'PENDING_PAYMENT', 'ACCEPTED', 'ADMIN_CHECKED', 'TECH_CHECKED_OK', 'TECH_CHECKED_FAIL'], true) && auth()->user()->can('race_registration.validate'))
                            <button id="registration-refuse-mobile-{{ $registration->id }}" type="button" wire:click="openValidationModal({{ $registration->id }}, 'refuse', 'mobile')"
                                    class="flex-1 flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-status-danger/20 text-status-danger text-sm font-medium hover:bg-status-danger/30 transition-colors tap-target focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-status-danger">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                </svg>
                                Refuser la participation
                            </button>
                        @endif
                        @if($registration->status === 'REFUSED' && auth()->user()->can('race_registration.validate'))
                            <button id="registration-reopen-mobile-{{ $registration->id }}" type="button" wire:click="openReopenModal({{ $registration->id }}, 'mobile')"
                                    class="flex-1 flex items-center justify-center px-4 py-2.5 rounded-lg bg-status-info/20 text-status-info text-sm font-medium hover:bg-status-info/30 transition-colors tap-target focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-status-info">
                                Réouvrir l'inscription
                            </button>
                        @endif
                        @if($registration->isAccepted() && auth()->user()->can('race_registration.assign_paddock'))
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

    {{-- Contrôle manuel d'une licence FFSA --}}
    @if($showLicenseModal && $licenseRegistration)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-carbon-950/80 px-4 py-6 sm:py-10"
             role="dialog" aria-modal="true" aria-labelledby="license-modal-title"
             x-data x-init="$nextTick(() => $el.querySelector('#license-modal-title')?.focus())"
             x-on:keydown.escape.window="$wire.closeLicenseModal()"
             x-on:keydown.tab="const controls = [...$el.querySelectorAll('a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled])')].filter(control => control.getClientRects().length); if (controls.length && $event.shiftKey && document.activeElement === controls[0]) { $event.preventDefault(); controls[controls.length - 1].focus(); } else if (controls.length && !$event.shiftKey && document.activeElement === controls[controls.length - 1]) { $event.preventDefault(); controls[0].focus(); }">
            <div class="mx-auto w-full max-w-2xl rounded-xl border border-carbon-600 bg-carbon-900 shadow-2xl">
                <div class="flex items-start justify-between gap-4 border-b border-carbon-700 p-5 sm:p-6">
                    <div>
                        <h2 id="license-modal-title" tabindex="-1" class="text-xl font-bold text-white">Contrôle de licence FFSA</h2>
                        <p class="mt-1 text-sm text-carbon-300">
                            {{ $licenseRegistration->pilot->first_name }} {{ $licenseRegistration->pilot->last_name }} ·
                            licence {{ $licenseRegistration->pilot->license_number }} ·
                            {{ $licenseRegistration->race->name }}
                        </p>
                        <p class="mt-1 text-sm text-checkered-yellow-500">
                            Requis : {{ $licenseRegistration->race->required_license_code }} pour {{ $licenseRegistration->race->race_date->format('Y') }},
                            valable le {{ $licenseRegistration->race->race_date->format('d/m/Y') }}.
                        </p>
                    </div>
                    <button type="button" wire:click="closeLicenseModal" aria-label="Fermer le contrôle de licence"
                            class="rounded-lg p-2 text-carbon-300 hover:bg-carbon-700 hover:text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-checkered-yellow-500">✕</button>
                </div>

                <div class="space-y-5 p-5 sm:p-6">
                    <div class="rounded-lg border border-status-info/40 bg-status-info/10 p-4 text-sm text-carbon-100">
                        <p>Ouvrez la recherche FFSA dans un autre onglet, recherchez le pilote, puis lisez le bloc de l’année de course : « Code type : RCCR », « État : Active » et « Date de fin ». Aucun nom ni numéro n’est transmis par ces liens.</p>
                        <div class="mt-3 flex flex-wrap gap-3">
                            <a href="https://ligue.ffsa.org/federation/ma_structure-rechercher.html" target="_blank" rel="noopener noreferrer"
                               class="font-semibold text-checkered-yellow-500 underline focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-checkered-yellow-500">Ouvrir la recherche FFSA ↗</a>
                            <a href="https://verification.ffsa.org/" target="_blank" rel="noopener noreferrer"
                               class="text-carbon-100 underline focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-checkered-yellow-500">Vérifier le numéro ↗</a>
                        </div>
                        <p class="mt-3 text-xs text-carbon-200">La vérification du numéro indique l’identité, l’année et la suspension, mais pas le code type de licence. Elle ne suffit pas à confirmer une RCCR. Le contrôle ne modifie pas les étapes VA/VT.</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <label class="block text-sm font-medium text-carbon-100">Source consultée
                            <select wire:model.live="licenseSource" class="mt-1 block w-full rounded-lg border border-carbon-600 bg-carbon-800 text-white focus:border-checkered-yellow-500 focus:ring-checkered-yellow-500">
                                <option value="ligue_ffsa">Recherche ligue.ffsa.org</option>
                                <option value="licence_ffsa">Portail licence.ffsa.org</option>
                                <option value="verification_ffsa">Vérification du numéro FFSA</option>
                                <option value="official_ffsa_document">Document officiel FFSA</option>
                            </select>
                        </label>
                        <label class="block text-sm font-medium text-carbon-100">Accès à la source
                            <select wire:model.live="licenseSourceAccessStatus" class="mt-1 block w-full rounded-lg border border-carbon-600 bg-carbon-800 text-white focus:border-checkered-yellow-500 focus:ring-checkered-yellow-500">
                                <option value="consulted">Consultée</option>
                                <option value="unavailable">Indisponible</option>
                                <option value="incomplete">Information incomplète</option>
                            </select>
                        </label>
                        <label class="block text-sm font-medium text-carbon-100">Code type observé
                            <input type="text" wire:model.live.debounce.250ms="licenseTypeObserved" maxlength="30" placeholder="Ex. RCCR"
                                   class="mt-1 block w-full rounded-lg border border-carbon-600 bg-carbon-800 text-white placeholder-carbon-500 focus:border-checkered-yellow-500 focus:ring-checkered-yellow-500">
                        </label>
                        <label class="block text-sm font-medium text-carbon-100">État observé
                            <select wire:model.live="licenseStateObserved" class="mt-1 block w-full rounded-lg border border-carbon-600 bg-carbon-800 text-white focus:border-checkered-yellow-500 focus:ring-checkered-yellow-500">
                                <option value="">Non vérifié</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="expired">Expirée</option>
                                <option value="suspended">Suspendue</option>
                                <option value="unknown">Inconnu</option>
                            </select>
                        </label>
                        <label class="block text-sm font-medium text-carbon-100">Année observée
                            <input type="text" inputmode="numeric" wire:model.live.debounce.250ms="licenseYearObserved" maxlength="4" placeholder="{{ $licenseRegistration->race->race_date->format('Y') }}"
                                   class="mt-1 block w-full rounded-lg border border-carbon-600 bg-carbon-800 text-white placeholder-carbon-500 focus:border-checkered-yellow-500 focus:ring-checkered-yellow-500">
                        </label>
                        <label class="block text-sm font-medium text-carbon-100">Identité concordante ?
                            <select wire:model.live="licenseIdentityMatches" class="mt-1 block w-full rounded-lg border border-carbon-600 bg-carbon-800 text-white focus:border-checkered-yellow-500 focus:ring-checkered-yellow-500">
                                <option value="">Non vérifié</option><option value="yes">Oui</option><option value="no">Non</option>
                            </select>
                        </label>
                        <label class="block text-sm font-medium text-carbon-100">Numéro concordant ?
                            <select wire:model.live="licenseNumberMatches" class="mt-1 block w-full rounded-lg border border-carbon-600 bg-carbon-800 text-white focus:border-checkered-yellow-500 focus:ring-checkered-yellow-500">
                                <option value="">Non vérifié</option><option value="yes">Oui</option><option value="no">Non</option>
                            </select>
                        </label>
                        <label class="block text-sm font-medium text-carbon-100">Valable le jour de la course ?
                            <select wire:model.live="licenseValidForRaceDate" class="mt-1 block w-full rounded-lg border border-carbon-600 bg-carbon-800 text-white focus:border-checkered-yellow-500 focus:ring-checkered-yellow-500">
                                <option value="">Non vérifié</option><option value="yes">Oui</option><option value="no">Non</option>
                            </select>
                        </label>
                        <label class="block text-sm font-medium text-carbon-100">Date de fin observée (obligatoire pour valider)
                            <input type="date" wire:model.live="licenseValidUntil" class="mt-1 block w-full rounded-lg border border-carbon-600 bg-carbon-800 text-white focus:border-checkered-yellow-500 focus:ring-checkered-yellow-500">
                        </label>
                    </div>

                    <label class="block text-sm font-medium text-carbon-100">Décision du contrôleur
                        <select wire:model.live="licenseResult" class="mt-1 block w-full rounded-lg border border-carbon-600 bg-carbon-800 text-white focus:border-checkered-yellow-500 focus:ring-checkered-yellow-500">
                            <option value="needs_review">À examiner ou vérification impossible</option>
                            <option value="invalid" @disabled($licenseSource !== 'ligue_ffsa' || $licenseSourceAccessStatus !== 'consulted')>Alerte : licence non conforme</option>
                            <option value="valid" @disabled(! $this->canConfirmValidLicense)>RCCR {{ $licenseRegistration->race->race_date->format('Y') }} vérifiée</option>
                        </select>
                    </label>
                    @if(! $this->canConfirmValidLicense)
                        <p class="text-xs text-status-warning">Le statut vert nécessite la recherche FFSA consultée, le code RCCR, l’année et l’état Active, une date de fin valable, l’identité et le numéro concordants.</p>
                    @endif
                    <p class="text-xs text-carbon-300">Une identité, un numéro ou un autre type de licence incertain reste « À examiner ». L’alerte rouge exige une preuve FFSA de licence expirée, suspendue ou inactive.</p>
                    @if($licenseResult === 'invalid' && ! $this->canConfirmInvalidLicense)
                        <p class="text-xs text-status-warning" role="status">Pour l’alerte rouge, sélectionnez « Non » à la validité le jour de la course, confirmez l’identité et le numéro, puis indiquez un motif cohérent avec l’état et la date de fin observés. En cas de doute, choisissez « À examiner ».</p>
                    @endif
                    @if($licenseResult !== 'valid')
                        <label class="block text-sm font-medium text-carbon-100">Motif du contrôle
                            <select wire:model.live="licenseReasonCode" class="mt-1 block w-full rounded-lg border border-carbon-600 bg-carbon-800 text-white focus:border-checkered-yellow-500 focus:ring-checkered-yellow-500">
                                @if($licenseResult === 'invalid')
                                    <option value="">Choisir un motif</option>
                                    <option value="expired">Licence expirée</option>
                                    <option value="revoked">Licence suspendue ou révoquée</option>
                                    <option value="no_current_license_confirmed">Aucune licence en cours confirmée</option>
                                @else
                                    <option value="">Choisir un motif</option>
                                    <option value="ambiguous">Information ambiguë</option>
                                    <option value="other_title_to_review">Autre titre ou catégorie à examiner</option>
                                    <option value="not_found">Licence introuvable</option>
                                    <option value="identity_uncertain">Identité incertaine</option>
                                    <option value="number_uncertain">Numéro incertain</option>
                                    <option value="source_unavailable">Portail indisponible</option>
                                    <option value="document_only">Document seul</option>
                                    <option value="other">Autre motif à préciser</option>
                                @endif
                            </select>
                        </label>
                    @endif
                    <label class="block text-sm font-medium text-carbon-100">Note pour le suivi administratif
                        <textarea wire:model="licenseNote" rows="2" maxlength="500" placeholder="Précisez le constat utile à la décision humaine."
                                  class="mt-1 block w-full rounded-lg border border-carbon-600 bg-carbon-800 text-white placeholder-carbon-500 focus:border-checkered-yellow-500 focus:ring-checkered-yellow-500"></textarea>
                    </label>
                    @if($errors->has('licenseResult') || $errors->has('licenseYearObserved') || $errors->has('licenseValidUntil') || $errors->has('licenseTypeObserved') || $errors->has('licenseNote') || $errors->has('licenseReasonCode'))
                        <div role="alert" class="rounded-lg border border-status-danger/40 bg-status-danger/10 p-3 text-sm text-status-danger">
                            @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
                        </div>
                    @endif
                </div>
                <div class="flex flex-wrap justify-end gap-3 border-t border-carbon-700 p-5 sm:p-6">
                    <button type="button" wire:click="closeLicenseModal" class="rounded-lg border border-carbon-600 px-4 py-2.5 text-sm font-semibold text-carbon-100 hover:bg-carbon-700">Annuler</button>
                    <button type="button" wire:click="confirmLicenseCheck" wire:loading.attr="disabled" wire:target="confirmLicenseCheck"
                            @disabled(($licenseResult === 'valid' && ! $this->canConfirmValidLicense) || ($licenseResult === 'invalid' && ! $this->canConfirmInvalidLicense))
                            class="rounded-lg bg-racing-red-500 px-4 py-2.5 text-sm font-semibold text-white hover:bg-racing-red-600 disabled:cursor-not-allowed disabled:opacity-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-checkered-yellow-500">
                        <span wire:loading.remove wire:target="confirmLicenseCheck">Enregistrer le contrôle</span>
                        <span wire:loading wire:target="confirmLicenseCheck">Enregistrement…</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal Validation --}}
    @if($showValidationModal && $selectedRegistration)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="validation-modal-title" role="dialog" aria-modal="true"
             x-data x-init="$nextTick(() => $el.querySelector('#validation-modal-title')?.focus())"
             x-on:keydown.escape="@this.closeValidationModal()"
             x-on:keydown.tab="const controls = [...$el.querySelectorAll('button:not([disabled]), input:not([disabled]), textarea:not([disabled])')].filter(control => control.getClientRects().length); if (controls.length && $event.shiftKey && document.activeElement === controls[0]) { $event.preventDefault(); controls[controls.length - 1].focus(); } else if (controls.length && !$event.shiftKey && document.activeElement === controls[controls.length - 1]) { $event.preventDefault(); controls[0].focus(); }">
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="fixed inset-0 bg-carbon-950/80 backdrop-blur-sm transition-opacity" wire:click="closeValidationModal"></div>

                <div class="relative bg-carbon-800 rounded-xl border border-carbon-700 shadow-2xl transform transition-all sm:max-w-lg w-full max-h-[calc(100vh-2rem)] overflow-y-auto">
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
                            <h3 id="validation-modal-title" tabindex="-1" class="text-lg font-semibold text-white focus:outline-none">
                                {{ $validationAction === 'accept' ? ($selectedRegistration->isPendingPayment() ? 'Accepter avant paiement' : 'Accepter l\'inscription') : 'Refuser la participation' }}
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
                                <p class="text-sm text-carbon-300">
                                    <span class="text-carbon-400">Statut actuel :</span>
                                    <span class="text-white">{{ \App\Domain\Registration\Enums\RegistrationStatus::tryFrom($selectedRegistration->status)?->label() ?? $selectedRegistration->status }}</span>
                                </p>
                            </div>
                        </div>

                        @if($validationAction === 'accept' && $selectedRegistration->isPendingPayment())
                            <div class="mb-6 p-4 rounded-xl border border-status-warning/40 bg-status-warning/10 text-sm text-carbon-100" role="status">
                                Le paiement n'est pas encore enregistré. Cette décision accepte l'inscription et donne accès à l'E-Card et aux vérifications. Le règlement devra être confirmé séparément, notamment lors des VA/VT. Aucun reçu ne sera créé par cette validation.
                            </div>
                        @endif

                        @if($validationAction === 'refuse')
                            @php
                                $refusalPaidCents = $selectedRegistration->getPaidAmountCents();
                                $refusalDueCents = max(0, (int) $selectedRegistration->race->entry_fee_cents - $refusalPaidCents);
                            @endphp
                            <div class="mb-4 rounded-xl border border-status-warning/40 bg-status-warning/10 p-4 text-sm text-carbon-100" role="note">
                                <p class="font-semibold text-status-warning">Le pilote ne participera pas à cette course.</p>
                                <p class="mt-2">Paiement encaissé : {{ number_format($refusalPaidCents / 100, 2, ',', ' ') }} € · Restant dû : {{ number_format($refusalDueCents / 100, 2, ',', ' ') }} €</p>
                                <p class="mt-2">Cette décision n'annule ni ne rembourse un paiement. Un paiement en ligne déjà ouvert peut encore aboutir ; il faudra le rapprocher séparément.</p>
                            </div>
                            <x-racing.form.textarea
                                wire:model="refusalReason"
                                name="refusalReason"
                                id="refusalReason"
                                label="Motif du refus"
                                required
                                rows="3"
                                hint="Au moins 10 caractères. Ce motif sera communiqué au pilote."
                                placeholder="Expliquez pourquoi ce pilote ne participera pas à cette course."
                            />
                        @endif
                        @if($validationError)
                            <p class="mt-4 rounded-lg border border-status-danger/40 bg-status-danger/10 p-3 text-sm text-status-danger" role="alert" aria-live="assertive">{{ $validationError }}</p>
                        @endif
                    </div>

                    <div class="flex items-center justify-end gap-3 px-6 py-4 bg-carbon-900/50 border-t border-carbon-700 rounded-b-xl">
                        <x-racing.button variant="outline" wire:click="closeValidationModal" wire:loading.attr="disabled" wire:target="confirmValidation">
                            Retour
                        </x-racing.button>
                        <x-racing.button
                            wire:click="confirmValidation"
                            wire:loading.attr="disabled"
                            wire:target="confirmValidation"
                            variant="{{ $validationAction === 'accept' ? 'primary' : 'danger' }}"
                        >
                            <span wire:loading.remove wire:target="confirmValidation">{{ $validationAction === 'accept' ? ($selectedRegistration->isPendingPayment() ? 'Accepter avant paiement' : 'Accepter') : 'Confirmer le refus' }}</span>
                            <span wire:loading wire:target="confirmValidation">{{ $validationAction === 'accept' ? 'Validation en cours…' : 'Refus en cours…' }}</span>
                        </x-racing.button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Réouverture contrôlée d'une inscription refusée --}}
    @if($showReopenModal && $selectedRegistration)
        <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true" aria-labelledby="reopen-modal-title"
             x-data x-init="$nextTick(() => $el.querySelector('#reopen-modal-title')?.focus())"
             x-on:keydown.escape="@this.closeReopenModal()"
             x-on:keydown.tab="const controls = [...$el.querySelectorAll('button:not([disabled]), input:not([disabled]), textarea:not([disabled])')].filter(control => control.getClientRects().length); if (controls.length && $event.shiftKey && document.activeElement === controls[0]) { $event.preventDefault(); controls[controls.length - 1].focus(); } else if (controls.length && !$event.shiftKey && document.activeElement === controls[controls.length - 1]) { $event.preventDefault(); controls[0].focus(); }">
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="fixed inset-0 bg-carbon-950/80 backdrop-blur-sm" wire:click="closeReopenModal"></div>
                <div class="relative w-full max-w-lg max-h-[calc(100vh-2rem)] overflow-y-auto rounded-xl border border-carbon-700 bg-carbon-800 shadow-2xl">
                    <div class="p-5 sm:p-6 space-y-4">
                        <h3 id="reopen-modal-title" tabindex="-1" class="text-lg font-semibold text-white focus:outline-none">Réouvrir l'inscription refusée</h3>
                        <p class="text-sm text-carbon-300">{{ $selectedRegistration->pilot->first_name }} {{ $selectedRegistration->pilot->last_name }} · #{{ $selectedRegistration->car->race_number }} {{ $selectedRegistration->car->make }} {{ $selectedRegistration->car->model }} · {{ $selectedRegistration->race->name }}</p>
                        <div class="rounded-lg border border-carbon-600 bg-carbon-700/50 p-4 text-sm text-carbon-200">
                            <p class="font-semibold text-white">Contrôles déjà enregistrés</p>
                            @forelse($selectedRegistration->passages->sortBy('scanned_at') as $passage)
                                <p class="mt-1">{{ $passage->checkpoint?->name ?? 'Étape inconnue' }} · {{ $passage->scanned_at?->format('d/m/Y H:i') }}</p>
                            @empty
                                <p class="mt-1">Aucun passage VA/VT.</p>
                            @endforelse
                        </div>
                        @if($reopenProposedStatus)
                            <p class="rounded-lg border border-status-info/40 bg-status-info/10 p-3 text-sm text-carbon-100" role="status">
                                Statut proposé : <strong>{{ \App\Domain\Registration\Enums\RegistrationStatus::tryFrom($reopenProposedStatus)?->label() ?? $reopenProposedStatus }}</strong>. La confirmation vérifiera de nouveau les passages et les paiements.
                            </p>
                        @else
                            <p class="rounded-lg border border-status-danger/40 bg-status-danger/10 p-3 text-sm text-status-danger" role="alert">
                                Les contrôles du dossier sont incohérents. Un administrateur doit les examiner et corriger le dossier avant la réouverture.
                            </p>
                        @endif
                        <x-racing.form.textarea wire:model="reopenReason" name="reopenReason" id="reopenReason" label="Motif de la réouverture" required rows="3"
                            hint="Au moins 10 caractères. La décision sera journalisée."
                            placeholder="Précisez la correction apportée au dossier." />
                        <label class="flex min-h-11 items-start gap-3 text-sm text-carbon-100">
                            <input type="checkbox" wire:model="reopenPaymentsReviewed" class="mt-1 rounded border-carbon-500 bg-carbon-900 text-status-info focus:ring-status-info" />
                            <span>J'ai vérifié les paiements et les sessions de paiement de ce dossier. Cette réouverture ne modifie aucun paiement.</span>
                        </label>
                        @error('reopenPaymentsReviewed') <p class="text-sm text-status-danger" role="alert">{{ $message }}</p> @enderror
                        @if($reopenError)
                            <p class="rounded-lg border border-status-danger/40 bg-status-danger/10 p-3 text-sm text-status-danger" role="alert" aria-live="assertive">{{ $reopenError }}</p>
                        @endif
                    </div>
                    <div class="flex flex-wrap justify-end gap-3 border-t border-carbon-700 bg-carbon-900/50 px-5 py-4 sm:px-6">
                        <button type="button" wire:click="closeReopenModal" wire:loading.attr="disabled" wire:target="confirmReopen"
                                class="min-h-11 rounded-lg border border-carbon-600 px-4 text-sm font-semibold text-carbon-100 hover:bg-carbon-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-status-info">Retour</button>
                        <button type="button" wire:click="confirmReopen" wire:loading.attr="disabled" wire:target="confirmReopen" @disabled($reopenProposedStatus === null)
                                class="min-h-11 rounded-lg bg-status-info px-4 text-sm font-semibold text-carbon-950 hover:brightness-110 disabled:cursor-not-allowed disabled:opacity-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-status-info">
                            <span wire:loading.remove wire:target="confirmReopen">Confirmer la réouverture</span>
                            <span wire:loading wire:target="confirmReopen">Réouverture en cours…</span>
                        </button>
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

    {{-- Modal QR Code --}}
    @if($showQrModal && $selectedRegistration)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                <div class="fixed inset-0 bg-carbon-950/80 transition-opacity" wire:click="closeQrModal"></div>

                <div class="relative inline-block w-full max-w-md overflow-hidden text-left align-bottom bg-carbon-900 rounded-2xl border border-carbon-700 shadow-xl transform transition-all sm:my-8 sm:align-middle">
                    {{-- Header --}}
                    <div class="px-6 py-4 border-b border-carbon-700">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                                📱 QR Code d'inscription
                            </h3>
                            <button wire:click="closeQrModal" class="text-carbon-400 hover:text-white transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Body --}}
                    <div class="px-6 py-6 space-y-6">
                        {{-- Infos pilote --}}
                        <div class="bg-carbon-800/50 rounded-xl p-4 border border-carbon-700">
                            <div class="text-center">
                                <p class="text-sm text-carbon-400">Pilote</p>
                                <p class="text-lg font-bold text-white">{{ $selectedRegistration->pilot->last_name }} {{ $selectedRegistration->pilot->first_name }}</p>
                                <p class="text-xs text-carbon-400 mt-1">Licence: {{ $selectedRegistration->pilot->license_number }}</p>
                            </div>
                        </div>

                        {{-- Code d'inscription --}}
                        <div class="text-center">
                            <p class="text-sm text-carbon-400 mb-2">Code d'inscription</p>
                            <div class="inline-flex items-center px-4 py-2 bg-racing-red-500/20 border border-racing-red-500/30 rounded-lg">
                                <span class="text-xl font-mono font-bold text-racing-red-500 tracking-wider">{{ $registrationCode }}</span>
                            </div>
                        </div>

                        {{-- QR Code --}}
                        <div class="flex justify-center">
                            <div class="p-4 bg-white rounded-xl">
                                {!! $qrCodeSvg !!}
                            </div>
                        </div>

                        {{-- Infos course et voiture --}}
                        <div class="grid grid-cols-2 gap-4 text-center">
                            <div class="bg-carbon-800/50 rounded-xl p-3 border border-carbon-700">
                                <p class="text-xs text-carbon-400">Course</p>
                                <p class="text-sm font-medium text-white">{{ $selectedRegistration->race->name }}</p>
                            </div>
                            <div class="bg-carbon-800/50 rounded-xl p-3 border border-carbon-700">
                                <p class="text-xs text-carbon-400">Voiture</p>
                                <p class="text-sm font-medium text-white">#{{ $selectedRegistration->car->race_number }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="px-6 py-4 bg-carbon-900/50 border-t border-carbon-700 rounded-b-xl">
                        <x-racing.button variant="outline" wire:click="closeQrModal" class="w-full">
                            Fermer
                        </x-racing.button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
