<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Lanka News Hub - Sri Lanka\'s Trusted News Network') ?></title>
    
    <!-- Meta & OpenGraph -->
    <meta name="description" content="<?= htmlspecialchars($meta_description ?? 'Lanka News Hub - Latest breaking news, politics, sports, business, world news, and technology from Sri Lanka.') ?>">
    <meta property="og:title" content="<?= htmlspecialchars($title ?? 'Lanka News Hub') ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= APP_URL ?>">
    
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Google Fonts: Plus Jakarta Sans & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Master Public CSS -->
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/style.css">
</head>
<body>

<!-- Site Top Bar -->
<div class="site-topbar d-none d-md-block">
    <div class="container d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-3">
            <span class="live-badge"><span class="live-dot"></span> LIVE UPDATES</span>
            <span class="text-white-50"><i class="far fa-calendar-alt me-1 text-danger"></i> <span id="currentDateStr"><?= date('l, F j, Y') ?></span></span>
            <span class="text-white-50 ms-2"><i class="far fa-clock me-1 text-danger"></i> <span id="liveTimeStr"><?= date('H:i:s') ?></span></span>
        </div>
        <div class="d-flex align-items-center gap-3">
            <div class="d-flex gap-2">
                <a href="#" class="text-white-50 hover-white px-1" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                <a href="#" class="text-white-50 hover-white px-1" title="Twitter / X"><i class="fab fa-x-twitter"></i></a>
                <a href="#" class="text-white-50 hover-white px-1" title="YouTube"><i class="fab fa-youtube"></i></a>
                <a href="#" class="text-white-50 hover-white px-1" title="Telegram"><i class="fab fa-telegram-plane"></i></a>
            </div>
            <div class="border-start border-secondary ps-3 ms-2">
                <a href="<?= APP_URL ?>/admin/login" class="text-white-50 text-decoration-none small hover-white">
                    <i class="fas fa-user-shield me-1 text-danger"></i> Newsroom Login
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Brand Masthead Area -->
<header class="brand-masthead">
    <div class="container d-flex flex-column flex-lg-row justify-content-between align-items-center gap-3">
        <div>
            <a href="<?= APP_URL ?>" class="brand-logo-text text-decoration-none">
                LANKA<span>NEWS HUB</span>
            </a>
            <div class="brand-tagline">Sri Lanka's Premier Digital News Network</div>
        </div>
        
        <!-- Header 728x90 Banner Ad -->
        <div class="d-none d-lg-block">
            <?= \App\Helpers\AdHelper::render('header_728x90') ?>
        </div>
    </div>
</header>

<!-- Main Navigation Bar -->
<nav class="navbar navbar-expand-lg navbar-dark main-navbar sticky-top">
    <div class="container">
        <a class="navbar-brand d-lg-none fw-bold text-white fs-6" href="<?= APP_URL ?>">
            <i class="fas fa-newspaper me-1"></i> LANKA NEWS
        </a>
        <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbarNav" aria-controls="mainNavbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="mainNavbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link <?= empty($current_category) ? 'active' : '' ?>" href="<?= APP_URL ?>">
                        <i class="fas fa-home"></i> Home
                    </a>
                </li>
                <?php if (!empty($categories)): ?>
                    <?php foreach ($categories as $cat): ?>
                        <li class="nav-item">
                            <a class="nav-link <?= (isset($current_category) && $current_category['id'] == $cat['id']) ? 'active' : '' ?>" href="<?= APP_URL ?>/category/<?= $cat['slug'] ?>">
                                <?= htmlspecialchars(!empty($cat['name_en']) ? $cat['name_en'] : $cat['name_si']) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
            
            <!-- Search Form -->
            <form action="<?= APP_URL ?>/search" method="GET" class="d-flex my-2 my-lg-0">
                <div class="input-group">
                    <input class="form-control nav-search-input py-2" type="search" name="q" placeholder="Search news..." aria-label="Search" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" required>
                    <button class="btn nav-search-btn px-3" type="submit"><i class="fas fa-search"></i></button>
                </div>
            </form>
        </div>
    </div>
</nav>

<!-- Breaking News Ticker Bar -->
<?php if (!empty($breaking)): ?>
<div class="breaking-ticker-wrap">
    <div class="container d-flex align-items-center overflow-hidden">
        <div class="breaking-badge-pill">
            <i class="fas fa-bolt me-1"></i> BREAKING NEWS
        </div>
        <div class="ticker-content flex-grow-1 overflow-hidden">
            <marquee behavior="scroll" direction="left" onmouseover="this.stop();" onmouseout="this.start();" scrollamount="6">
                <?php foreach ($breaking as $b): ?>
                    <span class="me-5">
                        <i class="fas fa-circle text-danger me-2" style="font-size: 0.5rem; vertical-align: middle;"></i>
                        <a href="<?= APP_URL ?>/news/<?= $b['slug'] ?>" class="text-decoration-none">
                            <?= htmlspecialchars(!empty($b['title_en']) ? $b['title_en'] : $b['title_si']) ?>
                        </a>
                    </span>
                <?php endforeach; ?>
            </marquee>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
    // Real-time Clock
    function updateClock() {
        const now = new Date();
        const timeEl = document.getElementById('liveTimeStr');
        if (timeEl) {
            timeEl.textContent = now.toLocaleTimeString();
        }
    }
    setInterval(updateClock, 1000);
</script>
