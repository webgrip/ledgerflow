# AI Tooling Strategy

LedgerFlow uses AI in three different ways.

## 1. Laravel Boost

Boost is for development assistance.

It helps AI coding agents understand:

- Laravel conventions
- installed package versions
- project structure
- documentation
- database schema
- logs
- browser issues
- tests

Boost should be installed early.

Use Boost to improve coding quality, not to define product behavior.

## 2. Laravel AI SDK

The AI SDK is for user-facing product features.

Possible features:

- explain a transaction
- classify a transaction
- summarize account activity
- analyze reconciliation mismatches
- draft support replies
- answer questions over uploaded statements
- detect suspicious patterns for review

AI SDK features should be tested with fakes where possible.

Treat structured output as an application contract.

Version it deliberately when downstream behavior depends on it.

## 3. Laravel MCP

MCP is for exposing safe app capabilities to external AI clients.

Add MCP later.

Start with read-only tools.

Possible tools:

- get account summary
- search transactions
- explain transaction
- list reconciliation issues
- list audit events

Do not expose write tools until the app has strong authorization, audit logging, and safety reviews.

## AI design rules

For user-facing AI features:

- start with a narrow user problem
- collect the minimum context required
- minimize secrets and personal data
- prefer structured output when code acts on the result
- record model/provider and important inputs at an audit-safe level
- define fallback behavior for provider failure, timeout, and malformed output
- define when human review is required

## Evaluation and failure handling

Important AI features should have evaluation examples, regression checks, or fixture-based tests.

AI should fail closed for risky workflows.

If the model is uncertain or malformed, the system should ask for review, degrade gracefully, or decline the action.

## AI safety rules

AI should not directly:

- move money
- post financial records
- delete financial data
- change permissions
- approve risky actions
- bypass authorization

AI output should be advisory unless explicitly designed otherwise.

Model choice, prompt shape, and tool access are architecture decisions, not just implementation details.
