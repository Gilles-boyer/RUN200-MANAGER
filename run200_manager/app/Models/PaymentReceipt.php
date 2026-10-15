<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentReceipt extends Model
{
    protected $fillable = [
        'payment_id', 'number', 'issued_at',
        'issuer_name', 'issuer_siret', 'issuer_address', 'issuer_email', 'issuer_phone',
        'payer_name', 'payer_email', 'race_name', 'race_date',
        'amount_cents', 'payment_status_at_issue', 'refund_amount_cents_at_issue',
        'currency', 'payment_method', 'paid_at',
        'email_status', 'delivery_email', 'email_sent_at', 'email_last_error',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
        'race_date' => 'date',
        'amount_cents' => 'integer',
        'refund_amount_cents_at_issue' => 'integer',
        'paid_at' => 'datetime',
        'email_sent_at' => 'datetime',
    ];

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount_cents / 100, 2, ',', ' ').' '.$this->currency;
    }
}
