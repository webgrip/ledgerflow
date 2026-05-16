<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    Route::livewire('organizations/create', 'pages::organizations.create')->name('organizations.create');

    Route::livewire('accounts', 'pages::accounts.index')->name('accounts.index');
    Route::livewire('accounts/create', 'pages::accounts.create')->name('accounts.create');
    Route::livewire('accounts/{account}', 'pages::accounts.show')->name('accounts.show');

    Route::livewire('accounts/{account}/transactions/create', 'pages::transactions.create')->name('transactions.create');
});

require __DIR__.'/settings.php';
