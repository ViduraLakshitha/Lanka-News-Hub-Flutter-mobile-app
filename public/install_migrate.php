<?php
// Emergency Web-Based Database Migrator for cPanel / Web Deployments
// Once executed, delete this file or click the Delete button.

require_once dirname(__DIR__) . '/app/bootstrap.php';

$output = [];
$error = null;

try {
    $db = App\Core\Database::getInstance()->getConnection();
    
    // 1. Advertisements columns
    $advertisementCols = [
        'priority' => "INT(11) NOT NULL DEFAULT 1",
        'ad_format' => "VARCHAR(20) NOT NULL DEFAULT 'image'",
        'advertiser' => "VARCHAR(150) NULL",
        'ad_text' => "TEXT NULL",
        'cta_label' => "VARCHAR(80) NULL"
    ];
    
    if (DB_DRIVER === 'mysql') {
        $existingCols = array_column($db->query("SHOW COLUMNS FROM advertisements")->fetchAll(PDO::FETCH_ASSOC), 'Field');
        foreach ($advertisementCols as $col => $def) {
            if (!in_array($col, $existingCols, true)) {
                $db->exec("ALTER TABLE advertisements ADD COLUMN `$col` $def");
                $output[] = "Added column '$col' to 'advertisements' table.";
            } else {
                $output[] = "Column '$col' already exists in 'advertisements'.";
            }
        }
        
        // 2. Categories columns
        $catCols = array_column($db->query("SHOW COLUMNS FROM categories")->fetchAll(PDO::FETCH_ASSOC), 'Field');
        if (!in_array('name_ta', $catCols, true)) {
            $db->exec("ALTER TABLE categories ADD COLUMN `name_ta` VARCHAR(255) NOT NULL DEFAULT ''");
            $output[] = "Added 'name_ta' to 'categories' table.";
        }
        
        // 3. Articles columns
        $artCols = array_column($db->query("SHOW COLUMNS FROM articles")->fetchAll(PDO::FETCH_ASSOC), 'Field');
        foreach ([
            'title_ta' => "VARCHAR(255) NOT NULL DEFAULT ''",
            'summary_ta' => "TEXT NULL",
            'content_ta' => "LONGTEXT NULL",
            'tags' => "TEXT NULL",
            'gallery_ids' => "TEXT NULL",
            'video_url' => "VARCHAR(255) NULL",
            'video_media_id' => "INT(11) NULL"
        ] as $col => $def) {
            if (!in_array($col, $artCols, true)) {
                $db->exec("ALTER TABLE articles ADD COLUMN `$col` $def");
                $output[] = "Added column '$col' to 'articles' table.";
            }
        }
        
        // 4. Update status enum on articles
        $db->exec("ALTER TABLE articles MODIFY status ENUM('draft','pending','scheduled','published','archived') DEFAULT 'draft'");
        $output[] = "Updated status enum on 'articles' table to support 'scheduled'.";
    }

    // 5. Run full migration definitions
    $id = DB_DRIVER === 'mysql' ? 'INTEGER PRIMARY KEY AUTO_INCREMENT' : 'INTEGER PRIMARY KEY AUTOINCREMENT';
    $longText = DB_DRIVER === 'mysql' ? 'LONGTEXT' : 'TEXT';
    $tail = DB_DRIVER === 'mysql' ? ' ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci' : '';
    
    $tables = [
        'roles' => "id $id, name VARCHAR(50) NOT NULL, slug VARCHAR(50) NOT NULL UNIQUE",
        'article_revisions' => "id $id, article_id INTEGER NOT NULL, user_id INTEGER NOT NULL, snapshot $longText NOT NULL, created_at DATETIME NOT NULL",
        'classified_categories' => "id $id, name_en VARCHAR(255) NOT NULL, name_si VARCHAR(255) NOT NULL, name_ta VARCHAR(255) NOT NULL, slug VARCHAR(100) NOT NULL UNIQUE",
        'classifieds' => "id $id, user_id INTEGER NOT NULL, category_id INTEGER NOT NULL, language VARCHAR(2) NOT NULL DEFAULT 'en', title VARCHAR(200) NOT NULL, description TEXT NOT NULL, location VARCHAR(150) NOT NULL, price DECIMAL(12,2), contact_name VARCHAR(150) NOT NULL, contact_email VARCHAR(255) NOT NULL, contact_phone VARCHAR(40) NOT NULL, images TEXT NOT NULL, status VARCHAR(20) NOT NULL DEFAULT 'pending', moderation_note TEXT, premium_requested INTEGER NOT NULL DEFAULT 0, is_premium INTEGER NOT NULL DEFAULT 0, expires_at DATETIME, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL",
        'favorites' => "id $id, user_id INTEGER NOT NULL, classified_id INTEGER NOT NULL, UNIQUE(user_id, classified_id)",
        'payments' => "id $id, classified_id INTEGER NOT NULL, amount DECIMAL(12,2) NOT NULL, currency VARCHAR(3) NOT NULL DEFAULT 'LKR', reference VARCHAR(150) NOT NULL UNIQUE, status VARCHAR(20) NOT NULL, recorded_by INTEGER NOT NULL, created_at DATETIME NOT NULL",
        'menus' => "id $id, name_en VARCHAR(100) NOT NULL, name_si VARCHAR(100) NOT NULL, name_ta VARCHAR(100) NOT NULL, url VARCHAR(255) NOT NULL, order_index INTEGER NOT NULL DEFAULT 0, is_active INTEGER NOT NULL DEFAULT 1",
        'login_attempts' => "id $id, bucket VARCHAR(64) NOT NULL, attempted_at DATETIME NOT NULL",
        'schema_versions' => "id $id, version VARCHAR(100) NOT NULL UNIQUE, applied_at DATETIME NOT NULL",
    ];

    foreach ($tables as $table => $columns) {
        $db->exec("CREATE TABLE IF NOT EXISTS $table ($columns)$tail");
        $output[] = "Verified table '$table'.";
    }

    // Seed roles
    $roles = [[1,'Administrator','super_admin'],[2,'Editor','editor'],[3,'Journalist','reporter'],[4,'Member','member']];
    foreach ($roles as $row) {
        $s = $db->prepare('SELECT id FROM roles WHERE slug=?');
        $s->execute([$row[2]]);
        if (!$s->fetch()) {
            $db->prepare('INSERT INTO roles (id,name,slug) VALUES (?,?,?)')->execute($row);
            $output[] = "Seeded role '{$row[1]}'.";
        }
    }

    // Seed classified categories
    $classifiedCats = [
        ['Property','දේපළ','சொத்துகள்','property'], ['Jobs','රැකියා','வேலைகள்','jobs'], ['Vehicles','වාහන','வாகනங்கள்','vehicles'],
        ['Business Promotions','ව්‍යාපාර ප්‍රවර්ධන','வணிக விளம்பரங்கள்','business'], ['Matrimonial','මංගල දැන්වීම්','திருමணம்','matrimonial'],
        ['Services','සේවා','சேவைகள்','services'], ['Community Notices','ප්‍රජා නිවේදන','சමூக அறிவிப்புகள்','community']
    ];
    foreach ($classifiedCats as $row) {
        $s = $db->prepare('SELECT id FROM classified_categories WHERE slug=?');
        $s->execute([$row[3]]);
        if (!$s->fetch()) {
            $db->prepare('INSERT INTO classified_categories (name_en,name_si,name_ta,slug) VALUES (?,?,?,?)')->execute($row);
            $output[] = "Seeded classified category '{$row[0]}'.";
        }
    }

    // Seed news categories if empty
    $newsCategories = [
        ['Local','දේශීය','உள்ளூர்','local'], ['Politics','දේශපාලන','அரசியல்','politics'], ['Business','ව්‍යාපාර','வணிகம்','business'], ['Sports','ක්‍රීඩා','විලையாட்டு','sports'],
        ['Entertainment','විනෝදාස්වාද','பொழுதுபோக்கு','entertainment'], ['Technology','තාක්ෂණය','தொழில்நுட்பம்','technology'], ['International','විදෙස්','சர்வதேசம்','international']
    ];
    foreach ($newsCategories as $index => $row) {
        $s = $db->prepare('SELECT id FROM categories WHERE slug=?');
        $s->execute([$row[3]]);
        if (!$s->fetch()) {
            $db->prepare('INSERT INTO categories (name_en,name_si,name_ta,slug,order_index,status) VALUES (?,?,?,?,?,?)')->execute([...$row, $index + 1, 'active']);
            $output[] = "Seeded news category '{$row[0]}'.";
        }
    }

    // Flush cache
    \App\Core\Cache::flush();
    $output[] = "Application caches flushed.";

    if (isset($_POST['delete_self'])) {
        @unlink(__FILE__);
        header('Location: /');
        exit;
    }

} catch (\Throwable $ex) {
    $error = $ex->getMessage();
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Database Migration · Lanka News Hub</title>
<style>
body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #0f172a; color: #f8fafc; padding: 2rem; margin: 0; }
.card { max-width: 680px; margin: 2rem auto; background: #1e293b; border-radius: 12px; padding: 2rem; border: 1px solid #334155; box-shadow: 0 10px 25px rgba(0,0,0,0.3); }
h1 { font-size: 1.5rem; margin-top: 0; color: #38bdf8; }
ul { list-style: none; padding: 0; }
li { padding: 0.5rem 0.75rem; margin-bottom: 0.25rem; background: #0f172a; border-radius: 6px; font-size: 0.9rem; font-family: monospace; color: #a7f3d0; }
.error { background: #7f1d1d; color: #fecaca; padding: 1rem; border-radius: 6px; font-family: monospace; }
.btn { display: inline-block; background: #0284c7; color: #fff; padding: 0.75rem 1.5rem; text-decoration: none; border-radius: 6px; font-weight: 600; border: none; cursor: pointer; margin-right: 0.5rem; }
.btn-danger { background: #dc2626; }
.actions { margin-top: 1.5rem; display: flex; gap: 1rem; }
</style>
</head>
<body>
<div class="card">
  <h1>Database Migration Status</h1>
  <?php if ($error): ?>
    <div class="error"><strong>Error:</strong> <?= htmlspecialchars($error) ?></div>
  <?php else: ?>
    <p>Database has been updated and synchronized with all required tables, columns, and indexes.</p>
    <ul>
      <?php foreach ($output as $line): ?>
        <li>✓ <?= htmlspecialchars($line) ?></li>
      <?php endforeach ?>
    </ul>
    <div class="actions">
      <a href="/" class="btn">Go to Homepage →</a>
      <form method="post" style="display:inline">
        <button type="submit" name="delete_self" value="1" class="btn btn-danger">Delete This Migrator & Exit</button>
      </form>
    </div>
  <?php endif ?>
</div>
</body>
</html>
