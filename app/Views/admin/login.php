<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Newsroom Sign In - Lanka News Hub</title>
    
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .login-card {
            max-width: 440px;
            width: 100%;
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35);
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .login-header {
            background: #ffffff;
            padding: 35px 35px 15px 35px;
            text-align: center;
        }
        .login-badge {
            width: 54px;
            height: 54px;
            background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);
            color: #ffffff;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.6rem;
            margin-bottom: 16px;
            box-shadow: 0 8px 16px rgba(220, 38, 38, 0.3);
        }
        .brand-name {
            font-size: 1.5rem;
            font-weight: 900;
            letter-spacing: -0.5px;
            color: #0f172a;
            margin-bottom: 4px;
        }
        .brand-sub {
            font-size: 0.82rem;
            color: #64748b;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="login-header">
        <div class="login-badge">
            <i class="fas fa-bolt"></i>
        </div>
        <h1 class="brand-name">LANKA NEWS HUB</h1>
        <div class="brand-sub">Editorial & Newsroom Access</div>
    </div>

    <div class="p-4 pt-2">
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger d-flex align-items-center gap-2 mb-4" role="alert">
                <i class="fas fa-circle-exclamation flex-shrink-0"></i>
                <div class="small fw-semibold"><?= $_SESSION['error'] ?></div>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <form action="<?= e(url('admin/login')) ?>" method="POST">
            <?= csrf() ?>
            <div class="mb-3">
                <label for="email" class="form-label small fw-bold text-secondary">Work Email Address</label>
                <div class="input-group">
                    <span class="input-group-text bg-light text-muted border-end-0"><i class="far fa-envelope"></i></span>
                    <input type="email" class="form-control border-start-0 py-2" id="email" name="email" placeholder="admin@example.test" value="admin@example.test" required autofocus>
                </div>
            </div>

            <div class="mb-4">
                <label for="password" class="form-label small fw-bold text-secondary">Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-light text-muted border-end-0"><i class="fas fa-lock"></i></span>
                    <input type="password" class="form-control border-start-0 py-2" id="password" name="password" placeholder="••••••••" value="MemberPassword123!" required>
                </div>
            </div>

            <div class="d-grid mb-3">
                <button type="submit" class="btn btn-danger btn-lg py-2 fw-bold shadow-sm" style="background:#dc2626;">
                    <i class="fas fa-arrow-right-to-bracket me-2"></i> Sign In to Newsroom
                </button>
            </div>

            <div class="text-center mt-4 pt-3 border-top">
                <a href="<?= APP_URL ?>" class="text-muted small text-decoration-none">
                    <i class="fas fa-arrow-left me-1"></i> Return to Public News Portal
                </a>
            </div>
        </form>
    </div>
</div>

</body>
</html>
