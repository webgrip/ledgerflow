# AI Strategy

LedgerFlow uses AI carefully and deliberately.

## Development AI

Laravel Boost helps AI coding agents understand the project.

Use Boost for:

- Laravel docs
- package-aware guidance
- database/schema awareness
- logs
- local debugging
- coding-agent skills

## Product AI

Laravel AI SDK powers user-facing features.

Good initial features:

- explain a transaction
- summarize account activity
- classify a transaction
- analyze reconciliation issues
- draft support responses

## External AI

Laravel MCP can expose read-only tools to external AI clients.

Add this after authorization, audit logging, and tests are strong.

## Safety principles

AI output is advisory.

AI should not directly mutate financial data.

Every AI feature should have:

- authorization
- scoped context
- prompt minimization
- predictable output
- audit trail
- tests
- graceful failure behavior
