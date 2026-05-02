<?php
require_once '../config.php';
requireAdmin();

$db = db();

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $articleId = intval($_POST['article_id'] ?? 0);

    if ($action === 'delete' && $articleId) {
        $stmt = $db->prepare("DELETE FROM articles WHERE id = ?");
        $stmt->execute([$articleId]);
        flashMessage('success', 'Article deleted successfully.');
    }

    elseif ($action === 'toggle_publish' && $articleId) {
        $stmt = $db->prepare("SELECT is_published FROM articles WHERE id = ?");
        $stmt->execute([$articleId]);
        $article = $stmt->fetch();
        $newStatus = $article['is_published'] ? 0 : 1;
        $publishedAt = $newStatus ? date('Y-m-d H:i:s') : null;
        $stmt = $db->prepare("UPDATE articles SET is_published = ?, published_at = ? WHERE id = ?");
        $stmt->execute([$newStatus, $publishedAt, $articleId]);
        flashMessage('success', $newStatus ? 'Article published.' : 'Article unpublished.');
    } 
    
    elseif ($action === 'toggle_premium' && $articleId) {
        $stmt = $db->prepare("UPDATE articles SET is_premium = NOT is_premium WHERE id = ?");
        $stmt->execute([$articleId]);
        flashMessage('success', 'Premium status updated.');
    }

    header('Location: articles.php?' . http_build_query(array_filter([
        'page' => $_POST['current_page'] ?? 1,
        'search' => $_POST['current_search'] ?? '',
        'status' => $_POST['current_status'] ?? ''
    ])));
    exit;
}

// Filters
$page       = max(1, intval($_GET['page'] ?? 1));
$search     = sanitizeInput($_GET['search'] ?? '');
$statusFilter = $_GET['status'] ?? '';
$perPage    = 15;
$offset     = ($page - 1) * $perPage;

$where  = "WHERE 1=1";
$params = [];

if ($search) {
    $where .= " AND (a.title LIKE ? OR u.full_name LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($statusFilter === 'published') {
    $where .= " AND a.is_published = 1";
} elseif ($statusFilter === 'draft') {
    $where .= " AND a.is_published = 0";
} elseif ($statusFilter === 'premium') {
    $where .= " AND a.is_premium = 1";
}

$countStmt = $db->prepare("SELECT COUNT(*) as total FROM articles a LEFT JOIN users u ON a.author_id = u.id $where");
$countStmt->execute($params);
$total = $countStmt->fetch()['total'];
$totalPages = max(1, ceil($total / $perPage));

