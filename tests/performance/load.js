/**
 * LedgerFlow — k6 Performance Test
 *
 * Prerequisites:
 *   brew install k6   (macOS)
 *   apt install k6    (Linux)
 *
 * Run:
 *   k6 run tests/performance/load.js
 *   k6 run --vus 50 --duration 30s tests/performance/load.js
 *
 * Dashboard (requires k6 v0.43+):
 *   K6_WEB_DASHBOARD=true k6 run tests/performance/load.js
 */

import http from 'k6/http';
import { check, sleep, group } from 'k6';
import { Trend, Rate } from 'k6/metrics';

// ── Targets ───────────────────────────────────────────────────────────────────

const BASE_URL = __ENV.BASE_URL || 'http://localhost';

// Custom metrics
const loginDuration = new Trend('login_duration', true);
const accountsPageDuration = new Trend('accounts_page_duration', true);
const webhookDuration = new Trend('webhook_duration', true);
const errorRate = new Rate('error_rate');

// ── Scenarios ─────────────────────────────────────────────────────────────────

export const options = {
    scenarios: {
        health_check: {
            executor: 'constant-vus',
            vus: 1,
            duration: '10s',
            tags: { scenario: 'health' },
        },
        authenticated_browse: {
            executor: 'ramping-vus',
            startVUs: 0,
            stages: [
                { duration: '10s', target: 10 },  // ramp up to 10 VUs
                { duration: '20s', target: 10 },  // hold
                { duration: '10s', target: 0 },   // ramp down
            ],
            tags: { scenario: 'browse' },
        },
        webhook_burst: {
            executor: 'constant-arrival-rate',
            rate: 20,
            timeUnit: '1s',
            duration: '15s',
            preAllocatedVUs: 10,
            tags: { scenario: 'webhooks' },
        },
    },
    thresholds: {
        // 95th percentile response time thresholds
        'http_req_duration{scenario:health}': ['p(95)<200'],
        'http_req_duration{scenario:browse}': ['p(95)<2000'],
        'http_req_duration{scenario:webhooks}': ['p(95)<500'],
        // Error rate < 5%
        error_rate: ['rate<0.05'],
    },
};

// ── Helpers ───────────────────────────────────────────────────────────────────

function login(email, password) {
    const csrfRes = http.get(`${BASE_URL}/login`);
    const csrfToken = csrfRes.html().find('meta[name=csrf-token]').attr('content');

    const start = Date.now();
    const res = http.post(
        `${BASE_URL}/login`,
        { email, password, _token: csrfToken },
        { redirects: 0 }
    );
    loginDuration.add(Date.now() - start);

    return {
        session: res.headers['Set-Cookie'],
        ok: res.status === 302,
    };
}

// ── Scenarios ─────────────────────────────────────────────────────────────────

export default function () {
    const scenario = __ENV.SCENARIO || 'browse';

    if (__ITER === 0 && scenario === 'health') {
        group('Health check', () => {
            const res = http.get(`${BASE_URL}/health`);
            check(res, {
                'health returns 200': (r) => r.status === 200,
                'status is healthy': (r) => {
                    try {
                        return JSON.parse(r.body).status === 'healthy';
                    } catch {
                        return false;
                    }
                },
            });
            errorRate.add(res.status !== 200);
        });
        return;
    }

    group('Unauthenticated — public pages', () => {
        const health = http.get(`${BASE_URL}/health`);
        check(health, { 'health 200': (r) => r.status === 200 });
        errorRate.add(health.status !== 200);
        sleep(0.1);
    });

    group('Webhook ingestion', () => {
        const payload = JSON.stringify({
            id: `evt_k6_${__VU}_${__ITER}`,
            type: 'payment_intent.succeeded',
            data: { object: { amount: 1000, currency: 'eur' } },
        });

        const start = Date.now();
        const res = http.post(`${BASE_URL}/webhooks/stripe`, payload, {
            headers: { 'Content-Type': 'application/json' },
        });
        webhookDuration.add(Date.now() - start);

        const ok = res.status === 202 || res.status === 200; // 200 = duplicate
        check(res, { 'webhook accepted': () => ok });
        errorRate.add(!ok);
        sleep(0.05);
    });

    sleep(1);
}

// ── Summary ───────────────────────────────────────────────────────────────────

export function handleSummary(data) {
    return {
        'tests/performance/results.json': JSON.stringify(data, null, 2),
        stdout: textSummary(data, { indent: '  ', enableColors: true }),
    };
}

function textSummary(data, opts) {
    return `
LedgerFlow Performance Summary
================================
Health p95:   ${data.metrics['http_req_duration{scenario:health}']?.values?.['p(95)']?.toFixed(0) ?? 'n/a'} ms
Browse p95:   ${data.metrics['http_req_duration{scenario:browse}']?.values?.['p(95)']?.toFixed(0) ?? 'n/a'} ms
Webhook p95:  ${data.metrics['http_req_duration{scenario:webhooks}']?.values?.['p(95)']?.toFixed(0) ?? 'n/a'} ms
Error rate:   ${((data.metrics['error_rate']?.values?.rate ?? 0) * 100).toFixed(2)}%
Total reqs:   ${data.metrics['http_reqs']?.values?.count ?? 0}
`;
}
