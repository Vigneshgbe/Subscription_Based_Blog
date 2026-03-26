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

// ============= ENHANCED USAGE LIMIT ENFORCEMENT =============
$canRead = false;
$blockReason = '';
$hasSubscription = isLoggedIn() ? hasActiveSubscription($_SESSION['user_id']) : false;
$freeRemaining = getFreeArticlesRemaining();

// Determine if user can read this article
if (!$article['is_premium']) {
    $canRead = true;
} elseif (isLoggedIn()) {
    if ($hasSubscription) {
        $canRead = true;
    } else {
        if ($freeRemaining > 0) {
            $canRead = true;
            recordArticleRead($article['id']);
        } else {
            $canRead = false;
            $blockReason = 'limit_reached';
        }
    }
} else {
    $canRead = false;
    $blockReason = 'not_logged_in';
}

// If blocked, show paywall page and exit
if (!$canRead && $article['is_premium']) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Premium Content - <?php echo SITE_NAME; ?></title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Playfair+Display:wght@700;800;900&display=swap" rel="stylesheet">
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body {
                font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
                display: flex;
                align-items: center;
                justify-content: center;
                min-height: 100vh;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                padding: 20px;
            }
            .paywall-container {
                max-width: 580px;
                width: 100%;
                text-align: center;
                background: rgba(255, 255, 255, 0.97);
                backdrop-filter: blur(10px);
                border-radius: 24px;
                /* FIX: padding-top increased so icon has room; overflow visible */
                padding: 48px 48px 56px;
                box-shadow: 0 25px 70px rgba(0, 0, 0, 0.3);
                color: #1a1a1a;
                overflow: visible;
            }
            /* FIX: icon wrapper with explicit size control, no clipping */
            .paywall-icon-wrap {
                display: flex;
                align-items: center;
                justify-content: center;
                margin-bottom: 24px;
                line-height: 1;
            }
            .paywall-icon {
                font-size: 72px;
                line-height: 1;
                display: block;
                /* FIX: prevent emoji font from rendering as outline/letter */
                font-family: "Apple Color Emoji", "Segoe UI Emoji", "Noto Color Emoji", sans-serif;
                animation: pulse 2s infinite;
            }
            @keyframes pulse {
                0%, 100% { transform: scale(1); }
                50% { transform: scale(1.06); }
            }
            h1 {
                font-size: 36px;
                font-weight: 900;
                margin-bottom: 14px;
                font-family: 'Playfair Display', serif;
                color: #0a0a0a;
                line-height: 1.2;
            }
            p {
                font-size: 17px;
                margin-bottom: 28px;
                color: #4a5568;
                line-height: 1.6;
            }
            .stats-box {
                background: linear-gradient(135deg, #FF6B6B 0%, #E74C3C 100%);
                color: white;
                padding: 28px 24px;
                border-radius: 16px;
                margin-bottom: 28px;
            }
            /* FIX: force number font to Inter so "0" renders as digit not serif O */
            .stats-box .number {
                font-size: 60px;
                font-weight: 900;
                font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
                margin-bottom: 8px;
                line-height: 1;
                font-variant-numeric: tabular-nums;
                letter-spacing: -2px;
            }
            .stats-box .label {
                font-size: 13px;
                text-transform: uppercase;
                letter-spacing: 1.5px;
                opacity: 0.92;
                font-weight: 600;
            }
            .benefits {
                text-align: left;
                margin: 24px 0;
                background: #f9fafb;
                padding: 24px 28px;
                border-radius: 12px;
            }
            .benefit {
                display: flex;
                align-items: flex-start;
                gap: 12px;
                margin-bottom: 12px;
                font-size: 15px;
                color: #1a1a1a;
                line-height: 1.5;
            }
            .benefit:last-child { margin-bottom: 0; }
            .benefit::before {
                content: '✓';
                color: #10b981;
                font-weight: 900;
                font-size: 18px;
                flex-shrink: 0;
                margin-top: 1px;
            }
            .btn-group {
                display: flex;
                flex-direction: column;
                gap: 12px;
                margin-top: 8px;
            }
            .btn {
                display: block;
                width: 100%;
                padding: 15px 32px;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                text-decoration: none;
                border-radius: 12px;
                font-weight: 700;
                font-size: 16px;
                transition: all 0.25s;
                box-shadow: 0 6px 20px rgba(102, 126, 234, 0.35);
                text-align: center;
            }
            .btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 10px 28px rgba(102, 126, 234, 0.45);
            }
            .btn-outline {
                background: transparent;
                border: 2px solid #667eea;
                color: #667eea;
                box-shadow: none;
            }
            .btn-outline:hover {
                background: #667eea;
                color: white;
            }

            /* ===== RESPONSIVE ===== */
            @media (max-width: 600px) {
                body { padding: 16px; align-items: flex-start; padding-top: 32px; }
                .paywall-container {
                    padding: 40px 24px 48px;
                    border-radius: 20px;
                }
                h1 { font-size: 28px; }
                p { font-size: 15px; }
                .paywall-icon { font-size: 60px; }
                .stats-box .number { font-size: 52px; }
                .benefits { padding: 20px; }
                .benefit { font-size: 14px; }
            }
            @media (max-width: 380px) {
                .paywall-container { padding: 32px 18px 40px; border-radius: 16px; }
                h1 { font-size: 24px; }
                .stats-box .number { font-size: 44px; }
                .btn { font-size: 14px; padding: 13px 24px; }
            }
        </style>
    </head>
    <body>
        <div class="paywall-container">
            <div class="paywall-icon-wrap">
                <span class="paywall-icon">🔒</span>
            </div>
            
            <?php if ($blockReason === 'not_logged_in'): ?>
                <h1>Premium Content</h1>
                <p>Sign in to access this premium article and start your free reading allowance.</p>
                
                <div class="stats-box">
                    <div class="number">5</div>
                    <div class="label">Free Premium Articles</div>
                </div>
                
                <div class="benefits">
                    <div class="benefit">Read 5 free premium articles every month</div>
                    <div class="benefit">No credit card required to start</div>
                    <div class="benefit">Upgrade anytime for unlimited access</div>
                </div>
                
                <div class="btn-group">
                    <a href="login.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" class="btn">Sign In to Read</a>
                    <a href="register.php" class="btn btn-outline">Create Free Account</a>
                </div>
                
            <?php else: // limit_reached ?>
                <h1>You've Reached Your Limit</h1>
                <p>You've read all your free premium articles this month. Subscribe for unlimited access to our entire library.</p>
                
                <div class="stats-box">
                    <div class="number">0</div>
                    <div class="label">Free Articles Remaining</div>
                </div>
                
                <div class="benefits">
                    <div class="benefit">Unlimited access to all premium content</div>
                    <div class="benefit">Ad-free reading experience</div>
                    <div class="benefit">Support quality journalism</div>
                    <div class="benefit">Cancel anytime, no strings attached</div>
                </div>
                
                <div class="btn-group">
                    <a href="pricing.php" class="btn">View Subscription Plans</a>
                    <a href="index.php" class="btn btn-outline">Back to Home</a>
                </div>
            <?php endif; ?>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// If user can read, update view count (only once per session)
