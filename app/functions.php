<?php
if (!function_exists('str_starts_with')) {
    function str_starts_with(string $haystack, string $needle): bool {
        return (string)$needle === '' || strncmp($haystack, $needle, strlen($needle)) === 0;
    }
}
if (!function_exists('str_ends_with')) {
    function str_ends_with(string $haystack, string $needle): bool {
        return (string)$needle === '' || $needle === substr($haystack, -strlen($needle));
    }
}
if (!function_exists('str_contains')) {
    function str_contains(string $haystack, string $needle): bool {
        return (string)$needle === '' || strpos($haystack, $needle) !== false;
    }
}
function e($value): string { return htmlspecialchars((string)($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function url(string $path = '', array $query = []): string {
    if (preg_match('~^https?://~i', $path)) {
        return $path . ($query ? (str_contains($path, '?') ? '&' : '?') . http_build_query($query) : '');
    }
    $base = defined('APP_URL') ? APP_URL : '';
    $clean = ltrim($path, '/');
    $res = $clean === '' ? $base : $base . '/' . $clean;
    return $res . ($query ? '?' . http_build_query($query) : '');
}

function lang(): string { 
    return \App\Helpers\DomainHelper::detectLanguage(); 
}
function edition_url(string $lang, ?string $path = null, array $query = []): string {
    return \App\Helpers\DomainHelper::urlForLanguage($lang, $path, $query);
}
function user_languages(?array $user): array {
    return \App\Helpers\DomainHelper::userLanguages($user);
}
function localized(array $row, string $field): string { return (string)($row[$field . '_' . lang()] ?? ''); }
function t(string $key): string {
    $words = [
        'Latest news'=>['si'=>'නවතම පුවත්','ta'=>'சமீபத்திய செய்திகள்'],
        'Breaking news'=>['si'=>'උණුසුම් පුවත්','ta'=>'முக்கிய செய்திகள்'],
        'Videos'=>['si'=>'වීඩියෝ','ta'=>'காணொளிகள்'],
        'Photo gallery'=>['si'=>'ඡායාරූප','ta'=>'புகைப்படங்கள்'],
        'Classifieds'=>['si'=>'ලුහුඬු දැන්වීම්','ta'=>'வகைப்படுத்தப்பட்ட விளம்பரங்கள்'],
        'Search'=>['si'=>'සොයන්න','ta'=>'தேடுக'],
        'My account'=>['si'=>'මගේ ගිණුම','ta'=>'என் கணக்கு'],
        'Read story'=>['si'=>'පුවත කියවන්න','ta'=>'செய்தியைப் படிக்கவும்'],
        'No stories yet'=>['si'=>'තවම පුවත් නොමැත','ta'=>'இதுவரை செய்திகள் இல்லை'],
        'More stories'=>['si'=>'තවත් පුවත්','ta'=>'மேலும் செய்திகள்'],
        'Spotlight reports'=>['si'=>'විශේෂ වාර්තා','ta'=>'சிறப்பு அறிக்கைகள்'],
        'All updates'=>['si'=>'සියලු යාවත්කාලීන','ta'=>'அனைத்து புதுப்பிப்புகளும்'],
        'Visual stories'=>['si'=>'දෘශ්‍ය පුවත්','ta'=>'காட்சி கதைகள்'],
        'Live wire'=>['si'=>'සජීවී පුවත්','ta'=>'நேரலை செய்திகள்'],
        'Latest updates'=>['si'=>'නවතම තොරතුරු','ta'=>'சமீபத்திய தகவல்கள்'],
        'Share this story'=>['si'=>'මෙම පුවත බෙදාගන්න','ta'=>'இச்செய்தியைப் பகிர்க'],
        'Copy link'=>['si'=>'සබැඳිය පිටපත් කරන්න','ta'=>'இணைப்பை நகலெடு'],
        'Post a classified'=>['si'=>'දැන්වීමක් පළ කරන්න','ta'=>'விளம்பரம் இடுக'],
        'Home'=>['si'=>'මුල් පිටුව','ta'=>'முகப்பு'],
        'Story'=>['si'=>'පුවත','ta'=>'செய்தி'],
        'By'=>['si'=>'කර්තෘ','ta'=>'ஆசிரியர்'],
        'Published'=>['si'=>'පළ කළේ','ta'=>'வெளியிடப்பட்டது'],
        'Updated'=>['si'=>'යාවත්කාලීන කළේ','ta'=>'புதுப்பிக்கப்பட்டது'],
        'More in this section'=>['si'=>'මෙම අංශයේ තවත් පුවත්','ta'=>'இப்பிரிவில் மேலும் செய்திகள்'],
        'Explore the desks'=>['si'=>'අංශ ගවේෂණය කරන්න','ta'=>'துறைகளை ஆராய்க'],
        'Chronological news stream'=>['si'=>'කාලානුක්‍රමික පුවත් ධාරාව','ta'=>'காலவரிசை செய்தி ஓட்டம்'],
        'Category sections'=>['si'=>'පුවත් අංශ','ta'=>'செய்திப் பிரிவுகள்'],
        'Browse video news'=>['si'=>'වීඩියෝ පුවත් නරඹන්න','ta'=>'காணொளிச் செய்திகளைப் பார்க்கவும்'],
    ];
    return $words[$key][lang()] ?? $key;
}
if (!function_exists('env')) {
    function env(string $key, $default = null) {
        $val = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
        if ($val === false || $val === null || $val === '') {
            return $default;
        }
        return match (strtolower((string)$val)) {
            'true', '(true)' => true,
            'false', '(false)' => false,
            'null', '(null)' => null,
            'empty', '(empty)' => '',
            default => $val,
        };
    }
}
function csrf(): string {
    $_SESSION['csrf'] ??= bin2hex(random_bytes(32));
    return '<input type="hidden" name="_token" value="' . e($_SESSION['csrf']) . '">';
}
function flash(string $message): void { $_SESSION['flash'] = $message; }
function setting(string $key, ?string $default = ''): string {
    static $settings;
    if ($settings === null) {
        $settings = \App\Core\Cache::remember('portal_settings', 600, function() {
            return array_column(\App\Core\Database::getInstance()->getConnection()->query('SELECT setting_key, setting_value FROM settings')->fetchAll(), 'setting_value', 'setting_key');
        });
    }
    return (string)($settings[$key] ?? $default ?? '');
}
function media_url(?string $path): string {
    if (!$path) return url('assets/news-placeholder.svg');
    if (preg_match('~^https?://~i', $path)) return $path;
    return url(ltrim($path, '/'));
}
function safe_link(string $value, bool $internal = false): string {
    if ($internal && preg_match('~^/[a-zA-Z0-9/_?=&%.-]*$~D', $value) && !str_starts_with($value, '//')) return $value;
    return filter_var($value, FILTER_VALIDATE_URL) && in_array(strtolower(parse_url($value, PHP_URL_SCHEME) ?? ''), ['http','https'], true) ? $value : '';
}
function rich_html(string $html): string {
    if ($html === '') return '';
    $doc = new DOMDocument();
    $prior = libxml_use_internal_errors(true);
    $doc->loadHTML('<?xml encoding="utf-8" ?><div>' . $html . '</div>', LIBXML_HTML_NODEFDTD | LIBXML_NONET);
    libxml_clear_errors(); libxml_use_internal_errors($prior);
    $allowed = ['p','br','strong','b','em','i','u','h2','h3','ul','ol','li','blockquote','a'];
    $clean = function ($node) use (&$clean, $allowed) {
        foreach (iterator_to_array($node->childNodes) as $child) {
            if ($child instanceof DOMElement) {
                if (!in_array(strtolower($child->tagName), $allowed, true)) {
                    if (in_array(strtolower($child->tagName), ['script','style','iframe','object','svg','math','template'], true)) { $node->removeChild($child); continue; }
                    $clean($child);
                    while ($child->firstChild) $node->insertBefore($child->firstChild, $child);
                    $node->removeChild($child); continue;
                }
                $href = $child->tagName === 'a' ? safe_link($child->getAttribute('href')) : '';
                foreach (iterator_to_array($child->attributes) as $attribute) $child->removeAttribute($attribute->name);
                if ($href) { $child->setAttribute('href', $href); $child->setAttribute('rel', 'noopener noreferrer'); }
                $clean($child);
            } elseif ($child->nodeType !== XML_TEXT_NODE) { $node->removeChild($child); }
        }
    };
    $body = $doc->getElementsByTagName('body')->item(0);
    $clean($body);
    $out = ''; foreach ($body->childNodes as $node) $out .= $doc->saveHTML($node);
    return $out;
}
function youtube_id(string $input): string {
    if (!$input) return '';
    $parts = parse_url($input);
    if (!$parts || !in_array(strtolower($parts['host'] ?? ''), ['youtube.com','www.youtube.com','youtu.be','www.youtube-nocookie.com'], true)) return '';
    parse_str($parts['query'] ?? '', $query);
    $id = ($parts['host'] === 'youtu.be') ? trim($parts['path'] ?? '', '/') : ($query['v'] ?? basename($parts['path'] ?? ''));
    return preg_match('/^[\w-]{11}$/D', $id) ? $id : '';
}

function time_ago(string $datetime): string {
    $time = strtotime($datetime);
    if (!$time) return '';
    $diff = time() - $time;
    if ($diff < 60) return 'Just now';
    if ($diff < 3600) return floor($diff / 60) . 'm ago';
    if ($diff < 86400) return floor($diff / 3600) . 'h ago';
    if ($diff < 604800) return floor($diff / 86400) . 'd ago';
    return date('d M Y', $time);
}

