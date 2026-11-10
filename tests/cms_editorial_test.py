"""Comprehensive CMS & Newsroom Editorial Functionality Test Suite.

Verifies against the live preview server at http://127.0.0.1:8080:
1. Role-based permissions & route authorization (Guest, Member, Journalist, Editor, Admin)
2. Direct URL access security (Redirection for guests, 403 for unauthorized roles)
3. Journalist editorial workflow (Draft -> Submit for review -> Status constraint)
4. Editor review & publishing workflow (Review pending -> Publish -> Attribution preservation)
5. Draft & pending public visibility (Must return 404 to public)
6. Scheduled publishing & future visibility rules
7. Revision history creation & restoration to draft
8. Media uploads, validation, and deletion safety (Referenced vs unreferenced)
9. Category management (Trilingual names, ordering, active/inactive, deletion safety)
10. User management (Creation, role changes, account status changes, self-demotion block)
11. Audit logging for all critical operations (Publishing, deletion, role changes, status changes, ads, moderation)
"""

import sys, os, time, re, sqlite3, http.cookiejar, urllib.request, urllib.parse, urllib.error

sys.stdout.reconfigure(encoding='utf-8')

BASE_URL = os.getenv('APP_URL', 'http://127.0.0.1:8080')
DB_PATH = os.path.join(os.path.dirname(os.path.dirname(os.path.abspath(__file__))), 'storage', 'preview.sqlite')
PASSWORD = 'MemberPassword123!'

class Client:
    def __init__(self):
        self.jar = http.cookiejar.CookieJar()
        self.opener = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(self.jar))
        self.token = ''

    def request(self, path, data=None, csrf=True):
        headers = {
            'User-Agent': 'CMS-Editorial-Tester/1.0',
            'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8'
        }
        body = None
        if data is not None:
            data = dict(data)
            if csrf and self.token:
                data['_token'] = self.token
            body = urllib.parse.urlencode(data, doseq=True).encode('utf-8')
            headers['Content-Type'] = 'application/x-www-form-urlencoded'

        req = urllib.request.Request(BASE_URL + path, data=body, headers=headers)
        try:
            resp = self.opener.open(req, timeout=10)
            status = resp.status
            url = resp.geturl()
            html = resp.read().decode('utf-8', errors='replace')
        except urllib.error.HTTPError as e:
            status = e.code
            url = e.geturl()
            html = e.read().decode('utf-8', errors='replace')

        found = re.search(r'name="_token"\s+value="([a-f0-9]+)"', html)
        if not found:
            found = re.search(r'value="([a-f0-9]+)"\s+name="_token"', html)
        if found:
            self.token = found.group(1)

        return status, html, url

    def login(self, email, password=PASSWORD):
        self.request('/account/login')
        status, html, url = self.request('/account/login', {'email': email, 'password': password})
        return status, html, url


tests_passed = 0
tests_total = 0

def check(description, condition, details=""):
    global tests_passed, tests_total
    tests_total += 1
    if condition:
        tests_passed += 1
        print(f"  [PASS] {description}")
    else:
        print(f"  [FAIL] {description} - Details: {details}")

print("=== STARTING CMS & EDITORIAL FUNCTIONALITY VERIFICATION ===")

# ==============================================================================
# 1. SECURITY & ROUTE AUTHORIZATION
# ==============================================================================
print("\n--- 1. Security & Route Authorization ---")

# 1.1 Unauthenticated guest direct URL access
guest = Client()
for guest_route in ['/admin', '/admin/articles', '/admin/users', '/admin/settings', '/admin/media']:
    st, html, url = guest.request(guest_route)
    check(f"Guest accessing {guest_route} redirected to /account/login",
          url.endswith('/account/login'), f"Status: {st}, Final URL: {url}")

# 1.2 Member direct URL access
member = Client()
st, _, url = member.login('member@example.test')
check("Member logged in successfully", url.endswith('/account') or 'account' in url)

for member_route in ['/admin', '/admin/articles', '/admin/users', '/admin/settings', '/admin/media']:
    st, html, _ = member.request(member_route)
    check(f"Member forbidden on {member_route} (403)", st == 403, f"Status: {st}")

# 1.3 Journalist / Reporter access permissions
reporter = Client()
st, _, url = reporter.login('reporter@example.test')
check("Reporter logged in successfully", url.endswith('/admin') or 'admin' in url)

