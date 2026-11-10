<?php 
$placementSpecs = $placementSpecs ?? \App\Helpers\AdHelper::placementSpecs();
$campaignCounts = ['Published'=>0,'Scheduled'=>0,'Paused'=>0,'Expired'=>0]; 
foreach ($items as $campaign) $campaignCounts[\App\Helpers\AdHelper::state($campaign)]++; 
?>
<div class="campaign-intro">
  <div>
    <h2>Publish advertisements & check placement sizes</h2>
    <p>Upload banners and inspect exact pixel dimensions across all 11 designated website ad placements.</p>
  </div>
  <div class="campaign-intro-actions">
    <a class="button secondary" href="<?= e(url('?preview_ads=1')) ?>" target="_blank" rel="noopener">👁️ Preview all 11 ad zones on website ↗</a>
    <form method="post" style="display:inline;margin:0;" onsubmit="return confirm('Reset and populate active sample advertisements with exact-dimension banners for all 11 placements?');">
      <?= csrf() ?>
      <input type="hidden" name="action" value="seed_sample_ads">
      <button type="submit" class="button secondary">⚡ Populate all 11 placement samples</button>
    </form>
    <a class="button secondary" href="<?= e(url()) ?>" target="_blank" rel="noopener">View live website ↗</a>
  </div>
</div>

<div class="stat-grid campaign-stats">
  <?php foreach ($campaignCounts as $label=>$count): ?>
    <div class="stat">
      <span><?= e($label) ?> campaigns</span>
      <strong><?= (int)$count ?></strong>
    </div>
  <?php endforeach ?>
</div>

<div class="ad-format-options" aria-label="Choose advertisement format">
  <?php foreach ($formats as $key=>$label): if ($key==='html' && $user['role']!=='super_admin') continue; ?>
    <button type="button" data-ad-preset="<?= e($key) ?>">
      <span><?= e(['image'=>'▧','video'=>'▶','text'=>'Aa','native'=>'▤','html'=>'〈 〉'][$key]) ?></span>
      <?= e($label) ?>
    </button>
  <?php endforeach ?>
</div>

