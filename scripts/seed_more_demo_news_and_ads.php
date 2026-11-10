<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Helpers/AdHelper.php';

$db = \App\Core\Database::getInstance()->getConnection();
$now = date('Y-m-d H:i:s');

echo "=== 1. SEEDING MULTI-AD LOOPS FOR PLACEMENTS ===\n";

// Helper to get or create media record
function getMediaId($db, $path, $title) {
    $stmt = $db->prepare("SELECT id FROM media WHERE file_path = ?");
    $stmt->execute([$path]);
    $id = $stmt->fetchColumn();
    if ($id) return (int)$id;

    $ins = $db->prepare("INSERT INTO media (uploader_id, file_name, file_path, file_type, alt_text, caption, created_at) VALUES (1, ?, ?, 'image/jpeg', ?, '', ?)");
    $ins->execute([basename($path), $path, $title, date('Y-m-d H:i:s')]);
    return (int)$db->lastInsertId();
}

$multiAds = [
    // --- header_728x90 (Top Leaderboard: 3-ad loop) ---
    [
        'title' => 'National Lotteries Board · Super Mega Jackpot Rs. 150 Million',
        'position' => 'header_728x90',
        'ad_format' => 'image',
        'advertiser' => 'National Lotteries Board',
        'url' => 'https://www.nlb.lk',
        'image_path' => 'assets/images/news/banner_strip_row1.jpg',
        'priority' => 90
    ],
    [
        'title' => 'Vallibel Finance PLC · Fixed Deposits & Premier Gold Loans',
        'position' => 'header_728x90',
        'ad_format' => 'image',
        'advertiser' => 'Vallibel Finance PLC',
        'url' => 'https://www.vallibelfinance.com',
        'image_path' => 'assets/images/news/banner_vallibel.jpg',
        'priority' => 80
    ],

    // --- top_left (Top Left Dual: 2-ad loop) ---
    [
        'title' => 'Metro College of Technology · Advanced Game Development Degree',
        'position' => 'top_left',
        'ad_format' => 'image',
        'advertiser' => 'Metro College',
        'url' => 'https://www.metro.lk',
        'image_path' => 'assets/images/news/banner_metro.jpg',
        'priority' => 85
    ],

    // --- top_right (Top Right Dual: 2-ad loop) ---
    [
        'title' => 'National Child Protection Authority · Care, Helpline & Counseling 1929',
        'position' => 'top_right',
        'ad_format' => 'image',
        'advertiser' => 'NCPA Sri Lanka',
        'url' => 'http://www.childprotection.gov.lk',
        'image_path' => 'assets/images/news/banner_strip_row2.jpg',
        'priority' => 85
    ],

    // --- sidebar_300x250 (Sidebar: 3-ad loop) ---
    [
        'title' => 'Commercial Bank Flash · Seamless Digital Lifestyle Banking',
        'position' => 'sidebar_300x250',
        'ad_format' => 'image',
        'advertiser' => 'Commercial Bank of Ceylon',
        'url' => 'https://www.combank.lk',
        'image_path' => 'assets/images/news/banner_combank.jpg',
        'priority' => 90
    ],
    [
        'title' => 'SLT-Mobitel 5G · Experience The Green Network Anywhere',
        'position' => 'sidebar_300x250',
        'ad_format' => 'image',
        'advertiser' => 'SLT-Mobitel Mobile',
        'url' => 'https://www.mobitel.lk',
        'image_path' => 'assets/images/news/banner_mobitel.jpg',
        'priority' => 80
    ],

    // --- sticky (Sticky Bottom Floating: 2-ad loop) ---
    [
        'title' => 'Dialog Axiata 5G · Unlimited Home Broadband & Streaming',
        'position' => 'sticky',
        'ad_format' => 'image',
        'advertiser' => 'Dialog Axiata',
        'url' => 'https://www.dialog.lk',
        'image_path' => 'uploads/ads/ad_sticky_728x90.png',
        'priority' => 90
    ],

    // --- homepage_mid_728x90 (Mid Page Divider: 2-ad loop) ---
    [
        'title' => 'Keells Supermarkets · Up to 25% Off with Credit Cards This Season',
        'position' => 'homepage_mid_728x90',
        'ad_format' => 'image',
        'advertiser' => 'Keells Supermarket',
        'url' => 'https://www.keellssuper.com',
        'image_path' => 'assets/images/news/banner_retail.jpg',
        'priority' => 85
    ],

    // --- inline_feed (Inside News Feed: 2-ad loop) ---
    [
        'title' => 'Singer Sri Lanka · Mega Electronics & Smart Appliances Festival',
        'position' => 'inline_feed',
        'ad_format' => 'image',
        'advertiser' => 'Singer Sri Lanka',
        'url' => 'https://www.singersl.com',
        'image_path' => 'assets/images/news/banner_retail.jpg',
        'priority' => 85
    ]
];

