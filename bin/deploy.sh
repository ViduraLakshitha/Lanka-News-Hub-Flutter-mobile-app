#!/usr/bin/env bash
# ==============================================================================
# Lanka News Hub — Production Zero-Downtime Deployment Automation
# Architecture: Atomic Release Symlinking with Automated Rollback
# ==============================================================================
set -euo pipefail

BASE_DIR="/var/www/news_page"
RELEASES_DIR="${BASE_DIR}/releases"
SHARED_DIR="${BASE_DIR}/shared"
CURRENT_LINK="${BASE_DIR}/current"
TIMESTAMP=$(date -u +'%Y%m%d%H%M%S')
NEW_RELEASE="${RELEASES_DIR}/${TIMESTAMP}"
PREVIOUS_RELEASE=""
PHP_BIN=$(command -v php8.2 || command -v php)
PHP_FPM_SERVICE="php8.2-fpm"
HEALTH_CHECK_URL="http://127.0.0.1/health"
APP_URL="http://127.0.0.1/"
KEEP_RELEASES=5

echo "========================================================================"
echo "Lanka News Hub: Starting Production Deployment"
echo "Timestamp:   ${TIMESTAMP}"
echo "Release Dir: ${NEW_RELEASE}"
echo "PHP Binary:  $(${PHP_BIN} -v | head -n 1)"
echo "========================================================================"

# Determine previous release for rollback reference
if [ -L "${CURRENT_LINK}" ]; then
    PREVIOUS_RELEASE=$(readlink -f "${CURRENT_LINK}")
    echo "Current Active Release: ${PREVIOUS_RELEASE}"
fi

# Function: Trigger Immediate Rollback
rollback() {
    echo "!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!"
    echo "ERROR: Deployment failed! Initiating immediate automated rollback..."
    echo "!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!"
    if [ -n "${PREVIOUS_RELEASE}" ] && [ -d "${PREVIOUS_RELEASE}" ]; then
        echo "Repointing ${CURRENT_LINK} back to ${PREVIOUS_RELEASE}..."
        ln -sfn "${PREVIOUS_RELEASE}" "${BASE_DIR}/current_tmp"
        mv -Tf "${BASE_DIR}/current_tmp" "${CURRENT_LINK}"
        
        echo "Reloading PHP-FPM service..."
        sudo systemctl reload "${PHP_FPM_SERVICE}" || true
        
        echo "Rollback completed. Restored release: ${PREVIOUS_RELEASE}"
    else
        echo "CRITICAL: No previous release found to roll back to!"
    fi
    exit 1
}

# ------------------------------------------------------------------------------
# STEP 1: Pre-Deployment Backup
# ------------------------------------------------------------------------------
echo -e "\n[1/10] Executing Pre-Deployment State Backup..."
if [ -L "${CURRENT_LINK}" ] && [ -f "${CURRENT_LINK}/bin/backup.php" ]; then
    ${PHP_BIN} "${CURRENT_LINK}/bin/backup.php" || {
        echo "WARNING: Pre-deployment backup encountered an issue. Proceeding with caution."
    }
else
    echo "Initial deployment detected or no backup script found; skipping pre-backup."
fi

# ------------------------------------------------------------------------------
# STEP 2: Code Release Preparation
# ------------------------------------------------------------------------------
echo -e "\n[2/10] Creating Release Directory..."
mkdir -p "${NEW_RELEASE}"

# If deploying from local repository clone or CI artifact:
if [ -d ".git" ]; then
    echo "Exporting git HEAD to release folder..."
    git archive HEAD | tar -x -C "${NEW_RELEASE}"
elif [ -f "release.tar.gz" ]; then
    echo "Unpacking release artifact..."
    tar -xzf release.tar.gz -C "${NEW_RELEASE}"
else
    echo "Copying application codebase..."
    cp -a . "${NEW_RELEASE}/"
fi

# ------------------------------------------------------------------------------
# STEP 3: Shared Storage Symlinks
# ------------------------------------------------------------------------------
echo -e "\n[3/10] Linking Shared Configuration and Storage Directories..."
mkdir -p "${SHARED_DIR}/storage/logs"
mkdir -p "${SHARED_DIR}/storage/cache"
mkdir -p "${SHARED_DIR}/storage/sessions"
mkdir -p "${SHARED_DIR}/storage/backups"
mkdir -p "${SHARED_DIR}/public/uploads"

# Verify .env exists in shared
if [ ! -f "${SHARED_DIR}/.env" ]; then
    echo "ERROR: Shared environment file ${SHARED_DIR}/.env not found!"
    exit 1
fi

# Symlink .env
rm -f "${NEW_RELEASE}/.env"
ln -sf "${SHARED_DIR}/.env" "${NEW_RELEASE}/.env"