if ($canRead && !isset($_SESSION['viewed_article_' . $article['id']])) {
    $updateViews = $db->prepare("UPDATE articles SET views = views + 1 WHERE id = ?");
    $updateViews->execute([$article['id']]);
    $_SESSION['viewed_article_' . $article['id']] = true;
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
$readingTime = ceil($wordCount / 200);
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
            padding: 18px 0;
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
        
        .logo:hover { opacity: 0.8; }
        
        .nav {
            display: flex;
            gap: 20px;
            align-items: center;
        }
        
        .nav a {
            color: var(--text);
            text-decoration: none;
            font-weight: 500;
            font-size: 15px;
            transition: color 0.2s;
        }
        
        .nav a:hover { color: var(--accent); }
        
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
            flex-shrink: 0;
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
            max-width: 740px;
            margin: 32px auto;
            padding: 0 24px;
        }
        
        .featured-image img {
            width: 100%;
            height: auto;
            max-height: 420px;
            object-fit: cover;
            display: block;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        
        /* Article Content */
        .article-content {
            max-width: 680px;
            margin: 0 auto 64px;
            padding: 0 24px;
            font-size: 19px;
            line-height: 1.8;
            color: var(--text);
            font-family: 'Merriweather', Georgia, serif;
        }
        
        .article-content > * { margin-bottom: 28px; }
        
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
            margin-left: 28px;
            margin-bottom: 28px;
            line-height: 1.8;
        }
        
        .article-content li {
            margin-bottom: 12px;
            padding-left: 6px;
        }
        
        .article-content ul li::marker { color: var(--accent); }
        
        .article-content blockquote {
            border-left: 4px solid var(--accent);
            padding-left: 28px;
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
        
        .article-content a:hover { color: var(--accent-dark); }
        
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
        .article-content i { font-style: italic; }
        
        .article-content hr {
            border: none;
            border-top: 2px solid var(--border);
            margin: 48px 0;
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
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 28px;
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
            font-size: 20px;
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
        
        .related-card h3 a:hover { color: var(--accent); }
        
        .related-card-excerpt {
            color: var(--text-light);
            font-size: 14px;
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
            gap: 28px;
            margin-bottom: 24px;
            flex-wrap: wrap;
            padding: 0 24px;
        }
        
        .footer a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            font-weight: 500;
            font-size: 15px;
            transition: color 0.2s;
        }
        
        .footer a:hover { color: var(--accent); }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 900px) {
            .article-title { font-size: 40px; }
            .article-content { font-size: 18px; }
            .related-articles h2 { font-size: 34px; }
        }

        @media (max-width: 768px) {
            .article-header { margin: 36px auto 32px; }
            .article-title { font-size: 32px; }
            .article-excerpt { font-size: 17px; }
            
            .article-content {
                font-size: 17px;
                padding: 0 20px;
            }
            .article-content h2 { font-size: 26px; margin: 40px 0 18px; }
            .article-content h3 { font-size: 22px; margin: 32px 0 16px; }
            .article-content h4 { font-size: 19px; }
            .article-content blockquote {
                font-size: 20px;
                padding-left: 20px;
                margin: 28px 0;
            }
            .article-content ul,
            .article-content ol { margin-left: 20px; }
            
            .article-meta {
                flex-direction: column;
                align-items: flex-start;
                gap: 14px;
            }
            
            .featured-image { margin: 20px auto; }
            .featured-image img { max-height: 260px; }
            
            .related-articles { padding: 56px 0; margin-top: 56px; }
            .related-articles h2 { font-size: 28px; margin-bottom: 32px; }
            .related-grid { grid-template-columns: 1fr; gap: 20px; }
            .related-card { padding: 22px; }
            
            .footer-links { gap: 16px; }
            .footer a { font-size: 14px; }
        }
        
        @media (max-width: 480px) {
            .container { padding: 0 16px; }
            .header-content { padding: 14px 0; }
            .logo { font-size: 20px; }
            .nav { gap: 14px; }
            .nav a { font-size: 14px; }
            
            .article-header { margin: 24px auto 24px; padding: 0 16px; }
            .article-title { font-size: 26px; letter-spacing: -0.3px; }
            .article-excerpt { font-size: 16px; margin-bottom: 24px; }
            .article-category { font-size: 11px; }
            
            .article-content { font-size: 16px; padding: 0 16px; }
            .article-content p { text-align: left; }
            .article-content h2 { font-size: 22px; margin: 32px 0 14px; }
            .article-content h3 { font-size: 19px; }
            .article-content blockquote { font-size: 17px; padding-left: 16px; }
            .article-content code { font-size: 14px; }

            .featured-image { padding: 0 16px; }
            .featured-image img { max-height: 200px; border-radius: 6px; }
            
            .related-grid { padding: 0 16px; }
            .related-card h3 { font-size: 18px; }

            .footer { padding: 36px 0 20px; }
            .footer-links { gap: 12px; }
        }

        @media (max-width: 360px) {
            .article-title { font-size: 22px; }
            .article-content { font-size: 15px; }
            .nav a:not(:first-child) { display: none; }
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
        
        <div class="article-content">
            <?php echo $article['content']; ?>
        </div>
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