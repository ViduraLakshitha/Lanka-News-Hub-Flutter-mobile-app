<?php include dirname(__DIR__) . '/partials/header.php'; ?>
<?php include dirname(__DIR__) . '/partials/sidebar.php'; ?>

<!-- CKEditor 5 CDN -->
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1">Create Editorial Article</h3>
        <p class="text-muted small mb-0">Write and publish digital news stories with rich media and SEO optimization.</p>
    </div>
    <a href="<?= APP_URL ?>/admin/articles" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i> Back to Articles
    </a>
</div>

<form action="<?= APP_URL ?>/admin/articles/create" method="POST" id="articleForm">
    <div class="row g-4">
        
        <!-- Left 8 Columns: Main Editor Workspace -->
        <div class="col-lg-8">
            <div class="admin-card mb-4">
                <div class="p-4">
                    
                    <!-- Headline English -->
                    <div class="mb-3">
                        <label for="title" class="form-label fw-bold">
                            Article Headline / Title <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control form-control-lg fw-bold" id="title" name="title" placeholder="e.g. Sri Lanka Launches Major Renewable Energy Initiative in Southern Province" required>
                    </div>

                    <!-- Slug URL -->
                    <div class="mb-3">
                        <label for="slug" class="form-label fw-bold text-secondary">
                            SEO Permalink / Slug <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light small text-muted"><?= APP_URL ?>/news/</span>
                            <input type="text" class="form-control" id="slug" name="slug" placeholder="sri-lanka-launches-major-renewable-energy-initiative" required>
                        </div>
                    </div>

                    <!-- Summary / Lead Paragraph -->
                    <div class="mb-3">
                        <label for="summary" class="form-label fw-bold">
                            Article Summary / Lead Paragraph
                        </label>
                        <textarea class="form-control" id="summary" name="summary" rows="3" placeholder="Provide a concise 2-3 sentence overview of the news story..."></textarea>
                    </div>

                    <!-- Rich Body Content -->
                    <div class="mb-3">
                        <label for="content" class="form-label fw-bold">
                            Full Article Content <span class="text-danger">*</span>
                        </label>
                        <textarea id="content" name="content"></textarea>
                    </div>

                </div>
            </div>

            <!-- Google Search Snippet Preview Box -->
            <div class="admin-card">
                <div class="admin-card-header">
                    <h5 class="admin-card-title"><i class="fab fa-google text-primary me-2"></i> Google Search SERP Preview</h5>
                </div>
                <div class="p-4">
                    <div class="serp-preview-box">
                        <div class="serp-url" id="serpUrlPreview"><?= APP_URL ?>/news/...</div>
                        <div class="serp-title" id="serpTitlePreview">Article Headline Will Appear Here...</div>
                        <div class="serp-desc" id="serpDescPreview">The summary of your article will appear in search engine results like this...</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right 4 Columns: Sticky Publishing Controls -->
        <div class="col-lg-4">
            <div class="admin-card sticky-top" style="top: 20px;">
                <div class="admin-card-header">
                    <h5 class="admin-card-title"><i class="fas fa-sliders text-danger me-2"></i> Publishing Settings</h5>
                </div>
                <div class="p-4">

                    <!-- Status Dropdown -->
                    <div class="mb-3">
                        <label for="status" class="form-label fw-bold">Publication Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="published" selected>🚀 Published (Live Immediately)</option>
                            <option value="draft">📝 Draft (Save Privately)</option>
                            <option value="pending">⏳ Pending Editorial Review</option>
                        </select>
                    </div>

                    <!-- Category Selector -->
                    <div class="mb-3">
                        <label for="category_id" class="form-label fw-bold">Category <span class="text-danger">*</span></label>
                        <select class="form-select" id="category_id" name="category_id" required>
                            <option value="">-- Select Category --</option>
                            <?php if (!empty($categories)): ?>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars(!empty($cat['name_en']) ? $cat['name_en'] : $cat['name_si']) ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <!-- Editorial Priority Toggles -->
                    <div class="card p-3 bg-light border-0 rounded-3 mb-3">
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" role="switch" id="is_breaking" name="is_breaking" value="1">
                            <label class="form-check-label fw-bold text-danger" for="is_breaking">
                                <i class="fas fa-bolt me-1"></i> Breaking News Ticker
                            </label>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="is_featured" name="is_featured" value="1">
                            <label class="form-check-label fw-bold text-dark" for="is_featured">
                                <i class="fas fa-star text-warning me-1"></i> Featured Story
                            </label>
                        </div>
                    </div>

                    <!-- Featured Image Selector -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">Featured Image</label>
                        <input type="hidden" id="featured_image_id" name="featured_image_id" value="">
                        
                        <!-- Thumbnail Preview Area -->
                        <div id="imagePreviewBox" class="border rounded-3 p-2 text-center bg-light mb-2" style="min-height: 120px; display: flex; align-items: center; justify-content: center;">
                            <div class="text-muted small">
                                <i class="far fa-image fa-2x mb-1 d-block opacity-50"></i>
                                No image selected
                            </div>
                        </div>

                        <button class="btn btn-outline-danger w-100 btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#mediaLibraryModal">
                            <i class="fas fa-images me-1"></i> Choose from Media Library
                        </button>
                    </div>

                    <!-- Submit Button -->
                    <div class="d-grid">
                        <button type="submit" class="btn btn-danger btn-lg py-3 fw-bold shadow-sm">
                            <i class="fas fa-paper-plane me-2"></i> Save & Publish Article
                        </button>
                    </div>

                </div>
            </div>
        </div>

    </div>
