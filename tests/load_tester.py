"""
Concurrent Load Testing Tool for Lanka News Hub
Executes concurrent request storms across critical endpoints to measure throughput, latency percentiles, and errors.
"""

import time
import urllib.request
import concurrent.futures
import statistics
import sys
import json
import sqlite3

if hasattr(sys.stdout, 'reconfigure'):
    sys.stdout.reconfigure(encoding='utf-8')

BASE_URL = "http://127.0.0.1:8080"
DB_PATH = "storage/preview.sqlite"

def get_target_endpoints():
    db = sqlite3.connect(DB_PATH)
    cur = db.cursor()
    cur.execute("SELECT slug FROM articles WHERE status='published' LIMIT 1")
    row = cur.fetchone()
    slug = row[0] if row else "local-story-1"
    cur.execute("SELECT id FROM classifieds WHERE status='approved' LIMIT 1")
    row = cur.fetchone()
    cid = row[0] if row else "1"
    db.close()

    return [
        '/',
        f'/news/{slug}',
        '/category/local',
        '/search?q=Sri',
        '/classifieds',
        f'/classifieds/{cid}'
    ]

def fetch_worker(endpoint):
    url = BASE_URL + endpoint
    t0 = time.perf_counter()
    req = urllib.request.Request(url, headers={'User-Agent': 'LoadTester/1.0'})
    try:
        with urllib.request.urlopen(req, timeout=5) as resp:
            data = resp.read()
            dt = (time.perf_counter() - t0) * 1000.0
            return True, resp.status, dt, len(data)
    except urllib.error.HTTPError as e:
        dt = (time.perf_counter() - t0) * 1000.0
        return False, e.code, dt, 0
    except Exception as e:
        dt = (time.perf_counter() - t0) * 1000.0
        return False, 500, dt, 0

def run_load_test(concurrency=20, total_requests=200):
    endpoints = get_target_endpoints()
    print("=" * 70)
    print(f"LOAD TEST: {total_requests} requests across {concurrency} concurrent workers")
    print("=" * 70)

    # Distribute requests evenly across endpoints
    work_items = [endpoints[i % len(endpoints)] for i in range(total_requests)]

    latencies = []
    statuses = {}
    successes = 0
    failures = 0

    t_start = time.perf_counter()
    with concurrent.futures.ThreadPoolExecutor(max_workers=concurrency) as executor:
        futures = [executor.submit(fetch_worker, item) for item in work_items]
        for f in concurrent.futures.as_completed(futures):
            ok, status, dt, size = f.result()
            latencies.append(dt)
            statuses[status] = statuses.get(status, 0) + 1
            if ok and status == 200:
                successes += 1
            else:
                failures += 1
    total_time = time.perf_counter() - t_start

    rps = round(total_requests / total_time, 2)
    mean_ms = round(statistics.mean(latencies), 2)
    p50_ms = round(statistics.median(latencies), 2)
    p95_ms = round(sorted(latencies)[int(len(latencies) * 0.95)], 2)
    p99_ms = round(sorted(latencies)[int(len(latencies) * 0.99)], 2)

    print(f"Total Time:      {total_time:.2f} s")
    print(f"Throughput:      {rps} req/sec")
    print(f"Success Rate:    {(successes / total_requests) * 100:.1f}% ({successes}/{total_requests})")
    print(f"Failures:        {failures}")
    print(f"Status Codes:    {statuses}")
    print(f"Mean Latency:    {mean_ms} ms")
    print(f"p50 Latency:     {p50_ms} ms")
    print(f"p95 Latency:     {p95_ms} ms")
    print(f"p99 Latency:     {p99_ms} ms")
    print("=" * 70)

    return {
        'concurrency': concurrency,
        'total_requests': total_requests,
        'total_time_s': round(total_time, 2),
        'rps': rps,
        'mean_ms': mean_ms,
        'p50_ms': p50_ms,
        'p95_ms': p95_ms,
        'p99_ms': p99_ms,
        'success_rate': round((successes / total_requests) * 100, 1),
        'statuses': statuses
    }

if __name__ == '__main__':
    concurrency = int(sys.argv[1]) if len(sys.argv) > 1 else 20
    total = int(sys.argv[2]) if len(sys.argv) > 2 else 200
    run_load_test(concurrency, total)
