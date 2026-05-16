<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->in('Feature');

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Integration', 'Functional', 'Contract', 'Smoke');

pest()->extend(TestCase::class)
    ->in('Unit');

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

expect()->extend('toBeMinorUnits', function (float $dollars) {
    return $this->toBe((int) round($dollars * 100));
});
