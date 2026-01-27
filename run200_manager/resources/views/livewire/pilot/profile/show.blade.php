<div>
    {{-- Header avec gradient --}}
    <div class="relative mb-8 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-8 bg-racing-gradient-subtle overflow-hidden">
        <div class="absolute top-0 right-0 w-64 h-64 bg-racing-red-500/5 rounded-full -translate-y-1/2 translate-x-1/2"></div>

        <div class="relative flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-carbon-900 dark:text-white flex items-center gap-3">
                    <span>👤</span> Mon Profil
                </h1>
                <p class="mt-2 text-carbon-600 dark:text-carbon-400">
                    Vos informations personnelles de pilote
                </p>
            </div>
            <x-racing.button href="{{ route('pilot.profile.edit') }}" class="self-start sm:self-auto">
                ✏️ Modifier le profil
            </x-racing.button>
        </div>
    </div>

    <x-racing.card>
        <div class="flex flex-col md:flex-row md:items-start gap-8">
            {{-- Photo et licence --}}
            <div class="flex flex-col items-center md:items-start">
                {{-- Photo --}}
                <div class="relative">
                    @if ($pilot->photo_path)
                        <img src="{{ Storage::url($pilot->photo_path) }}" alt="Photo {{ $pilot->first_name }}"
                             class="w-32 h-32 rounded-2xl object-cover border-4 border-white dark:border-carbon-700 shadow-lg">
                    @else
                        <div class="w-32 h-32 rounded-2xl bg-racing-gradient flex items-center justify-center border-4 border-white dark:border-carbon-700 shadow-lg">
                            <span class="text-4xl font-bold text-white">{{ substr($pilot->first_name, 0, 1) }}{{ substr($pilot->last_name, 0, 1) }}</span>
                        </div>
                    @endif

                    {{-- Badge saison active --}}
                    @if ($pilot->is_active_season)
                        <div class="absolute -bottom-2 -right-2 px-3 py-1 rounded-full bg-status-success text-white text-xs font-bold shadow-lg">
                            ✓ Actif
                        </div>
                    @endif
                </div>

                {{-- Licence --}}
                <div class="mt-6 text-center md:text-left">
                    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-racing-red-100 dark:bg-racing-red-900/30">
                        <span class="text-lg">🏎️</span>
                        <span class="font-bold text-racing-red-800 dark:text-racing-red-300">
                            {{ $pilot->license->toString() }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Informations principales --}}
            <div class="flex-1">
                <h2 class="text-2xl font-bold text-carbon-900 dark:text-white mb-6">
                    {{ $pilot->first_name }} {{ $pilot->last_name }}
                </h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div class="space-y-1">
                        <dt class="text-sm font-medium text-carbon-500 dark:text-carbon-400">📅 Date de naissance</dt>
                        <dd class="text-carbon-900 dark:text-white font-medium">{{ $pilot->birth_date->format('d/m/Y') }}</dd>
                    </div>
                    <div class="space-y-1">
                        <dt class="text-sm font-medium text-carbon-500 dark:text-carbon-400">📍 Lieu de naissance</dt>
                        <dd class="text-carbon-900 dark:text-white font-medium">{{ $pilot->birth_place }}</dd>
                    </div>
                    <div class="space-y-1">
                        <dt class="text-sm font-medium text-carbon-500 dark:text-carbon-400">📱 Téléphone</dt>
                        <dd class="text-carbon-900 dark:text-white font-medium">{{ $pilot->phone }}</dd>
                    </div>
                    <div class="space-y-1">
                        <dt class="text-sm font-medium text-carbon-500 dark:text-carbon-400">🏠 Adresse</dt>
                        <dd class="text-carbon-900 dark:text-white font-medium">{{ $pilot->address }}</dd>
                    </div>
                    <div class="space-y-1">
                        <dt class="text-sm font-medium text-carbon-500 dark:text-carbon-400">🪪 N° Permis</dt>
                        <dd class="text-carbon-900 dark:text-white font-medium">{{ $pilot->permit_number ?? '-' }}</dd>
                    </div>
                    <div class="space-y-1">
                        <dt class="text-sm font-medium text-carbon-500 dark:text-carbon-400">📆 Permis délivré le</dt>
                        <dd class="text-carbon-900 dark:text-white font-medium">{{ $pilot->permit_date?->format('d/m/Y') ?? '-' }}</dd>
                    </div>
                </div>
            </div>
        </div>

        {{-- Informations tuteur --}}
        @if ($pilot->is_minor && ($pilot->guardian_name || $pilot->guardian_phone))
            <div class="mt-8 pt-8 border-t border-carbon-200 dark:border-carbon-700">
                <h3 class="text-lg font-semibold text-carbon-900 dark:text-white mb-6 flex items-center gap-2">
                    <span>👨‍👧</span> Informations Tuteur
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                    @if ($pilot->guardian_name)
                        <div class="space-y-1">
                            <dt class="text-sm font-medium text-carbon-500 dark:text-carbon-400">Nom tuteur</dt>
                            <dd class="text-carbon-900 dark:text-white font-medium">{{ $pilot->guardian_name }}</dd>
                        </div>
                    @endif
                    @if ($pilot->guardian_phone)
                        <div class="space-y-1">
                            <dt class="text-sm font-medium text-carbon-500 dark:text-carbon-400">Téléphone tuteur</dt>
                            <dd class="text-carbon-900 dark:text-white font-medium">{{ $pilot->guardian_phone }}</dd>
                        </div>
                    @endif
                    @if ($pilot->guardian_license_number)
                        <div class="space-y-1">
                            <dt class="text-sm font-medium text-carbon-500 dark:text-carbon-400">Licence tuteur</dt>
                            <dd class="text-carbon-900 dark:text-white font-medium">{{ $pilot->guardian_license_number }}</dd>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </x-racing.card>
</div>
