<div>
    {{-- Header avec gradient --}}
    <div class="relative mb-8 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-8 bg-racing-gradient-subtle overflow-hidden">
        <div class="absolute top-0 right-0 w-64 h-64 bg-racing-red-500/5 rounded-full -translate-y-1/2 translate-x-1/2"></div>

        <div class="relative flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-carbon-900 dark:text-white flex items-center gap-3">
                    <span>🚗</span> Mes Voitures
                </h1>
                <p class="mt-2 text-carbon-600 dark:text-carbon-400">
                    Gérez vos véhicules de course
                </p>
            </div>
            <x-racing.button href="{{ route('pilot.cars.create') }}" class="self-start sm:self-auto">
                + Ajouter une voiture
            </x-racing.button>
        </div>
    </div>

    {{-- Filtres --}}
    <x-racing.card class="mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-racing.form.input
                wire:model.live="search"
                label="Rechercher"
                placeholder="Marque ou modèle..."
                icon="search"
            />
            <x-racing.form.select
                wire:model.live="categoryFilter"
                label="Catégorie"
            >
                <option value="">Toutes les catégories</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </x-racing.form.select>
        </div>
    </x-racing.card>

    @if($cars->isEmpty())
        {{-- État vide --}}
        <x-racing.card>
            <x-racing.empty-state
                icon="🚗"
                title="Aucune voiture enregistrée"
                description="Ajoutez votre première voiture pour pouvoir vous inscrire aux courses."
                actionLabel="Ajouter une voiture"
                actionHref="{{ route('pilot.cars.create') }}"
            />
        </x-racing.card>
    @else
        {{-- Liste des voitures en grille --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($cars as $car)
                <x-racing.card class="relative overflow-hidden hover:shadow-lg transition-shadow group">
                    {{-- Badge numéro de course --}}
                    <div class="absolute top-4 right-4 z-10">
                        <div class="w-14 h-14 rounded-xl bg-racing-gradient flex items-center justify-center shadow-lg transform group-hover:scale-105 transition-transform">
                            <span class="text-xl font-bold text-white">#{{ $car->race_number->toInt() }}</span>
                        </div>
                    </div>

                    {{-- Contenu --}}
                    <div class="pr-20">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="text-2xl">🏎️</span>
                            <h3 class="text-xl font-bold text-carbon-900 dark:text-white truncate">
                                {{ $car->make }} {{ $car->model }}
                            </h3>
                        </div>

                        <div class="mb-4">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-sm font-medium bg-carbon-100 dark:bg-carbon-800 text-carbon-700 dark:text-carbon-300">
                                📂 {{ $car->category->name }}
                            </span>
                        </div>

                        @if($car->notes)
                            <p class="text-sm text-carbon-500 dark:text-carbon-400 mb-4 line-clamp-2">
                                {{ $car->notes }}
                            </p>
                        @endif
                    </div>

                    {{-- Actions --}}
                    <div class="flex gap-2 mt-4 pt-4 border-t border-carbon-200 dark:border-carbon-700">
                        <x-racing.button href="{{ route('pilot.cars.edit', $car) }}" variant="outline" size="sm" class="flex-1">
                            ✏️ Modifier
                        </x-racing.button>
                        <x-racing.button
                            wire:click="deleteCar({{ $car->id }})"
                            wire:confirm="Êtes-vous sûr de vouloir supprimer cette voiture ?"
                            variant="danger"
                            size="sm"
                            class="flex-1"
                        >
                            🗑️ Supprimer
                        </x-racing.button>
                    </div>
                </x-racing.card>
            @endforeach
        </div>
    @endif
</div>
