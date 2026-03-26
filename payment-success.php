<?php
require_once 'config.php';
require_once 'stripe-php/init.php';

requireLogin();

$sessionId = $_GET['session_id'] ?? '';

if (empty($sessionId)) {
    flashMessage('danger', 'Invalid session');
    redirect('pricing.php');
}

\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

$success      = false;
$planType     = '';
$amount       = 0;
$errorMessage = '';

try {
    $session = \Stripe\Checkout\Session::retrieve([
        'id'     => $sessionId,
        'expand' => ['subscription'],
    ]);

    if ($session->payment_status === 'paid') {
        $db     = db();
        $userId = $_SESSION['user_id'];

        $subscription = $session->subscription;
        $planType     = $session->metadata->plan_type ?? 'monthly';
        $amount       = ($session->amount_total ?? 0) / 100;

        // ── Idempotent upsert ──────────────────────────────────────
        // The webhook (checkout.session.completed) also writes this row.
        // ON DUPLICATE KEY UPDATE makes this safe whether the webhook
        // fires first or the redirect hits first — no double-entries.
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

        // ── Transaction record (insert only if not already logged) ──
        $stmt = $db->prepare("
            SELECT id FROM transactions
            WHERE stripe_payment_intent_id = ?
            LIMIT 1
        ");
        $stmt->execute([$session->payment_intent]);
        if (!$stmt->fetch()) {
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

        logActivity($userId, 'subscription_activated', 'subscription', null, [
            'plan_type' => $planType,
            'amount'    => $amount,
        ]);

        // Confirmation email
        $emailBody = "
            <h2>Subscription Activated!</h2>
            <p>Thank you for subscribing to " . htmlspecialchars(SITE_NAME) . ".</p>
            <p><strong>Plan:</strong> " . ucfirst($planType) . "</p>
            <p><strong>Amount:</strong> ₹" . number_format($amount, 2) . "</p>
            <p>You now have unlimited access to all premium content.</p>
            <p><a href='" . SITE_URL . "'>Start Reading →</a></p>
        ";
        sendEmail($_SESSION['user_email'] ?? $session->customer_details->email ?? '', 
                  'Subscription Activated — ' . SITE_NAME, $emailBody);

        $success = true;
    }

} catch (Exception $e) {
    $errorMessage = $e->getMessage();
    error_log('payment-success error: ' . $e->getMessage());
}
?>
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
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 24px;
            -webkit-font-smoothing: antialiased;
        }

        .card {
            background: #fff;
            max-width: 480px;
            width: 100%;
            border-radius: 24px;
            padding: 56px 48px 52px;
            box-shadow: 0 24px 64px rgba(0,0,0,.28);
            text-align: center;
        }

        .icon-wrap {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 24px;
        }

        .icon-circle {
            width: 88px; height: 88px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 40px;
        }
        .icon-circle.success { background: #dcfce7; }
        .icon-circle.error   { background: #fee2e2; }

        h1 {
            font-family: 'Playfair Display', serif;
            font-size: 30px; font-weight: 900;
            color: #0a0a0a;
            margin-bottom: 12px; line-height: 1.2;
        }

        .subtitle {
            font-size: 16px; color: #4a5568;
            line-height: 1.6; margin-bottom: 28px;
        }

        .details-box {
            background: #f9fafb;
            border-radius: 12px;
            padding: 20px 24px;
            margin-bottom: 28px;
            text-align: left;
        }
        .details-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
            font-size: 14px;
        }
        .details-row:last-child { border-bottom: none; padding-bottom: 0; }
        .details-row .label { color: #718096; font-weight: 500; }
        .details-row .value { color: #1a1a1a; font-weight: 600; }
        .value.green { color: #16a34a; }

        .btn {
            display: block;
            width: 100%;
            padding: 14px 24px;
            border-radius: 12px;
            font-family: 'Inter', sans-serif;
            font-size: 15px; font-weight: 700;
            text-decoration: none; text-align: center;
            transition: all .2s;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            box-shadow: 0 6px 18px rgba(102,126,234,.38);
            margin-bottom: 10px;
        }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 10px 26px rgba(102,126,234,.48); }
        .btn-outline {
            background: transparent;
            border: 2px solid #e5e7eb;
            color: #718096;
        }
        .btn-outline:hover { border-color: #667eea; color: #667eea; }

        @media (max-width: 520px) {
            body { padding: 16px; align-items: flex-start; padding-top: 40px; }
            .card { padding: 40px 24px 44px; border-radius: 20px; }
            h1 { font-size: 24px; }
            .icon-circle { width: 72px; height: 72px; font-size: 32px; }
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
            <div class="icon-wrap">
                <div class="icon-circle success">✓</div>
            </div>
            <h1>Payment Successful!</h1>
            <p class="subtitle">Your subscription is now active. Enjoy unlimited access to all premium articles.</p>

            <div class="details-box">
                <div class="details-row">
                    <span class="label">Plan</span>
                    <span class="value"><?php echo ucfirst($planType); ?> Subscription</span>
                </div>
                <div class="details-row">
                    <span class="label">Amount</span>
                    <span class="value">₹<?php echo number_format($amount, 2); ?></span>
                </div>
                <div class="details-row">
                    <span class="label">Status</span>
                    <span class="value green">✓ Active</span>
                </div>
                <div class="details-row">
                    <span class="label">Access</span>
                    <span class="value">Unlimited Premium Articles</span>
                </div>
            </div>

            <a href="index.php" class="btn btn-primary">Start Reading →</a>
            <a href="pricing.php" class="btn btn-outline">View Subscription Details</a>

        <?php else: ?>
            <div class="icon-wrap">
                <div class="icon-circle error">✗</div>
            </div>
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