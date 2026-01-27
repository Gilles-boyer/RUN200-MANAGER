<div class="py-8 px-4 sm:px-6 lg:px-8 max-w-4xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('staff.registrations.index') }}" class="text-blue-600 hover:text-blue-800 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Retour aux inscriptions
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-md bg-green-50 dark:bg-green-900/30 p-4">
            <p class="text-sm text-green-700 dark:text-green-400">{{ session('success') }}</p>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 rounded-md bg-red-50 dark:bg-red-900/30 p-4">
            <p class="text-sm text-red-700 dark:text-red-400">{{ session('error') }}</p>
        </div>
    @endif

    <!-- Registration Info -->
    <div class="bg-white dark:bg-zinc-800 shadow rounded-lg mb-6">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-zinc-700">
            <h1 class="text-xl font-semibold text-gray-900 dark:text-white">
                Gestion des paiements
            </h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Inscription #{{ $registration->id }} - {{ $registration->race->name }}
            </p>
        </div>

        <div class="px-6 py-4 bg-gray-50 dark:bg-zinc-900/50">
            <dl class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
                <div>
                    <dt class="text-gray-500 dark:text-gray-400">Pilote</dt>
                    <dd class="font-medium text-gray-900 dark:text-white">
                        {{ $registration->pilot->first_name }} {{ $registration->pilot->last_name }}
                    </dd>
                </div>
                <div>
                    <dt class="text-gray-500 dark:text-gray-400">Voiture</dt>
                    <dd class="font-medium text-gray-900 dark:text-white">
                        #{{ $registration->car->race_number }} - {{ $registration->car->make }}
                    </dd>
                </div>
                <div>
                    <dt class="text-gray-500 dark:text-gray-400">Statut paiement</dt>
                    <dd>
                        @if($this->isPaid)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-400">
                                Payé ({{ $this->formattedTotalPaid }})
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-400">
                                Non payé
                            </span>
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-gray-500 dark:text-gray-400">Date course</dt>
                    <dd class="font-medium text-gray-900 dark:text-white">
                        {{ $registration->race->race_date?->format('d/m/Y') ?? 'À définir' }}
                    </dd>
                </div>
            </dl>
        </div>
    </div>

    <!-- Actions -->
    <div class="mb-6 flex gap-4">
        @if(!$this->isPaid)
            <button wire:click="openManualPaymentModal"
                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Enregistrer paiement manuel
            </button>
        @endif
    </div>

    <!-- Payments List -->
    <div class="bg-white dark:bg-zinc-800 shadow rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-zinc-700">
            <h2 class="text-lg font-medium text-gray-900 dark:text-white">
                Historique des paiements
            </h2>
        </div>

        @if($this->payments->isEmpty())
            <div class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                Aucun paiement enregistré pour cette inscription.
            </div>
        @else
            <div class="divide-y divide-gray-200 dark:divide-zinc-700">
                @foreach($this->payments as $payment)
                    <div class="px-6 py-4">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-3">
                                    <!-- Status Badge -->
                                    @switch($payment->status->value)
                                        @case('paid')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-400">
                                                Payé
                                            </span>
                                            @break
                                        @case('pending')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-400">
                                                En attente
                                            </span>
                                            @break
                                        @case('failed')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-400">
                                                Échoué
                                            </span>
                                            @break
                                        @case('refunded')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-900/50 dark:text-gray-400">
                                                Remboursé
                                            </span>
                                            @break
                                        @case('cancelled')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-900/50 dark:text-gray-400">
                                                Annulé
                                            </span>
                                            @break
                                        @default
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-400">
                                                {{ $payment->status->value }}
                                            </span>
                                    @endswitch

                                    <!-- Method Badge -->
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-400">
                                        {{ $payment->method->value === 'stripe' ? 'Stripe' : 'Manuel' }}
                                    </span>

                                    <!-- Amount -->
                                    <span class="font-semibold text-gray-900 dark:text-white">
                                        {{ $payment->formatted_amount }}
                                    </span>
                                </div>

                                <div class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                    <span>Créé le {{ $payment->created_at->format('d/m/Y à H:i') }}</span>
                                    @if($payment->paid_at)
                                        <span class="mx-2">•</span>
                                        <span>Payé le {{ $payment->paid_at->format('d/m/Y à H:i') }}</span>
                                    @endif
                                    @if($payment->refunded_at)
                                        <span class="mx-2">•</span>
                                        <span class="text-red-600 dark:text-red-400">Remboursé le {{ $payment->refunded_at->format('d/m/Y à H:i') }}</span>
                                    @endif
                                </div>

                                @if($payment->stripe_payment_intent_id)
                                    <div class="mt-1 text-xs text-gray-400 font-mono">
                                        Intent: {{ $payment->stripe_payment_intent_id }}
                                    </div>
                                @endif

                                @if($payment->notes)
                                    <div class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                                        <strong>Notes:</strong> {{ $payment->notes }}
                                    </div>
                                @endif

                                @if($payment->failure_reason)
                                    <div class="mt-2 text-sm text-red-600 dark:text-red-400">
                                        <strong>Erreur:</strong> {{ $payment->failure_reason }}
                                    </div>
                                @endif
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center gap-2">
                                @if($payment->canBeRefunded())
                                    <button wire:click="openRefundModal({{ $payment->id }})"
                                            class="inline-flex items-center px-3 py-1.5 border border-red-300 dark:border-red-600 rounded text-sm font-medium text-red-700 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30">
                                        Rembourser
                                    </button>
                                @endif

                                @if($payment->status === \App\Domain\Payment\Enums\PaymentStatus::PENDING)
                                    <button wire:click="cancelPayment({{ $payment->id }})"
                                            wire:confirm="Êtes-vous sûr de vouloir annuler ce paiement ?"
                                            class="inline-flex items-center px-3 py-1.5 border border-gray-300 dark:border-zinc-600 rounded text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-zinc-700">
                                        Annuler
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Manual Payment Modal -->
    @if($showManualPaymentModal)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeManualPaymentModal"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>

                <div class="inline-block align-bottom bg-white dark:bg-zinc-800 rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                    <div>
                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                            Enregistrer un paiement manuel
                        </h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Enregistrez un paiement effectué en espèces, chèque ou virement.
                        </p>
                    </div>

                    <form wire:submit.prevent="recordManualPayment" class="mt-6 space-y-4">
                        <div>
                            <label for="manualAmount" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Montant (en centimes)
                            </label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <input type="number"
                                       wire:model="manualAmount"
                                       id="manualAmount"
                                       min="100"
                                       step="100"
                                       class="block w-full rounded-md border-gray-300 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white pr-12 focus:ring-blue-500 focus:border-blue-500">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 dark:text-gray-400 sm:text-sm">centimes</span>
                                </div>
                            </div>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                = {{ number_format($manualAmount / 100, 2, ',', ' ') }} €
                            </p>
                            @error('manualAmount') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="manualNotes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Notes (optionnel)
                            </label>
                            <textarea wire:model="manualNotes"
                                      id="manualNotes"
                                      rows="3"
                                      class="mt-1 block w-full rounded-md border-gray-300 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white focus:ring-blue-500 focus:border-blue-500"
                                      placeholder="Ex: Paiement en espèces, Chèque n°..."></textarea>
                            @error('manualNotes') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div class="mt-6 flex justify-end gap-3">
                            <button type="button"
                                    wire:click="closeManualPaymentModal"
                                    class="px-4 py-2 border border-gray-300 dark:border-zinc-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-zinc-700">
                                Annuler
                            </button>
                            <button type="submit"
                                    class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700">
                                Enregistrer le paiement
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Refund Modal -->
    @if($showRefundModal)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeRefundModal"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>

                <div class="inline-block align-bottom bg-white dark:bg-zinc-800 rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                    <div>
                        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900/30">
                            <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <h3 class="mt-4 text-lg leading-6 font-medium text-gray-900 dark:text-white text-center">
                            Rembourser le paiement
                        </h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 text-center">
                            Cette action est irréversible. Le remboursement sera traité via Stripe.
                        </p>
                    </div>

                    <form wire:submit.prevent="processRefund" class="mt-6 space-y-4">
                        <div>
                            <label for="refundReason" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Motif du remboursement (obligatoire)
                            </label>
                            <textarea wire:model="refundReason"
                                      id="refundReason"
                                      rows="3"
                                      required
                                      minlength="10"
                                      class="mt-1 block w-full rounded-md border-gray-300 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white focus:ring-blue-500 focus:border-blue-500"
                                      placeholder="Indiquez le motif du remboursement..."></textarea>
                            @error('refundReason') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div class="mt-6 flex justify-end gap-3">
                            <button type="button"
                                    wire:click="closeRefundModal"
                                    class="px-4 py-2 border border-gray-300 dark:border-zinc-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-zinc-700">
                                Annuler
                            </button>
                            <button type="submit"
                                    class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700">
                                Confirmer le remboursement
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
