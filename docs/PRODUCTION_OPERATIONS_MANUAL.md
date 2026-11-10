# ==============================================================================
# Lanka News Hub — Production Operations & DevOps / SRE Manual
# Platform: Web Only (No Mobile Infrastructure | No SEO Infrastructure)
# Target Environments: Local, Staging, Production
# ==============================================================================

## 1. Deployment Architecture

### 1.1 High-Level Infrastructure Topology

```
                         [ Global Public Traffic ]
                                     │ (HTTPS Only)
                                     ▼
                ┌─────────────────────────────────────────┐
                │         Cloudflare Edge Network         │
                │  - DNSSEC & Anycast DNS                 │
                │  - WAF (OWASP Core Ruleset, Bot Mgmt)   │
                │  - Rate Limiting (Login, Submission)    │
                │  - CDN Static Asset Caching (30 Days)   │
                │  - SSL Termination / Origin CA TLS      │
                └────────────────────┬────────────────────┘
                                     │ (Cloudflare Authenticated Origin Pulls)
                                     ▼
                ┌─────────────────────────────────────────┐
                │        Origin Host Network / VPC        │
                │  - Firewall / UFW (Allow Cloudflare IPs)│
                │  - Nginx Reverse Proxy & HTTP Server    │
                └────────────────────┬────────────────────┘
                                     │ (FastCGI Unix Domain Socket)
                                     ▼
                ┌─────────────────────────────────────────┐
                │             PHP-FPM 8.2 Pool            │
                │  - OPcache bytecode cache               │
                │  - Isolated pool user: www-data         │
                │  - Restricted execution boundaries      │
                └───────────┬─────────────────┬───────────┘
                            │                 │
             (Unix Socket / TCP)       (Local Redis Port 6379)
                            ▼                 ▼
             ┌─────────────────────┐   ┌─────────────────────┐
             │   MySQL 8.0+ /      │   │  Redis Object Cache │
             │   MariaDB 10.4+     │   │  - Query Caching    │
             │   - utf8mb4_unicode │   │  - Shared Sessions  │
             │   - Async IO / ACID │   └─────────────────────┘
             └─────────────────────┘
```

### 1.2 Zero-Downtime Release Directory Structure

Production and Staging systems adhere to the atomic symlink release layout:

```
/var/www/news_page/
├── current -> /var/www/news_page/releases/20261005120000  (Atomic Symlink)
├── releases/
│   ├── 20261005113000/
│   ├── 20261005114500/
│   └── 20261005120000/                                   (Current Active)
└── shared/
    ├── .env                                              (Mode 0600 - Root/www-data)
    ├── storage/
    │   ├── logs/                                         (app.log, error.log, cron.log)
    │   ├── cache/                                        (File-based page/query cache)
    │   ├── sessions/                                     (Encrypted PHP session data)
    │   └── backups/                                      (Timestamped zip archives)
    └── public/
        └── uploads/                                      (User media, photos, banners)
```

---

## 2. Server Requirements & Hardening

### 2.1 Hardware Baseline Specifications

| Resource | Staging Environment | Production Environment (High Traffic) |
| :--- | :--- | :--- |
| **vCPU** | 2 Cores | 4 - 8 Cores (Dedicated Compute) |
| **RAM** | 4 GB | 16 GB (8 GB allocated to MySQL InnoDB Buffer Pool) |
| **Storage** | 40 GB NVMe SSD | 160 GB NVMe SSD (RAID-10 or Managed EBS gp3) |
| **Network** | 100 Mbps | 1 Gbps+ redundant uplink |
| **OS** | Ubuntu 22.04 / 24.04 LTS | Ubuntu 22.04 / 24.04 LTS or Debian 12 |

### 2.2 PHP 8.2 Runtime Tuning (`/etc/php/8.2/fpm/php.ini`)

