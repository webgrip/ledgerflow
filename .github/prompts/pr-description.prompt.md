---
mode: agent
description: Generate a PR description with risk, rollout, and rollback sections from the current branch.
---

Produce a PR description for the current branch.

Steps:

1. Get the commit list and diff: `git log --no-merges origin/main..HEAD --pretty=format:'- %s'` and `git diff --stat origin/main...HEAD`.
2. If the branch has no commits ahead of `main`, stop and say so.
3. Use this exact structure:

   ```markdown
   ## What
   <One paragraph. Plain language. No code references in this section.>

   ## Why
   <The forcing function. Link the issue / ADR / brief if one exists in `docs/adrs/` or `planning/briefs/`.>

   ## How
   <Bulleted summary of the approach. Mention boundaries crossed and patterns used.>

   ## Risk
   - **Blast radius:** <which modules / users / data>
   - **Reversibility:** <pure code | data migration | external side effect>
   - **Known unknowns:** <list>

   ## Rollout
   <Feature flag? Background backfill? Expand/contract migration step? Order of deploy?>

   ## Rollback
   <Exact steps to revert safely. If revert is unsafe, say so and link the forward-fix plan.>

   ## Tests
   <New tests added, what they assert, how to run them.>

   ## Screenshots / recordings
   <UI changes only. Otherwise N/A.>

   ## Checklist
   - [ ] Conventional Commits
   - [ ] Tests added / updated and passing locally
   - [ ] ADR added if architectural (see planning/adr-template.md)
   - [ ] Docs updated if behavior changed
   - [ ] No secrets, no PII, no floats for money
   ```

4. Fill every section from the diff. Use file paths only inside the **How** and **Tests** sections.
5. Print the result as a single markdown block ready to paste into the PR body. Do not commit anything.
