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
        } 
        elseif ($action === 'toggle_role') {
            $stmt = $db->prepare("SELECT role FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
            $newRole = $user['role'] === 'admin' ? 'user' : 'admin';
            $db->prepare("UPDATE users SET role = ? WHERE id = ?")->execute([$newRole, $userId]);
            setFlashMessage('User role updated.', 'success');
        } 
        elseif ($action === 'delete') {
            $db->prepare("DELETE FROM users WHERE id = ?")->execute([$userId]);
            setFlashMessage('User deleted.', 'success');
        }
        elseif ($action === 'reset_password') {
            // Generate random password
            $newPassword = bin2hex(random_bytes(8)); // 16 character password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $db->prepare("UPDATE users SET password_hash = ?, reset_token = NULL, reset_token_expiry = NULL WHERE id = ?")->execute([$hashedPassword, $userId]);
            
            // Store password temporarily in session to show to admin
            $_SESSION['temp_password'] = [
                'user_id' => $userId,
                'password' => $newPassword
            ];
            setFlashMessage('Password reset successfully. New password generated.', 'success');
        }
        elseif ($action === 'verify_email') {
            $db->prepare("UPDATE users SET email_verified = 1, verification_token = NULL WHERE id = ?")->execute([$userId]);
            setFlashMessage('Email verified successfully.', 'success');
        }
        elseif ($action === 'unverify_email') {
            $db->prepare("UPDATE users SET email_verified = 0 WHERE id = ?")->execute([$userId]);
            setFlashMessage('Email verification removed.', 'success');
        }
        elseif ($action === 'generate_verification') {
            $verificationToken = bin2hex(random_bytes(32));
            $db->prepare("UPDATE users SET verification_token = ?, email_verified = 0 WHERE id = ?")->execute([$verificationToken, $userId]);
            setFlashMessage('New verification token generated.', 'success');
        }
    } 
    elseif ($action === 'bulk_action') {
        $bulkAction = $_POST['bulk_action'] ?? '';
        $selectedUsers = $_POST['selected_users'] ?? [];
        
        if (!empty($selectedUsers) && $bulkAction) {
            $count = 0;
            foreach ($selectedUsers as $uid) {
                $uid = intval($uid);
                if ($uid && $uid !== $_SESSION['user_id']) {
                    if ($bulkAction === 'activate') {
                        $db->prepare("UPDATE users SET is_active = 1 WHERE id = ?")->execute([$uid]);
                        $count++;
                    } elseif ($bulkAction === 'deactivate') {
                        $db->prepare("UPDATE users SET is_active = 0 WHERE id = ?")->execute([$uid]);
                        $count++;
                    } elseif ($bulkAction === 'verify') {
                        $db->prepare("UPDATE users SET email_verified = 1, verification_token = NULL WHERE id = ?")->execute([$uid]);
                        $count++;
                    } elseif ($bulkAction === 'delete') {
                        $db->prepare("DELETE FROM users WHERE id = ?")->execute([$uid]);
                        $count++;
                    }
                }
            }
            setFlashMessage("Bulk action completed on $count users.", 'success');
        }
    }
    else {
        setFlashMessage('Cannot modify your own account or invalid user.', 'danger');
    }
    header('Location: users.php?' . http_build_query(array_filter([
        'page'   => $_POST['current_page'] ?? 1,
        'search' => $_POST['current_search'] ?? '',
        'role'   => $_POST['current_role'] ?? '',
        'status' => $_POST['current_status'] ?? '',
        'verified' => $_POST['current_verified'] ?? ''
    ])));
    exit;
}

