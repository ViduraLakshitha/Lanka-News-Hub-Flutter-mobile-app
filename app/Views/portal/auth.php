<?php require __DIR__.'/header.php'; ?>
<link rel="stylesheet" href="<?= e(url('assets/css/auth-modern.css')) ?>?v=<?= time() ?>">

<main id="main" class="container auth-page-space">
  <div class="modern-auth-container">
    <div class="modern-auth-card">
      
      <!-- Left Hero & Editorial Showcase Pane -->
      <aside class="auth-hero-pane">
        <div class="auth-hero-content">
          <!-- Brand Identity -->
          <div class="auth-brand-badge">
            <div class="auth-brand-logo">
              <span class="logo-main">LN</span>
              <span class="logo-si">ලංකා</span>
            </div>
            <div class="auth-brand-text">
              <span class="auth-brand-title"><?= e(setting('site_name', 'Lanka News Hub')) ?></span>
              <span class="auth-brand-tagline">National Digital Network</span>
            </div>
          </div>

          <!-- Hero Headline -->
          <div class="auth-hero-eyebrow">
            <span class="pulse-dot"></span>
            <?= $mode === 'register' ? 'Community Membership' : 'Editorial & Member Hub' ?>
          </div>
          
          <h1 class="auth-hero-title">
            <?= $mode === 'register' 
              ? 'Join Sri Lanka’s verified news and classifieds community.' 
              : 'Independent journalism. Real-time national perspective.' ?>
          </h1>
          
          <p class="auth-hero-description">
            <?= $mode === 'register'
              ? 'Create a reader account to submit marketplace classifieds, bookmark breaking stories, and engage with trusted local sellers.'
              : 'Sign in to access your newsroom editorial workspace, manage media assets, publish stories, or manage your community marketplace listings.' ?>
          </p>

          <!-- Core Feature Bullets -->
          <div class="auth-features-list">
            <div class="auth-feature-item">
              <span class="auth-feature-icon">📰</span>
              <div class="auth-feature-text">
                <strong>Newsroom Editorial Suite</strong>
                <span>Trilingual publishing in English, Sinhala & Tamil with media management</span>
              </div>
            </div>
            
            <div class="auth-feature-item">
              <span class="auth-feature-icon">💼</span>
              <div class="auth-feature-text">
                <strong>Classifieds Marketplace</strong>
                <span>Verified community advertisements with photo galleries & direct contact</span>
              </div>
            </div>

            <div class="auth-feature-item">
              <span class="auth-feature-icon">🛡️</span>
              <div class="auth-feature-text">
                <strong>Role-Governed Security</strong>
                <span>Hardened session tokens, CSRF protection, and audit activity logging</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Development / Admin Demo Credentials Quick Fill Assistant -->
        <div class="auth-demo-card">
          <div class="auth-demo-header">
            <span class="auth-demo-title">
              <span>⚡</span> Quick Test Credentials
            </span>
            <span class="auth-demo-badge">One-Click Fill</span>
          </div>

          <div class="auth-demo-chips">
            <button type="button" class="auth-demo-chip" onclick="fillCredentials('admin@example.test', 'MemberPassword123!')" title="Fill Newsroom Admin credentials">
              <span>👑</span> Newsroom Admin
            </button>
            <button type="button" class="auth-demo-chip" onclick="fillCredentials('editor@example.test', 'MemberPassword123!')" title="Fill Desk Editor credentials">
              <span>✍️</span> Desk Editor
            </button>
            <button type="button" class="auth-demo-chip" onclick="fillCredentials('preview@example.test', 'MemberPassword123!')" title="Fill Preview Editor credentials">
              <span>👁️</span> Preview Editor
            </button>
          </div>

          <div class="auth-demo-hint">
            <span>Default Password: <code>MemberPassword123!</code></span>
            <small style="color:#fca5a5;">Click above to auto-fill</small>
          </div>
        </div>

        <!-- Footer Trust Indicator -->
        <div class="auth-hero-footer">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:#10b981;">
            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
          </svg>
          <span>256-bit Encrypted Session · TLS 1.3 High-Security Gateway</span>
        </div>
      </aside>

      <!-- Right Form Pane -->
      <section class="auth-form-pane">
        
        <!-- Segmented Navigation Switcher -->
        <nav class="auth-nav-tabs" aria-label="Sign in or register">
          <a href="<?= e(url('account/login')) ?>" class="auth-tab-btn <?= $mode === 'login' ? 'active' : '' ?>">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
              <polyline points="10 17 15 12 10 7"></polyline>
              <line x1="15" y1="12" x2="3" y2="12"></line>
            </svg>
            Sign In
          </a>
          <a href="<?= e(url('account/register')) ?>" class="auth-tab-btn <?= $mode === 'register' ? 'active' : '' ?>">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
              <circle cx="8.5" cy="7" r="4"></circle>
              <line x1="20" y1="8" x2="20" y2="14"></line>
              <line x1="23" y1="11" x2="17" y2="11"></line>
            </svg>
            Create Account
          </a>
        </nav>

        <!-- Form Heading -->
        <header class="auth-form-header">
          <span class="auth-form-eyebrow">
            <?= $mode === 'register' ? 'COMMUNITY REGISTRATION' : 'MEMBER & NEWSROOM ACCESS' ?>
          </span>
          <h2 class="auth-form-title"><?= e($title) ?></h2>
          <p class="auth-form-subtitle">
            <?= $mode === 'register'
              ? 'Enter your name, email, and a secure password to get started.'
              : 'Enter your verified credentials to access your workspace or profile.' ?>
          </p>
        </header>

        <!-- Error Alert (If Any) -->
        <?php if (!empty($error)): ?>
          <div class="auth-alert-error" role="alert">
            <span class="alert-icon">⚠️</span>
            <div>
              <strong>Authentication notice:</strong>
              <div><?= e($error) ?></div>
              <?php if (str_contains(strtolower($error), 'email or password')): ?>
                <div style="font-size:11.5px; margin-top:4px; opacity:0.9;">
                  💡 <em>Hint: To sign in as an admin, use <code>admin@example.test</code> with password <code>MemberPassword123!</code> or click "Newsroom Admin" on the left.</em>
                </div>
              <?php endif; ?>
            </div>
          </div>
        <?php endif; ?>

        <!-- Authentication Form -->
        <form method="post" class="auth-fields-stack" id="authForm" novalidate>
          <?= csrf() ?>

          <?php if ($mode === 'register'): ?>
            <div class="auth-field-group">
              <label for="name" class="auth-field-label">Full Name</label>
              <div class="auth-input-wrapper">
                <span class="auth-input-icon">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                  </svg>
                </span>
                <input 
                  type="text" 
                  id="name" 
                  name="name" 
                  class="auth-input" 
                  autocomplete="name" 
                  maxlength="100" 
                  placeholder="e.g. Kasun Perera" 
                  required 
                  value="<?= e($_POST['name'] ?? '') ?>"
                  autofocus
                >
              </div>
            </div>
          <?php endif; ?>

          <div class="auth-field-group">
            <label for="email" class="auth-field-label">Email Address</label>
            <div class="auth-input-wrapper">
              <span class="auth-input-icon">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                  <polyline points="22,6 12,13 2,6"></polyline>
                </svg>
              </span>
              <input 
                type="email" 
                id="email" 
                name="email" 
                class="auth-input" 
                autocomplete="email" 
                maxlength="255" 
                placeholder="name@example.com or admin@example.test" 
                required 
                value="<?= e($_POST['email'] ?? '') ?>"
                <?= $mode === 'login' ? 'autofocus' : '' ?>
              >
            </div>
          </div>

          <div class="auth-field-group">
            <div class="auth-field-label">
              <label for="password">Password</label>
              <?php if ($mode === 'register'): ?>
                <span id="charCountLabel" style="font-size:11px; font-weight:600; color:#94a3b8;">12–72 characters</span>
              <?php endif; ?>
            </div>
            
            <div class="auth-input-wrapper">
              <span class="auth-input-icon">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                  <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                </svg>
              </span>
              <input 
                type="password" 
                id="password" 
                name="password" 
                class="auth-input" 
                autocomplete="<?= $mode === 'register' ? 'new-password' : 'current-password' ?>" 
                <?= $mode === 'register' ? 'minlength="12" maxlength="72"' : '' ?> 
                placeholder="••••••••••••" 
                required
              >
              <button 
                type="button" 
                class="auth-input-toggle" 
                id="togglePasswordBtn" 
                aria-label="Toggle password visibility" 
                title="Show / hide password"
              >
                <svg id="eyeIcon" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                  <circle cx="12" cy="12" r="3"></circle>
                </svg>
              </button>
            </div>

            <?php if ($mode === 'register'): ?>
              <!-- Password strength meter -->
              <div class="auth-password-strength" id="strengthMeter" aria-hidden="true">
                <span class="auth-strength-bar" id="bar1"></span>
                <span class="auth-strength-bar" id="bar2"></span>
                <span class="auth-strength-bar" id="bar3"></span>
                <span class="auth-strength-bar" id="bar4"></span>
              </div>
              <div class="auth-password-helper">
                <span id="strengthText" style="color:#64748b;">Must be 12 to 72 characters.</span>
              </div>
            <?php endif; ?>
          </div>

          <button type="submit" class="button auth-submit-btn" id="submitBtn">
            <span><?= $mode === 'register' ? 'Create account' : 'Sign in' ?></span>
            <span aria-hidden="true">→</span>
          </button>
        </form>

        <!-- Role Guidance Note -->
        <div class="auth-staff-note">
          <span style="font-size:16px;">ℹ️</span>
          <span>
            <?= $mode === 'login'
              ? '<strong>Newsroom access:</strong> Staff (Admin, Editor, Reporter) are automatically directed to the <strong>Editorial Newsroom (`/admin`)</strong> upon sign in.'
              : '<strong>Member accounts:</strong> New community accounts allow publishing and managing classified ads. Editorial staff are provisioned by administrators.' ?>
          </span>
        </div>

        <!-- Switch Mode Link -->
        <p class="auth-switch-prompt">
          <?= $mode === 'register' ? 'Already have an account?' : 'New to Lanka News Hub?' ?>
          <a class="auth-switch-link" href="<?= e(url($mode === 'register' ? 'account/login' : 'account/register')) ?>">
            <?= $mode === 'register' ? 'Sign in to your account' : 'Create a free account' ?>
          </a>
        </p>
      </section>

    </div>
  </div>
