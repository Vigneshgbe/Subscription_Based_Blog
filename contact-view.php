<?php
require_once '../config.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit;
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $messageId = (int)$_POST['message_id'];
    $newStatus = $_POST['status'];
    
    if (in_array($newStatus, ['unread', 'read', 'replied'])) {
        $stmt = $pdo->prepare("UPDATE contact_messages SET status = :status WHERE id = :id");
        $stmt->execute([':status' => $newStatus, ':id' => $messageId]);
        setFlashMessage('Message status updated successfully', 'success');
        header('Location: contact-messages.php');
        exit;
    }
}

// Handle message deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_message'])) {
    $messageId = (int)$_POST['message_id'];
    $stmt = $pdo->prepare("DELETE FROM contact_messages WHERE id = :id");
    $stmt->execute([':id' => $messageId]);
    setFlashMessage('Message deleted successfully', 'success');
    header('Location: contact-messages.php');
    exit;
}

// Get filter and search parameters
$statusFilter = $_GET['status'] ?? 'all';
$searchQuery = $_GET['search'] ?? '';

// Build query
$sql = "SELECT * FROM contact_messages WHERE 1=1";
$params = [];

if ($statusFilter !== 'all') {
    $sql .= " AND status = :status";
    $params[':status'] = $statusFilter;
}

if (!empty($searchQuery)) {
    $sql .= " AND (name LIKE :search OR email LIKE :search OR subject LIKE :search OR message LIKE :search)";
    $params[':search'] = "%$searchQuery%";
}

$sql .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$messages = $stmt->fetchAll();

// Get counts for different statuses
$unreadCount = $pdo->query("SELECT COUNT(*) FROM contact_messages WHERE status = 'unread'")->fetchColumn();
$readCount = $pdo->query("SELECT COUNT(*) FROM contact_messages WHERE status = 'read'")->fetchColumn();
$repliedCount = $pdo->query("SELECT COUNT(*) FROM contact_messages WHERE status = 'replied'")->fetchColumn();
$totalCount = $pdo->query("SELECT COUNT(*) FROM contact_messages")->fetchColumn();

