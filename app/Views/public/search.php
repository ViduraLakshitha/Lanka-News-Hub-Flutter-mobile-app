<?php include 'partials/header.php'; ?>

<main class="container my-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="<?= APP_URL ?>" class="text-danger"><i class="fas fa-home"></i> Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Search Results</li>
        </ol>
    </nav>

    <div class="section-heading-bar mb-4">
        <h1 class="section-heading-title" style="font-size: 1.5rem;">
            <i class="fas fa-search text-danger"></i> Search Results for: "<?= htmlspecialchars($query) ?>"
        </h1>
        <span class="text-muted small"><?= count($articles) ?> Articles Found</span>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="row g-4">
                <?php if (!empty($articles)): ?>
                    <?php foreach ($articles as $item): ?>
                    <div class="col-md-6">
                        <div class="news-card-premium">
                            <div class="news-card-thumb-wrap">
                                <span class="category-tag-pill"><?= htmlspecialchars(!empty($item['category_name_en']) ? $item['category_name_en'] : ($item['category_name'] ?? 'News')) ?></span>
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
                            <i class="fas fa-search fa-3x text-muted mb-3"></i>
                            <h4 class="text-secondary">No articles matched your search query.</h4>
                            <p class="text-muted small">Please try different keywords or check spelling.</p>
                            <form action="<?= APP_URL ?>/search" method="GET" class="col-md-6 mx-auto mt-3">
                                <div class="input-group">
                                    <input type="search" name="q" class="form-control" placeholder="Search again..." value="<?= htmlspecialchars($query) ?>" required>
                                    <button class="btn btn-danger" type="submit">Search</button>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="mb-4 text-center">
                <?= \App\Helpers\AdHelper::render('sidebar_300x250') ?>
            </div>
        </div>
    </div>
</main>

<?php include 'partials/footer.php'; ?>
