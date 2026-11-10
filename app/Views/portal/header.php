<!doctype html>
<html lang="<?= e(lang()) ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($title ?? setting('site_name', 'Lanka News Hub')) ?></title>
  <link rel="stylesheet" href="<?= e(url('assets/fonts/fonts.css')) ?>">
  <link rel="stylesheet" href="<?= e(url('assets/css/bootstrap.min.css')) ?>">
  <link rel="stylesheet" href="<?= e(url('assets/css/portal-broadsheet.css')) ?>?v=<?= time() ?>">
  <link rel="stylesheet" href="<?= e(url('assets/css/portal.css')) ?>?v=<?= time() ?>">
  <script src="<?= e(url('assets/js/bootstrap.bundle.min.js')) ?>" defer></script>
  <script src="<?= e(url('assets/js/portal.js')) ?>?v=5" defer></script>
</head>
<body class="news-site broadsheet-portal">
<?php if (APP_ENV==='demo'): ?>
  <div class="demo-banner" style="background:#fef3c7; color:#92400e; padding:4px 10px; font-size:11px; text-align:center; font-weight:600;">
    DEMO PREVIEW · Active News Hub Edition (<?= strtoupper(lang()) ?>)
  </div>
<?php endif; ?>
<?php if (\App\Helpers\AdHelper::isPreviewMode()): ?>
  <div class="ad-preview-floating-bar" role="status"><span>👁️ <strong>Ad Placement Preview Active</strong> (All 11 slots visible)</span><a href="?preview_ads=0" title="Exit placement preview">Exit preview ✕</a></div>
<?php endif; ?>

<!-- Top Network Bar (Desktop & Tablet) -->
<div class="ad-top-network-bar d-none d-md-block">
  <div class="ad-top-network-inner">
    <a href="<?= e(url('edition/videos')) ?>">Live TV</a>
    <span>|</span>
    <a href="<?= e(url('edition/videos')) ?>">News 24</a>
    <span>|</span>
    <a href="<?= e(url('category/business')) ?>">Business Wire</a>
    <span>|</span>
    <a href="<?= e(url('category/sports')) ?>">Sports Central</a>
    <span>|</span>
    <a href="<?= e(url('category/technology')) ?>">Tech Desk</a>
    <span>|</span>
    <a href="<?= e(url('category/entertainment')) ?>">Life & Culture</a>
    <span>|</span>
    <a href="<?= e(url('classifieds')) ?>" style="color:#2563eb; font-weight:700;">Classifieds ↗</a>
    <span>|</span>
    <a href="<?= e(url('account')) ?>">Member Account</a>
  </div>
</div>

