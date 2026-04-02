<?php
require_once 'config.php';

$db = db();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy - <?php echo SITE_NAME; ?></title>
    <meta name="description" content="Privacy Policy for <?php echo SITE_NAME; ?> - Learn how we collect, use, and protect your personal information">
    <link rel="icon" type="image/x-icon" href="https://raw.githubusercontent.com/Vigneshgbe/Subscription_Based_Blog/refs/heads/main/assets/Logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Playfair+Display:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        :root {
            --primary: #0a0a0a;
            --secondary: #ffffff;
            --accent: #FF6B6B;
            --accent-dark: #E74C3C;
            --text: #1a1a1a;
            --text-light: #6b7280;
            --text-lighter: #9ca3af;
            --border: #e5e7eb;
            --border-light: #f3f4f6;
            --bg-light: #fafafa;
            --bg-lighter: #f9fafb;
            --premium: #FFD700;
            --premium-dark: #F4C430;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            line-height: 1.6;
            color: var(--text);
            background: var(--secondary);
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        
        /* Header Styles */
        .header {
            background: var(--secondary);
            position: sticky;
            top: 0;
            z-index: 1000;
            border-bottom: 1px solid var(--border);
            box-shadow: var(--shadow-sm);
        }
        
        .header-top {
            background: var(--primary);
            color: var(--secondary);
            padding: 10px 0;
            font-size: 13px;
            font-weight: 500;
            letter-spacing: 0.3px;
        }
        
        .header-top-content {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
        }
        
        .premium-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: var(--premium);
            color: var(--primary);
            padding: 2px 8px;
            border-radius: 4px;
            font-weight: 700;
            font-size: 11px;
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
            padding: 20px 0;
            gap: 32px;
        }
        
        .logo {
            font-size: 26px;
            font-weight: 900;
            letter-spacing: -0.5px;
            color: var(--primary);
            text-decoration: none;
            font-family: 'Playfair Display', serif;
            transition: opacity 0.2s;
        }
        
        .logo:hover {
            opacity: 0.8;
        }
        
        .nav {
            display: flex;
            gap: 32px;
            align-items: center;
        }
        
        .nav a {
            color: var(--text);
            text-decoration: none;
            font-weight: 500;
            font-size: 15px;
            transition: color 0.2s;
            position: relative;
        }
        
        .nav a:hover {
            color: var(--accent);
        }
        
        .nav a::after {
            content: '';
            position: absolute;
            bottom: -4px;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--accent);
            transition: width 0.3s;
        }
        
        .nav a:hover::after {
            width: 100%;
        }
        
        /* Button Styles */
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
            font-family: 'Inter', sans-serif;
        }
        
        .btn-primary {
            background: var(--accent);
            color: var(--secondary);
            box-shadow: 0 2px 8px rgba(255, 107, 107, 0.3);
        }
        
        .btn-primary:hover {
            background: var(--accent-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 107, 107, 0.4);
        }
        
        .btn-outline {
            background: transparent;
            border: 2px solid var(--primary);
            color: var(--primary);
        }
        
        .btn-outline:hover {
            background: var(--primary);
            color: var(--secondary);
        }
        
        /* Mobile Menu */
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
            background: var(--secondary);
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
            margin-bottom: 32px;
        }
        
        .mobile-close {
            background: none;
            border: none;
            font-size: 32px;
            cursor: pointer;
            color: var(--text);
        }
        
        .mobile-nav-links {
            display: flex;
            flex-direction: column;
            gap: 24px;
        }
        
        .mobile-nav-links a {
            color: var(--text);
            text-decoration: none;
            font-weight: 600;
            font-size: 18px;
            padding: 12px 0;
            border-bottom: 1px solid var(--border-light);
        }
        
        /* Hero Section */
        .hero {
            background: linear-gradient(135deg, #0a0a0a 0%, #1a1a1a 50%, #2a2a2a 100%);
            color: var(--secondary);
            padding: 64px 0;
            margin-bottom: 48px;
            position: relative;
            overflow: hidden;
        }
        
        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 20% 50%, rgba(255, 107, 107, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(255, 215, 0, 0.1) 0%, transparent 50%);
            pointer-events: none;
        }
        
        .hero-content {
            position: relative;
            z-index: 1;
            max-width: 800px;
        }
        
        .hero h1 {
            font-size: 48px;
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 16px;
            letter-spacing: -1px;
            font-family: 'Playfair Display', serif;
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
        
        /* Legal Content Styles */
        .legal-content {
            max-width: 900px;
            margin: 0 auto 80px;
            background: var(--secondary);
        }
        
        .toc {
            background: var(--bg-lighter);
            border: 1px solid var(--border-light);
            border-radius: 12px;
            padding: 32px;
            margin-bottom: 48px;
        }
        
        .toc h2 {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 20px;
            color: var(--primary);
            font-family: 'Playfair Display', serif;
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
            color: var(--accent);
            margin-right: 12px;
            min-width: 24px;
        }
        
        .toc a:hover {
            color: var(--accent);
        }
        
        .legal-section {
            margin-bottom: 48px;
            scroll-margin-top: 100px;
        }
        
        .legal-section h2 {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 20px;
            color: var(--primary);
            font-family: 'Playfair Display', serif;
            padding-top: 16px;
            border-top: 2px solid var(--border-light);
        }
        
        .legal-section h2:first-of-type {
            border-top: none;
            padding-top: 0;
        }
        
        .legal-section h3 {
            font-size: 22px;
            font-weight: 600;
            margin: 32px 0 16px;
            color: var(--text);
            font-family: 'Playfair Display', serif;
        }
        
        .legal-section p {
            color: var(--text-light);
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
            color: var(--text-light);
            margin-bottom: 12px;
            font-size: 16px;
            line-height: 1.8;
        }
        
        .legal-section strong {
            color: var(--text);
            font-weight: 600;
        }
        
        .highlight-box {
            background: linear-gradient(135deg, rgba(255, 107, 107, 0.05) 0%, rgba(255, 107, 107, 0.02) 100%);
            border-left: 4px solid var(--accent);
            padding: 24px;
            margin: 32px 0;
            border-radius: 8px;
        }
        
        .highlight-box p {
            margin-bottom: 0;
        }
        
        .info-box {
            background: var(--bg-lighter);
            border: 1px solid var(--border-light);
            padding: 24px;
            margin: 32px 0;
            border-radius: 8px;
        }
        
        .info-box h4 {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 12px;
            color: var(--primary);
            font-family: 'Playfair Display', serif;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 24px 0;
            background: var(--secondary);
            border: 1px solid var(--border-light);
            border-radius: 8px;
            overflow: hidden;
        }
        
        .data-table th {
            background: var(--bg-lighter);
            padding: 16px;
            text-align: left;
            font-weight: 600;
            color: var(--primary);
            border-bottom: 2px solid var(--border);
            font-family: 'Playfair Display', serif;
        }
        
        .data-table td {
            padding: 16px;
            border-bottom: 1px solid var(--border-light);
            color: var(--text-light);
        }
        
        .data-table tr:last-child td {
            border-bottom: none;
        }
        
        /* Footer */
        .footer {
            background: var(--primary);
            color: var(--secondary);
            padding: 64px 0 32px;
            margin-top: 96px;
        }
        
        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 48px;
            margin-bottom: 48px;
        }
        
        .footer-section h3 {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 20px;
            font-family: 'Playfair Display', serif;
        }
        
        .footer-links {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        
        .footer a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            font-weight: 500;
            font-size: 15px;
            transition: color 0.2s;
        }
        
        .footer a:hover {
            color: var(--accent);
        }
        
        .footer-bottom {
            padding-top: 32px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center;
            color: rgba(255, 255, 255, 0.6);
            font-size: 14px;
        }
        
        /* Responsive Design */
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
            
            .logo {
                font-size: 22px;
            }
            
            .container {
                padding: 0 16px;
            }
            
            .legal-section h2 {
                font-size: 26px;
            }
            
            .legal-section h3 {
                font-size: 20px;
            }
            
            .toc {
                padding: 20px;
            }
            
            .data-table {
                font-size: 14px;
            }
            
            .data-table th,
            .data-table td {
                padding: 12px;
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
            <a href="index.php" class="logo"><?php echo SITE_NAME; ?></a>
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
                            <span class="premium-badge">✓ Premium Member</span>
                        <?php endif; ?>
                    <?php else: ?>
                        Get unlimited access • Subscribe today
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="container">
            <div class="header-content">
                <a href="index.php" class="logo"><?php echo SITE_NAME; ?></a>
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
                <h1>Privacy Policy</h1>
                <p>Your privacy matters to us. Learn how we collect, use, and protect your information</p>
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
                    <li><a href="#introduction">Introduction</a></li>
                    <li><a href="#information-we-collect">Information We Collect</a></li>
                    <li><a href="#how-we-use">How We Use Your Information</a></li>
                    <li><a href="#information-sharing">Information Sharing and Disclosure</a></li>
                    <li><a href="#data-security">Data Security</a></li>
                    <li><a href="#cookies">Cookies and Tracking Technologies</a></li>
                    <li><a href="#third-party">Third-Party Services</a></li>
                    <li><a href="#your-rights">Your Privacy Rights</a></li>
                    <li><a href="#data-retention">Data Retention</a></li>
                    <li><a href="#children">Children's Privacy</a></li>
                    <li><a href="#international">International Data Transfers</a></li>
                    <li><a href="#changes">Changes to This Policy</a></li>
                    <li><a href="#contact">Contact Us</a></li>
                </ol>
            </div>

            <!-- Section 1 -->
            <section id="introduction" class="legal-section">
                <h2>1. Introduction</h2>
                <p>Welcome to <?php echo SITE_NAME; ?>. We respect your privacy and are committed to protecting your personal data. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you visit our website and use our services.</p>
                
                <div class="highlight-box">
                    <p><strong>Our Commitment:</strong> We will never sell your personal information to third parties. We only use your data to provide and improve our services.</p>
                </div>
                
                <p>By using our service, you agree to the collection and use of information in accordance with this Privacy Policy. If you do not agree with our policies and practices, please do not use our service.</p>
            </section>

            <!-- Section 2 -->
            <section id="information-we-collect" class="legal-section">
                <h2>2. Information We Collect</h2>
                
                <h3>2.1 Information You Provide to Us</h3>
                <p>We collect information that you voluntarily provide when you:</p>
                <ul>
                    <li><strong>Create an account:</strong> Name, email address, password</li>
                    <li><strong>Subscribe:</strong> Billing information (processed securely by Stripe)</li>
                    <li><strong>Contact us:</strong> Name, email, message content</li>
                    <li><strong>Update your profile:</strong> Profile preferences and settings</li>
                </ul>
                
                <h3>2.2 Information Collected Automatically</h3>
                <p>When you access our service, we automatically collect certain information, including:</p>
                
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Information Type</th>
                            <th>Examples</th>
                            <th>Purpose</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Device Information</td>
                            <td>Browser type, operating system, device type</td>
                            <td>Service optimization</td>
                        </tr>
                        <tr>
                            <td>Usage Data</td>
                            <td>Pages viewed, time spent, articles read</td>
                            <td>Analytics and improvement</td>
                        </tr>
                        <tr>
                            <td>IP Address</td>
                            <td>Your internet protocol address</td>
                            <td>Security and location</td>
                        </tr>
                        <tr>
                            <td>Cookies</td>
                            <td>Session data, preferences</td>
                            <td>Personalization</td>
                        </tr>
                    </tbody>
                </table>
                
                <h3>2.3 Payment Information</h3>
                <p>We use Stripe as our payment processor. We do not store your complete credit card information on our servers. Payment data is encrypted and securely processed by Stripe in compliance with PCI-DSS standards.</p>
                
                <h3>2.4 Free Article Tracking</h3>
                <p>We track the number of premium articles you've accessed using cookies and session data to enforce our 3-article free access limit for non-subscribers.</p>
            </section>

            <!-- Section 3 -->
            <section id="how-we-use" class="legal-section">
                <h2>3. How We Use Your Information</h2>
                
                <p>We use the collected information for various purposes:</p>
                
                <h3>3.1 Service Provision</h3>
                <ul>
                    <li>Create and manage your account</li>
                    <li>Process your subscription and payments</li>
                    <li>Provide access to premium content</li>
                    <li>Track your free article usage</li>
                    <li>Deliver personalized content recommendations</li>
                </ul>
                
                <h3>3.2 Communication</h3>
                <ul>
                    <li>Send service-related notifications</li>
                    <li>Respond to your inquiries and support requests</li>
                    <li>Send subscription renewal reminders</li>
                    <li>Notify you of changes to our service or policies</li>
                    <li>Send promotional emails (you can opt-out)</li>
                </ul>
                
                <h3>3.3 Analytics and Improvement</h3>
                <ul>
                    <li>Analyze usage patterns and trends</li>
                    <li>Improve our content and user experience</li>
                    <li>Develop new features and services</li>
                    <li>Monitor and analyze the effectiveness of our service</li>
                </ul>
                
                <h3>3.4 Security and Compliance</h3>
                <ul>
                    <li>Detect and prevent fraud and abuse</li>
                    <li>Ensure platform security</li>
                    <li>Comply with legal obligations</li>
                    <li>Enforce our Terms of Service</li>
                </ul>
            </section>

            <!-- Section 4 -->
            <section id="information-sharing" class="legal-section">
                <h2>4. Information Sharing and Disclosure</h2>
                
                <h3>4.1 We Do Not Sell Your Data</h3>
                <div class="highlight-box">
                    <p><strong>Important:</strong> We do not sell, rent, or trade your personal information to third parties for their marketing purposes.</p>
                </div>
                
                <h3>4.2 Service Providers</h3>
                <p>We may share your information with trusted third-party service providers who assist us in operating our service:</p>
                <ul>
                    <li><strong>Payment Processing:</strong> Stripe (for subscription billing)</li>
                    <li><strong>Email Services:</strong> For sending transactional and marketing emails</li>
                    <li><strong>Analytics:</strong> To understand how our service is used</li>
                    <li><strong>Hosting:</strong> Cloud infrastructure providers</li>
                </ul>
                <p>These service providers have access to your information only to perform specific tasks on our behalf and are obligated to protect your data.</p>
                
                <h3>4.3 Legal Requirements</h3>
                <p>We may disclose your information if required to do so by law or in response to:</p>
                <ul>
                    <li>Valid legal process (subpoenas, court orders)</li>
                    <li>Government requests</li>
                    <li>Protection of our rights and property</li>
                    <li>Prevention of fraud or security issues</li>
                    <li>Protection of user safety</li>
                </ul>
                
                <h3>4.4 Business Transfers</h3>
                <p>If <?php echo SITE_NAME; ?> is involved in a merger, acquisition, or sale of assets, your information may be transferred. We will notify you before your information becomes subject to a different privacy policy.</p>
            </section>

            <!-- Section 5 -->
            <section id="data-security" class="legal-section">
                <h2>5. Data Security</h2>
                
                <p>We implement appropriate technical and organizational measures to protect your personal information:</p>
                
                <h3>5.1 Security Measures</h3>
                <ul>
                    <li><strong>Encryption:</strong> Data transmitted to and from our service is encrypted using SSL/TLS</li>
                    <li><strong>Secure Storage:</strong> Personal data is stored on secure servers</li>
                    <li><strong>Access Controls:</strong> Limited access to personal data on a need-to-know basis</li>
                    <li><strong>Password Protection:</strong> Passwords are hashed and never stored in plain text</li>
                    <li><strong>Regular Audits:</strong> Security practices are regularly reviewed and updated</li>
                </ul>
                
                <h3>5.2 Your Responsibility</h3>
                <p>While we take security seriously, you also play a role in protecting your information:</p>
                <ul>
                    <li>Use a strong, unique password</li>
                    <li>Don't share your account credentials</li>
                    <li>Log out after using shared devices</li>
                    <li>Report any suspicious activity immediately</li>
                </ul>
                
                <div class="info-box">
                    <h4>Security Incident Response</h4>
                    <p>In the event of a data breach that affects your personal information, we will notify you within 72 hours and take immediate steps to mitigate the impact.</p>
                </div>
            </section>

            <!-- Section 6 -->
            <section id="cookies" class="legal-section">
                <h2>6. Cookies and Tracking Technologies</h2>
                
                <h3>6.1 What Are Cookies?</h3>
                <p>Cookies are small text files placed on your device that help us provide and improve our service. For detailed information, please see our <a href="cookies.php" style="color: var(--accent); text-decoration: underline;">Cookie Policy</a>.</p>
                
                <h3>6.2 How We Use Cookies</h3>
                <p>We use cookies for:</p>
                <ul>
                    <li><strong>Essential Cookies:</strong> Required for the service to function (login, security)</li>
                    <li><strong>Performance Cookies:</strong> Help us understand how you use our service</li>
                    <li><strong>Functional Cookies:</strong> Remember your preferences and settings</li>
                    <li><strong>Article Tracking:</strong> Count free articles accessed by non-subscribers</li>
                </ul>
                
                <h3>6.3 Managing Cookies</h3>
                <p>You can control cookies through your browser settings. However, disabling certain cookies may limit your ability to use some features of our service.</p>
            </section>

            <!-- Section 7 -->
            <section id="third-party" class="legal-section">
                <h2>7. Third-Party Services</h2>
                
                <h3>7.1 Stripe Payment Processing</h3>
                <p>We use Stripe to process subscription payments. When you provide payment information, it is transmitted directly to Stripe. Please review <a href="https://stripe.com/privacy" target="_blank" style="color: var(--accent); text-decoration: underline;">Stripe's Privacy Policy</a> to understand how they handle your data.</p>
                
                <h3>7.2 Third-Party Links</h3>
                <p>Our service may contain links to third-party websites. We are not responsible for the privacy practices of these external sites. We encourage you to read their privacy policies.</p>
                
                <h3>7.3 Social Media</h3>
                <p>If you interact with social media features on our site (sharing buttons, embedded content), the social media platform may collect information about your activity.</p>
            </section>

            <!-- Section 8 -->
            <section id="your-rights" class="legal-section">
                <h2>8. Your Privacy Rights</h2>
                
                <p>Depending on your location, you may have the following rights regarding your personal information:</p>
                
                <h3>8.1 Access and Portability</h3>
                <ul>
                    <li><strong>Right to Access:</strong> Request a copy of the personal data we hold about you</li>
                    <li><strong>Data Portability:</strong> Receive your data in a structured, commonly used format</li>
                </ul>
                
                <h3>8.2 Correction and Deletion</h3>
                <ul>
                    <li><strong>Right to Rectification:</strong> Correct inaccurate or incomplete information</li>
                    <li><strong>Right to Erasure:</strong> Request deletion of your personal data (subject to legal obligations)</li>
                </ul>
                
                <h3>8.3 Control and Objection</h3>
                <ul>
                    <li><strong>Right to Object:</strong> Object to processing of your data for certain purposes</li>
                    <li><strong>Right to Restriction:</strong> Request limitation of how we use your data</li>
                    <li><strong>Marketing Opt-Out:</strong> Unsubscribe from promotional emails at any time</li>
                </ul>
                
                <h3>8.4 Exercising Your Rights</h3>
                <p>To exercise any of these rights, please contact us using the information in the Contact Us section. We will respond to your request within 30 days.</p>
                
                <div class="info-box">
                    <h4>Account Management</h4>
                    <p>You can access and update most of your information through your account settings. For data deletion or more complex requests, please contact our support team.</p>
                </div>
            </section>

            <!-- Section 9 -->
            <section id="data-retention" class="legal-section">
                <h2>9. Data Retention</h2>
                
                <h3>9.1 How Long We Keep Your Data</h3>
                <p>We retain your personal information for as long as necessary to provide our service and fulfill the purposes outlined in this Privacy Policy:</p>
                <ul>
                    <li><strong>Active Accounts:</strong> Data retained while your account is active</li>
                    <li><strong>Inactive Accounts:</strong> May be deleted after 2 years of inactivity</li>
                    <li><strong>Billing Records:</strong> Retained for 7 years for tax and accounting purposes</li>
                    <li><strong>Legal Compliance:</strong> Data may be retained longer if required by law</li>
                </ul>
                
                <h3>9.2 Account Deletion</h3>
                <p>When you delete your account:</p>
                <ul>
                    <li>Your profile and personal information are removed from our active databases</li>
                    <li>Some information may remain in backups for up to 90 days</li>
                    <li>Anonymized usage data may be retained for analytics</li>
                    <li>Billing records are retained as required by law</li>
                </ul>
            </section>

            <!-- Section 10 -->
            <section id="children" class="legal-section">
                <h2>10. Children's Privacy</h2>
                
                <p>Our service is not intended for children under the age of 18. We do not knowingly collect personal information from children under 18.</p>
                
                <p>If you are a parent or guardian and believe your child has provided us with personal information, please contact us immediately. We will take steps to remove such information from our systems.</p>
                
                <div class="highlight-box">
                    <p><strong>Age Requirement:</strong> You must be at least 18 years old to create an account and use our service.</p>
                </div>
            </section>

            <!-- Section 11 -->
            <section id="international" class="legal-section">
                <h2>11. International Data Transfers</h2>
                
                <p>Your information may be transferred to and maintained on servers located outside of your country, where data protection laws may differ.</p>
                
                <h3>11.1 Data Protection Safeguards</h3>
                <p>When we transfer your data internationally, we ensure appropriate safeguards are in place:</p>
                <ul>
                    <li>Standard contractual clauses approved by regulatory authorities</li>
                    <li>Adequate data protection certification of recipients</li>
                    <li>Compliance with applicable data protection laws</li>
                </ul>
                
                <h3>11.2 Your Consent</h3>
                <p>By using our service, you consent to the transfer of your information to countries outside your residence, including countries that may not provide the same level of data protection as your home country.</p>
            </section>

            <!-- Section 12 -->
            <section id="changes" class="legal-section">
                <h2>12. Changes to This Privacy Policy</h2>
                
                <h3>12.1 Policy Updates</h3>
                <p>We may update this Privacy Policy from time to time to reflect changes in our practices or for legal, operational, or regulatory reasons.</p>
                
                <h3>12.2 Notification of Changes</h3>
                <p>We will notify you of material changes by:</p>
                <ul>
                    <li>Posting the updated policy on our website</li>
                    <li>Updating the "Last Updated" date at the top of this policy</li>
                    <li>Sending an email notification for significant changes</li>
                    <li>Displaying a prominent notice on our platform</li>
                </ul>
                
                <h3>12.3 Review and Acceptance</h3>
                <p>We encourage you to review this Privacy Policy periodically. Your continued use of our service after changes become effective constitutes your acceptance of the revised policy.</p>
            </section>

            <!-- Section 13 -->
            <section id="contact" class="legal-section">
                <h2>13. Contact Us</h2>
                
                <p>If you have questions, concerns, or requests regarding this Privacy Policy or our data practices, please contact us:</p>
                
                <div class="info-box">
                    <h4>Privacy Contact Information</h4>
                    <p><strong>Email:</strong> privacy@<?php echo strtolower(str_replace(' ', '', SITE_NAME)); ?>.com</p>
                    <p><strong>Support:</strong> support@<?php echo strtolower(str_replace(' ', '', SITE_NAME)); ?>.com</p>
                    <p><strong>Data Protection Officer:</strong> dpo@<?php echo strtolower(str_replace(' ', '', SITE_NAME)); ?>.com</p>
                    <p><strong>Response Time:</strong> We aim to respond to all privacy inquiries within 5-7 business days</p>
                </div>
                
                <h3>13.1 Complaints</h3>
                <p>If you believe we have not adequately addressed your privacy concerns, you have the right to lodge a complaint with your local data protection authority.</p>
            </section>

            <div class="highlight-box" style="margin-top: 64px;">
                <p><strong>Your Trust Matters:</strong> We are committed to transparency and protecting your privacy. Thank you for trusting <?php echo SITE_NAME; ?> with your personal information.</p>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3><?php echo SITE_NAME; ?></h3>
                    <p style="color: rgba(255, 255, 255, 0.7); margin-top: 12px; line-height: 1.6;">
                        Premium insights and analysis for those who want to stay ahead.
                    </p>
                </div>
                <div class="footer-section">
                    <h3>Navigation</h3>
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
                    <div class="footer-links">
                        <a href="#">About</a>
                        <a href="#">Contact</a>
                        <a href="#">Careers</a>
                    </div>
                </div>
                <div class="footer-section">
                    <h3>Legal</h3>
                    <div class="footer-links">
                        <a href="terms.php">Terms of Service</a>
                        <a href="privacy.php">Privacy Policy</a>
                        <a href="cookies.php">Cookie Policy</a>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
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