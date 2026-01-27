<div class="max-w-lg mx-auto">
    <!-- Flash Messages -->
    @if (session()->has('success'))
        <x-racing.alert type="success" class="mb-4">
            {{ session('success') }}
        </x-racing.alert>
    @endif

    @if (session()->has('error'))
        <x-racing.alert type="danger" class="mb-4">
            {{ session('error') }}
        </x-racing.alert>
    @endif

    <!-- E-Card - Racing Design -->
    <div class="bg-carbon-800 shadow-2xl rounded-2xl overflow-hidden border border-carbon-700/50">
        <!-- Header with Racing Gradient -->
        <div class="bg-racing-gradient px-6 py-5 relative overflow-hidden">
            <!-- Checkered pattern overlay -->
            <div class="absolute inset-0 opacity-10">
                <div class="absolute inset-0" style="background-image: repeating-conic-gradient(#000 0% 25%, transparent 0% 50%); background-size: 20px 20px;"></div>
            </div>
            <div class="relative">
                <h1 class="text-2xl font-black text-white text-center tracking-wider">🏁 E-CARTE PILOTE</h1>
                <p class="text-white/80 text-center text-sm mt-1 font-medium">{{ $registration->race->name }}</p>
            </div>
        </div>

        <!-- QR Code Section -->
        <div class="px-6 py-8 flex flex-col items-center bg-gradient-to-b from-carbon-800 to-carbon-850">
            <div class="bg-white p-5 rounded-2xl shadow-xl ring-4 ring-carbon-700/50">
                <img src="{{ $qrCodeDataUri }}" alt="QR Code" class="w-56 h-56 sm:w-64 sm:h-64">
            </div>

            <button wire:click="regenerateQrCode"
                    class="mt-5 text-sm text-gray-500 hover:text-racing-red-400 flex items-center gap-2 transition-colors duration-200 font-medium">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Régénérer le QR code
            </button>
        </div>

        <!-- Pilot Info Section -->
        <div class="px-6 pb-6 space-y-4">
            <!-- Pilot Card -->
            <div class="flex items-center justify-between p-4 bg-carbon-700/30 rounded-xl border border-carbon-600/50">
                <div class="flex-1">
                    <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold">👤 Pilote</p>
                    <p class="text-xl font-bold text-white mt-1">
                        {{ $registration->pilot->last_name }} {{ $registration->pilot->first_name }}
                    </p>
                    <p class="text-sm text-gray-400 mt-0.5">
                        Licence: <span class="text-checkered-yellow-500 font-mono font-semibold">{{ $registration->pilot->license_number }}</span>
                    </p>
                </div>
                @if($registration->pilot->photo_path)
                    <img src="{{ Storage::url($registration->pilot->photo_path) }}"
                         alt="Photo pilote" class="w-18 h-18 rounded-xl object-cover ring-2 ring-racing-red-500/50 ml-4">
                @else
                    <div class="w-18 h-18 rounded-xl bg-carbon-600 flex items-center justify-center ring-2 ring-carbon-500 ml-4">
                        <svg class="w-8 h-8 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                @endif
            </div>

            <!-- Car Card -->
            <div class="p-4 bg-carbon-700/30 rounded-xl border border-carbon-600/50">
                <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold">🚗 Voiture</p>
                <div class="flex items-center gap-4 mt-2">
                    <span class="text-4xl font-black text-racing-red-500">
                        #{{ $registration->car->race_number }}
                    </span>
                    <div>
                        <p class="font-bold text-white text-lg">
                            {{ $registration->car->make }} {{ $registration->car->model }}
                        </p>
                        <p class="text-sm text-gray-400">
                            {{ $registration->car->category->name ?? 'N/A' }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Paddock Badge -->
            @if($registration->paddock)
                <div class="p-5 bg-status-info/10 border border-status-info/20 rounded-xl text-center">
                    <p class="text-xs text-status-info uppercase tracking-wide font-semibold">📍 Paddock assigné</p>
                    <p class="text-3xl font-black text-status-info mt-2">
                        {{ $registration->paddock }}
                    </p>
                </div>
            @endif

            <!-- Status Badge -->
            <div class="p-4 bg-carbon-700/30 rounded-xl border border-carbon-600/50">
                <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold mb-2">📋 Statut</p>
                <span class="inline-flex items-center px-4 py-2 rounded-xl text-sm font-bold
                    @if(in_array($registration->status, ['ACCEPTED', 'ADMIN_CHECKED', 'TECH_CHECKED_OK', 'ENTRY_SCANNED', 'BRACELET_GIVEN']))
                        bg-status-success/20 text-status-success border border-status-success/30
                    @else
                        bg-status-warning/20 text-status-warning border border-status-warning/30
                    @endif">
                    {{ $registration->status }}
                </span>
            </div>

            <!-- Checkpoints Progress -->
            <div class="p-4 bg-carbon-700/30 rounded-xl border border-carbon-600/50">
                <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold mb-4">✅ Checkpoints</p>
                <div class="space-y-3">
                    @php
                        $checkpoints = [
                            'ADMIN_CHECK' => ['label' => 'Vérification administrative', 'icon' => '📋'],
                            'TECH_CHECK' => ['label' => 'Vérification technique', 'icon' => '🔧'],
                            'ENTRY' => ['label' => 'Entrée pilote/voiture', 'icon' => '🚪'],
                            'BRACELET' => ['label' => 'Remise bracelet', 'icon' => '🏷️'],
                        ];
                    @endphp

                    @foreach($checkpoints as $code => $info)
                        <div class="flex items-center gap-3 p-2 rounded-lg transition-colors duration-200
                            @if(in_array($code, $passedCheckpoints)) bg-status-success/10 @endif">
                            @if(in_array($code, $passedCheckpoints))
                                <div class="w-8 h-8 rounded-lg bg-status-success/20 border border-status-success/30 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-status-success" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <span class="text-sm text-status-success font-medium">{{ $info['icon'] }} {{ $info['label'] }}</span>
                            @else
                                <div class="w-8 h-8 rounded-lg bg-carbon-600/50 border border-carbon-500/50 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm0-2a6 6 0 100-12 6 6 0 000 12z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <span class="text-sm text-gray-500">{{ $info['icon'] }} {{ $info['label'] }}</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="px-6 py-4 bg-carbon-700/30 border-t border-carbon-700/50">
            <p class="text-center text-xs text-gray-500 font-medium">
                📱 Présentez ce QR code aux différents contrôles
            </p>
        </div>
    </div>

    <!-- Back Button -->
    <div class="mt-8 text-center">
        <a href="{{ route('pilot.dashboard') }}"
           class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-racing-red-400 transition-colors duration-200 font-medium">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Retour au tableau de bord
        </a>
    </div>
</div>
