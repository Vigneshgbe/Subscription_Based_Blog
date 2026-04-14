<?php
require_once 'config.php';

$flash = getFlashMessage();
$hasSubscription = isLoggedIn() ? hasActiveSubscription($_SESSION['user_id']) : false;
$freeRemaining = getFreeArticlesRemaining();

// Handle contact form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitizeInput($_POST['name'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $subject = sanitizeInput($_POST['subject'] ?? '');
    $message = sanitizeInput($_POST['message'] ?? '');
    
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        FlashMessage('Please fill in all fields', 'danger');
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        FlashMessage('Please enter a valid email address', 'danger');
    } else {
        try {
            // Insert contact message into database
            $stmt = db()->prepare("
                INSERT INTO contact_messages (name, email, subject, message, status, created_at) 
                VALUES (:name, :email, :subject, :message, 'unread', NOW())
            ");
            
            $result = $stmt->execute([
                ':name' => $name,
                ':email' => $email,
                ':subject' => $subject,
                ':message' => $message
            ]);
            
            if ($result) {
                FlashMessage('Thank you for your message! We\'ll get back to you within 24 hours.', 'success');
                
                // Optional: Send email notification to admin
                // You can uncomment and configure this section if you want email notifications
                /*
                $to = "admin@" . strtolower(str_replace(' ', '', SITE_NAME)) . ".com";
                $email_subject = "New Contact Form Submission: " . $subject;
                $email_body = "Name: $name\nEmail: $email\nSubject: $subject\n\nMessage:\n$message";
                $headers = "From: noreply@" . strtolower(str_replace(' ', '', SITE_NAME)) . ".com";
                mail($to, $email_subject, $email_body, $headers);
                */
                
                // Redirect to prevent form resubmission
                header('Location: contact.php');
                exit;
            } else {
                FlashMessage('Sorry, there was an error sending your message. Please try again.', 'danger');
            }
        } catch (PDOException $e) {
            error_log("Contact form error: " . $e->getMessage());
            FlashMessage('Sorry, there was an error sending your message. Please try again.', 'danger');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - <?php echo SITE_NAME; ?></title>
    <meta name="description" content="Get in touch with our team. We'd love to hear from you.">
    <link rel="icon" type="image/x-icon" href="https://png.pngtree.com/element_our/sm/20180518/sm_5aff60887f7d9.jpg">
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

        .btn-submit {
            width: 100%;
            padding: 14px 24px;
            font-size: 16px;
            justify-content: center;
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
            padding: 88px 0 80px;
            margin-bottom: 64px;
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
            text-align: center;
            margin: 0 auto;
        }
        
        .hero h1 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 56px;
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 24px;
            letter-spacing: -1px;
        }
        
        .hero p {
            font-size: 20px;
            opacity: 0.9;
            line-height: 1.6;
            font-weight: 300;
        }

        /* ─── CONTACT SECTION ─────────────────────────────── */
        .contact-section {
            padding: 0 0 80px;
        }

        .contact-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 64px;
            align-items: start;
        }

        /* Contact Info */
        .contact-info h2 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 36px;
            font-weight: 800;
            margin-bottom: 24px;
            color: var(--navy);
        }

        .contact-info p {
            font-size: 17px;
            color: var(--text-mid);
            line-height: 1.8;
            margin-bottom: 40px;
        }

        .info-cards {
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        .info-card {
            background: var(--bg-tinted);
            padding: 28px;
            border-radius: 14px;
            border: 1px solid var(--border-light);
            transition: all 0.3s;
        }

        .info-card:hover {
            border-color: var(--purple);
            box-shadow: var(--shadow-purple);
            transform: translateY(-2px);
        }

        .info-card-header {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 12px;
        }

        .info-icon {
            font-size: 28px;
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, var(--gold) 0%, var(--gold-bright) 100%);
            color: var(--navy);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: var(--shadow-gold);
        }

        .info-card h3 {
            font-family: 'Cinzel', serif;
            font-size: 16px;
            font-weight: 700;
            color: var(--navy);
            letter-spacing: 1px;
        }

        .info-card p {
            color: var(--text);
            margin: 0;
            font-size: 15px;
            line-height: 1.7;
        }

        .info-card a {
            color: var(--purple);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s;
        }

        .info-card a:hover {
            color: var(--purple-mid);
        }

        /* Contact Form */
        .contact-form {
            background: var(--white);
            padding: 48px;
            border-radius: 16px;
            border: 1px solid var(--border-light);
            box-shadow: var(--shadow-lg);
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            font-size: 13px;
            margin-bottom: 8px;
            color: var(--text);
            text-transform: uppercase;
            letter-spacing: 1px;
            font-family: 'Cinzel', serif;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 14px 18px;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            font-size: 15px;
            font-family: 'DM Sans', sans-serif;
            color: var(--text);
            background: var(--white);
            transition: all 0.2s;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--purple);
            box-shadow: 0 0 0 3px rgba(107,63,160,0.1);
        }

        .form-group textarea {
            min-height: 160px;
            resize: vertical;
        }

        .form-group select {
            cursor: pointer;
        }

        /* ─── ALERTS ──────────────────────────────────────── */
        .alert {
            padding: 16px 24px;
            margin-bottom: 32px;
            border-left: 4px solid;
            font-weight: 500;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .alert-success {
            background: #d1fae5;
            border-color: #10b981;
            color: #065f46;
        }
        
        .alert-danger {
            background: #fee2e2;
            border-color: #ef4444;
            color: #991b1b;
        }

        /* ─── FAQ SECTION ─────────────────────────────────── */
        .faq-section {
            background: var(--bg-tinted);
            padding: 80px 0;
            margin-top: 80px;
        }

        .faq-header {
            text-align: center;
            max-width: 700px;
            margin: 0 auto 64px;
        }

        .faq-header h2 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 42px;
            font-weight: 800;
            margin-bottom: 16px;
            color: var(--navy);
        }

        .faq-header p {
            font-size: 18px;
            color: var(--text-mid);
        }

        .faq-list {
            max-width: 900px;
            margin: 0 auto;
        }

        .faq-item {
            background: var(--white);
            padding: 32px;
            border-radius: 14px;
            margin-bottom: 20px;
            border: 1px solid var(--border-light);
            transition: all 0.3s;
        }

        .faq-item:hover {
            box-shadow: var(--shadow-lg);
            border-color: var(--border);
        }

        .faq-question {
            font-family: 'Cormorant Garamond', serif;
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 12px;
            color: var(--navy);
        }

        .faq-answer {
            color: var(--text-mid);
            line-height: 1.8;
            font-size: 16px;
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
            text-align: center;
            color: rgba(255,255,255,0.45);
            font-size: 13px;
        }
        
        /* ─── RESPONSIVE ──────────────────────────────────── */
        @media (max-width: 1024px) {
            .contact-grid {
                grid-template-columns: 1fr;
                gap: 48px;
            }
        }

        @media (max-width: 768px) {
            .nav {
                display: none;
            }
            
            .mobile-menu-btn {
                display: block;
            }
            
            .hero {
                padding: 60px 0;
            }
            
            .hero h1 {
                font-size: 40px;
            }
            
            .hero p {
                font-size: 16px;
            }

            .contact-form {
                padding: 32px 24px;
            }

            .contact-info h2 {
                font-size: 28px;
            }

            .faq-header h2 {
                font-size: 32px;
            }
            
            .container {
                padding: 0 16px;
            }
        }
        
        @media (max-width: 480px) {
            .hero h1 {
                font-size: 32px;
            }

            .contact-info h2 {
                font-size: 24px;
            }

            .faq-question {
                font-size: 18px;
            }

            .logo-name {
                font-size: 18px;
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
                <h1>Get In Touch</h1>
                <p>We'd love to hear from you. Drop us a line and we'll get back to you soon.</p>
            </div>
        </div>
    </div>

    <!-- Contact Section -->
    <section class="contact-section">
        <div class="container">
            <?php if ($flash): ?>
                <div class="alert alert-<?php echo $flash['type']; ?>">
                    <?php echo htmlspecialchars($flash['message']); ?>
                </div>
            <?php endif; ?>

            <div class="contact-grid">
                <!-- Contact Info -->
                <div class="contact-info">
                    <h2>Let's Start a Conversation</h2>
                    <p>Whether you have a question about our content, subscriptions, partnerships, or just want to say hello—we're here to help.</p>

                    <div class="info-cards">
                        <div class="info-card">
                            <div class="info-card-header">
                                <div class="info-icon">✉️</div>
                                <h3>Email Us</h3>
                            </div>
                            <p><a href="mailto:hello@<?php echo strtolower(str_replace(' ', '', SITE_NAME)); ?>.com">hello@<?php echo strtolower(str_replace(' ', '', SITE_NAME)); ?>.com</a></p>
                            <p style="margin-top: 8px; font-size: 14px; color: var(--text-lighter);">We typically respond within 24 hours</p>
                        </div>

                        <div class="info-card">
                            <div class="info-card-header">
                                <div class="info-icon">📍</div>
                                <h3>Office</h3>
                            </div>
                            <p>123 Innovation Street<br>Tech District, San Francisco<br>CA 94105, United States</p>
                        </div>
                    </div>
                </div>

                <!-- Contact Form -->
                <div class="contact-form">
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="name">Your Name *</label>
                            <input type="text" id="name" name="name" required placeholder="John Doe">
                        </div>

                        <div class="form-group">
                            <label for="email">Email Address *</label>
                            <input type="email" id="email" name="email" required placeholder="john@example.com">
                        </div>

                        <div class="form-group">
                            <label for="subject">Subject *</label>
                            <select id="subject" name="subject" required>
                                <option value="">Select a topic...</option>
                                <option value="General Inquiry">General Inquiry</option>
                                <option value="Subscription Help">Subscription Help</option>
                                <option value="Technical Support">Technical Support</option>
                                <option value="Content Feedback">Content Feedback</option>
                                <option value="Partnership">Partnership Opportunity</option>
                                <option value="Press">Press Inquiry</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="message">Message *</label>
                            <textarea id="message" name="message" required placeholder="Tell us what's on your mind..."></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary btn-submit">Send Message</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="faq-section">
        <div class="container">
            <div class="faq-header">
                <h2>Frequently Asked Questions</h2>
                <p>Quick answers to common questions</p>
            </div>

            <div class="faq-list">
                <div class="faq-item">
                    <div class="faq-question">How quickly will I receive a response?</div>
                    <div class="faq-answer">We aim to respond to all inquiries within 24 hours during business days. For urgent subscription or technical issues, we often respond much faster.</div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">Can I schedule a call with your team?</div>
                    <div class="faq-answer">For partnership inquiries or enterprise subscriptions, absolutely! Send us a message through the form above and we'll arrange a convenient time to chat.</div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">Do you accept guest contributions?</div>
                    <div class="faq-answer">We occasionally publish guest articles from industry experts. If you have a compelling pitch, please email it to hello@<?php echo strtolower(str_replace(' ', '', SITE_NAME)); ?>.com with "Guest Post" in the subject line.</div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">How can I report a technical issue?</div>
                    <div class="faq-answer">Please use the contact form above and select "Technical Support" as your subject. Include details about your device, browser, and what you were trying to do when the issue occurred.</div>
                </div>

            </div>
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