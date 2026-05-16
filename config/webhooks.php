<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Webhook Signing Secrets
    |--------------------------------------------------------------------------
    |
    | Provider-keyed signing secrets. When a secret is set, the incoming
    | webhook signature will be validated. Empty secrets skip validation
    | (suitable for local development and demo environments).
    |
    | Stripe: set STRIPE_WEBHOOK_SECRET to the whsec_... value from the
    | Stripe dashboard → Developers → Webhooks → signing secret.
    |
    */
    'secrets' => [
        'stripe' => env('STRIPE_WEBHOOK_SECRET', ''),
        'mollie' => env('MOLLIE_WEBHOOK_SECRET', ''),
    ],
];
