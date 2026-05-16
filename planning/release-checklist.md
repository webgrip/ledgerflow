# Release Checklist

Use this before important demo or portfolio releases.

## Code quality

- [ ] tests pass
- [ ] formatter passes
- [ ] static analysis passes
- [ ] no obvious dead code
- [ ] no debug dumps
- [ ] no committed secrets

## Database

- [ ] migrations run cleanly
- [ ] seed/demo data works
- [ ] destructive migrations reviewed
- [ ] indexes considered

## Security

- [ ] authorization checked
- [ ] tenant isolation considered
- [ ] sensitive logs reviewed
- [ ] AI prompt data reviewed
- [ ] rate limits considered
- [ ] dependencies reviewed

## Operations

- [ ] queues work
- [ ] scheduled tasks documented
- [ ] failed jobs visible
- [ ] logs useful
- [ ] health check works

## Docs

- [ ] README updated
- [ ] setup guide accurate
- [ ] roadmap updated
- [ ] demo script updated
- [ ] architecture notes updated

## Demo

- [ ] app can be installed
- [ ] app can be run locally
- [ ] demo user exists
- [ ] demo data exists
- [ ] main workflow works
