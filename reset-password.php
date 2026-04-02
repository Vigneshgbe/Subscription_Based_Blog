<?php
require_once 'config.php';

if (isLoggedIn()) {
    redirect('index.php');
}

$errors = [];
$success = '';
$validToken = false;
$userEmail = '';

// Verify token
$token = $_GET['token'] ?? '';

if (!empty($token)) {
    $db = db();
    $stmt = $db->prepare("
        SELECT id, email, full_name, reset_token_expiry 
        FROM users 
        WHERE reset_token = ? 
        AND is_active = 1
    ");
    $stmt->execute([$token]);
    $user = $stmt->fetch();
    
    if ($user) {
        // Check if token is expired
        if (strtotime($user['reset_token_expiry']) > time()) {
            $validToken = true;
            $userEmail = $user['email'];
        } else {
            $errors[] = 'This password reset link has expired. Please request a new one.';
        }
    } else {
        $errors[] = 'Invalid or expired reset link.';
    }
} else {
    $errors[] = 'No reset token provided.';
}

// Process password reset
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $validToken) {
    $csrfToken = $_POST['csrf_token'] ?? '';
    
    if (!verifyCSRFToken($csrfToken)) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        // Validation
        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters long';
        }
        
        if ($password !== $confirmPassword) {
            $errors[] = 'Passwords do not match';
        }
        
        // Update password
        if (empty($errors)) {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $db->prepare("
                UPDATE users 
                SET password_hash = ?, 
                    reset_token = NULL, 
                    reset_token_expiry = NULL,
                    updated_at = CURRENT_TIMESTAMP
                WHERE reset_token = ?
            ");
            
            try {
                $stmt->execute([$passwordHash, $token]);
                
                // Log activity
                logActivity($user['id'], 'password_reset_completed', 'user', $user['id']);
                
                // Send confirmation email
                $emailBody = "
                    <html>
                    <head>
                        <style>
                            body { font-family: 'DM Sans', Arial, sans-serif; line-height: 1.6; color: #1a1830; }
                            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                            .header { background: linear-gradient(135deg, #0d0b2e 0%, #13114a 100%); color: #fff; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                            .logo { font-family: 'Cinzel', serif; font-size: 24px; font-weight: 700; letter-spacing: 3px; color: #F0B429; }
                            .content { background: #ffffff; padding: 30px; border: 1px solid #e4dfff; border-top: none; border-radius: 0 0 10px 10px; }
                            .success-box { background: #d1fae5; border-left: 4px solid #10b981; padding: 16px; margin: 20px 0; border-radius: 4px; color: #065f46; }
                            .footer { text-align: center; margin-top: 20px; color: #7a75a0; font-size: 13px; }
                        </style>
                    </head>
                    <body>
                        <div class='container'>
                            <div class='header'>
                                <div class='logo'>" . SITE_NAME . "</div>
                                <p style='margin: 10px 0 0 0; font-size: 14px; color: #ffd166;'>Password Successfully Reset</p>
                            </div>
                            <div class='content'>
                                <h2 style='color: #0d0b2e; margin-top: 0;'>Hello " . htmlspecialchars($user['full_name']) . ",</h2>
                                <div class='success-box'>
                                    <strong>✓ Success!</strong> Your password has been successfully reset.
                                </div>
                                <p>You can now log in to your account using your new password.</p>
                                <p>If you did not make this change, please contact our support team immediately.</p>
                                <p style='margin-top: 30px;'>
                                    <strong>Security Reminder:</strong> Never share your password with anyone and make sure to use a unique password for your account.
                                </p>
                            </div>
                            <div class='footer'>
                                <p>&copy; " . date('Y') . " " . SITE_NAME . ". All rights reserved.</p>
                            </div>
                        </div>
                    </body>
                    </html>
                ";
                
                sendEmail($user['email'], 'Password Successfully Reset - ' . SITE_NAME, $emailBody);
                
                $validToken = false;
                $success = 'Your password has been successfully reset! You can now log in with your new password.';
            } catch (Exception $e) {
                $errors[] = 'An error occurred while resetting your password. Please try again.';
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
    <title>Reset Password - <?php echo SITE_NAME; ?></title>
    <link rel="icon" type="image/x-icon" href="https://raw.githubusercontent.com/Vigneshgbe/Subscription_Based_Blog/refs/heads/main/assets/Logo.png">
    <meta name="description" content="Create a new password for your account">
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
            line-height: 1.6;
        }
        
        .user-info {
            background: var(--bg-tinted);
            border: 1px solid var(--border-light);
            border-radius: 10px;
            padding: 14px 16px;
            margin-bottom: 24px;
            text-align: center;
        }
        
        .user-info p {
            font-size: 13px;
            color: var(--text-mid);
            margin: 0;
        }
        
        .user-info strong {
            color: var(--purple);
            font-weight: 600;
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
        
        .password-hint {
            font-size: 12px;
            color: var(--text-lighter);
            margin-top: 6px;
            font-style: italic;
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
        
        .btn-secondary {
            background: var(--purple);
            color: var(--white);
            box-shadow: var(--shadow-purple);
        }
        
        .btn-secondary:hover {
            background: var(--purple-light);
            box-shadow: 0 8px 28px rgba(107,63,160,0.35);
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
        
        .alert-success {
            background: #d1fae5;
            border-color: #10b981;
            color: #065f46;
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
        
        .success-icon {
            text-align: center;
            margin-bottom: 24px;
            font-size: 64px;
            animation: scaleIn 0.5s ease-out;
        }
        
        @keyframes scaleIn {
            0% { transform: scale(0); opacity: 0; }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); opacity: 1; }
        }
        
        @media (max-width: 640px) {
            body {
                padding: 10px;
            }
            
            .auth-container {
                padding: 32px 24px;
            }
            
            h1 {
                font-size: 28px;
            }
            
            .logo {
                font-size: 22px;
            }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="back-home">
            <a href="login.php">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Login
            </a>
        </div>
        
        <div class="logo-section">
            <span class="logo"><?php echo SITE_NAME; ?></span>
            <p class="logo-tagline">Create. Share. Inspire.</p>
        </div>
        
        <?php if (!empty($success)): ?>
            <div class="success-icon">🎉</div>
            <h1>Success!</h1>
            <p class="subtitle">Your password has been reset</p>
            
            <div class="alert alert-success">
                <span style="font-size: 18px;">✓</span>
                <div><?php echo htmlspecialchars($success); ?></div>
            </div>
            
            <a href="login.php" class="btn btn-secondary">Go to Login</a>
            
        <?php elseif ($validToken): ?>
            <h1>Reset Password</h1>
            <p class="subtitle">Enter your new password below</p>
            
            <div class="user-info">
                <p>Resetting password for <strong><?php echo htmlspecialchars($userEmail); ?></strong></p>
            </div>
            
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
                    <label for="password">New Password</label>
                    <input type="password" 
                           id="password"
                           name="password" 
                           required 
                           placeholder="Enter your new password"
                           autofocus>
                    <p class="password-hint">Must be at least 8 characters long</p>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" 
                           id="confirm_password"
                           name="confirm_password" 
                           required
                           placeholder="Re-enter your new password">
                </div>
                
                <button type="submit" class="btn">Reset Password</button>
            </form>
            
        <?php else: ?>
            <h1>Invalid Link</h1>
            <p class="subtitle">This password reset link is invalid or has expired</p>
            
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
            
            <a href="forgot-password.php" class="btn">Request New Reset Link</a>
        <?php endif; ?>
        
        <div class="link">
            Remember your password? <a href="login.php">Sign in</a>
        </div>
    </div>
</body>
</html>