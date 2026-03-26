<?php
/**
 * sidebar.php — Admin Navigation Sidebar
 * Include this file in any admin page:
 *   <?php require_once 'sidebar.php'; ?>
 *
 * Requires: $_SESSION['user_name'] to be set (via config.php / requireAdmin())
 * The $currentPage variable is auto-detected from the current script filename,
 * so the active link highlights automatically on every page.
 */

// Auto-detect active page from current script filename
$_sidebar_page = basename($_SERVER['PHP_SELF']);
?>
<style>
    /* ─── Sidebar Reset & Base ─────────────────────────────────────── */
    .sidebar {
        width: 280px;
        background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
        color: white;
        padding: 0;
        position: fixed;
        height: 100vh;
        overflow-y: auto;
        z-index: 1000;
        transition: transform 0.3s ease;
        box-shadow: 4px 0 24px rgba(0, 0, 0, 0.15);
        display: flex;
        flex-direction: column;
    }

    /* ─── Brand / Logo Area ────────────────────────────────────────── */
    .sidebar-brand {
        padding: 28px 24px 24px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        background: rgba(255, 255, 255, 0.02);
        flex-shrink: 0;
    }

    .sidebar-brand h1 {
        font-size: 20px;
        font-weight: 800;
        margin-bottom: 4px;
        letter-spacing: -0.5px;
        color: #fff;
    }

    .sidebar-brand p {
        font-size: 12px;
        opacity: 0.5;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #fff;
    }

    /* ─── Navigation ───────────────────────────────────────────────── */
    .sidebar-menu {
        list-style: none;
        padding: 16px 12px;
        flex: 1;
        overflow-y: auto;
    }

    .sidebar-menu li {
        margin-bottom: 2px;
    }

    /* Section label */
    .sidebar-section-label {
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        color: rgba(255, 255, 255, 0.3);
        padding: 16px 16px 6px;
        display: block;
    }

    .sidebar-menu a {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 11px 16px;
        color: rgba(255, 255, 255, 0.65);
        text-decoration: none;
        font-weight: 600;
        font-size: 14px;
        border-radius: 10px;
        transition: all 0.2s ease;
        position: relative;
        margin-bottom: 2px;
    }

    .sidebar-menu a .nav-icon {
        font-size: 17px;
        width: 22px;
        text-align: center;
        flex-shrink: 0;
        transition: transform 0.2s ease;
    }

    .sidebar-menu a .nav-label {
        flex: 1;
    }

    /* Hover state */
    .sidebar-menu a:hover {
        background: rgba(255, 255, 255, 0.08);
        color: rgba(255, 255, 255, 0.95);
    }

    .sidebar-menu a:hover .nav-icon {
        transform: scale(1.15);
    }

    /* Active state */
    .sidebar-menu a.active {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: #fff;
        box-shadow: 0 4px 14px rgba(102, 126, 234, 0.45);
    }

    .sidebar-menu a.active .nav-icon {
        transform: scale(1.1);
    }

    /* Active pill indicator */
    .sidebar-menu a.active::before {
        content: '';
        position: absolute;
        left: -12px;
        top: 50%;
        transform: translateY(-50%);
        width: 4px;
        height: 20px;
        background: #a78bfa;
        border-radius: 0 4px 4px 0;
    }

    /* ─── Divider ──────────────────────────────────────────────────── */
    .sidebar-divider {
        height: 1px;
        background: rgba(255, 255, 255, 0.07);
        margin: 10px 16px;
    }

    /* ─── Footer / User strip ──────────────────────────────────────── */
    .sidebar-footer {
        padding: 16px;
        border-top: 1px solid rgba(255, 255, 255, 0.07);
        background: rgba(0, 0, 0, 0.15);
        flex-shrink: 0;
    }

    .sidebar-user {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 12px;
        background: rgba(255, 255, 255, 0.05);
        border-radius: 10px;
        border: 1px solid rgba(255, 255, 255, 0.07);
    }

    .sidebar-avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 15px;
        color: #fff;
        flex-shrink: 0;
        box-shadow: 0 2px 8px rgba(102, 126, 234, 0.4);
    }

    .sidebar-user-info {
        flex: 1;
        min-width: 0;
    }

    .sidebar-user-info strong {
        display: block;
        font-size: 13px;
        font-weight: 700;
        color: #fff;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .sidebar-user-info span {
        font-size: 11px;
        color: rgba(255, 255, 255, 0.45);
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* ─── Mobile Overlay ───────────────────────────────────────────── */
    .sidebar-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.55);
        z-index: 999;
        backdrop-filter: blur(2px);
    }

    .sidebar-overlay.active {
        display: block;
    }

    /* ─── Mobile Toggle Button ─────────────────────────────────────── */
    .mobile-header {
        display: none;
        background: white;
        padding: 14px 20px;
        position: sticky;
        top: 0;
        z-index: 998;
        align-items: center;
        justify-content: space-between;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.08);
    }

    .mobile-header .mobile-brand {
        font-size: 17px;
        font-weight: 800;
        color: #0f172a;
    }

    .menu-toggle {
        background: none;
        border: 1px solid #e2e8f0;
        font-size: 18px;
        cursor: pointer;
        padding: 8px 12px;
        border-radius: 8px;
        color: #1e293b;
        transition: background 0.2s;
    }

    .menu-toggle:hover {
        background: #f1f5f9;
    }

    /* ─── Responsive ───────────────────────────────────────────────── */
    @media (max-width: 768px) {
        .sidebar {
            transform: translateX(-100%);
        }

        .sidebar.active {
            transform: translateX(0);
        }

        .mobile-header {
            display: flex;
        }
    }