</form>

<!-- Media Library Modal -->
<div class="modal fade" id="mediaLibraryModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title"><i class="fas fa-photo-film me-2 text-danger"></i> Media Library Picker</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <div class="row g-3">
            <?php if (!empty($media)): ?>
                <?php foreach ($media as $item): ?>
                <div class="col-xl-2 col-lg-3 col-md-4 col-6">
                    <div class="card h-100 border text-center p-2 cursor-pointer media-select-item" 
                         onclick="selectMediaItem(<?= $item['id'] ?>, '<?= APP_URL ?>/<?= $item['file_path'] ?>')">
                        <div class="ratio ratio-1x1 bg-light rounded overflow-hidden mb-2">
                            <img src="<?= APP_URL ?>/<?= $item['file_path'] ?>" class="object-fit-cover w-100 h-100" alt="Image">
                        </div>
                        <small class="text-truncate d-block fw-semibold" style="font-size: 0.75rem;">
                            <?= htmlspecialchars($item['file_name']) ?>
                        </small>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5 text-muted">
                    <i class="fas fa-images fa-3x mb-2 opacity-50"></i>
                    <p>No media files found. Please upload images in the Media Library first.</p>
                </div>
            <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<style>
    .cursor-pointer { cursor: pointer; transition: all 0.2s ease; }
    .media-select-item:hover { border-color: #dc2626 !important; transform: scale(1.03); }
    .ck-editor__editable_inline { min-height: 380px; }
</style>

<script>
    // Initialize CKEditor 5
    ClassicEditor
        .create(document.querySelector('#content'))
        .catch(error => { console.error(error); });

    // Auto Slug Generator from English Headline
    document.getElementById('title').addEventListener('input', function() {
        const slugField = document.getElementById('slug');
        if (this.value.trim().length > 0) {
            let generated = this.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)+/g, '');
            slugField.value = generated;
            updateSerpPreview();
        }
    });

    // Realtime SERP Preview
    document.getElementById('title').addEventListener('input', updateSerpPreview);
    document.getElementById('summary').addEventListener('input', updateSerpPreview);
    document.getElementById('slug').addEventListener('input', updateSerpPreview);

    function updateSerpPreview() {
        const titleVal = document.getElementById('title').value || 'Article Headline Will Appear Here...';
        const summaryVal = document.getElementById('summary').value || 'The summary of your article will appear in search engine results like this...';
        const slugVal = document.getElementById('slug').value || '...';
        
        document.getElementById('serpTitlePreview').textContent = titleVal + ' - Lanka News Hub';
        document.getElementById('serpDescPreview').textContent = summaryVal.substring(0, 155) + '...';
        document.getElementById('serpUrlPreview').textContent = '<?= APP_URL ?>/news/' + slugVal;
    }

    // Media Picker Function
    function selectMediaItem(id, url) {
        document.getElementById('featured_image_id').value = id;
        document.getElementById('imagePreviewBox').innerHTML = `<img src="${url}" class="img-fluid rounded" style="max-height: 140px; object-fit: cover; width: 100%;">`;
        
        const modal = bootstrap.Modal.getInstance(document.getElementById('mediaLibraryModal'));
        if (modal) {
            modal.hide();
        }
    }
</script>

<?php include dirname(__DIR__) . '/partials/footer.php'; ?>
