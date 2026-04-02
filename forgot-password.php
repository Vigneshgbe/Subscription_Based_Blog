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
        $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address';
        }
        
        if (empty($errors)) {
            $db = db();
            $stmt = $db->prepare("SELECT id, full_name, email FROM users WHERE email = ? AND is_active = 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user) {
                // Generate reset token
                $resetToken = generateToken();
                $resetTokenExpiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                // Update user with reset token
                $stmt = $db->prepare("
                    UPDATE users 
                    SET reset_token = ?, reset_token_expiry = ? 
                    WHERE id = ?
                ");
                $stmt->execute([$resetToken, $resetTokenExpiry, $user['id']]);
                
                // Send reset email
                $resetLink = SITE_URL . "/reset-password.php?token=" . $resetToken;
                $emailBody = "
                    <html>
                    <head>
                        <style>
                            body { font-family: 'DM Sans', Arial, sans-serif; line-height: 1.6; color: #1a1830; }
                            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                            .header { background: linear-gradient(135deg, #0d0b2e 0%, #13114a 100%); color: #fff; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                            .logo { font-family: 'Cinzel', serif; font-size: 24px; font-weight: 700; letter-spacing: 3px; color: #F0B429; }
                            .content { background: #ffffff; padding: 30px; border: 1px solid #e4dfff; border-top: none; border-radius: 0 0 10px 10px; }
                            .button { display: inline-block; background: linear-gradient(135deg, #C9952A 0%, #F0B429 100%); color: #0d0b2e; padding: 14px 32px; text-decoration: none; border-radius: 8px; font-weight: 700; margin: 20px 0; }
                            .footer { text-align: center; margin-top: 20px; color: #7a75a0; font-size: 13px; }
                            .warning { background: #fff3cd; border-left: 4px solid #F0B429; padding: 12px; margin: 20px 0; border-radius: 4px; }
                        </style>
                    </head>
                    <body>
                        <div class='container'>
                            <div class='header'>
                                <div class='logo'>" . SITE_NAME . "</div>
                                <p style='margin: 10px 0 0 0; font-size: 14px; color: #ffd166;'>Password Reset Request</p>
                            </div>
                            <div class='content'>
                                <h2 style='color: #0d0b2e; margin-top: 0;'>Hello " . htmlspecialchars($user['full_name']) . ",</h2>
                                <p>We received a request to reset your password. Click the button below to create a new password:</p>
                                <div style='text-align: center;'>
                                    <a href='" . $resetLink . "' class='button'>Reset Password</a>
                                </div>
                                <div class='warning'>
                                    <strong>⚠️ Security Notice:</strong> This link will expire in 1 hour for your security.
                                </div>
                                <p>If you didn't request a password reset, please ignore this email. Your password will remain unchanged.</p>
                                <p style='margin-top: 30px; color: #7a75a0; font-size: 13px;'>
                                    Or copy and paste this link into your browser:<br>
                                    <a href='" . $resetLink . "' style='color: #6B3FA0; word-break: break-all;'>" . $resetLink . "</a>
                                </p>
                            </div>
                            <div class='footer'>
                                <p>&copy; " . date('Y') . " " . SITE_NAME . ". All rights reserved.</p>
                            </div>
                        </div>
                    </body>
                    </html>
                ";
                
                sendEmail($user['email'], 'Password Reset Request - ' . SITE_NAME, $emailBody);
                
                // Log activity
                logActivity($user['id'], 'password_reset_requested', 'user', $user['id']);
            }
            
            // Always show success message (security best practice - don't reveal if email exists)
            $success = 'If an account exists with that email, you will receive password reset instructions shortly.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - <?php echo SITE_NAME; ?></title>
    <link rel="icon" type="image/x-icon" href="https://png.pngtree.com/element_our/sm/20180518/sm_5aff60887f7d9.jpg">
    <meta name="description" content="Reset your password and regain access to your account">
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
        
        .icon-wrapper {
            text-align: center;
            margin-bottom: 24px;
        }
        
        .icon-circle {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--purple) 0%, var(--purple-light) 100%);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            box-shadow: var(--shadow-purple);
            animation: pulse 2s ease-in-out infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); box-shadow: var(--shadow-purple); }
            50%       { transform: scale(1.05); box-shadow: 0 8px 32px rgba(107,63,160,0.3); }
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
        
        input[type="email"] {
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
        
        .alert-success {
            background: #d1fae5;
            border-color: #10b981;
            color: #065f46;
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
        
        .info-box {
            background: var(--bg-tinted);
            border: 1px solid var(--border-light);
            border-radius: 10px;
            padding: 16px;
            margin-bottom: 24px;
        }
        
        .info-box p {
            font-size: 13px;
            color: var(--text-mid);
            line-height: 1.6;
            margin: 0;
        }
        
        .info-box strong {
            color: var(--purple);
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
            
            .icon-circle {
                width: 70px;
                height: 70px;
                font-size: 32px;
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
        
        <div class="icon-wrapper">
            <div class="icon-circle">🔐</div>
        </div>
        
        <div class="logo-section">
            <span class="logo"><?php echo SITE_NAME; ?></span>
            <p class="logo-tagline">Create. Share. Inspire.</p>
        </div>
        
        <h1>Forgot Password?</h1>
        <p class="subtitle">No worries! Enter your email address and we'll send you instructions to reset your password.</p>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <span style="font-size: 18px;">⚠️</span>
                <div>
                    <?php foreach ($errors as $error): ?>
                        <?php echo htmlspecialchars($error); ?><br>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="alert alert-success">
                <span style="font-size: 18px;">✓</span>
                <div><?php echo htmlspecialchars($success); ?></div>
            </div>
            
            <div class="info-box">
                <p><strong>Check your inbox</strong></p>
                <p>If you don't see the email within a few minutes, please check your spam folder.</p>
            </div>
        <?php endif; ?>
        
        <?php if (empty($success)): ?>
            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" 
                           id="email"
                           name="email" 
                           required 
                           placeholder="Enter your registered email"
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                           autofocus>
                </div>
                
                <button type="submit" class="btn">Send Reset Link</button>
            </form>
            
            <div class="info-box">
                <p><strong>Security Notice:</strong> The reset link will expire in 1 hour.</p>
            </div>
        <?php endif; ?>
        
        <div class="link">
            Remember your password? <a href="login.php">Sign in</a>
        </div>
    </div>
</body>
</html>