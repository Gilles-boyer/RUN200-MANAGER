<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    {{-- Racing Header --}}
    <div class="relative mb-8 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-8 bg-racing-gradient-subtle overflow-hidden">
        <div class="absolute top-0 right-0 w-64 h-64 bg-racing-red-500/5 rounded-full -translate-y-1/2 translate-x-1/2"></div>
        <div class="absolute bottom-0 left-0 w-48 h-48 bg-checkered-yellow-500/5 rounded-full translate-y-1/2 -translate-x-1/2"></div>

        <div class="relative flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-white flex items-center gap-3">
                    <span>📝</span> Feuilles d'Engagement
                </h1>
                <p class="mt-2 text-gray-400">
                    Signature électronique des feuilles d'engagement pendant le contrôle administratif
                </p>
            </div>

            {{-- Race Selector --}}
            <div class="w-full md:w-80">
                <select
                    wire:model.live="raceId"
                    class="w-full rounded-xl bg-carbon-800 border border-carbon-700 text-white px-4 py-3
                        focus:border-racing-red-500 focus:ring focus:ring-racing-red-500/20"
                >
                    <option value="">-- Sélectionner une course --</option>
                    @foreach($this->availableRaces as $r)
                        <option value="{{ $r->id }}">
                            {{ $r->name }} ({{ $r->race_date->format('d/m/Y') }})
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if (session('success'))
        <x-racing.alert type="success" :dismissible="true" class="mb-6">
            {{ session('success') }}
        </x-racing.alert>
    @endif

    {{-- Stats Cards --}}
    @if($this->race)
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            <x-racing.stat-card
                label="Inscrits acceptés"
                :value="$this->stats['total']"
                icon="👥"
            />
            <x-racing.stat-card
                label="Engagements signés"
                :value="$this->stats['signed']"
                icon="✅"
                class="border-status-success/30"
            />
            <x-racing.stat-card
                label="En attente"
                :value="$this->stats['pending']"
                icon="⏳"
                class="border-status-warning/30"
            />
            <x-racing.stat-card
                label="Taux de signature"
                :value="($this->stats['total'] > 0 ? round(($this->stats['signed'] / $this->stats['total']) * 100) : 0) . '%'"
                icon="📊"
            />
        </div>
    @endif

    @if($this->race)
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
            {{-- Pending Registrations --}}
            <div>
                <x-racing.card>
                    <x-slot name="header">
                        <div class="flex items-center justify-between">
                            <h2 class="text-lg font-semibold text-white flex items-center gap-2">
                                <span class="text-checkered-yellow-500">⏳</span>
                                En attente de signature
                            </h2>
                            <span class="px-3 py-1 bg-status-warning/20 text-status-warning text-sm rounded-full">
                                {{ $this->stats['pending'] }} pilotes
                            </span>
                        </div>
                        <div class="mt-4">
                            <input
                                type="text"
                                wire:model.live.debounce.300ms="searchQuery"
                                placeholder="Rechercher (nom, licence, n° course)..."
                                class="w-full rounded-xl bg-carbon-800 border border-carbon-700 text-white px-4 py-2.5 text-sm
                                    placeholder-carbon-500 focus:border-racing-red-500 focus:ring focus:ring-racing-red-500/20"
                            >
                        </div>
                    </x-slot>

                    <div class="divide-y divide-carbon-700/50 max-h-[600px] overflow-y-auto -mx-5 px-5">
                        @forelse($this->pendingRegistrations as $reg)
                            <div
                                wire:click="selectRegistration({{ $reg->id }})"
                                class="py-4 hover:bg-carbon-800/50 -mx-5 px-5 cursor-pointer transition-all group"
                            >
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-4">
                                        <div class="flex-shrink-0 w-14 h-14 bg-gradient-to-br from-racing-red-600 to-racing-red-700 rounded-xl flex items-center justify-center shadow-lg shadow-racing-red-500/20 group-hover:scale-105 transition-transform">
                                            <span class="text-lg font-bold text-white">
                                                {{ $reg->car->getAttributes()['race_number'] ?? '?' }}
                                            </span>
                                        </div>
                                        <div>
                                            <div class="font-semibold text-white group-hover:text-racing-red-400 transition-colors">
                                                {{ $reg->pilot->full_name }}
                                                @if($reg->pilot->is_minor)
                                                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-medium bg-status-warning/20 text-status-warning">
                                                        Mineur
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="text-sm text-carbon-400">
                                                {{ $reg->car->make }} {{ $reg->car->model }}
                                                <span class="mx-1 text-carbon-600">•</span>
                                                {{ $reg->car->category->name ?? 'Non catégorisé' }}
                                            </div>
                                            <div class="text-xs text-carbon-500 mt-1">
                                                Licence: {{ $reg->pilot->license_number ?? 'Non renseignée' }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-carbon-500 group-hover:text-racing-red-400 transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <x-racing.empty-state
                                icon="✅"
                                title="{{ $searchQuery ? 'Aucun résultat' : 'Tout est signé !' }}"
                                message="{{ $searchQuery ? 'Aucun résultat pour « ' . $searchQuery . ' »' : 'Tous les engagements ont été signés 🎉' }}"
                            />
                        @endforelse
                    </div>
                </x-racing.card>
            </div>

            {{-- Signed Engagements --}}
            <div>
                <x-racing.card>
                    <x-slot name="header">
                        <div class="flex items-center justify-between">
                            <h2 class="text-lg font-semibold text-white flex items-center gap-2">
                                <span class="text-status-success">✅</span>
                                Engagements signés récents
                            </h2>
                            <span class="px-3 py-1 bg-status-success/20 text-status-success text-sm rounded-full">
                                {{ $this->stats['signed'] }} signés
                            </span>
                        </div>
                    </x-slot>

                    <div class="divide-y divide-carbon-700/50 max-h-[600px] overflow-y-auto -mx-5 px-5">
                        @forelse($this->signedEngagements as $engagement)
                            <div class="py-4">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-4">
                                        <div class="flex-shrink-0 w-14 h-14 bg-gradient-to-br from-status-success/20 to-status-success/10 rounded-xl flex items-center justify-center border border-status-success/30">
                                            <svg class="w-7 h-7 text-status-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <div class="font-semibold text-white">
                                                {{ $engagement->pilot_name }}
                                                <span class="ml-2 text-sm font-medium text-racing-red-400">
                                                    #{{ $engagement->car_race_number }}
                                                </span>
                                            </div>
                                            <div class="text-sm text-carbon-400">
                                                {{ $engagement->car_make }} {{ $engagement->car_model }}
                                            </div>
                                            <div class="text-xs text-carbon-500 mt-1">
                                                Signé le {{ $engagement->signed_at->format('d/m/Y à H:i') }}
                                                par {{ $engagement->witness->name ?? 'Système' }}
                                            </div>
                                            {{-- Statuts de validation --}}
                                            <div class="mt-2 flex flex-wrap gap-2">
                                                {{-- Validation Administrative --}}
                                                @if($engagement->admin_validated_at)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-medium bg-status-info/20 text-status-info">
                                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                        </svg>
                                                        Admin: {{ $engagement->adminValidator?->name ?? 'Validé' }}
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-medium bg-carbon-700 text-carbon-400">
                                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                                        </svg>
                                                        Admin: En attente
                                                    </span>
                                                @endif

                                                {{-- Validation Technique --}}
                                                @if($engagement->tech_checked_at)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-medium bg-status-success/20 text-status-success">
                                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                        </svg>
                                                        Tech: {{ $engagement->tech_controller_name ?? 'Validé' }}
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-medium bg-carbon-700 text-carbon-400">
                                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                                        </svg>
                                                        Tech: En attente
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <a
                                            href="{{ route('staff.registrations.engagement-pdf', $engagement) }}"
                                            target="_blank"
                                            class="p-2 text-carbon-400 hover:text-racing-red-400 hover:bg-carbon-700 rounded-lg transition-all"
                                            title="Voir le PDF"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </a>
                                        <a
                                            href="{{ route('staff.registrations.engagement-pdf-download', $engagement) }}"
                                            class="p-2 text-carbon-400 hover:text-racing-red-400 hover:bg-carbon-700 rounded-lg transition-all"
                                            title="Télécharger le PDF"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <x-racing.empty-state
                                icon="📝"
                                title="Aucun engagement signé"
                                message="Aucun engagement signé pour cette course"
                            />
                        @endforelse
                    </div>
                </x-racing.card>
            </div>
        </div>
    @else
        <x-racing.alert type="warning" class="text-center py-8">
            <div class="flex flex-col items-center gap-4">
                <svg class="w-12 h-12 text-status-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <div>
                    <h3 class="text-lg font-semibold">Sélectionnez une course</h3>
                    <p class="mt-1 text-sm opacity-80">Veuillez sélectionner une course pour gérer les feuilles d'engagement.</p>
                </div>
            </div>
        </x-racing.alert>
    @endif

    {{-- Signature Modal --}}
    @if($showSignatureModal && $registration)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                {{-- Backdrop --}}
                <div class="fixed inset-0 bg-black/80 backdrop-blur-sm transition-opacity" wire:click="closeModal"></div>

                {{-- Modal Panel --}}
                <div class="relative bg-carbon-900 rounded-2xl text-left overflow-hidden shadow-2xl shadow-black/50 transform transition-all sm:my-8 sm:max-w-4xl sm:w-full border border-carbon-700">
                    {{-- Header --}}
                    <div class="bg-gradient-to-r from-racing-red-600 to-racing-red-700 px-6 py-5">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-xl font-bold text-white flex items-center gap-2" id="modal-title">
                                    <span>📝</span> Feuille d'Engagement
                                </h3>
                                <p class="text-racing-red-200 text-sm mt-1">
                                    {{ $registration->race->name }} - {{ $registration->race->race_date->format('d/m/Y') }}
                                </p>
                            </div>
                            <button wire:click="closeModal" class="text-white/80 hover:text-white hover:bg-white/10 rounded-lg p-2 transition-all">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Content --}}
                    <div class="px-6 py-6">
                        {{-- Errors --}}
                        @if($errors->any())
                            <x-racing.alert type="danger" class="mb-6">
                                @foreach($errors->all() as $error)
                                    <p>{{ $error }}</p>
                                @endforeach
                            </x-racing.alert>
                        @endif

                        {{-- Pilot & Car Info --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            {{-- Pilot Info --}}
                            <div class="bg-carbon-800 rounded-xl p-5 border border-carbon-700">
                                <h4 class="font-semibold text-white mb-4 flex items-center gap-2">
                                    <span class="text-racing-red-400">👤</span>
                                    Pilote
                                </h4>
                                <div class="space-y-3 text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-carbon-400">Nom :</span>
                                        <span class="font-medium text-white">{{ $registration->pilot->full_name }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-carbon-400">Licence :</span>
                                        <span class="font-medium text-white">{{ $registration->pilot->license_number ?? 'Non renseignée' }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-carbon-400">Né(e) le :</span>
                                        <span class="font-medium text-white">{{ $registration->pilot->birth_date?->format('d/m/Y') ?? 'Non renseigné' }}</span>
                                    </div>

                                    {{-- Additional pilot fields for official form --}}
                                    <div class="pt-3 mt-3 border-t border-carbon-700 space-y-3">
                                        <div>
                                            <label class="block text-xs text-carbon-400 mb-1.5">N° Permis</label>
                                            <input
                                                type="text"
                                                wire:model="pilotPermitNumber"
                                                class="w-full text-sm rounded-lg bg-carbon-900 border-carbon-600 text-white
                                                    focus:border-racing-red-500 focus:ring focus:ring-racing-red-500/20"
                                                placeholder="123456789"
                                            >
                                        </div>
                                        <div>
                                            <label class="block text-xs text-carbon-400 mb-1.5">Délivré le</label>
                                            <input
                                                type="date"
                                                wire:model="pilotPermitDate"
                                                class="w-full text-sm rounded-lg bg-carbon-900 border-carbon-600 text-white
                                                    focus:border-racing-red-500 focus:ring focus:ring-racing-red-500/20
                                                    [color-scheme:dark]"
                                            >
                                        </div>
                                        <div>
                                            <label class="block text-xs text-carbon-400 mb-1.5">Email</label>
                                            <input
                                                type="email"
                                                wire:model="pilotEmail"
                                                class="w-full text-sm rounded-lg bg-carbon-900 border-carbon-600 text-white
                                                    focus:border-racing-red-500 focus:ring focus:ring-racing-red-500/20"
                                                placeholder="pilote@email.com"
                                            >
                                        </div>
                                    </div>

                                    @if($registration->pilot->is_minor)
                                        <div class="mt-4 p-3 bg-status-warning/10 border border-status-warning/30 rounded-lg text-status-warning">
                                            <strong class="flex items-center gap-2">
                                                <span>⚠️</span> Pilote mineur
                                            </strong>
                                            <span class="block mt-1 text-sm opacity-80">
                                                Tuteur : {{ $registration->pilot->guardian_first_name }} {{ $registration->pilot->guardian_last_name }}
                                            </span>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Car Info --}}
                            <div class="bg-carbon-800 rounded-xl p-5 border border-carbon-700">
                                <h4 class="font-semibold text-white mb-4 flex items-center gap-2">
                                    <span class="text-racing-red-400">🏎️</span>
                                    Véhicule
                                </h4>
                                <div class="space-y-3 text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-carbon-400">Marque :</span>
                                        <span class="font-medium text-white">{{ $registration->car->make }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-carbon-400">Modèle :</span>
                                        <span class="font-medium text-white">{{ $registration->car->model }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-carbon-400">Catégorie :</span>
                                        <span class="font-medium text-white">{{ $registration->car->category->name ?? 'Non catégorisé' }}</span>
                                    </div>
                                    <div class="flex justify-between items-center pt-2">
                                        <span class="text-carbon-400">N° Course :</span>
                                        <span class="px-4 py-1.5 bg-gradient-to-r from-racing-red-600 to-racing-red-700 text-white font-bold rounded-lg shadow-lg shadow-racing-red-500/30">
                                            {{ $registration->car->getAttributes()['race_number'] ?? '?' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Engagement Text --}}
                        <div class="mb-6 p-5 bg-carbon-800 border border-carbon-700 rounded-xl text-sm text-carbon-300">
                            <p class="mb-3">
                                <strong class="text-white">Je soussigné(e), {{ $registration->pilot->full_name }},</strong>
                            </p>
                            <p class="mb-3">
                                Déclare m'engager à participer à l'épreuve <strong class="text-white">{{ $registration->race->name }}</strong>
                                et certifie avoir pris connaissance du règlement particulier de l'épreuve.
                            </p>
                            <p class="text-xs text-carbon-500">
                                Je certifie être titulaire d'une licence valide, que mon véhicule est conforme.
                                J'accepte les risques inhérents à la pratique du sport automobile.
                            </p>
                        </div>

                        {{-- Signature Areas --}}
                        <div class="grid grid-cols-1 {{ $registration->pilot->is_minor ? 'md:grid-cols-2' : '' }} gap-6">
                            {{-- Pilot Signature --}}
                            <div>
                                <label class="block text-sm font-medium text-carbon-300 mb-2">
                                    Signature du pilote <span class="text-racing-red-400">*</span>
                                </label>
                                <div class="border-2 border-dashed rounded-xl p-4 transition-all
                                    {{ $signatureData ? 'border-status-success bg-status-success/10' : 'border-carbon-600 hover:border-racing-red-500/50' }}">
                                    @if($signatureData)
                                        <div class="text-center">
                                            <img src="{{ $signatureData }}" alt="Signature" class="max-h-24 mx-auto">
                                            <button
                                                wire:click="clearSignature(false)"
                                                class="mt-3 text-sm text-status-danger hover:text-status-danger/80 transition-colors"
                                            >
                                                Effacer et refaire
                                            </button>
                                        </div>
                                    @else
                                        <button
                                            type="button"
                                            onclick="openSignaturePad(false)"
                                            class="w-full py-8 text-center text-carbon-400 hover:text-racing-red-400 transition-colors group"
                                        >
                                            <svg class="w-12 h-12 mx-auto mb-2 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                            </svg>
                                            <span class="text-lg font-medium">Cliquez pour signer</span>
                                        </button>
                                    @endif
                                </div>
                            </div>

                            {{-- Guardian Signature (if minor) --}}
                            @if($registration->pilot->is_minor)
                                <div>
                                    <label class="block text-sm font-medium text-carbon-300 mb-2">
                                        Signature du tuteur légal <span class="text-racing-red-400">*</span>
                                        <span class="text-carbon-500 font-normal">({{ $registration->pilot->guardian_first_name }} {{ $registration->pilot->guardian_last_name }})</span>
                                    </label>
                                    <div class="border-2 border-dashed rounded-xl p-4 transition-all
                                        {{ $guardianSignatureData ? 'border-status-success bg-status-success/10' : 'border-carbon-600 hover:border-racing-red-500/50' }}">
                                        @if($guardianSignatureData)
                                            <div class="text-center">
                                                <img src="{{ $guardianSignatureData }}" alt="Signature tuteur" class="max-h-24 mx-auto">
                                                <button
                                                    wire:click="clearSignature(true)"
                                                    class="mt-3 text-sm text-status-danger hover:text-status-danger/80 transition-colors"
                                                >
                                                    Effacer et refaire
                                                </button>
                                            </div>
                                        @else
                                            <button
                                                type="button"
                                                onclick="openSignaturePad(true)"
                                                class="w-full py-8 text-center text-carbon-400 hover:text-racing-red-400 transition-colors group"
                                            >
                                                <svg class="w-12 h-12 mx-auto mb-2 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                                </svg>
                                                <span class="text-lg font-medium">Cliquez pour signer</span>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="bg-carbon-800 px-6 py-4 flex justify-end gap-3 border-t border-carbon-700">
                        <x-racing.button
                            wire:click="closeModal"
                            variant="secondary"
                        >
                            Annuler
                        </x-racing.button>
                        <x-racing.button
                            wire:click="submitEngagement"
                            wire:loading.attr="disabled"
                        >
                            <span wire:loading.remove>Valider l'engagement</span>
                            <span wire:loading>Enregistrement...</span>
                        </x-racing.button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Signature Pad Modal --}}
        <div id="signature-pad-modal" class="hidden fixed inset-0 z-[60] overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="fixed inset-0 bg-black/90" onclick="closeSignaturePad()"></div>
                <div class="relative bg-carbon-900 rounded-2xl shadow-2xl shadow-black/50 w-full max-w-2xl border border-carbon-700">
                    <div class="p-5 border-b border-carbon-700 flex justify-between items-center">
                        <h4 id="signature-pad-title" class="text-lg font-semibold text-white flex items-center gap-2">
                            <span>✍️</span> Signature du pilote
                        </h4>
                        <button onclick="closeSignaturePad()" class="text-carbon-400 hover:text-white hover:bg-carbon-700 rounded-lg p-2 transition-all">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    <div class="p-5">
                        <p class="text-sm text-carbon-400 mb-4 text-center">
                            Signez dans le cadre ci-dessous avec votre doigt ou un stylet
                        </p>
                        <div class="border-2 border-carbon-600 rounded-xl bg-white overflow-hidden">
                            <canvas id="signature-canvas" class="w-full touch-none" style="height: 200px;"></canvas>
                        </div>
                    </div>
                    <div class="p-5 border-t border-carbon-700 flex justify-between">
                        <x-racing.button
                            onclick="clearCanvas()"
                            variant="secondary"
                            size="sm"
                        >
                            Effacer
                        </x-racing.button>
                        <x-racing.button
                            onclick="saveSignature()"
                            size="sm"
                        >
                            Valider la signature
                        </x-racing.button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Signature Pad JavaScript --}}
    @script
    <script>
        let canvas, ctx;
        let isDrawing = false;
        let lastX = 0;
        let lastY = 0;
        let isGuardian = false;

        function initCanvas() {
            canvas = document.getElementById('signature-canvas');
            if (!canvas) return;

            ctx = canvas.getContext('2d');

            // Set canvas size
            const rect = canvas.getBoundingClientRect();
            canvas.width = rect.width * 2;
            canvas.height = 400;

            // Set drawing style
            ctx.strokeStyle = '#000';
            ctx.lineWidth = 3;
            ctx.lineCap = 'round';
            ctx.lineJoin = 'round';

            // Scale for retina
            ctx.scale(2, 2);

            // Clear canvas
            ctx.fillStyle = '#fff';
            ctx.fillRect(0, 0, canvas.width, canvas.height);

            // Mouse events
            canvas.addEventListener('mousedown', startDrawing);
            canvas.addEventListener('mousemove', draw);
            canvas.addEventListener('mouseup', stopDrawing);
            canvas.addEventListener('mouseout', stopDrawing);

            // Touch events
            canvas.addEventListener('touchstart', handleTouchStart);
            canvas.addEventListener('touchmove', handleTouchMove);
            canvas.addEventListener('touchend', stopDrawing);
        }

        function getPosition(e, canvas) {
            const rect = canvas.getBoundingClientRect();
            const scaleX = canvas.width / rect.width / 2;
            const scaleY = canvas.height / rect.height / 2;

            if (e.touches) {
                return {
                    x: (e.touches[0].clientX - rect.left) * scaleX,
                    y: (e.touches[0].clientY - rect.top) * scaleY
                };
            }
            return {
                x: (e.clientX - rect.left) * scaleX,
                y: (e.clientY - rect.top) * scaleY
            };
        }

        function startDrawing(e) {
            isDrawing = true;
            const pos = getPosition(e, canvas);
            lastX = pos.x;
            lastY = pos.y;
        }

        function draw(e) {
            if (!isDrawing) return;
            e.preventDefault();

            const pos = getPosition(e, canvas);

            ctx.beginPath();
            ctx.moveTo(lastX, lastY);
            ctx.lineTo(pos.x, pos.y);
            ctx.stroke();

            lastX = pos.x;
            lastY = pos.y;
        }

        function stopDrawing() {
            isDrawing = false;
        }

        function handleTouchStart(e) {
            e.preventDefault();
            startDrawing(e);
        }

        function handleTouchMove(e) {
            e.preventDefault();
            draw(e);
        }

        window.openSignaturePad = function(forGuardian) {
            isGuardian = forGuardian;
            document.getElementById('signature-pad-modal').classList.remove('hidden');
            document.getElementById('signature-pad-title').textContent =
                forGuardian ? 'Signature du tuteur légal' : 'Signature du pilote';

            setTimeout(initCanvas, 100);
        }

        window.closeSignaturePad = function() {
            document.getElementById('signature-pad-modal').classList.add('hidden');
        }

        window.clearCanvas = function() {
            if (ctx) {
                ctx.fillStyle = '#fff';
                ctx.fillRect(0, 0, canvas.width / 2, canvas.height / 2);
            }
        }

        window.saveSignature = function() {
            if (!canvas) return;

            const dataUrl = canvas.toDataURL('image/png');

            // Check if canvas is empty (mostly white)
            const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
            let nonWhitePixels = 0;
            for (let i = 0; i < imageData.data.length; i += 4) {
                if (imageData.data[i] < 250 || imageData.data[i+1] < 250 || imageData.data[i+2] < 250) {
                    nonWhitePixels++;
                }
            }

            if (nonWhitePixels < 100) {
                alert('Veuillez signer avant de valider.');
                return;
            }

            $wire.saveSignature(dataUrl);
            closeSignaturePad();
        }
    </script>
    @endscript
</div>
