<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/Core/Database.php';

$db = \App\Core\Database::getInstance()->getConnection();
$now = date('Y-m-d H:i:s');

function addOrUpdateArticle($db, $data) {
    // Media
    $imgPath = "assets/images/adaderana/" . $data['image'];
    $stmt = $db->prepare("SELECT id FROM media WHERE file_path = ?");
    $stmt->execute([$imgPath]);
    $mediaId = $stmt->fetchColumn();
    if (!$mediaId) {
        $insM = $db->prepare("INSERT INTO media (uploader_id, file_name, file_path, file_type, alt_text, caption, created_at) VALUES (1, ?, ?, 'image/jpeg', ?, '', ?)");
        $insM->execute([$data['image'], $imgPath, $data['title_en'], date('Y-m-d H:i:s')]);
        $mediaId = $db->lastInsertId();
    }

    $chk = $db->prepare("SELECT id FROM articles WHERE slug = ?");
    $chk->execute([$data['slug']]);
    $existing = $chk->fetchColumn();

    $publishedAt = date('Y-m-d H:i:s', strtotime("-" . ($data['hours_ago'] ?? 1) . " hours"));
    $videoUrl = $data['video_url'] ?? '';

    if ($existing) {
        $sql = "UPDATE articles SET 
            category_id = ?, featured_image_id = ?,
            title_en = ?, title_si = ?, title_ta = ?,
            summary_en = ?, summary_si = ?, summary_ta = ?,
            content_en = ?, content_si = ?, content_ta = ?,
            status = 'published', is_breaking = ?, is_featured = ?,
            video_url = ?, published_at = ?, updated_at = ?
            WHERE id = ?";
        $db->prepare($sql)->execute([
            $data['category_id'], $mediaId,
            $data['title_en'], $data['title_si'] ?? $data['title_en'], $data['title_ta'] ?? $data['title_en'],
            $data['summary_en'], $data['summary_si'] ?? $data['summary_en'], $data['summary_ta'] ?? $data['summary_en'],
            $data['content_en'], $data['content_si'] ?? $data['content_en'], $data['content_ta'] ?? $data['content_en'],
            $data['is_breaking'] ?? 0, $data['is_featured'] ?? 0,
            $videoUrl, $publishedAt, $now, $existing
        ]);
    } else {
        $sql = "INSERT INTO articles (
            author_id, category_id, featured_image_id,
            title_en, title_si, title_ta, slug,
            summary_en, summary_si, summary_ta,
            content_en, content_si, content_ta,
            status, is_breaking, is_featured, video_url,
            published_at, created_at, updated_at
        ) VALUES (
            1, ?, ?,
            ?, ?, ?, ?,
            ?, ?, ?,
            ?, ?, ?,
            'published', ?, ?, ?,
            ?, ?, ?
        )";
        $db->prepare($sql)->execute([
            $data['category_id'], $mediaId,
            $data['title_en'], $data['title_si'] ?? $data['title_en'], $data['title_ta'] ?? $data['title_en'],
            $data['slug'],
            $data['summary_en'], $data['summary_si'] ?? $data['summary_en'], $data['summary_ta'] ?? $data['summary_en'],
            $data['content_en'], $data['content_si'] ?? $data['content_en'], $data['content_ta'] ?? $data['content_en'],
            $data['is_breaking'] ?? 0, $data['is_featured'] ?? 0, $videoUrl,
            $publishedAt, $now, $now
        ]);
    }
}

