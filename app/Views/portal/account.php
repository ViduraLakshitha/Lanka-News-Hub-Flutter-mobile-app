<?php
require __DIR__ . '/header.php';
$activeCount = count(array_filter($listings, fn($item) => $item['status'] === 'approved' && $item['expires_at'] > date('Y-m-d H:i:s')));
$userInitial = mb_strtoupper(mb_substr($user['name'], 0, 1));
$roleLabel = match ($user['role']) {
    'super_admin' => 'Super Administrator',
    'editor' => 'Editorial Lead',
    'reporter' => 'Staff Journalist',
    default => 'Community Member',
};
?>
<link rel="stylesheet" href="<?= e(url('assets/css/account-modern.css')) ?>?v=<?= time() ?>">

<main id="main" class="container account-page-wrapper">
  <!-- Breadcrumb Navigation -->
  <nav class="acc-breadcrumbs" aria-label="Breadcrumb">
    <a href="<?= e(url()) ?>">Home</a>
    <span class="sep">›</span>
    <span>Reader & seller central</span>
    <span class="sep">›</span>
    <span class="current">My account</span>
  </nav>

  <!-- Profile Hero Header -->
  <section class="acc-hero-card">
    <div class="acc-hero-profile">
      <div class="acc-avatar" aria-hidden="true"><?= e($userInitial) ?></div>
      <div class="acc-user-details">
        <div class="acc-eyebrow">
          <span>●</span> READER & SELLER CENTRAL
        </div>
        <div class="acc-user-name-row">
          <h1>Hello, <?= e($user['name']) ?><span class="red-dot">.</span></h1>
          <span class="acc-role-pill <?= e(str_replace('_', '-', $user['role'])) ?>">
            <?= e($roleLabel) ?>
          </span>
        </div>
        <div class="acc-user-meta">
          <span><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg> <?= e($user['email']) ?></span>
          <span><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg> Member since <?= e(date('M Y', strtotime($user['created_at'] ?? 'now'))) ?></span>
        </div>
      </div>
    </div>

    <!-- Quick Action Controls -->
    <div class="acc-hero-actions">
      <?php if ($user['role'] !== 'member'): ?>
        <a class="acc-btn acc-btn-dark" href="<?= e(url('admin')) ?>">
          Newsroom ↗
        </a>
      <?php endif; ?>
      <a class="acc-btn acc-btn-primary" href="<?= e(url('classifieds/create')) ?>">
        + Post a classified
      </a>
      <a class="acc-btn acc-btn-secondary" href="#profile-settings">
        Account settings
      </a>
      <form method="post" action="<?= e(url('account/logout')) ?>" style="margin:0;">
        <?= csrf() ?>
        <button class="acc-btn acc-btn-outline-danger" type="submit">Sign out</button>
      </form>
    </div>
  </section>

  <!-- Classifieds Metric Statistics Cards -->
  <div class="acc-stats-grid">
    <div class="acc-stat-card">
      <div class="acc-stat-icon green">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
      </div>
      <div class="acc-stat-content">
        <div class="acc-stat-number"><?= (int)$activeCount ?></div>
        <div class="acc-stat-label">Active classifieds</div>
        <div class="acc-stat-desc">Live and visible in marketplace</div>
      </div>
    </div>

    <div class="acc-stat-card">
      <div class="acc-stat-icon blue">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
      </div>
      <div class="acc-stat-content">
        <div class="acc-stat-number"><?= count($listings) ?></div>
        <div class="acc-stat-label">My advertisements</div>
        <div class="acc-stat-desc">Total submitted listings</div>
      </div>
    </div>

    <div class="acc-stat-card">
      <div class="acc-stat-icon amber">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"></path></svg>
      </div>
      <div class="acc-stat-content">
        <div class="acc-stat-number"><?= count($favorites) ?></div>
        <div class="acc-stat-label">Saved advertisements</div>
        <div class="acc-stat-desc">Bookmarked for later viewing</div>
      </div>
    </div>
  </div>

  <!-- Segmented Navigation Tabs -->
  <nav class="acc-nav-tabs" aria-label="Account sections">
    <a class="acc-tab-link active" href="#my-listings">
      <span>📋 My classified ads</span>
      <span class="acc-tab-count"><?= count($listings) ?></span>
    </a>
    <a class="acc-tab-link" href="#saved-listings">
      <span>★ Saved advertisements</span>
      <span class="acc-tab-count"><?= count($favorites) ?></span>
    </a>
    <a class="acc-tab-link" href="#profile-settings">
      <span>⚙ Profile settings</span>
    </a>
  </nav>

  <!-- Two-Column Editorial & Management Grid -->
  <div class="acc-layout-grid">
    <!-- Main Column -->
    <div class="acc-main-col">
      <!-- Section 1: My Classified Listings -->
      <section id="my-listings" class="acc-listings-section">
        <div class="acc-section-bar">
          <h2>
            <span>Classified management desk</span>
          </h2>
          <a class="acc-btn acc-btn-primary acc-btn-sm" href="<?= e(url('classifieds/create')) ?>">
            + Post a classified
          </a>
        </div>

        <?php if (!$listings): ?>
          <div class="acc-empty-card empty">
            <div class="acc-empty-icon" aria-hidden="true">
              <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
            </div>
            <h3>Your next opportunity starts here.</h3>
            <p>Post your first advertisement to reach your community.</p>
            <a class="acc-btn acc-btn-primary" href="<?= e(url('classifieds/create')) ?>">
              + Post a classified now
            </a>
          </div>
        <?php else: ?>
          <div class="acc-listings-stack account-listings">
            <?php foreach ($listings as $item): ?>
              <?php
              $isExpired = $item['status'] === 'approved' && $item['expires_at'] < date('Y-m-d H:i:s');
              $displayStatus = $isExpired ? 'expired' : $item['status'];
              ?>
              <article class="acc-listing-card panel">
                <div class="acc-listing-card-header section-heading">
                  <h3 class="acc-listing-title">
                    <a href="<?= e(url('classifieds/' . $item['id'])) ?>">
                      <?= e($item['title']) ?>
                    </a>
                  </h3>
                  <span class="status-badge status <?= e($displayStatus) ?>">
                    <?= e(ucfirst($displayStatus)) ?>
                  </span>
                </div>

                <div class="acc-listing-meta muted">
                  <span>📍 <?= e($item['location']) ?></span>
                  <?php if ($item['price'] !== null): ?>
                    <span class="price">LKR <?= number_format((float)$item['price'], 2) ?></span>
                  <?php endif; ?>
                  <?php if ($item['expires_at']): ?>
                    <span>Expires <?= e(date('d M Y', strtotime($item['expires_at']))) ?></span>
                  <?php endif; ?>
                  <?php if ($item['is_premium']): ?>
                    <span class="premium-tag">★ Premium</span>
                  <?php endif; ?>
                </div>

                <?php if ($item['moderation_note']): ?>
                  <div class="acc-moderation-callout notice">
                    <strong>Review note:</strong> <?= e($item['moderation_note']) ?>
                  </div>
                <?php endif; ?>

                <div class="acc-listing-actions inline-actions">
                  <a class="acc-btn acc-btn-secondary acc-btn-sm button secondary small" href="<?= e(url('classifieds/edit/' . $item['id'])) ?>">
                    Edit
                  </a>
                  <?php if ($item['status'] === 'approved' && $item['expires_at'] > date('Y-m-d H:i:s')): ?>
                    <a class="acc-btn acc-btn-secondary acc-btn-sm button secondary small" href="<?= e(url('classifieds/' . $item['id'])) ?>">
                      View
                    </a>
                  <?php endif; ?>
                  <form method="post" action="<?= e(url('classifieds/renew/' . $item['id'])) ?>">
                    <?= csrf() ?>
                    <button class="acc-btn acc-btn-secondary acc-btn-sm button secondary small" type="submit">Request renewal</button>
                  </form>
                  <form method="post" action="<?= e(url('classifieds/withdraw/' . $item['id'])) ?>" data-confirm="Withdraw this advertisement from the marketplace?">
                    <?= csrf() ?>
                    <button class="acc-btn acc-btn-outline-danger acc-btn-sm button danger small" type="submit">Withdraw</button>
                  </form>
                  <form method="post" action="<?= e(url('classifieds/delete/' . $item['id'])) ?>" data-confirm="Permanently delete this advertisement? This cannot be undone.">
                    <?= csrf() ?>
                    <button class="link-button" type="submit" style="color:#b91c1c; font-size:12px; margin-left:6px; font-weight:600;">Delete</button>
                  </form>
                </div>
              </article>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </section>

      <!-- Section 2: Saved Advertisements -->
      <section id="saved-listings" class="acc-saved-section">
        <div class="acc-section-bar">
          <h2>
            <span>Saved advertisements</span>
          </h2>
          <a class="acc-link-action" href="<?= e(url('classifieds')) ?>">
            Browse listings →
          </a>
        </div>

        <?php if (!$favorites): ?>
          <div class="acc-empty-card empty">
            <div class="acc-empty-icon" aria-hidden="true" style="background:#fef3c7; color:#b45309;">
              ★
            </div>
            <h3>Your saved, active advertisements will appear here.</h3>
            <p class="muted">Bookmarked a property, car, or job listing? It will be safely saved in this central space.</p>
            <a class="acc-btn acc-btn-secondary" href="<?= e(url('classifieds')) ?>">
              Browse marketplace listings →
            </a>
          </div>
        <?php else: ?>
          <div class="acc-saved-grid classified-grid">
            <?php foreach ($favorites as $item) require __DIR__ . '/classified-card.php'; ?>
          </div>
        <?php endif; ?>
      </section>

      <!-- Section 3: Profile & Security Settings -->
      <section id="profile-settings" class="panel profile-panel" style="background:none; border:none; padding:0; box-shadow:none;">
        <div class="acc-section-bar">
          <h2>
            <span>Profile & Security Settings</span>
          </h2>
          <span class="muted" style="font-size:13px;">Manage credentials</span>
        </div>

        <?php if ($error): ?>
          <div class="acc-error-alert error" role="alert">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
            <div><?= e($error) ?></div>
          </div>
        <?php endif; ?>

        <div class="acc-settings-stack">
          <!-- Sub-Card A: Personal Profile Information -->
          <div class="acc-card">
            <div class="acc-card-header">
              <div class="acc-card-icon crimson" aria-hidden="true">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
              </div>
              <div class="acc-card-header-text">
                <h3>Personal Profile Information</h3>
                <p>Update your public display name. Your email address serves as your primary sign-in ID.</p>
              </div>
            </div>

            <form method="post" action="<?= e(url('account')) ?>" class="form-stack">
              <?= csrf() ?>
              <input type="hidden" name="form_type" value="profile">
              <div class="acc-field">
                <div class="acc-field-label-row">
                  <label for="acc-name" class="acc-field-label">Full Name</label>
                </div>
                <div class="acc-input-with-icon">
                  <span class="acc-field-icon" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                  </span>
                  <input id="acc-name" name="name" required maxlength="100" value="<?= e($user['name']) ?>" placeholder="e.g. Kasun Perera">
                </div>
              </div>

              <div class="acc-field">
                <div class="acc-field-label-row">
                  <label class="acc-field-label">Registered Email Address</label>
                  <span class="acc-badge-info verified">Primary Login</span>
                </div>
                <div class="acc-input-with-icon">
                  <span class="acc-field-icon" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                  </span>
                  <input type="email" value="<?= e($user['email']) ?>" readonly class="acc-input-readonly" aria-readonly="true" style="background:#f8fafc; cursor:not-allowed;">
                </div>
                <div class="acc-field-hint">Email is your primary login identifier. Contact editorial administration to request an email update.</div>
              </div>

              <div class="acc-form-footer">
                <button class="acc-btn acc-btn-primary button" type="submit">
                  Save Profile Changes
                </button>
              </div>
            </form>
          </div>

          <!-- Sub-Card B: Security & Password Reset -->
          <div class="acc-card">
            <div class="acc-card-header">
              <div class="acc-card-icon blue" aria-hidden="true">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
              </div>
              <div class="acc-card-header-text">
                <h3>Change Password</h3>
                <p>Ensure your account is protected with a secure password of at least 12 characters.</p>
              </div>
            </div>

            <form method="post" action="<?= e(url('account')) ?>" id="form-change-password" class="form-stack">
              <?= csrf() ?>
              <input type="hidden" name="form_type" value="password">
              <input type="hidden" name="name" value="<?= e($user['name']) ?>">

              <!-- Current Password Field -->
              <div class="acc-field">
                <div class="acc-field-label-row">
                  <label for="acc-current-pw" class="acc-field-label">Current Password</label>
                  <span class="acc-badge-info">Identity Verification</span>
                </div>
                <div class="acc-input-with-icon">
                  <span class="acc-field-icon" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                  </span>
                  <input id="acc-current-pw" type="password" name="current_password" required autocomplete="current-password" placeholder="Enter your current password">
                  <button type="button" class="acc-toggle-pw" data-target="acc-current-pw" title="Show or hide password" aria-label="Toggle password visibility">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="eye-open"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="eye-closed" style="display:none;"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
                  </button>
                </div>
                <div class="acc-field-hint">Required to confirm ownership before updating your credentials.</div>
              </div>

              <!-- New Password & Confirmation Grid -->
              <div class="acc-grid-2col form-grid">
                <!-- New Password -->
                <div class="acc-field">
                  <div class="acc-field-label-row">
                    <label for="acc-new-pw" class="acc-field-label">New Password</label>
                    <span class="acc-badge-info" id="pw-chars-count">0 / 12 min</span>
                  </div>
                  <div class="acc-input-with-icon">
                    <span class="acc-field-icon" aria-hidden="true">
                      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 2l-2 2m-1.5 1.5L14 9l-3 3-2-2-4 4 2 2-4 4 2 2 4-4 2 2 4-4-2-2 3.5-3.5 1.5-1.5 2-2z"></path></svg>
                    </span>
                    <input id="acc-new-pw" type="password" name="password" required minlength="12" maxlength="72" autocomplete="new-password" placeholder="New password (12–72 chars)">
                    <button type="button" class="acc-toggle-pw" data-target="acc-new-pw" title="Show or hide password" aria-label="Toggle password visibility">
                      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="eye-open"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="eye-closed" style="display:none;"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
                    </button>
                  </div>
                </div>

                <!-- Confirm New Password -->
                <div class="acc-field">
                  <div class="acc-field-label-row">
                    <label for="acc-confirm-pw" class="acc-field-label">Confirm New Password</label>
                    <span class="acc-pw-match-indicator muted" id="pw-match-badge">Must match</span>
                  </div>
                  <div class="acc-input-with-icon">
                    <span class="acc-field-icon" aria-hidden="true">
                      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    </span>
                    <input id="acc-confirm-pw" type="password" name="password_confirmation" required minlength="12" maxlength="72" autocomplete="new-password" placeholder="Re-enter new password">
                    <button type="button" class="acc-toggle-pw" data-target="acc-confirm-pw" title="Show or hide password" aria-label="Toggle password visibility">
                      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="eye-open"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="eye-closed" style="display:none;"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
                    </button>
                  </div>
                </div>
              </div>

              <!-- Password Guidelines Checklist Box -->
              <div class="acc-guidelines-box">
                <div class="acc-guidelines-title">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                  Password Security Guidelines
                </div>
                <ul class="acc-guidelines-list">
                  <li><span class="dot">•</span> Minimum 12 characters and maximum 72 characters</li>
                  <li><span class="dot">•</span> Verification of your current password is required for security</li>
                  <li><span class="dot">•</span> Both the new password and confirmation field must match exactly</li>
                </ul>
              </div>

              <div class="acc-form-footer">
                <button class="acc-btn acc-btn-dark" type="submit" id="acc-btn-update-pw">
                  Update Password
                </button>
              </div>
            </form>
          </div>
        </div>
      </section>
    </div>

    <!-- Sidebar Rail Column -->
    <aside class="acc-side-col editorial-rail">
      <?php if ($user['role'] !== 'member'): ?>
        <section class="acc-rail-card primary rail-panel">
          <div class="acc-rail-title rail-heading">
            <span>📢</span>
            <h2>Editorial desk</h2>
          </div>
          <p style="font-size:13px; color:var(--acc-slate-600); margin:0 0 16px; line-height:1.5;">
            Manage and publish news stories, multimedia journalism, and editorial operations.
          </p>
          <a class="acc-btn acc-btn-dark full" style="width:100%;" href="<?= e(url('admin')) ?>">
            Editorial dashboard ↗
          </a>
        </section>
      <?php endif; ?>

      <!-- Publisher Desk Card -->
      <section class="acc-rail-card primary rail-panel">
        <div class="acc-rail-title rail-heading">
          <span>📢</span>
          <h2>Publisher desk</h2>
        </div>
        <p style="font-size:13px; color:var(--acc-slate-600); margin:0 0 16px; line-height:1.5;">
          Reach targeted readers across Sri Lanka with certified classified listings.
        </p>
        <a class="acc-btn acc-btn-primary full" style="width:100%;" href="<?= e(url('classifieds/create')) ?>">
          + Post a classified
        </a>
        <div class="acc-rail-menu desk-directory">
          <a class="acc-rail-link" href="<?= e(url('classifieds')) ?>">
            <span>Browse the marketplace</span>
            <span class="arrow">→</span>
          </a>
          <a class="acc-rail-link" href="<?= e(url('edition/latest')) ?>">
            <span>Read the latest news</span>
            <span class="arrow">→</span>
          </a>
          <?php if ($user['role'] !== 'member'): ?>
            <a class="acc-rail-link" href="<?= e(url('admin')) ?>">
              <span>Editorial dashboard</span>
              <span class="arrow">→</span>
            </a>
          <?php endif; ?>
        </div>
      </section>

      <!-- Seller Guidelines Card -->
      <section class="acc-rail-card">
        <div class="acc-rail-title">
          <span>💡</span>
          <h3>Quick selling tips</h3>
        </div>
        <ul class="acc-tips-list">
          <li>
            <span class="tip-icon">📸</span>
            <span>Include clear, well-lit photos for 3x higher inquiries.</span>
          </li>
          <li>
            <span class="tip-icon">🏷️</span>
            <span>Specify fair, transparent market pricing in LKR.</span>
          </li>
          <li>
            <span class="tip-icon">📍</span>
            <span>Provide accurate city or district details for local buyers.</span>
          </li>
          <li>
            <span class="tip-icon">⚡</span>
            <span>New submissions are reviewed by editorial within 24 hours.</span>
          </li>
        </ul>
      </section>

      <!-- Programmatic Ad Placement -->
      <div class="acc-ad-box">
        <?= \App\Helpers\AdHelper::render('sidebar_300x250') ?>
      </div>

      <!-- Community Panel -->
      <?php require __DIR__ . '/community-panel.php'; ?>

      <!-- Help & Hotline Support Card -->
      <section class="acc-help-card">
        <h3>Need Assistance?</h3>
        <p>Our newsroom support desk is available to assist with inquiries, account management, or verification.</p>
        <a href="tel:<?= e(preg_replace('/[^0-9+]/', '', setting('contact_phone', '0115300800'))) ?>" class="acc-help-btn">
          ☎ Call <?= e(setting('contact_phone', '011 5 300 800')) ?>
        </a>
      </section>
    </aside>
  </div>
