<?php 
require __DIR__.'/header.php'; 
$share = url('news/'.$article['slug'], ['lang' => lang()]); 
$encoded = rawurlencode($share); 
$shareTitle = rawurlencode($title); 
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

<div class="ad-article-layout-page">
  <div class="row g-4 align-items-start">
    
    <!-- ==========================================
         LEFT / MAIN COLUMN (ARTICLE CONTENT ~68%)
         ========================================== -->
    <main id="main" class="col-12 col-lg-8 ad-article-main-col">
      <article class="ad-article-content-card">
        
        <!-- Category Badge / Eyebrow -->
        <div class="ad-article-category-wrap">
          <a href="<?= e(url('category/'.$article['category_slug'])) ?>" class="ad-article-category-badge">
            <?= e($article['category_name']) ?>
          </a>
        </div>

        <!-- Headline -->
        <h1 class="ad-article-headline">
          <?= e($title) ?>
        </h1>

        <!-- Meta Bar: Time ago on left, Google News + Share on right -->
        <div class="ad-article-meta-bar">
          <div class="ad-article-meta-time">
            <time datetime="<?= e(date('c', strtotime($article['published_at']))) ?>">
              <?= e(time_ago($article['published_at'])) ?>
            </time>
          </div>

          <div class="ad-article-actions">
            <!-- Add on Google News -->
            <a href="https://news.google.com" target="_blank" rel="noopener" class="ad-google-news-pill" title="Add on Google News">
              <svg viewBox="0 0 24 24" width="16" height="16">
                <path fill="#4285F4" d="M23.745 12.27c0-.7-.06-1.4-.19-2.07H12v4.51h6.6c-.29 1.52-1.14 2.8-2.4 3.66v3.02h3.87c2.27-2.09 3.675-5.17 3.675-9.12z"/>
                <path fill="#34A853" d="M12 24c3.24 0 5.95-1.08 7.93-2.91l-3.87-3.02c-1.08.72-2.45 1.16-4.06 1.16-3.13 0-5.78-2.11-6.73-4.96H1.27v3.12C3.26 21.36 7.36 24 12 24z"/>
                <path fill="#FBBC05" d="M5.27 14.27c-.25-.72-.38-1.49-.38-2.27s.14-1.55.38-2.27V6.61H1.27C.46 8.23 0 10.06 0 12s.46 3.77 1.27 5.39l4-3.12z"/>
                <path fill="#EA4335" d="M12 4.77c1.77 0 3.35.61 4.6 1.8l3.42-3.42C17.95 1.19 15.24 0 12 0 7.36 0 3.26 2.64 1.27 6.61l4 3.12c.95-2.85 3.6-4.96 6.73-4.96z"/>
              </svg>
              <span>Add <?= e(setting('site_name', 'Ada Derana')) ?> on Google</span>
            </a>

            <!-- Share Button & Popup -->
            <div class="ad-share-wrap">
              <button type="button" class="ad-share-btn" onclick="document.getElementById('adShareMenu').classList.toggle('show');" aria-label="Share story">
                <span>Share</span>
                <svg viewBox="0 0 24 24" width="13" height="13" fill="currentColor"><path d="M18 16.08c-.76 0-1.44.3-1.96.77L8.91 12.7c.05-.23.09-.46.09-.7s-.04-.47-.09-.7l7.05-4.11c.54.5 1.25.81 2.04.81 1.66 0 3-1.34 3-3s-1.34-3-3-3-3 1.34-3 3c0 .24.04.47.09.7L8.04 9.81C7.5 9.31 6.79 9 6 9c-1.66 0-3 1.34-3 3s1.34 3 3 3c.79 0 1.5-.31 2.04-.81l7.12 4.16c-.05.21-.08.43-.08.65 0 1.61 1.31 2.92 2.92 2.92 1.61 0 2.92-1.31 2.92-2.92s-1.31-2.92-2.92-2.92z"/></svg>
              </button>
              <div id="adShareMenu" class="ad-share-dropdown-menu">
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?= $encoded ?>" target="_blank" rel="noopener">
                  <svg viewBox="0 0 24 24" width="14" height="14" fill="#1877f2" aria-hidden="true" style="margin-right:8px; vertical-align:-2px;"><path d="M13.397 20.997v-8.196h2.765l.411-3.209h-3.176V7.548c0-.926.258-1.56 1.587-1.56h1.684V3.127A22.336 22.336 0 0 0 14.201 3c-2.444 0-4.122 1.492-4.122 4.231v2.355H7.332v3.209h2.753v8.202h3.312z"/></svg>
                  Facebook
                </a>
                <a href="https://twitter.com/intent/tweet?url=<?= $encoded ?>&text=<?= $shareTitle ?>" target="_blank" rel="noopener">
                  <svg viewBox="0 0 24 24" width="13" height="13" fill="#000000" aria-hidden="true" style="margin-right:8px; vertical-align:-2px;"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                  X (Twitter)
                </a>
                <a href="https://wa.me/?text=<?= $shareTitle ?>%20<?= $encoded ?>" target="_blank" rel="noopener">
                  <svg viewBox="0 0 24 24" width="14" height="14" fill="#25d366" aria-hidden="true" style="margin-right:8px; vertical-align:-2px;"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                  WhatsApp
                </a>
                <a href="https://t.me/share/url?url=<?= $encoded ?>&text=<?= $shareTitle ?>" target="_blank" rel="noopener">
                  <svg viewBox="0 0 24 24" width="14" height="14" fill="#229ed9" aria-hidden="true" style="margin-right:8px; vertical-align:-2px;"><path d="m20.665 3.717-17.73 6.837c-1.21.486-1.203 1.161-.222 1.462l4.552 1.42 10.532-6.645c.498-.303.953-.14.579.192l-8.533 7.701h-.002l-.313 4.674c.459 0 .661-.21.917-.46l2.203-2.14 4.58 3.385c.844.464 1.452.226 1.663-.785l3.007-14.169c.308-1.233-.472-1.793-1.463-1.472z"/></svg>
                  Telegram
                </a>
                <button type="button" onclick="navigator.clipboard.writeText('<?= $share ?>'); alert('Link copied to clipboard!');">
                  <svg viewBox="0 0 24 24" width="13" height="13" fill="#666" aria-hidden="true" style="margin-right:8px; vertical-align:-2px;"><path d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/></svg>
                  Copy Link
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Featured Image -->
        <?php if ($article['image_path']): ?>
          <div class="ad-article-featured-image">
            <img src="<?= e(media_url($article['image_path'])) ?>" alt="<?= e($title) ?>" width="820" height="460" loading="eager">
          </div>
        <?php endif; ?>

        <!-- Dual In-Article / Feed Banners (Left & Right) -->
        <?php 
          $feedLeftAd = \App\Helpers\AdHelper::render('feed_left');
          $feedRightAd = \App\Helpers\AdHelper::render('feed_right');
        ?>
        <div class="ad-feed-dual-banners my-3" aria-label="Sponsored Advertisements">
          <div class="ad-banner-strip">
            <?= $feedLeftAd ?: '<a href="#" aria-label="Singer Sri Lanka Sponsor"><img src="'.e(url('assets/images/news/banner_feed_left.jpg')).'" alt="Singer Sri Lanka" width="590" height="100" loading="lazy"></a>' ?>
          </div>
          <div class="ad-banner-strip">
            <?= $feedRightAd ?: '<a href="#" aria-label="Keells Supermarket Sponsor"><img src="'.e(url('assets/images/news/banner_feed_right.jpg')).'" alt="Keells Supermarket" width="590" height="100" loading="lazy"></a>' ?>
          </div>
        </div>

        <!-- Follow Ada Derana Page on Facebook Banner -->
        <a href="https://facebook.com" target="_blank" rel="noopener" class="ad-article-fb-banner my-3">
          <div class="ad-afb-left">
            <span class="ad-afb-text">Follow <?= e(setting('site_name', 'Ada Derana')) ?> Page on Facebook</span>
          </div>
          <div class="ad-afb-right">
            <span class="ad-afb-join">Join Now</span>
            <span class="ad-afb-logo">facebook</span>
          </div>
        </a>

        <!-- Video Player (if attached) -->
        <?php if ($video): ?>
          <div class="ad-article-video-wrap my-3">
            <video controls preload="metadata" class="w-100" style="border-radius:3px;">
              <source src="<?= e(media_url($video['file_path'])) ?>" type="<?= e($video['file_type']) ?>">
            </video>
          </div>
        <?php endif; ?>
        <?php if ($youtube = youtube_id($article['video_url'] ?? '')): ?>
          <div class="ad-article-video-wrap my-3 ratio ratio-16x9">
            <iframe src="https://www.youtube-nocookie.com/embed/<?= e($youtube) ?>" title="Story video" allowfullscreen style="border-radius:3px;"></iframe>
          </div>
        <?php endif; ?>

        <!-- Full Story Body HTML Content -->
        <div class="ad-article-content-body">
          <?= rich_html(localized($article, 'content')) ?>
        </div>

        <!-- Photo Gallery (Showcase Viewer & Lightbox) -->
        <?php if (!empty($gallery)): ?>
          <div class="ad-article-gallery my-4" id="articleGallery">
            <div class="ad-gallery-header">
              <div class="ad-gallery-title-wrap">
                <span class="ad-gallery-icon">📷</span>
                <h3 class="ad-gallery-heading"><?= e(t('Photo gallery')) ?></h3>
                <span class="ad-gallery-count"><?= count($gallery) ?> <?= count($gallery) === 1 ? 'Photo' : 'Photos' ?></span>
              </div>
              <span class="ad-gallery-hint">Click any photo to enlarge ⤢</span>
            </div>

            <!-- Featured Stage -->
            <div class="ad-gallery-stage">
              <div class="ad-gallery-stage-viewport" onclick="openGalleryLightbox(currentGalleryIndex)" title="Click to view full screen">
                <img id="adGalleryStageImg" src="<?= e(media_url($gallery[0]['file_path'])) ?>" alt="<?= e($gallery[0]['alt_text'] ?: ($gallery[0]['caption'] ?: $title)) ?>" loading="eager">
                <div class="ad-gallery-stage-overlay">
                  <span class="ad-gallery-stage-counter" id="adGalleryCounter">1 / <?= count($gallery) ?></span>
                  <button type="button" class="ad-gallery-zoom-btn" title="View Fullscreen" aria-label="View Fullscreen">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="#fff"><path d="M7 14H5v5h5v-2H7v-3zm-2-4h2V7h3V5H5v5zm12 7h-3v2h5v-5h-2v3zM14 5v2h3v3h2V5h-5z"/></svg>
                  </button>
                </div>
                <div class="ad-gallery-stage-caption" id="adGalleryCaption">
                  <?= e($gallery[0]['caption'] ?: ($gallery[0]['alt_text'] ?: $title)) ?>
                </div>
              </div>
              <?php if (count($gallery) > 1): ?>
                <button type="button" class="ad-gallery-nav-btn prev" onclick="navigateGallery(-1)" aria-label="Previous photo">❮</button>
                <button type="button" class="ad-gallery-nav-btn next" onclick="navigateGallery(1)" aria-label="Next photo">❯</button>
              <?php endif; ?>
            </div>

            <!-- Thumbnail Strip -->
            <?php if (count($gallery) > 1): ?>
              <div class="ad-gallery-thumbs-grid">
                <?php foreach ($gallery as $idx => $photo): ?>
                  <button type="button" class="ad-gallery-thumb-item <?= $idx === 0 ? 'active' : '' ?>" onclick="selectGalleryPhoto(<?= $idx ?>)" data-index="<?= $idx ?>" aria-label="View photo <?= $idx + 1 ?>">
                    <img src="<?= e(media_url($photo['file_path'])) ?>" alt="<?= e($photo['alt_text'] ?: ($photo['caption'] ?: 'Photo '.($idx+1))) ?>" loading="lazy">
                    <span class="ad-gallery-thumb-num"><?= $idx + 1 ?></span>
                  </button>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
        <?php endif; ?>

        <!-- Byline / Source Attribution -->
        <div class="ad-article-source-attribution mt-3">
          <p>Source: <?= e($article['author_name'] ?: 'Agencies') ?></p>
        </div>

        <!-- Story Tags -->
        <?php if ($article['tags']): ?>
          <div class="ad-article-tags-wrap my-3">
            <?php foreach (array_filter(array_map('trim', explode(',', $article['tags']))) as $tag): ?>
              <a href="<?= e(url('search', ['q' => $tag])) ?>" class="ad-tag-pill">#<?= e($tag) ?></a>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <!-- Join Ada Derana WhatsApp Channel Banner -->
        <a href="https://whatsapp.com" target="_blank" rel="noopener" class="ad-article-wa-banner my-4">
          <div class="ad-awa-left">
            <span class="ad-awa-text">Join <?= e(setting('site_name', 'Ada Derana')) ?> WhatsApp Channel</span>
          </div>
          <div class="ad-awa-right">
            <span class="ad-awa-join">Join Now</span>
            <span class="ad-awa-logo">WhatsApp</span>
          </div>
        </a>

      </article>
    </main>

    <!-- ==========================================
         RIGHT COLUMN: SIDEBAR (~32%)
         ========================================== -->
    <aside class="col-12 col-lg-4 ad-article-sidebar">
      
      <!-- Most Read Widget -->
      <?php if (!empty($mostRead)): ?>
        <div class="ad-sidebar-box ad-most-read-widget mb-4">
          <div class="ad-widget-header">
            <h3>Most Read</h3>
          </div>
          <div class="ad-most-read-list">
            <?php foreach ($mostRead as $mStory): ?>
              <article class="ad-most-read-item">
                <a href="<?= e(url('news/'.$mStory['slug'])) ?>" class="ad-most-read-thumb">
                  <img src="<?= e(media_url($mStory['image_path'])) ?>" alt="<?= e(localized($mStory, 'title')) ?>" loading="lazy" width="75" height="52">
                  <?php if (!empty($mStory['video_url']) || !empty($mStory['video_media_id'])): ?>
                    <span class="ad-mini-media-badge">▶</span>
                  <?php elseif (!empty($mStory['gallery_ids']) && $mStory['gallery_ids'] !== '[]'): ?>
                    <span class="ad-mini-media-badge">📷</span>
                  <?php endif; ?>
                </a>
                <div class="ad-most-read-info">
                  <h4 class="ad-most-read-title">
                    <a href="<?= e(url('news/'.$mStory['slug'])) ?>"><?= e(localized($mStory, 'title')) ?></a>
                  </h4>
                  <time class="ad-most-read-time"><?= e(time_ago($mStory['published_at'])) ?></time>
                </div>
              </article>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>

      <!-- 300x100 / 300x250 Banner Ad -->
      <div class="ad-sidebar-slot mb-4">
        <?= \App\Helpers\AdHelper::render('sidebar_300x250') ?: '
        <a href="#" aria-label="Commercial Sponsor">
          <img src="'.e(url('assets/images/adaderana/banner_retail.jpg')).'" alt="Commercial Sponsor" width="300" height="250" style="max-width:100%; height:auto; display:block; border-radius:2px;">
        </a>' ?>
      </div>

      <!-- Video Stories Widget -->
      <?php if (!empty($videoStories)): ?>
        <?php 
          $topVideo = $videoStories[0];
          $sideVideos = array_slice($videoStories, 1, 4);
        ?>
        <div class="ad-video-widget mb-4">
          <div class="ad-widget-header">
            <h3><?= e(t('Video Stories')) ?></h3>
          </div>
          
          <!-- Top Featured Video -->
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

          <!-- Side Videos Stream -->
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

      <!-- 300x600 Half-Page Banner Ad -->
      <div class="ad-sidebar-slot mb-4">
        <?= \App\Helpers\AdHelper::render('sidebar_300x600') ?: '
        <a href="#" aria-label="Farm to Doorstep Sponsor">
          <img src="'.e(url('assets/images/adaderana/banner_metro.jpg')).'" alt="Farm to Doorstep Sponsor" width="300" height="600" style="max-width:100%; height:auto; display:block; border-radius:2px;">
        </a>' ?>
      </div>

    </aside>
  </div>
