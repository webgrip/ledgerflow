# LedgerFlow — Copilot Instructions

These instructions are loaded into every Copilot Chat turn. Keep them short. Detailed guidance lives in [AGENTS.md](../AGENTS.md), [.ai/guidelines/](../.ai/guidelines/), and [.ai/skills/](../.ai/skills/).

## Authority order

1. The user's current message.
2. `.ai/guidelines/` (project rules, merged into AGENTS.md by Laravel Boost).
3. `.ai/skills/{skill}/SKILL.md` (on-demand skills; activate proactively when the topic matches).
4. The Laravel Boost block inside [AGENTS.md](../AGENTS.md) (generated; do not hand-edit).
5. These Copilot instructions.

## Conventions

- Conventional Commits for all commit messages.
- Semantic Versioning for releases.
- Atomic commits: one logical change per commit; intent matters.
- Test behavior, not implementation.
- Aim for high, meaningful test coverage. Coverage without behavioral assertions does not count.
- Record significant architectural decisions as ADRs. Template: [planning/adr-template.md](../planning/adr-template.md).

## Thought influences

| Thinker             | Idea we adopt                            |
| ------------------- | ---------------------------------------- |
| Kent Beck           | TDD, small safe releases                 |
| Robert C. Martin    | Clean Code, SOLID                        |
| Sam Newman          | Service boundaries, continuous delivery  |
| Eric Evans          | Domain-Driven Design, ubiquitous language|

Apply these by default; do not cite them in code or PRs unless asked.

## Repository orientation

- `app/` — Laravel application code. Domain modules live under `app/Domain/{Name}/...` (see [.ai/guidelines/03-laravel-standards.md](../.ai/guidelines/03-laravel-standards.md)).
- `tests/` — Pest tests (Feature, Unit). New behavior must come with tests.
- `docs/` — Long-form architecture and strategy.
- `planning/` — Roadmap, backlog, sprint, ADR, and feature-brief templates.
- `.ai/` — Always-on guidelines and on-demand skills consumed by Laravel Boost.
- `.github/` — Copilot-specific instructions, prompts, skills (Boost-generated), and workflows.

## Environment

- All PHP, Artisan, Composer, and Node commands run through Sail: `vendor/bin/sail …`.
- Never push to `main` directly. Open a PR.
- Do not run destructive commands (`rm -rf`, `git push --force`, dropping tables, deleting branches) without explicit confirmation.

## Defaults Copilot should apply silently

- Prefer named routes (`route('…')`) over string URLs.
- Prefer factories over hand-built models in tests.
- Keep controllers, Livewire components, and jobs thin; push rules into domain actions/services.
- No floats for money. Use minor units / `Money` value object.
- Validate and authorize at boundaries; never inside domain entities.

## When unsure

Ask one focused question. Do not invent product behavior, file paths, package versions, or domain terminology.
