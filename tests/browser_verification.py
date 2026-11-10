"""Automated Playwright browser verification for Multilingual News & Classifieds Platform.
Verifies all required in-scope public portal features:
1. English Homepage
2. Sinhala Homepage
3. Tamil Homepage
4. Article Page
5. Category Page
6. Search
7. Breaking News
8. Photo Gallery
9. Video
10. Responsive Web (375px, 430px, 768px, 1024px, 1440px)
11. Accessibility (Semantic HTML, Alt text, Skip links, Headings)

Run: py tests/browser_verification.py
"""
import sys, os, time, json
sys.stdout.reconfigure(encoding='utf-8')
from playwright.sync_api import sync_playwright

BASE_URL = os.getenv('APP_URL', 'http://127.0.0.1:8080')
results = {}

def report(name, status, message=""):
    badge = 'PASS' if status else 'FAIL'
    print(f"[{badge}] {name}: {message}")
    results[name] = {'status': badge, 'message': message}

with sync_playwright() as p:
    browser = p.chromium.launch(headless=True)
    page = browser.new_page(viewport={'width': 1280, 'height': 800})

    print("=== STARTING FULL PORTAL BROWSER VERIFICATION ===")

    # 1. English Homepage
    try:
        page.goto(f"{BASE_URL}/?lang=en")
        title = page.title()
        has_nav = page.locator('.primary-nav a:has-text("Latest news")').count() > 0
        has_breaking_nav = page.locator('.primary-nav a:has-text("Breaking news")').count() > 0
        has_lead = page.locator('.front-lead h2').count() > 0
        has_cat_sec = page.locator('.category-block').count() > 0
        has_footer = page.locator('.site-footer').count() > 0
        lead_text = page.locator('.front-lead h2').inner_text() if has_lead else ""
        ad_count = page.locator('.ad-slot').count()
        report("English Homepage", has_nav and has_lead and has_cat_sec and has_footer,
               f"Nav: {has_nav}, Lead: '{lead_text[:30]}...', CatSections: {has_cat_sec}, Ads: {ad_count}")
    except Exception as e:
        report("English Homepage", False, str(e))

    # 2. Sinhala Homepage
    try:
        page.goto(f"{BASE_URL}/?lang=si")
        html_lang = page.locator('html').get_attribute('lang')
        has_si_nav = page.locator('.primary-nav a:has-text("නවතම පුවත්")').count() > 0
        has_si_breaking = page.locator('.primary-nav a:has-text("උණුසුම් පුවත්")').count() > 0
        has_lead = page.locator('.front-lead h2').count() > 0
        si_title = page.locator('.front-lead h2').inner_text() if has_lead else ""
        report("Sinhala Homepage", html_lang == 'si' and has_si_nav and has_lead,
               f"Lang: '{html_lang}', Si Nav: {has_si_nav}, Si Lead: '{si_title[:30]}...'")
    except Exception as e:
        report("Sinhala Homepage", False, str(e))

    # 3. Tamil Homepage
    try:
        page.goto(f"{BASE_URL}/?lang=ta")
        html_lang = page.locator('html').get_attribute('lang')
        has_ta_nav = page.locator('.primary-nav a:has-text("சமீபத்திய செய்திகள்")').count() > 0
        has_ta_breaking = page.locator('.primary-nav a:has-text("முக்கிய செய்திகள்")').count() > 0
        has_lead = page.locator('.front-lead h2').count() > 0
        ta_title = page.locator('.front-lead h2').inner_text() if has_lead else ""
        report("Tamil Homepage", html_lang == 'ta' and has_ta_nav and has_lead,
               f"Lang: '{html_lang}', Ta Nav: {has_ta_nav}, Ta Lead: '{ta_title[:30]}...'")
    except Exception as e:
        report("Tamil Homepage", False, str(e))

    # 4. Article Page
    try:
        page.goto(f"{BASE_URL}/news/sample-story-1?lang=en")
        art_title = page.locator('.article-title').inner_text() if page.locator('.article-title').count() > 0 else ""
        has_byline = page.locator('.article-byline').count() > 0
        has_body = page.locator('.article-body').count() > 0
        has_cover = page.locator('.article-cover').count() > 0
        cover_alt = page.locator('.article-cover').get_attribute('alt') if has_cover else ""
        # Check all 5 social share links + copy link
        fb_share = page.locator('.share-row a:has-text("Facebook")').count() > 0
        wa_share = page.locator('.share-row a:has-text("WhatsApp")').count() > 0
        x_share = page.locator('.share-row a:has-text("X")').count() > 0
        tg_share = page.locator('.share-row a:has-text("Telegram")').count() > 0
        li_share = page.locator('.share-row a:has-text("LinkedIn")').count() > 0
        copy_btn = page.locator('.share-row button[data-copy]').count() > 0
        all_social = fb_share and wa_share and x_share and tg_share and li_share and copy_btn
        report("Article Page", has_byline and has_body and all_social and len(cover_alt) > 0,
               f"Title: '{art_title[:30]}...', Byline: {has_byline}, Cover Alt: '{cover_alt[:20]}', All 5 Social + Copy: {all_social}")
    except Exception as e:
        report("Article Page", False, str(e))

    # 5. Category Page
    try:
        page.goto(f"{BASE_URL}/category/local?lang=en")
        cat_heading = page.locator('.desk-heading h1').inner_text() if page.locator('.desk-heading h1').count() > 0 else ""
        has_breadcrumbs = page.locator('.breadcrumbs').count() > 0
        story_count = page.locator('.story-card, .desk-lead').count()
        report("Category Page", has_breadcrumbs and story_count > 0,
               f"Heading: '{cat_heading}', Stories: {story_count}, Breadcrumbs: {has_breadcrumbs}")
    except Exception as e:
        report("Category Page", False, str(e))

    # 6. Functional Site Search
    try:
        page.goto(f"{BASE_URL}/search?q=perspective&lang=en")
        search_heading = page.locator('.desk-heading h1').inner_text() if page.locator('.desk-heading h1').count() > 0 else ""
        result_cards = page.locator('.story-card, .desk-lead').count()
        input_val = page.locator('.desk-heading input[name="q"]').get_attribute('value')
        report("Site Search", result_cards > 0 and input_val == 'perspective',
               f"Heading: '{search_heading}', Matches: {result_cards}, Input preserved: '{input_val}'")
    except Exception as e:
        report("Site Search", False, str(e))

    # 7. Breaking News
    try:
        page.goto(f"{BASE_URL}/edition/breaking?lang=en")
        breaking_heading = page.locator('.desk-heading h1').inner_text() if page.locator('.desk-heading h1').count() > 0 else ""
        breaking_cards = page.locator('.story-card, .desk-lead').count()
        report("Breaking News Stream", breaking_cards > 0 and 'Breaking' in breaking_heading,
               f"Heading: '{breaking_heading}', Breaking Stories: {breaking_cards}")
    except Exception as e:
        report("Breaking News Stream", False, str(e))

    # 8. Photo Gallery
    try:
        page.goto(f"{BASE_URL}/news/sample-story-7?lang=en")
        gallery_present = page.locator('.photo-gallery').count() > 0
        photo_count = page.locator('.photo-gallery figure').count()
        all_alts = page.locator('.photo-gallery img').evaluate_all("imgs => imgs.every(img => img.alt.length > 0)")
        report("Photo Gallery", gallery_present and photo_count > 0 and all_alts,
               f"Gallery present: {gallery_present}, Photos: {photo_count}, Accessible Alts: {all_alts}")
    except Exception as e:
        report("Photo Gallery", False, str(e))

    # 9. Embedded Video
    try:
        page.goto(f"{BASE_URL}/news/sample-story-3?lang=en")
        has_video_iframe = page.locator('iframe[src*="youtube-nocookie.com"]').count() > 0
        iframe_src = page.locator('iframe[src*="youtube-nocookie.com"]').get_attribute('src') if has_video_iframe else ""
        report("Embedded Video", has_video_iframe,
               f"YouTube NoCookie Embed: {has_video_iframe}, Src: '{iframe_src[:45]}...'")
    except Exception as e:
        report("Embedded Video", False, str(e))

    # 10. Responsive Web Design across required viewports
    viewports = [
        (375, 667, "375px (Mobile Small)"),
        (430, 932, "430px (Mobile Large)"),
        (768, 1024, "768px (Tablet)"),
        (1024, 768, "1024px (Small Laptop)"),
        (1440, 900, "1440px (Desktop Large)")
    ]

    print("\n--- Testing Responsive Viewports ---")
    for width, height, label in viewports:
        try:
            page.set_viewport_size({'width': width, 'height': height})
            page.goto(f"{BASE_URL}/?lang=en")
            # Verify no unexpected horizontal scroll
            scroll_width = page.evaluate("document.documentElement.scrollWidth")
            has_no_hscroll = scroll_width <= width + 5 # 5px tolerance for subpixel render
            
            # Check nav adaptation
            if width <= 650:
                menu_btn_visible = page.locator('.menu-toggle').is_visible()
                # Test opening mobile menu
                page.click('.menu-toggle')
                is_open = page.locator('.primary-nav').evaluate("el => el.classList.contains('menu-open')")
                nav_ok = menu_btn_visible and is_open
            else:
                nav_ok = page.locator('.primary-nav a:has-text("Latest news")').is_visible()

            # Check lead card visibility
            lead_visible = page.locator('.front-lead').is_visible()
            footer_visible = page.locator('.site-footer').is_visible()

            passed = has_no_hscroll and nav_ok and lead_visible and footer_visible
            report(f"Responsive {label}", passed,
                   f"ScrollWidth: {scroll_width}px <= {width}px, Nav OK: {nav_ok}, Lead: {lead_visible}, Footer: {footer_visible}")
        except Exception as e:
            report(f"Responsive {label}", False, str(e))

    # 11. Accessibility Verification
    try:
        page.set_viewport_size({'width': 1280, 'height': 800})
        page.goto(f"{BASE_URL}/?lang=en")
        has_skip = page.locator('a.skip-link[href="#main"]').count() > 0
        has_main = page.locator('main#main').count() > 0
        h1_count = page.locator('h1').count()
        # Verify images have alt
        all_imgs_have_alt = page.locator('img').evaluate_all("imgs => imgs.every(img => img.hasAttribute('alt'))")
        acc_passed = has_skip and has_main and h1_count == 1 and all_imgs_have_alt
        report("Accessibility Baseline", acc_passed,
               f"Skip link: {has_skip}, Main #main: {has_main}, Single H1: {h1_count == 1}, All img have alt: {all_imgs_have_alt}")
    except Exception as e:
        report("Accessibility Baseline", False, str(e))

    browser.close()

all_passed = all(r['status'] == 'PASS' for r in results.values())
print("\n=== COMPLETE PORTAL VERIFICATION SUMMARY ===")
print("Overall Result:", "ALL CHECKS PASSED (100%)" if all_passed else "SOME CHECKS FAILED")
sys.exit(0 if all_passed else 1)
