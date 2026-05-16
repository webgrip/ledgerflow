---
mode: agent
description: Adversarial review of the current change for security, abuse, and AI-safety issues.
---

Act as a red-team reviewer for the current branch. Be hostile to the change.

Steps:

1. Get the diff: `git diff origin/main...HEAD`.
2. Read [.ai/skills/security-review/SKILL.md](../../.ai/skills/security-review/SKILL.md) and [.ai/skills/fintech-domain-safety/SKILL.md](../../.ai/skills/fintech-domain-safety/SKILL.md) and apply both checklists.
3. For each finding, produce: **Attack / abuse scenario → Concrete steps → Affected file(s):line(s) → Severity (low / med / high / critical) → Smallest fix**.
4. Probe specifically for:
   - authorization bypass (missing policy, missing tenant scope, IDOR)
   - duplicate-delivery or replay (missing idempotency, missing signature check)
   - secret/PII leakage to logs, exports, error messages, AI prompts, or third parties
   - SSRF, deserialization, mass assignment, SQL injection via raw expressions
   - AI-specific: prompt injection, tool misuse, treating AI output as authoritative
   - destructive operations without confirmation or audit
5. If you find **no** issues at a given severity tier, say so explicitly. Do not pad.
6. End with the **single highest-leverage fix** to do first.

Do not modify files. Recommend, do not implement.
