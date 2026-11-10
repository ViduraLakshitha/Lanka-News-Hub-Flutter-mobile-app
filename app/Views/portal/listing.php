<?php 
require __DIR__.'/header.php'; 
$section = $section ?? 'latest';
$isVideo = $section === 'videos';
$isGallery = $section === 'gallery';
$isSearch = $section === 'search';
$page = (int)($page ?? 1);
?>

<!-- Top Leaderboard Ad Banners -->
<div class="ad-top-ads-bar" style="margin-top: 10px; margin-bottom: 14px;">
  <?php 
    $adHeader = \App\Helpers\AdHelper::render('header_728x90');
    $leftBanner = \App\Helpers\AdHelper::render('top_left');
    $rightBanner = \App\Helpers\AdHelper::render('top_right');
  ?>
  <div class="ad-banner-strip">
    <?= $adHeader ?: '<a href="#" aria-label="National Financial & Commercial Partners"><img src="'.e(url('assets/images/news/banner_strip_row1.jpg')).'" alt="National Partners" width="1220" height="85" loading="eager"></a>' ?>
  </div>
  <?php if ($leftBanner || $rightBanner): ?>
    <div class="ad-dual-banners">
      <div class="ad-banner-strip">
        <?= $leftBanner ?: '<a href="#"><img src="'.e(url('assets/images/news/banner_top_left.jpg')).'" alt="National Sponsors" width="606" height="85"></a>' ?>
      </div>
      <div class="ad-banner-strip">
        <?= $rightBanner ?: '<a href="#"><img src="'.e(url('assets/images/news/banner_top_right.jpg')).'" alt="Community Partners" width="606" height="85"></a>' ?>
      </div>
    </div>
  <?php else: ?>
    <div class="ad-banner-strip">
      <a href="#" aria-label="Community & Corporate Initiatives">
        <img src="<?= e(url('assets/images/news/banner_strip_row2.jpg')) ?>" alt="Community Initiatives" width="1220" height="85" loading="eager">
      </a>
    </div>
  <?php endif; ?>
</div>

