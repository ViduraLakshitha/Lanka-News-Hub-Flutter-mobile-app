import sys, os, time
if hasattr(sys.stdout, 'reconfigure'):
    sys.stdout.reconfigure(encoding='utf-8')
from playwright.sync_api import sync_playwright

BASE_URL = os.getenv('APP_URL', 'http://127.0.0.1:8080')
SCREENSHOT_DIR = 'scratch/screenshots'
os.makedirs(SCREENSHOT_DIR, exist_ok=True)

print("=" * 70)
print("TESTING AD PLACEMENT SIZES WITH UPLOADED BANNER IMAGES")
print("=" * 70)

expected_specs = {
    'header_728x90': {'w': 1200, 'h': 100, 'file': 'ad_header_1200x100.png'},
    'top_left': {'w': 590, 'h': 100, 'file': 'ad_top_left_590x100.png'},
    'top_right': {'w': 590, 'h': 100, 'file': 'ad_top_right_590x100.png'},
    'homepage_mid_728x90': {'w': 728, 'h': 90, 'file': 'ad_between_sections_728x90.png'},
    'inline_feed': {'w': 728, 'h': 90, 'file': 'ad_inline_feed_728x90.png'},
    'sidebar_300x250': {'w': 300, 'h': 250, 'file': 'ad_sidebar_300x250.png'},
    'sidebar_300x600': {'w': 300, 'h': 600, 'file': 'ad_tower_300x600.png'},
    'footer': {'w': 1200, 'h': 100, 'file': 'ad_footer_1200x100.png'},
    'sticky': {'w': 728, 'h': 90, 'file': 'ad_sticky_728x90.png'},
    'popup': {'w': 600, 'h': 400, 'file': 'ad_popup_600x400.png'},
    'in_article': {'w': 800, 'h': 250, 'file': 'ad_in_article_800x250.png'},
}

