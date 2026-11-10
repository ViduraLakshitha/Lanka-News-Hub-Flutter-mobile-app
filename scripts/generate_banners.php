<?php
$dir = __DIR__ . '/../public/assets/images/adaderana';
if (!is_dir($dir)) mkdir($dir, 0755, true);

// 1. Leaderboard strip: Metro (350x85)
$im = imagecreatetruecolor(380, 85);
$redDark = imagecolorallocate($im, 180, 16, 26);
$redBright = imagecolorallocate($im, 220, 20, 35);
$white = imagecolorallocate($im, 255, 255, 255);
$black = imagecolorallocate($im, 0, 0, 0);
$yellow = imagecolorallocate($im, 255, 220, 0);
imagefilledrectangle($im, 0, 0, 380, 85, $redDark);
imagefilledrectangle($im, 8, 8, 120, 77, $white);
imagestring($im, 5, 20, 30, 'METRO', $black);
imagestring($im, 2, 135, 18, 'CONVERT ART INTO', $white);
imagestring($im, 4, 135, 34, 'GAME DEVELOPMENT', $yellow);
imagestring($im, 3, 135, 54, 'REGISTER NOW >>', $white);
imagejpeg($im, "$dir/banner_metro.jpg", 90);
imagedestroy($im);

// 2. Leaderboard: NLB Lotteries (380x85)
$im = imagecreatetruecolor(380, 85);
$bg = imagecolorallocate($im, 250, 250, 250);
$red = imagecolorallocate($im, 200, 16, 26);
$blue = imagecolorallocate($im, 20, 80, 160);
$dark = imagecolorallocate($im, 30, 30, 30);
imagefilledrectangle($im, 0, 0, 380, 85, $bg);
imagerectangle($im, 0, 0, 379, 84, imagecolorallocate($im, 220, 220, 220));
imagefilledellipse($im, 50, 42, 60, 60, $red);
imagestring($im, 4, 34, 34, 'NLB', $white);
imagestring($im, 4, 100, 22, 'NATIONAL LOTTERIES BOARD', $blue);
imagestring($im, 3, 100, 44, 'MEGA JACKPOT OVER RS. 100M', $red);
imagejpeg($im, "$dir/banner_lottery.jpg", 90);
imagedestroy($im);

// 3. Leaderboard: Vallibel Finance (380x85)
$im = imagecreatetruecolor(380, 85);
$redBg = imagecolorallocate($im, 175, 15, 22);
$gold = imagecolorallocate($im, 245, 180, 0);
imagefilledrectangle($im, 0, 0, 380, 85, $redBg);
imagestring($im, 3, 20, 18, 'VALLIBEL FINANCE PLC', $gold);
imagestring($im, 5, 20, 36, 'HOTLINE: 011 7 400 400', $white);
imagestring($im, 2, 20, 58, 'FIXED DEPOSITS & LEASING SOLUTIONS', $white);
imagejpeg($im, "$dir/banner_vallibel.jpg", 90);
imagedestroy($im);

// 4. Commercial Bank (320x95)
$im = imagecreatetruecolor(320, 95);
$cBlue = imagecolorallocate($im, 0, 70, 140);
imagefilledrectangle($im, 0, 0, 320, 95, $cBlue);
imagestring($im, 4, 18, 16, 'COMMERCIAL BANK', $white);
imagestring($im, 3, 18, 38, 'COMBANK Q+ PAYMENT APP', $yellow);
imagestring($im, 2, 18, 60, 'Bank on the go with zero fees', $white);
imagejpeg($im, "$dir/banner_combank.jpg", 90);
imagedestroy($im);

// 5. Telecom Green Banner (320x85)
$im = imagecreatetruecolor(320, 85);
$tGreen = imagecolorallocate($im, 15, 120, 60);
imagefilledrectangle($im, 0, 0, 320, 85, $tGreen);
imagestring($im, 4, 16, 18, 'SLT-MOBITEL 5G', $white);
imagestring($im, 3, 16, 38, 'Experience The Green Network', $yellow);
imagestring($im, 2, 16, 56, 'Unlimited Data Packages Available', $white);
imagejpeg($im, "$dir/banner_mobitel.jpg", 90);
imagedestroy($im);

// 6. Supermarket / Retail (320x85)
$im = imagecreatetruecolor(320, 85);
$sBg = imagecolorallocate($im, 245, 240, 220);
$sDark = imagecolorallocate($im, 50, 40, 20);
$sRed = imagecolorallocate($im, 190, 20, 20);
$sGreen = imagecolorallocate($im, 20, 140, 50);
imagefilledrectangle($im, 0, 0, 320, 85, $sBg);
imagerectangle($im, 0, 0, 319, 84, imagecolorallocate($im, 220, 210, 180));
imagestring($im, 4, 18, 16, 'KEELLS / CARGILLS FOOD CITY', $sRed);
imagestring($im, 3, 18, 38, 'Festive Savings & Special Discounts', $sGreen);
imagestring($im, 2, 18, 56, 'Up to 25% OFF on credit cards', $sDark);
imagejpeg($im, "$dir/banner_retail.jpg", 90);
imagedestroy($im);

echo "All banners generated.\n";
