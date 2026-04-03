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
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="icon" type="image/x-icon" href="https://png.pngtree.com/element_our/sm/20180518/sm_5aff60887f7d9.jpg">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400;1,600&family=DM+Sans:wght@300;400;500;600;700&family=Cinzel:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { 
            margin: 0; 
            padding: 0; 
            box-sizing: border-box; 
        }
        
        :root {
            --navy:        #0d0b2e;
            --navy-mid:    #13114a;
            --navy-light:  #1e1b5e;
            --purple:      #6B3FA0;
            --purple-mid:  #7C3AED;
            --purple-light:#9d6fe8;
            --gold:        #C9952A;
            --gold-bright: #F0B429;
            --gold-light:  #ffd166;
            --white:       #ffffff;
            --text:        #1a1830;
            --text-mid:    #4a4570;
            --text-light:  #7a75a0;
            --bg-tinted:   #faf9ff;
            --border:      #e4dfff;
        }
        
        body {
            font-family: 'DM Sans', sans-serif;
            min-height: 100vh;
            display: flex; 
            align-items: center; 
            justify-content: center;
            background: linear-gradient(135deg, var(--navy) 0%, var(--navy-mid) 45%, var(--navy-light) 100%);
            padding: 24px;
            -webkit-font-smoothing: antialiased;
            position: relative;
            overflow: hidden;
        }
        
        body::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(ellipse at 15% 50%, rgba(107,63,160,0.25) 0%, transparent 55%),
                radial-gradient(ellipse at 85% 20%, rgba(201,149,42,0.15) 0%, transparent 50%);
            pointer-events: none;
        }
        
        .card {
            background: var(--white); 
            max-width: 520px; 
            width: 100%;
            border-radius: 24px; 
            padding: 56px 48px 52px;
            box-shadow: 0 24px 64px rgba(13,11,46,0.35);
            text-align: center;
            position: relative;
            z-index: 1;
            border: 1px solid rgba(201,149,42,0.1);
        }
        
        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--gold), var(--purple), var(--gold));
            border-radius: 24px 24px 0 0;
        }
        
        .icon-wrap { 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            margin-bottom: 28px; 
        }
        
        .icon-circle {
            width: 96px; 
            height: 96px; 
            border-radius: 50%;
            display: flex; 
            align-items: center; 
            justify-content: center;
            font-size: 44px; 
            font-weight: 900;
        }
        
        .icon-circle.success {
            background: linear-gradient(135deg, var(--gold) 0%, var(--gold-bright) 100%);
            color: var(--navy);
            box-shadow: 0 12px 36px rgba(201,149,42,0.35);
        }
        
        .icon-circle.error { 
            background: linear-gradient(135deg, #fee2e2, #fecaca); 
            color: #991b1b;
            box-shadow: 0 12px 36px rgba(153,27,27,0.25);
        }
        
        @keyframes pop { 
            0%{transform:scale(.5);opacity:0} 
            70%{transform:scale(1.15)} 
            100%{transform:scale(1);opacity:1} 
        }
        
        .icon-circle.success { 
            animation: pop .5s cubic-bezier(.34,1.56,.64,1) forwards; 
        }
        
        h1 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 30px; 
            font-weight: 700;
            color: var(--navy); 
            margin-bottom: 10px; 
            line-height: 1.2;
        }
        
        .subtitle { 
            font-size: 16px; 
            color: var(--text-mid); 
            line-height: 1.65; 
            margin-bottom: 24px;
            font-weight: 400;
        }
        
        .plan-badge {
            display: inline-flex; 
            align-items: center; 
            gap: 6px;
            background: linear-gradient(135deg, var(--gold) 0%, var(--gold-bright) 100%);
            color: var(--navy); 
            font-size: 11px; 
            font-weight: 700;
            padding: 6px 16px; 
            border-radius: 100px; 
            margin-bottom: 22px;
            text-transform: uppercase; 
            letter-spacing: 1px;
            font-family: 'Cinzel', serif;
            box-shadow: 0 4px 16px rgba(201,149,42,0.25);
        }
        
        .details-box {
            background: var(--bg-tinted); 
            border-radius: 14px; 
            padding: 20px 24px;
            margin-bottom: 24px; 
            text-align: left; 
            border: 1px solid var(--border);
        }
        
        .details-row {
            display: flex; 
            justify-content: space-between; 
            align-items: center;
            padding: 10px 0; 
            border-bottom: 1px solid var(--border); 
            font-size: 14px;
        }
        
        .details-row:last-child { 
            border-bottom: none; 
            padding-bottom: 0; 
        }
        
        .details-row .label { 
            color: var(--text-light); 
            font-weight: 500; 
        }
        
        .details-row .value { 
            color: var(--text); 
            font-weight: 700; 
        }
        
        .value.green { 
            color: var(--purple); 
        }
        
        .unlocked-section {
            background: linear-gradient(135deg, rgba(201,149,42,0.08) 0%, rgba(201,149,42,0.04) 100%);
            border: 1px solid rgba(201,149,42,0.25); 
            border-radius: 14px;
            padding: 18px 22px; 
            margin-bottom: 28px; 
            text-align: left;
        }
        
        .unlocked-title {
            font-size: 11px; 
            font-weight: 700; 
            text-transform: uppercase;
            letter-spacing: 1px; 
            color: var(--gold); 
            margin-bottom: 10px;
            font-family: 'Cinzel', serif;
        }
        
        .unlocked-item {
            display: flex; 
            align-items: center; 
            gap: 8px;
            font-size: 13px; 
            color: var(--text-mid); 
            padding: 5px 0;
        }
        
        .unlocked-item::before { 
            content: '✓'; 
            font-weight: 900; 
            font-size: 14px; 
            flex-shrink: 0;
            color: var(--gold);
        }
        
        .btn {
            display: block; 
            width: 100%; 
            padding: 15px 24px; 
            border-radius: 12px;
            font-family: 'DM Sans', sans-serif; 
            font-size: 15px; 
            font-weight: 700;
            text-decoration: none; 
            text-align: center; 
            transition: all .25s; 
            margin-bottom: 10px;
            border: none;
            cursor: pointer;
        }
        
        .btn:last-child { 
            margin-bottom: 0; 
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--gold) 0%, var(--gold-bright) 100%);
            color: var(--navy); 
            box-shadow: 0 6px 18px rgba(201,149,42,0.35);
        }
        
        .btn-primary:hover { 
            transform: translateY(-2px); 
            box-shadow: 0 10px 28px rgba(201,149,42,0.45); 
            background: linear-gradient(135deg, var(--gold-bright) 0%, var(--gold) 100%);
        }
        
        .btn-outline { 
            background: transparent; 
            border: 2px solid var(--border); 
            color: var(--text-mid); 
        }
        
        .btn-outline:hover { 
            border-color: var(--purple); 
            color: var(--purple); 
            background: rgba(107,63,160,0.05);
        }
        
        /* Decorative sparkle */
        .sparkle {
            position: absolute;
            font-size: 20px;
            opacity: 0.3;
            animation: twinkle 3s ease-in-out infinite;
        }
        
        .sparkle-1 { top: 30px; right: 40px; animation-delay: 0s; }
        .sparkle-2 { top: 120px; left: 30px; animation-delay: 1s; font-size: 16px; }
        .sparkle-3 { bottom: 60px; right: 50px; animation-delay: 2s; font-size: 14px; }
        
        @keyframes twinkle {
            0%, 100% { opacity: 0.2; transform: scale(1); }
            50%       { opacity: 0.6; transform: scale(1.2); }
        }
        
        @media (max-width: 520px) {
            body { 
                padding: 16px; 
                align-items: flex-start; 
                padding-top: 40px; 
            }
            .card { 
                padding: 40px 24px 44px; 
                border-radius: 20px; 
            }
            h1 { 
                font-size: 24px; 
            }
            .icon-circle { 
                width: 80px; 
                height: 80px; 
                font-size: 36px; 
            }
            .sparkle { display: none; }
        }
        
        @media (max-width: 380px) {
            .card { 
                padding: 32px 18px 36px; 
            }
            h1 { 
                font-size: 21px; 
            }
        }
    </style>
</head>
<body>
    <div class="card">
        <?php if ($success): ?>
            <span class="sparkle sparkle-1" style="color: var(--gold);">✦</span>
            <span class="sparkle sparkle-2" style="color: var(--purple);">✦</span>
            <span class="sparkle sparkle-3" style="color: var(--gold);">✦</span>
            
            <div class="icon-wrap"><div class="icon-circle success">✓</div></div>
            <div class="plan-badge">✦ <?php echo ucfirst($planType); ?> Member</div>
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