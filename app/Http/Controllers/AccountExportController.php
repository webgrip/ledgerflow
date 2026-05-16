<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Account;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AccountExportController extends Controller
{
    /**
     * Stream an account's transactions as a CSV file.
     *
     * Authorization: any org member may export (same as view).
     */
    public function __invoke(Request $request, Account $account): StreamedResponse
    {
        Gate::authorize('view', $account);

        $filename = sprintf(
            '%s-transactions-%s.csv',
            str($account->name)->slug(),
            now()->format('Y-m-d'),
        );

        return response()->streamDownload(function () use ($account): void {
            $handle = fopen('php://output', 'w');

            if ($handle === false) {
                return;
            }

            fputcsv($handle, ['Date', 'Description', 'Type', 'Amount', 'Currency']);

            $account->transactions()
                ->orderBy('transacted_at')
                ->each(function ($tx) use ($handle): void {
                    fputcsv($handle, [
                        $tx->transacted_at->format('Y-m-d'),
                        $tx->description,
                        $tx->type->value,
                        number_format($tx->amount_minor_units / 100, 2, '.', ''),
                        $tx->account->currency ?? 'EUR',
                    ]);
                });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
