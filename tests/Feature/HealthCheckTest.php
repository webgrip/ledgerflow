<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('GET /health', function () {
    it('returns 200 with healthy status when all services are up', function () {
        $response = $this->getJson('/health');

        $response->assertOk()
            ->assertJsonPath('status', 'healthy')
            ->assertJsonStructure([
                'status',
                'timestamp',
                'checks' => [
                    'database' => ['status'],
                    'cache' => ['status'],
                    'queue' => ['status'],
                ],
            ]);
    });

    it('is accessible without authentication', function () {
        $response = $this->getJson('/health');

        $response->assertOk();
    });

    it('reports individual service statuses as ok', function () {
        $response = $this->getJson('/health');

        $data = $response->json();
        expect($data['checks']['database']['status'])->toBe('ok')
            ->and($data['checks']['cache']['status'])->toBe('ok');
    });
});
