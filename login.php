<?php
require_once 'config.php';

if (isLoggedIn()) {
    redirect('index.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken = $_POST['csrf_token'] ?? '';
    
    if (!verifyCSRFToken($csrfToken)) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);
        
        if (empty($email) || empty($password)) {
            $errors[] = 'Please enter both email and password';
        } else {
            $db = db();
            $stmt = $db->prepare("SELECT * FROM users WHERE email = ? AND is_active = 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password_hash'])) {
                // Regenerate session ID to prevent session fixation
                session_regenerate_id(true);
                
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_name'] = $user['full_name'];
                $_SESSION['user_role'] = $user['role'];
                
                // Update last login
                $stmt = $db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                $stmt->execute([$user['id']]);
                
                // Log activity
                logActivity($user['id'], 'user_login', 'user', $user['id']);
                
                flashMessage('success', 'Welcome back, ' . $user['full_name'] . '!');
                
                // Redirect based on role
                if ($user['role'] === 'admin') {
                    redirect('admin/dashboard.php');
                } else {
                    redirect('index.php');
                }
            } else {
                $errors[] = 'Invalid email or password';
                logActivity(null, 'failed_login', null, null, ['email' => $email]);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo SITE_NAME; ?></title>
    <meta name="description" content="Sign in to access premium content">
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
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, #0a0a0a 0%, #1a1a1a 50%, #2a2a2a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow: hidden;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 20% 50%, rgba(255, 107, 107, 0.15) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(255, 215, 0, 0.1) 0%, transparent 50%);
            pointer-events: none;
        }
        
        .auth-container {
            background: var(--secondary);
            max-width: 480px;
            width: 100%;
            padding: 48px;
            border-radius: 16px;
            box-shadow: var(--shadow-xl);
            position: relative;
            z-index: 1;
            border: 1px solid var(--border-light);
        }
        
        .logo-section {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .logo {
            font-size: 32px;
            font-weight: 900;
            letter-spacing: -0.5px;
            color: var(--primary);
            font-family: 'Playfair Display', serif;
            margin-bottom: 12px;
            display: block;
        }
        
        .logo-tagline {
            font-size: 14px;
            color: var(--text-light);
            font-weight: 500;
        }
        
        h1 {
            font-size: 32px;
            font-weight: 800;
            margin-bottom: 12px;
            text-align: center;
            color: var(--primary);
            font-family: 'Playfair Display', serif;
            letter-spacing: -0.5px;
        }
        
        .subtitle {
            text-align: center;
            color: var(--text-light);
            margin-bottom: 40px;
            font-size: 16px;
            font-weight: 500;
        }
        
        .form-group {
            margin-bottom: 24px;
        }
        
        label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
            color: var(--text);
            font-size: 14px;
            letter-spacing: 0.2px;
        }
        
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid var(--border);
            border-radius: 8px;
            font-size: 15px;
            transition: all 0.2s;
            font-family: inherit;
            background: var(--secondary);
            color: var(--text);
        }
        
        input:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(255, 107, 107, 0.1);
        }
        
        input::placeholder {
            color: var(--text-lighter);
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 24px;
        }
        
        input[type="checkbox"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
            border-radius: 4px;
            border: 2px solid var(--border);
        }
        
        .checkbox-label {
            font-size: 14px;
            font-weight: 500;
            color: var(--text);
            cursor: pointer;
        }
        
        .btn {
            width: 100%;
            padding: 16px;
            background: var(--accent);
            color: var(--secondary);
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            font-family: inherit;
            box-shadow: 0 2px 8px rgba(255, 107, 107, 0.3);
        }
        
        .btn:hover {
            background: var(--accent-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 107, 107, 0.4);
        }
        
        .btn:active {
            transform: translateY(0);
        }
        
        .alert {
            padding: 16px 20px;
            border-left: 4px solid;
            font-weight: 500;
            border-radius: 8px;
            margin-bottom: 24px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            font-size: 14px;
            line-height: 1.5;
        }
        
        .alert-danger {
            background: #fee2e2;
            border-color: #ef4444;
            color: #991b1b;
        }
        
        .alert-success {
            background: #d1fae5;
            border-color: #10b981;
            color: #065f46;
        }
        
        .alert-warning {
            background: #fef3c7;
            border-color: #f59e0b;
            color: #92400e;
        }
        
        .forgot-link {
            text-align: right;
            margin-top: -12px;
            margin-bottom: 24px;
        }
        
        .forgot-link a {
            color: var(--accent);
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: color 0.2s;
        }
        
        .forgot-link a:hover {
            color: var(--accent-dark);
            text-decoration: underline;
        }
        
        .divider {
            text-align: center;
            margin: 32px 0;
            position: relative;
        }
        
        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: var(--border-light);
        }
        
        .divider span {
            background: var(--secondary);
            padding: 0 16px;
            color: var(--text-lighter);
            font-size: 13px;
            font-weight: 600;
            position: relative;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .link {
            text-align: center;
            margin-top: 32px;
            padding-top: 24px;
            border-top: 1px solid var(--border-light);
            font-size: 15px;
            color: var(--text-light);
        }
        
        .link a {
            color: var(--accent);
            text-decoration: none;
            font-weight: 700;
            transition: color 0.2s;
        }
        
        .link a:hover {
            color: var(--accent-dark);
            text-decoration: underline;
        }
        
        .back-home {
            text-align: center;
            margin-bottom: 24px;
        }
        
        .back-home a {
            color: var(--text-light);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: color 0.2s;
        }
        
        .back-home a:hover {
            color: var(--text);
        }
        
        .back-home svg {
            width: 16px;
            height: 16px;
        }
        
        .features {
            margin-top: 32px;
            padding-top: 24px;
            border-top: 1px solid var(--border-light);
        }
        
        .feature-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        
        .feature-item {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            color: var(--text-light);
        }
        
        .feature-icon {
            width: 20px;
            height: 20px;
            background: var(--accent);
            color: var(--secondary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 700;
            flex-shrink: 0;
        }
        
        @media (max-width: 640px) {
            .auth-container {
                padding: 32px 24px;
            }
            
            h1 {
                font-size: 28px;
            }
            
            .logo {
                font-size: 28px;
            }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="back-home">
            <a href="index.php">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Home
            </a>
        </div>
        
        <div class="logo-section">
            <span class="logo"><?php echo SITE_NAME; ?></span>
            <p class="logo-tagline">Premium insights for curious minds</p>
        </div>
        
        <h1>Welcome Back</h1>
        <p class="subtitle">Sign in to continue your journey</p>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <span style="font-size: 20px;">⚠️</span>
                <div>
                    <?php foreach ($errors as $error): ?>
                        <?php echo htmlspecialchars($error); ?><br>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
        
        <?php
        $flash = getFlashMessage();
        if ($flash):
        ?>
            <div class="alert alert-<?php echo $flash['type']; ?>">
                <span style="font-size: 20px;">
                    <?php 
                    echo $flash['type'] === 'success' ? '✓' : 
                         ($flash['type'] === 'warning' ? '⚠️' : 'ℹ️'); 
                    ?>
                </span>
                <div><?php echo htmlspecialchars($flash['message']); ?></div>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" 
                       id="email"
                       name="email" 
                       required 
                       placeholder="you@example.com"
                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" 
                       autofocus>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" 
                       id="password"
                       name="password" 
                       required
                       placeholder="Enter your password">
            </div>
            
            <div class="forgot-link">
                <a href="forgot-password.php">Forgot password?</a>
            </div>
            
            <div class="checkbox-group">
                <input type="checkbox" name="remember" id="remember">
                <label for="remember" class="checkbox-label">Keep me signed in</label>
            </div>
            
            <button type="submit" class="btn">Sign In</button>
        </form>
        
        <div class="features">
            <div class="feature-list">
                <div class="feature-item">
                    <span class="feature-icon">✓</span>
                    <span>Access premium articles</span>
                </div>
                <div class="feature-item">
                    <span class="feature-icon">✓</span>
                    <span>Personalized reading experience</span>
                </div>
                <div class="feature-item">
                    <span class="feature-icon">✓</span>
                    <span>Track your reading history</span>
                </div>
            </div>
        </div>
        
        <div class="link">
            Don't have an account? <a href="register.php">Create one now</a>
        </div>
    </div>
</body>
</html>