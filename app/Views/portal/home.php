<?php require __DIR__.'/header.php'; ?>

<!-- Top Leaderboard Ad Banners (Full Width Edge-to-Edge) -->
<div class="ad-top-ads-bar">
  <?php 
    $adHeader = \App\Helpers\AdHelper::render('header_728x90');
    $leftBanner = \App\Helpers\AdHelper::render('top_left');
    $rightBanner = \App\Helpers\AdHelper::render('top_right');
  ?>
  <div class="ad-banner-strip">
    <?php if ($adHeader): ?>
      <?= $adHeader ?>
    <?php else: ?>
      <a href="#" aria-label="National Financial & Commercial Partners">
        <img src="<?= e(url('assets/images/news/banner_strip_row1.jpg')) ?>" alt="National Partners" width="1220" height="85" loading="eager">
      </a>
    <?php endif; ?>
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


<?php 
  // Select hero and secondary stories
  $hero = $featured[0] ?? $latest[0] ?? null;
  $stream = $hero ? array_values(array_filter($latest, fn($x) => $x['id'] !== $hero['id'])) : $latest;
?>

<!-- Main 3-Column Layout -->
<div class="ad-main-grid row g-4 g-xl-5 align-items-start">

  <!-- ==========================================
       COLUMN 1 (LEFT ~42%)
       ========================================== -->
  <section class="ad-col-left col-12 col-lg-5" aria-label="Lead & Feed Stories">
    <?php if ($hero): ?>
      <!-- Lead Story -->
      <article class="ad-lead-story">
        <h1 class="ad-lead-headline">
          <a href="<?= e(url('news/'.$hero['slug'])) ?>"><?= e(localized($hero, 'title')) ?></a>
        </h1>
        <div class="ad-lead-meta">
          <time datetime="<?= e(date('c', strtotime($hero['published_at']))) ?>">
            <?= e(date('H:i · d M Y', strtotime($hero['published_at']))) ?>
          </time>
          <?php if (!empty($hero['category_name'])): ?>
            · <a href="<?= e(url('category/'.$hero['category_slug'])) ?>" style="color:#c4161c; font-weight:700;"><?= e($hero['category_name']) ?></a>
          <?php endif; ?>
        </div>
        <div class="ad-lead-image-wrap">
          <span class="ad-hot-badge"><?= $hero['is_breaking'] ? e(t('Breaking news')) : e(t('Hot News')) ?></span>
          <a href="<?= e(url('news/'.$hero['slug'])) ?>">
            <img src="<?= e(media_url($hero['image_path'])) ?>" alt="<?= e(localized($hero, 'title')) ?>" width="800" height="450" fetchpriority="high">
          </a>
        </div>
      </article>
    <?php endif; ?>

    <!-- App Promo Banner 1 -->
    <div class="ad-app-promo-banner">
      <div class="ad-app-promo-left">
        <img class="ad-app-icon" src="<?= e(url('assets/images/lankanews_mark.svg')) ?>" alt="News App Icon" width="42" height="42">
        <div class="ad-app-promo-text"><?= e(setting('site_name', 'Lanka News Hub')) ?> Mobile App<br><span style="color:#666; font-weight:500;">Available on the App Store & Google Play</span></div>
      </div>
      <div class="ad-app-promo-badges">
        <a href="#" aria-label="App Store"><img src="<?= e(url('assets/images/news/app_store.svg')) ?>" alt="App Store" height="32"></a>
        <a href="#" aria-label="Google Play"><img src="<?= e(url('assets/images/news/google_play.svg')) ?>" alt="Google Play" height="32"></a>
      </div>
    </div>

    <!-- Feed Items 1 to 5 -->
    <?php foreach (array_slice($stream, 0, 5) as $story): ?>
      <div class="ad-story-item">
        <a class="ad-story-thumb" href="<?= e(url('news/'.$story['slug'])) ?>">
          <img src="<?= e(media_url($story['image_path'])) ?>" alt="<?= e(localized($story, 'title')) ?>" width="120" height="80" loading="lazy">
        </a>
        <div class="ad-story-content">
          <h2 class="ad-story-title"><a href="<?= e(url('news/'.$story['slug'])) ?>"><?= e(localized($story, 'title')) ?></a></h2>
          <div class="ad-story-time">
            <time><?= e(date('H:i · d M', strtotime($story['published_at']))) ?></time>
            <?php if (!empty($story['category_name'])): ?>
              · <span style="color:#c4161c; font-weight:600;"><?= e($story['category_name']) ?></span>
            <?php endif; ?>
          </div>
        </div>
      </div>
    <?php endforeach; ?>

    <!-- Feed Dual Banners (Left & Right) -->
    <?php 
      $feedLeftHome = \App\Helpers\AdHelper::render('feed_left');
      $feedRightHome = \App\Helpers\AdHelper::render('feed_right');
    ?>
    <div class="ad-feed-dual-banners my-3" aria-label="Sponsored Advertisements">
      <div class="ad-banner-strip">
        <?= $feedLeftHome ?: '<a href="#" aria-label="Singer Sri Lanka Sponsor"><img src="'.e(url('assets/images/news/banner_feed_left.jpg')).'" alt="Singer Sri Lanka" width="590" height="100" loading="lazy"></a>' ?>
      </div>
      <div class="ad-banner-strip">
        <?= $feedRightHome ?: '<a href="#" aria-label="Keells Supermarket Sponsor"><img src="'.e(url('assets/images/news/banner_feed_right.jpg')).'" alt="Keells Supermarket" width="590" height="100" loading="lazy"></a>' ?>
      </div>
    </div>

    <!-- Blue Social Community Banner -->
    <div class="ad-facebook-banner">
      <div class="ad-fb-left">
        <span class="ad-fb-icon">
          <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor" aria-hidden="true"><path d="M13.397 20.997v-8.196h2.765l.411-3.209h-3.176V7.548c0-.926.258-1.56 1.587-1.56h1.684V3.127A22.336 22.336 0 0 0 14.201 3c-2.444 0-4.122 1.492-4.122 4.231v2.355H7.332v3.209h2.753v8.202h3.312z"/></svg>
        </span>
        <span>Follow <?= e(setting('site_name', 'Lanka News Hub')) ?> on Facebook</span>
      </div>
      <a href="https://facebook.com" target="_blank" rel="noopener" class="ad-fb-btn">Follow</a>
    </div>

    <!-- Feed Items 6 to 10 -->
    <?php foreach (array_slice($stream, 5, 5) as $story): ?>
      <div class="ad-story-item">
        <a class="ad-story-thumb" href="<?= e(url('news/'.$story['slug'])) ?>">
          <img src="<?= e(media_url($story['image_path'])) ?>" alt="<?= e(localized($story, 'title')) ?>" width="120" height="80" loading="lazy">
        </a>
        <div class="ad-story-content">
          <h2 class="ad-story-title"><a href="<?= e(url('news/'.$story['slug'])) ?>"><?= e(localized($story, 'title')) ?></a></h2>
          <div class="ad-story-time">
            <time><?= e(date('H:i · d M', strtotime($story['published_at']))) ?></time>
            <?php if (!empty($story['category_name'])): ?>
              · <span style="color:#c4161c; font-weight:600;"><?= e($story['category_name']) ?></span>
            <?php endif; ?>
          </div>
        </div>
      </div>
    <?php endforeach; ?>

    <!-- Green WhatsApp Channel Banner -->
    <div class="ad-whatsapp-banner">
      <div class="ad-wa-left">
        <span class="ad-wa-icon" style="display:inline-flex; align-items:center; justify-content:center; width:26px; height:26px;">
          <svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor" aria-hidden="true"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
        </span>
        <span>Join <?= e(setting('site_name', 'Lanka News Hub')) ?> WhatsApp Channel</span>
      </div>
      <a href="https://whatsapp.com" target="_blank" rel="noopener" class="ad-wa-btn">Follow</a>
    </div>

    <!-- Feed Items 11 to 15 -->
    <?php foreach (array_slice($stream, 10, 5) as $story): ?>
      <div class="ad-story-item">
        <a class="ad-story-thumb" href="<?= e(url('news/'.$story['slug'])) ?>">
          <img src="<?= e(media_url($story['image_path'])) ?>" alt="<?= e(localized($story, 'title')) ?>" width="120" height="80" loading="lazy">
        </a>
        <div class="ad-story-content">
          <h2 class="ad-story-title"><a href="<?= e(url('news/'.$story['slug'])) ?>"><?= e(localized($story, 'title')) ?></a></h2>
          <div class="ad-story-time">
            <time><?= e(date('H:i · d M', strtotime($story['published_at']))) ?></time>
            <?php if (!empty($story['category_name'])): ?>
              · <span style="color:#c4161c; font-weight:600;"><?= e($story['category_name']) ?></span>
            <?php endif; ?>
          </div>
        </div>
      </div>
    <?php endforeach; ?>

    <!-- Bottom App Promo Banner -->
    <div class="ad-app-promo-banner">
      <div class="ad-app-promo-left">
        <img class="ad-app-icon" src="<?= e(url('assets/images/lankanews_mark.svg')) ?>" alt="News App Icon" width="38" height="38">
        <div class="ad-app-promo-text"><?= e(setting('site_name', 'Lanka News Hub')) ?> 24x7 Alerts<br><span style="color:#666; font-weight:500;">Get breaking news on Android and iOS devices</span></div>
      </div>
      <div class="ad-app-promo-badges">
        <a href="#" aria-label="App Store"><img src="<?= e(url('assets/images/news/app_store.svg')) ?>" alt="App Store" height="28"></a>
        <a href="#" aria-label="Google Play"><img src="<?= e(url('assets/images/news/google_play.svg')) ?>" alt="Google Play" height="28"></a>
      </div>
    </div>
  </section>

  <!-- ==========================================
       COLUMN 2 (MIDDLE ~33%)
       ========================================== -->
  <aside class="ad-col-mid col-12 col-md-6 col-lg-4" aria-label="Audio and Video Features">
    <!-- Video of the Day -->
    <?php $leadVideo = $videos[0] ?? $stream[1] ?? $hero; ?>
    <?php if ($leadVideo): ?>
      <div class="ad-section-title">
        <div class="ad-title-left">
          <span>📹</span>
          <span><?= e(t('Videos')) ?> · Video of the Day</span>
        </div>
      </div>
      <div class="ad-video-card">
        <a href="<?= e(url('news/'.$leadVideo['slug'])) ?>" aria-label="Watch video: <?= e(localized($leadVideo, 'title')) ?>">
          <img src="<?= e(media_url($leadVideo['image_path'])) ?>" alt="<?= e(localized($leadVideo, 'title')) ?>" width="380" height="215">
          <div class="ad-play-overlay">▶</div>
        </a>
        <div class="ad-video-caption">
          <a href="<?= e(url('news/'.$leadVideo['slug'])) ?>" style="color:#fff;">
            <?= e(localized($leadVideo, 'title')) ?>
          </a>
        </div>
      </div>
    <?php endif; ?>

    <!-- AI Audio / Audio Briefings -->
    <div class="ad-ai-header" id="audio-briefing">
      <img src="<?= e(url('assets/images/lankanews_mark.svg')) ?>" alt="Audio Logo">
      <h3>Audio Briefings</h3>
    </div>
    <?php foreach (array_slice($latest, 0, 5) as $i => $item): ?>
      <div class="ad-compact-item">
        <div class="ad-compact-thumb">
          <img src="<?= e(media_url($item['image_path'])) ?>" alt="<?= e(localized($item, 'title')) ?>">
        </div>
        <div class="ad-compact-content">
          <h4 class="ad-compact-title">
            <a href="<?= e(url('news/'.$item['slug'])) ?>"><?= e(localized($item, 'title')) ?></a>
          </h4>
          <div class="ad-compact-meta">
            <span class="ad-audio-badge">▶ <?= sprintf('1:%02d min', 15 + ($i * 12)) ?></span> · 
            <span><?= e(date('H:i', strtotime($item['published_at']))) ?></span>
          </div>
        </div>
      </div>
    <?php endforeach; ?>

    <!-- AI Summary / Quick Wire -->
    <div class="ad-ai-header" id="summary-wire">
      <img src="<?= e(url('assets/images/lankanews_mark.svg')) ?>" alt="Summary Logo">
      <h3>Quick Summaries</h3>
    </div>
    <?php foreach (array_slice($latest, 5, 5) as $item): ?>
      <div class="ad-compact-item">
        <div class="ad-compact-thumb">
          <img src="<?= e(media_url($item['image_path'])) ?>" alt="<?= e(localized($item, 'title')) ?>">
        </div>
        <div class="ad-compact-content">
          <h4 class="ad-compact-title">
            <a href="<?= e(url('news/'.$item['slug'])) ?>"><?= e(localized($item, 'title')) ?></a>
          </h4>
          <div class="ad-compact-meta">
            <time><?= e(date('H:i · d M', strtotime($item['published_at']))) ?></time>
          </div>
        </div>
      </div>
    <?php endforeach; ?>

    <!-- Middle Column Ad Banner (Fills 100% Column Width) -->
    <?php $midAd = \App\Helpers\AdHelper::render('sidebar_300x250'); ?>
    <?php if ($midAd): ?>
      <div class="ad-col-banner" style="margin:14px 0 16px;"><?= $midAd ?></div>
    <?php else: ?>
      <div class="ad-col-banner" style="margin:14px 0 16px;">
        <a href="#" aria-label="Commercial Bank"><img src="<?= e(url('assets/images/news/banner_combank.jpg')) ?>" alt="Commercial Bank" width="320" height="95"></a>
      </div>
    <?php endif; ?>

    <!-- Fast Bullet Headlines -->
    <ul class="ad-bullet-news">
      <?php foreach (array_slice($latest, 10, 5) as $item): ?>
        <li>
          <a href="<?= e(url('news/'.$item['slug'])) ?>"><?= e(localized($item, 'title')) ?></a>
        </li>
      <?php endforeach; ?>
    </ul>

    <!-- Newsroom Desk Wire -->
    <div class="ad-ai-header">

      <img src="<?= e(url('assets/images/lankanews_mark.svg')) ?>" alt="News Wire">
      <h3>Newsroom Desk Wire</h3>
    </div>
    <ul class="ad-bullet-news">
      <?php foreach (array_slice($breaking, 0, 3) as $item): ?>
        <li>
          <a href="<?= e(url('news/'.$item['slug'])) ?>"><?= e(localized($item, 'title')) ?></a>
        </li>
      <?php endforeach; ?>
    </ul>
    <div style="text-align:right; margin-top:4px;">
      <a href="<?= e(url('edition/latest')) ?>" style="color:#c4161c; font-size:11px; font-weight:700;"><?= e(t('More news')) ?> »</a>
    </div>
  </aside>

  <!-- ==========================================
       COLUMN 3 (RIGHT ~25%)
       ========================================== -->
  <aside class="ad-col-right col-12 col-md-6 col-lg-3" aria-label="Bulletins and Visual Media">
    <!-- Featured Bulletin / Broadcast -->
    <?php $bulletinVideo = $videos[1] ?? $videos[0] ?? $stream[2] ?? null; ?>
    <div class="ad-section-title">
      <div class="ad-title-left">
        <span>Newsroom Bulletin Live</span>
      </div>
    </div>
    <div class="ad-video-card">
      <?php if ($bulletinVideo): ?>
        <a href="<?= e(url('news/'.$bulletinVideo['slug'])) ?>" aria-label="<?= e(localized($bulletinVideo, 'title')) ?>">
          <img src="<?= e(media_url($bulletinVideo['image_path'])) ?>" alt="<?= e(localized($bulletinVideo, 'title')) ?>" width="380" height="215">
          <div class="ad-play-overlay">▶</div>
        </a>
        <div class="ad-video-caption">
          <a href="<?= e(url('news/'.$bulletinVideo['slug'])) ?>" style="color:#fff;">
            <?= e(localized($bulletinVideo, 'title')) ?>
          </a>
        </div>
      <?php else: ?>
        <img src="<?= e(url('assets/images/news/bulletin_12pm.jpg')) ?>" alt="News Bulletin" width="380" height="215">
        <div class="ad-play-overlay">▶</div>
      <?php endif; ?>
    </div>

    <!-- Right Column Banner 1 -->
    <div class="ad-col-banner" style="margin:0 0 14px;">
      <a href="#" aria-label="Telecom Network Partner">
        <img src="<?= e(url('assets/images/news/banner_mobitel.jpg')) ?>" alt="Telecom Network" width="320" height="85">
      </a>
    </div>

    <!-- Top Picture Story -->
    <?php $picStory = $pictures[0] ?? $stream[3] ?? null; ?>
    <div class="ad-section-title">
      <div class="ad-title-left">
        <span>📷</span>
        <span><?= e(t('Photo gallery')) ?> · Feature Story</span>
      </div>
    </div>
    <div class="ad-picture-story-card">
      <?php if ($picStory): ?>
        <a href="<?= e(url('news/'.$picStory['slug'])) ?>">
          <img src="<?= e(media_url($picStory['image_path'])) ?>" alt="<?= e(localized($picStory, 'title')) ?>" width="400" height="300" loading="lazy">
        </a>
        <div class="ad-picture-caption">
          <a href="<?= e(url('news/'.$picStory['slug'])) ?>"><?= e(localized($picStory, 'title')) ?></a>
        </div>
      <?php else: ?>
        <img src="<?= e(url('assets/images/news/top_picture_girls.jpg')) ?>" alt="Picture Story" width="400" height="300" loading="lazy">
        <div class="ad-picture-caption">Documentary Dispatch · In Pictures</div>
      <?php endif; ?>
    </div>

    <!-- Video Stories Playlist -->
    <div class="ad-section-title">
      <div class="ad-title-left">
        <span><?= e(t('Videos')) ?> · Playlist</span>
      </div>
    </div>
    <div class="ad-video-playlist">
      <?php 
        $videoList = $videos ?: array_slice($latest, 2, 8); 
      ?>
      <?php foreach ($videoList as $vid): ?>
        <div class="ad-video-item">
          <div class="ad-video-item-thumb">
            <a href="<?= e(url('news/'.$vid['slug'])) ?>">
              <img src="<?= e(media_url($vid['image_path'])) ?>" alt="<?= e(localized($vid, 'title')) ?>" loading="lazy">
              <div class="ad-video-item-play">▶</div>
            </a>
          </div>
          <p class="ad-video-item-title">
            <a href="<?= e(url('news/'.$vid['slug'])) ?>"><?= e(localized($vid, 'title')) ?></a>
          </p>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- Right Column Banner 2 -->
    <div class="ad-col-banner" style="margin:14px 0 10px;">
      <a href="#" aria-label="Commercial Offers">
        <img src="<?= e(url('assets/images/news/banner_retail.jpg')) ?>" alt="Special Offers" width="320" height="85">
      </a>
    </div>

    <!-- SMS Alert Box -->
    <div class="ad-sms-alert-box">
      <div class="ad-sms-title"><?= e(setting('site_name', 'Lanka News Hub')) ?> SMS News Alert</div>
      <div class="ad-sms-desc">Type REG [space] NEWS and send to 2424</div>
    </div>
  </aside>

