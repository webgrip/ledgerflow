<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureRateLimiters();
    }

    /**
     * Configure named rate limiters used across the application.
     *
     * - api: 60 requests/minute per user or IP (standard API access)
     * - webhooks: 120 requests/minute per IP (external provider bursts)
     * - ai: 10 requests/minute per user (prevent AI cost runaway)
     */
    protected function configureRateLimiters(): void
    {
        RateLimiter::for('api', fn (Request $request) => Limit::perMinute(60)->by(
            $request->user()->id ?? $request->ip()
        ));

        RateLimiter::for('webhooks', fn (Request $request) => Limit::perMinute(120)->by(
            $request->ip()
        ));

        RateLimiter::for('ai', fn (Request $request) => Limit::perMinute(10)->by(
            $request->user()->id ?? $request->ip()
        ));
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
