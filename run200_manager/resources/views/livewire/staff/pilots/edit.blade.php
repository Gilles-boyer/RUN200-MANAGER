<div class="space-y-6">
    {{-- Racing Header --}}
    <div class="relative overflow-hidden rounded-xl bg-racing-gradient-subtle border border-carbon-700/50 p-6">
        <div class="absolute top-0 right-0 w-32 h-32 opacity-5">
            <svg viewBox="0 0 100 100" fill="currentColor" class="text-racing-red-500">
                <path d="M50 5L90 25v50L50 95 10 75V25L50 5z"/>
            </svg>
        </div>
        <div class="relative flex items-center justify-between">
            <div>
                <nav class="flex items-center space-x-2 text-sm mb-2">
                    <a href="{{ route('staff.pilots.index') }}" class="text-carbon-400 hover:text-white transition-colors" wire:navigate>
                        Pilotes
                    </a>
                    <svg class="w-4 h-4 text-carbon-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                    <span class="text-carbon-300">{{ $pilot->last_name }} {{ $pilot->first_name }}</span>
                </nav>
                <h1 class="text-2xl font-bold text-white flex items-center gap-3">
                    <div class="p-2 bg-racing-red-500/20 rounded-lg">
                        <svg class="w-6 h-6 text-racing-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    Fiche pilote : {{ $pilot->last_name }} {{ $pilot->first_name }}
                </h1>
            </div>
            <x-racing.button href="{{ route('staff.pilots.index') }}" variant="secondary" wire:navigate>
                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Retour à la liste
            </x-racing.button>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if (session()->has('success'))
        <x-racing.alert type="success">
            {{ session('success') }}
        </x-racing.alert>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Edit Form --}}
        <div class="lg:col-span-2">
            <form wire:submit="save">
                <x-racing.card>
                    <x-slot name="header">
                        <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                            <svg class="w-5 h-5 text-racing-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            Informations personnelles
                        </h3>
                    </x-slot>

                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        {{-- Prénom --}}
                        <x-racing.form.input
                            label="Prénom"
                            wire:model="first_name"
                            id="first_name"
                            required
                            :error="$errors->first('first_name')"
                        />

                        {{-- Nom --}}
                        <x-racing.form.input
                            label="Nom"
                            wire:model="last_name"
                            id="last_name"
                            required
                            :error="$errors->first('last_name')"
                        />

                        {{-- Date de naissance --}}
                        <x-racing.form.input
                            type="date"
                            label="Date de naissance"
                            wire:model="birth_date"
                            id="birth_date"
                            required
                            :error="$errors->first('birth_date')"
                        />

                        {{-- Numéro de licence --}}
                        <x-racing.form.input
                            label="Numéro de licence"
                            wire:model="license_number"
                            id="license_number"
                            :error="$errors->first('license_number')"
                        />

                        {{-- Téléphone --}}
                        <x-racing.form.input
                            type="tel"
                            label="Téléphone"
                            wire:model="phone"
                            id="phone"
                            :error="$errors->first('phone')"
                        />

                        {{-- N° Permis --}}
                        <x-racing.form.input
                            label="N° Permis"
                            wire:model="permit_number"
                            id="permit_number"
                            :error="$errors->first('permit_number')"
                        />

                        {{-- Permis délivré le --}}
                        <x-racing.form.input
                            type="date"
                            label="Permis délivré le"
                            wire:model="permit_date"
                            id="permit_date"
                            :error="$errors->first('permit_date')"
                        />

                        {{-- Certificat médical --}}
                        <x-racing.form.input
                            type="date"
                            label="Date certificat médical"
                            wire:model="medical_certificate_date"
                            id="medical_certificate_date"
                            :error="$errors->first('medical_certificate_date')"
                        />
                    </div>

                    {{-- Section Adresse --}}
                    <div class="mt-8 pt-6 border-t border-carbon-700">
                        <h4 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-checkered-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            Adresse
                        </h4>

                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-6">
                            <div class="sm:col-span-6">
                                <x-racing.form.input
                                    label="Adresse"
                                    wire:model="address"
                                    id="address"
                                    :error="$errors->first('address')"
                                />
                            </div>

                            <div class="sm:col-span-2">
                                <x-racing.form.input
                                    label="Code postal"
                                    wire:model="postal_code"
                                    id="postal_code"
                                    :error="$errors->first('postal_code')"
                                />
                            </div>

                            <div class="sm:col-span-4">
                                <x-racing.form.input
                                    label="Ville"
                                    wire:model="city"
                                    id="city"
                                    :error="$errors->first('city')"
                                />
                            </div>
                        </div>
                    </div>

                    {{-- Section Contact d'urgence --}}
                    <div class="mt-8 pt-6 border-t border-carbon-700">
                        <h4 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-status-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            Contact d'urgence
                        </h4>

                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <x-racing.form.input
                                label="Nom du contact"
                                wire:model="emergency_contact_name"
                                id="emergency_contact_name"
                                :error="$errors->first('emergency_contact_name')"
                            />

                            <x-racing.form.input
                                type="tel"
                                label="Téléphone du contact"
                                wire:model="emergency_contact_phone"
                                id="emergency_contact_phone"
                                :error="$errors->first('emergency_contact_phone')"
                            />
                        </div>
                    </div>

                    {{-- Section Notes --}}
                    <div class="mt-8 pt-6 border-t border-carbon-700">
                        <h4 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-status-info" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Notes
                        </h4>

                        <x-racing.form.textarea
                            label="Notes internes"
                            wire:model="notes"
                            id="notes"
                            rows="3"
                            placeholder="Notes visibles uniquement par le staff..."
                            :error="$errors->first('notes')"
                        />
                    </div>

                    <x-slot name="footer">
                        <div class="flex justify-end">
                            <x-racing.button type="submit">
                                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Enregistrer les modifications
                            </x-racing.button>
                        </div>
                    </x-slot>
                </x-racing.card>
            </form>
        </div>

        {{-- Sidebar Info --}}
        <div class="lg:col-span-1 space-y-6">
            {{-- Compte utilisateur --}}
            @if ($pilot->user)
                <x-racing.card>
                    <x-slot name="header">
                        <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                            <svg class="w-5 h-5 text-status-info" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Compte utilisateur
                        </h3>
                    </x-slot>

                    <div class="space-y-3">
                        <div class="flex items-center text-sm">
                            <svg class="h-5 w-5 text-carbon-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            <a href="mailto:{{ $pilot->user->email }}" class="text-racing-red-400 hover:text-racing-red-300 transition-colors">
                                {{ $pilot->user->email }}
                            </a>
                        </div>
                        <div class="flex items-center text-sm text-carbon-400">
                            <svg class="h-5 w-5 text-carbon-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            Inscrit le {{ $pilot->user->created_at->format('d/m/Y') }}
                        </div>
                    </div>
                </x-racing.card>
            @endif

            {{-- Véhicules --}}
            <x-racing.card>
                <x-slot name="header">
                    <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-checkered-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        Véhicules
                    </h3>
                </x-slot>

                @if ($this->pilotCars->count() > 0)
                    <ul class="space-y-3">
                        @foreach ($this->pilotCars as $car)
                            <li class="flex items-center justify-between p-3 bg-carbon-800/50 rounded-lg border border-carbon-700/50">
                                <div>
                                    <p class="text-sm font-medium text-white">
                                        {{ $car->brand ?? 'Marque inconnue' }} {{ $car->model ?? '' }}
                                    </p>
                                    @if ($car->category)
                                        <p class="text-xs text-carbon-400">{{ $car->category->name }}</p>
                                    @endif
                                </div>
                                @if ($car->race_number)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-racing-red-500/20 text-racing-red-400 text-sm font-bold border border-racing-red-500/30">
                                        #{{ $car->race_number }}
                                    </span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @else
                    <x-racing.empty-state
                        icon='<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>'
                        title="Aucun véhicule"
                        description="Ce pilote n'a pas encore de véhicule enregistré."
                    />
                @endif
            </x-racing.card>

            {{-- Historique inscriptions --}}
            <x-racing.card>
                <x-slot name="header">
                    <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-status-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                        </svg>
                        Historique des inscriptions
                    </h3>
                </x-slot>

                @if ($registrations->count() > 0)
                    <ul class="space-y-3">
                        @foreach ($registrations as $registration)
                            <li class="border-l-4 {{ $registration->is_paid ? 'border-status-success' : 'border-status-warning' }} pl-3 py-2 bg-carbon-800/30 rounded-r-lg">
                                <p class="text-sm font-medium text-white">
                                    {{ $registration->race?->name ?? 'Course inconnue' }}
                                </p>
                                <p class="text-xs text-carbon-400 mt-0.5">
                                    {{ $registration->race?->date?->format('d/m/Y') ?? '-' }}
                                    @if ($registration->race?->season)
                                        - {{ $registration->race->season->name }}
                                    @endif
                                </p>
                                <div class="flex items-center space-x-2 mt-2">
                                    @if ($registration->is_paid)
                                        <x-racing.badge-status status="accepted" size="sm">Payé</x-racing.badge-status>
                                    @else
                                        <x-racing.badge-status status="pending" size="sm">Non payé</x-racing.badge-status>
                                    @endif
                                    @if ($registration->passages && $registration->passages->count() > 0)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-status-info/20 text-status-info border border-status-info/30">
                                            {{ $registration->passages->count() }} étape(s)
                                        </span>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <x-racing.empty-state
                        icon='<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>'
                        title="Aucune inscription"
                        description="Ce pilote n'a pas encore d'inscription."
                    />
                @endif
            </x-racing.card>
        </div>
    </div>
</div>
