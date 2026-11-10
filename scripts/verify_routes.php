<?php
$urls = [
    '/',
    '/?lang=en',
    '/?lang=si',
    '/?lang=ta',
    '/category/business',
    '/category/sports',
    '/category/technology',
    '/category/entertainment',
    '/edition/latest',
    '/edition/breaking',
    '/edition/videos',
    '/edition/gallery',
    '/classifieds',
    '/search?q=Sri+Lanka',
    '/feed',
];

$failed = 0;
foreach ($urls as $path) {
    $ch = curl_init('http://127.0.0.1:8080' . $path);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Host: news.liontech.lk', 'X-Forwarded-Proto: https']);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $html = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($code === 200) {
        // check that 127.0.0.1 does not appear in links
        $hasLocalhost = str_contains($html, '127.0.0.1');
        $hasDeranaText = stripos($html, 'adaderana.lk') !== false || stripos($html, 'ada derana') !== false;
        $status = $hasLocalhost ? 'WARN (found 127.0.0.1)' : ($hasDeranaText ? 'WARN (found derana)' : 'PASS');
        echo sprintf("[%-4s] (HTTP %d) %s\n", $status, $code, $path);
        if ($hasLocalhost || $hasDeranaText) $failed++;
    } else {
        echo sprintf("[FAIL] (HTTP %d) %s\n", $code, $path);
        $failed++;
    }
}

if ($failed === 0) {
    echo "\nALL ROUTES PASSED PERFECTLY!\n";
} else {
    echo "\n$failed ROUTES HAD ISSUES!\n";
}
