<?php include dirname(__DIR__) . '/partials/header.php'; ?>
<?php include dirname(__DIR__) . '/partials/sidebar.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1">Add News Category</h3>
        <p class="text-muted small mb-0">Create a new section for organizing news articles.</p>
    </div>
    <a href="<?= APP_URL ?>/admin/categories" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i> Back to Categories
    </a>
</div>

<div class="admin-card">
    <div class="p-4">
        <form action="<?= APP_URL ?>/admin/categories/create" method="POST">
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="name" class="form-label fw-bold">Category Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control form-control-lg" id="name" name="name" placeholder="e.g. Sports, Politics, Technology" required autofocus>
                </div>
                
                <div class="col-md-6">
                    <label for="slug" class="form-label fw-bold">Category URL Slug <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted small"><?= APP_URL ?>/category/</span>
                        <input type="text" class="form-control" id="slug" name="slug" placeholder="sports" required>
                    </div>
                </div>

                <div class="col-md-4">
                    <label for="color" class="form-label fw-bold">Color Theme</label>
                    <input type="color" class="form-control form-control-color w-100" id="color" name="color" value="#dc2626">
                </div>

                <div class="col-md-4">
                    <label for="order_index" class="form-label fw-bold">Display Order</label>
                    <input type="number" class="form-control" id="order_index" name="order_index" value="0">
                </div>

                <div class="col-md-4">
                    <label for="status" class="form-label fw-bold">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>

            <div class="mt-4 pt-3 border-top">
                <button type="submit" class="btn btn-danger btn-lg px-4 fw-bold shadow-sm">
                    <i class="fas fa-save me-2"></i> Save Category
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.getElementById('name').addEventListener('input', function() {
        if (this.value.trim().length > 0) {
            let slug = this.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)+/g, '');
            document.getElementById('slug').value = slug;
        }
    });
</script>

<?php include dirname(__DIR__) . '/partials/footer.php'; ?>
