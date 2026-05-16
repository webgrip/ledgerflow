<!--
  Mirror this structure with the /pr-description prompt:
  vendor/bin/copilot or Copilot Chat → /pr-description
-->

## What

<!-- One paragraph, plain language, no code refs. -->

## Why

<!-- The forcing function. Link the issue / ADR (docs/adrs/) / brief (planning/briefs/). -->

## How

<!-- Bulleted summary of the approach. Mention boundaries crossed, patterns used, and any new dependencies. -->

## Risk

- **Blast radius:**
- **Reversibility:** <!-- pure code | data migration | external side effect -->
- **Known unknowns:**

## Rollout

<!-- Feature flag? Background backfill? Expand/contract migration step? Order of deploy? -->

## Rollback

<!-- Exact steps to revert safely. If revert is unsafe, link the forward-fix plan. -->

## Tests

<!-- New tests added, what they assert, how to run them. -->

## Screenshots / recordings

<!-- UI changes only. Otherwise N/A. -->

## Checklist

- [ ] Conventional Commits in the commit history
- [ ] Tests added / updated and passing locally (`vendor/bin/sail artisan test --compact`)
- [ ] Formatting clean (`vendor/bin/sail bin pint --dirty --format agent`)
- [ ] ADR added if architectural (see [planning/adr-template.md](../planning/adr-template.md))
- [ ] Docs updated if behavior changed
- [ ] No secrets, no PII, no floats for money
- [ ] Boost block in `AGENTS.md` is **not** hand-edited (regenerated via `php artisan boost:install`)
