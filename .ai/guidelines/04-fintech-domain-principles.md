# Fintech Domain Principles

These principles guide product and technical decisions.

## Money

Do not use floating point numbers for money.

Use integer minor units.

Examples:

```text
€10.50 => 1050
$42.99 => 4299
```

Track currency explicitly.

Do not mix amounts from different currencies without an explicit conversion rule and source.

Be precise about amount meaning:

- original amount
- settled amount
- fee amount
- net amount
- pending amount

## Auditability

Important financial actions should create audit events.

Audit logs should answer:

- who did it?
- when did it happen?
- what changed?
- what object was affected?
- what system/provider was involved?
- was AI involved?
- was it automatic or manual?

Application logs and audit events are not the same thing.

Logs help operators debug.

Audit events explain financially relevant history.

## Idempotency

Any externally triggered action should be safe to retry.

Examples:

- payment webhooks
- imported bank transactions
- reconciliation jobs
- AI classification jobs
- export generation

Idempotency must be designed, not assumed.

Use stable keys, duplicate detection, and replay-safe handlers.

## State clarity

Financial workflows should use clear lifecycle states.

Do not blur pending, posted, settled, reversed, failed, or reconciled behavior.

Illegal transitions should be rejected explicitly.

## Reversibility

Avoid destructive edits to financial history.

Prefer explicit correction flows.

Preserve enough history to explain how a balance or status changed over time.

## Tenant isolation

Financial data should be scoped to an organization or workspace.

Never assume record IDs are globally safe to access.

## Integration boundaries

Normalize provider payloads before they reach core domain logic.

Do not let provider field names, webhook quirks, or SDK shapes become the dominant language of the core model.

## Data lifecycle

Be deliberate about retention, export, redaction, and deletion.

When legal, audit, and user expectations conflict, choose a policy explicitly and document it.

## Human review

High-risk workflows should support human review.

AI may assist with:

- explanation
- categorization
- anomaly detection
- summaries
- drafting

AI should not be the final authority for financial mutations.
