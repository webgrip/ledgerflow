---
applyTo: "app/Livewire/**/*.php,resources/views/livewire/**/*.blade.php,resources/views/**/*.blade.php"
---

# Livewire & Blade UI

Apply silently to any Livewire component or Blade view edit.

- Keep state server-side. Validate and authorize inside the component action exactly like an HTTP request.
- Use Flux components ([livewire/flux](https://fluxui.dev)) before hand-rolling markup. Check sibling components for existing patterns.
- Loading, empty, error, and success states must all be represented in the UI — not just the happy path.
- Show units, signs, and totals unambiguously. Distinguish intermediate vs final state (e.g. pending vs posted) visually.
- Use Tailwind utility classes; no inline styles. Use `dark:` variants where the surrounding component already does.
- Use Alpine sparingly for client-only concerns (open/closed, focus). Do not reimplement server state on the client.
- Use `wire:model.live` only when you actually need live updates; otherwise debounce or use `wire:model.blur`.
- Use Heroicons or Lucide via Flux's icon component. Do not paste raw SVG.
- For any new interactive component, add a Livewire feature test in `tests/Feature/Livewire/`.
