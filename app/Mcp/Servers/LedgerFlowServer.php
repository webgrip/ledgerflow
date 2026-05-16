<?php

declare(strict_types=1);

namespace App\Mcp\Servers;

use App\Mcp\Tools\GetAccountSummary;
use App\Mcp\Tools\ListAuditEvents;
use App\Mcp\Tools\ListReconciliationIssues;
use App\Mcp\Tools\SearchTransactions;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;

#[Name('LedgerFlow')]
#[Version('1.0.0')]
#[Instructions(
    'LedgerFlow is a fintech platform for managing financial accounts, transactions, '.
    'and reconciliation. All tools are read-only and scoped to the authenticated '.
    'user\'s current organization. Never write or modify data through this server.'
)]
class LedgerFlowServer extends Server
{
    protected array $tools = [
        GetAccountSummary::class,
        SearchTransactions::class,
        ListReconciliationIssues::class,
        ListAuditEvents::class,
    ];

    protected array $resources = [];

    protected array $prompts = [];
}