</style>

<!-- Mobile Header -->
<div class="mobile-header">
    <button class="menu-toggle" onclick="sidebarToggle()" aria-label="Toggle menu">☰</button>
    <span class="mobile-brand"><?php echo defined('SITE_NAME') ? htmlspecialchars(SITE_NAME) : 'Admin'; ?></span>
</div>

<!-- Overlay (mobile) -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="sidebarToggle()"></div>

<!-- Sidebar -->
<aside class="sidebar" id="adminSidebar" role="navigation" aria-label="Admin navigation">

    <!-- Brand -->
    <div class="sidebar-brand">
        <h1><?php echo defined('SITE_NAME') ? htmlspecialchars(SITE_NAME) : 'Premium Blog'; ?></h1>
        <p>Admin Dashboard</p>
    </div>

    <!-- Navigation Menu -->
    <ul class="sidebar-menu">

        <!-- Main section -->
        <span class="sidebar-section-label">Main</span>

        <li>
            <a href="dashboard.php"
               class="<?php echo $_sidebar_page === 'dashboard.php' ? 'active' : ''; ?>">
                <span class="nav-icon">📊</span>
                <span class="nav-label">Dashboard</span>
            </a>
        </li>

        <li>
            <a href="articles.php"
               class="<?php echo in_array($_sidebar_page, ['articles.php', 'article-create.php', 'article-edit.php']) ? 'active' : ''; ?>">
                <span class="nav-icon">📝</span>
                <span class="nav-label">Articles</span>
            </a>
        </li>

        <!-- Users & Billing section -->
        <span class="sidebar-section-label">Users &amp; Billing</span>

        <li>
            <a href="users.php"
               class="<?php echo $_sidebar_page === 'users.php' ? 'active' : ''; ?>">
                <span class="nav-icon">👥</span>
                <span class="nav-label">Users</span>
            </a>
        </li>

        <li>
            <a href="subscriptions.php"
               class="<?php echo $_sidebar_page === 'subscriptions.php' ? 'active' : ''; ?>">
                <span class="nav-icon">💳</span>
                <span class="nav-label">Subscriptions</span>
            </a>
        </li>

        <li>
            <a href="transactions.php"
               class="<?php echo $_sidebar_page === 'transactions.php' ? 'active' : ''; ?>">
                <span class="nav-icon">💰</span>
                <span class="nav-label">Transactions</span>
            </a>
        </li>

        <!-- Config section -->
        <span class="sidebar-section-label">Configuration</span>

        <li>
            <a href="categories.php"
               class="<?php echo $_sidebar_page === 'categories.php' ? 'active' : ''; ?>">
                <span class="nav-icon">🏷️</span>
                <span class="nav-label">Categories</span>
            </a>
        </li>

        <li>
            <a href="settings.php"
               class="<?php echo $_sidebar_page === 'settings.php' ? 'active' : ''; ?>">
                <span class="nav-icon">⚙️</span>
                <span class="nav-label">Settings</span>
            </a>
        </li>

        <div class="sidebar-divider"></div>

        <li>
            <a href="../index.php">
                <span class="nav-icon">🌐</span>
                <span class="nav-label">View Site</span>
            </a>
        </li>

        <li>
            <a href="../logout.php">
                <span class="nav-icon">🚪</span>
                <span class="nav-label">Logout</span>
            </a>
        </li>

    </ul>

    <!-- Footer user strip -->
    <div class="sidebar-footer">
        <div class="sidebar-user">
            <div class="sidebar-avatar">
                <?php echo strtoupper(substr($_SESSION['user_name'] ?? 'A', 0, 1)); ?>
            </div>
            <div class="sidebar-user-info">
                <strong><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Admin'); ?></strong>
                <span>Administrator</span>
            </div>
        </div>
    </div>

</aside>

<script>
    function sidebarToggle() {
        const sidebar = document.getElementById('adminSidebar');
        const overlay = document.getElementById('sidebarOverlay');
        sidebar.classList.toggle('active');
        overlay.classList.toggle('active');
    }

    // Auto-close sidebar on link click (mobile)
    document.querySelectorAll('#adminSidebar .sidebar-menu a').forEach(link => {
        link.addEventListener('click', () => {
            if (window.innerWidth <= 768) sidebarToggle();
        });
    });
</script>