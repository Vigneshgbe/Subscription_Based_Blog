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
    <title>Admin Dashboard - <?php echo SITE_NAME; ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #f5f7fa;
        }
        
        .admin-layout {
            display: flex;
            min-height: 100vh;
        }
        
        .sidebar {
            width: 260px;
            background: #1a1d29;
            color: white;
            padding: 30px 0;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }
        
        .sidebar-brand {
            padding: 0 30px 30px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 30px;
        }
        
        .sidebar-brand h1 {
            font-size: 24px;
            font-weight: 900;
        }
        
        .sidebar-brand p {
            font-size: 12px;
            opacity: 0.6;
            margin-top: 5px;
        }
        
        .sidebar-menu {
            list-style: none;
        }
        
        .sidebar-menu a {
            display: block;
            padding: 15px 30px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s;
        }
        
        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background: rgba(255,255,255,0.1);
            color: white;
        }
        
        .main-content {
            flex: 1;
            margin-left: 260px;
            padding: 30px;
        }
        
        .top-bar {
            background: white;
            padding: 20px 30px;
            border-radius: 12px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        
        .top-bar h1 {
            font-size: 28px;
        }
        
        .user-info {
            display: flex;
            gap: 15px;
            align-items: center;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        
        .stat-icon {
            font-size: 32px;
            margin-bottom: 10px;
        }
        
        .stat-value {
            font-size: 36px;
            font-weight: 900;
            color: #667eea;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #666;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }
        
        .card {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
        
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .card-header h2 {
            font-size: 20px;
            font-weight: 700;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.2s;
        }
        
        .btn-primary {
            background: #667eea;
            color: white;
        }
        
        .btn-primary:hover {
            background: #5568d3;
        }
        
        .btn-success {
            background: #28a745;
            color: white;
        }
        
        .btn-sm {
            padding: 6px 12px;
            font-size: 13px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th {
            text-align: left;
            padding: 12px;
            background: #f8f9fa;
            font-weight: 700;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #666;
        }
        
        td {
            padding: 15px 12px;
            border-bottom: 1px solid #f0f0f0;
        }
        
        tr:last-child td {
            border-bottom: none;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .badge-success {
            background: #d4edda;
            color: #155724;
        }
        
        .badge-warning {
            background: #fff3cd;
            color: #856404;
        }
        
        .badge-danger {
            background: #f8d7da;
            color: #721c24;
        }
        
        .badge-info {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .action-buttons {
            display: flex;
            gap: 8px;
        }
        
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        
        .quick-action-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-decoration: none;
            display: block;
            transition: transform 0.2s;
        }
        
        .quick-action-card:hover {
            transform: translateY(-4px);
        }
        
        .quick-action-card h3 {
            font-size: 16px;
            margin-bottom: 5px;
        }
        
        .quick-action-card p {
            font-size: 13px;
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <div class="admin-layout">
        <aside class="sidebar">
            <div class="sidebar-brand">
                <h1><?php echo SITE_NAME; ?></h1>
                <p>Admin Panel</p>
            </div>
            <ul class="sidebar-menu">
                <li><a href="dashboard.php" class="active">📊 Dashboard</a></li>
                <li><a href="articles.php">📝 Articles</a></li>
                <li><a href="users.php">👥 Users</a></li>
                <li><a href="subscriptions.php">💳 Subscriptions</a></li>
                <li><a href="transactions.php">💰 Transactions</a></li>
                <li><a href="categories.php">🏷️ Categories</a></li>
                <li><a href="settings.php">⚙️ Settings</a></li>
                <li><a href="../index.php">🌐 View Site</a></li>
                <li><a href="../logout.php">🚪 Logout</a></li>
            </ul>
        </aside>
        
        <main class="main-content">
            <div class="top-bar">
                <h1>Dashboard Overview</h1>
                <div class="user-info">
                    <span>Welcome, <strong><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong></span>
                </div>
            </div>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">👥</div>
                    <div class="stat-value"><?php echo number_format($stats['total_users']); ?></div>
                    <div class="stat-label">Total Users</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">💎</div>
                    <div class="stat-value"><?php echo number_format($stats['active_subscriptions']); ?></div>
                    <div class="stat-label">Active Subscribers</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">📝</div>
                    <div class="stat-value"><?php echo number_format($stats['published_articles']); ?></div>
                    <div class="stat-label">Published Articles</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">💰</div>
                    <div class="stat-value">₹<?php echo number_format($stats['total_revenue'], 0); ?></div>
                    <div class="stat-label">Total Revenue</div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h2>Quick Actions</h2>
                </div>
                <div class="quick-actions">
                    <a href="article-create.php" class="quick-action-card">
                        <h3>New Article</h3>
                        <p>Create a new blog post</p>
                    </a>
                    <a href="users.php" class="quick-action-card" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                        <h3>Manage Users</h3>
                        <p>View and edit users</p>
                    </a>
                    <a href="subscriptions.php" class="quick-action-card" style="background: linear-gradient(135deg, #ff6b6b 0%, #feca57 100%);">
                        <h3>Subscriptions</h3>
                        <p>Monitor subscriptions</p>
                    </a>
                    <a href="settings.php" class="quick-action-card" style="background: linear-gradient(135deg, #4b6cb7 0%, #182848 100%);">
                        <h3>Settings</h3>
                        <p>Configure your site</p>
                    </a>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h2>Recent Articles</h2>
                    <a href="articles.php" class="btn btn-sm btn-primary">View All</a>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Author</th>
                            <th>Status</th>
                            <th>Views</th>
                            <th>Date</th>
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
                                    <span class="badge badge-info">Premium</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo number_format($article['views']); ?></td>
                            <td><?php echo timeAgo($article['created_at']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h2>Recent Subscriptions</h2>
                    <a href="subscriptions.php" class="btn btn-sm btn-primary">View All</a>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Email</th>
                            <th>Plan</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentSubscriptions as $sub): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($sub['full_name']); ?></td>
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
            
            <div class="card">
                <div class="card-header">
                    <h2>Recent Activity</h2>
                </div>
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
                            <td><?php echo htmlspecialchars($activity['full_name'] ?: 'System'); ?></td>
                            <td><?php echo htmlspecialchars(str_replace('_', ' ', ucwords($activity['action'], '_'))); ?></td>
                            <td><?php echo timeAgo($activity['created_at']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>