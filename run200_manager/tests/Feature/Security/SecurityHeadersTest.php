<?php

declare(strict_types=1);

namespace Tests\Feature\Security;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    use RefreshDatabase;

    public function test_response_has_x_frame_options_header(): void
    {
        $response = $this->get('/');

        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
    }

    public function test_response_has_x_content_type_options_header(): void
    {
        $response = $this->get('/');

        $response->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    public function test_response_has_referrer_policy_header(): void
    {
        $response = $this->get('/');

        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    public function test_response_has_permissions_policy_header(): void
    {
        $response = $this->get('/');

        // Camera is allowed for 'self' to enable QR code scanning functionality
        $response->assertHeader('Permissions-Policy', 'camera=(self), microphone=(), geolocation=()');
    }

    public function test_response_has_xss_protection_header(): void
    {
        $response = $this->get('/');

        $response->assertHeader('X-XSS-Protection', '1; mode=block');
    }

    public function test_response_has_content_security_policy_header(): void
    {
        $response = $this->get('/');

        $response->assertHeader('Content-Security-Policy');

        $csp = $response->headers->get('Content-Security-Policy');

        $this->assertStringContainsString("default-src 'self'", $csp);
        $this->assertStringContainsString("script-src 'self'", $csp);
        $this->assertStringContainsString("frame-ancestors 'self'", $csp);
    }

    public function test_hsts_not_applied_in_testing_environment(): void
    {
        $response = $this->get('/');

        // HSTS should only be applied in production with HTTPS
        $this->assertNull($response->headers->get('Strict-Transport-Security'));
    }

    public function test_all_security_headers_present_on_authenticated_routes(): void
    {
        $user = \App\Models\User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertHeader('X-Frame-Options');
        $response->assertHeader('X-Content-Type-Options');
        $response->assertHeader('Referrer-Policy');
        $response->assertHeader('Content-Security-Policy');
    }

    public function test_all_security_headers_present_on_api_like_routes(): void
    {
        $response = $this->get('/up');

        $response->assertHeader('X-Frame-Options');
        $response->assertHeader('X-Content-Type-Options');
    }
}
