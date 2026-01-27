<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Change amount to cents for better precision
            $table->integer('amount_cents')->default(0)->after('amount');
            $table->char('currency', 3)->default('EUR')->after('amount_cents');

            // Stripe specific fields
            $table->string('stripe_session_id')->nullable()->after('status');
            $table->string('stripe_payment_intent_id')->nullable()->after('stripe_session_id');
            $table->string('stripe_customer_id')->nullable()->after('stripe_payment_intent_id');

            // Payment tracking
            $table->datetime('paid_at')->nullable()->after('stripe_customer_id');
            $table->datetime('refunded_at')->nullable()->after('paid_at');
            $table->integer('refund_amount_cents')->nullable()->after('refunded_at');

            // Metadata
            $table->json('metadata')->nullable()->after('refund_amount_cents');
            $table->text('failure_reason')->nullable()->after('metadata');

            // Indexes
            $table->index('stripe_session_id');
            $table->index('stripe_payment_intent_id');
            $table->index(['method', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['stripe_session_id']);
            $table->dropIndex(['stripe_payment_intent_id']);
            $table->dropIndex(['method', 'status']);

            $table->dropColumn([
                'amount_cents',
                'currency',
                'stripe_session_id',
                'stripe_payment_intent_id',
                'stripe_customer_id',
                'paid_at',
                'refunded_at',
                'refund_amount_cents',
                'metadata',
                'failure_reason',
            ]);
        });
    }
};
