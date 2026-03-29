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
    <link rel="icon" type="image/x-icon" href="https://png.pngtree.com/element_our/sm/20180518/sm_5aff60887f7d9.jpg">
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
            padding: 100px 0;
            margin-bottom: 80px;
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
            text-align: center;
            margin: 0 auto;
        }
        
        .hero h1 {
            font-size: 64px;
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 24px;
            letter-spacing: -1px;
            font-family: 'Playfair Display', serif;
        }
        
        .hero p {
            font-size: 20px;
            opacity: 0.9;
            line-height: 1.6;
            font-weight: 300;
        }

        /* About Content Sections */
        .about-section {
            padding: 80px 0;
        }

        .about-section:nth-child(even) {
            background: var(--bg-lighter);
        }

        .section-header {
            text-align: center;
            max-width: 800px;
            margin: 0 auto 64px;
        }

        .section-header h2 {
            font-size: 42px;
            font-weight: 800;
            margin-bottom: 20px;
            font-family: 'Playfair Display', serif;
            color: var(--primary);
        }

        .section-header p {
            font-size: 18px;
            color: var(--text-light);
            line-height: 1.8;
        }

        .story-content {
            max-width: 900px;
            margin: 0 auto;
        }

        .story-content p {
            font-size: 17px;
            line-height: 1.9;
            color: var(--text);
            margin-bottom: 24px;
        }

        .story-content p:first-of-type::first-letter {
            font-size: 72px;
            font-weight: 700;
            float: left;
            line-height: 1;
            margin: 5px 16px 0 0;
            font-family: 'Playfair Display', serif;
            color: var(--accent);
        }

        /* Values Grid */
        .values-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 40px;
            margin-top: 48px;
        }

        .value-card {
            background: var(--secondary);
            padding: 40px;
            border-radius: 12px;
            border: 1px solid var(--border-light);
            transition: all 0.3s;
        }

        .value-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-xl);
            border-color: var(--accent);
        }

        .value-icon {
            font-size: 48px;
            margin-bottom: 20px;
            display: block;
        }

        .value-card h3 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 12px;
            font-family: 'Playfair Display', serif;
            color: var(--primary);
        }

        .value-card p {
            color: var(--text-light);
            line-height: 1.7;
            font-size: 16px;
        }

        /* Stats Section */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
            text-align: center;
        }

        .stat-item {
            padding: 32px;
        }

        .stat-number {
            font-size: 56px;
            font-weight: 900;
            color: var(--accent);
            font-family: 'Playfair Display', serif;
            margin-bottom: 8px;
            display: block;
        }

        .stat-label {
            font-size: 16px;
            color: var(--text-light);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Team Section */
        .team-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 40px;
            margin-top: 48px;
        }

        .team-card {
            text-align: center;
            background: var(--secondary);
            padding: 40px;
            border-radius: 12px;
            border: 1px solid var(--border-light);
            transition: all 0.3s;
        }

        .team-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-xl);
        }

        .team-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--accent) 0%, var(--accent-dark) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
            font-weight: 900;
            color: var(--secondary);
            margin: 0 auto 24px;
            font-family: 'Playfair Display', serif;
        }

        .team-card h3 {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 8px;
            font-family: 'Playfair Display', serif;
            color: var(--primary);
        }

        .team-role {
            color: var(--accent);
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 16px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .team-bio {
            color: var(--text-light);
            line-height: 1.6;
            font-size: 15px;
        }

        /* CTA Section */
        .cta-section {
            background: linear-gradient(135deg, var(--accent) 0%, var(--accent-dark) 100%);
            color: var(--secondary);
            padding: 80px 0;
            text-align: center;
            margin-top: 80px;
        }

        .cta-section h2 {
            font-size: 42px;
            font-weight: 800;
            margin-bottom: 20px;
            font-family: 'Playfair Display', serif;
        }

        .cta-section p {
            font-size: 18px;
            margin-bottom: 32px;
            opacity: 0.95;
        }

        .cta-section .btn {
            background: var(--secondary);
            color: var(--accent);
            padding: 14px 32px;
            font-size: 16px;
        }

        .cta-section .btn:hover {
            background: var(--primary);
            color: var(--secondary);
        }
        
        /* Footer */
        .footer {
            background: var(--primary);
            color: var(--secondary);
            padding: 64px 0 32px;
            margin-top: 0;
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
                padding: 60px 0;
            }
            
            .hero h1 {
                font-size: 40px;
            }
            
            .hero p {
                font-size: 16px;
            }

            .section-header h2 {
                font-size: 32px;
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

            .logo {
                font-size: 22px;
            }
            
            .container {
                padding: 0 16px;
            }

            .cta-section h2 {
                font-size: 32px;
            }
        }
        
        @media (max-width: 480px) {
            .hero h1 {
                font-size: 32px;
            }

            .section-header h2 {
                font-size: 28px;
            }

            .stat-number {
                font-size: 42px;
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
            <a href="about.php">About</a>
            <a href="contact.php">Contact</a>
            <a href="careers.php">Careers</a>
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
                            <span class="premium-badge">✓ Premium Member</span>
                        <?php else: ?>
                            • <?php echo $freeRemaining; ?> free articles remaining
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
                    <a href="about.php">About</a>
                    <a href="contact.php">Contact</a>
                    <a href="careers.php">Careers</a>
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
                    <span class="stat-number">50K+</span>
                    <span class="stat-label">Active Readers</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">1,200+</span>
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
                    <div class="team-avatar">SJ</div>
                    <h3>Sarah Johnson</h3>
                    <div class="team-role">Founder & Editor-in-Chief</div>
                    <p class="team-bio">Former investigative journalist with 15 years at major publications. Believes great stories change minds.</p>
                </div>
                <div class="team-card">
                    <div class="team-avatar">MC</div>
                    <h3>Michael Chen</h3>
                    <div class="team-role">Head of Technology</div>
                    <p class="team-bio">Ex-Silicon Valley engineer turned writer. Demystifies tech for non-technical audiences.</p>
                </div>
                <div class="team-card">
                    <div class="team-avatar">EP</div>
                    <h3>Emily Patel</h3>
                    <div class="team-role">Senior Business Analyst</div>
                    <p class="team-bio">MBA from Wharton, former strategy consultant. Makes complex economics accessible.</p>
                </div>
                <div class="team-card">
                    <div class="team-avatar">DK</div>
                    <h3>David Kim</h3>
                    <div class="team-role">Culture & Society Editor</div>
                    <p class="team-bio">Cultural critic and sociologist. Explores how we live, work, and think in the modern world.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="cta-section">
        <div class="container">
            <h2>Ready to Join Us?</h2>
            <p>Get unlimited access to all our premium content and join a community of curious minds.</p>
            <a href="pricing.php" class="btn">View Pricing Plans</a>
        </div>
    </section>

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
                        <a href="about.php">About</a>
                        <a href="contact.php">Contact</a>
                        <a href="careers.php">Careers</a>
                        <a href="pricing.php">Pricing</a>
                    </div>
                </div>
                <div class="footer-section">
                    <h3>Company</h3>
                    <div class="footer-links">
                        <a href="about.php">About</a>
                        <a href="contact.php">Contact</a>
                        <a href="careers.php">Careers</a>
                    </div>
                </div>
                <div class="footer-section">
                    <h3>Legal</h3>
                    <div class="footer-links">
                        <a href="#">Terms of Service</a>
                        <a href="#">Privacy Policy</a>
                        <a href="#">Cookie Policy</a>
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
    </script>
</body>
</html>