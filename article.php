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

// ============= ACCESS CONTROL =============
$canRead      = false;
$blockReason  = '';
$hasSubscription = isLoggedIn() ? hasActiveSubscription($_SESSION['user_id']) : false;
$freeRemaining   = getFreeArticlesRemaining();

if (!$article['is_premium']) {
    // Free article — always readable
    $canRead = true;

} elseif (isLoggedIn()) {
    if ($hasSubscription) {
        // Paid subscriber
        $canRead = true;
    } elseif ($freeRemaining > 0) {
        // Still has free reads left — consume one
        $canRead = true;
        recordArticleRead($article['id']);
    } else {
        $canRead     = false;
        $blockReason = 'limit_reached';
    }
} else {
    // Not logged in + premium article
    if ($freeRemaining > 0) {
        $canRead = true;
        recordArticleRead($article['id']);
    } else {
        $canRead     = false;
        $blockReason = 'not_logged_in';
    }
}

// ============= PAYWALL =============
if (!$canRead && $article['is_premium']) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Premium Content — <?php echo htmlspecialchars(SITE_NAME); ?></title>
        <link rel="icon" type="image/x-icon" href="https://png.pngtree.com/element_our/sm/20180518/sm_5aff60887f7d9.jpg">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Playfair+Display:wght@700;800;900&display=swap" rel="stylesheet">
        <style>
            *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

            body {
                font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
                display: flex;
                align-items: center;
                justify-content: center;
                min-height: 100vh;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                padding: 24px;
            }

            .paywall {
                max-width: 520px;
                width: 100%;
                background: #fff;
                border-radius: 24px;
                padding: 52px 48px 56px;
                box-shadow: 0 24px 64px rgba(0,0,0,.28);
                text-align: center;
                color: #1a1a1a;
            }

            /* ── Lock icon ── */
            .lock-wrap {
                display: flex;
                align-items: center;
                justify-content: center;
                margin-bottom: 24px;
            }
            .lock-icon {
                font-size: 68px;
                line-height: 1;
                /* Force color-emoji font so it renders as the padlock glyph, not an outline */
                font-family: "Apple Color Emoji","Segoe UI Emoji","Noto Color Emoji",sans-serif;
                animation: pulse 2.2s ease-in-out infinite;
            }
            @keyframes pulse {
                0%, 100% { transform: scale(1); }
                50%       { transform: scale(1.08); }
            }

            h1 {
                font-family: 'Playfair Display', serif;
                font-size: 32px;
                font-weight: 900;
                color: #0a0a0a;
                line-height: 1.2;
                margin-bottom: 12px;
            }

            .subtitle {
                font-size: 16px;
                color: #4a5568;
                line-height: 1.65;
                margin-bottom: 28px;
            }

            /* ── Stats box ── */
            .stats-box {
                background: linear-gradient(135deg, #FF6B6B 0%, #E74C3C 100%);
                color: #fff;
                border-radius: 16px;
                padding: 24px 20px;
                margin-bottom: 24px;
            }
            /* FIX: use Inter so digit "0" renders as a number, not Playfair serif "O" */
            .stats-box .stat-number {
                font-family: 'Inter', -apple-system, sans-serif;
                font-size: 56px;
                font-weight: 900;
                line-height: 1;
                margin-bottom: 6px;
                font-variant-numeric: tabular-nums;
                letter-spacing: -2px;
            }
            .stats-box .stat-label {
                font-size: 12px;
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: 1.5px;
                opacity: .9;
            }

            /* ── Benefits ── */
            .benefits {
                background: #f9fafb;
                border-radius: 12px;
                padding: 22px 24px;
                margin-bottom: 28px;
                text-align: left;
            }
            .benefit {
                display: flex;
                align-items: flex-start;
                gap: 10px;
                font-size: 14px;
                color: #1a1a1a;
                line-height: 1.5;
                padding: 7px 0;
                border-bottom: 1px solid #eee;
            }
            .benefit:last-child { border-bottom: none; padding-bottom: 0; }
            .benefit-check {
                color: #10b981;
                font-size: 17px;
                font-weight: 900;
                flex-shrink: 0;
                margin-top: 1px;
            }

            /* ── Buttons ── */
            .btn-group {
                display: flex;
                flex-direction: column;
                gap: 10px;
            }
            .btn {
                display: block;
                width: 100%;
                padding: 14px 24px;
                border-radius: 12px;
                font-family: 'Inter', sans-serif;
                font-size: 15px;
                font-weight: 700;
                text-decoration: none;
                text-align: center;
                cursor: pointer;
                transition: all .22s;
            }
            .btn-primary {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: #fff;
                box-shadow: 0 6px 18px rgba(102,126,234,.38);
            }
            .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 10px 26px rgba(102,126,234,.48); }
            .btn-outline {
                background: transparent;
                border: 2px solid #667eea;
                color: #667eea;
            }
            .btn-outline:hover { background: #667eea; color: #fff; }

            /* ── Responsive ── */
            @media (max-width: 600px) {
                body { padding: 16px; align-items: flex-start; padding-top: 40px; }
                .paywall { padding: 40px 24px 48px; border-radius: 20px; }
                h1 { font-size: 26px; }
                .subtitle { font-size: 15px; }
                .lock-icon { font-size: 56px; }
                .stats-box .stat-number { font-size: 48px; }
            }
            @media (max-width: 380px) {
                .paywall { padding: 32px 18px 40px; border-radius: 16px; }
                h1 { font-size: 22px; }
                .stats-box .stat-number { font-size: 42px; }
                .btn { font-size: 14px; padding: 13px 18px; }
            }
        </style>
    </head>
    <body>
        <div class="paywall">
            <div class="lock-wrap">
                <span class="lock-icon">🔒</span>
            </div>

            <?php if ($blockReason === 'not_logged_in'): ?>
                <h1>Premium Content</h1>
                <p class="subtitle">
                    Create a free account to start reading. Every reader gets
                    <strong><?php echo FREE_ARTICLE_LIMIT; ?> free premium articles</strong> per month.
                </p>

                <div class="stats-box">
                    <div class="stat-number"><?php echo FREE_ARTICLE_LIMIT; ?></div>
                    <div class="stat-label">Free Premium Articles / Month</div>
                </div>

                <div class="benefits">
                    <div class="benefit"><span class="benefit-check">✓</span> Read <?php echo FREE_ARTICLE_LIMIT; ?> premium articles every month for free</div>
                    <div class="benefit"><span class="benefit-check">✓</span> No credit card required to start</div>
                    <div class="benefit"><span class="benefit-check">✓</span> Upgrade anytime for unlimited access</div>
                </div>

                <div class="btn-group">
                    <a href="login.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" class="btn btn-primary">Sign In to Read</a>
                    <a href="register.php" class="btn btn-outline">Create Free Account</a>
                </div>

            <?php else: // limit_reached ?>
                <h1>You've Reached Your Limit</h1>
                <p class="subtitle">
                    You've used all <strong><?php echo FREE_ARTICLE_LIMIT; ?> free</strong> premium reads this month.
                    Subscribe for unlimited access.
                </p>

                <div class="stats-box">
                    <div class="stat-number">0</div>
                    <div class="stat-label">Free Articles Remaining</div>
                </div>

                <div class="benefits">
                    <div class="benefit"><span class="benefit-check">✓</span> Unlimited access to all premium content</div>
                    <div class="benefit"><span class="benefit-check">✓</span> Ad-free reading experience</div>
                    <div class="benefit"><span class="benefit-check">✓</span> Support quality journalism</div>
                    <div class="benefit"><span class="benefit-check">✓</span> Cancel anytime, no strings attached</div>
                </div>

                <div class="btn-group">
                    <a href="pricing.php" class="btn btn-primary">View Subscription Plans — from ₹299/mo</a>
                    <a href="index.php" class="btn btn-outline">Back to Home</a>
                </div>
            <?php endif; ?>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// ============= VIEW COUNT (deduplicated per session) =============
if ($canRead && !isset($_SESSION['viewed_article_' . $article['id']])) {
    $db->prepare("UPDATE articles SET views = views + 1 WHERE id = ?")->execute([$article['id']]);
    $_SESSION['viewed_article_' . $article['id']] = true;
}

// ============= RELATED ARTICLES =============
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

$wordCount   = str_word_count(strip_tags($article['content']));
$readingTime = max(1, ceil($wordCount / 200));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($article['meta_title'] ?: $article['title']); ?> — <?php echo htmlspecialchars(SITE_NAME); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($article['meta_description'] ?: $article['excerpt']); ?>">
    <meta name="keywords"    content="<?php echo htmlspecialchars($article['meta_keywords'] ?? ''); ?>">
    <meta name="author"      content="<?php echo htmlspecialchars($article['author_name']); ?>">

    <meta property="og:title"       content="<?php echo htmlspecialchars($article['title']); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($article['excerpt']); ?>">
    <meta property="og:image"       content="<?php echo htmlspecialchars($article['featured_image'] ?? ''); ?>">
    <meta property="og:url"         content="<?php echo SITE_URL . '/article.php?slug=' . urlencode($article['slug']); ?>">
    <meta property="og:type"        content="article">

    <meta name="twitter:card"        content="summary_large_image">
    <meta name="twitter:title"       content="<?php echo htmlspecialchars($article['title']); ?>">
    <meta name="twitter:description" content="<?php echo htmlspecialchars($article['excerpt']); ?>">
    <meta name="twitter:image"       content="<?php echo htmlspecialchars($article['featured_image'] ?? ''); ?>">

    <link rel="icon" type="image/x-icon" href="https://png.pngtree.com/element_our/sm/20180518/sm_5aff60887f7d9.jpg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Playfair+Display:wght@400;500;600;700;800;900&family=Merriweather:ital,wght@0,300;0,400;0,700;0,900;1,300;1,400&display=swap" rel="stylesheet">

    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --primary:      #0a0a0a;
            --secondary:    #ffffff;
            --accent:       #FF6B6B;
            --accent-dark:  #E74C3C;
            --text:         #1a1a1a;
            --text-light:   #4a5568;
            --text-lighter: #718096;
            --border:       #e5e7eb;
            --bg-light:     #f9fafb;
            --shadow:       0 4px 6px -1px rgba(0,0,0,.1);
            --shadow-lg:    0 10px 15px -3px rgba(0,0,0,.1);
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            line-height: 1.6;
            color: var(--text);
            background: var(--secondary);
            -webkit-font-smoothing: antialiased;
        }

        /* ── Header ── */
        .header {
            background: var(--secondary);
            position: sticky;
            top: 0;
            z-index: 1000;
            border-bottom: 1px solid var(--border);
            box-shadow: 0 1px 3px rgba(0,0,0,.05);
        }
        .container { max-width: 1200px; margin: 0 auto; padding: 0 24px; }
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 18px 0;
        }
        .logo {
            font-family: 'Playfair Display', serif;
            font-size: 24px;
            font-weight: 900;
            letter-spacing: -.5px;
            color: var(--primary);
            text-decoration: none;
        }
        .logo:hover { opacity: .8; }
        .nav { display: flex; gap: 20px; align-items: center; }
        .nav a { color: var(--text); text-decoration: none; font-weight: 500; font-size: 15px; transition: color .2s; }
        .nav a:hover { color: var(--accent); }

        /* ── Reading progress bar ── */
        #progress-bar {
            position: fixed;
            top: 0; left: 0;
            width: 0%;
            height: 3px;
            background: linear-gradient(90deg, var(--accent), #764ba2);
            z-index: 9999;
            transition: width .1s linear;
        }

        /* ── Free reads badge ── */
        .free-reads-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #fff3cd;
            border: 1px solid #ffc107;
            color: #856404;
            font-size: 12px;
            font-weight: 600;
            padding: 5px 12px;
            border-radius: 100px;
        }

        /* ── Article header ── */
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
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 16px;
            border-radius: 4px;
        }
        .article-title {
            font-family: 'Playfair Display', serif;
            font-size: 48px;
            font-weight: 800;
            line-height: 1.15;
            margin-bottom: 18px;
            letter-spacing: -.5px;
            color: var(--primary);
        }
        .article-excerpt {
            font-size: 20px;
            color: var(--text-light);
            margin-bottom: 28px;
            line-height: 1.6;
        }
        .article-meta {
            display: flex;
            align-items: center;
            gap: 24px;
            padding: 20px 0;
            border-top: 1px solid var(--border);
            border-bottom: 1px solid var(--border);
            font-size: 14px;
            color: var(--text-lighter);
            flex-wrap: wrap;
        }
        .article-meta-item { display: flex; align-items: center; gap: 8px; font-weight: 500; }
        .author-info { display: flex; align-items: center; gap: 12px; }
        .author-avatar {
            width: 40px; height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--accent), var(--accent-dark));
            display: flex; align-items: center; justify-content: center;
            color: white; font-weight: 700; font-size: 16px; flex-shrink: 0;
        }
        .author-details strong { display: block; color: var(--text); font-weight: 600; font-size: 15px; }
        .author-details span   { font-size: 13px; color: var(--text-lighter); }

        /* ── Featured image ── */
        .featured-image {
            width: 100%;
            max-width: 740px;
            margin: 28px auto;
            padding: 0 24px;
        }
        .featured-image img {
            width: 100%; height: auto;
            max-height: 420px;
            object-fit: cover;
            display: block;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,.08);
        }

        /* ── Article content ── */
        .article-content {
            max-width: 680px;
            margin: 0 auto 64px;
            padding: 0 24px;
            font-family: 'Merriweather', Georgia, serif;
            font-size: 19px;
            line-height: 1.85;
            color: var(--text);
        }
        .article-content > * { margin-bottom: 28px; }
        .article-content p   { margin-bottom: 28px; text-align: justify; }
        .article-content h2  {
            font-family: 'Playfair Display', serif;
            font-size: 34px; font-weight: 800;
            margin: 52px 0 22px; line-height: 1.3;
            color: var(--primary); letter-spacing: -.5px;
        }
        .article-content h3  {
            font-family: 'Playfair Display', serif;
            font-size: 26px; font-weight: 700;
            margin: 38px 0 18px; line-height: 1.3;
            color: var(--primary);
        }
        .article-content h4  { font-size: 21px; font-weight: 700; margin: 30px 0 14px; color: var(--primary); }
        .article-content ul,
        .article-content ol  { margin-left: 28px; margin-bottom: 28px; line-height: 1.8; }
        .article-content li  { margin-bottom: 10px; padding-left: 6px; }
        .article-content ul li::marker { color: var(--accent); }
        .article-content blockquote {
            border-left: 4px solid var(--accent);
            padding-left: 28px; margin: 40px 0;
            font-style: italic; font-size: 22px;
            line-height: 1.65; color: var(--text-light);
            font-family: 'Playfair Display', serif;
        }
        .article-content a   { color: var(--accent); text-decoration: underline; font-weight: 600; transition: color .2s; }
        .article-content a:hover { color: var(--accent-dark); }
        .article-content img {
            width: 100%; height: auto;
            max-height: 500px; object-fit: cover;
            border-radius: 6px; margin: 32px 0;
            box-shadow: 0 2px 6px rgba(0,0,0,.06);
        }
        .article-content code {
            background: var(--bg-light);
            padding: 3px 8px; border-radius: 4px;
            font-family: 'Monaco','Courier New',monospace;
            font-size: 15px; color: var(--accent-dark);
        }
        .article-content pre {
            background: var(--bg-light);
            padding: 22px; border-radius: 8px;
            overflow-x: auto; margin: 32px 0;
            border: 1px solid var(--border);
        }
        .article-content pre code { background: none; padding: 0; font-size: 14px; color: var(--text); }
        .article-content strong, .article-content b { font-weight: 700; color: var(--primary); }
        .article-content hr { border: none; border-top: 2px solid var(--border); margin: 48px 0; }

        /* ── Inline subscription nudge (shown when user has 1 free read left) ── */
        .subscribe-nudge {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            border-radius: 16px;
            padding: 28px 32px;
            margin: 48px 0;
            text-align: center;
        }
        .subscribe-nudge h3 { font-size: 20px; font-weight: 800; margin-bottom: 8px; }
        .subscribe-nudge p  { font-size: 14px; opacity: .85; margin-bottom: 18px; line-height: 1.5; }
        .subscribe-nudge a  {
            display: inline-block;
            background: #fff; color: #5b4cf5;
            padding: 10px 24px; border-radius: 8px;
            font-weight: 700; font-size: 14px;
            text-decoration: none; transition: all .2s;
        }
        .subscribe-nudge a:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(0,0,0,.15); }

        /* ── Related articles ── */
        .related-articles { background: var(--bg-light); padding: 72px 0; margin-top: 72px; }
        .related-articles h2 {
            text-align: center;
            font-family: 'Playfair Display', serif;
            font-size: 36px; font-weight: 800;
            color: var(--primary); margin-bottom: 40px;
        }
        .related-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(290px, 1fr));
            gap: 24px;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 24px;
        }
        .related-card {
            background: white; border-radius: 12px;
            padding: 26px; border: 1px solid var(--border);
            transition: all .3s;
        }
        .related-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-lg); border-color: var(--primary); }
        .related-card-category { font-size: 11px; text-transform: uppercase; color: var(--text-lighter); margin-bottom: 10px; font-weight: 700; letter-spacing: 1px; }
        .related-card h3 { font-family: 'Playfair Display', serif; font-size: 19px; margin-bottom: 10px; font-weight: 700; line-height: 1.4; }
        .related-card h3 a { color: var(--text); text-decoration: none; transition: color .2s; }
        .related-card h3 a:hover { color: var(--accent); }
        .related-card-excerpt { color: var(--text-light); font-size: 14px; line-height: 1.6; }

        /* ── Footer ── */
        .footer { background: var(--primary); color: var(--secondary); padding: 44px 0 22px; text-align: center; }
        .footer-links { display: flex; justify-content: center; gap: 28px; margin-bottom: 20px; flex-wrap: wrap; padding: 0 24px; }
        .footer a { color: rgba(255,255,255,.75); text-decoration: none; font-weight: 500; font-size: 14px; transition: color .2s; }
        .footer a:hover { color: var(--accent); }

        /* ── RESPONSIVE ── */
        @media (max-width: 900px) {
            .article-title   { font-size: 40px; }
            .article-content { font-size: 18px; }
            .related-articles h2 { font-size: 30px; }
        }
        @media (max-width: 768px) {
            .article-header  { margin: 32px auto 28px; }
            .article-title   { font-size: 30px; }
            .article-excerpt { font-size: 17px; }
            .article-content { font-size: 17px; padding: 0 20px; }
            .article-content h2 { font-size: 24px; margin: 38px 0 16px; }
            .article-content h3 { font-size: 21px; }
            .article-content blockquote { font-size: 19px; padding-left: 20px; }
            .article-meta { flex-direction: column; align-items: flex-start; gap: 12px; }
            .featured-image img { max-height: 250px; }
            .related-articles { padding: 52px 0; margin-top: 52px; }
            .related-grid { grid-template-columns: 1fr; }
        }
        @media (max-width: 480px) {
            .container { padding: 0 16px; }
            .header-content { padding: 14px 0; }
            .logo { font-size: 20px; }
            .nav  { gap: 12px; }
            .nav a { font-size: 13px; }
            .article-header  { margin: 20px auto 20px; padding: 0 16px; }
            .article-title   { font-size: 24px; letter-spacing: -.3px; }
            .article-excerpt { font-size: 15px; margin-bottom: 18px; }
            .article-content { font-size: 16px; padding: 0 16px; }
            .article-content p { text-align: left; }
            .article-content h2 { font-size: 21px; }
            .article-content h3 { font-size: 18px; }
            .article-content blockquote { font-size: 16px; padding-left: 16px; }
            .featured-image  { padding: 0 16px; }
            .featured-image img { max-height: 200px; }
            .related-grid    { padding: 0 16px; }
            .subscribe-nudge { padding: 22px 20px; border-radius: 12px; }
            .footer-links { gap: 12px; }
        }
        @media (max-width: 360px) {
            .article-title { font-size: 20px; }
            .nav a:not(:first-child) { display: none; }
        }
    </style>
