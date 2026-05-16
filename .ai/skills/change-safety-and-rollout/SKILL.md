---
name: change-safety-and-rollout
description: Use when changing schema, backfills, public contracts, rollout plans, feature flags, migration sequencing, or rollback behavior.
---

# Change Safety and Rollout Skill

## Use this when

- writing a risky migration
- planning a backfill
- changing an API, webhook, export, or AI schema
- introducing a feature flag
- deciding deployment or rollback steps

## Checklist

- is the change expandable and reversible?
- does the deploy require synchronized timing?
- is the backfill resumable and observable?
- are contract changes versioned or compatibility-safe?
- can the feature be disabled if it misbehaves?
- does the documentation explain rollout and rollback expectations?
