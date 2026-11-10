@echo off
title Lanka News Hub - Local PHP API Server
echo ===================================================
echo   Lanka News Hub - Local PHP API Server
echo   Running on: http://0.0.0.0:8080
echo   Wi-Fi IP:   http://172.20.10.5:8080/api/v1
echo ===================================================
echo.
echo Press Ctrl+C to stop the server at any time.
echo.
php -S 0.0.0.0:8080 -t public router.php
pause

