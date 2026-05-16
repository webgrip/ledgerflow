# ADR 002: Livewire 4 + Flux UI for the frontend

## Status
Accepted

## Context
LedgerFlow needs a reactive, server-rendered UI that demonstrates modern Laravel patterns without requiring a separate SPA framework.

## Decision
Use Livewire 4 for reactivity and Flux UI (livewire/flux) as the component library.

## Rationale
- Livewire keeps all domain logic in PHP, matching the "thin frontend" principle
- Flux provides production-quality components (modals, data tables, forms) with minimal custom CSS
- Anonymous Livewire components (blade files with inline classes) keep pages self-contained
- Tailwind CSS v4 is used for any bespoke styling

## Alternatives considered
### Inertia + Vue/React
Better for complex client-side interactions, but adds a JS ecosystem build step and splits domain logic across two languages.

### Livewire without Flux
Possible but requires building a component library from scratch or using a less polished alternative.

## Consequences
- Flux requires a license key (`FLUX_USERNAME` + `FLUX_LICENSE_KEY` secrets in CI)
- All page components follow the `⚡{name}.blade.php` convention from Laravel Boost
