# Quality, Security, and Observability

## Quality

Every important feature should include:

- tests
- clear validation
- authorization
- error handling
- documentation if behavior is not obvious

Favor correctness and repairability over clever implementation.

## Threat modeling

Before shipping risky features, identify:

- trust boundaries
- privileged actions
- attacker-controlled input
- cross-tenant exposure paths
- replay, spoofing, and duplicate-delivery risks
- data exfiltration risks through exports, AI context, or logs

## Security

Consider:

- authentication
- authorization
- tenant isolation
- rate limiting
- CSRF protection
- encrypted secrets
- sensitive logging
- data export controls
- AI prompt data minimization

Security-sensitive paths should have explicit negative tests where practical.

## Observability

Important workflows should be observable.

Examples:

- webhook received
- webhook processed
- duplicate webhook ignored
- reconciliation started
- reconciliation completed
- reconciliation failed
- AI request started
- AI request failed
- queued job failed
- export generated

Prefer correlation IDs or equivalent request/job identifiers so a workflow can be traced across logs, jobs, external calls, and audit records.

## Metrics to consider

Useful metrics:

- transaction count
- failed jobs
- webhook failures
- duplicate webhook count
- reconciliation duration
- open reconciliation issues
- AI request count
- AI error count
- AI cost estimate
- queue latency

## Service expectations

For important workflows, define what good operation means.

Examples:

- acceptable queue latency
- acceptable reconciliation duration
- acceptable webhook failure rate
- acceptable AI timeout behavior

These do not need full enterprise SLO machinery, but they should not be left implicit.

## Audit versus logs

Use audit events for material business history.

Use logs for operational diagnosis.

Do not assume one can substitute for the other.

## Logs

Use structured logs for important events.

Avoid logging:

- secrets
- access tokens
- raw credentials
- unnecessary personal data
- unrelated tenant data

Prefer logs that help answer what happened, why it happened, and what should be repaired next.

## Backpressure and failure visibility

When external systems flood the app, prefer controlled queuing, throttling, and clear failure handling over silent drop behavior.

## Local debugging

Use Telescope locally if installed.

Use Pulse for lightweight application insights.

Use Horizon for queues.
