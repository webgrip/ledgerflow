<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Laravel\Pulse\Facades\Pulse;
use Laravel\Pulse\PulseServiceProvider as BasePulseServiceProvider;

class PulseServiceProvider extends BasePulseServiceProvider
{
    public function boot(): void
    {
        parent::boot();

        Gate::define('viewPulse', function ($user = null): bool {
            if (app()->environment('production')) {
                return false;
            }

            return true;
        });

        // Tag every request with the current org for filtering
        Pulse::user(fn ($user) => [
            'name' => $user->name,
            'extra' => $user->email,
            'avatar' => null,
        ]);
    }
}
