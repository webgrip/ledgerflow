<?php

declare(strict_types=1);

namespace Tests\Eval;

/**
 * Example eval: extracting structured transaction data from free-text input.
 *
 * Replace the runner with a real call into your AI port once one exists, e.g.:
 *   $runner = fn (array $row) => app(TransactionExtractor::class)->extract($row['input']);
 *
 * This file is intentionally minimal — it demonstrates the harness shape.
 */
it('extracts transaction fields with a mean score above 0.8', function () {
    /** @var iterable<array<string, mixed>> $dataset */
    $dataset = require __DIR__.'/fixtures/transaction-extraction.golden.php';

    $runner = function (array $row): array {
        // Stub for offline runs. Replace with a real extractor when wired up.
        return [
            'amount_minor' => $row['expected']['amount_minor'],
            'currency' => $row['expected']['currency'],
            'category_hint' => $row['expected']['category_hint'],
        ];
    };

    $scorer = function (array $actual, array $row): float {
        $expected = $row['expected'];
        $hits = 0;
        $total = count($expected);

        foreach ($expected as $key => $value) {
            if (($actual[$key] ?? null) === $value) {
                $hits++;
            }
        }

        return $total === 0 ? 0.0 : $hits / $total;
    };

    /** @var EvalTestCase $this */
    $this->assertMeanScoreAtLeast(
        dataset: $dataset,
        runner: $runner,
        scorer: $scorer,
        threshold: 0.8,
        label: 'transaction-extraction',
    );
})->extends(EvalTestCase::class);
