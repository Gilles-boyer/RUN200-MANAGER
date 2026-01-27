<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

use App\Domain\Exceptions\InvalidQrCodeException;
use App\Models\RaceRegistration;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Service de sécurité pour les scans QR codes.
 * Gère le rate limiting, la détection d'abus, et l'alerting.
 */
class QrScanSecurityService
{
    /**
     * Nombre maximum de scans par token par minute.
     */
    public const MAX_SCANS_PER_TOKEN_PER_MINUTE = 3;

    /**
     * Nombre maximum de tokens différents scannés par scanner par minute.
     */
    public const MAX_DIFFERENT_TOKENS_PER_SCANNER_PER_MINUTE = 30;

    /**
     * Durée de blocage en minutes après détection d'abus.
     */
    public const BLOCK_DURATION_MINUTES = 15;

    /**
     * Nombre de scans suspects avant alerte admin.
     */
    public const SUSPICIOUS_THRESHOLD = 5;

    /**
     * Vérifie les limites de rate avant un scan.
     *
     * @throws InvalidQrCodeException
     */
    public function checkRateLimits(string $token, User $scanner): void
    {
        // 1. Vérifier si le scanner est bloqué
        if ($this->isScannerBlocked($scanner)) {
            $this->logSuspiciousActivity('blocked_scanner_attempt', $scanner, $token);
            throw new InvalidQrCodeException('Scanner temporairement bloqué suite à une activité suspecte');
        }

        // 2. Vérifier le rate limit par token
        $tokenKey = $this->buildKey('token_scans', $this->hashToken($token));
        $tokenScans = (int) Cache::get($tokenKey, 0);

        if ($tokenScans >= self::MAX_SCANS_PER_TOKEN_PER_MINUTE) {
            $this->incrementSuspiciousCount($scanner);
            $this->logSuspiciousActivity('token_rate_limit', $scanner, $token);

            throw InvalidQrCodeException::alreadyUsed('Trop de tentatives de scan pour ce QR code');
        }

        // 3. Vérifier le rate limit par scanner
        $scannerKey = $this->buildKey('scanner_count', $scanner->id);
        $scannerCount = (int) Cache::get($scannerKey, 0);

        if ($scannerCount >= self::MAX_DIFFERENT_TOKENS_PER_SCANNER_PER_MINUTE) {
            $this->incrementSuspiciousCount($scanner);
            $this->logSuspiciousActivity('scanner_rate_limit', $scanner, $token);

            throw new InvalidQrCodeException('Limite de scans atteinte. Veuillez patienter.');
        }
    }

    /**
     * Enregistre un scan réussi pour les compteurs de rate limiting.
     */
    public function recordSuccessfulScan(string $token, User $scanner): void
    {
        // Incrémenter le compteur de scans pour ce token
        $tokenKey = $this->buildKey('token_scans', $this->hashToken($token));
        Cache::increment($tokenKey);
        Cache::put($tokenKey, Cache::get($tokenKey), now()->addMinutes(1));

        // Incrémenter le compteur de scans différents pour ce scanner
        $scannerKey = $this->buildKey('scanner_count', $scanner->id);
        Cache::increment($scannerKey);
        Cache::put($scannerKey, Cache::get($scannerKey), now()->addMinutes(1));

        // Reset le compteur de tentatives suspectes (scan réussi = comportement normal)
        $this->resetSuspiciousCount($scanner);
    }

    /**
     * Vérifie si un token a déjà été utilisé pour un checkpoint donné.
     */
    public function checkDuplicateScan(RaceRegistration $registration, string $checkpointCode): void
    {
        $exists = $registration->passages()
            ->whereHas('checkpoint', fn ($q) => $q->where('code', $checkpointCode))
            ->exists();

        if ($exists) {
            throw InvalidQrCodeException::alreadyUsed($checkpointCode);
        }
    }

    /**
     * Détecte et signale une activité suspecte.
     */
    public function detectSuspiciousActivity(string $token, User $scanner, ?RaceRegistration $registration): void
    {
        $suspiciousPatterns = [];

        // 1. Vérifier les scans rapides successifs
        $recentScansKey = $this->buildKey('recent_scans', $scanner->id);
        $recentScans = Cache::get($recentScansKey, []);
        $now = now()->timestamp;

        // Nettoyer les scans > 5 min
        $recentScans = array_filter($recentScans, fn ($ts) => ($now - $ts) < 300);
        $recentScans[] = $now;
        Cache::put($recentScansKey, $recentScans, now()->addMinutes(5));

        if (count($recentScans) > 10) {
            $suspiciousPatterns[] = 'high_frequency_scanning';
        }

        // 2. Vérifier les scans de tokens invalides répétés
        $invalidTokensKey = $this->buildKey('invalid_tokens', $scanner->id);
        $invalidCount = (int) Cache::get($invalidTokensKey, 0);

        if ($registration === null) {
            Cache::put($invalidTokensKey, $invalidCount + 1, now()->addMinutes(10));

            if ($invalidCount >= 3) {
                $suspiciousPatterns[] = 'repeated_invalid_tokens';
            }
        }

        // 3. Si des patterns suspects sont détectés, alerter
        if (! empty($suspiciousPatterns)) {
            $this->alertAdmins($scanner, $suspiciousPatterns, $token);
        }
    }