<div class="management-grid campaign-workspace">
  <section class="panel">
    <div class="section-heading">
      <h2>Campaigns</h2>
      <span class="muted"><?= count($items) ?> total</span>
    </div>

    <?php if (!$items): ?>
      <div class="empty">
        <h3>Publish your first ad</h3>
        <p>Choose a format and placement in the form, or click "Populate all 11 placement samples" above to see live demo ads.</p>
      </div>
    <?php endif ?>

    <?php foreach ($items as $row): 
      $state = \App\Helpers\AdHelper::state($row); 
      $format = $row['ad_format'] ?: 'image'; 
      $spec = $placementSpecs[$row['position']] ?? null;
      $hasImage = in_array($format, ['image', 'native', 'video'], true) && !empty($row['image_path']);
    ?>
      <article class="campaign-card">
        <div class="campaign-art">
          <?php if ($format==='video' && $row['image_path']): ?>
            <video src="<?= e(media_url($row['image_path'])) ?>" controls preload="metadata"></video>
          <?php elseif (in_array($format,['image','native'],true) && $row['image_path']): ?>
            <img src="<?= e(media_url($row['image_path'])) ?>" alt="<?= e($row['title']) ?>" loading="lazy">
          <?php else: ?>
            <span><?= e($formats[$format] ?? $format) ?></span>
          <?php endif ?>
        </div>

        <div class="section-heading">
          <h3><?= e($row['title']) ?></h3>
          <span class="status <?= e(strtolower($state)) ?>"><?= e($state) ?></span>
        </div>

        <div class="campaign-meta-pills">
          <span class="pill-format"><?= e($formats[$format] ?? $format) ?></span>
          <?php if ($spec): ?>
            <span class="pill-dimensions" title="Target banner dimensions">📐 <?= e($spec['dimensions']) ?></span>
          <?php endif ?>
          <span class="pill-priority">Weight: <?= (int)($row['priority'] ?? 1) ?></span>
        </div>

        <p class="campaign-slot-label">
          <strong>Placement:</strong> <?= e($positions[$row['position']] ?? $row['position']) ?>
        </p>

        <?php if ($row['ad_text']): ?>
          <p class="campaign-ad-text"><?= e($row['ad_text']) ?></p>
        <?php endif ?>

        <p class="meta">
          <span><?= e($row['start_date'] ?: 'Immediately') ?> → <?= e($row['end_date'] ?: 'No end date') ?></span>
          <?php if ($hasImage): ?>
            <span>· Artwork: <code><?= e(basename($row['image_path'])) ?></code></span>
          <?php endif ?>
        </p>

        <div class="inline-actions">
          <a class="button secondary small" href="?edit=<?= (int)$row['id'] ?>#ad-editor">Edit</a>
          <form method="post" style="display:inline;margin:0;">
            <?= csrf() ?>
            <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
            <input type="hidden" name="action" value="toggle">
            <button class="button secondary small"><?= $row['is_active'] ? 'Pause' : 'Enable' ?></button>
          </form>
          <?php if ($hasImage): ?>
            <a href="<?= e(media_url($row['image_path'])) ?>" target="_blank" rel="noopener" class="button secondary small" title="Inspect full size image file">Check Image File ↗</a>
          <?php endif ?>
          <?php if ($row['url'] && safe_link($row['url'])): ?>
            <a href="<?= e($row['url']) ?>" target="_blank" rel="noopener" class="text-link">Destination ↗</a>
          <?php endif ?>
        </div>
      </article>
    <?php endforeach ?>
  </section>

  <form method="post" enctype="multipart/form-data" class="panel form-stack" id="ad-editor">
    <?= csrf() ?>
    <input type="hidden" name="id" value="<?= (int)($edit['id'] ?? 0) ?>">
    <h2><?= $edit ? 'Edit campaign' : 'Publish a new ad' ?></h2>

    <?php $field('title','Campaign title','text',null,'required maxlength="100"'); ?>

    <label>Format
      <select name="ad_format" data-ad-format>
        <?php foreach ($formats as $key=>$label): if ($key==='html' && $user['role']!=='super_admin') continue; ?>
          <option value="<?= e($key) ?>" <?= ($edit['ad_format'] ?? 'image')===$key ? 'selected' : '' ?>><?= e($label) ?></option>
        <?php endforeach ?>
      </select>
    </label>

    <?php $field('advertiser','Advertiser name','text',null,'maxlength="150" placeholder="Optional"'); ?>

    <label>Placement
      <select name="position" data-banner-position>
        <?php foreach ($positions as $key=>$label): ?>
          <option value="<?= e($key) ?>" <?= ($edit['position'] ?? '')===$key ? 'selected' : '' ?>><?= e($label) ?></option>
        <?php endforeach ?>
      </select>
    </label>

    <!-- LIVE PLACEMENT DIMENSION INSPECTOR CARD -->
    <div class="ad-dimension-card" id="ad-dimension-card" data-specs='<?= json_encode($placementSpecs, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>'>
      <div class="ad-dim-header">
        <span class="ad-dim-badge" id="ad-dim-badge">1200 × 100 px</span>
        <span class="ad-dim-ratio" id="ad-dim-ratio">Ratio: 12:1</span>
        <strong class="ad-dim-name" id="ad-dim-name">Top Leaderboard Banner</strong>
      </div>
      <p class="ad-dim-desc" id="ad-dim-desc">Header bar above site navigation. High visibility across all pages.</p>
      <div class="ad-dim-template-row" id="ad-dim-template-box">
        <span class="ad-dim-template-label">Preset uploaded banner:</span>
        <a id="ad-dim-template-link" href="<?= e(url('uploads/ads/ad_header_1200x100.png')) ?>" target="_blank" class="button secondary small">Check banner size (1200 × 100) ↗</a>
      </div>
    </div>

    <div class="placement-map" aria-label="Advertisement placement map">
      <div data-placement="header_728x90" role="button" tabindex="0" title="Click to select Top Leaderboard">
        <span>TOP LEADERBOARD (1200×100)</span> 
        <b class="slot-count <?= ($placementCounts['header_728x90'] ?? 0) ? 'has-ads' : 'empty' ?>"><?= (int)($placementCounts['header_728x90'] ?? 0) ?> active</b>
      </div>
      <div class="map-pair">
        <span data-placement="top_left" role="button" tabindex="0" title="Click to select Top Left">
          <span>TOP LEFT (590×100)</span> 
          <b class="slot-count <?= ($placementCounts['top_left'] ?? 0) ? 'has-ads' : 'empty' ?>"><?= (int)($placementCounts['top_left'] ?? 0) ?> active</b>
        </span>
        <span data-placement="top_right" role="button" tabindex="0" title="Click to select Top Right">
          <span>TOP RIGHT (590×100)</span> 
          <b class="slot-count <?= ($placementCounts['top_right'] ?? 0) ? 'has-ads' : 'empty' ?>"><?= (int)($placementCounts['top_right'] ?? 0) ?> active</b>
        </span>
      </div>
      <div class="map-content">
        <span>NEWS CONTENT
          <small data-placement="homepage_mid_728x90" role="button" tabindex="0" title="Click to select Between Sections">
            <span>BETWEEN SECTIONS (728×90)</span> 
            <b class="slot-count <?= ($placementCounts['homepage_mid_728x90'] ?? 0) ? 'has-ads' : 'empty' ?>"><?= (int)($placementCounts['homepage_mid_728x90'] ?? 0) ?> active</b>
          </small>
          <small data-placement="inline_feed" role="button" tabindex="0" title="Click to select Inside News Feed">
            <span>INSIDE NEWS FEED (728×90)</span> 
            <b class="slot-count <?= ($placementCounts['inline_feed'] ?? 0) ? 'has-ads' : 'empty' ?>"><?= (int)($placementCounts['inline_feed'] ?? 0) ?> active</b>
          </small>
          <small data-placement="in_article" role="button" tabindex="0" title="Click to select In Article">
            <span>IN ARTICLE (800×250)</span> 
            <b class="slot-count <?= ($placementCounts['in_article'] ?? 0) ? 'has-ads' : 'empty' ?>"><?= (int)($placementCounts['in_article'] ?? 0) ?> active</b>
          </small>
        </span>
        <span>
          <small data-placement="sidebar_300x250" role="button" tabindex="0" title="Click to select Sidebar">
            <span>SIDEBAR (300×250)</span> 
            <b class="slot-count <?= ($placementCounts['sidebar_300x250'] ?? 0) ? 'has-ads' : 'empty' ?>"><?= (int)($placementCounts['sidebar_300x250'] ?? 0) ?> active</b>
          </small>
          <small data-placement="sidebar_300x600" role="button" tabindex="0" title="Click to select Tower">
            <span>TOWER (300×600)</span> 
            <b class="slot-count <?= ($placementCounts['sidebar_300x600'] ?? 0) ? 'has-ads' : 'empty' ?>"><?= (int)($placementCounts['sidebar_300x600'] ?? 0) ?> active</b>
          </small>
        </span>
      </div>
      <div data-placement="footer" role="button" tabindex="0" title="Click to select Footer">
        <span>FOOTER (1200×100)</span> 
        <b class="slot-count <?= ($placementCounts['footer'] ?? 0) ? 'has-ads' : 'empty' ?>"><?= (int)($placementCounts['footer'] ?? 0) ?> active</b>
      </div>
      <div data-placement="sticky" role="button" tabindex="0" title="Click to select Sticky Bottom">
        <span>STICKY BOTTOM (728×90)</span> 
        <b class="slot-count <?= ($placementCounts['sticky'] ?? 0) ? 'has-ads' : 'empty' ?>"><?= (int)($placementCounts['sticky'] ?? 0) ?> active</b>
      </div>
      <div data-placement="popup" role="button" tabindex="0" title="Click to select Pop-Up">
        <span>POP-UP (600×400)</span> 
        <b class="slot-count <?= ($placementCounts['popup'] ?? 0) ? 'has-ads' : 'empty' ?>"><?= (int)($placementCounts['popup'] ?? 0) ?> active</b>
      </div>
    </div>
    <small class="muted">Tip: Click any box in the diagram above to choose that placement location.</small>

    <div data-ad-fields="image native video">
      <label>Upload artwork or video
        <input type="file" name="banner" data-banner-upload>
      </label>

      <!-- LIVE UPLOAD DIMENSION CHECKER -->
      <div class="upload-dim-inspector" id="upload-dim-inspector" style="display:none;">
        <div class="inspector-badge" id="inspector-badge">Checking image...</div>
        <div class="inspector-details" id="inspector-details"></div>
      </div>

      <small class="muted" data-upload-hint>Images: JPG, PNG, WebP or GIF. Video: MP4 or WebM. Maximum 25 MB.</small>

      <div class="banner-upload-preview" data-banner-preview <?= empty($edit['image_path']) ? 'hidden' : '' ?>>
        <img src="<?= e(!empty($edit['image_path']) && ($edit['ad_format'] ?? 'image')!=='video' ? media_url($edit['image_path']) : url('assets/news-placeholder.svg')) ?>" alt="Artwork preview" data-preview-image>
        <video src="<?= e(!empty($edit['image_path']) && ($edit['ad_format'] ?? '')==='video' ? media_url($edit['image_path']) : '') ?>" controls data-preview-video></video>
        <div class="banner-preview-meta">
          <small data-banner-dimensions>Current media</small>
          <?php if (!empty($edit['image_path'])): ?>
            <a href="<?= e(media_url($edit['image_path'])) ?>" target="_blank" class="button secondary small" style="margin-left:auto;">Inspect Full Size ↗</a>
          <?php endif ?>
        </div>
      </div>
    </div>

    <div data-ad-fields="text native">
      <label>Advertisement text
        <textarea name="ad_text" rows="4" maxlength="3000"><?= e($edit['ad_text'] ?? '') ?></textarea>
      </label>
    </div>

    <?php if ($user['role']==='super_admin'): ?>
      <div data-ad-fields="html">
        <label>AdSense or trusted HTML code
          <textarea name="ad_code" rows="6" maxlength="25000"><?= e($edit['ad_code'] ?? '') ?></textarea>
        </label>
        <small class="muted">Only super administrators can publish code. Use code from a trusted ad provider.</small>
      </div>
    <?php endif ?>

    <?php 
    $field('url','Click-through destination','url',null,'placeholder="https://advertiser.example"'); 
    $field('cta_label','Call-to-action label','text',null,'maxlength="80" placeholder="Learn more"'); 
    ?>

    <div class="form-grid">
      <?php 
      $field('start_date','Start date','date'); 
      $field('end_date','End date','date'); 
      ?>
    </div>

    <?php $field('priority','Display priority / rotation weight (1–100)','number',(int)($edit['priority'] ?? 1),'min="1" max="100"'); ?>
    <small class="muted">Leave dates blank for immediate, ongoing publication. Dates use Sri Lanka time.</small>

    <label class="check">
      <input type="checkbox" name="is_active" <?= ($edit['is_active'] ?? 1) ? 'checked' : '' ?>> Enable publication
    </label>

    <button class="button full">Save campaign →</button>
    <a href="<?= e(url('admin/advertisements')) ?>">Start a new campaign</a>
    <small class="muted">Active campaigns in the same placement rotate automatically in a timed loop.</small>
  </form>
