<?php
require_once '../config.php';
requireAdmin();

$db = db();
$errors   = [];
$success  = false;
$section  = $_GET['section'] ?? 'general';

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'change_password') {
        $currentPwd = $_POST['current_password'] ?? '';
        $newPwd     = $_POST['new_password'] ?? '';
        $confirmPwd = $_POST['confirm_password'] ?? '';

        $stmt = $db->prepare("SELECT password_hash FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();

        if (!password_verify($currentPwd, $user['password_hash'])) {
            $errors[] = 'Current password is incorrect.';
        } elseif (strlen($newPwd) < 8) {
            $errors[] = 'New password must be at least 8 characters.';
        } elseif ($newPwd !== $confirmPwd) {
            $errors[] = 'Passwords do not match.';
        } else {
            $hash = password_hash($newPwd, PASSWORD_BCRYPT);
            $db->prepare("UPDATE users SET password_hash = ? WHERE id = ?")->execute([$hash, $_SESSION['user_id']]);
            setFlashMessage('Password changed successfully!', 'success');
            header('Location: settings.php?section=account');
            exit;
        }
        $section = 'account';
    } elseif ($action === 'update_profile') {
        $fullName = sanitizeInput($_POST['full_name'] ?? '');
        $email    = sanitizeInput($_POST['email'] ?? '');

        if (!$fullName || !$email) {
            $errors[] = 'Name and email are required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email address.';
        } else {
            $check = $db->prepare("SELECT id FROM users WHERE email=? AND id!=?");
            $check->execute([$email, $_SESSION['user_id']]);
            if ($check->fetch()) {
                $errors[] = 'Email already in use.';
            } else {
                $db->prepare("UPDATE users SET full_name=?,email=? WHERE id=?")->execute([$fullName, $email, $_SESSION['user_id']]);
                $_SESSION['user_name'] = $fullName;
                $_SESSION['user_email'] = $email;
                setFlashMessage('Profile updated!', 'success');
                header('Location: settings.php?section=account');
                exit;
            }
        }
        $section = 'account';
    }
}

$flash = getFlashMessage();
$currentUser = $db->prepare("SELECT * FROM users WHERE id=?");
$currentUser->execute([$_SESSION['user_id']]);
$currentUser = $currentUser->fetch();