</div>

<!-- Fullscreen Lightbox Modal -->
<?php if (!empty($gallery)): ?>
<div id="adGalleryLightbox" class="ad-gallery-lightbox" role="dialog" aria-modal="true" style="display:none;">
  <div class="ad-lightbox-backdrop" onclick="closeGalleryLightbox()"></div>
  <div class="ad-lightbox-wrapper">
    <div class="ad-lightbox-top-bar">
      <span class="ad-lightbox-counter-badge" id="adLightboxCounter">1 / <?= count($gallery) ?></span>
      <button type="button" class="ad-lightbox-close-btn" onclick="closeGalleryLightbox()" aria-label="Close Lightbox">✕</button>
    </div>
    <div class="ad-lightbox-stage-row">
      <?php if (count($gallery) > 1): ?>
        <button type="button" class="ad-lightbox-arrow-btn prev" onclick="navigateGallery(-1)" aria-label="Previous">❮</button>
      <?php endif; ?>
      <div class="ad-lightbox-photo-box">
        <img id="adLightboxImg" src="" alt="Gallery Photo">
      </div>
      <?php if (count($gallery) > 1): ?>
        <button type="button" class="ad-lightbox-arrow-btn next" onclick="navigateGallery(1)" aria-label="Next">❯</button>
      <?php endif; ?>
    </div>
    <div class="ad-lightbox-caption-bar" id="adLightboxCaption"></div>
  </div>
