from playwright.sync_api import sync_playwright

p = sync_playwright().start()
b = p.chromium.launch()
page = b.new_page(viewport={'width': 1280, 'height': 900})
page.goto('http://127.0.0.1:8080/account/login')
page.fill('input[name="email"]', 'admin@example.test')
page.fill('input[name="password"]', 'MemberPassword123!')
page.click('button:has-text("Sign in")')
page.wait_for_url('**/admin**')

# Media view
page.goto('http://127.0.0.1:8080/admin/media')
page.screenshot(path=r'C:\Users\ASUS\.gemini\antigravity-ide\brain\fb31e748-22f4-470c-86a6-e5fecce9a4e5\test_admin_media_redesigned.png', full_page=True)

# Articles view
page.goto('http://127.0.0.1:8080/admin/articles')
page.screenshot(path=r'C:\Users\ASUS\.gemini\antigravity-ide\brain\fb31e748-22f4-470c-86a6-e5fecce9a4e5\test_admin_articles_redesigned.png', full_page=True)

b.close()
p.stop()
print("Redesigned screenshots captured successfully!")
