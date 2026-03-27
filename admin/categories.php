<?php
require_once '../config.php';
requireAdmin();

$db = db();
$errors = [];

// Handle create / update / delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $name = sanitizeInput($_POST['name'] ?? '');
        $desc = sanitizeInput($_POST['description'] ?? '');
        $slug = sanitizeInput($_POST['slug'] ?? strtolower(str_replace(' ', '-', $name)));
        $slug = preg_replace('/[^a-z0-9\-]/', '', $slug);

        if (!$name) {
            $errors[] = 'Name is required.';
        } else {
            $check = $db->prepare("SELECT id FROM categories WHERE name = ? OR slug = ?");
            $check->execute([$name, $slug]);
            if ($check->fetch()) {
                $errors[] = 'Category name or slug already exists.';
            } else {
                $db->prepare("INSERT INTO categories (name, slug, description) VALUES (?,?,?)")
                   ->execute([$name, $slug, $desc]);
                setFlashMessage('Category created!', 'success');
                header('Location: categories.php');
                exit;
            }
        }
    } elseif ($action === 'update') {
        $id   = intval($_POST['cat_id'] ?? 0);
        $name = sanitizeInput($_POST['name'] ?? '');
        $desc = sanitizeInput($_POST['description'] ?? '');
        $slug = sanitizeInput($_POST['slug'] ?? '');
        $slug = preg_replace('/[^a-z0-9\-]/', '', strtolower($slug));

        if (!$name || !$id) {
            $errors[] = 'Name is required.';
        } else {
            $check = $db->prepare("SELECT id FROM categories WHERE (name = ? OR slug = ?) AND id != ?");
            $check->execute([$name, $slug, $id]);
            if ($check->fetch()) {
                $errors[] = 'Category name or slug already exists.';
            } else {
                $db->prepare("UPDATE categories SET name=?,slug=?,description=? WHERE id=?")
                   ->execute([$name, $slug, $desc, $id]);
                setFlashMessage('Category updated!', 'success');
                header('Location: categories.php');
                exit;
            }
        }
    } elseif ($action === 'delete') {
        $id = intval($_POST['cat_id'] ?? 0);
        if ($id) {
            // Unlink articles first
            $db->prepare("UPDATE articles SET category_id=NULL WHERE category_id=?")->execute([$id]);
            $db->prepare("DELETE FROM categories WHERE id=?")->execute([$id]);
            setFlashMessage('Category deleted.', 'success');
        }
        header('Location: categories.php');
        exit;
    }
}

