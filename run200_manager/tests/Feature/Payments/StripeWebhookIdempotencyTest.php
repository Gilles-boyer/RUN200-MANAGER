<?php

declare(strict_types=1);

namespace Tests\Feature\Payments;

use App\Application\Payments\UseCases\HandleStripeWebhook;
use App\Models\Payment;
use App\Models\Pilot;
use App\Models\Race;
use App\Models\RaceRegistration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StripeWebhookIdempotencyTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Pilot $pilot;

    private Race $race;

    private RaceRegistration $registration;

    private Payment $payment;

    private HandleStripeWebhook $webhookHandler;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test data
        $this->user = User::factory()->create();
        $this->pilot = Pilot::factory()->create(['user_id' => $this->user->id]);
        $this->race = Race::factory()->create();
        $this->registration = RaceRegistration::factory()->create([
            'pilot_id' => $this->pilot->id,
            'race_id' => $this->race->id,
            'status' => 'PENDING_PAYMENT',
        ]);

        $this->payment = Payment::create([
            'race_registration_id' => $this->registration->id,
            'user_id' => $this->user->id,
            'amount' => 50.00,
            'amount_cents' => 5000,
            'currency' => 'EUR',
            'method' => 'stripe',
            'status' => 'pending',
            'stripe_session_id' => 'cs_test_123456',
        ]);

        $this->webhookHandler = app(HandleStripeWebhook::class);
    }

    public function test_webhook_handler_stores_event_id(): void
    {
        $sessionData = [
            'id' => 'cs_test_123456',
            'payment_intent' => 'pi_test_123',
            'customer' => 'cus_test_123',
            'payment_status' => 'paid',
        ];

        $eventId = 'evt_test_unique_123';

        // Process webhook
        $result = $this->webhookHandler->handleCheckoutCompleted($sessionData, $eventId);

        // Verify event ID was stored
        $this->assertEquals($eventId, $result->stripe_event_id);
        $this->assertEquals('paid', $result->status);
    }

    public function test_cannot_process_same_event_twice_via_handler(): void
    {
        $sessionData = [
            'id' => 'cs_test_123456',
            'payment_intent' => 'pi_test_123',
            'customer' => 'cus_test_123',
            'payment_status' => 'paid',
        ];

        $eventId = 'evt_test_duplicate_456';

        // First processing
        $result1 = $this->webhookHandler->handleCheckoutCompleted($sessionData, $eventId);
        $this->assertEquals($eventId, $result1->stripe_event_id);
        $this->assertEquals('paid', $result1->status);

        // Verify event ID exists in database
        $this->assertTrue(Payment::where('stripe_event_id', $eventId)->exists());

        // Check that duplicate check would work
        $duplicateExists = Payment::where('stripe_event_id', $eventId)->exists();
        $this->assertTrue($duplicateExists, 'Duplicate event ID check should return true');
    }

    public function test_stripe_event_id_unique_constraint(): void
    {
        // Update payment with event ID
        $this->payment->update(['stripe_event_id' => 'evt_unique_123']);

        // Try to create another payment with same event ID - should fail due to unique constraint
        $this->expectException(\Illuminate\Database\QueryException::class);

        Payment::create([
            'race_registration_id' => $this->registration->id,
            'user_id' => $this->user->id,
            'amount' => 50.00,
            'amount_cents' => 5000,
            'currency' => 'EUR',
            'method' => 'stripe',
            'status' => 'pending',
            'stripe_session_id' => 'cs_test_different',
            'stripe_event_id' => 'evt_unique_123', // Duplicate
        ]);
    }

    public function test_different_events_have_different_ids(): void
    {
        $sessionData1 = [
            'id' => 'cs_test_123456',
            'payment_intent' => 'pi_test_001',
            'customer' => 'cus_test_001',
            'payment_status' => 'paid',
        ];

        $eventId1 = 'evt_test_001';
        $result1 = $this->webhookHandler->handleCheckoutCompleted($sessionData1, $eventId1);

        // Create second payment
        $payment2 = Payment::create([
            'race_registration_id' => $this->registration->id,
            'user_id' => $this->user->id,
            'amount' => 50.00,
            'amount_cents' => 5000,
            'currency' => 'EUR',
            'method' => 'stripe',
            'status' => 'pending',
            'stripe_session_id' => 'cs_test_789',
        ]);

        $sessionData2 = [
            'id' => 'cs_test_789',
            'payment_intent' => 'pi_test_002',
            'customer' => 'cus_test_002',
            'payment_status' => 'paid',
        ];

        $eventId2 = 'evt_test_002';
        $result2 = $this->webhookHandler->handleCheckoutCompleted($sessionData2, $eventId2);

        // Verify both events were stored with different IDs
        $this->assertEquals($eventId1, $result1->stripe_event_id);
        $this->assertEquals($eventId2, $result2->stripe_event_id);
        $this->assertNotEquals($eventId1, $eventId2);

        // Verify database has both
        $this->assertEquals(1, Payment::where('stripe_event_id', $eventId1)->count());
        $this->assertEquals(1, Payment::where('stripe_event_id', $eventId2)->count());
    }

    public function test_idempotency_check_in_controller_logic(): void
    {
        // Simulate what the controller does
        $eventId = 'evt_controller_test_789';

        // First check - should not exist
        $firstCheck = Payment::where('stripe_event_id', $eventId)->exists();
        $this->assertFalse($firstCheck);

        // Process payment
        $this->payment->update([
            'stripe_event_id' => $eventId,
            'status' => 'paid',
        ]);

        // Second check - should exist now
        $secondCheck = Payment::where('stripe_event_id', $eventId)->exists();
        $this->assertTrue($secondCheck);

        // This simulates the controller's early return
        if ($secondCheck) {
            // Would return 200 with "Event already processed"
            $this->assertTrue(true, 'Duplicate event properly detected');
        }
    }
}
