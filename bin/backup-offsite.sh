#!/usr/bin/env bash
# ==============================================================================
# Lanka News Hub — Off-Site Encrypted Backup & Cloud Synchronization
# S3 / Cloudflare R2 / Backblaze B2 compliant with AES-256 GPG Encryption
# ==============================================================================
set -euo pipefail

BASE_DIR="/var/www/news_page"
SHARED_DIR="${BASE_DIR}/shared"
BACKUP_DIR="${SHARED_DIR}/storage/backups"
GPG_PASSPHRASE="${BACKUP_ENCRYPTION_KEY:-}"
S3_BUCKET="${BACKUP_S3_BUCKET:-s3://news-hub-production-backups}"
RETENTION_DAYS_LOCAL=7

echo "========================================================================"
echo "Lanka News Hub: Off-Site Backup & Encryption Dispatcher"
echo "Timestamp: $(date -u +'%Y-%m-%d %H:%M:%S UTC')"
echo "========================================================================"

if [ ! -d "${BACKUP_DIR}" ]; then
    echo "ERROR: Backup directory not found at ${BACKUP_DIR}"
    exit 1
fi

# 1. Locate the latest generated backup zip
LATEST_BACKUP=$(ls -t "${BACKUP_DIR}"/news-*.zip 2>/dev/null | head -n 1 || true)
if [ -z "${LATEST_BACKUP}" ]; then
    echo "ERROR: No backup zip archive found in ${BACKUP_DIR}!"
    exit 1
fi

echo "Latest local backup: ${LATEST_BACKUP} ($(du -h "${LATEST_BACKUP}" | cut -f1))"

# 2. Package critical infrastructure configuration
INFRA_ARCHIVE="${BACKUP_DIR}/infra-config-$(date -u +'%Y%m%d%H%M%S').tar.gz"
echo "Archiving server configurations to ${INFRA_ARCHIVE}..."
tar -czf "${INFRA_ARCHIVE}" \
    -C "${SHARED_DIR}" .env \
    -C "${BASE_DIR}/current" deploy/ \
    2>/dev/null || true

# 3. GPG Symmetric AES-256 Encryption (if passphrase provided)
TARGET_MEDIA="${LATEST_BACKUP}"
TARGET_INFRA="${INFRA_ARCHIVE}"

if [ -n "${GPG_PASSPHRASE}" ]; then
    echo "Encrypting backup archive with GPG AES-256..."
    gpg --batch --yes --symmetric --cipher-algo AES256 --passphrase "${GPG_PASSPHRASE}" "${LATEST_BACKUP}"
    gpg --batch --yes --symmetric --cipher-algo AES256 --passphrase "${GPG_PASSPHRASE}" "${INFRA_ARCHIVE}"
    TARGET_MEDIA="${LATEST_BACKUP}.gpg"
    TARGET_INFRA="${INFRA_ARCHIVE}.gpg"
    rm -f "${INFRA_ARCHIVE}"
    echo "✓ Encryption complete."
else
    echo "WARNING: BACKUP_ENCRYPTION_KEY not set. Transferring unencrypted over TLS."
fi

# 4. Off-Site Cloud Upload (AWS CLI or Rclone)
if command -v aws &>/dev/null; then
    echo "Uploading to S3 / Cloudflare R2: ${S3_BUCKET}..."
    aws s3 cp "${TARGET_MEDIA}" "${S3_BUCKET}/media-db/" --storage-class STANDARD_IA
    aws s3 cp "${TARGET_INFRA}" "${S3_BUCKET}/infra/" --storage-class STANDARD_IA
    echo "✓ Upload complete."
elif command -v rclone &>/dev/null; then
    echo "Uploading via rclone to remote:news-backups..."
    rclone copy "${TARGET_MEDIA}" "remote:news-backups/media-db/"
    rclone copy "${TARGET_INFRA}" "remote:news-backups/infra/"
    echo "✓ Upload complete."
else
    echo "NOTICE: Neither aws-cli nor rclone detected. Backup stored locally at ${TARGET_MEDIA}."
fi

# 5. Local Retention Pruning (Remove local backups older than 7 days)
echo "Pruning local backup archives older than ${RETENTION_DAYS_LOCAL} days..."
find "${BACKUP_DIR}" -name "news-*.zip*" -type f -mtime "+${RETENTION_DAYS_LOCAL}" -delete
find "${BACKUP_DIR}" -name "infra-config-*.tar.gz*" -type f -mtime "+${RETENTION_DAYS_LOCAL}" -delete
echo "✓ Local retention clean."

echo "========================================================================"
echo "OFF-SITE BACKUP PIPELINE FINISHED SUCCESSFULLY"
echo "========================================================================"
exit 0
