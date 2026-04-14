<?php
require_once '../config.php';
requireAdmin();

$db = db();

// Handle cancel action
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $subId  = intval($_POST['sub_id'] ?? 0);
    if ($action === 'cancel' && $subId) {
        $db->prepare("UPDATE subscriptions SET status='canceled', cancel_at_period_end=1 WHERE id=?")->execute([$subId]);
        FlashMessage('Subscription marked for cancellation.', 'success');
    } elseif ($action === 'reactivate' && $subId) {
        $db->prepare("UPDATE subscriptions SET status='active', cancel_at_period_end=0 WHERE id=?")->execute([$subId]);
        FlashMessage('Subscription reactivated.', 'success');
    }
    header('Location: subscriptions.php?page=' . intval($_POST['current_page'] ?? 1));
    exit;
}

$page    = max(1, intval($_GET['page'] ?? 1));
$search  = sanitizeInput($_GET['search'] ?? '');
$planFilter   = $_GET['plan'] ?? '';
$statusFilter = $_GET['status'] ?? '';
$perPage = 20;
$offset  = ($page - 1) * $perPage;

$where  = "WHERE 1=1";
$params = [];

if ($search) {
    $where .= " AND (u.full_name LIKE ? OR u.email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($planFilter) {
    $where .= " AND s.plan_type = ?";
    $params[] = $planFilter;
}
if ($statusFilter) {
    $where .= " AND s.status = ?";
    $params[] = $statusFilter;
}

$countStmt = $db->prepare("SELECT COUNT(*) as total FROM subscriptions s JOIN users u ON s.user_id=u.id $where");
$countStmt->execute($params);
$total      = $countStmt->fetch()['total'];
$totalPages = max(1, ceil($total / $perPage));