// Export users as CSV
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="users_' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID', 'Name', 'Email', 'Role', 'Plan', 'Status', 'Verified', 'Reads', 'Joined', 'Last Login']);
    
    $exportStmt = $db->query("
        SELECT u.id, u.full_name, u.email, u.role, u.is_active, u.email_verified, u.created_at, u.last_login,
            (SELECT plan_type FROM subscriptions WHERE user_id = u.id ORDER BY created_at DESC LIMIT 1) as plan
        FROM users u
        ORDER BY u.created_at DESC
    ");
    
    while ($row = $exportStmt->fetch()) {
        $readStmt = $db->prepare("SELECT COUNT(*) FROM reading_history WHERE user_id = ?");
        $readStmt->execute([$row['id']]);
        $reads = $readStmt->fetchColumn();
        
        fputcsv($output, [
            $row['id'],
            $row['full_name'],
            $row['email'],
            $row['role'],
            $row['plan'] ?? 'free',
            $row['is_active'] ? 'Active' : 'Inactive',
            $row['email_verified'] ? 'Yes' : 'No',
            $reads,
            $row['created_at'],
            $row['last_login'] ?? 'Never'
        ]);
    }
    
    fclose($output);
    exit;
}

$page       = max(1, intval($_GET['page'] ?? 1));
$search     = sanitizeInput($_GET['search'] ?? '');
$roleFilter = $_GET['role'] ?? '';
$statusFilter = $_GET['status'] ?? '';
$verifiedFilter = $_GET['verified'] ?? '';
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
if ($statusFilter === 'active') {
    $where .= " AND u.is_active = 1";
} elseif ($statusFilter === 'inactive') {
    $where .= " AND u.is_active = 0";
}
if ($verifiedFilter === 'verified') {
    $where .= " AND u.email_verified = 1";
} elseif ($verifiedFilter === 'unverified') {
    $where .= " AND u.email_verified = 0";
}

$countStmt = $db->prepare("SELECT COUNT(*) as total FROM users u $where");
$countStmt->execute($params);
$total      = $countStmt->fetch()['total'];
$totalPages = max(1, ceil($total / $perPage));

