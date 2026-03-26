<?php
require_once '../config.php';
requireAdmin();

$db = db();
$id = intval($_GET['id'] ?? 0);

if (!$id) {
    header('Location: articles.php');
    exit;
}

$stmt = $db->prepare("SELECT * FROM articles WHERE id = ?");
$stmt->execute([$id]);
$article = $stmt->fetch();

if (!$article) {
    flashMessage('danger', 'Article not found');
    header('Location: articles.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData = [
        'title'            => sanitizeInput($_POST['title'] ?? ''),
        'slug'             => sanitizeInput($_POST['slug'] ?? ''),
        'excerpt'          => sanitizeInput($_POST['excerpt'] ?? ''),
        'content'          => $_POST['content'] ?? '', // Don't sanitize - we need HTML
        'featured_image'   => sanitizeInput($_POST['featured_image'] ?? ''),
        'category_id'      => intval($_POST['category_id'] ?? 0) ?: null,
        'is_premium'       => isset($_POST['is_premium']) ? 1 : 0,
        'is_published'     => isset($_POST['is_published']) ? 1 : 0,
        'meta_title'       => sanitizeInput($_POST['meta_title'] ?? ''),
        'meta_description' => sanitizeInput($_POST['meta_description'] ?? ''),
        'meta_keywords'    => sanitizeInput($_POST['meta_keywords'] ?? ''),
    ];

    if (!$formData['title']) $errors[] = 'Title is required.';
    if (!$formData['slug'])  $errors[] = 'Slug is required.';
    if (!$formData['content']) $errors[] = 'Content is required.';

    if ($formData['slug'] && $formData['slug'] !== $article['slug']) {
        $check = $db->prepare("SELECT id FROM articles WHERE slug = ? AND id != ?");
        $check->execute([$formData['slug'], $id]);
        if ($check->fetch()) $errors[] = 'Slug already exists. Choose a different one.';
    }

    if (empty($errors)) {
        $publishedAt = $article['published_at'];
        if ($formData['is_published'] && !$article['is_published']) {
            $publishedAt = date('Y-m-d H:i:s');
        } elseif (!$formData['is_published']) {
            $publishedAt = null;
        }
        
        $stmt = $db->prepare("
            UPDATE articles SET title=?,slug=?,excerpt=?,content=?,featured_image=?,category_id=?,
                is_premium=?,is_published=?,meta_title=?,meta_description=?,meta_keywords=?,published_at=?
            WHERE id=?
        ");
        $stmt->execute([
            $formData['title'], $formData['slug'], $formData['excerpt'], $formData['content'],
            $formData['featured_image'], $formData['category_id'],
            $formData['is_premium'], $formData['is_published'],
            $formData['meta_title'], $formData['meta_description'], $formData['meta_keywords'],
            $publishedAt, $id
        ]);
        
        flashMessage('success', 'Article updated successfully!');
        header('Location: articles.php');
        exit;
    }
    $article = array_merge($article, $formData);
}

$categories = $db->query("SELECT * FROM categories ORDER BY name")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Article - Admin | <?php echo SITE_NAME; ?></title>
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;background:#f5f7fa}
        .admin-layout{display:flex;min-height:100vh}
        .sidebar{width:260px;background:#1a1d29;color:white;padding:30px 0;position:fixed;height:100vh;overflow-y:auto}
        .sidebar-brand{padding:0 30px 30px;border-bottom:1px solid rgba(255,255,255,.1);margin-bottom:30px}
        .sidebar-brand h1{font-size:24px;font-weight:900}
        .sidebar-brand p{font-size:12px;opacity:.6;margin-top:5px}
        .sidebar-menu{list-style:none}
        .sidebar-menu a{display:block;padding:15px 30px;color:rgba(255,255,255,.8);text-decoration:none;font-weight:600;transition:all .2s}
        .sidebar-menu a:hover,.sidebar-menu a.active{background:rgba(255,255,255,.1);color:white}
        .main-content{flex:1;margin-left:260px;padding:30px}
        .top-bar{background:white;padding:20px 30px;border-radius:12px;margin-bottom:30px;display:flex;justify-content:space-between;align-items:center;box-shadow:0 2px 8px rgba(0,0,0,.05)}
        .top-bar h1{font-size:24px}
        .grid-2{display:grid;grid-template-columns:1fr 340px;gap:25px;align-items:start}
        .card{background:white;padding:25px;border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,.05);margin-bottom:25px}
        .card h3{font-size:16px;font-weight:700;margin-bottom:20px;padding-bottom:12px;border-bottom:2px solid #f0f0f0}
        .form-group{margin-bottom:18px}
        label{display:block;font-weight:600;font-size:13px;margin-bottom:6px;color:#333;text-transform:uppercase;letter-spacing:.5px}
        input[type=text],input[type=url],textarea,select{width:100%;padding:10px 14px;border:2px solid #e0e0e0;border-radius:6px;font-size:14px;font-family:inherit;transition:border-color .2s}
        input[type=text]:focus,input[type=url]:focus,textarea:focus,select:focus{outline:none;border-color:#667eea}
        textarea{resize:vertical;min-height:80px}
        #content{min-height:350px;font-family:inherit;font-size:15px;line-height:1.6}
        .toggle-group{display:flex;flex-direction:column;gap:12px}
        .toggle-item{display:flex;align-items:center;gap:12px;padding:12px;background:#f8f9fa;border-radius:8px;cursor:pointer}
        .toggle-item input{width:18px;height:18px;cursor:pointer;accent-color:#667eea}
        .toggle-item .toggle-info strong{display:block;font-size:14px;font-weight:600}
        .toggle-item .toggle-info small{color:#999;font-size:12px}
        .btn{padding:10px 20px;border:none;border-radius:6px;font-weight:600;cursor:pointer;text-decoration:none;display:inline-block;transition:all .2s;font-size:14px;font-family:inherit}
        .btn-primary{background:#667eea;color:white}
        .btn-primary:hover{background:#5568d3}
        .btn-danger{background:#dc3545;color:white}
        .btn-outline{background:transparent;border:2px solid #e0e0e0;color:#333}
        .btn-outline:hover{border-color:#667eea;color:#667eea}
        .btn-block{width:100%;text-align:center;margin-bottom:10px}
        .alert{padding:15px 20px;border-radius:8px;margin-bottom:20px;font-weight:600}
        .alert-success{background:#d4edda;color:#155724;border-left:4px solid #28a745}
        .alert-danger{background:#f8d7da;color:#721c24;border-left:4px solid #dc3545}
        .alert-danger ul{margin:8px 0 0 20px}
        .hint{font-size:12px;color:#999;margin-top:4px}
        .char-count{font-size:11px;color:#999;text-align:right;margin-top:3px}
        .toolbar-btns{display:flex;gap:8px;margin-bottom:8px;flex-wrap:wrap}
        .toolbar-btns button{padding:6px 14px;border:1px solid #ddd;border-radius:4px;background:white;cursor:pointer;font-size:13px;font-weight:600;transition:all .2s}
        .toolbar-btns button:hover{background:#667eea;color:white;border-color:#667eea}
        .toolbar-btns button:active{transform:scale(0.95)}
        .meta-info{background:#f8f9fa;border-radius:8px;padding:15px;font-size:13px;color:#666}
        .meta-info strong{color:#333}
        @media(max-width:900px){.grid-2{grid-template-columns:1fr}}
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
            <li><a href="dashboard.php">📊 Dashboard</a></li>
            <li><a href="articles.php" class="active">📝 Articles</a></li>
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
            <h1>✏️ Edit Article</h1>
            <div style="display:flex;gap:10px">
                <?php if ($article['is_published']): ?>
                <a href="../article.php?slug=<?php echo urlencode($article['slug']); ?>" target="_blank" class="btn btn-outline">View Live ↗</a>
                <?php endif; ?>
                <a href="articles.php" class="btn btn-outline">← Back</a>
            </div>
        </div>

        <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <strong>Please fix the following errors:</strong>
            <ul><?php foreach($errors as $e): ?><li><?php echo htmlspecialchars($e); ?></li><?php endforeach; ?></ul>
        </div>
        <?php endif; ?>

        <form method="POST" id="articleForm">
        <div class="grid-2">
            <div>
                <div class="card">
                    <h3>Article Content</h3>
                    <div class="form-group">
                        <label>Title *</label>
                        <input type="text" name="title" value="<?php echo htmlspecialchars($article['title']); ?>" required maxlength="500">
                    </div>
                    <div class="form-group">
                        <label>Slug (URL) *</label>
                        <input type="text" name="slug" value="<?php echo htmlspecialchars($article['slug']); ?>" pattern="[a-z0-9\-]+" required>
                        <div class="hint">Lowercase letters, numbers, and hyphens only.</div>
                    </div>
                    <div class="form-group">
                        <label>Excerpt</label>
                        <textarea name="excerpt" rows="3" maxlength="500"><?php echo htmlspecialchars($article['excerpt']); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Content * (HTML supported)</label>
                        <div class="toolbar-btns">
                            <button type="button" onclick="insertHTML('<strong>','</strong>')"><b>Bold</b></button>
                            <button type="button" onclick="insertHTML('<em>','</em>')"><i>Italic</i></button>
                            <button type="button" onclick="insertHTML('<h2>','</h2>')">H2</button>
                            <button type="button" onclick="insertHTML('<h3>','</h3>')">H3</button>
                            <button type="button" onclick="insertHTML('<code>','</code>')">Code</button>
                            <button type="button" onclick="insertList()">• List</button>
                            <button type="button" onclick="insertLink()">🔗 Link</button>
                            <button type="button" onclick="insertParagraph()">¶ Para</button>
                        </div>
                        <textarea name="content" id="content" required><?php echo htmlspecialchars($article['content']); ?></textarea>
                        <div class="hint">Tip: Write in paragraphs using &lt;p&gt; tags. Use toolbar buttons for formatting.</div>
                    </div>
                    <div class="form-group">
                        <label>Featured Image URL</label>
                        <input type="url" name="featured_image" value="<?php echo htmlspecialchars($article['featured_image'] ?? ''); ?>"
                               placeholder="https://...">
                    </div>
                </div>

                <div class="card">
                    <h3>SEO Settings</h3>
                    <div class="form-group">
                        <label>Meta Title</label>
                        <input type="text" name="meta_title" id="meta_title" value="<?php echo htmlspecialchars($article['meta_title'] ?? ''); ?>" maxlength="255">
                        <div class="char-count"><span id="mt_count">0</span>/255</div>
                    </div>
                    <div class="form-group">
                        <label>Meta Description</label>
                        <textarea name="meta_description" id="meta_desc" rows="2" maxlength="500"><?php echo htmlspecialchars($article['meta_description'] ?? ''); ?></textarea>
                        <div class="char-count"><span id="md_count">0</span>/500</div>
                    </div>
                    <div class="form-group">
                        <label>Meta Keywords</label>
                        <input type="text" name="meta_keywords" value="<?php echo htmlspecialchars($article['meta_keywords'] ?? ''); ?>" placeholder="keyword1, keyword2">
                    </div>
                </div>
            </div>

            <div>
                <div class="card">
                    <h3>Publish Settings</h3>
                    <div class="toggle-group">
                        <label class="toggle-item">
                            <input type="checkbox" name="is_published" value="1" <?php echo $article['is_published']?'checked':''; ?>>
                            <div class="toggle-info">
                                <strong>Published</strong>
                                <small>Visible on site</small>
                            </div>
                        </label>
                        <label class="toggle-item">
                            <input type="checkbox" name="is_premium" value="1" <?php echo $article['is_premium']?'checked':''; ?>>
                            <div class="toggle-info">
                                <strong>★ Premium Content</strong>
                                <small>Requires paid subscription</small>
                            </div>
                        </label>
                    </div>
                    <br>
                    <button type="submit" class="btn btn-primary btn-block">✓ Update Article</button>
                    <a href="articles.php" class="btn btn-outline btn-block">Cancel</a>
                </div>

                <div class="card">
                    <h3>Category</h3>
                    <select name="category_id">
                        <option value="">— No Category —</option>
                        <?php foreach($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo $article['category_id']==$cat['id']?'selected':''; ?>>
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="card">
                    <h3>Article Info</h3>
                    <div class="meta-info">
                        <p><strong>ID:</strong> #<?php echo $article['id']; ?></p>
                        <p style="margin-top:8px"><strong>Views:</strong> <?php echo number_format($article['views']); ?></p>
                        <p style="margin-top:8px"><strong>Created:</strong> <?php echo formatDate($article['created_at']); ?></p>
                        <?php if ($article['published_at']): ?>
                        <p style="margin-top:8px"><strong>Published:</strong> <?php echo formatDate($article['published_at']); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        </form>
    </main>
</div>
<script>
const metaTitle = document.getElementById('meta_title');
const metaDesc  = document.getElementById('meta_desc');
const mtCount = document.getElementById('mt_count');
const mdCount = document.getElementById('md_count');

mtCount.textContent = metaTitle.value.length;
mdCount.textContent = metaDesc.value.length;

metaTitle.addEventListener('input', () => mtCount.textContent = metaTitle.value.length);
metaDesc.addEventListener('input',  () => mdCount.textContent = metaDesc.value.length);

// FIXED TOOLBAR FUNCTIONS
function insertHTML(openTag, closeTag) {
    const ta = document.getElementById('content');
    const start = ta.selectionStart;
    const end = ta.selectionEnd;
    const selected = ta.value.substring(start, end);
    
    const newText = openTag + (selected || 'text here') + closeTag;
    ta.value = ta.value.substring(0, start) + newText + ta.value.substring(end);
    
    // Set cursor position
    const newCursorPos = selected ? end + openTag.length + closeTag.length : start + openTag.length;
    ta.focus();
    ta.setSelectionRange(newCursorPos, newCursorPos);
}

function insertList() {
    const ta = document.getElementById('content');
    const start = ta.selectionStart;
    const listHTML = '\n<ul>\n  <li>Item 1</li>\n  <li>Item 2</li>\n  <li>Item 3</li>\n</ul>\n';
    ta.value = ta.value.substring(0, start) + listHTML + ta.value.substring(start);
    ta.focus();
}

function insertLink() {
    const url = prompt('Enter URL:');
    if (!url) return;
    
    const ta = document.getElementById('content');
    const start = ta.selectionStart;
    const end = ta.selectionEnd;
    const text = ta.value.substring(start, end) || 'link text';
    
    const linkHTML = `<a href="${url}">${text}</a>`;
    ta.value = ta.value.substring(0, start) + linkHTML + ta.value.substring(end);
    ta.focus();
}

function insertParagraph() {
    const ta = document.getElementById('content');
    const start = ta.selectionStart;
    const end = ta.selectionEnd;
    const selected = ta.value.substring(start, end);
    
    const paraHTML = `<p>${selected || 'Your paragraph text here.'}</p>\n\n`;
    ta.value = ta.value.substring(0, start) + paraHTML + ta.value.substring(end);
    ta.focus();
}
</script>
</body>
</html>