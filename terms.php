<?php
require_once 'config.php';

$db = db();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms of Service - <?php echo SITE_NAME; ?></title>
    <meta name="description" content="Terms of Service for <?php echo SITE_NAME; ?> - Please read these terms carefully before using our service">
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
            --gold-pale:   #fff3cd;
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
            --shadow-purple: 0 4px 24px rgba(107,63,160,0.18);
            --shadow-navy: 0 8px 32px rgba(13,11,46,0.18);
            --shadow-lg:   0 16px 48px rgba(13,11,46,0.12);
        }
        
        body {
            font-family: 'DM Sans', sans-serif;
            line-height: 1.6;
            color: var(--text);
            background: var(--white);
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
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
            flex-direction: column;
            line-height: 1;
            text-decoration: none;
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
        
        .logo:hover .logo-name {
            opacity: 0.85;
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
        
        .nav a:hover {
            color: var(--purple);
        }
        
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
        
        .nav a:hover::after {
            width: 100%;
        }
        
        /* ─── BUTTONS ─────────────────────────────────────── */
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
            letter-spacing: 0.3px;
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
            background: linear-gradient(135deg, var(--gold-bright) 0%, var(--gold) 100%);
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
            top: 0;
            left: 0;
            width: 100%;
            height: 100vh;
            background: var(--navy);
            z-index: 2000;
            padding: 24px;
            overflow-y: auto;
        }
        
        .mobile-nav.active {
            display: block;
        }
        
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
            letter-spacing: 0.5px;
            transition: color 0.2s;
        }
        
        .mobile-nav-links a:hover {
            color: var(--gold-bright);
        }
        
        /* ─── HERO ────────────────────────────────────────── */
        .hero {
            background: linear-gradient(135deg, var(--navy) 0%, var(--navy-mid) 45%, var(--navy-light) 100%);
            color: var(--white);
            padding: 72px 0 64px;
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
                radial-gradient(ellipse at 85% 20%, rgba(201,149,42,0.15) 0%, transparent 50%),
                radial-gradient(ellipse at 60% 90%, rgba(124,58,237,0.12) 0%, transparent 45%);
            pointer-events: none;
        }
        
        .hero::after {
            content: '✦';
            position: absolute;
            top: 32px;
            right: 10%;
            font-size: 28px;
            color: var(--gold);
            opacity: 0.4;
            animation: twinkle 3s ease-in-out infinite;
        }
        
        @keyframes twinkle {
            0%, 100% { opacity: 0.4; transform: scale(1); }
            50%       { opacity: 0.8; transform: scale(1.2); }
        }
        
        .hero-content {
            position: relative;
            z-index: 1;
            max-width: 800px;
        }
        
        .hero h1 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 48px;
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 16px;
            letter-spacing: -1px;
        }
        
        .hero p {
            font-size: 18px;
            opacity: 0.9;
            line-height: 1.6;
            font-weight: 300;
        }
        
        .hero .meta {
            margin-top: 20px;
            font-size: 14px;
            opacity: 0.7;
            font-weight: 400;
        }
        
        /* ─── LEGAL CONTENT ───────────────────────────────── */
        .legal-content {
            max-width: 900px;
            margin: 0 auto 80px;
            background: var(--white);
        }
        
        .toc {
            background: var(--bg-tinted);
            border: 1px solid var(--border-light);
            border-radius: 14px;
            padding: 32px;
            margin-bottom: 48px;
            box-shadow: var(--shadow-lg);
        }
        
        .toc h2 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 20px;
            color: var(--navy);
        }
        
        .toc ol {
            list-style: none;
            counter-reset: toc-counter;
            padding-left: 0;
        }
        
        .toc li {
            counter-increment: toc-counter;
            margin-bottom: 12px;
        }
        
        .toc a {
            color: var(--text);
            text-decoration: none;
            font-weight: 500;
            font-size: 15px;
            display: flex;
            align-items: baseline;
            transition: color 0.2s;
            padding: 8px 0;
        }
        
        .toc a::before {
            content: counter(toc-counter) ".";
            font-weight: 700;
            color: var(--gold);
            margin-right: 12px;
            min-width: 24px;
            font-family: 'Cinzel', serif;
        }
        
        .toc a:hover {
            color: var(--purple);
        }
        
        .legal-section {
            margin-bottom: 48px;
            scroll-margin-top: 100px;
        }
        
        .legal-section h2 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 20px;
            color: var(--navy);
            padding-top: 16px;
            border-top: 2px solid var(--border-light);
        }
        
        .legal-section h2:first-of-type {
            border-top: none;
            padding-top: 0;
        }
        
        .legal-section h3 {
            font-family: 'Cinzel', serif;
            font-size: 18px;
            font-weight: 600;
            margin: 32px 0 16px;
            color: var(--purple);
            letter-spacing: 0.5px;
        }
        
        .legal-section p {
            color: var(--text-mid);
            margin-bottom: 20px;
            font-size: 16px;
            line-height: 1.8;
        }
        
        .legal-section ul,
        .legal-section ol {
            margin-bottom: 20px;
            padding-left: 28px;
        }
        
        .legal-section li {
            color: var(--text-mid);
            margin-bottom: 12px;
            font-size: 16px;
            line-height: 1.8;
        }
        
        .legal-section strong {
            color: var(--text);
            font-weight: 600;
        }
        
        .highlight-box {
            background: linear-gradient(135deg, rgba(201,149,42,0.08) 0%, rgba(201,149,42,0.03) 100%);
            border-left: 4px solid var(--gold);
            padding: 24px;
            margin: 32px 0;
            border-radius: 10px;
            box-shadow: 0 2px 12px rgba(201,149,42,0.1);
        }
        
        .highlight-box p {
            margin-bottom: 0;
            color: var(--text);
        }
        
        .info-box {
            background: var(--bg-tinted);
            border: 1px solid var(--border);
            padding: 24px;
            margin: 32px 0;
            border-radius: 10px;
        }
        
        .info-box h4 {
            font-family: 'Cinzel', serif;
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 12px;
            color: var(--navy);
            letter-spacing: 0.5px;
        }
        
        .info-box p {
            color: var(--text);
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
        
        .footer-divider {
            width: 48px;
            height: 2px;
            background: linear-gradient(90deg, var(--gold), var(--gold-bright));
            margin: 12px 0 18px;
            border-radius: 2px;
        }
        
        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 48px;
            margin-bottom: 52px;
            position: relative;
        }
        
        .footer-section h3 {
            font-family: 'Cinzel', serif;
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: var(--gold-bright);
            margin-bottom: 6px;
        }
        
        .footer-brand-name {
            font-family: 'Cormorant Garamond', serif;
            font-size: 28px;
            font-weight: 600;
            background: linear-gradient(135deg, var(--gold) 0%, var(--gold-bright) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: 1px;
        }
        
        .footer-links {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        
        .footer a {
            color: rgba(255,255,255,0.65);
            text-decoration: none;
            font-weight: 400;
            font-size: 14px;
            transition: color 0.2s;
            letter-spacing: 0.2px;
        }
        
        .footer a:hover { 
            color: var(--gold-bright); 
        }
        
        .footer-bottom {
            padding-top: 32px;
            border-top: 1px solid rgba(201,149,42,0.15);
            text-align: center;
            color: rgba(255,255,255,0.45);
            font-size: 13px;
        }
        
        /* ─── RESPONSIVE ──────────────────────────────────── */
        @media (max-width: 768px) {
            .nav {
                display: none;
            }
            
            .mobile-menu-btn {
                display: block;
            }
            
            .hero {
                padding: 40px 0;
            }
            
            .hero h1 {
                font-size: 32px;
            }
            
            .hero p {
                font-size: 16px;
            }
            
            .container {
                padding: 0 16px;
            }
            
            .legal-section h2 {
                font-size: 26px;
            }
            
            .legal-section h3 {
                font-size: 16px;
            }
            
            .toc {
                padding: 20px;
            }

            .logo-name {
                font-size: 18px;
            }
        }
        
        @media (max-width: 480px) {
            .hero h1 {
                font-size: 28px;
            }
            
            .legal-section h2 {
                font-size: 24px;
            }
            
            .legal-section p,
            .legal-section li {
                font-size: 15px;
            }
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
                    <span class="logo-name"><?php echo SITE_NAME; ?></span>
                    <span class="logo-tagline">Create. Share. Inspire.</span>
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
                <h1>Terms of Service</h1>
                <p>Please read these terms carefully before using our service</p>
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
                    <li><a href="#acceptance">Acceptance of Terms</a></li>
                    <li><a href="#description">Description of Service</a></li>
                    <li><a href="#registration">Registration and Account</a></li>
                    <li><a href="#subscriptions">Subscriptions and Billing</a></li>
                    <li><a href="#content">Content and Intellectual Property</a></li>
                    <li><a href="#user-conduct">User Conduct</a></li>
                    <li><a href="#free-trial">Free Article Access</a></li>
                    <li><a href="#payment">Payment Processing</a></li>
                    <li><a href="#cancellation">Cancellation and Refunds</a></li>
                    <li><a href="#termination">Termination</a></li>
                    <li><a href="#disclaimers">Disclaimers</a></li>
                    <li><a href="#limitation">Limitation of Liability</a></li>
                    <li><a href="#changes">Changes to Terms</a></li>
                    <li><a href="#contact">Contact Information</a></li>
                </ol>
            </div>

            <!-- Section 1 -->
            <section id="acceptance" class="legal-section">
                <h2>1. Acceptance of Terms</h2>
                <p>Welcome to <?php echo SITE_NAME; ?>. By accessing or using our website and services, you agree to be bound by these Terms of Service ("Terms"). If you do not agree to these Terms, please do not use our service.</p>
                
                <div class="highlight-box">
                    <p><strong>Important:</strong> These Terms constitute a legally binding agreement between you and <?php echo SITE_NAME; ?>. Please read them carefully.</p>
                </div>
                
                <p>Your use of our service is also governed by our Privacy Policy and Cookie Policy, which are incorporated into these Terms by reference.</p>
            </section>

            <!-- Section 2 -->
            <section id="description" class="legal-section">
                <h2>2. Description of Service</h2>
                <p><?php echo SITE_NAME; ?> provides a premium content platform offering articles, insights, and analysis on various topics including technology, business, and culture.</p>
                
                <h3>2.1 Service Features</h3>
                <p>Our service includes:</p>
                <ul>
                    <li>Access to premium articles and exclusive content</li>
                    <li>Category-based content organization</li>
                    <li>Search and filtering capabilities</li>
                    <li>Personalized reading experience</li>
                    <li>Regular content updates and new publications</li>
                </ul>
                
                <h3>2.2 Service Availability</h3>
                <p>While we strive to provide uninterrupted service, we do not guarantee that our service will be available at all times. We may modify, suspend, or discontinue any aspect of our service at any time without prior notice.</p>
            </section>

            <!-- Section 3 -->
            <section id="registration" class="legal-section">
                <h2>3. Registration and Account</h2>
                
                <h3>3.1 Account Creation</h3>
                <p>To access certain features of our service, you must create an account. When creating an account, you agree to:</p>
                <ul>
                    <li>Provide accurate, current, and complete information</li>
                    <li>Maintain and update your information to keep it accurate</li>
                    <li>Maintain the security of your password and account</li>
                    <li>Accept responsibility for all activities under your account</li>
                    <li>Notify us immediately of any unauthorized use of your account</li>
                </ul>
                
                <h3>3.2 Account Eligibility</h3>
                <p>You must be at least 18 years old to create an account. By creating an account, you represent and warrant that you meet this age requirement.</p>
                
                <h3>3.3 Account Security</h3>
                <p>You are responsible for maintaining the confidentiality of your account credentials. We are not liable for any loss or damage arising from your failure to protect your account information.</p>
            </section>

            <!-- Section 4 -->
            <section id="subscriptions" class="legal-section">
                <h2>4. Subscriptions and Billing</h2>
                
                <h3>4.1 Subscription Plans</h3>
                <p><?php echo SITE_NAME; ?> offers various subscription plans that provide unlimited access to premium content. Subscription details, pricing, and features are available on our pricing page.</p>
                
                <h3>4.2 Billing Cycle</h3>
                <p>Subscriptions are billed on a recurring basis according to your selected plan (monthly or annually). Your subscription will automatically renew unless you cancel before the renewal date.</p>
                
                <h3>4.3 Price Changes</h3>
                <p>We reserve the right to change subscription prices at any time. Price changes will be communicated to you at least 30 days in advance and will apply to your next billing cycle.</p>
                
                <div class="info-box">
                    <h4>Auto-Renewal Notice</h4>
                    <p>Your subscription will automatically renew at the end of each billing period. You will be charged the then-current subscription fee unless you cancel before the renewal date.</p>
                </div>
            </section>

            <!-- Section 5 -->
            <section id="content" class="legal-section">
                <h2>5. Content and Intellectual Property</h2>
                
                <h3>5.1 Ownership</h3>
                <p>All content on <?php echo SITE_NAME; ?>, including but not limited to articles, images, graphics, logos, and software, is owned by or licensed to <?php echo SITE_NAME; ?> and is protected by copyright, trademark, and other intellectual property laws.</p>
                
                <h3>5.2 Limited License</h3>
                <p>Subject to your compliance with these Terms, we grant you a limited, non-exclusive, non-transferable, revocable license to:</p>
                <ul>
                    <li>Access and view content for personal, non-commercial use</li>
                    <li>Download and print individual articles for personal reference</li>
                </ul>
                
                <h3>5.3 Restrictions</h3>
                <p>You may not:</p>
                <ul>
                    <li>Reproduce, distribute, or publicly display our content without permission</li>
                    <li>Modify, adapt, or create derivative works from our content</li>
                    <li>Use our content for commercial purposes</li>
                    <li>Remove or alter any copyright or proprietary notices</li>
                    <li>Share your account credentials with others</li>
                    <li>Use automated systems to access or scrape our content</li>
                </ul>
            </section>

            <!-- Section 6 -->
            <section id="user-conduct" class="legal-section">
                <h2>6. User Conduct</h2>
                <p>You agree to use our service only for lawful purposes and in accordance with these Terms. You agree not to:</p>
                <ul>
                    <li>Violate any applicable laws or regulations</li>
                    <li>Infringe upon the rights of others</li>
                    <li>Transmit any harmful or malicious code</li>
                    <li>Interfere with or disrupt our service or servers</li>
                    <li>Attempt to gain unauthorized access to our systems</li>
                    <li>Engage in any fraudulent activity</li>
                    <li>Harass, abuse, or harm other users</li>
                    <li>Impersonate any person or entity</li>
                </ul>
            </section>

            <!-- Section 7 -->
            <section id="free-trial" class="legal-section">
                <h2>7. Free Article Access</h2>
                
                <h3>7.1 Free Article Limit</h3>
                <p>Non-subscribed users may access up to 3 premium articles for free. After reaching this limit, you must subscribe to continue accessing premium content.</p>
                
                <h3>7.2 Tracking</h3>
                <p>We track article views using cookies and other technologies. Attempting to circumvent the free article limit by clearing cookies, using multiple accounts, or other means is prohibited and may result in account termination.</p>
                
                <h3>7.3 Reset Policy</h3>
                <p>The free article counter may reset periodically at our discretion, but this is not guaranteed.</p>
            </section>

            <!-- Section 8 -->
            <section id="payment" class="legal-section">
                <h2>8. Payment Processing</h2>
                
                <h3>8.1 Payment Methods</h3>
                <p>We use Stripe as our payment processor. By providing payment information, you authorize us to charge your selected payment method for all fees incurred.</p>
                
                <h3>8.2 Payment Security</h3>
                <p>We do not store your complete credit card information. All payment data is securely processed and stored by Stripe in compliance with PCI-DSS standards.</p>
                
                <h3>8.3 Failed Payments</h3>
                <p>If a payment fails, we will attempt to process the payment again. If payment continues to fail, your subscription may be suspended or cancelled.</p>
                
                <div class="highlight-box">
                    <p><strong>Secure Payments:</strong> All transactions are processed securely through Stripe, a certified PCI Service Provider Level 1 - the highest level of security certification.</p>
                </div>
            </section>

            <!-- Section 9 -->
            <section id="cancellation" class="legal-section">
                <h2>9. Cancellation and Refunds</h2>
                
                <h3>9.1 Cancellation</h3>
                <p>You may cancel your subscription at any time through your account settings. Cancellation will be effective at the end of your current billing period.</p>
                
                <h3>9.2 No Refunds</h3>
                <p>Subscription fees are non-refundable except as required by law. If you cancel during a billing period, you will retain access until the end of that period, but no refund will be issued for the unused portion.</p>
                
                <h3>9.3 Exceptions</h3>
                <p>We may issue refunds on a case-by-case basis for exceptional circumstances, such as:</p>
                <ul>
                    <li>Technical issues preventing service access</li>
                    <li>Unauthorized charges</li>
                    <li>Duplicate charges due to system errors</li>
                </ul>
            </section>

            <!-- Section 10 -->
            <section id="termination" class="legal-section">
                <h2>10. Termination</h2>
                
                <h3>10.1 Termination by You</h3>
                <p>You may terminate your account at any time by contacting us or using the account deletion feature in your account settings.</p>
                
                <h3>10.2 Termination by Us</h3>
                <p>We reserve the right to suspend or terminate your account and access to our service at any time, with or without cause, including but not limited to:</p>
                <ul>
                    <li>Violation of these Terms</li>
                    <li>Fraudulent or illegal activity</li>
                    <li>Non-payment of fees</li>
                    <li>Abuse of our service or other users</li>
                </ul>
                
                <h3>10.3 Effect of Termination</h3>
                <p>Upon termination, your right to access and use our service will immediately cease. We may delete your account and all associated data.</p>
            </section>

            <!-- Section 11 -->
            <section id="disclaimers" class="legal-section">
                <h2>11. Disclaimers</h2>
                
                <div class="highlight-box">
                    <p><strong>AS IS BASIS:</strong> OUR SERVICE IS PROVIDED "AS IS" AND "AS AVAILABLE" WITHOUT WARRANTIES OF ANY KIND, EITHER EXPRESS OR IMPLIED.</p>
                </div>
                
                <p>We disclaim all warranties, including but not limited to:</p>
                <ul>
                    <li>Warranties of merchantability and fitness for a particular purpose</li>
                    <li>Warranties regarding the accuracy, reliability, or completeness of content</li>
                    <li>Warranties that the service will be uninterrupted or error-free</li>
                    <li>Warranties that defects will be corrected</li>
                </ul>
                
                <p>The content on our platform is for informational purposes only and should not be considered professional advice. You should consult with appropriate professionals before making any decisions based on our content.</p>
            </section>

            <!-- Section 12 -->
            <section id="limitation" class="legal-section">
                <h2>12. Limitation of Liability</h2>
                
                <p>TO THE MAXIMUM EXTENT PERMITTED BY LAW, <?php echo strtoupper(SITE_NAME); ?> SHALL NOT BE LIABLE FOR ANY INDIRECT, INCIDENTAL, SPECIAL, CONSEQUENTIAL, OR PUNITIVE DAMAGES, INCLUDING BUT NOT LIMITED TO:</p>
                <ul>
                    <li>Loss of profits, revenue, or data</li>
                    <li>Loss of use or interruption of business</li>
                    <li>Cost of substitute services</li>
                    <li>Personal injury or property damage</li>
                </ul>
                
                <p>Our total liability for any claims arising from or related to our service shall not exceed the amount you paid to us in the 12 months preceding the claim.</p>
                
                <p>Some jurisdictions do not allow the exclusion or limitation of certain warranties or liabilities. In such jurisdictions, our liability will be limited to the greatest extent permitted by law.</p>
            </section>

            <!-- Section 13 -->
            <section id="changes" class="legal-section">
                <h2>13. Changes to Terms</h2>
                
                <h3>13.1 Right to Modify</h3>
                <p>We reserve the right to modify these Terms at any time. We will notify you of material changes by:</p>
                <ul>
                    <li>Posting the updated Terms on our website</li>
                    <li>Sending an email to your registered email address</li>
                    <li>Displaying a notice on our platform</li>
                </ul>
                
                <h3>13.2 Effective Date</h3>
                <p>Changes will become effective immediately upon posting unless we specify a different effective date.</p>
                
                <h3>13.3 Continued Use</h3>
                <p>Your continued use of our service after changes become effective constitutes your acceptance of the revised Terms. If you do not agree to the changes, you must stop using our service and cancel your subscription.</p>
            </section>

            <!-- Section 14 -->
            <section id="contact" class="legal-section">
                <h2>14. Contact Information</h2>
                
                <p>If you have any questions about these Terms of Service, please contact us:</p>
                
                <div class="info-box">
                    <p><strong>Email:</strong> legal@<?php echo strtolower(str_replace(' ', '', SITE_NAME)); ?>.com</p>
                    <p><strong>Support:</strong> support@<?php echo strtolower(str_replace(' ', '', SITE_NAME)); ?>.com</p>
                    <p><strong>Website:</strong> <?php echo $_SERVER['HTTP_HOST']; ?></p>
                </div>
                
                <p>We will respond to your inquiries within 5-7 business days.</p>
            </section>

            <div class="highlight-box" style="margin-top: 64px;">
                <p><strong>Acknowledgment:</strong> By using <?php echo SITE_NAME; ?>, you acknowledge that you have read, understood, and agree to be bound by these Terms of Service.</p>
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
                        <a href="about.php">About</a>
                        <a href="contact.php">Contact</a>
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
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
</body>
</html>