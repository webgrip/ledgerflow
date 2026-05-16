<?php

declare(strict_types=1);

return [
    // Each row: ['input' => ..., 'expected' => ...]
    [
        'input' => 'The user paid 12.50 EUR for a sandwich.',
        'expected' => [
            'amount_minor' => 1250,
            'currency' => 'EUR',
            'category_hint' => 'food',
        ],
    ],
    [
        'input' => 'Refund of $4.00 from coffee shop.',
        'expected' => [
            'amount_minor' => 400,
            'currency' => 'USD',
            'category_hint' => 'food',
        ],
    ],
];
