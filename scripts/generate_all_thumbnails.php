<?php
$dir = __DIR__ . '/../public/assets/images/adaderana';
if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
}

function makeThumb($filename, $w, $h, $bgColor1, $bgColor2, $label, $subtext = '', $icon = '') {
    global $dir;
    $path = "$dir/$filename";
    $im = imagecreatetruecolor($w, $h);
    
    // Gradient fill
    for ($y = 0; $y < $h; $y++) {
        $r = (int)($bgColor1[0] + ($bgColor2[0] - $bgColor1[0]) * ($y / $h));
        $g = (int)($bgColor1[1] + ($bgColor2[1] - $bgColor1[1]) * ($y / $h));
        $b = (int)($bgColor1[2] + ($bgColor2[2] - $bgColor1[2]) * ($y / $h));
        $c = imagecolorallocate($im, $r, $g, $b);
        imageline($im, 0, $y, $w, $y, $c);
    }
    
    // Subtle vignette / photo framing
    $white = imagecolorallocate($im, 255, 255, 255);
    $light = imagecolorallocate($im, 240, 243, 246);
    $dark = imagecolorallocate($im, 15, 23, 42);
    $red = imagecolorallocate($im, 200, 16, 46);
    $gold = imagecolorallocate($im, 245, 158, 11);
    
    // Decorative visual elements: geometric camera/broadcast patterns
    imagesetthickness($im, 2);
    $accColor = imagecolorallocatealpha($im, 255, 255, 255, 80);
    imagerectangle($im, 4, 4, $w - 5, $h - 5, $accColor);
    
    if ($icon === 'play') {
        // Red play circle
        $cx = (int)($w / 2);
        $cy = (int)($h / 2);
        imagefilledellipse($im, $cx, $cy, 36, 36, $red);
        $tri = [
            $cx - 5, $cy - 8,
            $cx - 5, $cy + 8,
            $cx + 8, $cy
        ];
        imagefilledpolygon($im, $tri, $white);
    } elseif ($icon === 'audio') {
        $cx = (int)($w / 2);
        $cy = (int)($h / 2);
        imagefilledellipse($im, $cx, $cy, 28, 28, $red);
        // audio waveform bars
        for ($i = -2; $i <= 2; $i++) {
            $bh = 6 + (3 - abs($i)) * 4;
            imageline($im, $cx + $i * 4, $cy - (int)($bh/2), $cx + $i * 4, $cy + (int)($bh/2), $white);
        }
    }
    
    // Badge strip at bottom
    if ($label) {
        $band = imagecolorallocatealpha($im, 10, 15, 25, 30);
        imagefilledrectangle($im, 0, $h - 22, $w, $h, $band);
        // Truncate label if too long for gd built-in font
        $short = substr($label, 0, (int)($w / 8));
        imagestring($im, 2, 8, $h - 18, $short, $white);
    }
    
    imagejpeg($im, $path, 88);
    imagedestroy($im);
}

// Left column thumbnails (160x105)
$leftItems = [
    ['plane_chennai.jpg', [70, 130, 180], [20, 50, 90], 'SRILANKAN AIRLINES', '', ''],
    ['matara_court.jpg', [180, 140, 110], [90, 70, 50], 'MATARA COURT COMPLEX', '', ''],
    ['pm_welfare.jpg', [100, 110, 130], [40, 50, 70], 'PRIME MINISTER', '', ''],
    ['slpp_politician.jpg', [180, 130, 70], [100, 60, 30], 'SLPP GENERAL SECY', '', ''],
    ['catholic_bishops.jpg', [130, 120, 120], [60, 50, 60], 'CATHOLIC BISHOPS', '', ''],
    ['speaker_bills.jpg', [120, 90, 80], [50, 30, 30], 'SPEAKER PARLIAMENT', '', ''],
    ['customs_arecanut.jpg', [140, 60, 60], [70, 20, 20], 'CUSTOMS CONTRABAND', '', ''],
    ['trade_union.jpg', [80, 100, 120], [30, 45, 65], 'TRADE UNION COMMITTEE', '', ''],
    ['slfp_meeting.jpg', [110, 100, 80], [50, 45, 30], 'SLFP CENTRAL COMM', '', ''],
    ['cardamom_seized.jpg', [50, 110, 130], [20, 60, 80], 'SEIZED CARDAMOM', '', ''],
    ['fishermen_protest.jpg', [60, 120, 140], [30, 60, 80], 'MANNAR FISHERMEN', '', ''],
    ['navy_diver.jpg', [30, 80, 130], [10, 40, 80], 'SL NAVY DIVER', '', ''],
    ['president_projects.jpg', [90, 90, 110], [40, 40, 60], 'PRESIDENT ADDRESS', '', ''],
    ['cyber_scam.jpg', [100, 70, 80], [50, 30, 40], 'MYANMAR RESCUE', '', ''],
    ['road_accident.jpg', [140, 80, 70], [70, 30, 30], 'ACCIDENT PROBE', '', ''],
];

foreach ($leftItems as $item) {
    makeThumb($item[0], 160, 105, $item[1], $item[2], $item[3], '', $item[4]);
}

// Video of the day & Bulletins
makeThumb('video_opposition.jpg', 380, 215, [60, 70, 90], [20, 25, 40], 'OPPOSITION LEADER', '', 'play');
makeThumb('bulletin_12pm.jpg', 380, 215, [180, 20, 30], [60, 10, 15], 'DERANA 12:00 PM FIRST', '', 'play');
makeThumb('video_threewheeler.jpg', 380, 200, [40, 120, 90], [20, 60, 45], 'ELECTRIC THREE-WHEELER', '', 'play');

