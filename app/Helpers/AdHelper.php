<?php
namespace App\Helpers;
use App\Core\Database;
class AdHelper {
    public static function placements(): array {
        return [
            'header_728x90' => 'Top leaderboard · 1200 × 100 or 728 × 90',
            'top_left' => 'Top left · 590 × 100',
            'top_right' => 'Top right · 590 × 100',
            'sidebar_300x250' => 'Sidebar · 300 × 250',
            'sidebar_300x600' => 'Sidebar tower · 300 × 600',
            'homepage_mid_728x90' => 'Between news sections · 728 × 90',
            'inline_feed' => 'Inside news feed · Native / Leaderboard',
            'feed_left' => 'News feed left · 590 × 100',
            'feed_right' => 'News feed right · 590 × 100',
            'footer' => 'Footer banner · 1200 × 100 or 728 × 90',
            'in_article' => 'Inside article · In-read banner',
            'sticky' => 'Sticky bottom banner · Responsive',
            'popup' => 'Pop-up overlay · Interstitial modal'
        ];
    }

    public static function placementSpecs(): array {
        return [
            'header_728x90' => [
                'name' => 'Top Leaderboard Banner',
                'width' => 1200,
                'height' => 100,
                'dimensions' => '1200 × 100 px (or 728 × 90)',
                'aspect' => '12:1 / 8:1',
                'description' => 'Header bar above site navigation. High visibility across all pages.',
                'image' => 'uploads/ads/ad_header_1200x100.png'
            ],
            'top_left' => [
                'name' => 'Top Left Dual Banner',
                'width' => 590,
                'height' => 100,
                'dimensions' => '590 × 100 px',
                'aspect' => '5.9:1',
                'description' => 'Top left half-strip in dual-banner header row.',
                'image' => 'uploads/ads/ad_top_left_590x100.png'
            ],
            'top_right' => [
                'name' => 'Top Right Dual Banner',
                'width' => 590,
                'height' => 100,
                'dimensions' => '590 × 100 px',
                'aspect' => '5.9:1',
                'description' => 'Top right half-strip in dual-banner header row.',
                'image' => 'uploads/ads/ad_top_right_590x100.png'
            ],
            'feed_left' => [
                'name' => 'News Feed Left Dual Banner',
                'width' => 590,
                'height' => 100,
                'dimensions' => '590 × 100 px',
                'aspect' => '5.9:1',
                'description' => 'Left dual banner in news feed & article reading streams.',
                'image' => 'assets/images/news/banner_feed_left.jpg'
            ],
            'feed_right' => [
                'name' => 'News Feed Right Dual Banner',
                'width' => 590,
                'height' => 100,
                'dimensions' => '590 × 100 px',
                'aspect' => '5.9:1',
                'description' => 'Right dual banner in news feed & article reading streams.',
                'image' => 'assets/images/news/banner_feed_right.jpg'
            ],
            'homepage_mid_728x90' => [
                'name' => 'Between Sections Divider',
                'width' => 728,
                'height' => 90,
                'dimensions' => '728 × 90 px',
                'aspect' => '8.08:1',
                'description' => 'Mid-page horizontal separator between homepage news desks.',
                'image' => 'uploads/ads/ad_between_sections_728x90.png'
            ],
            'inline_feed' => [
                'name' => 'Inside News Feed Banner',
                'width' => 728,
                'height' => 90,
                'dimensions' => '728 × 90 px',
                'aspect' => '8.08:1',
                'description' => 'Embedded within the homepage latest news stream.',
                'image' => 'uploads/ads/ad_inline_feed_728x90.png'
            ],
            'in_article' => [
                'name' => 'In-Article Reading Banner',
                'width' => 800,
                'height' => 250,
                'dimensions' => '800 × 250 px',
                'aspect' => '3.2:1',
                'description' => 'Placed between article body paragraphs for maximum engaged readership.',
                'image' => 'uploads/ads/ad_in_article_800x250.png'
            ],
            'sidebar_300x250' => [
                'name' => 'Sidebar Medium Rectangle',
                'width' => 300,
                'height' => 250,
                'dimensions' => '300 × 250 px (IAB Standard MPU)',
                'aspect' => '6:5',
                'description' => 'Sticky or top right column sidebar block on desktop viewports.',
                'image' => 'uploads/ads/ad_sidebar_300x250.png'
            ],
            'sidebar_300x600' => [
                'name' => 'Sidebar Skyscraper Tower',
                'width' => 300,
                'height' => 600,
                'dimensions' => '300 × 600 px (Half Page Tower)',
                'aspect' => '1:2',
                'description' => 'High-impact vertical skyscraper banner in the right sidebar.',
                'image' => 'uploads/ads/ad_tower_300x600.png'
            ],
            'footer' => [
                'name' => 'Footer Full-Width Banner',
                'width' => 1200,
                'height' => 100,
                'dimensions' => '1200 × 100 px (or 728 × 90)',
                'aspect' => '12:1',
                'description' => 'Bottom full-width container above the global footer.',
                'image' => 'uploads/ads/ad_footer_1200x100.png'
            ],
            'sticky' => [
                'name' => 'Sticky Bottom Floating Banner',
                'width' => 728,
                'height' => 90,
                'dimensions' => '728 × 90 px',
                'aspect' => '8.08:1',
                'description' => 'Persistent floating strip pinned to the bottom of the viewport.',
                'image' => 'uploads/ads/ad_sticky_728x90.png'
            ],
            'popup' => [
                'name' => 'Pop-Up Interstitial Modal',
                'width' => 600,
                'height' => 400,
                'dimensions' => '600 × 400 px',
                'aspect' => '3:2',
                'description' => 'Centered lightbox dialog overlay on user session entry.',
                'image' => 'uploads/ads/ad_popup_600x400.png'
            ]
        ];
    }
    public static function formats(): array {
        return ['image'=>'Image / animated GIF','video'=>'Uploaded video','text'=>'Text advertisement','native'=>'Sponsored card','html'=>'AdSense / trusted HTML'];
    }
    public static function state(array $ad): string {
        if (!$ad['is_active']) return 'Paused';
        if ($ad['end_date'] && $ad['end_date']<date('Y-m-d')) return 'Expired';
        if ($ad['start_date'] && $ad['start_date']>date('Y-m-d')) return 'Scheduled';
        return 'Published';
    }
    public static function adsensePublisherId(): string {
        $setting = setting('adsense_id');
        if ($setting && preg_match('/^ca-pub-\d{10,20}$/D', trim($setting))) return trim($setting);
        $env = env('GOOGLE_ADSENSE_ID') ?: env('ADSENSE_PUBLISHER_ID');
        if ($env && preg_match('/^ca-pub-\d{10,20}$/D', trim($env))) return trim($env);
        return '';
    }
    private static ?array $activeAdsByPosition = null;

