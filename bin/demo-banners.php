<?php
if (PHP_SAPI!=='cli') exit;
require dirname(__DIR__).'/app/bootstrap.php';
if (APP_ENV!=='demo') exit("Only available in the demo environment.\n");
$db=App\Core\Database::getInstance()->getConnection();
foreach ([['Demo: leaderboard','header_728x90','leaderboard'],['Demo: learning','top_left','learning'],['Demo: community','top_right','community'],['Demo: sidebar','sidebar_300x250','sidebar']] as [$title,$position,$file]) {
    $existing=$db->prepare('SELECT id FROM advertisements WHERE title=?');$existing->execute([$title]);
    if ($existing->fetch()) continue;
    $db->prepare('INSERT INTO advertisements (title,position,image_path,url,is_active) VALUES (?,?,?,?,1)')->execute([$title,$position,'assets/banner-'.$file.'.svg',APP_URL.'/classifieds']);
}
echo "Sample banners are ready. Edit or pause them in Banner publishing.\n";
