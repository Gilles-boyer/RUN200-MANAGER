<div>
    {{-- Header avec gradient --}}
    <div class="relative mb-8 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-8 bg-racing-gradient-subtle overflow-hidden">
        <div class="absolute top-0 right-0 w-64 h-64 bg-racing-red-500/5 rounded-full -translate-y-1/2 translate-x-1/2"></div>

        <div class="relative">
            <h1 class="text-3xl font-bold text-carbon-900 dark:text-white flex items-center gap-3">
                <span>{{ $pilot ? '✏️' : '🆕' }}</span>
                {{ $pilot ? 'Modifier mon profil' : 'Créer mon profil pilote' }}
            </h1>

            @if($pilot)
                <div class="mt-4">
                    <div class="flex flex-wrap items-center gap-4">
                        <span class="text-sm text-carbon-600 dark:text-carbon-400">
                            Complétion du profil : {{ $this->profileCompletion['percentage'] }}%
                        </span>
                        <div class="flex-1 max-w-xs">
                            <x-racing.progress-bar
                                :value="$this->profileCompletion['percentage']"
                                :max="100"
                                :variant="$this->profileCompletion['percentage'] == 100 ? 'success' : 'primary'"
                            />
                        </div>
                        @if($this->profileCompletion['percentage'] == 100)
                            <span class="text-status-success text-sm font-medium">✓ Profil complet</span>
                        @endif
                    </div>
                    @if(count($this->profileCompletion['missing']) > 0)
                        <p class="text-sm text-status-warning mt-2">
                            ⚠️ Champs manquants : {{ implode(', ', array_map(fn($f) => __("validation.attributes.$f") ?: $f, $this->profileCompletion['missing'])) }}
                        </p>
                    @endif
                </div>
            @else
                <p class="mt-2 text-carbon-600 dark:text-carbon-400">
                    Complétez tous les champs obligatoires (*) et ajoutez une photo de profil pour pouvoir vous inscrire aux courses.
                </p>
            @endif
        </div>
    </div>

    <form wire:submit="save" class="space-y-6">
        {{-- Informations Personnelles --}}
        <x-racing.card>
            <h2 class="text-lg font-semibold text-carbon-900 dark:text-white mb-6 flex items-center gap-2">
                <span>👤</span> Informations Personnelles
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <x-racing.form.input
                    wire:model="first_name"
                    label="Prénom"
                    required
                    :error="$errors->first('first_name')"
                />

                <x-racing.form.input
                    wire:model="last_name"
                    label="Nom"
                    required
                    :error="$errors->first('last_name')"
                />

                <x-racing.form.input
                    wire:model="license_number"
                    label="N° Licence FFSA"
                    required
                    hint="1 à 6 chiffres"
                    maxlength="6"
                    pattern="[0-9]*"
                    :error="$errors->first('license_number')"
                />

                <x-racing.form.input
                    type="date"
                    wire:model="birth_date"
                    label="Date de naissance"
                    required
                    :error="$errors->first('birth_date')"
                />

                <x-racing.form.input
                    wire:model="birth_place"
                    label="Lieu de naissance"
                    required
                    :error="$errors->first('birth_place')"
                />

                <x-racing.form.input
                    type="tel"
                    wire:model="phone"
                    label="Téléphone"
                    placeholder="+33 6 12 34 56 78"
                    required
                    :error="$errors->first('phone')"
                />

                <x-racing.form.input
                    wire:model="permit_number"
                    label="N° Permis de conduire"
                    placeholder="123456789012"
                    :error="$errors->first('permit_number')"
                />

                <x-racing.form.input
                    type="date"
                    wire:model="permit_date"
                    label="Permis délivré le"
                    :error="$errors->first('permit_date')"
                />
            </div>
        </x-racing.card>

        {{-- Adresse --}}
        <x-racing.card>
            <h2 class="text-lg font-semibold text-carbon-900 dark:text-white mb-6 flex items-center gap-2">
                <span>🏠</span> Adresse
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <x-racing.form.textarea
                        wire:model="address"
                        label="Adresse"
                        rows="2"
                        placeholder="Numéro, rue..."
                        required
                        :error="$errors->first('address')"
                    />
                </div>

                <x-racing.form.input
                    wire:model="postal_code"
                    label="Code postal"
                    placeholder="75001"
                    required
                    :error="$errors->first('postal_code')"
                />

                <x-racing.form.input
                    wire:model="city"
                    label="Ville"
                    placeholder="Paris"
                    required
                    :error="$errors->first('city')"
                />
            </div>
        </x-racing.card>

        {{-- Photo de profil --}}
        <x-racing.card>
            <h2 class="text-lg font-semibold text-carbon-900 dark:text-white mb-2 flex items-center gap-2">
                <span>📸</span> Photo de profil <span class="text-racing-red-500">*</span>
            </h2>
            <p class="text-sm text-carbon-500 dark:text-carbon-400 mb-6">
                Une photo de profil est obligatoire pour vous inscrire aux courses.
            </p>

            <div class="flex flex-col sm:flex-row items-start gap-6">
                @if($pilot && $pilot->photo_path)
                    <div class="flex-shrink-0 text-center">
                        <img src="{{ Storage::url($pilot->photo_path) }}" alt="Photo actuelle"
                             class="w-28 h-28 rounded-2xl object-cover border-4 border-carbon-200 dark:border-carbon-700 shadow-lg">
                        <p class="text-xs text-carbon-500 dark:text-carbon-400 mt-2">Photo actuelle</p>
                    </div>
                @endif

                <div class="flex-1">
                    <x-racing.form.file-upload
                        wire:model="photo"
                        label="{{ $pilot && $pilot->photo_path ? 'Changer la photo' : 'Ajouter une photo' }}"
                        accept="image/jpeg,image/png,image/webp"
                        hint="JPG, PNG ou WebP, max 2MB"
                        :error="$errors->first('photo')"
                    />

                    @if($photo)
                        <div class="mt-4">
                            <p class="text-sm text-carbon-600 dark:text-carbon-400 mb-2">Aperçu :</p>
                            <img src="{{ $photo->temporaryUrl() }}" alt="Aperçu"
                                 class="w-28 h-28 rounded-2xl object-cover border-4 border-racing-red-500 shadow-lg">
                        </div>
                    @endif
                </div>
            </div>
        </x-racing.card>

        {{-- Contact d'urgence --}}
        <x-racing.card>
            <h2 class="text-lg font-semibold text-carbon-900 dark:text-white mb-2 flex items-center gap-2">
                <span>🚨</span> Contact d'urgence
            </h2>
            <p class="text-sm text-carbon-500 dark:text-carbon-400 mb-6">
                Personne à contacter en cas d'urgence sur le circuit.
            </p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <x-racing.form.input
                    wire:model="emergency_contact_name"
                    label="Nom du contact"
                    placeholder="Nom complet"
                    required
                    :error="$errors->first('emergency_contact_name')"
                />

                <x-racing.form.input
                    type="tel"
                    wire:model="emergency_contact_phone"
                    label="Téléphone du contact"
                    placeholder="+33 6 12 34 56 78"
                    required
                    :error="$errors->first('emergency_contact_phone')"
                />
            </div>
        </x-racing.card>

        {{-- Informations Tuteur (si mineur) --}}
        <x-racing.card>
            <div class="mb-6">
                <x-racing.form.checkbox
                    wire:model.live="is_minor"
                    label="Je suis mineur (moins de 18 ans)"
                />
            </div>

            @if ($is_minor)
                <h2 class="text-lg font-semibold text-carbon-900 dark:text-white mb-2 flex items-center gap-2">
                    <span>👨‍👧</span> Informations du représentant légal
                </h2>
                <p class="text-sm text-carbon-500 dark:text-carbon-400 mb-6">
                    Ces informations sont obligatoires pour les pilotes mineurs.
                </p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <x-racing.form.input
                        wire:model="guardian_first_name"
                        label="Prénom du représentant"
                        required
                        :error="$errors->first('guardian_first_name')"
                    />

                    <x-racing.form.input
                        wire:model="guardian_last_name"
                        label="Nom du représentant"
                        required
                        :error="$errors->first('guardian_last_name')"
                    />

                    <x-racing.form.input
                        wire:model="guardian_license_number"
                        label="N° Licence FFSA du représentant"
                        hint="Optionnel - Si le représentant possède une licence"
                        maxlength="6"
                        :error="$errors->first('guardian_license_number')"
                    />
                </div>
            @endif
        </x-racing.card>

        {{-- Actions --}}
        <div class="flex flex-col sm:flex-row justify-end gap-3">
            <x-racing.button
                type="button"
                variant="outline"
                href="{{ $pilot ? route('pilot.profile.show') : route('pilot.dashboard') }}"
            >
                Annuler
            </x-racing.button>
            <x-racing.button type="submit">
                {{ $pilot ? '💾 Mettre à jour' : '🚀 Créer le profil' }}
            </x-racing.button>
        </div>
    </form>
</div>