    public static function clearCache(): void {
        self::$activeAdsByPosition = null;
    }

    public static function isPreviewMode(): bool {
        if (isset($_GET['preview_ads'])) {
            return $_GET['preview_ads'] === '1' || $_GET['preview_ads'] === 'true';
        }
        return !empty($_SESSION['preview_ads']);
    }

    public static function activeCounts(): array {
        $activeMap = self::loadActiveAds();
        $counts = [];
        foreach (array_keys(self::placements()) as $pos) {
            $counts[$pos] = count($activeMap[$pos] ?? []);
        }
        return $counts;
    }

    private static function loadActiveAds(): array {
        if (self::$activeAdsByPosition !== null) {
            return self::$activeAdsByPosition;
        }
        $db = Database::getInstance()->getConnection();
        $today = date('Y-m-d');
        try {
            $statement = $db->prepare('SELECT * FROM advertisements WHERE is_active=1 AND (start_date IS NULL OR start_date="" OR start_date="0000-00-00" OR start_date<=?) AND (end_date IS NULL OR end_date="" OR end_date="0000-00-00" OR end_date>=?) ORDER BY priority DESC, id ASC');
            $statement->execute([$today, $today]);
            $all = $statement->fetchAll();
        } catch (\PDOException $e) {
            // Graceful fallback if table is not yet migrated to include priority column
            try {
                $statement = $db->prepare('SELECT * FROM advertisements WHERE is_active=1 AND (start_date IS NULL OR start_date="" OR start_date="0000-00-00" OR start_date<=?) AND (end_date IS NULL OR end_date="" OR end_date="0000-00-00" OR end_date>=?) ORDER BY id ASC');
                $statement->execute([$today, $today]);
                $all = $statement->fetchAll();
            } catch (\Throwable $fallbackEx) {
                try {
                    $statement = $db->prepare('SELECT * FROM advertisements WHERE is_active=1 ORDER BY id ASC');
                    $statement->execute();
                    $all = $statement->fetchAll();
                } catch (\Throwable $emptyEx) {
                    $all = [];
                }
            }
        }
        $byPosition = [];
        foreach ($all as $item) {
            $byPosition[$item['position']][] = $item;
        }
        self::$activeAdsByPosition = $byPosition;
        return self::$activeAdsByPosition;
    }

