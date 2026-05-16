<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Throwable;

class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'queue' => $this->checkQueue(),
        ];

        $healthy = collect($checks)->every(fn ($check) => $check['status'] === 'ok');

        return response()->json([
            'status' => $healthy ? 'healthy' : 'degraded',
            'timestamp' => now()->toIso8601String(),
            'checks' => $checks,
        ], $healthy ? 200 : 503);
    }

    /** @return array{status: string, message?: string} */
    private function checkDatabase(): array
    {
        try {
            DB::selectOne('SELECT 1');

            return ['status' => 'ok'];
        } catch (Throwable $e) {
            return ['status' => 'fail', 'message' => 'Database unreachable'];
        }
    }

    /** @return array{status: string, message?: string} */
    private function checkCache(): array
    {
        try {
            $key = 'health:'.uniqid();
            Cache::put($key, true, 5);
            Cache::forget($key);

            return ['status' => 'ok'];
        } catch (Throwable $e) {
            return ['status' => 'fail', 'message' => 'Cache unreachable'];
        }
    }

    /** @return array{status: string, message?: string} */
    private function checkQueue(): array
    {
        try {
            $size = Queue::size('default');

            return ['status' => 'ok', 'backlog' => $size];
        } catch (Throwable $e) {
            return ['status' => 'fail', 'message' => 'Queue unreachable'];
        }
    }
}
