# Boost Entrypoint

This project uses Laravel Boost.

Boost should treat `.ai/guidelines/` as always-on project guidance and `.ai/skills/{skill-name}/SKILL.md` as task-specific guidance.

This repo does not use a `.ai/roles/` layer.

## How Boost context should be used

When starting a task:

1. Read this file.
2. Read only the relevant guideline files.
3. Activate the most relevant skill files.
4. Read `planning/roadmap.md` only if the task affects sequencing, scope, or milestone choices.
5. Read `docs/` only when setup, architecture, or behavior explanation is required.
6. Make a small plan before coding.
7. Implement incrementally.
8. Add or update tests.
9. Update documentation when behavior, setup, architecture, or delivery expectations change.

## Guideline map

- `00-boost-entrypoint.md`: how to load and apply this pack
- `01-project-purpose.md`: what LedgerFlow is trying to prove
- `02-engineering-principles.md`: general engineering and architecture rules
- `03-laravel-standards.md`: Laravel-specific implementation guidance
- `04-fintech-domain-principles.md`: domain safety rules for money and financial workflows
- `05-ai-tooling-strategy.md`: how Boost, AI SDK, and MCP should be used
- `06-quality-security-observability.md`: quality, threat, logging, metrics, and audit expectations
- `07-change-safety-and-delivery.md`: ADRs, migrations, versioning, rollout, and release safety

## Context discipline

Do not load every guideline and every skill for trivial changes.

Prefer the smallest set of files that can shape the decision correctly.

When guidance overlaps, follow the most specific rule:

- Laravel-specific decisions defer to `03-laravel-standards.md`
- financial-risk decisions defer to `04-fintech-domain-principles.md`
- AI product decisions defer to `05-ai-tooling-strategy.md`
- rollout, migration, or contract decisions defer to `07-change-safety-and-delivery.md`

## Current project direction

LedgerFlow is a Laravel fintech learning and portfolio application.

It should demonstrate:

- strong Laravel fundamentals
- domain modeling judgment
- safe AI-assisted product design
- production-aware delivery habits
- clear observability and security thinking
- pragmatic architecture rather than pattern collecting
