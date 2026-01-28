<?php

namespace App\Models;

use App\Domain\Payment\Enums\PaymentMethod;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Infrastructure\Payments\Stripe\StripePaymentService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Payment extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'race_registration_id',
        'user_id',
        'amount',
        'amount_cents',
        'currency',
        'method',
        'status',
        'stripe_session_id',
        'stripe_payment_intent_id',
        'stripe_customer_id',
        'stripe_event_id',
        'paid_at',
        'refunded_at',
        'refund_amount_cents',
        'metadata',
        'failure_reason',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'amount_cents' => 'integer',
        'refund_amount_cents' => 'integer',
        'paid_at' => 'datetime',
        'refunded_at' => 'datetime',
        'metadata' => 'array',
        'method' => PaymentMethod::class,
        'status' => PaymentStatus::class,
    ];

    // =========================================================================
    // Relationships
    // =========================================================================

    public function registration(): BelongsTo
    {
        return $this->belongsTo(RaceRegistration::class, 'race_registration_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // =========================================================================
    // Accessors
    // =========================================================================

    /**
     * Get formatted amount for display.
     */
    public function getFormattedAmountAttribute(): string
    {
        return StripePaymentService::formatAmount(
            $this->amount_cents ?? (int) ($this->amount * 100),
            $this->currency ?? 'EUR'
        );
    }

    /**
     * Get formatted refund amount for display.
     */
    public function getFormattedRefundAmountAttribute(): ?string
    {
        if (! $this->refund_amount_cents) {
            return null;
        }

        return StripePaymentService::formatAmount(
            $this->refund_amount_cents,
            $this->currency ?? 'EUR'
        );
    }

    /**
     * Get payment method enum.
     */
    /**
     * Get method label for display.
     */
    public function getMethodLabelAttribute(): string
    {
        return $this->method->label();
    }

    /**
     * Get status label for display.
     */
    public function getStatusLabelAttribute(): string
    {
        return $this->status->label();
    }

    /**
     * Get status badge color.
     */
    public function getStatusColorAttribute(): string
    {
        return $this->status->badgeColor();
    }

    // =========================================================================
    // Status Checks
    // =========================================================================

    public function isPending(): bool
    {
        return $this->status === PaymentStatus::PENDING;
    }

    public function isProcessing(): bool
    {
        return $this->status === PaymentStatus::PROCESSING;
    }

    public function isPaid(): bool
    {
        return $this->status === PaymentStatus::PAID;
    }

    public function isFailed(): bool
    {
        return $this->status === PaymentStatus::FAILED;
    }

    public function isRefunded(): bool
    {
        return $this->status === PaymentStatus::REFUNDED;
    }

    public function isPartiallyRefunded(): bool
    {
        return $this->status === PaymentStatus::PARTIALLY_REFUNDED;
    }

    public function isCancelled(): bool
    {
        return $this->status === PaymentStatus::CANCELLED;
    }

    public function isCompleted(): bool
    {
        return $this->isPaid();
    }

    public function canBeRefunded(): bool
    {
        return $this->status->canBeRefunded()
            && $this->method === PaymentMethod::STRIPE
            && $this->stripe_payment_intent_id !== null;
    }

    public function isStripePayment(): bool
    {
        return $this->method === PaymentMethod::STRIPE;
    }

    public function isManualPayment(): bool
    {
        return $this->method === PaymentMethod::MANUAL;
    }

    // =========================================================================
    // Scopes
    // =========================================================================

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeStripe($query)
    {
        return $query->where('method', 'stripe');
    }

    public function scopeManual($query)
    {
        return $query->where('method', 'manual');
    }

    // =========================================================================
    // Activity Log
    // =========================================================================

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['amount', 'amount_cents', 'method', 'status', 'paid_at', 'refunded_at'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
