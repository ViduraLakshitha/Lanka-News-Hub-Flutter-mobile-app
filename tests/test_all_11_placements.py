import sys, os, time, sqlite3
if hasattr(sys.stdout, 'reconfigure'):
    sys.stdout.reconfigure(encoding='utf-8')
from playwright.sync_api import sync_playwright

BASE_URL = os.getenv('APP_URL', 'http://127.0.0.1:8080')
DB_PATH = 'storage/preview.sqlite'
SCREENSHOT_DIR = 'scratch/screenshots'
os.makedirs(SCREENSHOT_DIR, exist_ok=True)

print("=" * 70)
print("TESTING ALL 11 ADVERTISEMENT PLACEMENT LOCATIONS & ADMIN INTERACTIVITY")
print("=" * 70)

# 1. Connect to SQLite and ensure active ads exist for ALL 11 placements
conn = sqlite3.connect(DB_PATH)
cur = conn.cursor()

placements_to_seed = [
    ('SriLankan Airlines · Discover Paradise', 'header_728x90', 'image', 'SriLankan Airlines', None, None, 'https://www.srilankan.com', 'https://images.unsplash.com/photo-1436491865332-7a61a109cc05?w=1200&h=150&fit=crop&q=80', 10),
    ('Ceylon Pure Tea · Single Origin Excellence', 'top_left', 'image', 'Tea Board', None, None, 'https://www.pureceylontea.com', 'https://images.unsplash.com/photo-1576092768241-dec231879fc3?w=600&h=120&fit=crop&q=80', 10),
    ('Colombo Port City · South Asia Hub', 'top_right', 'image', 'Port City', None, None, 'https://www.portcitycolombo.lk', 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=600&h=120&fit=crop&q=80', 10),
    ('Commercial Bank Flash · Digital Banking', 'homepage_mid_728x90', 'image', 'Commercial Bank', None, None, 'https://www.combank.lk', 'https://images.unsplash.com/photo-1559526324-4b87b5e36e44?w=1200&h=150&fit=crop&q=80', 10),
    ('Dialog 5G Home Broadband · Blazing Speed', 'inline_feed', 'native', 'Dialog Axiata', 'Experience blazing speeds with Dialog 5G across Sri Lanka.', 'Get 5G Router', 'https://www.dialog.lk', 'https://images.unsplash.com/photo-1519389950473-47ba0277781c?w=400&h=300&fit=crop&q=80', 10),
    ('Cinnamon Grand Colombo · Luxury Stays', 'sidebar_300x250', 'image', 'Cinnamon Hotels', None, None, 'https://www.cinnamonhotels.com', 'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=350&h=280&fit=crop&q=80', 10),
    ('Bank of Ceylon · Bankers to the Nation', 'sidebar_300x600', 'image', 'Bank of Ceylon', None, None, 'https://www.boc.lk', 'https://images.unsplash.com/photo-1563986768609-322da13575f3?w=350&h=700&fit=crop&q=80', 10),
    ('Hayleys Solar · Clean Green Energy', 'in_article', 'native', 'Hayleys Solar', 'Reduce electricity bills by up to 90% with Tier-1 solar systems.', 'Free Site Survey', 'https://www.hayleysfentons.com', 'https://images.unsplash.com/photo-1509391365360-2e959784a276?w=400&h=300&fit=crop&q=80', 10),
    ('SLT-MOBITEL · National ICT Solutions', 'footer', 'image', 'SLT-MOBITEL', None, None, 'https://www.sltmobitel.lk', 'https://images.unsplash.com/photo-1451187580459-43490279c0fa?w=1200&h=150&fit=crop&q=80', 10),
    ('Sri Lanka Tourism · You Will Come Back', 'sticky', 'image', 'Sri Lanka Tourism', None, None, 'https://www.srilanka.travel', 'https://images.unsplash.com/photo-1546708973-b339540b5162?w=800&h=120&fit=crop&q=80', 10),
    ('LPL 2026 · Exclusive Match Tickets', 'popup', 'native', 'SLC', 'Book tickets online for LPL 2026 under the floodlights in Colombo.', 'Book Tickets', 'https://www.srilankacricket.lk', 'https://images.unsplash.com/photo-1540747913346-19e32dc3e97e?w=500&h=350&fit=crop&q=80', 10),
]

for title, pos, fmt, adv, txt, cta, url, img, pri in placements_to_seed:
    cur.execute("SELECT id FROM advertisements WHERE position=? AND is_active=1", (pos,))
    row = cur.fetchone()
    if not row:
        cur.execute("""
            INSERT INTO advertisements (title, position, ad_format, advertiser, ad_text, cta_label, url, image_path, priority, is_active)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)
        """, (title, pos, fmt, adv, txt, cta, url, img, pri))

conn.commit()

# Verify that all 11 placements are now active in DB
cur.execute("SELECT position, count(*) FROM advertisements WHERE is_active=1 GROUP BY position")
active_counts = dict(cur.fetchall())
conn.close()

all_11 = [
    'header_728x90', 'top_left', 'top_right', 'homepage_mid_728x90',
    'inline_feed', 'in_article', 'sidebar_300x250', 'sidebar_300x600',
    'footer', 'sticky', 'popup'
]

print("Database active placement counts:")
for p in all_11:
    count = active_counts.get(p, 0)
    print(f"  - {p:22}: {count} active")
    assert count > 0, f"Placement {p} has no active ads in DB!"

print("  ✓ All 11 placements verified active in database!")

with sync_playwright() as p:
    browser = p.chromium.launch(headless=True)
    page = browser.new_page(viewport={'width': 1280, 'height': 800})
    
    # ----------------------------------------------------
    # TEST 1: HOMEPAGE PLACEMENTS
    # ----------------------------------------------------
    print("\n[TEST 1] Verifying Homepage Placements...")
    page.goto(f"{BASE_URL}/", wait_until='networkidle')
    
    # Check Header Leaderboard
    header_ad = page.locator('.placement-header_728x90')
    assert header_ad.count() >= 1, "Missing placement-header_728x90 on homepage!"
    print("  ✓ TOP LEADERBOARD (header_728x90) is visible")

    # Check Top Left & Top Right dual banner pair
    top_pair = page.locator('.top-banner-pair')
    assert top_pair.count() >= 1, "Missing .top-banner-pair on homepage!"
    top_left_ad = page.locator('.placement-top_left')
    top_right_ad = page.locator('.placement-top_right')
    assert top_left_ad.count() >= 1, "Missing placement-top_left in top banner pair!"
    assert top_right_ad.count() >= 1, "Missing placement-top_right in top banner pair!"
    print("  ✓ TOP LEFT (top_left) is visible")
    print("  ✓ TOP RIGHT (top_right) is visible")

    # Check Inline Feed Ad
    inline_ad = page.locator('.placement-inline_feed')
    assert inline_ad.count() >= 1, "Missing placement-inline_feed on homepage!"
    print("  ✓ INSIDE NEWS FEED (inline_feed) is visible")

    # Check Between Sections Ad
    mid_ad = page.locator('.placement-homepage_mid_728x90')
    assert mid_ad.count() >= 1, "Missing placement-homepage_mid_728x90 on homepage!"
    print("  ✓ BETWEEN SECTIONS (homepage_mid_728x90) is visible")

    # Check Sidebar Ad
    sidebar_ad = page.locator('.editorial-rail .placement-sidebar_300x250')
    assert sidebar_ad.count() >= 1, "Missing placement-sidebar_300x250 in sidebar!"
    print("  ✓ SIDEBAR (sidebar_300x250) is visible")

    # Check Sidebar Tower Ad
    tower_ad = page.locator('.editorial-rail .placement-sidebar_300x600')
    assert tower_ad.count() >= 1, "Missing placement-sidebar_300x600 in sidebar!"
    print("  ✓ TOWER (sidebar_300x600) is visible")

    # Check Footer Ad
    footer_ad = page.locator('.placement-footer')
    assert footer_ad.count() >= 1, "Missing placement-footer on homepage!"
    print("  ✓ FOOTER (footer) is visible")

    # Check Sticky Bottom Ad
    sticky_ad = page.locator('.sticky-ad')
    assert sticky_ad.count() >= 1, "Missing sticky bottom ad on homepage!"
    print("  ✓ STICKY BOTTOM (sticky) is visible")

    # Check Pop-up element in DOM
    popup = page.locator('[data-ad-popup]')
    assert popup.count() >= 1, "Missing [data-ad-popup] in DOM!"
    print("  ✓ POP-UP (popup) present and ready in DOM")

    page.screenshot(path=f"{SCREENSHOT_DIR}/verified_homepage_placements.png", full_page=False)

    # ----------------------------------------------------
    # TEST 2: ARTICLE READING PAGE PLACEMENTS (IN_ARTICLE)
    # ----------------------------------------------------
    print("\n[TEST 2] Verifying Article Placements (in_article)...")
    first_article_link = page.locator('article.front-lead h2 a').get_attribute('href')
    assert first_article_link, "Could not find article link on homepage!"
    target_url = first_article_link if first_article_link.startswith('http') else f"{BASE_URL}{first_article_link}"
    page.goto(target_url, wait_until='networkidle')

    in_article_ad = page.locator('.placement-in_article')
    assert in_article_ad.count() >= 1, "Missing placement-in_article on article reading page!"
    print("  ✓ IN ARTICLE (in_article) is visible inside article view")

    art_sidebar = page.locator('.article-layout aside .placement-sidebar_300x250')
    assert art_sidebar.count() >= 1, "Missing sidebar_300x250 in article sidebar!"
    print("  ✓ SIDEBAR (sidebar_300x250) visible in article sidebar")

    art_tower = page.locator('.article-layout aside .placement-sidebar_300x600')
    assert art_tower.count() >= 1, "Missing sidebar_300x600 in article sidebar!"
    print("  ✓ TOWER (sidebar_300x600) visible in article sidebar")

    page.screenshot(path=f"{SCREENSHOT_DIR}/verified_article_placements.png", full_page=False)

    # ----------------------------------------------------
    # TEST 3: ADMIN PLACEMENT MAP INTERACTIVITY & COUNTS
    # ----------------------------------------------------
    print("\n[TEST 3] Testing Admin Visual Map Interactivity (/admin/advertisements)...")
    # Log in as admin
    page.goto(f"{BASE_URL}/admin/login", wait_until='networkidle')
    page.fill('input[name="email"]', 'admin@example.test')
    page.fill('input[name="password"]', 'MemberPassword123!')
    page.click('button:has-text("Sign in")')
    page.wait_for_url("**/admin**")

    # Navigate to Advertisements desk
    page.goto(f"{BASE_URL}/admin/advertisements", wait_until='networkidle')

    # Verify visual placement map exists
    placement_map = page.locator('.placement-map')
    assert placement_map.count() >= 1, "Placement map missing from admin advertisements page!"

    # Verify slot-count badges exist inside all 11 map boxes
    for p in all_11:
        box = page.locator(f'.placement-map [data-placement="{p}"]')
        assert box.count() == 1, f"Placement map missing box for {p}!"
        count_badge = box.locator('.slot-count')
        assert count_badge.count() == 1, f"Slot count badge missing for {p}!"
        print(f"  ✓ Admin visual box for '{p}' verified with active count badge ({count_badge.inner_text()})")

    # Test clicking interactive boxes in diagram
    position_select = page.locator('select[name="position"]')

    # 1. Click "TOP LEFT"
    page.locator('.placement-map [data-placement="top_left"]').click()
    assert position_select.input_value() == 'top_left', "Clicking TOP LEFT box did not select top_left in dropdown!"
    assert 'chosen' in (page.locator('.placement-map [data-placement="top_left"]').get_attribute('class') or '')
    print("  ✓ Clicked 'TOP LEFT' box -> dropdown updated to 'top_left', box highlighted with .chosen")

    # 2. Click "IN ARTICLE"
    page.locator('.placement-map [data-placement="in_article"]').click()
    assert position_select.input_value() == 'in_article', "Clicking IN ARTICLE box did not select in_article in dropdown!"
    assert 'chosen' in (page.locator('.placement-map [data-placement="in_article"]').get_attribute('class') or '')
    print("  ✓ Clicked 'IN ARTICLE' box -> dropdown updated to 'in_article', box highlighted with .chosen")

    # 3. Click "TOWER"
    page.locator('.placement-map [data-placement="sidebar_300x600"]').click()
    assert position_select.input_value() == 'sidebar_300x600', "Clicking TOWER box did not select sidebar_300x600 in dropdown!"
    print("  ✓ Clicked 'TOWER' box -> dropdown updated to 'sidebar_300x600'")

    # 4. Click "STICKY BOTTOM"
    page.locator('.placement-map [data-placement="sticky"]').click()
    assert position_select.input_value() == 'sticky', "Clicking STICKY BOTTOM box did not select sticky in dropdown!"
    print("  ✓ Clicked 'STICKY BOTTOM' box -> dropdown updated to 'sticky'")

    page.screenshot(path=f"{SCREENSHOT_DIR}/verified_admin_interactive_map.png", full_page=False)

    # ----------------------------------------------------
    # TEST 4: PREVIEW MODE (?preview_ads=1)
    # ----------------------------------------------------
    print("\n[TEST 4] Testing Live Visual Zone Preview Mode (?preview_ads=1)...")
    page.goto(f"{BASE_URL}/?preview_ads=1", wait_until='networkidle')

    # Verify floating preview indicator
    preview_bar = page.locator('.ad-preview-floating-bar')
    assert preview_bar.count() >= 1, "Missing .ad-preview-floating-bar in preview mode!"
    print("  ✓ Floating preview status bar visible: " + preview_bar.inner_text().replace('\n', ' '))

    # Verify active zones have zone pills
    zone_pills = page.locator('.ad-zone-pill')
    assert zone_pills.count() >= 5, f"Expected zone pills in preview mode, found {zone_pills.count()}!"
    print(f"  ✓ Found {zone_pills.count()} visual zone labels identifying placement IDs across the page")

    page.screenshot(path=f"{SCREENSHOT_DIR}/verified_live_preview_mode.png", full_page=False)

    browser.close()

print("\n" + "=" * 70)
print("ALL 11 ADVERTISEMENT PLACEMENT LOCATIONS VERIFIED & FULLY FUNCTIONAL!")
print("=" * 70)
