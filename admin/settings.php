<?php
ob_start();
require_once '../config.php';
requireAdmin();

$db      = db();
$errors  = [];
$section = $_GET['section'] ?? 'general';

// ── POST handlers ────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'change_password') {
        $newPwd     = $_POST['new_password']     ?? '';
        $confirmPwd = $_POST['confirm_password'] ?? '';

        if (strlen($newPwd) < 8) {
            $errors[] = 'New password must be at least 8 characters.';
        } elseif ($newPwd !== $confirmPwd) {
            $errors[] = 'Passwords do not match.';
        } else {
            $hash = password_hash($newPwd, PASSWORD_BCRYPT);
            $db->prepare("UPDATE users SET password_hash = ? WHERE id = ?")
               ->execute([$hash, $_SESSION['user_id']]);
            flashMessage('success', 'Password changed successfully!');
            ob_end_clean();
            header('Location: settings.php?section=account');
            exit;
        }
        $section = 'account';

    } elseif ($action === 'update_profile') {
        $fullName = sanitizeInput($_POST['full_name'] ?? '');
        $email    = sanitizeInput($_POST['email']     ?? '');

        if (!$fullName || !$email) {
            $errors[] = 'Name and email are required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email address.';
        } else {
            $check = $db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $check->execute([$email, $_SESSION['user_id']]);
            if ($check->fetch()) {
                $errors[] = 'That email is already in use by another account.';
            } else {
                $db->prepare("UPDATE users SET full_name = ?, email = ? WHERE id = ?")
                   ->execute([$fullName, $email, $_SESSION['user_id']]);
                $_SESSION['user_name']  = $fullName;
                $_SESSION['user_email'] = $email;
                flashMessage('success', 'Profile updated successfully!');
                ob_end_clean();
                header('Location: settings.php?section=account');
                exit;
            }
        }
        $section = 'account';
    }
}

$flash = getFlashMessage();

$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$currentUser = $stmt->fetch();

// ── System stats ─────────────────────────────────────────────
$phpVersion    = PHP_VERSION;
$mysqlVersion  = $db->query("SELECT VERSION()")->fetchColumn();
$totalArticles = $db->query("SELECT COUNT(*) FROM articles")->fetchColumn();
$totalUsers    = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalTxns     = $db->query("SELECT COUNT(*) FROM transactions WHERE status='succeeded'")->fetchColumn();
$pubArticles   = $db->query("SELECT COUNT(*) FROM articles WHERE is_published=1")->fetchColumn();
$premArticles  = $db->query("SELECT COUNT(*) FROM articles WHERE is_premium=1")->fetchColumn();
$activeSubs    = $db->query("SELECT COUNT(*) FROM subscriptions WHERE status='active' AND plan_type IN('monthly','yearly')")->fetchColumn();
$totalRev      = $db->query("SELECT SUM(amount) FROM transactions WHERE status='succeeded'")->fetchColumn() ?? 0;

$stripeKey  = defined('STRIPE_SECRET_KEY') ? STRIPE_SECRET_KEY : '';
$stripeMode = str_starts_with($stripeKey, 'sk_live_') ? 'live'
            : (str_starts_with($stripeKey, 'sk_test_') ? 'test' : 'unset');

