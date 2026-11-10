<?php
$userLangs = $userLangs ?? \App\Helpers\DomainHelper::userLanguages($user);
$editionsMeta = [
    'en' => [
        'code' => 'en',
        'name' => 'English',
        'subdomain' => \App\Helpers\DomainHelper::MAIN_DOMAIN,
        'badge' => 'English Edition',
        'bg' => '#fee2e2',
        'fg' => '#991b1b'
    ],
    'si' => [
        'code' => 'si',
        'name' => 'සිංහල',
        'subdomain' => \App\Helpers\DomainHelper::SINHALA_DOMAIN,
        'badge' => 'Sinhala Edition',
        'bg' => '#fef3c7',
        'fg' => '#92400e'
    ],
    'ta' => [
        'code' => 'ta',
        'name' => 'தமிழ்',
        'subdomain' => \App\Helpers\DomainHelper::TAMIL_DOMAIN,
        'badge' => 'Tamil Edition',
        'bg' => '#e0e7ff',
        'fg' => '#3730a3'
    ]
];

// Determine active initial tab
$activeTab = $userLangs[0] ?? 'en';
?>

<form method="post" class="editor-layout" data-editor-form>
  <?= csrf() ?>
  <div>
    <!-- Main Content Panel -->
    <div class="panel">
      <div class="section-heading" style="margin-bottom:12px">
        <div>
          <h2 style="margin:0 0 4px">The Story Desks</h2>
          <span class="muted" style="font-size:13px">Unified Multi-Domain Newsroom: Publish to <code>news.liontech.lk</code>, <code>sinhala.news.liontech.lk</code>, and <code>tamil.news.liontech.lk</code></span>
        </div>
        <?php if ($user['role'] === 'reporter'): ?>
          <div style="text-align:right">
            <span class="badge" style="background:#f1f5f9;color:#334155;border:1px solid #cbd5e1;padding:4px 10px;font-size:12px;font-weight:600">
              Assigned Editions: <?= implode(', ', array_map(fn($l) => strtoupper($l), $userLangs)) ?>
            </span>
          </div>
        <?php endif; ?>
      </div>

      <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;padding:10px 14px;margin-bottom:18px;font-size:13px;color:#475569;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px">
        <div>
          ℹ️ <strong>Multi-Domain Distribution:</strong> Articles completed in Sinhala appear automatically on <code>sinhala.news.liontech.lk</code>, Tamil on <code>tamil.news.liontech.lk</code>, and English on <code>news.liontech.lk</code>.
        </div>
      </div>

      <!-- Language Tabs with Subdomain Badges -->
      <div class="editor-tabs" role="tablist" aria-label="Story edition desks" style="margin-bottom:20px;border-bottom:2px solid #e2e8f0;display:flex;gap:4px">
        <?php foreach ($userLangs as $l): 
          $meta = $editionsMeta[$l] ?? $editionsMeta['en'];
        ?>
          <button type="button" 
                  role="tab" 
                  id="tab-<?= e($l) ?>" 
                  data-tab="<?= e($l) ?>" 
                  aria-controls="edition-<?= e($l) ?>" 
                  aria-selected="<?= $l === $activeTab ? 'true' : 'false' ?>"
                  style="display:inline-flex;flex-direction:column;align-items:flex-start;padding:8px 16px;border-radius:6px 6px 0 0;font-size:14px;cursor:pointer;border:1px solid transparent;border-bottom:none">
            <strong><?= e($meta['name']) ?></strong>
            <span style="font-size:11px;opacity:0.8;font-family:monospace"><?= e($meta['subdomain']) ?></span>
          </button>
        <?php endforeach; ?>
      </div>

      <!-- Language Form Panels -->
      <?php foreach ($userLangs as $l): 
        $meta = $editionsMeta[$l] ?? $editionsMeta['en'];
        $hasContent = !empty($article['title_' . $l]) && !empty($article['content_' . $l]);
      ?>
        <section class="edition-panel form-stack" 
                 id="edition-<?= e($l) ?>" 
                 data-edition="<?= e($l) ?>" 
                 role="tabpanel" 
                 aria-labelledby="tab-<?= e($l) ?>"
                 <?= $l !== $activeTab ? 'hidden' : '' ?>>
          
          <div style="display:flex;justify-content:space-between;align-items:center;background:<?= $meta['bg'] ?>;color:<?= $meta['fg'] ?>;padding:8px 14px;border-radius:6px;margin-bottom:14px">
            <div style="font-weight:700;font-size:14px">
              <?= e($meta['badge']) ?> · Subdomain: <code><?= e($meta['subdomain']) ?></code>
            </div>
            <?php if ($hasContent): ?>
              <span style="font-size:11px;font-weight:700;background:#ffffff;padding:2px 8px;border-radius:4px;color:<?= $meta['fg'] ?>">
                ✓ Ready for <?= e($meta['subdomain']) ?>
              </span>
            <?php else: ?>
              <span style="font-size:11px;opacity:0.8">
                Leave blank if not publishing to this edition
              </span>
            <?php endif; ?>
          </div>

          <label>
            Headline (<?= e($meta['name']) ?>)
            <input name="title_<?= e($l) ?>" 
                   maxlength="255" 
                   value="<?= e($article['title_' . $l] ?? '') ?>" 
                   placeholder="Enter headline for <?= e($meta['name']) ?> edition (<?= e($meta['subdomain']) ?>)">
          </label>

          <label>
            Summary / Excerpt (<?= e($meta['name']) ?>)
            <textarea name="summary_<?= e($l) ?>" 
                      rows="3" 
                      maxlength="2000" 
                      placeholder="Brief summary shown on homepage cards and search snippets"><?= e($article['summary_' . $l] ?? '') ?></textarea>
          </label>

          <label for="content-<?= e($l) ?>">
            Story Content (<?= e($meta['name']) ?>)
          </label>
          <textarea id="content-<?= e($l) ?>" 
                    name="content_<?= e($l) ?>" 
                    data-rich-editor 
                    rows="14" 
                    aria-label="<?= e($meta['name']) ?> story content"
                    placeholder="Compose the full article body..."><?= e(rich_html($article['content_' . $l] ?? '')) ?></textarea>
        </section>
      <?php endforeach; ?>
    </div>

    <!-- Media & Visuals Panel -->
    <div class="panel form-stack" style="margin-top:20px">
      <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:10px">
        <div>
          <h2 style="margin:0 0 4px">Photos & Video Attachments</h2>
          <span class="muted" style="font-size:13px">Attach YouTube videos, hosted MP4 clips, and curate a high-impact photo gallery.</span>
        </div>
        <a href="<?= e(url('admin/media')) ?>" target="_blank" rel="noopener" class="button secondary small" style="font-size:12px;padding:6px 14px">
          Media Library ↗
        </a>
      </div>

      <!-- Video Row -->
      <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:16px;padding-top:4px">
        <label>
          YouTube Video URL
          <input name="video_url" 
                 type="url" 
                 value="<?= e($article['video_url'] ?? '') ?>" 
                 placeholder="https://www.youtube.com/watch?v=…">
        </label>

        <label>
          Uploaded Video (Local Clip)
          <select name="video_media_id">
            <option value="">None (No hosted video)</option>
            <?php foreach (($media ?? []) as $file): 
              if (!str_starts_with((string)($file['file_type'] ?? ''), 'video/')) continue; 
            ?>
              <option value="<?= (int)$file['id'] ?>" <?= ($article['video_media_id'] ?? '') == $file['id'] ? 'selected' : '' ?>>
                <?= e($file['file_name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </label>
      </div>

      <!-- Image Gallery Grid Selector -->
      <?php 
      $galleryIds = array_map('intval', json_decode($article['gallery_ids'] ?? '[]', true) ?: []);
      $imageMedia = array_values(array_filter($media, fn($f) => str_starts_with($f['file_type'] ?? '', 'image/')));
      $selectedCount = count(array_filter($imageMedia, fn($f) => in_array((int)$f['id'], $galleryIds, true)));
      ?>
      <div class="gallery-picker-section" style="margin-top:14px">
        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;margin-bottom:10px">
          <div>
            <label style="font-size:14px;font-weight:700;color:#1e293b;display:inline-flex;align-items:center;gap:8px;margin:0">
              <span>Image Gallery (Visual Grid Selection)</span>
              <span class="gallery-count-pill" id="gallery-selected-badge"><?= $selectedCount ?> Selected</span>
            </label>
            <div class="muted" style="font-size:12px;margin-top:2px">
              Click photo cards below to add or remove them from the article's photo gallery.
            </div>
          </div>
        </div>

        <?php if (empty($imageMedia)): ?>
          <div style="text-align:center;padding:36px 16px;background:#f8fafc;border:2px dashed #cbd5e1;border-radius:10px">
            <div style="font-size:36px;margin-bottom:8px">🖼️</div>
            <strong style="display:block;font-size:14px;color:#334155;margin-bottom:4px">No Photos in Media Library Yet</strong>
            <p class="muted" style="font-size:12px;margin-bottom:14px">Upload images to your media library to attach them to stories and galleries.</p>
            <a href="<?= e(url('admin/media')) ?>" target="_blank" rel="noopener" class="button secondary small">
              Upload Images to Media Library ↗
            </a>
          </div>
        <?php else: ?>
          <div class="gallery-picker-wrapper">
            <!-- Toolbar -->
            <div class="gallery-toolbar">
              <div class="gallery-search-wrap">
                <span class="gallery-search-icon" aria-hidden="true">🔍</span>
                <input type="search" 
                       id="gallery-search" 
                       class="gallery-search-input" 
                       placeholder="Filter photos by filename..." 
                       autocomplete="off">
              </div>

              <div class="gallery-filters">
                <button type="button" class="gallery-pill-btn active" data-filter="all">
                  All Photos (<?= count($imageMedia) ?>)
                </button>
                <button type="button" class="gallery-pill-btn" data-filter="selected" id="gallery-filter-selected-btn">
                  Selected Only (<span id="gallery-filter-count"><?= $selectedCount ?></span>)
                </button>
                <button type="button" class="gallery-action-btn" id="gallery-select-all-btn">
                  Select All
                </button>
                <button type="button" class="gallery-action-btn" id="gallery-clear-btn" style="color:#b91c1c">
                  Clear All
                </button>
              </div>
            </div>

            <!-- Active Selected Photos Shelf (Preview Strip) -->
            <div class="gallery-selected-shelf <?= $selectedCount === 0 ? 'is-empty' : '' ?>" id="gallery-selected-shelf">
              <?php foreach ($imageMedia as $file): 
                if (!in_array((int)$file['id'], $galleryIds, true)) continue;
                $thumbUrl = media_url($file['file_path']);
              ?>
                <div class="gallery-shelf-item" data-id="<?= (int)$file['id'] ?>" title="<?= e($file['file_name']) ?>">
                  <img src="<?= e($thumbUrl) ?>" alt="<?= e($file['file_name']) ?>" loading="lazy">
                  <button type="button" class="gallery-shelf-remove" data-id="<?= (int)$file['id'] ?>" title="Remove photo" aria-label="Remove">✕</button>
                </div>
              <?php endforeach; ?>
            </div>

            <!-- Responsive Thumbnail Grid -->
            <div class="gallery-picker-grid" id="gallery-picker-grid">
              <?php foreach ($imageMedia as $file): 
                $fileId = (int)$file['id'];
                $isSelected = in_array($fileId, $galleryIds, true);
                $thumbUrl = media_url($file['file_path']);
                $cleanName = $file['file_name'] ?: 'Photo #' . $fileId;
              ?>
                <div class="gallery-card <?= $isSelected ? 'is-selected' : '' ?>" 
                     data-id="<?= $fileId ?>" 
                     data-name="<?= e(strtolower($cleanName)) ?>" 
                     data-thumb="<?= e($thumbUrl) ?>"
                     tabindex="0" 
                     role="checkbox" 
                     aria-checked="<?= $isSelected ? 'true' : 'false' ?>"
                     title="<?= e($cleanName) ?>">
                  
                  <input type="checkbox" 
                         name="gallery_ids[]" 
                         value="<?= $fileId ?>" 
                         class="gallery-checkbox" 
                         <?= $isSelected ? 'checked' : '' ?> 
                         style="display:none">

                  <div class="gallery-card-thumb">
                    <img src="<?= e($thumbUrl) ?>" alt="<?= e($cleanName) ?>" loading="lazy">
                    <div class="gallery-card-check" aria-hidden="true">✓</div>
                  </div>

                  <div class="gallery-card-meta">
                    <span class="gallery-card-name"><?= e($cleanName) ?></span>
                    <span class="gallery-card-sub">#<?= $fileId ?></span>
                  </div>
                </div>
              <?php endforeach; ?>

              <div id="gallery-empty-search" style="display:none;grid-column:1/-1;text-align:center;padding:32px 16px;color:#64748b;font-size:13px">
                🔍 No photos match "<span id="gallery-search-term"></span>".
              </div>
            </div>

            <!-- Footer Info -->
            <div style="display:flex;justify-content:space-between;align-items:center;font-size:12px;color:#64748b;padding-top:4px">
              <span>Showing <strong id="gallery-visible-count"><?= count($imageMedia) ?></strong> of <?= count($imageMedia) ?> photos</span>
              <a href="<?= e(url('admin/media')) ?>" target="_blank" rel="noopener" class="text-link">
                Upload to media library in a new tab ↗
              </a>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Sidebar / Actions -->
  <aside>
    <div class="panel form-stack">
      <h2>Publishing Controls</h2>

      <label>
        Editorial Status
        <select name="status">
          <?php 
          $states = $user['role'] === 'reporter' ? ['draft', 'pending'] : ['draft', 'pending', 'scheduled', 'published', 'archived']; 
          foreach ($states as $state): 
          ?>
            <option value="<?= e($state) ?>" <?= ($article['status'] ?? 'draft') === $state ? 'selected' : '' ?>>
              <?= e(ucfirst($state)) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </label>

      <?php if ($user['role'] !== 'reporter' && !empty($authors)): ?>
        <label>
          Author / Byline
          <select name="author_id">
            <?php foreach ($authors as $author): ?>
              <option value="<?= (int)$author['id'] ?>" <?= ((int)($article['author_id'] ?? $user['id'])) === (int)$author['id'] ? 'selected' : '' ?>>
                <?= e($author['name']) ?> (<?= e($author['role_name']) ?>)
              </option>
            <?php endforeach; ?>
          </select>
        </label>
      <?php endif; ?>

      <label>
        Publication Time (Sri Lanka)
        <input type="datetime-local" 
               name="published_at" 
               value="<?= e(!empty($article['published_at']) ? date('Y-m-d\TH:i', strtotime($article['published_at'])) : '') ?>">
      </label>

      <label>
        Desk / Category
        <select name="category_id" required>
          <option value="">Choose category</option>
          <?php foreach ($categories as $category): ?>
            <option value="<?= (int)$category['id'] ?>" <?= ($article['category_id'] ?? '') == $category['id'] ? 'selected' : '' ?>>
              <?= e($category['name_en']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </label>

      <label>
        Story URL Slug
        <input name="slug" 
               maxlength="180" 
               value="<?= e($article['slug'] ?? '') ?>" 
               placeholder="Auto-generated from title if blank">
      </label>

      <label>
        Featured Image (Hero Header)
        <select name="featured_image_id" id="featured_image_select">
          <option value="">None (No hero image)</option>
          <?php foreach (($media ?? []) as $file): 
            if (!str_starts_with((string)($file['file_type'] ?? ''), 'image/')) continue; 
            $mediaThumb = media_url($file['file_path'] ?? '');
          ?>
            <option value="<?= (int)$file['id'] ?>" 
                    <?= ($article['featured_image_id'] ?? '') == $file['id'] ? 'selected' : '' ?> 
                    data-thumb="<?= e($mediaThumb) ?>">
              <?= e($file['file_name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </label>

      <!-- Featured Image Live Thumbnail Preview -->
      <div id="featured_image_preview_box" style="margin-top:-6px;margin-bottom:12px;<?= empty($article['featured_image_id']) ? 'display:none;' : '' ?>">
        <div style="position:relative;border-radius:6px;overflow:hidden;border:1px solid #cbd5e1;aspect-ratio:16/9;background:#f1f5f9;box-shadow:0 1px 3px rgba(0,0,0,0.06)">
          <img id="featured_image_preview_img" 
               src="" 
               alt="Featured image preview" 
               style="width:100%;height:100%;object-fit:cover;display:block">
          <button type="button" 
                  id="featured_image_remove_btn" 
                  style="position:absolute;top:6px;right:6px;background:rgba(15,23,42,0.8);color:#fff;border:none;border-radius:4px;font-size:11px;padding:3px 8px;cursor:pointer;font-weight:600">
            Remove ✕
          </button>
        </div>
      </div>

      <label>
        Tags (comma separated)
        <input name="tags" 
               maxlength="2000" 
               value="<?= e($article['tags'] ?? '') ?>" 
               placeholder="e.g. Economy, Central Bank, Colombo">
      </label>

      <label class="check">
        <input type="checkbox" name="is_featured" <?= !empty($article['is_featured']) ? 'checked' : '' ?>> 
        Lead / Featured on Homepage
      </label>

      <label class="check">
        <input type="checkbox" name="is_breaking" <?= !empty($article['is_breaking']) ? 'checked' : '' ?>> 
        Breaking News Alert
      </label>

      <button class="button full" type="submit" style="margin-top:12px;font-size:15px;padding:12px">
        Save & Update Story →
      </button>

      <!-- Subdomain Preview Links if published -->
      <?php if (!empty($article['slug']) && in_array($article['status'] ?? '', ['published', 'scheduled'], true)): ?>
        <div style="border-top:1px solid #e2e8f0;padding-top:14px;margin-top:14px">
          <strong style="font-size:12px;display:block;margin-bottom:8px;color:#475569">View on Edition Subdomains:</strong>
          <div style="display:flex;flex-direction:column;gap:6px">
            <?php foreach (['en', 'si', 'ta'] as $subLang): 
              if (empty($article['title_' . $subLang])) continue;
              $subMeta = $editionsMeta[$subLang];
            ?>
              <a href="<?= e(edition_url($subLang, 'news/' . $article['slug'])) ?>" 
                 target="_blank" 
                 rel="noopener" 
                 class="button secondary small" 
                 style="font-size:11px;display:flex;justify-content:space-between;align-items:center;text-decoration:none">
                <span><?= e($subMeta['name']) ?> (<?= e($subMeta['subdomain']) ?>)</span>
                <span>↗</span>
              </a>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>
    </div>

    <?php if (!empty($article['id']) && $user['role'] !== 'reporter'): ?>
      <div class="panel form-stack">
        <h2>Danger Zone</h2>
        <p class="muted" style="font-size:13px">Permanently delete this story, all revisions, and media associations.</p>
        <form action="<?= e(url('admin/articles/delete/' . $article['id'])) ?>" method="post" data-confirm="Permanently delete this story? This cannot be undone.">
          <?= csrf() ?>
          <button class="button danger full" style="background:#b91c1c;color:#fff;border:none">
            Delete Story Permanently
          </button>
        </form>
      </div>
    <?php endif; ?>

    <div class="panel">
      <span class="eyebrow" style="font-size:11px;font-weight:700;letter-spacing:1px;color:#64748b">EDITORIAL WORKFLOW</span>
      <p style="margin:6px 0;font-weight:600">Draft → Review → Publish</p>
      <small class="muted" style="font-size:12px;line-height:1.4;display:block">
        Journalists submit stories for review. Editors control publication across all 3 subdomains. Scheduled stories go live automatically at their set Sri Lanka time.
      </small>
    </div>
  </aside>
</form>

<?php if (!empty($revisions)): ?>
  <section class="panel" style="margin-top:24px">
    <h2>Revision History</h2>
    <p class="muted">Restoring creates a draft and keeps the current version in history.</p>
    <?php foreach ($revisions as $revision): 
      $snapshot = json_decode($revision['snapshot'], true) ?: []; 
    ?>
      <details class="revision" style="margin-bottom:12px;border:1px solid #e2e8f0;border-radius:6px;padding:12px">
        <summary style="font-weight:600;cursor:pointer">
          <?= e($revision['created_at']) ?> · <?= e($revision['author']) ?>
        </summary>
        <h3 style="margin:10px 0 6px"><?= e($snapshot['title_en'] ?: ($snapshot['title_si'] ?: ($snapshot['title_ta'] ?? 'Untitled'))) ?></h3>
        <?php foreach (['en', 'si', 'ta'] as $l): 
          if (empty($snapshot['title_' . $l])) continue; 
        ?>
          <details style="margin:6px 0">
            <summary style="font-size:12px;font-weight:600"><?= e(strtoupper($l)) ?> Content (<?= e($editionsMeta[$l]['subdomain'] ?? '') ?>)</summary>
            <div class="article-body" style="font-size:13px;padding:8px;background:#f8fafc;border-radius:4px"><?= rich_html($snapshot['content_' . $l] ?? '') ?></div>
          </details>
        <?php endforeach; ?>
        <form action="<?= e(url('admin/articles/restore/' . $revision['id'])) ?>" method="post" data-confirm="Restore this revision as a draft?" style="margin-top:10px">
          <?= csrf() ?>
          <button class="button secondary small">Restore this revision</button>
        </form>
      </details>
    <?php endforeach; ?>
  </section>
<?php endif; ?>

<!-- ========================================================================= -->
<!-- Gallery Picker & Visual Media Selection Styles & Scripts                  -->
<!-- ========================================================================= -->
<style>
.gallery-count-pill {
  background: #b80d25;
  color: #ffffff;
  font-size: 11px;
  font-weight: 700;
  padding: 2px 8px;
  border-radius: 12px;
  letter-spacing: 0.3px;
}
.gallery-picker-wrapper {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  padding: 16px;
  display: flex;
  flex-direction: column;
  gap: 12px;
}
.gallery-toolbar {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
}
.gallery-search-wrap {
  position: relative;
  flex: 1 1 240px;
  max-width: 380px;
}
.gallery-search-input {
  width: 100% !important;
  padding: 8px 12px 8px 34px !important;
  font-size: 13px !important;
  border-radius: 6px !important;
  border: 1px solid #cbd5e1 !important;
  background: #ffffff !important;
  box-sizing: border-box;
}
.gallery-search-input:focus {
  border-color: #b80d25 !important;
  outline: 2px solid rgba(184, 13, 37, 0.2) !important;
}
.gallery-search-icon {
  position: absolute;
  left: 10px;
  top: 50%;
  transform: translateY(-50%);
  color: #94a3b8;
  font-size: 13px;
  pointer-events: none;
}
.gallery-filters {
  display: flex;
  align-items: center;
  gap: 6px;
  flex-wrap: wrap;
}
.gallery-pill-btn {
  background: #ffffff;
  border: 1px solid #cbd5e1;
  color: #475569;
  font-size: 12px;
  font-weight: 600;
  padding: 6px 12px;
  border-radius: 20px;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 5px;
  transition: all 0.15s ease;
}
.gallery-pill-btn:hover {
  background: #f1f5f9;
  border-color: #94a3b8;
  color: #0f172a;
}
.gallery-pill-btn.active {
  background: #0f172a;
  border-color: #0f172a;
  color: #ffffff;
}
.gallery-action-btn {
  background: none;
  border: none;
  color: #64748b;
  font-size: 12px;
  font-weight: 600;
  text-decoration: underline;
  cursor: pointer;
  padding: 4px 8px;
}
.gallery-action-btn:hover {
  color: #0f172a;
}

/* Active Selected Photos Shelf */
.gallery-selected-shelf {
  background: #ffffff;
  border: 1px dashed #cbd5e1;
  border-radius: 8px;
  padding: 8px 12px;
  display: flex;
  align-items: center;
  gap: 10px;
  overflow-x: auto;
  min-height: 56px;
  box-sizing: border-box;
}
.gallery-selected-shelf.is-empty::before {
  content: "No gallery photos attached yet. Click photo cards below to add them to this story.";
  color: #94a3b8;
  font-size: 12px;
  font-style: italic;
}
.gallery-shelf-item {
  position: relative;
  flex: 0 0 46px;
  height: 46px;
  border-radius: 6px;
  overflow: hidden;
  border: 2px solid #b80d25;
  box-shadow: 0 1px 3px rgba(0,0,0,0.08);
}
.gallery-shelf-item img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}
.gallery-shelf-remove {
  position: absolute;
  top: 0;
  right: 0;
  background: rgba(184, 13, 37, 0.9);
  color: #ffffff;
  border: none;
  width: 16px;
  height: 16px;
  font-size: 10px;
  line-height: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  border-bottom-left-radius: 4px;
}
.gallery-shelf-remove:hover {
  background: #8b0d11;
}

/* Grid of Cards */
.gallery-picker-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(135px, 1fr));
  gap: 12px;
  max-height: 420px;
  overflow-y: auto;
  padding: 4px;
  scrollbar-width: thin;
}
.gallery-card {
  position: relative;
  background: #ffffff;
  border: 1.5px solid #e2e8f0;
  border-radius: 8px;
  padding: 6px;
  cursor: pointer;
  transition: all 0.18s cubic-bezier(0.4, 0, 0.2, 1);
  display: flex;
  flex-direction: column;
  user-select: none;
  box-shadow: 0 1px 2px rgba(0,0,0,0.03);
}
.gallery-card:hover {
  transform: translateY(-2px);
  border-color: #cbd5e1;
  box-shadow: 0 4px 12px rgba(0,0,0,0.07);
}
.gallery-card.is-selected {
  border-color: #b80d25;
  background: #fff8f8;
  box-shadow: 0 0 0 2px rgba(184, 13, 37, 0.25), 0 4px 10px rgba(184, 13, 37, 0.12);
}
.gallery-card-thumb {
  position: relative;
  width: 100%;
  aspect-ratio: 4/3;
  border-radius: 6px;
  overflow: hidden;
  background: #f1f5f9;
}
.gallery-card-thumb img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
  transition: transform 0.2s ease;
}
.gallery-card:hover .gallery-card-thumb img {
  transform: scale(1.04);
}
.gallery-card-check {
  position: absolute;
  top: 5px;
  right: 5px;
  width: 22px;
  height: 22px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.85);
  border: 1.5px solid #cbd5e1;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
  font-weight: 700;
  color: transparent;
  transition: all 0.15s ease;
  backdrop-filter: blur(2px);
}
.gallery-card.is-selected .gallery-card-check {
  background: #b80d25;
  border-color: #b80d25;
  color: #ffffff;
  transform: scale(1.08);
}
.gallery-card-meta {
  margin-top: 6px;
  display: flex;
  flex-direction: column;
  gap: 2px;
}
.gallery-card-name {
  font-size: 11px;
  font-weight: 600;
  color: #1e293b;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
.gallery-card-sub {
  font-size: 10px;
  color: #64748b;
  font-family: monospace;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const grid = document.getElementById('gallery-picker-grid');
  if (!grid) return;

  const searchInput = document.getElementById('gallery-search');
  const cards = Array.from(grid.querySelectorAll('.gallery-card'));
  const shelf = document.getElementById('gallery-selected-shelf');
  const badge = document.getElementById('gallery-selected-badge');
  const filterCount = document.getElementById('gallery-filter-count');
  const visibleCount = document.getElementById('gallery-visible-count');
  const emptySearch = document.getElementById('gallery-empty-search');
  const searchTermSpan = document.getElementById('gallery-search-term');
  const filterBtns = document.querySelectorAll('.gallery-pill-btn[data-filter]');
  const selectAllBtn = document.getElementById('gallery-select-all-btn');
  const clearBtn = document.getElementById('gallery-clear-btn');

  let activeFilter = 'all'; // 'all' or 'selected'

  function updateShelf() {
    if (!shelf) return;
    shelf.innerHTML = '';
    const selectedCards = cards.filter(card => {
      const cb = card.querySelector('.gallery-checkbox');
      return cb && cb.checked;
    });

    if (selectedCards.length === 0) {
      shelf.classList.add('is-empty');
    } else {
      shelf.classList.remove('is-empty');
      selectedCards.forEach(card => {
        const id = card.dataset.id;
        const name = card.dataset.name || '';
        const thumb = card.dataset.thumb || '';
        const item = document.createElement('div');
        item.className = 'gallery-shelf-item';
        item.dataset.id = id;
        item.title = name;
        item.innerHTML = `
          <img src="${thumb}" alt="${name}" loading="lazy">
          <button type="button" class="gallery-shelf-remove" data-id="${id}" title="Remove photo" aria-label="Remove">✕</button>
        `;
        shelf.appendChild(item);
      });
    }

    if (badge) badge.textContent = `${selectedCards.length} Selected`;
    if (filterCount) filterCount.textContent = selectedCards.length;
  }

  function applyFilters() {
    const q = (searchInput?.value || '').toLowerCase().trim();
    if (searchTermSpan) searchTermSpan.textContent = q;
    let visibleTotal = 0;

    cards.forEach(card => {
      const cb = card.querySelector('.gallery-checkbox');
      const isChecked = cb && cb.checked;
      const name = (card.dataset.name || '').toLowerCase();
      const matchesSearch = !q || name.includes(q);
      const matchesFilter = activeFilter === 'all' || (activeFilter === 'selected' && isChecked);

      if (matchesSearch && matchesFilter) {
        card.style.display = '';
        visibleTotal++;
      } else {
        card.style.display = 'none';
      }
    });

    if (visibleCount) visibleCount.textContent = visibleTotal;
    if (emptySearch) {
      emptySearch.style.display = (visibleTotal === 0 && cards.length > 0) ? 'block' : 'none';
    }
  }

  function toggleCard(card, forcedState = null) {
    const cb = card.querySelector('.gallery-checkbox');
    if (!cb) return;
    const newState = forcedState !== null ? forcedState : !cb.checked;
    cb.checked = newState;
    card.classList.toggle('is-selected', newState);
    card.setAttribute('aria-checked', newState ? 'true' : 'false');
    updateShelf();
    if (activeFilter === 'selected' && !newState) {
      applyFilters();
    }
  }

  // Card click & keyboard interaction
  cards.forEach(card => {
    card.addEventListener('click', (e) => {
      e.preventDefault();
      toggleCard(card);
    });
    card.addEventListener('keydown', (e) => {
      if (e.key === ' ' || e.key === 'Enter') {
        e.preventDefault();
        toggleCard(card);
      }
    });
  });

  // Shelf remove button click
  shelf?.addEventListener('click', (e) => {
    const removeBtn = e.target.closest('.gallery-shelf-remove');
    if (!removeBtn) return;
    e.preventDefault();
    e.stopPropagation();
    const id = removeBtn.dataset.id;
    const targetCard = cards.find(c => c.dataset.id === id);
    if (targetCard) {
      toggleCard(targetCard, false);
    }
  });

  // Live search filtering
  searchInput?.addEventListener('input', applyFilters);

  // Filter tabs (All vs Selected)
  filterBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      filterBtns.forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      activeFilter = btn.dataset.filter || 'all';
      applyFilters();
    });
  });

  // Select all visible
  selectAllBtn?.addEventListener('click', () => {
    cards.forEach(card => {
      if (card.style.display !== 'none') {
        toggleCard(card, true);
      }
    });
  });

  // Clear all
  clearBtn?.addEventListener('click', () => {
    cards.forEach(card => toggleCard(card, false));
  });

  // Featured image preview logic in sidebar
  const featSelect = document.getElementById('featured_image_select');
  const featBox = document.getElementById('featured_image_preview_box');
  const featImg = document.getElementById('featured_image_preview_img');
  const featRemove = document.getElementById('featured_image_remove_btn');

  function updateFeaturedPreview() {
    if (!featSelect || !featBox || !featImg) return;
    const selectedOption = featSelect.options[featSelect.selectedIndex];
    const thumb = selectedOption ? selectedOption.dataset.thumb : null;
    if (featSelect.value && thumb) {
      featImg.src = thumb;
      featBox.style.display = 'block';
    } else {
      featImg.src = '';
      featBox.style.display = 'none';
    }
  }

  featSelect?.addEventListener('change', updateFeaturedPreview);
  featRemove?.addEventListener('click', () => {
    if (featSelect) {
      featSelect.value = '';
      updateFeaturedPreview();
    }
  });
  updateFeaturedPreview();
});
</script>
