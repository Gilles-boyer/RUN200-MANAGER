<?php

declare(strict_types=1);

namespace Tests\Feature\Sprint4;

use App\Infrastructure\Qr\QrTokenService;
use App\Models\RaceRegistration;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ============================================================================
// QrTokenService Tests
// ============================================================================

test('can generate a token for registration', function () {
    $registration = RaceRegistration::factory()->accepted()->create();

    $service = new QrTokenService;
    $token = $service->generate($registration);

    expect($token)->toBeString()
        ->and(strlen($token))->toBe(64);
});

test('generated token is stored as hash in database', function () {
    $registration = RaceRegistration::factory()->accepted()->create();

    $service = new QrTokenService;
    $plainToken = $service->generate($registration);

    $registration->refresh();
    $qrToken = $registration->qrToken;

    expect($qrToken)->not->toBeNull()
        ->and($qrToken->token_hash)->toBe(hash('sha256', $plainToken))
        ->and($qrToken->token_hash)->not->toBe($plainToken);
});

test('can validate a valid token', function () {
    $registration = RaceRegistration::factory()->accepted()->create();

    $service = new QrTokenService;
    $plainToken = $service->generate($registration);

    $validated = $service->validate($plainToken);

    expect($validated)->not->toBeNull()
        ->and($validated->id)->toBe($registration->id);
});

test('invalid token returns null', function () {
    $service = new QrTokenService;

    $validated = $service->validate('invalid_token_that_does_not_exist');

    expect($validated)->toBeNull();
});

test('expired token returns null', function () {
    $registration = RaceRegistration::factory()->accepted()->create();

    $service = new QrTokenService;
    $plainToken = $service->generate($registration, now()->subDay());

    $validated = $service->validate($plainToken);

    expect($validated)->toBeNull();
});

test('can generate QR code SVG', function () {
    $service = new QrTokenService;
    $svg = $service->generateQrCodeSvg('test_token');

    expect($svg)->toBeString()
        ->and($svg)->toContain('<svg')
        ->and($svg)->toContain('</svg>');
});

test('can generate QR code data URI', function () {
    $service = new QrTokenService;
    $dataUri = $service->generateQrCodeDataUri('test_token');

    expect($dataUri)->toStartWith('data:image/svg+xml;base64,');
});

test('regenerating token invalidates old token', function () {
    $registration = RaceRegistration::factory()->accepted()->create();

    $service = new QrTokenService;
    $firstToken = $service->generate($registration);
    $secondToken = $service->generate($registration);

    expect($firstToken)->not->toBe($secondToken);

    // First token should now be invalid
    $validatedFirst = $service->validate($firstToken);
    $validatedSecond = $service->validate($secondToken);

    expect($validatedFirst)->toBeNull()
        ->and($validatedSecond)->not->toBeNull();
});

test('can revoke token', function () {
    $registration = RaceRegistration::factory()->accepted()->create();

    $service = new QrTokenService;
    $token = $service->generate($registration);

    expect($service->validate($token))->not->toBeNull();

    $service->revoke($registration);

    expect($service->validate($token))->toBeNull();
});
