<div class="space-y-6">
    {{-- Racing Header --}}
    <div class="relative overflow-hidden rounded-xl bg-racing-gradient-subtle border border-carbon-700/50 p-6">
        <div class="absolute top-0 right-0 w-32 h-32 opacity-5">
            <svg viewBox="0 0 100 100" fill="currentColor" class="text-racing-red-500">
                <path d="M50 5L90 25v50L50 95 10 75V25L50 5z"/>
            </svg>
        </div>
        <div class="relative">
            <nav class="flex items-center space-x-2 text-sm mb-2">
                <a href="{{ route('admin.dashboard') }}" class="text-carbon-400 hover:text-white transition-colors" wire:navigate>
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                    </svg>
                </a>
                <svg class="w-4 h-4 text-carbon-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
                <a href="{{ route('admin.races.index') }}" class="text-carbon-400 hover:text-white transition-colors" wire:navigate>
                    Courses
                </a>
                <svg class="w-4 h-4 text-carbon-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
                <span class="text-carbon-300">{{ $isEdit ? 'Modifier' : 'Nouvelle course' }}</span>
            </nav>
            <h1 class="text-2xl font-bold text-white flex items-center gap-3">
                <div class="p-2 bg-racing-red-500/20 rounded-lg">
                    <svg class="w-6 h-6 text-racing-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/>
                    </svg>
                </div>
                {{ $isEdit ? 'Modifier la course' : 'Nouvelle course' }}
            </h1>
        </div>
    </div>

    {{-- Formulaire --}}
    <form wire:submit="save">
        <x-racing.card class="max-w-2xl">
            <x-slot name="header">
                <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-racing-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Informations de la course
                </h3>
            </x-slot>

            <div class="space-y-6">
                {{-- Saison --}}
                <x-racing.form.select
                    label="Saison"
                    wire:model="season_id"
                    id="season_id"
                    required
                    :error="$errors->first('season_id')"
                >
                    <option value="">Sélectionner une saison</option>
                    @foreach($seasons as $season)
                        <option value="{{ $season->id }}">{{ $season->name }}</option>
                    @endforeach
                </x-racing.form.select>

                {{-- Nom --}}
                <x-racing.form.input
                    label="Nom de la course"
                    wire:model="name"
                    id="name"
                    required
                    placeholder="Ex: Course de Printemps"
                    :error="$errors->first('name')"
                />

                {{-- Date --}}
                <x-racing.form.input
                    type="date"
                    label="Date de la course"
                    wire:model="race_date"
                    id="race_date"
                    required
                    :error="$errors->first('race_date')"
                />

                {{-- Lieu --}}
                <x-racing.form.input
                    label="Lieu"
                    wire:model="location"
                    id="location"
                    required
                    placeholder="Ex: Circuit de la Réunion"
                    :error="$errors->first('location')"
                />

                {{-- Prix d'inscription --}}
                <div>
                    <label for="entry_fee" class="block text-sm font-medium text-carbon-300 mb-1">
                        Prix d'inscription (€)
                    </label>
                    <div class="relative">
                        <input
                            type="number"
                            id="entry_fee"
                            wire:model="entry_fee"
                            step="0.01"
                            min="0"
                            max="9999.99"
                            class="w-full bg-carbon-800 border border-carbon-600 text-white rounded-lg px-4 py-2.5 pr-12 focus:ring-2 focus:ring-racing-red-500 focus:border-racing-red-500 transition-colors @error('entry_fee') border-status-danger @enderror"
                            placeholder="{{ number_format(config('stripe.registration_fee_cents', 5000) / 100, 2, '.', '') }}"
                        >
                        <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                            <span class="text-carbon-400">€</span>
                        </div>
                    </div>
                    <p class="mt-1 text-sm text-carbon-500">
                        Laissez vide pour utiliser le prix par défaut ({{ number_format(config('stripe.registration_fee_cents', 5000) / 100, 2, ',', ' ') }} €)
                    </p>
                    @error('entry_fee')
                        <p class="mt-1 text-sm text-status-danger">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Statut --}}
                <x-racing.form.select
                    label="Statut"
                    wire:model="status"
                    id="status"
                    required
                    :error="$errors->first('status')"
                >
                    <option value="DRAFT">🔸 Brouillon</option>
                    <option value="OPEN">🟢 Ouvert aux inscriptions</option>
                    <option value="CLOSED">🔴 Fermé</option>
                    <option value="COMPLETED">✅ Terminé</option>
                    <option value="CANCELLED">❌ Annulé</option>
                </x-racing.form.select>
            </div>

            <x-slot name="footer">
                <div class="flex items-center justify-end space-x-3">
                    <x-racing.button href="{{ route('admin.races.index') }}" variant="secondary" wire:navigate>
                        Annuler
                    </x-racing.button>
                    <x-racing.button type="submit">
                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        {{ $isEdit ? 'Mettre à jour' : 'Créer' }}
                    </x-racing.button>
                </div>
            </x-slot>
        </x-racing.card>
    </form>
</div>
