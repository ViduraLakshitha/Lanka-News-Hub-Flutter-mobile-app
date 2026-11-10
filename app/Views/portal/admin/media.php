<?php
$stats = $stats ?? [
    'total' => 0,
    'images' => 0,
    'videos' => 0,
    'documents' => 0
];
$filterType = $filterType ?? ($_GET['type'] ?? '');
$searchQuery = $searchQuery ?? ($_GET['q'] ?? '');
?>

<!-- Header / Overview Banner -->
<div class="media-hero">
  <div class="media-hero-content">
    <h2>Editorial Media Library</h2>
    <p>Central asset repository for news photography, video journalism, and document whitepapers across all language editions.</p>
  </div>
  <div class="media-hero-actions">
    <button type="button" class="button" onclick="document.getElementById('media-upload-details').open = true; document.getElementById('media-upload-details').scrollIntoView({behavior: 'smooth'});">
      + Upload media asset
    </button>
    <form method="post" style="display:inline;margin:0;" onsubmit="return confirm('Populate realistic editorial news photos and document assets into the media library?');">
      <?= csrf() ?>
      <input type="hidden" name="action" value="seed_sample_media">
      <button type="submit" class="button secondary">⚡ Populate editorial samples</button>
    </form>
    <a class="button secondary" href="<?= e(url('category/local')) ?>" target="_blank" rel="noopener">Browse news stories ↗</a>
  </div>
</div>

<!-- Metrics Bar -->
<div class="stat-grid" style="margin-bottom:24px">
  <div class="stat">
    <span>Total media assets</span>
    <strong><?= (int)$stats['total'] ?></strong>
  </div>
  <div class="stat">
    <span>Photographs & graphics</span>
    <strong style="color:var(--accent-green, #16a34a)"><?= (int)$stats['images'] ?></strong>
  </div>
  <div class="stat">
    <span>Video reports</span>
    <strong style="color:var(--accent-blue, #2563eb)"><?= (int)$stats['videos'] ?></strong>
  </div>
  <div class="stat">
    <span>Whitepapers & PDFs</span>
    <strong style="color:var(--accent-gold, #d97706)"><?= (int)$stats['documents'] ?></strong>
  </div>
</div>

<!-- Collapsible Modern Upload Section -->
<details class="media-upload-section" id="media-upload-details">
  <summary>
    <span>➕ Upload new media asset</span>
    <span class="muted" style="font-weight:normal;font-size:12px">Supported: WebP, PNG, JPG, GIF, MP4, WebM, PDF (Up to 25 MB)</span>
  </summary>
  <div class="media-upload-body">
    <form method="post" enctype="multipart/form-data" class="form-stack">
      <?= csrf() ?>

      <div class="dropzone-container" id="media-dropzone">
        <input type="file" name="file" id="media-file-input" accept="image/jpeg,image/png,image/webp,image/gif,video/mp4,video/webm,application/pdf" required>
        <span class="dropzone-icon">☁️</span>
        <div class="dropzone-title">Choose a file or drag & drop it here</div>
        <div class="dropzone-hint">JPG, PNG, WebP, GIF, MP4, WebM or PDF · Maximum 25 MB · High resolution supported</div>
      </div>

      <!-- Live File Selection Banner -->
      <div id="media-file-preview" class="preview-banner" style="display:none">
        <img id="media-preview-img" src="" alt="Selected file preview" style="display:none">
        <div id="media-preview-icon" style="font-size:28px;display:none">📄</div>
        <div>
          <strong id="media-file-name" style="font-size:13px;display:block"></strong>
          <small id="media-file-size" class="muted" style="font-size:11px"></small>
        </div>
      </div>

      <div class="form-grid">
        <label>
          Image description / Alt text (Accessibility & SEO)
          <input name="alt_text" maxlength="255" placeholder="e.g. Finance Minister presenting 2026 national budget in Parliament">
          <small class="muted">Describes the image for screen readers and search engines.</small>
        </label>
        <label>
          Journalistic caption & credit
          <input name="caption" maxlength="2000" placeholder="e.g. Photo taken at Temple Trees, Colombo by Staff Photojournalist.">
          <small class="muted">Full caption displayed below photographs in news stories.</small>
        </label>
      </div>

      <div style="display:flex;gap:12px;align-items:center;margin-top:10px">
        <button class="button" type="submit">Upload to library ↑</button>
        <button type="button" class="button secondary" onclick="document.getElementById('media-upload-details').open = false;">Cancel</button>
      </div>
    </form>
  </div>
</details>

