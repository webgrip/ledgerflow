---
name: architecture-boundaries
description: Use when designing or reviewing bounded contexts, domain boundaries, aggregates, value objects, application services, ports and adapters, read and write separation, or long-term architecture tradeoffs.
---

# Architecture Boundaries Skill

## Use this when

- designing a new subsystem
- changing module boundaries
- moving business logic out of controllers, jobs, or listeners
- reviewing DDD, clean architecture, or hexagonal tradeoffs
- deciding whether an abstraction or interface is justified

## Working rules

- prefer Laravel conventions unless the domain complexity justifies more structure
- keep framework and vendor concerns at the edges where practical
- use explicit domain language
- protect invariants in one clear place
- add interfaces at real seams, not everywhere
- prefer small architectural moves over broad speculative rewrites

## Review checklist

- is the boundary clear?
- are domain terms explicit and consistent?
- are transactions and consistency handled deliberately?
- is an abstraction present because it protects a real seam?
- can this evolve without forcing a rewrite?
