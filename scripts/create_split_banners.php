<?php
$sourcePath = __DIR__ . '/../public/assets/images/news/banner_strip_row2.jpg';
$outDir = __DIR__ . '/../public/assets/images/news';

if (!file_exists($sourcePath)) {
    die("Source banner not found.\n");
}

$src = imagecreatefromjpeg($sourcePath);
$w = imagesx($src);
$h = imagesy($src);
$halfW = (int)($w / 2);

// Left banner (606 x 85)
$left = imagecreatetruecolor($halfW, $h);
imagecopy($left, $src, 0, 0, 0, 0, $halfW, $h);
imagejpeg($left, $outDir . '/banner_top_left.jpg', 95);
imagedestroy($left);

// Right banner (614 x 85)
$rightW = $w - $halfW;
$right = imagecreatetruecolor($rightW, $h);
imagecopy($right, $src, 0, 0, $halfW, 0, $rightW, $h);
imagejpeg($right, $outDir . '/banner_top_right.jpg', 95);
imagedestroy($right);

imagedestroy($src);

echo "Created banner_top_left.jpg ({$halfW}x{$h}) and banner_top_right.jpg ({$rightW}x{$h})\n";

// Also copy banners to public/uploads/ads/ so if DB references uploads/ads, it exists!
$adsDir = __DIR__ . '/../public/uploads/ads';
if (!is_dir($adsDir)) {
    mkdir($adsDir, 0777, true);
}
copy($outDir . '/banner_strip_row1.jpg', $adsDir . '/ad_header_1200x100.png');
copy($outDir . '/banner_top_left.jpg', $adsDir . '/ad_top_left_590x100.png');
copy($outDir . '/banner_top_right.jpg', $adsDir . '/ad_top_right_590x100.png');
echo "Updated public/uploads/ads/ with high-res full banner images!\n";
