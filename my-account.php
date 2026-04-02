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
            background: linear-gradient(135deg, var(--bg-tinted) 0%, var(--white) 100%);
            padding: 0;
            color: var(--text);
            -webkit-font-smoothing: antialiased;
            min-height: 100vh;
        }
        
        /* ─── HEADER BAR ──────────────────────────────────── */
        .top-bar {
            background: linear-gradient(90deg, var(--navy) 0%, var(--navy-mid) 50%, var(--navy-light) 100%);
            padding: 16px 0;
            margin-bottom: 48px;
            box-shadow: 0 4px 16px rgba(13,11,46,0.15);
        }

        .top-bar-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .back-link {
            color: var(--gold-bright);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            font-size: 14px;
            letter-spacing: 0.3px;
            transition: all 0.2s;
        }
        
        .back-link:hover {
            color: var(--gold-light);
            gap: 12px;
        }

        .back-link svg {
            width: 18px;
            height: 18px;
        }

        .logout-link {
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
            transition: color 0.2s;
        }

        .logout-link:hover {
            color: var(--white);
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 24px 80px;
        }
        
        .header {
            margin-bottom: 48px;
            text-align: center;
        }
        
        h1 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 52px;
            font-weight: 600;
            margin-bottom: 12px;
            background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: -1px;
        }
        
        .subtitle {
            color: var(--text-light);
            font-size: 17px;
            font-weight: 300;
        }

        .user-greeting {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(135deg, rgba(107,63,160,0.1) 0%, rgba(124,58,237,0.08) 100%);
            color: var(--purple);
            padding: 8px 20px;
            border-radius: 24px;
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 20px;
            border: 1px solid rgba(107,63,160,0.15);
        }
        
        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 32px;
            margin-bottom: 32px;
        }
        
        .card {
            background: var(--white);
            border-radius: 16px;
            padding: 36px;
            box-shadow: var(--shadow-navy);
            border: 1px solid var(--border-light);
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
        }

        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--gold), var(--purple));
            opacity: 0;
            transition: opacity 0.3s;
        }

        .card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
        }

        .card:hover::before {
            opacity: 1;
        }
        
        .card h2 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 28px;
            color: var(--navy);
            letter-spacing: -0.5px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card h2::before {
            content: '✦';
            font-size: 20px;
            color: var(--gold);
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 18px 0;
            border-bottom: 1px solid var(--border-light);
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .info-label {
            font-weight: 600;
            color: var(--text-light);
            font-size: 14px;
            letter-spacing: 0.3px;
        }
        
        .info-value {
            color: var(--text);
            font-weight: 500;
        }
        
        .badge {
            display: inline-block;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-family: 'Cinzel', serif;
        }
        
        .badge-success {
            background: linear-gradient(135deg, var(--gold) 0%, var(--gold-bright) 100%);
            color: var(--navy);
            box-shadow: 0 2px 12px rgba(201,149,42,0.3);
        }
        
        .badge-warning {
            background: linear-gradient(135deg, rgba(201,149,42,0.2) 0%, rgba(240,180,41,0.15) 100%);
            color: var(--text);
            border: 1px solid var(--gold);
        }

        .badge-premium {
            background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
            color: var(--gold-bright);
            box-shadow: var(--shadow-navy);
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
            font-size: 15px;
            letter-spacing: 0.3px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--gold) 0%, var(--gold-bright) 100%);
            color: var(--navy);
            box-shadow: var(--shadow-gold);
        }
        
        .btn-primary:hover {
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
            color: var(--white);
        }
        
        .history-list {
            list-style: none;
        }
        
        .history-item {
            padding: 18px 0;
            border-bottom: 1px solid var(--border-light);
            transition: padding-left 0.2s;
        }

        .history-item:hover {
            padding-left: 8px;
        }
        
        .history-item:last-child {
            border-bottom: none;
        }
        
        .history-item a {
            color: var(--navy);
            text-decoration: none;
            font-weight: 600;
            font-size: 16px;
            transition: color 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .history-item a::before {
            content: '→';
            color: var(--gold);
            font-weight: 700;
            opacity: 0;
            transition: opacity 0.2s;
        }
        
        .history-item a:hover {
            color: var(--purple);
        }

        .history-item a:hover::before {
            opacity: 1;
        }
        
        .history-time {
            font-size: 13px;
            color: var(--text-lighter);
            margin-top: 6px;
            font-weight: 400;
        }
        
        .full-width {
            grid-column: 1 / -1;
        }

        .empty-state {
            text-align: center;
            padding: 48px 24px;
            color: var(--text-light);
        }

        .empty-state-icon {
            font-size: 48px;
            margin-bottom: 16px;
            opacity: 0.3;
        }

        .empty-state p {
            font-size: 15px;
            font-weight: 300;
        }

        /* Subscription card special styling */
        .subscription-card {
            background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
            color: var(--white);
            position: relative;
            overflow: hidden;
        }

        .subscription-card::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(ellipse at 80% 20%, rgba(201,149,42,0.15) 0%, transparent 60%);
            pointer-events: none;
        }

        .subscription-card h2 {
            color: var(--gold-bright);
        }

        .subscription-card .info-label {
            color: rgba(255,255,255,0.7);
        }

        .subscription-card .info-value {
            color: var(--white);
        }

        .subscription-card .info-row {
            border-bottom-color: rgba(255,255,255,0.1);
        }

        .subscription-card p {
            color: rgba(255,255,255,0.8);
        }

        /* Profile card icon */
        .profile-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--gold) 0%, var(--gold-bright) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            font-weight: 700;
            color: var(--navy);
            margin: 0 auto 24px;
            font-family: 'Cormorant Garamond', serif;
            box-shadow: var(--shadow-gold);
            border: 3px solid var(--white);
            position: relative;
        }

        .profile-icon::after {
            content: '';
            position: absolute;
            inset: -6px;
            border-radius: 50%;
            border: 1px solid var(--border);
        }
        
        @media (max-width: 768px) {
            .grid {
                grid-template-columns: 1fr;
            }

            h1 {
                font-size: 38px;
            }

            .card {
                padding: 28px 24px;
            }

            .container {
                padding: 0 16px 48px;
            }

            .top-bar-content {
                padding: 0 16px;
            }

            .logout-link {
                display: none;
            }

            .info-row {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }
        }

        @media (max-width: 480px) {
            h1 {
                font-size: 32px;
            }

            .card h2 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="top-bar">
        <div class="top-bar-content">
            <a href="index.php" class="back-link">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Home
            </a>
            <a href="logout.php" class="logout-link">Logout</a>
        </div>
    </div>

    <div class="container">
        <div class="header">
            <div class="user-greeting">
                Welcome back, <?php echo htmlspecialchars(explode(' ', $user['full_name'])[0]); ?>
            </div>
            <h1>My Account</h1>
            <p class="subtitle">Manage your profile and subscription</p>
        </div>
        
        <div class="grid">
            <div class="card">
                <div class="profile-icon">
                    <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                </div>
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
                            <span class="badge badge-success">✦ Active</span>
                        <?php else: ?>
                            <span class="badge badge-warning">Inactive</span>
                        <?php endif; ?>
                    </span>
                </div>
            </div>
            
            <div class="card <?php echo $hasSubscription ? 'subscription-card' : ''; ?>">
                <h2>Subscription</h2>
                <?php if ($subscription): ?>
                    <div class="info-row">
                        <span class="info-label">Plan</span>
                        <span class="info-value">
                            <span class="badge <?php echo $hasSubscription ? 'badge-premium' : 'badge-warning'; ?>">
                                <?php if ($hasSubscription): ?>✦<?php endif; ?> <?php echo ucfirst($subscription['plan_type']); ?>
                            </span>
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Status</span>
                        <span class="info-value">
                            <?php if ($hasSubscription): ?>
                                <span class="badge badge-success">✦ Active</span>
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
                    <div class="empty-state">
                        <div class="empty-state-icon">✦</div>
                        <p style="margin-bottom: 24px;">You don't have an active subscription.</p>
                    </div>
                    <a href="pricing.php" class="btn btn-primary" style="width: 100%;">View Plans</a>
                <?php endif; ?>
            </div>
            
            <div class="card full-width">
                <h2>Reading History</h2>
                <?php if (empty($readingHistory)): ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">📚</div>
                        <p>No reading history yet. Start exploring our articles!</p>
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