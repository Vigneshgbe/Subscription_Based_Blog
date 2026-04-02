<?php
require_once 'config.php';

requireLogin();

$db = db();
$userId = $_SESSION['user_id'];

// Get user details
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

// Get subscription details
$stmt = $db->prepare("
    SELECT * FROM subscriptions 
    WHERE user_id = ? 
    ORDER BY created_at DESC 
    LIMIT 1
");
$stmt->execute([$userId]);
$subscription = $stmt->fetch();

// Get reading history
$stmt = $db->prepare("
    SELECT a.title, a.slug, rh.read_at
    FROM reading_history rh
    JOIN articles a ON rh.article_id = a.id
    WHERE rh.user_id = ?
    ORDER BY rh.read_at DESC
    LIMIT 10
");
$stmt->execute([$userId]);
$readingHistory = $stmt->fetchAll();

$hasSubscription = hasActiveSubscription($userId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account - <?php echo SITE_NAME; ?></title>
    <link rel="icon" type="image/x-icon" href="https://raw.githubusercontent.com/Vigneshgbe/Subscription_Based_Blog/refs/heads/main/assets/Logo.png">
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
            background: linear-gradient(135deg, var(--navy) 0%, var(--navy-mid) 50%, var(--navy-light) 100%);
            min-height: 100vh;
            padding: 40px 20px;
            position: relative;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        
        /* Atmospheric background effects */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(ellipse at 15% 50%, rgba(107,63,160,0.25) 0%, transparent 55%),
                radial-gradient(ellipse at 85% 20%, rgba(201,149,42,0.15) 0%, transparent 50%),
                radial-gradient(ellipse at 60% 90%, rgba(124,58,237,0.12) 0%, transparent 45%);
            pointer-events: none;
            z-index: 0;
        }
        
        /* Floating decorative elements */
        body::after {
            content: '✦';
            position: fixed;
            top: 10%;
            right: 15%;
            font-size: 48px;
            color: var(--gold);
            opacity: 0.2;
            animation: float 6s ease-in-out infinite;
            z-index: 0;
        }
        
        @keyframes float {
            0%, 100% { transform: translate(0, 0) rotate(0deg); opacity: 0.2; }
            50% { transform: translate(20px, -20px) rotate(180deg); opacity: 0.4; }
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
        }
        
        .header {
            margin-bottom: 40px;
            animation: slideDown 0.6s ease-out;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .back-link {
            color: var(--gold);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 24px;
            font-weight: 600;
            font-size: 14px;
            padding: 8px 16px;
            border-radius: 8px;
            transition: all 0.3s;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(201,149,42,0.2);
        }
        
        .back-link:hover {
            background: rgba(201,149,42,0.15);
            transform: translateX(-4px);
            border-color: rgba(201,149,42,0.4);
            box-shadow: var(--shadow-gold);
        }
        
        .back-link svg {
            width: 16px;
            height: 16px;
            transition: transform 0.3s;
        }
        
        .back-link:hover svg {
            transform: translateX(-2px);
        }
        
        h1 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 48px;
            margin-bottom: 12px;
            background: linear-gradient(135deg, var(--white) 0%, var(--gold-light) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-weight: 700;
            letter-spacing: -1px;
        }
        
        .subtitle {
            color: var(--text-lighter);
            font-size: 16px;
            font-weight: 400;
        }
        
        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }
        
        .card {
            background: rgba(255, 255, 255, 0.98);
            border-radius: 20px;
            padding: 32px;
            box-shadow: var(--shadow-lg);
            border: 1px solid rgba(201,149,42,0.1);
            position: relative;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            animation: fadeInUp 0.6s ease-out backwards;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .card:nth-child(1) { animation-delay: 0.1s; }
        .card:nth-child(2) { animation-delay: 0.2s; }
        .card:nth-child(3) { animation-delay: 0.3s; }
        
        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--gold), var(--purple), var(--gold));
            opacity: 0;
            transition: opacity 0.3s;
        }
        
        .card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 60px rgba(13,11,46,0.2);
            border-color: rgba(201,149,42,0.3);
        }
        
        .card:hover::before {
            opacity: 1;
        }
        
        .card h2 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 26px;
            margin-bottom: 24px;
            color: var(--navy);
            font-weight: 600;
            letter-spacing: -0.5px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .card h2::before {
            content: '';
            width: 4px;
            height: 24px;
            background: linear-gradient(135deg, var(--gold), var(--gold-bright));
            border-radius: 2px;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 0;
            border-bottom: 1px solid var(--border-light);
            transition: all 0.2s;
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .info-row:hover {
            background: linear-gradient(90deg, transparent 0%, var(--bg-tinted) 50%, transparent 100%);
            padding-left: 12px;
            padding-right: 12px;
            margin-left: -12px;
            margin-right: -12px;
            border-radius: 8px;
        }
        
        .info-label {
            font-weight: 600;
            color: var(--text-mid);
            font-size: 14px;
            letter-spacing: 0.3px;
        }
        
        .info-value {
            color: var(--text);
            font-weight: 500;
            font-size: 14px;
        }
        
        .badge {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            transition: all 0.2s;
        }
        
        .badge:hover {
            transform: scale(1.05);
        }
        
        .badge-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }
        
        .badge-warning {
            background: linear-gradient(135deg, var(--gold) 0%, var(--gold-bright) 100%);
            color: var(--navy);
        }
        
        .badge-premium {
            background: linear-gradient(135deg, var(--purple) 0%, var(--purple-mid) 100%);
            color: white;
        }
        
        .btn {
            display: inline-block;
            padding: 14px 28px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 700;
            text-align: center;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            font-family: 'DM Sans', sans-serif;
            font-size: 14px;
            letter-spacing: 0.3px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--gold) 0%, var(--gold-bright) 100%);
            color: var(--navy);
            box-shadow: var(--shadow-gold);
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, var(--gold-bright) 0%, var(--gold) 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 28px rgba(201,149,42,0.35);
        }
        
        .btn-outline {
            background: transparent;
            border: 2px solid var(--purple);
            color: var(--purple);
        }
        
        .btn-outline:hover {
            background: var(--purple);
            color: white;
            transform: translateY(-2px);
            box-shadow: var(--shadow-purple);
        }
        
        .history-list {
            list-style: none;
        }
        
        .history-item {
            padding: 18px 0;
            border-bottom: 1px solid var(--border-light);
            transition: all 0.3s;
            position: relative;
        }
        
        .history-item:last-child {
            border-bottom: none;
        }
        
        .history-item::before {
            content: '';
            position: absolute;
            left: -32px;
            top: 50%;
            transform: translateY(-50%);
            width: 6px;
            height: 6px;
            background: var(--gold);
            border-radius: 50%;
            opacity: 0;
            transition: all 0.3s;
        }
        
        .history-item:hover {
            padding-left: 16px;
            background: linear-gradient(90deg, var(--bg-tinted) 0%, transparent 100%);
            margin-left: -16px;
            margin-right: -16px;
            padding-right: 16px;
            border-radius: 10px;
        }
        
        .history-item:hover::before {
            opacity: 1;
            left: 0;
        }
        
        .history-item a {
            color: var(--purple);
            text-decoration: none;
            font-weight: 600;
            font-size: 15px;
            transition: all 0.2s;
            display: inline-block;
        }
        
        .history-item a:hover {
            color: var(--gold);
            transform: translateX(4px);
        }
        
        .history-time {
            font-size: 12px;
            color: var(--text-light);
            margin-top: 6px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .history-time::before {
            content: '🕐';
            font-size: 14px;
        }
        
        .full-width {
            grid-column: 1 / -1;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: var(--text-mid);
        }
        
        .empty-state::before {
            content: '📚';
            font-size: 48px;
            display: block;
            margin-bottom: 16px;
            opacity: 0.5;
        }
        
        /* Decorative icon styles */
        .icon-wrapper {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--gold) 0%, var(--gold-bright) 100%);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            box-shadow: var(--shadow-gold);
            transition: all 0.3s;
        }
        
        .card:hover .icon-wrapper {
            transform: rotate(10deg) scale(1.1);
        }
        
        /* Responsive background sparkles */
        .sparkle {
            position: fixed;
            color: var(--gold);
            opacity: 0.15;
            pointer-events: none;
            animation: twinkle 4s ease-in-out infinite;
            z-index: 0;
        }
        
        .sparkle:nth-child(1) { top: 20%; left: 10%; font-size: 24px; animation-delay: 0s; }
        .sparkle:nth-child(2) { top: 60%; right: 20%; font-size: 32px; animation-delay: 1s; }
        .sparkle:nth-child(3) { bottom: 20%; left: 25%; font-size: 28px; animation-delay: 2s; }
        
        @keyframes twinkle {
            0%, 100% { opacity: 0.15; transform: scale(1); }
            50% { opacity: 0.35; transform: scale(1.3); }
        }
        
        @media (max-width: 768px) {
            body {
                padding: 20px 15px;
            }
            
            .grid {
                grid-template-columns: 1fr;
            }
            
            h1 {
                font-size: 36px;
            }
            
            .card {
                padding: 24px;
            }
            
            .card h2 {
                font-size: 22px;
            }
        }
        
        /* Loading animation for page entrance */
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
    </style>
