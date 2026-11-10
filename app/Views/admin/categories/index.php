<?php include dirname(__DIR__) . '/partials/header.php'; ?>
<?php include dirname(__DIR__) . '/partials/sidebar.php'; ?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h3 class="fw-bold mb-1">News Categories Management</h3>
        <p class="text-muted small mb-0">Organize and manage public editorial categories and navigation order.</p>
    </div>
    <a href="<?= APP_URL ?>/admin/categories/create" class="btn btn-danger px-4 py-2 fw-bold d-inline-flex align-items-center gap-2 shadow-sm">
        <i class="fas fa-plus-circle"></i> Add New Category
    </a>
</div>

<div class="admin-card">
    <div class="admin-card-header">
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-danger"><?= count($categories) ?></span>
            <span class="fw-bold text-secondary">Active & Inactive Sections</span>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table admin-table">
            <thead>
                <tr>
                    <th style="width: 70px;">ID</th>
                    <th>Category Name</th>
                    <th>Slug URL</th>
                    <th>Color Accent</th>
                    <th>Display Order</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($categories)): ?>
                    <?php foreach ($categories as $cat): ?>
                    <tr>
                        <td class="text-muted fw-bold">#<?= $cat['id'] ?></td>
                        <td>
                            <div class="fw-bold text-dark fs-6"><?= htmlspecialchars(!empty($cat['name_en']) ? $cat['name_en'] : $cat['name_si']) ?></div>
                        </td>
                        <td>
                            <code class="text-danger small">/category/<?= htmlspecialchars($cat['slug']) ?></code>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <span style="display:inline-block; width:18px; height:18px; background-color:<?= htmlspecialchars($cat['color'] ?? '#dc2626') ?>; border-radius:4px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);"></span>
                                <small class="text-muted"><?= htmlspecialchars($cat['color'] ?? '#dc2626') ?></small>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border"><?= $cat['order_index'] ?></span>
                        </td>
                        <td>
                            <?php if ($cat['status'] === 'active'): ?>
                                <span class="status-pill published"><i class="fas fa-circle" style="font-size: 0.45rem;"></i> Active</span>
                            <?php else: ?>
                                <span class="status-pill draft"><i class="fas fa-circle" style="font-size: 0.45rem;"></i> Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <a href="<?= APP_URL ?>/category/<?= $cat['slug'] ?>" target="_blank" class="btn btn-sm btn-outline-secondary me-1" title="View Category on Public Site">
                                <i class="fas fa-external-link-alt"></i>
                            </a>
                            <a href="<?= APP_URL ?>/admin/categories/delete/<?= $cat['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this category?')" title="Delete">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="fas fa-tags fa-3x mb-3 text-muted opacity-50"></i>
                            <h5>No categories defined yet</h5>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include dirname(__DIR__) . '/partials/footer.php'; ?>
