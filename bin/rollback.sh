#!/usr/bin/env bash
# ==============================================================================
# Lanka News Hub — Production Instantaneous Rollback Utility
# Usage: ./bin/rollback.sh [optional_target_release_path]
# ==============================================================================
set -euo pipefail

BASE_DIR="/var/www/news_page"
RELEASES_DIR="${BASE_DIR}/releases"
SHARED_DIR="${BASE_DIR}/shared"
CURRENT_LINK="${BASE_DIR}/current"
PHP_FPM_SERVICE="php8.2-fpm"
HEALTH_CHECK_URL="http://127.0.0.1/health"

echo "========================================================================"
echo "Lanka News Hub: Initiating Production Rollback"
echo "========================================================================"

if [ ! -L "${CURRENT_LINK}" ]; then
    echo "ERROR: Current release symlink does not exist at ${CURRENT_LINK}!"
    exit 1
fi

ACTIVE_RELEASE=$(readlink -f "${CURRENT_LINK}")
echo "Currently Active Release: ${ACTIVE_RELEASE}"

# Determine target release
TARGET_RELEASE="${1:-}"

if [ -z "${TARGET_RELEASE}" ]; then
    # Automatically locate previous release
    echo "Finding previous valid release directory..."
    TARGET_RELEASE=$(ls -dt "${RELEASES_DIR}"/*/ | grep -v "${ACTIVE_RELEASE}/" | head -n 1 | sed 's/\/$//' || true)
fi

if [ -z "${TARGET_RELEASE}" ] || [ ! -d "${TARGET_RELEASE}" ]; then
    echo "ERROR: No valid rollback target release found in ${RELEASES_DIR}!"
    exit 1
fi

echo "Rolling back to target release: ${TARGET_RELEASE}"

# Atomically switch symlink
echo "Updating active symlink..."
ln -sfn "${TARGET_RELEASE}" "${BASE_DIR}/current_tmp"
mv -Tf "${BASE_DIR}/current_tmp" "${CURRENT_LINK}"

# Flush cache
echo "Clearing application cache..."
rm -rf "${SHARED_DIR}/storage/cache"/*

# Reload web services
echo "Reloading PHP-FPM service..."
sudo systemctl reload "${PHP_FPM_SERVICE}" || true
sudo systemctl reload nginx || true

# Verify health status
echo "Verifying service health after rollback..."
HEALTH_STATUS=$(curl -s -o /dev/null -w "%{http_code}" -m 10 "${HEALTH_CHECK_URL}" || echo "000")

if [ "${HEALTH_STATUS}" == "200" ]; then
    echo "========================================================================"
    echo "ROLLBACK SUCCESSFUL: Restored to ${TARGET_RELEASE}"
    echo "Platform is healthy (HTTP 200)."
    echo "========================================================================"
    exit 0
else
    echo "WARNING: Rollback completed, but health check returned HTTP ${HEALTH_STATUS}!"
    exit 1
fi