// Articles List
$articles = [
    // --- BUSINESS (Lead Story) ---
    [
        'category_id' => 3,
        'slug' => 'sri-lanka-economy-reaches-pre-crisis-levels-world-bank',
        'is_featured' => 1,
        'is_breaking' => 1,
        'hours_ago' => 0.2,
        'image' => 'lead_world_bank.jpg',
        'title_en' => 'Sri Lanka’s economy reaches pre-crisis levels but recovery uneven - World Bank',
        'title_si' => 'ශ්‍රී ලංකා ආර්ථිකය පෙර අර්බුද මට්ටමට ළඟා වුවද ප්‍රකෘතිමත් වීම අසමාන බව ලෝක බැංකුව පවසයි',
        'title_ta' => 'இலங்கை பொருளாதாரம் நெருக்கடிக்கு முந்தைய நிலையை எட்டியுள்ள போதிலும் மீட்சி சீரற்றது - உலக வங்கி',
        'summary_en' => 'The World Bank reports that while economic output has rebounded towards pre-crisis levels, deep structural disparities persist across regional sectors.',
        'content_en' => '<p>The World Bank’s latest semi-annual development update confirms that Sri Lanka’s overall economic output has recovered significantly following extensive macroeconomic stabilization measures.</p><p>Growth was led by robust recovery in international tourism, strong agricultural yields, and consistent remittance inflows from overseas Sri Lankan workers.</p><p>However, the report cautions that domestic poverty numbers remain concerningly elevated, urging policymakers to protect vulnerable households through fortified social safety nets and sustained fiscal prudence.</p>'
    ],

    // --- LOCAL & POLITICS ---
    [
        'category_id' => 1,
        'slug' => 'srilankan-airlines-flight-chennai-returns-colombo',
        'hours_ago' => 0.5,
        'image' => 'plane_chennai.jpg',
        'title_en' => 'SriLankan Airlines flight to Chennai returns to Colombo due to technical issue',
        'title_si' => 'තාක්ෂණික දෝෂයක් හේතුවෙන් චෙන්නායි බලා ගිය ශ්‍රීලංකන් ගුවන් යානය යළි කටුනායකට',
        'title_ta' => 'தொழில்நுட்ப கோளாறு காரணமாக சென்னை நோக்கி சென்ற ஸ்ரீலங்கன் விமானம் கொழும்புக்கு திரும்பியது',
        'summary_en' => 'A Chennai-bound flight safely returned to Bandaranaike International Airport following an operational warning in the cockpit.',
        'content_en' => '<p>Flight UL 121 departed Colombo on schedule but aircrew initiated a precautionary return to Bandaranaike International Airport after standard warning sensors indicated a minor technical anomaly.</p><p>All 142 passengers disembarked normally without incident and were accommodated on a replacement aircraft shortly thereafter.</p>'
    ],
    [
        'category_id' => 2,
        'slug' => 'president-probe-matara-court-complex',
        'hours_ago' => 0.8,
        'image' => 'matara_court.jpg',
        'title_en' => 'President instructs to probe delay in completing Matara court complex',
        'title_si' => 'මාතර අධිකරණ සංකීර්ණයේ වැඩ අවසන්වීම ප්‍රමාද වීම ගැන සොයා බැලීමට ජනපතිගෙන් උපදෙස්',
        'title_ta' => 'மாத்தறை நீதிமன்ற வளாகப் பணிகள் நிறைவடைவதில் ஏற்பட்டுள்ள தாமதம் குறித்து விசாரிக்க ஜனாதிபதி உத்தரவு',
        'summary_en' => 'Special directive issued to judicial infrastructure oversight teams to expedite stalled provincial court building works.',
        'content_en' => '<p>The President ordered an immediate inquiry into procurement bottlenecks and contractor scheduling issues that have held up completion of the Matara combined courts building.</p>'
    ],
    [
        'category_id' => 2,
        'slug' => 'government-prioritizes-socio-economic-welfare-pm',
        'hours_ago' => 1.2,
        'image' => 'pm_welfare.jpg',
        'title_en' => 'Government prioritizes socio-economic welfare - Prime Minister',
        'title_si' => 'රජයේ ප්‍රමුඛතාවය සමාජ හා ආර්ථික සුභසාධනයට - අගමැති',
        'title_ta' => 'அரசாங்கம் சமூக பொருளாதார நலனுக்கே முன்னுரிமை அளிக்கிறது - பிரதமர்',
        'summary_en' => 'Prime Minister outlines national welfare enhancement goals focusing on nutrition, family assistance, and rural education.',
        'content_en' => '<p>Addressing civic representatives in Colombo, the Prime Minister emphasized that government fiscal strategies are designed around safeguarding living standards for middle and lower-income communities.</p>'
    ],
    [
        'category_id' => 2,
        'slug' => 'speaker-signs-three-bills-into-law',
        'hours_ago' => 3.0,
        'image' => 'speaker_bills.jpg',
        'title_en' => 'Speaker signs three Bills into law following parliamentary approval',
        'title_si' => 'පාර්ලිමේන්තුවේ සම්මත වූ පනත් කෙටුම්පත් තුනකට කථානායකවරයා අත්සන් තබයි',
        'title_ta' => 'பாராளுமன்றத்தில் நிறைவேற்றப்பட்ட மூன்று சட்டமூலங்களுக்கு சபாநாயகர் கையொப்பமிட்டார்',
        'summary_en' => 'Three key legislative enactments concerning public finance, administrative transparency, and civil law reform enter into force.',
        'content_en' => '<p>The Speaker of Parliament endorsed the certificate on three newly passed acts today, establishing formal legal frameworks that had received bipartisan support during recent sittings.</p>'
    ],

    // --- SPORTS ---
    [
        'category_id' => 4,
        'slug' => 'pathum-nissanka-historic-double-hundred-odi',
        'is_featured' => 1,
        'hours_ago' => 4.0,
        'image' => 'sports_nissanka.jpg',
        'title_en' => 'Pathum Nissanka rewrites record books with historic double hundred in ODI',
        'title_si' => 'පැතුම් නිස්සංක එක්දින තරගයකදී ඓතිහාසික ද්විත්ව ශතකයක් ලබා වාර්තා පොත් අලුත් කරයි',
        'title_ta' => 'பத்தும் நிஸ்ஸங்க சர்வதேச ஒருநாள் போட்டியில் வரலாற்று இரட்டை சதம் விளாசி சாதனை',
        'summary_en' => 'Pathum Nissanka hammered an unbeaten 210 off 139 deliveries, becoming the very first Sri Lankan to score an ODI double hundred.',
        'content_en' => '<p>Opening batsman Pathum Nissanka etched his name into world cricket history with a breathtaking innings of 210 not out, featuring 20 boundaries and 8 sixes in a dominant home masterclass.</p>'
    ],
    [
        'category_id' => 4,
        'slug' => 'daniil-medvedev-australian-open-quest',
        'hours_ago' => 5.0,
        'image' => 'sports_medvedev.jpg',
        'title_en' => 'Daniil Medvedev rebuilds confidence ahead of Australian Open title quest',
        'title_si' => 'ඔස්ට්‍රේලියානු විවෘත ටෙනිස් තරගාවලියට පෙර ඩැනීල් මෙද්වදෙව් ඉහළ විශ්වාසයකින් සූදානම් වේ',
        'title_ta' => 'அவுஸ்திரேலிய பகிரங்க டென்னிஸ் தொடருக்கு முன்னதாக டானில் மெட்வடேவ் தீவிர பயிற்சி',
        'summary_en' => 'The former US Open champion reflects on intense pre-season training and mental resilience ahead of Melbourne Grand Slam.',
        'content_en' => '<p>Daniil Medvedev expressed supreme satisfaction with his hard-court preparations, outlining his aggressive tactical adjustments and conditioning regimen designed to outlast rivals at Melbourne Park.</p>'
    ],
    [
        'category_id' => 4,
        'slug' => 'sri-lanka-finishes-fourth-asian-relay-championship',
        'hours_ago' => 6.0,
        'image' => 'sports_relay.jpg',
        'title_en' => 'Sri Lanka finishes 4th in Asian Relay Championship finals',
        'title_si' => 'ආසියානු සහය දිවීමේ ශූරතාවලියේ සිව්වන ස්ථානය ශ්‍රී ලංකාවට',
        'title_ta' => 'ஆசிய அஞ்சலோட்ட சாம்பியன்ஷிப் இறுதிப் போட்டியில் இலங்கை அணிக்கு 4வது இடம்',
        'summary_en' => 'National 4x400m relay quartet puts on strong performance against continental superpowers in Bangkok.',
        'content_en' => '<p>Sri Lanka clocked a spirited season-best time in the men’s 4x400m final, narrowly missing out on the podium in a fiercely contested championship race.</p>'
    ],
    [
        'category_id' => 4,
        'slug' => 'slc-names-squad-t20i-series',
        'hours_ago' => 7.0,
        'image' => 'sports_cricket_squad.jpg',
        'title_en' => 'SLC names balanced squad for upcoming international T20I series',
        'title_si' => 'එළඹෙන ජාත්‍යන්තර විස්සයි20 තරගාවලිය සඳහා ශ්‍රී ලංකා සංචිතය නම් කෙරේ',
        'title_ta' => 'வரவிருக்கும் சர்வதேச டி20 தொடருக்கான இலங்கை அணி அறிவிப்பு',
        'summary_en' => 'Selection committee recalls explosive all-rounders while rewarding top-performing domestic youngsters.',
        'content_en' => '<p>Sri Lanka Cricket selectors finalized a 16-man unit blending seasoned middle-order depth with rapid pace prospects ahead of the impending three-match bilateral series.</p>'
    ],

    // --- TECHNOLOGY ---
    [
        'category_id' => 6,
        'slug' => 'ray-ban-meta-smart-glasses-multimodal-ai',
        'is_featured' => 1,
        'hours_ago' => 8.0,
        'image' => 'tech_smart_glasses.jpg',
        'title_en' => 'Ray-Ban Meta smart glasses get multimodal AI features in latest software update',
        'title_si' => 'රේබෑන් මෙටා ස්මාර්ට් කණ්ණාඩි සඳහා නව බහුමාධ්‍ය කෘතිම බුද්ධි පහසුකම් එක් කෙරේ',
        'title_ta' => 'ரே-பான் மெட்டா ஸ்மார்ட் கண்ணாடிகளில் புதிய செயற்கை நுண்ணறிவு அம்சங்கள் அறிமுகம்',
        'summary_en' => 'Latest firmware rollout enables real-time scene understanding, conversational assistance, and language translation.',
        'content_en' => '<p>Meta has pushed a groundbreaking software upgrade to its smart glasses lineup, allowing users to ask queries about what they see around them and receive instant spoken audio commentary.</p>'
    ],
    [
        'category_id' => 6,
        'slug' => 'apple-issues-urgent-security-update',
        'hours_ago' => 9.0,
        'image' => 'tech_apple.jpg',
        'title_en' => 'Apple issues urgent security update for iPhones and iPads worldwide',
        'title_si' => 'ඇපල් දුරකථන සහ ටැබ්ලට් පරිගණක සඳහා හදිසි ආරක්ෂක යාවත්කාලීනයක්',
        'title_ta' => 'ஐபோன் மற்றும் ஐபேட்களுக்கான அவசர பாதுகாப்பு அப்டேட்டை வெளியிட்டது ஆப்பிள் நிறுவனம்',
        'summary_en' => 'Users advised to install critical patches remediating zero-day vulnerability across mobile operating systems.',
        'content_en' => '<p>Apple released rapid security patches for iOS and iPadOS addressing memory vulnerabilities actively monitored by cybersecurity watchdogs.</p>'
    ],
    [
        'category_id' => 6,
        'slug' => 'china-unveils-humanoid-robot-complex-tasks',
        'hours_ago' => 10.0,
        'image' => 'tech_robot.jpg',
        'title_en' => 'Advanced humanoid robot capable of factory assembly unveiled',
        'title_si' => 'කර්මාන්තශාලා එකලස් කිරීම් කළ හැකි උසස් මානවරූපී රොබෝවක් එළිදක්වයි',
        'title_ta' => 'தொழிற்சாலை பணிகளை மேற்கொள்ளும் அதிநவீன மனித உருவ ரோபோ அறிமுகம்',
        'summary_en' => 'Engineers display dynamic bipedal robot boasting millimeter precision manipulation and autonomous navigation.',
        'content_en' => '<p>The next-generation robotic platform utilizes advanced neural actuators and stereo vision systems to handle complex manufacturing assembly sequences alongside human colleagues.</p>'
    ],
    [
        'category_id' => 6,
        'slug' => 'spacex-launches-falcon-9-starlink',
        'hours_ago' => 11.0,
        'image' => 'tech_spacex.jpg',
        'title_en' => 'SpaceX launches Falcon 9 carrying Starlink broadband constellation',
        'title_si' => 'ස්ටාර්ලින්ක් චන්ද්‍රිකා රැගත් ෆැල්කන් 9 රොකට්ටුව සාර්ථකව උඩුගුවනට',
        'title_ta' => 'ஸ்டார்லிங்க் செயற்கைக்கோள்களுடன் பால்கன் 9 ரொக்கெட்டை வெற்றிகரமாக ஏவிய ஸ்பேஸ்எக்ஸ்',
        'summary_en' => 'Another batch of low-Earth orbit satellites deploy successfully to bolster worldwide high-speed satellite internet.',
        'content_en' => '<p>The Falcon 9 first-stage booster made its designated touchdown on a sea drone ship moments after fairing separation placed 23 communication satellites into initial transfer orbits.</p>'
    ],

    // --- ENTERTAINMENT ---
    [
        'category_id' => 5,
        'slug' => 'the-batman-part-ii-release-schedule',
        'is_featured' => 1,
        'hours_ago' => 12.0,
        'image' => 'ent_the_batman.jpg',
        'title_en' => '‘The Batman Part II’ moves to 2026: Warner Bros announces release schedule changes',
        'title_si' => '‘ද බැට්මෑන් පාට් 2’ සිනමාපටය 2026 වසරට කල් යයි - වෝනර් බ්‍රදර්ස් නිවේදනය කරයි',
        'title_ta' => '‘தி பேட்மேன் பாகம் 2’ வெளியீடு 2026 ஆம் ஆண்டிற்கு ஒத்திவைப்பு - வார்னர் பிரதர்ஸ் அறிவிப்பு',
        'summary_en' => 'Director Matt Reeves and star Robert Pattinson to return as production dates adjust for the anticipated noir comic sequel.',
        'content_en' => '<p>Warner Bros and DC Studios officially confirmed that the second installment of the critically acclaimed Gotham City detective saga has revised its premiere date to ensure complete cinematic refinement.</p>'
    ],
    [
        'category_id' => 5,
        'slug' => 'christopher-nolan-academy-awards-oppenheimer',
        'hours_ago' => 13.0,
        'image' => 'ent_nolan.jpg',
        'title_en' => 'Christopher Nolan sweeps major honours at world cinema ceremonies',
        'title_si' => 'ක්‍රිස්ටෝෆර් නෝලන් ලෝක සිනමා සම්මාන උළෙලේදී ප්‍රධාන සම්මාන රැසක් දිනා ගනියි',
        'title_ta' => 'பிரபல திரைப்பட இயக்குநர் கிறிஸ்டோபர் நோலன் உலக திரைப்பட விருது விழாக்களில் ஆதிக்கம்',
        'summary_en' => 'Historical biographical drama praised for monumental directing, editing, and nuanced acting performances.',
        'content_en' => '<p>Director Christopher Nolan received widespread international acclaim as his magnum opus claimed Best Picture and Best Director honors during prestigious film academy ceremonies.</p>'
    ],
    [
        'category_id' => 5,
        'slug' => 'dune-part-two-box-office-milestone',
        'hours_ago' => 14.0,
        'image' => 'ent_dune.jpg',
        'title_en' => 'Dune: Part Two crosses $700M worldwide box office milestone',
        'title_si' => '‘ඩියුන්: පාට් 2’ සිනමාපටය ඩොලර් මිලියන 700 ක ආදායම් සීමාව ඉක්මවයි',
        'title_ta' => '‘டூன்: பார்ட் 2’ திரைப்படம் உலகளவில் 700 மில்லியன் டொலர் வசூல் சாதனை',
        'summary_en' => 'Denis Villeneuve’s science fiction epic dominates global IMAX and standard theatrical attendance charts.',
        'content_en' => '<p>Stunning cinematography and massive word-of-mouth momentum propelled the second chapter of Frank Herbert’s sci-fi adaptation to record-setting box office receipts across four continents.</p>'
    ],

    // --- INTERNATIONAL / OTHER ---
    [
        'category_id' => 7,
        'slug' => 'global-pet-rescue-conference-animal-welfare',
        'is_featured' => 1,
        'hours_ago' => 15.0,
        'image' => 'other_pet_rescue.jpg',
        'title_en' => 'Global pet rescue conference in Colombo highlights animal welfare initiatives',
        'title_si' => 'සත්ව සුභසාධන වැඩසටහන් පිළිබඳ ජාත්‍යන්තර සුරතල් සතුන් මුදාගැනීමේ සමුළුව කොළඹදී',
        'title_ta' => 'விலங்கு நலன் குறித்த சர்வதேச மாநாடு கொழும்பில் வெற்றிகரமாக இடம்பெற்றது',
        'summary_en' => 'Veterinary leaders, rescue volunteers, and international shelters formulate compassionate sterilization and adoption framework.',
        'content_en' => '<p>The inaugural animal shelter and rescue symposium in Colombo brought together dedicated caregivers from across Sri Lanka and foreign welfare foundations to discuss humane rabies eradication and foster placement networks.</p>'
    ],
    [
        'category_id' => 7,
        'slug' => 'key-regional-security-summit-singapore',
        'hours_ago' => 16.0,
        'image' => 'other_summit.jpg',
        'title_en' => 'Key regional security summit opens in Singapore with global defence chiefs',
        'title_si' => 'කලාපීය ආරක්ෂක සමුළුව ලෝක ආරක්ෂක ප්‍රධානීන්ගේ සහභාගීත්වයෙන් සිංගප්පූරුවේදී ඇරඹේ',
        'title_ta' => 'முக்கிய பிராந்திய பாதுகாப்பு உச்சி மாநாடு சிங்கப்பூரில் ஆரம்பம்',
        'summary_en' => 'Maritime freedom, cyber defense cooperation, and crisis hotlines dominate high-level diplomatic talks in Southeast Asia.',
        'content_en' => '<p>Ministers and military chiefs gathered for the Shangri-La dialogue to discuss de-escalation protocols and collective humanitarian assistance agreements across Indian and Pacific Ocean waters.</p>'
    ],

    // --- VIDEO STORIES ---
    [
        'category_id' => 1,
        'slug' => 'video-special-security-katunayake-airport',
        'hours_ago' => 2.5,
        'image' => 'video_airport_security.jpg',
        'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        'title_en' => 'Special security plan deployed at Katunayake International Airport',
        'title_si' => 'කටුනායක ගුවන් තොටුපළ සඳහා විශේෂ ආරක්ෂක සැලැස්මක් ක්‍රියාත්මකයි',
        'title_ta' => 'கட்டுநாயக்க விமான நிலையத்திற்கு விசேட பாதுகாப்பு திட்டம் அமுல்படுத்தல்',
        'summary_en' => 'Aviation security services introduce biometric passenger screening and augmented perimeter surveillance.',
        'content_en' => '<p>Authorities have established round-the-clock patrol units and state-of-the-art surveillance checkpoints across both arrival and departure wings at Katunayake to guarantee optimal passenger flow and safety.</p>'
    ],
    [
        'category_id' => 2,
        'slug' => 'video-ranil-chamber-address',
        'hours_ago' => 3.5,
        'image' => 'video_ranil_chamber.jpg',
        'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        'title_en' => 'President addresses Ceylon Chamber of Commerce on export expansion',
        'title_si' => 'අපනයන ව්‍යාප්තිය පිළිබඳව ලංකා වාණිජ මණ්ඩලයේදී විශේෂ දේශනයක්',
        'title_ta' => 'ஏற்றுமதி விரிவாக்கம் குறித்து இலங்கை வர்த்தக சம்மேளனத்தில் ஜனாதிபதி உரை',
        'summary_en' => 'Keynote address emphasizes digitizing trade windows and supporting small-medium value-added manufacturers.',
        'content_en' => '<p>The government unveiled export diversification incentives and renewed customs automation procedures during the annual corporate breakfast forum held in Colombo.</p>'
    ],
    [
        'category_id' => 1,
        'slug' => 'video-sltb-additional-buses-festive-season',
        'hours_ago' => 4.5,
        'image' => 'video_sltb_buses.jpg',
        'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        'title_en' => 'SLTB to deploy 1,000 additional buses for holiday commuters',
        'title_si' => 'උත්සව සමය වෙනුවෙන් ලංගම අතිරේක බස් රථ 1,000 ක් ධාවනයට එක් කෙරේ',
        'title_ta' => 'பண்டிகைக் காலத்தை முன்னிட்டு 1,000 மேலதிக பேருந்துகளை சேவையில் ஈடுபடுத்த இலங்கை போக்குவரத்து சபை திட்டம்',
        'summary_en' => 'Transport Ministry coordinates inter-provincial public transit services to ensure seamless journeying for holidaymakers.',
        'content_en' => '<p>Special bus services connecting Bastian Mawatha, Pettah, Kadawatha, and Makumbura to outstation provincial hubs commenced operations with fixed fair scheduling.</p>'
    ]
];

foreach ($articles as $a) {
    addOrUpdateArticle($db, $a);
}

// Clear all cache
if (is_dir(__DIR__ . '/../storage/cache')) {
    foreach (glob(__DIR__ . '/../storage/cache/*') as $f) {
        @unlink($f);
    }
}

echo "Rich news stories seeded successfully across all categories.\n";
