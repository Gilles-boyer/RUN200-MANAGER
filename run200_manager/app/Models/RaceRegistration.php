<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class RaceRegistration extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'race_id',
        'pilot_id',
        'car_id',
        'status',
        'reason',
        'paddock',
        'paddock_spot_id',
        'validated_at',
        'validated_by',
    ];

    protected $casts = [
        'validated_at' => 'datetime',
    ];

    // =========================================================================
    // Scopes
    // =========================================================================

    public function scopePendingPayment(Builder $query): Builder
    {
        return $query->where('status', 'PENDING_PAYMENT');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'PENDING_VALIDATION');
    }

    public function scopeAccepted(Builder $query): Builder
    {
        return $query->where('status', 'ACCEPTED');
    }

    public function scopeRefused(Builder $query): Builder
    {
        return $query->where('status', 'REFUSED');
    }

    public function scopeCancelled(Builder $query): Builder
    {
        return $query->where('status', 'CANCELLED');
    }

    // =========================================================================
    // Relationships
    // =========================================================================

    public function race(): BelongsTo
    {
        return $this->belongsTo(Race::class);
    }

    public function pilot(): BelongsTo
    {
        return $this->belongsTo(Pilot::class);
    }

    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class);
    }

    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function qrToken(): HasOne
    {
        return $this->hasOne(QrToken::class);
    }

    public function passages(): HasMany
    {
        return $this->hasMany(CheckpointPassage::class);
    }

    public function techInspection(): HasOne
    {
        return $this->hasOne(TechInspection::class);
    }

    public function engagementForm(): HasOne
    {
        return $this->hasOne(EngagementForm::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function latestPayment(): HasOne
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }

    public function paidPayment(): HasOne
    {
        return $this->hasOne(Payment::class)->where('status', 'paid');
    }

    public function paddockSpot(): BelongsTo
    {
        return $this->belongsTo(PaddockSpot::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'reason', 'paddock', 'paddock_spot_id'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // =========================================================================
    // Helpers
    // =========================================================================

    public function isPendingPayment(): bool
    {
        return $this->status === 'PENDING_PAYMENT';
    }

    public function isPending(): bool
    {
        return $this->status === 'PENDING_VALIDATION';
    }

    public function isAccepted(): bool
    {
        return $this->status === 'ACCEPTED';
    }

    public function isRefused(): bool
    {
        return $this->status === 'REFUSED';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'CANCELLED';
    }

    /**
     * Mark registration as payment received (move to pending validation)
     */
    public function markAsPaid(): void
    {
        if ($this->status === 'PENDING_PAYMENT') {
            $this->update(['status' => 'PENDING_VALIDATION']);
        }
    }

    /**
     * Cancel the registration
     */
    public function cancel(?string $reason = null): void
    {
        $this->update([
            'status' => 'CANCELLED',
            'reason' => $reason,
        ]);
    }

    /**
     * Check if a specific checkpoint has been passed
     */
    public function hasPassedCheckpoint(string $checkpointCode): bool
    {
        return $this->passages()
            ->whereHas('checkpoint', fn ($q) => $q->where('code', $checkpointCode))
            ->exists();
    }

    /**
     * Get the passage for a specific checkpoint
     */
    public function getPassageForCheckpoint(string $checkpointCode): ?CheckpointPassage
    {
        /** @var CheckpointPassage|null */
        return $this->passages()
            ->whereHas('checkpoint', fn ($q) => $q->where('code', $checkpointCode))
            ->first();
    }

    /**
     * Check if registration has been paid
     */
    public function isPaid(): bool
    {
        return $this->payments()->where('status', 'paid')->exists();
    }

    /**
     * Check if registration has a pending payment
     */
    public function hasPendingPayment(): bool
    {
        return $this->payments()->where('status', 'pending')->exists();
    }

    /**
     * Get the paid amount in cents
     */
    public function getPaidAmountCents(): int
    {
        return $this->payments()
            ->where('status', 'paid')
            ->sum('amount_cents');
    }
}
