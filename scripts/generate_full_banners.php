<?php
$dir = __DIR__ . '/../public/assets/images/adaderana';
if (!is_dir($dir)) mkdir($dir, 0755, true);

$w = 1220;
$h = 85;

// =========================================================================
// ROW 1: NLB Lotteries (Left) + Credit Rating (Center) + Vallibel Finance (Right)
// =========================================================================
$im1 = imagecreatetruecolor($w, $h);

// Background
$white = imagecolorallocate($im1, 255, 255, 255);
$redDark = imagecolorallocate($im1, 160, 14, 20);
$redBright = imagecolorallocate($im1, 204, 22, 28);
$blue = imagecolorallocate($im1, 14, 76, 146);
$dark = imagecolorallocate($im1, 25, 25, 25);
$gold = imagecolorallocate($im1, 230, 175, 0);
$greyBorder = imagecolorallocate($im1, 220, 224, 230);
$lightBg = imagecolorallocate($im1, 248, 250, 252);

// Fill overall white
imagefilledrectangle($im1, 0, 0, $w, $h, $white);

// Left section: NLB Lotteries (0 to 450)
imagefilledrectangle($im1, 0, 0, 450, $h, $lightBg);
// Lottery circular balls
$ballColors = [
    [220, 38, 38], [37, 99, 235], [234, 179, 8], [22, 163, 74]
];
for ($i = 0; $i < 4; $i++) {
    $bx = 35 + $i * 38;
    $bc = imagecolorallocate($im1, $ballColors[$i][0], $ballColors[$i][1], $ballColors[$i][2]);
    imagefilledellipse($im1, $bx, 42, 32, 32, $bc);
    $numStr = (string)(($i + 1) * 7);
    imagestring($im1, 3, $bx - 6, 35, $numStr, $white);
}
imagestring($im1, 4, 195, 22, 'NATIONAL LOTTERIES BOARD', $blue);
imagestring($im1, 3, 195, 42, 'SUPER MEGA JACKPOT RS. 120M+', $redBright);

// Middle divider
imageline($im1, 450, 0, 450, $h, $greyBorder);

// Middle section: Credit Rating (451 to 720)
imagefilledrectangle($im1, 451, 0, 720, $h, $white);
// A+ rating box
imagefilledrectangle($im1, 475, 18, 545, 68, $redBright);
imagestring($im1, 5, 495, 26, 'A+', $white);
imagestring($im1, 2, 485, 48, 'FITCH RATING', $white);

imagestring($im1, 3, 560, 26, 'STABLE OUTLOOK', $dark);
imagestring($im1, 2, 560, 44, 'Fitch Ratings Lanka Ltd', imagecolorallocate($im1, 100, 100, 100));

// Right section: Vallibel Finance (721 to 1220) - Solid Red background
imagefilledrectangle($im1, 721, 0, $w, $h, $redDark);
imagestring($im1, 5, 750, 20, 'CALL: 011 7 400 400', $white);
imagestring($im1, 4, 750, 44, 'VALLIBEL FINANCE PLC', $gold);
imagestring($im1, 2, 750, 62, 'Fixed Deposits | Leasing | Gold Loans', $white);

// Border
imagerectangle($im1, 0, 0, $w - 1, $h - 1, $greyBorder);
imagejpeg($im1, "$dir/banner_strip_row1.jpg", 95);
imagedestroy($im1);


// =========================================================================
// ROW 2: Metro Game Dev (Left) + Child Protection (Center) + Sponsor (Right)
// =========================================================================
$im2 = imagecreatetruecolor($w, $h);

imagefilledrectangle($im2, 0, 0, $w, $h, $white);

// Left section: Metro Game Dev (0 to 450) - Dark Maroon / Red
imagefilledrectangle($im2, 0, 0, 450, $h, $redDark);
// White box for METRO
imagefilledrectangle($im2, 18, 14, 130, 70, $white);
imagestring($im2, 5, 36, 32, 'METRO', $dark);
imagestring($im2, 2, 148, 20, 'CONVERT ART INTO', $white);
imagestring($im2, 4, 148, 36, 'GAME DEVELOPMENT', $gold);
imagestring($im2, 3, 148, 54, 'REGISTER NOW >>', $white);

// Middle section: Child Protection (451 to 820)
imagefilledrectangle($im2, 451, 0, 820, $h, $lightBg);
imageline($im2, 450, 0, 450, $h, $greyBorder);
imageline($im2, 820, 0, 820, $h, $greyBorder);

// Red heart emblem
imagefilledellipse($im2, 490, 42, 40, 40, $redBright);
imagestring($im2, 5, 484, 34, '<3', $white);
imagestring($im2, 4, 525, 24, 'PUTTING CHILDREN FIRST', $redDark);
imagestring($im2, 2, 525, 44, 'National Child Protection Authority - Call 1929', $dark);

// Right section: Corporate Sponsor (821 to 1220)
imagefilledrectangle($im2, 821, 0, $w, $h, $white);
imagestring($im2, 4, 850, 22, 'COMMERCIAL FINANCIAL CORP', $blue);
imagestring($im2, 3, 850, 42, 'Empowering Your Future Today', $dark);
imagestring($im2, 2, 850, 60, 'Visit www.finance.lk for special rates', imagecolorallocate($im2, 120, 120, 120));

// Border
imagerectangle($im2, 0, 0, $w - 1, $h - 1, $greyBorder);
imagejpeg($im2, "$dir/banner_strip_row2.jpg", 95);
imagedestroy($im2);

echo "Full banner strips row 1 and row 2 generated successfully.\n";
