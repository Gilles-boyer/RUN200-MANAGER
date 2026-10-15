<x-layouts.racing-public
    title="Page introuvable - RUN200"
    description="Cette page RUN200 est introuvable. Retournez à l’accueil ou consultez le calendrier des courses."
    :indexable="false"
>
    <section class="mx-auto flex min-h-[65vh] max-w-3xl flex-col items-center justify-center px-4 py-16 text-center sm:px-6">
        <p class="mb-4 font-display text-7xl font-bold tracking-tight text-racing-red-500 sm:text-8xl" aria-hidden="true">404</p>
        <h1 class="text-3xl font-bold text-carbon-900 dark:text-white sm:text-4xl">Page introuvable</h1>
        <p class="mt-4 max-w-xl text-base text-carbon-600 dark:text-carbon-300">
            Ce lien ne mène plus à une page disponible. Vérifiez son adresse ou reprenez votre navigation.
        </p>
        <div class="mt-8 flex w-full flex-col justify-center gap-3 sm:w-auto sm:flex-row">
            <a href="{{ route('home') }}" class="btn-racing-primary inline-flex min-h-11 items-center justify-center px-6 py-3 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-racing-red-500">
                Retour à l’accueil
            </a>
            <a href="{{ route('public.calendar') }}" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-carbon-300 px-6 py-3 font-medium text-carbon-800 transition-colors hover:bg-carbon-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-racing-red-500 dark:border-carbon-700 dark:text-carbon-100 dark:hover:bg-carbon-800">
                Voir le calendrier
            </a>
        </div>
    </section>
</x-layouts.racing-public>
