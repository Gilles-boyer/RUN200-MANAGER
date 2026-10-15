<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_receipts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('payment_id')->unique()->constrained()->restrictOnDelete();
            $table->string('number')->nullable()->unique();
            $table->timestamp('issued_at');
            $table->string('issuer_name');
            $table->string('issuer_siret', 14);
            $table->string('issuer_address');
            $table->string('issuer_email');
            $table->string('issuer_phone', 32);
            $table->string('payer_name');
            $table->string('payer_email')->nullable();
            $table->string('race_name');
            $table->date('race_date')->nullable();
            $table->unsignedInteger('amount_cents');
            $table->string('payment_status_at_issue', 32);
            $table->unsignedInteger('refund_amount_cents_at_issue')->default(0);
            $table->char('currency', 3);
            $table->string('payment_method', 32);
            $table->timestamp('paid_at')->nullable();
            $table->string('email_status', 16)->default('pending');
            $table->string('delivery_email')->nullable();
            $table->timestamp('email_sent_at')->nullable();
            $table->string('email_last_error')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_receipts');
    }
};