</main>

<script>
// Smooth tab navigation highlight & password interactions
document.addEventListener('DOMContentLoaded', function() {
  const tabs = document.querySelectorAll('.acc-tab-link');
  tabs.forEach(tab => {
    tab.addEventListener('click', function() {
      tabs.forEach(t => t.classList.remove('active'));
      this.classList.add('active');
    });
  });

  // Password visibility toggle (Show/Hide)
  document.querySelectorAll('.acc-toggle-pw').forEach(function(button) {
    button.addEventListener('click', function(e) {
      e.preventDefault();
      const targetId = this.getAttribute('data-target');
      const input = document.getElementById(targetId);
      if (!input) return;

      const eyeOpen = this.querySelector('.eye-open');
      const eyeClosed = this.querySelector('.eye-closed');

      if (input.type === 'password') {
        input.type = 'text';
        if (eyeOpen) eyeOpen.style.display = 'none';
        if (eyeClosed) eyeClosed.style.display = 'inline-block';
        this.setAttribute('aria-label', 'Hide password');
      } else {
        input.type = 'password';
        if (eyeOpen) eyeOpen.style.display = 'inline-block';
        if (eyeClosed) eyeClosed.style.display = 'none';
        this.setAttribute('aria-label', 'Show password');
      }
    });
  });

  // Live password validation and matching indicator
  const newPwInput = document.getElementById('acc-new-pw');
  const confirmPwInput = document.getElementById('acc-confirm-pw');
  const charsBadge = document.getElementById('pw-chars-count');
  const matchBadge = document.getElementById('pw-match-badge');

  function validatePasswords() {
    if (!newPwInput || !confirmPwInput) return;
    const pw = newPwInput.value;
    const confirm = confirmPwInput.value;

    // Length badge
    if (charsBadge) {
      const len = pw.length;
      if (len === 0) {
        charsBadge.textContent = '0 / 12 min';
        charsBadge.style.color = '';
        charsBadge.style.backgroundColor = '';
      } else if (len >= 12 && len <= 72) {
        charsBadge.textContent = len + ' chars (Good)';
        charsBadge.style.color = '#15803d';
        charsBadge.style.backgroundColor = '#dcfce7';
      } else {
        charsBadge.textContent = len + ' / 12 min';
        charsBadge.style.color = '#b91c1c';
        charsBadge.style.backgroundColor = '#fee2e2';
      }
    }

    // Match badge & input highlights
    if (matchBadge) {
      if (!confirm) {
        matchBadge.textContent = 'Must match';
        matchBadge.className = 'acc-pw-match-indicator muted';
        confirmPwInput.classList.remove('match-success', 'match-error');
      } else if (pw === confirm) {
        matchBadge.textContent = '✓ Passwords match';
        matchBadge.className = 'acc-pw-match-indicator success';
        confirmPwInput.classList.remove('match-error');
        confirmPwInput.classList.add('match-success');
      } else {
        matchBadge.textContent = '✕ Passwords do not match';
        matchBadge.className = 'acc-pw-match-indicator error';
        confirmPwInput.classList.remove('match-success');
        confirmPwInput.classList.add('match-error');
      }
    }
  }

  if (newPwInput && confirmPwInput) {
    newPwInput.addEventListener('input', validatePasswords);
    confirmPwInput.addEventListener('input', validatePasswords);
  }

  // Pre-submit validation guard
  const pwForm = document.getElementById('form-change-password');
  if (pwForm) {
    pwForm.addEventListener('submit', function(e) {
      const currentPw = document.getElementById('acc-current-pw');
      if (currentPw && !currentPw.value) {
        e.preventDefault();
        alert('Please enter your current password to verify your identity.');
        currentPw.focus();
        return false;
      }
      const pw = newPwInput ? newPwInput.value : '';
      const confirm = confirmPwInput ? confirmPwInput.value : '';

      if (pw.length < 12 || pw.length > 72) {
        e.preventDefault();
        alert('New password must contain between 12 and 72 characters.');
        if (newPwInput) newPwInput.focus();
        return false;
      }
      if (pw !== confirm) {
        e.preventDefault();
        alert('New password and confirm password do not match.');
        if (confirmPwInput) confirmPwInput.focus();
        return false;
      }
    });
  }
});
</script>

<?php require __DIR__ . '/footer.php'; ?>