$params[] = $perPage;
$params[] = $offset;
$stmt = $db->prepare("
    SELECT u.id, u.email, u.full_name, u.role, u.is_active, u.email_verified, u.created_at, u.last_login,
        (SELECT plan_type FROM subscriptions WHERE user_id = u.id ORDER BY created_at DESC LIMIT 1) as plan,
        (SELECT status FROM subscriptions WHERE user_id = u.id ORDER BY created_at DESC LIMIT 1) as sub_status
    FROM users u
    $where
    ORDER BY u.created_at DESC
    LIMIT ? OFFSET ?
");
$stmt->execute($params);
$users = $stmt->fetchAll();

// Get read counts for each user
foreach ($users as &$user) {
    $readStmt = $db->prepare("SELECT COUNT(*) FROM reading_history WHERE user_id = ?");
    $readStmt->execute([$user['id']]);
    $user['reads'] = $readStmt->fetchColumn();
}

// Get statistics
$totalUsers  = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
$adminCount  = $db->query("SELECT COUNT(*) FROM users WHERE role='admin'")->fetchColumn();
$activeCount = $db->query("SELECT COUNT(*) FROM users WHERE is_active=1")->fetchColumn();
$verifiedCount = $db->query("SELECT COUNT(*) FROM users WHERE email_verified=1")->fetchColumn();
$subCount    = $db->query("SELECT COUNT(DISTINCT user_id) FROM subscriptions WHERE status='active' AND plan_type IN('monthly','yearly')")->fetchColumn();
$newUsersToday = $db->query("SELECT COUNT(*) FROM users WHERE DATE(created_at) = CURDATE()")->fetchColumn();

$flash = getFlashMessage();
$tempPassword = $_SESSION['temp_password'] ?? null;
unset($_SESSION['temp_password']);
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
        .btn-sm{padding:5px 10px;font-size:12px}
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
        .alert-info{background:#d1ecf1;color:#0c5460;border-left:4px solid #17a2b8}
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
        .modal{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9999;align-items:center;justify-content:center;padding:20px}
        .modal.show{display:flex}
        .modal-box{background:white;border-radius:12px;padding:30px;max-width:500px;width:100%;max-height:90vh;overflow-y:auto}
        .modal-box h3{margin-bottom:10px}
        .modal-box p{color:#666;margin-bottom:25px}
        .modal-box .btns{display:flex;gap:15px;justify-content:center;flex-wrap:wrap;margin-top:20px}
        .modal-close{position:absolute;top:15px;right:15px;background:none;border:none;font-size:24px;cursor:pointer;color:#999}
        .form-group{margin-bottom:20px}
        .form-group label{display:block;margin-bottom:8px;font-weight:600;color:#333}
        .form-group input,.form-group select,.form-group textarea{width:100%;padding:10px;border:2px solid #e0e0e0;border-radius:6px;font-family:inherit;font-size:14px}
        .form-group input:focus,.form-group select:focus,.form-group textarea:focus{outline:none;border-color:#667eea}
        .info-row{display:grid;grid-template-columns:140px 1fr;gap:10px;padding:10px 0;border-bottom:1px solid #f0f0f0}
        .info-row:last-child{border-bottom:none}
        .info-row strong{color:#666;font-size:13px}
        .info-row span{color:#333;font-size:14px;word-break:break-word}
        .password-display{background:#f8f9fa;padding:15px;border-radius:8px;margin-top:15px;text-align:center;border:2px dashed #667eea}
        .password-display code{font-size:18px;font-weight:700;color:#667eea;letter-spacing:2px}
        .password-display small{display:block;color:#999;margin-top:8px;font-size:12px}
        .bulk-actions-bar{background:#f8f9fa;padding:15px;border-radius:8px;margin-bottom:20px;display:none;align-items:center;gap:15px;flex-wrap:wrap}
        .bulk-actions-bar.show{display:flex}
        .bulk-count{font-weight:600;color:#667eea}
        .checkbox-cell{width:40px;text-align:center}
        .checkbox-cell input[type="checkbox"]{width:18px;height:18px;cursor:pointer}
        .dropdown{position:relative;display:inline-block}
        .dropdown-btn{background:white;border:2px solid #667eea;color:#667eea;padding:8px 16px;border-radius:6px;font-weight:600;cursor:pointer;font-size:13px;display:flex;align-items:center;gap:8px}
        .dropdown-btn:hover{background:#667eea;color:white}
        .dropdown-menu{display:none;position:absolute;top:100%;left:0;margin-top:5px;background:white;border:2px solid #e0e0e0;border-radius:6px;box-shadow:0 4px 12px rgba(0,0,0,.1);min-width:200px;z-index:1000}
        .dropdown.active .dropdown-menu{display:block}
        .dropdown-item{padding:10px 15px;cursor:pointer;transition:background .2s;color:#333;text-decoration:none;display:block;border-bottom:1px solid #f0f0f0}
        .dropdown-item:last-child{border-bottom:none}
        .dropdown-item:hover{background:#f8f9fa}
        
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
            .modal-box{padding:20px;max-width:95%}
            .info-row{grid-template-columns:100px 1fr;gap:8px;font-size:13px}
            .bulk-actions-bar{padding:12px}
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
            <div style="display:flex;gap:10px;flex-wrap:wrap">
                <span style="color:#666;font-size:14px"><?php echo $total; ?> total users</span>
                <a href="?export=csv" class="btn btn-success btn-sm">📥 Export CSV</a>
            </div>
        </div>

        <?php if ($flash): ?>
        <div class="alert alert-<?php echo $flash['type']; ?>"><?php echo htmlspecialchars($flash['message']); ?></div>
        <?php endif; ?>

        <?php if ($tempPassword): ?>
        <div class="alert alert-info">
            <strong>🔑 Password Reset Successful</strong>
            <div class="password-display">
                <div>New password for user ID <?php echo $tempPassword['user_id']; ?>:</div>
                <code><?php echo htmlspecialchars($tempPassword['password']); ?></code>
                <small>⚠️ Copy this password now - it won't be shown again!</small>
            </div>
        </div>
        <?php endif; ?>

        <div class="card">
            <div class="stats-row">
                <div class="mini-stat"><strong><?php echo $totalUsers; ?></strong><span>Total Users</span></div>
                <div class="mini-stat"><strong><?php echo $subCount; ?></strong><span>Subscribers</span></div>
                <div class="mini-stat"><strong><?php echo $activeCount; ?></strong><span>Active</span></div>
                <div class="mini-stat"><strong><?php echo $verifiedCount; ?></strong><span>Verified</span></div>
                <div class="mini-stat"><strong><?php echo $adminCount; ?></strong><span>Admins</span></div>
                <div class="mini-stat"><strong><?php echo $newUsersToday; ?></strong><span>New Today</span></div>
            </div>

            <form method="GET" class="toolbar">
                <input type="text" name="search" placeholder="Search name or email..." value="<?php echo htmlspecialchars($search); ?>">
                <select name="role" onchange="this.form.submit()">
                    <option value="">All Roles</option>
                    <option value="user"  <?php echo $roleFilter==='user'?'selected':''; ?>>Users</option>
                    <option value="admin" <?php echo $roleFilter==='admin'?'selected':''; ?>>Admins</option>
                </select>
                <select name="status" onchange="this.form.submit()">
                    <option value="">All Status</option>
                    <option value="active" <?php echo $statusFilter==='active'?'selected':''; ?>>Active</option>
                    <option value="inactive" <?php echo $statusFilter==='inactive'?'selected':''; ?>>Inactive</option>
                </select>
                <select name="verified" onchange="this.form.submit()">
                    <option value="">All Verified</option>
                    <option value="verified" <?php echo $verifiedFilter==='verified'?'selected':''; ?>>Verified</option>
                    <option value="unverified" <?php echo $verifiedFilter==='unverified'?'selected':''; ?>>Unverified</option>
                </select>
                <button type="submit" class="btn btn-outline">Search</button>
                <?php if ($search || $roleFilter || $statusFilter || $verifiedFilter): ?>
                    <a href="users.php" class="btn" style="background:#f0f0f0;color:#333">Clear</a>
                <?php endif; ?>
            </form>

            <form method="POST" id="bulkForm">
                <div class="bulk-actions-bar" id="bulkBar">
                    <span class="bulk-count"><span id="selectedCount">0</span> selected</span>
                    <select name="bulk_action" id="bulkAction">
                        <option value="">Choose Action...</option>
                        <option value="activate">Activate</option>
                        <option value="deactivate">Deactivate</option>
                        <option value="verify">Verify Email</option>
                        <option value="delete">Delete</option>
                    </select>
                    <button type="button" onclick="executeBulkAction()" class="btn btn-primary btn-sm">Apply</button>
                    <button type="button" onclick="clearSelection()" class="btn btn-sm" style="background:#f0f0f0;color:#333">Clear</button>
                </div>

                <?php if (empty($users)): ?>
                    <p style="text-align:center;padding:40px;color:#999">No users found.</p>
                <?php else: ?>
                <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th class="checkbox-cell">
                                <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)">
                            </th>
                            <th>User</th>
                            <th>Role</th>
                            <th>Plan</th>
                            <th>Status</th>
                            <th>Reads</th>
                            <th>Joined</th>
                            <th>Last Login</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td class="checkbox-cell">
                            <?php if ($user['id'] !== $_SESSION['user_id']): ?>
                            <input type="checkbox" name="selected_users[]" value="<?php echo $user['id']; ?>" class="user-checkbox" onchange="updateBulkBar()">
                            <?php endif; ?>
                        </td>
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
                            <?php else: ?>
                                <span class="badge badge-warning">✗ Unverified</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo number_format($user['reads']); ?></td>
                        <td><?php echo formatDate($user['created_at']); ?></td>
                        <td><?php echo $user['last_login'] ? formatDate($user['last_login']) : '<span style="color:#999">Never</span>'; ?></td>
                        <td>
                            <?php if ($user['id'] !== $_SESSION['user_id']): ?>
                            <div class="dropdown">
                                <button type="button" class="dropdown-btn" onclick="toggleDropdown(this)">
                                    ⚙️ Manage ▼
                                </button>
                                <div class="dropdown-menu">
                                    <a href="#" class="dropdown-item" onclick="viewUser(<?php echo $user['id']; ?>); return false;">👁️ View Details</a>
                                    <a href="#" class="dropdown-item" onclick="resetPassword(<?php echo $user['id']; ?>,'<?php echo addslashes($user['full_name']); ?>'); return false;">🔑 Reset Password</a>
                                    <?php if (!$user['email_verified']): ?>
                                    <a href="#" class="dropdown-item" onclick="verifyEmail(<?php echo $user['id']; ?>,'<?php echo addslashes($user['full_name']); ?>'); return false;">✓ Verify Email</a>
                                    <?php else: ?>
                                    <a href="#" class="dropdown-item" onclick="unverifyEmail(<?php echo $user['id']; ?>,'<?php echo addslashes($user['full_name']); ?>'); return false;">✗ Unverify Email</a>
                                    <?php endif; ?>
                                    <a href="#" class="dropdown-item" onclick="toggleStatus(<?php echo $user['id']; ?>,'<?php echo $user['is_active'] ? 'disable' : 'enable'; ?>'); return false;">
                                        <?php echo $user['is_active'] ? '⏸️ Disable' : '▶️ Enable'; ?>
                                    </a>
                                    <a href="#" class="dropdown-item" onclick="toggleRole(<?php echo $user['id']; ?>,'<?php echo $user['role']; ?>'); return false;">
                                        <?php echo $user['role']==='admin' ? '👤 Remove Admin' : '👑 Make Admin'; ?>
                                    </a>
                                    <a href="#" class="dropdown-item" style="color:#dc3545" onclick="confirmDelete(<?php echo $user['id']; ?>,'<?php echo addslashes($user['full_name']); ?>'); return false;">🗑️ Delete User</a>
                                </div>
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
                    <a href="?page=<?php echo $i?>&search=<?php echo urlencode($search)?>&role=<?php echo urlencode($roleFilter)?>&status=<?php echo urlencode($statusFilter)?>&verified=<?php echo urlencode($verifiedFilter)?>"
                       class="page-link <?php echo $i==$page?'active':''; ?>"><?php echo $i?></a>
                    <?php endfor; ?>
                </div>
                <?php endif; ?>
                <?php endif; ?>

                <input type="hidden" name="action" value="bulk_action">
                <input type="hidden" name="current_page" value="<?php echo $page; ?>">
                <input type="hidden" name="current_search" value="<?php echo htmlspecialchars($search); ?>">
                <input type="hidden" name="current_role" value="<?php echo htmlspecialchars($roleFilter); ?>">
                <input type="hidden" name="current_status" value="<?php echo htmlspecialchars($statusFilter); ?>">
                <input type="hidden" name="current_verified" value="<?php echo htmlspecialchars($verifiedFilter); ?>">
            </form>
        </div>
    </main>
</div>

<!-- User Details Modal -->
<div class="modal" id="userDetailsModal">
    <div class="modal-box">
        <h3>👤 User Details</h3>
        <div id="userDetailsContent" style="margin-top:20px">
            <div style="text-align:center;padding:20px;color:#999">Loading...</div>
        </div>
        <div class="btns">
            <button onclick="closeModal('userDetailsModal')" class="btn" style="background:#f0f0f0;color:#333">Close</button>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal" id="deleteModal">
    <div class="modal-box">
        <h3>🗑️ Delete User</h3>
        <p id="deleteMsg">Delete this user? All their data will be removed permanently.</p>
        <div class="btns">
            <button onclick="closeModal('deleteModal')" class="btn" style="background:#f0f0f0;color:#333">Cancel</button>
            <form method="POST" id="deleteForm">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="user_id" id="deleteId">
                <input type="hidden" name="current_page" value="<?php echo $page; ?>">
                <input type="hidden" name="current_search" value="<?php echo htmlspecialchars($search); ?>">
                <input type="hidden" name="current_role" value="<?php echo htmlspecialchars($roleFilter); ?>">
                <input type="hidden" name="current_status" value="<?php echo htmlspecialchars($statusFilter); ?>">
                <input type="hidden" name="current_verified" value="<?php echo htmlspecialchars($verifiedFilter); ?>">
                <button type="submit" class="btn btn-danger">Yes, Delete</button>
            </form>
        </div>
    </div>
</div>

<!-- Generic Action Modal -->
<div class="modal" id="actionModal">
    <div class="modal-box">
        <h3 id="actionTitle">Confirm Action</h3>
        <p id="actionMsg">Are you sure?</p>
        <div class="btns">
            <button onclick="closeModal('actionModal')" class="btn" style="background:#f0f0f0;color:#333">Cancel</button>
            <form method="POST" id="actionForm">
                <input type="hidden" name="action" id="actionType">
                <input type="hidden" name="user_id" id="actionUserId">
                <input type="hidden" name="current_page" value="<?php echo $page; ?>">
                <input type="hidden" name="current_search" value="<?php echo htmlspecialchars($search); ?>">
                <input type="hidden" name="current_role" value="<?php echo htmlspecialchars($roleFilter); ?>">
                <input type="hidden" name="current_status" value="<?php echo htmlspecialchars($statusFilter); ?>">
                <input type="hidden" name="current_verified" value="<?php echo htmlspecialchars($verifiedFilter); ?>">
                <button type="submit" class="btn btn-primary" id="actionSubmit">Confirm</button>
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

function closeModal(modalId) {
    document.getElementById(modalId).classList.remove('show');
}

function showModal(modalId) {
    document.getElementById(modalId).classList.add('show');
}

// Click outside modal to close
document.querySelectorAll('.modal').forEach(modal => {
    modal.addEventListener('click', function(e) {
        if (e.target === this) closeModal(this.id);
    });
});

// Dropdown toggle
function toggleDropdown(btn) {
    event.stopPropagation();
    const dropdown = btn.parentElement;
    const isActive = dropdown.classList.contains('active');
    
    // Close all dropdowns
    document.querySelectorAll('.dropdown').forEach(d => d.classList.remove('active'));
    
    // Toggle this one
    if (!isActive) dropdown.classList.add('active');
}

// Close dropdowns when clicking outside
document.addEventListener('click', () => {
    document.querySelectorAll('.dropdown').forEach(d => d.classList.remove('active'));
});

// Bulk selection
function toggleSelectAll(checkbox) {
    const checkboxes = document.querySelectorAll('.user-checkbox');
    checkboxes.forEach(cb => cb.checked = checkbox.checked);
    updateBulkBar();
}

function updateBulkBar() {
    const checkboxes = document.querySelectorAll('.user-checkbox:checked');
    const count = checkboxes.length;
    const bulkBar = document.getElementById('bulkBar');
    const selectAll = document.getElementById('selectAll');
    
    document.getElementById('selectedCount').textContent = count;
    
    if (count > 0) {
        bulkBar.classList.add('show');
    } else {
        bulkBar.classList.remove('show');
        selectAll.checked = false;
    }
    
    // Update select all checkbox
    const totalCheckboxes = document.querySelectorAll('.user-checkbox').length;
    selectAll.checked = count === totalCheckboxes && count > 0;
}

function clearSelection() {
    document.querySelectorAll('.user-checkbox').forEach(cb => cb.checked = false);
    document.getElementById('selectAll').checked = false;
    updateBulkBar();
}

function executeBulkAction() {
    const action = document.getElementById('bulkAction').value;
    const count = document.querySelectorAll('.user-checkbox:checked').length;
    
    if (!action) {
        alert('Please select an action');
        return;
    }
    
    if (count === 0) {
        alert('Please select users');
        return;
    }
    
    const confirmMsg = `Apply "${action}" to ${count} selected user(s)?`;
    if (confirm(confirmMsg)) {
        document.getElementById('bulkForm').submit();
    }
}

// User actions
function viewUser(userId) {
    showModal('userDetailsModal');
    document.getElementById('userDetailsContent').innerHTML = '<div style="text-align:center;padding:20px;color:#999">Loading...</div>';
    
    // Fetch user details via AJAX (you'll need to create an endpoint)
    fetch('get_user_details.php?id=' + userId)
        .then(r => r.json())
        .then(data => {
            let html = '<div>';
            html += '<div class="info-row"><strong>Full Name:</strong><span>' + data.full_name + '</span></div>';
            html += '<div class="info-row"><strong>Email:</strong><span>' + data.email + '</span></div>';
            html += '<div class="info-row"><strong>Role:</strong><span>' + data.role + '</span></div>';
            html += '<div class="info-row"><strong>Status:</strong><span>' + (data.is_active ? 'Active' : 'Inactive') + '</span></div>';
            html += '<div class="info-row"><strong>Email Verified:</strong><span>' + (data.email_verified ? 'Yes' : 'No') + '</span></div>';
            html += '<div class="info-row"><strong>Plan:</strong><span>' + (data.plan || 'Free') + '</span></div>';
            html += '<div class="info-row"><strong>Articles Read:</strong><span>' + data.reads + '</span></div>';
            html += '<div class="info-row"><strong>Joined:</strong><span>' + data.created_at + '</span></div>';
            html += '<div class="info-row"><strong>Last Login:</strong><span>' + (data.last_login || 'Never') + '</span></div>';
            html += '</div>';
            document.getElementById('userDetailsContent').innerHTML = html;
        })
        .catch(() => {
            document.getElementById('userDetailsContent').innerHTML = '<div style="text-align:center;padding:20px;color:#dc3545">Failed to load user details</div>';
        });
}

function confirmDelete(id, name) {
    document.getElementById('deleteId').value = id;
    document.getElementById('deleteMsg').textContent = 'Delete "' + name + '"? This cannot be undone.';
    showModal('deleteModal');
}

function resetPassword(id, name) {
    document.getElementById('actionType').value = 'reset_password';
    document.getElementById('actionUserId').value = id;
    document.getElementById('actionTitle').textContent = '🔑 Reset Password';
    document.getElementById('actionMsg').textContent = 'Reset password for "' + name + '"? A new random password will be generated.';
    document.getElementById('actionSubmit').textContent = 'Reset Password';
    document.getElementById('actionSubmit').className = 'btn btn-warning';
    showModal('actionModal');
}

function verifyEmail(id, name) {
    document.getElementById('actionType').value = 'verify_email';
    document.getElementById('actionUserId').value = id;
    document.getElementById('actionTitle').textContent = '✓ Verify Email';
    document.getElementById('actionMsg').textContent = 'Manually verify email for "' + name + '"?';
    document.getElementById('actionSubmit').textContent = 'Verify';
    document.getElementById('actionSubmit').className = 'btn btn-success';
    showModal('actionModal');
}

function unverifyEmail(id, name) {
    document.getElementById('actionType').value = 'unverify_email';
    document.getElementById('actionUserId').value = id;
    document.getElementById('actionTitle').textContent = '✗ Unverify Email';
    document.getElementById('actionMsg').textContent = 'Remove email verification for "' + name + '"?';
    document.getElementById('actionSubmit').textContent = 'Unverify';
    document.getElementById('actionSubmit').className = 'btn btn-warning';
    showModal('actionModal');
}

function toggleStatus(id, action) {
    document.getElementById('actionType').value = 'toggle_active';
    document.getElementById('actionUserId').value = id;
    document.getElementById('actionTitle').textContent = action === 'enable' ? '▶️ Enable User' : '⏸️ Disable User';
    document.getElementById('actionMsg').textContent = action === 'enable' ? 'Enable this user account?' : 'Disable this user account?';
    document.getElementById('actionSubmit').textContent = action === 'enable' ? 'Enable' : 'Disable';
    document.getElementById('actionSubmit').className = action === 'enable' ? 'btn btn-success' : 'btn btn-warning';
    showModal('actionModal');
}

function toggleRole(id, currentRole) {
    document.getElementById('actionType').value = 'toggle_role';
    document.getElementById('actionUserId').value = id;
    const newRole = currentRole === 'admin' ? 'user' : 'admin';
    document.getElementById('actionTitle').textContent = newRole === 'admin' ? '👑 Make Admin' : '👤 Remove Admin';
    document.getElementById('actionMsg').textContent = newRole === 'admin' ? 'Grant admin privileges to this user?' : 'Remove admin privileges from this user?';
    document.getElementById('actionSubmit').textContent = newRole === 'admin' ? 'Make Admin' : 'Remove Admin';
    document.getElementById('actionSubmit').className = 'btn btn-info';
    showModal('actionModal');
}

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