---
name: fintech-domain-safety
description: Use when implementing or reviewing money, ledger, transaction, payment, webhook, reconciliation, settlement, audit, or finance operations workflows.
---

# Fintech Domain Safety Skill

## Use this when

- money is involved
- external financial events are processed
- balances or statuses are changed
- reconciliation logic is introduced or modified
- auditability or reversibility matters
- AI is used around financial data

## Review checklist

- no floats for money
- currency and amount meaning are explicit
- lifecycle states are clear and legal transitions are enforced
- financial history is auditable and correction-friendly
- external events are idempotent and replay-safe
- tenant isolation is enforced
- risky actions require human review where appropriate
- AI output remains advisory unless explicitly designed otherwise
