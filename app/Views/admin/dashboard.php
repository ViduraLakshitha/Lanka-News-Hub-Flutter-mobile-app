<?php include 'partials/header.php'; ?>
<?php include 'partials/sidebar.php'; ?>

<!-- Top Row: Elevated Stat Cards -->
<div class="row g-4 mb-4">
    <!-- Total Articles -->
    <div class="col-xl-3 col-md-6">
        <div class="stat-card-elevated">
            <div>
                <div class="stat-card-label">Total Articles</div>
                <h3 class="stat-card-value"><?= number_format($total_articles) ?></h3>
                <small class="text-success fw-bold mt-1 d-inline-block">
                    <i class="fas fa-check-circle me-1"></i> <?= $published_articles ?> Published
                </small>
            </div>
            <div class="stat-card-icon stat-icon-red">
                <i class="fas fa-newspaper"></i>
            </div>
        </div>
    </div>

    <!-- Total Views -->
    <div class="col-xl-3 col-md-6">
        <div class="stat-card-elevated">
            <div>
                <div class="stat-card-label">Article Pageviews</div>
                <h3 class="stat-card-value"><?= number_format($total_views) ?></h3>
                <small class="text-primary fw-bold mt-1 d-inline-block">
                    <i class="fas fa-chart-line me-1"></i> Lifetime Views
                </small>
            </div>
            <div class="stat-card-icon stat-icon-blue">
                <i class="fas fa-eye"></i>
            </div>
        </div>
    </div>

    <!-- Total Categories -->
    <div class="col-xl-3 col-md-6">
        <div class="stat-card-elevated">
            <div>
                <div class="stat-card-label">Categories</div>
                <h3 class="stat-card-value"><?= number_format($total_categories) ?></h3>
                <small class="text-warning fw-bold mt-1 d-inline-block">
                    <i class="fas fa-tags me-1"></i> Active Sections
                </small>
            </div>
            <div class="stat-card-icon stat-icon-amber">
                <i class="fas fa-layer-group"></i>
            </div>
        </div>
    </div>

    <!-- Media Assets -->
    <div class="col-xl-3 col-md-6">
        <div class="stat-card-elevated">
            <div>
                <div class="stat-card-label">Media Assets</div>
                <h3 class="stat-card-value"><?= number_format($total_media) ?></h3>
                <small class="text-success fw-bold mt-1 d-inline-block">
                    <i class="fas fa-cloud-upload-alt me-1"></i> Uploaded Images
                </small>
            </div>
            <div class="stat-card-icon stat-icon-green">
                <i class="fas fa-images"></i>
            </div>
        </div>
    </div>
</div>

<!-- Middle Row: Analytics Graph & Quick Actions -->
<div class="row g-4 mb-4">
    <!-- Traffic Chart -->
    <div class="col-lg-8">
        <div class="admin-card mb-0 h-100">
            <div class="admin-card-header">
                <h4 class="admin-card-title"><i class="fas fa-chart-area text-danger me-2"></i> Readership & Engagement Trend</h4>
                <span class="badge bg-light text-secondary border">Real-Time Traffic</span>
            </div>
            <div class="p-4">
                <canvas id="trafficAnalyticsChart" style="max-height: 270px;"></canvas>
            </div>
        </div>
    </div>

    <!-- Quick Editorial Actions -->
    <div class="col-lg-4">
        <div class="admin-card mb-0 h-100">
            <div class="admin-card-header">
                <h4 class="admin-card-title"><i class="fas fa-bolt text-danger me-2"></i> Quick Actions</h4>
            </div>
            <div class="p-4 d-flex flex-column gap-3">
                <a href="<?= APP_URL ?>/admin/articles/create" class="btn btn-danger btn-lg py-3 text-start d-flex align-items-center justify-content-between shadow-sm">
                    <div>
                        <div class="fw-bold fs-6"><i class="fas fa-plus-circle me-2"></i> Write New Article</div>
                        <small class="opacity-75">Publish breaking or featured news</small>
                    </div>
                    <i class="fas fa-arrow-right"></i>
                </a>

                <a href="<?= APP_URL ?>/admin/media" class="btn btn-light border py-3 text-start d-flex align-items-center justify-content-between">
                    <div>
                        <div class="fw-bold text-dark"><i class="fas fa-upload me-2 text-primary"></i> Upload Media</div>
                        <small class="text-muted">Images & Photo assets</small>
                    </div>
                    <i class="fas fa-chevron-right text-muted"></i>
                </a>

                <a href="<?= APP_URL ?>/admin/categories" class="btn btn-light border py-3 text-start d-flex align-items-center justify-content-between">
                    <div>
                        <div class="fw-bold text-dark"><i class="fas fa-tags me-2 text-warning"></i> Manage Sections</div>
                        <small class="text-muted">Edit or organize news categories</small>
                    </div>
                    <i class="fas fa-chevron-right text-muted"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Bottom Row: Recent Articles Table -->