    private static function renderAdContent(array $ad): string {
        $format = $ad['ad_format'] ?: 'image';
        $destination = !empty($ad['url']) && safe_link($ad['url']) ? $ad['url'] : '';
        $linkStart = $destination ? '<a href="'.e($destination).'" target="_blank" rel="noopener noreferrer">' : '';
        $linkEnd = $destination ? '</a>' : '';
        $content = '';
        if ($format === 'html') {
            $code = $ad['ad_code'] ?? '';
            $publisherId = self::adsensePublisherId();
            if ($publisherId) {
                $code = str_replace(['{{ADSENSE_CLIENT}}', 'ca-pub-XXXXXXXXXXXXXXXX'], $publisherId, $code);
            }
            $content = $code;
        } elseif ($format === 'video' && !empty($ad['image_path'])) {
            $src = e(media_url($ad['image_path']));
            $mime = str_ends_with(strtolower($ad['image_path']), '.webm') ? 'video/webm' : 'video/mp4';
            $content = '<video controls playsinline preload="metadata" aria-label="'.e($ad['title']).'"><source src="'.$src.'" type="'.$mime.'">Your browser cannot play this video.</video>';
            if ($destination) $content .= '<a class="ad-cta" href="'.e($destination).'" target="_blank" rel="noopener noreferrer">'.e($ad['cta_label'] ?: 'Visit advertiser').' ↗</a>';
        } elseif ($format === 'text' || $format === 'native') {
            $image = $format === 'native' && !empty($ad['image_path']) ? '<img src="'.e(media_url($ad['image_path'])).'" alt="" loading="lazy">' : '';
            $content = $linkStart.'<div class="ad-card '.$format.'">'.$image.'<div><small>'.e($ad['advertiser'] ?: 'Sponsored').'</small><strong>'.e($ad['title']).'</strong><p>'.nl2br(e($ad['ad_text'])).'</p>'.($destination ? '<span class="ad-cta">'.e($ad['cta_label'] ?: 'Learn more').' ↗</span>' : '').'</div></div>'.$linkEnd;
        } elseif ($format === 'image') {
            $imgSrc = !empty($ad['image_path']) ? media_url($ad['image_path']) : '';
            if (!$imgSrc) {
                if ($ad['position'] === 'header_728x90') {
                    $imgSrc = url('assets/images/news/banner_strip_row1.jpg');
                } elseif ($ad['position'] === 'top_left') {
                    $imgSrc = url('assets/images/news/banner_top_left.jpg');
                } elseif ($ad['position'] === 'top_right') {
                    $imgSrc = url('assets/images/news/banner_top_right.jpg');
                } elseif ($ad['position'] === 'feed_left') {
                    $imgSrc = url('assets/images/news/banner_feed_left.jpg');
                } elseif ($ad['position'] === 'feed_right') {
                    $imgSrc = url('assets/images/news/banner_feed_right.jpg');
                } elseif (str_contains($ad['position'] ?? '', 'sidebar')) {
                    $imgSrc = url('assets/banner-sidebar.svg');
                } else {
                    $imgSrc = url('assets/banner-leaderboard.svg');
                }
            }
            $content = $linkStart.'<img src="'.e($imgSrc).'" alt="'.e($ad['title']).'" loading="lazy">'.$linkEnd;
        }
        return $content;
    }

