<?php
require_once 'config.php';

$flash = getFlashMessage();
$hasSubscription = isLoggedIn() ? hasActiveSubscription($_SESSION['user_id']) : false;
$freeRemaining = getFreeArticlesRemaining();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - <?php echo SITE_NAME; ?></title>
    <meta name="description" content="Learn about our mission to deliver premium insights and analysis">
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
        
        .logo:hover {
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

        .btn-royal {
            background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
            color: var(--white);
            box-shadow: var(--shadow-navy);
            padding: 14px 32px;
            font-size: 16px;
        }

        .btn-royal:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-purple);
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

        .mobile-nav .logo {
            flex-direction: row;
            gap: 10px;
        }

        .mobile-nav .logo-image {
            height: 40px;
        }
        
        /* ─── HERO ────────────────────────────────────────── */
        .hero {
            background: linear-gradient(135deg, var(--navy) 0%, var(--navy-mid) 45%, var(--navy-light) 100%);
            color: var(--white);
            padding: 100px 0;
            margin-bottom: 80px;
            position: relative;
            overflow: hidden;
        }
        
        .hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(ellipse at 20% 50%, rgba(107,63,160,0.25) 0%, transparent 55%),
                radial-gradient(ellipse at 80% 80%, rgba(201,149,42,0.15) 0%, transparent 50%);
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
            text-align: center;
            margin: 0 auto;
        }
        
        .hero h1 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 68px;
            font-weight: 600;
            line-height: 1.1;
            margin-bottom: 24px;
            letter-spacing: -1px;
        }
        
        .hero p {
            font-size: 20px;
            opacity: 0.85;
            line-height: 1.6;
            font-weight: 300;
        }

        /* ─── ABOUT SECTIONS ──────────────────────────────── */
        .about-section {
            padding: 80px 0;
        }

        .about-section:nth-child(even) {
            background: var(--bg-tinted);
        }

        .section-header {
            text-align: center;
            max-width: 800px;
            margin: 0 auto 64px;
        }

        .section-header h2 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 48px;
            font-weight: 600;
            margin-bottom: 20px;
            color: var(--navy);
            letter-spacing: -0.5px;
        }

        .section-header p {
            font-size: 18px;
            color: var(--text-light);
            line-height: 1.8;
            font-weight: 300;
        }

        /* ─── STORY CONTENT ───────────────────────────────── */
        .story-content {
            max-width: 900px;
            margin: 0 auto;
        }

        .story-content p {
            font-size: 17px;
            line-height: 1.9;
            color: var(--text-mid);
            margin-bottom: 28px;
            font-weight: 300;
        }

        .story-content p:first-of-type::first-letter {
            font-size: 82px;
            font-weight: 600;
            float: left;
            line-height: 0.85;
            margin: 8px 18px 0 0;
            font-family: 'Cormorant Garamond', serif;
            background: linear-gradient(135deg, var(--gold) 0%, var(--gold-bright) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* ─── VALUES GRID ─────────────────────────────────── */
        .values-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 32px;
            margin-top: 48px;
        }

        .value-card {
            background: var(--white);
            padding: 42px;
            border-radius: 14px;
            border: 1px solid var(--border-light);
            transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .value-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--gold), var(--purple));
            opacity: 0;
            transition: opacity 0.3s;
        }

        .value-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-lg);
            border-color: rgba(107,63,160,0.3);
        }

        .value-card:hover::before {
            opacity: 1;
        }

        .value-icon {
            font-size: 52px;
            margin-bottom: 20px;
            display: block;
            filter: grayscale(0.2);
        }

        .value-card h3 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 26px;
            font-weight: 600;
            margin-bottom: 14px;
            color: var(--navy);
            letter-spacing: -0.3px;
        }

        .value-card p {
            color: var(--text-mid);
            line-height: 1.7;
            font-size: 15px;
            font-weight: 300;
        }

        /* ─── STATS SECTION ───────────────────────────────── */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 48px;
            text-align: center;
        }

        .stat-item {
            padding: 32px;
            position: relative;
        }

        .stat-item::after {
            content: '✦';
            position: absolute;
            top: -10px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 20px;
            color: var(--gold);
            opacity: 0.3;
        }

        .stat-number {
            font-family: 'Cormorant Garamond', serif;
            font-size: 64px;
            font-weight: 700;
            background: linear-gradient(135deg, var(--gold) 0%, var(--gold-bright) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 12px;
            display: block;
            line-height: 1;
        }

        .stat-label {
            font-size: 14px;
            color: var(--text-light);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 2px;
            font-family: 'Cinzel', serif;
            font-size: 11px;
        }

        /* ─── TEAM SECTION ────────────────────────────────── */
        .team-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 40px;
            margin-top: 48px;
        }

        .team-card {
            text-align: center;
            background: var(--white);
            padding: 48px 40px 40px;
            border-radius: 14px;
            border: 1px solid var(--border-light);
            transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        }

        .team-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-lg);
            border-color: rgba(107,63,160,0.3);
        }

        .team-avatar {
            width: 130px;
            height: 130px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 52px;
            font-weight: 700;
            background: linear-gradient(135deg, var(--gold) 0%, var(--gold-bright) 100%);
            color: var(--navy);
            margin: 0 auto 28px;
            font-family: 'Cormorant Garamond', serif;
            box-shadow: var(--shadow-gold);
            border: 3px solid var(--white);
            position: relative;
        }

        .team-avatar::after {
            content: '';
            position: absolute;
            inset: -6px;
            border-radius: 50%;
            border: 1px solid var(--border);
        }

        .team-card h3 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 10px;
            color: var(--navy);
            letter-spacing: -0.3px;
        }

        .team-role {
            color: var(--purple);
            font-weight: 600;
            font-size: 12px;
            margin-bottom: 18px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            font-family: 'Cinzel', serif;
        }

        .team-bio {
            color: var(--text-mid);
            line-height: 1.7;
            font-size: 15px;
            font-weight: 300;
        }

        /* ─── CTA SECTION ─────────────────────────────────── */
        .cta-section {
            background: linear-gradient(135deg, var(--navy) 0%, var(--navy-mid) 50%, var(--navy-light) 100%);
            color: var(--white);
            padding: 88px 0;
            text-align: center;
            margin-top: 80px;
            position: relative;
            overflow: hidden;
        }

        .cta-section::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(ellipse at 50% 0%, rgba(201,149,42,0.12) 0%, transparent 60%);
            pointer-events: none;
        }

        .cta-section h2 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 48px;
            font-weight: 600;
            margin-bottom: 20px;
            position: relative;
            z-index: 1;
            letter-spacing: -0.5px;
        }

        .cta-section p {
            font-size: 18px;
            margin-bottom: 36px;
            opacity: 0.85;
            position: relative;
            z-index: 1;
            font-weight: 300;
        }

        .cta-section .btn {
            position: relative;
            z-index: 1;
        }
        
        /* ─── FOOTER ──────────────────────────────────────── */
        .footer {
            background: linear-gradient(160deg, var(--navy) 0%, var(--navy-mid) 60%, var(--navy-light) 100%);
            color: var(--white);
            padding: 72px 0 36px;
            margin-top: 0;
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
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: rgba(255,255,255,0.45);
            font-size: 13px;
            flex-wrap: wrap;
            gap: 12px;
            position: relative;
        }

        .footer-bottom-tagline {
            font-family: 'Cinzel', serif;
            font-size: 10px;
            letter-spacing: 3px;
            color: rgba(201,149,42,0.5);
            text-transform: uppercase;
        }
        
        /* ─── RESPONSIVE ──────────────────────────────────── */
        @media (max-width: 768px) {
            .nav {
                display: none;
            }
            
            .mobile-menu-btn {
                display: block;
            }

            .logo-image {
                height: 40px;
            }

            .logo-text {
                display: none;
            }
            
            .hero {
                padding: 60px 0;
                margin-bottom: 48px;
            }
            
            .hero h1 {
                font-size: 42px;
            }
            
            .hero p {
                font-size: 16px;
            }

            .section-header h2 {
                font-size: 36px;
            }

            .about-section {
                padding: 48px 0;
            }

            .values-grid,
            .stats-grid,
            .team-grid {
                grid-template-columns: 1fr;
                gap: 24px;
            }

            .container {
                padding: 0 16px;
            }

            .cta-section {
                padding: 56px 0;
            }

            .cta-section h2 {
                font-size: 36px;
            }

            .footer-bottom {
                justify-content: center;
                text-align: center;
            }

            .stat-number {
                font-size: 52px;
            }

            .value-card,
            .team-card {
                padding: 32px 24px;
            }
        }
        
        @media (max-width: 480px) {
            .hero h1 {
                font-size: 34px;
            }

            .section-header h2 {
                font-size: 30px;
            }

            .stat-number {
                font-size: 42px;
            }

            .cta-section h2 {
                font-size: 30px;
            }

            .hero p,
            .section-header p,
            .cta-section p {
                font-size: 15px;
            }
        }
    </style>