<!-- Search & Category Filters -->
<div class="media-filter-bar">
  <div class="filter-pills">
    <a class="filter-pill <?= empty($filterType) ? 'active' : '' ?>" href="<?= e(url('admin/media', array_filter(['q' => $searchQuery]))) ?>">
      All media (<?= (int)$stats['total'] ?>)
    </a>
    <a class="filter-pill <?= $filterType === 'image' ? 'active' : '' ?>" href="<?= e(url('admin/media', array_filter(['type' => 'image', 'q' => $searchQuery]))) ?>">
      📷 Photos (<?= (int)$stats['images'] ?>)
    </a>
    <a class="filter-pill <?= $filterType === 'video' ? 'active' : '' ?>" href="<?= e(url('admin/media', array_filter(['type' => 'video', 'q' => $searchQuery]))) ?>">
      🎥 Videos (<?= (int)$stats['videos'] ?>)
    </a>
    <a class="filter-pill <?= in_array($filterType, ['document', 'pdf']) ? 'active' : '' ?>" href="<?= e(url('admin/media', array_filter(['type' => 'document', 'q' => $searchQuery]))) ?>">
      📄 Documents (<?= (int)$stats['documents'] ?>)
    </a>
  </div>

  <form method="get" class="media-search-box" action="<?= e(url('admin/media')) ?>">
    <?php if (!empty($filterType)): ?>
      <input type="hidden" name="type" value="<?= e($filterType) ?>">
    <?php endif; ?>
    <input type="text" name="q" placeholder="Search by name, caption or alt..." value="<?= e($searchQuery) ?>" aria-label="Search media library">
    <button class="button secondary" type="submit" style="min-height:auto;padding:8px 14px">Search</button>
    <?php if (!empty($searchQuery)): ?>
      <a href="<?= e(url('admin/media', array_filter(['type' => $filterType]))) ?>" class="button secondary" style="min-height:auto;padding:8px 10px;text-decoration:none">✕</a>
    <?php endif; ?>
  </form>
</div>

<!-- Empty State -->
<?php if (!$items): ?>
  <div class="panel empty" style="text-align:center;padding:50px 20px">
    <div style="font-size:42px;margin-bottom:12px">🖼️</div>
    <h3 style="margin:0 0 8px">No media assets match your query</h3>
    <p class="muted" style="max-width:460px;margin:0 auto 20px">
      <?= !empty($searchQuery) ? 'Try searching for different keywords, or clear your filters to view all assets.' : 'Upload photos and videos to start building your editorial library.' ?>
    </p>
    <?php if (!empty($searchQuery) || !empty($filterType)): ?>
      <a href="<?= e(url('admin/media')) ?>" class="button secondary">Clear all filters</a>
    <?php else: ?>
      <button type="button" class="button" onclick="document.getElementById('media-upload-details').open = true;">Upload first asset ↑</button>
    <?php endif; ?>
  </div>
<?php endif; ?>

