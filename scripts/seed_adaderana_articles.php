<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/Core/Database.php';

$db = \App\Core\Database::getInstance()->getConnection();

$authorId = 1;
$now = date('Y-m-d H:i:s');

// Helper to create or get media id
function getOrCreateMedia($pdo, $filename, $title) {
    $path = "assets/images/adaderana/" . $filename;
    $stmt = $pdo->prepare("SELECT id FROM media WHERE file_path = ?");
    $stmt->execute([$path]);
    $id = $stmt->fetchColumn();
    if ($id) return (int)$id;

    $ins = $pdo->prepare("INSERT INTO media (uploader_id, file_name, file_path, file_type, alt_text, caption, created_at) VALUES (1, ?, ?, 'image/jpeg', ?, '', ?)");
    $ins->execute([$filename, $path, $title, date('Y-m-d H:i:s')]);
    return (int)$pdo->lastInsertId();
}

$stories = [
    [
        'slug' => 'sri-lanka-economy-reaches-pre-crisis-levels-world-bank',
        'cat_id' => 3, // Business
        'is_featured' => 1,
        'is_breaking' => 1,
        'image' => 'lead_world_bank.jpg',
        'title_en' => 'Sri Lanka’s economy reaches pre-crisis levels but recovery uneven - World Bank',
        'title_si' => 'ශ්‍රී ලංකා ආර්ථිකය පෙර අර්බුද මට්ටමට ළඟා වුවද ප්‍රකෘතිමත් වීම අසමාන බව ලෝක බැංකුව පවසයි',
        'title_ta' => 'இலங்கை பொருளாதாரம் நெருக்கடிக்கு முந்தைய நிலையை எட்டியுள்ள போதிலும் மீட்சி சீரற்றது - உலக வங்கி',
        'summary_en' => 'The World Bank reports that while Sri Lanka’s economic output has rebounded towards pre-crisis levels, severe challenges remain in poverty reduction and fiscal sustainability.',
        'content_en' => '<p>The World Bank’s latest development update highlights that Sri Lanka’s economy has reached pre-crisis output levels, driven by monetary stabilization, tourism recovery, and renewed remittance inflows.</p><p>However, the report underscores that economic vulnerability remains elevated, with household incomes still depressed and poverty rates elevated in rural and estate sectors. Sustained structural reforms remain critical for lasting prosperity.</p>'
    ],
    [
        'slug' => 'srilankan-airlines-flight-chennai-returns-colombo',
        'cat_id' => 1, // Local
        'is_featured' => 0,
        'is_breaking' => 0,
        'image' => 'plane_chennai.jpg',
        'title_en' => 'SriLankan Airlines flight to Chennai returns to Colombo due to technical issue',
        'title_si' => 'තාක්ෂණික දෝෂයක් හේතුවෙන් චෙන්නායි බලා ගිය ශ්‍රීලංකන් ගුවන් යානය යළි කටුනායකට',
        'title_ta' => 'தொழில்நுட்ப கோளாறு காரணமாக சென்னை நோக்கி சென்ற ஸ்ரீலங்கன் விமானம் கொழும்புக்கு திரும்பியது',
        'summary_en' => 'A Chennai-bound flight safely returned to Bandaranaike International Airport following an indication of a minor technical issue shortly after departure.',
        'content_en' => '<p>SriLankan Airlines flight UL 121 en route from Colombo to Chennai returned to Katunayake as a precautionary safety measure following an operational warning in the cockpit.</p><p>All passengers were disembarked safely and accommodated on an alternative flight, airline officials confirmed.</p>'
    ],
    [
        'slug' => 'president-probe-matara-court-complex',
        'cat_id' => 2, // Politics
        'is_featured' => 0,
        'is_breaking' => 0,
        'image' => 'matara_court.jpg',
        'title_en' => 'President instructs to probe delay in completing Matara court complex',
        'title_si' => 'මාතර අධිකරණ සංකීර්ණයේ වැඩ අවසන්වීම ප්‍රමාද වීම ගැන සොයා බැලීමට ජනපතිගෙන් උපදෙස්',
        'title_ta' => 'மாத்தறை நீதிமன்ற வளாகப் பணிகள் நிறைவடைவதில் ஏற்பட்டுள்ள தாமதம் குறித்து விசாரிக்க ஜனாதிபதி உத்தரவு',
        'summary_en' => 'President orders a comprehensive review into construction bottlenecks and contractor delays regarding the modern Matara combined law courts facility.',
        'content_en' => '<p>President instructed the Ministry of Justice and Urban Development authorities to submit an immediate report detailing why construction on the Matara court complex remains incomplete despite repeated budgetary allocations.</p>'
    ],
    [
        'slug' => 'government-prioritizes-socio-economic-welfare-pm',
        'cat_id' => 2,
        'is_featured' => 0,
        'is_breaking' => 0,
        'image' => 'pm_welfare.jpg',
        'title_en' => 'Government prioritizes socio-economic welfare - Prime Minister',
        'title_si' => 'රජයේ ප්‍රමුඛතාවය සමාජ හා ආර්ථික සුභසාධනයට - අගමැති',
        'title_ta' => 'அரசாங்கம் சமூக பொருளாதார நலனுக்கே முன்னுரிமை அளிக்கிறது - பிரதமர்',
        'summary_en' => 'The Prime Minister emphasized that governmental social safety initiatives are expanding to protect vulnerable families across the country.',
        'content_en' => '<p>Addressing an economic symposium in Colombo, the Prime Minister reaffirmed that safeguarding household nutrition and rural welfare stands at the core of national policies.</p>'
    ],
    [
        'slug' => 'interim-order-slpp-general-secretary',
        'cat_id' => 2,
        'is_featured' => 0,
        'is_breaking' => 0,
        'image' => 'slpp_politician.jpg',
        'title_en' => 'Interim order on SLPP General Secretary’s appointment extended',
        'title_si' => 'පොහොට්ටුවේ ප්‍රධාන ලේකම් පත්වීම සම්බන්ධ අතුරු තහනම් නියෝගය දීර්ඝ කෙරේ',
        'title_ta' => 'பொதுஜன பெரமுனவின் பொதுச்செயலாளர் நியமனம் தொடர்பான இடைக்கால தடை உத்தரவு நீடிப்பு',
        'summary_en' => 'Colombo District Court extends the interim injunction preventing party changes until the conclusion of pending hearings.',
        'content_en' => '<p>The Colombo District Court has extended the interim injunction concerning the secretarial leadership of the Sri Lanka Podujana Peramuna following oral submissions from counsel.</p>'
    ],
    [
        'slug' => 'catholic-bishops-easter-inquiry',
        'cat_id' => 1,
        'is_featured' => 0,
        'is_breaking' => 0,
        'image' => 'catholic_bishops.jpg',
        'title_en' => 'Catholic Bishops’ Conference calls for independent inquiry into Easter Sunday attacks',
        'title_si' => 'පාස්කු ප්‍රහාරය සම්බන්ධයෙන් ස්වාධීන පරීක්ෂණයක් කතෝලික රදගුරු සම්මේලනය ඉල්ලයි',
        'title_ta' => 'உயிர்த்த ஞாயிறு தாக்குதல்கள் குறித்து சுயாதீன விசாரணை கோருகிறது கத்தோலிக்க ஆயர் பேரவை',
        'summary_en' => 'The Catholic Bishops’ Conference has called on authorities to initiate a transparent international probe into all findings surrounding the 2019 terror attacks.',
        'content_en' => '<p>A media briefing held at the Archbishop’s House in Colombo renewed appeals for accountability and thorough justice for victims of the 2019 Easter Sunday bomb blasts.</p>'
    ]
];

