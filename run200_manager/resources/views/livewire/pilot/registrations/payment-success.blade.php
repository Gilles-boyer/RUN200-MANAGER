<div class="max-w-2xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <div class="bg-white dark:bg-zinc-800 shadow rounded-lg overflow-hidden">
        <div class="px-6 py-8 text-center">
            <!-- Success Icon -->
            <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-green-100 dark:bg-green-900/30">
                <svg class="h-10 w-10 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>

            <h1 class="mt-6 text-2xl font-bold text-gray-900 dark:text-white">
                Paiement réussi !
            </h1>

            <p class="mt-2 text-gray-500 dark:text-gray-400">
                Votre inscription à la course <strong>{{ $registration->race->name }}</strong> a été payée avec succès.
            </p>

            @if($payment)
                <div class="mt-6 bg-gray-50 dark:bg-zinc-900/50 rounded-lg p-4">
                    <dl class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Montant payé</dt>
                            <dd class="font-semibold text-gray-900 dark:text-white">
                                {{ $payment->formatted_amount }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Date</dt>
                            <dd class="font-semibold text-gray-900 dark:text-white">
                                {{ $payment->paid_at?->format('d/m/Y H:i') ?? now()->format('d/m/Y H:i') }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Voiture</dt>
                            <dd class="font-semibold text-gray-900 dark:text-white">
                                #{{ $registration->car->race_number }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Statut</dt>
                            <dd>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-400">
                                    Payé
                                </span>
                            </dd>
                        </div>
                    </dl>
                </div>
            @endif

            <div class="mt-8 space-y-3">
                <a href="{{ route('pilot.registrations.ecard', $registration) }}"
                   class="w-full inline-flex justify-center items-center px-4 py-3 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/>
                    </svg>
                    Voir ma e-carte
                </a>

                <a href="{{ route('pilot.dashboard') }}"
                   class="w-full inline-flex justify-center items-center px-4 py-3 border border-gray-300 dark:border-zinc-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-zinc-700 hover:bg-gray-50 dark:hover:bg-zinc-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Retour au tableau de bord
                </a>
            </div>
        </div>
    </div>
</div>
