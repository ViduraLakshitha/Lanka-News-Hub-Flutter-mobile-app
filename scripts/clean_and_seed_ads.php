<?php
putenv('DB_DRIVER=sqlite');
putenv('DB_PATH=' . __DIR__ . '/../storage/preview.sqlite');
$_ENV['DB_DRIVER'] = 'sqlite';
$_ENV['DB_PATH'] = __DIR__ . '/../storage/preview.sqlite';

require __DIR__ . '/../app/bootstrap.php';
use App\Core\Database;
use App\Helpers\AdHelper;

$db = Database::getInstance()->getConnection();

echo "Deleting old automated test junk from advertisements table...\n";
$db->exec("DELETE FROM advertisements WHERE title LIKE 'High Priority Ad%' OR title LIKE 'Low Priority Ad%' OR title LIKE 'Loop Campaign%' OR title LIKE 'AdSense Responsive Unit%' OR title LIKE 'Commercial Bank Flash Digital Account%' OR title LIKE 'Ceylinco VIP On The Spot%'");

$specs = [
    'header_728x90' => [
        'title' => 'Top Leaderboard · 1200 × 100 px',
        'file' => 'uploads/ads/ad_header_1200x100.png',
        'advertiser' => 'SriLankan Airlines · National Carrier',
        'url' => 'https://www.srilankan.com',
        'format' => 'image',
        'priority' => 100
    ],
    'top_left' => [
        'title' => 'Top Left Dual Banner · 590 × 100 px',
        'file' => 'uploads/ads/ad_top_left_590x100.png',
        'advertiser' => 'Pure Ceylon Tea · Board of Sri Lanka',
        'url' => 'https://www.pureceylontea.com',
        'format' => 'image',
        'priority' => 100
    ],
    'top_right' => [
        'title' => 'Top Right Dual Banner · 590 × 100 px',
        'file' => 'uploads/ads/ad_top_right_590x100.png',
        'advertiser' => 'Colombo Port City · Financial District',
        'url' => 'https://www.portcitycolombo.lk',
        'format' => 'image',
        'priority' => 100
    ],
    'homepage_mid_728x90' => [
        'title' => 'Between Sections Divider · 728 × 90 px',
        'file' => 'uploads/ads/ad_between_sections_728x90.png',
        'advertiser' => 'Commercial Bank Flash · Digital Banking',
        'url' => 'https://www.combank.lk',
        'format' => 'image',
        'priority' => 100
    ],
    'inline_feed' => [
        'title' => 'Inside News Feed · 728 × 90 px',
        'file' => 'uploads/ads/ad_inline_feed_728x90.png',
        'advertiser' => 'Dialog 5G Home Broadband · High Speed',
        'url' => 'https://www.dialog.lk',
        'format' => 'image',
        'priority' => 100
    ],
    'in_article' => [
        'title' => 'Inside Article In-Read · 800 × 250 px',
        'file' => 'uploads/ads/ad_in_article_800x250.png',
        'advertiser' => 'Hayleys Solar · Renewable Clean Energy',
        'url' => 'https://www.hayleysfentons.com',
        'format' => 'image',
        'priority' => 100
    ],
    'sidebar_300x250' => [
        'title' => 'Sidebar Medium Rectangle · 300 × 250 px',
        'file' => 'uploads/ads/ad_sidebar_300x250.png',
        'advertiser' => 'Cinnamon Grand Colombo · Luxury Stays',
        'url' => 'https://www.cinnamonhotels.com',
        'format' => 'image',
        'priority' => 100
    ],
    'sidebar_300x600' => [
        'title' => 'Sidebar Skyscraper Tower · 300 × 600 px',
        'file' => 'uploads/ads/ad_tower_300x600.png',
        'advertiser' => 'Bank of Ceylon · Bankers to the Nation',
        'url' => 'https://www.boc.lk',
        'format' => 'image',
        'priority' => 100
    ],
    'footer' => [
        'title' => 'Footer Full-Width Banner · 1200 × 100 px',
        'file' => 'uploads/ads/ad_footer_1200x100.png',
        'advertiser' => 'SLT-MOBITEL · National ICT Solutions',
        'url' => 'https://www.sltmobitel.lk',
        'format' => 'image',
        'priority' => 100
    ],
    'sticky' => [
        'title' => 'Sticky Bottom Floating Banner · 728 × 90 px',
        'file' => 'uploads/ads/ad_sticky_728x90.png',
        'advertiser' => 'Sri Lanka Tourism · Wonder of Asia',
        'url' => 'https://www.srilanka.travel',
        'format' => 'image',
        'priority' => 100
    ],
    'popup' => [
        'title' => 'Pop-Up Interstitial Modal · 600 × 400 px',
        'file' => 'uploads/ads/ad_popup_600x400.png',
        'advertiser' => 'Lanka Premier League 2026 · Official Tickets',
        'url' => 'https://www.srilankacricket.lk',
        'format' => 'image',
        'priority' => 100
    ]
];

foreach ($specs as $pos => $spec) {
    // Delete any previous ads for this position
    $del = $db->prepare('DELETE FROM advertisements WHERE position = ?');
    $del->execute([$pos]);

    // Insert fresh record
    $ins = $db->prepare('INSERT INTO advertisements (title, position, ad_format, advertiser, url, image_path, priority, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, 1)');
    $ins->execute([
        $spec['title'],
        $pos,
        $spec['format'],
        $spec['advertiser'],
        $spec['url'],
        $spec['file'],
        $spec['priority']
    ]);
    echo "  ✓ [{$pos}] Inserted {$spec['file']} ({$spec['title']})\n";
}

AdHelper::clearCache();

echo "\nSummary of active advertisements:\n";
$rows = $db->query('SELECT id, position, title, image_path, is_active FROM advertisements WHERE is_active=1 ORDER BY id')->fetchAll();
foreach ($rows as $r) {
    $file = dirname(__DIR__) . '/public/' . $r['image_path'];
    $size = file_exists($file) ? filesize($file) . ' bytes' : 'NOT FOUND';
    echo sprintf("  #%02d [%-20s] -> %-36s (%s)\n", $r['id'], $r['position'], $r['image_path'], $size);
}
