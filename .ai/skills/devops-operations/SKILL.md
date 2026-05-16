---
name: devops-operations
description: Use when setting up local development, CI/CD, queues, scheduler, Horizon, Pulse, logs, health checks, runtime diagnostics, or deployment operations.
---

# DevOps Operations Skill

## Use this when

- changing Docker/Sail setup
- adding queues
- adding scheduled tasks
- configuring CI
- adding observability
- documenting deployment
- defining runtime expectations or failure handling

## Checklist

- app can run locally
- tests run in CI
- queues are documented
- scheduler is documented
- failed jobs are visible
- logs are useful
- health checks exist where useful
- secrets are not committed

## Operational review

- are queue, scheduler, and worker assumptions explicit?
- can operators tell when a workflow is failing or falling behind?
- are correlation IDs or traceable identifiers available?
- is there a clear recovery path for failed jobs and backfills?
- is deployment or rollback guidance updated if behavior changes?