</div>

<!-- ALL 11 PLACEMENTS REFERENCE MATRIX -->
<details class="placement-specs-table-panel" open>
  <summary><strong>📐 All 11 Ad Placements: Dimensions & Live Artwork Check (Click to expand/collapse)</strong></summary>
  <div class="specs-table-wrap">
    <table class="specs-table">
      <thead>
        <tr>
          <th>Placement ID</th>
          <th>Placement Name</th>
          <th>Recommended Dimensions</th>
          <th>Aspect Ratio</th>
          <th>Uploaded Banner Artwork</th>
          <th>Website Location</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($placementSpecs as $slotId => $slotSpec): ?>
          <tr>
            <td><code><?= e($slotId) ?></code></td>
            <td><strong><?= e($slotSpec['name']) ?></strong></td>
            <td><span class="dim-tag"><?= e($slotSpec['dimensions']) ?></span></td>
            <td><span class="ratio-tag"><?= e($slotSpec['aspect']) ?></span></td>
            <td>
              <a href="<?= e(url($slotSpec['image'])) ?>" target="_blank" class="banner-thumb-link" title="Click to inspect banner file in full resolution">
                <img src="<?= e(url($slotSpec['image'])) ?>" alt="<?= e($slotSpec['name']) ?>" class="banner-thumb-img">
                <span><?= e(basename($slotSpec['image'])) ?> ↗</span>
              </a>
            </td>
            <td><small class="slot-loc-desc"><?= e($slotSpec['description']) ?></small></td>
            <td>
              <button type="button" class="button secondary small" data-choose-placement="<?= e($slotId) ?>">Select Slot</button>
            </td>
          </tr>
        <?php endforeach ?>
      </tbody>
    </table>
  </div>
</details>
