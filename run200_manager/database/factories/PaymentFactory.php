<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Payment\Enums\PaymentMethod;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\RaceRegistration;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'race_registration_id' => RaceRegistration::factory(),
            'amount_cents' => $this->faker->randomElement([5000, 7500, 10000]),
            'currency' => 'EUR',
            'status' => PaymentStatus::PENDING,
            'method' => PaymentMethod::STRIPE,
            'stripe_session_id' => null,
            'stripe_payment_intent_id' => null,
            'stripe_customer_id' => null,
            'paid_at' => null,
            'refunded_at' => null,
            'refund_amount_cents' => null,
            'notes' => null,
            'metadata' => null,
            'failure_reason' => null,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PaymentStatus::PENDING,
            'paid_at' => null,
        ]);
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PaymentStatus::PAID,
            'paid_at' => now(),
            'stripe_payment_intent_id' => 'pi_'.$this->faker->uuid(),
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PaymentStatus::FAILED,
            'failure_reason' => 'Card declined',
        ]);
    }

    public function refunded(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PaymentStatus::REFUNDED,
            'paid_at' => now()->subDay(),
            'refunded_at' => now(),
            'refund_amount_cents' => $attributes['amount_cents'] ?? 5000,
        ]);
    }

    public function manual(): static
    {
        return $this->state(fn (array $attributes) => [
            'method' => PaymentMethod::MANUAL,
            'status' => PaymentStatus::PAID,
            'paid_at' => now(),
            'stripe_session_id' => null,
            'stripe_payment_intent_id' => null,
            'stripe_customer_id' => null,
        ]);
    }

    public function stripe(): static
    {
        return $this->state(fn (array $attributes) => [
            'method' => PaymentMethod::STRIPE,
            'stripe_session_id' => 'cs_'.$this->faker->uuid(),
        ]);
    }

    public function withSession(): static
    {
        return $this->state(fn (array $attributes) => [
            'stripe_session_id' => 'cs_test_'.$this->faker->uuid(),
        ]);
    }
}
