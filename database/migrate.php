<?php
// Idempotent, additive migration. Run only from the CLI.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once dirname(__DIR__) . '/app/bootstrap.php';
$db = App\Core\Database::getInstance()->getConnection();
$activeDriver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);
if ($activeDriver === 'sqlite' && !is_dir(dirname(DB_PATH))) mkdir(dirname(DB_PATH), 0750, true);
if ($activeDriver === 'mysql') {
    $db->exec(file_get_contents(__DIR__ . '/schema.sql'));
} else {
    $db->exec(<<<'SQL'
CREATE TABLE IF NOT EXISTS roles (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT NOT NULL, slug TEXT NOT NULL UNIQUE);
CREATE TABLE IF NOT EXISTS users (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT NOT NULL, email TEXT NOT NULL UNIQUE, password TEXT NOT NULL, role_id INTEGER NOT NULL REFERENCES roles(id), status TEXT DEFAULT 'active', allowed_languages TEXT DEFAULT 'en,si,ta', created_at TEXT DEFAULT CURRENT_TIMESTAMP, updated_at TEXT DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE IF NOT EXISTS categories (id INTEGER PRIMARY KEY AUTOINCREMENT, parent_id INTEGER REFERENCES categories(id) ON DELETE SET NULL, name_si TEXT NOT NULL, name_en TEXT NOT NULL, slug TEXT NOT NULL UNIQUE, color TEXT, icon TEXT, status TEXT DEFAULT 'active', order_index INTEGER DEFAULT 0);
CREATE TABLE IF NOT EXISTS media (id INTEGER PRIMARY KEY AUTOINCREMENT, uploader_id INTEGER NOT NULL REFERENCES users(id), file_name TEXT NOT NULL, file_path TEXT NOT NULL, file_type TEXT NOT NULL, alt_text TEXT, caption TEXT, created_at TEXT DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE IF NOT EXISTS articles (id INTEGER PRIMARY KEY AUTOINCREMENT, author_id INTEGER NOT NULL REFERENCES users(id), category_id INTEGER NOT NULL REFERENCES categories(id), featured_image_id INTEGER REFERENCES media(id) ON DELETE SET NULL, title_si TEXT NOT NULL, title_en TEXT, slug TEXT NOT NULL UNIQUE, summary_si TEXT, summary_en TEXT, content_si TEXT, content_en TEXT, status TEXT DEFAULT 'draft', is_breaking INTEGER DEFAULT 0, is_featured INTEGER DEFAULT 0, priority INTEGER DEFAULT 0, views INTEGER DEFAULT 0, published_at TEXT, created_at TEXT DEFAULT CURRENT_TIMESTAMP, updated_at TEXT DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE IF NOT EXISTS advertisements (id INTEGER PRIMARY KEY AUTOINCREMENT, title TEXT NOT NULL, position TEXT NOT NULL, ad_code TEXT, image_path TEXT, url TEXT, start_date TEXT, end_date TEXT, is_active INTEGER DEFAULT 1);
CREATE TABLE IF NOT EXISTS settings (id INTEGER PRIMARY KEY AUTOINCREMENT, setting_key TEXT NOT NULL UNIQUE, setting_value TEXT);
CREATE TABLE IF NOT EXISTS activity_logs (id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER REFERENCES users(id) ON DELETE SET NULL, action TEXT NOT NULL, entity TEXT, entity_id INTEGER, ip_address TEXT, created_at TEXT DEFAULT CURRENT_TIMESTAMP);
SQL);
}
$id = $activeDriver === 'mysql' ? 'INTEGER PRIMARY KEY AUTO_INCREMENT' : 'INTEGER PRIMARY KEY AUTOINCREMENT';
$longText = $activeDriver === 'mysql' ? 'LONGTEXT' : 'TEXT';
$tail = $activeDriver === 'mysql' ? ' ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci' : '';
$tables = [
    'article_revisions'=>"id $id, article_id INTEGER NOT NULL, user_id INTEGER NOT NULL, snapshot $longText NOT NULL, created_at DATETIME NOT NULL",
    'classified_categories'=>"id $id, name_en VARCHAR(255) NOT NULL, name_si VARCHAR(255) NOT NULL, name_ta VARCHAR(255) NOT NULL, slug VARCHAR(100) NOT NULL UNIQUE",
    'classifieds'=>"id $id, user_id INTEGER NOT NULL, category_id INTEGER NOT NULL, language VARCHAR(2) NOT NULL, title VARCHAR(200) NOT NULL, description TEXT NOT NULL, location VARCHAR(150) NOT NULL, price DECIMAL(12,2), contact_name VARCHAR(150) NOT NULL, contact_email VARCHAR(255) NOT NULL, contact_phone VARCHAR(40) NOT NULL, images TEXT NOT NULL, status VARCHAR(20) NOT NULL DEFAULT 'pending', moderation_note TEXT, premium_requested INTEGER NOT NULL DEFAULT 0, is_premium INTEGER NOT NULL DEFAULT 0, expires_at DATETIME, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL",
    'favorites'=>"id $id, user_id INTEGER NOT NULL, classified_id INTEGER NOT NULL, UNIQUE(user_id, classified_id)",
    'payments'=>"id $id, classified_id INTEGER NOT NULL, amount DECIMAL(12,2) NOT NULL, currency VARCHAR(3) NOT NULL DEFAULT 'LKR', reference VARCHAR(150) NOT NULL UNIQUE, status VARCHAR(20) NOT NULL, recorded_by INTEGER NOT NULL, created_at DATETIME NOT NULL",
    'menus'=>"id $id, name_en VARCHAR(100) NOT NULL, name_si VARCHAR(100) NOT NULL, name_ta VARCHAR(100) NOT NULL, url VARCHAR(255) NOT NULL, order_index INTEGER NOT NULL DEFAULT 0, is_active INTEGER NOT NULL DEFAULT 1",
    'login_attempts'=>"id $id, bucket VARCHAR(64) NOT NULL, attempted_at DATETIME NOT NULL",
    'api_tokens'=>"id $id, user_id INTEGER NOT NULL, token VARCHAR(128) NOT NULL UNIQUE, expires_at DATETIME, created_at DATETIME NOT NULL",
    'saved_articles'=>"id $id, user_id INTEGER NOT NULL, article_id INTEGER NOT NULL, created_at DATETIME NOT NULL, UNIQUE(user_id, article_id)",
    'device_tokens'=>"id $id, user_id INTEGER, token VARCHAR(255) NOT NULL UNIQUE, platform VARCHAR(20) NOT NULL, topics TEXT, updated_at DATETIME NOT NULL",
    'notification_preferences'=>"id $id, user_id INTEGER NOT NULL UNIQUE, breaking_news INTEGER NOT NULL DEFAULT 1, categories TEXT, ad_updates INTEGER NOT NULL DEFAULT 1, promotions INTEGER NOT NULL DEFAULT 1, updated_at DATETIME NOT NULL",
    'schema_versions'=>"id $id, version VARCHAR(100) NOT NULL UNIQUE, applied_at DATETIME NOT NULL",
];
foreach ($tables as $table=>$columns) $db->exec("CREATE TABLE IF NOT EXISTS $table ($columns)$tail");
function addColumn($db, $table, $name, $type) {
    global $activeDriver;
    $columns = $activeDriver === 'mysql' ? array_column($db->query("SHOW COLUMNS FROM $table")->fetchAll(), 'Field') : array_column($db->query("PRAGMA table_info($table)")->fetchAll(), 'name');
    if (!in_array($name, $columns, true)) $db->exec("ALTER TABLE $table ADD COLUMN $name $type");
}
addColumn($db, 'categories', 'name_ta', "VARCHAR(255) NOT NULL DEFAULT ''");
addColumn($db, 'users', 'allowed_languages', "VARCHAR(50) NOT NULL DEFAULT 'en,si,ta'");
foreach (['title_ta'=>'VARCHAR(255)', 'summary_ta'=>'TEXT', 'content_ta'=>$longText, 'tags'=>'TEXT', 'gallery_ids'=>'TEXT', 'video_url'=>'VARCHAR(255)', 'video_media_id'=>'INTEGER'] as $name=>$type) addColumn($db, 'articles', $name, $type);
foreach (['ad_format'=>"VARCHAR(20) NOT NULL DEFAULT 'image'", 'advertiser'=>'VARCHAR(150)', 'ad_text'=>'TEXT', 'cta_label'=>'VARCHAR(80)', 'priority'=>'INTEGER NOT NULL DEFAULT 1'] as $name=>$type) addColumn($db, 'advertisements', $name, $type);
$db->exec("UPDATE advertisements SET ad_format='html' WHERE ad_format='image' AND ad_code IS NOT NULL AND TRIM(ad_code)<>''");
$s = $db->prepare('SELECT id FROM settings WHERE setting_key=?'); $s->execute(['adsense_id']);
if (!$s->fetch()) $db->prepare('INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)')->execute(['adsense_id', '']);
$version = $db->prepare('SELECT id FROM schema_versions WHERE version = ?'); $version->execute(['web-1']);
if (!$version->fetch()) {
    if (DB_DRIVER === 'mysql') $db->exec("ALTER TABLE articles MODIFY status ENUM('draft','pending','scheduled','published','archived') DEFAULT 'draft'");
    foreach (['articles_public'=>'articles(status,published_at,category_id)', 'classifieds_public'=>'classifieds(status,expires_at,category_id)', 'revision_article'=>'article_revisions(article_id)', 'login_bucket'=>'login_attempts(bucket,attempted_at)'] as $name=>$target) {
        $table=strstr($target,'(',true);
        $existing=DB_DRIVER==='mysql' ? array_column($db->query("SHOW INDEX FROM $table")->fetchAll(),'Key_name') : array_column($db->query("PRAGMA index_list($table)")->fetchAll(),'name');
        if (!in_array($name,$existing,true)) $db->exec("CREATE INDEX $name ON $target");
    }
    $db->prepare('INSERT INTO schema_versions (version,applied_at) VALUES (?,?)')->execute(['web-1', date('Y-m-d H:i:s')]);
}
$perfVersion = $db->prepare('SELECT id FROM schema_versions WHERE version = ?'); $perfVersion->execute(['web-perf-1']);
if (!$perfVersion->fetch()) {
    $perfIndexes = [
        'idx_articles_breaking' => 'articles(status, is_breaking, published_at)',
        'idx_articles_featured' => 'articles(status, is_featured, published_at)',
        'idx_articles_author' => 'articles(author_id, status)',
        'idx_articles_category' => 'articles(category_id, status, published_at)',
        'idx_ads_lookup' => 'advertisements(is_active, position, priority)',
        'idx_classifieds_user' => 'classifieds(user_id, status)',
        'idx_classifieds_lookup' => 'classifieds(status, language, expires_at, is_premium, created_at)',
        'idx_media_type' => 'media(file_type)',
        'idx_logs_user_date' => 'activity_logs(user_id, created_at)',
        'idx_payments_classified' => 'payments(classified_id, status)',
    ];
    foreach ($perfIndexes as $name => $target) {
        $table = strstr($target, '(', true);
        $existing = DB_DRIVER === 'mysql' ? array_column($db->query("SHOW INDEX FROM $table")->fetchAll(), 'Key_name') : array_column($db->query("PRAGMA index_list($table)")->fetchAll(), 'name');
        if (!in_array($name, $existing, true)) $db->exec("CREATE INDEX $name ON $target");
    }
    $db->prepare('INSERT INTO schema_versions (version,applied_at) VALUES (?,?)')->execute(['web-perf-1', date('Y-m-d H:i:s')]);
}
$roles = [[1,'Administrator','super_admin'],[2,'Editor','editor'],[3,'Journalist','reporter'],[4,'Member','member']];
foreach ($roles as $row) { $s=$db->prepare('SELECT id FROM roles WHERE slug=?'); $s->execute([$row[2]]); if (!$s->fetch()) $db->prepare('INSERT INTO roles (id,name,slug) VALUES (?,?,?)')->execute($row); }
$categories = [
 ['Property','දේපළ','சொத்துகள்','property'], ['Jobs','රැකියා','வேலைகள்','jobs'], ['Vehicles','වාහන','வாகனங்கள்','vehicles'],
 ['Business Promotions','ව්‍යාපාර ප්‍රවර්ධන','வணிக விளம்பரங்கள்','business'], ['Matrimonial','මංගල දැන්වීම්','திருமணம்','matrimonial'],
 ['Services','සේවා','சேவைகள்','services'], ['Community Notices','ප්‍රජා නිවේදන','சமூக அறிவிப்புகள்','community']
];
foreach ($categories as $row) { $s=$db->prepare('SELECT id FROM classified_categories WHERE slug=?'); $s->execute([$row[3]]); if (!$s->fetch()) $db->prepare('INSERT INTO classified_categories (name_en,name_si,name_ta,slug) VALUES (?,?,?,?)')->execute($row); }
$newsCategories = [
 ['Local','දේශීය','உள்ளூர்','local'], ['Politics','දේශපාලන','அரசியல்','politics'], ['Business','ව්‍යාපාර','வணிகம்','business'], ['Sports','ක්‍රීඩා','விளையாட்டு','sports'],
 ['Entertainment','විනෝදාස්වාද','பொழுதுபோக்கு','entertainment'], ['Technology','තාක්ෂණය','தொழில்நுட்பம்','technology'], ['International','විදෙස්','சர்வதேசம்','international']
];
foreach ($newsCategories as $index=>$row) {
    $s=$db->prepare('SELECT id FROM categories WHERE slug=?'); $s->execute([$row[3]]);
    if (!$s->fetch()) $db->prepare('INSERT INTO categories (name_en,name_si,name_ta,slug,order_index,status) VALUES (?,?,?,?,?,?)')->execute([...$row,$index+1,'active']);
}
foreach (['site_name'=>'Lanka News Hub','contact_email'=>'','language_en'=>'1','language_si'=>'1','language_ta'=>'1','ga_id'=>'','gtm_id'=>''] as $key=>$value) {
    $s=$db->prepare('SELECT id FROM settings WHERE setting_key=?'); $s->execute([$key]);
    if (!$s->fetch()) $db->prepare('INSERT INTO settings (setting_key,setting_value) VALUES (?,?)')->execute([$key,$value]);
}
echo "Web migration complete. Existing content preserved.\n";
