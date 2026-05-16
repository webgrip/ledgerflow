<?php

use App\Http\Controllers\AccountExportController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\WebhookController;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

// Health check — public, no auth
Route::get('/health', HealthController::class)->name('health');

Route::livewire('dev', 'pages::dev.dashboard')->name('dev.dashboard');

// Webhook endpoint — no auth, CSRF-exempt, rate-limited
Route::post('webhooks/{provider}', [WebhookController::class, 'receive'])
    ->name('webhooks.receive')
    ->withoutMiddleware([VerifyCsrfToken::class])
    ->middleware('throttle:webhooks');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    Route::livewire('organizations/create', 'pages::organizations.create')->name('organizations.create');

    Route::livewire('accounts', 'pages::accounts.index')->name('accounts.index');
    Route::livewire('accounts/create', 'pages::accounts.create')->name('accounts.create');
    Route::livewire('accounts/{account}', 'pages::accounts.show')->name('accounts.show');

    Route::livewire('accounts/{account}/transactions/create', 'pages::transactions.create')->name('transactions.create');

    Route::livewire('reconciliation', 'pages::reconciliation.index')->name('reconciliation.index');
    Route::livewire('reconciliation/{run}', 'pages::reconciliation.show')->name('reconciliation.show');

    Route::livewire('audit-log', 'pages::audit.index')->name('audit.index');

    Route::livewire('webhooks', 'pages::webhooks.index')->name('webhooks.index');

    Route::get('accounts/{account}/export', [AccountExportController::class, '__invoke'])
        ->name('accounts.export');
});

require __DIR__.'/settings.php';
