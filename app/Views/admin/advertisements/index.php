<?php include dirname(__DIR__) . '/partials/header.php'; ?>
<?php include dirname(__DIR__) . '/partials/sidebar.php'; ?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h3 class="fw-bold mb-1">Advertisement Management</h3>
        <p class="text-muted small mb-0">Manage website ad banners, Google AdSense tags, and promotional campaigns.</p>
    </div>
    <a href="<?= APP_URL ?>/admin/advertisements/create" class="btn btn-danger px-4 py-2 fw-bold d-inline-flex align-items-center gap-2 shadow-sm">
        <i class="fas fa-plus-circle"></i> Create New Campaign
    </a>
</div>

<div class="admin-card">
    <div class="admin-card-header">
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-danger"><?= count($ads) ?></span>
            <span class="fw-bold text-secondary">Active & Scheduled Ad Units</span>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table admin-table">
            <thead>
                <tr>
                    <th style="width: 60px;">ID</th>
                    <th>Campaign Name</th>
                    <th>Slot Position</th>
                    <th>Type / Preview</th>
                    <th>Schedule</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($ads)): ?>
                    <?php foreach ($ads as $ad): ?>
                    <tr>
                        <td class="text-muted fw-bold">#<?= $ad['id'] ?></td>
                        <td>
                            <div class="fw-bold text-dark fs-6"><?= htmlspecialchars($ad['title']) ?></div>
                            <?php if (!empty($ad['url'])): ?>
                                <a href="<?= htmlspecialchars($ad['url']) ?>" target="_blank" class="small text-muted text-decoration-none">
                                    <i class="fas fa-link me-1 text-danger"></i> <?= htmlspecialchars($ad['url']) ?>
                                </a>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border font-monospace"><?= htmlspecialchars($ad['position']) ?></span>
                        </td>
                        <td>
                            <?php if (!empty($ad['image_path'])): ?>
                                <span class="badge bg-primary"><i class="fas fa-image me-1"></i> Image Banner</span>
                            <?php elseif (!empty($ad['ad_code'])): ?>
                                <span class="badge bg-dark"><i class="fas fa-code me-1"></i> Custom Code / AdSense</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Empty</span>
                            <?php endif; ?>
                        </td>
                        <td class="small text-muted">
                            <div><strong>Start:</strong> <?= $ad['start_date'] ? date('M j, Y', strtotime($ad['start_date'])) : 'Immediately' ?></div>
                            <div><strong>End:</strong> <?= $ad['end_date'] ? date('M j, Y', strtotime($ad['end_date'])) : 'Never Expires' ?></div>
                        </td>
                        <td>
                            <?php if ($ad['is_active']): ?>
                                <span class="status-pill published"><i class="fas fa-circle" style="font-size: 0.45rem;"></i> Active</span>
                            <?php else: ?>
                                <span class="status-pill draft"><i class="fas fa-circle" style="font-size: 0.45rem;"></i> Disabled</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <a href="<?= APP_URL ?>/admin/advertisements/delete/<?= $ad['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this advertisement?')" title="Delete Campaign">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="fas fa-ad fa-3x mb-3 text-muted opacity-50"></i>
                            <h5>No advertisements created yet</h5>
                            <p class="small text-muted">Create your first ad campaign to display on the public website.</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include dirname(__DIR__) . '/partials/footer.php'; ?>
