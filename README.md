# Lanka News Hub — web implementation

This extends the existing PHP project using the supplied **Dev Doc News web.pdf**. The implementation is **web only**. No Android/iOS app, SEO metadata, structured data, sitemap, indexing automation, Search Console, or robots-management features were added. The website adapts to desktop, tablet and phone browser widths; that is responsive web layout, not a mobile application.

## What is implemented

- Separate English, Sinhala and Tamil content editions, language-aware categories, search, latest/breaking news, featured stories, video news, galleries and article sharing to Facebook, WhatsApp, X, Telegram and LinkedIn.
- A newsroom with rich-text editing, drafts, journalist review submissions, scheduled publishing, article editing, revision history/restoration, featured images, gallery images, YouTube and uploaded videos, tags and archiving.
- A media library for JPEG/PNG/WebP/GIF, MP4/WebM and PDF. Server-side MIME validation, random safe filenames, a 25 MB limit and a 24-megapixel image limit. GD compresses images to WebP and scales images to fit within 2000 × 2000. Animated GIFs become a still WebP when converted. Without GD, valid images retain their original format.
- A distinct `/classifieds` marketplace: property, jobs, vehicles, business promotions, matrimonial, services and community notices; category/location/text/price filters; member registration and login; eight-photo submissions; ownership checks; edits, renewal requests, withdrawal, saved listings and public contact details.
- Classified moderation: only approved, unexpired listings are public. Edits and renewals return to review; approval starts a 30-day publication period. Decisions and review notes appear in the member dashboard.
- Premium listings backed by a **manual payment ledger**. Administrators record externally received payments, with unique references and pending/paid/refunded statuses. Editors can then enable premium placement. Refunding the last paid record removes premium status. **No card is charged and no live payment gateway is connected.**
- Header, sidebar, homepage, footer, in-article and dismissible sticky ads. Active campaigns rotate on page load and respect date windows. Only administrators can enter trusted AdSense/custom script code.
- Administrator/editor/journalist/member roles, account activation, user management, language availability, extra navigation links, site settings, reports and activity logs.
- Optional Google Analytics and Google Tag Manager ID settings. External analytics scripts stay off until a reader consents. Both are disabled by default. Do not configure the same Analytics property twice through direct integration and GTM.
- Session protection, CSRF validation for every POST, prepared queries, safe article HTML, per-account/per-IP login throttling, protected upload paths, database indexes, image lazy loading, static-asset cache headers, GZIP configuration and a CLI backup utility.

UI labels in the main news navigation switch languages. Editorial and account-management forms use English; content translations are entered by the newsroom, not generated automatically. Untranslated stories are omitted from that edition. The recommended separate subdomain installations can be deployed later; the delivered application runs all editions and classifieds from one installation with centralized administration.

## Stitch design update

The active portal follows the supplied `stitch_liontech_news_design_system` references: crimson masthead, serif news headings, editorial story grids, latest-news rail, marketplace, account screens and a horizontal newsroom navigation. Advertisement publishing remains integrated with image/GIF, video, text, sponsored cards and administrator-only trusted HTML campaigns. Format shortcuts and campaign status counts are available at `/admin/advertisements`.

The design uses local Inter and Playfair Display fonts under `public/assets/fonts`, with their license files included. Responsive browser layouts, keyboard navigation and dark-theme styles are included. No mobile application or SEO features were added.

For an existing installation, upload the updated `app/` and `public/assets/` files together, including the new `editorial.css` and fonts directory. Preserve the server's `.env`, database and uploaded media. This visual update adds no database schema changes; installations that have not received the earlier advertisement-format update still need `php database/migrate.php` from the project root in the hosting terminal. The migration is not a public browser page.

## Requirements

- PHP 8.1+ (tested with PHP 8.2.12), PDO MySQL, mbstring, DOM, fileinfo; GD with WebP for compression and ZipArchive for backups.
- MySQL 5.7+ or MariaDB 10.4+ (tested on MariaDB 10.4.32). SQLite/PDO SQLite is also supported for local previews and tests.
- Apache with rewrite support, or PHP's built-in server for local development only.
- No Node packages, framework downloads, CDN CSS or JavaScript dependencies are required.

## Existing installation / production setup

