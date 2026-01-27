<div class="max-w-2xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Finaliser votre inscription</h1>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            Complétez votre paiement pour valider votre inscription
        </p>
    </div>

    <!-- Récapitulatif de l'inscription -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-medium text-gray-900 dark:text-white">Récapitulatif</h2>
        </div>

        <div class="p-6 space-y-4">
            <!-- Course -->
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Course</p>
                    <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $this->race->name }}</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        {{ $this->race->race_date->format('d/m/Y') }} - {{ $this->race->location }}
                    </p>
                </div>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300">
                    {{ $this->race->season?->name }}
                </span>
            </div>

            <!-- Pilote -->
            <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Pilote</p>
                <p class="text-base text-gray-900 dark:text-white">
                    {{ $this->pilot->first_name }} {{ $this->pilot->last_name }}
                </p>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Licence : {{ $this->pilot->license_number }}
                </p>
            </div>

            <!-- Voiture -->
            <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Voiture</p>
                <p class="text-base text-gray-900 dark:text-white">
                    #{{ $this->car->race_number }} - {{ $this->car->make }} {{ $this->car->model }}
                </p>
                @if($this->car->category)
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Catégorie : {{ $this->car->category->name }}
                    </p>
                @endif
            </div>
        </div>
    </div>

    <!-- Montant à payer -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden mb-6">
        <div class="p-6">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Frais d'inscription</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $this->registrationFee }}</p>
                </div>
                <div class="flex items-center space-x-2">
                    <svg class="h-8 w-auto" viewBox="0 0 60 25" xmlns="http://www.w3.org/2000/svg">
                        <path d="M59.64 14.28h-2.33v7.27h2.33v-7.27zM52.61 9.92c-.33 0-.65.03-.95.08v.5c.3-.07.63-.11.95-.11.92 0 1.41.33 1.41 1.01v.4h-.52c-1.6 0-2.61.67-2.61 1.92 0 1.08.74 1.77 1.88 1.77.76 0 1.34-.28 1.68-.75h.04l.12.62h1.86v-3.83c0-1.62-1.07-2.49-2.86-2.49v.88zm.89 3.79c0 .61-.45.95-1.07.95-.47 0-.77-.23-.77-.62 0-.48.43-.73 1.28-.73h.56v.4z" fill="#635BFF"/>
                        <path d="M44.33 14.28h2.31l.11.62h.04c.45-.49 1.05-.75 1.76-.75 1.5 0 2.49 1.21 2.49 2.87 0 1.9-1.17 3.11-2.7 3.11-.6 0-1.1-.2-1.46-.55h-.04v2.56h-2.33v-7.86h1.82zm2.9 1.65c-.63 0-1.12.51-1.12 1.31 0 .8.49 1.31 1.12 1.31.64 0 1.12-.51 1.12-1.31 0-.8-.48-1.31-1.12-1.31zM41.3 14.02c-.44 0-.79.16-1.08.49h-.04l-.1-.36h-1.86v7.27h2.33v-4.19c0-.49.28-.79.71-.79.39 0 .62.26.62.71v4.27h2.33v-4.59c0-1.73-.89-2.81-1.91-2.81zM34.78 9.25c-.77 0-1.38.62-1.38 1.38 0 .77.61 1.38 1.38 1.38s1.38-.61 1.38-1.38c0-.76-.61-1.38-1.38-1.38zm-1.17 5.03h2.33v7.27h-2.33v-7.27zM28.93 14.02c-.54 0-1.02.16-1.42.49l-.06-.36h-1.83v7.27h2.33v-4.29c0-.49.3-.73.68-.73.36 0 .58.23.58.66v4.36h2.33v-4.49c0-1.67-.78-2.91-2.61-2.91zM21.72 14.02c-1.92 0-3.24 1.31-3.24 3.18 0 1.91 1.25 3.16 3.5 3.16.84 0 1.55-.15 2.1-.44v-1.65c-.48.22-1.04.35-1.61.35-1.05 0-1.57-.44-1.57-1.15h3.4v-.84c0-1.68-1.03-2.61-2.58-2.61zm-.69 2.31c.02-.55.32-.92.77-.92.47 0 .75.37.75.92h-1.52zM14.78 9.25c-.77 0-1.38.62-1.38 1.38 0 .77.61 1.38 1.38 1.38s1.38-.61 1.38-1.38c0-.76-.61-1.38-1.38-1.38zm-1.16 5.03h2.33v7.27h-2.33v-7.27zM9.23 9.55h2.33v11.87H9.23V9.55zM4.96 14.28l.11.62h.04c.42-.5 1.01-.76 1.71-.76 1.5 0 2.49 1.21 2.49 2.87 0 1.9-1.17 3.11-2.7 3.11-.55 0-1.02-.17-1.36-.46v2.48H2.92v-7.86h2.04zm1.77 1.65c-.63 0-1.12.51-1.12 1.31 0 .8.49 1.31 1.12 1.31.64 0 1.12-.51 1.12-1.31 0-.8-.48-1.31-1.12-1.31z" fill="#635BFF"/>
                    </svg>
                    <span class="text-xs text-gray-500 dark:text-gray-400">Paiement sécurisé</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Message d'erreur -->
    @if($errorMessage)
        <div class="bg-red-50 dark:bg-red-900/20 border-l-4 border-red-400 p-4 mb-6 rounded">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-red-700 dark:text-red-300">{{ $errorMessage }}</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Actions -->
    <div class="flex flex-col sm:flex-row gap-4">
        <button
            wire:click="proceedToPayment"
            wire:loading.attr="disabled"
            wire:loading.class="opacity-50 cursor-not-allowed"
            class="flex-1 inline-flex justify-center items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition disabled:opacity-50"
            {{ $isProcessing ? 'disabled' : '' }}
        >
            <span wire:loading.remove wire:target="proceedToPayment">
                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
                Payer {{ $this->registrationFee }}
            </span>
            <span wire:loading wire:target="proceedToPayment" class="flex items-center">
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Redirection vers Stripe...
            </span>
        </button>

        <button
            wire:click="cancelRegistration"
            wire:confirm="Êtes-vous sûr de vouloir annuler votre inscription ?"
            class="flex-1 sm:flex-none inline-flex justify-center items-center px-6 py-3 border border-gray-300 dark:border-gray-600 text-base font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition"
        >
            Annuler l'inscription
        </button>
    </div>

    <!-- Informations supplémentaires -->
    <div class="mt-8 bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
        <h3 class="text-sm font-medium text-blue-800 dark:text-blue-300 mb-2">Informations</h3>
        <ul class="text-sm text-blue-700 dark:text-blue-400 space-y-1">
            <li>• Vous serez redirigé vers Stripe pour effectuer le paiement sécurisé.</li>
            <li>• Une fois le paiement effectué, votre inscription sera soumise pour validation.</li>
            <li>• Vous pouvez aussi vous inscrire directement sur le circuit le jour de la course.</li>
            <li>• En cas de problème, contactez l'organisation.</li>
        </ul>
    </div>
</div>
