<?php
require_once 'config.php';

requireLogin();

$db = db();
$userId = $_SESSION['user_id'];

// Check current subscription
$stmt = $db->prepare("
    SELECT * FROM subscriptions 
    WHERE user_id = ? 
    ORDER BY created_at DESC 
    LIMIT 1
");
$stmt->execute([$userId]);
$currentSubscription = $stmt->fetch();

$hasActiveSubscription = hasActiveSubscription($userId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pricing - <?php echo SITE_NAME; ?></title>
    <script src="https://js.stripe.com/v3/"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;800;900&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --ink:       #0d0d12;
            --ink-soft:  #4a4a5a;
            --ink-muted: #9090a0;
            --surface:   #ffffff;
            --surface-2: #f7f7fa;
            --accent:    #5b4cdb;
            --accent-2:  #8b5cf6;
            --gold:      #f59e0b;
            --emerald:   #10b981;
            --rose:      #f43f5e;
            --border:    #e8e8f0;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
            --shadow-md: 0 4px 16px rgba(0,0,0,0.08), 0 2px 6px rgba(0,0,0,0.04);
            --shadow-lg: 0 20px 48px rgba(0,0,0,0.12), 0 8px 16px rgba(0,0,0,0.06);
            --shadow-xl: 0 32px 64px rgba(91,76,219,0.18), 0 8px 24px rgba(91,76,219,0.1);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--surface-2);
            color: var(--ink);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* ── Background ──────────────────────────────────────── */
        .page-bg {
            position: fixed;
            inset: 0;
            z-index: 0;
            background: #fafafd;
        }

        .page-bg::before {
            content: '';
            position: absolute;
            top: -200px;
            left: -200px;
            width: 700px;
            height: 700px;
            background: radial-gradient(circle, rgba(91,76,219,0.08) 0%, transparent 70%);
            pointer-events: none;
        }

        .page-bg::after {
            content: '';
            position: absolute;
            bottom: -100px;
            right: -100px;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(139,92,246,0.06) 0%, transparent 70%);
            pointer-events: none;
        }

        /* ── Layout ──────────────────────────────────────────── */
        .wrap {
            position: relative;
            z-index: 1;
            max-width: 1100px;
            margin: 0 auto;
            padding: 0 24px 80px;
        }

        /* ── Nav bar ─────────────────────────────────────────── */
        .topnav {
            display: flex;
            align-items: center;
            padding: 24px 0 0;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--ink-soft);
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            padding: 8px 16px;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            background: var(--surface);
            transition: all 0.2s;
            box-shadow: var(--shadow-sm);
        }

        .back-link:hover {
            border-color: var(--accent);
            color: var(--accent);
            box-shadow: 0 0 0 3px rgba(91,76,219,0.08);
        }

        /* ── Hero ────────────────────────────────────────────── */
        .hero {
            text-align: center;
            padding: 64px 0 56px;
        }

        .hero-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(91,76,219,0.08);
            color: var(--accent);
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            padding: 6px 16px;
            border-radius: 100px;
            border: 1px solid rgba(91,76,219,0.15);
            margin-bottom: 24px;
        }

        .hero h1 {
            font-family: 'Playfair Display', serif;
            font-size: clamp(40px, 6vw, 68px);
            font-weight: 900;
            color: var(--ink);
            line-height: 1.05;
            letter-spacing: -1.5px;
            margin-bottom: 20px;
        }

        .hero h1 em {
            font-style: normal;
            background: linear-gradient(135deg, var(--accent), var(--accent-2));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero p {
            font-size: 18px;
            color: var(--ink-soft);
            max-width: 480px;
            margin: 0 auto;
            line-height: 1.6;
            font-weight: 400;
        }

        /* ── Active subscription banner ──────────────────────── */
        .active-banner {
            display: flex;
            align-items: center;
            gap: 14px;
            background: linear-gradient(135deg, #ecfdf5, #d1fae5);
            border: 1.5px solid #6ee7b7;
            border-radius: 14px;
            padding: 18px 24px;
            margin-bottom: 40px;
            box-shadow: var(--shadow-sm);
        }

        .active-banner-icon {
            width: 40px;
            height: 40px;
            background: var(--emerald);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }

        .active-banner strong {
            display: block;
            color: #065f46;
            font-size: 15px;
            font-weight: 700;
        }

        .active-banner span {
            color: #047857;
            font-size: 13px;
        }

        /* ── Pricing grid ────────────────────────────────────── */
        .pricing-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 24px;
            align-items: start;
            margin-bottom: 80px;
        }

        @media (max-width: 900px) {
            .pricing-grid { grid-template-columns: 1fr; max-width: 440px; margin-left: auto; margin-right: auto; }
        }

        /* ── Card base ───────────────────────────────────────── */
        .plan-card {
            background: var(--surface);
            border: 1.5px solid var(--border);
            border-radius: 20px;
            padding: 36px 32px;
            position: relative;
            transition: all 0.3s cubic-bezier(0.34,1.56,0.64,1);
            box-shadow: var(--shadow-md);
        }

        .plan-card:hover {
            transform: translateY(-6px);
            box-shadow: var(--shadow-lg);
        }

        /* ── Featured card ───────────────────────────────────── */
        .plan-card.featured {
            background: linear-gradient(160deg, #1a1033 0%, #2d1f5e 50%, #1e1040 100%);
            border-color: rgba(139,92,246,0.4);
            box-shadow: var(--shadow-xl);
            transform: scale(1.03);
        }

        .plan-card.featured:hover {
            transform: scale(1.03) translateY(-6px);
        }

        .plan-card.featured .plan-name,
        .plan-card.featured .plan-price,
        .plan-card.featured .plan-duration,
        .plan-card.featured .feature-list li {
            color: rgba(255,255,255,0.9);
        }

        .plan-card.featured .feature-list li {
            border-color: rgba(255,255,255,0.08);
            color: rgba(255,255,255,0.8);
        }

        .plan-card.featured .feature-list li::before {
            color: #a78bfa;
        }

        .plan-card.featured .plan-duration {
            color: rgba(255,255,255,0.55);
        }

        /* ── Pill badge ──────────────────────────────────────── */
        .pill-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 1px;
            text-transform: uppercase;
            padding: 5px 14px;
            border-radius: 100px;
            margin-bottom: 24px;
        }

        .pill-popular {
            background: linear-gradient(135deg, #f59e0b, #fbbf24);
            color: #78350f;
        }

        .pill-save {
            background: linear-gradient(135deg, #10b981, #34d399);
            color: #064e3b;
        }

        .pill-free {
            background: var(--surface-2);
            color: var(--ink-muted);
            border: 1.5px solid var(--border);
        }

        /* ── Plan info ───────────────────────────────────────── */
        .plan-name {
            font-family: 'Playfair Display', serif;
            font-size: 26px;
            font-weight: 800;
            color: var(--ink);
            margin-bottom: 16px;
        }

        .plan-price {
            font-family: 'Playfair Display', serif;
            font-size: 56px;
            font-weight: 900;
            color: var(--accent);
            line-height: 1;
            letter-spacing: -2px;
            margin-bottom: 6px;
        }

        .plan-card.featured .plan-price {
            color: #a78bfa;
        }

        .plan-price .currency {
            font-size: 28px;
            vertical-align: top;
            margin-top: 10px;
            display: inline-block;
            letter-spacing: 0;
        }

        .plan-duration {
            font-size: 14px;
            color: var(--ink-muted);
            margin-bottom: 28px;
            font-weight: 500;
        }

        /* ── Divider ─────────────────────────────────────────── */
        .plan-divider {
            height: 1px;
            background: var(--border);
            margin-bottom: 24px;
        }

        .plan-card.featured .plan-divider {
            background: rgba(255,255,255,0.1);
        }

        /* ── Feature list ────────────────────────────────────── */
        .feature-list {
            list-style: none;
            margin-bottom: 32px;
        }

        .feature-list li {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 10px 0;
            border-bottom: 1px solid var(--border);
            font-size: 14px;
            color: var(--ink-soft);
            font-weight: 500;
            line-height: 1.4;
        }

        .feature-list li:last-child {
            border-bottom: none;
        }

        .feature-list li::before {
            content: '✓';
            color: var(--emerald);
            font-weight: 900;
            font-size: 13px;
            flex-shrink: 0;
            margin-top: 1px;
        }

        /* ── Buttons ─────────────────────────────────────────── */
        .btn-plan {
            width: 100%;
            padding: 15px 20px;
            border: none;
            border-radius: 12px;
            font-family: 'DM Sans', sans-serif;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.25s;
            letter-spacing: 0.3px;
            position: relative;
            overflow: hidden;
        }

        .btn-plan::after {
            content: '';
            position: absolute;
            inset: 0;
            background: rgba(255,255,255,0);
            transition: background 0.2s;
        }

        .btn-plan:hover::after {
            background: rgba(255,255,255,0.12);
        }

        .btn-subscribe {
            background: linear-gradient(135deg, var(--accent), var(--accent-2));
            color: white;
            box-shadow: 0 4px 14px rgba(91,76,219,0.35);
        }

        .btn-subscribe:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(91,76,219,0.45);
        }

        .btn-subscribe:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none !important;
        }

        /* Featured card uses a lighter button */
        .plan-card.featured .btn-subscribe {
            background: linear-gradient(135deg, #a78bfa, #8b5cf6);
            box-shadow: 0 4px 20px rgba(167,139,250,0.4);
        }

        .btn-active {
            background: linear-gradient(135deg, #d1fae5, #a7f3d0);
            color: #065f46;
            border: 1.5px solid #6ee7b7;
            cursor: default;
        }

        .btn-ghost {
            background: transparent;
            border: 1.5px solid var(--border);
            color: var(--ink-muted);
            cursor: default;
        }

        /* ── Payment message ─────────────────────────────────── */
        .pay-msg {
            display: none;
            margin-top: 14px;
            padding: 12px 16px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            text-align: center;
            line-height: 1.4;
        }

        .pay-msg.error {
            background: #fef2f2;
            color: #b91c1c;
            border: 1px solid #fecaca;
            display: block;
        }

        .pay-msg.success {
            background: #ecfdf5;
            color: #065f46;
            border: 1px solid #6ee7b7;
            display: block;
        }

        /* ── Why subscribe section ───────────────────────────── */
        .why-section {
            background: var(--surface);
            border: 1.5px solid var(--border);
            border-radius: 24px;
            padding: 56px 48px;
            box-shadow: var(--shadow-md);
        }

        .why-header {
            text-align: center;
            margin-bottom: 48px;
        }

        .why-header h2 {
            font-family: 'Playfair Display', serif;
            font-size: clamp(28px, 4vw, 42px);
            font-weight: 800;
            letter-spacing: -0.5px;
            color: var(--ink);
            margin-bottom: 12px;
        }

        .why-header p {
            color: var(--ink-muted);
            font-size: 16px;
        }

        .why-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 32px;
        }

        @media (max-width: 900px) {
            .why-grid { grid-template-columns: repeat(2, 1fr); gap: 24px; }
            .why-section { padding: 40px 24px; }
        }

        @media (max-width: 560px) {
            .why-grid { grid-template-columns: 1fr; }
        }

        .why-item {
            text-align: center;
        }

        .why-icon {
            width: 64px;
            height: 64px;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            margin: 0 auto 18px;
        }

        .why-icon.purple  { background: rgba(91,76,219,0.1); }
        .why-icon.amber   { background: rgba(245,158,11,0.1); }
        .why-icon.emerald { background: rgba(16,185,129,0.1); }
        .why-icon.rose    { background: rgba(244,63,94,0.1); }

        .why-item h3 {
            font-size: 16px;
            font-weight: 700;
            color: var(--ink);
            margin-bottom: 8px;
        }

        .why-item p {
            font-size: 14px;
            color: var(--ink-muted);
            line-height: 1.6;
        }

        /* ── Loading spinner ─────────────────────────────────── */
        @keyframes spin { to { transform: rotate(360deg); } }

        .spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255,255,255,0.4);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.7s linear infinite;
            vertical-align: middle;
            margin-right: 8px;
        }

        /* ── Fade-in animations ──────────────────────────────── */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(24px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .hero        { animation: fadeUp 0.5s ease both; }
        .plan-card   { animation: fadeUp 0.5s ease both; }
        .plan-card:nth-child(1) { animation-delay: 0.1s; }
        .plan-card:nth-child(2) { animation-delay: 0.2s; }
        .plan-card:nth-child(3) { animation-delay: 0.3s; }
        .why-section { animation: fadeUp 0.5s 0.35s ease both; }
    </style>
</head>
<body>
<div class="page-bg"></div>

<div class="wrap">

    <!-- Nav -->
    <nav class="topnav">
        <a href="index.php" class="back-link">← Back to Home</a>
    </nav>

    <!-- Hero -->
    <div class="hero">
        <div class="hero-eyebrow">✦ Membership Plans</div>
        <h1>Simple, <em>transparent</em><br>pricing.</h1>
        <p>Pick the plan that works for you. Cancel anytime, no questions asked.</p>
    </div>

    <?php if ($hasActiveSubscription): ?>
    <div class="active-banner">
        <div class="active-banner-icon">✓</div>
        <div>
            <strong>You have an active subscription</strong>
            <span>Thank you for your support! You have full access to all premium content.</span>
        </div>
    </div>
    <?php endif; ?>

    <!-- Pricing Cards -->
    <div class="pricing-grid">

        <!-- Free -->
        <div class="plan-card">
            <div class="pill-badge pill-free">Free tier</div>
            <div class="plan-name">Starter</div>
            <div class="plan-price"><span class="currency">₹</span>0</div>
            <div class="plan-duration">Forever free</div>
            <div class="plan-divider"></div>
            <ul class="feature-list">
                <li>3 premium articles per month</li>
                <li>Access to all free articles</li>
                <li>Weekly email newsletter</li>
                <li>Community access</li>
            </ul>
            <button class="btn-plan btn-ghost" disabled>Current Plan</button>
        </div>

        <!-- Monthly (featured) -->
        <div class="plan-card featured">
            <div class="pill-badge pill-popular">⚡ Most Popular</div>
            <div class="plan-name">Monthly</div>
            <div class="plan-price"><span class="currency">₹</span>299</div>
            <div class="plan-duration">Billed monthly · cancel anytime</div>
            <div class="plan-divider"></div>
            <ul class="feature-list">
                <li>Unlimited premium articles</li>
                <li>Ad-free reading experience</li>
                <li>Exclusive members-only content</li>
                <li>Early access to new articles</li>
                <li>Download articles as PDF</li>
                <li>Priority support</li>
            </ul>
            <?php if ($hasActiveSubscription && $currentSubscription['plan_type'] === 'monthly'): ?>
                <button class="btn-plan btn-active" disabled>✓ Your Active Plan</button>
            <?php else: ?>
                <button class="btn-plan btn-subscribe" id="btn-monthly" onclick="subscribe('monthly', this)">Subscribe Now →</button>
            <?php endif; ?>
            <div class="pay-msg" id="msg-monthly"></div>
        </div>

        <!-- Yearly -->
        <div class="plan-card">
            <div class="pill-badge pill-save">🎉 Save 30%</div>
            <div class="plan-name">Yearly</div>
            <div class="plan-price"><span class="currency">₹</span>2,499</div>
            <div class="plan-duration">₹208/month · billed annually</div>
            <div class="plan-divider"></div>
            <ul class="feature-list">
                <li>Everything in Monthly</li>
                <li>2 months completely free</li>
                <li>Exclusive yearly member perks</li>
                <li>Invitation to annual event</li>
                <li>Premium member badge</li>
                <li>Dedicated account manager</li>
            </ul>
            <?php if ($hasActiveSubscription && $currentSubscription['plan_type'] === 'yearly'): ?>
                <button class="btn-plan btn-active" disabled>✓ Your Active Plan</button>
            <?php else: ?>
                <button class="btn-plan btn-subscribe" id="btn-yearly" onclick="subscribe('yearly', this)">Subscribe Now →</button>
            <?php endif; ?>
            <div class="pay-msg" id="msg-yearly"></div>
        </div>

    </div>

    <!-- Why Subscribe -->
    <div class="why-section">
        <div class="why-header">
            <h2>Why go Premium?</h2>
            <p>Join thousands of readers who already enjoy unlimited access.</p>
        </div>
        <div class="why-grid">
            <div class="why-item">
                <div class="why-icon purple">📚</div>
                <h3>Unlimited Access</h3>
                <p>Every article, every day. No caps, no limits. New content added daily.</p>
            </div>
            <div class="why-item">
                <div class="why-icon amber">🚫</div>
                <h3>Zero Ads</h3>
                <p>Clean, distraction-free reading. Your experience, uninterrupted.</p>
            </div>
            <div class="why-item">
                <div class="why-icon emerald">⭐</div>
                <h3>Exclusive Content</h3>
                <p>Members-only deep-dives, interviews, and behind-the-scenes insights.</p>
            </div>
            <div class="why-item">
                <div class="why-icon rose">🔔</div>
                <h3>First to Know</h3>
                <p>Get early access to articles before they're available to the public.</p>
            </div>
        </div>
    </div>

</div>

<script>
    const stripe = Stripe('<?php echo STRIPE_PUBLISHABLE_KEY; ?>');

    async function subscribe(planType, btn) {
        const msgEl = document.getElementById('msg-' + planType);

        // Reset state
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner"></span>Processing…';
        msgEl.className = 'pay-msg';
        msgEl.textContent = '';

        try {
            const response = await fetch('process-payment.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    plan_type: planType,
                    csrf_token: '<?php echo generateCSRFToken(); ?>'
                })
            });

            // ── Key fix: check content-type before parsing ──
            const contentType = response.headers.get('content-type') || '';
            if (!contentType.includes('application/json')) {
                const text = await response.text();
                console.error('Server returned non-JSON:', text);
                throw new Error('Server error. Please try again or contact support.');
            }

            const data = await response.json();

            if (data.error) {
                throw new Error(data.error);
            }

            if (data.sessionId) {
                const result = await stripe.redirectToCheckout({ sessionId: data.sessionId });
                if (result.error) throw new Error(result.error.message);
            } else {
                throw new Error('Could not create checkout session. Please try again.');
            }

        } catch (err) {
            console.error('Payment error:', err);
            msgEl.className = 'pay-msg error';
            msgEl.textContent = err.message || 'Payment failed. Please try again.';
            btn.disabled = false;
            btn.textContent = 'Subscribe Now →';
        }
    }
</script>
</body>
</html>