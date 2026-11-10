<?php
if (PHP_SAPI !== 'cli' && empty($inWebApp)) exit;

if (!getenv('DB_DRIVER') && file_exists(__DIR__ . '/../storage/preview.sqlite')) {
    putenv('DB_DRIVER=sqlite');
    putenv('DB_PATH=' . __DIR__ . '/../storage/preview.sqlite');
    $_ENV['DB_DRIVER'] = 'sqlite';
    $_ENV['DB_PATH'] = __DIR__ . '/../storage/preview.sqlite';
}

require_once dirname(__DIR__) . '/app/bootstrap.php';
use App\Core\Database;

$db = Database::getInstance()->getConnection();

$authorId = $db->query("SELECT id FROM users WHERE role_id IN (1, 2) ORDER BY id ASC LIMIT 1")->fetchColumn();
if (!$authorId) {
    $authorId = 1;
}

$sampleMedia = [
    [
        'file_name' => 'Colombo Port City & Financial Skyline',
        'file_path' => 'uploads/media/news_colombo_skyline.png',
        'file_type' => 'image/png',
        'alt_text' => 'Aerial panorama of Colombo Port City and coastal financial district skyline',
        'caption' => 'The Colombo financial district skyline captured at dusk, showcasing newly commissioned offshore commercial towers and marina infrastructure.'
    ],
    [
        'file_name' => 'Nuwara Eliya High-Grown Tea Harvest',
        'file_path' => 'uploads/media/news_tea_harvest.png',
        'file_type' => 'image/png',
        'alt_text' => 'Tea plantation workers harvesting orthodox Ceylon tea leaves in Nuwara Eliya',
        'caption' => 'High-grown Ceylon tea estates in Nuwara Eliya reporting strong export demand across international markets.'
    ],
    [
        'file_name' => 'National Cricket T20 Championship Finals',
        'file_path' => 'uploads/media/news_cricket_action.png',
        'file_type' => 'image/png',
        'alt_text' => 'Sri Lanka national cricket team celebrating during T20 finals',
        'caption' => 'Spectators cheer during the thrilling championship finals held under floodlights at R. Premadasa International Stadium.'
    ],
    [
        'file_name' => 'Colombo AI & Digital Economy Forum 2026',
        'file_path' => 'uploads/media/news_tech_summit.png',
        'file_type' => 'image/png',
        'alt_text' => 'Keynote presentation at Colombo Artificial Intelligence and Cloud Tech Summit',
        'caption' => 'Industry leaders discuss generative AI infrastructure, regional talent development, and export growth at the 2026 Tech Forum.'
    ],
    [
        'file_name' => 'Historic Kandy Esala Perahera Pageant',
        'file_path' => 'uploads/media/news_kandy_culture.png',
        'file_type' => 'image/png',
        'alt_text' => 'Traditional Kandyan dancers and ceremonial procession at Esala Perahera',
        'caption' => 'The sacred hill country cultural procession draws hundreds of thousands of local and international visitors.'
    ],
    [
        'file_name' => 'Mannar Island 100MW Wind Farm Project',
        'file_path' => 'uploads/media/news_green_energy.png',
        'file_type' => 'image/png',
        'alt_text' => 'Wind turbines and utility-scale solar panels operating on Mannar Island',
        'caption' => 'Mannar Island wind farm expands national grid renewable capacity as Sri Lanka accelerates clean energy targets.'
    ],
    [
        'file_name' => 'Central Bank Annual Fiscal & Monetary Report',
        'file_path' => 'uploads/media/news_central_bank.png',
        'file_type' => 'image/png',
        'alt_text' => 'Central Bank of Sri Lanka monetary policy committee press briefing',
        'caption' => 'Governor of the Central Bank outlines foreign reserve growth, headline inflation, and annual GDP trajectory during the monetary review.'
    ],
    [
        'file_name' => 'Yala National Park Leopard & Elephant Conservation',
        'file_path' => 'uploads/media/news_wildlife_yala.png',
        'file_type' => 'image/png',
        'alt_text' => 'Sri Lankan leopard resting on rock outcrop in Yala National Park',
        'caption' => 'Wildlife conservationists and park rangers record healthy leopard population densities in Block 1 of Yala National Park.'
    ],
    [
        'file_name' => 'Fiscal & Economic Reform Whitepaper 2026',
        'file_path' => 'uploads/media/whitepaper_economic_outlook_2026.pdf',
        'file_type' => 'application/pdf',
        'alt_text' => 'Official Whitepaper on National Fiscal and Economic Policy 2026',
        'caption' => 'Complete analytical report and macroeconomic data tables published by the Parliamentary Research Committee.'
    ]
];

$log = function(string $msg) {
    if (PHP_SAPI === 'cli') echo $msg . "\n";
};

$log("===============================================================");
$log("SEEDING EDITORIAL MEDIA LIBRARY ASSETS");
$log("===============================================================");

$inserted = 0;
$now = date('Y-m-d H:i:s');

foreach ($sampleMedia as $item) {
    $existing = $db->prepare("SELECT id FROM media WHERE file_path = ?");
    $existing->execute([$item['file_path']]);
    $foundId = $existing->fetchColumn();

    if ($foundId) {
        $stmt = $db->prepare("UPDATE media SET file_name=?, file_type=?, alt_text=?, caption=? WHERE id=?");
        $stmt->execute([
            $item['file_name'],
            $item['file_type'],
            $item['alt_text'],
            $item['caption'],
            $foundId
        ]);
        $log("  [UPDATED] #{$foundId} · {$item['file_name']}");
    } else {
        $stmt = $db->prepare("INSERT INTO media (uploader_id, file_name, file_path, file_type, alt_text, caption, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $authorId,
            $item['file_name'],
            $item['file_path'],
            $item['file_type'],
            $item['alt_text'],
            $item['caption'],
            $now
        ]);
        $newId = $db->lastInsertId();
        $inserted++;
        $log("  [INSERTED] #{$newId} · {$item['file_name']}");
    }
}

$total = $db->query("SELECT COUNT(*) FROM media")->fetchColumn();
$log("SUMMARY: Total media assets in library: {$total}");
$log("===============================================================");
