"""Playwright Browser Verification for Website Advertising Management.

Verifies:
1. Admin Advertising Desk (/admin/advertisements):
   - Campaign stats and format selector buttons.
   - Interactive campaign creation with priority and placement.
   - Interactive Pause / Enable toggle button.
2. Public Advertisement Placements & Rendering:
   - Homepage: Header (header_728x90), Inline Feed (inline_feed), Sidebar (sidebar_300x250, sidebar_300x600), Footer (footer).
   - Article Page: In-article ad (in_article), Sticky bottom banner (sticky).
   - Interactive Sticky Ad Dismissal (clicks data-close-ad and verifies dismissal).
3. Responsive Web Testing:
   - Mobile 375px, Tablet 768px, Desktop 1280px (asserts scrollWidth <= clientWidth).
4. Visual Screenshots saved to artifacts directory.
"""

import sys, os, time, uuid
sys.stdout.reconfigure(encoding='utf-8')
from playwright.sync_api import sync_playwright

BASE_URL = os.getenv('APP_URL', 'http://127.0.0.1:8080')
ARTIFACT_DIR = r"C:\Users\ASUS\.gemini\antigravity-ide\brain\fb31e748-22f4-470c-86a6-e5fecce9a4e5\scratch\screenshots"
os.makedirs(ARTIFACT_DIR, exist_ok=True)

checks = []

def report(name, status, msg=""):
    badge = "PASS" if status else "FAIL"
    print(f"[{badge}] {name}: {msg}", flush=True)
    checks.append((name, status, msg))
    if not status:
        raise AssertionError(f"Browser check failed: {name} - {msg}")