1. Back up the existing database and `public/uploads` before deployment. The migration is additive and does not run the old sample SQL or overwrite existing articles/categories/users.
2. Set the document root to **`public/`**. Keep `.env`, `storage`, source code, the specification PDF and archives outside the public document root.
3. Configure `.env` using `.env.example`. For MySQL, create the selected UTF-8 database first and set `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASSWORD` and the full `APP_URL`. Set `APP_ENV=production` and `APP_DEBUG=false`.
4. Run from the project root:

   ```sh
   php database/migrate.php
   ```

5. Existing users retain their passwords. For a **new** installation, create the first administrator with a unique password passed via `NEWS_ADMIN_PASSWORD`:

   ```powershell
   $env:NEWS_ADMIN_PASSWORD = Read-Host 'Choose an administrator password (12+ characters)'
   C:/xampp/php/php.exe bin/admin.php editor@example.com 'Chief Editor'
   Remove-Item Env:NEWS_ADMIN_PASSWORD
   ```

   The command refuses to overwrite an existing account. There is no default administrator password and no web-based installer. The previous `/install.php` that reset credentials is disabled.

6. Give the PHP worker write access to `storage/` and `public/uploads/`. Sessions are kept under `storage/sessions`. For multi-server operation, replace local sessions and local media with shared storage/session infrastructure before adding nodes.
7. Sign in at `/admin/login`, review category translations, publish content, and configure the publication/contact details. Existing category names and article translations are preserved exactly by the migration; correct any old English text stored in Sinhala fields in the editor.

The old `database/seed.sql` is retained as a legacy file. **Do not run it against an existing or live installation:** it contains sample content and overwriting upserts. The migration does not invoke it. Legacy controllers/views remain as historical source but are not used by `app/routes.php`; the active application uses `NewsController`, `AccountController`, `ClassifiedController`, `NewsroomController` and `app/Views/portal`.

### Upload configuration

`public/.user.ini` supplies FPM/CGI limits and `public/.htaccess` supplies equivalent mod_php limits. Confirm the hosting provider honors them: `upload_max_filesize=25M`, `post_max_size=210M`, `memory_limit=256M`. Set PHP's `upload_tmp_dir` to a writable, non-public directory. The preview script configures this automatically. PHP may reject oversized requests before application validation.

### Local preview without MySQL

This uses `storage/preview.sqlite` and overrides settings only in the current process; it does not change `.env` or use the production database.

```powershell
# First run only: choose the local demo account password.
$env:NEWS_DEMO_PASSWORD = Read-Host 'Choose a local demo password (12+ characters)'
./bin/preview.ps1
```

Open `http://127.0.0.1:8080`. The demo account is `preview@example.test` with the password supplied on its first creation. Subsequent runs keep existing demo data and credentials. Choose a different port with `./bin/preview.ps1 -Port 8082`. Stop the server with Ctrl+C. The preview includes clearly labelled fictional stories, local vector illustrations and sample classifieds. Never copy the demo database to production.

For a configured database without demo data:

```sh
php -S 127.0.0.1:8080 -t public public/router.php
```

Set `APP_URL=http://127.0.0.1:8080` in the process environment for this command. The development server is not a production hosting solution.

## Editorial and payment behavior

All publication input/output uses **Asia/Colombo, UTC+05:30**. A scheduled article is included in public queries as soon as its publication time is reached; no cron is needed. Its stored status remains `scheduled` until edited, so reports describe stored editorial status while the story listing indicates whether it is now published. Cache deployments must not serve stale future-publication responses.

Revision restoration restores language content, tags, video URL and gallery IDs as a **draft**. It preserves the current author, category, address, featured/uploaded-video references and view count. The editor shows the previous version so it can be checked before restoring. Editors should coordinate when editing the same story; there is not yet collaborative editing or an optimistic concurrency lock.

Payments are a bookkeeping workflow, not a payment gateway. A `paid` record means the administrator has confirmed an external payment. Refunds must be performed outside the website before marking the record refunded. Premium pricing, gateway selection, webhook verification, receipt delivery and real transaction processing require a separate provider integration.

## Backups

```sh
php bin/backup.php
```

Creates a database export and a ZIP including `public/uploads` in `storage/backups`. MySQL exports use a consistent transaction for InnoDB data; SQLite uses `VACUUM INTO`. Backups contain account hashes and contact information: keep the directory private and copy backups to access-controlled off-site storage. Avoid schema migrations during a backup and briefly stop editorial/uploads if you need the database and filesystem captured at exactly the same instant.

