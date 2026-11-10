<?php
$broadsheet = file_get_contents(__DIR__ . '/../public/assets/css/portal-broadsheet.css');

// 1. Copy to adaderana.css
file_put_contents(__DIR__ . '/../public/assets/css/adaderana.css', $broadsheet);

// 2. Refresh in portal.css
$portalCss = file_get_contents(__DIR__ . '/../public/assets/css/portal.css');
$portalBase = explode("/* === BROADSHEET PORTAL STYLES === */", $portalCss)[0];
file_put_contents(
    __DIR__ . '/../public/assets/css/portal.css',
    rtrim($portalBase) . "\n\n/* === BROADSHEET PORTAL STYLES === */\n" . $broadsheet
);

// 3. Refresh in editorial.css
$editorialCss = file_get_contents(__DIR__ . '/../public/assets/css/editorial.css');
$editorialBase = explode("/* === BROADSHEET PORTAL STYLES === */", $editorialCss)[0];
file_put_contents(
    __DIR__ . '/../public/assets/css/editorial.css',
    rtrim($editorialBase) . "\n\n/* === BROADSHEET PORTAL STYLES === */\n" . $broadsheet
);

echo "Fully synced broadsheet styles to portal.css, editorial.css, and adaderana.css!\n";