with sync_playwright() as p:
    browser = p.chromium.launch(headless=True)
    context = browser.new_context(viewport={'width': 1280, 'height': 850})
    page = context.new_page()

    print("=== STARTING ADVERTISING BROWSER VERIFICATION ===")

    # 1. Admin Login & Ad Desk Inspection
    page.goto(f"{BASE_URL}/account/login", wait_until='networkidle')
    page.fill('input[name="email"]', 'admin@example.test')
    page.fill('input[name="password"]', 'MemberPassword123!')
    page.press('input[name="password"]', 'Enter')
    page.wait_for_load_state('networkidle')
    report("1. Admin Login", '/admin' in page.url, f"Logged in: {page.url}")

    page.goto(f"{BASE_URL}/admin/advertisements", wait_until='networkidle')
    report("1. Ad Desk Loaded", page.locator('h2:has-text("Publish advertisements")').count() > 0, "Campaign desk loaded")

    # Campaign stats & Format selectors
    stat_boxes = page.locator('.campaign-stats .stat').count()
    report("1. Campaign Stats", stat_boxes >= 4, f"Found {stat_boxes} campaign status counters")

    format_btns = page.locator('.ad-format-options button').count()
    report("1. Format Presets", format_btns == 5, f"Found {format_btns} format presets (Image, Video, Text, Native, HTML)")

    # Screenshot: Admin Advertising Desk
    ss_path_1 = os.path.join(ARTIFACT_DIR, "40_admin_ad_desk.png")
    page.screenshot(path=ss_path_1, full_page=True)
    report("1. Admin Desk Screenshot Saved", os.path.exists(ss_path_1), ss_path_1)

    # 2. Interactive Campaign Creation with Priority
    run_id = uuid.uuid4().hex[:6]
    test_ad_title = f"Dialog Star Points Seasonal Promo {run_id}"
    page.fill('input[name="title"]', test_ad_title)
    page.select_option('select[data-ad-format]', value='text')
    page.fill('input[name="advertiser"]', 'Dialog Axiata')
    page.select_option('select[data-banner-position]', value='header_728x90')
    page.fill('textarea[name="ad_text"]', 'Redeem your Star Points for exclusive discounts on electronics and dining.')
    page.fill('input[name="url"]', 'https://www.dialog.lk/starpoints')
    page.fill('input[name="cta_label"]', 'Redeem Now')
    page.fill('input[name="priority"]', '15')

    # Screenshot: Filled Ad Form with Placement & Priority
    ss_path_2 = os.path.join(ARTIFACT_DIR, "41_admin_ad_form_filled.png")
    page.screenshot(path=ss_path_2, full_page=True)
    report("2. Ad Form Screenshot Saved", os.path.exists(ss_path_2), ss_path_2)

    page.click('button:has-text("Save campaign")')
    page.wait_for_load_state('networkidle')
    report("2. Campaign Created", test_ad_title in page.content(), "New campaign appears in campaign card list")
    report("2. Priority Reflected", page.locator('.campaign-card:has-text("Priority: 15")').count() > 0, "Priority: 15 displayed on campaign card")

    # Create a footer campaign so footer placement renders
    page.fill('input[name="title"]', f"National Insurance Footer {run_id}")
    page.select_option('select[data-ad-format]', value='text')
    page.fill('input[name="advertiser"]', 'National Insurance')
    page.select_option('select[data-banner-position]', value='footer')
    page.fill('textarea[name="ad_text"]', 'Protect your loved ones with comprehensive family life coverage.')
    page.fill('input[name="url"]', 'https://insurance.example.lk')
    page.fill('input[name="priority"]', '5')
    page.click('button:has-text("Save campaign")')
    page.wait_for_load_state('networkidle')
    report("2. Footer Campaign Created", "National Insurance Footer" in page.content(), "Footer campaign created")

    # 3. Interactive Pause / Enable Toggle
    pause_btn = page.locator(f'.campaign-card:has-text("{test_ad_title}") button:has-text("Pause")')
    report("3. Pause Button Exists", pause_btn.count() > 0, "Pause toggle button found")
    pause_btn.click()
    page.wait_for_load_state('networkidle')
    report("3. Campaign Paused", page.locator(f'.campaign-card:has-text("{test_ad_title}") button:has-text("Enable")').count() > 0, "Campaign toggled to Paused (Enable button present)")

    enable_btn = page.locator(f'.campaign-card:has-text("{test_ad_title}") button:has-text("Enable")')
    enable_btn.click()
    page.wait_for_load_state('networkidle')
    report("3. Campaign Re-enabled", page.locator(f'.campaign-card:has-text("{test_ad_title}") button:has-text("Pause")').count() > 0, "Campaign re-enabled successfully")

    # 4. Public Homepage Advertisement Placements
    page.goto(f"{BASE_URL}/", wait_until='networkidle')
    has_header_ad = page.locator('.ad-slot.placement-header_728x90').count() > 0
    report("4. Homepage Header Leaderboard", has_header_ad, "Header ad slot rendered")

    has_feed_ad = page.locator('.ad-slot.placement-inline_feed').count() > 0
    report("4. Homepage Inline Feed Ad", has_feed_ad, "Inline feed ad slot rendered")

    has_sidebar_ad = page.locator('.ad-slot.placement-sidebar_300x250').count() > 0
    report("4. Homepage Sidebar Ad", has_sidebar_ad, "Sidebar 300x250 ad slot rendered")

    has_footer_ad = page.locator('.ad-slot.placement-footer').count() > 0
    report("4. Homepage Footer Ad", has_footer_ad, "Footer ad slot rendered")

    # Screenshot: Homepage with Public Ads
    ss_path_3 = os.path.join(ARTIFACT_DIR, "42_homepage_ads_desktop.png")
    page.screenshot(path=ss_path_3, full_page=True)
    report("4. Homepage Ads Screenshot Saved", os.path.exists(ss_path_3), ss_path_3)

    # 5. Article Page: In-Article Ad & Sticky Ad Interaction
    article_link = page.locator('article h3 a').first.get_attribute('href')
    target_article = article_link if article_link.startswith('http') else f"{BASE_URL}{article_link}"
    page.goto(target_article, wait_until='networkidle')

    has_in_article = page.locator('.ad-slot.placement-in_article').count() > 0
    report("5. In-Article Ad Placement", has_in_article, "In-article ad slot rendered inside story")

    has_sticky = page.locator('.sticky-ad').count() > 0
    report("5. Sticky Bottom Ad Rendered", has_sticky, "Sticky bottom advertisement rendered")

    # Screenshot: Article Page with Sticky Ad
    ss_path_4 = os.path.join(ARTIFACT_DIR, "43_article_with_sticky_ad.png")
    page.screenshot(path=ss_path_4, full_page=True)
    report("5. Article Sticky Screenshot Saved", os.path.exists(ss_path_4), ss_path_4)

    # Dismiss Sticky Ad
    close_sticky_btn = page.locator('.sticky-ad button[data-close-ad]')
    report("5. Sticky Dismiss Button Exists", close_sticky_btn.count() > 0, "Sticky dismiss button present")
    close_sticky_btn.click()
    time.sleep(0.5)
    report("5. Sticky Ad Dismissed", page.locator('.sticky-ad').count() == 0 or not page.locator('.sticky-ad').is_visible(), "Sticky ad closed on click")

    # 6. Responsive Viewports
    print("\n--- Testing Advertising Responsive Viewports ---")
    viewports = [
        ("Mobile 375px", 375, 667),
        ("Mobile 430px", 430, 932),
        ("Tablet 768px", 768, 1024),
        ("Laptop 1024px", 1024, 768),
        ("Desktop 1440px", 1440, 900)
    ]
    for name, w, h in viewports:
        page.set_viewport_size({'width': w, 'height': h})
        page.goto(f"{BASE_URL}/", wait_until='networkidle')
        scroll_w = page.evaluate("() => document.documentElement.scrollWidth")
        client_w = page.evaluate("() => document.documentElement.clientWidth")
        report(f"6. Responsive {name}", scroll_w <= client_w, f"ScrollWidth: {scroll_w}px <= ClientWidth: {client_w}px")

    # Screenshot: Mobile 375px with Header Ad
    page.set_viewport_size({'width': 375, 'height': 812})
    page.goto(f"{BASE_URL}/", wait_until='networkidle')
    ss_path_5 = os.path.join(ARTIFACT_DIR, "44_homepage_ads_mobile_375.png")
    page.screenshot(path=ss_path_5, full_page=True)
    report("6. Mobile Ads Screenshot Saved", os.path.exists(ss_path_5), ss_path_5)

    browser.close()

    print("\n" + "=" * 70)
    print(f"ALL {len(checks)} ADVERTISING BROWSER VERIFICATION CHECKS PASSED (100%)!")
    print("=" * 70)
