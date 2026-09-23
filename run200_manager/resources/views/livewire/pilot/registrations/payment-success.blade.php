<div class="max-w-2xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <div class="bg-white dark:bg-zinc-800 shadow rounded-lg overflow-hidden">
        <div class="px-6 py-8 text-center" aria-live="polite">
            @if($paymentState === 'confirmed')
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 dark:bg-green-900/30" aria-hidden="true">
                    <svg class="h-8 w-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </div>
                <h1 class="mt-6 text-2xl font-bold text-gray-900 dark:text-white">Paiement confirmé</h1>
                <p class="mt-2 text-gray-600 dark:text-gray-300">Le paiement de votre inscription à <strong>{{ $registration->race->name }}</strong> a été confirmé.</p>

                <div class="mt-6 rounded-lg bg-gray-50 dark:bg-zinc-900/50 p-4 text-left">
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                        <div><dt class="text-gray-500 dark:text-gray-400">Montant payé</dt><dd class="font-semibold text-gray-900 dark:text-white">{{ $payment->formatted_amount }}</dd></div>
                        @if($payment->paid_at)
                            <div><dt class="text-gray-500 dark:text-gray-400">Date du paiement</dt><dd class="font-semibold text-gray-900 dark:text-white">{{ $payment->paid_at->format('d/m/Y H:i') }}</dd></div>
                        @endif
                        <div><dt class="text-gray-500 dark:text-gray-400">Voiture</dt><dd class="font-semibold text-gray-900 dark:text-white">#{{ $registration->car->race_number }}</dd></div>
                    </dl>
                </div>

                @unless($canAccessEcard)
                    @if(in_array($registration->status, ['PENDING_PAYMENT', 'PENDING_VALIDATION'], true))
                        <p class="mt-6 text-gray-600 dark:text-gray-300">Votre paiement est reçu. Votre e-carte sera disponible après validation de votre inscription par l'organisation.</p>
                    @elseif(in_array($registration->status, ['REFUSED', 'CANCELLED'], true))
                        <p class="mt-6 text-gray-600 dark:text-gray-300">Votre inscription n'est plus active. Contactez l'organisation pour connaître les suites de votre paiement.</p>
                    @else
                        <p class="mt-6 text-gray-600 dark:text-gray-300">L'e-carte n'est pas disponible pour l'état actuel de votre inscription.</p>
                    @endif
                @endunless
            @elseif($paymentState === 'pending')
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Vérification du paiement en cours</h1>
                <p class="mt-3 text-gray-600 dark:text-gray-300">Nous attendons la confirmation de votre paiement pour <strong>{{ $registration->race->name }}</strong>. Cela peut prendre quelques instants. Ne lancez pas un nouveau paiement avant d'avoir vérifié son état.</p>
                <button type="button" wire:click="$refresh" class="mt-6 inline-flex justify-center rounded-md bg-blue-600 px-4 py-3 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">Actualiser le statut</button>
            @elseif($paymentState === 'not_confirmed')
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Paiement non confirmé</h1>
                <p class="mt-3 text-gray-600 dark:text-gray-300">Ce paiement n'a pas été confirmé. Consultez votre inscription pour vérifier son état ou réessayer si nécessaire.</p>
                @if(in_array($registration->status, ['PENDING_PAYMENT', 'ACCEPTED'], true))
                    <a href="{{ route('pilot.registrations.payment', $registration) }}" class="mt-6 inline-flex justify-center rounded-md bg-blue-600 px-4 py-3 text-sm font-medium text-white hover:bg-blue-700">Voir les options de paiement</a>
                @endif
            @else
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Paiement impossible à vérifier</h1>
                <p class="mt-3 text-gray-600 dark:text-gray-300">Ce lien ne permet pas de retrouver une session de paiement pour votre inscription. Consultez votre inscription pour connaître son état.</p>
            @endif

            <div class="mt-8 space-y-3">
                @if($canAccessEcard)
                    <a href="{{ route('pilot.registrations.ecard', $registration) }}" class="w-full inline-flex justify-center items-center rounded-md bg-blue-600 px-4 py-3 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">Voir ma e-carte</a>
                @endif
                <a href="{{ route('pilot.registrations.index') }}" class="w-full inline-flex justify-center items-center rounded-md border border-gray-300 dark:border-zinc-600 px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-zinc-700 hover:bg-gray-50 dark:hover:bg-zinc-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">Mes inscriptions</a>
            </div>
        </div>
    </div>
</div>
