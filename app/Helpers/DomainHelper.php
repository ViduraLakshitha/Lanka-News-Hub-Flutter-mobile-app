<?php
namespace App\Helpers;

class DomainHelper
{
    public const MAIN_DOMAIN = 'news.liontech.lk';
    public const SINHALA_DOMAIN = 'sinhala.news.liontech.lk';
    public const TAMIL_DOMAIN = 'tamil.news.liontech.lk';

    public const EDITIONS = [
        'en' => [
            'code' => 'en',
            'name' => 'English',
            'label' => 'English',
            'subdomain' => 'news',
            'domain' => self::MAIN_DOMAIN,
            'color' => '#c4161c'
        ],
        'si' => [
            'code' => 'si',
            'name' => 'සිංහල',
            'label' => 'සිංහල (Sinhala)',
            'subdomain' => 'sinhala',
            'domain' => self::SINHALA_DOMAIN,
            'color' => '#8b0d11'
        ],
        'ta' => [
            'code' => 'ta',
            'name' => 'தமிழ்',
            'label' => 'தமிழ் (Tamil)',
            'subdomain' => 'tamil',
            'domain' => self::TAMIL_DOMAIN,
            'color' => '#b91c1c'
        ]
    ];

    /**
     * Detects active language based on subdomain or fallback.
     */
    public static function detectLanguage(): string
    {
        $host = strtolower($_SERVER['HTTP_HOST'] ?? '');
        $cleanHost = explode(':', $host)[0];

        // 1. Direct subdomain matching (production & local subdomains)
        if (str_starts_with($cleanHost, 'sinhala.')) {
            $_SESSION['language'] = 'si';
            return 'si';
        }

        if (str_starts_with($cleanHost, 'tamil.')) {
            $_SESSION['language'] = 'ta';
            return 'ta';
        }

        if (str_starts_with($cleanHost, 'news.') || $cleanHost === 'news.liontech.lk') {
            if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'si', 'ta'], true)) {
                $_SESSION['language'] = $_GET['lang'];
                return $_GET['lang'];
            }
            $_SESSION['language'] = 'en';
            return 'en';
        }

        // 3. Fallback to existing session language if valid, else English
        if (!empty($_SESSION['language']) && in_array($_SESSION['language'], ['en', 'si', 'ta'], true)) {
            return $_SESSION['language'];
        }

        $_SESSION['language'] = 'en';
        return 'en';
    }

    /**
     * Returns the current detected subdomain slug ('sinhala', 'tamil', or 'news').
     */
    public static function currentSubdomain(): string
    {
        $lang = self::detectLanguage();
        return self::EDITIONS[$lang]['subdomain'] ?? 'news';
    }

    /**
     * Checks if current request is served from a recognized news subdomain.
    /**
     * Checks whether separate subdomain routing is active.
     * Can be set to false via .env (SUBDOMAINS_ENABLED=false) during early development on news.liontech.lk
     * before DNS records for nested subdomains (sinhala.news.liontech.lk) are created.
     */
    public static function subdomainsEnabled(): bool
    {
        $val = $_ENV['SUBDOMAINS_ENABLED'] ?? ($_SERVER['SUBDOMAINS_ENABLED'] ?? null);
        if ($val !== null && $val !== '') {
            return filter_var($val, FILTER_VALIDATE_BOOLEAN);
        }
        return true;
    }

    /**
     * Checks if current request is served from a recognized news subdomain.
     */
    public static function isProductionHost(): bool
    {
        $host = strtolower($_SERVER['HTTP_HOST'] ?? '');
        return str_contains($host, 'liontech.lk');
    }

    /**
     * Generates target URL for a given language edition.
     * In production (or liontech.lk), generates absolute URL to the respective subdomain:
     * - 'en' -> https://news.liontech.lk/...
     * - 'si' -> https://sinhala.news.liontech.lk/...
     * - 'ta' -> https://tamil.news.liontech.lk/...
     * In local development, generates subdomain or query param fallback.
     */
    public static function urlForLanguage(string $lang, ?string $path = null, array $query = []): string
    {
        if (!isset(self::EDITIONS[$lang])) {
            $lang = 'en';
        }

        // Determine path and current query
        if ($path === null) {
            $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        }
        $cleanPath = ltrim($path, '/');

        // Check if on production or liontech.lk host
        $host = strtolower($_SERVER['HTTP_HOST'] ?? '');
        $isHttps = (!empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off')
            || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443)
            || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower((string)$_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https');
        $scheme = $isHttps ? 'https://' : 'http://';

        if (self::isProductionHost()) {
            if (!self::subdomainsEnabled()) {
                // While developing on single subdomain news.liontech.lk without DNS records yet:
                $currentHost = explode(':', $host)[0];
                $mergedQuery = array_merge($_GET, $query, ['lang' => $lang]);
                unset($mergedQuery['page']);
                $url = $scheme . $currentHost . ($cleanPath !== '' ? '/' . $cleanPath : '');
                return $url . '?' . http_build_query($mergedQuery);
            }

            $targetDomain = self::EDITIONS[$lang]['domain'];
            $fullUrl = $scheme . $targetDomain . ($cleanPath !== '' ? '/' . $cleanPath : '');
            if ($query) {
                $fullUrl .= '?' . http_build_query($query);
            }
            return $fullUrl;
        }

        // Local development: check if host already uses a subdomain (e.g. sinhala.localhost:8000)
        $port = isset($_SERVER['SERVER_PORT']) && !in_array((int)$_SERVER['SERVER_PORT'], [80, 443], true) ? ':' . $_SERVER['SERVER_PORT'] : '';
        $cleanHost = explode(':', $host)[0];

        if (str_ends_with($cleanHost, '.localhost') || str_ends_with($cleanHost, '.test')) {
            $baseLocal = preg_replace('/^(sinhala|tamil|news)\./', '', $cleanHost);
            $sub = self::EDITIONS[$lang]['subdomain'];
            $targetHost = ($sub === 'news' ? 'news.' : $sub . '.') . $baseLocal;
            $fullUrl = $scheme . $targetHost . $port . ($cleanPath !== '' ? '/' . $cleanPath : '');
            if ($query) {
                $fullUrl .= '?' . http_build_query($query);
            }
            return $fullUrl;
        }

        // Fallback for IP / localhost dev without subdomain DNS: use query param ?lang=
        $mergedQuery = array_merge($_GET, $query, ['lang' => $lang]);
        unset($mergedQuery['page']);
        $baseUrl = url($cleanPath);
        return $baseUrl . '?' . http_build_query($mergedQuery);
    }

    /**
     * Returns the session cookie domain for sharing login sessions across subdomains.
     */
    public static function sessionCookieDomain(): ?string
    {
        $host = strtolower($_SERVER['HTTP_HOST'] ?? '');
        $cleanHost = explode(':', $host)[0];
        if (str_ends_with($cleanHost, 'news.liontech.lk')) {
            return '.news.liontech.lk';
        }
        if (str_ends_with($cleanHost, 'liontech.lk')) {
            return '.liontech.lk';
        }
        if (str_ends_with($cleanHost, '.localhost')) {
            return '.localhost';
        }
        return null;
    }

    /**
     * Resolves the allowed languages for a user.
     * Administrators and Editors can write and manage all languages.
     * Reporters/writers write the languages assigned by the administrator.
     */
    public static function userLanguages(?array $user): array
    {
        if (!$user) {
            return ['en', 'si', 'ta'];
        }

        $role = $user['role'] ?? ($user['role_slug'] ?? 'reporter');
        if (in_array($role, ['super_admin', 'editor'], true)) {
            return ['en', 'si', 'ta'];
        }

        $raw = trim((string)($user['allowed_languages'] ?? ''));
        if ($raw === '') {
            return ['en', 'si', 'ta'];
        }

        // Can be comma-separated or JSON
        if (str_starts_with($raw, '[')) {
            $parsed = json_decode($raw, true);
            $langs = is_array($parsed) ? $parsed : [];
        } else {
            $langs = array_map('trim', explode(',', $raw));
        }

        $filtered = array_values(array_intersect($langs, ['en', 'si', 'ta']));
        return $filtered ?: ['en'];
    }

    /**
     * Checks if a user has permission to write in a given language.
     */
    public static function canWriteLanguage(array $user, string $lang): bool
    {
        return in_array($lang, self::userLanguages($user), true);
    }

    /**
     * Returns an array of human-readable language badges for admin UI.
     */
    public static function formatLanguageBadges(string $allowedLanguagesStr): array
    {
        $raw = trim($allowedLanguagesStr);
        if ($raw === '' || $raw === 'en,si,ta') {
            return [
                ['code' => 'all', 'label' => 'All Editions', 'class' => 'badge-all', 'subdomain' => '*.news.liontech.lk']
            ];
        }

        $langs = array_map('trim', explode(',', $raw));
        $badges = [];
        foreach ($langs as $code) {
            if (isset(self::EDITIONS[$code])) {
                $badges[] = [
                    'code' => $code,
                    'label' => self::EDITIONS[$code]['name'],
                    'domain' => self::EDITIONS[$code]['domain'],
                    'subdomain' => self::EDITIONS[$code]['subdomain'] . '.'
                ];
            }
        }
        return $badges ?: [['code' => 'en', 'label' => 'English', 'domain' => self::MAIN_DOMAIN, 'subdomain' => 'news.']];
    }
}