<!-- Media Assets Grid -->
<div class="media-grid-pro">
  <?php foreach ($items as $file): 
    $isImage = str_starts_with($file['file_type'], 'image/');
    $isVideo = str_starts_with($file['file_type'], 'video/');
    $isPdf = $file['file_type'] === 'application/pdf';
    $ext = pathinfo($file['file_path'], PATHINFO_EXTENSION);
    $formatLabel = strtoupper($ext ?: explode('/', $file['file_type'])[1] ?? 'FILE');
    $mediaUrl = media_url($file['file_path']);
  ?>
  <article class="media-card-pro">
    <!-- Thumbnail Preview Area -->
    <div class="media-thumb-area">
      <span class="media-badge-id">#<?= (int)$file['id'] ?></span>
      <span class="media-badge-format"><?= e($formatLabel) ?></span>

      <?php if ($isImage): ?>
        <img src="<?= e($mediaUrl) ?>" alt="<?= e($file['alt_text'] ?: $file['file_name']) ?>" loading="lazy">
      <?php elseif ($isVideo): ?>
        <div style="text-align:center;color:#fff">
          <div style="font-size:36px;margin-bottom:6px">🎥</div>
          <span style="font-size:11px;font-weight:600;letter-spacing:1px">VIDEO PLAYBACK</span>
        </div>
      <?php elseif ($isPdf): ?>
        <div style="text-align:center;color:#fff">
          <div style="font-size:36px;margin-bottom:6px">📄</div>
          <span style="font-size:11px;font-weight:600;letter-spacing:1px">PDF DOCUMENT</span>
        </div>
      <?php else: ?>
        <div style="text-align:center;color:#fff">
          <div style="font-size:36px;margin-bottom:6px">📁</div>
          <span style="font-size:11px;font-weight:600;letter-spacing:1px">ATTACHMENT</span>
        </div>
      <?php endif; ?>
    </div>

    <!-- Details Body -->
    <div class="media-card-info">
      <h3 title="<?= e($file['file_name']) ?>"><?= e($file['file_name']) ?></h3>
      
      <div class="media-card-meta">
        <span><?= e(date('d M Y', strtotime($file['created_at'] ?? 'now'))) ?></span>
        <?php if (!empty($file['uploader_name'])): ?>
          · <span>Uploaded by <?= e($file['uploader_name']) ?></span>
        <?php endif; ?>
      </div>

      <?php if (!empty($file['alt_text'])): ?>
        <div class="media-card-alt" title="<?= e($file['alt_text']) ?>">
          <strong>♿ Alt:</strong> <?= e($file['alt_text']) ?>
        </div>
      <?php else: ?>
        <div class="media-card-alt" style="color:#94a3b8;border-style:dashed">
          <em>No alt text provided</em>
        </div>
      <?php endif; ?>

      <?php if (!empty($file['caption'])): ?>
        <div class="media-card-alt" style="margin-top:6px;background:#fff;border-color:#e2e8f0" title="<?= e($file['caption']) ?>">
          <strong>📝</strong> <?= e($file['caption']) ?>
        </div>
      <?php endif; ?>
    </div>

    <!-- Action Bar -->
    <div class="media-card-actions">
      <button type="button" class="btn-copy-url" data-copy-url="<?= e($mediaUrl) ?>" title="Copy public URL to clipboard">
        <span>📋 Copy URL</span>
      </button>

      <a href="<?= e($mediaUrl) ?>" target="_blank" rel="noopener" class="btn-view-file">
        Preview ↗
      </a>

      <?php if ($user['role'] !== 'reporter'): ?>
        <form action="<?= e(url('admin/media/delete/' . $file['id'])) ?>" method="post" onsubmit="return confirm('Permanently delete this media asset? Files actively referenced in stories or advertisements cannot be deleted.');" style="margin:0">
          <?= csrf() ?>
          <button type="submit" class="btn-delete-file" title="Delete from media library">Delete</button>
        </form>
      <?php endif; ?>
    </div>
  </article>
  <?php endforeach; ?>
</div>

<?php require dirname(__DIR__) . '/pagination.php'; ?>

<!-- Client-side Interactive Behavior -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  // File input live preview
  const fileInput = document.getElementById('media-file-input');
  const previewBox = document.getElementById('media-file-preview');
  const previewImg = document.getElementById('media-preview-img');
  const previewIcon = document.getElementById('media-preview-icon');
  const previewName = document.getElementById('media-file-name');
  const previewSize = document.getElementById('media-file-size');
  const dropzone = document.getElementById('media-dropzone');

  if (fileInput) {
    fileInput.addEventListener('change', function() {
      if (this.files && this.files[0]) {
        const file = this.files[0];
        previewName.textContent = file.name;
        previewSize.textContent = (file.size / (1024 * 1024)).toFixed(2) + ' MB · ' + file.type;
        previewBox.style.display = 'flex';

        if (file.type.startsWith('image/')) {
          const reader = new FileReader();
          reader.onload = function(e) {
            previewImg.src = e.target.result;
            previewImg.style.display = 'block';
            previewIcon.style.display = 'none';
          };
          reader.readAsDataURL(file);
        } else {
          previewImg.style.display = 'none';
          previewIcon.style.display = 'block';
          previewIcon.textContent = file.type.startsWith('video/') ? '🎥' : '📄';
        }
      }
    });

    // Drag over feedback
    ['dragenter', 'dragover'].forEach(eventName => {
      dropzone.addEventListener(eventName, () => dropzone.classList.add('drag-over'), false);
    });
    ['dragleave', 'drop'].forEach(eventName => {
      dropzone.addEventListener(eventName, () => dropzone.classList.remove('drag-over'), false);
    });
  }

  // Copy URL with visual toast feedback
  document.querySelectorAll('[data-copy-url]').forEach(btn => {
    btn.addEventListener('click', function(e) {
      e.preventDefault();
      const url = this.getAttribute('data-copy-url');
      if (!url) return;

      const performFeedback = () => {
        const originalHtml = this.innerHTML;
        this.innerHTML = '<span>✓ Copied!</span>';
        this.classList.add('copied');
        setTimeout(() => {
          this.innerHTML = originalHtml;
          this.classList.remove('copied');
        }, 2200);
      };

      if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(url).then(performFeedback).catch(() => {
          fallbackCopy(url);
          performFeedback();
        });
      } else {
        fallbackCopy(url);
        performFeedback();
      }
    });
  });

  function fallbackCopy(text) {
    const tempInput = document.createElement('input');
    tempInput.value = text;
    document.body.appendChild(tempInput);
    tempInput.select();
    document.execCommand('copy');
    document.body.removeChild(tempInput);
  }
});
</script>