To restore, use a **new, empty** database, import the SQL export (or copy the SQLite file to a new configured `DB_PATH`), extract uploads into a fresh deployment's `public/uploads`, then run the migration and verify the restored installation before switching traffic. The SQL export intentionally has no `DROP` statements. Schedule this CLI command using your host's scheduler and test restore procedures; daily scheduling/off-site transfer are not configured on this computer.

## Verification

The test suite creates a new isolated database for every run, starts/stops its own local PHP server, and keeps artifacts under `tests/.runtime`. It never uses the database selected by `.env`.

```sh
python tests/integration.py --php C:/xampp/php/php.exe
```

For an **isolated MySQL test server**, set `TEST_MYSQL_HOST`, `TEST_MYSQL_PORT`, `TEST_MYSQL_USER`, `TEST_MYSQL_PASSWORD`, then add `--mysql`. The test account needs permission to create `news_test_<random>` databases. They are kept for inspection; the suite does not delete databases.

Checks cover migration reruns, all newsroom screens, unauthorized/role/ownership access, CSRF, multilingual rendering, native prepared search, future/due scheduled stories, revisions, rich-text sanitization, safe filenames/WebP, forged uploads, moderation, classifieds filters, favorites, renewal, refunds, disabled-account sessions, analytics settings, banner dates, login throttling and backup ZIP integrity. Browser QA also covers the desktop homepage/dashboard, Tamil editor save, and narrow layouts for news, classifieds and the editor.

## Hosting work still required

The document includes infrastructure/services that cannot be activated by application code alone: domain/subdomains, HTTPS certificates, firewall/WAF, malware scanning, uptime/daily monitoring, scheduled off-site backups, Cloudflare CDN, LiteSpeed cache and Redis. These require a selected host and service credentials. No such external resources have been provisioned or billed.

Dynamic pages deliberately send `Cache-Control: no-store` because they contain sessions, CSRF tokens and personalized/language-dependent content. Static media, CSS and JS can be cached. A production full-page cache needs explicit bypass rules for accounts/admin/forms and language-aware cache keys. High-traffic capacity has not been load-tested. Styles/scripts are local; a minification/build pipeline is not configured. Use a host asset optimizer if needed after functional verification.

No mobile apps, push-notification service, email delivery, payment gateway, app-store integration, SEO/Search Console or live deployment is included.


## Advertisement publishing and the updated news layout

The public layout now follows a compact red-and-white news format inspired by Ada Derana, using this publication's own branding and content. The homepage includes a wide leaderboard, two top banner slots, a main news column, a latest-updates column, and a sidebar with display advertising and media stories. The paired banners stack on narrow browser screens.

To publish an advertisement:

1. Sign in as an administrator or editor and open **Ad publishing** (`/admin/advertisements`).
2. Enter a campaign title, advertiser name, format and placement. The placement map highlights the selected location.
3. For image ads, upload JPG, PNG, WebP or GIF. For video ads, upload MP4 or WebM. Text ads need a headline and body; sponsored cards can also include an image. Super administrators can publish AdSense or other trusted HTML code. Files are limited to 25 MB. GIF animation is preserved.
4. Add an optional HTTP(S) destination and call-to-action label, then optional start/end dates. Dates use Sri Lanka time; blank dates mean immediate publication without expiry.
5. Leave **Enable publication** checked and click **Save campaign**. Use **Pause**, **Enable**, or **Edit** on campaign cards. Status labels distinguish Published, Scheduled, Paused and Expired.

Supported placements: top leaderboard, top left/right, sidebar rectangle, homepage sidebar tower, between homepage news sections, inside the news feed, footer, in-article, sticky bottom and a dismissible pop-up shown once per browser session. Active campaigns sharing a placement rotate on page load. Empty placements take no space. The 300 × 600 tower is hidden on narrow screens. Existing databases receive the new format and copy fields through the additive migration.

The local preview includes four clearly labelled sample campaigns. They can be edited or paused from the same screen. `bin/demo-banners.php` seeds those examples only when `APP_ENV=demo`; it never seeds production campaigns. Actual advertiser artwork and campaign URLs should replace the samples before launch.
#   L a n k a - N e w s - H u b - F l u t t e r - m o b i l e - a p p  
 #   L a n k a - N e w s - H u b - F l u t t e r - m o b i l e - a p p  
 