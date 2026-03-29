<?php
require_once 'config.php';

// Get published articles
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * ARTICLES_PER_PAGE;

$categoryFilter = isset($_GET['category']) ? intval($_GET['category']) : null;
$searchQuery = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';

$db = db();

// Build query
$whereClause = "WHERE a.is_published = 1";
$params = [];

if ($categoryFilter) {
    $whereClause .= " AND a.category_id = ?";
    $params[] = $categoryFilter;
}

if ($searchQuery) {
    $whereClause .= " AND (a.title LIKE ? OR a.excerpt LIKE ?)";
    $params[] = "%{$searchQuery}%";
    $params[] = "%{$searchQuery}%";
}

// Get total count
$countStmt = $db->prepare("SELECT COUNT(*) as total FROM articles a $whereClause");
$countStmt->execute($params);
$totalArticles = $countStmt->fetch()['total'];
$totalPages = ceil($totalArticles / ARTICLES_PER_PAGE);

// Get articles
$params[] = ARTICLES_PER_PAGE;
$params[] = $offset;
$stmt = $db->prepare("
    SELECT a.*, u.full_name as author_name, c.name as category_name, c.slug as category_slug
    FROM articles a
    LEFT JOIN users u ON a.author_id = u.id
    LEFT JOIN categories c ON a.category_id = c.id
    $whereClause
    ORDER BY a.published_at DESC
    LIMIT ? OFFSET ?
");
$stmt->execute($params);
$articles = $stmt->fetchAll();

// Get categories for filter
$categoriesStmt = $db->query("SELECT * FROM categories ORDER BY name");
$categories = $categoriesStmt->fetchAll();

// Get flash message
$flash = getFlashMessage();

// Check subscription status
$hasSubscription = isLoggedIn() ? hasActiveSubscription($_SESSION['user_id']) : false;
$freeRemaining = getFreeArticlesRemaining();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Premium Articles & Insights</title>
    <meta name="description" content="Access premium articles, insights, and exclusive content">
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
            padding: 80px 0;
            margin-bottom: 64px;
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
            font-size: 56px;
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
        
        /* Subscription Banner */
        .subscription-banner {
            background: linear-gradient(135deg, var(--accent) 0%, var(--accent-dark) 100%);
            color: var(--secondary);
            padding: 32px;
            margin: 40px 0;
            text-align: center;
            border-radius: 16px;
            box-shadow: var(--shadow-xl);
        }
        
        .subscription-banner .remaining {
            font-size: 48px;
            font-weight: 900;
            margin: 16px 0;
            font-family: 'Playfair Display', serif;
        }
        
        .subscription-banner p {
            font-size: 18px;
            font-weight: 500;
            margin-bottom: 16px;
        }
        
        .subscription-banner a {
            color: var(--secondary);
            text-decoration: underline;
            font-weight: 700;
            transition: opacity 0.2s;
        }
        
        .subscription-banner a:hover {
            opacity: 0.8;
        }
        
        /* Filters */
        .filters-wrapper {
            margin-bottom: 48px;
            background: var(--bg-lighter);
            padding: 24px;
            border-radius: 12px;
            border: 1px solid var(--border-light);
        }
        
        .filters {
            display: flex;
            gap: 12px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .filter-btn {
            padding: 10px 20px;
            background: var(--secondary);
            border: 1px solid var(--border);
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.2s;
            text-decoration: none;
            color: var(--text);
            border-radius: 8px;
            font-family: 'Inter', sans-serif;
        }
        
        .filter-btn:hover {
            background: var(--primary);
            color: var(--secondary);
            border-color: var(--primary);
            transform: translateY(-2px);
        }
        
        .filter-btn.active {
            background: var(--primary);
            color: var(--secondary);
            border-color: var(--primary);
        }
        
        .search-box {
            width: 100%;
        }
        
        .search-box input {
            width: 100%;
            padding: 14px 20px;
            border: 1px solid var(--border);
            font-size: 15px;
            font-family: inherit;
            border-radius: 8px;
            transition: all 0.2s;
        }
        
        .search-box input:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(255, 107, 107, 0.1);
        }
        
        /* Articles Grid */
        .articles-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
            gap: 32px;
            margin-bottom: 64px;
        }
        
        .article-card {
            background: var(--secondary);
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.3s;
            position: relative;
            display: flex;
            flex-direction: column;
            border: 1px solid var(--border-light);
        }
        
        .article-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-xl);
            border-color: var(--border);
        }
        
        .article-image-wrapper {
            position: relative;
            overflow: hidden;
            background: var(--bg-light);
            aspect-ratio: 16/10;
        }
        
        .article-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            transition: transform 0.3s;
        }
        
        .article-card:hover .article-image {
            transform: scale(1.05);
        }
        
        .article-badge {
            position: absolute;
            top: 16px;
            right: 16px;
            background: var(--premium);
            color: var(--primary);
            padding: 8px 16px;
            font-weight: 800;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-radius: 6px;
            box-shadow: var(--shadow);
        }
        
        .article-content {
            padding: 28px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        
        .article-meta {
            display: flex;
            gap: 16px;
            font-size: 13px;
            margin-bottom: 16px;
            color: var(--text-lighter);
            font-weight: 500;
            flex-wrap: wrap;
        }
        
        .article-meta span {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .category-tag {
            background: var(--bg-light);
            color: var(--text);
            padding: 4px 12px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 12px;
        }
        
        .article-title {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 14px;
            line-height: 1.4;
            color: var(--primary);
            font-family: 'Playfair Display', serif;
        }
        
        .article-title a {
            color: inherit;
            text-decoration: none;
            transition: color 0.2s;
        }
        
        .article-title a:hover {
            color: var(--accent);
        }
        
        .article-excerpt {
            color: var(--text-light);
            margin-bottom: 20px;
            line-height: 1.7;
            flex: 1;
            font-size: 15px;
        }
        
        .read-more {
            font-weight: 600;
            font-size: 14px;
            color: var(--accent);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: gap 0.3s;
        }
        
        .read-more:hover {
            gap: 12px;
        }
        
        .read-more svg {
            width: 16px;
            height: 16px;
        }
        
        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin: 48px 0;
            flex-wrap: wrap;
        }
        
        .page-link {
            padding: 10px 16px;
            border: 1px solid var(--border);
            color: var(--text);
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.2s;
            border-radius: 8px;
            min-width: 44px;
            text-align: center;
        }
        
        .page-link:hover {
            background: var(--primary);
            color: var(--secondary);
            border-color: var(--primary);
        }
        
        .page-link.active {
            background: var(--primary);
            color: var(--secondary);
            border-color: var(--primary);
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
        
        /* Alerts */
        .alert {
            padding: 16px 24px;
            margin-bottom: 24px;
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
        
        .alert-warning {
            background: #fef3c7;
            border-color: #f59e0b;
            color: #92400e;
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 80px 20px;
            color: var(--text-light);
        }
        
        .empty-state svg {
            width: 80px;
            height: 80px;
            margin-bottom: 24px;
            opacity: 0.3;
        }
        
        .empty-state h3 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 12px;
            color: var(--text);
        }
        
        .empty-state p {
            font-size: 16px;
        }

        /* Usage Popup Styles */
        .usage-popup {
            position: fixed;
            inset: 0;
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: fadeIn 0.3s;
        }

        .popup-overlay {
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(4px);
        }

        .popup-content {
            position: relative;
            background: var(--secondary);
            border-radius: 16px;
            padding: 40px;
            max-width: 480px;
            width: 90%;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            text-align: center;
            animation: slideUp 0.3s;
        }

        .popup-close {
            position: absolute;
            top: 16px;
            right: 16px;
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

        .popup-close:hover {
            color: var(--text);
            background: var(--bg-light);
        }

        .popup-icon {
            font-size: 64px;
            margin-bottom: 20px;
        }

        .popup-content h3 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 12px;
            color: var(--primary);
            font-family: 'Playfair Display', serif;
        }

        .popup-content p {
            color: var(--text-light);
            font-size: 16px;
            margin-bottom: 28px;
            line-height: 1.6;
        }

        .popup-actions {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
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
        
        /* Responsive Design */
        @media (max-width: 1024px) {
            .articles-grid {
                grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
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
                padding: 48px 0;
            }
            
            .hero h1 {
                font-size: 36px;
            }
            
            .hero p {
                font-size: 16px;
            }
            
            .articles-grid {
                grid-template-columns: 1fr;
                gap: 24px;
            }
            
            .logo {
                font-size: 22px;
            }
            
            .container {
                padding: 0 16px;
            }
            
            .filters-wrapper {
                padding: 16px;
            }
            
            .subscription-banner {
                padding: 24px 16px;
            }
            
            .subscription-banner .remaining {
                font-size: 36px;
            }
        }
        
        @media (max-width: 480px) {
            .hero h1 {
                font-size: 28px;
            }
            
            .article-title {
                font-size: 20px;
            }
            
            .filters {
                gap: 8px;
            }
            
            .filter-btn {
                padding: 8px 16px;
                font-size: 13px;
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

    <!-- Subscription Reminder Popup (instead of banner) -->
    <?php if (!$hasSubscription && isLoggedIn() && $freeRemaining <= 2): ?>
    <div class="usage-popup" id="usagePopup" style="display: none;">
        <div class="popup-overlay" onclick="closeUsagePopup()"></div>
        <div class="popup-content">
            <button class="popup-close" onclick="closeUsagePopup()">×</button>
            <div class="popup-icon">
                <?php if ($freeRemaining == 0): ?>
                    🔒
                <?php else: ?>
                    ⚠️
                <?php endif; ?>
            </div>
            <h3><?php echo $freeRemaining == 0 ? 'No Free Articles Remaining' : $freeRemaining . ' Free Article' . ($freeRemaining > 1 ? 's' : '') . ' Remaining'; ?></h3>
            <p><?php echo $freeRemaining == 0 ? 'Subscribe now to continue reading premium content' : 'Subscribe for unlimited access to all premium articles'; ?></p>
            <div class="popup-actions">
                <a href="pricing.php" class="btn btn-primary">View Plans</a>
                <button onclick="closeUsagePopup()" class="btn btn-outline">Maybe Later</button>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Hero -->
    <div class="hero">
        <div class="container">
            <div class="hero-content">
                <h1>Insights That<br>Matter</h1>
                <p>Deep dives into technology, business, and culture. Premium content for curious minds.</p>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <main class="container">
        <?php if ($flash): ?>
            <div class="alert alert-<?php echo $flash['type']; ?>">
                <?php echo htmlspecialchars($flash['message']); ?>
            </div>
        <?php endif; ?>

        <!-- Filters -->
        <div class="filters-wrapper">
            <div class="filters">
                <a href="index.php" class="filter-btn <?php echo !$categoryFilter ? 'active' : ''; ?>">All</a>
                <?php foreach ($categories as $cat): ?>
                    <a href="?category=<?php echo $cat['id']; ?>" 
                       class="filter-btn <?php echo $categoryFilter == $cat['id'] ? 'active' : ''; ?>">
                        <?php echo htmlspecialchars($cat['name']); ?>
                    </a>
                <?php endforeach; ?>
            </div>
            
            <form method="GET" class="search-box">
                <?php if ($categoryFilter): ?>
                    <input type="hidden" name="category" value="<?php echo $categoryFilter; ?>">
                <?php endif; ?>
                <input type="text" name="search" placeholder="Search articles..." 
                       value="<?php echo htmlspecialchars($searchQuery); ?>">
            </form>
        </div>

        <!-- Articles Grid -->
        <div class="articles-grid">
            <?php if (empty($articles)): ?>
                <div class="empty-state" style="grid-column: 1/-1;">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3>No articles found</h3>
                    <p>Try adjusting your search or filters</p>
                </div>
            <?php else: ?>
                <?php foreach ($articles as $article): ?>
                <article class="article-card">
                    <div class="article-image-wrapper">
                        <?php if ($article['is_premium']): ?>
                            <span class="article-badge">★ PREMIUM</span>
                        <?php endif; ?>
                        
                        <?php if ($article['featured_image']): ?>
                            <img src="<?php echo htmlspecialchars($article['featured_image']); ?>" 
                                 alt="<?php echo htmlspecialchars($article['title']); ?>" 
                                 class="article-image">
                        <?php else: ?>
                            <div class="article-image" style="display: flex; align-items: center; justify-content: center; font-size: 64px; font-weight: 900; color: var(--border); font-family: 'Playfair Display', serif;">
                                <?php echo strtoupper(substr($article['title'], 0, 1)); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="article-content">
                        <div class="article-meta">
                            <?php if ($article['category_name']): ?>
                                <span class="category-tag"><?php echo htmlspecialchars($article['category_name']); ?></span>
                            <?php endif; ?>
                            <span><?php echo formatDate($article['published_at']); ?></span>
                            <span>👁 <?php echo number_format($article['views']); ?></span>
                        </div>
                        
                        <h2 class="article-title">
                            <a href="article.php?slug=<?php echo htmlspecialchars($article['slug']); ?>">
                                <?php echo htmlspecialchars($article['title']); ?>
                            </a>
                        </h2>
                        
                        <p class="article-excerpt">
                            <?php echo htmlspecialchars($article['excerpt'] ?: truncateText(strip_tags($article['content']), 150)); ?>
                        </p>
                        
                        <a href="article.php?slug=<?php echo htmlspecialchars($article['slug']); ?>" class="read-more">
                            Read Article
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                            </svg>
                        </a>
                    </div>
                </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=<?php echo $page - 1; ?><?php echo $categoryFilter ? '&category='.$categoryFilter : ''; ?><?php echo $searchQuery ? '&search='.urlencode($searchQuery) : ''; ?>" class="page-link">← Prev</a>
            <?php endif; ?>
            
            <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                <a href="?page=<?php echo $i; ?><?php echo $categoryFilter ? '&category='.$categoryFilter : ''; ?><?php echo $searchQuery ? '&search='.urlencode($searchQuery) : ''; ?>" 
                   class="page-link <?php echo $i == $page ? 'active' : ''; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
            
            <?php if ($page < $totalPages): ?>
                <a href="?page=<?php echo $page + 1; ?><?php echo $categoryFilter ? '&category='.$categoryFilter : ''; ?><?php echo $searchQuery ? '&search='.urlencode($searchQuery) : ''; ?>" class="page-link">Next →</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
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
                        <a href="about.php">About</a>
                        <a href="contact.php">Contact</a>
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
    </script>

    <script>
    function toggleMobileMenu() {
        const mobileNav = document.getElementById('mobileNav');
        mobileNav.classList.toggle('active');
        document.body.style.overflow = mobileNav.classList.contains('active') ? 'hidden' : '';
    }

    // Usage popup functionality
    function showUsagePopup() {
        const popup = document.getElementById('usagePopup');
        if (popup) {
            popup.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
    }

    function closeUsagePopup() {
        const popup = document.getElementById('usagePopup');
        if (popup) {
            popup.style.display = 'none';
            document.body.style.overflow = '';
            // Set cookie to not show again for 24 hours
            document.cookie = 'usage_reminder_shown=1; max-age=86400; path=/';
        }
    }

    // Show popup on page load if user has low remaining articles
    window.addEventListener('DOMContentLoaded', function() {
        <?php if (!$hasSubscription && isLoggedIn() && $freeRemaining <= 2): ?>
            // Check if we've shown the popup recently
            if (!document.cookie.includes('usage_reminder_shown=1')) {
                setTimeout(showUsagePopup, 2000); // Show after 2 seconds
            }
        <?php endif; ?>
    });
</script>
</body>
</html>