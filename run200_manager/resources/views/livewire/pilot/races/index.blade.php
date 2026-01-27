<div>
    {{-- Header avec gradient --}}
    <div class="relative mb-8 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-8 bg-racing-gradient-subtle overflow-hidden">
        <div class="absolute top-0 right-0 w-64 h-64 bg-racing-red-500/5 rounded-full -translate-y-1/2 translate-x-1/2"></div>

        <div class="relative">
            <h1 class="text-3xl font-bold text-carbon-900 dark:text-white flex items-center gap-3">
                <span>🏁</span> Courses disponibles
            </h1>
            <p class="mt-2 text-carbon-600 dark:text-carbon-400">
                Découvrez les courses ouvertes et inscrivez-vous
            </p>
        </div>
    </div>

    {{-- Alerte profil manquant --}}
    @if (!$pilot)
        <x-racing.alert type="warning" class="mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <p class="font-semibold">Profil pilote requis</p>
                    <p class="text-sm mt-1 opacity-90">Vous devez créer votre profil pilote pour pouvoir vous inscrire aux courses.</p>
                </div>
                <x-racing.button href="{{ route('pilot.profile.edit') }}" variant="secondary" size="sm">
                    Créer mon profil
                </x-racing.button>
            </div>
        </x-racing.alert>
    @endif

    {{-- Filtres --}}
    <x-racing.card class="mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <x-racing.form.input
                wire:model.live.debounce.300ms="search"
                label="Rechercher"
                placeholder="Nom, lieu..."
                icon="search"
            />

            <x-racing.form.select
                wire:model.live="statusFilter"
                label="Statut"
            >
                <option value="">Tous les statuts</option>
                <option value="OPEN">🟢 Ouvertes aux inscriptions</option>
                <option value="CLOSED">🔴 Fermées</option>
                <option value="COMPLETED">🏆 Terminées</option>
            </x-racing.form.select>
        </div>
    </x-racing.card>

    {{-- Liste des courses --}}
    @if($races->isEmpty())
        <x-racing.card>
            <x-racing.empty-state
                icon="🏁"
                title="Aucune course disponible"
                description="Il n'y a pas de course correspondant à vos critères de recherche."
            />
        </x-racing.card>
    @else
        <div class="space-y-4">
            @foreach($races as $race)
                <x-racing.card-race
                    :name="$race->name"
                    :date="$race->race_date"
                    :location="$race->location"
                    :status="$race->status"
                    :price="$race->formatted_entry_fee"
                    :registrations="$race->registrations->count()"
                    :season="$race->season?->name"
                    :isRegistered="in_array($race->id, $registeredRaceIds)"
                    :canRegister="$race->isOpen() && $pilot"
                    :registerUrl="route('pilot.registrations.create', $race)"
                />
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="mt-6">
            {{ $races->links() }}
        </div>
    @endif
</div>
