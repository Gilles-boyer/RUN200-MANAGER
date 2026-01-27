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
                        <a href="{{ route('pilot.races.index') }}" class="ml-2 text-carbon-500 hover:text-racing-red-500 dark:text-carbon-400 dark:hover:text-racing-red-400 transition-colors">
                            Courses
                        </a>
                    </li>
                    <li class="flex items-center">
                        <svg class="h-4 w-4 text-carbon-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <span class="ml-2 text-carbon-700 dark:text-carbon-300 font-medium">Inscription</span>
                    </li>
                </ol>
            </nav>

            <h1 class="text-3xl font-bold text-carbon-900 dark:text-white flex items-center gap-3">
                <span>🏁</span> S'inscrire à une course
            </h1>
            <p class="mt-2 text-carbon-600 dark:text-carbon-400">
                Complétez le formulaire pour participer à <span class="font-semibold text-racing-red-500">{{ $race->name }}</span>
            </p>
        </div>
    </div>

    {{-- Erreur globale --}}
    @if($errorMessage)
        <x-racing.alert type="danger" class="mb-6" dismissible>
            {{ $errorMessage }}
        </x-racing.alert>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Formulaire d'inscription --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Card Informations Pilote --}}
            <x-racing.card>
                <x-slot name="header">
                    <div class="flex items-center gap-2">
                        <span class="text-xl">👤</span>
                        <h2 class="font-semibold">Vos informations</h2>
                    </div>
                </x-slot>

                @if($this->pilot)
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="bg-carbon-50 dark:bg-carbon-800/50 rounded-xl p-4 border border-carbon-200 dark:border-carbon-700">
                            <span class="text-xs text-carbon-500 dark:text-carbon-400 uppercase tracking-wider">Nom complet</span>
                            <p class="mt-1 text-carbon-900 dark:text-white font-medium">{{ $this->pilot->fullName }}</p>
                        </div>
                        <div class="bg-carbon-50 dark:bg-carbon-800/50 rounded-xl p-4 border border-carbon-200 dark:border-carbon-700">
                            <span class="text-xs text-carbon-500 dark:text-carbon-400 uppercase tracking-wider">Numéro de licence</span>
                            <p class="mt-1 text-carbon-900 dark:text-white font-medium">
                                {{ $this->pilot->license_number ?? 'Non renseignée' }}
                            </p>
                        </div>
                    </div>
                @else
                    <x-racing.alert type="danger">
                        Profil pilote non trouvé. Veuillez créer votre profil avant de vous inscrire.
                    </x-racing.alert>
                @endif
            </x-racing.card>

            {{-- Card Sélection Voiture --}}
            <x-racing.card>
                <x-slot name="header">
                    <div class="flex items-center gap-2">
                        <span class="text-xl">🚗</span>
                        <h2 class="font-semibold">Sélectionnez votre voiture</h2>
                        <span class="text-racing-red-500">*</span>
                    </div>
                </x-slot>

                @if($this->cars->isEmpty())
                    <x-racing.alert type="warning">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                            <div>
                                <p class="font-semibold">Aucune voiture enregistrée</p>
                                <p class="text-sm mt-1 opacity-90">Vous devez d'abord ajouter une voiture à votre profil.</p>
                            </div>
                            <x-racing.button href="{{ route('pilot.cars.create') }}" variant="primary" size="sm" icon="🚗">
                                Ajouter une voiture
                            </x-racing.button>
                        </div>
                    </x-racing.alert>
                @else
                    <div class="space-y-3">
                        @foreach($this->cars as $car)
                            <label
                                class="relative flex cursor-pointer rounded-xl border-2 p-4 transition-all duration-200 hover:border-racing-red-400
                                    {{ $selectedCarId == $car->id
                                        ? 'border-racing-red-500 bg-racing-red-50 dark:bg-racing-red-900/20 ring-2 ring-racing-red-500/20'
                                        : 'border-carbon-200 dark:border-carbon-700 bg-white dark:bg-carbon-900' }}"
                            >
                                <input
                                    type="radio"
                                    wire:model.live="selectedCarId"
                                    name="car"
                                    value="{{ $car->id }}"
                                    class="sr-only"
                                >
                                <div class="flex items-center gap-4 w-full">
                                    {{-- Car Icon --}}
                                    <div class="flex-shrink-0 w-12 h-12 rounded-xl flex items-center justify-center
                                        {{ $selectedCarId == $car->id
                                            ? 'bg-racing-red-500 text-white'
                                            : 'bg-carbon-100 dark:bg-carbon-800 text-carbon-500' }}">
                                        <span class="text-2xl">🏎️</span>
                                    </div>

                                    {{-- Car Info --}}
                                    <div class="flex-1 min-w-0">
                                        <span class="block text-sm font-semibold text-carbon-900 dark:text-white truncate">
                                            {{ $car->brand?->name ?? '' }} {{ $car->model }}
                                        </span>
                                        <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-carbon-500 dark:text-carbon-400">
                                            @if($car->license_plate)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-carbon-100 dark:bg-carbon-800 font-mono">
                                                    {{ $car->license_plate }}
                                                </span>
                                            @endif
                                            @if($car->category)
                                                <x-racing.badge-status status="info" size="sm">
                                                    {{ $car->category->name ?? 'Catégorie inconnue' }}
                                                </x-racing.badge-status>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Check indicator --}}
                                    <div class="flex-shrink-0 w-6 h-6 rounded-full border-2 flex items-center justify-center transition-all
                                        {{ $selectedCarId == $car->id
                                            ? 'border-racing-red-500 bg-racing-red-500'
                                            : 'border-carbon-300 dark:border-carbon-600' }}">
                                        @if($selectedCarId == $car->id)
                                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        @endif
                                    </div>
                                </div>
                            </label>
                        @endforeach
                    </div>

                    <div class="mt-4 pt-4 border-t border-carbon-200 dark:border-carbon-700">
                        <a href="{{ route('pilot.cars.create') }}" class="inline-flex items-center gap-1 text-sm text-racing-red-500 hover:text-racing-red-600 dark:text-racing-red-400 dark:hover:text-racing-red-300 font-medium transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Ajouter une nouvelle voiture
                        </a>
                    </div>
                @endif
            </x-racing.card>

            {{-- Card Conditions --}}
            <x-racing.card>
                <x-slot name="header">
                    <div class="flex items-center gap-2">
                        <span class="text-xl">📋</span>
                        <h2 class="font-semibold">Conditions de participation</h2>
                    </div>
                </x-slot>

                <div class="space-y-4">
                    <div class="bg-carbon-50 dark:bg-carbon-800/50 rounded-xl p-4 border border-carbon-200 dark:border-carbon-700 text-sm text-carbon-600 dark:text-carbon-400">
                        <p>En vous inscrivant à cette course, vous vous engagez à :</p>
                        <ul class="mt-2 space-y-1 list-disc list-inside">
                            <li>Respecter le règlement de la course</li>
                            <li>Avoir un équipement conforme aux normes de sécurité</li>
                            <li>Certifier l'exactitude des informations fournies</li>
                            <li>Accepter les décisions des commissaires de course</li>
                        </ul>
                    </div>

                    <label class="flex items-start gap-3 cursor-pointer group">
                        <div class="relative flex-shrink-0 mt-0.5">
                            <input
                                type="checkbox"
                                wire:model="confirmTerms"
                                id="confirmTerms"
                                class="peer sr-only"
                            />
                            <div class="w-5 h-5 rounded-md border-2 transition-all duration-200 ease-out flex items-center justify-center
                                peer-focus:ring-2 peer-focus:ring-racing-red-500/20
                                peer-checked:bg-racing-red-500 peer-checked:border-racing-red-500
                                border-carbon-300 dark:border-carbon-600 group-hover:border-racing-red-400">
                            </div>
                            <svg
                                class="absolute inset-0 w-5 h-5 text-white pointer-events-none opacity-0 peer-checked:opacity-100 transition-opacity duration-150"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <span class="text-sm font-medium text-carbon-900 dark:text-carbon-100">
                                J'accepte les conditions de participation
                                <span class="text-racing-red-500 ml-0.5">*</span>
                            </span>
                            <p class="text-xs text-carbon-500 dark:text-carbon-400 mt-1">
                                Je confirme avoir pris connaissance du règlement de la course et m'engage à le respecter.
                            </p>
                        </div>
                    </label>
                </div>
            </x-racing.card>

            {{-- Boutons d'action --}}
            <div class="flex flex-col sm:flex-row items-center justify-end gap-3 pt-4">
                <x-racing.button href="{{ route('pilot.races.index') }}" variant="secondary">
                    Annuler
                </x-racing.button>
                <x-racing.button
                    type="submit"
                    variant="primary"
                    icon="✓"
                    wire:click="submit"
                    :disabled="$this->cars->isEmpty()"
                    wire:loading.attr="disabled"
                >
                    <span wire:loading.remove wire:target="submit">Confirmer l'inscription</span>
                    <span wire:loading wire:target="submit">Inscription en cours...</span>
                </x-racing.button>
            </div>
        </div>

        {{-- Sidebar - Résumé de la course --}}
        <div class="lg:col-span-1">
            <div class="sticky top-6 space-y-6">
                {{-- Race Summary Card --}}
                <x-racing.card :hover="false">
                    <x-slot name="header">
                        <div class="flex items-center gap-2">
                            <span class="text-xl">🏆</span>
                            <h2 class="font-semibold">Résumé de la course</h2>
                        </div>
                    </x-slot>

                    <div class="space-y-4">
                        {{-- Race Name --}}
                        <div>
                            <h3 class="text-xl font-bold text-carbon-900 dark:text-white">{{ $race->name }}</h3>
                            @if($race->season)
                                <p class="text-sm text-carbon-500 dark:text-carbon-400 mt-1">{{ $race->season->name }}</p>
                            @endif
                        </div>

                        {{-- Race Details --}}
                        <div class="space-y-3">
                            <div class="flex items-center gap-3 text-sm">
                                <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-carbon-100 dark:bg-carbon-800 flex items-center justify-center">
                                    <span>📅</span>
                                </div>
                                <div>
                                    <span class="text-carbon-500 dark:text-carbon-400 text-xs">Date</span>
                                    <p class="text-carbon-900 dark:text-white font-medium">{{ $race->race_date->translatedFormat('l d F Y') }}</p>
                                </div>
                            </div>

                            <div class="flex items-center gap-3 text-sm">
                                <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-carbon-100 dark:bg-carbon-800 flex items-center justify-center">
                                    <span>📍</span>
                                </div>
                                <div>
                                    <span class="text-carbon-500 dark:text-carbon-400 text-xs">Lieu</span>
                                    <p class="text-carbon-900 dark:text-white font-medium">{{ $race->location }}</p>
                                </div>
                            </div>

                            <div class="flex items-center gap-3 text-sm">
                                <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-carbon-100 dark:bg-carbon-800 flex items-center justify-center">
                                    <span>👥</span>
                                </div>
                                <div>
                                    <span class="text-carbon-500 dark:text-carbon-400 text-xs">Participants</span>
                                    <p class="text-carbon-900 dark:text-white font-medium">{{ $race->registrations->count() }} inscrit(s)</p>
                                </div>
                            </div>
                        </div>

                        {{-- Divider --}}
                        <div class="border-t border-carbon-200 dark:border-carbon-700"></div>

                        {{-- Price --}}
                        <div class="bg-racing-gradient-subtle rounded-xl p-4">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-carbon-600 dark:text-carbon-400">Frais d'inscription</span>
                                <span class="text-2xl font-bold text-racing-red-500">{{ $race->formatted_entry_fee }}</span>
                            </div>
                            <p class="mt-2 text-xs text-carbon-500 dark:text-carbon-400 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                                Paiement en ligne sécurisé par Stripe
                            </p>
                        </div>

                        {{-- Status Badge --}}
                        <div class="flex justify-center">
                            <x-racing.badge-status status="success" size="md">
                                Inscriptions ouvertes
                            </x-racing.badge-status>
                        </div>
                    </div>
                </x-racing.card>

                {{-- Help Card --}}
                <x-racing.card :hover="false" class="bg-carbon-50 dark:bg-carbon-800/50">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 w-10 h-10 rounded-xl bg-status-info/20 flex items-center justify-center">
                            <span class="text-xl">💡</span>
                        </div>
                        <div>
                            <h4 class="text-sm font-semibold text-carbon-900 dark:text-white">Besoin d'aide ?</h4>
                            <p class="text-xs text-carbon-500 dark:text-carbon-400 mt-1">
                                Si vous avez des questions concernant l'inscription, contactez notre équipe.
                            </p>
                        </div>
                    </div>
                </x-racing.card>
            </div>
        </div>
    </div>
</div>
