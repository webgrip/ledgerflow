<?php

declare(strict_types=1);

namespace Tests\Eval;

use Tests\TestCase;

/**
 * Base class for AI evaluation tests.
 *
 * Evals are different from unit/feature tests:
 * - they may be slow, flaky, and call real providers;
 * - they assert against scoring thresholds, not exact equality;
 * - they are gated behind EVAL_LIVE=1 by default.
 *
 * Run offline (skips live calls):
 *   vendor/bin/sail artisan test --configuration=phpunit.eval.xml
 *
 * Run live (requires provider credentials in .env):
 *   EVAL_LIVE=1 vendor/bin/sail artisan test --configuration=phpunit.eval.xml
 */
abstract class EvalTestCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! $this->isLive()) {
            // Subclasses should mock providers here. By default we skip.
            $this->markTestSkipped('Eval is offline (set EVAL_LIVE=1 to run live).');
        }
    }

    protected function isLive(): bool
    {
        return (string) env('EVAL_LIVE', '0') === '1';
    }

    /**
     * Score a single sample against expected criteria.
     *
     * @param  array<string, mixed>  $sample   The dataset row (input + expected).
     * @param  callable(array<string, mixed>): mixed  $runner   Produces the actual output.
     * @param  callable(mixed, array<string, mixed>): float  $scorer  Returns a score in [0, 1].
     */
    protected function score(array $sample, callable $runner, callable $scorer): float
    {
        $actual = $runner($sample);

        return (float) $scorer($actual, $sample);
    }

    /**
     * Assert that the mean score over a dataset clears a threshold.
     *
     * @param  iterable<array<string, mixed>>  $dataset
     * @param  callable(array<string, mixed>): mixed  $runner
     * @param  callable(mixed, array<string, mixed>): float  $scorer
     */
    protected function assertMeanScoreAtLeast(
        iterable $dataset,
        callable $runner,
        callable $scorer,
        float $threshold,
        string $label = 'eval',
    ): void {
        $scores = [];
        foreach ($dataset as $sample) {
            $scores[] = $this->score($sample, $runner, $scorer);
        }

        $count = count($scores);
        $this->assertGreaterThan(0, $count, "{$label}: dataset is empty");

        $mean = array_sum($scores) / $count;

        $this->assertGreaterThanOrEqual(
            $threshold,
            $mean,
            sprintf('%s: mean score %.3f below threshold %.3f (n=%d)', $label, $mean, $threshold, $count),
        );
    }
}
