---
name: security-review
description: Use when reviewing authentication, authorization, tenant isolation, secrets, logs, exports, webhooks, AI prompt safety, MCP tools, or trust boundaries.
---

# Security Review Skill

## Use this when

- adding sensitive data access
- adding AI prompts
- adding exports
- adding webhooks
- adding MCP tools
- changing authorization

## Review priorities

1. cross-tenant data exposure
2. authorization bypass
3. sensitive logging
4. webhook spoofing/replay
5. unsafe AI prompt context
6. insecure exports
7. missing rate limits
8. secrets exposure

## Threat checklist

- what is attacker-controlled?
- what crosses a trust boundary?
- what data could leak across tenants?
- what actions are privileged or irreversible?
- what replay, duplicate-delivery, or impersonation risks exist?
- what sensitive data might enter logs, prompts, or exports?

## Output format

Use:

```text
Critical
High
Medium
Low
Notes
```
