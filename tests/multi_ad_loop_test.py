import sys, os, time, sqlite3
if hasattr(sys.stdout, 'reconfigure'):
    sys.stdout.reconfigure(encoding='utf-8')
from playwright.sync_api import sync_playwright

BASE_URL = os.getenv('APP_URL', 'http://127.0.0.1:8080')
DB_PATH = 'storage/preview.sqlite'

print("=" * 70)
print("TESTING MULTI-AD AUTO-ROTATING LOOP IN PLACEMENTS")
print("=" * 70)

# 1. Insert 3 test ads for the same placement: 'sidebar_300x250'
conn = sqlite3.connect(DB_PATH)
cur = conn.cursor()

# Deactivate existing sidebar_300x250 ads to ensure a clean test set
cur.execute("UPDATE advertisements SET is_active=0 WHERE position='sidebar_300x250'")

ads_data = [
    ("Loop Campaign Alpha", "sidebar_300x250", "text", "Brand Alpha", "Exclusive premium discounts for members.", "https://example.com/alpha", 10),
    ("Loop Campaign Beta", "sidebar_300x250", "text", "Brand Beta", "Fast nationwide delivery within 24 hours.", "https://example.com/beta", 5),
    ("Loop Campaign Gamma", "sidebar_300x250", "text", "Brand Gamma", "Save up to 40% on annual subscriptions.", "https://example.com/gamma", 1)
]

for title, pos, fmt, adv, txt, url, pri in ads_data:
    cur.execute("""
        INSERT INTO advertisements (title, position, ad_format, advertiser, ad_text, url, priority, is_active)
        VALUES (?, ?, ?, ?, ?, ?, ?, 1)
    """, (title, pos, fmt, adv, txt, url, pri))

conn.commit()
conn.close()
print("  ✓ Seeded 3 test ads in 'sidebar_300x250' placement with priorities 10, 5, 1")

with sync_playwright() as p:
    browser = p.chromium.launch(headless=True)
    page = browser.new_page(viewport={'width': 1280, 'height': 800})
    page.add_init_script("sessionStorage.setItem('ad-popup-seen', '1');")
    page.goto(f"{BASE_URL}/", wait_until='networkidle')

    # 2. Check HTML structure of the multi-ad slot
    slot = page.locator('.ad-slot.placement-sidebar_300x250')
    assert slot.count() > 0, "Sidebar ad slot must exist"
    assert "has-loop" in slot.first.get_attribute("class"), "Ad slot must have 'has-loop' class"
    assert slot.first.get_attribute("data-ad-loop") == "5000", "Ad slot must specify rotation interval"

    slides = slot.locator('.ad-slide')
    assert slides.count() == 3, f"Expected 3 slides, got {slides.count()}"
    print(f"  ✓ Found 3 ad slides in placement: {[slides.nth(i).inner_text().strip()[:30] for i in range(3)]}")

    dots = slot.locator('.ad-dot')
    assert dots.count() == 3, f"Expected 3 navigation dots, got {dots.count()}"
    print("  ✓ Found 3 pagination dots")

    counter = slot.locator('.ad-loop-counter')
    assert counter.inner_text() == "1/3", f"Initial counter should be '1/3', got {counter.inner_text()}"
    print("  ✓ Initial slide index counter is '1/3'")

    # 3. Check Initial Active Slide (should be priority 10: Loop Campaign Alpha)
    active_slide = slot.locator('.ad-slide.is-active')
    assert "Loop Campaign Alpha" in active_slide.inner_text(), f"Highest priority ad should be active first, got {active_slide.inner_text()}"
    print("  ✓ Highest priority ad (Loop Campaign Alpha, p10) is displayed first")

    # 4. Interactive dot click test
    print("\nTesting interactive pagination dots...")
    dots.nth(1).click()
    page.wait_for_timeout(300)
    assert "Loop Campaign Beta" in slot.locator('.ad-slide.is-active').inner_text(), "Slide 1 (Loop Campaign Beta) should be active after clicking dot 1"
    assert counter.inner_text() == "2/3", f"Counter should update to '2/3', got {counter.inner_text()}"
    print("  ✓ Clicked dot 1 -> Switched to 'Loop Campaign Beta', counter updated to '2/3'")

    dots.nth(2).click()
    page.wait_for_timeout(300)
    assert "Loop Campaign Gamma" in slot.locator('.ad-slide.is-active').inner_text(), "Slide 2 (Loop Campaign Gamma) should be active after clicking dot 2"
    assert counter.inner_text() == "3/3", f"Counter should update to '3/3', got {counter.inner_text()}"
    print("  ✓ Clicked dot 2 -> Switched to 'Loop Campaign Gamma', counter updated to '3/3'")

    # 5. Automated rotation test
    print("\nTesting automated rotation loop...")
    dots.nth(0).click()
    page.wait_for_timeout(300)
    assert counter.inner_text() == "1/3"
    # Move mouse away so slot is not hovered
    page.mouse.move(0, 0)
    
    # Wait for the 5-second interval plus transition
    print("  Waiting 5.3s for automated transition to next slide...")
    page.wait_for_timeout(5300)
    
    new_counter = counter.inner_text()
    assert new_counter == "2/3", f"Auto-rotation should advance to '2/3', got {new_counter}"
    assert "Loop Campaign Beta" in slot.locator('.ad-slide.is-active').inner_text()
    print(f"  ✓ Automated loop successfully transitioned from slide 1 to slide 2 (counter: {new_counter})")

    # 6. Pause on hover test
    print("\nTesting pause-on-hover behavior...")
    slot.hover()
    print("  Hovering over ad slot for 5.5s...")
    page.wait_for_timeout(5500)
    hover_counter = counter.inner_text()
    assert hover_counter == "2/3", f"Ad loop should remain paused at '2/3' while hovered, got {hover_counter}"
    print("  ✓ Ad loop remained paused during mouse hover so reader can interact with the ad")

    # Move mouse away to resume
    page.mouse.move(0, 0)
    print("  Mouse moved away. Waiting 5.3s for loop to resume...")
    page.wait_for_timeout(5300)
    resumed_counter = counter.inner_text()
    assert resumed_counter == "3/3", f"Ad loop should resume and advance to '3/3', got {resumed_counter}"
    print(f"  ✓ Ad loop successfully resumed after mouse leave (counter: {resumed_counter})")

    browser.close()

print("\n" + "=" * 70)
print("MULTI-AD PLACEMENT LOOP FULLY VERIFIED & WORKING PERFECTLY!")
print("=" * 70)
