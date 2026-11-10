<?php include 'partials/header.php'; ?>

<main class="container my-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="<?= APP_URL ?>" class="text-danger"><i class="fas fa-home"></i> Home</a></li>
            <li class="breadcrumb-item"><a href="<?= APP_URL ?>/category/<?= $article['category_slug'] ?>" class="text-danger"><?= htmlspecialchars(!empty($article['category_name_en']) ? $article['category_name_en'] : $article['category_name']) ?></a></li>
            <li class="breadcrumb-item active text-truncate" aria-current="page" style="max-width: 350px;"><?= htmlspecialchars(!empty($article['title_en']) ? $article['title_en'] : $article['title_si']) ?></li>
        </ol>
    </nav>

    <div class="row g-4">
        <!-- Main Article Column -->
        <div class="col-lg-8">
            <div class="article-container">
                
                <!-- Category Tag -->
                <a href="<?= APP_URL ?>/category/<?= $article['category_slug'] ?>" class="badge bg-danger text-uppercase px-3 py-2 fw-bold text-decoration-none mb-3 d-inline-block">
                    <?= htmlspecialchars(!empty($article['category_name_en']) ? $article['category_name_en'] : $article['category_name']) ?>
                </a>

                <!-- Headline -->
                <h1 class="article-headline mb-3">
                    <?= htmlspecialchars(!empty($article['title_en']) ? $article['title_en'] : $article['title_si']) ?>
                </h1>

                <!-- Metadata Row -->
                <div class="article-meta-header">
                    <div class="author-pill">
                        <div class="author-avatar">
                            <i class="fas fa-user-edit"></i>
                        </div>
                        <div>
                            <span><?= htmlspecialchars($article['author_name'] ?? 'Editorial Desk') ?></span>
                        </div>
                    </div>
                    
                    <div>
                        <i class="far fa-clock text-danger me-1"></i>
                        <?= date('F j, Y - g:i A', strtotime($article['published_at'] ?? $article['created_at'])) ?>
                    </div>

                    <div>
                        <i class="far fa-eye text-danger me-1"></i>
                        <?= $article['views'] ?? 0 ?> Views
                    </div>

                    <!-- Font Resizer & Print -->
                    <div class="d-none d-sm-flex align-items-center gap-2 ms-auto">
                        <button class="btn btn-sm btn-outline-secondary py-0 px-2" onclick="changeFontSize(1)" title="Increase Font Size">A+</button>
                        <button class="btn btn-sm btn-outline-secondary py-0 px-2" onclick="changeFontSize(-1)" title="Decrease Font Size">A-</button>
                        <button class="btn btn-sm btn-outline-secondary py-0 px-2" onclick="window.print()" title="Print Article"><i class="fas fa-print"></i></button>
                    </div>
                </div>

                <!-- Social Share Buttons -->
                <div class="d-flex align-items-center justify-content-between my-3 p-2 bg-light rounded-2">
                    <span class="small fw-bold text-secondary ps-2">Share this story:</span>
                    <div class="share-action-group">
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode(APP_URL . '/news/' . $article['slug']) ?>" target="_blank" class="share-btn share-fb" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="https://twitter.com/intent/tweet?url=<?= urlencode(APP_URL . '/news/' . $article['slug']) ?>&text=<?= urlencode(!empty($article['title_en']) ? $article['title_en'] : $article['title_si']) ?>" target="_blank" class="share-btn share-tw" title="Twitter"><i class="fab fa-x-twitter"></i></a>
                        <a href="https://api.whatsapp.com/send?text=<?= urlencode((!empty($article['title_en']) ? $article['title_en'] : $article['title_si']) . ' - ' . APP_URL . '/news/' . $article['slug']) ?>" target="_blank" class="share-btn share-wa" title="WhatsApp"><i class="fab fa-whatsapp"></i></a>
                        <a href="https://t.me/share/url?url=<?= urlencode(APP_URL . '/news/' . $article['slug']) ?>&text=<?= urlencode(!empty($article['title_en']) ? $article['title_en'] : $article['title_si']) ?>" target="_blank" class="share-btn share-tg" title="Telegram"><i class="fab fa-telegram-plane"></i></a>
                        <button class="share-btn bg-secondary border-0" onclick="copyArticleUrl()" title="Copy Link"><i class="fas fa-link"></i></button>
                    </div>
                </div>

                <!-- Featured Image -->
                <?php if (!empty($article['image_path'])): ?>
                <div class="my-4">
                    <img src="<?= APP_URL ?>/<?= $article['image_path'] ?>" class="img-fluid rounded-3 w-100 shadow-sm" alt="Featured Image">
                </div>
                <?php endif; ?>

                <!-- Summary Lead Box -->
                <?php 
                $summaryContent = !empty($article['summary_en']) ? $article['summary_en'] : $article['summary_si'];
                if (!empty($summaryContent)): 
                ?>
                <div class="article-lead-text">
                    <?= nl2br(htmlspecialchars($summaryContent)) ?>
                </div>
                <?php endif; ?>

                <!-- Main Body Content -->
                <div class="article-rich-body" id="articleBody">
                    <?= !empty($article['content_en']) ? $article['content_en'] : $article['content_si'] ?>
                </div>

                <!-- Tags / Keyword Footer -->
                <div class="mt-4 pt-3 border-top d-flex align-items-center gap-2 flex-wrap">
                    <span class="small fw-bold text-muted"><i class="fas fa-tags me-1 text-danger"></i> Tags:</span>
                    <a href="#" class="badge bg-light text-dark border text-decoration-none">#SriLanka</a>
                    <a href="#" class="badge bg-light text-dark border text-decoration-none">#News</a>
                    <a href="#" class="badge bg-light text-dark border text-decoration-none">#Breaking</a>
                    <a href="#" class="badge bg-light text-dark border text-decoration-none">#LankaNewsHub</a>
                </div>

            </div>

            <!-- Related Articles -->
            <?php if (!empty($related)): ?>
            <div class="mt-5">
                <div class="section-heading-bar">
                    <h3 class="section-heading-title">
                        <i class="fas fa-layer-group"></i> Related Stories
                    </h3>
                </div>
                <div class="row g-3">
                    <?php foreach ($related as $r): ?>
                    <div class="col-md-4">
                        <div class="news-card-premium">
                            <div class="news-card-thumb-wrap" style="aspect-ratio: 16/10;">
                                <a href="<?= APP_URL ?>/news/<?= $r['slug'] ?>">
                                    <img src="<?= !empty($r['image_path']) ? (APP_URL . '/' . $r['image_path']) : 'https://images.unsplash.com/photo-1504711434969-e33886168f5c?w=500&q=80' ?>" class="news-card-thumb" alt="News Image">
                                </a>
                            </div>
                            <div class="news-card-body p-3">
                                <a href="<?= APP_URL ?>/news/<?= $r['slug'] ?>">
                                    <h4 class="news-card-title" style="font-size: 0.95rem;"><?= htmlspecialchars(!empty($r['title_en']) ? $r['title_en'] : $r['title_si']) ?></h4>
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Sidebar Ad Space (300 x 600 Half-page Ad) -->
            <div class="mb-4 text-center sticky-top" style="top: 80px;">
                <?= \App\Helpers\AdHelper::render('sidebar_300x600') ?>
            </div>
        </div>
    </div>
</main>

<script>
    let currentFontSize = 1.08;
    function changeFontSize(delta) {
        const bodyEl = document.getElementById('articleBody');
        if (bodyEl) {
            currentFontSize += delta * 0.1;
            if (currentFontSize < 0.9) currentFontSize = 0.9;
            if (currentFontSize > 1.6) currentFontSize = 1.6;
            bodyEl.style.fontSize = currentFontSize + 'rem';
        }
    }

    function copyArticleUrl() {
        navigator.clipboard.writeText(window.location.href).then(() => {
            alert('Article URL successfully copied to clipboard!');
        });
    }
</script>

<?php include 'partials/footer.php'; ?>
