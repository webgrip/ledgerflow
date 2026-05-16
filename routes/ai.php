<?php

declare(strict_types=1);

use App\Mcp\Servers\LedgerFlowServer;
use Illuminate\Support\Facades\Route;
use Laravel\Mcp\Facades\Mcp;

/*
|--------------------------------------------------------------------------
| LedgerFlow MCP Server
|--------------------------------------------------------------------------
|
| Exposes a read-only MCP server for AI clients (Cursor, Claude Desktop,
| VS Code, etc.) to query accounts, transactions, reconciliation issues,
| and audit events for the authenticated user's organization.
|
| All tools are organization-scoped via Auth::user()->current_organization_id.
|
*/

Route::middleware(['auth'])->group(function () {
    Mcp::web('/mcp/ledgerflow', LedgerFlowServer::class);
});
