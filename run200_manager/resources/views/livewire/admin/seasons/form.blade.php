<div class="space-y-6">
    {{-- Racing Header --}}
    <div class="relative overflow-hidden rounded-xl bg-racing-gradient-subtle border border-carbon-700/50 p-6">
        <div class="absolute top-0 right-0 w-32 h-32 opacity-5">
            <svg viewBox="0 0 100 100" fill="currentColor" class="text-checkered-yellow-500">
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
                <a href="{{ route('admin.seasons.index') }}" class="text-carbon-400 hover:text-white transition-colors" wire:navigate>
                    Saisons
                </a>
                <svg class="w-4 h-4 text-carbon-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
                <span class="text-carbon-300">{{ $isEdit ? 'Modifier' : 'Nouvelle saison' }}</span>
            </nav>
            <h1 class="text-2xl font-bold text-white flex items-center gap-3">
                <div class="p-2 bg-checkered-yellow-500/20 rounded-lg">
                    <svg class="w-6 h-6 text-checkered-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                {{ $isEdit ? 'Modifier la saison' : 'Nouvelle saison' }}
            </h1>
        </div>
    </div>

    {{-- Formulaire --}}
    <form wire:submit="save">
        <x-racing.card class="max-w-2xl">
            <x-slot name="header">
                <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-checkered-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Informations de la saison
                </h3>
            </x-slot>

            <div class="space-y-6">
                {{-- Nom --}}
                <x-racing.form.input
                    label="Nom de la saison"
                    wire:model="name"
                    id="name"
                    required
                    placeholder="Ex: Saison 2025"
                    :error="$errors->first('name')"
                />

                {{-- Dates --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-racing.form.input
                        type="date"
                        label="Date de début"
                        wire:model="start_date"
                        id="start_date"
                        required
                        :error="$errors->first('start_date')"
                    />

                    <x-racing.form.input
                        type="date"
                        label="Date de fin"
                        wire:model="end_date"
                        id="end_date"
                        required
                        :error="$errors->first('end_date')"
                    />
                </div>

                {{-- Saison active --}}
                <div class="p-4 bg-carbon-800/50 rounded-xl border border-carbon-700/50">
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input
                                type="checkbox"
                                id="is_active"
                                wire:model="is_active"
                                class="h-5 w-5 rounded bg-carbon-800 border-carbon-600 text-racing-red-500 focus:ring-racing-red-500 focus:ring-offset-carbon-900"
                            >
                        </div>
                        <div class="ml-3">
                            <label for="is_active" class="text-sm font-medium text-white">Saison active</label>
                            <p class="text-sm text-carbon-400 mt-0.5">
                                Seule une saison active sera visible pour les pilotes. Activer cette saison désactivera automatiquement les autres.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <x-slot name="footer">
                <div class="flex items-center justify-end space-x-3">
                    <x-racing.button href="{{ route('admin.seasons.index') }}" variant="secondary" wire:navigate>
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
