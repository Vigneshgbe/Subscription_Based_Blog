<?php
require_once '../config.php';
requireAdmin();

$db = db();

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $userId = intval($_POST['user_id'] ?? 0);

    if ($userId && $userId !== $_SESSION['user_id']) {
        if ($action === 'toggle_active') {
            $db->prepare("UPDATE users SET is_active = NOT is_active WHERE id = ?")->execute([$userId]);
            setFlashMessage('User status updated.', 'success');
        } elseif ($action === 'toggle_role') {
            $stmt = $db->prepare("SELECT role FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
            $newRole = $user['role'] === 'admin' ? 'user' : 'admin';
            $db->prepare("UPDATE users SET role = ? WHERE id = ?")->execute([$newRole, $userId]);
            setFlashMessage('User role updated.', 'success');
        } elseif ($action === 'delete') {
            $db->prepare("DELETE FROM users WHERE id = ?")->execute([$userId]);
            setFlashMessage('User deleted.', 'success');
        }
    } else {
        setFlashMessage('Cannot modify your own account or invalid user.', 'danger');
    }
    header('Location: users.php?' . http_build_query(array_filter([
        'page'   => $_POST['current_page'] ?? 1,
        'search' => $_POST['current_search'] ?? '',
        'role'   => $_POST['current_role'] ?? ''
    ])));
    exit;
}

$page       = max(1, intval($_GET['page'] ?? 1));
$search     = sanitizeInput($_GET['search'] ?? '');
$roleFilter = $_GET['role'] ?? '';
$perPage    = 20;
$offset     = ($page - 1) * $perPage;

$where  = "WHERE 1=1";
$params = [];
if ($search) {
    $where .= " AND (u.full_name LIKE ? OR u.email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($roleFilter) {
    $where .= " AND u.role = ?";
    $params[] = $roleFilter;
}

$countStmt = $db->prepare("SELECT COUNT(*) as total FROM users u $where");
$countStmt->execute($params);
$total      = $countStmt->fetch()['total'];
$totalPages = max(1, ceil($total / $perPage));