$categories = $db->query("
    SELECT c.*, COUNT(a.id) as article_count
    FROM categories c
    LEFT JOIN articles a ON c.id = a.category_id AND a.is_published = 1
    GROUP BY c.id
    ORDER BY c.name
")->fetchAll();

$flash = getFlashMessage();
$editId = intval($_GET['edit'] ?? 0);
$editCat = null;
if ($editId) {
    $stmt = $db->prepare("SELECT * FROM categories WHERE id=?");
    $stmt->execute([$editId]);
    $editCat = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories - Admin | <?php echo SITE_NAME; ?></title>
    <link rel="icon" type="image/x-icon" href="https://png.pngtree.com/element_our/sm/20180518/sm_5aff60887f7d9.jpg">
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;background:#f5f7fa}
        .admin-layout{display:flex;min-height:100vh}
        .main-content{flex:1;margin-left:280px;padding:30px}
        .top-bar{background:white;padding:20px 30px;border-radius:12px;margin-bottom:30px;display:flex;justify-content:space-between;align-items:center;box-shadow:0 2px 8px rgba(0,0,0,.05)}
        .top-bar h1{font-size:24px}
        .grid-2{display:grid;grid-template-columns:1fr 380px;gap:25px;align-items:start}
        .card{background:white;padding:25px;border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,.05);margin-bottom:25px}
        .card h3{font-size:16px;font-weight:700;margin-bottom:20px;padding-bottom:12px;border-bottom:2px solid #f0f0f0}
        .form-group{margin-bottom:16px}
        label{display:block;font-weight:600;font-size:13px;margin-bottom:6px;color:#333;text-transform:uppercase;letter-spacing:.5px}
        input[type=text],textarea{width:100%;padding:10px 14px;border:2px solid #e0e0e0;border-radius:6px;font-size:14px;font-family:inherit}
        input[type=text]:focus,textarea:focus{outline:none;border-color:#667eea}
        .hint{font-size:12px;color:#999;margin-top:4px}
        .btn{padding:10px 20px;border:none;border-radius:6px;font-weight:600;cursor:pointer;text-decoration:none;display:inline-block;transition:all .2s;font-size:14px;font-family:inherit}
        .btn-primary{background:#667eea;color:white}
        .btn-primary:hover{background:#5568d3}
        .btn-danger{background:#dc3545;color:white}
        .btn-warning{background:#ffc107;color:#333}
        .btn-sm{padding:5px 12px;font-size:12px}
        .btn-block{width:100%;text-align:center;margin-bottom:8px}
        .alert{padding:15px 20px;border-radius:8px;margin-bottom:20px;font-weight:600}
        .alert-success{background:#d4edda;color:#155724;border-left:4px solid #28a745}
        .alert-danger{background:#f8d7da;color:#721c24;border-left:4px solid #dc3545}
        .alert-danger ul{margin:8px 0 0 20px}
        table{width:100%;border-collapse:collapse}
        th{text-align:left;padding:12px;background:#f8f9fa;font-weight:700;font-size:12px;text-transform:uppercase;letter-spacing:.5px;color:#666}
        td{padding:13px 12px;border-bottom:1px solid #f0f0f0;vertical-align:middle;font-size:14px}
        tr:last-child td{border-bottom:none}
        tr:hover td{background:#fafafa}
        .badge{display:inline-block;padding:3px 8px;border-radius:4px;font-size:11px;font-weight:700}
        .badge-info{background:#d1ecf1;color:#0c5460}
        .action-btns{display:flex;gap:6px}
        .cat-icon{width:36px;height:36px;border-radius:8px;background:linear-gradient(135deg,#667eea,#764ba2);display:inline-flex;align-items:center;justify-content:center;color:white;font-weight:700;font-size:14px}
        .form-card{border:2px solid #667eea}
        @media(max-width:900px){.grid-2{grid-template-columns:1fr}}
    </style>
</head>
<body>
<div class="admin-layout">
    <?php require_once 'sidebar.php'; ?>

    <main class="main-content">
        <div class="top-bar">
            <h1>🏷️ Categories</h1>
            <span style="color:#666;font-size:14px"><?php echo count($categories); ?> categories</span>
        </div>

        <?php if ($flash): ?>
        <div class="alert alert-<?php echo $flash['type']; ?>"><?php echo htmlspecialchars($flash['message']); ?></div>
        <?php endif; ?>

        <div class="grid-2">
            <!-- Left: list -->
            <div class="card">
                <h3>All Categories</h3>
                <?php if (empty($categories)): ?>
                    <p style="text-align:center;padding:30px;color:#999">No categories yet. Create one!</p>
                <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Slug</th>
                            <th>Articles</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($categories as $cat): ?>
                    <tr>
                        <td>
                            <div style="display:flex;align-items:center;gap:10px">
                                <div class="cat-icon"><?php echo strtoupper(substr($cat['name'],0,1)); ?></div>
                                <div>
                                    <strong><?php echo htmlspecialchars($cat['name']); ?></strong>
                                    <?php if ($cat['description']): ?>
                                        <br><small style="color:#999"><?php echo htmlspecialchars(substr($cat['description'],0,50)); ?>...</small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td><code style="font-size:12px;color:#667eea">/<?php echo htmlspecialchars($cat['slug']); ?></code></td>
                        <td><span class="badge badge-info"><?php echo $cat['article_count']; ?> articles</span></td>
                        <td>
                            <div class="action-btns">
                                <a href="?edit=<?php echo $cat['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                                <form method="POST" onsubmit="return confirm('Delete this category? Articles will be uncategorized.')">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="cat_id" value="<?php echo $cat['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>

            <!-- Right: form -->
            <div class="card form-card">
                <h3><?php echo $editCat ? '✏️ Edit Category' : '+ New Category'; ?></h3>

                <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul><?php foreach($errors as $e): ?><li><?php echo htmlspecialchars($e); ?></li><?php endforeach; ?></ul>
                </div>
                <?php endif; ?>

                <form method="POST" id="catForm">
                    <input type="hidden" name="action" value="<?php echo $editCat ? 'update' : 'create'; ?>">
                    <?php if ($editCat): ?>
                        <input type="hidden" name="cat_id" value="<?php echo $editCat['id']; ?>">
                    <?php endif; ?>

                    <div class="form-group">
                        <label>Name *</label>
                        <input type="text" name="name" id="catName" value="<?php echo htmlspecialchars($editCat['name'] ?? ''); ?>"
                               placeholder="e.g. Technology" required>
                    </div>
                    <div class="form-group">
                        <label>Slug *</label>
                        <input type="text" name="slug" id="catSlug" value="<?php echo htmlspecialchars($editCat['slug'] ?? ''); ?>"
                               placeholder="e.g. technology">
                        <div class="hint">Auto-generated. Used in URLs.</div>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" rows="3" placeholder="Short description..."><?php echo htmlspecialchars($editCat['description'] ?? ''); ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">
                        <?php echo $editCat ? '✓ Update Category' : '+ Create Category'; ?>
                    </button>
                    <?php if ($editCat): ?>
                        <a href="categories.php" class="btn btn-block" style="background:#f0f0f0;color:#333;text-align:center">Cancel</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </main>
</div>
<script>
const nameInput = document.getElementById('catName');
const slugInput = document.getElementById('catSlug');
let slugEdited = <?php echo $editCat ? 'true' : 'false'; ?>;
nameInput.addEventListener('input', function(){
    if (!slugEdited) {
        slugInput.value = this.value.toLowerCase().replace(/\s+/g,'-').replace(/[^a-z0-9\-]/g,'');
    }
});
slugInput.addEventListener('input', () => slugEdited = true);
</script>
</body>
</html>