// AI Audio thumbnails (80x80)
$audioItems = [
    ['ai_audio_debt.jpg', [100, 120, 150], [40, 50, 80], 'DEBT', 'audio'],
    ['ai_audio_australia.jpg', [80, 140, 160], [30, 70, 90], 'AUST', 'audio'],
    ['ai_audio_shooting.jpg', [140, 60, 60], [70, 20, 20], 'ALERT', 'audio'],
    ['ai_audio_tax.jpg', [130, 110, 70], [65, 50, 30], 'TAX', 'audio'],
    ['ai_audio_excise.jpg', [120, 80, 100], [60, 35, 50], 'RAID', 'audio'],
];
foreach ($audioItems as $item) {
    makeThumb($item[0], 80, 80, $item[1], $item[2], $item[3], '', $item[4]);
}

// AI Summary thumbnails (80x80)
$summaryItems = [
    ['ai_summary_rehab.jpg', [70, 130, 110], [30, 65, 50], 'REHAB'],
    ['ai_summary_weather.jpg', [80, 110, 150], [35, 55, 90], 'RAIN'],
    ['ai_summary_fta.jpg', [140, 120, 70], [70, 60, 30], 'FTA'],
    ['ai_summary_cbsl.jpg', [120, 90, 110], [55, 40, 60], 'CBSL'],
    ['ai_summary_imf.jpg', [100, 100, 130], [45, 45, 75], 'IMF'],
];
foreach ($summaryItems as $item) {
    makeThumb($item[0], 80, 80, $item[1], $item[2], $item[3]);
}

// Video Stories list (120x80)
$videoStories = [
    ['video_julie_chung.jpg', 'JULIE CHUNG'],
    ['video_ranil_chamber.jpg', 'CHAMBER'],
    ['video_airport_security.jpg', 'BIA AIRPORT'],
    ['video_rice_mills.jpg', 'RICE MILLS'],
    ['video_slfp_dispute.jpg', 'SLFP LEADERSHIP'],
    ['video_teachers_protest.jpg', 'PROTEST MARCH'],
    ['video_traffic.jpg', 'COLOMBO TRAFFIC'],
    ['video_sltb_buses.jpg', 'SLTB BUSES'],
    ['video_visa_racket.jpg', 'VISA RACKET'],
    ['video_gold_price.jpg', 'GOLD MARKET'],
    ['video_train_coastal.jpg', 'COASTAL LINE'],
];
foreach ($videoStories as $vs) {
    makeThumb($vs[0], 120, 80, [80, 90, 110], [30, 35, 50], $vs[1], '', 'play');
}

// Sports compact items (120x80)
makeThumb('sports_relay.jpg', 120, 80, [150, 100, 50], [70, 40, 20], 'ASIAN RELAY');
makeThumb('sports_cricket_squad.jpg', 120, 80, [40, 130, 90], [15, 60, 40], 'SLC SQUAD');
makeThumb('sports_slc_logo.jpg', 120, 80, [30, 80, 150], [10, 35, 80], 'CRICKET BOARD');
makeThumb('sports_commonwealth.jpg', 120, 80, [140, 110, 40], [70, 50, 15], 'TRIALS');

// Sci/Tech grid (220x140)
makeThumb('tech_apple.jpg', 220, 140, [80, 80, 90], [25, 25, 30], 'APPLE SECURITY');
makeThumb('tech_robot.jpg', 220, 140, [120, 100, 160], [50, 35, 80], 'HUMANOID AI');
makeThumb('tech_spacex.jpg', 220, 140, [40, 60, 110], [15, 25, 55], 'SPACEX FALCON');
makeThumb('tech_openai.jpg', 220, 140, [30, 120, 130], [10, 55, 65], 'OPENAI GPT-4O');
makeThumb('tech_sony.jpg', 220, 140, [100, 100, 140], [40, 40, 70], 'SONY PSVR2');
makeThumb('tech_gemini.jpg', 220, 140, [80, 110, 180], [30, 45, 90], 'GOOGLE GEMINI');

// Entertainment grid (220x140)
makeThumb('ent_nolan.jpg', 220, 140, [130, 110, 90], [60, 45, 35], 'CHRIS NOLAN');
makeThumb('ent_dune.jpg', 220, 140, [160, 110, 60], [80, 50, 20], 'DUNE PART TWO');
makeThumb('ent_furiosa.jpg', 220, 140, [150, 70, 40], [75, 25, 15], 'FURIOSA');
makeThumb('ent_eminem.jpg', 220, 140, [90, 80, 90], [35, 30, 40], 'EMINEM');
makeThumb('ent_brad_f1.jpg', 220, 140, [140, 90, 50], [70, 40, 15], 'BRAD PITT F1');
makeThumb('ent_marvel.jpg', 220, 140, [120, 50, 60], [60, 20, 25], 'MARVEL STUDIOS');

// Other grid (220x140)
makeThumb('other_sbf.jpg', 220, 140, [110, 100, 110], [45, 40, 50], 'SBF TRIAL');
makeThumb('other_summit.jpg', 220, 140, [90, 100, 130], [35, 45, 65], 'DEFENCE SUMMIT');
makeThumb('other_bridge.jpg', 220, 140, [120, 110, 100], [55, 50, 45], 'OVERPASS PROBE');
makeThumb('other_airplanes.jpg', 220, 140, [60, 100, 140], [25, 45, 75], 'FLIGHT REROUTE');
makeThumb('other_protest_europe.jpg', 220, 140, [140, 80, 60], [65, 30, 20], 'EUROPE PROTESTS');
makeThumb('other_marathon.jpg', 220, 140, [80, 130, 90], [30, 65, 40], 'PEACE MARATHON');

echo "All thumbnails generated successfully.\n";
