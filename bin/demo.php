<?php
if (PHP_SAPI !== 'cli')
    exit;
require dirname(__DIR__) . '/app/bootstrap.php';
if (APP_ENV !== 'demo')
    exit("Demo seed requires APP_ENV=demo. Use a separate database.\n");
$db = App\Core\Database::getInstance()->getConnection();
$email = getenv('NEWS_DEMO_EMAIL') ?: 'preview@example.test';
$password = getenv('NEWS_DEMO_PASSWORD') ?: '';
if (strlen($password) < 12)
    exit("Set NEWS_DEMO_PASSWORD to a unique password of at least 12 characters.\n");
$find = $db->prepare('SELECT id FROM users WHERE email=?');
$find->execute([$email]);
$author = $find->fetchColumn();
if (!$author) {
    $db->prepare("INSERT INTO users (name,email,password,role_id,status) VALUES (?,?,?,?,?)")->execute(['Preview Editor', $email, password_hash($password, PASSWORD_DEFAULT), 1, 'active']);
    $author = $db->lastInsertId();
}
if ($db->query('SELECT COUNT(*) FROM articles')->fetchColumn())
    exit("Database already contains news. Demo seeding skipped.\n");
$stories = [
    ['A new perspective on the city we call home', 'අපේ නගරය දෙස නව දැක්මක්', 'நம் நகரத்தைப் பற்றிய புதிய பார்வை', 'local', 'A walk through familiar streets reveals the people, places and everyday ideas that bring a community together.', 'city'],
    ['The small businesses shaping our neighbourhoods', 'අපේ ප්‍රදේශය හැඩගස්වන කුඩා ව්‍යාපාර', 'நம் சுற்றுப்புறத்தை வடிவமைக்கும் சிறு வணிகங்கள்', 'business', 'Meet the independent makers and shopkeepers building connections close to home.', 'market'],
    ['Beyond the scoreboard: a community comes together', 'ලකුණු පුවරුවෙන් ඔබ්බට එකමුතු ප්‍රජාවක්', 'விளையாட்டின் வழியே இணையும் சமூகம்', 'sports', 'A weekend on the field brings a new generation of players and supporters together.', 'sport'],
    ['A journey through the island’s green heart', 'දිවයිනේ හරිත හදවත හරහා ගමනක්', 'தீவின் பசுமை இதயத்தில் ஒரு பயணம்', 'local', 'Discover the trails, tea country and quiet moments that make the hill country special.', 'landscape'],
    ['Local ideas, a world of possibilities', 'දේශීය අදහස් සඳහා නව අවස්ථා', 'உள்ளூர் சிந்தனைகளுக்கான புதிய வாய்ப்புகள்', 'technology', 'Inside a shared workspace where students and makers turn their ideas into practical projects.', 'city'],
    ['The creative voices bringing new stories to life', 'නව කතා ජීවමාන කරන නිර්මාණශීලී හඬ', 'புதிய கதைகளுக்கு உயிரூட்டும் படைப்பாளிகள்', 'entertainment', 'Artists, photographers and storytellers find inspiration in everyday life.', 'market'],
    ['In pictures: the beauty in everyday places', 'ඡායාරූපවලින්: එදිනෙදා ස්ථානවල සුන්දරත්වය', 'படங்களில்: அன்றாட இடங்களின் அழகு', 'local', 'A visual notebook of landscapes and city streets from our sample gallery.', 'landscape'],
    ['Building a more connected community', 'වඩාත් සම්බන්ධිත ප්‍රජාවක් ගොඩනැගීම', 'இணைந்த சமூகத்தை உருவாக்குதல்', 'international', 'A sample editorial exploring how shared spaces help people meet and exchange ideas.', 'sport']
];
$media = [];
foreach (['city', 'market', 'sport', 'landscape'] as $scene) {
    $db->prepare('INSERT INTO media (uploader_id,file_name,file_path,file_type,alt_text,caption) VALUES (?,?,?,?,?,?)')->execute([$author, 'Demo ' . $scene, 'assets/demo-' . $scene . '.svg', 'image/svg+xml', 'Illustrative ' . $scene . ' artwork', 'Illustration for demonstration purposes']);
    $media[$scene] = (int) $db->lastInsertId();
}
foreach ($stories as $index => [$en, $si, $ta, $category, $summary, $scene]) {
    $cat = $db->prepare('SELECT id FROM categories WHERE slug=?');
    $cat->execute([$category]);
    $categoryId = $cat->fetchColumn();
    $data = ['author_id' => $author, 'category_id' => $categoryId, 'featured_image_id' => $media[$scene], 'title_en' => $en, 'title_si' => $si, 'title_ta' => $ta, 'slug' => 'sample-story-' . ($index + 1), 'summary_en' => $summary, 'summary_si' => 'මෙය වෙබ් අඩවියේ පෙනුම පෙන්වීම සඳහා සකස් කළ ආදර්ශ පුවතකි.', 'summary_ta' => 'இது வலைத்தளத்தின் தோற்றத்தைக் காட்டுவதற்கான மாதிரிச் செய்தி.', 'content_en' => '<p><strong>Demonstration story.</strong> This is illustrative sample content, not a report of an actual event.</p><p>' . $summary . '</p><h2>A place for every perspective</h2><p>The newsroom brings together stories in English, Sinhala and Tamil. This sample shows how an article, photographs and related stories are presented to readers.</p><blockquote>Every community has a story worth telling.</blockquote><p>Replace this content with verified reporting before publishing your website.</p>', 'content_si' => '<p><strong>ආදර්ශ පුවතකි.</strong> මෙය සැබෑ සිදුවීමක් පිළිබඳ වාර්තාවක් නොවේ.</p><p>මෙම පිටුව වෙබ් අඩවියේ සිංහල පුවත් පෙන්වන ආකාරය නිරූපණය කරයි.</p>', 'content_ta' => '<p><strong>மாதிரிச் செய்தி.</strong> இது உண்மைச் சம்பவம் பற்றிய செய்தி அல்ல.</p><p>இந்தப் பக்கம் தமிழ்ச் செய்திகள் எவ்வாறு காட்டப்படுகின்றன என்பதை விளக்குகிறது.</p>', 'status' => 'published', 'is_breaking' => $index < 2 ? 1 : 0, 'is_featured' => $index === 0 ? 1 : 0, 'published_at' => date('Y-m-d H:i:s', time() - $index * 3600), 'tags' => 'community, sample', 'gallery_ids' => $index === 6 ? json_encode(array_values($media)) : '[]'];
    $columns = implode(',', array_keys($data));
    $marks = implode(',', array_fill(0, count($data), '?'));
    $db->prepare("INSERT INTO articles ($columns) VALUES ($marks)")->execute(array_values($data));
}
$inWebApp = true;
require __DIR__ . '/demo-media.php';
require __DIR__ . '/demo-classifieds.php';
echo "Demo content created in the selected database. No production content changed.\n";