ob_end_clean();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings — <?php echo htmlspecialchars(SITE_NAME); ?></title>
    <link rel="icon" type="image/x-icon" href="https://raw.githubusercontent.com/Vigneshgbe/Subscription_Based_Blog/refs/heads/main/assets/Logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --ink:       #0d0d1a;
            --ink-2:     #3d3d55;
            --ink-3:     #8888a0;
            --surface:   #ffffff;
            --surface-2: #f7f7fb;
            --surface-3: #f0f0f8;
            --border:    #e8e8f0;
            --accent:    #5b4cf5;
            --accent-2:  #7c6ff7;
            --accent-bg: #eef2ff;
            --green:     #16a34a;
            --green-bg:  #f0fdf4;
            --red:       #dc2626;
            --red-bg:    #fef2f2;
            --gold:      #f59e0b;
            --shadow:    0 2px 12px rgba(13,13,26,.07);
            --shadow-lg: 0 8px 32px rgba(13,13,26,.12);
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--surface-2);
            color: var(--ink);
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
        }

        /* ── Admin layout ── */
        .admin-layout { display: flex; min-height: 100vh; }
        .main-content { flex: 1; margin-left: 280px; padding: 32px; min-width: 0; }

        /* ── Top bar ── */
        .top-bar {
            background: var(--surface);
            padding: 22px 32px;
            border-radius: 16px;
            margin-bottom: 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
        }
        .top-bar-title {
            display: flex; align-items: center; gap: 12px;
        }
        .top-bar-icon {
            width: 42px; height: 42px; border-radius: 12px;
            background: linear-gradient(135deg, var(--accent), var(--accent-2));
            display: flex; align-items: center; justify-content: center;
            font-size: 20px; flex-shrink: 0;
        }
        .top-bar h1 {
            font-family: 'Playfair Display', serif;
            font-size: 22px; font-weight: 900; color: var(--ink);
        }
        .top-bar-sub { font-size: 14px; color: var(--ink-3); margin-top: 1px; font-family: 'Plus Jakarta Sans', sans-serif; }

        /* ── Settings layout ── */
        .settings-layout {
            display: grid;
            grid-template-columns: 200px 1fr;
            gap: 24px;
            align-items: start;
        }

        /* ── Nav ── */
        .settings-nav {
            background: var(--surface);
            border-radius: 16px;
            padding: 12px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
            position: sticky;
            top: 24px;
        }
        .nav-label {
            font-size: 10px; font-weight: 700; text-transform: uppercase;
            letter-spacing: 1.5px; color: var(--ink-3);
            padding: 8px 12px 4px; display: block;
        }
        .settings-nav a {
            display: flex; align-items: center; gap: 10px;
            padding: 11px 14px; border-radius: 10px;
            text-decoration: none; font-weight: 600; font-size: 14px;
            color: var(--ink-2); transition: all .18s; margin-bottom: 2px;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        .settings-nav a .nav-icon { font-size: 16px; flex-shrink: 0; }
        .settings-nav a:hover { background: var(--surface-3); color: var(--ink); }
        .settings-nav a.active {
            background: linear-gradient(135deg, var(--accent), var(--accent-2));
            color: white;
            box-shadow: 0 4px 12px rgba(91,76,245,.3);
        }

        /* ── Cards ── */
        .card {
            background: var(--surface);
            border-radius: 16px;
            padding: 28px 32px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
            margin-bottom: 24px;
        }
        .card:last-child { margin-bottom: 0; }

        .card-header {
            display: flex; align-items: center; gap: 12px;
            margin-bottom: 24px; padding-bottom: 18px;
            border-bottom: 2px solid var(--surface-3);
        }
        .card-header-icon {
            width: 36px; height: 36px; border-radius: 10px;
            background: var(--accent-bg);
            display: flex; align-items: center; justify-content: center;
            font-size: 17px; flex-shrink: 0;
        }
        .card-header h3 {
            font-family: 'Playfair Display', serif;
            font-size: 18px; font-weight: 700; color: var(--ink);
        }
        .card-header p { font-size: 14px; color: var(--ink-3); margin-top: 2px; font-family: 'Plus Jakarta Sans', sans-serif; }

        /* ── Alerts ── */
        .alert {
            display: flex; align-items: flex-start; gap: 12px;
            padding: 14px 18px; border-radius: 12px; margin-bottom: 24px;
            font-size: 15px; font-weight: 500;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        .alert-icon { font-size: 18px; flex-shrink: 0; margin-top: 1px; }
        .alert-success { background: var(--green-bg); border: 1px solid #bbf7d0; color: #065f46; }
        .alert-danger  { background: var(--red-bg);   border: 1px solid #fecaca; color: #991b1b; }
        .alert ul { margin: 6px 0 0 16px; }
        .alert li { margin-bottom: 4px; }

        /* ── Info grid ── */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 14px;
        }
        .info-item {
            background: var(--surface-2);
            border: 1px solid var(--border);
            padding: 16px 18px;
            border-radius: 12px;
            transition: border-color .2s;
        }
        .info-item:hover { border-color: var(--accent); }
        .info-item strong {
            display: block; font-size: 11px; font-weight: 700;
            text-transform: uppercase; letter-spacing: 0.8px;
            color: var(--ink-3); margin-bottom: 8px;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        .info-item span {
            font-size: 17px; font-weight: 700; color: var(--ink);
            font-family: 'Plus Jakarta Sans', sans-serif;
            letter-spacing: -0.3px;
        }
        .info-item .info-sub { font-size: 12px; color: var(--ink-3); font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 500; display: block; margin-top: 2px; }

        /* ── Mode badge ── */
        .mode-badge {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 5px 14px; border-radius: 100px; font-size: 13px; font-weight: 700;
            font-family: 'Plus Jakarta Sans', sans-serif;
            white-space: nowrap;
        }
        .mode-badge.live    { background: #dcfce7; color: #15803d; }
        .mode-badge.test    { background: #fef9c3; color: #a16207; }
        .mode-badge.unset   { background: var(--surface-3); color: var(--ink-3); }
        .mode-badge::before { content: ''; width: 7px; height: 7px; border-radius: 50%; background: currentColor; }

        /* ── Forms ── */
        .form-group { margin-bottom: 20px; }
        .form-group:last-of-type { margin-bottom: 0; }

        label {
            display: block; font-size: 13px; font-weight: 700;
            text-transform: uppercase; letter-spacing: .6px;
            color: var(--ink-2); margin-bottom: 7px;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        input[type=text],
        input[type=email],
        input[type=password] {
            width: 100%; padding: 12px 16px;
            border: 2px solid var(--border); border-radius: 10px;
            font-size: 15px; font-family: 'Plus Jakarta Sans', sans-serif;
            color: var(--ink); background: var(--surface);
            transition: border-color .2s, box-shadow .2s;
        }
        input:focus {
            outline: none; border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(91,76,245,.12);
        }
        input:disabled {
            background: var(--surface-3); color: var(--ink-3); cursor: not-allowed;
        }
        .input-hint { font-size: 12px; color: var(--ink-3); margin-top: 5px; }

        /* Password strength bar */
        .pwd-strength { margin-top: 8px; }
        .pwd-bar { height: 4px; background: var(--border); border-radius: 2px; overflow: hidden; }
        .pwd-fill { height: 100%; width: 0; transition: width .3s, background .3s; border-radius: 2px; }
        .pwd-label { font-size: 11px; color: var(--ink-3); margin-top: 4px; }

        /* ── Buttons ── */
        .btn-row { display: flex; gap: 12px; margin-top: 24px; flex-wrap: wrap; }

        .btn {
            padding: 11px 24px; border: none; border-radius: 10px;
            font-size: 14px; font-weight: 700; font-family: 'Plus Jakarta Sans', sans-serif;
            cursor: pointer; transition: all .2s;
            display: inline-flex; align-items: center; gap: 8px;
            text-decoration: none;
        }
        .btn-primary {
            background: linear-gradient(135deg, var(--accent), var(--accent-2));
            color: white; box-shadow: 0 4px 14px rgba(91,76,245,.3);
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(91,76,245,.4);
        }
        .btn-ghost {
            background: var(--surface-3); color: var(--ink-2);
            border: 2px solid var(--border);
        }
        .btn-ghost:hover { border-color: var(--accent); color: var(--accent); }

        /* ── Stripe webhook display ── */
        .webhook-url {
            background: var(--ink); color: #a8ff78;
            font-family: 'JetBrains Mono', monospace;
            font-size: 13px; padding: 16px 20px;
            border-radius: 10px; word-break: break-all;
            margin: 12px 0; line-height: 1.6;
            border: 1px solid rgba(255,255,255,.08);
        }
        .event-chips { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 12px; }
        .event-chip {
            background: var(--accent-bg); color: var(--accent);
            font-size: 12px; font-weight: 600;
            padding: 5px 11px; border-radius: 6px;
            font-family: 'JetBrains Mono', monospace;
        }

        /* ── Divider ── */
        .section-divider {
            border: none; border-top: 2px solid var(--surface-3); margin: 24px 0;
        }

        /* ══ LARGE TABLET (≤1100px) ═══════════════════════════════════ */
        @media (max-width: 1100px) {
            .main-content { padding: 24px; }
        }

        /* ══ TABLET (≤900px) ══════════════════════════════════════════ */
        @media (max-width: 900px) {
            .settings-layout { grid-template-columns: 1fr; }
            .settings-nav {
                position: static;
                display: flex;
                flex-wrap: wrap;
                gap: 6px;
                padding: 10px;
            }
            .nav-label { display: none; }
            .settings-nav a { margin-bottom: 0; font-size: 13px; padding: 9px 12px; }
        }

        /* ══ MOBILE (≤768px) ══════════════════════════════════════════ */
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 80px 16px 24px; /* 60px fixed mobile header + 20px gap */
            }
            .top-bar {
                padding: 16px;
                border-radius: 12px;
                margin-bottom: 20px;
            }
            .top-bar h1 { font-size: 18px; }
            .top-bar-sub { font-size: 13px; }
            .top-bar-icon { width: 36px; height: 36px; font-size: 17px; }
            .card { padding: 18px 16px; margin-bottom: 16px; }
            .card-header { margin-bottom: 18px; padding-bottom: 14px; }
            .card-header h3 { font-size: 16px; }
            .info-grid { grid-template-columns: 1fr 1fr; gap: 10px; }
            .info-item { padding: 12px 14px; }
            .info-item span { font-size: 15px; }
            .settings-nav a { font-size: 13px; padding: 9px 11px; }
        }

        /* ══ SMALL PHONES (≤480px) ════════════════════════════════════ */
        @media (max-width: 480px) {
            .main-content { padding: 76px 12px 20px; }
            .top-bar { flex-direction: column; align-items: flex-start; gap: 10px; }
            .info-grid { grid-template-columns: 1fr; }
            .btn-row { flex-direction: column; }
            .btn { width: 100%; justify-content: center; }
            input[type=text],
            input[type=email],
            input[type=password] { font-size: 16px; } /* prevent iOS zoom */
        }
    </style>
</head>
<body>
<div class="admin-layout">
    <?php require_once 'sidebar.php'; ?>

    <main class="main-content">

        <!-- Top bar -->
        <div class="top-bar">
            <div class="top-bar-title">
                <div class="top-bar-icon">⚙️</div>
                <div>
                    <h1>Settings</h1>
                    <div class="top-bar-sub">Manage your account and site configuration</div>
                </div>
            </div>
            <div class="mode-badge <?php echo $stripeMode; ?>">
                Stripe: <?php echo ucfirst($stripeMode === 'unset' ? 'Not configured' : $stripeMode . ' mode'); ?>
            </div>
        </div>

        <!-- Flash message -->
        <?php $flash = getFlashMessage(); ?>
        <?php if ($flash): ?>
        <div class="alert alert-<?php echo htmlspecialchars($flash['type']); ?>">
            <span class="alert-icon"><?php echo $flash['type'] === 'success' ? '✓' : '!'; ?></span>
            <span><?php echo htmlspecialchars($flash['message']); ?></span>
        </div>
        <?php endif; ?>

        <div class="settings-layout">

            <!-- Nav -->
            <nav class="settings-nav">
                <span class="nav-label">Settings</span>
                <a href="?section=general" class="<?php echo $section === 'general' ? 'active' : ''; ?>">
                    <span class="nav-icon">🏠</span> General
                </a>
                <a href="?section=account" class="<?php echo $section === 'account' ? 'active' : ''; ?>">
                    <span class="nav-icon">👤</span> My Account
                </a>
                <a href="?section=system"  class="<?php echo $section === 'system'  ? 'active' : ''; ?>">
                    <span class="nav-icon">🔧</span> System
                </a>
            </nav>

            <!-- Content -->
            <div>

            <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <span class="alert-icon">⚠</span>
                <div>
                    <?php if (count($errors) === 1): ?>
                        <?php echo htmlspecialchars($errors[0]); ?>
                    <?php else: ?>
                        <ul><?php foreach ($errors as $e): ?><li><?php echo htmlspecialchars($e); ?></li><?php endforeach; ?></ul>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- ── GENERAL ── -->
            <?php if ($section === 'general'): ?>

                <div class="card">
                    <div class="card-header">
                        <div class="card-header-icon">🏠</div>
                        <div>
                            <h3>Site Overview</h3>
                            <p>Current configuration from your .env file</p>
                        </div>
                    </div>
                    <div class="info-grid">
                        <div class="info-item">
                            <strong>Site Name</strong>
                            <span><?php echo htmlspecialchars(SITE_NAME); ?></span>
                        </div>
                        <div class="info-item">
                            <strong>Free Article Limit</strong>
                            <span><?php echo FREE_ARTICLE_LIMIT; ?></span>
                            <span class="info-sub">per reader / month</span>
                        </div>
                        <div class="info-item">
                            <strong>Articles Per Page</strong>
                            <span><?php echo ARTICLES_PER_PAGE; ?></span>
                        </div>
                        <div class="info-item">
                            <strong>Currency</strong>
                            <span>USD $</span>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <div class="card-header-icon">💳</div>
                        <div>
                            <h3>Pricing Plans</h3>
                            <p>As configured in your .env file</p>
                        </div>
                    </div>
                    <div class="info-grid">
                        <div class="info-item">
                            <strong>Monthly Plan</strong>
                            <span>$<?php echo defined('MONTHLY_PRICE') ? number_format(MONTHLY_PRICE / 100, 0) : '3'; ?></span>
                            <span class="info-sub">per month</span>
                        </div>
                        <div class="info-item">
                            <strong>Yearly Plan</strong>
                            <span>$<?php echo defined('YEARLY_PRICE') ? number_format(YEARLY_PRICE / 100, 0) : '30'; ?></span>
                            <span class="info-sub">per year</span>
                        </div>
                    </div>
                    <p style="margin-top:16px;font-size:13px;color:var(--ink-3);">
                        To update pricing, edit <code style="background:var(--surface-3);padding:2px 6px;border-radius:4px;font-family:'JetBrains Mono',monospace">MONTHLY_PRICE</code> and <code style="background:var(--surface-3);padding:2px 6px;border-radius:4px;font-family:'JetBrains Mono',monospace">YEARLY_PRICE</code> in your .env file, then update the corresponding prices in your Stripe Dashboard.
                    </p>
                </div>

            <!-- ── ACCOUNT ── -->
            <?php elseif ($section === 'account'): ?>

                <div class="card">
                    <div class="card-header">
                        <div class="card-header-icon">👤</div>
                        <div>
                            <h3>Profile Information</h3>
                            <p>Update your display name and email</p>
                        </div>
                    </div>
                    <form method="POST">
                        <input type="hidden" name="action" value="update_profile">
                        <div class="form-group">
                            <label>Full Name</label>
                            <input type="text" name="full_name"
                                   value="<?php echo htmlspecialchars($currentUser['full_name']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Email Address</label>
                            <input type="email" name="email"
                                   value="<?php echo htmlspecialchars($currentUser['email']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Role</label>
                            <input type="text" value="<?php echo ucfirst($currentUser['role'] ?? 'admin'); ?>" disabled>
                        </div>
                        <div class="form-group">
                            <label>Member Since</label>
                            <input type="text" value="<?php echo formatDate($currentUser['created_at']); ?>" disabled>
                        </div>
                        <div class="btn-row">
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>

                <div class="card">
                    <div class="card-header">
                        <div class="card-header-icon">🔑</div>
                        <div>
                            <h3>Change Password</h3>
                            <p>Set a new admin password — no current password required</p>
                        </div>
                    </div>
                    <form method="POST">
                        <input type="hidden" name="action" value="change_password">
                        <div class="form-group">
                            <label>New Password</label>
                            <input type="password" name="new_password" id="newPwd"
                                   required minlength="8"
                                   placeholder="At least 8 characters"
                                   oninput="checkStrength(this.value)">
                            <div class="pwd-strength">
                                <div class="pwd-bar"><div class="pwd-fill" id="pwdFill"></div></div>
                                <div class="pwd-label" id="pwdLabel">Enter a password</div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Confirm New Password</label>
                            <input type="password" name="confirm_password"
                                   required placeholder="Re-enter password">
                        </div>
                        <div class="btn-row">
                            <button type="submit" class="btn btn-primary">Update Password</button>
                        </div>
                    </form>
                </div>

            <!-- ── SYSTEM ── -->
            <?php elseif ($section === 'system'): ?>

                <div class="card">
                    <div class="card-header">
                        <div class="card-header-icon">🔧</div>
                        <div>
                            <h3>System Information</h3>
                            <p>Server environment and runtime details</p>
                        </div>
                    </div>
                    <div class="info-grid">
                        <div class="info-item">
                            <strong>PHP Version</strong>
                            <span><?php echo htmlspecialchars($phpVersion); ?></span>
                        </div>
                        <div class="info-item">
                            <strong>MySQL Version</strong>
                            <span><?php echo htmlspecialchars($mysqlVersion); ?></span>
                        </div>
                        <div class="info-item">
                            <strong>Stripe Mode</strong>
                            <span>
                                <span class="mode-badge <?php echo $stripeMode; ?>">
                                    <?php echo $stripeMode === 'live' ? 'Live' : ($stripeMode === 'test' ? 'Test' : 'Not Set'); ?>
                                </span>
                            </span>
                        </div>
                        <div class="info-item">
                            <strong>Timezone</strong>
                            <span style="font-size:14px"><?php echo date_default_timezone_get(); ?></span>
                        </div>
                        <div class="info-item">
                            <strong>Server Time</strong>
                            <span style="font-size:14px"><?php echo date('d M Y, H:i'); ?></span>
                        </div>
                        <div class="info-item">
                            <strong>Environment</strong>
                            <span style="font-size:14px"><?php echo file_exists(__DIR__ . '/../.env') ? 'Using .env' : 'No .env file'; ?></span>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <div class="card-header-icon">📊</div>
                        <div>
                            <h3>Site Statistics</h3>
                            <p>Live counts from your database</p>
                        </div>
                    </div>
                    <div class="info-grid">
                        <div class="info-item">
                            <strong>Total Articles</strong>
                            <span><?php echo number_format($totalArticles); ?></span>
                        </div>
                        <div class="info-item">
                            <strong>Published</strong>
                            <span><?php echo number_format($pubArticles); ?></span>
                        </div>
                        <div class="info-item">
                            <strong>Premium Articles</strong>
                            <span><?php echo number_format($premArticles); ?></span>
                        </div>
                        <div class="info-item">
                            <strong>Total Users</strong>
                            <span><?php echo number_format($totalUsers); ?></span>
                        </div>
                        <div class="info-item">
                            <strong>Active Subscribers</strong>
                            <span><?php echo number_format($activeSubs); ?></span>
                        </div>
                        <div class="info-item">
                            <strong>Total Revenue</strong>
                            <span>$<?php echo number_format($totalRev, 0); ?></span>
                        </div>
                        <div class="info-item">
                            <strong>Successful Txns</strong>
                            <span><?php echo number_format($totalTxns); ?></span>
                        </div>
                    </div>
                </div>

            <?php endif; ?>
            </div><!-- /content -->
        </div><!-- /settings-layout -->
    </main>
</div>

<script>
function checkStrength(val) {
    const fill  = document.getElementById('pwdFill');
    const label = document.getElementById('pwdLabel');
    let score = 0;
    if (val.length >= 8)  score++;
    if (val.length >= 12) score++;
    if (/[A-Z]/.test(val)) score++;
    if (/[0-9]/.test(val)) score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;

    const levels = [
        { pct: '0%',   color: 'var(--border)',  text: 'Enter a password' },
        { pct: '20%',  color: 'var(--red)',      text: 'Very weak' },
        { pct: '40%',  color: '#f97316',         text: 'Weak' },
        { pct: '60%',  color: 'var(--gold)',     text: 'Fair' },
        { pct: '80%',  color: '#22c55e',         text: 'Strong' },
        { pct: '100%', color: 'var(--green)',    text: 'Very strong' },
    ];

    const lvl = val.length === 0 ? levels[0] : levels[Math.min(score, 5)];
    fill.style.width      = lvl.pct;
    fill.style.background = lvl.color;
    label.textContent     = lvl.text;
    label.style.color     = lvl.color;
}
</script>
</body>
</html>