# Symlink storage
rm -rf "${NEW_RELEASE}/storage"
ln -sf "${SHARED_DIR}/storage" "${NEW_RELEASE}/storage"

# Symlink uploads
rm -rf "${NEW_RELEASE}/public/uploads"
ln -sf "${SHARED_DIR}/public/uploads" "${NEW_RELEASE}/public/uploads"

# ------------------------------------------------------------------------------
# STEP 4: Dependency Installation
# ------------------------------------------------------------------------------
echo -e "\n[4/10] Installing Composer Production Dependencies..."
if [ -f "${NEW_RELEASE}/composer.json" ] && command -v composer &> /dev/null; then
    (cd "${NEW_RELEASE}" && composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction --no-progress)
else
    echo "Zero external Composer runtime dependencies required (Clean core architecture)."
fi

# ------------------------------------------------------------------------------
# STEP 5: Database Migration
# ------------------------------------------------------------------------------
echo -e "\n[5/10] Running Database Migrations..."
if [ -f "${NEW_RELEASE}/database/migrate.php" ]; then
    (cd "${NEW_RELEASE}" && ${PHP_BIN} database/migrate.php) || {
        echo "ERROR: Database migration failed!"
        rollback
    }
fi

# ------------------------------------------------------------------------------
# STEP 6: Cache Refresh
# ------------------------------------------------------------------------------
echo -e "\n[6/10] Flushing Application and Query Caches..."
rm -rf "${SHARED_DIR}/storage/cache"/*
mkdir -p "${SHARED_DIR}/storage/cache"

# ------------------------------------------------------------------------------
# STEP 7: File Permissions Hardening
# ------------------------------------------------------------------------------
echo -e "\n[7/10] Applying Least-Privilege Permissions..."
chown -R www-data:www-data "${NEW_RELEASE}"
chown -R www-data:www-data "${SHARED_DIR}"
find "${NEW_RELEASE}" -type d -exec chmod 755 {} +
find "${NEW_RELEASE}" -type f -exec chmod 644 {} +
chmod +x "${NEW_RELEASE}/bin"/* || true
chmod 600 "${SHARED_DIR}/.env"
chmod -R 775 "${SHARED_DIR}/storage"
chmod -R 775 "${SHARED_DIR}/public/uploads"

# ------------------------------------------------------------------------------
# STEP 8: Atomic Symlink Switch
# ------------------------------------------------------------------------------
echo -e "\n[8/10] Atomically Switching Current Symlink to New Release..."
ln -sfn "${NEW_RELEASE}" "${BASE_DIR}/current_tmp"
mv -Tf "${BASE_DIR}/current_tmp" "${CURRENT_LINK}"

# ------------------------------------------------------------------------------
# STEP 9: Service Reload & OPcache Reset
# ------------------------------------------------------------------------------
echo -e "\n[9/10] Reloading PHP-FPM and Nginx Services..."
sudo systemctl reload "${PHP_FPM_SERVICE}" || echo "Warning: Unable to reload ${PHP_FPM_SERVICE}"
sudo systemctl reload nginx || echo "Warning: Unable to reload nginx"

# ------------------------------------------------------------------------------
# STEP 10: Automated Smoke Testing
# ------------------------------------------------------------------------------
echo -e "\n[10/10] Executing Automated Smoke Tests..."
sleep 1

# Check 1: Health endpoint
echo "Checking Health Probe: ${HEALTH_CHECK_URL}..."
HEALTH_RESPONSE=$(curl -s -o /dev/null -w "%{http_code}" -m 10 "${HEALTH_CHECK_URL}" || echo "000")
if [ "${HEALTH_RESPONSE}" != "200" ]; then
    echo "FAILED: Health check returned HTTP ${HEALTH_RESPONSE}"
    rollback
fi
echo "✓ Health check passed (HTTP 200)."

# Check 2: Homepage response
echo "Checking Homepage Response: ${APP_URL}..."
HOME_RESPONSE=$(curl -s -o /dev/null -w "%{http_code}" -m 10 "${APP_URL}" || echo "000")
if [ "${HOME_RESPONSE}" != "200" ]; then
    echo "FAILED: Homepage returned HTTP ${HOME_RESPONSE}"
    rollback
fi
echo "✓ Homepage passed (HTTP 200)."

# ------------------------------------------------------------------------------
# Prune Old Releases
# ------------------------------------------------------------------------------
echo -e "\nPruning old releases (keeping last ${KEEP_RELEASES})..."
(cd "${RELEASES_DIR}" && ls -dt */ | tail -n +$((KEEP_RELEASES + 1)) | xargs -r rm -rf)

echo -e "\n========================================================================"
echo "DEPLOYMENT SUCCESSFUL! Active release: ${NEW_RELEASE}"
echo "========================================================================"
exit 0