</head>
<body>
    <!-- Mobile Navigation -->
    <div class="mobile-nav" id="mobileNav">
        <div class="mobile-nav-header">
            <a href="index.php" class="logo">
                <img src="https://github.com/Vigneshgbe/Subscription_Based_Blog/blob/main/assets/Logo.png?raw=true" alt="<?php echo SITE_NAME; ?> Logo" class="logo-image">
                <div class="logo-text">
                    <span class="logo-name"><?php echo SITE_NAME; ?></span>
                    <span class="logo-tagline" style="color: rgba(201,149,42,0.7);">Create. Share. Inspire.</span>
                </div>
            </a>
            <button class="mobile-close" onclick="toggleMobileMenu()">×</button>
        </div>
        <nav class="mobile-nav-links">
            <a href="index.php">Home</a>
            <a href="about.php">About</a>
            <a href="contact.php">Contact</a>
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
                        <?php if ($hasSubscription): ?>
                            <span class="premium-badge">✦ Premium Member</span>
                        <?php else: ?>
                            &nbsp;·&nbsp; <?php echo $freeRemaining; ?> free articles remaining
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
                    <a href="about.php">About</a>
                    <a href="contact.php">Contact</a>
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
                <h1>About Us</h1>
                <p>Crafting premium insights for the intellectually curious since day one.</p>
            </div>
        </div>
    </div>

    <!-- Our Story -->
    <section class="about-section">
        <div class="container">
            <div class="section-header">
                <h2>Our Story</h2>
                <p>Where it all began and where we're headed</p>
            </div>
            <div class="story-content">
                <p>
                    Founded on the belief that quality content deserves to be valued, <?php echo SITE_NAME; ?> emerged from a simple observation: the internet was drowning in noise, and readers were starving for signal.
                </p>
                <p>
                    We set out to create something different—a platform where depth beats speed, insight trumps clickbait, and every piece of content earns your time and attention. Our writers aren't chasing viral moments; they're building understanding.
                </p>
                <p>
                    Today, we're proud to serve thousands of readers who share our conviction that great journalism and analysis are worth supporting. Our community doesn't just consume content—they engage with it, discuss it, and use it to make better decisions in their professional and personal lives.
                </p>
                <p>
                    We're not the biggest platform, and that's by design. We'd rather be the most trusted source for our readers than the most popular. Every article we publish is a commitment to that promise.
                </p>
            </div>
        </div>
    </section>

    <!-- Stats -->
    <section class="about-section">
        <div class="container">
            <div class="stats-grid">
                <div class="stat-item">
                    <span class="stat-number">50+</span>
                    <span class="stat-label">Active Readers</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">10+</span>
                    <span class="stat-label">Articles Published</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">95%</span>
                    <span class="stat-label">Satisfaction Rate</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">24/7</span>
                    <span class="stat-label">Access Anywhere</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Our Values -->
    <section class="about-section">
        <div class="container">
            <div class="section-header">
                <h2>Our Values</h2>
                <p>The principles that guide everything we do</p>
            </div>
            <div class="values-grid">
                <div class="value-card">
                    <span class="value-icon">🎯</span>
                    <h3>Quality Over Quantity</h3>
                    <p>We publish when we have something worth saying, not to fill a content calendar. Every piece goes through rigorous editing and fact-checking.</p>
                </div>
                <div class="value-card">
                    <span class="value-icon">🔍</span>
                    <h3>Depth & Nuance</h3>
                    <p>Complex topics deserve complex treatment. We don't oversimplify to fit a narrative—we explore multiple perspectives and acknowledge uncertainty.</p>
                </div>
                <div class="value-card">
                    <span class="value-icon">💡</span>
                    <h3>Original Thinking</h3>
                    <p>Our writers bring fresh perspectives, not just aggregated news. We aim to shape conversations, not just report on them.</p>
                </div>
                <div class="value-card">
                    <span class="value-icon">🤝</span>
                    <h3>Reader First</h3>
                    <p>Your trust is our currency. We're transparent about our methods, sources, and limitations. No hidden agendas, no sponsored content masquerading as journalism.</p>
                </div>
                <div class="value-card">
                    <span class="value-icon">🌍</span>
                    <h3>Global Perspective</h3>
                    <p>Important stories happen everywhere. We cover what matters, not just what's trending in one geography or echo chamber.</p>
                </div>
                <div class="value-card">
                    <span class="value-icon">📚</span>
                    <h3>Timeless Value</h3>
                    <p>Our articles remain relevant long after publication. We focus on analysis and frameworks that help you understand not just what happened, but why it matters.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Team -->
    <section class="about-section">
        <div class="container">
            <div class="section-header">
                <h2>Meet The Team</h2>
                <p>The people behind the insights</p>
            </div>
            <div class="team-grid">
                <div class="team-card">
                    <div class="team-avatar">AP</div>
                    <h3>Arthy Paranitharan</h3>
                    <div class="team-role">Founder & CEO</div>
                    <p class="team-bio">Former journalist Believes great stories change minds.</p>
                </div>
                <div class="team-card">
                    <div class="team-avatar">VG</div>
                    <h3>Vignesh G</h3>
                    <div class="team-role">Head of Technology</div>
                    <p class="team-bio">An Engineer turned writer. Demystifies tech for non-technical audiences.</p>
                </div>
                <div class="team-card">
                    <div class="team-avatar">AP</div>
                    <h3>Abiram P</h3>
                    <div class="team-role">Senior Business Development</div>
                    <p class="team-bio">Former strategy consultant. Makes complex economics accessible.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="cta-section">
        <div class="container">
            <h2>Ready to Join Us?</h2>
            <p>Get unlimited access to all our premium content and join a community of curious minds.</p>
            <a href="pricing.php" class="btn btn-royal">View Pricing Plans</a>
        </div>
    </section>

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
    </script>
</body>
</html>