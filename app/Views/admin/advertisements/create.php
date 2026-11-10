<?php include dirname(__DIR__) . '/partials/header.php'; ?>
<?php include dirname(__DIR__) . '/partials/sidebar.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1">Create Advertisement Campaign</h3>
        <p class="text-muted small mb-0">Upload creative banner images directly from your computer or configure custom AdSense tags.</p>
    </div>
    <a href="<?= APP_URL ?>/admin/advertisements" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i> Back to Advertisements
    </a>
</div>

<div class="admin-card">
    <div class="p-4">
        <form action="<?= APP_URL ?>/admin/advertisements/create" method="POST" enctype="multipart/form-data">
            <div class="row g-4">
                
                <div class="col-md-8">
                    <!-- Title -->
                    <div class="mb-3">
                        <label for="title" class="form-label fw-bold">Campaign Name / Sponsor <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-lg" id="title" name="title" placeholder="e.g. SriLankan Airlines Summer Promo" required autofocus>
                    </div>

                    <!-- Position Selector -->
                    <div class="mb-4">
                        <label for="position" class="form-label fw-bold">Ad Slot Position <span class="text-danger">*</span></label>
                        <select class="form-select form-select-lg" id="position" name="position" required>
                            <option value="header_728x90">Top Header Leaderboard (728 x 90)</option>
                            <option value="homepage_mid_728x90">Homepage Mid-Feed Banner (728 x 90 / Responsive)</option>
                            <option value="sidebar_300x250">Sidebar Medium Rectangle (300 x 250)</option>
                            <option value="sidebar_300x600">Sidebar Half-Page Sticky Banner (300 x 600)</option>
                        </select>
                    </div>

                    <!-- Direct Device File Upload Box -->
                    <div class="card p-3 bg-light border rounded-3 mb-4">
                        <label for="banner_file" class="form-label fw-bold text-dark mb-1">
                            <i class="fas fa-cloud-arrow-up text-danger me-1"></i> Upload Image Banner From Device
                        </label>
                        <input type="file" class="form-control form-control-lg" id="banner_file" name="banner_file" accept="image/jpeg, image/png, image/webp, image/gif" onchange="previewBannerFile(this)">
                        <div class="form-text mt-1 text-muted">Supports JPG, PNG, WEBP, GIF (Max 8MB).</div>

                        <!-- Live Banner Preview -->
                        <div id="bannerPreviewContainer" class="mt-3 text-center d-none">
                            <div class="small fw-bold text-secondary mb-1">Selected Image Preview:</div>
                            <img id="bannerImagePreview" src="" class="img-fluid rounded border shadow-sm" style="max-height: 180px; object-fit: contain;">
                        </div>
                    </div>

                    <!-- Target Destination Click URL -->
                    <div class="mb-3">
                        <label for="url" class="form-label fw-bold">Target Click-Through URL</label>
                        <input type="url" class="form-control" id="url" name="url" placeholder="https://example.com/promo">
                        <div class="form-text">Where users will be redirected when clicking your image banner.</div>
                    </div>

                    <!-- Optional External URL / CDN Link -->
                    <div class="mb-3">
                        <label for="image_path" class="form-label fw-semibold text-secondary">Or Use External Image URL (Optional)</label>
                        <input type="text" class="form-control" id="image_path" name="image_path" placeholder="https://example.com/banner.jpg">
                        <div class="form-text">Only needed if you are hosting the banner on an external CDN instead of uploading.</div>
                    </div>

                    <!-- Custom HTML / Google AdSense Code -->
                    <div class="mb-3">
                        <label for="ad_code" class="form-label fw-bold">Custom Code / Google AdSense Script (Optional)</label>
                        <textarea class="form-control font-monospace" id="ad_code" name="ad_code" rows="4" placeholder="<script async src='https://pagead2.googlesyndication.com...'></script>"></textarea>
                        <div class="form-text">If entered, custom HTML/AdSense code will override image banners.</div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card p-3 bg-light border-0 rounded-3 mb-3">
                        <h6 class="fw-bold mb-3">Campaign Schedule</h6>
                        
                        <div class="mb-3">
                            <label for="start_date" class="form-label small fw-bold">Start Date</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" value="<?= date('Y-m-d') ?>">
                        </div>

                        <div class="mb-3">
                            <label for="end_date" class="form-label small fw-bold">End Date (Expiry)</label>
                            <input type="date" class="form-control" id="end_date" name="end_date">
                            <small class="text-muted" style="font-size: 0.72rem;">Leave empty to run without expiration.</small>
                        </div>

                        <div class="form-check form-switch mt-3 pt-3 border-top">
                            <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" checked>
                            <label class="form-check-label fw-bold text-success" for="is_active">
                                Active & Live
                            </label>
                        </div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-danger btn-lg py-3 fw-bold shadow-sm">
                            <i class="fas fa-paper-plane me-2"></i> Save & Publish Ad
                        </button>
                    </div>
                </div>

            </div>
        </form>
    </div>
</div>

<script>
    function previewBannerFile(input) {
        const previewContainer = document.getElementById('bannerPreviewContainer');
        const previewImg = document.getElementById('bannerImagePreview');
        
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                previewContainer.classList.remove('d-none');
            };
            reader.readAsDataURL(input.files[0]);
        } else {
            previewContainer.classList.add('d-none');
        }
    }
</script>

<?php include dirname(__DIR__) . '/partials/footer.php'; ?>