<div class="admin-card">
    <div class="admin-card-header">
        <h4 class="admin-card-title"><i class="fas fa-clock-rotate-left text-danger me-2"></i> Recent Articles</h4>
        <a href="<?= APP_URL ?>/admin/articles" class="btn btn-sm btn-outline-danger">View All News</a>
    </div>
    <div class="table-responsive">
        <table class="table admin-table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Author</th>
                    <th>Views</th>
                    <th>Date</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($recent_articles)): ?>
                    <?php foreach ($recent_articles as $art): ?>
                    <tr>
                        <td style="max-width: 320px;">
                            <div class="fw-bold text-dark text-truncate"><?= htmlspecialchars($art['title_si']) ?></div>
                            <?php if ($art['is_breaking']): ?>
                                <span class="badge bg-danger" style="font-size: 0.65rem;">BREAKING</span>
                            <?php endif; ?>
                            <?php if ($art['is_featured']): ?>
                                <span class="badge bg-warning text-dark" style="font-size: 0.65rem;">FEATURED</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border"><?= htmlspecialchars($art['category_name'] ?? 'General') ?></span>
                        </td>
                        <td>
                            <?php if ($art['status'] === 'published'): ?>
                                <span class="status-pill published"><i class="fas fa-circle" style="font-size: 0.45rem;"></i> Published</span>
                            <?php elseif ($art['status'] === 'pending'): ?>
                                <span class="status-pill pending"><i class="fas fa-circle" style="font-size: 0.45rem;"></i> Pending</span>
                            <?php else: ?>
                                <span class="status-pill draft"><i class="fas fa-circle" style="font-size: 0.45rem;"></i> Draft</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($art['author_name'] ?? 'Admin') ?></td>
                        <td><i class="far fa-eye text-muted me-1"></i> <?= $art['views'] ?></td>
                        <td class="text-muted small"><?= date('M j, Y', strtotime($art['created_at'])) ?></td>
                        <td class="text-end">
                            <a href="<?= APP_URL ?>/news/<?= $art['slug'] ?>" target="_blank" class="btn btn-sm btn-outline-secondary me-1" title="View Article"><i class="fas fa-external-link-alt"></i></a>
                            <a href="<?= APP_URL ?>/admin/articles/delete/<?= $art['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this article?')" title="Delete"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="fas fa-newspaper fa-2x mb-2 d-block text-muted opacity-50"></i>
                            No articles created yet. Click "Write New Article" to start!
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Chart.js Script -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    const ctx = document.getElementById('trafficAnalyticsChart').getContext('2d');
    const chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
            datasets: [{
                label: 'Page Views',
                data: [1200, 1900, 3000, 5200, 4800, 6100, 7500],
                backgroundColor: 'rgba(220, 38, 38, 0.08)',
                borderColor: '#dc2626',
                borderWidth: 2.5,
                fill: true,
                tension: 0.35,
                pointBackgroundColor: '#dc2626',
                pointRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: '#f1f5f9' }
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });
});
</script>

<?php include 'partials/footer.php'; ?>
