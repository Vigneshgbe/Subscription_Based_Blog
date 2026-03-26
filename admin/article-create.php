<?php
require_once '../config.php';
requireAdmin();

$db = db();

$errors   = [];
$formData = [
    'title' => '', 'slug' => '', 'excerpt' => '', 'content' => '',
    'featured_image' => '', 'category_id' => '', 'is_premium' => 0,
    'is_published' => 0, 'meta_title' => '', 'meta_description' => '', 'meta_keywords' => ''
];

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

    // Check unique slug
    if ($formData['slug']) {
        $check = $db->prepare("SELECT id FROM articles WHERE slug = ?");
        $check->execute([$formData['slug']]);
        if ($check->fetch()) $errors[] = 'Slug already exists. Choose a different one.';
    }

    if (empty($errors)) {
        $publishedAt = $formData['is_published'] ? date('Y-m-d H:i:s') : null;
        $stmt = $db->prepare("
            INSERT INTO articles (title, slug, excerpt, content, featured_image, author_id, category_id,
                is_premium, is_published, meta_title, meta_description, meta_keywords, published_at)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)
        ");
        $stmt->execute([
            $formData['title'], $formData['slug'], $formData['excerpt'], $formData['content'],
            $formData['featured_image'], $_SESSION['user_id'], $formData['category_id'],
            $formData['is_premium'], $formData['is_published'],
            $formData['meta_title'], $formData['meta_description'], $formData['meta_keywords'],
            $publishedAt
        ]);
        
        flashMessage('success', 'Article created successfully!');
        header('Location: articles.php');
        exit;
    }
}

