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
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #f5f7fa;
            padding: 40px 20px;
        }
        
        .container {
            max-width: 1000px;
            margin: 0 auto;
        }
        
        .header {
            margin-bottom: 40px;
        }
        
        .back-link {
            color: #667eea;
            text-decoration: none;
            display: inline-block;
            margin-bottom: 20px;
            font-weight: 600;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
        
        h1 {
            font-size: 36px;
            margin-bottom: 10px;
        }
        
        .subtitle {
            color: #666;
            font-size: 16px;
        }
        
        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }
        
        .card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        
        .card h2 {
            font-size: 20px;
            margin-bottom: 20px;
            color: #333;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 15px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .info-label {
            font-weight: 600;
            color: #666;
        }
        
        .info-value {
            color: #333;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
        }
        
        .badge-success {
            background: #d4edda;
            color: #155724;
        }
        
        .badge-warning {
            background: #fff3cd;
            color: #856404;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 24px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 700;
            text-align: center;
            transition: all 0.2s;
            border: none;
            cursor: pointer;
        }
        
        .btn-primary {
            background: #667eea;
            color: white;
        }
        
        .btn-primary:hover {
            background: #5568d3;
        }
        
        .btn-outline {
            background: transparent;
            border: 2px solid #667eea;
            color: #667eea;
        }
        
        .btn-outline:hover {
            background: #667eea;
            color: white;
        }
        
        .history-list {
            list-style: none;
        }
        
        .history-item {
            padding: 15px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .history-item:last-child {
            border-bottom: none;
        }
        
        .history-item a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }
        
        .history-item a:hover {
            text-decoration: underline;
        }
        
        .history-time {
            font-size: 13px;
            color: #999;
            margin-top: 5px;
        }
        
        .full-width {
            grid-column: 1 / -1;
        }
        
        @media (max-width: 768px) {
            .grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <a href="index.php" class="back-link">← Back to Home</a>
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
                            <span class="badge badge-success"><?php echo ucfirst($subscription['plan_type']); ?></span>
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
                    <div style="margin-top: 20px;">
                        <?php if (!$hasSubscription): ?>
                            <a href="pricing.php" class="btn btn-primary" style="width: 100%;">Upgrade Now</a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <p style="color: #666; margin-bottom: 20px;">You don't have an active subscription.</p>
                    <a href="pricing.php" class="btn btn-primary" style="width: 100%;">View Plans</a>
                <?php endif; ?>
            </div>
            
            <div class="card full-width">
                <h2>Reading History</h2>
                <?php if (empty($readingHistory)): ?>
                    <p style="color: #666;">No reading history yet. Start exploring our articles!</p>
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