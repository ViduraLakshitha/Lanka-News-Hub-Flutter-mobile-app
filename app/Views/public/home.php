<?php include 'partials/header.php'; ?>

<main class="container my-4">

    <!-- =========================================================================
         SECTION 1: HERO SPOTLIGHT (Main Story + 2 Secondary Highlights)
         ========================================================================= -->
    <div class="row g-4 mb-5">
        <!-- Main Large Hero Story -->
        <div class="col-lg-8">
            <?php if (!empty($latest[0])): $hero = $latest[0]; ?>
            <div class="news-card-premium">
                <div class="news-card-thumb-wrap" style="aspect-ratio: 16/9;">
                    <span class="category-tag-pill"><?= htmlspecialchars(!empty($hero['category_name_en']) ? $hero['category_name_en'] : ($hero['category_name'] ?? 'News')) ?></span>
                    <a href="<?= APP_URL ?>/news/<?= $hero['slug'] ?>">
                        <img src="<?= !empty($hero['image_path']) ? (APP_URL . '/' . $hero['image_path']) : 'https://images.unsplash.com/photo-1585829365295-ab7cd400c167?w=1000&q=80' ?>" 
                             class="news-card-thumb" 
                             alt="<?= htmlspecialchars(!empty($hero['title_en']) ? $hero['title_en'] : $hero['title_si']) ?>">
                    </a>
                </div>
                <div class="news-card-body p-4">
                    <a href="<?= APP_URL ?>/news/<?= $hero['slug'] ?>">
                        <h1 class="news-card-title news-card-title-lg mb-3">
                            <?= htmlspecialchars(!empty($hero['title_en']) ? $hero['title_en'] : $hero['title_si']) ?>
                        </h1>
                    </a>
                    <p class="news-card-summary mb-3">
                        <?= htmlspecialchars(mb_substr(!empty($hero['summary_en']) ? $hero['summary_en'] : ($hero['summary_si'] ?? strip_tags($hero['content_en'] ?? $hero['content_si'])), 0, 180)) ?>...
                    </p>
                    <div class="news-meta-row">
                        <span><i class="far fa-clock"></i> <?= date('F j, Y, g:i A', strtotime($hero['published_at'] ?? $hero['created_at'])) ?></span>
                        <?php if (!empty($hero['author_name'])): ?>
                            <span><i class="far fa-user"></i> <?= htmlspecialchars($hero['author_name']) ?></span>
                        <?php endif; ?>
                        <span><i class="far fa-eye"></i> <?= $hero['views'] ?? 0 ?> Views</span>
                    </div>
                </div>
            </div>
            <?php else: ?>
                <div class="alert alert-info py-5 text-center">
                    <i class="fas fa-info-circle fa-2x mb-3 d-block"></i>
                    No articles published yet. Please log into the Admin Panel to publish your first news article.
                </div>
            <?php endif; ?>
        </div>

        <!-- Secondary Stories (Right Column) -->
        <div class="col-lg-4">
            <div class="d-flex flex-column gap-3 h-100">
                <?php for ($i = 1; $i <= 2; $i++): if (!empty($latest[$i])): $sec = $latest[$i]; ?>
                <div class="news-card-premium flex-grow-1">
                    <div class="news-card-thumb-wrap" style="aspect-ratio: 16/9; max-height: 165px;">
                        <span class="category-tag-pill" style="font-size: 0.68rem; padding: 2px 8px;">
                            <?= htmlspecialchars(!empty($sec['category_name_en']) ? $sec['category_name_en'] : ($sec['category_name'] ?? 'News')) ?>
                        </span>
                        <a href="<?= APP_URL ?>/news/<?= $sec['slug'] ?>">
                            <img src="<?= !empty($sec['image_path']) ? (APP_URL . '/' . $sec['image_path']) : ('https://images.unsplash.com/photo-1504711434969-e33886168f5c?w=600&q=80&sig=' . $i) ?>" 
                                 class="news-card-thumb" 
                                 alt="<?= htmlspecialchars(!empty($sec['title_en']) ? $sec['title_en'] : $sec['title_si']) ?>">
                        </a>
                    </div>
                    <div class="news-card-body p-3">
                        <a href="<?= APP_URL ?>/news/<?= $sec['slug'] ?>">
                            <h3 class="news-card-title" style="font-size: 1.05rem;">
                                <?= htmlspecialchars(!empty($sec['title_en']) ? $sec['title_en'] : $sec['title_si']) ?>
                            </h3>
                        </a>
                        <div class="news-meta-row mt-auto pt-2">
                            <span><i class="far fa-clock"></i> <?= date('M j, Y - H:i', strtotime($sec['published_at'] ?? $sec['created_at'])) ?></span>
                        </div>
                    </div>
                </div>
                <?php endif; endfor; ?>
            </div>
        </div>
    </div>

    <!-- =========================================================================
         SECTION 2: LATEST NEWS GRID & SIDEBAR (Most Read + Ads)
         ========================================================================= -->
    <div class="row g-4">
        <!-- Left Main Column (Latest News) -->
        <div class="col-lg-8">
            <div class="section-heading-bar">
                <h2 class="section-heading-title">
                    <i class="fas fa-newspaper"></i> Latest News Stories
                </h2>
            </div>

            <div class="row g-4">
                <?php for ($i = 3; $i < count($latest); $i++): $item = $latest[$i]; ?>
                <div class="col-md-6">
                    <div class="news-card-premium">
                        <div class="news-card-thumb-wrap">
                            <span class="category-tag-pill"><?= htmlspecialchars(!empty($item['category_name_en']) ? $item['category_name_en'] : ($item['category_name'] ?? 'News')) ?></span>
                            <a href="<?= APP_URL ?>/news/<?= $item['slug'] ?>">
                                <img src="<?= !empty($item['image_path']) ? (APP_URL . '/' . $item['image_path']) : ('https://images.unsplash.com/photo-1495020689067-958852a7765e?w=600&q=80&sig=' . $i) ?>" 
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
                <?php endfor; ?>
                
                <?php if (count($latest) <= 3): ?>
                <div class="col-12">
                    <div class="card p-4 text-center border-dashed text-secondary">
                        <i class="far fa-newspaper fa-2x mb-2 text-danger"></i>
                        <p class="mb-0">More stories will appear here as they are published via the Newsroom CMS.</p>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Mid-Content Banner Ad -->
            <div class="my-5">
                <?= \App\Helpers\AdHelper::render('homepage_mid_728x90') ?>
            </div>

        </div>

        <!-- Right Sidebar (Most Read & Sticky Ads) -->
        <div class="col-lg-4">
            
            <!-- Most Read Widget -->
            <div class="card border-0 shadow-sm p-4 bg-white rounded-3 mb-4">
                <div class="section-heading-bar mb-3">
                    <h3 class="section-heading-title" style="font-size: 1.1rem;">
                        <i class="fas fa-fire text-danger"></i> Most Read Stories
                    </h3>
                </div>

                <div class="ranked-list">
                    <?php 
                    $mostRead = $latest;
                    usort($mostRead, fn($a, $b) => ($b['views'] ?? 0) <=> ($a['views'] ?? 0));
                    foreach (array_slice($mostRead, 0, 5) as $idx => $pop): 
                    ?>
                    <div class="ranked-card-item">
                        <div class="rank-number-badge"><?= $idx + 1 ?></div>
                        <div>
                            <span class="badge bg-light text-danger fw-bold mb-1" style="font-size: 0.68rem;">
                                <?= htmlspecialchars(!empty($pop['category_name_en']) ? $pop['category_name_en'] : ($pop['category_name'] ?? 'News')) ?>
                            </span>
                            <a href="<?= APP_URL ?>/news/<?= $pop['slug'] ?>" class="rank-news-title d-block">
                                <?= htmlspecialchars(!empty($pop['title_en']) ? $pop['title_en'] : $pop['title_si']) ?>
                            </a>
                            <small class="text-muted" style="font-size: 0.72rem;">
                                <i class="far fa-eye me-1"></i> <?= $pop['views'] ?? 0 ?> views
                            </small>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Square 300x250 Ad -->
            <div class="mb-4 text-center">
                <?= \App\Helpers\AdHelper::render('sidebar_300x250') ?>
            </div>

            <!-- Social Media Follow Box -->
            <div class="card border-0 shadow-sm p-4 bg-white rounded-3">
                <h4 class="fw-bold mb-3 fs-6 text-uppercase text-secondary">Follow Our Channels</h4>
                <div class="d-grid gap-2">
                    <a href="#" class="btn btn-primary btn-sm text-start py-2 d-flex align-items-center justify-content-between" style="background:#1877f2; border:none;">
                        <span><i class="fab fa-facebook-f me-2"></i> Follow on Facebook</span>
                        <span class="badge bg-white text-primary">150K</span>
                    </a>
                    <a href="#" class="btn btn-danger btn-sm text-start py-2 d-flex align-items-center justify-content-between" style="background:#ff0000; border:none;">
                        <span><i class="fab fa-youtube me-2"></i> Subscribe on YouTube</span>
                        <span class="badge bg-white text-danger">85K</span>
                    </a>
                    <a href="#" class="btn btn-info btn-sm text-white text-start py-2 d-flex align-items-center justify-content-between" style="background:#229ed9; border:none;">
                        <span><i class="fab fa-telegram-plane me-2"></i> Join Telegram Channel</span>
                        <span class="badge bg-white text-info">30K</span>
                    </a>
                </div>
            </div>

        </div>
    </div>

</main>

<?php include 'partials/footer.php'; ?>