```ini
; Production Security
expose_php = Off
display_errors = Off
display_startup_errors = Off
log_errors = On
error_log = /var/log/php8.2-fpm.log
error_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT

; Memory & Execution Limits
memory_limit = 256M
max_execution_time = 30
max_input_time = 60
post_max_size = 25M
upload_max_filesize = 25M
max_file_uploads = 10

; Session Security
session.cookie_httponly = 1
session.cookie_secure = 1
session.cookie_samesite = "Lax"
session.use_strict_mode = 1
session.gc_maxlifetime = 86400

; OPcache Bytecode Optimization
opcache.enable = 1
opcache.enable_cli = 0
opcache.memory_consumption = 256
opcache.interned_strings_buffer = 16
opcache.max_accelerated_files = 10000
opcache.validate_timestamps = 0  ; (Zero filesystem checks; flushed on deploy)
opcache.save_comments = 1
opcache.revalidate_freq = 0
```

### 2.3 PHP-FPM Pool Configuration (`/etc/php/8.2/fpm/pool.d/www.conf`)

```ini
[www]
user = www-data
group = www-data
listen = /run/php/php8.2-fpm.sock
listen.owner = www-data
listen.group = www-data
listen.mode = 0660

pm = dynamic
pm.max_children = 50
pm.start_servers = 10
pm.min_spare_servers = 5
pm.max_spare_servers = 20
pm.max_requests = 1000
pm.process_idle_timeout = 10s

; Environment Isolation
security.limit_extensions = .php
php_admin_value[open_basedir] = /var/www/news_page/:/tmp/
```

### 2.4 File Permissions & Ownership Matrix

```bash
# Set ownership
chown -R www-data:www-data /var/www/news_page

# Code directory lockdown (Read-only for web worker)
find /var/www/news_page/releases -type d -exec chmod 755 {} \;
find /var/www/news_page/releases -type f -exec chmod 644 {} \;

# Writable state directories
chmod -R 775 /var/www/news_page/shared/storage
chmod -R 775 /var/www/news_page/shared/public/uploads

# Sensitive configuration isolation
chmod 600 /var/www/news_page/shared/.env
chmod +x /var/www/news_page/releases/*/bin/*.sh
```

---

## 3. Database Architecture & Permissions

### 3.1 Character Encoding & Multi-Language Support
* **Character Set:** `utf8mb4`
* **Collation:** `utf8mb4_unicode_ci` (Supports Sinhala `[\x{0D80}-\x{0DFF}]`, Tamil `[\x{0B80}-\x{0BFF}]`, and English)
* **Timezone:** `+05:30` (`Asia/Colombo`)

### 3.2 Production Database Roles & Least-Privilege Grants

```sql
-- 1. Create Production Database with Explicit Collation
CREATE DATABASE news_hub_prod
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

-- 2. Web Application Runtime User (DML Only — No DDL)
CREATE USER 'news_web_user'@'localhost' IDENTIFIED BY 'STRONG_SECRET_APP_KEY';
GRANT SELECT, INSERT, UPDATE, DELETE ON news_hub_prod.* TO 'news_web_user'@'localhost';

-- 3. Migration & Backup Automation User (Administrative Privileges)
CREATE USER 'news_admin_user'@'localhost' IDENTIFIED BY 'STRONG_SECRET_ADMIN_KEY';
GRANT ALL PRIVILEGES ON news_hub_prod.* TO 'news_admin_user'@'localhost';

FLUSH PRIVILEGES;
```

---

## 4. HTTPS, SSL/TLS & Web Server Security

### 4.1 Automated ACME Certificate Provisioning & Renewal
Certbot automates Let's Encrypt certificates:

```bash
# Initial certificate acquisition
certbot certonly --webroot -w /var/www/letsencrypt -d news.example.lk -d www.news.example.lk \
  --agree-tos --email admin@example.lk --non-interactive

# Automated Renewal via Systemd Timer (/etc/systemd/system/certbot.timer)
# Renewal hook reloads Nginx without downtime:
echo 'deploy-hook = systemctl reload nginx' >> /etc/letsencrypt/cli.ini
```