$categories = $db->query("SELECT * FROM categories ORDER BY name")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Article - Admin | <?php echo SITE_NAME; ?></title>
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;background:#f5f7fa}
        .admin-layout{display:flex;min-height:100vh}
        .main-content{flex:1;margin-left:280px;padding:30px}
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
        .btn-outline{background:transparent;border:2px solid #e0e0e0;color:#333}
        .btn-outline:hover{border-color:#667eea;color:#667eea}
        .btn-block{width:100%;text-align:center;margin-bottom:10px}
        .alert{padding:15px 20px;border-radius:8px;margin-bottom:20px;font-weight:600}
        .alert-danger{background:#f8d7da;color:#721c24;border-left:4px solid #dc3545}
        .alert-danger ul{margin:8px 0 0 20px}
        .hint{font-size:12px;color:#999;margin-top:4px}
        .char-count{font-size:11px;color:#999;text-align:right;margin-top:3px}
        .toolbar-btns{display:flex;gap:8px;margin-bottom:8px;flex-wrap:wrap}
        .toolbar-btns button{padding:6px 14px;border:1px solid #ddd;border-radius:4px;background:white;cursor:pointer;font-size:13px;font-weight:600;transition:all .2s}
        .toolbar-btns button:hover{background:#667eea;color:white;border-color:#667eea}
        .toolbar-btns button:active{transform:scale(0.95)}
        @media(max-width:900px){.grid-2{grid-template-columns:1fr}}
    </style>
</head>
<body>
<div class="admin-layout">
    <?php require_once 'sidebar.php'; ?>
    <main class="main-content">
        <div class="top-bar">
            <h1>✍️ New Article</h1>
            <a href="articles.php" class="btn btn-outline">← Back to Articles</a>
        </div>

        <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <strong>Please fix the following errors:</strong>
            <ul><?php foreach($errors as $e): ?><li><?php echo htmlspecialchars($e); ?></li><?php endforeach; ?></ul>
        </div>
        <?php endif; ?>

        <form method="POST" id="articleForm">
        <div class="grid-2">
            <!-- Left Column -->
            <div>
                <div class="card">
                    <h3>Article Content</h3>
                    <div class="form-group">
                        <label>Title *</label>
                        <input type="text" name="title" id="title" value="<?php echo htmlspecialchars($formData['title']); ?>"
                               placeholder="Enter article title..." maxlength="500" required>
                    </div>
                    <div class="form-group">
                        <label>Slug (URL) *</label>
                        <input type="text" name="slug" id="slug" value="<?php echo htmlspecialchars($formData['slug']); ?>"
                               placeholder="article-url-slug" pattern="[a-z0-9\-]+" required>
                        <div class="hint">Lowercase letters, numbers, and hyphens only. Auto-generated from title.</div>
                    </div>
                    <div class="form-group">
                        <label>Excerpt</label>
                        <textarea name="excerpt" id="excerpt" rows="3" maxlength="500" placeholder="Short summary (shown in article cards)..."><?php echo htmlspecialchars($formData['excerpt']); ?></textarea>
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
                        <textarea name="content" id="content" placeholder="Write your article content here... Use the toolbar buttons to add formatting." required><?php echo htmlspecialchars($formData['content']); ?></textarea>
                        <div class="hint">Tip: Write in paragraphs using &lt;p&gt; tags. Use toolbar buttons for formatting.</div>
                    </div>
                    <div class="form-group">
                        <label>Featured Image URL</label>
                        <input type="url" name="featured_image" value="<?php echo htmlspecialchars($formData['featured_image']); ?>"
                               placeholder="https://...">
                    </div>
                </div>

                <div class="card">
                    <h3>SEO Settings</h3>
                    <div class="form-group">
                        <label>Meta Title</label>
                        <input type="text" name="meta_title" id="meta_title" value="<?php echo htmlspecialchars($formData['meta_title']); ?>"
                               maxlength="255" placeholder="SEO title (leave blank to use article title)">
                        <div class="char-count"><span id="mt_count">0</span>/255</div>
                    </div>
                    <div class="form-group">
                        <label>Meta Description</label>
                        <textarea name="meta_description" id="meta_desc" rows="2" maxlength="500"
                                  placeholder="SEO description (150–160 chars recommended)..."><?php echo htmlspecialchars($formData['meta_description']); ?></textarea>
                        <div class="char-count"><span id="md_count">0</span>/500</div>
                    </div>
                    <div class="form-group">
                        <label>Meta Keywords</label>
                        <input type="text" name="meta_keywords" value="<?php echo htmlspecialchars($formData['meta_keywords']); ?>"
                               placeholder="keyword1, keyword2, keyword3">
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div>
                <div class="card">
                    <h3>Publish Settings</h3>
                    <div class="toggle-group">
                        <label class="toggle-item">
                            <input type="checkbox" name="is_published" value="1" <?php echo $formData['is_published']?'checked':''; ?>>
                            <div class="toggle-info">
                                <strong>Publish Now</strong>
                                <small>Make article visible on site</small>
                            </div>
                        </label>
                        <label class="toggle-item">
                            <input type="checkbox" name="is_premium" value="1" <?php echo $formData['is_premium']?'checked':''; ?>>
                            <div class="toggle-info">
                                <strong>★ Premium Content</strong>
                                <small>Requires paid subscription</small>
                            </div>
                        </label>
                    </div>
                    <br>
                    <button type="submit" name="publish" class="btn btn-primary btn-block">✓ Save Article</button>
                    <a href="articles.php" class="btn btn-outline btn-block">Cancel</a>
                </div>

                <div class="card">
                    <h3>Category</h3>
                    <div class="form-group">
                        <select name="category_id">
                            <option value="">— No Category —</option>
                            <?php foreach($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo $formData['category_id']==$cat['id']?'selected':''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <a href="categories.php" class="hint" style="color:#667eea">+ Manage Categories</a>
                </div>

                <div class="card">
                    <h3>Preview</h3>
                    <div id="preview-box" style="background:#f8f9fa;border-radius:8px;padding:15px;min-height:80px">
                        <div id="preview-title" style="font-weight:700;margin-bottom:6px;color:#333">Title will appear here</div>
                        <div id="preview-excerpt" style="font-size:13px;color:#666">Excerpt will appear here</div>
                    </div>
                </div>
            </div>
        </div>
        </form>
    </main>
</div>

<script>
// Auto-generate slug from title
const titleInput = document.getElementById('title');
const slugInput  = document.getElementById('slug');
const excerptInput = document.getElementById('excerpt');
let slugEdited = false;

titleInput.addEventListener('input', function(){
    document.getElementById('preview-title').textContent = this.value || 'Title will appear here';
    if (!slugEdited) {
        slugInput.value = this.value.toLowerCase()
            .replace(/[^a-z0-9\s-]/g,'')
            .replace(/\s+/g,'-')
            .replace(/-+/g,'-')
            .substring(0,100);
    }
});

slugInput.addEventListener('input', function(){ slugEdited = true; });

excerptInput.addEventListener('input', function(){
    document.getElementById('preview-excerpt').textContent = this.value || 'Excerpt will appear here';
});

// Char counters
const metaTitle = document.getElementById('meta_title');
const metaDesc  = document.getElementById('meta_desc');
const mtCount   = document.getElementById('mt_count');
const mdCount   = document.getElementById('md_count');

metaTitle.addEventListener('input', () => mtCount.textContent = metaTitle.value.length);
metaDesc.addEventListener('input',  () => mdCount.textContent = metaDesc.value.length);

mtCount.textContent = metaTitle.value.length;
mdCount.textContent = metaDesc.value.length;

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