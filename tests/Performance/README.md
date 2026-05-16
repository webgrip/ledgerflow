# Performance Tests — k6

## Install k6

```bash
# macOS
brew install k6

# Linux
sudo gpg -k
sudo gpg --no-default-keyring \
  --keyring /usr/share/keyrings/k6-archive-keyring.gpg \
  --keyserver hkp://keyserver.ubuntu.com:80 \
  --recv-keys C5AD17C747E3415A3642D57D77C6C491D6AC1D69
echo "deb [signed-by=/usr/share/keyrings/k6-archive-keyring.gpg] https://dl.k6.io/deb stable main" \
  | sudo tee /etc/apt/sources.list.d/k6.list
sudo apt-get update && sudo apt-get install k6
```

## Run

```bash
# Load test (ramp to 20 VUs over 2 minutes)
k6 run tests/Performance/load-test.js

# Stress test (ramp to 200 VUs — use with caution)
k6 run tests/Performance/stress-test.js

# Against a specific URL
APP_URL=http://localhost:8003 k6 run tests/Performance/load-test.js

# With HTML report
k6 run --out json=results.json tests/Performance/load-test.js
```

## Files

| File | Purpose | Peak VUs |
|------|---------|----------|
| `load-test.js` | Normal load (public + auth guard pages) | 20 |
| `stress-test.js` | Find breaking point | 200 |

## Thresholds

- `p(95) < 500ms` under normal load
- `error rate < 5%` under normal load
- `p(99) < 2s` under stress
- `error rate < 20%` under stress (5xx only)

## What is tested

- `GET /` (home page)
- `GET /up` (health check)
- `GET /dev` (dev dashboard — DB-heavy Livewire page)
- `GET /login`, `GET /register`
- Auth guard: `GET /dashboard`, `GET /accounts` must return 302 (not 200 or 500)
