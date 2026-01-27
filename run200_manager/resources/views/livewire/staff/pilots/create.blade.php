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
                    <span class="text-carbon-300">Nouveau pilote</span>
                </nav>
                <h1 class="text-2xl font-bold text-white flex items-center gap-3">
                    <div class="p-2 bg-racing-red-500/20 rounded-lg">
                        <svg class="w-6 h-6 text-racing-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                        </svg>
                    </div>
                    Créer un nouveau pilote
                </h1>
                <p class="mt-1 text-carbon-400">Remplissez les informations du pilote</p>
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
    @if (session()->has('error'))
        <x-racing.alert type="danger">
            {{ session('error') }}
        </x-racing.alert>
    @endif

    {{-- Form --}}
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

                {{-- Lieu de naissance --}}
                <x-racing.form.input
                    label="Lieu de naissance"
                    wire:model="birth_place"
                    id="birth_place"
                    required
                    :error="$errors->first('birth_place')"
                />

                {{-- Numéro de licence --}}
                <div>
                    <label for="license_number" class="block text-sm font-medium text-carbon-300 mb-1">
                        Numéro de licence <span class="text-racing-red-500">*</span>
                    </label>
                    <div class="flex">
                        <input
                            type="text"
                            wire:model="license_number"
                            id="license_number"
                            maxlength="6"
                            class="flex-1 bg-carbon-800 border border-carbon-600 text-white rounded-l-lg px-4 py-2.5 focus:ring-2 focus:ring-racing-red-500 focus:border-racing-red-500 transition-colors @error('license_number') border-status-danger @enderror"
                        >
                        <button
                            type="button"
                            wire:click="generateLicenseNumber"
                            class="px-4 py-2.5 bg-carbon-700 border border-l-0 border-carbon-600 text-carbon-300 rounded-r-lg hover:bg-carbon-600 hover:text-white transition-colors"
                        >
                            Générer
                        </button>
                    </div>
                    @error('license_number')
                        <p class="mt-1 text-sm text-status-danger">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Téléphone --}}
                <x-racing.form.input
                    type="tel"
                    label="Téléphone"
                    wire:model="phone"
                    id="phone"
                    required
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
                            required
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
                    />

                    <x-racing.form.input
                        type="tel"
                        label="Téléphone du contact"
                        wire:model="emergency_contact_phone"
                        id="emergency_contact_phone"
                    />
                </div>
            </div>

            {{-- Section Informations complémentaires --}}
            <div class="mt-8 pt-6 border-t border-carbon-700">
                <h4 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-status-info" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Informations complémentaires
                </h4>

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <x-racing.form.input
                        type="date"
                        label="Date certificat médical"
                        wire:model="medical_certificate_date"
                        id="medical_certificate_date"
                    />

                    <div class="sm:col-span-2">
                        <x-racing.form.textarea
                            label="Notes internes"
                            wire:model="notes"
                            id="notes"
                            rows="3"
                            placeholder="Notes visibles uniquement par le staff..."
                        />
                    </div>
                </div>
            </div>

            {{-- Section Compte utilisateur --}}
            <div class="mt-8 pt-6 border-t border-carbon-700">
                <div class="flex items-start">
                    <div class="flex items-center h-5">
                        <input
                            type="checkbox"
                            wire:model.live="createUserAccount"
                            id="createUserAccount"
                            class="h-5 w-5 rounded bg-carbon-800 border-carbon-600 text-racing-red-500 focus:ring-racing-red-500 focus:ring-offset-carbon-900"
                        >
                    </div>
                    <div class="ml-3">
                        <label for="createUserAccount" class="text-sm font-medium text-white">Créer un compte utilisateur</label>
                        <p class="text-sm text-carbon-400">Permet au pilote de se connecter et gérer ses inscriptions en ligne.</p>
                    </div>
                </div>

                @if($createUserAccount)
                    <div class="mt-4 ml-8 max-w-md">
                        <x-racing.form.input
                            type="email"
                            label="Email"
                            wire:model="email"
                            id="email"
                            required
                            placeholder="email@exemple.com"
                            :error="$errors->first('email')"
                        />
                        <p class="mt-1 text-xs text-carbon-500">Un mot de passe temporaire sera généré automatiquement.</p>
                    </div>
                @endif
            </div>

            <x-slot name="footer">
                <div class="flex justify-end">
                    <x-racing.button type="submit">
                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        Créer le pilote
                    </x-racing.button>
                </div>
            </x-slot>
        </x-racing.card>
    </form>
</div>
