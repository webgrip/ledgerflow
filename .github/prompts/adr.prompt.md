---
mode: agent
description: Draft an Architectural Decision Record from the current change or a described decision.
---

You will produce an ADR using [planning/adr-template.md](../../planning/adr-template.md) as the structure.

Inputs:

- ${input:title:Short, decision-shaped title (e.g. "Use outbox for provider events")}
- ${input:context:What problem or forcing function triggered this decision?}

Steps:

1. Read [planning/adr-template.md](../../planning/adr-template.md) and reuse its sections verbatim.
2. Inspect the current git diff (`git diff --stat origin/main...HEAD` and `git diff origin/main...HEAD`) to ground the decision in real changes. If there is no diff, ask the user for the change scope before continuing.
3. Cross-reference [.ai/guidelines/02-engineering-principles.md](../../.ai/guidelines/02-engineering-principles.md) and [.ai/guidelines/07-change-safety-and-delivery.md](../../.ai/guidelines/07-change-safety-and-delivery.md) for ADR triggers and language.
4. Produce the ADR as a new file under `docs/adrs/` named `NNNN-${kebab-title}.md`, where `NNNN` is the next free 4-digit number.
5. Fill every section. If a section genuinely does not apply, write "N/A — <one-line reason>" rather than removing it.
6. End with a short summary in chat: the file path, the decision in one sentence, and the top two consequences.

Do not invent constraints, deadlines, or stakeholders. If unknown, mark as TBD and list the question to resolve.
