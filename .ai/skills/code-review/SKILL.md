---
name: code-review
description: Use when reviewing changes for correctness, conventions, domain safety, transactions, security, tests, observability, maintainability, or rollback risk.
---

# Code Review Skill

## Review order

1. user value
2. correctness
3. domain safety
4. authorization and tenant isolation
5. tests
6. framework conventions
7. observability
8. maintainability
9. performance
10. delivery and rollback safety

## Output format

Use:

```text
Summary
Critical issues
High-priority issues
Medium-priority issues
Low-priority issues
Positive notes
Suggested follow-up
```

## Questions

- Does this solve the intended problem?
- Is it scoped to the planned work?
- Are transaction boundaries and side effects safe?
- Are concurrency and duplicate-delivery risks handled?
- Are important edge cases tested?
- Is authorization correct?
- Is tenant data protected?
- Is AI output safe and advisory where required?
- Is the code idiomatic for the framework in use?

## Review checklist

- are domain invariants enforced in one clear place?
- are framework and vendor concerns kept at the edge where useful?
- does the change introduce a contract or migration risk?
- is rollback or disablement obvious if the change misbehaves?