</head>
<body>
    <!-- Decorative sparkles -->
    <div class="sparkle">✦</div>
    <div class="sparkle">✦</div>
    <div class="sparkle">✦</div>
    
    <div class="container">
        <div class="header">
            <a href="index.php" class="back-link">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Home
            </a>
            <h1>My Account</h1>
            <p class="subtitle">Manage your profile and subscription</p>
        </div>
        
        <div class="grid">
            <div class="card">
                <h2>Profile Information</h2>
                <div class="info-row">
                    <span class="info-label">Name</span>
                    <span class="info-value"><?php echo htmlspecialchars($user['full_name']); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Email</span>
                    <span class="info-value"><?php echo htmlspecialchars($user['email']); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Member Since</span>
                    <span class="info-value"><?php echo formatDate($user['created_at']); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Account Status</span>
                    <span class="info-value">
                        <?php if ($user['is_active']): ?>
                            <span class="badge badge-success">Active</span>
                        <?php else: ?>
                            <span class="badge badge-warning">Inactive</span>
                        <?php endif; ?>
                    </span>
                </div>
            </div>
            
            <div class="card">
                <h2>Subscription</h2>
                <?php if ($subscription): ?>
                    <div class="info-row">
                        <span class="info-label">Plan</span>
                        <span class="info-value">
                            <span class="badge badge-premium"><?php echo ucfirst($subscription['plan_type']); ?></span>
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Status</span>
                        <span class="info-value">
                            <?php if ($hasSubscription): ?>
                                <span class="badge badge-success">Active</span>
                            <?php else: ?>
                                <span class="badge badge-warning"><?php echo ucfirst($subscription['status']); ?></span>
                            <?php endif; ?>
                        </span>
                    </div>
                    <?php if ($subscription['current_period_end']): ?>
                    <div class="info-row">
                        <span class="info-label">Renews On</span>
                        <span class="info-value"><?php echo formatDate($subscription['current_period_end']); ?></span>
                    </div>
                    <?php endif; ?>
                    <div style="margin-top: 24px;">
                        <?php if (!$hasSubscription): ?>
                            <a href="pricing.php" class="btn btn-primary" style="width: 100%;">Upgrade Now</a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state" style="padding: 30px 20px;">
                        <p style="color: var(--text-mid); margin-bottom: 20px; font-weight: 500;">You don't have an active subscription.</p>
                        <a href="pricing.php" class="btn btn-primary" style="width: 100%;">View Plans</a>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="card full-width">
                <h2>Reading History</h2>
                <?php if (empty($readingHistory)): ?>
                    <div class="empty-state">
                        <p style="font-weight: 500;">No reading history yet. Start exploring our articles!</p>
                    </div>
                <?php else: ?>
                    <ul class="history-list">
                        <?php foreach ($readingHistory as $history): ?>
                        <li class="history-item">
                            <a href="article.php?slug=<?php echo htmlspecialchars($history['slug']); ?>">
                                <?php echo htmlspecialchars($history['title']); ?>
                            </a>
                            <div class="history-time"><?php echo timeAgo($history['read_at']); ?></div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>