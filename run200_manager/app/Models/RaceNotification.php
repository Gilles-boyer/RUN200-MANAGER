<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RaceNotification extends Model
{
    protected $fillable = [
        'race_id',
        'created_by',
        'subject',
        'message',
        'type',
        'recipients',
        'scheduled_at',
        'sent_at',
        'sent_count',
    ];

    protected $casts = [
        'recipients' => 'array',
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    public function race(): BelongsTo
    {
        return $this->belongsTo(Race::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Check if notification has been sent.
     */
    public function isSent(): bool
    {
        return $this->sent_at !== null;
    }

    /**
     * Check if notification is scheduled for future.
     */
    public function isScheduled(): bool
    {
        return $this->scheduled_at !== null && $this->scheduled_at->isFuture() && ! $this->isSent();
    }

    /**
     * Check if notification should be sent now.
     */
    public function shouldSendNow(): bool
    {
        if ($this->isSent()) {
            return false;
        }

        if ($this->scheduled_at === null) {
            return true;
        }

        return $this->scheduled_at->isPast();
    }
}
