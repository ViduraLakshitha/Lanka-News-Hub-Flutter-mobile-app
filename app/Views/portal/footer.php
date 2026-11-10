  <!-- Main Portal Footer -->
  <footer class="ad-footer">
    <div class="ad-footer-top">
      <div class="ad-footer-brand">
        <a href="<?= e(url()) ?>" aria-label="<?= e(setting('site_name', 'Lanka News Hub')) ?>" style="display:inline-flex; align-items:center; gap:10px;">
          <div style="background:#c4161c; width:44px; height:44px; border-radius:6px; display:flex; flex-direction:column; align-items:center; justify-content:center; color:#fff; flex-shrink:0;">
            <span style="font-family:'Arial Black', Impact, sans-serif; font-size:15px; font-weight:900; line-height:1;">LN</span>
            <span style="font-family:'Noto Sans Sinhala', 'Iskoola Pota', sans-serif; font-size:12px; font-weight:700; line-height:1.2;">ලංකා</span>
          </div>
          <span style="font-family:Arial, Helvetica, sans-serif; font-weight:900; font-size:24px; color:#c4161c; letter-spacing:-0.5px;">
            <?= e(setting('site_name', 'Lanka News Hub')) ?>
          </span>
        </a>
      </div>

      <div class="ad-footer-social">
        <span><?= e(t('Follow us')) ?>:</span>
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
        <a href="https://whatsapp.com" target="_blank" rel="noopener" class="ad-social-icon social-wa" title="WhatsApp" aria-label="WhatsApp">
          <svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor" aria-hidden="true"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
        </a>
        <a href="https://telegram.org" target="_blank" rel="noopener" class="ad-social-icon social-tg" title="Telegram" aria-label="Telegram">
          <svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor" aria-hidden="true"><path d="m20.665 3.717-17.73 6.837c-1.21.486-1.203 1.161-.222 1.462l4.552 1.42 10.532-6.645c.498-.303.953-.14.579.192l-8.533 7.701h-.002l-.313 4.674c.459 0 .661-.21.917-.46l2.203-2.14 4.58 3.385c.844.464 1.452.226 1.663-.785l3.007-14.169c.308-1.233-.472-1.793-1.463-1.472z"/></svg>
        </a>
      </div>

      <div class="ad-footer-apps">
        <a href="#" aria-label="Download on App Store">
          <img src="<?= e(url('assets/images/news/app_store.svg')) ?>" alt="App Store" height="32">
        </a>
        <a href="#" aria-label="Get it on Google Play">
          <img src="<?= e(url('assets/images/news/google_play.svg')) ?>" alt="Google Play" height="32">
        </a>
      </div>
    </div>

    <div class="ad-footer-cols row g-4">
      <div class="col-12 col-sm-6 col-lg-3">
        <h5><?= e(t('News editions')) ?></h5>
        <ul>
          <li><a href="<?= e(url()) ?>"><?= e(t('Home')) ?></a></li>
          <li><a href="<?= e(url('edition/latest')) ?>"><?= e(t('Latest news')) ?></a></li>
          <li><a href="<?= e(url('edition/breaking')) ?>"><?= e(t('Breaking news')) ?></a></li>
          <?php foreach ($categories as $cat): ?>
            <li><a href="<?= e(url('category/'.$cat['slug'])) ?>"><?= e(localized($cat,'name') ?: $cat['name_en']) ?></a></li>
          <?php endforeach; ?>
        </ul>
      </div>

      <div class="col-12 col-sm-6 col-lg-3">
        <h5><?= e(t('Media & Features')) ?></h5>
        <ul>
          <li><a href="#audio-briefing">Audio Briefings</a></li>
          <li><a href="#summary-wire">Quick Summaries</a></li>
          <li><a href="<?= e(url('edition/videos')) ?>"><?= e(t('Videos')) ?></a></li>
          <li><a href="<?= e(url('edition/gallery')) ?>"><?= e(t('Photo gallery')) ?></a></li>
          <li><a href="<?= e(url('classifieds')) ?>"><?= e(t('Classifieds')) ?></a></li>
          <li><a href="<?= e(url('classifieds/create')) ?>"><?= e(t('Post a classified')) ?></a></li>
        </ul>
      </div>

      <div class="col-12 col-sm-6 col-lg-3">
        <h5><?= e(t('Information')) ?></h5>
        <ul>
          <li><a href="<?= e(url('account')) ?>"><?= e(t('My account')) ?></a></li>
          <li><a href="<?= e(url('admin')) ?>">Editorial Newsroom ↗</a></li>
          <li><a href="<?= e(url('search')) ?>"><?= e(t('Search')) ?></a></li>
          <?php if ($menus): ?>
            <?php foreach ($menus as $menu): ?>
              <?php if (!safe_link($menu['url'], true)) continue; ?>
              <li><a href="<?= e(str_starts_with($menu['url'],'/') ? url($menu['url']) : $menu['url']) ?>"><?= e(localized($menu,'name')) ?></a></li>
            <?php endforeach; ?>
          <?php endif; ?>
        </ul>
      </div>

      <div class="col-12 col-sm-6 col-lg-3 ad-footer-contact">
        <h5><?= e(t('Contact details')) ?></h5>
        <p><strong><?= e(setting('site_name', 'Lanka News Hub')) ?></strong><br>
        <?= e(setting('contact_address', 'Central Newsroom, Colombo, Sri Lanka')) ?></p>
        <p><strong>Hotline:</strong> <?= e(setting('contact_phone', '011 5 300 800')) ?><br>
        <strong>Email:</strong> <?= e(setting('contact_email', 'contact@example.lk')) ?></p>
      </div>
    </div>

    <div class="ad-footer-bottom">
      <p>Copyright &copy; <?= date('Y') ?> <?= e(setting('site_name', 'Lanka News Hub')) ?>. All rights reserved.</p>
    </div>
  </footer>
</div><!-- /.ad-wrapper -->

<!-- Floating Back to Top Button -->
<a href="#" class="ad-scroll-top" onclick="window.scrollTo({top:0,behavior:'smooth'}); return false;" title="Back to Top" aria-label="Back to top">
  ▲
</a>

<?php $sticky=\App\Helpers\AdHelper::render('sticky'); if ($sticky): ?>
  <aside class="sticky-ad"><button type="button" data-close-ad aria-label="Close advertisement">×</button><?= $sticky ?></aside>
<?php endif; ?>

<?php if (preg_match('/^G-[A-Z0-9]+$/D',setting('ga_id')) || preg_match('/^GTM-[A-Z0-9]+$/D',setting('gtm_id'))): ?>
  <div class="consent" data-consent data-ga="<?= e(setting('ga_id')) ?>" data-gtm="<?= e(setting('gtm_id')) ?>">
    <span>Allow optional analytics to help us understand website usage?</span>
    <button data-consent-value="yes">Allow</button>
    <button data-consent-value="no">Decline</button>
  </div>
<?php endif; ?>
</body></html>
