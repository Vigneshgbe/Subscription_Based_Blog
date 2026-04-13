<?php
ob_start();
require_once 'config.php';
require_once __DIR__ . '/vendor/autoload.php';

\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

$sessionId    = $_GET['session_id'] ?? '';
$success      = false;
$planType     = '';
$amount       = 0;
$currency     = 'CHF';
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

        $userId = $_SESSION['user_id'] ?? null;

        if (!$userId) {
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
            flashMessage('warning', 'Payment successful! Please log in to activate your subscription.');
            redirect('login.php');
        }

        $subscription = $session->subscription;
        $planType     = $session->metadata->plan_type ?? 'monthly';
        $amount       = ($session->amount_total ?? 0) / 100;
        $currency     = strtoupper($session->currency ?? 'CHF');

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
            $session->customer->id ?? $session->customer,
            $subscription->id,
            $planType,
            $subscription->current_period_start,
            $subscription->current_period_end,
        ]);

        $check = $db->prepare("SELECT id FROM transactions WHERE stripe_payment_intent_id = ? LIMIT 1");
        $check->execute([$session->payment_intent]);
        if (!$check->fetch()) {
            $stmt = $db->prepare("
                INSERT INTO transactions
                    (user_id, stripe_payment_intent_id, amount, currency, status, payment_method, description)
                VALUES (?, ?, ?, ?, 'succeeded', 'card', ?)
            ");
            $stmt->execute([
                $userId,
                $session->payment_intent,
                $amount,
                $currency,
                ucfirst($planType) . ' subscription',
            ]);
        }

        $_SESSION['is_premium']        = true;
        $_SESSION['subscription_plan'] = $planType;

        logActivity($userId, 'subscription_activated', 'subscription', null, [
            'plan_type' => $planType,
            'amount'    => $amount,
        ]);

        $success = true;
    }

} catch (Exception $e) {
    $errorMessage = $e->getMessage();
    error_log('payment-success error: ' . $e->getMessage());
}
ob_end_clean();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $success ? 'Payment Successful' : 'Payment Failed'; ?> — <?php echo htmlspecialchars(SITE_NAME); ?></title>
    <link rel="icon" type="image/x-icon" href="https://raw.githubusercontent.com/Vigneshgbe/Subscription_Based_Blog/refs/heads/main/assets/Logo.png">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=DM+Sans:wght@400;500;600;700&family=Cinzel:wght@600&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --navy: #0d0b2e; --navy-mid: #13114a;
            --purple: #6B3FA0; --purple-light: #9d6fe8;
            --gold: #C9952A; --gold-bright: #F0B429;
            --white: #ffffff; --text: #1a1830;
            --text-mid: #4a4570; --text-light: #7a75a0;
            --bg-tinted: #faf9ff; --border: #e4dfff;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            background: linear-gradient(135deg, var(--navy) 0%, var(--navy-mid) 50%, #1e1b5e 100%);
            padding: 20px;
            -webkit-font-smoothing: antialiased;
        }

        body::before {
            content: '';
            position: fixed; inset: 0;
            background:
                radial-gradient(ellipse at 15% 50%, rgba(107,63,160,0.2) 0%, transparent 55%),
                radial-gradient(ellipse at 85% 20%, rgba(201,149,42,0.12) 0%, transparent 50%);
            pointer-events: none;
        }

        .card {
            background: var(--white);
            width: 100%; max-width: 480px;
            border-radius: 20px;
            padding: 36px 32px 32px;
            box-shadow: 0 20px 60px rgba(13,11,46,0.4);
            position: relative; z-index: 1;
            border: 1px solid rgba(201,149,42,0.1);
        }

        .card::before {
            content: '';
            position: absolute; top: 0; left: 0; right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--gold), var(--purple), var(--gold));
            border-radius: 20px 20px 0 0;
        }

        /* Icon */
        .icon-wrap { text-align: center; margin-bottom: 16px; }
        .icon-circle {
            width: 72px; height: 72px; border-radius: 50%;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 32px; font-weight: 900;
        }
        .icon-circle.success {
            background: linear-gradient(135deg, var(--gold), var(--gold-bright));
            color: var(--navy);
            box-shadow: 0 8px 24px rgba(201,149,42,0.3);
            animation: pop .5s cubic-bezier(.34,1.56,.64,1) forwards;
        }
        .icon-circle.error {
            background: linear-gradient(135deg, #fee2e2, #fecaca);
            color: #991b1b;
        }
        @keyframes pop {
            0%{transform:scale(.5);opacity:0}
            70%{transform:scale(1.1)}
            100%{transform:scale(1);opacity:1}
        }

        /* Badge */
        .plan-badge {
            display: inline-flex; align-items: center; gap: 5px;
            background: linear-gradient(135deg, var(--gold), var(--gold-bright));
            color: var(--navy); font-size: 10px; font-weight: 700;
            padding: 4px 14px; border-radius: 100px; margin-bottom: 12px;
            text-transform: uppercase; letter-spacing: 1px;
            font-family: 'Cinzel', serif;
        }

        h1 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 26px; font-weight: 700;
            color: var(--navy); margin-bottom: 6px; text-align: center;
        }

        .subtitle {
            font-size: 13px; color: var(--text-mid);
            line-height: 1.6; margin-bottom: 18px; text-align: center;
        }

        /* Details box */
        .details-box {
            background: var(--bg-tinted);
            border: 1px solid var(--border);
            border-radius: 12px; padding: 14px 18px;
            margin-bottom: 16px;
        }
        .details-row {
            display: flex; justify-content: space-between; align-items: center;
            padding: 7px 0; border-bottom: 1px solid var(--border);
            font-size: 13px;
        }
        .details-row:last-child { border-bottom: none; padding-bottom: 0; }
        .details-row .label { color: var(--text-light); font-weight: 500; }
        .details-row .value { color: var(--text); font-weight: 700; }
        .value.green { color: var(--purple); }

        /* Unlocked */
        .unlocked-section {
            background: linear-gradient(135deg, rgba(201,149,42,0.07), rgba(201,149,42,0.03));
            border: 1px solid rgba(201,149,42,0.2);
            border-radius: 12px; padding: 14px 16px; margin-bottom: 18px;
        }
        .unlocked-title {
            font-size: 10px; font-weight: 700; text-transform: uppercase;
            letter-spacing: 1px; color: var(--gold); margin-bottom: 8px;
            font-family: 'Cinzel', serif;
        }
        .unlocked-item {
            display: flex; align-items: center; gap: 7px;
            font-size: 12px; color: var(--text-mid); padding: 3px 0;
        }
        .unlocked-item::before { content: '✓'; font-weight: 900; color: var(--gold); font-size: 12px; }

        /* Buttons */
        .btn {
            display: block; width: 100%; padding: 13px 20px;
            border-radius: 10px; font-family: 'DM Sans', sans-serif;
            font-size: 14px; font-weight: 700; text-decoration: none;
            text-align: center; transition: all .2s;
            margin-bottom: 8px; border: none; cursor: pointer;
        }
        .btn:last-child { margin-bottom: 0; }
        .btn-primary {
            background: linear-gradient(135deg, var(--gold), var(--gold-bright));
            color: var(--navy); box-shadow: 0 4px 14px rgba(201,149,42,0.3);
        }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(201,149,42,0.4); }
        .btn-outline {
            background: transparent; border: 2px solid var(--border); color: var(--text-mid);
        }
        .btn-outline:hover { border-color: var(--purple); color: var(--purple); }
    </style>
</head>
<body>
<div class="card">

    <?php if ($success): ?>
        <div class="icon-wrap"><div class="icon-circle success">✓</div></div>
        <div style="text-align:center;">
            <div class="plan-badge">✦ <?php echo ucfirst($planType); ?> Member</div>
        </div>
        <h1>You're all set!</h1>
        <p class="subtitle">Payment successful. Your subscription is now active.</p>

        <div class="details-box">
            <div class="details-row">
                <span class="label">Plan</span>
                <span class="value"><?php echo ucfirst($planType); ?> Subscription</span>
            </div>
            <div class="details-row">
                <span class="label">Amount Paid</span>
                <span class="value"><?php echo $currency; ?> <?php echo number_format($amount, 2); ?></span>
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
            <div class="unlocked-title">🔓 Now Unlocked</div>
            <div class="unlocked-item">Unlimited access to all premium articles</div>
            <div class="unlocked-item">Ad-free reading experience</div>
            <div class="unlocked-item">Exclusive member-only content</div>
            <div class="unlocked-item">Early access to new articles</div>
        </div>

        <a href="index.php" class="btn btn-primary">Start Reading Premium Articles →</a>
        <a href="my-account.php" class="btn btn-outline">View My Account</a>

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