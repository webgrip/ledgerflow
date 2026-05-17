# AI Strategy

LedgerFlow uses AI as an advisory layer over scoped financial context. AI must not directly mutate financial data.

## Development AI

Laravel Boost keeps coding agents aligned with the installed Laravel ecosystem, project skills, and generated guidance. Boost artifacts should be regenerated when package or skill guidance changes.

## Product AI

User-facing AI features use `laravel/ai`:

- Explain a transaction.
- Summarize account activity.
- Classify or categorize transactions.
- Analyze reconciliation issues.
- Draft support-oriented financial explanations.

## Safety principles

Every AI feature should have:

- Authorization before context collection.
- Organization-scoped context.
- Prompt minimization and redaction where needed.
- Predictable output shape for anything machine-consumed.
- Audit trail with agent, model, and token metadata.
- Tests that fake agent responses.
- Graceful failure behavior that does not corrupt financial state.

## MCP integration

Laravel MCP should expose read-only tools only. Mutation-capable tools should require a separate architectural decision because they change the risk model for financial data.
