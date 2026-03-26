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

// Estimate reading time
$wordCount = str_word_count(strip_tags($article['content']));
$readingTime = ceil($wordCount / 200); // Average reading speed: 200 words/min
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
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Playfair+Display:wght@400;500;600;700;800;900&family=Merriweather:ital,wght@0,300;0,400;0,700;0,900;1,300;1,400&display=swap" rel="stylesheet">
    
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
            --text-light: #4a5568;
            --text-lighter: #718096;
            --border: #e5e7eb;
            --bg-light: #f9fafb;
            --premium: #FFD700;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            line-height: 1.6;
            color: var(--text);
            background: var(--secondary);
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        
        /* Header */
        .header {
            background: var(--secondary);
            position: sticky;
            top: 0;
            z-index: 1000;
            border-bottom: 1px solid var(--border);
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 24px;
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 0;
        }
        
        .logo {
            font-size: 24px;
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
            gap: 24px;
            align-items: center;
        }
        
        .nav a {
            color: var(--text);
            text-decoration: none;
            font-weight: 500;
            font-size: 15px;
            transition: color 0.2s;
        }
        
        .nav a:hover {
            color: var(--accent);
        }
        
        /* Article Header */
        .article-header {
            max-width: 740px;
            margin: 48px auto 40px;
            padding: 0 24px;
        }
        
        .article-category {
            display: inline-block;
            background: var(--primary);
            color: var(--secondary);
            padding: 6px 14px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .article-title {
            font-size: 48px;
            font-weight: 800;
            line-height: 1.15;
            margin-bottom: 20px;
            letter-spacing: -0.5px;
            font-family: 'Playfair Display', serif;
            color: var(--primary);
        }
        
        .article-excerpt {
            font-size: 20px;
            color: var(--text-light);
            margin-bottom: 32px;
            line-height: 1.6;
            font-weight: 400;
        }
        
        .article-meta {
            display: flex;
            align-items: center;
            gap: 24px;
            padding: 24px 0;
            border-top: 1px solid var(--border);
            border-bottom: 1px solid var(--border);
            font-size: 14px;
            color: var(--text-lighter);
            flex-wrap: wrap;
        }
        
        .article-meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
        }
        
        .author-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .author-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--accent), var(--accent-dark));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 16px;
        }
        
        .author-details strong {
            display: block;
            color: var(--text);
            font-weight: 600;
            font-size: 15px;
        }
        
        .author-details span {
            font-size: 13px;
            color: var(--text-lighter);
        }
        
        /* Featured Image */
        .featured-image {
            width: 100%;
            max-width: 740px;  /* Match article content width */
            margin: 32px auto;  /* Reduced from 48px */
            padding: 0 24px;
        }
        
        .featured-image img {
            width: 100%;
            height: auto;
            max-height: 420px;  /* ADD THIS - caps the height */
            object-fit: cover;  /* ADD THIS - crops intelligently */
            display: block;
            border-radius: 8px;  /* Reduced from 12px for subtlety */
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);  /* Softer shadow */
        }
        
        /* Article Content - THE MOST IMPORTANT PART */
        .article-content {
            max-width: 680px;
            margin: 0 auto 64px;
            padding: 0 24px;
            font-size: 19px;
            line-height: 1.8;
            color: var(--text);
            font-family: 'Merriweather', Georgia, serif;
        }
        
        .article-content > * {
            margin-bottom: 28px;
        }
        
        .article-content p {
            margin-bottom: 28px;
            text-align: justify;
        }
        
        .article-content h2 {
            font-size: 36px;
            font-weight: 800;
            margin: 56px 0 24px;
            line-height: 1.3;
            color: var(--primary);
            font-family: 'Playfair Display', serif;
            letter-spacing: -0.5px;
        }
        
        .article-content h3 {
            font-size: 28px;
            font-weight: 700;
            margin: 40px 0 20px;
            line-height: 1.3;
            color: var(--primary);
            font-family: 'Playfair Display', serif;
        }
        
        .article-content h4 {
            font-size: 22px;
            font-weight: 700;
            margin: 32px 0 16px;
            color: var(--primary);
        }
        
        .article-content ul,
        .article-content ol {
            margin-left: 32px;
            margin-bottom: 28px;
            line-height: 1.8;
        }
        
        .article-content li {
            margin-bottom: 12px;
            padding-left: 8px;
        }
        
        .article-content ul li::marker {
            color: var(--accent);
        }
        
        .article-content blockquote {
            border-left: 4px solid var(--accent);
            padding-left: 32px;
            margin: 40px 0;
            font-style: italic;
            font-size: 24px;
            line-height: 1.6;
            color: var(--text-light);
            font-family: 'Playfair Display', serif;
        }
        
        .article-content a {
            color: var(--accent);
            text-decoration: underline;
            font-weight: 600;
            transition: color 0.2s;
        }
        
        .article-content a:hover {
            color: var(--accent-dark);
        }
        
        .article-content img {
            width: 100%;
            height: auto;
            max-height: 500px;  
            object-fit: cover;  
            border-radius: 6px;  
            margin: 32px 0;  
            box-shadow: 0 2px 6px rgba(0,0,0,0.06);  
        }
        
        .article-content code {
            background: var(--bg-light);
            padding: 3px 8px;
            border-radius: 4px;
            font-family: 'Monaco', 'Courier New', monospace;
            font-size: 16px;
            color: var(--accent-dark);
        }
        
        .article-content pre {
            background: var(--bg-light);
            padding: 24px;
            border-radius: 8px;
            overflow-x: auto;
            margin: 32px 0;
            border: 1px solid var(--border);
        }
        
        .article-content pre code {
            background: none;
            padding: 0;
            font-size: 14px;
            color: var(--text);
        }
        
        .article-content strong,
        .article-content b {
            font-weight: 700;
            color: var(--primary);
        }
        
        .article-content em,
        .article-content i {
            font-style: italic;
        }
        
        .article-content hr {
            border: none;
            border-top: 2px solid var(--border);
            margin: 48px 0;
        }
        
        /* Paywall */
        .paywall-overlay {
            position: relative;
            max-width: 680px;
            margin: 40px auto;
            padding: 0 24px;
        }
        
        .blurred-content {
            filter: blur(8px);
            user-select: none;
            pointer-events: none;
            opacity: 0.4;
        }
        
        .paywall-box {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            border-radius: 16px;
            padding: 48px;
            text-align: center;
            max-width: 500px;
            width: 90%;
            box-shadow: 0 20px 60px rgba(0,0,0,0.2);
            border: 1px solid var(--border);
        }
        
        .paywall-icon {
            font-size: 48px;
            margin-bottom: 16px;
        }
        
        .paywall-box h2 {
            font-size: 32px;
            margin-bottom: 16px;
            font-family: 'Playfair Display', serif;
            color: var(--primary);
        }
        
        .paywall-box p {
            font-size: 17px;
            margin-bottom: 28px;
            color: var(--text-light);
            line-height: 1.6;
        }
        
        .paywall-stats {
            background: linear-gradient(135deg, var(--bg-light) 0%, var(--secondary) 100%);
            padding: 32px;
            margin-bottom: 28px;
            border-radius: 12px;
            border: 2px solid var(--border);
        }
        
        .paywall-stats .number {
            font-size: 56px;
            font-weight: 900;
            color: var(--accent);
            margin-bottom: 8px;
            font-family: 'Playfair Display', serif;
        }
        
        .paywall-stats .label {
            font-size: 14px;
            color: var(--text-lighter);
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
        }
        
        .paywall-benefits {
            text-align: left;
            margin: 28px 0;
        }
        
        .paywall-benefit {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
            font-size: 15px;
            color: var(--text);
        }
        
        .paywall-benefit::before {
            content: '✓';
            color: var(--accent);
            font-weight: 900;
            font-size: 18px;
        }
        
        .btn {
            padding: 14px 32px;
            border: none;
            font-weight: 700;
            font-size: 15px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
            border-radius: 8px;
            font-family: 'Inter', sans-serif;
        }
        
        .btn-primary {
            background: var(--accent);
            color: white;
            box-shadow: 0 4px 12px rgba(255, 107, 107, 0.3);
        }
        
        .btn-primary:hover {
            background: var(--accent-dark);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(255, 107, 107, 0.4);
        }
        
        .btn-outline {
            background: transparent;
            border: 2px solid var(--primary);
            color: var(--primary);
            margin-left: 12px;
        }
        
        .btn-outline:hover {
            background: var(--primary);
            color: var(--secondary);
        }
        
        /* Related Articles */
        .related-articles {
            background: var(--bg-light);
            padding: 80px 0;
            margin-top: 80px;
        }
        
        .related-articles h2 {
            text-align: center;
            font-size: 40px;
            margin-bottom: 48px;
            font-weight: 800;
            font-family: 'Playfair Display', serif;
            color: var(--primary);
        }
        
        .related-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 32px;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 24px;
        }
        
        .related-card {
            background: white;
            border-radius: 12px;
            padding: 28px;
            transition: all 0.3s;
            border: 1px solid var(--border);
        }
        
        .related-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
            border-color: var(--primary);
        }
        
        .related-card-category {
            font-size: 11px;
            text-transform: uppercase;
            color: var(--text-lighter);
            margin-bottom: 12px;
            font-weight: 700;
            letter-spacing: 1px;
        }
        
        .related-card h3 {
            font-size: 22px;
            margin-bottom: 12px;
            font-weight: 700;
            line-height: 1.4;
            font-family: 'Playfair Display', serif;
        }
        
        .related-card h3 a {
            color: var(--text);
            text-decoration: none;
            transition: color 0.2s;
        }
        
        .related-card h3 a:hover {
            color: var(--accent);
        }
        
        .related-card-excerpt {
            color: var(--text-light);
            font-size: 15px;
            line-height: 1.6;
        }
        
        /* Footer */
        .footer {
            background: var(--primary);
            color: var(--secondary);
            padding: 48px 0 24px;
            text-align: center;
        }
        
        .footer-links {
            display: flex;
            justify-content: center;
            gap: 32px;
            margin-bottom: 24px;
            flex-wrap: wrap;
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
        
        /* Mobile Responsive */
        @media (max-width: 768px) {
            .article-title {
                font-size: 32px;
            }
            
            .article-excerpt {
                font-size: 18px;
            }
            
            .article-content {
                font-size: 17px;
                padding: 0 20px;
            }
            
            .article-content h2 {
                font-size: 28px;
                margin: 40px 0 20px;
            }
            
            .article-content h3 {
                font-size: 24px;
            }
            
            .article-content blockquote {
                font-size: 20px;
                padding-left: 24px;
                margin: 32px 0;
            }
            
            .paywall-box {
                padding: 32px 24px;
            }
            
            .paywall-stats {
                padding: 24px;
            }
            
            .paywall-stats .number {
                font-size: 48px;
            }
            
            .btn-outline {
                margin-left: 0;
                margin-top: 12px;
                display: block;
                width: 100%;
            }
            
            .article-meta {
                flex-direction: column;
                align-items: flex-start;
                gap: 16px;
            }
            
            .related-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .featured-image {
                margin: 24px auto;  /* Even tighter on mobile */
            }
            
            .featured-image img {
                max-height: 280px;  /* Mobile-friendly height */
                border-radius: 6px;
            }
        }
        
        @media (max-width: 480px) {
            .article-title {
                font-size: 28px;
            }
            
            .article-header {
                margin: 32px auto 32px;
            }
            
            .featured-image {
                margin: 24px auto;
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
                    <a href="index.php">← Articles</a>
                    <?php if (!isLoggedIn()): ?>
                        <a href="login.php">Login</a>
                    <?php endif; ?>
                </nav>
            </div>
        </div>
    </header>

    <article>
        <div class="article-header">
            <?php if ($article['category_name']): ?>
                <span class="article-category"><?php echo htmlspecialchars($article['category_name']); ?></span>
            <?php endif; ?>
            
            <h1 class="article-title"><?php echo htmlspecialchars($article['title']); ?></h1>
            
            <?php if ($article['excerpt']): ?>
                <p class="article-excerpt"><?php echo htmlspecialchars($article['excerpt']); ?></p>
            <?php endif; ?>
            
            <div class="article-meta">
                <div class="author-info">
                    <div class="author-avatar">
                        <?php echo strtoupper(substr($article['author_name'], 0, 1)); ?>
                    </div>
                    <div class="author-details">
                        <strong><?php echo htmlspecialchars($article['author_name']); ?></strong>
                        <span><?php echo formatDate($article['published_at']); ?></span>
                    </div>
                </div>
                <div class="article-meta-item">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <?php echo $readingTime; ?> min read
                </div>
                <div class="article-meta-item">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <?php echo number_format($article['views']); ?> views
                </div>
            </div>
        </div>
        
        <?php if ($article['featured_image']): ?>
            <div class="featured-image">
                <img src="<?php echo htmlspecialchars($article['featured_image']); ?>" 
                     alt="<?php echo htmlspecialchars($article['title']); ?>">
            </div>
        <?php endif; ?>
        
        <?php if ($canRead): ?>
            <div class="article-content">
                <?php echo $article['content']; ?>
            </div>
        <?php else: ?>
            <div class="paywall-overlay">
                <div class="article-content blurred-content">
                    <?php echo substr(strip_tags($article['content']), 0, 400); ?>...
                </div>
                
                <div class="paywall-box">
                    <div class="paywall-icon">🔒</div>
                    <h2>Continue Reading</h2>
                    
                    <?php if (!isLoggedIn()): ?>
                        <p>Join thousands of readers getting premium insights delivered daily.</p>
                        
                        <div class="paywall-stats">
                            <div class="number"><?php echo $freeRemaining; ?></div>
                            <div class="label">Free articles remaining</div>
                        </div>
                        
                        <div class="paywall-benefits">
                            <div class="paywall-benefit">Unlimited access to all articles</div>
                            <div class="paywall-benefit">Ad-free reading experience</div>
                            <div class="paywall-benefit">Support quality journalism</div>
                        </div>
                        
                        <a href="register.php" class="btn btn-primary">Subscribe Now</a>
                        <a href="login.php" class="btn btn-outline">Sign In</a>
                    <?php else: ?>
                        <p>Upgrade to premium and unlock unlimited access to our entire library of in-depth articles.</p>
                        
                        <div class="paywall-stats">
                            <div class="number">₹299</div>
                            <div class="label">Per month</div>
                        </div>
                        
                        <div class="paywall-benefits">
                            <div class="paywall-benefit">Unlimited premium articles</div>
                            <div class="paywall-benefit">Exclusive member content</div>
                            <div class="paywall-benefit">Cancel anytime</div>
                        </div>
                        
                        <a href="pricing.php" class="btn btn-primary">Upgrade Now</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </article>

    <?php if (!empty($relatedArticles)): ?>
    <section class="related-articles">
        <div class="container">
            <h2>Continue Reading</h2>
            <div class="related-grid">
                <?php foreach ($relatedArticles as $related): ?>
                <div class="related-card">
                    <?php if ($related['category_name']): ?>
                        <div class="related-card-category">
                            <?php echo htmlspecialchars($related['category_name']); ?>
                        </div>
                    <?php endif; ?>
                    <h3>
                        <a href="article.php?slug=<?php echo htmlspecialchars($related['slug']); ?>">
                            <?php echo htmlspecialchars($related['title']); ?>
                        </a>
                    </h3>
                    <p class="related-card-excerpt">
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
            <div class="footer-links">
                <a href="index.php">Home</a>
                <a href="pricing.php">Pricing</a>
                <a href="#">About</a>
                <a href="#">Contact</a>
                <a href="#">Privacy</a>
                <a href="#">Terms</a>
            </div>
            <p style="color: rgba(255, 255, 255, 0.6); font-size: 14px;">
                &copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.
            </p>
        </div>
    </footer>
</body>
</html>