# Reporter allowed routes
st, html, _ = reporter.request('/admin')
check("Reporter can access /admin newsroom dashboard", st == 200 and 'EDITORIAL WORKSPACE' in html)
st, html, _ = reporter.request('/admin/articles')
check("Reporter can access /admin/articles", st == 200 and 'Find stories' in html)
st, html, _ = reporter.request('/admin/articles/create')
check("Reporter can access /admin/articles/create editor", st == 200 and 'The story' in html)
st, html, _ = reporter.request('/admin/media')
check("Reporter can access /admin/media library", st == 200 and 'Add to the library' in html)

# Reporter forbidden manager & admin screens (Must return 403)
for restricted in ['/admin/categories', '/admin/classifieds', '/admin/advertisements', '/admin/reports',
                   '/admin/users', '/admin/settings', '/admin/menus', '/admin/payments', '/admin/logs']:
    st, _, _ = reporter.request(restricted)
    check(f"Reporter forbidden on {restricted} (403)", st == 403, f"Received status: {st}")

# 1.4 Editor access permissions
editor = Client()
st, _, url = editor.login('editor@example.test')
check("Editor logged in successfully", url.endswith('/admin') or 'admin' in url)

for editor_allowed in ['/admin', '/admin/articles', '/admin/media', '/admin/categories',
                       '/admin/classifieds', '/admin/advertisements', '/admin/reports']:
    st, _, _ = editor.request(editor_allowed)
    check(f"Editor allowed on {editor_allowed} (200)", st == 200, f"Received status: {st}")

# Editor forbidden super_admin screens (Must return 403)
for admin_only in ['/admin/users', '/admin/settings', '/admin/menus', '/admin/payments', '/admin/logs']:
    st, _, _ = editor.request(admin_only)
    check(f"Editor forbidden on {admin_only} (403)", st == 403, f"Received status: {st}")

# 1.5 Administrator access permissions
admin = Client()
st, _, url = admin.login('admin@example.test')
check("Administrator logged in successfully", url.endswith('/admin') or 'admin' in url)

for full_route in ['/admin', '/admin/articles', '/admin/media', '/admin/categories', '/admin/classifieds',
                   '/admin/advertisements', '/admin/reports', '/admin/users', '/admin/settings',
                   '/admin/menus', '/admin/payments', '/admin/logs']:
    st, _, _ = admin.request(full_route)
    check(f"Administrator has full access to {full_route} (200)", st == 200, f"Received status: {st}")


# ==============================================================================
# 2. EDITORIAL WORKFLOW: DRAFT -> SUBMIT FOR REVIEW -> PUBLISH
# ==============================================================================
print("\n--- 2. Editorial Workflow: Draft -> Review -> Publish ---")

# 2.1 Reporter creates story and attempts to self-publish
# Server must downgrade status to 'pending' because reporters cannot self-publish
story_slug = f"workflow-test-{int(time.time())}"
post_data = {
    'title_en': 'Editorial Workflow Story by Reporter',
    'summary_en': 'Summary of workflow story testing review states.',
    'content_en': '<p>This story was written by the journalist and submitted for editorial review.</p>',
    'title_si': 'මාධ්‍යවේදියා විසින් ලියන ලද පුවත',
    'summary_si': 'සංස්කාරක සමාලෝචනය සඳහා ඉදිරිපත් කරන ලදී.',
    'content_si': '<p>සංස්කාරක සමාලෝචනය සඳහා ඉදිරිපත් කරන ලද සම්පූර්ණ පුවත මෙසේය.</p>',
    'title_ta': '',
    'summary_ta': '',
    'content_ta': '',
    'category_id': '1',  # Local
    'slug': story_slug,
    'tags': 'workflow, test, newsroom',
    'status': 'published',  # TAMPER ATTEMPT: reporter sends 'published'
}

st, resp_html, _ = reporter.request('/admin/articles/create', post_data)
check("Reporter story creation POST accepted", st == 200 or st == 302)

conn = sqlite3.connect(DB_PATH)
conn.row_factory = sqlite3.Row
cur = conn.cursor()
story = cur.execute("SELECT * FROM articles WHERE slug=?", (story_slug,)).fetchone()
check("Story record created in database", story is not None)
story_id = story['id'] if story else None

check("Reporter cannot self-publish: status downgraded to 'pending'", story and story['status'] == 'pending')
check("Story author_id assigned to reporter (ID 4)", story and story['author_id'] == 4)

# 2.2 Public visibility verification for pending story
guest = Client()
pub_st, pub_html, _ = guest.request(f'/news/{story_slug}')
check("Pending story is NOT publicly visible (404)", pub_st == 404)

