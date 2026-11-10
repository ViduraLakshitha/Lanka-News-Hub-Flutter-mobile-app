<?php
$newsDir = __DIR__ . '/../public/assets/images/news';
$adsDir = __DIR__ . '/../public/uploads/ads';
if (!is_dir($newsDir)) mkdir($newsDir, 0755, true);
if (!is_dir($adsDir)) mkdir($adsDir, 0755, true);

// 1. FULL ROW 1: 1220 x 85
$w = 1220; $h = 85;
$im1 = imagecreatetruecolor($w, $h);
$white = imagecolorallocate($im1, 255, 255, 255);
$redDark = imagecolorallocate($im1, 160, 14, 20);
$redBright = imagecolorallocate($im1, 204, 22, 28);
$blue = imagecolorallocate($im1, 14, 76, 146);
$dark = imagecolorallocate($im1, 25, 25, 25);
$gold = imagecolorallocate($im1, 230, 175, 0);
$greyBorder = imagecolorallocate($im1, 220, 224, 230);
$lightBg = imagecolorallocate($im1, 248, 250, 252);
$muted = imagecolorallocate($im1, 110, 110, 110);

imagefilledrectangle($im1, 0, 0, $w, $h, $white);
imagefilledrectangle($im1, 0, 0, 450, $h, $lightBg);

$ballColors = [[220, 38, 38], [37, 99, 235], [234, 179, 8], [22, 163, 74]];
for ($i = 0; $i < 4; $i++) {
    $bx = 35 + $i * 38;
    $bc = imagecolorallocate($im1, $ballColors[$i][0], $ballColors[$i][1], $ballColors[$i][2]);
    imagefilledellipse($im1, $bx, 42, 32, 32, $bc);
    $numStr = (string)(($i + 1) * 7);
    imagestring($im1, 3, $bx - 6, 35, $numStr, $white);
}
imagestring($im1, 4, 195, 22, 'NATIONAL LOTTERIES BOARD', $blue);
imagestring($im1, 3, 195, 42, 'SUPER MEGA JACKPOT RS. 120M+', $redBright);

imageline($im1, 450, 0, 450, $h, $greyBorder);
imagefilledrectangle($im1, 451, 0, 720, $h, $white);
imagefilledrectangle($im1, 475, 16, 545, 68, $redBright);
imagestring($im1, 5, 495, 24, 'A+', $white);
imagestring($im1, 2, 482, 46, 'FITCH RATING', $white);
imagestring($im1, 3, 560, 24, 'STABLE OUTLOOK', $dark);
imagestring($im1, 2, 560, 44, 'Fitch Ratings Lanka Ltd', $muted);

imagefilledrectangle($im1, 721, 0, $w, $h, $redDark);
imagestring($im1, 5, 750, 18, 'CALL: 011 7 400 400', $white);
imagestring($im1, 4, 750, 42, 'VALLIBEL FINANCE PLC', $gold);
imagestring($im1, 2, 750, 60, 'Fixed Deposits | Leasing | Gold Loans', $white);

imagejpeg($im1, $newsDir . '/banner_strip_row1.jpg', 96);
imagepng($im1, $adsDir . '/ad_header_1200x100.png');
imagepng($im1, $adsDir . '/ad_footer_1200x100.png');
imagedestroy($im1);

// 2. FULL ROW 2: 1220 x 85
$im2 = imagecreatetruecolor($w, $h);
$white = imagecolorallocate($im2, 255, 255, 255);
$redDark = imagecolorallocate($im2, 140, 12, 18);
$redBright = imagecolorallocate($im2, 190, 18, 24);
$blue = imagecolorallocate($im2, 16, 80, 150);
$dark = imagecolorallocate($im2, 25, 25, 25);
$orange = imagecolorallocate($im2, 245, 130, 32);
$greyBorder = imagecolorallocate($im2, 220, 224, 230);
$lightBg = imagecolorallocate($im2, 246, 248, 250);
$muted = imagecolorallocate($im2, 100, 100, 100);

imagefilledrectangle($im2, 0, 0, $w, $h, $white);

