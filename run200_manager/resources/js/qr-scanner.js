/**
 * QR Code Scanner using html5-qrcode library
 * This module provides camera-based QR code scanning functionality
 */

class QrScanner {
    constructor(containerId, options = {}) {
        this.containerId = containerId;
        this.scanner = null;
        this.isScanning = false;
        this.onScanSuccess = options.onScanSuccess || (() => {});
        this.onScanError = options.onScanError || (() => {});
        this.onCameraStart = options.onCameraStart || (() => {});
        this.onCameraStop = options.onCameraStop || (() => {});
    }

    async init() {
        // Dynamically load html5-qrcode if not already loaded
        if (typeof Html5Qrcode === 'undefined') {
            await this.loadScript('https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js');
        }

        this.scanner = new Html5Qrcode(this.containerId);
    }

    loadScript(src) {
        return new Promise((resolve, reject) => {
            const script = document.createElement('script');
            script.src = src;
            script.onload = resolve;
            script.onerror = reject;
            document.head.appendChild(script);
        });
    }

    async start(cameraId = null) {
        if (this.isScanning) {
            console.warn('Scanner is already running');
            return;
        }

        try {
            await this.init();

            const config = {
                fps: 10,
                qrbox: { width: 250, height: 250 },
                aspectRatio: 1.0,
                disableFlip: false,
            };

            const successCallback = (decodedText, decodedResult) => {
                // Play success sound/vibration
                this.playSuccessSound();
                this.onScanSuccess(decodedText, decodedResult);
            };

            const errorCallback = (errorMessage) => {
                // Ignore "QR code parse error" as it's expected when no QR is in view
                if (!errorMessage.includes('QR code parse error')) {
                    this.onScanError(errorMessage);
                }
            };

            if (cameraId) {
                await this.scanner.start(cameraId, config, successCallback, errorCallback);
            } else {
                // Use back camera by default (better for scanning)
                await this.scanner.start(
                    { facingMode: "environment" },
                    config,
                    successCallback,
                    errorCallback
                );
            }

            this.isScanning = true;
            this.onCameraStart();
        } catch (err) {
            console.error('Failed to start scanner:', err);
            this.onScanError('Impossible de démarrer la caméra: ' + err.message);
        }
    }

    async stop() {
        if (!this.isScanning || !this.scanner) {
            return;
        }

        try {
            await this.scanner.stop();
            this.isScanning = false;
            this.onCameraStop();
        } catch (err) {
            console.error('Failed to stop scanner:', err);
        }
    }

    async getCameras() {
        if (typeof Html5Qrcode === 'undefined') {
            await this.loadScript('https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js');
        }
        return await Html5Qrcode.getCameras();
    }

    playSuccessSound() {
        // Vibrate if supported
        if (navigator.vibrate) {
            navigator.vibrate(200);
        }

        // Play a short beep sound
        try {
            const audioContext = new (window.AudioContext || window.webkitAudioContext)();
            const oscillator = audioContext.createOscillator();
            const gainNode = audioContext.createGain();

            oscillator.connect(gainNode);
            gainNode.connect(audioContext.destination);

            oscillator.frequency.value = 800;
            oscillator.type = 'sine';
            gainNode.gain.value = 0.3;

            oscillator.start();
            setTimeout(() => {
                oscillator.stop();
            }, 150);
        } catch (e) {
            // Audio not available
        }
    }
}

// Alpine.js component for QR Scanner
document.addEventListener('alpine:init', () => {
    Alpine.data('qrScanner', () => ({
        scanner: null,
        isScanning: false,
        hasCamera: false,
        cameras: [],
        selectedCamera: null,
        error: null,
        lastScan: null,
        scanPaused: false,

        async init() {
            // Check camera availability
            try {
                this.cameras = await Html5Qrcode.getCameras();
                this.hasCamera = this.cameras.length > 0;

                // Select back camera by default
                const backCamera = this.cameras.find(c =>
                    c.label.toLowerCase().includes('back') ||
                    c.label.toLowerCase().includes('arrière') ||
                    c.label.toLowerCase().includes('environment')
                );
                this.selectedCamera = backCamera ? backCamera.id : (this.cameras[0]?.id || null);
            } catch (err) {
                console.error('Camera detection failed:', err);
                this.error = 'Impossible de détecter les caméras. Vérifiez les permissions.';
            }
        },

        async startScanning() {
            if (this.isScanning || !this.hasCamera) return;

            this.error = null;
            this.scanner = new QrScanner('qr-reader', {
                onScanSuccess: (decodedText) => {
                    if (this.scanPaused) return;

                    this.lastScan = decodedText;
                    this.scanPaused = true;

                    // Emit to Livewire
                    this.$wire.processToken(decodedText);

                    // Resume scanning after a delay
                    setTimeout(() => {
                        this.scanPaused = false;
                    }, 2000);
                },
                onScanError: (error) => {
                    if (error && !error.includes('parse error')) {
                        this.error = error;
                    }
                },
                onCameraStart: () => {
                    this.isScanning = true;
                },
                onCameraStop: () => {
                    this.isScanning = false;
                }
            });

            await this.scanner.start(this.selectedCamera);
        },

        async stopScanning() {
            if (this.scanner) {
                await this.scanner.stop();
            }
        },

        async switchCamera(cameraId) {
            if (this.isScanning) {
                await this.stopScanning();
            }
            this.selectedCamera = cameraId;
            await this.startScanning();
        }
    }));
});

// Export for global use
window.QrScanner = QrScanner;