<div class="ad-wrapper container-xl">
  <!-- Main Brand Header -->
  <header class="ad-header-main">
    <div class="d-flex align-items-center justify-content-between w-100 flex-wrap gap-2">
      <!-- Brand on Left -->
      <div class="ad-header-brand">
        <a href="<?= e(url()) ?>" aria-label="<?= e(setting('site_name', 'Lanka News Hub')) ?>" style="display:inline-flex; align-items:center; gap:12px;">
          <div style="background:#c4161c; width:48px; height:48px; border-radius:8px; display:flex; flex-direction:column; align-items:center; justify-content:center; color:#fff; flex-shrink:0;">
            <span style="font-family:'Arial Black', Impact, sans-serif; font-size:16px; font-weight:900; line-height:1; letter-spacing:0.5px;">LN</span>
            <span style="font-family:'Noto Sans Sinhala', 'Iskoola Pota', sans-serif; font-size:13px; font-weight:700; line-height:1.2;">ලංකා</span>
          </div>
          <div style="display:flex; flex-direction:column;">
            <span style="font-family:Arial, Helvetica, sans-serif; font-weight:900; font-size:clamp(20px, 4vw, 29px); color:#c4161c; line-height:1.05; letter-spacing:-0.5px;">
              <?= e(setting('site_name', 'Lanka News Hub')) ?>
            </span>
            <span class="d-none d-sm-block" style="font-family:Arial, Helvetica, sans-serif; font-size:10.5px; font-weight:700; color:#555; letter-spacing:2px; margin-top:3px;">
              <?= e(setting('site_tagline', 'TRUTH FIRST · NATIONAL PERSPECTIVE')) ?>
            </span>
          </div>
        </a>
      </div>

      <!-- Mobile Right Controls (Only on mobile < 992px) -->
      <div class="d-flex d-lg-none align-items-center gap-2 ms-auto">
        <!-- Compact Language Switcher on Mobile -->
        <div class="btn-group btn-group-sm" role="group" aria-label="Language selection">
          <?php foreach (['en'=>'EN','si'=>'සිං','ta'=>'த'] as $code=>$label): ?>
            <?php if (setting('language_'.$code, '1') !== '1') continue; ?>
            <a class="btn btn-sm <?= lang()===$code ? 'btn-danger' : 'btn-outline-secondary' ?> py-1 px-2" 
               style="font-size:12px; font-weight:700;" 
               href="<?= e(edition_url($code)) ?>" 
               lang="<?= e($code) ?>">
              <?= e($label) ?>
            </a>
          <?php endforeach; ?>
        </div>

        <!-- Mobile Search Toggle Button -->
        <button class="btn btn-sm btn-outline-secondary py-1 px-2" type="button" data-bs-toggle="collapse" data-bs-target="#mobileQuickSearch" aria-expanded="false" aria-controls="mobileQuickSearch" aria-label="Search" style="font-size:14px;">
          ⌕
        </button>

        <!-- Mobile Offcanvas Toggler Button -->
        <button class="btn btn-sm btn-danger py-1 px-2 text-white" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileNavOffcanvas" aria-controls="mobileNavOffcanvas" aria-label="Toggle navigation" style="font-size:16px; font-weight:bold; line-height:1.2;">
          ☰
        </button>
      </div>

      <!-- Desktop Header Right (>= 992px) -->
      <div class="d-none d-lg-flex flex-column align-items-end ms-auto gap-2">
        <div class="ad-header-meta">
          <span><?= e(date('l, d F Y')) ?> | Colombo</span>
        </div>
        <div class="ad-social-row">
          <div class="ad-social-icons-group">
            <a href="https://facebook.com" target="_blank" rel="noopener" class="ad-social-icon social-fb" title="Facebook" aria-label="Facebook">
              <svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor" aria-hidden="true"><path d="M13.397 20.997v-8.196h2.765l.411-3.209h-3.176V7.548c0-.926.258-1.56 1.587-1.56h1.684V3.127A22.336 22.336 0 0 0 14.201 3c-2.444 0-4.122 1.492-4.122 4.231v2.355H7.332v3.209h2.753v8.202h3.312z"/></svg>
            </a>
            <a href="https://twitter.com" target="_blank" rel="noopener" class="ad-social-icon social-x" title="X (Twitter)" aria-label="X (Twitter)">
              <svg viewBox="0 0 24 24" width="13" height="13" fill="currentColor" aria-hidden="true"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
            </a>
            <a href="https://youtube.com" target="_blank" rel="noopener" class="ad-social-icon social-yt" title="YouTube" aria-label="YouTube">
              <svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor" aria-hidden="true"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
            </a>
            <a href="https://instagram.com" target="_blank" rel="noopener" class="ad-social-icon social-ig" title="Instagram" aria-label="Instagram">
              <svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor" aria-hidden="true"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838a6.162 6.162 0 1 0 0 12.324 6.162 6.162 0 0 0 0-12.324zM12 16a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm6.406-11.845a1.44 1.44 0 1 0 0 2.881 1.44 1.44 0 0 0 0-2.881z"/></svg>
            </a>
            <a href="https://whatsapp.com" target="_blank" rel="noopener" class="ad-social-icon social-wa" title="WhatsApp" aria-label="WhatsApp">
              <svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor" aria-hidden="true"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
            </a>
            <a href="https://telegram.org" target="_blank" rel="noopener" class="ad-social-icon social-tg" title="Telegram" aria-label="Telegram">
              <svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor" aria-hidden="true"><path d="m20.665 3.717-17.73 6.837c-1.21.486-1.203 1.161-.222 1.462l4.552 1.42 10.532-6.645c.498-.303.953-.14.579.192l-8.533 7.701h-.002l-.313 4.674c.459 0 .661-.21.917-.46l2.203-2.14 4.58 3.385c.844.464 1.452.226 1.663-.785l3.007-14.169c.308-1.233-.472-1.793-1.463-1.472z"/></svg>
            </a>
          </div>
          
          <div class="ad-header-actions-group">
            <?php foreach (['en'=>'English','si'=>'සිංහල','ta'=>'தமிழ்'] as $code=>$label): ?>
              <?php if (setting('language_'.$code, '1') !== '1') continue; ?>
              <a class="ad-lang-btn <?= lang()===$code ? 'active' : '' ?>" 
                 style="<?= lang()===$code ? 'background:#8b0d11; box-shadow:0 0 0 1px #fff;' : '' ?>" 
                 href="<?= e(edition_url($code)) ?>" 
                 lang="<?= e($code) ?>">
                <?= e($label) ?>
              </a>
            <?php endforeach; ?>
            
            <form action="<?= e(url('search')) ?>" method="get" class="ad-search-form" style="display:inline-flex; align-items:center;">
              <input type="search" name="q" value="<?= e($_GET['q'] ?? '') ?>" placeholder="<?= e(t('Search')) ?>…" aria-label="<?= e(t('Search')) ?>" style="padding:5px 12px; font-size:12.5px; border:1px solid #ccc; border-radius:14px; width:125px;">
              <button type="submit" class="ad-search-trigger" style="border:none; margin-left:4px;" aria-label="<?= e(t('Search')) ?>">⌕</button>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- Mobile Collapsible Quick Search Box (Underneath Header) -->
    <div class="collapse d-lg-none mt-2" id="mobileQuickSearch">
      <form action="<?= e(url('search')) ?>" method="get" class="d-flex gap-1">
        <input type="search" name="q" class="form-control form-control-sm" placeholder="<?= e(t('Search news, topics, stories')) ?>…" value="<?= e($_GET['q'] ?? '') ?>" aria-label="<?= e(t('Search')) ?>">
        <button class="btn btn-sm btn-danger px-3" type="submit"><?= e(t('Search')) ?></button>
      </form>
    </div>
  </header>

  <?php 
    $currentPath = rtrim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/'); 
    $isCurrent = static fn($path) => $currentPath === rtrim(parse_url(url($path), PHP_URL_PATH), '/'); 
  ?>

  <!-- Mobile Quick Category Strip (Horizontal Swipe Bar) -->
  <div class="ad-mobile-quick-strip d-flex d-lg-none" role="navigation" aria-label="Mobile Category Quick Bar">
    <a href="<?= e(url()) ?>" class="<?= $isCurrent('') ? 'active' : '' ?>"><?= e(t('Home')) ?></a>
    <a href="<?= e(url('edition/breaking')) ?>" class="highlight <?= $isCurrent('edition/breaking') ? 'active' : '' ?>">🔴 24/7 Breaking</a>
    <a href="<?= e(url('edition/latest')) ?>" class="<?= $isCurrent('edition/latest') ? 'active' : '' ?>">🔥 <?= e(t('Hot News')) ?></a>
    <?php foreach ($categories as $cat): ?>
      <?php 
        $catActive = in_array($cat['slug'], $cat['category_slugs'] ?? [$cat['slug']], true) && (
          $isCurrent('category/'.$cat['slug']) || 
          count(array_filter($cat['category_slugs'] ?? [], static fn($s)=>$isCurrent('category/'.$s))) > 0
        );
      ?>
      <a href="<?= e(url('category/'.$cat['slug'])) ?>" class="<?= $catActive ? 'active' : '' ?>">
        <?= e(localized($cat, 'name') ?: $cat['name_en']) ?>
      </a>
    <?php endforeach; ?>
    <a href="<?= e(url('edition/videos')) ?>" class="<?= $isCurrent('edition/videos') ? 'active' : '' ?>">📺 <?= e(t('Videos')) ?></a>
    <a href="<?= e(url('edition/gallery')) ?>" class="<?= $isCurrent('edition/gallery') ? 'active' : '' ?>">📸 <?= e(t('Photo gallery')) ?></a>
    <a href="<?= e(url('classifieds')) ?>" class="text-primary font-weight-bold"><?= e(t('Classifieds')) ?> ↗</a>
  </div>

  <!-- Mobile Offcanvas Drawer Navigation -->
  <div class="offcanvas offcanvas-end d-lg-none" tabindex="-1" id="mobileNavOffcanvas" aria-labelledby="mobileNavOffcanvasLabel" style="width:310px; max-width:85vw;">
    <div class="offcanvas-header border-bottom py-2 bg-light">
      <div class="d-flex align-items-center gap-2">
        <div style="background:#c4161c; width:34px; height:34px; border-radius:6px; display:flex; flex-direction:column; align-items:center; justify-content:center; color:#fff; flex-shrink:0;">
          <span style="font-family:'Arial Black', Impact, sans-serif; font-size:12px; font-weight:900; line-height:1;">LN</span>
          <span style="font-family:'Noto Sans Sinhala', 'Iskoola Pota', sans-serif; font-size:9px; font-weight:700; line-height:1.1;">ලංකා</span>
        </div>
        <div>
          <strong class="d-block text-danger" style="font-size:15px; line-height:1.2;"><?= e(setting('site_name', 'Lanka News Hub')) ?></strong>
          <small class="text-muted" style="font-size:10px;"><?= e(date('l, d M Y')) ?></small>
        </div>
      </div>
      <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>

    <div class="offcanvas-body p-3">
      <!-- Search inside Drawer -->
      <form action="<?= e(url('search')) ?>" method="get" class="mb-3">
        <div class="input-group">
          <input type="search" name="q" class="form-control form-control-sm" placeholder="<?= e(t('Search')) ?>…" value="<?= e($_GET['q'] ?? '') ?>" aria-label="<?= e(t('Search')) ?>">
          <button class="btn btn-sm btn-danger px-3" type="submit">⌕</button>
        </div>
      </form>

      <!-- Language selector in Drawer -->
      <div class="d-flex gap-1 mb-3">
        <?php foreach (['en'=>'English','si'=>'සිංහල','ta'=>'தமிழ்'] as $code=>$label): ?>
          <?php if (setting('language_'.$code, '1') !== '1') continue; ?>
          <a class="btn btn-sm flex-fill <?= lang()===$code ? 'btn-danger' : 'btn-outline-secondary' ?>" 
             style="font-size:12px; font-weight:600;" 
             href="<?= e(edition_url($code)) ?>" 
             lang="<?= e($code) ?>">
            <?= e($label) ?>
          </a>
        <?php endforeach; ?>
      </div>

      <!-- Navigation Links -->
      <div class="ad-drawer-section-title">Editions & Features</div>
      <ul class="ad-drawer-nav-list mb-3">
        <li class="ad-drawer-nav-item">
          <a class="ad-drawer-nav-link <?= $isCurrent('') ? 'active' : '' ?>" href="<?= e(url()) ?>">
            <span>🏠 <?= e(t('Home')) ?></span> <span>›</span>
          </a>
        </li>
        <li class="ad-drawer-nav-item">
          <a class="ad-drawer-nav-link <?= $isCurrent('edition/breaking') ? 'active' : '' ?>" href="<?= e(url('edition/breaking')) ?>" style="color:#c4161c; font-weight:700;">
            <span>🔴 24X7 <?= e(t('Breaking news')) ?></span> <span>›</span>
          </a>
        </li>
        <li class="ad-drawer-nav-item">
          <a class="ad-drawer-nav-link <?= $isCurrent('edition/latest') ? 'active' : '' ?>" href="<?= e(url('edition/latest')) ?>">
            <span>🔥 <?= e(t('Hot News')) ?></span> <span>›</span>
          </a>
        </li>
        <li class="ad-drawer-nav-item">
          <a class="ad-drawer-nav-link <?= $isCurrent('edition/videos') ? 'active' : '' ?>" href="<?= e(url('edition/videos')) ?>">
            <span>📺 <?= e(t('Videos')) ?></span> <span>›</span>
          </a>
        </li>
        <li class="ad-drawer-nav-item">
          <a class="ad-drawer-nav-link <?= $isCurrent('edition/gallery') ? 'active' : '' ?>" href="<?= e(url('edition/gallery')) ?>">
            <span>📸 <?= e(t('Photo gallery')) ?></span> <span>›</span>
          </a>
        </li>
        <li class="ad-drawer-nav-item">
          <a class="ad-drawer-nav-link text-primary fw-bold" href="<?= e(url('classifieds')) ?>">
            <span>💼 <?= e(t('Classifieds')) ?> ↗</span> <span>›</span>
          </a>
        </li>
      </ul>

      <div class="ad-drawer-section-title">Categories</div>
      <ul class="ad-drawer-nav-list mb-3">
        <?php foreach ($categories as $cat): ?>
          <?php 
            $catActive = in_array($cat['slug'], $cat['category_slugs'] ?? [$cat['slug']], true) && (
              $isCurrent('category/'.$cat['slug']) || 
              count(array_filter($cat['category_slugs'] ?? [], static fn($s)=>$isCurrent('category/'.$s))) > 0
            );
          ?>
          <li class="ad-drawer-nav-item">
            <a class="ad-drawer-nav-link <?= $catActive ? 'active' : '' ?>" href="<?= e(url('category/'.$cat['slug'])) ?>">
              <span><?= e(localized($cat, 'name') ?: $cat['name_en']) ?></span>
              <span>›</span>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>

      <!-- Hotline Direct Link -->
      <div class="p-2 mb-3 bg-light rounded text-center">
        <small class="text-muted d-block mb-1">News Hotline</small>
        <a href="tel:<?= e(preg_replace('/[^0-9+]/', '', setting('contact_phone', '0115300800'))) ?>" class="btn btn-sm btn-outline-danger w-100 fw-bold">
          ☎ <?= e(setting('contact_phone', '011 5 300 800')) ?>
        </a>
      </div>

      <!-- Social Media Icons in Drawer -->
      <div class="d-flex justify-content-center gap-2 mb-3 flex-wrap">
        <a href="https://facebook.com" target="_blank" rel="noopener" class="ad-social-icon social-fb" title="Facebook">
          <svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor" aria-hidden="true"><path d="M13.397 20.997v-8.196h2.765l.411-3.209h-3.176V7.548c0-.926.258-1.56 1.587-1.56h1.684V3.127A22.336 22.336 0 0 0 14.201 3c-2.444 0-4.122 1.492-4.122 4.231v2.355H7.332v3.209h2.753v8.202h3.312z"/></svg>
        </a>
        <a href="https://twitter.com" target="_blank" rel="noopener" class="ad-social-icon social-x" title="X (Twitter)">
          <svg viewBox="0 0 24 24" width="13" height="13" fill="currentColor" aria-hidden="true"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
        </a>
        <a href="https://youtube.com" target="_blank" rel="noopener" class="ad-social-icon social-yt" title="YouTube">
          <svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor" aria-hidden="true"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
        </a>
        <a href="https://instagram.com" target="_blank" rel="noopener" class="ad-social-icon social-ig" title="Instagram">
          <svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor" aria-hidden="true"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838a6.162 6.162 0 1 0 0 12.324 6.162 6.162 0 0 0 0-12.324zM12 16a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm6.406-11.845a1.44 1.44 0 1 0 0 2.881 1.44 1.44 0 0 0 0-2.881z"/></svg>
        </a>
        <a href="https://whatsapp.com" target="_blank" rel="noopener" class="ad-social-icon social-wa" title="WhatsApp">
          <svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor" aria-hidden="true"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
        </a>
        <a href="https://telegram.org" target="_blank" rel="noopener" class="ad-social-icon social-tg" title="Telegram">
          <svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor" aria-hidden="true"><path d="m20.665 3.717-17.73 6.837c-1.21.486-1.203 1.161-.222 1.462l4.552 1.42 10.532-6.645c.498-.303.953-.14.579.192l-8.533 7.701h-.002l-.313 4.674c.459 0 .661-.21.917-.46l2.203-2.14 4.58 3.385c.844.464 1.452.226 1.663-.785l3.007-14.169c.308-1.233-.472-1.793-1.463-1.472z"/></svg>
        </a>
      </div>

      <div class="text-center pt-2 border-top">
        <a href="<?= e(url('account')) ?>" class="text-muted small text-decoration-none">Member Account</a> · 
        <a href="<?= e(url('admin')) ?>" class="text-muted small text-decoration-none">Editorial ↗</a>
      </div>
    </div>
  </div>

  <!-- Navigation Bar 1: Light Bar (Desktop Only) -->
  <nav class="ad-nav-light d-none d-lg-flex" aria-label="Main Navigation">
    <a href="<?= e(url()) ?>" <?= $isCurrent('') ? 'class="active"' : '' ?>><?= e(t('Home')) ?></a>
    <a href="<?= e(url('edition/latest')) ?>" <?= $isCurrent('edition/latest') ? 'class="active"' : '' ?>><?= e(t('Hot News')) ?></a>
    <?php foreach ($categories as $cat): ?>
      <?php 
        $catActive = in_array($cat['slug'], $cat['category_slugs'] ?? [$cat['slug']], true) && (
          $isCurrent('category/'.$cat['slug']) || 
          count(array_filter($cat['category_slugs'] ?? [], static fn($s)=>$isCurrent('category/'.$s))) > 0
        );
      ?>
      <a href="<?= e(url('category/'.$cat['slug'])) ?>" <?= $catActive ? 'class="active"' : '' ?>>
        <?= e(localized($cat, 'name') ?: $cat['name_en']) ?>
      </a>
    <?php endforeach; ?>
    <a href="<?= e(url('edition/videos')) ?>" <?= $isCurrent('edition/videos') ? 'class="active"' : '' ?>><?= e(t('Videos')) ?></a>
    <a href="<?= e(url('edition/gallery')) ?>" <?= $isCurrent('edition/gallery') ? 'class="active"' : '' ?>><?= e(t('Photo gallery')) ?></a>
    <a href="<?= e(url('classifieds')) ?>" class="more-link" style="color:#2563eb;"><?= e(t('Classifieds')) ?> ↗</a>
  </nav>

  <!-- Navigation Bar 2: Maroon Bar (Desktop Only) -->
  <nav class="ad-nav-maroon d-none d-lg-flex" aria-label="Edition Navigation">
    <div class="ad-nav-maroon-links">
      <a href="<?= e(url()) ?>"><span class="bullet">■</span> <?= e(strtoupper(t('Home'))) ?></a>
      <a href="<?= e(url('edition/breaking')) ?>"><span class="bullet">■</span> 24X7 <?= e(strtoupper(t('Breaking news'))) ?></a>
      <a href="<?= e(url('edition/latest')) ?>"><span class="bullet">■</span> <?= e(strtoupper(t('Hot News'))) ?></a>
      <a href="<?= e(url('category/international')) ?>"><span class="bullet">■</span> <?= e(strtoupper(t('World News'))) ?></a>
      <a href="<?= e(url('category/sports')) ?>"><span class="bullet">■</span> <?= e(strtoupper(t('Sports'))) ?></a>
      <a href="<?= e(url('category/entertainment')) ?>"><span class="bullet">■</span> <?= e(strtoupper(t('Entertainment'))) ?></a>
      <a href="<?= e(url('category/business')) ?>"><span class="bullet">■</span> <?= e(strtoupper(t('Business'))) ?></a>
    </div>
    <div class="ad-nav-maroon-hotline">
      <span>☎</span> <?= e(setting('contact_phone', '011 5 300 800')) ?>
    </div>
  </nav>

  <?php if (!empty($_SESSION['flash'])): ?>
    <div style="background:#fee2e2; color:#991b1b; padding:10px 14px; border-radius:4px; margin:10px 0; font-weight:600;" role="status">
      <?= e($_SESSION['flash']) ?>
    </div>
    <?php unset($_SESSION['flash']); ?>
  <?php endif; ?>

