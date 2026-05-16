/**
 * LedgerFlow — k6 Stress Test
 *
 * Pushes the application well beyond normal load to find breaking points.
 * Focuses on public endpoints only (no auth overhead).
 *
 * Run:
 *   k6 run tests/Performance/stress-test.js
 *
 * WARNING: This test will generate significant traffic.
 * Only run against a dedicated test environment.
 */
import http from 'k6/http';
import { check, sleep } from 'k6';
import { Rate } from 'k6/metrics';

export const errorRate = new Rate('errors');

export const options = {
  stages: [
    { duration: '1m',  target: 50  },  // ramp up aggressively
    { duration: '2m',  target: 100 },  // stress at 100 VUs
    { duration: '1m',  target: 200 },  // push further
    { duration: '2m',  target: 200 },  // sustain peak
    { duration: '1m',  target: 0   },  // ramp down
  ],
  thresholds: {
    // We expect degradation; alert if error rate exceeds 20%
    errors:          ['rate<0.2'],
    http_req_failed: ['rate<0.2'],
    // p99 under 2s at peak
    http_req_duration: ['p(99)<2000'],
  },
};

const BASE_URL = __ENV.APP_URL || 'http://localhost:8003';

export default function () {
  // Mix of the cheapest-to-render public pages
  const endpoints = [
    `${BASE_URL}/`,
    `${BASE_URL}/up`,
    `${BASE_URL}/login`,
    `${BASE_URL}/register`,
    `${BASE_URL}/dev`,
  ];

  const url = endpoints[Math.floor(Math.random() * endpoints.length)];
  const res = http.get(url);

  check(res, {
    'status is 2xx or 3xx': r => r.status >= 200 && r.status < 400,
    'response time < 1s':   r => r.timings.duration < 1000,
  });

  errorRate.add(res.status >= 500); // only 5xx counts as error

  sleep(Math.random() * 0.5); // random think time 0–500ms
}
