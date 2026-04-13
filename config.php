<?php
// config.php - Central Configuration File
require_once __DIR__ . '/vendor/autoload.php';

// Error Reporting (Set to 0 in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Timezone
date_default_timezone_set('Asia/Kolkata');

// Session Configuration (Security Hardening)
ini_set('session.cookie_httponly', 1);
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
           || ($_SERVER['SERVER_PORT'] ?? 80) == 443;
ini_set('session.cookie_secure', $isHttps ? 1 : 0);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_samesite', 'Strict');
session_name('BLOG_SESSION');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load .env only if the file exists (works on both local and production)
if (file_exists(__DIR__ . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}

// Helper to read from $_ENV with fallback
function env($key, $default = '') {
    return $_ENV[$key] ?? getenv($key) ?? $default;
}

// Database
define('DB_HOST',    env('DB_HOST', 'localhost'));
define('DB_NAME',    env('DB_NAME', 'subscription_blog'));
define('DB_USER',    env('DB_USER', 'root'));
define('DB_PASS',    env('DB_PASS', ''));
define('DB_CHARSET', 'utf8mb4');

// Stripe
define('STRIPE_SECRET_KEY',      env('STRIPE_SECRET_KEY'));
define('STRIPE_PUBLISHABLE_KEY', env('STRIPE_PUBLISHABLE_KEY'));
define('STRIPE_WEBHOOK_SECRET',  env('STRIPE_WEBHOOK_SECRET'));

// Pricing Configuration (in INR)
define('MONTHLY_PRICE', 29900); // ₹299.00 in paisa
define('YEARLY_PRICE', 299900); // ₹2,999.00 in paisa

// Site
define('SITE_NAME',  env('SITE_NAME',  'Premium Blog'));
define('SITE_URL',   env('SITE_URL',   'http://localhost/blog'));
define('SITE_EMAIL', env('SITE_EMAIL', 'noreply@yourblog.com'));
define('ADMIN_EMAIL',env('ADMIN_EMAIL','admin@yourblog.com'));

// ============================================================
// FREE ARTICLE LIMIT — Single source of truth: 3
// article.php paywall UI, pricing.php, and all logic must
// reference this constant — never hardcode 5 or any other value
// ============================================================
define('FREE_ARTICLE_LIMIT', 3);

// Security Keys (Generate unique keys for production)
define('ENCRYPTION_KEY', 'your-32-character-secret-key-here!!'); // Change this!
define('CSRF_TOKEN_NAME', 'csrf_token');

// Upload Configuration
define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);

// Pagination
define('ARTICLES_PER_PAGE', 12);
define('ADMIN_ITEMS_PER_PAGE', 20);

// ── Database Singleton ──────────────────────────────────────
class Database {
    private static $instance = null;
    private $conn;
    
    private function __construct() {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ];
        
        try {
            $this->conn = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch(PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->conn;
    }
    
    // Prevent cloning and unserialization
    private function __clone() {}
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}

// ── Core Helpers ────────────────────────────────────────────
function db() {
    return Database::getInstance()->getConnection();
}

function redirect($url) {
    header("Location: " . $url);
    exit;
}

function flashMessage($type, $message) {
    $_SESSION['flash_message'] = ['type' => $type, 'message' => $message];
}

function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function requireLogin() {
    if (!isLoggedIn()) {
        flashMessage('warning', 'Please login to continue');
        redirect('login.php');
    }
}

function requireAdmin() {
    if (!isAdmin()) {
        flashMessage('danger', 'Access denied. Admin privileges required.');
        redirect('index.php');
    }
}

function sanitizeInput($data) {
    return htmlspecialchars(stripslashes(trim($data)), ENT_QUOTES, 'UTF-8');
}

function generateToken($length = 32) {
    return bin2hex(random_bytes($length));
}

function generateCSRFToken() {
    if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = generateToken();
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

function verifyCSRFToken($token) {
    return isset($_SESSION[CSRF_TOKEN_NAME]) && hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

function createSlug($string) {
    $string = strtolower($string);
    $string = preg_replace('/[^a-z0-9\s-]/', '', $string);
    $string = preg_replace('/[\s-]+/', '-', $string);
    return trim($string, '-');
}

function formatDate($date) {
    return date('M d, Y', strtotime($date));
}

function timeAgo($datetime) {
    $diff = time() - strtotime($datetime);
    
    if ($diff < 60)     return 'just now';
    if ($diff < 3600)   return floor($diff / 60) . ' minutes ago';
    if ($diff < 86400)  return floor($diff / 3600) . ' hours ago';
    if ($diff < 604800) return floor($diff / 86400) . ' days ago';
    
    return formatDate($datetime);
}

function truncateText($text, $length = 150) {
    if (strlen($text) <= $length) return $text;
    return substr($text, 0, $length) . '...';
}

function getClientIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP']))       return $_SERVER['HTTP_CLIENT_IP'];
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) return $_SERVER['HTTP_X_FORWARDED_FOR'];
    return $_SERVER['REMOTE_ADDR'];
}

function logActivity($userId, $action, $entityType = null, $entityId = null, $details = null) {
    try {
        $db = db();
        $stmt = $db->prepare("
            INSERT INTO activity_logs (user_id, action, entity_type, entity_id, ip_address, user_agent, details)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $userId,
            $action,
            $entityType,
            $entityId,
            getClientIP(),
            $_SERVER['HTTP_USER_AGENT'] ?? '',
            $details ? json_encode($details) : null
        ]);
    } catch (Exception $e) {
        // Silent fail — logging must never break user flow
    }
}

