<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Stripe API Keys
    |--------------------------------------------------------------------------
    |
    | The Stripe publishable key and secret key for your account.
    | Use test keys for development and live keys for production.
    |
    */
    'key' => env('STRIPE_KEY'),
    'secret' => env('STRIPE_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Stripe Webhook Secret
    |--------------------------------------------------------------------------
    |
    | The webhook signing secret for validating Stripe webhook events.
    |
    */
    'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Default Currency
    |--------------------------------------------------------------------------
    |
    | The default currency for payments (ISO 4217 code).
    |
    */
    'currency' => env('STRIPE_CURRENCY', 'EUR'),

    /*
    |--------------------------------------------------------------------------
    | Race Registration Fee
    |--------------------------------------------------------------------------
    |
    | The default fee for race registration in cents.
    | Can be overridden per race.
    |
    */
    'registration_fee_cents' => env('STRIPE_REGISTRATION_FEE_CENTS', 5000), // 50.00 EUR

    /*
    |--------------------------------------------------------------------------
    | Payment URLs
    |--------------------------------------------------------------------------
    |
    | Success and cancel URLs for Stripe Checkout.
    |
    */
    'success_url' => env('STRIPE_SUCCESS_URL', '/pilot/registrations/{registration_id}/payment/success'),
    'cancel_url' => env('STRIPE_CANCEL_URL', '/pilot/registrations/{registration_id}/payment/cancel'),

    /*
    |--------------------------------------------------------------------------
    | Enabled Payment Methods
    |--------------------------------------------------------------------------
    |
    | List of enabled payment methods in Stripe Checkout.
    |
    */
    'payment_methods' => ['card', 'bancontact', 'ideal', 'sepa_debit'],

    /*
    |--------------------------------------------------------------------------
    | Test Mode
    |--------------------------------------------------------------------------
    |
    | Whether Stripe is in test mode.
    |
    */
    'test_mode' => env('STRIPE_TEST_MODE', true),
];