$insAd = $db->prepare("INSERT INTO advertisements (title, position, ad_format, advertiser, url, image_path, priority, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, 1)");

foreach ($multiAds as $ad) {
    try {
        // Check if ad with same title and position already exists
        $chk = $db->prepare("SELECT id FROM advertisements WHERE title = ? AND position = ?");
        $chk->execute([$ad['title'], $ad['position']]);
        $exists = $chk->fetchColumn();
        if (!$exists) {
            $insAd->execute([
                $ad['title'],
                $ad['position'],
                $ad['ad_format'],
                $ad['advertiser'],
                $ad['url'],
                $ad['image_path'],
                $ad['priority']
            ]);
            echo "  [+] Added Ad for [{$ad['position']}]: {$ad['title']} (advertiser: {$ad['advertiser']})\n";
        } else {
            echo "  [.] Already exists [{$ad['position']}]: {$ad['title']}\n";
        }
    } catch (\Exception $e) {
        echo "  [!] Error adding ad [{$ad['position']}]: " . $e->getMessage() . "\n";
    }
}

echo "\n=== 2. SEEDING NEW RICH DEMO NEWS ARTICLES ===\n";

$newArticles = [
    [
        'slug' => 'sri-lanka-cricket-historic-championship-series-victory',
        'cat_id' => 4, // Sports
        'is_featured' => 1,
        'is_breaking' => 1,
        'image' => 'assets/images/news/sports_nissanka.jpg',
        'title_en' => 'Sri Lanka Clinches Historic Bilateral ODI Championship with Record Victory',
        'title_si' => 'ඓතිහාසික වාර්තාගත ජයක් සමගින් ශ්‍රී ලංකාව එක්දින තරගාවලිය ජයගනී',
        'title_ta' => 'வரலாற்று சிறப்புமிக்க வெற்றியுடன் இலங்கை ஒருநாள் சர்வதேச தொடரை கைப்பற்றியது',
        'summary_en' => 'A blistering batting masterclass backed by disciplined pace bowling powered Sri Lanka to a dominant series-clinching triumph before an ecstatic packed home crowd.',
        'content_en' => '<p>Sri Lanka secured an emphatic victory in the series decider, outclassing the visitors across every department of the game. Pathum Nissanka set the tone with a fluent century, followed by a clinical bowling exhibition under lights.</p><p>With this win, the national team consolidates its position in the ICC top rankings, winning praise from cricket commentators and legends across the globe.</p>'
    ],
    [
        'slug' => 'renewable-energy-landmark-solar-wind-project-mannar',
        'cat_id' => 5, // Technology
        'is_featured' => 0,
        'is_breaking' => 0,
        'image' => 'assets/images/news/tech_spacex.jpg',
        'title_en' => 'Sri Lanka Commissions 500MW Hybrid Solar & Wind Grid Project in Mannar',
        'title_si' => 'මන්නාරම මෙගාවොට් 500ක සුළං සහ සූර්ය දෙමුහුන් බලශක්ති ව්‍යාපෘතිය ජාතික විදුලිබල පද්ධතියට',
        'title_ta' => 'மன்னாரில் 500MW சூரிய மற்றும் காற்று சக்தி கலப்பு மின் திட்டத்தை இலங்கை தொடங்கியது',
        'summary_en' => 'The flagship renewable project will supply over 20% of the island’s daytime electricity requirement, drastically reducing fossil fuel import expenditure.',
        'content_en' => '<p>The Ministry of Power and Energy successfully connected the Phase-1 500MW Mannar hybrid clean energy facility to the national grid. Built with international concessionary financing, the plant features state-of-the-art wind turbines and high-efficiency bifacial solar arrays.</p><p>Officials confirmed the milestone will save approximately $180 million annually in imported heavy fuel costs.</p>'
    ],
    [
        'slug' => 'sri-lanka-annual-tourism-crosses-two-million-visitors',
        'cat_id' => 3, // Business
        'is_featured' => 1,
        'is_breaking' => 0,
        'image' => 'assets/images/news/top_picture_girls.jpg',
        'title_en' => 'Sri Lanka Tourism Exceeds 2 Million Annual Arrivals Ahead of Projections',
        'title_si' => 'ශ්‍රී ලංකා සංචාරක පැමිණීම් අපේක්ෂිත ඉලක්ක අභිබවා මිලියන 2 සීමාව පසුකරයි',
        'title_ta' => 'இலங்கை சுற்றுலாப் பயணிகளின் வருகை கணிப்புகளை தாண்டி 2 மில்லியனை தாண்டியது',
        'summary_en' => 'Buoyed by surging demand from European, Indian, and East Asian travellers, foreign exchange earnings from hospitality surged past $3.2 billion.',
        'content_en' => '<p>Sri Lanka Tourism Development Authority (SLTDA) confirmed that visitor numbers for the year crossed the milestone 2.1 million mark, registering the fastest post-pandemic growth rate in South Asia.</p><p>Key attractions in the Central Highlands, Southern coastline, and cultural triangle experienced near full hotel occupancy throughout the peak winter season.</p>'
    ],
    [
        'slug' => 'colombo-international-film-festival-celebrates-cinema',
        'cat_id' => 6, // Entertainment
        'is_featured' => 0,
        'is_breaking' => 0,
        'image' => 'assets/images/news/ent_the_batman.jpg',
        'title_en' => 'Colombo International Film Festival Opens with 40 Acclaimed Global Premieres',
        'title_si' => 'ලොව සම්මානනීය සිනමා නිර්මාණ 40ක් සමගින් කොළඹ ජාත්‍යන්තර සිනමා උළෙල ඇරඹේ',
        'title_ta' => 'கொழும்பு சர்வதேச திரைப்பட விழா 40 உலகளாவிய திரையிடல்களுடன் தொடங்கியது',
        'summary_en' => 'Renowned directors, Asian indie filmmakers, and cinematic enthusiasts gathered in Colombo for the prestigious annual festival showcase.',
        'content_en' => '<p>The 9th edition of the Colombo International Film Festival kicked off with a dazzling red-carpet ceremony attended by regional cinema stars and delegates from 18 nations.</p><p>The week-long festival will feature masterclasses, student workshops, and free public outdoor screenings across key cultural venues in the city.</p>'
    ],
    [
        'slug' => 'ai-healthcare-diagnostic-platform-launched-colombo',
        'cat_id' => 5, // Technology
        'is_featured' => 0,
        'is_breaking' => 1,
        'image' => 'assets/images/news/tech_robot.jpg',
        'title_en' => 'Sri Lankan HealthTech Startup Deploys AI Diagnostic Screening in 50 Hospitals',
        'title_si' => 'ශ්‍රී ලාංකික නව නිපැයුම්කරුවන්ගේ AI රෝග විනිශ්චය පද්ධතිය රෝහල් 50කට හඳුන්වා දෙයි',
        'title_ta' => 'இலங்கை ஸ்டார்ட்அப் நிறுவனத்தின் AI மருத்துவ பரிசோதனை தளம் 50 மருத்துவமனைகளில் அறிமுகம்',
        'summary_en' => 'A breakthrough artificial intelligence tool developed by local biomedical engineers is expediting early detection of radiological pathologies.',
        'content_en' => '<p>In collaboration with the Ministry of Health, a Colombo-based healthcare technology company launched an automated clinical imaging AI tool across provincial general hospitals.</p><p>The deep-learning model analyzes chest radiographs and scans in under 15 seconds, assisting radiologists in prioritizing critical emergencies.</p>'
    ],
    [
        'slug' => 'central-bank-maintains-favorable-interest-rates-growth',
        'cat_id' => 3, // Business
        'is_featured' => 0,
        'is_breaking' => 0,
        'image' => 'assets/images/news/lead_world_bank.jpg',
        'title_en' => 'Central Bank Retains Policy Rates Steady to Anchor Low Inflation and Growth',
        'title_si' => 'ዋጋ උද්ධමනය පාලනය කරමින් ආර්ථික වර්ධනයට මග පාදමින් මහ බැංකුව පොලී අනුපාත නොවෙනස්ව තබයි',
        'title_ta' => 'பணவீக்கத்தை கட்டுப்படுத்தி வளர்ச்சியை ஊக்குவிக்க மத்திய வங்கி வட்டி விகிதங்களை மாற்றமின்றி பராமரிக்கிறது',
        'summary_en' => 'The Monetary Policy Board noted that private credit expansion is accelerating as domestic business sentiment reaches a three-year high.',
        'content_en' => '<p>The Central Bank of Sri Lanka announced its decision to maintain the Standing Deposit Facility Rate (SDFR) and Standing Lending Facility Rate (SLFR) at their present supportive levels.</p><p>The decision reflects confidence that headline inflation will remain comfortably anchored within the targeted band while enabling commercial banks to expand competitive enterprise lending.</p>'
    ],
    [
        'slug' => 'diplomatic-maritime-security-conclave-hosted-colombo',
        'cat_id' => 2, // Politics
        'is_featured' => 0,
        'is_breaking' => 0,
        'image' => 'assets/images/news/other_summit.jpg',
        'title_en' => 'Indian Ocean Maritime Security Conclave Reaches Historic Freedom of Navigation Pact',
        'title_si' => 'ඉන්දීය සාගර නාවික ආරක්ෂක සමුළුව කොළඹදී ඵලදායී එකඟතාවන් රැසකට එළඹෙයි',
        'title_ta' => 'கொழும்பில் நடைபெற்ற இந்தியப் பெருங்கடல் கடல்சார் பாதுகாப்பு மாநாடு முக்கிய உடன்படிக்கையை எட்டியது',
        'summary_en' => 'Delegates from 24 littoral states signed the Colombo Communique, emphasizing collaborative antipiracy patrols and sea lane safety.',
        'content_en' => '<p>Colombo hosted defense ministers, maritime experts, and naval commanders for the Indian Ocean Security Conclave. Discussions focused on enhancing maritime domain awareness, environmental disaster readiness, and cooperative emergency responses.</p>'
    ],
    [
        'slug' => 'asian-relay-grand-prix-sri-lanka-wins-double-gold',
        'cat_id' => 4, // Sports
        'is_featured' => 0,
        'is_breaking' => 0,
        'image' => 'assets/images/news/sports_medvedev.jpg',
        'title_en' => 'Sri Lanka Sprint Quartet Grabs Double Gold at Asian Relay Championships',
        'title_si' => 'ආසියානු රිලේ ශූරතාවලියේදී ශ්‍රී ලංකා කෙටිදුර ධාවකයන් රන් පදක්කම් ද්විත්වයක් දිනාගනී',
        'title_ta' => 'ஆசிய ரிலே சாம்பியன்ஷிப்பில் இலங்கை தடகள அணி இரண்டு தங்கப் பதக்கங்களை வென்றது',
        'summary_en' => 'The men’s and women’s 4x400m relay teams shattered national records on a triumphant evening in Bangkok, qualifying for world championships.',
        'content_en' => '<p>Sri Lanka’s premier sprint relay teams delivered a sensational display of speed and baton exchange precision to secure double gold in the Asian Relay Grand Prix.</p><p>The resounding victories ensure direct qualification berths for the forthcoming World Athletics Relays in Europe.</p>'
    ]
];

