param([int]$Port = 8080)
$ErrorActionPreference = 'Stop'
$projectPath = (Resolve-Path (Join-Path $PSScriptRoot '..')).Path
$phpCommand = Get-Command php -ErrorAction SilentlyContinue
$phpPath = if ($phpCommand) { $phpCommand.Source } elseif (Test-Path 'C:/xampp/php/php.exe') { 'C:/xampp/php/php.exe' } else { throw 'PHP 8.1+ is required.' }
$previewDatabase = Join-Path $projectPath 'storage/preview.sqlite'
$newPreview = !(Test-Path -LiteralPath $previewDatabase)
if ($newPreview -and (!$env:NEWS_DEMO_PASSWORD -or $env:NEWS_DEMO_PASSWORD.Length -lt 12)) { throw 'Set NEWS_DEMO_PASSWORD to a unique local password of at least 12 characters before starting the first preview.' }
$env:DB_DRIVER = 'sqlite'
$env:DB_PATH = $previewDatabase
$env:APP_ENV = 'demo'
$env:APP_URL = "http://127.0.0.1:$Port"
$env:APP_DEBUG = 'true'
$uploadTemp = Join-Path $projectPath 'storage/upload-temp'
New-Item -ItemType Directory -Force -Path $uploadTemp | Out-Null
Push-Location $projectPath
try {
    & $phpPath database/migrate.php
    if ($LASTEXITCODE -ne 0) { throw 'Migration failed.' }
    if ($newPreview) {
        & $phpPath bin/demo.php
        if ($LASTEXITCODE -ne 0) { throw 'Demo setup failed.' }
        & $phpPath bin/demo-banners.php
        & $phpPath bin/demo-media.php
        & $phpPath bin/demo-classifieds.php
    }
    Write-Host "Local demo: $env:APP_URL (Ctrl+C stops it)."
    Write-Host 'Demo sign-in: preview@example.test, using the password supplied when this demo was created.'
    & $phpPath -d "upload_tmp_dir=$uploadTemp" -d upload_max_filesize=25M -d post_max_size=210M -d memory_limit=256M -S "127.0.0.1:$Port" -t public public/router.php
} finally { Pop-Location }
