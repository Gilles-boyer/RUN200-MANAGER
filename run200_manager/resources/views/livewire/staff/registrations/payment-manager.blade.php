<div class="space-y-6">
    {{-- Header avec style Racing --}}
    <div class="relative overflow-hidden rounded-xl bg-racing-gradient-subtle p-6 border border-carbon-700">
        <div class="absolute top-0 right-0 w-64 h-64 bg-racing-red-500/5 rounded-full -translate-y-1/2 translate-x-1/2"></div>

        <div class="relative">
            {{-- Bouton retour --}}
            <a href="{{ route('staff.registrations.index') }}"
               class="inline-flex items-center gap-2 text-carbon-400 hover:text-racing-red-500 transition-colors mb-4">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Retour aux inscriptions
            </a>

            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-status-warning/20 flex items-center justify-center">
                        <svg class="w-6 h-6 text-status-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-white">Gestion des paiements</h1>
                        <p class="text-carbon-400 text-sm">Inscription #{{ $registration->id }} - {{ $registration->race->name }}</p>
                    </div>
                </div>

                {{-- Bouton ajouter paiement --}}
                @if(!$this->isPaid)
                    <button wire:click="openManualPaymentModal"
                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-status-success text-white font-medium hover:bg-status-success/90 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        Enregistrer paiement
                    </button>
                @endif
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

    {{-- Infos Inscription --}}
    <x-racing.card>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-6">
            {{-- Pilote --}}
            <div class="space-y-1">
                <span class="text-xs text-carbon-400 uppercase tracking-wider">Pilote</span>
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-racing-red-500/20 flex items-center justify-center">
                        <svg class="w-4 h-4 text-racing-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <span class="font-medium text-white">{{ $registration->pilot->first_name }} {{ $registration->pilot->last_name }}</span>
                </div>
            </div>

            {{-- Voiture --}}
            <div class="space-y-1">
                <span class="text-xs text-carbon-400 uppercase tracking-wider">Voiture</span>
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-status-info/20 flex items-center justify-center">
                        <span class="text-status-info text-sm font-bold">#{{ $registration->car->race_number }}</span>
                    </div>
                    <span class="font-medium text-white">{{ $registration->car->brand ?? $registration->car->make }}</span>
                </div>
            </div>

            {{-- Statut Paiement --}}
            <div class="space-y-1">
                <span class="text-xs text-carbon-400 uppercase tracking-wider">Statut Paiement</span>
                @if($this->isPaid)
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-status-success/20 text-status-success">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <span class="font-medium">Payé ({{ $this->formattedTotalPaid }})</span>
                    </div>
                @else
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-status-warning/20 text-status-warning">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                        </svg>
                        <span class="font-medium">Non payé</span>
                    </div>
                @endif
            </div>

            {{-- Date Course --}}
            <div class="space-y-1">
                <span class="text-xs text-carbon-400 uppercase tracking-wider">Date Course</span>
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-carbon-700 flex items-center justify-center">
                        <svg class="w-4 h-4 text-carbon-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <span class="font-medium text-white">{{ $registration->race->race_date?->format('d/m/Y') ?? 'À définir' }}</span>
                </div>
            </div>
        </div>
    </x-racing.card>

    {{-- Historique des paiements --}}
    <x-racing.card>
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-lg font-semibold text-white flex items-center gap-2">
                <svg class="w-5 h-5 text-carbon-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                Historique des paiements
            </h2>
            <span class="text-sm text-carbon-400">{{ $this->payments->count() }} paiement(s)</span>
        </div>

        @if($this->payments->isEmpty())
            <x-racing.empty-state
                title="Aucun paiement"
                description="Aucun paiement n'a été enregistré pour cette inscription."
                icon='<svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
            />
        @else
            <div class="space-y-4">
                @foreach($this->payments as $payment)
                    <div class="p-4 rounded-xl border border-carbon-700 bg-carbon-800/50 hover:border-carbon-600 transition-colors">
                        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                            {{-- Infos principales --}}
                            <div class="flex-1 space-y-3">
                                {{-- Badges et montant --}}
                                <div class="flex flex-wrap items-center gap-3">
                                    {{-- Status Badge --}}
                                    @if($payment->isPaid())
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-medium bg-status-success/20 text-status-success">
                                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                            </svg>
                                            Payé
                                        </span>
                                    @elseif($payment->isPending())
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-medium bg-status-warning/20 text-status-warning">
                                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                            </svg>
                                            En attente
                                        </span>
                                    @elseif($payment->isFailed())
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-medium bg-status-danger/20 text-status-danger">
                                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                            </svg>
                                            Échoué
                                        </span>
                                    @elseif($payment->isRefunded())
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-medium bg-purple-500/20 text-purple-400">
                                            ↩️ Remboursé
                                        </span>
                                    @elseif($payment->isCancelled())
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-medium bg-carbon-700 text-carbon-400">
                                            Annulé
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-medium bg-carbon-700 text-carbon-400">
                                            {{ $payment->status_label }}
                                        </span>
                                    @endif

                                    {{-- Method Badge --}}
                                    @php
                                        $methodIcons = [
                                            'cash' => '💵',
                                            'stripe' => '💳',
                                            'bank_transfer' => '🏦',
                                            'card_onsite' => '💳',
                                            'manual' => '✋',
                                        ];
                                    @endphp
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-medium bg-status-info/20 text-status-info">
                                        {{ $methodIcons[$payment->method->value] ?? '❓' }} {{ $payment->method_label }}
                                    </span>

                                    {{-- Amount --}}
                                    <span class="text-lg font-bold text-white">
                                        {{ $payment->formatted_amount }}
                                    </span>
                                </div>

                                {{-- Dates --}}
                                <div class="flex flex-wrap items-center gap-4 text-sm text-carbon-400">
                                    <span class="flex items-center gap-1.5">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        Créé le {{ $payment->created_at->format('d/m/Y à H:i') }}
                                    </span>
                                    @if($payment->paid_at)
                                        <span class="flex items-center gap-1.5 text-status-success">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                            </svg>
                                            Payé le {{ $payment->paid_at->format('d/m/Y à H:i') }}
                                        </span>
                                    @endif
                                    @if($payment->refunded_at)
                                        <span class="flex items-center gap-1.5 text-purple-400">
                                            ↩️ Remboursé le {{ $payment->refunded_at->format('d/m/Y à H:i') }}
                                        </span>
                                    @endif
                                </div>

                                {{-- Stripe Intent ID --}}
                                @if($payment->stripe_payment_intent_id)
                                    <div class="text-xs text-carbon-500 font-mono bg-carbon-900/50 px-2 py-1 rounded inline-block">
                                        Intent: {{ $payment->stripe_payment_intent_id }}
                                    </div>
                                @endif

                                {{-- Notes --}}
                                @if($payment->metadata && isset($payment->metadata['notes']) && $payment->metadata['notes'])
                                    <div class="text-sm text-carbon-300 bg-carbon-900/30 p-3 rounded-lg border-l-2 border-carbon-600">
                                        <span class="text-carbon-500">📝 Note :</span> {{ $payment->metadata['notes'] }}
                                    </div>
                                @endif

                                {{-- Failure Reason --}}
                                @if($payment->failure_reason)
                                    <div class="text-sm text-status-danger bg-status-danger/10 p-3 rounded-lg border-l-2 border-status-danger">
                                        <span class="font-medium">⚠️ Erreur :</span> {{ $payment->failure_reason }}
                                    </div>
                                @endif
                            </div>

                            {{-- Actions --}}
                            <div class="flex items-center gap-2 lg:flex-col">
                                @if($payment->canBeRefunded())
                                    <button wire:click="openRefundModal({{ $payment->id }})"
                                            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-status-danger/50 text-status-danger hover:bg-status-danger/10 transition-colors text-sm font-medium">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                                        </svg>
                                        Rembourser
                                    </button>
                                @endif

                                @if($payment->isPending())
                                    <button wire:click="cancelPayment({{ $payment->id }})"
                                            wire:confirm="Êtes-vous sûr de vouloir annuler ce paiement ?"
                                            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-carbon-600 text-carbon-400 hover:bg-carbon-700 transition-colors text-sm font-medium">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                        Annuler
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </x-racing.card>

    {{-- Modal Paiement Manuel --}}
    @if($showManualPaymentModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="fixed inset-0 bg-carbon-950/80 backdrop-blur-sm transition-opacity" wire:click="closeManualPaymentModal"></div>

                <div class="relative bg-carbon-800 rounded-xl border border-carbon-700 shadow-2xl transform transition-all sm:max-w-lg w-full">
                    <div class="p-6">
                        {{-- Header --}}
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-10 h-10 rounded-lg bg-status-success/20 flex items-center justify-center">
                                <svg class="w-6 h-6 text-status-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-white">Enregistrer un paiement</h3>
                                <p class="text-sm text-carbon-400">Paiement en espèces, CB sur place ou virement</p>
                            </div>
                        </div>

                        <form wire:submit.prevent="recordManualPayment" class="space-y-4">
                            {{-- Méthode de paiement --}}
                            <div>
                                <label class="block text-sm font-medium text-carbon-300 mb-2">Méthode de paiement</label>
                                <div class="grid grid-cols-2 gap-3">
                                    @foreach(['cash' => '💵 Espèces', 'card_onsite' => '💳 CB sur place', 'bank_transfer' => '🏦 Virement', 'manual' => '✋ Autre'] as $value => $label)
                                        <label class="relative flex items-center justify-center p-3 rounded-lg border cursor-pointer transition-all
                                            {{ ($manualMethod ?? 'cash') === $value ? 'border-racing-red-500 bg-racing-red-500/10 text-white' : 'border-carbon-600 hover:border-carbon-500 text-carbon-400' }}">
                                            <input type="radio" wire:model="manualMethod" value="{{ $value }}" class="sr-only">
                                            <span class="text-sm font-medium">{{ $label }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Montant --}}
                            <div>
                                <label for="manualAmount" class="block text-sm font-medium text-carbon-300 mb-2">
                                    Montant
                                </label>
                                <div class="relative">
                                    <input type="number"
                                           wire:model.live="manualAmount"
                                           id="manualAmount"
                                           min="100"
                                           step="100"
                                           class="block w-full rounded-lg border-carbon-600 bg-carbon-900 text-white placeholder-carbon-500 focus:ring-racing-red-500 focus:border-racing-red-500 pr-20">
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <span class="text-carbon-400 text-sm">centimes</span>
                                    </div>
                                </div>
                                <p class="mt-2 text-sm text-racing-red-500 font-medium">
                                    = {{ number_format(($manualAmount ?? 0) / 100, 2, ',', ' ') }} €
                                </p>
                                @error('manualAmount')
                                    <p class="mt-1 text-sm text-status-danger">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Notes --}}
                            <div>
                                <label for="manualNotes" class="block text-sm font-medium text-carbon-300 mb-2">
                                    Notes (optionnel)
                                </label>
                                <textarea wire:model="manualNotes"
                                          id="manualNotes"
                                          rows="3"
                                          class="block w-full rounded-lg border-carbon-600 bg-carbon-900 text-white placeholder-carbon-500 focus:ring-racing-red-500 focus:border-racing-red-500"
                                          placeholder="Ex: Paiement en espèces, Chèque n°..."></textarea>
                                @error('manualNotes')
                                    <p class="mt-1 text-sm text-status-danger">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Actions --}}
                            <div class="flex justify-end gap-3 pt-4 border-t border-carbon-700">
                                <button type="button"
                                        wire:click="closeManualPaymentModal"
                                        class="px-4 py-2.5 rounded-lg border border-carbon-600 text-carbon-400 hover:bg-carbon-700 transition-colors font-medium">
                                    Annuler
                                </button>
                                <button type="submit"
                                        class="px-4 py-2.5 rounded-lg bg-status-success text-white hover:bg-status-success/90 transition-colors font-medium">
                                    Enregistrer le paiement
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal Remboursement --}}
    @if($showRefundModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="fixed inset-0 bg-carbon-950/80 backdrop-blur-sm transition-opacity" wire:click="closeRefundModal"></div>

                <div class="relative bg-carbon-800 rounded-xl border border-carbon-700 shadow-2xl transform transition-all sm:max-w-lg w-full">
                    <div class="p-6">
                        {{-- Header avec warning --}}
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-10 h-10 rounded-lg bg-status-danger/20 flex items-center justify-center">
                                <svg class="w-6 h-6 text-status-danger" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-white">Rembourser le paiement</h3>
                                <p class="text-sm text-status-danger">Cette action est irréversible</p>
                            </div>
                        </div>

                        <div class="mb-6 p-4 rounded-lg bg-status-danger/10 border border-status-danger/20">
                            <p class="text-sm text-carbon-300">
                                Le remboursement sera traité via Stripe et crédité sur le moyen de paiement d'origine du client.
                            </p>
                        </div>

                        <form wire:submit.prevent="processRefund" class="space-y-4">
                            <div>
                                <label for="refundReason" class="block text-sm font-medium text-carbon-300 mb-2">
                                    Motif du remboursement <span class="text-status-danger">*</span>
                                </label>
                                <textarea wire:model="refundReason"
                                          id="refundReason"
                                          rows="3"
                                          required
                                          minlength="10"
                                          class="block w-full rounded-lg border-carbon-600 bg-carbon-900 text-white placeholder-carbon-500 focus:ring-racing-red-500 focus:border-racing-red-500"
                                          placeholder="Indiquez le motif du remboursement..."></textarea>
                                @error('refundReason')
                                    <p class="mt-1 text-sm text-status-danger">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Actions --}}
                            <div class="flex justify-end gap-3 pt-4 border-t border-carbon-700">
                                <button type="button"
                                        wire:click="closeRefundModal"
                                        class="px-4 py-2.5 rounded-lg border border-carbon-600 text-carbon-400 hover:bg-carbon-700 transition-colors font-medium">
                                    Annuler
                                </button>
                                <button type="submit"
                                        class="px-4 py-2.5 rounded-lg bg-status-danger text-white hover:bg-status-danger/90 transition-colors font-medium">
                                    Confirmer le remboursement
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