$insArticle = $db->prepare("INSERT INTO articles (
    author_id, category_id, featured_image_id,
    title_en, title_si, title_ta, slug,
    summary_en, summary_si, summary_ta,
    content_en, content_si, content_ta,
    status, is_breaking, is_featured,
    published_at, created_at, updated_at
) VALUES (
    1, ?, ?,
    ?, ?, ?, ?,
    ?, ?, ?,
    ?, ?, ?,
    'published', ?, ?,
    ?, ?, ?
)");

foreach ($newArticles as $art) {
    // Check if slug exists
    $chk = $db->prepare("SELECT id FROM articles WHERE slug = ?");
    $chk->execute([$art['slug']]);
    $artId = $chk->fetchColumn();

    $mediaId = getMediaId($db, $art['image'], $art['title_en']);
    $pubDate = date('Y-m-d H:i:s', strtotime('-' . rand(2, 48) . ' hours'));

    if (!$artId) {
        $insArticle->execute([
            $art['cat_id'],
            $mediaId,
            $art['title_en'],
            $art['title_si'],
            $art['title_ta'],
            $art['slug'],
            $art['summary_en'],
            $art['summary_si'],
            $art['summary_ta'],
            $art['content_en'],
            $art['content_en'],
            $art['content_en'],
            $art['is_breaking'],
            $art['is_featured'],
            $pubDate,
            $now,
            $now
        ]);
        echo "  [+] Added Article: {$art['title_en']} (Category {$art['cat_id']})\n";
    } else {
        $upd = $db->prepare("UPDATE articles SET featured_image_id = ?, title_en = ?, title_si = ?, title_ta = ?, summary_en = ?, is_breaking = ?, is_featured = ? WHERE id = ?");
        $upd->execute([
            $mediaId,
            $art['title_en'],
            $art['title_si'],
            $art['title_ta'],
            $art['summary_en'],
            $art['is_breaking'],
            $art['is_featured'],
            $artId
        ]);
        echo "  [.] Updated Article [{$artId}]: {$art['title_en']}\n";
    }
}

// Clear AdHelper cache
\App\Helpers\AdHelper::clearCache();

echo "\nSeeding complete! Multiple looping ads and fresh news articles successfully added!\n";
