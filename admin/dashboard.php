<?php
require_once '../config.php';

requireAdmin();

$db = db();

// Get statistics
$stats = [];

// Total users
$stmt = $db->query("SELECT COUNT(*) as total FROM users");
$stats['total_users'] = $stmt->fetch()['total'];

// Active subscriptions
$stmt = $db->query("SELECT COUNT(*) as total FROM subscriptions WHERE status = 'active' AND plan_type IN ('monthly', 'yearly')");
$stats['active_subscriptions'] = $stmt->fetch()['total'];

// Total articles
$stmt = $db->query("SELECT COUNT(*) as total FROM articles");
$stats['total_articles'] = $stmt->fetch()['total'];

// Published articles
$stmt = $db->query("SELECT COUNT(*) as total FROM articles WHERE is_published = 1");
$stats['published_articles'] = $stmt->fetch()['total'];

// Total revenue (approximate)
$stmt = $db->query("SELECT SUM(amount) as total FROM transactions WHERE status = 'succeeded'");
$stats['total_revenue'] = $stmt->fetch()['total'] ?? 0;

// Monthly revenue (current month)
$stmt = $db->query("
    SELECT SUM(amount) as total 
    FROM transactions 
    WHERE status = 'succeeded' 
    AND MONTH(created_at) = MONTH(CURRENT_DATE())
    AND YEAR(created_at) = YEAR(CURRENT_DATE())
");
$stats['monthly_revenue'] = $stmt->fetch()['total'] ?? 0;

// Recent articles
$recentArticles = $db->query("
    SELECT a.*, u.full_name as author_name 
    FROM articles a
    LEFT JOIN users u ON a.author_id = u.id
    ORDER BY a.created_at DESC
    LIMIT 5
")->fetchAll();

// Recent subscriptions
$recentSubscriptions = $db->query("
    SELECT s.*, u.full_name, u.email
    FROM subscriptions s
    JOIN users u ON s.user_id = u.id
    WHERE s.plan_type IN ('monthly', 'yearly')
    ORDER BY s.created_at DESC
    LIMIT 5
")->fetchAll();

// Recent activity logs
$recentActivity = $db->query("
    SELECT al.*, u.full_name
    FROM activity_logs al
    LEFT JOIN users u ON al.user_id = u.id
    ORDER BY al.created_at DESC
    LIMIT 10
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo SITE_NAME; ?></title>
    <link rel="icon" type="image/x-icon" href="https://raw.githubusercontent.com/Vigneshgbe/Subscription_Based_Blog/refs/heads/main/assets/Logo.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #f8fafc;
            color: #1e293b;
        }
        
        .admin-layout {
            display: flex;
            min-height: 100vh;
        }
        
        /* ── Main content area ─────────────────────────────────────── */
        .main-content {
            flex: 1;
            margin-left: 280px; /* matches sidebar width */
            padding: 32px;
            transition: margin-left 0.3s ease;
        }
        
        /* ── Top bar ───────────────────────────────────────────────── */
        .top-bar {
            background: white;
            padding: 28px 32px;
            border-radius: 16px;
            margin-bottom: 32px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            border: 1px solid #e2e8f0;
        }
        
        .top-bar-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
        }
        
        .top-bar h1 {
            font-size: 28px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.5px;
        }
        
        .top-bar-time {
            font-size: 14px;
            color: #64748b;
            font-weight: 500;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 8px 16px;
            background: #f8fafc;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 16px;
        }
        
        .user-details strong {
            display: block;
            font-size: 14px;
            color: #0f172a;
            font-weight: 600;
        }
        
        .user-details span {
            font-size: 12px;
            color: #64748b;
        }
        
        /* ── Stat Cards ────────────────────────────────────────────── */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 24px;
            margin-bottom: 32px;
        }
        
        .stat-card {
            background: white;
            padding: 24px;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            position: relative;
            overflow: hidden;
            transition: all 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(0,0,0,0.08);
            border-color: #cbd5e1;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
        }
        
        .stat-card.card-green::before {
            background: linear-gradient(90deg, #10b981 0%, #059669 100%);
        }
        
        .stat-card.card-blue::before {
            background: linear-gradient(90deg, #3b82f6 0%, #2563eb 100%);
        }
        
        .stat-card.card-orange::before {
            background: linear-gradient(90deg, #f59e0b 0%, #d97706 100%);
        }
        
        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 16px;
        }
        
        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            background: linear-gradient(135deg, #667eea15 0%, #764ba215 100%);
        }
        
        .stat-card.card-green .stat-icon {
            background: linear-gradient(135deg, #10b98115 0%, #05966915 100%);
        }
        
        .stat-card.card-blue .stat-icon {
            background: linear-gradient(135deg, #3b82f615 0%, #2563eb15 100%);
        }
        
        .stat-card.card-orange .stat-icon {
            background: linear-gradient(135deg, #f59e0b15 0%, #d9770615 100%);
        }
        
        .stat-value {
            font-size: 32px;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 4px;
            letter-spacing: -1px;
        }
        
        .stat-label {
            color: #64748b;
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .stat-change {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 12px;
            font-weight: 600;
            padding: 4px 8px;
            border-radius: 6px;
            background: #dcfce7;
            color: #059669;
            margin-top: 8px;
        }
        
        /* ── Cards ─────────────────────────────────────────────────── */
        .card {
            background: white;
            padding: 28px;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            margin-bottom: 32px;
        }
        
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .card-header h2 {
            font-size: 18px;
            font-weight: 700;
            color: #0f172a;
        }
        
        /* ── Buttons ────────────────────────────────────────────────── */
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
            font-size: 14px;
            font-family: inherit;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(102, 126, 234, 0.4);
        }
        
        .btn-sm {
            padding: 8px 16px;
            font-size: 13px;
        }
        
        /* ── Quick Actions ──────────────────────────────────────────── */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 32px;
        }
        
        .quick-action-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 24px;
            border-radius: 16px;
            text-decoration: none;
            display: flex;
            flex-direction: column;
            gap: 8px;
            transition: all 0.3s;
            border: 1px solid rgba(255,255,255,0.1);
            position: relative;
            overflow: hidden;
        }
        
        .quick-action-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100px;
            height: 100px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
            transform: translate(30%, -30%);
        }
        
        .quick-action-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 16px 32px rgba(0,0,0,0.2);
        }
        
        .quick-action-card.card-green {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }
        
        .quick-action-card.card-orange {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        }
        
        .quick-action-card.card-blue {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        }
        
        .quick-action-icon {
            font-size: 28px;
            margin-bottom: 8px;
        }
        
        .quick-action-card h3 {
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 4px;
            position: relative;
            z-index: 1;
        }
        
        .quick-action-card p {
            font-size: 13px;
            opacity: 0.9;
            font-weight: 500;
            position: relative;
            z-index: 1;
        }
        
        /* ── Tables ─────────────────────────────────────────────────── */
        .table-container {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 700px;
        }
        
        th {
            text-align: left;
            padding: 12px 16px;
            background: #f8fafc;
            font-weight: 700;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            border-bottom: 1px solid #e2e8f0;
        }
        
        td {
            padding: 16px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 14px;
        }
        
        tr:last-child td {
            border-bottom: none;
        }
        
        tr:hover td {
            background: #f8fafc;
        }
        
        /* ── Badges ─────────────────────────────────────────────────── */
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 12px;
            border-radius: 8px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .badge-success {
            background: #dcfce7;
            color: #059669;
        }
        
        .badge-warning {
            background: #fef3c7;
            color: #d97706;
        }
        
        .badge-info {
            background: #dbeafe;
            color: #2563eb;
        }
        
        .badge-purple {
            background: #f3e8ff;
            color: #7c3aed;
        }
        
        /* ── Responsive ─────────────────────────────────────────────── */
        @media (max-width: 1024px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 20px 16px;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
                gap: 16px;
            }
            
            .top-bar {
                padding: 20px;
            }
            
            .top-bar h1 {
                font-size: 24px;
            }
            
            .card {
                padding: 20px;
            }
            
            .quick-actions {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
<div class="admin-layout">

    <?php require_once 'sidebar.php'; ?>

    <main class="main-content">

        <!-- Top Bar -->
        <div class="top-bar">
            <div class="top-bar-content">
                <div>
                    <h1>Dashboard Overview</h1>
                    <p class="top-bar-time"><?php echo date('l, F j, Y'); ?></p>
                </div>
                <div class="user-info">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($_SESSION['user_name'], 0, 1)); ?>
                    </div>
                    <div class="user-details">
                        <strong><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong>
                        <span>Administrator</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon">👥</div>
                </div>
                <div class="stat-value"><?php echo number_format($stats['total_users']); ?></div>
                <div class="stat-label">Total Users</div>
                <span class="stat-change">↗ Growing</span>
            </div>
            
            <div class="stat-card card-green">
                <div class="stat-header">
                    <div class="stat-icon">💎</div>
                </div>
                <div class="stat-value"><?php echo number_format($stats['active_subscriptions']); ?></div>
                <div class="stat-label">Active Subscribers</div>
                <span class="stat-change">↗ Active</span>
            </div>
            
            <div class="stat-card card-blue">
                <div class="stat-header">
                    <div class="stat-icon">📝</div>
                </div>
                <div class="stat-value"><?php echo number_format($stats['published_articles']); ?></div>
                <div class="stat-label">Published Articles</div>
                <span class="stat-change">of <?php echo number_format($stats['total_articles']); ?> total</span>
            </div>
            
            <div class="stat-card card-orange">
                <div class="stat-header">
                    <div class="stat-icon">💰</div>
                </div>
                <div class="stat-value">₹<?php echo number_format($stats['total_revenue'], 0); ?></div>
                <div class="stat-label">Total Revenue</div>
                <span class="stat-change">₹<?php echo number_format($stats['monthly_revenue'], 0); ?> this month</span>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <a href="article-create.php" class="quick-action-card">
                <div class="quick-action-icon">✨</div>
                <h3>New Article</h3>
                <p>Create a new blog post</p>
            </a>
            <a href="users.php" class="quick-action-card card-green">
                <div class="quick-action-icon">👥</div>
                <h3>Manage Users</h3>
                <p>View and edit users</p>
            </a>
            <a href="subscriptions.php" class="quick-action-card card-orange">
                <div class="quick-action-icon">📊</div>
                <h3>Subscriptions</h3>
                <p>Monitor subscriptions</p>
            </a>
            <a href="settings.php" class="quick-action-card card-blue">
                <div class="quick-action-icon">⚙️</div>
                <h3>Settings</h3>
                <p>Configure your site</p>
            </a>
        </div>

        <!-- Recent Articles -->
        <div class="card">
            <div class="card-header">
                <h2>Recent Articles</h2>
                <a href="articles.php" class="btn btn-sm btn-primary">View All →</a>
            </div>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Author</th>
                            <th>Status</th>
                            <th>Views</th>
                            <th>Published</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentArticles as $article): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($article['title']); ?></strong></td>
                            <td><?php echo htmlspecialchars($article['author_name']); ?></td>
                            <td>
                                <?php if ($article['is_published']): ?>
                                    <span class="badge badge-success">Published</span>
                                <?php else: ?>
                                    <span class="badge badge-warning">Draft</span>
                                <?php endif; ?>
                                <?php if ($article['is_premium']): ?>
                                    <span class="badge badge-purple">Premium</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo number_format($article['views']); ?></td>
                            <td><?php echo timeAgo($article['created_at']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Subscriptions -->
        <div class="card">
            <div class="card-header">
                <h2>Recent Subscriptions</h2>
                <a href="subscriptions.php" class="btn btn-sm btn-primary">View All →</a>
            </div>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Email</th>
                            <th>Plan</th>
                            <th>Status</th>
                            <th>Subscribed</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentSubscriptions as $sub): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($sub['full_name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($sub['email']); ?></td>
                            <td><span class="badge badge-info"><?php echo ucfirst($sub['plan_type']); ?></span></td>
                            <td>
                                <?php if ($sub['status'] === 'active'): ?>
                                    <span class="badge badge-success">Active</span>
                                <?php else: ?>
                                    <span class="badge badge-warning"><?php echo ucfirst($sub['status']); ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo timeAgo($sub['created_at']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="card">
            <div class="card-header">
                <h2>Recent Activity</h2>
            </div>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Action</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentActivity as $activity): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($activity['full_name'] ?: 'System'); ?></strong></td>
                            <td><?php echo htmlspecialchars(str_replace('_', ' ', ucwords($activity['action'], '_'))); ?></td>
                            <td><?php echo timeAgo($activity['created_at']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </main>
</div>
</body>
</html>