// Fixed SQL query - removed the problematic reads calculation from main query
$params[] = $perPage;
$params[] = $offset;
$stmt = $db->prepare("
    SELECT u.id, u.email, u.full_name, u.role, u.is_active, u.email_verified, u.created_at,
        (SELECT plan_type FROM subscriptions WHERE user_id = u.id ORDER BY created_at DESC LIMIT 1) as plan,
        (SELECT status FROM subscriptions WHERE user_id = u.id ORDER BY created_at DESC LIMIT 1) as sub_status
    FROM users u
    $where
    ORDER BY u.created_at DESC
    LIMIT ? OFFSET ?
");
$stmt->execute($params);
$users = $stmt->fetchAll();

// Get read counts separately for each user
foreach ($users as &$user) {
    $readStmt = $db->prepare("SELECT COUNT(*) FROM reading_history WHERE user_id = ?");
    $readStmt->execute([$user['id']]);
    $user['reads'] = $readStmt->fetchColumn();
}

$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users - Admin | <?php echo SITE_NAME; ?></title>
    <link rel="icon" type="image/x-icon" href="https://png.pngtree.com/element_our/sm/20180518/sm_5aff60887f7d9.jpg">
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;background:#f5f7fa}
        .admin-layout{display:flex;min-height:100vh}
        .main-content{flex:1;margin-left:280px;padding:30px;transition:margin-left 0.3s ease}
        .mobile-header{display:none;background:#1a1d29;color:white;padding:15px;position:sticky;top:0;z-index:999;align-items:center;justify-content:space-between}
        .menu-toggle{background:none;border:none;color:white;font-size:24px;cursor:pointer;padding:5px}
        .top-bar{background:white;padding:20px 30px;border-radius:12px;margin-bottom:30px;display:flex;justify-content:space-between;align-items:center;box-shadow:0 2px 8px rgba(0,0,0,.05);flex-wrap:wrap;gap:10px}
        .top-bar h1{font-size:24px}
        .card{background:white;padding:30px;border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,.05);margin-bottom:30px}
        .toolbar{display:flex;gap:15px;margin-bottom:25px;flex-wrap:wrap;align-items:center}
        .toolbar input,.toolbar select{padding:10px 15px;border:2px solid #e0e0e0;border-radius:6px;font-size:14px;font-family:inherit}
        .toolbar input:focus,.toolbar select:focus{outline:none;border-color:#667eea}
        .toolbar input{flex:1;min-width:200px}
        .btn{padding:8px 16px;border:none;border-radius:6px;font-weight:600;cursor:pointer;text-decoration:none;display:inline-block;transition:all .2s;font-size:13px;font-family:inherit;white-space:nowrap}
        .btn-primary{background:#667eea;color:white}
        .btn-danger{background:#dc3545;color:white}
        .btn-success{background:#28a745;color:white}
        .btn-warning{background:#ffc107;color:#333}
        .btn-info{background:#17a2b8;color:white}
        .btn-outline{background:transparent;border:2px solid #667eea;color:#667eea}
        .btn-outline:hover{background:#667eea;color:white}
        .btn:hover{opacity:0.9;transform:translateY(-1px)}
        .table-container{overflow-x:auto;-webkit-overflow-scrolling:touch}
        table{width:100%;border-collapse:collapse;min-width:800px}
        th{text-align:left;padding:12px;background:#f8f9fa;font-weight:700;font-size:12px;text-transform:uppercase;letter-spacing:.5px;color:#666;white-space:nowrap}
        td{padding:13px 12px;border-bottom:1px solid #f0f0f0;vertical-align:middle}
        tr:last-child td{border-bottom:none}
        tr:hover td{background:#fafafa}
        .badge{display:inline-block;padding:3px 8px;border-radius:4px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;white-space:nowrap}
        .badge-success{background:#d4edda;color:#155724}
        .badge-warning{background:#fff3cd;color:#856404}
        .badge-danger{background:#f8d7da;color:#721c24}
        .badge-info{background:#d1ecf1;color:#0c5460}
        .badge-secondary{background:#e2e3e5;color:#383d41}
        .badge-purple{background:#e8d5ff;color:#6f42c1}
        .action-btns{display:flex;gap:6px;flex-wrap:wrap}
        .alert{padding:15px 20px;border-radius:8px;margin-bottom:20px;font-weight:600}
        .alert-success{background:#d4edda;color:#155724;border-left:4px solid #28a745}
        .alert-danger{background:#f8d7da;color:#721c24;border-left:4px solid #dc3545}
        .pagination{display:flex;gap:8px;margin-top:20px;justify-content:flex-end;align-items:center;flex-wrap:wrap}
        .page-link{padding:8px 14px;border:2px solid #e0e0e0;border-radius:6px;text-decoration:none;color:#333;font-weight:600;font-size:13px;transition:all .2s}
        .page-link:hover,.page-link.active{background:#667eea;color:white;border-color:#667eea}
        .stats-row{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:15px;margin-bottom:25px}
        .mini-stat{background:#f8f9fa;border-radius:8px;padding:15px;text-align:center}
        .mini-stat strong{display:block;font-size:24px;font-weight:900;color:#667eea}
        .mini-stat span{font-size:12px;color:#666;text-transform:uppercase;letter-spacing:.5px}
        .user-avatar{width:36px;height:36px;border-radius:50%;background:#667eea;color:white;display:inline-flex;align-items:center;justify-content:center;font-weight:700;font-size:14px;flex-shrink:0}
        .user-cell{display:flex;align-items:center;gap:10px}
        .user-cell-info strong{display:block;font-size:14px}
        .user-cell-info small{color:#999;font-size:12px;word-break:break-all}
        .confirm-delete{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9999;align-items:center;justify-content:center;padding:20px}
        .confirm-box{background:white;border-radius:12px;padding:30px;max-width:400px;width:100%;text-align:center}
        .confirm-box h3{margin-bottom:10px}
        .confirm-box p{color:#666;margin-bottom:25px}
        .confirm-box .btns{display:flex;gap:15px;justify-content:center;flex-wrap:wrap}
        
        /* Mobile Styles */
        @media (max-width: 768px) {
            .sidebar{transform:translateX(-100%)}
            .sidebar.active{transform:translateX(0)}
            .main-content{margin-left:0;padding:15px}
            .mobile-header{display:flex}
            .top-bar{padding:15px;border-radius:8px}
            .top-bar h1{font-size:20px}
            .card{padding:20px;border-radius:8px}
            .toolbar{gap:10px}
            .toolbar input{min-width:150px;font-size:13px}
            .toolbar select{font-size:13px}
            .stats-row{grid-template-columns:repeat(2,1fr);gap:10px}
            .mini-stat strong{font-size:20px}
            .mini-stat span{font-size:11px}
            .action-btns{flex-direction:column}
            .action-btns .btn{width:100%;text-align:center}
            .pagination{justify-content:center}
            .page-link{padding:6px 10px;font-size:12px}
            th,td{padding:10px 8px;font-size:12px}
            .user-avatar{width:32px;height:32px;font-size:12px}
            .confirm-box{padding:20px}
        }
        
        @media (max-width: 480px) {
            .top-bar{flex-direction:column;align-items:flex-start}
            .stats-row{grid-template-columns:1fr}
            .toolbar input{width:100%}
            table{min-width:600px}
        }
        
        .overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:999}
        .overlay.active{display:block}
    </style>
</head>
<body>
<div class="mobile-header">
    <button class="menu-toggle" onclick="toggleMenu()">☰</button>
    <h1><?php echo SITE_NAME; ?></h1>
</div>

<div class="overlay" id="overlay" onclick="toggleMenu()"></div>

<div class="admin-layout">
    <?php require_once 'sidebar.php'; ?>

    <main class="main-content">
        <div class="top-bar">
            <h1>👥 Users</h1>
            <span style="color:#666;font-size:14px"><?php echo $total; ?> total users</span>
        </div>

        <?php if ($flash): ?>
        <div class="alert alert-<?php echo $flash['type']; ?>"><?php echo htmlspecialchars($flash['message']); ?></div>
        <?php endif; ?>

        <div class="card">
            <div class="stats-row">
                <?php
                $totalUsers  = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
                $adminCount  = $db->query("SELECT COUNT(*) FROM users WHERE role='admin'")->fetchColumn();
                $activeCount = $db->query("SELECT COUNT(*) FROM users WHERE is_active=1")->fetchColumn();
                $subCount    = $db->query("SELECT COUNT(DISTINCT user_id) FROM subscriptions WHERE status='active' AND plan_type IN('monthly','yearly')")->fetchColumn();
                ?>
                <div class="mini-stat"><strong><?php echo $totalUsers; ?></strong><span>Total Users</span></div>
                <div class="mini-stat"><strong><?php echo $subCount; ?></strong><span>Subscribers</span></div>
                <div class="mini-stat"><strong><?php echo $activeCount; ?></strong><span>Active</span></div>
                <div class="mini-stat"><strong><?php echo $adminCount; ?></strong><span>Admins</span></div>
            </div>

            <form method="GET" class="toolbar">
                <input type="text" name="search" placeholder="Search name or email..." value="<?php echo htmlspecialchars($search); ?>">
                <select name="role" onchange="this.form.submit()">
                    <option value="">All Roles</option>
                    <option value="user"  <?php echo $roleFilter==='user'?'selected':''; ?>>Users</option>
                    <option value="admin" <?php echo $roleFilter==='admin'?'selected':''; ?>>Admins</option>
                </select>
                <button type="submit" class="btn btn-outline">Search</button>
                <?php if ($search || $roleFilter): ?>
                    <a href="users.php" class="btn" style="background:#f0f0f0;color:#333">Clear</a>
                <?php endif; ?>
            </form>

            <?php if (empty($users)): ?>
                <p style="text-align:center;padding:40px;color:#999">No users found.</p>
            <?php else: ?>
            <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Role</th>
                        <th>Plan</th>
                        <th>Status</th>
                        <th>Reads</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td>
                        <div class="user-cell">
                            <div class="user-avatar"><?php echo strtoupper(substr($user['full_name'],0,1)); ?></div>
                            <div class="user-cell-info">
                                <strong><?php echo htmlspecialchars($user['full_name']); ?></strong>
                                <small><?php echo htmlspecialchars($user['email']); ?></small>
                            </div>
                        </div>
                    </td>
                    <td>
                        <?php echo $user['role']==='admin'
                            ? '<span class="badge badge-purple">Admin</span>'
                            : '<span class="badge badge-secondary">User</span>'; ?>
                    </td>
                    <td>
                        <?php
                        $plan = $user['plan'] ?? 'free';
                        $subStatus = $user['sub_status'] ?? 'active';
                        if ($plan === 'monthly') echo '<span class="badge badge-info">Monthly</span>';
                        elseif ($plan === 'yearly') echo '<span class="badge badge-success">Yearly</span>';
                        else echo '<span class="badge badge-secondary">Free</span>';
                        ?>
                    </td>
                    <td>
                        <?php echo $user['is_active']
                            ? '<span class="badge badge-success">Active</span>'
                            : '<span class="badge badge-danger">Inactive</span>'; ?>
                        <?php if ($user['email_verified']): ?>
                            <span class="badge badge-info">✓ Verified</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo number_format($user['reads']); ?></td>
                    <td><?php echo formatDate($user['created_at']); ?></td>
                    <td>
                        <?php if ($user['id'] !== $_SESSION['user_id']): ?>
                        <div class="action-btns">
                            <form method="POST" style="display:inline">
                                <input type="hidden" name="action" value="toggle_active">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <input type="hidden" name="current_page" value="<?php echo $page; ?>">
                                <input type="hidden" name="current_search" value="<?php echo htmlspecialchars($search); ?>">
                                <input type="hidden" name="current_role" value="<?php echo htmlspecialchars($roleFilter); ?>">
                                <button type="submit" class="btn <?php echo $user['is_active']?'btn-warning':'btn-success'; ?>">
                                    <?php echo $user['is_active']?'Disable':'Enable'; ?>
                                </button>
                            </form>
                            <form method="POST" style="display:inline">
                                <input type="hidden" name="action" value="toggle_role">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <input type="hidden" name="current_page" value="<?php echo $page; ?>">
                                <input type="hidden" name="current_search" value="<?php echo htmlspecialchars($search); ?>">
                                <input type="hidden" name="current_role" value="<?php echo htmlspecialchars($roleFilter); ?>">
                                <button type="submit" class="btn btn-info">
                                    <?php echo $user['role']==='admin'?'Remove Admin':'Make Admin'; ?>
                                </button>
                            </form>
                            <button class="btn btn-danger" onclick="confirmDelete(<?php echo $user['id']; ?>,'<?php echo addslashes($user['full_name']); ?>')">Delete</button>
                        </div>
                        <?php else: ?>
                            <span style="color:#999;font-size:12px">You</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            </div>

            <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <span style="color:#666;font-size:13px;margin-right:10px">Showing <?php echo ($offset+1)?>–<?php echo min($offset+$perPage,$total)?> of <?php echo $total?></span>
                <?php for ($i=1;$i<=$totalPages;$i++): ?>
                <a href="?page=<?php echo $i?>&search=<?php echo urlencode($search)?>&role=<?php echo urlencode($roleFilter)?>"
                   class="page-link <?php echo $i==$page?'active':''; ?>"><?php echo $i?></a>
                <?php endfor; ?>
            </div>
            <?php endif; ?>
            <?php endif; ?>
        </div>
    </main>
</div>

<div class="confirm-delete" id="deleteModal">
    <div class="confirm-box">
        <h3>🗑️ Delete User</h3>
        <p id="deleteMsg">Delete this user? All their data will be removed permanently.</p>
        <div class="btns">
            <button onclick="closeDelete()" class="btn" style="background:#f0f0f0;color:#333">Cancel</button>
            <form method="POST" id="deleteForm">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="user_id" id="deleteId">
                <input type="hidden" name="current_page" value="<?php echo $page; ?>">
                <input type="hidden" name="current_search" value="<?php echo htmlspecialchars($search); ?>">
                <input type="hidden" name="current_role" value="<?php echo htmlspecialchars($roleFilter); ?>">
                <button type="submit" class="btn btn-danger">Yes, Delete</button>
            </form>
        </div>
    </div>
</div>
<script>
function toggleMenu() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('overlay');
    sidebar.classList.toggle('active');
    overlay.classList.toggle('active');
}

function confirmDelete(id, name) {
    document.getElementById('deleteId').value = id;
    document.getElementById('deleteMsg').textContent = 'Delete "' + name + '"? This cannot be undone.';
    document.getElementById('deleteModal').style.display = 'flex';
}

function closeDelete() { 
    document.getElementById('deleteModal').style.display = 'none'; 
}

document.getElementById('deleteModal').addEventListener('click', function(e){ 
    if(e.target===this) closeDelete(); 
});

// Close sidebar when clicking on a link (mobile)
document.querySelectorAll('.sidebar-menu a').forEach(link => {
    link.addEventListener('click', () => {
        if (window.innerWidth <= 768) {
            toggleMenu();
        }
    });
});
</script>
</body>
</html>