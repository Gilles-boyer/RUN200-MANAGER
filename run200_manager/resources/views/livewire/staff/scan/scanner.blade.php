<div class="max-w-2xl mx-auto" x-data="qrScannerComponent()" x-init="initScanner()">
    <!-- Racing Header -->
    <div class="mb-8">
        <div class="flex items-center gap-3 mb-2">
            <div class="w-10 h-10 bg-racing-gradient rounded-xl flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                </svg>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-white">{{ $checkpoint->name }}</h1>
                <p class="text-sm text-gray-400">Scannez le QR code du pilote pour valider ce checkpoint</p>
            </div>
        </div>
    </div>

    <!-- Scan Mode Selector - Racing Style -->
    <div class="mb-6">
        <div class="flex rounded-xl bg-carbon-800 border border-carbon-700/50 p-1">
            <button wire:click="setScanMode('camera')"
                    class="flex-1 px-4 py-3 text-sm font-semibold rounded-lg transition-all duration-200 flex items-center justify-center gap-2
                    {{ $scanMode === 'camera' ? 'bg-racing-gradient text-white shadow-lg shadow-racing-red-500/25' : 'text-gray-400 hover:text-white hover:bg-carbon-700/50' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Caméra
            </button>
            <button wire:click="setScanMode('manual')"
                    class="flex-1 px-4 py-3 text-sm font-semibold rounded-lg transition-all duration-200 flex items-center justify-center gap-2
                    {{ $scanMode === 'manual' ? 'bg-racing-gradient text-white shadow-lg shadow-racing-red-500/25' : 'text-gray-400 hover:text-white hover:bg-carbon-700/50' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Saisie manuelle
            </button>
        </div>
    </div>

    <!-- Camera Scanner -->
    @if($scanMode === 'camera')
        <x-racing.card class="mb-6">
            <!-- Camera Permission Request -->
            <template x-if="!hasCamera && !cameraError">
                <div class="text-center py-12">
                    <div class="w-20 h-20 mx-auto bg-carbon-700/50 rounded-2xl flex items-center justify-center mb-6">
                        <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <p class="text-gray-400 mb-6">Autorisation caméra requise pour scanner</p>
                    <x-racing.button @click="requestCameraAccess()" class="px-8">
                        🎥 Activer la caméra
                    </x-racing.button>
                </div>
            </template>

            <!-- Camera Error -->
            <template x-if="cameraError">
                <div class="text-center py-12">
                    <div class="w-20 h-20 mx-auto bg-status-danger/20 rounded-2xl flex items-center justify-center mb-6">
                        <svg class="w-10 h-10 text-status-danger" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <p class="text-status-danger font-semibold mb-2">Erreur caméra</p>
                    <p class="text-gray-400 mb-6 text-sm" x-text="cameraError"></p>
                    <x-racing.button variant="secondary" @click="requestCameraAccess()">
                        Réessayer
                    </x-racing.button>
                </div>
            </template>

            <!-- Camera View -->
            <template x-if="hasCamera">
                <div>
                    <!-- Camera selector if multiple cameras -->
                    <template x-if="cameras.length > 1">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-300 mb-2">
                                Sélectionner une caméra
                            </label>
                            <select x-model="selectedCamera"
                                    @change="switchCamera()"
                                    class="w-full rounded-xl bg-carbon-700 border-carbon-600 text-white shadow-sm focus:ring-racing-red-500 focus:border-racing-red-500">
                                <template x-for="camera in cameras" :key="camera.id">
                                    <option :value="camera.id" x-text="camera.label || 'Caméra ' + camera.id"></option>
                                </template>
                            </select>
                        </div>
                    </template>

                    <!-- QR Scanner Container -->
                    <div class="relative">
                        <div id="qr-reader" class="w-full rounded-xl overflow-hidden border-2 border-carbon-600" style="min-height: 300px;"></div>

                        <!-- Scanning overlay -->
                        <div x-show="isScanning" class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-carbon-950/80 to-transparent p-4">
                            <div class="flex items-center justify-center gap-2 text-white">
                                <svg class="animate-pulse w-5 h-5 text-racing-red-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h3a1 1 0 011 1v3a1 1 0 01-1 1H4a1 1 0 01-1-1V4zm2 2V5h1v1H5zM3 13a1 1 0 011-1h3a1 1 0 011 1v3a1 1 0 01-1 1H4a1 1 0 01-1-1v-3zm2 2v-1h1v1H5zM13 3a1 1 0 00-1 1v3a1 1 0 001 1h3a1 1 0 001-1V4a1 1 0 00-1-1h-3zm1 2v1h1V5h-1z" clip-rule="evenodd"/>
                                    <path d="M11 4a1 1 0 10-2 0v1a1 1 0 002 0V4zM10 7a1 1 0 011 1v1h2a1 1 0 110 2h-3a1 1 0 01-1-1V8a1 1 0 011-1zM16 9a1 1 0 100 2 1 1 0 000-2zM9 13a1 1 0 011-1h1a1 1 0 110 2v2a1 1 0 11-2 0v-3zM7 11a1 1 0 100-2H4a1 1 0 100 2h3zM17 13a1 1 0 01-1 1h-2a1 1 0 110-2h2a1 1 0 011 1zM16 17a1 1 0 100-2h-3a1 1 0 100 2h3z"/>
                                </svg>
                                <span class="text-sm font-medium">Recherche de QR code...</span>
                            </div>
                        </div>
                    </div>

                    <!-- Scanner Controls -->
                    <div class="mt-6 flex justify-center gap-4">
                        <button x-show="!isScanning"
                                @click="startScanning()"
                                class="px-6 py-3 bg-status-success text-white rounded-xl font-semibold hover:bg-status-success/80 transition-all duration-200 flex items-center gap-2 shadow-lg shadow-status-success/25">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Démarrer le scan
                        </button>
                        <button x-show="isScanning"
                                @click="stopScanning()"
                                class="px-6 py-3 bg-status-danger text-white rounded-xl font-semibold hover:bg-status-danger/80 transition-all duration-200 flex items-center gap-2 shadow-lg shadow-status-danger/25">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"/>
                            </svg>
                            Arrêter le scan
                        </button>
                    </div>

                    <!-- Last scanned info -->
                    <template x-if="lastScannedToken">
                        <div class="mt-4 p-4 bg-status-info/10 border border-status-info/20 rounded-xl">
                            <p class="text-xs text-status-info uppercase tracking-wide font-semibold">Dernier scan</p>
                            <p class="text-sm text-gray-300 font-mono truncate mt-1" x-text="lastScannedToken"></p>
                        </div>
                    </template>
                </div>
            </template>
        </x-racing.card>
    @endif

    <!-- Manual Input -->
    @if($scanMode === 'manual')
        <x-racing.card class="mb-6">
            <div class="space-y-4">
                <div>
                    <label for="token" class="block text-sm font-medium text-gray-300 mb-2">
                        Token QR (scanner externe ou coller)
                    </label>
                    <div class="flex gap-3">
                        <input wire:model="token"
                               wire:keydown.enter="processToken"
                               type="text"
                               id="token"
                               autofocus
                               class="flex-1 rounded-xl bg-carbon-700 border-carbon-600 text-white placeholder-gray-500 shadow-sm focus:ring-racing-red-500 focus:border-racing-red-500"
                               placeholder="Scannez ou collez le token QR...">
                        <x-racing.button wire:click="processToken">
                            Vérifier
                        </x-racing.button>
                    </div>
                </div>

                <p class="text-sm text-gray-500 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Utilisez un scanner QR externe (type pistolet) ou collez directement le token
                </p>
            </div>
        </x-racing.card>
    @endif

    <!-- Error Message -->
    @if($errorMessage)
        <x-racing.alert type="danger" class="mb-6">
            <p>{{ $errorMessage }}</p>
            <button wire:click="resetScanner" class="mt-2 text-sm text-status-danger hover:underline font-medium">
                🔄 Réessayer
            </button>
        </x-racing.alert>
    @endif

    <!-- Success Message -->
    @if($showSuccess)
        <x-racing.alert type="success" class="mb-6">
            <p class="font-semibold">{{ $scanResult }}</p>
            <button wire:click="resetScanner" @click="resumeScanning()" class="mt-2 text-sm text-status-success hover:underline font-medium">
                📷 Scanner un autre pilote
            </button>
        </x-racing.alert>
    @endif

    <!-- Registration Info -->
    @if($registrationInfo)
        <x-racing.card noPadding>
            <!-- Header with Status -->
            <div class="px-6 py-4 border-b border-carbon-700/50 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-white">Informations inscription</h2>
                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-semibold
                    @if(in_array($registrationInfo['status'], ['ACCEPTED', 'ADMIN_CHECKED', 'TECH_CHECKED_OK', 'ENTRY_SCANNED', 'BRACELET_GIVEN']))
                        bg-status-success/20 text-status-success border border-status-success/30
                    @elseif($registrationInfo['status'] === 'REFUSED' || $registrationInfo['status'] === 'TECH_CHECKED_FAIL')
                        bg-status-danger/20 text-status-danger border border-status-danger/30
                    @else
                        bg-status-warning/20 text-status-warning border border-status-warning/30
                    @endif">
                    {{ $registrationInfo['status'] }}
                </span>
            </div>

            <div class="p-6 space-y-4">
                <!-- Pilot Info -->
                <div class="flex items-center gap-4 p-4 bg-carbon-700/30 rounded-xl border border-carbon-600/50">
                    <div class="flex-shrink-0">
                        @if($registrationInfo['pilot']->photo_path)
                            <img src="{{ Storage::url($registrationInfo['pilot']->photo_path) }}"
                                 alt="Photo pilote" class="w-16 h-16 rounded-full object-cover ring-2 ring-racing-red-500/50">
                        @else
                            <div class="w-16 h-16 rounded-full bg-carbon-600 flex items-center justify-center ring-2 ring-carbon-500">
                                <svg class="w-8 h-8 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        @endif
                    </div>
                    <div class="flex-1">
                        <p class="text-lg font-bold text-white">
                            {{ $registrationInfo['pilot']->last_name }} {{ $registrationInfo['pilot']->first_name }}
                        </p>
                        <p class="text-sm text-gray-400">
                            Licence: <span class="text-checkered-yellow-500 font-mono">{{ $registrationInfo['pilot']->license_number }}</span>
                        </p>
                    </div>
                </div>

                <!-- Car Info -->
                <div class="p-4 bg-carbon-700/30 rounded-xl border border-carbon-600/50 flex items-center gap-4">
                    <span class="text-3xl font-black text-racing-red-500">
                        #{{ $registrationInfo['car']->race_number }}
                    </span>
                    <div>
                        <p class="font-semibold text-white">
                            {{ $registrationInfo['car']->make }} {{ $registrationInfo['car']->model }}
                        </p>
                        <p class="text-sm text-gray-400">
                            {{ $registrationInfo['car']->category->name ?? 'N/A' }}
                        </p>
                    </div>
                </div>

                <!-- Race Info -->
                <div class="p-4 bg-carbon-700/30 rounded-xl border border-carbon-600/50">
                    <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold">Course</p>
                    <p class="font-semibold text-white mt-1">{{ $registrationInfo['race']->name }}</p>
                    <p class="text-sm text-gray-400">{{ $registrationInfo['race']->race_date->format('d/m/Y') }}</p>
                </div>

                <!-- Paddock -->
                @if($registrationInfo['paddock'])
                    <div class="p-4 bg-status-info/10 border border-status-info/20 rounded-xl text-center">
                        <p class="text-xs text-status-info uppercase tracking-wide font-semibold">Paddock</p>
                        <p class="text-2xl font-black text-status-info mt-1">{{ $registrationInfo['paddock'] }}</p>
                    </div>
                @endif

                <!-- Checkpoints Progress -->
                <div class="p-4 bg-carbon-700/30 rounded-xl border border-carbon-600/50">
                    <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold mb-3">Checkpoints passés</p>
                    <div class="flex flex-wrap gap-2">
                        @php
                            $allCheckpoints = [
                                'ADMIN_CHECK' => 'Admin',
                                'TECH_CHECK' => 'Tech',
                                'ENTRY' => 'Entrée',
                                'BRACELET' => 'Bracelet',
                            ];
                        @endphp

                        @foreach($allCheckpoints as $code => $label)
                            <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold
                                @if(in_array($code, $registrationInfo['passed_checkpoints']))
                                    bg-status-success/20 text-status-success border border-status-success/30
                                @else
                                    bg-carbon-600/50 text-gray-500 border border-carbon-500/50
                                @endif">
                                @if(in_array($code, $registrationInfo['passed_checkpoints']))
                                    ✓
                                @endif
                                {{ $label }}
                            </span>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Action Button -->
            @if(!$showSuccess && !in_array($checkpointCode, $registrationInfo['passed_checkpoints']))
                <div class="px-6 py-4 bg-carbon-700/30 border-t border-carbon-700/50">
                    <button wire:click="confirmScan"
                            class="w-full px-4 py-4 bg-status-success text-white rounded-xl font-bold hover:bg-status-success/80 transition-all duration-200 flex items-center justify-center gap-2 shadow-lg shadow-status-success/25 text-lg">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        Valider {{ $checkpoint->name }}
                    </button>
                </div>
            @elseif(in_array($checkpointCode, $registrationInfo['passed_checkpoints']) && !$showSuccess)
                <div class="px-6 py-4 bg-status-warning/10 border-t border-status-warning/20">
                    <p class="text-center text-status-warning font-semibold">
                        ⚠️ Ce checkpoint a déjà été validé pour cette inscription
                    </p>
                </div>
            @endif
        </x-racing.card>
    @endif

    <!-- Back Link -->
    <div class="mt-8 text-center">
        <a href="{{ route('staff.dashboard') }}" class="text-sm text-gray-500 hover:text-racing-red-400 transition-colors duration-200">
            ← Retour au tableau de bord
        </a>
    </div>
</div>

@script
<script>
    // Load html5-qrcode library and define Alpine component
    (function() {
        // Check if library is already loaded
        if (typeof Html5Qrcode === 'undefined') {
            const script = document.createElement('script');
            script.src = 'https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js';
            script.async = false;
            document.head.appendChild(script);
        }
    })();

    Alpine.data('qrScannerComponent', () => ({
        scanner: null,
        isScanning: false,
        hasCamera: false,
        cameras: [],
        selectedCamera: null,
        cameraError: null,
        lastScannedToken: null,
        scanPaused: false,
        libraryLoaded: false,

        async initScanner() {
            // Wait for library to load with timeout
            let attempts = 0;
            while (typeof Html5Qrcode === 'undefined' && attempts < 50) {
                await new Promise(resolve => setTimeout(resolve, 100));
                attempts++;
            }

            if (typeof Html5Qrcode === 'undefined') {
                this.cameraError = 'Impossible de charger la librairie de scan QR. Rechargez la page.';
                return;
            }

            this.libraryLoaded = true;
            console.log('QR Scanner library loaded successfully');
        },

        async requestCameraAccess() {
            this.cameraError = null;

            // Make sure library is loaded
            if (!this.libraryLoaded) {
                await this.initScanner();
            }

            if (!this.libraryLoaded) {
                return;
            }

            try {
                // Request camera permission
                const stream = await navigator.mediaDevices.getUserMedia({ video: true });
                stream.getTracks().forEach(track => track.stop());

                // Get available cameras
                this.cameras = await Html5Qrcode.getCameras();

                if (this.cameras.length === 0) {
                    this.cameraError = 'Aucune caméra détectée sur cet appareil.';
                    return;
                }

                this.hasCamera = true;

                // Prefer back camera
                const backCamera = this.cameras.find(c =>
                    c.label.toLowerCase().includes('back') ||
                    c.label.toLowerCase().includes('arrière') ||
                    c.label.toLowerCase().includes('rear')
                );
                this.selectedCamera = backCamera ? backCamera.id : this.cameras[0].id;

                // Auto-start scanning
                this.$nextTick(() => {
                    this.startScanning();
                });

            } catch (err) {
                console.error('Camera access error:', err);
                if (err.name === 'NotAllowedError') {
                    this.cameraError = 'Permission refusée. Autorisez l\'accès à la caméra dans les paramètres de votre navigateur.';
                } else if (err.name === 'NotFoundError') {
                    this.cameraError = 'Aucune caméra trouvée sur cet appareil.';
                } else {
                    this.cameraError = 'Erreur: ' + err.message;
                }
            }
        },

        async startScanning() {
            if (this.isScanning) return;

            try {
                this.scanner = new Html5Qrcode('qr-reader');

                const config = {
                    fps: 10,
                    qrbox: { width: 250, height: 250 },
                    aspectRatio: 1.0
                };

                await this.scanner.start(
                    this.selectedCamera,
                    config,
                    (decodedText) => {
                        if (this.scanPaused) return;

                        this.lastScannedToken = decodedText;
                        this.scanPaused = true;

                        // Play success feedback
                        this.playSuccessFeedback();

                        // Send to Livewire
                        $wire.processToken(decodedText);

                        // Pause scanning briefly
                        setTimeout(() => {
                            this.scanPaused = false;
                        }, 3000);
                    },
                    (errorMessage) => {
                        // Ignore parse errors (normal when no QR in view)
                    }
                );

                this.isScanning = true;

            } catch (err) {
                console.error('Start scanning error:', err);
                this.cameraError = 'Impossible de démarrer le scanner: ' + err.message;
            }
        },

        async stopScanning() {
            if (!this.isScanning || !this.scanner) return;

            try {
                await this.scanner.stop();
                this.isScanning = false;
            } catch (err) {
                console.error('Stop scanning error:', err);
            }
        },

        async switchCamera() {
            if (this.isScanning) {
                await this.stopScanning();
            }
            await this.startScanning();
        },

        resumeScanning() {
            this.scanPaused = false;
            this.lastScannedToken = null;
        },

        playSuccessFeedback() {
            // Vibration
            if (navigator.vibrate) {
                navigator.vibrate([100, 50, 100]);
            }

            // Sound
            try {
                const audioContext = new (window.AudioContext || window.webkitAudioContext)();
                const oscillator = audioContext.createOscillator();
                const gainNode = audioContext.createGain();

                oscillator.connect(gainNode);
                gainNode.connect(audioContext.destination);

                oscillator.frequency.value = 880;
                oscillator.type = 'sine';
                gainNode.gain.value = 0.3;

                oscillator.start();
                setTimeout(() => oscillator.stop(), 150);
            } catch (e) {}
        }
    }));
</script>
@endscript