</div>

<script>
const galleryItems = <?= json_encode(array_map(function($p) use ($title) {
  return [
    'src' => media_url($p['file_path']),
    'caption' => $p['caption'] ?: ($p['alt_text'] ?: $title),
    'alt' => $p['alt_text'] ?: ($p['caption'] ?: $title)
  ];
}, $gallery)) ?>;

let currentGalleryIndex = 0;

function updateGalleryStage(index) {
  if (!galleryItems || galleryItems.length === 0) return;
  if (index < 0) index = galleryItems.length - 1;
  if (index >= galleryItems.length) index = 0;
  currentGalleryIndex = index;

  const item = galleryItems[index];
  const stageImg = document.getElementById('adGalleryStageImg');
  const counter = document.getElementById('adGalleryCounter');
  const caption = document.getElementById('adGalleryCaption');

  if (stageImg) {
    stageImg.style.opacity = '0.3';
    stageImg.src = item.src;
    stageImg.alt = item.alt;
    setTimeout(() => { stageImg.style.opacity = '1'; }, 100);
  }
  if (counter) counter.textContent = (index + 1) + ' / ' + galleryItems.length;
  if (caption) caption.textContent = item.caption;

  document.querySelectorAll('.ad-gallery-thumb-item').forEach((thumb, i) => {
    if (i === index) thumb.classList.add('active');
    else thumb.classList.remove('active');
  });

  const lbImg = document.getElementById('adLightboxImg');
  const lbCounter = document.getElementById('adLightboxCounter');
  const lbCaption = document.getElementById('adLightboxCaption');
  if (lbImg) {
    lbImg.src = item.src;
    lbImg.alt = item.alt;
  }
  if (lbCounter) lbCounter.textContent = (index + 1) + ' / ' + galleryItems.length;
  if (lbCaption) lbCaption.textContent = item.caption;
}

