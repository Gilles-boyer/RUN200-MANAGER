<div>
    {{-- Header avec gradient --}}
    <div class="relative mb-8 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-8 bg-racing-gradient-subtle overflow-hidden">
        <div class="absolute top-0 right-0 w-64 h-64 bg-racing-red-500/5 rounded-full -translate-y-1/2 translate-x-1/2"></div>

        <div class="relative">
            <h1 class="text-3xl font-bold text-carbon-900 dark:text-white flex items-center gap-3">
                <span>{{ $car ? '✏️' : '🚗' }}</span>
                {{ $car ? 'Modifier la voiture' : 'Ajouter une voiture' }}
            </h1>
            <p class="mt-2 text-carbon-600 dark:text-carbon-400">
                {{ $car ? 'Modifiez les informations de votre véhicule' : 'Enregistrez un nouveau véhicule pour participer aux courses' }}
            </p>
        </div>
    </div>

    <form wire:submit="save">
        <x-racing.card class="mb-6">
            <h2 class="text-lg font-semibold text-carbon-900 dark:text-white mb-6 flex items-center gap-2">
                <span>🏎️</span> Informations Véhicule
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Numéro de course avec bouton générateur --}}
                <div>
                    <label class="block text-sm font-medium text-carbon-700 dark:text-carbon-300 mb-1.5">
                        N° de course <span class="text-racing-red-500">*</span>
                    </label>
                    <div class="flex gap-2">
                        <div class="flex-1 relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-carbon-400 text-lg">#</span>
                            </div>
                            <input
                                type="number"
                                wire:model="race_number"
                                min="0"
                                max="999"
                                class="input-racing pl-8"
                                placeholder="123"
                            >
                        </div>
                        <button
                            type="button"
                            wire:click="generateRandomNumber"
                            class="px-4 py-2 bg-carbon-100 hover:bg-carbon-200 dark:bg-carbon-700 dark:hover:bg-carbon-600 border border-carbon-300 dark:border-carbon-600 rounded-xl text-carbon-700 dark:text-carbon-200 transition-colors flex items-center gap-2"
                            title="Générer un numéro aléatoire disponible"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            <span class="hidden sm:inline">Générer</span>
                        </button>
                    </div>
                    <p class="mt-1.5 text-xs text-carbon-500 dark:text-carbon-400">Numéro attribué à vie, unique (0-999)</p>
                    @error('race_number') <span class="text-sm text-status-danger mt-1">{{ $message }}</span> @enderror
                </div>

                <x-racing.form.select
                    wire:model="car_category_id"
                    label="Catégorie"
                    required
                    :error="$errors->first('car_category_id')"
                >
                    <option value="">Sélectionner une catégorie</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </x-racing.form.select>

                <x-racing.form.input
                    wire:model="make"
                    label="Marque"
                    placeholder="ex: Renault, Peugeot..."
                    required
                    :error="$errors->first('make')"
                />

                <x-racing.form.input
                    wire:model="model"
                    label="Modèle"
                    placeholder="ex: Clio RS, 208 GTi..."
                    required
                    :error="$errors->first('model')"
                />

                <div class="md:col-span-2">
                    <x-racing.form.textarea
                        wire:model="notes"
                        label="Notes"
                        rows="3"
                        placeholder="Informations supplémentaires (modifications, spécificités...)"
                        :error="$errors->first('notes')"
                    />
                </div>
            </div>
        </x-racing.card>

        {{-- Actions --}}
        <div class="flex flex-col sm:flex-row justify-between gap-4">
            <div>
                @if($car)
                    <x-racing.button
                        type="button"
                        wire:click="delete"
                        wire:confirm="Êtes-vous sûr de vouloir supprimer cette voiture ?"
                        variant="danger"
                    >
                        🗑️ Supprimer
                    </x-racing.button>
                @endif
            </div>
            <div class="flex flex-col sm:flex-row gap-3">
                <x-racing.button type="button" variant="outline" href="{{ route('pilot.cars.index') }}">
                    Annuler
                </x-racing.button>
                <x-racing.button type="submit">
                    {{ $car ? '💾 Mettre à jour' : '🚗 Créer la voiture' }}
                </x-racing.button>
            </div>
        </div>
    </form>
</div>
