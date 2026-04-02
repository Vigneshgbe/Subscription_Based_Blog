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
    <title><?php echo SITE_NAME; ?> - Create. Share. Inspire.</title>
    <meta name="description" content="Access premium articles, insights, and exclusive content">
    <link rel="icon" type="image/x-icon" href="https://png.pngtree.com/element_our/sm/20180518/sm_5aff60887f7d9.jpg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400;1,600&family=DM+Sans:wght@300;400;500;600;700&family=Cinzel:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --navy:        #0d0b2e;
            --navy-mid:    #13114a;
            --navy-light:  #1e1b5e;
            --purple:      #6B3FA0;
            --purple-mid:  #7C3AED;
            --purple-light:#9d6fe8;
            --gold:        #C9952A;
            --gold-bright: #F0B429;
            --gold-light:  #ffd166;
            --gold-pale:   #fff3cd;
            --white:       #ffffff;
            --off-white:   #f8f6f0;
            --text:        #1a1830;
            --text-mid:    #4a4570;
            --text-light:  #7a75a0;
            --text-lighter:#a8a4c8;
            --border:      #e4dfff;
            --border-light:#f0eeff;
            --bg-tinted:   #faf9ff;
            --shadow-gold: 0 4px 24px rgba(201,149,42,0.18);
            --shadow-purple: 0 4px 24px rgba(107,63,160,0.18);
            --shadow-navy: 0 8px 32px rgba(13,11,46,0.18);
            --shadow-lg:   0 16px 48px rgba(13,11,46,0.12);
        }

        body {
            font-family: 'DM Sans', sans-serif;
            line-height: 1.6;
            color: var(--text);
            background: var(--white);
            -webkit-font-smoothing: antialiased;
        }

        /* ─── HEADER ─────────────────────────────────────── */
        .header {
            background: var(--white);
            position: sticky;
            top: 0;
            z-index: 1000;
            border-bottom: 1px solid var(--border-light);
            box-shadow: 0 2px 16px rgba(13,11,46,0.07);
        }

        .header-top {
            background: linear-gradient(90deg, var(--navy) 0%, var(--navy-mid) 50%, var(--navy-light) 100%);
            color: var(--white);
            padding: 10px 0;
            font-size: 13px;
            font-weight: 500;
            letter-spacing: 0.5px;
        }

        .header-top-content {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
        }

        .premium-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: linear-gradient(135deg, var(--gold) 0%, var(--gold-bright) 100%);
            color: var(--navy);
            padding: 3px 10px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 11px;
            letter-spacing: 0.5px;
            font-family: 'Cinzel', serif;
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
            padding: 18px 0;
            gap: 32px;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            transition: opacity 0.3s;
        }

        .logo-image {
            height: 48px;
            width: auto;
            display: block;
        }

        .logo-text {
            display: flex;
            flex-direction: column;
            line-height: 1;
            gap: 2px;
        }

        .logo-name {
            font-family: 'Cinzel', serif;
            font-size: 22px;
            font-weight: 700;
            letter-spacing: 3px;
            background: linear-gradient(135deg, var(--gold) 0%, var(--gold-bright) 50%, var(--gold) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-transform: uppercase;
        }

        .logo-tagline {
            font-family: 'DM Sans', sans-serif;
            font-size: 9px;
            font-weight: 500;
            letter-spacing: 3px;
            color: var(--purple);
            text-transform: uppercase;
        }

        .logo:hover {
            opacity: 0.85;
        }

        .nav {
            display: flex;
            gap: 28px;
            align-items: center;
        }

        .nav a {
            color: var(--text-mid);
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
            letter-spacing: 0.3px;
            transition: color 0.2s;
            position: relative;
        }

        .nav a:hover {
            color: var(--purple);
        }

        .nav a::after {
            content: '';
            position: absolute;
            bottom: -4px;
            left: 0;
            width: 0;
            height: 2px;
            background: linear-gradient(90deg, var(--gold), var(--purple));
            transition: width 0.3s;
            border-radius: 2px;
        }

        .nav a:hover::after {
            width: 100%;
        }

        /* ─── BUTTONS ─────────────────────────────────────── */
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
            font-family: 'DM Sans', sans-serif;
            letter-spacing: 0.3px;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--gold) 0%, var(--gold-bright) 100%);
            color: var(--navy);
            font-weight: 700;
            box-shadow: var(--shadow-gold);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 28px rgba(201,149,42,0.35);
            background: linear-gradient(135deg, var(--gold-bright) 0%, var(--gold) 100%);
        }

        .btn-outline {
            background: transparent;
            border: 2px solid var(--purple);
            color: var(--purple);
        }

        .btn-outline:hover {
            background: var(--purple);
            color: var(--white);
        }

        .btn-royal {
            background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
            color: var(--white);
            box-shadow: var(--shadow-navy);
        }

        .btn-royal:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-purple);
        }

        /* ─── MOBILE MENU ─────────────────────────────────── */
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
            background: var(--navy);
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
            margin-bottom: 40px;
        }

        .mobile-close {
            background: none;
            border: none;
            font-size: 32px;
            cursor: pointer;
            color: var(--gold);
        }

        .mobile-nav-links {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .mobile-nav-links a {
            color: rgba(255,255,255,0.85);
            text-decoration: none;
            font-weight: 500;
            font-size: 18px;
            padding: 16px 0;
            border-bottom: 1px solid rgba(201,149,42,0.2);
            letter-spacing: 0.5px;
            transition: color 0.2s;
        }

        .mobile-nav-links a:hover {
            color: var(--gold-bright);
        }

        /* Mobile logo in nav */
        .mobile-nav .logo {
            flex-direction: row;
            gap: 10px;
        }

        .mobile-nav .logo-image {
            height: 40px;
        }

        /* ─── HERO ────────────────────────────────────────── */
        .hero {
            background: linear-gradient(135deg, var(--navy) 0%, var(--navy-mid) 45%, var(--navy-light) 100%);
            color: var(--white);
            padding: 88px 0 80px;
            margin-bottom: 64px;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(ellipse at 15% 50%, rgba(107,63,160,0.25) 0%, transparent 55%),
                radial-gradient(ellipse at 85% 20%, rgba(201,149,42,0.15) 0%, transparent 50%),
                radial-gradient(ellipse at 60% 90%, rgba(124,58,237,0.12) 0%, transparent 45%);
            pointer-events: none;
        }

        /* Decorative star/sparkle motifs from logo */
        .hero::after {
            content: '✦';
            position: absolute;
            top: 32px;
            right: 10%;
            font-size: 28px;
            color: var(--gold);
            opacity: 0.4;
            animation: twinkle 3s ease-in-out infinite;
        }

        @keyframes twinkle {
            0%, 100% { opacity: 0.4; transform: scale(1); }
            50%       { opacity: 0.8; transform: scale(1.2); }
        }

        .hero-inner {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            gap: 48px;
        }

        .hero-content {
            flex: 1;
            max-width: 700px;
        }

        .hero-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-family: 'Cinzel', serif;
            font-size: 11px;
            letter-spacing: 4px;
            color: var(--gold-bright);
            text-transform: uppercase;
            margin-bottom: 24px;
        }

        .hero-eyebrow::before,
        .hero-eyebrow::after {
            content: '';
            display: block;
            width: 32px;
            height: 1px;
            background: var(--gold);
            opacity: 0.7;
        }

        .hero h1 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 68px;
            font-weight: 600;
            line-height: 1.05;
            margin-bottom: 24px;
            letter-spacing: -1px;
        }

        .hero h1 em {
            font-style: italic;
            background: linear-gradient(135deg, var(--gold) 0%, var(--gold-bright) 50%, var(--gold-light) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero p {
            font-size: 18px;
            opacity: 0.8;
            line-height: 1.7;
            font-weight: 300;
            max-width: 520px;
            margin-bottom: 36px;
        }

        .hero-cta {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
        }

        .hero-ornament {
            position: absolute;
            right: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 380px;
            height: 380px;
            opacity: 0.06;
            pointer-events: none;
        }

        /* ─── SUBSCRIPTION BANNER ─────────────────────────── */
        .subscription-banner {
            background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
            color: var(--white);
            padding: 36px;
            margin: 40px 0;
            text-align: center;
            border-radius: 16px;
            box-shadow: var(--shadow-navy);
            border: 1px solid rgba(201,149,42,0.25);
            position: relative;
            overflow: hidden;
        }

        .subscription-banner::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(ellipse at 50% 0%, rgba(201,149,42,0.1) 0%, transparent 60%);
            pointer-events: none;
        }

        .subscription-banner .remaining {
            font-family: 'Cormorant Garamond', serif;
            font-size: 56px;
            font-weight: 700;
            margin: 12px 0;
            background: linear-gradient(135deg, var(--gold) 0%, var(--gold-bright) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .subscription-banner p {
            font-size: 17px;
            font-weight: 400;
            margin-bottom: 16px;
            opacity: 0.9;
        }

        .subscription-banner a {
            color: var(--gold-bright);
            text-decoration: underline;
            font-weight: 600;
            transition: opacity 0.2s;
        }

        .subscription-banner a:hover { opacity: 0.8; }

        /* ─── FILTERS ─────────────────────────────────────── */
        .filters-wrapper {
            margin-bottom: 48px;
            background: var(--bg-tinted);
            padding: 28px;
            border-radius: 14px;
            border: 1px solid var(--border-light);
        }

        .filters-label {
            font-family: 'Cinzel', serif;
            font-size: 10px;
            letter-spacing: 3px;
            color: var(--text-light);
            text-transform: uppercase;
            margin-bottom: 14px;
        }

        .filters {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .filter-btn {
            padding: 9px 20px;
            background: var(--white);
            border: 1.5px solid var(--border);
            cursor: pointer;
            font-weight: 500;
            font-size: 13px;
            transition: all 0.2s;
            text-decoration: none;
            color: var(--text-mid);
            border-radius: 24px;
            font-family: 'DM Sans', sans-serif;
            letter-spacing: 0.2px;
        }

        .filter-btn:hover {
            background: var(--navy);
            color: var(--white);
            border-color: var(--navy);
        }

        .filter-btn.active {
            background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
            color: var(--gold-bright);
            border-color: var(--navy);
            font-weight: 600;
        }

        .search-box { width: 100%; }

        .search-box input {
            width: 100%;
            padding: 14px 20px;
            border: 1.5px solid var(--border);
            font-size: 15px;
            font-family: 'DM Sans', sans-serif;
            border-radius: 10px;
            transition: all 0.2s;
            background: var(--white);
            color: var(--text);
        }

        .search-box input::placeholder { color: var(--text-lighter); }

        .search-box input:focus {
            outline: none;
            border-color: var(--purple);
            box-shadow: 0 0 0 3px rgba(107,63,160,0.1);
        }

        /* ─── SECTION HEADING ─────────────────────────────── */
        .section-heading {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 36px;
        }

        .section-heading h2 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 32px;
            font-weight: 600;
            color: var(--navy);
            letter-spacing: -0.5px;
        }

        .section-heading-line {
            flex: 1;
            height: 1px;
            background: linear-gradient(90deg, var(--gold), transparent);
            opacity: 0.4;
        }

        /* ─── ARTICLES GRID ───────────────────────────────── */
        .articles-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
            gap: 32px;
            margin-bottom: 64px;
        }

        .article-card {
            background: var(--white);
            border-radius: 14px;
            overflow: hidden;
            transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            display: flex;
            flex-direction: column;
            border: 1px solid var(--border-light);
        }

        .article-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-lg);
            border-color: rgba(107,63,160,0.2);
        }

        .article-image-wrapper {
            position: relative;
            overflow: hidden;
            background: var(--navy);
            aspect-ratio: 16/10;
        }

        .article-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            transition: transform 0.45s ease;
        }

        .article-card:hover .article-image {
            transform: scale(1.06);
        }

        .article-badge {
            position: absolute;
            top: 14px;
            right: 14px;
            background: linear-gradient(135deg, var(--gold) 0%, var(--gold-bright) 100%);
            color: var(--navy);
            padding: 6px 14px;
            font-weight: 800;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-radius: 20px;
            font-family: 'Cinzel', serif;
            box-shadow: 0 2px 12px rgba(201,149,42,0.4);
        }

        /* Placeholder image background */
        .article-placeholder {
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .article-placeholder-letter {
            font-family: 'Cormorant Garamond', serif;
            font-size: 72px;
            font-weight: 600;
            background: linear-gradient(135deg, var(--gold) 0%, var(--gold-bright) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            opacity: 0.6;
        }

        .article-content {
            padding: 28px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .article-meta {
            display: flex;
            gap: 12px;
            font-size: 12px;
            margin-bottom: 14px;
            color: var(--text-lighter);
            font-weight: 500;
            flex-wrap: wrap;
            align-items: center;
        }

        .article-meta span {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .category-tag {
            background: linear-gradient(135deg, rgba(107,63,160,0.1) 0%, rgba(124,58,237,0.08) 100%);
            color: var(--purple);
            padding: 4px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 11px;
            border: 1px solid rgba(107,63,160,0.15);
            letter-spacing: 0.3px;
        }

        .article-title {
            font-family: 'Cormorant Garamond', serif;
            font-size: 23px;
            font-weight: 600;
            margin-bottom: 12px;
            line-height: 1.35;
            color: var(--navy);
        }

        .article-title a {
            color: inherit;
            text-decoration: none;
            transition: color 0.2s;
        }

        .article-title a:hover { color: var(--purple); }

        .article-excerpt {
            color: var(--text-mid);
            margin-bottom: 22px;
            line-height: 1.7;
            flex: 1;
            font-size: 14px;
            font-weight: 300;
        }

        .read-more {
            font-weight: 600;
            font-size: 13px;
            color: var(--gold);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.25s;
            letter-spacing: 0.3px;
            border-bottom: 1px solid rgba(201,149,42,0.3);
            padding-bottom: 2px;
            width: fit-content;
        }

        .read-more:hover {
            color: var(--purple);
            border-color: rgba(107,63,160,0.4);
            gap: 12px;
        }

        .read-more svg {
            width: 15px;
            height: 15px;
            transition: transform 0.25s;
        }

        .read-more:hover svg { transform: translateX(3px); }

        /* ─── PAGINATION ──────────────────────────────────── */
        .pagination {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin: 48px 0;
            flex-wrap: wrap;
        }

        .page-link {
            padding: 10px 18px;
            border: 1.5px solid var(--border);
            color: var(--text-mid);
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.2s;
            border-radius: 8px;
            min-width: 44px;
            text-align: center;
        }

        .page-link:hover {
            background: var(--navy);
            color: var(--gold-bright);
            border-color: var(--navy);
        }

        .page-link.active {
            background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
            color: var(--gold-bright);
            border-color: var(--navy);
        }

        /* ─── FOOTER ──────────────────────────────────────── */
        .footer {
            background: linear-gradient(160deg, var(--navy) 0%, var(--navy-mid) 60%, var(--navy-light) 100%);
            color: var(--white);
            padding: 72px 0 36px;
            margin-top: 96px;
            position: relative;
            overflow: hidden;
        }

        .footer::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(ellipse at 80% 0%, rgba(201,149,42,0.08) 0%, transparent 55%);
            pointer-events: none;
        }

        .footer-divider {
            width: 48px;
            height: 2px;
            background: linear-gradient(90deg, var(--gold), var(--gold-bright));
            margin: 12px 0 18px;
            border-radius: 2px;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 48px;
            margin-bottom: 52px;
            position: relative;
        }

        .footer-section h3 {
            font-family: 'Cinzel', serif;
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: var(--gold-bright);
            margin-bottom: 6px;
        }

        .footer-brand-name {
            font-family: 'Cormorant Garamond', serif;
            font-size: 28px;
            font-weight: 600;
            background: linear-gradient(135deg, var(--gold) 0%, var(--gold-bright) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: 1px;
        }

        .footer-links {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .footer a {
            color: rgba(255,255,255,0.65);
            text-decoration: none;
            font-weight: 400;
            font-size: 14px;
            transition: color 0.2s;
            letter-spacing: 0.2px;
        }

        .footer a:hover { color: var(--gold-bright); }

        .footer-bottom {
            padding-top: 32px;
            border-top: 1px solid rgba(201,149,42,0.15);
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: rgba(255,255,255,0.45);
            font-size: 13px;
            flex-wrap: wrap;
            gap: 12px;
            position: relative;
        }

        .footer-bottom-tagline {
            font-family: 'Cinzel', serif;
            font-size: 10px;
            letter-spacing: 3px;
            color: rgba(201,149,42,0.5);
            text-transform: uppercase;
        }

        /* ─── ALERTS ──────────────────────────────────────── */
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
            background: var(--gold-pale);
            border-color: var(--gold);
            color: #7a5a00;
        }

        /* ─── EMPTY STATE ─────────────────────────────────── */
        .empty-state {
            text-align: center;
            padding: 80px 20px;
            color: var(--text-light);
        }

        .empty-state svg {
            width: 80px;
            height: 80px;
            margin-bottom: 24px;
            opacity: 0.2;
            color: var(--purple);
        }

        .empty-state h3 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 12px;
            color: var(--navy);
        }

        .empty-state p { font-size: 16px; }

        /* ─── USAGE POPUP ─────────────────────────────────── */
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
            background: rgba(13,11,46,0.75);
            backdrop-filter: blur(6px);
        }

        .popup-content {
            position: relative;
            background: var(--white);
            border-radius: 20px;
            padding: 48px 40px 40px;
            max-width: 480px;
            width: 90%;
            box-shadow: 0 32px 64px rgba(13,11,46,0.35);
            text-align: center;
            animation: slideUp 0.3s;
            border: 1px solid var(--border-light);
            overflow: hidden;
        }

        .popup-content::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--gold), var(--purple), var(--gold));
        }

        .popup-close {
            position: absolute;
            top: 16px;
            right: 16px;
            background: none;
            border: none;
            font-size: 28px;
            cursor: pointer;
            color: var(--text-lighter);
            transition: color 0.2s;
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
        }

        .popup-close:hover {
            color: var(--text);
            background: var(--bg-tinted);
        }

        .popup-icon {
            font-size: 56px;
            margin-bottom: 18px;
        }

        .popup-content h3 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 26px;
            font-weight: 600;
            margin-bottom: 12px;
            color: var(--navy);
        }

        .popup-content p {
            color: var(--text-mid);
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
            to   { opacity: 1; }
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(24px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ─── RESPONSIVE ──────────────────────────────────── */
        @media (max-width: 1024px) {
            .articles-grid {
                grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            }
        }

        @media (max-width: 768px) {
            .nav { display: none; }
            .mobile-menu-btn { display: block; }

            .logo-image {
                height: 40px;
            }

            .logo-text {
                display: none;
            }

            .hero { padding: 52px 0 48px; }
            .hero h1 { font-size: 42px; }
            .hero p  { font-size: 16px; }

            .hero-ornament { display: none; }

            .articles-grid {
                grid-template-columns: 1fr;
                gap: 24px;
            }

            .container { padding: 0 16px; }
            .filters-wrapper { padding: 18px; }

            .subscription-banner { padding: 28px 20px; }
            .subscription-banner .remaining { font-size: 42px; }

            .footer-bottom { justify-content: center; text-align: center; }
        }

        @media (max-width: 480px) {
            .hero h1 { font-size: 34px; }
            .article-title { font-size: 21px; }
            .filters { gap: 8px; }
            .filter-btn { padding: 8px 16px; font-size: 12px; }
            .hero-cta { gap: 12px; }
            .hero-cta .btn { font-size: 13px; padding: 10px 18px; }
        }
    </style>
</head>
<body>

    <!-- Mobile Navigation -->
    <div class="mobile-nav" id="mobileNav">
        <div class="mobile-nav-header">
            <a href="index.php" class="logo">
                <img src="assets/images/logo.png" alt="<?php echo SITE_NAME; ?> Logo" class="logo-image">
                <div class="logo-text">
                    <span class="logo-name"><?php echo SITE_NAME; ?></span>
                    <span class="logo-tagline" style="color: rgba(201,149,42,0.7);">Create. Share. Inspire.</span>
                </div>
            </a>
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
                            <span class="premium-badge">✦ Premium Member</span>
                        <?php else: ?>
                            &nbsp;·&nbsp; <?php echo $freeRemaining; ?> free articles remaining
                        <?php endif; ?>
                    <?php else: ?>
                        Unlock unlimited access &nbsp;·&nbsp; Subscribe today
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="container">
            <div class="header-content">
                <a href="index.php" class="logo">
                    <img src="https://github.com/Vigneshgbe/Subscription_Based_Blog/blob/main/assets/Logo.png?raw=true" alt="<?php echo SITE_NAME; ?> Logo" class="logo-image">
                    <div class="logo-text">
                        <span class="logo-name"><?php echo SITE_NAME; ?></span>
                        <span class="logo-tagline">Create. Share. Inspire.</span>
                    </div>
                </a>
                <nav class="nav">
                    <a href="index.php">Home</a>
                    <a href="about.php">About</a>
                    <a href="contact.php">Contact</a>
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

    <!-- Subscription Reminder Popup -->
    <?php if (!$hasSubscription && isLoggedIn() && $freeRemaining <= 2): ?>
    <div class="usage-popup" id="usagePopup" style="display: none;">
        <div class="popup-overlay" onclick="closeUsagePopup()"></div>
        <div class="popup-content">
            <button class="popup-close" onclick="closeUsagePopup()">×</button>
            <div class="popup-icon">
                <?php echo $freeRemaining == 0 ? '🔒' : '✦'; ?>
            </div>
            <h3><?php echo $freeRemaining == 0 ? 'No Free Articles Remaining' : $freeRemaining . ' Free Article' . ($freeRemaining > 1 ? 's' : '') . ' Remaining'; ?></h3>
            <p><?php echo $freeRemaining == 0 ? 'Subscribe now to continue reading premium content.' : 'Subscribe for unlimited access to all premium articles.'; ?></p>
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
            <div class="hero-inner" style="position:relative;">
                <div class="hero-content">
                    <div class="hero-eyebrow">Create. Share. Inspire.</div>
                    <h1>Insights That<br><em>Move You</em></h1>
                    <p>Deep dives into technology, business, and culture. Premium content for curious, inspired minds.</p>
                    <div class="hero-cta">
                        <?php if (!isLoggedIn()): ?>
                            <a href="register.php" class="btn btn-primary">Start Reading Free</a>
                            <a href="pricing.php" class="btn btn-royal">View Plans</a>
                        <?php else: ?>
                            <a href="#articles" class="btn btn-primary">Explore Articles</a>
                        <?php endif; ?>
                    </div>
                </div>
                <!-- Decorative SVG ornament echoing the logo circle -->
                <img src="https://github.com/Vigneshgbe/Subscription_Based_Blog/blob/main/assets/Logo.png?raw=true" 
                    alt="Slice Life Logo" 
                    class="hero-ornament"
                    style="opacity: 1; object-fit: contain;">
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <main class="container" id="articles">
        <?php if ($flash): ?>
            <div class="alert alert-<?php echo $flash['type']; ?>">
                <?php echo htmlspecialchars($flash['message']); ?>
            </div>
        <?php endif; ?>

        <!-- Filters -->
        <div class="filters-wrapper">
            <div class="filters-label">Browse by category</div>
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

        <!-- Section heading -->
        <?php if (!$searchQuery && !$categoryFilter): ?>
        <div class="section-heading">
            <h2>Latest Articles</h2>
            <div class="section-heading-line"></div>
        </div>
        <?php elseif ($searchQuery): ?>
        <div class="section-heading">
            <h2>Results for "<?php echo htmlspecialchars($searchQuery); ?>"</h2>
            <div class="section-heading-line"></div>
        </div>
        <?php endif; ?>

        <!-- Articles Grid -->
        <div class="articles-grid">
            <?php if (empty($articles)): ?>
                <div class="empty-state" style="grid-column: 1/-1;">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3>No articles found</h3>
                    <p>Try adjusting your search or filters</p>
                </div>
            <?php else: ?>
                <?php foreach ($articles as $article): ?>
                <article class="article-card">
                    <div class="article-image-wrapper">
                        <?php if ($article['is_premium']): ?>
                            <span class="article-badge">✦ Premium</span>
                        <?php endif; ?>

                        <?php if ($article['featured_image']): ?>
                            <img src="<?php echo htmlspecialchars($article['featured_image']); ?>"
                                 alt="<?php echo htmlspecialchars($article['title']); ?>"
                                 class="article-image">
                        <?php else: ?>
                            <div class="article-placeholder">
                                <span class="article-placeholder-letter">
                                    <?php echo strtoupper(substr($article['title'], 0, 1)); ?>
                                </span>
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
                    <div class="footer-brand-name"><?php echo SITE_NAME; ?></div>
                    <div class="footer-divider"></div>
                    <p style="color: rgba(255,255,255,0.55); line-height: 1.7; font-size: 14px; max-width: 260px;">
                        Premium insights and analysis for those who want to stay ahead — and inspired.
                    </p>
                </div>
                <div class="footer-section">
                    <h3>Navigate</h3>
                    <div class="footer-divider"></div>
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
                    <div class="footer-divider"></div>
                    <div class="footer-links">
                        <a href="about.php">About</a>
                        <a href="contact.php">Contact</a>
                    </div>
                </div>
                <div class="footer-section">
                    <h3>Legal</h3>
                    <div class="footer-divider"></div>
                    <div class="footer-links">
                        <a href="terms.php">Terms of Service</a>
                        <a href="privacy.php">Privacy Policy</a>
                        <a href="cookies.php">Cookie Policy</a>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <span>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</span>
                <span class="footer-bottom-tagline">Create. Share. Inspire.</span>
            </div>
        </div>
    </footer>

    <script>
        function toggleMobileMenu() {
            const mobileNav = document.getElementById('mobileNav');
            mobileNav.classList.toggle('active');
            document.body.style.overflow = mobileNav.classList.contains('active') ? 'hidden' : '';
        }

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
                document.cookie = 'usage_reminder_shown=1; max-age=86400; path=/';
            }
        }

        window.addEventListener('DOMContentLoaded', function() {
            <?php if (!$hasSubscription && isLoggedIn() && $freeRemaining <= 2): ?>
                if (!document.cookie.includes('usage_reminder_shown=1')) {
                    setTimeout(showUsagePopup, 2000);
                }
            <?php endif; ?>
        });
    </script>
</body>
</html>