with sync_playwright() as p:
    browser = p.chromium.launch(headless=True)
    page = browser.new_page(viewport={"width": 1440, "height": 1000})
    page.on("console", lambda msg: print(f"[Browser Console] {msg.type}: {msg.text}"))
    page.on("pageerror", lambda err: print(f"[Browser Error] {err}"))

    # 1. Test Homepage Placements
    print("\n[Step 1] Loading Homepage & verifying uploaded banner dimensions...")
    page.goto(f"{BASE_URL}/?preview_ads=1", wait_until="networkidle")
    time.sleep(1.8) # wait for popup timer

    homepage_placements = [
        'header_728x90', 'top_left', 'top_right', 'homepage_mid_728x90',
        'inline_feed', 'sidebar_300x250', 'sidebar_300x600', 'footer',
        'sticky', 'popup'
    ]

    for pos in homepage_placements:
        spec = expected_specs[pos]
        selector = f".placement-{pos} img"
        img = page.query_selector(selector)
        if not img:
            print(f"  ❌ Missing <img> in placement-{pos}")
            continue

        img.scroll_into_view_if_needed()
        natural_w = 0
        natural_h = 0
        for _ in range(30):
            natural_w = page.evaluate("(el) => el.naturalWidth", img)
            natural_h = page.evaluate("(el) => el.naturalHeight", img)
            if natural_w > 0 and natural_h > 0:
                break
            time.sleep(0.1)

        src = img.get_attribute("src") or ""
        
        file_match = spec['file'] in src
        dim_match = (natural_w == spec['w'] and natural_h == spec['h'])
        status = "✓" if (file_match and dim_match) else "⚠️"
        print(f"  {status} {pos:20}: src={os.path.basename(src):30} Natural: {natural_w} × {natural_h} px (Expected: {spec['w']} × {spec['h']})")
        assert dim_match, f"Dimension mismatch for {pos}: got {natural_w}x{natural_h}, expected {spec['w']}x{spec['h']}"

    screenshot_home = f"{SCREENSHOT_DIR}/ad_sizes_homepage.png"
    page.screenshot(path=screenshot_home, full_page=False)
    print(f"  ✓ Homepage screenshot saved: {screenshot_home}")

    # 2. Test In-Article Placement
    print("\n[Step 2] Loading Article Page for in_article reading banner...")
    article_link = page.query_selector("a[href*='/news/']")
    if article_link:
        href = article_link.get_attribute("href")
        article_url = f"{BASE_URL}{href}" if href.startswith('/') else href
        if '?' in article_url:
            article_url += '&preview_ads=1'
        else:
            article_url += '?preview_ads=1'
        page.goto(article_url, wait_until="networkidle")
        time.sleep(0.5)

        img = page.query_selector(".placement-in_article img")
        assert img, "Could not find <img> in placement-in_article!"
        src = img.get_attribute("src") or ""
        natural_w = page.evaluate("(el) => el.naturalWidth", img)
        natural_h = page.evaluate("(el) => el.naturalHeight", img)
        spec = expected_specs['in_article']
        dim_match = (natural_w == spec['w'] and natural_h == spec['h'])
        print(f"  ✓ in_article: src={os.path.basename(src):30} Natural: {natural_w} × {natural_h} px (Expected: {spec['w']} × {spec['h']})")
        assert dim_match, f"Dimension mismatch for in_article: got {natural_w}x{natural_h}"

        screenshot_article = f"{SCREENSHOT_DIR}/ad_sizes_in_article.png"
        page.screenshot(path=screenshot_article, full_page=False)
        print(f"  ✓ Article screenshot saved: {screenshot_article}")

    # 3. Test Admin Advertisements Manager & Dimension Tools
    print("\n[Step 3] Loading Admin Advertisements Page...")
    page.goto(f"{BASE_URL}/admin/login", wait_until="networkidle")
    if page.query_selector("input[name='email']"):
        page.fill("input[name='email']", "admin@example.test")
        page.fill("input[name='password']", "MemberPassword123!")
        page.click('button:has-text("Sign in")')
        page.wait_for_load_state("networkidle")

    page.goto(f"{BASE_URL}/admin/advertisements", wait_until="networkidle")
    time.sleep(0.5)

    # Verify Dimension Inspector Card
    badge_el = page.query_selector("#ad-dim-badge")
    assert badge_el, "#ad-dim-badge element not found!"
    print(f"  ✓ Initial dimension badge text: '{badge_el.inner_text().strip()}'")

    # Select placement 'top_left' and verify dimension card updates to 590 × 100 px
    page.select_option("select[data-banner-position]", "top_left")
    time.sleep(0.2)
    dim_text = page.inner_text("#ad-dim-badge").strip()
    print(f"  ✓ After selecting top_left: badge displays '{dim_text}'")
    assert "590 × 100" in dim_text, f"Expected 590 × 100 in badge text, got '{dim_text}'"

    # Select placement 'sidebar_300x600'
    page.select_option("select[data-banner-position]", "sidebar_300x600")
    time.sleep(0.2)
    dim_text = page.inner_text("#ad-dim-badge").strip()
    print(f"  ✓ After selecting sidebar_300x600: badge displays '{dim_text}'")
    assert "300 × 600" in dim_text, f"Expected 300 × 600 in badge text, got '{dim_text}'"

    # Test file upload with dimension verification
    print("\n[Step 4] Testing live upload dimension detection...")
    test_upload_file = os.path.abspath("public/uploads/ads/ad_top_left_590x100.png")
    page.select_option("select[data-banner-position]", "top_left")
    page.set_input_files("input[data-banner-upload]", test_upload_file)

    badge_text = ""
    for _ in range(40):
        badge_text = page.inner_text("#inspector-badge").strip()
        if "590 × 100" in badge_text or "Exact size match" in badge_text:
            break
        time.sleep(0.1)

    details_text = page.inner_text("#inspector-details").strip()
    print(f"  ✓ Upload Inspector Badge: '{badge_text}'")
    print(f"  ✓ Upload Inspector Details: '{details_text}'")
    assert "Exact size match" in badge_text or "590 × 100" in badge_text, f"Unexpected badge text: {badge_text}"

    # Click reference table "Select Slot" for footer
    print("\n[Step 5] Testing reference table slot selection...")
    footer_btn = page.query_selector("button[data-choose-placement='footer']")
    assert footer_btn, "Footer choose button not found in table!"
    footer_btn.click()
    time.sleep(0.3)
    curr_select = page.input_value("select[data-banner-position]")
    dim_text = page.inner_text("#ad-dim-badge").strip()
    print(f"  ✓ Selected slot from table: '{curr_select}', badge: '{dim_text}'")
    assert curr_select == "footer", f"Expected select to be 'footer', got '{curr_select}'"
    assert "1200 × 100" in dim_text, f"Expected 1200 × 100 in badge, got '{dim_text}'"

    screenshot_admin = f"{SCREENSHOT_DIR}/ad_sizes_admin_inspector.png"
    page.screenshot(path=screenshot_admin, full_page=False)
    print(f"  ✓ Admin Inspector screenshot saved: {screenshot_admin}")

    browser.close()

print("\n" + "=" * 70)
print("ALL TESTS PASSED: UPLOADED BANNER SIZES & DIMENSION TOOLS VERIFIED!")
print("=" * 70)