$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Messages - Admin Dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Playfair+Display:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        :root {
            --primary: #0a0a0a;
            --secondary: #ffffff;
            --accent: #FF6B6B;
            --accent-dark: #E74C3C;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --text: #1a1a1a;
            --text-light: #6b7280;
            --text-lighter: #9ca3af;
            --border: #e5e7eb;
            --border-light: #f3f4f6;
            --bg-light: #fafafa;
            --bg-lighter: #f9fafb;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            line-height: 1.6;
            color: var(--text);
            background: var(--bg-lighter);
            -webkit-font-smoothing: antialiased;
        }

        .header {
            background: var(--secondary);
            border-bottom: 1px solid var(--border);
            padding: 20px 0;
            box-shadow: var(--shadow-sm);
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 24px;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 24px;
            font-weight: 800;
            color: var(--primary);
            text-decoration: none;
            font-family: 'Playfair Display', serif;
        }

        .nav {
            display: flex;
            gap: 24px;
            align-items: center;
        }

        .nav a {
            color: var(--text);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }

        .nav a:hover {
            color: var(--accent);
        }

        .main-content {
            padding: 40px 0;
        }

        .page-header {
            margin-bottom: 32px;
        }

        .page-title {
            font-size: 32px;
            font-weight: 800;
            margin-bottom: 8px;
            font-family: 'Playfair Display', serif;
        }

        .page-subtitle {
            color: var(--text-light);
            font-size: 16px;
        }

        /* Alert */
        .alert {
            padding: 16px 24px;
            margin-bottom: 24px;
            border-left: 4px solid;
            font-weight: 500;
            border-radius: 8px;
        }
        
        .alert-success {
            background: #d1fae5;
            border-color: var(--success);
            color: #065f46;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 24px;
            margin-bottom: 32px;
        }

        .stat-card {
            background: var(--secondary);
            padding: 24px;
            border-radius: 12px;
            border: 1px solid var(--border-light);
            box-shadow: var(--shadow-sm);
        }

        .stat-label {
            font-size: 14px;
            color: var(--text-light);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }

        .stat-value {
            font-size: 36px;
            font-weight: 800;
            color: var(--primary);
            font-family: 'Playfair Display', serif;
        }

        /* Filters */
        .filters-section {
            background: var(--secondary);
            padding: 24px;
            border-radius: 12px;
            border: 1px solid var(--border-light);
            margin-bottom: 24px;
            box-shadow: var(--shadow-sm);
        }

        .filters-row {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
            align-items: flex-end;
        }

        .filter-group {
            flex: 1;
            min-width: 200px;
        }

        .filter-label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--text);
        }

        .filter-select,
        .filter-input {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 15px;
            font-family: inherit;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s;
            border-radius: 8px;
            font-family: inherit;
        }

        .btn-primary {
            background: var(--accent);
            color: var(--secondary);
        }

        .btn-primary:hover {
            background: var(--accent-dark);
        }

        .btn-secondary {
            background: var(--border);
            color: var(--text);
        }

        .btn-secondary:hover {
            background: var(--border-light);
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 13px;
        }

        .btn-danger {
            background: var(--danger);
            color: var(--secondary);
        }

        /* Messages Table */
        .messages-table {
            background: var(--secondary);
            border-radius: 12px;
            border: 1px solid var(--border-light);
            box-shadow: var(--shadow-sm);
            overflow: hidden;
        }

        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: var(--bg-lighter);
            padding: 16px;
            text-align: left;
            font-weight: 700;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--text-light);
            border-bottom: 2px solid var(--border);
        }

        td {
            padding: 16px;
            border-bottom: 1px solid var(--border-light);
        }

        tr:hover {
            background: var(--bg-lighter);
        }

        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .status-unread {
            background: #fee2e2;
            color: #991b1b;
        }

        .status-read {
            background: #dbeafe;
            color: #1e40af;
        }

        .status-replied {
            background: #d1fae5;
            color: #065f46;
        }

        .message-preview {
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .actions {
            display: flex;
            gap: 8px;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: var(--secondary);
            padding: 32px;
            border-radius: 16px;
            max-width: 600px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
            box-shadow: var(--shadow-lg);
        }

        .modal-header {
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 1px solid var(--border);
        }

        .modal-title {
            font-size: 24px;
            font-weight: 700;
            font-family: 'Playfair Display', serif;
        }

        .modal-body {
            margin-bottom: 24px;
        }

        .detail-row {
            margin-bottom: 20px;
        }

        .detail-label {
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--text-light);
            margin-bottom: 8px;
        }

        .detail-value {
            font-size: 15px;
            color: var(--text);
            line-height: 1.6;
        }

        .modal-footer {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            padding-top: 16px;
            border-top: 1px solid var(--border);
        }

        /* Form in modal */
        .form-group {
            margin-bottom: 16px;
        }

        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        select.form-control {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 15px;
            font-family: inherit;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }

            .filters-row {
                flex-direction: column;
            }

            .filter-group {
                width: 100%;
            }

            .table-wrapper {
                font-size: 14px;
            }

            th, td {
                padding: 12px 8px;
            }

            .message-preview {
                max-width: 150px;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <a href="dashboard.php" class="logo"><?php echo SITE_NAME; ?> Admin</a>
                <nav class="nav">
                    <a href="dashboard.php">Dashboard</a>
                    <a href="contact-messages.php">Messages</a>
                    <a href="../index.php">View Site</a>
                    <a href="../logout.php">Logout</a>
                </nav>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <?php if ($flash): ?>
                <div class="alert alert-<?php echo $flash['type']; ?>">
                    <?php echo htmlspecialchars($flash['message']); ?>
                </div>
            <?php endif; ?>

            <div class="page-header">
                <h1 class="page-title">Contact Messages</h1>
                <p class="page-subtitle">Manage and respond to customer inquiries</p>
            </div>

            <!-- Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-label">Total Messages</div>
                    <div class="stat-value"><?php echo $totalCount; ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Unread</div>
                    <div class="stat-value" style="color: var(--danger);"><?php echo $unreadCount; ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Read</div>
                    <div class="stat-value" style="color: #3b82f6;"><?php echo $readCount; ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Replied</div>
                    <div class="stat-value" style="color: var(--success);"><?php echo $repliedCount; ?></div>
                </div>
            </div>

            <!-- Filters -->
            <div class="filters-section">
                <form method="GET" action="">
                    <div class="filters-row">
                        <div class="filter-group">
                            <label class="filter-label">Status Filter</label>
                            <select name="status" class="filter-select">
                                <option value="all" <?php echo $statusFilter === 'all' ? 'selected' : ''; ?>>All Messages</option>
                                <option value="unread" <?php echo $statusFilter === 'unread' ? 'selected' : ''; ?>>Unread</option>
                                <option value="read" <?php echo $statusFilter === 'read' ? 'selected' : ''; ?>>Read</option>
                                <option value="replied" <?php echo $statusFilter === 'replied' ? 'selected' : ''; ?>>Replied</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label class="filter-label">Search</label>
                            <input type="text" name="search" class="filter-input" placeholder="Search by name, email, subject..." value="<?php echo htmlspecialchars($searchQuery); ?>">
                        </div>
                        <div style="display: flex; gap: 8px;">
                            <button type="submit" class="btn btn-primary">Apply Filters</button>
                            <a href="contact-messages.php" class="btn btn-secondary">Clear</a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Messages Table -->
            <div class="messages-table">
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Subject</th>
                                <th>Message</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($messages)): ?>
                                <tr>
                                    <td colspan="7" style="text-align: center; padding: 40px; color: var(--text-light);">
                                        No messages found
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($messages as $message): ?>
                                    <tr>
                                        <td><?php echo date('M j, Y', strtotime($message['created_at'])); ?></td>
                                        <td><?php echo htmlspecialchars($message['name']); ?></td>
                                        <td><?php echo htmlspecialchars($message['email']); ?></td>
                                        <td><?php echo htmlspecialchars($message['subject']); ?></td>
                                        <td>
                                            <div class="message-preview">
                                                <?php echo htmlspecialchars(substr($message['message'], 0, 50)) . (strlen($message['message']) > 50 ? '...' : ''); ?>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="status-badge status-<?php echo $message['status']; ?>">
                                                <?php echo $message['status']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="actions">
                                                <button onclick="viewMessage(<?php echo htmlspecialchars(json_encode($message)); ?>)" class="btn btn-primary btn-sm">
                                                    View
                                                </button>
                                                <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this message?');">
                                                    <input type="hidden" name="message_id" value="<?php echo $message['id']; ?>">
                                                    <button type="submit" name="delete_message" class="btn btn-danger btn-sm">Delete</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <!-- View Message Modal -->
    <div id="messageModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Message Details</h2>
            </div>
            <div class="modal-body" id="messageDetails">
                <!-- Content will be inserted by JavaScript -->
            </div>
            <div class="modal-footer">
                <button onclick="closeModal()" class="btn btn-secondary">Close</button>
            </div>
        </div>
    </div>

    <script>
        function viewMessage(message) {
            const modal = document.getElementById('messageModal');
            const detailsContainer = document.getElementById('messageDetails');
            
            detailsContainer.innerHTML = `
                <div class="detail-row">
                    <div class="detail-label">Date</div>
                    <div class="detail-value">${new Date(message.created_at).toLocaleString()}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Name</div>
                    <div class="detail-value">${escapeHtml(message.name)}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Email</div>
                    <div class="detail-value"><a href="mailto:${escapeHtml(message.email)}" style="color: var(--accent);">${escapeHtml(message.email)}</a></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Subject</div>
                    <div class="detail-value">${escapeHtml(message.subject)}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Message</div>
                    <div class="detail-value" style="white-space: pre-wrap;">${escapeHtml(message.message)}</div>
                </div>
                <div class="detail-row">
                    <form method="POST">
                        <div class="form-group">
                            <label class="form-label">Update Status</label>
                            <select name="status" class="form-control">
                                <option value="unread" ${message.status === 'unread' ? 'selected' : ''}>Unread</option>
                                <option value="read" ${message.status === 'read' ? 'selected' : ''}>Read</option>
                                <option value="replied" ${message.status === 'replied' ? 'selected' : ''}>Replied</option>
                            </select>
                        </div>
                        <input type="hidden" name="message_id" value="${message.id}">
                        <button type="submit" name="update_status" class="btn btn-primary">Update Status</button>
                    </form>
                </div>
            `;
            
            modal.classList.add('active');
        }

        function closeModal() {
            document.getElementById('messageModal').classList.remove('active');
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Close modal when clicking outside
        document.getElementById('messageModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
    </script>
</body>
</html>