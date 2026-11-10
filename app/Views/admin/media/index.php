<?php include dirname(__DIR__) . '/partials/header.php'; ?>
<?php include dirname(__DIR__) . '/partials/sidebar.php'; ?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h3 class="fw-bold mb-1">Media & Asset Library</h3>
        <p class="text-muted small mb-0">Upload and manage featured images, article photos, and banners.</p>
    </div>
</div>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i> <?= $_SESSION['success'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i> <?= $_SESSION['error'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<!-- Modern Upload Box -->
<div class="admin-card mb-4">
    <div class="admin-card-header">
        <h5 class="admin-card-title"><i class="fas fa-cloud-arrow-up text-danger me-2"></i> Upload New Image</h5>
    </div>
    <div class="p-4">
        <form action="<?= APP_URL ?>/admin/media/upload" method="POST" enctype="multipart/form-data" class="d-flex flex-column flex-md-row align-items-md-center gap-3">
            <div class="flex-grow-1">
                <input type="file" class="form-control form-control-lg" name="file" accept="image/jpeg, image/png, image/webp" required>
                <div class="form-text mt-1 text-muted">Supported formats: JPG, PNG, WEBP (Max 5MB).</div>
            </div>
            <button type="submit" class="btn btn-danger btn-lg px-4 fw-bold d-inline-flex align-items-center gap-2 shadow-sm">
                <i class="fas fa-upload"></i> Upload Media
            </button>
        </form>
    </div>
</div>

<!-- Media Grid -->
<div class="admin-card">
    <div class="admin-card-header">
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-danger"><?= count($media) ?></span>
            <span class="fw-bold text-secondary">Uploaded Assets</span>
        </div>
    </div>
    <div class="p-4">
        <div class="row g-4">
            <?php if (!empty($media)): ?>
                <?php foreach ($media as $item): ?>
                <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                    <div class="card h-100 border shadow-sm rounded-3 overflow-hidden position-relative group-hover">
                        <div class="ratio ratio-1x1 bg-light">
                            <img src="<?= APP_URL ?>/<?= $item['file_path'] ?>" class="object-fit-cover" alt="<?= htmlspecialchars($item['file_name']) ?>">
                        </div>
                        <div class="p-2 text-center bg-white">
                            <div class="small fw-semibold text-truncate mb-2" title="<?= htmlspecialchars($item['file_name']) ?>">
                                <?= htmlspecialchars($item['file_name']) ?>
                            </div>
                            <div class="d-flex justify-content-center gap-1">
                                <button type="button" class="btn btn-xs btn-outline-secondary copy-btn py-1 px-2 small" data-url="<?= APP_URL ?>/<?= $item['file_path'] ?>" title="Copy Image URL">
                                    <i class="fas fa-copy"></i>
                                </button>
                                <a href="<?= APP_URL ?>/admin/media/delete/<?= $item['id'] ?>" class="btn btn-xs btn-outline-danger py-1 px-2 small" onclick="return confirm('Delete this image permanently?')" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5 text-muted">
                    <i class="fas fa-images fa-3x mb-3 text-muted opacity-50"></i>
                    <h5>No media uploaded yet</h5>
                    <p class="small text-muted">Use the form above to upload images for your articles.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    document.querySelectorAll('.copy-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            navigator.clipboard.writeText(this.dataset.url).then(() => {
                let originalText = this.innerHTML;
                this.innerHTML = '<i class="fas fa-check text-success"></i>';
                setTimeout(() => this.innerHTML = originalText, 2000);
            });
        });
    });
</script>

<?php include dirname(__DIR__) . '/partials/footer.php'; ?>
