<?php include dirname(__DIR__) . '/partials/header.php'; ?>
<?php include dirname(__DIR__) . '/partials/sidebar.php'; ?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h3 class="fw-bold mb-1">News Article Management</h3>
        <p class="text-muted small mb-0">Create, review, publish, and organize editorial articles.</p>
    </div>
    <a href="<?= APP_URL ?>/admin/articles/create" class="btn btn-danger px-4 py-2 fw-bold d-inline-flex align-items-center gap-2 shadow-sm">
        <i class="fas fa-plus-circle"></i> Create New Article
    </a>
</div>

<div class="admin-card">
    <div class="admin-card-header">
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-danger"><?= count($articles) ?></span>
            <span class="fw-bold text-secondary">Total Published & Draft Stories</span>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table admin-table">
            <thead>
                <tr>
                    <th style="width: 70px;">ID</th>
                    <th>Headline</th>
                    <th>Category</th>
                    <th>Author</th>
                    <th>Status</th>
                    <th>Views</th>
                    <th>Date</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($articles)): ?>
                    <?php foreach ($articles as $art): ?>
                    <tr>
                        <td class="text-muted fw-bold">#<?= $art['id'] ?></td>
                        <td style="max-width: 360px;">
                            <div class="fw-bold text-dark mb-1" style="font-size: 0.95rem;">
                                <?= htmlspecialchars(!empty($art['title_en']) ? $art['title_en'] : $art['title_si']) ?>
                            </div>
                            <div class="mt-1 d-flex gap-1">
                                <?php if ($art['is_breaking']): ?>
                                    <span class="badge bg-danger" style="font-size: 0.65rem;">BREAKING</span>
                                <?php endif; ?>
                                <?php if ($art['is_featured']): ?>
                                    <span class="badge bg-warning text-dark" style="font-size: 0.65rem;">FEATURED</span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border"><?= htmlspecialchars(!empty($art['category_name_en']) ? $art['category_name_en'] : ($art['category_name'] ?? 'General')) ?></span>
                        </td>
                        <td>
                            <div class="small fw-semibold"><?= htmlspecialchars($art['author_name'] ?? 'Admin') ?></div>
                        </td>
                        <td>
                            <div class="small text-muted"><i class="far fa-eye me-1"></i> <?= $art['views'] ?></div>
                        </td>
                        <td>
                            <div class="small text-muted"><?= date('Y-m-d', strtotime($art['created_at'])) ?></div>
                            <small class="text-muted opacity-75" style="font-size: 0.72rem;"><?= date('H:i', strtotime($art['created_at'])) ?></small>
                        </td>
                        <td class="text-end">
                            <a href="<?= APP_URL ?>/news/<?= $art['slug'] ?>" target="_blank" class="btn btn-sm btn-outline-secondary me-1" title="View Public Post">
                                <i class="fas fa-external-link-alt"></i>
                            </a>
                            <a href="<?= APP_URL ?>/admin/articles/delete/<?= $art['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this article?')" title="Delete">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="fas fa-newspaper fa-3x mb-3 text-muted opacity-50"></i>
                            <h5>No articles published yet</h5>
                            <p class="small text-muted">Start writing your first news article by clicking the button above.</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include dirname(__DIR__) . '/partials/footer.php'; ?>
