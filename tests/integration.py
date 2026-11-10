"""End-to-end HTTP regression tests. Uses a NEW SQLite database, never .env data.
Run: python tests/integration.py --php C:/xampp/php/php.exe
"""
import argparse, base64, http.cookiejar, json, os, pathlib, re, socket, sqlite3
import subprocess, time, urllib.error, urllib.parse, urllib.request, uuid, zipfile, struct, zlib
from datetime import datetime, timedelta, timezone

ROOT = pathlib.Path(__file__).resolve().parents[1]
parser = argparse.ArgumentParser()
parser.add_argument('--php', default='php')
parser.add_argument('--mysql', action='store_true', help='Use an isolated database on TEST_MYSQL_HOST/PORT/USER/PASSWORD')
args = parser.parse_args()
run_id = uuid.uuid4().hex[:10]
runtime = ROOT / 'tests' / '.runtime' / run_id
runtime.mkdir(parents=True)
database = runtime / 'test.sqlite'
with socket.socket() as s:
    s.bind(('127.0.0.1', 0)); port = s.getsockname()[1]
BASE = f'http://127.0.0.1:{port}'
PASSWORD = 'Test-' + uuid.uuid4().hex
env = dict(os.environ, DB_DRIVER='sqlite', DB_PATH=str(database), APP_ENV='test',
           APP_URL=BASE, APP_DEBUG='true', NEWS_ADMIN_PASSWORD=PASSWORD)
if args.mysql:
    env.update(DB_DRIVER='mysql', DB_HOST=os.getenv('TEST_MYSQL_HOST','127.0.0.1'), DB_PORT=os.getenv('TEST_MYSQL_PORT','3307'), DB_USER=os.getenv('TEST_MYSQL_USER','root'), DB_PASSWORD=os.getenv('TEST_MYSQL_PASSWORD',''), DB_NAME='mysql')
checks = 0

def check(condition, message):
    global checks
    if not condition: raise AssertionError(message)
    checks += 1

def cli(*cmd):
    result = subprocess.run([args.php, *cmd], cwd=ROOT, env=env, text=True, capture_output=True)
    check(result.returncode == 0, result.stdout + result.stderr)
    return result.stdout

class Client:
    def __init__(self):
        self.jar = http.cookiejar.CookieJar()
        self.opener = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(self.jar))
        self.token = ''
    def request(self, path, data=None, files=None, csrf=True):
        headers = {}
        body = None
        if data is not None:
            data = dict(data)
            if csrf: data['_token'] = self.token
            if files:
                boundary = 'test' + uuid.uuid4().hex
                parts = []
                for key, value in data.items():
                    parts += [f'--{boundary}\r\nContent-Disposition: form-data; name="{key}"\r\n\r\n{value}\r\n'.encode()]
                for key, name, mime, content in files:
                    parts += [f'--{boundary}\r\nContent-Disposition: form-data; name="{key}"; filename="{name}"\r\nContent-Type: {mime}\r\n\r\n'.encode(), content, b'\r\n']
                parts += [f'--{boundary}--\r\n'.encode()]
                body = b''.join(parts)
                headers['Content-Type'] = 'multipart/form-data; boundary=' + boundary
            else:
                body = urllib.parse.urlencode(data, doseq=True).encode()
                headers['Content-Type'] = 'application/x-www-form-urlencoded'
        request = urllib.request.Request(BASE + path, data=body, headers=headers)
        try:
            response = self.opener.open(request, timeout=10)
        except urllib.error.HTTPError as error:
            response = error
        html = response.read().decode('utf-8', errors='replace')
        found = re.search(r'name="_token" value="([a-f0-9]+)"', html)
        if found: self.token = found[1]
        check(not re.search(r'(Warning|Fatal error)(</b>)?:',html) and 'could not complete this request' not in html, f'PHP error at {path}: {html[:1000]}')
        return response.status, html, response.geturl()
    def login(self, email):
        self.request('/account/login')
        status, html, location = self.request('/account/login', {'email': email, 'password': PASSWORD})
        check(status == 200 and location.endswith('/admin'), 'Staff login failed')

