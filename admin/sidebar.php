<?php
/**
 * sidebar.php — Admin Navigation Sidebar
 * Include this file in any admin page:
 *   <?php require_once 'sidebar.php'; ?>
 *
 * Requires: $_SESSION['user_name'] to be set (via config.php / requireAdmin())
 * Active link is auto-detected from the current script filename.
 */

$_sidebar_page = basename($_SERVER['PHP_SELF']);
?>
<style>
    /* ─── Sidebar ──────────────────────────────────────────────────── */
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
        box-shadow: 4px 0 24px rgba(0,0,0,0.1);
    }

    .sidebar-brand {
        padding: 32px 24px;
        border-bottom: 1px solid rgba(255,255,255,0.1);
        background: rgba(255,255,255,0.03);
    }

    .sidebar-brand h1 {
        font-size: 22px;
        font-weight: 800;
        margin-bottom: 4px;
        letter-spacing: -0.5px;
    }

    .sidebar-brand p {
        font-size: 13px;
        opacity: 0.6;
        font-weight: 500;
    }

    .sidebar-menu {
        list-style: none;
        padding: 16px 12px;
    }

    .sidebar-menu a {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 16px;
        color: rgba(255,255,255,0.7);
        text-decoration: none;
        font-weight: 600;
        font-size: 14px;
        border-radius: 8px;
        transition: all 0.2s;
        margin-bottom: 4px;
    }

    .sidebar-menu a:hover {
        background: rgba(255,255,255,0.1);
        color: white;
        transform: translateX(4px);
    }

    .sidebar-menu a.active {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    }

    .sidebar-menu a .icon {
        width: 20px;
        height: 20px;
        flex-shrink: 0;
        font-size: 16px;
        text-align: center;
    }

    /* ─── Mobile overlay ───────────────────────────────────────────── */
    .overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.5);
        z-index: 999;
    }

    .overlay.active {
        display: block;
    }

    /* ─── Mobile header ────────────────────────────────────────────── */
    .mobile-header {
        display: none;
        background: white;
        padding: 16px 20px;
        position: sticky;
        top: 0;
        z-index: 998;
        align-items: center;
        justify-content: space-between;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    .menu-toggle {
        background: none;
        border: none;
        font-size: 24px;
        cursor: pointer;
        padding: 8px;
        color: #1e293b;
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
    <button class="menu-toggle" onclick="toggleMenu()">☰</button>
    <h1><?php echo defined('SITE_NAME') ? htmlspecialchars(SITE_NAME) : 'Admin'; ?></h1>
</div>

<!-- Overlay -->
<div class="overlay" id="overlay" onclick="toggleMenu()"></div>

<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <h1><?php echo defined('SITE_NAME') ? htmlspecialchars(SITE_NAME) : 'Premium Blog'; ?></h1>
        <p>Admin Dashboard</p>
    </div>
    <ul class="sidebar-menu">
        <li>
            <a href="dashboard.php" class="<?php echo $_sidebar_page === 'dashboard.php' ? 'active' : ''; ?>">
                <span class="icon">📊</span> Dashboard
            </a>
        </li>
        <li>
            <a href="articles.php" class="<?php echo in_array($_sidebar_page, ['articles.php','article-create.php','article-edit.php']) ? 'active' : ''; ?>">
                <span class="icon">📝</span> Articles
            </a>
        </li>
        <li>
            <a href="users.php" class="<?php echo $_sidebar_page === 'users.php' ? 'active' : ''; ?>">
                <span class="icon">👥</span> Users
            </a>
        </li>
        <li>
            <a href="contact-us.php" class="<?php echo $_sidebar_page === 'contact-us.php' ? 'active' : ''; ?>">
                <span class="icon">📩</span> Messages
            </a>
        </li>
        <li>
            <a href="subscriptions.php" class="<?php echo $_sidebar_page === 'subscriptions.php' ? 'active' : ''; ?>">
                <span class="icon">💳</span> Subscriptions
            </a>
        </li>
        <li>
            <a href="transactions.php" class="<?php echo $_sidebar_page === 'transactions.php' ? 'active' : ''; ?>">
                <span class="icon">💰</span> Transactions
            </a>
        </li>
        <li>
            <a href="categories.php" class="<?php echo $_sidebar_page === 'categories.php' ? 'active' : ''; ?>">
                <span class="icon">🏷️</span> Categories
            </a>
        </li>
        <li>
            <a href="settings.php" class="<?php echo $_sidebar_page === 'settings.php' ? 'active' : ''; ?>">
                <span class="icon">⚙️</span> Settings
            </a>
        </li>
        <li>
            <a href="../index.php">
                <span class="icon">🌐</span> View Site
            </a>
        </li>
        <li>
            <a href="../logout.php">
                <span class="icon">🚪</span> Logout
            </a>
        </li>
    </ul>
</aside>

<script>
    function toggleMenu() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');
        sidebar.classList.toggle('active');
        overlay.classList.toggle('active');
    }

    // Auto-close sidebar on link click (mobile)
    document.querySelectorAll('.sidebar-menu a').forEach(link => {
        link.addEventListener('click', () => {
            if (window.innerWidth <= 768) toggleMenu();
        });
    });
</script>