<div class="max-w-2xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <div class="bg-white dark:bg-zinc-800 shadow rounded-lg overflow-hidden">
        <div class="px-6 py-8 text-center">
            <!-- Cancel Icon -->
            <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-yellow-100 dark:bg-yellow-900/30">
                <svg class="h-10 w-10 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>

            <h1 class="mt-6 text-2xl font-bold text-gray-900 dark:text-white">
                Paiement annulé
            </h1>

            <p class="mt-2 text-gray-500 dark:text-gray-400">
                Le paiement de votre inscription à la course <strong>{{ $registration->race->name }}</strong> a été annulé.
            </p>

            <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">
                Vous pouvez réessayer à tout moment depuis la page de paiement.
            </p>

            <div class="mt-8 space-y-3">
                <a href="{{ route('pilot.registrations.payment', $registration) }}"
                   class="w-full inline-flex justify-center items-center px-4 py-3 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Réessayer le paiement
                </a>

                <a href="{{ route('pilot.dashboard') }}"
                   class="w-full inline-flex justify-center items-center px-4 py-3 border border-gray-300 dark:border-zinc-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-zinc-700 hover:bg-gray-50 dark:hover:bg-zinc-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Retour au tableau de bord
                </a>
            </div>
        </div>
    </div>
</div>
