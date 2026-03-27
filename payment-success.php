<?php
ob_start(); // Buffer output

require_once 'config.php';
require_once __DIR__ . '/vendor/autoload.php';

// ── DO NOT call requireLogin() here ────────────────────────
// Stripe redirects from stripe.com back to this page.
// On HTTP (localhost), session.cookie_secure drops the session.
// We recover the user from Stripe metadata instead.

\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

$sessionId    = $_GET['session_id'] ?? '';
$success      = false;
$planType     = '';
$amount       = 0;
$errorMessage = '';

if (empty($sessionId)) {
    redirect('pricing.php');
}

try {
    $session = \Stripe\Checkout\Session::retrieve([
        'id'     => $sessionId,
        'expand' => ['subscription', 'customer'],
    ]);

    if ($session->payment_status === 'paid') {
        $db = db();

        // ── Recover user_id ─────────────────────────────────
        $userId = $_SESSION['user_id'] ?? null;

        if (!$userId) {
            // Session was lost during Stripe redirect — recover from metadata
            $userId = $session->metadata->user_id ?? null;

            if ($userId) {
                $stmt = $db->prepare("SELECT * FROM users WHERE id = ? AND is_active = 1 LIMIT 1");
                $stmt->execute([$userId]);
                $recoveredUser = $stmt->fetch();

                if ($recoveredUser) {
                    $_SESSION['user_id']    = $recoveredUser['id'];
                    $_SESSION['user_email'] = $recoveredUser['email'];
                    $_SESSION['user_name']  = $recoveredUser['full_name'];
                    $_SESSION['user_role']  = $recoveredUser['role'] ?? 'user';
                }
            }
        }

        if (!$userId) {
            flashMessage('warning', 'Payment was successful! Please log in to activate your subscription.');
            redirect('login.php');
        }

        $subscription = $session->subscription;
        $planType     = $session->metadata->plan_type ?? 'monthly';
        $amount       = ($session->amount_total ?? 0) / 100;

        // ── Idempotent upsert ────────────────────────────────
        $stmt = $db->prepare("
            INSERT INTO subscriptions
                (user_id, stripe_customer_id, stripe_subscription_id, plan_type, status,
                 current_period_start, current_period_end)
            VALUES (?, ?, ?, ?, 'active', FROM_UNIXTIME(?), FROM_UNIXTIME(?))
            ON DUPLICATE KEY UPDATE
                stripe_subscription_id = VALUES(stripe_subscription_id),
                plan_type              = VALUES(plan_type),
                status                 = 'active',
                current_period_start   = VALUES(current_period_start),
                current_period_end     = VALUES(current_period_end)
        ");
        $stmt->execute([
            $userId,
            $session->customer,
            $subscription->id,
            $planType,
            $subscription->current_period_start,
            $subscription->current_period_end,
        ]);

        // ── Transaction (idempotent) ─────────────────────────
        $check = $db->prepare("SELECT id FROM transactions WHERE stripe_payment_intent_id = ? LIMIT 1");
        $check->execute([$session->payment_intent]);
        if (!$check->fetch()) {
            $stmt = $db->prepare("
                INSERT INTO transactions
                    (user_id, stripe_payment_intent_id, amount, currency, status, payment_method, description)
                VALUES (?, ?, ?, 'INR', 'succeeded', 'card', ?)
            ");
            $stmt->execute([
                $userId,
                $session->payment_intent,
                $amount,
                ucfirst($planType) . ' subscription',
            ]);
        }

        // ── Immediately mark session as premium ──────────────
        // Prevents showing free-tier UI before the next DB check
        $_SESSION['is_premium']        = true;
        $_SESSION['subscription_plan'] = $planType;

        logActivity($userId, 'subscription_activated', 'subscription', null, [
            'plan_type' => $planType,
            'amount'    => $amount,
        ]);

        $emailTo = $_SESSION['user_email']
            ?? ($session->customer_details->email ?? '');
        $emailBody = "
            <h2>Subscription Activated!</h2>
            <p>Thank you for subscribing to " . htmlspecialchars(SITE_NAME) . ".</p>
            <p><strong>Plan:</strong> " . ucfirst($planType) . "</p>
            <p><strong>Amount:</strong> ₹" . number_format($amount, 2) . "</p>
            <p>You now have unlimited access to all premium content.</p>
            <p><a href='" . SITE_URL . "'>Start Reading →</a></p>
        ";
        sendEmail($emailTo, 'Subscription Activated — ' . SITE_NAME, $emailBody);

        $success = true;
    }

} catch (Exception $e) {
    $errorMessage = $e->getMessage();
    error_log('payment-success error: ' . $e->getMessage());
}
?>
<?php ob_end_clean(); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $success ? 'Payment Successful' : 'Payment Failed'; ?> — <?php echo htmlspecialchars(SITE_NAME); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Playfair+Display:wght@700;900&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', -apple-system, sans-serif;
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 24px;
            -webkit-font-smoothing: antialiased;
        }
        .card {
            background: #fff; max-width: 520px; width: 100%;
            border-radius: 24px; padding: 56px 48px 52px;
            box-shadow: 0 24px 64px rgba(0,0,0,.28); text-align: center;
        }
        .icon-wrap { display: flex; align-items: center; justify-content: center; margin-bottom: 28px; }
        .icon-circle {
            width: 96px; height: 96px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 44px; font-weight: 900;
        }
        .icon-circle.success {
            background: linear-gradient(135deg, #d1fae5, #a7f3d0);
            color: #065f46;
            box-shadow: 0 8px 32px rgba(16,185,129,.25);
        }
        .icon-circle.error { background: linear-gradient(135deg, #fee2e2, #fecaca); color: #991b1b; }
        @keyframes pop { 0%{transform:scale(.5);opacity:0} 70%{transform:scale(1.15)} 100%{transform:scale(1);opacity:1} }
        .icon-circle.success { animation: pop .5s cubic-bezier(.34,1.56,.64,1) forwards; }
        h1 {
            font-family: 'Playfair Display', serif;
            font-size: 30px; font-weight: 900;
            color: #0a0a0a; margin-bottom: 10px; line-height: 1.2;
        }
        .subtitle { font-size: 16px; color: #4a5568; line-height: 1.65; margin-bottom: 24px; }
        .plan-badge {
            display: inline-flex; align-items: center; gap: 6px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white; font-size: 12px; font-weight: 700;
            padding: 6px 16px; border-radius: 100px; margin-bottom: 22px;
            text-transform: uppercase; letter-spacing: .5px;
        }
        .details-box {
            background: #f9fafb; border-radius: 14px; padding: 20px 24px;
            margin-bottom: 24px; text-align: left; border: 1px solid #e5e7eb;
        }
        .details-row {
            display: flex; justify-content: space-between; align-items: center;
            padding: 10px 0; border-bottom: 1px solid #eee; font-size: 14px;
        }
        .details-row:last-child { border-bottom: none; padding-bottom: 0; }
        .details-row .label { color: #718096; font-weight: 500; }
        .details-row .value { color: #1a1a1a; font-weight: 700; }
        .value.green { color: #059669; }
        .unlocked-section {
            background: linear-gradient(135deg, #f0fdf4, #dcfce7);
            border: 1px solid #bbf7d0; border-radius: 14px;
            padding: 18px 22px; margin-bottom: 28px; text-align: left;
        }
        .unlocked-title {
            font-size: 11px; font-weight: 700; text-transform: uppercase;
            letter-spacing: 1px; color: #065f46; margin-bottom: 10px;
        }
        .unlocked-item {
            display: flex; align-items: center; gap: 8px;
            font-size: 13px; color: #065f46; padding: 5px 0;
        }
        .unlocked-item::before { content: '✓'; font-weight: 900; font-size: 14px; flex-shrink: 0; }
        .btn {
            display: block; width: 100%; padding: 15px 24px; border-radius: 12px;
            font-family: 'Inter', sans-serif; font-size: 15px; font-weight: 700;
            text-decoration: none; text-align: center; transition: all .22s; margin-bottom: 10px;
        }
        .btn:last-child { margin-bottom: 0; }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff; box-shadow: 0 6px 18px rgba(102,126,234,.4);
        }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 10px 28px rgba(102,126,234,.5); }
        .btn-outline { background: transparent; border: 2px solid #e5e7eb; color: #718096; }
        .btn-outline:hover { border-color: #667eea; color: #667eea; }
        @media (max-width: 520px) {
            body { padding: 16px; align-items: flex-start; padding-top: 40px; }
            .card { padding: 40px 24px 44px; border-radius: 20px; }
            h1 { font-size: 24px; }
            .icon-circle { width: 80px; height: 80px; font-size: 36px; }
        }
        @media (max-width: 380px) {
            .card { padding: 32px 18px 36px; }
            h1 { font-size: 21px; }
        }
    </style>
</head>
<body>
    <div class="card">
        <?php if ($success): ?>
            <div class="icon-wrap"><div class="icon-circle success">✓</div></div>
            <div class="plan-badge">⭐ <?php echo ucfirst($planType); ?> Member</div>
            <h1>You're all set!</h1>
            <p class="subtitle">Payment successful. Your premium subscription is now active.</p>

            <div class="details-box">
                <div class="details-row">
                    <span class="label">Plan</span>
                    <span class="value"><?php echo ucfirst($planType); ?> Subscription</span>
                </div>
                <div class="details-row">
                    <span class="label">Amount Paid</span>
                    <span class="value">₹<?php echo number_format($amount, 2); ?></span>
                </div>
                <div class="details-row">
                    <span class="label">Status</span>
                    <span class="value green">✓ Active</span>
                </div>
                <div class="details-row">
                    <span class="label">Renews</span>
                    <span class="value"><?php echo $planType === 'monthly' ? 'Every month' : 'Every year'; ?></span>
                </div>
            </div>

            <div class="unlocked-section">
                <div class="unlocked-title">🔓 Now Unlocked For You</div>
                <div class="unlocked-item">Unlimited access to all premium articles</div>
                <div class="unlocked-item">Ad-free reading experience</div>
                <div class="unlocked-item">Exclusive member-only content</div>
                <div class="unlocked-item">Early access to new articles</div>
            </div>

            <a href="index.php" class="btn btn-primary">Start Reading Premium Articles →</a>
            <a href="account.php" class="btn btn-outline">View My Account</a>

        <?php else: ?>
            <div class="icon-wrap"><div class="icon-circle error">✗</div></div>
            <h1>Payment Failed</h1>
            <p class="subtitle">We couldn't process your payment. No charges have been made.</p>
            <?php if ($errorMessage): ?>
            <div class="details-box">
                <div class="details-row">
                    <span class="label">Reason</span>
                    <span class="value"><?php echo htmlspecialchars($errorMessage); ?></span>
                </div>
            </div>
            <?php endif; ?>
            <a href="pricing.php" class="btn btn-primary">Try Again</a>
            <a href="index.php" class="btn btn-outline">Back to Home</a>
        <?php endif; ?>
    </div>
</body>
</html>