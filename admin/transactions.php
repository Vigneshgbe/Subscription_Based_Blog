<?php
require_once '../config.php';
requireAdmin();

$db = db();

$page         = max(1, intval($_GET['page'] ?? 1));
$search       = sanitizeInput($_GET['search'] ?? '');
$statusFilter = $_GET['status'] ?? '';
$perPage      = 20;
$offset       = ($page - 1) * $perPage;

$where  = "WHERE 1=1";
$params = [];
if ($search) {
    $where .= " AND (u.full_name LIKE ? OR u.email LIKE ? OR t.stripe_payment_intent_id LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($statusFilter) {
    $where .= " AND t.status = ?";
    $params[] = $statusFilter;
}

$countStmt = $db->prepare("SELECT COUNT(*) as total FROM transactions t JOIN users u ON t.user_id=u.id $where");
$countStmt->execute($params);
$total      = $countStmt->fetch()['total'];
$totalPages = max(1, ceil($total / $perPage));

$params[] = $perPage;
$params[] = $offset;
$stmt = $db->prepare("
    SELECT t.*, u.full_name, u.email
    FROM transactions t
    JOIN users u ON t.user_id = u.id
    $where
    ORDER BY t.created_at DESC
    LIMIT ? OFFSET ?
");
$stmt->execute($params);
$transactions = $stmt->fetchAll();

// Revenue stats
$totalRevenue     = $db->query("SELECT SUM(amount) FROM transactions WHERE status='succeeded'")->fetchColumn() ?? 0;
$monthlyRevenue   = $db->query("SELECT SUM(amount) FROM transactions WHERE status='succeeded' AND created_at >= DATE_FORMAT(NOW(),'%Y-%m-01')")->fetchColumn() ?? 0;
$totalTxns        = $db->query("SELECT COUNT(*) FROM transactions")->fetchColumn();
$successfulTxns   = $db->query("SELECT COUNT(*) FROM transactions WHERE status='succeeded'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transactions - Admin | <?php echo SITE_NAME; ?></title>
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
        .stat-card strong{display:block;font-size:28px;font-weight:900;color:#667eea}
        .stat-card span{font-size:12px;color:#666;text-transform:uppercase;letter-spacing:.5px;margin-top:4px;display:block}
        .card{background:white;padding:30px;border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,.05)}
        .toolbar{display:flex;gap:15px;margin-bottom:25px;flex-wrap:wrap;align-items:center}
        .toolbar input,.toolbar select{padding:10px 15px;border:2px solid #e0e0e0;border-radius:6px;font-size:14px;font-family:inherit}
        .toolbar input{flex:1;min-width:180px}
        .btn{padding:8px 16px;border:none;border-radius:6px;font-weight:600;cursor:pointer;text-decoration:none;display:inline-block;transition:all .2s;font-size:13px;font-family:inherit}
        .btn-outline{background:transparent;border:2px solid #667eea;color:#667eea}
        .btn-outline:hover{background:#667eea;color:white}
        table{width:100%;border-collapse:collapse}
        th{text-align:left;padding:12px;background:#f8f9fa;font-weight:700;font-size:12px;text-transform:uppercase;letter-spacing:.5px;color:#666}
        td{padding:13px 12px;border-bottom:1px solid #f0f0f0;vertical-align:middle;font-size:14px}
        tr:last-child td{border-bottom:none}
        tr:hover td{background:#fafafa}
        .badge{display:inline-block;padding:3px 8px;border-radius:4px;font-size:11px;font-weight:700;text-transform:uppercase}
        .badge-success{background:#d4edda;color:#155724}
        .badge-warning{background:#fff3cd;color:#856404}
        .badge-danger{background:#f8d7da;color:#721c24}
        .badge-secondary{background:#e2e3e5;color:#383d41}
        .amount{font-weight:700;font-size:15px;color:#333}
        .amount.success{color:#28a745}
        .amount.failed{color:#dc3545;text-decoration:line-through}
        .mono{font-family:monospace;font-size:12px;color:#999;max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;display:block}
        .pagination{display:flex;gap:8px;margin-top:20px;justify-content:flex-end}
        .page-link{padding:8px 14px;border:2px solid #e0e0e0;border-radius:6px;text-decoration:none;color:#333;font-weight:600;font-size:13px;transition:all .2s}
        .page-link:hover,.page-link.active{background:#667eea;color:white;border-color:#667eea}
        @media(max-width:900px){.stats-grid{grid-template-columns:repeat(2,1fr)}}
    </style>
</head>
<body>
<div class="admin-layout">
    <?php require_once 'sidebar.php'; ?>

    <main class="main-content">
        <div class="top-bar">
            <h1>💰 Transactions</h1>
        </div>

        <div class="stats-grid">
            <div class="stat-card"><strong>$<?php echo number_format($totalRevenue, 0); ?></strong><span>Total Revenue</span></div>
            <div class="stat-card"><strong>$<?php echo number_format($monthlyRevenue, 0); ?></strong><span>This Month</span></div>
            <div class="stat-card"><strong><?php echo $totalTxns; ?></strong><span>Total Transactions</span></div>
            <div class="stat-card"><strong><?php echo $successfulTxns; ?></strong><span>Successful</span></div>
        </div>

        <div class="card">
            <form method="GET" class="toolbar">
                <input type="text" name="search" placeholder="Search user or payment ID..." value="<?php echo htmlspecialchars($search); ?>">
                <select name="status" onchange="this.form.submit()">
                    <option value="">All Status</option>
                    <option value="succeeded" <?php echo $statusFilter==='succeeded'?'selected':''; ?>>Succeeded</option>
                    <option value="pending"   <?php echo $statusFilter==='pending'?'selected':''; ?>>Pending</option>
                    <option value="failed"    <?php echo $statusFilter==='failed'?'selected':''; ?>>Failed</option>
                    <option value="refunded"  <?php echo $statusFilter==='refunded'?'selected':''; ?>>Refunded</option>
                </select>
                <button type="submit" class="btn btn-outline">Filter</button>
                <?php if ($search || $statusFilter): ?>
                    <a href="transactions.php" class="btn" style="background:#f0f0f0;color:#333">Clear</a>
                <?php endif; ?>
            </form>

            <?php if (empty($transactions)): ?>
                <p style="text-align:center;padding:40px;color:#999">No transactions found.</p>
            <?php else: ?>
            <div style="overflow-x:auto">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>User</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Method</th>
                        <th>Payment Intent</th>
                        <th>Description</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($transactions as $txn): ?>
                <tr>
                    <td style="color:#999;font-size:12px">#<?php echo $txn['id']; ?></td>
                    <td>
                        <strong><?php echo htmlspecialchars($txn['full_name']); ?></strong><br>
                        <small style="color:#999"><?php echo htmlspecialchars($txn['email']); ?></small>
                    </td>
                    <td>
                        <span class="amount <?php echo $txn['status']==='succeeded'?'success':($txn['status']==='failed'?'failed':''); ?>">
                            $<?php echo number_format($txn['amount'], 2); ?>
                        </span>
                        <small style="color:#999"><?php echo strtoupper($txn['currency']); ?></small>
                    </td>
                    <td>
                        <?php if ($txn['status']==='succeeded'): ?>
                            <span class="badge badge-success">Succeeded</span>
                        <?php elseif ($txn['status']==='pending'): ?>
                            <span class="badge badge-warning">Pending</span>
                        <?php elseif ($txn['status']==='failed'): ?>
                            <span class="badge badge-danger">Failed</span>
                        <?php else: ?>
                            <span class="badge badge-secondary"><?php echo ucfirst($txn['status']); ?></span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($txn['payment_method'] ?? '—'); ?></td>
                    <td>
                        <?php if ($txn['stripe_payment_intent_id']): ?>
                            <span class="mono" title="<?php echo htmlspecialchars($txn['stripe_payment_intent_id']); ?>">
                                <?php echo htmlspecialchars($txn['stripe_payment_intent_id']); ?>
                            </span>
                        <?php else: ?>
                            <span style="color:#ccc">—</span>
                        <?php endif; ?>
                    </td>
                    <td style="max-width:180px;font-size:13px;color:#666">
                        <?php echo htmlspecialchars(substr($txn['description'] ?? '', 0, 60)); ?>
                    </td>
                    <td><?php echo formatDate($txn['created_at']); ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            </div>

            <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php for ($i=1;$i<=$totalPages;$i++): ?>
                <a href="?page=<?php echo $i?>&search=<?php echo urlencode($search)?>&status=<?php echo urlencode($statusFilter)?>"
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