</div><!-- /.ad-main-grid -->

<!-- ==========================================================================
     DYNAMIC CATEGORY SECTIONS (SPORTS, SCI/TECH, ENTERTAINMENT, ETC.)
     ========================================================================== -->
<?php if (!empty($categorySections)): ?>
  <?php foreach ($categorySections as $sec): ?>
    <?php 
      $desk = $sec['category']; 
      $deskStories = $sec['stories']; 
      if (empty($deskStories)) continue;
      $deskSlug = $desk['slug'] ?? '';
      $deskName = localized($desk, 'name') ?: ($desk['name_en'] ?? 'News');
    ?>

    <?php if ($deskSlug === 'sports'): ?>
      <!-- SPORTS SECTION LAYOUT -->
      <section class="ad-category-block" aria-label="Sports News">
        <div class="ad-block-header">
          <h2><?= e($deskName) ?></h2>
          <a href="<?= e(url('category/'.$deskSlug)) ?>" class="ad-more-link"><?= e(t('More news')) ?> »</a>
        </div>

        <div class="ad-sports-grid row g-4">
          <?php if (isset($deskStories[0])): $s1 = $deskStories[0]; ?>
            <div class="col-12 col-md-6 col-lg-5">
              <article class="ad-sports-hero h-100">
                <a href="<?= e(url('news/'.$s1['slug'])) ?>">
                  <img src="<?= e(media_url($s1['image_path'])) ?>" alt="<?= e(localized($s1, 'title')) ?>" width="450" height="280" loading="lazy">
                </a>
                <h3><a href="<?= e(url('news/'.$s1['slug'])) ?>"><?= e(localized($s1, 'title')) ?></a></h3>
                <p><?= e(localized($s1, 'summary')) ?></p>
                <div class="ad-date"><?= e(date('d M', strtotime($s1['published_at']))) ?></div>
              </article>
            </div>
          <?php endif; ?>

          <?php if (isset($deskStories[1])): $s2 = $deskStories[1]; ?>
            <div class="col-12 col-md-6 col-lg-4">
              <article class="ad-sports-hero h-100">
                <a href="<?= e(url('news/'.$s2['slug'])) ?>">
                  <img src="<?= e(media_url($s2['image_path'])) ?>" alt="<?= e(localized($s2, 'title')) ?>" width="450" height="280" loading="lazy">
                </a>
                <h3><a href="<?= e(url('news/'.$s2['slug'])) ?>"><?= e(localized($s2, 'title')) ?></a></h3>
                <p><?= e(localized($s2, 'summary')) ?></p>
                <div class="ad-date"><?= e(date('d M', strtotime($s2['published_at']))) ?></div>
              </article>
            </div>
          <?php endif; ?>

          <div class="col-12 col-lg-3">
            <div class="ad-sports-compact-col h-100">
              <?php foreach (array_slice($deskStories, 2, 4) as $sItem): ?>
                <div class="ad-sports-compact-item">
                  <a href="<?= e(url('news/'.$sItem['slug'])) ?>">
                    <img src="<?= e(media_url($sItem['image_path'])) ?>" alt="<?= e(localized($sItem, 'title')) ?>" loading="lazy">
                  </a>
                  <h4><a href="<?= e(url('news/'.$sItem['slug'])) ?>"><?= e(localized($sItem, 'title')) ?></a></h4>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>

        <?php if (count($deskStories) > 6): ?>
          <div class="ad-sports-footer-links">
            <?php foreach (array_slice($deskStories, 6, 3) as $footItem): ?>
              <a href="<?= e(url('news/'.$footItem['slug'])) ?>">• <?= e(localized($footItem, 'title')) ?></a>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </section>

    <?php elseif ($deskSlug === 'technology' || $deskSlug === 'entertainment' || $deskSlug === 'international' || $deskSlug === 'business'): ?>
      <!-- SPLIT GRID CATEGORY LAYOUT (Hero Left + 6 Cards Right) -->
      <section class="ad-category-block" aria-label="<?= e($deskName) ?>">
        <div class="ad-block-header">
          <h2><?= e($deskName) ?></h2>
          <a href="<?= e(url('category/'.$deskSlug)) ?>" class="ad-more-link"><?= e(t('More news')) ?> »</a>
        </div>

        <div class="ad-split-grid row g-4">
          <?php if (isset($deskStories[0])): $cHero = $deskStories[0]; ?>
            <div class="col-12 col-lg-5">
              <article class="ad-split-hero h-100">
                <a href="<?= e(url('news/'.$cHero['slug'])) ?>">
                  <img src="<?= e(media_url($cHero['image_path'])) ?>" alt="<?= e(localized($cHero, 'title')) ?>" width="450" height="280" loading="lazy">
                </a>
                <h3><a href="<?= e(url('news/'.$cHero['slug'])) ?>"><?= e(localized($cHero, 'title')) ?></a></h3>
                <p><?= e(localized($cHero, 'summary')) ?></p>
                <div class="ad-date"><?= e(date('d M', strtotime($cHero['published_at']))) ?></div>
              </article>
            </div>
          <?php endif; ?>

          <div class="col-12 col-lg-7">
            <div class="ad-six-grid row row-cols-2 row-cols-sm-3 g-3">
              <?php foreach (array_slice($deskStories, 1, 6) as $cCard): ?>
                <div class="col">
                  <div class="ad-six-card h-100">
                    <a href="<?= e(url('news/'.$cCard['slug'])) ?>">
                      <img src="<?= e(media_url($cCard['image_path'])) ?>" alt="<?= e(localized($cCard, 'title')) ?>" loading="lazy">
                    </a>
                    <h4><a href="<?= e(url('news/'.$cCard['slug'])) ?>"><?= e(localized($cCard, 'title')) ?></a></h4>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </section>

    <?php else: ?>
      <!-- STANDARD SPOTLIGHT BLOCK -->
      <section class="ad-category-block" aria-label="<?= e($deskName) ?>">
        <div class="ad-block-header">
          <h2><?= e($deskName) ?></h2>
          <a href="<?= e(url('category/'.$deskSlug)) ?>" class="ad-more-link"><?= e(t('More news')) ?> »</a>
        </div>
        <div class="ad-six-grid row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-4">
          <?php foreach (array_slice($deskStories, 0, 4) as $cCard): ?>
            <div class="col">
              <div class="ad-six-card h-100">
                <a href="<?= e(url('news/'.$cCard['slug'])) ?>">
                  <img src="<?= e(media_url($cCard['image_path'])) ?>" alt="<?= e(localized($cCard, 'title')) ?>" loading="lazy">
                </a>
                <h4><a href="<?= e(url('news/'.$cCard['slug'])) ?>"><?= e(localized($cCard, 'title')) ?></a></h4>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </section>
    <?php endif; ?>

  <?php endforeach; ?>
<?php endif; ?>

<?php require __DIR__.'/footer.php'; ?>