$params[] = $perPage;
$params[] = $offset;
$stmt = $db->prepare("
    SELECT a.*, u.full_name as author_name, c.name as category_name
    FROM articles a
    LEFT JOIN users u ON a.author_id = u.id
    LEFT JOIN categories c ON a.category_id = c.id
    $where
    ORDER BY a.created_at DESC
    LIMIT ? OFFSET ?
");
$stmt->execute($params);
$articles = $stmt->fetchAll();

$flash = getFlashMessage();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Articles - Admin | <?php echo SITE_NAME; ?></title>
    <link rel="icon" type="image/x-icon" href="https://raw.githubusercontent.com/Vigneshgbe/Subscription_Based_Blog/refs/heads/main/assets/Logo.png">
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;background:#f5f7fa}
        .admin-layout{display:flex;min-height:100vh}

        /* ── Main content ── */
        .main-content{flex:1;margin-left:280px;padding:30px;min-width:0}

        /* ── Top bar ── */
        .top-bar{background:white;padding:20px 30px;border-radius:12px;margin-bottom:30px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;box-shadow:0 2px 8px rgba(0,0,0,.05)}
        .top-bar h1{font-size:24px}

        /* ── Card ── */
        .card{background:white;padding:30px;border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,.05);margin-bottom:30px}

        /* ── Toolbar ── */
        .toolbar{display:flex;gap:15px;margin-bottom:25px;flex-wrap:wrap;align-items:center}
        .toolbar input,.toolbar select{padding:10px 15px;border:2px solid #e0e0e0;border-radius:6px;font-size:14px;font-family:inherit}
        .toolbar input:focus,.toolbar select:focus{outline:none;border-color:#667eea}
        .toolbar input{flex:1;min-width:200px}

        /* ── Buttons ── */
        .btn{padding:10px 20px;border:none;border-radius:6px;font-weight:600;cursor:pointer;text-decoration:none;display:inline-block;transition:all .2s;font-size:14px;font-family:inherit}
        .btn-primary{background:#667eea;color:white}
        .btn-primary:hover{background:#5568d3}
        .btn-danger{background:#dc3545;color:white}
        .btn-success{background:#28a745;color:white}
        .btn-warning{background:#ffc107;color:#333}
        .btn-sm{padding:5px 12px;font-size:12px}
        .btn-outline{background:transparent;border:2px solid #667eea;color:#667eea}
        .btn-outline:hover{background:#667eea;color:white}

        /* ── Table ── */
        .table-wrap{overflow-x:auto;-webkit-overflow-scrolling:touch}
        table{width:100%;border-collapse:collapse;min-width:700px}
        th{text-align:left;padding:12px;background:#f8f9fa;font-weight:700;font-size:12px;text-transform:uppercase;letter-spacing:.5px;color:#666}
        td{padding:13px 12px;border-bottom:1px solid #f0f0f0;vertical-align:middle}
        tr:last-child td{border-bottom:none}
        tr:hover td{background:#fafafa}

        /* ── Badges ── */
        .badge{display:inline-block;padding:3px 8px;border-radius:4px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px}
        .badge-success{background:#d4edda;color:#155724}
        .badge-warning{background:#fff3cd;color:#856404}
        .badge-info{background:#d1ecf1;color:#0c5460}
        .badge-secondary{background:#e2e3e5;color:#383d41}

        /* ── Action buttons ── */
        .action-btns{display:flex;gap:6px;flex-wrap:wrap}

        /* ── Alerts ── */
        .alert{padding:15px 20px;border-radius:8px;margin-bottom:20px;font-weight:600}
        .alert-success{background:#d4edda;color:#155724;border-left:4px solid #28a745}
        .alert-danger{background:#f8d7da;color:#721c24;border-left:4px solid #dc3545}

        /* ── Pagination ── */
        .pagination{display:flex;gap:8px;margin-top:20px;justify-content:flex-end;align-items:center;flex-wrap:wrap}
        .page-link{padding:8px 14px;border:2px solid #e0e0e0;border-radius:6px;text-decoration:none;color:#333;font-weight:600;font-size:13px;transition:all .2s}
        .page-link:hover,.page-link.active{background:#667eea;color:white;border-color:#667eea}

        /* ── Mini stats ── */
        .stats-row{display:flex;gap:15px;margin-bottom:25px;flex-wrap:wrap}
        .mini-stat{flex:1;min-width:80px;background:#f8f9fa;border-radius:8px;padding:15px;text-align:center}
        .mini-stat strong{display:block;font-size:24px;font-weight:900;color:#667eea}
        .mini-stat span{font-size:12px;color:#666;text-transform:uppercase;letter-spacing:.5px}

        /* ── Article title cell ── */
        .article-title-cell{max-width:300px}
        .article-title-cell strong{display:block;font-size:14px;line-height:1.4}
        .article-title-cell small{color:#999;font-size:12px}
        .truncate{white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:200px;display:block}

        /* ── Delete modal ── */
        .confirm-delete{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9999;align-items:center;justify-content:center}
        .confirm-box{background:white;border-radius:12px;padding:30px;max-width:400px;width:90%;text-align:center}
        .confirm-box h3{margin-bottom:10px;font-size:20px}
        .confirm-box p{color:#666;margin-bottom:25px}
        .confirm-box .btns{display:flex;gap:15px;justify-content:center;flex-wrap:wrap}

        /* ══ MOBILE (≤768px) ═════════════════════════════════════════ */
        @media(max-width:768px){
            .main-content{
                margin-left:0;
                padding:80px 16px 24px;
            }
            .top-bar{
                padding:16px;
                border-radius:10px;
                margin-bottom:20px;
            }
            .top-bar h1{font-size:18px}
            .card{padding:16px;border-radius:10px;margin-bottom:20px}
            .toolbar{gap:10px;margin-bottom:18px}
            .toolbar input,.toolbar select{padding:9px 12px;font-size:14px}
            .toolbar input{min-width:0}
            .stats-row{gap:10px}
            .mini-stat{padding:12px 8px}
            .mini-stat strong{font-size:20px}
            .mini-stat span{font-size:11px}
            .pagination{justify-content:center}
            .confirm-box{padding:20px}
        }

        /* ══ SMALL PHONES (≤480px) ═══════════════════════════════════ */
        @media(max-width:480px){
            .main-content{padding:76px 12px 20px}
            .top-bar{flex-direction:column;align-items:flex-start;gap:10px}
            .top-bar h1{font-size:17px}
            .top-bar .btn{width:100%;text-align:center}
            .toolbar{flex-direction:column}
            .toolbar input,.toolbar select,.toolbar .btn{width:100%}
            .stats-row{display:grid;grid-template-columns:repeat(2,1fr);gap:10px}
            .mini-stat{min-width:0}
            .action-btns .btn-sm{padding:6px 10px;font-size:11px}
        }
    </style>
</head>
<body>
<div class="admin-layout">
    <?php require_once 'sidebar.php'; ?>
    <main class="main-content">
        <div class="top-bar">
            <h1>📝 Articles</h1>
            <a href="article-create.php" class="btn btn-primary">+ New Article</a>
        </div>

        <?php if ($flash): ?>
        <div class="alert alert-<?php echo $flash['type']; ?>"><?php echo htmlspecialchars($flash['message']); ?></div>
        <?php endif; ?>

        <div class="card">
            <div class="stats-row">
                <?php
                $totalCount = $db->query("SELECT COUNT(*) FROM articles")->fetchColumn();
                $pubCount   = $db->query("SELECT COUNT(*) FROM articles WHERE is_published=1")->fetchColumn();
                $draftCount = $db->query("SELECT COUNT(*) FROM articles WHERE is_published=0")->fetchColumn();
                $premCount  = $db->query("SELECT COUNT(*) FROM articles WHERE is_premium=1")->fetchColumn();
                ?>
                <div class="mini-stat"><strong><?php echo $totalCount; ?></strong><span>Total</span></div>
                <div class="mini-stat"><strong><?php echo $pubCount; ?></strong><span>Published</span></div>
                <div class="mini-stat"><strong><?php echo $draftCount; ?></strong><span>Drafts</span></div>
                <div class="mini-stat"><strong><?php echo $premCount; ?></strong><span>Premium</span></div>
            </div>

            <form method="GET" class="toolbar">
                <input type="text" name="search" placeholder="Search title or author..." value="<?php echo htmlspecialchars($search); ?>">
                <select name="status" onchange="this.form.submit()">
                    <option value="">All Status</option>
                    <option value="published" <?php echo $statusFilter==='published'?'selected':''; ?>>Published</option>
                    <option value="draft"     <?php echo $statusFilter==='draft'?'selected':''; ?>>Draft</option>
                    <option value="premium"   <?php echo $statusFilter==='premium'?'selected':''; ?>>Premium</option>
                </select>
                <button type="submit" class="btn btn-outline">Search</button>
                <?php if ($search || $statusFilter): ?>
                    <a href="articles.php" class="btn btn-sm" style="background:#f0f0f0;color:#333">Clear</a>
                <?php endif; ?>
            </form>

            <?php if (empty($articles)): ?>
                <p style="text-align:center;padding:40px;color:#999;font-size:16px">No articles found.</p>
            <?php else: ?>
            <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Views</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($articles as $article): ?>
                <tr>
                    <td class="article-title-cell">
                        <strong class="truncate" title="<?php echo htmlspecialchars($article['title']); ?>"><?php echo htmlspecialchars($article['title']); ?></strong>
                        <small><?php echo htmlspecialchars($article['slug']); ?></small>
                    </td>
                    <td><?php echo htmlspecialchars($article['author_name']); ?></td>
                    <td><?php echo htmlspecialchars($article['category_name'] ?? '—'); ?></td>
                    <td>
                        <?php echo $article['is_published']
                            ? '<span class="badge badge-success">Published</span>'
                            : '<span class="badge badge-warning">Draft</span>'; ?>
                        <?php if ($article['is_premium']): ?>
                            <span class="badge badge-info">Premium</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo number_format($article['views']); ?></td>
                    <td><?php echo formatDate($article['created_at']); ?></td>
                    <td>
                        <div class="action-btns">
                            <a href="article-edit.php?id=<?php echo $article['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                            <form method="POST" style="display:inline">
                                <input type="hidden" name="action" value="toggle_publish">
                                <input type="hidden" name="article_id" value="<?php echo $article['id']; ?>">
                                <input type="hidden" name="current_page" value="<?php echo $page; ?>">
                                <input type="hidden" name="current_search" value="<?php echo htmlspecialchars($search); ?>">
                                <input type="hidden" name="current_status" value="<?php echo htmlspecialchars($statusFilter); ?>">
                                <button type="submit" class="btn btn-sm <?php echo $article['is_published']?'btn-warning':'btn-success'; ?>">
                                    <?php echo $article['is_published']?'Unpublish':'Publish'; ?>
                                </button>
                            </form>
                            <button class="btn btn-sm btn-danger" onclick="confirmDelete(<?php echo $article['id']; ?>, '<?php echo addslashes($article['title']); ?>')">Delete</button>
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

<!-- Delete Confirmation Modal -->
<div class="confirm-delete" id="deleteModal" style="display:none">
    <div class="confirm-box">
        <h3>🗑️ Delete Article</h3>
        <p id="deleteMsg">Are you sure you want to delete this article? This cannot be undone.</p>
        <div class="btns">
            <button onclick="closeDelete()" class="btn" style="background:#f0f0f0;color:#333">Cancel</button>
            <form method="POST" id="deleteForm">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="article_id" id="deleteId">
                <input type="hidden" name="current_page" value="<?php echo $page; ?>">
                <input type="hidden" name="current_search" value="<?php echo htmlspecialchars($search); ?>">
                <input type="hidden" name="current_status" value="<?php echo htmlspecialchars($statusFilter); ?>">
                <button type="submit" class="btn btn-danger">Yes, Delete</button>
            </form>
        </div>
    </div>
</div>

<script>
function confirmDelete(id, title) {
    document.getElementById('deleteId').value = id;
    document.getElementById('deleteMsg').textContent = 'Delete "' + title + '"? This cannot be undone.';
    document.getElementById('deleteModal').style.display = 'flex';
}
function closeDelete() {
    document.getElementById('deleteModal').style.display = 'none';
}
document.getElementById('deleteModal').addEventListener('click', function(e){
    if(e.target===this) closeDelete();
});
</script>
</body>
</html>