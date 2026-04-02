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
        
        .cookie-table {
            width: 100%;
            border-collapse: collapse;
            margin: 24px 0;
            background: var(--secondary);
            border: 1px solid var(--border-light);
            border-radius: 8px;
            overflow: hidden;
        }
        
        .cookie-table th {
            background: var(--bg-lighter);
            padding: 16px;
            text-align: left;
            font-weight: 600;
            color: var(--primary);
            border-bottom: 2px solid var(--border);
            font-family: 'Playfair Display', serif;
        }
        
        .cookie-table td {
            padding: 16px;
            border-bottom: 1px solid var(--border-light);
            color: var(--text-light);
            vertical-align: top;
        }
        
        .cookie-table tr:last-child td {
            border-bottom: none;
        }
        
        .cookie-type {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .cookie-type.essential {
            background: #fef3c7;
            color: #92400e;
        }
        
        .cookie-type.performance {
            background: #dbeafe;
            color: #1e3a8a;
        }
        
        .cookie-type.functional {
            background: #e0e7ff;
            color: #3730a3;
        }
        
        .cookie-type.targeting {
            background: #fce7f3;
            color: #831843;
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
            
            .cookie-table {
                font-size: 14px;
            }
            
            .cookie-table th,
            .cookie-table td {
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
                <p>Learn more: <a href="https://stripe.com/cookies-policy/legal" target="_blank" style="color: var(--accent); text-decoration: underline;">Stripe Cookie Policy</a></p>
                
                <h3>5.2 Google Analytics</h3>
                <p>We use Google Analytics to understand how our service is used:</p>
                <ul>
                    <li>Track page views and user journeys</li>
                    <li>Measure content engagement</li>
                    <li>Analyze traffic sources</li>
                    <li>Generate usage reports</li>
                </ul>
                <p>All data is anonymized. Learn more: <a href="https://policies.google.com/technologies/cookies" target="_blank" style="color: var(--accent); text-decoration: underline;">Google's Cookie Policy</a></p>
                
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
                    <li><strong>Google Analytics:</strong> Install the <a href="https://tools.google.com/dlpage/gaoptout" target="_blank" style="color: var(--accent); text-decoration: underline;">Google Analytics Opt-out Browser Add-on</a></li>
                    <li><strong>Advertising cookies:</strong> Visit <a href="http://www.youronlinechoices.com/" target="_blank" style="color: var(--accent); text-decoration: underline;">Your Online Choices</a> or <a href="http://optout.aboutads.info/" target="_blank" style="color: var(--accent); text-decoration: underline;">NAI Opt-Out</a></li>
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
                    <li><a href="privacy.php" style="color: var(--accent); text-decoration: underline;">Privacy Policy</a> - Our complete data protection practices</li>
                    <li><a href="terms.php" style="color: var(--accent); text-decoration: underline;">Terms of Service</a> - Terms governing your use of our service</li>
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