</main>

<script>
  // Show / Hide Password toggle
  (function() {
    const toggleBtn = document.getElementById('togglePasswordBtn');
    const passwordInput = document.getElementById('password');
    const eyeIcon = document.getElementById('eyeIcon');

    if (toggleBtn && passwordInput && eyeIcon) {
      toggleBtn.addEventListener('click', function() {
        const isPassword = passwordInput.getAttribute('type') === 'password';
        passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
        
        // Toggle SVG icon
        if (isPassword) {
          eyeIcon.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line>';
        } else {
          eyeIcon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle>';
        }
      });
    }

    // Password strength check on register
    <?php if ($mode === 'register'): ?>
      const pwd = document.getElementById('password');
      const b1 = document.getElementById('bar1');
      const b2 = document.getElementById('bar2');
      const b3 = document.getElementById('bar3');
      const b4 = document.getElementById('bar4');
      const sText = document.getElementById('strengthText');

      if (pwd) {
        pwd.addEventListener('input', function() {
          const val = pwd.value;
          const len = val.length;
          
          [b1, b2, b3, b4].forEach(b => b.style.background = '#e2e8f0');

          if (len === 0) {
            sText.textContent = 'Must be 12 to 72 characters.';
            sText.style.color = '#64748b';
            return;
          }

          if (len < 12) {
            b1.style.background = '#ef4444';
            sText.textContent = 'Too short (' + len + '/12 chars). Needs ' + (12 - len) + ' more characters.';
            sText.style.color = '#ef4444';
          } else {
            // Evaluates complexity
            let score = 2; // base for 12+ chars
            if (/[A-Z]/.test(val) && /[a-z]/.test(val)) score++;
            if (/[0-9]/.test(val) && /[^A-Za-z0-9]/.test(val)) score++;

            b1.style.background = '#10b981';
            b2.style.background = '#10b981';
            if (score >= 3) b3.style.background = '#10b981';
            if (score >= 4) b4.style.background = '#10b981';

            sText.textContent = score >= 4 ? 'Strong password! Valid for registration.' : 'Valid password length (12+ characters).';
            sText.style.color = '#059669';
          }
        });
      }
    <?php endif; ?>
  })();

  // Quick fill demo credentials
  function fillCredentials(email, password) {
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const submitBtn = document.getElementById('submitBtn');

    if (emailInput && passwordInput) {
      emailInput.value = email;
      passwordInput.value = password;
      
      // Trigger subtle pulse animation on inputs
      [emailInput, passwordInput].forEach(inp => {
        inp.style.transition = 'all 0.3s';
        inp.style.borderColor = '#c4161c';
        inp.style.backgroundColor = '#fff5f5';
        setTimeout(() => {
          inp.style.backgroundColor = '#ffffff';
        }, 600);
      });

      if (submitBtn) {
        submitBtn.focus();
      }
    }
  }
</script>

<?php require __DIR__.'/footer.php'; ?>
