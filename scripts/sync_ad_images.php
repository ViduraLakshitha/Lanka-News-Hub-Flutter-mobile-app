<?php
putenv('DB_DRIVER=sqlite');
putenv('DB_PATH=' . __DIR__ . '/../storage/preview.sqlite');
$_ENV['DB_DRIVER'] = 'sqlite';
$_ENV['DB_PATH'] = __DIR__ . '/../storage/preview.sqlite';

require __DIR__ . '/../app/bootstrap.php';
use App\Core\Database;
use App\Helpers\AdHelper;

$db = Database::getInstance()->getConnection();

$specs = [
    'header_728x90' => [
        'title' => 'Top Leaderboard · 1200 × 100 px',
        'file' => 'uploads/ads/ad_header_1200x100.png',
        'advertiser' => 'SriLankan Airlines · National Carrier',
        'url' => 'https://www.srilankan.com',
        'format' => 'image',
        'priority' => 10
    ],
    'top_left' => [
        'title' => 'Top Left Dual Banner · 590 × 100 px',
        'file' => 'uploads/ads/ad_top_left_590x100.png',
        'advertiser' => 'Pure Ceylon Tea · Board of Sri Lanka',
        'url' => 'https://www.pureceylontea.com',
        'format' => 'image',
        'priority' => 10
    ],
    'top_right' => [
        'title' => 'Top Right Dual Banner · 590 × 100 px',
        'file' => 'uploads/ads/ad_top_right_590x100.png',
        'advertiser' => 'Colombo Port City · Financial District',
        'url' => 'https://www.portcitycolombo.lk',
        'format' => 'image',
        'priority' => 10
    ],
    'homepage_mid_728x90' => [
        'title' => 'Between Sections Divider · 728 × 90 px',
        'file' => 'uploads/ads/ad_between_sections_728x90.png',
        'advertiser' => 'Commercial Bank Flash · Digital Banking',
        'url' => 'https://www.combank.lk',
        'format' => 'image',
        'priority' => 10
    ],
    'inline_feed' => [
        'title' => 'Inside News Feed · 728 × 90 px',
        'file' => 'uploads/ads/ad_inline_feed_728x90.png',
        'advertiser' => 'Dialog 5G Home Broadband · High Speed',
        'url' => 'https://www.dialog.lk',
        'format' => 'image',
        'priority' => 10
    ],
    'in_article' => [
        'title' => 'Inside Article In-Read · 800 × 250 px',
        'file' => 'uploads/ads/ad_in_article_800x250.png',
        'advertiser' => 'Hayleys Solar · Renewable Clean Energy',
        'url' => 'https://www.hayleysfentons.com',
        'format' => 'image',
        'priority' => 10
    ],
    'sidebar_300x250' => [
        'title' => 'Sidebar Medium Rectangle · 300 × 250 px',
        'file' => 'uploads/ads/ad_sidebar_300x250.png',
        'advertiser' => 'Cinnamon Grand Colombo · Luxury Stays',
        'url' => 'https://www.cinnamonhotels.com',
        'format' => 'image',
        'priority' => 10
    ],
    'sidebar_300x600' => [
        'title' => 'Sidebar Skyscraper Tower · 300 × 600 px',
        'file' => 'uploads/ads/ad_tower_300x600.png',
        'advertiser' => 'Bank of Ceylon · Bankers to the Nation',
        'url' => 'https://www.boc.lk',
        'format' => 'image',
        'priority' => 10
    ],
    'footer' => [
        'title' => 'Footer Full-Width Banner · 1200 × 100 px',
        'file' => 'uploads/ads/ad_footer_1200x100.png',
        'advertiser' => 'SLT-MOBITEL · National ICT Solutions',
        'url' => 'https://www.sltmobitel.lk',
        'format' => 'image',
        'priority' => 10
    ],
    'sticky' => [
        'title' => 'Sticky Bottom Floating Banner · 728 × 90 px',
        'file' => 'uploads/ads/ad_sticky_728x90.png',
        'advertiser' => 'Sri Lanka Tourism · Wonder of Asia',
        'url' => 'https://www.srilanka.travel',
        'format' => 'image',
        'priority' => 10
    ],
    'popup' => [
        'title' => 'Pop-Up Interstitial Modal · 600 × 400 px',
        'file' => 'uploads/ads/ad_popup_600x400.png',
        'advertiser' => 'Lanka Premier League 2026 · Official Tickets',
        'url' => 'https://www.srilankacricket.lk',
        'format' => 'image',
        'priority' => 10
    ]
];

echo "===============================================================\n";
echo "UPDATING ALL 11 AD PLACEMENTS WITH UPLOADED BANNER IMAGES\n";
echo "===============================================================\n";

foreach ($specs as $pos => $spec) {
    // Check if an ad for this position exists
    $stmt = $db->prepare('SELECT id FROM advertisements WHERE position = ? LIMIT 1');
    $stmt->execute([$pos]);
    $existing = $stmt->fetch();

    if ($existing) {
        // Update to point to the uploaded banner image
        $update = $db->prepare('UPDATE advertisements SET title = ?, image_path = ?, advertiser = ?, url = ?, ad_format = ?, is_active = 1 WHERE id = ?');
        $update->execute([$spec['title'], $spec['file'], $spec['advertiser'], $spec['url'], $spec['format'], $existing['id']]);
        echo "  [UPDATED] Placement '{$pos}' -> {$spec['file']} (ID: {$existing['id']})\n";
    } else {
        // Insert new ad
        $insert = $db->prepare('INSERT INTO advertisements (title, position, ad_format, advertiser, url, image_path, priority, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, 1)');
        $insert->execute([$spec['title'], $pos, $spec['format'], $spec['advertiser'], $spec['url'], $spec['file'], $spec['priority']]);
        $newId = $db->lastInsertId();
        echo "  [INSERTED] Placement '{$pos}' -> {$spec['file']} (ID: {$newId})\n";
    }
}

AdHelper::clearCache();

echo "===============================================================\n";
echo "VERIFYING ACTIVE PLACEMENTS:\n";
$stmt = $db->query('SELECT id, position, title, ad_format, image_path, is_active FROM advertisements WHERE is_active = 1');
while ($row = $stmt->fetch()) {
    $fullPath = dirname(__DIR__) . '/public/' . ltrim($row['image_path'], '/');
    $exists = file_exists($fullPath) ? 'EXISTS (' . filesize($fullPath) . ' B)' : 'MISSING!';
    echo "  * [{$row['position']}] ID:{$row['id']} | {$row['image_path']} -> {$exists}\n";
}
echo "===============================================================\n";