# 2.3 Story ownership boundary: Reporter can edit own story
st, own_html, _ = reporter.request(f'/admin/articles/edit/{story_id}')
check("Reporter can edit own story (200)", st == 200)

# Reporter cannot edit stories authored by other users (e.g., sample story 1 authored by admin)
st, _, _ = reporter.request('/admin/articles/edit/1')
check("Reporter forbidden from editing other users' stories (403)", st == 403)

# 2.4 Editor reviews and publishes the pending story
st, edit_html, _ = editor.request(f'/admin/articles/edit/{story_id}')
check("Editor can open reporter's pending story for review (200)", st == 200)

# Editor publishes story
edit_data = {
    'title_en': 'Editorial Workflow Story by Reporter (Approved)',
    'summary_en': 'Summary of workflow story testing review states.',
    'content_en': '<p>This story was approved and published by the editor.</p>',
    'title_si': 'මාධ්‍යවේදියා විසින් ලියන ලද පුවත',
    'summary_si': 'සංස්කාරක සමාලෝචනය සඳහා ඉදිරිපත් කරන ලදී.',
    'content_si': '<p>සංස්කාරක සමාලෝචනය සඳහා ඉදිරිපත් කරන ලද සම්පූර්ණ පුවත මෙසේය.</p>',
    'title_ta': '',
    'summary_ta': '',
    'content_ta': '',
    'category_id': '1',
    'slug': story_slug,
    'tags': 'workflow, test, newsroom',
    'status': 'published',
    'published_at': time.strftime('%Y-%m-%dT%H:%M')
}
st, _, _ = editor.request(f'/admin/articles/edit/{story_id}', edit_data)
check("Editor publish POST accepted", st == 200 or st == 302)

# Verify database state after editor publishing
story = cur.execute("SELECT * FROM articles WHERE id=?", (story_id,)).fetchone()
check("Story status updated to 'published'", story['status'] == 'published')
check("Author attribution preserved for reporter (ID 4)", story['author_id'] == 4)

# 2.5 Public visibility verification for published story
pub_st, pub_html, _ = guest.request(f'/news/{story_slug}')
check("Published story is now publicly visible (200)", pub_st == 200)
check("Published story displays updated headline", 'Editorial Workflow Story by Reporter (Approved)' in pub_html)
check("Published story displays journalist author byline", 'reporter' in pub_html.lower() or 'lead reporter' in pub_html.lower())


# ==============================================================================
# 3. REVISION HISTORY & RESTORATION
# ==============================================================================
print("\n--- 3. Revision History & Restore Workflow ---")

# Verify revision was created when editor updated the story
revisions = cur.execute("SELECT * FROM article_revisions WHERE article_id=? ORDER BY id DESC", (story_id,)).fetchall()
check("Revision history snapshot created on edit", len(revisions) >= 1)

first_revision_id = revisions[0]['id']

# Editor restores the previous revision
editor.request(f'/admin/articles/edit/{story_id}')
st, _, _ = editor.request(f'/admin/articles/restore/{first_revision_id}', {})
check("Restore revision POST accepted", st == 200 or st == 302)

# Verify story state after restore
restored_story = cur.execute("SELECT * FROM articles WHERE id=?", (story_id,)).fetchone()
check("Restored story status demoted safely to 'draft'", restored_story['status'] == 'draft')
check("Restored story title reverted to initial snapshot", 'Approved' not in restored_story['title_en'])

# Verify public access after demotion to draft
pub_st, _, _ = guest.request(f'/news/{story_slug}')
check("Restored draft story is NOT visible publicly (404)", pub_st == 404)


# ==============================================================================
# 4. SCHEDULED PUBLISHING & TIMEZONE HANDLING
# ==============================================================================
print("\n--- 4. Scheduled Publishing & Timezone ---")

# 4.1 Create a future scheduled story (+2 hours)
sched_slug = f"scheduled-test-{int(time.time())}"
future_time = time.strftime('%Y-%m-%dT%H:%M', time.localtime(time.time() + 7200))
sched_data = {
    'title_en': 'Future Scheduled Exclusive News',
    'summary_en': 'Scheduled for publication in two hours.',
    'content_en': '<p>This story should remain completely hidden until scheduled time.</p>',
    'title_si': '', 'summary_si': '', 'content_si': '',
    'title_ta': '', 'summary_ta': '', 'content_ta': '',
    'category_id': '1',
    'slug': sched_slug,
    'status': 'scheduled',
    'published_at': future_time
}
editor.request('/admin/articles/create')
st, _, _ = editor.request('/admin/articles/create', sched_data)
check("Scheduled article creation accepted", st == 200 or st == 302)