### 4.2 Strict Security Headers & Rewrites
Configured in `deploy/nginx/news_page.conf`:
* `Strict-Transport-Security`: `max-age=31536000; includeSubDomains; preload`
* `X-Content-Type-Options`: `nosniff`
* `X-Frame-Options`: `SAMEORIGIN`
* `Referrer-Policy`: `strict-origin-when-cross-origin`
* `Permissions-Policy`: `camera=(), microphone=(), geolocation=(), payment=()`
* Script Execution Prevention in `/uploads/`: All requests to `.php`, `.phtml`, `.sh`, `.exe`, `.cgi` inside `/uploads/` return **HTTP 403 Forbidden**.

---

## 5. CDN & WAF Architecture (Cloudflare)

### 5.1 DNS & Origin Protection
* **DNS Records:** Apex `news.example.lk` and `www` configured as CNAME / A records proxied (Orange Cloud).
* **SSL/TLS Mode:** Full (Strict) with Cloudflare Origin CA certificate installed on origin Nginx.
* **Origin Firewall:** Port 80 and 443 restricted exclusively to Cloudflare IP ranges using UFW:
  ```bash
  for ip in $(curl -s https://www.cloudflare.com/ips-v4); do ufw allow from $ip to any port 443 proto tcp; done
  for ip in $(curl -s https://www.cloudflare.com/ips-v6); do ufw allow from $ip to any port 443 proto tcp; done
  ufw reload
  ```
* **Real-IP Restoration:** `deploy/nginx/cloudflare_ips.conf` restores the real visitor IP from `CF-Connecting-IP`.

### 5.2 Edge Caching Rules
* **Static Assets:** Match `*.css`, `*.js`, `*.jpg`, `*.png`, `*.webp`, `*.svg`, `*.woff2` -> **Cache Everything**, Edge Cache TTL = 30 days.
* **Dynamic Content Bypass:** Match `/admin/*`, `/account/*`, `/classifieds/create`, `/health` -> **Bypass Cache**.

### 5.3 Edge WAF & Rate Limiting Directives
* **WAF Rules:** Enable OWASP Core Ruleset (Paranoia Level 1), Bot Fight Mode, and block known malicious scrapers.
* **Rate Limiting Policies:**
  1. Login Protection: `/admin/login` and `/account/login` -> Threshold: 5 requests / 60 seconds. Action: Managed Challenge / Block.
  2. Member Registration: `/account/register` -> Threshold: 3 requests / 5 minutes. Action: Block.
  3. Classified Ad Postings: `/classifieds/create` -> Threshold: 5 submissions / 5 minutes. Action: Block.
  4. Search Flood Protection: `/search` -> Threshold: 60 queries / 60 seconds. Action: Managed Challenge.

---

## 6. Backup & Disaster Recovery SLA

### 6.1 SLA Targets
* **Recovery Point Objective (RPO):** 24 hours (Automated nightly snapshots).
* **Recovery Time Objective (RTO):** < 15 minutes (Single command automated restoration).

### 6.2 Backup Components
1. **Database:** Full transactional snapshot via `php bin/backup.php` with `SET TRANSACTION ISOLATION LEVEL REPEATABLE READ`.
2. **Media:** Recursive archive of `/var/www/news_page/shared/public/uploads/`.
3. **Critical Config:** Automated bundle of `.env` and `deploy/` virtual host configs.

### 6.3 Automated Schedule & Systemd Timers
* Service: `deploy/systemd/news-backup.service`
* Timer: `deploy/systemd/news-backup.timer` triggers daily at 02:00 Colombo time (`OnCalendar=*-*-* 02:00:00`).
* Offsite Push: `bin/backup-offsite.sh` encrypts archives using AES-256 GPG and synchronizes to AWS S3 / Cloudflare R2 with 7-day local, 30-day standard, and 365-day cold archive retention.

---

## 7. Disaster Recovery & Restore Procedure

In the event of database corruption, ransomware, or catastrophic host loss:

### Step 1: Provision Clean System & Shared Directory
```bash
mkdir -p /var/www/news_page/shared/storage/backups
mkdir -p /var/www/news_page/shared/public/uploads
```

### Step 2: Fetch and Decrypt Backup Archive
```bash
# Retrieve from cloud off-site storage
aws s3 cp s3://news-hub-production-backups/media-db/news-20261005-020000.zip.gpg /var/www/news_page/shared/storage/backups/

# Decrypt using master encryption key
gpg --decrypt --batch --passphrase "$BACKUP_ENCRYPTION_KEY" \
  /var/www/news_page/shared/storage/backups/news-20261005-020000.zip.gpg > /var/www/news_page/shared/storage/backups/news-restore.zip
```

### Step 3: Execute Automated Restore Utility
```bash
cd /var/www/news_page/current
php bin/restore.php /var/www/news_page/shared/storage/backups/news-restore.zip --force
```

### Step 4: Health Verification
```bash
curl -i http://127.0.0.1/health
```

---

## 8. 24/7 Monitoring & Alerting Matrix

| Monitor Target | Metric / Path | Tool / Probe | Threshold | Alert Severity |
| :--- | :--- | :--- | :--- | :--- |
| **Endpoint Health** | `/health` HTTP probe | Uptime Kuma / Datadog | HTTP status != 200 | **P1 - Critical (Pager)** |
| **Host Disk Space** | Root `/` & `/var/www` | Node Exporter / Prometheus | > 85% full | **P2 - Warning (Slack)** |
| **System Memory** | RAM utilization | Netdata / Node Exporter | > 90% for 5 mins | **P2 - Warning (Slack)** |
| **Database Status** | MySQL `threads_running` | Prometheus mysqld_exporter | > 80 connections | **P1 - Critical (Pager)** |
| **Slow Queries** | MySQL `Slow_queries` | MySQL Slow Query Log | Query time > 1.0s | **P3 - Low (Ticket)** |
| **Application Errors** | `storage/logs/error.log` | Vector / Loki / CloudWatch | > 10 errors / min | **P2 - Warning (Slack)** |
| **Cron Execution** | `news-cron.timer` | Healthchecks.io heartbeat | Missed execution > 10m | **P2 - Warning (Slack)** |
| **Nightly Backups** | `news-backup.timer` | `bin/backup-offsite.sh` | Age > 26 hours | **P1 - Critical (Pager)** |

---

## 9. Zero-Downtime Deployment SOP

### Deployment Execution Flow:
1. CI pipeline completes test suites and packages `release.tar.gz`.
2. Staging is automatically deployed and verified.
3. Production deployment requires explicit authorized peer review approval in GitHub Environments.
4. SSH into production host:
   ```bash
   cd /var/www/news_page
   ./bin/deploy.sh
   ```
5. `bin/deploy.sh` automatically performs:
   * Pre-deployment state backup
   * Unpacks new release into `releases/<timestamp>`
   * Symlinks shared `.env`, `storage/`, and `public/uploads/`
   * Executes idempotent database migrations (`php database/migrate.php`)
   * Clears application & OPcache bytecode caches
   * Hardens directory and file permissions (`chown www-data`, `chmod 755/644/600`)
   * Atomically flips `/var/www/news_page/current` symlink
   * Reloads `php8.2-fpm` and `nginx`
   * Performs automated smoke tests on `/health` and homepage `/`
   * Automatically rolls back if any smoke check fails.

---

## 10. Instantaneous Rollback Procedure

If unexpected runtime regressions are detected after deployment:

```bash
# Execute instantaneous one-command rollback:
cd /var/www/news_page/current
./bin/rollback.sh

# Or rollback to a specific release timestamp:
./bin/rollback.sh /var/www/news_page/releases/20261005113000
```

`rollback.sh` atomically flips the `current` symlink to the previous stable release, reloads PHP-FPM/Nginx, flushes stale caches, and verifies that the platform `/health` endpoint is 100% operational in under **2 seconds**.