    public static function render($position) {
        $activeMap = self::loadActiveAds();
        $ads = $activeMap[$position] ?? [];

        $validAds = [];
        if ($ads) {
            foreach ($ads as $adItem) {
                $c = self::renderAdContent($adItem);
                if ($c !== '') {
                    $validAds[] = [
                        'ad' => $adItem,
                        'content' => $c
                    ];
                }
            }
        }

        // When no active ad is found in this placement:
        if (empty($validAds)) {
            if (self::isPreviewMode()) {
                $meta = self::placements()[$position] ?? $position;
                $adminUrl = url('admin/advertisements', ['placement' => $position]) . '#ad-editor';
                $slot = '<div class="ad-slot ad-slot-preview placement-'.e($position).'" data-preview-zone="'.e($position).'">'
                      . '<div class="ad-zone-badge">📍 AD ZONE: '.e(strtoupper(str_replace('_', ' ', $position))).'</div>'
                      . '<div class="ad-zone-info"><strong>'.e($meta).'</strong><span>(No active ad published in this placement yet)</span></div>'
                      . '<a class="ad-zone-create-link" href="'.e($adminUrl).'" target="_blank">+ Create ad for this slot ↗</a>'
                      . '</div>';
                if ($position === 'popup') {
                    return '<div class="ad-popup" data-ad-popup hidden role="dialog" aria-modal="true" aria-label="Advertisement Preview">'
                         . '<div class="ad-popup-panel"><button type="button" data-close-popup aria-label="Close advertisement">×</button>'.$slot.'</div>'
                         . '</div>';
                }
                return $slot;
            }
            return '';
        }

        $zonePill = self::isPreviewMode() 
            ? '<span class="ad-zone-pill">📍 Zone: '.e($position).' ('.count($validAds).' active)</span>' 
            : '';

        $isTopBanner = in_array($position, ['header_728x90', 'top_left', 'top_right', 'feed_left', 'feed_right'], true);

        // If only 1 ad exists in this placement, render as standard single ad
        if (count($validAds) === 1) {
            $ad = $validAds[0]['ad'];
            $content = $validAds[0]['content'];
            $format = $ad['ad_format'] ?: 'image';
            $adLabel = ($isTopBanner || empty($ad['advertiser']))
                ? ''
                : '<span>ADVERTISEMENT'.($ad['advertiser'] ? ' · '.e($ad['advertiser']) : '').'</span>';

            $slot = '<div class="ad-slot placement-'.e($position).' format-'.e($format).'">'
                  . $zonePill
                  . $adLabel
                  . '<div class="ad-slot-inner">'.$content.'</div>'
                  . '</div>';
            if ($position === 'popup') {
                return '<div class="ad-popup" data-ad-popup hidden role="dialog" aria-modal="true" aria-label="Advertisement"><div class="ad-popup-panel"><button type="button" data-close-popup aria-label="Close advertisement">×</button>'.$slot.'</div></div>';
            }
            return $slot;
        }


        // Multiple ads in the same placement: Sort by priority (higher priority first)
        usort($validAds, function($a, $b) {
            $pA = (int)($a['ad']['priority'] ?? 1);
            $pB = (int)($b['ad']['priority'] ?? 1);
            if ($pA === $pB) {
                return (int)($a['ad']['id'] ?? 0) <=> (int)($b['ad']['id'] ?? 0);
            }
            return $pB <=> $pA;
        });

        $total = count($validAds);
        $slidesHtml = '';
        $dotsHtml = '';
        foreach ($validAds as $idx => $item) {
            $isActive = ($idx === 0);
            $activeClass = $isActive ? ' is-active' : '';
            $ariaHidden = $isActive ? 'false' : 'true';
            $adFmt = $item['ad']['ad_format'] ?: 'image';
            $sponsor = $item['ad']['advertiser'] ? ' · '.e($item['ad']['advertiser']) : '';
            
            $slidesHtml .= '<div class="ad-slide'.$activeClass.' format-'.e($adFmt).'" data-ad-slide="'.$idx.'" data-ad-sponsor="'.$sponsor.'" aria-hidden="'.$ariaHidden.'">';
            $slidesHtml .= $item['content'];
            $slidesHtml .= '</div>';

            $dotsHtml .= '<button type="button" class="ad-dot'.$activeClass.'" data-ad-dot="'.$idx.'" aria-label="Advertisement '.($idx + 1).' of '.$total.'"></button>';
        }

        $firstSponsor = $validAds[0]['ad']['advertiser'] ? ' · '.e($validAds[0]['ad']['advertiser']) : '';
        $headerHtml = $isTopBanner 
            ? '' 
            : '<div class="ad-slot-header"><span>ADVERTISEMENT<span class="ad-sponsor-label" data-sponsor-display>'.$firstSponsor.'</span></span> <span class="ad-loop-counter" data-ad-counter>1/'.$total.'</span></div>';
        $dotsMarkup = $isTopBanner ? '' : '<div class="ad-loop-dots" role="tablist" aria-label="Advertisement pagination">'.$dotsHtml.'</div>';
        $slot = '<div class="ad-slot placement-'.e($position).' format-multi has-loop" data-ad-loop="5000" role="region" aria-label="Sponsored advertisements">'
              . $zonePill
              . $headerHtml
              . '<div class="ad-loop-track">'.$slidesHtml.'</div>'
              . $dotsMarkup
              . '</div>';


        if ($position === 'popup') {
            return '<div class="ad-popup" data-ad-popup hidden role="dialog" aria-modal="true" aria-label="Advertisement"><div class="ad-popup-panel"><button type="button" data-close-popup aria-label="Close advertisement">×</button>'.$slot.'</div></div>';
        }
        return $slot;
    }

