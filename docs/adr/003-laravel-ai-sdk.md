# ADR 003: Laravel AI SDK for all AI features

## Status
Accepted

## Context
LedgerFlow includes several AI features: transaction explanation, categorization, account activity summarization, and reconciliation analysis. We need a consistent, testable, provider-agnostic approach.

## Decision
Use `laravel/ai` (the official Laravel AI SDK) for all AI agent features.

## Rationale
- Provider-agnostic: can switch between OpenAI, Anthropic, Gemini, etc. via config
- First-class testing support: `AgentClass::fake(['response'])` for unit tests without real API calls
- Structured output (`HasStructuredOutput` + `schema()`) for machine-readable AI responses
- Built-in streaming, queueing, and broadcasting for future async features
- `#[UseCheapestModel]` attribute allows cost-optimized routing

## Key patterns
- Agents implement `Agent` interface + use `Promptable` trait
- Structured agents implement `HasStructuredOutput` and return arrays (not strings)
- When faking structured output, pass arrays: `AgentClass::fake([['key' => 'val']])` not JSON strings
- Agent classes live in `app/Ai/Agents/`

## Alternatives considered
### Prism PHP
Also installed (`prism-php/prism`) but `laravel/ai` is the official first-party SDK with better Laravel integration.

### Direct OpenAI PHP client
Tightly coupled to a single provider; loses testability and provider failover.

## Consequences
- AI features require a valid provider API key in `.env` (`OPENAI_API_KEY` or equivalent)
- Structured output requires passing arrays to `fake()`, not JSON strings