    /**
     * Vérifie si un scanner est bloqué.
     */
    public function isScannerBlocked(User $scanner): bool
    {
        $blockedKey = $this->buildKey('blocked', $scanner->id);

        return Cache::has($blockedKey);
    }

    /**
     * Bloque un scanner temporairement.
     */
    public function blockScanner(User $scanner, string $reason): void
    {
        $blockedKey = $this->buildKey('blocked', $scanner->id);
        Cache::put($blockedKey, $reason, now()->addMinutes(self::BLOCK_DURATION_MINUTES));

        Log::warning('Scanner blocked', [
            'user_id' => $scanner->id,
            'user_email' => $scanner->email,
            'reason' => $reason,
            'duration_minutes' => self::BLOCK_DURATION_MINUTES,
        ]);

        $this->alertAdmins($scanner, ['scanner_blocked'], null, $reason);
    }

    /**
     * Débloque un scanner manuellement (pour admin).
     */
    public function unblockScanner(User $scanner): void
    {
        $blockedKey = $this->buildKey('blocked', $scanner->id);
        Cache::forget($blockedKey);

        $this->resetSuspiciousCount($scanner);

        Log::info('Scanner unblocked', [
            'user_id' => $scanner->id,
            'user_email' => $scanner->email,
        ]);
    }

    /**
     * Invalide manuellement un token QR (pour admin).
     */
    public function invalidateToken(string $token): void
    {
        $invalidKey = $this->buildKey('invalidated', $this->hashToken($token));
        Cache::put($invalidKey, true, now()->addDays(7));

        Log::info('QR token manually invalidated', [
            'token_hash' => $this->hashToken($token),
        ]);
    }

    /**
     * Vérifie si un token a été manuellement invalidé.
     */
    public function isTokenInvalidated(string $token): bool
    {
        $invalidKey = $this->buildKey('invalidated', $this->hashToken($token));

        return Cache::has($invalidKey);
    }

    /**
     * Récupère les statistiques de sécurité pour le dashboard admin.
     */
    public function getSecurityStats(): array
    {
        return [
            'blocked_scanners' => $this->countBlockedScanners(),
            'suspicious_activities_24h' => $this->countSuspiciousActivities(24),
            'invalidated_tokens' => $this->countInvalidatedTokens(),
        ];
    }

    /**
     * Incrémente le compteur de tentatives suspectes.
     */
    private function incrementSuspiciousCount(User $scanner): void
    {
        $key = $this->buildKey('suspicious', $scanner->id);
        $count = Cache::increment($key);
        Cache::put($key, $count, now()->addMinutes(30));

        if ($count >= self::SUSPICIOUS_THRESHOLD) {
            $this->blockScanner($scanner, 'Too many suspicious attempts');
        }
    }

    /**
     * Reset le compteur de tentatives suspectes.
     */
    private function resetSuspiciousCount(User $scanner): void
    {
        $key = $this->buildKey('suspicious', $scanner->id);
        Cache::forget($key);
    }

    /**
     * Log une activité suspecte.
     */
    private function logSuspiciousActivity(string $type, User $scanner, ?string $token): void
    {
        Log::warning('Suspicious QR scan activity detected', [
            'type' => $type,
            'user_id' => $scanner->id,
            'user_email' => $scanner->email,
            'token_hash' => $token ? $this->hashToken($token) : null,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        // Stocker pour statistiques
        $statsKey = $this->buildKey('suspicious_log', date('Y-m-d'));
        $logs = Cache::get($statsKey, []);
        $logs[] = [
            'type' => $type,
            'user_id' => $scanner->id,
            'timestamp' => now()->toIso8601String(),
        ];
        Cache::put($statsKey, $logs, now()->addDays(7));
    }

    /**
     * Alerte les administrateurs d'une activité suspecte.
     */
    private function alertAdmins(User $scanner, array $patterns, ?string $token, ?string $additionalInfo = null): void
    {
        Log::channel('security')->alert('QR Security Alert', [
            'scanner_id' => $scanner->id,
            'scanner_email' => $scanner->email,
            'patterns' => $patterns,
            'token_hash' => $token ? $this->hashToken($token) : null,
            'additional_info' => $additionalInfo,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        // TODO: Envoyer une notification aux admins (email/Slack)
        // AdminNotification::send('qr_security_alert', [...]);
    }

    /**
     * Construit une clé de cache.
     */
    private function buildKey(string $type, int|string $identifier): string
    {
        return "qr_security:{$type}:{$identifier}";
    }

    /**
     * Hash un token pour le stockage sécurisé.
     */
    private function hashToken(string $token): string
    {
        return hash('sha256', $token);
    }

    /**
     * Compte le nombre de scanners bloqués (approximatif).
     */
    private function countBlockedScanners(): int
    {
        // Note: En production, utiliser Redis SCAN pour compter les clés matching
        // Pour l'instant, on retourne une estimation
        return 0;
    }

    /**
     * Compte les activités suspectes sur les dernières heures.
     */
    private function countSuspiciousActivities(int $hours): int
    {
        $count = 0;
        $now = now();

        for ($i = 0; $i < $hours / 24 + 1; $i++) {
            $date = $now->copy()->subDays($i)->format('Y-m-d');
            $logs = Cache::get($this->buildKey('suspicious_log', $date), []);
            $count += count($logs);
        }

        return $count;
    }

    /**
     * Compte les tokens invalidés.
     */
    private function countInvalidatedTokens(): int
    {
        // Note: En production, utiliser Redis SCAN
        return 0;
    }
}