// System info
$phpVersion   = PHP_VERSION;
$mysqlVersion = $db->query("SELECT VERSION()")->fetchColumn();
$totalArticles = $db->query("SELECT COUNT(*) FROM articles")->fetchColumn();
$totalUsers    = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalTxns     = $db->query("SELECT COUNT(*) FROM transactions WHERE status='succeeded'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Admin | <?php echo SITE_NAME; ?></title>
    <link rel="icon" type="image/x-icon" href="https://png.pngtree.com/element_our/sm/20180518/sm_5aff60887f7d9.jpg">
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;background:#f5f7fa}
        .admin-layout{display:flex;min-height:100vh}
        .main-content{flex:1;margin-left:280px;padding:30px}
        .top-bar{background:white;padding:20px 30px;border-radius:12px;margin-bottom:30px;display:flex;justify-content:space-between;align-items:center;box-shadow:0 2px 8px rgba(0,0,0,.05)}
        .top-bar h1{font-size:24px}
        .settings-layout{display:grid;grid-template-columns:220px 1fr;gap:25px}
        .settings-nav{background:white;border-radius:12px;padding:15px;box-shadow:0 2px 8px rgba(0,0,0,.05);height:fit-content}
        .settings-nav a{display:block;padding:12px 15px;border-radius:8px;text-decoration:none;font-weight:600;font-size:14px;color:#666;transition:all .2s;margin-bottom:4px}
        .settings-nav a:hover,.settings-nav a.active{background:#667eea;color:white}
        .card{background:white;padding:30px;border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,.05);margin-bottom:25px}
        .card h3{font-size:18px;font-weight:700;margin-bottom:20px;padding-bottom:15px;border-bottom:2px solid #f0f0f0}
        .form-group{margin-bottom:20px}
        label{display:block;font-weight:600;font-size:13px;margin-bottom:6px;color:#333;text-transform:uppercase;letter-spacing:.5px}
        input[type=text],input[type=email],input[type=password]{width:100%;padding:11px 14px;border:2px solid #e0e0e0;border-radius:6px;font-size:14px;font-family:inherit;transition:border-color .2s}
        input:focus{outline:none;border-color:#667eea}
        .btn{padding:10px 24px;border:none;border-radius:6px;font-weight:600;cursor:pointer;text-decoration:none;display:inline-block;transition:all .2s;font-size:14px;font-family:inherit}
        .btn-primary{background:#667eea;color:white}
        .btn-primary:hover{background:#5568d3}
        .btn-danger{background:#dc3545;color:white}
        .alert{padding:15px 20px;border-radius:8px;margin-bottom:20px;font-weight:600}
        .alert-success{background:#d4edda;color:#155724;border-left:4px solid #28a745}
        .alert-danger{background:#f8d7da;color:#721c24;border-left:4px solid #dc3545}
        .alert-danger ul{margin:8px 0 0 20px}
        .info-grid{display:grid;grid-template-columns:1fr 1fr;gap:15px}
        .info-item{background:#f8f9fa;padding:15px;border-radius:8px}
        .info-item strong{display:block;font-size:12px;text-transform:uppercase;letter-spacing:.5px;color:#666;margin-bottom:6px}
        .info-item span{font-size:16px;font-weight:700;color:#333}
        .badge-pill{display:inline-block;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:700;background:#e8d5ff;color:#6f42c1}
        .divider{border:none;border-top:2px solid #f0f0f0;margin:25px 0}
        .hint{font-size:12px;color:#999;margin-top:4px}
        .danger-zone{border:2px solid #dc3545;border-radius:12px;padding:25px}
        .danger-zone h3{color:#dc3545;border-bottom-color:#dc3545}
        .config-display{background:#1a1d29;border-radius:8px;padding:20px;color:#a8ff78;font-family:monospace;font-size:13px;line-height:1.8}
        .config-display .key{color:#78c1ff}
        .config-display .val{color:#ffda78}
        @media(max-width:900px){.settings-layout{grid-template-columns:1fr}.settings-nav{display:flex;flex-wrap:wrap;gap:5px;padding:10px}.settings-nav a{margin-bottom:0}}
    </style>
</head>
<body>
<div class="admin-layout">
    <?php require_once 'sidebar.php'; ?>

    <main class="main-content">
        <div class="top-bar">
            <h1>⚙️ Settings</h1>
        </div>

        <?php if ($flash): ?>
        <div class="alert alert-<?php echo $flash['type']; ?>"><?php echo htmlspecialchars($flash['message']); ?></div>
        <?php endif; ?>

        <div class="settings-layout">
            <nav class="settings-nav">
                <a href="?section=general" class="<?php echo $section==='general'?'active':''; ?>">🏠 General</a>
                <a href="?section=account" class="<?php echo $section==='account'?'active':''; ?>">👤 My Account</a>
                <a href="?section=system"  class="<?php echo $section==='system'?'active':''; ?>">🔧 System Info</a>
                <a href="?section=config"  class="<?php echo $section==='config'?'active':''; ?>">📋 Config Guide</a>
            </nav>

            <div>
            <?php if ($section === 'general'): ?>
                <div class="card">
                    <h3>Site Information</h3>
                    <div class="info-grid">
                        <div class="info-item"><strong>Site Name</strong><span><?php echo SITE_NAME; ?></span></div>
                        <div class="info-item"><strong>Currency</strong><span>INR (₹)</span></div>
                        <div class="info-item"><strong>Free Articles Limit</strong><span><?php echo FREE_ARTICLES_LIMIT; ?> per user</span></div>
                        <div class="info-item"><strong>Articles per Page</strong><span><?php echo ARTICLES_PER_PAGE; ?></span></div>
                    </div>
                    <p style="margin-top:20px;color:#666;font-size:14px">
                        💡 To change site-wide settings like site name, pricing, and Stripe keys, edit the <code style="background:#f0f0f0;padding:2px 6px;border-radius:4px">config.php</code> file directly.
                    </p>
                </div>

                <div class="card">
                    <h3>Pricing Plans</h3>
                    <div class="info-grid">
                        <div class="info-item">
                            <strong>Monthly Plan</strong>
                            <span>₹<?php echo defined('MONTHLY_PRICE') ? number_format(MONTHLY_PRICE/100, 0) : '299'; ?>/month</span>
                        </div>
                        <div class="info-item">
                            <strong>Yearly Plan</strong>
                            <span>₹<?php echo defined('YEARLY_PRICE') ? number_format(YEARLY_PRICE/100, 0) : '2499'; ?>/year</span>
                        </div>
                    </div>
                    <p style="margin-top:20px;color:#666;font-size:14px">
                        To update pricing, update <code style="background:#f0f0f0;padding:2px 6px;border-radius:4px">MONTHLY_PRICE</code> and <code style="background:#f0f0f0;padding:2px 6px;border-radius:4px">YEARLY_PRICE</code> in config.php, and update your Stripe product prices.
                    </p>
                </div>

            <?php elseif ($section === 'account'): ?>
                <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul><?php foreach($errors as $e): ?><li><?php echo htmlspecialchars($e); ?></li><?php endforeach; ?></ul>
                </div>
                <?php endif; ?>

                <div class="card">
                    <h3>👤 Profile</h3>
                    <form method="POST">
                        <input type="hidden" name="action" value="update_profile">
                        <div class="form-group">
                            <label>Full Name</label>
                            <input type="text" name="full_name" value="<?php echo htmlspecialchars($currentUser['full_name']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" value="<?php echo htmlspecialchars($currentUser['email']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Role</label>
                            <input type="text" value="<?php echo ucfirst($currentUser['role']); ?>" disabled style="background:#f8f9fa;color:#666">
                        </div>
                        <button type="submit" class="btn btn-primary">Save Profile</button>
                    </form>
                </div>

                <div class="card">
                    <h3>🔑 Change Password</h3>
                    <form method="POST">
                        <input type="hidden" name="action" value="change_password">
                        <div class="form-group">
                            <label>Current Password</label>
                            <input type="password" name="current_password" required>
                        </div>
                        <div class="form-group">
                            <label>New Password</label>
                            <input type="password" name="new_password" id="newPwd" required minlength="8">
                            <div class="hint">At least 8 characters</div>
                        </div>
                        <div class="form-group">
                            <label>Confirm New Password</label>
                            <input type="password" name="confirm_password" id="confirmPwd" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Change Password</button>
                    </form>
                </div>

            <?php elseif ($section === 'system'): ?>
                <div class="card">
                    <h3>🔧 System Information</h3>
                    <div class="info-grid">
                        <div class="info-item"><strong>PHP Version</strong><span><?php echo $phpVersion; ?></span></div>
                        <div class="info-item"><strong>MySQL Version</strong><span><?php echo $mysqlVersion; ?></span></div>
                        <div class="info-item"><strong>Total Articles</strong><span><?php echo $totalArticles; ?></span></div>
                        <div class="info-item"><strong>Total Users</strong><span><?php echo $totalUsers; ?></span></div>
                        <div class="info-item"><strong>Successful Transactions</strong><span><?php echo $totalTxns; ?></span></div>
                        <div class="info-item"><strong>Stripe Mode</strong>
                            <span>
                                <?php
                                $stripeKey = defined('STRIPE_SECRET_KEY') ? STRIPE_SECRET_KEY : '';
                                if (str_starts_with($stripeKey, 'sk_live_')) echo '🟢 Live';
                                elseif (str_starts_with($stripeKey, 'sk_test_')) echo '🟡 Test';
                                else echo '⚪ Not Set';
                                ?>
                            </span>
                        </div>
                        <div class="info-item"><strong>Server Time</strong><span><?php echo date('d M Y H:i:s'); ?></span></div>
                        <div class="info-item"><strong>Timezone</strong><span><?php echo date_default_timezone_get(); ?></span></div>
                    </div>
                </div>

                <div class="card">
                    <h3>📊 Quick Stats</h3>
                    <div class="info-grid">
                        <?php
                        $pubArticles = $db->query("SELECT COUNT(*) FROM articles WHERE is_published=1")->fetchColumn();
                        $premArticles = $db->query("SELECT COUNT(*) FROM articles WHERE is_premium=1")->fetchColumn();
                        $activeSubs = $db->query("SELECT COUNT(*) FROM subscriptions WHERE status='active' AND plan_type IN('monthly','yearly')")->fetchColumn();
                        $totalRev = $db->query("SELECT SUM(amount) FROM transactions WHERE status='succeeded'")->fetchColumn() ?? 0;
                        ?>
                        <div class="info-item"><strong>Published Articles</strong><span><?php echo $pubArticles; ?></span></div>
                        <div class="info-item"><strong>Premium Articles</strong><span><?php echo $premArticles; ?></span></div>
                        <div class="info-item"><strong>Active Subscribers</strong><span><?php echo $activeSubs; ?></span></div>
                        <div class="info-item"><strong>Total Revenue</strong><span>₹<?php echo number_format($totalRev, 0); ?></span></div>
                    </div>
                </div>

            <?php elseif ($section === 'config'): ?>
                <div class="card">
                    <h3>📋 Config.php Key Settings</h3>
                    <p style="color:#666;margin-bottom:20px;font-size:14px">Important constants to configure in your <code style="background:#f0f0f0;padding:2px 6px;border-radius:4px">config.php</code> file:</p>
                    <div class="config-display">
                        <div><span class="key">DB_HOST</span> = <span class="val">'localhost'</span></div>
                        <div><span class="key">DB_NAME</span> = <span class="val">'subscription_blog'</span></div>
                        <div><span class="key">DB_USER</span> = <span class="val">'your_db_user'</span></div>
                        <div><span class="key">DB_PASS</span> = <span class="val">'your_db_password'</span></div>
                        <br>
                        <div><span class="key">STRIPE_PUBLIC_KEY</span> = <span class="val">'pk_test_...'</span></div>
                        <div><span class="key">STRIPE_SECRET_KEY</span> = <span class="val">'sk_test_...'</span></div>
                        <div><span class="key">STRIPE_WEBHOOK_SECRET</span> = <span class="val">'whsec_...'</span></div>
                        <br>
                        <div><span class="key">MONTHLY_PRICE_ID</span> = <span class="val">'price_...'</span> <span style="color:#999">// Stripe Price ID</span></div>
                        <div><span class="key">YEARLY_PRICE_ID</span>  = <span class="val">'price_...'</span> <span style="color:#999">// Stripe Price ID</span></div>
                        <br>
                        <div><span class="key">SITE_NAME</span> = <span class="val">'Premium Blog'</span></div>
                        <div><span class="key">FREE_ARTICLES_LIMIT</span> = <span class="val">3</span></div>
                        <div><span class="key">ARTICLES_PER_PAGE</span>  = <span class="val">12</span></div>
                    </div>
                </div>

                <div class="card">
                    <h3>🌐 Stripe Webhook Setup</h3>
                    <p style="color:#666;font-size:14px;margin-bottom:15px">Configure this URL in your Stripe Dashboard → Webhooks:</p>
                    <div style="background:#f0f4ff;border:2px solid #667eea;padding:15px;border-radius:8px;font-family:monospace;font-size:14px;color:#667eea;word-break:break-all">
                        <?php echo (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/stripe-webhook.php
                    </div>
                    <p style="color:#666;font-size:13px;margin-top:12px">Events to listen: <code>checkout.session.completed</code>, <code>customer.subscription.updated</code>, <code>customer.subscription.deleted</code>, <code>invoice.payment_failed</code></p>
                </div>
            <?php endif; ?>
            </div>
        </div>
    </main>
</div>
</body>
</html>