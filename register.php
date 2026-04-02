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
    <link rel="icon" type="image/x-icon" href="https://raw.githubusercontent.com/Vigneshgbe/Subscription_Based_Blog/refs/heads/main/assets/Logo.png">
    <meta name="description" content="Create your account and start reading premium content">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400;1,600&family=DM+Sans:wght@300;400;500;600;700&family=Cinzel:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        :root {
            --navy:        #0d0b2e;
            --navy-mid:    #13114a;
            --navy-light:  #1e1b5e;
            --purple:      #6B3FA0;
            --purple-mid:  #7C3AED;
            --purple-light:#9d6fe8;
            --gold:        #C9952A;
            --gold-bright: #F0B429;
            --gold-light:  #ffd166;
            --gold-pale:   #fff3cd;
            --white:       #ffffff;
            --off-white:   #f8f6f0;
            --text:        #1a1830;
            --text-mid:    #4a4570;
            --text-light:  #7a75a0;
            --text-lighter:#a8a4c8;
            --border:      #e4dfff;
            --border-light:#f0eeff;
            --bg-tinted:   #faf9ff;
            --shadow-gold: 0 4px 24px rgba(201,149,42,0.18);
            --shadow-purple: 0 4px 24px rgba(107,63,160,0.18);
            --shadow-navy: 0 8px 32px rgba(13,11,46,0.18);
            --shadow-lg:   0 16px 48px rgba(13,11,46,0.12);
        }
        
        body {
            font-family: 'DM Sans', sans-serif;
            background: linear-gradient(135deg, var(--navy) 0%, var(--navy-mid) 50%, var(--navy-light) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 15px;
            position: relative;
            overflow-x: hidden;
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
                radial-gradient(ellipse at 15% 50%, rgba(107,63,160,0.25) 0%, transparent 55%),
                radial-gradient(ellipse at 85% 20%, rgba(201,149,42,0.15) 0%, transparent 50%),
                radial-gradient(ellipse at 60% 90%, rgba(124,58,237,0.12) 0%, transparent 45%);
            pointer-events: none;
        }
        
        /* Decorative sparkle */
        body::after {
            content: '✦';
            position: absolute;
            top: 8%;
            right: 12%;
            font-size: 32px;
            color: var(--gold);
            opacity: 0.3;
            animation: twinkle 3s ease-in-out infinite;
        }
        
        @keyframes twinkle {
            0%, 100% { opacity: 0.3; transform: scale(1); }
            50%       { opacity: 0.7; transform: scale(1.2); }
        }
        
        .auth-container {
            background: var(--white);
            max-width: 460px;
            width: 100%;
            padding: 40px 36px;
            border-radius: 20px;
            box-shadow: var(--shadow-lg);
            position: relative;
            z-index: 1;
            border: 1px solid rgba(201,149,42,0.1);
            max-height: 95vh;
            overflow-y: auto;
        }
        
        .auth-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--gold), var(--purple), var(--gold));
            border-radius: 20px 20px 0 0;
        }
        
        /* Custom scrollbar */
        .auth-container::-webkit-scrollbar {
            width: 6px;
        }
        
        .auth-container::-webkit-scrollbar-track {
            background: var(--bg-tinted);
            border-radius: 10px;
        }
        
        .auth-container::-webkit-scrollbar-thumb {
            background: var(--border);
            border-radius: 10px;
        }
        
        .auth-container::-webkit-scrollbar-thumb:hover {
            background: var(--purple-light);
        }
        
        .logo-section {
            text-align: center;
            margin-bottom: 28px;
        }
        
        .logo {
            font-family: 'Cinzel', serif;
            font-size: 26px;
            font-weight: 700;
            letter-spacing: 3px;
            background: linear-gradient(135deg, var(--gold) 0%, var(--gold-bright) 50%, var(--gold) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-transform: uppercase;
            margin-bottom: 8px;
            display: block;
        }
        
        .logo-tagline {
            font-size: 11px;
            color: var(--purple);
            font-weight: 600;
            letter-spacing: 2px;
            text-transform: uppercase;
        }
        
        h1 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 32px;
            font-weight: 600;
            margin-bottom: 10px;
            text-align: center;
            color: var(--navy);
            letter-spacing: -0.5px;
        }
        
        .subtitle {
            text-align: center;
            color: var(--text-mid);
            margin-bottom: 28px;
            font-size: 14px;
            font-weight: 400;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--text);
            font-size: 13px;
            letter-spacing: 0.3px;
        }
        
        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 13px 16px;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.2s;
            font-family: 'DM Sans', sans-serif;
            background: var(--white);
            color: var(--text);
        }
        
        input:focus {
            outline: none;
            border-color: var(--purple);
            box-shadow: 0 0 0 3px rgba(107,63,160,0.1);
        }
        
        input::placeholder {
            color: var(--text-lighter);
        }
        
        .btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, var(--gold) 0%, var(--gold-bright) 100%);
            color: var(--navy);
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            font-family: 'DM Sans', sans-serif;
            box-shadow: var(--shadow-gold);
            margin-top: 8px;
            letter-spacing: 0.3px;
        }
        
        .btn:hover {
            background: linear-gradient(135deg, var(--gold-bright) 0%, var(--gold) 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 28px rgba(201,149,42,0.35);
        }
        
        .btn:active {
            transform: translateY(0);
        }
        
        .alert {
            padding: 14px 18px;
            border-left: 4px solid;
            font-weight: 500;
            border-radius: 10px;
            margin-bottom: 22px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            font-size: 13px;
            line-height: 1.6;
        }
        
        .alert-danger {
            background: #fee2e2;
            border-color: #ef4444;
            color: #991b1b;
        }
        
        .alert ul {
            margin: 0;
            padding-left: 18px;
        }
        
        .alert li {
            margin-bottom: 4px;
        }
        
        .alert li:last-child {
            margin-bottom: 0;
        }
        
        .link {
            text-align: center;
            margin-top: 24px;
            padding-top: 24px;
            border-top: 1px solid var(--border-light);
            font-size: 14px;
            color: var(--text-mid);
        }
        
        .link a {
            color: var(--gold);
            text-decoration: none;
            font-weight: 700;
            transition: all 0.2s;
            border-bottom: 1px solid rgba(201,149,42,0.3);
            padding-bottom: 1px;
        }
        
        .link a:hover {
            color: var(--purple);
            border-color: rgba(107,63,160,0.4);
        }
        
        .back-home {
            text-align: center;
            margin-bottom: 24px;
        }
        
        .back-home a {
            color: var(--text-light);
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
            padding: 6px 12px;
            border-radius: 8px;
        }
        
        .back-home a:hover {
            color: var(--purple);
            background: var(--bg-tinted);
        }
        
        .back-home svg {
            width: 14px;
            height: 14px;
        }
        
        .benefits {
            margin-top: 24px;
            padding-top: 24px;
            border-top: 1px solid var(--border-light);
        }
        
        .benefits-title {
            font-size: 11px;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
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
            font-size: 13px;
            color: var(--text-mid);
            line-height: 1.5;
        }
        
        .benefit-icon {
            width: 20px;
            height: 20px;
            background: linear-gradient(135deg, var(--gold) 0%, var(--gold-bright) 100%);
            color: var(--white);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 700;
            flex-shrink: 0;
            margin-top: 1px;
        }
        
        .password-hint {
            font-size: 12px;
            color: var(--text-lighter);
            margin-top: 6px;
            font-style: italic;
        }
        
        @media (max-width: 640px) {
            body {
                padding: 10px;
            }
            
            .auth-container {
                padding: 32px 24px;
                max-height: 98vh;
            }
            
            h1 {
                font-size: 28px;
            }
            
            .logo {
                font-size: 22px;
            }
        }
        
        @media (min-height: 800px) {
            .auth-container {
                max-height: none;
                overflow-y: visible;
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
            <p class="logo-tagline">Create. Share. Inspire.</p>
        </div>
        
        <h1>Create Account</h1>
        <p class="subtitle">Join thousands of readers today</p>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <span style="font-size: 18px;">⚠️</span>
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
           
        <div class="link">
            Already have an account? <a href="login.php">Sign in</a>
        </div>
    </div>
</body>
</html>