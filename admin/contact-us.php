<?php
require_once '../config.php';
requireAdmin();

$db = db();

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $messageId = intval($_POST['message_id'] ?? 0);

    if ($action === 'delete' && $messageId) {
        $stmt = $db->prepare("DELETE FROM contact_messages WHERE id = ?");
        $stmt->execute([$messageId]);
        flashMessage('success', 'Message deleted successfully.');
    }

    elseif ($action === 'update_status' && $messageId) {
        $newStatus = $_POST['status'] ?? 'unread';
        if (in_array($newStatus, ['unread', 'read', 'replied'])) {
            $stmt = $db->prepare("UPDATE contact_messages SET status = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$newStatus, $messageId]);
            flashMessage('success', 'Message status updated to ' . ucfirst($newStatus) . '.');
        }
    }

    elseif ($action === 'bulk_delete' && !empty($_POST['message_ids'])) {
        $ids = array_map('intval', $_POST['message_ids']);
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $db->prepare("DELETE FROM contact_messages WHERE id IN ($placeholders)");
        $stmt->execute($ids);
        flashMessage('success', count($ids) . ' message(s) deleted successfully.');
    }

    elseif ($action === 'bulk_status' && !empty($_POST['message_ids'])) {
        $ids = array_map('intval', $_POST['message_ids']);
        $newStatus = $_POST['bulk_status'] ?? 'read';
        if (in_array($newStatus, ['unread', 'read', 'replied'])) {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $stmt = $db->prepare("UPDATE contact_messages SET status = ?, updated_at = NOW() WHERE id IN ($placeholders)");
            $stmt->execute(array_merge([$newStatus], $ids));
            flashMessage('success', count($ids) . ' message(s) marked as ' . ucfirst($newStatus) . '.');
        }
    }

    header('Location: admin-contacts.php?' . http_build_query(array_filter([
        'page' => $_POST['current_page'] ?? 1,
        'search' => $_POST['current_search'] ?? '',
        'status' => $_POST['current_status'] ?? ''
    ])));
    exit;
}

// Filters
$page = max(1, intval($_GET['page'] ?? 1));
$search = sanitizeInput($_GET['search'] ?? '');
$statusFilter = $_GET['status'] ?? '';
$perPage = 20;
$offset = ($page - 1) * $perPage;

$where = "WHERE 1=1";
$params = [];

if ($search) {
    $where .= " AND (name LIKE ? OR email LIKE ? OR subject LIKE ? OR message LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($statusFilter && in_array($statusFilter, ['unread', 'read', 'replied'])) {
    $where .= " AND status = ?";
    $params[] = $statusFilter;
}

$countStmt = $db->prepare("SELECT COUNT(*) as total FROM contact_messages $where");
$countStmt->execute($params);
$total = $countStmt->fetch()['total'];
$totalPages = max(1, ceil($total / $perPage));