function selectGalleryPhoto(index) {
  updateGalleryStage(index);
}

function navigateGallery(step) {
  updateGalleryStage(currentGalleryIndex + step);
}

function openGalleryLightbox(index) {
  updateGalleryStage(index);
  const lb = document.getElementById('adGalleryLightbox');
  if (lb) {
    lb.style.display = 'flex';
    document.body.style.overflow = 'hidden';
  }
}

function closeGalleryLightbox() {
  const lb = document.getElementById('adGalleryLightbox');
  if (lb) {
    lb.style.display = 'none';
    document.body.style.overflow = '';
  }
}

document.addEventListener('keydown', function(e) {
  const lb = document.getElementById('adGalleryLightbox');
  if (lb && lb.style.display === 'flex') {
    if (e.key === 'Escape') closeGalleryLightbox();
    else if (e.key === 'ArrowLeft') navigateGallery(-1);
    else if (e.key === 'ArrowRight') navigateGallery(1);
  }
});
</script>
<?php endif; ?>

<script>
document.addEventListener('click', function(e) {
  var menu = document.getElementById('adShareMenu');
  if (menu && !e.target.closest('.ad-share-wrap')) {
    menu.classList.remove('show');
  }
});
</script>

<?php require __DIR__.'/footer.php'; ?>
