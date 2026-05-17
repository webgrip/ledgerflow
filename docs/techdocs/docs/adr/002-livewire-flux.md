# ADR 002: Livewire 4 + Flux UI for the frontend

## Status

Accepted

## Context

LedgerFlow needs a reactive, server-rendered UI that demonstrates modern Laravel patterns without requiring a separate SPA framework.

## Decision

Use Livewire 4 for reactivity and Flux UI as the component library.

## Rationale

- Livewire keeps domain logic in PHP, matching the thin frontend principle.
- Flux provides production-quality components with minimal custom CSS.
- Anonymous Livewire components keep pages self-contained.
- Tailwind CSS v4 is used for bespoke styling.

## Consequences

- Flux requires `FLUX_USERNAME` and `FLUX_LICENSE_KEY` secrets in CI.
- Page components should follow the project's Laravel Boost and Livewire conventions.
