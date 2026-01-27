<div class="max-w-6xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
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
                <span>📍</span> Suivi des étapes - Checkpoints
            </h1>
            <p class="text-gray-400 mt-1">
                Visualisez et corrigez les passages aux différents checkpoints du pilote
            </p>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if (session()->has('success'))
        <x-racing.alert type="success" class="mb-4" dismissible>
            {{ session('success') }}
        </x-racing.alert>
    @endif

    @if (session()->has('error'))
        <x-racing.alert type="danger" class="mb-4" dismissible>
            {{ session('error') }}
        </x-racing.alert>
    @endif

    {{-- Registration Info Card --}}
    <x-racing.card class="mb-6">
        <h2 class="text-lg font-semibold text-white mb-6 pb-4 border-b border-carbon-700/50 flex items-center gap-2">
            <span>📋</span> Informations de l'inscription
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            {{-- Pilot Info --}}
            <div class="flex items-start space-x-4">
                @if($registration->pilot->photo_path)
                    <img src="{{ Storage::url($registration->pilot->photo_path) }}" class="h-16 w-16 rounded-xl object-cover ring-2 ring-carbon-600">
                @else
                    <div class="h-16 w-16 rounded-xl bg-carbon-700 flex items-center justify-center ring-2 ring-carbon-600">
                        <span class="text-white font-bold text-xl">
                            {{ substr($registration->pilot->first_name, 0, 1) }}{{ substr($registration->pilot->last_name, 0, 1) }}
                        </span>
                    </div>
                @endif
                <div>
                    <h3 class="text-xs font-medium text-gray-500 uppercase tracking-wide">Pilote</h3>
                    <p class="text-lg font-semibold text-white">
                        {{ $registration->pilot->first_name }} {{ $registration->pilot->last_name }}
                    </p>
                    <p class="text-sm text-gray-400">
                        Licence <span class="text-checkered-yellow-500 font-mono">#{{ $registration->pilot->license_number }}</span>
                    </p>
                </div>
            </div>

            {{-- Car Info --}}
            <div>
                <h3 class="text-xs font-medium text-gray-500 uppercase tracking-wide">Voiture</h3>
                <p class="text-lg font-semibold text-white">
                    <span class="text-racing-red-500">#{{ $registration->car->race_number }}</span> — {{ $registration->car->make }} {{ $registration->car->model }}
                </p>
                <p class="text-sm text-gray-400">
                    Catégorie: {{ $registration->car->category->name ?? 'N/A' }}
                </p>
            </div>

            {{-- Race Info --}}
            <div>
                <h3 class="text-xs font-medium text-gray-500 uppercase tracking-wide">Course</h3>
                <p class="text-lg font-semibold text-checkered-yellow-500">
                    {{ $registration->race->name }}
                </p>
                <p class="text-sm text-gray-400">
                    {{ $registration->race->race_date->format('d/m/Y') }} — {{ $registration->race->location }}
                </p>
            </div>
        </div>

        {{-- Status badges --}}
        <div class="mt-6 flex flex-wrap items-center gap-3 pt-4 border-t border-carbon-700/50">
            <span class="inline-flex items-center px-3 py-1 rounded-lg text-sm font-semibold
                @if($registration->status === 'PENDING_VALIDATION') bg-status-warning/20 text-status-warning border border-status-warning/30
                @elseif($registration->status === 'ACCEPTED') bg-status-success/20 text-status-success border border-status-success/30
                @elseif($registration->status === 'TECH_VERIFIED') bg-status-info/20 text-status-info border border-status-info/30
                @elseif($registration->status === 'ENTERED') bg-checkered-yellow-500/20 text-checkered-yellow-500 border border-checkered-yellow-500/30
                @elseif($registration->status === 'READY') bg-status-success/20 text-status-success border border-status-success/30
                @else bg-status-danger/20 text-status-danger border border-status-danger/30
                @endif">
                Statut: {{ $registration->status }}
            </span>

            @if($registration->paddock)
                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-lg text-sm font-semibold bg-status-info/20 text-status-info border border-status-info/30">
                    📍 Paddock: {{ $registration->paddock }}
                </span>
            @endif

            @if($registration->techInspection)
                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-lg text-sm font-semibold
                    @if($registration->techInspection->passed) bg-status-success/20 text-status-success border border-status-success/30
                    @else bg-status-danger/20 text-status-danger border border-status-danger/30
                    @endif">
                    🔧 CT: {{ $registration->techInspection->passed ? 'Validé' : 'Refusé' }}
                </span>
            @endif

            @if($registration->payments->where('status', 'paid')->count() > 0)
                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-lg text-sm font-semibold bg-status-success/20 text-status-success border border-status-success/30">
                    💳 Paiement OK
                </span>
            @endif
        </div>
    </x-racing.card>

    {{-- Checkpoints Timeline --}}
    <x-racing.card>
        <div class="flex items-center justify-between mb-6 pb-4 border-b border-carbon-700/50">
            <h2 class="text-lg font-semibold text-white flex items-center gap-2">
                <span>🏁</span> Étapes du parcours
            </h2>
            <span class="px-3 py-1 rounded-lg text-sm font-semibold bg-carbon-700 text-gray-300 border border-carbon-600">
                {{ $this->passagesByCheckpoint->count() }} / {{ $this->checkpoints->count() }} validés
            </span>
        </div>

        <div class="relative">
            {{-- Timeline line --}}
            <div class="absolute left-8 top-0 bottom-0 w-0.5 bg-carbon-700"></div>

            <div class="space-y-6">
                @foreach($this->checkpoints as $checkpoint)
                    @php
                        $passage = $this->passagesByCheckpoint->get($checkpoint->id);
                        $status = $this->getCheckpointStatus($checkpoint);
                    @endphp

                    <div class="relative flex items-start group">
                        {{-- Status indicator --}}
                        <div class="flex-shrink-0 w-16 flex items-center justify-center">
                            @if($status === 'completed')
                                <div class="w-10 h-10 rounded-xl bg-status-success flex items-center justify-center z-10 shadow-lg shadow-status-success/30">
                                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                            @elseif($status === 'pending')
                                <div class="w-10 h-10 rounded-xl bg-checkered-yellow-500 flex items-center justify-center z-10 shadow-lg shadow-checkered-yellow-500/30">
                                    <svg class="w-6 h-6 text-carbon-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                            @else
                                <div class="w-10 h-10 rounded-xl bg-carbon-700 flex items-center justify-center z-10 border border-carbon-600">
                                    <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                </div>
                            @endif
                        </div>

                        {{-- Content --}}
                        <div class="flex-1 ml-4 min-w-0">
                            <div class="bg-carbon-700/30 rounded-xl p-4 border
                                {{ $status === 'completed' ? 'border-l-4 border-l-status-success border-t-carbon-700/50 border-r-carbon-700/50 border-b-carbon-700/50' : ($status === 'pending' ? 'border-l-4 border-l-checkered-yellow-500 border-t-carbon-700/50 border-r-carbon-700/50 border-b-carbon-700/50' : 'border-carbon-700/50') }}">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h3 class="text-base font-semibold text-white">{{ $checkpoint->name }}</h3>
                                        <p class="text-sm text-gray-400">Code: <span class="font-mono text-checkered-yellow-500">{{ $checkpoint->code }}</span></p>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        @if($passage)
                                            <button wire:click="openEditPassageModal({{ $passage->id }})"
                                                class="p-2 text-checkered-yellow-500 hover:text-checkered-yellow-400 hover:bg-checkered-yellow-500/10 rounded-lg transition" title="Modifier">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </button>
                                            <button wire:click="confirmDeletePassage({{ $passage->id }})"
                                                class="p-2 text-status-danger hover:text-status-danger/80 hover:bg-status-danger/10 rounded-lg transition" title="Supprimer">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        @else
                                            <button wire:click="openAddPassageModal({{ $checkpoint->id }})"
                                                class="p-2 text-status-success hover:text-status-success/80 hover:bg-status-success/10 rounded-lg transition" title="Ajouter manuellement">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                                </svg>
                                            </button>
                                        @endif
                                    </div>
                                </div>

                                @if($passage)
                                    <div class="mt-3 pt-3 border-t border-carbon-700/50">
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                            <div>
                                                <span class="text-gray-500">Scanné le:</span>
                                                <span class="ml-2 font-medium text-white">{{ $passage->scanned_at->format('d/m/Y à H:i:s') }}</span>
                                            </div>
                                            <div>
                                                <span class="text-gray-500">Par:</span>
                                                <span class="ml-2 font-medium text-white">{{ $passage->scanner->name ?? 'Inconnu' }}</span>
                                            </div>
                                        </div>

                                        @if($passage->meta && isset($passage->meta['manual_entry']))
                                            <div class="mt-2">
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-xs font-medium bg-status-warning/20 text-status-warning border border-status-warning/30">
                                                    ✏️ Entrée manuelle
                                                </span>
                                            </div>
                                        @endif

                                        @if($passage->meta && isset($passage->meta['staff_note']) && $passage->meta['staff_note'])
                                            <div class="mt-3 p-3 bg-checkered-yellow-500/10 border border-checkered-yellow-500/30 rounded-lg">
                                                <div class="flex items-start">
                                                    <span class="text-lg mr-2">📝</span>
                                                    <div>
                                                        <p class="text-xs font-medium text-checkered-yellow-500 mb-1">Note interne (staff uniquement)</p>
                                                        <p class="text-sm text-gray-300">{{ $passage->meta['staff_note'] }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    <div class="mt-3 pt-3 border-t border-carbon-700/50">
                                        <p class="text-sm text-gray-500 italic">
                                            @if($status === 'locked')
                                                🔒 En attente des étapes précédentes
                                            @else
                                                ⏳ Non scanné - En attente
                                            @endif
                                        </p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </x-racing.card>

    {{-- Activity Log --}}
    @if($this->registrationActivities->count() > 0)
    <x-racing.card class="mt-6">
        <h2 class="text-lg font-semibold text-white mb-6 pb-4 border-b border-carbon-700/50 flex items-center gap-2">
            <span>📜</span> Historique des modifications
        </h2>
        <div class="flow-root">
            <ul class="-mb-8">
                @foreach($this->registrationActivities->take(10) as $activity)
                    <li>
                        <div class="relative pb-8">
                            @if(!$loop->last)
                                <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-carbon-700" aria-hidden="true"></span>
                            @endif
                            <div class="relative flex space-x-3">
                                <div>
                                    <span class="h-8 w-8 rounded-lg bg-carbon-700 flex items-center justify-center ring-4 ring-carbon-800">
                                        <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </span>
                                </div>
                                <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                    <div>
                                        <p class="text-sm text-gray-400">
                                            {{ $activity->description }}
                                            @if($activity->causer)
                                                <span class="font-medium text-white">par {{ $activity->causer->name }}</span>
                                            @endif
                                        </p>
                                    </div>
                                    <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                        {{ $activity->created_at->diffForHumans() }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    </x-racing.card>
    @endif

    {{-- Modal Add/Edit Passage --}}
    @if($showPassageModal)
    <div class="fixed inset-0 bg-carbon-900/80 backdrop-blur-sm z-50 flex items-center justify-center p-4" wire:click.self="closePassageModal">
        <div class="bg-carbon-800 rounded-2xl shadow-2xl max-w-lg w-full overflow-hidden border border-carbon-700/50">
            <div class="px-6 py-4 border-b border-carbon-700/50">
                <h3 class="text-lg font-semibold text-white">
                    {{ $editingPassageId ? '✏️ Modifier le passage' : '➕ Ajouter un passage manuellement' }}
                </h3>
            </div>

            <div class="p-6">
                @php $selectedCheckpoint = $this->checkpoints->find($selectedCheckpointId); @endphp

                <div class="mb-4 p-4 bg-carbon-700/30 rounded-xl border border-carbon-700/50">
                    <p class="text-sm text-gray-300">
                        <strong class="text-white">Checkpoint:</strong> {{ $selectedCheckpoint->name ?? 'N/A' }}
                    </p>
                    <p class="text-sm text-gray-300">
                        <strong class="text-white">Pilote:</strong> {{ $registration->pilot->first_name }} {{ $registration->pilot->last_name }}
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <x-racing.form.input type="date" wire:model="passageDate" label="Date *" :error="$errors->first('passageDate')" />
                    <x-racing.form.input type="time" wire:model="passageTime" label="Heure *" :error="$errors->first('passageTime')" />
                </div>

                <div class="mt-4">
                    <x-racing.form.textarea wire:model="staffNote" label="Note interne" rows="2" placeholder="Ex: Documents vérifiés par Jean, RAS..." hint="Facultatif - visible uniquement par le staff" />
                </div>

                <p class="mt-4 text-xs text-gray-500">
                    <strong>Note:</strong> Cette modification sera enregistrée dans l'historique avec votre nom.
                </p>
            </div>

            <div class="px-6 py-4 bg-carbon-700/30 flex justify-end gap-3">
                <x-racing.button wire:click="closePassageModal" variant="secondary">
                    Annuler
                </x-racing.button>
                <x-racing.button wire:click="savePassage">
                    {{ $editingPassageId ? 'Modifier' : 'Ajouter' }}
                </x-racing.button>
            </div>
        </div>
    </div>
    @endif

    {{-- Modal Confirm Delete --}}
    @if($showDeleteModal)
    <div class="fixed inset-0 bg-carbon-900/80 backdrop-blur-sm z-50 flex items-center justify-center p-4" wire:click.self="closeDeleteModal">
        <div class="bg-carbon-800 rounded-2xl shadow-2xl max-w-lg w-full overflow-hidden border border-carbon-700/50">
            <div class="px-6 py-4 border-b border-carbon-700/50">
                <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                    <span class="text-2xl">⚠️</span> Supprimer le passage
                </h3>
            </div>

            <div class="p-6">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0 w-12 h-12 rounded-xl bg-status-danger/20 flex items-center justify-center">
                        <svg class="h-6 w-6 text-status-danger" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-300">
                            Êtes-vous sûr de vouloir supprimer le passage au checkpoint
                            @if($passageToDelete)
                            <strong class="text-white">{{ $passageToDelete->checkpoint->name ?? 'N/A' }}</strong> ?
                            @endif
                        </p>
                        <p class="mt-2 text-xs text-gray-500">Cette action sera enregistrée dans l'historique.</p>
                    </div>
                </div>
            </div>

            <div class="px-6 py-4 bg-carbon-700/30 flex justify-end gap-3">
                <x-racing.button wire:click="closeDeleteModal" variant="secondary">
                    Annuler
                </x-racing.button>
                <x-racing.button wire:click="deletePassage" variant="danger">
                    Supprimer
                </x-racing.button>
            </div>
        </div>
    </div>
    @endif
</div>