class MysqlRow(dict):
    def __getitem__(self, key):
        return list(self.values())[key] if isinstance(key,int) else super().__getitem__(key)
class MysqlCursor:
    def __init__(self, rows): self.rows=rows
    def fetchone(self): return MysqlRow(self.rows[0]) if self.rows else None
class MysqlDatabase:
    def execute(self, sql, params=()):
        result=subprocess.run([args.php,'tests/db_query.php'], cwd=ROOT, env=env, input=json.dumps({'sql':sql,'params':params}),text=True,capture_output=True,encoding='utf-8')
        if result.returncode: raise RuntimeError(result.stdout+result.stderr)
        return MysqlCursor(json.loads(result.stdout))
    def commit(self): pass
    def close(self): pass
if args.mysql:
    MysqlDatabase().execute(f'CREATE DATABASE news_test_{run_id} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci')
    env['DB_NAME']='news_test_'+run_id
cli('database/migrate.php')
cli('database/migrate.php')
cli('bin/admin.php', 'admin@example.test', 'Test Administrator')
db = MysqlDatabase() if args.mysql else sqlite3.connect(database)
if not args.mysql: db.row_factory = sqlite3.Row
log_path = runtime / 'server.log'
log = log_path.open('w', encoding='utf-8')
server = subprocess.Popen([args.php, '-d', f'upload_tmp_dir={runtime}', '-S', f'127.0.0.1:{port}', '-t', 'public', 'public/router.php'], cwd=ROOT, env=env, stdout=log, stderr=log)
try:
    for _ in range(100):
        try:
            with socket.create_connection(('127.0.0.1', port), timeout=.2): break
        except OSError: time.sleep(.05)
    public, admin, reporter, member, outsider = [Client() for _ in range(5)]
    check(public.request('/')[0] == 200, 'Empty homepage')
    check(public.request('/admin')[2].endswith('/account/login'), 'Anonymous staff access')
    check(public.request('/admin/articles/delete/1')[0] == 404, 'Legacy GET delete must not exist')
    check(public.request('/install.php')[0] == 404, 'Web installer must not exist')
    check(public.request('/.env')[0] == 404, 'Environment must not be served')
    check(public.request('/account/login', {'email':'x','password':'x'}, csrf=False)[0] == 419, 'CSRF required')
    admin.login('admin@example.test')
    for route in ['','articles','articles/create','media','categories','classifieds','advertisements','users','settings','menus','payments','reports','logs']:
        check(admin.request('/admin' + ('/' + route if route else ''))[0] == 200, 'Admin route '+route)
    admin.request('/admin/users', {'name':'Reporter','email':'reporter@example.test','password':PASSWORD,'role_id':3,'active':1})
    reporter.login('reporter@example.test')
    check(reporter.request('/admin/users')[0] == 403, 'Reporter cannot manage users')
    check(reporter.request('/admin/payments')[0] == 403, 'Reporter cannot record payments')
    cat = db.execute("SELECT id FROM categories WHERE slug='local'").fetchone()[0]
    story = {'title_en':'A tested story','content_en':'<p>Verified body <strong>bold</strong><script>alert(1)</script><a href="javascript:alert(1)" onclick="evil()">unsafe</a></p>', 'title_si':'පරීක්ෂණ පුවත','content_si':'<p>සිංහල පුවත</p>','title_ta':'சோதனைச் செய்தி','content_ta':'<p>தமிழ்ச் செய்தி</p>','category_id':cat,'slug':'tested-story','status':'published','is_featured':1,'is_breaking':1,'tags':'integration, tested'}
    reporter.request('/admin/articles/create', story)
    row = db.execute("SELECT * FROM articles WHERE slug='tested-story'").fetchone()
    check(row['status'] == 'pending', 'Reporter publication must become pending')
    check(public.request('/news/tested-story')[0] == 404, 'Pending stories private')
    check('script' not in row['content_en'] and 'javascript:' not in row['content_en'] and 'onclick' not in row['content_en'], 'Stored rich text sanitized')
    check('<strong>bold</strong>' in row['content_en'], 'Safe rich formatting preserved')
    sid = row['id']
    admin.request('/admin/articles/edit/'+str(sid))
    admin.request('/admin/articles/edit/'+str(sid), story)
    check(public.request('/news/tested-story?lang=en')[0] == 200, 'Published story visible')
    check('පරීක්ෂණ පුවත' in public.request('/news/tested-story?lang=si')[1], 'Sinhala edition')
    check('சோதனைச் செய்தி' in public.request('/news/tested-story?lang=ta')[1], 'Tamil edition')
    check('A tested story' in public.request('/search?q=tested&lang=en')[1], 'Search native prepared parameters')
    check(db.execute('SELECT COUNT(*) FROM article_revisions WHERE article_id=?',(sid,)).fetchone()[0] == 1, 'Edits create revision')
    future = (datetime.now(timezone(timedelta(hours=5, minutes=30))) + timedelta(days=1)).strftime('%Y-%m-%dT%H:%M')
    story.update(status='scheduled', published_at=future)
    admin.request('/admin/articles/edit/'+str(sid), story)
    check(public.request('/news/tested-story')[0] == 404, 'Scheduled future story hidden')
    check('A tested story' not in public.request('/edition/breaking')[1], 'Scheduled future absent from breaking')
    db.execute("UPDATE articles SET published_at='2020-01-01 00:00:00' WHERE id=?", (sid,)); db.commit()
    check(public.request('/news/tested-story')[0] == 200, 'Due scheduled story public without cron')
    revision = db.execute('SELECT id FROM article_revisions WHERE article_id=? ORDER BY id DESC',(sid,)).fetchone()[0]
    admin.request('/admin/articles/restore/'+str(revision), {})
    check(db.execute('SELECT status FROM articles WHERE id=?',(sid,)).fetchone()[0] == 'draft', 'Restore becomes draft')
    check(public.request('/news/tested-story')[0] == 404, 'Restored draft is not public')
    story.update(status='published',published_at='',title_ta='',content_ta='')
    admin.request('/admin/articles/edit/'+str(sid), story)
    check(public.request('/news/tested-story?lang=ta')[0] == 404, 'Untranslated edition not duplicated')
    check(public.request('/news/tested-story?lang=en')[0] == 200, 'Translated edition still visible')
    other = dict(story, title_en='Admin-owned story', slug='admin-owned')
    admin.request('/admin/articles/create', other)
    otherid = db.execute("SELECT id FROM articles WHERE slug='admin-owned'").fetchone()[0]
    check(reporter.request('/admin/articles/edit/'+str(otherid))[0] == 403, 'Reporter cannot edit other author')
    def chunk(kind, data):
        return struct.pack('!I',len(data))+kind+data+struct.pack('!I',zlib.crc32(kind+data)&0xffffffff)
    png = b'\x89PNG\r\n\x1a\n'+chunk(b'IHDR',struct.pack('!IIBBBBB',2,2,8,2,0,0,0))+chunk(b'IDAT',zlib.compress(b'\x00'+b'\x4a\x7b\x60'*2+b'\x00'+b'\x4a\x7b\x60'*2))+chunk(b'IEND',b'')
    admin.request('/admin/media')
    admin.request('/admin/media', {'alt_text':'Test image','caption':'Caption'}, [('file','test.php','image/png',png)])
    media = db.execute('SELECT * FROM media ORDER BY id DESC').fetchone()
    check(media is not None and media['file_path'].endswith('.webp'), 'MIME-validated image converted to WebP with safe filename')
    before = db.execute('SELECT COUNT(*) FROM media').fetchone()[0]
    status, html, _ = admin.request('/admin/media', {}, [('file','evil.jpg','image/jpeg',b'<?php echo "not an image";')])
    check('Unsupported file' in html and db.execute('SELECT COUNT(*) FROM media').fetchone()[0]==before, 'Forged upload rejected')
    story.update(featured_image_id=media['id'], **{'gallery_ids[]':[media['id']]},video_url='https://www.youtube.com/watch?v=abcdefghijk')
    admin.request('/admin/articles/edit/'+str(sid), story)
    article_html = public.request('/news/tested-story')[1]
    check('youtube-nocookie.com/embed/abcdefghijk' in article_html and 'Caption' in article_html, 'Video and gallery rendering')
    check('A tested story' in public.request('/edition/videos')[1] and 'A tested story' in public.request('/edition/gallery')[1], 'Media listing pages')
    member.request('/account/register')
    member.request('/account/register', {'name':'Member','email':'member@example.test','password':PASSWORD})
    check(member.request('/admin')[0] == 403, 'Member barred from newsroom')
    outsider.request('/account/register')
    outsider.request('/account/register', {'name':'Other','email':'other@example.test','password':PASSWORD})
    cid = db.execute("SELECT id FROM classified_categories WHERE slug='property'").fetchone()[0]
    listing={'category_id':cid,'language':'en','title':'Test house','description':'An illustrative property','location':'Colombo','price':'25000.50','contact_name':'Test Member','contact_email':'member@example.test','contact_phone':'0771234567','premium_requested':1}
    member.request('/classifieds/create', listing, [('images[]','listing.png','image/png',png)])
    classified=db.execute("SELECT * FROM classifieds WHERE title='Test house'").fetchone(); aid=classified['id']
    check(classified['status']=='pending', 'Classified submitted for review')
    check(public.request('/classifieds/'+str(aid))[0]==404, 'Pending classified private')
    check(outsider.request('/classifieds/edit/'+str(aid))[0]==404, 'Listing ownership enforced')
    admin.request('/admin/classifieds', {'id':aid,'status':'approved','is_premium':1})
    check(db.execute('SELECT status FROM classifieds WHERE id=?',(aid,)).fetchone()[0]=='pending', 'Unpaid premium rejected')
    admin.request('/admin/payments', {'classified_id':aid,'amount':'1000','reference':'TEST-PAY-1','status':'paid'})
    admin.request('/admin/classifieds', {'id':aid,'status':'approved','is_premium':1})
    check(public.request('/classifieds/'+str(aid))[0]==200, 'Approved classified public')
    check('Test house' in public.request('/classifieds?category=property&location=Colombo&min=20000&max=30000')[1], 'Classified category/location/price filters')
    check('Test house' not in public.request('/classifieds?max=100')[1], 'Classified price filter excludes')
    member.request('/classifieds/favorite/'+str(aid), {})
    check(db.execute('SELECT COUNT(*) FROM favorites').fetchone()[0]==1, 'Save favorite')
    member.request('/classifieds/favorite/'+str(aid), {})
    check(db.execute('SELECT COUNT(*) FROM favorites').fetchone()[0]==0, 'Remove favorite')
    admin.request('/admin/payments', {'classified_id':aid,'amount':'1000','reference':'TEST-PAY-1','status':'refunded'})
    check(db.execute('SELECT COUNT(*) FROM payments').fetchone()[0]==1, 'Payment reference update does not duplicate')
    check(db.execute('SELECT is_premium FROM classifieds WHERE id=?',(aid,)).fetchone()[0]==0, 'Refund removes premium')
    member.request('/classifieds/edit/'+str(aid), dict(listing,title='Updated house'))
    check(public.request('/classifieds/'+str(aid))[0]==404, 'Editing requires reapproval')
    member.request('/classifieds/renew/'+str(aid), {})
    check(db.execute('SELECT status FROM classifieds WHERE id=?',(aid,)).fetchone()[0]=='pending', 'Renewal requires review')
    member.request('/classifieds/withdraw/'+str(aid), {})
    check(db.execute('SELECT status FROM classifieds WHERE id=?',(aid,)).fetchone()[0]=='withdrawn', 'Withdraw listing')
    admin.request('/admin/menus', {'name_en':'Board','name_si':'දැන්වීම්','name_ta':'விளம்பரங்கள்','url':'javascript:alert(1)','active':1})
    check(db.execute('SELECT COUNT(*) FROM menus').fetchone()[0]==0, 'Unsafe menu URL rejected')
    admin.request('/admin/menus', {'name_en':'Board','name_si':'දැන්වීම්','name_ta':'விளம்பரங்கள்','url':'/classifieds','active':1})
    check('Board' in public.request('/')[1], 'Menu rendered')
    admin.request('/admin/settings', {'site_name':'Test publication','language_en':1,'language_si':1,'language_ta':1,'ga_id':'G-TEST123','gtm_id':''})
    html=public.request('/')[1]
    check('Test publication' in html and 'data-ga="G-TEST123"' in html, 'Settings and optional analytics')
    check('property="og:' not in html and 'name="description"' not in html and 'application/ld+json' not in html, 'No SEO markup')
    admin.request('/admin/advertisements', {'title':'Test banner','position':'header_728x90','url':'https://example.test','is_active':1}, [('banner','banner.png','image/png',png)])
    check('Test banner' in public.request('/')[1], 'Banner placement')
    advertisement=db.execute('SELECT * FROM advertisements ORDER BY id DESC').fetchone()
    admin.request('/admin/advertisements', {'id':advertisement['id'],'title':'Test banner','position':'header_728x90','url':'https://example.test','is_active':1,'end_date':'2000-01-01'})
    check('Test banner' not in public.request('/')[1], 'Expired banner excluded')
    for placement in ['top_left','top_right','sidebar_300x250','sidebar_300x600','homepage_mid_728x90','footer','in_article','sticky']:
        title='Placement '+placement
        admin.request('/admin/advertisements', {'title':title,'position':placement,'is_active':1}, [('banner','banner.png','image/png',png)])
        target='/news/tested-story' if placement=='in_article' else '/'
        check(title in public.request(target)[1], 'Published placement '+placement)
    campaign=db.execute("SELECT * FROM advertisements WHERE title='Placement top_left'").fetchone()
    admin.request('/admin/advertisements', {'id':campaign['id'],'action':'toggle'})
    check('Placement top_left' not in public.request('/')[1], 'Pause removes public banner')
    admin.request('/admin/advertisements', {'id':campaign['id'],'action':'toggle'})
    check('Placement top_left' in public.request('/')[1], 'Enable republishes banner')
    admin.request('/admin/advertisements', {'id':campaign['id'],'title':campaign['title'],'position':'top_left','is_active':1,'start_date':'2099-01-01'})
    check('Placement top_left' not in public.request('/')[1], 'Future banner excluded')
    check('Scheduled' in admin.request('/admin/advertisements')[1], 'Scheduled banner status visible')
    gif=base64.b64decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7')
    admin.request('/admin/advertisements', {'title':'GIF banner','position':'top_right','is_active':1}, [('banner','animated.gif','image/gif',gif)])
    check(db.execute("SELECT image_path FROM advertisements WHERE title='GIF banner'").fetchone()[0].endswith('.gif'), 'GIF banners retain original format for animation')
    admin.request('/admin/advertisements', {'title':'Feed sponsor','ad_format':'text','position':'inline_feed','advertiser':'Sample partner','ad_text':'Offers <today> & tomorrow','url':'https://example.test/deals','cta_label':'See deals','is_active':1})
    feed=public.request('/')[1]
    check('Feed sponsor' in feed and 'Offers &lt;today&gt; &amp; tomorrow' in feed and 'See deals' in feed, 'Text feed ad renders escaped copy and CTA')
    admin.request('/admin/advertisements', {'title':'Sponsored feature','ad_format':'native','position':'footer','ad_text':'A sponsored message','is_active':1}, [('banner','native.png','image/png',png)])
    db.execute("UPDATE advertisements SET is_active=0 WHERE title='Placement footer'"); db.commit()
    check('Sponsored feature' in public.request('/')[1], 'Sponsored card publishes with image')
    admin.request('/admin/advertisements', {'title':'Popup offer','ad_format':'text','position':'popup','ad_text':'Limited offer','is_active':1})
    check('data-ad-popup' in public.request('/')[1] and 'Popup offer' in public.request('/')[1], 'Popup placement renders dismissible overlay')
    mp4=b'\x00\x00\x00\x18ftypisom\x00\x00\x00\x00isomiso2'
    admin.request('/admin/advertisements', {'title':'Video sponsor','ad_format':'video','position':'in_article','is_active':1}, [('banner','spot.mp4','video/mp4',mp4)])
    db.execute("UPDATE advertisements SET is_active=0 WHERE title='Placement in_article'"); db.commit()
    check('<video controls' in public.request('/news/tested-story')[1] and 'Video sponsor' in public.request('/news/tested-story')[1], 'Video advertisement publishes inside article')
    admin.request('/admin/advertisements', {'title':'Trusted ad','ad_format':'html','position':'popup','ad_code':'<div id="trusted-ad">Provider code</div>','is_active':1})
    check(db.execute("SELECT ad_format FROM advertisements WHERE title='Trusted ad'").fetchone()[0]=='html', 'Super administrator can save trusted ad code')
    before=db.execute('SELECT COUNT(*) FROM advertisements').fetchone()[0]
    admin.request('/admin/advertisements', {'title':'Missing copy','ad_format':'text','position':'footer','is_active':1})
    check(db.execute('SELECT COUNT(*) FROM advertisements').fetchone()[0]==before, 'Text advertisement requires copy')
    admin.request('/admin/advertisements', {'title':'Wrong video file','ad_format':'video','position':'footer','is_active':1}, [('banner','brochure.pdf','application/pdf',b'%PDF-1.4\n1 0 obj\n<<>>\nendobj\n%%EOF')])
    check(db.execute('SELECT COUNT(*) FROM advertisements').fetchone()[0]==before, 'Video format rejects PDF uploads')
    check(reporter.request('/admin/advertisements', {'id':campaign['id'],'action':'toggle'})[0]==403, 'Reporter cannot pause campaign')
    check(db.execute('SELECT COUNT(*) FROM activity_logs').fetchone()[0]>10, 'Activity log populated')
    user=db.execute("SELECT * FROM users WHERE email='member@example.test'").fetchone()
    admin.request('/admin/users', {'id':user['id'],'name':user['name'],'email':user['email'],'role_id':4})
    check(member.request('/account')[2].endswith('/account/login'), 'Disabled account loses existing session')
    backup = cli('bin/backup.php')
    archive = pathlib.Path(re.search(r'Backup created: (.+)',backup).group(1).strip())
    with zipfile.ZipFile(archive) as z:
        check(z.testzip() is None and any(n.endswith('.sql' if args.mysql else '.sqlite') for n in z.namelist()), 'Backup archive valid')
    attacker=Client(); attacker.request('/account/login')
    for _ in range(6): result=attacker.request('/account/login', {'email':'no-user@example.test','password':'wrong'})
    check(result[0]==429, 'Persistent login throttling')
    fresh=Client(); fresh.request('/account/login')
    check(fresh.request('/account/login', {'email':'no-user@example.test','password':'wrong'})[0]==429, 'New session cannot bypass account throttling')
    for n in range(6): result=fresh.request('/account/login', {'email':f'alternate{n}@example.test','password':'wrong'})
    check(result[0]==429, 'Changing email cannot bypass IP throttling')
    db.execute("INSERT INTO categories (name_en,name_si,name_ta,slug,status,order_index) VALUES ('Business','Business','Business','business-legacy','active',99)")
    duplicate_id=db.execute("SELECT id FROM categories WHERE slug='business-legacy'").fetchone()[0]
    db.execute("UPDATE articles SET category_id=? WHERE slug='tested-story'", (duplicate_id,)); db.commit()
    for path in ['/category/business?lang=en','/category/business-legacy?lang=en']:
        status, html, _ = public.request(path)
        check(status==200 and 'A tested story' in html, 'Both Business routes include articles from duplicate category')
        for nav_class in ['primary-nav','desk-tabs']:
            nav=re.search(r'<nav class="'+nav_class+r'".*?</nav>',html,re.S).group(0)
            check(len(re.findall(r'>Business</a>',nav))==1, 'Business appears once in '+nav_class)
            check(re.search(r'<a [^>]*class="active"[^>]*>Business</a>',nav) is not None, 'Business stays active on both category routes')
    check(db.execute('SELECT id FROM categories WHERE id=?',(duplicate_id,)).fetchone() is not None, 'Navigation grouping preserves category records')
    print(f'PASS: {checks} checks. HTTP workflows, role boundaries, CSRF, translations, publishing, revisions, uploads, classifieds, payments, settings, banners, throttling and backups.')
finally:
    server.terminate()
    server.wait(timeout=10)
    log.close()
    db.close()
    print(f'Test artifacts: {runtime}')