$params[] = $perPage;
$params[] = $offset;
$stmt = $db->prepare("
    SELECT *
    FROM contact_messages
    $where
    ORDER BY created_at DESC
    LIMIT ? OFFSET ?
");
$stmt->execute($params);
$messages = $stmt->fetchAll();

// Get stats
$unreadCount = $db->query("SELECT COUNT(*) FROM contact_messages WHERE status='unread'")->fetchColumn();
$readCount = $db->query("SELECT COUNT(*) FROM contact_messages WHERE status='read'")->fetchColumn();
$repliedCount = $db->query("SELECT COUNT(*) FROM contact_messages WHERE status='replied'")->fetchColumn();
$totalCount = $db->query("SELECT COUNT(*) FROM contact_messages")->fetchColumn();

$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Messages - Admin | <?php echo SITE_NAME; ?></title>
    <link rel="icon" type="image/x-icon" href="https://raw.githubusercontent.com/Vigneshgbe/Subscription_Based_Blog/refs/heads/main/assets/Logo.png">
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;background:#f5f7fa}
        .admin-layout{display:flex;min-height:100vh}
        .main-content{flex:1;margin-left:280px;padding:30px}
        @media(max-width:768px){
            .main-content{margin-left:0;padding:15px}
        }
        .top-bar{background:white;padding:20px 30px;border-radius:12px;margin-bottom:30px;display:flex;justify-content:space-between;align-items:center;box-shadow:0 2px 8px rgba(0,0,0,.05);flex-wrap:wrap;gap:15px}
        .top-bar h1{font-size:24px}
        @media(max-width:768px){
            .top-bar{padding:15px}
            .top-bar h1{font-size:20px}
        }
        .card{background:white;padding:30px;border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,.05);margin-bottom:30px}
        @media(max-width:768px){
            .card{padding:15px}
        }
        .toolbar{display:flex;gap:15px;margin-bottom:25px;flex-wrap:wrap;align-items:center}
        .toolbar input,.toolbar select{padding:10px 15px;border:2px solid #e0e0e0;border-radius:6px;font-size:14px;font-family:inherit}
        .toolbar input:focus,.toolbar select:focus{outline:none;border-color:#667eea}
        .toolbar input{flex:1;min-width:200px}
        @media(max-width:768px){
            .toolbar input{min-width:100%;flex:none}
        }
        .btn{padding:10px 20px;border:none;border-radius:6px;font-weight:600;cursor:pointer;text-decoration:none;display:inline-block;transition:all .2s;font-size:14px;font-family:inherit}
        .btn-primary{background:#667eea;color:white}
        .btn-primary:hover{background:#5568d3}
        .btn-danger{background:#dc3545;color:white}
        .btn-danger:hover{background:#c82333}
        .btn-success{background:#28a745;color:white}
        .btn-success:hover{background:#218838}
        .btn-warning{background:#ffc107;color:#333}
        .btn-warning:hover{background:#e0a800}
        .btn-info{background:#17a2b8;color:white}
        .btn-info:hover{background:#138496}
        .btn-sm{padding:5px 12px;font-size:12px}
        .btn-outline{background:transparent;border:2px solid #667eea;color:#667eea}
        .btn-outline:hover{background:#667eea;color:white}
        table{width:100%;border-collapse:collapse}
        th{text-align:left;padding:12px;background:#f8f9fa;font-weight:700;font-size:12px;text-transform:uppercase;letter-spacing:.5px;color:#666}
        td{padding:13px 12px;border-bottom:1px solid #f0f0f0;vertical-align:top}
        tr:last-child td{border-bottom:none}
        tr:hover td{background:#fafafa}
        @media(max-width:1024px){
            table{display:block;overflow-x:auto;white-space:nowrap}
        }
        .badge{display:inline-block;padding:3px 8px;border-radius:4px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px}
        .badge-danger{background:#f8d7da;color:#721c24}
        .badge-success{background:#d4edda;color:#155724}
        .badge-info{background:#d1ecf1;color:#0c5460}
        .badge-warning{background:#fff3cd;color:#856404}
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
        .message-preview{max-width:300px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:#666;font-size:13px}
        .sender-info strong{display:block;font-size:14px;margin-bottom:3px}
        .sender-info small{color:#999;font-size:12px}
        .bulk-actions{background:#f8f9fa;padding:15px;border-radius:8px;margin-bottom:20px;display:none;align-items:center;gap:15px;flex-wrap:wrap}
        .bulk-actions.active{display:flex}
        .confirm-modal{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9999;align-items:center;justify-content:center}
        .confirm-box{background:white;border-radius:12px;padding:30px;max-width:500px;width:90%;max-height:90vh;overflow-y:auto}
        .confirm-box h3{margin-bottom:10px;font-size:20px}
        .confirm-box .message-details{background:#f8f9fa;padding:20px;border-radius:8px;margin:20px 0;text-align:left}
        .confirm-box .message-details p{margin-bottom:10px;line-height:1.6}
        .confirm-box .message-details strong{display:block;color:#333;margin-bottom:5px}
        .confirm-box .btns{display:flex;gap:15px;justify-content:center;flex-wrap:wrap}
        @media(max-width:768px){
            .confirm-box{padding:20px}
            .message-details{padding:15px}
        }
        .status-select{padding:5px 10px;border:2px solid #e0e0e0;border-radius:4px;font-size:12px;font-weight:600}
    </style>
</head>
<body>
<div class="admin-layout">
    <?php require_once 'sidebar.php'; ?>
    <main class="main-content">
        <div class="top-bar">
            <h1>📨 Contact Messages</h1>
            <div style="display:flex;gap:10px;flex-wrap:wrap">
                <button onclick="toggleBulkActions()" class="btn btn-outline" id="bulkToggle">Bulk Actions</button>
            </div>
        </div>

        <?php if ($flash): ?>
        <div class="alert alert-<?php echo $flash['type']; ?>"><?php echo htmlspecialchars($flash['message']); ?></div>
        <?php endif; ?>

        <div class="card">
            <div class="stats-row">
                <div class="mini-stat"><strong><?php echo $totalCount; ?></strong><span>Total</span></div>
                <div class="mini-stat"><strong><?php echo $unreadCount; ?></strong><span>Unread</span></div>
                <div class="mini-stat"><strong><?php echo $readCount; ?></strong><span>Read</span></div>
                <div class="mini-stat"><strong><?php echo $repliedCount; ?></strong><span>Replied</span></div>
            </div>

            <form method="GET" class="toolbar">
                <input type="text" name="search" placeholder="Search name, email, subject, or message..." value="<?php echo htmlspecialchars($search); ?>">
                <select name="status" onchange="this.form.submit()">
                    <option value="">All Status</option>
                    <option value="unread" <?php echo $statusFilter==='unread'?'selected':''; ?>>Unread</option>
                    <option value="read" <?php echo $statusFilter==='read'?'selected':''; ?>>Read</option>
                    <option value="replied" <?php echo $statusFilter==='replied'?'selected':''; ?>>Replied</option>
                </select>
                <button type="submit" class="btn btn-outline">Search</button>
                <?php if ($search || $statusFilter): ?>
                    <a href="admin-contacts.php" class="btn btn-sm" style="background:#f0f0f0;color:#333">Clear</a>
                <?php endif; ?>
            </form>

            <div class="bulk-actions" id="bulkActions">
                <span style="font-weight:600;color:#666">Selected: <span id="selectedCount">0</span></span>
                <form method="POST" style="display:flex;gap:10px;flex:1;flex-wrap:wrap" onsubmit="return validateBulk()">
                    <input type="hidden" name="action" value="bulk_status">
                    <input type="hidden" name="current_page" value="<?php echo $page; ?>">
                    <input type="hidden" name="current_search" value="<?php echo htmlspecialchars($search); ?>">
                    <input type="hidden" name="current_status" value="<?php echo htmlspecialchars($statusFilter); ?>">
                    <div id="bulkIdsStatus"></div>
                    <select name="bulk_status" class="status-select">
                        <option value="read">Mark as Read</option>
                        <option value="unread">Mark as Unread</option>
                        <option value="replied">Mark as Replied</option>
                    </select>
                    <button type="submit" class="btn btn-sm btn-info">Apply</button>
                </form>
                <button onclick="bulkDelete()" class="btn btn-sm btn-danger">Delete Selected</button>
            </div>

            <?php if (empty($messages)): ?>
                <p style="text-align:center;padding:40px;color:#999;font-size:16px">No messages found.</p>
            <?php else: ?>
            <div style="overflow-x:auto">
            <table>
                <thead>
                    <tr>
                        <th style="width:40px"><input type="checkbox" id="selectAll" onchange="toggleAll(this)"></th>
                        <th>Sender</th>
                        <th>Subject</th>
                        <th>Message</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($messages as $msg): ?>
                <tr data-id="<?php echo $msg['id']; ?>">
                    <td><input type="checkbox" class="msg-checkbox" value="<?php echo $msg['id']; ?>" onchange="updateBulkCount()"></td>
                    <td class="sender-info">
                        <strong><?php echo htmlspecialchars($msg['name']); ?></strong>
                        <small><?php echo htmlspecialchars($msg['email']); ?></small>
                    </td>
                    <td>
                        <strong><?php echo htmlspecialchars($msg['subject']); ?></strong>
                    </td>
                    <td>
                        <div class="message-preview" title="<?php echo htmlspecialchars($msg['message']); ?>">
                            <?php echo htmlspecialchars(mb_substr($msg['message'], 0, 100)); ?>
                            <?php if (mb_strlen($msg['message']) > 100): ?>...<?php endif; ?>
                        </div>
                    </td>
                    <td>
                        <form method="POST" style="display:inline" onsubmit="return confirm('Update status?')">
                            <input type="hidden" name="action" value="update_status">
                            <input type="hidden" name="message_id" value="<?php echo $msg['id']; ?>">
                            <input type="hidden" name="current_page" value="<?php echo $page; ?>">
                            <input type="hidden" name="current_search" value="<?php echo htmlspecialchars($search); ?>">
                            <input type="hidden" name="current_status" value="<?php echo htmlspecialchars($statusFilter); ?>">
                            <select name="status" class="status-select" onchange="this.form.submit()">
                                <option value="unread" <?php echo $msg['status']==='unread'?'selected':''; ?>>Unread</option>
                                <option value="read" <?php echo $msg['status']==='read'?'selected':''; ?>>Read</option>
                                <option value="replied" <?php echo $msg['status']==='replied'?'selected':''; ?>>Replied</option>
                            </select>
                        </form>
                    </td>
                    <td><?php echo formatDate($msg['created_at']); ?></td>
                    <td>
                        <div class="action-btns">
                            <button onclick="viewMessage(<?php echo $msg['id']; ?>)" class="btn btn-sm btn-primary">View</button>
                            <button onclick="confirmDelete(<?php echo $msg['id']; ?>, '<?php echo addslashes($msg['subject']); ?>')" class="btn btn-sm btn-danger">Delete</button>
                        </div>
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
                    <a href="?page=<?php echo $i?>&search=<?php echo urlencode($search)?>&status=<?php echo urlencode($statusFilter)?>"
                       class="page-link <?php echo $i==$page?'active':''; ?>"><?php echo $i?></a>
                <?php endfor; ?>
            </div>
            <?php endif; ?>
            <?php endif; ?>
        </div>
    </main>
</div>

<!-- View Message Modal -->
<div class="confirm-modal" id="viewModal">
    <div class="confirm-box">
        <h3>📧 Message Details</h3>
        <div class="message-details" id="messageContent"></div>
        <div class="btns">
            <button onclick="closeModal('viewModal')" class="btn btn-primary">Close</button>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="confirm-modal" id="deleteModal">
    <div class="confirm-box">
        <h3>🗑️ Delete Message</h3>
        <p id="deleteMsg">Are you sure you want to delete this message? This cannot be undone.</p>
        <div class="btns">
            <button onclick="closeModal('deleteModal')" class="btn" style="background:#f0f0f0;color:#333">Cancel</button>
            <form method="POST" id="deleteForm">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="message_id" id="deleteId">
                <input type="hidden" name="current_page" value="<?php echo $page; ?>">
                <input type="hidden" name="current_search" value="<?php echo htmlspecialchars($search); ?>">
                <input type="hidden" name="current_status" value="<?php echo htmlspecialchars($statusFilter); ?>">
                <button type="submit" class="btn btn-danger">Yes, Delete</button>
            </form>
        </div>
    </div>
</div>

<!-- Bulk Delete Form -->
<form method="POST" id="bulkDeleteForm" style="display:none">
    <input type="hidden" name="action" value="bulk_delete">
    <input type="hidden" name="current_page" value="<?php echo $page; ?>">
    <input type="hidden" name="current_search" value="<?php echo htmlspecialchars($search); ?>">
    <input type="hidden" name="current_status" value="<?php echo htmlspecialchars($statusFilter); ?>">
    <div id="bulkIdsDelete"></div>
</form>

<script>
const messagesData = <?php echo json_encode($messages); ?>;

function viewMessage(id) {
    const msg = messagesData.find(m => m.id == id);
    if (!msg) return;
    
    document.getElementById('messageContent').innerHTML = `
        <p><strong>From:</strong> ${escapeHtml(msg.name)}</p>
        <p><strong>Email:</strong> <a href="mailto:${escapeHtml(msg.email)}">${escapeHtml(msg.email)}</a></p>
        <p><strong>Subject:</strong> ${escapeHtml(msg.subject)}</p>
        <p><strong>Status:</strong> <span class="badge badge-${getStatusBadge(msg.status)}">${msg.status.toUpperCase()}</span></p>
        <p><strong>Received:</strong> ${msg.created_at}</p>
        <hr style="margin:15px 0;border:none;border-top:1px solid #e0e0e0">
        <p><strong>Message:</strong></p>
        <p style="white-space:pre-wrap;line-height:1.6;color:#333">${escapeHtml(msg.message)}</p>
    `;
    document.getElementById('viewModal').style.display = 'flex';
}

function confirmDelete(id, subject) {
    document.getElementById('deleteId').value = id;
    document.getElementById('deleteMsg').textContent = 'Delete message "' + subject + '"? This cannot be undone.';
    document.getElementById('deleteModal').style.display = 'flex';
}

function closeModal(id) {
    document.getElementById(id).style.display = 'none';
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function getStatusBadge(status) {
    return {unread:'danger',read:'info',replied:'success'}[status] || 'secondary';
}

document.querySelectorAll('.confirm-modal').forEach(modal => {
    modal.addEventListener('click', function(e) {
        if (e.target === this) closeModal(this.id);
    });
});

// Bulk actions
function toggleAll(checkbox) {
    document.querySelectorAll('.msg-checkbox').forEach(cb => {
        cb.checked = checkbox.checked;
    });
    updateBulkCount();
}

function updateBulkCount() {
    const checked = document.querySelectorAll('.msg-checkbox:checked');
    const count = checked.length;
    document.getElementById('selectedCount').textContent = count;
    document.getElementById('bulkActions').classList.toggle('active', count > 0);
    document.getElementById('selectAll').checked = count === document.querySelectorAll('.msg-checkbox').length;
}

function toggleBulkActions() {
    const bulkDiv = document.getElementById('bulkActions');
    const isActive = bulkDiv.classList.contains('active');
    
    if (isActive) {
        document.querySelectorAll('.msg-checkbox').forEach(cb => cb.checked = false);
        document.getElementById('selectAll').checked = false;
        updateBulkCount();
    } else {
        bulkDiv.classList.add('active');
    }
}

function validateBulk() {
    const checked = document.querySelectorAll('.msg-checkbox:checked');
    if (checked.length === 0) {
        alert('Please select at least one message.');
        return false;
    }
    
    const container = document.getElementById('bulkIdsStatus');
    container.innerHTML = '';
    checked.forEach(cb => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'message_ids[]';
        input.value = cb.value;
        container.appendChild(input);
    });
    return true;
}

function bulkDelete() {
    const checked = document.querySelectorAll('.msg-checkbox:checked');
    if (checked.length === 0) {
        alert('Please select at least one message to delete.');
        return;
    }
    
    if (!confirm(`Delete ${checked.length} message(s)? This cannot be undone.`)) {
        return;
    }
    
    const container = document.getElementById('bulkIdsDelete');
    container.innerHTML = '';
    checked.forEach(cb => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'message_ids[]';
        input.value = cb.value;
        container.appendChild(input);
    });
    
    document.getElementById('bulkDeleteForm').submit();
}
</script>
</body>
</html>