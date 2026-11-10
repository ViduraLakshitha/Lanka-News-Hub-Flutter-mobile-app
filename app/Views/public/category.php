<?php include 'partials/header.php'; ?>

<main class="container my-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="<?= APP_URL ?>" class="text-danger"><i class="fas fa-home"></i> Home</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars(!empty($current_category['name_en']) ? $current_category['name_en'] : $current_category['name_si']) ?></li>
        </ol>
    </nav>

    <div class="section-heading-bar mb-4">
        <h1 class="section-heading-title" style="font-size: 1.65rem;">
            <span style="display:inline-block; width:12px; height:12px; border-radius:50%; background:<?= htmlspecialchars($current_category['color'] ?? '#c61a09') ?>; margin-right:4px;"></span>
            <?= htmlspecialchars(!empty($current_category['name_en']) ? $current_category['name_en'] : $current_category['name_si']) ?>
        </h1>
        <span class="text-muted small"><?= count($articles) ?> Stories Found</span>
    </div>

    <div class="row g-4">
        <!-- Main News Column -->
        <div class="col-lg-8">
            <div class="row g-4">
                <?php if (!empty($articles)): ?>
                    <?php foreach ($articles as $item): ?>
                    <div class="col-md-6">
                        <div class="news-card-premium">
                            <div class="news-card-thumb-wrap">
                                <span class="category-tag-pill"><?= htmlspecialchars(!empty($current_category['name_en']) ? $current_category['name_en'] : $current_category['name_si']) ?></span>
                                <a href="<?= APP_URL ?>/news/<?= $item['slug'] ?>">
                                    <img src="<?= !empty($item['image_path']) ? (APP_URL . '/' . $item['image_path']) : 'https://images.unsplash.com/photo-1504711434969-e33886168f5c?w=600&q=80' ?>" 
                                         class="news-card-thumb" 
                                         alt="<?= htmlspecialchars(!empty($item['title_en']) ? $item['title_en'] : $item['title_si']) ?>">
                                </a>
                            </div>
                            <div class="news-card-body">
                                <a href="<?= APP_URL ?>/news/<?= $item['slug'] ?>">
                                    <h3 class="news-card-title"><?= htmlspecialchars(!empty($item['title_en']) ? $item['title_en'] : $item['title_si']) ?></h3>
                                </a>
                                <p class="news-card-summary">
                                    <?= htmlspecialchars(mb_substr(!empty($item['summary_en']) ? $item['summary_en'] : ($item['summary_si'] ?? strip_tags($item['content_en'] ?? $item['content_si'])), 0, 110)) ?>...
                                </p>
                                <div class="news-meta-row">
                                    <span><i class="far fa-clock"></i> <?= date('M j, Y - H:i', strtotime($item['published_at'] ?? $item['created_at'])) ?></span>
                                    <span><i class="far fa-eye"></i> <?= $item['views'] ?? 0 ?> Views</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="card p-5 text-center bg-white border-0 shadow-sm rounded-3">
                            <i class="far fa-folder-open fa-3x text-muted mb-3"></i>
                            <h4 class="text-secondary">No articles published in this category yet.</h4>
                            <p class="text-muted small">Please check back later for updates.</p>
                            <a href="<?= APP_URL ?>" class="btn btn-outline-danger btn-sm align-self-center mt-2">Return to Home</a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Ad Space -->
            <div class="mb-4 text-center">
                <?= \App\Helpers\AdHelper::render('sidebar_300x250') ?>
            </div>

            <!-- Other Categories -->
            <div class="card border-0 shadow-sm p-4 bg-white rounded-3">
                <h4 class="fw-bold mb-3 fs-6 text-uppercase text-secondary">Explore Other Sections</h4>
                <div class="d-flex flex-wrap gap-2">
                    <?php foreach ($categories as $cat): ?>
                        <a href="<?= APP_URL ?>/category/<?= $cat['slug'] ?>" class="btn btn-sm btn-light border">
                            <?= htmlspecialchars(!empty($cat['name_en']) ? $cat['name_en'] : $cat['name_si']) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include 'partials/footer.php'; ?>