foreach ($stories as $s) {
    $mediaId = getOrCreateMedia($db, $s['image'], $s['title_en']);
    $chk = $db->prepare("SELECT id FROM articles WHERE slug = ?");
    $chk->execute([$s['slug']]);
    $existingId = $chk->fetchColumn();

    if ($existingId) {
        $upd = $db->prepare("UPDATE articles SET title_en = ?, title_si = ?, title_ta = ?, summary_en = ?, summary_si = ?, summary_ta = ?, content_en = ?, featured_image_id = ?, category_id = ?, is_featured = ?, is_breaking = ?, status = 'published', updated_at = ? WHERE id = ?");
        $upd->execute([
            $s['title_en'], $s['title_si'], $s['title_ta'],
            $s['summary_en'], $s['title_si'], $s['title_ta'],
            $s['content_en'], $mediaId, $s['cat_id'],
            $s['is_featured'], $s['is_breaking'],
            $now, $existingId
        ]);
    } else {
        $ins = $db->prepare("INSERT INTO articles (author_id, category_id, featured_image_id, title_en, title_si, title_ta, slug, summary_en, summary_si, summary_ta, content_en, content_si, content_ta, status, is_breaking, is_featured, published_at, created_at, updated_at) VALUES (1, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'published', ?, ?, ?, ?, ?)");
        $ins->execute([
            $s['cat_id'], $mediaId,
            $s['title_en'], $s['title_si'], $s['title_ta'],
            $s['slug'],
            $s['summary_en'], $s['title_si'], $s['title_ta'],
            $s['content_en'], $s['content_en'], $s['content_en'],
            $s['is_breaking'], $s['is_featured'],
            $now, $now, $now
        ]);
    }
}

// Clear homepage cache
if (is_dir(__DIR__ . '/../storage/cache')) {
    foreach (glob(__DIR__ . '/../storage/cache/*') as $f) {
        @unlink($f);
    }
}

echo "Database seeded with Ada Derana stories.\n";