</head>
<body>
    <div id="progress-bar"></div>

    <header class="header">
        <div class="container">
            <div class="header-content">
                <a href="index.php" class="logo"><?php echo htmlspecialchars(SITE_NAME); ?></a>
                <nav class="nav">
                    <a href="index.php">← Articles</a>
                    <?php if (!isLoggedIn()): ?>
                        <a href="login.php">Login</a>
                    <?php elseif (!$hasSubscription): ?>
                        <?php $rem = getFreeArticlesRemaining(); ?>
                        <span class="free-reads-badge">
                            📖 <?php echo $rem; ?> free read<?php echo $rem !== 1 ? 's' : ''; ?> left
                        </span>
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
                    <div class="author-avatar"><?php echo strtoupper(substr($article['author_name'], 0, 1)); ?></div>
                    <div class="author-details">
                        <strong><?php echo htmlspecialchars($article['author_name']); ?></strong>
                        <span><?php echo formatDate($article['published_at']); ?></span>
                    </div>
                </div>
                <div class="article-meta-item">
                    <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <?php echo $readingTime; ?> min read
                </div>
                <div class="article-meta-item">
                    <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    <?php echo number_format($article['views']); ?> views
                </div>
            </div>
        </div>

        <?php if (!empty($article['featured_image'])): ?>
            <div class="featured-image">
                <img src="<?php echo htmlspecialchars($article['featured_image']); ?>"
                     alt="<?php echo htmlspecialchars($article['title']); ?>">
            </div>
        <?php endif; ?>

        <div class="article-content">
            <?php echo $article['content']; ?>

            <?php
            // Show subscribe nudge inline if user is on their last free read
            $remAfter = getFreeArticlesRemaining();
            if (!$hasSubscription && isLoggedIn() && $remAfter === 0):
            ?>
            <div class="subscribe-nudge">
                <h3>You've used your last free article</h3>
                <p>Subscribe now for unlimited access to every premium article — from just ₹299/month.</p>
                <a href="pricing.php">See Plans →</a>
            </div>
            <?php endif; ?>
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
                        <div class="related-card-category"><?php echo htmlspecialchars($related['category_name']); ?></div>
                    <?php endif; ?>
                    <h3>
                        <a href="article.php?slug=<?php echo htmlspecialchars($related['slug']); ?>">
                            <?php echo htmlspecialchars($related['title']); ?>
                        </a>
                    </h3>
                    <p class="related-card-excerpt">
                        <?php echo htmlspecialchars(truncateText(strip_tags($related['content']), 110)); ?>
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
            <p style="color:rgba(255,255,255,.5);font-size:13px;">
                &copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars(SITE_NAME); ?>. All rights reserved.
            </p>
        </div>
    </footer>

    <script>
        // Reading progress bar
        window.addEventListener('scroll', () => {
            const doc    = document.documentElement;
            const scrolled  = doc.scrollTop || document.body.scrollTop;
            const total  = doc.scrollHeight - doc.clientHeight;
            const pct    = total > 0 ? (scrolled / total) * 100 : 0;
            document.getElementById('progress-bar').style.width = pct + '%';
        });
    </script>
</body>
</html>