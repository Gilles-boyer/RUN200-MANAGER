<div class="max-w-3xl mx-auto">
    {{-- Back Link --}}
    <a href="{{ route('staff.registrations.index') }}"
       class="inline-flex items-center gap-2 text-sm text-gray-400 hover:text-checkered-yellow-500 transition-colors mb-4">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Retour aux inscriptions
    </a>

    {{-- Racing Header --}}
    <div class="relative mb-6 -mx-4 sm:mx-0 px-4 sm:px-8 py-8 bg-racing-gradient-subtle rounded-none sm:rounded-2xl overflow-hidden">
        <div class="absolute top-0 right-0 w-48 h-48 bg-racing-red-500/5 rounded-full -translate-y-1/2 translate-x-1/2"></div>
        <div class="relative">
            <h1 class="text-2xl font-bold text-white flex items-center gap-2">
                <span>🔧</span> Contrôle Technique
            </h1>
            <p class="text-gray-400 mt-1">
                Course : <span class="text-checkered-yellow-500 font-medium">{{ $registration->race->name }}</span>
            </p>
        </div>
    </div>

    {{-- Messages --}}
    @if($errorMessage)
        <x-racing.alert type="danger" class="mb-6" dismissible>
            {{ $errorMessage }}
        </x-racing.alert>
    @endif

    @if($successMessage)
        <x-racing.alert type="success" class="mb-6" dismissible>
            {{ $successMessage }}
        </x-racing.alert>
    @endif

    {{-- Registration Info Card --}}
    <x-racing.card class="mb-6">
        <div class="flex items-center justify-between mb-6 pb-4 border-b border-carbon-700/50">
            <h2 class="text-lg font-semibold text-white">📋 Informations Inscription</h2>
            <div class="flex items-center gap-3">
                <a href="{{ route('staff.cars.tech-history', $registration->car) }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium text-checkered-yellow-500 hover:text-checkered-yellow-400 hover:bg-checkered-yellow-500/10 border border-checkered-yellow-500/30 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Historique
                </a>
                <span class="inline-flex items-center px-3 py-1 rounded-lg text-sm font-semibold
                    @if(in_array($registration->status, ['ACCEPTED', 'ADMIN_CHECKED']))
                        bg-status-info/20 text-status-info border border-status-info/30
                    @elseif($registration->status === 'TECH_CHECKED_OK')
                        bg-status-success/20 text-status-success border border-status-success/30
                    @elseif($registration->status === 'TECH_CHECKED_FAIL')
                        bg-status-danger/20 text-status-danger border border-status-danger/30
                    @else
                        bg-carbon-700 text-gray-400 border border-carbon-600
                    @endif">
                    {{ $registration->status }}
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Pilot Info --}}
            <div class="p-4 bg-carbon-700/30 rounded-xl border border-carbon-700/50">
                <p class="text-xs text-gray-500 uppercase tracking-wide mb-3">👤 Pilote</p>
                <div class="flex items-center gap-3">
                    @if($registration->pilot->photo_path)
                        <img src="{{ Storage::url($registration->pilot->photo_path) }}"
                             alt="Photo pilote" class="w-12 h-12 rounded-xl object-cover ring-2 ring-carbon-600">
                    @else
                        <div class="w-12 h-12 rounded-xl bg-carbon-700 flex items-center justify-center ring-2 ring-carbon-600">
                            <svg class="w-6 h-6 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                    @endif
                    <div>
                        <p class="font-semibold text-white">
                            {{ $registration->pilot->last_name }} {{ $registration->pilot->first_name }}
                        </p>
                        <p class="text-sm text-gray-400">
                            Licence: <span class="text-checkered-yellow-500 font-mono">{{ $registration->pilot->license_number }}</span>
                        </p>
                    </div>
                </div>
            </div>

            {{-- Car Info --}}
            <div class="p-4 bg-carbon-700/30 rounded-xl border border-carbon-700/50">
                <p class="text-xs text-gray-500 uppercase tracking-wide mb-3">🚗 Voiture</p>
                <div class="flex items-center gap-4">
                    <span class="text-3xl font-black text-racing-red-500">
                        #{{ $registration->car->race_number }}
                    </span>
                    <div>
                        <p class="font-medium text-white">
                            {{ $registration->car->make }} {{ $registration->car->model }}
                        </p>
                        <p class="text-sm text-gray-400">
                            {{ $registration->car->category->name ?? 'N/A' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </x-racing.card>

    {{-- Existing Inspection Result --}}
    @if($registration->techInspection)
        <x-racing.card class="mb-6">
            <h2 class="text-lg font-semibold text-white mb-6 pb-4 border-b border-carbon-700/50">
                📊 Résultat du contrôle technique
            </h2>

            <div class="flex items-center gap-4 mb-4">
                @if($registration->techInspection->isOk())
                    <div class="flex-shrink-0 w-16 h-16 rounded-2xl bg-status-success/20 flex items-center justify-center">
                        <svg class="w-10 h-10 text-status-success" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xl font-bold text-status-success">VALIDÉ</p>
                        <p class="text-sm text-gray-400">Le contrôle technique a été validé</p>
                    </div>
                @else
                    <div class="flex-shrink-0 w-16 h-16 rounded-2xl bg-status-danger/20 flex items-center justify-center">
                        <svg class="w-10 h-10 text-status-danger" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xl font-bold text-status-danger">ÉCHOUÉ</p>
                        <p class="text-sm text-gray-400">Le contrôle technique a échoué</p>
                    </div>
                @endif
            </div>

            @if($registration->techInspection->notes)
                <div class="mt-4 p-4 bg-carbon-700/30 rounded-xl border border-carbon-700/50">
                    <p class="text-xs text-gray-500 uppercase tracking-wide mb-2">Notes</p>
                    <p class="text-white">{{ $registration->techInspection->notes }}</p>
                </div>
            @endif

            <div class="mt-4 text-sm text-gray-400">
                Contrôlé par <span class="font-medium text-white">{{ $registration->techInspection->inspector->name }}</span>
                le <span class="text-checkered-yellow-500">{{ $registration->techInspection->inspected_at->format('d/m/Y à H:i') }}</span>
            </div>

            @if($this->canReset())
                <div class="mt-6 pt-4 border-t border-carbon-700/50">
                    <x-racing.button
                        wire:click="resetInspection"
                        wire:confirm="Êtes-vous sûr de vouloir réinitialiser ce contrôle technique ?"
                        variant="warning"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Réinitialiser le contrôle
                    </x-racing.button>
                    <p class="mt-2 text-xs text-gray-500">
                        Permet au pilote de repasser le contrôle technique après correction des problèmes.
                    </p>
                </div>
            @endif
        </x-racing.card>
    @endif

    {{-- Inspection Form --}}
    @if($this->canInspect())
        <x-racing.card>
            <h2 class="text-lg font-semibold text-white mb-6 pb-4 border-b border-carbon-700/50">
                🔧 Effectuer le contrôle technique
            </h2>

            {{-- Status Selection --}}
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-300 mb-3">Résultat du contrôle</label>
                <div class="grid grid-cols-2 gap-4">
                    {{-- OK Button --}}
                    <button wire:click="$set('status', 'OK')"
                            type="button"
                            class="p-6 rounded-xl border-2 transition-all
                                @if($status === 'OK')
                                    border-status-success bg-status-success/10
                                @else
                                    border-carbon-700 hover:border-status-success/50
                                @endif">
                        <div class="flex flex-col items-center gap-2">
                            <div class="w-12 h-12 rounded-xl flex items-center justify-center
                                @if($status === 'OK')
                                    bg-status-success text-white
                                @else
                                    bg-carbon-700 text-gray-400
                                @endif">
                                <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <span class="font-bold text-lg @if($status === 'OK') text-status-success @else text-gray-300 @endif">
                                VALIDÉ
                            </span>
                            <span class="text-xs text-gray-500">Le véhicule est conforme</span>
                        </div>
                    </button>

                    {{-- FAIL Button --}}
                    <button wire:click="$set('status', 'FAIL')"
                            type="button"
                            class="p-6 rounded-xl border-2 transition-all
                                @if($status === 'FAIL')
                                    border-status-danger bg-status-danger/10
                                @else
                                    border-carbon-700 hover:border-status-danger/50
                                @endif">
                        <div class="flex flex-col items-center gap-2">
                            <div class="w-12 h-12 rounded-xl flex items-center justify-center
                                @if($status === 'FAIL')
                                    bg-status-danger text-white
                                @else
                                    bg-carbon-700 text-gray-400
                                @endif">
                                <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <span class="font-bold text-lg @if($status === 'FAIL') text-status-danger @else text-gray-300 @endif">
                                ÉCHOUÉ
                            </span>
                            <span class="text-xs text-gray-500">Non-conformités détectées</span>
                        </div>
                    </button>
                </div>
            </div>

            {{-- Notes --}}
            <div class="mb-6">
                <x-racing.form.textarea
                    wire:model="notes"
                    label="Notes {{ $status === 'FAIL' ? '*' : '' }}"
                    rows="4"
                    placeholder="{{ $status === 'FAIL' ? 'Décrivez les non-conformités détectées (obligatoire)...' : 'Notes optionnelles sur le contrôle...' }}"
                />
                @if($status === 'FAIL')
                    <p class="mt-1 text-xs text-status-danger">
                        Les notes sont obligatoires pour un contrôle échoué.
                    </p>
                @endif
            </div>

            {{-- Submit Buttons --}}
            <div class="flex justify-end gap-3">
                <x-racing.button href="{{ route('staff.registrations.index') }}" variant="secondary">
                    Annuler
                </x-racing.button>
                <x-racing.button
                    wire:click="confirmInspection"
                    :variant="$status === 'OK' ? 'success' : 'danger'"
                >
                    @if($status === 'OK')
                        Valider le contrôle
                    @else
                        Enregistrer l'échec
                    @endif
                </x-racing.button>
            </div>
        </x-racing.card>
    @elseif(!$registration->techInspection)
        {{-- Cannot inspect message --}}
        <x-racing.alert type="warning">
            Cette inscription n'est pas éligible au contrôle technique.
            Statut actuel : <strong>{{ $registration->status }}</strong>
        </x-racing.alert>
    @endif

    {{-- Confirmation Modal --}}
    @if($showConfirmation)
        <div class="fixed inset-0 bg-carbon-900/80 backdrop-blur-sm flex items-center justify-center z-50" wire:click.self="cancelConfirmation">
            <div class="bg-carbon-800 rounded-2xl shadow-2xl max-w-md w-full mx-4 overflow-hidden border border-carbon-700/50">
                <div class="px-6 py-4 border-b border-carbon-700/50">
                    <h3 class="text-lg font-semibold text-white">Confirmer le contrôle technique</h3>
                </div>
                <div class="p-6">
                    <div class="flex items-center gap-3 mb-4">
                        @if($status === 'OK')
                            <div class="w-12 h-12 rounded-xl bg-status-success/20 flex items-center justify-center">
                                <svg class="w-6 h-6 text-status-success" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-white">Valider le contrôle technique</p>
                                <p class="text-sm text-gray-400">Le véhicule sera marqué comme conforme.</p>
                            </div>
                        @else
                            <div class="w-12 h-12 rounded-xl bg-status-danger/20 flex items-center justify-center">
                                <svg class="w-6 h-6 text-status-danger" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-white">Enregistrer l'échec</p>
                                <p class="text-sm text-gray-400">Le pilote ne pourra pas entrer en piste.</p>
                            </div>
                        @endif
                    </div>

                    <div class="p-4 bg-carbon-700/30 rounded-xl border border-carbon-700/50 mb-4">
                        <p class="text-sm text-gray-300">
                            <strong class="text-white">Pilote:</strong> {{ $registration->pilot->last_name }} {{ $registration->pilot->first_name }}<br>
                            <strong class="text-white">Voiture:</strong> <span class="text-racing-red-500">#{{ $registration->car->race_number }}</span> — {{ $registration->car->make }} {{ $registration->car->model }}
                        </p>
                    </div>

                    @if($notes)
                        <div class="p-4 bg-carbon-700/30 rounded-xl border border-carbon-700/50 mb-4">
                            <p class="text-xs text-gray-500 uppercase mb-1">Notes</p>
                            <p class="text-sm text-white">{{ $notes }}</p>
                        </div>
                    @endif
                </div>
                <div class="px-6 py-4 bg-carbon-700/30 flex justify-end gap-3">
                    <x-racing.button wire:click="cancelConfirmation" variant="secondary">
                        Annuler
                    </x-racing.button>
                    <x-racing.button
                        wire:click="submitInspection"
                        :variant="$status === 'OK' ? 'success' : 'danger'"
                    >
                        Confirmer
                    </x-racing.button>
                </div>
            </div>
        </div>
    @endif
</div>
