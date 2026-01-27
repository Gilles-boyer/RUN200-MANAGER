<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecureHeaders
{
    /**
     * Headers de sécurité à appliquer sur toutes les réponses.
     */
    private array $headers = [
        // Empêche le clickjacking
        'X-Frame-Options' => 'SAMEORIGIN',

        // Empêche le MIME-sniffing
        'X-Content-Type-Options' => 'nosniff',

        // Contrôle les informations de referrer
        'Referrer-Policy' => 'strict-origin-when-cross-origin',

        // Permissions-Policy : autorise la caméra pour le scanner QR
        'Permissions-Policy' => 'camera=(self), microphone=(), geolocation=()',

        // Protection XSS (legacy, mais utile pour vieux navigateurs)
        'X-XSS-Protection' => '1; mode=block',
    ];

    /**
     * Content Security Policy - adapté pour Laravel + Livewire + Tailwind + QR Scanner.
     */
    private function getCsp(): string
    {
        $directives = [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://unpkg.com", // Livewire + html5-qrcode CDN
            "style-src 'self' 'unsafe-inline' https://fonts.bunny.net", // Tailwind inline styles + Bunny Fonts CSS
            "img-src 'self' data: https: blob:", // blob: pour le flux caméra
            "font-src 'self' data: https://fonts.bunny.net", // Bunny Fonts
            "connect-src 'self' https://unpkg.com", // Pour le fetch du script QR
            "media-src 'self' blob:", // Pour le flux vidéo de la caméra
            "frame-ancestors 'self'",
            "form-action 'self'",
            "base-uri 'self'",
        ];

        return implode('; ', $directives);
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Ajouter les headers de sécurité
        foreach ($this->headers as $header => $value) {
            $response->headers->set($header, $value);
        }

        // Ajouter CSP
        $response->headers->set('Content-Security-Policy', $this->getCsp());

        // HSTS uniquement en production HTTPS
        if (app()->isProduction() && $request->secure()) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains'
            );
        }

        return $response;
    }
}
