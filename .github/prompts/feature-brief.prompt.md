---
mode: agent
description: Turn a rough idea into a structured feature brief using the repo template.
---

You will produce a feature brief using [planning/feature-brief-template.md](../../planning/feature-brief-template.md).

Inputs:

- ${input:idea:Describe the feature in 1–3 sentences}

Steps:

1. Read [planning/feature-brief-template.md](../../planning/feature-brief-template.md) and use its exact section structure.
2. Read [.ai/guidelines/01-project-purpose.md](../../.ai/guidelines/01-project-purpose.md) and [.ai/guidelines/04-fintech-domain-principles.md](../../.ai/guidelines/04-fintech-domain-principles.md) so the brief uses correct LedgerFlow terminology (ubiquitous language).
3. Ask up to 3 clarifying questions only if the idea is missing a user, a triggering event, or a definition of success. Otherwise proceed.
4. Write the brief to `planning/briefs/${kebab-title}.md`. Create the folder if missing.
5. Explicitly fill: user / job-to-be-done, scope (in/out), domain impact (which modules under `app/Domain/**`), data shape changes, risks, rollout strategy, and test plan headings.
6. End with a checklist of follow-up artifacts the team will likely need (ADR? migration? new skill? new contract test?).

Never assume monetary, regulatory, or jurisdictional details. Mark them TBD.