    public static function seedSampleAds(): int {
        $db = Database::getInstance()->getConnection();
        $sampleAds = [
            [
                'title' => 'SriLankan Airlines · Discover The Pearl of The Indian Ocean',
                'position' => 'header_728x90',
                'ad_format' => 'image',
                'advertiser' => 'SriLankan Airlines',
                'url' => 'https://www.srilankan.com',
                'image_path' => 'uploads/ads/ad_header_1200x100.png',
                'priority' => 10
            ],
            [
                'title' => 'Ceylon Pure Tea · Handcrafted Single Origin Excellence',
                'position' => 'top_left',
                'ad_format' => 'image',
                'advertiser' => 'Sri Lanka Tea Board',
                'url' => 'https://www.pureceylontea.com',
                'image_path' => 'uploads/ads/ad_top_left_590x100.png',
                'priority' => 10
            ],
            [
                'title' => 'Colombo Port City · South Asia’s Premier Offshore Financial Hub',
                'position' => 'top_right',
                'ad_format' => 'image',
                'advertiser' => 'Port City Colombo',
                'url' => 'https://www.portcitycolombo.lk',
                'image_path' => 'uploads/ads/ad_top_right_590x100.png',
                'priority' => 10
            ],
            [
                'title' => 'Commercial Bank Flash · Next-Generation Digital Banking',
                'position' => 'homepage_mid_728x90',
                'ad_format' => 'image',
                'advertiser' => 'Commercial Bank of Ceylon',
                'url' => 'https://www.combank.lk',
                'image_path' => 'uploads/ads/ad_between_sections_728x90.png',
                'priority' => 10
            ],
            [
                'title' => 'Dialog 5G Home Broadband · Unlimited High-Speed Entertainment',
                'position' => 'inline_feed',
                'ad_format' => 'image',
                'advertiser' => 'Dialog Axiata PLC',
                'url' => 'https://www.dialog.lk',
                'image_path' => 'uploads/ads/ad_inline_feed_728x90.png',
                'priority' => 10
            ],
            [
                'title' => 'Cinnamon Grand Colombo · Luxury Stays in the Heart of the City',
                'position' => 'sidebar_300x250',
                'ad_format' => 'image',
                'advertiser' => 'Cinnamon Hotels & Resorts',
                'url' => 'https://www.cinnamonhotels.com',
                'image_path' => 'uploads/ads/ad_sidebar_300x250.png',
                'priority' => 10
            ],
            [
                'title' => 'Bank of Ceylon · Bankers to the Nation Since 1939',
                'position' => 'sidebar_300x600',
                'ad_format' => 'image',
                'advertiser' => 'Bank of Ceylon',
                'url' => 'https://www.boc.lk',
                'image_path' => 'uploads/ads/ad_tower_300x600.png',
                'priority' => 10
            ],
            [
                'title' => 'Hayleys Solar · Power Your Home & Business with Clean Green Energy',
                'position' => 'in_article',
                'ad_format' => 'image',
                'advertiser' => 'Hayleys Fentons Solar',
                'url' => 'https://www.hayleysfentons.com',
                'image_path' => 'uploads/ads/ad_in_article_800x250.png',
                'priority' => 10
            ],
            [
                'title' => 'SLT-MOBITEL · The National ICT Solutions Provider of Sri Lanka',
                'position' => 'footer',
                'ad_format' => 'image',
                'advertiser' => 'SLT-MOBITEL',
                'url' => 'https://www.sltmobitel.lk',
                'image_path' => 'uploads/ads/ad_footer_1200x100.png',
                'priority' => 10
            ],
            [
                'title' => 'Sri Lanka Tourism · You Will Come Back for Love',
                'position' => 'sticky',
                'ad_format' => 'image',
                'advertiser' => 'Sri Lanka Tourism Promotion Bureau',
                'url' => 'https://www.srilanka.travel',
                'image_path' => 'uploads/ads/ad_sticky_728x90.png',
                'priority' => 10
            ],
            [
                'title' => 'Lanka Premier League 2026 · Witness the Thrill Live at the Stadium',
                'position' => 'popup',
                'ad_format' => 'image',
                'advertiser' => 'Sri Lanka Cricket (SLC)',
                'url' => 'https://www.srilankacricket.lk',
                'image_path' => 'uploads/ads/ad_popup_600x400.png',
                'priority' => 10
            ]
        ];

        $inserted = 0;
        foreach ($sampleAds as $ad) {
            $existing = $db->prepare('SELECT id FROM advertisements WHERE position=? AND is_active=1 LIMIT 1');
            $existing->execute([$ad['position']]);
            if ($existing->fetch()) continue; // Keep existing active ad

            try {
                $stmt = $db->prepare('INSERT INTO advertisements (title, position, ad_format, advertiser, ad_text, cta_label, url, image_path, priority, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)');
                $stmt->execute([
                    $ad['title'],
                    $ad['position'],
                    $ad['ad_format'],
                    $ad['advertiser'] ?? null,
                    $ad['ad_text'] ?? null,
                    $ad['cta_label'] ?? null,
                    $ad['url'] ?? null,
                    $ad['image_path'] ?? null,
                    $ad['priority'] ?? 10
                ]);
                $inserted++;
            } catch (\Throwable $e) {
                // Fallback without priority column if needed
                $stmt = $db->prepare('INSERT INTO advertisements (title, position, ad_format, advertiser, ad_text, cta_label, url, image_path, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)');
                $stmt->execute([
                    $ad['title'],
                    $ad['position'],
                    $ad['ad_format'],
                    $ad['advertiser'] ?? null,
                    $ad['ad_text'] ?? null,
                    $ad['cta_label'] ?? null,
                    $ad['url'] ?? null,
                    $ad['image_path'] ?? null
                ]);
                $inserted++;
            }
        }
        self::clearCache();
        return $inserted;
    }
}