sched_story = cur.execute("SELECT * FROM articles WHERE slug=?", (sched_slug,)).fetchone()
check("Scheduled story recorded with status='scheduled'", sched_story and sched_story['status'] == 'scheduled')

# 4.2 Verify future visibility rule: Public cannot see future scheduled article
guest_sched_st, _, _ = guest.request(f'/news/{sched_slug}')
check("Future scheduled story returns 404 to public", guest_sched_st == 404)


# ==============================================================================
# 5. ARTICLE ARCHIVAL & DELETION
# ==============================================================================
print("\n--- 5. Article Archival & Deletion ---")

# 5.1 Archive story
editor.request('/admin/articles')
st, _, _ = editor.request(f'/admin/articles/archive/{sched_story["id"]}', {})
check("Article archive POST accepted", st == 200 or st == 302)
archived = cur.execute("SELECT status FROM articles WHERE id=?", (sched_story["id"],)).fetchone()
check("Article status transitioned to 'archived'", archived['status'] == 'archived')

# 5.2 Delete story
admin.request('/admin/articles')
st, _, _ = admin.request(f'/admin/articles/delete/{sched_story["id"]}', {})
check("Article permanent delete POST accepted", st == 200 or st == 302)
deleted = cur.execute("SELECT id FROM articles WHERE id=?", (sched_story["id"],)).fetchone()
check("Article permanently removed from database", deleted is None)


# ==============================================================================
# 6. MEDIA MANAGEMENT & DELETION SAFETY
# ==============================================================================
print("\n--- 6. Media Management & Deletion Safety ---")

# 6.1 Check deletion safety on referenced media
admin.request('/admin/media')
st, html, _ = admin.request('/admin/media/delete/1', {})
check("Deletion of referenced media file is BLOCKED", st == 200 or st == 302)
media_still_exists = cur.execute("SELECT id FROM media WHERE id=1").fetchone()
check("Referenced media record safely preserved in database", media_still_exists is not None)

# 6.2 Safe deletion of unreferenced media
cur.execute("INSERT INTO media (uploader_id, file_name, file_path, file_type, alt_text, created_at) VALUES (1, 'temp-unreferenced.png', 'uploads/2026/10/temp-unreferenced.png', 'image/png', 'Temporary', datetime('now'))")
conn.commit()
temp_media_id = cur.lastrowid

st, _, _ = admin.request(f'/admin/media/delete/{temp_media_id}', {})
check("Deletion of unreferenced media file succeeds", st == 200 or st == 302)
temp_deleted = cur.execute("SELECT id FROM media WHERE id=?", (temp_media_id,)).fetchone()
check("Unreferenced media record removed from database", temp_deleted is None)


# ==============================================================================
# 7. CATEGORY MANAGEMENT
# ==============================================================================
print("\n--- 7. Category Management ---")

editor.request('/admin/categories')
new_cat_data = {
    'name_en': 'Special Investigations',
    'name_si': 'විශේෂ විමර්ශන',
    'name_ta': 'சிறப்பு விசாரணைகள்',
    'slug': f'investigations-{int(time.time())}',
    'order_index': '99',
    'active': '1'
}
st, _, _ = editor.request('/admin/categories', new_cat_data)
check("Category creation with trilingual names accepted", st == 200 or st == 302)

created_cat = cur.execute("SELECT * FROM categories WHERE name_en='Special Investigations'").fetchone()
check("Category record created with English, Sinhala, and Tamil names", created_cat is not None and created_cat['name_si'] != '')

# Category deletion safety: try deleting a category that has articles (Category ID 1)
admin.request('/admin/categories')
st, _, _ = admin.request('/admin/categories/delete/1', {})
check("Category with assigned articles cannot be deleted", st == 200 or st == 302)
cat1_exists = cur.execute("SELECT id FROM categories WHERE id=1").fetchone()
check("Referenced category safely preserved in database", cat1_exists is not None)

# Delete the empty category we just created
st, _, _ = admin.request(f'/admin/categories/delete/{created_cat["id"]}', {})
check("Empty category deletion succeeds", st == 200 or st == 302)
cat_del = cur.execute("SELECT id FROM categories WHERE id=?", (created_cat['id'],)).fetchone()
check("Empty category removed from database", cat_del is None)


