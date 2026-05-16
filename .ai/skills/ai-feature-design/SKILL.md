---
name: ai-feature-design
description: Use when designing or implementing Laravel AI SDK features, prompts, structured outputs, RAG, AI assistants, MCP tools, or human-in-the-loop AI workflows.
---

# AI Feature Design Skill

## Use this when

- adding an AI assistant
- designing an agent
- writing prompts
- using structured output
- adding document Q&A
- planning MCP tools
- deciding what context an AI feature may see
- reviewing whether an AI workflow should require human approval

## Process

1. Define user value.
2. Define the minimum required context.
3. Authorize before collecting context.
4. Minimize and redact data.
5. Decide whether the output is advisory or acted on by code.
6. Prefer structured output when downstream code depends on the result.
7. Define fallback behavior for refusal, malformed output, or provider failure.
8. Audit the AI call at a safe level.
9. Add tests with fakes, fixtures, or regression examples.

## Design rules

- solve one narrow user problem first
- treat structured output as a contract
- version contracts when consumers depend on them
- keep prompts explicit about risk boundaries
- keep tool access narrow
- require human review for risky or ambiguous actions
- fail closed when the model is uncertain in a high-risk path

## Safety rules

AI should not directly:

- perform irreversible actions
- change permissions or authorization state
- delete or mutate authoritative records
- approve high-risk workflows
- bypass human review where risk is material

## Review checklist

- is the feature advisory, assistive, or authoritative?
- is the prompt receiving the minimum safe context?
- is the output schema explicit where code depends on it?
- are provider timeout, refusal, and malformed-output cases handled?
- is human review required where risk is material?
