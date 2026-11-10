import os, time
from playwright.sync_api import sync_playwright

with sync_playwright() as p:
    browser = p.chromium.launch(headless=True)
    page = browser.new_page(viewport={'width': 1440, 'height': 1200})
    page.goto('http://127.0.0.1:8080/admin/login')
    if page.query_selector("input[name='email']"):
        page.fill("input[name='email']", "admin@example.test")
        page.fill("input[name='password']", "MemberPassword123!")
        page.click('button:has-text("Sign in")')
        page.wait_for_load_state('networkidle')
    page.goto('http://127.0.0.1:8080/admin/advertisements', wait_until='networkidle')
    page.select_option("select[data-banner-position]", "top_left")
    page.set_input_files("input[data-banner-upload]", os.path.abspath("public/uploads/ads/ad_top_left_590x100.png"))
    time.sleep(1)
    page.screenshot(path='scratch/screenshots/admin_dimensions_full.png', full_page=True)
    browser.close()
print("Admin full page captured successfully")
