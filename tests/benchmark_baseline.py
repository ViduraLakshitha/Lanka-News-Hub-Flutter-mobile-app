"""
Performance Benchmark Tool for Lanka News Hub
Measures response time, status, and throughput across critical web endpoints.
"""

import time
import urllib.request
import urllib.parse
import http.cookiejar
import json
import statistics
import sys
import sqlite3

if hasattr(sys.stdout, 'reconfigure'):
    sys.stdout.reconfigure(encoding='utf-8')

BASE_URL = "http://127.0.0.1:8080"
DB_PATH = "storage/preview.sqlite"

def get_test_urls():
    # Fetch a sample article slug and classified id from the database
    db = sqlite3.connect(DB_PATH)
    cur = db.cursor()
    cur.execute("SELECT slug FROM articles WHERE status='published' LIMIT 1")
    row = cur.fetchone()
    article_slug = row[0] if row else "local-story-1"

    cur.execute("SELECT id FROM classifieds WHERE status='approved' LIMIT 1")
    row = cur.fetchone()
    classified_id = row[0] if row else "1"

    cur.execute("SELECT slug FROM categories WHERE status='active' LIMIT 1")
    row = cur.fetchone()
    category_slug = row[0] if row else "local"
    db.close()

    return {
        'homepage': '/',
        'article': f'/news/{article_slug}',
        'category': f'/category/{category_slug}',
        'search': '/search?q=Sri',
        'classified_list': '/classifieds',
        'classified_detail': f'/classifieds/{classified_id}',
    }

def benchmark_endpoint(url, iterations=20, cookie_jar=None):
    opener = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(cookie_jar or http.cookiejar.CookieJar()))
    durations = []
    statuses = []
    sizes = []

    full_url = BASE_URL + url if url.startswith('/') else url
    req = urllib.request.Request(full_url, headers={
        'User-Agent': 'Benchmark/1.0',
        'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8'
    })

    # Warm-up request
    try:
        with opener.open(req, timeout=10) as resp:
            resp.read()
    except Exception as e:
        pass

    for _ in range(iterations):
        t0 = time.perf_counter()
        try:
            with opener.open(req, timeout=10) as resp:
                data = resp.read()
                durations.append((time.perf_counter() - t0) * 1000.0) # ms
                statuses.append(resp.status)
                sizes.append(len(data))
        except urllib.error.HTTPError as e:
            durations.append((time.perf_counter() - t0) * 1000.0)
            statuses.append(e.code)
            sizes.append(len(e.read()))
        except Exception as e:
            durations.append((time.perf_counter() - t0) * 1000.0)
            statuses.append(500)
            sizes.append(0)

    return {
        'url': url,
        'count': iterations,
        'mean_ms': round(statistics.mean(durations), 2),
        'median_ms': round(statistics.median(durations), 2),
        'p95_ms': round(sorted(durations)[int(len(durations) * 0.95)], 2),
        'min_ms': round(min(durations), 2),
        'max_ms': round(max(durations), 2),
        'avg_size_kb': round(statistics.mean(sizes) / 1024.0, 2),
        'success_rate': round(sum(1 for s in statuses if s == 200) / len(statuses) * 100, 1)
    }

def get_admin_jar():
    jar = http.cookiejar.CookieJar()
    opener = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(jar))
    # Fetch login form
    with opener.open(f"{BASE_URL}/account/login") as resp:
        html = resp.read().decode('utf-8')
    import re
    token = re.search(r'name="_token"\s+value="([a-f0-9]{64})"', html).group(1)
    data = urllib.parse.urlencode({'_token': token, 'email': 'admin@example.test', 'password': 'MemberPassword123!'}).encode('utf-8')
    req = urllib.request.Request(f"{BASE_URL}/account/login", data=data, headers={'Content-Type': 'application/x-www-form-urlencoded'})
    opener.open(req)
    return jar

def run_benchmarks(label="BASELINE"):
    print("=" * 70)
    print(f"BENCHMARK RUN: {label}")
    print("=" * 70)
    endpoints = get_test_urls()
    results = {}

    print("\n[PUBLIC ENDPOINTS]")
    for name, path in endpoints.items():
        res = benchmark_endpoint(path, iterations=25)
        results[name] = res
        print(f"  {name:<18} | {res['mean_ms']:>6.2f} ms mean | {res['p95_ms']:>6.2f} ms p95 | {res['avg_size_kb']:>6.1f} KB | {res['success_rate']}% ok")

    print("\n[ADMIN / NEWSROOM ENDPOINTS]")
    try:
        admin_jar = get_admin_jar()
        admin_endpoints = {
            'admin_dashboard': '/admin',
            'admin_articles': '/admin/articles',
            'admin_media': '/admin/media',
            'admin_classifieds': '/admin/classifieds',
            'admin_advertisements': '/admin/advertisements',
        }
        for name, path in admin_endpoints.items():
            res = benchmark_endpoint(path, iterations=20, cookie_jar=admin_jar)
            results[name] = res
            print(f"  {name:<18} | {res['mean_ms']:>6.2f} ms mean | {res['p95_ms']:>6.2f} ms p95 | {res['avg_size_kb']:>6.1f} KB | {res['success_rate']}% ok")
    except Exception as e:
        print(f"  Admin benchmark error: {e}")

    # Save to JSON
    filename = f"storage/benchmark_{label.lower()}.json"
    with open(filename, 'w', encoding='utf-8') as f:
        json.dump(results, f, indent=2)
    print(f"\nSaved results to {filename}")
    return results

if __name__ == '__main__':
    label = sys.argv[1] if len(sys.argv) > 1 else "BASELINE"
    run_benchmarks(label)
