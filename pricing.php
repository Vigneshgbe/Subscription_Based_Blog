<?php
require_once 'config.php';

requireLogin();

$db = db();
$userId = $_SESSION['user_id'];

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
    <title>Pricing — <?php echo htmlspecialchars(SITE_NAME); ?></title>
    <script src="https://js.stripe.com/v3/"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --ink:      #0d0d1a;
            --ink-2:    #3d3d55;
            --ink-3:    #8888a0;
            --surface:  #ffffff;
            --surface-2:#f7f7fb;
            --border:   #e8e8f0;
            --accent:   #5b4cf5;
            --accent-2: #7c6ff7;
            --gold:     #f5a623;
            --green:    #16a34a;
            --red:      #dc2626;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--surface-2);
            color: var(--ink);
            min-height: 100vh;
        }

        /* ── Hero ──────────────────────────────────────────────────── */
        .hero {
            background: var(--ink);
            padding: 72px 24px 120px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(ellipse 60% 80% at 20% 50%, rgba(91,76,245,.35) 0%, transparent 70%),
                radial-gradient(ellipse 50% 60% at 80% 30%, rgba(124,111,247,.2) 0%, transparent 70%);
            pointer-events: none;
        }

        .hero-back {
            position: relative;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: rgba(255,255,255,.5);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 40px;
            transition: color .2s;
        }
        .hero-back:hover { color: rgba(255,255,255,.9); }

        .hero-eyebrow {
            position: relative;
            display: inline-block;
            background: rgba(91,76,245,.2);
            border: 1px solid rgba(91,76,245,.45);
            color: #b0a8ff;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 2px;
            text-transform: uppercase;
            padding: 6px 18px;
            border-radius: 100px;
            margin-bottom: 22px;
        }

        .hero h1 {
            position: relative;
            font-family: 'Playfair Display', serif;
            font-size: clamp(38px, 6vw, 68px);
            font-weight: 900;
            color: #fff;
            line-height: 1.1;
            margin-bottom: 16px;
        }

        .hero h1 span {
            background: linear-gradient(120deg, #a89eff 0%, #f5a623 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero p {
            position: relative;
            font-size: 18px;
            color: rgba(255,255,255,.55);
            max-width: 460px;
            margin: 0 auto;
            line-height: 1.65;
        }

        /* ── Active banner ──────────────────────────────────────────── */
        .active-banner-wrap {
            max-width: 660px;
            margin: -30px auto 0;
            padding: 0 24px;
            position: relative;
            z-index: 10;
        }

        .active-banner {
            background: #fff;
            border: 2px solid var(--green);
            border-radius: 16px;
            padding: 16px 22px;
            display: flex;
            align-items: center;
            gap: 14px;
            box-shadow: 0 8px 32px rgba(22,163,74,.13);
        }

        .active-banner .chk-circle {
            width: 34px; height: 34px;
            background: var(--green);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            color: white; font-size: 16px; flex-shrink: 0;
        }

        .active-banner strong { color: var(--green); display: block; font-size: 15px; }
        .active-banner p { font-size: 13px; color: var(--ink-2); margin-top: 2px; }

        /* ── Pricing section ────────────────────────────────────────── */
        .pricing-wrap {
            max-width: 1080px;
            margin: 0 auto;
            padding: 64px 24px 80px;
        }

        .pricing-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 24px;
            align-items: start;
        }

        /* ── Card ───────────────────────────────────────────────────── */
        .card {
            background: var(--surface);
            border: 2px solid var(--border);
            border-radius: 20px;
            padding: 36px 26px 30px;
            position: relative;
            transition: border-color .25s, box-shadow .25s, transform .25s;
        }

        .card:hover {
            border-color: #c5bfff;
            box-shadow: 0 18px 48px rgba(91,76,245,.1);
            transform: translateY(-4px);
        }

        .card.featured {
            border-color: var(--accent);
            box-shadow: 0 24px 56px rgba(91,76,245,.2);
            transform: translateY(-10px);
        }

        .card.featured:hover {
            transform: translateY(-16px);
            box-shadow: 0 32px 64px rgba(91,76,245,.26);
        }

        .pill {
            position: absolute;
            top: -14px; left: 50%;
            transform: translateX(-50%);
            padding: 5px 18px;
            border-radius: 100px;
            font-size: 11px; font-weight: 700;
            letter-spacing: 1px; text-transform: uppercase;
            white-space: nowrap;
        }
        .pill-purple { background: var(--accent); color: #fff; }
        .pill-green  { background: var(--green);  color: #fff; }

        .plan-label {
            font-size: 12px; font-weight: 600;
            text-transform: uppercase; letter-spacing: 1.2px;
            color: var(--ink-3); margin-bottom: 6px;
        }

        .plan-name {
            font-family: 'Playfair Display', serif;
            font-size: 27px; font-weight: 700;
            color: var(--ink); margin-bottom: 20px;
        }

        .price-row {
            display: flex; align-items: flex-end; gap: 3px; margin-bottom: 5px;
        }

        .price-cur {
            font-size: 19px; font-weight: 600;
            color: var(--accent); line-height: 1; margin-bottom: 9px;
        }

        .price-amt {
            font-family: 'Playfair Display', serif;
            font-size: 50px; font-weight: 900;
            color: var(--ink); line-height: 1;
        }

        .price-amt.muted { color: var(--ink-3); }

        .price-period {
            font-size: 13px; color: var(--ink-3); margin-bottom: 26px;
        }

        .divider { height: 1px; background: var(--border); margin-bottom: 22px; }

        .features { list-style: none; margin-bottom: 28px; }

        .features li {
            display: flex; align-items: flex-start; gap: 10px;
            padding: 9px 0; font-size: 14px; color: var(--ink-2);
            border-bottom: 1px solid var(--border);
        }
        .features li:last-child { border-bottom: none; }

        .features li .chk {
            width: 18px; height: 18px;
            background: #eef2ff; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0; margin-top: 1px;
            font-size: 10px; color: var(--accent); font-weight: 700;
        }

        /* ── Buttons ─────────────────────────────────────────────────── */
        .btn {
            width: 100%; padding: 14px;
            border: none; border-radius: 12px;
            font-size: 15px; font-weight: 600;
            font-family: 'DM Sans', sans-serif;
            cursor: pointer; transition: all .2s;
            display: block; text-align: center;
        }

        .btn-accent {
            background: var(--accent); color: #fff;
            box-shadow: 0 4px 16px rgba(91,76,245,.32);
        }
        .btn-accent:hover:not(:disabled) {
            background: var(--accent-2);
            box-shadow: 0 8px 24px rgba(91,76,245,.44);
            transform: translateY(-2px);
        }

        .btn-ghost {
            background: transparent;
            border: 2px solid var(--border);
            color: var(--ink-3);
        }

        .btn-active {
            background: #f0fdf4;
            border: 2px solid var(--green);
            color: var(--green); font-weight: 700;
        }

        .btn:disabled { opacity: .65; cursor: not-allowed; transform: none !important; }

        /* payment message */
        .pay-msg {
            display: none; margin-top: 12px;
            padding: 11px 14px; border-radius: 10px;
            font-size: 13px; font-weight: 500; text-align: center;
        }
        .pay-msg.error {
            display: block;
            background: #fef2f2; border: 1px solid #fecaca; color: var(--red);
        }
        .pay-msg.success {
            display: block;
            background: #f0fdf4; border: 1px solid #bbf7d0; color: var(--green);
        }

        /* ── Why Subscribe ───────────────────────────────────────────── */
        .why-section {
            background: var(--ink);
            padding: 80px 24px 90px;
        }

        .why-inner { max-width: 1080px; margin: 0 auto; }

        .why-inner h2 {
            font-family: 'Playfair Display', serif;
            font-size: clamp(28px, 4vw, 42px);
            font-weight: 900; color: #fff;
            text-align: center; margin-bottom: 56px;
        }

        .why-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
            gap: 36px;
        }

        .why-item { text-align: center; }

        .why-icon {
            width: 58px; height: 58px;
            border-radius: 16px;
            background: rgba(91,76,245,.18);
            border: 1px solid rgba(91,76,245,.35);
            display: flex; align-items: center; justify-content: center;
            font-size: 25px; margin: 0 auto 18px;
        }

        .why-item h3 { font-size: 16px; font-weight: 700; color: #fff; margin-bottom: 10px; }
        .why-item p  { font-size: 14px; color: rgba(255,255,255,.48); line-height: 1.7; }

        /* ── Responsive ──────────────────────────────────────────────── */
        @media (max-width: 900px) {
            .pricing-grid {
                grid-template-columns: 1fr;
                max-width: 400px;
                margin: 0 auto;
            }
            .card.featured { transform: none; order: -1; }
            .card.featured:hover { transform: translateY(-4px); }
        }
    </style>
</head>
<body>

<!-- Hero -->
<section class="hero">
    <a href="index.php" class="hero-back">← Back to Home</a>
    <div class="hero-eyebrow">Simple, transparent pricing</div>
    <h1>Choose Your <span>Plan</span></h1>
    <p>Unlock unlimited access to premium articles, exclusive content, and an ad-free experience.</p>
</section>

<?php if ($hasActiveSubscription): ?>
<div class="active-banner-wrap">
    <div class="active-banner">
        <div class="chk-circle">✓</div>
        <div>
            <strong>You have an active subscription</strong>
            <p>Thank you for being a premium member. Your support means everything.</p>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Pricing Cards -->
<div class="pricing-wrap">
    <div class="pricing-grid">

        <!-- Free -->
        <div class="card">
            <div class="plan-label">Get started</div>
            <div class="plan-name">Free</div>
            <div class="price-row">
                <span class="price-cur">₹</span>
                <span class="price-amt muted">0</span>
            </div>
            <div class="price-period">Forever — no credit card needed</div>
            <div class="divider"></div>
            <ul class="features">
                <li><span class="chk">✓</span> 3 premium articles per month</li>
                <li><span class="chk">✓</span> Access to all free articles</li>
                <li><span class="chk">✓</span> Email newsletter</li>
                <li><span class="chk">✓</span> Community access</li>
            </ul>
            <button class="btn btn-ghost" disabled>Current Plan</button>
        </div>

        <!-- Monthly -->
        <div class="card featured">
            <span class="pill pill-purple">Most Popular</span>
            <div class="plan-label">For readers</div>
            <div class="plan-name">Monthly</div>
            <div class="price-row">
                <span class="price-cur">₹</span>
                <span class="price-amt">299</span>
            </div>
            <div class="price-period">Per month — cancel anytime</div>
            <div class="divider"></div>
            <ul class="features">
                <li><span class="chk">✓</span> Unlimited premium articles</li>
                <li><span class="chk">✓</span> Ad-free reading experience</li>
                <li><span class="chk">✓</span> Exclusive member content</li>
                <li><span class="chk">✓</span> Early access to articles</li>
                <li><span class="chk">✓</span> Download articles as PDF</li>
                <li><span class="chk">✓</span> Priority support</li>
            </ul>
            <?php if ($hasActiveSubscription && $currentSubscription['plan_type'] === 'monthly'): ?>
                <button class="btn btn-active" disabled>✓ Active Plan</button>
            <?php else: ?>
                <button class="btn btn-accent" onclick="subscribe('monthly', this)">Subscribe Now</button>
            <?php endif; ?>
            <div class="pay-msg" id="msg-monthly"></div>
        </div>

        <!-- Yearly -->
        <div class="card">
            <span class="pill pill-green">Save 30%</span>
            <div class="plan-label">Best value</div>
            <div class="plan-name">Yearly</div>
            <div class="price-row">
                <span class="price-cur">₹</span>
                <span class="price-amt">2,999</span>
            </div>
            <div class="price-period">Per year — just ₹250/month</div>
            <div class="divider"></div>
            <ul class="features">
                <li><span class="chk">✓</span> Everything in Monthly</li>
                <li><span class="chk">✓</span> 2 months completely free</li>
                <li><span class="chk">✓</span> Exclusive yearly member perks</li>
                <li><span class="chk">✓</span> Invitation to annual event</li>
                <li><span class="chk">✓</span> Premium member badge</li>
                <li><span class="chk">✓</span> Dedicated account manager</li>
            </ul>
            <?php if ($hasActiveSubscription && $currentSubscription['plan_type'] === 'yearly'): ?>
                <button class="btn btn-active" disabled>✓ Active Plan</button>
            <?php else: ?>
                <button class="btn btn-accent" onclick="subscribe('yearly', this)">Subscribe Now</button>
            <?php endif; ?>
            <div class="pay-msg" id="msg-yearly"></div>
        </div>

    </div>
</div>

<!-- Why Subscribe -->
<section class="why-section">
    <div class="why-inner">
        <h2>Why Subscribe?</h2>
        <div class="why-grid">
            <div class="why-item">
                <div class="why-icon">📚</div>
                <h3>Unlimited Access</h3>
                <p>Read every premium article without limits. Fresh content added daily by expert writers.</p>
            </div>
            <div class="why-item">
                <div class="why-icon">🚫</div>
                <h3>Ad-Free Experience</h3>
                <p>Enjoy clean, distraction-free reading. No ads, no interruptions — ever.</p>
            </div>
            <div class="why-item">
                <div class="why-icon">⭐</div>
                <h3>Exclusive Content</h3>
                <p>Members-only deep-dives, interviews, and insights you won't find anywhere else.</p>
            </div>
            <div class="why-item">
                <div class="why-icon">🔔</div>
                <h3>Early Access</h3>
                <p>Be the first to read every new article before it goes public.</p>
            </div>
        </div>
    </div>
</section>

<script>
    const stripe = Stripe('<?php echo STRIPE_PUBLISHABLE_KEY; ?>');

    async function subscribe(planType, btn) {
        const msgDiv = document.getElementById('msg-' + planType);
        const originalText = btn.textContent;

        // Reset
        btn.disabled = true;
        btn.textContent = 'Processing…';
        msgDiv.className = 'pay-msg';
        msgDiv.textContent = '';

        try {
            const response = await fetch('process-payment.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    plan_type:  planType,
                    csrf_token: '<?php echo generateCSRFToken(); ?>'
                })
            });

            // ── KEY FIX: read raw text first, then parse ─────────────
            // If PHP outputs a warning/notice before JSON, json() throws.
            // Parsing manually lets us catch it and show a clean error.
            const rawText = await response.text();
            let data;
            try {
                data = JSON.parse(rawText);
            } catch (parseErr) {
                console.error('Server returned non-JSON response:', rawText);
                throw new Error('Server error — please check your configuration or contact support.');
            }

            if (!response.ok || data.error) {
                throw new Error(data.error || 'Something went wrong. Please try again.');
            }

            if (data.sessionId) {
                const result = await stripe.redirectToCheckout({ sessionId: data.sessionId });
                if (result.error) throw new Error(result.error.message);
            } else {
                throw new Error('Failed to create checkout session.');
            }

        } catch (err) {
            console.error('Payment error:', err);
            msgDiv.className = 'pay-msg error';
            msgDiv.textContent = err.message || 'Payment failed. Please try again.';
            btn.disabled = false;
            btn.textContent = originalText;
        }
    }
</script>
</body>
</html>