<?php
require_once '../config.php';
requireAdmin();

$db = db();
$errors = [];
$formData = [
    'title' => '',
    'slug' => '',
    'excerpt' => '',
    'content' => '',
    'featured_image' => '',
    'category_id' => 0,
    'is_premium' => 0,
    'is_published' => 0,
    'meta_title' => '',
    'meta_description' => '',
    'meta_keywords' => ''
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

    if ($formData['slug']) {
        $check = $db->prepare("SELECT id FROM articles WHERE slug = ?");
        $check->execute([$formData['slug']]);
        if ($check->fetch()) $errors[] = 'Slug already exists. Choose a different one.';
    }

    if (empty($errors)) {
        $publishedAt = $formData['is_published'] ? date('Y-m-d H:i:s') : null;
        
        $stmt = $db->prepare("
            INSERT INTO articles (title,slug,excerpt,content,featured_image,category_id,
                is_premium,is_published,meta_title,meta_description,meta_keywords,published_at,created_at)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,NOW())
        ");
        $stmt->execute([
            $formData['title'], $formData['slug'], $formData['excerpt'], $formData['content'],
            $formData['featured_image'], $formData['category_id'],
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
    <title>Add Article - Admin | <?php echo SITE_NAME; ?></title>
    
    <!-- TinyMCE CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.7.0/tinymce.min.js" referrerpolicy="origin"></script>
    
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
        
        /* TinyMCE Editor Styling */
        .editor-wrapper{border:2px solid #e0e0e0;border-radius:6px;overflow:hidden;transition:border-color .2s}
        .editor-wrapper:focus-within{border-color:#667eea}
        
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
        .alert-success{background:#d4edda;color:#155724;border-left:4px solid #28a745}
        .alert-danger{background:#f8d7da;color:#721c24;border-left:4px solid #dc3545}
        .alert-danger ul{margin:8px 0 0 20px}
        .hint{font-size:12px;color:#999;margin-top:4px}
        .char-count{font-size:11px;color:#999;text-align:right;margin-top:3px}
        
        /* Editor info badge */
        .editor-info{display:inline-flex;align-items:center;gap:8px;padding:8px 12px;background:#e8f4fd;border-radius:6px;font-size:12px;color:#0066cc;margin-top:8px}
        .editor-info i{font-style:normal;font-weight:700}
        
        /* Quick tips card */
        .quick-tips{background:#fff9e6;border-left:4px solid #ffc107;padding:15px;border-radius:6px;font-size:13px;margin-bottom:20px}
        .quick-tips h4{font-size:14px;font-weight:700;margin-bottom:10px;color:#856404}
        .quick-tips ul{margin-left:20px}
        .quick-tips li{margin-bottom:6px;color:#856404}
        
        @media(max-width:900px){.grid-2{grid-template-columns:1fr}}
    </style>
</head>
<body>
<div class="admin-layout">
    <?php require_once 'sidebar.php'; ?>
    <main class="main-content">
        <div class="top-bar">
            <h1>✨ Add New Article</h1>
            <a href="articles.php" class="btn btn-outline">← Back to Articles</a>
        </div>

        <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <strong>Please fix the following errors:</strong>
            <ul><?php foreach($errors as $e): ?><li><?php echo htmlspecialchars($e); ?></li><?php endforeach; ?></ul>
        </div>
        <?php endif; ?>

        <div class="quick-tips">
            <h4>💡 Quick Tips for Writing Great Articles</h4>
            <ul>
                <li><strong>Title:</strong> Make it compelling and descriptive (50-60 characters is ideal for SEO)</li>
                <li><strong>Slug:</strong> Keep it short, use hyphens instead of spaces (auto-generated from title if left empty)</li>
                <li><strong>Content:</strong> Use the rich text editor to format your article professionally</li>
                <li><strong>Images:</strong> Use high-quality featured images (recommended: 1200x630px)</li>
                <li><strong>SEO:</strong> Fill in meta tags to improve search engine visibility</li>
            </ul>
        </div>

        <form method="POST" id="articleForm">
        <div class="grid-2">
            <div>
                <div class="card">
                    <h3>Article Content</h3>
                    <div class="form-group">
                        <label>Title *</label>
                        <input type="text" name="title" id="title" value="<?php echo htmlspecialchars($formData['title']); ?>" 
                               required maxlength="500" placeholder="Enter a compelling article title...">
                        <div class="hint">This will be displayed as the main heading of your article.</div>
                    </div>
                    <div class="form-group">
                        <label>Slug (URL) *</label>
                        <input type="text" name="slug" id="slug" value="<?php echo htmlspecialchars($formData['slug']); ?>" 
                               pattern="[a-z0-9\-]+" required placeholder="article-url-slug">
                        <div class="hint">Lowercase letters, numbers, and hyphens only. Leave empty to auto-generate from title.</div>
                    </div>
                    <div class="form-group">
                        <label>Excerpt</label>
                        <textarea name="excerpt" rows="3" maxlength="500" 
                                  placeholder="Write a brief summary of your article..."><?php echo htmlspecialchars($formData['excerpt']); ?></textarea>
                        <div class="hint">Brief summary shown in article listings (max 500 characters).</div>
                    </div>
                    <div class="form-group">
                        <label>Content * (Rich Text Editor)</label>
                        <div class="editor-wrapper">
                            <textarea name="content" id="rich-text-editor"><?php echo htmlspecialchars($formData['content']); ?></textarea>
                        </div>
                        <div class="editor-info">
                            <i>ℹ️</i> Use the toolbar above to format your article with headings, bold, italic, lists, links, images, and more.
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Featured Image URL</label>
                        <input type="url" name="featured_image" value="<?php echo htmlspecialchars($formData['featured_image']); ?>"
                               placeholder="https://example.com/image.jpg">
                        <div class="hint">Main image displayed with the article (recommended: 1200x630px).</div>
                    </div>
                </div>

                <div class="card">
                    <h3>SEO Settings</h3>
                    <div class="form-group">
                        <label>Meta Title</label>
                        <input type="text" name="meta_title" id="meta_title" value="<?php echo htmlspecialchars($formData['meta_title']); ?>" 
                               maxlength="255" placeholder="SEO-optimized title for search engines">
                        <div class="char-count"><span id="mt_count">0</span>/255 characters</div>
                        <div class="hint">Title shown in search results (leave empty to use article title).</div>
                    </div>
                    <div class="form-group">
                        <label>Meta Description</label>
                        <textarea name="meta_description" id="meta_desc" rows="2" maxlength="500" 
                                  placeholder="Write a description that will appear in search results..."><?php echo htmlspecialchars($formData['meta_description']); ?></textarea>
                        <div class="char-count"><span id="md_count">0</span>/500 characters</div>
                        <div class="hint">Description shown in search results (leave empty to use excerpt). Aim for 150-160 characters.</div>
                    </div>
                    <div class="form-group">
                        <label>Meta Keywords</label>
                        <input type="text" name="meta_keywords" value="<?php echo htmlspecialchars($formData['meta_keywords']); ?>" 
                               placeholder="keyword1, keyword2, keyword3">
                        <div class="hint">Comma-separated keywords for SEO (e.g., "web development, javascript, tutorial").</div>
                    </div>
                </div>
            </div>

            <div>
                <div class="card">
                    <h3>Publish Settings</h3>
                    <div class="toggle-group">
                        <label class="toggle-item">
                            <input type="checkbox" name="is_published" value="1" <?php echo $formData['is_published']?'checked':''; ?>>
                            <div class="toggle-info">
                                <strong>Published</strong>
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
                    <button type="submit" class="btn btn-primary btn-block">✓ Create Article</button>
                    <a href="articles.php" class="btn btn-outline btn-block">Cancel</a>
                </div>

                <div class="card">
                    <h3>Category</h3>
                    <select name="category_id">
                        <option value="">— No Category —</option>
                        <?php foreach($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo $formData['category_id']==$cat['id']?'selected':''; ?>>
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="hint" style="margin-top:10px">Choose a category to help organize your content.</div>
                </div>

                <div class="card" style="background:#f8f9fa">
                    <h3>Writing Tips</h3>
                    <div style="font-size:13px;color:#666;line-height:1.6">
                        <p style="margin-bottom:10px"><strong>Structure:</strong> Use H2/H3 headings to organize content into scannable sections.</p>
                        <p style="margin-bottom:10px"><strong>Paragraphs:</strong> Keep paragraphs short (3-4 sentences) for better readability.</p>
                        <p style="margin-bottom:10px"><strong>Images:</strong> Break up text with relevant images every 300-400 words.</p>
                        <p><strong>Links:</strong> Link to related articles or external sources to add value.</p>
                    </div>
                </div>
            </div>
        </div>
        </form>
    </main>
</div>

<script>
// Character counters for SEO fields
const metaTitle = document.getElementById('meta_title');
const metaDesc  = document.getElementById('meta_desc');
const mtCount = document.getElementById('mt_count');
const mdCount = document.getElementById('md_count');

mtCount.textContent = metaTitle.value.length;
mdCount.textContent = metaDesc.value.length;

metaTitle.addEventListener('input', () => mtCount.textContent = metaTitle.value.length);
metaDesc.addEventListener('input',  () => mdCount.textContent = metaDesc.value.length);

// Auto-generate slug from title
const titleInput = document.getElementById('title');
const slugInput = document.getElementById('slug');

titleInput.addEventListener('input', function() {
    if (!slugInput.value || slugInput.dataset.autogenerated === 'true') {
        const slug = this.value
            .toLowerCase()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .replace(/^-|-$/g, '');
        slugInput.value = slug;
        slugInput.dataset.autogenerated = 'true';
    }
});

slugInput.addEventListener('input', function() {
    if (this.value) {
        this.dataset.autogenerated = 'false';
    }
});

// Initialize TinyMCE Rich Text Editor
tinymce.init({
    selector: '#rich-text-editor',
    height: 500,
    
    // Plugins for rich functionality
    plugins: [
        'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
        'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
        'insertdatetime', 'media', 'table', 'code', 'help', 'wordcount',
        'emoticons', 'codesample', 'hr', 'pagebreak', 'nonbreaking'
    ],
    
    // Comprehensive toolbar
    toolbar: 'undo redo | styles | fontsizeselect lineheight | ' +
             'bold italic underline strikethrough | forecolor backcolor | ' +
             'alignleft aligncenter alignright alignjustify | ' +
             'bullist numlist outdent indent | ' +
             'link image media table hr | ' +
             'charmap emoticons | removeformat | code fullscreen',
    
    // Toolbar mode
    toolbar_mode: 'sliding',
    
    // Menu bar
    menubar: 'file edit view insert format tools table help',
    
    // Font size options
    fontsize_formats: '8pt 9pt 10pt 11pt 12pt 14pt 16pt 18pt 24pt 30pt 36pt 48pt',
    
    // Line height options
    lineheight_formats: '1 1.1 1.2 1.3 1.4 1.5 1.6 1.8 2.0 2.5 3.0',
    
    // Style formats
    style_formats: [
        { title: 'Headings', items: [
            { title: 'Heading 1', format: 'h1' },
            { title: 'Heading 2', format: 'h2' },
            { title: 'Heading 3', format: 'h3' },
            { title: 'Heading 4', format: 'h4' },
            { title: 'Heading 5', format: 'h5' },
            { title: 'Heading 6', format: 'h6' }
        ]},
        { title: 'Inline', items: [
            { title: 'Bold', format: 'bold' },
            { title: 'Italic', format: 'italic' },
            { title: 'Underline', format: 'underline' },
            { title: 'Strikethrough', format: 'strikethrough' },
            { title: 'Code', format: 'code' }
        ]},
        { title: 'Blocks', items: [
            { title: 'Paragraph', format: 'p' },
            { title: 'Blockquote', format: 'blockquote' },
            { title: 'Div', format: 'div' },
            { title: 'Pre', format: 'pre' }
        ]},
        { title: 'Alignment', items: [
            { title: 'Left', format: 'alignleft' },
            { title: 'Center', format: 'aligncenter' },
            { title: 'Right', format: 'alignright' },
            { title: 'Justify', format: 'alignjustify' }
        ]}
    ],
    
    // Content style
    content_style: `
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            font-size: 16px;
            line-height: 1.6;
            color: #333;
            padding: 15px;
        }
        p { 
            margin: 0 0 1em 0;
            line-height: inherit;
        }
        h1, h2, h3, h4, h5, h6 {
            margin: 1.5em 0 0.5em 0;
            font-weight: 600;
            line-height: 1.3;
        }
        h1 { font-size: 2.5em; }
        h2 { font-size: 2em; }
        h3 { font-size: 1.75em; }
        h4 { font-size: 1.5em; }
        h5 { font-size: 1.25em; }
        h6 { font-size: 1em; }
        ul, ol {
            margin: 0 0 1em 0;
            padding-left: 2em;
        }
        li {
            margin-bottom: 0.5em;
        }
        blockquote {
            border-left: 4px solid #667eea;
            padding-left: 1em;
            margin: 1em 0;
            color: #666;
            font-style: italic;
        }
        code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
            font-size: 0.9em;
        }
        pre {
            background: #f4f4f4;
            padding: 1em;
            border-radius: 5px;
            overflow-x: auto;
        }
        img {
            max-width: 100%;
            height: auto;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            margin: 1em 0;
        }
        table td, table th {
            border: 1px solid #ddd;
            padding: 8px;
        }
        table th {
            background-color: #f4f4f4;
            font-weight: 600;
        }
        a {
            color: #667eea;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    `,
    
    // Image settings
    image_advtab: true,
    image_caption: true,
    image_title: true,
    
    // Link settings
    link_title: true,
    link_target_list: [
        { title: 'Same window', value: '' },
        { title: 'New window', value: '_blank' }
    ],
    
    // Additional settings
    branding: false,
    promotion: false,
    resize: true,
    elementpath: true,
    statusbar: true,
    
    // Custom line height button
    setup: function(editor) {
        // Add line height dropdown
        editor.ui.registry.addMenuButton('lineheight', {
            icon: 'line-height',
            tooltip: 'Line Height',
            fetch: function(callback) {
                const items = [
                    { type: 'menuitem', text: '1.0', onAction: () => applyLineHeight(editor, '1') },
                    { type: 'menuitem', text: '1.1', onAction: () => applyLineHeight(editor, '1.1') },
                    { type: 'menuitem', text: '1.2', onAction: () => applyLineHeight(editor, '1.2') },
                    { type: 'menuitem', text: '1.3', onAction: () => applyLineHeight(editor, '1.3') },
                    { type: 'menuitem', text: '1.4', onAction: () => applyLineHeight(editor, '1.4') },
                    { type: 'menuitem', text: '1.5', onAction: () => applyLineHeight(editor, '1.5') },
                    { type: 'menuitem', text: '1.6', onAction: () => applyLineHeight(editor, '1.6') },
                    { type: 'menuitem', text: '1.8', onAction: () => applyLineHeight(editor, '1.8') },
                    { type: 'menuitem', text: '2.0', onAction: () => applyLineHeight(editor, '2.0') },
                    { type: 'menuitem', text: '2.5', onAction: () => applyLineHeight(editor, '2.5') },
                    { type: 'menuitem', text: '3.0', onAction: () => applyLineHeight(editor, '3.0') }
                ];
                callback(items);
            }
        });
        
        // Function to apply line height
        function applyLineHeight(editor, value) {
            editor.formatter.apply('lineheight', { value: value });
            const node = editor.selection.getNode();
            editor.dom.setStyle(node, 'line-height', value);
        }
        
        // Set default content on init
        editor.on('init', function() {
            // Editor is ready
            console.log('TinyMCE editor initialized');
        });
    },
    
    // Valid elements (allow most HTML)
    extended_valid_elements: 'style,script[src|async|defer|type|charset]',
    
    // Paste settings
    paste_as_text: false,
    paste_enable_default_filters: false,
    
    // Content filtering
    verify_html: false,
    
    // Template settings
    template_cdate_format: '[Date Created (CDATE): %m/%d/%Y : %H:%M:%S]',
    template_mdate_format: '[Date Modified (MDATE): %m/%d/%Y : %H:%M:%S]',
    
    // Custom formats
    formats: {
        lineheight: { selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li', styles: { 'line-height': '%value' } }
    }
});

// Form validation before submit
document.getElementById('articleForm').addEventListener('submit', function(e) {
    // Update textarea with TinyMCE content
    tinymce.triggerSave();
    
    // Validate content
    const content = tinymce.get('rich-text-editor').getContent();
    if (!content || content.trim() === '') {
        e.preventDefault();
        alert('Please add content to your article.');
        return false;
    }
});

// Warn user before leaving with unsaved changes
let formChanged = false;
document.getElementById('articleForm').addEventListener('change', function() {
    formChanged = true;
});

// Also track TinyMCE changes
tinymce.activeEditor && tinymce.activeEditor.on('change', function() {
    formChanged = true;
});

window.addEventListener('beforeunload', function(e) {
    if (formChanged) {
        e.preventDefault();
        e.returnValue = '';
    }
});

// Clear flag on submit
document.getElementById('articleForm').addEventListener('submit', function() {
    formChanged = false;
});
</script>
</body>
</html>