// Left: Metro Game Dev (0 to 450)
imagefilledrectangle($im2, 0, 0, 450, $h, $redDark);
imagefilledrectangle($im2, 16, 16, 120, 68, $white);
imagestring($im2, 5, 32, 34, 'METRO', $dark);
imagestring($im2, 3, 140, 20, 'CONVERT ART INTO', $white);
imagestring($im2, 4, 140, 38, 'GAME DEVELOPMENT', $orange);
imagestring($im2, 4, 140, 56, 'REGISTER NOW >>', $white);

// Center: NCPA (451 to 800)
imagefilledrectangle($im2, 451, 0, 800, $h, $lightBg);
imageline($im2, 450, 0, 450, $h, $greyBorder);
imagefilledellipse($im2, 485, 42, 36, 36, $redBright);
imagestring($im2, 4, 477, 34, '<3', $white);
imagestring($im2, 4, 520, 24, 'PUTTING CHILDREN FIRST', $redDark);
imagestring($im2, 3, 520, 46, 'National Child Protection Authority - Call 1929', $dark);

// Right: Commercial Financial (801 to 1220)
imageline($im2, 800, 0, 800, $h, $greyBorder);
imagefilledrectangle($im2, 801, 0, $w, $h, $white);
imagestring($im2, 4, 830, 20, 'COMMERCIAL FINANCIAL CORP', $blue);
imagestring($im2, 4, 830, 38, 'Empowering Your Future Today', $dark);
imagestring($im2, 3, 830, 56, 'Visit www.finance.lk for special rates', $muted);

imagejpeg($im2, $newsDir . '/banner_strip_row2.jpg', 96);
imagedestroy($im2);

// 3. DUAL BANNER LEFT: 590 x 85 (Metro Game Dev + Callout)
$imL = imagecreatetruecolor(590, $h);
$white = imagecolorallocate($imL, 255, 255, 255);
$redDark = imagecolorallocate($imL, 140, 12, 18);
$orange = imagecolorallocate($imL, 245, 130, 32);
$dark = imagecolorallocate($imL, 25, 25, 25);
$gold = imagecolorallocate($imL, 255, 215, 0);

imagefilledrectangle($imL, 0, 0, 590, $h, $redDark);
imagefilledrectangle($imL, 16, 16, 125, 68, $white);
imagestring($imL, 5, 34, 34, 'METRO', $dark);
imagestring($imL, 3, 145, 18, 'CONVERT ART INTO CAREER', $white);
imagestring($imL, 4, 145, 36, 'ADVANCED GAME DEVELOPMENT', $orange);
imagestring($imL, 4, 145, 54, 'REGISTER NOW: WWW.METRO.LK >>', $gold);
imagejpeg($imL, $newsDir . '/banner_top_left.jpg', 96);
imagepng($imL, $adsDir . '/ad_top_left_590x100.png');
imagedestroy($imL);

// 4. DUAL BANNER RIGHT: 590 x 85 (National Lotteries + NCPA)
$imR = imagecreatetruecolor(590, $h);
$white = imagecolorallocate($imR, 255, 255, 255);
$lightBg = imagecolorallocate($imR, 248, 250, 252);
$redBright = imagecolorallocate($imR, 204, 22, 28);
$redDark = imagecolorallocate($imR, 140, 12, 18);
$blue = imagecolorallocate($imR, 16, 80, 150);
$dark = imagecolorallocate($imR, 25, 25, 25);
$greyBorder = imagecolorallocate($imR, 220, 224, 230);
$muted = imagecolorallocate($imR, 100, 100, 100);

imagefilledrectangle($imR, 0, 0, 590, $h, $lightBg);
imagerectangle($imR, 0, 0, 589, 84, $greyBorder);
imagefilledellipse($imR, 38, 42, 38, 38, $redBright);
imagestring($imR, 4, 30, 34, '<3', $white);
imagestring($imR, 4, 75, 18, 'PUTTING CHILDREN FIRST', $redDark);
imagestring($imR, 3, 75, 36, 'National Child Protection Authority - Call 1929', $dark);
imagestring($imR, 3, 75, 54, 'Care, Helpline, Counseling & Immediate Support', $blue);
imagejpeg($imR, $newsDir . '/banner_top_right.jpg', 96);
imagepng($imR, $adsDir . '/ad_top_right_590x100.png');
imagedestroy($imR);

echo "All banners generated successfully in news/ and uploads/ads/!\n";