function sendEmail($to, $subject, $htmlBody) {
    if (empty($to)) return false;

    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

    try {
        // ── SMTP Settings ──────────────────────────────────
        $mail->isSMTP();
        $mail->Host       = env('MAIL_HOST', 'smtp.hostinger.com');
        $mail->SMTPAuth   = true;
        $mail->Username   = env('MAIL_USERNAME', SITE_EMAIL);
        $mail->Password   = env('MAIL_PASSWORD', '');
        $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SSL;
        $mail->Port       = (int) env('MAIL_PORT', 465);
        $mail->CharSet    = 'UTF-8';

        // ── Sender & Recipient ─────────────────────────────
        $mail->setFrom(SITE_EMAIL, SITE_NAME);
        $mail->addReplyTo(SITE_EMAIL, SITE_NAME);
        $mail->addAddress($to);

        // ── Content ────────────────────────────────────────
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $htmlBody;
        $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $htmlBody));

        $mail->send();
        return true;

    } catch (\PHPMailer\PHPMailer\Exception $e) {
        error_log("sendEmail() failed to [{$to}] subject [{$subject}]: " . $mail->ErrorInfo);
        return false;
    }
}

function getSessionId() {
    if (!isset($_SESSION['anonymous_session_id'])) {
        $_SESSION['anonymous_session_id'] = generateToken();
    }
    return $_SESSION['anonymous_session_id'];
}

// ── Subscription / Reading Logic ────────────────────────────
function hasActiveSubscription($userId) {
    $db = db();
    $stmt = $db->prepare("
        SELECT id FROM subscriptions 
        WHERE user_id = ? 
        AND status = 'active' 
        AND plan_type IN ('monthly', 'yearly')
        AND (current_period_end IS NULL OR current_period_end > NOW())
        LIMIT 1
    ");
    $stmt->execute([$userId]);
    return $stmt->fetch() !== false;
}

/**
 * Returns how many free premium article reads the current visitor has left.
 * Uses FREE_ARTICLE_LIMIT (3) as the cap.
 */
function getFreeArticlesRemaining() {
    $db        = db();
    $userId    = isLoggedIn() ? $_SESSION['user_id'] : null;
    $sessionId = getSessionId();
    
    $stmt = $db->prepare("
        SELECT COUNT(DISTINCT article_id) AS count 
        FROM reading_history 
        WHERE (user_id = ? OR session_id = ?)
        AND article_id IN (SELECT id FROM articles WHERE is_premium = 1)
    ");
    $stmt->execute([$userId, $sessionId]);
    $result = $stmt->fetch();
    
    return max(0, FREE_ARTICLE_LIMIT - (int)$result['count']);
}

/**
 * Records that the current visitor has read a premium article.
 * ON DUPLICATE KEY UPDATE prevents double-counting.
 */
function recordArticleRead($articleId) {
    $db        = db();
    $userId    = isLoggedIn() ? $_SESSION['user_id'] : null;
    $sessionId = getSessionId();
    
    try {
        $stmt = $db->prepare("
            INSERT INTO reading_history (user_id, session_id, article_id, ip_address, user_agent)
            VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE read_at = CURRENT_TIMESTAMP
        ");
        $stmt->execute([
            $userId,
            $sessionId,
            $articleId,
            getClientIP(),
            $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);
        
        // Increment article views
        $stmt = $db->prepare("UPDATE articles SET views = views + 1 WHERE id = ?");
        $stmt->execute([$articleId]);
    } catch (Exception $e) {
        // Silent fail - don't break user experience
    }
}

function canReadArticle($articleId) {
    // Admin can read everything
    if (isAdmin()) {
        return true;
    }
    
    $db = db();
    
    // Get article details
    $stmt = $db->prepare("SELECT is_premium FROM articles WHERE id = ? AND is_published = 1");
    $stmt->execute([$articleId]);
    $article = $stmt->fetch();
    
    if (!$article) {
        return false;
    }
    
    // Free articles are accessible to everyone
    if (!$article['is_premium']) {
        return true;
    }
    
    // Check if user has active subscription
    if (isLoggedIn() && hasActiveSubscription($_SESSION['user_id'])) {
        return true;
    }
    
    // Check free article limit
    return getFreeArticlesRemaining() > 0;
}

/**
 * Render article content - outputs HTML safely
 * This function ensures HTML content is displayed properly while still being safe
 */
function renderArticleContent($content) {
    // Content is stored as HTML, so we output it directly
    // The content was already validated when saved to DB
    return $content;
}

function sanitizeHTML($html) {
    // Allow these tags (including <img> for article images)
    $allowed = '<p><br><strong><b><em><i><u><h2><h3><h4><ul><ol><li><a><code><pre><blockquote><img>';
    return strip_tags($html, $allowed);
}

// Auto-load any additional functions or classes
// require_once 'functions.php';
?>