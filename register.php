<?php
require_once 'config.php';

if (isLoggedIn()) {
    redirect('index.php');
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken = $_POST['csrf_token'] ?? '';
    
    if (!verifyCSRFToken($csrfToken)) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        $fullName = sanitizeInput($_POST['full_name'] ?? '');
        $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        // Validation
        if (empty($fullName)) {
            $errors[] = 'Full name is required';
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Valid email is required';
        }
        
        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters';
        }
        
        if ($password !== $confirmPassword) {
            $errors[] = 'Passwords do not match';
        }
        
        // Check if email exists
        if (empty($errors)) {
            $db = db();
            $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $errors[] = 'Email already registered';
            }
        }
        
        // Register user
        if (empty($errors)) {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $verificationToken = generateToken();
            
            $stmt = $db->prepare("
                INSERT INTO users (email, password_hash, full_name, verification_token, email_verified)
                VALUES (?, ?, ?, ?, 0)
            ");
            
            try {
                $stmt->execute([$email, $passwordHash, $fullName, $verificationToken]);
                $userId = $db->lastInsertId();
                
                // Create free subscription
                $stmt = $db->prepare("
                    INSERT INTO subscriptions (user_id, plan_type, status)
                    VALUES (?, 'free', 'active')
                ");
                $stmt->execute([$userId]);
                
                // Log activity
                logActivity($userId, 'user_registered', 'user', $userId);
                
                // Send verification email (simplified)
                $verificationLink = SITE_URL . "/verify.php?token=" . $verificationToken;
                $emailBody = "Welcome to " . SITE_NAME . "!<br><br>Please verify your email: <a href='{$verificationLink}'>Verify Email</a>";
                sendEmail($email, 'Verify Your Email', $emailBody);
                
                flashMessage('success', 'Registration successful! Please check your email to verify your account.');
                redirect('login.php');
            } catch (Exception $e) {
                $errors[] = 'Registration failed. Please try again.';
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
    <title>Register - <?php echo SITE_NAME; ?></title>
    <meta name="description" content="Create your account and start reading premium content">
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
            max-width: 520px;
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
        
        input[type="text"],
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
            margin-top: 8px;
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
        
        .alert ul {
            margin: 0;
            padding-left: 20px;
        }
        
        .alert li {
            margin-bottom: 4px;
        }
        
        .alert li:last-child {
            margin-bottom: 0;
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
        
        .benefits {
            margin-top: 32px;
            padding-top: 24px;
            border-top: 1px solid var(--border-light);
        }
        
        .benefits-title {
            font-size: 14px;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 16px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .benefit-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        
        .benefit-item {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            font-size: 14px;
            color: var(--text-light);
            line-height: 1.5;
        }
        
        .benefit-icon {
            width: 20px;
            height: 20px;
            background: linear-gradient(135deg, var(--accent) 0%, var(--accent-dark) 100%);
            color: var(--secondary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 700;
            flex-shrink: 0;
            margin-top: 2px;
        }
        
        .password-hint {
            font-size: 13px;
            color: var(--text-lighter);
            margin-top: 6px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
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
            
            .form-row {
                grid-template-columns: 1fr;
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
        
        <h1>Create Account</h1>
        <p class="subtitle">Join thousands of readers today</p>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <span style="font-size: 20px;">⚠️</span>
                <div>
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            
            <div class="form-group">
                <label for="full_name">Full Name</label>
                <input type="text" 
                       id="full_name"
                       name="full_name" 
                       required 
                       placeholder="John Doe"
                       value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>"
                       autofocus>
            </div>
            
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" 
                       id="email"
                       name="email" 
                       required
                       placeholder="you@example.com"
                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" 
                       id="password"
                       name="password" 
                       required 
                       placeholder="Create a strong password">
                <p class="password-hint">Must be at least 8 characters long</p>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" 
                       id="confirm_password"
                       name="confirm_password" 
                       required
                       placeholder="Re-enter your password">
            </div>
            
            <button type="submit" class="btn">Create Account</button>
        </form>
        
        <div class="benefits">
            <p class="benefits-title">What You Get</p>
            <div class="benefit-list">
                <div class="benefit-item">
                    <span class="benefit-icon">✓</span>
                    <span><strong>3 free premium articles</strong> to start your journey</span>
                </div>
                <div class="benefit-item">
                    <span class="benefit-icon">✓</span>
                    <span><strong>Unlimited access</strong> to all free content</span>
                </div>
                <div class="benefit-item">
                    <span class="benefit-icon">✓</span>
                    <span><strong>Personalized recommendations</strong> based on your interests</span>
                </div>
                <div class="benefit-item">
                    <span class="benefit-icon">✓</span>
                    <span><strong>Reading history</strong> across all your devices</span>
                </div>
            </div>
        </div>
        
        <div class="link">
            Already have an account? <a href="login.php">Sign in</a>
        </div>
    </div>
</body>
</html>