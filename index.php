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
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        :root {
            --primary: #000000;
            --secondary: #ffffff;
            --accent: #ff0055;
            --text: #1a1a1a;
            --text-light: #666666;
            --border: #e0e0e0;
            --bg-light: #fafafa;
            --premium: #ffd700;
        }
        
        body {
            font-family: 'Georgia', 'Times New Roman', serif;
            line-height: 1.7;
            color: var(--text);
            background: var(--secondary);
        }
        
        .header {
            border-bottom: 4px solid var(--primary);
            background: var(--secondary);
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        
        .header-top {
            background: var(--primary);
            color: var(--secondary);
            padding: 8px 0;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-family: -apple-system, BlinkMacSystemFont, sans-serif;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 0;
        }
        
        .logo {
            font-size: 42px;
            font-weight: 900;
            letter-spacing: -2px;
            text-transform: uppercase;
            color: var(--primary);
            text-decoration: none;
            font-family: -apple-system, BlinkMacSystemFont, sans-serif;
        }
        
        .nav {
            display: flex;
            gap: 30px;
            align-items: center;
        }
        
        .nav a {
            color: var(--text);
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: color 0.2s;
            font-family: -apple-system, BlinkMacSystemFont, sans-serif;
        }
        
        .nav a:hover {
            color: var(--accent);
        }
        
        .btn {
            padding: 10px 24px;
            border: none;
            cursor: pointer;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 1px;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
            font-family: -apple-system, BlinkMacSystemFont, sans-serif;
        }
        
        .btn-primary {
            background: var(--accent);
            color: var(--secondary);
        }
        
        .btn-primary:hover {
            background: #cc0044;
            transform: translateY(-2px);
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
        
        .hero {
            background: linear-gradient(135deg, #000000 0%, #2d2d2d 100%);
            color: var(--secondary);
            padding: 60px 0;
            margin-bottom: 60px;
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
            background: repeating-linear-gradient(
                0deg,
                transparent,
                transparent 2px,
                rgba(255,255,255,0.03) 2px,
                rgba(255,255,255,0.03) 4px
            );
            pointer-events: none;
        }
        
        .hero-content {
            position: relative;
            z-index: 1;
        }
        
        .hero h1 {
            font-size: 56px;
            font-weight: 900;
            line-height: 1.1;
            margin-bottom: 20px;
            letter-spacing: -1px;
        }
        
        .hero p {
            font-size: 20px;
            opacity: 0.9;
            max-width: 600px;
        }
        
        .subscription-banner {
            background: linear-gradient(135deg, var(--accent) 0%, #ff3366 100%);
            color: var(--secondary);
            padding: 20px;
            margin: 30px 0;
            text-align: center;
            font-weight: 600;
            border-radius: 4px;
        }
        
        .subscription-banner .remaining {
            font-size: 32px;
            font-weight: 900;
            margin: 10px 0;
        }
        
        .filters {
            display: flex;
            gap: 15px;
            margin-bottom: 40px;
            flex-wrap: wrap;
            align-items: center;
        }
        
        .filter-btn {
            padding: 8px 20px;
            background: var(--bg-light);
            border: 2px solid var(--border);
            cursor: pointer;
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.2s;
            text-decoration: none;
            color: var(--text);
            font-family: -apple-system, BlinkMacSystemFont, sans-serif;
        }
        
        .filter-btn:hover, .filter-btn.active {
            background: var(--primary);
            color: var(--secondary);
            border-color: var(--primary);
        }
        
        .search-box {
            flex: 1;
            min-width: 250px;
        }
        
        .search-box input {
            width: 100%;
            padding: 10px 20px;
            border: 2px solid var(--border);
            font-size: 14px;
            font-family: inherit;
        }
        
        .search-box input:focus {
            outline: none;
            border-color: var(--primary);
        }
        
        .articles-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
            gap: 40px;
            margin-bottom: 60px;
        }
        
        .article-card {
            border: 2px solid var(--border);
            background: var(--secondary);
            transition: all 0.3s;
            position: relative;
            display: flex;
            flex-direction: column;
        }
        
        .article-card:hover {
            border-color: var(--primary);
            transform: translateY(-4px);
            box-shadow: 8px 8px 0 var(--primary);
        }
        
        .article-image {
            width: 100%;
            height: 240px;
            object-fit: cover;
            display: block;
            background: var(--bg-light);
        }
        
        .article-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: var(--premium);
            color: var(--primary);
            padding: 6px 14px;
            font-weight: 900;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
            border: 2px solid var(--primary);
            font-family: -apple-system, BlinkMacSystemFont, sans-serif;
        }
        
        .article-content {
            padding: 25px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        
        .article-meta {
            display: flex;
            gap: 15px;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 15px;
            color: var(--text-light);
            font-weight: 600;
            font-family: -apple-system, BlinkMacSystemFont, sans-serif;
        }
        
        .article-meta span {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .article-title {
            font-size: 24px;
            font-weight: 900;
            margin-bottom: 12px;
            line-height: 1.3;
            color: var(--primary);
        }
        
        .article-title a {
            color: inherit;
            text-decoration: none;
        }
        
        .article-title a:hover {
            color: var(--accent);
        }
        
        .article-excerpt {
            color: var(--text-light);
            margin-bottom: 20px;
            line-height: 1.6;
            flex: 1;
        }
        
        .read-more {
            font-weight: 700;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 1px;
            color: var(--primary);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-family: -apple-system, BlinkMacSystemFont, sans-serif;
        }
        
        .read-more:hover {
            color: var(--accent);
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin: 40px 0;
        }
        
        .page-link {
            padding: 10px 18px;
            border: 2px solid var(--primary);
            color: var(--primary);
            text-decoration: none;
            font-weight: 700;
            font-size: 14px;
            transition: all 0.2s;
            font-family: -apple-system, BlinkMacSystemFont, sans-serif;
        }
        
        .page-link:hover, .page-link.active {
            background: var(--primary);
            color: var(--secondary);
        }
        
        .footer {
            background: var(--primary);
            color: var(--secondary);
            padding: 40px 0;
            margin-top: 80px;
            text-align: center;
            font-family: -apple-system, BlinkMacSystemFont, sans-serif;
        }
        
        .footer-links {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-bottom: 20px;
        }
        
        .footer a {
            color: var(--secondary);
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .footer a:hover {
            color: var(--accent);
        }
        
        .alert {
            padding: 15px 20px;
            margin-bottom: 20px;
            border-left: 4px solid;
            font-weight: 600;
            font-family: -apple-system, BlinkMacSystemFont, sans-serif;
        }
        
        .alert-success {
            background: #d4edda;
            border-color: #28a745;
            color: #155724;
        }
        
        .alert-danger {
            background: #f8d7da;
            border-color: #dc3545;
            color: #721c24;
        }
        
        .alert-warning {
            background: #fff3cd;
            border-color: #ffc107;
            color: #856404;
        }
        
        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            font-size: 28px;
            cursor: pointer;
        }
        
        @media (max-width: 768px) {
            .nav {
                display: none;
            }
            
            .mobile-menu-btn {
                display: block;
            }
            
            .hero h1 {
                font-size: 36px;
            }
            
            .articles-grid {
                grid-template-columns: 1fr;
            }
            
            .logo {
                font-size: 28px;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-top">
            <div class="container">
                <?php if (isLoggedIn()): ?>
                    Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                    <?php if ($hasSubscription): ?>
                        | ✓ Premium Member
                    <?php else: ?>
                        | <?php echo $freeRemaining; ?> free articles remaining
                    <?php endif; ?>
                <?php else: ?>
                    Get unlimited access • Subscribe today
                <?php endif; ?>
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
                <button class="mobile-menu-btn" onclick="alert('Mobile menu coming soon')">☰</button>
            </div>
        </div>
    </header>

    <?php if (!$hasSubscription && isLoggedIn() && $freeRemaining <= 1): ?>
    <div class="subscription-banner">
        <div class="container">
            <div class="remaining"><?php echo $freeRemaining; ?></div>
            Free articles remaining. <a href="pricing.php" style="color: white; text-decoration: underline;">Subscribe now</a> for unlimited access!
        </div>
    </div>
    <?php endif; ?>

    <div class="hero">
        <div class="container">
            <div class="hero-content">
                <h1>Insights That<br>Matter</h1>
                <p>Deep dives into technology, business, and culture. Premium content for curious minds.</p>
            </div>
        </div>
    </div>

    <main class="container">
        <?php if ($flash): ?>
            <div class="alert alert-<?php echo $flash['type']; ?>">
                <?php echo htmlspecialchars($flash['message']); ?>
            </div>
        <?php endif; ?>

        <div class="filters">
            <a href="index.php" class="filter-btn <?php echo !$categoryFilter ? 'active' : ''; ?>">All</a>
            <?php foreach ($categories as $cat): ?>
                <a href="?category=<?php echo $cat['id']; ?>" 
                   class="filter-btn <?php echo $categoryFilter == $cat['id'] ? 'active' : ''; ?>">
                    <?php echo htmlspecialchars($cat['name']); ?>
                </a>
            <?php endforeach; ?>
            
            <form method="GET" class="search-box">
                <?php if ($categoryFilter): ?>
                    <input type="hidden" name="category" value="<?php echo $categoryFilter; ?>">
                <?php endif; ?>
                <input type="text" name="search" placeholder="Search articles..." 
                       value="<?php echo htmlspecialchars($searchQuery); ?>">
            </form>
        </div>

        <div class="articles-grid">
            <?php if (empty($articles)): ?>
                <p style="grid-column: 1/-1; text-align: center; padding: 60px 0; font-size: 18px; color: var(--text-light);">
                    No articles found. Check back soon!
                </p>
            <?php else: ?>
                <?php foreach ($articles as $article): ?>
                <article class="article-card">
                    <?php if ($article['is_premium']): ?>
                        <span class="article-badge">★ PREMIUM</span>
                    <?php endif; ?>
                    
                    <?php if ($article['featured_image']): ?>
                        <img src="<?php echo htmlspecialchars($article['featured_image']); ?>" 
                             alt="<?php echo htmlspecialchars($article['title']); ?>" 
                             class="article-image">
                    <?php else: ?>
                        <div class="article-image" style="display: flex; align-items: center; justify-content: center; font-size: 48px; font-weight: 900; color: var(--border);">
                            <?php echo strtoupper(substr($article['title'], 0, 1)); ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="article-content">
                        <div class="article-meta">
                            <?php if ($article['category_name']): ?>
                                <span><?php echo htmlspecialchars($article['category_name']); ?></span>
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
                            Read Article →
                        </a>
                    </div>
                </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

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

    <footer class="footer">
        <div class="container">
            <div class="footer-links">
                <a href="index.php">Home</a>
                <a href="pricing.php">Pricing</a>
                <a href="#">About</a>
                <a href="#">Terms</a>
                <a href="#">Privacy</a>
                <a href="#">Contact</a>
            </div>
            <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>