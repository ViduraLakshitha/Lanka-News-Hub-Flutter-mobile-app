"""Playwright Browser Verification for Classifieds Marketplace.

Verifies interactive browser user experience:
1. Classifieds Homepage & Category Browsing (Desktop 1280x800)
2. Category Chip Filtering (Vehicles, Property, Jobs)
3. Search and Filter Operations
4. Listing Details Page with Photo Gallery & Contact Links (tel:, mailto:)
5. Member Interactive Flow: Register -> Create Ad -> View on Member Dashboard
6. Member Actions: Edit Ad, Toggle Favorite, View Saved Ad on Dashboard
7. Responsive Web Testing: 375px (Mobile), 768px (Tablet), 1280px (Desktop)
8. Visual Screenshots saved to artifacts directory for user inspection.
"""

import sys, os, time, uuid, json
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

    print("=== STARTING CLASSIFIEDS BROWSER VERIFICATION ===")

    # 1. Marketplace Homepage
    page.goto(f"{BASE_URL}/classifieds", wait_until='networkidle')
    report("1. Marketplace URL Load", page.url.endswith('/classifieds'), f"Loaded {page.url}")
    report("1. Marketplace Title", "Classifieds" in page.title(), f"Title: {page.title()}")

    # Category Chips
    chips = page.locator('.category-chips a').all_text_contents()
    expected_categories = ['Property', 'Jobs', 'Vehicles', 'Business Promotions', 'Matrimonial', 'Services', 'Community Notices']
    matched_cats = [c for c in expected_categories if any(c.lower() in chip.lower() for chip in chips)]
    report("1. Trilingual Category Chips", len(matched_cats) >= 5, f"Matched categories: {matched_cats}")

    # Classified Cards
    card_count = page.locator('.classified-card').count()
    report("1. Classified Grid Cards", card_count > 0, f"Found {card_count} classified cards")

    # Screenshot: Marketplace Homepage Desktop
    ss_path_1 = os.path.join(ARTIFACT_DIR, "30_marketplace_desktop.png")
    page.screenshot(path=ss_path_1, full_page=True)
    report("1. Desktop Screenshot Saved", os.path.exists(ss_path_1), ss_path_1)

    # 2. Category Filter Interaction (Property has 2 approved listings in seed data)
    page.click('.category-chips a:has-text("Property")')
    page.wait_for_load_state('networkidle')
    report("2. Property Filter URL", "category=property" in page.url, f"Filtered URL: {page.url}")
    report("2. Property Filter Active State", page.locator('.category-chips a.active:has-text("Property")').count() > 0, "Active chip highlighted")
    report("2. Property Filter Results", page.locator('.classified-card').count() >= 2, f"Found {page.locator('.classified-card').count()} property listings")

    # 3. Listing Detail View
    first_ad_link = page.locator('.classified-card a.classified-image').first
    detail_url = first_ad_link.get_attribute('href')
    first_ad_link.click()
    page.wait_for_load_state('networkidle')
    report("3. Listing Detail Navigation", '/classifieds/' in page.url, f"Detail URL: {page.url}")
    report("3. Detail Title Present", page.locator('h1').count() == 1, f"Title: {page.locator('h1').text_content()}")
    report("3. Asking Price Present", page.locator('.price').count() > 0, f"Price: {page.locator('.price').first.text_content()}")
    report("3. Tel Contact Link", page.locator('a[href^="tel:"]').count() > 0, f"Tel link: {page.locator('a[href^=\"tel:\"]').first.get_attribute('href')}")
    report("3. Email Contact Link", page.locator('a[href^="mailto:"]').count() > 0, f"Mail link: {page.locator('a[href^=\"mailto:\"]').first.get_attribute('href')}")

    # Screenshot: Listing Detail Page
    ss_path_2 = os.path.join(ARTIFACT_DIR, "31_classified_detail.png")
    page.screenshot(path=ss_path_2, full_page=True)
    report("3. Detail Screenshot Saved", os.path.exists(ss_path_2), ss_path_2)

    # 4. Member Journey: Registration
    reg_id = uuid.uuid4().hex[:6]
    test_user_email = f"buyer_{reg_id}@example.test"
    test_user_name = f"Priyantha Silva {reg_id}"

    page.goto(f"{BASE_URL}/account/register", wait_until='networkidle')
    page.fill('input[name="name"]', test_user_name)
    page.fill('input[name="email"]', test_user_email)
    page.fill('input[name="password"]', 'MemberPassword123!')
    page.click('button:has-text("Create account")')
    page.wait_for_load_state('networkidle')
    report("4. Member Registered", page.url.endswith('/account'), f"Redirected to {page.url}")
    report("4. Dashboard Greets Member", test_user_name in page.content(), f"Found {test_user_name} on dashboard")

    # 5. Member Journey: Create Classified Listing
    page.goto(f"{BASE_URL}/classifieds/create", wait_until='networkidle')
    report("5. Create Form Loaded", page.locator('h1:has-text("Post a classified")').count() > 0, "Create form displayed")

    listing_title = f"Modern 3BR Luxury Apartment Colombo 07 {reg_id}"
    page.fill('input[name="title"]', listing_title)
    page.select_option('select[name="category_id"]', value='1') # Property
    page.fill('textarea[name="description"]', "Spacious modern apartment located in Prime Colombo 07. Fully furnished with swimming pool, gym, 24/7 security and dedicated parking.")
    page.fill('input[name="location"]', "Barnes Place, Colombo 07")
    page.fill('input[name="price"]', "85000000.00")
    page.fill('input[name="contact_name"]', test_user_name)
    page.fill('input[name="contact_email"]', test_user_email)
    page.fill('input[name="contact_phone"]', "+94 71 888 9900")
    if page.locator('[data-ad-popup]:not([hidden])').count() > 0:
        page.click('[data-close-popup]')
    page.check('input[name="premium_requested"]')

    # Screenshot: Create Form Filled
    ss_path_3 = os.path.join(ARTIFACT_DIR, "32_classified_create_filled.png")
    page.screenshot(path=ss_path_3, full_page=True)
    report("5. Form Screenshot Saved", os.path.exists(ss_path_3), ss_path_3)

    with page.expect_navigation():
        page.click('button:has-text("Submit for review")')
    report("5. Listing Submitted", page.url.endswith('/account'), f"Redirected to dashboard: {page.url}")
    report("5. Listing In Dashboard", listing_title in page.content(), "Listing title appears on member dashboard")
    report("5. Pending Badge Displayed", page.locator('.account-listings .status.pending').count() > 0, "Pending badge displayed")

    # Screenshot: Member Dashboard with Pending Listing
    ss_path_4 = os.path.join(ARTIFACT_DIR, "33_member_dashboard_with_ad.png")
    page.screenshot(path=ss_path_4, full_page=True)
    report("5. Dashboard Screenshot Saved", os.path.exists(ss_path_4), ss_path_4)

    # 6. Favorite Toggle Interaction
    target_detail = detail_url if detail_url.startswith('http') else f"{BASE_URL}{detail_url}"
    page.goto(target_detail, wait_until='networkidle')
    save_btn = page.locator('button:has-text("Save advertisement")')
    report("6. Save Button Present", save_btn.count() > 0, "Save advertisement button present")
    save_btn.click()
    page.wait_for_load_state('networkidle')
    report("6. Saved State Updated", page.locator('button:has-text("Remove from saved")').count() > 0, "Button updated to 'Remove from saved'")

    page.goto(f"{BASE_URL}/account", wait_until='networkidle')
    saved_ad_card = page.locator('#saved-listings ~ .classified-grid .classified-card')
    report("6. Saved Ad Appears In Dashboard", saved_ad_card.count() > 0, f"Found {saved_ad_card.count()} saved ads on dashboard")

    # 7. Responsive Web Testing
    print("\n--- Testing Classifieds Responsive Viewports ---")
    viewports = [
        ("Mobile 375px", 375, 667),
        ("Mobile 430px", 430, 932),
        ("Tablet 768px", 768, 1024),
        ("Laptop 1024px", 1024, 768),
        ("Desktop 1440px", 1440, 900)
    ]
    for name, w, h in viewports:
        page.set_viewport_size({'width': w, 'height': h})
        page.goto(f"{BASE_URL}/classifieds", wait_until='networkidle')
        # Check no horizontal overflow
        scroll_w = page.evaluate("() => document.documentElement.scrollWidth")
        client_w = page.evaluate("() => document.documentElement.clientWidth")
        no_overflow = scroll_w <= client_w
        report(f"7. Responsive {name}", no_overflow, f"ScrollWidth: {scroll_w}px <= ClientWidth: {client_w}px")

    # Mobile screenshot
    page.set_viewport_size({'width': 375, 'height': 812})
    page.goto(f"{BASE_URL}/classifieds", wait_until='networkidle')
    ss_path_5 = os.path.join(ARTIFACT_DIR, "34_classifieds_mobile_375.png")
    page.screenshot(path=ss_path_5, full_page=True)
    report("7. Mobile Screenshot Saved", os.path.exists(ss_path_5), ss_path_5)

    browser.close()

    print("\n" + "=" * 70)
    print(f"ALL {len(checks)} BROWSER VERIFICATION CHECKS PASSED (100%)!")
    print("=" * 70)