<div class="ad-listing-page">
  <!-- Section Title -->
  <div class="ad-section-header">
    <h1 class="ad-section-title"><?= e($title) ?></h1>
  </div>

  <?php if (empty($items)): ?>
    <div class="ad-empty-state">
      <h3><?= e(t('No stories yet')) ?></h3>
      <p>No stories found for this section. Please check back shortly or explore our latest news edition.</p>
      <a href="<?= e(url()) ?>" class="ad-btn-back">← <?= e(t('Home')) ?></a>
    </div>
  <?php else: ?>
    <!-- 2-Column Broadsheet Layout (Main Stream + Sidebar) -->
    <div class="ad-listing-layout row g-4">
      
      <!-- Left Column: Story Stream (~68%) -->
      <div class="col-12 col-lg-8 ad-listing-main">
        <?php 
          // On page 1 (and not search), display the top 2 stories side-by-side
          if ($page === 1 && !$isSearch && count($items) >= 2):
            $topLeads = array_slice($items, 0, 2);
            $streamStories = array_slice($items, 2);
        ?>
          <!-- Top 2 Featured Lead Stories (Side-by-Side) -->
          <div class="ad-top-leads-grid">
            <?php foreach ($topLeads as $lead): ?>
              <article class="ad-lead-card">
                <a href="<?= e(url('news/'.$lead['slug'])) ?>" class="ad-lead-card-media">
                  <img src="<?= e(media_url($lead['image_path'])) ?>" alt="<?= e(localized($lead, 'title')) ?>" loading="lazy" width="360" height="200">
                  <?php if ($isVideo || !empty($lead['video_url']) || !empty($lead['video_media_id'])): ?>
                    <span class="ad-play-badge">▶</span>
                  <?php elseif ($isGallery || !empty($lead['gallery_ids']) && $lead['gallery_ids'] !== '[]'): ?>
                    <span class="ad-gallery-badge">📷</span>
                  <?php endif; ?>
                </a>
                <h2 class="ad-lead-card-title">
                  <a href="<?= e(url('news/'.$lead['slug'])) ?>"><?= e(localized($lead, 'title')) ?></a>
                </h2>
                <p class="ad-lead-card-summary">
                  <?= e(localized($lead, 'summary')) ?>
                </p>
                <div class="ad-lead-card-meta">
                  <time datetime="<?= e(date('c', strtotime($lead['published_at']))) ?>">
                    <?= e(date('d F Y · H:i', strtotime($lead['published_at']))) ?>
                  </time>
                </div>
              </article>
            <?php endforeach; ?>
          </div>
        <?php 
          else: 
            $streamStories = $items;
          endif; 
        ?>

        <!-- Compact Horizontal River of News -->
        <div class="ad-stream-feed">
          <?php foreach ($streamStories as $idx => $story): ?>
            <?php if ($idx === 6): ?>
              <?php 
                $inlineAd = \App\Helpers\AdHelper::render('inline_feed');
                $feedLeftList = \App\Helpers\AdHelper::render('feed_left');
                $feedRightList = \App\Helpers\AdHelper::render('feed_right');
              ?>
              <?php if ($inlineAd): ?>
                <div class="ad-inline-banner my-3">
                  <?= $inlineAd ?>
                </div>
              <?php else: ?>
                <div class="ad-feed-dual-banners my-3" aria-label="Sponsored Advertisements">
                  <div class="ad-banner-strip">
                    <?= $feedLeftList ?: '<a href="#" aria-label="Singer Sri Lanka Sponsor"><img src="'.e(url('assets/images/news/banner_feed_left.jpg')).'" alt="Singer Sri Lanka" width="590" height="100" loading="lazy"></a>' ?>
                  </div>
                  <div class="ad-banner-strip">
                    <?= $feedRightList ?: '<a href="#" aria-label="Keells Supermarket Sponsor"><img src="'.e(url('assets/images/news/banner_feed_right.jpg')).'" alt="Keells Supermarket" width="590" height="100" loading="lazy"></a>' ?>
                  </div>
                </div>
              <?php endif; ?>
            <?php endif; ?>
            <article class="ad-stream-row">
              <a href="<?= e(url('news/'.$story['slug'])) ?>" class="ad-stream-thumb">
                <img src="<?= e(media_url($story['image_path'])) ?>" alt="<?= e(localized($story, 'title')) ?>" loading="lazy" width="140" height="92">
                <?php if ($isVideo || !empty($story['video_url']) || !empty($story['video_media_id'])): ?>
                  <span class="ad-play-badge-sm">▶</span>
                <?php elseif ($isGallery || !empty($story['gallery_ids']) && $story['gallery_ids'] !== '[]'): ?>
                  <span class="ad-gallery-badge-sm">📷</span>
                <?php endif; ?>
              </a>
              <div class="ad-stream-content">
                <h3 class="ad-stream-title">
                  <a href="<?= e(url('news/'.$story['slug'])) ?>"><?= e(localized($story, 'title')) ?></a>
                </h3>
                <p class="ad-stream-summary">
                  <?= e(localized($story, 'summary')) ?>
                </p>
                <div class="ad-stream-meta">
                  <time datetime="<?= e(date('c', strtotime($story['published_at']))) ?>">
                    <?= e(date('F d, Y  h:i a', strtotime($story['published_at']))) ?>
                  </time>
                </div>
              </div>
            </article>
          <?php endforeach; ?>
        </div>

        <!-- Ada Derana Style Pagination -->
        <?php require __DIR__.'/pagination.php'; ?>

      </div>

      <!-- Right Column: Sidebar (~32%) -->
      <aside class="col-12 col-lg-4 ad-listing-sidebar">
        
        <!-- Video Stories Widget -->
        <?php if (!empty($videoStories)): ?>
          <?php 
            $topVideo = $videoStories[0];
            $sideVideos = array_slice($videoStories, 1, 4);
          ?>
          <div class="ad-video-widget">
            <div class="ad-widget-header">
              <h3><?= e(t('Video Stories')) ?></h3>
            </div>
            
            <!-- Featured Video with Prominent Red Play Button -->
            <div class="ad-video-featured">
              <a href="<?= e(url('news/'.$topVideo['slug'])) ?>" class="ad-video-featured-media">
                <img src="<?= e(media_url($topVideo['image_path'])) ?>" alt="<?= e(localized($topVideo, 'title')) ?>" loading="lazy" width="340" height="190">
                <div class="ad-video-play-btn" aria-label="Play video">
                  <svg viewBox="0 0 24 24" width="36" height="36" fill="#fff"><path d="M8 5v14l11-7z"/></svg>
                </div>
              </a>
              <h4 class="ad-video-featured-title">
                <a href="<?= e(url('news/'.$topVideo['slug'])) ?>"><?= e(localized($topVideo, 'title')) ?></a>
              </h4>
            </div>

            <!-- List of Video Stories -->
            <div class="ad-video-stream">
              <?php foreach ($sideVideos as $vItem): ?>
                <div class="ad-video-stream-row">
                  <a href="<?= e(url('news/'.$vItem['slug'])) ?>" class="ad-video-stream-thumb">
                    <img src="<?= e(media_url($vItem['image_path'])) ?>" alt="<?= e(localized($vItem, 'title')) ?>" loading="lazy" width="75" height="50">
                  </a>
                  <a href="<?= e(url('news/'.$vItem['slug'])) ?>" class="ad-video-stream-title">
                    <?= e(localized($vItem, 'title')) ?>
                  </a>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>

        <!-- 300x250 Banner Ad -->
        <div class="ad-sidebar-slot mt-3">
          <?= \App\Helpers\AdHelper::render('sidebar_300x250') ?: '
          <a href="#" aria-label="Commercial Partner">
            <img src="'.e(url('assets/images/adaderana/banner_combank.jpg')).'" alt="Commercial Partner" width="300" height="250" style="max-width:100%; height:auto; display:block; border-radius:3px;">
          </a>' ?>
        </div>

        <!-- 300x600 Half-Page Banner Ad -->
        <div class="ad-sidebar-slot mt-3">
          <?= \App\Helpers\AdHelper::render('sidebar_300x600') ?: '
          <a href="#" aria-label="Metro Sponsor">
            <img src="'.e(url('assets/images/adaderana/banner_metro.jpg')).'" alt="Metro Sponsor" width="300" height="600" style="max-width:100%; height:auto; display:block; border-radius:3px;">
          </a>' ?>
        </div>

      </aside>
    </div>
  <?php endif; ?>

  <!-- App Download & Facebook Promo Bar (Right Above Footer) -->
  <div class="ad-promo-strip my-4">
    <div class="ad-app-box">
      <div class="ad-app-box-text">
        <span class="ad-app-brand-badge">LN</span>
        <span><strong><?= e(setting('site_name', 'Ada Derana')) ?> News App</strong> is now available on:</span>
      </div>
      <div class="ad-app-badges">
        <a href="#" aria-label="Download on App Store"><img src="<?= e(url('assets/images/news/app_store.svg')) ?>" alt="App Store" height="32"></a>
        <a href="#" aria-label="Get it on Google Play"><img src="<?= e(url('assets/images/news/google_play.svg')) ?>" alt="Google Play" height="32"></a>
      </div>
    </div>
    <a href="https://facebook.com" target="_blank" rel="noopener" class="ad-fb-bar">
      <span>Follow <?= e(setting('site_name', 'Ada Derana')) ?> Page on Facebook</span>
      <span class="ad-fb-badge">facebook</span>
    </a>
  </div>
</div>

<?php require __DIR__.'/footer.php'; ?>