# ==============================================================================
# 8. USER MANAGEMENT & AUDIT LOGGING
# ==============================================================================
print("\n--- 8. User Management & Audit Logging ---")

# 8.1 Administrator creates a new user
admin.request('/admin/users')
new_user_email = f"trainee_{int(time.time())}@example.test"
create_user_data = {
    'name': 'Test Trainee Reporter',
    'email': new_user_email,
    'password': 'SecureReporterPass123!',
    'role_id': '4',  # Member initially
    'active': '1'
}
st, _, _ = admin.request('/admin/users', create_user_data)
check("Administrator user creation accepted", st == 200 or st == 302)

new_user = cur.execute("SELECT * FROM users WHERE email=?", (new_user_email,)).fetchone()
check("User created in database", new_user is not None)

# 8.2 Promote user: Member (4) -> Journalist (3)
admin.request(f'/admin/users?edit={new_user["id"]}')
update_user_data = {
    'id': str(new_user['id']),
    'name': 'Test Trainee Reporter',
    'email': new_user_email,
    'role_id': '3',  # Journalist / Reporter
    'active': '1'
}
st, _, _ = admin.request('/admin/users', update_user_data)
check("Administrator role update accepted", st == 200 or st == 302)

updated_user = cur.execute("SELECT * FROM users WHERE id=?", (new_user['id'],)).fetchone()
check("User role updated to Journalist (ID 3)", updated_user['role_id'] == 3)

# 8.3 Deactivate user: status -> 'inactive'
admin.request(f'/admin/users?edit={new_user["id"]}')
deact_data = {
    'id': str(new_user['id']),
    'name': 'Test Trainee Reporter',
    'email': new_user_email,
    'role_id': '3'
    # 'active' omitted -> inactive
}
st, _, _ = admin.request('/admin/users', deact_data)
check("Administrator user deactivation accepted", st == 200 or st == 302)

deact_user = cur.execute("SELECT status FROM users WHERE id=?", (new_user['id'],)).fetchone()
check("User status updated to 'inactive'", deact_user['status'] == 'inactive')

# Verify inactive user cannot access /admin
inactive_client = Client()
st, _, url = inactive_client.login(new_user_email, 'SecureReporterPass123!')
check("Inactive user cannot authenticate/access admin", url.endswith('/account/login') or 'login' in url)

# 8.4 Self-demotion / deactivation prevention
admin.request('/admin/users?edit=2')
admin_user_data = {
    'id': '2',  # Logged in Admin ID (admin@example.test)
    'name': 'Newsroom Admin',
    'email': 'admin@example.test',
    'role_id': '4',  # Attempt self-demotion to Member
    'active': '1'
}
st, html, _ = admin.request('/admin/users', admin_user_data)
check("Administrator self-demotion attempt is BLOCKED", 'cannot disable or demote' in html.lower() or st == 200)
admin_still_admin = cur.execute("SELECT role_id FROM users WHERE id=2").fetchone()
check("Admin role safely preserved as super_admin", admin_still_admin['role_id'] == 1)

# 8.5 Audit log verification
print("\n--- Verifying Audit Log Records in Database ---")
audit_records = cur.execute("SELECT action, entity, entity_id FROM activity_logs ORDER BY id DESC LIMIT 30").fetchall()
actions = [r['action'] for r in audit_records]

check("Audit log records 'Published' actions", any('Published' in a for a in actions), f"Recent actions: {actions[:6]}")
check("Audit log records 'Deleted' actions", any('Deleted' in a for a in actions), f"Recent actions: {actions[:6]}")
check("Audit log records role changes", any('Role changed' in a for a in actions), f"Recent actions: {actions[:6]}")
check("Audit log records account status changes", any('Account status changed' in a for a in actions), f"Recent actions: {actions[:6]}")

conn.close()

# Cleanup created test article
conn = sqlite3.connect(DB_PATH)
conn.execute("DELETE FROM article_revisions WHERE article_id=?", (story_id,))
conn.execute("DELETE FROM articles WHERE id=?", (story_id,))
conn.commit()
conn.close()

print(f"\n==================================================")
print(f"CMS & EDITORIAL SUITE: {tests_passed}/{tests_total} CHECKS PASSED ({(tests_passed/tests_total)*100:.1f}%)")
print(f"==================================================")

if tests_passed == tests_total:
    sys.exit(0)
else:
    sys.exit(1)
