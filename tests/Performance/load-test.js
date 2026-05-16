/**
 * LedgerFlow — k6 Load Test
 *
 * Simulates realistic user load on the application's public-facing routes.
 *
 * Run:
 *   k6 run tests/Performance/load-test.js
 *   k6 run --vus 10 --duration 30s tests/Performance/load-test.js
 *
 * Install k6: https://k6.io/docs/getting-started/installation/
 */
import http from 'k6/http';
import { check, sleep, group } from 'k6';
import { Rate, Trend } from 'k6/metrics';

export const errorRate = new Rate('errors');
export const ttfb = new Trend('time_to_first_byte');

export const options = {
  stages: [
    { duration: '30s', target: 10 },  // ramp up to 10 VUs
    { duration: '1m',  target: 20 },  // stay at 20 VUs
    { duration: '30s', target: 0 },   // ramp down
  ],
  thresholds: {
    http_req_duration: ['p(95)<500'],  // 95th percentile under 500ms
    errors:            ['rate<0.05'],  // error rate under 5%
    http_req_failed:   ['rate<0.05'],
  },
};

const BASE_URL = __ENV.APP_URL || 'http://localhost';

export default function () {
  group('Public pages', () => {
    // Home page
    const home = http.get(`${BASE_URL}/`);
    check(home, { 'home page 200': r => r.status === 200 });
    ttfb.add(home.timings.waiting);
    errorRate.add(home.status !== 200);

    sleep(0.5);

    // Health check
    const up = http.get(`${BASE_URL}/up`);
    check(up, { 'health check 200': r => r.status === 200 });
    errorRate.add(up.status !== 200);

    sleep(0.5);

    // Dev dashboard (public)
    const dev = http.get(`${BASE_URL}/dev`);
    check(dev, { 'dev dashboard 200': r => r.status === 200 });
    ttfb.add(dev.timings.waiting);
    errorRate.add(dev.status !== 200);

    sleep(0.5);

    // Login page
    const login = http.get(`${BASE_URL}/login`);
    check(login, { 'login page 200': r => r.status === 200 });
    errorRate.add(login.status !== 200);

    sleep(1);
  });

  group('Auth guard verification', () => {
    // Protected routes should redirect (302) for unauthenticated users
    const dashboard = http.get(`${BASE_URL}/dashboard`, { redirects: 0 });
    check(dashboard, { 'dashboard redirects guest': r => r.status === 302 });
    errorRate.add(dashboard.status !== 302);

    const accounts = http.get(`${BASE_URL}/accounts`, { redirects: 0 });
    check(accounts, { 'accounts redirects guest': r => r.status === 302 });
    errorRate.add(accounts.status !== 302);

    sleep(1);
  });
}
