# Boost File Map

Use this document to understand where agent guidance lives.

## Files Boost should care about directly

```text
.ai/guidelines/
.ai/skills/{skill-name}/SKILL.md
```

## Files outside direct Boost input

```text
planning/
docs/
AGENTS.md
```

## Recommended workflow after extracting this pack

```bash
php artisan boost:update --discover
php artisan boost:skill-list
```

## Directory purpose

| Directory | Purpose |
| --- | --- |
| `.ai/guidelines/` | Always-on project rules and context |
| `.ai/skills/` | Task-specific Boost skills and project/domain seams |
| `planning/` | Roadmap, milestones, backlog, planning templates |
| `docs/` | Human-facing docs and demo material |
| `AGENTS.md` | Generic root entrypoint for coding agents |

## Guidance split

Use `AGENTS.md` for generic, reusable agent behavior.

Use `.ai/guidelines/` for project-specific always-on rules.

Use `.ai/skills/` for project-specific task seams that should activate on demand.

That means `.ai/skills/` is the right place for domain-specific or workflow-specific guidance.

Examples in this repo:

- `transaction-consistency-and-concurrency`
- `integration-contracts-and-outbox`
- `fintech-domain-safety`
- `change-safety-and-rollout`

## Current .ai/skills taxonomy

- `architecture-boundaries`
- `ai-feature-design`
- `change-safety-and-rollout`
- `code-review`
- `devops-operations`
- `documentation`
- `fintech-domain-safety`
- `integration-contracts-and-outbox`
- `laravel-backend-development`
- `livewire-ui-development`
- `product-planning`
- `security-review`
- `testing-strategy`
- `transaction-consistency-and-concurrency`
