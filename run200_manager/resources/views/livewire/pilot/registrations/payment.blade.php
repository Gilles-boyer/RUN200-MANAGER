<div>
    {{-- Header avec gradient --}}
    <div class="relative mb-8 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-8 bg-racing-gradient-subtle overflow-hidden">
        {{-- Decorative elements --}}
        <div class="absolute top-0 right-0 w-64 h-64 bg-racing-red-500/5 rounded-full -translate-y-1/2 translate-x-1/2"></div>
        <div class="absolute bottom-0 left-0 w-32 h-32 bg-checkered-yellow-500/10 rounded-full translate-y-1/2 -translate-x-1/2"></div>

        <div class="relative">
            {{-- Breadcrumb --}}
            <nav class="flex mb-4" aria-label="Breadcrumb">
                <ol class="flex items-center space-x-2 text-sm">
                    <li>
                        <a href="{{ route('pilot.dashboard') }}" class="text-carbon-500 hover:text-racing-red-500 dark:text-carbon-400 dark:hover:text-racing-red-400 transition-colors">
                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                            </svg>
                        </a>
                    </li>
                    <li class="flex items-center">
                        <svg class="h-4 w-4 text-carbon-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <a href="{{ route('pilot.registrations.index') }}" class="ml-2 text-carbon-500 hover:text-racing-red-500 dark:text-carbon-400 dark:hover:text-racing-red-400 transition-colors">
                            Mes inscriptions
                        </a>
                    </li>
                    <li class="flex items-center">
                        <svg class="h-4 w-4 text-carbon-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <span class="ml-2 text-carbon-700 dark:text-carbon-300 font-medium">Paiement</span>
                    </li>
                </ol>
            </nav>

            <h1 class="text-3xl font-bold text-carbon-900 dark:text-white flex items-center gap-3">
                <span>💳</span> Paiement de l'inscription
            </h1>
            <p class="mt-2 text-carbon-600 dark:text-carbon-400">
                Course : <span class="font-semibold text-racing-red-500">{{ $registration->race->name }}</span>
            </p>
        </div>
    </div>

    <div class="max-w-3xl mx-auto">
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
            {{-- Main Content --}}
            <div class="lg:col-span-3">
                <x-racing.card>
                    {{-- Payment Content --}}
                    @if($this->hasPaidPayment)
                        {{-- Already Paid --}}
                        <div class="text-center py-6">
                            <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-status-success/20">
                                <span class="text-4xl">✅</span>
                            </div>
                            <h3 class="mt-6 text-xl font-bold text-carbon-900 dark:text-white">
                                Inscription payée
                            </h3>
                            <p class="mt-2 text-sm text-carbon-500 dark:text-carbon-400">
                                Votre inscription a été payée le {{ $this->paidPayment->paid_at->format('d/m/Y à H:i') }}
                            </p>
                            <p class="mt-4 text-3xl font-bold text-status-success">
                                {{ $this->paidPayment->formatted_amount }}
                            </p>
                            <div class="mt-8">
                                <x-racing.button href="{{ route('pilot.registrations.ecard', $registration) }}" variant="primary" icon="🎫">
                                    Voir ma e-carte
                                </x-racing.button>
                            </div>
                        </div>

                    @elseif($this->pendingPayment)
                        {{-- Pending Payment --}}
                        <div class="text-center py-6">
                            <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-status-warning/20">
                                <span class="text-4xl">⏳</span>
                            </div>
                            <h3 class="mt-6 text-xl font-bold text-carbon-900 dark:text-white">
                                Paiement en attente
                            </h3>
                            <p class="mt-2 text-sm text-carbon-500 dark:text-carbon-400">
                                Vous avez un paiement en cours. Cliquez ci-dessous pour le finaliser.
                            </p>
                            <p class="mt-4 text-3xl font-bold text-racing-red-500">
                                {{ $this->formattedFee }}
                            </p>
                            <div class="mt-8 space-y-3">
                                <x-racing.button
                                    wire:click="resumePayment"
                                    wire:loading.attr="disabled"
                                    variant="primary"
                                    class="w-full justify-center"
                                >
                                    <span wire:loading.remove wire:target="resumePayment">Reprendre le paiement</span>
                                    <span wire:loading wire:target="resumePayment">Redirection...</span>
                                </x-racing.button>
                                <x-racing.button
                                    wire:click="initiateStripePayment"
                                    wire:loading.attr="disabled"
                                    variant="secondary"
                                    class="w-full justify-center"
                                >
                                    <span wire:loading.remove wire:target="initiateStripePayment">Créer un nouveau paiement</span>
                                    <span wire:loading wire:target="initiateStripePayment">Création...</span>
                                </x-racing.button>
                            </div>
                        </div>

                    @else
                        {{-- New Payment --}}
                        <div class="text-center py-6">
                            <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-racing-red-500/20">
                                <span class="text-4xl">💳</span>
                            </div>
                            <h3 class="mt-6 text-xl font-bold text-carbon-900 dark:text-white">
                                @if($registration->status === 'PENDING_PAYMENT')
                                    Finalisez votre inscription
                                @else
                                    Frais d'inscription
                                @endif
                            </h3>
                            <p class="mt-2 text-sm text-carbon-500 dark:text-carbon-400 max-w-sm mx-auto">
                                @if($registration->status === 'PENDING_PAYMENT')
                                    Votre inscription est en attente de paiement. Réglez par carte bancaire pour valider votre demande.
                                @else
                                    Réglez votre inscription par carte bancaire de manière sécurisée via Stripe.
                                @endif
                            </p>
                            <p class="mt-6 text-4xl font-bold text-racing-red-500">
                                {{ $this->formattedFee }}
                            </p>

                            @if($errorMessage)
                                <div class="mt-6">
                                    <x-racing.alert type="danger">
                                        {{ $errorMessage }}
                                    </x-racing.alert>
                                </div>
                            @endif

                            <div class="mt-8">
                                <x-racing.button
                                    wire:click="initiateStripePayment"
                                    wire:loading.attr="disabled"
                                    :disabled="$isProcessing"
                                    variant="primary"
                                    size="lg"
                                    class="w-full justify-center"
                                >
                                    <span wire:loading.remove wire:target="initiateStripePayment" class="flex items-center gap-2">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                        </svg>
                                        Payer {{ $this->formattedFee }}
                                    </span>
                                    <span wire:loading wire:target="initiateStripePayment" class="flex items-center gap-2">
                                        <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Redirection vers Stripe...
                                    </span>
                                </x-racing.button>
                            </div>

                            <p class="mt-6 text-xs text-carbon-500 dark:text-carbon-400 flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                                Paiement sécurisé par Stripe
                            </p>
                        </div>
                    @endif
                </x-racing.card>
            </div>

            {{-- Sidebar - Registration Details --}}
            <div class="lg:col-span-2">
                <x-racing.card :hover="false">
                    <x-slot name="header">
                        <div class="flex items-center gap-2">
                            <span class="text-xl">📋</span>
                            <h2 class="font-semibold">Détails de l'inscription</h2>
                        </div>
                    </x-slot>

                    <div class="space-y-4">
                        <div class="flex items-center gap-3">
                            <div class="flex-shrink-0 w-10 h-10 rounded-xl bg-carbon-100 dark:bg-carbon-800 flex items-center justify-center">
                                <span>👤</span>
                            </div>
                            <div class="min-w-0">
                                <span class="text-xs text-carbon-500 dark:text-carbon-400">Pilote</span>
                                <p class="text-sm font-medium text-carbon-900 dark:text-white truncate">
                                    {{ $registration->pilot->first_name }} {{ $registration->pilot->last_name }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <div class="flex-shrink-0 w-10 h-10 rounded-xl bg-carbon-100 dark:bg-carbon-800 flex items-center justify-center">
                                <span>🎫</span>
                            </div>
                            <div class="min-w-0">
                                <span class="text-xs text-carbon-500 dark:text-carbon-400">Licence</span>
                                <p class="text-sm font-medium text-carbon-900 dark:text-white truncate">
                                    {{ $registration->pilot->license_number }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <div class="flex-shrink-0 w-10 h-10 rounded-xl bg-carbon-100 dark:bg-carbon-800 flex items-center justify-center">
                                <span>🏎️</span>
                            </div>
                            <div class="min-w-0">
                                <span class="text-xs text-carbon-500 dark:text-carbon-400">Voiture</span>
                                <p class="text-sm font-medium text-carbon-900 dark:text-white truncate">
                                    #{{ (string) $registration->car->race_number }} - {{ $registration->car->make }} {{ $registration->car->model }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <div class="flex-shrink-0 w-10 h-10 rounded-xl bg-carbon-100 dark:bg-carbon-800 flex items-center justify-center">
                                <span>📅</span>
                            </div>
                            <div class="min-w-0">
                                <span class="text-xs text-carbon-500 dark:text-carbon-400">Date de course</span>
                                <p class="text-sm font-medium text-carbon-900 dark:text-white truncate">
                                    {{ $registration->race->race_date?->translatedFormat('l d F Y') ?? 'À définir' }}
                                </p>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-carbon-200 dark:border-carbon-700">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-carbon-500 dark:text-carbon-400">Statut</span>
                                <x-racing.badge-status :status="$registration->status">
                                    {{ $registration->status_label ?? $registration->status }}
                                </x-racing.badge-status>
                            </div>
                        </div>
                    </div>
                </x-racing.card>

                {{-- Security Card --}}
                <x-racing.card :hover="false" class="mt-6 bg-carbon-50 dark:bg-carbon-800/50">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 w-10 h-10 rounded-xl bg-status-success/20 flex items-center justify-center">
                            <span class="text-xl">🔒</span>
                        </div>
                        <div>
                            <h4 class="text-sm font-semibold text-carbon-900 dark:text-white">Paiement sécurisé</h4>
                            <p class="text-xs text-carbon-500 dark:text-carbon-400 mt-1">
                                Vos données bancaires sont protégées par le cryptage SSL de Stripe. Nous ne stockons aucune information de carte.
                            </p>
                        </div>
                    </div>
                </x-racing.card>
            </div>
        </div>
    </div>
</div>
