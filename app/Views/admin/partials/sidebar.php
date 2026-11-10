    <!-- Admin Sidebar -->
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="sidebar-brand">
            <div class="brand-badge-icon">
                <i class="fas fa-bolt"></i>
            </div>
            <div>
                <h1 class="brand-title">LANKA NEWS</h1>
                <div class="brand-subtitle">CMS NEWSROOM</div>
            </div>
        </div>

        <ul class="sidebar-menu">
            <li class="sidebar-heading">Core Editorial</li>
            <li class="sidebar-item">
                <a href="<?= APP_URL ?>/admin" class="sidebar-link <?= (($_SERVER['REQUEST_URI'] == '/admin' || str_ends_with($_SERVER['REQUEST_URI'], '/admin')) ? 'active' : '') ?>">
                    <i class="fas fa-chart-pie"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?= APP_URL ?>/admin/articles" class="sidebar-link <?= strpos($_SERVER['REQUEST_URI'], '/admin/articles') !== false ? 'active' : '' ?>">
                    <i class="fas fa-newspaper"></i>
                    <span>News Articles</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?= APP_URL ?>/admin/categories" class="sidebar-link <?= strpos($_SERVER['REQUEST_URI'], '/admin/categories') !== false ? 'active' : '' ?>">
                    <i class="fas fa-tags"></i>
                    <span>Categories</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?= APP_URL ?>/admin/media" class="sidebar-link <?= strpos($_SERVER['REQUEST_URI'], '/admin/media') !== false ? 'active' : '' ?>">
                    <i class="fas fa-photo-film"></i>
                    <span>Media Library</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?= APP_URL ?>/admin/advertisements" class="sidebar-link <?= strpos($_SERVER['REQUEST_URI'], '/admin/advertisements') !== false ? 'active' : '' ?>">
                    <i class="fas fa-rectangle-ad"></i>
                    <span>Advertisements</span>
                </a>
            </li>

            <li class="sidebar-heading mt-3">Quick Navigation</li>
            <li class="sidebar-item">
                <a href="<?= APP_URL ?>" target="_blank" class="sidebar-link text-white-50">
                    <i class="fas fa-arrow-up-right-from-square"></i>
                    <span>View Live Site</span>
                </a>
            </li>
        </ul>

        <div class="sidebar-footer">
            <a href="<?= APP_URL ?>/admin/logout" class="btn btn-outline-danger btn-sm w-100 fw-bold d-flex align-items-center justify-content-center gap-2">
                <i class="fas fa-arrow-right-from-bracket"></i>
                <span>Sign Out</span>
            </a>
        </div>
    </aside>

    <!-- Admin Main Canvas Area -->
    <div class="admin-main">
        <!-- Topbar -->
        <header class="admin-topbar">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-sm btn-light border d-lg-none" id="sidebarToggleBtn" type="button">
                    <i class="fas fa-bars"></i>
                </button>
                <h2 class="topbar-title"><?= htmlspecialchars($title ?? 'Dashboard') ?></h2>
            </div>

            <div class="topbar-actions">
                <div class="user-profile-badge">
                    <div class="user-avatar-circle">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <span class="d-none d-sm-inline"><?= htmlspecialchars($_SESSION['user_name'] ?? 'Editor in Chief') ?></span>
                </div>
            </div>
        </header>

        <!-- Dynamic Body Container -->
        <div class="admin-content-body">
