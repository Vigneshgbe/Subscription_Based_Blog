<?php
require_once 'config.php';

$db = db();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cookie Policy - <?php echo SITE_NAME; ?></title>
    <meta name="description" content="Cookie Policy for <?php echo SITE_NAME; ?> - Learn about how we use cookies and similar technologies">
    <link rel="icon" type="image/x-icon" href="https://raw.githubusercontent.com/Vigneshgbe/Subscription_Based_Blog/refs/heads/main/assets/Logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400;1,600&family=DM+Sans:wght@300;400;500;600;700&family=Cinzel:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
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
            --gold-pale:   #fff8e6;
            --white:       #ffffff;
            --off-white:   #f8f6f0;
            --text:        #1a1830;
            --text-mid:    #4a4570;
            --text-light:  #7a75a0;
            --text-lighter:#a8a4c8;
            --border:      #e4dfff;
            --border-light:#f0eeff;
            --bg-tinted:   #faf9ff;
            --shadow-gold: 0 4px 24px rgba(201,149,42,0.18);
            --shadow-navy: 0 8px 32px rgba(13,11,46,0.18);
            --shadow-lg:   0 16px 48px rgba(13,11,46,0.12);
        }

        body {
            font-family: 'DM Sans', sans-serif;
            line-height: 1.6;
            color: var(--text);
            background: var(--white);
            -webkit-font-smoothing: antialiased;
        }

        /* ─── HEADER ─────────────────────────────────────── */
        .header {
            background: var(--white);
            position: sticky;
            top: 0;
            z-index: 1000;
            border-bottom: 1px solid var(--border-light);
            box-shadow: 0 2px 16px rgba(13,11,46,0.07);
        }

        .header-top {
            background: linear-gradient(90deg, var(--navy) 0%, var(--navy-mid) 50%, var(--navy-light) 100%);
            color: var(--white);
            padding: 10px 0;
            font-size: 13px;
            font-weight: 500;
            letter-spacing: 0.5px;
        }

        .header-top-content {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
        }

        .premium-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: linear-gradient(135deg, var(--gold) 0%, var(--gold-bright) 100%);
            color: var(--navy);
            padding: 3px 10px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 11px;
            letter-spacing: 0.5px;
            font-family: 'Cinzel', serif;
        }

        .container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 24px;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 18px 0;
            gap: 32px;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            transition: opacity 0.3s;
        }

        .logo-image {
            height: 48px;
            width: auto;
            display: block;
        }

        .logo-text {
            display: flex;
            flex-direction: column;
            line-height: 1;
            gap: 2px;
        }

        .logo-name {
            font-family: 'Cinzel', serif;
            font-size: 22px;
            font-weight: 700;
            letter-spacing: 3px;
            background: linear-gradient(135deg, var(--gold) 0%, var(--gold-bright) 50%, var(--gold) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-transform: uppercase;
        }

        .logo-tagline {
            font-family: 'DM Sans', sans-serif;
            font-size: 9px;
            font-weight: 500;
            letter-spacing: 3px;
            color: var(--purple);
            text-transform: uppercase;
        }

        .nav {
            display: flex;
            gap: 28px;
            align-items: center;
        }

        .nav a {
            color: var(--text-mid);
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
            letter-spacing: 0.3px;
            transition: color 0.2s;
            position: relative;
        }

        .nav a:hover { color: var(--purple); }

        .nav a::after {
            content: '';
            position: absolute;
            bottom: -4px;
            left: 0;
            width: 0;
            height: 2px;
            background: linear-gradient(90deg, var(--gold), var(--purple));
            transition: width 0.3s;
            border-radius: 2px;
        }

        .nav a:hover::after { width: 100%; }

        .btn {
            padding: 10px 24px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border-radius: 8px;
            font-family: 'DM Sans', sans-serif;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--gold) 0%, var(--gold-bright) 100%);
            color: var(--navy);
            font-weight: 700;
            box-shadow: var(--shadow-gold);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 28px rgba(201,149,42,0.35);
        }

        .btn-outline {
            background: transparent;
            border: 2px solid var(--purple);
            color: var(--purple);
        }

        .btn-outline:hover {
            background: var(--purple);
            color: var(--white);
        }

        /* ─── MOBILE MENU ─────────────────────────────────── */
        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: var(--text);
            padding: 8px;
        }

        .mobile-nav {
            display: none;
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100vh;
            background: var(--navy);
            z-index: 2000;
            padding: 24px;
            overflow-y: auto;
        }

        .mobile-nav.active { display: block; }

        .mobile-nav-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
        }

        .mobile-close {
            background: none;
            border: none;
            font-size: 32px;
            cursor: pointer;
            color: var(--gold);
        }

        .mobile-nav-links {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .mobile-nav-links a {
            color: rgba(255,255,255,0.85);
            text-decoration: none;
            font-weight: 500;
            font-size: 18px;
            padding: 16px 0;
            border-bottom: 1px solid rgba(201,149,42,0.2);
            transition: color 0.2s;
        }

        .mobile-nav-links a:hover { color: var(--gold-bright); }

        /* ─── HERO ────────────────────────────────────────── */
        .hero {
            background: linear-gradient(135deg, var(--navy) 0%, var(--navy-mid) 45%, var(--navy-light) 100%);
            color: var(--white);
            padding: 64px 0;
            margin-bottom: 48px;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(ellipse at 15% 50%, rgba(107,63,160,0.25) 0%, transparent 55%),
                radial-gradient(ellipse at 85% 20%, rgba(201,149,42,0.15) 0%, transparent 50%);
            pointer-events: none;
        }

        .hero::after {
            content: '✦';
            position: absolute;
            top: 28px;
            right: 8%;
            font-size: 24px;
            color: var(--gold);
            opacity: 0.35;
            animation: twinkle 3s ease-in-out infinite;
        }

        @keyframes twinkle {
            0%, 100% { opacity: 0.35; transform: scale(1); }
            50%       { opacity: 0.7;  transform: scale(1.2); }
        }

        .hero-content {
            position: relative;
            z-index: 1;
            max-width: 800px;
        }

        .hero-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-family: 'Cinzel', serif;
            font-size: 10px;
            letter-spacing: 4px;
            color: var(--gold-bright);
            text-transform: uppercase;
            margin-bottom: 20px;
        }

        .hero-eyebrow::before, .hero-eyebrow::after {
            content: '';
            display: block;
            width: 28px;
            height: 1px;
            background: var(--gold);
            opacity: 0.6;
        }

        .hero h1 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 52px;
            font-weight: 600;
            line-height: 1.1;
            margin-bottom: 16px;
            letter-spacing: -0.5px;
        }

        .hero p {
            font-size: 18px;
            opacity: 0.8;
            line-height: 1.6;
            font-weight: 300;
        }

        .hero .meta {
            margin-top: 20px;
            font-size: 13px;
            opacity: 0.55;
            font-weight: 400;
            font-family: 'Cinzel', serif;
            letter-spacing: 1px;
        }

        /* ─── LEGAL CONTENT ───────────────────────────────── */
        .legal-content {
            max-width: 900px;
            margin: 0 auto 80px;
            background: var(--white);
        }

        /* TOC */
        .toc {
            background: var(--bg-tinted);
            border: 1px solid var(--border-light);
            border-radius: 14px;
            padding: 36px;
            margin-bottom: 48px;
            border-left: 4px solid var(--gold);
        }

        .toc h2 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 22px;
            font-weight: 600;
            margin-bottom: 22px;
            color: var(--navy);
        }

        .toc ol {
            list-style: none;
            counter-reset: toc-counter;
            padding-left: 0;
        }

        .toc li {
            counter-increment: toc-counter;
            margin-bottom: 4px;
        }

        .toc a {
            color: var(--text-mid);
            text-decoration: none;
            font-weight: 500;
            font-size: 15px;
            display: flex;
            align-items: baseline;
            transition: color 0.2s;
            padding: 8px 0;
            border-bottom: 1px solid var(--border-light);
        }

        .toc li:last-child a { border-bottom: none; }

        .toc a::before {
            content: counter(toc-counter) ".";
            font-weight: 700;
            color: var(--gold);
            margin-right: 12px;
            min-width: 24px;
            font-family: 'Cinzel', serif;
            font-size: 12px;
        }

        .toc a:hover { color: var(--purple); }

        /* Legal sections */
        .legal-section {
            margin-bottom: 52px;
            scroll-margin-top: 100px;
        }

        .legal-section h2 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 34px;
            font-weight: 600;
            margin-bottom: 20px;
            color: var(--navy);
            padding-top: 20px;
            border-top: 1px solid var(--border-light);
            position: relative;
        }

        .legal-section h2::after {
            content: '';
            display: block;
            width: 40px;
            height: 2px;
            background: linear-gradient(90deg, var(--gold), var(--gold-bright));
            margin-top: 10px;
            border-radius: 2px;
        }

        .legal-section h2:first-of-type {
            border-top: none;
            padding-top: 0;
        }

        .legal-section h3 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 22px;
            font-weight: 600;
            margin: 32px 0 14px;
            color: var(--navy-mid);
        }

        .legal-section p {
            color: var(--text-mid);
            margin-bottom: 18px;
            font-size: 15.5px;
            line-height: 1.85;
        }

        .legal-section ul,
        .legal-section ol {
            margin-bottom: 18px;
            padding-left: 28px;
        }

        .legal-section li {
            color: var(--text-mid);
            margin-bottom: 10px;
            font-size: 15.5px;
            line-height: 1.8;
        }

        .legal-section strong {
            color: var(--navy);
            font-weight: 600;
        }

        .legal-section a[style*="color: var(--accent)"] {
            color: var(--purple) !important;
        }

        /* Inline links within legal content */
        .legal-section a {
            color: var(--purple);
            text-decoration: underline;
            transition: color 0.2s;
        }

        .legal-section a:hover { color: var(--gold); }

        /* Highlight box */
        .highlight-box {
            background: linear-gradient(135deg, rgba(201,149,42,0.07) 0%, rgba(240,180,41,0.04) 100%);
            border-left: 4px solid var(--gold);
            padding: 24px 28px;
            margin: 32px 0;
            border-radius: 0 10px 10px 0;
        }

        .highlight-box p { margin-bottom: 0; }

        /* Info box */
        .info-box {
            background: var(--bg-tinted);
            border: 1px solid var(--border-light);
            border-top: 3px solid var(--purple);
            padding: 24px 28px;
            margin: 32px 0;
            border-radius: 0 0 10px 10px;
        }

        .info-box h4 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 19px;
            font-weight: 600;
            margin-bottom: 10px;
            color: var(--navy);
        }

        /* ─── COOKIE TABLE ────────────────────────────────── */
        .cookie-table {
            width: 100%;
            border-collapse: collapse;
            margin: 24px 0;
            background: var(--white);
            border: 1px solid var(--border-light);
            border-radius: 10px;
            overflow: hidden;
        }

        .cookie-table th {
            background: linear-gradient(135deg, var(--navy) 0%, var(--navy-mid) 100%);
            padding: 16px 18px;
            text-align: left;
            font-weight: 600;
            color: var(--gold-bright);
            font-family: 'Cinzel', serif;
            font-size: 12px;
            letter-spacing: 1px;
        }

        .cookie-table td {
            padding: 15px 18px;
            border-bottom: 1px solid var(--border-light);
            color: var(--text-mid);
            vertical-align: top;
            font-size: 14.5px;
            line-height: 1.7;
        }

        .cookie-table tr:last-child td { border-bottom: none; }

        .cookie-table tr:nth-child(even) td { background: var(--bg-tinted); }

        .cookie-type {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            font-family: 'Cinzel', serif;
        }

        .cookie-type.essential {
            background: linear-gradient(135deg, rgba(201,149,42,0.15) 0%, rgba(240,180,41,0.1) 100%);
            color: var(--gold);
            border: 1px solid rgba(201,149,42,0.3);
        }

        .cookie-type.performance {
            background: linear-gradient(135deg, rgba(13,11,46,0.1) 0%, rgba(30,27,94,0.08) 100%);
            color: var(--navy-light);
            border: 1px solid rgba(13,11,46,0.15);
        }

        .cookie-type.functional {
            background: linear-gradient(135deg, rgba(107,63,160,0.12) 0%, rgba(124,58,237,0.08) 100%);
            color: var(--purple);
            border: 1px solid rgba(107,63,160,0.2);
        }

        .cookie-type.targeting {
            background: linear-gradient(135deg, rgba(157,111,232,0.12) 0%, rgba(124,58,237,0.08) 100%);
            color: var(--purple-mid);
            border: 1px solid rgba(157,111,232,0.25);
        }

        /* ─── FOOTER ──────────────────────────────────────── */
        .footer {
            background: linear-gradient(160deg, var(--navy) 0%, var(--navy-mid) 60%, var(--navy-light) 100%);
            color: var(--white);
            padding: 72px 0 36px;
            margin-top: 96px;
            position: relative;
            overflow: hidden;
        }

        .footer::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(ellipse at 80% 0%, rgba(201,149,42,0.08) 0%, transparent 55%);
            pointer-events: none;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 48px;
            margin-bottom: 52px;
            position: relative;
        }

        .footer-divider {
            width: 40px;
            height: 2px;
            background: linear-gradient(90deg, var(--gold), var(--gold-bright));
            margin: 10px 0 16px;
            border-radius: 2px;
        }

        .footer-section h3 {
            font-family: 'Cinzel', serif;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: var(--gold-bright);
            margin-bottom: 4px;
        }

        .footer-brand-name {
            font-family: 'Cormorant Garamond', serif;
            font-size: 26px;
            font-weight: 600;
            background: linear-gradient(135deg, var(--gold) 0%, var(--gold-bright) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .footer-links {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .footer a {
            color: rgba(255,255,255,0.6);
            text-decoration: none;
            font-weight: 400;
            font-size: 14px;
            transition: color 0.2s;
        }

        .footer a:hover { color: var(--gold-bright); }

        .footer-bottom {
            padding-top: 32px;
            border-top: 1px solid rgba(201,149,42,0.15);
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: rgba(255,255,255,0.4);
            font-size: 13px;
            flex-wrap: wrap;
            gap: 12px;
            position: relative;
        }

        .footer-bottom-tagline {
            font-family: 'Cinzel', serif;
            font-size: 10px;
            letter-spacing: 3px;
            color: rgba(201,149,42,0.45);
            text-transform: uppercase;
        }

        /* ─── RESPONSIVE ──────────────────────────────────── */
        @media (max-width: 768px) {
            .nav { display: none; }
            .mobile-menu-btn { display: block; }
            .hero { padding: 44px 0; }
            .hero h1 { font-size: 36px; }
            .hero p { font-size: 16px; }
            .container { padding: 0 16px; }

            .legal-section h2 { font-size: 28px; }
            .legal-section h3 { font-size: 20px; }
            .toc { padding: 22px; }

            .cookie-table { font-size: 13px; }
            .cookie-table th,
            .cookie-table td { padding: 12px 14px; }

            .footer-bottom { justify-content: center; text-align: center; }
        }

        @media (max-width: 480px) {
            .hero h1 { font-size: 30px; }
            .legal-section h2 { font-size: 24px; }
            .legal-section p,
            .legal-section li { font-size: 14.5px; }
        }
    </style>
</head>
<body>
    <!-- Mobile Navigation -->
    <div class="mobile-nav" id="mobileNav">
        <div class="mobile-nav-header">
            <div class="logo">
                <span class="logo-name"><?php echo SITE_NAME; ?></span>
                <span class="logo-tagline" style="color: rgba(201,149,42,0.7);">Create. Share. Inspire.</span>
            </div>
            <button class="mobile-close" onclick="toggleMobileMenu()">×</button>
        </div>
        <nav class="mobile-nav-links">
            <a href="index.php">Home</a>
            <?php if (isLoggedIn()): ?>
                <?php if (isAdmin()): ?>
                    <a href="admin/dashboard.php">Dashboard</a>
                <?php endif; ?>
                <a href="my-account.php">My Account</a>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="login.php">Login</a>
                <a href="register.php">Subscribe Now</a>
            <?php endif; ?>
        </nav>
    </div>

    <!-- Header -->
    <header class="header">
        <div class="header-top">
            <div class="container">
                <div class="header-top-content">
                    <?php if (isLoggedIn()): ?>
                        Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                        <?php if (hasActiveSubscription($_SESSION['user_id'])): ?>
                            <span class="premium-badge">✦ Premium Member</span>
                        <?php endif; ?>
                    <?php else: ?>
                        Unlock unlimited access &nbsp;·&nbsp; Subscribe today
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="container">
            <div class="header-content">
                <a href="index.php" class="logo">
                    <img src="https://github.com/Vigneshgbe/Subscription_Based_Blog/blob/main/assets/Logo.png?raw=true" alt="<?php echo SITE_NAME; ?> Logo" class="logo-image">
                    <div class="logo-text">
                        <span class="logo-name"><?php echo SITE_NAME; ?></span>
                        <span class="logo-tagline">Create. Share. Inspire.</span>
                    </div>
                </a>
                <nav class="nav">
                    <a href="index.php">Home</a>
                    <?php if (isLoggedIn()): ?>
                        <?php if (isAdmin()): ?>
                            <a href="admin/dashboard.php">Dashboard</a>
                        <?php endif; ?>
                        <a href="my-account.php">My Account</a>
                        <a href="logout.php">Logout</a>
                    <?php else: ?>
                        <a href="login.php">Login</a>
                        <a href="register.php" class="btn btn-primary">Subscribe</a>
                    <?php endif; ?>
                </nav>
                <button class="mobile-menu-btn" onclick="toggleMobileMenu()">☰</button>
            </div>
        </div>
    </header>

    <!-- Hero -->
    <div class="hero">
        <div class="container">
            <div class="hero-content">
                <div class="hero-eyebrow">Legal</div>
                <h1>Cookie Policy</h1>
                <p>Understanding how we use cookies and similar technologies on our platform</p>
                <div class="meta">Last Updated: <?php echo date('F d, Y'); ?></div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <main class="container">
        <div class="legal-content">
            <!-- Table of Contents -->
            <div class="toc">
                <h2>Table of Contents</h2>
                <ol>
                    <li><a href="#introduction">What Are Cookies?</a></li>
                    <li><a href="#why-we-use">Why We Use Cookies</a></li>
                    <li><a href="#types">Types of Cookies We Use</a></li>
                    <li><a href="#cookie-list">Detailed Cookie List</a></li>
                    <li><a href="#third-party">Third-Party Cookies</a></li>
                    <li><a href="#managing">Managing Your Cookie Preferences</a></li>
                    <li><a href="#browser-settings">Browser-Specific Settings</a></li>
                    <li><a href="#impact">Impact of Disabling Cookies</a></li>
                    <li><a href="#updates">Updates to This Policy</a></li>
                    <li><a href="#contact">Contact Us</a></li>
                </ol>
            </div>

            <!-- Section 1 -->
            <section id="introduction" class="legal-section">
                <h2>1. What Are Cookies?</h2>
                <p>Cookies are small text files that are placed on your device (computer, smartphone, or tablet) when you visit a website. They are widely used to make websites work more efficiently and provide information to website owners.</p>

                <div class="highlight-box">
                    <p><strong>Quick Summary:</strong> Cookies help us remember your preferences, keep you logged in, track how you use our service, and improve your experience on <?php echo SITE_NAME; ?>.</p>
                </div>

                <h3>1.1 How Cookies Work</h3>
                <p>When you visit <?php echo SITE_NAME; ?>, our server sends a cookie to your device. Your browser stores this cookie and sends it back to our server with each subsequent request, allowing us to recognize you and remember your preferences.</p>

                <h3>1.2 Types of Cookie Technologies</h3>
                <p>In addition to cookies, we may use similar technologies including:</p>
                <ul>
                    <li><strong>Web Beacons:</strong> Small graphic images (also known as pixel tags) that track user behavior</li>
                    <li><strong>Local Storage:</strong> Browser storage that retains data across sessions</li>
                    <li><strong>Session Storage:</strong> Temporary storage that clears when you close your browser</li>
                    <li><strong>Flash Cookies:</strong> Data stored via Adobe Flash (rarely used)</li>
                </ul>
            </section>

            <!-- Section 2 -->
            <section id="why-we-use" class="legal-section">
                <h2>2. Why We Use Cookies</h2>
                <p>We use cookies and similar technologies for several important purposes:</p>

                <h3>2.1 Essential Functionality</h3>
                <ul>
                    <li>Keep you logged into your account as you navigate our site</li>
                    <li>Remember your language and regional preferences</li>
                    <li>Protect against security threats and abuse</li>
                    <li>Ensure the platform functions correctly</li>
                </ul>

                <h3>2.2 Free Article Tracking</h3>
                <p>We use cookies to track how many premium articles you've accessed. This is essential for enforcing our 3-article free access limit for non-subscribers.</p>

                <h3>2.3 Performance and Analytics</h3>
                <ul>
                    <li>Understand how you use our service</li>
                    <li>Identify which articles are most popular</li>
                    <li>Measure the effectiveness of our content</li>
                    <li>Improve user experience based on usage patterns</li>
                </ul>

                <h3>2.4 Personalization</h3>
                <ul>
                    <li>Remember your reading preferences</li>
                    <li>Customize content recommendations</li>
                    <li>Maintain your filter and search settings</li>
                </ul>
            </section>

            <!-- Section 3 -->
            <section id="types" class="legal-section">
                <h2>3. Types of Cookies We Use</h2>

                <h3>3.1 Session Cookies vs Persistent Cookies</h3>

                <div class="info-box">
                    <h4>Session Cookies</h4>
                    <p>These cookies are temporary and are deleted when you close your browser. They help us maintain your session as you navigate between pages.</p>
                </div>

                <div class="info-box">
                    <h4>Persistent Cookies</h4>
                    <p>These cookies remain on your device for a set period or until you delete them. They remember your preferences for future visits.</p>
                </div>

                <h3>3.2 First-Party vs Third-Party Cookies</h3>
                <p><strong>First-Party Cookies:</strong> Set directly by <?php echo SITE_NAME; ?> and can only be read by our website.</p>
                <p><strong>Third-Party Cookies:</strong> Set by external services we use (like Stripe for payments or analytics providers). These cookies may track you across different websites.</p>

                <h3>3.3 Cookie Categories</h3>
                <table class="cookie-table">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Purpose</th>
                            <th>Can Be Disabled?</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><span class="cookie-type essential">Essential</span></td>
                            <td>Required for the website to function properly. Enable core features like security, account access, and shopping cart functionality.</td>
                            <td>No - these are necessary for the service to work</td>
                        </tr>
                        <tr>
                            <td><span class="cookie-type performance">Performance</span></td>
                            <td>Collect anonymous data about how visitors use our site. Help us improve performance and user experience.</td>
                            <td>Yes - can be disabled in browser settings</td>
                        </tr>
                        <tr>
                            <td><span class="cookie-type functional">Functional</span></td>
                            <td>Remember your preferences and choices. Provide enhanced, personalized features.</td>
                            <td>Yes - disabling may limit functionality</td>
                        </tr>
                        <tr>
                            <td><span class="cookie-type targeting">Targeting</span></td>
                            <td>Track your browsing to show relevant advertisements (if applicable). May be set by advertising partners.</td>
                            <td>Yes - can be disabled</td>
                        </tr>
                    </tbody>
                </table>
            </section>

            <!-- Section 4 -->
            <section id="cookie-list" class="legal-section">
                <h2>4. Detailed Cookie List</h2>
                <p>Here are the specific cookies we use on <?php echo SITE_NAME; ?>:</p>

                <h3>4.1 Essential Cookies</h3>
                <table class="cookie-table">
                    <thead>
                        <tr>
                            <th>Cookie Name</th>
                            <th>Purpose</th>
                            <th>Duration</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>PHPSESSID</strong></td>
                            <td>Maintains your login session and user state</td>
                            <td>Session (deleted when browser closes)</td>
                        </tr>
                        <tr>
                            <td><strong>user_token</strong></td>
                            <td>Authenticates your account and keeps you logged in</td>
                            <td>30 days</td>
                        </tr>
                        <tr>
                            <td><strong>csrf_token</strong></td>
                            <td>Protects against cross-site request forgery attacks</td>
                            <td>Session</td>
                        </tr>
                        <tr>
                            <td><strong>cookie_consent</strong></td>
                            <td>Remembers your cookie preference choices</td>
                            <td>1 year</td>
                        </tr>
                    </tbody>
                </table>

                <h3>4.2 Functional Cookies</h3>
                <table class="cookie-table">
                    <thead>
                        <tr>
                            <th>Cookie Name</th>
                            <th>Purpose</th>
                            <th>Duration</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>free_articles_count</strong></td>
                            <td>Tracks the number of free articles you've accessed</td>
                            <td>90 days</td>
                        </tr>
                        <tr>
                            <td><strong>user_preferences</strong></td>
                            <td>Stores your display preferences and settings</td>
                            <td>1 year</td>
                        </tr>
                        <tr>
                            <td><strong>reading_history</strong></td>
                            <td>Remembers articles you've read for recommendations</td>
                            <td>90 days</td>
                        </tr>
                        <tr>
                            <td><strong>filter_settings</strong></td>
                            <td>Saves your category and search filter choices</td>
                            <td>30 days</td>
                        </tr>
                    </tbody>
                </table>

                <h3>4.3 Performance Cookies</h3>
                <table class="cookie-table">
                    <thead>
                        <tr>
                            <th>Cookie Name</th>
                            <th>Purpose</th>
                            <th>Duration</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>_ga</strong></td>
                            <td>Google Analytics - distinguishes users</td>
                            <td>2 years</td>
                        </tr>
                        <tr>
                            <td><strong>_gid</strong></td>
                            <td>Google Analytics - distinguishes users</td>
                            <td>24 hours</td>
                        </tr>
                        <tr>
                            <td><strong>analytics_session</strong></td>
                            <td>Tracks your session for analytics purposes</td>
                            <td>Session</td>
                        </tr>
                    </tbody>
                </table>
            </section>

            <!-- Section 5 -->
            <section id="third-party" class="legal-section">
                <h2>5. Third-Party Cookies</h2>
                <p>We work with trusted third-party services that may set their own cookies when you use <?php echo SITE_NAME; ?>:</p>

                <h3>5.1 Stripe (Payment Processing)</h3>
                <p>When you subscribe or manage your payment information, Stripe sets cookies to:</p>
                <ul>
                    <li>Process payments securely</li>
                    <li>Prevent fraud and ensure transaction security</li>
                    <li>Remember your payment preferences</li>
                </ul>
                <p>Learn more: <a href="https://stripe.com/cookies-policy/legal" target="_blank">Stripe Cookie Policy</a></p>

                <h3>5.2 Google Analytics</h3>
                <p>We use Google Analytics to understand how our service is used:</p>
                <ul>
                    <li>Track page views and user journeys</li>
                    <li>Measure content engagement</li>
                    <li>Analyze traffic sources</li>
                    <li>Generate usage reports</li>
                </ul>
                <p>All data is anonymized. Learn more: <a href="https://policies.google.com/technologies/cookies" target="_blank">Google's Cookie Policy</a></p>

                <h3>5.3 Content Delivery Networks (CDN)</h3>
                <p>We use CDNs to deliver content faster. These services may set performance cookies to optimize content delivery.</p>
            </section>

            <!-- Section 6 -->
            <section id="managing" class="legal-section">
                <h2>6. Managing Your Cookie Preferences</h2>
                <p>You have several options to control cookies on <?php echo SITE_NAME; ?>:</p>

                <h3>6.1 Browser Settings</h3>
                <p>All modern browsers allow you to:</p>
                <ul>
                    <li>View cookies stored on your device</li>
                    <li>Delete existing cookies</li>
                    <li>Block cookies from being set</li>
                    <li>Set preferences for specific websites</li>
                </ul>

                <div class="highlight-box">
                    <p><strong>Important:</strong> Blocking or deleting cookies may affect your ability to use certain features of <?php echo SITE_NAME; ?>, including staying logged in and accessing premium content.</p>
                </div>

                <h3>6.2 Cookie Consent Management</h3>
                <p>When you first visit our site, you'll see a cookie notice allowing you to accept or customize your cookie preferences. You can change these settings at any time.</p>

                <h3>6.3 Opt-Out Tools</h3>
                <p>You can opt out of specific tracking cookies:</p>
                <ul>
                    <li><strong>Google Analytics:</strong> Install the <a href="https://tools.google.com/dlpage/gaoptout" target="_blank">Google Analytics Opt-out Browser Add-on</a></li>
                    <li><strong>Advertising cookies:</strong> Visit <a href="http://www.youronlinechoices.com/" target="_blank">Your Online Choices</a> or <a href="http://optout.aboutads.info/" target="_blank">NAI Opt-Out</a></li>
                </ul>
            </section>

            <!-- Section 7 -->
            <section id="browser-settings" class="legal-section">
                <h2>7. Browser-Specific Cookie Settings</h2>

                <h3>7.1 Google Chrome</h3>
                <ol>
                    <li>Click the three dots menu → Settings</li>
                    <li>Navigate to Privacy and security → Cookies and other site data</li>
                    <li>Choose your preferred cookie settings</li>
                    <li>To delete cookies: Click "See all cookies and site data" → Remove All</li>
                </ol>

                <h3>7.2 Mozilla Firefox</h3>
                <ol>
                    <li>Click the menu button → Settings</li>
                    <li>Select Privacy & Security</li>
                    <li>Under Cookies and Site Data, click Manage Data</li>
                    <li>Select specific cookies to remove or Remove All</li>
                </ol>

                <h3>7.3 Safari</h3>
                <ol>
                    <li>Go to Preferences → Privacy</li>
                    <li>Choose your cookie blocking preferences</li>
                    <li>Click Manage Website Data to view and remove cookies</li>
                </ol>

                <h3>7.4 Microsoft Edge</h3>
                <ol>
                    <li>Click the three dots menu → Settings</li>
                    <li>Select Cookies and site permissions</li>
                    <li>Click Manage and delete cookies and site data</li>
                    <li>Toggle settings or view and delete specific cookies</li>
                </ol>

                <div class="info-box">
                    <h4>Mobile Browsers</h4>
                    <p>Mobile browsers have similar cookie management options in their settings menus. Look for Privacy or Site Settings in your mobile browser's menu.</p>
                </div>
            </section>

            <!-- Section 8 -->
            <section id="impact" class="legal-section">
                <h2>8. Impact of Disabling Cookies</h2>
                <p>While you can choose to disable cookies, doing so may affect your experience on <?php echo SITE_NAME; ?>:</p>

                <h3>8.1 If You Disable Essential Cookies</h3>
                <ul>
                    <li>You won't be able to log into your account</li>
                    <li>Your free article count won't be tracked accurately</li>
                    <li>Security features may not function properly</li>
                    <li>The website may not work correctly</li>
                </ul>

                <h3>8.2 If You Disable Functional Cookies</h3>
                <ul>
                    <li>You'll need to log in every time you visit</li>
                    <li>Your preferences won't be remembered</li>
                    <li>Filter and search settings will reset</li>
                    <li>Content recommendations won't be personalized</li>
                </ul>

                <h3>8.3 If You Disable Performance Cookies</h3>
                <ul>
                    <li>We won't be able to improve the service based on usage data</li>
                    <li>Loading times may not be optimized</li>
                    <li>Content recommendations may be less relevant</li>
                </ul>

                <div class="highlight-box">
                    <p><strong>Recommendation:</strong> For the best experience, we recommend keeping at least Essential and Functional cookies enabled.</p>
                </div>
            </section>

            <!-- Section 9 -->
            <section id="updates" class="legal-section">
                <h2>9. Updates to This Cookie Policy</h2>

                <h3>9.1 Changes and Modifications</h3>
                <p>We may update this Cookie Policy from time to time to reflect:</p>
                <ul>
                    <li>Changes in the cookies we use</li>
                    <li>New features or services</li>
                    <li>Changes in legal requirements</li>
                    <li>Updates to our technology and practices</li>
                </ul>

                <h3>9.2 Notification of Changes</h3>
                <p>When we make significant changes to this policy, we will:</p>
                <ul>
                    <li>Update the "Last Updated" date at the top of this page</li>
                    <li>Display a notice on our website</li>
                    <li>Send an email notification for substantial changes</li>
                </ul>

                <h3>9.3 Your Continued Use</h3>
                <p>By continuing to use <?php echo SITE_NAME; ?> after changes to this policy become effective, you accept the updated Cookie Policy.</p>
            </section>

            <!-- Section 10 -->
            <section id="contact" class="legal-section">
                <h2>10. Contact Us</h2>
                <p>If you have questions about our use of cookies or this Cookie Policy, please contact us:</p>

                <div class="info-box">
                    <h4>Cookie Policy Inquiries</h4>
                    <p><strong>Email:</strong> privacy@<?php echo strtolower(str_replace(' ', '', SITE_NAME)); ?>.com</p>
                    <p><strong>Support:</strong> support@<?php echo strtolower(str_replace(' ', '', SITE_NAME)); ?>.com</p>
                    <p><strong>Website:</strong> <?php echo $_SERVER['HTTP_HOST']; ?></p>
                </div>

                <h3>10.1 Related Policies</h3>
                <p>For more information about how we handle your data, please review:</p>
                <ul>
                    <li><a href="privacy.php">Privacy Policy</a> - Our complete data protection practices</li>
                    <li><a href="terms.php">Terms of Service</a> - Terms governing your use of our service</li>
                </ul>
            </section>

            <div class="highlight-box" style="margin-top: 64px;">
                <p><strong>Your Control:</strong> You are always in control of your cookie preferences. We respect your choices and are committed to transparency about how we use cookies.</p>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <div class="footer-brand-name"><?php echo SITE_NAME; ?></div>
                    <div class="footer-divider"></div>
                    <p style="color: rgba(255,255,255,0.55); line-height: 1.7; font-size: 14px; max-width: 260px;">
                        Premium insights and analysis for those who want to stay ahead — and inspired.
                    </p>
                </div>
                <div class="footer-section">
                    <h3>Navigate</h3>
                    <div class="footer-divider"></div>
                    <div class="footer-links">
                        <a href="index.php">Home</a>
                        <a href="pricing.php">Pricing</a>
                        <?php if (isLoggedIn()): ?>
                            <a href="my-account.php">My Account</a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="footer-section">
                    <h3>Company</h3>
                    <div class="footer-divider"></div>
                    <div class="footer-links">
                        <a href="#">About</a>
                        <a href="#">Contact</a>
                        <a href="#">Careers</a>
                    </div>
                </div>
                <div class="footer-section">
                    <h3>Legal</h3>
                    <div class="footer-divider"></div>
                    <div class="footer-links">
                        <a href="terms.php">Terms of Service</a>
                        <a href="privacy.php">Privacy Policy</a>
                        <a href="cookies.php">Cookie Policy</a>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <span>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</span>
                <span class="footer-bottom-tagline">Create. Share. Inspire.</span>
            </div>
        </div>
    </footer>

    <script>
        function toggleMobileMenu() {
            const mobileNav = document.getElementById('mobileNav');
            mobileNav.classList.toggle('active');
            document.body.style.overflow = mobileNav.classList.contains('active') ? 'hidden' : '';
        }

        // Smooth scroll for TOC links
        document.querySelectorAll('.toc a').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });
    </script>
</body>
</html>