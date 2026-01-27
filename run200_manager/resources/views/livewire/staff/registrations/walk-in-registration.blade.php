<div class="max-w-4xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    {{-- Racing Header --}}
    <div class="relative mb-8 -mx-4 sm:mx-0 px-4 sm:px-8 py-8 bg-racing-gradient-subtle rounded-none sm:rounded-2xl overflow-hidden">
        <div class="absolute top-0 right-0 w-48 h-48 bg-racing-red-500/5 rounded-full -translate-y-1/2 translate-x-1/2"></div>
        <div class="relative">
            <h1 class="text-2xl font-bold text-white flex items-center gap-2">
                <span>📝</span> Inscription sur place
            </h1>
            <p class="mt-2 text-gray-400">
                Inscrivez un pilote qui se présente directement au bureau avant la compétition.
            </p>
        </div>
    </div>

    {{-- Progress Steps --}}
    @if($currentStep < 5)
    <nav aria-label="Progress" class="mb-10">
        <ol role="list" class="flex items-center">
            @foreach([
                ['step' => 1, 'name' => 'Pilote', 'icon' => '👤'],
                ['step' => 2, 'name' => 'Voiture', 'icon' => '🚗'],
                ['step' => 3, 'name' => 'Course', 'icon' => '🏁'],
                ['step' => 4, 'name' => 'Paiement', 'icon' => '💳'],
            ] as $item)
                <li class="relative {{ $loop->last ? '' : 'pr-8 sm:pr-20 flex-1' }}">
                    @if($currentStep > $item['step'])
                        {{-- Completed step --}}
                        @if(!$loop->first)
                        <div class="absolute inset-0 flex items-center" aria-hidden="true">
                            <div class="h-0.5 w-full bg-racing-red-500"></div>
                        </div>
                        @endif
                        <button
                            type="button"
                            wire:click="goToStep({{ $item['step'] }})"
                            class="relative flex h-10 w-10 items-center justify-center rounded-xl bg-racing-red-600 hover:bg-racing-red-700 shadow-lg shadow-racing-red-500/30 transition-all"
                        >
                            <svg class="h-5 w-5 text-white" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    @elseif($currentStep === $item['step'])
                        {{-- Current step --}}
                        @if(!$loop->first)
                        <div class="absolute inset-0 flex items-center" aria-hidden="true">
                            <div class="h-0.5 w-full bg-carbon-700"></div>
                        </div>
                        @endif
                        <div class="relative flex h-10 w-10 items-center justify-center rounded-xl border-2 border-checkered-yellow-500 bg-carbon-800 shadow-lg shadow-checkered-yellow-500/20">
                            <span class="text-lg">{{ $item['icon'] }}</span>
                        </div>
                    @else
                        {{-- Upcoming step --}}
                        @if(!$loop->first)
                        <div class="absolute inset-0 flex items-center" aria-hidden="true">
                            <div class="h-0.5 w-full bg-carbon-700"></div>
                        </div>
                        @endif
                        <div class="relative flex h-10 w-10 items-center justify-center rounded-xl border-2 border-carbon-700 bg-carbon-800">
                            <span class="text-lg grayscale opacity-50">{{ $item['icon'] }}</span>
                        </div>
                    @endif
                    <span class="absolute -bottom-6 left-1/2 -translate-x-1/2 text-xs font-medium
                        {{ $currentStep > $item['step'] ? 'text-racing-red-500' : ($currentStep === $item['step'] ? 'text-checkered-yellow-500' : 'text-gray-500') }}">
                        {{ $item['name'] }}
                    </span>
                </li>
            @endforeach
        </ol>
    </nav>
    @endif

    {{-- Error Message --}}
    @if($errorMessage)
        <x-racing.alert type="danger" class="mb-6" dismissible>
            {{ $errorMessage }}
        </x-racing.alert>
    @endif

    <x-racing.card class="mt-10" noPadding>
        {{-- Step 1: Pilot Selection/Creation --}}
        @if($currentStep === 1)
        <div class="p-6">
            <h2 class="text-lg font-semibold text-white mb-6 flex items-center gap-2">
                <span>👤</span> Étape 1 : Sélectionner ou créer un pilote
            </h2>

            {{-- Mode Toggle --}}
            <div class="flex gap-4 mb-6">
                <button
                    type="button"
                    wire:click="$set('pilotMode', 'search')"
                    class="flex-1 py-4 px-4 rounded-xl border-2 transition-all text-center
                        {{ $pilotMode === 'search'
                            ? 'border-racing-red-500 bg-racing-red-500/10'
                            : 'border-carbon-700 hover:border-carbon-600' }}"
                >
                    <svg class="w-6 h-6 mx-auto mb-2 {{ $pilotMode === 'search' ? 'text-racing-red-500' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <span class="text-sm font-medium {{ $pilotMode === 'search' ? 'text-racing-red-500' : 'text-gray-300' }}">Rechercher existant</span>
                </button>
                <button
                    type="button"
                    wire:click="$set('pilotMode', 'create')"
                    class="flex-1 py-4 px-4 rounded-xl border-2 transition-all text-center
                        {{ $pilotMode === 'create'
                            ? 'border-racing-red-500 bg-racing-red-500/10'
                            : 'border-carbon-700 hover:border-carbon-600' }}"
                >
                    <svg class="w-6 h-6 mx-auto mb-2 {{ $pilotMode === 'create' ? 'text-racing-red-500' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    <span class="text-sm font-medium {{ $pilotMode === 'create' ? 'text-racing-red-500' : 'text-gray-300' }}">Créer nouveau</span>
                </button>
            </div>

            @if($pilotMode === 'search')
                @if($this->selectedPilot)
                    <div class="bg-status-success/10 border border-status-success/30 rounded-xl p-4 mb-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                @if($this->selectedPilot->photo_path)
                                    <img src="{{ Storage::url($this->selectedPilot->photo_path) }}" class="h-12 w-12 rounded-xl object-cover ring-2 ring-status-success/30">
                                @else
                                    <div class="h-12 w-12 rounded-xl bg-carbon-700 flex items-center justify-center ring-2 ring-status-success/30">
                                        <span class="text-white font-bold">
                                            {{ substr($this->selectedPilot->first_name, 0, 1) }}{{ substr($this->selectedPilot->last_name, 0, 1) }}
                                        </span>
                                    </div>
                                @endif
                                <div class="ml-4">
                                    <p class="font-semibold text-white">
                                        {{ $this->selectedPilot->first_name }} {{ $this->selectedPilot->last_name }}
                                    </p>
                                    <p class="text-sm text-gray-400">
                                        Licence <span class="text-checkered-yellow-500 font-mono">#{{ $this->selectedPilot->license_number }}</span> • {{ $this->selectedPilot->user->email }}
                                    </p>
                                </div>
                            </div>
                            <button type="button" wire:click="clearPilotSelection" class="p-2 text-gray-400 hover:text-white hover:bg-carbon-700 rounded-lg transition-colors">
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                    </div>
                @else
                    <div class="relative">
                        <x-racing.form.input
                            wire:model.live.debounce.300ms="pilotSearch"
                            placeholder="Rechercher par nom, prénom, email ou numéro de licence..."
                            icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>'
                        />
                        <div wire:loading wire:target="pilotSearch" class="absolute right-3 top-3">
                            <svg class="animate-spin h-5 w-5 text-racing-red-500" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>
                    </div>

                    @if($this->searchResults->count() > 0)
                        <ul class="mt-4 divide-y divide-carbon-700/50 border border-carbon-700 rounded-xl overflow-hidden">
                            @foreach($this->searchResults as $pilot)
                                <li>
                                    <button type="button" wire:click="selectPilot({{ $pilot->id }})"
                                        class="w-full px-4 py-3 flex items-center hover:bg-carbon-700/50 text-left transition-colors">
                                        @if($pilot->photo_path)
                                            <img src="{{ Storage::url($pilot->photo_path) }}" class="h-10 w-10 rounded-lg object-cover">
                                        @else
                                            <div class="h-10 w-10 rounded-lg bg-carbon-700 flex items-center justify-center">
                                                <span class="text-gray-400 font-medium">{{ substr($pilot->first_name, 0, 1) }}{{ substr($pilot->last_name, 0, 1) }}</span>
                                            </div>
                                        @endif
                                        <div class="ml-4 flex-1">
                                            <p class="text-sm font-medium text-white">{{ $pilot->first_name }} {{ $pilot->last_name }}</p>
                                            <p class="text-sm text-gray-400">Licence <span class="text-checkered-yellow-500 font-mono">#{{ $pilot->license_number }}</span></p>
                                        </div>
                                        <svg class="h-5 w-5 text-gray-500" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    @elseif(strlen($pilotSearch) >= 2)
                        <div class="mt-4 py-6">
                            <x-racing.empty-state icon="👤" title="Aucun pilote trouvé" description="Essayez de créer un nouveau pilote." />
                        </div>
                    @endif
                @endif
            @else
                {{-- Create new pilot form --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-racing.form.input wire:model="newPilotFirstName" label="Prénom *" :error="$errors->first('newPilotFirstName')" />
                    <x-racing.form.input wire:model="newPilotLastName" label="Nom *" :error="$errors->first('newPilotLastName')" />
                    <x-racing.form.input type="email" wire:model="newPilotEmail" label="Email *" :error="$errors->first('newPilotEmail')" />
                    <x-racing.form.input wire:model="newPilotLicense" label="N° de licence *" :error="$errors->first('newPilotLicense')" />
                    <x-racing.form.input type="date" wire:model="newPilotBirthDate" label="Date de naissance *" :error="$errors->first('newPilotBirthDate')" />
                    <x-racing.form.input wire:model="newPilotBirthPlace" label="Lieu de naissance *" :error="$errors->first('newPilotBirthPlace')" />
                    <x-racing.form.input type="tel" wire:model="newPilotPhone" label="Téléphone *" :error="$errors->first('newPilotPhone')" />
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Photo (optionnel)</label>
                        <input type="file" wire:model="newPilotPhoto" accept="image/*"
                            class="w-full text-sm text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-racing-red-600 file:text-white hover:file:bg-racing-red-700 transition-all">
                    </div>
                    <div class="md:col-span-2">
                        <x-racing.form.textarea wire:model="newPilotAddress" label="Adresse *" rows="2" :error="$errors->first('newPilotAddress')" />
                    </div>
                    <div class="md:col-span-2">
                        <label class="flex items-center cursor-pointer group">
                            <input type="checkbox" wire:model.live="newPilotIsMinor" class="rounded border-carbon-600 bg-carbon-700 text-racing-red-600 focus:ring-racing-red-500">
                            <span class="ml-2 text-sm text-gray-300 group-hover:text-white transition-colors">Le pilote est mineur</span>
                        </label>
                    </div>
                    @if($newPilotIsMinor)
                        <x-racing.form.input wire:model="newPilotGuardianFirstName" label="Prénom du tuteur" />
                        <x-racing.form.input wire:model="newPilotGuardianLastName" label="Nom du tuteur" />
                        <div class="md:col-span-2">
                            <x-racing.form.input wire:model="newPilotGuardianLicense" label="N° de licence du tuteur" />
                        </div>
                    @endif
                </div>
            @endif
        </div>
        @endif

        {{-- Step 2: Car Selection/Creation --}}
        @if($currentStep === 2)
        <div class="p-6">
            <h2 class="text-lg font-semibold text-white mb-6 flex items-center gap-2">
                <span>🚗</span> Étape 2 : Sélectionner ou créer une voiture
            </h2>

            @if($pilotMode === 'search' && $this->pilotCars->count() > 0)
                <div class="flex gap-4 mb-6">
                    <button type="button" wire:click="$set('carMode', 'select')"
                        class="flex-1 py-3 px-4 rounded-xl border-2 transition-all
                            {{ $carMode === 'select' ? 'border-racing-red-500 bg-racing-red-500/10 text-racing-red-500' : 'border-carbon-700 text-gray-300 hover:border-carbon-600' }}">
                        Utiliser une voiture existante
                    </button>
                    <button type="button" wire:click="$set('carMode', 'create')"
                        class="flex-1 py-3 px-4 rounded-xl border-2 transition-all
                            {{ $carMode === 'create' ? 'border-racing-red-500 bg-racing-red-500/10 text-racing-red-500' : 'border-carbon-700 text-gray-300 hover:border-carbon-600' }}">
                        Ajouter une nouvelle voiture
                    </button>
                </div>
            @endif

            @if($carMode === 'select' && $this->pilotCars->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($this->pilotCars as $car)
                        <button type="button" wire:click="selectCar({{ $car->id }})"
                            class="p-4 rounded-xl border-2 text-left transition-all
                                {{ $selectedCarId === $car->id ? 'border-racing-red-500 bg-racing-red-500/10' : 'border-carbon-700 hover:border-carbon-600' }}">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-semibold text-white">
                                        <span class="text-racing-red-500">#{{ $car->race_number }}</span> — {{ $car->make }} {{ $car->model }}
                                    </p>
                                    <p class="text-sm text-gray-400">Catégorie : {{ $car->category->name ?? 'Non définie' }}</p>
                                </div>
                                @if($selectedCarId === $car->id)
                                    <svg class="h-6 w-6 text-racing-red-500" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                                    </svg>
                                @endif
                            </div>
                        </button>
                    @endforeach
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-racing.form.input type="number" wire:model="newCarRaceNumber" label="Numéro de course *" min="0" max="999" :error="$errors->first('newCarRaceNumber')" />
                    <x-racing.form.select wire:model="newCarCategoryId" label="Catégorie *" :error="$errors->first('newCarCategoryId')">
                        <option value="">Sélectionner une catégorie</option>
                        @foreach($this->categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </x-racing.form.select>
                    <x-racing.form.input wire:model="newCarMake" label="Marque *" :error="$errors->first('newCarMake')" />
                    <x-racing.form.input wire:model="newCarModel" label="Modèle *" :error="$errors->first('newCarModel')" />
                </div>
            @endif
        </div>
        @endif

        {{-- Step 3: Race Selection --}}
        @if($currentStep === 3)
        <div class="p-6">
            <h2 class="text-lg font-semibold text-white mb-6 flex items-center gap-2">
                <span>🏁</span> Étape 3 : Sélectionner une course
            </h2>

            @if($this->availableRaces->count() > 0)
                <div class="space-y-4">
                    @foreach($this->availableRaces as $race)
                        <button type="button" wire:click="$set('selectedRaceId', {{ $race->id }})"
                            class="w-full p-4 rounded-xl border-2 text-left transition-all
                                {{ $selectedRaceId === $race->id ? 'border-racing-red-500 bg-racing-red-500/10' : 'border-carbon-700 hover:border-carbon-600' }}">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-semibold text-white">{{ $race->name }}</p>
                                    <p class="text-sm text-gray-400">
                                        {{ \Carbon\Carbon::parse($race->race_date)->format('d/m/Y') }}
                                        @if($race->location) • {{ $race->location }} @endif
                                    </p>
                                    @if($race->entry_fee)
                                        <p class="text-sm font-semibold text-checkered-yellow-500 mt-1">
                                            Frais d'inscription : {{ number_format($race->entry_fee, 2) }} €
                                        </p>
                                    @endif
                                </div>
                                @if($selectedRaceId === $race->id)
                                    <svg class="h-6 w-6 text-racing-red-500" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                                    </svg>
                                @endif
                            </div>
                        </button>
                    @endforeach
                </div>
            @else
                <x-racing.empty-state icon="🏁" title="Aucune course disponible" description="Aucune course ouverte disponible pour le moment." />
            @endif
        </div>
        @endif

        {{-- Step 4: Payment --}}
        @if($currentStep === 4)
        <div class="p-6">
            <h2 class="text-lg font-semibold text-white mb-6 flex items-center gap-2">
                <span>💳</span> Étape 4 : Enregistrer le paiement
            </h2>

            {{-- Summary --}}
            <div class="bg-carbon-700/30 rounded-xl p-4 mb-6 border border-carbon-700/50">
                <h3 class="text-sm font-medium text-gray-400 mb-3">📋 Résumé de l'inscription</h3>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Pilote :</dt>
                        <dd class="font-medium text-white">
                            @if($this->selectedPilot)
                                {{ $this->selectedPilot->first_name }} {{ $this->selectedPilot->last_name }}
                            @else
                                {{ $newPilotFirstName }} {{ $newPilotLastName }} <span class="text-checkered-yellow-500">(nouveau)</span>
                            @endif
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Voiture :</dt>
                        <dd class="font-medium text-white">
                            @if($selectedCarId && $this->pilotCars)
                                @php $car = $this->pilotCars->find($selectedCarId) @endphp
                                @if($car)
                                    <span class="text-racing-red-500">#{{ $car->race_number }}</span> — {{ $car->make }} {{ $car->model }}
                                @endif
                            @else
                                <span class="text-racing-red-500">#{{ $newCarRaceNumber }}</span> — {{ $newCarMake }} {{ $newCarModel }} <span class="text-checkered-yellow-500">(nouvelle)</span>
                            @endif
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Course :</dt>
                        <dd class="font-medium text-checkered-yellow-500">{{ $this->selectedRace?->name }}</dd>
                    </div>
                    @if($this->selectedRace?->entry_fee)
                    <div class="flex justify-between pt-2 border-t border-carbon-700/50">
                        <dt class="font-medium text-white">Montant à payer :</dt>
                        <dd class="font-bold text-xl text-checkered-yellow-500">{{ number_format($this->selectedRace->entry_fee, 2) }} €</dd>
                    </div>
                    @endif
                </dl>
            </div>

            {{-- Payment Method --}}
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-300 mb-3">Mode de paiement *</label>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @foreach([
                        ['value' => 'cash', 'icon' => '💵', 'label' => 'Espèces'],
                        ['value' => 'bank_transfer', 'icon' => '🏦', 'label' => 'Virement'],
                        ['value' => 'card_onsite', 'icon' => '💳', 'label' => 'CB sur place'],
                    ] as $method)
                        <button type="button" wire:click="$set('paymentMethod', '{{ $method['value'] }}')"
                            class="p-4 rounded-xl border-2 text-center transition-all
                                {{ $paymentMethod === $method['value'] ? 'border-racing-red-500 bg-racing-red-500/10' : 'border-carbon-700 hover:border-carbon-600' }}">
                            <span class="text-2xl mb-2 block">{{ $method['icon'] }}</span>
                            <span class="font-medium {{ $paymentMethod === $method['value'] ? 'text-racing-red-500' : 'text-gray-300' }}">{{ $method['label'] }}</span>
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Payment Status --}}
            <div class="mb-6">
                <label class="flex items-center cursor-pointer group">
                    <input type="checkbox" wire:model="paymentReceived" class="rounded border-carbon-600 bg-carbon-700 text-racing-red-600 focus:ring-racing-red-500">
                    <span class="ml-2 text-sm text-gray-300 group-hover:text-white transition-colors">Paiement reçu</span>
                </label>
                <p class="mt-1 text-xs text-gray-500">Décochez si le paiement est attendu (ex: virement en cours)</p>
            </div>

            {{-- Notes --}}
            <x-racing.form.textarea wire:model="paymentNotes" label="Notes (optionnel)" rows="2" placeholder="Informations supplémentaires sur le paiement..." />
        </div>
        @endif

        {{-- Step 5: Success --}}
        @if($currentStep === 5)
        <div class="p-6 text-center">
            <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-2xl bg-status-success/20 mb-6">
                <span class="text-4xl">🎉</span>
            </div>

            <h2 class="text-2xl font-bold text-white mb-2">Inscription créée avec succès !</h2>
            <p class="text-gray-400 mb-8">{{ $successMessage }}</p>

            @if($createdRegistration)
            <div class="bg-carbon-700/30 rounded-xl p-6 text-left mb-8 max-w-md mx-auto border border-carbon-700/50">
                <h3 class="text-sm font-medium text-gray-400 mb-4 text-center">📋 Détails de l'inscription</h3>
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Pilote :</dt>
                        <dd class="font-medium text-white">{{ $createdRegistration->pilot->first_name }} {{ $createdRegistration->pilot->last_name }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Voiture :</dt>
                        <dd class="font-medium text-white"><span class="text-racing-red-500">#{{ $createdRegistration->car->race_number }}</span> — {{ $createdRegistration->car->make }} {{ $createdRegistration->car->model }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Course :</dt>
                        <dd class="font-medium text-checkered-yellow-500">{{ $createdRegistration->race->name }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Statut :</dt>
                        <dd><span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-status-success/20 text-status-success border border-status-success/30">Accepté</span></dd>
                    </div>
                    @if($createdRegistration->payments->first())
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Paiement :</dt>
                        <dd>
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold
                                {{ $createdRegistration->payments->first()->status === 'paid'
                                    ? 'bg-status-success/20 text-status-success border border-status-success/30'
                                    : 'bg-status-warning/20 text-status-warning border border-status-warning/30' }}">
                                {{ $createdRegistration->payments->first()->status === 'paid' ? 'Payé' : 'En attente' }}
                            </span>
                        </dd>
                    </div>
                    @endif
                </dl>
            </div>
            @endif

            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <x-racing.button wire:click="createAnother">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Nouvelle inscription
                </x-racing.button>
                <x-racing.button href="{{ route('staff.dashboard') }}" variant="secondary">
                    Retour au tableau de bord
                </x-racing.button>
            </div>
        </div>
        @endif

        {{-- Navigation Buttons --}}
        @if($currentStep < 5)
        <div class="px-6 py-4 bg-carbon-700/30 border-t border-carbon-700/50 flex justify-between">
            @if($currentStep > 1)
                <x-racing.button wire:click="previousStep" variant="secondary">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Précédent
                </x-racing.button>
            @else
                <div></div>
            @endif

            @if($currentStep < 4)
                <x-racing.button wire:click="nextStep" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="nextStep">Suivant</span>
                    <span wire:loading wire:target="nextStep" class="flex items-center">
                        <svg class="animate-spin h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Chargement...
                    </span>
                    <svg wire:loading.remove wire:target="nextStep" class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </x-racing.button>
            @else
                <x-racing.button wire:click="submit" wire:loading.attr="disabled" variant="success">
                    <span wire:loading.remove wire:target="submit">Confirmer l'inscription</span>
                    <span wire:loading wire:target="submit" class="flex items-center">
                        <svg class="animate-spin h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Création en cours...
                    </span>
                    <svg wire:loading.remove wire:target="submit" class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </x-racing.button>
            @endif
        </div>
        @endif
    </x-racing.card>
</div>
