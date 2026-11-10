<?php
require_once dirname(__DIR__) . '/app/bootstrap.php';
$sessionDirectory = dirname(__DIR__) . '/storage/sessions';
if (!is_dir($sessionDirectory)) mkdir($sessionDirectory, 0700, true);
session_save_path($sessionDirectory);
ini_set('session.use_strict_mode', '1');
$sessionLifetime = defined('SESSION_LIFETIME') ? SESSION_LIFETIME : 86400;
ini_set('session.gc_maxlifetime', (string)$sessionLifetime);
$isActualHttps = (!empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off')
    || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443)
    || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower((string)$_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https')
    || (isset($_SERVER['HTTP_X_FORWARDED_SSL']) && strtolower((string)$_SERVER['HTTP_X_FORWARDED_SSL']) === 'on');

$clientHost = strtolower(explode(':', $_SERVER['HTTP_HOST'] ?? '')[0]);
$isLocal = in_array($clientHost, ['127.0.0.1', 'localhost', '::1'], true);

$secureCookieEnv = $_ENV['SESSION_SECURE_COOKIE'] ?? null;
if ($secureCookieEnv !== null) {
    $isSecureCookie = filter_var($secureCookieEnv, FILTER_VALIDATE_BOOLEAN) && !$isLocal;
} else {
    $isSecureCookie = $isActualHttps && !$isLocal;
}

$cookieDomain = \App\Helpers\DomainHelper::sessionCookieDomain();
$cookieParams = [
    'lifetime' => $sessionLifetime,
    'httponly' => true,
    'samesite' => 'Lax',
    'secure' => $isSecureCookie,
    'path' => '/'
];
if ($cookieDomain) {
    $cookieParams['domain'] = $cookieDomain;
}
session_set_cookie_params($cookieParams);
session_start();
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('X-XSS-Protection: 0');
header('Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=()');
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://pagead2.googlesyndication.com https://www.googletagmanager.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com data:; img-src 'self' data: https:; media-src 'self' https: data:; frame-src 'self' https://www.youtube.com https://www.youtube-nocookie.com https://googleads.g.doubleclick.net https://pagead2.googlesyndication.com; connect-src 'self' https://www.google-analytics.com; object-src 'none'; base-uri 'self'; form-action 'self'");
if ($isActualHttps) {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
}
$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$isGuest = empty($_SESSION['user_id']);
$isPublicGet = ($_SERVER['REQUEST_METHOD'] ?? '') === 'GET' && !str_starts_with($requestPath, '/admin') && !str_starts_with($requestPath, '/account');
if ($isGuest && $isPublicGet) {
    header('Cache-Control: public, max-age=60, stale-while-revalidate=120');
} else {
    header('Cache-Control: no-store, private');
}
$_SESSION['csrf'] ??= bin2hex(random_bytes(32));
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !str_starts_with($requestPath, '/api/') && !hash_equals($_SESSION['csrf'], (string)($_POST['_token'] ?? ''))) {
    http_response_code(419); exit('Your form expired. Reload the page and try again.');
}
\App\Helpers\DomainHelper::detectLanguage();
try {
    ob_start();
    $router = new App\Core\Router();
    require dirname(__DIR__) . '/app/routes.php';
    $router->resolve();
    ob_end_flush();
} catch (Throwable $exception) {
    while (ob_get_level() > 0) ob_end_clean();
    App\Core\Logger::error((string)$exception, ['uri' => $_SERVER['REQUEST_URI'] ?? '/']);
    http_response_code(500);
    if (defined('APP_DEBUG') && APP_DEBUG) {
        echo '<h1>Application Error</h1><pre>' . htmlspecialchars((string)$exception, ENT_QUOTES, 'UTF-8') . '</pre>';
    } else {
        echo '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>500 - Server Error</title><style>body{font-family:sans-serif;padding:40px;text-align:center;background:#f8f9fa;color:#212529}h1{font-size:2rem;color:#b91c1c}p{color:#6c757d}</style></head><body><h1>Service Unavailable</h1><p>We could not complete this request at this time. Please try again shortly.</p></body></html>';
    }
}
