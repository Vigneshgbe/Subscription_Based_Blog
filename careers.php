<?php
require_once 'config.php';

$flash = getFlashMessage();
$hasSubscription = isLoggedIn() ? hasActiveSubscription($_SESSION['user_id']) : false;
$freeRemaining = getFreeArticlesRemaining();

// Handle application form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitizeInput($_POST['name'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $position = sanitizeInput($_POST['position'] ?? '');
    $coverLetter = sanitizeInput($_POST['cover_letter'] ?? '');
    $linkedin = sanitizeInput($_POST['linkedin'] ?? '');
    
    if (empty($name) || empty($email) || empty($position) || empty($coverLetter)) {
        setFlashMessage('Please fill in all required fields', 'danger');
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        setFlashMessage('Please enter a valid email address', 'danger');
    } else {
        // Here you would typically save to database or send email
        setFlashMessage('Thank you for your application! We\'ll review it and get back to you soon.', 'success');
        
        header('Location: careers.php');
        exit;
    }
}

// Job listings
$jobs = [
    [
        'title' => 'Senior Technology Writer',
        'department' => 'Editorial',
        'location' => 'Remote',
        'type' => 'Full-time',
        'description' => 'We\'re looking for an experienced technology writer who can demystify complex topics and make them accessible to a broad audience.',
        'requirements' => [
            '5+ years of professional writing experience in technology journalism',
            'Deep understanding of enterprise software, cloud computing, or AI/ML',
            'Portfolio demonstrating ability to explain technical concepts clearly',
            'Strong research and fact-checking skills'
        ]
    ],
    [
        'title' => 'Business & Economics Editor',
        'department' => 'Editorial',
        'location' => 'San Francisco, CA',
        'type' => 'Full-time',
        'description' => 'Join our editorial team to shape coverage of business trends, economic policy, and market analysis.',
        'requirements' => [
            'MBA or equivalent business education',
            '7+ years in business journalism or financial analysis',
            'Exceptional editing and mentoring skills',
            'Network of industry sources and contacts'
        ]
    ],
    [
        'title' => 'Full-Stack Developer',
        'department' => 'Engineering',
        'location' => 'Remote',
        'type' => 'Full-time',
        'description' => 'Help us build and scale our publishing platform. You\'ll work on everything from content management to subscription systems.',
        'requirements' => [
            'Expert knowledge of PHP, JavaScript, and modern web frameworks',
            'Experience with MySQL/PostgreSQL and Redis',
            'Understanding of payment processing and subscription models',
            'Portfolio of production web applications'
        ]
    ],
    [
        'title' => 'Product Marketing Manager',
        'department' => 'Marketing',
        'location' => 'Remote',
        'type' => 'Full-time',
        'description' => 'Drive growth and engagement for our premium subscription product. You\'ll own messaging, positioning, and go-to-market strategy.',
        'requirements' => [
            '4+ years in product marketing, preferably in media or SaaS',
            'Track record of successful product launches',
            'Data-driven approach to marketing strategy',
            'Excellent written and verbal communication'
        ]
    ],
    [
        'title' => 'Content Strategist',
        'department' => 'Editorial',
        'location' => 'Remote',
        'type' => 'Contract',
        'description' => '6-month contract to help optimize our content calendar, SEO strategy, and reader engagement initiatives.',
        'requirements' => [
            'Experience in content strategy for digital publications',
            'Strong understanding of SEO and content analytics',
            'Ability to work independently and meet deadlines',
            'Excellent project management skills'
        ]
    ],
    [
        'title' => 'Customer Success Specialist',
        'department' => 'Support',
        'location' => 'Remote',
        'type' => 'Part-time',
        'description' => 'Be the voice of our readers. Help subscribers get the most value from their membership while gathering feedback for product improvements.',
        'requirements' => [
            '2+ years in customer support or success',
            'Exceptional written communication skills',
            'Empathy and patience in problem-solving',
            'Familiarity with help desk software'
        ]
    ]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Careers - <?php echo SITE_NAME; ?></title>
    <meta name="description" content="Join our team and help shape the future of premium content">
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
            margin-bottom: 32px;
        }

        /* Benefits Section */
        .benefits-section {
            padding: 80px 0;
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

        .benefits-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 32px;
        }

        .benefit-card {
            background: var(--secondary);
            padding: 36px;
            border-radius: 12px;
            border: 1px solid var(--border-light);
            transition: all 0.3s;
        }

        .benefit-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-xl);
            border-color: var(--accent);
        }

        .benefit-icon {
            font-size: 42px;
            margin-bottom: 20px;
            display: block;
        }

        .benefit-card h3 {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 12px;
            font-family: 'Playfair Display', serif;
            color: var(--primary);
        }

        .benefit-card p {
            color: var(--text-light);
            line-height: 1.7;
            font-size: 15px;
        }

        /* Jobs Section */
        .jobs-section {
            padding: 80px 0;
        }

        .jobs-list {
            display: flex;
            flex-direction: column;
            gap: 24px;
            max-width: 900px;
            margin: 0 auto;
        }

        .job-card {
            background: var(--secondary);
            border: 1px solid var(--border-light);
            border-radius: 12px;
            padding: 36px;
            transition: all 0.3s;
            cursor: pointer;
        }

        .job-card:hover {
            box-shadow: var(--shadow-xl);
            border-color: var(--accent);
        }

        .job-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 16px;
            flex-wrap: wrap;
            gap: 16px;
        }

        .job-title-group h3 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 8px;
            font-family: 'Playfair Display', serif;
            color: var(--primary);
        }

        .job-meta {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
            font-size: 14px;
            color: var(--text-lighter);
        }

        .job-meta span {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .job-description {
            color: var(--text);
            line-height: 1.7;
            margin-bottom: 20px;
            font-size: 16px;
        }

        .job-requirements {
            background: var(--bg-lighter);
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 24px;
        }

        .job-requirements h4 {
            font-size: 14px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 12px;
            color: var(--text);
        }

        .job-requirements ul {
            list-style: none;
            padding: 0;
        }

        .job-requirements li {
            padding: 8px 0;
            padding-left: 24px;
            position: relative;
            color: var(--text-light);
            font-size: 15px;
            line-height: 1.6;
        }

        .job-requirements li::before {
            content: '✓';
            position: absolute;
            left: 0;
            color: var(--accent);
            font-weight: 700;
        }

        .job-details {
            display: none;
            margin-top: 24px;
            padding-top: 24px;
            border-top: 1px solid var(--border-light);
        }

        .job-details.active {
            display: block;
        }

        /* Application Modal */
        .modal {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 9999;
            align-items: center;
            justify-content: center;
            animation: fadeIn 0.3s;
        }

        .modal.active {
            display: flex;
        }

        .modal-overlay {
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(4px);
        }

        .modal-content {
            position: relative;
            background: var(--secondary);
            border-radius: 16px;
            padding: 48px;
            max-width: 600px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            animation: slideUp 0.3s;
        }

        .modal-close {
            position: absolute;
            top: 20px;
            right: 20px;
            background: none;
            border: none;
            font-size: 32px;
            cursor: pointer;
            color: var(--text-lighter);
            transition: color 0.2s;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
        }

        .modal-close:hover {
            color: var(--text);
            background: var(--bg-light);
        }

        .modal-content h2 {
            font-size: 32px;
            font-weight: 800;
            margin-bottom: 8px;
            font-family: 'Playfair Display', serif;
            color: var(--primary);
        }

        .modal-content .job-position {
            color: var(--accent);
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 32px;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 8px;
            color: var(--text);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 14px 18px;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 15px;
            font-family: inherit;
            color: var(--text);
            background: var(--secondary);
            transition: all 0.2s;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(255, 107, 107, 0.1);
        }

        .form-group textarea {
            min-height: 140px;
            resize: vertical;
        }

        .btn-submit {
            width: 100%;
            padding: 14px 24px;
            font-size: 16px;
            justify-content: center;
        }

        /* Alerts */
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

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideUp {
            from { 
                opacity: 0;
                transform: translateY(20px);
            }
            to { 
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Footer */
        .footer {
            background: var(--primary);
            color: var(--secondary);
            padding: 64px 0 32px;
            margin-top: 80px;
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

            .benefits-grid {
                grid-template-columns: 1fr;
            }

            .job-header {
                flex-direction: column;
            }

            .modal-content {
                padding: 32px 24px;
            }

            .logo {
                font-size: 22px;
            }
            
            .container {
                padding: 0 16px;
            }
        }
        
        @media (max-width: 480px) {
            .hero h1 {
                font-size: 32px;
            }

            .section-header h2 {
                font-size: 28px;
            }

            .job-title-group h3 {
                font-size: 20px;
            }

            .modal-content h2 {
                font-size: 24px;
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
                <h1>Join Our Team</h1>
                <p>Help us build the future of premium content. We're looking for talented people who share our mission.</p>
                <a href="#openings" class="btn btn-primary" style="background: var(--secondary); color: var(--primary);">View Open Positions</a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <main>
        <?php if ($flash): ?>
            <div class="container">
                <div class="alert alert-<?php echo $flash['type']; ?>">
                    <?php echo htmlspecialchars($flash['message']); ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Benefits -->
        <section class="benefits-section">
            <div class="container">
                <div class="section-header">
                    <h2>Why Work With Us?</h2>
                    <p>We believe in taking care of our team so they can do their best work</p>
                </div>

                <div class="benefits-grid">
                    <div class="benefit-card">
                        <span class="benefit-icon">🌍</span>
                        <h3>Remote-First Culture</h3>
                        <p>Work from anywhere. We've been remote since day one and have the systems to make it work seamlessly.</p>
                    </div>

                    <div class="benefit-card">
                        <span class="benefit-icon">💰</span>
                        <h3>Competitive Compensation</h3>
                        <p>Market-leading salaries, equity options, and annual performance bonuses. We pay for talent.</p>
                    </div>

                    <div class="benefit-card">
                        <span class="benefit-icon">🏥</span>
                        <h3>Comprehensive Health</h3>
                        <p>Premium medical, dental, and vision coverage for you and your family. Mental health support included.</p>
                    </div>

                    <div class="benefit-card">
                        <span class="benefit-icon">📚</span>
                        <h3>Learning Budget</h3>
                        <p>$2,000 annual stipend for courses, conferences, books, or any professional development you choose.</p>
                    </div>

                    <div class="benefit-card">
                        <span class="benefit-icon">⏰</span>
                        <h3>Flexible Schedule</h3>
                        <p>Set your own hours. We care about output, not when you're online. Take breaks when you need them.</p>
                    </div>

                    <div class="benefit-card">
                        <span class="benefit-icon">🌴</span>
                        <h3>Unlimited PTO</h3>
                        <p>Minimum 20 days encouraged per year, plus holidays. We actually want you to use your vacation time.</p>
                    </div>

                    <div class="benefit-card">
                        <span class="benefit-icon">💻</span>
                        <h3>Top-Tier Equipment</h3>
                        <p>MacBook Pro, external monitor, and $500 for your home office setup. Get the tools you need.</p>
                    </div>

                    <div class="benefit-card">
                        <span class="benefit-icon">🎯</span>
                        <h3>Meaningful Work</h3>
                        <p>Your work matters. You'll see direct impact on readers and the conversations we shape.</p>
                    </div>

                    <div class="benefit-card">
                        <span class="benefit-icon">👥</span>
                        <h3>Small Team</h3>
                        <p>Work closely with founders and senior team. Your voice will be heard and your ideas implemented.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Job Openings -->
        <section class="jobs-section" id="openings">
            <div class="container">
                <div class="section-header">
                    <h2>Open Positions</h2>
                    <p><?php echo count($jobs); ?> opportunities to make your mark</p>
                </div>

                <div class="jobs-list">
                    <?php foreach ($jobs as $index => $job): ?>
                    <div class="job-card" id="job-<?php echo $index; ?>">
                        <div class="job-header">
                            <div class="job-title-group">
                                <h3><?php echo htmlspecialchars($job['title']); ?></h3>
                                <div class="job-meta">
                                    <span>📁 <?php echo htmlspecialchars($job['department']); ?></span>
                                    <span>📍 <?php echo htmlspecialchars($job['location']); ?></span>
                                    <span>⏰ <?php echo htmlspecialchars($job['type']); ?></span>
                                </div>
                            </div>
                            <button class="btn btn-outline" onclick="openApplicationModal('<?php echo htmlspecialchars($job['title']); ?>')">
                                Apply Now
                            </button>
                        </div>

                        <p class="job-description"><?php echo htmlspecialchars($job['description']); ?></p>

                        <div class="job-requirements">
                            <h4>What We're Looking For</h4>
                            <ul>
                                <?php foreach ($job['requirements'] as $req): ?>
                                    <li><?php echo htmlspecialchars($req); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    </main>

    <!-- Application Modal -->
    <div class="modal" id="applicationModal">
        <div class="modal-overlay" onclick="closeApplicationModal()"></div>
        <div class="modal-content">
            <button class="modal-close" onclick="closeApplicationModal()">×</button>
            <h2>Apply for Position</h2>
            <div class="job-position" id="modalJobPosition"></div>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="name">Full Name *</label>
                    <input type="text" id="name" name="name" required placeholder="Jane Doe">
                </div>

                <div class="form-group">
                    <label for="email">Email Address *</label>
                    <input type="email" id="email" name="email" required placeholder="jane@example.com">
                </div>

                <input type="hidden" id="position" name="position" value="">

                <div class="form-group">
                    <label for="linkedin">LinkedIn Profile (Optional)</label>
                    <input type="url" id="linkedin" name="linkedin" placeholder="https://linkedin.com/in/yourprofile">
                </div>

                <div class="form-group">
                    <label for="cover_letter">Cover Letter *</label>
                    <textarea id="cover_letter" name="cover_letter" required placeholder="Tell us why you're excited about this role and what makes you a great fit..."></textarea>
                </div>

                <p style="font-size: 14px; color: var(--text-light); margin-bottom: 20px;">
                    Note: Please attach your resume/portfolio by emailing it to careers@<?php echo strtolower(str_replace(' ', '', SITE_NAME)); ?>.com with your name in the subject line.
                </p>

                <button type="submit" class="btn btn-primary btn-submit">Submit Application</button>
            </form>
        </div>
    </div>

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

        function openApplicationModal(jobTitle) {
            const modal = document.getElementById('applicationModal');
            const modalJobPosition = document.getElementById('modalJobPosition');
            const positionInput = document.getElementById('position');
            
            modalJobPosition.textContent = jobTitle;
            positionInput.value = jobTitle;
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeApplicationModal() {
            const modal = document.getElementById('applicationModal');
            modal.classList.remove('active');
            document.body.style.overflow = '';
        }

        // Close modal on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeApplicationModal();
            }
        });
    </script>
</body>
</html>