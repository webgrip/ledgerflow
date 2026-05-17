# ADR 003: Laravel AI SDK for all AI features

## Status

Accepted

## Context

LedgerFlow includes transaction explanation, categorization, account activity summarization, and reconciliation analysis. These features need a consistent, testable, provider-agnostic AI layer.

## Decision

Use `laravel/ai`, the official Laravel AI SDK, for all AI agent features.

## Rationale

- Provider-agnostic configuration supports OpenAI, Anthropic, Gemini, and other providers.
- Agent fakes make feature tests deterministic.
- Structured output supports machine-readable AI responses.
- Streaming, queueing, and broadcasting are available for future async features.

## Consequences

- AI features require valid provider credentials in `.env`.
- Structured output tests should fake arrays rather than JSON strings.
- Agent classes live in `app/Ai/Agents/`.