$params[] = $perPage;
$params[] = $offset;
$stmt = $db->prepare("
    SELECT s.*, u.full_name, u.email
    FROM subscriptions s
    JOIN users u ON s.user_id = u.id
    $where
    ORDER BY s.created_at DESC
    LIMIT ? OFFSET ?
");
$stmt->execute($params);
$subscriptions = $stmt->fetchAll();

// Summary stats
$totalSubs   = $db->query("SELECT COUNT(*) FROM subscriptions WHERE plan_type IN('monthly','yearly') AND status='active'")->fetchColumn();
$monthlySubs = $db->query("SELECT COUNT(*) FROM subscriptions WHERE plan_type='monthly' AND status='active'")->fetchColumn();
$yearlySubs  = $db->query("SELECT COUNT(*) FROM subscriptions WHERE plan_type='yearly' AND status='active'")->fetchColumn();
$canceledSubs= $db->query("SELECT COUNT(*) FROM subscriptions WHERE status='canceled'")->fetchColumn();

$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscriptions - Admin | <?php echo SITE_NAME; ?></title>
    <link rel="icon" type="image/x-icon" href="https://raw.githubusercontent.com/Vigneshgbe/Subscription_Based_Blog/refs/heads/main/assets/Logo.png">
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;background:#f5f7fa}
        .admin-layout{display:flex;min-height:100vh}
        .main-content{flex:1;margin-left:280px;padding:30px}
        .top-bar{background:white;padding:20px 30px;border-radius:12px;margin-bottom:30px;display:flex;justify-content:space-between;align-items:center;box-shadow:0 2px 8px rgba(0,0,0,.05)}
        .top-bar h1{font-size:24px}
        .stats-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:20px;margin-bottom:25px}
        .stat-card{background:white;padding:20px;border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,.05);text-align:center}
        .stat-card strong{display:block;font-size:32px;font-weight:900;color:#667eea}
        .stat-card span{font-size:12px;color:#666;text-transform:uppercase;letter-spacing:.5px;margin-top:4px;display:block}
        .card{background:white;padding:30px;border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,.05);margin-bottom:30px}
        .toolbar{display:flex;gap:15px;margin-bottom:25px;flex-wrap:wrap;align-items:center}
        .toolbar input,.toolbar select{padding:10px 15px;border:2px solid #e0e0e0;border-radius:6px;font-size:14px;font-family:inherit}
        .toolbar input:focus,.toolbar select:focus{outline:none;border-color:#667eea}
        .toolbar input{flex:1;min-width:180px}
        .btn{padding:8px 16px;border:none;border-radius:6px;font-weight:600;cursor:pointer;text-decoration:none;display:inline-block;transition:all .2s;font-size:13px;font-family:inherit}
        .btn-primary{background:#667eea;color:white}
        .btn-danger{background:#dc3545;color:white}
        .btn-success{background:#28a745;color:white}
        .btn-outline{background:transparent;border:2px solid #667eea;color:#667eea}
        .btn-outline:hover{background:#667eea;color:white}
        table{width:100%;border-collapse:collapse}
        th{text-align:left;padding:12px;background:#f8f9fa;font-weight:700;font-size:12px;text-transform:uppercase;letter-spacing:.5px;color:#666}
        td{padding:13px 12px;border-bottom:1px solid #f0f0f0;vertical-align:middle;font-size:14px}
        tr:last-child td{border-bottom:none}
        tr:hover td{background:#fafafa}
        .badge{display:inline-block;padding:3px 8px;border-radius:4px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px}
        .badge-success{background:#d4edda;color:#155724}
        .badge-warning{background:#fff3cd;color:#856404}
        .badge-danger{background:#f8d7da;color:#721c24}
        .badge-info{background:#d1ecf1;color:#0c5460}
        .badge-secondary{background:#e2e3e5;color:#383d41}
        .alert{padding:15px 20px;border-radius:8px;margin-bottom:20px;font-weight:600}
        .alert-success{background:#d4edda;color:#155724;border-left:4px solid #28a745}
        .alert-danger{background:#f8d7da;color:#721c24;border-left:4px solid #dc3545}
        .pagination{display:flex;gap:8px;margin-top:20px;justify-content:flex-end;align-items:center}
        .page-link{padding:8px 14px;border:2px solid #e0e0e0;border-radius:6px;text-decoration:none;color:#333;font-weight:600;font-size:13px;transition:all .2s}
        .page-link:hover,.page-link.active{background:#667eea;color:white;border-color:#667eea}
        .stripe-id{font-family:monospace;font-size:11px;color:#999;max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;display:block}
        @media(max-width:900px){.stats-grid{grid-template-columns:repeat(2,1fr)}}
    </style>
</head>
<body>
<div class="admin-layout">
    <?php require_once 'sidebar.php'; ?>

    <main class="main-content">
        <div class="top-bar">
            <h1>💳 Subscriptions</h1>
        </div>

        <?php if ($flash): ?>
        <div class="alert alert-<?php echo $flash['type']; ?>"><?php echo htmlspecialchars($flash['message']); ?></div>
        <?php endif; ?>

        <div class="stats-grid">
            <div class="stat-card"><strong><?php echo $totalSubs; ?></strong><span>Active Subscribers</span></div>
            <div class="stat-card"><strong><?php echo $monthlySubs; ?></strong><span>Monthly</span></div>
            <div class="stat-card"><strong><?php echo $yearlySubs; ?></strong><span>Yearly</span></div>
            <div class="stat-card"><strong><?php echo $canceledSubs; ?></strong><span>Canceled</span></div>
        </div>

        <div class="card">
            <form method="GET" class="toolbar">
                <input type="text" name="search" placeholder="Search user..." value="<?php echo htmlspecialchars($search); ?>">
                <select name="plan" onchange="this.form.submit()">
                    <option value="">All Plans</option>
                    <option value="free"    <?php echo $planFilter==='free'?'selected':''; ?>>Free</option>
                    <option value="monthly" <?php echo $planFilter==='monthly'?'selected':''; ?>>Monthly</option>
                    <option value="yearly"  <?php echo $planFilter==='yearly'?'selected':''; ?>>Yearly</option>
                </select>
                <select name="status" onchange="this.form.submit()">
                    <option value="">All Status</option>
                    <option value="active"   <?php echo $statusFilter==='active'?'selected':''; ?>>Active</option>
                    <option value="canceled" <?php echo $statusFilter==='canceled'?'selected':''; ?>>Canceled</option>
                    <option value="expired"  <?php echo $statusFilter==='expired'?'selected':''; ?>>Expired</option>
                    <option value="past_due" <?php echo $statusFilter==='past_due'?'selected':''; ?>>Past Due</option>
                </select>
                <button type="submit" class="btn btn-outline">Filter</button>
                <?php if ($search || $planFilter || $statusFilter): ?>
                    <a href="subscriptions.php" class="btn" style="background:#f0f0f0;color:#333">Clear</a>
                <?php endif; ?>
            </form>

            <?php if (empty($subscriptions)): ?>
                <p style="text-align:center;padding:40px;color:#999">No subscriptions found.</p>
            <?php else: ?>
            <div style="overflow-x:auto">
            <table>
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Plan</th>
                        <th>Status</th>
                        <th>Period</th>
                        <th>Stripe ID</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($subscriptions as $sub): ?>
                <tr>
                    <td>
                        <strong><?php echo htmlspecialchars($sub['full_name']); ?></strong><br>
                        <small style="color:#999"><?php echo htmlspecialchars($sub['email']); ?></small>
                    </td>
                    <td>
                        <?php if ($sub['plan_type']==='monthly'): ?>
                            <span class="badge badge-info">Monthly</span>
                        <?php elseif ($sub['plan_type']==='yearly'): ?>
                            <span class="badge badge-success">Yearly</span>
                        <?php else: ?>
                            <span class="badge badge-secondary">Free</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($sub['status']==='active'): ?>
                            <span class="badge badge-success">Active<?php echo $sub['cancel_at_period_end']?' (Cancels)':''; ?></span>
                        <?php elseif ($sub['status']==='canceled'): ?>
                            <span class="badge badge-danger">Canceled</span>
                        <?php elseif ($sub['status']==='past_due'): ?>
                            <span class="badge badge-warning">Past Due</span>
                        <?php else: ?>
                            <span class="badge badge-secondary"><?php echo ucfirst($sub['status']); ?></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($sub['current_period_start']): ?>
                            <small><?php echo formatDate($sub['current_period_start']); ?></small><br>
                            <small>→ <?php echo formatDate($sub['current_period_end']); ?></small>
                        <?php else: ?>
                            <span style="color:#999">—</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($sub['stripe_subscription_id']): ?>
                            <span class="stripe-id" title="<?php echo htmlspecialchars($sub['stripe_subscription_id']); ?>">
                                <?php echo htmlspecialchars($sub['stripe_subscription_id']); ?>
                            </span>
                        <?php else: ?>
                            <span style="color:#ccc">—</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo formatDate($sub['created_at']); ?></td>
                    <td>
                        <?php if ($sub['plan_type'] !== 'free'): ?>
                        <form method="POST" style="display:inline">
                            <input type="hidden" name="current_page" value="<?php echo $page; ?>">
                            <?php if ($sub['status']==='active' && !$sub['cancel_at_period_end']): ?>
                                <input type="hidden" name="action" value="cancel">
                                <input type="hidden" name="sub_id" value="<?php echo $sub['id']; ?>">
                                <button type="submit" class="btn btn-danger">Cancel</button>
                            <?php elseif ($sub['status']==='canceled' || $sub['cancel_at_period_end']): ?>
                                <input type="hidden" name="action" value="reactivate">
                                <input type="hidden" name="sub_id" value="<?php echo $sub['id']; ?>">
                                <button type="submit" class="btn btn-success">Reactivate</button>
                            <?php endif; ?>
                        </form>
                        <?php else: ?>
                            <span style="color:#ccc;font-size:12px">—</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            </div>

            <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php for ($i=1;$i<=$totalPages;$i++): ?>
                <a href="?page=<?php echo $i?>&search=<?php echo urlencode($search)?>&plan=<?php echo urlencode($planFilter)?>&status=<?php echo urlencode($statusFilter)?>"
                   class="page-link <?php echo $i==$page?'active':''; ?>"><?php echo $i?></a>
                <?php endfor; ?>
            </div>
            <?php endif; ?>
            <?php endif; ?>
        </div>
    </main>
</div>
</body>
</html>