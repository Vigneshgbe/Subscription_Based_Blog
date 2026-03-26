<?php
require_once 'config.php';

$slug = $_GET['slug'] ?? '';

if (empty($slug)) {
    redirect('index.php');
}

$db = db();
$stmt = $db->prepare("
    SELECT a.*, u.full_name as author_name, c.name as category_name, c.slug as category_slug
    FROM articles a
    LEFT JOIN users u ON a.author_id = u.id
    LEFT JOIN categories c ON a.category_id = c.id
    WHERE a.slug = ? AND a.is_published = 1
    LIMIT 1
");
$stmt->execute([$slug]);
$article = $stmt->fetch();

if (!$article) {
    flashMessage('danger', 'Article not found');
    redirect('index.php');
}

// Check if user can read this article
$canRead = canReadArticle($article['id']);
$hasSubscription = isLoggedIn() ? hasActiveSubscription($_SESSION['user_id']) : false;
$freeRemaining = getFreeArticlesRemaining();

// Record article view if user can read
if ($canRead) {
    recordArticleRead($article['id']);
}

// Get related articles
$relatedStmt = $db->prepare("
    SELECT a.*, c.name as category_name
    FROM articles a
    LEFT JOIN categories c ON a.category_id = c.id
    WHERE a.is_published = 1 
    AND a.id != ?
    AND (a.category_id = ? OR a.category_id IS NULL)
    ORDER BY RAND()
    LIMIT 3
");
$relatedStmt->execute([$article['id'], $article['category_id']]);
$relatedArticles = $relatedStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($article['meta_title'] ?: $article['title']); ?> - <?php echo SITE_NAME; ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($article['meta_description'] ?: $article['excerpt']); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars($article['meta_keywords']); ?>">
    <meta name="author" content="<?php echo htmlspecialchars($article['author_name']); ?>">
    
    <!-- Open Graph -->
    <meta property="og:title" content="<?php echo htmlspecialchars($article['title']); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($article['excerpt']); ?>">
    <meta property="og:image" content="<?php echo htmlspecialchars($article['featured_image']); ?>">
    <meta property="og:url" content="<?php echo SITE_URL . '/article.php?slug=' . $article['slug']; ?>">
    <meta property="og:type" content="article">
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo htmlspecialchars($article['title']); ?>">
    <meta name="twitter:description" content="<?php echo htmlspecialchars($article['excerpt']); ?>">
    <meta name="twitter:image" content="<?php echo htmlspecialchars($article['featured_image']); ?>">
    
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
        }
        
        body {
            font-family: 'Georgia', 'Times New Roman', serif;
            line-height: 1.8;
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
            font-size: 32px;
            font-weight: 900;
            letter-spacing: -1px;
            color: var(--primary);
            text-decoration: none;
            font-family: -apple-system, BlinkMacSystemFont, sans-serif;
        }
        
        .nav {
            display: flex;
            gap: 25px;
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
        
        .article-header {
            max-width: 800px;
            margin: 60px auto;
            text-align: center;
        }
        
        .article-category {
            display: inline-block;
            background: var(--primary);
            color: var(--secondary);
            padding: 6px 16px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 20px;
            font-family: -apple-system, BlinkMacSystemFont, sans-serif;
        }
        
        .article-title {
            font-size: 48px;
            font-weight: 900;
            line-height: 1.2;
            margin-bottom: 20px;
            letter-spacing: -1px;
        }
        
        .article-excerpt {
            font-size: 22px;
            color: var(--text-light);
            margin-bottom: 30px;
            line-height: 1.6;
        }
        
        .article-meta {
            display: flex;
            justify-content: center;
            gap: 30px;
            font-size: 14px;
            color: var(--text-light);
            font-family: -apple-system, BlinkMacSystemFont, sans-serif;
        }
        
        .article-meta span {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .featured-image {
            width: 100%;
            max-width: 1000px;
            margin: 0 auto 60px;
            display: block;
        }
        
        .featured-image img {
            width: 100%;
            height: auto;
            display: block;
            border: 4px solid var(--primary);
        }
        
        .article-content {
            max-width: 700px;
            margin: 0 auto 60px;
            font-size: 19px;
            line-height: 1.8;
        }
        
        .article-content p {
            margin-bottom: 24px;
        }
        
        .article-content h2 {
            font-size: 32px;
            margin: 40px 0 20px;
            font-weight: 900;
        }
        
        .article-content h3 {
            font-size: 24px;
            margin: 30px 0 15px;
            font-weight: 700;
        }
        
        .article-content ul, .article-content ol {
            margin-left: 30px;
            margin-bottom: 24px;
        }
        
        .article-content li {
            margin-bottom: 12px;
        }
        
        .article-content blockquote {
            border-left: 4px solid var(--primary);
            padding-left: 30px;
            margin: 30px 0;
            font-style: italic;
            font-size: 22px;
            color: var(--text-light);
        }
        
        .paywall-overlay {
            position: relative;
            max-width: 700px;
            margin: 40px auto;
        }
        
        .blurred-content {
            filter: blur(5px);
            user-select: none;
            pointer-events: none;
        }
        
        .paywall-box {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            border: 4px solid var(--primary);
            padding: 40px;
            text-align: center;
            max-width: 500px;
            width: 90%;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        
        .paywall-box h2 {
            font-size: 32px;
            margin-bottom: 15px;
            font-family: -apple-system, BlinkMacSystemFont, sans-serif;
        }
        
        .paywall-box p {
            font-size: 18px;
            margin-bottom: 25px;
            color: var(--text-light);
        }
        
        .paywall-stats {
            background: #f5f5f5;
            padding: 20px;
            margin-bottom: 25px;
            font-family: -apple-system, BlinkMacSystemFont, sans-serif;
        }
        
        .paywall-stats .number {
            font-size: 48px;
            font-weight: 900;
            color: var(--accent);
            margin-bottom: 5px;
        }
        
        .btn {
            padding: 14px 32px;
            background: var(--accent);
            color: white;
            border: none;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 14px;
            letter-spacing: 1px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
            font-family: -apple-system, BlinkMacSystemFont, sans-serif;
        }
        
        .btn:hover {
            background: #cc0044;
            transform: translateY(-2px);
        }
        
        .btn-outline {
            background: transparent;
            border: 2px solid var(--primary);
            color: var(--primary);
            margin-left: 10px;
        }
        
        .btn-outline:hover {
            background: var(--primary);
            color: var(--secondary);
        }
        
        .related-articles {
            background: #fafafa;
            padding: 60px 0;
            margin-top: 80px;
        }
        
        .related-articles h2 {
            text-align: center;
            font-size: 36px;
            margin-bottom: 40px;
            font-weight: 900;
            font-family: -apple-system, BlinkMacSystemFont, sans-serif;
        }
        
        .related-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }
        
        .related-card {
            background: white;
            border: 2px solid var(--border);
            padding: 25px;
            transition: all 0.3s;
        }
        
        .related-card:hover {
            border-color: var(--primary);
            transform: translateY(-4px);
        }
        
        .related-card h3 {
            font-size: 20px;
            margin-bottom: 10px;
            font-weight: 700;
        }
        
        .related-card h3 a {
            color: var(--text);
            text-decoration: none;
        }
        
        .related-card h3 a:hover {
            color: var(--accent);
        }
        
        .footer {
            background: var(--primary);
            color: var(--secondary);
            padding: 40px 0;
            text-align: center;
            font-family: -apple-system, BlinkMacSystemFont, sans-serif;
        }
        
        @media (max-width: 768px) {
            .article-title {
                font-size: 32px;
            }
            
            .article-excerpt {
                font-size: 18px;
            }
            
            .article-content {
                font-size: 17px;
            }
            
            .paywall-box {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="header-content">
                <a href="index.php" class="logo"><?php echo SITE_NAME; ?></a>
                <nav class="nav">
                    <a href="index.php">← Back to Articles</a>
                    <?php if (!isLoggedIn()): ?>
                        <a href="login.php">Login</a>
                    <?php endif; ?>
                </nav>
            </div>
        </div>
    </header>

    <article>
        <div class="container">
            <div class="article-header">
                <?php if ($article['category_name']): ?>
                    <span class="article-category"><?php echo htmlspecialchars($article['category_name']); ?></span>
                <?php endif; ?>
                
                <h1 class="article-title"><?php echo htmlspecialchars($article['title']); ?></h1>
                
                <?php if ($article['excerpt']): ?>
                    <p class="article-excerpt"><?php echo htmlspecialchars($article['excerpt']); ?></p>
                <?php endif; ?>
                
                <div class="article-meta">
                    <span>✍️ <?php echo htmlspecialchars($article['author_name']); ?></span>
                    <span>📅 <?php echo formatDate($article['published_at']); ?></span>
                    <span>👁 <?php echo number_format($article['views']); ?> views</span>
                </div>
            </div>
        </div>
        
        <?php if ($article['featured_image']): ?>
            <div class="featured-image">
                <img src="<?php echo htmlspecialchars($article['featured_image']); ?>" 
                     alt="<?php echo htmlspecialchars($article['title']); ?>">
            </div>
        <?php endif; ?>
        
        <div class="container">
            <?php if ($canRead): ?>
                <div class="article-content">
                    <?php echo $article['content']; ?>
                </div>
            <?php else: ?>
                <div class="paywall-overlay">
                    <div class="article-content blurred-content">
                        <?php echo substr($article['content'], 0, 500); ?>...
                        Lorem ipsum dolor sit amet, consectetur adipiscing elit...
                    </div>
                    
                    <div class="paywall-box">
                        <h2>🔒 Premium Content</h2>
                        
                        <?php if (!isLoggedIn()): ?>
                            <p>You've reached your free article limit. Subscribe to continue reading.</p>
                            <div class="paywall-stats">
                                <div class="number"><?php echo $freeRemaining; ?></div>
                                <div>Free articles remaining</div>
                            </div>
                            <a href="register.php" class="btn">Subscribe Now</a>
                            <a href="login.php" class="btn btn-outline">Sign In</a>
                        <?php else: ?>
                            <p>Upgrade to premium to unlock unlimited articles.</p>
                            <div class="paywall-stats">
                                <div class="number">₹299</div>
                                <div>Per month</div>
                            </div>
                            <a href="pricing.php" class="btn">Upgrade Now</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </article>

    <?php if (!empty($relatedArticles)): ?>
    <section class="related-articles">
        <div class="container">
            <h2>Related Articles</h2>
            <div class="related-grid">
                <?php foreach ($relatedArticles as $related): ?>
                <div class="related-card">
                    <?php if ($related['category_name']): ?>
                        <div style="font-size: 11px; text-transform: uppercase; color: #999; margin-bottom: 10px; font-weight: 700; letter-spacing: 1px; font-family: -apple-system, BlinkMacSystemFont, sans-serif;">
                            <?php echo htmlspecialchars($related['category_name']); ?>
                        </div>
                    <?php endif; ?>
                    <h3>
                        <a href="article.php?slug=<?php echo htmlspecialchars($related['slug']); ?>">
                            <?php echo htmlspecialchars($related['title']); ?>
                        </a>
                    </h3>
                    <p style="color: #666; font-size: 15px; margin-top: 10px;">
                        <?php echo htmlspecialchars(truncateText(strip_tags($related['content']), 120)); ?>
                    </p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <footer class="footer">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>